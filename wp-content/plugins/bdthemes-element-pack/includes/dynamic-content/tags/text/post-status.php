<?php

use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Post_Status extends \Elementor\Core\DynamicTags\Tag {

    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-post-status';
    }

    public function get_title(): string {
        return esc_html__('Post Status', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-post'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    public function is_settings_required() {
        return true;
    }

    protected function register_controls(): void {
        $this->common_post_controls();
    }

    public function render(): void {
        $post_id = $this->get_post_id();
        if (!$post_id) return;
        $post = get_post($post_id);
        if (!$post) return;
        $status = get_post_status($post_id);
        echo wp_kses_post($status);
    }
}
