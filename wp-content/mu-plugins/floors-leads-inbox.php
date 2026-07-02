<?php
/**
 * Plugin Name: Floors Today Inbox
 * Description: Homepage lead inbox and REST submissions for Floors Today.
 * Version: 1.0.0
 * Author: Faris
 */

if (!defined('ABSPATH')) {
    exit;
}

const FT_INBOX_POST_TYPE = 'ft_inbox_lead';
const FT_INBOX_REST_NAMESPACE = 'floors-today/v1';
const FT_INBOX_REST_ROUTE = '/inbox-leads';
const FT_INBOX_SETTINGS_OPTION = 'ft_inbox_settings';

function ft_inbox_allowed_statuses() {
    return [
        'new' => 'New',
        'contacted' => 'Contacted',
        'estimate_booked' => 'Estimate Booked',
        'closed' => 'Closed',
    ];
}

function ft_inbox_capability() {
    return current_user_can('manage_ft_inbox');
}

function ft_inbox_field_labels() {
    return [
        'full_name' => 'Full Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'unit' => 'Unit / Apt',
        'street' => 'Street Address',
        'city' => 'City',
        'province' => 'Province',
        'postal_code' => 'Postal Code',
        'message' => 'Message',
        'flooring_type' => 'Flooring Type',
        'property_type' => 'Property Type',
        'start_time' => 'Start Time',
        'source' => 'Source',
        'traffic_source' => 'Traffic Source',
        'utm_source' => 'UTM Source',
        'utm_medium' => 'UTM Medium',
        'utm_campaign' => 'UTM Campaign',
        'utm_term' => 'UTM Term',
        'referrer_url' => 'Previous Website',
        'utm_content' => 'UTM Content',
        'device_platform' => 'Device Platform',
        'page_url' => 'Page URL',
        'privacy_consent' => 'Privacy / Terms Acceptance',
        'sms_consent' => 'SMS Consent',
        'email_consent' => 'Email Consent',
        'user_agent' => 'User Agent',
        'ip_address' => 'IP Address',
    ];
}

function ft_inbox_default_email_template() {
    return "<p><strong>New homepage lead received.</strong></p>\n"
        . "<table style=\"border-collapse:collapse;width:100%;\">\n"
        . "<tr><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\"><strong>Name</strong></td><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\">{full_name}</td></tr>\n"
        . "<tr><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\"><strong>Phone</strong></td><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\">{phone}</td></tr>\n"
        . "<tr><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\"><strong>Email</strong></td><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\">{email}</td></tr>\n"
        . "<tr><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\"><strong>Address</strong></td><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\">{address}, {city}, {province} {postal_code}</td></tr>\n"
        . "<tr><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\"><strong>Flooring</strong></td><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\">{flooring_type}</td></tr>\n"
        . "<tr><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\"><strong>Property</strong></td><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\">{property_type}</td></tr>\n"
        . "<tr><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\"><strong>Start Time</strong></td><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\">{start_time}</td></tr>\n"
        . "<tr><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\"><strong>Source</strong></td><td style=\"padding:6px 12px;border-bottom:1px solid #eee;\">{source}</td></tr>\n"
        . "<tr><td style=\"padding:6px 12px;\"><strong>Consents</strong></td><td style=\"padding:6px 12px;\">Privacy &amp; Terms: {privacy_consent} &nbsp;&bull;&nbsp; SMS: {sms_consent} &nbsp;&bull;&nbsp; Email: {email_consent}</td></tr>\n"
        . "</table>\n"
        . "<p style=\"margin-top:20px;\"><a href=\"{lead_url}\" style=\"background:#235bb8;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;display:inline-block;\">Open Lead in Inbox &rarr;</a></p>";
}

function ft_inbox_default_client_email_template() {
    return "<h2>Thank you, {full_name}</h2>\n"
        . "<p>We received your free in-home estimate request.</p>\n"
        . "<p>A Floors Today specialist will contact you shortly using the phone number or email you provided.</p>\n"
        . "<p><strong>Flooring:</strong> {flooring_type}<br>\n"
        . "<strong>Property:</strong> {property_type}<br>\n"
        . "<strong>Preferred timing:</strong> {start_time}</p>\n"
        . "<p>Floors Today</p>";
}

function ft_inbox_default_newsletter_template() {
    return "<h2>Welcome, {full_name}! Your $300 Store Credit is confirmed.</h2>\n"
        . "<p>Thank you for subscribing to the Floors Today newsletter.</p>\n"
        . "<p>You're now on the list to receive exclusive flooring offers, project tips, and your <strong>$300 store credit</strong> details.</p>\n"
        . "<p>Our team will be in touch soon with everything you need to claim your credit.</p>\n"
        . "<p>In the meantime, feel free to explore our showroom or browse our collections online.</p>\n"
        . "<p>Talk soon,<br>The Floors Today Team</p>";
}

function ft_inbox_default_settings() {
    return [
        'notifications_enabled'        => '1',
        'notification_recipients'      => get_option('admin_email'),
        'notification_subject'         => 'New Floors Today Lead: {full_name}',
        'notification_template'        => ft_inbox_default_email_template(),
        'from_name'                    => get_bloginfo('name'),
        'from_email'                   => 'info@floorstoday.ca',
        'reply_to_customer'            => '1',
        'client_notifications_enabled' => '1',
        'client_subject'               => 'We received your Floors Today estimate request',
        'client_template'              => ft_inbox_default_client_email_template(),
        'client_from_name'             => get_bloginfo('name'),
        'client_from_email'            => 'info@floorstoday.ca',
        'email_logo_url'               => '',
        'email_header_color'           => '#235bb8',
        'email_footer_text'            => get_bloginfo('name') . ' &mdash; All rights reserved.',
        'newsletter_enabled'           => '1',
        'newsletter_subject'           => 'Welcome! Your $300 Store Credit is waiting, {full_name}',
        'newsletter_template'          => ft_inbox_default_newsletter_template(),
        'newsletter_from_name'         => get_bloginfo('name'),
        'newsletter_from_email'        => 'info@floorstoday.ca',
    ];
}

function ft_inbox_settings() {
    $saved    = is_array(get_option(FT_INBOX_SETTINGS_OPTION, [])) ? get_option(FT_INBOX_SETTINGS_OPTION, []) : [];
    $defaults = ft_inbox_default_settings();
    // Empty strings in saved settings must not override defaults for branding/template fields.
    foreach (['email_footer_text', 'email_header_color', 'notification_subject', 'client_subject'] as $key) {
        if (isset($saved[$key]) && $saved[$key] === '') {
            unset($saved[$key]);
        }
    }
    return array_replace($defaults, $saved);
}

function ft_inbox_template_variables() {
    return [
        'lead_id',
        'lead_url',
        'full_name',
        'email',
        'phone',
        'address',
        'postal_code',
        'flooring_type',
        'property_type',
        'start_time',
        'source',
        'page_url',
        'privacy_consent',
        'sms_consent',
        'email_consent',
        'date',
        'status',
    ];
}

function ft_inbox_render_template($template, $lead) {
    $replacements = [];

    foreach (ft_inbox_template_variables() as $variable) {
        $replacements['{' . $variable . '}'] = isset($lead[$variable]) ? (string) $lead[$variable] : '';
    }

    return strtr($template, $replacements);
}

function ft_inbox_normalize_consent($value) {
    if (is_bool($value)) {
        return $value ? 'Yes' : 'No';
    }

    $value = strtolower(trim((string) $value));
    return in_array($value, ['1', 'true', 'yes', 'on'], true) ? 'Yes' : 'No';
}

function ft_inbox_consent_references() {
    return [
        'privacy_consent' => [
            'label' => 'Privacy / Terms Acceptance',
            'text' => 'I agree to receive promotional emails from Floors Today and have read the Privacy Policy and Terms & Conditions.',
        ],
        'sms_consent' => [
            'label' => 'SMS Consent',
            'text' => 'I agree to receive SMS marketing and informational messages from Floors Today at the contact information provided above. Message frequency may vary. Message & data rates may apply. Reply STOP to unsubscribe or HELP for assistance.',
        ],
        'email_consent' => [
            'label' => 'Email Consent',
            'text' => 'I agree to receive email marketing communications from Floors Today at the email address provided above. I understand iTech CRM may respond to any messages or emails I send.',
        ],
    ];
}

