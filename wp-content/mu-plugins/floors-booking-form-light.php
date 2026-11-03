<?php
/**
 * Plugin Name: Floors Today Light Booking Form
 * Description: [floors_booking_form_light] - the homepage light hero's 3-step estimate form (Project / Address / Contact) for internal pages.
 * Version: 1.0.0
 * Author: xdeye.com
 *
 * Same questions, checks, consents and lead payload as the homepage hero form
 * (next-homepage-src/components/estimate-hero-form.tsx). Leads go to the
 * Floors Today Inbox (and on to the CRM) and land on the same thank-you page.
 *
 * Usage:
 *   [floors_booking_form_light]
 *   [floors_booking_form_light title="Get your free estimate" subtitle="No obligation." header="no" flooring_type="Vinyl"]
 */

if (!defined('ABSPATH')) {
    exit;
}

function ft_bfl_icon($name, $class = 'ft-bfl__icon') {
    $paths = [
        'home'      => '<path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'building'  => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
        'briefcase' => '<path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/>',
        'pin'       => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
        'user'      => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'mail'      => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'phone'     => '<path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/>',
        'calendar'  => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>',
        'clock'     => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'chevron'   => '<path d="m6 9 6 6 6-6"/>',
        'check'     => '<path d="M20 6 9 17l-5-5"/>',
        'left'      => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        'right'     => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'lock'      => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'help'      => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>',
    ];
    return '<svg class="' . esc_attr($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
}

function ft_next_booking_form_light_shortcode($atts = []) {
    static $assets_printed = false;
    static $places_printed = false;

    $atts = shortcode_atts([
        'title'         => 'Book your free in-home estimate',
        'subtitle'      => 'Lock in sale pricing today. No obligation.',
        'header'        => 'yes',
        'flooring_type' => '',
        // Phones: a button slides up the homepage phone form (Details / Address /
        // Confirm). mobile="inline" shows the 3-step form on phones instead.
        'mobile'        => 'sheet',
        'button'        => '',
    ], $atts, 'floors_booking_form_light');

    $settings       = function_exists('ft_next_homepage_settings') ? ft_next_homepage_settings() : [];
    $inbox_settings = function_exists('ft_inbox_settings') ? ft_inbox_settings() : [];
    $primary        = sanitize_hex_color($settings['primary_color'] ?? '') ?: '#235bb8';
    $secondary      = sanitize_hex_color($settings['secondary_color'] ?? '') ?: '#cc9c2e';
    $button         = sanitize_hex_color($settings['form_button_color'] ?? '') ?: $secondary;
    $button_text    = sanitize_hex_color($settings['form_button_text_color'] ?? '') ?: '#ffffff';
    $places_key     = sanitize_text_field($settings['google_places_api_key'] ?? '');
    $phone          = (string) ($settings['phone'] ?? '');
    $phone_href     = preg_replace('/[^\d+]/', '', $phone);
    $endpoint       = rest_url('floors-today/v1/inbox-leads');
    $redirect       = (string) ($inbox_settings['booking_form_redirect_url'] ?? '');
    $image_base     = (function_exists('ft_next_homepage_asset_url') ? ft_next_homepage_asset_url() : home_url('/public/')) . 'images/estimate-hero/';
    $id             = 'ft-bfl-' . wp_unique_id();

    $property_types = [['Residential', 'home'], ['Office Space', 'building'], ['Business', 'briefcase']];
    $floor_types    = [
        ['Solid Hardwood', 'solid-hardwood.webp'],
        ['Engineered Hardwood', 'engineered.webp'],
        ['Laminate', 'laminate.webp'],
        ['Vinyl', 'vinyl.webp'],
        ['Carpet', 'carpet.webp'],
        ['Not sure yet', ''],
    ];
    $provinces = [
        'AB' => 'Alberta', 'BC' => 'British Columbia', 'MB' => 'Manitoba', 'NB' => 'New Brunswick',
        'NL' => 'Newfoundland and Labrador', 'NS' => 'Nova Scotia', 'NT' => 'Northwest Territories',
        'NU' => 'Nunavut', 'ON' => 'Ontario', 'PE' => 'Prince Edward Island', 'QC' => 'Quebec',
        'SK' => 'Saskatchewan', 'YT' => 'Yukon',
    ];
    $start_times = ['ASAP', 'Within 1 month', '1-3 months', '3+ months', 'Just researching'];
    $visit_times = ['Morning', 'Afternoon', 'Evening'];
    $consents    = [
        'privacyConsent' => 'I agree to receive promotional emails from Floors Today and have read the <a href="' . esc_url(home_url('/privacy-policy/')) . '">Privacy Policy</a> and <a href="' . esc_url(home_url('/terms-of-use/')) . '">Terms &amp; Conditions</a>.',
        'smsConsent'     => 'I agree to receive SMS marketing and informational messages from Floors Today at the contact information provided above. Message frequency may vary. Message &amp; data rates may apply. Reply STOP to unsubscribe or HELP for assistance.',
        'emailConsent'   => 'I agree to receive email marketing communications from Floors Today at the email address provided above. I understand Floors Today may respond to any messages or emails I send.',
    ];
    $preset_floor = '';
    foreach ($floor_types as $f) {
        if ($atts['flooring_type'] !== '' && stripos($atts['flooring_type'], $f[0]) !== false) {
            $preset_floor = $f[0];
            break;
        }
    }

    ob_start();

    if (!$assets_printed) {
        $assets_printed = true;
        ?>
        <style id="ft-bfl-styles">
            .ft-bfl{--bfl-primary:<?php echo esc_html($primary); ?>;--bfl-btn:<?php echo esc_html($button); ?>;--bfl-btn-text:<?php echo esc_html($button_text); ?>;--bfl-border:#e5e7eb;--bfl-input:#d1d5db;--bfl-muted:#6b7280;--bfl-text:#111827;--bfl-error:#dc2626;
                max-width:640px;margin:0 auto;overflow:hidden;border:1px solid var(--bfl-border);border-radius:16px;background:#fff;color:var(--bfl-text);box-shadow:0 20px 40px -12px color-mix(in srgb,var(--bfl-primary) 18%,transparent);font-family:inherit;text-align:left;}
            .ft-bfl *,.ft-bfl *::before,.ft-bfl *::after{box-sizing:border-box;}
            .ft-bfl__head{display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:4px 16px;padding:14px 24px;background:var(--bfl-primary);color:#fff;}
            .ft-bfl__head h2{margin:0!important;color:#fff!important;font-size:20px!important;font-weight:700!important;line-height:1.3!important;}
            .ft-bfl__head p{margin:0!important;color:#fff!important;opacity:.85;font-size:14px!important;}
            .ft-bfl__form{position:relative;display:flex;flex-direction:column;gap:20px;padding:20px;margin:0;}
            @media(min-width:768px){.ft-bfl__form{padding:24px;}}
            .ft-bfl__trap{position:absolute!important;left:-9999px!important;width:1px;height:1px;overflow:hidden;}
            .ft-bfl__icon{width:16px;height:16px;flex:none;}
            /* Stepper */
            .ft-bfl__steps{display:flex;align-items:center;gap:8px;margin:0!important;padding:0!important;list-style:none!important;}
            .ft-bfl__steps li{display:flex;flex:1;align-items:center;gap:8px;margin:0!important;padding:0!important;list-style:none!important;}
            .ft-bfl__steps li::before,.ft-bfl__steps li::marker{content:none!important;}
            .ft-bfl__dot{display:flex;width:28px;height:28px;flex:none;align-items:center;justify-content:center;border-radius:50%;background:#f3f4f6;color:var(--bfl-muted);font-size:12px;font-weight:700;transition:background .2s,color .2s,box-shadow .2s;}
            .ft-bfl__dot .ft-bfl__icon{width:14px;height:14px;}
            .ft-bfl__steplabel{color:var(--bfl-muted);font-size:14px;font-weight:600;}
            .ft-bfl__line{height:2px;flex:1;border-radius:2px;background:#f3f4f6;}
            .ft-bfl__steps li.is-current .ft-bfl__dot,.ft-bfl__steps li.is-done .ft-bfl__dot{background:var(--bfl-primary);color:#fff;}
            .ft-bfl__steps li.is-current .ft-bfl__dot{box-shadow:0 0 0 4px color-mix(in srgb,var(--bfl-primary) 15%,transparent);}
            .ft-bfl__steps li.is-current .ft-bfl__steplabel,.ft-bfl__steps li.is-done .ft-bfl__steplabel{color:var(--bfl-text);}
            .ft-bfl__steps li.is-done .ft-bfl__line{background:var(--bfl-primary);}
            .ft-bfl__steps .ft-bfl__num{display:inline;}.ft-bfl__steps .ft-bfl__tick{display:none;}
            .ft-bfl__steps li.is-done .ft-bfl__num{display:none;}.ft-bfl__steps li.is-done .ft-bfl__tick{display:block;}
            /* Steps */
            .ft-bfl__step{display:flex;flex-direction:column;gap:20px;animation:ft-bfl-in .3s ease-out;}
            .ft-bfl__step[hidden]{display:none!important;}
            @keyframes ft-bfl-in{from{opacity:0;transform:translateX(12px);}to{opacity:1;transform:none;}}
            .ft-bfl__legend,.ft-bfl__title{margin:0 0 12px!important;padding:0!important;color:var(--bfl-text)!important;font-size:18px!important;font-weight:700!important;line-height:1.3!important;}
            .ft-bfl__title{margin:0!important;}
            .ft-bfl fieldset{margin:0!important;padding:0!important;border:0!important;min-width:0;}
            .ft-bfl__tiles{display:grid;gap:8px;}
            .ft-bfl__tiles--3{grid-template-columns:repeat(3,minmax(0,1fr));}
            .ft-bfl__tiles--floors{grid-template-columns:repeat(2,minmax(0,1fr));}
            @media(min-width:640px){.ft-bfl__tiles--floors{grid-template-columns:repeat(3,minmax(0,1fr));}}
            .ft-bfl__tile{all:unset;box-sizing:border-box;position:relative;display:flex;align-items:center;gap:12px;border:2px solid var(--bfl-border);border-radius:12px;background:#fff;color:var(--bfl-text);font-size:14px;font-weight:600;line-height:1.25;cursor:pointer;transition:border-color .15s,background .15s;}
            .ft-bfl__tile:hover{border-color:color-mix(in srgb,var(--bfl-primary) 40%,transparent);background:#f9fafb;}
            .ft-bfl__tile:focus-visible{box-shadow:0 0 0 4px color-mix(in srgb,var(--bfl-primary) 20%,transparent);}
            .ft-bfl__tile[aria-pressed="true"]{border-color:var(--bfl-primary);background:color-mix(in srgb,var(--bfl-primary) 5%,#fff);}
            .ft-bfl__tile--prop{height:56px;justify-content:center;padding:0 8px;text-align:center;}
            .ft-bfl__tile--prop .ft-bfl__icon{color:var(--bfl-muted);}
            .ft-bfl__tile--prop[aria-pressed="true"] .ft-bfl__icon{color:var(--bfl-primary);}
            .ft-bfl__tile--floor{padding:6px 12px 6px 6px;}
            .ft-bfl__thumb{position:relative;display:block;width:40px;height:40px;flex:none;overflow:hidden;border-radius:8px;background:#f3f4f6;}
            .ft-bfl__thumb img{display:block;width:100%!important;height:100%!important;object-fit:cover;margin:0!important;}
            .ft-bfl__thumb>.ft-bfl__icon{position:absolute;inset:0;margin:auto;width:20px;height:20px;color:var(--bfl-muted);}
            .ft-bfl__thumbcheck{position:absolute;inset:0;display:none;align-items:center;justify-content:center;background:color-mix(in srgb,var(--bfl-primary) 75%,transparent);color:#fff;}
            .ft-bfl__flabel{min-width:0;flex:1;}
            .ft-bfl__radio{display:none;width:20px;height:20px;flex:none;align-items:center;justify-content:center;border:2px solid var(--bfl-border);border-radius:50%;color:#fff;}
            .ft-bfl__radio .ft-bfl__icon{width:12px;height:12px;visibility:hidden;}
            @media(min-width:640px){.ft-bfl__radio{display:flex;}}
            .ft-bfl__tile--floor[aria-pressed="true"] .ft-bfl__radio{border-color:var(--bfl-primary);background:var(--bfl-primary);}
            .ft-bfl__tile--floor[aria-pressed="true"] .ft-bfl__radio .ft-bfl__icon{visibility:visible;}
            @media(max-width:639px){.ft-bfl__tile--floor[aria-pressed="true"] .ft-bfl__thumbcheck{display:flex;}}
            /* Theme and Elementor button styles (blue fill, white text, big font, pill
               corners) must not reach these buttons: restate the look with !important. */
            .ft-bfl button.ft-bfl__tile{display:flex!important;align-items:center!important;gap:12px!important;width:100%!important;max-width:100%!important;box-sizing:border-box!important;min-width:0!important;min-height:0!important;margin:0!important;border:2px solid var(--bfl-border)!important;border-radius:12px!important;background:#fff!important;background-image:none!important;color:var(--bfl-text)!important;font-family:inherit!important;font-size:14px!important;font-weight:600!important;line-height:1.25!important;letter-spacing:0!important;text-transform:none!important;text-align:left!important;box-shadow:none!important;text-shadow:none!important;}
            .ft-bfl button.ft-bfl__tile:hover{border-color:color-mix(in srgb,var(--bfl-primary) 40%,transparent)!important;background:#f9fafb!important;color:var(--bfl-text)!important;}
            .ft-bfl button.ft-bfl__tile[aria-pressed="true"]{border-color:var(--bfl-primary)!important;background:color-mix(in srgb,var(--bfl-primary) 5%,#fff)!important;}
            .ft-bfl button.ft-bfl__tile:focus-visible{box-shadow:0 0 0 4px color-mix(in srgb,var(--bfl-primary) 20%,transparent)!important;outline:none!important;}
            .ft-bfl button.ft-bfl__tile--prop{height:56px!important;justify-content:center!important;padding:0 8px!important;text-align:center!important;}
            .ft-bfl button.ft-bfl__tile--floor{height:auto!important;padding:6px 12px 6px 6px!important;}
            .ft-bfl button.ft-bfl__tile::before,.ft-bfl button.ft-bfl__tile::after,.ft-bfl button.ft-bfl__submit::after,.ft-bfl button.ft-bfl__back::after{content:none!important;display:none!important;}
            .ft-bfl button.ft-bfl__back{display:flex!important;width:56px!important;height:56px!important;min-height:0!important;margin:0!important;padding:0!important;border:2px solid var(--bfl-border)!important;border-radius:8px!important;background:#fff!important;color:var(--bfl-text)!important;box-shadow:none!important;}
            .ft-bfl button.ft-bfl__back[hidden]{display:none!important;}
            .ft-bfl button.ft-bfl__submit{display:flex!important;height:56px!important;min-height:0!important;margin:0!important;padding:0 16px!important;border:0!important;border-radius:8px!important;background:var(--bfl-btn)!important;background-image:none!important;color:var(--bfl-btn-text)!important;font-family:inherit!important;font-size:18px!important;font-weight:700!important;line-height:1.2!important;letter-spacing:0!important;text-transform:none!important;box-shadow:0 10px 15px -3px color-mix(in srgb,var(--bfl-btn) 25%,transparent)!important;}
            .ft-bfl button .ft-bfl__icon{color:currentColor;}
            .ft-bfl button.ft-bfl__tile--prop .ft-bfl__icon{color:var(--bfl-muted)!important;}
            .ft-bfl button.ft-bfl__tile--prop[aria-pressed="true"] .ft-bfl__icon{color:var(--bfl-primary)!important;}
            /* Small phones: tighter steps, icon above text on the property choices,
               smaller floor cards, so nothing spills past the edges */
            @media(max-width:480px){
                .ft-bfl__form{padding:16px;gap:16px;}
                .ft-bfl__head{padding:12px 16px;}
                .ft-bfl__steps{gap:6px;}
                .ft-bfl__steps li{gap:6px;min-width:0;}
                .ft-bfl__dot{width:24px;height:24px;font-size:11px;}
                .ft-bfl__steplabel{font-size:12px;white-space:nowrap;}
                .ft-bfl__line{min-width:6px;}
                .ft-bfl__legend,.ft-bfl__title{font-size:16px!important;margin-bottom:10px!important;}
                .ft-bfl__tiles{gap:6px;}
                .ft-bfl button.ft-bfl__tile--prop{flex-direction:column!important;justify-content:center!important;gap:4px!important;height:64px!important;padding:6px 4px!important;font-size:12.5px!important;line-height:1.15!important;}
                .ft-bfl button.ft-bfl__tile--floor{gap:8px!important;padding:5px 8px 5px 5px!important;font-size:13px!important;}
                .ft-bfl__thumb{width:36px;height:36px;}
            }
            /* Fields */
            .ft-bfl__grid{display:grid;gap:16px;}
            @media(min-width:640px){.ft-bfl__grid--2{grid-template-columns:repeat(2,minmax(0,1fr));}.ft-bfl__grid--addr{grid-template-columns:1fr 140px;}.ft-bfl__span{grid-column:1/-1;}}
            .ft-bfl__field{display:flex;flex-direction:column;gap:6px;min-width:0;}
            .ft-bfl__field>label{margin:0!important;color:var(--bfl-text)!important;font-size:14px!important;font-weight:600!important;}
            .ft-bfl__control{position:relative;}
            .ft-bfl__control>.ft-bfl__icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--bfl-muted);pointer-events:none;}
            .ft-bfl__control>.ft-bfl__chev{left:auto;right:14px;}
            .ft-bfl input.ft-bfl__input,.ft-bfl select.ft-bfl__input{display:block;width:100%!important;max-width:none!important;height:48px!important;min-height:0!important;margin:0!important;padding:0 16px 0 40px!important;border:1px solid var(--bfl-input)!important;border-radius:8px!important;background:#fff!important;color:var(--bfl-text)!important;font:inherit!important;font-size:16px!important;line-height:normal!important;box-shadow:none!important;outline:none!important;-webkit-appearance:none;appearance:none;transition:border-color .15s,box-shadow .15s;}
            .ft-bfl select.ft-bfl__input{padding-right:40px!important;cursor:pointer;}
            .ft-bfl input.ft-bfl__input::placeholder{color:#9ca3af;}
            .ft-bfl .ft-bfl__input:hover{border-color:color-mix(in srgb,var(--bfl-primary) 50%,transparent)!important;}
            .ft-bfl .ft-bfl__input:focus{border-color:var(--bfl-primary)!important;box-shadow:0 0 0 4px color-mix(in srgb,var(--bfl-primary) 15%,transparent)!important;}
            .ft-bfl .ft-bfl__input[readonly]{background:#f9fafb!important;color:var(--bfl-muted)!important;}
            .ft-bfl__field.is-error .ft-bfl__input{border-color:var(--bfl-error)!important;}
            .ft-bfl__field.is-error .ft-bfl__control>.ft-bfl__icon{color:var(--bfl-error);}
            .ft-bfl__err{margin:0!important;color:var(--bfl-error)!important;font-size:12px!important;font-weight:500;}
            .ft-bfl__err:empty{display:none;}
            .ft-bfl fieldset .ft-bfl__err{margin-top:8px!important;}
            /* Consents */
            .ft-bfl__consents{display:flex;flex-direction:column;gap:8px;}
            .ft-bfl__consents-title{margin:0!important;color:var(--bfl-text)!important;font-size:14px!important;font-weight:700!important;}
            .ft-bfl__consent{display:flex!important;align-items:flex-start;gap:12px;margin:0!important;padding:12px;border:2px solid var(--bfl-border);border-radius:12px;color:var(--bfl-muted);font-size:12px!important;font-weight:400!important;line-height:1.4!important;cursor:pointer;transition:border-color .15s,background .15s;}
            .ft-bfl__consent:hover{border-color:color-mix(in srgb,var(--bfl-primary) 40%,transparent);}
            .ft-bfl__consent input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none;}
            .ft-bfl__box{display:flex;width:20px;height:20px;flex:none;margin-top:1px;align-items:center;justify-content:center;border:2px solid var(--bfl-input);border-radius:6px;background:#fff;color:#fff;transition:background .15s,border-color .15s;}
            .ft-bfl__box .ft-bfl__icon{width:14px;height:14px;visibility:hidden;}
            .ft-bfl__consent:has(input:checked){border-color:var(--bfl-primary);background:color-mix(in srgb,var(--bfl-primary) 5%,#fff);color:var(--bfl-text);}
            .ft-bfl__consent:has(input:checked) .ft-bfl__box{border-color:var(--bfl-primary);background:var(--bfl-primary);}
            .ft-bfl__consent:has(input:checked) .ft-bfl__box .ft-bfl__icon{visibility:visible;animation:ft-bfl-pop .2s ease-out;}
            .ft-bfl__consent:has(input:focus-visible) .ft-bfl__box{box-shadow:0 0 0 4px color-mix(in srgb,var(--bfl-primary) 20%,transparent);}
            .ft-bfl__consents.is-error .ft-bfl__consent:not(:has(input:checked)){border-color:color-mix(in srgb,var(--bfl-error) 50%,transparent);}
            .ft-bfl__consent a{color:var(--bfl-primary)!important;font-weight:600;text-decoration:underline!important;}
            @keyframes ft-bfl-pop{from{transform:scale(.5);}to{transform:scale(1);}}
            /* Buttons */
            .ft-bfl__formerr{margin:0!important;padding:8px 12px;border-radius:8px;background:#fef2f2;color:var(--bfl-error)!important;font-size:14px!important;font-weight:500;}
            .ft-bfl__formerr[hidden]{display:none!important;}
            .ft-bfl__actions{display:flex;flex-direction:column;gap:12px;}
            .ft-bfl__row{display:flex;gap:8px;}
            .ft-bfl__back{all:unset;box-sizing:border-box;display:flex;width:56px;height:56px;flex:none;align-items:center;justify-content:center;border:2px solid var(--bfl-border);border-radius:8px;background:#fff;color:var(--bfl-text);cursor:pointer;transition:border-color .15s;}
            .ft-bfl__back:hover{border-color:color-mix(in srgb,var(--bfl-primary) 40%,transparent);}
            .ft-bfl__back[hidden]{display:none!important;}
            .ft-bfl__back .ft-bfl__icon,.ft-bfl__submit .ft-bfl__icon{width:20px;height:20px;}
            .ft-bfl__submit{all:unset;box-sizing:border-box;display:flex;height:56px;flex:1;align-items:center;justify-content:center;gap:8px;border-radius:8px;background:var(--bfl-btn);color:var(--bfl-btn-text);font-size:18px;font-weight:700;text-align:center;cursor:pointer;box-shadow:0 10px 15px -3px color-mix(in srgb,var(--bfl-btn) 25%,transparent);transition:filter .15s,transform .1s;}
            .ft-bfl__submit:hover{filter:brightness(1.05);}
            .ft-bfl__submit:active{transform:scale(.99);}
            .ft-bfl__submit[disabled]{opacity:.7;cursor:default;}
            .ft-bfl__submit .ft-bfl__icon{transition:transform .15s;}
            .ft-bfl__submit:hover .ft-bfl__icon{transform:translateX(2px);}
            .ft-bfl__foot{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px 24px;color:var(--bfl-muted);font-size:12px;text-align:center;}
            @media(min-width:640px){.ft-bfl__foot{flex-direction:row;}}
            .ft-bfl__foot span,.ft-bfl__foot a{display:flex;align-items:center;gap:6px;}
            .ft-bfl__foot .ft-bfl__icon{width:14px;height:14px;}
            .ft-bfl__foot a{color:var(--bfl-primary)!important;font-weight:600;text-decoration:none!important;}
            /* Done */
            .ft-bfl__done{display:flex;flex-direction:column;align-items:center;gap:16px;padding:32px;text-align:center;animation:ft-bfl-in .3s ease-out;}
            .ft-bfl__done[hidden]{display:none!important;}
            .ft-bfl__done-icon{display:flex;width:56px;height:56px;align-items:center;justify-content:center;border-radius:50%;background:var(--bfl-primary);color:#fff;box-shadow:0 0 0 8px color-mix(in srgb,var(--bfl-primary) 10%,transparent);}
            .ft-bfl__done-icon .ft-bfl__icon{width:28px;height:28px;}
            .ft-bfl__done h3{margin:0!important;color:var(--bfl-text)!important;font-size:24px!important;font-weight:700!important;}
            .ft-bfl__done p{max-width:28rem;margin:0!important;color:var(--bfl-muted)!important;line-height:1.6;}
            .ft-bfl__done a{color:var(--bfl-primary)!important;font-weight:600;text-decoration:underline!important;text-underline-offset:4px;}
            @media(prefers-reduced-motion:reduce){.ft-bfl__step,.ft-bfl__done,.ft-bfl__consent .ft-bfl__icon{animation:none!important;}}
        </style>
        <?php
    }

    if ($places_key && !$places_printed) {
        $places_printed = true;
        ?>
        <script>
            if (!window.__ftPlacesScript && !(window.google && window.google.maps && window.google.maps.places)) {
                window.__ftPlacesScript = true;
                window.__ftPlacesReady = function () {
                    window.__ftPlacesLoaded = true;
                    document.dispatchEvent(new CustomEvent('ft:places:ready'));
                };
                var ftBflPlaces = document.createElement('script');
                ftBflPlaces.async = true;
                ftBflPlaces.src = 'https://maps.googleapis.com/maps/api/js?key=<?php echo esc_js($places_key); ?>&libraries=places&loading=async&callback=__ftPlacesReady';
                document.head.appendChild(ftBflPlaces);
            }
        </script>
        <?php
    }
    $phone_sheet = $atts['mobile'] !== 'inline' && function_exists('ft_bfl_mobile_sheet_html');
    ?>
    <div class="ft-bfl-wrap<?php echo $phone_sheet ? ' ft-bfl-wrap--sheet' : ''; ?>" style="--bfl-btn:<?php echo esc_attr($button); ?>;--bfl-btn-text:<?php echo esc_attr($button_text); ?>;">
    <div class="ft-bfl" id="<?php echo esc_attr($id); ?>"
         data-endpoint="<?php echo esc_url($endpoint); ?>"
         data-redirect="<?php echo esc_url($redirect); ?>"
         data-preset-floor="<?php echo esc_attr($preset_floor); ?>">
        <?php if ($atts['header'] !== 'no') : ?>
            <div class="ft-bfl__head">
                <h2><?php echo esc_html($atts['title']); ?></h2>
                <?php if ($atts['subtitle'] !== '') : ?><p><?php echo esc_html($atts['subtitle']); ?></p><?php endif; ?>
            </div>
        <?php endif; ?>

        <form class="ft-bfl__form" novalidate>
            <ol class="ft-bfl__steps" aria-label="Form progress">
                <?php foreach (['Project', 'Address', 'Contact'] as $i => $label) : ?>
                    <li class="<?php echo $i === 0 ? 'is-current' : ''; ?>"<?php echo $i === 0 ? ' aria-current="step"' : ''; ?>>
                        <span class="ft-bfl__dot"><span class="ft-bfl__num"><?php echo (int) $i + 1; ?></span><?php echo ft_bfl_icon('check', 'ft-bfl__icon ft-bfl__tick'); ?></span>
                        <span class="ft-bfl__steplabel"><?php echo esc_html($label); ?></span>
                        <?php if ($i < 2) : ?><span class="ft-bfl__line" aria-hidden="true"></span><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>

            <?php /* Honeypot - the inbox endpoint rejects any submission that fills this in */ ?>
            <div class="ft-bfl__trap" aria-hidden="true">
                <label>Leave this field empty <input name="ftInboxTrap" type="text" tabindex="-1" autocomplete="new-password"></label>
            </div>

            <div class="ft-bfl__step" data-step="0">
                <fieldset data-group="property">
                    <legend class="ft-bfl__legend">Property type</legend>
                    <div class="ft-bfl__tiles ft-bfl__tiles--3">
                        <?php foreach ($property_types as $p) : ?>
                            <button type="button" class="ft-bfl__tile ft-bfl__tile--prop" aria-pressed="false" data-value="<?php echo esc_attr($p[0]); ?>">
                                <?php echo ft_bfl_icon($p[1]); ?><span><?php echo esc_html($p[0]); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <p class="ft-bfl__err" data-error="property"></p>
                </fieldset>
                <fieldset data-group="floors">
                    <legend class="ft-bfl__legend">What floors interest you?</legend>
                    <div class="ft-bfl__tiles ft-bfl__tiles--floors">
                        <?php foreach ($floor_types as $f) : ?>
                            <button type="button" class="ft-bfl__tile ft-bfl__tile--floor" aria-pressed="false" data-value="<?php echo esc_attr($f[0]); ?>">
                                <span class="ft-bfl__thumb">
                                    <?php if ($f[1] !== '') : ?>
                                        <img src="<?php echo esc_url($image_base . $f[1]); ?>" alt="" width="40" height="40" loading="lazy" decoding="async">
                                    <?php else : ?>
                                        <?php echo ft_bfl_icon('help'); ?>
                                    <?php endif; ?>
                                    <span class="ft-bfl__thumbcheck" aria-hidden="true"><?php echo ft_bfl_icon('check'); ?></span>
                                </span>
                                <span class="ft-bfl__flabel"><?php echo esc_html($f[0]); ?></span>
                                <span class="ft-bfl__radio" aria-hidden="true"><?php echo ft_bfl_icon('check'); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <p class="ft-bfl__err" data-error="floors"></p>
                </fieldset>
            </div>

            <div class="ft-bfl__step" data-step="1" hidden>
                <div class="ft-bfl__grid ft-bfl__grid--addr">
                    <p class="ft-bfl__title ft-bfl__span">Where should we visit?</p>
                    <div class="ft-bfl__field" data-field="street">
                        <label for="<?php echo esc_attr($id); ?>-street">Street address</label>
                        <div class="ft-bfl__control"><?php echo ft_bfl_icon('home'); ?><input class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-street" name="street" type="text" autocomplete="address-line1" placeholder="123 Main St"></div>
                        <p class="ft-bfl__err" data-error="street"></p>
                    </div>
                    <div class="ft-bfl__field">
                        <label for="<?php echo esc_attr($id); ?>-unit">Unit / Apt</label>
                        <div class="ft-bfl__control"><?php echo ft_bfl_icon('home'); ?><input class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-unit" name="unit" type="text" autocomplete="address-line2" placeholder="2B"></div>
                    </div>
                    <div class="ft-bfl__grid ft-bfl__grid--2 ft-bfl__span">
                        <div class="ft-bfl__field" data-field="city">
                            <label for="<?php echo esc_attr($id); ?>-city">City</label>
                            <div class="ft-bfl__control"><?php echo ft_bfl_icon('pin'); ?><input class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-city" name="city" type="text" autocomplete="address-level2" placeholder="Toronto"></div>
                            <p class="ft-bfl__err" data-error="city"></p>
                        </div>
                        <div class="ft-bfl__field">
                            <label for="<?php echo esc_attr($id); ?>-province">Province</label>
                            <div class="ft-bfl__control"><?php echo ft_bfl_icon('pin'); ?>
                                <select class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-province" name="province">
                                    <?php foreach ($provinces as $code => $pname) : ?>
                                        <option value="<?php echo esc_attr($code); ?>"<?php selected($code, 'ON'); ?>><?php echo esc_html($pname); ?></option>
                                    <?php endforeach; ?>
                                </select><?php echo ft_bfl_icon('chevron', 'ft-bfl__icon ft-bfl__chev'); ?>
                            </div>
                        </div>
                        <div class="ft-bfl__field" data-field="postalCode">
                            <label for="<?php echo esc_attr($id); ?>-postal">Postal code</label>
                            <div class="ft-bfl__control"><?php echo ft_bfl_icon('pin'); ?><input class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-postal" name="postalCode" type="text" autocomplete="postal-code" placeholder="A1A 1A1"></div>
                            <p class="ft-bfl__err" data-error="postalCode"></p>
                        </div>
                        <div class="ft-bfl__field">
                            <label for="<?php echo esc_attr($id); ?>-country">Country</label>
                            <div class="ft-bfl__control"><?php echo ft_bfl_icon('pin'); ?><input class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-country" type="text" value="Canada" readonly tabindex="-1"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ft-bfl__step" data-step="2" hidden>
                <div class="ft-bfl__grid ft-bfl__grid--2">
                    <p class="ft-bfl__title ft-bfl__span">Your contact details</p>
                    <div class="ft-bfl__field" data-field="name">
                        <label for="<?php echo esc_attr($id); ?>-name">Full name</label>
                        <div class="ft-bfl__control"><?php echo ft_bfl_icon('user'); ?><input class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-name" name="fullName" type="text" autocomplete="name" placeholder="Jane Doe"></div>
                        <p class="ft-bfl__err" data-error="name"></p>
                    </div>
                    <div class="ft-bfl__field" data-field="email">
                        <label for="<?php echo esc_attr($id); ?>-email">Email</label>
                        <div class="ft-bfl__control"><?php echo ft_bfl_icon('mail'); ?><input class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-email" name="email" type="email" autocomplete="email" placeholder="jane@email.com"></div>
                        <p class="ft-bfl__err" data-error="email"></p>
                    </div>
                    <div class="ft-bfl__field" data-field="phone">
                        <label for="<?php echo esc_attr($id); ?>-phone">Phone</label>
                        <div class="ft-bfl__control"><?php echo ft_bfl_icon('phone'); ?><input class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="(416) 555-0199"></div>
                        <p class="ft-bfl__err" data-error="phone"></p>
                    </div>
                    <div class="ft-bfl__field">
                        <label for="<?php echo esc_attr($id); ?>-rooms">Number of rooms</label>
                        <div class="ft-bfl__control"><?php echo ft_bfl_icon('home'); ?><input class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-rooms" name="numberOfRooms" type="number" inputmode="numeric" min="1" max="99" step="1" placeholder="Enter number of rooms"></div>
                    </div>
                    <div class="ft-bfl__field">
                        <label for="<?php echo esc_attr($id); ?>-start">When are you looking to start?</label>
                        <div class="ft-bfl__control"><?php echo ft_bfl_icon('calendar'); ?>
                            <select class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-start" name="startTime">
                                <?php foreach ($start_times as $t) : ?><option value="<?php echo esc_attr($t); ?>"><?php echo esc_html($t); ?></option><?php endforeach; ?>
                            </select><?php echo ft_bfl_icon('chevron', 'ft-bfl__icon ft-bfl__chev'); ?>
                        </div>
                    </div>
                    <div class="ft-bfl__field">
                        <label for="<?php echo esc_attr($id); ?>-visit">Preferred visit time</label>
                        <div class="ft-bfl__control"><?php echo ft_bfl_icon('clock'); ?>
                            <select class="ft-bfl__input" id="<?php echo esc_attr($id); ?>-visit" name="preferredVisitTime">
                                <?php foreach ($visit_times as $t) : ?><option value="<?php echo esc_attr($t); ?>"><?php echo esc_html($t); ?></option><?php endforeach; ?>
                            </select><?php echo ft_bfl_icon('chevron', 'ft-bfl__icon ft-bfl__chev'); ?>
                        </div>
                    </div>
                </div>
                <div class="ft-bfl__consents" role="group" aria-labelledby="<?php echo esc_attr($id); ?>-consent-title">
                    <p class="ft-bfl__consents-title" id="<?php echo esc_attr($id); ?>-consent-title">Please confirm</p>
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

            <div class="ft-bfl__actions">
                <div class="ft-bfl__row">
                    <button type="button" class="ft-bfl__back" aria-label="Previous step" hidden><?php echo ft_bfl_icon('left'); ?></button>
                    <button type="submit" class="ft-bfl__submit"><span class="ft-bfl__submit-text">Continue</span><?php echo ft_bfl_icon('right'); ?></button>
                </div>
                <div class="ft-bfl__foot">
                    <span><?php echo ft_bfl_icon('lock'); ?>No obligation. We never share your details.</span>
                    <?php if ($phone !== '') : ?>
                        <a href="<?php echo esc_url('tel:' . $phone_href); ?>"><?php echo ft_bfl_icon('phone'); ?>Or call <?php echo esc_html($phone); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <div class="ft-bfl__done" role="status" hidden>
            <span class="ft-bfl__done-icon"><?php echo ft_bfl_icon('check'); ?></span>
            <h3></h3>
            <p>A flooring specialist will call you shortly to confirm a time. Your sale pricing is locked in.</p>
            <?php if ($phone !== '') : ?>
                <a href="<?php echo esc_url('tel:' . $phone_href); ?>">Prefer to talk now? <?php echo esc_html($phone); ?></a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($phone_sheet) { echo ft_bfl_mobile_sheet_html($id, $settings, $atts); } ?>
    </div><?php /* .ft-bfl-wrap */ ?>
    <script>
    (function () {
        var root = document.getElementById(<?php echo wp_json_encode($id); ?>);
        if (!root || root.dataset.ready) return;
        root.dataset.ready = '1';

        var form = root.querySelector('.ft-bfl__form');
        var steps = root.querySelectorAll('.ft-bfl__step');
        var dots = root.querySelectorAll('.ft-bfl__steps li');
        var back = root.querySelector('.ft-bfl__back');
        var submit = root.querySelector('.ft-bfl__submit');
        var submitText = root.querySelector('.ft-bfl__submit-text');
        var formErr = root.querySelector('.ft-bfl__formerr');
        var state = { step: 0, property: '', floor: '', pending: false };

        var AREA = ['204','226','236','249','250','263','289','306','343','354','365','367','368','382','403','416','418','428','431','437','438','450','468','474','506','514','519','548','579','581','584','587','604','613','639','647','672','683','705','709','742','753','778','780','782','807','819','825','867','873','902','905'];
        function validPhone(v) {
            var d = v.replace(/\D/g, '');
            if (d.length === 11 && d.charAt(0) === '1') d = d.slice(1);
            if (d.length !== 10) return false;
            if (d.charAt(3) === '0' || d.charAt(3) === '1') return false;
            return AREA.indexOf(d.slice(0, 3)) !== -1;
        }
        var POSTAL = /^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/;
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

        function show(step) {
            state.step = step;
            steps.forEach(function (s, i) { s.hidden = i !== step; });
            dots.forEach(function (li, i) {
                li.classList.toggle('is-done', i < step);
                li.classList.toggle('is-current', i === step);
                if (i === step) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
            });
            back.hidden = step === 0;
            submitText.textContent = step < 2 ? 'Continue' : 'Book Your Appointment';
            if (step === 1) attachPlaces();
        }

        function pick(groupSel, key, btn) {
            root.querySelectorAll(groupSel + ' .ft-bfl__tile').forEach(function (b) { b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'); });
            state[key] = btn.getAttribute('data-value');
            setError(key === 'property' ? 'property' : 'floors', '');
        }
        root.querySelectorAll('[data-group="property"] .ft-bfl__tile').forEach(function (b) {
            b.addEventListener('click', function () { pick('[data-group="property"]', 'property', b); });
        });
        root.querySelectorAll('[data-group="floors"] .ft-bfl__tile').forEach(function (b) {
            b.addEventListener('click', function () { pick('[data-group="floors"]', 'floor', b); });
        });
        if (root.dataset.presetFloor) {
            var preset = root.querySelector('[data-group="floors"] .ft-bfl__tile[data-value="' + root.dataset.presetFloor + '"]');
            if (preset) pick('[data-group="floors"]', 'floor', preset);
        }

        form.elements.phone.addEventListener('input', function (e) { e.target.value = fmtPhone(e.target.value); setError('phone', ''); });
        form.elements.postalCode.addEventListener('input', function (e) { e.target.value = fmtPostal(e.target.value); setError('postalCode', ''); });
        form.elements.numberOfRooms.addEventListener('input', function (e) { e.target.value = e.target.value.replace(/\D/g, '').slice(0, 2); });
        ['street', 'city', 'fullName', 'email'].forEach(function (n) {
            form.elements[n].addEventListener('input', function () { setError(n === 'fullName' ? 'name' : n, ''); formErr.hidden = true; });
        });
        root.querySelectorAll('.ft-bfl__consent input').forEach(function (c) {
            c.addEventListener('change', function () { setError('consent', ''); });
        });

        back.addEventListener('click', function () { clearErrors(); show(state.step - 1); });

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
                ['street', 'city', 'postalCode'].forEach(function (k) { setError(k, ''); });
            });
        }
        document.addEventListener('ft:places:ready', function () { if (state.step === 1) attachPlaces(); });

        // Same checks, in the same steps, as the homepage form
        function validate() {
            var e = {};
            if (state.step === 0) {
                if (!state.property) e.property = 'Please choose a property type.';
                if (!state.floor) e.floors = 'Please choose a flooring option.';
            }
            if (state.step === 1) {
                if (!val('street')) e.street = 'Please enter your street address.';
                if (!val('city')) e.city = 'Please enter your city.';
                if (!val('postalCode')) e.postalCode = 'Please enter your postal code.';
                else if (!POSTAL.test(val('postalCode'))) e.postalCode = 'Enter a valid postal code, e.g. L5B 1M2.';
            }
            if (state.step === 2) {
                var name = val('fullName');
                if (!name) e.name = 'Please enter your full name.';
                else if (name.split(/\s+/).filter(Boolean).length < 2) e.name = 'Please enter your first and last name.';
                var email = val('email');
                if (!email) e.email = 'Please enter your email address.';
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) e.email = 'Please enter a valid email address.';
                if (!validPhone(val('phone'))) e.phone = 'Please enter a valid Canadian phone number.';
                var all = Array.prototype.every.call(root.querySelectorAll('.ft-bfl__consent input'), function (c) { return c.checked; });
                if (!all) e.consent = 'Please check all three boxes to continue.';
            }
            return e;
        }

        function attribution() {
            var url = new URL(window.location.href);
            var a = window.ftGetAttribution ? window.ftGetAttribution() : {};
            var refUrl = a.referrerUrl || document.referrer || '';
            var refHost = a.referrerHost || '';
            if (!refHost && document.referrer) { try { refHost = new URL(document.referrer).hostname.replace(/^www\./, ''); } catch (x) {} }
            var utmSource = a.utmSource || url.searchParams.get('hello_social') || url.searchParams.get('utm_source') || '';
            return {
                trafficSource: a.trafficSource || utmSource || refHost || 'Direct',
                referrerUrl: refUrl,
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
            if (keys.length) {
                keys.forEach(function (k) { setError(k, errs[k]); });
                var first = root.querySelector('.is-error input, .ft-bfl__err:not(:empty)');
                if (first && first.scrollIntoView) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if (state.step < 2) { show(state.step + 1); return; }

            var attr = attribution();
            var unit = val('unit');
            var streetLine = val('street') + (unit ? ' Unit ' + unit : '');
            var payload = {
                fullName: val('fullName'),
                email: val('email'),
                phone: '+1 ' + val('phone'),
                address: [streetLine, val('city'), val('province'), val('postalCode'), 'Canada'].filter(Boolean).join(', '),
                street: val('street'),
                unit: unit,
                city: val('city'),
                province: val('province'),
                postalCode: val('postalCode'),
                flooringType: state.floor,
                propertyType: state.property,
                numberOfRooms: val('numberOfRooms'),
                startTime: val('startTime'),
                preferredVisitTime: val('preferredVisitTime'),
                ftInboxTrap: form.elements.ftInboxTrap.value,
                source: 'WordPress light booking form shortcode',
                pageUrl: window.location.href,
                devicePlatform: /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent) ? 'Mobile / Tablet' : 'Desktop',
                privacyConsent: form.elements.privacyConsent.checked,
                smsConsent: form.elements.smsConsent.checked,
                emailConsent: form.elements.emailConsent.checked
            };
            Object.keys(attr).forEach(function (k) { payload[k] = attr[k]; });

            state.pending = true;
            submit.disabled = true;
            submitText.textContent = 'Sending...';
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
                var done = root.querySelector('.ft-bfl__done');
                done.querySelector('h3').textContent = "You're booked in, " + payload.fullName.split(/\s+/)[0] + '.';
                done.hidden = false;

                // Same conversion event as the booking form, so ad and CRM tracking keep counting
                if (window.XDTrack && window.XDTrack.lead) {
                    window.XDTrack.lead({
                        formId: 'booking_form',
                        formName: 'Booking Form Shortcode',
                        flooringType: state.floor,
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
                submitText.textContent = 'Book Your Appointment';
            } finally {
                state.pending = false;
            }
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('floors_booking_form_light', 'ft_next_booking_form_light_shortcode');
