<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks;

use YahnisElsts\AdminMenuEditor\Customizable\Builders\BaseElementBuilder;
use YahnisElsts\AdminMenuEditor\Customizable\Builders\ElementBuilderFactory;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas\Boolean;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\WithSchema\SingularSetting;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\AbstractSettingsDictionary;
use YahnisElsts\AdminMenuEditor\Tweaks\Core\ameBaseTweak;

class PlaceholderTweak extends ameBaseTweak {
	public function apply($settings = null) {
		//This tweak doesn't do anything, it's just a placeholder for Pro features.
	}

	public function createUiElement(
		ElementBuilderFactory $b,
		AbstractSettingsDictionary $settings,
		$settingPrefix,
		array $extraElementParams = []
	): BaseElementBuilder {
		//Create a checkbox with a dummy setting that doesn't do anything.
		$tweakControl = $b->checkBox(self::getPlaceholderSetting())
			->id($this->generateUiElementHtmlId())
			->label($this->getLabel() ?? $this->getId())
			->params($extraElementParams);

		if ( $this->getDescription() ) {
			$tweakControl->description($this->getDescription());
		}

		return $tweakControl;
	}

	private static function getPlaceholderSetting(): AbstractSetting {
		static $setting = null;
		if ( $setting === null ) {
			$setting = new SingularSetting(
				new Boolean(),
				'ame_placeholder_tweak_enabled',
			);
		}
		return $setting;
	}
}