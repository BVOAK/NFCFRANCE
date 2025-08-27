<?php

use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Post_Comments_URL extends \Elementor\Core\DynamicTags\Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-post-comments-url';
    }

    public function get_title(): string {
        return esc_html__('Post Comments URL', 'bdthemes-element-pack');
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
            'ep_url_type',
            [
                'label' => esc_html__('URL Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'comments' => esc_html__('Comments', 'bdthemes-element-pack'),
                    'respond' => esc_html__('Respond', 'bdthemes-element-pack'),
                ],
                'default' => 'comments',
            ]
        );

        $this->fallback_control();
    }

    protected function register_advanced_section(): void {}

    public function render(): void {
        $settings = $this->get_settings();
        $post_id = $this->get_post_id();

        if (!$post_id) {
            return;
        }

        $post = get_post($post_id);
        
        if (!$post) {
            return;
        }

        $url = '';

        if ($settings['ep_url_type'] === 'respond') {
            $url = get_permalink($post_id) . '#respond';
        } else {
            $url = get_comments_link($post_id);
        }

        if (empty($url)) {
            return;
        }

        echo esc_url($url);
    }
}
