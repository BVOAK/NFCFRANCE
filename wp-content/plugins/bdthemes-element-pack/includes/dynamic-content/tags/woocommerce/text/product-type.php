<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Type extends Tag
{
    use UtilsTrait;

    public function get_name(): string
    {
        return 'element-pack-product-type';
    }

    public function get_title(): string
    {
        return esc_html__('Product Type', 'bdthemes-element-pack');
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
            'ep_type_format',
            [
                'label' => esc_html__('Type Format', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name' => esc_html__('Name', 'bdthemes-element-pack'),
                    'slug' => esc_html__('Slug', 'bdthemes-element-pack'),
                    'label' => esc_html__('Label', 'bdthemes-element-pack'),
                    'properties' => esc_html__('Properties', 'bdthemes-element-pack'),
                ],
            ]
        );

        $this->add_control(
            'ep_property_separator',
            [
                'label' => esc_html__('Property Separator', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => ', ',
                'condition' => [
                    'ep_type_format' => 'properties',
                ],
            ]
        );
    }

    public function render()
    {
        $product_id = $this->get_product_id();
        $type_format = $this->get_settings('ep_type_format');
        $property_separator = $this->get_settings('ep_property_separator');

        if (!$product_id) {
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }

        $product_type = $product->get_type();
        $type_info = '';

        switch ($type_format) {
            case 'name':
                $type_info = ucfirst($product_type);
                break;

            case 'slug':
                $type_info = $product_type;
                break;

            case 'label':
                $type_labels = [
                    'simple' => esc_html__('Simple Product', 'bdthemes-element-pack'),
                    'grouped' => esc_html__('Grouped Product', 'bdthemes-element-pack'),
                    'external' => esc_html__('External/Affiliate Product', 'bdthemes-element-pack'),
                    'variable' => esc_html__('Variable Product', 'bdthemes-element-pack'),
                ];
                $type_info = isset($type_labels[$product_type]) ? $type_labels[$product_type] : ucfirst($product_type);
                break;

            case 'properties':
                $properties = [];
                
                // Add base type
                $type_labels = [
                    'simple' => esc_html__('Simple', 'bdthemes-element-pack'),
                    'grouped' => esc_html__('Grouped', 'bdthemes-element-pack'),
                    'external' => esc_html__('External', 'bdthemes-element-pack'),
                    'variable' => esc_html__('Variable', 'bdthemes-element-pack'),
                ];
                $properties[] = isset($type_labels[$product_type]) ? $type_labels[$product_type] : ucfirst($product_type);

                // Add downloadable property
                if ($product->is_downloadable()) {
                    $properties[] = esc_html__('Downloadable', 'bdthemes-element-pack');
                }

                // Add virtual property
                if ($product->is_virtual()) {
                    $properties[] = esc_html__('Virtual', 'bdthemes-element-pack');
                }

                $type_info = implode($property_separator, $properties);
                break;
        }

        echo wp_kses_post($type_info);
    }
}
