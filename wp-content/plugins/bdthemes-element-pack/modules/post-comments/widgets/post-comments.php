<?php

namespace ElementPack\Modules\PostComments\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Plugin;


if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

class Post_Comments extends Module_Base {

	public function get_name() {
		return 'bdt-post-comments';
	}

	public function get_title() {
		return BDTEP . esc_html__('Post Comments', 'bdthemes-element-pack');
	}

	public function get_icon() {
		return 'bdt-wi-post-comments';
	}

	public function get_categories() {
		return ['bdt-template-builder'];
	}
	public function show_in_panel() {
		return get_post_type() === 'elementor_library' || get_post_type() === 'post';
	}
	public function get_keywords() {
		return ['post', 'title', 'blog', 'recent', 'news', 'comments', 'comment', 'post comments'];
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return true;
	}

	public function register_controls() {
		$this->register_controls_comments_list();
		$this->register_controls_comment_box();
		$this->register_controls_style_button();
	}


	protected function register_controls_comments_list() {
		$this->start_controls_section(
			'section_comment_list',
			[
				'label' => __('Comment List', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_responsive_control(
			'comment_list_padding',
			[
				'label' => __('Wrapper Padding', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .bdt-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->start_controls_tabs(
			'comment_list_tabs'
		);
		$this->start_controls_tab(
			'comment_list_tab_heading',
			[
				'label' => __('Heading', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'comment_list_heading_color',
			[
				'label'     => __('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .title-comments' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'comment_list_heading_typography',
				'label'     => __('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .title-comments',
			]
		);

		$this->end_controls_tab();
		$this->start_controls_tab(
			'comment_list_tab_author',
			[
				'label' => __('Author', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'comment_list_author_color',
			[
				'label'     => __('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-author a' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'comment_list_author_meta_link_hover_color',
			[
				'label'     => __('Hover Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-author a:hover' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'comment_list_author_typography',
				'label'     => __('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-author a',
			]
		);
		$this->add_control(
			'author_meta_heading',
			[
				'label'     => __('Meta', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_control(
			'comment_list_author_meta_color',
			[
				'label'     => __('Meta Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-author .says' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'author_meta_typography',
				'label'     => __('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-author .says',
			]
		);

		$this->end_controls_tab();
		$this->start_controls_tab(
			'comment_list_tab_text',
			[
				'label' => __('Text', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'comment_list_text_color',
			[
				'label'     => __('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-content' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'comment_list_text_link_color',
			[
				'label'     => __('Link Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-content a' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'comment_list_text_link_hover_color',
			[
				'label'     => __('Link Hover Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-content a:hover' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'comment_list_text_typography',
				'label'     => __('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-content',
			]
		);

		$this->end_controls_tab();
		$this->start_controls_tab(
			'comment_list_tab_meta',
			[
				'label' => __('Meta', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'comment_list_meta_color',
			[
				'label'     => __('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-metadata a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'comment_list_meta_link_hover_color',
			[
				'label'     => __('Hover Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-metadata a:hover' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'comment_list_meta_typography',
				'label'     => __('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-metadata a',
			]
		);
		$this->add_control(
			'comment_list_reply_color',
			[
				'label'     => __('Reply Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments a.comment-reply-link' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'comment_list_reply_h_color',
			[
				'label'     => __('Reply Hover Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments a.comment-reply-link:hover' => 'color: {{VALUE}}',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'comment_list_reply_typography',
				'label'     => __('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments a.comment-reply-link',
			]
		);


		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
	}
	protected function register_controls_comment_box() {
		$this->start_controls_section(
			'section_comment_box',
			[
				'label' => __('Comment Box', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs(
			'comment_box_tabs'
		);
		$this->start_controls_tab(
			'comment_box_heading_tab',
			[
				'label' => __('Heading', 'bdthemes-element-pack'),
			]
		);
		$this->add_control(
			'comment_box_title_color',
			[
				'label'     => __('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-reply-title' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'comment_box_title_typography',
				'label'     => __('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .comment-reply-title',
			]
		);

		$this->end_controls_tab();
		$this->start_controls_tab(
			'comment_box_text_tab',
			[
				'label' => __('Text', 'bdthemes-element-pack'),
			]
		);
		$this->add_control(
			'comment_box_text_color',
			[
				'label'     => __('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .logged-in-as, {{WRAPPER}}.elementor-widget-bdt-post-comments .comment-form-comment, {{WRAPPER}}.elementor-widget-bdt-post-comments textarea' => 'color: {{VALUE}}',
				],
			]
		);

		// $this->add_control(
		// 	'comment_box_text_border_color',
		// 	[
		// 		'label'     => __('Form Border Color', 'bdthemes-element-pack'),
		// 		'type'      => Controls_Manager::COLOR,
		// 		'selectors' => [
		// 			'{{WRAPPER}}.elementor-widget-bdt-post-comments textarea:focus' => 'border-color: {{VALUE}}',
		// 		],
		// 	]
		// );


		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'comment_box_text_typography',
				'label'     => __('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .logged-in-as, {{WRAPPER}}.elementor-widget-bdt-post-comments .comment-form-commentm , {{WRAPPER}}.elementor-widget-bdt-post-comments textarea',
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'comment_box_link_tab',
			[
				'label' => __('Link', 'bdthemes-element-pack'),
			]
		);
		$this->add_control(
			'comment_box_link_color',
			[
				'label'     => __('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .logged-in-as a' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'comment_box_link_typography',
				'label'     => __('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .logged-in-as a',
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
	}
	public function register_controls_style_button() {
		$this->start_controls_section(
			'section_style_button',
			[
				'label'     => esc_html__('Button', 'bdthemes-element-pack'),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('tabs_button_style');

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__('Normal', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'button_color',
			[
				'label'     => esc_html__('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .form-submit [type=submit]' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'button_background',
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .form-submit [type=submit]',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' 	 => __('Padding', 'bdthemes-element-pack'),
				'type' 		 => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .form-submit [type=submit]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);


		$this->add_responsive_control(
			'button_margin',
			[
				'label' 	 => __('Margin', 'bdthemes-element-pack'),
				'type' 		 => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .form-submit [type=submit]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'button_border',
				'selector'  =>
				'{{WRAPPER}}.elementor-widget-bdt-post-comments .form-submit [type=submit]'
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label'		 => __('Border Radius', 'bdthemes-element-pack'),
				'type' 		 => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .form-submit [type=submit]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'button_shadow',
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .form-submit [type=submit]'
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'button_typography',
				'label'     => esc_html__('Typography', 'bdthemes-element-pack'),
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .form-submit [type=submit]',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__('Hover', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'button_hover_color',
			[
				'label'     => esc_html__('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .form-submit [type=submit]:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_border_hover_color',
			[
				'label'     => esc_html__('Border Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-post-comments .form-submit [type=submit]:hover' => 'color: {{VALUE}};',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'button_hover_background',
				'selector'  => '{{WRAPPER}}.elementor-widget-bdt-post-comments .form-submit [type=submit]:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	public function render() {

		if (!comments_open() && (Plugin::instance()->preview->is_preview_mode() || Plugin::instance()->editor->is_edit_mode())) : ?>
			<div class="elementor-alert elementor-alert-danger" role="alert">
				<span class="elementor-alert-title">
					<?php esc_html_e('Comments are closed.', 'bdthemes-element-pack'); ?>
				</span>
				<span class="elementor-alert-description">
					<?php esc_html_e('Switch on comments from either the discussion box on the WordPress post edit screen or from the WordPress discussion settings.', 'bdthemes-element-pack'); ?>
				</span>
			</div>
<?php
		else :
			comments_template();
		endif;
	}
}
