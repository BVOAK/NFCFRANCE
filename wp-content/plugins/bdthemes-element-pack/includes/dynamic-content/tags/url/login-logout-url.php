<?php

use ElementPack\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Login_Logout_URL extends Data_Tag
{
    use UtilsTrait;

    public function get_name(): string
    {
        return 'element-pack-login-logout-url';
    }

    public function get_title(): string
    {
        return esc_html__('Login/Logout URL', 'bdthemes-element-pack');
    }

    public function get_group(): array
    {
        return ['element-pack-user'];
    }

    public function get_categories(): array
    {
        return [
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
        ];
    }

    public function is_settings_required()
    {
        return false;
    }

    protected function register_controls(): void
    {
        $this->add_control(
            'ep_url_type',
            [
                'label' => esc_html__('URL Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'login' => esc_html__('Login', 'bdthemes-element-pack'),
                    'logout' => esc_html__('Logout', 'bdthemes-element-pack'),
                    'register' => esc_html__('Register', 'bdthemes-element-pack'),
                    'lost_password' => esc_html__('Lost Password', 'bdthemes-element-pack'),
                ],
                'default' => 'login',
            ]
        );

        $this->add_control(
            'ep_redirect_url',
            [
                'label' => esc_html__('Redirect URL', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__('Enter redirect URL', 'bdthemes-element-pack'),
                'description' => esc_html__('Where to redirect after login/logout. Leave empty for default behavior.', 'bdthemes-element-pack'),
                
            ]
        );

        $this->fallback_control();
    }

    protected function register_advanced_section(): void {}

    public function get_value(array $options = [])
    {
        $url_type = $this->get_settings('ep_url_type');
        $redirect_url = $this->get_settings('ep_redirect_url');
        $url = '';

        switch ($url_type) {
            case 'login':
                $url = wp_login_url($redirect_url);
                break;
            case 'logout':
                $url = wp_logout_url($redirect_url);
                break;
            case 'register':
                $url = wp_registration_url();
                break;
            case 'lost_password':
                $url = wp_lostpassword_url($redirect_url);
                break;
        }

        return $url;
    }
} 