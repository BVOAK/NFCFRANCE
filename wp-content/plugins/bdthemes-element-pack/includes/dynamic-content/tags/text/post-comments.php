<?php

use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;
use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Post_Comments extends \Elementor\Core\DynamicTags\Tag {

    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-post-comments';
    }

    public function get_title(): string {
        return esc_html__('Post Comments', 'bdthemes-element-pack');
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

        $this->add_control(
            'ep_comments_data_type',
            [
                'label' => esc_html__('Data Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'count' => esc_html__('Comments Count', 'bdthemes-element-pack'),
                    'number' => esc_html__('Comments Number', 'bdthemes-element-pack'),
                ],
                'default' => 'count',
            ]
        );

        $this->add_control(
            'ep_comments_no_text',
            [
                'label' => esc_html__('No Comments Text', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('No Comments', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_comments_data_type' => 'number',
                ],
                'label_block' => true,
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'ep_comments_single_text',
            [
                'label' => esc_html__('Single Comment Text', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('1 Comment', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_comments_data_type' => 'number',
                ],
                'label_block' => true,
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'ep_comments_multi_text',
            [
                'label' => esc_html__('Multiple Comments Text', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('{{number}} Comments', 'bdthemes-element-pack'),
                'condition' => [
                    'ep_comments_data_type' => 'number',
                ],
                'description' => esc_html__('Use {{number}} as a placeholder for the comments count.', 'bdthemes-element-pack'),
                'label_block' => true,
                'ai' => [
                    'active' => false,
                ],
            ]
        );
    }

    public function render(): void {
        $settings = $this->get_settings();

        $post_id = $this->get_post_id();

        if (!$post_id) {
            return;
        }

        $comments_count = get_comments_number($post_id);
        $output = '';

        if ($settings['ep_comments_data_type'] === 'count') {
            $output = $comments_count;
        } else {
            if ($comments_count == 0) {
                $output = $settings['ep_comments_no_text'];
            } elseif ($comments_count == 1) {
                $output = $settings['ep_comments_single_text'];
            } else {
                $output = str_replace('{{number}}', $comments_count, $settings['ep_comments_multi_text']);
            }
        }

        echo wp_kses_post($output);
    }
} 