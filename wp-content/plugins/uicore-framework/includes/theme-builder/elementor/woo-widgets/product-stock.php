<?php
namespace UiCore\ThemeBuilder\Widgets;

use UiCore\Elementor\TB_Widget_Base;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined('ABSPATH') || exit();

/**
 * Product Stock Widget - Based on Elementor Pro
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */

class Product_Stock extends TB_Widget_Base {

	public function get_name() {
		return 'uicore-woo-product-stock';
	}
	public function get_title() {
		return esc_html__( 'Product Stock', 'uicore-framework' );
	}
	public function get_icon() {
		return 'eicon-product-stock ui-e-widget';
	}
    public function get_categories() {
		return ['uicore-woo', 'uicore-theme-builder'];
	}
	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'stock', 'quantity', 'product' ];
	}
    public function get_styles() {
        return [];
	}
    public function get_scripts() {
        return [];
    }

	protected function register_controls() {
        if ( $this->no_woo_fallback(true) ) {
            return;
        }

		$this->start_controls_section(
			'section_product_stock',
            [
                'label' => esc_html__( 'Stock', 'uicore-framework' ),
            ]
		);

        $this->register_post_list('product');
		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Text Color', 'uicore-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .stock' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'text_typography',
				'selector' => '{{WRAPPER}} .stock',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
        if ( $this->no_woo_fallback() ) {
            return;
        }

        $product = $this->get_product_data();
        if (!$product) return;

		echo \wc_get_stock_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function render_plain_content() {}
}
\Elementor\Plugin::instance()->widgets_manager->register(new Product_Stock());