<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Gallery_Image extends Data_Tag
{
    use UtilsTrait;

    public function get_name(): string
    {
        return 'element-pack-product-gallery-image';
    }

    public function get_title(): string
    {
        return esc_html__('Product Gallery Image', 'bdthemes-element-pack');
    }

    public function get_group(): array
    {
        return ['element-pack-woocommerce'];
    }

    public function get_categories(): array
    {
        return [
            \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
        ];
    }

    public function is_settings_required(): bool
    {
        return true;
    }

    protected function register_controls(): void
    {
        $this->common_product_controls();

        $this->add_control(
            'ep_gallery_index',
            [
                'label' => esc_html__('Gallery Image Index', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => esc_html__('0 for first gallery image, 1 for second image, etc.', 'bdthemes-element-pack'),
            ]
        );

        $this->add_control(
            'fallback',
            [
                'label' => esc_html__('Fallback', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'description' => esc_html__('This image will be used if the gallery image is not found', 'bdthemes-element-pack'),
            ]
        );
    }

    protected function register_advanced_section(): void {}

    public function get_value(array $options = [])
    {
        $settings = $this->get_settings();
        $product_id = $this->get_product_id();
        $gallery_index = absint($settings['ep_gallery_index'] ?? 0);

        if (!$product_id) {
            return $this->get_settings('fallback');
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return $this->get_settings('fallback');
        }

        // Get gallery image IDs
        $gallery_image_ids = $product->get_gallery_image_ids();

        if (empty($gallery_image_ids)) {
            return $this->get_settings('fallback');
        }

        // Get the image at the specified index
        $image_id = isset($gallery_image_ids[$gallery_index]) ? $gallery_image_ids[$gallery_index] : null;

        if ($image_id) {
            $image_data = [
                'id' => $image_id,
                'url' => wp_get_attachment_image_src($image_id, 'full')[0],
            ];
        } else {
            $image_data = $this->get_settings('fallback');
        }

        return $image_data;
    }
}
