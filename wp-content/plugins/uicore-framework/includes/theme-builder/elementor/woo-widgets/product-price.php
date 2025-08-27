<?php
namespace UiCore\ThemeBuilder\Widgets;

use UiCore\Elementor\TB_Widget_Base;
use Uicore\Helper;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

defined('ABSPATH') || exit();

/**
 * Product Price Widget - Based on Elementor Pro
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */

class Product_Price extends TB_Widget_Base {

	public function get_name() {
		return 'uicore-woo-product-price';
	}
	public function get_title() {
		return esc_html__( 'Product Price', 'uicore-framework' );
	}
    public function get_icon() {
		return 'eicon-product-price ui-e-widget';
	}
    public function get_categories() {
		return ['uicore-woo', 'uicore-theme-builder'];
	}
	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'price', 'product', 'sale' ];
	}
    public function get_styles() {
        return ['single-product'];
	}
    public function get_scripts() {
        return [];
    }

	protected function register_controls() {
        if( $this->no_woo_fallback(true) ){
            return;
        }

		$this->start_controls_section(
			'section_price',
			[
				'label' => esc_html__( 'Price', 'uicore-framework' ),
			]
		);

        $this->register_post_list('product');
        $this->add_control(
            'warning',
            [
                'type' => Controls_Manager::NOTICE,
                'notice_type' => 'warning',
                'dismissible' => true,
                'heading' => esc_html__( 'Style problems?', 'uicore-framework' ),
                'content' => esc_html__( "Sale price styles will only work if the product have a sale price. Try selecting a different product for the preview if your changes don't seen to be applied.", 'uicore-framework' ),
            ]
        );

		$this->add_responsive_control(
			'text_align',
			[
				'label' => esc_html__( 'Alignment', 'uicore-framework' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'uicore-framework' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'uicore-framework' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'uicore-framework' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => esc_html__( 'Color', 'uicore-framework' ),
				'type' => Controls_Manager::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .summary .price, {{WRAPPER}} .summary .price bdi, {{WRAPPER}} .summary .price del bdi' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .summary .price del' => 'text-decoration-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .summary .price bdi, {{WRAPPER}} .summary .price del bdi',
			]
		);

		$this->add_control(
			'sale_heading',
			[
				'label' => esc_html__( 'Sale Price', 'uicore-framework' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'sale_price_color',
			[
				'label' => esc_html__( 'Color', 'uicore-framework' ),
				'type' => Controls_Manager::COLOR,
                'selectors' => [
					'{{WRAPPER}} .summary .price ins bdi' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .summary .price ins' => 'text-decoration-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'sale_price_typography',
				'selector' => '{{WRAPPER}} .summary .price bdi, {{WRAPPER}} .summary .price ins bdi',
			]
		);

		$this->add_control(
			'price_block',
			[
				'label' => esc_html__( 'Stacked', 'uicore-framework' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'uicore-product-price-block',
				'prefix_class' => '',
			]
		);

		$this->add_responsive_control(
			'sale_price_spacing',
			[
				'label' => esc_html__( 'Spacing', 'uicore-framework' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'max' => 100,
					],
					'em' => [
						'max' => 10,
					],
					'rem' => [
						'max' => 10,
					],
				],
				'selectors' => [
					'body:not(.rtl) {{WRAPPER}}:not(.uicore-product-price-block) del' => 'margin-right: {{SIZE}}{{UNIT}}',
					'body.rtl {{WRAPPER}}:not(.uicore-product-price-block) del' => 'margin-left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.uicore-product-price-block del' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
        if ($this->no_woo_fallback()) {
            return;
        }

        $product = $this->get_product_data();
        if (!$product) return;

        if($this->is_edit_mode()) {
            $this->render_woo_wrapper(false, 'single-product');
        }

        // Price theme styles uses `summary` as target, so it don't applies to loop template prices
        ?>
            <div class="summary">
                <?php \wc_get_template( '/single-product/price.php' ); ?>
            </div>
        <?php

        if($this->is_edit_mode()) {
            $this->render_woo_wrapper(true);
        }
	}

	public function render_plain_content() {}
}
\Elementor\Plugin::instance()->widgets_manager->register(new Product_Price());