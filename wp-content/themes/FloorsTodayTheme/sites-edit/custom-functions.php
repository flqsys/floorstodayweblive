<?php
/**
 * Plugin Name: Custom Functions
 * Description: Site-specific snippets loaded automatically by WordPress.
 * Version: 1.0.0
 * Author: Faris
 */

if (!defined('ABSPATH')) {
    exit;
}

// Hide Astra fallback header.
add_action('wp', function () {
    if (!is_admin()) {
        remove_all_actions('astra_header');
    }
}, 100);

// Load admin-only custom CSS.
function ft_enqueue_custom_styles() {
    $custom_css = get_stylesheet_directory() . '/assets/css/custom.css';

    wp_enqueue_style(
        'ft-site-custom',
        get_stylesheet_directory_uri() . '/assets/css/custom.css',
        [],
        is_readable($custom_css) ? filemtime($custom_css) : null
    );
}

add_action('admin_enqueue_scripts', 'ft_enqueue_custom_styles');

// Keep public WordPress pages readable without loading admin cleanup CSS on the frontend.
function ft_enqueue_frontend_styles() {
    $css = '
        body:not(.elementor-template-canvas) .site-header,
        body:not(.elementor-template-canvas) .site-footer {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100%;
            background: #fff;
            color: #111827;
        }

        body:not(.elementor-template-canvas) .site-header {
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 0 rgba(17, 24, 39, 0.04);
        }

        body:not(.elementor-template-canvas) .site-footer {
            border-top: 1px solid #e5e7eb;
        }

        body:not(.elementor-template-canvas) .site-header .header-inner,
        body:not(.elementor-template-canvas) .site-footer .footer-inner {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            max-width: 1180px;
            margin: 0 auto;
            padding: 18px 24px;
            gap: 24px;
        }

        body:not(.elementor-template-canvas) .site-header .custom-logo,
        body:not(.elementor-template-canvas) .site-footer .custom-logo {
            display: block;
            width: auto;
            max-width: 220px;
            height: auto;
            max-height: 62px;
            object-fit: contain;
        }

        body:not(.elementor-template-canvas) .site-footer .copyright p {
            margin: 0;
            color: #4b5563;
            font-size: 15px;
        }

        body:not(.elementor-template-canvas) .site-main,
        .page-content {
            width: 100%;
        }

body.elementor-page:not(.elementor-template-canvas) .site-main {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

body:not(.elementor-page):not(.elementor-template-canvas) .site-main {
    max-width: 1180px;
    margin: 0 auto;
    padding: 48px 24px 72px;
}

        .page-header {
            margin-bottom: 24px;
        }

        .entry-title {
            margin: 0;
            color: #111827;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(32px, 5vw, 54px);
            line-height: 1.05;
        }

        .ft-checkout-page {
            max-width: 880px;
            margin: 0;
            padding: 36px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 18px 40px rgba(17, 24, 39, 0.08);
        }

        .ft-checkout-page h1 {
            margin: 0 0 14px;
            color: #111827;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(30px, 4vw, 46px);
            line-height: 1.08;
        }

        .ft-checkout-page p {
            max-width: 680px;
            margin: 0 0 28px;
            color: #4b5563;
            font-size: 18px;
            line-height: 1.65;
        }

        .ft-checkout-page .wp-block-buttons {
            gap: 14px;
        }

        .ft-checkout-page .wp-block-button__link {
            border-radius: 6px;
            background: #1e66ac;
            color: #fff;
            font-weight: 700;
            text-decoration: none;
        }

        .ft-checkout-page .is-style-outline .wp-block-button__link {
            border: 1px solid #1e66ac;
            background: transparent;
            color: #1e66ac;
        }

        @media (min-width: 768px) {
            html,
            html body.admin-bar {
                margin-top: 0 !important;
            }

            body.admin-bar .ft-sh-header {
                top: 0 !important;
            }

            body.admin-bar #wpadminbar {
                display: none !important;
            }
        }
        @media (max-width: 767px) {
            body:not(.elementor-template-canvas) .site-header .header-inner,
            body:not(.elementor-template-canvas) .site-footer .footer-inner {
                padding: 14px 16px;
            }

            body:not(.elementor-template-canvas) .site-header .custom-logo,
            body:not(.elementor-template-canvas) .site-footer .custom-logo {
                max-width: 180px;
                max-height: 52px;
            }

            body:not(.elementor-template-canvas) .site-main {
                padding: 32px 16px 56px;
            }

            .ft-checkout-page {
                padding: 24px 18px;
            }
        }
    ';

    wp_register_style('ft-frontend-fixes', false, [], null);
    wp_enqueue_style('ft-frontend-fixes');
    wp_add_inline_style('ft-frontend-fixes', $css);
}

add_action('wp_enqueue_scripts', 'ft_enqueue_frontend_styles');

// Use the same theme header/footer across the frontend, except 404 and Elementor Canvas.
function ft_is_elementor_edit_context() {
    if (is_admin() || wp_doing_ajax()) {
        return true;
    }

    if (isset($_GET['elementor-preview'])) {
        return true;
    }

    if (is_singular('elementor_library')) {
        return true;
    }

    return (
        class_exists('\Elementor\Plugin') &&
        isset(\Elementor\Plugin::$instance->editor) &&
        \Elementor\Plugin::$instance->editor->is_edit_mode()
    );
}

function ft_uses_elementor_canvas_template() {
    if (!is_singular()) {
        return false;
    }

    $template = get_page_template_slug(get_queried_object_id());

    return in_array($template, [
        'elementor_canvas',
        'elementor_canvas.php',
        'templates/canvas.php',
    ], true);
}

