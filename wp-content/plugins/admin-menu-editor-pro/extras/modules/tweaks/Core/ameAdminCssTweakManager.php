<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks\Core;
use YahnisElsts\AdminMenuEditor\Tweaks\Core\ameDelegatedTweak;
use YahnisElsts\AdminMenuEditor\Tweaks\Core\ameTweakManager;
use YahnisElsts\AdminMenuEditor\Tweaks\Core\ameAdminCssTweak;

class ameAdminCssTweakManager {
	private $isOutputHookRegistered = false;
	private $pendingCss = array();

	public function __construct() {
		add_action('admin-menu-editor-register_tweaks', array($this, 'registerDefaultTweak'), 10, 1);
	}

	public function enqueueCss($settings = null) {
		if ( ($settings === null) || (empty($settings['css'])) ) {
			return;
		}
		$this->pendingCss[] = $settings['css'];
		if ( !$this->isOutputHookRegistered ) {
			add_action('admin_print_scripts', array($this, 'outputCss'));
			$this->isOutputHookRegistered = true;
		}
	}

	public function outputCss() {
		if ( empty($this->pendingCss) ) {
			return;
		}

		$cssCode = implode("\n", $this->pendingCss);
		//Escape closing tags to prevent breaking out of the style element.
		$cssCode = str_ireplace('</style', '<\/style', $cssCode);

		echo '<!-- Admin Menu Editor: Admin CSS tweaks -->', "\n";
		echo '<style id="ame-admin-css-tweaks">', "\n";
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Minimal escaping is done above.
		echo $cssCode;
		echo "\n", '</style>', "\n";
	}

	/**
	 * Create a CSS tweak instance with the specified properties.
	 *
	 * @param array $properties
	 * @return ameDelegatedTweak
	 */
	public function createTweak($properties) {
		$cssTweak = new ameAdminCssTweak(
			$properties['id'],
			$properties['label'],
			array($this, 'enqueueCss')
		);
		$cssTweak->setSectionId('admin-css');

		return $cssTweak;
	}

	/**
	 * @param ameTweakManager $tweakManager
	 */
	public function registerDefaultTweak($tweakManager) {
		$tweakManager->addSection('admin-css', 'Admin CSS', 20);

		$defaultTweak = $this->createTweak(array(
			'id'    => 'default-admin-css',
			'label' => 'Add custom admin CSS',
		));
		$tweakManager->addTweak($defaultTweak);
	}
}