<?php

use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_Post_Custom_Field extends \Elementor\Core\DynamicTags\Tag {
    use UtilsTrait;

    public function get_name(): string {
        return 'element-pack-post-custom-field';
    }

    public function get_title(): string {
        return esc_html__('Custom Field', 'bdthemes-element-pack');
    }

    public function get_group(): array {
        return ['element-pack-post'];
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
        ];
    }

    public function is_settings_required() {
        return true;
    }

    protected function register_controls(): void {
        $this->common_post_controls();

        $this->add_control(
            'ep_meta_key',
            [
                'label' => esc_html__('Field Key', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_custom_keys_array(),
            ]
        );

        $this->add_control(
            'ep_custom_meta_key',
            [
                'label' => esc_html__('Custom Field Key', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
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

    private function get_custom_keys_array() : array {
		$custom_keys = get_post_custom_keys();
		$options = [
			'' => esc_html__( 'Select...', 'bdthemes-element-pack' ),
		];

		if ( ! empty( $custom_keys ) ) {
			foreach ( $custom_keys as $custom_key ) {
				if ( '_' !== substr( $custom_key, 0, 1 ) ) {
					$options[ $custom_key ] = $custom_key;
				}
			}
		}

		return $options;
	}

    public function render(): void {
        $settings = $this->get_settings();
        $value = '';

        // Get post ID based on settings
        $post_id = $this->get_post_id();

        // Get the meta key
        $meta_key = '';

        if (!empty($settings['ep_meta_key'])) {
            $meta_key = $settings['ep_meta_key'];
        }elseif (!empty($settings['ep_custom_meta_key'])) {
            $meta_key = $settings['ep_custom_meta_key'];
        }
        
        // If we have both post ID and meta key, get the value
        if ($post_id && $meta_key) {
            $value = get_post_meta($post_id, $meta_key, true);
        }

        echo wp_kses_post($this->apply_word_limit($value));
    }
}
