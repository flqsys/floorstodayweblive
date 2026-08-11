<?php
/**
 * Plugin Name: Floors Today Product Filter Shortcodes
 * Description: Vanilla JSON product filters for Floors Today product archives.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function ft_pf_fields() {
    return [
        'dimensions' => 'Dimensions',
        'color_shade' => 'Color Shade',
        'flooring_look' => 'Flooring Look',
        'flooring_types' => 'Flooring Types',
        'gloss_level' => 'Gloss Level',
        'species' => 'Species',
        'surface_texture' => 'Surface Texture',
    ];
}

// Which fields apply per category - not derived from product data, because
// some fields (e.g. Flooring Look) are fully populated across every product
// in a category yet still aren't meaningful filters there (per the agreed
// spec). Explicit allowlist, not auto-detection.
function ft_pf_category_field_allowlist() {
    return [
        'carpet' => ['dimensions', 'color_shade', 'flooring_types', 'surface_texture'],
        'engineered-hardwood' => ['dimensions', 'color_shade', 'flooring_types', 'surface_texture', 'gloss_level', 'species'],
        'laminate' => ['dimensions', 'color_shade', 'flooring_look', 'gloss_level'],
        'solid-hardwood' => ['dimensions', 'color_shade', 'flooring_types', 'surface_texture', 'gloss_level', 'species'],
        'vinyl' => ['dimensions', 'color_shade', 'flooring_look', 'flooring_types', 'surface_texture', 'gloss_level'],
    ];
}

function ft_pf_clean_product_title($title) {
    $title = (string) $title;

    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode(wp_specialchars_decode($title, ENT_QUOTES), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $title) {
            break;
        }
        $title = $decoded;
    }

    return $title;
}
function ft_pf_split_values($value) {
    $value = wp_strip_all_tags((string) $value);
    $parts = preg_split('/[,|\/]+/', $value);
    $parts = array_map('trim', is_array($parts) ? $parts : []);
    return array_values(array_unique(array_filter($parts, static fn($part) => $part !== '')));
}

function ft_pf_product_image($post_id) {
    $catalog_image = get_post_meta($post_id, 'catalog_image', true);

    // Some products have this stored as a serialized array of one ID
    // (e.g. from an ACF/import path) rather than a bare scalar -
    // get_post_meta() auto-unserializes it, so $catalog_image comes back
    // as an array here. is_numeric()/is_string() both silently return
    // false for an array, so this field was never actually reaching
    // either branch below - every card fell through to the featured
    // image regardless of what catalog_image pointed to.
    if (is_array($catalog_image)) {
        $catalog_image = reset($catalog_image);
    }

    if (is_numeric($catalog_image)) {
        // 'large' (WP core default, max 1024px) instead of 'full' - if a
        // smaller registered size doesn't exist yet for a given image, WP
        // just falls back to full size on its own, so this is free to
        // request even before thumbnails are regenerated, and starts
        // paying off automatically the moment they are.
        $url = wp_get_attachment_image_url((int) $catalog_image, 'large');
        if ($url) {
            return $url;
        }
    }

    if (is_string($catalog_image) && preg_match('#^https?://#i', $catalog_image)) {
        return esc_url_raw($catalog_image);
    }

    $thumb = get_the_post_thumbnail_url($post_id, 'large');
    return $thumb ?: '';
}

function ft_pf_build_data($atts, $fixed_category = '') {
    $fields = ft_pf_fields();
    $meta_fields = ['color' => 'Color'] + $fields;
    $terms = get_terms([
        'taxonomy' => 'categories',
        'hide_empty' => true,
    ]);

    $categories = [];
    $options = ['categories' => []];

    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $categories[] = [
                'id' => (int) $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'count' => (int) $term->count,
            ];
            $options['categories'][$term->slug] = $term->name;
        }
    }

    $args = [
        'post_type' => 'our-products',
        'post_status' => 'publish',
        'posts_per_page' => (int) $atts['limit'],
        'orderby' => 'title',
        'order' => 'ASC',
        'no_found_rows' => true,
    ];

    // Only scoped when the category is locked (hide_category=yes, e.g. category
    // archive pages) - the field/option lists below are built purely from
    // whichever products this query returns, so locking it to one category here
    // is what keeps irrelevant fields (e.g. Species on a Carpet page) from
    // showing at all. Never applied when the category picker itself is visible
    // and changeable - that needs every category's products loaded at once.
    if ($fixed_category !== '') {
        $args['tax_query'] = [
            [
                'taxonomy' => 'categories',
                'field' => 'slug',
                'terms' => $fixed_category,
            ],
        ];
    }

    $query = new WP_Query($args);
    $products = [];

    foreach (array_keys($fields) as $key) {
        $options[$key] = [];
    }

    foreach ($query->posts as $post) {
        $post_terms = get_the_terms($post, 'categories');
        $product_categories = [];

        if (!is_wp_error($post_terms) && is_array($post_terms)) {
            foreach ($post_terms as $term) {
                $product_categories[] = [
                    'id' => (int) $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                ];
                $options['categories'][$term->slug] = $term->name;
            }
        }

        $meta = [];
        foreach ($meta_fields as $key => $label) {
            $raw = get_post_meta($post->ID, $key, true);
            $values = ft_pf_split_values($raw);
            $meta[$key] = [
                'raw' => wp_strip_all_tags((string) $raw),
                'values' => $values,
                'slugs' => array_map('sanitize_title', $values),
            ];

            if (isset($fields[$key])) {
                foreach ($values as $value) {
                    $options[$key][sanitize_title($value)] = $value;
                }
            }
        }

        $products[] = [
            'id' => (int) $post->ID,
            'title' => ft_pf_clean_product_title(get_the_title($post)),
            'url' => get_permalink($post),
            'image' => ft_pf_product_image($post->ID),
            'excerpt' => wp_trim_words(wp_strip_all_tags($post->post_content), 18),
            'categories' => $product_categories,
            'categorySlugs' => array_column($product_categories, 'slug'),
            'meta' => $meta,
        ];
    }

    $allowlist = ft_pf_category_field_allowlist();
    if ($fixed_category !== '' && isset($allowlist[$fixed_category])) {
        $allowed = $allowlist[$fixed_category];
        foreach ($fields as $key => $label) {
            if (!in_array($key, $allowed, true)) {
                $options[$key] = [];
            }
        }
    }

    foreach ($options as $key => $values) {
        asort($values, SORT_NATURAL | SORT_FLAG_CASE);
        $options[$key] = array_map(
            static fn($slug, $label) => ['slug' => $slug, 'label' => $label],
            array_keys($values),
            array_values($values)
        );
    }

    return [
        'fields' => $fields,
        'categories' => $categories,
        'options' => $options,
        'products' => $products,
    ];
}

function ft_pf_shortcode($atts) {
    static $assets_printed = false;

    $atts = shortcode_atts([
        'layout' => 'vertical',
        'category' => '',
        'hide_category' => 'no',
        'limit' => -1,
        'columns' => 3,
        'gap' => 0,
        'per_page' => 15,
    ], $atts, 'floors_product_filter');

    $layout = strtolower((string) $atts['layout']) === 'horizontal' ? 'horizontal' : 'vertical';
    $columns = max(1, min(4, (int) $atts['columns']));
    $gap = max(0, (int) $atts['gap']);
    $per_page = max(1, (int) $atts['per_page']);
    $hide_category = in_array(strtolower((string) $atts['hide_category']), ['1', 'yes', 'true'], true);
    $fixed_category = $hide_category ? sanitize_title($atts['category']) : '';
    $data = ft_pf_build_data($atts, $fixed_category);
    $instance_id = wp_unique_id('ft-product-filter-');

    ob_start();

    if (!$assets_printed) {
        $assets_printed = true;
        ?>
        <style id="ft-product-filter-styles">
            .ft-pf,
            .ft-pf * { box-sizing: border-box; }
            .ft-pf {
                --ft-pf-primary: #235bb8;
                --ft-pf-accent: #CC9C2E;
                --ft-pf-text: #111827;
                --ft-pf-muted: #667085;
                --ft-pf-border: #e5e7eb;
                --ft-pf-card: #fff;
                display: grid;
                gap: var(--ft-pf-gap);
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 0;
                color: var(--ft-pf-text);
                font-family: Arial, Helvetica, sans-serif;
            }
            .ft-pf[data-layout="vertical"] {
                grid-template-columns: minmax(230px, 300px) minmax(0, 1fr);
                align-items: start;
            }
            .ft-pf[data-layout="horizontal"] {
                grid-template-columns: minmax(230px, 280px) minmax(0, 1fr);
                align-items: start;
                column-gap: 32px;
                row-gap: 32px;
            }
            .ft-pf__panel {
                border: 1px solid var(--ft-pf-border);
                border-radius: 8px;
                background: var(--ft-pf-card);
                box-shadow: 0 18px 45px rgba(15, 23, 42, .06);
            }
            .ft-pf__filter-toggle,
            .ft-pf__panel-close,
            .ft-pf__backdrop {
                display: none;
            }
            .ft-pf[data-layout="vertical"] .ft-pf__panel {
                position: sticky;
                top: 112px;
                padding: 18px;
                z-index: 500;
            }
            .ft-pf[data-layout="horizontal"] .ft-pf__panel {
                position: sticky;
                top: 112px;
                padding: 18px;
                z-index: 500;
            }
            .ft-pf__top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 16px;
            }
            .ft-pf__heading {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 12px;
            }
            .ft-pf__title {
                margin: 0 !important;
                color: var(--ft-pf-text) !important;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 18px !important;
                font-style: normal !important;
                font-weight: 700 !important;
                letter-spacing: 0 !important;
                line-height: 1.25 !important;
                text-transform: none !important;
            }
            .ft-pf button,
            .ft-pf input,
            .ft-pf select {
                font-family: Arial, Helvetica, sans-serif !important;
            }
            .ft-pf button {
                appearance: none !important;
                -webkit-appearance: none !important;
                letter-spacing: 0 !important;
                text-transform: none !important;
                text-decoration: none !important;
            }
            .ft-pf__clear {
                display: inline-flex !important;
                min-height: 36px;
                align-items: center !important;
                justify-content: center !important;
                border: 0 !important;
                border-radius: 8px !important;
                background: var(--ft-pf-accent) !important;
                color: #fff !important;
                cursor: pointer;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 14px !important;
                font-style: normal !important;
                font-weight: 700 !important;
                line-height: 1 !important;
                padding: 0 16px !important;
                box-shadow: none !important;
                transition: background-color .18s ease;
            }
            .ft-pf__clear:hover,
            .ft-pf__clear:focus-visible {
                background: #ad8121 !important;
                color: #fff !important;
                outline: none;
            }
            .ft-pf__select-wrap {
                --select-border: #d0d5dd;
                --select-arrow: var(--select-border);
                display: grid;
                grid-template-areas: "select";
                align-items: center;
                position: relative;
                min-height: 42px;
                border: 1px solid var(--select-border);
                border-radius: 8px;
                background-color: #fff;
                transition: border-color .18s ease, box-shadow .18s ease;
            }
            .ft-pf__select-wrap.is-open,
            .ft-pf__select-wrap:focus-within {
                --select-border: var(--ft-pf-accent);
                box-shadow: 0 0 0 3px rgba(204, 156, 46, .16);
            }
            .ft-pf__select-wrap::after {
                content: "";
                grid-area: select;
                width: .8em;
                height: .5em;
                margin-right: 12px;
                justify-self: end;
                background-color: var(--select-arrow);
                clip-path: polygon(100% 0%, 0 0%, 50% 100%);
                pointer-events: none;
                transition: transform .18s ease, background-color .18s ease;
            }
            .ft-pf__select-wrap.is-open::after {
                background-color: var(--ft-pf-accent);
                transform: rotate(180deg);
            }
            .ft-pf button.ft-pf__select-trigger {
                grid-area: select;
                display: flex;
                align-items: center;
                width: 100%;
                min-width: 0;
                min-height: 42px;
                border: 0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                color: var(--ft-pf-text) !important;
                cursor: pointer;
                font: inherit;
                line-height: inherit;
                outline: none;
                padding: 0 34px 0 12px;
                text-align: left;
                box-shadow: none !important;
                text-decoration: none !important;
            }
            .ft-pf button.ft-pf__select-trigger:hover,
            .ft-pf button.ft-pf__select-trigger:focus-visible,
            .ft-pf__select-wrap.is-open button.ft-pf__select-trigger {
                background: transparent !important;
                color: var(--ft-pf-text) !important;
                box-shadow: none !important;
            }
            .ft-pf__select-current {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .ft-pf__select-menu {
                position: absolute;
                z-index: 30;
                top: calc(100% + 8px);
                left: 0;
                right: 0;
                max-height: var(--ft-pf-menu-max, 250px);
                overflow: auto;
                border: 1px solid #d0d5dd;
                border-radius: 8px;
                background: #fff;
                box-shadow: 0 18px 45px rgba(15, 23, 42, .16);
                padding: 6px;
            }
            .ft-pf__select-wrap.is-drop-up .ft-pf__select-menu {
                top: auto;
                bottom: calc(100% + 8px);
            }
            .ft-pf__select-menu[hidden] {
                display: none !important;
            }
            .ft-pf button.ft-pf__select-option {
                display: block;
                width: 100%;
                min-height: 36px;
                border: 0 !important;
                border-radius: 6px !important;
                background: transparent !important;
                color: var(--ft-pf-text) !important;
                cursor: pointer;
                font: inherit;
                padding: 8px 10px;
                text-align: left;
                box-shadow: none !important;
                text-decoration: none !important;
                transition: background .16s ease, color .16s ease;
            }
            .ft-pf button.ft-pf__select-option:hover,
            .ft-pf button.ft-pf__select-option:focus-visible {
                background: #CC9C2E1F !important;
                color: #5f450d !important;
                outline: none;
            }
            .ft-pf button.ft-pf__select-option.is-selected {
                background: #CC9C2E63 !important;
                color: #111827 !important;
                font-weight: 700;
            }
            .ft-pf__field {
                margin-top: 16px;
                padding-top: 16px;
                border-top: 1px solid var(--ft-pf-border);
            }
            .ft-pf__field:first-of-type {
                margin-top: 0;
                padding-top: 0;
                border-top: 0;
            }
            .ft-pf__search-input {
                width: 100% !important;
                min-height: 42px;
                border: 1px solid #d0d5dd !important;
                border-radius: 8px !important;
                background: #fff !important;
                color: var(--ft-pf-text) !important;
                font-size: 15px !important;
                line-height: 1.3 !important;
                padding: 0 12px !important;
                box-shadow: none !important;
                outline: none !important;
                transition: border-color .18s ease, box-shadow .18s ease;
            }
            .ft-pf__search-input:focus {
                border-color: var(--ft-pf-accent) !important;
                box-shadow: 0 0 0 3px rgba(204, 156, 46, .16) !important;
            }
            .ft-pf__search-input::placeholder {
                color: #667085;
            }
            .ft-pf__label {
                display: block;
                margin: 0 0 10px !important;
                color: var(--ft-pf-text) !important;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 14px !important;
                font-weight: 600 !important;
                line-height: 1.35 !important;
            }
            .ft-pf__checks {
                display: grid;
                gap: 9px;
                max-height: 190px;
                overflow: auto;
                padding-right: 4px;
            }
            .ft-pf__check {
                display: flex;
                align-items: center;
                gap: 12px;
                color: #344054;
                cursor: pointer;
                font-size: 14px;
                line-height: 1.3;
            }
            .ft-pf__checkbox-anim {
                position: relative;
                display: inline-block;
                width: 28px;
                height: 28px;
                flex: 0 0 28px;
            }
            .ft-pf__checkbox-anim input[type="checkbox"] {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                margin: 0;
                opacity: 0;
                appearance: none;
                -webkit-appearance: none;
                cursor: pointer;
                z-index: 2;
            }
            .ft-pf__checkbox-anim svg {
                display: block;
                width: 100%;
                height: 100%;
            }
            .ft-pf__checkbox-anim .background {
                fill: #d7dde8;
                transition: ease all .6s;
                -webkit-transition: ease all .6s;
            }
            .ft-pf__checkbox-anim .stroke {
                fill: none;
                stroke: #fff;
                stroke-miterlimit: 10;
                stroke-width: 2px;
                stroke-dashoffset: 100;
                stroke-dasharray: 100;
                transition: ease all .6s;
                -webkit-transition: ease all .6s;
            }
            .ft-pf__checkbox-anim .check {
                fill: none;
                stroke: #fff;
                stroke-linecap: round;
                stroke-linejoin: round;
                stroke-width: 2px;
                stroke-dashoffset: 22;
                stroke-dasharray: 22;
                transition: ease all .6s;
                -webkit-transition: ease all .6s;
            }
            .ft-pf__check:hover .ft-pf__checkbox-anim .check {
                stroke-dashoffset: 0;
            }
            .ft-pf__checkbox-anim input[type="checkbox"]:checked + svg .background {
                fill: var(--ft-pf-accent);
            }
            .ft-pf__checkbox-anim input[type="checkbox"]:checked + svg .stroke,
            .ft-pf__checkbox-anim input[type="checkbox"]:checked + svg .check {
                stroke-dashoffset: 0;
            }
            .ft-pf[data-layout="horizontal"] .ft-pf__controls {
                display: grid;
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .ft-pf[data-layout="horizontal"] .ft-pf__field {
                margin: 0;
                padding: 0;
                border: 0;
            }
            .ft-pf[data-layout="horizontal"] .ft-pf__top {
                align-items: stretch;
                flex-direction: column;
            }
            .ft-pf[data-layout="horizontal"] .ft-pf__clear {
                width: 100%;
            }
            .ft-pf__count {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                min-height: 30px;
                border: 1px solid rgba(204, 156, 46, .35);
                border-radius: 999px;
                background: rgba(204, 156, 46, .12);
                color: #745715;
                font-family: "Montserrat", sans-serif;
                font-size: 12px;
                font-weight: 500;
                padding: 5px 11px;
            }
            .ft-pf__count i {
                color: var(--ft-pf-accent);
                font-size: 13px;
            }
            .ft-pf__grid {
                display: grid;
                grid-template-columns: repeat(var(--ft-pf-columns), minmax(0, 1fr));
                column-gap: 40px !important;
                row-gap: 40px !important;
                transition: height .26s ease, opacity .16s ease;
            }
            @media (prefers-reduced-motion: reduce) {
                .ft-pf__grid {
                    transition: none;
                }
            }
            .ft-pf__card {
                position: relative;
                display: grid;
                min-height: 260px;
                overflow: hidden;
                border-radius: 8px;
                background: #f4f4f5;
                color: #fff;
                text-decoration: none;
                box-shadow: 0 14px 34px rgba(15, 23, 42, .10);
                transition: transform .18s ease, box-shadow .18s ease;
            }
            .ft-pf__card:hover {
                transform: translateY(-3px);
                box-shadow: 0 20px 45px rgba(15, 23, 42, .16);
            }
            .ft-pf__image {
                position: absolute;
                inset: 0 !important;
                display: block;
                width: 100% !important;
                height: 100% !important;
                background-position: center center !important;
                background-repeat: no-repeat !important;
                background-size: 120% auto !important;
                background-color: #f4f4f5;
            }
            .ft-pf__image-placeholder {
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, #e5e7eb, #f8fafc);
            }
            .ft-pf__card-content {
                position: relative;
                z-index: 1;
                align-self: end;
                background: linear-gradient(to top, rgba(0, 0, 0, .72), rgba(0, 0, 0, .02));
                padding: 76px 16px 14px;
            }
            .ft-pf__card-title {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                margin: 0;
                font-family: "Montserrat", sans-serif;
                font-size: 16px;
                font-weight: 400;
                line-height: 1.2;
                text-shadow: 0 1px 12px rgba(0, 0, 0, .35);
                transition: color .18s ease;
            }
            .ft-pf__card-title,
            .ft-pf__card-title *,
            .ft-pf__card-title a {
                color: #fff !important;
                text-shadow: 0 1px 3px rgba(0,0,0,.65);
            }
            .ft-pf__card:hover .ft-pf__card-title,
            .ft-pf__card:focus-visible .ft-pf__card-title {
                color: #fff !important;
            }
            .ft-pf__card-title,
            .ft-pf__card-title *,
            .ft-pf__card-title a {
                color: #fff !important;
                text-shadow: 0 1px 3px rgba(0,0,0,.65);
            }
            .ft-pf__chips {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 10px;
            }
            .ft-pf__chip {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                border-radius: 999px;
                background: #CC9C2E63;
                color: #fff;
                font-size: 11px;
                font-weight: 400;
                padding: 5px 8px;
            }
            .ft-pf__empty {
                display: none;
                border: 1px dashed #cbd5e1;
                border-radius: 8px;
                color: var(--ft-pf-muted);
                padding: 32px;
                text-align: center;
            }
            .ft-pf__load-wrap {
                display: flex;
                justify-content: center;
                margin-top: 30px;
            }
            .ft-pf__load-more {
                min-height: 44px;
                border: 0;
                border-radius: 8px;
                background: #171717;
                color: #fff;
                cursor: pointer;
                font: inherit;
                font-size: 16px;
                font-weight: 400;
                padding: 0 28px;
                transition: background .18s ease, transform .12s ease;
            }
            .ft-pf__load-more:hover,
            .ft-pf__load-more:focus-visible {
                background: var(--ft-pf-accent);
                outline: none;
            }
            .ft-pf__load-more:active {
                transform: translateY(1px);
            }
            .ft-pf__load-more[hidden] {
                display: none !important;
            }
            .ft-pf.is-empty .ft-pf__empty { display: block; }
            .ft-pf.is-empty .ft-pf__grid,
            .ft-pf.is-empty .ft-pf__load-wrap { display: none; }
            @media (max-width: 1024px) {
                .ft-pf[data-layout="vertical"],
                .ft-pf[data-layout="horizontal"] {
                    grid-template-columns: 1fr;
                }
                .ft-pf[data-layout="vertical"] .ft-pf__panel,
                .ft-pf[data-layout="horizontal"] .ft-pf__panel {
                    position: static;
                }
                .ft-pf[data-layout="horizontal"] .ft-pf__controls {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
                .ft-pf[data-layout="horizontal"] .ft-pf__top {
                    align-items: center;
                    flex-direction: row;
                }
                .ft-pf[data-layout="horizontal"] .ft-pf__clear {
                    width: auto;
                }
                .ft-pf__grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
            @media (max-width: 640px) {
                .ft-pf {
                    row-gap: 18px;
                }
                .ft-pf__filter-toggle {
                    display: inline-flex;
                    width: fit-content;
                    min-height: 42px;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    border: 1px solid var(--ft-pf-border) !important;
                    border-radius: 999px !important;
                    background: #fff !important;
                    color: var(--ft-pf-text) !important;
                    cursor: pointer;
                    font: inherit;
                    font-size: 14px;
                    font-weight: 700;
                    padding: 0 16px;
                    box-shadow: 0 12px 28px rgba(15, 23, 42, .08) !important;
                }
                .ft-pf__filter-toggle svg {
                    width: 16px;
                    height: 16px;
                    flex: 0 0 16px;
                }
                .ft-pf__backdrop {
                    position: fixed;
                    inset: 0;
                    z-index: 1000;
                    display: block;
                    background: rgba(15, 23, 42, .48);
                    opacity: 0;
                    pointer-events: none;
                    transition: opacity .18s ease;
                }
                .ft-pf.is-filter-open .ft-pf__backdrop {
                    opacity: 1;
                    pointer-events: auto;
                }
                .ft-pf[data-layout="horizontal"] .ft-pf__panel,
                .ft-pf[data-layout="vertical"] .ft-pf__panel {
                    position: fixed;
                    z-index: 1001;
                    top: 74px;
                    right: 12px;
                    bottom: 14px;
                    left: 12px;
                    overflow: auto;
                    padding: 18px;
                    border-radius: 14px;
                    opacity: 0;
                    pointer-events: none;
                    transform: translateY(16px);
                    visibility: hidden;
                    transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
                }
                .ft-pf.is-filter-open .ft-pf__panel {
                    opacity: 1;
                    pointer-events: auto;
                    transform: translateY(0);
                    visibility: visible;
                }
                .ft-pf__panel-close {
                    display: inline-flex;
                    width: 34px;
                    height: 34px;
                    align-items: center;
                    justify-content: center;
                    border: 1px solid var(--ft-pf-border) !important;
                    border-radius: 999px !important;
                    background: #fff !important;
                    color: var(--ft-pf-text) !important;
                    cursor: pointer;
                    font: inherit;
                    font-size: 20px;
                    line-height: 1;
                    padding: 0;
                    box-shadow: none !important;
                }
                .ft-pf[data-layout="horizontal"] .ft-pf__controls {
                    grid-template-columns: 1fr;
                }
                .ft-pf__grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    column-gap: 12px !important;
                    row-gap: 14px !important;
                }
                .ft-pf__card {
                    min-height: 176px;
                    border-radius: 7px;
                }
                .ft-pf__card-content {
                    padding: 56px 10px 10px;
                }
                .ft-pf__card-title {
                    font-size: 18px;
                    line-height: 1.25;
                }
            .ft-pf__card-title,
            .ft-pf__card-title *,
            .ft-pf__card-title a {
                color: #fff !important;
                text-shadow: 0 1px 3px rgba(0,0,0,.65);
            }
                .ft-pf__chips {
                    gap: 5px;
                    margin-top: 7px;
                }
                .ft-pf__chip {
                    font-size: 9px;
                    padding: 4px 6px;
                }
                .ft-pf[data-layout="horizontal"] .ft-pf__top {
                    align-items: center;
                    flex-direction: row;
                }
            }
        </style>
        <script id="ft-product-filter-script">
            (function () {
                function normalize(value) {
                    return String(value || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
                }

                function matchesSearch(product, searchTerm) {
                    if (!searchTerm) return true;
                    var title = normalize(product.title);
                    return searchTerm.split(/\s+/).every(function (term) {
                        return !term || title.indexOf(term) !== -1;
                    });
                }

                function escapeHtml(value) {
                    return String(value || '').replace(/[&<>"']/g, function (match) {
                        return {
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            '"': '&quot;',
                            "'": '&#039;'
                        }[match];
                    });
                }

                function selectedValues(root, key) {
                    return Array.from(root.querySelectorAll('[data-filter="' + key + '"]:checked')).map(function (input) {
                        return input.value;
                    });
                }

                function selectedSelect(root, key) {
                    var input = root.querySelector('.ft-pf__select-value[data-filter="' + key + '"]');
                    return input ? input.value : '';
                }

                function matchesMulti(productValues, selected) {
                    if (!selected.length) return true;
                    return selected.some(function (value) {
                        return productValues.indexOf(value) !== -1;
                    });
                }

                function positionSelectMenu(wrap, menu) {
                    var rect = wrap.getBoundingClientRect();
                    var viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
                    var below = Math.max(0, viewportHeight - rect.bottom - 12);
                    var above = Math.max(0, rect.top - 12);
                    var desiredHeight = Math.min(menu.scrollHeight || 250, 250);
                    var dropUp = below < desiredHeight && above > below;
                    var available = dropUp ? above : below;

                    wrap.classList.toggle('is-drop-up', dropUp);
                    wrap.style.setProperty('--ft-pf-menu-max', Math.max(140, Math.min(250, available)) + 'px');
                }

                // Cards store their image URL in data-bg instead of an
                // inline background-image, so nothing downloads until
                // observeCardImages() below actually applies it once the
                // card scrolls near the viewport. These product images
                // aren't resized on the server (some are 200-450KB each),
                // so loading all 15 of a page at once was the actual cause
                // of the slow/half-rendered look on first load - this
                // spreads that network cost out over scrolling instead.
                function renderCard(product) {
                    var image = product.image
                        ? '<span class="ft-pf__image" role="img" aria-label="' + escapeHtml(product.title) + '" data-bg="' + escapeHtml(product.image) + '"></span>'
                        : '<span class="ft-pf__image-placeholder"></span>';

                    // A plain div, not h2/h3 - a heading tag here means the
                    // theme's global heading styles (font-size, font-family,
                    // etc.) apply on top of this file's own styling, and can
                    // win depending on specificity - exactly what inflated
                    // this to ~30px instead of the intended 16px. It's also
                    // not really a real heading anyway: it's one of many
                    // repeated card labels in a grid, not document structure.
                    return '<a class="ft-pf__card" href="' + escapeHtml(product.url) + '">' + image + '<div class="ft-pf__card-content"><div class="ft-pf__card-title">' + escapeHtml(product.title) + '</div></div></a>';
                }

                function init(root) {
                    if (!root || root.dataset.ftPfReady === '1') return;
                    root.dataset.ftPfReady = '1';

                    var dataNode = root.querySelector('script[type="application/json"]');
                    var data = dataNode ? JSON.parse(dataNode.textContent) : null;
                    if (!data) return;

                    var grid = root.querySelector('.ft-pf__grid');
                    var count = root.querySelector('.ft-pf__count');
                    var loadMore = root.querySelector('.ft-pf__load-more');
                    var filterToggle = root.querySelector('.ft-pf__filter-toggle');
                    var filterClose = root.querySelector('.ft-pf__panel-close');
                    var filterBackdrop = root.querySelector('.ft-pf__backdrop');
                    var perPage = Number(root.dataset.perPage || 15);
                    var visibleLimit = perPage;
                    var lastFiltered = [];
                    var initialCategory = root.dataset.initialCategory || '';
                    var fixedCategory = root.dataset.fixedCategory || '';
                    var searchInput = root.querySelector('.ft-pf__search-input');

                    // Loads a card's background-image only once it scrolls
                    // near the viewport (200px early, so it's ready by the
                    // time it's actually visible) instead of every card on
                    // the page requesting its image immediately. Falls back
                    // to loading everything immediately on very old browsers
                    // without IntersectionObserver, rather than never
                    // loading images at all.
                    var imageObserver = ('IntersectionObserver' in window)
                        ? new IntersectionObserver(function (entries) {
                            entries.forEach(function (entry) {
                                if (!entry.isIntersecting) return;
                                loadCardImage(entry.target);
                                imageObserver.unobserve(entry.target);
                            });
                        }, { rootMargin: '200px 0px' })
                        : null;

                    function loadCardImage(el) {
                        var src = el.dataset.bg;
                        if (!src) return;
                        el.style.backgroundImage = 'url("' + src + '")';
                        el.removeAttribute('data-bg');
                    }

                    function observeCardImages() {
                        var targets = grid.querySelectorAll('.ft-pf__image[data-bg]');
                        if (imageObserver) {
                            targets.forEach(function (el) { imageObserver.observe(el); });
                        } else {
                            targets.forEach(loadCardImage);
                        }
                    }

                    if (initialCategory) {
                        var categoryInput = root.querySelector('.ft-pf__select-value[data-filter="categories"]');
                        var categoryCheck = root.querySelector('input[type="checkbox"][data-filter="categories"][value="' + initialCategory + '"]');

                        if (categoryInput) {
                            var categoryWrap = categoryInput.closest('.ft-pf__select-wrap');
                            var categoryOption = categoryWrap ? categoryWrap.querySelector('.ft-pf__select-option[data-value="' + initialCategory + '"]') : null;

                            if (categoryOption) {
                                categoryInput.value = initialCategory;
                                categoryWrap.querySelector('.ft-pf__select-current').textContent = categoryOption.textContent;
                                categoryWrap.querySelectorAll('.ft-pf__select-option').forEach(function (option) {
                                    option.classList.toggle('is-selected', option === categoryOption);
                                });
                            }
                        } else if (categoryCheck) {
                            categoryCheck.checked = true;
                        }
                    }

                    function closeFilterPanel() {
                        root.classList.remove('is-filter-open');
                        if (filterToggle) filterToggle.setAttribute('aria-expanded', 'false');
                    }

                    function openFilterPanel() {
                        root.classList.add('is-filter-open');
                        if (filterToggle) filterToggle.setAttribute('aria-expanded', 'true');
                    }

                    function apply(scrollToTop) {
                        visibleLimit = perPage;
                        render();
                        if (scrollToTop) {
                            anchorToFilterTop();
                        }
                    }

                    // Brings the filter panel back into view (below the
                    // sticky site header) after a selection changes the
                    // results - otherwise, if the visitor had scrolled down
                    // (e.g. via Load More) and a filter narrows the results
                    // way down, they'd be stranded looking at empty space
                    // with no results in sight. Only moves the page when the
                    // panel's actually been scrolled past; does nothing if
                    // it's already in view, so it never fights a visitor who
                    // hasn't scrolled anywhere.
                    function anchorToFilterTop() {
                        var headerOffset = 128;
                        var rect = root.getBoundingClientRect();
                        if (rect.top >= headerOffset) return;
                        window.scrollTo({
                            top: window.pageYOffset + rect.top - headerOffset,
                            behavior: 'smooth'
                        });
                    }

                    // Swaps the grid's content without an instant snap: holds
                    // the current height, fades out, mutates the DOM, then
                    // animates to the new natural height while fading back
                    // in. Without this, a filter that changes the result
                    // count changes the grid's height instantly, reflowing
                    // everything below it in one frame - the "page jumps"
                    // feeling. Height is cleared back to auto once settled
                    // so later reflows (window resize, etc.) aren't pinned
                    // to a stale pixel value.
                    //
                    // Checkboxes fire both "input" and "change" for a single
                    // click, and both are listened for (search needs "input",
                    // the custom selects call apply() directly) - so two
                    // overlapping calls for the same interaction are routine,
                    // not an edge case. Without cancelling the first call's
                    // pending steps, its later timeout can stomp the second
                    // call's in-progress height, leaving the grid pinned to a
                    // stale pixel height from before the filter - exactly the
                    // large blank gap seen with a single result. Every call
                    // cancels whatever's still in flight before starting.
                    var gridAnimTimeouts = [];
                    function animateGridUpdate(mutate) {
                        gridAnimTimeouts.forEach(function (id) { clearTimeout(id); });
                        gridAnimTimeouts = [];

                        var startHeight = grid.offsetHeight;
                        grid.style.height = startHeight + 'px';
                        grid.style.overflow = 'hidden';
                        grid.style.opacity = '1';

                        requestAnimationFrame(function () {
                            grid.style.opacity = '0';
                            gridAnimTimeouts.push(setTimeout(function () {
                                mutate();
                                var endHeight = grid.scrollHeight;
                                grid.style.height = endHeight + 'px';
                                requestAnimationFrame(function () {
                                    grid.style.opacity = '1';
                                });
                                gridAnimTimeouts.push(setTimeout(function () {
                                    grid.style.height = '';
                                    grid.style.overflow = '';
                                }, 260));
                            }, 160));
                        });
                    }

                    function render() {
                        var categoryChecks = selectedValues(root, 'categories');
                        var categorySelect = selectedSelect(root, 'categories');
                        var categorySelected = fixedCategory
                            ? [fixedCategory]
                            : (categorySelect ? [categorySelect] : categoryChecks);

                        var searchTerm = normalize(searchInput ? searchInput.value : '').trim();

                        lastFiltered = data.products.filter(function (product) {
                            if (!matchesSearch(product, searchTerm)) return false;
                            if (!matchesMulti(product.categorySlugs, categorySelected)) return false;

                            return Object.keys(data.fields).every(function (key) {
                                var checked = selectedValues(root, key);
                                var selected = selectedSelect(root, key);
                                var values = selected ? [selected] : checked;
                                var productValues = product.meta[key] ? product.meta[key].slugs : [];
                                return matchesMulti(productValues, values);
                            });
                        });

                        animateGridUpdate(function () {
                            grid.innerHTML = lastFiltered.slice(0, visibleLimit).map(renderCard).join('');
                            observeCardImages();
                            count.innerHTML = '<i class="fa-solid fa-box-archive" aria-hidden="true"></i><span>' + lastFiltered.length + ' product' + (lastFiltered.length === 1 ? '' : 's') + '</span>';
                            root.classList.toggle('is-empty', lastFiltered.length === 0);
                            if (loadMore) loadMore.hidden = visibleLimit >= lastFiltered.length;
                        });
                    }

                    // Search-as-you-type is the one exception that shouldn't
                    // anchor-scroll - the visitor's still typing, mid-input,
                    // and yanking the page around under their cursor would
                    // be disruptive. Every other input on this panel
                    // (checkboxes) is a discrete, one-shot selection.
                    function isSearchInput(target) {
                        return !!target.closest('.ft-pf__search-input');
                    }
                    root.addEventListener('input', function (event) {
                        apply(!isSearchInput(event.target));
                    });
                    root.addEventListener('change', function (event) {
                        apply(!isSearchInput(event.target));
                    });
                    root.addEventListener('click', function (event) {
                        var trigger = event.target.closest('.ft-pf__select-trigger');
                        var option = event.target.closest('.ft-pf__select-option');

                        if (trigger) {
                            var wrap = trigger.closest('.ft-pf__select-wrap');
                            var menu = wrap ? wrap.querySelector('.ft-pf__select-menu') : null;
                            if (!menu) return;

                            root.querySelectorAll('.ft-pf__select-wrap.is-open').forEach(function (openWrap) {
                                if (openWrap !== wrap) {
                                    openWrap.classList.remove('is-open', 'is-drop-up');
                                    openWrap.querySelector('.ft-pf__select-menu').hidden = true;
                                    openWrap.querySelector('.ft-pf__select-trigger').setAttribute('aria-expanded', 'false');
                                }
                            });

                            var opening = menu.hidden;
                            menu.hidden = !opening;
                            wrap.classList.toggle('is-open', opening);
                            if (opening) {
                                positionSelectMenu(wrap, menu);
                            } else {
                                wrap.classList.remove('is-drop-up');
                            }
                            trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
                        }

                        if (event.target.closest('.ft-pf__filter-toggle')) {
                            openFilterPanel();
                            return;
                        }

                        if (event.target.closest('.ft-pf__panel-close') || event.target.closest('.ft-pf__backdrop')) {
                            closeFilterPanel();
                            return;
                        }

                        if (option) {
                            var optionWrap = option.closest('.ft-pf__select-wrap');
                            var input = optionWrap.querySelector('.ft-pf__select-value');
                            var current = optionWrap.querySelector('.ft-pf__select-current');
                            var optionMenu = optionWrap.querySelector('.ft-pf__select-menu');
                            var optionTrigger = optionWrap.querySelector('.ft-pf__select-trigger');

                            input.value = option.dataset.value || '';
                            current.textContent = option.textContent;
                            optionWrap.querySelectorAll('.ft-pf__select-option').forEach(function (item) {
                                item.classList.toggle('is-selected', item === option);
                            });
                            optionMenu.hidden = true;
                            optionWrap.classList.remove('is-open', 'is-drop-up');
                            optionTrigger.setAttribute('aria-expanded', 'false');
                            apply(true);
                        }
                    });
                    document.addEventListener('click', function (event) {
                        if (root.contains(event.target)) return;
                        root.querySelectorAll('.ft-pf__select-wrap.is-open').forEach(function (wrap) {
                            wrap.classList.remove('is-open', 'is-drop-up');
                            wrap.querySelector('.ft-pf__select-menu').hidden = true;
                            wrap.querySelector('.ft-pf__select-trigger').setAttribute('aria-expanded', 'false');
                        });
                    });
                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && root.classList.contains('is-filter-open')) {
                            closeFilterPanel();
                        }
                    });
                    root.querySelector('.ft-pf__clear').addEventListener('click', function () {
                        root.querySelectorAll('input[type="checkbox"]').forEach(function (input) { input.checked = false; });
                        root.querySelectorAll('.ft-pf__search-input').forEach(function (input) { input.value = ''; });
                        root.querySelectorAll('.ft-pf__select-wrap').forEach(function (wrap) {
                            var input = wrap.querySelector('.ft-pf__select-value');
                            var current = wrap.querySelector('.ft-pf__select-current');
                            var first = wrap.querySelector('.ft-pf__select-option');
                            input.value = '';
                            if (first) current.textContent = first.textContent;
                            wrap.querySelectorAll('.ft-pf__select-option').forEach(function (option, index) {
                                option.classList.toggle('is-selected', index === 0);
                            });
                            wrap.classList.remove('is-open', 'is-drop-up');
                            wrap.querySelector('.ft-pf__select-menu').hidden = true;
                            wrap.querySelector('.ft-pf__select-trigger').setAttribute('aria-expanded', 'false');
                        });
                        apply(true);
                    });
                    if (loadMore) {
                        loadMore.addEventListener('click', function () {
                            visibleLimit += perPage;
                            grid.innerHTML = lastFiltered.slice(0, visibleLimit).map(renderCard).join('');
                            observeCardImages();
                            loadMore.hidden = visibleLimit >= lastFiltered.length;
                        });
                    }

                    apply();
                }

                function initAll() {
                    document.querySelectorAll('.ft-pf').forEach(init);
                }

                window.ftProductFilterInit = init;
                window.ftProductFilterInitAll = initAll;

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initAll);
                } else {
                    initAll();
                }
            }());
        </script>
        <?php
    }

    ?>
    <div id="<?php echo esc_attr($instance_id); ?>" class="ft-pf" data-layout="<?php echo esc_attr($layout); ?>" data-per-page="<?php echo esc_attr($per_page); ?>" data-initial-category="<?php echo esc_attr(sanitize_title($atts['category'])); ?>" data-fixed-category="<?php echo esc_attr($fixed_category); ?>" style="<?php echo esc_attr('--ft-pf-columns:' . $columns . ';--ft-pf-gap:' . $gap . 'px;'); ?>">
        <script type="application/json"><?php echo wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
        <button class="ft-pf__filter-toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr($instance_id . '-panel'); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18"></path><path d="M6 12h12"></path><path d="M10 19h4"></path></svg>
            <span><?php esc_html_e('Filter', 'floors-today'); ?></span>
        </button>
        <span class="ft-pf__backdrop" aria-hidden="true"></span>
        <aside id="<?php echo esc_attr($instance_id . '-panel'); ?>" class="ft-pf__panel" aria-label="<?php esc_attr_e('Product filters', 'floors-today'); ?>">
            <div class="ft-pf__top">
                <div class="ft-pf__heading">
                    <h2 class="ft-pf__title"><?php esc_html_e('Filter Products', 'floors-today'); ?></h2>
                    <span class="ft-pf__count"></span>
                </div>
                <button class="ft-pf__clear" type="button"><?php esc_html_e('Reset', 'floors-today'); ?></button>
                <button class="ft-pf__panel-close" type="button" aria-label="<?php esc_attr_e('Close filters', 'floors-today'); ?>">&times;</button>
            </div>
            <div class="ft-pf__controls">
                <?php ft_pf_render_search($instance_id); ?>
                <?php if ($layout === 'horizontal') : ?>
                    <?php if (!$hide_category) : ?>
                        <?php ft_pf_render_select($instance_id, 'categories', 'Category', $data['options']['categories']); ?>
                    <?php endif; ?>
                    <?php foreach ($data['fields'] as $key => $label) :
                        ft_pf_render_select($instance_id, $key, $label, $data['options'][$key]);
                    endforeach; ?>
                <?php else : ?>
                    <?php if (!$hide_category) : ?>
                        <?php ft_pf_render_checks('categories', 'Categories', $data['options']['categories']); ?>
                    <?php endif; ?>
                    <?php foreach ($data['fields'] as $key => $label) :
                        ft_pf_render_checks($key, $label, $data['options'][$key]);
                    endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>
        <section class="ft-pf__results">
            <div class="ft-pf__grid"></div>
            <div class="ft-pf__load-wrap">
                <button class="ft-pf__load-more" type="button"><?php esc_html_e('Load More', 'floors-today'); ?></button>
            </div>
            <div class="ft-pf__empty"><?php esc_html_e('No products match those filters.', 'floors-today'); ?></div>
        </section>
    </div>
    <script>
        if (window.ftProductFilterInit) {
            window.ftProductFilterInit(document.getElementById(<?php echo wp_json_encode($instance_id); ?>));
        }
    </script>
    <?php

    return ob_get_clean();
}

function ft_pf_render_search($instance_id) {
    ?>
    <div class="ft-pf__field ft-pf__field--search">
        <label class="ft-pf__label" for="<?php echo esc_attr($instance_id . '-product-search'); ?>"><?php esc_html_e('Search Product', 'floors-today'); ?></label>
        <input id="<?php echo esc_attr($instance_id . '-product-search'); ?>" class="ft-pf__search-input" type="search" autocomplete="off" placeholder="<?php esc_attr_e('Enter product name', 'floors-today'); ?>" data-filter-search="title">
    </div>
    <?php
}

function ft_pf_render_checks($key, $label, $options) {
    if (empty($options)) {
        return;
    }
    ?>
    <div class="ft-pf__field">
        <span class="ft-pf__label"><?php echo esc_html($label); ?></span>
        <div class="ft-pf__checks">
            <?php foreach ($options as $option) : ?>
                <label class="ft-pf__check">
                    <span class="ft-pf__checkbox-anim">
                        <input type="checkbox" data-filter="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($option['slug']); ?>">
                        <svg viewBox="0 0 35.6 35.6" aria-hidden="true" focusable="false">
                            <circle class="background" cx="17.8" cy="17.8" r="17.8"></circle>
                            <circle class="stroke" cx="17.8" cy="17.8" r="14.37"></circle>
                            <polyline class="check" points="11.78 18.12 15.55 22.23 25.17 12.87"></polyline>
                        </svg>
                    </span>
                    <span><?php echo esc_html($option['label']); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function ft_pf_render_select($instance_id, $key, $label, $options) {
    if (empty($options)) {
        return;
    }
    ?>
    <div class="ft-pf__field">
        <label class="ft-pf__label" for="<?php echo esc_attr($instance_id . '-' . $key); ?>"><?php echo esc_html($label); ?></label>
        <div class="ft-pf__select-wrap">
            <button
                id="<?php echo esc_attr($instance_id . '-' . $key); ?>"
                class="ft-pf__select-trigger"
                type="button"
                aria-expanded="false"
            >
                <span class="ft-pf__select-current"><?php echo esc_html(sprintf('All %s', strtolower($label))); ?></span>
            </button>
            <input class="ft-pf__select-value" type="hidden" data-filter="<?php echo esc_attr($key); ?>" value="">
            <div class="ft-pf__select-menu" hidden>
                <button class="ft-pf__select-option is-selected" type="button" data-value=""><?php echo esc_html(sprintf('All %s', strtolower($label))); ?></button>
                <?php foreach ($options as $option) : ?>
                    <button class="ft-pf__select-option" type="button" data-value="<?php echo esc_attr($option['slug']); ?>"><?php echo esc_html($option['label']); ?></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}

add_shortcode('floors_product_filter', 'ft_pf_shortcode');
add_shortcode('floors_product_filter_vertical', function ($atts) {
    $atts = is_array($atts) ? $atts : [];
    $atts['layout'] = 'vertical';
    return ft_pf_shortcode($atts);
});
add_shortcode('floors_product_filter_horizontal', function ($atts) {
    $atts = is_array($atts) ? $atts : [];
    $atts['layout'] = 'horizontal';
    return ft_pf_shortcode($atts);
});
add_shortcode('floors_category_product_filter', function ($atts) {
    $atts = is_array($atts) ? $atts : [];
    $queried_object = get_queried_object();

    if (
        empty($atts['category'])
        && $queried_object instanceof WP_Term
        && $queried_object->taxonomy === 'categories'
    ) {
        $atts['category'] = $queried_object->slug;
    }

    $atts['hide_category'] = 'yes';
    $atts['layout'] = $atts['layout'] ?? 'horizontal';

    return ft_pf_shortcode($atts);
});





