<?php
namespace UiCore;

use MatthiasMullie\Minify;
defined('ABSPATH') || exit();

/**
 *  Js Util
 */
class JS
{
    private $settings;
    private $global_animations;
    public $files;
    public $js;

    /**
     * Apply the filter to get the class (disabled by default)
     *
     * @param string $item
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.4
     */
    function __construct($settings)
    {
        $this->settings= $settings;
        $this->global_animations = ($settings['animations'] === 'true' && $settings['performance_animations'] === 'true');
        // if(\defined(THEME_NAME) && \THEME_NAME !== 'Brisk'){
            $this->get_the_js_parts();
            $this->get_js_from_settings();
            $this->combine_js();
        // }
    }

    function get_the_js_parts()
    {
        //node upgrade to 20+ removed manifest file
        // $this->files['global'][] = UICORE_PATH . '/assets/js/manifest.min.js';
        $this->files['global'][] = UICORE_PATH . '/assets/js/frontend.min.js';

        if($this->settings['performance_smart_preload'] === 'true')
        {
            $this->files['global'][] = UICORE_PATH . '/assets/js/lib/instant-5.1.0.js';
        }

        /*
        * add some files to global based on settings or
        * just becase you want to improve the performance and add your files as a developer
        */
        $this->files['global'] = apply_filters('uicore_js_global_files', $this->files['global'], $this->settings);
    }

    function get_js_from_settings()
    {
        //only global for now
        $this->js['global'] = $this->global_js();

    }

    function global_js()
    {
        $global_animations = $this->global_animations;
        $settings = $this->settings;
        $js = null;

        //TODO: check if components are enabled
        if($settings['animations'] === 'true'){
            include UICORE_INCLUDES .'/extra/javascript/animations-js.php';
        }

        //Header
        if($settings['header'] === 'true'){
            //Drawer
            if($settings['header_side_drawer'] === 'true'){
                include UICORE_INCLUDES .'/extra/javascript/drawer-js.php';
            }
            include UICORE_INCLUDES .'/extra/javascript/header-js.php';

        }
        if($settings['gen_cursor'] === 'true'){
            $cursor_color = Settings::color_filter($settings['gen_cursor_color']);
            include UICORE_INCLUDES .'/extra/javascript/cursor-js.php';
        }
        if(class_exists('\UiCore\Elementor\Core') && $settings['button_interaction'] != 'none'){
            $css_selectors = str_replace('{{WRAPPER}}','',Elementor\Core::get_buttons_class());
            $css_selectors .= self::get_extra_attract_elemnts();
            include UICORE_INCLUDES .'/extra/javascript/btn-interactions-js.php';
        }

        if(class_exists('WooCommerce')){
            include UICORE_INCLUDES .'/extra/javascript/qty-js.php';
            include UICORE_INCLUDES .'/extra/javascript/cart-js.php';
            include UICORE_INCLUDES .'/extra/javascript/sidebar-toggle-js.php';

            if($settings['woos_ajax_add_to_cart'] === 'true'){
                include UICORE_INCLUDES .'/extra/javascript/ajax-add-to-cart-js.php';
            }

            if($settings['woos_sticky_add_to_cart'] === 'true'){
                include UICORE_INCLUDES .'/extra/javascript/sticky-add_to_cart-js.php';
            }
            if($settings['woocommerce_sidebar_id'] !== 'none' && $settings['header_side_drawer'] != 'true'){
                include UICORE_INCLUDES .'/extra/javascript/drawer-js.php';
            }
        }


        //Custom CSS
        $js .= $settings['customjs'];

        return $js;
    }
    function combine_js()
    {
        foreach($this->files as $type=>$files){

            $minifier = new Minify\JS();
            $minifier->addFile($files);

            if(array_key_exists($type,$this->js)){
                $minifier->add($this->js[$type]);
            }

            $upload_dir = wp_upload_dir();
            $upload_dir = apply_filters('uicore_global_upload_dir', $upload_dir);
            $file = $upload_dir['basedir']."/uicore-".$type.'.js';
            $minifier->minify($file);

        }

    }

    static function get_extra_attract_elemnts(){
        $selectors = [
            '.ui-attract'
        ];
        return ','.implode( ',', $selectors );
    }

}
