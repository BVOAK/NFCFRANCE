<?php

use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;
use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Post_Terms extends \Elementor\Core\DynamicTags\Tag {

    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-post-terms';
    }

    public function get_title(): string {
        return esc_html__('Post Terms', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-post'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    public function is_settings_required() {
        return true;
    }

    public function get_all_taxonomies_array($output = 'names', $operator = 'and') {
        $args = [
            'public'   => true,
            'show_ui'  => true,
        ];
        $taxonomies = get_taxonomies($args, 'objects', $operator);
        $taxonomies_array = [];
        foreach ($taxonomies as $taxonomy) {
            $taxonomies_array[$taxonomy->name] = $taxonomy->label;
        }
        return $taxonomies_array;
    }

    protected function register_controls(): void {
        $this->common_post_controls();

        $this->add_control(
            'ep_taxonomy',
            [
                'label' => esc_html__('Taxonomy', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_all_taxonomies_array(),
                'default' => 'category',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'ep_separator',
            [
                'label' => esc_html__('Separator', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => ', ',
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'ep_link',
            [
                'label' => esc_html__('Link', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        // Add limit control
        $this->add_control(
            'ep_terms_limit',
            [
                'label' => esc_html__('Limit', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => esc_html__('0 means no limit', 'bdthemes-element-pack'),
            ]
        );

        // Add offset control
        $this->add_control(
            'ep_terms_offset',
            [
                'label' => esc_html__('Offset', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
            ]
        );
    }

    public function render(): void {
        $settings = $this->get_settings();

        $post_id = $this->get_post_id();

        if (!$post_id) return;

        $taxonomy = $settings['ep_taxonomy'];
        $separator = $settings['ep_separator'];
        $should_link = 'yes' === $settings['ep_link'];
        
        $terms = wp_get_post_terms($post_id, $taxonomy);
        
        if (is_wp_error($terms) || empty($terms)) return;

        // Apply offset and limit
        $limit = isset($settings['ep_terms_limit']) ? intval($settings['ep_terms_limit']) : 0;
        $offset = isset($settings['ep_terms_offset']) ? intval($settings['ep_terms_offset']) : 0;
        if ($limit > 0) {
            $terms = array_slice($terms, $offset, $limit);
        } else {
            $terms = array_slice($terms, $offset);
        }

        $terms_list = [];

        foreach ($terms as $term) {
            if ($should_link) {
                $terms_list[] = '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
            } else {
                $terms_list[] = esc_html($term->name);
            }
        }

        echo wp_kses_post(implode($separator, $terms_list));
    }
} 