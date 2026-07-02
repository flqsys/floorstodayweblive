<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\WithSchema;

use YahnisElsts\AdminMenuEditor\Customizable\Builders;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;
use YahnisElsts\AdminMenuEditor\ProCustomizable\HasUnit;

class CssBoxDimensions extends CssSettingCollection implements HasUnit {
	const DEFAULT_SIDE_DIMENSIONS = [
		'top'    => 'top',
		'right'  => 'right',
		'bottom' => 'bottom',
		'left'   => 'left',
	];

	/**
	 * @var array An associative array mapping dimension keys to their CSS property prefixes.
	 * For example: ['topLeft' => 'top-left'].
	 */
	protected array $dimensions;
	protected string $cssPropertySuffix = '';
	protected ?string $cssShorthandProperty = null;

	public function __construct(
		Schemas\Struct    $schema,
		                  $id = '',
		?StorageInterface $store = null,
		                  $params = []
	) {
		parent::__construct($schema, $id, $store, $params);

		if ( isset($params['dimensions']) ) {
			$this->dimensions = (array)$params['dimensions'];
		} else {
			throw new \InvalidArgumentException('The "dimensions" parameter is required for CssBoxDimensions.');
		}

		if ( isset($params['cssPropertyPrefix']) ) {
			$this->cssPropertyPrefix = (string)$params['cssPropertyPrefix'];
		} else {
			$this->cssPropertyPrefix = '';
		}
		if ( isset($params['cssPropertySuffix']) ) {
			$this->cssPropertySuffix = (string)$params['cssPropertySuffix'];
		}
		if ( isset($params['cssShorthandProperty']) ) {
			$this->cssShorthandProperty = (string)$params['cssShorthandProperty'];
		}
	}

	public function getCssProperties(): array {
		$properties = [];
		if ( empty($this->cssPropertyPrefix) ) {
			return $properties;
		}

		$areAllEqual = true;
		$firstValue = null;

		foreach ($this->dimensions as $dimensionKey => $_unused) {
			$setting = $this->settings[$dimensionKey];
			if ( !($setting instanceof CssLengthSetting) ) {
				continue;
			}

			$value = $setting->getCssValue();
			if ( $value !== null ) {
				$properties[$this->getCssPropertyForDimension($dimensionKey)] = $value;
			}

			if ( $firstValue === null ) {
				$firstValue = $value;
			} else if ( $firstValue !== $value ) {
				$areAllEqual = false;
			}
		}

		//Optimization: If all properties are set and equal, we can use the shorthand property.
		if (
			$this->cssShorthandProperty
			&& $areAllEqual
			&& ($firstValue !== null)
			&& (count($properties) === count($this->dimensions))
		) {
			$properties = [$this->cssShorthandProperty => $firstValue];
		}

		return $properties;
	}

	public function getJsPreviewConfiguration(): array {
		$sideConfigs = [];
		foreach ($this->dimensions as $side => $unused) {
			$setting = $this->settings[$side];
			if ( !($setting instanceof CssLengthSetting) ) {
				continue;
			}
			$sideConfigs = array_merge($sideConfigs, $setting->getJsPreviewConfiguration());
		}
		return $sideConfigs;
	}

	protected function getCssPropertyForDimension(string $dimensionKey): string {
		if ( empty($this->cssPropertyPrefix) ) {
			return '';
		}
		$cssNameComponent = $this->dimensions[$dimensionKey] ?? $dimensionKey;
		return ($this->cssPropertyPrefix . $cssNameComponent . $this->cssPropertySuffix);
	}

	public function getUnitSetting(): ?AbstractSetting {
		return $this->settings['unit'] ?? null;
	}

	public function createControls(Builders\ElementBuilderFactory $b): array {
		return [$b->boxDimensions($this)];
	}

	protected static function createDefaultSchema(
		Schemas\SchemaFactory $s,
		array                 $dimensions,
		string                $cssPropertyPrefix = '',
		string                $cssPropertySuffix = '',
		?string               $cssShorthandProperty = null,
		array                 $dimensionDefaults = []
	): Schemas\Struct {
		$fields = [];

		$fields['unit'] = $s->enum(['px', 'em', '%'])->defaultValue('px')
			->describeValue('px', 'px')
			->describeValue('em', 'em')
			->describeValue('%', '%');

		foreach ($dimensions as $dimensionKey => $cssNameComponent) {
			$fields[$dimensionKey] = $s->number($dimensionKey)->min(-1000)->max(1000)
				->defaultValue(\ameUtils::get($dimensionDefaults, $dimensionKey, null))
				->s(CssLengthSetting::class, ['cssProperty' => $cssPropertyPrefix . $cssNameComponent . $cssPropertySuffix])
				->settingReference('unitSetting', 'unit');
		}

		$schema = new Schemas\Struct($fields);
		$schema->s(static::class, [
			'dimensions'           => $dimensions,
			'cssPropertyPrefix'    => $cssPropertyPrefix,
			'cssPropertySuffix'    => $cssPropertySuffix,
			'cssShorthandProperty' => $cssShorthandProperty,
		]);
		return $schema;
	}

	public static function createPaddingSchema(Schemas\SchemaFactory $s, ?string $label = null): Schemas\Struct {
		$schema = self::createDefaultSchema($s, self::DEFAULT_SIDE_DIMENSIONS, 'padding-', '', 'padding');
		$schema->settingParams(['label' => $label ?? 'Padding']);
		return $schema;
	}

	public static function createMarginSchema(Schemas\SchemaFactory $s, ?string $label = null): Schemas\Struct {
		$schema = self::createDefaultSchema($s, self::DEFAULT_SIDE_DIMENSIONS, 'margin-', '', 'margin');
		$schema->settingParams(['label' => $label ?? 'Margin']);
		return $schema;
	}

	public static function createBorderRadiusSchema(Schemas\SchemaFactory $s): Schemas\Struct {
		$dimensions = [
			'topLeft'     => 'top-left',
			'topRight'    => 'top-right',
			'bottomRight' => 'bottom-right',
			'bottomLeft'  => 'bottom-left',
		];
		$schema = self::createDefaultSchema(
			$s,
			$dimensions,
			'border-',
			'-radius',
			'border-radius'
		);
		$schema->settingParams(['label' => 'Border radius']);
		return $schema;
	}

	public static function createBorderWidthSchema(Schemas\SchemaFactory $s, array $dimensionDefaults = []): Schemas\Struct {
		$schema = self::createDefaultSchema(
			$s,
			self::DEFAULT_SIDE_DIMENSIONS,
			'border-',
			'-width',
			'border-width',
			$dimensionDefaults
		);
		$schema->settingParams(['label' => 'Border width']);
		return $schema;
	}
}