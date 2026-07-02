<?php

if (!defined('ABSPATH')) {
    exit;
}

class FT_XD_CRM_API {

    private string $base_url;
    private string $token;

    public function __construct(string $base_url, string $token) {
        $this->base_url = rtrim($base_url, '/');
        $this->token    = $token;
    }

    public function create_lead(array $fields): array|WP_Error {
        $url = $this->base_url . '/api/leads';

        $response = wp_remote_post($url, [
            'timeout' => 20,
            'headers' => [
                'authtoken'    => $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => $this->build_body($fields),
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

    public function test_connection(): true|WP_Error {
        $url = $this->base_url . '/api/leads?limit=1';

        $response = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => ['authtoken' => $this->token],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code === 401 || $code === 403) {
            return new WP_Error('xd_crm_auth_failed', 'Invalid API token.');
        }

        if ($code < 200 || $code >= 300) {
            return new WP_Error('xd_crm_connection_failed', "Connection failed (HTTP $code). Check the CRM URL.");
        }

        return true;
    }

    private function build_body(array $fields): string {
        $flat = [];

        foreach ($fields as $key => $value) {
            if ($key === 'custom_fields' && is_array($value)) {
                foreach ($value as $cf_id => $cf_val) {
                    $flat["custom_fields[$cf_id]"] = $cf_val;
                }
            } else {
                $flat[$key] = $value;
            }
        }

        return http_build_query($flat);
    }
}
