<?php
namespace UiCore;

defined('ABSPATH') || exit();


/**
 *  Admin Customizer Functions
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 3.0.0
 */
class AdminCustomizer
{
    private static $instance;
    private static $data;
	/**
	 * Init
	 *
	 * @return mixexd
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    function __construct()
    {
        if(self::get_prop('admin_customizer') === 'true'){
            //Theme Name
            if(self::get_prop('theme_name')){
                add_filter('uicore_theme_name', function ($default){
                    return self::get_prop('theme_name');
                });
            }

            //Admin Icon
            if(self::get_prop('admin_icon')){
                add_filter('uicore_theme_icon_url', function ($default){
                    return self::get_prop('admin_icon');
                });
            }

            //Wp Login
            add_action('login_enqueue_scripts', [$this, 'wp_login_css']);

            
        }

        //Element Pack Remove from menu
        if (self::get_prop('to_ep') === 'true' ){
            add_action('admin_menu', [$this, 'remove_ep_menu'], 203);
        }

    }

    static function get_data()
    {
        if(!is_array(self::$data)){
            self::$data = ThemeOptions::get_admin_customizer_db_options();
        }
        return self::$data;
    }

    static function get_prop($prop, $fallback = false)
    {
        $data = self::get_data();
        return isset($data[$prop]) ? $data[$prop] : $fallback;
    }

    function wp_login_css()
    {
        $css = null;

        if(self::get_prop('wp_logo')){
            $css .='
            #login h1 a, .login h1 a {
                background-image: url('.self::get_prop('wp_logo').');
                height:'.self::get_prop('wp_logo_height','40').'px;
                width: 100%;
                background-repeat: no-repeat;
                background-size: contain
            }
            ';
        }
        if(self::get_prop('wp_background')){
            $css .='
            body{
                background: '.self::get_prop('wp_background').'!important;
            }
            ';
        }
        if(self::get_prop('wp_text_bg_color')){
            $css .='
            .login #backtoblog a,
            .login #nav a{
                color: '.self::get_prop('wp_text_bg_color').' !important;
            }
            .login #backtoblog a:hover,
            .login #nav a:hover {
                color: '.self::get_prop('wp_text_bg_color').' !important;
                opacity:.75;
            }
            ';
        }

        if(self::get_prop('wp_form_background')){
            $css .='
            .login form,
            .login #login_error, .login .message, .login .success,
            .login form .input, .login form input[type=checkbox], .login input[type=text]{
                background: '.self::get_prop('wp_form_background').'!important;
                border:1px solid #8c8f9470;
                border-radius:4px;
            }
            .login form{
                border:1px solid #8c8f9421!important;
            }
            .login #login_error, .login .message, .login .success{
                border-color:transparent;
            }
            ';
        }
        if(self::get_prop('wp_text_color')){
            $css .='
            .login,
            .login form input{
                color: '.self::get_prop('wp_text_color').';
            }
            ';
        }

        if(self::get_prop('wp_button_background')){
            $css .='
            .wp-core-ui .button.button-primary{
                background: '.self::get_prop('wp_button_background').'!important;
                border: '.self::get_prop('wp_button_background').'!important;
                color:white!important;
            }
            .wp-core-ui .button-primary:hover{
                background: '.self::get_prop('wp_button_background').'!important;
                border: '.self::get_prop('wp_button_background').'!important;
                opacity:.75;
            }
            .wp-core-ui .button{
                color: '.self::get_prop('wp_button_background').'!important;
                border: '.self::get_prop('wp_button_background').'!important;
            }
            input[type=checkbox]:focus, input[type=color]:focus, input[type=date]:focus, input[type=datetime-local]:focus, input[type=datetime]:focus, input[type=email]:focus, input[type=month]:focus, input[type=number]:focus, input[type=password]:focus, input[type=radio]:focus, input[type=search]:focus, input[type=tel]:focus, input[type=text]:focus, input[type=time]:focus, input[type=url]:focus, input[type=week]:focus, select:focus, textarea:focus {
                border-color: '.self::get_prop('wp_button_background').'!important;
                box-shadow: 0 0 0 1px  '.self::get_prop('wp_button_background').'!important;
            }
            ';
        }

        if($css){
            echo '<style id="uicore-custom-login" type="text/css">';
            echo $css;
            echo '</style>';
        }

    }

    function remove_ep_menu () 
    {
       remove_menu_page('element_pack_options');
    } 

}
AdminCustomizer::get_instance();
