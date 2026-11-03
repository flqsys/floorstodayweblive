<?php
/**
 * Plugin Name: Floors Today Light Booking Form - phone sheet
 * Description: Phone part of [floors_booking_form_light]: a button that slides up the same simple form as the homepage phone hero (Details / Address / Confirm).
 * Version: 1.0.0
 * Author: xdeye.com
 *
 * Same fields, checks, texts (Home Settings > Hero > Estimate form layout - phones)
 * and lead details as next-homepage-src/components/mobile-showroom-hero.tsx.
 * Larger screens never see this; they get the 3-step form from
 * floors-booking-form-light.php.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Lead source for this form. Listed in ft_inbox_quick_lead_sources()
// (floors-leads-inbox.php) so a first name is enough, like the homepage phone form.
define('FT_BFL_MOBILE_SOURCE', 'WordPress light booking form (phone)');

function ft_bfl_mobile_sheet_html($id, $settings, $atts) {
    static $assets_printed = false;

    $inbox_settings = function_exists('ft_inbox_settings') ? ft_inbox_settings() : [];
    $phone          = (string) ($settings['phone'] ?? '');
    $phone_href     = preg_replace('/[^\d+]/', '', $phone);
    $map_url        = trim((string) ($settings['mobile_hero_map_url'] ?? ''));
    $map_url        = ($map_url !== '' && $map_url !== '#') ? $map_url : '';
    $open_label     = $atts['button'] !== '' ? $atts['button'] : (string) ($settings['mobile_hero_button'] ?? '');
    $open_label     = $open_label !== '' ? $open_label : 'Book In-Home Consultation';
    $form_title     = (string) ($settings['mobile_form_title'] ?? '') ?: 'Book In-Home Consultation';
    $form_subtitle  = (string) ($settings['mobile_form_subtitle'] ?? '') ?: 'Takes under a minute. No obligation.';
    $form_button    = (string) ($settings['mobile_form_button'] ?? '') ?: 'Book Now';
    $form_note      = (string) ($settings['mobile_form_note'] ?? '');
    $sheet_id       = $id . '-m';
    $floors         = ['Hardwood', 'Engineered', 'Laminate', 'Vinyl', 'Carpet', 'Not sure yet'];
    $provinces      = [
        'AB' => 'Alberta', 'BC' => 'British Columbia', 'MB' => 'Manitoba', 'NB' => 'New Brunswick',
        'NL' => 'Newfoundland and Labrador', 'NS' => 'Nova Scotia', 'NT' => 'Northwest Territories',
        'NU' => 'Nunavut', 'ON' => 'Ontario', 'PE' => 'Prince Edward Island', 'QC' => 'Quebec',
        'SK' => 'Saskatchewan', 'YT' => 'Yukon',
    ];
    $consents = [
        'privacyConsent' => 'I agree to receive promotional emails from Floors Today and have read the <a href="' . esc_url(home_url('/privacy-policy/')) . '">Privacy Policy</a> and <a href="' . esc_url(home_url('/terms-of-use/')) . '">Terms &amp; Conditions</a>.',
        'smsConsent'     => 'I agree to receive SMS marketing and informational messages from Floors Today at the contact information provided above. Message frequency may vary. Message &amp; data rates may apply. Reply STOP to unsubscribe or HELP for assistance.',
        'emailConsent'   => 'I agree to receive email marketing communications from Floors Today at the email address provided above. I understand Floors Today may respond to any messages or emails I send.',
    ];

    ob_start();

    if (!$assets_printed) {
        $assets_printed = true;
        ?>
        <style id="ft-bflm-styles">
            /* Larger screens: none of this shows (the 3-step form does) */
            .ft-bflm-open,.ft-bflm-sheet{display:none!important;}
            @media(max-width:640px){
                /* Phones: the page shows only the button; the 3-step card hides */
                .ft-bfl-wrap--sheet > .ft-bfl{display:none!important;}
                .ft-bflm-open{display:flex!important;justify-content:center;margin:18px 0 0;}
                .ft-bfl-wrap button.ft-bflm-open-btn{display:flex!important;align-items:center!important;justify-content:center!important;gap:8px!important;width:100%!important;max-width:100%!important;height:56px!important;min-height:0!important;margin:0!important;padding:0 20px!important;border:0!important;border-radius:8px!important;background:var(--bfl-btn)!important;background-image:none!important;color:var(--bfl-btn-text)!important;font-family:inherit!important;font-size:17px!important;font-weight:700!important;line-height:1.2!important;letter-spacing:0!important;text-transform:none!important;cursor:pointer;box-shadow:0 10px 15px -3px color-mix(in srgb,var(--bfl-btn) 30%,transparent)!important;}
                .ft-bflm-open-btn .ft-bfl__icon{width:20px;height:20px;}
                .ft-bflm-sheet{display:block!important;position:fixed;inset:0;z-index:99990;visibility:hidden;pointer-events:none;}
                .ft-bflm-sheet.is-open{visibility:visible;pointer-events:auto;}
            }
            .ft-bflm-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.5);opacity:0;transition:opacity .3s;}
            .ft-bflm-sheet.is-open .ft-bflm-backdrop{opacity:1;}
            .ft-bflm-panel{display:flex;flex-direction:column;position:absolute;left:0;right:0;bottom:0;max-height:92dvh;background:#fff;border-radius:16px 16px 0 0;box-shadow:0 -10px 40px rgba(0,0,0,.25);transform:translateY(100%);transition:transform .3s ease-out;outline:none;}
            .ft-bflm-sheet.is-open .ft-bflm-panel{transform:translateY(0);}
            .ft-bflm-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:16px 16px 12px;border-bottom:1px solid #e5e7eb;}
            .ft-bflm-title{margin:0!important;color:#111827!important;font-family:Georgia,"Times New Roman",serif!important;font-size:20px!important;font-weight:700!important;line-height:1.25!important;}
            .ft-bflm-sub{margin:2px 0 0!important;color:#6b7280!important;font-size:12px!important;}
            .ft-bflm-sheet button.ft-bflm-close{all:unset;flex-shrink:0;display:flex!important;align-items:center;justify-content:center;width:36px!important;height:36px!important;border:1px solid #e5e7eb!important;border-radius:50%!important;background:#fff!important;color:#111827!important;font-size:22px!important;line-height:1!important;cursor:pointer;box-shadow:none!important;}
            .ft-bflm-body{overflow-y:auto;overscroll-behavior:contain;padding:16px 16px calc(16px + env(safe-area-inset-bottom));}
            /* The form reuses the 3-step form's look (.ft-bfl), without its card */
            .ft-bflm-body .ft-bfl{max-width:none;border:0!important;border-radius:0!important;box-shadow:none!important;overflow:visible;}
            .ft-bflm-form{position:relative;display:flex;flex-direction:column;gap:16px;margin:0;}
            .ft-bflm-form .ft-bfl__step{gap:16px;}
            .ft-bflm-form .ft-bfl__step.is-back{animation-name:ft-bflm-back;}
            @keyframes ft-bflm-back{from{opacity:0;transform:translateX(-12px);}to{opacity:1;transform:none;}}
            .ft-bflm-legend{margin:0 0 8px!important;padding:0!important;color:var(--bfl-text)!important;font-size:14px!important;font-weight:700!important;}
            .ft-bflm-chips{display:flex;flex-wrap:wrap;gap:8px;}
            .ft-bfl button.ft-bflm-chip{all:unset;box-sizing:border-box!important;display:inline-flex!important;align-items:center!important;width:auto!important;min-height:0!important;margin:0!important;padding:6px 12px!important;border:1px solid var(--bfl-border)!important;border-radius:6px!important;background:#fff!important;background-image:none!important;color:var(--bfl-text)!important;font-family:inherit!important;font-size:14px!important;font-weight:500!important;line-height:1.4!important;letter-spacing:0!important;text-transform:none!important;box-shadow:none!important;cursor:pointer;transition:border-color .15s,background .15s,color .15s;}
            .ft-bfl button.ft-bflm-chip:hover{border-color:color-mix(in srgb,var(--bfl-primary) 40%,transparent)!important;}
            .ft-bfl button.ft-bflm-chip[aria-pressed="true"]{border-color:var(--bfl-primary)!important;background:color-mix(in srgb,var(--bfl-primary) 10%,#fff)!important;color:var(--bfl-primary)!important;}
            .ft-bfl button.ft-bflm-chip::before,.ft-bfl button.ft-bflm-chip::after{content:none!important;display:none!important;}
            .ft-bflm-form .ft-bfl__field>label{font-weight:700!important;}
            .ft-bfl input.ft-bflm-input,.ft-bfl select.ft-bflm-input{padding:0 14px!important;}
            .ft-bfl select.ft-bflm-input{padding-right:14px!important;}
            .ft-bflm-two{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}
            .ft-bflm-street{display:grid;grid-template-columns:minmax(0,1fr) 88px;gap:12px;}
            .ft-bflm-map{display:flex;align-items:center;gap:6px;color:var(--bfl-primary)!important;font-size:14px;font-weight:600;text-decoration:underline!important;text-underline-offset:2px;}
            .ft-bflm-map .ft-bfl__icon{width:16px;height:16px;}
            .ft-bflm-form .ft-bfl__submit,.ft-bflm-form .ft-bfl__back{height:52px!important;}
            .ft-bflm-form .ft-bfl__back{width:52px!important;}
            .ft-bflm-form .ft-bfl__submit{font-size:16px!important;}
            .ft-bflm-note{margin:0!important;color:var(--bfl-muted)!important;font-size:12px!important;text-align:center;}
            .ft-bflm-done{display:flex;flex-direction:column;align-items:center;gap:16px;padding:24px 8px;text-align:center;animation:ft-bfl-in .3s ease-out;}
            .ft-bflm-done[hidden]{display:none!important;}
            .ft-bflm-done h3{margin:0!important;color:var(--bfl-text)!important;font-family:Georgia,"Times New Roman",serif!important;font-size:24px!important;font-weight:700!important;}
            .ft-bflm-done p{margin:0!important;color:var(--bfl-muted)!important;font-size:14px!important;line-height:1.6;}
            .ft-bflm-done a{color:var(--bfl-primary)!important;font-weight:600;text-decoration:underline!important;text-underline-offset:4px;}
            .ft-bfl button.ft-bflm-done-close{all:unset;box-sizing:border-box;display:flex!important;align-items:center;justify-content:center;width:100%!important;height:44px!important;margin-top:8px!important;border:2px solid var(--bfl-border)!important;border-radius:8px!important;background:#fff!important;color:var(--bfl-text)!important;font-size:15px!important;font-weight:600!important;cursor:pointer;box-shadow:none!important;}
            @media(prefers-reduced-motion:reduce){.ft-bflm-panel,.ft-bflm-backdrop{transition:none!important;}.ft-bflm-done{animation:none!important;}}
        </style>
        <script id="ft-bflm-sheet-script">
            window.ftBflmSheet = window.ftBflmSheet || (function () {
                var phone = window.matchMedia('(max-width: 640px)');
                var saved = { overflow: '', chat: '' };
                var openSheet = null, opener = null;
                function chatEl() { return document.getElementById('chatbot-container'); }
                function open(sheet, fromBtn) {
                    if (!sheet || !phone.matches) return;
                    // Elementor sections with animations (transform) would trap a fixed
                    // sheet inside them; move it to <body> the first time.
                    if (sheet.parentNode !== document.body) document.body.appendChild(sheet);
                    openSheet = sheet; opener = fromBtn || null;
                    var panel = sheet.querySelector('.ft-bflm-panel');
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
                    var panel = openSheet.querySelector('.ft-bflm-panel');
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
                phone.addEventListener('change', function () { if (!phone.matches) close(); });
                document.addEventListener('click', function (event) {
                    var btn = event.target.closest('[data-bflm-open]');
                    if (btn) { event.preventDefault(); open(document.getElementById(btn.getAttribute('aria-controls')), btn); return; }
                    if (event.target.closest('[data-bflm-close]')) { event.preventDefault(); close(); }
                });
                document.addEventListener('keydown', function (event) { if (event.key === 'Escape') close(); });
                return { open: open, close: close };
            })();
        </script>
        <?php
    }
    ?>
    <div class="ft-bflm-open">
        <button type="button" class="ft-bflm-open-btn" data-bflm-open aria-controls="<?php echo esc_attr($sheet_id); ?>" aria-expanded="false"><?php echo esc_html($open_label); ?> <?php echo ft_bfl_icon('right'); ?></button>
    </div>
    <div class="ft-bflm-sheet" id="<?php echo esc_attr($sheet_id); ?>">
        <div class="ft-bflm-backdrop" data-bflm-close></div>
        <div class="ft-bflm-panel" aria-labelledby="<?php echo esc_attr($sheet_id); ?>-title" tabindex="-1">
            <div class="ft-bflm-head">
                <div>
                    <p class="ft-bflm-title" id="<?php echo esc_attr($sheet_id); ?>-title"><?php echo esc_html($form_title); ?></p>
                    <p class="ft-bflm-sub"><?php echo esc_html($form_subtitle); ?></p>
                </div>
                <button type="button" class="ft-bflm-close" data-bflm-close aria-label="Close">&times;</button>
            </div>
            <div class="ft-bflm-body">
                <div class="ft-bfl" data-endpoint="<?php echo esc_url(rest_url('floors-today/v1/inbox-leads')); ?>" data-redirect="<?php echo esc_url((string) ($inbox_settings['booking_form_redirect_url'] ?? '')); ?>" data-source="<?php echo esc_attr(FT_BFL_MOBILE_SOURCE); ?>" id="<?php echo esc_attr($sheet_id); ?>-root">
                    <form class="ft-bflm-form" novalidate>
                        <div class="ft-bfl__trap" aria-hidden="true">
                            <label>Leave this field empty <input name="ftInboxTrap" type="text" tabindex="-1" autocomplete="new-password"></label>
                        </div>
                        <ol class="ft-bfl__steps" aria-label="Form progress">
                            <?php foreach (['Details', 'Address', 'Confirm'] as $i => $label) : ?>
                                <li class="<?php echo $i === 0 ? 'is-current' : ''; ?>"<?php echo $i === 0 ? ' aria-current="step"' : ''; ?>>
                                    <span class="ft-bfl__dot"><span class="ft-bfl__num"><?php echo (int) $i + 1; ?></span><?php echo ft_bfl_icon('check', 'ft-bfl__icon ft-bfl__tick'); ?></span>
                                    <span class="ft-bfl__steplabel"><?php echo esc_html($label); ?></span>
                                    <?php if ($i < 2) : ?><span class="ft-bfl__line" aria-hidden="true"></span><?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>

                        <div class="ft-bfl__step" data-step="0">
                            <fieldset>
                                <legend class="ft-bflm-legend">What floors are you thinking about?</legend>
                                <div class="ft-bflm-chips">
                                    <?php foreach ($floors as $f) : ?>
                                        <button type="button" class="ft-bflm-chip" aria-pressed="false" data-value="<?php echo esc_attr($f); ?>"><?php echo esc_html($f); ?></button>
                                    <?php endforeach; ?>
                                </div>
                                <p class="ft-bfl__err" data-error="floors"></p>
                            </fieldset>
                            <div class="ft-bfl__field" data-field="firstName">
                                <label for="<?php echo esc_attr($sheet_id); ?>-first">First name</label>
                                <input class="ft-bfl__input ft-bflm-input" id="<?php echo esc_attr($sheet_id); ?>-first" name="firstName" type="text" autocomplete="given-name" placeholder="Your first name">
                                <p class="ft-bfl__err" data-error="firstName"></p>
                            </div>
                            <div class="ft-bflm-two">
                                <div class="ft-bfl__field" data-field="phone">
                                    <label for="<?php echo esc_attr($sheet_id); ?>-phone">Mobile</label>
                                    <input class="ft-bfl__input ft-bflm-input" id="<?php echo esc_attr($sheet_id); ?>-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="(___) ___-____">
                                    <p class="ft-bfl__err" data-error="phone"></p>
                                </div>
                                <div class="ft-bfl__field" data-field="postalCode">
                                    <label for="<?php echo esc_attr($sheet_id); ?>-postal">Postal code</label>
                                    <input class="ft-bfl__input ft-bflm-input" id="<?php echo esc_attr($sheet_id); ?>-postal" name="postalCode" type="text" autocomplete="postal-code" placeholder="e.g. M5V 2T6">
                                    <p class="ft-bfl__err" data-error="postalCode"></p>
                                </div>
                            </div>
                            <?php if ($map_url !== '') : ?>
                                <a class="ft-bflm-map" href="<?php echo esc_url($map_url); ?>"><?php echo ft_bfl_icon('pin'); ?>Check if we serve your area – view map <?php echo ft_bfl_icon('right'); ?></a>
                            <?php endif; ?>
                        </div>

                        <div class="ft-bfl__step" data-step="1" hidden>
                            <div class="ft-bfl__field" data-field="email">
                                <label for="<?php echo esc_attr($sheet_id); ?>-email">Email</label>
                                <input class="ft-bfl__input ft-bflm-input" id="<?php echo esc_attr($sheet_id); ?>-email" name="email" type="email" autocomplete="email" placeholder="you@email.com">
                                <p class="ft-bfl__err" data-error="email"></p>
                            </div>
                            <div class="ft-bflm-street">
                                <div class="ft-bfl__field" data-field="street">
                                    <label for="<?php echo esc_attr($sheet_id); ?>-street">Street address</label>
                                    <input class="ft-bfl__input ft-bflm-input" id="<?php echo esc_attr($sheet_id); ?>-street" name="street" type="text" autocomplete="address-line1" placeholder="123 Main St">
                                    <p class="ft-bfl__err" data-error="street"></p>
                                </div>
                                <div class="ft-bfl__field">
                                    <label for="<?php echo esc_attr($sheet_id); ?>-unit">Unit</label>
                                    <input class="ft-bfl__input ft-bflm-input" id="<?php echo esc_attr($sheet_id); ?>-unit" name="unit" type="text" autocomplete="address-line2" placeholder="2B">
                                </div>
                            </div>
                            <div class="ft-bflm-two">
                                <div class="ft-bfl__field" data-field="city">
                                    <label for="<?php echo esc_attr($sheet_id); ?>-city">City</label>
                                    <input class="ft-bfl__input ft-bflm-input" id="<?php echo esc_attr($sheet_id); ?>-city" name="city" type="text" autocomplete="address-level2" placeholder="Toronto">
                                    <p class="ft-bfl__err" data-error="city"></p>
                                </div>
                                <div class="ft-bfl__field">
                                    <label for="<?php echo esc_attr($sheet_id); ?>-province">Province</label>
                                    <select class="ft-bfl__input ft-bflm-input" id="<?php echo esc_attr($sheet_id); ?>-province" name="province" autocomplete="address-level1">
                                        <?php foreach ($provinces as $code => $pname) : ?>
                                            <option value="<?php echo esc_attr($code); ?>"<?php selected($code, 'ON'); ?>><?php echo esc_html($pname); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="ft-bfl__step" data-step="2" hidden>
                            <div class="ft-bfl__field">
                                <label for="<?php echo esc_attr($sheet_id); ?>-rooms">Number of rooms</label>
                                <input class="ft-bfl__input ft-bflm-input" id="<?php echo esc_attr($sheet_id); ?>-rooms" name="numberOfRooms" type="number" inputmode="numeric" min="1" max="99" placeholder="Enter number of rooms">
                            </div>
                            <div class="ft-bfl__consents" role="group" aria-labelledby="<?php echo esc_attr($sheet_id); ?>-consent-title">
                                <p class="ft-bfl__consents-title" id="<?php echo esc_attr($sheet_id); ?>-consent-title">Please confirm</p>
                                <?php foreach ($consents as $key => $text) : ?>
                                    <label class="ft-bfl__consent">
                                        <input type="checkbox" name="<?php echo esc_attr($key); ?>" required>
                                        <span class="ft-bfl__box" aria-hidden="true"><?php echo ft_bfl_icon('check'); ?></span>
                                        <span><?php echo wp_kses($text, ['a' => ['href' => []]]); ?></span>
                                    </label>
                                <?php endforeach; ?>
                                <p class="ft-bfl__err" data-error="consent"></p>
                            </div>
                        </div>

                        <p class="ft-bfl__formerr" role="alert" hidden></p>

                        <div class="ft-bfl__row">
                            <button type="button" class="ft-bfl__back" aria-label="Previous step" hidden><?php echo ft_bfl_icon('left'); ?></button>
                            <button type="submit" class="ft-bfl__submit" data-final-label="<?php echo esc_attr($form_button); ?>"><span class="ft-bfl__submit-text">Continue</span><?php echo ft_bfl_icon('right'); ?></button>
                        </div>
                        <?php if ($form_note !== '') : ?><p class="ft-bflm-note"><?php echo esc_html($form_note); ?></p><?php endif; ?>
                    </form>

                    <div class="ft-bflm-done" role="status" hidden>
                        <span class="ft-bfl__done-icon"><?php echo ft_bfl_icon('check'); ?></span>
                        <h3></h3>
                        <?php if ($form_note !== '') : ?><p><?php echo esc_html($form_note); ?></p><?php endif; ?>
                        <?php if ($phone !== '') : ?>
                            <a href="<?php echo esc_url('tel:' . $phone_href); ?>">Prefer to talk now? <?php echo esc_html($phone); ?></a>
                        <?php endif; ?>
                        <button type="button" class="ft-bflm-done-close" data-bflm-close>Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function () {
        var root = document.getElementById(<?php echo wp_json_encode($sheet_id . '-root'); ?>);
        if (!root || root.dataset.ready) return;
        root.dataset.ready = '1';

        var form = root.querySelector('.ft-bflm-form');
        var steps = root.querySelectorAll('.ft-bfl__step');
        var dots = root.querySelectorAll('.ft-bfl__steps li');
        var back = root.querySelector('.ft-bfl__back');
        var submit = root.querySelector('.ft-bfl__submit');
        var submitText = root.querySelector('.ft-bfl__submit-text');
        var formErr = root.querySelector('.ft-bfl__formerr');
        var state = { step: 0, floors: [], pending: false };
        var POSTAL = /^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/;
        // Province from the first letter of the postal code (same as the homepage)
        var POSTAL_PROVINCE = { A: 'NL', B: 'NS', C: 'PE', E: 'NB', G: 'QC', H: 'QC', J: 'QC', K: 'ON', L: 'ON', M: 'ON', N: 'ON', P: 'ON', R: 'MB', S: 'SK', T: 'AB', V: 'BC', Y: 'YT' };

        function fmtPhone(v) {
            var d = v.replace(/\D/g, '').slice(0, 10);
            if (d.length < 4) return d;
            if (d.length < 7) return '(' + d.slice(0, 3) + ') ' + d.slice(3);
            return '(' + d.slice(0, 3) + ') ' + d.slice(3, 6) + '-' + d.slice(6);
        }
        function fmtPostal(v) {
            var c = v.replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 6);
            return c.length > 3 ? c.slice(0, 3) + ' ' + c.slice(3) : c;
        }
        function val(name) { var el = form.elements[name]; return el ? String(el.value || '').trim() : ''; }
        function setError(key, msg) {
            var p = root.querySelector('[data-error="' + key + '"]');
            if (p) p.textContent = msg || '';
            var field = root.querySelector('[data-field="' + key + '"]');
            if (field) field.classList.toggle('is-error', !!msg);
            if (key === 'consent') root.querySelector('.ft-bfl__consents').classList.toggle('is-error', !!msg);
        }
        function clearErrors() {
            root.querySelectorAll('[data-error]').forEach(function (p) { setError(p.getAttribute('data-error'), ''); });
            formErr.hidden = true;
        }
        function show(step, goingBack) {
            state.step = step;
            steps.forEach(function (s, i) { s.hidden = i !== step; s.classList.toggle('is-back', !!goingBack); });
            dots.forEach(function (li, i) {
                li.classList.toggle('is-done', i < step);
                li.classList.toggle('is-current', i === step);
                if (i === step) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
            });
            back.hidden = step === 0;
            submitText.textContent = step < 2 ? 'Continue' : submit.getAttribute('data-final-label');
            if (step === 1) attachPlaces();
        }

        root.querySelectorAll('.ft-bflm-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                var v = chip.getAttribute('data-value');
                var on = chip.getAttribute('aria-pressed') !== 'true';
                chip.setAttribute('aria-pressed', on ? 'true' : 'false');
                state.floors = on ? state.floors.concat([v]) : state.floors.filter(function (f) { return f !== v; });
                setError('floors', '');
            });
        });
        form.elements.phone.addEventListener('input', function (e) { e.target.value = fmtPhone(e.target.value); setError('phone', ''); });
        form.elements.postalCode.addEventListener('input', function (e) { e.target.value = fmtPostal(e.target.value); setError('postalCode', ''); });
        form.elements.numberOfRooms.addEventListener('input', function (e) { e.target.value = e.target.value.replace(/\D/g, '').slice(0, 2); });
        ['firstName', 'email', 'street', 'city'].forEach(function (n) {
            form.elements[n].addEventListener('input', function () { setError(n, ''); formErr.hidden = true; });
        });
        root.querySelectorAll('.ft-bfl__consent input').forEach(function (c) { c.addEventListener('change', function () { setError('consent', ''); }); });
        back.addEventListener('click', function () { clearErrors(); show(state.step - 1, true); });

        // Google address suggestions on the street field (fills city, province, postal code)
        var placesOn = false;
        function attachPlaces() {
            var places = window.google && window.google.maps && window.google.maps.places;
            if (placesOn || !places) return;
            placesOn = true;
            var input = form.elements.street;
            var ac = new places.Autocomplete(input, { types: ['address'], componentRestrictions: { country: 'ca' }, fields: ['address_components'] });
            ac.addListener('place_changed', function () {
                var parts = (ac.getPlace() || {}).address_components;
                if (!parts) return;
                var by = {};
                parts.forEach(function (c) { c.types.forEach(function (t) { by[t] = c; }); });
                var street = ((by.street_number ? by.street_number.long_name : '') + ' ' + (by.route ? by.route.long_name : '')).trim();
                if (street) input.value = street;
                var city = (by.locality || by.sublocality || by.postal_town || {}).long_name;
                if (city) form.elements.city.value = city;
                var prov = by.administrative_area_level_1 ? by.administrative_area_level_1.short_name : '';
                if (prov && form.elements.province.querySelector('option[value="' + prov + '"]')) form.elements.province.value = prov;
                if (by.postal_code) form.elements.postalCode.value = fmtPostal(by.postal_code.long_name);
                ['street', 'city'].forEach(function (k) { setError(k, ''); });
            });
        }
        document.addEventListener('ft:places:ready', function () { if (state.step === 1) attachPlaces(); });

        // Same checks, in the same steps, as the homepage phone form
        function validate() {
            var e = {};
            if (state.step === 0) {
                if (!state.floors.length) e.floors = 'Pick at least one option.';
                if (!val('firstName')) e.firstName = 'Please enter your first name.';
                if (val('phone').replace(/\D/g, '').length < 10) e.phone = 'Enter a 10-digit mobile number.';
                if (!POSTAL.test(val('postalCode'))) e.postalCode = 'Enter a valid postal code.';
            } else if (state.step === 1) {
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val('email'))) e.email = 'Please enter a valid email address.';
                if (!val('street')) e.street = 'Please enter your street address.';
                if (!val('city')) e.city = 'Please enter your city.';
            } else {
                var all = Array.prototype.every.call(root.querySelectorAll('.ft-bfl__consent input'), function (c) { return c.checked; });
                if (!all) e.consent = 'Please check all three boxes to continue.';
            }
            return e;
        }

        function attribution() {
            var url = new URL(window.location.href);
            var a = window.ftGetAttribution ? window.ftGetAttribution() : {};
            var refHost = a.referrerHost || '';
            if (!refHost && document.referrer) { try { refHost = new URL(document.referrer).hostname.replace(/^www\./, ''); } catch (x) {} }
            var utmSource = a.utmSource || url.searchParams.get('hello_social') || url.searchParams.get('utm_source') || '';
            return {
                trafficSource: a.trafficSource || utmSource || refHost || 'Direct',
                referrerUrl: a.referrerUrl || document.referrer || '',
                utmSource: utmSource,
                utmMedium: a.utmMedium || url.searchParams.get('utm_medium') || '',
                utmCampaign: a.utmCampaign || url.searchParams.get('utm_campaign') || '',
                utmContent: a.utmContent || url.searchParams.get('utm_content') || '',
                utmTerm: a.utmTerm || url.searchParams.get('utm_term') || ''
            };
        }

        form.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            if (state.pending) return;
            clearErrors();
            var errs = validate();
            var keys = Object.keys(errs);
            if (keys.length) { keys.forEach(function (k) { setError(k, errs[k]); }); return; }
            if (state.step === 0) {
                var prov = POSTAL_PROVINCE[val('postalCode').charAt(0).toUpperCase()];
                if (prov) form.elements.province.value = prov;
                show(1);
                return;
            }
            if (state.step === 1) { show(2); return; }

            var attr = attribution();
            var unit = val('unit');
            var streetLine = val('street') + (unit ? ' Unit ' + unit : '');
            var flooringType = state.floors.join(', ');
            var payload = {
                fullName: val('firstName'),
                email: val('email'),
                phone: '+1 ' + val('phone'),
                address: [streetLine, val('city'), val('province'), val('postalCode'), 'Canada'].filter(Boolean).join(', '),
                street: val('street'),
                unit: unit,
                city: val('city'),
                province: val('province'),
                postalCode: val('postalCode'),
                flooringType: flooringType,
                ftInboxTrap: form.elements.ftInboxTrap.value,
                source: root.dataset.source,
                pageUrl: window.location.href,
                devicePlatform: /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent) ? 'Mobile / Tablet' : 'Desktop',
                numberOfRooms: val('numberOfRooms'),
                privacyConsent: form.elements.privacyConsent.checked,
                smsConsent: form.elements.smsConsent.checked,
                emailConsent: form.elements.emailConsent.checked
            };
            Object.keys(attr).forEach(function (k) { payload[k] = attr[k]; });

            state.pending = true;
            submit.disabled = true;
            submitText.textContent = 'Booking…';
            try {
                var res = await fetch(root.dataset.endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                var result = await res.json().catch(function () { return null; });
                if (!res.ok) throw new Error(result && result.message ? result.message : 'We could not send your request. Please try again.');

                form.hidden = true;
                var done = root.querySelector('.ft-bflm-done');
                done.querySelector('h3').textContent = 'Thanks, ' + payload.fullName + "! You're booked in.";
                done.hidden = false;

                // Same conversion event as the booking form, so ad and CRM tracking keep counting
                if (window.XDTrack && window.XDTrack.lead) {
                    window.XDTrack.lead({
                        formId: 'booking_form',
                        formName: 'Booking Form Shortcode',
                        flooringType: flooringType,
                        leadSource: attr.trafficSource || attr.utmSource || 'Direct',
                        utmSource: attr.utmSource
                    });
                }
                if (root.dataset.redirect) {
                    setTimeout(function () { window.location.href = root.dataset.redirect; }, 600);
                }
            } catch (err) {
                formErr.textContent = (err && err.message) || 'We could not send your request. Please try again.';
                formErr.hidden = false;
                submit.disabled = false;
                submitText.textContent = submit.getAttribute('data-final-label');
            } finally {
                state.pending = false;
            }
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
