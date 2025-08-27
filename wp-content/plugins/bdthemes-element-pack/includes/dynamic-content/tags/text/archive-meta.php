<?php

use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Archive_Meta extends \Elementor\Core\DynamicTags\Tag {

    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-archive-meta';
    }

    public function get_title(): string {
        return esc_html__('Archive Meta', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-archive'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    protected function register_controls(): void {
        $this->add_control(
            'ep_archive_meta_key',
            [
                'label' => esc_html__('Meta Key', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__('Enter the meta key to retrieve the value', 'bdthemes-element-pack'),
                'ai' => [
                    'active' => false,
                ],
            ]
        );
    }

    protected function register_advanced_section() {
        $this->advanced_controls();
    }

    public function render(): void {
        $key = $this->get_settings('ep_archive_meta_key');
        
        if (empty($key)) {
            return;
        }

        $meta_value = '';

        if (is_category() || is_tag() || is_tax()) {
            $term_id = get_queried_object_id();
            $meta_value = get_term_meta($term_id, $key, true);
        } elseif (is_author()) {
            $author_id = get_queried_object_id();
            $meta_value = get_user_meta($author_id, $key, true);
        }

        if (is_array($meta_value)) {
            $meta_value = implode(', ', $meta_value);
        }

        echo wp_kses_post($this->apply_word_limit($meta_value));
    }
} 