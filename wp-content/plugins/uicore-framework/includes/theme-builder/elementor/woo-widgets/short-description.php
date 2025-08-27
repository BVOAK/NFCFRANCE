<?php
namespace UiCore\ThemeBuilder\Widgets;

use UiCore\Elementor\TB_Widget_Base;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined('ABSPATH') || exit();

/**
 * Product Short Description Widget
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */

class Product_Short_Description extends TB_Widget_Base {

	public function get_name() {
		return 'uicore-woo-product-short-description';
	}
	public function get_title() {
		return esc_html__( 'Short Description', 'uicore-framework' );
	}
	public function get_icon() {
		return 'eicon-product-description ui-e-widget';
	}
    public function get_categories() {
		return ['uicore-woo', 'uicore-theme-builder'];
	}
	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'text', 'description', 'product' ];
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
			'section_product_description_style',
			[
				'label' => esc_html__( 'Short Description', 'uicore-framework' ),
			]
		);

        $this->register_post_list('product');
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
					'justify' => [
						'title' => esc_html__( 'Justified', 'uicore-framework' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Text Color', 'uicore-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-product-details__short-description' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .woocommerce-product-details__short-description p' => 'margin-bottom: 0px;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'text_typography',
				'selector' => '{{WRAPPER}} .woocommerce-product-details__short-description',
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

        // In edit mode, the short description template can't get the post because `global $post` is not available,
        // so we use the woo filter to display update the content.
        if($this->is_edit_mode()) {
           add_filter('woocommerce_short_description', function() use($product) {
				return $product->get_short_description();
            });

            $this->render_woo_wrapper(false, 'single-product');
        }

        \wc_get_template( 'single-product/short-description.php' );

        if($this->is_edit_mode()) {
            $this->render_woo_wrapper(true);
        }
	}

	public function render_plain_content() {}
}
\Elementor\Plugin::instance()->widgets_manager->register(new Product_Short_Description());