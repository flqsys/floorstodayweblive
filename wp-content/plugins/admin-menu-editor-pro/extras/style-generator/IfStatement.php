<?php

namespace YahnisElsts\AdminMenuEditor\StyleGenerator;

use YahnisElsts\AdminMenuEditor\StyleGenerator\Dsl\Expression;

class IfStatement extends BaseCssStatement {
	protected Expression $condition;

	public function __construct(Expression $condition, array $children = []) {
		parent::__construct($children);
		$this->condition = $condition;
	}

	/**
	 * @inheritDoc
	 */
	public function evaluate(int $indentLevel = 0, array $parentSelectors = []): StatementEvaluationResult {
		if ( !$this->condition->getValue() ) {
			return new StatementEvaluationResult();
		}
		return parent::evaluateChildren($indentLevel, $parentSelectors);
	}

	public function serializeForJs(): array {
		$result = parent::serializeForJs();
		$result['t'] = 'ifStatement';
		$result['condition'] = $this->condition->jsonSerialize();
		return $result;
	}
}