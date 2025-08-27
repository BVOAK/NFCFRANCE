<?php

namespace UiCoreAnimate;


defined('ABSPATH') || exit();

/**
 * PageTransition Handler
 */
class PageTransition
{

    private $animation;
    private $preloader;

    private $body_selector = '#uicore-page';
    private static $instance;

    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * Constructor function to initialize hooks
     *
     * @return void
     */

    public function __construct()
    {
        add_action('init', function () {
            remove_action('uicore_before_content', '\UiCore\Animations::add_page_transition_script');
            remove_action('wp_body_open', '\UiCore\Animations::add_transition_markup');
        });

        $this->animation = Settings::get_option('animations_page');
        $this->preloader = Settings::get_option('animations_preloader');

        if ($this->preloader != 'none') {
            add_action('wp_footer', [$this, 'add_preloader'], 0);
        }

        //continue only if the animations are enabled
        if ($this->animation != 'none') {

            if (Helper::is_edit_mode(true)) {
                return;
            }

            if (!\class_exists('\UiCore\Core')) {
                $this->body_selector = 'body';
                add_action('wp_head', [$this, 'add_page_transition_style'], 90);
            } else {
                // TODO: in here we should return if animations in `Performance` tab OR `Animations` tab, on Framework, are disable
                add_filter('uicore_css_global_code', [$this, 'add_css_to_framework'], 10, 2);
            }

            if ($this->animation != 'fade in') {
                add_action('wp_body_open', [$this, 'add_page_transition_script'], 90);
            }
            if ($this->animation === 'reveal' || $this->animation === 'fade and reveal' || $this->animation === 'columns' || $this->animation === 'multilayer') {

                add_action('wp_body_open', function () {
                    echo '<div class="uicore-animation-bg ui-transition">';
                    if ($this->animation === 'columns' || $this->animation === 'multilayer') {
                        echo '<div class="uicore-animation-col"></div>';
                        echo '<div class="uicore-animation-col"></div>';
                        echo '<div class="uicore-animation-col"></div>';
                        echo '<div class="uicore-animation-col"></div>';
                        echo '<div class="uicore-animation-col"></div>';
                        echo '<div class="uicore-animation-col"></div>';
                    }
                    echo '</div>';
                });
            }
        }
    }