function ft_inbox_sanitize_lead_payload($request) {
    $params = $request instanceof WP_REST_Request ? $request->get_json_params() : (array) $request;

    if (!is_array($params)) {
        $params = [];
    }

    return [
        'full_name' => sanitize_text_field($params['fullName'] ?? $params['full_name'] ?? ''),
        'email' => sanitize_email($params['email'] ?? ''),
        'phone' => sanitize_text_field($params['phone'] ?? ''),
        'address' => sanitize_text_field($params['address'] ?? ''),
        'street' => sanitize_text_field($params['street'] ?? ''),
        'unit' => sanitize_text_field($params['unit'] ?? ''),
        'city' => sanitize_text_field($params['city'] ?? ''),
        'province' => sanitize_text_field($params['province'] ?? ''),
        'postal_code' => sanitize_text_field($params['postalCode'] ?? $params['postal_code'] ?? ''),
        'message' => sanitize_textarea_field($params['message'] ?? ''),
        'flooring_type' => sanitize_text_field($params['flooringType'] ?? $params['flooring_type'] ?? ''),
        'property_type' => sanitize_text_field($params['propertyType'] ?? $params['property_type'] ?? ''),
        'start_time' => sanitize_text_field($params['startTime'] ?? $params['start_time'] ?? ''),
        'source' => sanitize_text_field($params['source'] ?? 'Homepage estimate form'),
        'traffic_source' => sanitize_text_field($params['trafficSource'] ?? $params['traffic_source'] ?? 'Direct'),
        'utm_source' => sanitize_text_field($params['utmSource'] ?? $params['utm_source'] ?? ''),
        'utm_medium' => sanitize_text_field($params['utmMedium'] ?? $params['utm_medium'] ?? ''),
        'utm_campaign' => sanitize_text_field($params['utmCampaign'] ?? $params['utm_campaign'] ?? ''),
        'utm_term' => sanitize_text_field($params['utmTerm'] ?? $params['utm_term'] ?? ''),
        'referrer_url' => esc_url_raw($params['referrerUrl'] ?? $params['referrer_url'] ?? ''),
        'utm_content' => sanitize_text_field($params['utmContent'] ?? $params['utm_content'] ?? ''),
        'device_platform' => sanitize_text_field($params['devicePlatform'] ?? $params['device_platform'] ?? ''),
        'page_url' => esc_url_raw($params['pageUrl'] ?? $params['page_url'] ?? ''),
        'privacy_consent' => ft_inbox_normalize_consent($params['privacyConsent'] ?? $params['privacy_consent'] ?? $params['marketingConsent'] ?? ''),
        'sms_consent' => ft_inbox_normalize_consent($params['smsConsent'] ?? $params['sms_consent'] ?? ''),
        'email_consent' => ft_inbox_normalize_consent($params['emailConsent'] ?? $params['email_consent'] ?? $params['marketingConsent'] ?? ''),
        'honeypot' => sanitize_text_field($params['ftInboxTrap'] ?? ''),
    ];
}

function ft_inbox_unread_count() {
    $query = new WP_Query([
        'post_type' => FT_INBOX_POST_TYPE,
        'post_status' => 'private',
        'fields' => 'ids',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => '_ft_inbox_unread',
                'value' => '1',
            ],
        ],
    ]);

    return (int) $query->found_posts;
}

add_action('init', function () {
    register_post_type(FT_INBOX_POST_TYPE, [
        'labels' => [
            'name' => 'Inbox Leads',
            'singular_name' => 'Inbox Lead',
        ],
        'public' => false,
        'show_ui' => false,
        'show_in_menu' => false,
        'supports' => ['title'],
        'capability_type' => 'post',
    ]);

    foreach (['administrator', 'shop_manager', 'sales_admin'] as $role_name) {
        $role = get_role($role_name);

        if ($role && !$role->has_cap('manage_ft_inbox')) {
            $role->add_cap('manage_ft_inbox');
        }
    }
});

add_action('admin_menu', function () {
    add_menu_page(
        'Inbox',
        'Inbox',
        'manage_ft_inbox',
        'ft-inbox',
        'ft_inbox_render_admin_page',
        'dashicons-email-alt2',
        57
    );

    add_options_page(
        'Form Settings',
        'Form Settings',
        'manage_options',
        'ft-form-settings',
        'ft_inbox_render_settings_page'
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, ['toplevel_page_ft-inbox', 'settings_page_ft-form-settings'], true)) {
        return;
    }

    if ($hook === 'settings_page_ft-form-settings') {
        wp_enqueue_media();
    }

    $base = plugin_dir_url(__FILE__);
    $dir = __DIR__;

    wp_enqueue_style('ft-inbox', $base . 'inbox.css', [], filemtime($dir . '/inbox.css'));

    if (current_user_can('manage_options')) {
        wp_enqueue_style('ft-inbox-admin', $base . 'inbox-admin.css', ['ft-inbox'], filemtime($dir . '/inbox-admin.css'));
    } else {
        wp_enqueue_style('ft-inbox-sales', $base . 'inbox-sales.css', ['ft-inbox'], filemtime($dir . '/inbox-sales.css'));
    }
});

add_action('admin_footer', function () {
    if (!ft_inbox_capability()) {
        return;
    }

    $count = ft_inbox_unread_count();
    ?>
    <script>
      (function () {
        function syncInboxCount() {
          var inboxLinks = document.querySelectorAll('#adminmenu a[href*="page=ft-inbox"]');

          document.querySelectorAll('#adminmenu .ft-inbox-menu-count').forEach(function (badge) {
            badge.remove();
          });

          inboxLinks.forEach(function (link) {
            var menuItem = link.closest('li');
            var scope = menuItem || link;

            scope.querySelectorAll('.awaiting-mod, .update-plugins').forEach(function (badge) {
              badge.remove();
            });
          });

          if (<?php echo (int) $count; ?> < 1 || !inboxLinks.length) {
            return;
          }

          var inboxLink = inboxLinks[0];
          var menuLabel = inboxLink.querySelector('.wp-menu-name') || inboxLink;
          var badge = document.createElement('span');
          badge.className = 'ft-inbox-menu-count';
          badge.textContent = <?php echo wp_json_encode((string) $count); ?>;
          badge.setAttribute('aria-label', <?php echo wp_json_encode($count . ' unread leads'); ?>);
          menuLabel.appendChild(badge);
        }

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', syncInboxCount);
        } else {
          syncInboxCount();
        }
      }());
    </script>
    <?php
});

add_action('rest_api_init', function () {
    register_rest_route(FT_INBOX_REST_NAMESPACE, FT_INBOX_REST_ROUTE, [
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => 'ft_inbox_handle_rest_submission',
    ]);
});

