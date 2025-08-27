<?php
namespace UiCore\ThemeBuilder\Widgets;

use Uicore\Elementor\TB_Widget_Base;
use UiCore\Elementor\Generic\Query_Trait;

use Uicore\Helper;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

defined('ABSPATH') || exit();

/**
 * Product Grid Widget
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */

class Product_Grid extends TB_Widget_Base {

    use Query_Trait;

    private $_query;

	public function get_name() {
		return 'uicore-woo-product-grid';
	}
	public function get_title() {
		return esc_html__( 'Product Grid', 'uicore-framework' );
	}
	public function get_icon() {
		return 'eicon-product-related ui-e-widget';
	}
	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'related', 'product', 'similar', 'grid' ];
	}
    public function get_categories() {
		return ['uicore-woo', 'uicore-theme-builder'];
	}
    public function get_styles() {
        return [
            'single-product' => [
                'deps' => [
                    'woocommerce-general'
                ]
            ]
        ];
	}
    public function get_scripts() {
        return [];
    }

    protected function register_controls() {
        if( $this->no_woo_fallback(true) ){
            return;
        }

        $default_columns = Helper::get_option('blog_col', 3);

		$this->start_controls_section(
            'section_products_content',
            [
                'label' => esc_html__( 'Products', 'uicore-framework' ),
            ]
        );

            $this->register_post_list('product');
            $this->add_responsive_control(
                'columns',
                [
                    'label' => esc_html__( 'Columns', 'uicore-framework' ),
                    'type' => Controls_Manager::NUMBER,
                    'min' => 1,
                    'max' => 12,
                    'default' => $default_columns,
                    'tablet_default' => '2',
                    'mobile_default' => '1',
                    'required' => true,
                    'selectors' => [
                        '{{WRAPPER}} ul.products' => 'grid-template-columns: repeat({{VALUE}}, 1fr)',
                    ],
                ]
            );
            $this->add_control(
                'orderby',
                [
                    'label' => esc_html__( 'Order By', 'uicore-framework' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'date',
                    'options' => [
                        'date' => esc_html__( 'Date', 'uicore-framework' ),
                        'title' => esc_html__( 'Title', 'uicore-framework' ),
                        'price' => esc_html__( 'Price', 'uicore-framework' ),
                        'popularity' => esc_html__( 'Popularity', 'uicore-framework' ),
                        'rating' => esc_html__( 'Rating', 'uicore-framework' ),
                        'rand' => esc_html__( 'Random', 'uicore-framework' ),
                        'menu_order' => esc_html__( 'Menu Order', 'uicore-framework' ),
                    ],
                ]
            );
            $this->add_control(
                'order',
                [
                    'label' => esc_html__( 'Order', 'uicore-framework' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'desc',
                    'options' => [
                        'asc' => esc_html__( 'ASC', 'uicore-framework' ),
                        'desc' => esc_html__( 'DESC', 'uicore-framework' ),
                    ],
                ]
            );

            $this->add_control(
                'heading_query',
                [
                    'label' => esc_html__( 'Query', 'uicore-framework' ),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );
            $this->TRAIT_register_post_query_controls(false);

        $this->end_controls_section();

        $this->start_controls_section(
            'section_products_style',
            [
                'label' => esc_html__( 'Products', 'uicore-framework' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_responsive_control(
                'column_gap',
                [
                    'label' => esc_html__( 'Columns Gap', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                    'default' => [
                        'size' => 80,
                    ],
                    'tablet_default' => [
                        'size' => 60,
                    ],
                    'mobile_default' => [
                        'size' => 20,
                    ],
                    'range' => [
                        'px' => [
                            'max' => 200,
                        ],
                        'em' => [
                            'max' => 10,
                        ],
                        'rem' => [
                            'max' => 10,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} ul.products' => 'grid-column-gap: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );

            $this->add_responsive_control(
                'row_gap',
                [
                    'label' => esc_html__( 'Rows Gap', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                    'default' => [
                        'size' => 40,
                    ],
                    'tablet_default' => [
                        'size' => 40,
                    ],
                    'mobile_default' => [
                        'size' => 40,
                    ],
                    'range' => [
                        'px' => [
                            'max' => 100,
                        ],
                        'em' => [
                            'max' => 10,
                        ],
                        'rem' => [
                            'max' => 10,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} ul.products' => 'grid-row-gap: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );

            $this->add_responsive_control(
                'align',
                [
                    'label' => esc_html__( 'Alignment', 'uicore-framework' ),
                    'type' => Controls_Manager::CHOOSE,
                    'options' => [
                        'left' => [
                            'title' => esc_html__( 'Left', 'uicore-framework' ),
                            'icon' => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => esc_html__( 'Center', 'uicore-framework' ),
                            'icon' => 'eicon-text-align-center',
                        ],
                        'right' => [
                            'title' => esc_html__( 'Right', 'uicore-framework' ),
                            'icon' => 'eicon-text-align-right',
                        ],
                    ],
                    'prefix_class' => 'elementor-product-loop-item--align-',
                    'selectors' => [
                        '{{WRAPPER}} ul.products li.product' => 'text-align: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'heading_title_style',
                [
                    'label' => esc_html__( 'Title', 'uicore-framework' ),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );

            $this->add_control(
                'title_color',
                [
                    'label' => esc_html__( 'Color', 'uicore-framework' ),
                    'type' => Controls_Manager::COLOR,
                    'global' => [
                        'default' => Global_Colors::COLOR_PRIMARY,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} ul.products li.product .woocommerce-loop-product__title' => 'color: {{VALUE}}',
                        '{{WRAPPER}} ul.products li.product .woocommerce-loop-category__title' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'title_typography',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                    ],
                    'selector' => '{{WRAPPER}} ul.products li.product .woocommerce-loop-product__title, ' .
                                  '{{WRAPPER}} ul.products li.product .woocommerce-loop-category__title',

                ]
            );

            $this->add_responsive_control(
                'title_spacing',
                [
                    'label' => esc_html__( 'Spacing', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                    'range' => [
                        'px' => [
                            'max' => 100,
                        ],
                        'em' => [
                            'max' => 10,
                        ],
                        'rem' => [
                            'max' => 10,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} ul.products li.product .woocommerce-loop-product__title' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                        '{{WRAPPER}} ul.products li.product .woocommerce-loop-category__title' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );

            $this->add_control(

                'heading_price_style',
                [
                    'label' => esc_html__( 'Price', 'uicore-framework' ),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );

            $this->add_control(
                'price_color',
                [
                    'label' => esc_html__( 'Color', 'uicore-framework' ),
                    'type' => Controls_Manager::COLOR,
                    'global' => [
                        'default' => Global_Colors::COLOR_PRIMARY,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} ul.products li.product .price' => 'color: {{VALUE}}',
                        '{{WRAPPER}} ul.products li.product .price bdi' => 'color: {{VALUE}}',
                        '{{WRAPPER}} ul.products li.product .price ins .amount' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'price_typography',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                    ],
                    'selector' => '{{WRAPPER}} ul.products li.product .price;' .
                                  '{{WRAPPER}} ul.products li.product .price bdi',
                ]
            );

            $this->add_control(
                'heading_old_price_style',
                [
                    'label' => esc_html__( 'Regular Price', 'uicore-framework' ),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );

            $this->add_control(
                'old_price_color',
                [
                    'label' => esc_html__( 'Color', 'uicore-framework' ),
                    'type' => Controls_Manager::COLOR,
                    'global' => [
                        'default' => Global_Colors::COLOR_PRIMARY,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} ul.products li.product .price del' => 'color: {{VALUE}}',
                        '{{WRAPPER}} ul.products li.product .price del .amount' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'old_price_typography',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                    ],
                    'selector' => '{{WRAPPER}} ul.products li.product .price del .amount  ',
                    'selector' => '{{WRAPPER}} ul.products li.product .price del ',
                ]
            );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_design_box',
            [
                'label' => esc_html__( 'Box', 'uicore-framework' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'border',
                    'selector' => '{{WRAPPER}} ul.products li.product',
                ]
            );
            $this->add_responsive_control(
                'product_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'uicore-framework' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', 'rem', '%'],
                    'selectors' => [
                        '{{WRAPPER}} ul.products li.product' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                    ],
                ]
            );
            $this->add_responsive_control(
                'box_padding',
                [
                    'label' => esc_html__( 'Padding', 'uicore-framework' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 50,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} ul.products li.product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                    ],
                ]
            );

            $this->start_controls_tabs( 'box_style_tabs' );

                $this->start_controls_tab( 'classic_style_normal',
                    [
                        'label' => esc_html__( 'Normal', 'uicore-framework' ),
                    ]
                );

                    $this->add_group_control(
                        Group_Control_Box_Shadow::get_type(),
                        [
                            'name' => 'box_shadow',
                            'selector' => '{{WRAPPER}} ul.products li.product',
                        ]
                    );
                    $this->add_control(
                        'box_bg_color',
                        [
                            'label' => esc_html__( 'Background Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} ul.products li.product' => 'background-color: {{VALUE}}',
                            ],
                        ]
                    );

                $this->end_controls_tab();

                $this->start_controls_tab( 'classic_style_hover',
                    [
                        'label' => esc_html__( 'Hover', 'uicore-framework' ),
                    ]
                );

                    $this->add_group_control(
                        Group_Control_Box_Shadow::get_type(),
                        [
                            'name' => 'box_shadow_hover',
                            'selector' => '{{WRAPPER}} ul.products li.product:hover',
                        ]
                    );
                    $this->add_control(
                        'box_bg_color_hover',
                        [
                            'label' => esc_html__( 'Background Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} ul.products li.product:hover' => 'background-color: {{VALUE}}',
                            ],
                        ]
                    );
                    $this->add_control(
                        'box_border_color_hover',
                        [
                            'label' => esc_html__( 'Border Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} ul.products li.product:hover' => 'border-color: {{VALUE}}',
                            ],
                        ]
                    );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();
	}

    public function before_render() {
        //wee need those on the widget so the global styles will be also applyed even if is not a
        //woo page ( eg: widget can be added in any page )
        $this->add_render_attribute( '_wrapper', 'class', 'woocommerce' );
        $this->add_render_attribute( '_wrapper', 'class', 'elementor-kit-' . get_option( 'elementor_active_kit' ) );
        // Ensure you call the parent method
        parent::before_render();
    }

    function get_query()
    {
        return $this->_query;
    }

    protected function render()
    {
        if( $this->no_woo_fallback() ){
            return;
        }

        $query_type = $this->get_settings('product_query_post_type');

        if($query_type === 'related'){
            $this->render_related_products();
            return;
        }

        // Render current/custom products otherwhise
        $this->render_products();
    }

    protected function render_products() {
        global  $wp_query;
        $default_query = $wp_query;

        $settings = $this->get_settings();

        $this->TRAIT_query_product($settings);
        $wp_query = $this->get_query();

        // If the user adds the widget without changing the column value, an "undefined column key" error occurs on frontend due to a bug where,
        // setting a given control default value as an dinamic variable, prevents the editor from saving this value. So we recover it again on render.
        $columns = isset($settings['columns'])
                 ? $settings['columns']
                 : Helper::get_option('blog_col', 3);

        $loops = 0;

        if($this->is_edit_mode()){
            //kit is used for global styles
            $kit_class = 'elementor-kit-' . get_option( 'elementor_active_kit' );
            $this->render_woo_wrapper(false,$kit_class);
        }

        // No posts found
        if ( $wp_query->have_posts() ) {
            ?>
                <ul class="products elementor-grid columns-<?php echo esc_attr($columns); ?>">
                    <?php
                    while ($wp_query->have_posts()) {
                        $wp_query->the_post();
                        \wc_get_template_part( 'content', 'product' );
                        $loops++;
                    }

                    ?>
                </ul>
            <?php
        } else {
            ?>
                <p style="text-align:center"> <?php echo __('No posts found.', 'uicore-framework'); ?> </p>
            <?php
        }

        if($this->is_edit_mode()){
            $this->render_woo_wrapper(true);
        }

        wp_reset_query();
        $wp_query = $default_query;
    }

    protected function render_related_products()
    {
        $product = $this->get_product_data();
        if (!$product) return;

        $settings = $this->get_settings();

        $args = [
			'posts_per_page' => empty( $settings['item_limit'] ) ? 4 : $settings['item_limit']['size'],
			'columns' => empty( $settings['columns'] ) ? 4 : $settings['columns'],
			'orderby' => $settings['orderby'],
			'order' => $settings['order'],
		];

		$args = array_map( 'sanitize_text_field', $args );

		// Get visible related products then sort them at random.
		$args['related_products'] = array_filter(
            array_map(
                'wc_get_product',
                wc_get_related_products( $product->get_id(),
                $args['posts_per_page'],
                $product->get_upsell_ids() )
            ),
            'wc_products_array_filter_visible'
        );

		// Handle orderby.
		$args['related_products'] = wc_products_array_orderby( $args['related_products'], $args['orderby'], $args['order'] );

        // Remove template heading title
        add_filter('woocommerce_product_related_products_heading', function(){ return; });

        ob_start();
        \wc_get_template( 'single-product/related.php', $args );
        $related_products_html = ob_get_clean();

		if ( $related_products_html ) {

            if($this->is_edit_mode()){
                $this->render_woo_wrapper();
            }

            // Add elementor-grid and query-related classes
			$related_products_html = str_replace(
                '<ul class="products',
                '<ul class="products elementor-grid',
                $related_products_html
            );

			// PHPCS - Doesn't need to be escaped since it's a WooCommerce template, and 3rd party plugins might hook into it.
			echo $related_products_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

            if($this->is_edit_mode()){
                $this->render_woo_wrapper(true);
            }
		}
    }

	public function render_plain_content() {}
}
\Elementor\Plugin::instance()->widgets_manager->register(new Product_Grid());