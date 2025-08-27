<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Description extends Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-product-description';
    }

    public function get_title(): string {
        return esc_html__('Product Description', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-woocommerce'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    public function is_settings_required(): bool {
        return true;
    }

    protected function register_controls(): void {
        $this->common_product_controls();

        $this->add_control(
            'ep_description_type',
            [
                'label' => esc_html__('Description Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'short',
                'options' => [
                    'short' => esc_html__('Short Description', 'bdthemes-element-pack'),
                    'full' => esc_html__('Full Description', 'bdthemes-element-pack'),
                ],
            ]
        );

        $this->add_control(
            'ep_description_prefix',
            [
                'label' => esc_html__('Description Prefix', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'ep_strip_tags',
            [
                'label' => esc_html__('Strip HTML Tags', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
                'label_on' => esc_html__('Yes', 'bdthemes-element-pack'),
                'label_off' => esc_html__('No', 'bdthemes-element-pack'),
            ]
        );

        $this->add_control(
            'ep_limit_words',
            [
                'label' => esc_html__('Limit Words', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'step' => 1,
                'condition' => [
                    'ep_strip_tags' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'ep_ellipsis',
            [
                'label' => esc_html__('Ellipsis', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '...',
                'condition' => [
                    'ep_strip_tags' => 'yes',
                    'ep_limit_words!' => '',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );
    }

    public function render() {
        $settings = $this->get_settings();
        $product_id = $this->get_product_id();
        $description_type = $settings['ep_description_type'] ?? 'short';
        $description_prefix = $settings['ep_description_prefix'] ?? '';
        $strip_tags = $settings['ep_strip_tags'] === 'yes';
        $limit_words = (int)($settings['ep_limit_words'] ?? 0);
        $ellipsis = $settings['ep_ellipsis'] ?? '...';

        if (!$product_id) {
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }

        // Get description based on type
        $description = $description_type === 'short' 
            ? $product->get_short_description() 
            : $product->get_description();

        // Process description based on settings
        if ($strip_tags) {
            $description = wp_strip_all_tags($description);

            if ($limit_words > 0) {
                $description = wp_trim_words($description, $limit_words, $ellipsis);
            }
        }

        echo wp_kses_post($description_prefix . ' ' . $description);
    }
}
