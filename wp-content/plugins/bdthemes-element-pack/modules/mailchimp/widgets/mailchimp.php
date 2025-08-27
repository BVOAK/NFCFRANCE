<?php

namespace ElementPack\Modules\Mailchimp\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class Mailchimp extends Module_Base {

	public function get_name() {
		return 'bdt-mailchimp';
	}

	public function get_title() {
		return BDTEP . esc_html__( 'Mailchimp', 'bdthemes-element-pack' );
	}

	public function get_icon() {
		return 'bdt-wi-mailchimp';
	}

	public function get_categories() {
		return [ 'element-pack' ];
	}

	public function get_keywords() {
		return [ 'mailchimp', 'email', 'marketing', 'newsletter' ];
	}

	public function get_style_depends() {
		if ( $this->ep_is_edit_mode() ) {
			return [ 'ep-styles' ];
		} else {
			return [ 'ep-font', 'ep-mailchimp' ];
		}
	}

	public function get_script_depends() {
		if ( $this->ep_is_edit_mode() ) {
			return [ 'ep-scripts' ];
		} else {
			return [ 'ep-mailchimp' ];
		}
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/AVqliwiyMLg';
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return false;
	}
	
	protected function register_controls() {

		$this->start_controls_section(
			'section_content_layout',
			[ 
				'label' => esc_html__( 'Layout', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'show_before_icon',
			[ 
				'label' => esc_html__( 'Show Before Icon', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'before_icon_inline',
			[ 
				'label'        => esc_html__( 'Inline Before Icon', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'bdt-before-icon-inline--',
				'render_type'  => 'template',
				'condition'    => [ 
					'show_before_icon' => 'yes',
				],
			]
		);

		$this->add_control(
			'mailchimp_before_icon',
			[ 
				'label'            => esc_html__( 'Choose Icon', 'bdthemes-element-pack' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'before_icon',
				'default'          => [ 
					'value'   => 'far fa-envelope-open',
					'library' => 'fa-regular',
				],
				'condition'        => [ 
					'show_before_icon' => 'yes'
				],
				'label_block'      => false,
				'skin'             => 'inline'
			]
		);

		$this->add_control(
			'show_fname',
			[ 
				'label' => esc_html__( 'Show Name', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'fname_field_placeholder',
			[ 
				'label'       => esc_html__( 'Name Field Placeholder', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'label_block' => true,
				'default'     => esc_html__( 'Name ', 'bdthemes-element-pack' ),
				'placeholder' => esc_html__( 'Name ', 'bdthemes-element-pack' ),
				'condition'   => [ 
					'show_fname' => 'yes',
				]
			]
		);

		$this->add_control(
			'email_field_placeholder',
			[ 
				'label'       => esc_html__( 'Email Field Placeholder', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'label_block' => true,
				'default'     => esc_html__( 'Email *', 'bdthemes-element-pack' ),
				'placeholder' => esc_html__( 'Email *', 'bdthemes-element-pack' ),
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'before_text',
			[ 
				'label'       => esc_html__( 'Before Text', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => esc_html__( 'Before Text', 'bdthemes-element-pack' ),
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'after_text',
			[ 
				'label'       => esc_html__( 'After Text', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => esc_html__( 'After Text', 'bdthemes-element-pack' ),
			]
		);

		$this->add_responsive_control(
			'align',
			[ 
				'label'        => esc_html__( 'Text Alignment', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::CHOOSE,
				'prefix_class' => 'elementor%s-align-',
				'default'      => '',
				'options'      => [ 
					'left'    => [ 
						'title' => esc_html__( 'Left', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [ 
						'title' => esc_html__( 'Center', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [ 
						'title' => esc_html__( 'Right', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [ 
						'title' => esc_html__( 'Justified', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'conditions'   => [ 
					'relation' => 'or',
					'terms'    => [ 
						[ 
							'name'     => 'before_text',
							'operator' => '!=',
							'value'    => '',
						],
						[ 
							'name'     => 'after_text',
							'operator' => '!=',
							'value'    => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'fullwidth_input',
			[ 
				'label'        => esc_html__( 'Fullwidth Fields', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
				'separator'    => 'before',
			]
		);

		$this->add_responsive_control(
			'flex_direction',
			[ 
				'label'     => esc_html__( 'Fields Direction', 'bdthemes-element-pack' ) . BDTEP_NC,
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'column',
				'options'   => [ 
					'row'    => [ 
						'title' => esc_html__( 'Row', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-h-align-left',
					],
					'column' => [ 
						'title' => esc_html__( 'Column', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-v-align-top',
					],
				],
				'condition' => [ 
					'fullwidth_input' => 'yes',
				],
				'selectors_dictionary' => [
					'row' => 'flex-direction: row; align-items: center;',
					'column' => 'flex-direction: column; align-items: flex-start;',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-mailchimp' => '{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'fullwidth_button',
			[ 
				'label'     => esc_html__( 'Fullwidth Button', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SWITCHER,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-newsletter-signup-wrapper' => 'width: 100%;',
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-newsletter-signup-wrapper button' => 'width: 100%;',
				],
				'condition' => [ 
					'fullwidth_input' => 'yes',
				],
			]
		);
		
		$this->add_responsive_control(
			'button_alignment',
			[ 
				'label'     => esc_html__( 'Button Alignment', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [ 
					'left'     => [ 
						'title' => esc_html__( 'Left', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'   => [ 
						'title' => esc_html__( 'Center', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-center',
					],
					'flex-end' => [ 
						'title' => esc_html__( 'Right', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors_dictionary' => [
					'left' => 'align-items: flex-start;',
					'center' => 'align-items: center;',
					'flex-end' => 'align-items: flex-end;',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-mailchimp' => '{{VALUE}}',
				],
				'condition' => [ 
					'fullwidth_input'  => 'yes',
					'fullwidth_button' => '',
				],
			]
		);

		$this->add_control(
			'space',
			[ 
				'label'   => esc_html__( 'Space Between', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => [ 
					''         => esc_html__( 'Default', 'bdthemes-element-pack' ),
					'small'    => esc_html__( 'Small', 'bdthemes-element-pack' ),
					'medium'   => esc_html__( 'Medium', 'bdthemes-element-pack' ),
					'large'    => esc_html__( 'Large', 'bdthemes-element-pack' ),
					'collapse' => esc_html__( 'Collapse', 'bdthemes-element-pack' ),
					'custom'   => esc_html__( 'Custom', 'bdthemes-element-pack' ),
				],
				'selectors_dictionary' => [
					''         => 'gap: 40px;',
					'small'    => 'gap: 15px;',
					'medium'   => 'gap: 30px;',
					'large'    => 'gap: 70px;',
					'collapse' => 'gap: 0;',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-mailchimp' => '{{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'gap_custom',
			[ 
				'label'      => esc_html__( 'Custom Gap', 'bdthemes-element-pack' ) . BDTEP_NC,
				'type'       => Controls_Manager::SLIDER,
				'range'      => [ 
					'px' => [ 
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-mailchimp' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 
					'space' => 'custom',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_button',
			[ 
				'label' => esc_html__( 'Signup Button', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'button_text',
			[ 
				'label'       => esc_html__( 'Button Text', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => esc_html__( 'SIGNUP', 'bdthemes-element-pack' ),
				'default'     => esc_html__( 'SIGNUP', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'mailchimp_button_icon',
			[ 
				'label'            => esc_html__( 'Icon', 'bdthemes-element-pack' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'label_block'      => false,
				'skin'             => 'inline'
			]
		);

		$this->add_control(
			'icon_align',
			[ 
				'label'     => esc_html__( 'Icon Position', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'right',
				'options'   => [ 
					'left'   => esc_html__( 'Left', 'bdthemes-element-pack' ),
					'right'  => esc_html__( 'Right', 'bdthemes-element-pack' ),
					'top'    => esc_html__( 'Top', 'bdthemes-element-pack' ),
					'bottom' => esc_html__( 'Bottom', 'bdthemes-element-pack' ),
				],
				'condition' => [ 
					'mailchimp_button_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'icon_indent',
			[ 
				'label'     => esc_html__( 'Icon Spacing', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 
					'px' => [ 
						'max' => 100,
					],
				],
				'default'   => [ 
					'size' => 8,
				],
				'condition' => [ 
					'mailchimp_button_icon[value]!' => '',
				],
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-btn .bdt-flex-align-right'  => is_rtl() ? 'margin-right: {{SIZE}}{{UNIT}};' : 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-newsletter-btn .bdt-flex-align-left'   => is_rtl() ? 'margin-left: {{SIZE}}{{UNIT}};' : 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-newsletter-btn .bdt-flex-align-top'    => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-newsletter-btn .bdt-flex-align-bottom' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_before_icon',
			[ 
				'label'     => esc_html__( 'Before Icon', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 
					'show_before_icon'              => 'yes',
					'mailchimp_before_icon[value]!' => '',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_before_icon_style' );

		$this->start_controls_tab(
			'tab_before_icon_normal',
			[ 
				'label' => esc_html__( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'before_icon_color',
			[ 
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-before-icon'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-newsletter-before-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[ 
				'name'     => 'before_icon_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .bdt-newsletter-before-icon',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[ 
				'name'        => 'before_icon_border',
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-newsletter-before-icon',
			]
		);

		$this->add_responsive_control(
			'before_icon_radius',
			[ 
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-newsletter-before-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'before_icon_padding',
			[ 
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-newsletter-before-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'before_icon_margin',
			[ 
				'label'      => esc_html__( 'Margin', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-newsletter-before-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[ 
				'name'     => 'before_icon_shadow',
				'selector' => '{{WRAPPER}} .bdt-newsletter-before-icon',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[ 
				'name'     => 'before_icon_typography',
				'selector' => '{{WRAPPER}} .bdt-newsletter-before-icon',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_before_icon_hover',
			[ 
				'label' => esc_html__( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'before_icon_hover_color',
			[ 
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-before-icon:hover'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-newsletter-before-icon:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[ 
				'name'     => 'before_icon_hover_background',
				'selector' => '{{WRAPPER}} .bdt-newsletter-before-icon:hover',
			]
		);

		$this->add_control(
			'before_icon_hover_border_color',
			[ 
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 
					'before_icon_border_border!' => '',
				],
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-before-icon:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_input',
			[ 
				'label' => esc_html__( 'Field', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'input_placeholder_color',
			[ 
				'label'     => esc_html__( 'Placeholder Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper input[type*="email"]::placeholder, {{WRAPPER}} .bdt-newsletter-wrapper input[type*="text"]::placeholder' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_color',
			[ 
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-input' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_background',
			[ 
				'label'     => esc_html__( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-input' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[ 
				'name'     => 'placeholder_typography',
				'label'    => esc_html__( 'Typography', 'bdthemes-element-pack' ),
				'selector' => '{{WRAPPER}} .bdt-newsletter-wrapper .bdt-input',
			]
		);

		$this->add_control(
			'input_border_show',
			[ 
				'label'     => esc_html__( 'Border Style', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[ 
				'name'        => 'input_border',
				'label'       => esc_html__( 'Border', 'bdthemes-element-pack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-newsletter-wrapper .bdt-input',
				'condition'   => [ 
					'input_border_show' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'input_radius',
			[ 
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'input_padding',
			[ 
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_button',
			[ 
				'label' => esc_html__( 'Sign Up Button', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );
		$this->start_controls_tab(
			'tab_button_normal',
			[ 
				'label' => esc_html__( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[ 
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-button.bdt-button-primary' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'background_color',
			[ 
				'label'     => esc_html__( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-button.bdt-button-primary' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[ 
				'name'        => 'border',
				'label'       => esc_html__( 'Border', 'bdthemes-element-pack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-newsletter-wrapper .bdt-button.bdt-button-primary',
			]
		);

		$this->add_responsive_control(
			'radius',
			[ 
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-button.bdt-button-primary' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_padding',
			[ 
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-button.bdt-button-primary' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_width',
			[ 
				'label'      => esc_html__( 'Width', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 
					'px' => [ 
						'min' => 100,
						'max' => 500,
					],
					'%'  => [ 
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-newsletter-signup-wrapper' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-newsletter-signup-wrapper button' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 
					'fullwidth_button' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[ 
				'name'     => 'button_shadow',
				'selector' => '{{WRAPPER}} .bdt-newsletter-wrapper .bdt-button.bdt-button-primary',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[ 
				'name'     => 'button_typography',
				'label'    => esc_html__( 'Typography', 'bdthemes-element-pack' ),
				'selector' => '{{WRAPPER}} .bdt-newsletter-wrapper .bdt-button.bdt-button-primary',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[ 
				'label' => esc_html__( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'hover_color',
			[ 
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-button.bdt-button-primary:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[ 
				'label'     => esc_html__( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-button.bdt-button-primary:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[ 
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 
					'border_border!' => '',
				],
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-button.bdt-button-primary:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[ 
				'label' => esc_html__( 'Hover Animation', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_icon',
			[ 
				'label'     => esc_html__( 'Signup Button Icon', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 
					'mailchimp_button_icon[value]!' => '',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_signup_btn_icon_style' );

		$this->start_controls_tab(
			'tab_signup_btn_icon_normal',
			[ 
				'label' => esc_html__( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'signup_btn_icon_color',
			[ 
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-btn .bdt-newsletter-btn-icon'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-newsletter-btn .bdt-newsletter-btn-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[ 
				'name'     => 'signup_btn_icon_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .bdt-newsletter-btn .bdt-newsletter-btn-icon',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[ 
				'name'        => 'signup_btn_icon_border',
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-newsletter-btn .bdt-newsletter-btn-icon',
			]
		);

		$this->add_responsive_control(
			'signup_btn_icon_radius',
			[ 
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-newsletter-btn .bdt-newsletter-btn-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'signup_btn_icon_padding',
			[ 
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-newsletter-btn .bdt-newsletter-btn-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'signup_btn_icon_margin',
			[ 
				'label'      => esc_html__( 'Margin', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'{{WRAPPER}} .bdt-newsletter-btn .bdt-newsletter-btn-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[ 
				'name'     => 'signup_btn_icon_shadow',
				'selector' => '{{WRAPPER}} .bdt-newsletter-btn .bdt-newsletter-btn-icon',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[ 
				'name'      => 'signup_btn_icon_typography',
				'selector'  => '{{WRAPPER}} .bdt-newsletter-btn .bdt-newsletter-btn-icon',
				'separator' => 'before',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_signup_btn_icon_hover',
			[ 
				'label' => esc_html__( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'signup_btn_icon_hover_color',
			[ 
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-btn:hover .bdt-newsletter-btn-icon'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-newsletter-btn:hover .bdt-newsletter-btn-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[ 
				'name'     => 'signup_btn_icon_hover_background',
				'selector' => '{{WRAPPER}} .bdt-newsletter-btn:hover .bdt-newsletter-btn-icon',
			]
		);

		$this->add_control(
			'icon_hover_border_color',
			[ 
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 
					'signup_btn_icon_border_border!' => '',
				],
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-btn:hover .bdt-newsletter-btn-icon' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_before_text',
			[ 
				'label'     => esc_html__( 'Before Text', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 
					'before_text!' => '',
				],
			]
		);

		$this->add_control(
			'before_text_color',
			[ 
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-newsletter-before-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'before_text_spacing',
			[ 
				'label'     => esc_html__( 'Spacing', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 
					'px' => [ 
						'max' => 100,
					],
				],
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-newsletter-before-text' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[ 
				'name'     => 'before_text_typography',
				'label'    => esc_html__( 'Typography', 'bdthemes-element-pack' ),
				'selector' => '{{WRAPPER}} .bdt-newsletter-wrapper .bdt-newsletter-before-text',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_after_text',
			[ 
				'label'     => esc_html__( 'After Text', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 
					'after_text!' => '',
				],
			]
		);

		$this->add_control(
			'after_text_color',
			[ 
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-newsletter-after-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'after_text_spacing',
			[ 
				'label'     => esc_html__( 'Spacing', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 
					'px' => [ 
						'max' => 100,
					],
				],
				'selectors' => [ 
					'{{WRAPPER}} .bdt-newsletter-wrapper .bdt-newsletter-after-text' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[ 
				'name'     => 'after_text_typography',
				'label'    => esc_html__( 'Typography', 'bdthemes-element-pack' ),
				'selector' => '{{WRAPPER}} .bdt-newsletter-wrapper .bdt-newsletter-after-text',
			]
		);

		$this->end_controls_section();
	}

	public function render_text( $settings ) {

		$this->add_render_attribute( 'content-wrapper', 'class', 'bdt-newsletter-btn-content-wrapper' );

		if ( 'left' == $settings['icon_align'] or 'right' == $settings['icon_align'] ) {
			$this->add_render_attribute( 'content-wrapper', 'class', 'bdt-flex bdt-flex-middle bdt-flex-center' );
		}

		$this->add_render_attribute( 'content-wrapper', 'class', ( 'top' == $settings['icon_align'] ) ? 'bdt-flex bdt-flex-column bdt-flex-center' : '' );
		$this->add_render_attribute( 'content-wrapper', 'class', ( 'bottom' == $settings['icon_align'] ) ? 'bdt-flex bdt-flex-column-reverse bdt-flex-center' : '' );

		$this->add_render_attribute( 'icon-align', 'class', 'elementor-align-icon-' . $settings['icon_align'] );
		$this->add_render_attribute( 'icon-align', 'class', 'bdt-newsletter-btn-icon' );

		$this->add_render_attribute( 'text', 'class', [ 'bdt-newsletter-btn-text', 'bdt-display-inline-block' ] );
		$this->add_inline_editing_attributes( 'text', 'none' );

		if ( ! isset( $settings['icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['icon'] = 'fas fa-arrow-right';
		}

		$migrated = isset( $settings['__fa4_migrated']['mailchimp_button_icon'] );
		$is_new   = empty( $settings['icon'] ) && Icons_Manager::is_migration_allowed();

		?>
		<div <?php $this->print_render_attribute_string( 'content-wrapper' ); ?>>
			<?php if ( ! empty( $settings['mailchimp_button_icon']['value'] ) ) : ?>
				<div class="bdt-newsletter-btn-icon bdt-flex-align-<?php echo esc_attr( $settings['icon_align'] ); ?>">

					<?php if ( $is_new || $migrated ) :
						Icons_Manager::render_icon( $settings['mailchimp_button_icon'], [ 'aria-hidden' => 'true', 'class' => 'fa-fw' ] );
					else : ?>
						<i class="<?php echo esc_attr( $settings['icon'] ); ?>" aria-hidden="true"></i>
					<?php endif; ?>

				</div>
			<?php endif; ?>
			<div <?php $this->print_render_attribute_string( 'text' ); ?>>
				<?php echo wp_kses( $settings['button_text'], element_pack_allow_tags( 'title' ) ); ?>
			</div>
		</div>
		<?php
	}

	public function render_before_icon() {
		$settings = $this->get_settings_for_display();

		$migrated = isset( $settings['__fa4_migrated']['mailchimp_before_icon'] );
		$is_new   = empty( $settings['before_icon'] ) && Icons_Manager::is_migration_allowed();

		if ( $settings['show_before_icon'] and ! empty( $settings['mailchimp_before_icon']['value'] ) ) : ?>
			<div class="bdt-before-icon">
				<div class="bdt-newsletter-before-icon">

					<?php if ( $is_new || $migrated ) :
						Icons_Manager::render_icon( $settings['mailchimp_before_icon'], [ 'aria-hidden' => 'true', 'class' => 'fa-fw' ] );
					else : ?>
						<i class="<?php echo esc_attr( $settings['before_icon'] ); ?>" aria-hidden="true"></i>
					<?php endif; ?>

				</div>
			</div>
		<?php endif;
	}

	public function render() {
		$settings = $this->get_settings_for_display();
		$id       = 'bdt-mailchimp-' . $this->get_id();

		// $space = ( '' !== $settings['space'] ) ? ' bdt-grid-' . $settings['space'] : '';

		if ( $settings['button_text'] ) {
			$button_text = $settings['button_text'];
		} else {
			$button_text = esc_html__( 'Subscribe', 'bdthemes-element-pack' );
		}

		$this->add_render_attribute( 'input-wrapper', 'class', 'bdt-newsletter-input-wrapper' );

		// if ( $settings['fullwidth_input'] ) {
		// 	$this->add_render_attribute( 'input-wrapper', 'class', 'bdt-width-1-1' );
		// } else {
		// 	$this->add_render_attribute( 'input-wrapper', 'class', 'bdt-width-expand' );
		// }

		if ( ! isset( $settings['before_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['before_icon'] = 'fas fa-envelope-open';
		}

		$form_id = ! empty( $settings['_element_id'] ) ? 'bdt-sf-' . $settings['_element_id'] : 'bdt-sf-' . $id;

		?>
		<div class="bdt-newsletter-wrapper">

			<?php if ( ! empty( $settings['before_text'] ) ) : ?>
				<div class="bdt-newsletter-before-text">
					<?php echo esc_html( $settings['before_text'] ); ?>
				</div>
			<?php endif; ?>

			<form action="<?php echo esc_url( site_url() ); ?>/wp-admin/admin-ajax.php" class="bdt-mailchimp bdt-flex">

				<?php if($settings['before_icon_inline'] !== 'yes') { $this->render_before_icon(); } ?>

				<?php if ( $settings['show_fname'] == 'yes' ) : ?>
					<div <?php $this->print_render_attribute_string( 'input-wrapper' ); ?>>
						<div class="bdt-position-relative">
							<?php if($settings['before_icon_inline'] == 'yes') { 
								if ( $settings['show_before_icon'] and ! empty( $settings['mailchimp_before_icon']['value'] ) ) : ?>
									<div class="bdt-width-auto bdt-before-icon">
										<div class="bdt-newsletter-before-icon">
											<i class="ep-icon-user-circle-o" aria-hidden="true"></i>
										</div>
									</div>
								<?php endif;
							 } ?>
							<input type="text" name="fname" placeholder="<?php echo esc_html( $settings['fname_field_placeholder'] ); ?>" class="bdt-input" />
							
						</div>
					</div>
				<?php endif; ?>

				<div <?php $this->print_render_attribute_string( 'input-wrapper' ); ?>>
					<div class="bdt-position-relative">
						<?php if($settings['before_icon_inline'] == 'yes') { $this->render_before_icon(); } ?>
						<input type="email" name="email" placeholder="<?php echo esc_html( $settings['email_field_placeholder'] ); ?>"
							required class="bdt-input" />
						<input type="hidden" name="action" value="element_pack_mailchimp_subscribe" />
						<input type="hidden" name="<?php echo esc_attr( $form_id ); ?>" value="true" />
						<!-- we need action parameter to receive ajax request in WordPress -->

					</div>
				</div>
				<?php


				$this->add_render_attribute( 'signup_button', 'class', [ 'bdt-newsletter-btn', 'bdt-button', 'bdt-button-primary', 'bdt-width-1-1' ] );

				if ( $settings['hover_animation'] ) {
					$this->add_render_attribute( 'signup_button', 'class', 'elementor-animation-' . $settings['hover_animation'] );
				}

				?>
				<div class="bdt-newsletter-signup-wrapper">
					<button type="submit" <?php $this->print_render_attribute_string( 'signup_button' ); ?>>
						<?php $this->render_text( $settings ); ?>
					</button>
				</div>
			</form>

			<!-- after text -->
			<?php if ( ! empty( $settings['after_text'] ) ) : ?>
				<div class="bdt-newsletter-after-text">
					<?php echo esc_html( $settings['after_text'] ); ?>
				</div>
			<?php endif; ?>

		</div><!-- end newsletter-signup -->


		<?php
	}
}
