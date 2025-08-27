<?php
/*
Plugin Name: UiCore Elements
Plugin URI: https://elements.uicore.co
Description: Elementor Widgets and Theme Builder Elements
Version: 1.3.5
Author: UiCore
Author URI: https://uicore.co
License: GPL3
Text Domain: uicore-elements
Domain Path: /languages
 * Elementor requires at least: 3.19.2
 * Elementor tested up to: 3.31.2
*/

namespace UiCoreElements;

// don't call the file directly
if (!defined('ABSPATH')) exit;

/**
 * Base class
 *
 * @class Base The class that holds the entire plugin
 */
final class Base
{

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.3.5';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * Constructor for the Base class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct()
    {

        $this->define_constants();

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('plugins_loaded', array($this, 'init_plugin'));
    }

    /**
     * Initializes the Base() class
     *
     * Checks for an existing Base() instance
     * and if it doesn't find one, creates it.
     */
    public static function init()
    {
        static $instance = false;

        if (! $instance) {
            $instance = new Base();
        }

        return $instance;
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get($prop)
    {
        if (array_key_exists($prop, $this->container)) {
            return $this->container[$prop];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset($prop)
    {
        return isset($this->{$prop}) || isset($this->container[$prop]);
    }

    /**
     * Define the constants
     *
     * @return void
     */
    public function define_constants()
    {
        define('UICORE_ELEMENTS_VERSION', $this->version);
        define('UICORE_ELEMENTS_FILE', __FILE__);
        define('UICORE_ELEMENTS_PATH', dirname(UICORE_ELEMENTS_FILE));
        define('UICORE_ELEMENTS_INCLUDES', UICORE_ELEMENTS_PATH . '/includes');
        define('UICORE_ELEMENTS_URL', plugins_url('', UICORE_ELEMENTS_FILE));
        define('UICORE_ELEMENTS_ASSETS', UICORE_ELEMENTS_URL . '/assets');
        define('UICORE_ELEMENTS_BADGE', '<span title="Powered by UiCore Elements" style="font-size:10px;font-weight:500;background:#5dbad8;color:black;padding:2px 5px;border-radius:3px;margin-right:4px;">UiCore</span> ');
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function init_plugin()
    {
        if (\class_exists('Elementor\Plugin')) {
            $this->includes();
            $this->init_hooks();
        }
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate()
    {

        $installed = get_option('uicore_elements_installed');

        if (! $installed) {
            update_option('uicore_elements_installed', time());
        }

        update_option('uicore_elements_version', UICORE_ELEMENTS_VERSION);
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {}

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes()
    {

        require_once UICORE_ELEMENTS_INCLUDES . '/class-assets.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/class-elementor.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/class-design-cloud.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/class-rest-api.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/class-helper.php';

        if ($this->is_request('admin')) {
            require_once UICORE_ELEMENTS_INCLUDES . '/class-admin.php';
        }

        if ($this->is_request('frontend')) {
            require_once UICORE_ELEMENTS_INCLUDES . '/class-frontend.php';
        }
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks()
    {

        add_action('init', array($this, 'init_classes'));

        // Localize our plugin
        add_action('init', array($this, 'localization_setup'));
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {

        new REST_API();
        new Elementor();
        if ($this->is_request('admin')) {
            $this->container['admin'] = new Admin();
        }

        if ($this->is_request('frontend')) {
            $this->container['frontend'] = new Frontend();
        }

        $this->container['assets'] = new Assets();
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup()
    {
        load_plugin_textdomain('uicore-elements', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();

            case 'frontend':
                return (! is_admin() || defined('DOING_AJAX')) && ! defined('DOING_CRON');
        }
    }
} // Base

$uicore_elements = Base::init();
