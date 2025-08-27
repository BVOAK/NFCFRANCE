<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Post_Featured_Image_Data extends Data_Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-post-featured-image';
    }

    public function get_title(): string {
        return esc_html__('Featured Image', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-post'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
        ];
    }

    public function is_settings_required() {
        return true;
    }

    protected function register_controls(): void {
        $this->common_post_controls();

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
        $post_id = $this->get_post_id();

        if (!$post_id) {
            return $this->get_settings('fallback');
        }

        $thumbnail_id = get_post_thumbnail_id($post_id);

        if ($thumbnail_id) {
            $image_data = [
                'id' => $thumbnail_id,
                'url' => wp_get_attachment_image_src($thumbnail_id, 'full')[0],
            ];
        } else {
            $image_data = $this->get_settings('fallback');
        }

        return $image_data;
    }
}