function ft_should_show_global_header_footer() {
    if (ft_is_elementor_edit_context() || is_404() || ft_uses_elementor_canvas_template()) {
        return false;
    }

    if (function_exists('is_front_page') && is_front_page()) {
        return false;
    }

    if (defined('REST_REQUEST') && REST_REQUEST) {
        return false;
    }

    return true;
}

add_filter('hello_elementor_header_footer', 'ft_should_show_global_header_footer', 20);

add_action('wp', function () {
    if (!ft_should_show_global_header_footer() || !function_exists('elementor_theme_do_location')) {
        return;
    }

    add_filter('elementor/theme/get_location_templates/template_id', function ($template_id, $location) {
        if (in_array($location, ['header', 'footer'], true)) {
            return 0;
        }

        return $template_id;
    }, 20, 2);
});

// Hide WooCommerce marketing menu.
function wc_hide_marketing_woocommerce_menus() {
    remove_menu_page('woocommerce-marketing');
    remove_submenu_page('woocommerce-marketing', 'admin.php?page=wc-admin&path=/marketing');
    remove_submenu_page('woocommerce-marketing', 'edit.php?post_type=shop_coupon');
}

add_action('admin_menu', 'wc_hide_marketing_woocommerce_menus', 999);

// Disable comments support in admin.
add_action('admin_init', function () {
    foreach (get_post_types([], 'names') as $post_type) {
        remove_post_type_support($post_type, 'comments');
        remove_post_type_support($post_type, 'trackbacks');
    }
});

// Remove comments menu.
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
}, 999);

// Remove comments from admin bar.
add_action('admin_bar_menu', function ($wp_admin_bar) {
    if ($wp_admin_bar) {
        $wp_admin_bar->remove_node('comments');
    }
}, 999);

// Redirect comment feeds.
add_action('template_redirect', function () {
    if (is_comment_feed()) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
});

// Change admin-bar account greeting.
add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;

    if (!$wp_admin_bar) {
        return;
    }

    $account = $wp_admin_bar->get_node('my-account');

    if (!$account) {
        return;
    }

    $wp_admin_bar->add_node([
        'id'    => 'my-account',
        'title' => preg_replace('/Howdy,?\s*/i', 'Hey! ', $account->title, 1),
    ]);
}, 999);

// Add copyright below Lara Analytics menu.
add_action('admin_footer', function () {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var menuLinks = document.querySelectorAll('#adminmenu a');
        var laraLink = Array.from(menuLinks).find(function (link) {
            return /lara|analytics/i.test(link.textContent || '');
        });

        if (!laraLink || document.getElementById('ft-admin-sidebar-copyright')) {
            return;
        }

        var menuItem = laraLink.closest('li');

        if (!menuItem || !menuItem.parentNode) {
            return;
        }

        var copyright = document.createElement('li');
        copyright.id = 'ft-admin-sidebar-copyright';
        copyright.className = 'wp-not-current-submenu';
        copyright.innerHTML =
            '<a class="ft-admin-sidebar-copyright" href="https://xdeye.com" target="_blank" rel="noopener noreferrer">' +
            '&copy; ' + new Date().getFullYear() +
            ' XDEYE Digital. All rights reserved.</a>';

        menuItem.parentNode.insertBefore(copyright, menuItem.nextSibling);
    });
    </script>
    <?php
});

// Format active theme description.
add_action('admin_footer-themes.php', function () {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function formatThemeDescription() {
            document.querySelectorAll('.theme-overlay .theme-description').forEach(function (description) {
                var marker = 'About XDEYE:';
                var text = description.textContent || '';

                if (description.dataset.ftXdeyeFormatted || text.indexOf(marker) === -1) {
                    return;
                }

                var parts = text.split(marker);
                var intro = parts.shift().trim();
                var about = parts.join(marker).trim();

                description.textContent = '';
                description.appendChild(document.createTextNode(intro));
                description.appendChild(document.createElement('br'));
                description.appendChild(document.createElement('br'));

                var label = document.createElement('strong');
                label.textContent = marker;

                description.appendChild(label);
                description.appendChild(document.createTextNode(' ' + about));
                description.dataset.ftXdeyeFormatted = '1';
            });
        }

        formatThemeDescription();

        new MutationObserver(formatThemeDescription).observe(document.body, {
            childList: true,
            subtree: true
        });
    });
    </script>
    <?php
});

// Change toolbar site-name link.
add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;

    if (!$wp_admin_bar) {
        return;
    }

    $site_node = $wp_admin_bar->get_node('site-name');

    if (!$site_node) {
        return;
    }

    $wp_admin_bar->add_node([
        'id'    => 'site-name',
        'title' => '<span class="ab-icon dashicons dashicons-visibility" aria-hidden="true"></span><span class="ab-label">Visit Floors Today</span>',
        'href'  => home_url('/'),
        'meta'  => $site_node->meta,
    ]);
}, 999);

// Keep the front-end header flush to the top for logged-in users.
add_action('after_setup_theme', function () {
    if (!is_admin()) {
        remove_action('wp_head', '_admin_bar_bump_cb');
    }
}, 20);

add_action('wp_head', function () {
    if (is_admin()) {
        return;
    }
    ?>
    <style id="ft-front-header-gap-guard">
        html,
        html body,
        html body.admin-bar {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        body {
            padding-top: 0 !important;
        }
        body.admin-bar #wpadminbar {
            display: none !important;
        }
        body.admin-bar .ft-sh-header,
        body.admin-bar .ft-homepage-shell > header,
        .ft-sh-header,
        .ft-homepage-shell > header {
            top: 0 !important;
            margin-top: 0 !important;
        }
        @media (max-width: 640px) {
            body:has(.ft-sh-header) {
                padding-top: 101px !important;
            }
            body:has(.ft-homepage-shell > header) {
                padding-top: 104px !important;
            }
        }
    </style>
    <?php
}, 9999);
