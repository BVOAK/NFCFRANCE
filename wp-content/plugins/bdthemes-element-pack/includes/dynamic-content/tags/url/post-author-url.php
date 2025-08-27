<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Post_Author_URL extends Data_Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-post-author-url';
    }

    public function get_title(): string {
        return esc_html__('Post Author URL', 'bdthemes-element-pack');
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
                    'archive' => esc_html__('Author Archive', 'bdthemes-element-pack'),
                    'website' => esc_html__('Author Website', 'bdthemes-element-pack'),
                ],
                'default' => 'archive',
            ]
        );

        $this->fallback_control();
    }

    protected function register_advanced_section(): void {}

    public function get_value(array $options = []) {
        $settings = $this->get_settings();
        $post_id = $this->get_post_id();

        if (!$post_id) {
            return '';
        }

        $post = get_post($post_id);

        if (!$post) {
            return '';
        }

        $url = '';

        if ($settings['ep_url_type'] === 'website') {
            $url = get_the_author_meta('user_url', $post->post_author);
        } else {
            $url = get_author_posts_url($post->post_author);
        }

        if (empty($url)) {
            return '';
        }

        return $url;
    }
}
