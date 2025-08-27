<?php

namespace ElementPack\Includes\Traits;

trait UtilsTrait
{

    protected function advanced_controls() {
        $tag_name = $this->get_name();
    
        $this->start_controls_section(
            'advanced',
            [
                'label' => esc_html__('Advanced', 'bdthemes-element-pack'),
            ]
        );
    
        // Dynamic hook: before core controls
        do_action("element_pack/advanced_section/{$tag_name}/before", $this);
        
        $this->add_control(
            'ep_word_limit',
            [
                'label' => esc_html__('Word Limit', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => esc_html__('0 means no limit', 'bdthemes-element-pack'),
            ]
        );
    
        $this->add_control('before', [
            'label' => esc_html__('Before', 'bdthemes-element-pack'),
            'ai' => [
                'active' => false,
            ],
        ]);
    
        $this->add_control('after', [
            'label' => esc_html__('After', 'bdthemes-element-pack'),
            'ai' => [
                'active' => false,
            ],
        ]);
    
        $this->add_control('fallback', [
            'label' => esc_html__('Fallback', 'bdthemes-element-pack'),
            'ai' => [
                'active' => false,
            ],
        ]);
    
        // Dynamic hook: after core controls
        do_action("element_pack/advanced_section/{$tag_name}/after", $this);
    
        $this->end_controls_section();
    }


    protected function apply_word_limit($text) {
        $settings = $this->get_settings();
        $limit = isset($settings['ep_word_limit']) ? $settings['ep_word_limit'] : 0;
        if ($limit > 0) {
            $limited = wp_trim_words( $text, $limit, '…' );
            return $limited;
        }
        return $text;
    }

    protected function common_product_controls() {
        $this->add_control(
            'ep_product_id',
            [
                'label' => esc_html__('Search & Select Product', 'bdthemes-element-pack'),
                'type' => \ElementPack\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
                'multiple' => false,
                'label_block' => true,
                'description' => esc_html__('Leave blank to use current product', 'bdthemes-element-pack'),
                'query_args' => [
                    'query' => 'posts',
                    'post_type' => 'product',
                ],
            ]
        );
    }
    protected function common_post_controls() {
        $this->add_control(
            'ep_post_type',
            [
                'label' => esc_html__('Post Type', 'bdthemes-element-pack'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'current' => esc_html__('Current Post', 'bdthemes-element-pack'),
                    'selected' => esc_html__('Selected Post', 'bdthemes-element-pack'),
                ],
                'default' => 'current',
            ]
        );

        $this->add_control(
            'ep_posts_selected_id',
            [
                'label' => esc_html__('Search & Select Post', 'bdthemes-element-pack'),
                'type' => \ElementPack\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
                'multiple' => false,
                'label_block' => true,
                'query_args' => [
                    'query' => 'posts',
                ],
                'condition' => [
                    'ep_post_type' => 'selected',
                ],
            ]
        );
    }

    protected function common_term_controls() {
        $this->add_control(
            'ep_selected_term_id',
            [
                'label' => esc_html__('Search & Select Term', 'bdthemes-element-pack'),
                'type' => \ElementPack\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
                'multiple' => false,
                'label_block' => true,
                'query_args' => [
                    'query' => 'terms',
                    'post_type' => '_related_post_type',
                ],
            ]
        );
    }

    protected function common_user_controls() {
        $this->add_control(
            'ep_selected_user_id',
            [
                'label' => esc_html__('Search & Select User', 'bdthemes-element-pack'),
                'type' => \ElementPack\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
                'multiple' => false,
                'label_block' => true,
                'query_args' => [
                    'query' => 'authors',
                ],
            ]
        );
    }

    protected function fallback_control() {
        $this->add_control(
			'fallback',
			[
				'label' => esc_html__( 'Fallback', 'bdthemes-element-pack' ),
				'ai' => [
					'active' => false,
				],
			]
		);
    }

    protected function get_post_id() {
        $settings = $this->get_settings();
        if (!empty($settings['ep_post_type']) && $settings['ep_post_type'] === 'selected' && !empty($settings['ep_posts_selected_id'])) {
            return $settings['ep_posts_selected_id'];
        }
        return get_the_ID();
    }

    protected function get_term_id() {
        $settings = $this->get_settings();
        
        if (empty($settings['ep_selected_term_id'])) return;

        return $settings['ep_selected_term_id'];
    }

    protected function get_user_id() {
        $user_id = $this->get_settings('ep_selected_user_id');
        
        if (empty($user_id)) return get_current_user_id();

        return $user_id;
    }

    protected function get_product_id(): ?int {
        $settings = $this->get_settings();
        $product_id = $settings['ep_product_id'] ?? null;

        if (!$product_id) {
            $product_id = get_the_ID();
        }

        if (!$product_id || get_post_type($product_id) !== 'product') {
            return null;
        }

        return (int) $product_id;
    }
}
