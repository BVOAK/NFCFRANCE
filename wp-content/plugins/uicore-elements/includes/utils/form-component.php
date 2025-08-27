<?php

namespace UiCoreElements\Utils;

use UiCoreElements\Helper;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

defined('ABSPATH') || exit();

/**
 * Register some Form Widgets controls and methods.
 */

trait Form_Component
{

    // Submission Controls
    function TRAIT_register_submit_email_controls($instance, $slug = '')
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
    function TRAIT_register_submit_redirect_controls()
    {
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
    }
    function TRAIT_register_submit_popup_controls()
    {
        if (defined('UICORE_ASSETS')) {
            $this->start_controls_section(
                'section_popup',
                [
                    'label' => esc_html__('Popup', 'uicore-elements'),
                    'condition' => [
                        'submit_actions' => 'popup',
                    ],
                ]
            );
            $this->add_control(
                'popup_action',
                [
                    'label' => esc_html__('Action', 'uicore-elements'),
                    'type' => Controls_Manager::SELECT,
                    'default' => '',
                    'options' => [
                        '' => esc_html__('Choose', 'uicore-elements'),
                        'close' => esc_html__('Close Popup', 'uicore-elements'),
                        'open' => esc_html__('Open Popup', 'uicore-elements'),
                    ],
                    'label_block' => true,
                    'render_type' => 'none',
                ]
            );
            $this->add_control(
                'open_popup',
                [
                    'label' => esc_html__('Popup', 'uicore-elements'),
                    'type' => Controls_Manager::SELECT2,
                    'multiple' => false,
                    'options' => Helper::get_uicore_popups(),
                    'label_block' => true,
                    'render_type' => 'none',
                    'condition' => [
                        'popup_action' => 'open',
                    ],
                ]
            );
            $this->add_control(
                'popup_warning',
                [
                    'type' => Controls_Manager::ALERT,
                    'alert_type' => 'warning',
                    'dismissible' => false,
                    'heading' => esc_html__('Popup Visibility', 'uicore-elements'),
                    'content' => esc_html__("Make sure the chosen popup is enabled on the pages where your widget will be shown. Otherwise, this action won't work.", 'uicore-elements'),
                    'condition' => [
                        'popup_action' => 'open',
                    ],
                ]
            );

            $this->end_controls_section();
        }
    }

