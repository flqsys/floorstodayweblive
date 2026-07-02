<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks\Core;

class ameHideJquerySelectorTweak extends ameBaseTweak {
	/**
	 * @var array
	 */
	protected $selector;

	public function __construct($id, $label, array $advancedSelector) {
		parent::__construct($id, $label);
		$this->selector = $advancedSelector;
	}

	public function apply($settings = null) {
		$manager = ameJqueryTweakManager::getInstance();
		$manager->addSelector($this->selector);
	}
}

