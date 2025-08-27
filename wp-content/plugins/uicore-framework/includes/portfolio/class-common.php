<?php
namespace UiCore\Portfolio;
use UiCore\Helper as Helper;

defined('ABSPATH') || exit();


/**
 * Common Function for Portfolio
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.2
 */
class Common
{

    /**
     * Construct Common
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.2
     */
    public function __construct()
    {
        add_action('init', [$this, 'register_portfolio'], 10);
        $this->register_sidebars();
        
    }

    public static function get_portfolio_display_name(){
        $name =  __('Portfolio', 'Frontend - Portfolio name default', 'uicore-framework');
        $slug =  __('portfolio', 'Frontend - Portfolio slug default', 'uicore-framework');

        $portfolio_page = Helper::get_option('portfolio_page');
        if(isset($portfolio_page['id']) && $portfolio_page['id'] != '0'){
            $portfolio_post = apply_filters( 'wpml_object_id', $portfolio_page['id'] , 'post', true );
            $slug = get_post_field( 'post_name', $portfolio_post );
            $name =  get_post_field( 'post_title', $portfolio_post );
        }

        //last chance
        $slug = apply_filters( 'uicore_portfolio_slug', $slug );

        return [
            'name'  => $name,
            'slug'  => $slug
        ];
    }

    /**
     * Register Custom Post for Portfolio
     *
     * @return void
     */
    function register_portfolio()
    {

        $portfolio_config = self::get_portfolio_display_name();
        $name = $portfolio_config['name'];
        $slug = $portfolio_config['slug'];
        $category_slug = apply_filters( 'uicore_portfolio_category_slug', $slug.'-category' );

        register_taxonomy(
            'portfolio_category',
            [],
            [
                'hierarchical' => true,
                // 'labels' => $portfolio_cats,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'show_in_rest' => true,
                'rewrite' => ['slug' => $category_slug ],
            ]
        );

        register_post_type('portfolio', [
            'labels' => [
                'name' => $name,
                'singular_name' => $name,
            ],
            'has_archive' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'taxonomies' => ['portfolio_category'],
            'menu_icon' => 'dashicons-format-gallery',
            'public' => true,
            'rewrite' => [
                'slug' => $slug,
                'with_front' => false
            ],
            'show_in_rest' => true,
            'supports' => [
                'title',
                'editor',
                'excerpt',
                'author',
                'thumbnail',
                'comments',
                'revisions',
                'custom-fields',
                'permalinks',
                'featured_image',
				'page-attributes',
            ],
        ]);

        if(get_option('uicore_needs_flush')){
            flush_rewrite_rules();
            delete_option('uicore_needs_flush');
        }
    }
    /**
     * Register all sidebar needed for portfolio
     *
     * @return void
     */
    function register_sidebars()
    {
        //register Portfolio Post Sidebar
        register_sidebar([
            'name' => 'Portfolio Post',
            'id' => 'portfolio-sidebar',
            'before_widget' => '<div class="uicore-widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="uicore-widget-title">',
            'after_title' => '</h4>',
        ]);
        //register Portfolio Archive Sidebar
        register_sidebar([
            'name' => 'Portfolio Archive',
            'id' => 'portfolio-post-sidebar',
            'before_widget' => '<div class="uicore-widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="uicore-widget-title">',
            'after_title' => '</h4>',
        ]);
    }
}
new Common();