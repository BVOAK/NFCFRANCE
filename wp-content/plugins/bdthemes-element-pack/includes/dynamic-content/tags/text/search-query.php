<?php
use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Search_Query extends \Elementor\Core\DynamicTags\Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-search-query';
    }

    public function get_title(): string {
        return esc_html__('Search Query', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-search'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    protected function register_advanced_section()
    {
        $this->advanced_controls();
    }

    public function render(): void {
        echo esc_html($this->apply_word_limit(get_search_query()));
    }
} 