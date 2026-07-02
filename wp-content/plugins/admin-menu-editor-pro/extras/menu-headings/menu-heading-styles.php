<?php

namespace YahnisElsts\AdminMenuEditor\MenuHeadingStyles;

use YahnisElsts\AdminMenuEditor\Customizable\Controls\ChoiceControlOption;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\InterfaceStructure;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\StaticHtml;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\TabbedPanelRenderer;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas\Enum;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas\SchemaFactory;
use YahnisElsts\AdminMenuEditor\Customizable\SettingCondition;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\WithSchema\SettingWithSchema;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\AbstractSettingsDictionary;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\LazyArrayStorage;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\MenuConfigurationWrapper;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;
use YahnisElsts\AdminMenuEditor\DynamicStylesheets\MenuScopedStylesheetHelper;
use YahnisElsts\AdminMenuEditor\DynamicStylesheets\Stylesheet;
use YahnisElsts\AdminMenuEditor\StyleGenerator\CssRuleSet;
use YahnisElsts\AdminMenuEditor\StyleGenerator\StyleGenerator;
use YahnisElsts\WpDependencyWrapper\v1\ScriptDependency;

class MenuHeadingStyler {

	protected \WPMenuEditor $menuEditor;
	protected ?HeadingStyleSettings $settings = null;

	public function __construct(\WPMenuEditor $menuEditor) {
		$this->menuEditor = $menuEditor;

		if ( !is_admin() ) {
			return;
		}

		add_action('admin_init', [$this, 'registerMenuHeadingStylesheet']);

		if ( !empty($_COOKIE['ame-collapsed-menu-headings']) ) {
			add_action('in_admin_header', [$this, 'outputRestorationTrigger']);
		}

		add_filter('admin_menu_editor-aux_data_config', [$this, 'addAuxDataConfig']);
		add_filter('admin_menu_editor-editor_script_dependencies', [$this, 'addEditorDependencies']);
		add_action('admin_menu_editor-enqueue_styles-editor', [$this, 'enqueueEditorStyles']);
		add_action('admin_menu_editor-footer-editor', [$this, 'outputUiTemplate']);

		if ( defined('DOING_AJAX') ) {
			$this->getPresetStylesheet()->addOutputHook();
		}
	}

	protected function getSettings($menuConfigId = null): HeadingStyleSettings {
		if ( $this->settings !== null ) {
			return $this->settings;
		}

		if ( $menuConfigId === null ) {
			$helper = MenuScopedStylesheetHelper::getInstance($this->menuEditor);
			$menuConfigId = $helper->getConfigIdFromAjaxRequest();
		}

		$this->settings = new HeadingStyleSettings(
			MenuConfigurationWrapper::getStore($menuConfigId)
				->buildSlot(HeadingStyleSettings::CONFIG_KEY)
		);
		return $this->settings;
	}

