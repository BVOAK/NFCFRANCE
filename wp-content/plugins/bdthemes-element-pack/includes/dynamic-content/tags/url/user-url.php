<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_User_URL extends Data_Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-user-url';
    }

    public function get_title(): string {
        return esc_html__('User URL', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-user'];
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
        $this->common_user_controls();

        $this->add_control(
            'ep_url_type',
            [
                'label' => esc_html__('URL Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'archive' => esc_html__('Author Archive', 'bdthemes-element-pack'),
                    'website' => esc_html__('Website', 'bdthemes-element-pack'),
                    'email' => esc_html__('Email', 'bdthemes-element-pack'),
                    'feed' => esc_html__('Author Feed', 'bdthemes-element-pack'),
                ],
                'default' => 'archive',
            ]
        );

        $this->fallback_control();
    }

    protected function register_advanced_section(): void {}

    public function get_value(array $options = []) {
        $settings = $this->get_settings();
        $user_id = $this->get_user_id();

        if (empty($user_id)) {
            return '';
        }

        $user = get_user_by('id', $user_id);

        if (!$user) {
            return '';
        }

        $url = '';

        switch ($settings['ep_url_type']) {
            case 'website':
                $url = $user->user_url;
                break;
            case 'email':
                $url = 'mailto:' . $user->user_email;
                break;
            case 'feed':
                $url = get_author_feed_link($user_id);
                break;
            case 'archive':
            default:
                $url = get_author_posts_url($user_id);
                break;
        }

        if (empty($url)) {
            return '';
        }

        return $url;
    }
}
