<?php
/**
 * Plugin Name: Floors Today Elementor Content Type Support
 * Description: Makes the ACF product post type and taxonomy available to Elementor conditions.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('register_post_type_args', function ($args, $post_type) {
    if ('our-products' !== $post_type) {
        return $args;
    }

    $args['has_archive'] = 'our-products';
    $args['show_in_rest'] = true;

    return $args;
}, 20, 2);

add_filter('register_taxonomy_args', function ($args, $taxonomy) {
    if ('categories' !== $taxonomy) {
        return $args;
    }

    $args['public'] = true;
    $args['publicly_queryable'] = true;
    $args['show_ui'] = true;
    $args['show_in_nav_menus'] = true;
    $args['show_in_rest'] = true;

    $labels = isset($args['labels']) && is_array($args['labels']) ? $args['labels'] : [];
    $labels['name'] = 'Product Categories';
    $labels['singular_name'] = 'Product Category';
    $labels['menu_name'] = 'Product Categories';
    $args['labels'] = $labels;
    $args['label'] = 'Product Categories';

    return $args;
}, 20, 2);

add_action('init', function () {
    $rewrite_version = '1.0.0';

    if (get_option('ft_elementor_content_types_rewrite_version') === $rewrite_version) {
        return;
    }

    flush_rewrite_rules(false);
    update_option('ft_elementor_content_types_rewrite_version', $rewrite_version, false);
}, 99);
