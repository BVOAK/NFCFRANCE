<?php

namespace UiCore;

defined('ABSPATH') || exit();

use WP_Error;
use WP_REST_Response;

class Api
{
    private $new_widgets;
    private $ep_settings;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'add_route']);
        add_action(
            'rest_after_save_widget',
            function () {
                Helper::delete_frontend_transients();
            },
            1
        );
    }

    /**
     * Add routes
     */
    public function add_route()
    {
        //settings Api
        register_rest_route('uicore/v1', 'settings', [
            [
                'methods' => 'GET',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'get_settings'],
                'show_in_index' => false,
            ],
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'rest_update_settings'],
                'args' => [],
                'show_in_index' => false,
            ],
        ]);

        //admin settings Api
        register_rest_route('uicore/v1', 'admin', [
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'admin_utility'],
                'show_in_index' => false,
            ],
        ]);
        register_rest_route('uicore/v1', 'import-log', [
            [
                'methods' => 'GET',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'get_import_log'],
                'show_in_index' => false,
            ],
        ]);
        register_rest_route('uicore/v1', 'import-log', [
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'clear_import_log'],
                'show_in_index' => false,
            ],
        ]);

        //import Api
        register_rest_route('uicore/v1', 'import', [
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'import'],
                'show_in_index' => false,
            ],
        ]);
        register_rest_route('uicore/v1', 'import-library', [
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'import_library'],
                'show_in_index' => false,
            ],
        ]);

        //Theme Builder
        // UiCore\ThemeBuilderApi::class;
    }

    public function check_for_permission()
    {
        return current_user_can('manage_options');
    }

    /**
     * Get Current Theme Options Settings
     *
     * @return object
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public static function get_settings()
    {
        $current = Settings::current_settings();
        return rest_ensure_response($current);
    }

    /**
     * Update Theme Options Settings
     *
     * @param \WP_REST_Request $request
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function rest_update_settings(\WP_REST_Request $request)
    {
        $settings = $request->get_json_params();
        $response = Settings::update_settings($settings);
        // $response = ['status'=> 'success'];
        return rest_ensure_response($response);
    }

    /**
     * Do Admin Utility functions from 'admin' API endpoint
     *
     * @param \WP_REST_Request $request
     * @return array Action Response
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function admin_utility(\WP_REST_Request $request)
    {
        //default response
        // $response = array("status"=>"error");

        if ($request['advanced_mode']) {
            $settings = ThemeOptions::get_admin_db_options();
            $settings['advanced_mode'] = $request['advanced_mode'];
            update_option(UICORE_SETTINGS . '_admin', $settings, false);
            return new WP_REST_Response(['status' => 'success']);
        }
        if ($request['backgrounds']) {
            $settings = ThemeOptions::get_admin_db_options();
            $settings['backgrounds'] = $request['backgrounds'];
            update_option(UICORE_SETTINGS . '_admin', $settings, false);
            return new WP_REST_Response(['status' => 'success']);
        }
        if ($request['scheme']) {
            $settings = ThemeOptions::get_admin_db_options();
            $settings['scheme'] = $request['scheme'];
            update_option(UICORE_SETTINGS . '_admin', $settings, false);
            return new WP_REST_Response(['status' => 'success']);
        }
        if ($request['proxy']) {
            $settings = ThemeOptions::get_admin_db_options();
            $settings['proxy'] = $request['proxy'];
            update_option(UICORE_SETTINGS . '_admin', $settings, false);
            return $this->refresh_transients();
        }
        if ($request['presets']) {
            return $this->update_presets($request['presets']);
        }
        if ($request['demos']) {
            return $this->get_demos($request['api']);
        }
        if ($request['reset']) {
            return $this->reset_settings();
        }
        if ($request['refresh']) {
            return $this->refresh_transients();
        }
        if ($request['typekit']) {
            return $this->sync_typekit($request['typekit']);
        }
        if (isset($request['debug'])) {
            return \update_option('uicore_beta_debug', $request['debug']);
        }

        if ($request['purchase']) {
            error_log('Deprecated purchase request');
            return Helper::handle_connect('remove');
        }

        if ($request['connect']) {
            return Helper::handle_connect($request['connect']['type']);
        }

        if ($request['aiKey']) {
            update_option('uicore_ai_key', \sanitize_text_field($request['aiKey']), false);
            return array("status" => "succes");
        }
        if ($request['option_update']) {
            \update_option($request['option_update']['option'], $request['option_update']['value'], false);
            return array("status" => "succes");
        }
        if ($request['clear_cache']) {
            Settings::clear_cache();
            $this->refresh_transients();
            return array("status" => "succes");
        }
    }

    /**
     * Process Import Data
     *
     * @param \WP_REST_Request $request
     * @return array
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function import(\WP_REST_Request $request)
    {
        if (!class_exists('\UiCore\Import')) {
            require_once UICORE_INCLUDES . '/extra/class-import.php';
        }
        $import = new Import($request);
        return rest_ensure_response($import->response);
    }

    /**
     * Process Import Data
     *
     * @param \WP_REST_Request $request
     * @return array
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function update(\WP_REST_Request $request)
    {
        if (!class_exists('\UiCore\Import')) {
            require_once UICORE_INCLUDES . '/extra/class-import.php';
        }
        $import = new Import($request);
        return rest_ensure_response($import->response);
    }

    /**
     * Get Demo List and save it for 7 days
     *
     * @return array
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function get_demos($api = null)
    {
        $demos = [];
        $api = $api ? $api : UICORE_API;
        $is_gutenberg = get_option('uicore_is_gutenberg', null) === 'true' ? true : false;
        $suffix = $is_gutenberg ? '?gutenberg=true' : '';
        $api_response = wp_remote_get($api . '/demos' . $suffix);
        if (!is_wp_error($api_response)) {
            $demos = wp_remote_retrieve_body($api_response);
            set_transient('uicore_demos', $demos, DAY_IN_SECONDS);
        }
        return new WP_REST_Response($demos);
    }

    /**
     * Delete all saved frontend settings
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public function reset_settings()
    {
        delete_option(UICORE_SETTINGS);
        delete_option(UICORE_SETTINGS . '_admin');

        $new = ThemeOptions::get_all_defaults();
        Helper::delete_frontend_transients();

        //update elementor options and glbals
        Settings::elementor_update($new);

        //Update all styles and scripts
        Settings::update_style($new);

        return new WP_REST_Response($new);
    }

    /**
     * Update Preset Manager local List
     *
     * @param array $presets
     * @return \WP_REST_Response
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public function update_presets(array $presets)
    {
        $settings = ThemeOptions::get_admin_db_options();
        $settings['presets'] = $presets;
        $update = update_option(UICORE_SETTINGS . '_admin', $settings, false);

        if (is_wp_error($update)) {
            $response = ['status' => 'error'];
        } else {
            $response = ['status' => 'succes'];
        }

        return new WP_REST_Response($response);
    }

    /**
     * Refresh all Uicore Admin Data Transients;
     * eg:demos, changelog, blocks, pages
     *
     * @return \WP_REST_Response
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public function refresh_transients()
    {
        if (!class_exists('\Uicore\Data')) {
            require_once UICORE_INCLUDES . '/extra/class-data.php';
        }
        delete_transient('uicore_pages');
        delete_transient('uicore_demos');
        $new = $this->get_demos();

        delete_transient('uicore_changelog');
        $new = Data::get_changelog(true);

        delete_transient('uicore_library_v2__blocks');
        $new = Data::get_library('blocks');

        delete_transient('uicore_library_v2__pages');
        $new = Data::get_library('pages');

        delete_transient('uicore_last_version');

        $response = ['status' => 'succes'];
        return new WP_REST_Response($response);
    }

    /**
     * Get Import Log
     *
     * @return WP_REST_Response
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.1
     */
    public function get_import_log()
    {
        return new WP_REST_Response(get_option('uicore_imported_demos', []));
    }

    /**
     * Get Import Log
     *
     * @return WP_REST_Response
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.1
     */
    public function clear_import_log($request)
    {
        if ($request['clear']) {
            return new WP_REST_Response(update_option('uicore_imported_demos', []));
        } else {
            return new WP_Error('Task Imposible');
        }
    }

    /**
     * Sync Typekit Fonts
     *
     * @return WP_REST_Response
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.1
     */
    public function sync_typekit($typekit)
    {

        $data = wp_remote_get(
            'https://typekit.com/api/v1/json/kits/' . $typekit["id"] . '/published',
            array(
                'timeout' => '30',
            )
        );
        if (is_wp_error($data) || wp_remote_retrieve_response_code($data) !== 200) {
            return new WP_REST_Response(['error' => 'Connection to Typekit was failed or bad Project ID. Please try again!']);
        }
        $typekit_info = array();
        $data     = json_decode(wp_remote_retrieve_body($data), true);
        $families = $data['kit']['families'];

        foreach ($families as $family) {

            $family_name = str_replace(' ', '-', $family['name']);

            $typekit_info[$family_name] = array(
                'family'   => $family_name,
                'fallback' => str_replace('"', '', $family['css_stack']),
                'weights'  => array(),
            );

            foreach ($family['variations'] as $variation) {

                $variations = str_split($variation);

                switch ($variations[0]) {
                    case 'n':
                        $style = 'normal';
                        break;
                    default:
                        $style = 'normal';
                        break;
                }

                $weight = $variations[1] . '00';

                if (!in_array($weight, $typekit_info[$family_name]['weights'])) {
                    $typekit_info[$family_name]['weights'][] = $weight;
                }
            }

            $typekit_info[$family_name]['slug']      = $family['slug'];
            $typekit_info[$family_name]['css_names'] = $family['css_names'];
        }



        if ($typekit['id']) {
            return new WP_REST_Response(Data::get_typekit_fonts(null, null, ['fonts' => $typekit_info]));
        } else {
            return new WP_REST_Response(['error' => 'Connection to Typekit was failed or bad Project ID. Please try again!']);
        }
    }


    static function remove_purchase($mode)
    {
        $settings = get_option(UICORE_SETTINGS . '_admin');
        if ($mode === 'remove') {
            unset($settings['connect']);
        } else {
            $settings['connect'] = $mode;
        }
        update_option(UICORE_SETTINGS . '_admin', $settings, false);

        return ["status" => "succes"];
    }


    public function import_library(\WP_REST_Request $request)
    {
        $start = microtime(true);

        if (!isset($request['import'])) {
            return ['status' => 'error'];
        }
        $id = $request['import'];
        $data = [];
        $data = wp_remote_get(UICORE_LIBRARY . 'get/' . $id);
        $data = wp_remote_retrieve_body($data);
        if (\is_wp_error($data) || isset(json_decode($data)->code)) {
            return ['status' => 'error', 'data' => $data];
        }
        $data = json_decode($data);

        if (!isset($data->content)) {
            return ['status' => 'error', 'data2' => $data];
        }
        //check if widget with name bdt- exists in the content
        $data = json_decode($data->content, true);

        //Generate new widget id and activate required ep widgets
        $this->ep_settings = get_option('element_pack_active_modules', []);
        if (!is_array($this->ep_settings)) {
            $this->ep_settings = [];
        }

        $data = $this->ready_for_import($data);

        // If the result is a JSON response we need to return because is an exception
        $decoded_data = is_string($data) ? json_decode($data, true) : $data;
        if ( is_array($decoded_data) && isset($decoded_data['status']) ) {
            return new WP_REST_Response($decoded_data);
        }

        update_option('element_pack_active_modules', $this->ep_settings);

        $get = microtime(true);

        //Need to try again
        if (is_array($this->new_widgets)) {
            return new WP_REST_Response(['status' => 'retry', 'new_widgets' => $this->new_widgets]);
        }
        $data = $this->import_content($data);
        $done = microtime(true);

        $time = 'Total:' . ($done - $start) . 's / get:' . ($get - $start) . 's / import:' . ($done - $get) . 's';
        return new WP_REST_Response(['status' => 'succes', 'time' => $time, 'new_widgets' => $this->new_widgets, 'template' => $data]);
    }

    protected function process_import_content(\Elementor\Controls_Stack $element)
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

                $settings = $element->get_settings($control['name']);

                // Remove placeholder images
                if ($control_class->get_type() === 'media') {
                    $settings['url'] = (strpos($settings['url'], 'placeholder.png') !== false) ? '' : $settings['url'];
                    $settings['url'] = (strpos($settings['url'], '.ogg') !== false) ? '' : $settings['url']; //remove sound effects
                }

                // get the setings from the new widget
                $element_data['settings'][$control['name']] = $control_class->{$method}($settings, $control);
            }
        }

        return $element_data;
    }

    protected function ready_for_import($content)
    {
        try{
            return \Elementor\Plugin::instance()->db->iterate_data($content, function ($element) {

                $element['id'] = \Elementor\Utils::generate_random_string();
                if (isset($element['widgetType'])) {
                    if (self::is_bdp_widget($element['widgetType'])) {

                        // Activates BDT if not active
                        if( ! is_plugin_active('bdthemes-element-pack/bdthemes-element-pack.php') ){
                            $response =  $this->activate_bdt();
                            throw new \Exception(json_encode(['status' => $response]));
                        }

                        $name = $element['widgetType'];
                        $name = str_replace('bdt-', '', $name);
                        $widget_id = str_replace('-', '_', $name);
                        if (!apply_filters("elementpack/module/{$widget_id}", \element_pack_is_widget_enabled($name, $this->ep_settings))) {
                            error_log('called inside bdt methods');
                            $this->ep_settings[$name] = 'on';
                            $this->new_widgets[$element['widgetType']] = [];
                        }
                    }
                }
                return $element;
            });
        } catch (\Exception $e) {;
            return $e->getMessage();
        }
    }

    protected function activate_bdt( $retry_count = 0 )
    {

        $plugin_path = 'bdthemes-element-pack/bdthemes-element-pack.php';
        if ( is_plugin_active($plugin_path) ) {
            // Manually include the plugin file to load its functions now
            include_once WP_PLUGIN_DIR . '/' . $plugin_path;
            return 'reload';
        }

        if ($retry_count >= 5) {
            error_log('Maximum retries reached. Plugin activation failed.');
            return 'error';
        }

        $result = activate_plugin($plugin_path);

        if (is_wp_error($result)) {
            error_log('Plugin not activated: ' . $result->get_error_message());
            return 'error';
        }

        sleep(1);

        return $this->activate_bdt($retry_count + 1);
    }

    protected function import_content($content)
    {
        return \Elementor\Plugin::instance()->db->iterate_data(
            $content,
            function ($element_data) {
                $element = \Elementor\Plugin::instance()->elements_manager->create_element_instance($element_data);

                if (!$element) {
                    return null;
                }

                return $this->process_import_content($element);
            }
        );
    }

    static function is_bdp_widget($widget_name)
    {
        if (substr($widget_name, 0, 4) === "bdt-") {
            return true;
        }
        $other_widget = [
            'lightbox'
        ];
        if (\in_array($widget_name, $other_widget)) {
            return true;
        }
        return false;
    }
}
// new Api();
