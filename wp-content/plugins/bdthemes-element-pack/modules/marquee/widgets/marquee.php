<?php

namespace ElementPack\Modules\Marquee\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Css_Filter;
use Elementor\Repeater;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Text_Shadow;
use ElementPack\Base\Module_Base;

if (! defined('ABSPATH')) {
	exit;
}

// Exit if accessed directly

class Marquee extends Module_Base {

	public function get_name() {
		return 'bdt-marquee';
	}

	public function get_title() {
		return BDTEP . esc_html__('Marquee', 'bdthemes-element-pack');
	}

	public function get_icon() {
		return 'bdt-wi-marquee';
	}

	public function get_categories() {
		return ['element-pack'];
	}

	public function get_keywords() {
		return ['marquee', 'marquee text', 'marquee-list', 'news', 'ticker'];
	}

	public function get_style_depends() {

		if ($this->ep_is_edit_mode()) {
			return ['ep-styles'];
		} else {
			return ['ep-marquee'];
		}
	}

	public function get_script_depends() {

		if ($this->ep_is_edit_mode()) {
			return ['gsap', 'ep-scripts'];
		} else {
			return ['gsap', 'draggable', 'InertiaPlugin', 'ep-marquee'];
		}
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/3Dnxt9V0mzc';
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return false;
	}

	protected function register_controls() {
		$this->register_controls_layout_items();
		$this->register_controls_marquee_options();
		$this->register_controls_style_text();
		$this->register_controls_style_images();
	}

