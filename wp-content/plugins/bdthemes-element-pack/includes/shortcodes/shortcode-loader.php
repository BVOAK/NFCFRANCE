<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Element_Pack_Shortcode_Loader {

	/**
	 * Class instance.
	 *
	 * @since  5.4.2
	 * @access private
	 * @var    Element_Pack_Shortcode_Loader|null
	 */
	private static $instance = null;

	/**
	 * Get class instance.
	 *
	 * @return Element_Pack_Shortcode_Loader
	 */
	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor: Sets up actions.
	 */
	private function __construct() {
		add_action('init', [$this, 'init'], 9);
	}

	/**
	 * Initialize the plugin functionality (after init hook).
	 */
	public function init() {
		$this->load_dependencies();
		$this->register_actions();
	}

	/**
	 * Load required files for shortcodes.
	 */
	private function load_dependencies() {
		require_once BDTEP_INC_PATH . 'shortcodes/class-element-pack-shortcodes.php';
		require_once BDTEP_INC_PATH . 'shortcodes/shortcode-functions.php';

		// All shortcode elements added here
		require_once BDTEP_INC_PATH . 'shortcodes/elements/alert.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/animated-link.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/author-avatar.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/author-name.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/badge.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/button.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/breadcrumbs.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/current-date.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/current-user.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/clipboard.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/countdown.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/label.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/lightbox.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/notification.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/page-title.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/page-url.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/post-date.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/rating.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/site-title.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/site-url.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/tag-list.php';
		require_once BDTEP_INC_PATH . 'shortcodes/elements/tooltip.php';
	}

	/**
	 * Register shortcodes.
	 */
	private function register_actions() {
		add_action('init', ['Element_Pack_Shortcodes', 'register']);
	}
}

// Initialize the plugin loader
Element_Pack_Shortcode_Loader::get_instance();
