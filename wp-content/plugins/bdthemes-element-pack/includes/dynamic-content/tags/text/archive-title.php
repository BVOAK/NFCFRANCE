<?php

use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Archive_Title extends \Elementor\Core\DynamicTags\Tag {

    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-archive-title';
    }

    public function get_title(): string {
        return esc_html__('Archive Title', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-archive'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    protected function register_controls(): void {
        $this->add_control(
            'ep_include_context',
            [
                'label' => esc_html__('Include Context', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'description' => esc_html__('Include archive context (Category:, Tag:, Author:, etc.)', 'bdthemes-element-pack'),
            ]
        );
    }

    protected function register_advanced_section() {
        $this->advanced_controls();
    }

    public function render(): void {
        $include_context = 'yes' === $this->get_settings('ep_include_context');

        if ($include_context) {
            $title = get_the_archive_title();
        } else {
            $title = post_type_archive_title('', false);
            
            if (empty($title)) {
                if (is_category() || is_tag() || is_tax()) {
                    $title = single_term_title('', false);
                } elseif (is_author()) {
                    $title = get_the_author();
                } elseif (is_date()) {
                    if (is_year()) {
                        $title = get_the_date('Y');
                    } elseif (is_month()) {
                        $title = get_the_date('F Y');
                    } else {
                        $title = get_the_date();
                    }
                } elseif (is_post_type_archive()) {
                    $title = post_type_archive_title('', false);
                }
            }
        }

        echo wp_kses_post($this->apply_word_limit($title));
    }
} 