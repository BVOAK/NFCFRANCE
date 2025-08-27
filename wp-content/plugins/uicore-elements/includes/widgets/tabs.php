<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Plugin;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Modules\NestedElements\Controls\Control_Nested_Repeater;

defined('ABSPATH') || exit();

/**
 * Tabs
 *
 * @author Lucas Marini Falbo <lucas@uicore.co>
 * @since 1.0.6
 */

class Tabs extends UiCoreNestedWidget
{

    public function get_name()
    {
        return 'uicore-tabs';
    }
    public function get_title()
    {
        return esc_html__('Tabs', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-tabs ui-e-widget';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_keywords()
    {
        return ['tabs', 'tab', 'content', 'nested'];
    }
    public function get_styles()
    {
        return ['tabs'];
    }
    public function get_scripts()
    {
        return ['tabs'];
    }
    // TODO: remove after Optmized Markup experiment is merged to the core
    public function has_widget_inner_wrapper(): bool
    {
        return true;
    }

    // Nested required functions
    protected function tab_content_container(int $index)
    {
        return [
            'elType' => 'container',
            'settings' => [
                /* translators: %s: Tab index */
                '_title' => sprintf(__('Tab #%s', 'uicore-elements'), $index),
                'content_width' => 'full',
            ],
        ];
    }
    protected function get_default_children_elements()
    {
        return [
            $this->tab_content_container(1),
            $this->tab_content_container(2),
            $this->tab_content_container(3),
        ];
    }
    protected function get_default_repeater_title_setting_key()
    {
        return 'tab_title';
    }
    protected function get_default_children_title()
    {
        /* translators: %d: Tab index */
        return esc_html__('Tab #%d', 'uicore-elements');
    }
    protected function get_default_children_placeholder_selector()
    {
        return '.ui-e-tabs-content';
    }

    protected function register_controls()
    {

        if (!Plugin::$instance->experiments->is_feature_active('nested-elements')) {
            $this->nesting_fallback('controls');
            return;
        }

        // Helper properties
        $start = is_rtl() ? 'right' : 'left';
        $end = is_rtl() ? 'left' : 'right';
        $start_logical = is_rtl() ? 'end' : 'start';
        $end_logical = is_rtl() ? 'start' : 'end';
        $heading_selector = '{{WRAPPER}} > .elementor-widget-container > .ui-e-tabs > .ui-e-tabs-heading'; // TODO: .elementor-widget-container will be gone soon
        $content_selector = ':where( {{WRAPPER}} > .elementor-widget-container > .ui-e-tabs > .ui-e-tabs-content ) > .e-con'; // TODO: .elementor-widget-container will be gone soon

        $this->start_controls_section(
            'section_tabs',
            [
                'label' => esc_html__('Tabs', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control('tab_title', [
            'label' => esc_html__('Title', 'uicore-elements'),
            'type' => Controls_Manager::TEXT,
            'default' => esc_html__('Tab Title', 'uicore-elements'),
            'placeholder' => esc_html__('Tab Title', 'uicore-elements'),
            'label_block' => true,
            'dynamic' => [
                'active' => true,
            ],
        ]);

        $repeater->add_control('tab_description', [
            'label' => esc_html__('Description', 'uicore-elements'),
            'type' => Controls_Manager::TEXTAREA,
            'rows' => 2,
            'placeholder' => esc_html__('Tab Title', 'uicore-elements'),
            'label_block' => true,
            'dynamic' => [
                'active' => true,
            ],
        ]);

        $repeater->add_control(
            'tab_icon',
            [
                'label' => esc_html__('Icon', 'uicore-elements'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,
            ]
        );

        $repeater->add_control(
            'tab_icon_active',
            [
                'label' => esc_html__('Active Icon', 'uicore-elements'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,
                'condition' => [
                    'tab_icon[value]!' => '',
                ],
            ]
        );

        $repeater->add_control(
            'tab_id',
            [
                'label' => esc_html__('CSS ID', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'ai' => [
                    'active' => false,
                ],
                'dynamic' => [
                    'active' => true,
                ],
                'title' => esc_html__('Add your custom id WITHOUT the Pound key. e.g: my-id', 'uicore-elements'),
                'style_transfer' => false,
                'classes' => 'elementor-control-direction-ltr',
            ]
        );

        $this->add_control('tabs', [
            'label' => esc_html__('Tabs Items', 'uicore-elements'),
            'type' => Control_Nested_Repeater::CONTROL_TYPE,
            'fields' => $repeater->get_controls(),
            'default' => [
                [
                    'tab_title' => esc_html__('Tab #1', 'uicore-elements'),
                ],
                [
                    'tab_title' => esc_html__('Tab #2', 'uicore-elements'),
                ],
                [
                    'tab_title' => esc_html__('Tab #3', 'uicore-elements'),
                ],
            ],
            'title_field' => '{{{ tab_title }}}',
            'button_text' => 'Add Tab',
        ]);

        // Tabs position and alignment group values
        $styling_block_start = '--ui-e-tabs-direction: column; --ui-e-tabs-head-direction: row; --ui-e-tabs-head-width: initial; --ui-e-tabs-title-flex-basis: content; --ui-e-tabs-title-flex-shrink: initial;';
        $styling_block_end = '--ui-e-tabs-direction: column-reverse; --ui-e-tabs-head-direction: row; --ui-e-tabs-head-width: initial; --ui-e-tabs-title-flex-basis: content; --ui-e-tabs-title-flex-shrink: initial;';
        $styling_inline_end = '--ui-e-tabs-direction: row-reverse; --ui-e-tabs-head-direction: column; --ui-e-tabs-head-width: 240px; --ui-e-tabs-title-flex-basis: initial; --ui-e-tabs-title-flex-shrink: 0; --ui-e-tabs-title-width: 100%';
        $styling_inline_start = '--ui-e-tabs-direction: row; --ui-e-tabs-head-direction: column; --ui-e-tabs-head-width: 240px; --ui-e-tabs-title-flex-basis: initial; --ui-e-tabs-title-flex-shrink: 0; --ui-e-tabs-title-width: 100%';

        $this->add_responsive_control('tabs_direction', [
            'label' => esc_html__('Tabs Direction', 'uicore-elements'),
            'type' => Controls_Manager::CHOOSE,
            'options' => [
                'block-start' => [
                    'title' => esc_html__('Above', 'uicore-elements'),
                    'icon' => 'eicon-v-align-top',
                ],
                'block-end' => [
                    'title' => esc_html__('Below', 'uicore-elements'),
                    'icon' => 'eicon-v-align-bottom',
                ],
                'inline-end' => [
                    'title' => esc_html__('After', 'uicore-elements'),
                    'icon' => 'eicon-h-align-' . $end,
                ],
                'inline-start' => [
                    'title' => esc_html__('Before', 'uicore-elements'),
                    'icon' => 'eicon-h-align-' . $start,
                ],
            ],
            'default' => 'block-start',
            'separator' => 'before',
            'selectors_dictionary' => [
                'block-start' => $styling_block_start,
                'block-end' => $styling_block_end,
                'inline-end' => $styling_inline_end,
                'inline-start' => $styling_inline_start,
            ],
            'selectors' => [
                '{{WRAPPER}}' => '{{VALUE}}',
            ],
        ]);

        $this->add_responsive_control('tabs_width', [
            'label' => esc_html__('Tabs Width', 'uicore-elements'),
            'type' => Controls_Manager::SLIDER,
            'range' => [
                '%' => [
                    'min' => 10,
                    'max' => 50,
                ],
                'px' => [
                    'min' => 20,
                    'max' => 600,
                ],
            ],
            'default' => [
                'unit' => '%',
            ],
            'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
            'selectors' => [
                '{{WRAPPER}}' => '--ui-e-tabs-head-width: {{SIZE}}{{UNIT}}',
            ],
            'condition' => [
                'tabs_direction' => [
                    'inline-start',
                    'inline-end',
                ],
            ],
        ]);

        $this->add_responsive_control('tabs_justify_horizontal', [
            'label' => esc_html__('Justify', 'uicore-elements'),
            'type' => Controls_Manager::CHOOSE,
            'options' => [
                'start' => [
                    'title' => esc_html__('Start', 'uicore-elements'),
                    'icon' => "eicon-align-$start_logical-h",
                ],
                'center' => [
                    'title' => esc_html__('Center', 'uicore-elements'),
                    'icon' => 'eicon-align-center-h',
                ],
                'end' => [
                    'title' => esc_html__('End', 'uicore-elements'),
                    'icon' => "eicon-align-$end_logical-h",
                ],
                'stretch' => [
                    'title' => esc_html__('Stretch', 'uicore-elements'),
                    'icon' => 'eicon-align-stretch-h',
                ],
            ],
            'selectors_dictionary' => [
                'start' => 'justify-content: flex-start; --ui-e-tabs-title-flex-width: initial; --ui-e-tabs-title-justify-content: center; --ui-e-tabs-title-flex-grow: 0;',
                'center' => 'justify-content: center; --ui-e-tabs-title-flex-width: initial; --ui-e-tabs-title-justify-content: center; --ui-e-tabs-title-flex-grow: 0;',
                'end' => 'justify-content: flex-end; --ui-e-tabs-title-flex-width: initial; --ui-e-tabs-title-justify-content: center; --ui-e-tabs-title-flex-grow: 0;',
                'stretch' => 'justify-content: initial; --ui-e-tabs-title-flex-width: 100%; --ui-e-tabs-title-justify-content: center; --ui-e-tabs-title-flex-grow: 1;',
            ],
            'selectors' => [
                '{{WRAPPER}} .ui-e-tabs-heading' => '{{VALUE}}',
            ],
            'condition' => [
                'tabs_direction' => [
                    'block-start',
                    'block-end',
                ],
            ],
            'frontend_available' => true,
        ]);

        $this->add_responsive_control('tabs_justify_vertical', [
            'label' => esc_html__('Tabs alignment', 'uicore-elements'),
            'type' => Controls_Manager::CHOOSE,
            'options' => [
                'start' => [
                    'title' => esc_html__('Start', 'uicore-elements'),
                    'icon' => 'eicon-align-start-v',
                ],
                'center' => [
                    'title' => esc_html__('Center', 'uicore-elements'),
                    'icon' => 'eicon-align-center-v',
                ],
                'end' => [
                    'title' => esc_html__('End', 'uicore-elements'),
                    'icon' => 'eicon-align-end-v',
                ],
                'stretch' => [
                    'title' => esc_html__('Stretch', 'uicore-elements'),
                    'icon' => 'eicon-align-stretch-v',
                ],
            ],
            'selectors_dictionary' => [
                'start' => 'justify-content: flex-start; --ui-e-tabs-title-height: initial; --ui-e-tabs-title-justify-content: initial; --ui-e-tabs-heading-wrap: wrap; --ui-e-tabs-title-flex-basis: content; --ui-e-tabs-title-flex-grow: 0;',
                'center' => 'justify-content: center; --ui-e-tabs-title-height: initial; --ui-e-tabs-title-justify-content: initial; --ui-e-tabs-heading-wrap: wrap; --ui-e-tabs-title-flex-basis: content; --ui-e-tabs-title-flex-grow: 0;',
                'end' => 'justify-content: flex-end; --ui-e-tabs-title-height: initial; --ui-e-tabs-title-justify-content: initial; --ui-e-tabs-heading-wrap: wrap; --ui-e-tabs-title-flex-basis: content; --ui-e-tabs-title-flex-grow: 0;',
                'stretch' => 'justify-content: flex-start; --ui-e-tabs-title-height: 100%; --ui-e-tabs-title-justify-content: center; --ui-e-tabs-heading-wrap: nowrap; --ui-e-tabs-title-flex-basis: auto; --ui-e-tabs-title-flex-grow: 1;',
            ],
            'selectors' => [
                '{{WRAPPER}} .ui-e-tabs-heading' => '{{VALUE}}',
            ],
            'condition' => [
                'tabs_direction' => [
                    'inline-start',
                    'inline-end',
                ],
            ],
        ]);

        $styling_icon_above = '--ui-e-tabs-title-direction: column; --ui-e-tabs-icon-order: initial;';
        $styling_icon_before = '--ui-e-tabs-title-direction: row; --ui-e-tabs-icon-order: initial';
        $styling_icon_after = '--ui-e-tabs-title-direction: row; --ui-e-tabs-icon-order: 1;';

        $this->add_responsive_control('icon_position', [
            'label' => esc_html__('Icon Position', 'uicore-elements'),
            'type' => Controls_Manager::CHOOSE,
            'options' => [
                'above' => [
                    'title' => esc_html__('Before', 'uicore-elements'),
                    'icon' => 'eicon-v-align-top',
                ],
                'before' => [
                    'title' => esc_html__('Before', 'uicore-elements'),
                    'icon' => 'eicon-h-align-' . $start,
                ],
                'after' => [
                    'title' => esc_html__('After', 'uicore-elements'),
                    'icon' => 'eicon-h-align-' . $end,
                ],
            ],
            'default' => 'before',
            'selectors_dictionary' => [
                'above' =>  $styling_icon_above,
                'before' => $styling_icon_before,
                'after' => $styling_icon_after,
            ],
            'separator' => 'before',
            'selectors' => [
                '{{WRAPPER}}' => '{{VALUE}}',
            ],
        ]);

        $this->add_responsive_control('title_alignment', [
            'label' => esc_html__('Content Alignment', 'uicore-elements'),
            'type' => Controls_Manager::CHOOSE,
            'options' => [
                'start' => [
                    'title' => esc_html__('Start', 'uicore-elements'),
                    'icon' => 'eicon-text-align-left',
                ],
                'center' => [
                    'title' => esc_html__('Center', 'uicore-elements'),
                    'icon' => 'eicon-text-align-center',
                ],
                'end' => [
                    'title' => esc_html__('End', 'uicore-elements'),
                    'icon' => 'eicon-text-align-right',
                ],
                'justify' => [
                    'title' => esc_html__('Justify', 'uicore-elements'),
                    'icon' => 'eicon-text-align-justify',
                ],
            ],
            'selectors_dictionary' => [
                'start' => '--ui-e-tabs-title-justify: flex-start; --ui-e-tabs-title-align: flex-start; --ui-e-tabs-title-text-align: start;',
                'center' => '--ui-e-tabs-title-justify: center; --ui-e-tabs-title-align: flex-start; --ui-e-tabs-title-text-align: center;',
                'end' => '--ui-e-tabs-title-justify: flex-end; --ui-e-tabs-title-align: flex-start; --ui-e-tabs-title-text-align: end;',
                'justify' => '--ui-e-tabs-title-justify: space-between; --ui-e-tabs-title-align: flex-start; --ui-e-tabs-title-text-align: start;',
            ],
            'condition' => [
                'icon_position!' => 'above',
            ],
            'selectors' => [
                '{{WRAPPER}}' => '{{VALUE}}',
            ],
        ]);

        $this->add_responsive_control('title_alignment_above', [
            'label' => esc_html__('Content Alignment', 'uicore-elements'),
            'type' => Controls_Manager::CHOOSE,
            'options' => [
                'start' => [
                    'title' => esc_html__('Start', 'uicore-elements'),
                    'icon' => 'eicon-text-align-left',
                ],
                'center' => [
                    'title' => esc_html__('Center', 'uicore-elements'),
                    'icon' => 'eicon-text-align-center',
                ],
                'end' => [
                    'title' => esc_html__('End', 'uicore-elements'),
                    'icon' => 'eicon-text-align-right',
                ],
            ],
            'selectors_dictionary' => [
                'start' => '--ui-e-tabs-title-justify: flex-start; --ui-e-tabs-title-align: flex-start; --ui-e-tabs-title-text-align: start;',
                'center' => '--ui-e-tabs-title-justify: flex-start; --ui-e-tabs-title-align: center; --ui-e-tabs-title-text-align: center;',
                'end' => '--ui-e-tabs-title-justify: flex-start; --ui-e-tabs-title-align: flex-end; --ui-e-tabs-title-text-align: end;',
            ],
            'condition' => [
                'icon_position' => 'above',
            ],
            'selectors' => [
                '{{WRAPPER}}' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section(
            'section_tabs_responsive',
            [
                'label' => esc_html__('Additional Settings', 'uicore-elements'),
            ]
        );

        // Build breakpoint options
        $dropdown_options = [
            'none' => esc_html__('None', 'uicore-elements'),
        ];
        $excluded_breakpoints = [
            'laptop',
            'tablet_extra',
            'widescreen',
        ];

        foreach (Plugin::$instance->breakpoints->get_active_breakpoints() as $breakpoint_key => $breakpoint_instance) {
            // Exclude the larger breakpoints from the dropdown selector.
            if (in_array($breakpoint_key, $excluded_breakpoints, true)) {
                continue;
            }

            $dropdown_options[$breakpoint_key] = sprintf(
                /* translators: 1: Breakpoint label, 2: `>` character, 3: Breakpoint value. */
                esc_html__('%1$s (%2$s %3$dpx)', 'uicore-elements'),
                $breakpoint_instance->get_label(),
                '>',
                $breakpoint_instance->get_value()
            );
        }

        $this->add_control(
            'breakpoint_selector',
            [
                'label' => esc_html__('Breakpoint', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'description' => esc_html__('Note: Choose the breakpoint where tabs automatically switch to a vertical (“accordion”) layout.', 'uicore-elements'),
                'options' => $dropdown_options,
                'default' => 'mobile',
                'prefix_class' => 'ui-e-tabs-',
            ]
        );

        $this->add_control(
            'show_description',
            [
                'label' => esc_html__('Toggle Description', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'uicore-elements'),
                'label_off' => esc_html__('Hide', 'uicore-elements'),
                'description' => esc_html__('Show descriptions only for active items.', 'uicore-elements'),
                'return_value' => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_tabs_style',
            [
                'label' => esc_html__('Tabs', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control('tabs_title_space_between', [
            'label' => esc_html__('Gap between tabs', 'uicore-elements'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', 'em', 'rem', 'custom'],
            'range' => [
                'px' => [
                    'max' => 100,
                ],
                'em' => [
                    'max' => 40,
                ],
                'rem' => [
                    'max' => 40,
                ],
            ],
            'default' => [
                'size' => 10,
                'unit' => 'px',
            ],
            'selectors' => [
                '{{WRAPPER}}' => '--ui-e-tabs-title-gap: {{SIZE}}{{UNIT}}',
            ],
        ]);

        $this->add_responsive_control('tabs_title_spacing', [
            'label' => esc_html__('Distance from content', 'uicore-elements'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', 'em', 'rem', 'custom'],
            'range' => [
                'px' => [
                    'max' => 100,
                ],
                'em' => [
                    'max' => 40,
                ],
                'rem' => [
                    'max' => 40,
                ],
            ],
            'selectors' => [
                '{{WRAPPER}}' => '--ui-e-tabs-gap: {{SIZE}}{{UNIT}}',
            ],
        ]);

        $this->start_controls_tabs('tabs_title_style');

        $this->start_controls_tab(
            'tabs_title_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'tabs_title_background_color',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => "{$heading_selector} > .ui-e-tab-title[aria-selected=\"false\"]",
                'fields_options' => [
                    'color' => [
                        'label' => esc_html__('Background Color', 'uicore-elements'),
                        'selectors' => [
                            '{{SELECTOR}}' => 'background: {{VALUE}}',
                        ],
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tabs_title_border',
                'selector' => "{$heading_selector} > .ui-e-tab-title[aria-selected=\"false\"]",
                'fields_options' => [
                    'color' => [
                        'label' => esc_html__('Border Color', 'uicore-elements'),
                    ],
                    'width' => [
                        'label' => esc_html__('Border Width', 'uicore-elements'),
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tabs_title_box_shadow',
                'label' => esc_html__('Shadow', 'uicore-elements'),
                'separator' => 'after',
                'selector' => "{$heading_selector} > .ui-e-tab-title[aria-selected=\"false\"]",
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tabs_title_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'tabs_title_background_color_hover',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => "{$heading_selector} > .ui-e-tab-title[aria-selected=\"false\"]:hover",
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => Global_Colors::COLOR_ACCENT,
                        ],
                        'label' => esc_html__('Background Color', 'uicore-elements'),
                        'selectors' => [
                            '{{SELECTOR}}' => 'background: {{VALUE}};',
                        ],
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tabs_title_border_hover',
                'selector' => "{$heading_selector} > .ui-e-tab-title[aria-selected=\"false\"]:hover",
                'fields_options' => [
                    'color' => [
                        'label' => esc_html__('Border Color', 'uicore-elements'),
                    ],
                    'width' => [
                        'label' => esc_html__('Border Width', 'uicore-elements'),
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tabs_title_box_shadow_hover',
                'label' => esc_html__('Shadow', 'uicore-elements'),
                'separator' => 'after',
                'selector' => "{$heading_selector} > .ui-e-tab-title[aria-selected=\"false\"]:hover",
            ]
        );

        $this->add_control(
            'hover_animation',
            [
                'label' => esc_html__('Hover Animation', 'uicore-elements'),
                'type' => Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->add_control(
            'tabs_title_transition_duration',
            [
                'label' => esc_html__('Transition Duration', 'uicore-elements') . ' (s)',
                'type' => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-tabs-title-transition: {{SIZE}}s',
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tabs_title_active',
            [
                'label' => esc_html__('Active', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'tabs_title_background_color_active',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => "{$heading_selector} > .ui-e-tab-title[aria-selected=\"true\"]",
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => Global_Colors::COLOR_ACCENT,
                        ],
                        'label' => esc_html__('Background Color', 'uicore-elements'),
                        'selectors' => [
                            '{{SELECTOR}}' => 'background: {{VALUE}};',
                        ],
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tabs_title_border_active',
                'selector' => "{$heading_selector} > .ui-e-tab-title[aria-selected=\"true\"]",
                'fields_options' => [
                    'color' => [
                        'label' => esc_html__('Border Color', 'uicore-elements'),
                    ],
                    'width' => [
                        'label' => esc_html__('Border Width', 'uicore-elements'),
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tabs_title_box_shadow_active',
                'label' => esc_html__('Shadow', 'uicore-elements'),
                'selector' => "{$heading_selector} > .ui-e-tab-title[aria-selected=\"true\"]",
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'tabs_pill',
            [
                'label' => esc_html__('Pill Style', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'prefix_class' => 'ui-e-pill-tabs-',
                'separator' => 'before',
                'device_args' => [
                    'desktop' => [
                        'condition' => [
                            'tabs_direction' => ['block-start', 'block-end'],
                        ],
                    ],
                    'mobile' => [
                        'condition' => [
                            'tabs_direction' => ['block-start', 'block-end'],
                        ]
                    ],
                    'tablet' => [
                        'condition' => [
                            'tabs_direction' => ['block-start', 'block-end'],
                        ]
                    ]
                ],
            ]
        );

        $this->add_responsive_control(
            'tabs_title_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    "{$heading_selector}" => '--ui-e-tab-border-top: {{TOP}}{{UNIT}}; --ui-e-tab-border-right: {{RIGHT}}{{UNIT}}; --ui-e-tab-border-bot: {{BOTTOM}}{{UNIT}}; --ui-e-tab-border-left: {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding',
            [
                'label' => esc_html__('Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
                'selectors' => [
                    "{$heading_selector} > .ui-e-tab-title" => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_title_style',
            [
                'label' => esc_html__('Titles', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name' => 'title_typography',
            'global' => [
                'default' => Global_Typography::TYPOGRAPHY_ACCENT,
            ],
            'selector' => "{$heading_selector} .ui-e-tab-texts span",
        ]);

        $this->start_controls_tabs('title_style');

        $this->start_controls_tab(
            'title_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_text_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    "{$heading_selector} > .ui-e-tab-title[aria-selected=\"false\"] .ui-e-tab-texts > span" => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'title_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_text_color_hover',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    "{$heading_selector} > .ui-e-tab-title[aria-selected=\"false\"]:hover .ui-e-tab-texts > span" => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'title_active',
            [
                'label' => esc_html__('Active', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_text_color_active',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    "{$heading_selector} > .ui-e-tab-title[aria-selected=\"true\"] .ui-e-tab-texts > span" => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_description_style',
            [
                'label' => esc_html__('Description', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name' => 'description_typography',
            'global' => [
                'default' => Global_Typography::TYPOGRAPHY_ACCENT,
            ],
            'selector' => "{$heading_selector} .ui-e-tab-texts p",
        ]);

        $this->add_responsive_control(
            'description_spacing',
            [
                'label' => esc_html__('Top Spacing', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                    '%' => [
                        'max' => 50,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 0,
                    'unit' => 'px',
                ],
                'size_units' => ['px', 'em', 'rem', '%'],
                'selectors' => [
                    "{$heading_selector} .ui-e-tab-texts > p" => 'margin-top: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->start_controls_tabs('description_style');

        $this->start_controls_tab(
            'description_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'description_text_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    "{$heading_selector} > .ui-e-tab-title[aria-selected=\"false\"] .ui-e-tab-texts > p" => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'description_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'description_text_color_hover',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    "{$heading_selector} > .ui-e-tab-title[aria-selected=\"false\"]:hover .ui-e-tab-texts > p" => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'description_active',
            [
                'label' => esc_html__('Active', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'description_text_color_active',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    "{$heading_selector} > .ui-e-tab-title[aria-selected=\"true\"] .ui-e-tab-texts > p" => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'icon_section_style',
            [
                'label' => esc_html__('Icon', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control('icon_size', [
            'label' => esc_html__('Size', 'uicore-elements'),
            'type' => Controls_Manager::SLIDER,
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
            'size_units' => ['px', 'em', 'rem', 'vw', 'custom'],
            'selectors' => [
                '{{WRAPPER}}' => '--ui-e-tabs-icon-size: {{SIZE}}{{UNIT}}',
            ],
        ]);

        $this->add_responsive_control('icon_spacing', [
            'label' => esc_html__('Spacing', 'uicore-elements'),
            'type' => Controls_Manager::SLIDER,
            'range' => [
                'px' => [
                    'max' => 200,
                ],
                'vw' => [
                    'max' => 50,
                    'step' => 0.1,
                ],
            ],
            'size_units' => ['px', 'em', 'rem', 'vw', 'custom'],
            'selectors' => [
                '{{WRAPPER}} .ui-e-tab-title' => 'gap: {{SIZE}}{{UNIT}}',
            ],
        ]);

        $this->add_control(
            'icon_padding',
            [
                'label' => esc_html__('Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-tab-title .ui-e-tab-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'icon_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-tab-title .ui-e-tab-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'icon_border',
                'selector' => '{{WRAPPER}} .ui-e-tab-title .ui-e-tab-icon',
            ]
        );

        $this->start_controls_tabs('icon_style_states');

        $this->start_controls_tab(
            'icon_section_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control('icon_color', [
            'label' => esc_html__('Color', 'uicore-elements'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ui-e-tab-title .ui-e-tab-icon > i' => 'color: {{VALUE}}',
                '{{WRAPPER}} .ui-e-tab-title .ui-e-tab-icon > svg' => 'fill: {{VALUE}}',
            ],
        ]);

        $this->add_control(
            'icon_background_color',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-tab-title .ui-e-tab-icon' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'icon_section_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control('icon_color_hover', [
            'label' => esc_html__('Color', 'uicore-elements'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ui-e-tab-title:hover .ui-e-tab-icon > i' => 'color: {{VALUE}}',
                '{{WRAPPER}} .ui-e-tab-title:hover .ui-e-tab-icon > svg' => 'fill: {{VALUE}}',
            ],
        ]);

        $this->add_control(
            'icon_background_color_hover',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-tab-title:hover .ui-e-tab-icon' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'icon_section_active',
            [
                'label' => esc_html__('Active', 'uicore-elements'),
            ]
        );

        $this->add_control('icon_color_active', [
            'label' => esc_html__('Color', 'uicore-elements'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ui-e-tab-title[aria-selected="true"] .ui-e-tab-icon > i' => 'color: {{VALUE}}',
                '{{WRAPPER}} .ui-e-tab-title[aria-selected="true"] .ui-e-tab-icon > svg' => 'fill: {{VALUE}}',
            ],
        ]);

        $this->add_control(
            'icon_background_color_active',
            [
                'label' => esc_html__('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-tab-title[aria-selected="true"] .ui-e-tab-icon' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('section_box_style', [
            'label' => esc_html__('Content', 'uicore-elements'),
            'tab' => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'box_background_color',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => $content_selector,
                'fields_options' => [
                    'color' => [
                        'label' => esc_html__('Background Color', 'uicore-elements'),
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'box_border',
                'selector' => $content_selector,
                'fields_options' => [
                    'color' => [
                        'label' => esc_html__('Border Color', 'uicore-elements'),
                    ],
                    'width' => [
                        'label' => esc_html__('Border Width', 'uicore-elements'),
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'box_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    $content_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_box_shadow',
                'selector' => $content_selector,
            ]
        );

        $this->add_responsive_control(
            'box_padding',
            [
                'label' => esc_html__('Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
                'selectors' => [
                    $content_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function maybe_render_tab_icons_html($item_settings): void
    {
        $icon_settings = $item_settings['item']['tab_icon'];

        if (empty($icon_settings['value'])) {
            return;
        }

        $active_icon_settings = $this->is_active_icon_exist($item_settings['item'])
            ? $item_settings['item']['tab_icon_active']
            : $icon_settings;
?>
        <span <?php $this->print_render_attribute_string('tab-icon'); ?>>
            <?php Icons_Manager::render_icon($active_icon_settings, ['aria-hidden' => 'true']); ?>
            <?php Icons_Manager::render_icon($icon_settings, ['aria-hidden' => 'true']); ?>
        </span>
    <?php
    }

    protected function render_tab_titles_html($item_settings): string
    {
        // Data
        $setting_key = $this->get_repeater_setting_key('tab_title', 'tabs', $item_settings['index']);
        $title = $item_settings['item']['tab_title'];
        $description = $item_settings['item']['tab_description'];
        // Classes
        $hover_animation = $item_settings['hover_animation'] ? 'elementor-animation-' . $item_settings['hover_animation'] : '';
        $toggle_description = $item_settings['show_description'] ? 'ui-e-toggle-description' : '';

        $this->add_render_attribute($setting_key, [
            'id' => $item_settings['tab_id'],
            'class' => [
                'ui-e-tab-title',
                $toggle_description,
                $hover_animation
            ],
            'aria-selected' => (1 === (int)$item_settings['tab_count'] ? 'true' : 'false'),
            'data-tab-index' => $item_settings['tab_count'],
            'role' => 'tab',
            'tabindex' => (1 === $item_settings['tab_count'] ? '0' : '-1'),
            'aria-controls' => $item_settings['container_id'],
            'style' => '--ui-e-tabs-order: ' . $item_settings['tab_count'] . ';',
        ]);

        $render_attributes = $this->get_render_attribute_string($setting_key);
        $text_class = $this->get_render_attribute_string('tab-texts');

        ob_start();
    ?>
        <button <?php echo $render_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>>
            <?php $this->maybe_render_tab_icons_html($item_settings); ?>
            <div <?php echo $text_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>>

                <?php if (!empty($title)) : ?>
                    <span> <?php echo wp_kses_post($title); ?> </span>
                <?php endif; ?>

                <?php if (!empty($description)) : ?>
                    <p> <?php echo wp_kses_post($description); ?> </p>
                <?php endif; ?>

            </div>
        </button>
    <?php
        return ob_get_clean();
    }

    protected function render_tab_containers_html($item_settings): string
    {
        ob_start();
        $this->print_child($item_settings['index'], $item_settings);
        return ob_get_clean();
    }

    private function is_active_icon_exist($item)
    {
        return array_key_exists('tab_icon_active', $item) && ! empty($item['tab_icon_active']) && ! empty($item['tab_icon_active']['value']);
    }

    /**
     * Print the content area.
     *
     * @param int $index
     * @param array $item_settings
     */
    public function print_child($index, $item_settings = [])
    {
        $children = $this->get_children();
        $child_ids = [];
        if (empty($children)) {
            return;
        }
        foreach ($children as $child) {
            $child_ids[] = $child->get_id();
        }

        // Add data-tab-index attribute to the content area.
        $add_attribute_to_container = function ($should_render, $container) use ($item_settings, $child_ids) {
            if (in_array($container->get_id(), $child_ids)) {
                $this->add_attributes_to_container($container, $item_settings);
            }

            return $should_render;
        };

        add_filter('elementor/frontend/container/should_render', $add_attribute_to_container, 10, 3);
        $children[$index]->print_element();
        remove_filter('elementor/frontend/container/should_render', $add_attribute_to_container);
    }

    protected function add_attributes_to_container($container, $item_settings)
    {
        $container->add_render_attribute('_wrapper', [
            'id' => $item_settings['container_id'],
            'role' => 'tabpanel',
            'aria-labelledby' => $item_settings['tab_id'],
            'data-tab-index' => $item_settings['tab_count'],
            'style' => '--ui-e-tabs-order: ' . $item_settings['tab_count'] . ';',
            'class' => 0 === $item_settings['index'] ? 'e-active' : '',
        ]);
    }

    protected function render()
    {

        if (Plugin::$instance->experiments->is_feature_active('nested-elements') == false) {
            $this->nesting_fallback();
            return;
        }

        $settings = $this->get_settings_for_display();
        $widget_number = substr($this->get_id_int(), 0, 3);

        $this->add_render_attribute('elementor-tabs', [
            'class' => 'ui-e-tabs',
            'data-widget-number' => $widget_number,
            'aria-label' => esc_html__('Tabs. Open items with Enter or Space, close with Escape and navigate using the Arrow keys.', 'uicore-elements'),
        ]);

        $this->add_render_attribute('tab-texts', 'class', 'ui-e-tab-texts');
        $this->add_render_attribute('tab-icon', 'class', 'ui-e-tab-icon');
        $this->add_render_attribute('tab-icon-active', 'class', ['ui-e-tab-icon']);

        $tab_titles_html = '';
        $tab_containers_html = '';

        foreach ($settings['tabs'] as $index => $item) {
            $tab_count = $index + 1;

            $tab_id = empty($item['tab_id'])
                ? 'ui-e-tab-title-' . $widget_number . $tab_count
                : $item['tab_id'];

            $item_settings = [
                'index' => $index,
                'tab_count' => $tab_count,
                'tab_id' => $tab_id,
                'container_id' => 'ui-e-tab-content-' . $widget_number . $tab_count,
                'widget_number' => $widget_number,
                'hover_animation' => $settings['hover_animation'],
                'show_description' => $settings['show_description'],
                'item' => $item,
                'settings' => $settings,
            ];

            $tab_titles_html .= $this->render_tab_titles_html($item_settings);
            $tab_containers_html .= $this->render_tab_containers_html($item_settings);
        }
    ?>
        <div <?php $this->print_render_attribute_string('elementor-tabs'); ?>>
            <div class="ui-e-tabs-heading" role="tablist">
                <?php echo $tab_titles_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
            </div>
            <div class="ui-e-tabs-content">
                <?php echo $tab_containers_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
            </div>
        </div>
    <?php
    }

    protected function get_initial_config(): array
    {
        if (Plugin::$instance->experiments->is_feature_active('e_nested_atomic_repeaters')) {
            return array_merge(parent::get_initial_config(), [
                'support_improved_repeaters' => true,
                'target_container' => ['.ui-e-tabs-heading'],
                'node' => 'button',
            ]);
        }

        return parent::get_initial_config();
    }
    /*
	protected function content_template_single_repeater_item() {
		?>
		<#
		const tabCount = view.collection.length + 1,
			elementUid = view.getIDInt().toString().substring( 0, 3 ) + tabCount,
			tabIcon = elementor.helpers.renderIcon( view, data.tab_icon, { 'aria-hidden': true }, 'i' , 'object' );

		let tabActiveIcon = tabIcon,
			tabId = 'ui-e-tab-title-' + elementUid;

		if ( '' !== data.tab_icon_active.value ) {
			tabActiveIcon = elementor.helpers.renderIcon( view, data.tab_icon_active, { 'aria-hidden': true }, 'i' , 'object' );
		}

		const tabWrapperKey = {
			'id': 'ui-e-tab-title-' + elementUid,
			'class': [ 'ui-e-tab-title' ],
			'data-tab-index': tabCount,
			'role': 'tab',
			'aria-selected': 1 === tabCount ? 'true' : 'false',
			'tabindex': 1 === tabCount ? '0' : '-1',
			'aria-controls': 'ui-e-tab-content-' + elementUid,
			'style': '--ui-e-tabs-order: ' + tabCount + ';',
		};

		const tabIconKey = {
			'class': [ 'ui-e-tab-icon' ],
			'data-binding-type': 'repeater-item',
			'data-binding-repeater-name': 'tabs',
			'data-binding-setting': [ data.tab_icon.value, data.tab_icon_active.value ],
			'data-binding-index': tabCount,
		};

		const tabTitleKey = {
			'class': [ 'ui-e-tab-title-text' ],
			'data-binding-type': 'repeater-item',
			'data-binding-repeater-name': 'tabs',
			'data-binding-setting': [ 'tab_title' ],
			'data-binding-index': tabCount,
		};

		view.addRenderAttribute( 'button-container', tabWrapperKey, null, true );
		view.addRenderAttribute( 'tab-title-container', tabTitleKey, null, true );
		view.addRenderAttribute( 'tab-icon-key-container', tabIconKey, null, true );
		#>

		<button {{{ view.getRenderAttributeString( 'button-container' ) }}}>
			<# if ( tabIcon.value ) { #>
				<span {{{ view.getRenderAttributeString( 'tab-icon-key-container' ) }}}> {{{ tabIcon.value }}}{{{ tabActiveIcon.value }}} </span>
			<# } #>
			<span {{{ view.getRenderAttributeString( 'tab-title-container' ) }}}> {{{ data.tab_title }}} </span>
		</button>
		<?php
	}
	*/
    /*
	protected function content_template_single_repeater_item() {
		?>
		<#
		const tabIndex = view.collection.length,
			elementUid = view.getIDInt().toString(),
			item = data,
			hoverAnimationSetting = view?.container?.settings?.attributes?.hover_animation;
			hoverAnimationClass = hoverAnimationSetting
				? `elementor-animation-${ hoverAnimationSetting }`
				: '';
		#>
		<?php $this->content_template_single_item( '{{ tabIndex }}', '{{ item }}', '{{ elementUid }}', '{{ hoverAnimationClass }}' );
	}
	*/
    protected function content_template()
    {

        if (Plugin::$instance->experiments->is_feature_active('nested-elements') == false) {
            return;
        }

    ?>
        <# const elementUid=view.getIDInt().toString().substr( 0, 3 ); #>
            <div class="ui-e-tabs" data-widget-number="{{ elementUid }}" aria-label="<?php echo esc_html__('Tabs. Open items with Enter or Space, close with Escape and navigate using the Arrow keys.', 'uicore-elements'); ?>">
                <# if ( settings['tabs'] ) { #>
                    <div class="ui-e-tabs-heading" role="tablist">
                        <# _.each( settings['tabs'], function( item, index ) {
                            const tabCount=index + 1,
                            tabUid=elementUid + tabCount,
                            tabWrapperKey=tabUid,
                            tabTitleKey='tab-title-' + tabUid,
                            tabIconKey='tab-icon-' + tabUid,
                            tabIcon=elementor.helpers.renderIcon( view, item.tab_icon, { 'aria-hidden' : true }, 'i' , 'object' ),
                            hoverAnimationClass=settings['hover_animation'] ? `elementor-animation-${ settings['hover_animation'] }` : '' ,
                            showDescription=settings['show_description'] ? 'ui-e-toggle-description' : '' ;

                            let tabActiveIcon=tabIcon,
                            tabId='ui-e-tab-title-' + tabUid;

                            if ( '' !==item.tab_icon_active.value ) {
                            tabActiveIcon=elementor.helpers.renderIcon( view, item.tab_icon_active, { 'aria-hidden' : true }, 'i' , 'object' );
                            }

                            if ( '' !==item.tab_id ) {
                            tabId=item.tab_id;
                            }

                            view.addRenderAttribute( tabWrapperKey, { 'id' : tabId, 'class' : [ 'ui-e-tab-title' , hoverAnimationClass, showDescription ], 'data-tab-index' : tabCount, 'role' : 'tab' , 'aria-selected' : 1===tabCount ? 'true' : 'false' , 'tabindex' : 1===tabCount ? '0' : '-1' , 'aria-controls' : 'ui-e-tab-content-' + tabUid, 'style' : '--ui-e-tabs-order: ' + tabCount + ';' ,
                            } );

                            view.addRenderAttribute( tabTitleKey, { 'class' : [ 'ui-e-tab-texts' ], 'data-binding-type' : 'repeater-item' , 'data-binding-repeater-name' : 'tabs' , 'data-binding-setting' : [ 'tab_title' ], 'data-binding-index' : tabCount,
                            } );

                            view.addRenderAttribute( tabIconKey, { 'class' : [ 'ui-e-tab-icon' ], 'data-binding-type' : 'repeater-item' , 'data-binding-repeater-name' : 'tabs' , 'data-binding-setting' : [ 'tab_icon.value' , 'tab_icon_active.value' ], 'data-binding-index' : tabCount,
                            } );
                            #>
                            <button {{{ view.getRenderAttributeString( tabWrapperKey ) }}}>
                                <# if ( tabIcon.value ) { #>
                                    <span {{{ view.getRenderAttributeString( tabIconKey ) }}}>{{{ tabActiveIcon.value }}}{{{ tabIcon.value }}}</span>
                                    <# } #>
                                        <div {{{ view.getRenderAttributeString( tabTitleKey ) }}}>
                                            <# if ( item.tab_title ) { #>
                                                <span> {{{ item.tab_title }}} </span>
                                                <# } #>
                                                    <# if ( item.tab_description ) { #>
                                                        <p> {{{ item.tab_description }}} </p>
                                                        <# } #>
                                        </div>
                            </button>
                            <# } ); #>
                    </div>
                    <div class="ui-e-tabs-content"></div>
                    <# } #>
            </div>
    <?php
    }
    /*
	private function content_template_single_item( $tab_index, $item, $element_uid, $hover_animation_class ) {
		?>
		<#
		const tabCount = tabIndex + 1,
			tabId = item.tab_id
				? item.tab_id
				: 'ui-e-tab-title-' + elementUid + ( tabIndex + 1 ),
			tabUid = elementUid + tabCount,
			tabIcon = elementor.helpers.renderIcon( view, item.tab_icon, { 'aria-hidden': true }, 'i' , 'object' ),
			activeTabIcon = item.tab_icon_active.value
				? elementor.helpers.renderIcon( view, item.tab_icon_active, { 'aria-hidden': true }, 'i' , 'object' )
				: tabIcon,
			escapedHoverAnimationClass = _.escape( hoverAnimationClass );

		view.addRenderAttribute( 'tab-title', {
			'id': tabId,
			'class': [ 'ui-e-tab-title',escapedHoverAnimationClass ],
			'data-tab-index': tabCount,
			'role': 'tab',
			'aria-selected': 1 === tabCount ? 'true' : 'false',
			'tabindex': 1 === tabCount ? '0' : '-1',
			'aria-controls': 'ui-e-tab-content-' + tabUid,
			'style': '--ui-e-tabs-order: ' + tabCount + ';',
		}, null, true );

		view.addRenderAttribute( 'tab-title-text', {
			'class': [ 'ui-e-tab-title-text' ],
			'data-binding-type': 'repeater-item',
			'data-binding-repeater-name': 'tabs',
			'data-binding-setting': [ 'tab_title' ],
			'data-binding-index': tabCount,
			'data-binding-dynamic': 'true',
		}, null, true );

		view.addRenderAttribute( 'tab-icon', {
			'class': [ 'ui-e-tab-icon' ],
			'data-binding-type': 'repeater-item',
			'data-binding-repeater-name': 'tabs',
			'data-binding-setting': [ 'tab_icon', 'tab_icon_active' ],
			'data-binding-index': tabCount,
		}, null, true );
		#>

		<button {{{ view.getRenderAttributeString( 'tab-title' ) }}}>
			<# if ( !! item.tab_icon.value ) { #>
			<span {{{ view.getRenderAttributeString( 'tab-icon' ) }}}>{{{ tabIcon.value }}}{{{ activeTabIcon.value }}}</span>
			<# } #>

			<span {{{ view.getRenderAttributeString( 'tab-title-text' ) }}}>{{{ item.tab_title }}}</span>
		</button>
		<?php
	}
	*/
}
\Elementor\Plugin::instance()->widgets_manager->register(new Tabs());
