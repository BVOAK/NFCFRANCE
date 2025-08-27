<?php

namespace UiCoreAnimate;


/**
 * Frontend Pages Handler
 */
class Frontend
{

    private $style = '';

    /**
     * Constructor function to initialize hooks
     *
     * @return void
     */

    public function __construct()
    {
        //Handle animation style in UiCore Framework Global if is active
        if (!\class_exists('\UiCore\Helper')) {
            $style = Settings::get_option('uianim_style');
            if (is_array($style)) {
                $this->style = $style['value'] ? $style['value'] : 'style1'; //fallback for default style
            } else {
                $this->style = 'style1';
            }

            if ($this->style) {
                add_action('elementor/frontend/after_enqueue_scripts', function () {
                    wp_deregister_style('e-animations');
                    wp_dequeue_style('e-animations');
                }, 20);

                add_action('wp_enqueue_scripts', [$this, 'remove_elementor_animation_style'], 60);

                // Defer the loading to footer by adding inline style in the footer
                add_action('get_footer', function () {
                    wp_enqueue_style('uianim-style', UICORE_ANIMATE_ASSETS . '/css/' . $this->style . '.css');
                }, 1000);
            }

            //scroll
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts'], 60);
        } else {
            //add the resources to global files in UiCore Framework
            add_filter('uicore_css_global_files', [$this, 'add_css_to_framework'], 10, 2);
            add_filter('uicore_js_global_files', [$this, 'add_js_to_framework'], 10, 2);
        }

        \add_action('wp_footer', [$this, 'add_scroll_timeline_polyfill'], 999);
    }

    /**
     * Enqueue animation style
     *
     */
    public function remove_elementor_animation_style()
    {
        wp_dequeue_style('elementor-animations');
    }

    /**
     * Enqueue scroll
     *
     */
    public function enqueue_scripts()
    {
        //scroll
        if (Settings::get_option('uianim_scroll')  == 'true') {
            wp_enqueue_script('uianim-scroll', UICORE_ANIMATE_ASSETS . '/js/scroll.js',  UICORE_ANIMATE_VERSION, true);
        }
        wp_enqueue_script('uianim-entrance-animation', UICORE_ANIMATE_ASSETS . '/js/entrance-animation.js',  UICORE_ANIMATE_VERSION, true);
    }



    public function add_css_to_framework($files, $settings)
    {
        if ($settings['performance_animations'] === 'true') {
            $style = $settings['uianim_style'];
            $style = (isset($style['value']) && $style['value']) ? $style['value'] : 'style1';  //fallback for default style
            $files[] = UICORE_ANIMATE_PATH . '/assets/css/' . $style . '.css';
        }

        return $files;
    }

    public function add_js_to_framework($files, $settings)
    {
        if ($settings['performance_animations'] === 'true' && $settings['uianim_scroll'] == 'true') {
            $files[] =  UICORE_ANIMATE_PATH . '/assets/js/scroll.js';
        }

        $files[] =  UICORE_ANIMATE_PATH . '/assets/js/entrance-animation.js';

        return $files;
    }

    public function add_scroll_timeline_polyfill()
    { ?>
        <style>
            .uicore-animate-scroll {
                animation-fill-mode: both;
                animation-timing-function: linear;
                animation-timeline: view(block);
            }
        </style>
        <script>
            (function() {
                const supportsAnimationTimeline = CSS.supports("animation-timeline", "scroll()");

                if (!supportsAnimationTimeline && document.querySelector('.uicore-animate-scroll')) {
                    const script = document.createElement('script');
                    script.src = "<?php echo UICORE_ANIMATE_ASSETS . '/js/scroll-timeline.js'; ?>";
                    script.async = true;
                    document.head.appendChild(script);
                }
            })();
        </script>
<?php
    }
}
