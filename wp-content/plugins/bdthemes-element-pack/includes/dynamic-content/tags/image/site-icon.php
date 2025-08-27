<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Site_Icon extends Data_Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-site-icon';
    }

    public function get_title(): string {
        return esc_html__('Site Icon', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-site'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
        ];
    }

    public function is_settings_required() {
        return false;
    }

    protected function register_controls(): void {
        $this->add_control(
            'fallback',
            [
                'label' => esc_html__('Fallback', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::MEDIA,
            ]
        );
    }

    protected function register_advanced_section(): void {}

    public function get_value(array $options = []) {
        $site_icon_id = get_option('site_icon');

        if ($site_icon_id) {
            $image_url = wp_get_attachment_image_url($site_icon_id, 'full');
            if ($image_url) {
                return [
                    'id' => $site_icon_id,
                    'url' => $image_url,
                ];
            }
        }

        return $this->get_settings('fallback');
    }
} 