	protected function register_controls_marquee_options() {
		$this->start_controls_section(
			'section_controls_marquee',
			[
				'label' => esc_html__('Marquee Options', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_responsive_control(
			'marquee_item_spacing',
			[
				'label'              => esc_html__('Item Spacing', 'bdthemes-element-pack'),
				'type'               => Controls_Manager::SLIDER,
				'range'              => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'          => [
					'{{WRAPPER}} .bdt-marquee .marquee-content' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
				'frontend_available' => true,
				'render_type'        => 'template',
			]
		);

		$this->add_control(
			'marquee_speed',
			[
				'label'              => esc_html__('Scroll Speed', 'bdthemes-element-pack'),
				'type'               => Controls_Manager::NUMBER,
				'min'                => 0,
				'max'                => 10000,
				'step'               => 1,
				'default'            => 50,
				'frontend_available' => true,
				'render_type'        => 'none',
				'separator'          => 'after',

			]
		);
		$this->add_control(
			'marquee_direction',
			[
				'label'              => esc_html__('Direction', 'bdthemes-element-pack'),
				'type'               => Controls_Manager::CHOOSE,
				'options'            => [
					'left'  => [
						'title' => esc_html__('Left', 'bdthemes-element-pack'),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__('Right', 'bdthemes-element-pack'),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'            => 'left',
				'frontend_available' => true,
				'render_type'        => 'template',
				'toggle'             => false,
				'separator'          => 'before',
			]
		);
		$this->add_control(
			'marquee_pause_on_hover',
			[
				'label'              => esc_html__('pauseOnHover', 'bdthemes-element-pack'),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => esc_html__('Yes', 'bdthemes-element-pack'),
				'label_off'          => esc_html__('No', 'bdthemes-element-pack'),
				'return_value'       => 'yes',
				'frontend_available' => true,

			]
		);

		$this->add_control(
			'marquee_draggable',
			[
				'label'              => esc_html__('Draggable', 'bdthemes-element-pack'),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => esc_html__('Yes', 'bdthemes-element-pack'),
				'label_off'          => esc_html__('No', 'bdthemes-element-pack'),
				'return_value'       => 'yes',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'marquee_clickable',
			[
				'label'              => esc_html__('Clickable', 'bdthemes-element-pack'),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => esc_html__('Yes', 'bdthemes-element-pack'),
				'label_off'          => esc_html__('No', 'bdthemes-element-pack'),
				'return_value'       => 'yes',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'marquee_advanced',
			[
				'label'              => esc_html__('Advanced Options', 'bdthemes-element-pack'),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => esc_html__('Yes', 'bdthemes-element-pack'),
				'label_off'          => esc_html__('No', 'bdthemes-element-pack'),
				'return_value'       => 'yes',
				'frontend_available' => true,
				'render_type'        => 'template',
			]
		);

		$this->add_responsive_control(
			'marquee_rotate',
			[
				'label'     => esc_html__('Rotate', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => -100,
						'max'  => 100,
						'step' => 1,
					]
				],
				'selectors' => [
					'{{WRAPPER}}' => '--bdt-marquee-rotate: {{SIZE}}deg;',
				],
				'separator' => 'before',
				'condition' => [
					'marquee_advanced' => 'yes'
				]
			]
		);

		$this->add_responsive_control(
			'marquee_rotate_offset',
			[
				'label'     => esc_html__('Offset left', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => -500,
						'max'  => 500,
						'step' => 1,
					]
				],

				'selectors' => [
					'{{WRAPPER}} ' => '--bdt-marquee-offset: -{{SIZE}}px;',
				],
				'condition' => [
					'marquee_advanced' => 'yes'
				]
			]
		);

		$this->add_responsive_control(
			'marquee_rotate_adjustment',
			[
				'label'     => esc_html__('Offset Right', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => -500,
						'max'  => 500,
						'step' => 1,
					]
				],
				'selectors' => [
					'{{WRAPPER}} ' => '--bdt-marquee-adjustment: {{SIZE}}px;',
				],
				'condition' => [
					'marquee_advanced' => 'yes'
				]
			]
		);

		$this->add_control(
			'skin_shadow_mode',
			[
				'label'        => esc_html__('Shadow Mode', 'bdthemes-element-pack') . BDTEP_NC,
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'bdt-ep-shadow-mode-',
				'render_type' => 'template',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'skin_shadow_color',
			[
				'label'     => esc_html__('Shadow Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'skin_shadow_mode' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}.bdt-ep-shadow-mode-yes:before' => is_rtl() ? 'background: linear-gradient(to left, {{VALUE}} 5%,rgba(255,255,255,0) 100%);' : 'background: linear-gradient(to right, {{VALUE}} 5%,rgba(255,255,255,0) 100%);',
					'{{WRAPPER}}.bdt-ep-shadow-mode-yes:after'  => is_rtl() ? 'background: linear-gradient(to left, rgba(255,255,255,0) 0%, {{VALUE}} 95%);' : 'background: linear-gradient(to right, rgba(255,255,255,0) 0%, {{VALUE}} 95%);',
				],
			]
		);

		$this->end_controls_section();
	}
	protected function register_controls_layout_items() {
		$this->start_controls_section(
			'section_layout_text',
			[
				'label' => esc_html__('Marquee', 'bdthemes-element-pack'),
			]
		);
		$this->add_control(
			'marquee_motice',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__('Please switch to "Preview Mode" to fully experience the Marquee Widget\'s functionality and make any needed adjustments.', 'bdthemes-element-pack'),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);
		$this->add_control(
			'marquee_type',
			[
				'label'              => esc_html__('Marquee Type', 'bdthemes-element-pack'),
				'type'               => Controls_Manager::SELECT,
				'options'            => [
					'text'  => esc_html__('Text', 'bdthemes-element-pack'),
					'image' => esc_html__('Image', 'bdthemes-element-pack'),
				],
				'default'            => 'text',
				'frontend_available' => true,
				'render_type'        => 'template',
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'thumbnail',
				'label'     => esc_html__('Image Size', 'bdthemes-element-pack'),
				'exclude'   => ['custom'],
				'default'   => 'medium',
				'condition' => [
					'marquee_type' => 'image'
				]
			]
		);
		$repeater = new Repeater();
		$repeater->add_control(
			'marquee_content',
			[
				'label'       => esc_html__('Content', 'bdthemes-element-pack'),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
			]
		);
		$repeater->add_control(
			'marquee_link',
			[
				'label'         => __('Link', 'bdthemes-element-pack'),
				'type'          => Controls_Manager::URL,
				'placeholder'   => __('https://example.com', 'bdthemes-element-pack'),
				'show_external' => true,
				'default'       => [
					'url'         => '',
					'is_external' => true,
					'nofollow'    => true,
				],
			]
		);
		$repeater->add_control(
			'marquee_color',
			[
				'label'       => esc_html__('Color', 'bdthemes-element-pack'),
				'type'        => Controls_Manager::COLOR,
				'default'     => '',
				'render_type' => 'template',
				'separator'   => 'before'
			]
		);

		$repeater->add_control(
			'marquee_bg_color',
			[
				'label'       => esc_html__('BackgroundColor', 'bdthemes-element-pack'),
				'type'        => Controls_Manager::COLOR,
				'render_type' => 'template'
			]
		);

		$this->add_control(
			'marquee_type_text',
			[
				'label'              => esc_html__('Maruqee Items', 'bdthemes-element-pack'),
				'type'               => Controls_Manager::REPEATER,
				'fields'             => $repeater->get_controls(),
				'title_field'        => '{{{ marquee_content }}}',
				'condition'          => [
					'marquee_type' => 'text'
				],
				'frontend_available' => true,
				'render_type'        => 'none',
				'prevent_empty'      => false,
				'default'            => [
					[
						'marquee_content' => esc_html__("Element Pack", 'bdthemes-element-pack')
					],
					[
						'marquee_content' => esc_html__("Prime Slider ", 'bdthemes-element-pack')
					],
					[
						'marquee_content' => esc_html__("Ultimate Post Kit", 'bdthemes-element-pack')
					],
					[
						'marquee_content' => esc_html__("Ultimate Store Kit", 'bdthemes-element-pack')
					],
					[
						'marquee_content' => esc_html__("Pixel Gallery", 'bdthemes-element-pack')
					],
					[
						'marquee_content' => esc_html__("Live Copy Paste", 'bdthemes-element-pack')
					],
				]
			]
		);

		$image_slides = new Repeater();
		$image_slides->add_control(
			'marquee_image',
			[
				'label' => esc_html__('Image', 'bdthemes-element-pack'),
				'type'  => Controls_Manager::MEDIA,
			]
		);
		$image_slides->add_control(
			'marquee_image_link',
			[
				'label'         => __('Link', 'bdthemes-element-pack'),
				'type'          => Controls_Manager::URL,
				'placeholder'   => __('https://example.com', 'bdthemes-element-pack'),
				'show_external' => true,
				'default'       => [
					'url'         => '',
					'is_external' => true,
					'nofollow'    => true,
				],
			]
		);
		$image_slides->add_control(
			'marquee_image_width',
			[
				'label'         => __('Width', 'bdthemes-element-pack'),
				'type'          => Controls_Manager::SLIDER,
				'size_units'    => ['px', '%', 'vw'],
				'range'         => [
					'px' => [
						'min'  => 50,
						'max'  => 500,
						'step' => 1,
					],
					'%'  => [
						'min'  => 10,
						'max'  => 100,
						'step' => 1,
					],
					'vw' => [
						'min'  => 10,
						'max'  => 100,
						'step' => 1,
					],
				],
			]
		);
		$this->add_control(
			'marquee_type_images',
			[
				'label'         => esc_html__('Maruqee Items', 'bdthemes-element-pack'),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $image_slides->get_controls(),
				'condition'     => [
					'marquee_type' => 'image'
				],
				'prevent_empty' => false,
				'default'       => [
					[
						'marquee_image' => [
							'url' => BDTEP_ASSETS_URL . 'images/gallery/item-1.svg'

						]
					],
					[
						'marquee_image' => [
							'url' => BDTEP_ASSETS_URL . 'images/gallery/item-2.svg'
						]
					],
					[
						'marquee_image' => [
							'url' => BDTEP_ASSETS_URL . 'images/gallery/item-3.svg'
						]
					],
					[
						'marquee_image' => [
							'url' => BDTEP_ASSETS_URL . 'images/gallery/item-4.svg'
						]
					],
					[
						'marquee_image' => [
							'url' => BDTEP_ASSETS_URL . 'images/gallery/item-5.svg'
						]
					],
					[
						'marquee_image' => [
							'url' => BDTEP_ASSETS_URL . 'images/gallery/item-6.svg'
						]
					],
					[
						'marquee_image' => [
							'url' => BDTEP_ASSETS_URL . 'images/gallery/item-7.svg'
						]
					],
					[
						'marquee_image' => [
							'url' => BDTEP_ASSETS_URL . 'images/gallery/item-8.svg'
						]
					]

				]
			]
		);
		$this->end_controls_section();
	}

	protected function register_controls_style_text() {

		$this->start_controls_section(
			'section_style_title',
			[
				'label'     => esc_html__('Marquee', 'bdthemes-element-pack'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'marquee_type' => 'text'
				]
			]
		);

		$this->start_controls_tabs(
			'marquee_title_style_tabs'
		);
		$this->start_controls_tab(
			'marquee_title_normal',
			[
				'label' => esc_html__('Normal', 'bdthemes-element-pack'),
			]
		);
		$this->add_control(
			'marquee_title_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-marquee .marquee-content .marquee-title' => 'color: {{VALUE}} !important',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'marquee_title_background',
				'label'     => esc_html__('Background', 'bdthemes-element-pack'),
				'types'     => ['classic', 'gradient'],
				'selector'  => '{{WRAPPER}} .bdt-marquee .marquee-content',
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'marquee_title_border',
				'label'     => esc_html__('Border', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}} .bdt-marquee .marquee-content',
				'separator' => 'before',
			]
		);
		$this->add_responsive_control(
			'marquee_title_radius',
			[
				'label'      => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors'  => [
					'{{WRAPPER}} .bdt-marquee .marquee-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'marquee_title_padding',
			[
				'label'      => esc_html__('Padding', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors'  => [
					'{{WRAPPER}} .bdt-marquee .marquee-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'marquee_title_typogrphy',
				'label'     => esc_html__('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}} .bdt-marquee .marquee-content',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'      => 'title_text_stroke',
				'label'     => esc_html__('Text Stroke', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}} .bdt-marquee .marquee-content',
			]
		);
		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'marquee_title_text_shadow',
				'label'    => esc_html__('Text Shadow', 'bdthemes-element-pack'),
				'selector' => '{{WRAPPER}} .bdt-marquee .marquee-content',
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'marquee_title_hover',
			[
				'label' => esc_html__('Hover', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'marquee_title_h_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-marquee .marquee-content:hover .marquee-title' => 'color: {{VALUE}} !important',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'marquee_title_h_background',
				'label'     => esc_html__('Background', 'bdthemes-element-pack'),
				'types'     => ['classic', 'gradient'],
				'selector'  => '{{WRAPPER}} .bdt-marquee .marquee-content:hover',
			]
		);
		$this->add_control(
			'marquee_title_border_h_color',
			[
				'label'     => esc_html__('Border Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-marquee .marquee-content:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'marquee_title_border_border!' => '',
				]
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
	}
	protected function register_controls_style_images() {
		$this->start_controls_section(
			'section_style_controls_image',
			[
				'label'     => esc_html__('Marquee Images', 'bdthemes-element-pack'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'marquee_type' => 'image'
				]
			]
		);
		
		$this->add_responsive_control(
			'marquee_image_height',
			[
				'label'       => esc_html__('Height', 'bdthemes-element-pack'),
				'description' => esc_html__('Set image size in pixel. Default is 250px', 'bdthemes-element-pack'),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => ['px', 'vh', '%'],
				'range'       => [
					'px' => [
						'min'  => 50,
						'max'  => 450,
						'step' => 1,
					]
				],
				'selectors'   => [
					'{{WRAPPER}} .bdt-marquee .marquee-content' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->start_controls_tabs(
			'marquee_image_style_tabs'
		);
		$this->start_controls_tab(
			'marquee_image_normal',
			[
				'label' => esc_html__('Normal', 'bdthemes-element-pack'),
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'marquee_image_background',
				'label'    => esc_html__('Background', 'bdthemes-element-pack'),
				'types'    => ['classic', 'gradient'],
				'selector' => '{{WRAPPER}} .bdt-marquee .marquee-content',
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'marquee_image_border',
				'label'    => esc_html__('Border', 'bdthemes-element-pack'),
				'selector' => '{{WRAPPER}} .bdt-marquee .marquee-content',
			]
		);
		$this->add_responsive_control(
			'marquee_image_radius',
			[
				'label'      => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors'  => [
					'{{WRAPPER}} .bdt-marquee .marquee-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'marquee_image_padding',
			[
				'label'      => esc_html__('Padding', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors'  => [
					'{{WRAPPER}} .bdt-marquee .marquee-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[ 
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .bdt-marquee .marquee-image img',
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'marquee_image_hover',
			[
				'label' => esc_html__('Hover', 'bdthemes-element-pack'),
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'marquee_image_background_hover',
				'label'    => esc_html__('Background', 'bdthemes-element-pack'),
				'types'    => ['classic', 'gradient'],
				'selector' => '{{WRAPPER}} .bdt-marquee .marquee-content:hover',
			]
		);
		$this->add_control(
			'marquee_border_hover_color',
			[
				'label'     => esc_html__('Border Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-marquee .marquee-content:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'marquee_image_border_border!' => '',
				]
			]
		);
		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[ 
				'name'     => 'css_filters_hover',
				'selector' => '{{WRAPPER}} .bdt-marquee .marquee-image:hover img',
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
	}


	public function render_thumbnail($item) {
		$settings  = $this->get_settings_for_display();
		$image     = $item['marquee_image'];
		$thumb_url = Group_Control_Image_Size::get_attachment_image_src($image['id'], 'thumbnail', $settings);
		$link      = $item['marquee_image_link'];
		$width     = esc_attr($item['marquee_image_width']['size'] . $item['marquee_image_width']['unit']);
		$style    = 'width:' . $width . ';';
		// Define image content based on available URL
		$content = $thumb_url
			? wp_get_attachment_image($image['id'], $settings['thumbnail_size'])
			: '<img src="' . esc_url($image['url']) . '" alt="">';

		// Check if link exists and wrap content with link attributes
		if (! empty($link['url'])) {
			$this->add_link_attributes('marquee-link', $link, true);
			$link_attributes = 'class="marquee-link marquee-content marquee-image" ' . $this->get_render_attribute_string('marquee-link');

			echo wp_kses_post('<a ' . $link_attributes . ' style="' . esc_attr($style) . '">' . $content . '</a>');
		} else {
			echo '<div class="marquee-content marquee-image" style="' . esc_attr($style) . '">' . wp_kses_post($content) . '</div>';
		}
	}


	function marquee_rolling() {
		$settings      = $this->get_settings_for_display();
		$contentText   = $settings['marquee_type_text'];
		$contentImages = $settings['marquee_type_images'];
?>
		<?php if ($settings['marquee_type'] === 'text') : ?>
			<?php if ($contentText) :
				$count = 0;
				foreach ($contentText as $index => $list) :
					$single_color     = 'link_' . $index;
					$marquee_bg_color = 'marquee_bg_color_' . $index;
					if (! empty($list['marquee_bg_color'])) {
						$this->add_render_attribute($marquee_bg_color, 'style', 'background-color: ' . $list['marquee_bg_color'] . ';');
					}
					if (! empty($list['marquee_color'])) {
						$this->add_render_attribute($single_color, 'style', 'color: ' . $list['marquee_color'] . ';');
					}
			?>

					<div class="marquee-content marquee-text" <?php $this->print_render_attribute_string($marquee_bg_color); ?>>


						<?php
						if (! empty($list['marquee_link']['url'])) {
							$this->add_link_attributes('marquee-link', $list['marquee_link'], true);
							$link_attributes = 'class="marquee-title"';
							$link_attributes .= ' ' . $this->get_render_attribute_string('marquee-link');
							printf('<a %1$s %3$s>%2$s</a>', wp_kses_post($link_attributes), esc_html($list['marquee_content']), wp_kses_post($this->get_render_attribute_string($single_color)));
						} else {
							printf('<span class="marquee-title" %1$s>%2$s</span>', wp_kses_post($this->get_render_attribute_string($single_color)), esc_html($list['marquee_content']));
						}
						?>
					</div>

			<?php
					$count++;
				endforeach;
			endif; ?>
		<?php endif; ?>

		<?php if ($settings['marquee_type'] === 'image') : ?>
			<?php if ($contentImages) :
				foreach ($contentImages as $key => $image) :
					$this->render_thumbnail($image);
			?>
			<?php endforeach;
			endif; ?>
		<?php endif; ?>

	<?php
	}

	public function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute('bdt-marquee', [
			'id'    => 'bdt-marque-' . $this->get_id() . '',
			'class' => ['bdt-marquee', 'marquee-type-' . $settings['marquee_type'] . ''],
		], null, true); ?>


		<div <?php $this->print_render_attribute_string('bdt-marquee'); ?>>
			<?php $this->marquee_rolling(); ?>
		</div>
<?php
	}
}
