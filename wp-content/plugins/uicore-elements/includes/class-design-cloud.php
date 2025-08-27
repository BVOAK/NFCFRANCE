<?php

namespace UiCoreElements;

/**
 * DesignCloud Handler
 */
class DesignCloud
{
    /**
     * Constructor function to initialize hooks
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_elementor_assets']);
    }


    /**
     * Elementor Editor Style, Fonts and Scripts
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function enqueue_elementor_assets()
    {
        if (!function_exists('_uicore_pro')) {
            return;
        }
        $dc_data = require UICORE_ELEMENTS_PATH . '/assets/design-cloud/design-cloud.asset.php';

        wp_enqueue_script(
            'ui-e-design-cloud',
            UICORE_ELEMENTS_ASSETS . '/design-cloud/design-cloud.js',
            $dc_data['dependencies'],
            $dc_data['version'],
            true
        );
        wp_enqueue_style(
            'ui-e-design-cloud',
            UICORE_ELEMENTS_ASSETS . '/design-cloud/design-cloud.css',
            array(),
            $dc_data['version'],
        );
        $kit_id = get_option('elementor_active_kit');
        $kit_id = $kit_id ? $kit_id : 1;
        $assets = $this->get_elementor_kit_assets($kit_id);

        if (class_exists('\UiCore\Assets')) {
            $css_url = \UiCore\Assets::get_global("uicore-global.css");
        } else {
            // get elementor kit css file
            $css_url = \get_upload_dir()['baseurl'] . "/elementor/css/post-{$kit_id}.css";
        }
        $response = wp_remote_get($css_url);
        if (! is_wp_error($response)) {
            $inline_css = wp_remote_retrieve_body($response);
        }
        $local_data = get_option('uicore_connect', [
            'url' => '',
            'token' => '',
        ]);
        $product = \defined('UICORE_NAME') ? \UICORE_NAME : 'uicore-elements-free';
        $product = \apply_filters('uicore_product_id', $product);
        $dc_settings = [
            'api' => 'https://dc.uicore.co',
            // 'api' => 'http://dc.uicore',
            'builder' => 'el',
            'nonce' => wp_create_nonce('wp_rest'),
            'preview' => [
                'class' => 'elementor-kit-' . $kit_id,
                'assets' => $assets,
                'inline_css' => $inline_css,
            ],
            'local_url' => get_site_url(),
            'license' => [
                'product' => $product,
                'key' => $local_data['token'],
                'url' => $local_data['url'],
            ]
        ];
        wp_add_inline_script('ui-e-design-cloud', "window.ui_dc_global = " . json_encode($dc_settings) . ";", 'before');
    }

    function get_elementor_kit_assets($kit_id)
    {
        $post_css_file = new \Elementor\Core\Files\CSS\Post($kit_id);
        $meta = $post_css_file->get_meta();
        $assets = [];

        // Handle fonts.
        if (! empty($meta['fonts'])) {
            $fonts_url = \Elementor\Plugin::$instance->frontend->get_stable_google_fonts_url($meta['fonts']);
            $assets[] = $fonts_url;
        }

        if (! empty($meta['icons'])) {
            $icons_types = \Elementor\Icons_Manager::get_icon_manager_tabs();

            foreach ($meta['icons'] as $icon_font) {
                if (isset($icons_types[$icon_font]['url'])) {
                    $assets[] = $icons_types[$icon_font]['url'];
                }
            }
        }
        return $assets;
    }

    static function process_import_content(\Elementor\Controls_Stack $element)
    {
        $element_data = $element->get_data();
        $method       = 'on_import';

        if (method_exists($element, $method)) {
            $element_data = $element->{$method}($element_data);
        }

        foreach ($element->get_controls() as $control) {
            $control_class = \Elementor\Plugin::instance()->controls_manager->get_control($control['type']);

            if (!$control_class) {
                return $element_data;
            }

            // If the control isn't exist, like a plugin that creates the control but deactivated.
            if (method_exists($control_class, $method)) {
                $element_data['settings'][$control['name']] = $control_class->{$method}($element->get_settings($control['name']), $control);
            }
        }

        return $element_data;
    }


    static function import_content($content)
    {
        return \Elementor\Plugin::instance()->db->iterate_data(
            $content,
            function ($element_data) {
                //change $element id
                $element_data['id'] = \Elementor\Utils::generate_random_string();
                $element = \Elementor\Plugin::instance()->elements_manager->create_element_instance($element_data);

                if (!$element) {
                    return null;
                }

                return self::process_import_content($element);
            }
        );
    }
}
new DesignCloud();
