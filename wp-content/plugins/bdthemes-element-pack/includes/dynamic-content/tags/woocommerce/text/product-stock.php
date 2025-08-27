<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Stock extends Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-product-stock';
    }

    public function get_title(): string {
        return esc_html__('Product Stock', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-woocommerce'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    public function is_settings_required() {
        return true;
    }

    protected function register_controls(): void {
        $this->common_product_controls();

        $this->add_control(
            'ep_stock_type',
            [
                'label' => esc_html__('Stock Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'status',
                'options' => [
                    'status' => esc_html__('Status', 'bdthemes-element-pack'),
                    'quantity' => esc_html__('Quantity', 'bdthemes-element-pack'),
                    'low_stock' => esc_html__('Low Stock', 'bdthemes-element-pack'),
                    'stock_status' => esc_html__('Stock Status Text', 'bdthemes-element-pack'),
                ],
            ]
        );

        $this->add_control(
            'ep_stock_text',
            [
                'label' => esc_html__('Stock Text', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('In Stock', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_stock_type' => 'stock_status',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'ep_out_of_stock_text',
            [
                'label' => esc_html__('Out of Stock Text', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Out of Stock', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_stock_type' => 'stock_status',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'ep_backorder_text',
            [
                'label' => esc_html__('Backorder Text', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Available on Backorder', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_stock_type' => 'stock_status',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );
    }

    public function render() {
        $product_id = $this->get_product_id();
        $stock_type = $this->get_settings('ep_stock_type');


        if (!$product_id) {
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }

        switch ($stock_type) {
            case 'status':
                echo esc_html($product->get_stock_status());
                break;

            case 'quantity':
                if ($product->managing_stock()) {
                    echo esc_html($product->get_stock_quantity());
                }
                break;

            case 'low_stock':
                if ($product->managing_stock()) {
                    $low_stock_amount = wc_get_low_stock_amount($product);
                    $stock_quantity = $product->get_stock_quantity();
                    
                    if ($stock_quantity <= $low_stock_amount) {
                        echo esc_html__('Yes', 'bdthemes-element-pack');
                    } else {
                        echo esc_html__('No', 'bdthemes-element-pack');
                    }
                }
                break;

            case 'stock_status':
                $stock_status = $product->get_stock_status();
                $stock_text = '';

                switch ($stock_status) {
                    case 'instock':
                        $stock_text = $this->get_settings('ep_stock_text');
                        break;
                    case 'outofstock':
                        $stock_text = $this->get_settings('ep_out_of_stock_text');
                        break;
                    case 'onbackorder':
                        $stock_text = $this->get_settings('ep_backorder_text');
                        break;
                }

                echo esc_html($stock_text);
                break;
        }
    }
}
