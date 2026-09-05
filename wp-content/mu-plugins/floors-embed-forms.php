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
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', function () {
    $embed = isset($_GET['ft_embed']) ? sanitize_key(wp_unslash($_GET['ft_embed'])) : '';

    if (!in_array($embed, ['booking', 'contact'], true)) {
        return;
    }

    status_header(200);
    nocache_headers();
    header_remove('X-Frame-Options');
    header('Content-Type: text/html; charset=UTF-8');

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
