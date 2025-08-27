<?php
use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Search_Results_Count extends \Elementor\Core\DynamicTags\Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-search-results-count';
    }

    public function get_title(): string {
        return esc_html__('Search Results Count', 'bdthemes-element-pack');
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
        if( !is_search() ) return;

        global $wp_query;
        echo $wp_query->found_posts;
    }
} 