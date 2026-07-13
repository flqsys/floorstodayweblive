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

        $crm_id = $result['data']['id'] ?? '';
        update_post_meta($lead_id, '_ft_xd_crm_lead_id',     $crm_id);
        update_post_meta($lead_id, '_ft_xd_crm_sync_status', 'synced');
        update_post_meta($lead_id, '_ft_xd_crm_sync_error',  '');
        update_post_meta($lead_id, '_ft_xd_crm_sync_time',   $now);
    }

    /**
     * Create a real CRM appointment for a lead that's already been synced.
     * Not linked to a CRM client record (leads and clients are separate
     * entities until a lead is converted) — the customer's details go in
     * the description instead, along with a back-reference to the CRM lead.
     */
    public function create_appointment_for_lead(int $lead_id, string $date, string $start_time): array|WP_Error {
        $crm_lead_id = get_post_meta($lead_id, '_ft_xd_crm_lead_id', true);
        $full_name   = get_post_meta($lead_id, '_ft_inbox_full_name', true);
        $phone       = get_post_meta($lead_id, '_ft_inbox_phone', true);
        $email       = get_post_meta($lead_id, '_ft_inbox_email', true);
        $flooring    = get_post_meta($lead_id, '_ft_inbox_flooring_type', true);

        $description = trim(implode(' | ', array_filter([
            $full_name,
            $phone,
            $email,
            $flooring ? "Flooring: $flooring" : '',
            $crm_lead_id ? "CRM Lead #$crm_lead_id" : '',
        ])));

        $result = $this->api->create_appointment([
            'appointment_date'       => $date,
            'appointment_start_time' => $start_time,
            'description'            => $description,
        ]);

        if (is_wp_error($result)) {
            update_post_meta($lead_id, '_ft_xd_crm_appointment_status', 'error');
            update_post_meta($lead_id, '_ft_xd_crm_appointment_error',  $result->get_error_message());
            return $result;
        }

        update_post_meta($lead_id, '_ft_xd_crm_appointment_status', 'created');
        update_post_meta($lead_id, '_ft_xd_crm_appointment_id',     $result['data']['id'] ?? '');
        update_post_meta($lead_id, '_ft_xd_crm_appointment_error',  '');

        return $result;
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
            'zip'         => $data['postal_code']  ?? '',
            'website'     => $data['referrer_url'] ?? '',
            'source'      => $source,
        ];

        // Both configured via Settings → Default Lead Values, picked from a
        // dropdown of the CRM's actual statuses/countries - not guessed. If
        // left unconfigured, omit rather than send a made-up value; the CRM
        // applies its own default status in that case.
        if (!empty($settings['default_status'])) {
            $payload['status'] = $settings['default_status'];
        }
        if (!empty($settings['default_country'])) {
            $payload['country'] = $settings['default_country'];
        }

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
            'preferred_visit_time' => $data['preferred_visit_time'] ?? '',
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
            $this->render_create_appointment_form($lead_id);
        } else {
            echo '<p><span style="color:#b32d2e;font-weight:600;">&#10007; Sync failed</span>';
            if ($synced_at) echo ' &mdash; ' . esc_html($synced_at);
            echo '</p>';
            if ($error) echo '<p style="color:#b32d2e;font-size:12px;">' . esc_html($error) . '</p>';
        }
    }

    /**
     * Manual "create a real appointment" form — deliberately not automatic.
     * Staff pick a real date/time after actually talking to the customer,
     * since the CRM has no availability-aware auto-assignment and the
     * appointee field is required (an unattended auto-create would silently
     * land on the hidden integration service account).
     */
    private function render_create_appointment_form(int $lead_id): void {
        $apt_status = get_post_meta($lead_id, '_ft_xd_crm_appointment_status', true);
        $apt_id     = get_post_meta($lead_id, '_ft_xd_crm_appointment_id',     true);
        $apt_error  = get_post_meta($lead_id, '_ft_xd_crm_appointment_error', true);

        echo '<hr style="margin:12px 0;">';

        if ($apt_status === 'created' && $apt_id) {
            echo '<p><span style="color:#0a7a0a;font-weight:600;">&#10003; Appointment created</span> (CRM ID: ' . esc_html($apt_id) . ')</p>';
            return;
        }

        if ($apt_status === 'error' && $apt_error) {
            echo '<p style="color:#b32d2e;font-size:12px;">Appointment creation failed: ' . esc_html($apt_error) . '</p>';
        }

        $nonce = wp_create_nonce('ft_xd_crm_create_appointment_' . $lead_id);
        ?>
        <p style="font-weight:600;margin-bottom:6px;">Create Appointment</p>
        <p style="margin:0 0 8px;font-size:12px;color:#666;">Pick the date/time you agreed with the customer.</p>
        <p>
            <label style="display:block;font-size:11px;color:#666;">Date</label>
            <input type="date" id="ft-xd-apt-date-<?php echo (int) $lead_id; ?>" style="width:100%;">
        </p>
        <p>
            <label style="display:block;font-size:11px;color:#666;">Start time</label>
            <input type="time" id="ft-xd-apt-time-<?php echo (int) $lead_id; ?>" style="width:100%;">
        </p>
        <button type="button" class="button button-primary ft-xd-create-appointment-btn"
            data-lead-id="<?php echo (int) $lead_id; ?>"
            data-nonce="<?php echo esc_attr($nonce); ?>">Create Appointment</button>
        <span class="ft-xd-appointment-result" style="margin-left:8px;"></span>

        <script>
        (function () {
            var btn = document.currentScript.previousElementSibling;
            if (!btn || !btn.classList.contains('ft-xd-create-appointment-btn')) {
                btn = document.querySelector('.ft-xd-create-appointment-btn[data-lead-id="<?php echo (int) $lead_id; ?>"]');
            }
            if (!btn || btn.dataset.wired) return;
            btn.dataset.wired = '1';

            btn.addEventListener('click', function () {
                var leadId = btn.dataset.leadId;
                var date   = document.getElementById('ft-xd-apt-date-' + leadId).value;
                var time   = document.getElementById('ft-xd-apt-time-' + leadId).value;
                var result = btn.nextElementSibling;

                if (!date || !time) {
                    result.style.color = '#b32d2e';
                    result.textContent = 'Pick a date and time first.';
                    return;
                }

                btn.disabled = true;
                result.style.color = '#666';
                result.textContent = 'Creating…';

                var data = new FormData();
                data.append('action', 'ft_xd_crm_create_appointment');
                data.append('nonce', btn.dataset.nonce);
                data.append('lead_id', leadId);
                data.append('appointment_date', date);
                data.append('appointment_start_time', time);

                fetch(ajaxurl, { method: 'POST', body: data })
                    .then(function (r) { return r.json(); })
                    .then(function (json) {
                        result.style.color = json.success ? '#0a7a0a' : '#b32d2e';
                        result.textContent = json.success ? '✓ ' + json.data : '✗ ' + json.data;
                        if (json.success) {
                            setTimeout(function () { location.reload(); }, 1200);
                        }
                    })
                    .catch(function () {
                        result.style.color = '#b32d2e';
                        result.textContent = '✗ Request failed.';
                    })
                    .finally(function () { btn.disabled = false; });
            });
        })();
        </script>
        <?php
    }
}
