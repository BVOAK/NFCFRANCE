<?php
namespace UiCore\Portfolio;
use UiCore\Helper as Helper;

defined('ABSPATH') || exit();


/**
 * Frontend Portfolio
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.2
 */
class Frontend
{

    /**
     * Construct Frontend
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.2
     */
    public function __construct()
    {
        //hook on this to have all conditions
        add_action('wp', function () {
            if(self::is_portfolio()){
                //Enque general scripts and style
                add_action('wp_enqueue_scripts', [$this, 'frontend_css'], 1001);

                //Add custom classes to body
                add_filter('body_class', [$this, 'add_body_class']);

            }
        });

        //filter posts number
        add_action('pre_get_posts', [$this, 'filter_posts_number']);
    }


     /**
     * Enqueue frontend css and js
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.2
     */
    public static function frontend_css()
    {
        wp_enqueue_style('uicore-portfolio-st');
    }


    /**
     * Add Conditional body classes
     *
     * @param array $classes
     * @return array $classes
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function add_body_class(array $classes)
    {
        $portfolio_single_sidebar = Helper::get_option('portfolios_sidebar_id') !== 'none' ? true : false;
        $portfolio_single_sidebar_pos = Helper::get_option('portfolios_sidebar');
        $portfolio_width = Helper::get_option('portfolio_full_width');
        $portfolio_archive_sidebar = Helper::get_option('portfolio_sidebar_id');
        $portfolio_archive_sidebar_pos = Helper::get_option('portfolio_sidebar');


        $newclasses = [
            'uicore-portfolio',
            !is_singular('portfolio') && $portfolio_width == 'true'
            ? 'uicore-full-width'
            : null,
            !is_singular('portfolio') &&  $portfolio_archive_sidebar !== 'none'
                ? 'uicore-sidebar-' . $portfolio_archive_sidebar_pos
                : null,
            is_singular('portfolio') && $portfolio_single_sidebar
                ? 'uicore-sidebar-' . $portfolio_single_sidebar_pos
                : null,

        ];

        return array_merge($classes, $newclasses);
    }

    /**
     * Filter posts to return a number that fit's the grid.
     *
     * @param object $query
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.2
     */
    function filter_posts_number($query)
    {
        if(is_admin()){
            return $query;
        }
        if (!$query->is_page() && !is_single() && self::is_portfolio()) {
            $portfolio = Helper::get_option('portfolio_posts_number');
            if ($query->is_main_query()) {
                $query->set('posts_per_page', $portfolio);
				$query->set('orderby', 'menu_order date');
            }
        }
    }

    static function is_portfolio()
    {
        $is_theme_builder_template = apply_filters('uicore_is_template', false);
        return !$is_theme_builder_template && (is_post_type_archive('portfolio') || is_tax('portfolio_category') || is_singular('portfolio') );
    }

    static function get_portfolio_page_id()
    {
        $portfolio_post = 0;
        $portfolio_page = Helper::get_option('portfolio_page');
        if(isset($portfolio_page['id']) && $portfolio_page['id'] != '0'){
            $portfolio_post = apply_filters( 'wpml_object_id', $portfolio_page['id'] , 'post', true );
        }
        return $portfolio_post;
    }

}
