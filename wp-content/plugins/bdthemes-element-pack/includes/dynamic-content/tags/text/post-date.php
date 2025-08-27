<?php
use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Post_Date extends \Elementor\Core\DynamicTags\Tag {

    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-post-date';
    }

    public function get_title(): string {
        return esc_html__('Post Date', 'bdthemes-element-pack');
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
            'ep_date_type',
            [
                'label' => esc_html__('Date Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'post_date' => esc_html__('Post Date', 'bdthemes-element-pack'),
                    'post_modified' => esc_html__('Post Modified Date', 'bdthemes-element-pack'),
                ],
                'default' => 'post_date',
            ]
        );

        $this->add_control(
            'ep_format_type',
            [
                'label' => esc_html__('Format', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'default' => esc_html__('Default', 'bdthemes-element-pack'),
                    'F j, Y' => date_i18n('F j, Y'),   // April 30, 2025
                    'Y-m-d' => date_i18n('Y-m-d'),     // 2025-04-30
                    'm/d/Y' => date_i18n('m/d/Y'),     // 04/30/2025
                    'd/m/Y' => date_i18n('d/m/Y'),     // 30/04/2025
                    'human' => esc_html__('Human Readable', 'bdthemes-element-pack'),
                    'custom' => esc_html__('Custom', 'bdthemes-element-pack'),
                ],
                'default' => 'default',
            ]
        );

        $this->add_control(
            'ep_custom_format',
            [
                'label' => esc_html__('Custom Format', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'F j, Y',
                'condition' => [
                    'ep_format_type' => 'custom',
                ],
                'description' => sprintf(
                    '<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">%s</a>',
                    esc_html__('Documentation on date and time formatting', 'bdthemes-element-pack')
                ),
                'ai' => [
					'active' => false,
				],
            ]
        );
    }

    protected function register_advanced_section()
    {
        $this->advanced_controls();
    }

    public function render(): void {
        $settings = $this->get_settings();

        $post_id = $this->get_post_id();

        if (!$post_id) return;

        $post = get_post($post_id);
        
        if (!$post) return;

        $date = $settings['ep_date_type'] === 'post_modified' ? $post->post_modified : $post->post_date;
        $timestamp = strtotime($date);

        if ($settings['ep_format_type'] === 'human') {
            $value = human_time_diff($timestamp, current_time('timestamp'));
        } elseif ($settings['ep_format_type'] === 'default') {
            $value = date_i18n(get_option('date_format'), $timestamp);
        } elseif ($settings['ep_format_type'] === 'custom') {
            $format = $settings['ep_custom_format'];
            $value = date_i18n($format, $timestamp);
        } else {
            $value = date_i18n($settings['ep_format_type'], $timestamp);
        }

        echo wp_kses_post($this->apply_word_limit($value));
    }
} 