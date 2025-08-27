<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Post_Navigation_URL extends Data_Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-post-navigation-url';
    }

    public function get_title(): string {
        return esc_html__('Post Navigation URL', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-post'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
        ];
    }

    public function is_settings_required() {
        return true;
    }

    protected function register_controls(): void {
        $this->common_post_controls();

        $this->add_control(
            'ep_navigation_type',
            [
                'label' => esc_html__('Navigation Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'previous' => esc_html__('Previous Post', 'bdthemes-element-pack'),
                    'next' => esc_html__('Next Post', 'bdthemes-element-pack'),
                    'parent' => esc_html__('Parent Post', 'bdthemes-element-pack'),
                    'child' => esc_html__('Child Post', 'bdthemes-element-pack'),
                ],
                'default' => 'next',
            ]
        );

        $this->add_control(
            'ep_same_term',
            [
                'label' => esc_html__('Same Term', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'bdthemes-element-pack'),
                'label_off' => esc_html__('No', 'bdthemes-element-pack'),
                'default' => 'no',
                'description' => esc_html__('Whether to navigate within the same term.', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_navigation_type' => ['previous', 'next'],
                ],
            ]
        );

        $this->add_control(
            'ep_taxonomy',
            [
                'label' => esc_html__('Taxonomy', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_taxonomies_options(),
                'default' => 'category',
                'condition' => [
                    'ep_same_term' => 'yes',
                    'ep_navigation_type' => ['previous', 'next'],
                ],
            ]
        );

        $this->add_control(
            'ep_child_order',
            [
                'label' => esc_html__('Child Order', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'menu_order' => esc_html__('Menu Order', 'bdthemes-element-pack'),
                    'date' => esc_html__('Date', 'bdthemes-element-pack'),
                    'title' => esc_html__('Title', 'bdthemes-element-pack'),
                ],
                'default' => 'menu_order',
                'condition' => [
                    'ep_navigation_type' => 'child',
                ],
            ]
        );

        $this->fallback_control();
    }

    protected function register_advanced_section(): void {}

    private function get_taxonomies_options(): array {
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $options = [];

        foreach ($taxonomies as $taxonomy) {
            $options[$taxonomy->name] = $taxonomy->label;
        }

        return $options;
    }

    public function get_value(array $options = []) {
        $settings = $this->get_settings();
        $post_id = $this->get_post_id();

        if (!$post_id) {
            return '';
        }

        $navigation_type = $settings['ep_navigation_type'] ?? 'next';
        $post = get_post($post_id);

        if (!$post) {
            return '';
        }

        // Handle parent/child navigation
        if ($navigation_type === 'parent') {
            if ($post->post_parent) {
                return get_permalink($post->post_parent);
            }
            return '';
        }

        if ($navigation_type === 'child') {
            $child_order = $settings['ep_child_order'] ?? 'menu_order';
            $args = [
                'post_type' => $post->post_type,
                'post_parent' => $post_id,
                'posts_per_page' => 1,
                'orderby' => $child_order,
                'order' => 'ASC',
                'post_status' => 'publish',
            ];

            $query = new \WP_Query($args);
            if (!$query->have_posts()) {
                return '';
            }

            return get_permalink($query->posts[0]->ID);
        }

        // Handle previous/next navigation
        $same_term = $settings['ep_same_term'] === 'yes';
        $taxonomy = $settings['ep_taxonomy'] ?? 'category';

        $args = [
            'post_type' => $post->post_type,
            'posts_per_page' => 1,
            'order' => $navigation_type === 'next' ? 'ASC' : 'DESC',
            'orderby' => 'date',
            'post_status' => 'publish',
            'post__not_in' => [$post_id],
        ];

        if ($same_term) {
            $terms = get_the_terms($post_id, $taxonomy);
            if ($terms && !is_wp_error($terms)) {
                $term_ids = wp_list_pluck($terms, 'term_id');
                $args['tax_query'] = [
                    [
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $term_ids,
                    ],
                ];
            }
        }

        // Get the post date for comparison
        $post_date = get_post_field('post_date', $post_id);
        if ($navigation_type === 'next') {
            $args['date_query'] = [
                [
                    'after' => $post_date,
                ],
            ];
        } else {
            $args['date_query'] = [
                [
                    'before' => $post_date,
                ],
            ];
        }

        $query = new \WP_Query($args);

        if (!$query->have_posts()) {
            return '';
        }

        return get_permalink($query->posts[0]->ID);
    }
} 