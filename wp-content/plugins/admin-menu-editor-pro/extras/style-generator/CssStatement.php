<?php

namespace YahnisElsts\AdminMenuEditor\StyleGenerator;

interface CssStatement {
	/**
	 * @param int $indentLevel
	 * @param string[] $parentSelectors
	 * @return StatementEvaluationResult
	 */
	public function evaluate(int $indentLevel = 0, array $parentSelectors = []): StatementEvaluationResult;

	public function serializeForJs(): array;
}