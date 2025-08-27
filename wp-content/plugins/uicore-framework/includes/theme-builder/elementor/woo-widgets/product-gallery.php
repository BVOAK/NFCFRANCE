<?php
namespace UiCore\ThemeBuilder\Widgets;

use UiCore\Elementor\TB_Widget_Base;
use Uicore\Helper;
use UiCore\WooCommerce\ProductGallery;

use Elementor\Controls_Manager;

defined('ABSPATH') || exit();

/**
 * Product Gallery Widget
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */

class ProductGalleryWidget extends TB_Widget_Base {

	public function get_name() {
		return 'uicore-woo-product-gallery';
	}
	public function get_title() {
		return esc_html__( 'Product Gallery', 'uicore-framework' );
	}
	public function get_icon() {
		return 'eicon-gallery-group ui-e-widget';
	}
	public function get_categories() {
		return ['uicore-woo', 'uicore-theme-builder'];
	}
	public function get_keywords() {
		return [ 'woocommerce', 'product', 'shop', 'store', 'gallery', 'images', 'pictures' ];
	}
    public function get_styles() {

        $theme_style = Helper::get_option('woos_product_gallery'); // Theme default option

        // Handlers for each gallery style
        $handlers = [
            'left_thumbs'   => 'woocommerce/gallery-thumbs',
            'grid_column'   => 'woocommerce/gallery-columns',
            'grid_column_2' => 'woocommerce/gallery-columns',
        ];

        $styles = [
            'single-product' => [
                'deps' => ['woocommerce-general']
            ],
            $handlers['grid_column'] => [
                'condition' => [
                    'gallery_style' => ['grid_column', 'grid_column_2']
                ]
            ],
            $handlers['left_thumbs'] => [
                'condition' => [
                    'gallery_style' => ['left_thumbs']
                ]
            ],
        ];

        if( ! empty($theme_style) && array_key_exists($theme_style, $handlers) ) {
            $styles[$handlers[$theme_style]]['condition']['gallery_style'][] = 'theme';
        }

        return $styles;
	}
    public function get_scripts()
    {
        return [
            'gallery-editor' => [
                'deps' => [
                    // 'uicore-manifest',
                    'flexslider',
                    'wc-single-product'
                ],
                'custom_conditions' => [
                    'direct_condition' => $this->is_edit_mode()
                ]
            ],
        ];
    }

	protected function register_controls() {
        if( $this->no_woo_fallback(true) ){
            return;
        }

        $this->start_controls_section(
			'content_gallery',
			[
				'label' => esc_html__( 'Gallery', 'uicore-framework' ),
			]
		);

            $radius_options = [
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ]
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
            ];

            $this->register_post_list('product');

