<?php
/**
 * TEMPORARY tool - exports all ft_inbox_lead posts + meta as JSON so they
 * can be merged into the local dev database. Delete this file once the
 * export is done; it's not meant to stay installed long-term.
 */

defined('ABSPATH') or exit;

add_action('admin_post_ft_export_leads', 'ft_export_leads_handler');

function ft_export_leads_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Not allowed.', 403);
    }
    check_admin_referer('ft_export_leads');

    $posts = get_posts([
        'post_type'      => 'ft_inbox_lead',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ]);

    $export = [];
    foreach ($posts as $post) {
        $meta = get_post_meta($post->ID);
        $flatMeta = [];
        foreach ($meta as $key => $values) {
            $flatMeta[$key] = $values[0] ?? '';
        }
        $export[] = [
            'ID'         => $post->ID,
            'post_title' => $post->post_title,
            'post_date'  => $post->post_date,
            'post_status'=> $post->post_status,
            'meta'       => $flatMeta,
        ];
    }

    nocache_headers();
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="ft-leads-export-' . date('Y-m-d-His') . '.json"');
    echo wp_json_encode(['count' => count($export), 'leads' => $export], JSON_PRETTY_PRINT);
    exit;
}

add_action('admin_notices', function () {
    if (!current_user_can('manage_options')) {
        return;
    }
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'ft-leads') === false) {
        return;
    }
    $url = wp_nonce_url(admin_url('admin-post.php?action=ft_export_leads'), 'ft_export_leads');
    echo '<div class="notice notice-info"><p><a href="' . esc_url($url) . '" class="button button-primary">Download Leads Export (JSON)</a> - temporary tool, remove floors-leads-export-tool.php from mu-plugins when done.</p></div>';
});
