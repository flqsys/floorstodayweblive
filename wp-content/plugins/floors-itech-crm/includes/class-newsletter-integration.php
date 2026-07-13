<?php

if (!defined('ABSPATH')) {
    exit;
}

class FT_XD_Newsletter_Integration {

    public const SETTINGS_KEY = 'ft_xd_newsletter_settings';
    public const LOG_POST_TYPE = 'ft_newsletter_log';

    public static function defaults(): array {
        return [
            'destination' => 'sendy',
            'sendy_url' => 'http://sendy.flooringliquidators.ca',
            'sendy_login_url' => 'http://sendy.flooringliquidators.ca/',
            'sendy_api_key' => '',
            'sendy_brand_id' => '',
            'sendy_list_id' => '',
            'itech_crm_enabled' => '',
            'itech_crm_source' => 'Website Newsletter',
            'itech_crm_status' => '1',
            'itech_crm_tags' => 'newsletter, website',
        ];
    }

    public static function get_settings(): array {
        $saved = get_option(self::SETTINGS_KEY, []);
        return array_replace(self::defaults(), is_array($saved) ? $saved : []);
    }

    public function register(): void {
        add_action('init', [$this, 'register_log_post_type']);
        add_action('rest_api_init', [$this, 'register_routes']);
        add_shortcode('xd_newsletter_form', [$this, 'render_shortcode']);
    }

