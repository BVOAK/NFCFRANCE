<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Post_URL extends Data_Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-post-url';
    }

    public function get_title(): string {
        return esc_html__('Post URL', 'bdthemes-element-pack');
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
        return false;
    }

    protected function register_controls(): void {
        $this->common_post_controls();
        $this->fallback_control();
    }

    protected function register_advanced_section(): void {}

    public function get_value(array $options = []) {
        $value = '';

        // Get post ID based on settings
        $post_id = $this->get_post_id();

        if (empty($post_id)) {
            return $value;
        }

        $value = get_the_permalink($post_id);

        return $value;
    }
}