	protected function getInterfaceStructure(): InterfaceStructure {
		$settings = $this->getSettings();
		$b = $settings->elementBuilder();

		//Section toggle icons: Create a radio card group with a preview of each icon option.
		$iconSetting = $settings->getSetting('collapsibleSections.icons');
		$toggleIconPicker = $b->radioCardGroup($iconSetting);
		if ( $iconSetting instanceof SettingWithSchema ) {
			$schema = $iconSetting->getSchema();
			if ( $schema instanceof Enum ) {
				//The two conditions above should always be true, but they help avoid IDE warnings
				//and also future-proof the code a bit.
				foreach ($schema->getEnumValues() as $enumValue) {
					if ( $enumValue === 'none' ) {
						continue;
					}

					$parts = [];
					foreach ([true, false] as $isCollapsed) {
						$containerClasses = ['ame-mh-toggle-preview'];
						if ( $isCollapsed ) {
							$containerClasses[] = 'ame-is-collapsed-heading';
						}
						$parts[] = sprintf(
							'<div class="%s"><div class="ame-mh-toggle ame-mh-toggle--%s"></div></div>',
							esc_attr(implode(' ', $containerClasses)),
							esc_attr($enumValue)
						);
					}
					$previewHtml = '<div class="ame-mh-toggle-preview-container">' . implode('', $parts) . '</div>';
					$toggleIconPicker->choiceChild($enumValue, new StaticHtml($previewHtml));
				}
			}
		}

		$presetChoices = [new ChoiceControlOption('custom', 'Custom')];
		$presetChoiceSamples = [];
		foreach ($this->getPresets() as $preset) {
			$presetChoices[] = new ChoiceControlOption(
				$preset->getId(),
				$preset->getName()
			);
			$presetChoiceSamples[$preset->getId()] = $b->html(sprintf(
				'<div class="ame-heading-preset-container">
					<div class="ame-menu-heading-item ame-is-collapsed-heading ame-heading-preset--%1$s">
						<a>
							<div class="wp-menu-name">
								<span class="ame-mh-toggle ame-mh-toggle--%3$s"></span>
								<span class="ame-mh-divider ame-mh-divider-l"></span>
								<span class="ame-mh-title">%2$s</span>
								<span class="ame-mh-divider ame-mh-divider-r"></span>
							</div>
						</a>
					</div>
				</div>',
				esc_attr($preset->getId()),
				esc_html('Heading'),
				esc_attr($preset->getToggleIcon())
			));
		}
		$presetPicker = $b->radioCardGroup($settings->getSetting('selectedPreset'))
			->classes('ame-mh-preset-previews')
			->params([
				'choices'        => $presetChoices,
				'choiceChildren' => $presetChoiceSamples,
			]);

		$structure = $b->structure(
			$b->section(
				'Presets',
				$presetPicker->asGroup()->params(['fullWidth' => true])
			),
			$b->section(
				'Collapsible Sections',
				$b->auto('collapsibleSections.enabled')->asGroup()->classes('ame-tp-control-group-medium'),
				$toggleIconPicker,
				$b->auto('collapsibleSections.iconPosition'),
				$b->auto('collapsibleSections.iconPadding')
			),
			$b->section(
				'Title',
				$b->auto('titles.alignment')
					->asGroup()
					->classes('ame-tp-control-group-medium')
					->add($b->html(sprintf(
						'<span class="description ame-description">%s</span>',
						esc_html(
							'Note: The "Left" and "Right" options automatically hide the corresponding divider.'
						)
					))),
				$b->toggleCheckBox('titles.width')
					->onValue('grow')->offValue('auto')
					->label('Expand to fill available space')
					->asGroup('Width'),
				$b->auto('titles.opacity')->params(['step' => 0.05]),
				$b->auto('titles.font'),
				$b->auto('titles.colors'),
				$b->section('Spacing',
					$b->auto('titles.margin'),
					$b->auto('titles.padding'),
					$b->auto('titles.border')
				)
			),
			$b->section(
				'Container',
				$b->auto('containers.colors'),
				$b->section('Spacing',
					$b->auto('containers.paddingType'),
					$b->auto('containers.padding')->enabled(new SettingCondition(
						$settings->getSetting('containers.paddingType'),
						'==',
						'custom'
					)),
					$b->auto('containers.margin'),
				),
				$b->auto('containers.border'),
			),
			$b->section(
				'Dividers',
				$b->group(
					'Show dividers',
					$b->auto('dividers.leftDividerEnabled'),
					$b->auto('dividers.rightDividerEnabled')
				)->stacked(),
				$b->auto('dividers.colors'),
				$b->section(
					'Line',
					$b->auto('dividers.style'),
					$b->auto('dividers.thickness')
				)
			),
			$b->section(
				'Miscellaneous',
				$b->auto('iconVisibility'),
				$b->auto('removeHoverBar')->asGroup()->classes('ame-tp-control-group-medium'),
			),
		);

		return $structure->build();
	}


	public function addAuxDataConfig($config): array {
		$config['keys'][HeadingStyleSettings::CONFIG_KEY] = HeadingStyleSettings::SETTING_ID_PREFIX;
		return $config;
	}

	public function addEditorDependencies($dependencies) {
		$scriptData = [
			'defaults'            => $this->getSettings()->getRecursiveDefaultsForJs(),
			'configKey'           => HeadingStyleSettings::CONFIG_KEY,
			'settingIdPrefix'     => HeadingStyleSettings::SETTING_ID_PREFIX,
			'readAliases'         => HeadingStyleSettings::getReadAliases(),
			'presets'             => $this->getPresets(),
			'presetStylesheetUrl' => $this->getPresetStylesheet()->getUrl(),
		];

		$useBundles = defined('WS_AME_USE_BUNDLES') && WS_AME_USE_BUNDLES;
		if ( $useBundles ) {
			$script = $this->menuEditor->get_webpack_registry()->getWebpackEntryPoint('menu-headings-ui');
		} else {
			$script = ScriptDependency::create(
				plugins_url('menu-headings-ui.js', __FILE__),
				'ame-menu-headings-ui-js',
				__DIR__ . '/menu-headings-ui.js'
			)
				->addDependencies('ame-customizable-settings')
				->setTypeToModule();
		}

		$script
			->addDependencies(
				'jquery',
				'jquery-ui-dialog',
				'wp-color-picker',
				'ame-lodash',
				'ame-knockout',
				'ame-mini-functional-lib'
			)
			->addJsVariable('ameMenuHeadingConfig', $scriptData)
			->register();

		$dependencies[] = $script->getHandle();

		return $dependencies;
	}

