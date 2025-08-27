<?php
namespace UiCore\Blog;

use UiCore\Helper as Helper;

defined('ABSPATH') || exit();


/**
 * Frontend Blog
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.2
 */
class Frontend
{

    /**
     * Google Font Url for Blog
     *
     * @var string|bolean Url if is set false if empty
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    private $blog_google_fonts;

    /**
     * Google Font Url for Blog grid
     *
     * @var string|bolean Url if is set false if empty
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    private $blog_grid_google_fonts;

    /**
     * Construct Frontend
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function __construct()
    {
        //hook on this to have all conditions
        add_action('wp', function () {
            if(self::is_blog()){

                //Enque general scripts and style
                add_action('wp_enqueue_scripts', [$this, 'frontend_css'], 1001);

                //Add custom classes to body
                add_filter('body_class', [$this, 'add_body_class']);
            }
        });
        // $fonts = $post_css_file->enqueue();

        //filter posts number
        add_action('pre_get_posts', [$this, 'filter_posts_number']);

		//limit excerpt length
        add_action('excerpt_length', [$this, 'excerpt_length'], 999);

    }

     /**
     * Enqueue frontend css and js
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.2
     */
    public static function frontend_css($force = false,$custom_item_style = null)
    {
        $blog_google_fonts = get_option('uicore_blog_fonts');
        $blog_grid_google_fonts = get_option('uicore_blog_grid_fonts');

        //If Google font url is setted add it to registred style
        if ($blog_google_fonts && is_singular('post') ) {
            wp_enqueue_style('uicore_blog_fonts', $blog_google_fonts);
        }

        //If Google font url is setted add it to registred style
        if ($blog_grid_google_fonts && (is_archive() || is_home() || is_search() && $force)) {
            wp_enqueue_style('uicore_blog_grid_fonts', $blog_grid_google_fonts);
        }
        wp_enqueue_style('uicore-blog-st');

        
        //Enqueue custom css
        if ($custom_item_style) {
            wp_enqueue_style('uicore-blog-custom-st', \UICORE_ASSETS . '/css/blog/item-style-' . $custom_item_style . '.css', [], UICORE_VERSION);
        }

    }

    /**
     * Add Conditional body classes
     *
     * @param array $classes
     * @return array $classes
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.2
     */
    public function add_body_class(array $classes)
    {
        $single_sidebar = apply_filters('uicore_blogs_sidebar', Helper::get_option('blogs_sidebar_id'), get_post());
        $single_sidebar_pos = Helper::get_option('blogs_sidebar');
        $archive_sidebar = Helper::get_option('blog_sidebar_id');
        $archive_sidebar_pos = Helper::get_option('blog_sidebar');
        $single_narow = Helper::get_option('blogs_narrow');


        $newclasses = [
            //blog
            'uicore-blog',
            is_singular('post') && $single_sidebar !== 'none' ? 'uicore-sidebar-' . $single_sidebar_pos : null,
            is_singular('post') && $single_narow == 'true' ? 'uicore-narow' : null,
            is_home() && $archive_sidebar !== 'none' ? 'uicore-sidebar-' . $archive_sidebar_pos : null,
            is_archive()  && $archive_sidebar !== 'none' ? ' uicore-sidebar-' . $archive_sidebar_pos : null,
            is_search() && $archive_sidebar !== 'none' ? 'uicore-sidebar-' . $archive_sidebar_pos : null, //this helps using the same archieve for search
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
        if (!$query->is_page() && !is_single() && $this->is_blog()) {
            $blog = Helper::get_option('blog_posts_number');
            if ($query->is_main_query()) {
                $query->set('posts_per_page', $blog);
            }
        }
    }

    /**
     * Change excerpt length on blog page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.2
     */
    function excerpt_length($length)
    {
        $length = Helper::get_option('blog_excerpt_length');
        return $length;
    }

    static function is_blog()
    {
        $is_not_shop = true;
        if( function_exists('is_shop') && function_exists('is_product_taxonomy') ) {
            $is_not_shop = ( is_shop() || is_product_taxonomy() );
        }
        $enable_blog_single_styles = (\UiCore\Helper::get_option('blogs_styles_tb') == 'true') ? false : null;
        $is_theme_builder_template = $enable_blog_single_styles ?? apply_filters('uicore_is_template', false);
        return !$is_theme_builder_template && (is_category() ||
            is_day() ||
            is_month() ||
            is_author() ||
            is_year() ||
            is_tag() ||
            is_home() ||
            is_singular('post') ||
			(is_archive() && !($is_not_shop || (\class_exists('\UiCore\Portfolio\Frontend') && \UiCore\Portfolio\Frontend::is_portfolio())) ) ||
            is_search());
    }

}
