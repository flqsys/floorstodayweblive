<?php

if (!defined('ABSPATH')) {
    exit;
}

class FT_XD_Event_Tracking {

    const SETTINGS_KEY = 'ft_event_tracking_settings';

    public function register(): void {
        add_action('wp_head',   [$this, 'output_pixel_head']);
        add_action('wp_footer', [$this, 'output_tracking_manager']);
        add_action('wp_ajax_ft_get_detected_forms',        [$this, 'ajax_get_detected_forms']);
        add_action('wp_ajax_nopriv_ft_get_detected_forms', [$this, 'ajax_get_detected_forms']);
    }

    // ── Head scripts (FB Pixel + GA4 + GTM) ─────────────────────────────────

    public function output_pixel_head(): void {
        $cfg = self::get_settings();

        if (!empty($cfg['fb_pixel_id'])) {
            $pid = esc_js($cfg['fb_pixel_id']);
            echo "<!-- XD CRM | Facebook Pixel -->\n";
            echo "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{$pid}');fbq('track','PageView');</script>\n";
            echo "<noscript><img height=\"1\" width=\"1\" style=\"display:none\" src=\"https://www.facebook.com/tr?id={$pid}&ev=PageView&noscript=1\"/></noscript>\n";
        }

        if (!empty($cfg['gtm_container_id'])) {
            $gtm = esc_js($cfg['gtm_container_id']);
            echo "<!-- XD CRM | Google Tag Manager -->\n";
            echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$gtm}');</script>\n";
        }

