<?php
use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_User_Info extends \Elementor\Core\DynamicTags\Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-user-info';
    }

    public function get_title(): string {
        return esc_html__('User Info', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-user'];
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
        $this->common_user_controls();

        $this->add_control(
            'user_info_type',
            [
                'label' => esc_html__('User Info Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'id' => esc_html__('ID', 'bdthemes-element-pack'),
                    'name' => esc_html__('Name', 'bdthemes-element-pack'),
                    'email' => esc_html__('Email', 'bdthemes-element-pack'),
                    'website' => esc_html__('Website', 'bdthemes-element-pack'),
                    'display_name' => esc_html__('Display Name', 'bdthemes-element-pack'),
                    'nickname' => esc_html__('Nickname', 'bdthemes-element-pack'),
                    'first_name' => esc_html__('First Name', 'bdthemes-element-pack'),
                    'last_name' => esc_html__('Last Name', 'bdthemes-element-pack'),
                    'description' => esc_html__('Description', 'bdthemes-element-pack'),
                    'role' => esc_html__('Role', 'bdthemes-element-pack'),
                ],
                'default' => 'name',
            ]
        );
    }

    protected function register_advanced_section()
    {
        $this->advanced_controls();
    }

    public function render(): void {
        $user_id = $this->get_user_id();
        if (empty($user_id)) return;

        $user = get_user_by('id', $user_id);
        if (empty($user)) return;

        $user_info_type = $this->get_settings_for_display('user_info_type');
        switch ($user_info_type) {
            case 'id':
                echo esc_html($user_id);
                break;
            case 'name':
                echo esc_html($user->display_name);
                break;
            case 'email':
                echo esc_html($user->user_email);
                break;
            case 'website':
                echo esc_html($user->user_url);
                break;
            case 'display_name':
                echo esc_html($user->display_name);
                break;
            case 'nickname':
                echo esc_html($user->nickname);
                break;
            case 'first_name':
                echo esc_html($user->first_name);
                break;
            case 'last_name':
                echo esc_html($user->last_name);
                break;
            case 'description':
                echo esc_html($user->description);
                break;
            case 'role':
                echo esc_html($user->roles[0]);
                break;
            default:
                echo esc_html($user->display_name);
                break;
        }
    }
} 