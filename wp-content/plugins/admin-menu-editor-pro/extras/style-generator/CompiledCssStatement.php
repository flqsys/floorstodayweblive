<?php

namespace YahnisElsts\AdminMenuEditor\StyleGenerator;

class CompiledCssStatement {
	protected string $cssText;

	public function __construct(string $cssText) {
		$this->cssText = $cssText;
	}

	public function getCssText(): string {
		return $this->cssText;
	}
}