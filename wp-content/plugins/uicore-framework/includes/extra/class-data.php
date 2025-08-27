<?php

namespace UiCore;

defined('ABSPATH') || exit();

/**
 * Get and send data to dashboard in a single array
 */
class Data
{

    /**
     * Get all site plugins
     *
     * @return array
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public static function get_plugins(): array
    {
        // Get all plugins
        $all_plugins = get_plugins();

        $plugins = [];

        // Assemble array of name, version, and whether plugin is active (boolean)
        foreach ($all_plugins as $key => $value) {
            // $is_active = in_array($key, $active_plugins) ? true : false;
            $plugin = [
                'name' => $value['Name'],
                'version' => $value['Version'],
                'active' => is_plugin_active($key),
            ];
            array_push($plugins, $plugin);
        }

        return $plugins;
    }

    /**
     * Get the recomended PLugins List
     *
     * @return array
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public static function get_recomended_plugins(): array
    {
        function check_plugin_installed($plugin_slug): bool
        {
            $installed_plugins = get_plugins();
            return array_key_exists($plugin_slug, $installed_plugins) ||
                in_array($plugin_slug, $installed_plugins, true);
        }

        $plugins = [
            'bdthemes-element-pack/bdthemes-element-pack.php' => [
                'version' => '1.3.2',
                'name' => 'Element Pack PRO',
                'slug' => 'bdthemes-element-pack',
                'description' => 'Element Pack is a powerful Elementor plugin with 300+ widgets.',
                'active' => is_plugin_active('bdthemes-element-pack/bdthemes-element-pack.php'),
                'installed' => check_plugin_installed('bdthemes-element-pack/bdthemes-element-pack.php'),
            ],
            'metform-pro/metform-pro.php' => [
                'version' => '1.3.2',
                'name' => 'Metform PRO - Included FREE',
                'slug' => 'metform-pro',
                'description' => 'Metform Elementor Addon - Your ideal drag-and-drop contact form builder with high-quality pre-built form layouts. (Requires Metform Free)',
                'active' => is_plugin_active('metform-pro/metform-pro.php'),
                'installed' => check_plugin_installed('metform-pro/metform-pro.php'),
            ],
            'metform/metform.php' => [
                'version' => '1.3.2',
                'name' => 'Metform FREE',
                'slug' => 'metform',
                'description' => 'Metform Elementor Addon - Your ideal drag-and-drop contact form builder with high-quality pre-built form layouts. (Required  by Metform PRO)',
                'active' => is_plugin_active('metform/metform.php'),
                'installed' => check_plugin_installed('metform/metform.php'),
            ],
            'w3-total-cache/w3-total-cache.php' => [
                'version' => '0.15.2',
                'name' => 'W3 Total Cache',
                'slug' => 'w3-total-cache',
                'description' => 'In order to make sure that your site scales and sustain huge loads of traffic without crashing, we recomend using this plugin.',
                'active' => is_plugin_active('w3-total-cache/w3-total-cache.php'),
                'installed' => check_plugin_installed('w3-total-cache/w3-total-cache.php'),
            ],
            'wordpress-seo/wp-seo.php' => [
                'version' => '15.3',
                'name' => 'Yoast SEO',
                'slug' => 'wordpress-seo',
                'description' => 'Yoast SEO is packed full of features, designed to help visitors and search engines to get the most out of your website.',
                'active' => is_plugin_active('wordpress-seo/wp-seo.php'),
                'installed' => check_plugin_installed('wordpress-seo/wp-seo.php'),
            ],
            'loco-translate/loco.php' => [
                'version' => '2.4.5',
                'name' => 'Loco Translate',
                'slug' => 'loco-translate',
                'description' => 'Loco Translate provides in-browser editing of WordPress translation files and integration with automatic translation services.',
                'active' => is_plugin_active('loco-translate/loco.php'),
                'installed' => check_plugin_installed('loco-translate/loco.php'),
            ],
            'tutor/tutor.php' => [
                'version' => '2.4.5',
                'name' => 'Tutor LMS',
                'slug' => 'tutor',
                'description' => 'Tutor is a complete, feature-packed and robust WordPress LMS plugin to create & sell courses online easily.',
                'active' => is_plugin_active('tutor/tutor.php'),
                'installed' => check_plugin_installed('tutor/tutor.php'),
            ],
            'woocommerce/woocommerce.php' => [
                'version' => '2.4.5',
                'name' => 'WooCommerce',
                'slug' => 'woocommerce',
                'description' => 'WooCommerce is the world’s most popular open-source eCommerce solution.',
                'active' => is_plugin_active('woocommerce/woocommerce.php'),
                'installed' => check_plugin_installed('woocommerce/woocommerce.php'),
            ],
            'wp-job-openings/wp-job-openings.php' => [
                'version' => '3.3.1',
                'name' => 'WP Job Openings',
                'slug' => 'wp-job-openings',
                'description' => 'Super simple Job Listing plugin to manage Job Openings and Applicants on your WordPress site.',
                'active' => is_plugin_active('wp-job-openings/wp-job-openings.php'),
                'installed' => check_plugin_installed('wp-job-openings/wp-job-openings.php'),
            ],
            'leadin/leadin.php' => [
                'version' => '9.2.81',
                'name' => 'HubSpot All-In-One Marketing - Forms, Popups, Live Chat',
                'slug' => 'leadin',
                'description' => 'HubSpot’s official WordPress plugin allows you to add forms, popups, and live chat to your website and integrate with the best WordPress CRM.',
                'active' => is_plugin_active('leadin/leadin.php'),
                'installed' => check_plugin_installed('leadin/leadin.php'),
            ],
        ];

        return $plugins;
    }

    /**
     * Get all the required admin data so we can use it in Admin Panel.
     *
     * @return array | string
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public static function get_admin_data($type = 'array')
    {
        if (!defined('UICORE_THEME_NAME') || UICORE_NAME === 'UiCore') {
            $data = array('mode' => 'disabled');
        } else {

            global $wpdb;
            /// Server Related data
            if (is_multisite()) {
                $multisite = 'true';
            } else {
                $multisite = 'false';
            }

            if (function_exists('ini_get')) {
                $sv_mem = ini_get('memory_limit');
            }
            if (\class_exists('WP_Site_Health')) {
                $sv_mem = \WP_Site_Health::get_instance()->php_memory_limit;
            }

            if (defined('WP_DEBUG') && WP_DEBUG) {
                $wp_debug = 'true';
            } else {
                $wp_debug = 'false';
            }

            if (function_exists('phpversion')) {
                $php_v = esc_html(phpversion());
            }

            $theme_logo = @file_get_contents(get_template_directory() . "/assets/img/logo.svg", false, stream_context_create(array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false))));
            $settings = Settings::current_settings();
            $name = defined('UICORE_THEME_NAME') ? UICORE_THEME_NAME : UICORE_NAME;

            $data = [
                'version' => defined('UICORE_THEME_VERSION') ? UICORE_THEME_VERSION : 'none',
                'theme' => $name,
                'uicore_assets_path' => UICORE_ASSETS,
                'logo' => $theme_logo ? $theme_logo : '<span class="uicore_h1">' . apply_filters('uicore_theme_name', UICORE_NAME) . '</span>',
                'name' => apply_filters('uicore_theme_name', UICORE_NAME),
                'root' => get_site_url(),
                'wpjson' => get_rest_url(null, 'uicore/v1'),
                'api' => UICORE_API,
                'path' => strtolower(UICORE_NAME),
                'user_id' => get_current_user_id(),
                'nonce' => wp_create_nonce('wp_rest'),
                'devmode' => ((defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) || defined('UICORE_LOCAL')) ? true : false,
                'server' => [
                    'home_url' => home_url(),
                    'site_url' => site_url(),
                    'wp_version' => get_bloginfo('version'),
                    'wp_multisite' => $multisite,
                    'server_memory' => $sv_mem,
                    'wp_permalink' => get_option('permalink_structure'),
                    'wp_debug' => $wp_debug,
                    'language' => get_locale(),
                    'server_info' => esc_html($_SERVER['SERVER_SOFTWARE']),
                    'php_version' => $php_v,
                    'post_max_size' => ini_get('post_max_size'),
                    'php_time_limit' => ini_get('max_execution_time'),
                    'php_max_input' => ini_get('max_input_vars'),
                    'mysql_version' => $wpdb->db_version(),
                    'max_upload_size' => size_format(wp_max_upload_size()),
                ],
                'plugins' => Data::get_plugins(),
                'recomended_plugins' => Data::get_recomended_plugins(),
                'pages' => Data::get_pages(),
                'demos' => Data::get_demos(),
                'changelog' => Data::get_changelog(),
                'settings' => $settings,
                'settings_data' => Settings::get_settings_data(),
                'to_text' => Data::get_theme_options_generic_strings(),
                'importlog' => get_option('uicore_imported_demos', []),
                'admin_settings' => [
                    'scheme' => $settings['scheme'],
                    'presets' => is_array($settings['presets']) ? $settings['presets'] : [],
                    'advanced_mode' => $settings['advanced_mode']
                ],
                'custom_fonts' => Data::get_custom_fonts(),
                'typekit_fonts' => $settings['typekit']['fonts'],
                'elementor_custom_fonts' => Data::get_elementor_fonts(),
                'elementor_typekit_fonts' => Data::get_elementor_typekit_fonts(),
                'update' => Data::update_required(),
                'hide_admin_customizer' => apply_filters('uicore_hide_admin_customizer', false),
                'imported_inner' => get_option('uicore_imported_inner'),
                'is_sandbox' => apply_filters('uicore_is_sandbox', false),
                'webp' => wp_image_editor_supports(array('mime_type' => 'image/webp')),
                'is_child' => is_child_theme(),
                'debug' => \get_option('uicore_beta_debug'),
                'debug_settings' => \get_option('uicore_beta_debug') ? Settings_Helper::get_all_settings(Debug::$default_settings, Debug::$option_name) : [],
                'debug_file' => \get_option('uicore_beta_debug') ? file_exists(Debug::$log_path) : '',
                'connect'   => Helper::handle_connect('get'),
                'is_gutenberg' => get_option('uicore_is_gutenberg', null) === 'true' ? true : false,
                'integrations' => [
                    'openai' => get_option('uicore_ai_key', ''),
                    'recaptchaSite' => get_option('uicore_elements_recaptcha_site_key', ''),
                    'recaptchaSecret' => get_option('uicore_elements_recaptcha_secret_key', ''),
                ]
            ];
        }


        if ($type === 'json') {
            $data = json_encode(Data::utf8ize($data));
        }

        return $data;
    }

    /**
     * Get library list's transient
     *
     * @param string $type
     * @return string Json List
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    static function get_library($type = 'blocks')
    {
        if (false === ($data = get_transient('uicore_library_v3_' . $type))) {
            $type = ($type === 'pages') ? $type . '/' . strtolower(UICORE_NAME) : $type;
            $type = ($type === 'blocks') ? $type . '/' . strtolower(UICORE_NAME) : $type;
            $type = (@substr_compare($type, '_type', 0, 5) == 0) ? 'tb/' . $type : $type;
            $data = [];
            $data = wp_remote_get(UICORE_LIBRARY . $type);
            $data = wp_remote_retrieve_body($data);
            set_transient('uicore_library_v3_' . $type, $data, \DAY_IN_SECONDS);
        }
        return $data;
    }

    /**
     * get changelog and save it for a month
     *
     * @param boolean $force
     * @return string $changelog - Json List
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    static function get_changelog($force = false)
    {
        if (false === ($changelog = get_transient('uicore_changelog')) or $force) {
            $changelog = [];
            $api_response = wp_remote_get(UICORE_API . '/changelog');
            $changelog = wp_remote_retrieve_body($api_response);
            if ($changelog && is_array(json_decode($changelog)) && !empty(json_decode($changelog))) {
                set_transient('uicore_changelog', $changelog, \DAY_IN_SECONDS);
            }
        }
        return $changelog;
    }

    /**
     * Get Demos List from transients or return an empty object if none
     *
     * @return string $demos - Json list
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    static function get_demos()
    {
        if (false === ($demos = get_transient('uicore_demos'))) {
            $demos = [];
        }
        return $demos;
    }

    /**
     * Get Pages list from transients or create, store (for one week) and return it
     *
     * @return array
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    static function get_pages()
    {
        if (false === ($pages = get_transient('uicore_pages'))) {
            $all_pages = get_pages();
            $pages = [];
            $pages[] = [
                'name' => 'default',
                'id' => 0
            ];
            foreach ($all_pages as $key => $page) {
                $title = strlen($page->post_title) > 30 ? substr($page->post_title, 0, 27) . '...' : $page->post_title;
                $pages[] = [
                    'name' => $title,
                    'id' => $page->ID,
                ];
            }
            set_transient('uicore_pages', $pages, WEEK_IN_SECONDS);
        }
        return $pages;
    }

    /**
     * Convert to UTF-8
     *
     * @param [type] $mixed
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.1.1
     */
    static function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = Data::utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        }
        return $mixed;
    }

    /**
     * Get Elementor custom fonts in a UiCore T.O. format
     *
     * @return array
     * @author Andrei Voica <andrei@uicore.co>
     * @since 6.0.0
     */
    static function get_elementor_fonts(): array
    {
        $fonts_list = array();
        $fams = array();
        $data = array();

        // ELEMENTOR PRO ONLY !!!
        if (defined('ELEMENTOR_PRO_VERSION')) {

            $fonts = new \WP_Query([
                'post_type' => 'elementor_font',
                'posts_per_page' => -1,
            ]);

            //Continue only if custom fonts exists
            if ($fonts->have_posts()) {
                foreach ($fonts->posts as $font) {
                    //get variants
                    $variations = get_post_meta($font->ID, 'elementor_font_files', true);
                    $variations_list = array();

                    if (is_array($variations)) {
                        foreach ($variations as $font_data) {
                            $variations_list[] = $font_data['font_weight'];
                        }
                    }
                    $fonts_list[] = array(
                        'family' => $font->post_title,
                        'variants' => $variations_list
                    );
                    $fams[] = $font->post_title;
                }
                //combine and add to array to play nice with fonts selector
                $data = array(
                    "fam" => $fams,
                    "items" => $fonts_list,
                    "type" => 'Elementor Custom'
                );
            }
        }
        return $data;
    }

    /**
     * Get Elementor PRO Tipekit
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.1.2
     */
    static function get_elementor_typekit_fonts()
    {
        $data = [];

        // ELEMENTOR PRO ONLY !!!
        if (defined('ELEMENTOR_PRO_VERSION')) {
            $data = \get_option('elementor_typekit-data');

            if ($data) {
                $fams = [];
                $items = [];
                foreach ($data as $font => $type) {
                    $items[] = [
                        "family" => $font,
                        "variants" => [
                            "100",
                            "100italic",
                            "200",
                            "200italic",
                            "300",
                            "300italic",
                            "regular",
                            "italic",
                            "500",
                            "500italic",
                            "600",
                            "600italic",
                            "700",
                            "700italic",
                            "800",
                            "800italic",
                            "900",
                            "900italic"
                        ]
                    ];
                }

                foreach ($data as $font => $key) {
                    $fams[] = $font;
                }

                //combine and add to array to play nice with fonts selector
                $data = array(
                    "fam" => $fams,
                    "items" => $items,
                    "type" => 'Elementor Typekit Fonts'
                );
            } else {
                $data = [];
            }
        }

        return $data;
    }

    /**
     * Get the list of UiCore Custom Fonts
     * if $data_type === simple wil return the list for elementor
     * elsereturn the list for UiCore
     *
     * @param string $data_type
     * @param string $type
     * @return array
     * @author Andrei Voica <andrei@uicore.co>
     * @since 6.0.0
     */
    static function get_custom_fonts($data_type = null, $type = null): array
    {
        $data = Helper::get_option('customFonts');
        $fams = [];

        //The lis for elementor
        if ($data_type === 'simple' && is_array($data)) {
            foreach ($data as $key => $font) {
                $fams[$font['family']] = $type;
            }
            $data = $fams;
        } else {

            //the list for UiCore
            if (!empty($data)) {
                foreach ($data as $key => $font) {
                    foreach ($font['variants'] as $key2 => $variant) {
                        $data[$key]['variants'][$key2] = $font['variants'][$key2]['type'];
                    }
                }
                foreach ($data as $key => $font) {
                    $fams[] = $font['family'];
                }
                //combine and add to array to play nice with fonts selector
                $data = array(
                    "fam" => $fams,
                    "items" => $data,
                    "type" => 'Custom Fonts'
                );
            } else {
                $data = [];
            }
        }

        return $data;
    }


    /**
     * Get uiCore Typekit Fonts
     *
     * @param string $data_type
     * @param string $type
     * @param array $data
     * @return array
     * @author Andrei Voica <andrei@uicore.co>
     * @since 6.0.0
     */
    static function get_typekit_fonts($data_type = null, $type = null, $data = null): array
    {
        $data = $data ? $data : Helper::get_option('typekit');
        if (isset($data['fonts'])) {
            $fams = [];
            $items = [];
            $data = $data['fonts'];

            //The lis for elementor
            if ($data_type === 'simple' && isset($data['fam'])) {
                foreach ($data['fam'] as $key => $font) {
                    $fams[$font] = $type;
                }
                $data = $fams;
            } else {

                //the list for UiCore


                foreach ($data as $key => $font) {
                    $items[] = [
                        "family" => $font['family'],
                        "variants" => $font['weights']
                    ];
                }

                foreach ($data as $key => $font) {
                    $fams[] = $font['family'];
                }

                //combine and add to array to play nice with fonts selector
                $data = array(
                    "fam" => $fams,
                    "items" => $items,
                    "type" => 'Typekit Fonts'
                );
            }
        } else {
            $data = [];
        }

        return $data;
    }


    static function update_required()
    {
        if (false === ($last_version = get_transient('uicore_last_version'))) {
            $api_response = wp_remote_get(UICORE_API . '/updates');
            $api_response = wp_remote_retrieve_body($api_response);
            $api_response = json_decode($api_response, true);
            $last_version = '';
            if (isset($api_response['last_v'])) {
                $last_version = $api_response['last_v'];
                set_transient('uicore_last_version', $last_version, DAY_IN_SECONDS * 3);
            }
        }
        return [
            "require_update" => version_compare(UICORE_THEME_VERSION, $last_version, '<'),
            "latest_version" => $last_version,
            "current_version" => UICORE_THEME_VERSION
        ];
    }

    public static function get_theme_builder_data()
    {
        $admin_customizer = ThemeOptions::get_admin_customizer_db_options();
        $data = array(
            'conditions' => ThemeBuilder\Common::get_location_selections(),
            'wp_json' => get_rest_url(null, 'uicore/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'scheme' => 'dark',
            'admin_url' => get_admin_url(get_current_blog_id(), 'post.php'),
            'settings' => Settings::current_settings(), //FOR GLOBALS
            'admin_customizer' => isset($admin_customizer['admin_customizer']) ? $admin_customizer['admin_customizer'] : false,
            'to_color' => isset($admin_customizer['to_color']) ? $admin_customizer['to_color'] : null,
            'rev' => wp_get_post_revisions(get_the_ID()),
            'is_woo' => class_exists('WooCommerce') ? true : false,
            'edit_with_gtbg' => \apply_filters('uicore_edit_with_gtbg', get_option('uicore_is_gutenberg', null) === 'true' ? true : false),

        );

        return $data;
    }

    static function get_theme_options_generic_strings()
    {
        return [
            "menu"  => [
                "gs" => _x('GET STARTED', 'Admin - Theme Options Menu', 'uicore-framework'),
                "ds" => _x('Dashboard', 'Admin - Theme Options Menu', 'uicore-framework'),
                "di" => _x('Demo Import', 'Admin - Theme Options Menu', 'uicore-framework'),
                "dg" => _x('DESIGN SYSTEM', 'Admin - Theme Options Menu', 'uicore-framework'),
                "gc" => _x('Global Colors', 'Admin - Theme Options Menu', 'uicore-framework'),
                "gf" => _x('Global Fonts', 'Admin - Theme Options Menu', 'uicore-framework'),
                "ts" => _x('THEME STYLE', 'Admin - Theme Options Menu', 'uicore-framework'),
                "ty" => _x('Typography', 'Admin - Theme Options Menu', 'uicore-framework'),
                "bt" => _x('Buttons', 'Admin - Theme Options Menu', 'uicore-framework'),
                "an" => _x('Animations', 'Admin - Theme Options Menu', 'uicore-framework'),
                "th" => _x('Theme Skin', 'Admin - Theme Options Menu', 'uicore-framework'),
                "se" => _x('SETTINGS', 'Admin - Theme Options Menu', 'uicore-framework'),
                "bd" => _x('Branding', 'Admin - Theme Options Menu', 'uicore-framework'),
                "ge" => _x('General', 'Admin - Theme Options Menu', 'uicore-framework'),
                "tb" => _x('Top Banner', 'Admin - Theme Options Menu', 'uicore-framework'),
                "he" => _x('Header', 'Admin - Theme Options Menu', 'uicore-framework'),
                "fo" => _x('Footer', 'Admin - Theme Options Menu', 'uicore-framework'),
                "pt" => _x('Page Title', 'Admin - Theme Options Menu', 'uicore-framework'),
                "bg" => _x('Blog', 'Admin - Theme Options Menu', 'uicore-framework'),
                "po" => _x('Portfolio', 'Admin - Theme Options Menu', 'uicore-framework'),
                "wo" => _x('WooCommerce', 'Admin - Theme Options Menu', 'uicore-framework'),
                "so" => _x('Social', 'Admin - Theme Options Menu', 'uicore-framework'),
                "cu" => _x('Custom', 'Admin - Theme Options Menu', 'uicore-framework'),
                "mi" => _x('MISC', 'Admin - Theme Options Menu', 'uicore-framework'),
                "pe" => _x('Performance', 'Admin - Theme Options Menu', 'uicore-framework'),
                "sy" => _x('System', 'Admin - Theme Options Menu', 'uicore-framework'),
                "debug" => _x('Debug', 'Admin - Theme Options Menu', 'uicore-framework'),
                "up" => _x('Updates', 'Admin - Theme Options Menu', 'uicore-framework'),
                "pl" => _x('Plugins', 'Admin - Theme Options Menu', 'uicore-framework'),
                "pm" => _x('Preset Manager', 'Admin - Theme Options Menu', 'uicore-framework'),
                "ac" => _x('Admin Customizer', 'Admin - Theme Options Menu', 'uicore-framework'),
            ],
            "general" => [
                "search" => _x('Search for settings', 'Admin - Theme Options General', 'uicore-framework'),
                "no_search" => _x('No Settings Found for this search.', 'Admin - Theme Options General', 'uicore-framework'),
                "simplified" => _x('Simplified View', 'Admin - Theme Options General', 'uicore-framework'),
                "dark" => _x('Switch to Dark Mode', 'Admin - Theme Options General', 'uicore-framework'),
                "light" => _x('Switch to Light Mode', 'Admin - Theme Options General', 'uicore-framework'),
                "doc" => _x('See Documentation', 'Admin - Theme Options General', 'uicore-framework'),
                "save" => _x('Save', 'Admin - Theme Options General', 'uicore-framework'),
                "saved" => _x('Saved', 'Admin - Theme Options General', 'uicore-framework'),
                "error" => _x('Error!', 'Admin - Theme Options General', 'uicore-framework'),
                "done" => _x('Done!', 'Admin - Theme Options General', 'uicore-framework'),
                "wait" => _x('Please Wait', 'Admin - Theme Options General', 'uicore-framework'),
                "install" => _x('Install', 'Admin - Theme Options General', 'uicore-framework'),
                "active" => _x('Active', 'Admin - Theme Options General', 'uicore-framework'),
                "activate" => _x('Activate', 'Admin - Theme Options General', 'uicore-framework'),
                "saving" => _x('Saving', 'Admin - Theme Options General', 'uicore-framework'),
                "cancel" => _x('Cancel', 'Admin - Theme Options General', 'uicore-framework'),
                "new" => _x('Add New', 'Admin - Theme Options General', 'uicore-framework'),
                "no_results" => _x('No Results', 'Admin - Theme Options General', 'uicore-framework'),
                "sidebar_notice" => _x('Sidebars style is disabled from Performance', 'Admin - Theme Options General', 'uicore-framework'),
                "typo_notice" => _x('For better performance, it\'s recommended you limit typography to two font families.', 'Admin - Theme Options General', 'uicore-framework'),
            ],
            "import" => [
                "title" => _x('Import Demo Templates', 'Admin - Theme Options Import', 'uicore-framework'),
                "text" => sprintf(
                    _x('Before running Demo Import, make sure %s Server Status %s is all green. If any values
                            don’t meet the minimum requirements, process might not run properly.', 'Admin - Theme Options Import', 'uicore-framework'),
                    '<a href="#/system">',
                    '</a>'
                ),
                "run" => _x('Run Demo Import', 'Admin - Theme Options Import', 'uicore-framework'),
                "title_pages" => _x('Import Inner Pages', 'Admin - Theme Options Import', 'uicore-framework'),
                "text_pages" => _x('comes with a complete set of ready-made inner pages. This process will import all pages with no theme options settings.', 'Admin - Theme Options Import', 'uicore-framework'),
                "run_pages" => _x('Run Pages Import', 'Admin - Theme Options Import', 'uicore-framework'),
                "rerun_pages" => _x('ReImport Pages', 'Admin - Theme Options Import', 'uicore-framework'),
                "get" => _x('Getting data from Server', 'Admin - Theme Options Import', 'uicore-framework'),
                "media" => _x('Downloading and importing Media', 'Admin - Theme Options Import', 'uicore-framework'),
                "pages" => _x('Importing Pages', 'Admin - Theme Options Import', 'uicore-framework'),

                "forms" => _x('Importing Forms', 'Admin - Theme Options Import', 'uicore-framework'),
                "portfolio" => _x('Importing Portfolio', 'Admin - Theme Options Import', 'uicore-framework'),
                "products" => _x('Importing Products', 'Admin - Theme Options Import', 'uicore-framework'),
                "to" => _x('Importing Theme Options', 'Admin - Theme Options Import', 'uicore-framework'),
                "menu" => _x('Importing Menu', 'Admin - Theme Options Import', 'uicore-framework'),
                "widgets" => _x('Importing Widgets', 'Admin - Theme Options Import', 'uicore-framework'),
                "tb_header" => _x('Importing Headers', 'Admin - Theme Options Import', 'uicore-framework'),
                "tb_footer" => _x('Importing Footers', 'Admin - Theme Options Import', 'uicore-framework'),
                "tb_mm" => _x('Importing Mega Menu', 'Admin - Theme Options Import', 'uicore-framework'),
                "tb_block" => _x('Importing Blocks', 'Admin - Theme Options Import', 'uicore-framework'),
                "tb_popup" => _x('Importing Popup', 'Admin - Theme Options Import', 'uicore-framework'),
                "tb_archive" => _x('Importing Archive', 'Admin - Theme Options Import', 'uicore-framework'),
                "tb_single" => _x('Importing Single', 'Admin - Theme Options Import', 'uicore-framework'),
                "tb_pagetitle" => _x('Importing Page Title', 'Admin - Theme Options Import', 'uicore-framework'),
                "widgets" => _x('Importing Widgets', 'Admin - Theme Options Import', 'uicore-framework'),

                "done" => _x('Import process Done!', 'Admin - Theme Options Import', 'uicore-framework'),
                "issue" => _x('There was an issue with the import', 'Admin - Theme Options Import', 'uicore-framework'),
                "title_info" => _x('Looking to import a single page or section?', 'Admin - Theme Options Import', 'uicore-framework'),
                "text_info" => _x('You can import any of the pages/sections available in Demo Import, directly into Elementor Page Builder,
                                    using UiCore template library.', 'Admin - Theme Options Import', 'uicore-framework'),
                "learn" => _x('Learn about', 'Admin - Theme Options Import', 'uicore-framework'),
                "title_info2" => _x('Demo Import not working properly?', 'Admin - Theme Options Import', 'uicore-framework'),
                "text_info2" => sprintf(
                    _x(' Demo Import is a complex process that usually fails (or doesn\'t work as intended) due to low default limits on your server.
                     Whenever this happens, you will see an error message after the import and the last error will be stored in %s Error Log %s.
                     Luckily, most of these errors can be fixed by changing your server configuration.', 'Admin - Theme Options Import', 'uicore-framework'),
                    '<a href="#/system?#uicore-log">',
                    '</a>'
                ),
                "trouble" => _x('Demo Import Troubleshooting', 'Admin - Theme Options Import', 'uicore-framework'),

                "import" => _x('Import', 'Admin - Theme Options Import', 'uicore-framework'),
                "preview" => _x('Preview', 'Admin - Theme Options Import', 'uicore-framework'),
                "list_updating" => _x('Demo list is updating', 'Admin - Theme Options Import', 'uicore-framework'),
                "content_loading" => _x('Demo content is loading.', 'Admin - Theme Options Import', 'uicore-framework'),
                "required" => _x('THE FOLLOWING PLUGINS ARE REQUIRED AND WILL BE INSTALLED:', 'Admin - Theme Options Import', 'uicore-framework'),
                "content_loading" => _x('Demo content is loading.', 'Admin - Theme Options Import', 'uicore-framework'),
                "content" => _x('Import Content:', 'Admin - Theme Options Import', 'uicore-framework'),
                "_pages" => _x('Pages', 'Admin - Theme Options Import', 'uicore-framework'),
                "_posts" => _x('Posts', 'Admin - Theme Options Import', 'uicore-framework'),
                "_portfolio" => _x('Portfolio', 'Admin - Theme Options Import', 'uicore-framework'),
                "_products" => _x('Products', 'Admin - Theme Options Import', 'uicore-framework'),
                "_media" => _x('Media', 'Admin - Theme Options Import', 'uicore-framework'),
                "_tb_header" => _x('Header', 'Admin - Theme Options Import', 'uicore-framework'),
                "_tb_footer" => _x('Footer', 'Admin - Theme Options Import', 'uicore-framework'),
                "_tb_mm" => _x('Mega Menu', 'Admin - Theme Options Import', 'uicore-framework'),
                "_tb_block" => _x('Block', 'Admin - Theme Options Import', 'uicore-framework'),
                "_tb_popup" => _x('Popup', 'Admin - Theme Options Import', 'uicore-framework'),
                "_tb_archive" => _x('Archive', 'Admin - Theme Options Import', 'uicore-framework'),
                "_tb_single" => _x('Single', 'Admin - Theme Options Import', 'uicore-framework'),
                "_tb_pagetitle" => _x('Page Title', 'Admin - Theme Options Import', 'uicore-framework'),
                "_widgets" => _x('Widgets', 'Admin - Theme Options Import', 'uicore-framework'),
                "_menu" => _x('Navigation Menu Items', 'Admin - Theme Options Import', 'uicore-framework'),
                "_to" => _x('Theme Options', 'Admin - Theme Options Import', 'uicore-framework'),
                "update" => _x('Require Update', 'Admin - Theme Options Import', 'uicore-framework'),
                "new" => _x('New', 'Admin - Theme Options Import', 'uicore-framework'),
                "installing_plugins" => _x('Installing and Activating Required Plugins', 'Admin - Theme Options Import', 'uicore-framework'),
                "installing" => _x('Installing', 'Admin - Theme Options Import', 'uicore-framework'),
                "activating" => _x('Activating', 'Admin - Theme Options Import', 'uicore-framework'),
                "_error" => _x('Errors occurred during import. See Error Log.', 'Admin - Theme Options Import', 'uicore-framework'),
                "back" => _x('Back to Dashboard', 'Admin - Theme Options Import', 'uicore-framework'),
                "view" => _x('View Site', 'Admin - Theme Options Import', 'uicore-framework'),
                "notice" => _x('Please chose at least 1 content type to continue', 'Admin - Theme Options Import', 'uicore-framework'),
            ],
            "font"  => [
                "custom" => _x('Custom Fonts', 'Admin - Theme Options Fonts', 'uicore-framework'),
                "ff" => _x('Font Family', 'Admin - Theme Options Fonts', 'uicore-framework'),
                "fs" => _x('Font Style', 'Admin - Theme Options Fonts', 'uicore-framework'),
                "tt" => _x('Text Transform', 'Admin - Theme Options Fonts', 'uicore-framework'),
                "size" => _x('Font Size', 'Admin - Theme Options Fonts', 'uicore-framework'),
                "lh" => _x('LH', 'Admin - Theme Options Fonts', 'uicore-framework'),
                "ls" => _x('LS', 'Admin - Theme Options Fonts', 'uicore-framework'),
                "notice" => _x('For better performance, it\'s recommended you limit typography to two font families.', 'Admin - Theme Options Fonts', 'uicore-framework'),
            ],
            "color"  => [
                "main" => _x('Main Color', 'Admin - Theme Options Colors', 'uicore-framework'),
                "color" => _x('Color', 'Admin - Theme Options Colors', 'uicore-framework'),
                "hover" => _x('Hover Color', 'Admin - Theme Options Colors', 'uicore-framework'),
                "size" => _x('Font Size', 'Admin - Theme Options Colors', 'uicore-framework'),
                "lh" => _x('Line Height', 'Admin - Theme Options Colors', 'uicore-framework'),
                "ls" => _x('Letter Spacing', 'Admin - Theme Options Colors', 'uicore-framework'),
            ],
            "animations"  => [
                "notice" => _x('Animations are disabled globally from Performance.', 'Admin - Theme Options Animations', 'uicore-framework'),
                "page" => _x('Page Transition', 'Admin - Theme Options Animations', 'uicore-framework'),
            ],
            "header"  => [
                "s" => _x('Header Style', 'Admin - Theme Options Header', 'uicore-framework'),
                "classic" => _x('Classic', 'Admin - Theme Options Header', 'uicore-framework'),
                "classic_center" => _x('Classic Center', 'Admin - Theme Options Header', 'uicore-framework'),
                "center_creative" => _x('Center Creative', 'Admin - Theme Options Header', 'uicore-framework'),
                "left" => _x('Left Header', 'Admin - Theme Options Header', 'uicore-framework'),
                "ham" => _x('Hamburger Classic', 'Admin - Theme Options Header', 'uicore-framework'),
                "ham_center" => _x('Hamburger Center', 'Admin - Theme Options Header', 'uicore-framework'),
                "ham_creative" => _x('Hamburger Creative', 'Admin - Theme Options Header', 'uicore-framework'),
                "e" => _x('Header Extras', 'Admin - Theme Options Header', 'uicore-framework'),
                "m" => _x('Menu', 'Admin - Theme Options Header', 'uicore-framework'),
                "mm" => _x('Mobile Menu', 'Admin - Theme Options Header', 'uicore-framework'),
            ],
            "footer"  => [
                "s" => _x('Footer Style', 'Admin - Theme Options Footer', 'uicore-framework'),
                "column" => _x('Column', 'Admin - Theme Options Footer', 'uicore-framework'),
                "columns" => _x('Columns', 'Admin - Theme Options Footer', 'uicore-framework'),
                "left" => _x('Left', 'Admin - Theme Options Footer', 'uicore-framework'),
                "right" => _x('Right', 'Admin - Theme Options Footer', 'uicore-framework'),
                "center" => _x('Center', 'Admin - Theme Options Footer', 'uicore-framework'),
                "copy" => _x('Copyright Style', 'Admin - Theme Options Footer', 'uicore-framework'),
            ],
            "blog"  => [
                "archive" => _x('Blog Page (Archive)', 'Admin - Theme Options Blog', 'uicore-framework'),
                "classic" => _x('Blog Classic', 'Admin - Theme Options Blog', 'uicore-framework'),
                "grid" => _x('Blog Grid', 'Admin - Theme Options Blog', 'uicore-framework'),
                "horizontal" => _x('Blog Horizontal', 'Admin - Theme Options Blog', 'uicore-framework'),
                "masonry" => _x('Blog Masonry', 'Admin - Theme Options Blog', 'uicore-framework'),
                "post" => _x('Blog Post', 'Admin - Theme Options Blog', 'uicore-framework'),
                "typo" => _x('Blog Post Typography', 'Admin - Theme Options Blog', 'uicore-framework'),
            ],
            "portfolio"  => [
                "archive" => _x('Porfolio Page (Archive)', 'Admin - Theme Options Porfolio', 'uicore-framework'),
                "single" => _x('Porfolio Post (Single)', 'Admin - Theme Options Porfolio', 'uicore-framework'),
                "grid" => _x('Grid', 'Admin - Theme Options Porfolio', 'uicore-framework'),
                "tiles" => _x('Grid Tiles', 'Admin - Theme Options Porfolio', 'uicore-framework'),
                "masonry" => _x('Masonry', 'Admin - Theme Options Porfolio', 'uicore-framework'),
                "masonry_tiles" => _x('Masonry Tiles', 'Admin - Theme Options Porfolio', 'uicore-framework'),
                "justified" => _x('Justified Tiles', 'Admin - Theme Options Porfolio', 'uicore-framework'),
            ],
            "woo"  => [
                "archive" => _x('Shop Page (Archive)', 'Admin - Theme Options WooCommerce', 'uicore-framework'),
                "product" => _x('Product (Single)', 'Admin - Theme Options WooCommerce', 'uicore-framework'),
            ],
            "performance"  => [
                "features" => _x('Disable Unused Features', 'Admin - Theme Options Performance', 'uicore-framework'),
                "theme_features" => _x('Disable Theme Features', 'Admin - Theme Options Performance', 'uicore-framework'),
                "optimizations" => _x('Performance Optimization', 'Admin - Theme Options Performance', 'uicore-framework'),
                "preload" => _x('Preload Resources', 'Admin - Theme Options Performance', 'uicore-framework'),
                "preload_remove" => _x('Remove Preload', 'Admin - Theme Options Performance', 'uicore-framework'),
                "preload_add" => _x('+ Add Preload', 'Admin - Theme Options Performance', 'uicore-framework'),
            ],
            "system"  => [
                "desc" => _x('Use this page to control the normal function and features of Theme options panel.', 'Admin - Theme Options System', 'uicore-framework'),
                "status" => _x('Server Status', 'Admin - Theme Options System', 'uicore-framework'),
                "api" => _x('API Connection', 'Admin - Theme Options System', 'uicore-framework'),
                "webp" => _x('WEBP Support', 'Admin - Theme Options System', 'uicore-framework'),
                "home_url" => _x('Home Url', 'Admin - Theme Options System', 'uicore-framework'),
                "site_url" => _x('Site Url', 'Admin - Theme Options System', 'uicore-framework'),
                "wp_version" => _x('WP Version', 'Admin - Theme Options System', 'uicore-framework'),
                "wp_multisite" => _x('Multisite', 'Admin - Theme Options System', 'uicore-framework'),
                "server_memory" => _x('Server Memory', 'Admin - Theme Options System', 'uicore-framework'),
                "wp_debug" => _x('WP Debug', 'Admin - Theme Options System', 'uicore-framework'),
                "language" => _x('Language', 'Admin - Theme Options System', 'uicore-framework'),
                "server_info" => _x('Server Info', 'Admin - Theme Options System', 'uicore-framework'),
                "php_version" => _x('PHP Version', 'Admin - Theme Options System', 'uicore-framework'),
                "post_max_size" => _x('Post Max Size', 'Admin - Theme Options System', 'uicore-framework'),
                "php_time_limit" => _x('PHP Time Limit', 'Admin - Theme Options System', 'uicore-framework'),
                "php_max_input" => _x('PHP Max Input', 'Admin - Theme Options System', 'uicore-framework'),
                "mysql_version" => _x('MySQL Version', 'Admin - Theme Options System', 'uicore-framework'),
                "max_upload_size" => _x('Max Upload', 'Admin - Theme Options System', 'uicore-framework'),
                "tools" => _x('Tools', 'Admin - Theme Options System', 'uicore-framework'),
                "export" => _x('Export Settings', 'Admin - Theme Options System', 'uicore-framework'),
                "export_desc" => _x('Download the current configuration in a Json file.', 'Admin - Theme Options System', 'uicore-framework'),
                "ex" => _x('Download Json', 'Admin - Theme Options System', 'uicore-framework'),
                "import" => _x('Import Settings', 'Admin - Theme Options System', 'uicore-framework'),
                "import_desc" => _x('Import settings by uploading a Json file.', 'Admin - Theme Options System', 'uicore-framework'),
                "proxy" => _x('API Connection Proxy', 'Admin - Theme Options System', 'uicore-framework'),
                "proxy_desc" => _x('Use a proxy to connect to our API server. Click to enable/disable.', 'Admin - Theme Options System', 'uicore-framework'),

                "child" => _x('Install & Activate Child Theme', 'Admin - Theme Options System', 'uicore-framework'),
                "child_desc" => _x('Automatically install child theme.', 'Admin - Theme Options System', 'uicore-framework'),
                "imp" => _x('Import Json', 'Admin - Theme Options System', 'uicore-framework'),
                "refresh" => _x('Refresh UiCore Data', 'Admin - Theme Options System', 'uicore-framework'),
                "refresh_desc" => _x('Refetch all data from UiCore API.', 'Admin - Theme Options System', 'uicore-framework'),
                "refreshing" => _x('Refreshing', 'Admin - Theme Options System', 'uicore-framework'),
                "re" => _x('Refresh Data', 'Admin - Theme Options System', 'uicore-framework'),
                "recomended" => _x('Recomended Value', 'Admin - Theme Options System', 'uicore-framework'),
            ],
            "updates"  => [
                "up" => _x('Updates', 'Admin - Theme Options Updates', 'uicore-framework'),
                "latest" => _x('Congrats! You\'re using the latest version.', 'Admin - Theme Options Updates', 'uicore-framework'),
                "check" => _x('Check for Updates', 'Admin - Theme Options Updates', 'uicore-framework'),
                "cheking" => _x('Looking for updates', 'Admin - Theme Options Updates', 'uicore-framework'),
                "rollback" => _x('Rollback to Previous Version', 'Admin - Theme Options Updates', 'uicore-framework'),
                "changelog" => _x('Changelog', 'Admin - Theme Options Updates', 'uicore-framework')
            ],
            "plugins"  => [
                "pu" => _x('Plugins', 'Admin - Theme Options Plugins', 'uicore-framework'),
                "pu_desc" => _x('The following plugins were thoroughly tested with our theme.', 'Admin - Theme Options Plugins', 'uicore-framework'),
                "recomended" => _x('RECOMMENDED PLUGINS', 'Admin - Theme Options Plugins', 'uicore-framework'),
                "req" => _x('Requires Free Version!', 'Admin - Theme Options Plugins', 'uicore-framework'),
            ],
        ];
    }


    static function get_menu_icons($name = false, $color = false)
    {
        //Return markup for one
        if ($name) {
            $markup = file_get_contents(UICORE_PATH . '/assets/img/menu-icons/' . $name);
            $markup = file_get_contents(UICORE_PATH . '/assets/img/menu-icons/' . $name);
            $pre =  '<span class="ui-svg-wrapp">';
            if ($color) {
                $pre =  '<span class="ui-svg-wrapp" style="color:' . Helper::get_css_color($color) . '">';
            }

            return $pre . $markup . '</span>';
        }

        //Return a array with all
        $svg_list = \scandir(UICORE_PATH . '/assets/img/menu-icons/');
        $svg_list_with_url = [];
        foreach ($svg_list as $svg) {
            if (strlen($svg) > 3) {
                $svg_list_with_url[$svg] = file_get_contents(UICORE_PATH . '/assets/img/menu-icons/' . $svg);
            }
        }

        return $svg_list_with_url;
    }

    static function get_menu_data()
    {
        $data = [
            'icons' => Data::get_menu_icons(),
            'settings' => Settings::current_settings()
        ];

        return json_encode($data);
    }
}
