<?php
/**
 * Bare, background-free renders of the booking and contact forms for
 * embedding on other (sub)domains via <iframe>. Bypasses the theme
 * entirely and strips the forms' own card chrome (padding, border,
 * shadow, background) so the iframe shows only the form itself,
 * blending into whatever page it's dropped into.
 *
 * URLs:
 *   https://floorstoday.ca/?ft_embed=booking
 *   https://floorstoday.ca/?ft_embed=contact
 *   https://floorstoday.ca/?ft_embed=utm (admin-only - requires being logged
 *   in as an admin in the same browser; shows a plain message otherwise)
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', function () {
    $embed = isset($_GET['ft_embed']) ? sanitize_key(wp_unslash($_GET['ft_embed'])) : '';

    if (!in_array($embed, ['booking', 'contact', 'utm'], true)) {
        return;
    }

    status_header(200);
    nocache_headers();
    header_remove('X-Frame-Options');
    header('Content-Type: text/html; charset=UTF-8');

    if ($embed === 'utm') {
        ft_embed_render_utm_page();
        exit;
    }

    $shortcode = $embed === 'booking' ? '[floors_booking_form]' : '[floors_booking_form_contact]';
    ?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Floors Today Form</title>
<style>
    html, body {
        margin: 0;
        padding: 0;
        background: transparent !important;
    }
    .ft-bf, .ft-cf,
    .elementor-shortcode > .ft-bf, .elementor-widget-shortcode .ft-bf, .elementor-widget-container > .ft-bf,
    .elementor-shortcode > .ft-cf, .elementor-widget-shortcode .ft-cf, .elementor-widget-container > .ft-cf {
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        border: none !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }
</style>
</head>
<body>
<?php echo do_shortcode($shortcode); ?>
<script>
(function () {
    function reportHeight() {
        var height = document.body.scrollHeight;
        window.parent.postMessage({ ftEmbedHeight: height, ftEmbedType: <?php echo wp_json_encode($embed); ?> }, '*');
    }
    if ('ResizeObserver' in window) {
        new ResizeObserver(reportHeight).observe(document.body);
    } else {
        setInterval(reportHeight, 500);
    }
    window.addEventListener('load', reportHeight);
})();
</script>
</body>
</html>
    <?php
    exit;
}, 1);

/**
 * Standalone (non wp-admin) render of the Social UTM Links table, gated by
 * capability rather than WordPress's admin-only clickjacking header (which
 * can't be selectively disabled for one admin.php?page= value without
 * weakening it for every other admin page sharing that URL). Only works if
 * the viewer already has a logged-in floorstoday.ca admin session in the
 * same browser - otherwise shows a plain "please log in" message.
 */
function ft_embed_render_utm_page() {
    ?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Social UTM Links</title>
<style>
    html, body { margin: 0; padding: 0; background: transparent !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    body { padding: 4px; color: #1d2327; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #e2e4e7; }
    th { font-weight: 600; color: #50575e; }
    .ft-embed-row { display: flex; gap: 6px; align-items: center; }
    .ft-embed-row input { flex: 1; padding: 6px 8px; border: 1px solid #c3c4c7; border-radius: 4px; font: inherit; font-size: 13px; }
    .ft-embed-copy { flex-shrink: 0; padding: 6px 12px; border: 1px solid #2271b1; border-radius: 4px; background: #2271b1; color: #fff; font: inherit; font-size: 13px; cursor: pointer; }
    .ft-embed-copy.is-copied { background: #00a32a; border-color: #00a32a; }
    .ft-embed-denied { padding: 16px; color: #646970; font-size: 14px; }
</style>
</head>
<body>
<?php if (!current_user_can('manage_options')) : ?>
    <p class="ft-embed-denied">Please log in to floorstoday.ca as an admin to view this.</p>
<?php else : ?>
    <table>
        <thead><tr><th>Social</th><th>Tracking link</th></tr></thead>
        <tbody>
        <?php foreach (ft_next_homepage_utm_links() as $utm_link) : ?>
            <tr>
                <td><?php echo esc_html($utm_link['label']); ?></td>
                <td>
                    <div class="ft-embed-row">
                        <input type="text" readonly onclick="this.select();" value="<?php echo esc_attr($utm_link['url']); ?>">
                        <button type="button" class="ft-embed-copy" data-copy="<?php echo esc_attr($utm_link['url']); ?>">Copy</button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<script>
(function () {
    function reportHeight() {
        window.parent.postMessage({ ftEmbedHeight: document.body.scrollHeight, ftEmbedType: 'utm' }, '*');
    }
    if ('ResizeObserver' in window) {
        new ResizeObserver(reportHeight).observe(document.body);
    } else {
        setInterval(reportHeight, 500);
    }
    window.addEventListener('load', reportHeight);

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.ft-embed-copy');
        if (!button) return;
        var value = button.getAttribute('data-copy') || '';
        var done = function () {
            button.classList.add('is-copied');
            button.textContent = 'Copied!';
            setTimeout(function () {
                button.classList.remove('is-copied');
                button.textContent = 'Copy';
            }, 1400);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(value).then(done);
        } else {
            var input = button.previousElementSibling;
            input.select();
            document.execCommand('copy');
            done();
        }
    });
})();
</script>
</body>
</html>
    <?php
}
