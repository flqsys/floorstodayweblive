<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\WithSchema;

use YahnisElsts\AdminMenuEditor\Customizable\Builders;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;

class Borders extends CssSettingCollection {
	protected bool $includesColor = true;

	public function __construct(Schemas\Struct $schema, $id = '', ?StorageInterface $store = null, $params = []) {
		parent::__construct($schema, $id, $store, $params);

		if ( array_key_exists('includesColor', $params) ) {
			$this->includesColor = (bool)$params['includesColor'];
		}
	}

	public static function createDefaultSchema(
		Schemas\SchemaFactory $s,
		bool                  $includesColor = true,
		array                 $defaultWidths = []
	): Schemas\Struct {
		$fields = [
			'style'  => $s->cssBorderStyle('Style'),
			'width'  => CssBoxDimensions::createBorderWidthSchema($s, $defaultWidths)->settingParams(['label' => 'Width']),
			'radius' => CssBoxDimensions::createBorderRadiusSchema($s)->settingParams(['label' => 'Border radius']),
		];

		$params = ['includesColor' => $includesColor];
		if ( $includesColor ) {
			$fields['color'] = $s->cssColor('Color', 'border-color');
		}

		return $s->struct($fields, 'Border')->s(static::class, $params);
	}

	/**
	 * @inheritDoc
	 */
	public function createControls(Builders\ElementBuilderFactory $b): array {
		$controls = [
			$b->select($this->settings['style']),
		];
		if ( $this->includesColor && isset($this->settings['color']) ) {
			$controls[] = $b->colorPicker($this->settings['color']);
		}
		$controls[] = $b->boxDimensions($this->settings['width'])->classes('ame-box-dimensions-with-indicators');
		$controls[] = $b->boxDimensions($this->settings['radius']);

		return $controls;
	}
}