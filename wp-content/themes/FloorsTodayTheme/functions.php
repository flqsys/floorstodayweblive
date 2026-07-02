<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

if ( !function_exists( 'chld_thm_cfg_parent_css' ) ):
    function chld_thm_cfg_parent_css() {
        wp_enqueue_style( 'chld_thm_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array(  ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_parent_css', 10 );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'chld_thm_cfg_parent' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION
// Load Floors Today site-specific customizations.
$ft_custom_functions = get_stylesheet_directory() . '/sites-edit/custom-functions.php';
if (is_readable($ft_custom_functions)) {
    require_once $ft_custom_functions;
}

if (!function_exists('ft_theme_enqueue_custom_css')) :
    function ft_theme_enqueue_custom_css() {
        $custom_css = get_stylesheet_directory() . '/assets/css/custom.css';

        wp_enqueue_style(
            'ft-theme-custom',
            get_stylesheet_directory_uri() . '/assets/css/custom.css',
            ['chld_thm_cfg_child'],
            is_readable($custom_css) ? (string) filemtime($custom_css) : null
        );
    }
endif;
add_action('wp_enqueue_scripts', 'ft_theme_enqueue_custom_css', 20);

