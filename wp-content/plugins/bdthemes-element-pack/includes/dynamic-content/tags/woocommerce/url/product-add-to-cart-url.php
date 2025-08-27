<?php
use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Add_To_Cart_URL extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'element-pack-product-add-to-cart-url';
	}

	public function get_title(): string {
		return esc_html__('Product Add to Cart URL', 'bdthemes-element-pack');
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
			'ep_quantity',
			[
				'label' => esc_html__('Quantity', 'bdthemes-element-pack'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 1,
				'description' => esc_html__('Number of items to add to cart', 'bdthemes-element-pack'),
			]
		);

		$this->fallback_control();
	}

	protected function register_advanced_section(): void {}

	public function get_value(array $options = []) {
		$settings = $this->get_settings();
		$product_id = $this->get_product_id();

		if ( ! $product_id ) {
			return '';
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return '';
		}

		$quantity = (int)($settings['ep_quantity'] ?? 1);

		// For variable or grouped products, redirect to single product page
		if ($product->is_type(['variable', 'grouped'])) {
			return get_permalink($product_id);
		}

		// For simple products, direct add to cart
		return add_query_arg(
			[
				'add-to-cart' => $product_id,
				'quantity'    => $quantity,
			],
			wc_get_cart_url()
		);
	}
}
