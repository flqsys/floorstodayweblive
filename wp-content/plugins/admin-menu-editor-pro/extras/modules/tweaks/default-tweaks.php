<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks;

use YahnisElsts\AdminMenuEditor\Tweaks\Core\ameDisableRemotePatternsTweak;
use YahnisElsts\AdminMenuEditor\Tweaks\Core\ameEnvironmentColorTweak;
use YahnisElsts\AdminMenuEditor\Tweaks\Core\ameEnvironmentNameTweak;
use YahnisElsts\AdminMenuEditor\Tweaks\Core\ameUnregisterPatternsTweak;

$result = [
	'sections' => [
		'gutenberg-general' => ['label' => 'Gutenberg (Block Editor)', 'priority' => 25],
		'environment-type'  => ['label' => 'Environment Type', 'priority' => 30],
		'plugins-page'      => ['label' => 'Plugins Page', 'priority' => 35],
	],

	'tweaks' => [
		'hide-screen-meta-links' => [
			'label'            => 'Hide screen meta links',
			'selector'         => '#screen-meta-links',
			'hideableLabel'    => 'Screen meta links',
			'hideableCategory' => 'admin-ui',
		],
		'hide-screen-options'    => [
			'label'            => 'Hide the "Screen Options" button',
			'selector'         => '#screen-options-link-wrap',
			'parent'           => 'hide-screen-meta-links',
			'hideableLabel'    => '"Screen Options" button',
			'hideableCategory' => 'admin-ui',
		],
		'hide-help-panel'        => [
			'label'            => 'Hide the "Help" button',
			'selector'         => '#contextual-help-link-wrap',
			'parent'           => 'hide-screen-meta-links',
			'hideableLabel'    => '"Help" button',
			'hideableCategory' => 'admin-ui',
		],
		'hide-all-admin-notices' => [
			'label'            => 'Hide ALL admin notices',
			'selector'         => '#wpbody-content .notice, #wpbody-content .updated, #wpbody-content .update-nag',
			'hideableLabel'    => 'All admin notices',
			'hideableCategory' => 'admin-ui',
		],

		'hide-gutenberg-options'    => [
			'label'         => 'Hide the Gutenberg options menu (three vertical dots)',
			'selector'      => '#editor .edit-post-header__settings .edit-post-more-menu,'
				//WP 6.x
				. ' #editor .edit-post-header__settings .interface-more-menu-dropdown,'
				//WP 6.7.1
				. ' #editor .editor-header__settings .components-dropdown-menu:not(.editor-preview-dropdown):last-child',
			'section'       => 'gutenberg-general',
			'hideableLabel' => 'Gutenberg options menu',
		],
		'hide-gutenberg-fs-wp-logo' => [
			'label'         => 'Hide the WordPress logo in Gutenberg fullscreen mode',
			'selector'      => '#editor .edit-post-header a.components-button[href^="edit.php"]',
			'section'       => 'gutenberg-general',
			'hideableLabel' => 'WordPress logo in Gutenberg fullscreen mode',
		],

		'show-environment-in-toolbar'  => [
			'label'     => 'Show environment type in the Toolbar',
			'section'   => 'environment-type',
			'className' => ameEnvironmentNameTweak::class,
		],
		'environment-dependent-colors' => [
			'label'     => 'Change menu color depending on the environment',
			'section'   => 'environment-type',
			'className' => ameEnvironmentColorTweak::class,
		],

		'hide-inserter-media-tab' => [
			'label'         => 'Hide the "Media" tab in the block inserter',
			'selector'      => implode(', ', [
				'#editor #tab-panel-0-media',
				//It appears that the tab IDs vary from site to site, and may depend on the order in
				//which the tabs were created/opened. So we try to target multiple versions. Unfortunately,
				//there doesn't seem to be a concise way to target specific block inserter tabs. They
				//don't have any unique classes or data attributes.
				'#editor .editor-inserter-sidebar #tabs-1-media',
				'#editor .editor-inserter-sidebar #tabs-2-media',
				'#editor .editor-inserter-sidebar #tabs-3-media',
				'#editor .editor-inserter-sidebar #tabs-4-media',
			]),
			'section'       => 'gutenberg-general',
			'hideableLabel' => '"Media" tab in the block inserter',
		],

		'hide-block-patterns'        => [
			'label'         => 'Hide block patterns',
			'isGroup'       => true,
			'section'       => 'gutenberg-general',
			'hideableLabel' => 'Block patterns',
		],
		'hide-patterns-tab-with-css' => [
			'label'         => 'Hide the "Patterns" tab in the block inserter',
			'selector'      => implode(', ', [
				'#editor #tab-panel-0-patterns',
				'#editor .editor-inserter-sidebar #tabs-1-patterns',
				'#editor .editor-inserter-sidebar #tabs-2-patterns',
				'#editor .editor-inserter-sidebar #tabs-3-patterns',
				'#editor .editor-inserter-sidebar #tabs-4-patterns',
			]),
			'parent'        => 'hide-block-patterns',
			'section'       => 'gutenberg-general',
			'hideableLabel' => '"Patterns" tab in the block inserter',
		],
		'disable-remote-patterns'    => [
			'label'     => 'Disable remote patterns',
			'className' => ameDisableRemotePatternsTweak::class,
			'parent'    => 'hide-block-patterns',
			'section'   => 'gutenberg-general',
		],
		'unregister-all-patterns'    => [
			'label'     => 'Unregister all visible patterns (Caution: Also affects "Appearance → Editor")',
			'className' => ameUnregisterPatternsTweak::class,
			'parent'    => 'hide-block-patterns',
			'section'   => 'gutenberg-general',
		],

		'disable-gutenberg-welcome-guide' => [
			'label'    => 'Disable the "Welcome to the editor" message',
			'section'  => 'gutenberg-general',
			'callback' => function () {
				add_action('enqueue_block_editor_assets', function () {
					wp_enqueue_script('wp-data');
					wp_enqueue_script('wp-dom-ready');

					/** @noinspection JSUnresolvedReference -- Alas, I don't have the defs for the WP JS APIs. */
					$inlineScript = /** @lang JavaScript */
						<<<EOJS
				        wp.domReady(function() {
				            const {select, dispatch} = wp.data;
				            if (select('core/edit-post').isFeatureActive('welcomeGuide')) {
				                dispatch('core/edit-post').toggleFeature('welcomeGuide');
				            }
				        });
EOJS;

					wp_add_inline_script('wp-dom-ready', $inlineScript, 'after');
				});
			},
		],
		'disable-gutenberg-block-locking' => [
			'label'    => 'Disable block locking and unlocking',
			'section'  => 'gutenberg-general',
			'callback' => function () {
				add_filter('block_editor_settings_all', function ($settings) {
					if ( !is_array($settings) ) {
						return $settings;
					}
					$settings['canLockBlocks'] = false;
					return $settings;
				}, 10, 1);
			},
		],
		'disable-gutenberg-code-editor'   => [
			'label'    => 'Disable access to the Code Editor and "Edit as HTML"',
			'section'  => 'gutenberg-general',
			'callback' => function () {
				add_filter('block_editor_settings_all', function ($settings) {
					if ( !is_array($settings) ) {
						return $settings;
					}
					$settings['codeEditingEnabled'] = false;
					return $settings;
				}, 10, 1);
			},
		],

		'move-active-plugins-to-top' => [
			'label'    => 'Move active plugins to the top of the plugin list',
			'section'  => 'plugins-page',
			'callback' => function () {
				add_filter('plugins_list', function ($plugins = []) {
					if ( empty($plugins) || !is_array($plugins) || empty($plugins['active']) ) {
						return $plugins;
					}

					$activationStateField = 'AME_is_plugin_active';

					//Note: Technically, we don't need to add the field to every subarray - for example, plugins
					//in the "active" array are all active. However, if we leave it out, some later filter can
					//change $status to point to an array that doesn't have this field, which will cause PHP
					//warnings when WP tries to use the field for sorting.

					foreach ($plugins as $status => $items) {
						if ( empty($items) || !is_array($items) ) {
							continue;
						}

						foreach ($items as $pluginFile => $pluginData) {
							if ( !is_array($pluginData) ) {
								continue;
							}

							$isActive = isset($plugins['active'][$pluginFile]);
							/**
							 * WP compares the values as strings, so we need to use a string here.
							 *
							 * @see WP_Plugins_List_Table::_order_callback()
							 */
							$plugins[$status][$pluginFile][$activationStateField] = $isActive ? 'A' : 'N';
						}
					}

					//Override the default sort order.
					global $orderby;
					if ( !$orderby ) {
						//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- It's opt-in and the purpose of this tweak.
						$orderby = $activationStateField;
					}

					return $plugins;
				});
			},
		],

		'plugins-default-to-active-view' => [
			'label'    => 'Select the "Active" filter by default',
			'section'  => 'plugins-page',
			'callback' => function () {
				add_filter('plugins_list', function ($plugins = []) {
					global $status;

					if ( empty($plugins) || !is_array($plugins) || empty($plugins['active']) ) {
						return $plugins;
					}

					//Override the view/status only if it's not already set by the user.
					//The WP_Plugins_List_Table constructor sets $status to "all" by default, so
					//just checking if it's set is not enough; we need to look at the request.
					//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Not processing form data.
					if ( !$status || (($status === 'all') && !isset($_REQUEST['plugin_status'])) ) {
						//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						$status = 'active';
					}
					return $plugins;
				});
			},
		],
	],

	'definitionFactories' => [],
];

if ( isset($twmPlaceholdersEnabled) && $twmPlaceholdersEnabled ) {
	$placeholderTweakDefs = require __DIR__ . '/placeholder-tweaks.php';
	$result['sections'] = array_merge($result['sections'], $placeholderTweakDefs['sections']);
	$result['tweaks'] = array_merge($result['tweaks'], $placeholderTweakDefs['tweaks']);
}

return $result;