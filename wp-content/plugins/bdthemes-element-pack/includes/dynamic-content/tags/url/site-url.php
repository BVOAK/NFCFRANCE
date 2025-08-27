<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Site_URL extends Data_Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-site-url';
    }

    public function get_title(): string {
        return esc_html__('Site URL', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-site'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
        ];
    }

    public function is_settings_required() {
        return false;
    }

    protected function register_controls(): void {
        $this->add_control(
            'ep_url_type',
            [
                'label' => esc_html__('URL Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'home' => esc_html__('Home URL', 'bdthemes-element-pack'),
                    'site' => esc_html__('Site URL', 'bdthemes-element-pack'),
                    'admin' => esc_html__('Admin URL', 'bdthemes-element-pack'),
                ],
                'default' => 'home',
            ]
        );

        $this->fallback_control();
    }

    protected function register_advanced_section(): void {}

    public function get_value(array $options = []) {
        $url_type = $this->get_settings('ep_url_type');
        $url = '';

        switch ($url_type) {
            case 'site':
                $url = get_site_url();
                break;
            case 'admin':
                $url = admin_url();
                break;
            case 'home':
            default:
                $url = home_url();
                break;
        }

        return $url;
    }
}
