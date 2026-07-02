<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\WithSchema;

use YahnisElsts\AdminMenuEditor\ProCustomizable\CssPropertyGenerator;
use YahnisElsts\AdminMenuEditor\Customizable\Settings;

abstract class CssSettingCollection
	extends Settings\WithSchema\StructSetting
	implements CssPropertyGenerator, Settings\PredefinedSet {

	/**
	 * @var string|null
	 */
	protected ?string $cssPropertyPrefix = null;

	public function getCssProperties(): array {
		$result = array();
		foreach ($this->settings as $key => $setting) {
			if ( $setting instanceof CssPropertyGenerator ) {
				$result = array_merge($result, $setting->getCssProperties());
			} else if (
				(($setting instanceof Settings\Setting) || ($setting instanceof Settings\WithSchema\SingularSetting))
				&& !empty($key)
			) {
				$value = $setting->getValue();
				if ( ($value !== null) && ($value !== '') && ($this->cssPropertyPrefix !== null) ) {
					$result[$this->cssPropertyPrefix . $key] = $value;
				}
			}
		}
		return $result;
	}

	public function getIterator(): \Iterator {
		return new \ArrayIterator($this->settings);
	}

	public function getJsPreviewConfiguration(): array {
		$configs = [];
		foreach ($this->settings as $setting) {
			if ( $setting instanceof CssPropertyGenerator ) {
				$childConfig = $setting->getJsPreviewConfiguration();
				if ( !empty($childConfig) ) {
					$configs = array_merge($configs, $childConfig);
				}
			}
		}
		return $configs;
	}
}