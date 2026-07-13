<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Talks to the CRM's xd_api module (the same REST API the mobile app uses)
 * via a static X-Api-Key header instead of a staff JWT login/refresh cycle -
 * see Xd_Base::require_auth($allow_api_key) on the CRM side. Endpoints used
 * here (xd/leads, xd/appointments) are scoped to accept that key; the rest
 * of xd_api still requires a real staff login.
 */
class FT_XD_CRM_API {

    private string $base_url;
    private string $api_key;

    public function __construct(string $base_url, string $api_key) {
        $this->base_url = rtrim($base_url, '/');
        $this->api_key  = $api_key;
    }

    public function create_lead(array $fields): array|WP_Error {
        return $this->post('/xd/leads', $fields);
    }

    public function create_appointment(array $fields): array|WP_Error {
        return $this->post('/xd/appointments', $fields);
    }

    public function test_connection(): true|WP_Error {
        $response = wp_remote_get($this->base_url . '/xd/leads', [
            'timeout' => 10,
            'headers' => ['X-Api-Key' => $this->api_key],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code === 401 || $code === 403) {
            return new WP_Error('xd_crm_auth_failed', 'Invalid API key.');
        }

        if ($code < 200 || $code >= 300) {
            return new WP_Error('xd_crm_connection_failed', "Connection failed (HTTP $code). Check the CRM URL.");
        }

        return true;
    }

    /**
     * Lead statuses, countries, and lead custom fields from the CRM, for
     * rendering real dropdowns on the Settings page instead of asking an
     * admin to type in raw numeric IDs.
     */
    public function get_lead_field_options(): array|WP_Error {
        $response = wp_remote_get($this->base_url . '/xd/leads/field-options', [
            'timeout' => 15,
            'headers' => ['X-Api-Key' => $this->api_key],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $json = json_decode(wp_remote_retrieve_body($response), true);

        if ($code < 200 || $code >= 300 || !is_array($json) || empty($json['data'])) {
            return new WP_Error('xd_crm_field_options_failed', 'Could not load field options from the CRM.');
        }

        return $json['data'];
    }

    private function post(string $path, array $fields): array|WP_Error {
        $response = wp_remote_post($this->base_url . $path, [
            'timeout' => 20,
            'headers' => [
                'X-Api-Key'    => $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($fields),
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if ($code < 200 || $code >= 300) {
            $message = isset($json['message']) ? $json['message'] : "HTTP $code";
            return new WP_Error('xd_crm_api_error', $message, ['code' => $code, 'body' => $body]);
        }

        if (!is_array($json)) {
            return new WP_Error('xd_crm_invalid_response', 'Invalid JSON response from CRM.', ['body' => $body]);
        }

        return $json;
    }
}
