<?php
use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Term_Title extends \Elementor\Core\DynamicTags\Tag {
    use UtilsTrait;
    
    public function get_name(): string {
        return 'element-pack-term-title';
    }

    public function get_title(): string {
        return esc_html__('Term Title', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-term'];
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
        $this->common_term_controls();
    }

    protected function register_advanced_section()
    {
        $this->advanced_controls();
    }

    public function render(): void {
        $term_id = $this->get_term_id();
        if (empty($term_id)) return;

        $term = get_term($term_id);
        if (is_wp_error($term) || empty($term)) return;

        echo esc_html($this->apply_word_limit($term->name));
    }
} 