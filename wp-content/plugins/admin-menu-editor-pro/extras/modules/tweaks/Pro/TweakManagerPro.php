<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks\Pro;

use YahnisElsts\AdminMenuEditor\EasyHide\HideableItemStore;
use YahnisElsts\AdminMenuEditor\Tweaks\Core\ameBaseTweak;
use YahnisElsts\AdminMenuEditor\Tweaks\Core\ameTweakManager;

class TweakManagerPro extends ameTweakManager {
	const HIDEABLE_ITEM_COMPONENT = 'tw';
	const HIDEABLE_ITEM_PREFIX = 'tweaks/';

	public function __construct($menuEditor) {
		parent::__construct($menuEditor);

		//We need to process widgets after they've been registered (usually priority 10)
		//but before WordPress has populated the $wp_registered_widgets global (priority 95 or 100).
		add_action('widgets_init', [$this, 'processSidebarWidgets'], 50);
		//Sidebars are simpler: we can just use a really late priority.
		add_action('widgets_init', [$this, 'processSidebars'], 1000);

		add_action(
			'admin_menu_editor-register_hideable_items',
			function (HideableItemStore $store) {
				$this->getHideableIntegration()->registerHideableItems($store);
			},
			20
		);
		add_filter(
			'admin_menu_editor-save_hideable_items-' . self::HIDEABLE_ITEM_COMPONENT,
			function ($errors, $items) {
				return $this->getHideableIntegration()->saveHideableItems($errors, $items);
			},
			10, 2
		);
	}


	protected function getDefaultTweakFiles(): array {
		$filePaths = parent::getDefaultTweakFiles();
		$filePaths[] = __DIR__ . '/../pro-default-tweaks.php';
		return $filePaths;
	}

	protected function initProFeatures() {
		new ameTinyMceButtonManager();
		new ameMediaRestrictionsManager();
		new ameGutenbergBlockManager($this->menuEditor);
		new ameProfileFieldTweakManager($this->menuEditor);
	}

	public function displaySettingsPage() {
		if (
			class_exists(PlaceholderGenerator::class)
			&& defined('WP_DEBUG') && constant('WP_DEBUG')
			&& defined('AME_TEST_MODE') && constant('AME_TEST_MODE')
		) {
			new PlaceholderGenerator($this);
		}

		parent::displaySettingsPage();
	}

	public function processSidebarWidgets() {
		global $wp_widget_factory;
		global $pagenow;
		if ( !isset($wp_widget_factory, $wp_widget_factory->widgets) || !is_array($wp_widget_factory->widgets) ) {
			return;
		}

		$widgetTweaks = [];
		foreach ($wp_widget_factory->widgets as $widget) {
			$tweak = new ameHideSidebarWidgetTweak($widget);
			$widgetTweaks[$tweak->getId()] = $tweak;
		}

		//Sort the tweaks in alphabetic order.
		uasort(
			$widgetTweaks,
			function (ameBaseTweak $a, ameBaseTweak $b) {
				return strnatcasecmp($a->getLabel(), $b->getLabel());
			}
		);

		foreach ($widgetTweaks as $tweak) {
			$this->addTweak($tweak, self::APPLY_TWEAK_MANUALLY);
		}

		if ( is_admin() && ($pagenow === 'widgets.php') ) {
			$this->processTweaks($widgetTweaks);
		}
	}

	private ?TweakToHideableIntegration $hideableIntegration = null;

	public function processSidebars() {
		global $wp_registered_sidebars;
		global $pagenow;
		if ( !isset($wp_registered_sidebars) || !is_array($wp_registered_sidebars) ) {
			return;
		}

		$sidebarTweaks = [];
		foreach ($wp_registered_sidebars as $sidebar) {
			$tweak = new ameHideSidebarTweak($sidebar);
			$this->addTweak($tweak, self::APPLY_TWEAK_MANUALLY);
			$sidebarTweaks[$tweak->getId()] = $tweak;
		}

		if ( is_admin() && ($pagenow === 'widgets.php') ) {
			$this->processTweaks($sidebarTweaks);
		}
	}

	private function getHideableIntegration(): TweakToHideableIntegration {
		if ( $this->hideableIntegration === null ) {
			$this->hideableIntegration = new TweakToHideableIntegration($this);
		}
		return $this->hideableIntegration;
	}
}