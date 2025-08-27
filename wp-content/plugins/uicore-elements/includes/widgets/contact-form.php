<?php

namespace UiCoreElements;

use UiCoreElements\Helper;
use UiCoreElements\Utils\Contact_Form_Service;
use UiCoreElements\Utils\Form_Component;
use UiCoreElements\Utils\Newsletter_Services;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;


defined('ABSPATH') || exit();

/**
 * Contact Form
 *
 * @author Lucas Marini Falbo <lucas@uicore.co>
 * @since 1.0.5
 */

class ContactForm extends UiCoreWidget
{

    use Form_Component;

    public function get_name()
    {
        return 'uicore-contact-form';
    }

    public function get_title()
    {
        return __('Contact Form', 'uicore-elements');
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
        return ['form', 'forms', 'contact', 'mail', 'send', 'field', 'button', 'recaptcha',];
    }
    public function get_styles()
    {
        return ['contact-form'];
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
            ],
            'repeater-custom-key' => [
                'custom_condition' => $this->is_edit_mode()
            ]
        ];
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');;
    }
    function check_recaptcha_version($version)
    {

        if ($this->is_edit_mode()) {
            return true;
        }
        $settings = $this->get_settings_for_display();
        foreach ($settings['form_fields'] as $field) {
            $version_to_check = $version === 'v2' ? 'recaptcha' : 'recaptcha_v3';
            if ($field['field_type'] === $version_to_check) {
                return true;
            }
        }
        return false;
    }

    // Helper Functions
    function get_field_types()
    {
        return [
            'text'      => esc_html__('Text', 'uicore-elements'),
            'email'     => esc_html__('Email', 'uicore-elements'),
            'textarea'  => esc_html__('Textarea', 'uicore-elements'),
            'url'       => esc_html__('URL', 'uicore-elements'),
            'tel'       => esc_html__('Tel', 'uicore-elements'),
            'radio'     => esc_html__('Radio', 'uicore-elements'),
            'select'    => esc_html__('Select', 'uicore-elements'),
            'checkbox'  => esc_html__('Checkbox', 'uicore-elements'),
            'acceptance' => esc_html__('Acceptance', 'uicore-elements'),
            'number'    => esc_html__('Number', 'uicore-elements'),
            'date'      => esc_html__('Date', 'uicore-elements'),
            'time'      => esc_html__('Time', 'uicore-elements'),
            'file'      => esc_html__('File Upload', 'uicore-elements'),
            'password'  => esc_html__('Password', 'uicore-elements'),
            'address'    => esc_html__('Honeypot', 'uicore-elements'),
            'recaptcha' => esc_html__('reCAPTCHA', 'uicore-elements'),
            'recaptcha_v3' => esc_html__('reCAPTCHA V3', 'uicore-elements'),
            'html'      => esc_html__('HTML', 'uicore-elements'),
            'hidden'    => esc_html__('Hidden', 'uicore-elements'),
        ];
    }

    function get_attribute_name($item)
    {
        return "form_fields[{$item['custom_id']}]";
    }
    function get_attribute_id($item)
    {
        return 'form-field-' . $item['custom_id'];
    }
    function add_required_attribute($element, $js_verication = false)
    {

        // Used for elements (eg: checkbox) that aren't natively supported by the browsers validations
        if ($js_verication) {
            $this->add_render_attribute($element, 'data-ui-e-required', 'true');
            return;
        }

        // Default method
        $this->add_render_attribute($element, 'required', 'required');
        $this->add_render_attribute($element, 'aria-required', 'true');
    }

    // Control Render Functions
    function register_submit_email_controls($instance, $slug = '')
    {
        $instance->add_control(
            'email_to' . $slug,
            [
                'label' => esc_html__('To', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => get_option('admin_email'),
                'ai' => [
                    'active' => false,
                ],
                'placeholder' => get_option('admin_email'),
                'label_block' => true,
                'title' => esc_html__('Separate emails with commas', 'uicore-elements'),
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        /* translators: %s: Site title. */
        $default_message = sprintf(html_entity_decode(esc_html__('New message from "%s"', 'uicore-elements')), get_option('blogname'));

        $instance->add_control(
            'email_subject' . $slug,
            [
                'label' => esc_html__('Subject', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => $default_message,
                'ai' => [
                    'active' => false,
                ],
                'placeholder' => $default_message,
                'label_block' => true,
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $instance->add_control(
            'email_content' . $slug,
            [
                'label' => esc_html__('Message', 'uicore-elements'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => '[all-fields]',
                'ai' => [
                    'active' => false,
                ],
                'placeholder' => '[all-fields]',
                'description' => sprintf(
                    /* translators: %s: The [all-fields] shortcode. */
                    esc_html__('By default, all form fields are sent via %s shortcode. To customize sent fields, copy the shortcode that appears inside each field and paste it above.', 'uicore-elements'),
                    '<code>[all-fields]</code>'
                ),
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $site_domain = Helper::get_site_domain();

        $instance->add_control(
            'email_from' . $slug,
            [
                'label' => esc_html__('From Email', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => 'email@' . $site_domain,
                'ai' => [
                    'active' => false,
                ],
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $instance->add_control(
            'email_from_name' . $slug,
            [
                'label' => esc_html__('From Name', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => get_bloginfo('name'),
                'ai' => [
                    'active' => false,
                ],
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $instance->add_control(
            'email_reply_to' . $slug,
            [
                'label' => esc_html__('Reply-To', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'options' => [
                    '' => '',
                ],
                'default' => 'noreply@' . Helper::get_site_domain(),
                'render_type' => 'none',
            ]
        );

        $instance->add_control(
            'email_to_cc' . $slug,
            [
                'label' => esc_html__('Cc', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'ai' => [
                    'active' => false,
                ],
                'title' => esc_html__('Separate emails with commas', 'uicore-elements'),
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $instance->add_control(
            'email_to_bcc' . $slug,
            [
                'label' => esc_html__('Bcc', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'ai' => [
                    'active' => false,
                ],
                'title' => esc_html__('Separate emails with commas', 'uicore-elements'),
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $instance->add_control(
            'form_metadata' . $slug,
            [
                'label' => esc_html__('Meta Data', 'uicore-elements'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'separator' => 'before',
                'default' => [
                    'date',
                    'time',
                    'page_url',
                    'user_agent',
                    'remote_ip',
                ],
                'options' => [
                    'date' => esc_html__('Date', 'uicore-elements'),
                    'time' => esc_html__('Time', 'uicore-elements'),
                    'page_url' => esc_html__('Page URL', 'uicore-elements'),
                    'user_agent' => esc_html__('User Agent', 'uicore-elements'),
                    'remote_ip' => esc_html__('Remote IP', 'uicore-elements'),
                ],
                'render_type' => 'none',
            ]
        );

        $instance->add_control(
            'email_content_type' . $slug,
            [
                'label' => esc_html__('Send As', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'html',
                'render_type' => 'none',
                'options' => [
                    'html' => esc_html__('HTML', 'uicore-elements'),
                    'plain' => esc_html__('Plain', 'uicore-elements'),
                ],
            ]
        );
    }

    // Field Render Functions
    function build_honeypot_field($item, $item_index)
    {
        $this->add_render_attribute('honeypot' . $item_index, [
            'class' => [
                'ui-e-field',
                'ui-e-h-p',
                esc_attr($item['css_classes']),
            ],
            'name' => 'ui-e-h-p',
            'id' => 'ui-e-h-p',
            'autocomplete' => 'off',
        ]);

        if ($item['placeholder']) {
            $this->add_render_attribute('honeypot' . $item_index, 'placeholder', $item['placeholder']);
        }

        if ($item['required']) {
            $this->add_required_attribute('honeypot' . $item_index);
        }

        $value = empty($item['field_value']) ? '' : $item['field_value'];

        return '<input ' . $this->get_render_attribute_string('honeypot' . $item_index) . ' tabindex="-1">' . $value . '</input>';
    }
    function build_textarea_field($item, $item_index)
    {
        $this->add_render_attribute('textarea' . $item_index, [
            'class' => [
                'ui-e-field',
                esc_attr($item['css_classes']),
            ],
            'name' => $this->get_attribute_name($item),
            'id' => $this->get_attribute_id($item),
            'rows' => $item['rows'],
        ]);

        if ($item['placeholder']) {
            $this->add_render_attribute('textarea' . $item_index, 'placeholder', $item['placeholder']);
        }

        if ($item['required']) {
            $this->add_required_attribute('textarea' . $item_index);
        }

        $value = empty($item['field_value']) ? '' : $item['field_value'];

        return '<textarea ' . $this->get_render_attribute_string('textarea' . $item_index) . '>' . $value . '</textarea>';
    }
    function build_select_field($item, $i)
    {
        $this->add_render_attribute(
            [
                'select-wrapper' . $i => [
                    'class' => [
                        'ui-e-field',
                        'ui-e-field-select',
                        'ui-e-field-subgroup',
                        esc_attr($item['css_classes']),
                    ],
                ],
                'select' . $i => [
                    'name' => $this->get_attribute_name($item) . (! empty($item['allow_multiple']) ? '[]' : ''),
                    'id' => $this->get_attribute_id($item),
                    'class' => [
                        'ui-e-field-textual',
                    ],
                ],
            ]
        );

        if ($item['required']) {
            $this->add_required_attribute('select' . $i);
        }

        if ($item['allow_multiple']) {
            $this->add_render_attribute('select' . $i, 'multiple');
            if (! empty($item['select_size'])) {
                $this->add_render_attribute('select' . $i, 'size', $item['select_size']);
            }
        }

        $options = preg_split("/\\r\\n|\\r|\\n/", $item['field_options']);

        if (! $options) {
            return '';
        }

        ob_start();
?>
        <div <?php $this->print_render_attribute_string('select-wrapper' . $i); ?>>
            <select <?php $this->print_render_attribute_string('select' . $i); ?>>
                <?php
                foreach ($options as $key => $option) {
                    $option_id = $item['custom_id'] . $key;
                    $option_value = esc_attr($option);
                    $option_label = esc_html($option);

                    if (false !== strpos($option, '|')) {
                        list($label, $value) = explode('|', $option);
                        $option_value = esc_attr($value);
                        $option_label = esc_html($label);
                    }

                    $this->add_render_attribute($option_id, 'value', $option_value);

                    // Support multiple selected values
                    if (! empty($item['field_value']) && in_array($option_value, explode(',', $item['field_value']))) {
                        $this->add_render_attribute($option_id, 'selected', 'selected');
                    } ?>
                    <option <?php $this->print_render_attribute_string($option_id); ?>><?php
                                                                                        // PHPCS - $option_label is already escaped
                                                                                        echo $option_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                                                        ?></option>
                <?php } ?>
            </select>
        </div>
    <?php

        $select = ob_get_clean();
        return $select;
    }
    function build_radio_checkbox_field($item, $item_index, $type)
    {
        $options = preg_split("/\\r\\n|\\r|\\n/", $item['field_options']);
        $html = '';
        if ($options) {
            $html .= '<div class="ui-e-field-subgroup ' . esc_attr($item['css_classes']) . ' ' . $item['inline_list'] . '">';
            foreach ($options as $key => $option) {
                $element_id = $item['custom_id'] . $key;
                $html_id = $this->get_attribute_id($item) . '-' . $key;
                $option_label = $option;
                $option_value = $option;
                if (false !== strpos($option, '|')) {
                    list($option_label, $option_value) = explode('|', $option);
                }

                $this->add_render_attribute(
                    $element_id,
                    [
                        'type' => $type,
                        'value' => $option_value,
                        'id' => $html_id,
                        'name' => $this->get_attribute_name($item) . (('checkbox' === $type && count($options) > 1) ? '[]' : ''),
                    ]
                );

                if ($item['required']) {
                    $this->add_required_attribute($element_id, true);
                }

                if (! empty($item['field_value']) && $option_value === $item['field_value']) {
                    $this->add_render_attribute($element_id, 'checked', 'checked');
                }

                if ($item['required'] && 'radio' === $type) {
                    $this->add_required_attribute($element_id);
                }

                $html .= '<span class="ui-e-field-option"><input ' . $this->get_render_attribute_string($element_id) . '> <label for="' . $html_id . '">' . $option_label . '</label></span>';
            }
            $html .= '</div>';
        }

        return $html;
    }
    function build_acceptance_field($item, $item_index)
    {
        $text = '';
        $this->add_render_attribute('input' . $item_index, 'class', 'ui-e-acceptance-field');
        $this->add_render_attribute('input' . $item_index, 'type', 'checkbox', true);

        if (! empty($item['acceptance_text'])) {
            $text = '<label for="' . $this->get_attribute_id($item) . '">' . $item['acceptance_text'] . '</label>';
        }

        if (! empty($item['checked_by_default'])) {
            $this->add_render_attribute('input' . $item_index, 'checked', 'checked');
        }

    ?>
        <div class="ui-e-field-subgroup <?php echo esc_attr($item['css_classes']); ?>">
            <input <?php $this->print_render_attribute_string('input' . $item_index); ?>>
            <?php // PHPCS - the variables $text is safe.
            echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        </div>
    <?php
    }
    function form_fields_render_attributes($i, $instance, $item)
    {
        $this->add_render_attribute(
            [
                'field-group' . $i => [
                    'class' => [
                        'ui-e-field-type-' . $item['field_type'],
                        'ui-e-field-group',
                        'elementor-column',
                        'ui-e-field-group-' . $item['custom_id'],
                    ],
                ],
                'input' . $i => [
                    'type' => $item['field_type'],
                    'name' => $this->get_attribute_name($item),
                    'id' => $this->get_attribute_id($item),
                    'class' => [
                        'ui-e-field',
                        empty($item['css_classes']) ? '' : esc_attr($item['css_classes']),
                    ],
                ],
                'label' . $i => [
                    'for' => $this->get_attribute_id($item),
                    'class' => 'ui-e-label',
                ],
            ]
        );

        if (empty($item['width'])) {
            $item['width'] = '100';
        }

        $this->add_render_attribute('field-group' . $i, 'class', 'elementor-col-' . $item['width']);

        if (! empty($item['width_tablet'])) {
            $this->add_render_attribute('field-group' . $i, 'class', 'elementor-md-' . $item['width_tablet']);
        }

        if ($item['allow_multiple']) {
            $this->add_render_attribute('field-group' . $i, 'class', 'ui-e-field-type-' . $item['field_type'] . '-multiple');
        }

        if (! empty($item['width_mobile'])) {
            $this->add_render_attribute('field-group' . $i, 'class', 'elementor-sm-' . $item['width_mobile']);
        }

        // Allow zero as placeholder.
        if (! Utils::is_empty($item['placeholder'])) {
            $this->add_render_attribute('input' . $i, 'placeholder', $item['placeholder']);
        }

        if (! empty($item['field_value'])) {
            $this->add_render_attribute('input' . $i, 'value', $item['field_value']);
        }

        if (! $instance['show_labels']) {
            $this->add_render_attribute('label' . $i, 'class', 'elementor-screen-only');
        }

        if (! empty($item['required'])) {
            $class = '';
            if (! empty($instance['mark_required'])) {
                $class .= ' ui-e-required';
            }
            $this->add_render_attribute('field-group' . $i, 'class', $class);
            $this->add_required_attribute('input' . $i);
        }
    }

    protected function register_controls()
    {

        // Content Controls
        // Repeater Controls used by `form_fields` control
        $repeater = new Repeater();
        $repeater->start_controls_tabs('form_fields_tabs');

        $repeater->start_controls_tab(
            'form_fields_content_tab',
            [
                'label' => esc_html__('Content', 'uicore-elements'),
            ]
        );

        $repeater->add_control(
            'field_type',
            [
                'label' => esc_html__('Type', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_field_types(),
                'default' => 'text',
            ]
        );
        $repeater->add_control(
            'field_label',
            [
                'label' => esc_html__('Label', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $repeater->add_control(
            'placeholder',
            [
                'label' => esc_html__('Placeholder', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'field_type',
                            'operator' => 'in',
                            'value' => [
                                'tel',
                                'text',
                                'email',
                                'textarea',
                                'number',
                                'url',
                                'password',
                            ],
                        ],
                    ],
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $repeater->add_control(
            'required',
            [
                'label' => esc_html__('Required', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'default' => '',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'field_type',
                            'operator' => '!in',
                            'value' => [
                                'recaptcha',
                                'recaptcha_v3',
                                'hidden',
                                'html',
                            ],
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'field_options',
            [
                'label' => esc_html__('Options', 'uicore-elements'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => '',
                'description' => esc_html__('Enter each option in a separate line. To differentiate between label and value, separate them with a pipe char ("|"). For example: First Name|f_name', 'uicore-elements'),
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'field_type',
                            'operator' => 'in',
                            'value' => [
                                'select',
                                'checkbox',
                                'radio',
                            ],
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'allow_multiple',
            [
                'label' => esc_html__('Multiple Selection', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'field_type',
                            'value' => 'select',
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'select_size',
            [
                'label' => esc_html__('Rows', 'uicore-elements'),
                'type' => Controls_Manager::NUMBER,
                'min' => 2,
                'step' => 1,
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'field_type',
                            'value' => 'select',
                        ],
                        [
                            'name' => 'allow_multiple',
                            'value' => 'true',
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'inline_list',
            [
                'label' => esc_html__('Inline List', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'ui-e-subgroup-inline',
                'default' => '',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'field_type',
                            'operator' => 'in',
                            'value' => [
                                'checkbox',
                                'radio',
                            ],
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'field_html',
            [
                'label' => esc_html__('HTML', 'uicore-elements'),
                'type' => Controls_Manager::TEXTAREA,
                'dynamic' => [
                    'active' => true,
                ],
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'field_type',
                            'value' => 'html',
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'acceptance_text',
            [
                'label' => esc_html__('Acceptance Text', 'uicore-elements'),
                'type' => Controls_Manager::TEXTAREA,
                'condition' => [
                    'field_type' => 'acceptance',
                ],
            ],
        );
        $repeater->add_control(
            'checked_by_default',
            [
                'label' => esc_html__('Checked by Default', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'condition' => [
                    'field_type' => 'acceptance',
                ],
            ],
        );
        $repeater->add_responsive_control(
            'width',
            [
                'label' => esc_html__('Column Width', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__('Default', 'uicore-elements'),
                    '100' => '100%',
                    '80' => '80%',
                    '75' => '75%',
                    '70' => '70%',
                    '66' => '66%',
                    '60' => '60%',
                    '50' => '50%',
                    '40' => '40%',
                    '33' => '33%',
                    '30' => '30%',
                    '25' => '25%',
                    '20' => '20%',
                ],
                'default' => '100',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'field_type',
                            'operator' => '!in',
                            'value' => [
                                'hidden',
                                'recaptcha',
                                'recaptcha_v3'
                            ],
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'rows',
            [
                'label' => esc_html__('Rows', 'uicore-elements'),
                'type' => Controls_Manager::NUMBER,
                'default' => 4,
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'field_type',
                            'value' => 'textarea',
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
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
                    'field_type' => 'recaptcha_v3',
                ],
                'selectors' => [
                    '.grecaptcha-badge' => 'visibility: {{VALUE}};',
                ],
            ]
        );
        $recaptcha_keys = (!get_option('uicore_elements_recaptcha_secret_key') && !get_option('uicore_elements_recaptcha_site_key')) ? true : false;  // Check if recaptcha API keys were set
        $repeater->add_control(
            'recaptcha_warning',
            [
                'type' => Controls_Manager::ALERT,
                'alert_type' => 'warning',
                'dismissible' => false,
                'heading' => esc_html__("You haven't set your reCAPTCHA API keys yet.", 'uicore-elements'),
                'content' => Helper::get_admin_settings_url(esc_html__("Click here to configure your API keys.", 'uicore-elements')),
                'condition' => [
                    'field_type' => $recaptcha_keys ? ['recaptcha', 'recaptcha_v3'] : [],
                ]
            ]
        );

        $repeater->end_controls_tab();

        $repeater->start_controls_tab(
            'form_fields_advanced_tab',
            [
                'label' => esc_html__('Advanced', 'uicore-elements'),
                'condition' => [
                    'field_type!' => ['html', 'address'],
                ],
            ]
        );

        $repeater->add_control(
            'field_value',
            [
                'label' => esc_html__('Default Value', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'dynamic' => [
                    'active' => true,
                ],
                'ai' => [
                    'active' => false,
                ],
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'field_type',
                            'operator' => 'in',
                            'value' => [
                                'text',
                                'email',
                                'textarea',
                                'url',
                                'tel',
                                'radio',
                                'select',
                                'number',
                                'date',
                                'time',
                                'hidden',
                            ],
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'custom_id',
            [
                'label' => esc_html__('ID', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'description' => esc_html__('Please make sure the ID is unique and not used elsewhere in this form. This field allows `A-z 0-9` & underscore chars without spaces.', 'uicore-elements'),
                'render_type' => 'none',
                'required' => true,
                'dynamic' => [
                    'active' => true,
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );
        $repeater->add_control(
            'css_classes',
            [
                'label' => esc_html__('Custom Class', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'ai' => [
                    'active' => false,
                ],
                'placeholder' => esc_html__('e.g: my-class', 'uicore-elements'),
            ]
        );
        $shortcode_template = '{{ view.container.settings.get( \'custom_id\' ) }}';
        $repeater->add_control(
            'shortcode',
            [
                'label' => esc_html__('Shortcode', 'uicore-elements'),
                'type' => Controls_Manager::RAW_HTML,
                'classes' => 'forms-field-shortcode',
                'raw' => '<input class="elementor-form-field-shortcode" value=\'[field id="' . $shortcode_template . '"]\' readonly />',
                'label_block' => false,
            ]
        );

        $repeater->end_controls_tab();

        $repeater->end_controls_tabs();

        $this->start_controls_section(
            'section_form_fields',
            [
                'label' => esc_html__('Form Fields', 'uicore-elements'),
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
            'form_fields',
            [
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'custom_id' => 'name',
                        'field_type' => 'text',
                        'field_label' => esc_html__('Name', 'uicore-elements'),
                        'placeholder' => esc_html__('Name', 'uicore-elements'),
                        'width' => '100',
                        'dynamic' => [
                            'active' => true,
                        ],
                    ],
                    [
                        'custom_id' => 'email',
                        'field_type' => 'email',
                        'required' => 'true',
                        'field_label' => esc_html__('Email', 'uicore-elements'),
                        'placeholder' => esc_html__('Email', 'uicore-elements'),
                        'width' => '100',
                    ],
                    [
                        'custom_id' => 'address',
                        'field_type' => 'address',
                        'field_label' => esc_html__('address (honeypot)', 'uicore-elements'),
                        'placeholder' => esc_html__('address', 'uicore-elements'),
                        'width' => '100',
                    ],
                    [
                        'custom_id' => 'message',
                        'field_type' => 'textarea',
                        'field_label' => esc_html__('Message', 'uicore-elements'),
                        'placeholder' => esc_html__('Message', 'uicore-elements'),
                        'width' => '100',
                    ],
                ],
                'title_field' => '{{{ field_label }}}',
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
                'separator' => 'before',
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
            'section_buttons',
            [
                'label' => esc_html__('Button', 'uicore-elements'),
            ]
        );

        $this->add_responsive_control(
            'button_width',
            [
                'label' => esc_html__('Column Width', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__('Default', 'uicore-elements'),
                    '100' => '100%',
                    '80' => '80%',
                    '75' => '75%',
                    '70' => '70%',
                    '66' => '66%',
                    '60' => '60%',
                    '50' => '50%',
                    '40' => '40%',
                    '33' => '33%',
                    '30' => '30%',
                    '25' => '25%',
                    '20' => '20%',
                ],
                'default' => '100',
            ]
        );
        $this->add_responsive_control(
            'button_align',
            [
                'label' => esc_html__('Alignment', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'uicore-elements'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'uicore-elements'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'uicore-elements'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'stretch' => [
                        'title' => esc_html__('Justified', 'uicore-elements'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => 'stretch',
                'prefix_class' => 'ui-e-submit-align-',
            ]
        );
        $this->add_control(
            'button_text',
            [
                'label' => esc_html__('Submit', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Send', 'uicore-elements'),
                'placeholder' => esc_html__('Send', 'uicore-elements'),
                'dynamic' => [
                    'active' => true,
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );
        $this->add_control(
            'selected_button_icon',
            [
                'label' => esc_html__('Icon', 'uicore-elements'),
                'type' => Controls_Manager::ICONS,
                'skin' => 'inline',
                'label_block' => false,
            ]
        );
        $this->add_control(
            'button_icon_align',
            [
                'label' => esc_html__('Icon Position', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'left',
                'options' => [
                    'left' => esc_html__('Before', 'uicore-elements'),
                    'right' => esc_html__('After', 'uicore-elements'),
                ],
                'condition' => [
                    'selected_button_icon[value]!' => '',
                ],
            ]
        );
        $this->add_control(
            'button_icon_indent',
            [
                'label' => esc_html__('Icon Spacing', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                    'em' => [
                        'max' => 10,
                    ],
                    'rem' => [
                        'max' => 10,
                    ],
                ],
                'condition' => [
                    'selected_button_icon[value]!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button .ui-e-align-right' => 'margin-left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-button .ui-e-align-left' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'button_css_id',
            [
                'label' => esc_html__('Button ID', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'ai' => [
                    'active' => false,
                ],
                'title' => esc_html__('Add your custom id WITHOUT the Pound key. e.g: my-id', 'uicore-elements'),
                'description' => esc_html__('Please make sure the ID is unique and not used elsewhere on the page this form is displayed. This field allows `A-z 0-9` & underscore chars without spaces.', 'uicore-elements'),
                'separator' => 'before',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );


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
                'options' => Contact_Form_Service::get_form_submit_options(),
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

        $this->register_submit_email_controls($this);

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

        $this->register_submit_email_controls($this, '_2');

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

        $this->add_control(
            'redirect_to',
            [
                'label' => esc_html__('Redirect To', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__('https://your-link.com', 'uicore-elements'),
                'ai' => [
                    'active' => false,
                ],
                'dynamic' => [
                    'active' => true,
                    'categories' => [
                        'url',
                    ],
                ],
                'label_block' => true,
                'render_type' => 'none',
            ]
        );

        $this->end_controls_section();

        $this->TRAIT_register_submit_popup_controls($this);

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

        $this->TRAIT_register_submit_newsletter_services_controls($this, true);

        $this->end_controls_section();

        $default_messages = Contact_Form_Service::get_default_messages();
        $this->start_controls_section(
            'section_form_options',
            [
                'label' => esc_html__('Additional Options', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'form_id',
            [
                'label' => esc_html__('Form ID', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'ai' => [
                    'active' => false,
                ],
                'placeholder' => 'new_form_id',
                'description' => esc_html__('Please make sure the ID is unique and not used elsewhere on the page this form is displayed. This field allows `A-z 0-9` & underscore chars without spaces.', 'uicore-elements'),
                'separator' => 'after',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $this->add_control(
            'form_validation',
            [
                'label' => esc_html__('Form Validation', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__('Browser Default', 'uicore-elements'),
                    'no' => esc_html__('No validation', 'uicore-elements'),
                ],
                'default' => '',
            ]
        );
        $this->add_control(
            'custom_messages',
            [
                'label' => esc_html__('Custom Messages', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'separator' => 'before',
                'render_type' => 'none',
            ]
        );
        $this->add_control(
            'success_message',
            [
                'label' => esc_html__('Success Message', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => $default_messages['success'],
                'placeholder' => $default_messages['success'],
                'label_block' => true,
                'condition' => [
                    'custom_messages!' => '',
                ],
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $this->add_control(
            'error_message',
            [
                'label' => esc_html__('Form Error', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => $default_messages['error'],
                'placeholder' => $default_messages['error'],
                'label_block' => true,
                'condition' => [
                    'custom_messages!' => '',
                ],
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $this->add_control(
            'mail_error_message',
            [
                'label' => esc_html__('Email Sending Error', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => $default_messages['mail_error'],
                'placeholder' => $default_messages['mail_error'],
                'label_block' => true,
                'condition' => [
                    'custom_messages!' => '',
                ],
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $this->add_control(
            'required_fields_message',
            [
                'label' => esc_html__('Required Fields', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => $default_messages['required'],
                'placeholder' => $default_messages['required'],
                'label_block' => true,
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'custom_messages',
                            'operator' => '!=',
                            'value' => '',
                        ],
                    ],
                ],
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $this->add_control(
            'redirect_message',
            [
                'label' => esc_html__('Sucessfull Redirect', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => $default_messages['redirect'],
                'placeholder' => $default_messages['redirect'],
                'label_block' => true,
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'custom_messages',
                            'operator' => '!=',
                            'value' => '',
                        ],
                        [
                            'name' => 'submit_actions',
                            'operator' => 'contains',
                            'value' => 'redirect',
                        ]
                    ],
                ],
                'render_type' => 'none',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_form_style',
            [
                'label' => esc_html__('Form', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'column_gap',
            [
                'label' => esc_html__('Columns Gap', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'default' => [
                    'size' => 10,
                ],
                'range' => [
                    'px' => [
                        'max' => 60,
                    ],
                    'em' => [
                        'max' => 6,
                    ],
                    'rem' => [
                        'max' => 6,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group' => 'padding-right: calc( {{SIZE}}{{UNIT}}/2 ); padding-left: calc( {{SIZE}}{{UNIT}}/2 );',
                    '{{WRAPPER}} .ui-e-fields-wrp' => 'margin-left: calc( -{{SIZE}}{{UNIT}}/2 ); margin-right: calc( -{{SIZE}}{{UNIT}}/2 );',
                ],
            ]
        );
        $this->add_control(
            'row_gap',
            [
                'label' => esc_html__('Rows Gap', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'default' => [
                    'size' => 10,
                ],
                'range' => [
                    'px' => [
                        'max' => 60,
                    ],
                    'em' => [
                        'max' => 6,
                    ],
                    'rem' => [
                        'max' => 6,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .ui-e-fields-wrp' => 'margin-bottom: -{{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'heading_label',
            [
                'label' => esc_html__('Label', 'uicore-elements'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'label_spacing',
            [
                'label' => esc_html__('Spacing', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'max' => 60,
                    ],
                    'em' => [
                        'max' => 6,
                    ],
                    'rem' => [
                        'max' => 6,
                    ],
                ],
                'selectors' => [
                    'body {{WRAPPER}} .ui-e-field-group > label' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'label_color',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group > label, {{WRAPPER}} .ui-e-field-subgroup label' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
            ]
        );
        $this->add_control(
            'mark_required_color',
            [
                'label' => esc_html__('Mark Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-required .ui-e-label:after' => 'color: {{COLOR}};',
                ],
                'condition' => [
                    'mark_required' => 'yes',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .ui-e-field-group > label',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
            ]
        );
        $this->add_control(
            'heading_html',
            [
                'label' => esc_html__('HTML Field', 'uicore-elements'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'html_spacing',
            [
                'label' => esc_html__('Spacing', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'max' => 60,
                    ],
                    'em' => [
                        'max' => 6,
                    ],
                    'rem' => [
                        'max' => 6,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-type-html' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'html_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-type-html' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'html_typography',
                'selector' => '{{WRAPPER}} .ui-e-field-type-html',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_field_style',
            [
                'label' => esc_html__('Fields', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs(
            'field_style_tabs'
        );

        $this->start_controls_tab(
            'field_normal_tab',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'field_typography',
                'selector' => '{{WRAPPER}} .ui-e-field-group .ui-e-field, {{WRAPPER}} .ui-e-field-subgroup label, {{WRAPPER}} .ui-e-field-group .ui-e-field-select select',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
            ]
        );
        $this->add_control(
            'field_text_color',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group .ui-e-field, {{WRAPPER}} .ui-e-field-group .ui-e-field-select select' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
            ]
        );
        $this->add_control(
            'field_placeholder_color',
            [
                'label' => esc_html__('Placeholder Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group .ui-e-field::placeholder' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
            ]
        );
        $this->add_control(
            'field_background_color',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group:not(.ui-e-field-type-file) .ui-e-field:not(.ui-e-field-select)' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-field-group .ui-e-field-select select' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'field_border',
                'selector' =>
                '{{WRAPPER}} .ui-e-field-group:not(.ui-e-field-type-file) .ui-e-field:not(.ui-e-field-select),
								 {{WRAPPER}} .ui-e-field-group .ui-e-field-select select',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'field_hover_tab',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'field_text_hover_color',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group:hover .ui-e-field, {{WRAPPER}} .ui-e-field-group:hover .ui-e-field-select select' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
            ]
        );
        $this->add_control(
            'field_placeholder_hover_color',
            [
                'label' => esc_html__('Placeholder Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group:hover .ui-e-field::placeholder' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
            ]
        );
        $this->add_control(
            'field_background_hover_color',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group:hover:not(.ui-e-field-type-file) .ui-e-field:not(.ui-e-field-select)' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-field-group:hover .ui-e-field-select select' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'field_hover_border',
                'selector' =>
                '{{WRAPPER}} .ui-e-field-group:hover:not(.ui-e-field-type-file) .ui-e-field:not(.ui-e-field-select),
								 {{WRAPPER}} .ui-e-field-group:hover .ui-e-field-select select',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'field_active_tab',
            [
                'label' => esc_html__('Active', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'field_text_active_color',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group .ui-e-field:focus, {{WRAPPER}} .ui-e-field-group .ui-e-field-select select:focus' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
            ]
        );
        $this->add_control(
            'field_background_active_color',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group:not(.ui-e-field-type-file) .ui-e-field:focus:not(.ui-e-field-select)' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-field-group .ui-e-field-select:focus select' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'field_active_border',
                'selector' =>
                '{{WRAPPER}} .ui-e-field-group:not(.ui-e-field-type-file) .ui-e-field:focus:not(.ui-e-field-select),
								 {{WRAPPER}} .ui-e-field-group .ui-e-field-select:focus select',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'field_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group:not(.ui-e-field-type-file) .ui-e-field:not(.ui-e-field-select)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .ui-e-field-group .ui-e-field-select select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before'
            ]
        );
        $this->add_control(
            'field_padding',
            [
                'label' => esc_html__('Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-field-group:not(.ui-e-field-type-file) .ui-e-field:not(.ui-e-field-select)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .ui-e-field-group .ui-e-field-select select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_button_style',
            [
                'label' => esc_html__('Button', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_ACCENT,
                ],
                'selector' => '{{WRAPPER}} .elementor-button',
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .elementor-button',
                'exclude' => [
                    'color',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_button_style');

        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_ACCENT,
                ],
                'selectors' => [
                    '{{WRAPPER}} .e-form__buttons__wrapper__button-next' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button[type="submit"]' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_text_color',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .e-form__buttons__wrapper__button-next' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button[type="submit"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button[type="submit"] svg *' => 'fill: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_border_color',
            [
                'label' => esc_html__('Border Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .e-form__buttons__wrapper__button-next' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button[type="submit"]' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'button_border_border!' => '',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'button_background_hover_color',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .e-form__buttons__wrapper__button-next:hover' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button[type="submit"]:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_hover_color',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .e-form__buttons__wrapper__button-next:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button[type="submit"]:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button[type="submit"]:hover svg *' => 'fill: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_hover_border_color',
            [
                'label' => esc_html__('Border Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .e-form__buttons__wrapper__button-next:hover' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button[type="submit"]:hover' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'button_border_border!' => '',
                ],
            ]
        );
        $this->add_control(
            'hover_transition_duration',
            [
                'label' => esc_html__('Transition Duration', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['s', 'ms', 'custom'],
                'default' => [
                    'unit' => 'ms',
                ],
                'selectors' => [
                    '{{WRAPPER}} .e-form__buttons__wrapper__button-previous' => 'transition-duration: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .e-form__buttons__wrapper__button-next' => 'transition-duration: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-button[type="submit"] svg *' => 'transition-duration: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-button[type="submit"]' => 'transition-duration: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'button_hover_animation',
            [
                'label' => esc_html__('Animation', 'uicore-elements'),
                'type' => Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'button_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'button_text_padding',
            [
                'label' => esc_html__('Text Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_messages_style',
            [
                'label' => esc_html__('Messages', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'show_messages',
            [
                'label' => esc_html__('Show/Hide messages', 'uicore-elements'),
                'type' => Controls_Manager::BUTTON,
                'separator' => 'before',
                'text' => esc_html__('Toggle', 'uicore-elements'),
                'event' => 'ui-e-form-show-messages',
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'message_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .ui-e-message',
            ]
        );
        $this->add_control(
            'success_message_color',
            [
                'label' => esc_html__('Success Message Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '#4CAF50',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-message span.success' => 'color: {{COLOR}};',
                ],
            ]
        );
        $this->add_control(
            'error_message_color',
            [
                'label' => esc_html__('Error Message Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '#F44336',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-message span.error' => 'color: {{COLOR}};',
                ],
            ]
        );

        $this->end_controls_section();
    }
    function recaptcha_js()
    {
    ?>
        <script>
            window.uicore_elements_recaptcha = '<?php echo esc_html(get_option('uicore_elements_recaptcha_site_key')); ?>';
        </script>
    <?php
    }

    protected function render()
    {

        $instance = $this->get_settings_for_display();
        $is_recaptcha_required = false;

        // Save widget settings so form API can retrieve them
        set_transient('ui_e_form_widget_settings_' . $this->get_id(), $instance, DAY_IN_SECONDS);

        // Render Form Atts
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

        $referer_title = trim(wp_title('', false));
        if (! $referer_title && is_home()) {
            $referer_title = get_option('blogname');
        }

        // Get default messages
        $messages = Contact_Form_Service::get_default_messages();

        // Set frontend validation message
        $front_msg_required = isset($instance['required_fields_message'])
            ? $instance['required_fields_message']
            : $messages['required'];
    ?>

        <form class="ui-e-form" method="post" <?php $this->print_render_attribute_string('form'); ?>>

            <input type="hidden" name="post_id" value="<?php echo esc_attr(get_the_ID()); ?>" />
            <input type="hidden" name="widget_type" value="contact-form">

            <input type="hidden" name="required_fields_message" value="<?php echo esc_html($front_msg_required); ?>">

            <div <?php $this->print_render_attribute_string('wrapper'); ?>>

                <?php foreach ($instance['form_fields'] as $item_index => $item) :
                    $this->form_fields_render_attributes($item_index, $instance, $item);

                    $field_type = $item['field_type'];

                    if ($field_type === 'recaptcha' || $field_type === 'recaptcha_v3') {
                        $is_recaptcha_required = true;
                    }

                    $print_label = ! in_array($item['field_type'], ['hidden', 'html'], true);
                ?>

                    <div <?php $this->print_render_attribute_string('field-group' . $item_index); ?>>
                        <?php
                        // Print label (excepts if recaptcha)
                        if ($print_label && $item['field_label'] && $item['field_type'] !== 'recaptcha' && $item['field_type'] !== 'recaptcha_v3') {
                        ?>
                            <label <?php $this->print_render_attribute_string('label' . $item_index); ?>>
                                <?php // PHPCS - the variable $item['field_label'] is safe.
                                echo $item['field_label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                ?>
                            </label>
                            <?php
                        }

                        // Print field
                        switch ($item['field_type']):
                            case 'html':
                                echo do_shortcode($item['field_html']);
                                break;

                            case 'textarea':
                                // PHPCS - the method build_textarea_field is safe.
                                echo $this->build_textarea_field($item, $item_index); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                break;

                            case 'select':
                                // PHPCS - the method build_select_field is safe.
                                echo $this->build_select_field($item, $item_index); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                break;

                            case 'acceptance':
                                // PHPCS - the method build_select_field is safe.
                                echo $this->build_acceptance_field($item, $item_index); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                break;

                            case 'radio':
                            case 'checkbox':
                                // PHPCS - the method build_radio_checkbox_field is safe.
                                echo $this->build_radio_checkbox_field($item, $item_index, $item['field_type']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                break;

                            case 'address': // is actually honeypot
                                // PHPCS - the method build_honeypot_field is safe.
                                echo $this->build_honeypot_field($item, $item_index); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                break;

                            case 'recaptcha':
                                // get widget ID
                                echo '<input type="hidden" name="recaptcha"/>  <div id="ui-e-recaptcha-' . esc_attr($this->get_ID()) . '"></div>';
                                break;

                            case 'recaptcha_v3':
                                echo '<input type="hidden" name="recaptcha_v3"/>';
                                break;

                            // All other cases are covered by the add_render_atribute()
                            default:
                                $this->add_render_attribute('input' . $item_index, 'class', 'ui-e-field-textual');
                            ?>
                                <input size="1" <?php $this->print_render_attribute_string('input' . $item_index); ?>>
                        <?php
                                break;

                        endswitch;
                        ?>
                    </div>

                <?php endforeach; ?>

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

            </div>

            <div class="ui-e-message <?php echo $this->is_edit_mode() ? 'elementor-hidden' : ''; ?>">
                <?php if ($this->is_edit_mode()) :
                    // Get custom messages if set, else use default
                    $success = isset($instance['success_message'])
                        ? $instance['success_message']
                        : $messages['success'];
                    $error = isset($instance['error_message'])
                        ? $instance['error_message']
                        : $messages['error'];
                ?>
                    <span class="success"> <?php echo esc_html($success); ?> </span> <br>
                    <span class="error"> <?php echo esc_html($error); ?> </span>
                <?php endif; ?>
            </div>
        </form>
        <?php

        //add js to footer if recaptcha is enabled
        if ($is_recaptcha_required) {
            add_action('wp_footer', [$this, 'recaptcha_js'], 999);
        }

        // Add frontend validation messages to js var
        ?>
        <script>
            window.ui_e_form_validation_messages = <?php echo json_encode([
                                                        'required_fields' => esc_html($front_msg_required),
                                                    ]); ?>;
        </script>
<?php
    }

    /*
	protected function content_template() {
		?>
		<#
		view.addRenderAttribute(
			'form',
			{
				'id': settings.form_id,
				'name': settings.form_name,
			}
		);
		if ( 'no' === settings.form_validation ) {
			view.addRenderAttribute( 'form', 'novalidate' );
		}
		#>
		<form class="ui-e-form" {{{ view.getRenderAttributeString( 'form' ) }}}>
			<div class="ui-e-fields-wrp">
				<#
					for ( var i in settings.form_fields ) {
						var item = settings.form_fields[ i ];
						item.field_type  = _.escape( item.field_type );
						item.field_value = _.escape( item.field_value );

						var options = item.field_options ? item.field_options.split( '\n' ) : [],
							itemClasses = _.escape( item.css_classes ),
							labelVisibility = '',
							placeholder = '',
							required = '',
							checked_by_default = '',
							inputField = '',
							multiple = '',
							fieldGroupClasses = 'ui-e-field-group elementor-column ui-e-field-type-' + item.field_type,
							printLabel = settings.show_labels && ! [ 'hidden', 'html', 'recaptcha', 'recaptcha_v3' ].includes( item.field_type );

						fieldGroupClasses += ' elementor-col-' + ( ( '' !== item.width ) ? item.width : '100' );

						if ( item.width_tablet ) {
							fieldGroupClasses += ' elementor-md-' + item.width_tablet;
						}

						if ( item.width_mobile ) {
							fieldGroupClasses += ' elementor-sm-' + item.width_mobile;
						}

						if ( item.required ) {
							required = 'required';
							fieldGroupClasses += ' ui-e-field-required';

							if ( settings.mark_required ) {
								fieldGroupClasses += ' ui-e-required';
							}
						}

						if ( item.placeholder ) {
							placeholder = 'placeholder="' + _.escape( item.placeholder ) + '"';
						}

						if ( item.allow_multiple ) {
							multiple = ' multiple';
							fieldGroupClasses += ' ui-e-field-type-' + item.field_type + '-multiple';
						}

						switch ( item.field_type ) {
							case 'html':
								inputField = item.field_html;
								break;

							case 'textarea':
								inputField = '<textarea class="ui-e-field ui-e-field-textual elementor-size-' + settings.input_size + ' ' + itemClasses + '" name="form_field_' + i + '" id="form_field_' + i + '" rows="' + item.rows + '" ' + required + ' ' + placeholder + '>' + item.field_value + '</textarea>';
								break;

							case 'select':
								if ( options ) {
									var size = '';
									if ( item.allow_multiple && item.select_size ) {
										size = ' size="' + item.select_size + '"';
									}
									inputField = '<div class="ui-e-field ui-e-field-select ui-e-field-subgroup' + itemClasses + '">';
									inputField += '<select class="ui-e-field-textual" name="form_field_' + i + '" id="form_field_' + i + '" ' + required + multiple + size + ' >';
									for ( var x in options ) {
										var option_value = options[ x ];
										var option_label = options[ x ];
										var option_id = 'form_field_option' + i + x;

										if ( options[ x ].indexOf( '|' ) > -1 ) {
											var label_value = options[ x ].split( '|' );
											option_label = label_value[0];
											option_value = label_value[1];
										}

										view.addRenderAttribute( option_id, 'value', option_value );
										if ( item.field_value.split( ',' ) .indexOf( option_value ) ) {
											view.addRenderAttribute( option_id, 'selected', 'selected' );
										}
										inputField += '<option ' + view.getRenderAttributeString( option_id ) + '>' + option_label + '</option>';
									}
									inputField += '</select></div>';
								}
								break;

							case 'radio':
							case 'checkbox':
								if ( options ) {
									var multiple = '';

									if ( 'checkbox' === item.field_type && options.length > 1 ) {
										multiple = '[]';
									}

									inputField = '<div class="ui-e-field-subgroup ' + itemClasses + ' ' + _.escape( item.inline_list ) + '">';

									for ( var x in options ) {
										var option_value = options[ x ];
										var option_label = options[ x ];
										var option_id = 'form_field_' + item.field_type + i + x;
										if ( options[x].indexOf( '|' ) > -1 ) {
											var label_value = options[x].split( '|' );
											option_label = label_value[0];
											option_value = label_value[1];
										}

										view.addRenderAttribute( option_id, {
											value: option_value,
											type: item.field_type,
											id: 'form_field_' + i + '-' + x,
											name: 'form_field_' + i + multiple
										} );

										if ( option_value ===  item.field_value ) {
											view.addRenderAttribute( option_id, 'checked', 'checked' );
										}

										inputField += '<span class="ui-e-field-option"><input ' + view.getRenderAttributeString( option_id ) + ' ' + required + '> ';
										inputField += '<label for="form_field_' + i + '-' + x + '">' + option_label + '</label></span>';

									}

									inputField += '</div>';
								}
								break;

							case 'acceptance' :
								var checked = '';
								if(item.checked_by_default == 'yes') {
									checked = ' checked';
								}
								inputField += '<div class="ui-e-field-subgroup">';
								inputField += '<input type="checkbox" class="ui-e-field ui-e-acceptance-field" name="form_field_' + i + '" id="form_field_' + i + '" ' + required + checked + '>';
								inputField += '<label for="form_field_' + i + '">' + item.acceptance_text + '</label>';
								inputField += '</div>';
								break;

                            case 'recaptcha' :
                            case 'recaptcha_v3' :
                                inputField = '<input type="hidden" name="recaptcha"/> <div id="ui-e-recaptcha"></div>';
                                break;

							default:
								itemClasses = 'ui-e-field-textual ' + itemClasses;
								inputField = '<input size="1" type="' + item.field_type + '" value="' + item.field_value + '" class="ui-e-field elementor-size-' + settings.input_size + ' ' + itemClasses + '" name="form_field_' + i + '" id="form_field_' + i + '" ' + required + ' ' + placeholder + ' >';
								break;
						}

						if ( inputField ) {
							#>
							<div class="{{ fieldGroupClasses }}">

								<# if ( printLabel && item.field_label ) { #>
									<label class="ui-e-field-label" for="form_field_{{ i }}" {{{ labelVisibility }}}>{{{ item.field_label }}}</label>
								<# } #>

								{{{ inputField }}}
							</div>
							<#
						}
					}


					var buttonClasses = 'ui-e-field-group elementor-column ui-e-field-type-submit e-form__buttons';

					buttonClasses += ' elementor-col-' + ( ( '' !== settings.button_width ) ? settings.button_width : '100' );

					if ( settings.button_width_tablet ) {
						buttonClasses += ' elementor-md-' + settings.button_width_tablet;
					}

					if ( settings.button_width_mobile ) {
						buttonClasses += ' elementor-sm-' + settings.button_width_mobile;
					}

					var iconHTML = elementor.helpers.renderIcon( view, settings.selected_button_icon, { 'aria-hidden': true }, 'i' , 'object' )
					#>

					<div class="{{ buttonClasses }}">
						<button id="{{ settings.button_css_id }}" type="submit" class="elementor-button elementor-animation-{{ settings.button_hover_animation }}">
							<span>
								<# if ( settings.button_icon || settings.selected_button_icon ) { #>
									<span class="ui-e-icon ui-e-align-{{ settings.button_icon_align }}">
										<# if ( iconHTML && iconHTML.rendered && ( ! settings.button_icon ) ) { #>
											{{{ iconHTML.value }}}
										<# } else { #>
											<i class="{{ settings.button_icon }}" aria-hidden="true"></i>
										<# } #>
										<span class="elementor-screen-only"><?php echo esc_html__( 'Submit', 'uicore-elements' ); ?></span>
									</span>
								<# } #>

								<# if ( settings.button_text ) { #>
									<span class="ui-e-text">{{{ settings.button_text }}}</span>
								<# } #>
							</span>
						</button>
					</div>
			</div>
            <div class="ui-e-message elementor-hidden">
                <#
                    const success = settings.success_message;
                    const error = settings.error_message;
                #>
                <span class="success">{{{ success }}}</span> <br>
                <span class="error">{{{ error }}}</span>
            </div>
		</form>
		<?php
	}
    */
}
\Elementor\Plugin::instance()->widgets_manager->register(new ContactForm());
