<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Css_Filter;
use Elementor\Icons_Manager;
use Elementor\Utils;
use UiCoreElements\UiCoreWidget;

defined('ABSPATH') || exit();

/**
 * Icon Box
 *
 * @author Lucas Marini Falbo <lucas@uicore.co>
 * @since 1.0.0
 */

class IconBox extends UiCoreWidget
{

    public function get_name()
    {
        return 'uicore-icon-box';
    }
    public function get_title()
    {
        return __('Icon Box', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-icon-box ui-e-widget';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_keywords()
    {
        return ['icon', 'features', 'info', 'box'];
    }
    public function get_styles()
    {
        return ['icon-box'];
    }
    public function get_scripts()
    {
        return [
            'icon-box' => [
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'description_animation',
                            'operator' => '==',
                            'value' => 'ui-e-animation-description-show',
                        ],
                        [
                            'name' => 'readmore_animation',
                            'operator' => '==',
                            'value' => 'ui-e-animation-rm-show',
                        ],
                    ],
                ],
            ]
        ];
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }
    protected function register_controls()
    {

        $this->start_controls_section(
            'section_content_icon_box',
            [
                'label' => esc_html__('Icon Box', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'icon_type',
            [
                'label'        => esc_html__('Icon Type', 'uicore-elements'),
                'type'         => Controls_Manager::CHOOSE,
                'toggle'       => false,
                'default'      => 'icon',
                'render_type'  => 'template',
                'options'      => [
                    'none' => [
                        'title' => esc_html__('None', 'uicore-elements'),
                        'icon'  => 'eicon-editor-close'
                    ],
                    'icon' => [
                        'title' => esc_html__('Icon', 'uicore-elements'),
                        'icon'  => 'fas fa-star'
                    ],
                    'image' => [
                        'title' => esc_html__('Image', 'uicore-elements'),
                        'icon'  => 'far fa-image'
                    ]
                ]
            ]
        );

        $this->add_control(
            'selected_icon',
            [
                'label'            => esc_html__('Icon', 'uicore-elements'),
                'type'             => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-star',
                    'library' => 'fa-solid',
                ],
                'render_type'      => 'template',
                'condition'        => [
                    'icon_type' => 'icon',
                ],
            ]
        );

        $this->add_control(
            'image',
            [
                'label'       => esc_html__('Image Icon', 'uicore-elements'),
                'type'        => Controls_Manager::MEDIA,
                'render_type' => 'template',
                'default'     => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'icon_type' => 'image'
                ]
            ]
        );

        $this->add_control(
            'title_text',
            [
                'label'   => esc_html__('Title', 'uicore-elements'),
                'type'    => Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'default'     => esc_html__('Icon Box Title', 'uicore-elements'),
                'placeholder' => esc_html__('Enter your title', 'uicore-elements'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'title_link_url',
            [
                'label'       => esc_html__('Title Link URL', 'uicore-elements'),
                'type'        => Controls_Manager::URL,
                'dynamic'     => ['active' => true],
            ]
        );

        $this->add_control(
            'sub_title_text',
            [
                'label'   => esc_html__('Subtitle', 'uicore-elements'),
                'type'    => Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'default'     => esc_html__('Icon Box Subtitle', 'uicore-elements'),
                'placeholder' => esc_html__('Enter your sub title', 'uicore-elements'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'description_text',
            [
                'label'   => esc_html__('Description', 'uicore-elements'),
                'type'    => Controls_Manager::WYSIWYG,
                'dynamic' => [
                    'active' => true,
                ],
                'default'     => esc_html__("This serves as the explanatory text for the icon box. Use it to provide additional context or details that elaborate on the title's content.", "uicore-elements"),
                'placeholder' => esc_html__('Enter your description', 'uicore-elements'),
                'rows'        => 10,
            ]
        );

        $this->add_control(
            'position',
            [
                'label'     => esc_html__('Icon Position', 'uicore-elements'),
                'type'      => Controls_Manager::CHOOSE,
                'separator' => 'before',
                'default'   => 'top',
                'options'   => [
                    'left' => [
                        'title' => esc_html__('Left', 'uicore-elements'),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'top' => [
                        'title' => esc_html__('Top', 'uicore-elements'),
                        'icon'  => 'eicon-v-align-top',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'uicore-elements'),
                        'icon'  => 'eicon-h-align-right',
                    ],
                    'bottom' => [
                        'title' => esc_html__('Bottom', 'uicore-elements'),
                        'icon'  => 'eicon-v-align-bottom',
                    ],
                ],
                'frontend_available' => true,
                'prefix_class' => 'ui-e-',
                'toggle'       => false,
                'render_type' => 'template',
                'conditions' => [
                    'relation' => 'or',
                    'terms'    => [
                        [
                            'name'     => 'selected_icon[value]',
                            'operator' => '!=',
                            'value'    => ''
                        ],
                        [
                            'name'     => 'image[url]',
                            'operator' => '!=',
                            'value'    => ''
                        ],
                    ]
                ]
            ]
        );

        $this->add_control(
            'icon_inline',
            [
                'label'        => esc_html__('Icon Inline', 'uicore-elements'),
                'type'         => Controls_Manager::SWITCHER,
                'prefix_class' => 'ui-e-inline-',
                'render_type' => 'template',
                'frontend_available' => true,
                'condition'    => [
                    'position' => ['left', 'right']
                ],
            ]
        );

        $this->add_control(
            'icon_mobile',
            [
                'label'        => esc_html__('Icon Top on Mobile', 'uicore-elements'),
                'type'         => Controls_Manager::SWITCHER,
                'prefix_class' => 'ui-e-mobile-',
                'condition'    => [
                    'position' => ['left', 'right'],
                    'icon_inline' => ''
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_vertical_alignment',
            [
                'label'   => esc_html__('Icon Vertical Alignment', 'uicore-elements'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'start'   => [
                        'title' => esc_html__('Top', 'uicore-elements'),
                        'icon'  => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => esc_html__('Middle', 'uicore-elements'),
                        'icon'  => 'eicon-v-align-middle',
                    ],
                    'end' => [
                        'title' => esc_html__('Bottom', 'uicore-elements'),
                        'icon'  => 'eicon-v-align-bottom',
                    ],
                ],
                'default'      => 'start',
                'toggle'       => false,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-flex-wrp' => 'align-items: {{VALUE}};',
                ],
                'condition'    => [
                    'position' => ['left', 'right'],
                    'icon_inline' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'text_align',
            [
                'label'   => esc_html__('Alignment', 'uicore-elements'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__('Justified', 'uicore-elements'),
                        'icon'  => 'eicon-text-align-justify',
                    ],
                ],
                'default'    => 'center',
                'tablet_default'    => 'center',
                'mobile_default'    => 'center',
                'toggle'    => false,
                'prefix_class'    => 'ui-e-align%s-',
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-ico-box-text-align: {{VALUE}};',
                ],
            ]
        );

        // Responsible for using 'text_align' value as css property instead of css value on the readmore, if animated and left/right positioned without inline.
        // In this case, the readmore with show animation is absolute with margin:auto, text-align stops working.
        $this->add_control(
            'text_align_readmore',
            [
                'label' => esc_html__('Readmore Animation Alignment', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => '0',
                'condition'    => [
                    'text_align' => ['left', 'right'],
                    'icon_inline' => '',
                    'readmore_animation' => 'ui-e-animation-rm-show',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-readmore' => 'margin-{{text_align.VALUE}}: {{VALUE}} !important;',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_readmore',
            [
                'label'     => esc_html__('Read More', 'uicore-elements'),
                'condition' => [
                    'readmore' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'readmore_text',
            [
                'label'       => esc_html__('Text', 'uicore-elements'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => ['active' => true],
                'default'     => esc_html__('Read More', 'uicore-elements'),
                'placeholder' => esc_html__('Read More', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'readmore_link',
            [
                'label'     => esc_html__('Link to', 'uicore-elements'),
                'type'      => Controls_Manager::URL,
                'dynamic'   => [
                    'active' => true,
                ],
                'placeholder' => esc_html__('https://your-link.com', 'uicore-elements'),
                'default'     => [
                    'url' => '#',
                ],
                'condition' => [
                    'readmore'       => 'yes',
                ]
            ]
        );

        // TODO: disabled due to a security complain from Wordfence team in name of Wordpress.
        // $this->add_control(
        // 	'onclick',
        // 	[
        // 		'label'     => esc_html__('OnClick', 'uicore-elements'),
        // 		'type'      => Controls_Manager::SWITCHER,
        // 		'condition' => [
        // 			'readmore'       => 'yes',
        // 		]
        // 	]
        // );

        // $this->add_control(
        // 	'onclick_event',
        // 	[
        // 		'label'       => esc_html__('OnClick Event', 'uicore-elements'),
        // 		'type'        => Controls_Manager::TEXT,
        // 		'placeholder' => 'myFunction()',
        // 		'condition' => [
        // 			'readmore'       => 'yes',
        // 			'onclick'        => 'yes'
        // 		]
        // 	]
        // );

        $this->add_control(
            'readmore_icon',
            [
                'label'       => esc_html__('Icon', 'uicore-elements'),
                'type'             => Controls_Manager::ICONS,
                'condition'   => [
                    'readmore'       => 'yes'
                ],
                'label_block' => false,
                'skin' => 'inline'
            ]
        );

        $this->add_control(
            'readmore_icon_align',
            [
                'label'   => esc_html__('Icon Position', 'uicore-elements'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'right',
                'options' => [
                    'left'   => esc_html__('Left', 'uicore-elements'),
                    'right'  => esc_html__('Right', 'uicore-elements'),
                ],
                'selectors_dictionary' => [
                    'left' => 'flex-direction: row-reverse;',
                    'right' => 'flex-direction: row;',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-readmore' => '{{VALUE}};',
                ],
                'condition' => [
                    'readmore_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'readmore_icon_indent',
            [
                'label' => esc_html__('Icon Spacing', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 8,
                ],
                'condition' => [
                    'readmore_icon[value]!' => '',
                    'readmore_text!' => '',
                    'text_align!'  => 'justify'
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-readmore' => 'gap: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_responsive_control(
            'readmore_horizontal_offset',
            [
                'label' => esc_html__('Horizontal Offset', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
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
                    'readmore_on_hover' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'readmore_vertical_offset',
            [
                'label' => esc_html__('Vertical Offset', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
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
                'selectors' => [
                    '{{WRAPPER}} .ui-e-readmore' => 'translate: {{readmore_horizontal_offset.SIZE}}px {{SIZE}}px;'
                ],
                'condition' => [
                    'readmore_on_hover' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_css_id',
            [
                'label' => esc_html__('Button ID', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => '',
                'title' => esc_html__('Add your custom id WITHOUT hash (#) key. e.g: my-id', 'uicore-elements'),
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_badge',
            [
                'label'     => esc_html__('Badge', 'uicore-elements'),
                'condition' => [
                    'badge' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'badge_text',
            [
                'label'       => esc_html__('Badge Text', 'uicore-elements'),
                'type'        => Controls_Manager::TEXT,
                'default'     => 'POPULAR',
                'placeholder' => 'Type Badge Title',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_additional',
            [
                'label' => esc_html__('Additional Options', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_size',
            [
                'label'   => esc_html__('Title HTML Tag', 'uicore-elements'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'h4',
                'options' => Helper::get_title_tags(),
            ]
        );

        $this->add_control(
            'readmore',
            [
                'label'     => esc_html__('Read More Button', 'uicore-elements'),
                'type'      => Controls_Manager::SWITCHER,
                'separator' => 'before',
                'frontend_available'    => true,
                'default'   => 'yes',
            ]
        );

        $this->add_control(
            'badge',
            [
                'label' => esc_html__('Badge', 'uicore-elements'),
                'type'  => Controls_Manager::SWITCHER,
            ]
        );

        $this->add_control(
            'global_link',
            [
                'label'        => esc_html__('Global Link', 'uicore-elements'),
                'type'         => Controls_Manager::SWITCHER,
            ]
        );

        $this->add_control(
            'global_link_url',
            [
                'label'       => esc_html__('Global Link URL', 'uicore-elements'),
                'type'        => Controls_Manager::URL,
                'dynamic'     => ['active' => true],
                'placeholder' => 'http://your-link.com',
                'condition'   => [
                    'global_link' => 'yes'
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_icon_box',
            [
                'label'      => esc_html__('Icon/Image', 'uicore-elements'),
                'tab'        => Controls_Manager::TAB_STYLE,
                'condition'     => [
                    'icon_type!' => 'none',
                ],
            ]
        );

        $this->start_controls_tabs('icon_colors');

        $this->start_controls_tab(
            'icon_colors_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label'     => esc_html__('Icon Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon-wrp i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-icon-wrp svg' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    'icon_type!' => 'image',
                ],
            ]
        );

        $this->add_control(
            'show_svg_icon_color',
            [
                'label'     => esc_html__('Svg Icon Color', 'uicore-elements'),
                'type'      => Controls_Manager::SWITCHER,
                'condition' => [
                    'icon_type!' => 'image',
                ],
            ]
        );

        $this->add_control(
            'svg_icon_fill_color',
            [
                'label'     => esc_html__('Fill Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon-wrp svg, {{WRAPPER}} .ui-e-icon-wrp svg *' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    'icon_type!' => 'image',
                    'show_svg_icon_color' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'svg_icon_stroke_color',
            [
                'label'     => esc_html__('Stroke Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon-wrp svg, {{WRAPPER}} .ui-e-icon-wrp svg *' => 'stroke: {{VALUE}};',
                ],
                'condition' => [
                    'icon_type!' => 'image',
                    'show_svg_icon_color' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'blur_effect',
            [
                'label' => esc_html__('Blur Filter', 'uicore-elements'),
                'type'  => Controls_Manager::SWITCHER,
            ]
        );

        $this->add_control(
            'blur_level',
            [
                'label'       => esc_html__('Blur Level', 'uicore-elements'),
                'type'        => Controls_Manager::SLIDER,
                'range'       => [
                    'px' => [
                        'min'  => 0,
                        'step' => 1,
                        'max'  => 50,
                    ]
                ],
                'default'     => [
                    'size' => 5
                ],
                'selectors'   => [
                    '{{WRAPPER}} .ui-e-icon-wrp' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);'
                ],
                'condition' => [
                    'blur_effect' => 'yes',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'icon_background',
                'selector'  => '{{WRAPPER}} .ui-e-icon-wrp',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'icon_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .ui-e-icon-wrp'
            ]
        );

        $this->add_responsive_control(
            'icon_radius',
            [
                'label'      => esc_html__('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-icon-wrp' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'icon_radius_advanced_show!' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'icon_radius_advanced_show',
            [
                'label' => esc_html__('Advanced Radius', 'uicore-elements'),
                'type'  => Controls_Manager::SWITCHER,
                ''
            ]
        );

        $this->add_control(
            'icon_radius_advanced',
            [
                'label'       => esc_html__('Radius', 'uicore-elements'),
                'description' => sprintf('Generate the advanced border radius on https://9elements.github.io/fancy-border-radius/', 'uicore-elements'),
                'type'        => Controls_Manager::TEXT,
                'size_units'  => ['px', '%'],
                'default'     => '75% 25% 43% 57% / 46% 29% 71% 54%',
                'selectors'   => [
                    '{{WRAPPER}} .ui-e-icon-wrp'     => 'border-radius: {{VALUE}}; overflow: hidden;',
                    '{{WRAPPER}} .ui-e-icon-wrp img' => 'border-radius: {{VALUE}}; overflow: hidden;'
                ],
                'condition' => [
                    'icon_radius_advanced_show' => 'yes',
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
                    'top' => 10,
                    'right' => 10,
                    'bottom' => 10,
                    'left' => 10,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-icon-wrp' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'icon_shadow',
                'selector' => '{{WRAPPER}} .ui-e-icon-wrp'
            ]
        );

        $this->add_responsive_control(
            'icon_space',
            [
                'label'     => esc_html__('Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'separator' => 'before',
                'default'   => [
                    'size' => 15,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}'  => '--ui-e-ico-box-icon-spacing: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_control(
            'image_fullwidth',
            [
                'label' => esc_html__('Image Fullwidth', 'uicore-elements'),
                'type'  => Controls_Manager::SWITCHER,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon-wrp' => 'width: 100%; box-sizing: border-box;
                                                     margin-left: calc(var(--ui-e-content-padding-left) * -1);
                                                     margin-right: calc(var(--ui-e-content-padding-right) * -1);
                                                     margin-top: calc(var(--ui-e-content-padding-top) *-1);',
                ],
                'condition' => [
                    'icon_type' => 'image'
                ]
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => esc_html__('Size', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'vh', 'vw'],
                'range' => [
                    'px' => [
                        'min' => 6,
                        'max' => 300,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 42,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon-wrp' => '--ui-e-media-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'icon_type' => 'icon',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_size',
            [
                'label' => esc_html__('Size', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'vh', 'vw'],
                'range' => [
                    'px' => [
                        'min' => 6,
                        'max' => 300,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 120,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon-wrp' => '--ui-e-media-size: {{SIZE}}{{UNIT}};',
                ],
                'conditions' => [
                    'relation' => 'and',
                    'terms'    => [
                        [
                            'name'     => 'image_fullwidth',
                            'operator' => '==',
                            'value'    => ''
                        ],
                        [
                            'name'     => 'icon_type',
                            'operator' => '==',
                            'value'    => 'image'
                        ],
                    ]
                ]
            ]
        );

        $this->add_control(
            'rotate',
            [
                'label'   => esc_html__('Rotate', 'uicore-elements'),
                'type'    => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                    'unit' => 'deg',
                ],
                'range' => [
                    'deg' => [
                        'max'  => 360,
                        'min'  => -360,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}'   => '--ui-e-ico-box-icon-rotate: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'icon_background_rotate',
            [
                'label'   => esc_html__('Background Rotate', 'uicore-elements'),
                'type'    => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                    'unit' => 'deg',
                ],
                'range' => [
                    'deg' => [
                        'max'  => 360,
                        'min'  => -360,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon-wrp' => '--ui-e-ico-box-icon-wrp-rotate: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_vertical_offset',
            [
                'label' => esc_html__('Icon Vertical Offset', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                ],
                'default' => [
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon-wrp' => '--ui-e-ico-box-vertical-off: {{SIZE}}px'
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_horizontal_offset',
            [
                'label' => esc_html__('Icon Horizontal Offset', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                ],
                'default' => [
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon-wrp' => '--ui-e-ico-box-horizontal-off: {{SIZE}}px'
                ],
            ]
        );

        $this->add_control(
            'image_icon_heading',
            [
                'label'     => esc_html__('Image Effect', 'uicore-elements'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'icon_type' => 'image',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Css_Filter::get_type(),
            [
                'name'      => 'css_filters',
                'selector'  => '{{WRAPPER}} .ui-e-ico-box img',
                'condition' => [
                    'icon_type' => 'image',
                ],
            ]
        );

        $this->add_control(
            'image_opacity',
            [
                'label' => esc_html__('Opacity', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max'  => 1,
                        'min'  => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-ico-box img' => 'opacity: {{SIZE}};',
                ],
                'condition' => [
                    'icon_type' => 'image',
                ],
            ]
        );

        $this->add_control(
            'background_hover_transition',
            [
                'label' => esc_html__('Transition Duration', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0.3,
                ],
                'range' => [
                    'px' => [
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-icon-wrp img' => 'transition-duration: {{SIZE}}s',
                    '{{WRAPPER}}:hover .ui-e-icon-wrp img' => 'transform: ',
                ],
                'condition' => [
                    'icon_type' => 'image',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'icon_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'icon_hover_color',
            [
                'label'     => esc_html__('Icon Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-icon-wrp' => 'color: {{VALUE}};',
                    '{{WRAPPER}}:hover .ui-e-icon-wrp i' => 'color: {{VALUE}};',
                    '{{WRAPPER}}:hover .ui-e-icon-wrp svg' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    'icon_type!' => 'image',
                ],
            ]
        );

        $this->add_control(
            'svg_icon_hover_fill_color',
            [
                'label'     => esc_html__('Fill Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-icon-wrp svg, {{WRAPPER}}:hover .ui-e-icon-wrp svg *' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    'icon_type!' => 'image',
                    'show_svg_icon_color' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'svg_icon_hover_stroke_color',
            [
                'label'     => esc_html__('Stroke Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-icon-wrp svg, {{WRAPPER}}:hover .ui-e-icon-wrp svg *' => 'stroke: {{VALUE}};',
                ],
                'condition' => [
                    'icon_type!' => 'image',
                    'show_svg_icon_color' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'icon_hover_background',
                'separator' => 'before',
                'selector'  => '{{WRAPPER}}:hover .ui-e-icon-wrp',
            ]
        );

        $this->add_control(
            'icon_hover_border_color',
            [
                'label'     => esc_html__('Border Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-icon-wrp'  => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'icon_border_border!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_hover_radius',
            [
                'label'      => esc_html__('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'separator'  => 'after',
                'selectors'  => [
                    '{{WRAPPER}}:hover .ui-e-icon-wrp' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
                    '{{WRAPPER}}:hover .ui-e-icon-wrp img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;'
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'icon_hover_shadow',
                'selector' => '{{WRAPPER}}:hover .ui-e-icon-wrp'
            ]
        );

        $this->add_control(
            'image_icon_hover_heading',
            [
                'label'     => esc_html__('Image Effect', 'uicore-elements'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'icon_type' => 'image',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Css_Filter::get_type(),
            [
                'name'      => 'css_filters_hover',
                'selector'  => '{{WRAPPER}}:hover .ui-e-icon-wrp img',
                'condition' => [
                    'icon_type' => 'image',
                ],
            ]
        );

        $this->add_control(
            'image_opacity_hover',
            [
                'label' => esc_html__('Opacity', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-icon-wrp img' => 'opacity: {{SIZE}};',
                ],
                'condition' => [
                    'icon_type' => 'image',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_title',
            [
                'label' => esc_html__('Title', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('tabs_title_style');

        $this->start_controls_tab(
            'tab_title_style_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => esc_html__('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_title_style_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'title_color_hover',
            [
                'label'     => esc_html__('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->add_responsive_control(
            'title_bottom_space',
            [
                'label' => esc_html__('Spacing', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'separator' => 'before',
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-title' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .ui-e-title',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_sub_title',
            [
                'label' => esc_html__('Subtitle', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition'    => [
                    'sub_title_text!'    => '',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_sub_title_style');

        $this->start_controls_tab(
            'tab_sub_title_style_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'sub_title_color',
            [
                'label'     => esc_html__('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-subtitle' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_sub_title_style_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'sub_title_color_hover',
            [
                'label'     => esc_html__('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-subtitle' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'sub_title_bottom_space',
            [
                'label' => esc_html__('Spacing', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'separator' => 'before',
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'sub_title_typography',
                'selector' => '{{WRAPPER}} .ui-e-subtitle',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_description',
            [
                'label' => esc_html__('Description', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('tabs_description_style');

        $this->start_controls_tab(
            'tab_description_style_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label'     => esc_html__('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-description' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_description_style_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'description_color_hover',
            [
                'label'     => esc_html__('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-description' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'description_bottom_space',
            [
                'label'     => esc_html__('Spacing', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-description' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'description_typography',
                'selector' => '{{WRAPPER}} .ui-e-description',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_readmore',
            [
                'label'     => esc_html__('Read More', 'uicore-elements'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'readmore'       => 'yes',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_readmore_style');

        $this->start_controls_tab(
            'tab_readmore_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'readmore_typography',
                'selector' => '{{WRAPPER}} .ui-e-readmore',
            ]
        );

        $this->add_control(
            'readmore_text_color',
            [
                'label'     => esc_html__('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-readmore' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-readmore svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'readmore_background',
                'selector'  => '{{WRAPPER}} .ui-e-readmore',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'readmore_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .ui-e-readmore'
            ]
        );

        $this->add_responsive_control(
            'readmore_radius',
            [
                'label'      => esc_html__('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-readmore' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'readmore_padding',
            [
                'label'      => esc_html__('Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-readmore' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'readmore_shadow',
                'selector' => '{{WRAPPER}} .ui-e-readmore',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_readmore_box_hover',
            [
                'label' => esc_html__('Box hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'readmore_box_hover_text_color',
            [
                'label'     => esc_html__('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-readmore' => 'color: {{VALUE}};',
                    '{{WRAPPER}}:hover .ui-e-readmore svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'readmore_box_hover_background',
                'selector'  => '{{WRAPPER}}:hover .ui-e-readmore',
            ]
        );

        $this->add_control(
            'readmore_box_hover_border_color',
            [
                'label'     => esc_html__('Border Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-readmore' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'readmore_border_border!' => ''
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'readmore_box_hover_shadow',
                'selector' => '{{WRAPPER}}:hover .ui-e-readmore',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_readmore_hover',
            [
                'label' => esc_html__('Button hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'readmore_hover_text_color',
            [
                'label'     => esc_html__('Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-readmore:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-readmore:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'readmore_hover_background',
                'selector'  => '{{WRAPPER}} .ui-e-readmore:hover',
            ]
        );

        $this->add_control(
            'readmore_hover_border_color',
            [
                'label'     => esc_html__('Border Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-readmore:hover' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'readmore_border_border!' => ''
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'readmore_hover_shadow',
                'selector' => '{{WRAPPER}} .ui-e-readmore:hover',
            ]
        );

        $this->add_control(
            'readmore_hover_animation',
            [
                'label' => esc_html__('Hover Animation', 'uicore-elements'),
                'type' => Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_badge',
            [
                'label'     => esc_html__('Badge', 'uicore-elements'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'badge' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'badge_position',
            [
                'label'   => esc_html__('Position', 'uicore-elements'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'start right',
                'options' => [
                    'start left'    => esc_html__('Top Left', 'uicore-elements'),
                    'start center'  => esc_html__('Top Center', 'uicore-elements'),
                    'start right'   => esc_html__('Top Right', 'uicore-elements'),
                    'center '       => esc_html__('Center', 'uicore-elements'),
                    'center left'   => esc_html__('Center Left', 'uicore-elements'),
                    'center right'  => esc_html__('Center Right', 'uicore-elements'),
                    'end left'       => esc_html__('Bottom Left', 'uicore-elements'),
                    'end center'     => esc_html__('Bottom Center', 'uicore-elements'),
                    'end right'      => esc_html__('Bottom Right', 'uicore-elements'),
                ],
                'selectors'    => [
                    '{{WRAPPER}} .ui-e-badge-wrp' => '--ui-e-badge-position: {{VALUE}}',
                ]
            ]
        );

        $this->add_responsive_control(
            'badge_horizontal_offset',
            [
                'label' => esc_html__('Horizontal Offset', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
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
                        'min'  => -300,
                        'step' => 2,
                        'max'  => 300,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-badge-wrp' => '--ui-e-badge-horizontal-off: {{SIZE}}px;'
                ],
            ]
        );

        $this->add_responsive_control(
            'badge_vertical_offset',
            [
                'label' => esc_html__('Vertical Offset', 'uicore-elements'),
                'type'  => Controls_Manager::SLIDER,
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
                        'min'  => -300,
                        'step' => 2,
                        'max'  => 300,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-badge-wrp' => '--ui-e-badge-vertical-off: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_responsive_control(
            'badge_rotate',
            [
                'label'   => esc_html__('Rotate', 'uicore-elements'),
                'type'    => Controls_Manager::SLIDER,
                'devices' => ['desktop', 'tablet', 'mobile'],
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
                        'min'  => -360,
                        'max'  => 360,
                        'step' => 5,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-badge' => 'rotate: {{SIZE}}deg;'
                ],
            ]
        );

        $this->start_controls_tabs('tabs_badge');

        $this->start_controls_tab(
            'tab_badge_style_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .ui-e-badge',
                'fields_options' => [
                    'font_size' => [
                        'default' => [
                            'unit' => 'px',
                            'size' => 12,
                        ],
                    ],
                    'line_height' => [
                        'default' => [
                            'unit' => 'em',
                            'size' => 1,
                        ],
                    ],
                ],
            ]
        );

        $this->add_control(
            'badge_text_color',
            [
                'label'     => esc_html__('Text Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-badge' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'badge_background',
                'selector'  => '{{WRAPPER}} .ui-e-badge',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'badge_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .ui-e-badge'
            ]
        );

        $this->add_responsive_control(
            'badge_radius',
            [
                'label'      => esc_html__('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 10,
                    'right' => 10,
                    'bottom' => 10,
                    'left' => 10,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'badge_padding',
            [
                'label'      => esc_html__('Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default'   => [
                    'top' => '8',
                    'right' => '12',
                    'bottom' => '8',
                    'left' => '12',
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'badge_shadow',
                'selector' => '{{WRAPPER}} .ui-e-badge',
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_badge_style_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'badge_text_hover_color',
            [
                'label'     => esc_html__('Text Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-badge' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'badge_hover_background',
                'selector'  => '{{WRAPPER}}:hover .ui-e-badge',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'badge_hover_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}}:hover .ui-e-badge'
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'badge_hover_shadow',
                'selector' => '{{WRAPPER}}:hover .ui-e-badge',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_animation',
            [
                'label' => esc_html__('Animations', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'description_animation',
            [
                'label' => esc_html__('Description Hover Animation', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => esc_html__('None', 'uicore-elements'),
                    'ui-e-animation-description-show' => esc_html__('Show', 'uicore-elements'),
                ],
                'prefix_class'    => '',
                'frontend_available' => true,
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'position',
                            'operator' => '==',
                            'value' => 'top',
                        ],
                        [
                            'relation' => 'and',
                            'terms' => [
                                [
                                    'name' => 'position',
                                    'operator' => 'in',
                                    'value' => ['left', 'right'],
                                ],
                                [
                                    'name' => 'icon_inline',
                                    'operator' => '==',
                                    'value' => 'yes',
                                ],
                            ],
                        ],
                    ],
                ],

            ]
        );

        // Creates a prefix if has readmore button AND description animation. This allows us to apply some bottom space to the absolute flex-wrapper, keeping it above readmore
        $this->add_control(
            'description_prefix',
            [
                'label' => esc_html__('Prefix Class for Description with Readmore', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => 'readmore',
                'condition' => [
                    'readmore'    => 'yes',
                    'description_animation'    => 'ui-e-animation-description-show',
                ],
                'prefix_class'    => 'ui-e-has-',
            ]
        );

        $this->add_control(
            'readmore_animation',
            [
                'label' => esc_html__('Button Hover Animation', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => esc_html__('None', 'uicore-elements'),
                    'ui-e-animation-rm-show' => esc_html__('Show', 'uicore-elements'),
                    'ui-e-animation-rm-ico' => esc_html__('Icon', 'uicore-elements'),
                ],
                'prefix_class'    => '',
                'frontend_available' => true,
                'condition'    => [
                    'readmore' => 'yes',
                    'position!' => 'bottom',
                ]
            ]
        );

        $this->add_control(
            'icon_animation',
            [
                'label'        => esc_html__('Icon Hover Animation', 'uicore-elements'),
                'type'         => Controls_Manager::SELECT,
                'prefix_class' => '',
                'default'      => '',
                'options'      => [
                    ''                             => esc_html__('None', 'uicore-elements'),
                    'ui-e-animation-ico-float'  => esc_html__('Icon Float', 'uicore-elements'),
                    'ui-e-animation-ico-grow'   => esc_html__('Background Circle', 'uicore-elements'),
                    'ui-e-animation-ico-slide'  => esc_html__('Background Slide', 'uicore-elements'),
                ],
                'condition'    => [
                    'icon_type!' => 'none',
                ]
            ]
        );
        $this->add_control(
            'icon_animation_background',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'icon_animation' => [
                        'ui-e-animation-ico-grow',
                        'ui-e-animation-ico-slide'
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}}:hover .ui-e-icon-wrp:after' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'widget_animation',
            [
                'label' => esc_html__('Item Hover Animation', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => esc_html__('None', 'uicore-elements'),
                    'ui-e-animation-widget-zoom' => esc_html__('Zoom', 'uicore-elements'),
                    'ui-e-animation-widget-translate' => esc_html__('Translate', 'uicore-elements'),
                ],
                'prefix_class'    => '',
            ]
        );

        $this->add_control(
            'widget_animation_zoom',
            [
                'label' => esc_html__('Zoom scale', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => .8,
                        'max' => 1.3,
                        'step' => 0.01,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1.12,
                ],
                'condition' => [
                    'widget_animation' => 'ui-e-animation-widget-zoom',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-ico-box-widget-zoom: {{SIZE}}',
                ],
            ]
        );

        $this->add_control(
            'widget_animation_translate',
            [
                'label' => esc_html__('Translate value', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => -50,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => -36,
                ],
                'condition' => [
                    'widget_animation' => 'ui-e-animation-widget-translate',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-ico-box-widget-translate: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_additional',
            [
                'label' => esc_html__('Additional', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label'      => esc_html__('Content Inner Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'frontend_available' => true,
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-ico-box' => '--ui-e-content-padding-top: {{TOP}}{{UNIT}};
                                                    --ui-e-content-padding-right: {{RIGHT}}{{UNIT}};
                                                    --ui-e-content-padding-bot: {{BOTTOM}}{{UNIT}};
                                                    --ui-e-content-padding-left: {{LEFT}}{{UNIT}};'
                ]
            ]
        );

        $this->end_controls_section();
    }
    protected function render()
    {
        $settings  = $this->get_settings_for_display();

        // Get the proper media
        $media_type = $settings['icon_type'];
        switch ($media_type) {

            case 'icon':
                $media       = $settings['selected_icon'];
                break;

            case 'image':
                $media         = $settings['image'];
                $media_size    = isset($settings['image_fullwidth']) ? 'large' : 'medium'; // Gets the proper media size.
                break;
        }

        // Build the title
        if (! empty($settings['title_link_url']['url'])) {

            $hasTitleUrl    = true;
            $this->add_link_attributes('title_link', $settings['title_link_url']);
            $titleUrl         = "<a {$this->get_render_attribute_string('title_link')}>";
        } else {
            $hasTitleUrl = false;
        }

        $title         = '<' . Helper::esc_tag($settings['title_size']) . ' class="ui-e-title">';
        $title         .= $hasTitleUrl ? wp_kses_post($titleUrl) : ''; // url
        $title         .= esc_html($settings['title_text']);
        $title         .= $hasTitleUrl ? '</a>' : ''; // url closure
        $title         .= '</' . Helper::esc_tag($settings['title_size']) . '>';

        // Build the global Url
        if ($settings['global_link'] && $settings['global_link_url']['url'] && !$this->is_edit_mode()) {

            $global_target = $settings['global_link_url']['is_external'] ? '_blank' : '_self';
            $this->add_render_attribute('global_link', 'onclick', "window.open('{$settings['global_link_url']['url']}', '$global_target')");
        }

        // Build the subtitle
        $subtitle = $settings['sub_title_text'] != '' ? "<div class='ui-e-subtitle'> {$settings['sub_title_text']} </div>" : '';

        // Build the readmore
        $readmore = $settings['readmore'];
        if ($readmore) {

            $rmContent     = $settings['readmore_text'];
            $rmIcon         = $settings['readmore_icon']['value'] == '' ? false : true;

            if (!empty($settings['readmore_link']['url'])) {

                $this->add_link_attributes('rm_link', $settings['readmore_link']);
            }

            $this->add_render_attribute('rm_atts', 'class', $settings['readmore_hover_animation'] ? 'ui-e-readmore elementor-animation-' . esc_attr($settings['readmore_hover_animation']) : 'ui-e-readmore');

            if (!empty($settings['button_css_id'])) {
                $this->add_render_attribute('rm_atts', 'id', esc_attr($settings['button_css_id']));
            }

            // if ($settings['onclick']) {
            // 	$this->add_render_attribute( 'rm_atts', 'onclick', esc_js($settings['onclick_event']) );
            // }
        };
        // Check the icon position and sets the inline (true puts title/subtitlte inside flex wrapper, false puts inside content)
        switch ($settings['position']) {

            // Always true if top; always false if bottom and left/right checks the user choice
            case 'top':
                $inline = true;
                break;

            case 'bottom':
                $inline = false;
                break;

            default:
                $inline = $this->is_option('icon_inline', 'yes');
                break;
        }

?>

        <div class="ui-e-ico-box" <?php $this->print_render_attribute_string('global_link'); ?>>

            <?php if ($settings['badge']) : ?>
                <span class="ui-e-badge-wrp">
                    <?php $position = isset($settings['badge_position']) ? $settings['badge_position'] : 'start right'; // TODO: default start right value is not printed for some reason
                    ?>
                    <span class="ui-e-badge <?php echo esc_attr($position); ?>"> <?php echo esc_html($settings['badge_text']); ?> </span>
                </span>
            <?php endif; ?>

            <div class='ui-e-flex-wrp'>

                <?php if ($media_type != 'none') : ?>
                    <div class="ui-e-icon-wrp">

                        <?php if ($media_type == 'icon') {
                            Icons_Manager::render_icon($media, ['aria-hidden' => 'true']);
                        } else {
                            echo wp_get_attachment_image($media['id'], $media_size);
                        }
                        ?>

                    </div>
                <?php endif; ?>

                <?php echo $inline ? '<div class="ui-e-title-wrp">' . wp_kses_post($title . $subtitle) . '</div>' : ''; ?>

            </div>

            <div class="ui-e-box-content">
                <?php echo $inline ? '' : '<div class="ui-e-title-wrp">' . wp_kses_post($title . $subtitle) . '</div>'; ?>

                <div class="ui-e-description">
                    <?php echo wp_kses_post($settings['description_text']); ?>
                </div>

                <?php if ($readmore) : ?>
                    <a <?php $this->print_render_attribute_string('rm_link');
                        $this->print_render_attribute_string('rm_atts'); ?>>

                        <?php echo esc_html($rmContent); ?>
                        <?php if ($rmIcon) : ?>
                            <span>
                                <?php Icons_Manager::render_icon($settings['readmore_icon'], ['aria-hidden' => 'true']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

            </div>
        </div>
<?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new IconBox());
