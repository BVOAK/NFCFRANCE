<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Archive_URL extends Data_Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-archive-url';
    }

    public function get_title(): string {
        return esc_html__('Archive URL', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-archive'];
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
        $this->fallback_control();
    }

    protected function register_advanced_section(): void {}

    public function get_value(array $options = []) {
        $url = '';

        if (is_category() || is_tag() || is_tax()) {
            $url = get_term_link(get_queried_object());
        } elseif (is_author()) {
            $url = get_author_posts_url(get_queried_object_id());
        } elseif (is_date()) {
            if (is_year()) {
                $url = get_year_link(get_query_var('year'));
            } elseif (is_month()) {
                $url = get_month_link(get_query_var('year'), get_query_var('monthnum'));
            } else {
                $url = get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
            }
        } elseif (is_post_type_archive()) {
            $url = get_post_type_archive_link(get_query_var('post_type'));
        }
        
        if (is_wp_error($url)) {
            $url = '';
        }

        return $url;
    }
}
