<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\WithSchema;

use YahnisElsts\AdminMenuEditor\Customizable\Controls\ChoiceControlOption;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas\SchemaFactory;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;
use YahnisElsts\AdminMenuEditor\Customizable\Builders\ElementBuilderFactory;

class Font extends CssSettingCollection {
	protected bool $includesLineHeight = true;

	public function __construct(Schemas\Struct $schema, $id = '', ?StorageInterface $store = null, $params = []) {
		parent::__construct($schema, $id, $store, $params);

		if ( array_key_exists('includesLineHeight', $params) ) {
			$this->includesLineHeight = (bool)$params['includesLineHeight'];
		}
	}

	public static function createDefaultSchema(SchemaFactory $s, $label = '', $params = []): Schemas\Struct {
		if ( array_key_exists('includesLineHeight', $params) ) {
			$includesLineHeight = (bool)$params['includesLineHeight'];
		} else {
			$includesLineHeight = true;
		}
		$params['includesLineHeight'] = $includesLineHeight;

		$fields = [
			'family' => $s->string()->defaultValue(null),

			'size'     => $s->number('Font size')->min(0)->max(200)->defaultValue(null)
				->s(CssLengthSetting::class, ['cssProperty' => 'font-size'])
				->settingReference('unitSetting', 'sizeUnit'),

			//This is intentionally defined after "size" to verify that the builder can handle
			//fields in any order.
			'sizeUnit' => $s->enum(['px', 'em', 'rem', 'vw'])->defaultValue('px')
				//Override the default label generation algorithm and just use the unit name.
				->describeValue('px', 'px')
				->describeValue('em', 'em')
				->describeValue('rem', 'rem')
				->describeValue('vw', 'vw'),

			'weight' => $s->enum(
				[
					null,
					'normal',
					'bold',
					'bolder',
					'lighter',
					'100',
					'200',
					'300',
					'400',
					'500',
					'600',
					'700',
					'800',
					'900',
				],
				'Font weight'
			)->defaultValue(null)
				->settingParams(['cssProperty' => 'font-weight'])
				->settingClassHint(CssPropertySetting::class),

			'style' => $s->enum([null, 'normal', 'italic', 'oblique'])->defaultValue(null)
				->s(CssPropertySetting::class, ['cssProperty' => 'font-style']),

			'variant' => $s->enum([null, 'normal', 'small-caps'])->defaultValue(null)
				->s(CssPropertySetting::class, ['cssProperty' => 'font-variant']),

			'text-transform' => $s->enum([
				null,
				'none',
				'uppercase',
				'lowercase',
				'capitalize',
				'full-width',
			])->defaultValue(null)
				->s(CssPropertySetting::class, ['cssProperty' => 'text-transform']),

			'text-decoration' => $s->enum([null, 'none', 'underline', 'overline', 'line-through'])
				->defaultValue(null)
				->s(CssPropertySetting::class, ['cssProperty' => 'text-decoration']),

			'letter-spacing' => $s->number('Letter spacing')->min(-10)->max(100)->defaultValue(null)
				->s(CssLengthSetting::class, ['cssProperty' => 'letter-spacing'])
				->settingReference('unitSetting', 'letterSpacingUnit'),

			'letterSpacingUnit' => $s->enum(['px', 'em', 'rem', 'vw'])->defaultValue('px')
				->describeValue('px', 'px')
				->describeValue('em', 'em')
				->describeValue('rem', 'rem')
				->describeValue('vw', 'vw'),
		];

		if ( $includesLineHeight ) {
			$fields['lineHeightUnit'] = $s->enum(['', 'px', 'em'])->defaultValue('')
				->describeValue('', '—') //Unit-less.
				->describeValue('px', 'px')
				->describeValue('em', 'em');

			$fields['line-height'] = $s->number('Line height')->min(0)->max(200)->defaultValue(null)
				->s(CssLengthSetting::class, ['cssProperty' => 'line-height', 'defaultUnit' => ''])
				->settingReference('unitSetting', 'lineHeightUnit');
		}

		return $s->struct($fields, $label)->s(self::class, $params);
	}

	public function createControls(ElementBuilderFactory $b): array {
		$sizeUnit = $this->settings['sizeUnit'];

		$controls = [
			$b->number($this->settings['size'])
				->unitSetting($sizeUnit)
				->inputClasses('ame-font-size-input')
				->params([
					'rangeByUnit' => [
						'px'  => ['min' => 1, 'max' => 72, 'step' => 1],
						'em'  => ['min' => 0.2, 'max' => 10, 'step' => 0.1],
						'rem' => ['min' => 0.2, 'max' => 10, 'step' => 0.1],
						'vw'  => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
					],
				])
				->asGroup(),
			$b->select($this->settings['weight'])
				->params([
					'choices' => [
						//The default value is NULL = no change.
						new ChoiceControlOption(null, 'Default'),
						new ChoiceControlOption('100', 'Thin'),
						new ChoiceControlOption('200', 'Extra Light'),
						new ChoiceControlOption('300', 'Light'),
						new ChoiceControlOption('400', 'Normal'),
						new ChoiceControlOption('500', 'Medium'),
						new ChoiceControlOption('600', 'Semi Bold'),
						new ChoiceControlOption('700', 'Bold'),
						new ChoiceControlOption('800', 'Extra Bold'),
						new ChoiceControlOption('900', 'Heavy'),
					],
				]),
		];

		$controls[] = $b->fontStyle([
			'font-style'      => $this->settings['style'],
			'font-variant'    => $this->settings['variant'],
			'text-transform'  => $this->settings['text-transform'],
			'text-decoration' => $this->settings['text-decoration'],
		])->label('Style');

		if ( $this->includesLineHeight && isset($this->settings['line-height']) ) {
			$controls[] = $b->number($this->settings['line-height'])
				->inputClasses('ame-small-number-input', 'ame-line-height-input')
				->params([
					'rangeByUnit' => [
						''   => ['min' => 0.1, 'max' => 5, 'step' => 0.1],
						'px' => ['min' => 1, 'max' => 100, 'step' => 1],
						'em' => ['min' => 0.2, 'max' => 10, 'step' => 0.1],
					],
				]);
		}

		//Layout note: Both Divi and Elementor put letter spacing and line height together, but Elementor
		//puts line height first and Divi puts it second.
		$controls[] = $b->number($this->settings['letter-spacing'])
			->inputClasses('ame-small-number-input', 'ame-letter-spacing-input')
			->params([
				'rangeByUnit' => [
					'px'  => ['min' => -10, 'max' => 30, 'step' => 1],
					'em'  => ['min' => -1, 'max' => 3, 'step' => 0.1],
					'rem' => ['min' => -1, 'max' => 3, 'step' => 0.1],
					'vw'  => ['min' => -1, 'max' => 5, 'step' => 0.1],
				],
			]);

		return $controls;
	}
}

