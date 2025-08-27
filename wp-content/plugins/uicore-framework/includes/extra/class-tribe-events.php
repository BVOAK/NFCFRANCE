<?php
namespace UiCore;

defined('ABSPATH') || exit();


/**
 *  Tribe Events (the events calendar) Support
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since [currentVersion]
 */
class TribeEvents
{

    function __construct()
    {
        add_action('wp_enqueue_scripts', function(){

            $assets = [
                'tribe-events-v2-single-skeleton-full', // single page style
                'tribe-events-views-v2-full' // archive page style
            ];

            // Push new color palette to the assets
            foreach($assets as $asset_name){
                $asset = wp_styles()->get_data($asset_name, 'after');
                if (!$asset) {
                    $asset = array();
                }
                array_push($asset, self::load_color_palette());
                wp_styles()->add_data($asset_name, 'after', $asset);
            }
        });
    }

    /**
     * Default color from Global Colors
     *
     * @return string (css)
     * @author Lucas Marini <lucas@uicore.co>
     * @since [currentVersion]
     */
    static function load_color_palette()
    {

        $body_rgb = self::get_rgb_from_color(Helper::get_option('bColor'));
        $primary_rgb =  self::get_rgb_from_color(Helper::get_option('pColor'));
        $dark_rgb = self::get_rgb_from_color(Helper::get_option('dColor'));

        $css = "body{";
        // text colors
        $css .= " --tec-color-text-primary: var(--uicore-headline-color);";
        $css .= " --tec-color-text-primary-light: rgba(".$body_rgb.", .62);";
        $css .= " --tec-color-text-secondary: var(--uicore-secondary-color);";
        $css .= " --tec-color-text-disabled: rgba(".$body_rgb.", .7);";
        $css .= " --tec-color-text-event-title: var(--uicore-headline-color);";
        $css .= " --tec-color-text-event-date: var(--uicore-body-color);";

        // icon colors
        $css .= " --tec-color-icon-primary: var(--uicore-secondary-color);";
        $css .= " --tec-color-icon-secondary: var(--uicore-primary-color);";
        $css .= " --tec-color-icon-active: var(--uicore-accent-color);";
        $css .= " --tec-color-icon-disabled: var(--uicore-dark-color);";
        $css .= " --tec-color-icon-focus: var(--uicore-accent-color);";
        $css .= " --tec-color-icon-error: var(--uicore-dark-color);";
        $css .= " --tec-color-event-icon-hover: var(--uicore-accent-color);";

        // accents and buttons colors
        $css .= " --tec-color-accent-primary: var(--uicore-primary-color);";
        $css .= " --tec-color-accent-secondary: var(--uicore-primary-color);";
        $css .= " --tec-color-button-primary: var(--uicore-primary-color);";
        $css .= "--tec-color-background-events-bar-submit-button: var(--uicore-primary-color);";
        $css .= "--tec-color-background-events-bar-submit-button-hover: rgba(".$primary_rgb.", .75);";

        // links and borders colors
        $css .= " --tec-color-link-primary: var(--uicore-primary-color);";
        $css .= " --tec-color-link-accent: var(--uicore-accent-color);";

        // borders, backgrounds and shadows colors
        $css .= " --tec-color-border-default: rgba(" . $body_rgb . ", .15);";
        $css .= " --tec-color-border-secondary: rgba(" . $body_rgb . ", .15);";
        $css .= " --tec-color-border-active: var(--uicore-dark-color);";
        $css .= " --tec-color-background: var(--uicore-white-color);";
        $css .= " --tec-color-background-secondary: var(--uicore-white-color);";
        $css .= " --tec-color-background-secondary-hover: var(--uicore-white-color);";
        $css .= " --tec-color-background-primary-multiday: var(--uicore-primary-color);";
        $css .= " --tec-color-background-primary-multiday-hover:  rgba(".$primary_rgb.", .75);";

        // extras colors
        $css .= " --tec-color-background-secondary-datepicker: var(--uicore-light-color);";
        $css .= " --tec-box-shadow-default: 0 2px 5px 0 rgba(".$body_rgb.", .3);";
        $css .= " --tec-box-shadow-tooltip:  0 2px 12px 0 rgba(".$dark_rgb.", .3);";
        $css .= " --tec-box-shadow-card: 0 1px 6px 2px rgba(".$dark_rgb.", .3);";
        $css .= " --tec-box-shadow-multiday: 16px 6px 6px -2px rgba(".$dark_rgb.", .3);";
        $css .= " --tec-color-background-subscribe-list-item-hover: var(--uicore-light-color);";
        $css .= " --tec-color-border-events-bar: var(--uicore-light-color);";
        $css .= " --tec-color-text-events-bar-input-placeholder: var(--uicore-body-color);";

        // Font family
        $css .= " --tec-font-family-sans-serif: var(--uicore-typography--h2-f);";
        $css .= "}";

        return $css;
    }

    static function get_rgb_from_color($color)
    {
        $color = str_replace('#', '', $color);
        if (strlen($color) > 3) {
            $rgb = array(
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else {
            $color = str_replace('#', '', $color);
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b)
            );
        }
        //retun rgb as string "255,255,255"
        return  implode(',', $rgb);
    }
}
new TribeEvents;