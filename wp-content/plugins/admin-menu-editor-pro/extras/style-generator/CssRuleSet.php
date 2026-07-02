<?php

namespace YahnisElsts\AdminMenuEditor\StyleGenerator;

use YahnisElsts\AdminMenuEditor\ProCustomizable\CssPropertyGenerator;

class CssRuleSet extends BaseCssStatement implements CssStatement {
	private array $selectors;

	/**
	 * @param string[] $selectors
	 * @param array<string|int|float|CssPropertyGenerator> $declarations
	 * @param CssStatement[] $nestedStatements
	 */
	public function __construct($selectors, array $declarations, array $nestedStatements = []) {
		parent::__construct(array_merge($declarations, $nestedStatements));
		$this->selectors = $selectors;
	}

	public function serializeForJs(): array {
		$result = parent::serializeForJs();

		$result['t'] = 'ruleset';
		$result['selectors'] = $this->selectors;

		return $result;
	}

	public function evaluate(int $indentLevel = 0, array $parentSelectors = []): StatementEvaluationResult {
		//Combine our selectors with the parent selectors like SCSS does.
		$selectors = $this->selectors;
		if ( !empty($parentSelectors) ) {
			$selectors = self::combineSelectors($selectors, $parentSelectors);
		}

		$childrenResult = $this->evaluateChildren($indentLevel, $selectors);
		$compiledStatements = $childrenResult->getCompiledStatements();

		//Wrap the declarations from this rule set in a new compiled statement and prepend it to the list.
		$declarations = $childrenResult->getDeclarations();
		if ( !empty($declarations) ) {
			$css = '';

			$selectorIndent = str_repeat("\t", $indentLevel);
			$declarationIndent = str_repeat("\t", $indentLevel + 1);

			$css .= $selectorIndent . implode(', ', $selectors) . " {\n";
			foreach ($declarations as $key => $value) {
				$css .= $declarationIndent . $key . ': ' . $value . ";\n";
			}
			$css .= $selectorIndent . "}\n";

			array_unshift($compiledStatements, new CompiledCssStatement($css));
		}

		return new StatementEvaluationResult([], $compiledStatements);
	}

	private static function combineSelectors(array $selectors, array $parentSelectors): array {
		$combinedSelectors = [];
		foreach ($selectors as $selector) {
			if ( $selector === '' ) {
				continue;
			}
			if ( strpos($selector, '&') !== false ) {
				//Insert the parent selectors into the current selector at the position of the "&".
				foreach ($parentSelectors as $parentSelector) {
					$combinedSelectors[] = str_replace('&', rtrim($parentSelector), $selector);
				}
			} else {
				//Just append the current selector to the parent selectors.
				foreach ($parentSelectors as $parentSelector) {
					$combinedSelectors[] = $parentSelector . ' ' . $selector;
				}
			}
		}
		return $combinedSelectors;
	}
}