        if (!empty($cfg['ga4_measurement_id']) && empty($cfg['gtm_container_id'])) {
            // Only inject GA4 directly when GTM is not handling it
            $ga4 = esc_js($cfg['ga4_measurement_id']);
            echo "<!-- XD CRM | Google Analytics 4 -->\n";
            echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$ga4}\"></script>\n";
            echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$ga4}');</script>\n";
        }
    }

    // ── Footer: XDTrack global + form event listeners ────────────────────────

    public function output_tracking_manager(): void {
        $cfg       = self::get_settings();
        $forms     = $cfg['forms'] ?? [];
        $forms_json = wp_json_encode($forms, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
        ?>
<!-- XD CRM | Event Tracking Manager -->
<script>
(function () {
  'use strict';

  var formsCfg = <?php echo $forms_json ?: '{}'; ?>;

  function getFormCfg(formId) {
    return formsCfg[formId] || formsCfg['*'] || {};
  }

  function safeCall(fn) {
    try { fn(); } catch (e) { /* silent */ }
  }

  window.XDTrack = {
    lead: function (data) {
      var fid  = data.formId || 'unknown';
      var cfg  = getFormCfg(fid);
      var fbEv = cfg.fb_event  || 'Lead';
      var gaEv = cfg.ga4_event || 'generate_lead';
      var gtmEv= cfg.gtm_event || 'form_lead';

      safeCall(function () {
        if (window.fbq && fbEv) {
          window.fbq('track', fbEv, {
            content_name: data.formName || fid,
            content_category: data.flooringType || data.category || '',
            currency: 'CAD',
          });
        }
      });

      safeCall(function () {
        if (window.gtag && gaEv) {
          window.gtag('event', gaEv, {
            form_id:       fid,
            flooring_type: data.flooringType || '',
            lead_source:   data.leadSource   || '',
            currency: 'CAD',
          });
        }
      });

      safeCall(function () {
        if (window.dataLayer && gtmEv) {
          window.dataLayer.push({
            event:         gtmEv,
            form_id:       fid,
            form_name:     data.formName    || fid,
            flooring_type: data.flooringType || '',
            lead_source:   data.leadSource   || '',
            utm_source:    data.utmSource    || '',
          });
        }
      });
    },

    formView: function (formId) {
      var cfg  = getFormCfg(formId);
      var gaEv = cfg.ga4_view_event || 'view_form';
      safeCall(function () {
        if (window.gtag && gaEv) {
          window.gtag('event', gaEv, { form_id: formId });
        }
        if (window.dataLayer) {
          window.dataLayer.push({ event: 'form_view', form_id: formId });
        }
      });
    },
  };

  // ── Elementor Pro forms ──────────────────────────────────────────────────
  document.addEventListener('submit_success', function (e) {
    var formEl = e.target;
    var formId = formEl.dataset.formId || formEl.id || 'elementor_form';
    window.XDTrack.lead({ formId: formId, formName: formEl.dataset.formName || formId });
  });

  // Elementor Pro fires a custom jQuery event; catch it via standard events too
  document.addEventListener('elementor/forms/submit_success', function (e) {
    var detail = e.detail || {};
    var formId = detail.formId || 'elementor_form';
    window.XDTrack.lead({ formId: formId, formName: detail.formName || formId });
  });

  // ── Generic WordPress form detection ────────────────────────────────────
  // Contact Form 7
  document.addEventListener('wpcf7mailsent', function (e) {
    var detail = e.detail || {};
    var formId = 'cf7_' + (detail.contactFormId || detail.id || 'form');
    window.XDTrack.lead({ formId: formId, formName: 'Contact Form 7' });
  });

  // Gravity Forms
  document.addEventListener('gform_confirmation_loaded', function (e) {
    var detail = e.detail || {};
    var formId = 'gf_' + (detail.formId || 'form');
    window.XDTrack.lead({ formId: formId, formName: 'Gravity Form' });
  });

  // WPForms
  document.addEventListener('wpformsAjaxSubmitSuccess', function (e) {
    var detail = e.detail || {};
    var formId = 'wpforms_' + (detail.formId || 'form');
    window.XDTrack.lead({ formId: formId, formName: 'WPForms' });
  });

})();
</script>
<?php if (!empty($cfg['gtm_container_id'])): ?>
<!-- XD CRM | GTM noscript -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($cfg['gtm_container_id']); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>
        <?php
    }

    // ── Settings helpers ─────────────────────────────────────────────────────

    public static function get_settings(): array {
        return (array) get_option(self::SETTINGS_KEY, []);
    }

    // ── Detected forms (for admin UI) ────────────────────────────────────────

    public function ajax_get_detected_forms(): void {
        check_ajax_referer('ft_get_detected_forms', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }

        $forms = $this->detect_forms();
        wp_send_json_success($forms);
    }

    public function detect_forms(): array {
        $forms = [];

        // Hero form is always present
        $forms[] = [
            'id'     => 'hero_estimate',
            'name'   => 'Next.js Hero Estimate Form',
            'source' => 'Next.js Homepage',
            'type'   => 'nextjs',
        ];

        // WordPress shortcode forms
        $forms[] = [
            'id'     => 'booking_form',
            'name'   => 'Booking Form Shortcode',
            'source' => '[floors_booking_form]',
            'type'   => 'shortcode',
        ];
        $forms[] = [
            'id'     => 'product_estimate',
            'name'   => 'Product Page Estimate Form',
            'source' => '[floors_booking_form_products]',
            'type'   => 'shortcode',
        ];
        $forms[] = [
            'id'     => 'contact_form',
            'name'   => 'Contact Form',
            'source' => '[floors_booking_form_contact]',
            'type'   => 'shortcode',
        ];

        // Elementor Pro forms: query posts/pages with Elementor data containing form widget
        if (defined('ELEMENTOR_VERSION')) {
            $query = new WP_Query([
                'post_type'      => ['page', 'post', 'elementor_library'],
                'post_status'    => 'publish',
                'posts_per_page' => 50,
                'meta_query'     => [
                    ['key' => '_elementor_edit_mode', 'value' => 'builder'],
                ],
                'fields'         => 'ids',
            ]);

            foreach ($query->posts as $post_id) {
                $data = get_post_meta($post_id, '_elementor_data', true);
                if (!$data || strpos($data, '"widgetType":"form"') === false) {
                    continue;
                }

                $widgets = $this->extract_elementor_forms(json_decode($data, true) ?: []);
                foreach ($widgets as $widget) {
                    $forms[] = [
                        'id'     => 'elementor_' . $widget['id'],
                        'name'   => $widget['settings']['form_name'] ?? ('Elementor Form — ' . get_the_title($post_id)),
                        'source' => 'Elementor — ' . get_the_title($post_id),
                        'type'   => 'elementor',
                    ];
                }
            }
        }

        // Contact Form 7
        if (class_exists('WPCF7_ContactForm')) {
            $cf7_forms = get_posts(['post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1, 'fields' => 'ids']);
            foreach ($cf7_forms as $id) {
                $forms[] = [
                    'id'     => 'cf7_' . $id,
                    'name'   => get_the_title($id),
                    'source' => 'Contact Form 7',
                    'type'   => 'cf7',
                ];
            }
        }

        // Gravity Forms
        if (class_exists('GFForms') && class_exists('GFAPI') && method_exists('GFAPI', 'get_forms')) {
            /** @var array $gf_forms */
            $gf_forms = call_user_func(['GFAPI', 'get_forms']);
            foreach ($gf_forms as $gf) {
                $forms[] = [
                    'id'     => 'gf_' . $gf['id'],
                    'name'   => $gf['title'],
                    'source' => 'Gravity Forms',
                    'type'   => 'gravityforms',
                ];
            }
        }

        // WPForms
        if (function_exists('wpforms')) {
            $wf_forms = get_posts(['post_type' => 'wpforms', 'posts_per_page' => -1, 'fields' => 'ids']);
            foreach ($wf_forms as $id) {
                $forms[] = [
                    'id'     => 'wpforms_' . $id,
                    'name'   => get_the_title($id),
                    'source' => 'WPForms',
                    'type'   => 'wpforms',
                ];
            }
        }

        return $forms;
    }

    private function extract_elementor_forms(array $elements): array {
        $found = [];
        foreach ($elements as $el) {
            if (isset($el['widgetType']) && $el['widgetType'] === 'form') {
                $found[] = ['id' => $el['id'] ?? uniqid(), 'settings' => $el['settings'] ?? []];
            }
            if (!empty($el['elements'])) {
                $found = array_merge($found, $this->extract_elementor_forms($el['elements']));
            }
        }
        return $found;
    }
}
