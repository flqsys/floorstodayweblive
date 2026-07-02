<?php

if (!defined('ABSPATH')) {
    exit;
}

class FT_XD_Elementor_Action extends \ElementorPro\Modules\Forms\Classes\Action_Base {

    private FT_XD_CRM_API $api;

    public function __construct(FT_XD_CRM_API $api) {
        $this->api = $api;
    }

    public function get_name(): string {
        return 'xd_crm';
    }

    public function get_label(): string {
        return 'XD CRM Integration';
    }

    public function register_settings_section($widget): void {
        $widget->start_controls_section('section_xd_crm', [
            'label'     => 'XD CRM Integration',
            'condition' => ['submit_actions' => $this->get_name()],
        ]);

        $widget->add_control('xd_crm_field_name', [
            'label'       => 'Name Field ID',
            'type'        => \Elementor\Controls_Manager::TEXT,
            'description' => 'Elementor field ID that maps to the contact name.',
        ]);

        $widget->add_control('xd_crm_field_email', [
            'label'       => 'Email Field ID',
            'type'        => \Elementor\Controls_Manager::TEXT,
            'description' => 'Elementor field ID for email.',
        ]);

        $widget->add_control('xd_crm_field_phone', [
            'label'       => 'Phone Field ID',
            'type'        => \Elementor\Controls_Manager::TEXT,
            'description' => 'Elementor field ID for phone number.',
        ]);

        $widget->add_control('xd_crm_field_message', [
            'label'       => 'Message / Notes Field ID',
            'type'        => \Elementor\Controls_Manager::TEXT,
            'description' => 'Optional — maps to CRM lead description.',
        ]);

        $widget->add_control('xd_crm_lead_source', [
            'label'       => 'Default Lead Source',
            'type'        => \Elementor\Controls_Manager::TEXT,
            'description' => 'CRM source when no UTM source is detected.',
            'default'     => 'Website Form',
        ]);

        $widget->add_control('xd_crm_also_inbox', [
            'label'   => 'Also save to Floors Today Inbox',
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => '',
        ]);

        $widget->end_controls_section();
    }

    public function on_export($element): void {}

    public function run($record, $ajax_handler): void {
        $settings = get_option(FT_XD_CRM_SETTINGS_KEY, []);

        if (empty($settings['enabled'])) {
            return;
        }

        $ws      = $record->get('form_settings');
        $name    = $this->get_field_value($record, $ws['xd_crm_field_name']    ?? '');
        $email   = $this->get_field_value($record, $ws['xd_crm_field_email']   ?? '');
        $phone   = $this->get_field_value($record, $ws['xd_crm_field_phone']   ?? '');
        $message = $this->get_field_value($record, $ws['xd_crm_field_message'] ?? '');
        $source  = $this->resolve_source($ws, $settings);

        $payload = [
            'name'        => sanitize_text_field($name),
            'email'       => sanitize_email($email),
            'phonenumber' => sanitize_text_field($phone),
            'source'      => $source,
            'status'      => '1',
        ];

        if ($message) {
            $payload['description'] = sanitize_textarea_field($message);
        }

        $result = $this->api->create_lead($payload);

        if (!empty($ws['xd_crm_also_inbox']) && $ws['xd_crm_also_inbox'] === 'yes') {
            $this->create_inbox_lead($payload, $source, $result);
        }
    }

    private function get_field_value($record, string $field_id): string {
        if ($field_id === '') return '';
        foreach ($record->get('fields') as $field) {
            if (($field['id'] ?? '') === $field_id) return $field['value'] ?? '';
        }
        return '';
    }

    private function resolve_source(array $ws, array $plugin_settings): string {
        $default = sanitize_text_field($ws['xd_crm_lead_source'] ?? 'Website Form');
        $mapping = $plugin_settings['source_mapping'] ?? FT_XD_Lead_Sync::default_source_mapping();

        $utm_source = '';
        if (isset($_POST['utm_source'])) {
            $utm_source = strtolower(sanitize_text_field($_POST['utm_source']));
        }

        if ($utm_source) {
            foreach ($mapping as $keyword => $crm_source) {
                if ($utm_source === strtolower($keyword) || str_contains($utm_source, strtolower($keyword))) {
                    return $crm_source;
                }
            }
        }

        return $default;
    }

    private function create_inbox_lead(array $payload, string $source, $crm_result): void {
        if (!defined('FT_INBOX_POST_TYPE')) return;

        $title   = ($payload['name'] ?? 'Unknown') . ' - ' . current_time('M j, Y g:ia');
        $lead_id = wp_insert_post([
            'post_type'   => FT_INBOX_POST_TYPE,
            'post_status' => 'private',
            'post_title'  => $title,
        ]);

        if (is_wp_error($lead_id)) return;

        update_post_meta($lead_id, '_ft_inbox_full_name', $payload['name']        ?? '');
        update_post_meta($lead_id, '_ft_inbox_email',     $payload['email']       ?? '');
        update_post_meta($lead_id, '_ft_inbox_phone',     $payload['phonenumber'] ?? '');
        update_post_meta($lead_id, '_ft_inbox_source',    'Elementor Form');
        update_post_meta($lead_id, '_ft_inbox_traffic_source', $source);
        update_post_meta($lead_id, '_ft_inbox_status',   'new');
        update_post_meta($lead_id, '_ft_inbox_notes',    '');
        update_post_meta($lead_id, '_ft_inbox_unread',   '1');

        if (!is_wp_error($crm_result)) {
            update_post_meta($lead_id, '_ft_xd_crm_lead_id',     $crm_result['id'] ?? '');
            update_post_meta($lead_id, '_ft_xd_crm_sync_status', 'synced');
            update_post_meta($lead_id, '_ft_xd_crm_sync_time',   current_time('mysql'));
        }
    }
}
