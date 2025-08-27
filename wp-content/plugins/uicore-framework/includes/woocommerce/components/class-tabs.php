<?php
namespace UiCore\WooCommerce;

use UiCore\Helper;

defined('ABSPATH') || exit();

/**
 * Woocommerce Single Product Tabs Component.
 *
 * @author Lucas Marini <lucas@uicore.co
 * @since 6.0.0
 */
class Product_Tab
{

    public function __construct() {}

    /**
     * Prepares and initialize everything for the Product Tabs component.
     *
     * @param string $gallery_type The gallery type to be used.
     * @param bool $hook If true, replaces the Woocommerce variation template by the swatch component instead of returning the html markup.
     * @return void/string The gallery HTML markup or void if hook is true.
     */
    public static function init($tab_style, $hook = false)
    {
        // If empty and not hooking, means is coming from widget and is set as "theme default"
        $style = ( empty($tab_style) && !$hook ) ?
                 Helper::get_option('woos_tabs_style') :
                 $tab_style;

        // Check for both theme options keys and widget keys
        $custom_markup = strpos($style, 'accordion') !== false ||
                         strpos($style, 'sections') !== false;

        // Theme actions
        if($hook) {

            if($custom_markup){
                add_action('woocommerce_product_tabs', [self::class, 'tabs_custom_style_markup'], 10);
            }
            self::manage_assets($style);

        // Widget actions
        } else {

            // Remove the custom template hook to avoid conflicts. in case theme options hooks a custom markup but widgets use the default template
            remove_action('woocommerce_product_tabs', [self::class, 'tabs_custom_style_markup'], 10);

            if($custom_markup){
                $tabs = apply_filters('woocommerce_product_tabs', []); // Retrieve product tabs data array
                // TODO: `section` on TB with `accordion` on widget is conflicting. Removing the `tabs_custom_style_markup` hook here don't seen to work
                ob_start();
                self::tabs_custom_style_markup($tabs, $style, true);
                return ob_get_clean(); // we need the ob_start so it returns a string since the elementor widget filters the content. This also counts for the default template.
                // TODO: update, we don't need to return string anymore I believe.
            }

            // Return the default template otherwise
            ob_start();
            \wc_get_template( 'single-product/tabs/tabs.php' );
            return ob_get_clean();
        }
    }

    /**
     * Enqueue the selected tab style asset or dequeue theme style asset if no option is passed.
     *
     * @param string $tab_style The tab style to be used. If false, will dequeue the theme style asset. Default is false.
     * @return void
     */
    public static function manage_assets($tab_style = false)
    {
        // Dequeue actions
        if( $tab_style === false ) {
            $handler = Helper::get_option('woos_tabs_style');
            if ( empty($handler) ) {
                wp_dequeue_style('uicore-product-tabs-horizontal'); // 'horizontal' is set as empty value on class settings
            } else {
                wp_dequeue_style('uicore-product-tabs-' . $handler);
            }
            if ($handler === 'accordion') {
                wp_dequeue_script('uicore-product-tabs');
            }
            return;
        }

        $styles = ['vertical', 'accordion', 'sections']; // horizontal is set as empty

        if ( in_array($tab_style, $styles) ) {
            wp_enqueue_style( 'uicore-product-tabs-' . $tab_style );

            // Enqueue script
            if ( $tab_style === 'accordion' ) {
                wp_enqueue_script('uicore-product-tabs');
            }

        } else if ( empty($tab_style) ) {
            wp_enqueue_style( 'uicore-product-tabs-horizontal' );
        }
    }

    /**
     * Customize WooCommerce tabs markup
     *
     * @param array $tabs - The array of tabs data.
     * @param string $style - The tab style to be used. If false, pulls the style from theme options.
     * @param bool $return_markup - If true, returns the markup instead of an empty array. Default is false.
     *
     * @return array/void The html markup OR an empty array to prevent WooCommerce from outputting the default tabs.
     */
    public static function tabs_custom_style_markup($tabs, $style = false, $return_markup = false)
    {
        // Retrieve the tab style from theme options if no option was passed
        $type = $style === false ? Helper::get_option('woos_tabs_style') : $style;
        $is_first = true; // Flag to add ui-active class to the first tab

        if ( !empty($tabs) ) :

            if( in_array($type, ['accordion', 'uicore-tab-accordion']) ){ ?>
                <div class="woocommerce-ui-accordion woocommerce-tabs">
                    <?php foreach ($tabs as $key => $tab) :
                        //add ui-active class if is the first tab
                        $class = $is_first ? ' ui-active' : '';

                        ?>

                        <div class="ui-accordion">
                            <div class="ui-accordion-header<?php echo $is_first ? ' ui-active' : '' ?>">
                                <?php echo apply_filters('woocommerce_product_' . $key . '_tab_title', $tab['title'], $key); ?>
                            </div>
                            <div class="woocommerce-Tabs-panel" id="tab-<?php echo esc_attr($key); ?>" style="<?php echo $is_first ? '' : 'display:none' ?>">
                                <div class="ui-tabs-content">
                                    <?php call_user_func($tab['callback'], $key, $tab); ?>
                                </div>
                            </div>
                        </div>
                    <?php
                    $is_first = false;
                    endforeach;
                    ?>
                </div>
                <?php

            } else if( in_array($type, ['sections', 'uicore-tab-sections']) ) {
                ?>
                <div class="woocommerce-ui-sections woocommerce-tabs">
                    <?php foreach ($tabs as $key => $tab) : ?>
                        <div class="ui-section">
                            <div class="ui-tabs-content woocommerce-Tabs-panel" id="tab-<?php echo esc_attr($key); ?>">
                                <div class="ui-tabs-content">
                                    <?php call_user_func($tab['callback'], $key, $tab); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php
            }

        endif;

        // Return an empty array to prevent WooCommerce from outputting the default tabs
        // since we're not returning because this function was hooked to the 'woocommerce_product_tabs' filter
        if($return_markup === false){
            return array();
        }
    }
}
new Product_Tab();
