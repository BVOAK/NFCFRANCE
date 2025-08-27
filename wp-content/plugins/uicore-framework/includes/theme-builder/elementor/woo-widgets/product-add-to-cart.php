<?php
namespace UiCore\ThemeBuilder\Widgets;

use Uicore\Elementor\TB_Widget_Base;
use UiCore\WooCommerce\Swatches;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Typography;

defined('ABSPATH') || exit();

/**
 * Add To Cart Widget
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */
class Product_Add_To_Cart extends TB_Widget_Base {

	public function get_name() {
		return 'uicore-woo-product-add-to-cart';
	}
	public function get_title() {
		return esc_html__( 'Add To Cart', 'uicore-framework' );
	}
	public function get_icon() {
		return 'eicon-product-add-to-cart ui-e-widget';
	}
    public function get_categories() {
		return ['uicore-woo', 'uicore-theme-builder'];
	}
	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'cart', 'product', 'button', 'add to cart' ];
	}
    public function get_styles() {

        return ['single-product'];
	}
    public function get_scripts()
    {
        return [
            'add-to-cart-editor' => [
                'deps' => [
                    'wc-add-to-cart-variation',
                    'wc-single-product',
                    'wc-order-attribution',
                    'uicore-swatches',
                ],
                'custom_conditions' => [
                    'direct_condition' => $this->is_edit_mode()
                ]
            ]
        ];
    }


	protected function register_controls() {
        if( $this->no_woo_fallback(true) ){
            return;
        }

		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Content', 'uicore-framework' ),
			]
		);

            $this->register_post_list('product');

		$this->end_controls_section();

        $this->start_controls_section(
			'section_atc_general_style',
			[
				'label' => esc_html__( 'General', 'uicore-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

            $this->add_responsive_control(
                'atc_gap',
                [
                    'label' => esc_html__( 'Elements spacing', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => ['px', '%', 'em', 'rem'],
                    'default' => [
                        'unit' => 'px',
                        'size' => 20,
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--uicore-add-to-cart-gap: {{SIZE}}{{UNIT}}'
                    ]
                ],
            );

            $this->add_control(
                'atc_price_color',
                [
                    'label' => esc_html__( 'Price Color', 'uicore-framework' ),
                    'type' => Controls_Manager::COLOR,
                    'global' => [
                        'default' => Global_Colors::COLOR_PRIMARY,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} form.cart .price, {{WRAPPER}} form.cart .price bdi, {{WRAPPER}} form.cart .price del bdi' => 'color: {{VALUE}} !important;',
                        '{{WRAPPER}} form.cart .price del' => 'text-decoration-color: {{VALUE}} !important;',
                    ],
                ]
            );

            $this->add_control(
                'atc_stock_color',
                [
                    'label' => esc_html__( 'Stock Color', 'uicore-framework' ),
                    'type' => Controls_Manager::COLOR,
                    'global' => [
                        'default' => Global_Colors::COLOR_PRIMARY,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .stock.in-stock' => 'color: {{VALUE}} !important;',

                    ],
                ]
            );

        $this->end_controls_section();

		$this->start_controls_section(
			'section_atc_button_style',
			[
				'label' => esc_html__( 'Button', 'uicore-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

            $this->add_control(
                'button_fit_width',
                [
                    'label' => esc_html__( 'Fill Width', 'uicore-framework' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'true',
                    'default' => 'true',
                    'label_on' => esc_html__( 'True', 'uicore-framework' ),
                    'label_off' => esc_html__( 'False', 'uicore-framework' ),
                ]
            );
            $this->add_responsive_control(
                'button_width',
                [
                    'label' => esc_html__( 'Button Width', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'default' => [
                        'size' => 200,
                        'unit' => 'px',
                    ],
                    'range' => [
                        'px' => [
                            'min' => 50,
                            'max' => 500,
                            'step' => 5,
                        ],
                        '%' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                    'condition' => [
                        'button_fit_width!' => 'true',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .cart button, {{WRAPPER}} .cart .button' => 'width: {{SIZE}}{{UNIT}} !important;',
                    ],
                ]
            );

            $this->add_control(
                'button_height',
                [
                    'label' => esc_html__( 'Button Height', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', 'em', 'rem', 'vw', 'vh', 'custom' ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--uicore-woo-single-add-to-cart-height: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'button_spacing',
                [
                    'label' => esc_html__( 'Spacing', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'default' => [
                        'size' => 14,
                        'unit' => 'px',
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--uicore-button-spacing: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'button_typography',
                    'selector' => '{{WRAPPER}} .cart button, {{WRAPPER}} .cart .button.single_add_to_cart_button',
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'button_border',
                    'selector' => '{{WRAPPER}} .cart button, {{WRAPPER}} .cart .button.single_add_to_cart_button',
                    'exclude' => [ 'color' ],
                ]
            );

            $this->add_control(
                'button_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'uicore-framework' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                    'selectors' => [
                        '{{WRAPPER}} .cart button, {{WRAPPER}} .cart .button.single_add_to_cart_button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->start_controls_tabs( 'button_style_tabs' );

                $this->start_controls_tab( 'button_style_normal',
                    [
                        'label' => esc_html__( 'Normal', 'uicore-framework' ),
                    ]
                );

                    $this->add_control(
                        'button_text_color',
                        [
                            'label' => esc_html__( 'Text Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .cart button, {{WRAPPER}} .cart .button.single_add_to_cart_button' => 'color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_bg_color',
                        [
                            'label' => esc_html__( 'Background Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .cart button, {{WRAPPER}} .cart .button.single_add_to_cart_button' => 'background-color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_border_color',
                        [
                            'label' => esc_html__( 'Border Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .cart button, {{WRAPPER}} .cart .button.single_add_to_cart_button' => 'border-color: {{VALUE}}',
                            ],
                        ]
                    );

		        $this->end_controls_tab();

                $this->start_controls_tab( 'button_style_hover',
                    [
                        'label' => esc_html__( 'Hover', 'uicore-framework' ),
                    ]
                );

                    $this->add_control(
                        'button_text_color_hover',
                        [
                            'label' => esc_html__( 'Text Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .cart button:hover, {{WRAPPER}} .cart .button.single_add_to_cart_button:hover' => 'color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_bg_color_hover',
                        [
                            'label' => esc_html__( 'Background Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .cart button:hover, {{WRAPPER}} .cart .button.single_add_to_cart_button:hover' => 'background-color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_border_color_hover',
                        [
                            'label' => esc_html__( 'Border Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .cart button:hover, {{WRAPPER}} .cart .button.single_add_to_cart_button:hover' => 'border-color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_transition',
                        [
                            'label' => esc_html__( 'Transition Duration', 'uicore-framework' ) . ' (s)',
                            'type' => Controls_Manager::SLIDER,
                            'default' => [
                                'size' => 0.2,
                            ],
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 3,
                                    'step' => 0.1,
                                ],
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .cart button, {{WRAPPER}} .cart .button.single_add_to_cart_button' => 'transition: all {{SIZE}}s',
                            ],
                        ]
                    );

		        $this->end_controls_tab();

		    $this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_atc_quantity_style',
			[
				'label' => esc_html__( 'Quantity', 'uicore-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

            $this->add_control(
                'show_quantity',
                [
                    'label' => esc_html__( 'Hide Quantity', 'uicore-framework' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'uicore-hide-quantity',
                    'prefix_class' => '',
                    'render_type' => 'template',
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'quantity_typography',
                    'selector' => '{{WRAPPER}} .quantity .qty',
                    'condition' => [
                        'show_quantity' => '',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'quantity_border',
                    'selector' => '{{WRAPPER}} .quantity .qty',
                    'exclude' => [ 'color' ],
                    'condition' => [
                        'show_quantity' => '',
                    ],
                ]
            );

            $this->add_control(
                'quantity_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'uicore-framework' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                    'selectors' => [
                        '{{WRAPPER}} .quantity .qty' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'condition' => [
                        'show_quantity' => '',
                    ],
                ]
            );

            $this->start_controls_tabs( 'quantity_style_tabs',
                [
                    'condition' => [
                        'show_quantity' => '',
                    ],
                ]
            );

                $this->start_controls_tab( 'quantity_style_normal',
                    [
                        'label' => esc_html__( 'Normal', 'uicore-framework' ),
                    ]
                );

                    $this->add_control(
                        'quantity_text_color',
                        [
                            'label' => esc_html__( 'Text Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .quantity .qty, {{WRAPPER}} .quantity .plus, {{WRAPPER}} .quantity .minus' => 'color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'quantity_bg_color',
                        [
                            'label' => esc_html__( 'Background Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .quantity .qty' => 'background-color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'quantity_border_color',
                        [
                            'label' => esc_html__( 'Border Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .quantity .qty' => 'border-color: {{VALUE}}',
                            ],
                        ]
                    );

                $this->end_controls_tab();

                $this->start_controls_tab( 'quantity_style_focus',
                    [
                        'label' => esc_html__( 'Focus', 'uicore-framework' ),
                    ]
                );

                    $this->add_control(
                        'quantity_text_color_focus',
                        [
                            'label' => esc_html__( 'Text Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .quantity .qty:focus' => 'color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'quantity_bg_color_focus',
                        [
                            'label' => esc_html__( 'Background Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .quantity .qty:focus' => 'background-color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'quantity_border_color_focus',
                        [
                            'label' => esc_html__( 'Border Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .quantity .qty:focus' => 'border-color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'quantity_transition',
                        [
                            'label' => esc_html__( 'Transition Duration', 'uicore-framework' ) . ' (s)',
                            'type' => Controls_Manager::SLIDER,
                            'default' => [
                                'size' => 0.2,
                            ],
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 3,
                                    'step' => 0.1,
                                ],
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .quantity .qty' => 'transition: all {{SIZE}}s',
                            ],
                        ]
                    );

                $this->end_controls_tab();

            $this->end_controls_tabs();

		$this->end_controls_section();

        $this->start_controls_section(
			'section_swatches',
			[
				'label' => esc_html__( 'Swatches', 'uicore-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

            $this->add_control(
                'swatch_size',
                [
                    'label' => esc_html__( 'Size', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'selectors' => [
                        '{{WRAPPER}}' => '--uicore-swatch-size: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );
            $this->add_control(
                'swatch_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'default' => [
                        'size' => 4,
                        'unit' => 'px',
                    ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 100,
                            'step' => 2,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--uicore-swatch-big-radius: {{SIZE}}{{UNIT}}; --uicore-swatch-radius: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'swatch_border',
                    'selector' => '{{WRAPPER}} .uicore-swatch',
                ]
            );

            $this->add_control(
                'swatch_selected_color',
                [
                    'label' => esc_html__( 'Selected Border Color', 'uicore-framework' ),
                    'type' => Controls_Manager::COLOR,
                    'default' => '#0bac00;',
                ]
            );

            $this->add_control(
                'swatch_selected_border_width',
                [
                    'label' => esc_html__( 'Selected Border Width', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'default' => [
                        'size' => 1,
                        'unit' => 'px',
                    ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 8,
                            'step' => 1,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .uicore-swatch.selected, {{WRAPPER}} .uicore-swatch:hover ' => 'box-shadow: 0 0 0 {{SIZE}}px {{swatch_selected_color.VALUE}}; border-color: {{swatch_selected_color.VALUE}};',
                    ],
                ]
            );

        $this->end_controls_section();
	}

	protected function render() {
        if( $this->no_woo_fallback() ){
            return;
        }

        $product = $this->get_product_data();
        if (!$product) return;

        if($this->is_edit_mode()) {
                //required for variation
                ?>
                <script>
                    window._ = {};
                    window._.template = function(template) {

                        return function(data) {
                            const templateData = {
                                data: data
                            }
                            return template.replace(/{{{\s*([\w.]+)\s*}}}/g, (_, key) => {

                            const keys = key.split('.');
                            let value = templateData;

                            // Log to check each step of key traversal
                            for (let k of keys) {
                                value = value[k];
                                if (value === undefined) {
                                    return ''; // Return empty string if any key is undefined
                                }
                            }
                            return value;
                        });
                        }
                    }
                </script>
            <?php
            \wc_get_template('single-product/add-to-cart/variation.php');

            $data = array(
                "wc_ajax_url" => "/?wc-ajax=%%endpoint%%",
                "i18n_no_matching_variations_text" => "Sorry, no products matched your selection. Please choose a different combination.",
                "i18n_make_a_selection_text" => "Please select some product options before adding this product to your cart.",
                "i18n_unavailable_text" => "Sorry, this product is unavailable. Please choose a different combination."
            );
            wp_add_inline_script('wc-add-to-cart-variation', 'var wc_add_to_cart_variation_params  = ' . json_encode($data) . ';', 'before');

            // Add the wrappers that are not present in the editor context
            // used by several css classes to style different woocommerce components
            $this->render_woo_wrapper(false, 'single-product' );

            Swatches::init(); // on frontend we don't need to init it because the theme already does it
        }
		?>

		<div class="elementor-add-to-cart elementor-product-<?php echo esc_attr( $product->get_type() ); ?>">

			<?php if ( $this->is_loop_item() ) {
				$this->render_loop_add_to_cart();
			} else {
				\woocommerce_template_single_add_to_cart();
			} ?>

		</div>

		<?php

        if($this->is_edit_mode()){
            $this->render_woo_wrapper(true);
        }
	}
	private function render_loop_add_to_cart() {
		$quantity_args = $this->get_loop_quantity_args();
		$button_args = [ 'quantity' => $quantity_args['min_value'] ];
		?>
		<div class="e-loop-add-to-cart-form-container">
			<form class="cart e-loop-add-to-cart-form">
				<?php
                    $this->render_loop_quantity_input( $quantity_args );
                    \woocommerce_template_loop_add_to_cart( $button_args );
				?>
			</form>
		</div>
		<?php
	}
	private function render_loop_quantity_input( $quantity_args ) {
        $product = $this->get_product_data();

		if (
			'simple' === $product->get_type()
			&& 'yes' === $this->get_settings_for_display( 'show_quantity' )
		) {
			woocommerce_quantity_input( $quantity_args );
		}
	}
	private function get_loop_quantity_args() {
        $product = $this->get_product_data();

		$quantity_args = [
			'min_value' => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
			'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
			'input_value' => $product->get_min_purchase_quantity(),
			'classes' => [ 'input-text', 'qty', 'text' ],
		];

		if ( 'no' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
			$quantity_args['min_value'] = $product->get_min_purchase_quantity();
			$quantity_args['input_value'] = $product->get_min_purchase_quantity();
			$quantity_args['classes'][] = 'disabled';
		}

		return $quantity_args;
	}
	private function is_loop_item() {
        return \is_shop();
	}

	public function render_plain_content() {}
}
\Elementor\Plugin::instance()->widgets_manager->register(new Product_Add_To_Cart());