<?php

namespace ElementPack\Modules\PostInfo\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Icons_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

use ElementPack\Base\Module_Base;

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

class Post_Info extends Module_Base {

	public function get_name() {
		return 'bdt-post-info';
	}

	public function get_title() {
		return BDTEP . esc_html__('Post Info', 'bdthemes-element-pack');
	}

	public function get_icon() {
		return 'bdt-wi-post-info';
	}

	public function get_categories() {
		return [ 'bdt-template-builder' ]; 
	}

	public function get_keywords() {
		return ['post', 'comment', 'date', 'author', 'tags', 'alter'];
	}
	public function show_in_panel() {
		return get_post_type() === 'post';
	}

	public function get_style_depends() {
		if ( $this->ep_is_edit_mode() ) {
			return [ 'ep-styles' ];
		} else {
			return [ 'ep-font', 'ep-post-info' ];
		}
	}
	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return true;
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_icon',
			[
				'label' => esc_html__('Meta Data', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'view',
			[
				'label' => esc_html__('Layout', 'bdthemes-element-pack'),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'inline',
				'options' => [
					'traditional' => [
						'title' => esc_html__('Default', 'bdthemes-element-pack'),
						'icon' => 'eicon-editor-list-ul',
					],
					'inline' => [
						'title' => esc_html__('Inline', 'bdthemes-element-pack'),
						'icon' => 'eicon-ellipsis-h',
					],
				],
				'render_type' => 'template',
				'classes' => 'elementor-control-start-end',
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'type',
			[
				'label' => esc_html__('Type', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'author' => esc_html__('Author', 'bdthemes-element-pack'),
					'date' => esc_html__('Date', 'bdthemes-element-pack'),
					'time' => esc_html__('Time', 'bdthemes-element-pack'),
					'comments' => esc_html__('Comments', 'bdthemes-element-pack'),
					'terms' => esc_html__('Terms', 'bdthemes-element-pack'),
					'custom' => esc_html__('Custom', 'bdthemes-element-pack'),
				],
			]
		);

		$repeater->add_control(
			'date_format',
			[
				'label' => esc_html__('Date Format', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => 'Default',
					'0' => _x('March 6, 2018 (F j, Y)', 'Date Format', 'bdthemes-element-pack'),
					'1' => '2018-03-06 (Y-m-d)',
					'2' => '03/06/2018 (m/d/Y)',
					'3' => '06/03/2018 (d/m/Y)',
					'custom' => esc_html__('Custom', 'bdthemes-element-pack'),
				],
				'condition' => [
					'type' => 'date',
				],
			]
		);

		$repeater->add_control(
			'custom_date_format',
			[
				'label' => esc_html__('Custom Date Format', 'bdthemes-element-pack'),
				'type' => Controls_Manager::TEXT,
				'default' => 'F j, Y',
				'condition' => [
					'type' => 'date',
					'date_format' => 'custom',
				],
				'description' => sprintf(
					/* translators: %s: Allowed data letters (see: http://php.net/manual/en/function.date.php). */
					__('Use the letters: %s', 'bdthemes-element-pack'),
					'l D d j S F m M n Y y'
				),
			]
		);

		$repeater->add_control(
			'time_format',
			[
				'label' => esc_html__('Time Format', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => 'Default',
					'0' => '3:31 pm (g:i a)',
					'1' => '3:31 PM (g:i A)',
					'2' => '15:31 (H:i)',
					'custom' => esc_html__('Custom', 'bdthemes-element-pack'),
				],
				'condition' => [
					'type' => 'time',
				],
			]
		);
		$repeater->add_control(
			'custom_time_format',
			[
				'label' => esc_html__('Custom Time Format', 'bdthemes-element-pack'),
				'type' => Controls_Manager::TEXT,
				'default' => 'g:i a',
				'placeholder' => 'g:i a',
				'condition' => [
					'type' => 'time',
					'time_format' => 'custom',
				],
				'description' => sprintf(
					/* translators: %s: Allowed time letters (see: http://php.net/manual/en/function.time.php). */
					__('Use the letters: %s', 'bdthemes-element-pack'),
					'g G H i a A'
				),
			]
		);

		$repeater->add_control(
			'taxonomy',
			[
				'label' => esc_html__('Taxonomy', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => [],
				'options' => $this->get_taxonomies(),
				'condition' => [
					'type' => 'terms',
				],
			]
		);

		$repeater->add_control(
			'text_prefix',
			[
				'label' => esc_html__('Before', 'bdthemes-element-pack'),
				'type' => Controls_Manager::TEXT,
				'condition' => [
					'type!' => 'custom',
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'show_avatar',
			[
				'label' => esc_html__('Avatar', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'type' => 'author',
				],
			]
		);

		$repeater->add_responsive_control(
			'avatar_size',
			[
				'label' => esc_html__('Size', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'em', 'rem', 'custom'],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .elementor-icon-list-icon' => 'width: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'show_avatar' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'comments_custom_strings',
			[
				'label' => esc_html__('Custom Format', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => false,
				'condition' => [
					'type' => 'comments',
				],
			]
		);

		$repeater->add_control(
			'string_no_comments',
			[
				'label' => esc_html__('No Comments', 'bdthemes-element-pack'),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__('No Comments', 'bdthemes-element-pack'),
				'condition' => [
					'comments_custom_strings' => 'yes',
					'type' => 'comments',
				],
			]
		);

		$repeater->add_control(
			'string_one_comment',
			[
				'label' => esc_html__('One Comment', 'bdthemes-element-pack'),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__('One Comment', 'bdthemes-element-pack'),
				'condition' => [
					'comments_custom_strings' => 'yes',
					'type' => 'comments',
				],
			]
		);

		$repeater->add_control(
			'string_comments',
			[
				'label' => esc_html__('Comments', 'bdthemes-element-pack'),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__('%s Comments', 'bdthemes-element-pack'),
				'condition' => [
					'comments_custom_strings' => 'yes',
					'type' => 'comments',
				],
			]
		);

		$repeater->add_control(
			'custom_text',
			[
				'label' => esc_html__('Custom', 'bdthemes-element-pack'),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'label_block' => true,
				'condition' => [
					'type' => 'custom',
				],
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => esc_html__('Link', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'type!' => 'time',
				],
			]
		);

		$repeater->add_control(
			'custom_url',
			[
				'label' => esc_html__('Custom URL', 'bdthemes-element-pack'),
				'type' => Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'condition' => [
					'type' => 'custom',
				],
			]
		);

		$repeater->add_control(
			'show_icon',
			[
				'label' => esc_html__('Icon', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'none' => esc_html__('None', 'bdthemes-element-pack'),
					'default' => esc_html__('Default', 'bdthemes-element-pack'),
					'custom' => esc_html__('Custom', 'bdthemes-element-pack'),
				],
				'default' => 'custom',
				'condition' => [
					'show_avatar!' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'selected_icon',
			[
				'label' => esc_html__('Choose Icon', 'bdthemes-element-pack'),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'condition' => [
					'show_icon' => 'custom',
					'show_avatar!' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_list',
			[
				'label' => '',
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'type' => 'author',
						'selected_icon' => [
							'value' => 'far fa-user-circle',
							'library' => 'fa-regular',
						],
					],
					[
						'type' => 'date',
						'selected_icon' => [
							'value' => 'fas fa-calendar',
							'library' => 'fa-solid',
						],
					],
					[
						'type' => 'time',
						'selected_icon' => [
							'value' => 'far fa-clock',
							'library' => 'fa-regular',
						],
					],
					[
						'type' => 'comments',
						'selected_icon' => [
							'value' => 'far fa-comment-dots',
							'library' => 'fa-regular',
						],
					],
				],
				'title_field' => '{{{ elementor.helpers.renderIcon( this, selected_icon, {}, "i", "panel" ) || \'<i class="{{ icon }}" aria-hidden="true"></i>\' }}} <span style="text-transform: capitalize;">{{{ type }}}</span>',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_icon_list',
			[
				'label' => esc_html__('List', 'bdthemes-element-pack'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'space_between',
			[
				'label' => esc_html__('Space Between', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', 'custom'],
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-items:not(.elementor-inline-items) .elementor-icon-list-item:not(:last-child)' => 'padding-bottom: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .elementor-icon-list-items:not(.elementor-inline-items) .elementor-icon-list-item:not(:first-child)' => 'margin-top: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .elementor-icon-list-items.elementor-inline-items .elementor-icon-list-item' => 'margin-right: calc({{SIZE}}{{UNIT}}/2); margin-left: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .elementor-icon-list-items.elementor-inline-items' => 'margin-right: calc(-{{SIZE}}{{UNIT}}/2); margin-left: calc(-{{SIZE}}{{UNIT}}/2)',
					'body.rtl {{WRAPPER}} .elementor-icon-list-items.elementor-inline-items .elementor-icon-list-item:after' => 'left: calc(-{{SIZE}}{{UNIT}}/2)',
					'body:not(.rtl) {{WRAPPER}} .elementor-icon-list-items.elementor-inline-items .elementor-icon-list-item:after' => 'right: calc(-{{SIZE}}{{UNIT}}/2)',
				],
			]
		);

		$this->add_responsive_control(
			'icon_align',
			[
				'label' => esc_html__('Alignment', 'bdthemes-element-pack'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__('Start', 'bdthemes-element-pack'),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__('Center', 'bdthemes-element-pack'),
						'icon' => 'eicon-h-align-center',
					],
					'right' => [
						'title' => esc_html__('End', 'bdthemes-element-pack'),
						'icon' => 'eicon-h-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-icon-list-item' => 'justify-content: {{VALUE}}',
					'{{WRAPPER}} .elementor-icon-list-items.elementor-inline-items' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'divider',
			[
				'label' => esc_html__('Divider', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => esc_html__('Off', 'bdthemes-element-pack'),
				'label_on' => esc_html__('On', 'bdthemes-element-pack'),
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-item:not(:last-child):after' => 'content: ""',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'divider_style',
			[
				'label' => esc_html__('Style', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'solid' => esc_html__('Solid', 'bdthemes-element-pack'),
					'double' => esc_html__('Double', 'bdthemes-element-pack'),
					'dotted' => esc_html__('Dotted', 'bdthemes-element-pack'),
					'dashed' => esc_html__('Dashed', 'bdthemes-element-pack'),
				],
				'default' => 'solid',
				'condition' => [
					'divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-items:not(.elementor-inline-items) .elementor-icon-list-item:not(:last-child):after' => 'border-top-style: {{VALUE}};',
					'{{WRAPPER}} .elementor-icon-list-items.elementor-inline-items .elementor-icon-list-item:not(:last-child):after' => 'border-left-style: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'divider_weight',
			[
				'label' => esc_html__('Weight', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 1,
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'condition' => [
					'divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-items:not(.elementor-inline-items) .elementor-icon-list-item:not(:last-child):after' => 'border-top-width: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .elementor-inline-items .elementor-icon-list-item:not(:last-child):after' => 'border-left-width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'divider_width',
			[
				'label' => esc_html__('Width', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
				'default' => [
					'unit' => '%',
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'condition' => [
					'divider' => 'yes',
					'view!' => 'inline',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-item:not(:last-child):after' => 'width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'divider_height',
			[
				'label' => esc_html__('Height', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', 'vh', 'custom'],
				'default' => [
					'unit' => '%',
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'condition' => [
					'divider' => 'yes',
					'view' => 'inline',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-item:not(:last-child):after' => 'height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'divider_color',
			[
				'label' => esc_html__('Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'default' => '#ddd',
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'condition' => [
					'divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-item:not(:last-child):after' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_icon_style',
			[
				'label' => esc_html__('Icon', 'bdthemes-element-pack'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs(
			'icon_tabs'
		);
		$this->start_controls_tab(
			'icon_tabs_normal',
			[
				'label' => __('Normal', 'bdthemes-element-pack'),
			]
		);
		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__('Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .elementor-icon-list-icon svg' => 'fill: {{VALUE}};',
				],
				'global' => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'icon_tabs_hover',
			[
				'label' => __('Hover', 'bdthemes-element-pack'),
			]
		);
		$this->add_control(
			'icon_color_hover',
			[
				'label' => esc_html__('Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-icon i:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .elementor-icon-list-icon svg:hover' => 'fill: {{VALUE}};',
				],
				'global' => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__('Size', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', 'custom'],
				'default' => [
					'size' => 14,
				],
				'range' => [
					'px' => [
						'min' => 6,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-icon img' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elementor-icon-list-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_text_style',
			[
				'label' => esc_html__('Text', 'bdthemes-element-pack'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs(
			'text_style_tabs'
		);
		$this->start_controls_tab(
			'text_tab_normal',
			[
				'label' => __('Normal', 'bdthemes-element-pack'),
			]
		);
		$this->add_control(
			'text_color',
			[
				'label' => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-text, {{WRAPPER}} .elementor-icon-list-text a' => 'color: {{VALUE}}',
				],
				'global' => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'text_tab_hover',
			[
				'label' => __('Hover', 'bdthemes-element-pack'),
			]
		);
		$this->add_control(
			'text_color_hover',
			[
				'label' => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-list-text:hover, {{WRAPPER}} .elementor-icon-list-text a:hover' => 'color: {{VALUE}}',
				],
				'global' => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_control(
			'text_indent',
			[
				'label' => esc_html__('Indent', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', 'custom'],
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'body:not(.rtl) {{WRAPPER}} .elementor-icon-list-text' => 'padding-left: {{SIZE}}{{UNIT}}',
					'body.rtl {{WRAPPER}} .elementor-icon-list-text' => 'padding-right: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'icon_typography',
				'selector' => '{{WRAPPER}} .elementor-icon-list-item .bdt-post-info__item',
			]
		);

		$this->end_controls_section();
	}

	protected function get_taxonomies() {
		$taxonomies = get_taxonomies([
			'show_in_nav_menus' => true,
		], 'objects');

		$options = [
			'' => esc_html__('Choose', 'bdthemes-element-pack'),
		];

		foreach ($taxonomies as $taxonomy) {
			$options[$taxonomy->name] = $taxonomy->label;
		}

		return $options;
	}

	protected function get_meta_data($repeater_item) {
		$item_data = [];

		switch ($repeater_item['type']) {
			case 'author':
				$item_data['text'] = get_the_author_meta('display_name');
				$item_data['icon'] = 'fa fa-user-circle-o'; // Default icon.
				$item_data['selected_icon'] = [
					'value' => 'far fa-user-circle',
					'library' => 'fa-regular',
				]; // Default icons.
				$item_data['itemprop'] = 'author';

				if ('yes' === $repeater_item['link']) {
					$item_data['url'] = [
						'url' => get_author_posts_url(get_the_author_meta('ID')),
					];
				}

				if ('yes' === $repeater_item['show_avatar']) {
					$item_data['image'] = get_avatar_url(get_the_author_meta('ID'), 96);
				}

				break;

			case 'date':
				$custom_date_format = empty($repeater_item['custom_date_format']) ? 'F j, Y' : $repeater_item['custom_date_format'];

				$format_options = [
					'default' => 'F j, Y',
					'0' => 'F j, Y',
					'1' => 'Y-m-d',
					'2' => 'm/d/Y',
					'3' => 'd/m/Y',
					'custom' => $custom_date_format,
				];

				$item_data['text'] = get_the_time($format_options[$repeater_item['date_format']]);
				$item_data['icon'] = 'fa fa-calendar'; // Default icon
				$item_data['selected_icon'] = [
					'value' => 'fas fa-calendar',
					'library' => 'fa-solid',
				]; // Default icons.
				$item_data['itemprop'] = 'datePublished';

				if ('yes' === $repeater_item['link']) {
					$item_data['url'] = [
						'url' => get_day_link(get_post_time('Y'), get_post_time('m'), get_post_time('j')),
					];
				}
				break;

			case 'time':
				$custom_time_format = empty($repeater_item['custom_time_format']) ? 'g:i a' : $repeater_item['custom_time_format'];

				$format_options = [
					'default' => 'g:i a',
					'0' => 'g:i a',
					'1' => 'g:i A',
					'2' => 'H:i',
					'custom' => $custom_time_format,
				];
				$item_data['text'] = get_the_time($format_options[$repeater_item['time_format']]);
				$item_data['icon'] = 'fa fa-clock-o'; // Default icon
				$item_data['selected_icon'] = [
					'value' => 'far fa-clock',
					'library' => 'fa-regular',
				]; // Default icons.
				break;

			case 'comments':
				if (comments_open()) {
					$default_strings = [
						'string_no_comments' => esc_html__('No Comments', 'bdthemes-element-pack'),
						'string_one_comment' => esc_html__('One Comment', 'bdthemes-element-pack'),
						'string_comments' => esc_html__('%s Comments', 'bdthemes-element-pack'),
					];

					if ('yes' === $repeater_item['comments_custom_strings']) {
						if (!empty($repeater_item['string_no_comments'])) {
							$default_strings['string_no_comments'] = $repeater_item['string_no_comments'];
						}

						if (!empty($repeater_item['string_one_comment'])) {
							$default_strings['string_one_comment'] = $repeater_item['string_one_comment'];
						}

						if (!empty($repeater_item['string_comments'])) {
							$default_strings['string_comments'] = $repeater_item['string_comments'];
						}
					}

					$num_comments = (int) get_comments_number(); // get_comments_number returns only a numeric value

					if (0 === $num_comments) {
						$item_data['text'] = $default_strings['string_no_comments'];
					} else {
						$item_data['text'] = sprintf(_n($default_strings['string_one_comment'], $default_strings['string_comments'], $num_comments, 'bdthemes-element-pack'), $num_comments);
					}

					if ('yes' === $repeater_item['link']) {
						$item_data['url'] = [
							'url' => get_comments_link(),
						];
					}
					$item_data['icon'] = 'fa fa-commenting-o'; // Default icon
					$item_data['selected_icon'] = [
						'value' => 'far fa-comment-dots',
						'library' => 'fa-regular',
					]; // Default icons.
					$item_data['itemprop'] = 'commentCount';
				}
				break;

			case 'terms':
				$item_data['icon'] = 'fa fa-tags'; // Default icon
				$item_data['selected_icon'] = [
					'value' => 'fas fa-tags',
					'library' => 'fa-solid',
				]; // Default icons.
				$item_data['itemprop'] = 'about';

				$taxonomy = $repeater_item['taxonomy'];
				$terms = wp_get_post_terms(get_the_ID(), $taxonomy);
				foreach ($terms as $term) {
					$item_data['terms_list'][$term->term_id]['text'] = $term->name;
					if ('yes' === $repeater_item['link']) {
						$item_data['terms_list'][$term->term_id]['url'] = get_term_link($term);
					}
				}
				break;

			case 'custom':
				$item_data['text'] = $repeater_item['custom_text'];
				$item_data['icon'] = 'fa fa-info-circle'; // Default icon.
				$item_data['selected_icon'] = [
					'value' => 'far fa-tags',
					'library' => 'fa-regular',
				]; // Default icons.

				if ('yes' === $repeater_item['link'] && !empty($repeater_item['custom_url'])) {
					$item_data['url'] = $repeater_item['custom_url'];
				}

				break;
		}

		$item_data['type'] = $repeater_item['type'];

		if (!empty($repeater_item['text_prefix'])) {
			$item_data['text_prefix'] = esc_html($repeater_item['text_prefix']);
		}

		return $item_data;
	}

	protected function render_item($repeater_item) {
		$item_data = $this->get_meta_data($repeater_item);
		$repeater_index = $repeater_item['_id'];

		if (empty($item_data['text']) && empty($item_data['terms_list'])) {
			return;
		}

		$has_link = false;
		$link_key = 'link_' . $repeater_index;
		$item_key = 'item_' . $repeater_index;

		$this->add_render_attribute(
			$item_key,
			'class',
			[
				'bdt-icon-list-item',
				'elementor-icon-list-item',
				'elementor-repeater-item-' . $repeater_item['_id'],
			]
		);

		$active_settings = $this->get_active_settings();

		if ('inline' === $active_settings['view']) {
			$this->add_render_attribute($item_key, 'class', 'elementor-inline-item');
		}

		if (!empty($item_data['url']['url'])) {
			$has_link = true;

			$this->add_link_attributes($link_key, $item_data['url']);
		}

		if (!empty($item_data['itemprop'])) {
			$this->add_render_attribute($item_key, 'itemprop', $item_data['itemprop']);
		}

?>
		<li <?php $this->print_render_attribute_string($item_key); ?>>
			<?php if ($has_link) : ?>
				<a <?php $this->print_render_attribute_string($link_key); ?>>
				<?php endif; ?>
				<?php $this->render_item_icon_or_image($item_data, $repeater_item, $repeater_index); ?>
				<?php $this->render_item_text($item_data, $repeater_index); ?>
				<?php if ($has_link) : ?>
				</a>
			<?php endif; ?>
		</li>
		<?php
	}

	protected function render_item_icon_or_image($item_data, $repeater_item, $repeater_index) {
		// Set icon according to user settings.
		$migration_allowed = Icons_Manager::is_migration_allowed();
		if (!$migration_allowed) {
			if ('custom' === $repeater_item['show_icon'] && !empty($repeater_item['icon'])) {
				$item_data['icon'] = $repeater_item['icon'];
			} elseif ('none' === $repeater_item['show_icon']) {
				$item_data['icon'] = '';
			}
		} else {
			if ('custom' === $repeater_item['show_icon'] && !empty($repeater_item['selected_icon'])) {
				$item_data['selected_icon'] = $repeater_item['selected_icon'];
			} elseif ('none' === $repeater_item['show_icon']) {
				$item_data['selected_icon'] = [];
			}
		}

		if (empty($item_data['icon']) && empty($item_data['selected_icon']) && empty($item_data['image'])) {
			return;
		}

		$migrated = isset($repeater_item['__fa4_migrated']['selected_icon']);
		$is_new = empty($repeater_item['icon']) && $migration_allowed;
		$show_icon = 'none' !== $repeater_item['show_icon'];

		if (!empty($item_data['image']) || $show_icon) {
		?>
			<span class="elementor-icon-list-icon">
				<?php
				if (!empty($item_data['image'])) :
					$image_data = 'image_' . $repeater_index;
					$this->add_render_attribute(
						$image_data,
						[
							'class' => 'elementor-avatar',
							'src' => $item_data['image'],
							'alt' => $item_data['text'],
							'loading' => 'lazy',
						]
					);
				?>
					<img <?php $this->print_render_attribute_string($image_data); ?>>
				<?php elseif ($show_icon) : ?>
					<?php if ($is_new || $migrated) :
						Icons_Manager::render_icon($item_data['selected_icon'], ['aria-hidden' => 'true']);
					else : ?>
						<i class="<?php echo esc_attr($item_data['icon']); ?>" aria-hidden="true"></i>
					<?php endif; ?>
				<?php endif; ?>
			</span>
		<?php
		}
	}

	protected function render_item_text($item_data, $repeater_index) {
		$repeater_setting_key = $this->get_repeater_setting_key('text', 'icon_list', $repeater_index);

		$this->add_render_attribute($repeater_setting_key, 'class', ['elementor-icon-list-text', 'bdt-post-info__item', 'bdt-post-info__item--type-' . $item_data['type']]);
		if (!empty($item['terms_list'])) {
			$this->add_render_attribute($repeater_setting_key, 'class', 'elementor-terms-list');
		}

		?>
		<span <?php $this->print_render_attribute_string($repeater_setting_key); ?>>
			<?php if (!empty($item_data['text_prefix'])) : ?>
				<span class="bdt-post-info__item-prefix"><?php echo esc_html($item_data['text_prefix']); ?></span>
			<?php endif; ?>
			<?php
			if (!empty($item_data['terms_list'])) :
				$terms_list = [];
				$item_class = 'bdt-post-info__terms-list-item';
			?>
				<span class="bdt-post-info__terms-list">
					<?php
					foreach ($item_data['terms_list'] as $term) :
						if (!empty($term['url'])) :
							$terms_list[] = '<a href="' . esc_attr($term['url']) . '" class="' . $item_class . '">' . esc_html($term['text']) . '</a>';
						else :
							$terms_list[] = '<span class="' . $item_class . '">' . esc_html($term['text']) . '</span>';
						endif;
					endforeach;

					// PHPCS - the variable $terms_list is safe.
					echo implode(', ', $terms_list); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</span>
			<?php else : ?>
				<?php
				echo wp_kses($item_data['text'], [
					'a' => [
						'href' => [],
						'title' => [],
						'rel' => [],
					],
				]);
				?>
			<?php endif; ?>
		</span>
	<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		ob_start();
		if (!empty($settings['icon_list'])) {
			foreach ($settings['icon_list'] as $repeater_item) {
				$this->render_item($repeater_item);
			}
		}
		$items_html = ob_get_clean();

		if (empty($items_html)) {
			return;
		}

		if ('inline' === $settings['view']) {
			$this->add_render_attribute('icon_list', 'class', 'elementor-inline-items');
		}

		$this->add_render_attribute('icon_list', 'class', ['bdt-post-info', 'elementor-icon-list-items',]);
	?>
		<ul <?php $this->print_render_attribute_string('icon_list'); ?>>
			<?php echo $items_html; ?>
		</ul>
<?php
	}
}
