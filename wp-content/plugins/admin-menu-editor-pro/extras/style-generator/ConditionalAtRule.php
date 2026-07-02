<?php

namespace YahnisElsts\AdminMenuEditor\StyleGenerator;

class ConditionalAtRule extends BaseCssStatement implements CssStatement {
	/**
	 * @var string
	 */
	protected string $identifier;
	/**
	 * @var string
	 */
	protected string $conditionString;

	public function __construct($identifier, $conditionString, $nestedStatements = []) {
		parent::__construct($nestedStatements);
		$this->identifier = $identifier;
		$this->conditionString = $conditionString;
	}

	public function serializeForJs(): array {
		$result = parent::serializeForJs();

		return array_merge($result, [
			't'          => 'conditionalAtRule',
			'identifier' => $this->identifier,
			'condition'  => $this->conditionString,
		]);
	}

	public function evaluate(int $indentLevel = 0, array $parentSelectors = []): StatementEvaluationResult {
		$childrenResult = $this->evaluateChildren($indentLevel + 1, $parentSelectors);

		$indent = str_repeat("\t", $indentLevel);

		$nestedStatementCss = '';
		foreach ($childrenResult->getCompiledStatements() as $item) {
			$statementCss = $item->getCssText();
			if ( $statementCss !== '' ) {
				$nestedStatementCss .= $indent . $statementCss . "\n";
			}
		}

		if ( $nestedStatementCss === '' ) {
			return new StatementEvaluationResult(); //Empty result.
		}

		$output = "@$this->identifier $this->conditionString {\n";
		$output .= $nestedStatementCss;
		$output .= "}\n";

		return new StatementEvaluationResult([], [new CompiledCssStatement($output)]);
	}

}