    public function register_log_post_type(): void {
        register_post_type(self::LOG_POST_TYPE, [
            'labels' => [
                'name' => 'Newsletter Logs',
                'singular_name' => 'Newsletter Log',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'options-general.php',
            'supports' => ['title'],
            'capability_type' => 'post',
        ]);
    }


    public function render_shortcode($atts = []): string {
        // The site uses the single global [floors_footer] renderer for newsletter CTA output.
        // Keep this shortcode registered for old content, but do not render a duplicate section.
        return '';
    }
    public function register_routes(): void {
        register_rest_route('floors-integrations/v1', '/newsletter', [
            'methods' => 'POST',
            'permission_callback' => '__return_true',
            'callback' => [$this, 'handle_newsletter'],
        ]);

        register_rest_route('floors-integrations/v1', '/newsletter-status', [
            'methods' => 'GET',
            'permission_callback' => [$this, 'check_status_auth'],
            'callback' => [$this, 'handle_status'],
        ]);
    }

    /**
     * Reuses the same shared secret already configured for the WP->CRM
     * direction (FT_XD_CRM_SETTINGS_KEY api_token) so this reverse,
     * CRM->WP direction doesn't need a second credential to keep in sync.
     */
    public function check_status_auth(WP_REST_Request $request): bool {
        $crm = get_option(FT_XD_CRM_SETTINGS_KEY, []);
        $expected = trim((string) ($crm['api_token'] ?? ''));
        if ($expected === '') {
            return false;
        }

        $provided = trim((string) $request->get_header('X-Api-Key'));

        return $provided !== '' && hash_equals($expected, $provided);
    }

    /**
     * Live subscription-status lookup for the CRM's promotion badges - "is
     * this email currently subscribed to the active promotion's Sendy list."
     */
    public function handle_status(WP_REST_Request $request): WP_REST_Response {
        $email = sanitize_email((string) $request->get_param('email'));
        $list_id = sanitize_text_field((string) $request->get_param('list_id'));

        if ($email === '' || !is_email($email)) {
            return rest_ensure_response(['subscribed' => false, 'raw' => 'invalid_email']);
        }

        $settings = self::get_settings();
        $list_id = $list_id !== '' ? $list_id : (string) ($settings['sendy_list_id'] ?? '');

        if (empty($settings['sendy_url']) || empty($settings['sendy_api_key']) || $list_id === '') {
            return rest_ensure_response(['subscribed' => false, 'raw' => 'sendy_not_configured']);
        }

        $api = new FT_XD_Sendy_API($settings['sendy_url'], $settings['sendy_api_key']);
        $status = $api->subscription_status($list_id, $email);

        if (is_wp_error($status)) {
            return rest_ensure_response(['subscribed' => false, 'raw' => $status->get_error_message()]);
        }

        return rest_ensure_response([
            'subscribed' => strtolower(trim($status)) === 'subscribed',
            'raw' => $status,
        ]);
    }

    public function handle_newsletter(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $params = $request->get_json_params();
        if (!is_array($params) || empty($params)) {
            $params = $request->get_body_params();
        }

        $trap = trim((string) ($params['hp'] ?? $params['ftInboxTrap'] ?? ''));
        if ($trap !== '') {
            return rest_ensure_response(['ok' => true]);
        }

        $data = [
            'name' => sanitize_text_field($params['name'] ?? $params['fullName'] ?? ''),
            'email' => sanitize_email($params['email'] ?? ''),
            'phone' => sanitize_text_field($params['phone'] ?? ''),
            'city' => sanitize_text_field($params['city'] ?? ''),
            'source' => sanitize_text_field($params['source'] ?? 'Website Newsletter'),
            'page_url' => esc_url_raw($params['pageUrl'] ?? $params['page_url'] ?? ''),
            'referrer_url' => esc_url_raw($params['referrerUrl'] ?? $params['referrer_url'] ?? wp_get_referer()),
        ];

        if ($data['email'] === '' || !is_email($data['email'])) {
            return new WP_Error('xd_newsletter_invalid_email', 'Please enter a valid email address.', ['status' => 400]);
        }

        $log_id = $this->create_log($data);
        $settings = self::get_settings();
        $destination = $settings['destination'] ?: 'sendy';
        $results = [];

        if (in_array($destination, ['sendy', 'both'], true)) {
            $results['sendy'] = $this->send_to_sendy($data, $settings);
            $this->record_result($log_id, 'sendy', $results['sendy']);
        }

        if (in_array($destination, ['itech_crm', 'both'], true) || !empty($settings['itech_crm_enabled'])) {
            $results['itech_crm'] = $this->send_to_itech_crm($data, $settings);
            $this->record_result($log_id, 'itech_crm', $results['itech_crm']);
        }

        $errors = array_filter($results, 'is_wp_error');
        if (!empty($errors)) {
            $first = reset($errors);
            return new WP_Error('xd_newsletter_delivery_failed', $first->get_error_message(), ['status' => 502]);
        }

        update_post_meta($log_id, '_ft_xd_newsletter_status', 'sent');

        return rest_ensure_response([
            'ok' => true,
            'message' => 'Thanks! You are subscribed.',
        ]);
    }

    private function send_to_sendy(array $data, array $settings): true|WP_Error {
        if (empty($settings['sendy_url']) || empty($settings['sendy_api_key']) || empty($settings['sendy_list_id'])) {
            return new WP_Error('xd_newsletter_sendy_missing', 'Sendy settings are incomplete.');
        }

        $api = new FT_XD_Sendy_API($settings['sendy_url'], $settings['sendy_api_key']);
        return $api->subscribe([
            'list_id' => $settings['sendy_list_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'city' => $data['city'],
            'referrer' => $data['page_url'] ?: $data['referrer_url'],
            'country' => 'CA',
        ]);
    }

    private function send_to_itech_crm(array $data, array $settings): true|WP_Error {
        $crm = get_option(FT_XD_CRM_SETTINGS_KEY, []);
        if (empty($crm['base_url']) || empty($crm['api_token'])) {
            return new WP_Error('xd_newsletter_itech_crm_missing', 'iTech CRM settings are incomplete.');
        }

        $tags = array_filter(array_map('trim', explode(',', (string) ($settings['itech_crm_tags'] ?? 'newsletter'))));
        $description = implode("\n", array_filter([
            'Newsletter signup',
            'Name: ' . $data['name'],
            'Email: ' . $data['email'],
            'Phone: ' . $data['phone'],
            'City: ' . $data['city'],
            'Page: ' . $data['page_url'],
            'Referrer: ' . $data['referrer_url'],
        ]));

        $payload = [
            'name' => $data['name'] !== '' ? $data['name'] : $data['email'],
            'email' => $data['email'],
            'phonenumber' => $data['phone'],
            'city' => $data['city'],
            'source' => sanitize_text_field($settings['itech_crm_source'] ?? 'Website Newsletter'),
            'status' => sanitize_text_field($settings['itech_crm_status'] ?? '1'),
            'description' => $description,
        ];

        if (!empty($tags)) {
            $payload['tags'] = implode(',', $tags);
        }

        $api = new FT_XD_CRM_API($crm['base_url'], $crm['api_token']);
        $result = $api->create_lead($payload);

        if (is_wp_error($result)) {
            return $result;
        }

        return true;
    }

    private function create_log(array $data): int {
        $title = sprintf('Newsletter: %s', $data['email']);
        $post_id = wp_insert_post([
            'post_type' => self::LOG_POST_TYPE,
            'post_status' => 'private',
            'post_title' => $title,
        ]);

        if (is_wp_error($post_id)) {
            return 0;
        }

        foreach ($data as $key => $value) {
            update_post_meta($post_id, '_ft_xd_newsletter_' . $key, $value);
        }
        update_post_meta($post_id, '_ft_xd_newsletter_status', 'pending');

        return (int) $post_id;
    }

    private function record_result(int $log_id, string $target, true|WP_Error $result): void {
        if (!$log_id) return;

        if (is_wp_error($result)) {
            update_post_meta($log_id, '_ft_xd_newsletter_' . $target . '_status', 'error');
            update_post_meta($log_id, '_ft_xd_newsletter_' . $target . '_error', $result->get_error_message());
            update_post_meta($log_id, '_ft_xd_newsletter_status', 'error');
            return;
        }

        update_post_meta($log_id, '_ft_xd_newsletter_' . $target . '_status', 'sent');
        update_post_meta($log_id, '_ft_xd_newsletter_' . $target . '_error', '');
    }
}
