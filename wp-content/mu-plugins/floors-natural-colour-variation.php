<?php
/**
 * Plugin Name: Floors Today - Natural Colour Variation Notice
 * Description: Hides the "Natural Colour Variation" Elementor alert on
 *              single product pages unless the ACF natural_colour_variation
 *              field (Button Group, choices YSE/NO - note the "YSE" typo in
 *              ACF, matched here regardless) is set to yes. Elementor's own
 *              per-widget display conditions can't key off a custom field
 *              value, so this hides it with a targeted CSS override instead
 *              (no change to the Elementor template itself).
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_head', function () {
    if (!is_singular('our-products')) {
        return;
    }

    $value = strtolower(trim((string) get_post_meta(get_the_ID(), 'natural_colour_variation', true)));
    $is_yes = $value !== '' && $value[0] === 'y';

    if ($is_yes) {
        return;
    }

    echo '<style id="ft-natural-colour-variation">#natural{display:none !important;}</style>' . "\n";
});
