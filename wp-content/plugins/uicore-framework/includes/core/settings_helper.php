<?php

namespace UiCore;


defined('ABSPATH') || exit();

/**
 * Brisk Settings Utils
 */
class Settings_Helper
{

    public static function get_all_settings($default,$option_name)
    {
        $settings = get_option($option_name);

        if(!is_array($settings)){
            $settings = [];
        }
        
        return \wp_parse_args($settings, $default);
    }

    public static function update_all_settings($default, $option_name, $settings)
    {

        $db_settings = self::get_all_settings($default,$option_name);

        foreach($default as $key => $value){

            if(isset($settings[$key]) ){
                if(Settings::is_not_default($value, $settings[$key]) ){
                    $db_settings[$key] = $settings[$key];
                }else{
                    unset($db_settings[$key]);
                }
            }elseif( !Settings::is_not_default($value, $db_settings[$key])) {
                unset($db_settings[$key]);
            }
        
		}

        update_option( $option_name, $db_settings);

    }

}
