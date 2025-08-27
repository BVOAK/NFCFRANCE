<?php
use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Term_Archive_URL extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'element-pack-product-term-archive-url';
	}

	public function get_title(): string {
		return esc_html__('Terms Archive URL', 'bdthemes-element-pack');
	}

	public function get_group(): array {
		return ['element-pack-woocommerce'];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		];
	}

	protected function register_controls(): void {
		$this->add_control(
			'ep_term_id',
			[
				'label' => esc_html__('Term', 'bdthemes-element-pack'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_product_terms(),
				'description' => esc_html__('Select the term to link to', 'bdthemes-element-pack'),
			]
		);
	}

	private function get_product_terms(): array {
		$terms = get_terms([
			'taxonomy' => 'product_cat',
			'hide_empty' => true,
		]);

		if (is_wp_error($terms)) {
			return [];
		}

		$options = [];
		foreach ($terms as $term) {
			$options[$term->term_id] = $term->name;
		}

		return $options;
	}

	public function get_value(array $options = []) {
		$settings = $this->get_settings();
		$term_id = $settings['ep_term_id'] ?? 0;

		if ($term_id) {
			$term = get_term($term_id);
			if ($term && !is_wp_error($term)) {
				return get_term_link($term);
			}
		}

		return wc_get_page_permalink('shop');
	}
} 