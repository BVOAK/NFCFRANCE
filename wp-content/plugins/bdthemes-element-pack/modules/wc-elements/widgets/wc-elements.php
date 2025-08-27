<?php

namespace ElementPack\Modules\WcElements\Widgets;

use Elementor\Controls_Manager;
use ElementPack\Base\Module_Base;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use ElementPack\Modules\WcElements\Module;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WC_Elements extends Module_Base {

	public function get_name() {
		return 'bdt-wc-elements';
	}

	public function get_title() {
		return BDTEP . esc_html__('WC - Elements', 'bdthemes-element-pack');
	}

	public function get_icon() {
		return 'bdt-wi-wc-elements';
	}

	public function get_categories() {
		return ['element-pack'];
	}

	public function get_keywords() {
		return ['cart', 'woocommerce', 'single', 'product', 'checkout', 'order', 'tracking', 'form', 'account', 'wc elements'];
	}

	public function get_style_depends() {
		if ($this->ep_is_edit_mode()) {
			return [ 'ep-font', 'ep-styles' ];
		} else {
			return [ 'ep-font', 'ep-wc-elements' ];
		}
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/9IoAw7MUomA?si=LGU9PzwZ1Wx4Jm5k';
	}

	public function on_export($element) {
		unset($element['settings']['product_id']);

		return $element;
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return false;
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_product',
			[
				'label' => esc_html__('Element', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'element',
			[
				'label' => esc_html__('Element', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					''                           => esc_html__('Select', 'bdthemes-element-pack'),
					'woocommerce_cart'           => esc_html__('Cart Page', 'bdthemes-element-pack'),
					'product_page'               => esc_html__('Single Product Page', 'bdthemes-element-pack'),
					'woocommerce_checkout'       => esc_html__('Checkout Page', 'bdthemes-element-pack'),
					'woocommerce_order_tracking' => esc_html__('Order Tracking Form', 'bdthemes-element-pack'),
					'woocommerce_my_account'     => esc_html__('My Account', 'bdthemes-element-pack'),
				],
			]
		);

		$this->add_control(
			'product_id',
			[
				'label'     => esc_html__('Select Product', 'bdthemes-element-pack') . BDTEP_UC,
				'type'      => Controls_Manager::SELECT,
				'default'   => '0',
				'options'   => element_pack_get_all_woocommerce_product_title(),
				'condition' => [
					'element' => ['product_page'],
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_checkout_style_label',
			[
				'label' => esc_html__('Label', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_checkout'],
				],
			]
		);

		$this->add_control(
			'label_color',
			[
				'label'     => esc_html__('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce form .form-row label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'required_color',
			[
				'label'     => esc_html__('Required Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce form .form-row .required' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'label' => esc_html__('Typography', 'bdthemes-element-pack'),
				//'scheme' => Schemes\Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .woocommerce form .form-row label',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_checkout_style_input',
			[
				'label' => esc_html__('Input', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_checkout'],
				],
			]
		);

		$this->add_control(
			'input_placeholder_color',
			[
				'label'     => esc_html__('Placeholder Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce .input-text::placeholder' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_text_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce .input-text' => 'color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce select' => 'color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce textarea.input-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_text_background',
			[
				'label'     => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce .input-text' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce select' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce textarea.input-text' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'textarea_height',
			[
				'label' => esc_html__('Textarea Height', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 125,
				],
				'range' => [
					'px' => [
						'min' => 30,
						'max' => 500,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .woocommerce textarea.input-text' => 'height: {{SIZE}}{{UNIT}}; display: block;',
				],
				'separator' => 'before',

			]
		);

		$this->add_control(
			'input_padding',
			[
				'label' => esc_html__('Padding', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce .input-text,
					 {{WRAPPER}} .woocommerce textarea.input-text,
					 {{WRAPPER}} .select2-container--default .select2-selection--single,
					 {{WRAPPER}} .woocommerce select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .select2-container--default .select2-selection--single' => 'height: auto; min-height: 37px;',
					'{{WRAPPER}} .select2-container--default .select2-selection--single .select2-selection__rendered' => 'line-height: initial;',
				],
			]
		);

		$this->add_responsive_control(
			'input_space',
			[
				'label' => esc_html__('Element Space', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 25,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .woocommerce form .form-row' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'input_border_show',
			[
				'label' => esc_html__('Border Style', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
				'label_on' => 'Hide',
				'label_off' => 'Show',
				'return_value' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'input_border',
				'label'       => esc_html__('Border', 'bdthemes-element-pack'),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '
					{{WRAPPER}} .woocommerce .input-text,
					{{WRAPPER}} .woocommerce select,
					{{WRAPPER}} .select2-container--default .select2-selection--single',
				'condition' => [
					'input_border_show' => 'yes',
				],
			]
		);

		$this->add_control(
			'input_border_radius',
			[
				'label' => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce .input-text,
					 {{WRAPPER}} .select2-container--default .select2-selection--single,
					 {{WRAPPER}} .woocommerce select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_checkout_style_order_table',
			[
				'label' => esc_html__('Order Table', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_checkout'],
				],
			]
		);

		$this->add_control(
			'order_table_padding',
			[
				'label' => esc_html__('Padding', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce table.shop_table th,
					{{WRAPPER}} .woocommerce table.shop_table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'order_table_border_show',
			[
				'label' => esc_html__('Border Style', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
				'label_on' => 'Hide',
				'label_off' => 'Show',
				'return_value' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'order_table_border',
				'label'       => esc_html__('Border', 'bdthemes-element-pack'),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .woocommerce table.shop_table',

				'condition' => [
					'order_table_border_show' => 'yes',
				],
			]
		);

		$this->add_control(
			'order_table_border_radius',
			[
				'label' => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce table.shop_table' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);


		$this->end_controls_section();



		// Payment section
		$this->start_controls_section(
			'section_style_checkout_payment',
			[
				'label' => esc_html__('Payment', 'bdthemes-element-pack'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_checkout'],
				],
			]
		);

		$this->add_control(
			'checkout_payment_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-checkout #payment, {{WRAPPER}} .woocommerce-checkout #payment div.payment_box' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'checkout_payment_background',
			[
				'label'     => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-checkout #payment' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce-checkout #payment div.payment_box' => 'opacity:0.5;',
					'{{WRAPPER}} .woocommerce-checkout #payment div.payment_box::before' => 'opacity:0.5;',
				],
			]
		);

		$this->add_control(
			'checkout_payment_button_heading',
			[
				'label' => esc_html__('Button Style', 'bdthemes-element-pack'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs('tabs_payment_button_style');

		$this->start_controls_tab(
			'tab_payment_button_normal',
			[
				'label' => esc_html__('Normal', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'payment_button_text_color',
			[
				'label' => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'payment_button_background_color',
			[
				'label' => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'payment_button_border',
				'label' => esc_html__('Border', 'bdthemes-element-pack'),
				'placeholder' => '1px',
				'default' => '1px',
				'selector' => '{{WRAPPER}} .woocommerce input.button',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'payment_button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'payment_button_box_shadow',
				'selector' => '{{WRAPPER}} .wpcf7-submit',
			]
		);

		$this->add_control(
			'payment_button_text_padding',
			[
				'label' => esc_html__('Padding', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'payment_button_typography',
				'label' => esc_html__('Typography', 'bdthemes-element-pack'),
				//'scheme' => Schemes\Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .woocommerce input.button',
				'separator' => 'before',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_payment_button_hover',
			[
				'label' => esc_html__('Hover', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'payment_button_hover_color',
			[
				'label' => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'payment_button_background_hover_color',
			[
				'label' => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'payment_button_hover_border_color',
			[
				'label' => esc_html__('Border Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();



		// TRacking section
		$this->start_controls_section(
			'section_tracking_style_label',
			[
				'label' => esc_html__('Label', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_order_tracking'],
				],
			]
		);

		$this->add_control(
			'tracking_label_color',
			[
				'label'     => esc_html__('Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce form .form-row label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'tracking_label_typography',
				'label' => esc_html__('Typography', 'bdthemes-element-pack'),
				//'scheme' => Schemes\Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .woocommerce form .form-row label',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_tracking_style_input',
			[
				'label' => esc_html__('Input', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_order_tracking'],
				],
			]
		);

		$this->add_control(
			'tracking_input_placeholder_color',
			[
				'label'     => esc_html__('Placeholder Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce .input-text::placeholder' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tracking_input_text_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce .input-text' => 'color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce select' => 'color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce textarea.input-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tracking_input_text_background',
			[
				'label'     => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce .input-text' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce select' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce textarea.input-text' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tracking_input_padding',
			[
				'label' => esc_html__('Padding', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce .input-text,
					 {{WRAPPER}} .woocommerce textarea.input-text,
					 {{WRAPPER}} .select2-container--default .select2-selection--single,
					 {{WRAPPER}} .woocommerce select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .select2-container--default .select2-selection--single' => 'height: auto; min-height: 37px;',
					'{{WRAPPER}} .select2-container--default .select2-selection--single .select2-selection__rendered' => 'line-height: initial;',
				],
			]
		);

		$this->add_responsive_control(
			'tracking_input_space',
			[
				'label' => esc_html__('Element Space', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 25,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .woocommerce form .form-row' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'tracking_input_border_show',
			[
				'label' => esc_html__('Border Style', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
				'label_on' => 'Hide',
				'label_off' => 'Show',
				'return_value' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'tracking_input_border',
				'label'       => esc_html__('Border', 'bdthemes-element-pack'),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '
					{{WRAPPER}} .woocommerce .input-text,
					{{WRAPPER}} .woocommerce select,
					{{WRAPPER}} .select2-container--default .select2-selection--single',
				'condition' => [
					'tracking_input_border_show' => 'yes',
				],
			]
		);

		$this->add_control(
			'tracking_input_border_radius',
			[
				'label' => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce .input-text,
					 {{WRAPPER}} .select2-container--default .select2-selection--single,
					 {{WRAPPER}} .woocommerce select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_style_tracking',
			[
				'label' => esc_html__('Button', 'bdthemes-element-pack'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_order_tracking'],
				],
			]
		);

		$this->add_control(
			'tracking_button_heading',
			[
				'label' => esc_html__('Button Style', 'bdthemes-element-pack'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs('tabs_tracking_button_style');

		$this->start_controls_tab(
			'tab_tracking_button_normal',
			[
				'label' => esc_html__('Normal', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'tracking_button_text_color',
			[
				'label' => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button, {{WRAPPER}} .woocommerce button.button, {{WRAPPER}} .woocommerce a.button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tracking_button_background_color',
			[
				'label' => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button, {{WRAPPER}} .woocommerce button.button, {{WRAPPER}} .woocommerce a.button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'tracking_button_border',
				'label' => esc_html__('Border', 'bdthemes-element-pack'),
				'placeholder' => '1px',
				'default' => '1px',
				'selector' => '{{WRAPPER}} .woocommerce input.button, {{WRAPPER}} .woocommerce button.button, {{WRAPPER}} .woocommerce a.button',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'tracking_button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button, {{WRAPPER}} .woocommerce button.button, {{WRAPPER}} .woocommerce a.button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'tracking_button_box_shadow',
				'selector' => '{{WRAPPER}} .wpcf7-submit, {{WRAPPER}} .woocommerce button.button, {{WRAPPER}} .woocommerce a.button',
			]
		);

		$this->add_control(
			'tracking_button_text_padding',
			[
				'label' => esc_html__('Padding', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button, {{WRAPPER}} .woocommerce button.button, {{WRAPPER}} .woocommerce a.button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'tracking_button_typography',
				'label' => esc_html__('Typography', 'bdthemes-element-pack'),
				//'scheme' => Schemes\Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .woocommerce input.button, {{WRAPPER}} .woocommerce button.button, {{WRAPPER}} .woocommerce a.button',
				'separator' => 'before',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_tracking_button_hover',
			[
				'label' => esc_html__('Hover', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'tracking_button_hover_color',
			[
				'label' => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button:hover, {{WRAPPER}} .woocommerce button.button:hover, {{WRAPPER}} .woocommerce a.button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tracking_button_background_hover_color',
			[
				'label' => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button:hover, {{WRAPPER}} .woocommerce button.button:hover, {{WRAPPER}} .woocommerce a.button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tracking_button_hover_border_color',
			[
				'label' => esc_html__('Border Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'tracking_button_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .woocommerce input.button:hover, {{WRAPPER}} .woocommerce button.button:hover, {{WRAPPER}} .woocommerce a.button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// Cart style

		$this->start_controls_section(
			'section_cart_style_heading',
			[
				'label' => esc_html__('Table Heading', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_cart'],
				],
			]
		);

		$this->add_control(
			'cart_table_heading_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce table.shop_table.cart th' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_table_heading_background',
			[
				'label'     => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce table.shop_table.cart th' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_cart_style_table',
			[
				'label' => esc_html__('Table Content', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_cart'],
				],
			]
		);

		$this->add_control(
			'cart_table_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce table.shop_table.cart td *' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_table_background',
			[
				'label'     => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce table.shop_table.cart' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_table_padding',
			[
				'label' => esc_html__('Padding', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce table.shop_table.cart td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'cart_table_border_show',
			[
				'label' => esc_html__('Border Style', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
				'label_on' => 'Hide',
				'label_off' => 'Show',
				'return_value' => 'yes',
				'separator' => 'before',
			]
		);



		$this->add_control(
			'cart_table_border_width',
			[
				'label' => esc_html__('Border Width', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce table.shop_table.cart' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .woocommerce table.shop_table.cart td' => 'border-top-width: {{TOP}}{{UNIT}};',
				],
				'condition'   => [
					'cart_table_border_show' => ['yes'],
				],
			]
		);

		$this->add_control(
			'cart_table_border_color',
			[
				'label' => esc_html__('Border Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce table.shop_table.cart' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce table.shop_table.cart td' => 'border-top-color: {{VALUE}};',
				],
				'condition'   => [
					'cart_table_border_show' => ['yes'],
				],
			]
		);

		$this->add_control(
			'cart_table_border_radius',
			[
				'label' => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce .input-text,
					 {{WRAPPER}} .select2-container--default .select2-selection--single,
					 {{WRAPPER}} .woocommerce select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_cart_style_input',
			[
				'label' => esc_html__('Input', 'bdthemes-element-pack'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_cart'],
				],
			]
		);

		$this->add_control(
			'cart_input_placeholder_color',
			[
				'label'     => esc_html__('Placeholder Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} table.cart .input-text::placeholder' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_input_text_color',
			[
				'label'     => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} table.cart .input-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_input_text_background',
			[
				'label'     => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} table.cart .input-text' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_input_padding',
			[
				'label' => esc_html__('Padding', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} table.cart .input-text, {{WRAPPER}} table.cart td.actions .coupon .input-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; box-sizing: content-box;',
				],
			]
		);


		$this->add_control(
			'cart_input_border_show',
			[
				'label' => esc_html__('Border Style', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
				'label_on' => 'Hide',
				'label_off' => 'Show',
				'return_value' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'cart_input_border',
				'label'       => esc_html__('Border', 'bdthemes-element-pack'),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '
					{{WRAPPER}} table.cart .input-text,
					{{WRAPPER}} table.cart td.actions .coupon .input-text',
				'condition' => [
					'cart_input_border_show' => 'yes',
				],
			]
		);

		$this->add_control(
			'cart_input_border_radius',
			[
				'label' => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', '%'],
				'selectors' => [
					'{{WRAPPER}} table.cart .input-text,
					 {{WRAPPER}} .select2-container--default .select2-selection--single,
					 {{WRAPPER}} .woocommerce select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


		// Cart table button
		$this->start_controls_section(
			'section_style_cart_button',
			[
				'label' => esc_html__('Coupon/Update Button', 'bdthemes-element-pack'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_cart'],
				],
			]
		);

		$this->add_control(
			'cart_button_heading',
			[
				'label' => esc_html__('Button Style', 'bdthemes-element-pack'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs('tabs_cart_button_style');

		$this->start_controls_tab(
			'tab_cart_button_normal',
			[
				'label' => esc_html__('Normal', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'cart_button_text_color',
			[
				'label' => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .woocommerce table tr td button.button, {{WRAPPER}} .woocommerce table.cart td.actions .coupon .wp-element-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_button_background_color',
			[
				'label' => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce table tr td button.button, {{WRAPPER}} .woocommerce table.cart td.actions .coupon .wp-element-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'cart_button_border',
				'label' => esc_html__('Border', 'bdthemes-element-pack'),
				'placeholder' => '1px',
				'default' => '1px',
				'selector' => '{{WRAPPER}} .woocommerce table tr td button.button, {{WRAPPER}} .woocommerce table.cart td.actions .coupon .wp-element-button',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'cart_button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce table tr td button.button, {{WRAPPER}} .woocommerce table.cart td.actions .coupon .wp-element-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'cart_button_box_shadow',
				'selector' => '{{WRAPPER}} .woocommerce table tr td button.button, {{WRAPPER}} .woocommerce table.cart td.actions .coupon .wp-element-button',
			]
		);

		$this->add_control(
			'cart_button_text_padding',
			[
				'label' => esc_html__('Padding', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce table tr td button.button, {{WRAPPER}} .woocommerce table.cart td.actions .coupon .wp-element-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'cart_button_typography',
				'label' => esc_html__('Typography', 'bdthemes-element-pack'),
				//'scheme' => Schemes\Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .woocommerce table tr td button.button',
				'separator' => 'before',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_cart_button_hover',
			[
				'label' => esc_html__('Hover', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'cart_button_hover_color',
			[
				'label' => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce table tr td button.button:hover, {{WRAPPER}} .woocommerce table.cart td.actions .coupon .wp-element-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_button_background_hover_color',
			[
				'label' => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce table tr td button.button:hover, {{WRAPPER}} .woocommerce table.cart td.actions .coupon .wp-element-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_button_hover_border_color',
			[
				'label' => esc_html__('Border Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .woocommerce table tr td button.button:hover, {{WRAPPER}} .woocommerce table.cart td.actions .coupon .wp-element-button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();


		// Cart table button
		$this->start_controls_section(
			'section_style_cart_checkout_button',
			[
				'label' => esc_html__('Checkout Button', 'bdthemes-element-pack'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_cart'],
				],
			]
		);

		$this->add_control(
			'cart_checkout_button_heading',
			[
				'label' => esc_html__('Button Style', 'bdthemes-element-pack'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs('tabs_cart_checkout_button_style');

		$this->start_controls_tab(
			'tab_cart_checkout_button_normal',
			[
				'label' => esc_html__('Normal', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'cart_checkout_button_text_color',
			[
				'label' => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .wc-proceed-to-checkout a.checkout-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_checkout_button_background_color',
			[
				'label' => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wc-proceed-to-checkout a.checkout-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'cart_checkout_button_border',
				'label' => esc_html__('Border', 'bdthemes-element-pack'),
				'placeholder' => '1px',
				'default' => '1px',
				'selector' => '{{WRAPPER}} .wc-proceed-to-checkout a.checkout-button',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'cart_checkout_button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', '%'],
				'selectors' => [
					'{{WRAPPER}} .wc-proceed-to-checkout a.checkout-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'cart_checkout_button_box_shadow',
				'selector' => '{{WRAPPER}} .wpcf7-submit',
			]
		);

		$this->add_control(
			'cart_checkout_button_text_padding',
			[
				'label' => esc_html__('Padding', 'bdthemes-element-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'rem', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .wc-proceed-to-checkout a.checkout-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'cart_checkout_button_typography',
				'label' => esc_html__('Typography', 'bdthemes-element-pack'),
				//'scheme' => Schemes\Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .wc-proceed-to-checkout a.checkout-button',
				'separator' => 'before',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_cart_checkout_button_hover',
			[
				'label' => esc_html__('Hover', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'cart_checkout_button_hover_color',
			[
				'label' => esc_html__('Text Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wc-proceed-to-checkout a.checkout-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_checkout_button_background_hover_color',
			[
				'label' => esc_html__('Background Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wc-proceed-to-checkout a.checkout-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_checkout_button_hover_border_color',
			[
				'label' => esc_html__('Border Color', 'bdthemes-element-pack'),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .wc-proceed-to-checkout a.checkout-button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();


		// Account style
		$this->start_controls_section(
			'section_style_my_account',
			[
				'label' => esc_html__('My Account Style', 'bdthemes-element-pack'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['woocommerce_my_account'],
				],
			]
		);

		$this->add_control(
			'my_account_notice',
			[
				'label' => '<i>My Account does not support any style because my account others menu is dynamic part of My Account widget. We are sorry for it.</i>',
				'type' => Controls_Manager::RAW_HTML,


			]
		);

		$this->end_controls_section();

		/**
		 * Woocommerce Single Product Page Style
		 */
		$this->start_controls_section(
            'section_style_title',
            [
                'label' => __('Product Title', 'bdthemes-element-pack'),
                'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => __('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product .product_title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .woocommerce div.product .product_title',
            ]
        );

        $this->end_controls_section();

		$this->start_controls_section(
            'section_price',
            [
                'label' => __('Product Price', 'bdthemes-element-pack'),
                'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );

		$this->add_control(
            'regular_color',
            [
                'label'     => esc_html__('Regular Color', 'bdthemes-element-pack') . BDTEP_NC,
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product p.price del, {{WRAPPER}} .woocommerce div.product span.price del' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'price_color',
            [
                'label'     => esc_html__('Sale Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product p.price,{{WRAPPER}} .woocommerce div.product span.price' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .woocommerce div.product p.price ins, {{WRAPPER}} .woocommerce div.product span.price ins' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'price_typography',
                'selector' => '{{WRAPPER}} .woocommerce div.product p.price,{{WRAPPER}} .woocommerce div.product span.price',
            ]
        );
		$this->end_controls_section();
        $this->start_controls_section(
            'short_desc_color',
            [
                'label' => __('Product Description', 'bdthemes-element-pack'),
                'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );

        $this->add_control(
            'sd_color',
            [
                'label'     => esc_html__('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-product-details__short-description' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'sd_color_typo',
                'selector' => '{{WRAPPER}} .woocommerce-product-details__short-description',
            ]
        );

        $this->end_controls_section();

		$this->start_controls_section(
            'section_style_add_to_cart',
            [
                'label' => __('Add To Cart', 'bdthemes-element-pack'),
                'tab' => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );

        $this->start_controls_tabs('tabs_add_to_cart_style');

        $this->start_controls_tab(
            'tab_add_to_cart_normal',
            [
                'label' => __('Normal', 'bdthemes-element-pack'),
            ]
        );

        $this->add_control(
            'add_to_cart_text_color',
            [
                'label' => __('Color', 'bdthemes-element-pack'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product form.cart .button' => 'color: {{VALUE}}; cursor:pointer;'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'add_to_cart_background',
                'label' => __('Background', 'bdthemes-element-pack'),
                'types' => [
                    'classic', 'gradient'
                ],
                'exclude' => ['image'],
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'default' => '#1E87F0',
                    ],
                ],
                'selector' => '{{WRAPPER}} .woocommerce div.product form.cart .button',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'           => 'add_to_cart_border',
                'label'          => __('Border', 'bdthemes-element-pack'),
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width'  => [
                        'default' => [
                            'top'      => '0',
                            'right'    => '0',
                            'bottom'   => '0',
                            'left'     => '0',
                            'isLinked' => false,
                        ],
                    ],
                    // 'color'  => [
                    //     'default' => '#8D99AE',
                    // ],
                ],
                'selector' => '{{WRAPPER}} .woocommerce div.product form.cart .button',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'add_to_cart_border_radius',
            [
                'label' => __('Border Radius', 'bdthemes-element-pack'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', '%'],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product form.cart .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'add_to_cart_padding',
            [
                'label' => __('Padding', 'bdthemes-element-pack'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product form.cart .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
		$this->add_responsive_control(
			'qty_fields_gap',
			[
				'label' => __('Spacing', 'bdthemes-element-pack') . BDTEP_NC,
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .woocommerce div.product form.cart .button' => 'margin-left: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'add_to_cart_typography',
                'selector' => '{{WRAPPER}} .woocommerce div.product form.cart .button',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'add_to_cart_box_shadow',
                'selector' => '{{WRAPPER}} .woocommerce div.product form.cart .button',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_add_to_cart_hover',
            [
                'label' => __('Hover', 'bdthemes-element-pack'),
            ]
        );

        $this->add_control(
            'add_to_cart_hover_color',
            [
                'label' => __('Color', 'bdthemes-element-pack'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product form.cart .button:hover, {{WRAPPER}} .woocommerce div.product form.cart .button:focus' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'add_to_cart_hover_border_color',
            [
                'label' => __('Border Color', 'bdthemes-element-pack'),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'add_to_cart_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product form.cart .button:hover, {{WRAPPER}} .woocommerce div.product form.cart .button:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'add_to_cart_hover_background',
                'label' => __('Background', 'bdthemes-element-pack'),
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .woocommerce div.product form.cart .button:hover, {{WRAPPER}} .elementor-button:focus',
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

        $this->start_controls_section(
            'qty_style',
            [
                'label'     => __('Quantity Field', 'bdthemes-element-pack'),
                'tab'       => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );

        $this->add_responsive_control(
            'qty_fields_width',
            [
                'label' => esc_html__('Width', 'bdthemes-element-pack'),
                'type'  => Controls_Manager::SLIDER,
                'size_units' => ['px', 'rem', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                    'rem' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce .quantity .qty'  => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->add_responsive_control(
			'qty_fields_height',
			[
				'label' => esc_html__('Height', 'bdthemes-element-pack'),
				'type'  => Controls_Manager::SLIDER,
				'size_units' => ['px', 'rem', 'em', '%'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 20,
					],
					'rem' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce .quantity .qty'  => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
            'qty_fields_color',
            [
                'label'     => __('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity input[type=number]' => 'color: {{VALUE}} ',
                    '{{WRAPPER}} .quantity input[type=number]::placeholder' => 'color: {{VALUE}} ',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'qty_fields_background',
                'exclude'  => ['image'],
                'selector' => '{{WRAPPER}} .quantity input[type=number]',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'           => 'qty_fields_border',
                'label'          => __(
                    'Border',
                    'bdthemes-element-pack'
                ),
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width'  => [
                        'default' => [
                            'top'      => '1',
                            'right'    => '1',
                            'bottom'   => '1',
                            'left'     => '1',
                            'isLinked' => false,
                        ],
                    ],
                    'color'  => [
                        'default' => '#a4afb7',
                    ],
                ],
                'selector' => '{{WRAPPER}} .quantity input[type=number]',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'qty_fields_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', '%'],
                'default'    => [
                    'top'      => '3',
                    'right'    => '3',
                    'bottom'   => '3',
                    'left'     => '3',
                    'isLinked' => false
                ],
                'selectors'  => [
                    '{{WRAPPER}} .quantity input[type=number]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'qty_fields_padding',
            [
                'label'   => __('Padding', 'bdthemes-element-pack'),
                'type'    => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .quantity input[type=number]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} ;',
                ],
            ]
        );

		// $this->add_responsive_control(
		// 	'qty_fields_spacing',
		// 	[
		// 		'label' => __('Spacing', 'bdthemes-element-pack'),
		// 		'type' => Controls_Manager::SLIDER,
		// 		'size_units' => ['px', 'rem', 'em', '%'],
		// 		'selectors' => [
		// 			'{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce .quantity' => 'margin: 0 {{SIZE}}{{UNIT}} 0 0;',
		// 		],
		// 	]
		// );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'qty_fields_typography',
                'selector' => '{{WRAPPER}} .quantity input[type=number]',
            ]
        );


        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'qty_fields_shadow',
                'selector' => '{{WRAPPER}} .quantity input[type=number]'
            ]
        );
        $this->end_controls_section();

		/**
         * Quantity Plus Minus
         */ 
        $this->start_controls_section(
            'section_quantity_plus_minus',
            [
                'label' => __('Quantity Plus Minus', 'bdthemes-element-pack') . BDTEP_NC,
                'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );
        $this->start_controls_tabs('quantity_plus_minus_tabs');
        $this->start_controls_tab(
            'quantity_plus_minus_normal_tab',
            [
                'label' => __('Normal', 'bdthemes-element-pack'),
            ]
        );
        $this->add_control(
            'quantity_plus_minus_color',
            [
                'label'     => __('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity button' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'quantity_plus_minus_background',
                'types'     => ['classic', 'gradient'],
                'selector'  => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity button',
                'exclude'   => ['image'],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'           => 'quantity_plus_minus_border',
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity button',
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'quantity_plus_minus_border_radius',
            [
                'label'      => __('Border Radius', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'quantity_plus_minus_padding',
            [
                'label'      => __('Padding', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'quantity_plus_minus_box_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity button',
            ]
        );
		// $this->add_responsive_control(
		// 	'quantity_plus_minus_spacing',
		// 	[
		// 		'label' => __('Space Between', 'bdthemes-element-pack'),
		// 		'type' => Controls_Manager::SLIDER,
		// 		'size_units' => ['px', 'rem', 'em', '%'],
		// 		'selectors' => [
		// 			'{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce .quantity .qty' => 'margin: 0 {{SIZE}}{{UNIT}};',
		// 		],
		// 	]
		// );
        $this->add_responsive_control(
            'quantity_plus_minus_icon_size',
            [
                'label'      => __('Icon Size', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', 'rem', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity button' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
		$this->add_responsive_control(
			'quantity_plus_minus_gap',
			[
				'label' => __('Gap', 'bdthemes-element-pack'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);
        $this->end_controls_tab();
        $this->start_controls_tab(
            'quantity_plus_minus_hover_tab',
            [
                'label' => __('Hover', 'bdthemes-element-pack'),
            ]
        );
        $this->add_control(
            'quantity_plus_minus_hover_color',
            [
                'label'     => __('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'quantity_plus_minus_hover_background',
                'types'     => ['classic', 'gradient'],
                'selector'  => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity button:hover',
                'exclude'   => ['image'],
            ]
        );
        $this->add_control(
            'quantity_plus_minus_hover_border_color',
            [
                'label'     => __('Border Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity button:hover' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'quantity_plus_minus_border_border!' => '',
                ],
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'quantity_plus_minus_hover_box_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .quantity button:hover',
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

		/**
         * Variation Swatches
         */
        $this->start_controls_section(
            'section_variation_swatches',
            [
                'label' => __('Variation Swatches', 'bdthemes-element-pack'),
                'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );
        $this->add_control(
            'variation_label_color',
            [
                'label'     => __('Label Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations label' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'variation_label_spacing',
            [
                'label'      => __('Right Spacing', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::SLIDER,
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements form.cart  table.variations td' => 'padding-left: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'variation_label_typography',
                'label'    => __('Label Typography', 'bdthemes-element-pack'),
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations label',
            ]
        );
        $this->start_controls_tabs('variation_tabs');
        $this->start_controls_tab(
            'variation_tab_normal',
            [
                'label' => __('Normal', 'bdthemes-element-pack'),
            ]
        );
        $this->add_control(
            'variation_color',
            [
                'label'     => __('Text Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select' => 'color: {{VALUE}};',
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'variation_background',
                'types'     => ['classic', 'gradient'],
                'selector'  => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select, {{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item',
                'exclude'   => ['image'],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'           => 'variation_border',
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select, {{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item',
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'variation_border_radius',
            [
                'label'      => __('Border Radius', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'variation_padding',
            [
                'label'      => __('Padding', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'variation_gap',
            [
                'label'      => __('Gap', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', 'rem', '%'],
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 50,
                        'step' => 1,
                    ],
					'rem' => [
						'min' => 0,
						'max' => 20,
						'step' => 0.1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
                ],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'variation_box_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select, {{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item',
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'variation_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select, {{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item',
            ]
        );

        $this->add_control(
            'variation_reset_color',
            [
                'label'     => __('Reset Text Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .reset_variations' => 'color: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'variation_reset_gap',
            [
                'label'      => __('Left Spacing', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', 'rem', '%'],
                'range'      => [
                    'px' => [
                        'min'  => 40,
                        'max'  => 100,
                        'step' => 1,
                    ],
					'rem' => [
						'min' => 0,
						'max' => 20,
						'step' => 0.1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
                ],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .reset_variations' => 'right: -{{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'variation_reset_typography',
                'label'    => __('Reset Typography', 'bdthemes-element-pack'),
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .reset_variations',
            ]
        );
        $this->end_controls_tab();
        $this->start_controls_tab(
            'variation_tab_hover',
            [
                'label' => __('Hover', 'bdthemes-element-pack'),
            ]
        );
        $this->add_control(
            'variation_color_hover',
            [
                'label'     => __('Text Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'variation_background_hover',
                'types'     => ['classic', 'gradient'],
                'selector'  => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select:hover, {{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item:hover',
                'exclude'   => ['image'],
            ]
        );
        $this->add_control(
            'variation_border_color_hover',
            [
                'label'     => __('Border Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select:hover' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item:hover' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'variation_border_border!' => '',
                ],
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'variation_box_shadow_hover',
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select:hover, {{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item:hover',
            ]
        );
        $this->end_controls_tab();
        $this->start_controls_tab(
            'variation_tab_active',
            [
                'label' => __('Active', 'bdthemes-element-pack'),
            ]
        );
        $this->add_control(
            'variation_color_active',
            [
                'label'     => __('Text Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item.selected' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'variation_background_active',
                'types'     => ['classic', 'gradient'],
                'selector'  => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select:focus, {{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item.selected',
                'exclude'   => ['image'],
            ]
        );
        $this->add_control(
            'variation_border_color_active',
            [
                'label'     => __('Border Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item.selected' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'variation_border_border!' => '',
                ],
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'variation_box_shadow_active',
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .variations select:focus, {{WRAPPER}}.elementor-widget-bdt-wc-elements .variations .ep-variation-swatches__item.selected',
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

		/**
         * Variation Swatches product price
         */
        $this->start_controls_section(
            'price_variation',
            [
                'label' => __('Variation Price', 'bdthemes-element-pack') . BDTEP_NC,
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );
        $this->add_responsive_control(
            'price_variation_margin',
            [
                'label'      => __('Margin', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-variation-price' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->start_controls_tabs('tabs_price_variation_style');
        $this->start_controls_tab(
            'price_variation_regular',
            [
                'label' => __('Regular Price', 'bdthemes-element-pack'),
            ]
        );
        $this->add_control(
            'price_variation_color',
            [
                'label'     => esc_html__('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-variation-price .price, {{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-variation-price del' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'price_variation_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-variation-price .price, {{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-variation-price del'
            ]
        );
        $this->end_controls_tab();
        $this->start_controls_tab(
            'price_variation_sale',
            [
                'label' => __('Sale price ', 'bdthemes-element-pack'),
            ]
        );
        $this->add_control(
            'price_variation_color_sale',
            [
                'label'     => esc_html__('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-variation-price ins' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'price_variation_typography_sale',
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-variation-price ins',
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

		$this->start_controls_section(
            'tabs_nav_item_style_section',
            [
                'label' => __('Tabs Item', 'bdthemes-element-pack'),
                'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );


        $this->add_responsive_control(
            'tabs_nav_item_padding',
            [
                'label'      => esc_html__(
                    'Padding',
                    'bdthemes-element-pack'
                ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tabs_nav_item_margin',
            [
                'label'      => esc_html__('Margin', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'tabs_nav_item_typography',
                'selector' => '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'tabs_nav_item_border',
                'selector'    => '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li a',
            ]
        );


        $this->add_responsive_control(
            'tabs_nav_item_border_radius',
            [
                'label'      => __('Border Radius', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_tabs_nav');

        $this->start_controls_tab(
            'tabs_nav_item_normal',
            [
                'label' => __('Normal', 'bdthemes-element-pack'),
            ]
        );

        $this->add_control(
            'tabs_nav_item_color',
            [
                'label'     => esc_html__('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li a'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'tabs_nav_item_bg',
                'types'     => ['classic', 'gradient'],
                'selector'  => '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li a',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tabs_nav_item_hover',
            [
                'label' => __('Hover', 'bdthemes-element-pack'),
            ]
        );

        $this->add_control(
            'tabs_nav_item_color_hover',
            [
                'label'     => esc_html__('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li a:hover'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'tabs_nav_item_bg_hover',
                'types'     => ['classic', 'gradient'],
                'selector'  => '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li a:hover',
            ]
        );

        $this->add_control(
            'tabs_nav_item_border_color_hover',
            [
                'label'     => esc_html__('Border Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'condition' => [
                    'tabs_nav_item_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li a:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'tabs_nav_active',
            [
                'label' => __('Active', 'bdthemes-element-pack'),
            ]
        );

        $this->add_control(
            'tabs_nav_color_active',
            [
                'label'     => esc_html__('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li.active a'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'tabs_nav_bg_active',
                'types'     => ['classic', 'gradient'],
                'selector'  => '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li.active a',
            ]
        );

        $this->add_control(
            'tabs_nav_item_border_color_active',
            [
                'label'     => esc_html__('Border Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'condition' => [
                    'tabs_nav_item_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce div.product .woocommerce-tabs ul.tabs li.active a' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

        $this->start_controls_section(
            'tabs_content',
            [
                'label' => __('Tabs Content', 'bdthemes-element-pack'),
                'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );

        $this->add_control(
            'tabs_content_color',
            [
                'label'     => __('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-tabs .wc-tab' => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'tabs_content_bg',
                'types'     => ['classic', 'gradient'],
                'selector'  => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-tabs .wc-tab',
            ]
        );

        $this->add_responsive_control(
            'tabs_content_padding',
            [
                'label'      => esc_html__('Padding', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-tabs .wc-tab' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tabs_content_border_radius',
            [
                'label'      => __('Border Radius', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-tabs .wc-tab' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'tabs_content_border',
                'selector'    => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .woocommerce-tabs .wc-tab',
            ]
        );

        $this->end_controls_section();

		$this->start_controls_section(
            'section_style_product_related',
            [
                'label' => __('Related Product', 'bdthemes-element-pack'),
                'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );
        $this->start_controls_tabs(
            'tabs_product_related'
        );
        $this->start_controls_tab(
            'tab_product_related_heading',
            [
                'label' => __('Heading', 'bdthemes-element-pack'),
            ]
        );
        $this->add_control(
            'related_heading_color',
            [
                'label'     => __( 'Color', 'bdthemes-element-pack' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .related.products > h2' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'related_heading_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-bdt-wc-elements .related.products > h2',
            ]
        );

        $this->add_responsive_control(
            'related_heading_margin',
            [
                'label'      => __('Margin', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'rem', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements .related.products > h2' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->start_controls_tab(
            'tab_product_related_title',
            [
                'label' => __('Title', 'bdthemes-element-pack'),
            ]
        );

        $this->add_control(
            'related_title_color',
            [
                'label'     => __('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product .woocommerce-loop-product__title' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'related_title_typography',
                'selector' => '{{WRAPPER}} .woocommerce ul.products li.product .woocommerce-loop-product__title',
            ]
        );
        $this->end_controls_tab();
        $this->start_controls_tab(
            'tab_product_related_price',
            [
                'label' => __('Price', 'bdthemes-element-pack'),
            ]
        );
        $this->add_control(
            'related_price_color',
            [
                'label'     => esc_html__('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product .price' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'related_price_typography',
                'selector' => '{{WRAPPER}} .woocommerce ul.products li.product .price',
            ]
        );
        $this->end_controls_tab();
        $this->start_controls_tab(
            'tab_product_related_cart',
            [
                'label' => __('Cart', 'bdthemes-element-pack'),
            ]
        );
        $this->add_control(
            'related_cart_color',
            [
                'label'     => esc_html__('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product a.button' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'related_cart_background_color',
            [
                'label'     => esc_html__('Background', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product a.button' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'related_cart_padding',
            [
                'label'                 => __( 'Padding', 'bdthemes-element-pack' ),
                'type'                  => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'rem', 'em', '%'],
                'selectors'             => [
                    '{{WRAPPER}} .woocommerce ul.products li.product a.button'    => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'related_cart_typography',
                'label'     => __( 'Typography', 'bdthemes-element-pack' ),
                'selector'  => '{{WRAPPER}} .woocommerce ul.products li.product a.button',
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

		$this->start_controls_section(
            'section_style_badge',
            [
                'label' => __('Sale Badge', 'bdthemes-element-pack'),
                'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'element' => ['product_page'],
				],
            ]
        );
        $this->add_control(
            'sale_badge_color',
            [
                'label'     => __('Color', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bdt-wc-elements span.onsale' => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'      => 'sale_badge_background',
                'label'     => __('Background', 'bdthemes-element-pack'),
                'types'     => ['classic', 'gradient'],
                'selector'  => '{{WRAPPER}}.elementor-widget-bdt-wc-elements span.onsale',
            ]
        );
        $this->end_controls_section();

	}

	private function get_shortcode() {
		$settings = $this->get_settings_for_display();

		switch ($settings['element']) {
			case '':
				return '';
				break;

			case 'product_page':

				if (!empty($settings['product_id'])) {
					$product_data = get_post($settings['product_id']);
					$product = !empty($product_data) && in_array($product_data->post_type, array('product', 'product_variation')) ? wc_setup_product_data($product_data) : false;
				}

				if (empty($product) && current_user_can('manage_options')) {
					return esc_html__('Please set a valid product', 'bdthemes-element-pack');
				}

				$this->add_render_attribute('shortcode', 'id', $settings['product_id']);
				break;

			case 'woocommerce_cart':
			case 'woocommerce_checkout':
			case 'woocommerce_order_tracking':
				break;
		}

		$shortcode = sprintf('[%s %s]', $settings['element'], $this->get_render_attribute_string('shortcode'));

		return $shortcode;
	}

	protected function render() {
		$shortcode = $this->get_shortcode();

		if (empty($shortcode)) {
			return;
		}

		Module::instance()->add_products_post_class_filter();

		$html = do_shortcode($shortcode);

		if ('woocommerce_checkout' === $this->get_settings('element') && '<div class="woocommerce"></div>' === $html) {
			$html = '<div class="woocommerce">' . esc_html__('Your cart is currently empty.', 'bdthemes-element-pack') . '</div>';
		}

		echo $html;

		Module::instance()->remove_products_post_class_filter();
	}

	public function render_plain_content() {
		echo wp_kses_post($this->get_shortcode());
	}
}
