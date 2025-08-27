<?php

namespace UiCoreElements;

use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Plugin;
use Elementor\Icons_Manager;

use UiCoreElements\UiCoreWidget;

defined('ABSPATH') || exit();

/**
 * Accordion
 *
 * @author Lucas Marini Falbo <lucas@uicore.co>
 * @since 1.0.0
 */

class Accordion extends UiCoreWidget
{

    public function get_name()
    {
        return 'uicore-accordion';
    }
    public function get_title()
    {
        return __('Accordion', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-accordion ui-e-widget';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_keywords()
    {
        return ['accordion', 'tabs', 'toggle', 'uicore'];
    }
    public function get_styles()
    {
        return ['accordion'];
    }
    public function get_scripts()
    {
        return ['accordion'];
    }
    // TODO: remove after Optmized Markup experiment is merged to the core
    public function has_widget_inner_wrapper(): bool
    {
        return true;
    }
    protected function register_controls()
    {
        $this->start_controls_section(
            'section_title',
            [
                'label' => __('Accordion', 'uicore-elements'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'tab_title',
            [
                'label'       => __('Title & Content', 'uicore-elements'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => ['active' => true],
                'default'     => __('Accordion Title', 'uicore-elements'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'source',
            [
                'label'   => esc_html__('Select Source', 'uicore-elements'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'custom'    => esc_html__('Custom Content', 'uicore-elements'),
                    'sectionId'  => esc_html__('Section ID', 'uicore-elements'),
                ],
            ]
        );

        $repeater->add_control(
            'tab_content',
            [
                'label'      => __('Content', 'uicore-elements'),
                'type'       => Controls_Manager::WYSIWYG,
                'dynamic'    => ['active' => true],
                'default'    => __('Accordion Content', 'uicore-elements'),
                'show_label' => false,
                'condition'  => ['source' => 'custom'],
            ]
        );

        $repeater->add_control(
            'section_id',
            [
                'label'         => esc_html__('Section ID', 'uicore-elements'),
                'type'          => Controls_Manager::TEXT,
                'placeholder'   => esc_html__('CSS ID from an element', 'uicore-elements'),
                'condition'     => [
                    'source'    => 'sectionId'
                ],
                'dynamic'     => ['active' => true],
            ]
        );

        $repeater->add_control(
            'repeater_icon',
            [
                'label'            => __('Title Icon', 'uicore-elements'),
                'type'             => Controls_Manager::ICONS,
                'label_block' => false,
                'skin'        => 'inline',
            ]
        );

        $this->add_control(
            'tabs',
            [
                'label'       => __('Items', 'uicore-elements'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [
                        'tab_title'   => __('Accordion #1', 'uicore-elements'),
                        'tab_content' => __('I am item content. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'uicore-elements'),
                    ],
                    [
                        'tab_title'   => __('Accordion #2', 'uicore-elements'),
                        'tab_content' => __('I am item content. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'uicore-elements'),
                    ],
                    [
                        'tab_title'   => __('Accordion #3', 'uicore-elements'),
                        'tab_content' => __('I am item content. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'uicore-elements'),
                    ],
                ],
                'title_field' => '{{{ tab_title }}}',
            ]
        );

        $this->add_control(
            'title_html_tag',
            [
                'label'   => __('Title HTML Tag', 'uicore-elements'),
                'type'    => Controls_Manager::SELECT,
                'options' => Helper::get_title_tags(),
                'default' => 'h5',
            ]
        );

        $this->add_control(
            'accordion_icon',
            [
                'label'            => __('Icon', 'uicore-elements'),
                'type'             => Controls_Manager::ICONS,
                'default'          => [
                    'value'   => 'fas fa-plus',
                    'library' => 'fa-solid',
                ],
                'recommended'      => [
                    'fa-solid'   => [
                        'chevron-down',
                        'angle-down',
                        'angle-double-down',
                        'caret-down',
                        'caret-square-down',
                    ],
                    'fa-regular' => [
                        'caret-square-down',
                    ],
                ],
                'label_block' => false,
                'skin'        => 'inline',
            ]
        );

        $this->add_control(
            'accordion_active_icon',
            [
                'label'            => __('Active Icon', 'uicore-elements'),
                'type'             => Controls_Manager::ICONS,
                'default'          => [
                    'value'   => 'fas fa-minus',
                    'library' => 'fa-solid',
                ],
                'recommended'      => [
                    'fa-solid'   => [
                        'chevron-up',
                        'angle-up',
                        'angle-double-up',
                        'caret-up',
                        'caret-square-up',
                    ],
                    'fa-regular' => [
                        'caret-square-up',
                    ],
                ],
                'label_block' => false,
                'skin'        => 'inline',
                'condition'        => [
                    'accordion_icon[value]!' => '',
                    'icon_animation!'   => 'ui-e-animation-ico-spin'
                ],
            ]
        );

        $this->add_control(
            'show_custom_icon',
            [
                'label'   => esc_html__('Show Title Icon', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'separator' => 'before'
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_additional',
            [
                'label' => __('Additional', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'collapsible',
            [
                'label'   => __('Collapse All Items', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'true',
                'return_value' => 'true',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'multiple',
            [
                'label' => __('Multiple Open', 'uicore-elements'),
                'type'  => Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'active_item',
            [
                'label' => __('Active Item', 'uicore-elements'),
                'type'  => Controls_Manager::NUMBER,
                'min'   => 1,
                'max'   => 20,
                'default' => 1,
            ]
        );

        $this->add_control(
            'active_hash',
            [
                'label'   => esc_html__('Hash Location', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'no',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'active_scrollspy',
            [
                'label'     => esc_html__('Scrollspy', 'uicore-elements'),
                'type'      => Controls_Manager::SWITCHER,
                'default'   => 'no',
                'return'    => 'yes',
                'frontend_available' => true,
                'condition' => [
                    'active_hash' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'hash_top_offset',
            [
                'label'      => esc_html__('Top Offset ', 'uicore-elements'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', ''],
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 200,
                        'step' => 5,
                    ],

                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 70,
                ],
                'condition'  => [
                    'active_hash'      => 'yes',
                    'active_scrollspy' => 'yes',
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'hash_scrollspy_delay',
            [
                'label'      => esc_html__('Scrollspy Delay', 'uicore-elements'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['ms', ''],
                'range'      => [
                    'ms' => [
                        'min'  => 100,
                        'max'  => 5000,
                        'step' => 50,
                    ],
                ],
                'default'    => [
                    'unit' => 'ms',
                    'size' => 1000,
                ],
                'condition'  => [
                    'active_hash'      => 'yes',
                    'active_scrollspy' => 'yes',
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'hash_scrollspy_duration',
            [
                'label'      => esc_html__('Scrollspy Duration', 'uicore-elements'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['ms', ''],
                'range'      => [
                    'ms' => [
                        'min'  => 100,
                        'max'  => 5000,
                        'step' => 50,
                    ],
                ],
                'default'    => [
                    'unit' => 'ms',
                    'size' => 800,
                ],
                'condition'  => [
                    'active_hash'      => 'yes',
                    'active_scrollspy' => 'yes',
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'schema_activity',
            [
                'label'       => esc_html__('FAQ Schema', 'uicore-elements'),
                'description' => esc_html__('Avoid activating schema for multiple Accordions on the same page to prevent errors in Google indexing. Activate schema only for the Accordion you intend to display in search results.', 'uicore-elements'),
                'type'        => Controls_Manager::SWITCHER,
                'separator'   => 'before',
            ]
        );

        $this->end_controls_section();

        //Style
        $this->start_controls_section(
            'section_toggle_style_item',
            [
                'label' => __('Item', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'item_spacing',
            [
                'label'     => __('Item Gap', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default'   => [
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-item + .ui-e-accordion-item' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_item_style');

        $this->start_controls_tab(
            'tab_item_normal',
            [
                'label' => __('Normal', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'item_background',
                'selector'  => '{{WRAPPER}} .ui-e-accordion-item',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'item_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .ui-e-accordion-item',
            ]
        );

        $this->add_responsive_control(
            'item_radius',
            [
                'label'      => __('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-accordion-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_padding',
            [
                'label'      => __('Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 15,
                    'right' => 15,
                    'bottom' => 15,
                    'left' => 15,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-accordion-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'item_shadow',
                'selector' => '{{WRAPPER}} .ui-e-accordion-item',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_item_hover',
            [
                'label' => __('Hover', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'hover_item_background',
                'selector'  => '{{WRAPPER}} .ui-e-accordion-item:hover',
            ]
        );

        $this->add_control(
            'item_hover_border_color',
            [
                'label'     => __('Border Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'condition' => [
                    'item_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-item:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_item_active',
            [
                'label' => __('Active', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'active_item_background',
                'selector'  => '{{WRAPPER}} .ui-e-accordion-item.ui-open',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'active_item_shadow',
                'selector' => '{{WRAPPER}} .ui-e-accordion-item.ui-open',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'active_item_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .ui-e-accordion-item.ui-open',
            ]
        );

        $this->add_responsive_control(
            'active_item_radius',
            [
                'label'      => __('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-accordion-item.ui-open' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_toggle_style_title',
            [
                'label' => __('Title', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'title_alignment',
            [
                'label'       => __('Alignment', 'uicore-elements'),
                'type'        => Controls_Manager::CHOOSE,
                'options'     => [
                    'flex-start'  => [
                        'title' => __('Left', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => __('Right', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default'     => is_rtl() ? 'flex-end' : 'flex-start',
                'default'     => 'flex-start',
                'toggle'      => false,
                'label_block' => false,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-title-text' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_title_style');

        $this->start_controls_tab(
            'tab_title_normal',
            [
                'label' => __('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => __('Title Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-custom-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'title_background',
                'selector'  => '{{WRAPPER}} .ui-e-accordion-title',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'title_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .ui-e-accordion-title',
            ]
        );

        $this->add_responsive_control(
            'title_radius',
            [
                'label'      => __('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-accordion-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_padding',
            [
                'label'      => __('Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-accordion-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .ui-e-accordion-title',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'title_shadow',
                'selector' => '{{WRAPPER}} .ui-e-accordion-title',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_title_hover',
            [
                'label' => __('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'hover_title_color',
            [
                'label'     => __('Title Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-item:hover .ui-e-accordion-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-accordion-item:hover .ui-e-custom-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'hover_title_background',
                'selector'  => '{{WRAPPER}} .ui-e-accordion-item:hover .ui-e-accordion-title',
            ]
        );

        $this->add_control(
            'title_hover_border_color',
            [
                'label'     => __('Border Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'condition' => [
                    'title_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-item:hover .ui-e-accordion-title' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_title_active',
            [
                'label' => __('Active', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'active_title_color',
            [
                'label'     => __('Title Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-accordion-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-custom-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'active_title_background',
                'selector'  => '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-accordion-title',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'active_title_shadow',
                'selector' => '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-accordion-title',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'active_title_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-accordion-title',
            ]
        );

        $this->add_responsive_control(
            'active_title_radius',
            [
                'label'      => __('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-accordion-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_title_icon',
            [
                'label' => __('Title Icon', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_custom_icon' => 'yes'
                ]
            ]
        );

        $this->start_controls_tabs('tabs_title_icon_style');

        $this->start_controls_tab(
            'tab_title_icon_normal',
            [
                'label' => __('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_icon_color',
            [
                'label'     => __('Title Icon Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-custom-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-custom-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_icon_size',
            [
                'label'   => esc_html__('Size', 'uicore-elements'),
                'type'    => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-custom-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .ui-e-custom-icon svg' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_indent',
            [
                'label'   => esc_html__('Spacing', 'uicore-elements'),
                'type'    => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-custom-icon' => '--ui-e-margin-right:{{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_title_icon_hover',
            [
                'label' => __('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_hover_icon_color',
            [
                'label'     => __('Title Icon Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-item:hover .ui-e-custom-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-accordion-item:hover .ui-e-custom-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_title_icon_active',
            [
                'label' => __('Active', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_active_icon_color',
            [
                'label'     => __('Title Icon Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-custom-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-custom-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_toggle_style_icon',
            [
                'label'     => __('Open & Close Icon', 'uicore-elements'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'accordion_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'icon_align',
            [
                'label'       => __('Alignment', 'uicore-elements'),
                'type'        => Controls_Manager::CHOOSE,
                'options'     => [
                    'left'  => [
                        'title' => __('Start', 'uicore-elements'),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => __('End', 'uicore-elements'),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'default'     => is_rtl() ? 'left' : 'right',
                'toggle'      => false,
                'label_block' => false,
            ]
        );

        $this->start_controls_tabs('tabs_icon_style');

        $this->start_controls_tab(
            'tab_icon_normal',
            [
                'label' => __('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label'     => esc_html__('Item Icon Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-accordion-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'icon_background_color',
                'selector'  => '{{WRAPPER}} .ui-e-accordion-icon'
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'icon_border',
                'selector'    => '{{WRAPPER}} .ui-e-accordion-icon',
            ]
        );

        $this->add_responsive_control(
            'icon_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-accordion-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_padding',
            [
                'label'      => esc_html__('Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    // set as css variables because animations may need some particular value, such as pad-top for fade icon animation
                    '{{WRAPPER}} .ui-e-accordion-icon' => '--ui-e-icon-pad-top: {{TOP}}{{UNIT}}; --ui-e-icon-pad-right: {{RIGHT}}{{UNIT}}; --ui-e-icon-pad-bot: {{BOTTOM}}{{UNIT}}; --ui-e-icon-pad-left: {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label'     => __('Icon Size', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-title .ui-e-accordion-icon' => '--ui-e-icon-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label'     => __('Icon Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-title' => 'gap: {{SIZE}}{{UNIT}};',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'icon_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-accordion-icon',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_icon_hover',
            [
                'label' => __('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'icon_hover_color',
            [
                'label'     => esc_html__('Item Icon Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-item:hover .ui-e-accordion-icon'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-accordion-item:hover .ui-e-accordion-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'icon_hover_background_color',
                'selector'  => '{{WRAPPER}} .ui-e-accordion-item:hover .ui-e-accordion-icon'
            ]
        );

        $this->add_control(
            'icon_hover_border_color',
            [
                'label'     => esc_html__('Border Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'condition' => [
                    'icon_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-item:hover .ui-e-accordion-icon' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_icon_active',
            [
                'label' => __('Active', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'icon_active_color',
            [
                'label'     => esc_html__('Item Icon Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-accordion-icon'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-accordion-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'icon_active_background_color',
                'selector'  => '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-accordion-icon'
            ]
        );

        $this->add_control(
            'icon_active_border_color',
            [
                'label'     => esc_html__('Border Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'condition' => [
                    'icon_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-item.ui-open .ui-e-accordion-icon' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_toggle_style_content',
            [
                'label' => __('Content', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_color',
            [
                'label'     => __('Text Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-content' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'content_background_color',
                'selector'  => '{{WRAPPER}} .ui-e-accordion-content',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'content_border',
                'label'       => __('Border', 'uicore-elements'),
                'selector'    => '{{WRAPPER}} .ui-e-accordion-content',
            ]
        );

        $this->add_responsive_control(
            'content_radius',
            [
                'label'      => __('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label'      => __('Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-accordion-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_spacing',
            [
                'label'     => __('Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-content' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'content_typography',
                'selector' => '{{WRAPPER}} .ui-e-accordion-content',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'content_shadow',
                'label'       => __('Box Shadow', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-accordion-content',
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label'     => __('Alignment', 'uicore-elements'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'left'   => [
                        'title' => __('Left', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right'  => [
                        'title' => __('Right', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-content' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_animation',
            [
                'label' => esc_html__('Animations', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'accordion_animation',
            [
                'label' => esc_html__('Accordion Animation', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'ui-e-animation-acc-basic',
                'options' => [
                    '' => esc_html__('None', 'uicore-elements'),
                    'ui-e-animation-acc-basic' => esc_html__('Basic', 'uicore-elements'),
                    'ui-e-animation-acc-slow' => esc_html__('Slow', 'uicore-elements'),
                    'ui-e-animation-acc-expand' => esc_html__('Expand', 'uicore-elements'),
                    'ui-e-animation-acc-overshadow' => esc_html__('Overshadow', 'uicore-elements'),
                ],
                'prefix_class'    => '',
                'render_type'   => 'template',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'icon_animation',
            [
                'label' => esc_html__('Icon Animation', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'ui-e-animation-ico-fade',
                'options' => [
                    '' => esc_html__('None', 'uicore-elements'),
                    'ui-e-animation-ico-fade' => esc_html__('Fade', 'uicore-elements'),
                    'ui-e-animation-ico-spin' => esc_html__('Spin', 'uicore-elements'),
                    'ui-e-animation-ico-slide' => esc_html__('Slide', 'uicore-elements'),
                ],
                'prefix_class'    => '',
                'render_type'   => 'template',
            ]
        );

        $this->add_control(
            'icon_spin_value',
            [
                'label' => esc_html__('Icon Rotation', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['deg', ''],
                'range' => [
                    'deg' => [
                        'min' => 0,
                        'max' => 360,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'deg',
                    'size' => 315,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-accordion-icon' => '--ui-e-spin:{{SIZE}}deg;',
                ],
                'condition' => [
                    'icon_animation'  => 'ui-e-animation-ico-spin',
                ]
            ]
        );

        $this->end_controls_section();
    }
    protected function render()
    {

        $settings = $this->get_settings_for_display();
        $ID = 'ui-e-accordion-' . $this->get_id();

        // Sets schema atts if active
        if ($this->is_option('schema_activity', 'yes')) {
            $this->add_render_attribute('accordion', 'itemscope');
            $this->add_render_attribute('accordion', ['itemtype' => 'https://schema.org/FAQPage']);
        }

        $titleTag = $settings['title_html_tag'] . ' ';  // HTML tag

?>
        <div class="ui-e-accordion" <?php $this->print_render_attribute_string('accordion'); ?>>

            <?php foreach ($settings['tabs'] as $index => $item) :

                $loopCounter = $index + 1;

                // Builds the accordion ID
                $acc_id = $item['tab_title']
                    ? 'ui-e-' . sanitize_title($item['tab_title'])
                    : $ID . $loopCounter;

                // Build the Aria ID
                if ('sectionId' == $item['source']) {
                    $aria_id = 'ui-' . preg_replace('/#/', '', $item['section_id']);
                } else {
                    $aria_id = 'ui-e-acc-' . $loopCounter;
                }

                // Creates the title and content atts
                $tab_title_setting_key   = $this->get_repeater_setting_key('tab_title', 'tabs', $index);
                $tab_content_setting_key = $this->get_repeater_setting_key('tab_content', 'tabs', $index);

                // Render title atts
                // TODO: remove `ui-e-title` class after, at least, 3 releases from 1.2.1. Kept for compatibility purposes
                $this->add_render_attribute($tab_title_setting_key, [
                    'class' => ['ui-e-accordion-title ui-e-title'],
                ]);
                $this->add_render_attribute($tab_title_setting_key, 'class', ('right' == $settings['icon_align']) ? 'ui-right' : '');

                // Render content atts
                // TODO: remove `ui-e-content` class after, at least, 3 releases from 1.2.1. Kept for compatibility purposes
                $this->add_render_attribute($tab_content_setting_key, [
                    'class' => ['ui-e-accordion-content ui-e-content'],
                    'style' => ($loopCounter === $settings['active_item']) ? '' : 'display:none;', // keep content hide if item not active (has to be set inline so jquery can take control over it)
                    'aria-labelledby' => $acc_id,
                    'id' => $aria_id
                ]);

                // Check if the current accordion item uses sectionId as source
                if ('sectionId' == $item['source']) {
                    // and also check if an ID was set
                    if ($item['section_id'] != '') {
                        $this->add_render_attribute($tab_content_setting_key, ['class' => ['ui-section-id']]); // class used by JS
                        $sectionWarning = "The element with ID <em>{$item['section_id']}</em> is going to be inserted here on the front-end";
                    } else {
                        $sectionWarning = esc_html__("You didn't define an ID.", 'uicore-elements');
                    }
                }

                // Render item atts
                // TODO: remove `ui-e-item` class after, at least, 3 releases from 1.2.1. Kept for compatibility purposes
                $item_key = 'ui-e-item-' . $index;
                $this->add_render_attribute($item_key, [
                    'class' => ($loopCounter === $settings['active_item']) ? 'ui-e-accordion-item ui-e-item ui-open' : 'ui-e-accordion-item ui-e-item',
                    'role' => 'button',
                    'tabindex' => '0',
                    'aria-expanded' => ($loopCounter === $settings['active_item']) ? 'true' : 'false',
                    'aria-controls' => $aria_id,
                    'id' => $acc_id,
                ]);

                // Render schema atts if active
                if ($this->is_option('schema_activity', 'yes')) {
                    $this->add_render_attribute($item_key, 'itemscope');
                    $this->add_render_attribute($item_key, 'itemprop', 'mainEntity');
                    $this->add_render_attribute($item_key, 'itemtype', 'https://schema.org/Question');

                    $this->add_render_attribute($tab_content_setting_key, 'itemscope');
                    $this->add_render_attribute($tab_content_setting_key, 'itemprop', 'acceptedAnswer', true);
                    $this->add_render_attribute($tab_content_setting_key, 'itemtype', 'https://schema.org/Answer', true);

                    $schema_text = $this->get_repeater_setting_key('schema_text', 'tabs', $index);
                    $schema_name = $this->get_repeater_setting_key('schema_name', 'tabs', $index);
                    $this->add_render_attribute($schema_text, 'itemprop', 'text');
                    $this->add_render_attribute($schema_name, 'itemprop', 'name');

                    // Sets the variables to empty to avoid undefined variable problems
                } else {
                    $schema_text = $schema_name = '';
                }
            ?>
                <div <?php $this->print_render_attribute_string($item_key); ?>>

                    <<?php echo Helper::esc_tag($titleTag) ?> <?php $this->print_render_attribute_string($tab_title_setting_key); ?>>

                        <?php if ($settings['accordion_icon']['value']) :
                            // TODO: remove `ui-e-icon` class after, at least, 3 releases from 1.2.1. Kept for compatibility purposes
                        ?>
                            <span class="ui-e-accordion-icon ui-e-icon ui-e-<?php echo esc_attr($settings['icon_align']); ?>" aria-hidden="true">

                                <span class="ui-e-accordion-icon-closed">
                                    <?php Icons_Manager::render_icon($settings['accordion_icon'], ['aria-hidden' => 'true']); ?>
                                </span>

                                <?php if ($settings['icon_animation'] != 'ui-e-animation-ico-spin') : //spin animation dismiss the opened accordion icon
                                ?>
                                    <span class="ui-e-accordion-icon-opened">
                                        <?php Icons_Manager::render_icon($settings['accordion_active_icon'], ['aria-hidden' => 'true']); ?>
                                    </span>
                                <?php endif; ?>

                            </span>
                        <?php endif; ?>

                        <?php // TODO: remove `ui-e-title-text` class after, at least, 3 releases from 1.2.1. Kept for compatibility purposes
                        ?>
                        <span class="ui-e-accordion-title-text ui-e-title-text" <?php $this->print_render_attribute_string($schema_name); ?>>
                            <?php if (!empty($item['repeater_icon']['value']) && $this->is_option('show_custom_icon', 'yes')) : ?>
                                <span class="ui-e-custom-icon">
                                    <?php Icons_Manager::render_icon($item['repeater_icon'], ['aria-hidden' => 'true']); ?>
                                </span>
                            <?php endif; ?>
                            <?php echo esc_html($item['tab_title']); ?>
                        </span>

                    </<?php echo Helper::esc_tag($titleTag) ?>>

                    <div <?php $this->print_render_attribute_string($tab_content_setting_key); ?>>
                        <?php
                        if ('custom' == $item['source'] && !empty($item['tab_content'])) {

                            // if schema is active, creates a wrapper for the itemprop structure
                            if ($this->is_option('schema_activity', 'yes')) {
                        ?>
                                <div <?php $this->print_render_attribute_string($schema_text); ?>>
                                <?php
                            }

                            echo $this->parse_text_editor($item['tab_content']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                            if ($this->is_option('schema_activity', 'yes')) {
                                ?>
                                </div>
                        <?php
                            }
                        }
                        // if source is sectionId and we're inside editor, returns a warning for context. The sectionId content is moved via JS
                        if ('sectionId' == $item['source'] && $this->is_edit_mode()) {
                            echo wp_kses($sectionWarning, ['em' => []]);
                        }
                        ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
<?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new Accordion());