            $this->add_control(
                'gallery_style',
                [
                    'label' => esc_html__( 'Gallery Style', 'uicore-framework' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'theme',
                    'description' => esc_html('If set to "Theme Default", the gallery will inherit theme options style.', 'uicore-framework'),
                    'options' => [
                        'theme'         => esc_html__( 'Theme Default', 'uicore-framework' ),
                        ''              => esc_html__( 'Thumbnails', 'uicore-framework' ),
                        'left_thumbs'   => esc_html__( 'Left Thumbnails', 'uicore-framework' ),
                        'grid_column'   => esc_html__( 'Column', 'uicore-framework' ),
                        'grid_column_2' => esc_html__( 'Two Columns', 'uicore-framework' ),
                    ],
                ]
            );

            $this->add_control(
                'gallery_warning',
                [
                    'type' => Controls_Manager::NOTICE,
                    'notice_type' => 'info',
                    'dismissible' => true,
                    'heading' => esc_html__( 'Gallery options', 'uicore-framework' ),
                    'content' => esc_html__( 'To enable gallery style options, you must select a tab style instead of "Theme Default"', 'uicore-framework'),
                    'condition' => [
                        'gallery_style' => ['theme']
                    ],
                ]
            );

            $this->add_control(
                'gallery_radius',
                [
                    'label' => esc_html__( 'Image Radius', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                    'range' => $radius_options['range'],
                    'default' => $radius_options['default'],
                    'selectors' => [
                        '{{WRAPPER}} .woocommerce-product-gallery .woocommerce-product-gallery__image img' => 'border-radius: {{SIZE}}{{UNIT}};',
                    ],
                    'condition' => [
                        'gallery_style!' => ['theme']
                    ],
                ]
            );
            $this->add_control(
                'gallery_thumb_radius',
                [
                    'label' => esc_html__( 'Thumbnails Radius', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                    'range' => $radius_options['range'],
                    'default' => $radius_options['default'],
                    'selectors' => [
                        '{{WRAPPER}} .woocommerce-product-gallery .flex-control-thumbs img' => 'border-radius: {{SIZE}}{{UNIT}};',
                    ],
                    'condition' => [
                        'gallery_style' => ['', 'left_thumbs']
                    ],
                ]
            );
            $this->add_control(
                'gallery_spacing',
                [
                    'label' => esc_html__( 'Spacing', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => ['px', 'em', 'rem'],
                    'range' =>  [
                        'px' => [
                            'min' => 0,
                            'max' => 50,
                            'step' => 5,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 15,
                    ],
                    'selectors' => [
                        // left thumbs
                        '{{WRAPPER}} .uicore-gallery-left-thumbs .flex-control-thumbs' => 'gap: {{SIZE}}{{UNIT}};',
                        '{{WRAPPER}} .uicore-gallery-left-thumbs ol li' => 'width: 100% !important; float: none !important;',
                        // default thumbs
                        '{{WRAPPER}} .woocommerce-product-gallery:not(.uicore-gallery-left-thumbs) .flex-viewport ' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                        '{{WRAPPER}} .woocommerce-product-gallery:not(.uicore-gallery-left-thumbs) .flex-control-thumbs li' => 'width: calc((100% - {{SIZE}}{{UNIT}} * 3) / 4) !important;',
                    ],
                    'condition' => [
                        'gallery_style' => ['', 'left_thumbs']
                    ],
                ]
            );
            $this->add_control(
                'gallery_gap',
                [
                    'label' => esc_html__( 'Grid Gap', 'uicore-framework' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 100,
                            'step' => 5,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 20,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .uicore-grid-gallery' => '--uicore-gallery-gap: {{SIZE}}{{UNIT}};',
                    ],
                    'condition' => [
                        'gallery_style' => ['grid_column', 'grid_column_2']
                    ],
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

        if($this->is_edit_mode()){

            // Add the data requested by woo single product script in editor as inline script
            $data = array(
                'i18n_required_rating_text' => esc_attr__( 'Please select a rating', 'woocommerce' ),
                'review_rating_required'    => wc_review_ratings_required() ? 'yes' : 'no',
                'flexslider'                => apply_filters(
                    'woocommerce_single_product_carousel_options',
                    array(
                        'rtl'            => is_rtl(),
                        'animation'      => 'slide',
                        'smoothHeight'   => true,
                        'directionNav'   => false,
                        'controlNav'     => 'thumbnails',
                        'slideshow'      => false,
                        'animationSpeed' => 500,
                        'animationLoop'  => false, // Breaks photoswipe pagination if true.
                        'allowOneSlide'  => false,
                    )
                ),
                'zoom_enabled'              => apply_filters( 'woocommerce_single_product_zoom_enabled', get_theme_support( 'wc-product-gallery-zoom' ) ),
                'zoom_options'              => apply_filters( 'woocommerce_single_product_zoom_options', array() ),
                'photoswipe_enabled'        => apply_filters( 'woocommerce_single_product_photoswipe_enabled', get_theme_support( 'wc-product-gallery-lightbox' ) ),
                'photoswipe_options'        => apply_filters(
                    'woocommerce_single_product_photoswipe_options',
                    array(
                        'shareEl'               => false,
                        'closeOnScroll'         => false,
                        'history'               => false,
                        'hideAnimationDuration' => 0,
                        'showAnimationDuration' => 0,
                    )
                ),
                'flexslider_enabled'        => apply_filters( 'woocommerce_single_product_flexslider_enabled', get_theme_support( 'wc-product-gallery-slider' ) ),
            );
            wp_add_inline_script('wc-singl  e-product', 'var wc_single_product_params = ' . json_encode($data) . ';', 'before');

            $this->render_woo_wrapper();
        }

        ProductGallery::init( $this->get_settings('gallery_style'), false );

        // Close `.woocommerce` class
        if($this->is_edit_mode()){
            $this->render_woo_wrapper(true);
        }
	}

	protected function content_template() {
	}
}
\Elementor\Plugin::instance()->widgets_manager->register(new ProductGalleryWidget());
