<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Product_Rating extends Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-product-rating';
    }

    public function get_title(): string {
        return esc_html__('Product Rating', 'bdthemes-element-pack');
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
            'ep_rating_type',
            [
                'label' => esc_html__('Rating Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'rating',
                'options' => [
                    'rating' => esc_html__('Rating', 'bdthemes-element-pack'),
                    'rating_count' => esc_html__('Rating Count', 'bdthemes-element-pack'),
                    'review_count' => esc_html__('Review Count', 'bdthemes-element-pack'),
                    'rating_text' => esc_html__('Rating Text', 'bdthemes-element-pack'),
                ],
            ]
        );

        $this->add_control(
            'ep_rating_format',
            [
                'label' => esc_html__('Rating Format', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'decimal',
                'options' => [
                    'decimal' => esc_html__('Decimal (4.5)', 'bdthemes-element-pack'),
                    'percentage' => esc_html__('Percentage (90%)', 'bdthemes-element-pack'),
                    'fraction' => esc_html__('Fraction (4.5/5)', 'bdthemes-element-pack'),
                ],
                'condition' => [
                    'ep_rating_type' => 'rating',
                ],
            ]
        );

        $this->add_control(
            'ep_rating_text_format',
            [
                'label' => esc_html__('Text Format', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'both',
                'options' => [
                    'both' => esc_html__('Rating & Count', 'bdthemes-element-pack'),
                    'rating' => esc_html__('Rating Only', 'bdthemes-element-pack'),
                    'count' => esc_html__('Count Only', 'bdthemes-element-pack'),
                    'custom' => esc_html__('Custom Format', 'bdthemes-element-pack'),
                ],
                'condition' => [
                    'ep_rating_type' => 'rating_text',
                ],
            ]
        );

        $this->add_control(
            'ep_rating_text_prefix',
            [
                'label' => esc_html__('Prefix', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Rated', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_rating_type' => 'rating_text',
                    'ep_rating_text_format' => ['both', 'rating', 'custom'],
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'ep_rating_text_suffix',
            [
                'label' => esc_html__('Suffix', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('out of 5', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_rating_type' => 'rating_text',
                    'ep_rating_text_format' => ['both', 'rating', 'custom'],
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'ep_count_text_prefix',
            [
                'label' => esc_html__('Count Prefix', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('based on', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_rating_type' => 'rating_text',
                    'ep_rating_text_format' => ['both', 'count', 'custom'],
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'ep_count_text_suffix',
            [
                'label' => esc_html__('Count Suffix', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('ratings', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_rating_type' => 'rating_text',
                    'ep_rating_text_format' => ['both', 'count', 'custom'],
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'ep_custom_format',
            [
                'label' => esc_html__('Custom Format', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('{prefix} {rating} {suffix} {count_prefix} {count} {count_suffix}', 'bdthemes-element-pack'),
                'description' => esc_html__('Available tags: {prefix}, {rating}, {suffix}, {count_prefix}, {count}, {count_suffix}', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_rating_type' => 'rating_text',
                    'ep_rating_text_format' => 'custom',
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
        $rating_type = $settings['ep_rating_type'] ?? 'rating';

        if (!$product_id) {
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }

        $rating = $product->get_average_rating();
        $rating_count = $product->get_rating_count();
        $review_count = $product->get_review_count();

        // Return if rating is 0
        if ($rating <= 0) {
            return;
        }

        switch ($rating_type) {
            case 'rating':
                $format = $settings['ep_rating_format'] ?? 'decimal';
                
                switch ($format) {
                    case 'decimal':
                        echo esc_html(number_format($rating, 1));
                        break;
                    case 'percentage':
                        echo esc_html(round($rating * 20) . '%');
                        break;
                    case 'fraction':
                        echo esc_html(number_format($rating, 1) . '/5');
                        break;
                }
                break;

            case 'rating_count':
                if ($rating_count > 0) {
                    echo esc_html($rating_count);
                }
                break;

            case 'review_count':
                if ($review_count > 0) {
                    echo esc_html($review_count);
                }
                break;

            case 'rating_text':
                $rating_text = '';
                switch (true) {
                    case $rating >= 4.5:
                        $rating_text = esc_html__('Excellent', 'bdthemes-element-pack');
                        break;
                    case $rating >= 3.5:
                        $rating_text = esc_html__('Very Good', 'bdthemes-element-pack');
                        break;
                    case $rating >= 2.5:
                        $rating_text = esc_html__('Good', 'bdthemes-element-pack');
                        break;
                    case $rating >= 1.5:
                        $rating_text = esc_html__('Fair', 'bdthemes-element-pack');
                        break;
                    default:
                        $rating_text = esc_html__('Poor', 'bdthemes-element-pack');
                }
                echo esc_html($rating_text);
                break;
        }
    }
}
