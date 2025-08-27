<?php
use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Review_URL extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'element-pack-product-review-url';
	}

	public function get_title(): string {
		return esc_html__('Product Review URL', 'bdthemes-element-pack');
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
			'ep_review_id',
			[
				'label' => esc_html__('Review ID', 'bdthemes-element-pack'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
                'ai' => [
                    'active' => false,
                ],
				'description' => esc_html__('Leave empty to link to the review section. Enter a specific review ID to link to that review.', 'bdthemes-element-pack'),
			]
		);

		$this->fallback_control();
	}

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

		// Get the product URL
		$url = get_permalink($product_id);

		// If a specific review ID is provided, link to that review
		if ( ! empty($settings['ep_review_id']) ) {
			$review_id = absint($settings['ep_review_id']);
			if ($review_id > 0) {
				// Verify the review belongs to this product
				$review = get_comment($review_id);
				if ($review && $review->comment_post_ID === $product_id) {
					return $url . '#review-' . $review_id;
				}
			}
		}

		// Otherwise, link to the review section
		return $url . '#reviews';
	}
}
