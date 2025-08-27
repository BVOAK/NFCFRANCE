<?php
namespace UiCore;
defined('ABSPATH') || exit();

/**
 * Animations Class ( for frontend )
 */
class Animations
{

    public function __construct()
    {
        //continue only if the animations are enabled
        if(Helper::get_option('animations') === 'true'){

            if (Helper::is_edit_mode(true)) {
                return;
            }

            $is_ham = (strpos(Helper::get_option('header_layout'), 'ham') !== false);

            //Page Transition
            if(!class_exists('\UiCoreAnimate\Base')){
                //page transition fade in does not require js
                if(Helper::get_option('animations_page') != 'fade in'){
                    add_action('uicore_before_content', [$this, 'add_page_transition_script']);
                }
            }

            if($is_ham && Helper::get_option('animations_ham') !== 'fade in'){
                add_action('uicore_after_body_content', function () {
                    echo '<div class="uicore-ham-reveal"></div>';
                });
            }

            add_action('wp_body_open', [$this, 'add_transition_markup']);

            //Add custom classes to body
            add_filter('body_class', [$this, 'add_body_class']);

        }
    }


    function add_transition_markup()
    {
        echo '<div class="uicore-animation-bg"></div>';
    }


    /**
     * Add Conditional body classes
     *
     * @param array $classes
     * @return array $classes
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function add_body_class(array $classes)
    {
        $mobile_submenu_animation = Helper::get_option('animations_submmenu')  === 'slide' ? 'ui-a-dsmm-slide' : 'ui-a-dsmm-expand';

        $newclasses = [
            $mobile_submenu_animation
        ];

        return array_merge($classes, $newclasses);
    }

    /**
     * create the page transition js script
     *
     * @return string with javascript for animations
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.1.0
     */
    function add_page_transition_script()
    {
        $js = null;

        //Page Transition js
        $animation = str_replace(' ','-',Helper::get_option('animations_page'));
        $animation_reversed = null;

        if($animation === 'fade'){
            $animation_reversed = 'document.querySelector("#uicore-page").style.animationDirection = "reverse";';
        }else if($animation === 'reveal'){
            $animation_reversed = 'document.querySelector(".uicore-animation-bg").style.animationName = "uiCoreAnimationsRevealInversed";';
        }else if($animation === 'fade-and-reveal'){
            $animation_reversed = '
			document.querySelector(".uicore-animation-bg").style.animationName = "uiCoreAnimationsFadeT";
			document.querySelector(".uicore-animation-bg").style.animationTimingFunction = "ease-in";
			document.querySelector(".uicore-animation-bg").style.animationDuration = "0.3s";

			';
        }

        if($animation != 'none' && $animation != 'fade'){
            $js .= '
            document.querySelector(".uicore-animation-bg").style.animationPlayState="running";
            document.querySelector(".uicore-animation-bg").style.animationName = "";
            ';
        }
        if($animation != 'none'){
            $js .= '

            window.onbeforeunload = function(e) {
                '.$animation_reversed.'
                document.body.classList.remove("ui-a-pt-'.$animation.'");
                void document.querySelector("#uicore-page").offsetWidth;
                document.body.pointerEvents = "none";
                document.body.classList.add("ui-a-pt-'.$animation.'");
            }
            ';
        }

        echo '<script id="uicore-page-transition">';
        echo "window.onload=window.onpageshow= function() { ";
        echo $js;
        echo ' }; ';
        echo '</script>';
    }
}
