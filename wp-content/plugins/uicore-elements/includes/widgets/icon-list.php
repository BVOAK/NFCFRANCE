<?php

namespace UiCoreElements;

use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use UiCoreElements\UiCoreWidget;
use UiCoreELements\Helper;

defined('ABSPATH') || exit();

/**
 * Icon List
 *
 * @author Lucas Marini Falbo <lucas95@uicore.co>
 * @since 1.0.0
 */

class IconList extends UiCoreWidget
{

    public function get_name()
    {
        return 'uicore-icon-list';
    }
    public function get_title()
    {
        return esc_html__('Icon List', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-post-list ui-e-widget';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_styles()
    {
        return ['icon-list'];
    }
    public function get_scripts()
    {
        return [];
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }
    public function get_keywords()
    {
        return ['icon', 'list', 'uicore'];
    }
    protected function register_controls()
    {

        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Items', 'uicore-elements'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'text',
            [
                'label' => esc_html__('Title', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => esc_html__('List Item', 'uicore-elements'),
                'default' => esc_html__('List Item', 'uicore-elements'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $repeater->add_control(
            'text_details',
            [
                'label' => esc_html__('Subtitle', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => esc_html__('Subtitle', 'uicore-elements'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $repeater->add_control(
            'list_icon',
            [
                'label' => esc_html__('Icon', 'uicore-elements'),
                'type' => Controls_Manager::ICONS,
                'label_block' => false,
                'skin' => 'inline',
                'default' => [
                    'value' => 'fas fa-check',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $repeater->add_control(
            'img',
            [
                'label' => esc_html__('Image', 'uicore-elements'),
                'type' => Controls_Manager::MEDIA,
                'dynamic' => [
                    'active' => true,
                ],
                'label_block' => false,

            ]
        );

        $repeater->add_control(
            'link',
            [
                'label' => esc_html__('Link', 'uicore-elements'),
                'type' => Controls_Manager::URL,
                'options' => ['url', 'is_external', 'nofollow'],
                'default' => [
                    'url' => '',
                    'is_external' => false,
                    'nofollow' => false,
                ],
                'dynamic' => [
                    'active' => true,
                ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'icon_list',
            [
                'label' => '',
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'text' => esc_html__('List Item #1', 'uicore-elements'),
                    ],
                    [
                        'text' => esc_html__('List Item #2', 'uicore-elements'),
                    ],
                    [
                        'text' => esc_html__('List Item #3', 'uicore-elements'),
                    ],
                ],
                'title_field' => '{{{ elementor.helpers.renderIcon( this, list_icon, {}, "i", "panel" ) || \'<i class="{{ icon }}" aria-hidden="true"></i>\' }}} {{{ text }}}',
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label'          => esc_html__('Columns', 'uicore-elements'),
                'type'           => Controls_Manager::SELECT,
                'default'        => '1',
                'tablet_default' => '1',
                'mobile_default' => '1',
                'options'        => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} ul' => 'grid-template-columns: repeat({{SIZE}}, 1fr);',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'list_item_space_between',
            [
                'label' => esc_html__('Grid Gap', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-grid-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'show_number_icon',
            [
                'label' => esc_html__('Show Number Count', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
            ]
        );

        $this->add_control(
            'vertical_alignment_number',
            [
                'label' => esc_html__('Number Vertical Alignment', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'toggle' => false,
                'default' => 'center',
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
                'condition' => [
                    'show_number_icon' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-number' => 'align-self: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'connector_line',
            [
                'label' => esc_html__('Connector Line', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'prefix_class' => 'ui-e-connector-line-'
            ]
        );

        $this->add_control(
            'title_tags',
            [
                'label'   => esc_html__('Title HTML Tag', 'uicore-elements'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'span',
                'options' => Helper::get_title_tags(),
            ]
        );

        $this->add_responsive_control(
            'icon_position',
            [
                'label' => esc_html__('Icon Position', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'toggle' => false,
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
                'default' => 'left',
                'prefix_class' => 'ui-e-icon-',
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'vertical_alignment',
            [
                'label' => esc_html__('Icon Vertical Alignment', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'toggle' => false,
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
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon' => 'align-self: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'vertical_alignment_offset',
            [
                'label' => esc_html__('Vertical Alignment Offset', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => -25,
                        'max' => 25,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'size' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon' => 'transform: translate3d(0,{{SIZE}}{{UNIT}},0);',
                ],
            ]
        );

        $this->add_control(
            'content_position',
            [
                'label' => esc_html__('Content Position', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'toggle' => false,
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
                'default' => is_rtl() ? 'right' : 'left',
                'prefix_class' => 'ui-e-',
            ]
        );

        $this->add_responsive_control(
            'list_item_align',
            [
                'label' => esc_html__('Alignment', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'toggle' => false,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'uicore-elements'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'uicore-elements'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'uicore-elements'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => is_rtl() ? 'right' : 'left',
                'prefix_class' => 'elementor%s-align-',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_list_items',
            [
                'label' => esc_html__('List Item', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs(
            'list_item_tabs'
        );

        $this->start_controls_tab(
            'list_item_tabs_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'list_item_bg_color',
                'selector'  => '{{WRAPPER}} .ui-e-wrap',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'list_item_border',
                'label' => esc_html__('Border', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-wrap',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'list_item_box_shadow',
                'label' => esc_html__('Box Shadow', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-wrap',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'list_item_tabs_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'list_item_hover_bg_color',
                'selector'  => '{{WRAPPER}} .ui-e-wrap:hover',
            ]
        );

        $this->add_control(
            'list_item_hover_border',
            [
                'label'     => esc_html__('Border Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-wrap:hover' => 'border-color: {{VALUE}} !important',
                ],
                'condition' => [
                    'list_item_border_border!' => ''
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'list_item_box_shadow_hover',
                'label' => esc_html__('Box Shadow', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-wrap:hover',
            ]
        );

        $this->add_responsive_control(
            'list_item_transition',
            [
                'label' => esc_html__('Transition', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['s'],
                'range' => [
                    's' => [
                        'min' => 0.01,
                        'max' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 's',
                    'size' => 0.2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-wrap' => '--ui-e-transition: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'hr',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_responsive_control(
            'list_item_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_responsive_control(
            'list_item_padding',
            [
                'label' => esc_html__('Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} ',
                ],
            ]
        );

        $this->add_responsive_control(
            'list_item_height',
            [
                'label' => esc_html__('Height', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-wrap' => 'min-height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'list_item_spacing',
            [
                'label' => esc_html__('Space between elements', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 80,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-wrap' => 'gap: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_icon',
            [
                'label' => esc_html__('Number Count', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_number_icon' => 'yes',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_number_icon_style');

        $this->start_controls_tab(
            'tab_number_icon_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'number_icon_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-number span' => 'color: {{VALUE}} ',
                ],
            ]
        );

        $this->add_control(
            'icon_bg_color',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-number' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'icon_number_border',
                'label' => esc_html__('Border', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-number',
            ]
        );

        $this->add_responsive_control(
            'icon_number_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-number' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} ',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_number_padding',
            [
                'label'      => esc_html__('Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-number' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_number_margin',
            [
                'label'      => esc_html__('Margin', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-number' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'number_icon_typography',
                'selector' => '{{WRAPPER}} .ui-e-number span',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_number_icon_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'number_icon_color_hover',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ul li:hover .ui-e-number span' => 'color: {{VALUE}} ',
                ],
            ]
        );

        $this->add_control(
            'number_icon_bg_color_hover',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ul li:hover .ui-e-number' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'number_icon_border_color_hover',
            [
                'label' => esc_html__('Border Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ul li:hover .ui-e-number' => 'border-color: {{VALUE}}',
                ],
                'condition' => [
                    'icon_number_border_border!' => ''
                ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_text_style',
            [
                'label' => esc_html__('Title / Subtitle', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('tabs_mode_style');
        $this->start_controls_tab(
            'tab_normal_mode_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_heading',
            [
                'label' => esc_html__('Title', 'uicore-elements'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-title ' => 'color: {{VALUE}} ',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .ui-e-title',
            ]
        );

        $this->add_control(
            'subtitle_heading',
            [
                'label' => esc_html__('Subtitle', 'uicore-elements'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'subtitle_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-text' => 'color: {{VALUE}} ',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sub_title_typography',
                'selector' => '{{WRAPPER}} .ui-e-text',
            ]
        );

        $this->add_responsive_control(
            'subtitle_margin',
            [
                'label'      => esc_html__('Margin', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_hover_mode_normal',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_color_hover',
            [
                'label' => esc_html__('Title Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ul li:hover .ui-e-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'title_link_color',
            [
                'label' => esc_html__('Linked Title Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ul li:hover a .ui-e-title ' => 'color: {{VALUE}} ',
                ],
            ]
        );

        $this->add_control(
            'subtitle_color_hover',
            [
                'label' => esc_html__('Subtitle Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ul li:hover .ui-e-text' => 'color: {{VALUE}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'subtitle_link_color',
            [
                'label' => esc_html__('Linked Subtitle Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ul li:hover a .ui-e-text ' => 'color: {{VALUE}} ',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_icon_style',
            [
                'label' => esc_html__('Icon', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'icon_size',
            [
                'label' => esc_html__('Icon Size', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 80,
                        'step' => 3,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon' => '--ui-e-icon-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_mode_style1');
        $this->start_controls_tab(
            'tab_normal_mode_normal1',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon' => '--ui-e-icon-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'right_icon_bg_color',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon ' => 'background: {{VALUE}} ;'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'icon_border',
                'label' => esc_html__('Border', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-icon',
            ]
        );

        $this->add_responsive_control(
            'icon_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} ;',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_padding',
            [
                'label'      => esc_html__('Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_margin',
            [
                'label'      => esc_html__('Margin', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_hover_mode_normal1',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'icon_color_hover',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-wrap:hover .ui-e-icon' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .ui-e-wrap:hover .ui-e-icon i' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .ui-e-wrap:hover .ui-e-icon svg' => 'fill: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'icon_bg_color_hover',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-wrap:hover .ui-e-icon' => 'background-color: {{VALUE}} ;',
                ],
            ]
        );

        $this->add_control(
            'icon_border_color_hover',
            [
                'label' => esc_html__('Border Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'icon_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-wrap:hover .ui-e-icon' => 'border-color: {{VALUE}} ;',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_line_style',
            [
                'label' => esc_html__('Connector Line', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'connector_line' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'line_color',
            [
                'label' => esc_html__('Line Color', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333',
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
                'selectors' => [
                    '{{WRAPPER}} li:after' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'line_thick',
            [
                'label' => esc_html__('Line Thickness', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 10,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1,
                ],
                'selectors' => [
                    '{{WRAPPER}} li:after' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'line_horizontal_offset',
            [
                'label' => esc_html__('Line Horizontal Offset', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} li:after' => '{{icon_position.VALUE}}: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'line_vertical_offset',
            [
                'label' => esc_html__('Line Vertical Offset', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} li:after' => 'top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_image_style',
            [
                'label' => esc_html__('Image', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'label' => esc_html__('Border', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-img img',
            ]
        );

        $this->add_responsive_control(
            'border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-img img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} ;',
                ],
            ]
        );

        $this->add_control(
            'image_background',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-img img' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_margin',
            [
                'label'      => esc_html__('Margin', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_size',
            [
                'label' => esc_html__('Size', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 40,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-img img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }
    protected function render()
    {

        $settings = $this->get_settings_for_display();
        $this->add_render_attribute('icon_list', 'class', $this->is_option('show_number_icon', 'yes') ? 'ui-e-number' : '');
?>
        <ul>
            <?php foreach ($settings['icon_list'] as $index => $item) :

                $i = $index + 1; // index is 0 based

                // Prepare the atts
                $repeater_setting_key = $this->get_repeater_setting_key('text', 'icon_list', $index);
                $tag = $settings['title_tags'];
                $this->add_render_attribute($repeater_setting_key, 'class', 'elementor-icon-list-text');
                $this->add_render_attribute('list_title_tags', 'class', 'ui-e-title', true);

                // Build url wrapper if link is set
                if (!empty($item['link']['url'])) {
                    $link_key = 'link_' . $index;
                    $this->add_link_attributes($link_key, $item['link']);

                    $wrapper        = "<a class='ui-e-wrap' {$this->get_render_attribute_string($link_key)}>";
                    $wrapperClosure = 'a';

                    // Build default wrapper otherwise
                } else {
                    $wrapper        = '<div class="ui-e-wrap">';
                    $wrapperClosure = 'div';
                }

            ?>
                <li <?php $this->print_render_attribute_string('list_class'); ?>>

                    <?php echo wp_kses_post($wrapper); ?>

                    <?php if ($this->is_option('show_number_icon', 'yes')) : ?>
                        <div class='ui-e-number'>
                            <span> <?php echo esc_html($i); ?> </span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($item['img']['url'])) : ?>
                        <div class="ui-e-img">
                            <?php
                            $thumb_url = $item['img']['url'];
                            if ($thumb_url) {
                                echo wp_kses_post(wp_get_attachment_image(
                                    $item['img']['id'],
                                    'medium',
                                    false,
                                    [
                                        'alt' => esc_html($item['text'])
                                    ]
                                ));
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="ui-e-content">
                        <<?php echo Helper::esc_tag($tag); ?> <?php $this->print_render_attribute_string('list_title_tags'); ?>>
                            <?php echo wp_kses_post($item['text']); ?>
                        </<?php echo Helper::esc_tag($tag); ?>>
                        <p class="ui-e-text"> <?php echo wp_kses_post($item['text_details']); ?> </p>
                    </div>

                    <?php if (!empty($item['list_icon']['value'])) : ?>
                        <div class="ui-e-icon">
                            <?php Icons_Manager::render_icon($item['list_icon'], ['aria-hidden' => 'true']); ?>
                        </div>
                    <?php endif; ?>

                    </<?php echo esc_html($wrapperClosure) ?>>
                </li>
            <?php
            endforeach; ?>
        </ul>
<?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new IconList());
