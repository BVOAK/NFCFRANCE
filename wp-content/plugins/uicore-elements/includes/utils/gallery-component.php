<?php

namespace UiCoreElements\Utils;

use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Repeater;
use Elementor\Control_Media;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use UiCoreElements\Helper;

use UiCoreElements\Utils\Carousel_Trait;


defined('ABSPATH') || exit();

/**
 * Gallery Component
 *
 * @since 1.0.14
 */


trait Gallery_Trait
{

    use Carousel_Trait;

    // Controls Registration
    function TRAIT_register_gallery_repeater_controls($title)
    {
        $this->start_controls_section(
            'content_section',
            [
                /* translators: %s: Control title */
                'label' => esc_html(sprintf('%s', $title), 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();
        $repeater->add_control(
            'item_image',
            [
                'label' => esc_html__('Image', 'uicore-elements'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );
        $repeater->add_control(
            'item_url',
            [
                'label' => esc_html__('Link', 'uicore-elements'),
                'type' => Controls_Manager::URL,
                'placeholder' => 'https://your-link.com',
            ]
        );
        $repeater->add_control(
            'item_title',
            [
                'label' => esc_html__('Title', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Title', 'uicore-elements'),
                'label_block' => true,
            ]
        );
        $repeater->add_control(
            'item_description',
            [
                'label' => esc_html__('Description', 'uicore-elements'),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
            ]
        );
        $repeater->add_control(
            'item_badge',
            [
                'label' => esc_html__('Badge', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );
        $repeater->add_control(
            'item_tags',
            [
                'label' => esc_html__('Tags', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'description' => esc_html__('Separate tags with commas', 'uicore-elements'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'gallery_items',
            [
                'label' => esc_html__('Items', 'uicore-elements'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'item_title' => esc_html__('Item #1', 'uicore-elements'),
                    ],
                    [
                        'item_title' => esc_html__('Item #2', 'uicore-elements'),
                    ],
                    [
                        'item_title' => esc_html__('Item #3', 'uicore-elements'),
                    ],
                    [
                        'item_title' => esc_html__('Item #4', 'uicore-elements'),
                    ],
                    [
                        'item_title' => esc_html__('Item #5', 'uicore-elements'),
                    ],
                    [
                        'item_title' => esc_html__('Item #6', 'uicore-elements'),
                    ],
                ],
                'title_field' => '{{{ item_title }}}',
            ]
        );

        $this->end_controls_section();
    }
    function TRAIT_register_additional_controls($carousel = false)
    {
        $this->start_controls_section(
            'additional_section',
            [
                'label' => esc_html__('Additional', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'layout',
            [
                'label' => esc_html__('Layout', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'prefix_class' => '',
                'render_type' => 'template',
                'options' => [
                    '' => esc_html__('Default', 'uicore-elements'),
                    'ui-e-overlay' => esc_html__('Overlay', 'uicore-elements'),
                ],
            ]
        );

        $this->add_control(
            'item_overflow',
            [
                'label' => esc_html__('Hide overflow', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
                'prefix_class' => 'ui-e-overflow-',
                'description' => __('Useful if you have an animation, on an image, for example, that surpasses the item area and you want to hide it.', 'uicore-elements'),
                'condition' => [
                    'layout' => '',
                ]
            ]
        );

        $this->add_control(
            'hide_tags',
            [
                'label'   => __('Hide item tags', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'separator' => 'before',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'hide_title',
            [
                'label'   => __('Hide title', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );

        $this->add_control(
            'hide_description',
            [
                'label'   => __('Hide description', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );

        if (! $carousel) {
            $this->add_control(
                'filters',
                [
                    'label'   => __('Use filters', 'uicore-elements'),
                    'type'    => Controls_Manager::SWITCHER,
                    'default' => 'no',
                ]
            );
            $this->add_control(
                'clear_text',
                [
                    'label'   => __('Clear Text', 'uicore-elements'),
                    'type'    => Controls_Manager::TEXT,
                    'default' => __('All', 'uicore-elements'),
                    'placeholder' => __('Clear filter text', 'uicore-elements'),
                    'condition' => [
                        'filters' => 'yes'
                    ]
                ]
            );
        }

        $this->add_control(
            'title_tag',
            [
                'label' => esc_html__('Title Tag', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'h4',
                'options' => Helper::get_title_tags(),
                'separator' => 'before'
            ]
        );
        $this->add_control(
            'image_size',
            [
                'label' => esc_html__('Image Size', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'uicore-medium',
                'options' => Helper::get_images_sizes(),
            ]
        );
        $this->add_control(
            'content_position',
            [
                'label' => esc_html__('Content Position', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'top',
                'prefix_class' => 'ui-e-content-',
                'options' => [
                    'top' => esc_html__('Top', 'uicore-elements'),
                    'center' => esc_html__('Center', 'uicore-elements'),
                    'bottom' => esc_html__('Bottom', 'uicore-elements'),
                ],
                'condition' => [
                    'layout' => 'ui-e-overlay',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-content' => 'text-align:' . is_rtl() ? 'right' : 'left' . ';',
                    '{{WRAPPER}} .ui-e-title-wrapper' => 'justify-content:' . is_rtl() ? 'right' : 'left' . ';',
                ],
            ]
        );
        $this->add_control(
            'badge_position',
            [
                'label' => esc_html__('Badge Position', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'ui-e-badge-start-left',
                'prefix_class' => '',
                'render_type' => 'template', // without it, loading a page with image and changing to title won't work
                'options' => [
                    '' => __('After title', 'uicore-elements'),
                    'ui-e-badge-start-left' => __('Image top left', 'uicore-elements'),
                    'ui-e-badge-start-right' => __('Image top right', 'uicore-elements'),
                    'ui-e-badge-end-left' => __('Image bottom left', 'uicore-elements'),
                    'ui-e-badge-end-right' => __('Image bottom right', 'uicore-elements'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    function TRAIT_register_filters_style_controls()
    {
        $this->start_controls_section(
            'style_filters_section',
            [
                'label' => __('Filters', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filters' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'filters_align',
            [
                'label' => esc_html__('Alignment', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start'    => [
                        'title' => esc_html__('Left', 'uicore-elements'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'uicore-elements'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__('Right', 'uicore-elements'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '(desktop){{WRAPPER}} .ui-e-filters' => 'justify-content: {{VALUE}}',
                    '(mobile){{WRAPPER}} .ui-e-filters' => 'align-items: {{VALUE}}' // mobile version sets flex-direction as column
                ]
            ]
        );
        $this->add_control(
            'filters_bottom_space',
            [
                'label'     => __('Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'size' => 10,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-filters' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs(
            'filters_tabs',
            [
                'condition' => [
                    'filters' => 'yes',
                ],
            ]
        );

        $this->start_controls_tab(
            'filters_normal_tab',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'filters_typography',
                'selector' => '{{WRAPPER}} .ui-e-filters button',
            ]
        );
        $this->add_control(
            'filters_color',
            [
                'label'     => __('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-filters button' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'filters_background',
                'selector' => '{{WRAPPER}} .ui-e-filters button',
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'filters_border',
                'label' => esc_html__('Border', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-filters button',
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'filters_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-filters button',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'filters_hover_tab',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'filters_hover_color',
            [
                'label'     => __('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-filters button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'filters_hover_background',
                'selector' => '{{WRAPPER}} .ui-e-filters button:hover',
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'filters_hover_border',
                'label' => esc_html__('Border', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-filters button:hover',
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'filters_hover_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-filters button:hover',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'filters_active_tab',
            [
                'label' => esc_html__('Active', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'filters_active_color',
            [
                'label'     => __('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-filters button.ui-e-active' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'filters_active_background',
                'selector' => '{{WRAPPER}} .ui-e-filters button.ui-e-active',
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'filters_active_border',
                'label' => esc_html__('Border', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-filters button.ui-e-active',
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'filters_active_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-filters button.ui-e-active',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'filters_separator',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        $this->add_control(
            'filters_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-filters button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'filters_padding',
            [
                'label' => esc_html__('Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-filters button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }
    function TRAIT_register_image_style_controls($is_carousel = false)
    {
        // Condition used by both image height and the image section
        $condition = [
            'relation' => 'or',
            'terms' => [
                [
                    'name' => 'layout',
                    'operator' => '==',
                    'value' => '',
                ],
                [
                    'relation' => 'and',
                    'terms' => [
                        [
                            'name' => 'layout',
                            'operator' => '==',
                            'value' => 'ui-e-overlay',
                        ],
                        [
                            'name' => 'masonry',
                            'operator' => '!==',
                            'value' => 'yes',
                        ]
                    ],
                ],
            ],
        ];

        $this->start_controls_section(
            'style_image_section',
            [
                'label' => __('Image', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'conditions' => $condition
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label' => esc_html__('Image Height', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'vh'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 500,
                        'step' => 5,
                    ],
                    'em' => [
                        'min' => 5,
                        'max' => 30,
                        'step' => 1,
                    ],
                    'vh' => [
                        'min' => 5,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 400,
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-img-height: {{SIZE}}{{UNIT}};',
                ],
                'conditions' => $condition
            ]
        );

        $this->add_control(
            'image_bottom_space',
            [
                'label'     => __('Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'size' => 10,
                    'unit' => 'px'
                ],
                'condition' => [
                    'layout' => ''
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-image-spacing: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'image_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'condition' => [
                    'layout' => ''
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-item img',
                'condition' => [
                    'layout' => ''
                ]
            ]
        );

        $this->end_controls_section();
    }
    function TRAIT_register_title_style_controls()
    {
        $this->start_controls_section(
            'style_title_section',
            [
                'label' => __('Title', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'hide_title!' => 'yes',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .ui-e-title',
            ]
        );
        $this->add_control(
            'title_color',
            [
                'label'     => __('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'title_hover_color',
            [
                'label'     => __('Hover Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item:hover .ui-e-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'title_bottom_space',
            [
                'label'     => __('Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'size' => 10,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-title-spacing: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }
    function TRAIT_register_description_style_controls()
    {
        $this->start_controls_section(
            'style_description_section',
            [
                'label' => __('Description', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'hide_description!' => 'yes',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'description_typography',
                'selector' => '{{WRAPPER}} .ui-e-description',
            ]
        );
        $this->add_control(
            'description_color',
            [
                'label'     => __('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-description' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'description_bottom_space',
            [
                'label'     => __('Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'size' => 10,
                    'unit' => 'px'
                ],
                'condition' => [
                    'hide_tags!' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-description' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }
    function TRAIT_register_tags_style_controls()
    {
        $this->start_controls_section(
            'style_tags_section',
            [
                'label' => __('Tags', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'hide_tags!' => 'yes',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'tags_typography',
                'selector' => '{{WRAPPER}} .ui-e-tags',
            ]
        );
        $this->add_control(
            'tags_color',
            [
                'label'     => __('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-tags' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }
    function TRAIT_register_badge_style_controls()
    {
        $this->start_controls_section(
            'style_badge_section',
            [
                'label' => __('Badge', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .ui-e-badge',
            ]
        );
        $this->add_control(
            'badge_color',
            [
                'label'     => __('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-badge' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'badge_background_color',
            [
                'label'     => __('Background Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-badge' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'badge_border',
                'label' => esc_html__('Border', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-badge',
            ]
        );
        $this->add_control(
            'badge_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 4,
                    'right' => 4,
                    'bottom' => 4,
                    'left' => 4,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'badge_padding',
            [
                'label' => esc_html__('Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 3,
                    'right' => 10,
                    'bottom' => 3,
                    'left' => 10,
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'badge_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-badge',
            ]
        );

        $this->end_controls_section();
    }
    function TRAIT_register_gallery_animations()
    {
        $this->start_controls_section(
            'items_animation',
            [
                'label' => esc_html__('Animation', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->TRAIT_register_entrance_animations_controls(); // animate each item
        $this->TRAIT_register_hover_animation_control(
            'Item Hover Animation',
            [],
            ['underline']
        );
        $this->TRAIT_register_hover_animation_control(
            'Image Animation',
            ['layout' => ''],
            ['underline']
        );
        // TODO: update all controls below after 'show' is globally implemented
        $this->TRAIT_register_hover_animation_control(
            'Title Animation',
            ['hide_title!' => 'yes'],
            ['zoom'],
            null,
            true,
            'ui-e-animated-title-'
        );
        $this->TRAIT_register_hover_animation_control(
            'Description Animation',
            ['hide_description!' => 'yes'],
            ['zoom', 'underline'],
            null,
            true,
            'ui-e-animated-description-'
        );
        $this->TRAIT_register_hover_animation_control(
            'Badge Animation',
            [],
            ['underline'],
            null,
            true
        );
        $this->TRAIT_register_hover_animation_control(
            'Tags Animation',
            ['hide_tags!' => 'yes'],
            ['zoom'],
            null,
            true
        );

        $this->end_controls_section();
    }

    // Elements rendering
    protected function TRAIT_render_gallery($settings, $is_carousel = false)
    {
        // Get entrance and item hover animation classes
        $entrance   = $this->is_option('animate_items', 'ui-e-grid-animate') ? 'elementor-invisible' : '';
        $hover      = isset($settings['item_hover_animation']) ? $settings['item_hover_animation'] : null;
        $animations = sprintf('%s %s', $entrance, $hover);

        // Get all elements animation control options (triggered by .ui-e-animations-wrp:hover)
        $animation_options = [
            $settings['title_animation'],
            $settings['description_animation'],
            $settings['image_animation'],
            $settings['badge_animation'],
            $settings['tags_animation'],
        ];

        // get item animation only if is not slider,
        if (!str_contains($this->get_name(), 'slider')) {
            $animation_options[] = $settings['item_hover_animation'];
        }

        // check if any of the animation are set
        $has_animation = array_filter($animation_options, function ($value) {
            return $value !== '';
        });

        // and also check if entrance is set
        $has_animation = $entrance !== '' ? true : $has_animation;

        //
        $item_classes = $is_carousel
            ? 'ui-e-wrp swiper-slide'
            : 'ui-e-wrp ui-e-filtered';
        $this->add_render_attribute('item_wrapper', 'class', $item_classes);

        // Render items
        foreach ($settings['gallery_items'] as $index => $item) {
            $this->render_item($index, $item, $settings, $has_animation, $animations);
        }

        if ($is_carousel) {
            $total_slides = count($settings['gallery_items']);

            // Most recent swiper versions requires, if loop, at least one extra slide compared to visible slides
            if ($this->TRAIT_should_duplicate_slides($total_slides)) {
                $diff = $this->TRAIT_get_duplication_diff($total_slides);
                for ($i = 0; $i <= $diff; $i++) {
                    $this->render_item($index, $item, $settings, $has_animation, $animations);
                }
            }
        }
    }

    protected function render_item($index, $item, $settings, $has_animation, $animations)
    {
        // Params
        $key = 'item_' . $index;
        $tag = 'div';

        $this->add_render_attribute($key, 'class', 'ui-e-item');

        // Build URL
        if (!empty($item['item_url']['url'])) {
            $tag = 'a';
            $this->add_link_attributes($key, $item['item_url']);
        }

?>
        <div <?php $this->print_render_attribute_string('item_wrapper'); ?> data-ui-e-tags="<?php echo esc_attr(sanitize_title($item['item_tags'])); ?>">

            <?php if ($has_animation) : ?>
                <div class='ui-e-animations-wrp <?php echo esc_attr($animations); ?>'>
                <?php endif; ?>

                <<?php echo Helper::esc_tag($tag); ?> <?php $this->print_render_attribute_string($key); ?>>
                    <?php $this->render_image($index, $item, $settings); ?>
                    <?php $this->render_contents($item, $settings); ?>
                </<?php echo Helper::esc_tag($tag); ?>>

                <?php if ($has_animation) : ?>
                </div>
            <?php endif; ?>

        </div>
    <?php
    }

    protected function render_contents($instance, $settings)
    {

        // Content container should only be rendered if at least one of the content elements is available
        if (
            (empty($instance['item_title']) || $this->is_option('hide_title', 'yes')) &
            (empty($instance['item_description']) || $this->is_option('hide_description', 'yes')) &
            (empty($instance['item_tags']) || $this->is_option('hide_tags', 'yes'))
        ) {
            return;
        }

    ?>
        <div class="ui-e-content">
            <?php $this->render_title($instance, $settings); ?>
            <?php $this->render_description($instance, $settings['description_animation']) ?>
            <?php $this->render_tags($instance, $settings); ?>
        </div>
        <?php
    }
    protected function render_image($index, $instance, $settings)
    {

        if (empty($instance['item_image']['url'])) {
            return;
        }


        // Only default layout has media wrapper
        if ($this->is_option('layout', '')) {
        ?> <div class="ui-e-media-wrp <?php echo esc_attr($settings['image_animation']); ?>">
            <?php
        }

        $this->add_render_attribute(
            'image',
            [
                'src' => $instance['item_image']['url'],
                'alt' => Control_Media::get_image_alt($instance['item_image']),
                'title' => Control_Media::get_image_title($instance['item_image']),
                'class' => 'ui-e-img',
            ]
        );

        if (! empty($settings['badge_position'])) {
            $this->render_badge($instance, $settings['badge_animation']);
        }

        echo wp_kses_post(Group_Control_Image_Size::get_attachment_image_html($instance, 'item_image',));

        if ($this->is_option('layout', '')) {
            ?> </div>
        <?php
        }
    }
    protected function render_title($instance, $settings)
    {
        if (empty($instance['item_title']) || $this->is_option('hide_title', 'yes')) {
            return;
        }

        ?>
        <div class="ui-e-title-wrapper">
            <<?php echo Helper::esc_tag($settings['title_tag']); ?> class="ui-e-title <?php echo esc_attr($settings['title_animation']) ?>">
                <span> <?php echo Helper::esc_string($instance['item_title']); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?> </span>
            </<?php echo Helper::esc_tag($settings['title_tag']); ?>>

            <?php
            if (empty($settings['badge_position'])) {
                $this->render_badge($instance, $settings['badge_animation']);
            }
            ?>
        </div>
    <?php
    }
    protected function render_description($instance, $animation)
    {
        if (empty($instance['item_description']) || $this->is_option('hide_description', 'yes')) {
            return;
        }

    ?>
        <p class="ui-e-description <?php echo esc_attr($animation); ?>">
            <?php echo Helper::esc_string($instance['item_description']); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        </p>
    <?php
    }
    protected function render_badge($instance, $animation)
    {
        if (empty($instance['item_badge'])) {
            return;
        }

    ?>
        <span class="ui-e-badge <?php echo esc_attr($animation); ?>">
            <?php echo esc_html($instance['item_badge']); ?>
        </span>
    <?php
    }
    protected function render_tags($instance, $settings)
    {
        if (empty($instance['item_tags']) || $this->is_option('hide_tags', 'yes')) {
            return;
        }

        $tags = explode(',', $instance['item_tags']);

    ?>
        <div class="ui-e-tags <?php echo esc_attr($settings['tags_animation']); ?>">
            <?php foreach ($tags as $tag) : ?>
                <span class="ui-e-tag">
                    <?php
                    echo esc_html($tag);
                    echo end($tags) !== $tag ? ', ' : ''; // print separator if is not last item
                    ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php
    }
    protected function TRAIT_render_gallery_filters($settings)
    {
        if ($this->is_option('filters', 'yes', '!==')) {
            return;
        }

        $filters = [];

        foreach ($settings['gallery_items'] as $item) {

            // tags are comma separated strings
            $tags = explode(',', $item['item_tags']);
            $tags = array_map('trim', $tags);

            foreach ($tags as $tag) {
                // skip duplicates or empty tags
                if (in_array($tag, $filters) || empty($tag)) {
                    continue;
                }

                $filters[] = $tag;
            }
        }

    ?>
        <div class="ui-e-filters">

            <button class="ui-e-filter-item" data-filter="all">
                <?php echo esc_html($settings['clear_text']); ?>
            </button>

            <?php foreach ($filters as $filter) : ?>
                <button class="ui-e-filter-item" data-filter="<?php echo esc_attr(sanitize_title($filter)); ?>">
                    <?php echo esc_html($filter); ?>
                </button>
            <?php endforeach; ?>

        </div>
<?php
    }
}
