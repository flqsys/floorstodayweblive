<?php

use YahnisElsts\AdminMenuEditor\Tweaks\PlaceholderTweak;

//This is in a function so that local variables don't leak into the scope of the file that includes this one.
function ws_tw_placeholder_tweaks(): array {
	//region Auto-generated placeholder definitions

	$definitions = array(
		'sections' =>
			array(
				'plh-media-restrictions'     =>
					array(
						'label'    => 'Media Library Restrictions',
						'priority' => 51,
					),
				'plh-hide-others-posts'      =>
					array(
						'label'    => 'Hide Other Users\' Posts',
						'priority' => 61,
					),
				'plh-gutenberg-blocks'       =>
					array(
						'label'    => 'Hide Gutenberg Blocks',
						'priority' => 71,
					),
				'plh-profile'                =>
					array(
						'label'    => 'Hide Profile Fields',
						'priority' => 81,
					),
				'plh-sidebar-widgets'        =>
					array(
						'label'    => 'Hide Sidebar Widgets',
						'priority' => 101,
					),
				'plh-sidebars'               =>
					array(
						'label'    => 'Hide Sidebars',
						'priority' => 121,
					),
				'plh-tmce-buttons'           =>
					array(
						'label'    => 'Hide TinyMCE Buttons',
						'priority' => 151,
					),
				'plh-disable-customizations' =>
					array(
						'label'    => 'Disable Customizations',
						'priority' => 201,
					),
			),
		'tweaks'   =>
			array(
				'plh-mr-all-container'                             =>
					array(
						'label'   => 'Restrict access to media uploaded by other users',
						'section' => 'plh-media-restrictions',
					),
				'plh-mr-others-delete_post'                        =>
					array(
						'label'   => 'Prevent deletion',
						'section' => 'plh-media-restrictions',
						'parent'  => 'plh-mr-all-container',
					),
				'plh-mr-others-edit_post'                          =>
					array(
						'label'   => 'Prevent editing',
						'section' => 'plh-media-restrictions',
						'parent'  => 'plh-mr-all-container',
					),
				'plh-ame_hide_others_posts-attachment--alias-dddc' =>
					array(
						'label'   => 'Hide other users\' uploads',
						'parent'  => 'plh-mr-all-container',
						'section' => 'plh-media-restrictions',
					),
				'plh-ame_hide_others_posts-post'                   =>
					array(
						'label'   => 'Posts',
						'section' => 'plh-hide-others-posts',
					),
				'plh-ame_hide_others_posts-page'                   =>
					array(
						'label'   => 'Pages',
						'section' => 'plh-hide-others-posts',
					),
				'plh-ame_hide_others_posts-attachment'             =>
					array(
						'label'   => 'Media',
						'section' => 'plh-hide-others-posts',
					),
				'plh-gtb-block-section-text'                       =>
					array(
						'label'   => 'Text',
						'section' => 'plh-gutenberg-blocks',
					),
				'plh-hide-gtb-core/paragraph'                      =>
					array(
						'label'   => 'Paragraph',
						'section' => 'plh-gutenberg-blocks',
						'parent'  => 'plh-gtb-block-section-text',
					),
				'plh-hide-gtb-core/heading'                        =>
					array(
						'label'   => 'Heading',
						'section' => 'plh-gutenberg-blocks',
						'parent'  => 'plh-gtb-block-section-text',
					),
				'plh-hide-gtb-core/list'                           =>
					array(
						'label'   => 'List',
						'section' => 'plh-gutenberg-blocks',
						'parent'  => 'plh-gtb-block-section-text',
					),
				'plh-hide-gtb-core/list-item'                      =>
					array(
						'label'   => 'List Item',
						'section' => 'plh-gutenberg-blocks',
						'parent'  => 'plh-gtb-block-section-text',
					),
				'plh-hide-gtb-core/quote'                          =>
					array(
						'label'   => 'Quote',
						'section' => 'plh-gutenberg-blocks',
						'parent'  => 'plh-gtb-block-section-text',
					),
				'plh-hide-gtb-core/code'                           =>
					array(
						'label'   => 'Code',
						'section' => 'plh-gutenberg-blocks',
						'parent'  => 'plh-gtb-block-section-text',
					),
				'plh-hide-gtb-core/details'                        =>
					array(
						'label'   => 'Details',
						'section' => 'plh-gutenberg-blocks',
						'parent'  => 'plh-gtb-block-section-text',
					),
				'plh-hide-gtb-core/math'                           =>
					array(
						'label'   => 'Math',
						'section' => 'plh-gutenberg-blocks',
						'parent'  => 'plh-gtb-block-section-text',
					),
				'plh-hide-gtb-core/preformatted'                   =>
					array(
						'label'   => 'Preformatted',
						'section' => 'plh-gutenberg-blocks',
						'parent'  => 'plh-gtb-block-section-text',
					),
				'plh-hide-profile-group-personal-info'             =>
					array(
						'label'   => 'Personal Options',
						'section' => 'plh-profile',
					),
				'plh-hide-profile-syntax-highlighting'             =>
					array(
						'label'   => 'Syntax Highlighting',
						'section' => 'plh-profile',
						'parent'  => 'plh-hide-profile-group-personal-info',
					),
				'plh-hide-profile-color-scheme-selector'           =>
					array(
						'label'   => 'Administration Color Scheme',
						'section' => 'plh-profile',
						'parent'  => 'plh-hide-profile-group-personal-info',
					),
				'plh-hide-profile-keyboard-shortcuts'              =>
					array(
						'label'   => 'Keyboard Shortcuts',
						'section' => 'plh-profile',
						'parent'  => 'plh-hide-profile-group-personal-info',
					),
				'plh-hide-profile-toolbar-toggle'                  =>
					array(
						'label'   => 'Toolbar',
						'section' => 'plh-profile',
						'parent'  => 'plh-hide-profile-group-personal-info',
					),
				'plh-hide-dpf-po-language'                         =>
					array(
						'label'   => 'Language',
						'section' => 'plh-profile',
						'parent'  => 'plh-hide-profile-group-personal-info',
					),
				'plh-hide-dpf-po-defaulteditor'                    =>
					array(
						'label'   => 'Default Editor',
						'section' => 'plh-profile',
						'parent'  => 'plh-hide-profile-group-personal-info',
					),
				'plh-hide-profile-visual-editor'                   =>
					array(
						'label'   => 'Visual Editor',
						'section' => 'plh-profile',
						'parent'  => 'plh-hide-profile-group-personal-info',
					),
				'plh-hide-profile-group-name'                      =>
					array(
						'label'   => 'Name',
						'section' => 'plh-profile',
					),
				'plh-hide-profile-user-login'                      =>
					array(
						'label'   => 'Username',
						'section' => 'plh-profile',
						'parent'  => 'plh-hide-profile-group-name',
					),
				'plh-hide-sidebar-widget-WP_Widget_Archives'       =>
					array(
						'label'   => 'Archives',
						'section' => 'plh-sidebar-widgets',
					),
				'plh-hide-sidebar-widget-WP_Widget_Media_Audio'    =>
					array(
						'label'   => 'Audio',
						'section' => 'plh-sidebar-widgets',
					),
				'plh-hide-sidebar-widget-WP_Widget_Block'          =>
					array(
						'label'   => 'Block',
						'section' => 'plh-sidebar-widgets',
					),
				'plh-hide-sidebar-widget-WP_Widget_Calendar'       =>
					array(
						'label'   => 'Calendar',
						'section' => 'plh-sidebar-widgets',
					),
				'plh-hide-sidebar-widget-WP_Widget_Categories'     =>
					array(
						'label'   => 'Categories',
						'section' => 'plh-sidebar-widgets',
					),
				'plh-hide-sidebar-widget-WP_Widget_Custom_HTML'    =>
					array(
						'label'   => 'Custom HTML',
						'section' => 'plh-sidebar-widgets',
					),
				'plh-hide-sidebar-widget-WP_Widget_Media_Gallery'  =>
					array(
						'label'   => 'Gallery',
						'section' => 'plh-sidebar-widgets',
					),
				'plh-hide-sidebar-widget-WP_Widget_Media_Image'    =>
					array(
						'label'   => 'Image',
						'section' => 'plh-sidebar-widgets',
					),
				'plh-hide-sidebar-widget-WP_Widget_Links'          =>
					array(
						'label'   => 'Links',
						'section' => 'plh-sidebar-widgets',
					),
				'plh-hide-sidebar-widget-WP_Widget_Meta'           =>
					array(
						'label'   => 'Meta',
						'section' => 'plh-sidebar-widgets',
					),
				'plh-hide-sidebar-sidebar-1'                       =>
					array(
						'label'   => 'Blog Sidebar',
						'section' => 'plh-sidebars',
					),
				'plh-hide-sidebar-sidebar-2'                       =>
					array(
						'label'   => 'Footer 1',
						'section' => 'plh-sidebars',
					),
				'plh-hide-sidebar-sidebar-3'                       =>
					array(
						'label'   => 'Footer 2',
						'section' => 'plh-sidebars',
					),
				'plh-hide-tmce-wp_add_media'                       =>
					array(
						'label'   => 'Add Media (wp_add_media)',
						'section' => 'plh-tmce-buttons',
					),
				'plh-hide-tmce-aligncenter'                        =>
					array(
						'label'   => 'Align center (aligncenter)',
						'section' => 'plh-tmce-buttons',
					),
				'plh-hide-tmce-alignleft'                          =>
					array(
						'label'   => 'Align left (alignleft)',
						'section' => 'plh-tmce-buttons',
					),
				'plh-hide-tmce-alignright'                         =>
					array(
						'label'   => 'Align right (alignright)',
						'section' => 'plh-tmce-buttons',
					),
				'plh-hide-tmce-backcolor'                          =>
					array(
						'label'   => 'Background color (backcolor)',
						'section' => 'plh-tmce-buttons',
					),
				'plh-hide-tmce-blockquote'                         =>
					array(
						'label'   => 'Blockquote (blockquote)',
						'section' => 'plh-tmce-buttons',
					),
				'plh-hide-tmce-bold'                               =>
					array(
						'label'   => 'Bold (bold)',
						'section' => 'plh-tmce-buttons',
					),
				'plh-hide-tmce-bullist'                            =>
					array(
						'label'   => 'Bulleted list (bullist)',
						'section' => 'plh-tmce-buttons',
					),
				'plh-hide-tmce-removeformat'                       =>
					array(
						'label'   => 'Clear formatting (removeformat)',
						'section' => 'plh-tmce-buttons',
					),
				'plh-hide-tmce-wp_code'                            =>
					array(
						'label'   => 'Code (wp_code)',
						'section' => 'plh-tmce-buttons',
					),
				'plh-disable-custom-admin_menu_structure'          =>
					array(
						'label'       => 'Admin menu content',
						'section'     => 'plh-disable-customizations',
						'description' => 'Disables custom permissions, menu order, user-created items, etc. Does not affect global menu styles.',
					),
				'plh-disable-custom-plugin_visibility'             =>
					array(
						'label'       => 'Plugin list',
						'section'     => 'plh-disable-customizations',
						'description' => 'Disables custom plugin visibility and custom plugin names/descriptions on the "Plugins" page.',
					),
				'plh-disable-custom-dashboard_widgets'             =>
					array(
						'label'       => 'Dashboard widgets',
						'section'     => 'plh-disable-customizations',
						'description' => 'Disables custom widget visibility, layout, titles, and user-created widgets.',
					),
				'plh-disable-custom-metaboxes'                     =>
					array(
						'label'       => 'Meta boxes',
						'section'     => 'plh-disable-customizations',
						'description' => 'Disables custom meta box visibility in the post editor.',
					),
			),
	);

	//--------------------------------
	//endregion

	$placeholderSectionMessages = [
		'media-restrictions'     => 'You can prevent users from editing or deleting media items uploaded by others, or hide them from the Media Library.',
		'hide-others-posts'      => 'You can hide posts created by other users on pages like "Posts → All Posts". Supports custom post types.',
		'gutenberg-blocks'       => 'You can hide specific block types in the default block editor.',
		'profile'                => 'You can hide specific user profile fields.',
		'sidebar-widgets'        => 'You can hide specific sidebar widget types.',
		'sidebars'               => 'You can hide theme sidebars.',
		'tmce-buttons'           => 'You can hide individual TinyMCE buttons in the classic editor.',
		'disable-customizations' => 'You can disable some customizations for a role or user. For example, you could let a specific user see the default admin menu.',
	];

	foreach ($definitions['tweaks'] as $id => $def) {
		$def['className'] = PlaceholderTweak::class;
		$definitions['tweaks'][$id] = $def;
	}

	foreach ($definitions['sections'] as $id => $def) {
		$def['isProPlaceholder'] = true;

		$realSectionId = str_replace('plh-', '', $id);
		if ( isset($placeholderSectionMessages[$realSectionId]) ) {
			$def['proPlaceholderMessage'] = $placeholderSectionMessages[$realSectionId]
				. ' This feature is available in Admin Menu Editor Pro.';
		}
		$definitions['sections'][$id] = $def;
	}

	return $definitions;
}

return ws_tw_placeholder_tweaks();