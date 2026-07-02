<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable;

use YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting;

interface HasUnit {
	public function getUnitSetting(): ?AbstractSetting;
}