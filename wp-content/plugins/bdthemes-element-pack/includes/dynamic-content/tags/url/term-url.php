<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Term_URL extends Data_Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-term-url';
    }

    public function get_title(): string {
        return esc_html__('Term URL', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-term'];
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
        $this->common_term_controls();

        $this->add_control(
            'ep_url_type',
            [
                'label' => esc_html__('URL Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'archive' => esc_html__('Term Archive', 'bdthemes-element-pack'),
                    'feed' => esc_html__('Term Feed', 'bdthemes-element-pack'),
                ],
                'default' => 'archive',
            ]
        );

        $this->fallback_control();
    }

    protected function register_advanced_section(): void {}

    public function get_value(array $options = []) {
        $settings = $this->get_settings();
        $term_id = $this->get_term_id();

        if (empty($term_id)) {
            return '';
        }

        $term = get_term($term_id);

        if (is_wp_error($term) || empty($term)) {
            return '';
        }

        $url = '';

        if ($settings['ep_url_type'] === 'feed') {
            $url = get_term_feed_link($term_id, $term->taxonomy);
        } else {
            $url = get_term_link($term);
        }

        if (is_wp_error($url)) {
            return '';
        }

        return $url;
    }
}