	public function enqueueEditorStyles() {
		wp_enqueue_auto_versioned_style(
			'ame-menu-heading-editor-css',
			plugins_url('menu-headings-editor.css', __FILE__)
		);
	}

	public function outputUiTemplate() {
		$structure = $this->getInterfaceStructure();
		$renderer = new TabbedPanelRenderer(['ame-tp-height-100']);

		require __DIR__ . '/menu-headings-template-next.php';

		$renderer->enqueueDependencies();
	}

	private function getStyleGenerator(
		HeadingStyleSettings $s,
		string               $baseSelector = '#adminmenu li.ame-menu-heading-item.ame-menu-heading-item'
	): StyleGenerator {
		$g = new StyleGenerator();

		//Link element
		$g->addRuleSet(
			[$baseSelector . ' > a'],
			[
				//Normal cursor by default, changes to pointer if settings are collapsible.
				'cursor'           => 'default',
				'color'            => $s->getSetting('titles.colors.textBase'),
				'background-color' => $s->getSetting('containers.colors.backgroundBase'),
				$s->getSetting('titles.font'),
				$s->getSetting('containers.border'),
			]
		);

		//region Container spacing
		$g->addCondition(
			$g->ifLooselyEqual($s->getSetting('containers.paddingType'), 'custom'),
			new CssRuleSet(
				[$baseSelector . ' .wp-menu-name'],
				[$s->getSetting('containers.padding')]
			)
		);

		$g->addRuleSet(
			[$baseSelector],
			[$s->getSetting('containers.margin')]
		);

		//Auto left padding if the menu icon is hidden.
		//It varies depending on whether the expand/collapse icon is enabled.
		$hasCollapsibleIconOnTheLeft = $g->ifAll([
			$s->getSetting('collapsibleSections.enabled'),
			$g->compare($s->getSetting('collapsibleSections.icons'), '!=', 'none'),
			$g->compare($s->getSetting('collapsibleSections.iconPosition'), '==', 'left'),
		]);
		$g->addCondition(
			$g->ifAll([
				$g->ifLooselyEqual($s->getSetting('containers.paddingType'), 'auto'),
				$g->compare($s->getSetting('iconVisibility'), '!=', 'always'),
			]),
			//No padding if there's a collapsible icon. The icon should be close to the left edge.
			//This won't look great with all icons since they have different dimensions and spacing,
			//but the user can adjust the padding manually if needed.
			$g->condition(
				$hasCollapsibleIconOnTheLeft,
				new CssRuleSet(
					[$baseSelector . ' .wp-menu-name'],
					['padding-left' => '0']
				)
			),
			//Otherwise, add some padding to compensate for the missing menu icon.
			$g->condition(
				$hasCollapsibleIconOnTheLeft->not(),
				new CssRuleSet(
					[$baseSelector . ' .wp-menu-name'],
					['padding-left' => '8px'] //Note: Was 9px in a previous version.
				)
			)
		);
		//endregion

		//region Title
		$g->addRuleSet(
			[$baseSelector . ' > a .ame-mh-title'],
			[
				$s->getSetting('titles.padding'),
				$s->getSetting('titles.margin'),
				$s->getSetting('titles.border'),
				'opacity'          => $s->getSetting('titles.opacity'),
				'background-color' => $s->getSetting('titles.colors.backgroundBase'),
				'flex-grow'        => $g->ifLooselyEqual($s->getSetting('titles.width'), 'grow', '1', '0'),
			]
		);

		//Title alignment is implemented by using auto margins to push the title.
		//However, if the corresponding divider is enabled, we don't need to do that because the divider
		//will grow to take up the available space and push the title that way.
		$g->addCondition(
			$g->ifLooselyEqual($s->getSetting('titles.alignment'), 'left'),
			$g->condition(
				$g->ifFalsy($s->getSetting('dividers.rightDividerEnabled')),
				new CssRuleSet(
					[$baseSelector . ' > a .ame-mh-title'],
					[
						'margin-right' => 'auto',
						'text-align'   => 'left',
					]
				)
			),
			//Hide the left divider if the title is aligned to the left, since it would prevent the title
			//from reaching the left edge of the heading.
			new CssRuleSet(
				[$baseSelector . ' > a .ame-mh-divider-l'],
				['display' => 'none']
			)
		);

		$g->addCondition(
			$g->ifLooselyEqual($s->getSetting('titles.alignment'), 'right'),
			$g->condition(
				$g->ifFalsy($s->getSetting('dividers.leftDividerEnabled')),
				new CssRuleSet(
					[$baseSelector . ' > a .ame-mh-title'],
					[
						'margin-left' => 'auto',
						'text-align'  => 'right',
					]
				)
			),
			new CssRuleSet(
				[$baseSelector . ' > a .ame-mh-divider-r'],
				['display' => 'none']
			)
		);

		//For center alignment, we need to do this for both sides.
		$g->addCondition(
			$g->ifLooselyEqual($s->getSetting('titles.alignment'), 'center'),
			$g->ruleset(
				[$baseSelector . ' > a .ame-mh-title'],
				$g->condition(
					$g->ifFalsy($s->getSetting('dividers.leftDividerEnabled')),
					['margin-left' => 'auto']
				),
				$g->condition(
					$g->ifFalsy($s->getSetting('dividers.rightDividerEnabled')),
					['margin-right' => 'auto']
				),
				['text-align' => 'center']
			)
		);
		//endregion

		//region Menu icon
		$g->addRuleSet(
			[$baseSelector . ' .wp-menu-image'],
			['color' => $s->getSetting('titles.colors.textBase')]
		);
		$g->addCondition(
			$g->compare($s->getSetting('iconVisibility'), '!=', 'always'),
			new CssRuleSet(
				[$baseSelector . ' .wp-menu-image'],
				['display' => 'none']
			)
		);

		//Menu icon when the menu is collapsed.
		$g->addSimpleCondition(
			$s->getSetting('iconVisibility'),
			'==',
			'if-collapsed',
			new CssRuleSet(
				['body.folded ' . $baseSelector . ' .wp-menu-image'],
				['display' => 'unset']
			)
		);
		//The menu will also collapse below a certain screen width.
		$g->addMediaQuery(
			$g->ifLooselyEqual($s->getSetting('iconVisibility'), 'if-collapsed'),
			'screen and (max-width: 960px) and (min-width: 783px)',
			new CssRuleSet(
				['.auto-fold ' . $baseSelector . ' .wp-menu-image'],
				['display' => 'unset']
			)
		);
		//endregion

		//region Toggle icons
		$g->addRuleSet(
			[$baseSelector . ' .wp-menu-name .ame-mh-toggle'],
			[$s->getSetting('collapsibleSections.iconPadding')]
		);
		//By default, the icon is on the left. Move it to the end if the position is set to "right".
		$g->addSimpleCondition(
			$s->getSetting('collapsibleSections.iconPosition'),
			'==',
			'right',
			new CssRuleSet(
				[$baseSelector . ' .wp-menu-name .ame-mh-toggle'],
				['order' => 10]
			)
		);
		//Hide the icon if collapsible sections are disabled.
		//This won't be necessary for the actual menu, but it matters for the preset previews in the settings UI
		//because they use fixed HTML that always includes the icon element.
		$g->addCondition(
			$g->ifFalsy($s->getSetting('collapsibleSections.enabled')),
			new CssRuleSet(
				[$baseSelector . ' .wp-menu-name .ame-mh-toggle'],
				['display' => 'none']
			)
		);
		//endregion

		//region Divider styles
		$dividerBaseColor = $g->firstNonEmpty([
			$s->getSetting('dividers.colors.base'),
			$g->ifLooselyEqual($s->getSetting('dividers.style'), 'none', 'transparent', 'currentColor'),
		]);
		$dividerHoverColor = $g->firstNonEmpty([
			$s->getSetting('dividers.colors.hover'),
			$dividerBaseColor,
		]);

		$g->addCondition(
			$g->ifSome([
				$s->getSetting('dividers.leftDividerEnabled'),
				$s->getSetting('dividers.rightDividerEnabled'),
			]),
			(new CssRuleSet(
				[$baseSelector . ' .wp-menu-name .ame-mh-divider'],
				[
					'display'          => 'block',
					'background-color' => $dividerBaseColor,
					'border-color'     => $dividerBaseColor,
				]
			))->noSkipOwnDeclarations(),
			new CssRuleSet(
				[
					$baseSelector . ':hover > a .ame-mh-divider',
					$baseSelector . ':active > a .ame-mh-divider',
					$baseSelector . ':focus > a .ame-mh-divider',
					$baseSelector . ' > a:hover .ame-mh-divider',
					$baseSelector . '.menu-top > a:active .ame-mh-divider',
					$baseSelector . '.menu-top > a:focus .ame-mh-divider',
					$baseSelector . '.opensub > a.menu-top .ame-mh-divider',
				],
				[
					'background-color' => $dividerHoverColor,
					'border-color'     => $dividerHoverColor,
				]
			),
			new CssRuleSet(
				[$baseSelector . ' .wp-menu-name .ame-mh-divider'],
				[
					'border-top-width' => $g->cssValue($s->getSetting('dividers.thickness')),
					'border-top-style' => $g->cssValue($s->getSetting('dividers.style')),
				]
			)
		);

		$g->addCondition(
			$g->ifFalsy($s->getSetting('dividers.leftDividerEnabled')),
			new CssRuleSet(
				[$baseSelector . ' > a .ame-mh-divider.ame-mh-divider-l'],
				['display' => 'none']
			)
		);
		$g->addCondition(
			$g->ifFalsy($s->getSetting('dividers.rightDividerEnabled')),
			new CssRuleSet(
				[$baseSelector . ' > a .ame-mh-divider.ame-mh-divider-r'],
				['display' => 'none']
			)
		);
		//endregion

		//region Hover styles
		$hoverSelectors = [
			$baseSelector . ':hover',
			$baseSelector . ':active',
			$baseSelector . ':focus',
			$baseSelector . ' > a:hover',
			$baseSelector . '.menu-top > a:active',
			$baseSelector . '.menu-top > a:focus',
			$baseSelector . '.opensub > a.menu-top',
		];
		//The background colour is transparent by default, even when no custom colours are set.
		//This needs to be a separate ruleset because rulesets that use settings won't generate
		//any declarations when all of those settings are empty.
		$g->addRuleSet($hoverSelectors, ['background-color' => 'transparent']);
		$g->addRuleSet(
			$hoverSelectors,
			[
				'color'            => $g->firstNonEmpty([
					$s->getSetting('titles.colors.textHover'),
					$s->getSetting('titles.colors.textBase'),
				]),
				'background-color' => $g->firstNonEmpty([
					$s->getSetting('containers.colors.backgroundHover'),
					$s->getSetting('containers.colors.backgroundBase'),
					'transparent',
				]),
			]
		);

		//Menu icon hover styles.
		$g->addRuleSet(
			[
				$baseSelector . ':hover div.wp-menu-image::before',
				$baseSelector . ' > a:focus div.wp-menu-image::before',
				$baseSelector . '.opensub div.wp-menu-image::before',
			],
			[
				'color' => $g->firstNonEmpty([
					$s->getSetting('titles.colors.textHover'),
					$s->getSetting('titles.colors.textBase'),
				]),
			]
		);

		//Hover styles specifically for the inner title.
		$g->addRuleSet(
			[
				$baseSelector . ':hover > a .ame-mh-title',
				$baseSelector . ':active > a .ame-mh-title',
				$baseSelector . ':focus > a .ame-mh-title',
				$baseSelector . ' > a:hover .ame-mh-title',
				$baseSelector . '.menu-top > a:active .ame-mh-title',
				$baseSelector . '.menu-top > a:focus .ame-mh-title',
				$baseSelector . '.opensub > a.menu-top .ame-mh-title',
			],
			[
				'background-color' => $g->firstNonEmpty([
					$s->getSetting('titles.colors.backgroundHover'),
					$s->getSetting('titles.colors.backgroundBase'),
				]),
			]
		);
		//endregion

		//Pointer cursor for collapsible headings.
		$g->addRuleSet(
			[$baseSelector . '.ame-collapsible-heading > a'],
			['cursor' => 'pointer']
		);

		//Override the 34px min-height set in /wp-admin/css/admin-menu.css if the actual heading
		//could be smaller than that. I'm not sure if it's safe to do that always, so let's only
		//unset it if the user has changed the font size or padding.
		$g->addCondition(
			$g->ifSome([
				$g->ifAll([
					$s->getSetting('titles.font.size'),
					$g->compare($s->getSetting('titles.font.size'), '<', 14),
				]),
				$g->ifLooselyEqual($s->getSetting('containers.paddingType'), 'custom'),
			]),
			new CssRuleSet(
				[$baseSelector . '.menu-top'],
				['min-height' => 'unset']
			)
		);

		//Optionally, remove the hover bar.
		$g->addCondition(
			$g->ifTruthy($s->getSetting('removeHoverBar')),
			new CssRuleSet(
				[$baseSelector . ' > a'],
				['box-shadow' => 'none']
			)
		);

		return $g;
	}

