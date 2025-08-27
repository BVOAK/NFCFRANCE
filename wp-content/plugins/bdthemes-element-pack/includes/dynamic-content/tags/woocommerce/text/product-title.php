<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Title extends Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-product-title';
    }

    public function get_title(): string {
        return esc_html__('Product Title', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-woocommerce'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    protected function register_controls(): void {
        $this->common_product_controls();
    }

    public function render() {
        $product_id = $this->get_product_id();

        if (!$product_id) {
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }

        echo esc_html($product->get_title());
    }
}
