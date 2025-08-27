<?php

namespace UiCoreElements;

use Elementor\Plugin;

/**
 * Scripts and Styles Class
 */
class Assets
{

    function __construct()
    {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'register'], 5);
            add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue'], 5);
        } else {
            add_action('wp_enqueue_scripts', [$this, 'register'], 5);
        }
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register()
    {
        $this->register_scripts($this->get_scripts());
        $this->register_styles($this->get_styles());
    }

    /**
     * Enqueue our app scripts directly in the Elementor editor
     *
     * @return void
     */
    public function enqueue()
    {
        if (Plugin::instance()->editor->is_edit_mode()) {
            wp_enqueue_script('ui-nested-elements', UICORE_ELEMENTS_ASSETS . '/js/components/nested-elements.js', [], UICORE_ELEMENTS_VERSION, true);
        }
    }

    /**
     * Register scripts
     *
     * @param  array $scripts
     *
     * @return void
     */
    private function register_scripts($scripts)
    {
        foreach ($scripts as $handle => $script) {
            $deps      = isset($script['deps']) ? $script['deps'] : false;
            $in_footer = isset($script['in_footer']) ? $script['in_footer'] : false;
            $version   = isset($script['version']) ? $script['version'] : UICORE_ELEMENTS_VERSION;

            wp_register_script($handle, $script['src'], $deps, $version, $in_footer);
        }
    }

    /**
     * Register styles
     *
     * @param  array $styles
     *
     * @return void
     */
    public function register_styles($styles)
    {
        foreach ($styles as $handle => $style) {
            $deps = isset($style['deps']) ? $style['deps'] : false;

            wp_register_style($handle, $style['src'], $deps, UICORE_ELEMENTS_VERSION);
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts()
    {
        $prefix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        $scripts = [
            // Libs that extend current scripts
            'ui-e-countup' => [
                'src'       => UICORE_ELEMENTS_ASSETS . '/js/lib/countUp.umd.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => true
            ],
            'ui-e-odometer' => [
                'src'       => UICORE_ELEMENTS_ASSETS . '/js/lib/odometer.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => true
            ],
            // TODO: update swiper version when loop bug is fixed (last checked on 11.2.6)
            'ui-e-swiper' => [
                'src'       => UICORE_ELEMENTS_ASSETS . '/js/lib/swiper.js',
                'version'   => '11.2.1-ui',
                'in_footer' => true
            ],
            'ui-e-special-effects' => [
                'src' => UICORE_ELEMENTS_ASSETS . '/js/lib/carousel-effects/special-effects.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => true
            ],
            'ui-e-circular-avatar-carousel' => [
                'src' => UICORE_ELEMENTS_ASSETS . '/js/lib/carousel-effects/circular_avatar.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => true
            ],
            'ui-e-stacked-carousel' => [
                'src' => UICORE_ELEMENTS_ASSETS . '/js/lib/carousel-effects/stacked.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => true
            ],

            // Utils and non-compiled assets
            'ui-e-recaptcha' => [
                'src'       => 'https://www.google.com/recaptcha/api.js?render=explicit',
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => false
            ],
            'ui-e-recaptcha-v3' => [
                'src'       => 'https://www.google.com/recaptcha/api.js?render=' . get_option('uicore_elements_recaptcha_site_key'),
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => false
            ],

            // Components compiled for enqueue
            'ui-e-entrance' => [
                'src'       => UICORE_ELEMENTS_ASSETS . '/js/components/global-entrances.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => true
            ],
            'ui-e-masonry' => [
                'src'       => UICORE_ELEMENTS_ASSETS . '/js/components/global-masonry.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => true
            ],
            'ui-e-ajax-request' => [
                'src'       => UICORE_ELEMENTS_ASSETS . '/js/components/ajax-request.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => true
            ],
            'ui-e-counter' => [
                'src'       => UICORE_ELEMENTS_ASSETS . '/js/components/global-counter.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'deps'      => ['ui-e-countup'],
                'in_footer' => true
            ],
            'ui-e-carousel' => [
                'src'       => UICORE_ELEMENTS_ASSETS . '/js/components/global-carousel.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'deps'      => ['ui-e-swiper'],
                'in_footer' => true
            ],
            'ui-e-repeater-custom-key' => [
                'src'       => UICORE_ELEMENTS_ASSETS . '/js/components/repeater-custom-key.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => true
            ],
            'ui-e-testimonial' => [
                'src'       => UICORE_ELEMENTS_ASSETS . '/js/components/global-testimonial.js',
                'version'   => UICORE_ELEMENTS_VERSION,
                'in_footer' => true
            ]
        ];

        return $scripts;
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles()
    {

        // Singular files widgets may enqueue
        $styles = [
            // Animation related
            'ui-e-animation' => [
                'src' =>  UICORE_ELEMENTS_ASSETS . '/css/components/global-animations.css'
            ],
            'ui-e-entrance' => [
                'src' =>  UICORE_ELEMENTS_ASSETS . '/css/components/global-entrances.css'
            ],

            // Layout related
            'ui-e-grid' => [
                'src' => UICORE_ELEMENTS_ASSETS . '/css/components/grid.css'
            ],
            'ui-e-legacy-grid' => [
                'src' => UICORE_ELEMENTS_ASSETS . '/css/components/legacy-grid.css'
            ],
            'ui-e-carousel' => [
                'src' => UICORE_ELEMENTS_ASSETS . '/css/components/carousel.css',
                'deps' => ['swiper', 'e-swiper'],
            ],

            // Specifics
            'ui-e-post-meta' => [
                'src' =>  UICORE_ELEMENTS_ASSETS . '/css/components/meta.css'
            ],
            'ui-e-filters' => [
                'src' => UICORE_ELEMENTS_ASSETS . '/css/components/filters.css'
            ],
            'ui-e-counter-motion' => [
                'src' => UICORE_ELEMENTS_ASSETS . '/css/components/counter-motion.css'
            ]
        ];

        return $styles;
    }
}
