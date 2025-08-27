<?php

namespace UiCore\WooCommerce;

defined('ABSPATH') || exit();

/**
 * UiCore Utils Functions
 */
class Settings
{


    /**
     * Constructor function to initialize hooks
     *
     * @return void
     */
    public function __construct()
    {
        \add_filter('uicore_extra_settings', [$this, 'extra_settings']);

        //enable autoload on frontend module
        $module = UICORE_SETTINGS . '_woo';
        \add_filter('_uicore_' . $module . '_autoload', '__return_true');

        //add the module frontend options to all fontend options
        \add_filter('_uicore_front_options_all', [$this, 'add_frontend_options_to_all']);

    }


    /**
     * Adds extra settings to the given list of options.
     *
     * @param array $list The list of options to add the extra settings to.
     * @return array The updated list of options with the extra settings.
     */
    function extra_settings($list)
    {
        if(!\class_exists('WooCommerce')){
            return $list;
        }

        $new_modules_list = \wp_parse_args(
            $list,
            [
                UICORE_SETTINGS . '_woo' => self::get_front_default_settings(),
                UICORE_SETTINGS . '_woo_admin' => self::get_admin_default_settings(),
            ]
        );
        return $new_modules_list;
    }

    /**
     * Retrieves the default settings for the UI Core Animate plugin.
     *
     * @param string|null $key The specific setting key to retrieve. If null, returns the entire settings list.
     * @return mixed The value of the specified setting key, or the entire settings list if $key is null.
     */
    static function get_front_default_settings()
    {
        $list = [
            'woocommerce_col'           => '3',
            'woocommerce_posts_number'  => '12',
            'woocommerce_sidebar_id'    => 'none',
            'woocommerce_sidebar'       => 'left',
            'woocommerce_sidebars'      => 'true',
            'woo_swatch_inherit_image'  => 'true',
            'woo_filters_toggle'        => 'true',
            'woo_rating'                => 'true',
            'woo_swatches'              => 'false',
            'woo_quick_desc'            => 'false',
            'woo_hover_effect'          => 'zoom',
            'woo_add_to_cart_style'     => 'reveal',


            'woocommerces_sidebar_id'   => 'none',
            'woocommerces_sidebar'      => 'left',
            'woocommerces_sidebars'     => 'true',
            'woocommerces_title'        => 'default page title',
            'woos_tabs_position'        => '',
            'woos_tabs_style'           => '',
            'woos_product_gallery'      => '',
            'woos_share'                => 'false',
            'woos_rating_style'         => 'stars',
            'woos_related'              => 'true',
            'woos_ajax_add_to_cart'     => 'true',
        ];

        return $list;
    }

    static function get_admin_default_settings()
    {
        $list = [
            'woocommerce_padding'       => [
                'd' => '100',
                't' => '75',
                'm' => '45',
            ],
            'woo_item_style'            => 'default',
            'woo_img_radius'            => '0',
            'woo_grid_gap'              => '30',
            'woo_title'                 => [
                'f' => 'Primary',
                's' => [
                  'd' => '22',
                  't' => '20',
                  'm' => '18',
                ],
                'h' => '1.2',
                'ls' => '0',
                't' => 'None',
                'st' => '600',
                'c' => 'Headline',
            ],
            'woo_price'                 => [
                'f' => 'Primary',
                's' => [
                  'd' => '14',
                  't' => '14',
                  'm' => '14',
                ],
                'h' => '1.2',
                'ls' => '0',
                't' => 'None',
                'st' => '500',
                'c' => 'Body',
            ],
            'woo_description'           => [
                'f' => 'Text',
                's' => [
                  'd' => '14',
                  't' => '14',
                  'm' => '14',
                ],
                'h' => '1.5',
                'ls' => '0',
                't' => 'None',
                'st' => 'regular',
                'c' => 'Body',
            ],
            'woo_align_center'           => 'false',


            'woos_swatch_size'          => '30',
            'woos_swatch_radius'        => 'medium',
            'woos_swatch_border'        => '1',
            'woos_swatch_border_color'  => '#222222',
            'woos_sticky_add_to_cart'   => 'true',
            'woos_add_to_cart_height'   => '44',
            'woos_category'             => 'true',
            'woos_tags'                 => 'true',
            'woos_sku'                  => 'true',
            'woos_title'                => [
                'f' => 'Primary',
                's' => [
                    'd' => '32',
                    't' => '28',
                    'm' => '24',
                ],
                'h' => '1.44',
                'ls' => '0',
                't' => 'None',
                'st' => '600',
                'c' => 'Headline',
            ],
            'woos_price'                => [
                'f' => 'Primary',
                's' => [
                    'd' => '24',
                    't' => '22',
                    'm' => '20',
                ],
                'h' => '1.44',
                'ls' => '0',
                't' => 'None',
                'st' => '600',
                'c' => 'Headline',
            ],
            'woos_excerpt'              => [
                'f' => 'Text',
                's' => [
                    'd' => '16',
                    't' => '15',
                    'm' => '14',
                ],
                'h' => '1.875',
                'ls' => '0',
                't' => 'None',
                'st' => 'regular',
                'c' => 'Body',
            ],
            'woos_gallery_gap'          => '20',
            'woos_gallery_radius'       => '0',
            'woos_summary_width'        => '37',

        ];
        return $list;
    }



    function add_frontend_options_to_all($list)
    {
        if(!\class_exists('WooCommerce')){
            return $list;
        }

        $db_options = get_option(UICORE_SETTINGS . '_woo',[]);
        $all_options = \wp_parse_args($db_options, self::get_front_default_settings());
        return \wp_parse_args($all_options,$list);
    }
}
new Settings();
