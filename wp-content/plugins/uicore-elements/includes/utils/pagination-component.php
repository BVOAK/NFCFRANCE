<?php

namespace UiCoreElements\Utils;

use UicoreElements\Helper;

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Typography;

defined('ABSPATH') || exit();

/**
 * Paginations Trait Component
 *
 */

trait Pagination_Trait
{

    function pagination_options()
    {
        return [
            'numbers' => esc_html__('Numbers', 'uicore-elements'),
            'load_more' => esc_html__('Load More', 'uicore-elements'),
        ];
    }
    function TRAIT_register_pagination_controls($section = true)
    {
        if ($section) {
            $this->start_controls_section(
                'section_pagination',
                [
                    'label' => esc_html__('Pagination', 'uicore-elements'),
                ]
            );
        }

        $this->add_control(
            'pagination',
            [
                'label' => __('Pagination', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
                'render_type' => 'template',
            ]
        );
        $this->add_control(
            'pagination_type',
            [
                'label' => esc_html__('Pagination Type', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'numbers',
                'options' => $this->pagination_options(),
                'frontend_available' => true,
                'condition' => [
                    'pagination' => 'yes',
                ],
            ]
        );

        if ($section) {
            $this->end_controls_section();
        }
    }
    function TRAIT_register_pagination_style_controls($section = true)
    {
        if ($section) {
            $this->start_controls_section(
                'section_style_pagination',
                [
                    'label' => __('Pagination Style', 'uicore-elements'),
                    'tab' => Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'pagination' => 'yes',
                    ],
                ]
            );
        }

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'pagination_typography',
                'label' => esc_html__('Typography', 'uicore-elements'),
                'selector' => '{{WRAPPER}} .uicore-page-item, {{WRAPPER}} .ui-e-load-more',
            ]
        );
        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Previous/Next Icon Size', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 30,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .uicore-pagination a.prev svg, {{WRAPPER}} .uicore-pagination a.next svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .uicore-pagination a.prev i, {{WRAPPER}} .uicore-pagination a.next i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'pagination_type' => 'numbers'
                ]
            ]
        );
        $this->add_responsive_control(
            'pagination_top',
            [
                'label' => __('Pagination Top Space', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 50,
                ],
                'selectors' => [
                    '{{WRAPPER}} .uicore-pagination' => 'margin-top: {{SIZE}}px;',
                    '{{WRAPPER}} .ui-e-load-more' => 'margin-top: {{SIZE}}px;',
                ],
            ]
        );
        $this->add_control(
            'pagination_align',
            [
                'label' => esc_html__('Pagination Alignment', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Left', 'uicore-elements'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'uicore-elements'),
                        'icon' => 'eicon-text-align-center',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .uicore-pagination ul' => 'justify-content: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-pagination' => 'text-align: {{VALUE}};',
                ],
            ]
        );
        // Number Specific
        $this->add_control(
            'pagination_padding',
            [
                'label' => __('Items Padding', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['em'],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'em',
                    'size' => 0.4,
                ],
                'selectors' => [
                    '{{WRAPPER}} .uicore-pagination ul li > *' => '    width: calc(1em + {{SIZE}}em);line-height: calc(1em + {{SIZE}}em);',
                ],
                'condition' => array(
                    'pagination_type' => 'numbers',
                ),
            ]
        );
        $this->add_control(
            'pagination_gap',
            [
                'label' => __('Items Gap', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['em'],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'em',
                    'size' => 0.4,
                ],
                'selectors' => [
                    '{{WRAPPER}} .uicore-pagination ul' => 'gap: {{SIZE}}em;',
                ],
                'condition' => array(
                    'pagination_type' => 'numbers',
                ),
            ]
        );
        $this->add_control(
            'pagination_radius',
            [
                'label' => __('Items Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['em'],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'em',
                    'size' => 0.2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .uicore-pagination ul li' => 'border-radius: {{SIZE}}em;',
                ],
                'condition' => array(
                    'pagination_type' => 'numbers',
                ),
            ]
        );
        // Load More Specific
        $this->add_control(
            'load_more_padding',
            [
                'label' => esc_html__('Button Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-load-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => array(
                    'pagination_type' => 'load_more'
                ),
            ]
        );
        $this->add_control(
            'load_more_radius',
            [
                'label' => esc_html__('Button Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-load-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => array(
                    'pagination_type' => 'load_more'
                ),
            ]
        );
        // General Again
        $this->start_controls_tabs(
            'pagination_item',
            [
                'condition' => [
                    'pagination' => 'yes',
                ],
            ]
        );
        $this->start_controls_tab(
            'pagination_normal_tab',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );
        $this->add_control(
            'pagination_bg',
            [
                'label' => esc_html__('Background', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .uicore-pagination li' => 'background: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-load-more' => 'background: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'pagination_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .uicore-pagination li a' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .uicore-pagination svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-load-more' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'pagination_border',
                'selector' => '{{WRAPPER}} .uicore-pagination ul li, {{WRAPPER}} .ui-e-load-more',
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'pagination_shadow',
                'selector' => '{{WRAPPER}} .uicore-pagination ul li, {{WRAPPER}} .ui-e-load-more',
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
            'pagination_hover_tab',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );
        $this->add_control(
            'pagination_hover_bg',
            [
                'label' => esc_html__('Background', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .uicore-pagination ul li:hover:not(.uicore-active)' => 'background: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-load-more:hover' => 'background: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'pagination_hover_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .uicore-pagination li:hover:not(.uicore-active) a' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .uicore-pagination li:hover:not(.uicore-active) svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .ui-e-load-more:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'pagination_border_hover',
                'selector' => '{{WRAPPER}} .uicore-pagination ul li:hover:not(.uicore-active), {{WRAPPER}} .ui-e-load-more:hover',
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'pagination_shadow_hover',
                'selector' => '{{WRAPPER}} .uicore-pagination ul li:hover:not(.uicore-active), {{WRAPPER}} .ui-e-load-more:hover',
            ]
        );
        $this->end_controls_tab();
        // Number specific
        $this->start_controls_tab(
            'pagination_active_tab',
            [
                'label' => esc_html__('Active', 'uicore-elements'),
                'condition' => [
                    'pagination_type' => 'numbers',
                ]
            ]
        );
        $this->add_control(
            'pagination_active_bg',
            [
                'label' => esc_html__('Background', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .uicore-page-link.current' => 'background: {{VALUE}};',
                ],
                'condition' => [
                    'pagination_type' => 'numbers',
                ]
            ]
        );
        $this->add_control(
            'pagination_active_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .uicore-page-link.current' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'pagination_type' => 'numbers',
                ]
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'pagination_border_active',
                'selector' => '{{WRAPPER}} .uicore-pagination li.uicore-active',
                'separator' => 'before',
                'condition' => [
                    'pagination_type' => 'numbers',
                ]
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'pagination_shadow_active',
                'selector' => '{{WRAPPER}} .uicore-pagination li.uicore-active',
                'condition' => [
                    'pagination_type' => 'numbers',
                ]
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        if ($section) {
            $this->end_controls_section();
        }
    }

    /**
     * Render pagination numbers.
     *
     * @param bool $is_product - If the pagination is for products
     * @param array $args
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    function render_numbers($is_product, $args = [])
    {

        if ($is_product) {
            $query = $this->get_query();

            // Currently we alow users to use number pagination in contexts that shouldn't be used. E.g: in related posts on single product pages
            if (!isset($query)) {
                return;
            }

            $total_items = Helper::get_framework_visible_posts('product');
            $total_count = wc_get_products(array_merge($query, ['limit' => -1, 'return' => 'ids']));  // Fetch all products - TODO: -1 is very bad for performance
            $total = ceil(count($total_count) / $total_items);
            $current_page = max(1, get_query_var('paged'));
        } else {
            global $query;
        }

        // Set pagination args
        $args = wp_parse_args($args, [
            'mid_size' => 2,
            'prev_next' => true,
            'prev_text' => null,
            'next_text' => null,
            'screen_reader_text' => _x('Posts navigation', 'Frontend - Pagination', 'uicore-elements'),
            'type' => 'array',
            'current' => max(1, get_query_var('paged')),
        ]);


        // Build woo pagination args
        if ($is_product) {

            if ($total && $total <= 1) {
                return;
            }

            $args = apply_filters('woocommerce_pagination_args', [
                // WPCS: XSS ok.
                'current' => $current_page,
                'total' => $total,
                'prev_text' => '',
                'next_text' => '',
                'type' => 'array',
                'base'    => esc_url_raw(add_query_arg('product-page', '%#%', false)),
                'screen_reader_text' => _x('Products navigation', 'Frontend - Pagination', 'uicore-elements'),
            ]);

            if (! wc_get_loop_prop('is_shortcode')) {
                $args['format'] = '';
                $args['base']   = esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))));
            }
        }

        $links = paginate_links($args);
        $class = 'uicore-pagination';
        $class .= !defined('UICORE_ASSETS') ? ' ui-e-pagination' : '';

        if (is_array($links) || is_object($links)) { ?>
            <nav aria-label="<?php echo esc_attr($args['screen_reader_text']); ?>" class="<?php echo esc_attr($class); ?>">
                <ul>
                    <?php foreach ($links as $key => $link) :
                        // If next/prev buttons and Uicore Framework is not active, get arrow icon and append it
                        if ((strpos($link, 'prev') || strpos($link, 'next')) && !defined('UICORE_ASSETS')) {
                            $svg  = \file_get_contents(UICORE_ELEMENTS_ASSETS . '/media/svg/pagination-icon.svg');
                            $link = str_replace('</a>', $svg . '</a>', $link);
                        }
                    ?>
                        <li class="uicore-page-item <?php echo strpos($link, 'current') ? 'uicore-active' : ''; ?>">
                            <?php echo wp_kses_post(str_replace('page-numbers', 'uicore-page-link', $link)); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php }
    }
    function render_load_more($postID)
    {
        // Script may get the current page ID to set as exclusion list on related posts query
        ?>
        <nav data-ui-e-current-page="<?php echo esc_attr($postID); ?>" aria-label="Posts navigation" class="ui-e-pagination">
            <button class="ui-e-load-more elementor-button">Load more</button>
        </nav>
<?php
    }

    /**
     * Render pagination for post/product widgets
     *
     * @param array $settings - The settings of the widget
     * @param int $postID - The ID of the post or product
     * @param bool $is_product - Whether the pagination is for products. Default is false.
     */
    function TRAIT_render_pagination($settings, $postID, $is_product = false)
    {
        if ($this->is_option('pagination', 'yes')) {
            if ($this->is_option('pagination_type', 'load_more')) {
                $this->render_load_more($postID);
            } else {
                $this->render_numbers($is_product);
            }
        }
    }
}
