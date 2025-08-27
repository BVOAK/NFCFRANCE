<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Request_Parameter extends \Elementor\Core\DynamicTags\Tag {

    public function get_name(): string {
        return 'element-pack-request-parameter';
    }

    public function get_title(): string {
        return esc_html__('Request Parameter', 'bdthemes-element-pack');
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
            'ep_request_type',
            [
                'label' => esc_html__('Request Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'get' => esc_html__('GET', 'bdthemes-element-pack'),
                    'post' => esc_html__('POST', 'bdthemes-element-pack'),
                    'query_var' => esc_html__('Query Var', 'bdthemes-element-pack'),
                ],
                'default' => 'get',
            ]
        );

        $this->add_control(
            'ep_param_name',
            [
                'label' => esc_html__('Parameter Name', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__('Enter the parameter name to get its value', 'bdthemes-element-pack'),
                'label_block' => true,
                'condition' => [
                    'ep_request_type!' => '',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );
    }

    public function render(): void {
        $settings = $this->get_settings();
        $param_name = $settings['ep_param_name'];
        $type = isset($settings['ep_request_type']) ? $settings['ep_request_type'] : 'get';

        if (empty($param_name)) {
            return;
        }

        $value = '';
        if ($type === 'get') {
            if (isset($_GET[$param_name])) {
                $value = sanitize_text_field($_GET[$param_name]);
            }
        } elseif ($type === 'post') {
            if (isset($_POST[$param_name])) {
                $value = sanitize_text_field($_POST[$param_name]);
            }
        } elseif ($type === 'query_var') {
            $query_var_value = get_query_var($param_name, '');
    
            if (!empty($query_var_value)) {
                $value = sanitize_text_field($query_var_value);
            }
        }
        echo wp_kses_post($value);
    }
} 