    /**
     * Register Newsletter Services controls for the submit action.
     *
     * @param $instance - The widget instance. Required if $has_repeaters is true.
     * @param bool $field_mapping - Enable Field Mapping control if the widget let allow form fields management.
     *
     * @return void
     */
    function TRAIT_register_submit_newsletter_services_controls($instance = null, bool $field_mapping = false)
    {

        if (!get_option('uicore_elements_newsletter_service_key')) {
            $this->add_control(
                'services_warning',
                [
                    'type' => Controls_Manager::ALERT,
                    'alert_type' => 'warning',
                    'dismissible' => false,
                    'heading' => esc_html__("You haven't set your Newsletter Service API key yet.", 'uicore-elements'),
                    'content' => Helper::get_admin_settings_url(esc_html__("Click here to configure your API keys.", 'uicore-elements')),
                ]
            );
        }

        $this->add_control(
            'mailchimp_audience_id',
            [
                'label' => esc_html__('ID', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'description' => esc_html__('The ID of the service list, group, audience, or form where contacts will be saved.', 'uicore-elements'),
                'ai' => [
                    'active' => false,
                ],
                'dynamic' => [
                    'active' => true,
                ],
                'label_block' => true,
                'render_type' => 'none',
            ]
        );

        if (! $field_mapping) {
            return;
        }

        $this->add_control(
            'services_map_description',
            [
                'label' => esc_html__('Field Mapping', 'uicore-elements'),
                'type' => Controls_Manager::RAW_HTML,
                'raw' => esc_html__('You need to set the "ID" of the field you want to map.', 'uicore-elements'),
                'content_classes' => 'elementor-control-field-description',
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'mailchimp_fname_id',
            [
                'label' => esc_html__('First Name', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'ai' => [
                    'active' => false,
                ],
                'render_type' => 'none',
            ]
        );
        $this->add_control(
            'mailchimp_lname_id',
            [
                'label' => esc_html__('Last Name', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'ai' => [
                    'active' => false,
                ],
                'render_type' => 'none',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'submit_actions',
                            'operator' => '!contains',
                            'value' => 'moosend',
                        ],
                        [
                            'name' => 'submit_actions',
                            'operator' => '!contains',
                            'value' => 'getresponse',
                        ],
                    ]
                ]
            ]
        );
        $this->add_control(
            'mailchimp_email_id',
            [
                'label' => esc_html__('Email*', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'ai' => [
                    'active' => false,
                ],
                'render_type' => 'none',
            ]
        );
        $this->add_control(
            'mailchimp_birthday_id',
            [
                'label' => esc_html__('Birthday', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'ai' => [
                    'active' => false,
                ],
                'render_type' => 'none',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'submit_actions',
                            'operator' => '!contains',
                            'value' => 'moosend',
                        ],
                        [
                            'name' => 'submit_actions',
                            'operator' => '!contains',
                            'value' => 'getresponse',
                        ],
                        [
                            'name' => 'submit_actions',
                            'operator' => '!contains',
                            'value' => 'mailerlite',
                        ],
                    ]
                ]
            ]
        );
        $this->add_control(
            'mailchimp_phone_id',
            [
                'label' => esc_html__('Phone', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'ai' => [
                    'active' => false,
                ],
                'render_type' => 'none',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'submit_actions',
                            'operator' => '!contains',
                            'value' => 'moosend',
                        ],
                        [
                            'name' => 'submit_actions',
                            'operator' => '!contains',
                            'value' => 'getresponse',
                        ],
                    ]
                ]
            ]
        );

        $this->add_control(
            'services_custom_fields_description',
            [
                'label' => esc_html__('Custom Fields', 'uicore-elements'),
                'type' => Controls_Manager::RAW_HTML,
                'raw' => esc_html__('Set the "ID" of the custom field you want to pass.', 'uicore-elements'),
                'content_classes' => 'elementor-control-field-description',
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'newsletter_service_custom_fields',
            [
                'label' => esc_html__('Custom Fields', 'uicore-elements'),
                'type' => Controls_Manager::REPEATER,
                'prevent_empty' => false,
                'fields' => [
                    [
                        'name' => 'field_name',
                        'label' => esc_html__('API Field Key', 'uicore-elements'),
                        'description' => esc_html__('How the field is called in the newsletter service provider.', 'uicore-elements'),
                        'type' => Controls_Manager::TEXT,
                    ],
                    [
                        'name' => 'field_id',
                        'label' => esc_html__('Form Field ID', 'uicore-elements'),
                        'description' => esc_html__('The correspondent form field ID in the widget data.', 'uicore-elements'),
                        'type' => Controls_Manager::TEXT,
                    ],
                    [
                        'name' => 'field_type',
                        'label' => esc_html__('Sanitization Type', 'uicore-elements'),
                        'description' => esc_html__('Defines which sanitization type this field should receive. E.g.: phone number validation.', 'uicore-elements'),
                        'default' => 'text',
                        'options' => [
                            'text' => esc_html__('Text', 'uicore-elements'),
                            'email' => esc_html__('Email', 'uicore-elements'),
                            'phone' => esc_html__('Phone', 'uicore-elements'),
                            'birthday' => esc_html__('Birthday', 'uicore-elements'),
                            'date' => esc_html__('Date', 'uicore-elements'),
                        ],
                        'type' => Controls_Manager::SELECT,
                    ]
                ],
            ]
        );
    }

    // Settings and Content Controls
    function TRAIT_register_additional_controls($messages)
    {
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
                'default' => $messages['success'],
                'placeholder' => $messages['success'],
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
                'default' => $messages['error'],
                'placeholder' => $messages['error'],
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
                'default' => $messages['mail_error'],
                'placeholder' => $messages['mail_error'],
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
            'redirect_message',
            [
                'label' => esc_html__('Sucessfull Redirect', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => $messages['redirect'],
                'placeholder' => $messages['redirect'],
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
    }
    function TRAIT_register_button_controls(string $submit_text = 'send')
    {
        $this->add_responsive_control(
            'button_width',
            [
                'label' => esc_html__('Column Width', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->TRAIT_get_width_values(),
                'default' => '20',
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
                /* translators: %s: Submit text */
                'default' => esc_html(sprintf('%s', $submit_text), 'uicore-elements'),
                /* translators: %s: Submit text */
                'placeholder' => esc_html(sprintf('%s', $submit_text), 'uicore-elements'),
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
    }

    // Style Controls
    function TRAIT_register_form_style_controls($section = true)
    {
        if ($section) {
            $this->start_controls_section(
                'section_form_style',
                [
                    'label' => esc_html__('Form', 'uicore-elements'),
                    'tab' => Controls_Manager::TAB_STYLE,
                ]
            );
        }
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
        if ($section) {
            $this->end_controls_section();
        }
    }
    function TRAIT_register_fields_style_controls($section = true)
    {
        if ($section) {
            $this->start_controls_section(
                'section_field_style',
                [
                    'label' => esc_html__('Fields', 'uicore-elements'),
                    'tab' => Controls_Manager::TAB_STYLE,
                ]
            );
        }

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
                'selector' => '{{WRAPPER}} .ui-e-field-group .ui-e-field, {{WRAPPER}} .ui-e-field-subgroup label',
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
                    '{{WRAPPER}} .ui-e-field-group .ui-e-field' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .ui-e-field-group:hover .ui-e-field' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .ui-e-field-group .ui-e-field:focus' => 'color: {{VALUE}};',
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
        $this->add_responsive_control(
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
        if ($section) {
            $this->end_controls_section();
        }
    }
    function TRAIT_register_button_style_controls($section = true)
    {
        if ($section) {
            $this->start_controls_section(
                'section_button_style',
                [
                    'label' => esc_html__('Button', 'uicore-elements'),
                    'tab' => Controls_Manager::TAB_STYLE,
                ]
            );
        }

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

        if ($section) {
            $this->end_controls_section();
        }
    }
    function TRAIT_register_messages_style_controls($section = true)
    {
        if ($section) {
            $this->start_controls_section(
                'section_messages_style',
                [
                    'label' => esc_html__('Messages', 'uicore-elements'),
                    'tab' => Controls_Manager::TAB_STYLE,
                ]
            );
        }

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

        if ($section) {
            $this->end_controls_section();
        }
    }
    function TRAIT_register_all_form_style_controls()
    {
        $this->TRAIT_register_form_style_controls();
        $this->TRAIT_register_fields_style_controls();
        $this->TRAIT_register_button_style_controls();
        $this->TRAIT_register_messages_style_controls();
    }

    // Helper and Utility Functions
    function TRAIT_get_width_values()
    {
        return [
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
        ];
    }
    function TRAIT_recaptcha_key_js()
    {
?>
        <script>
            window.uicore_elements_recaptcha = '<?php echo esc_html(get_option('uicore_elements_recaptcha_site_key')); ?>';
        </script>
<?php
    }
}
