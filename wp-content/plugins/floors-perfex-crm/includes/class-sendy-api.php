<?php

if (!defined('ABSPATH')) {
    exit;
}

class FT_XD_Sendy_API {

    private string $base_url;
    private string $api_key;

    public function __construct(string $base_url, string $api_key) {
        $this->base_url = rtrim($base_url, '/');
        $this->api_key  = $api_key;
    }

    public function subscribe(array $subscriber): true|WP_Error {
        $email = sanitize_email($subscriber['email'] ?? '');
        if ($email === '' || !is_email($email)) {
            return new WP_Error('xd_sendy_invalid_email', 'Please enter a valid email address.');
        }

        $list_id = sanitize_text_field($subscriber['list_id'] ?? '');
        if ($list_id === '') {
            return new WP_Error('xd_sendy_missing_list', 'Sendy list ID is missing.');
        }

        $body = [
            'api_key'  => $this->api_key,
            'list'     => $list_id,
            'email'    => $email,
            'name'     => sanitize_text_field($subscriber['name'] ?? ''),
            'country'  => sanitize_text_field($subscriber['country'] ?? 'CA'),
            'referrer' => esc_url_raw($subscriber['referrer'] ?? home_url('/')),
            'gdpr'     => 'true',
            'hp'       => '',
            'boolean'  => 'true',
        ];

        $phone = sanitize_text_field($subscriber['phone'] ?? '');
        $city  = sanitize_text_field($subscriber['city'] ?? '');

        if ($phone !== '') {
            $body['Phone'] = $phone;
            $body['phone'] = $phone;
        }

        if ($city !== '') {
            $body['City'] = $city;
            $body['city'] = $city;
        }

        $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
        if ($ip !== '') {
            $body['ipaddress'] = $ip;
        }

        $response = wp_remote_post($this->base_url . '/subscribe', [
            'timeout' => 15,
            'body'    => $body,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $raw  = trim((string) wp_remote_retrieve_body($response));
        $body = strtolower($raw);

        if ($code >= 200 && $code < 300 && in_array($body, ['true', '1', 'already subscribed.', 'already subscribed'], true)) {
            return true;
        }

        return new WP_Error('xd_sendy_subscribe_failed', $raw !== '' ? $raw : 'Sendy rejected the subscriber.', [
            'code' => $code,
            'body' => $raw,
        ]);
    }

    public function get_brands(): array|WP_Error {
        return $this->post_json('/api/brands/get-brands.php', ['api_key' => $this->api_key]);
    }

    public function get_lists(string $brand_id, bool $include_hidden = false): array|WP_Error {
        return $this->post_json('/api/lists/get-lists.php', [
            'api_key'        => $this->api_key,
            'brand_id'       => sanitize_text_field($brand_id),
            'include_hidden' => $include_hidden ? 'yes' : 'no',
        ]);
    }

    public function active_subscriber_count(string $list_id): int|WP_Error {
        $response = $this->post_plain('/api/subscribers/active-subscriber-count.php', [
            'api_key' => $this->api_key,
            'list_id' => sanitize_text_field($list_id),
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return is_numeric($response) ? (int) $response : new WP_Error('xd_sendy_count_failed', $response);
    }

    private function post_json(string $path, array $body): array|WP_Error {
        $response = $this->post_plain($path, $body);
        if (is_wp_error($response)) {
            return $response;
        }

        $json = json_decode($response, true);
        if (!is_array($json)) {
            return new WP_Error('xd_sendy_invalid_json', $response);
        }

        return $json;
    }

    private function post_plain(string $path, array $body): string|WP_Error {
        $response = wp_remote_post($this->base_url . $path, [
            'timeout' => 15,
            'body'    => $body,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $raw  = trim((string) wp_remote_retrieve_body($response));

        if ($code < 200 || $code >= 300 || stripos($raw, 'Error:') === 0) {
            return new WP_Error('xd_sendy_api_failed', $raw !== '' ? $raw : 'Sendy API request failed.', [
                'code' => $code,
                'body' => $raw,
            ]);
        }

        return $raw;
    }
}