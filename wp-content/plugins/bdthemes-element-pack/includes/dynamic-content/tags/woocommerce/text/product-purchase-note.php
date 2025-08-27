<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!function_exists('wc_get_product')) {
    return; // Exit if WooCommerce is not active
}

class ElementPack_Dynamic_Tag_Product_Purchase_Note extends Tag
{
    use UtilsTrait;

    public function get_name(): string
    {
        return 'element-pack-product-purchase-note';
    }

    public function get_title(): string
    {
        return esc_html__('Product Purchase Note', 'bdthemes-element-pack');
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
            'ep_purchase_note_format',
            [
                'label' => esc_html__('Format', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'text',
                'options' => [
                    'text' => esc_html__('Plain Text', 'bdthemes-element-pack'),
                    'html' => esc_html__('HTML', 'bdthemes-element-pack'),
                ],
            ]
        );
    }

    public function render()
    {
        $settings = $this->get_settings();
        $product_id = $this->get_product_id();
        $format = $settings['ep_purchase_note_format'] ?? 'text';

        if (!$product_id) {
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }

        $purchase_note = $product->get_purchase_note();

        if (empty($purchase_note)) {
            return;
        }

        switch ($format) {
            case 'html':
                // Allow safe HTML tags
                echo wp_kses_post($purchase_note);
                break;

            case 'text':
            default:
                // Strip all HTML and convert to plain text
                echo esc_html(strip_tags($purchase_note));
                break;
        }
    }
}
