<?php
namespace UiCore\ThemeBuilder\Widgets;

use Uicore\Elementor\TB_Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

use Uicore\Helper;

defined('ABSPATH') || exit();

/**
 * Product Rating
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */
class Product_Rating extends TB_Widget_Base {

    public function get_name() {
		return 'uicore-woo-product-rating';
	}
	public function get_title() {
		return esc_html__( 'Product Rating', 'uicore-framework' );
	}
	public function get_icon() {
		return 'eicon-product-rating ui-e-widget';
	}
	public function get_categories() {
		return ['uicore-woo', 'uicore-theme-builder'];
	}
	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'product', 'rating', 'review', 'comments', 'uicore-stars-rating'];
	}
    public function get_styles() {
        return [
            'single-product' => [
                'deps' => [
                    'woocommerce-general'
                ]
            ],
        ];
	}
    public function get_scripts(){
        return [];
    }

	protected function register_controls() {
        if ( $this->no_woo_fallback(true) ) {
            return;
        }

        $rating_style = Helper::get_option('woos_rating_style');

		$this->start_controls_section(
			'section_rating',
			[
				'label' => esc_html__( 'Style', 'uicore-elements' ),
			]
		);

            $this->register_post_list('product');

            $this->add_control(
                'rating_style',
                [
                    'type' => Controls_Manager::NOTICE,
                    'notice_type' => 'info',
                    'dismissible' => true,
                    'heading' => esc_html__( 'Rating Style', 'uicore-framework' ),
                    'content' => esc_html__('To change the rating style, go to theme options; search for `rating style`; select a style; save it, than update this page to see the updated controls.', 'uicore-framework' ),
                ]
            );

            // Star type controls
            if($rating_style === 'stars'){
                $this->add_control(
                    'star_style_heading',
                    [
                        'label' => esc_html__( 'Star styles', 'uicore-framework'),
                        'type' => Controls_Manager::HEADING,
                    ]
                );
                $this->add_control(
                    'star_color',
                    [
                        'label' => esc_html__( 'Star Color', 'uicore-framework'),
                        'type' => Controls_Manager::COLOR,
                        'default' => '#ffb62b',
                        'selectors' => [
                            '{{WRAPPER}} .star-rating span::before' => 'color: {{VALUE}}',
                        ],
                    ]
                );
                $this->add_control(
                    'empty_star_color',
                    [
                        'label' => esc_html__( 'Empty Star Color', 'uicore-framework'),
                        'type' => Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .star-rating::before' => 'color: {{VALUE}}',
                        ],
                    ]
                );
                $this->add_control(
                    'star_size',
                    [
                        'label' => esc_html__( 'Star Size', 'uicore-framework'),
                        'type' => Controls_Manager::SLIDER,
                        'size_units' => [ 'px'],
                        'default' => [
                            'unit' => 'px',
                            'size' => 12,
                        ],
                        'range' => [
                            'px' => [
                                'max' => 50,
                            ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .star-rating span::before, {{WRAPPER}} .star-rating' => 'font-size: {{SIZE}}{{UNIT}} !important;',
                        ],
                    ]
                );

            // Bar type controls
            } else {
                $this->add_control(
                    'rating_style_heading',
                    [
                        'label' => esc_html__( 'Bar styles', 'uicore-framework'),
                        'type' => Controls_Manager::HEADING,
                    ]
                );
                $this->add_control(
                    'bar_radius',
                    [
                        'label' => esc_html__( 'Border Radius', 'uicore-framework'),
                        'type' => Controls_Manager::DIMENSIONS,
                        'selectors' => [
                            '{{WRAPPER}} .star-rating, {{WRAPPER}} .star-rating span, {{WRAPPER}} .star-rating span:before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',

                        ],
                    ]
                );
                $this->add_control(
                    'bar_border_color',
                    [
                        'label' => esc_html__( 'Border Color', 'uicore-framework'),
                        'type' => Controls_Manager::COLOR,
                        'default' => '#000',
                        'selectors' => [
                            '{{WRAPPER}} .star-rating' => 'border-color: {{VALUE}}',
                        ],
                    ]
                );
                $this->add_control(
                    'bar_color',
                    [
                        'label' => esc_html__( 'Grade Color', 'uicore-framework'),
                        'type' => Controls_Manager::COLOR,
                        'default' => '#FFF',
                        'selectors' => [
                            '{{WRAPPER}} .star-rating .rating' => 'color: {{VALUE}};',
                            '{{WRAPPER}} .star-rating .rating span::before' => 'color: transparent !important;',
                        ],
                    ]
                );
                $this->add_control(
                    'bar_background_color',
                    [
                        'label' => esc_html__( 'Background Color', 'uicore-framework'),
                        'type' => Controls_Manager::COLOR,
                        'default' => '#000',
                        'selectors' => [
                            '{{WRAPPER}} .star-rating span::before' => 'background-color: {{VALUE}}',
                        ],
                    ]
                );
            }

            $this->add_control(
                'link_style_heading',
                [
                    'label' => esc_html__( 'Link', 'uicore-framework'),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'before'
                ]
            );

            $this->add_control(
                'space_between',
                [
                    'label' => esc_html__( 'Spacing', 'uicore-framework'),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                    'default' => [
                        'unit' => 'em',
                    ],
                    'range' => [
                        'px' => [
                            'max' => 50,
                        ],
                        'em' => [
                            'max' => 5,
                        ],
                        'rem' => [
                            'max' => 5,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .star-rating' => 'margin-right: {{SIZE}}{{UNIT}} !important;',
                    ],
                ]
            );

            $this->add_control(
                'link_color',
                [
                    'label' => esc_html__( 'Link Color', 'uicore-framework'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .woocommerce-review-link' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'text_typography',
                    'selector' => '{{WRAPPER}} .woocommerce-review-link',
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

        if ( ! post_type_supports( 'product', 'comments' ) ) {
			return;
		}

        if($this->is_edit_mode()){
            $this->render_woo_wrapper();
        }

          // \wc_get_template( 'single-product/rating.php' ); See render_rating() comments to understand why we're not using the template.
          $this->render_rating($product);

        if($this->is_edit_mode()){
            $this->render_woo_wrapper(true);
        }
	}

    /**
     *  Renders the Woocommerce single product rating template. Needs to keep track of single-product/rating.php template for future updates from Woocommerce.
     *
     * @param object $product - The product ID.
     */
    protected function render_rating($product) {

        // Instead of adding the woo template, we re-write it here so users can see, on editor, the review link text.
        // The reviews text is not rendered by calling the template because, since is conditionally printed under `comments_open()`, takes the current page ID as
        // param, wich in editor context means the current page ID, not a product ID. Also, the template don't give ways of passing a product ID as param.

        // Return the simple rating markup if we're in a loop template
        if( \is_shop() || \is_post_type_archive('product')  || \is_product_taxonomy() ){
            echo wc_get_rating_html( $product->get_average_rating() ); // WPCS: XSS ok.
            return;
        }

        $rating_count = $product->get_rating_count();
        $review_count = $product->get_review_count();
        $average      = $product->get_average_rating();

        if ( $rating_count > 0 ) : ?>

            <div class="woocommerce-product-rating">
                <?php echo wc_get_rating_html( $average, $rating_count ); // WPCS: XSS ok. ?>
                <?php if ( comments_open($product) ) : ?>
                    <?php //phpcs:disable ?>
                    <a href="#reviews" class="woocommerce-review-link" rel="nofollow">
                        (<?php printf( _n( '%s customer review', '%s customer reviews', $review_count, 'uicore-framework' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)</a>
                    <?php // phpcs:enable ?>
                <?php endif ?>
            </div>

        <?php endif;
    }

	protected function content_template() {}
}
\Elementor\Plugin::instance()->widgets_manager->register(new Product_Rating());
