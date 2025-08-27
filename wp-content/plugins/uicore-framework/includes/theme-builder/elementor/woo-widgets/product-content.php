<?php
namespace ElementorPro\Modules\ThemeBuilder\Widgets;

use Uicore\Elementor\TB_Widget_Base;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Typography;

defined('ABSPATH') || exit();

/**
 * Product Content Widget
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */

class Product_Content extends TB_Widget_Base {

	public function get_name() {
		// `theme` prefix is to avoid conflicts with a dynamic-tag with same name.
		return 'uicore-woo-product-content';
	}
	public function get_title() {
		return esc_html__( 'Product Content', 'uicore-framework' );
	}
	public function get_icon() {
		return 'eicon-post-content ui-e-widget';
	}
	public function get_categories() {
		return ['uicore-woo', 'uicore-theme-builder'];
	}
	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'content', 'product' ];
	}
    public function get_styles() {
		return [];
	}
    public function get_scripts() {
        return [];
    }

	protected function register_controls() {
        if( $this->no_woo_fallback(true) ){
            return;
        }

		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Product Content', 'uicore-framework' ),
			]
		);

        $this->register_post_list('product');
		$this->add_responsive_control(
			'align',
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
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Text Color', 'uicore-framework' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}};',
				],
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
        if( $this->no_woo_fallback() ){
            return;
        }

        $product = $this->get_product_data();
        if (!$product) return;

		static $did_posts = [];

		if ( post_password_required( $product->get_ID() ) ) {
			echo get_the_password_form( $product->get_ID() ); // WPCS: XSS ok.
			return;
		}

		// Avoid recursion
		if ( isset( $did_posts[ $product->get_ID() ] ) ) {
			return;
		}
		$did_posts[ $product->get_ID() ] = true;

        // `the_content()` in editor screen context returns the page template,
        // instead of the product description.
        if ($this->is_edit_mode()) {
            echo $product->get_description();
            return;
        }

		\the_content();
	}

	public function render_plain_content() {}
}
\Elementor\Plugin::instance()->widgets_manager->register(new Product_Content());