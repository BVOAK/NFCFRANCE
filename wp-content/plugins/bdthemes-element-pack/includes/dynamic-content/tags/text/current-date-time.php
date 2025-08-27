<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Current_Date_Time extends \Elementor\Core\DynamicTags\Tag {

    public function get_name(): string {
        return 'element-pack-current-date-time';
    }

    public function get_title(): string {
        return esc_html__('Current Date Time', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-site'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    protected function register_controls(): void {
        $this->add_control(
            'ep_format',
            [
                'label' => esc_html__('Format', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'default' => esc_html__('Default', 'bdthemes-element-pack'),
                    // Date only
                    'F j, Y' => date_i18n('F j, Y'),
                    'Y-m-d' => date_i18n('Y-m-d'),
                    'm/d/Y' => date_i18n('m/d/Y'),
                    'd-m-Y' => date_i18n('d-m-Y'),
                    'D, M j, Y' => date_i18n('D, M j, Y'),
                    'l, F j, Y' => date_i18n('l, F j, Y'),
                    'Y/m/d' => date_i18n('Y/m/d'),
                    // Time only
                    'g:i a' => date_i18n('g:i a'),
                    'g:i A' => date_i18n('g:i A'),
                    'H:i' => date_i18n('H:i'),
                    'h:i:s A' => date_i18n('h:i:s A'),
                    'H:i:s' => date_i18n('H:i:s'),
                    'g:i:s a' => date_i18n('g:i:s a'),
                    // Combined
                    'F j, Y g:i a' => date_i18n('F j, Y g:i a'),
                    'Y-m-d H:i' => date_i18n('Y-m-d H:i'),
                    'd/m/Y H:i' => date_i18n('d/m/Y H:i'),
                    'M j, Y @ H:i' => date_i18n('M j, Y @ H:i'),
                    'l, F j, Y g:i A' => date_i18n('l, F j, Y g:i A'),
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
                'default' => 'F j, Y g:i a',
                'condition' => [
                    'ep_format' => 'custom',
                ],
                'description' => sprintf(
                    '<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">%s</a>',
                    esc_html__('Documentation on date and time formatting', 'bdthemes-element-pack')
                ),
            ]
        );
    }

    public function render(): void {
        $settings = $this->get_settings();

        if ('default' === $settings['ep_format']) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        } elseif ('custom' === $settings['ep_format']) {
            $format = $settings['ep_custom_format'];
        } else {
            $format = $settings['ep_format'];
        }

        echo wp_kses_post(date_i18n($format));
    }
} 