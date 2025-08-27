<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Archive_Meta_Image extends Data_Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-archive-meta-image';
    }

    public function get_title(): string {
        return esc_html__('Archive Meta Image', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-archive'];
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
        $this->add_control(
            'ep_meta_key',
            [
                'label' => esc_html__('Meta Key', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__('Enter meta key', 'bdthemes-element-pack'),
                'description' => esc_html__('Enter the meta key that stores the image ID, URL, or array', 'bdthemes-element-pack'),
                'ai' => [
                    'active' => false,
                ],
            ]
        );

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
        $meta_key = $this->get_settings('ep_meta_key');

        if (!$meta_key) {
            return $this->get_settings('fallback');
        }

        $value = null;

        // Handle different archive types
        if (is_author()) {
            // Get author ID
            $author_id = get_queried_object_id();
            if ($author_id) {
                $value = get_user_meta($author_id, $meta_key, true);
            }
        } elseif (is_category() || is_tag() || is_tax()) {
            // Get term ID
            $term_id = get_queried_object_id();
            if ($term_id) {
                $value = get_term_meta($term_id, $meta_key, true);
            }
        }

        if (!$value) {
            return $this->get_settings('fallback');
        }

        // Handle different types of image values
        if (is_array($value)) {
            // Handle ACF image array
            if (isset($value['ID']) && isset($value['url'])) {
                return [
                    'id' => $value['ID'],
                    'url' => $value['url'],
                ];
            }
            // Handle other image arrays
            elseif (isset($value['id']) && isset($value['url'])) {
                return [
                    'id' => $value['id'],
                    'url' => $value['url'],
                ];
            }
            // Handle array with just ID
            elseif (isset($value['ID'])) {
                $image_url = wp_get_attachment_image_url($value['ID'], 'full');
                if ($image_url) {
                    return [
                        'id' => $value['ID'],
                        'url' => $image_url,
                    ];
                }
            }
            // Handle array with just id
            elseif (isset($value['id'])) {
                $image_url = wp_get_attachment_image_url($value['id'], 'full');
                if ($image_url) {
                    return [
                        'id' => $value['id'],
                        'url' => $image_url,
                    ];
                }
            }
        } elseif (is_numeric($value)) {
            // If value is an attachment ID
            $image_url = wp_get_attachment_image_url($value, 'full');
            if ($image_url) {
                return [
                    'id' => $value,
                    'url' => $image_url,
                ];
            }
        } elseif (is_string($value)) {
            // If value is a URL
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return [
                    'id' => 0,
                    'url' => $value,
                ];
            }
        }

        return $this->get_settings('fallback');
    }
} 