function ft_inbox_handle_rest_submission(WP_REST_Request $request) {
    $data = ft_inbox_sanitize_lead_payload($request);

    if ($data['honeypot'] !== '') {
        return new WP_Error('ft_inbox_spam', 'Unable to send this request.', ['status' => 400]);
    }

    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    $rate_key = 'ft_inbox_rate_' . md5($ip);

    if (get_transient($rate_key)) {
        return new WP_Error('ft_inbox_rate_limited', 'Please wait a moment before sending another request.', ['status' => 429]);
    }

    $name_parts = preg_split('/\s+/', trim($data['full_name']), -1, PREG_SPLIT_NO_EMPTY);

    if (count($name_parts) < 2) {
        return new WP_Error('ft_inbox_invalid_name', 'Please enter your first and last name.', ['status' => 400]);
    }

    if ($data['phone'] === '' || $data['phone'] === '+1' || $data['email'] === '' || !is_email($data['email'])) {
        return new WP_Error('ft_inbox_invalid', 'Please enter your name, phone, and a valid email.', ['status' => 400]);
    }

    $title = sprintf(
        '%s - %s',
        $data['full_name'],
        current_time('M j, Y g:ia')
    );

    $lead_id = wp_insert_post([
        'post_type' => FT_INBOX_POST_TYPE,
        'post_status' => 'private',
        'post_title' => $title,
    ], true);

    if (is_wp_error($lead_id)) {
        return new WP_Error('ft_inbox_save_failed', 'Unable to save this request.', ['status' => 500]);
    }

    $data['ip_address'] = $ip;
    $data['user_agent'] = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');

    foreach ($data as $key => $value) {
        if ($key !== 'honeypot') {
            update_post_meta($lead_id, '_ft_inbox_' . $key, $value);
        }
    }

    update_post_meta($lead_id, '_ft_inbox_status', 'new');
    update_post_meta($lead_id, '_ft_inbox_notes', '');
    update_post_meta($lead_id, '_ft_inbox_unread', '1');
    set_transient($rate_key, 1, MINUTE_IN_SECONDS);

    do_action('ft_inbox_lead_created', $lead_id, $data);

    ft_inbox_send_notification($lead_id);

    return rest_ensure_response([
        'ok' => true,
        'leadId' => $lead_id,
        'message' => 'Your request was received.',
    ]);
}

function ft_inbox_build_email_html(string $content, array $settings): string {
    $logo_url     = esc_url($settings['email_logo_url']     ?? '');
    $header_color = esc_attr($settings['email_header_color'] ?? '#235bb8');
    $footer_text  = wp_kses_post($settings['email_footer_text'] ?: get_bloginfo('name'));

    $logo_block = $logo_url
        ? '<img src="' . $logo_url . '" alt="' . esc_attr(get_bloginfo('name')) . '" style="max-height:60px;max-width:220px;display:block;margin:0 auto;">'
        : '<span style="color:#fff;font-size:20px;font-weight:700;">' . esc_html(get_bloginfo('name')) . '</span>';

    return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;">
<tr><td align="center" style="padding:24px 16px;">
  <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">
    <tr><td style="background:' . $header_color . ';padding:24px 32px;text-align:center;">' . $logo_block . '</td></tr>
    <tr><td style="padding:32px;color:#222;font-size:15px;line-height:1.6;">' . $content . '</td></tr>
    <tr><td style="background:#f4f4f4;padding:16px 32px;text-align:center;font-size:12px;color:#888;border-top:1px solid #e8e8e8;">' . $footer_text . '</td></tr>
  </table>
</td></tr>
</table>
</body></html>';
}

function ft_inbox_send_notification($lead_id) {
    $settings = ft_inbox_settings();
    $lead = ft_inbox_get_lead($lead_id);

    if (!$lead) {
        return;
    }

    if ($settings['notifications_enabled'] === '1') {
        $recipients = preg_split('/[,;\r\n]+/', $settings['notification_recipients']);
        $recipients = array_values(array_filter(array_map('sanitize_email', $recipients), 'is_email'));

        if ($recipients) {
            $headers = ['Content-Type: text/html; charset=UTF-8'];

            if ($settings['from_name'] !== '' && is_email($settings['from_email'])) {
                $headers[] = 'From: ' . sanitize_text_field($settings['from_name']) . ' <' . sanitize_email($settings['from_email']) . '>';
            }

            if ($settings['reply_to_customer'] === '1' && is_email($lead['email'])) {
                $headers[] = 'Reply-To: ' . sanitize_text_field($lead['full_name']) . ' <' . $lead['email'] . '>';
            }

            $admin_body = ft_inbox_build_email_html(
                wpautop(ft_inbox_render_template($settings['notification_template'], $lead)),
                $settings
            );
            ft_inbox_send_mail(
                $lead_id,
                'admin',
                $recipients,
                ft_inbox_render_template($settings['notification_subject'], $lead),
                $admin_body,
                $headers
            );
        }
    }

    $is_newsletter = strtolower(trim($lead['source'] ?? '')) === 'newsletter cta';

    if ($is_newsletter && $settings['newsletter_enabled'] === '1' && is_email($lead['email'])) {
        $nl_headers = ['Content-Type: text/html; charset=UTF-8'];
        $nl_from_name  = $settings['newsletter_from_name'] ?: $settings['client_from_name'];
        $nl_from_email = $settings['newsletter_from_email'] ?: $settings['client_from_email'];
        if ($nl_from_name !== '' && is_email($nl_from_email)) {
            $nl_headers[] = 'From: ' . sanitize_text_field($nl_from_name) . ' <' . sanitize_email($nl_from_email) . '>';
        }
        $nl_body = ft_inbox_build_email_html(
            wpautop(ft_inbox_render_template($settings['newsletter_template'], $lead)),
            $settings
        );
        ft_inbox_send_mail(
            $lead_id,
            'client',
            $lead['email'],
            ft_inbox_render_template($settings['newsletter_subject'], $lead),
            $nl_body,
            $nl_headers
        );
    } elseif (!$is_newsletter && $settings['client_notifications_enabled'] === '1' && is_email($lead['email'])) {
        $client_headers = ['Content-Type: text/html; charset=UTF-8'];

        if ($settings['client_from_name'] !== '' && is_email($settings['client_from_email'])) {
            $client_headers[] = 'From: ' . sanitize_text_field($settings['client_from_name']) . ' <' . sanitize_email($settings['client_from_email']) . '>';
        }

        $client_body = ft_inbox_build_email_html(
            wpautop(ft_inbox_render_template($settings['client_template'], $lead)),
            $settings
        );
        ft_inbox_send_mail(
            $lead_id,
            'client',
            $lead['email'],
            ft_inbox_render_template($settings['client_subject'], $lead),
            $client_body,
            $client_headers
        );
    }
}

function ft_inbox_send_mail($lead_id, $type, $to, $subject, $message, $headers) {
    $error_message = '';
    $capture_error = function ($error) use (&$error_message) {
        if (is_wp_error($error)) {
            $error_message = $error->get_error_message();
        }
    };

    add_action('wp_mail_failed', $capture_error);
    $accepted = wp_mail($to, $subject, $message, $headers);
    remove_action('wp_mail_failed', $capture_error);

    update_post_meta($lead_id, '_ft_inbox_' . $type . '_email_status', $accepted ? 'accepted' : 'failed');
    update_post_meta($lead_id, '_ft_inbox_' . $type . '_email_error', $accepted ? '' : $error_message);
    update_post_meta($lead_id, '_ft_inbox_' . $type . '_email_checked_at', current_time('mysql'));

    return $accepted;
}

function ft_inbox_get_lead($lead_id) {
    $post = get_post($lead_id);

    if (!$post || $post->post_type !== FT_INBOX_POST_TYPE) {
        return null;
    }

    $lead = [
        'id' => $post->ID,
        'lead_id' => $post->ID,
        'lead_url' => admin_url('admin.php?page=ft-inbox&lead=' . (int) $post->ID),
        'title' => $post->post_title,
        'date' => get_the_date('M j, Y g:ia', $post),
        'status' => get_post_meta($post->ID, '_ft_inbox_status', true) ?: 'new',
        'unread' => get_post_meta($post->ID, '_ft_inbox_unread', true) === '1',
        'notes' => get_post_meta($post->ID, '_ft_inbox_notes', true),
    ];

    foreach (array_keys(ft_inbox_field_labels()) as $key) {
        $lead[$key] = get_post_meta($post->ID, '_ft_inbox_' . $key, true);
    }

    return $lead;
}

