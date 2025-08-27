<?php
use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Checkout_URL extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'element-pack-product-checkout-url';
	}

	public function get_title(): string {
		return esc_html__('Product Checkout URL', 'bdthemes-element-pack');
	}

	public function get_group(): array {
		return ['element-pack-woocommerce'];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		];
	}

	public function is_settings_required(): bool {
		return true;
	}

	protected function register_controls(): void {
		$this->common_product_controls();

		$this->add_control(
			'ep_checkout_type',
			[
				'label' => esc_html__('Checkout Type', 'bdthemes-element-pack'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'direct',
				'options' => [
					'direct' => esc_html__('Direct to Checkout', 'bdthemes-element-pack'),
					'add_to_cart' => esc_html__('Add to Cart & Checkout', 'bdthemes-element-pack'),
				],
				'description' => esc_html__('Choose whether to go directly to checkout or add product to cart first', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'ep_quantity',
			[
				'label' => esc_html__('Quantity', 'bdthemes-element-pack'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 1,
				'description' => esc_html__('Number of items to add to cart', 'bdthemes-element-pack'),
				'condition' => [
					'ep_checkout_type' => 'add_to_cart',
				],
			]
		);

		$this->fallback_control();
	}

	public function get_value(array $options = []) {
		$settings = $this->get_settings();
		$checkout_type = $settings['ep_checkout_type'] ?? 'direct';

		// If direct checkout, just return checkout URL
		if ($checkout_type === 'direct') {
			return wc_get_checkout_url();
		}

		// For add to cart & checkout, we need a product
		$product_id = $this->get_product_id();
		if ( ! $product_id ) {
			return wc_get_checkout_url();
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return wc_get_checkout_url();
		}

		$quantity = (int)($settings['ep_quantity'] ?? 1);

		// For variable or grouped products, redirect to single product page
		if ($product->is_type(['variable', 'grouped'])) {
			return get_permalink($product_id);
		}

		// For simple products, add to cart and redirect to checkout
		return add_query_arg(
			[
				'add-to-cart' => $product_id,
				'quantity'    => $quantity,
				'checkout'    => '1',
			],
			wc_get_cart_url()
		);
	}
} 