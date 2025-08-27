<?php
use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Term_Meta extends \Elementor\Core\DynamicTags\Tag {
    use UtilsTrait;
    
    public function get_name(): string {
        return 'element-pack-term-meta';
    }

    public function get_title(): string {
        return esc_html__('Term Meta', 'bdthemes-element-pack');
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
        $this->add_control('ep_term_meta_key', [
            'label' => esc_html__('Meta Key', 'bdthemes-element-pack'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'label_block' => true,
            'condition' => [
                'ep_selected_term_id!' => '',
            ],
            'ai' => [
                'active' => false,
            ],
        ]);
    }

    protected function register_advanced_section()
    {
        $this->advanced_controls();
    }

    public function render(): void {
        $term_id = $this->get_term_id();
        if (empty($term_id)) return;

        $term_meta_key = $this->get_settings('ep_term_meta_key');
        if (empty($term_meta_key)) return;

        $term_meta = get_term_meta($term_id, $term_meta_key, true);
        if (empty($term_meta)) return;

        echo esc_html($this->apply_word_limit($term_meta));
    }
} 