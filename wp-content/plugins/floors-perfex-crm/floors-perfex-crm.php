<?php
/**
 * Plugin Name: XD Integration
 * Plugin URI:  https://xdeye.com
 * Description: Connects WordPress newsletter forms to Sendy, collects subscriber details, and keeps signup delivery logs in the backend.
 * Version:     1.1.0
 * Author:      xdeye.com
 * Author URI:  https://xdeye.com
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FT_XD_CRM_VERSION',     '1.1.0');
define('FT_XD_CRM_DIR',         plugin_dir_path(__FILE__));
define('FT_XD_CRM_URL',         plugin_dir_url(__FILE__));
define('FT_XD_CRM_SETTINGS_KEY','ft_xd_crm_settings');

require_once FT_XD_CRM_DIR . 'includes/class-crm-api.php';
require_once FT_XD_CRM_DIR . 'includes/class-lead-sync.php';
require_once FT_XD_CRM_DIR . 'includes/class-event-tracking.php';
require_once FT_XD_CRM_DIR . 'includes/class-sendy-api.php';
require_once FT_XD_CRM_DIR . 'includes/class-newsletter-integration.php';
// class-elementor-action.php is loaded lazily inside elementor_pro/init (see below)

// ─── Event Tracking bootstrap (always active) ────────────────────────────────

add_action('plugins_loaded', function () {
    $tracker = new FT_XD_Event_Tracking();
    $tracker->register();

    $newsletter = new FT_XD_Newsletter_Integration();
    $newsletter->register();
});

// ─── CRM Sync bootstrap ──────────────────────────────────────────────────────

add_action('plugins_loaded', function () {
    $settings = get_option(FT_XD_CRM_SETTINGS_KEY, []);

    if (!empty($settings['base_url']) && !empty($settings['api_token'])) {
        $api  = new FT_XD_CRM_API($settings['base_url'], $settings['api_token']);
        $sync = new FT_XD_Lead_Sync($api);
        $sync->register();

        // Elementor Pro integration — the registrar hook fires after EP is ready,
        // so the Action_Base parent class is guaranteed to exist at that point.
        add_action('elementor_pro/forms/actions/register', function ($registrar) use ($api) {
            require_once FT_XD_CRM_DIR . 'includes/class-elementor-action.php';
            $registrar->register(new FT_XD_Elementor_Action($api));
        });

        add_action('ft_inbox_lead_detail_meta', function ($lead_id) use ($sync) {
            $sync->render_inbox_meta_box($lead_id);
        }, 10, 1);
    }
});

// ─── Admin Menu ──────────────────────────────────────────────────────────────

add_action('admin_menu', function () {
    add_options_page(
        'XD Integration',
        'XD Integration',
        'manage_options',
        'ft-xd-crm',
        'ft_xd_crm_render_settings_page'
    );

    add_options_page(
        'XD Events & Pixels',
        'XD Events & Pixels',
        'manage_options',
        'ft-xd-events',
        'ft_xd_event_render_settings_page'
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, ['settings_page_ft-xd-crm', 'settings_page_ft-xd-events'], true)) {
        return;
    }
    wp_enqueue_style('ft-xd-crm-admin', FT_XD_CRM_URL . 'assets/admin.css', [], FT_XD_CRM_VERSION);
});

// ─── AJAX: Connection Test ────────────────────────────────────────────────────

add_action('wp_ajax_ft_xd_crm_test_connection', function () {
    check_ajax_referer('ft_xd_crm_test_connection', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
    }

    $base_url = esc_url_raw(wp_unslash($_POST['base_url'] ?? ''));
    $token    = sanitize_text_field(wp_unslash($_POST['api_token'] ?? ''));

    if (!$base_url || !$token) {
        wp_send_json_error('Please enter both the CRM URL and API token first.');
    }

    $api    = new FT_XD_CRM_API($base_url, $token);
    $result = $api->test_connection();

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success('Connection successful!');
});

// ─── AJAX: Re-sync a Lead ────────────────────────────────────────────────────

add_action('wp_ajax_ft_xd_sendy_test_connection', function () {
    check_ajax_referer('ft_xd_sendy_test_connection', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
    }

    $sendy_url = esc_url_raw(wp_unslash($_POST['sendy_url'] ?? ''));
    $api_key   = sanitize_text_field(wp_unslash($_POST['sendy_api_key'] ?? ''));
    $list_id   = sanitize_text_field(wp_unslash($_POST['sendy_list_id'] ?? ''));
    $brand_id  = sanitize_text_field(wp_unslash($_POST['sendy_brand_id'] ?? ''));

    if (!$sendy_url || !$api_key || !$list_id) {
        wp_send_json_error('Please fill in Sendy URL, API Key, and List ID first.');
    }

    $api = new FT_XD_Sendy_API($sendy_url, $api_key);

    // Test list ID — get active subscriber count
    $count = $api->active_subscriber_count($list_id);
    if (is_wp_error($count)) {
        wp_send_json_error('List ID error: ' . $count->get_error_message());
    }

    $msg = 'List OK — ' . number_format($count) . ' active subscribers.';

    // Test brand ID if provided
    if ($brand_id !== '') {
        $lists = $api->get_lists($brand_id);
        if (is_wp_error($lists)) {
            wp_send_json_error($msg . ' | Brand ID error: ' . $lists->get_error_message());
        }
        $msg .= ' Brand ID OK — ' . count($lists) . ' list(s) found.';
    }

    wp_send_json_success($msg);
});

// ─── AJAX: Re-sync a Lead ────────────────────────────────────────────────────

add_action('wp_ajax_ft_xd_crm_resync_lead', function () {
    check_ajax_referer('ft_xd_crm_resync_lead', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
    }

    $lead_id  = (int) ($_POST['lead_id'] ?? 0);
    $settings = get_option(FT_XD_CRM_SETTINGS_KEY, []);

    if (!$lead_id || empty($settings['base_url']) || empty($settings['api_token'])) {
        wp_send_json_error('Missing lead ID or CRM credentials.');
    }

    $meta_keys = [
        'full_name', 'email', 'phone', 'street', 'unit', 'city', 'province',
        'postal_code', 'flooring_type', 'property_type', 'start_time', 'source',
        'traffic_source', 'utm_source', 'utm_medium', 'utm_campaign',
        'utm_content', 'utm_term', 'referrer_url', 'page_url', 'device_platform',
    ];

    $data = [];
    foreach ($meta_keys as $key) {
        $data[$key] = get_post_meta($lead_id, '_ft_inbox_' . $key, true);
    }

    $api  = new FT_XD_CRM_API($settings['base_url'], $settings['api_token']);
    $sync = new FT_XD_Lead_Sync($api);
    $sync->sync_lead($lead_id, $data);

    $status = get_post_meta($lead_id, '_ft_xd_crm_sync_status', true);

    if ($status === 'synced') {
        wp_send_json_success('Lead successfully synced to CRM.');
    }

    $error = get_post_meta($lead_id, '_ft_xd_crm_sync_error', true);
    wp_send_json_error($error ?: 'Sync failed.');
});

// ─── CRM Settings Save ───────────────────────────────────────────────────────

add_action('admin_post_ft_xd_crm_save_settings', function () {
    check_admin_referer('ft_xd_crm_save_settings');

    if (!current_user_can('manage_options')) {
        wp_die('Permission denied.');
    }

    $base_url       = esc_url_raw(wp_unslash($_POST['base_url']       ?? ''));
    $api_token      = sanitize_text_field(wp_unslash($_POST['api_token']    ?? ''));
    $enabled        = !empty($_POST['enabled']) ? '1' : '';
    $default_source = sanitize_text_field(wp_unslash($_POST['default_source'] ?? 'Direct'));

    $newsletter_settings = [
        'destination' => sanitize_text_field(wp_unslash($_POST['newsletter_destination'] ?? 'sendy')),
        'sendy_url' => esc_url_raw(wp_unslash($_POST['sendy_url'] ?? '')),
        'sendy_login_url' => esc_url_raw(wp_unslash($_POST['sendy_login_url'] ?? '')),
        'sendy_api_key' => sanitize_text_field(wp_unslash($_POST['sendy_api_key'] ?? '')),
        'sendy_brand_id' => sanitize_text_field(wp_unslash($_POST['sendy_brand_id'] ?? '')),
        'sendy_list_id' => sanitize_text_field(wp_unslash($_POST['sendy_list_id'] ?? '')),
        'itech_crm_enabled' => !empty($_POST['newsletter_itech_crm_enabled']) ? '1' : '',
        'itech_crm_source' => sanitize_text_field(wp_unslash($_POST['newsletter_itech_crm_source'] ?? 'Website Newsletter')),
        'itech_crm_status' => sanitize_text_field(wp_unslash($_POST['newsletter_itech_crm_status'] ?? '1')),
        'itech_crm_tags' => sanitize_text_field(wp_unslash($_POST['newsletter_itech_crm_tags'] ?? 'newsletter, website')),
    ];
    if (!in_array($newsletter_settings['destination'], ['sendy', 'itech_crm', 'both'], true)) {
        $newsletter_settings['destination'] = 'sendy';
    }
    update_option(FT_XD_Newsletter_Integration::SETTINGS_KEY, array_replace(FT_XD_Newsletter_Integration::defaults(), $newsletter_settings));

    $raw_keywords = $_POST['source_keyword'] ?? [];
    $raw_values   = $_POST['source_value']   ?? [];
    $source_mapping = [];

    foreach ($raw_keywords as $i => $kw) {
        $kw  = sanitize_text_field($kw);
        $val = sanitize_text_field($raw_values[$i] ?? '');
        if ($kw !== '' && $val !== '') {
            $source_mapping[$kw] = $val;
        }
    }

    $cf_fields = [
        'flooring_type', 'property_type', 'start_time',
        'utm_campaign', 'utm_medium', 'utm_content', 'utm_term',
        'page_url', 'device_platform',
    ];
    $custom_field_ids = [];
    foreach ($cf_fields as $f) {
        $id = sanitize_text_field($_POST['cf_' . $f] ?? '');
        if ($id !== '') {
            $custom_field_ids[$f] = $id;
        }
    }

    update_option(FT_XD_CRM_SETTINGS_KEY, [
        'base_url'         => $base_url,
        'api_token'        => $api_token,
        'enabled'          => $enabled,
        'default_source'   => $default_source,
        'source_mapping'   => $source_mapping ?: FT_XD_Lead_Sync::default_source_mapping(),
        'custom_field_ids' => $custom_field_ids,
    ]);

    wp_redirect(add_query_arg([
        'page'       => 'ft-xd-crm',
        'xd_updated' => '1',
    ], admin_url('options-general.php')));
    exit;
});

// ─── CRM Settings Page UI ────────────────────────────────────────────────────

function ft_xd_crm_render_settings_page(): void {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings       = get_option(FT_XD_CRM_SETTINGS_KEY, []);
    $base_url       = $settings['base_url']        ?? '';
    $api_token      = $settings['api_token']        ?? '';
    $enabled        = $settings['enabled']          ?? '';
    $default_source = $settings['default_source']   ?? 'Direct';
    $source_mapping = $settings['source_mapping']   ?? FT_XD_Lead_Sync::default_source_mapping();
    $cf_ids         = $settings['custom_field_ids'] ?? [];
    $newsletter     = FT_XD_Newsletter_Integration::get_settings();

    $cf_fields = [
        'flooring_type'   => 'Flooring Type',
        'property_type'   => 'Property Type',
        'start_time'      => 'Start Time',
        'utm_campaign'    => 'UTM Campaign',
        'utm_medium'      => 'UTM Medium',
        'utm_content'     => 'UTM Content',
        'utm_term'        => 'UTM Term',
        'page_url'        => 'Page URL',
        'device_platform' => 'Device Platform',
    ];

    $updated = isset($_GET['xd_updated']);
    ?>
    <div class="wrap ft-xd-wrap">
        <h1>
            <span class="ft-xd-logo">XD</span>
            XD Integration
            <small>by <a href="https://xdeye.com" target="_blank">xdeye.com</a></small>
        </h1>

        <?php if ($updated): ?>
            <div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('ft_xd_crm_save_settings'); ?>
            <input type="hidden" name="action" value="ft_xd_crm_save_settings">

            <div class="ft-xd-card">
                <h2>CRM Connection</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="enabled">Enable Sync</label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" id="enabled" value="1" <?php checked($enabled, '1'); ?>>
                                Automatically push new leads to your CRM
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="base_url">CRM Base URL</label></th>
                        <td>
                            <input type="url" name="base_url" id="base_url" value="<?php echo esc_attr($base_url); ?>" class="regular-text" placeholder="https://crm.yourdomain.com">
                            <p class="description">Base URL of your CRM installation (no trailing slash).</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="api_token">API Token</label></th>
                        <td>
                            <input type="password" name="api_token" id="api_token" value="<?php echo esc_attr($api_token); ?>" class="regular-text" autocomplete="new-password">
                            <p class="description">Found in your CRM under Setup &rarr; API.</p>
                        </td>
                    </tr>
                </table>
                <button type="button" id="ft-xd-test-btn" class="button button-secondary">Test Connection</button>
                <span id="ft-xd-test-result"></span>
            </div>

            <div class="ft-xd-card">
                <h2>Newsletter Integrations</h2>
                <p class="description">Newsletter signups collect name, email, phone, and city. WordPress keeps delivery status only; Sendy and/or iTech CRM store the subscriber/lead.</p>
                <table class="form-table">
                    <tr>
                        <th><label for="newsletter_destination">Destination</label></th>
                        <td>
                            <select name="newsletter_destination" id="newsletter_destination">
                                <option value="sendy" <?php selected($newsletter['destination'] ?? 'sendy', 'sendy'); ?>>Sendy only</option>
                                <option value="itech_crm" <?php selected($newsletter['destination'] ?? 'sendy', 'itech_crm'); ?>>iTech CRM only</option>
                                <option value="both" <?php selected($newsletter['destination'] ?? 'sendy', 'both'); ?>>Both Sendy + iTech CRM</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="sendy_url">Sendy URL</label></th>
                        <td>
                            <input type="url" name="sendy_url" id="sendy_url" value="<?php echo esc_attr($newsletter['sendy_url'] ?? ''); ?>" class="regular-text" placeholder="https://sendy.example.com">
                            <p class="description">Any Sendy installation URL. No trailing slash required.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="sendy_login_url">Sendy Login URL</label></th>
                        <td>
                            <input type="url" name="sendy_login_url" id="sendy_login_url" value="<?php echo esc_attr($newsletter['sendy_login_url'] ?? 'http://sendy.flooringliquidators.ca/'); ?>" class="regular-text">
                            <?php if (!empty($newsletter['sendy_login_url'])): ?>
                                <p><a class="button button-secondary" href="<?php echo esc_url($newsletter['sendy_login_url']); ?>" target="_blank" rel="noopener">Login to Sendy</a></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="sendy_api_key">Sendy API Key</label></th>
                        <td><input type="password" name="sendy_api_key" id="sendy_api_key" value="<?php echo esc_attr($newsletter['sendy_api_key'] ?? ''); ?>" class="regular-text" autocomplete="new-password"></td>
                    </tr>
                    <tr>
                        <th><label for="sendy_brand_id">Sendy Brand ID</label></th>
                        <td>
                            <input type="text" name="sendy_brand_id" id="sendy_brand_id" value="<?php echo esc_attr($newsletter['sendy_brand_id'] ?? ''); ?>" class="regular-text">
                            <p class="description">Required when one Sendy installation has multiple brands. Find it on Sendy's Brands page under ID.</p>
                        </td>
                    </tr>                    <tr>
                        <th><label for="sendy_list_id">Sendy List ID</label></th>
                        <td>
                            <input type="text" name="sendy_list_id" id="sendy_list_id" value="<?php echo esc_attr($newsletter['sendy_list_id'] ?? ''); ?>" class="regular-text">
                            <p class="description">Sendy calls this the encrypted/hashed list ID. Subscribe API sends it as the <code>list</code> parameter.</p>
                        </td>
                    </tr>
                </table>
                <button type="button" id="ft-xd-sendy-test-btn" class="button button-secondary">Test Sendy Connection</button>
                <span id="ft-xd-sendy-test-result" style="margin-left:10px;font-weight:600;"></span>
                <table class="form-table">
                    <tr>
                        <th><label for="newsletter_itech_crm_enabled">Also Send to iTech CRM</label></th>
                        <td><label><input type="checkbox" name="newsletter_itech_crm_enabled" id="newsletter_itech_crm_enabled" value="1" <?php checked($newsletter['itech_crm_enabled'] ?? '', '1'); ?>> Create an iTech CRM lead for newsletter signups</label></td>
                    </tr>
                    <tr>
                        <th><label for="newsletter_itech_crm_source">iTech CRM Newsletter Source</label></th>
                        <td><input type="text" name="newsletter_itech_crm_source" id="newsletter_itech_crm_source" value="<?php echo esc_attr($newsletter['itech_crm_source'] ?? 'Website Newsletter'); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="newsletter_itech_crm_status">iTech CRM Status ID</label></th>
                        <td><input type="text" name="newsletter_itech_crm_status" id="newsletter_itech_crm_status" value="<?php echo esc_attr($newsletter['itech_crm_status'] ?? '1'); ?>" class="small-text"></td>
                    </tr>
                    <tr>
                        <th><label for="newsletter_itech_crm_tags">iTech CRM Tags</label></th>
                        <td><input type="text" name="newsletter_itech_crm_tags" id="newsletter_itech_crm_tags" value="<?php echo esc_attr($newsletter['itech_crm_tags'] ?? 'newsletter, website'); ?>" class="regular-text"></td>
                    </tr>
                </table>
            </div>

            <div class="ft-xd-card">
                <h2>Traffic Source Mapping</h2>
                <p class="description">Map UTM source values to CRM lead source names. Match is case-insensitive and partial.</p>
                <table class="ft-xd-mapping-table">
                    <thead>
                        <tr>
                            <th>UTM / Traffic Keyword</th>
                            <th>CRM Source Name</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="ft-xd-mapping-rows">
                        <?php foreach ($source_mapping as $keyword => $value): ?>
                            <tr>
                                <td><input type="text" name="source_keyword[]" value="<?php echo esc_attr($keyword); ?>" class="regular-text" placeholder="e.g. facebook"></td>
                                <td><input type="text" name="source_value[]"   value="<?php echo esc_attr($value);   ?>" class="regular-text" placeholder="e.g. Facebook"></td>
                                <td><button type="button" class="button ft-xd-remove-row">Remove</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" id="ft-xd-add-row" class="button">+ Add Mapping</button>

                <table class="form-table" style="margin-top:20px;">
                    <tr>
                        <th><label for="default_source">Default Source</label></th>
                        <td>
                            <input type="text" name="default_source" id="default_source" value="<?php echo esc_attr($default_source); ?>" class="regular-text" placeholder="Direct">
                            <p class="description">Used when no UTM source keyword matches.</p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="ft-xd-card">
                <h2>Custom Field IDs</h2>
                <p class="description">Enter your CRM custom field numeric IDs. Leave blank to skip.</p>
                <table class="form-table">
                    <?php foreach ($cf_fields as $field_key => $label): ?>
                        <tr>
                            <th><label for="cf_<?php echo esc_attr($field_key); ?>"><?php echo esc_html($label); ?></label></th>
                            <td>
                                <input type="number" name="cf_<?php echo esc_attr($field_key); ?>" id="cf_<?php echo esc_attr($field_key); ?>"
                                    value="<?php echo esc_attr($cf_ids[$field_key] ?? ''); ?>"
                                    class="small-text" placeholder="e.g. 3">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <?php submit_button('Save Settings'); ?>
        </form>
    </div>

    <script>
    (function () {
        document.getElementById('ft-xd-test-btn').addEventListener('click', function () {
            var btn    = this;
            var result = document.getElementById('ft-xd-test-result');
            btn.disabled = true;
            result.style.color = '#666';
            result.textContent = ' Testing…';

            var data = new FormData();
            data.append('action',    'ft_xd_crm_test_connection');
            data.append('nonce',     '<?php echo esc_js(wp_create_nonce('ft_xd_crm_test_connection')); ?>');
            data.append('base_url',  document.getElementById('base_url').value);
            data.append('api_token', document.getElementById('api_token').value);

            fetch(ajaxurl, { method: 'POST', body: data })
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    result.style.color = json.success ? '#0a7a0a' : '#b32d2e';
                    result.textContent = ' ' + (json.data || (json.success ? 'Connected!' : 'Failed.'));
                })
                .catch(function () {
                    result.style.color = '#b32d2e';
                    result.textContent = ' Request failed.';
                })
                .finally(function () { btn.disabled = false; });
        });

        document.getElementById('ft-xd-sendy-test-btn').addEventListener('click', function () {
            var btn    = this;
            var result = document.getElementById('ft-xd-sendy-test-result');
            btn.disabled = true;
            result.style.color = '#666';
            result.textContent = 'Testing…';

            var data = new FormData();
            data.append('action',       'ft_xd_sendy_test_connection');
            data.append('nonce',        '<?php echo esc_js(wp_create_nonce('ft_xd_sendy_test_connection')); ?>');
            data.append('sendy_url',      document.getElementById('sendy_url').value);
            data.append('sendy_api_key',  document.getElementById('sendy_api_key').value);
            data.append('sendy_list_id',  document.getElementById('sendy_list_id').value);
            data.append('sendy_brand_id', document.getElementById('sendy_brand_id').value);

            fetch(ajaxurl, { method: 'POST', body: data })
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    result.style.color = json.success ? '#0a7a0a' : '#b32d2e';
                    result.textContent = json.success ? '✓ ' + json.data : '✗ ' + json.data;
                })
                .catch(function () {
                    result.style.color = '#b32d2e';
                    result.textContent = '✗ Request failed.';
                })
                .finally(function () { btn.disabled = false; });
        });

        document.getElementById('ft-xd-add-row').addEventListener('click', function () {
            var tbody = document.getElementById('ft-xd-mapping-rows');
            var row   = document.createElement('tr');
            row.innerHTML = '<td><input type="text" name="source_keyword[]" class="regular-text" placeholder="e.g. tiktok"></td>'
                          + '<td><input type="text" name="source_value[]"   class="regular-text" placeholder="e.g. TikTok"></td>'
                          + '<td><button type="button" class="button ft-xd-remove-row">Remove</button></td>';
            tbody.appendChild(row);
        });

        document.getElementById('ft-xd-mapping-rows').addEventListener('click', function (e) {
            if (e.target.classList.contains('ft-xd-remove-row')) {
                e.target.closest('tr').remove();
            }
        });
    })();
    </script>
    <?php
}

// ─── Events & Pixels: Settings Save ─────────────────────────────────────────

add_action('admin_post_ft_xd_event_save_settings', function () {
    check_admin_referer('ft_xd_event_save_settings');

    if (!current_user_can('manage_options')) {
        wp_die('Permission denied.');
    }

    $fb_pixel_id = sanitize_text_field(wp_unslash($_POST['ev_fb_pixel_id']        ?? ''));
    $ga4_id      = sanitize_text_field(wp_unslash($_POST['ev_ga4_measurement_id'] ?? ''));
    $gtm_id      = sanitize_text_field(wp_unslash($_POST['ev_gtm_container_id']   ?? ''));

    $form_ids   = $_POST['ev_form_id']   ?? [];
    $fb_events  = $_POST['ev_fb_event']  ?? [];
    $ga4_events = $_POST['ev_ga4_event'] ?? [];
    $gtm_events = $_POST['ev_gtm_event'] ?? [];

    $forms = [];
    foreach ($form_ids as $i => $fid) {
        $fid = sanitize_text_field($fid);
        if ($fid === '') continue;
        $forms[$fid] = [
            'fb_event'  => sanitize_text_field($fb_events[$i]  ?? 'Lead'),
            'ga4_event' => sanitize_text_field($ga4_events[$i] ?? 'generate_lead'),
            'gtm_event' => sanitize_text_field($gtm_events[$i] ?? 'form_lead'),
        ];
    }

    update_option(FT_XD_Event_Tracking::SETTINGS_KEY, [
        'fb_pixel_id'        => $fb_pixel_id,
        'ga4_measurement_id' => $ga4_id,
        'gtm_container_id'   => $gtm_id,
        'forms'              => $forms,
    ]);

    // Keep homepage settings in sync so Next.js picks up the IDs too
    $hp = get_option('ft_next_homepage_settings', []);
    $hp['fb_pixel_id']        = $fb_pixel_id;
    $hp['ga4_measurement_id'] = $ga4_id;
    $hp['gtm_container_id']   = $gtm_id;
    update_option('ft_next_homepage_settings', $hp);

    wp_redirect(add_query_arg([
        'page'       => 'ft-xd-events',
        'xd_updated' => '1',
    ], admin_url('options-general.php')));
    exit;
});

// ─── Events & Pixels: Settings Page UI ───────────────────────────────────────

function ft_xd_event_render_settings_page(): void {
    if (!current_user_can('manage_options')) {
        return;
    }

    $cfg         = FT_XD_Event_Tracking::get_settings();
    $fb_pixel_id = $cfg['fb_pixel_id']        ?? '';
    $ga4_id      = $cfg['ga4_measurement_id']  ?? '';
    $gtm_id      = $cfg['gtm_container_id']    ?? '';
    $saved_forms = $cfg['forms']               ?? [];

    $fb_event_options  = ['Lead', 'Contact', 'CompleteRegistration', 'Schedule', 'FindLocation', 'Search', 'ViewContent', 'Subscribe', 'SubmitApplication'];
    $ga4_event_options = ['generate_lead', 'contact', 'sign_up', 'search', 'view_item', 'submit_application', 'schedule'];

    $tracker   = new FT_XD_Event_Tracking();
    $all_forms = $tracker->detect_forms();
    $updated   = isset($_GET['updated']);

    $type_icons = [
        'nextjs'       => '&#9654;',
        'elementor'    => '&#9645;',
        'cf7'          => '&#9993;',
        'gravityforms' => '&#9776;',
        'wpforms'      => '&#9998;',
    ];
    ?>
    <div class="wrap ft-xd-wrap">
        <h1>
            <span class="ft-xd-logo">XD</span>
            Events &amp; Pixels
            <small>by <a href="https://xdeye.com" target="_blank">xdeye.com</a></small>
        </h1>

        <?php if ($updated): ?>
            <div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('ft_xd_event_save_settings'); ?>
            <input type="hidden" name="action" value="ft_xd_event_save_settings">

            <div class="ft-xd-card">
                <h2>Tracking IDs</h2>
                <p class="description">Enter your tracking IDs here. These are injected on all WordPress pages <em>and</em> synced to the Next.js homepage automatically.</p>
                <table class="form-table">
                    <tr>
                        <th><label for="ev_fb_pixel_id">Facebook Pixel ID</label></th>
                        <td>
                            <input type="text" name="ev_fb_pixel_id" id="ev_fb_pixel_id"
                                value="<?php echo esc_attr($fb_pixel_id); ?>" class="regular-text" placeholder="e.g. 123456789012345">
                            <p class="description">
                                Found in <a href="https://business.facebook.com/events_manager" target="_blank" rel="noopener">Meta Business Suite &rarr; Events Manager</a>.
                                &nbsp;<a href="https://www.facebook.com/business/help/952192354843755" target="_blank" rel="noopener" style="color:#888;">How to find it &nearr;</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ev_ga4_measurement_id">GA4 Measurement ID</label></th>
                        <td>
                            <input type="text" name="ev_ga4_measurement_id" id="ev_ga4_measurement_id"
                                value="<?php echo esc_attr($ga4_id); ?>" class="regular-text" placeholder="e.g. G-XXXXXXXXXX">
                            <p class="description">
                                Found in <a href="https://analytics.google.com/analytics/web/#/a/p/admin/streams/table" target="_blank" rel="noopener">Google Analytics &rarr; Admin &rarr; Data Streams</a>.
                                Leave blank if you load GA4 via GTM.
                                &nbsp;<a href="https://support.google.com/analytics/answer/9304153" target="_blank" rel="noopener" style="color:#888;">How to find it &nearr;</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ev_gtm_container_id">GTM Container ID</label></th>
                        <td>
                            <input type="text" name="ev_gtm_container_id" id="ev_gtm_container_id"
                                value="<?php echo esc_attr($gtm_id); ?>" class="regular-text" placeholder="e.g. GTM-XXXXXXX">
                            <p class="description">
                                Found in <a href="https://tagmanager.google.com/" target="_blank" rel="noopener">Google Tag Manager &rarr; your workspace</a> (top of the page, e.g. GTM-XXXXXXX).
                                When set, GA4 is handled through GTM — leave the GA4 field blank to avoid double-counting.
                                &nbsp;<a href="https://support.google.com/tagmanager/answer/6103696" target="_blank" rel="noopener" style="color:#888;">How to find it &nearr;</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="ft-xd-card">
                <h2>Form Event Configuration</h2>
                <p class="description">All forms detected on this website are listed below. Configure which event fires on each platform when a form is submitted.</p>
                <table class="ft-xd-mapping-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="width:30%;">Form</th>
                            <th style="width:14%;">Source</th>
                            <th style="width:18%;">FB Pixel Event</th>
                            <th style="width:18%;">GA4 Event</th>
                            <th style="width:15%;">GTM Event</th>
                            <th style="width:5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="ft-xd-form-rows">
                        <?php
                        $listed = [];
                        foreach ($all_forms as $form):
                            $fid   = $form['id'];
                            $listed[] = $fid;
                            $saved = $saved_forms[$fid] ?? [];
                            $icon  = $type_icons[$form['type'] ?? ''] ?? '&#9679;';
                        ?>
                        <tr>
                            <td>
                                <input type="hidden" name="ev_form_id[]" value="<?php echo esc_attr($fid); ?>">
                                <span style="margin-right:5px;opacity:.6;"><?php echo $icon; ?></span>
                                <strong><?php echo esc_html($form['name']); ?></strong>
                                <br><small style="color:#888;"><?php echo esc_html($fid); ?></small>
                            </td>
                            <td style="color:#777;font-size:12px;"><?php echo esc_html($form['source'] ?? ''); ?></td>
                            <td>
                                <select name="ev_fb_event[]" style="width:100%;">
                                    <?php foreach ($fb_event_options as $opt): ?>
                                        <option value="<?php echo esc_attr($opt); ?>" <?php selected($saved['fb_event'] ?? 'Lead', $opt); ?>><?php echo esc_html($opt); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="ev_ga4_event[]" value="<?php echo esc_attr($saved['ga4_event'] ?? 'generate_lead'); ?>" list="ga4-suggestions" style="width:100%;"></td>
                            <td><input type="text" name="ev_gtm_event[]" value="<?php echo esc_attr($saved['gtm_event'] ?? 'form_lead'); ?>" style="width:100%;"></td>
                            <td style="text-align:center;color:#0a7a0a;" title="Auto-detected">&#10003;</td>
                        </tr>
                        <?php endforeach; ?>

                        <?php foreach ($saved_forms as $fid => $ev):
                            if (in_array($fid, $listed, true)) continue; ?>
                        <tr>
                            <td>
                                <input type="hidden" name="ev_form_id[]" value="<?php echo esc_attr($fid); ?>">
                                <em style="color:#888;"><?php echo esc_html($fid); ?></em>
                                <small style="color:#aaa;">(custom)</small>
                            </td>
                            <td style="color:#999;font-size:12px;">Custom</td>
                            <td>
                                <select name="ev_fb_event[]" style="width:100%;">
                                    <?php foreach ($fb_event_options as $opt): ?>
                                        <option value="<?php echo esc_attr($opt); ?>" <?php selected($ev['fb_event'] ?? 'Lead', $opt); ?>><?php echo esc_html($opt); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="ev_ga4_event[]" value="<?php echo esc_attr($ev['ga4_event'] ?? 'generate_lead'); ?>" list="ga4-suggestions" style="width:100%;"></td>
                            <td><input type="text" name="ev_gtm_event[]" value="<?php echo esc_attr($ev['gtm_event'] ?? 'form_lead'); ?>" style="width:100%;"></td>
                            <td style="text-align:center;">
                                <button type="button" class="ft-xd-remove-form-row" style="background:none;border:none;cursor:pointer;color:#b32d2e;font-size:16px;">&#10005;</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <datalist id="ga4-suggestions">
                    <?php foreach ($ga4_event_options as $opt): ?><option value="<?php echo esc_attr($opt); ?>"><?php endforeach; ?>
                </datalist>

                <br>
                <button type="button" id="ft-xd-add-form-row" class="button">+ Add Custom Form</button>
                <p class="description" style="margin-top:8px;">Use this to track forms not auto-detected (e.g. embedded third-party widgets).</p>
            </div>

            <?php submit_button('Save Events & Pixels'); ?>
        </form>
    </div>

    <script>
    (function () {
        var fbOpts = <?php echo wp_json_encode($fb_event_options); ?>;

        document.getElementById('ft-xd-add-form-row').addEventListener('click', function () {
            var tbody = document.getElementById('ft-xd-form-rows');
            var sel   = fbOpts.map(function (o) { return '<option value="' + o + '">' + o + '</option>'; }).join('');
            var row   = document.createElement('tr');
            row.innerHTML =
                '<td><input type="hidden" name="ev_form_id[]" value="">'
                + '<input type="text" name="_ev_form_id_label" style="width:140px;" placeholder="form_id"'
                + ' oninput="this.previousElementSibling.value=this.value"></td>'
                + '<td style="color:#999;font-size:12px;">Custom</td>'
                + '<td><select name="ev_fb_event[]" style="width:100%;">' + sel + '</select></td>'
                + '<td><input type="text" name="ev_ga4_event[]" value="generate_lead" list="ga4-suggestions" style="width:100%;"></td>'
                + '<td><input type="text" name="ev_gtm_event[]" value="form_lead" style="width:100%;"></td>'
                + '<td><button type="button" class="ft-xd-remove-form-row" style="background:none;border:none;cursor:pointer;color:#b32d2e;font-size:16px;">&#10005;</button></td>';
            tbody.appendChild(row);
        });

        document.getElementById('ft-xd-form-rows').addEventListener('click', function (e) {
            if (e.target.classList.contains('ft-xd-remove-form-row')) {
                e.target.closest('tr').remove();
            }
        });
    })();
    </script>
    <?php
}
