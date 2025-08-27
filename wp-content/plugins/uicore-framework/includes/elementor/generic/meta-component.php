<?php 
namespace UiCore\Elementor\Generic;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use UiCore\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait Meta_Trait {

    function get_meta_content_controls()
    {
        
        $options = [
            'none'  => __( 'None', 'uicore-framework' ),
            'author' => __( 'Author', 'uicore-framework' ),
            'date' => __( 'Posted Date', 'uicore-framework' ),
            'updated date' => __( 'Updated Date', 'uicore-framework' ),
            'comment' => __( 'Comments Count', 'uicore-framework' ),
            'reading time' => __( 'Reading Time', 'uicore-framework' ),
            'category' => __( 'Category', 'uicore-framework' ),
            'custom meta' => __( 'Custom Meta', 'uicore-framework' ),
            'custom taxonomy' => __( 'Custom Taxonomy', 'uicore-framework' ),
        ];

        //add woocomerce meta to options if woocommerce is instaled
        $options['product price'] = __( 'Product Price', 'uicore-framework' );
        $options['product rating'] = __( 'Product Rating', 'uicore-framework' );
        // $options['product stock'] = __( 'Product Stock', 'uicore-framework' );
        $options['product category'] = __( 'Product Category', 'uicore-framework' );
        $options['product tag'] = __( 'Product Tag', 'uicore-framework' );
        $options['product attribute'] = __( 'Product Attribute', 'uicore-framework' );

        $options['portfolio category'] = __( 'Portfolio Category', 'uicore-framework' );

        $repeater = new \Elementor\Repeater();
        $repeater->add_control('type',[
                'label' => __( 'Meta', 'uicore-framework' ),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => $options
            ]
        );
            $repeater->add_control('type_custom',[
                    'label' => __( 'Custom Field Name', 'uicore-framework' ),
                    'type' => Controls_Manager::TEXT,
                    'condition' => [
                        'type' => ['custom meta','custom taxonomy']
                    ]
                ]
            );
        $repeater->add_control('before',[
                'label' => __( 'Text Before', 'uicore-framework' ),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'type!' => 'none',
                ]
            ]
        );
        $repeater->add_control('after',[
                'label' => __( 'Text After', 'uicore-framework' ),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'type!' => 'none',
                ]
            ]
        );
        $repeater->add_control('autor_display',[
                'label' => __( 'Display Type', 'uicore-framework' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name'  => __( 'Name', 'uicore-framework' ),
                    'full' => __( 'Avatar & Name', 'uicore-framework' ),
                    'avatar' => __( 'Avatar', 'uicore-framework' ),
                ],
                'condition' => [
                    'type' => 'author',
                ],
            ]
        );
        $repeater->add_control('icon',[
                'label' => __( 'Icon', 'uicore-framework' ),
                'type' => Controls_Manager::ICONS,
                'condition' => [
                    'autor_display' => 'name',
                    'type!' => 'none',

                ],
            ]
        );
        return $repeater->get_controls();
    }

    function get_meta_style_controls($position = 'tb-meta')
    {
        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => $position.'_meta_typography',
				'selector' => '{{WRAPPER}} .ui-e-'.$position,
                'separator' => 'before',
			]
		);
        $this->add_control(
			$position.'_meta_color',
			[
				'label' => __( 'Text Color', 'uicore-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ui-e-'.$position => 'color: {{VALUE}}',

				],
			]
		);
        $this->add_control(
			$position.'_link_color',
			[
				'label' => __( 'Link Color', 'uicore-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ui-e-'.$position.' .ui-e-meta-item a' => 'color: {{VALUE}}',

				],
			]
		);
        $this->add_control(
			$position.'_linkh_color',
			[
				'label' => __( 'Link Hover Color', 'uicore-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ui-e-'.$position.' .ui-e-meta-item a:hover' => 'color: {{VALUE}}',

				],
			]
		);
        $this->add_control(
			$position.'_meta_background',
			[
				'label' => __( 'Background Color', 'uicore-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ui-e-'.$position.' .ui-e-meta-item' => 'background-color: {{VALUE}}',

				],
			]
		);
        $this->add_control(
			$position.'_meta_radius',
			[
				'label'       => esc_html__('Border Radius', 'uicore-framework'),
				'type'        => Controls_Manager::DIMENSIONS,
				'selectors'   => [
					'{{WRAPPER}} .ui-e-'.$position.' .ui-e-meta-item' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;'
				],
			]
		);
        $this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => $position.'_meta_shadow',
				'label' => esc_html__( 'Box Shadow', 'uicore-framework' ),
				'selector' => '{{WRAPPER}} .ui-e-'.$position.' .ui-e-meta-item',
			]
		);
		$this->add_responsive_control(
			$position.'_meta_padding',
			[
				'label'      => esc_html__('Padding', 'uicore-framework'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ui-e-'.$position.' .ui-e-meta-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);
        if($position === 'top'){
            $this->add_responsive_control(
                $position.'_meta_margin',
                [
                    'label'      => esc_html__('Margin', 'uicore-framework'),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .ui-e-'.$position => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                    ]
                ]
            );
        }else{
            $this->add_control($position.'_meta_margin',[
                'label' => __( 'Meta Top Space', 'uicore-framework' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'em' ],
                'separator' => 'after',
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'em',
                    'size' => 1.2,
                ],
                'selectors' => [
                    '{{WRAPPER}}  .ui-e-'.$position => 'margin-top: {{SIZE}}em;',
                ],
            ]
        );
        }

        $this->add_responsive_control($position.'_meta_gap',[
                'label' => __( 'Items Gap', 'uicore-framework' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 30,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-'.$position.' ' => 'gap: {{SIZE}}px;',
                ],
            ]
        );
        $this->add_control($position.'_meta_separator',[
                'label' => __( 'Separator', 'uicore-framework' ),
                'type' => Controls_Manager::TEXT,
            ]
        );
        if($position === 'top'){
            $this->add_control($position.'_meta_placement',[
                'label' => __( 'Items placement', 'uicore-framework' ),
                'type' => Controls_Manager::SELECT,
                'default' => '',
				'options' => [
					'start left'  => __( 'Top Left', 'uicore-framework' ),
					'start right' => __( 'Top Right', 'uicore-framework' ),
					'end left' => __( 'Bottom Left', 'uicore-framework' ),
					'end right' => __( 'Bottom Right', 'uicore-framework' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-post-top-meta' => 'place-content: {{VALUE}};',
                ],
                ]
            );
        }
    }

    function get_meta_the_author($mode){
        global $post;
        $author_id = $post->post_author;

        // name, full, avatar
        if($mode === 'avatar'){
            $display = '<img class="ui-e-meta-avatar" src="' . esc_url( get_avatar_url($author_id, array('size' => 100)) ) . '" />';
        }elseif($mode === 'full'){
            $display = '<img class="ui-e-meta-avatar" src="' . esc_url( get_avatar_url($author_id, array('size' => 100)) ) . '" /> '.get_the_author_meta('display_name', $author_id);
        }else{
            $display = \get_the_author_meta('display_name', $author_id);
        }
        $link = sprintf(
            '<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
            esc_url( get_author_posts_url( $author_id) ),
            /* translators: %s: Author's display name. */
            esc_attr( sprintf( __( 'View %s&#8217;s posts', 'uicore-framework' ), $display ) ),
            $display
        );
        return $link;
    }

    function get_woo_meta($type) {
        global $product;
    
        if (empty($product)) {
            return 'No product found.';
        }
    
        switch ($type) {
            case 'product price':
                return $product->get_price_html();
            case 'product rating':
                return $product->get_rating_html();
            case 'product category':
                return get_the_term_list($product->get_id(), 'product_cat', '', ', ', '');
            case 'product tag':
                return get_the_term_list($product->get_id(), 'product_tag', '', ', ', '');
            case 'product attribute':
                return wc_display_product_attributes($product);
            default:
                return 'Invalid meta type.';
        }
    }
    

    function display_meta($meta){

        if($meta['type'] === 'none')
            return;

        \Elementor\Icons_Manager::render_icon( $meta['icon'], [ 'aria-hidden' => 'true', 'class'=>'ui-e-meta-icon' ],'span' );

        if($meta['before'])
            echo '<span>'.esc_html($meta['before']).'</span>';

        $type = $meta['type'];
        if($type === 'author'){
            echo $this->get_meta_the_author($meta['autor_display']);
        }elseif($type === 'date'){
            echo get_the_date();
        }elseif($type === 'updated date'){
            echo get_the_modified_date();
        }elseif($type === 'category'){
            echo Helper::get_taxonomy('category');
        }elseif($type === 'portfolio category'){
            echo Helper::get_taxonomy('portfolio_category');
        }elseif($type === 'comment'){
            echo get_comments_number();
        }elseif($type === 'custom meta'){
            echo get_post_meta( get_the_ID(), $meta['type_custom'], true );
        }elseif($type === 'custom taxonomy'){
            echo Helper::get_taxonomy($meta['type_custom']);
        }elseif($type === 'reading time'){
            echo Helper::get_reading_time();
        }else if(strpos($type, 'product') === 0){
            echo $this->get_woo_meta($type);
        }else{
            echo $type;
        }

        if($meta['after'])
        echo '<span class="ui-e-meta-after">'.esc_html($meta['after']).'</span>';
    }
}