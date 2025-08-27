<?php

namespace UiCoreAnimate;

defined('ABSPATH') || exit();
/**
 * UiCore Utils Functions
 */
class Helper
{
    static function get_split_animations_list()
    {
        $animations = [
            'fadeInUp' => __('Fade In Up', 'uicore-animate'),
            'fadeInUp blur' => __('Fade In Up Blur', 'uicore-animate'),
            'fadeInUp cut' => __('Fade In Up Cut', 'uicore-animate'),
            'fadeInDown' => __('Fade In Down', 'uicore-animate'),
            'fadeInDown cut' => __('Fade In Down Cut', 'uicore-animate'),
            'fadeInLeft' => __('Fade In Left', 'uicore-animate'),
            'fadeInLeft cut' => __('Fade In Left Cut', 'uicore-animate'),
            'fadeInRight' => __('Fade In Right', 'uicore-animate'),
            'fadeInRight cut' => __('Fade In Right Cut', 'uicore-animate'),
            'fadeInUpAlt' => __('Fade In Up Alt', 'uicore-animate'),
            'fadeInUpAlt cut' => __('Fade In Up Alt Cut', 'uicore-animate'),
            'fadeIn' => __('Fade In', 'uicore-animate'),
            'fadeIn blur' => __('Fade In Blur', 'uicore-animate'),
            'zoomIn' => __('Zoom In', 'uicore-animate'),
            'scaleIn' => __('Scale In', 'uicore-animate'),
            'rollIn' => __('Roll In', 'uicore-animate'),
            'zoomOut' => __('Zoom Out', 'uicore-animate'),
            'zoomOutDown' => __('Zoom Out Down', 'uicore-animate'),
            'zoomOutLeft' => __('Zoom Out Left', 'uicore-animate'),
            'zoomOutRight' => __('Zoom Out Right', 'uicore-animate'),
            'zoomOutUp' => __('Zoom Out Up', 'uicore-animate')
        ];
        $new_animations = apply_filters('uicore_split_animations_list', []);
        return array_merge($animations, $new_animations);
    }

    static function get_zoom_out_animations_list()
    {
        return [
            'zoomOut' => 'Zoom Out',
            'zoomOutDown' => 'Zoom Out Down',
            'zoomOutLeft' => 'Zoom Out Left',
            'zoomOutRight' => 'Zoom Out Right',
            'zoomOutUp' => 'Zoom Out Up',
        ];
    }
    static function get_blur_animations_list()
    {
        return [
            'fadeIn blur' => 'Fade In Blur',
            'fadeInUp blur' => 'Fade In Up Blur',
            'fadeInLeft blur' => 'Fade In Left Blur',
            'fadeInRight blur' => 'Fade In Right Blur',
            'fadeInDown blur' => 'Fade In Down Blur',
        ];
    }

    static function get_animations_list()
    {
        $animations = [

            'fadeIn' => 'Fade In',
            'fadeInDown' => 'Fade In Down',
            'fadeInLeft' => 'Fade In Left',
            'fadeInRight' => 'Fade In Right',
            'fadeInUp' => 'Fade In Up',

            'zoomIn' => 'Zoom In',
            'zoomInDown' => 'Zoom In Down',
            'zoomInLeft' => 'Zoom In Left',
            'zoomInRight' => 'Zoom In Right',
            'zoomInUp' => 'Zoom In Up',

            'scaleIn' => 'Scale In',
        ];

        //add zoom out animations
        $animations = array_merge(
            $animations,
            Helper::get_zoom_out_animations_list()
        );

        //add the rest of the list
        $animations = array_merge($animations, [
            'slideInDown' => 'Slide In Down',
            'slideInLeft' => 'Slide In Left',
            'slideInRight' => 'Slide In Right',
            'slideInUp' => 'Slide In Up',

            'rotateIn' => 'Rotate In',
            'rotateInDownLeft' => 'Rotate In Down Left',
            'rotateInDownRight' => 'Rotate In Down Right',
            'rotateInUpLeft' => 'Rotate In Up Left',
            'rotateInUpRight' => 'Rotate In Up Right',
        ]);

        //add blur animations
        $animations = array_merge(
            $animations,
            Helper::get_blur_animations_list()
        );
        $new_animations = apply_filters('uicore_animations_list', []);
        return array_merge($animations, $new_animations);
    }

     /**
     * Check if we're on Elementor Edit or Preview mode.
     *
     * @param bool $server_method - If true, checks for elementor URI request parameters instead of using elementor API.
     *
     * @return bool
     */
    static function is_edit_mode( $server_method = false ) {

        // Cases where Elementor instance is not available
        if($server_method){
            if (
                strpos($_SERVER['REQUEST_URI'], 'elementor') !== false ||
                (
                    strpos($_SERVER['REQUEST_URI'], 'preview') !== false &&
                    strpos($_SERVER['REQUEST_URI'], 'preview_id') !== false &&
                    strpos($_SERVER['REQUEST_URI'], 'preview_nonce') !== false
                )
            ) {
                return true;
            }

            return false;
        }

        // Default elementor method
        $elementor_instance = \Elementor\Plugin::instance();
        if ( $elementor_instance->preview->is_preview_mode() || $elementor_instance->editor->is_edit_mode() ) {
            return true;
        }

        return false;
    }
}
