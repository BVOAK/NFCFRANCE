<?php

namespace UiCoreElements;


use UiCoreElements\Utils\Contact_Form_Service as Form_Service;
use UiCoreElements\Utils\Newsletter_Services;
use UiCoreElements\Utils\Form_Component;


use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Utils;


defined('ABSPATH') || exit();

/**
 * Newsletter
 *
 * @author Lucas Marini Falbo <lucas@uicore.co>
 * @since 1.0.7
 */

class Newsletter extends UiCoreWidget
{

    use Form_Component;

    public function get_name()
    {
        return 'uicore-newsletter';
    }

    public function get_title()
    {
        return __('Newsletter', 'uicore-elements');
    }

    public function get_icon()
    {
        return 'eicon-form-horizontal ui-e-widget';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_keywords()
    {
        return ['form', 'forms', 'newsletter', 'news', 'subscribe', 'subscription', 'mailchimp'];
    }
    public function get_styles()
    {
        return ['newsletter'];
    }
    public function get_scripts()
    {
        return [
            'contact-form',
            'recaptcha' => [
                'custom_condition' => $this->check_recaptcha_version('v2')
            ],
            'recaptcha-v3' => [
                'custom_condition' => $this->check_recaptcha_version('v3')
            ]
        ];
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }
    function check_recaptcha_version($version)
    {

        if ($this->is_edit_mode()) {
            return true;
        }

        return $version === $this->get_settings_for_display('recaptcha_version') ? true : false;
    }

    function form_fields_render_attributes($i, $instance, $item)
    {
        $this->add_render_attribute(
            [
                'field-group' . $i => [
                    'class' => [
                        'ui-e-field-type-text',
                        'ui-e-field-group',
                        'elementor-column',
                    ],
                ],
                'honeypot' => [
                    'class' => [
                        'ui-e-field-type-address',
                        'ui-e-field-group',
                        'elementor-column',
                    ],
                ],
                'recaptcha' => [
                    'class' => [
                        'ui-e-field-type-recaptcha',
                        'ui-e-field-group',
                        'elementor-column',
                        'elementor-col-100'
                    ],
                ],
                'recaptcha_v3' => [
                    'class' => [
                        'ui-e-field-type-recaptcha_v3',
                        'ui-e-field-group',
                        'elementor-column',
                        'elementor-col-100'
                    ],
                ],
                'input' . $i => [
                    'type' => 'text',
                    'size' => '1',
                    'name' => 'form_fields[' . $item . ']',
                    'id' => 'form-fields-' . $item,
                    'class' => [
                        'ui-e-field',
                        empty($instance[$item . '_css']) ? '' : esc_attr($instance[$item . '_css']),
                    ],
                ],
                'label' . $i => [
                    'for' => 'form-field-' . $item,
                    'class' => 'ui-e-label',
                ],
            ]
        );

        if (empty($instance[$item . '_width'])) {
            $item['width'] = '100';
        }

        $this->add_render_attribute('field-group' . $i, 'class', 'elementor-col-' . $instance[$item . '_width']);

        if (! empty($instance[$item . '_width_tablet'])) {
            $this->add_render_attribute('field-group' . $i, 'class', 'elementor-md-' . $instance[$item . '_width_tablet']);
        }

        if (! empty($instance[$item . '_width_mobile'])) {
            $this->add_render_attribute('field-group' . $i, 'class', 'elementor-sm-' . $instance[$item . '_width_mobile']);
        }

        // Allow zero as placeholder.
        if (! Utils::is_empty($instance[$item . '_placeholder'])) {
            $this->add_render_attribute('input' . $i, 'placeholder', $instance[$item . '_placeholder']);
        }

        if (! $instance['show_labels']) {
            $this->add_render_attribute('label' . $i, 'class', 'elementor-screen-only');
        }

        if (! empty($instance[$item . '_required'])) {
            if (! empty($instance['mark_required'])) {
                $this->add_render_attribute(
                    'field-group' . $i,
                    [
                        'class' => 'ui-e-required',
                        'required' => 'required',
                    ]
                );
            }
        }
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'section_form_settings',
            [
                'label' => esc_html__('Form Settings', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'form_name',
            [
                'label' => esc_html__('Form Name', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('New Form', 'uicore-elements'),
                'placeholder' => esc_html__('Form Name', 'uicore-elements'),
            ]
        );
        $this->add_control(
            'show_labels',
            [
                'label' => esc_html__('Label', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'uicore-elements'),
                'label_off' => esc_html__('Hide', 'uicore-elements'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );
        $this->add_control(
            'mark_required',
            [
                'label' => esc_html__('Required Mark', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'uicore-elements'),
                'label_off' => esc_html__('Hide', 'uicore-elements'),
                'default' => '',
                'render_type' => 'template',
                'condition' => [
                    'show_labels!' => '',
                ],
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'section_form_fields',
            [
                'label' => esc_html__('Fields', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'add_name',
            [
                'label' => esc_html__('Name', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'default' => 'true',
            ]
        );
        $this->add_control(
            'add_recaptcha',
            [
                'label' => esc_html__('Recaptcha', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'true',
            ]
        );
        $this->add_control(
            'add_honeypot',
            [
                'label' => esc_html__('Honeypot', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'description' => esc_html__('Adds a hidden field to trap and identify bots by capturing automated form submissions.', 'uicore-elements'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->start_controls_tabs('form_fields_tabs');

        $this->start_controls_tab(
            'email_tab',
            [
                'label' => esc_html__('E-mail', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'email_label',
            [
                'label' => esc_html__('Label', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $this->add_control(
            'email_placeholder',
            [
                'label' => esc_html__('Placeholder', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Your e-mail', 'uicore-elements'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $this->add_control(
            'email_required',
            [
                'label' => esc_html__('Required', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'default' => '',
            ]
        );
        $this->add_responsive_control(
            'email_width',
            [
                'label' => esc_html__('Column Width', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->TRAIT_get_width_values(),
                'default' => '40',
            ]
        );
        $this->add_control(
            'email_css',
            [
                'label' => esc_html__('Custom Class', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'ai' => [
                    'active' => false,
                ],
                'separator' => 'before',
                'placeholder' => esc_html__('e.g: my-class', 'uicore-elements'),
            ]
        );
        $this->add_control(
            'email_shortcode',
            [
                'label' => esc_html__('Shortcode', 'uicore-elements'),
                'type' => Controls_Manager::RAW_HTML,
                'classes' => 'forms-field-shortcode',
                'raw' => '<input class="elementor-form-field-shortcode" value=\'[field id="email"]\' readonly />',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'name_tab',
            [
                'label' => esc_html__('Name', 'uicore-elements'),
                'condition' => [
                    'add_name' => 'true',
                ],
            ]
        );

        $this->add_control(
            'name_label',
            [
                'label' => esc_html__('Label', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $this->add_control(
            'name_placeholder',
            [
                'label' => esc_html__('Placeholder', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Your name', 'uicore-elements'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $this->add_control(
            'name_required',
            [
                'label' => esc_html__('Required', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'default' => '',
            ]
        );
        $this->add_responsive_control(
            'name_width',
            [
                'label' => esc_html__('Column Width', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->TRAIT_get_width_values(),
                'default' => '40',
            ]
        );
        $this->add_control(
            'name_css',
            [
                'label' => esc_html__('Custom Class', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'ai' => [
                    'active' => false,
                ],
                'separator' => 'before',
                'placeholder' => esc_html__('e.g: my-class', 'uicore-elements'),
            ]
        );
        $this->add_control(
            'name_shortcode',
            [
                'label' => esc_html__('Shortcode', 'uicore-elements'),
                'type' => Controls_Manager::RAW_HTML,
                'classes' => 'forms-field-shortcode',
                'raw' => '<input class="elementor-form-field-shortcode" value=\'[field id="name"]\' readonly />',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'recaptcha_tab',
            [
                'label' => esc_html__('Recaptcha', 'uicore-elements'),
                'condition' => [
                    'add_recaptcha' => 'true',
                ],
            ]
        );

        $this->add_control(
            'recaptcha_version',
            [
                'label' => esc_html__('Recaptcha Version', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'v2' => 'V2',
                    'v3' => 'V3',
                ],
                'default' => 'v2',
            ]
        );
        $this->add_control(
            'recaptcha_hide',
            [
                'label' => esc_html__('Badge', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'hidden',
                'options' => [
                    'hidden' => esc_html__('Hidden', 'uicore-elements'),
                    'visible' => esc_html__('Visible', 'uicore-elements')
                ],
                'condition' => [
                    'recaptcha_version' => 'v3',
                ],
                'selectors' => [
                    '.grecaptcha-badge' => 'visibility: {{VALUE}};',
                ],
            ]
        );
        $recaptcha_keys = (!get_option('uicore_elements_recaptcha_secret_key') && !get_option('uicore_elements_recaptcha_site_key')) ? true : false;  // Check if recaptcha API keys were set
        $this->add_control(
            'recaptcha_warning',
            [
                'type' => Controls_Manager::ALERT,
                'alert_type' => 'warning',
                'dismissible' => false,
                'heading' => esc_html__("You haven't set your reCAPTCHA API keys yet.", 'uicore-elements'),
                'content' => Helper::get_admin_settings_url(esc_html__("Click here to configure your API keys.", 'uicore-elements')),
                'condition' => [
                    'add_recaptcha' => $recaptcha_keys ? 'true' : [],
                ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_buttons',
            [
                'label' => esc_html__('Button', 'uicore-elements'),
            ]
        );

        $this->TRAIT_register_button_controls('Subscribe');

        $this->end_controls_section();

        $this->start_controls_section(
            'section_actions',
            [
                'label' => esc_html__('Actions After Submit', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'submit_actions',
            [
                'label' => esc_html__('Submit Actions', 'uicore-elements'),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'multiple' => true,
                'options' => Form_Service::get_form_submit_options(),
                'default' => ['email'],
            ]
        );

        $this->end_controls_section();

        // Submit sections
        $this->start_controls_section(
            'section_email',
            [
                'label' => esc_html__('Email', 'uicore-elements'),
                'condition' => [
                    'submit_actions' => 'email',
                ],
            ]
        );

        $this->TRAIT_register_submit_email_controls($this);

        $this->end_controls_section();

        $this->start_controls_section(
            'section_email_2',
            [
                'label' => esc_html__('Email 2', 'uicore-elements'),
                'condition' => [
                    'submit_actions' => 'email_2',
                ],
            ]
        );

        $this->TRAIT_register_submit_email_controls($this, '_2');

        $this->end_controls_section();

        $this->start_controls_section(
            'section_redirect',
            [
                'label' => esc_html__('Redirect', 'uicore-elements'),
                'condition' => [
                    'submit_actions' => 'redirect',
                ],
            ]
        );

        $this->TRAIT_register_submit_redirect_controls();

        $this->end_controls_section();

        // Get and build the newsletter services section conditional display
        $terms = [];
        foreach (Newsletter_Services::get_services_list('keys') as $service) {
            $terms[] = [
                'name' => 'submit_actions',
                'operator' => 'contains',
                'value' => $service,
            ];
        }

        $this->start_controls_section(
            'newsletter_services_section',
            [
                'label' => esc_html__('Newsletter Service', 'uicore-elements'),
                'conditions' => [
                    'relation' => 'or',
                    'terms' => $terms,
                ],
            ]
        );

        $this->TRAIT_register_submit_newsletter_services_controls();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_form_options',
            [
                'label' => esc_html__('Additional Options', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->TRAIT_register_additional_controls(Form_Service::get_default_messages());

        $this->end_controls_section();

        $this->TRAIT_register_all_form_style_controls();
    }

    protected function render()
    {

        $instance = $this->get_settings_for_display();
        $is_recaptcha_required = false;

        // Save widget settings so form API can retrieve them
        set_transient('ui_e_form_widget_settings_' . $this->get_id(), $instance, DAY_IN_SECONDS);

        // Render Form and Fields Atts
        $this->add_render_attribute(
            [
                'wrapper' => [
                    'class' => [
                        'ui-e-fields-wrp',
                    ],
                ],
                'submit-group' => [
                    'class' => [
                        'ui-e-field-group',
                        'elementor-column',
                        'ui-e-field-type-submit',
                    ],
                ],
                'button' => [
                    'class' => 'elementor-button',
                ],
                'icon-align' => [
                    'class' => [
                        empty($instance['button_icon_align']) ? '' : 'ui-e-icon ui-e-align-' . $instance['button_icon_align'],
                    ],
                ],
            ]
        );
        if ($instance['add_name'] == 'true') {
            $this->form_fields_render_attributes('_name', $instance, 'name');
        }
        $this->form_fields_render_attributes('_email', $instance, 'email');

        // Fallback for empty control values
        if (empty($instance['button_width'])) {
            $instance['button_width'] = '100';
        }

        // Button atts
        $this->add_render_attribute('submit-group', 'class', 'elementor-col-' . $instance['button_width'] . ' e-form__buttons');

        if (! empty($instance['button_width_tablet'])) {
            $this->add_render_attribute('submit-group', 'class', 'elementor-md-' . $instance['button_width_tablet']);
        }
        if (! empty($instance['button_width_mobile'])) {
            $this->add_render_attribute('submit-group', 'class', 'elementor-sm-' . $instance['button_width_mobile']);
        }
        if ($instance['button_hover_animation']) {
            $this->add_render_attribute('button', 'class', 'elementor-animation-' . $instance['button_hover_animation']);
        }

        // Form atts
        if (! empty($instance['form_id'])) {
            $this->add_render_attribute('form', 'id', $instance['form_id']);
        }
        if (! empty($instance['form_name'])) {
            $this->add_render_attribute('form', 'name', $instance['form_name']);
        }
        if ('no' === $instance['form_validation']) {
            $this->add_render_attribute('form', 'novalidate');
        }
        if (! empty($instance['button_css_id'])) {
            $this->add_render_attribute('button', 'id', $instance['button_css_id']);
        }

        // Basic settings
        $print_label = $instance['show_labels'] == 'true';
?>

        <form class="ui-e-form" method="post" <?php $this->print_render_attribute_string('form'); ?>>

            <input type="hidden" name="widget_id" value="<?php echo esc_attr($this->get_id()); ?>" />
            <input type="hidden" name="widget_type" value="newsletter">

            <div <?php $this->print_render_attribute_string('wrapper'); ?>>

                <?php if ($instance['add_name'] == 'true') : ?>
                    <div <?php $this->print_render_attribute_string('field-group_name'); ?>>
                        <?php if ($print_label && $instance['name_label']) : ?>
                            <label <?php $this->print_render_attribute_string('label_name'); ?>>
                                <?php echo esc_html($instance['name_label']); ?>
                            </label>
                        <?php endif; ?>
                        <input <?php $this->print_render_attribute_string('input_name'); ?>>
                    </div>
                <?php endif; ?>

                <div <?php $this->print_render_attribute_string('field-group_email'); ?>>
                    <?php if ($print_label && $instance['email_label']) : ?>
                        <label <?php $this->print_render_attribute_string('label_email'); ?>>
                            <?php echo esc_html($instance['email_label']); ?>
                        </label>
                    <?php endif; ?>
                    <input <?php $this->print_render_attribute_string('input_email'); ?>>
                </div>

                <?php if ($instance['add_honeypot'] == 'true') : ?>
                    <div <?php $this->print_render_attribute_string('honeypot'); ?>>
                        <label for="form-field-address" class="ui-e-label">
                            <?php echo esc_html__('Address', 'uicore-elements'); ?>
                        </label>
                        <input class="ui-e-field ui-e-h-p" name="ui-e-h-p" id="ui-e-h-p" tabindex="-1" autocomplete="off">
                    </div>
                <?php endif; ?>

                <div <?php $this->print_render_attribute_string('submit-group'); ?>>
                    <button type="submit" <?php $this->print_render_attribute_string('button'); ?>>
                        <span <?php $this->print_render_attribute_string('content-wrapper'); ?>>
                            <?php if (! empty($instance['button_icon']) || ! empty($instance['selected_button_icon'])) : ?>
                                <span <?php $this->print_render_attribute_string('icon-align'); ?>>
                                    <?php Icons_Manager::render_icon($instance['selected_button_icon'], ['aria-hidden' => 'true']); ?>
                                    <?php if (empty($instance['button_text'])) : ?>
                                        <span class="elementor-screen-only"><?php echo esc_html__('Submit', 'uicore-elements'); ?></span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            <?php if (! empty($instance['button_text'])) : ?>
                                <span class="ui-e-text"><?php $this->print_unescaped_setting('button_text'); ?></span>
                            <?php endif; ?>
                        </span>
                    </button>
                </div>

                <?php
                if ($instance['add_recaptcha'] == 'true') :
                    $recaptcha_type = $instance['recaptcha_version'] === 'v2' ? 'recaptcha' : 'recaptcha_v3';
                    $is_recaptcha_required = true;
                ?>
                    <div <?php $this->print_render_attribute_string($recaptcha_type); ?>>
                        <input type="hidden" name="<?php echo esc_attr($recaptcha_type); ?>">
                        <?php if ($instance['recaptcha_version'] == 'v2') : ?>
                            <div id="ui-e-recaptcha-<?php echo esc_attr($this->get_ID()); ?>"></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>

            <div class="ui-e-message <?php echo $this->is_edit_mode() ? 'elementor-hidden' : ''; ?>">
                <?php if ($this->is_edit_mode()) :
                    // Get custom messages if set, else use default
                    $messages = Form_Service::get_default_messages();
                    $success = isset($instance['success_message']) ? $instance['success_message'] : $messages['success'];
                    $error = isset($instance['error_message']) ? $instance['error_message'] : $messages['error'];
                ?>
                    <span class="success"> <?php echo esc_html($success); ?> </span> <br>
                    <span class="error"> <?php echo esc_html($error); ?> </span>
                <?php endif; ?>
            </div>
        </form>
<?php

        //add js to footer if recaptcha is enabled
        if ($is_recaptcha_required) {
            add_action('wp_footer', [$this, 'TRAIT_recaptcha_key_js'], 999);
        }
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new Newsletter());
