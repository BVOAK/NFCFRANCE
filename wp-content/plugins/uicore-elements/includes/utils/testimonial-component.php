<?php

namespace UiCoreElements\Utils;

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Repeater;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use UiCoreElements\Helper;


defined('ABSPATH') || exit();

trait Testimonial_Trait
{

    // Content Controls Functions
    function TRAIT_register_testimonial_repeater_controls($logo)
    {
        $this->start_controls_section(
            'section_reviewer_content',
            [
                /* translators: %s: Control title  */
                'label' => esc_html(sprintf('%s', $logo), 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'avatar',
            [
                'label'       => __('Avatar', 'uicore-elements'),
                'type'        => Controls_Manager::MEDIA,
                'render_type' => 'template',
                'dynamic'     => [
                    'active' => true,
                ],
                'default'     => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );
        $repeater->add_control(
            'reviewer_name',
            [
                'label'       => __('Name', 'uicore-elements'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => [
                    'active' => true,
                ],
                'default'     => __('Adam Smith', 'uicore-elements'),
                'placeholder' => __('Enter reviewer name', 'uicore-elements'),
                'label_block' => true,
            ]
        );
        $repeater->add_control(
            'reviewer_job_title',
            [
                'label'       => __('Job Title', 'uicore-elements'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => [
                    'active' => true,
                ],
                'default'     => __('SEO Expert', 'uicore-elements'),
                'placeholder' => __('Enter reviewer job title', 'uicore-elements'),
                'label_block' => true,
            ]
        );
        $repeater->add_control(
            'rating_number',
            [
                'label' => __('Rating', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'default' => [
                    'size' => 4.5,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 5,
                        'step' => .5,
                    ],
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $repeater->add_control(
            'review_text',
            [
                'label'       => __('Review Text', 'uicore-elements'),
                'type'        => Controls_Manager::WYSIWYG,
                'dynamic'     => [
                    'active' => true,
                ],
                'default'     => __('Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'uicore-elements'),
                'placeholder' => __('Enter review text', 'uicore-elements'),
            ]
        );
        $repeater->add_control(
            'image',
            [
                'label'       => __('Secondary Image', 'uicore-elements'),
                'type'        => Controls_Manager::MEDIA,
                'render_type' => 'template',
                'dynamic'     => [
                    'active' => true,
                ],
                'default'     => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );
        $this->add_control(
            'review_items',
            [
                'show_label'  => false,
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ reviewer_name }}}',
                'default'     => [
                    [
                        'reviewer_name' => __('Adam', 'uicore-elements'),
                        'reviewer_job_title' => __('SEO Expert', 'uicore-elements'),
                    ],
                    [
                        'reviewer_name' => __('Karl', 'uicore-elements'),
                        'reviewer_job_title' => __('Designer', 'uicore-elements'),
                    ],
                    [
                        'reviewer_name' => __('Maria', 'uicore-elements'),
                        'reviewer_job_title' => __('Developer', 'uicore-elements'),
                    ],
                    [
                        'reviewer_name' => __('Michael', 'uicore-elements'),
                        'reviewer_job_title' => __('Manager', 'uicore-elements'),
                    ],
                    [
                        'reviewer_name' => __('Lena', 'uicore-elements'),
                        'reviewer_job_title' => __('CEO', 'uicore-elements'),
                    ],
                    [
                        'reviewer_name' => __('Jennifer', 'uicore-elements'),
                        'reviewer_job_title' => __('Consultant', 'uicore-elements'),
                    ],
                ]
            ]
        );

        $this->end_controls_section();
    }
    function TRAIT_register_testimonial_additional_controls()
    {
        $this->add_control(
            'show_reviewer_name',
            [
                'label'   => __('Show Name', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'review_name_tag',
            [
                'label'   => __('Name HTML Tag', 'uicore-elements'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'h4',
                'options' => Helper::get_title_tags(),
                'condition' => [
                    'show_reviewer_name' => 'yes',
                ]
            ]
        );
        $this->add_control(
            'show_reviewer_job_title',
            [
                'label'   => __('Show Job Title', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'show_reviewer_rating',
            [
                'label'   => __('Show Rating', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before'
            ]
        );
        $this->add_control(
            'rating_type',
            [
                'label'   => __('Rating Type', 'uicore-elements'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'star',
                'options' => [
                    'star'   => __('Star', 'uicore-elements'),
                    'number' => __('Number', 'uicore-elements'),
                ],
                'condition' => [
                    'show_reviewer_rating' => 'yes'
                ]
            ]
        );
        $this->add_control(
            'show_reviewer_text',
            [
                'label'   => __('Show Review Text', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'show_reviewer_image',
            [
                'label'   => __('Show Image', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name'    => 'image_media_size',
                'default' => 'medium',
                'condition' => [
                    'show_reviewer_image' => 'yes'
                ]
            ]
        );
        $this->add_control(
            'show_reviewer_avatar',
            [
                'label'   => __('Show Avatar', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name'    => 'avatar_media_size',
                'default' => 'medium',
                'condition' => [
                    'show_reviewer_avatar' => 'yes'
                ]
            ]
        );
        $this->add_control(
            'image_inline',
            [
                'label'        => esc_html__('Image Inline', 'uicore-elements'),
                'type'         => Controls_Manager::SWITCHER,
                'condition'    => [
                    'show_reviewer_avatar' => 'yes'
                ],
                'prefix_class' => 'ui-e-img-inline-',
                'render_type' => 'template',
                'condition' => [
                    'layout' => ['layout_1', 'layout_2', 'layout_3'],
                    'show_reviewer_avatar' => 'yes'
                ]
            ]
        );
        $this->add_responsive_control(
            'v_alignment',
            [
                'label'     => __('Image Alignment', 'uicore-elements'),
                'type'      => Controls_Manager::CHOOSE,
                'default'   => 'flex-start',
                'toggle' => false,
                'options'   => [
                    'flex-start' => [
                        'title' => __('Top', 'uicore-elements'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => __('Center', 'uicore-elements'),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'flex-end' => [
                        'title' => __('Bottom', 'uicore-elements'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item' => '--ui-e-img-alignment: {{VALUE}};',
                ],
                'condition' => [
                    'layout' => 'layout_6'
                ],
            ]
        );
        $this->add_control(
            'show_reviewer_divider',
            [
                'label'   => __('Show Divider', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before',
                'condition' => [
                    'layout' => 'layout_3'
                ]
            ]
        );
        $this->add_responsive_control(
            'h_alignment',
            [
                'label'     => __('Content Alignment', 'uicore-elements'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'left'    => [
                        'title' => __('Left', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'  => [
                        'title' => __('Center', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right'   => [
                        'title' => __('Right', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-justify',
                    ],
                ],
                'prefix_class' => 'ui-e-h-align-',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item' => 'text-align: {{VALUE}};',
                ],
                'separator' => 'before'
            ]
        );
        $this->add_responsive_control(
            'content_v_alignment',
            [
                'label'     => __('Vertical Alignment', 'uicore-elements'),
                'type'      => Controls_Manager::CHOOSE,
                'default' => 'center',
                'options'   => [
                    'start'    => [
                        'title' => __('Top', 'uicore-elements'),
                        'icon'  => 'eicon-align-start-v',
                    ],
                    'center'  => [
                        'title' => __('Center', 'uicore-elements'),
                        'icon'  => 'eicon-align-center-v',
                    ],
                    'end'   => [
                        'title' => __('Bottom', 'uicore-elements'),
                        'icon'  => 'eicon-align-end-v',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item' => '--ui-e-content-v-alignment: {{VALUE}};',
                ]
            ]
        );
    }

    // Style Controls Functions}
    function register_testimonial_image_controls()
    {
        $this->start_controls_section(
            'section_style_avatar',
            [
                'label' => __('Avatar', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_reviewer_avatar' => 'yes'
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'avatar_border',
                'selector' => '{{WRAPPER}} .ui-e-testimonial-avatar img'
            ]
        );
        $this->add_control(
            'avatar_radius',
            [
                'label'      => esc_html__('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-testimonial-avatar img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'avatar_padding',
            [
                'label'      => __('Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-testimonial-avatar img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'avatar_size',
            [
                'label'     => __('Size', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'default'   => [
                    'size' => 120,
                    'unit' => 'px'
                ],
                'range'     => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 10,
                        'max' => 500,
                    ]
                ],
                'description' => __('Using `%` might make image height fits the available space, depending on layout and inline options, cutting the image. Using `px` althougt adjust the image.', 'uicore-elements'),
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-avatar-size: {{SIZE}}{{UNIT}};',
                ],
                'render_type' => 'template',
                'condition' => [
                    'layout!' => 'layout_5',
                ]
            ]
        );
        $this->add_responsive_control(
            'avatar_size_px',
            [
                'label'     => __('Size', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'default'   => [
                    'size' => 80,
                    'unit' => 'px'
                ],
                'range'     => [
                    'px' => [
                        'min' => 1,
                        'max' => 250,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-avatar-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'layout' => 'layout_5',
                ]
            ]
        );
        $this->add_responsive_control(
            'avatar_spacing',
            [
                'label'      => __('Spacing', 'uicore-elements'),
                'type'       => Controls_Manager::SLIDER,
                'default'    => [
                    'size' => 15,
                ],
                'selectors'   => [
                    '{{WRAPPER}}' => '--ui-e-avatar-spacing: {{SIZE}}{{UNIT}}'
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'avatar_shadow',
                'selector' => '{{WRAPPER}} .ui-e-testimonial-avatar img'
            ]
        );
        $this->add_control(
            'avatar_offset_toggle',
            [
                'label'        => __('Offset', 'uicore-elements'),
                'type'         => Controls_Manager::POPOVER_TOGGLE,
                'label_off'    => __('None', 'uicore-elements'),
                'label_on'     => __('Custom', 'uicore-elements'),
                'return_value' => 'yes',
            ]
        );
        $this->start_popover();

        $this->add_responsive_control(
            'avatar_horizontal_offset',
            [
                'label' => __('Horizontal', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                ],
                'tablet_default' => [
                    'size' => 0,
                ],
                'mobile_default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                ],
                'condition' => [
                    'avatar_offset_toggle' => 'yes'
                ],
                'render_type' => 'ui',
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-avatar-h-offset: {{SIZE}}px;'
                ],
            ]
        );
        $this->add_responsive_control(
            'avatar_vertical_offset',
            [
                'label' => __('Vertical', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                ],
                'tablet_default' => [
                    'size' => 0,
                ],
                'mobile_default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                ],
                'condition' => [
                    'avatar_offset_toggle' => 'yes'
                ],
                'render_type' => 'ui',
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-avatar-v-offset: {{SIZE}}px;'
                ],
            ]
        );
        $this->end_popover();

        $this->end_controls_section();
        $this->start_controls_section(
            'section_style_image',
            [
                'label' => __('Secondary Image', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_reviewer_image' => 'yes'
                ]
            ]
        );
        $this->add_responsive_control(
            'image_size',
            [
                'label'     => __('Size', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'default'   => [
                    'size' => 40,
                    'unit' => 'px'
                ],
                'range'     => [
                    'px' => [
                        'min' => 10,
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-image-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'image_spacing',
            [
                'label'      => __('Spacing', 'uicore-elements'),
                'type'       => Controls_Manager::SLIDER,
                'default'    => [
                    'size' => 15,
                ],
                'selectors'   => [
                    '{{WRAPPER}}' => '--ui-e-img-spacing: {{SIZE}}{{UNIT}}'
                ],
                'condition' => [
                    'layout!' => ['layout_2', 'layout_4', 'layout_6'],
                ]
            ]
        );
        $this->add_control(
            'image_offset_toggle',
            [
                'label'        => __('Offset', 'uicore-elements'),
                'type'         => Controls_Manager::POPOVER_TOGGLE,
                'label_off'    => __('None', 'uicore-elements'),
                'label_on'     => __('Custom', 'uicore-elements'),
                'return_value' => 'yes',
            ]
        );
        $this->start_popover();

        $this->add_responsive_control(
            'image_horizontal_offset',
            [
                'label' => __('Horizontal', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                ],
                'tablet_default' => [
                    'size' => 0,
                ],
                'mobile_default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                ],
                'condition' => [
                    'image_offset_toggle' => 'yes'
                ],
                'render_type' => 'ui',
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-img-h-offset: {{SIZE}}px;'
                ],
            ]
        );
        $this->add_responsive_control(
            'image_vertical_offset',
            [
                'label' => __('Vertical', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                ],
                'tablet_default' => [
                    'size' => 0,
                ],
                'mobile_default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                ],
                'condition' => [
                    'image_offset_toggle' => 'yes'
                ],
                'render_type' => 'ui',
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-img-v-offset: {{SIZE}}px;'
                ],
            ]
        );
        $this->end_popover();
        $this->end_controls_section();
    }
    function register_testimonial_name_controls()
    {
        $this->start_controls_section(
            'section_style_name',
            [
                'label' => __('Name', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_reviewer_name' => 'yes',
                ]
            ]
        );

        $this->add_control(
            'name_color',
            [
                'label'     => __('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-name' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'name_hover_color',
            [
                'label'     => __('Hover Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item:hover .ui-e-testimonial-name' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'name_bottom_space',
            [
                'label'     => __('Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-name' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'name_typography',
                'selector' => '{{WRAPPER}} .ui-e-testimonial-name',
            ]
        );
        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'name_shadow',
                'label' => __('Text Shadow', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .ui-e-testimonial-name',
            ]
        );

        $this->end_controls_section();
    }
    function register_testimonial_job_controls()
    {
        $this->start_controls_section(
            'section_style_job_title',
            [
                'label' => __('Job Title', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_reviewer_job_title' => 'yes',
                ]
            ]
        );

        $this->add_control(
            'job_title_color',
            [
                'label'     => __('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-job-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'job_title_hover_color',
            [
                'label'     => __('Hover Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item:hover .ui-e-testimonial-job-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'job_title_bottom_space',
            [
                'label'     => __('Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default'   => [
                    'size' => 10,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-job-title' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'job_title_typography',
                'selector' => '{{WRAPPER}} .ui-e-testimonial-job-title',
            ]
        );

        $this->end_controls_section();
    }
    function register_testimonial_text_controls()
    {
        $this->start_controls_section(
            'section_style_text',
            [
                'label' => __('Text', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_reviewer_text' => 'yes',
                ]
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label'     => __('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-text' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'text_hover_color',
            [
                'label'     => __('Hover Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item:hover .ui-e-testimonial-text' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'text_typography',
                'selector' => '{{WRAPPER}} .ui-e-testimonial-text',
            ]
        );
        $this->add_control(
            'text_bottom_space',
            [
                'label'     => __('Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default'   => [
                    'size' => 15,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-text' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }
    function register_testimonial_rating_controls()
    {
        $this->start_controls_section(
            'section_style_rating',
            [
                'label'     => esc_html__('Rating', 'uicore-elements'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_reviewer_rating' => 'yes',
                ],
            ]
        );

        // If SVG inline, we only need one color control because the semi-star bg color hack is not enabled with this experiment
        if (Plugin::$instance->experiments->is_feature_active('e_font_icon_svg')) {
            $this->add_control(
                'active_rating_color',
                [
                    'label'     => esc_html__('Color', 'uicore-elements'),
                    'type'      => Controls_Manager::COLOR,
                    'default'   => '#ffab1a',
                    'selectors' => [
                        '{{WRAPPER}} .ui-e-testimonial-rating svg' => 'fill: {{VALUE}};',
                    ],
                    'condition' => [
                        'rating_type' => 'star',
                    ],
                ]
            );
            // Without the experiment, two colors are usefull because <i> elements can be cut and printed as decimal-stars
        } else {
            $this->add_control(
                'active_rating_color',
                [
                    'label'     => esc_html__('Active Color', 'uicore-elements'),
                    'type'      => Controls_Manager::COLOR,
                    'default'   => '#ffab1a',
                    'selectors' => [
                        '{{WRAPPER}} .ui-e-testimonial-rating .ui-e-marked i' => 'color: {{VALUE}};',
                    ],
                    'condition' => [
                        'rating_type' => 'star',
                    ],
                ]
            );
            $this->add_control(
                'rating_color',
                [
                    'label'     => esc_html__('Color', 'uicore-elements'),
                    'type'      => Controls_Manager::COLOR,
                    'default'   => '#ccd6df',
                    'selectors' => [
                        '{{WRAPPER}} .ui-e-testimonial-rating .ui-e-unmarked i' => 'color: {{VALUE}};',
                    ],
                    'condition' => [
                        'rating_type' => 'star',
                    ],
                ]
            );
        }
        $this->add_control(
            'rating_number_color',
            [
                'label'     => esc_html__('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-rating' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'rating_type' => 'number',
                ],
            ]
        );
        $this->add_control(
            'rating_background',
            [
                'label' => __('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-rating' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'rating_type' => 'star',
                ],
            ]
        );
        $this->add_control(
            'rating_number_background',
            [
                'label' => __('Background Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-rating' => 'background-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
                'condition' => [
                    'rating_type' => 'number',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'rating_border',
                'selector' => '{{WRAPPER}} .ui-e-testimonial-rating',
            ]
        );
        $this->add_control(
            'rating_border_radius',
            [
                'label' => __('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-rating' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'rating_padding',
            [
                'label'      => esc_html__('Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-testimonial-rating' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'rating_margin',
            [
                'label'      => esc_html__('Margin', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-testimonial-rating' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'rating_size',
            [
                'label' => esc_html__('Size', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 14,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-rating' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .ui-e-testimonial-rating svg' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'rating_space_between',
            [
                'label' => esc_html__('Space Between', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-rating .ui-e-icon + .ui-e-icon' => 'margin-left: {{SIZE}}{{UNIT}};', // star type
                    '{{WRAPPER}} .ui-e-testimonial-rating span' => 'margin-right: {{SIZE}}{{UNIT}};', // number type
                ],
                'condition' => [
                    'rating_type' => 'star',
                ],
            ]
        );

        $this->end_controls_section();
    }
    function register_testimonial_divider_controls()
    {
        $this->start_controls_section(
            'section_style_divider',
            [
                'label' => __('Divider', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'layout' => 'layout_3',
                    'show_reviewer_divider' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'divider_color',
            [
                'label'     => __('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-divider' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'divider_spacing',
            [
                'label'     => __('Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'default'   => [
                    'size' => 20,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-divider' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    // Register all Style Controls at once but Cards Controls since different widgets may/may not require an 'active' state
    function TRAIT_register_style_controls()
    {
        $this->register_testimonial_image_controls();
        $this->register_testimonial_name_controls();
        $this->register_testimonial_job_controls();
        $this->register_testimonial_text_controls();
        $this->register_testimonial_rating_controls();
        $this->register_testimonial_divider_controls();
    }
    // Register all Animation Controls at once
    function TRAIT_register_testimonial_animation_controls()
    {
        $this->TRAIT_register_entrance_animations_controls(); // animate each item
        // Hover Animations -- IMPORTANT: any new animation added here must also be added on condition list to the animation wrapper at TRAIT_render_review_item()
        $this->TRAIT_register_hover_animation_control(
            'Item Hover Animation',
            [],
            ['underline']
        );
        $this->TRAIT_register_hover_animation_control(
            'Image Animations',
            ['show_reviewer_image' => 'yes'],
            ['underline']
        );
        $this->TRAIT_register_hover_animation_control(
            'Avatar Animations',
            ['show_reviewer_avatar' => 'yes'],
            ['underline']
        );
        $this->TRAIT_register_hover_animation_control(
            'Text Animations',
            ['show_reviewer_text' => 'yes'],
            ['zoom', 'underline']
        );
        $this->TRAIT_register_hover_animation_control(
            'Name Animations',
            ['show_reviewer_name' => 'yes'],
            ['zoom']
        );
        $this->TRAIT_register_hover_animation_control(
            'Job Animations',
            ['show_reviewer_job_title' => 'yes'],
            ['zoom']
        );
        $this->TRAIT_register_hover_animation_control(
            'Rating Animations',
            ['show_reviewer_rating' => 'yes'],
            ['underline']
        );
    }
    // Register specific Controls that can't be called in blocks because are positioned differently on widgets, or used only by specific testimonial types, such as grid or carousel
    function TRAIT_register_specific_testimonial_controls($arg)
    {

        switch ($arg) {
            case 'layout':
                $this->add_control(
                    'layout',
                    [
                        'label' => esc_html__('Layout', 'uicore-elements'),
                        'type' => Controls_Manager::SELECT,
                        'default' => 'layout_1',
                        'options' => [
                            'layout_1' => esc_html__('Layout 1', 'uicore-elements'),
                            'layout_2' => esc_html__('Layout 2', 'uicore-elements'),
                            'layout_3' => esc_html__('Layout 3', 'uicore-elements'),
                            'layout_4' => esc_html__('Layout 4', 'uicore-elements'),
                            'layout_5' => esc_html__('Layout 5', 'uicore-elements'),
                            'layout_6' => esc_html__('Layout 6', 'uicore-elements'),
                        ],
                        'render_type' => 'template',
                        'prefix_class' => 'ui-e-',
                        'frontend_available' => true
                    ]
                );
                break;

            case 'item_animations':
                $this->add_control(
                    'item_hover_animation',
                    [
                        'label' => esc_html__('Item Hover Animation', 'uicore-elements'),
                        'type' => Controls_Manager::SELECT,
                        'default' => '',
                        'label_block' => true,
                        'options' => $this->get_animations(),
                    ]
                );
                break;
        }
    }

    // HTML Rendering Functions
    function render_reviewer_avatar($item)
    {
        $settings = $this->get_settings_for_display();
        $fit_avatar = ($this->is_option('avatar_size') && $settings['avatar_size']['unit'] === 'px')
            ? 'ui-avatar-fit'
            : '';

        if (! $settings['show_reviewer_avatar']) {
            return;
        }

?>
        <div class="ui-e-testimonial-avatar <?php echo esc_attr($settings['avatar_animations']); ?> <?php echo esc_attr($fit_avatar); ?>">
            <?php
            $avatar_url = Group_Control_Image_Size::get_attachment_image_src($item['avatar']['id'], 'avatar_media_size', $settings);
            if (!$avatar_url) {
                printf('<img src="%1$s" alt="%2$s">', esc_url($item['avatar']['url']), esc_html($item['reviewer_name']));
            } else {
                print(wp_get_attachment_image(
                    $item['avatar']['id'],
                    $settings['avatar_media_size_size'],
                    false,
                    [
                        'alt' => esc_html($item['reviewer_name'])
                    ]
                ));
            }
            ?>
        </div>
    <?php
    }
    function render_reviewer_secondary_image($item)
    {
        $settings = $this->get_settings_for_display();

        $thumb_url = Group_Control_Image_Size::get_attachment_image_src($item['image']['id'], 'image_media_size', $settings);

        if (! $settings['show_reviewer_image'] || !$thumb_url) {
            return;
        }

    ?>
        <div class="ui-e-testimonial-image <?php echo esc_attr($settings['image_animations']); ?>">
            <?php
            if ($thumb_url) {
                print(wp_get_attachment_image(
                    $item['image']['id'],
                    $settings['image_media_size_size'],
                    false,
                    []
                ));
            }
            ?>
        </div>
    <?php
    }
    function render_reviewer_name($item)
    {
        $settings = $this->get_settings_for_display();

        if (! $settings['show_reviewer_name']) {
            return;
        }

    ?>
        <?php if ($item['reviewer_name']) : ?>
            <<?php echo esc_html($settings['review_name_tag']); ?> class="ui-e-testimonial-name <?php echo esc_attr($settings['name_animations']); ?>">
                <span><?php echo wp_kses_post($item['reviewer_name']); ?></span>
            </<?php echo esc_html($settings['review_name_tag']); ?>>
        <?php endif; ?>
    <?php
    }
    function render_reviewer_job_title($item)
    {
        $settings = $this->get_settings_for_display();

        if (! $settings['show_reviewer_job_title']) {
            return;
        }

    ?>
        <?php if ($item['reviewer_job_title']) : ?>
            <div class="ui-e-testimonial-job-title <?php echo esc_attr($settings['job_animations']); ?>">
                <span><?php echo esc_html($item['reviewer_job_title']); ?></span>
            </div>
        <?php endif; ?>
    <?php
    }
    function render_review_text($item)
    {
        $settings = $this->get_settings_for_display();

        if (! $settings['show_reviewer_text']) {
            return;
        }

    ?>
        <?php if ($item['review_text']) : ?>
            <div class="ui-e-testimonial-text <?php echo esc_attr($settings['text_animations']); ?>">
                <?php echo wp_kses_post($item['review_text']); ?>
            </div>
        <?php endif; ?>
        <?php
    }
    function render_review_rating($item)
    {

        $settings = $this->get_settings_for_display();

        $ID = $item['_id'];
        if (!$settings['show_reviewer_rating']) {
            return;
        }

        $rating_value  = $item['rating_number']['size']; // get rating value

        if ($settings['rating_type'] == 'star') {

        ?>
            <div class="ui-e-testimonial-rating <?php echo esc_attr($settings['rating_animations']); ?>">
                <?php

                $rating_scale  = 5;

                if ('' === $rating_value) {
                    $rating_value = $rating_scale;
                }

                $rating_value = floatval($rating_value);
                $rating_value = round($rating_value, 2);

                for ($index = 1; $index <= $rating_scale; $index++) {

                    // SVG Method (uses font-awesome library for it's half-star icon, and can prints full and half-star)
                    if (Plugin::$instance->experiments->is_feature_active('e_font_icon_svg')) {
                ?>
                        <div class="ui-e-icon">
                            <?php
                            if ($rating_value >= $index) {
                                echo Icons_Manager::try_get_icon_html(['value' =>  'fas fa-star', 'library' => 'fa-solid'], ['aria-hidden' => 'true']); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            } elseif (intval(ceil($rating_value)) === $index) {
                                echo Icons_Manager::try_get_icon_html(['value' => 'fas fa-star-half', 'library' => 'fa-solid'], ['aria-hidden' => 'true']); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            }
                            ?>
                        </div>
                    <?php
                        // Font Icon Method (uses elementor library and can print decimal specific stars)
                    } else {

                        if ($rating_value >= $index) {
                            $width = '100%'; // Full star if rate superior to current loop
                        } elseif (intval(ceil($rating_value)) === $index) {
                            $width = ($rating_value - ($index - 1)) * 100 . '%'; // Semi-star, calculating the width based on the decimal value
                        } else {
                            $width = '0%'; // No star if rate inferior to current loop
                        }

                        // Creates the atts
                        $this->add_render_attribute("icon_marked_$ID" . "_$index", [
                            'class' => 'ui-e-marked',
                        ]);
                        if ('100%' !== $width) {
                            $this->add_render_attribute("icon_marked_$ID" . "_$index", [
                                'style' => '--ui-e-rate-width: ' . $width . ';',
                            ]);
                        }

                    ?>
                        <div class="ui-e-icon">
                            <div <?php $this->print_render_attribute_string("icon_marked_$ID" . "_$index",); ?>>
                                <?php echo Icons_Manager::try_get_icon_html(['value' => 'eicon-star', 'library' => 'eicons'], ['aria-hidden' => 'true']); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                ?>
                            </div>
                            <div class="ui-e-unmarked">
                                <?php echo Icons_Manager::try_get_icon_html(['value' => 'eicon-star', 'library' => 'eicons'], ['aria-hidden' => 'true']); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                ?>
                            </div>
                        </div>
                <?php
                    }
                }
                ?>
            </div>
        <?php

        } else { // number type rating
        ?>
            <div class="ui-e-testimonial-rating number">
                <span><?php echo esc_html($rating_value); ?></span>
            </div>
        <?php
        }
    }
    function render_review_divider()
    {
        $settings = $this->get_settings_for_display();

        if (!$settings['show_reviewer_divider']) {
            return;
        }

        ?>
        <span class="ui-e-testimonial-divider"></span>
    <?php
    }
    function TRAIT_render_review_item($item, $layout)
    {
        $settings  = $this->get_settings_for_display();

        // Atts used by some layouts
        $atts      = [
            'inline' => $this->is_option('image_inline', 'yes') ? true : false,
        ];

        // Get entrance and item hover animation classes
        $entrance = $this->is_option('animate_items', 'ui-e-grid-animate') ? 'elementor-invisible' : '';
        $hover = isset($settings['item_hover_animation']) ? $settings['item_hover_animation'] : null;
        $animations = sprintf('%s %s', $entrance, $hover);

        // Get all elements animation control options (triggered by .ui-e-animations-wrp:hover)
        $animation_options = [
            $settings['image_animations'],
            $settings['avatar_animations'],
            $settings['text_animations'],
            $settings['name_animations'],
            $settings['job_animations'],
            $settings['rating_animations']
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

    ?>

        <div class="ui-e-wrp swiper-slide">
            <?php if (!empty($has_animation)) : ?>
                <div class="ui-e-animations-wrp <?php echo esc_attr($animations); ?>">
                <?php endif; ?>
                <div class="ui-e-item">
                    <?php
                    switch ($layout) {
                        case 'layout_2':
                            $this->render_layout_2($item, $atts);
                            break;
                        case 'layout_3':
                            $this->render_layout_3($item, $atts);
                            break;
                        case 'layout_4':
                            $this->render_layout_4($item);
                            break;
                        case 'layout_5':
                            $this->render_layout_5($item);
                            break;
                        case 'layout_6':
                            $this->render_layout_6($item);
                            break;
                        default:
                            $this->render_layout_1($item, $atts);
                            break;
                    }
                    ?>
                </div>
                <?php if (!empty($has_animation)) : ?>
                </div>
            <?php endif; ?>
        </div>
    <?php
    }

    // Layouts
    function render_layout_1($item, $atts)
    {

        $this->render_reviewer_secondary_image($item);
    ?>
        <div class="ui-e-content">

            <?php $this->render_review_text($item); ?>

            <?php if ($atts['inline']) : ?>

                <div class="ui-e-testimonial-flex">
                    <?php $this->render_reviewer_avatar($item); ?>
                    <div class="ui-e-inner-content">
                        <?php
                        $this->render_reviewer_name($item);
                        $this->render_reviewer_job_title($item);
                        $this->render_review_rating($item);
                        ?>
                    </div>
                </div>

            <?php else :
                $this->render_reviewer_avatar($item);
                $this->render_reviewer_name($item);
                $this->render_reviewer_job_title($item);
                $this->render_review_rating($item);
            endif; ?>

        </div>
    <?php
    }
    function render_layout_2($item, $atts)
    {
    ?>
        <div class="ui-e-content">

            <?php if ($atts['inline']) : ?>
                <div class="ui-e-testimonial-flex">
                    <?php $this->render_reviewer_avatar($item); ?>
                    <div class="ui-e-inner-content">
                        <?php
                        $this->render_reviewer_name($item);
                        $this->render_reviewer_job_title($item);
                        $this->render_review_rating($item);
                        ?>
                    </div>
                </div>

            <?php else :
                $this->render_reviewer_avatar($item);
                $this->render_reviewer_name($item);
                $this->render_reviewer_job_title($item);
                $this->render_review_rating($item);
            endif; ?>

            <?php $this->render_review_text($item); ?>
        </div>

    <?php
        $this->render_reviewer_secondary_image($item);
    }
    function render_layout_3($item, $atts)
    {
        $this->render_reviewer_secondary_image($item);
    ?>
        <div class="ui-e-content">

            <?php
            $this->render_review_rating($item);
            $this->render_review_text($item);
            $this->render_review_divider();
            ?>

            <?php if ($atts['inline']) : ?>

                <div class="ui-e-testimonial-flex">
                    <?php $this->render_reviewer_avatar($item); ?>
                    <div class="ui-e-inner-content">
                        <?php $this->render_reviewer_name($item); ?>
                        <?php $this->render_reviewer_job_title($item); ?>
                    </div>
                </div>

            <?php else :
                $this->render_reviewer_avatar($item);
                $this->render_reviewer_name($item);
                $this->render_reviewer_job_title($item);
            endif; ?>
        </div>
    <?php
    }
    function render_layout_4($item)
    {
    ?>
        <div class="ui-e-testimonial-flex">
            <?php $this->render_reviewer_avatar($item); ?>
            <?php $this->render_reviewer_secondary_image($item); ?>
        </div>

        <div class="ui-e-content">
            <?php
            $this->render_review_text($item);
            $this->render_reviewer_name($item);
            $this->render_reviewer_job_title($item);
            $this->render_review_rating($item);
            ?>
        </div>
    <?php
    }
    function render_layout_5($item)
    {
    ?>
        <div class="ui-e-testimonial-flex">
            <div class="ui-e-content">

                <?php
                $this->render_reviewer_secondary_image($item);
                $this->render_review_text($item);
                $this->render_review_rating($item);
                $this->render_reviewer_name($item);
                $this->render_reviewer_job_title($item);
                ?>
            </div>
            <?php $this->render_reviewer_avatar($item); ?>
        </div>
    <?php
    }
    function render_layout_6($item)
    {
    ?>
        <div class="ui-e-testimonial-flex">

            <?php $this->render_reviewer_avatar($item); ?>

            <div class="ui-e-content">
                <?php
                $this->render_reviewer_name($item);
                $this->render_reviewer_job_title($item);
                $this->render_review_rating($item);
                $this->render_review_text($item);
                $this->render_reviewer_secondary_image($item);
                ?>
            </div>
        </div>
<?php
    }
}
