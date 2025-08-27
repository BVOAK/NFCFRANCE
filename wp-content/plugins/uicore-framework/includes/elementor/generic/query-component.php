<?php
namespace UiCore\Elementor\Generic;

use UiCore\Elementor\Generic\Product_Filter;
use UiCOre\Elementor\Generic\Query;

defined('ABSPATH') || exit();

/**
 * Query Component - Based on Uicore Elements Post Component
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */

trait Query_Trait {

    /**
     * Register the Query Controls
     *
     * @param boolean $section - If true, the controls will be wrapped in a separated section.
     */
    function TRAIT_register_post_query_controls($section = true)
    {
        if($section){
            $this->start_controls_section(
                'section_query',
                [
                    'label' => esc_html__('Query', 'uicore-framework'),
                ]
            );
        }

            $this->add_group_control(
                Product_Filter::get_type(),
                [
                    'name' => 'product_query',
                    'label' => esc_html__('Products', 'uicore-framework'),
                    'description' => esc_html__('Current Query Settings > Reading', 'uicore-framework'),
                ]
            );

            $this->add_control(
                'item_limit',
                [
                    'label' => esc_html__('Item Limit', 'uicore-framework'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'reder_type' => 'template',
                    'range' => [
                        'px' => [
                            'min' => -1,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'size' => 3,
                    ],
                    'condition' => array(
                        'product_query_post_type!' => 'current',
                    ),
                ]
            );

            // $this->add_control(
            //     'offset',
            //     [
            //         'label' => esc_html__('Query Offset', 'uicore-framework'),
            //         'type' => \Elementor\Controls_Manager::SLIDER,
            //         'render_type' => 'template',
            //         'range' => [
            //             'px' => [
            //                 'min' => -1,
            //                 'max' => 10,
            //             ],
            //         ],
            //         'default' => [
            //             'size' => 0,
            //         ],
            //         'condition' => [
            //             'pagination_type' => 'numbers',
            //         ]
            //     ]
            // );

            // $this->add_control(
            //     'offset_alert',
            //     [
            //         'type' => \Elementor\Controls_Manager::ALERT,
            //         'alert_type' => 'info',
            //         'content' => esc_html__('Offset is disabled with Load More pagination.', 'uicore-framework'),
            //         'condition' => [
            //             'pagination_type!' => 'numbers',
            //         ]
            //     ]
            // );

            // $this->add_control(
            //     'sticky',
            //     [
            //         'label' => esc_html__('Sticky Posts', 'uicore-framework'),
            //         'type' => \Elementor\Controls_Manager::SWITCHER,
            //         'label_on' => esc_html__('Show', 'uicore-framework'),
            //         'label_off' => esc_html__('Hide', 'uicore-framework'),
            //         'description' => esc_html__('Sticky posts works only on the front-end.', 'uicore-framework'),
            //         'return_value' => 1,
            //         'default' => 0,
            //         'condition' => [
            //             'pagination!' => 'yes',
            //         ],
            //     ]
            // );

        if($section){
            $this->end_controls_section();
        }
    }

    // Helper functions
    function TRAIT_query_product($settings)
    {
        $post_type = $settings['product_query_post_type'];
        if ( $post_type === 'related' ) {
            $this->_query = $this->get_related('random', $settings['item_limit']['size']);

        } else {
            if ($post_type === 'current' && !$this->is_edit_mode()) {
                global $wp_query;
                $this->_query = $wp_query;
            }else{
                $query_args = Query::get_query_args('product_query', $settings, get_the_ID());

                //allways set the post type to product
                $query_args['post_type'] = 'product';
                $this->_query = new \WP_Query($query_args);
            }

        }
    }
    function get_related($filter, $number)
    {
        global $post;

        $args = [];

        if ($filter == 'category') {
            $categories = get_the_category($post->ID);

            if ($categories) {
                $category_ids = [];
                foreach ($categories as $individual_category) {
                    $category_ids[] = $individual_category->term_id;
                }

                $args = [
                    'category__in' => $category_ids,
                    'post__not_in' => [$post->ID],
                    'posts_per_page' => $number,
                    'ignore_sticky_posts' => 1,
                ];
            }
        } elseif ($filter == 'tag') {
            $tags = wp_get_post_tags($post->ID);

            if ($tags) {
                $tag_ids = [];
                foreach ($tags as $individual_tag) {
                    $tag_ids[] = $individual_tag->term_id;
                }
                $args = [
                    'tag__in' => $tag_ids,
                    'post__not_in' => [$post->ID],
                    'posts_per_page' => $number,
                    'ignore_sticky_posts' => 1,
                ];
            }
        } else {
            $args = [
                'post__not_in' => [$post->ID],
                'posts_per_page' => $number,
                'orderby' => 'rand',
            ];
        }

        $related_query = new \wp_query($args);

        if ($related_query->have_posts()) {
            return $related_query;
        } else {
            return false;
        }
    }
}