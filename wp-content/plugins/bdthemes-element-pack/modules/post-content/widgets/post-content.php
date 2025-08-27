<?php

namespace ElementPack\Modules\PostContent\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

class Post_Content extends Module_Base {

	public function get_name() {
		return 'bdt-post-content';
	}

	public function get_title() {
		return BDTEP . esc_html__('Post Content', 'bdthemes-element-pack');
	}

	public function get_icon() {
		return 'bdt-wi-post-content';
	}

	public function get_categories() {
		return ['bdt-template-builder'];
	}

	public function get_keywords() {
		return ['post', 'content', 'blog', 'recent', 'news', 'alter'];
	}

	public function show_in_panel() {
		return get_post_type() === 'elementor_library' || get_post_type() === 'post';
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return true;
	}
	
	public function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __('Layout', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'content_type',
			[
				'label'   => __('Content Type', 'bdthemes-element-pack'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'excerpt',
				'options' => [
					'excerpt' => __('Excerpt', 'bdthemes-element-pack'),
					'full'    => __('Full Content', 'bdthemes-element-pack'),
				],
			]
		);
		$this->add_control(
			'limit',
			[
				'label'     => __('Excerpt', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 10,
				],
				'range'     => [
					'ms' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'condition' => [
					'content_type' => 'excerpt',
				],
			]
		);

		// alignment
		$this->add_responsive_control(
			'upk_content_alignment',
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
		$this->end_controls_section();

		//Style
		$this->start_controls_section(
			'section_style',
			[
				'label' => __('Content', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'label'    => __('Typography', 'bdthemes-element-pack'),
				'selector' => '{{WRAPPER}} *',
			]
		);
		$this->add_responsive_control(
			'content_margin',
			[
				'label'                 => __('Margin', 'bdthemes-element-pack'),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => ['px', '%', 'em'],
				'selectors'             => [
					'{{WRAPPER}}'    => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'text_color',
			[
				'label'     => __('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}};',
				],
			]
		);

		$this->start_controls_tabs('link_tabs');

		$this->start_controls_tab(
			'link_normal',
			[
				'label' => __('Normal', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'link_color',
			[
				'label'     => __('Links Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a' => 'color: {{VALUE}}',
				]
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'link_hover',
			[
				'label' => __('Hover', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'link_color_hover',
			[
				'label'     => __('Link Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	public function render() {
		$settings = $this->get_settings_for_display();
		$post = $this->bdt_set_single_post_preview_data();
		$limit = ('excerpt' === $settings['content_type'] && $settings['limit']['size']) ? $settings['limit']['size'] : '';

		if (isset($post)) :
			$post_excerpt = $post->excerpt;
			$post_content = $post->content;
		else :
			$post_excerpt = get_the_excerpt();
			$post_content = get_the_content();
		endif;


		if ('excerpt' === $settings['content_type']) :
			if (!empty($post_excerpt)) :
				echo wp_kses_post(wp_trim_words(wp_strip_all_tags($post_excerpt), $limit));
			else :
				echo wp_kses_post(wp_trim_words(wp_strip_all_tags($post_content), $limit));
			endif;
		else :
			if (!empty($post_content)) :
				echo wp_kses_post(apply_filters('the_content', get_post_field('post_content', get_the_ID())));
			else :
				echo wp_kses_post(__('This is a dummy text to demonstration purpose. It will be replaced with the post content or excerpt.', 'bdthemes-element-pack'));
			endif;
		endif;
	}
}
