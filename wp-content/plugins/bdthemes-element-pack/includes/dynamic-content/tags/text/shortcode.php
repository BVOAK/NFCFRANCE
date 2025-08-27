<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Shortcode extends \Elementor\Core\DynamicTags\Tag {

    public function get_name(): string {
        return 'element-pack-shortcode';
    }

    public function get_title(): string {
        return esc_html__('Shortcode', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-site'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
        ];
    }

    protected function register_controls(): void {
        $this->add_control(
            'ep_shortcode',
            [
                'label' => esc_html__('Shortcode', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => '',
                'ai' => [
                    'active' => false,
                ]
            ]
        );
    }

    public function render(): void {
        $settings = $this->get_settings();

        if (empty($settings['ep_shortcode'])) {
            return;
        }

        $shortcode_string = trim($settings['ep_shortcode']);
        // Auto-wrap in brackets if not present
        if (strpos($shortcode_string, '[') !== 0) {
            $shortcode_string = '[' . $shortcode_string . ']';
        }
        // Handle escaped quotes
        $shortcode_string = str_replace('"', '"', $shortcode_string);
        $shortcode_string = str_replace("'", "'", $shortcode_string);

        $value = do_shortcode($shortcode_string);

        echo wp_kses_post($value);
    }
} 