	/**
	 * @return Preset[]
	 */
	private function getPresets(): array {
		return [
			new Preset(
				'subtle',
				'Subtle',
				[
					'titles'              => [
						'font'    => [
							'size'              => 13,
							'sizeUnit'          => 'px',
							'text-transform'    => 'uppercase',
							//'variant' => 'small-caps',
							'letter-spacing'    => 1,
							'letterSpacingUnit' => 'px',
						],
						'opacity' => 0.8,
						'width'   => 'grow',
					],
					'dividers'            => [
						'leftDividerEnabled'  => false,
						'rightDividerEnabled' => false,
					],
					'collapsibleSections' => [
						'enabled' => false,
						'icons'   => 'none',
					],
				]
			),

			new Preset(
				'divided',
				'Divided',
				[
					'titles'     => [
						'font'      => [
							'size'     => 13,
							'sizeUnit' => 'px',
						],
						'width'     => 'auto',
						'alignment' => 'center',
						'border'    => [
							'style'  => 'solid',
							'width'  => ['top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1],
							'radius' => ['topLeft' => 4, 'topRight' => 4, 'bottomRight' => 4, 'bottomLeft' => 4],
						],
						'padding'   => ['left' => 6, 'right' => 6, 'top' => 1, 'bottom' => 1],
					],
					'containers' => [
						'paddingType' => 'custom',
						'padding'     => ['left' => 0, 'right' => 0],
					],
					'dividers'   => [
						'leftDividerEnabled'  => true,
						'rightDividerEnabled' => true,
					],
				]
			),

			new Preset(
				'pill',
				'Pill',
				[
					'titles'   => [
						'font'      => [
							'size'     => 13,
							'sizeUnit' => 'px',
						],
						'width'     => 'auto',
						'alignment' => 'center',
						'border'    => [
							'radius' => ['topLeft' => 8, 'topRight' => 8, 'bottomRight' => 8, 'bottomLeft' => 8],
						],
						'padding'   => ['left' => 8, 'right' => 8, 'bottom' => 1],
						'colors'    => [
							'textBase'       => '#f8f9f9',
							'backgroundBase' => '#008a20',
						],
					],
					'dividers' => [
						'leftDividerEnabled'  => false,
						'rightDividerEnabled' => false,
					],
				]
			),

			new Preset(
				'banner',
				'Banner',
				[
					'titles'              => [
						'font'      => [
							//'size'     => 13,
							'sizeUnit' => 'px',
							'variant'  => 'small-caps',
						],
						'width'     => 'grow',
						'alignment' => 'left',
						'colors'    => [
							'textBase' => '#f8f9f9',
						],
					],
					'containers'          => [
						'colors' => [
							'backgroundBase' => '#007017',
						],
					],
					'collapsibleSections' => [
						'enabled'      => true,
						'icons'        => 'widget-arrows',
						'iconPosition' => 'right',
					],
				],
			),

			new Preset(
				'underlined',
				'Underlined',
				[
					'titles'              => [
						'font'      => [
							'size'     => 14,
							'sizeUnit' => 'px',
							'weight'   => '400',
						],
						'width'     => 'grow',
						'alignment' => 'left',
						'border'    => [
							'style' => 'solid',
							'width' => ['bottom' => 1, 'top' => 0, 'left' => 0, 'right' => 0],
						],
					],
					'containers'          => [
						'paddingType' => 'custom',
						'padding'     => ['left' => 0],
					],
					'collapsibleSections' => [
						'enabled'      => true,
						'icons'        => 'widget-arrows-alt',
						'iconPosition' => 'left',
					],
					'removeHoverBar'      => true,
				],
			),
		];
	}

	private function generatePresetCss(Preset $preset): string {
		$baseSelector = '.ame-heading-preset-container .ame-heading-preset--' . $preset->getId();

		$presetSettings = new HeadingStyleSettings(new LazyArrayStorage($preset->getSettings()));
		$generator = $this->getStyleGenerator($presetSettings, $baseSelector);

		return $generator->generateCss();
	}

	private ?Stylesheet $presetStylesheet = null;

	private function getPresetStylesheet(): Stylesheet {
		if ( $this->presetStylesheet ) {
			return $this->presetStylesheet;
		}

		$this->presetStylesheet = new Stylesheet(
			'ame-menu-heading-presets',
			function () {
				$parts = [];
				foreach ($this->getPresets() as $preset) {
					$parts[] = $this->generatePresetCss($preset);
				}
				return implode("\n", $parts);
			},
			function () {
				$myModificationTime = filemtime(__FILE__);
				if ( $myModificationTime === false ) {
					return strtotime('2026-03-25 00:00:00');
				}
				return $myModificationTime;
			}
		);
		return $this->presetStylesheet;
	}

	/**
	 * Create/filter an admin menu item that represents a menu heading.
	 *
	 * Receives the underlying menu item in the internal format, and should return a modified item.
	 *
	 * @param array $item
	 * @return array
	 */
	public function createHeadingMenuItem(array $item): array {
		$settings = $this->getSettings();

		//Mark headings as collapsible if that feature is enabled.
		$areHeadingsCollapsible = $settings->getSetting('collapsibleSections.enabled')->getValue();
		if ( $areHeadingsCollapsible ) {
			$item['css_class'] .= ' ame-collapsible-heading';
		}

		//Wrap the title in an extra element for styling.
		$title = sprintf('<span class="ame-mh-title">%s</span>', $item['menu_title']);

		//Add extra elements for dividers and expand/collapse icons.
		//Currently, dividers are always added and shown/hidden via CSS.
		$title = '<span class="ame-mh-divider ame-mh-divider-l"></span>'
			. $title
			. '<span class="ame-mh-divider ame-mh-divider-r"></span>';


		$toggleIconName = $settings->getSetting('collapsibleSections.icons')->getValue();
		if ( ($toggleIconName !== 'none') && $toggleIconName && $areHeadingsCollapsible ) {
			$title = sprintf(
					'<span class="ame-mh-toggle ame-mh-toggle--%s"></span>',
					esc_attr($toggleIconName)
				) . $title;
		}

		$item['menu_title'] = $title;
		return $item;
	}

	public function registerMenuHeadingStylesheet() {
		$helper = MenuScopedStylesheetHelper::getInstance($this->menuEditor);
		$helper->addStylesheet(
			'ame-menu-headings',
			function ($menuConfigId) {
				$settings = $this->getSettings($menuConfigId);

				$modTimeCallback = function () use ($settings) {
					$modificationTime = $settings->get('modificationTimestamp');
					return !empty($modificationTime) ? $modificationTime : 0;
				};

				$styleGenerationCallback = function () use ($settings) {
					$styleGenerator = $this->getStyleGenerator($settings);
					return $styleGenerator->generateCss();
				};

				return [$modTimeCallback, $styleGenerationCallback];
			},
			'ame-menu-style-bundle',
			//This stylesheet should go before the ame-custom-menu-colors stylesheet to make it
			//easier to override heading colors for individual items using the "Color scheme" field.
			//(Both use selectors with the same specificity, so the order of the stylesheets will
			//determine which one takes precedence.)
			5
		);
	}

	public function outputRestorationTrigger() {
		//Note: Heading state will still get restored even if this trigger is not present,
		//but it will happen later during the page load, which might cause hidden menus
		//to be briefly visible before they get hidden.
		?>
		<script type="text/javascript">
			if (jQuery && document) {
				jQuery(document).trigger('restoreCollapsedHeadings.adminMenuEditor');
			}
		</script>
		<?php
	}
}

class HeadingStyleSettings extends AbstractSettingsDictionary {
	const CONFIG_KEY = 'menu_headings';
	const SETTING_ID_PREFIX = 'ws_heading_styles--';

