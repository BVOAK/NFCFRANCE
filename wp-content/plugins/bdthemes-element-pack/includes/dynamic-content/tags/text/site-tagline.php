<?php

use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Site_Tagline extends \Elementor\Core\DynamicTags\Tag {

    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-site-tagline';
    }

    public function get_title(): string {
        return esc_html__('Site Tagline', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-site'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    protected function register_advanced_section() {
        $this->advanced_controls();
    }

    public function render(): void {
        echo wp_kses_post($this->apply_word_limit(get_bloginfo('description')));
    }
} 