function ft_inbox_handle_settings_save() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (($_POST['ft_inbox_action'] ?? '') !== 'save_settings') {
        return;
    }

    check_admin_referer('ft_inbox_save_settings', 'ft_inbox_settings_nonce');

    $defaults = ft_inbox_default_settings();
    $subject = sanitize_text_field(wp_unslash($_POST['notification_subject'] ?? ''));
    $template = wp_kses_post(wp_unslash($_POST['notification_template'] ?? ''));
    $client_subject = sanitize_text_field(wp_unslash($_POST['client_subject'] ?? ''));
    $client_template = wp_kses_post(wp_unslash($_POST['client_template'] ?? ''));
    $footer_text = wp_kses_post(wp_unslash($_POST['email_footer_text'] ?? ''));
    $newsletter_subject = sanitize_text_field(wp_unslash($_POST['newsletter_subject'] ?? ''));
    $newsletter_template = wp_kses_post(wp_unslash($_POST['newsletter_template'] ?? ''));

    update_option(FT_INBOX_SETTINGS_OPTION, [
        'notifications_enabled'        => isset($_POST['notifications_enabled']) ? '1' : '0',
        'notification_recipients'      => sanitize_textarea_field(wp_unslash($_POST['notification_recipients'] ?? '')),
        'notification_subject'         => $subject !== '' ? $subject : $defaults['notification_subject'],
        'notification_template'        => $template !== '' ? $template : $defaults['notification_template'],
        'from_name'                    => sanitize_text_field(wp_unslash($_POST['from_name'] ?? '')),
        'from_email'                   => sanitize_email(wp_unslash($_POST['from_email'] ?? $defaults['from_email'])),
        'reply_to_customer'            => isset($_POST['reply_to_customer']) ? '1' : '0',
        'client_notifications_enabled' => isset($_POST['client_notifications_enabled']) ? '1' : '0',
        'client_subject'               => $client_subject !== '' ? $client_subject : $defaults['client_subject'],
        'client_template'              => $client_template !== '' ? $client_template : $defaults['client_template'],
        'client_from_name'             => sanitize_text_field(wp_unslash($_POST['client_from_name'] ?? '')),
        'client_from_email'            => sanitize_email(wp_unslash($_POST['client_from_email'] ?? $defaults['client_from_email'])),
        'email_logo_url'               => esc_url_raw(wp_unslash($_POST['email_logo_url'] ?? '')),
        'email_header_color'           => sanitize_hex_color(wp_unslash($_POST['email_header_color'] ?? '#235bb8')) ?: '#235bb8',
        'email_footer_text'            => $footer_text !== '' ? $footer_text : $defaults['email_footer_text'],
        'newsletter_enabled'           => isset($_POST['newsletter_enabled']) ? '1' : '0',
        'newsletter_subject'           => $newsletter_subject !== '' ? $newsletter_subject : $defaults['newsletter_subject'],
        'newsletter_template'          => $newsletter_template !== '' ? $newsletter_template : $defaults['newsletter_template'],
        'newsletter_from_name'         => sanitize_text_field(wp_unslash($_POST['newsletter_from_name'] ?? '')),
        'newsletter_from_email'        => sanitize_email(wp_unslash($_POST['newsletter_from_email'] ?? $defaults['newsletter_from_email'])),
    ]);

    wp_safe_redirect(admin_url('options-general.php?page=ft-form-settings&updated=1'));
    exit;
}

add_action('admin_init', 'ft_inbox_handle_settings_save');

function ft_inbox_handle_admin_actions() {
    if (!ft_inbox_capability()) {
        return;
    }

    if (!isset($_POST['ft_inbox_action'], $_POST['ft_inbox_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ft_inbox_nonce'])), 'ft_inbox_action')) {
        return;
    }

    $lead_id = absint($_POST['lead_id'] ?? 0);
    $lead = ft_inbox_get_lead($lead_id);

    if (!$lead) {
        return;
    }

    $action = sanitize_text_field(wp_unslash($_POST['ft_inbox_action']));

    if ($action === 'update_lead') {
        $status = sanitize_text_field(wp_unslash($_POST['status'] ?? 'new'));
        $statuses = ft_inbox_allowed_statuses();

        if (!isset($statuses[$status])) {
            $status = 'new';
        }

        update_post_meta($lead_id, '_ft_inbox_status', $status);

        $new_note_text = sanitize_textarea_field(wp_unslash($_POST['new_note'] ?? ''));
        if ($new_note_text !== '') {
            $raw = get_post_meta($lead_id, '_ft_inbox_notes', true) ?: '[]';
            $notes = json_decode($raw, true);
            if (!is_array($notes)) {
                $notes = $raw !== '' && $raw !== '[]' ? [['text' => $raw, 'date' => 'Legacy', 'ts' => 0]] : [];
            }
            $notes[] = ['text' => $new_note_text, 'date' => current_time('M j, Y g:ia'), 'ts' => time()];
            update_post_meta($lead_id, '_ft_inbox_notes', wp_json_encode($notes));
        }

        wp_safe_redirect(admin_url('admin.php?page=ft-inbox&lead=' . $lead_id . '&updated=1'));
        exit;
    }
}

add_action('admin_init', 'ft_inbox_handle_admin_actions');

function ft_inbox_render_admin_page() {
    if (!ft_inbox_capability()) {
        wp_die(esc_html__('You do not have permission to view this page.', 'floors-today'));
    }

    $lead_id = absint($_GET['lead'] ?? 0);

    echo '<div class="wrap ft-inbox-wrap">';

    if ($lead_id) {
        ft_inbox_render_detail($lead_id);
    } else {
        ft_inbox_render_list();
    }

    echo '</div>';
}

