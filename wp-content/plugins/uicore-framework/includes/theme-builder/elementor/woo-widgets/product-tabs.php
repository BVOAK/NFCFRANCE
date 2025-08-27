<?php
namespace UiCore\ThemeBuilder\Widgets;

use Uicore\Elementor\TB_Widget_Base;
use UiCore\WooCommerce\Product_Tab;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

defined('ABSPATH') || exit();

/**
 * Product Tabs Widget
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */

class Product_Tabs extends TB_Widget_Base {

	public function get_name() {
		return 'uicore-woo-product-tabs';
	}
	public function get_title() {
		return esc_html__( 'Product Tabs', 'uicore-framework' );
	}
	public function get_icon() {
		return 'eicon-product-tabs ui-e-widget';
	}
    public function get_categories() {
		return ['uicore-woo', 'uicore-theme-builder'];
	}
	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'data', 'product', 'tabs' ];
	}
    public function get_styles() {
        return ['single-product'];
	}
    public function get_scripts() {
        return [
            // accordion style script
            'woocommerce/product-tabs' => [
                'custom_conditions' => [
                    'controls_condition' => [
                        'tabs' => 'uicore-tab-accordion'
                    ],
                    'direct_condition' => \Uicore\Helper::get_option('woos_tabs_style') === 'accordion',
                ],
            ],
            // editor trigger script
            'product-tabs' => [
                'custom_conditions' => [
                    'direct_condition' => $this->is_edit_mode()
                ],
            ]
        ];
    }

	protected function register_controls() {
        if ( $this->no_woo_fallback(true) ) {
            return;
        }

        $this->start_controls_section(
			'section_product_content',
			[
				'label' => esc_html__( 'Content', 'uicore-framework' ),
			]
		);
            $this->register_post_list('product');
            $this->add_control(
                'tabs',
                [
                    'label' => esc_html__( 'Tabs Style', 'uicore-framework' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => '',
                    'prefix_class' => '',
                    'render_type' => 'template',
                    'frontend_available' => true,
                    'description' => esc_html('If set to "Theme Default", the gallery will inherit theme options style.', 'uicore-framework'),
                    'options' => [
                        ''                      => esc_html__( 'Theme Default', 'uicore-framework' ),
                        'uicore-horizontal'     => esc_html__( 'Horizontal Tabs', 'uicore-framework' ),
                        'uicore-tab-vertical'   => esc_html__( 'Vertical Tabs', 'uicore-framework' ),
                        'uicore-tab-accordion'  => esc_html__( 'Accordion', 'uicore-framework' ),
                        'uicore-tab-sections'   => esc_html__( 'Sections', 'uicore-framework' ),
                    ],
                ]
            );

        $this->end_controls_section();

		$this->start_controls_section(
			'section_product_tabs',
			[
				'label' => esc_html__( 'Tabs', 'uicore-framework' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'tabs!' => 'uicore-tab-sections'
                ],
			]
		);

            $this->add_control(
                'style_alert',
                [
                    'type' => Controls_Manager::ALERT,
                    'alert_type' => 'info',
                    'heading' => esc_html__( 'Tab options', 'uicore-framework' ),
                    'content' => esc_html__( 'To enable tab style options, you must select a tab style instead of "Theme Default"', 'uicore-framework'),
                    'condition' => [
                        'tabs' => ''
                    ],
                ]
            );

            $this->start_controls_tabs('tabs_style');

                $this->start_controls_tab( 'normal_tabs_style',
                    [
                        'label' => esc_html__( 'Normal', 'uicore-framework' ),
                        'condition' => [
                            'tabs!' => ''
                        ],
                    ]
                );

                    $this->add_control(
                        'tab_text_color',
                        [
                            'label' => esc_html__( 'Text Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .woocommerce-tabs ul.wc-tabs li a' => 'color: {{VALUE}} !important',
                                '{{WRAPPER}} .woocommerce-ui-accordion .ui-accordion-header' => 'color: {{VALUE}}',
                            ],
                            'condition' => [
                                'tabs!' => ''
                            ],
                        ]
                    );

                    $this->add_control(
                        'tab_bg_color',
                        [
                            'label' => esc_html__( 'Background Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'alpha' => false,
                            'selectors' => [
                                '{{WRAPPER}}' => '--uicore-tabs-bg: {{VALUE}};',
                                '{{WRAPPER}} .woocommerce-ui-accordion .ui-accordion-header' => 'background-color: {{VALUE}}',
                            ],
                            'condition' => [
                                'tabs!' => ''
                            ],
                        ]
                    );

                $this->end_controls_tab();

                $this->start_controls_tab( 'active_tabs_style',
                    [
                        'label' => esc_html__( 'Active', 'uicore-framework' ),
                        'condition' => [
                            'tabs!' => ''
                        ],
                    ]
                );

                    $this->add_control(
                        'active_tab_text_color',
                        [
                            'label' => esc_html__( 'Text Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .woocommerce-tabs ul.wc-tabs li.active a' => 'color: {{VALUE}} !important;',
                                '{{WRAPPER}} .woocommerce-ui-accordion .ui-active' => 'color: {{VALUE}}',
                            ],
                            'condition' => [
                                'tabs!' => ''
                            ],

                        ]
                    );

                    $this->add_control(
                        'active_tab_bg_color',
                        [
                            'label' => esc_html__( 'Background Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'alpha' => false,
                            'selectors' => [
                                '{{WRAPPER}} .woocommerce-tabs ul.wc-tabs li.active' => 'background-color: {{VALUE}} !important;',
                                '{{WRAPPER}} .woocommerce-ui-accordion .ui-active' => 'background-color: {{VALUE}}',
                            ],
                            'condition' => [
                                'tabs!' => ''
                            ],
                        ]
                    );

                    $this->add_control(
                        'active_tabs_border_color',
                        [
                            'label' => esc_html__( 'Border Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}}' => '--uicore-tabs-active-border-color: {{VALUE}}',
                                '{{WRAPPER}} .woocommerce-ui-accordion .ui-active' => 'border-color: {{VALUE}}',
                            ],
                            'condition' => [
                                'tabs!' => ''
                            ],
                            'separator' => 'after',
                        ]
                    );

                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'tab_typography',
                    'selector' => '{{WRAPPER}} .woocommerce-tabs ul.wc-tabs li a',
                    'condition' => [
                        'tabs!' => ''
                    ],
                ]
            );

            $this->add_responsive_control(
                'tab_padding',
                [
                    'label' => esc_html__( 'Padding', 'uicore-framework' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', 'rem'],
                    'selectors' => [
                        '{{WRAPPER}}.uicore-horizontal .woocommerce-tabs ul.wc-tabs li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                        '{{WRAPPER}}.uicore-tab-vertical .woocommerce-tabs ul.wc-tabs li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                        '{{WRAPPER}} .woocommerce-ui-accordion .ui-accordion-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'condition' => [
                        'tabs!' => ''
                    ],
                ]
            );
            $this->add_control(
                'tab_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                    'selectors' => [
                        '{{WRAPPER}}.uicore-horizontal .woocommerce-tabs ul.wc-tabs li' => 'border-radius: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0 0;',
                        '{{WRAPPER}}.uicore-tab-vertical .woocommerce-tabs ul.wc-tabs li' => 'border-radius: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}};',
                        '{{WRAPPER}} .woocommerce-ui-accordion .ui-accordion-header' => 'border-radius: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}};',
                    ],
                    'condition' => [
                        'tabs!' => ''
                    ],
                ]
            );
            $this->add_control(
                'tab_gap',
                [
                    'label' => esc_html__( 'Tabs Spacing', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'default' => [
                        'size' => 30,
                        'unit' => 'px'
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--uicore-tabs-gap: {{SIZE}}{{UNIT}};',
                    ],
                    'condition' => [
                        'tabs!' => ''
                    ],
                ]
            );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_product_panel_style',
            [
                'label' => esc_html__( 'Content', 'uicore-framework' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->start_controls_tabs( 'tabs_content' );
                $this->start_controls_tab( 'content_text_style',
                    [
                        'label' => esc_html__( 'Texts', 'uicore-framework' ),
                    ]
                );

                    $this->add_control(
                        'text_color',
                        [
                            'label' => esc_html__( 'Text Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .woocommerce-Tabs-panel > *' => 'color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Typography::get_type(),
                        [
                            'name' => 'content_typography',
                            'selector' => '{{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel > *',
                        ]
                    );

                    $this->end_controls_tab();

                $this->start_controls_tab( 'content_heading_style',
                    [
                        'label' => esc_html__( 'Headings', 'uicore-framework' ),
                    ]
                );

                    $this->add_control(
                        'heading_color',
                        [
                            'label' => esc_html__( 'Heading Color', 'uicore-framework' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .woocommerce-Tabs-panel h2' => 'color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Typography::get_type(),
                        [
                            'name' => 'content_heading_typography',
                            'selector' => '{{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel h2',
                        ]
                    );

                $this->end_controls_tab();
            $this->end_controls_tabs();

            $this->add_control(
                'panel_background_color',
                [
                    'label' => esc_html__( 'Background Color', 'uicore-framework' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .woocommerce-Tabs-panel' => 'background-color: {{VALUE}}',
                    ],
                    'separator' => 'before',
                ]
            );

            // Add group border control
            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'panel_border',
                    'selector' => '{{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel',
                ]
            );

            $this->add_control(
                'content_gap',
                [
                    'label' => esc_html__( 'Content Spacing', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'default' => [
                        'size' => 10,
                        'unit' => 'px'
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--uicore-panel-gap: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_control(
                'panel_padding',
                [
                    'label' => esc_html__( 'Padding', 'uicore-framework' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', 'rem'],
                    'selectors' => [
                        '{{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_control(
                'panel_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'uicore-framework' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', 'rem'],
                    'selectors' => [
                        '{{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'panel_box_shadow',
                    'selector' => '{{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel',
                ]
            );

		$this->end_controls_section();
	}

	protected function render() {
        if ( $this->no_woo_fallback() ) {
            return;
        }

        $product = $this->get_product_data();
        if (!$product) return;

        $tab_style = $this->get_settings_for_display('tabs');

        // If style is 'Theme Default', we get the theme style and add it to the widget wrapper,
        // since prefix class is not usefull in this case
        if( empty($tab_style) ) {
            $theme_style  = \Uicore\Helper::get_option('woos_tabs_style');
            $prefix_class = empty($theme_style) ? ' uicore-horizontal' : ' uicore-tab-' . esc_html($theme_style);
            $this->add_render_attribute('_wrapper', 'class', $prefix_class);
        }

        if( $this->is_edit_mode() ) {

            // The tab description content uses `the_content()`, wich in the editor screen context means will
            // return the page template instead of the product description, therefore we need to filter it.
            add_filter('the_content', function() use($product) {
                return $product->get_description();
            });

            // Adds two extra tabs with dummy content so users can better preview the component for styling purposes.
            // this is being used because, so far, can't retrieve all the product tabs content in editor.
            add_filter('woocommerce_product_tabs', function($tabs) {
                $tabs['uicore_fake_tab'] = [
                    'title' => 'Fake Tab',
                    'priority' => 25,
                    'callback' => function(){
                        echo '<h3>' . __('A fake tab to help you out.', 'uicore-framework') . '</h3>';
                        echo '<p>' . __('This tab appears only here, in editor mode, to help you style the widget.', 'uicore-framework') . '</p>';
                    }
                ];
                $tabs['uicore_fake_tab_2'] = [
                    'title' => 'Fake Tab 2',
                    'priority' => 25,
                    'callback' => function(){
                        echo '<h3>' . __('An extra one for you.', 'uicore-framework') . '</h3>';
                        echo '<p>' . __('This tab appears only here, in editor mode, to help you style the widget.', 'uicore-framework') . '</p>';
                    }
                ];
                return $tabs;
            });

            $this->render_woo_wrapper();
        }

        $html = Product_Tab::init( $tab_style );

        echo $html; // WPCS: XSS ok.

        if( $this->is_edit_mode() ) {
            $this->render_woo_wrapper(true);
        }
	}

	public function render_plain_content() {}
}
\Elementor\Plugin::instance()->widgets_manager->register(new Product_Tabs());