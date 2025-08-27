<?php
namespace UiCore;
defined('ABSPATH') || exit();

/**
 * Here we generate the footer
 */
class Footer
{

    function __construct()
    {

        //Hook this to init to get is_user_logged_in() -> for maintenance mode
        // add_action('wp_loaded', function () {

            /*
            * Theme builder footer priority = 5;
            * Theme Builder footer can remove this action if needed
            */            
            add_action('uicore_content_end', [$this, 'footer_display'], 10);
            
        // });
    }

    public function footer_display()
    {
        //continue only if is not in maintenance mode
        $is_maintenance = Helper::get_option('gen_maintenance') == 'false';
        if (!$is_maintenance && !is_user_logged_in()) {
            return;
        }
        
        $enable = \apply_filters('uicore-footer-cache', true);

        //Elementor PRO Theme Builder First!!! 
        if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
            global $post;
            //check if post id is setted if not return 0
            $post_id = $post->ID ?? 0;
            $footer = Helper::po('footer', 'footer', 'true', $post_id);
            $copyrights = Helper::po('copyright', 'copyrights', 'true', $post_id);
            $show_footer = ($footer == 'true' || $copyrights == 'true');
            $show_footer = apply_filters('uicore_is_footer', $show_footer);
            if ($show_footer) {

                //start the footer markup
                echo '<footer class="uicore-footer-wrapper">';

                if ($footer == 'true') {
                    if (!($output = $enable ? get_transient('uicore-footer-markup') : false)) {
                        ob_start();
                        $this->footer();
                        $output = ob_get_clean();
                        set_transient('uicore-footer-markup', $output, DAY_IN_SECONDS);
                    }
                    echo $output;
                }

                if ($copyrights == 'true') {
                    $this->copyrights();
                }

                echo '</footer>';
            }
        }
    }

    /**
     * Render our search html
     *
     * @return void
     */
    public function footer()
    {
        //get the footer column number
        $layout = Helper::get_option('footer_layout');
        if ($layout == 'one') {
            $n = 1;
        }

        if ($layout != 'one') {
            $n = 2;
        }

        if ($layout != 'two' && $layout != 'two_left' && $layout != 'two_right' && $layout != 'one') {
            $n = 3;
        }

        if (
            $layout == 'four' ||
            $layout == 'four_left' ||
            $layout == 'four_right' ||
            $layout == 'four_left_2' ||
            $layout == 'four_right_2'
        ) {
            $n = 4;
        }

        if ($layout == 'five' || $layout == 'five_left' || $layout == 'five_right') {
            $n = 5;
        }

        if (
            !(
                is_active_sidebar('footer-1') ||
                is_active_sidebar('footer-2') ||
                is_active_sidebar('footer-3') ||
                is_active_sidebar('footer-4') ||
                is_active_sidebar('footer-5')
            )
        ) {
            return;
        }

        ?>
        <div class="uicore uicore-inner-footer uicore-section <?php if (Helper::get_option('footer_wide') === 'false') {
            echo 'uicore-box ';
        } ?>">
            <div class="uicore uicore-container <?php echo $layout; ?> ">

            <?php for ($i = 1; $i <= $n; $i++) {
                echo '<div class="uicore-footer-column uicore-animate">';
                dynamic_sidebar('footer-' . $i);
                echo '</div>';
            } ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render our search html
     *
     * @return void
     */
    public function copyrights()
    {
        $have_social = Helper::get_option('copyrights_icons') === 'true'; ?>


        <div class="uicore uicore-copyrights uicore-section <?php if (Helper::get_option('footer_wide') === 'false') {
            echo 'uicore-box ';
        } ?>">
            <div class="uicore uicore-container">
                <div class="uicore-copyrights-wrapper">
                    <div class="uicore-copy-content uicore-animate <?php
                    if (!$have_social) {
                        echo 'uicore-no-socials';
                    } ?>">
                    <?php echo do_shortcode(wp_kses_post(Helper::get_option('copyrights_content',''))); ?>
                    </div>
                    <?php if ($have_social) {
                        echo '<div class="uicore-copy-socials uicore-animate">' . Utils::get_social_icons() . '</div>';
                    } ?>
                </div>
            </div>
        </div>

    <?php
    }
}
