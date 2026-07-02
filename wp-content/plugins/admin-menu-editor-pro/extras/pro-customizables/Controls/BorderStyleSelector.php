<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\Controls\RadioGroup;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\StaticHtml;

class BorderStyleSelector extends RadioGroup {
	const SUPPORTED_STYLES = array(
		'hidden' => true,
		'dotted' => true,
		'dashed' => true,
		'solid'  => true,
		'double' => true,
		'groove' => true,
		'ridge'  => true,
		'inset'  => true,
		'outset' => true,
		//"none", "inherit", and "initial" are not included because they
		//cannot be represented visually.
	);

	public function __construct($settings = array(), $params = array(), $children = []) {
		parent::__construct($settings, $params, $children);

		//Add samples for recognized values.
		foreach ($this->options as $option) {
			if (
				!empty(self::SUPPORTED_STYLES[$option->value])
				//Don't overwrite existing children.
				&& !$this->hasOptionChild($option->value)
			) {
				$this->addOptionChild($option->value, new StaticHtml(sprintf(
					'<label class="ame-border-sample-container" for="%1$s">'
					. '<span class="ame-border-sample ame-border-sample--%2$s" style="border-style: %2$s"></span>'
					. '</label>',
					$this->getRadioInputId($option),
					esc_attr($option->value)
				)));
			}
		}
	}
}