function ft_inbox_render_list() {
    $status      = sanitize_text_field(wp_unslash($_GET['status']      ?? ''));
    $search      = sanitize_text_field(wp_unslash($_GET['s']           ?? ''));
    $form_source = sanitize_text_field(wp_unslash($_GET['form_source'] ?? ''));
    $statuses    = ft_inbox_allowed_statuses();

    $args = [
        'post_type'      => FT_INBOX_POST_TYPE,
        'post_status'    => 'private',
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    if ($search !== '') {
        $args['s'] = $search;
    }

    $meta_clauses = [];
    if (isset($statuses[$status])) {
        $meta_clauses[] = ['key' => '_ft_inbox_status', 'value' => $status];
    }
    if ($form_source !== '') {
        $meta_clauses[] = ['key' => '_ft_inbox_source', 'value' => $form_source];
    }
    if ($meta_clauses) {
        $args['meta_query'] = array_merge(['relation' => 'AND'], $meta_clauses);
    }

    // Distinct source values for the filter dropdown
    global $wpdb;
    $sources = $wpdb->get_col(
        "SELECT DISTINCT meta_value FROM {$wpdb->postmeta}
         WHERE meta_key = '_ft_inbox_source' AND meta_value != ''
         ORDER BY meta_value ASC"
    );

    $query = new WP_Query($args);

    echo '<div class="ft-inbox-hero">';
    echo '<div><span class="ft-inbox-eyebrow">Sales workspace</span><h1>Inbox</h1><p>Review estimate requests and keep every follow-up moving.</p></div>';
    echo '<div class="ft-inbox-hero__count"><strong>' . esc_html((string) ft_inbox_unread_count()) . '</strong><span>Unread leads</span></div>';
    echo '</div>';

    echo '<nav class="ft-inbox-status-nav" aria-label="Lead status filters">';
    echo '<a class="' . ($status === '' ? 'is-active' : '') . '" href="' . esc_url(admin_url('admin.php?page=ft-inbox')) . '">All</a>';
    foreach ($statuses as $key => $label) {
        $status_url = add_query_arg(['page' => 'ft-inbox', 'status' => $key], admin_url('admin.php'));
        echo '<a class="' . ($status === $key ? 'is-active' : '') . '" href="' . esc_url($status_url) . '">' . esc_html($label) . '</a>';
    }
    echo '</nav>';

    echo '<form class="ft-inbox-filters" method="get">';
    echo '<input type="hidden" name="page" value="ft-inbox">';
    echo '<div class="ft-inbox-search"><span class="dashicons dashicons-search" aria-hidden="true"></span><input type="search" name="s" value="' . esc_attr($search) . '" placeholder="Search name, phone, or email"></div>';
    echo '<select name="status"><option value="">All statuses</option>';
    foreach ($statuses as $key => $label) {
        echo '<option value="' . esc_attr($key) . '"' . selected($status, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    if (!empty($sources)) {
        echo '<select name="form_source"><option value="">All forms</option>';
        foreach ($sources as $src) {
            echo '<option value="' . esc_attr($src) . '"' . selected($form_source, $src, false) . '>' . esc_html($src) . '</option>';
        }
        echo '</select>';
    }
    echo '<button class="button button-primary">Filter</button>';
    echo '</form>';

    echo '<div class="ft-inbox-list">';

    if (!$query->have_posts()) {
        echo '<div class="ft-inbox-empty">No leads found.</div>';
    }

    while ($query->have_posts()) {
        $query->the_post();
        $lead = ft_inbox_get_lead(get_the_ID());
        $detail_url = admin_url('admin.php?page=ft-inbox&lead=' . (int) $lead['id']);

        $read_class = $lead['unread'] ? ' is-unread' : ' is-read';
        echo '<a class="ft-inbox-card ft-status-' . esc_attr($lead['status']) . esc_attr($read_class) . '" href="' . esc_url($detail_url) . '">';
        echo '<span class="ft-inbox-avatar">' . esc_html(strtoupper(substr($lead['full_name'] ?: '?', 0, 1))) . '</span>';
        echo '<div class="ft-inbox-card__main">';
        echo '<strong>' . esc_html($lead['full_name'] ?: 'Unknown lead') . '</strong>';
        echo '<span>' . esc_html($lead['flooring_type'] ?: 'Flooring not selected') . ' · ' . esc_html($lead['property_type'] ?: 'Property not selected') . '</span>';
        echo '</div>';
        echo '<div class="ft-inbox-card__meta">';
        $card_location = ($lead['city'] ?? '') ?: ($lead['postal_code'] ?? '') ?: '';
        echo '<span>' . esc_html($lead['phone']) . ($card_location ? ' · ' . esc_html($card_location) : '') . '</span>';
        echo '<span>' . esc_html($lead['date']) . '</span>';
        echo '</div>';
        echo '<div class="ft-inbox-card__status">';
        echo '<em>' . esc_html(ft_inbox_allowed_statuses()[$lead['status']] ?? 'New') . '</em>';
        echo '<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>';
        echo '</div>';
        echo '</a>';
    }

    wp_reset_postdata();
    echo '</div>';
}

function ft_inbox_render_detail($lead_id) {
    $lead = ft_inbox_get_lead($lead_id);
    $statuses = ft_inbox_allowed_statuses();

    if (!$lead) {
        echo '<h1>Lead not found</h1>';
        return;
    }

    if ($lead['unread']) {
        update_post_meta($lead_id, '_ft_inbox_unread', '0');
        $lead['unread'] = false;
    }

    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ft-inbox')) . '">&larr; Back to Inbox</a></p>';
    echo '<div class="ft-inbox-detail">';
    echo '<section class="ft-inbox-panel ft-inbox-panel--primary">';
    echo '<div class="ft-inbox-detail__head">';
    echo '<div class="ft-inbox-detail__identity"><span class="ft-inbox-avatar ft-inbox-avatar--large">' . esc_html(strtoupper(substr($lead['full_name'] ?: '?', 0, 1))) . '</span><div><span class="ft-inbox-eyebrow">Estimate request</span><h1>' . esc_html($lead['full_name'] ?: 'Inbox Lead') . '</h1><p>Received ' . esc_html($lead['date']) . '</p></div></div>';
    echo '<span class="ft-inbox-pill ft-status-' . esc_attr($lead['status']) . '">' . esc_html($statuses[$lead['status']] ?? 'New') . '</span>';
    echo '</div>';

    echo '<div class="ft-inbox-quick-actions">';
    if ($lead['phone']) {
        echo '<a class="button button-primary" href="tel:' . esc_attr(preg_replace('/[^\d+]/', '', $lead['phone'])) . '"><span class="dashicons dashicons-phone"></span> Call</a>';
    }
    if ($lead['email']) {
        echo '<a class="button" href="mailto:' . esc_attr($lead['email']) . '"><span class="dashicons dashicons-email-alt"></span> Email</a>';
    }
    echo '</div>';

    $tracking_fields = [
        'traffic_source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'device_platform',
        'referrer_url',
        'page_url',
    ];
    $consent_fields = [
        'privacy_consent',
        'sms_consent',
        'email_consent',
    ];

    echo '<div class="ft-inbox-data-grid">';
    foreach (ft_inbox_field_labels() as $key => $label) {
        if (in_array($key, array_merge(['user_agent', 'ip_address'], $tracking_fields, $consent_fields), true)) {
            continue;
        }

        if ($key === 'flooring_type') {
            echo '<h3 class="ft-inbox-data-grid__heading ft-inbox-data-grid__heading--sm">Project Details</h3>';
        }

        if ($key === 'message' && ($lead[$key] ?? '') === '') {
            continue;
        }

        if ($key === 'full_name') {
            $name_parts = explode(' ', trim($lead['full_name'] ?? ''), 2);
            echo '<div data-key="first_name"><span>First Name</span><strong>' . esc_html($name_parts[0] ?: '-') . '</strong></div>';
            echo '<div data-key="last_name"><span>Last Name</span><strong>' . esc_html($name_parts[1] ?? '-') . '</strong></div>';
            continue;
        }

        echo '<div data-key="' . esc_attr($key) . '"><span>' . esc_html($label) . '</span><strong>' . esc_html($lead[$key] ?: '-') . '</strong></div>';
    }

    echo '<h2 class="ft-inbox-data-grid__heading">Tracking Information</h2>';
    foreach ($tracking_fields as $key) {
        $label = ft_inbox_field_labels()[$key] ?? $key;
        echo '<div data-key="' . esc_attr($key) . '"><span>' . esc_html($label) . '</span><strong>' . esc_html($lead[$key] ?: '-') . '</strong></div>';
    }
    echo '</div>';

    echo '<section class="ft-inbox-consent-reference" aria-labelledby="ft-inbox-consent-heading">';
    echo '<h2 id="ft-inbox-consent-heading">Consent / Acceptance References</h2>';
    foreach (ft_inbox_consent_references() as $key => $consent) {
        $value = $lead[$key] ?: 'Not recorded';
        $is_yes = strtolower((string) $value) === 'yes';
        echo '<div class="ft-inbox-consent-reference__item">';
        echo '<div class="ft-inbox-consent-reference__head"><span>' . esc_html($consent['label']) . '</span><strong class="' . esc_attr($is_yes ? 'is-accepted' : 'is-missing') . '">' . esc_html($value) . '</strong></div>';
        echo '<p>' . esc_html($consent['text']) . '</p>';
        echo '</div>';
    }
    echo '</section>';
    echo '</section>';

    echo '<aside class="ft-inbox-panel">';
    echo '<form method="post">';
    wp_nonce_field('ft_inbox_action', 'ft_inbox_nonce');
    echo '<input type="hidden" name="ft_inbox_action" value="update_lead">';
    echo '<input type="hidden" name="lead_id" value="' . esc_attr((string) $lead['id']) . '">';
    echo '<label>Status<select name="status">';
    foreach ($statuses as $key => $label) {
        echo '<option value="' . esc_attr($key) . '"' . selected($lead['status'], $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></label>';
    $raw_notes = get_post_meta($lead['id'], '_ft_inbox_notes', true) ?: '[]';
    $notes_arr = json_decode($raw_notes, true);
    if (!is_array($notes_arr)) {
        $notes_arr = ($raw_notes !== '' && $raw_notes !== '[]') ? [['text' => $raw_notes, 'date' => 'Legacy', 'ts' => 0]] : [];
    }
    echo '<div class="ft-inbox-notes-label">Internal notes</div>';
    if (!empty($notes_arr)) {
        echo '<div class="ft-inbox-notes-list">';
        foreach (array_reverse($notes_arr) as $note) {
            echo '<div class="ft-inbox-note"><span class="ft-inbox-note__date">' . esc_html($note['date'] ?? '') . '</span><p class="ft-inbox-note__text">' . nl2br(esc_html($note['text'] ?? '')) . '</p></div>';
        }
        echo '</div>';
    }
    echo '<textarea name="new_note" rows="4" placeholder="Add a note..."></textarea>';
    echo '<button class="button button-primary button-large">Save</button>';
    echo '</form>';
    echo '</aside>';
    echo '</div>';
}

function ft_inbox_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to view this page.', 'floors-today'));
    }

    $settings = ft_inbox_settings();
    $updated  = isset($_GET['updated']);

    $field_descriptions = [
        'lead_id'       => 'Numeric lead ID',
        'lead_url'      => 'Admin link to view the lead',
        'full_name'     => 'Customer full name',
        'email'         => 'Customer email address',
        'phone'         => 'Customer phone number',
        'address'       => 'Full address string',
        'city'          => 'City',
        'province'      => 'Province',
        'postal_code'   => 'Postal / ZIP code',
        'flooring_type' => 'Selected flooring type',
        'property_type' => 'Property type',
        'start_time'    => 'Preferred start timeline',
        'source'        => 'Form source label',
        'page_url'      => 'Page URL the form was on',
        'date'          => 'Submission date & time',
        'status'        => 'Lead status',
    ];
    ?>
    <style>
    .ft-fs-wrap { max-width: 1100px; }
    .ft-fs-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .ft-fs-tabs { display:flex; gap:4px; margin-bottom:20px; border-bottom:2px solid #e2e8f0; padding-bottom:0; }
    .ft-fs-tab { padding:10px 18px; cursor:pointer; border:1px solid transparent; border-bottom:none; border-radius:6px 6px 0 0; font-weight:600; font-size:13px; color:#555; background:none; margin-bottom:-2px; }
    .ft-fs-tab.is-active { border-color:#e2e8f0; border-bottom-color:#fff; background:#fff; color:#1d4ed8; }
    .ft-fs-panel { display:none; }
    .ft-fs-panel.is-active { display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start; }
    .ft-fs-card { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:24px; }
    .ft-fs-card h2 { font-size:15px; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f0f0f0; }
    .ft-fs-field { margin-bottom:16px; }
    .ft-fs-field label { display:block; font-weight:600; font-size:13px; margin-bottom:5px; }
    .ft-fs-field input[type=text],
    .ft-fs-field input[type=email],
    .ft-fs-field input[type=url],
    .ft-fs-field textarea { width:100%; box-sizing:border-box; }
    .ft-fs-field small { display:block; margin-top:4px; color:#666; font-size:12px; }
    .ft-fs-check { display:flex; align-items:center; gap:8px; margin-bottom:14px; font-size:13px; }
    .ft-fs-row2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .ft-fs-sidebar { display:flex; flex-direction:column; gap:16px; }
    .ft-fs-tokens h3 { font-size:13px; font-weight:700; margin:0 0 10px; }
    .ft-fs-token-row { margin-bottom:8px; }
    .ft-fs-token { display:inline-block; background:#e8f0fe; color:#1d4ed8; border:1px solid #c7d7fc; border-radius:4px; padding:2px 8px; font-family:monospace; font-size:12px; cursor:pointer; margin:0; transition:background .15s; }
    .ft-fs-token:hover { background:#c7d7fc; }
    .ft-fs-token-desc { font-size:11px; color:#666; display:block; margin-top:3px; }
    .ft-fs-logo-preview { margin-top:8px; }
    .ft-fs-logo-preview img { max-height:50px; max-width:180px; border:1px solid #e2e8f0; border-radius:4px; padding:4px; background:#fff; }
    .ft-fs-color-row { display:flex; align-items:center; gap:10px; }
    .ft-fs-color-row input[type=color] { width:48px; height:36px; border:1px solid #ddd; border-radius:4px; padding:2px; cursor:pointer; }
    .ft-fs-email-preview { background:#f0f4ff; border:1px solid #c7d7fc; border-radius:6px; padding:12px; font-size:12px; color:#444; margin-top:8px; }
    </style>

    <div class="wrap ft-fs-wrap">
        <div class="ft-fs-header">
            <div>
                <h1 style="margin:0;">Form Settings</h1>
                <p style="margin:4px 0 0;color:#666;font-size:13px;">Email notifications, templates, logo and branding for all lead forms.</p>
            </div>
            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=ft-inbox')); ?>">Open Inbox &rarr;</a>
        </div>


        <form method="post">
            <?php wp_nonce_field('ft_inbox_save_settings', 'ft_inbox_settings_nonce'); ?>
            <input type="hidden" name="ft_inbox_action" value="save_settings">

            <!-- Tab Nav -->
            <div class="ft-fs-tabs">
                <button type="button" class="ft-fs-tab is-active" data-tab="email-design">Email Design & Logo</button>
                <button type="button" class="ft-fs-tab" data-tab="admin-email">Admin Notifications</button>
                <button type="button" class="ft-fs-tab" data-tab="client-email">Customer Confirmation</button>
                <button type="button" class="ft-fs-tab" data-tab="newsletter-email">Newsletter Email</button>
            </div>

            <!-- ── Tab 1: Email Design & Logo ─────────────────────────── -->
            <div class="ft-fs-panel is-active" id="ft-fs-tab-email-design">
                <div>
                    <div class="ft-fs-card">
                        <h2>Email Logo</h2>
                        <div class="ft-fs-field">
                            <label for="email_logo_url">Logo URL</label>
                            <input type="url" name="email_logo_url" id="email_logo_url"
                                value="<?php echo esc_attr($settings['email_logo_url']); ?>"
                                placeholder="https://yourdomain.com/logo.png">
                            <small>Paste a direct URL to your logo image. Recommended: PNG or SVG, max height 120px.</small>
                            <?php if ($settings['email_logo_url']): ?>
                                <div class="ft-fs-logo-preview">
                                    <img src="<?php echo esc_url($settings['email_logo_url']); ?>" alt="Logo preview" id="ft-logo-preview-img">
                                </div>
                            <?php else: ?>
                                <div class="ft-fs-logo-preview" id="ft-logo-preview-wrap" style="display:none;">
                                    <img src="" alt="Logo preview" id="ft-logo-preview-img">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ft-fs-field">
                            <label>Or select from Media Library</label>
                            <button type="button" class="button" id="ft-logo-media-btn">Choose Logo from Media</button>
                        </div>
                    </div>

                    <div class="ft-fs-card" style="margin-top:16px;">
                        <h2>Email Branding</h2>
                        <div class="ft-fs-field">
                            <label for="email_header_color">Header Background Color</label>
                            <div class="ft-fs-color-row">
                                <input type="color" name="email_header_color" id="email_header_color"
                                    value="<?php echo esc_attr($settings['email_header_color'] ?: '#235bb8'); ?>">
                                <input type="text" id="email_header_color_text"
                                    value="<?php echo esc_attr($settings['email_header_color'] ?: '#235bb8'); ?>"
                                    style="width:120px;" placeholder="#235bb8">
                            </div>
                        </div>
                        <div class="ft-fs-field">
                            <label for="email_footer_text">Footer Text</label>
                            <input type="text" name="email_footer_text" id="email_footer_text"
                                value="<?php echo esc_attr($settings['email_footer_text']); ?>"
                                placeholder="Company Name — All rights reserved.">
                            <small>Appears in the gray footer bar of every email.</small>
                        </div>

                        <div style="margin-top:16px;">
                            <?php submit_button('Save Settings', 'primary large', 'submit', false); ?>
                        </div>

                        <div class="ft-fs-email-preview">
                            <strong style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Email Layout Preview</strong><br><br>
                            <div style="background:<?php echo esc_attr($settings['email_header_color'] ?: '#235bb8'); ?>;border-radius:4px 4px 0 0;padding:10px;text-align:center;font-size:11px;color:#fff;" id="ft-color-preview-bar">
                                <?php if ($settings['email_logo_url']): ?>
                                    <img src="<?php echo esc_url($settings['email_logo_url']); ?>" style="max-height:28px;" id="ft-preview-logo">
                                <?php else: ?>
                                    <span id="ft-preview-logo" style="font-weight:700;"><?php echo esc_html(get_bloginfo('name')); ?></span>
                                <?php endif; ?>
                            </div>
                            <div style="border:1px solid #e2e8f0;border-top:none;border-radius:0 0 4px 4px;padding:10px;font-size:11px;color:#444;background:#fff;">Email content goes here&hellip;</div>
                            <div style="background:#f4f4f4;border-radius:0 0 4px 4px;padding:6px;text-align:center;font-size:10px;color:#888;" id="ft-preview-footer"><?php echo esc_html($settings['email_footer_text']); ?></div>
                        </div>
                    </div>
                </div>

                <div class="ft-fs-sidebar">
                    <div class="ft-fs-card ft-fs-tokens">
                        <h3>Available Field Tags</h3>
                        <p style="font-size:12px;color:#666;margin-bottom:10px;">Click any tag to copy it. Paste into the subject line or email template.</p>
                        <?php foreach ($field_descriptions as $var => $desc): ?>
                            <span class="ft-fs-token" data-token="{<?php echo esc_attr($var); ?>}"
                                title="<?php echo esc_attr($desc); ?>">{<?php echo esc_html($var); ?>}</span>
                        <?php endforeach; ?>
                        <p id="ft-token-copied" style="display:none;color:#0a7a0a;font-size:12px;margin-top:8px;">&#10003; Copied to clipboard!</p>
                    </div>
                </div>
            </div>

            <!-- ── Tab 2: Admin Notifications ─────────────────────────── -->
            <div class="ft-fs-panel" id="ft-fs-tab-admin-email">
                <div>
                    <div class="ft-fs-card">
                        <h2>Admin Email Notifications</h2>

                        <label class="ft-fs-check">
                            <input type="checkbox" name="notifications_enabled" value="1" <?php checked($settings['notifications_enabled'], '1'); ?>>
                            <span>Send an email to admins whenever a new lead is submitted</span>
                        </label>

                        <div class="ft-fs-field">
                            <label for="notification_recipients">Recipient Email Addresses</label>
                            <textarea name="notification_recipients" id="notification_recipients" rows="4"
                                placeholder="sales@example.com&#10;manager@example.com"><?php echo esc_textarea($settings['notification_recipients']); ?></textarea>
                            <small>One address per line, or separate with commas. All addresses will receive every new lead notification.</small>
                        </div>

                        <div class="ft-fs-row2">
                            <div class="ft-fs-field">
                                <label for="from_name">From Name</label>
                                <input type="text" name="from_name" id="from_name" value="<?php echo esc_attr($settings['from_name']); ?>" placeholder="Floors Today">
                            </div>
                            <div class="ft-fs-field">
                                <label for="from_email">From Email</label>
                                <input type="email" name="from_email" id="from_email" value="<?php echo esc_attr($settings['from_email']); ?>" placeholder="info@floorstoday.ca">
                            </div>
                        </div>

                        <label class="ft-fs-check">
                            <input type="checkbox" name="reply_to_customer" value="1" <?php checked($settings['reply_to_customer'], '1'); ?>>
                            <span>Set Reply-To as the customer's email so replying opens a conversation with them</span>
                        </label>

                        <div class="ft-fs-field">
                            <label for="notification_subject">Email Subject Line</label>
                            <input type="text" name="notification_subject" id="notification_subject"
                                value="<?php echo esc_attr($settings['notification_subject']); ?>"
                                placeholder="New Lead: {full_name}">
                            <small>You can use field tags like <code>{full_name}</code> in the subject.</small>
                        </div>
                    </div>

                    <div class="ft-fs-card" style="margin-top:16px;">
                        <h2>Admin Email Template</h2>
                        <p style="font-size:13px;color:#555;margin-bottom:14px;">This is the email body sent to your team when a new lead comes in. Use the field tags from the sidebar to include lead data.</p>
                        <?php
                        wp_editor(
                            $settings['notification_template'],
                            'notification_template',
                            [
                                'textarea_name' => 'notification_template',
                                'media_buttons' => false,
                                'teeny'         => false,
                                'tinymce'       => true,
                                'quicktags'     => true,
                                'editor_height' => 380,
                            ]
                        );
                        ?>
                    </div>

                    <div style="margin-top:16px;">
                        <?php submit_button('Save Settings', 'primary large', 'submit', false); ?>
                    </div>
                </div>

                <div class="ft-fs-sidebar">
                    <div class="ft-fs-card ft-fs-tokens">
                        <h3>Field Tags</h3>
                        <p style="font-size:12px;color:#666;margin-bottom:10px;">Click to copy, then paste into the subject or template above.</p>
                        <?php foreach ($field_descriptions as $var => $desc): ?>
                            <div class="ft-fs-token-row">
                                <span class="ft-fs-token" data-token="{<?php echo esc_attr($var); ?>}"
                                    title="<?php echo esc_attr($desc); ?>">{<?php echo esc_html($var); ?>}</span>
                                <span class="ft-fs-token-desc"><?php echo esc_html($desc); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <p id="ft-token-copied-2" style="display:none;color:#0a7a0a;font-size:12px;margin-top:8px;">&#10003; Copied!</p>
                    </div>
                    <div class="ft-fs-card">
                        <h3 style="font-size:13px;margin:0 0 8px;">Delivery</h3>
                        <p style="font-size:12px;color:#555;margin:0;">Emails are sent via WordPress <code>wp_mail()</code>. For reliable delivery configure SMTP in your server settings.</p>
                    </div>
                </div>
            </div>

            <!-- ── Tab 3: Client Confirmation ─────────────────────────── -->
            <div class="ft-fs-panel" id="ft-fs-tab-client-email">
                <div>
                    <div class="ft-fs-card">
                        <h2>Customer Confirmation Email</h2>

                        <label class="ft-fs-check">
                            <input type="checkbox" name="client_notifications_enabled" value="1" <?php checked($settings['client_notifications_enabled'], '1'); ?>>
                            <span>Send a confirmation email to the customer after they submit the form</span>
                        </label>

                        <div class="ft-fs-row2">
                            <div class="ft-fs-field">
                                <label for="client_from_name">From Name</label>
                                <input type="text" name="client_from_name" id="client_from_name"
                                    value="<?php echo esc_attr($settings['client_from_name']); ?>"
                                    placeholder="Floors Today">
                            </div>
                            <div class="ft-fs-field">
                                <label for="client_from_email">From Email</label>
                                <input type="email" name="client_from_email" id="client_from_email"
                                    value="<?php echo esc_attr($settings['client_from_email']); ?>"
                                    placeholder="info@floorstoday.ca">
                            </div>
                        </div>

                        <div class="ft-fs-field">
                            <label for="client_subject">Email Subject Line</label>
                            <input type="text" name="client_subject" id="client_subject"
                                value="<?php echo esc_attr($settings['client_subject']); ?>"
                                placeholder="We received your estimate request, {full_name}">
                            <small>You can use field tags like <code>{full_name}</code> in the subject.</small>
                        </div>
                    </div>

                    <div class="ft-fs-card" style="margin-top:16px;">
                        <h2>Customer Email Template</h2>
                        <p style="font-size:13px;color:#555;margin-bottom:14px;">This email is sent to the customer to confirm their request was received. Personalize it with field tags.</p>
                        <?php
                        wp_editor(
                            $settings['client_template'],
                            'client_template',
                            [
                                'textarea_name' => 'client_template',
                                'media_buttons' => false,
                                'teeny'         => false,
                                'tinymce'       => true,
                                'quicktags'     => true,
                                'editor_height' => 380,
                            ]
                        );
                        ?>
                    </div>

                    <div style="margin-top:16px;">
                        <?php submit_button('Save Settings', 'primary large', 'submit', false); ?>
                    </div>
                </div>

                <div class="ft-fs-sidebar">
                    <div class="ft-fs-card ft-fs-tokens">
                        <h3>Field Tags</h3>
                        <p style="font-size:12px;color:#666;margin-bottom:10px;">Click to copy, then paste into the subject or template above.</p>
                        <?php foreach ($field_descriptions as $var => $desc): ?>
                            <div class="ft-fs-token-row">
                                <span class="ft-fs-token" data-token="{<?php echo esc_attr($var); ?>}"
                                    title="<?php echo esc_attr($desc); ?>">{<?php echo esc_html($var); ?>}</span>
                                <span class="ft-fs-token-desc"><?php echo esc_html($desc); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <p id="ft-token-copied-3" style="display:none;color:#0a7a0a;font-size:12px;margin-top:8px;">&#10003; Copied!</p>
                    </div>
                    <div class="ft-fs-card">
                        <h3 style="font-size:13px;margin:0 0 8px;">Note</h3>
                        <p style="font-size:12px;color:#555;margin:0;">The customer's email address is captured from the form. The logo and header color set in the <em>Email Design</em> tab apply to this email too.</p>
                    </div>
                </div>
            </div>

            <!-- ── Tab 4: Newsletter Email ───────────────────────────────── -->
            <div class="ft-fs-panel" id="ft-fs-tab-newsletter-email">
                <div>
                    <div class="ft-fs-card">
                        <h2>Newsletter Subscriber Email</h2>
                        <p style="font-size:13px;color:#555;margin-bottom:14px;">This email is sent <strong>only</strong> to subscribers who submit the newsletter CTA form. It is completely separate from the estimate confirmation email.</p>

                        <label class="ft-fs-check">
                            <input type="checkbox" name="newsletter_enabled" value="1" <?php checked($settings['newsletter_enabled'], '1'); ?>>
                            <span>Send a thank-you email to newsletter subscribers</span>
                        </label>

                        <div class="ft-fs-row2">
                            <div class="ft-fs-field">
                                <label for="newsletter_from_name">From Name</label>
                                <input type="text" name="newsletter_from_name" id="newsletter_from_name"
                                    value="<?php echo esc_attr($settings['newsletter_from_name']); ?>"
                                    placeholder="Floors Today">
                            </div>
                            <div class="ft-fs-field">
                                <label for="newsletter_from_email">From Email</label>
                                <input type="email" name="newsletter_from_email" id="newsletter_from_email"
                                    value="<?php echo esc_attr($settings['newsletter_from_email']); ?>"
                                    placeholder="info@floorstoday.ca">
                            </div>
                        </div>

                        <div class="ft-fs-field">
                            <label for="newsletter_subject">Email Subject Line</label>
                            <input type="text" name="newsletter_subject" id="newsletter_subject"
                                value="<?php echo esc_attr($settings['newsletter_subject']); ?>"
                                placeholder="Welcome! Your $300 Store Credit is waiting, {full_name}">
                            <small>You can use <code>{full_name}</code> and <code>{email}</code> in the subject.</small>
                        </div>
                    </div>

                    <div class="ft-fs-card" style="margin-top:16px;">
                        <h2>Newsletter Email Template</h2>
                        <p style="font-size:13px;color:#555;margin-bottom:14px;">Customize the thank-you email sent to newsletter subscribers. Use <code>{full_name}</code> and <code>{email}</code> to personalize it.</p>
                        <?php
                        wp_editor(
                            $settings['newsletter_template'],
                            'newsletter_template',
                            [
                                'textarea_name' => 'newsletter_template',
                                'media_buttons' => false,
                                'teeny'         => false,
                                'tinymce'       => true,
                                'quicktags'     => true,
                                'editor_height' => 380,
                            ]
                        );
                        ?>
                    </div>

                    <div style="margin-top:16px;">
                        <?php submit_button('Save Settings', 'primary large', 'submit', false); ?>
                    </div>
                </div>

                <div class="ft-fs-sidebar">
                    <div class="ft-fs-card ft-fs-tokens">
                        <h3>Available Tags</h3>
                        <p style="font-size:12px;color:#666;margin-bottom:10px;">Click to copy, paste into subject or template.</p>
                        <?php foreach (['full_name' => 'Subscriber full name', 'email' => 'Subscriber email address', 'phone' => 'Subscriber phone number'] as $var => $desc): ?>
                            <div class="ft-fs-token-row">
                                <span class="ft-fs-token" data-token="{<?php echo esc_attr($var); ?>}"
                                    title="<?php echo esc_attr($desc); ?>">{<?php echo esc_html($var); ?>}</span>
                                <span class="ft-fs-token-desc"><?php echo esc_html($desc); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="ft-fs-card">
                        <h3 style="font-size:13px;margin:0 0 8px;">How it works</h3>
                        <p style="font-size:12px;color:#555;margin:0;">This template is only triggered when the form source is <strong>Newsletter CTA</strong>. Estimate forms send the Customer Confirmation email instead.</p>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
    (function () {
        // ── Tab switching ─────────────────────────────────────────────────
        var tabs = document.querySelectorAll('.ft-fs-tab');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('is-active'); });
                document.querySelectorAll('.ft-fs-panel').forEach(function (p) { p.classList.remove('is-active'); });
                tab.classList.add('is-active');
                var target = document.getElementById('ft-fs-tab-' + tab.dataset.tab);
                if (target) target.classList.add('is-active');
            });
        });

        // ── Token copy ────────────────────────────────────────────────────
        document.querySelectorAll('.ft-fs-token').forEach(function (el) {
            el.addEventListener('click', function () {
                var token = el.dataset.token;
                navigator.clipboard.writeText(token).then(function () {
                    document.querySelectorAll('[id^="ft-token-copied"]').forEach(function (msg) {
                        msg.style.display = 'block';
                        setTimeout(function () { msg.style.display = 'none'; }, 2500);
                    });
                });
            });
        });

        // ── Logo URL preview ──────────────────────────────────────────────
        var logoInput = document.getElementById('email_logo_url');
        var previewImg = document.getElementById('ft-logo-preview-img');
        var previewWrap = document.getElementById('ft-logo-preview-wrap') || previewImg?.parentElement;
        var previewLogoEl = document.getElementById('ft-preview-logo');

        if (logoInput && previewImg) {
            logoInput.addEventListener('input', function () {
                var val = this.value.trim();
                if (val) {
                    previewImg.src = val;
                    if (previewWrap) previewWrap.style.display = '';
                    if (previewLogoEl && previewLogoEl.tagName === 'IMG') { previewLogoEl.src = val; }
                    else if (previewLogoEl) {
                        var img = document.createElement('img');
                        img.src = val;
                        img.style.maxHeight = '28px';
                        img.id = 'ft-preview-logo';
                        previewLogoEl.parentNode.replaceChild(img, previewLogoEl);
                    }
                }
            });
        }

        // ── Color ↔ text sync ─────────────────────────────────────────────
        var colorPicker = document.getElementById('email_header_color');
        var colorText   = document.getElementById('email_header_color_text');
        var colorPreviewBar = document.getElementById('ft-color-preview-bar');

        if (colorPicker && colorText) {
            colorPicker.addEventListener('input', function () {
                colorText.value = this.value;
                if (colorPreviewBar) colorPreviewBar.style.background = this.value;
            });
            colorText.addEventListener('input', function () {
                if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
                    colorPicker.value = this.value;
                    if (colorPreviewBar) colorPreviewBar.style.background = this.value;
                }
            });
        }

        // ── Media library logo picker ─────────────────────────────────────
        var mediaBtn = document.getElementById('ft-logo-media-btn');
        if (mediaBtn) {
            mediaBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (typeof wp === 'undefined' || !wp.media) {
                    alert('Media library not available. Please paste the URL directly.');
                    return;
                }
                var frame = wp.media({ title: 'Select Logo', multiple: false, library: { type: 'image' } });
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    if (logoInput) {
                        logoInput.value = attachment.url;
                        logoInput.dispatchEvent(new Event('input'));
                    }
                });
                frame.open();
            });
        }

        // ── Footer text live preview ──────────────────────────────────────
        var footerInput   = document.getElementById('email_footer_text');
        var footerPreview = document.getElementById('ft-preview-footer');
        if (footerInput && footerPreview) {
            footerInput.addEventListener('input', function () {
                footerPreview.textContent = this.value;
            });
        }

        // ── Sync all TinyMCE editors before submit (handles hidden-tab editors)
        var theForm = document.querySelector('form');
        if (theForm) {
            theForm.addEventListener('submit', function () {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }
            });
        }
    })();
    </script>
    <?php
}
