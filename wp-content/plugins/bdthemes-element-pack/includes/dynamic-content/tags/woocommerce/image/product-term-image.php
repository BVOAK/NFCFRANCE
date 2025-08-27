<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Term_Image extends Data_Tag
{
    use UtilsTrait;

    public function get_name(): string
    {
        return 'element-pack-product-term-image';
    }

    public function get_title(): string
    {
        return esc_html__('Product Term Image', 'bdthemes-element-pack');
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

    private function get_product_taxonomies_options(): array
    {
        $taxonomies = get_object_taxonomies('product', 'objects');
        $options = [];

        foreach ($taxonomies as $taxonomy) {
            // Skip product shipping class
            if ($taxonomy->name === 'product_shipping_class') {
                continue;
            }

            if ($taxonomy->public) {
                $options[$taxonomy->name] = $taxonomy->label;
            }
        }

        return $options;
    }

    protected function register_controls(): void
    {
        $this->common_product_controls();

        $this->add_control(
            'ep_taxonomy',
            [
                'label' => esc_html__('Taxonomy', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'product_cat' => esc_html__('Category', 'bdthemes-element-pack'),
                    'product_brand' => esc_html__('Brand', 'bdthemes-element-pack'),
                ],
                'default' => 'product_cat',
            ]
        );

        $this->add_control(
            'ep_term_index',
            [
                'label' => esc_html__('Term Index', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => esc_html__('0 for first term, 1 for second term, etc.', 'bdthemes-element-pack'),
            ]
        );

        $this->add_control(
            'fallback',
            [
                'label' => esc_html__('Fallback', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'description' => esc_html__('This image will be used if the term has no image', 'bdthemes-element-pack'),
            ]
        );
    }

    protected function register_advanced_section(): void {}

    public function get_value(array $options = [])
    {
        $settings = $this->get_settings();
        $product_id = $this->get_product_id();
        $taxonomy = $settings['ep_taxonomy'] ?? 'product_cat';
        $term_index = absint($settings['ep_term_index'] ?? 0);
        
        if (!$product_id) {
            return $this->get_settings('fallback');
        }

        // Get all terms for the product in the selected taxonomy
        $terms = get_the_terms($product_id, $taxonomy);
        
        if (!$terms || is_wp_error($terms)) {
            return $this->get_settings('fallback');
        }

        // Get the term at the specified index
        $term = isset($terms[$term_index]) ? $terms[$term_index] : [];
        
        if (!$term) {
            return $this->get_settings('fallback');
        }

        $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);

        if ($thumbnail_id) {
            $image_data = [
                'id' => $thumbnail_id,
                'url' => wp_get_attachment_image_src($thumbnail_id, 'full')[0],
            ];
        } else {
            $image_data = $this->get_settings('fallback');
        }

        return $image_data;
    }
}
