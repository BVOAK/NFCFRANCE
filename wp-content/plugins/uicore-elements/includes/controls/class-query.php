<?php

namespace UiCoreElements\Controls;

use UiCoreElements\Helper;
use Elementor\Control_Select2;

defined('ABSPATH') || exit();

class Query extends Control_Select2
{
    const CONTROL_ID = 'elements_query';

    public function get_type()
    {
        return self::CONTROL_ID;
    }

    /**
     * Get query args for a given post type query
     *
     * @param string $control_id The control slug, should be '{post-type}-filter'. Eg: `product-filter`.
     * @param array $settings The control settings array.
     * @param bool $is_product If the args are for woocommerce products. Default is false.
     */
    public static function get_query_args($control_id, $settings, $is_product = false)
    {
        // get post type
        $post_type = $settings[$control_id . '_post_type'];

        // Add extra settings
        $defaults = [
            $control_id . '_post_type' => $post_type,
            $control_id . '_posts_ids' => [],
            'orderby' => 'date',
            'order' => 'desc',
        ];
        $settings = wp_parse_args($settings, $defaults);

        $paged = self::get_queried_page($settings);

        // Build query args
        $query_args = [
            'post_type' => $post_type,
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'post_status' => 'publish', // Hide drafts/private posts for admins
            'paged' => $paged,
            'ignore_sticky_posts' => true
        ];

        // Get posts per page
        $query_args['posts_per_page'] = isset($settings['item_limit'])
            ? $settings['item_limit']['size']
            : Helper::get_framework_visible_posts($post_type);

        // Update posts quantity to woo requirements
        if ($is_product) {
            $query_args['limit'] = $query_args['posts_per_page'];
            unset($query_args['posts_per_page']);
            unset($query_args['post_type']);
        }

        // Offset arg
        if (isset($settings['offset']) && !empty($settings['offset']['size'])) {
            $query_args['offset'] = $settings['offset']['size'];
        }

        // Sticky arg
        if (isset($settings['sticky']) && $settings['sticky']) {
            $query_args['ignore_sticky_posts'] = false;
        }

        //
        $queried_filters = self::get_queried_filters($settings, $post_type, $control_id);
        if (!empty($queried_filters['tax_query'])) {
            $query_args['tax_query'] = $queried_filters['tax_query'];
        }

        //Enable for data analysis
        // error_log( __FILE__ . '@' . __LINE__ );
        // error_log( __METHOD__);
        // \error_log(print_r($query_args, true));
        // \error_log("-----------------");

        return $query_args;
    }

    /**
     * Get the current page value in a query.
     *
     * @param array $settings The control settings array.
     */
    public static function get_queried_page($settings)
    {
        if (!isset($settings['__current_page'])) {
            if (get_query_var('paged')) {
                $paged = get_query_var('paged');
            } elseif (get_query_var('page')) {
                $paged = get_query_var('page');
            } else {
                $paged = 1;
            }
        } else {
            $paged = $settings['__current_page'];
        }

        return $paged;
    }

    /**
     * Build the `tax_query` args to work with filter component.
     *
     * @param array $settings The control settings array.
     * @param string $post_type The post type slug.
     * @param string $control_id The control slug. Can be 'posts-type' or 'product-filter'.
     *
     * @return array The query args.
     */
    public static function get_queried_filters($settings, $post_type, $control_id)
    {
        $args = [
            'post_type' => $post_type,
            'tax_query' => [],
        ];

        if (
            isset($settings['post_filtering']) &&
            $settings['post_filtering'] &&
            isset($_GET['tax']) && //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            isset($_GET['term']) //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ) {

            $args['tax_query'][] = [
                'taxonomy' => sanitize_text_field(wp_unslash($_GET['tax'])), //phpcs:ignore WordPress.Security.NonceVerification.Recommended
                'field'    => 'term_id',
                'terms'    => intval($_GET['term']), //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ];
        } else {
            $taxonomies = get_object_taxonomies($post_type, 'objects');

            foreach ($taxonomies as $object) {
                $setting_key = $control_id . '_' . $object->name . '_ids';

                if (!empty($settings[$setting_key])) {
                    $terms_list = $settings[$setting_key];

                    if (!is_array($terms_list)) {
                        $terms_list = explode(',', $terms_list);
                    }

                    $args['tax_query'][] = [
                        'taxonomy' => $object->name,
                        'field'    => 'term_id',
                        'terms'    => array_map('intval', $terms_list),
                    ];
                }
            }
        }

        // Set `AND` relation if we're working with multiple tax queries
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        };

        return $args;
    }

    /**
     * Get all products from a given product query and return the total amount of pages for it. Only for woo product queries.
     *
     * TODO: investigate more performatic approaches, not only we're running a query for the second time,
     * but we're using -1, wich means a big CPU and memory usage.
     */
    public static function get_total_pages($default_query)
    {
        // Set non-limit posts and a light return type
        $calc_args = [
            'limit' => '-1',
            'return' => 'ids'
        ];
        $calc_args = array_merge($default_query, $calc_args);

        // Makes sure we have a limit set
        if (isset($default_query['limit']) || isset($default_query['posts_per_page'])) {
            $limit = isset($default_query['limit'])
                ? $default_query['limit']
                : $default_query['posts_per_page'];

            // Get limit from framework otherwise
        } else {
            $limit = Helper::get_framework_visible_posts('product');
        }

        // Get total pages value
        $total_products = wc_get_products($calc_args);
        $total = ceil(count($total_products) / $limit);

        return $total;
    }
}

\Elementor\Plugin::$instance->controls_manager->register_control('elements_query', new Query());
