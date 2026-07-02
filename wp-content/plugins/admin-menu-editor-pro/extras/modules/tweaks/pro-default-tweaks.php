<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks\Pro;

use ameMetaBoxEditor, amePluginVisibility, ameWidgetEditor, WPMenuEditor;

$result = [
	'sidebar-widgets'     => ['label' => 'Hide Sidebar Widgets', 'priority' => 100],
	'sidebars'            => ['label' => 'Hide Sidebars', 'priority' => 120],
	'sections'            => [
		'disable-customizations' => [
			'label'       => 'Disable Customizations',
			'priority'    => 200,
			'description' =>
				'You can selectively disable some customizations for a role or user. This means the user'
				. ' will see the default, unmodified version of the thing. It doesn\'t prevent the user'
				. ' from editing the relevant settings.'
				. "\n\n"
				. 'Note: "Default" here only means that AME will leave it unchanged. Other plugins can still make changes.',
		],
	],
	'tweaks'              => [],
	'definitionFactories' => [],
];

//region "Disable Customizations" tweaks

function ws_ame_get_dc_tweak_defs($earlyStage = false): array {
	$dcOptions = [];
	if ( $earlyStage ) {
		$dcOptions[WPMenuEditor::ADMIN_MENU_STRUCTURE_COMPONENT] = [
			'Admin menu content',
			'Disables custom permissions, menu order, user-created items, etc. Does not affect global menu styles.',
		];
	} else {
		//Since we do class_exists() checks here, this code should run after all modules have been
		//loaded, not as early as possible.
		if ( class_exists(amePluginVisibility::class, false) ) {
			$dcOptions[amePluginVisibility::CUSTOMIZATION_COMPONENT] = [
				'Plugin list',
				'Disables custom plugin visibility and custom plugin names/descriptions on the "Plugins" page.',
			];
		}
		if ( class_exists(ameWidgetEditor::class, false) ) {
			$dcOptions[ameWidgetEditor::CUSTOMIZATION_COMPONENT] = [
				'Dashboard widgets',
				'Disables custom widget visibility, layout, titles, and user-created widgets.',
			];
		}
		if ( class_exists(ameMetaBoxEditor::class, false) ) {
			$dcOptions[ameMetaBoxEditor::CUSTOMIZATION_COMPONENT] = [
				'Meta boxes',
				'Disables custom meta box visibility in the post editor.',
			];
		}
	}

	$defs = [];
	foreach ($dcOptions as $component => $texts) {
		list($label, $description) = $texts;
		$defs['disable-custom-' . $component] = [
			'label'              => $label,
			'description'        => $description,
			'componentToDisable' => $component,
			'section'            => 'disable-customizations',
			'factory'            => [ameDisableCustomizationsTweak::class, 'create'],
		];
	}
	return $defs;
}

$result['tweaks'] = array_merge($result['tweaks'], ws_ame_get_dc_tweak_defs(true));

$result['definitionFactories'][] = __NAMESPACE__ . '\\ws_ame_get_dc_tweak_defs';
//endregion

return $result;