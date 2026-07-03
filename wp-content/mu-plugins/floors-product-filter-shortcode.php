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

    if (is_numeric($catalog_image)) {
        $url = wp_get_attachment_image_url((int) $catalog_image, 'full');
        if ($url) {
            return $url;
        }
    }

    if (is_string($catalog_image) && preg_match('#^https?://#i', $catalog_image)) {
        return esc_url_raw($catalog_image);
    }

    $thumb = get_the_post_thumbnail_url($post_id, 'full');
    return $thumb ?: '';
}

function ft_pf_build_data($atts) {
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
    $data = ft_pf_build_data($atts);
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
                    font-size: 12px;
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

                function renderCard(product) {
                    var image = product.image
                        ? '<span class="ft-pf__image" role="img" aria-label="' + escapeHtml(product.title) + '" style="background-image:url(&quot;' + escapeHtml(product.image) + '&quot;)"></span>'
                        : '<span class="ft-pf__image-placeholder"></span>';

                    return '<a class="ft-pf__card" href="' + escapeHtml(product.url) + '">' + image + '<div class="ft-pf__card-content"><h3 class="ft-pf__card-title">' + escapeHtml(product.title) + '</h3></div></a>';
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

                    function apply() {
                        visibleLimit = perPage;
                        render();
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

                        grid.innerHTML = lastFiltered.slice(0, visibleLimit).map(renderCard).join('');
                        count.innerHTML = '<i class="fa-solid fa-box-archive" aria-hidden="true"></i><span>' + lastFiltered.length + ' product' + (lastFiltered.length === 1 ? '' : 's') + '</span>';
                        root.classList.toggle('is-empty', lastFiltered.length === 0);
                        if (loadMore) loadMore.hidden = visibleLimit >= lastFiltered.length;
                    }

                    root.addEventListener('input', apply);
                    root.addEventListener('change', apply);
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
                            apply();
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
                        apply();
                    });
                    if (loadMore) {
                        loadMore.addEventListener('click', function () {
                            visibleLimit += perPage;
                            grid.innerHTML = lastFiltered.slice(0, visibleLimit).map(renderCard).join('');
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





