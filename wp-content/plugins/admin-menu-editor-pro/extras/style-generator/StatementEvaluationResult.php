<?php

namespace YahnisElsts\AdminMenuEditor\StyleGenerator;

class StatementEvaluationResult {
	private array $declarations;
	private array $compiledStatements;

	public function __construct(array $declarations = [], array $compiledStatements = []) {
		$this->declarations = $declarations;
		$this->compiledStatements = $compiledStatements;
	}

	/**
	 * @return array<string, string>
	 */
	public function getDeclarations(): array {
		return $this->declarations;
	}

	/**
	 * @return CompiledCssStatement[]
	 */
	public function getCompiledStatements(): array {
		return $this->compiledStatements;
	}
}