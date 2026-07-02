<?php

if (!defined('ABSPATH')) {
    exit;
}

class FT_XD_Lead_Sync {

    private FT_XD_CRM_API $api;

    public function __construct(FT_XD_CRM_API $api) {
        $this->api = $api;
    }

    public function register(): void {
        add_action('ft_inbox_lead_created', [$this, 'sync_lead'], 10, 2);
    }

    public function sync_lead(int $lead_id, array $data): void {
        $settings = get_option(FT_XD_CRM_SETTINGS_KEY, []);

        if (empty($settings['enabled'])) {
            return;
        }

        $payload = $this->build_payload($data, $settings);
        $result  = $this->api->create_lead($payload);
        $now     = current_time('mysql');

        if (is_wp_error($result)) {
            update_post_meta($lead_id, '_ft_xd_crm_sync_status', 'error');
            update_post_meta($lead_id, '_ft_xd_crm_sync_error',  $result->get_error_message());
            update_post_meta($lead_id, '_ft_xd_crm_sync_time',   $now);
            return;
        }

        $crm_id = $result['id'] ?? ($result['lead_id'] ?? '');
        update_post_meta($lead_id, '_ft_xd_crm_lead_id',     $crm_id);
        update_post_meta($lead_id, '_ft_xd_crm_sync_status', 'synced');
        update_post_meta($lead_id, '_ft_xd_crm_sync_error',  '');
        update_post_meta($lead_id, '_ft_xd_crm_sync_time',   $now);
    }

    private function build_payload(array $data, array $settings): array {
        $source = $this->resolve_source($data, $settings);

        $address_parts = array_filter([
            $data['street'] ?? '',
            $data['unit']   ?? '',
        ]);

        $payload = [
            'name'        => $data['full_name']   ?? '',
            'email'       => $data['email']        ?? '',
            'phonenumber' => $data['phone']        ?? '',
            'address'     => implode(', ', $address_parts),
            'city'        => $data['city']         ?? '',
            'state'       => $data['province']     ?? '',
            'country'     => 'Canada',
            'zip'         => $data['postal_code']  ?? '',
            'website'     => $data['referrer_url'] ?? '',
            'source'      => $source,
            'status'      => '1',
        ];

        $custom_fields = $this->build_custom_fields($data, $settings);
        if (!empty($custom_fields)) {
            $payload['custom_fields'] = $custom_fields;
        }

        return $payload;
    }

    private function resolve_source(array $data, array $settings): string {
        $utm_source     = strtolower(trim($data['utm_source']     ?? ''));
        $traffic_source = strtolower(trim($data['traffic_source'] ?? ''));
        $referrer       = strtolower($data['referrer_url']        ?? '');

        $mapping  = $settings['source_mapping'] ?? self::default_source_mapping();
        $default  = $settings['default_source'] ?? 'Direct';

        foreach (array_filter([$utm_source, $traffic_source]) as $candidate) {
            foreach ($mapping as $keyword => $crm_source) {
                if ($candidate === strtolower($keyword) || str_contains($candidate, strtolower($keyword))) {
                    return $crm_source;
                }
            }
        }

        if ($referrer) {
            foreach ($mapping as $keyword => $crm_source) {
                if (str_contains($referrer, strtolower($keyword))) {
                    return $crm_source;
                }
            }
        }

        return $default;
    }

    private function build_custom_fields(array $data, array $settings): array {
        $cf_map = $settings['custom_field_ids'] ?? [];
        $out    = [];

        $field_values = [
            'flooring_type'   => $data['flooring_type']   ?? '',
            'property_type'   => $data['property_type']   ?? '',
            'start_time'      => $data['start_time']       ?? '',
            'utm_campaign'    => $data['utm_campaign']     ?? '',
            'utm_medium'      => $data['utm_medium']       ?? '',
            'utm_content'     => $data['utm_content']      ?? '',
            'utm_term'        => $data['utm_term']         ?? '',
            'page_url'        => $data['page_url']         ?? '',
            'device_platform' => $data['device_platform']  ?? '',
        ];

        foreach ($field_values as $field_key => $value) {
            if (!empty($cf_map[$field_key]) && $value !== '') {
                $out[(int) $cf_map[$field_key]] = $value;
            }
        }

        return $out;
    }

    public static function default_source_mapping(): array {
        return [
            'google'     => 'Google',
            'google_ads' => 'Google',
            'adwords'    => 'Google',
            'facebook'   => 'Facebook',
            'fb'         => 'Facebook',
            'instagram'  => 'Facebook',
            'ig'         => 'Facebook',
            'tiktok'     => 'TikTok',
            'email'      => 'Email',
            'newsletter' => 'Email',
            'yelp'       => 'Yelp',
            'kijiji'     => 'Kijiji',
            'direct'     => 'Direct',
        ];
    }

    public function render_inbox_meta_box(int $lead_id): void {
        $settings  = get_option(FT_XD_CRM_SETTINGS_KEY, []);
        $base_url  = rtrim($settings['base_url'] ?? '', '/');
        $status    = get_post_meta($lead_id, '_ft_xd_crm_sync_status', true);
        $crm_id    = get_post_meta($lead_id, '_ft_xd_crm_lead_id',     true);
        $error     = get_post_meta($lead_id, '_ft_xd_crm_sync_error',  true);
        $synced_at = get_post_meta($lead_id, '_ft_xd_crm_sync_time',   true);

        if (!$status) {
            echo '<p style="color:#666;">Not synced yet.</p>';
            return;
        }

        if ($status === 'synced') {
            echo '<p><span style="color:#0a7a0a;font-weight:600;">&#10003; Synced to CRM</span>';
            if ($synced_at) echo ' &mdash; ' . esc_html($synced_at);
            echo '</p>';
            if ($crm_id && $base_url) {
                $url = $base_url . '/leads/' . $crm_id . '/edit';
                echo '<p><a href="' . esc_url($url) . '" target="_blank">View in CRM &rarr;</a></p>';
            }
        } else {
            echo '<p><span style="color:#b32d2e;font-weight:600;">&#10007; Sync failed</span>';
            if ($synced_at) echo ' &mdash; ' . esc_html($synced_at);
            echo '</p>';
            if ($error) echo '<p style="color:#b32d2e;font-size:12px;">' . esc_html($error) . '</p>';
        }
    }
}
