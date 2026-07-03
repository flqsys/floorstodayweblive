<?php
/**
 * Plugin Name: Floors Today Form Variants
 * Description: Additional booking form shortcodes: product page variant and contact form.
 * Version: 1.0.0
 * Author: xdeye.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// ── [floors_booking_form_products] ──────────────────────────────────────────

function ft_next_booking_form_products_shortcode($atts) {
    static $styles_printed = false;
    static $places_script_printed = false;

    $settings          = function_exists('ft_next_homepage_settings') ? ft_next_homepage_settings() : [];
    $google_places_key = sanitize_text_field($settings['google_places_api_key'] ?? '');
    $primary_color     = sanitize_hex_color($settings['primary_color']  ?? '') ?: '#155f99';
    $secondary_color   = sanitize_hex_color($settings['secondary_color'] ?? '') ?: '#cc9c2e';
    $title             = (string) ($settings['form_title']    ?? 'Get Your FREE In-Home Estimate');
    $subtitle          = (string) ($settings['form_subtitle'] ?? 'No obligation. Takes just 2 minutes.');
    $endpoint          = rest_url('floors-today/v1/inbox-leads');

    // ── Flooring type detection ──────────────────────────────────────────────
    $atts = shortcode_atts(['flooring_type' => '', 'product' => ''], $atts);

    if (is_singular('our-products')) {
        if ($atts['flooring_type'] === '' && function_exists('get_field')) {
            $ft = get_field('flooring_types');
            if (is_array($ft)) {
                $atts['flooring_type'] = implode(', ', array_filter(array_map('strval', $ft)));
            } elseif ($ft) {
                $atts['flooring_type'] = (string) $ft;
            }
        }
        if ($atts['product'] === '') {
            $atts['product'] = get_the_title();
        }
    }

    $flooring_type = sanitize_text_field($atts['flooring_type']);
    $product_name  = sanitize_text_field($atts['product']);
    $instance_id   = wp_unique_id('ft-bfp-');

    $property_types = ['Residential', 'Office Space', 'Business'];
    $property_icons = [
        'Residential' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>',
        'Office Space' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M9 17h.01"/></svg>',
        'Business'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/><path d="M12 13v2"/></svg>',
    ];
    $start_times = ['ASAP', 'Within 1 month', '1-3 months', '3+ months', 'Just researching'];

    ob_start();

    if (!$styles_printed) {
        $styles_printed = true;
        ?>
        <style id="ft-bfp-styles">
            .ft-bf, .ft-bf * { box-sizing: border-box; }
            .ft-bf button, .ft-bf input, .ft-bf select, .ft-bf textarea { font-family: inherit; }
            .ft-bf button { appearance: none; -webkit-appearance: none; text-transform: none; letter-spacing: 0; box-shadow: none; }
            .ft-bf { display: block; width: 100% !important; max-width: 100% !important; margin-inline: 0 !important; padding: clamp(20px, 5vw, 36px); overflow: hidden; border: 1px solid rgba(255,255,255,.5); border-radius: 20px; background: #fff; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25); color: #0f172a; font-family: inherit; font-size: 14px; }
            .elementor-shortcode > .ft-bf, .elementor-widget-shortcode .ft-bf, .elementor-widget-container > .ft-bf { width: 100% !important; max-width: 100% !important; }
            .ft-bf__heading { margin-bottom: 0; text-align: center; }
            .ft-bf__title { margin: 0; color: #020617; font-family: Georgia,"Times New Roman",serif; font-size: 1.9rem !important; font-weight: 700; line-height: 1.12; }
            .ft-bf__subtitle { margin: 8px 0 0; color: #475569; font-size: 14px; }
            .ft-bf__progress { display: flex; align-items: center; justify-content: center; margin: 28px 0; }
            .ft-bf__dot { display: inline-flex; width: 36px; height: 36px; flex: 0 0 36px; align-items: center; justify-content: center; border-radius: 50%; background: #f5f5f4; color: #78716c; font-size: 14px; font-weight: 700; transition: background-color .18s ease, color .18s ease; }
            .ft-bf__dot.is-active { background: var(--ft-bf-primary); color: #fff; }
            .ft-bf__dot.is-complete { background: #059669; color: #fff; }
            .ft-bf__line { width: clamp(32px,10vw,56px); height: 2px; margin: 0 10px; border-radius: 999px; background: #f5f5f4; transition: background-color .18s ease; }
            .ft-bf__line.is-complete { background: #059669; }
            .ft-bf__step[hidden] { display: none !important; }
            .ft-bf__step { animation: ft-bf-fade .22s ease both; }
            @keyframes ft-bf-fade { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
            .ft-bf__question { margin: 0 0 16px; color: #0f172a; font-size: 16px; font-weight: 700; }
            .ft-bf__choices { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 12px; }
            .ft-bf__choices--property { grid-template-columns: repeat(3,minmax(0,1fr)) !important; }
            .ft-bf button.ft-bf__choice { width: 100%; min-height: 54px; padding: 12px 16px; border: 1px solid #e7e5e4 !important; border-radius: 8px !important; background: #fff !important; color: #0f172a !important; font: inherit; font-weight: 600; text-align: left; white-space: normal; word-break: break-word; cursor: pointer; transition: border-color .18s ease, background-color .18s ease, color .18s ease, transform .12s ease; }
            .ft-bf button.ft-bf__choice:hover, .ft-bf button.ft-bf__choice:focus-visible { border-color: var(--ft-bf-primary) !important; outline: none; }
            .ft-bf button.ft-bf__choice:active { transform: translateY(1px); }
            .ft-bf button.ft-bf__choice.is-selected { border-color: var(--ft-bf-primary) !important; background: color-mix(in srgb, var(--ft-bf-primary) 7%, white) !important; color: var(--ft-bf-primary) !important; }
            .ft-bf__property { min-height: 96px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #475569; }
            .ft-bf__property-icon { display: inline-flex; align-items: center; justify-content: center; color: var(--ft-bf-primary); }
            .ft-bf button.ft-bf__property.is-selected { color: #0f172a !important; background: color-mix(in srgb, var(--ft-bf-secondary) 10%, white) !important; border-color: var(--ft-bf-secondary) !important; }
            .ft-bf__property svg { width: 24px; height: 24px; }
            .ft-bf__field { display: block; margin: 0 0 16px; }
            .ft-bf__field > span { display: block; margin-bottom: 7px; color: #475569; font-size: 14px; font-weight: 600; }
            .ft-bf__input, .ft-bf__select, .elementor-widget-container .ft-bf__input, .elementor-widget-container .ft-bf__select { display: block; width: 100%; height: 48px; padding: 0 14px; border: 1px solid #d6d3d1 !important; border-radius: 8px !important; background: #fff !important; color: #0f172a; font: inherit; font-size: 16px; box-shadow: none !important; transition: border-color .18s ease, box-shadow .18s ease; }
            .ft-bf__input:focus, .ft-bf__select:focus, .elementor-widget-container .ft-bf__input:focus, .elementor-widget-container .ft-bf__select:focus { border-color: var(--ft-bf-primary) !important; outline: 0; box-shadow: 0 0 0 3px color-mix(in srgb, var(--ft-bf-primary) 18%, transparent) !important; }
            .ft-bf__columns { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 16px; }
            .ft-bf__actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 28px; }
            .ft-bf button.ft-bf__next, .ft-bf button.ft-bf__submit { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; gap: 8px; padding: 8px 20px; border: 0 !important; border-radius: 999px !important; background: var(--ft-bf-primary) !important; color: #fff !important; font: inherit; font-weight: 700; cursor: pointer; white-space: nowrap; text-decoration: none !important; transition: background-color .18s ease, box-shadow .18s ease, transform .12s ease, opacity .18s ease; }
            .ft-bf button.ft-bf__next:hover, .ft-bf button.ft-bf__submit:hover { background: color-mix(in srgb, var(--ft-bf-primary) 90%, white) !important; color: #fff !important; box-shadow: 0 10px 20px rgba(35,91,184,.18) !important; }
            .ft-bf button.ft-bf__next:focus-visible, .ft-bf button.ft-bf__submit:focus-visible { outline: 0; box-shadow: 0 0 0 3px color-mix(in srgb, var(--ft-bf-primary) 24%, transparent); }
            .ft-bf button.ft-bf__next:active, .ft-bf button.ft-bf__submit:active { transform: translateY(1px); }
            .ft-bf button.ft-bf__submit:disabled { cursor: wait; opacity: .65; animation: ft-bf-pulse 1.1s ease-in-out infinite; }
            .ft-bf__arrow { display: inline-block; line-height: 1; transition: transform .18s ease; }
            .ft-bf button.ft-bf__next:hover .ft-bf__arrow, .ft-bf button.ft-bf__next:focus-visible .ft-bf__arrow, .ft-bf button.ft-bf__submit:hover .ft-bf__arrow, .ft-bf button.ft-bf__submit:focus-visible .ft-bf__arrow { transform: translateX(4px); }
            @keyframes ft-bf-pulse { 0%,100% { opacity: .65; } 50% { opacity: .88; } }
            .ft-bf button.ft-bf__back { display: inline-flex; align-items: center; gap: 8px; padding: 8px 2px; border: 0 !important; border-radius: 0 !important; background: transparent !important; color: #64748b !important; font: inherit; font-weight: 600; cursor: pointer; box-shadow: none !important; transition: color .18s ease; }
            .ft-bf button.ft-bf__back:hover, .ft-bf button.ft-bf__back:focus-visible { color: #020617 !important; background: transparent !important; outline: none; }
            .ft-bf__error { margin: 14px 0 0; color: #dc2626; font-size: 14px; font-weight: 600; }
            .ft-bf__success { min-height: 360px; text-align: center; align-content: center; animation: ft-bf-fade .22s ease both; }
            .ft-bf__success-icon { display: inline-flex; width: 64px; height: 64px; align-items: center; justify-content: center; border-radius: 50%; background: #ecfdf5; color: #047857; font-size: 32px; font-weight: 700; }
            .ft-bf__success h3 { margin: 18px 0 8px; color: #0f172a; font-size: 24px; }
            .ft-bf__success p { margin: 0; color: #475569; }
            .ft-bf__trap { position: absolute !important; left: -10000px !important; width: 1px !important; height: 1px !important; overflow: hidden !important; }
            .ft-bf__addr-row { display: grid; grid-template-columns: 3fr 1fr; gap: 12px; }
            .ft-bf__select { appearance: none; cursor: pointer; }
            .ft-bf__sel-wrap { display: grid; grid-template-areas: "sel"; align-items: center; }
            .ft-bf__sel-wrap::after { content: ""; grid-area: sel; width: .65em; height: .42em; margin-right: 14px; justify-self: end; background-color: #94a3b8; clip-path: polygon(100% 0%,0% 0%,50% 100%); pointer-events: none; transition: background-color .18s ease; }
            .ft-bf__sel-wrap:focus-within::after { background-color: var(--ft-bf-primary); }
            .ft-bf__sel-wrap .ft-bf__select { grid-area: sel; padding-right: 36px; }
            .ft-bf__phone-wrap { display: flex; align-items: stretch; border: 1px solid #d6d3d1; border-radius: 8px; overflow: hidden; background: #fff; transition: border-color .18s ease, box-shadow .18s ease; }
            .ft-bf__phone-wrap:focus-within { border-color: var(--ft-bf-primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--ft-bf-primary) 18%, transparent); }
            .ft-bf__phone-pfx { display: flex; align-items: center; padding: 0 12px; background: #f5f5f4; border-right: 1px solid #d6d3d1; font-size: 15px; color: #64748b; user-select: none; flex-shrink: 0; }
            .ft-bf__phone-wrap .ft-bf__input { border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; }
            .ft-bf__consents { margin-top: 18px; display: grid; gap: 10px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; padding: 14px; text-align: left; }
            .ft-bf__consent { display: flex; align-items: flex-start; gap: 10px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; color: #475569; font-size: 12px; line-height: 1.5; }
            .ft-bf__consent input { width: 16px !important; height: 16px !important; min-width: 16px !important; margin: 2px 0 0 !important; padding: 0 !important; border: 1px solid #cbd5e1 !important; border-radius: 3px !important; background: #fff !important; box-shadow: none !important; appearance: auto !important; -webkit-appearance: checkbox !important; }
            .ft-bf__consent a { color: var(--ft-bf-primary); font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }
            .ft-bf__input[readonly] { opacity: .65; cursor: default; background: #f8f7f6; }
            .ft-bf__flooring-info { display: flex; align-items: center; gap: 10px; padding: 12px 16px; margin-bottom: 20px; background: color-mix(in srgb, var(--ft-bf-primary) 7%, white); border: 1px solid color-mix(in srgb, var(--ft-bf-primary) 20%, transparent); border-radius: 8px; font-size: 14px; color: #0f172a; }
            .ft-bf__flooring-info svg { flex-shrink: 0; width: 18px; height: 18px; color: var(--ft-bf-primary); }
            @media (max-width: 560px) {
                .ft-bf, .elementor-shortcode > .ft-bf, .elementor-widget-shortcode .ft-bf, .elementor-widget-container > .ft-bf { margin-left: 10px !important; margin-right: 10px !important; width: calc(100% - 20px) !important; max-width: calc(100% - 20px) !important; }
                .ft-bf__title { font-size: 25px !important; }
                .ft-bf__columns { grid-template-columns: 1fr; }
                .ft-bf__choices--property { grid-template-columns: 1fr !important; gap: 8px; }
                .ft-bf button.ft-bf__choice { padding: 11px 12px; }
                .ft-bf__property { min-height: 84px; }
                .ft-bf__property svg { width: 22px; height: 22px; }
            }
        </style>
        <?php
    }

    if ($google_places_key && !$places_script_printed) {
        $places_script_printed = true;
        ?>
        <script>
            if (!window.__ftPlacesScript) {
                window.__ftPlacesScript = true;
                window.__ftPlacesReady = function () {
                    window.__ftPlacesLoaded = true;
                    document.dispatchEvent(new CustomEvent('ft:places:ready'));
                };
                var s = document.createElement('script');
                s.async = true;
                s.src = 'https://maps.googleapis.com/maps/api/js?key=<?php echo esc_js($google_places_key); ?>&libraries=places&callback=__ftPlacesReady';
                document.head.appendChild(s);
            }
        </script>
        <?php
    }
    ?>
    <div
        id="<?php echo esc_attr($instance_id); ?>"
        class="ft-bf"
        data-endpoint="<?php echo esc_url($endpoint); ?>"
        style="<?php echo esc_attr('--ft-bf-primary:' . $primary_color . ';--ft-bf-secondary:' . $secondary_color . ';'); ?>"
    >
        <div class="ft-bf__heading">
            <h2 class="ft-bf__title"><?php echo esc_html($title); ?></h2>
            <p class="ft-bf__subtitle"><?php echo esc_html($subtitle); ?></p>
        </div>
        <div class="ft-bf__progress" aria-label="<?php esc_attr_e('Booking form progress', 'floors-today'); ?>">
            <span class="ft-bf__dot is-active" data-dot="1">1</span>
            <span class="ft-bf__line" data-line="1"></span>
            <span class="ft-bf__dot" data-dot="2">2</span>
            <span class="ft-bf__line" data-line="2"></span>
            <span class="ft-bf__dot" data-dot="3">3</span>
        </div>
        <form class="ft-bf__form">
            <div class="ft-bf__trap" aria-hidden="true">
                <label>Leave this field empty<input name="ftInboxTrap" type="text" tabindex="-1" autocomplete="new-password"></label>
            </div>

            <?php if ($flooring_type) : ?>
            <div class="ft-bf__flooring-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                <span>Flooring: <strong><?php echo esc_html($flooring_type); ?></strong></span>
            </div>
            <?php endif; ?>

            <section class="ft-bf__step" data-step="1">
                <h3 class="ft-bf__question">Property type</h3>
                <div class="ft-bf__choices ft-bf__choices--property">
                    <?php foreach ($property_types as $pt) : ?>
                        <button class="ft-bf__choice ft-bf__property" type="button" data-field="propertyType" data-value="<?php echo esc_attr($pt); ?>">
                            <span class="ft-bf__property-icon"><?php echo $property_icons[$pt] ?? ''; ?></span>
                            <span><?php echo esc_html($pt); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <label class="ft-bf__field" style="margin-top:20px">
                    <span>When are you looking to start?</span>
                    <div class="ft-bf__sel-wrap">
                    <select class="ft-bf__select" name="startTime">
                        <?php foreach ($start_times as $st) : ?>
                            <option value="<?php echo esc_attr($st); ?>"><?php echo esc_html($st); ?></option>
                        <?php endforeach; ?>
                    </select>
                    </div>
                </label>
                <div class="ft-bf__actions">
                    <span></span>
                    <button class="ft-bf__next" type="button">Continue <span class="ft-bf__arrow" aria-hidden="true">&rarr;</span></button>
                </div>
            </section>

            <section class="ft-bf__step" data-step="2" hidden>
                <h3 class="ft-bf__question">Your contact details</h3>
                <label class="ft-bf__field"><span>Full name</span><input class="ft-bf__input" name="fullName" type="text" autocomplete="name" placeholder="Jane Doe" required></label>
                <label class="ft-bf__field"><span>Email</span><input class="ft-bf__input" name="email" type="email" autocomplete="email" placeholder="jane@email.com" required></label>
                <label class="ft-bf__field">
                    <span>Phone</span>
                    <div class="ft-bf__phone-wrap">
                        <span class="ft-bf__phone-pfx" aria-hidden="true">+1</span>
                        <input class="ft-bf__input" name="phoneLocal" type="tel" autocomplete="tel" placeholder="(416) 555-0199" required>
                    </div>
                </label>
                <div class="ft-bf__actions">
                    <button class="ft-bf__back" type="button">&larr; Back</button>
                    <button class="ft-bf__next" type="button">Continue <span class="ft-bf__arrow" aria-hidden="true">&rarr;</span></button>
                </div>
            </section>

            <section class="ft-bf__step" data-step="3" hidden>
                <h3 class="ft-bf__question">Where should we visit?</h3>
                <div class="ft-bf__addr-row">
                    <label class="ft-bf__field">
                        <span>Street address</span>
                        <input class="ft-bf__input ft-bf__street" name="street" type="text" autocomplete="address-line1" placeholder="123 Main St" required>
                    </label>
                    <label class="ft-bf__field">
                        <span>Unit / Apt</span>
                        <input class="ft-bf__input" name="unit" type="text" autocomplete="address-line2" placeholder="2B">
                    </label>
                </div>
                <div class="ft-bf__columns">
                    <label class="ft-bf__field">
                        <span>City</span>
                        <input class="ft-bf__input ft-bf__city" name="city" type="text" autocomplete="address-level2" placeholder="Toronto" required>
                    </label>
                    <label class="ft-bf__field">
                        <span>Province</span>
                        <div class="ft-bf__sel-wrap">
                        <select class="ft-bf__select ft-bf__province" name="province" required>
                            <option value="">Select...</option>
                            <option value="AB">Alberta</option>
                            <option value="BC">British Columbia</option>
                            <option value="MB">Manitoba</option>
                            <option value="NB">New Brunswick</option>
                            <option value="NL">Newfoundland and Labrador</option>
                            <option value="NS">Nova Scotia</option>
                            <option value="NT">Northwest Territories</option>
                            <option value="NU">Nunavut</option>
                            <option value="ON" selected>Ontario</option>
                            <option value="PE">Prince Edward Island</option>
                            <option value="QC">Quebec</option>
                            <option value="SK">Saskatchewan</option>
                            <option value="YT">Yukon</option>
                        </select>
                        </div>
                    </label>
                </div>
                <div class="ft-bf__columns">
                    <label class="ft-bf__field">
                        <span>Postal code</span>
                        <input class="ft-bf__input ft-bf__postal" name="postalCode" type="text" autocomplete="postal-code" placeholder="A1A 1A1" required>
                    </label>
                    <label class="ft-bf__field">
                        <span>Country</span>
                        <input class="ft-bf__input" name="country" type="text" value="Canada" readonly>
                    </label>
                </div>
                <div class="ft-bf__consents">
                    <label class="ft-bf__consent"><input name="privacyConsent" type="checkbox" required><span>I agree to receive promotional emails from Floors Today and have read the <a href="/privacy-policy/">Privacy Policy</a> and <a href="/terms-of-use/">Terms &amp; Conditions</a>.</span></label>
                    <label class="ft-bf__consent"><input name="smsConsent" type="checkbox" required><span>I agree to receive SMS marketing and informational messages from Floors Today at the contact information provided above. Message frequency may vary. Message &amp; data rates may apply. Reply STOP to unsubscribe or HELP for assistance.</span></label>
                    <label class="ft-bf__consent"><input name="emailConsent" type="checkbox" required><span>I agree to receive email marketing communications from Floors Today at the email address provided above. I understand Floors Today may respond to any messages or emails I send.</span></label>
                </div>
                <div class="ft-bf__actions">
                    <button class="ft-bf__back" type="button">&larr; Back</button>
                    <button class="ft-bf__submit" type="submit">Get My Free Estimate <span class="ft-bf__arrow" aria-hidden="true">&rarr;</span></button>
                </div>
            </section>

            <p class="ft-bf__error" role="alert" hidden></p>
        </form>
        <div class="ft-bf__success" hidden>
            <span class="ft-bf__success-icon" aria-hidden="true">&check;</span>
            <h3>Request received</h3>
            <p>A Floors Today specialist will contact you shortly.</p>
        </div>
    </div>
    <script>
    (function () {
        var root = document.getElementById(<?php echo wp_json_encode($instance_id); ?>);
        if (!root || root.dataset.ready === '1') return;
        root.dataset.ready = '1';

        var form  = root.querySelector('.ft-bf__form');
        var error = root.querySelector('.ft-bf__error');
        var state = {
            step: 1,
            flooringType: <?php echo wp_json_encode($flooring_type); ?>,
            propertyType: '',
            totalSteps: 3,
        };
        var productName = <?php echo wp_json_encode($product_name); ?>;

        function showError(msg) {
            error.textContent = msg || '';
            error.hidden = !msg;
        }

        function showStep(step) {
            state.step = step;
            root.querySelectorAll('[data-step]').forEach(function (panel) {
                panel.hidden = Number(panel.dataset.step) !== step;
            });
            root.querySelectorAll('[data-dot]').forEach(function (dot) {
                var n = Number(dot.dataset.dot);
                dot.classList.toggle('is-active', n === step);
                dot.classList.toggle('is-complete', n < step);
                dot.textContent = n < step ? '✓' : String(n);
            });
            root.querySelectorAll('[data-line]').forEach(function (line) {
                line.classList.toggle('is-complete', Number(line.dataset.line) < step);
            });
            showError('');
        }

        root.addEventListener('click', function (event) {
            var choice = event.target.closest('.ft-bf__choice');
            var next   = event.target.closest('.ft-bf__next');
            var back   = event.target.closest('.ft-bf__back');

            if (choice) {
                state[choice.dataset.field] = choice.dataset.value;
                root.querySelectorAll('[data-field="' + choice.dataset.field + '"]').forEach(function (item) {
                    item.classList.toggle('is-selected', item === choice);
                });
            }

            if (next) {
                if (state.step === 1 && !state.propertyType) return showError('Please choose a property type.');
                if (state.step === 2) {
                    var fullName   = (root.querySelector('[name="fullName"]').value || '').trim();
                    var email      = (root.querySelector('[name="email"]').value || '').trim();
                    var phoneLocal = (root.querySelector('[name="phoneLocal"]').value || '').trim();
                    if (!fullName) return showError('Please enter your full name.');
                    if (fullName.split(/\s+/).filter(Boolean).length < 2) return showError('Please enter your first and last name.');
                    if (!email) return showError('Please enter your email address.');
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return showError('Please enter a valid email address.');
                    if (!phoneLocal) return showError('Please enter your phone number.');
                }
                showStep(Math.min(state.totalSteps, state.step + 1));
            }

            if (back) showStep(Math.max(1, state.step - 1));
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            showError('');

            if (!form.reportValidity()) return;

            var data       = new FormData(form);
            var fullName   = String(data.get('fullName') || '').trim();
            if (fullName.split(/\s+/).filter(Boolean).length < 2) return showError('Please enter your first and last name.');
            var phoneLocal = String(data.get('phoneLocal') || '').trim();
            var smsConsent = data.get('smsConsent') === 'on';
            var emailConsent = data.get('emailConsent') === 'on';
            var privacyConsent = data.get('privacyConsent') === 'on';
            var phone      = '+1 ' + phoneLocal;
            var street     = String(data.get('street') || '').trim();
            var unit       = String(data.get('unit') || '').trim();
            var city       = String(data.get('city') || '').trim();
            var province   = String(data.get('province') || '').trim();
            var postalCode = String(data.get('postalCode') || '').trim();
            var address    = [street + (unit ? ' Unit ' + unit : ''), city, province, postalCode, 'Canada'].filter(Boolean).join(', ');

            var submit = root.querySelector('.ft-bf__submit');
            submit.disabled = true;
            submit.textContent = 'Sending...';

            try {
                var pageUrl      = new URL(window.location.href);
                var referrer     = document.referrer || '';
                var referrerHost = referrer ? new URL(referrer).hostname.replace(/^www\./, '') : '';
                var utmSource    = pageUrl.searchParams.get('hello_social') || pageUrl.searchParams.get('utm_source') || '';
                var attribution  = window.ftGetAttribution ? window.ftGetAttribution() : {};
                utmSource    = attribution.utmSource    || utmSource;
                referrer     = attribution.referrerUrl  || referrer;
                referrerHost = attribution.referrerHost || referrerHost;

                var response = await fetch(root.dataset.endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        fullName:       fullName,
                        email:          data.get('email'),
                        phone:          phone,
                        address:        address,
                        street:         street,
                        unit:           unit,
                        city:           city,
                        province:       province,
                        postalCode:     postalCode,
                        flooringType:   state.flooringType,
                        propertyType:   state.propertyType,
                        startTime:      data.get('startTime'),
                        ftInboxTrap:    data.get('ftInboxTrap'),
                        source:         'Product page form',
                        productName:    productName,
                        pageUrl:        window.location.href,
                        trafficSource:  attribution.trafficSource  || utmSource || referrerHost || 'Direct',
                        referrerUrl:    referrer,
                        utmSource:      utmSource,
                        utmMedium:      attribution.utmMedium   || pageUrl.searchParams.get('utm_medium')   || '',
                        utmCampaign:    attribution.utmCampaign || pageUrl.searchParams.get('utm_campaign') || '',
                        utmContent:     attribution.utmContent  || pageUrl.searchParams.get('utm_content')  || '',
                        utmTerm:        attribution.utmTerm     || pageUrl.searchParams.get('utm_term')     || '',
                        devicePlatform: /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent) ? 'Mobile / Tablet' : 'Desktop',
                        smsConsent: smsConsent,
                        emailConsent: emailConsent,
                        privacyConsent: privacyConsent,
                    }),
                });
                var result = await response.json().catch(function () { return null; });
                if (!response.ok) throw new Error(result && result.message ? result.message : 'We could not send your request.');

                form.hidden = true;
                root.querySelector('.ft-bf__progress').hidden = true;
                root.querySelector('.ft-bf__success').hidden = false;

                if (window.XDTrack && window.XDTrack.lead) {
                    window.XDTrack.lead({
                        formId:       'product_estimate',
                        formName:     'Product Page Estimate',
                        flooringType: state.flooringType,
                        leadSource:   attribution.trafficSource || utmSource || 'Direct',
                        utmSource:    utmSource,
                    });
                }
            } catch (err) {
                showError(err.message || 'We could not send your request. Please try again.');
                submit.disabled = false;
                submit.innerHTML = 'Get My Free Estimate <span class="ft-bf__arrow" aria-hidden="true">&rarr;</span>';
            }
        });

        function ftInitPlaces() {
            var streetInput = root.querySelector('.ft-bf__street');
            if (!streetInput || streetInput.dataset.acInit) return;
            streetInput.dataset.acInit = '1';
            var ac = new google.maps.places.Autocomplete(streetInput, {
                types: ['address'],
                componentRestrictions: { country: 'ca' },
                fields: ['address_components'],
            });
            ac.addListener('place_changed', function () {
                var place = ac.getPlace();
                if (!place || !place.address_components) return;
                var map = {};
                place.address_components.forEach(function (c) {
                    c.types.forEach(function (t) { map[t] = c; });
                });
                var num = map['street_number'] ? map['street_number'].long_name : '';
                var rt  = map['route']         ? map['route'].long_name         : '';
                streetInput.value = (num + ' ' + rt).trim();
                var cityEl     = root.querySelector('.ft-bf__city');
                var provinceEl = root.querySelector('.ft-bf__province');
                var postalEl   = root.querySelector('.ft-bf__postal');
                if (cityEl     && map['locality'])                    cityEl.value     = map['locality'].long_name;
                if (provinceEl && map['administrative_area_level_1']) provinceEl.value = map['administrative_area_level_1'].short_name;
                if (postalEl   && map['postal_code'])                 postalEl.value   = map['postal_code'].long_name;
            });
        }

        if (window.__ftPlacesLoaded) {
            ftInitPlaces();
        } else {
            document.addEventListener('ft:places:ready', ftInitPlaces);
        }
    }());
    </script>
    <?php

    return ob_get_clean();
}

add_shortcode('floors_booking_form_products', 'ft_next_booking_form_products_shortcode');

// ── [floors_booking_form_contact] ───────────────────────────────────────────

function ft_next_booking_form_contact_shortcode($atts) {
    static $styles_printed = false;

    $settings      = function_exists('ft_next_homepage_settings') ? ft_next_homepage_settings() : [];
    $primary_color = sanitize_hex_color($settings['primary_color']  ?? '') ?: '#155f99';
    $endpoint      = rest_url('floors-today/v1/inbox-leads');
    $instance_id   = wp_unique_id('ft-bfc-');

    ob_start();

    if (!$styles_printed) {
        $styles_printed = true;
        ?>
        <style id="ft-bfc-styles">
            .ft-cf, .ft-cf * { box-sizing: border-box; }
            .ft-cf button, .ft-cf input, .ft-cf textarea { font-family: inherit; }
            .ft-cf button { appearance: none; -webkit-appearance: none; text-transform: none; letter-spacing: 0; box-shadow: none; }
            .ft-cf { display: block; width: 100% !important; max-width: 100% !important; margin-inline: 0 !important; padding: clamp(20px,5vw,36px); border: 1px solid rgba(255,255,255,.5); border-radius: 20px; background: #fff; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25); color: #0f172a; font-family: inherit; font-size: 14px; }
            .elementor-shortcode > .ft-cf, .elementor-widget-shortcode .ft-cf, .elementor-widget-container > .ft-cf { width: 100% !important; max-width: 100% !important; }
            .ft-cf__heading { margin: 0 0 28px; text-align: center; }
            .ft-cf__title { margin: 0; color: #020617; font-family: Georgia,"Times New Roman",serif; font-size: 1.9rem !important; font-weight: 700; line-height: 1.12; }
            .ft-cf__subtitle { margin: 8px 0 0; color: #475569; font-size: 14px; }
            .ft-cf__field { display: block; margin: 0 0 16px; }
            .ft-cf__field > span { display: block; margin-bottom: 7px; color: #475569; font-size: 14px; font-weight: 600; }
            .ft-cf__input, .elementor-widget-container .ft-cf__input { display: block; width: 100%; height: 48px; padding: 0 14px; border: 1px solid #d6d3d1 !important; border-radius: 8px !important; background: #fff !important; color: #0f172a; font: inherit; font-size: 16px; box-shadow: none !important; transition: border-color .18s ease, box-shadow .18s ease; }
            .ft-cf__input:focus, .elementor-widget-container .ft-cf__input:focus { border-color: var(--ft-cf-primary) !important; outline: 0; box-shadow: 0 0 0 3px color-mix(in srgb, var(--ft-cf-primary) 18%, transparent) !important; }
            .ft-cf__textarea { display: block; width: 100%; min-height: 120px; padding: 12px 14px; border: 1px solid #d6d3d1 !important; border-radius: 8px !important; background: #fff !important; color: #0f172a; font: inherit; font-size: 16px; line-height: 1.5; resize: vertical; box-shadow: none !important; transition: border-color .18s ease, box-shadow .18s ease; }
            .ft-cf__textarea:focus { border-color: var(--ft-cf-primary) !important; outline: 0; box-shadow: 0 0 0 3px color-mix(in srgb, var(--ft-cf-primary) 18%, transparent) !important; }
            .ft-cf__phone-wrap { display: flex; align-items: stretch; border: 1px solid #d6d3d1; border-radius: 8px; overflow: hidden; background: #fff; transition: border-color .18s ease, box-shadow .18s ease; }
            .ft-cf__phone-wrap:focus-within { border-color: var(--ft-cf-primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--ft-cf-primary) 18%, transparent); }
            .ft-cf__phone-pfx { display: flex; align-items: center; padding: 0 12px; background: #f5f5f4; border-right: 1px solid #d6d3d1; font-size: 15px; color: #64748b; user-select: none; flex-shrink: 0; }
            .ft-cf__phone-wrap .ft-cf__input { border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; }
            .ft-cf__consents { margin: 18px 0 0; display: grid; gap: 10px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; padding: 14px; text-align: left; }
            .ft-cf__consent { display: flex; align-items: flex-start; gap: 10px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; color: #475569; font-size: 12px; line-height: 1.5; }
            .ft-cf__consent input { width: 16px !important; height: 16px !important; min-width: 16px !important; margin: 2px 0 0 !important; padding: 0 !important; border: 1px solid #cbd5e1 !important; border-radius: 3px !important; background: #fff !important; box-shadow: none !important; appearance: auto !important; -webkit-appearance: checkbox !important; }
            .ft-cf__consent a { color: var(--ft-cf-primary); font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }
            .ft-cf button.ft-cf__submit { display: inline-flex; width: 100%; justify-content: center; align-items: center; gap: 8px; min-height: 48px; padding: 10px 24px; margin-top: 8px; border: 0 !important; border-radius: 999px !important; background: var(--ft-cf-primary) !important; color: #fff !important; font: inherit; font-size: 16px; font-weight: 700; cursor: pointer; transition: background-color .18s ease, box-shadow .18s ease, transform .12s ease, opacity .18s ease; }
            .ft-cf button.ft-cf__submit:hover { background: color-mix(in srgb, var(--ft-cf-primary) 90%, white) !important; box-shadow: 0 10px 20px rgba(35,91,184,.18) !important; }
            .ft-cf button.ft-cf__submit:focus-visible { outline: 0; box-shadow: 0 0 0 3px color-mix(in srgb, var(--ft-cf-primary) 24%, transparent); }
            .ft-cf button.ft-cf__submit:active { transform: translateY(1px); }
            .ft-cf button.ft-cf__submit:disabled { cursor: wait; opacity: .65; animation: ft-cf-pulse 1.1s ease-in-out infinite; }
            @keyframes ft-cf-pulse { 0%,100% { opacity: .65; } 50% { opacity: .88; } }
            .ft-cf__error { margin: 14px 0 0; color: #dc2626; font-size: 14px; font-weight: 600; }
            .ft-cf__success { min-height: 280px; text-align: center; align-content: center; animation: ft-cf-fade .22s ease both; }
            .ft-cf__success-icon { display: inline-flex; width: 64px; height: 64px; align-items: center; justify-content: center; border-radius: 50%; background: #ecfdf5; color: #047857; font-size: 32px; font-weight: 700; }
            .ft-cf__success h3 { margin: 18px 0 8px; color: #0f172a; font-size: 22px; }
            .ft-cf__success p { margin: 0; color: #475569; }
            .ft-cf__trap { position: absolute !important; left: -10000px !important; width: 1px !important; height: 1px !important; overflow: hidden !important; }
            @keyframes ft-cf-fade { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
            @media (max-width: 560px) {
                .ft-cf, .elementor-shortcode > .ft-cf, .elementor-widget-shortcode .ft-cf, .elementor-widget-container > .ft-cf { margin-left: 10px !important; margin-right: 10px !important; width: calc(100% - 20px) !important; max-width: calc(100% - 20px) !important; }
                .ft-cf__title { font-size: 25px !important; }
            }
        </style>
        <?php
    }
    ?>
    <div
        id="<?php echo esc_attr($instance_id); ?>"
        class="ft-cf"
        data-endpoint="<?php echo esc_url($endpoint); ?>"
        style="<?php echo esc_attr('--ft-cf-primary:' . $primary_color . ';'); ?>"
    >
        <div class="ft-cf__heading">
            <h2 class="ft-cf__title">Get in Touch</h2>
            <p class="ft-cf__subtitle">We&rsquo;ll get back to you as soon as possible.</p>
        </div>
        <form class="ft-cf__form">
            <div class="ft-cf__trap" aria-hidden="true">
                <label>Leave this field empty<input name="ftInboxTrap" type="text" tabindex="-1" autocomplete="new-password"></label>
            </div>
            <label class="ft-cf__field">
                <span>Full name</span>
                <input class="ft-cf__input" name="fullName" type="text" autocomplete="name" placeholder="Jane Doe" required>
            </label>
            <label class="ft-cf__field">
                <span>Email</span>
                <input class="ft-cf__input" name="email" type="email" autocomplete="email" placeholder="jane@email.com" required>
            </label>
            <label class="ft-cf__field">
                <span>Phone</span>
                <div class="ft-cf__phone-wrap">
                    <span class="ft-cf__phone-pfx" aria-hidden="true">+1</span>
                    <input class="ft-cf__input" name="phoneLocal" type="tel" autocomplete="tel" placeholder="(416) 555-0199" required>
                </div>
            </label>
            <label class="ft-cf__field">
                <span>Message <span style="font-weight:400;color:#94a3b8;">(optional)</span></span>
                <textarea class="ft-cf__textarea" name="message" placeholder="How can we help you?"></textarea>
            </label>
            <div class="ft-cf__consents">
                <label class="ft-cf__consent"><input name="privacyConsent" type="checkbox" required><span>I agree to receive promotional emails from Floors Today and have read the <a href="/privacy-policy/">Privacy Policy</a> and <a href="/terms-of-use/">Terms &amp; Conditions</a>.</span></label>
                <label class="ft-cf__consent"><input name="smsConsent" type="checkbox" required><span>I agree to receive SMS marketing and informational messages from Floors Today at the contact information provided above. Message frequency may vary. Message &amp; data rates may apply. Reply STOP to unsubscribe or HELP for assistance.</span></label>
                <label class="ft-cf__consent"><input name="emailConsent" type="checkbox" required><span>I agree to receive email marketing communications from Floors Today at the email address provided above. I understand Floors Today may respond to any messages or emails I send.</span></label>
            </div>
            <button class="ft-cf__submit" type="submit">Send Message <span aria-hidden="true">&rarr;</span></button>
            <p class="ft-cf__error" role="alert" hidden></p>
        </form>
        <div class="ft-cf__success" hidden>
            <span class="ft-cf__success-icon" aria-hidden="true">&check;</span>
            <h3>Message sent!</h3>
            <p>A Floors Today specialist will be in touch with you shortly.</p>
        </div>
    </div>
    <script>
    (function () {
        var root = document.getElementById(<?php echo wp_json_encode($instance_id); ?>);
        if (!root || root.dataset.ready === '1') return;
        root.dataset.ready = '1';

        var form  = root.querySelector('.ft-cf__form');
        var error = root.querySelector('.ft-cf__error');

        function showError(msg) {
            error.textContent = msg || '';
            error.hidden = !msg;
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            showError('');

            var data       = new FormData(form);
            var fullName   = String(data.get('fullName') || '').trim();
            var email      = String(data.get('email')    || '').trim();
            var phoneLocal = String(data.get('phoneLocal') || '').trim();
            var smsConsent = data.get('smsConsent') === 'on';
            var emailConsent = data.get('emailConsent') === 'on';
            var privacyConsent = data.get('privacyConsent') === 'on';

            if (!fullName) return showError('Please enter your full name.');
            if (fullName.split(/\s+/).filter(Boolean).length < 2) return showError('Please enter your first and last name.');
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return showError('Please enter a valid email address.');
            if (!phoneLocal || phoneLocal.replace(/\D/g, '').length < 7) return showError('Please enter a valid phone number.');

            var phone  = '+1 ' + phoneLocal;
            var submit = root.querySelector('.ft-cf__submit');
            submit.disabled = true;
            submit.textContent = 'Sending...';

            try {
                var pageUrl      = new URL(window.location.href);
                var referrer     = document.referrer || '';
                var referrerHost = referrer ? new URL(referrer).hostname.replace(/^www\./, '') : '';
                var utmSource    = pageUrl.searchParams.get('hello_social') || pageUrl.searchParams.get('utm_source') || '';
                var attribution  = window.ftGetAttribution ? window.ftGetAttribution() : {};
                utmSource    = attribution.utmSource    || utmSource;
                referrer     = attribution.referrerUrl  || referrer;
                referrerHost = attribution.referrerHost || referrerHost;

                var response = await fetch(root.dataset.endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        fullName:       fullName,
                        email:          email,
                        phone:          phone,
                        message:        data.get('message') || '',
                        ftInboxTrap:    data.get('ftInboxTrap'),
                        source:         'Contact form',
                        pageUrl:        window.location.href,
                        trafficSource:  attribution.trafficSource  || utmSource || referrerHost || 'Direct',
                        referrerUrl:    referrer,
                        utmSource:      utmSource,
                        utmMedium:      attribution.utmMedium   || pageUrl.searchParams.get('utm_medium')   || '',
                        utmCampaign:    attribution.utmCampaign || pageUrl.searchParams.get('utm_campaign') || '',
                        utmContent:     attribution.utmContent  || pageUrl.searchParams.get('utm_content')  || '',
                        utmTerm:        attribution.utmTerm     || pageUrl.searchParams.get('utm_term')     || '',
                        devicePlatform: /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent) ? 'Mobile / Tablet' : 'Desktop',
                        smsConsent: smsConsent,
                        emailConsent: emailConsent,
                        privacyConsent: privacyConsent,
                    }),
                });
                var result = await response.json().catch(function () { return null; });
                if (!response.ok) throw new Error(result && result.message ? result.message : 'We could not send your message.');

                form.hidden = true;
                root.querySelector('.ft-cf__success').hidden = false;

                if (window.XDTrack && window.XDTrack.lead) {
                    window.XDTrack.lead({
                        formId:    'contact_form',
                        formName:  'Contact Form',
                        leadSource: attribution.trafficSource || utmSource || 'Direct',
                        utmSource:  utmSource,
                    });
                }
            } catch (err) {
                showError(err.message || 'We could not send your message. Please try again.');
                submit.disabled = false;
                submit.innerHTML = 'Send Message <span aria-hidden="true">&rarr;</span>';
            }
        });
    }());
    </script>
    <?php

    return ob_get_clean();
}

add_shortcode('floors_booking_form_contact', 'ft_next_booking_form_contact_shortcode');