    /**
     * create the page transition js script
     *
     * @return string with javascript for animations
     * @author Andrei Voica <andrei@uicore.co>w
     * @since 1.1.0
     */
    function add_page_transition_script()
    {
        if (Helper::is_edit_mode(true)) {
            return;
        }

        $js = $pre_js = null;

        //Page Transition js
        $animation = str_replace(' ', '-', $this->animation);
        $animation_reversed = null;

        if ($animation === 'fade') {
            $animation_reversed = 'document.querySelector("' . $this->body_selector . '").style.animationDirection = "reverse";';
        } else if ($animation === 'reveal') {
            $animation_reversed = 'document.querySelector(".uicore-animation-bg").style.animationName = "uiCoreAnimationsRevealInversed";';
        } else if ($animation === 'fade-and-reveal') {
            $animation_reversed = '
			document.querySelector(".uicore-animation-bg").style.animationName = "uiCoreAnimationsFadeT";
			document.querySelector(".uicore-animation-bg").style.animationTimingFunction = "ease-in";
			document.querySelector(".uicore-animation-bg").style.animationDuration = "0.3s";

			';
        } elseif ($animation === 'columns') {
            $animation_reversed = 'document.querySelectorAll(".uicore-animation-col").forEach((col, index) => {
                setTimeout(() => {
                    col.style.animationName = "uiCoreAnimationsColumnsInversed";
                }, index * 55);
            });';
        } elseif ($animation === 'multilayer') {
            $animation_reversed = '
            Array.from(document.querySelectorAll(".uicore-animation-col")).reverse().forEach((col, index) => {
                setTimeout(() => {
                    col.style.animationName = "uiCoreAnimationsColumnsInversed";
                }, index * 55);
            });';
        }

        if ($animation != 'none' && $animation != 'fade' && $animation != 'columns') {
            $pre_js .= '
            function uiAnimatePreloaderHide() {
                document.querySelector(".uicore-animation-bg").style.animationPlayState="running";
                document.querySelector(".uicore-animation-bg").style.animationName = "";
            }
            ';
        }
        if ($animation == 'columns' || $animation == 'multilayer') {
            $pre_js .= '
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll("a").forEach((link) => {
                    link.addEventListener("click", (e) => {
                        e.preventDefault();
                        if (link.nextElementSibling && link.nextElementSibling.classList.contains("sub-menu")) {
                            return;
                        }
                        console.log("click");
                        var prefetchLink = document.createElement("link");
                        prefetchLink.setAttribute("rel", "prefetch");
                        prefetchLink.setAttribute("href", link.href);
                        document.head.appendChild(prefetchLink);
                        ' . $animation_reversed . '
                        document.body.classList.add("ui-a-pt-' . $animation . '");
                        setTimeout(() => {
                            //prefetch the link

                            window.location.href = link.href;
                        }, 380);
                    });
                });
            });
            function uiAnimatePreloaderHide() {
                document.querySelectorAll(".uicore-animation-col").forEach((col, index) => {
                    setTimeout(() => {
                        col.style.animationPlayState = "running";
                        col.style.animationName = "";
                    }, index * 80);
                });
            }
            ';
        }
        if ($animation != 'none' && $animation != 'fade' && $animation != 'fade in') {
            $js .= '
            window.onbeforeunload = function(e) {
                ' . $animation_reversed . '
                document.body.classList.remove("ui-a-pt-' . $animation . '");
                void document.querySelector("' . $this->body_selector . '").offsetWidth;
                document.body.pointerEvents = "none";
                document.body.classList.add("ui-a-pt-' . $animation . '");
            }
            if(typeof uiAnimateCustomPreloaderHide == "undefined" || (typeof uiAnimateCustomPreloaderHide != "undefined" && !uiAnimateCustomPreloaderHide)){
                uiAnimatePreloaderHide();
            }
            ';
        }

        echo '<script id="uicore-page-transition">';
        echo 'const animationBg = document.querySelector(".uicore-animation-bg:not(.ui-transition)");';
        echo 'if(animationBg){ animationBg.remove(); }';
        echo $pre_js;
        echo " window.onload=window.onpageshow= function() { ";
        echo $js;
        echo ' }; ';
        echo '</script>';
    }

    function add_page_transition_style()
    {
        $css = $this->generate_css(null);
        echo '<style id="uicore-page-transition">' . $css . '</style>';
    }
    function add_css_to_framework($css, \UiCore\CSS $class)
    {
        $css .= $this->generate_css($class);
        return $css;
    }

    function generate_css($class)
    {

        //check if $class is an instance of CSS
        if (($class instanceof \UiCore\CSS)) {

            // TODO: remove both property checks after Framework updates
            // UiCore\CSS $global_animations from private to public
            if (
                property_exists($class, 'global_animations') &&
                isset($class->global_animations) &&
                ! $class->global_animations
            ) {
                return;
            }

            $background = $class->color(Settings::get_option('animations_page_color'));
        } else {
            $background = self::get_color(Settings::get_option('animations_page_color'));
        }
        $css = null;

        $css .= '
            .uicore-animation-bg{
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                display: block;
                pointer-events: none;
                z-index: 99999999999999999999;
            }
            ';
        if (Settings::get_option('animations_page') === 'fade') {
            $css .= $this->body_selector . '{
                    opacity: 0;
                    animation-name: uicoreFadeIn;
                    animation-fill-mode: forwards;
                    animation-timing-function: ease-in;
                ';
            if (Settings::get_option('animations_page_duration') === 'fast') {
                $css .= 'animation-duration: 0.15s;';
            } elseif (Settings::get_option('animations_page_duration') === 'slow') {
                $css .= 'animation-duration: 0.8s;';
            } else {
                $css .= 'animation-duration: 0.35s;';
            }
            $css .= '}';
        }
        if (Settings::get_option('animations_page') === 'fade in') {
            $css .= $this->body_selector . '{
                opacity: 0;
                animation-name: uicoreFadeIn;
                animation-fill-mode: forwards;
                animation-timing-function: ease-in;
            ';
            if (Settings::get_option('animations_page_duration') === 'fast') {
                $css .= 'animation-duration: 0.1s;';
            } elseif (Settings::get_option('animations_page_duration') === 'slow') {
                $css .= 'animation-duration: 0.6s;';
            } else {
                $css .= 'animation-duration: 0.2s;';
            }
            $css .= '}';
        }
        if (Settings::get_option('animations_page') === 'reveal') {
            $css .= '
            @keyframes uiCoreAnimationsReveal {
                0% {
                    transform: scaleX(1);
                }

                30% {
                    transform: scaleX(1);
                }

                100% {
                    transform: scaleX(0);
                }
            }
            @keyframes uiCoreAnimationsRevealInversed {
                0% {
                    transform: scaleX(0);
                    transform-origin: left center;
                }

                70% {
                    transform: scaleX(1);
                    transform-origin: left center;
                }

                100% {
                    transform: scaleX(1);
                    transform-origin: left center;
                }
            }

            .uicore-animation-bg {
                background-color:' . $background . ';
                transform: scaleX(0);
                animation-fill-mode: forwards;
                transform-origin: right center;
                animation-name: uiCoreAnimationsReveal;
                animation-play-state: paused;
                animation-timing-function: cubic-bezier(0.87, 0, 0.13, 1);

            ';
            if (Settings::get_option('animations_page_duration') === 'fast') {
                $css .= 'animation-duration: 0.4s;';
            } elseif (Settings::get_option('animations_page_duration') === 'slow') {
                $css .= 'animation-duration: 1.2s;';
            } else {
                $css .= 'animation-duration: 0.65s;';
            }
            $css .= '}';
        }
        if (Settings::get_option('animations_page') === 'fade and reveal') {
            $css .= '

            @keyframes uiCoreAnimationsRevealBottom {
                0% {
                    transform: scaleY(1);
                    transform-origin: center top;
                }

                30% {
                    transform: scaleY(1);
                    transform-origin: center top;
                }

                100% {
                    transform: scaleY(0);
                    transform-origin: center top;
                }
            }
            @keyframes uiCoreAnimationsFadeT {
                0% {
                    transform: scaleX(1);
                    opacity: 0;
                }

                100% {
                    transform: scaleX(1);
                    opacity: 1;
                }
            }

            .uicore-animation-bg {
                background-color:' . $background . ';
                pointer-events: none;
                transform: scaleX(1);
                animation-fill-mode: forwards;
                transform-origin: right center;
                animation-timing-function: cubic-bezier(0.87, 0, 0.13, 1);
                animation-name: uiCoreAnimationsRevealBottom;
                animation-play-state: paused;

            ';
            if (Settings::get_option('animations_page_duration') === 'fast') {
                $css .= 'animation-duration: 0.75s;';
            } elseif (Settings::get_option('animations_page_duration') === 'slow') {
                $css .= 'animation-duration: 1.2s;';
            } else {
                $css .= 'animation-duration: 0.9s;';
            }
            $css .= '}';
        } elseif (Settings::get_option('animations_page') === 'columns') {
            $css .= '
            @keyframes uiCoreAnimationsColumns {
                0% {
                    transform: scaleX(1);
                }
                100% {
                    transform: scaleX(0);
                }
            }
            @keyframes uiCoreAnimationsColumnsInversed {
                0% {
                    transform: scaleX(0);
                    transform-origin: left center;
                }
                50% {
                    transform: scaleX(1);
                    transform-origin: left center;
                }
                100% {
                    transform: scaleX(1);
                    transform-origin: left center;
                }
            }
            .uicore-animation-col {
                width: 20%;
                height: 100vh;
                display: inline-block;
                pointer-events: none;
                transform: scaleX(1);
                animation-fill-mode: forwards;
                transform-origin: right center;
                animation-timing-function: cubic-bezier(0.87, 0, 0.13, 1);
                animation-name: uiCoreAnimationsColumns;
                animation-play-state: paused;
                background-color:' . $background . ';
            ';
            if (Settings::get_option('animations_page_duration') === 'fast') {
                $css .= 'animation-duration: 0.75s;';
            } elseif (Settings::get_option('animations_page_duration') === 'slow') {
                $css .= 'animation-duration: 1.2s;';
            } else {
                $css .= 'animation-duration: 0.9s;';
            }
            $css .= '}';
        } elseif (Settings::get_option('animations_page') === 'multilayer') {
            $css .= '
            @keyframes uiCoreAnimationsColumns {
                0% {
                    transform: scaleX(1);
                }
                100% {
                    transform: scaleX(0);
                }
            }
            @keyframes uiCoreAnimationsColumnsInversed {
                0% {
                    transform: scaleX(0);
                    transform-origin: left center;
                }
                50% {
                    transform: scaleX(1);
                    transform-origin: left center;
                }
                100% {
                    transform: scaleX(1);
                    transform-origin: left center;
                }
            }
            .uicore-animation-col {
                position: absolute;
                left: 0%;
                top: 0%;
                right: 0%;
                bottom: 0%;
                transform: scaleX(1);
                animation-fill-mode: forwards;
                transform-origin: right center;
                animation-timing-function: cubic-bezier(0.87, 0, 0.13, 1);
                animation-name: uiCoreAnimationsColumns;
                animation-play-state: paused;
                background-color:' . $background . ';
            ';
            if (Settings::get_option('animations_page_duration') === 'fast') {
                $css .= 'animation-duration: 0.75s;';
            } elseif (Settings::get_option('animations_page_duration') === 'slow') {
                $css .= 'animation-duration: 1.2s;';
            } else {
                $css .= 'animation-duration: 0.9s;';
            }
            $css .= '}
            .uicore-animation-col:nth-child(1) {
                opacity: 0.9;

            }
            .uicore-animation-col:nth-child(2) {
                opacity: 0.7;

            }
            .uicore-animation-col:nth-child(3) {
                opacity: 0.55;

            }
            .uicore-animation-col:nth-child(4) {
                opacity: 0.35;

            }
            .uicore-animation-col:nth-child(5) {
                opacity: 0.2;

            }
            .uicore-animation-col:nth-child(6) {
                opacity: 0.1;

            }
            ';
        }

        return $css;
    }

    static function get_color($color)
    {
        if (!is_string($color) && (isset($color['type']) || isset($color['blur']))) {
            $color = $color['color'];
        }
        //check if color is in array x
        if (\in_array($color, ['Primary', 'Secondary', 'Accent', 'Headline', 'Body', 'Dark Neutral', 'Light Neutral', 'White'])) {
            return '#306BFF'; //fallback color
        }
        return $color;
    }

    function add_preloader()
    {
        $run_once = Settings::get_option('animations_preloader_once') != 'true' ? false : true;

        if ($this->preloader === 'custom') {
            $preloader = Settings::get_option('animations_preloader_custom');
            echo '<div class="ui-anim-preloader">' . $preloader . '</div>';
            return;
        }

        if ($run_once === false || ($run_once === true && !isset($_COOKIE['ui_anim_preloader']))) {
            echo $this->get_preloader_html($this->preloader);
        }
?>
        <script>
            const uiAnimPreloader = document.querySelector('.ui-anim-preloader');

            <?php if ($run_once) : ?>
                // If is the user first time, we set the cookie
                if (!uiAnimategetCookie('ui_anim_preloader')) {
                    uiAnimatesetCookie('ui_anim_preloader', true, 30);
                }

                function uiAnimategetCookie(name) {
                    const value = `; ${document.cookie}`;
                    const parts = value.split(`; ${name}=`);
                    if (parts.length === 2) return parts.pop().split(';').shift();
                }

                function uiAnimatesetCookie(name, value, days) {
                    const date = new Date();
                    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
                    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${date.toUTCString()}; path=/`;
                }
            <?php endif; ?>

            function uiAnimateTogglePreloader(show) {
                if (show) {
                    uiAnimPreloader.style.display = 'flex';
                    uiAnimPreloader.style.opacity = '1';
                } else {
                    uiAnimPreloader.style.opacity = '0';
                    setTimeout(() => {
                        uiAnimPreloader.style.display = 'none';
                    }, 600);
                }
            }

            uiAnimateTogglePreloader(true);
            if (typeof uiAnimateCustomPreloaderHide == 'undefined' || (typeof uiAnimateCustomPreloaderHide != 'undefined' && !
                    uiAnimateCustomPreloaderHide)) {
                window.addEventListener('load', () => uiAnimateTogglePreloader(false));
                window.addEventListener('pageshow', () => uiAnimateTogglePreloader(false));
            }
            if (typeof uiAnimateCustomPreloaderShow == 'undefined' || (typeof uiAnimateCustomPreloaderShow != 'undefined' && !
                    uiAnimateCustomPreloaderShow)) {
                window.addEventListener('beforeunload', () => uiAnimateTogglePreloader(true));
            }
        </script>
    <?php
    }

    /**
     * Get the list of available preloader animations
     *
     * @return array
     */
    static function get_preloaders_list()
    {
        $preloaders = [
            'none'                         => __('None', 'uicore-animate'),

            'blobs-spinner'                 => __('Blobs Spinner', 'uicore-animate'),
            'blobs-spinner-text'            => __('Blobs Spinner & Text', 'uicore-animate'),
            'box-spinner'                  => __('Box', 'uicore-animate'),
            'box-spinner-text'             => __('Box & Text', 'uicore-animate'),
            'glowing-spinner'              => __('Glowing Spinner', 'uicore-animate'),
            'glowing-spinner-text'         => __('Glowing Spinner & Text', 'uicore-animate'),
            'infinity-spinner'             => __('Infinity', 'uicore-animate'),
            'infinity-spinner-text'        => __('Infinity & Text', 'uicore-animate'),
            'lines-spinner'                => __('Lines', 'uicore-animate'),
            'lines-spinner-text'           => __('Lines & Text', 'uicore-animate'),
            'liquid-box-spinner'           => __('Liquid Box Spinner', 'uicore-animate'),
            'liquid-box-spinner-text'      => __('Liquid Box Spinner & Text', 'uicore-animate'),
            'simple-spinner'               => __('Simple Spinner', 'uicore-animate'),
            'simple-spinner-text'          => __('Simple Spinner & Text', 'uicore-animate'),
            'circle-spinner'               => __('Circle Spinner', 'uicore-animate'),
            'circle-spinner-text'          => __('Circle Spinner & Text', 'uicore-animate'),
            'triangle-spinner'             => __('Triangle', 'uicore-animate'),
            'triangle-spinner-text'        => __('Triangle & Text', 'uicore-animate'),
            'unfold-spinner'               => __('Unfold Spinner', 'uicore-animate'),
            'unfold-spinner-text'          => __('Unfold Spinner & Text', 'uicore-animate'),

            'simple-counter'               => __('Simple Counter', 'uicore-animate'),
            'big-counter'                  => __('Big Counter', 'uicore-animate'),

            'intro-words'                  => __('Intro Words', 'uicore-animate'),

            // 'custom'                       => __('Custom', 'uicore-animate'),

        ];
        $preocessed_preloaders = [];
        foreach ($preloaders as $key => $value) {
            $preocessed_preloaders[] = ['value' => $key, 'name' => $value];
        }
        return $preocessed_preloaders;
    }

    function get_preloader_html($preloader)
    {
        ob_start();
        $color = Settings::get_option('animations_preloader_color');
        if (\class_exists('\UiCore\Settings')) {
            $color = \UiCore\Settings::color_filter($color);
        }
    ?>
        <style>
            .ui-anim-preloader {
                --ui-e-anim-preloader-color: <?php echo $color;
                                                ?>;
            }
        </style>

        <?php
        //if prelaoder name contains spinner or text
        if (strpos($preloader, 'spinner') !== false) {
            $text_color = Settings::get_option('animations_preloader_text_color');
            if (\class_exists('\UiCore\Settings')) {
                $text_color = \UiCore\Settings::color_filter($text_color);
            }
        ?>
            <style>
                .ui-anim-preloader {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    z-index: 99999999999999999999;
                    transition: opacity .2s ease;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                }

                <?php if (strpos($preloader, 'text') !== false) {
                ?>.ui-anim-preloader .ui-anim-loading-text {
                    text-align: center;
                    width: 100%;
                    color: <?php echo $text_color;
                            ?>;
                    font-size: 14px;
                    font-family: sans-serif;
                    letter-spacing: 3px;
                    line-height: 10px;
                    height: 10px;
                    animation: fade 1.3s ease 0s infinite;
                    margin-top: 18px;
                }

                <?php
                }

                ?>@keyframes fade {
                    0% {
                        opacity: 1;
                    }

                    60% {
                        opacity: 0;
                    }

                    100% {
                        opacity: 1;
                    }
                }
            </style>

            <div class="ui-anim-preloader">
                <div class="ui-anim-loading-wrapper">
                    <?php
                    $preoloader = str_replace('-text', '', $this->preloader);
                    include 'preloaders/' . $preoloader . '.php';
                    ?>
                </div>
                <?php if (strpos($this->preloader, 'text') !== false) { ?>
                    <div class="ui-anim-loading-text">
                        <?php echo \esc_html(Settings::get_option('animations_preloader_text')); ?>
                    </div>
                <?php } ?>
            </div>

<?php
        } elseif ($this->preloader === 'intro-words') {
            include 'preloaders/intro-words.php';
        } else {

            include 'preloaders/' . $this->preloader . '.php';
        }
        return ob_get_clean();
    }
}

PageTransition::init();
