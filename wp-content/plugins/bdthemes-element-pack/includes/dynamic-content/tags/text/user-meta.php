<?php
use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_User_Meta extends \Elementor\Core\DynamicTags\Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-user-meta';
    }

    public function get_title(): string {
        return esc_html__('User Meta', 'bdthemes-element-pack');
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
            'ep_user_meta_key',
            [
                'label' => esc_html__('Meta Key', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
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

        $user_meta_key = $this->get_settings_for_display('ep_user_meta_key');
        if (empty($user_meta_key)) return;

        echo wp_kses_post(get_user_meta($user_id, $user_meta_key, true));
    }
} 