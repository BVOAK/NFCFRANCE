<?php

namespace UiCoreElements\Utils;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

defined('ABSPATH') || exit();

/*
* Basic Item Styles Component
*/

trait Item_Style_Component
{

    function TRAIT_register_normal_item_style_controls($tab = true)
    {
        // If the widget doesn't require extra controls, we can use tabs to decrease the widget code
        if ($tab) {
            $this->start_controls_tab(
                'tab_item_normal',
                [
                    'label' => esc_html__('Normal', 'uicore-elements'),
                ]
            );
        }

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'item_background',
                'selector' => '{{WRAPPER}} .ui-e-item',
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'item_border',
                'label' => esc_html__('Border', 'uicore-elements'),
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top'      => '1',
                            'right'    => '1',
                            'bottom'   => '1',
                            'left'     => '1',
                            'isLinked' => true,
                        ],
                    ],
                    'color' => [
                        'default' => '#EEE'
                    ]
                ],
                'selector' => '{{WRAPPER}} .ui-e-item',
            ]
        );
        $this->add_responsive_control(
            'item_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 12,
                    'right' => 12,
                    'bottom' => 12,
                    'left' => 12,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};', // Sliders use --ui-e-radius var as item radius
                    '{{WRAPPER}} .ui-e-item' => 'border-radius: var(--ui-e-radius);',
                ],
            ]
        );
        $this->add_responsive_control(
            'item_padding',
            [
                'label' => esc_html__('Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 30,
                    'right' => 30,
                    'bottom' => 30,
                    'left' => 30,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'item_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-item',
            ]
        );

        if ($tab) {
            $this->end_controls_tab();
        }
    }
    function TRAIT_register_hover_item_style_controls($tab = true)
    {
        // If the widget doesn't require extra controls, we can use tabs to decrease the widget code
        if ($tab) {
            $this->start_controls_tab(
                'tab_item_hover',
                [
                    'label' => esc_html__('Hover', 'uicore-elements'),
                ]
            );
        }

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'item_hover_background',
                'selector' => '{{WRAPPER}} .ui-e-item:hover',
            ]
        );
        $this->add_control(
            'item_hover_border_color',
            [
                'label' => esc_html__('Border Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'item_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'item_hover_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-wrp:hover .ui-e-item',
            ]
        );

        if ($tab) {
            $this->end_controls_tab();
        }
    }
    function TRAIT_register_active_item_style_controls($tab = true)
    {
        // If the widget doesn't require extra controls, we can use tabs to decrease the widget code
        if ($tab) {
            $this->start_controls_tab(
                'tab_item_active',
                [
                    'label' => esc_html__('Active', 'uicore-elements'),
                ]
            );
        }

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'item_active_background',
                'selector' => '{{WRAPPER}} .ui-e-wrp.is-selected .ui-e-item',
            ]
        );
        $this->add_control(
            'item_active_border_color',
            [
                'label' => esc_html__('Border Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'item_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-wrp.is-selected .ui-e-item' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'item_active_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-wrp.is-selected .ui-e-item',
            ]
        );

        if ($tab) {
            $this->end_controls_tab();
        }
    }

    /**
     * Register all Item Style Controls at Once
     *
     * @param boolean $active_state Register Active State Controls. Default is `true`
     * @return void
     */
    function TRAIT_register_all_item_style_controls($active_state = true)
    {
        $this->start_controls_tabs('tabs_item_style');
        $this->TRAIT_register_normal_item_style_controls();
        $this->TRAIT_register_hover_item_style_controls();
        if ($active_state) {
            $this->TRAIT_register_active_item_style_controls();
        }
        $this->end_controls_tabs();
    }
}
