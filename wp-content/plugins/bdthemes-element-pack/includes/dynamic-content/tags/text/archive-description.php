<?php

use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Archive_Description extends \Elementor\Core\DynamicTags\Tag {

    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-archive-description';
    }

    public function get_title(): string {
        return esc_html__('Archive Description', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-archive'];
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
        $description = '';

        if (is_category() || is_tag() || is_tax()) {
            $description = term_description();
        } elseif (is_author()) {
            $description = get_the_author_meta('description');
        } elseif (is_post_type_archive()) {
            $description = get_the_post_type_description();
        }

        echo wp_kses_post($this->apply_word_limit($description));
    }
} 