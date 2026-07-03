<?php
/**
 * Plugin Name: Floors Elementor ACF FAQ Widget
 * Description: Elementor widget for manual or ACF repeater FAQ toggle accordions.
 * Version: 1.0.0
 * Author: Floors Today
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('elementor/elements/categories_registered', function ($elements_manager) {
    $elements_manager->add_category('floors-today', [
        'title' => __('Floors Today', 'floors-today'),
        'icon' => 'fa fa-home',
    ]);
});

add_action('elementor/widgets/register', function ($widgets_manager) {
    if (!class_exists('\Elementor\Widget_Base')) {
        return;
    }

    if (!class_exists('FT_Elementor_ACF_FAQ_Widget')) {
        class FT_Elementor_ACF_FAQ_Widget extends \Elementor\Widget_Base {
            public function get_name() {
                return 'ft_acf_faq_toggle';
            }

            public function get_title() {
                return __('ACF FAQ Toggle', 'floors-today');
            }

            public function get_icon() {
                return 'eicon-accordion';
            }

            public function get_categories() {
                return ['floors-today'];
            }

            public function get_keywords() {
                return ['faq', 'acf', 'repeater', 'accordion', 'toggle'];
            }

            protected function register_controls() {
                $this->start_controls_section('section_content', [
                    'label' => __('Content', 'floors-today'),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]);

                $this->add_control('main_title', [
                    'label' => __('Main Title', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __('My FAQ', 'floors-today'),
                    'dynamic' => ['active' => true],
                    'label_block' => true,
                ]);

                $this->add_control('description', [
                    'label' => __('Description', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'dynamic' => ['active' => true],
                ]);

                $this->add_control('source', [
                    'label' => __('Questions Source', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'acf',
                    'options' => [
                        'acf' => __('ACF Repeater', 'floors-today'),
                        'manual' => __('Manual Repeater', 'floors-today'),
                    ],
                ]);

                $this->add_control('acf_repeater', [
                    'label' => __('ACF Repeater Field Name', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => 'faqs',
                    'placeholder' => 'faqs',
                    'condition' => ['source' => 'acf'],
                ]);

                $this->add_control('acf_question', [
                    'label' => __('Question Sub Field Name', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => 'question',
                    'placeholder' => 'question',
                    'condition' => ['source' => 'acf'],
                ]);

                $this->add_control('acf_answer', [
                    'label' => __('Answer Sub Field Name', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => 'answer',
                    'placeholder' => 'answer',
                    'condition' => ['source' => 'acf'],
                ]);

                $this->add_control('acf_post_id', [
                    'label' => __('ACF Post ID', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => __('Leave empty for current post. Use option for options page.', 'floors-today'),
                    'condition' => ['source' => 'acf'],
                ]);

                $repeater = new \Elementor\Repeater();
                $repeater->add_control('question', [
                    'label' => __('Question', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __('What is FAQ?', 'floors-today'),
                    'dynamic' => ['active' => true],
                    'label_block' => true,
                ]);
                $repeater->add_control('answer', [
                    'label' => __('Answer', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::WYSIWYG,
                    'default' => __('Add your answer here.', 'floors-today'),
                    'dynamic' => ['active' => true],
                ]);

                $this->add_control('manual_items', [
                    'label' => __('Questions', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $repeater->get_controls(),
                    'title_field' => '{{{ question }}}',
                    'condition' => ['source' => 'manual'],
                    'default' => [
                        ['question' => __('What is Common Ninja?', 'floors-today'), 'answer' => __('This is a sample answer.', 'floors-today')],
                        ['question' => __('What is FAQ?', 'floors-today'), 'answer' => __('This is a sample answer.', 'floors-today')],
                        ['question' => __('Where Can I Use This Widget?', 'floors-today'), 'answer' => __('Anywhere you can place an Elementor widget.', 'floors-today')],
                    ],
                ]);

                $this->add_control('first_open', [
                    'label' => __('Open First Item', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => '',
                ]);

                if (defined('Elementor\\Controls_Manager::ICONS')) {
                    $this->add_control('closed_icon', [
                        'label' => __('Closed Icon', 'floors-today'),
                        'type' => \Elementor\Controls_Manager::ICONS,
                        'default' => [
                            'value' => 'fas fa-plus',
                            'library' => 'fa-solid',
                        ],
                    ]);

                    $this->add_control('open_icon', [
                        'label' => __('Open Icon', 'floors-today'),
                        'type' => \Elementor\Controls_Manager::ICONS,
                        'default' => [
                            'value' => 'fas fa-minus',
                            'library' => 'fa-solid',
                        ],
                    ]);
                }

                $this->end_controls_section();

                $this->start_controls_section('section_style', [
                    'label' => __('Style', 'floors-today'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]);

                $this->add_control('title_color', [
                    'label' => __('Title Color', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#235bb8',
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq__title' => 'color: {{VALUE}};'],
                ]);

                $this->add_control('accent_color', [
                    'label' => __('Accent Color', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#235bb8',
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-accent: {{VALUE}};'],
                ]);

                $this->add_control('border_color', [
                    'label' => __('Border Color', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#e5e7eb',
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-border: {{VALUE}};'],
                ]);

                $this->add_control('tab_background_color', [
                    'label' => __('Tab Background', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-tab-bg: {{VALUE}};'],
                ]);

                $this->add_control('tab_hover_background_color', [
                    'label' => __('Tab Hover Background', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#f8fafc',
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-tab-hover-bg: {{VALUE}};'],
                ]);

                $this->add_control('tab_active_background_color', [
                    'label' => __('Open Tab Background', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-tab-active-bg: {{VALUE}};'],
                ]);

                $this->add_control('answer_active_background_color', [
                    'label' => __('Open Answer Background', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => 'transparent',
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-answer-active-bg: {{VALUE}};'],
                ]);

                $this->add_control('question_color', [
                    'label' => __('Question Color', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#111827',
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-text: {{VALUE}};'],
                ]);

                $this->add_control('question_hover_color', [
                    'label' => __('Question Hover Color', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-text-hover: {{VALUE}};'],
                ]);

                $this->add_control('question_active_color', [
                    'label' => __('Open Question Color', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-text-active: {{VALUE}};'],
                ]);

                $this->add_control('icon_color', [
                    'label' => __('Icon Color', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#9ca3af',
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-icon: {{VALUE}};'],
                ]);

                $this->add_control('icon_hover_color', [
                    'label' => __('Icon Hover Color', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-icon-hover: {{VALUE}};'],
                ]);

                $this->add_control('icon_active_color', [
                    'label' => __('Open Icon Color', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq' => '--ft-faq-icon-active: {{VALUE}};'],
                ]);

                $this->add_responsive_control('gap', [
                    'label' => __('Item Gap', 'floors-today'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => ['px' => ['min' => 0, 'max' => 40]],
                    'default' => ['size' => 10, 'unit' => 'px'],
                    'selectors' => ['{{WRAPPER}} .ft-acf-faq__list' => 'gap: {{SIZE}}{{UNIT}};'],
                ]);

                if (class_exists('\Elementor\Group_Control_Typography')) {
                    $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
                        'name' => 'title_typography',
                        'label' => __('Title Typography', 'floors-today'),
                        'selector' => '{{WRAPPER}} .ft-acf-faq__title',
                    ]);

                    $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
                        'name' => 'description_typography',
                        'label' => __('Description Typography', 'floors-today'),
                        'selector' => '{{WRAPPER}} .ft-acf-faq__description',
                    ]);

                    $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
                        'name' => 'question_typography',
                        'label' => __('Question Typography', 'floors-today'),
                        'selector' => '{{WRAPPER}} .ft-acf-faq__question',
                    ]);

                    $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
                        'name' => 'answer_typography',
                        'label' => __('Answer Typography', 'floors-today'),
                        'selector' => '{{WRAPPER}} .ft-acf-faq__answer',
                    ]);
                }

                $this->end_controls_section();
            }

            private function render_faq_icon($icon, $fallback) {
                if (is_array($icon) && !empty($icon['value']) && class_exists('\Elementor\Icons_Manager')) {
                    ob_start();
                    $rendered = \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']);
                    $output = ob_get_clean();

                    if (is_string($rendered)) {
                        $output .= $rendered;
                    }

                    if ($output !== '') {
                        echo $output;
                        return;
                    }
                }

                echo esc_html($fallback);
            }

            private function get_acf_items($settings) {
                if (!function_exists('get_field')) {
                    return [];
                }

                $field = sanitize_key($settings['acf_repeater'] ?? '');
                if ($field === '') {
                    return [];
                }

                $post_id = trim((string) ($settings['acf_post_id'] ?? ''));
                if ($post_id === '') {
                    $post_id = get_the_ID();
                } elseif (is_numeric($post_id)) {
                    $post_id = (int) $post_id;
                }

                $rows = get_field($field, $post_id);
                if (!is_array($rows)) {
                    return [];
                }

                $question_key = sanitize_key($settings['acf_question'] ?? 'question');
                $answer_key = sanitize_key($settings['acf_answer'] ?? 'answer');
                $items = [];

                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }

                    $question = $row[$question_key] ?? '';
                    $answer = $row[$answer_key] ?? '';

                    if (trim(wp_strip_all_tags((string) $question)) === '' && trim(wp_strip_all_tags((string) $answer)) === '') {
                        continue;
                    }

                    $items[] = [
                        'question' => (string) $question,
                        'answer' => (string) $answer,
                    ];
                }

                return $items;
            }

            private function get_manual_items($settings) {
                $items = [];
                foreach (($settings['manual_items'] ?? []) as $item) {
                    $question = $item['question'] ?? '';
                    $answer = $item['answer'] ?? '';
                    if (trim(wp_strip_all_tags((string) $question)) === '' && trim(wp_strip_all_tags((string) $answer)) === '') {
                        continue;
                    }
                    $items[] = [
                        'question' => (string) $question,
                        'answer' => (string) $answer,
                    ];
                }
                return $items;
            }

            protected function render() {
                $settings = $this->get_settings_for_display();
                $items = ($settings['source'] ?? 'acf') === 'manual'
                    ? $this->get_manual_items($settings)
                    : $this->get_acf_items($settings);

                if (empty($items) && ($settings['source'] ?? 'acf') === 'acf') {
                    $items = $this->get_manual_items([
                        'manual_items' => [
                            ['question' => __('No ACF FAQ rows found', 'floors-today'), 'answer' => __('Check your repeater field name and sub field names.', 'floors-today')],
                        ],
                    ]);
                }

                $widget_id = 'ft-acf-faq-' . $this->get_id();
                ?>
                <style>
                    #<?php echo esc_attr($widget_id); ?> .ft-acf-faq__item { width:100%; }
                    #<?php echo esc_attr($widget_id); ?> .ft-acf-faq__question { position:relative !important; display:block !important; width:100% !important; height:auto !important; padding-right:56px !important; background:transparent !important; text-align:left !important; white-space:normal !important; }
                    #<?php echo esc_attr($widget_id); ?> .ft-acf-faq__question-text { display:block !important; width:100% !important; white-space:normal !important; overflow-wrap:break-word !important; word-break:break-word !important; }
                    #<?php echo esc_attr($widget_id); ?> .ft-acf-faq__icon { position:absolute !important; top:50% !important; right:20px !important; left:auto !important; display:inline-flex !important; width:22px !important; height:22px !important; margin:0 !important; transform:translateY(-50%) !important; }
                    #<?php echo esc_attr($widget_id); ?> .ft-acf-faq__icon > span { position:absolute !important; inset:0 !important; display:inline-flex !important; align-items:center !important; justify-content:center !important; transition:opacity .18s ease, transform .18s ease; }
                    #<?php echo esc_attr($widget_id); ?> .ft-acf-faq__icon-closed { opacity:1 !important; transform:scale(1) rotate(0) !important; }
                    #<?php echo esc_attr($widget_id); ?> .ft-acf-faq__icon-open { opacity:0 !important; transform:scale(.75) rotate(-45deg) !important; }
                    #<?php echo esc_attr($widget_id); ?> .ft-acf-faq__item.is-open .ft-acf-faq__icon-closed { opacity:0 !important; transform:scale(.75) rotate(45deg) !important; }
                    #<?php echo esc_attr($widget_id); ?> .ft-acf-faq__item.is-open .ft-acf-faq__icon-open { opacity:1 !important; transform:scale(1) rotate(0) !important; }
                    @media (max-width:640px) { #<?php echo esc_attr($widget_id); ?> .ft-acf-faq__question { padding-right:48px !important; } #<?php echo esc_attr($widget_id); ?> .ft-acf-faq__icon { right:16px !important; } }
                </style>
                <div id="<?php echo esc_attr($widget_id); ?>" class="ft-acf-faq">
                    <?php if (!empty($settings['main_title'])) : ?>
                        <h2 class="ft-acf-faq__title"><?php echo esc_html($settings['main_title']); ?></h2>
                    <?php endif; ?>
                    <?php if (!empty($settings['description'])) : ?>
                        <div class="ft-acf-faq__description"><?php echo wp_kses_post(wpautop($settings['description'])); ?></div>
                    <?php endif; ?>
                    <div class="ft-acf-faq__list">
                        <?php foreach ($items as $index => $item) :
                            $open = $index === 0 && ($settings['first_open'] ?? '') === 'yes';
                            $button_id = $widget_id . '-button-' . $index;
                            $panel_id = $widget_id . '-panel-' . $index;
                            ?>
                            <div class="ft-acf-faq__item <?php echo $open ? 'is-open' : ''; ?>">
                                <button id="<?php echo esc_attr($button_id); ?>" class="ft-acf-faq__question" type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($panel_id); ?>">
                                    <span class="ft-acf-faq__question-text"><?php echo esc_html($item['question']); ?></span>
                                    <span class="ft-acf-faq__icon" aria-hidden="true">
                                        <span class="ft-acf-faq__icon-closed"><?php $this->render_faq_icon($settings['closed_icon'] ?? [], '+'); ?></span>
                                        <span class="ft-acf-faq__icon-open"><?php $this->render_faq_icon($settings['open_icon'] ?? [], '-'); ?></span>
                                    </span>
                                </button>
                                <div id="<?php echo esc_attr($panel_id); ?>" class="ft-acf-faq__answer" role="region" aria-labelledby="<?php echo esc_attr($button_id); ?>" aria-hidden="<?php echo $open ? 'false' : 'true'; ?>">
                                    <div class="ft-acf-faq__answer-inner">
                                        <?php echo wp_kses_post(wpautop($item['answer'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
            }
        }
    }

    $widgets_manager->register(new FT_Elementor_ACF_FAQ_Widget());
});

add_action('wp_enqueue_scripts', 'ft_elementor_acf_faq_assets');
add_action('elementor/editor/after_enqueue_styles', 'ft_elementor_acf_faq_assets');
add_action('elementor/preview/enqueue_styles', 'ft_elementor_acf_faq_assets');
add_action('elementor/preview/enqueue_scripts', 'ft_elementor_acf_faq_assets');

function ft_elementor_acf_faq_assets() {
    $elementor_asset_hooks = [
        'elementor/editor/after_enqueue_styles',
        'elementor/preview/enqueue_styles',
        'elementor/preview/enqueue_scripts',
    ];
    $is_elementor_asset_hook = in_array(current_filter(), $elementor_asset_hooks, true);

    if (!$is_elementor_asset_hook && (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) || (function_exists('wp_is_json_request') && wp_is_json_request()))) {
        return;
    }

    static $loaded = [];
    $load_key = is_admin() ? 'admin' : 'frontend';
    if (isset($loaded[$load_key])) {
        return;
    }
    $loaded[$load_key] = true;

    $css = '
        .ft-acf-faq,
        .ft-acf-faq * { box-sizing: border-box; }
        .ft-acf-faq { --ft-faq-accent:#235bb8; --ft-faq-border:#e5e7eb; --ft-faq-text:#111827; --ft-faq-text-hover:var(--ft-faq-text); --ft-faq-text-active:var(--ft-faq-text); --ft-faq-icon:#9ca3af; --ft-faq-icon-hover:var(--ft-faq-icon); --ft-faq-icon-active:var(--ft-faq-accent); --ft-faq-tab-bg:#fff; --ft-faq-tab-hover-bg:#f8fafc; --ft-faq-tab-active-bg:#fff; --ft-faq-answer-active-bg:transparent; --ft-faq-muted:#667085; width:100%; font-family:Arial, Helvetica, sans-serif; }
        .ft-acf-faq__title { margin:0 0 22px !important; color:var(--ft-faq-accent); font-family:Arial, Helvetica, sans-serif; font-size:32px; font-weight:700; line-height:1.15; letter-spacing:0; }
        .ft-acf-faq__description { max-width:760px; margin:0 0 22px; color:var(--ft-faq-muted); font-size:16px; line-height:1.65; }
        .ft-acf-faq__description p { margin:0 0 12px; }
        .ft-acf-faq__list { display:grid; width:100%; gap:10px; }
        .ft-acf-faq__item { width:100%; overflow:hidden; border:1px solid var(--ft-faq-border); border-radius:8px; background:var(--ft-faq-tab-bg); box-shadow:0 8px 24px rgba(15,23,42,.04); transition:background-color .2s ease, border-color .2s ease, box-shadow .2s ease; }
        .ft-acf-faq__item:hover, .ft-acf-faq__item:focus-within { background:var(--ft-faq-tab-hover-bg); }
        .ft-acf-faq__item.is-open { background:var(--ft-faq-tab-active-bg); border-color:rgba(35,91,184,.24); box-shadow:0 12px 28px rgba(15,23,42,.07); }
        .ft-acf-faq__item.is-open:hover, .ft-acf-faq__item.is-open:focus-within { background:var(--ft-faq-tab-active-bg); }
        .ft-acf-faq .ft-acf-faq__question { appearance:none; -webkit-appearance:none; position:relative; display:block; width:100%; min-height:64px; height:auto !important; border:0 !important; border-radius:0 !important; background:transparent !important; color:var(--ft-faq-text) !important; cursor:pointer; font-family:Arial, Helvetica, sans-serif; font-size:16px; font-weight:700; line-height:1.35; padding:18px 56px 18px 20px; text-align:left; box-shadow:none !important; text-transform:none !important; letter-spacing:0 !important; white-space:normal !important; transition:color .2s ease; }
        .ft-acf-faq .ft-acf-faq__question:hover, .ft-acf-faq .ft-acf-faq__question:focus-visible { color:var(--ft-faq-text-hover) !important; outline:0; }
        .ft-acf-faq .ft-acf-faq__item.is-open .ft-acf-faq__question { color:var(--ft-faq-text-active) !important; }
        .ft-acf-faq .ft-acf-faq__question-text { display:block; width:100%; min-width:0; white-space:normal !important; overflow-wrap:break-word !important; word-break:break-word !important; }
        .ft-acf-faq .ft-acf-faq__icon { position:absolute !important; top:50% !important; right:20px !important; display:inline-flex !important; width:22px; height:22px; align-items:center; justify-content:center; color:var(--ft-faq-icon); font-size:16px; line-height:1; transform:translateY(-50%) !important; transition:color .2s ease; }
        .ft-acf-faq .ft-acf-faq__icon svg { display:block; width:1em; height:1em; fill:currentColor; }
        .ft-acf-faq .ft-acf-faq__icon > span { position:absolute; inset:0; display:inline-flex; align-items:center; justify-content:center; transition:opacity .18s ease, transform .18s ease; }
        .ft-acf-faq .ft-acf-faq__icon-closed { opacity:1; transform:scale(1) rotate(0); }
        .ft-acf-faq .ft-acf-faq__icon-open { opacity:0; transform:scale(.75) rotate(-45deg); }
        .ft-acf-faq__question:hover .ft-acf-faq__icon, .ft-acf-faq__question:focus-visible .ft-acf-faq__icon { color:var(--ft-faq-icon-hover); }
        .ft-acf-faq__item.is-open .ft-acf-faq__icon { color:var(--ft-faq-icon-active); }
        .ft-acf-faq .ft-acf-faq__item.is-open .ft-acf-faq__icon-closed { opacity:0; transform:scale(.75) rotate(45deg); }
        .ft-acf-faq .ft-acf-faq__item.is-open .ft-acf-faq__icon-open { opacity:1; transform:scale(1) rotate(0); }
        .ft-acf-faq__answer { display:grid; grid-template-rows:0fr; padding:0 20px; background:transparent; color:#374151; font-size:15px; line-height:1.7; opacity:0; overflow:hidden; transition:grid-template-rows .28s ease, opacity .2s ease, padding-bottom .28s ease; }
        .ft-acf-faq__item.is-open .ft-acf-faq__answer { grid-template-rows:1fr; padding-bottom:20px; background:var(--ft-faq-answer-active-bg); opacity:1; }
        .ft-acf-faq__answer-inner { min-height:0; overflow:hidden; }
        .ft-acf-faq__answer p { margin:0 0 12px; }
        .ft-acf-faq__answer p:last-child { margin-bottom:0; }
        @media (max-width:640px) { .ft-acf-faq__title{font-size:28px;} .ft-acf-faq__question{min-height:56px;padding:16px 48px 16px 16px;font-size:15px;} .ft-acf-faq__icon{right:16px;} .ft-acf-faq__answer{padding:0 16px;} .ft-acf-faq__item.is-open .ft-acf-faq__answer{padding-bottom:16px;} }
    ';

    wp_register_style('ft-elementor-acf-faq', false, [], '1.0.0');
    wp_enqueue_style('ft-elementor-acf-faq');
    wp_add_inline_style('ft-elementor-acf-faq', $css);

    $js = '
        document.addEventListener("click", function(event) {
            var button = event.target.closest(".ft-acf-faq__question");
            if (!button) return;
            var item = button.closest(".ft-acf-faq__item");
            var panel = item ? item.querySelector(".ft-acf-faq__answer") : null;
            if (!item || !panel) return;
            var open = !item.classList.contains("is-open");
            var list = item.closest(".ft-acf-faq__list");
            if (list) {
                list.querySelectorAll(".ft-acf-faq__item.is-open").forEach(function(openItem) {
                    if (openItem === item) return;
                    openItem.classList.remove("is-open");
                    var openButton = openItem.querySelector(".ft-acf-faq__question");
                    var openPanel = openItem.querySelector(".ft-acf-faq__answer");
                    if (openButton) openButton.setAttribute("aria-expanded", "false");
                    if (openPanel) openPanel.setAttribute("aria-hidden", "true");
                });
            }
            item.classList.toggle("is-open", open);
            button.setAttribute("aria-expanded", open ? "true" : "false");
            panel.setAttribute("aria-hidden", open ? "false" : "true");
        });
    ';

    wp_register_script('ft-elementor-acf-faq', false, [], '1.0.0', true);
    wp_enqueue_script('ft-elementor-acf-faq');
    wp_add_inline_script('ft-elementor-acf-faq', $js);
}

