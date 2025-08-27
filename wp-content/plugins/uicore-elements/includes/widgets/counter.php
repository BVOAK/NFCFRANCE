<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use UiCoreElements\UiCoreWidget;


defined('ABSPATH') || exit();

/**
 * Counter
 *
 * @author Lucas Marini Falbo <lucas@uicore.co>
 * @since 1.0.0
 */

class Counter extends UiCoreWidget
{

    public function get_name()
    {
        return 'uicore-counter';
    }
    public function get_title()
    {
        return __('Counter', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-counter ui-e-widget';
    }
    public function get_keywords()
    {
        return ['counter', 'count', 'number'];
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_styles()
    {
        return [
            'counter',
            'counter-motion' => [
                'condition' => [
                    'counter_animation' => 'motion',
                ],
            ],
        ];
    }
    public function get_scripts()
    {
        return [
            'counter',
            'odometer' => [
                'condition' => [
                    'counter_animation' => 'odometer',
                ],
            ]
        ];
    }
    protected function register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Counter', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_icon',
            [
                'label' => esc_html__('Show Icon', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'uicore-elements'),
                'label_off' => esc_html__('Hide', 'uicore-elements'),
                'return_value' => 'true',
                'prefix_class' => 'ui-e-counter-media-',
                'render_type'  => 'template',
            ]
        );

        $this->add_control(
            'icon_type',
            [
                'label' => esc_html__('Icon Type', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'icon' => [
                        'title' => esc_html__('Icon', 'uicore-elements'),
                        'icon' => 'eicon-star',
                    ],
                    'image' => [
                        'title' => esc_html__('Image', 'uicore-elements'),
                        'icon' => 'eicon-image-bold',
                    ],

                ],
                'default' => 'icon',
                'toggle' => false,
                'condition' => [
                    'show_icon' => 'true',
                ],
            ]
        );

        $this->add_control(
            'selected_icon',
            [
                'label' => esc_html__('Choose icon', 'uicore-elements'),
                'type' => Controls_Manager::ICONS,
                'condition' => [
                    'show_icon' => 'true',
                    'icon_type'    => 'icon',
                ],
            ]
        );

        $this->add_control(
            'image',
            [
                'label' => esc_html__('Choose Image', 'uicore-elements'),
                'type' => Controls_Manager::MEDIA,
                'condition' => [
                    'show_icon' => 'true',
                    'icon_type'    => 'image',
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'count_start',
            [
                'label' => esc_html__('Starting number', 'uicore-elements'),
                'type' => Controls_Manager::NUMBER,
                'step' => 1,
                'default' => 10,
                'frontend_available' => true,
                'dynamic' => [
                    'active' => true,
                ],
                'condition'    => [
                    'counter_animation!' => ['motion'],
                ],
            ]
        );

        $this->add_control(
            'count_end',
            [
                'label' => esc_html__('Ending number', 'uicore-elements'),
                'type' => Controls_Manager::NUMBER,
                'step' => 1,
                'default' => 120,
                'frontend_available'    => true,
                'dynamic' => [
                    'active' => true,
                ],
                'condition'    => [
                    'counter_animation!' => ['motion'],
                ],
            ]
        );

        $this->add_control(
            'count_number',
            [
                'label' => esc_html__('Number', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '120,00',
                'frontend_available' => true,
                'label_block' => true,
                'dynamic' => [
                    'active' => true,
                ],
                'condition'    => [
                    'counter_animation' => ['motion'],
                ],
            ]
        );

        $this->add_control(
            'counter_html_tag',
            [
                'label' => esc_html__('Number HTML class', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default'    => 'h1',
                'options' => [
                    'h1' => esc_html__('H1', 'uicore-elements'),
                    'h2' => esc_html__('H2', 'uicore-elements'),
                    'h3' => esc_html__('H3', 'uicore-elements'),
                    'h4' => esc_html__('H4', 'uicore-elements'),
                    'h5' => esc_html__('H5', 'uicore-elements'),
                    'h6' => esc_html__('H6', 'uicore-elements'),
                    'p' => esc_html__('p', 'uicore-elements'),
                    'span' => esc_html__('span', 'uicore-elements'),
                ],
            ]
        );

        $this->add_control(
            'content_text',
            [
                'label' => esc_html__('Title', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Awesome number', 'uicore-elements'),
                'placeholder' => esc_html__('Type your title here', 'uicore-elements'),
                'separator'    => 'before',
                'label_block' => true,
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'content_html_tag',
            [
                'label' => esc_html__('Title HTML tag', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default'    => 'p',
                'options' => [
                    'H1' => esc_html__('H1', 'uicore-elements'),
                    'H2' => esc_html__('H2', 'uicore-elements'),
                    'H3' => esc_html__('H3', 'uicore-elements'),
                    'H4' => esc_html__('H4', 'uicore-elements'),
                    'H5' => esc_html__('H5', 'uicore-elements'),
                    'H6' => esc_html__('H6', 'uicore-elements'),
                    'p' => esc_html__('p', 'uicore-elements'),
                    'span' => esc_html__('span', 'uicore-elements'),
                ],
            ]
        );

        $this->add_control(
            'counter_text_inline',
            [
                'label' => esc_html__('Text inline', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('True', 'uicore-elements'),
                'label_off' => esc_html__('False', 'uicore-elements'),
                'return_value' => 'true',
                'prefix_class'    => 'ui-e-inline-',
                'render_type' => 'template',
                'default' => false,
            ]
        );

        $this->add_control(
            'icon_position',
            [
                'label' => esc_html__('Icon position', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'uicore-elements'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'top' => [
                        'title' => esc_html__('Top', 'uicore-elements'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'uicore-elements'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default'     => 'top',
                'toggle'    => false,
                'condition'    => [
                    'show_icon' => 'true',
                    'counter_text_inline!' => 'true',
                ],
                'prefix_class'    => 'ui-e-',
                'render_type' => 'template',
            ]
        );

        $this->add_control(
            'icon_position_inline',
            [
                'label' => esc_html__('Icon position', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'uicore-elements'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'uicore-elements'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default'     => 'left',
                'toggle'    => false,
                'condition'    => [
                    'show_icon' => 'true',
                    'counter_text_inline' => 'true',
                ],
                'prefix_class'    => 'ui-e-',
                'render_type' => 'template',
            ]
        );

        $this->add_control(
            'vertical_align',
            [
                'label' => esc_html__('Vertical position', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Top', 'uicore-elements'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'uicore-elements'),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'end' => [
                        'title' => esc_html__('Bottom', 'uicore-elements'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default'             => 'center',
                'toggle'    => false,
                'selectors' => [
                    '{{WRAPPER}} > div' => 'align-items: {{VALUE}};',
                ],
                'condition' => [
                    'icon_position!'    => ['top'],
                    'counter_text_inline!' => ['true'],
                ],
            ]
        );

        $this->add_control(
            'vertical_align_inline',
            [
                'label' => esc_html__('Vertical position', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Top', 'uicore-elements'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'uicore-elements'),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'end' => [
                        'title' => esc_html__('Bottom', 'uicore-elements'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default'     => 'center',
                'toggle'    => false,
                'selectors' => [
                    '{{WRAPPER}} > div' => 'align-items: {{VALUE}};',
                ],
                'condition' => [
                    'counter_text_inline' => ['true'],
                ],
            ]
        );

        $this->add_control(
            'text_align',
            [
                'label' => esc_html__('Text Align', 'uicore-elements'),
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
                ],
                'toggle' => false,
                'default' => 'center',
                'prefix_class'    => 'ui-e-align-',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-title' => 'text-align: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-ico' => 'text-align: {{VALUE}};',
                ],

            ]
        );

        $this->add_control(
            'icon_offset_toggle',
            [
                'label' => esc_html__('Icon Offset', 'uicore-elements'),
                'type' => Controls_Manager::POPOVER_TOGGLE,
                'label_off' => esc_html__('Default', 'uicore-elements'),
                'label_on' => esc_html__('Custom', 'uicore-elements'),
                'return_value' => 'yes',
                'condition' => [
                    'show_icon'                  => 'true',
                    'counter_text_inline!'     => 'true',
                ],
            ]
        );

        $this->start_popover();

        $this->add_responsive_control(
            'icon_vertical_offset',
            [
                'label' => esc_html__('Vertical offset', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => -100,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'condition' => [
                    'counter_text_inline!'         => 'true',
                    'icon_offset_toggle'    => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-ico-vertical-off: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_horizontal_offset',
            [
                'label' => esc_html__('Horizontal offset', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => -100,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'condition' => [
                    'counter_text_inline!'         => 'true',
                    'icon_offset_toggle'    => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-ico-horizontal-off: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_popover();

        $this->end_controls_section();

        $this->start_controls_section(
            'add_op_section',
            [
                'label' => __('Additional Options', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'use_grouping',
            [
                'label' => esc_html__('Use Grouping', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('True', 'uicore-elements'),
                'label_off' => esc_html__('False', 'uicore-elements'),
                'return_value' => 'true',
                'default' => 'true',
                'frontend_available'    => true,
                'condition'    => [
                    'counter_animation!' => ['motion'],
                ],
            ]
        );

        $this->add_control(
            'counter_separator',
            [
                'label' => esc_html__('Thousand symbol', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__(',', 'uicore-elements'),
                'condition'    => [
                    'use_grouping'    => 'true',
                    'counter_animation!' => ['motion'],
                ],
                'ai' => [
                    'active' => false,
                ],
                'frontend_available'    => true,
            ]
        );

        $this->add_control(
            'decimal_places',
            [
                'label' => esc_html__('Decimal Places', 'uicore-elements'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 4,
                'step' => 1,
                'default' => 0,
                'ai' => [
                    'active' => false,
                ],
                'frontend_available'    => true,
                'condition'        => [
                    'counter_animation!' => ['motion'],
                ],
            ]
        );

        $this->add_control(
            'decimal_symbol',
            [
                'label' => esc_html__('Decimal Symbol', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('.', 'uicore-elements'),
                'condition'    => [
                    'counter_animation!' => ['motion'],
                ],
                'ai' => [
                    'active' => false,
                ],
                'frontend_available'    => true,
            ]
        );

        $this->add_control(
            'counter_prefix',
            [
                'label' => esc_html__('Counter Prefix', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => esc_html__('$', 'uicore-elements'),
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'counter_suffix',
            [
                'label' => esc_html__('Counter Suffix', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => esc_html__('+', 'uicore-elements'),
                'ai' => [
                    'active' => false,
                ],
                'frontend_available'    => true,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'icon_image_section',
            [
                'label' => __('Icon/Image', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_icon'    => 'true',
                ],
            ]
        );

        $this->start_controls_tabs(
            'style_tabs'
        );

        $this->start_controls_tab(
            'icon_colors',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => esc_html__('Icon color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'condition'    => [
                    'icon_type!' => 'image',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-offset > svg' => 'fill:{{VALUE}};',
                    '{{WRAPPER}} .ui-e-offset > i' => 'color: {{VALUE}};',
                ],

            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'icon_background',
                'types' => ['classic', 'gradient', 'video'],
                'selector'    => '{{WRAPPER}} .ui-e-ico .ui-e-offset',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'icon_colors_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'icon_color_hover',
            [
                'label' => esc_html__('Icon color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'condition'    => [
                    'icon_type!' => 'image',
                ],
                'selectors' => [
                    '{{WRAPPER}} > div:hover .ui-e-offset > svg' => 'fill:{{VALUE}};',
                    '{{WRAPPER}} > div:hover .ui-e-offset > i' => 'color: {{VALUE}};',
                ],

            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'icon_background_hover',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} > div:hover .ui-e-offset',

            ]
        );

        $this->add_control(
            'border_color_hover',
            [
                'label' => esc_html__('Border Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} > div:hover .ui-e-offset' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'hr',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        $this->add_responsive_control(
            'icon_padding',
            [
                'label' => esc_html__('Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 25,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-offset' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],

            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'icon_border',
                'selector' => '{{WRAPPER}} .ui-e-offset',

            ]
        );

        $this->add_responsive_control(
            'icon_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'default' => [
                    'unit' => 'px',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-offset' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-top-right-radius:{{RIGHT}}{{UNIT}}; border-bottom-right-radius:{{BOTTOM}}{{UNIT}}; border-bottom-left-radius:{{LEFT}}{{UNIT}};',
                ],

            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'icon_shadow',
                'selector' => '{{WRAPPER}} .ui-e-offset',

            ]
        );

        $this->add_responsive_control(
            'icon_space',
            [
                'label' => esc_html__('Icon Space', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-ico-spacing: {{SIZE}}{{UNIT}}',
                ],

            ]
        );

        $this->add_control(
            'image_fullwidth',
            [
                'label' => esc_html__('Image fullwidth', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('True', 'uicore-elements'),
                'label_off' => esc_html__('False', 'uicore-elements'),
                'return_value' => 'true',
                'default' => 'false',
                'condition'    => [
                    'icon_type' => 'image',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-offset > *' => 'width: 100%;',

                ],
            ]
        );

        $this->add_responsive_control(
            'image_size',
            [
                'label' => esc_html__('Image Size', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 991,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 120,
                ],
                'condition'    => [
                    'icon_type' => 'image',
                    'image_fullwidth!' => 'true',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-offset > *' => 'width: {{SIZE}}{{UNIT}};',

                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => esc_html__('Icon Size', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 160,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 40,
                ],
                'condition'    => [
                    'icon_type' => 'icon',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-icon-size:{{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'counter_number_section',
            [
                'label' => __('Number', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'counter_number_space',
            [
                'label' => esc_html__('Number space', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-num-spacing: {{SIZE}}{{UNIT}};',
                ],

            ]
        );

        $this->start_controls_tabs(
            'counter_tabs'
        );

        $this->start_controls_tab(
            'counter_color_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'counter_number_color',
            [
                'label' => esc_html__('Number Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-num' => 'color: {{VALUE}}',
                    '{{WRAPPER}}' => '--ui-e-num-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'counter_color_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'counter_number_color_hover',
            [
                'label' => esc_html__('Number Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} > div:hover .ui-e-num' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'counter_number_typography',
                'selector' => '{{WRAPPER}} .ui-e-num',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'suffix_prefix_section',
            [
                'label' => __('Suffix/Prefix', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'suffix_prefix_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-prefix, {{WRAPPER}} .ui-e-suffix' => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'suffix_prefix_typography',
                'selector' => '{{WRAPPER}} .ui-e-prefix, {{WRAPPER}} .ui-e-suffix',
            ]
        );

        $this->start_controls_tabs(
            'prefix_suffix_tabs'
        );

        $this->start_controls_tab(
            'prefix_tab',
            [
                'label' => esc_html__('Prefix', 'uicore-elements'),
            ]
        );

        $this->add_responsive_control(
            'prefix_h_offset',
            [
                'label' => esc_html__('Horizontal Offset', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
            ]
        );
        $this->add_responsive_control(
            'prefix_v_offset',
            [
                'label' => esc_html__('Vertical Offset', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => -80,
                        'max' => 80,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-prefix' => 'transform: translate3d( {{prefix_h_offset.SIZE}}{{prefix_h_offset.UNIT}}, {{SIZE}}{{UNIT}}, 0);',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'suffix_tab',
            [
                'label' => esc_html__('Suffix', 'uicore-elements'),
            ]
        );

        $this->add_responsive_control(
            'suffix_h_offset',
            [
                'label' => esc_html__('Horizontal Offset', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
            ]
        );
        $this->add_responsive_control(
            'suffix_v_offset',
            [
                'label' => esc_html__('Vertical Offset', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => -80,
                        'max' => 80,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-suffix' => 'transform: translate3d( {{suffix_h_offset.SIZE}}{{suffix_h_offset.UNIT}}, {{SIZE}}{{UNIT}}, 0);',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'content_text_section',
            [
                'label' => __('Text', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs(
            'content_text_tabs'
        );

        $this->start_controls_tab(
            'content_text_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'content_text_color',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'content_text_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'content_text_color_hover',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} > div:hover .ui-e-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'content_text_typography',
                'selector' => '{{WRAPPER}} .ui-e-title',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'additional_section',
            [
                'label' => __('Additional', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => esc_html__('Content Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 80,
                        'step' => 5,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} > div' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'animation_section',
            [
                'label' => __('Animations', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'counter_animation',
            [
                'label' => esc_html__('Number animation', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'simple',
                'options' => [
                    'simple'     => esc_html__('Simple', 'uicore-elements'),
                    'odometer'  => esc_html__('Odometer', 'uicore-elements'),
                    'motion'     => esc_html__('Motion Blur', 'uicore-elements'),
                ],
                'frontend_available' => true,
                'prefix_class'        => 'ui-e-counter-',
                'render_type' => 'template',
            ]
        );
        $this->add_control(
            'duration',
            [
                'label' => esc_html__('Duration (in seconds)', 'uicore-elements'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0.1,
                'max' => 8.0,
                'step' => 0.1,
                'default' => 2.5,
                'condition' => [
                    'counter_animation!' => 'motion',
                ],
                'frontend_available'    => true,
            ]
        );
        $this->add_control(
            'motion_heading',
            [
                'label' => esc_html__('Motion Durations (in ms)', 'uicore-elements'),
                'type' => Controls_Manager::HEADING,
                'condition' => [
                    'counter_animation' => 'motion',
                ],
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'motion_translate_duration',
            [
                'label' => esc_html__('Numbers translate', 'uicore-elements'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0.1,
                'max' => 8.0,
                'step' => 0.1,
                'default' => 0.32,
                'condition' => [
                    'counter_animation' => 'motion',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-motion-translate: {{VALUE}}s;',
                ],
            ]
        );
        $this->add_control(
            'motion_opacity_duration',
            [
                'label' => esc_html__('Numbers opacity', 'uicore-elements'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0.1,
                'max' => 8.0,
                'step' => 0.1,
                'default' => 0.2,
                'condition' => [
                    'counter_animation' => 'motion',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-motion-opacity: {{VALUE}}s;',
                ],
            ]
        );
        $this->add_control(
            'motion_blur_duration',
            [
                'label' => esc_html__('Blur effect', 'uicore-elements'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0.1,
                'max' => 8.0,
                'step' => 0.1,
                'default' => 0.36,
                'condition' => [
                    'counter_animation' => 'motion',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-motion-blur: {{VALUE}}s;',
                ],
            ]
        );
        $this->add_control(
            'motion_delay',
            [
                'label' => esc_html__('Delay between numbers', 'uicore-elements'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0.1,
                'max' => 8.0,
                'step' => 0.1,
                'default' => 0.2,
                'condition' => [
                    'counter_animation' => 'motion',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-motion-delay: {{VALUE}}s;',
                ],
            ]
        );
        $this->add_control(
            'animate_suffix',
            [
                'label' => esc_html__('Animate suffix', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('True', 'uicore-elements'),
                'label_off' => esc_html__('False', 'uicore-elements'),
                'return_value' => 'true',
                'frontend_available' => true,
                'description' => esc_html__('Add the suffix to the Odometer animation, but removes any suffix custom styling.', 'uicore-elements'),
                'condition'    => [
                    'counter_animation' => ['odometer'],
                ],
            ]
        );

        $this->end_controls_section();
    }
    protected function render()
    {

        $settings = $this->get_settings_for_display();

        $icon_type    = $settings['icon_type'];
        $num_tag    = $settings['counter_html_tag'];
        $txt_tag    = $settings['content_html_tag'];
        $prefix        = $settings['counter_prefix'];
        $suffix        = $settings['counter_suffix'];
        $media         = $settings['show_icon'] ? true : false; // Check if media is enabled,

        if ($media) {
            switch ($icon_type) {
                case 'icon':
                    $icon = $settings['selected_icon'];
                    break;
                case 'image':
                    $icon = $settings['image'];
                    break;
            }
            $icon_width = ($icon_type === 'image' && $this->is_option('image_fullwidth', 'true'))
                ? esc_attr('large')
                : esc_attr('thumbnail');
        }

        $stack     = $settings['counter_text_inline'] ? false : true; // If text inline, no stack wrapper between elements is needed
        $title     = !empty($settings['content_text'])  ?
            '<' . esc_html($txt_tag) . ' class="ui-e-title">' . wp_kses_data($settings['content_text']) . '</' . esc_html($txt_tag) . '>' :
            "<!-- no title -->";
        $html = '<' . esc_html($num_tag) . ' class="ui-e-num">';

        // Motion animation markup
        if ($settings['counter_animation'] == 'motion') {
            $value         = $settings['count_number'];
            $numbers     = str_split($value);

            foreach ($numbers as $n => $value) {
                $index = 'style="--index:' . $n . '"';

                // prefix
                if ($n === 0 && !empty($prefix)) {
                    $html .= '<span class="ui-e-prefix" ' . $index . '>' . esc_html($prefix) . '</span>';
                }

                // number
                $html .= '<span ' . $index . '>' . esc_html($value) . ' </span>';

                // suffix
                if ($n === count($numbers) - 1 && !empty($suffix)) {
                    $html .= '<span class="ui-e-suffix" ' . $index . '>' . esc_html($suffix) . '</span>';
                }
            }

            // Default markup
        } else {
            $html .= '<span class="ui-e-prefix">' . $prefix . '</span>';
            $html .= '<span class="value"> 0 </span>';
            $html .= $settings['animate_suffix'] == 'true' ? '' : '<span class="ui-e-suffix">' . $suffix . '</span>'; // Suffix might be animated by Odometer animation
        }

        $html .= '</' . esc_html($num_tag) . '>';

        // Build the widget HTML
        if ($media) : ?>
            <div class="ui-e-ico">
                <div class="ui-e-offset">
                    <?php if ($icon_type === 'icon') {
                        Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']);
                    } else {
                        echo wp_get_attachment_image($icon['id'], $icon_width);
                    }  ?>
                </div>
            </div>
        <?php endif;

        if ($stack) {
        ?> <div class="ui-e-wrp"> <?php
                                }
                                echo wp_kses_post($html . $title);
                                if ($stack) {
                                    ?> </div> <?php
                                                }
                                            }
                                        }
                                        \Elementor\Plugin::instance()->widgets_manager->register(new Counter());
