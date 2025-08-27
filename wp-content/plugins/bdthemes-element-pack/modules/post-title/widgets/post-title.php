<?php

namespace ElementPack\Modules\PostTitle\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Text_Stroke;


if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

class Post_Title extends Module_Base {

	public function get_name() {
		return 'bdt-post-title';
	}

	public function get_title() {
		return BDTEP . esc_html__('Post Title', 'bdthemes-element-pack');
	}

	public function get_icon() {
		return 'bdt-wi-post-title';
	}

	public function get_categories() {
		return ['bdt-template-builder'];
	}
	public function show_in_panel() {
		return get_post_type() === 'elementor_library' || get_post_type() === 'post';
	}
	public function get_keywords() {
		return ['post', 'title', 'blog', 'recent', 'news', 'alter'];
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return true;
	}

	public function register_controls() {
		$this->start_controls_section(
			'section_content_layout',
			[
				'label' => esc_html__('Layout', 'bdthemes-element-pack'),
			]
		);

		// title field
		$this->add_control(
			'bdt_title_tag',
			[
				'label'   => esc_html__('Title HTML Tag', 'bdthemes-element-pack'),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'h1' => esc_html__('h1', 'bdthemes-element-pack'),
					'h2' => esc_html__('h2', 'bdthemes-element-pack'),
					'h3' => esc_html__('h3', 'bdthemes-element-pack'),
					'h4' => esc_html__('h4', 'bdthemes-element-pack'),
					'h5' => esc_html__('h5', 'bdthemes-element-pack'),
					'h6' => esc_html__('h6', 'bdthemes-element-pack'),
					'p'  => esc_html__('p', 'bdthemes-element-pack'),
					'div' => esc_html__('div', 'bdthemes-element-pack'),
				],
				'default' => 'h2',
			]
		);

		// alignment
		$this->add_responsive_control(
			'bdt_title_alignment',
			[
				'label'     => __('Alignment', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => __('Left', 'bdthemes-element-pack'),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __('Center', 'bdthemes-element-pack'),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __('Right', 'bdthemes-element-pack'),
						'icon'  => 'eicon-text-align-right',
					],
					'justify'  => [
						'title' => __('Justify', 'bdthemes-element-pack'),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				],
			]
		);


		$this->add_control(
			'bdt_post_link',
			[
				'label'        => __('Post Link', 'bdthemes-element-pack'),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'separator'    => 'before',
			]
		);
		$this->end_controls_section();

		/**
		 * Style controls: Title
		 */
		$this->register_controls_style_title();
	}


	function register_controls_style_title() {
		$this->start_controls_section(
			'section_style_title',
			[
				'label'     => esc_html__('Title', 'bdthemes-element-pack'),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('tabs_title_style');

		$this->start_controls_tab(
			'tab_title_normal',
			[
				'label' => esc_html__('Normal', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ep-post-title-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'title_typography',
				'label'     => esc_html__('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}} .ep-post-title-text',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name' => 'title_text_stroke',
				'label' => __('Text Stroke', 'bdthemes-element-pack'),
				'selector' => '{{WRAPPER}} .ep-post-title-text',
			]
		);
		//margin
		$this->add_responsive_control(
			'title_margin',
			[
				'label' => __('Margin', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .ep-post-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_advanced_style',
			[
				'label' => esc_html__('Advanced Style', 'bdthemes-element-pack'),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'title_background',
				'selector'  => '{{WRAPPER}} .ep-post-title-text',
				'condition' => [
					'title_advanced_style' => 'yes'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'      => 'title_text_shadow',
				'label'     => __('Text Shadow', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}} .ep-post-title-text',
				'condition' => [
					'title_advanced_style' => 'yes'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'title_border',
				'selector'  => '{{WRAPPER}} .ep-post-title-text',
				'condition' => [
					'title_advanced_style' => 'yes'
				]
			]
		);

		$this->add_responsive_control(
			'title_border_radius',
			[
				'label'		 => __('Border Radius', 'bdthemes-element-pack'),
				'type' 		 => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}} .ep-post-title-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'title_advanced_style' => 'yes'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'title_box_shadow',
				'selector'  => '{{WRAPPER}} .ep-post-title-text',
				'condition' => [
					'title_advanced_style' => 'yes'
				]
			]
		);

		$this->add_responsive_control(
			'title_text_padding',
			[
				'label' 	 => __('Padding', 'bdthemes-element-pack'),
				'type' 		 => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .ep-post-title-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'title_advanced_style' => 'yes'
				]
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_title_hover',
			[
				'label' => esc_html__('Hover', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label'     => esc_html__('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ep-post-title-text:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_border_hover_color',
			[
				'label'     => esc_html__('Border Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ep-post-title-text:hover' => 'color: {{VALUE}};',
				],
				'condition' => [
					'title_advanced_style' => 'yes',
					'title_border_border!' => '',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'title_hover_background',
				'selector'  => '{{WRAPPER}} .ep-post-title-text:hover',
				'condition' => [
					'title_advanced_style' => 'yes'
				]
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	public function render() {
		$settings = $this->get_settings_for_display();
		$title_tag = $settings['bdt_title_tag'];
		$post_data = $this->bdt_set_single_post_preview_data();

		if (isset($post_data)) {
			$title_text = $post_data->post_title;
		}

		if (empty($title_text)) {
			$title_text = get_the_title();
		}

		if ('yes' === $settings['bdt_post_link']) :
			printf('<a href="%3$s" class="ep-post-title-link"><%1$s class="ep-post-title"><span class="ep-post-title-text">%2$s</span></%1$s></a>', esc_attr($title_tag), wp_kses_post($title_text), esc_url(get_permalink()));
		else :
			printf('<%1$s class="ep-post-title"><span class="ep-post-title-text">%2$s</span></%1$s>', esc_attr($title_tag), wp_kses_post($title_text));
		endif;
	}
}
