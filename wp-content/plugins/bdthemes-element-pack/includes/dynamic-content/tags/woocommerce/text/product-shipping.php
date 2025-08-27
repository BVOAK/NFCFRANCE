<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Shipping extends Tag
{
    use UtilsTrait;

    public function get_name(): string
    {
        return 'element-pack-product-shipping';
    }

    public function get_title(): string
    {
        return esc_html__('Product Shipping', 'bdthemes-element-pack');
    }

    public function get_group(): array
    {
        return ['element-pack-woocommerce'];
    }

    public function get_categories(): array
    {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
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
            'ep_shipping_type',
            [
                'label' => esc_html__('Shipping Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'weight',
                'options' => [
                    'weight' => esc_html__('Weight', 'bdthemes-element-pack'),
                    'dimensions' => esc_html__('Dimensions', 'bdthemes-element-pack'),
                    'class' => esc_html__('Shipping Class', 'bdthemes-element-pack'),
                ],
            ]
        );

        $this->add_control(
            'ep_dimension_type',
            [
                'label' => esc_html__('Dimension Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'all',
                'options' => [
                    'all' => esc_html__('All Dimensions', 'bdthemes-element-pack'),
                    'length' => esc_html__('Length', 'bdthemes-element-pack'),
                    'width' => esc_html__('Width', 'bdthemes-element-pack'),
                    'height' => esc_html__('Height', 'bdthemes-element-pack'),
                ],
                'condition' => [
                    'ep_shipping_type' => 'dimensions',
                ],
            ]
        );
    }

    public function render()
    {
        $product_id = $this->get_product_id();
        $shipping_type = $this->get_settings('ep_shipping_type');
        $dimension_type = $this->get_settings('ep_dimension_type');

        if (!$product_id) {
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }

        $shipping_info = '';

        switch ($shipping_type) {
            case 'weight':
                $weight = $product->get_weight();
                if (!empty($weight)) {
                    $shipping_info = wc_format_weight($weight);
                }
                break;

            case 'dimensions':
                $length = $product->get_length();
                $width = $product->get_width();
                $height = $product->get_height();

                switch ($dimension_type) {
                    case 'length':
                        if (!empty($length)) {
                            $shipping_info = wc_format_dimensions([$length]);
                        }
                        break;

                    case 'width':
                        if (!empty($width)) {
                            $shipping_info = wc_format_dimensions([$width]);
                        }
                        break;

                    case 'height':
                        if (!empty($height)) {
                            $shipping_info = wc_format_dimensions([$height]);
                        }
                        break;

                    case 'all':
                        $shipping_info = wc_format_dimensions([$length, $width, $height]);
                        break;
                }
                break;

            case 'class':
                $shipping_class = $product->get_shipping_class();
                if (!empty($shipping_class)) {
                    $term = get_term_by('slug', $shipping_class, 'product_shipping_class');
                    $shipping_info = $term ? $term->name : $shipping_class;
                }
                break;
        }

        echo wp_kses_post($shipping_info);
    }
}
