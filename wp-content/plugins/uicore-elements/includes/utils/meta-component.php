<?php
namespace UiCoreElements\Utils;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use UiCoreElements\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait Meta_Trait {

    function get_meta_content_controls($product_metas = false)
    {

        // Meta options
        $options = [
            'none' => __( 'None', 'uicore-elements' ),
        ];
        $generic_posts = [
            'author'       => __( 'Author', 'uicore-elements' ),
            'date'         => __( 'Posted Date', 'uicore-elements' ),
            'updated date' => __( 'Updated Date', 'uicore-elements' ),
            'comment'      => __( 'Comments Count', 'uicore-elements' ),
            'reading time' => __( 'Reading Time', 'uicore-elements' ),
            'category'     => __( 'Category', 'uicore-elements' ),
            'tag'          => __( 'Tag', 'uicore-elements' ),
        ];
        $products = [
            'product price'     => __( 'Product Price', 'uicore-elements' ),
            'product rating'    => __( 'Product Rating', 'uicore-elements' ),
            'product stock'     => __( 'Product Stock', 'uicore-elements' ),
            'product category'  => __( 'Product Category', 'uicore-elements' ),
            'product tag'       => __( 'Product Tag', 'uicore-elements' ),
            'product attribute' => __( 'Product Attribute', 'uicore-elements' ),
            'product sale'      => __( 'Product Sale', 'uicore-elements' ),
        ];
        $custom = [
            'custom meta'       => __( 'Custom Meta', 'uicore-elements' ),
            'custom taxonomy'   => __( 'Custom Taxonomy', 'uicore-elements' ),
            'custom html'       => __( 'Custom HTML', 'uicore-elements' ),
        ];
        $portfolio = [
            'portfolio category' => __( 'Portfolio Category', 'uicore-elements' ),
        ];

        $options = $product_metas ?
            array_merge($options, $products, $custom) : // specific to product widgets
            array_merge($options, $generic_posts, $products, $portfolio, $custom); // generic posts widgets


        $repeater = new \Elementor\Repeater();
        $repeater->add_control('type',[
                'label' => __( 'Meta', 'uicore-elements' ),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => $options
            ]
        );
            $repeater->add_control('type_custom',[
                    'label' => __( 'Custom Field Name', 'uicore-elements' ),
                    'type' => Controls_Manager::TEXT,
                    'condition' => [
                        'type' => ['custom meta','custom taxonomy']
                    ]
                ]
            );
            $repeater->add_control('html_custom',[
                    'label' => __( 'Custom HTML', 'uicore-elements' ),
                    'type' => Controls_Manager::TEXTAREA,
                    'condition' => [
                        'type' => ['custom html']
                    ]
                ]
            );
            $repeater->add_control('date_format',[
                    'label' => esc_html__( 'Date Format', 'uicore-elements' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'default',
                    'options' => [
                        'default'   => esc_html__( 'Default', 'uicore-elements' ),
                        'F j, Y'    => gmdate( 'F j, Y' ),
                        'Y-m-d'     => gmdate( 'Y-m-d' ),
                        'm/d/Y'     => gmdate( 'm/d/Y' ),
                        'd/m/Y'     => gmdate( 'd/m/Y' ),
                        'custom'    => esc_html__( 'Custom', 'uicore-elements' ),
                    ],
                    'condition' => [
                        'type' => ['date','updated date']
                    ]
                ]
            );
            $repeater->add_control('custom_format',[
                    'label'     => esc_html__( 'Custom Format', 'uicore-elements' ),
                    'default'   => get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
                    'description' => sprintf( '<a href="https://wordpress.org/documentation/article/customize-date-and-time-format/" target="_blank">%s</a>', esc_html__( 'Documentation on date and time formatting', 'uicore-elements' ) ),
                    'condition' => [
                        'date_format' => 'custom',
                        'type'        => ['date','updated date']
                    ],
                ]
            );
        $repeater->add_control('before',[
                'label' => __( 'Text Before', 'uicore-elements' ),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'type!' => 'none',
                ]
            ]
        );
        $repeater->add_control('after',[
                'label' => __( 'Text After', 'uicore-elements' ),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'type!' => 'none',
                ]
            ]
        );
        $repeater->add_control('autor_display',[
                'label' => __( 'Display Type', 'uicore-elements' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name'  => __( 'Name', 'uicore-elements' ),
                    'full' => __( 'Avatar & Name', 'uicore-elements' ),
                    'avatar' => __( 'Avatar', 'uicore-elements' ),
                ],
                'condition' => [
                    'type' => 'author',
                ],
            ]
        );
        $repeater->add_control('icon',[
                'label' => __( 'Icon', 'uicore-elements' ),
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
				'label' => __( 'Text Color', 'uicore-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ui-e-'.$position => 'color: {{VALUE}}',
                    '{{WRAPPER}} .ui-e-'.$position.' svg' => 'fill: {{VALUE}}',
				],
			]
		);
        $this->add_control(
			$position.'_link_color',
			[
				'label' => __( 'Link Color', 'uicore-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ui-e-'.$position.' .ui-e-meta-item a' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .ui-e-'.$position.' .ui-e-meta-item svg' => 'fill: {{VALUE}}',
				],
			]
		);
        $this->add_control(
			$position.'_linkh_color',
			[
				'label' => __( 'Link Hover Color', 'uicore-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ui-e-'.$position.' .ui-e-meta-item a:hover' => 'color: {{VALUE}}',
				],
			]
		);
        $this->add_control(
			$position.'_meta_background',
			[
				'label' => __( 'Background Color', 'uicore-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ui-e-'.$position.' .ui-e-meta-item' => 'background-color: {{VALUE}}',

				],
			]
		);
        $this->add_control(
			$position.'_meta_radius',
			[
				'label'       => esc_html__('Border Radius', 'uicore-elements'),
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
				'label' => esc_html__( 'Box Shadow', 'uicore-elements' ),
				'selector' => '{{WRAPPER}} .ui-e-'.$position.' .ui-e-meta-item',
			]
		);
		$this->add_responsive_control(
			$position.'_meta_padding',
			[
				'label'      => esc_html__('Padding', 'uicore-elements'),
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
                    'label'      => esc_html__('Margin', 'uicore-elements'),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .ui-e-'.$position => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                    ]
                ]
            );
        }else{
            $this->add_control($position.'_meta_margin',[
                'label' => __( 'Meta Top Space', 'uicore-elements' ),
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
                'label' => __( 'Items Gap', 'uicore-elements' ),
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
                'label' => __( 'Separator', 'uicore-elements' ),
                'type' => Controls_Manager::TEXT,
            ]
        );
        if($position === 'top'){
            $this->add_control($position.'_meta_placement',[
                'label' => __( 'Items placement', 'uicore-elements' ),
                'type' => Controls_Manager::SELECT,
                'default' => '',
				'options' => [
					'start left'  => __( 'Top Left', 'uicore-elements' ),
					'start right' => __( 'Top Right', 'uicore-elements' ),
					'end left' => __( 'Bottom Left', 'uicore-elements' ),
					'end right' => __( 'Bottom Right', 'uicore-elements' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-post-top-meta' => 'place-content: {{VALUE}};',
                ],
                ]
            );
        }
    }

    function get_meta_the_author($mode)
    {
        global $post;
        $author_id = $post->post_author;
        $author_name = get_the_author_meta('display_name', $author_id);

        // name, full, avatar
        if($mode === 'avatar'){
            $display = '<img class="ui-e-meta-avatar" src="' . esc_url( get_avatar_url($author_id, array('size' => 100)) ) . '" />';
        }elseif($mode === 'full'){
            $display = '<img class="ui-e-meta-avatar" src="' . esc_url( get_avatar_url($author_id, array('size' => 100)) ) . '" /> '. esc_html($author_name);
        }else{
            $display = esc_html($author_name);
        }
        $link = sprintf(
            /* translators: 1: Author archive url, 2: Author meta title, 3: Author display name */
            '<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
            esc_url( get_author_posts_url( $author_id) ),
            /* translators: %s: Author's display name. */
            esc_attr( sprintf( __( 'View %s&#8217;s posts', 'uicore-elements' ), esc_html($author_name) ) ),
            $display
        );
        return $link;
    }

    function get_woo_meta($type)
    {
        global $product;

        if (empty($product)) {
            return 'No product found.';
        }

        switch ($type) {
            case 'product price':
                return $product->get_price_html();
            case 'product rating':
                return  wc_get_rating_html( $product->get_average_rating() );
            case 'product category':
                return get_the_term_list($product->get_id(), 'product_cat', '', ', ', '');
            case 'product tag':
                return get_the_term_list($product->get_id(), 'product_tag', '', ', ', '');
            case 'product stock' :
                return ( $product->managing_stock() && $product->is_type('simple') && $product->get_stock_quantity() !== null ) ? $product->get_stock_quantity() : '';
            case 'product attribute':
                return wc_display_product_attributes($product);
            case 'product sale' :
                return $product->is_on_sale() ? esc_html__( 'Sale!', 'uicore-elements' ) : '';
            default:
                return 'Invalid meta type.';
        }
    }

    function display_meta($meta)
    {

        if( $meta['type'] === 'none' )
            return;

        $content = '';
        $wrapper = '<div class="ui-e-meta-item">';
        $type    = $meta['type'];
        $prefix  = $meta['before'] ? '<span>'.esc_html($meta['before']).'</span>' : '';
        $suffix  = $meta['after'] ? '<span class="ui-e-meta-after">'.esc_html($meta['after']).'</span>' : '';

        ob_start();
        \Elementor\Icons_Manager::render_icon( $meta['icon'], [ 'aria-hidden' => 'true', 'class'=> 'ui-e-meta-icon' ], 'span' );
        $icon = ob_get_clean();

        // Build content
        switch ($type) {
            case 'author':
                $content .= $this->get_meta_the_author($meta['autor_display']);  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                break;
            case 'date':
                $content .= Helper::format_date( get_the_date('U'), $meta['date_format'], $meta['custom_format']); // 'U' param returns the date in timestamp, necessary for format_date()
                break;
            case 'updated date':
                $content .= Helper::format_date( get_the_modified_date('U'), $meta['date_format'], $meta['custom_format']); // 'U' param returns the date in timestamp, necessary for format_date()
                break;
            case 'category':
                $content .= Helper::get_taxonomy('category');
                break;
            case 'tag':
                $content .= Helper::get_taxonomy('post_tag');
                break;
            case 'portfolio category':
                $content .= Helper::get_taxonomy('portfolio_category');
                break;
            case 'comment':
                $content .= esc_html( get_comments_number() );
                break;
            case 'custom meta':
                $content .= Helper::get_custom_meta($meta['type_custom']);
                break;
            case 'custom taxonomy':
                $content .= Helper::get_taxonomy($meta['type_custom']);
                break;
            case 'custom html':
                $content .= wp_kses_post($meta['html_custom']);
                break;
            case 'reading time':
                $content .= esc_html( Helper::get_reading_time() );
                break;
            default:
                if (strpos($type, 'product') === 0) {
                    if ($this->get_woo_meta($type) === false) return; // Abort if there's no data for this product meta
                    $content .= wp_kses_post($this->get_woo_meta($type));
                } else {
                    echo \esc_html($type);
                }
                break;
        }

        // Only output if there's content
        if (!empty($content)) {
            echo $wrapper . Helper::esc_svg($icon) . $prefix . $content . $suffix . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }
}