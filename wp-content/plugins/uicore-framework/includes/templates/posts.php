<?php
namespace UiCore;
defined('ABSPATH') || exit();

/**
 * Here we generate the header
 */
class Posts
{
    function __construct()
    {
       
        if (!class_exists('Uicore\Pagination')) {
            require UICORE_INCLUDES . '/templates/pagination.php';
        }
        $is_theme_builder_template = apply_filters('uicore_is_template', false);
        if($is_theme_builder_template){
            do_action('uicore_do_template');
        }else{
            if ((is_singular('portfolio') || is_post_type_archive('portfolio') || is_tax('portfolio_category')) && Helper::get_option('disable_portfolio') === 'false' ) {
                new Portfolio\Template();
            } else if( (Helper::get_option('disable_blog') === 'false') && Blog\Frontend::is_blog()) {
                new Blog\Template();
            }else {
                new Pages();
            }
        }

    }
}
