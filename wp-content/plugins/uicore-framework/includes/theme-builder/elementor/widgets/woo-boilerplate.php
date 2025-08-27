<?php
namespace UiCore\ThemeBuilder\Widgets;

use Uicore\Elementor\TB_Widget_Base;
use Elementor\Controls_Manager;

defined('ABSPATH') || exit();

/**
 * The Title widget.
 *
 * @since 4.0.0
 */
class WooBoilerplate extends TB_Widget_Base {

    public function get_name() {
		return 'uicore-woo-boilerplate';
	}
	public function get_title() {
		return esc_html__( 'Boilerplate', 'uicore-framework' );
	}
	public function get_icon() {
		return 'eicon-post-info ui-e-widget';
	}
	public function get_categories() {
		return ['uicore-woo', 'uicore-theme-builder'];
	}
	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'cart', 'product', 'button', 'add to cart' ];
	}
    public function get_styles() {

        $styles = [
            'widget-style' => [
                'deps' => [],
                'custom_conditions' => [
                    'controls_condition' => [
                        'control_key' => 'control_value'
                    ],
                    'direct_condition' => $this->is_edit_mode()
                ]
            ],
        ];

        return [];
	}
    public function get_scripts()
    {
        return [];
    }

	protected function register_controls() {
		$this->start_controls_section(
			'section_product_content',
			[
				'label' => esc_html__( 'Content', 'uicore-framework' ),
			]
		);
            $this->register_post_list('product');

		$this->end_controls_section();

	}

	protected function render() {
        $product = $this->get_product_data();
        if (!$product) return;
        ?>
            <h1>Woo Widget</h1>
        <?php
	}

	protected function content_template() {}
}
\Elementor\Plugin::instance()->widgets_manager->register(new WooBoilerplate());
