<?php
/**
 * Plugin Name: Floors Today Contact Form (phone slide-up)
 * Description: [floors_booking_form_contact_sheet] - the contact form; on phones a button slides it up as a sheet (same as the newsletter form). Larger screens show it inline as before.
 * Version: 1.0.0
 * Author: xdeye.com
 *
 * Wraps [floors_booking_form_contact] (floors-form-variants.php), so the fields,
 * checks and leads are exactly the same.
 *
 * Usage:
 *   [floors_booking_form_contact_sheet]
 *   [floors_booking_form_contact_sheet button="Contact Us" title="Get in Touch"]
 */

if (!defined('ABSPATH')) {
    exit;
}

function ft_next_contact_form_sheet_shortcode($atts = []) {
    static $assets_printed = false;

    if (!function_exists('ft_next_booking_form_contact_shortcode')) {
        return '';
    }

    $atts = shortcode_atts([
        'button' => 'Get in Touch',
        'title'  => 'Get in Touch',
    ], $atts, 'floors_booking_form_contact_sheet');

    $settings  = function_exists('ft_next_homepage_settings') ? ft_next_homepage_settings() : [];
    $secondary = sanitize_hex_color($settings['secondary_color'] ?? '') ?: '#cc9c2e';
    $id        = wp_unique_id('ft-cfs-');
    $form      = ft_next_booking_form_contact_shortcode([]);

    ob_start();

    if (!$assets_printed) {
        $assets_printed = true;
        ?>
        <style id="ft-cfs-styles">
            /* Larger screens: the wrappers vanish and the form shows inline as before */
            .ft-cfs-open, .ft-cfs-backdrop, .ft-cfs-head { display: none; }
            .ft-cfs-sheet, .ft-cfs-panel, .ft-cfs-body { display: contents; }
            @media (max-width: 640px) {
                .ft-cfs-open { display: flex; justify-content: center; margin: 18px 0 0; }
                .ft-cfs-open-btn {
                    display: flex !important; align-items: center !important; justify-content: center !important; gap: 8px;
                    width: 100% !important; height: 52px !important; margin: 0 !important; padding: 0 20px !important;
                    border: none !important; border-radius: 999px !important;
                    background: var(--ft-cfs-btn) !important; color: #fff !important;
                    font-family: inherit; font-size: 17px !important; font-weight: 700 !important; line-height: 1 !important;
                    text-transform: none !important; letter-spacing: 0 !important; cursor: pointer;
                    box-shadow: 0 10px 24px -8px color-mix(in srgb, var(--ft-cfs-btn) 60%, transparent);
                }
                .ft-cfs-sheet { display: block; position: fixed; inset: 0; z-index: 99990; visibility: hidden; pointer-events: none; }
                .ft-cfs-sheet.is-open { visibility: visible; pointer-events: auto; }
                .ft-cfs-backdrop { display: block; position: absolute; inset: 0; background: rgba(0, 0, 0, .5); opacity: 0; transition: opacity .3s; }
                .ft-cfs-sheet.is-open .ft-cfs-backdrop { opacity: 1; }
                .ft-cfs-panel {
                    display: flex; flex-direction: column; position: absolute; left: 0; right: 0; bottom: 0; max-height: 92dvh;
                    background: #fff; border-radius: 16px 16px 0 0; box-shadow: 0 -10px 40px rgba(0, 0, 0, .25);
                    transform: translateY(100%); transition: transform .3s ease-out; outline: none;
                }
                .ft-cfs-sheet.is-open .ft-cfs-panel { transform: translateY(0); }
                .ft-cfs-head {
                    display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                    padding: 16px 16px 12px; border-bottom: 1px solid #e5e7eb;
                }
                .ft-cfs-title { margin: 0 !important; color: #111827 !important; font-size: 19px !important; font-weight: 700 !important; line-height: 1.25 !important; }
                .ft-cfs-close {
                    all: unset; flex-shrink: 0; display: flex !important; align-items: center; justify-content: center;
                    width: 36px; height: 36px; border: 1px solid #e5e7eb; border-radius: 50%;
                    color: #111827; font-size: 22px; line-height: 1; cursor: pointer;
                }
                .ft-cfs-body { display: block; overflow-y: auto; overscroll-behavior: contain; padding: 14px 12px calc(16px + env(safe-area-inset-bottom)); }
                /* Inside the sheet: no card border/shadow, and the title is already in the sheet header */
                .ft-cfs-body .ft-cf { margin: 0 !important; border: 0 !important; box-shadow: none !important; padding: 4px !important; }
                .ft-cfs-body .ft-cf__heading { display: none; }
            }
        </style>
        <script id="ft-cfs-script">
            window.ftContactSheet = window.ftContactSheet || (function () {
                var phone = window.matchMedia('(max-width: 640px)');
                var saved = { overflow: '', chat: '' };
                var openSheet = null, opener = null;
                function chatEl() { return document.getElementById('chatbot-container'); }
                function open(sheet, fromBtn) {
                    if (!sheet || !phone.matches) return;
                    // Elementor sections with animations (transform) would trap a
                    // fixed sheet inside them; move it to <body> the first time.
                    if (sheet.parentNode !== document.body) {
                        sheet.__ftHome = sheet.parentNode;
                        var styleVar = sheet.parentNode && sheet.parentNode.style ? sheet.parentNode.style.getPropertyValue('--ft-cfs-btn') : '';
                        if (styleVar) sheet.style.setProperty('--ft-cfs-btn', styleVar);
                        document.body.appendChild(sheet);
                    }
                    openSheet = sheet; opener = fromBtn || null;
                    var panel = sheet.querySelector('.ft-cfs-panel');
                    panel.setAttribute('role', 'dialog');
                    panel.setAttribute('aria-modal', 'true');
                    sheet.classList.add('is-open');
                    if (opener) opener.setAttribute('aria-expanded', 'true');
                    saved.overflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                    var chat = chatEl();
                    if (chat) { saved.chat = chat.style.visibility; chat.style.visibility = 'hidden'; }
                    setTimeout(function () { panel.focus({ preventScroll: true }); }, 50);
                }
                function close() {
                    if (!openSheet) return;
                    var panel = openSheet.querySelector('.ft-cfs-panel');
                    openSheet.classList.remove('is-open');
                    panel.removeAttribute('role');
                    panel.removeAttribute('aria-modal');
                    document.body.style.overflow = saved.overflow;
                    var chat = chatEl();
                    if (chat) chat.style.visibility = saved.chat;
                    if (opener) { opener.setAttribute('aria-expanded', 'false'); opener.focus({ preventScroll: true }); }
                    openSheet = null; opener = null;
                }
                // Rotating / resizing past phone width closes the sheet
                // and puts any moved form back in its place on the page
                phone.addEventListener('change', function () {
                    if (phone.matches) return;
                    close();
                    document.querySelectorAll('body > .ft-cfs-sheet').forEach(function (sheet) {
                        if (sheet.__ftHome) sheet.__ftHome.appendChild(sheet);
                    });
                });
                document.addEventListener('click', function (event) {
                    var btn = event.target.closest('[data-cfs-open]');
                    if (btn) { event.preventDefault(); open(document.getElementById(btn.getAttribute('aria-controls')), btn); return; }
                    if (event.target.closest('[data-cfs-close]')) { event.preventDefault(); close(); }
                });
                document.addEventListener('keydown', function (event) { if (event.key === 'Escape') close(); });
                return { open: open, close: close };
            })();
        </script>
        <?php
    }
    ?>
    <div class="ft-cfs" style="--ft-cfs-btn: <?php echo esc_attr($secondary); ?>;">
        <?php /* Phones: this button slides the form up. Hidden on larger screens. */ ?>
        <div class="ft-cfs-open">
            <button type="button" class="ft-cfs-open-btn" data-cfs-open aria-controls="<?php echo esc_attr($id); ?>" aria-expanded="false"><?php echo esc_html($atts['button']); ?> <span aria-hidden="true">&rarr;</span></button>
        </div>
        <div class="ft-cfs-sheet" id="<?php echo esc_attr($id); ?>">
            <div class="ft-cfs-backdrop" data-cfs-close></div>
            <div class="ft-cfs-panel" aria-labelledby="<?php echo esc_attr($id); ?>-title" tabindex="-1">
                <div class="ft-cfs-head">
                    <h3 class="ft-cfs-title" id="<?php echo esc_attr($id); ?>-title"><?php echo esc_html($atts['title']); ?></h3>
                    <button type="button" class="ft-cfs-close" data-cfs-close aria-label="<?php esc_attr_e('Close', 'floors-today'); ?>">&times;</button>
                </div>
                <div class="ft-cfs-body">
                    <?php echo $form; // Already escaped by [floors_booking_form_contact] ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('floors_booking_form_contact_sheet', 'ft_next_contact_form_sheet_shortcode');
