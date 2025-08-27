<?php
namespace UiCore;
defined('ABSPATH') || exit();

/**
 * Here we generate the header
 */
class Extras
{

    function __construct()
    {        
        add_action('uicore_body_end', [$this, 'extras_display']);
    }

    

    /**
     * Check what we need and call it.
     *
     * @return void
     */
    public function extras_display()
    {
        if (Helper::get_option('gen_btop') === 'true') {
            $this->add_back_to_top();
        }
        if (class_exists('WooCommerce')) {
            if (Helper::get_option('woo') === 'true') {
                $this->side_cart();
            }
        }
        
    }

    /**
     * Enqueue Custom js in footer
     *
     * @static
     */
    public function add_back_to_top()
    {
        echo '<div id="uicore-back-to-top" class="uicore-back-to-top uicore-i-arrow';
        if (Helper::get_option('gen_btopm') === 'false') {
            echo ' uicore_hide_mobile ';
        }
        echo '"></div>';
    }

    /**
     * HTML markup for asside cart
     *
     * @return void
     */
    function side_cart()
    {
        ?>
        <div id="cart-wrapper" class="uicore-wrapper"></div>
        <div class="uicore-asside-cart">
            <div class="uicore-asside-cart-inner">
                <div class="uicore-asside-header">
                    <div id="uicore-cart-close" class="uicore-header-inner">
                    ×
                    </div>
                </div>
                <?php
                $instance = [
                    'title' => '',
                ];
                the_widget('WC_Widget_Cart', $instance);?>
            </div>
        </div>
        <?php
    }
}
