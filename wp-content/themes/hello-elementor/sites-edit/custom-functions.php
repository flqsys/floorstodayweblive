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

// Custom site code by Faris.

// Hide Astra's fallback header on public WordPress pages. Added by Faris.
// Elementor Theme Builder headers can still be restored later by removing this snippet.
add_action('wp', function () {
    if (!is_admin()) {
        remove_all_actions('astra_header');
    }
}, 100);

// Load the shared custom stylesheet on public WordPress pages.
add_action('wp_enqueue_scripts', function () {
    $custom_css = __DIR__ . '/custom.css';

    wp_enqueue_style(
        'ft-site-custom',
        plugin_dir_url(__FILE__) . 'custom.css',
        [],
        is_readable($custom_css) ? (string) filemtime($custom_css) : null
    );
});

// Hide WooCommerce marketing menu items.
add_filter('woocommerce_marketing_menu_items', '__return_empty_array');

function wc_hide_marketing_woocommerce_menus() {
    remove_menu_page('woocommerce-marketing');
    remove_submenu_page(
        'woocommerce-marketing',
        'admin.php?page=wc-admin&path=/marketing'
    );
    remove_submenu_page(
        'woocommerce-marketing',
        'edit.php?post_type=shop_coupon'
    );
}

add_action('admin_menu', 'wc_hide_marketing_woocommerce_menus', 999);

// Disable WordPress comments everywhere without deleting existing comments.
add_action('admin_init', function () {
    foreach (get_post_types([], 'names') as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
        }

        if (post_type_supports($post_type, 'trackbacks')) {
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('comments_array', '__return_empty_array', 10, 2);
add_filter('xmlrpc_methods', function ($methods) {
    unset($methods['wp.newComment']);
    return $methods;
});

add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
}, 999);

add_action('admin_bar_menu', function ($wp_admin_bar) {
    $wp_admin_bar->remove_node('comments');
}, 999);

add_action('template_redirect', function () {
    if (is_comment_feed()) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
});

add_filter('rest_endpoints', function ($endpoints) {
    foreach (array_keys($endpoints) as $route) {
        if (str_starts_with($route, '/wp/v2/comments')) {
            unset($endpoints[$route]);
        }
    }

    return $endpoints;
});

// Change the WordPress admin-bar greeting from "Howdy" to "Hey!".
add_filter('gettext', function ($translated, $text, $domain) {
    if ($text === 'Howdy, %s') {
        return 'Hey! %s';
    }

    return $translated;
}, 20, 3);

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
        'id' => 'my-account',
        'title' => preg_replace('/Howdy,?\s*/i', 'Hey! ', $account->title, 1),
    ]);
}, 999);

// Add a small copyright line below the Lara Analytics admin menu item.
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
          '<a class="ft-admin-sidebar-copyright" href=<?php echo wp_json_encode(home_url('/')); ?>>' +
          '&copy; ' + new Date().getFullYear() +
          ' Floors Today. All rights reserved.</a>';

        menuItem.parentNode.insertBefore(copyright, menuItem.nextSibling);
      });
    </script>
    <?php
});

// Make the toolbar website link clear and add a visibility icon.
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
        'id' => 'site-name',
        'title' => '<span class="ab-icon dashicons dashicons-visibility" aria-hidden="true"></span><span class="ab-label">Visit Floors Today</span>',
        'href' => home_url('/'),
        'meta' => $site_node->meta,
    ]);
}, 999);
// Hide WooCommerce prices across product, cart, and checkout views. Added by Faris.
add_filter('woocommerce_get_price_html', '__return_empty_string');
add_filter('woocommerce_cart_item_price', '__return_empty_string');
add_filter('woocommerce_cart_item_subtotal', '__return_empty_string');
