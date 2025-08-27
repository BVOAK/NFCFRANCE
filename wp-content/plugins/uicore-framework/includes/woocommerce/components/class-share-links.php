<?php
namespace UiCore\WooCommerce;

use UiCore\Utils;
use UiCore\Helper;

defined('ABSPATH') || exit();


/**
 * Woocommerce share product links Component.
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */
class ShareLinks
{

    public function __construct() {}

    /**
     * Hook the Share Buttons component markup.
     *
     * @return void
     */
    public static function init() {
        if (Helper::get_option('woos_share') === 'true') {
            add_action('woocommerce_share', [self::class, 'print_share_links'], 10);
        }
    }

    /**
     * Returns the data used to build the share button, for each platform.
     *
     * @return array
     */
    public static function get_platforms_data() {

        $product_url   = get_the_permalink();
        $product_title = get_the_title();
        $social_icons  = Utils::get_social_icons(true);

        // Not all social platforms from $social_icons can be used to share links
        $accepted_plataforms = [
            'Facebook'  => [],
            'Tweeter'   => [],
            'Pinterest' => [],
            'LinkedIn'  => [],
            'Whatsapp'  => [],
            'Telegram'  => [],
        ];

        // Mail is not a social platform, so we add it manually
        // $platforms[] = [
        //     'label' => __('Share this product via email', 'uicore-framework'),
        //     'class' => 'uicore-i-mail',
        //     'url' => 'mailto:?subject=' . esc_html($product_title) . '&body=' . esc_url($product_url)
        // ];

        foreach ($social_icons as $class => $name) {

            // Build URL
            switch($name) {
                case 'Facebook':
                    $url = 'https://www.facebook.com/sharer/sharer.php?u=' . esc_url($product_url);
                    break;

                case 'Tweeter': // Is not a typo, the platform is named this way on the social icons list
                    $url = 'https://x.com/intent/tweet?url=' . esc_url($product_url);
                    break;

                case 'Pinterest':
                    $url = 'https://pinterest.com/pin/create/button/?url=' . esc_url($product_url) . '&media=' . get_the_post_thumbnail_url() . '&description=' . esc_html($product_title);
                    break;

                case 'LinkedIn':
                    $url = 'https://www.linkedin.com/shareArticle?mini=true&url=' . esc_url($product_url) . '&title=' . esc_html($product_title);
                    break;

                case 'Whatsapp':
                    $url = 'https://api.whatsapp.com/send?text=' . esc_url($product_url);
                    break;

                case 'Telegram':
                    $url = 'https://t.me/share/url?url=' . esc_url($product_url) ;
                    break;

                default:
                    $url = false;
                    break;
            }

            // Ignore the platform if not accepted
            if (!array_key_exists($name, $accepted_plataforms) || !$url) {
                continue;
            }

            $platforms[] = [
                'label' => sprintf( __('Share this product on %s', 'uicore-framework'), $name),
                'class' => $class,
                'url' => $url
            ];
        }

        return $platforms;
    }

    /**
     * Print the product share links section.
     *
     * @return string The share component HTML markup.
     */
    public static function print_share_links() {

        $platforms = self::get_platforms_data();

        $buttons = array_map( function($button) {
            return sprintf(
                        '<a href="%s"
                            target="_blank"
                            rel="noopener noreferrer"
                            title="%s"
                            class="uicore-social-icon uicore-link %s"> </a>',
                            $button['url'],
                            $button['label'],
                            $button['class']
                    );
            },
            $platforms
        );

        echo '<div class="uicore-share-product">' . implode('', $buttons) . '</div>';
    }

}
new ShareLinks();