	public function __construct(StorageInterface $store) {
		parent::__construct($store, self::SETTING_ID_PREFIX);
		$this->addReadAliases(self::getReadAliases());
	}

	protected function createDefaults(): array {
		return [];
	}

	protected function createSettings(): array {
		$f = $this->settingFactory();
		$s = new SchemaFactory();

		$fontSchema = $s->cssFont('Font');
		$fontSchema->getFieldShema('size')->defaultValue(14);

		return $f->buildSettings([
			'iconVisibility' => $s->enum(['always', 'never', 'if-collapsed'], 'Show the icon')
				->defaultValue('if-collapsed')
				->describeValue('if-collapsed', 'Only when the admin menu is collapsed'),
			'removeHoverBar' => $s->boolean('Remove the hover bar')->defaultValue(true)
				->settingParams([
					'groupTitle'  => 'Hover bar',
					'description' => 'The hover bar is the thin highlight that appears on the left edge of menu items when you hover over them.',
				]),
			'selectedPreset' => $s->string()->defaultValue('custom')->max(50),

			'collapsibleSections' => $s->struct([
				'enabled'      => $s->boolean('Make headings act as collapsible sections')
					->defaultValue(false)
					->settingParams([
						'groupTitle'  => 'Enabled',
						'description' => 'Clicking on a heading will show/hide the menu items below that heading.',
					]),
				'icons'        => $s->enum(
					[
						'none',
						'widget-arrows',
						'widget-arrows-alt',
						'black-triangles',
						'small-black-triangles',
						'white-triangles',
						'small-white-triangles',
						'heavy-arrow',
						'single-chevron',
						'double-chevron',
						'plus-minus',
						'circled-plus-minus',
						'squared-plus-minus',
						'hamburger-close',
					],
					'Expand/collapse icons'
				)
					->defaultValue('none')
					->describeValue('none', 'No icons')
					->describeValue('widget-arrows', "Widget arrows")
					->describeValue('widget-arrows-alt', "Widget arrows (alt)")
					->describeValue('black-triangles', "Triangles")
					->describeValue('small-black-triangles', "Small triangles")
					->describeValue('white-triangles', "Empty triangles")
					->describeValue('small-white-triangles', "Small empty triangles")
					->describeValue('heavy-arrow', "Heavy arrow")
					->describeValue('single-chevron', "Single chevron")
					->describeValue('double-chevron', "Double chevron")
					->describeValue('plus-minus', "Plus/minus")
					->describeValue('circled-plus-minus', "Circled plus/minus")
					->describeValue('squared-plus-minus', "Squared plus/minus")
					->describeValue('hamburger-close', "Hamburger"),
				'iconPosition' => $s->enum(['left', 'right'], 'Icon position')->defaultValue('left')
					->describeValue('left', 'Left edge')
					->describeValue('right', 'Right edge'),
				'iconPadding'  => $s->cssPadding('Icon padding'),
			], 'Collapsible sections'),

			'titles' => $s->struct([
				'alignment' => $s->enum(['left', 'center', 'right'], 'Alignment')
					->defaultValue('left'),
				'width'     => $s->enum(['auto', 'grow'], 'Width')->defaultValue('auto')
					->describeValue('auto', 'Auto')
					->describeValue('grow', 'Expand to fill available space'),
				'opacity'   => $s->number('Opacity')->min(0)->max(1)->defaultValue(1),
				'font'      => $fontSchema,
				'colors'    => $s->struct([
					//Note: textColorType is not used in this implementation, and the default vs custom
					//state will need to be determined by checking if the textBase colour is non-empty.
					'textBase'        => $s->cssColor('Text'),
					'backgroundBase'  => $s->cssColor('Background'),
					'textHover'       => $s->cssColor('Text (hover)'),
					'backgroundHover' => $s->cssColor('Background (hover)'),
				], 'Colors'),
				'margin'    => $s->cssMargin(),
				'padding'   => $s->cssPadding(),
				'border'    => $s->cssBorders(['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0]),
			], 'Title'),

			'containers' => $s->struct([
				'colors'      => $s->struct([
					//Note: backgroundColorType is also not used in this implementation.
					'backgroundBase'  => $s->cssColor('Background'),
					'backgroundHover' => $s->cssColor('Background (hover)'),
				], 'Colors'),
				'paddingType' => $s->enum(['auto', 'custom'], 'Padding mode')->defaultValue('auto'),
				'padding'     => $s->cssPadding(),
				'margin'      => $s->cssMargin(),
				'border'      => $s->cssBorders(),
			], 'Container'),

			'dividers' => $s->struct([
				'leftDividerEnabled'  => $s->boolean('Left divider')->defaultValue(false),
				'rightDividerEnabled' => $s->boolean('Right divider')->defaultValue(false),
				'colors'              => $s->struct([
					'base'  => $s->cssColor('Normal'),
					'hover' => $s->cssColor('Hover'),
				], 'Colors'),
				'style'               => $s->cssBorderStyle('Line style', false)->defaultValue('solid'),
				'thickness'           => $s->cssLength('Line thickness')->int()->min(0)->max(20)->defaultValue(1)
					->settingParams(['defaultUnit' => 'px']),
			], 'Dividers'),

			'modificationTimestamp' => $s->int()->min(0)->defaultValue(0),
		]);
	}

	public static function getReadAliases(): array {
		return [
			'collapsibleSections.enabled' => 'collapsible',

			'titles.font.size'           => 'fontSizeValue',
			'titles.font.sizeUnit'       => 'fontSizeUnit',
			'titles.font.weight'         => 'fontWeight',
			'titles.font.family'         => 'fontFamily',
			'titles.font.text-transform' => 'textTransform',

			'titles.colors.textBase'           => 'textColor',
			'containers.colors.backgroundBase' => 'backgroundColor',

			'titles.border.style'        => 'bottomBorder.style',
			'titles.border.width.bottom' => 'bottomBorder.width',
			'titles.border.color'        => 'bottomBorder.color',

			'containers.paddingType'    => 'paddingType',
			'containers.padding.top'    => 'paddingTop',
			'containers.padding.right'  => 'paddingRight',
			'containers.padding.bottom' => 'paddingBottom',
			'containers.padding.left'   => 'paddingLeft',

			'containers.margin.top'    => 'marginTop',
			'containers.margin.right'  => 'marginRight',
			'containers.margin.bottom' => 'marginBottom',
			'containers.margin.left'   => 'marginLeft',
		];
	}
}

class Preset implements \JsonSerializable {
	private string $id;
	private string $name;
	private array $settings;

	public function __construct(string $id, string $name, array $settings) {
		$this->id = $id;
		$this->name = $name;
		$this->settings = $settings;
	}

	public function getId(): string {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getSettings(): array {
		return $this->settings;
	}

	public function getToggleIcon(): string {
		return \ameUtils::get($this->settings, 'collapsibleSections.icons', 'none');
	}

	public function jsonSerialize(): array {
		return [
			'id'       => $this->id,
			'settings' => (object)$this->settings,
		];
	}
}
