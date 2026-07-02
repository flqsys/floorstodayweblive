<?php

namespace YahnisElsts\AdminMenuEditor\StyleGenerator;

use YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\Setting;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\WithSchema\SingularSetting;
use YahnisElsts\AdminMenuEditor\ProCustomizable\CssPropertyGenerator;
use YahnisElsts\AdminMenuEditor\ProCustomizable\CssValueGenerator;
use YahnisElsts\AdminMenuEditor\StyleGenerator\Dsl\Expression;
use YahnisElsts\AdminMenuEditor\StyleGenerator\Dsl\JsFunctionCall;
use YahnisElsts\AdminMenuEditor\StyleGenerator\Dsl\SerializableToJsExpression;

abstract class BaseCssStatement implements CssStatement {
	protected array $children = [];
	protected bool $autoSkipModeEnabled = true;

	protected function __construct(array $children = []) {
		$this->children = $children;
	}

	protected function evaluateChildren(int $indentLevel = 0, array $selectors = []): StatementEvaluationResult {
		$declarations = [];
		$compiledRules = [];

		$hasSettings = false;
		$hasNonEmptySettings = false;

		foreach ($this->children as $key => $value) {
			//Remember if this statement uses settings in its contents.
			if ( !$hasNonEmptySettings ) {
				if ( $value instanceof AbstractSetting ) {
					$hasSettings = true;
					if ( $value->getValue('') !== '' ) {
						$hasNonEmptySettings = true;
					}
				} else if ( $value instanceof Expression ) {
					list($exprUsesSettings, $exprHasNonEmptySettings) = $value->checkUsedSettingStatus();
					$hasSettings = $hasSettings || $exprUsesSettings;
					$hasNonEmptySettings = $hasNonEmptySettings || $exprHasNonEmptySettings;
				}
			}

			if ( is_string($key) ) {
				$cssValue = null;

				if ( $value instanceof CssValueGenerator ) {
					$cssValue = $value->getCssValue();
				} else if ( $value instanceof Expression ) {
					$cssValue = $value->getValue();
				} else if ( $value instanceof Setting ) {
					$cssValue = $value->getValue();
				} else if ( $value instanceof SingularSetting ) {
					$cssValue = $value->getValue();
				} else if ( is_scalar($value) ) {
					$cssValue = $value;
				} else {
					throw new \LogicException(sprintf(
						"Unsupported declaration type '%s' for key '%s'",
						gettype($value),
						$key
					));
				}

				if ( !StyleGenerator::isEmptyCssValue($cssValue) ) {
					$declarations[$key] = $cssValue;
				}

			} else if ( $value instanceof CssPropertyGenerator ) {
				$declarations = array_merge($declarations, $value->getCssProperties());
			} else if ( $value instanceof CssStatement ) {
				$result = $value->evaluate($indentLevel, $selectors);
				$declarations = array_merge($declarations, $result->getDeclarations());
				$compiledRules = array_merge($compiledRules, $result->getCompiledStatements());
			} else {
				throw new \LogicException(sprintf(
					"Unsupported child type '%s'",
					is_object($value) ? get_class($value) : gettype($value)
				));
			}
		}

		//If the declarations in this statement are based on dynamic settings but all the settings
		//are empty, don't generate any CSS declarations. However, nested statements can still
		//generate CSS in this case.
		if ( $hasSettings && !$hasNonEmptySettings && $this->autoSkipModeEnabled ) {
			$declarations = [];
		}

		return new StatementEvaluationResult($declarations, $compiledRules);
	}

	/**
	 * Disable the default behaviour that automatically discards all declarations when any declarations
	 * contain settings but all of those settings are empty.
	 *
	 * This doesn't affect declarations inside nested statements.
	 *
	 * @return $this
	 */
	public function noSkipOwnDeclarations(): self {
		$this->autoSkipModeEnabled = false;
		return $this;
	}

	public function serializeForJs(): array {
		$serializedChildren = iterator_to_array($this->makeJsConfigsForChildren(), false);

		$result = [];
		if ( !empty($serializedChildren) ) {
			$result['children'] = $serializedChildren;
		}
		return $result;
	}

	protected function makeJsConfigsForChildren(): \Generator {
		foreach ($this->children as $key => $value) {
			if ( is_string($key) ) {
				if ( $value instanceof CssPropertyGenerator ) {
					foreach ($value->getJsPreviewConfiguration() as $c) {
						$c->setCssProperty($key);
						yield $c;
					}
				} else {
					yield JsFunctionCall::prop($key, $value);
				}
			} else if ( $value instanceof CssPropertyGenerator ) {
				yield from $value->getJsPreviewConfiguration();
			} else if ( $value instanceof SerializableToJsExpression ) {
				//It's up to you to ensure that the serialized expression actually
				//produces a valid array of CSS declarations.
				yield $value;
			} else if ( $value instanceof CssStatement ) {
				yield $value->serializeForJs();
			} else {
				throw new \LogicException(sprintf(
					"Error generating JS config: Unsupported child type '%s' for key '%s'",
					gettype($value),
					$key
				));
			}
		}
	}
}