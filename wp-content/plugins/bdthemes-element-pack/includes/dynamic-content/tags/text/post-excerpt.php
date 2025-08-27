<?php

use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Post_Excerpt extends \Elementor\Core\DynamicTags\Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-post-excerpt';
    }

    public function get_title(): string {
        return esc_html__('Post Excerpt', 'bdthemes-element-pack');
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
            'ep_excerpt_length',
            [
                'label' => esc_html__('Excerpt Length', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 55,
                'min' => 1,
                'max' => 1000,
            ]
        );

        $this->add_control(
            'ep_excerpt_end_text',
            [
                'label' => esc_html__('End Text', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '[Read More]',
                'label_block' => true,
                'ai' => [
                    'active' => false,
                ]
            ]
        );

        $this->add_control(
            'ep_end_text_linked',
            [
                'label' => esc_html__('Link End Text', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => esc_html__('Yes', 'bdthemes-element-pack'),
                'label_off' => esc_html__('No', 'bdthemes-element-pack'),
            ]
        );
    }

    public function render(): void {
        $settings = $this->get_settings();

        $post_id = $this->get_post_id();

        if (!$post_id) return;

        $post = get_post($post_id);
        
        if (!$post) return;

        $excerpt = '';

        // First try to get manual excerpt
        if (has_excerpt($post_id)) {
            $excerpt = get_the_excerpt($post_id);
        } else {
            // Fallback to automatic excerpt from content
            $excerpt = $post->post_content;
            $excerpt = strip_tags(strip_shortcodes($excerpt));
        }

        $excerpt_length = empty($settings['ep_excerpt_length']) ? 55 : intval($settings['ep_excerpt_length']);
        $end_text = !empty($settings['ep_excerpt_end_text']) ? $settings['ep_excerpt_end_text'] : '';
        
        $trimmed_excerpt = wp_trim_words($excerpt, $excerpt_length, '');
        $is_trimmed = str_word_count($excerpt) > $excerpt_length;

        if (!empty($trimmed_excerpt)) {
            if ($is_trimmed && $end_text) {
                if ('yes' === $settings['ep_end_text_linked']) {
                    $trimmed_excerpt .= sprintf(' <a href="%s" class="ep-excerpt-end-text">%s</a>', get_permalink($post_id), $end_text);
                } else {
                    $trimmed_excerpt .= $end_text;
                }
            }
            echo wp_kses_post($trimmed_excerpt);
        }
    }
} 