<?php
use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Back_To_Shop_URL extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'element-pack-product-back-to-shop-url';
	}

	public function get_title(): string {
		return esc_html__('Back to Shop URL', 'bdthemes-element-pack');
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
		return false;
	}

	protected function register_controls(): void {
		$this->add_control(
			'ep_shop_page',
			[
				'label' => esc_html__('Shop Page', 'bdthemes-element-pack'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => esc_html__('Default Shop Page', 'bdthemes-element-pack'),
					'custom' => esc_html__('Custom Page', 'bdthemes-element-pack'),
				],
				'description' => esc_html__('Choose which page to link to', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
			'ep_custom_shop_url',
			[
				'label' => esc_html__('Custom Shop URL', 'bdthemes-element-pack'),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__('https://your-shop-url.com', 'bdthemes-element-pack'),
				'condition' => [
					'ep_shop_page' => 'custom',
				],
				'description' => esc_html__('Enter a custom URL to return to', 'bdthemes-element-pack'),
			]
		);

		$this->fallback_control();
	}

	public function get_value(array $options = []) {
		$settings = $this->get_settings();
		$shop_page = $settings['ep_shop_page'] ?? 'default';

		if ($shop_page === 'custom' && !empty($settings['ep_custom_shop_url']['url'])) {
			return $settings['ep_custom_shop_url']['url'];
		}

		// Get the default shop page URL
		$shop_url = wc_get_page_permalink('shop');
		
		// If no shop page is set, fallback to home URL
		if (!$shop_url) {
			$shop_url = home_url();
		}

		return $shop_url;
	}
} 