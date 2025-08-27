<?php

namespace ElementPack\Admin;

use Elementor\Modules\Usage\Module;
use Elementor\Tracker;
use ElementPack\Admin\ModuleService;
use ElementPack\Base\Element_Pack_Base;
use ElementPack\Notices;
use ElementPack\Utils;

/**
 * Element Pack Admin Settings Class
 */

class ElementPack_Admin_Settings
{

	public static $modules_list;
	public static $modules_names;

	public static $modules_list_only_widgets;
	public static $modules_names_only_widgets;

	public static $modules_list_only_3rdparty;
	public static $modules_names_only_3rdparty;
	public $license_title;

	const PAGE_ID = 'element_pack_options';

	private $settings_api;

	public $responseObj;
	public $licenseMessage;
	public $showMessage = false;
	private $is_activated = false;

	public function __construct()
	{

		$this->settings_api = new ElementPack_Settings_API;

		$license_key = self::get_license_key();
		$license_email = self::get_license_email();

		Element_Pack_Base::add_on_delete(
			function () {
				update_option('element_pack_license_email', '');
				update_option('element_pack_license_key', '');
				update_option(Element_Pack_Base::get_lic_key_param('element_pack_license_key'), '');
			}
		);

		/**
		 * Mini-Cart issue fixed
		 * Check if MiniCart activate in EP and Elementor
		 * If both is activated then Show Notice
		 */

		$ep_3rdPartyOption = get_option('element_pack_third_party_widget');

		$el_use_mini_cart = get_option('elementor_use_mini_cart_template');

		if ($el_use_mini_cart !== false && $ep_3rdPartyOption !== false) {
			if ($ep_3rdPartyOption) {
				if ('yes' == $el_use_mini_cart && isset($ep_3rdPartyOption['wc-mini-cart']) && 'off' !== trim($ep_3rdPartyOption['wc-mini-cart'])) {
					add_action('admin_notices', [$this, 'el_use_mini_cart'], 10, 3);
				}
			}
		}

		// Check if we're on a subsite with license activated on main site
		$subsite_status = Element_Pack_Base::get_subsite_license_status();
		
		// For subsites with main site licensed, set activated to true
		if ($subsite_status['is_subsite'] && $subsite_status['is_main_site_licensed']) {
			$this->is_activated = true;
		} elseif (!empty($license_key) && !empty($license_email)) {
			$this->is_activated = Element_Pack_Base::check_wp_plugin($license_key, $license_email, $this->licenseMessage, $this->responseObj, BDTEP__FILE__);
		}

		if (!$this->is_activated) {
			if (!empty($this->licenseMessage)) {
				$this->showMessage = true;
			}
		}

		// Set up license actions based on activation status
		if ($this->is_activated) {
			add_action('admin_post_element_pack_deactivate_license', [$this, 'action_deactivate_license']);
		} else {
			add_action('admin_post_element_pack_activate_license', [$this, 'action_activate_license']);
			
			// Show admin notices for license activation (but not on license page)
			if (!isset($_GET['page']) || $_GET['page'] !== 'element_pack_license') {
				add_action('admin_notices', [$this, 'license_activate_notice'], 10, 3);
				
				if (!empty($this->licenseMessage)) {
					add_action('admin_notices', [$this, 'license_activate_error_notice'], 10, 3);
				}
			}
			
			// Clear invalid license key
			if (!empty($license_key) && !empty($this->licenseMessage)) {
				update_option(Element_Pack_Base::get_lic_key_param('element_pack_license_key'), "");
			}
		}

		// Process license title for white label functionality
		$license_info = Element_Pack_Base::get_register_info();
		$title_info = isset($license_info->license_title) && !empty($license_info->license_title) && $license_info->license_title ? $license_info->license_title : 'None';
		$this->license_title = $title_info;
		$this->license_wl_process();

		// Dynamic white label show hide admin notice end

		if ( ! defined( 'BDTEP_HIDE' ) || ! BDTEP_HIDE || false == self::license_wl_status() ) {
			add_action( 'admin_init', [ $this, 'admin_init' ] );
			add_action( 'admin_menu', [ $this, 'admin_menu' ], 201 );
			add_action( 'admin_menu', [ $this, 'admin_license_menu' ], 202 );
		}

		/**
		 * black_friday_notice
		 * Will be not show after 2024-12-06 00:00:00
		 */
		$current_date = date('Y-m-d H:i:s');
		$end_date = '2024-12-06 00:00:00';

		if (strtotime($current_date) < strtotime($end_date)) {
			add_action('admin_notices', [$this, 'black_friday_notice'], 10, 3);
		}

		// Handle white label access link
		$this->handle_white_label_access();

		// Add custom CSS/JS functionality
		$this->init_custom_code_functionality();
		
		// Add white label icon CSS
		add_action( 'admin_head', [ $this, 'inject_white_label_icon_css' ] );
		
		// Add AJAX handler for plugin installation
		add_action('wp_ajax_ep_install_plugin', [$this, 'install_plugin_ajax']);
	}

	/**
	 * Handle white label access link
	 * 
	 * @access private
	 * @return void
	 */
	private function handle_white_label_access() {
		// Check if this is a white label access request
		if ( ! isset( $_GET['ep_wl'] ) || ! isset( $_GET['license'] ) ) {
			return;
		}

		// Check user capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		$ep_wl = sanitize_text_field( $_GET['ep_wl'] );
		$license_key = sanitize_text_field( $_GET['license'] );

		// Check if ep_wl is set to 1
		if ( $ep_wl !== '1' ) {
			$this->show_access_error( 'Invalid access parameter. Please use the correct link from your email.' );
			return;
		}

		// Get current license key
		$current_license_key = self::get_license_key();

		// Check license key match
		if ( $current_license_key !== $license_key ) {
			$this->show_access_error( 'License key mismatch. Please use the correct access link.' );
			return;
		}

		// Valid access - temporarily allow access by setting a flag
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 201 );
		add_action( 'admin_menu', [ $this, 'admin_license_menu' ], 202 );

		// Add success notice
		add_action( 'admin_notices', function() {
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p><strong>✅ White Label Access Granted!</strong> You can now modify white label settings.</p>';
			echo '</div>';
		} );
	}

	/**
	 * Show access error page
	 * 
	 * @access private
	 * @param string $message
	 * @return void
	 */
	private function show_access_error( $message ) {
		wp_die( 
			'<h1>🔒 Element Pack White Label Access</h1>' .
			'<p><strong>Access Denied:</strong> ' . esc_html( $message ) . '</p>' .
			'<p>If you need assistance, please contact support with your license information.</p>' .
			'<p><a href="' . admin_url() . '" class="button button-primary">← Return to Dashboard</a></p>',
			'Access Denied',
			[ 'response' => 403 ]
		);
	}

	/**
	 * Inject white label icon CSS
	 * 
	 * @access public
	 * @return void
	 */
	public function inject_white_label_icon_css() {
		$white_label_enabled = get_option('ep_white_label_enabled', false);
		$white_label_icon = get_option('ep_white_label_icon', '');
		
		// Only inject CSS when white label is enabled AND a custom icon is set
		if ( $white_label_enabled && ! empty( $white_label_icon ) ) {
			echo '<style type="text/css">';
			echo '#toplevel_page_element_pack_options .wp-menu-image {';
			echo 'background-image: url(' . esc_url( $white_label_icon ) . ') !important;';
			echo 'background-size: 20px 20px !important;';
			echo 'background-repeat: no-repeat !important;';
			echo 'background-position: center !important;';
			echo '}';
			echo '#toplevel_page_element_pack_options .wp-menu-image:before {';
			echo 'display: none !important;';
			echo '}';
			echo '#toplevel_page_element_pack_options .wp-menu-image img {';
			echo 'display: none !important;';
			echo '}';
			echo '</style>';
		}
		// When white label is disabled or no icon is set, don't inject any CSS
		// This allows WordPress's original icon to display naturally
	}

	/**
	 * Initialize Custom Code Functionality
	 * 
	 * @access public
	 * @return void
	 */
	public function init_custom_code_functionality() {
		// AJAX handler for saving custom code (admin only)
		add_action( 'wp_ajax_ep_save_custom_code', [ $this, 'save_custom_code_ajax' ] );
		
		// AJAX handler for saving white label settings (admin only)
		add_action( 'wp_ajax_ep_save_white_label', [ $this, 'save_white_label_ajax' ] );
		
		// Admin scripts (admin only)
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_custom_code_scripts' ] );
		
		// Frontend injection is now handled by global functions in the main plugin file
		self::init_frontend_injection();
	}

	/**
	 * Initialize frontend injection hooks (works on both admin and frontend)
	 * 
	 * @access public static
	 * @return void
	 */
	public static function init_frontend_injection() {
		// Frontend hooks are now registered in the main plugin file
		// This method is kept for backwards compatibility but does nothing
	}

	/**
	 * Enqueue scripts for custom code editor
	 * 
	 * @access public
	 * @return void
	 */
	public function enqueue_custom_code_scripts( $hook ) {
		if ( $hook !== 'toplevel_page_element_pack_options' ) {
			return;
		}

		// Enqueue WordPress built-in CodeMirror 
		wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
		wp_enqueue_code_editor( array( 'type' => 'application/javascript' ) );
		
		// Enqueue WordPress media library scripts
		wp_enqueue_media();
		
		// Enqueue the admin script if it exists
		$admin_script_path = BDTEP_ASSETS_PATH . 'js/ep-admin.js';
		if ( file_exists( $admin_script_path ) ) {
			wp_enqueue_script( 
				'ep-admin-script', 
				BDTEP_ASSETS_URL . 'js/ep-admin.js', 
				[ 'jquery', 'media-upload', 'media-views', 'code-editor' ], 
				BDTEP_VER, 
				true 
			);
			
			// Localize script with AJAX data
			wp_localize_script( 'ep-admin-script', 'ep_admin_ajax', [
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ep_custom_code_nonce' ),
				'white_label_nonce' => wp_create_nonce( 'ep_white_label_nonce' )
			] );
		} else {
			// Fallback: localize to jquery if the admin script doesn't exist
			wp_localize_script( 'jquery', 'ep_admin_ajax', [
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ep_custom_code_nonce' ),
				'white_label_nonce' => wp_create_nonce( 'ep_white_label_nonce' )
			] );
		}
	}

	/**
	 * AJAX handler for saving custom code
	 * 
	 * @access public
	 * @return void
	 */
	public function save_custom_code_ajax() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'ep_custom_code_nonce' ) ) {
			wp_send_json_error( [ 'message' => 'Invalid security token.' ] );
		}

		// Check user capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
		}

		// Sanitize and save the custom code
		$custom_css = isset( $_POST['custom_css'] ) ? wp_unslash( $_POST['custom_css'] ) : '';
		$custom_js = isset( $_POST['custom_js'] ) ? wp_unslash( $_POST['custom_js'] ) : '';
		$custom_css_2 = isset( $_POST['custom_css_2'] ) ? wp_unslash( $_POST['custom_css_2'] ) : '';
		$custom_js_2 = isset( $_POST['custom_js_2'] ) ? wp_unslash( $_POST['custom_js_2'] ) : '';

		// Handle excluded pages - ensure we get proper array format
		$excluded_pages = array();
		if ( isset( $_POST['excluded_pages'] ) ) {
			if ( is_array( $_POST['excluded_pages'] ) ) {
				$excluded_pages = $_POST['excluded_pages'];
			} elseif ( is_string( $_POST['excluded_pages'] ) && ! empty( $_POST['excluded_pages'] ) ) {
				// Handle case where it might be a single value
				$excluded_pages = [ $_POST['excluded_pages'] ];
			}
		}
		
		// Sanitize excluded pages - convert to integers and remove empty values
		$excluded_pages = array_map( 'intval', $excluded_pages );
		$excluded_pages = array_filter( $excluded_pages, function( $page_id ) {
			return $page_id > 0;
		} );

		// Save to database
		update_option( 'ep_custom_css', $custom_css );
		update_option( 'ep_custom_js', $custom_js );
		update_option( 'ep_custom_css_2', $custom_css_2 );
		update_option( 'ep_custom_js_2', $custom_js_2 );
		update_option( 'ep_excluded_pages', $excluded_pages );

		wp_send_json_success( [ 
			'message' => 'Custom code saved successfully!',
			'excluded_count' => count( $excluded_pages )
		] );
	}

	/**
	 * AJAX handler for saving white label settings
	 * 
	 * @access public
	 * @return void
	 */
	public function save_white_label_ajax() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'ep_white_label_nonce' ) ) {
			wp_send_json_error( [ 'message' => 'Invalid security token.' ] );
		}

		// Check user capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
		}

		// Sanitize and save white label setting
		$white_label_enabled = isset( $_POST['white_label_enabled'] ) && $_POST['white_label_enabled'] == '1';
		
		// Sanitize and save white label title
		$white_label_title = isset( $_POST['white_label_title'] ) ? sanitize_text_field( $_POST['white_label_title'] ) : '';

		// Sanitize and save white label icon
		$white_label_icon = isset( $_POST['white_label_icon'] ) ? esc_url_raw( $_POST['white_label_icon'] ) : '';
		$white_label_icon_id = isset( $_POST['white_label_icon_id'] ) ? absint( $_POST['white_label_icon_id'] ) : 0;

		// Sanitize and save license hide option
		$hide_license = isset( $_POST['hide_license'] ) && $_POST['hide_license'] == '1';

		// Sanitize and save BDTEP_HIDE option
		$bdtep_hide = isset( $_POST['bdtep_hide'] ) && $_POST['bdtep_hide'] == '1';

		// Save to database as boolean
		update_option( 'ep_white_label_enabled', $white_label_enabled );
		update_option( 'ep_white_label_title', $white_label_title );
		update_option( 'ep_white_label_icon', $white_label_icon );
		update_option( 'ep_white_label_icon_id', $white_label_icon_id );
		update_option( 'ep_white_label_hide_license', $hide_license );
		update_option( 'ep_white_label_bdtep_hide', $bdtep_hide );

		$email_sent = false;

		// Send email notification if BDTEP_HIDE is enabled
		if ( $bdtep_hide && $white_label_enabled ) {
			$email_sent = $this->send_white_label_access_email();
		}

		// Prepare response message for localhost or email failure
		$response_message = 'White label settings saved successfully!';
		$access_url = '';
		
		if ( $bdtep_hide && $white_label_enabled ) {
			$localhost_email_data = get_option( 'ep_localhost_email_data' );
			
			if ( ! $email_sent && $localhost_email_data ) {
				$access_url = $localhost_email_data['access_url'];
				$response_message .= '<br><br><strong>📧 Localhost Email Notice:</strong><br>Email functionality is not available on localhost. <br><strong>Your Access URL:</strong><br><a href="' . esc_url( $access_url ) . '" target="_blank">Click here to access white label settings</a><br><small>Save this URL - you\'ll need it to modify settings when BDTEP_HIDE is active.</small>';
			}
		}

		wp_send_json_success( [ 
			'message' => $response_message,
			'enabled' => $white_label_enabled,
			'title' => $white_label_title,
			'icon' => $white_label_icon,
			'icon_id' => $white_label_icon_id,
			'hide_license' => $hide_license,
			'bdtep_hide' => $bdtep_hide,
			'email_sent' => $email_sent,
			'access_url' => $access_url,
			'is_localhost' => $this->is_localhost()
		] );
	}

	/**
	 * Send white label access email with special link
	 * 
	 * @access private
	 * @return bool
	 */
	private function send_white_label_access_email() {
		
		$license_email = self::get_license_email();
		$admin_email = get_bloginfo( 'admin_email' );
		$license_key = self::get_license_key();
		$site_name = get_bloginfo( 'name' );
		$site_url = get_bloginfo( 'url' );
		
		// Generate secure access token
		$access_token = wp_hash( $license_key . time() . wp_salt() );
		
		// Store access token in database with expiration (30 days)
		$token_data = [
			'token' => $access_token,
			'license_key' => $license_key
			];
		
		update_option( 'ep_white_label_access_token', $token_data );
		
		// Generate access URL
		$access_url = admin_url( 'admin.php?page=element_pack_options&ep_wl=1&license=' . $license_key . '#element_pack_extra_options_page' );
		
		// Email subject
		$subject = sprintf( '[%s] Element Pack White Label Access Instructions', $site_name );
		
		// Email message
		$message = $this->get_white_label_email_template( $site_name, $site_url, $access_url, $license_key );
		
		// Email headers
		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $site_name . ' <' . $admin_email . '>'
		];
		
		$email_sent = false;
		
		// Send to license email
		if ( ! empty( $license_email ) && is_email( $license_email ) ) {
			$email_sent = wp_mail( $license_email, $subject, $message, $headers );
			
			// If on localhost or email failed, save email content for manual access
			if ( ! $email_sent || $this->is_localhost() ) {
				$this->save_email_content_for_localhost( $access_url, $message, $license_email );
			}
		}
		
		// Send to admin email if different from license email
		if ( ! empty( $admin_email ) && is_email( $admin_email ) && $admin_email !== $license_email ) {
			$admin_email_sent = wp_mail( $admin_email, $subject, $message, $headers );
			if ( $admin_email_sent ) {
				$email_sent = true;
			}
		}
		
		return $email_sent;
	}

	/**
	 * Check if running on localhost
	 * 
	 * @access private
	 * @return bool
	 */
	private function is_localhost() {
		$server_name = $_SERVER['SERVER_NAME'] ?? '';
		$server_addr = $_SERVER['SERVER_ADDR'] ?? '';
		
		$localhost_indicators = [
			'localhost',
			'127.0.0.1',
			'::1',
			'.local',
			'.test',
			'.dev'
		];
		
		foreach ( $localhost_indicators as $indicator ) {
			if ( strpos( $server_name, $indicator ) !== false || 
				 strpos( $server_addr, $indicator ) !== false ) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Save email content for localhost testing
	 * 
	 * @access private
	 * @param string $access_url
	 * @param string $email_content
	 * @param string $recipient_email
	 * @return void
	 */
	private function save_email_content_for_localhost( $access_url, $email_content, $recipient_email ) {
		$email_data = [
			'access_url' => $access_url,
			'email_content' => $email_content,
			'recipient_email' => $recipient_email,
			'message' => 'Email functionality not available on localhost. Use the access URL below:'
		];
		
		// Save for admin notice display
		update_option( 'ep_localhost_email_data', $email_data );
	}

	/**
	 * Get white label email template
	 * 
	 * @access private
	 * @param string $site_name
	 * @param string $site_url  
	 * @param string $access_url
	 * @param string $license_key
	 * @return string
	 */
	private function get_white_label_email_template( $site_name, $site_url, $access_url, $license_key ) {
		$masked_license = substr( $license_key, 0, 8 ) . '****-****-****-' . substr( $license_key, -4 );
		
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title>Element Pack White Label Access</title>
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #2196F3; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
				.content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
				.access-link { background: #2196F3; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
				.warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
				.footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1>🔒 Element Pack White Label Access</h1>
				</div>
				<div class="content">
					<h2>Important: Save This Email!</h2>
					
					<p>Hello,</p>
					
					<p>You have successfully enabled <strong>BDTEP_HIDE mode</strong> for Element Pack Pro on <strong><?php echo esc_html( $site_name ); ?></strong>. This advanced white label feature has hidden the Element Pack interface from your WordPress admin.</p>
					
					<div class="warning">
						<h3>⚠️ IMPORTANT NOTICE</h3>
						<p>Element Pack Pro is now in advanced white label mode. The plugin menus and settings are hidden from the WordPress admin interface. Use the access link below to modify white label settings.</p>
					</div>
					
					<h3>🔗 Your Access Link</h3>
					<p>To access Element Pack white label settings in the future, use this special link:</p>
					
					<p style="text-align: center;">
						<a href="<?php echo esc_url( $access_url ); ?>" class="access-link">Access White Label Settings</a>
					</p>
					
					<p><strong>Direct Link:</strong><br>
					<a href="<?php echo esc_url( $access_url ); ?>"><?php echo esc_html( $access_url ); ?></a></p>
					
					<h3>📋 Access Details</h3>
					<ul>
						<li><strong>Site:</strong> <?php echo esc_html( $site_name ); ?> (<?php echo esc_html( $site_url ); ?>)</li>
						<li><strong>License:</strong> <?php echo esc_html( $masked_license ); ?></li>
						<li><strong>Access Valid Until:</strong> <?php echo date( 'F j, Y', time() + (30 * DAY_IN_SECONDS) ); ?></li>
					</ul>
					
					<div class="warning">
						<h3>🛡️ Security Notes</h3>
						<ul>
							<li>This access link is valid for 30 days</li>
							<li>The link is tied to your license key for security</li>
							<li>Only users with admin capabilities can use this link</li>
							<li>Keep this email safe - you'll need it to access settings</li>
						</ul>
					</div>
					
					<h3>🔧 What You Can Do</h3>
					<p>Using the access link above, you can:</p>
					<ul>
						<li>Modify white label settings</li>
						<li>Change the custom title</li>
						<li>Disable BDTEP_HIDE mode</li>
						<li>Update license hiding options</li>
					</ul>
					
					<p>If you need assistance, please contact support with your license information.</p>
				</div>
				<div class="footer">
					<p>This email was automatically generated from <?php echo esc_html( $site_name ); ?> when Element Pack Pro white label settings were saved.</p>
					<p>Generated on: <?php echo date( 'F j, Y \a\t g:i A T' ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get used widgets.
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */
	public static function get_used_widgets()
	{

		$used_widgets = array();

		if (!Tracker::is_allow_track()) {
			return $used_widgets;
		}

		if (class_exists('Elementor\Modules\Usage\Module')) {

			$module = Module::instance();

			$old_error_level = error_reporting();
			error_reporting(E_ALL & ~E_WARNING); // Suppress warnings
			$elements = $module->get_formatted_usage('raw');
			error_reporting($old_error_level); // Restore

			$ep_widgets = self::get_ep_widgets_names();

			if (is_array($elements) || is_object($elements)) {
				foreach ($elements as $post_type => $data) {
					foreach ($data['elements'] as $element => $count) {
						if (in_array($element, $ep_widgets, true)) {
							if (isset($used_widgets[$element])) {
								$used_widgets[$element] += $count;
							} else {
								$used_widgets[$element] = $count;
							}
						}
					}
				}
			}
		}
		return $used_widgets;
	}

	/**
	 * Get used separate widgets.
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_used_only_widgets()
	{

		$used_widgets = array();

		if (!Tracker::is_allow_track()) {
			return $used_widgets;
		}

		if (class_exists('Elementor\Modules\Usage\Module')) {

			$module = Module::instance();

			$old_error_level = error_reporting();
			error_reporting(E_ALL & ~E_WARNING); // Suppress warnings
			$elements = $module->get_formatted_usage('raw');
			error_reporting($old_error_level); // Restore

			$ep_widgets = self::get_ep_only_widgets();

			if (is_array($elements) || is_object($elements)) {

				foreach ($elements as $post_type => $data) {
					foreach ($data['elements'] as $element => $count) {
						if (in_array($element, $ep_widgets, true)) {
							if (isset($used_widgets[$element])) {
								$used_widgets[$element] += $count;
							} else {
								$used_widgets[$element] = $count;
							}
						}
					}
				}
			}
		}

		return $used_widgets;
	}

	/**
	 * Get used only separate 3rdParty widgets.
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_used_only_3rdparty()
	{

		$used_widgets = array();

		if (!Tracker::is_allow_track()) {
			return $used_widgets;
		}

		if (class_exists('Elementor\Modules\Usage\Module')) {

			$module = Module::instance();

			$old_error_level = error_reporting();
			error_reporting(E_ALL & ~E_WARNING); // Suppress warnings
			$elements = $module->get_formatted_usage('raw');
			error_reporting($old_error_level); // Restore

			$ep_widgets = self::get_ep_only_3rdparty_names();

			if (is_array($elements) || is_object($elements)) {

				foreach ($elements as $post_type => $data) {
					foreach ($data['elements'] as $element => $count) {
						if (in_array($element, $ep_widgets, true)) {
							if (isset($used_widgets[$element])) {
								$used_widgets[$element] += $count;
							} else {
								$used_widgets[$element] = $count;
							}
						}
					}
				}
			}
		}

		return $used_widgets;
	}

	/**
	 * Get unused widgets.
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_unused_widgets()
	{

		if (!current_user_can('manage_options')) {
			die();
		}

		$ep_widgets = self::get_ep_widgets_names();

		$used_widgets = self::get_used_widgets();

		$unused_widgets = array_diff($ep_widgets, array_keys($used_widgets));

		return $unused_widgets;
	}

	/**
	 * Get unused separate widgets.
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_unused_only_widgets()
	{

		if (!current_user_can('manage_options')) {
			die();
		}

		$ep_widgets = self::get_ep_only_widgets();

		$used_widgets = self::get_used_only_widgets();

		$unused_widgets = array_diff($ep_widgets, array_keys($used_widgets));

		return $unused_widgets;
	}

	/**
	 * Get unused separate 3rdparty widgets.
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_unused_only_3rdparty()
	{

		if (!current_user_can('manage_options')) {
			die();
		}

		$ep_widgets = self::get_ep_only_3rdparty_names();

		$used_widgets = self::get_used_only_3rdparty();

		$unused_widgets = array_diff($ep_widgets, array_keys($used_widgets));

		return $unused_widgets;
	}

	/**
	 * Get widgets name
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_ep_widgets_names()
	{
		$names = self::$modules_names;

		if (null === $names) {
			$names = array_map(
				function ($item) {
					return isset($item['name']) ? 'bdt-' . str_replace('_', '-', $item['name']) : 'none';
				},
				self::$modules_list
			);
		}

		return $names;
	}

	/**
	 * Get separate widgets name
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_ep_only_widgets()
	{
		$names = self::$modules_names_only_widgets;

		if (null === $names) {
			$names = array_map(
				function ($item) {
					return isset($item['name']) ? 'bdt-' . str_replace('_', '-', $item['name']) : 'none';
				},
				self::$modules_list_only_widgets
			);
		}

		return $names;
	}

	/**
	 * Get separate 3rdParty widgets name
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_ep_only_3rdparty_names()
	{
		$names = self::$modules_names_only_3rdparty;

		if (null === $names) {
			$names = array_map(
				function ($item) {
					return isset($item['name']) ? 'bdt-' . str_replace('_', '-', $item['name']) : 'none';
				},
				self::$modules_list_only_3rdparty
			);
		}

		return $names;
	}

	/**
	 * Get URL with page id
	 *
	 * @access public
	 *
	 */

	public static function get_url()
	{
		return admin_url('admin.php?page=' . self::PAGE_ID);
	}

	/**
	 * Init settings API
	 *
	 * @access public
	 *
	 */

	public function admin_init()
	{

		//set the settings
		$this->settings_api->set_sections($this->get_settings_sections());
		$this->settings_api->set_fields($this->element_pack_admin_settings());

		//initialize settings
		$this->settings_api->admin_init();
		$this->bdt_redirect_to_renew_link();
	}

	// Redirect to renew link
	public function bdt_redirect_to_renew_link()
	{
		if (isset($_GET['page']) && $_GET['page'] === self::PAGE_ID . '_license_renew') {
			wp_redirect('https://account.bdthemes.com/');
			exit;
		}
	}

	/**
	 * Add Plugin Menus
	 *
	 * @access public
	 *
	 */

	public function admin_menu()
	{
		add_menu_page(
			BDTEP_TITLE . ' ' . esc_html__('Dashboard', 'bdthemes-element-pack'),
			BDTEP_TITLE,
			'manage_options',
			self::PAGE_ID,
			[$this, 'plugin_page'],
			$this->element_pack_icon(),
			58
		);

		add_submenu_page(
			self::PAGE_ID,
			BDTEP_TITLE,
			esc_html__('Core Widgets', 'bdthemes-element-pack'),
			'manage_options',
			self::PAGE_ID . '#element_pack_active_modules',
			[$this, 'plugin_page']
		);

		add_submenu_page(
			self::PAGE_ID,
			BDTEP_TITLE,
			esc_html__('3rd Party Widgets', 'bdthemes-element-pack'),
			'manage_options',
			self::PAGE_ID . '#element_pack_third_party_widget',
			[$this, 'plugin_page']
		);

		add_submenu_page(
			self::PAGE_ID,
			BDTEP_TITLE,
			esc_html__('Extensions', 'bdthemes-element-pack'),
			'manage_options',
			self::PAGE_ID . '#element_pack_elementor_extend',
			[$this, 'plugin_page']
		);

		add_submenu_page(
			self::PAGE_ID,
			BDTEP_TITLE,
			esc_html__('Special Features', 'bdthemes-element-pack'),
			'manage_options',
			self::PAGE_ID . '#element_pack_other_settings',
			[$this, 'plugin_page']
		);

		add_submenu_page(
			self::PAGE_ID,
			BDTEP_TITLE,
			esc_html__('API Settings', 'bdthemes-element-pack'),
			'manage_options',
			self::PAGE_ID . '#element_pack_api_settings',
			[$this, 'plugin_page']
		);
		
		add_submenu_page(
			self::PAGE_ID,
			BDTEP_TITLE,
			esc_html__('Extra Options', 'bdthemes-element-pack'),
			'manage_options',
			self::PAGE_ID . '#element_pack_extra_options',
			[$this, 'plugin_page']
		);
		
		add_submenu_page(
			self::PAGE_ID,
			BDTEP_TITLE,
			esc_html__('System Status', 'bdthemes-element-pack'),
			'manage_options',
			self::PAGE_ID . '#element_pack_analytics_system_req',
			[$this, 'plugin_page']
		);
		
		add_submenu_page(
			self::PAGE_ID,
			BDTEP_TITLE,
			esc_html__('Other Plugins', 'bdthemes-element-pack'),
			'manage_options',
			self::PAGE_ID . '#element_pack_other_plugins',
			[$this, 'plugin_page']
		);
		
		add_submenu_page(
			self::PAGE_ID,
			BDTEP_TITLE,
			esc_html__('Get 50% Payout', 'bdthemes-element-pack'),
			'manage_options',
			self::PAGE_ID . '#element_pack_affiliate',
			[$this, 'plugin_page']
		);

	}

	public function admin_license_menu()
	{



		if (!defined('BDTEP_LO') || false == self::license_wl_status()) {
			add_submenu_page(
				self::PAGE_ID,
				BDTEP_TITLE,
				esc_html__('License', 'bdthemes-element-pack'),
				'manage_options',
				self::PAGE_ID . '#element_pack_license_settings',
				[$this, 'plugin_page']
			);

			$license_info = Element_Pack_Base::get_register_info();

			if (isset($license_info) && isset($license_info->expire_date)) {
				$expire_date = $license_info->expire_date; // "2026-04-19 09:15:25" Or "No expiry"

				if (strtolower($expire_date) !== 'no expiry') {
					$expire_timestamp = strtotime($expire_date);
					$current_timestamp = time();
					$days_left = ($expire_timestamp - $current_timestamp) / (60 * 60 * 24);

					if ($days_left <= 7) {
						add_submenu_page(
							self::PAGE_ID,
							BDTEP_TITLE,
							esc_html__('🔔 Pro Renew Now', 'bdthemes-element-pack'),
							'manage_options',
							self::PAGE_ID . '_license_renew',
							[$this, 'plugin_page']
						);
					}
				}
			}
		}
	}

	/**
	 * Get SVG Icons of Element Pack
	 *
	 * @access public
	 * @return string
	 */

	public function element_pack_icon()
	{
		return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMy4wLjIsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyMzAuN3B4IiBoZWlnaHQ9IjI1NC44MXB4IiB2aWV3Qm94PSIwIDAgMjMwLjcgMjU0LjgxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAyMzAuNyAyNTQuODE7Ig0KCSB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik02MS4wOSwyMjkuMThIMjguOTVjLTMuMTcsMC01Ljc1LTIuNTctNS43NS01Ljc1bDAtMTkyLjA3YzAtMy4xNywyLjU3LTUuNzUsNS43NS01Ljc1aDMyLjE0DQoJYzMuMTcsMCw1Ljc1LDIuNTcsNS43NSw1Ljc1djE5Mi4wN0M2Ni44MywyMjYuNjEsNjQuMjYsMjI5LjE4LDYxLjA5LDIyOS4xOHoiLz4NCjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0yMDcuNSwzMS4zN3YzMi4xNGMwLDMuMTctMi41Nyw1Ljc1LTUuNzUsNS43NUg5MC4wNGMtMy4xNywwLTUuNzUtMi41Ny01Ljc1LTUuNzVWMzEuMzcNCgljMC0zLjE3LDIuNTctNS43NSw1Ljc1LTUuNzVoMTExLjcyQzIwNC45MywyNS42MiwyMDcuNSwyOC4yLDIwNy41LDMxLjM3eiIvPg0KPHBhdGggY2xhc3M9InN0MCIgZD0iTTIwNy41LDExMS4zM3YzMi4xNGMwLDMuMTctMi41Nyw1Ljc1LTUuNzUsNS43NUg5MC4wNGMtMy4xNywwLTUuNzUtMi41Ny01Ljc1LTUuNzV2LTMyLjE0DQoJYzAtMy4xNywyLjU3LTUuNzUsNS43NS01Ljc1aDExMS43MkMyMDQuOTMsMTA1LjU5LDIwNy41LDEwOC4xNiwyMDcuNSwxMTEuMzN6Ii8+DQo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMjA3LjUsMTkxLjN2MzIuMTRjMCwzLjE3LTIuNTcsNS43NS01Ljc1LDUuNzVIOTAuMDRjLTMuMTcsMC01Ljc1LTIuNTctNS43NS01Ljc1VjE5MS4zDQoJYzAtMy4xNywyLjU3LTUuNzUsNS43NS01Ljc1aDExMS43MkMyMDQuOTMsMTg1LjU1LDIwNy41LDE4OC4xMywyMDcuNSwxOTEuM3oiLz4NCjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xNjkuNjIsMjUuNjJoMzIuMTRjMy4xNywwLDUuNzUsMi41Nyw1Ljc1LDUuNzV2MTEyLjFjMCwzLjE3LTIuNTcsNS43NS01Ljc1LDUuNzVoLTMyLjE0DQoJYy0zLjE3LDAtNS43NS0yLjU3LTUuNzUtNS43NVYzMS4zN0MxNjMuODcsMjguMiwxNjYuNDQsMjUuNjIsMTY5LjYyLDI1LjYyeiIvPg0KPC9zdmc+DQo=';
	}

	/**
	 * Get SVG Icons of Element Pack
	 *
	 * @access public
	 * @return array
	 */

	public function get_settings_sections()
	{
		$sections = [
			[
				'id' => 'element_pack_active_modules',
				'title' => esc_html__('Core Widgets', 'bdthemes-element-pack'),
				'icon' => 'dashicons dashicons-screenoptions',
			],
			[
				'id' => 'element_pack_third_party_widget',
				'title' => esc_html__('3rd Party Widgets', 'bdthemes-element-pack'),
				'icon' => 'dashicons dashicons-screenoptions',
			],
			[
				'id' => 'element_pack_elementor_extend',
				'title' => esc_html__('Extensions', 'bdthemes-element-pack'),
				'icon' => 'dashicons dashicons-screenoptions',
			],
			[
				'id' => 'element_pack_other_settings',
				'title' => esc_html__('Special Features', 'bdthemes-element-pack'),
				'icon' => 'dashicons dashicons-screenoptions',
			],
			[
				'id' => 'element_pack_api_settings',
				'title' => esc_html__('API Settings', 'bdthemes-element-pack'),
				'icon' => 'dashicons dashicons-admin-settings',
			],
		];

		return $sections;
	}

	/**
	 * Merge Admin Settings
	 *
	 * @access protected
	 * @return array
	 */

	protected function element_pack_admin_settings()
	{
		return ModuleService::get_widget_settings(function ($settings) {
			$settings_fields = $settings['settings_fields'];

			self::$modules_list = array_merge($settings_fields['element_pack_active_modules'], $settings_fields['element_pack_third_party_widget']);
			self::$modules_list_only_widgets = $settings_fields['element_pack_active_modules'];
			self::$modules_list_only_3rdparty = $settings_fields['element_pack_third_party_widget'];

			return $settings_fields;
		});
	}

	/**
	 * Get Welcome Panel
	 *
	 * @access public
	 * @return void
	 */

	public function element_pack_welcome()
	{

		?>

		<div class="ep-dashboard-panel"
			bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">

			<div class="ep-dashboard-welcome-container">

				<div class="ep-dashboard-item ep-dashboard-welcome bdt-card bdt-card-body">
					<h1 class="ep-feature-title ep-dashboard-welcome-title">
						<?php esc_html_e('Welcome to Element Pack!', 'bdthemes-element-pack'); ?>
					</h1>
					<p class="ep-dashboard-welcome-desc">
						<?php esc_html_e('Empower your web creation with powerful widgets, advanced extensions, and 2700+ ready templates and more.', 'bdthemes-element-pack'); ?>
					</p>

					<div class="ep-dashboard-compare-section">
						<h4 class="ep-feature-sub-title">
							<?php printf(esc_html__('Unlock %sPremium Features%s', 'bdthemes-element-pack'), '<strong class="ep-highlight-text">', '</strong>'); ?>
						</h4>
						<h1 class="ep-feature-title ep-dashboard-compare-title">
							<?php esc_html_e('Create Your Sleek Website with Element Pack Pro!', 'bdthemes-element-pack'); ?>
						</h1>
						<p><?php esc_html_e('Don\'t need more plugins. This pro addon helps you build complex or professional websites—visually stunning, functional and customizable.', 'bdthemes-element-pack'); ?>
						</p>
						<ul>
							<li><?php esc_html_e('Dynamic Content and Integrations', 'bdthemes-element-pack'); ?></li>
							<li><?php esc_html_e('Enhanced Template Library', 'bdthemes-element-pack'); ?></li>
							<li><?php esc_html_e('Theme Builder', 'bdthemes-element-pack'); ?></li>
							<li><?php esc_html_e('Mega Menu Builder', 'bdthemes-element-pack'); ?></li>
							<li><?php esc_html_e('Powerful Widgets and Advanced Extensions', 'bdthemes-element-pack'); ?>
							</li>
						</ul>
						<div class="ep-dashboard-compare-section-buttons">
							<a href="https://www.elementpack.pro/pricing/#a2a0062"
								class="bdt-button bdt-welcome-button bdt-margin-small-right"
								target="_blank"><?php esc_html_e('Compare Free Vs Pro', 'bdthemes-element-pack'); ?></a>
							<a href="https://www.elementpack.pro/pricing/?utm_source=ElementPackLite&utm_medium=PluginPage&utm_campaign=ElementPackLite&coupon=FREETOPRO"
								class="bdt-button bdt-dashboard-sec-btn"
								target="_blank"><?php esc_html_e('Get Premium at 30% OFF', 'bdthemes-element-pack'); ?></a>
						</div>
					</div>
				</div>

				<div class="ep-dashboard-item ep-dashboard-template-quick-access bdt-card bdt-card-body">
					<div class="ep-dashboard-template-section">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/template.jpg'; ?>"
							alt="Element Pack Dashboard Template">
						<h1 class="ep-feature-title ">
							<?php esc_html_e('Faster Web Creation with Sleek and Ready-to-Use Templates!', 'bdthemes-element-pack'); ?>
						</h1>
						<p><?php esc_html_e('Build your wordpress websites of any niche—not from scratch and in a single click.', 'bdthemes-element-pack'); ?>
						</p>
						<a href="https://www.elementpack.pro/ready-templates/"
							class="bdt-button bdt-dashboard-sec-btn bdt-margin-small-top"
							target="_blank"><?php esc_html_e('View Templates', 'bdthemes-element-pack'); ?></a>
					</div>

					<div class="ep-dashboard-quick-access bdt-margin-medium-top">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/support.svg'; ?>"
							alt="Element Pack Dashboard Template">
						<h1 class="ep-feature-title">
							<?php esc_html_e('Getting Started with Quick Access', 'bdthemes-element-pack'); ?>
						</h1>
						<ul>
							<li><a href="https://www.elementpack.pro/contact/"
									target="_blank"><?php esc_html_e('Contact Us', 'bdthemes-element-pack'); ?></a></li>
							<li><a href="https://bdthemes.com/support/"
									target="_blank"><?php esc_html_e('Help Centre', 'bdthemes-element-pack'); ?></a></li>
							<li><a href="https://feedback.bdthemes.com/b/6vr2250l/feature-requests/idea/new"
									target="_blank"><?php esc_html_e('Request a Feature', 'bdthemes-element-pack'); ?></a>
							</li>
						</ul>
						<div class="ep-dashboard-support-section">
							<h1 class="ep-feature-title">
								<i class="dashicons dashicons-phone"></i>
								<?php esc_html_e('24/7 Support', 'bdthemes-element-pack'); ?>
							</h1>
							<p><?php esc_html_e('Helping you get real-time solutions related to web creation with WordPress, Elementor, and Element Pack.', 'bdthemes-element-pack'); ?>
							</p>
							<a href="https://bdthemes.com/support/" class="bdt-margin-small-top"
								target="_blank"><?php esc_html_e('Get Your Support', 'bdthemes-element-pack'); ?></a>
						</div>
					</div>
				</div>

				<div class="ep-dashboard-item ep-dashboard-request-feature bdt-card bdt-card-body">
					<h1 class="ep-feature-title ep-dashboard-template-quick-title">
						<?php esc_html_e('What\'s Stacking You?', 'bdthemes-element-pack'); ?>
					</h1>
					<p><?php esc_html_e('We are always here to help you. If you have any feature request, please let us know.', 'bdthemes-element-pack'); ?>
					</p>
					<a href="https://feedback.elementpack.pro/b/3v2gg80n/feature-requests/idea/new"
						class="bdt-button bdt-dashboard-sec-btn bdt-margin-small-top"
						target="_blank"><?php esc_html_e('Request Your Features', 'bdthemes-element-pack'); ?></a>
				</div>

				<a href="https://www.youtube.com/watch?v=-e-kr4Vkh4E&list=PLP0S85GEw7DOJf_cbgUIL20qqwqb5x8KA" target="_blank"
					class="ep-dashboard-item ep-dashboard-footer-item ep-dashboard-video-tutorial bdt-card bdt-card-body bdt-card-small">
					<span class="ep-dashboard-footer-item-icon">
						<i class="dashicons dashicons-video-alt3"></i>
					</span>
					<h1 class="ep-feature-title"><?php esc_html_e('Watch Video Tutorials', 'bdthemes-element-pack'); ?></h1>
					<p><?php esc_html_e('An invaluable resource for mastering WordPress, Elementor, and Web Creation', 'bdthemes-element-pack'); ?>
					</p>
				</a>
				<a href="https://bdthemes.com/all-knowledge-base-of-element-pack/" target="_blank"
					class="ep-dashboard-item ep-dashboard-footer-item ep-dashboard-documentation bdt-card bdt-card-body bdt-card-small">
					<span class="ep-dashboard-footer-item-icon">
						<i class="dashicons dashicons-admin-tools"></i>
					</span>
					</span>
					<h1 class="ep-feature-title"><?php esc_html_e('Read Easy Documentation', 'bdthemes-element-pack'); ?></h1>
					<p><?php esc_html_e('A way to eliminate the challenges you might face', 'bdthemes-element-pack'); ?></p>
				</a>
				<a href="https://www.facebook.com/bdthemes" target="_blank"
					class="ep-dashboard-item ep-dashboard-footer-item ep-dashboard-community bdt-card bdt-card-body bdt-card-small">
					<span class="ep-dashboard-footer-item-icon">
						<i class="dashicons dashicons-admin-users"></i>
					</span>
					<h1 class="ep-feature-title"><?php esc_html_e('Join Our Community', 'bdthemes-element-pack'); ?></h1>
					<p><?php esc_html_e('A platform for the opportunity to network, collaboration and innovation', 'bdthemes-element-pack'); ?>
					</p>
				</a>
				<a href="https://wordpress.org/plugins/bdthemes-element-pack-lite/#reviews" target="_blank"
					class="ep-dashboard-item ep-dashboard-footer-item ep-dashboard-review bdt-card bdt-card-body bdt-card-small">
					<span class="ep-dashboard-footer-item-icon">
						<i class="dashicons dashicons-star-filled"></i>
					</span>
					<h1 class="ep-feature-title"><?php esc_html_e('Show Your Love', 'bdthemes-element-pack'); ?></h1>
					<p><?php esc_html_e('A way of the assessment of code', 'bdthemes-element-pack'); ?></p>
				</a>
			</div>

		</div>

		<?php
	}

	/**
	 * Others Plugin
	 */

	public function element_pack_others_plugin() {
		// Define plugins with their paths and install URLs
		$plugins = [
			'prime_slider' => [
				'path' => 'bdthemes-prime-slider-lite/bdthemes-prime-slider.php',
				'install_url' => 'https://wordpress.org/plugins/bdthemes-prime-slider-lite/',
				'website_url' => 'https://primeslider.pro/'
			],
			'ultimate_post_kit' => [
				'path' => 'ultimate-post-kit/ultimate-post-kit.php', 
				'install_url' => 'https://wordpress.org/plugins/ultimate-post-kit/',
				'website_url' => 'https://postkit.pro/'
			],
			'ultimate_store_kit' => [
				'path' => 'ultimate-store-kit/ultimate-store-kit.php',
				'install_url' => 'https://wordpress.org/plugins/ultimate-store-kit/',
				'website_url' => 'https://storekit.pro/'
			],
			'pixel_gallery' => [
				'path' => 'pixel-gallery/pixel-gallery.php',
				'install_url' => 'https://wordpress.org/plugins/pixel-gallery/',
				'website_url' => 'https://pixelgallery.pro/'
			],
			'live_copy_paste' => [
				'path' => 'live-copy-paste/live-copy-paste.php',
				'install_url' => 'https://wordpress.org/plugins/live-copy-paste/',
				'website_url' => 'https://www.youtube.com/watch?v=KWxbZfPIcqU'
			],
			'zoloblocks' => [
				'path' => 'zoloblocks/zoloblocks.php',
				'install_url' => 'https://wordpress.org/plugins/zoloblocks/',
				'website_url' => 'https://zoloblocks.com/'
			],
			'spin_wheel' => [
				'path' => 'spin-wheel/spin-wheel.php',
				'install_url' => 'https://wordpress.org/plugins/spin-wheel/',
				'website_url' => 'https://spinwheel.bdthemes.com/'
			],
			'ai_image' => [
				'path' => 'ai-image/ai-image.php',
				'install_url' => 'https://wordpress.org/plugins/ai-image/',
				'website_url' => 'https://www.youtube.com/watch?v=cGmPFU_ju4s'
			],
			'dark_reader' => [
				'path' => 'dark-reader/dark-reader.php',
				'install_url' => 'https://wordpress.org/plugins/dark-reader/',
				'website_url' => 'https://wordpress.org/plugins/dark-reader/'
			],
			'ar_viewer' => [
				'path' => 'ar-viewer/ar-viewer.php',
				'install_url' => 'https://wordpress.org/plugins/ar-viewer/',
				'website_url' => 'https://wordpress.org/plugins/ar-viewer/'
			]
		];
		?>
		<div class="ep-dashboard-panel"
			bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">
			<div class="ep-dashboard-others-plugin">
				<!-- Prime Slider -->
				<div class="bdt-card bdt-card-body bdt-flex bdt-flex-middle bdt-flex-between">
					<div class="bdt-others-plugin-content bdt-flex bdt-flex-middle ">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/prime-slider.svg'; ?>" alt="Prime Slider">
						<div class="bdt-others-plugin-content-text">
							<div class="bdt-others-plugin-user-wrap bdt-flex bdt-flex-middle">
								<h1 class="ep-feature-title "><?php _e('Prime Slider', 'bdthemes-element-pack'); ?></h1>
								<span class="bdt-others-plugin-user"><?php esc_html_e('100k+ active users', 'bdthemes-element-pack'); ?></span>
							</div>
							
							<p><?php _e('The revolutionary slider builder addon for Elementor with next-gen superb interface. It\'s Free! Download it.', 'bdthemes-element-pack'); ?></p>

							<div class="bdt-others-plugin-rating bdt-margin-small-top bdt-flex bdt-flex-middle">
								<span class="bdt-others-plugin-rating-stars">
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-half"></i>
								</span>
								<span class="bdt-others-plugin-rating-text bdt-margin-small-left">
									<?php _e('4.5 out of 5 stars.', 'bdthemes-element-pack'); ?>
								</span>
							</div>
						</div>
						
					</div>
				
					<div class="bdt-others-plugins-link">
				    	<?php echo $this->get_plugin_action_button($plugins['prime_slider']['path'], $plugins['prime_slider']['install_url']); ?>
						<a class="bdt-button bdt-dashboard-sec-btn" target="_blank"
							href="<?php echo esc_url($plugins['prime_slider']['website_url']); ?>">
							<?php _e('View Website', 'bdthemes-element-pack'); ?>
						</a>
					</div>

					
				</div>
				<!-- Ultimate Post Kit -->
				<div class="bdt-card bdt-card-body bdt-flex bdt-flex-middle bdt-flex-between">
					<div class="bdt-others-plugin-content bdt-flex bdt-flex-middle ">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/ultimate-post-kit.svg'; ?>" alt="zoloblocks">
						<div class="bdt-others-plugin-content-text">
							<div class="bdt-others-plugin-user-wrap bdt-flex bdt-flex-middle">
								<h1 class="ep-feature-title "><?php _e('Ultimate Post Kit', 'bdthemes-element-pack'); ?></h1>
								<span class="bdt-others-plugin-user"><?php esc_html_e('30k+ active users', 'bdthemes-element-pack'); ?></span>
							</div>
							
							<p><?php _e('Best blogging addon for building quality blogging website with fine-tuned features and widgets. It\'s Free! Download it.', 'bdthemes-element-pack'); ?></p>

							<div class="bdt-others-plugin-rating bdt-margin-small-top bdt-flex bdt-flex-middle">
								<span class="bdt-others-plugin-rating-stars">
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
								</span>
								<span class="bdt-others-plugin-rating-text bdt-margin-small-left">
									<?php _e('4.8 out of 5 stars.', 'bdthemes-element-pack'); ?>
								</span>
							</div>

						</div>
					</div>
				
					<div class="bdt-others-plugins-link">
				     	<?php echo $this->get_plugin_action_button($plugins['ultimate_post_kit']['path'], $plugins['ultimate_post_kit']['install_url']); ?>
						<a class="bdt-button bdt-dashboard-sec-btn" target="_blank"
							href="<?php echo esc_url($plugins['ultimate_post_kit']['website_url']); ?>">
							<?php _e('View Website', 'bdthemes-element-pack'); ?>
						</a>
					</div>
				</div>
				<!-- Ultimate Store Kit -->
				<div class="bdt-card bdt-card-body bdt-flex bdt-flex-middle bdt-flex-between">
					<div class="bdt-others-plugin-content bdt-flex bdt-flex-middle ">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/ultimate-store-kit.svg'; ?>" alt="zoloblocks">
						<div class="bdt-others-plugin-content-text">
							<div class="bdt-others-plugin-user-wrap bdt-flex bdt-flex-middle">
								<h1 class="ep-feature-title "><?php _e('Ultimate Store Kit', 'bdthemes-element-pack'); ?></h1>
								<span class="bdt-others-plugin-user"><?php esc_html_e('1000+ active users', 'bdthemes-element-pack'); ?></span>
							</div>
							<p><?php _e('The only eCommmerce addon for answering all your online store design problems in one package. It\'s Free! Download it.', 'bdthemes-element-pack'); ?></p>

							<div class="bdt-others-plugin-rating bdt-margin-small-top bdt-flex bdt-flex-middle">
								<span class="bdt-others-plugin-rating-stars">
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-half"></i>
								</span>
								<span class="bdt-others-plugin-rating-text bdt-margin-small-left">
									<?php _e('4.4 out of 5 stars.', 'bdthemes-element-pack'); ?>
								</span>
							</div>

						</div>
					</div>
				
					<div class="bdt-others-plugins-link">
					    <?php echo $this->get_plugin_action_button($plugins['ultimate_store_kit']['path'], $plugins['ultimate_store_kit']['install_url']); ?>
						<a class="bdt-button bdt-dashboard-sec-btn" target="_blank"
							href="<?php echo esc_url($plugins['ultimate_store_kit']['website_url']); ?>">
							<?php _e('View Website', 'bdthemes-element-pack'); ?>
						</a>
					</div>
				</div>
				<!-- Pixel Gallery -->
				<div class="bdt-card bdt-card-body bdt-flex bdt-flex-middle bdt-flex-between">
					<div class="bdt-others-plugin-content bdt-flex bdt-flex-middle ">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/pixel-gallery.svg'; ?>" alt="Pixel Gallery">
						<div class="bdt-others-plugin-content-text">
							<div class="bdt-others-plugin-user-wrap bdt-flex bdt-flex-middle">
								<h1 class="ep-feature-title "><?php _e('Pixel Gallery', 'bdthemes-element-pack'); ?></h1>
								<span class="bdt-others-plugin-user"><?php esc_html_e('3000+ active users', 'bdthemes-element-pack'); ?></span>
							</div>
							<p><?php _e('Pixel Gallery provides more than 30+ essential elements for everyday applications to simplify the whole web building process. It\'s Free! Download it.', 'bdthemes-element-pack'); ?></p>

							<div class="bdt-others-plugin-rating bdt-margin-small-top bdt-flex bdt-flex-middle">
								<span class="bdt-others-plugin-rating-stars">
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
								</span>
								<span class="bdt-others-plugin-rating-text bdt-margin-small-left">
									<?php _e('5 out of 5 stars.', 'bdthemes-element-pack'); ?>
								</span>
							</div>

						</div>
					</div>
				
					<div class="bdt-others-plugins-link">
						<?php echo $this->get_plugin_action_button($plugins['pixel_gallery']['path'], $plugins['pixel_gallery']['install_url']); ?>
						<a class="bdt-button bdt-dashboard-sec-btn" target="_blank"
							href="<?php echo esc_url($plugins['pixel_gallery']['website_url']); ?>">
							<?php _e('View Website', 'bdthemes-element-pack'); ?>
						</a>
					</div>
				</div>
				<!-- Live Copy Paste -->
				<div class="bdt-card bdt-card-body bdt-flex bdt-flex-middle bdt-flex-between">
					<div class="bdt-others-plugin-content bdt-flex bdt-flex-middle ">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/live-copy-paste.svg'; ?>" alt="live copy paste">
						<div class="bdt-others-plugin-content-text">
							<div class="bdt-others-plugin-user-wrap bdt-flex bdt-flex-middle">
								<h1 class="ep-feature-title "><?php _e('Live Copy Paste', 'bdthemes-element-pack'); ?></h1>
								<span class="bdt-others-plugin-user"><?php esc_html_e('3000+ active users', 'bdthemes-element-pack'); ?></span>
							</div>
							<p><?php _e('Superfast cross-domain copy-paste mechanism for WordPress websites with true UI copy experience. It\'s Free! Download it.', 'bdthemes-element-pack'); ?></p>

							<div class="bdt-others-plugin-rating bdt-margin-small-top bdt-flex bdt-flex-middle">
								<span class="bdt-others-plugin-rating-stars">
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-half"></i>
								</span>
								<span class="bdt-others-plugin-rating-text bdt-margin-small-left">
									<?php _e('4.3 out of 5 stars.', 'bdthemes-element-pack'); ?>
								</span>
							</div>

						</div>
					</div>
				
					<div class="bdt-others-plugins-link">
				        <?php echo $this->get_plugin_action_button($plugins['live_copy_paste']['path'], $plugins['live_copy_paste']['install_url']); ?>

						<a class="bdt-button bdt-dashboard-sec-btn" target="_blank"
							href="<?php echo esc_url($plugins['live_copy_paste']['website_url']); ?>">
							<?php _e('Video Tutorial', 'bdthemes-element-pack'); ?>
						</a>
					</div>
				</div>
				<!-- ZoloBlocks -->
				<div class="bdt-card bdt-card-body bdt-flex bdt-flex-middle bdt-flex-between">
					<div class="bdt-others-plugin-content bdt-flex bdt-flex-middle ">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/zoloblocks.svg'; ?>" alt="zoloblocks">
						<div class="bdt-others-plugin-content-text">
							<div class="bdt-others-plugin-user-wrap bdt-flex bdt-flex-middle">
								<h1 class="ep-feature-title "><?php _e('ZoloBlocks', 'bdthemes-element-pack'); ?></h1>
								<span class="bdt-others-plugin-user"><?php esc_html_e('300+ active users', 'bdthemes-element-pack'); ?></span>
							</div>
							<p><?php _e('ZoloBlocks is a collection of blocks for the new WordPress block editor (Gutenberg). It\'s Free! Download it.', 'bdthemes-element-pack'); ?></p>

							<div class="bdt-others-plugin-rating bdt-margin-small-top bdt-flex bdt-flex-middle">
								<span class="bdt-others-plugin-rating-stars">
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
								</span>
								<span class="bdt-others-plugin-rating-text bdt-margin-small-left">
									<?php _e('5 out of 5 stars.', 'bdthemes-element-pack'); ?>
								</span>
							</div>

						</div>
					</div>
				
					<div class="bdt-others-plugins-link">
						<?php echo $this->get_plugin_action_button($plugins['zoloblocks']['path'], $plugins['zoloblocks']['install_url']); ?>
						<a class="bdt-button bdt-dashboard-sec-btn" target="_blank"
							href="<?php echo esc_url($plugins['zoloblocks']['website_url']); ?>">
							<?php _e('View Website', 'bdthemes-element-pack'); ?>
						</a>
					</div>
				</div>
				<!-- Spin Wheel -->
				<div class="bdt-card bdt-card-body bdt-flex bdt-flex-middle bdt-flex-between">
					<div class="bdt-others-plugin-content bdt-flex bdt-flex-middle ">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/spin-wheel.svg'; ?>" alt="spin wheel">
						<div class="bdt-others-plugin-content-text">
							<div class="bdt-others-plugin-user-wrap bdt-flex bdt-flex-middle">
								<h1 class="ep-feature-title "><?php _e('Spin Wheel', 'bdthemes-element-pack'); ?></h1>
								<span class="bdt-others-plugin-user"><?php esc_html_e('100+ active users', 'bdthemes-element-pack'); ?></span>
							</div>
							<p><?php _e('Add a fun, interactive spin wheel to offer instant coupons, boost engagement, and grow your email list. It\'s free!.', 'bdthemes-element-pack'); ?></p>
						</div>
					</div>
				
					<div class="bdt-others-plugins-link">
				        <?php echo $this->get_plugin_action_button($plugins['spin_wheel']['path'], $plugins['spin_wheel']['install_url']); ?>

						<a class="bdt-button bdt-dashboard-sec-btn" target="_blank"
							href="<?php echo esc_url($plugins['spin_wheel']['website_url']); ?>">
							<?php _e('View Website', 'bdthemes-element-pack'); ?>
						</a>
					</div>
				</div>

				<!-- Instant Image Generator -->
				<div class="bdt-card bdt-card-body bdt-flex bdt-flex-middle bdt-flex-between">
					<div class="bdt-others-plugin-content bdt-flex bdt-flex-middle ">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/instant-image-generator.svg'; ?>" alt="instant image generator">
						<div class="bdt-others-plugin-content-text">
							<div class="bdt-others-plugin-user-wrap bdt-flex bdt-flex-middle">
								<h1 class="ep-feature-title "><?php _e('Instant Image Generator', 'bdthemes-element-pack'); ?></h1>
								<span class="bdt-others-plugin-user"><?php esc_html_e('100+ active users', 'bdthemes-element-pack'); ?></span>
							</div>
							<p><?php _e('Instant Image Generator (One Click Image Uploads from Pixabay, Pexels and OpenAI). It\'s Free! Download it.', 'bdthemes-element-pack'); ?></p>
						</div>
					</div>
				
					<div class="bdt-others-plugins-link">
				        <?php echo $this->get_plugin_action_button($plugins['ai_image']['path'], $plugins['ai_image']['install_url']); ?>

						<a class="bdt-button bdt-dashboard-sec-btn" target="_blank"
							href="<?php echo esc_url($plugins['ai_image']['website_url']); ?>">
							<?php _e('Video Tutorial', 'bdthemes-element-pack'); ?>
						</a>
					</div>
				</div>

				<!-- Dark Reader -->
				<div class="bdt-card bdt-card-body bdt-flex bdt-flex-middle bdt-flex-between">
					<div class="bdt-others-plugin-content bdt-flex bdt-flex-middle ">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/dark-reader.svg'; ?>" alt="dark reader">
						<div class="bdt-others-plugin-content-text">
							<div class="bdt-others-plugin-user-wrap bdt-flex bdt-flex-middle">
								<h1 class="ep-feature-title "><?php _e('Dark Reader', 'bdthemes-element-pack'); ?></h1>
								<span class="bdt-others-plugin-user"><?php esc_html_e('New', 'bdthemes-element-pack'); ?></span>
							</div>
							<p><?php _e('Add beautiful dark mode to your WordPress site with customizable settings. Reduce eye strain and improve accessibility. It\'s Free! Download it.', 'bdthemes-element-pack'); ?></p>
						</div>
					</div>
				
					<div class="bdt-others-plugins-link">
				        <?php echo $this->get_plugin_action_button($plugins['dark_reader']['path'], $plugins['dark_reader']['install_url']); ?>

						<a class="bdt-button bdt-dashboard-sec-btn" target="_blank"
							href="<?php echo esc_url($plugins['dark_reader']['website_url']); ?>">
							<?php _e('View Website', 'bdthemes-element-pack'); ?>
						</a>
					</div>
				</div>

				<!-- AR Viewer -->
				<div class="bdt-card bdt-card-body bdt-flex bdt-flex-middle bdt-flex-between">
					<div class="bdt-others-plugin-content bdt-flex bdt-flex-middle ">
						<img src="<?php echo BDTEP_ADMIN_URL . 'assets/images/ar-viewer.svg'; ?>" alt="ar viewer">
						<div class="bdt-others-plugin-content-text">
							<div class="bdt-others-plugin-user-wrap bdt-flex bdt-flex-middle">
								<h1 class="ep-feature-title "><?php _e('AR Viewer', 'bdthemes-element-pack'); ?></h1>
								<span class="bdt-others-plugin-user"><?php esc_html_e('60+ active users', 'bdthemes-element-pack'); ?></span>
							</div>
							<p><?php _e('Augmented Reality Viewer – 3D Model Viewer. It\'s Free! Download it.', 'bdthemes-element-pack'); ?></p>
						</div>
					</div>
				
					<div class="bdt-others-plugins-link">
				        <?php echo $this->get_plugin_action_button($plugins['ar_viewer']['path'], $plugins['ar_viewer']['install_url']); ?>

						<a class="bdt-button bdt-dashboard-sec-btn" target="_blank"
							href="<?php echo esc_url($plugins['ar_viewer']['website_url']); ?>">
							<?php _e('View Website', 'bdthemes-element-pack'); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Widgets Status
	 */

	public function element_pack_widgets_status()
	{
		$track_nw_msg = '';
		if (!Tracker::is_allow_track()) {
			$track_nw = esc_html__('This feature is not working because the Elementor Usage Data Sharing feature is Not Enabled.', 'bdthemes-element-pack');
			$track_nw_msg = 'bdt-tooltip="' . $track_nw . '"';
		}
		?>
		<div class="ep-dashboard-widgets-status">
			<div class="bdt-grid bdt-grid-medium" bdt-grid bdt-height-match="target: > div > .bdt-card">
				<div class="bdt-width-1-2@m bdt-width-1-4@xl">
					<div class="ep-widget-status bdt-card bdt-card-body" <?php echo wp_kses_post($track_nw_msg); ?>>

						<?php
						$used_widgets = count(self::get_used_widgets());
						$un_used_widgets = count(self::get_unused_widgets());
						?>

						<div class="ep-count-canvas-wrap">
							<h1 class="ep-feature-title"><?php esc_html_e('All Widgets', 'bdthemes-element-pack'); ?></h1>
							<div class="bdt-flex bdt-flex-between bdt-flex-middle">
								<div class="ep-count-wrap">
									<div class="ep-widget-count"><?php esc_html_e('Used:', 'bdthemes-element-pack'); ?> <b>
											<?php echo esc_html($used_widgets); ?>
										</b></div>
									<div class="ep-widget-count"><?php esc_html_e('Unused:', 'bdthemes-element-pack'); ?> <b>
											<?php echo esc_html($un_used_widgets); ?>
										</b>
									</div>
									<div class="ep-widget-count"><?php esc_html_e('Total:', 'bdthemes-element-pack'); ?>
										<b>
											<?php echo esc_html($used_widgets + $un_used_widgets); ?>
										</b>
									</div>
								</div>

								<div class="ep-canvas-wrap">
									<canvas id="bdt-db-total-status" style="height: 100px; width: 100px;"
										data-label="Total Widgets Status - (<?php echo esc_html($used_widgets + $un_used_widgets); ?>)"
										data-labels="<?php echo esc_attr('Used, Unused'); ?>"
										data-value="<?php echo esc_attr($used_widgets) . ',' . esc_attr($un_used_widgets); ?>"
										data-bg="#FFD166, #fff4d9" data-bg-hover="#0673e1, #e71522"></canvas>
								</div>
							</div>
						</div>

					</div>
				</div>
				<div class="bdt-width-1-2@m bdt-width-1-4@xl">
					<div class="ep-widget-status bdt-card bdt-card-body" <?php echo wp_kses_post($track_nw_msg); ?>>

						<?php
						$used_only_widgets = count(self::get_used_only_widgets());
						$unused_only_widgets = count(self::get_unused_only_widgets());
						?>


						<div class="ep-count-canvas-wrap">
							<h1 class="ep-feature-title"><?php esc_html_e('Core', 'bdthemes-element-pack'); ?></h1>
							<div class="bdt-flex bdt-flex-between bdt-flex-middle">
								<div class="ep-count-wrap">
									<div class="ep-widget-count"><?php esc_html_e('Used:', 'bdthemes-element-pack'); ?> <b>
											<?php echo esc_html($used_only_widgets); ?>
										</b></div>
									<div class="ep-widget-count"><?php esc_html_e('Unused:', 'bdthemes-element-pack'); ?> <b>
											<?php echo esc_html($unused_only_widgets); ?>
										</b></div>
									<div class="ep-widget-count"><?php esc_html_e('Total:', 'bdthemes-element-pack'); ?>
										<b>
											<?php echo esc_html($used_only_widgets + $unused_only_widgets); ?>
										</b>
									</div>
								</div>

								<div class="ep-canvas-wrap">
									<canvas id="bdt-db-only-widget-status" style="height: 100px; width: 100px;"
										data-label="Core Widgets Status - (<?php echo esc_html($used_only_widgets + $unused_only_widgets); ?>)"
										data-labels="<?php echo esc_attr('Used, Unused'); ?>"
										data-value="<?php echo esc_attr($used_only_widgets) . ',' . esc_attr($unused_only_widgets); ?>"
										data-bg="#EF476F, #ffcdd9" data-bg-hover="#0673e1, #e71522"></canvas>
								</div>
							</div>
						</div>

					</div>
				</div>
				<div class="bdt-width-1-2@m bdt-width-1-4@xl">
					<div class="ep-widget-status bdt-card bdt-card-body" <?php echo wp_kses_post($track_nw_msg); ?>>

						<?php
						$used_only_3rdparty = count(self::get_used_only_3rdparty());
						$unused_only_3rdparty = count(self::get_unused_only_3rdparty());
						?>


						<div class="ep-count-canvas-wrap">
							<h1 class="ep-feature-title"><?php esc_html_e('3rd Party', 'bdthemes-element-pack'); ?></h1>
							<div class="bdt-flex bdt-flex-between bdt-flex-middle">
								<div class="ep-count-wrap">
									<div class="ep-widget-count"><?php esc_html_e('Used:', 'bdthemes-element-pack'); ?> <b>
											<?php echo esc_html($used_only_3rdparty); ?>
										</b></div>
									<div class="ep-widget-count"><?php esc_html_e('Unused:', 'bdthemes-element-pack'); ?> <b>
											<?php echo esc_html($unused_only_3rdparty); ?>
										</b></div>
									<div class="ep-widget-count"><?php esc_html_e('Total:', 'bdthemes-element-pack'); ?>
										<b>
											<?php echo esc_html($used_only_3rdparty + $unused_only_3rdparty); ?>
										</b>
									</div>
								</div>

								<div class="ep-canvas-wrap">
									<canvas id="bdt-db-only-3rdparty-status" style="height: 100px; width: 100px;"
										data-label="3rd Party Widgets Status - (<?php echo esc_html($used_only_3rdparty + $unused_only_3rdparty); ?>)"
										data-labels="<?php echo esc_attr('Used, Unused'); ?>"
										data-value="<?php echo esc_attr($used_only_3rdparty) . ',' . esc_attr($unused_only_3rdparty); ?>"
										data-bg="#06D6A0, #B6FFEC" data-bg-hover="#0673e1, #e71522"></canvas>
								</div>
							</div>
						</div>

					</div>
				</div>

				<div class="bdt-width-1-2@m bdt-width-1-4@xl">
					<div class="ep-widget-status bdt-card bdt-card-body" <?php echo wp_kses_post($track_nw_msg); ?>>

						<div class="ep-count-canvas-wrap">
							<h1 class="ep-feature-title"><?php esc_html_e('Active', 'bdthemes-element-pack'); ?></h1>
							<div class="bdt-flex bdt-flex-between bdt-flex-middle">
								<div class="ep-count-wrap">
									<div class="ep-widget-count"><?php esc_html_e('Core:', 'bdthemes-element-pack'); ?> <b
											id="bdt-total-widgets-status-core">0</b></div>
									<div class="ep-widget-count"><?php esc_html_e('3rd Party:', 'bdthemes-element-pack'); ?>
										<b id="bdt-total-widgets-status-3rd">0</b>
									</div>
									<div class="ep-widget-count"><?php esc_html_e('Extensions:', 'bdthemes-element-pack'); ?>
										<b id="bdt-total-widgets-status-extensions">0</b>
									</div>
									<div class="ep-widget-count"><?php esc_html_e('Total:', 'bdthemes-element-pack'); ?> <b
											id="bdt-total-widgets-status-heading">0</b></div>
								</div>

								<div class="ep-canvas-wrap">
									<canvas id="bdt-total-widgets-status" style="height: 100px; width: 100px;"
										data-label="Total Active Widgets Status"
										data-labels="<?php echo esc_attr('Core, 3rd Party, Extensions'); ?>"
										data-value="0,0,0"
										data-bg="#0680d6, #B0EBFF, #E6F9FF" data-bg-hover="#0673e1, #B0EBFF, #b6f9e8">
									</canvas>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>

		<?php if (!Tracker::is_allow_track()): ?>
			<div class="bdt-border-rounded bdt-box-shadow-small bdt-alert-warning" bdt-alert>
				<a href class="bdt-alert-close" bdt-close></a>
				<div class="bdt-text-default">
					<?php
					esc_html_e('To view widgets analytics, Elementor Usage Data Sharing feature by Elementor needs to be activated. Please activate the feature to get widget analytics instantly ', 'bdthemes-element-pack');
					echo '<a href="' . esc_url(admin_url('admin.php?page=elementor')) . '">from here.</a>';
					?>
				</div>
			</div>
		<?php endif; ?>

		<?php
	}

	/**
	 * Get License Key
	 *
	 * @access public
	 * @return string
	 */

	public static function get_license_key()
	{
		$license_key = get_option(Element_Pack_Base::get_lic_key_param('element_pack_license_key'));
		if (empty($license_key)) {
			$license_key = get_option('element_pack_license_key');
			if (!empty($license_key)) {
				self::set_license_key($license_key);
				update_option('element_pack_license_key', '');
			}
		}
		return trim($license_key);
	}

	/**
	 * Get License Email
	 *
	 * @access public
	 * @return string
	 */

	public static function get_license_email()
	{
		return trim(get_option('element_pack_license_email', get_bloginfo('admin_email')));
	}

	/**
	 * Set License Key
	 *
	 * @access public
	 * @return string
	 */

	public static function set_license_key($license_key)
	{

		return update_option(Element_Pack_Base::get_lic_key_param('element_pack_license_key'), $license_key);
	}

	/**
	 * Set License Email
	 *
	 * @access public
	 * @return string
	 */

	public static function set_license_email($license_email)
	{
		return update_option('element_pack_license_email', $license_email);
	}

	/**
	 * Display License Page
	 *
	 * @access public
	 */

	public function license_page()
	{
		// For multisite subsites, always show subsite message (never show license form)
		if (is_multisite() && !is_main_site()) {
			$subsite_status = Element_Pack_Base::get_subsite_license_status();
			$this->license_subsite_message($subsite_status);
			return; // Important: return after showing subsite message
		}
		
		// For main sites only, show regular license form or activated status
		if ($this->is_activated) {
			$this->license_activated();
		} else {
			$this->license_form();
		}
	}

	/**
	 * Display System Requirement
	 *
	 * @access public
	 * @return void
	 */

	public function element_pack_system_requirement()
	{
		$php_version = phpversion();
		$max_execution_time = ini_get('max_execution_time');
		$memory_limit = ini_get('memory_limit');
		$post_limit = ini_get('post_max_size');
		$uploads = wp_upload_dir();
		$upload_path = $uploads['basedir'];
		$yes_icon = '<span class="valid"><i class="dashicons-before dashicons-yes"></i></span>';
		$no_icon = '<span class="invalid"><i class="dashicons-before dashicons-no-alt"></i></span>';

		$environment = Utils::get_environment_info();

		?>
		<ul class="check-system-status bdt-grid bdt-child-width-1-2@m  bdt-grid-small ">
			<li>
				<div>
					<span class="label1"><?php esc_html_e('PHP Version:', 'bdthemes-element-pack'); ?></span>

					<?php
					if (version_compare($php_version, '7.4.0', '<')) {
						echo wp_kses_post($no_icon);
						echo '<span class="label2" title="' . esc_attr__('Min: 7.4 Recommended', 'bdthemes-element-pack') . '" bdt-tooltip>' . esc_html__('Currently:', 'bdthemes-element-pack') . ' ' . esc_html($php_version) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">' . esc_html__('Currently:', 'bdthemes-element-pack') . ' ' . esc_html($php_version) . '</span>';
					}
					?>
				</div>

			</li>

			<li>
				<div>
					<span class="label1"><?php esc_html_e('Max execution time:', 'bdthemes-element-pack'); ?> </span>
					<?php
					if ($max_execution_time < '90') {
						echo wp_kses_post($no_icon);
						echo '<span class="label2" title="Min: 90 Recommended" bdt-tooltip>Currently: ' . esc_html($max_execution_time) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">Currently: ' . esc_html($max_execution_time) . '</span>';
					}
					?>
				</div>
			</li>
			<li>
				<div>
					<span class="label1"><?php esc_html_e('Memory Limit:', 'bdthemes-element-pack'); ?> </span>

					<?php
					if (intval($memory_limit) < '512') {
						echo wp_kses_post($no_icon);
						echo '<span class="label2" title="Min: 512M Recommended" bdt-tooltip>Currently: ' . esc_html($memory_limit) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">Currently: ' . esc_html($memory_limit) . '</span>';
					}
					?>
				</div>
			</li>

			<li>
				<div>
					<span class="label1"><?php esc_html_e('Max Post Limit:', 'bdthemes-element-pack'); ?> </span>

					<?php
					if (intval($post_limit) < '32') {
						echo wp_kses_post($no_icon);
						echo '<span class="label2" title="Min: 32M Recommended" bdt-tooltip>Currently: ' . wp_kses_post($post_limit) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">Currently: ' . wp_kses_post($post_limit) . '</span>';
					}
					?>
				</div>
			</li>

			<li>
				<div>
					<span class="label1"><?php esc_html_e('Uploads folder writable:', 'bdthemes-element-pack'); ?></span>

					<?php
					if (!is_writable($upload_path)) {
						echo wp_kses_post($no_icon);
					} else {
						echo wp_kses_post($yes_icon);
					}
					?>
				</div>

			</li>

			<li>
				<div>
					<span class="label1"><?php esc_html_e('MultiSite:', 'bdthemes-element-pack'); ?></span>

					<?php
					if ($environment['wp_multisite']) {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">' . esc_html__('MultiSite Enabled', 'bdthemes-element-pack') . '</span>';
						
						// Get multisite info from Element_Pack_Base
						$multisite_info = Element_Pack_Base::get_multisite_info();
						if (!empty($multisite_info['main_site_url'])) {
							echo '<div class="bdt-margin-small-top" style="font-size: 12px; color: #666;">'; 
							echo '<strong>' . esc_html__('License Domain:', 'bdthemes-element-pack') . '</strong> ';
							echo esc_html(preg_replace("(^https?://)", "", $multisite_info['main_site_url']));
							echo '<br><em>' . esc_html__('Single license covers all sites in this network', 'bdthemes-element-pack') . '</em>';
							echo '</div>';
						}
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">' . esc_html__('Single Site', 'bdthemes-element-pack') . '</span>';
					}
					?>
				</div>
			</li>

			<li>
				<div>
					<span class="label1"><?php esc_html_e('GZip Enabled:', 'bdthemes-element-pack'); ?></span>

					<?php
					if ($environment['gzip_enabled']) {
						echo wp_kses_post($yes_icon);
					} else {
						echo wp_kses_post($no_icon);
					}
					?>
				</div>

			</li>

			<li>
				<div>
					<span class="label1"><?php esc_html_e('Debug Mode:', 'bdthemes-element-pack'); ?></span>
					<?php
					if ($environment['wp_debug_mode']) {
						echo wp_kses_post($no_icon);
						echo '<span class="label2">' . esc_html__('Currently Turned On', 'bdthemes-element-pack') . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">' . esc_html__('Currently Turned Off', 'bdthemes-element-pack') . '</span>';
					}
					?>
				</div>

			</li>

		</ul>

		<div class="bdt-admin-alert">
			<strong><?php esc_html_e('Note:', 'bdthemes-element-pack'); ?></strong>
			<?php
			/* translators: %s: Plugin name 'Element Pack' */
			printf(
				esc_html__('If you have multiple addons like %s so you may need to allocate additional memory for other addons as well.', 'bdthemes-element-pack'),
				'<b>Element Pack</b>'
			);
			?>
		</div>

		<?php
	}

	/**
	 * Display Plugin Page
	 *
	 * @access public
	 * @return void
	 */

	public function plugin_page()
	{

		?>

		<div class="wrap element-pack-dashboard">
			<h1></h1> <!-- don't remove this div, it's used for the notice container -->
		
			<div class="ep-dashboard-wrapper bdt-margin-top">
				<div class="ep-dashboard-header bdt-flex bdt-flex-wrap bdt-flex-between bdt-flex-middle"
					bdt-sticky="offset: 32; animation: bdt-animation-slide-top-small; duration: 300">

					<div class="bdt-flex bdt-flex-wrap bdt-flex-middle">
						<!-- Header Shape Elements -->
						<div class="ep-header-elements">
							<span class="ep-header-element ep-header-circle"></span>
							<span class="ep-header-element ep-header-dots"></span>
							<span class="ep-header-element ep-header-line"></span>
							<span class="ep-header-element ep-header-square"></span>
							<span class="ep-header-element ep-header-wave"></span>
						</div>

						<div class="ep-logo">
							<img src="<?php echo BDTEP_URL . 'assets/images/logo-with-text.svg'; ?>" alt="Element Pack Logo">
						</div>
					</div>

					<div class="ep-dashboard-new-page-wrapper bdt-flex bdt-flex-wrap bdt-flex-middle">
						

						<!-- Always render save button, JavaScript will control visibility -->
						<div class="ep-dashboard-save-btn" style="display: none;">
							<button class="bdt-button bdt-button-primary element-pack-settings-save-btn" type="submit">
								<?php esc_html_e('Save Settings', 'bdthemes-element-pack'); ?>
							</button>
						</div>

						<div class="ep-dashboard-new-page">
							<a class="bdt-flex bdt-flex-middle" href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>" class=""><i class="dashicons dashicons-admin-page"></i>
								<?php echo esc_html__('Create New Page', 'bdthemes-element-pack') ?>
							</a>
						</div>
						
					</div>

				</div>

				<div class="ep-dashboard-container bdt-flex">
					<div class="ep-dashboard-nav-container-wrapper">
						<div class="ep-dashboard-nav-container-inner" bdt-sticky="end: !.ep-dashboard-container; offset: 115; animation: bdt-animation-slide-top-small; duration: 300">

							<!-- Navigation Shape Elements -->
							<div class="ep-nav-elements">
								<span class="ep-nav-element ep-nav-circle"></span>
								<span class="ep-nav-element ep-nav-dots"></span>
								<span class="ep-nav-element ep-nav-line"></span>
								<span class="ep-nav-element ep-nav-square"></span>
								<span class="ep-nav-element ep-nav-triangle"></span>
								<span class="ep-nav-element ep-nav-plus"></span>
								<span class="ep-nav-element ep-nav-wave"></span>
							</div>

							<?php $this->settings_api->show_navigation(); ?>
						</div>
					</div>


					<div class="bdt-switcher bdt-tab-container bdt-container-xlarge bdt-flex-1">
						<div id="element_pack_welcome_page" class="ep-option-page group">
							<?php $this->element_pack_welcome(); ?>
						</div>

						<?php
						$this->settings_api->show_forms();
						?>

						<div id="element_pack_extra_options_page" class="ep-option-page group">
							<?php $this->element_pack_extra_options(); ?>
						</div>

						<div id="element_pack_analytics_system_req_page" class="ep-option-page group">
							<?php $this->element_pack_analytics_system_req_content(); ?>
						</div>

						<div id="element_pack_other_plugins_page" class="ep-option-page group">
							<?php $this->element_pack_others_plugin(); ?>
						</div>

						<div id="element_pack_affiliate_page" class="ep-option-page group">
							<?php $this->element_pack_affiliate_content(); ?>
						</div>

						<div id="element_pack_license_settings_page" class="ep-option-page group">
							<?php $this->license_page(); ?>
						</div>

					</div>
				</div>

				<?php if (!defined('BDTEP_WL') || false == self::license_wl_status()) {
					$this->footer_info();
				} ?>
			</div>

		</div>

		<?php

		$this->script();

		?>

		<?php
	}

	/**
	 * License Activate Action
	 * @access public
	 */

	public function action_activate_license()
	{
		check_admin_referer('el-license');

		$licenseKey = !empty($_POST['element_pack_license_key']) ? sanitize_text_field($_POST['element_pack_license_key']) : "";
		$licenseEmail = !empty($_POST['element_pack_license_email']) ? wp_unslash($_POST['element_pack_license_email']) : "";

		update_option(Element_Pack_Base::get_lic_key_param('element_pack_license_key'), $licenseKey);
		update_option("element_pack_license_email", $licenseEmail);

		wp_safe_redirect(admin_url('admin.php?page=' . 'element_pack_options#element_pack_license_settings'));
	}

	/**
	 * License Deactivate Action
	 * @access public
	 */

	public function action_deactivate_license()
	{

		check_admin_referer('el-license');
		if (Element_Pack_Base::remove_license_key(BDTEP__FILE__, $message)) {
			update_option(Element_Pack_Base::get_lic_key_param('element_pack_license_key'), "");
		}
		wp_safe_redirect(admin_url('admin.php?page=' . 'element_pack_options#element_pack_license_settings'));
	}

	/**
	 * Display License Activated
	 *
	 * @access public
	 * @return void
	 */

	public function license_activated()
	{
		?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="element_pack_deactivate_license" />
			<div class="el-license-container bdt-card bdt-card-body">


				<h3 class="el-license-title"><span class="dashicons dashicons-admin-network"></span>
					<?php esc_html_e("Element Pack License Information", 'bdthemes-element-pack'); ?>
				</h3>

				       <ul class="element-pack-license-info bdt-list bdt-list-divider">
           <li>
               <div>
                   <span class="license-info-title"><?php _e('Status', 'bdthemes-element-pack'); ?></span>
                   <span class="license-valid">Valid License</span>
               </div>
           </li>
       </ul>

				<div class="el-license-active-btn">
					<?php wp_nonce_field('el-license'); ?>
					<?php submit_button(esc_html__('Deactivate License', 'bdthemes-element-pack')); ?>
				</div>
			</div>
		</form>
		<?php
	}

	/**
	 * Display License Form
	 *
	 * @access public
	 * @return void
	 */

	public function license_form()
	{
		?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="element_pack_activate_license" />
			<div class="el-license-container bdt-card bdt-card-body">

				<h1 class="bdt-text-large">
					<strong>
						<?php esc_html_e('Enter your license key here, to activate Element Pack Pro, and get full feature updates and premium support.', 'bdthemes-element-pack'); ?>
					</strong>
				</h1>

				<ol class="bdt-text-default">
					<li>
						<?php
						echo wp_kses_post(sprintf('Log in to your <a href="%1s" target="_blank">bdthemes fastspring</a> or <a href="%2s" target="_blank">envato</a> account to get your license key.', esc_url('https://account.bdthemes.com/'), esc_url('https://codecanyon.net/downloads')));
						?>
					</li>
					<li>
						<?php echo wp_kses_post(sprintf('If you don\'t yet have a license key, <a href="%s" target="_blank">get Element Pack Pro now</a>.', esc_url('https://elementpack.pro/pricing/'))); ?>
					</li>
					<li>
						<?php esc_html_e('Copy the license key from your account and paste it below for work element pack properly.', 'bdthemes-element-pack'); ?>
					</li>
					<?php 
					// Show multisite information if this is a multisite installation
					$multisite_info = Element_Pack_Base::get_multisite_info();
					if ($multisite_info['is_multisite']): 
					?>
					<li style="color: #00a32a; font-weight: bold;">
						<?php esc_html_e('✓ Multisite Detected: Your single license will work across all sites in this network.', 'bdthemes-element-pack'); ?>
					</li>
					<?php endif; ?>
				</ol>

				<div class="bdt-ep-license-field">
					<label for="element_pack_license_email">
						<?php esc_html_e('License Email', 'bdthemes-element-pack'); ?>
						<input type="text" class="regular-text code" name="element_pack_license_email" size="50"
							placeholder="example@email.com" required="required">
					</label>
				</div>

				<div class="bdt-ep-license-field">
					<label for="element_pack_license_key"><?php esc_html_e('License Code', 'bdthemes-element-pack'); ?>
						<input type="text" class="regular-text code" name="element_pack_license_key" size="50"
							placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" required="required">
					</label>
					<?php
					// Show error message directly under license field if there's an error
					if (!empty($this->showMessage) && !empty($this->licenseMessage)) {
						?>
						<div class="bdt-license-error-message" style="margin-top: 8px;">
							<div style="padding: 8px 12px; background-color: #fdf2f2; max-width: 650px; border-radius: 4px; color: #dc2626;">
								<strong><?php esc_html_e('License Error:', 'bdthemes-element-pack'); ?></strong> 
								<?php echo wp_kses_post($this->licenseMessage); ?>
							</div>
						</div>
						<?php
					}
					?>
				</div>

				<div class="el-license-active-btn">
					<?php wp_nonce_field('el-license'); ?>
					<?php submit_button(esc_html__('Activate License', 'bdthemes-element-pack')); ?>
				</div>
			</div>
		</form>
		<?php
	}

	/**
	 * Tabbable JavaScript codes & Initiate Color Picker
	 *
	 * This code uses localstorage for displaying active tabs
	 */
	public function script()
	{
		?>
		<script>
			jQuery(document).ready(function () {
				jQuery('.ep-no-result').removeClass('bdt-animation-shake');
			});

			function filterSearch(e) {
				var parentID = '#' + jQuery(e).data('id');
				var search = jQuery(parentID).find('.bdt-search-input').val().toLowerCase();


				jQuery(".ep-options .ep-option-item").filter(function () {
					jQuery(this).toggle(jQuery(this).attr('data-widget-name').toLowerCase().indexOf(search) > -1)
				});

				if (!search) {
					jQuery(parentID).find('.bdt-search-input').attr('bdt-filter-control', "");
					jQuery(parentID).find('.ep-widget-all').trigger('click');
				} else {
					// if (search.length < 3) {
					//     return;
					// }
					jQuery(parentID).find('.bdt-search-input').attr('bdt-filter-control', "filter: [data-widget-name*='" + search + "']");
					jQuery(parentID).find('.bdt-search-input').removeClass('bdt-active');
				}
				jQuery(parentID).find('.bdt-search-input').trigger('click');

			}


			jQuery('.ep-options-parent').each(function (e, item) {
				var eachItem = '#' + jQuery(item).attr('id');
				jQuery(eachItem).on("beforeFilter", function () {
					jQuery(eachItem).find('.ep-no-result').removeClass('bdt-animation-shake');
				});

				jQuery(eachItem).on("afterFilter", function () {
					var isElementVisible = false;
					var i = 0;

					if (jQuery(eachItem).closest(".ep-options-parent").eq(i).is(":visible")) { } else {
						isElementVisible = true;
					}

					while (!isElementVisible && i < jQuery(eachItem).find(".ep-option-item").length) {
						if (jQuery(eachItem).find(".ep-option-item").eq(i).is(":visible")) {
							isElementVisible = true;
						}
						i++;
					}

					if (isElementVisible === false) {
						jQuery(eachItem).find('.ep-no-result').addClass('bdt-animation-shake');
					}

				});
			});

			function clearSearchInputs(context) {
				context.find('.bdt-search-input').val('').attr('bdt-filter-control', '');
			}

			jQuery('.ep-widget-filter-nav li a').on('click', function () {
				// Scroll to top when filter tabs are clicked
				window.scrollTo({
					top: 0,
					behavior: 'smooth'
				});
				
				const wrapper = jQuery(this).closest('.bdt-widget-filter-wrapper');
				clearSearchInputs(wrapper);
			});

			jQuery('.bdt-dashboard-navigation li a').on('click', function () {
				// Scroll to top when main navigation tabs are clicked
				window.scrollTo({
					top: 0,
					behavior: 'smooth'
				});
				
				const tabContainer = jQuery(this).closest('.bdt-dashboard-navigation').siblings('.bdt-tab-container');
				clearSearchInputs(tabContainer);
				tabContainer.find('.bdt-search-input').trigger('keyup');
			});

			jQuery(document).ready(function ($) {
				'use strict';

				// Improved hash handler for tab switching
				function hashHandler() {
					if (window.location.hash) {
						var hash = window.location.hash.substring(1);
						
						// Handle different hash formats
						var targetPage = hash;
						if (hash.includes('_page')) {
							targetPage = hash.replace('_page', '');
						}
						
						// Find the navigation tab that corresponds to this hash
						var $navItem = $('.bdt-dashboard-navigation a[href="#' + targetPage + '"], .bdt-dashboard-navigation a[href="#' + hash + '"]').first();
						
						if ($navItem.length > 0) {
							var tabIndex = $navItem.data('tab-index');
							if (typeof tabIndex !== 'undefined') {
								// Use UIkit tab system
								var $tab = $('.element-pack-dashboard .bdt-tab');
								if (typeof bdtUIkit !== 'undefined' && bdtUIkit.tab) {
									bdtUIkit.tab($tab).show(tabIndex);
								}
							}
						}
					}
				}

				// Handle initial page load
				function onWindowLoad() {
					hashHandler();
				}

				// Initialize on document ready and window load
				if (document.readyState === 'complete') {
					onWindowLoad();
				} else {
					$(window).on('load', onWindowLoad);
				}

				// Listen for hash changes
				window.addEventListener("hashchange", hashHandler, true);

				// Handle admin menu clicks (from WordPress admin sidebar)
				$('.toplevel_page_element_pack_options > ul > li > a').on('click', function (event) {
					// Scroll to top when admin sub menu items are clicked
					window.scrollTo({
						top: 0,
						behavior: 'smooth'
					});
					
					$(this).parent().siblings().removeClass('current');
					$(this).parent().addClass('current');
					
					// Extract hash from href and trigger hash change
					var href = $(this).attr('href');
					if (href && href.includes('#')) {
						var hash = href.substring(href.indexOf('#'));
						if (hash && hash.length > 1) {
							window.location.hash = hash;
						}
					}
				});

				// Handle navigation tab clicks
				$('.bdt-dashboard-navigation a').on('click', function(e) {
					// Scroll to top immediately when tab is clicked
					window.scrollTo({
						top: 0,
						behavior: 'smooth'
					});
					
					var href = $(this).attr('href');
					if (href && href.startsWith('#')) {
						// Update URL hash for proper navigation
						window.history.pushState(null, null, href);
						
						// Trigger hash change for proper tab switching
						setTimeout(function() {
							$(window).trigger('hashchange');
						}, 50);
					}
				});

				// Handle filter navigation clicks (All, Free, Pro, etc.)
				$('.ep-widget-filter-nav li a').on('click', function() {
					// Scroll to top when filter tabs are clicked
					window.scrollTo({
						top: 0,
						behavior: 'smooth'
					});
					
					const wrapper = jQuery(this).closest('.bdt-widget-filter-wrapper');
					clearSearchInputs(wrapper);
				});

				// Handle sub-navigation clicks (within widget pages)
				$(document).on('click', '.bdt-subnav a, .ep-widget-filter a', function() {
					// Scroll to top for sub-navigation clicks
					window.scrollTo({
						top: 0,
						behavior: 'smooth'
					});
				});

				// Enhanced tab switching with scroll to top
				$(document).on('click', '.bdt-tab a, .bdt-tab-item', function(e) {
					// Scroll to top for any tab click
					window.scrollTo({
						top: 0,
						behavior: 'smooth'
					});
				});

				// Advanced tab switching for direct URL access
				function switchToTab(targetId) {
					var $navItem = $('.bdt-dashboard-navigation a[href="#' + targetId + '"]');
					if ($navItem.length > 0) {
						var tabIndex = $navItem.data('tab-index');
						if (typeof tabIndex !== 'undefined') {
							var $tab = $('.element-pack-dashboard .bdt-tab');
							if (typeof bdtUIkit !== 'undefined' && bdtUIkit.tab) {
								bdtUIkit.tab($tab).show(tabIndex);
							}
						}
					}
				}

				// Handle direct section navigation from external links
				$(document).on('click', 'a[href*="#element_pack"]', function(e) {
					var href = $(this).attr('href');
					if (href && href.includes('#element_pack')) {
						var hash = href.substring(href.indexOf('#element_pack'));
						var targetId = hash.substring(1);
						
						// Navigate to the tab
						switchToTab(targetId);
						
						// Update URL
						window.history.pushState(null, null, hash);
					}
				});

				// Activate/Deactivate all widgets functionality
				$('#element_pack_active_modules_page a.ep-active-all-widget').on('click', function (e) {
					e.preventDefault();

					$('#element_pack_active_modules_page .ep-option-item:not(.ep-pro-inactive) .checkbox:visible').each(function () {
						$(this).attr('checked', 'checked').prop("checked", true);
					});

					$(this).addClass('bdt-active');
					$('#element_pack_active_modules_page a.ep-deactive-all-widget').removeClass('bdt-active');
					
					// Ensure save button remains visible
					setTimeout(function() {
						$('.ep-dashboard-save-btn').show();
					}, 100);
				});

				$('#element_pack_active_modules_page a.ep-deactive-all-widget').on('click', function (e) {
					e.preventDefault();

					$('#element_pack_active_modules_page .checkbox:visible').each(function () {
						$(this).removeAttr('checked').prop("checked", false);
					});

					$(this).addClass('bdt-active');
					$('#element_pack_active_modules_page a.ep-active-all-widget').removeClass('bdt-active');
					
					// Ensure save button remains visible
					setTimeout(function() {
						$('.ep-dashboard-save-btn').show();
					}, 100);
				});

				$('#element_pack_third_party_widget_page a.ep-active-all-widget').on('click', function (e) {
					e.preventDefault();

					$('#element_pack_third_party_widget_page .ep-option-item:not(.ep-pro-inactive) .checkbox:visible').each(function () {
						$(this).attr('checked', 'checked').prop("checked", true);
					});

					$(this).addClass('bdt-active');
					$('#element_pack_third_party_widget_page a.ep-deactive-all-widget').removeClass('bdt-active');
					
					// Ensure save button remains visible
					setTimeout(function() {
						$('.ep-dashboard-save-btn').show();
					}, 100);
				});

				$('#element_pack_third_party_widget_page a.ep-deactive-all-widget').on('click', function (e) {
					e.preventDefault();

					$('#element_pack_third_party_widget_page .checkbox:visible').each(function () {
						$(this).removeAttr('checked').prop("checked", false);
					});

					$(this).addClass('bdt-active');
					$('#element_pack_third_party_widget_page a.ep-active-all-widget').removeClass('bdt-active');
					
					// Ensure save button remains visible
					setTimeout(function() {
						$('.ep-dashboard-save-btn').show();
					}, 100);
				});

				$('#element_pack_elementor_extend_page a.ep-active-all-widget').on('click', function (e) {
					e.preventDefault();

					$('#element_pack_elementor_extend_page .ep-option-item:not(.ep-pro-inactive) .checkbox:visible').each(function () {
						$(this).attr('checked', 'checked').prop("checked", true);
					});

					$(this).addClass('bdt-active');
					$('#element_pack_elementor_extend_page a.ep-deactive-all-widget').removeClass('bdt-active');
					
					// Ensure save button remains visible
					setTimeout(function() {
						$('.ep-dashboard-save-btn').show();
					}, 100);
				});

				$('#element_pack_elementor_extend_page a.ep-deactive-all-widget').on('click', function (e) {
					e.preventDefault();

					$('#element_pack_elementor_extend_page .checkbox:visible').each(function () {
						$(this).removeAttr('checked').prop("checked", false);
					});

					$(this).addClass('bdt-active');
					$('#element_pack_elementor_extend_page a.ep-active-all-widget').removeClass('bdt-active');
					
					// Ensure save button remains visible
					setTimeout(function() {
						$('.ep-dashboard-save-btn').show();
					}, 100);
				});

				$('#element_pack_active_modules_page, #element_pack_third_party_widget_page, #element_pack_elementor_extend_page, #element_pack_other_settings_page').find('.ep-pro-inactive .checkbox').each(function () {
					$(this).removeAttr('checked');
					$(this).attr("disabled", true);
				});

			});

			// License Renew Redirect
			jQuery(document).ready(function ($) {
				const renewalLink = $('a[href="admin.php?page=element_pack_options_license_renew"]');
				if (renewalLink.length) {
					renewalLink.attr('target', '_blank');
				}
			});

			// License Renew Redirect
			jQuery(document).ready(function ($) {
				const renewalLink = $('a[href="admin.php?page=element_pack_options_license_renew"]');
				if (renewalLink.length) {
					renewalLink.attr('target', '_blank');
				}
			});

			// Dynamic Save Button Control
			jQuery(document).ready(function ($) {
				// Define pages that need save button - only specific settings pages
				const pagesWithSave = [
					'element_pack_active_modules',        // Core widgets
					'element_pack_third_party_widget',    // 3rd party widgets  
					'element_pack_elementor_extend',      // Extensions
					'element_pack_other_settings',        // Special features
					'element_pack_api_settings'           // API settings
				];

				function toggleSaveButton() {
					const currentHash = window.location.hash.substring(1);
					const saveButton = $('.ep-dashboard-save-btn');
					
					// Check if current page should have save button
					if (pagesWithSave.includes(currentHash)) {
						saveButton.fadeIn(200);
					} else {
						saveButton.fadeOut(200);
					}
				}

				// Force save button to be visible for settings pages
				function forceSaveButtonVisible() {
					const currentHash = window.location.hash.substring(1);
					const saveButton = $('.ep-dashboard-save-btn');
					
					if (pagesWithSave.includes(currentHash)) {
						saveButton.show();
					}
				}

				// Initial check
				toggleSaveButton();

				// Listen for hash changes
				$(window).on('hashchange', function() {
					toggleSaveButton();
				});

				// Listen for tab clicks
				$('.bdt-dashboard-navigation a').on('click', function() {
					setTimeout(toggleSaveButton, 100);
				});

				// Also listen for navigation menu clicks (from show_navigation())
				$(document).on('click', '.bdt-tab a, .bdt-subnav a, .ep-dashboard-nav a, [href*="#element_pack"]', function() {
					setTimeout(toggleSaveButton, 100);
				});

				// Listen for bulk active/deactive button clicks to maintain save button visibility
				$(document).on('click', '.ep-active-all-widget, .ep-deactive-all-widget', function() {
					setTimeout(forceSaveButtonVisible, 50);
				});

				// Listen for individual checkbox changes to maintain save button visibility
				$(document).on('change', '#element_pack_third_party_widget_page .checkbox, #element_pack_elementor_extend_page .checkbox, #element_pack_active_modules_page .checkbox', function() {
					setTimeout(forceSaveButtonVisible, 50);
				});

				// Update URL when navigation items are clicked
				$(document).on('click', '.bdt-tab a, .bdt-subnav a, .ep-dashboard-nav a', function(e) {
					const href = $(this).attr('href');
					if (href && href.includes('#')) {
						const hash = href.substring(href.indexOf('#'));
						if (hash && hash.length > 1) {
							// Update browser URL with the hash
							const currentUrl = window.location.href.split('#')[0];
							const newUrl = currentUrl + hash;
							window.history.pushState(null, null, newUrl);
							
							// Trigger hash change event for other listeners
							$(window).trigger('hashchange');
						}
					}
				});

				// Handle save button click
				$(document).on('click', '.element-pack-settings-save-btn', function(e) {
					e.preventDefault();
					
					// Find the active form in the current tab
					const currentHash = window.location.hash.substring(1);
					let targetForm = null;
					
					// Look for forms in the active tab content
					if (currentHash) {
						// Try to find form in the specific tab page
						targetForm = $('#' + currentHash + '_page form.settings-save');
						
						// If not found, try without _page suffix
						if (!targetForm || targetForm.length === 0) {
							targetForm = $('#' + currentHash + ' form.settings-save');
						}
						
						// Try to find any form in the active tab content
						if (!targetForm || targetForm.length === 0) {
							targetForm = $('#' + currentHash + '_page form');
						}
					}
					
					// Fallback to any visible form with settings-save class
					if (!targetForm || targetForm.length === 0) {
						targetForm = $('form.settings-save:visible').first();
					}
					
					// Last fallback - any visible form
					if (!targetForm || targetForm.length === 0) {
						targetForm = $('.bdt-switcher .group:visible form').first();
					}
					
					if (targetForm && targetForm.length > 0) {
						// Show loading notification
						// bdtUIkit.notification({
						// 	message: '<div bdt-spinner></div> <?php //esc_html_e('Please wait, Saving settings...', 'bdthemes-element-pack') ?>',
						// 	timeout: false
						// });

						// Submit form using AJAX (same logic as existing form submission)
						targetForm.ajaxSubmit({
							success: function () {
								bdtUIkit.notification.closeAll();
								bdtUIkit.notification({
									message: '<span class="dashicons dashicons-yes"></span> <?php esc_html_e('Settings Saved Successfully.', 'bdthemes-element-pack') ?>',
									status: 'primary',
									pos: 'top-center'
								});
							},
							error: function (data) {
								bdtUIkit.notification.closeAll();
								bdtUIkit.notification({
									message: '<span bdt-icon=\'icon: warning\'></span> <?php esc_html_e('Unknown error, make sure access is correct!', 'bdthemes-element-pack') ?>',
									status: 'warning'
								});
							}
						});
					} else {
						// Show error if no form found
						bdtUIkit.notification({
							message: '<span bdt-icon="icon: warning"></span> <?php esc_html_e('No settings form found to save.', 'bdthemes-element-pack') ?>',
							status: 'warning'
						});
					}
				});

				// White Label Settings Functionality
				// Check if ep_admin_ajax is available
				if (typeof ep_admin_ajax === 'undefined') {
					window.ep_admin_ajax = {
						ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
						white_label_nonce: '<?php echo wp_create_nonce('ep_white_label_nonce'); ?>'
					};
				}				
				
				// Initialize CodeMirror editors for custom code
				var codeMirrorEditors = {};
				
				function initializeCodeMirrorEditors() {
					// CSS Editor 1
					if (document.getElementById('ep-custom-css')) {
						codeMirrorEditors['ep-custom-css'] = wp.codeEditor.initialize('ep-custom-css', {
							type: 'text/css',
							codemirror: {
								lineNumbers: true,
								mode: 'css',
								theme: 'default',
								lineWrapping: true,
								autoCloseBrackets: true,
								matchBrackets: true,
								lint: false
							}
						});
					}
					
					// JavaScript Editor 1
					if (document.getElementById('ep-custom-js')) {
						codeMirrorEditors['ep-custom-js'] = wp.codeEditor.initialize('ep-custom-js', {
							type: 'application/javascript',
							codemirror: {
								lineNumbers: true,
								mode: 'javascript',
								theme: 'default',
								lineWrapping: true,
								autoCloseBrackets: true,
								matchBrackets: true,
								lint: false
							}
						});
					}
					
					// CSS Editor 2
					if (document.getElementById('ep-custom-css-2')) {
						codeMirrorEditors['ep-custom-css-2'] = wp.codeEditor.initialize('ep-custom-css-2', {
							type: 'text/css',
							codemirror: {
								lineNumbers: true,
								mode: 'css',
								theme: 'default',
								lineWrapping: true,
								autoCloseBrackets: true,
								matchBrackets: true,
								lint: false
							}
						});
					}
					
					// JavaScript Editor 2
					if (document.getElementById('ep-custom-js-2')) {
						codeMirrorEditors['ep-custom-js-2'] = wp.codeEditor.initialize('ep-custom-js-2', {
							type: 'application/javascript',
							codemirror: {
								lineNumbers: true,
								mode: 'javascript',
								theme: 'default',
								lineWrapping: true,
								autoCloseBrackets: true,
								matchBrackets: true,
								lint: false
							}
						});
					}
					
					// Refresh all editors after a short delay to ensure proper rendering
					setTimeout(function() {
						refreshAllCodeMirrorEditors();
					}, 100);
				}
				
				// Function to refresh all CodeMirror editors
				function refreshAllCodeMirrorEditors() {
					Object.keys(codeMirrorEditors).forEach(function(editorKey) {
						if (codeMirrorEditors[editorKey] && codeMirrorEditors[editorKey].codemirror) {
							codeMirrorEditors[editorKey].codemirror.refresh();
						}
					});
				}
				
				// Function to refresh editors when tab becomes visible
				function refreshEditorsOnTabShow() {
					// Listen for tab changes (UIkit tab switching)
					if (typeof bdtUIkit !== 'undefined' && bdtUIkit.tab) {
						// When tab becomes active, refresh editors
						bdtUIkit.util.on(document, 'shown', '.bdt-tab', function() {
							setTimeout(function() {
								refreshAllCodeMirrorEditors();
							}, 50);
						});
					}
					
					// Also listen for direct tab clicks
					$('.bdt-tab a').on('click', function() {
						setTimeout(function() {
							refreshAllCodeMirrorEditors();
						}, 100);
					});
					
					// Listen for switcher changes (UIkit switcher)
					if (typeof bdtUIkit !== 'undefined' && bdtUIkit.switcher) {
						bdtUIkit.util.on(document, 'shown', '.bdt-switcher', function() {
							setTimeout(function() {
								refreshAllCodeMirrorEditors();
							}, 50);
						});
					}
				}
				
				// Initialize editors when page loads - with delay for better rendering
				setTimeout(function() {
					initializeCodeMirrorEditors();
				}, 100);
				
				// Setup tab switching handlers
				setTimeout(function() {
					refreshEditorsOnTabShow();
				}, 100);
				
				// Handle window resize events
				$(window).on('resize', function() {
					setTimeout(function() {
						refreshAllCodeMirrorEditors();
					}, 100);
				});
				
				// Handle page visibility changes (when switching browser tabs)
				document.addEventListener('visibilitychange', function() {
					if (!document.hidden) {
						setTimeout(function() {
							refreshAllCodeMirrorEditors();
						}, 200);
					}
				});
				
				// Force refresh when clicking on the Custom CSS & JS tab specifically
				$('a[href="#"]').on('click', function() {
					var tabText = $(this).text().trim();
					if (tabText === 'Custom CSS & JS') {
						setTimeout(function() {
							refreshAllCodeMirrorEditors();
						}, 150);
					}
				});

				// Toggle white label fields visibility
				$('#ep-white-label-enabled').on('change', function() {
					if ($(this).is(':checked')) {
						$('.ep-white-label-fields').slideDown(300);
					} else {
						$('.ep-white-label-fields').slideUp(300);
					}
				});

				// WordPress Media Library Integration for Icon Upload
				var mediaUploader;
				
				$('#ep-upload-icon').on('click', function(e) {
					e.preventDefault();
					
					// If the uploader object has already been created, reopen the dialog
					if (mediaUploader) {
						mediaUploader.open();
						return;
					}
					
					// Create the media frame
					mediaUploader = wp.media.frames.file_frame = wp.media({
						title: 'Select Icon',
						button: {
							text: 'Use This Icon'
						},
						library: {
							type: ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml']
						},
						multiple: false
					});
					
					// When an image is selected, run a callback
					mediaUploader.on('select', function() {
						var attachment = mediaUploader.state().get('selection').first().toJSON();
						
						// Set the hidden inputs
						$('#ep-white-label-icon').val(attachment.url);
						$('#ep-white-label-icon-id').val(attachment.id);
						
						// Update preview
						$('#ep-icon-preview-img').attr('src', attachment.url);
						$('.ep-icon-preview-container').show();
					});
					
					// Open the uploader dialog
					mediaUploader.open();
				});
				
				// Remove icon functionality
				$('#ep-remove-icon').on('click', function(e) {
					e.preventDefault();
					
					// Clear the hidden inputs
					$('#ep-white-label-icon').val('');
					$('#ep-white-label-icon-id').val('');
					
					// Hide preview
					$('.ep-icon-preview-container').hide();
					$('#ep-icon-preview-img').attr('src', '');
				});

				// BDTEP_HIDE Warning when checkbox is enabled
				$('#ep-white-label-bdtep-hide').on('change', function() {
					if ($(this).is(':checked')) {
						// Show warning modal/alert
						var warningMessage = '⚠️ WARNING: ADVANCED FEATURE\n\n' +
							'Enabling BDTEP_HIDE will activate advanced white label mode that:\n\n' +
							'• Hides ALL Element Pack branding and menus\n' +
							'• Makes these settings difficult to access later\n' +
							'• Requires the special access link to return\n' +
							'• Is intended for client/agency use only\n\n' +
							'An email with access instructions will be sent if you proceed.\n\n' +
							'Are you sure you want to enable this advanced mode?';
						
						if (!confirm(warningMessage)) {
							// User cancelled, uncheck the box
							$(this).prop('checked', false);
							return false;
						}
						
						// Show additional info message
						if ($('#ep-bdtep-hide-info').length === 0) {
							$(this).closest('.ep-option-item').after(
								'<div id="ep-bdtep-hide-info" class="bdt-alert bdt-alert-warning bdt-margin-small-top">' +
								'<p><strong>BDTEP_HIDE Mode Enabled</strong></p>' +
								'<p>When you save these settings, an email will be sent with instructions to access white label settings in the future.</p>' +
								'</div>'
							);
						}
					} else {
						// Remove info message when unchecked
						$('#ep-bdtep-hide-info').remove();
					}
				});

				// Save white label settings with confirmation
				$('#ep-save-white-label').on('click', function(e) {
					e.preventDefault();
					
					// Check if button is disabled (no license or no agency license)
					if ($(this).prop('disabled')) {
						var buttonText = $(this).text().trim();
						var alertMessage = '';
						
						if (buttonText.includes('License Not Activated')) {
							alertMessage = '<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
								'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
								'<p><strong>License Not Activated</strong><br>You need to activate your Element Pack license to access White Label functionality. Please activate your license first.</p>' +
								'</div>';
						} else {
							alertMessage = '<div class="bdt-alert bdt-alert-warning" bdt-alert>' +
								'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
								'<p><strong>Agency License Required</strong><br>White Label functionality requires an Agency license. Please upgrade your license to access these features.</p>' +
								'</div>';
						}
						
						$('#ep-white-label-message').html(alertMessage).show();
						return false;
					}
					
					// Check if white label mode is being enabled
					var whiteLabelEnabled = $('#ep-white-label-enabled').is(':checked');
					var bdtepHideEnabled = $('#ep-white-label-bdtep-hide').is(':checked');
					
					// Only show confirmation dialog if white label is enabled AND BDTEP_HIDE is enabled
					if (whiteLabelEnabled && bdtepHideEnabled) {
						var confirmMessage = '🔒 FINAL CONFIRMATION\n\n' +
							'You are about to save settings with BDTEP_HIDE enabled.\n\n' +
							'This will:\n' +
							'• Hide Element Pack from WordPress admin immediately\n' +
							'• Send access instructions to your email addresses\n' +
							'• Require the special link to modify these settings\n\n' +
							'Email will be sent to:\n' +
							'• License email: <?php echo esc_js(self::get_license_email()); ?>\n' +
							'• Admin email: <?php echo esc_js(get_bloginfo('admin_email')); ?>\n\n' +
							'Are you absolutely sure you want to proceed?';
						
						if (!confirm(confirmMessage)) {
							return false;
						}
					}
					
					var $button = $(this);
					var originalText = $button.html();
					
					// Show loading state
					$button.html('<span class="dashicons dashicons-update-alt"></span> Saving...');
					$button.prop('disabled', true);
					
					// Collect form data
					var formData = {
						action: 'ep_save_white_label',
						nonce: ep_admin_ajax.white_label_nonce,
						white_label_enabled: $('#ep-white-label-enabled').is(':checked') ? 1 : 0,
						white_label_title: $('#ep-white-label-title').val(),
						white_label_icon: $('#ep-white-label-icon').val(),
						white_label_icon_id: $('#ep-white-label-icon-id').val(),
						hide_license: $('#ep-white-label-hide-license').is(':checked') ? 1 : 0,
						bdtep_hide: $('#ep-white-label-bdtep-hide').is(':checked') ? 1 : 0
					};
					
					// Send AJAX request
					$.post(ep_admin_ajax.ajax_url, formData)
						.done(function(response) {
							if (response.success) {
								// Show success message with countdown
								var countdown = 2;
								var successMessage = response.data.message;
								
								// Add email notification info if BDTEP_HIDE was enabled
								if (response.data.bdtep_hide && response.data.email_sent) {
									successMessage += '<br><br><strong>📧 Access Email Sent!</strong><br>Check your email for the access link to modify these settings in the future.';
								} else if (response.data.bdtep_hide && !response.data.email_sent && response.data.access_url) {
									// Localhost scenario - show the access URL directly
									successMessage += '<br><br><strong>📧 Localhost Email Notice:</strong><br>Email functionality is not available on localhost.<br><strong>Your Access URL:</strong><br><a href="' + response.data.access_url + '" target="_blank">Click here to access white label settings</a><br><small>Save this URL - you\'ll need it to modify settings when BDTEP_HIDE is active.</small>';
								} else if (response.data.bdtep_hide && !response.data.email_sent) {
									successMessage += '<br><br><strong>⚠️ Email Notice:</strong><br>There was an issue sending the access email. Please check your email settings or contact support.';
								}
								
								$('#ep-white-label-message').html(
									'<div class="bdt-alert bdt-alert-success" bdt-alert>' +
									'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
									'<p>' + successMessage + ' <span id="ep-reload-countdown">Reloading in ' + countdown + ' seconds...</span></p>' +
									'</div>'
								).show();
								
								// Update button text
								$button.html('<span class="dashicons dashicons-update-alt"></span> Reloading...');
								
								// Countdown timer
								var countdownInterval = setInterval(function() {
									countdown--;
									if (countdown > 0) {
										$('#ep-reload-countdown').text('Reloading in ' + countdown + ' seconds...');
									} else {
										$('#ep-reload-countdown').text('Reloading now...');
										clearInterval(countdownInterval);
									}
								}, 1000);
								
								// Check if BDTEP_HIDE is enabled and redirect accordingly
								setTimeout(function() {
									if (response.data.bdtep_hide) {
										// Redirect to admin dashboard if BDTEP_HIDE is enabled
										window.location.href = '<?php echo admin_url('index.php'); ?>';
									} else {
										// Reload current page if BDTEP_HIDE is not enabled
										window.location.reload();
									}
								}, 1500);
							} else {
								// Show error message
								$('#ep-white-label-message').html(
									'<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
									'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
									'<p>Error: ' + (response.data.message || 'Unknown error occurred') + '</p>' +
									'</div>'
								).show();
								
								// Restore button state for error case
								$button.html(originalText);
								$button.prop('disabled', false);
							}
						})
						.fail(function(xhr, status, error) {
							// Show error message
							$('#ep-white-label-message').html(
								'<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
								'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
								'<p>Error: Failed to save settings. Please try again. (' + status + ')</p>' +
								'</div>'
							).show();
							
							// Restore button state for failure case
							$button.html(originalText);
							$button.prop('disabled', false);
						});
				});

				// Save custom code functionality (updated for CodeMirror)
				$('#ep-save-custom-code').on('click', function(e) {
					e.preventDefault();
					
					var $button = $(this);
					var originalText = $button.html();
					
					// Prevent multiple simultaneous saves
					if ($button.prop('disabled') || $button.hasClass('ep-saving')) {
						return;
					}
					
					// Mark as saving
					$button.addClass('ep-saving');
					
					// Get content from CodeMirror editors
					function getCodeMirrorContent(elementId) {
						if (codeMirrorEditors[elementId] && codeMirrorEditors[elementId].codemirror) {
							return codeMirrorEditors[elementId].codemirror.getValue();
						} else {
							// Fallback to textarea value
							return $('#' + elementId).val() || '';
						}
					}
					
					var cssContent = getCodeMirrorContent('ep-custom-css');
					var jsContent = getCodeMirrorContent('ep-custom-js');
					var css2Content = getCodeMirrorContent('ep-custom-css-2');
					var js2Content = getCodeMirrorContent('ep-custom-js-2');
					
					// Show loading state
					$button.html('<span class="dashicons dashicons-update-alt"></span> Saving...');
					$button.prop('disabled', true);
					
					// Timeout safeguard - if AJAX doesn't complete in 30 seconds, restore button
					var timeoutId = setTimeout(function() {
						$button.removeClass('ep-saving');
						$button.html(originalText);
						$button.prop('disabled', false);
						$('#ep-custom-code-message').html(
							'<div class="bdt-alert bdt-alert-warning" bdt-alert>' +
							'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
							'<p>Save operation timed out. Please try again.</p>' +
							'</div>'
						).show();
					}, 30000);
					
					// Collect form data
					var formData = {
						action: 'ep_save_custom_code',
						nonce: ep_admin_ajax.nonce,
						custom_css: cssContent,
						custom_js: jsContent,
						custom_css_2: css2Content,
						custom_js_2: js2Content,
						excluded_pages: $('#ep-excluded-pages').val() || []
					};
					
					
					// Verify we have some content before sending (optional check)
					var totalContentLength = cssContent.length + jsContent.length + css2Content.length + js2Content.length;
					if (totalContentLength === 0) {
						var confirmEmpty = confirm('No content detected in any editor. Do you want to save empty content (this will clear all custom code)?');
						if (!confirmEmpty) {
							// Restore button state
							$button.html(originalText);
							$button.prop('disabled', false);
							return;
						}
					}
					
					// Send AJAX request
					$.post(ep_admin_ajax.ajax_url, formData)
						.done(function(response) {
							if (response.success) {
								// Show success message
								var successMessage = response.data.message;
								if (response.data.excluded_count) {
									successMessage += ' (' + response.data.excluded_count + ' pages excluded)';
								}
								
								$('#ep-custom-code-message').html(
									'<div class="bdt-alert bdt-alert-success" bdt-alert>' +
									'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
									'<p>' + successMessage + '</p>' +
									'</div>'
								).show();
								
								// Auto-hide message after 5 seconds
								setTimeout(function() {
									$('#ep-custom-code-message').fadeOut();
								}, 5000);
								
							} else {
								// Show error message
								$('#ep-custom-code-message').html(
									'<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
									'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
									'<p>Error: ' + (response.data.message || 'Unknown error occurred') + '</p>' +
									'</div>'
								).show();
							}
						})
						.fail(function(xhr, status, error) {
							// Show error message
							$('#ep-custom-code-message').html(
								'<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
								'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
								'<p>Error: Failed to save custom code. Please try again. (' + status + ')</p>' +
								'</div>'
							).show();
						})
						.always(function() {
							
							// Clear the timeout since AJAX completed
							clearTimeout(timeoutId);
							
							try {
								$button.removeClass('ep-saving');
								$button.html(originalText);
								$button.prop('disabled', false);
							} catch (e) {
								// Fallback: force button restoration
								$('#ep-save-custom-code').removeClass('ep-saving').html('<span class="dashicons dashicons-yes"></span> Save Custom Code').prop('disabled', false);
							}
						});
				});

				// Reset custom code functionality (updated for CodeMirror)
				$('#ep-reset-custom-code').on('click', function(e) {
					e.preventDefault();
					
					if (confirm('Are you sure you want to reset all custom code? This action cannot be undone.')) {
						// Clear CodeMirror editors
						function clearCodeMirrorEditor(elementId) {
							if (codeMirrorEditors[elementId] && codeMirrorEditors[elementId].codemirror) {
								codeMirrorEditors[elementId].codemirror.setValue('');
							} else {
								// Fallback to clearing textarea
								$('#' + elementId).val('');
							}
						}
						
						// Clear all editors
						clearCodeMirrorEditor('ep-custom-css');
						clearCodeMirrorEditor('ep-custom-js');
						clearCodeMirrorEditor('ep-custom-css-2');
						clearCodeMirrorEditor('ep-custom-js-2');
						
						// Clear exclusions
						$('#ep-excluded-pages').val([]).trigger('change');
						
						$('#ep-custom-code-message').html(
							'<div class="bdt-alert bdt-alert-warning" bdt-alert>' +
							'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
							'<p>All custom code has been cleared. Don\'t forget to save changes!</p>' +
							'</div>'
						).show();
						
						// Auto-hide message after 3 seconds
						setTimeout(function() {
							$('#ep-custom-code-message').fadeOut();
						}, 3000);
					}
				});				
			});

			// Chart.js initialization for system status canvas charts
			function initElementPackCharts() {
				// Wait for Chart.js to be available
				if (typeof Chart === 'undefined') {
					setTimeout(initElementPackCharts, 500);
					return;
				}

				// Chart instances storage
				window.epChartInstances = window.epChartInstances || {};
				window.epChartsInitialized = false;

				// Function to create a chart
				function createChart(canvasId) {
					var canvas = document.getElementById(canvasId);
					if (!canvas) {
						return;
					}

					var $canvas = jQuery('#' + canvasId);
					var valueStr = $canvas.data('value');
					var labelsStr = $canvas.data('labels');
					var bgStr = $canvas.data('bg');

					if (!valueStr || !labelsStr || !bgStr) {
						return;
					}

					// Parse data
					var values = valueStr.toString().split(',').map(v => parseInt(v.trim()) || 0);
					var labels = labelsStr.toString().split(',').map(l => l.trim());
					var colors = bgStr.toString().split(',').map(c => c.trim());

					// Destroy existing chart using Chart.js built-in method
					var existingChart = Chart.getChart(canvas);
					if (existingChart) {
						existingChart.destroy();
					}

					// Also destroy from our instance storage
					if (window.epChartInstances && window.epChartInstances[canvasId]) {
						window.epChartInstances[canvasId].destroy();
						delete window.epChartInstances[canvasId];
					}

					// Create new chart
					try {
						var newChart = new Chart(canvas, {
							type: 'doughnut',
							data: {
								labels: labels,
								datasets: [{
									data: values,
									backgroundColor: colors,
									borderWidth: 0
								}]
							},
							options: {
								responsive: true,
								maintainAspectRatio: false,
								plugins: {
									legend: { display: false },
									tooltip: { enabled: true }
								},
								cutout: '60%'
							}
						});
						
						// Store in our instance storage
						if (!window.epChartInstances) window.epChartInstances = {};
						window.epChartInstances[canvasId] = newChart;
					} catch (error) {
						// Do nothing
					}
				}

				// Update total widgets status
				function updateTotalStatus() {
					var coreCount = jQuery('#element_pack_active_modules_page input:checked').length;
					var thirdPartyCount = jQuery('#element_pack_third_party_widget_page input:checked').length;
					var extensionsCount = jQuery('#element_pack_elementor_extend_page input:checked').length;

					jQuery('#bdt-total-widgets-status-core').text(coreCount);
					jQuery('#bdt-total-widgets-status-3rd').text(thirdPartyCount);
					jQuery('#bdt-total-widgets-status-extensions').text(extensionsCount);
					jQuery('#bdt-total-widgets-status-heading').text(coreCount + thirdPartyCount + extensionsCount);
					
					jQuery('#bdt-total-widgets-status').attr('data-value', [coreCount, thirdPartyCount, extensionsCount].join(','));
				}

				// Initialize all charts once
				function initAllCharts() {
					// Check if charts already exist and are properly rendered
					if (window.epChartInstances && Object.keys(window.epChartInstances).length >= 4) {
						return;
					}
					
					// Update total status first
					updateTotalStatus();
					
					// Create all charts
					var chartCanvases = [
						'bdt-db-total-status',
						'bdt-db-only-widget-status', 
						'bdt-db-only-3rdparty-status',
						'bdt-total-widgets-status'
					];

					var successfulCharts = 0;
					chartCanvases.forEach(function(canvasId) {
						var canvas = document.getElementById(canvasId);
						if (canvas && canvas.offsetParent !== null) { // Check if canvas is visible
							createChart(canvasId);
							if (window.epChartInstances && window.epChartInstances[canvasId]) {
								successfulCharts++;
							}
						}
					});
				}

				// Check if we're currently on system status tab and initialize
				function checkAndInitIfOnSystemStatus() {
					if (window.location.hash === '#element_pack_analytics_system_req') {
						setTimeout(initAllCharts, 300);
					}
				}

				// Initialize charts when DOM is ready
				jQuery(document).ready(function() {
					// Only initialize if we're on the system status tab
					setTimeout(checkAndInitIfOnSystemStatus, 500);
				});

				// Add click handler for System Status tab to create/refresh charts
				jQuery(document).on('click', 'a[href="#element_pack_analytics_system_req"], a[href*="element_pack_analytics_system_req"]', function() {
					setTimeout(function() {
						// Always recreate charts when tab is clicked to ensure they're visible
						initAllCharts();
					}, 200);
				});
			}

			// Start the chart initialization
			setTimeout(initElementPackCharts, 1000);

			// Handle plugin installation via AJAX
			jQuery(document).on('click', '.ep-install-plugin', function(e) {
				e.preventDefault();
				
				var $button = jQuery(this);
				var pluginSlug = $button.data('plugin-slug');
				var nonce = $button.data('nonce');
				var originalText = $button.text();
				
				// Disable button and show loading state
				$button.prop('disabled', true)
					   .text('<?php echo esc_js(__('Installing...', 'bdthemes-element-pack')); ?>')
					   .addClass('bdt-installing');
				
				// Perform AJAX request
				jQuery.ajax({
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					type: 'POST',
					data: {
						action: 'ep_install_plugin',
						plugin_slug: pluginSlug,
						nonce: nonce
					},
					success: function(response) {
						if (response.success) {
							// Show success message
							$button.text('<?php echo esc_js(__('Installed!', 'bdthemes-element-pack')); ?>')
								   .removeClass('bdt-installing')
								   .addClass('bdt-installed');
							
							// Show success notification
							if (typeof bdtUIkit !== 'undefined' && bdtUIkit.notification) {
								bdtUIkit.notification({
									message: '<span class="dashicons dashicons-yes"></span> ' + response.data.message,
									status: 'success'
								});
							}
							
							// Reload the page after 2 seconds to update button states
							setTimeout(function() {
								window.location.reload();
							}, 2000);
							
						} else {
							// Show error message
							$button.prop('disabled', false)
								   .text(originalText)
								   .removeClass('bdt-installing');
							
							// Show error notification
							if (typeof bdtUIkit !== 'undefined' && bdtUIkit.notification) {
								bdtUIkit.notification({
									message: '<span class="dashicons dashicons-warning"></span> ' + response.data.message,
									status: 'danger'
								});
							}
						}
					},
					error: function() {
						// Handle network/server errors
						$button.prop('disabled', false)
							   .text(originalText)
							   .removeClass('bdt-installing');
						
						// Show error notification
						if (typeof bdtUIkit !== 'undefined' && bdtUIkit.notification) {
							bdtUIkit.notification({
								message: '<span class="dashicons dashicons-warning"></span> <?php echo esc_js(__('Installation failed. Please try again.', 'bdthemes-element-pack')); ?>',
								status: 'danger'
							});
						}
					}
				});
			});
		</script>
		<?php
	}

	/**
	 * Display Footer
	 *
	 * @access public
	 * @return void
	 */

	public function footer_info()
	{
		?>
		<div class="element-pack-footer-info bdt-margin-medium-top">
			<div class="bdt-grid ">
				<div class="bdt-width-auto@s ep-setting-save-btn">
				</div>
				<div class="bdt-width-expand@s bdt-text-right">
					<p class="">
						<?php
						/* translators: %1$s: URL link to BdThemes website */
						echo sprintf(
							__('Element Pack Pro plugin made with love by <a target="_blank" href="%1$s">BdThemes</a> Team.<br>All rights reserved by <a target="_blank" href="%1$s">BdThemes.com</a>.', 'bdthemes-element-pack'),
							esc_url('https://bdthemes.com')
						);
						?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * License Active Error
	 *
	 * @access public
	 */

	public function license_activate_error_notice()
	{
		Notices::add_notice(
			[
				'id' => 'license-error',
				'type' => 'error',
				'dismissible' => true,
				'dismissible-time' => 43200,
				'title' => esc_html__('Sorry, Element Pack is not activated!', 'bdthemes-element-pack'),
				'message' => $this->licenseMessage,
			]
		);
	}

	/**
	 * License Active Notice
	 *
	 * @access public
	 */

	public function license_activate_notice()
	{
		Notices::add_notice(
			[
				'id' => 'license-issue',
				'type' => 'error',
				'dismissible' => true,
				'dismissible-time' => HOUR_IN_SECONDS * 72,
				'html_message' => $this->license_active_notice_message(),
			]
		);
	}

	public function license_active_notice_message()
	{
		$plugin_icon = BDTEP_ASSETS_URL . 'images/logo.svg';
		$plugin_title = __('Element Pack Pro', 'bdthemes-element-pack');
		$plugin_msg = __('Thank you for purchase Element Pack. Please activate your license to get feature updates, premium support. Don\'t have Element Pack license? Purchase and download your license copy from here.', 'bdthemes-element-pack');
		ob_start();
		?>
		<div class="bdt-license-notice-global element_pack_pro">
			<?php if (!empty($plugin_icon)): ?>
				<div class="bdt-license-notice-logo">
					<img src="<?php echo esc_url($plugin_icon); ?>" alt="icon">
				</div>
			<?php endif; ?>
			<div class="bdt-license-notice-content">
				<h3>
					<?php printf(wp_kses_post($plugin_title)); ?>
				</h3>
				<p>
					<?php printf(wp_kses_post($plugin_msg)); ?>
				</p>
				<div class="bdt-license-notice-button-wrap">
					<a href="<?php echo esc_url(self::get_url()); ?>#element_pack_license_settings"
						class="bdt-button bdt-button-allow">
						<?php esc_html_e('Activate License', 'bdthemes-element-pack'); ?>
					</a>
					<a href="https://elementpack.pro/" target="_blank" class="bdt-button bdt-button-skip">
						<?php esc_html_e('Get License', 'bdthemes-element-pack'); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 *Black Friday Notice
	 *
	 * @access public
	 */

	public function black_friday_notice()
	{
		Notices::add_notice(
			[
				'id' => 'black-friday',
				'type' => 'success',
				'dismissible' => true,
				'dismissible-time' => HOUR_IN_SECONDS * 72,
				'html_message' => $this->black_friday_offer_notice_message(),
			]
		);
	}

	public function black_friday_offer_notice_message()
	{
		$plugin_icon = BDTEP_ASSETS_URL . 'images/logo.svg';
		$plugin_title = __('Best Savings On Black Friday Deals - ⚡Up To 85% Off🔥 ', 'bdthemes-element-pack');
		ob_start();
		?>
		<div class="bdt-license-notice-global element_pack_pro">
			<div class="bdt-license-notice-content">
				<h3>
					<?php echo wp_kses_post($plugin_title); ?>
				</h3>
				<div class="bdt-license-notice-button-wrap">
					<a href="https://bdthemes.com/black-friday/" target="_blank" class="bdt-button bdt-button-allow">
						<?php esc_html_e('Get Deals Now', 'bdthemes-element-pack'); ?>
					</a>
				</div>
			</div>
			<a href="https://bdthemes.com/black-friday/" target="_blank" class="bdt-link-btn"></a>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 *
	 * Check mini-Cart of Elementor Activated or Not
	 * It's better to not use multiple mini-Cart on the same time.
	 * Transient Expire on 15 days
	 *
	 * @access public
	 */

	public function el_use_mini_cart()
	{
		Notices::add_notice(
			[
				'id' => 'ep-el-use-mini-cart',
				'type' => 'warning',
				'dismissible' => true,
				'dismissible-time' => MONTH_IN_SECONDS / 2,
				'title' => esc_html__('Oops, Possibilities to get errors', 'bdthemes-element-pack'),
				'message' => __('We can see you activated the <strong>Mini-Cart</strong> of Elementor Pro and also Element Pack Pro. We will recommend you to choose one of them, otherwise you will get conflict. Thank you.', 'bdthemes-element-pack'),
			]
		);
	}

	/**
	 * Get all the pages
	 *
	 * @return array page names with key value pairs
	 */
	public function get_pages()
	{
		$pages = get_pages();
		$pages_options = [];
		if ($pages) {
			foreach ($pages as $page) {
				$pages_options[$page->ID] = $page->post_title;
			}
		}

		return $pages_options;
	}

	/**
	 * Display Affiliate Content
	 *
	 * @access public
	 * @return void
	 */

	public function element_pack_affiliate_content()
	{
		?>
		<div class="ep-dashboard-panel"
			bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">
			<div class="ep-dashboard-affiliate">
				<div class="bdt-card bdt-card-body">
					<h1 class="ep-feature-title">
						<?php printf(esc_html__('Earn %s as an Affiliate', 'bdthemes-element-pack'), '<strong class="ep-highlight-text">50% Commission</strong>'); ?>
					</h1>
					<p>
						<?php esc_html_e('Join our affiliate program and earn a 50% commission on every sale you refer. It\'s a great way to earn passive income while promoting high-quality WordPress plugins.', 'bdthemes-element-pack'); ?>
					</p>
					<div class="ep-affiliate-features">
						<h3 class="ep-affiliate-sub-title"><?php esc_html_e('Benefits of joining our affiliate program:', 'bdthemes-element-pack'); ?></h3>
						<ul>
							<li><?php esc_html_e('50% commission on all sales', 'bdthemes-element-pack'); ?></li>
							<li><?php esc_html_e('Real-time tracking of referrals and sales', 'bdthemes-element-pack'); ?></li>
							<li><?php esc_html_e('Dedicated affiliate support', 'bdthemes-element-pack'); ?></li>
							<li><?php esc_html_e('Marketing materials provided', 'bdthemes-element-pack'); ?></li>
							<li><?php esc_html_e('Monthly payments via PayPal', 'bdthemes-element-pack'); ?></li>
						</ul>
					</div>
					<a href="https://bdthemes.com/affiliate/?utm_sourcce=ep_wp_dashboard&utm_medium=affiliate_payout&utm_campaign=affiliate_onboarding" target="_blank"
						class="bdt-button bdt-welcome-button bdt-margin-small-top"><?php esc_html_e('Join Our Affiliate Program', 'bdthemes-element-pack'); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Display Analytics and System Requirements
	 *
	 * @access public
	 * @return void
	 */

	public function element_pack_analytics_system_req_content()
	{
		?>
		<div class="ep-dashboard-panel"
			bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">
			<div class="ep-dashboard-analytics-system">

				<?php $this->element_pack_widgets_status(); ?>

				<div class="bdt-grid bdt-grid-medium bdt-margin-medium-top" bdt-grid
					bdt-height-match="target: > div > .bdt-card">
					<div class="bdt-width-1-1">
						<div class="bdt-card bdt-card-body ep-system-requirement">
							<h1 class="ep-feature-title bdt-margin-small-bottom">
								<?php esc_html_e('System Requirement', 'bdthemes-element-pack'); ?>
							</h1>
							<?php $this->element_pack_system_requirement(); ?>
						</div>
					</div>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Extra Options Start Here
	 */

	public function element_pack_extra_options()
	{
		?>
		<div class="ep-dashboard-panel"
			bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">
			<div class="ep-dashboard-extra-options">
				<div class="bdt-card bdt-card-body">
					<h1 class="ep-feature-title"><?php esc_html_e('Extra Options', 'bdthemes-element-pack'); ?></h1>

					<div class="ep-extra-options-tabs">
						<ul class="bdt-tab" bdt-tab="connect: #ep-extra-options-tab-content; animation: bdt-animation-fade">
							<li class="bdt-active"><a
									href="#"><?php esc_html_e('Custom CSS & JS', 'bdthemes-element-pack'); ?></a></li>
							<li><a href="#"><?php esc_html_e('White Label', 'bdthemes-element-pack'); ?></a></li>
						</ul>

						<div id="ep-extra-options-tab-content" class="bdt-switcher">
							<!-- Custom CSS & JS Tab -->
							<div>
								<?php $this->render_custom_css_js_section(); ?>
							</div>
							
							<!-- White Label Tab -->
							<div>
								<?php $this->render_white_label_section(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Extra Options Start Here
	 */

	/**
	 * Render Custom CSS & JS Section
	 * 
	 * @access public
	 * @return void
	 */
	public function render_custom_css_js_section() {
		?>
		<div class="ep-custom-code-section">
			<!-- Header Section -->
			<div class="ep-code-section-header">
				<h2 class="ep-section-title"><?php esc_html_e('Header Code Injection', 'bdthemes-element-pack'); ?></h2>
				<p class="ep-section-description"><?php esc_html_e('Code added here will be injected into the &lt;head&gt; section of your website.', 'bdthemes-element-pack'); ?></p>
			</div>
			<div class="ep-code-row bdt-grid bdt-grid-small" bdt-grid>
				<div class="bdt-width-1-2@m">
					<div class="ep-code-editor-wrapper">
						<h3 class="ep-code-editor-title"><?php esc_html_e('CSS', 'bdthemes-element-pack'); ?></h3>
						<p class="ep-code-editor-description"><?php esc_html_e('Enter raw CSS code without &lt;style&gt; tags.', 'bdthemes-element-pack'); ?></p>
						<div class="ep-codemirror-editor-container">
							<textarea id="ep-custom-css" name="ep_custom_css" class="ep-code-editor" data-mode="css" placeholder=".example {&#10;    background: red;&#10;    border-radius: 5px;&#10;    padding: 15px;&#10;}&#10;&#10;"><?php echo esc_textarea(get_option('ep_custom_css', '')); ?></textarea>
						</div>
					</div>
				</div>
				<div class="bdt-width-1-2@m">
					<div class="ep-code-editor-wrapper">
						<h3 class="ep-code-editor-title"><?php esc_html_e('JS', 'bdthemes-element-pack'); ?></h3>
						<p class="ep-code-editor-description"><?php esc_html_e('Enter raw JavaScript code without &lt;script&gt; tags.', 'bdthemes-element-pack'); ?></p>
						<div class="ep-codemirror-editor-container">
							<textarea id="ep-custom-js" name="ep_custom_js" class="ep-code-editor" data-mode="javascript" placeholder="alert('Hello, Element Pack!');"><?php echo esc_textarea(get_option('ep_custom_js', '')); ?></textarea>
						</div>
					</div>
				</div>
			</div>

			<!-- Footer Section -->
			<div class="ep-code-section-header bdt-margin-medium-top">
				<h2 class="ep-section-title"><?php esc_html_e('Footer Code Injection', 'bdthemes-element-pack'); ?></h2>
				<p class="ep-section-description"><?php esc_html_e('Code added here will be injected before the closing &lt;/body&gt; tag of your website.', 'bdthemes-element-pack'); ?></p>
			</div>
			<div class="ep-code-row bdt-grid bdt-grid-small bdt-margin-small-top" bdt-grid>
				<div class="bdt-width-1-2@m">
					<div class="ep-code-editor-wrapper">
						<h3 class="ep-code-editor-title"><?php esc_html_e('CSS', 'bdthemes-element-pack'); ?></h3>
						<p class="ep-code-editor-description"><?php esc_html_e('Enter raw CSS code without &lt;style&gt; tags.', 'bdthemes-element-pack'); ?></p>
						<div class="ep-codemirror-editor-container">
							<textarea id="ep-custom-css-2" name="ep_custom_css_2" class="ep-code-editor" data-mode="css" placeholder=".example {&#10;    background: green;&#10;}&#10;&#10;"><?php echo esc_textarea(get_option('ep_custom_css_2', '')); ?></textarea>
						</div>
					</div>
				</div>
				<div class="bdt-width-1-2@m">
					<div class="ep-code-editor-wrapper">
						<h3 class="ep-code-editor-title"><?php esc_html_e('JS', 'bdthemes-element-pack'); ?></h3>
						<p class="ep-code-editor-description"><?php esc_html_e('Enter raw JavaScript code without &lt;script&gt; tags.', 'bdthemes-element-pack'); ?></p>
						<div class="ep-codemirror-editor-container">
							<textarea id="ep-custom-js-2" name="ep_custom_js_2" class="ep-code-editor" data-mode="javascript" placeholder="console.log('Hello, Element Pack!');"><?php echo esc_textarea(get_option('ep_custom_js_2', '')); ?></textarea>
						</div>
					</div>
				</div>
			</div>

			<!-- Page Exclusion Section -->
			<div class="ep-code-section-header bdt-margin-medium-top">
				<h2 class="ep-section-title"><?php esc_html_e('Page & Post Exclusion Settings', 'bdthemes-element-pack'); ?></h2>
				<p class="ep-section-description"><?php esc_html_e('Select pages and posts where you don\'t want any custom code to be injected. This applies to all sections above.', 'bdthemes-element-pack'); ?></p>
			</div>
			<div class="ep-page-exclusion-wrapper">
				<label for="ep-excluded-pages" class="ep-exclusion-label">
					<?php esc_html_e('Exclude Pages & Posts:', 'bdthemes-element-pack'); ?>
				</label>
				<select id="ep-excluded-pages" name="ep_excluded_pages[]" multiple class="ep-page-select">
					<option value=""><?php esc_html_e('-- Select pages/posts to exclude --', 'bdthemes-element-pack'); ?></option>
					<?php
					$excluded_pages = get_option('ep_excluded_pages', array());
					if (!is_array($excluded_pages)) {
						$excluded_pages = array();
					}
					
					// Get all published pages
					$pages = get_pages(array(
						'sort_order' => 'ASC',
						'sort_column' => 'post_title',
						'post_status' => 'publish'
					));
					
					// Get recent posts (last 50)
					$posts = get_posts(array(
						'numberposts' => 50,
						'post_status' => 'publish',
						'post_type' => 'post',
						'orderby' => 'date',
						'order' => 'DESC'
					));
					
					// Display pages first
					if (!empty($pages)) {
						echo '<optgroup label="' . esc_attr__('Pages', 'bdthemes-element-pack') . '">';
						foreach ($pages as $page) {
							$selected = in_array($page->ID, $excluded_pages) ? 'selected' : '';
							echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
						}
						echo '</optgroup>';
					}
					
					// Then display posts
					if (!empty($posts)) {
						echo '<optgroup label="' . esc_attr__('Recent Posts', 'bdthemes-element-pack') . '">';
						foreach ($posts as $post) {
							$selected = in_array($post->ID, $excluded_pages) ? 'selected' : '';
							$post_date = date('M j, Y', strtotime($post->post_date));
							echo '<option value="' . esc_attr($post->ID) . '" ' . $selected . '>' . esc_html($post->post_title) . ' (' . $post_date . ')</option>';
						}
						echo '</optgroup>';
					}
					?>
				</select>
				<p class="ep-exclusion-help">
					<?php esc_html_e('Hold Ctrl (or Cmd on Mac) to select multiple items. Selected pages and posts will not load any custom CSS or JavaScript code. The list shows all pages and the 50 most recent posts.', 'bdthemes-element-pack'); ?>
				</p>
			</div>

			<!-- Save Button Section -->
			<div class="ep-code-save-section bdt-margin-medium-top bdt-text-center">
				<button type="button" id="ep-save-custom-code" class="bdt-button bdt-btn-blue bdt-margin-small-right">
					<span class="dashicons dashicons-yes"></span>
					<?php esc_html_e('Save Custom Code', 'bdthemes-element-pack'); ?>
				</button>
				<button type="button" id="ep-reset-custom-code" class="bdt-button bdt-btn-grey">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e('Reset Code', 'bdthemes-element-pack'); ?>
				</button>
			</div>

			<!-- Success/Error Messages -->
			<div id="ep-custom-code-message" class="ep-code-message bdt-margin-small-top" style="display: none;">
				<div class="bdt-alert bdt-alert-success" bdt-alert>
					<a href class="bdt-alert-close" bdt-close></a>
					<p><?php esc_html_e('Custom code saved successfully!', 'bdthemes-element-pack'); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render White Label Section
	 * 
	 * @access public
	 * @return void
	 */
	public function render_white_label_section() {
		?>
		<div class="ep-white-label-section">
			<h1 class="ep-feature-title"><?php esc_html_e('White Label Settings', 'bdthemes-element-pack'); ?></h1>
			<p><?php esc_html_e('Enable white label mode to hide Element Pack branding from the admin interface and widgets.', 'bdthemes-element-pack'); ?></p>

			<?php 
			$is_license_active = $this->is_activated;
			$is_agency_license = self::is_agency_license();
			
			// Show appropriate notices based on license status
			if (!$is_license_active): ?>
				<div class="bdt-alert bdt-alert-danger bdt-margin-medium-top" bdt-alert>
					<p><strong><?php esc_html_e('License Not Activated', 'bdthemes-element-pack'); ?></strong></p>
					<p><?php esc_html_e('You need to activate your Element Pack license to access White Label functionality. Please activate your license first.', 'bdthemes-element-pack'); ?></p>
					<div class="bdt-margin-small-top">
						<a href="<?php echo esc_url(admin_url('admin.php?page=element_pack_options#element_pack_license_settings')); ?>" class="bdt-button bdt-btn-blue bdt-margin-small-right">
							<?php esc_html_e('Activate License', 'bdthemes-element-pack'); ?>
						</a>
						<a href="https://elementpack.pro/pricing/" target="_blank" class="bdt-button bdt-btn-blue">
							<?php esc_html_e('Get License', 'bdthemes-element-pack'); ?>
						</a>
					</div>
				</div>
			<?php elseif ($is_license_active && !$is_agency_license): ?>
				<div class="bdt-alert bdt-alert-warning bdt-margin-medium-top" bdt-alert>
					<p><strong><?php esc_html_e('Agency License Required', 'bdthemes-element-pack'); ?></strong></p>
					<p><?php esc_html_e('White Label functionality is only available for Agency license holders. Please upgrade your license to access these features.', 'bdthemes-element-pack'); ?></p>
					<a href="https://elementpack.pro/pricing/" target="_blank" class="bdt-button bdt-btn-blue bdt-margin-small-top">
						<?php esc_html_e('Upgrade to Agency License', 'bdthemes-element-pack'); ?>
					</a>
				</div>
			<?php endif; ?>

			<div class="ep-white-label-options <?php echo (!$is_license_active || !$is_agency_license) ? 'ep-white-label-locked' : ''; ?>">
				<div class="ep-option-item ">
					<div class="ep-option-item-inner bdt-card">
						<div class="bdt-flex bdt-flex-between bdt-flex-middle">
							<div>
								<h3 class="ep-option-title"><?php esc_html_e('Enable White Label Mode', 'bdthemes-element-pack'); ?></h3>
								<p class="ep-option-description">
									<?php if ($is_license_active && $is_agency_license): ?>
										<?php esc_html_e('When enabled, Element Pack branding will be hidden from the admin interface and widgets.', 'bdthemes-element-pack'); ?>
									<?php elseif (!$is_license_active): ?>
										<?php esc_html_e('This feature requires an active Element Pack license. Please activate your license first.', 'bdthemes-element-pack'); ?>
									<?php else: ?>
										<?php esc_html_e('This feature requires an Agency license. Upgrade your license to access white label functionality.', 'bdthemes-element-pack'); ?>
									<?php endif; ?>
								</p>
							</div>
							<div class="ep-option-switch">
								<?php
								$white_label_enabled = ($is_license_active && $is_agency_license) ? get_option('ep_white_label_enabled', false) : false;
								// Convert to boolean to ensure proper comparison
								$white_label_enabled = (bool) $white_label_enabled;
								?>
								<label class="switch">
									<input type="checkbox" 
										   id="ep-white-label-enabled" 
										   name="ep_white_label_enabled" 
										   <?php checked($white_label_enabled, true); ?>
										   <?php disabled(!$is_license_active || !$is_agency_license); ?>>
									<span class="slider"></span>
								</label>
							</div>
						</div>
					</div>
				</div>

				<!-- White Label Title Field (conditional) -->
				<div class="ep-option-item ep-white-label-fields" style="<?php echo ($white_label_enabled && $is_license_active && $is_agency_license) ? '' : 'display: none;'; ?>">
					<div class="ep-option-item-inner bdt-card">
						<div class="ep-white-label-title-section">
							<h3 class="ep-option-title"><?php esc_html_e('White Label Title', 'bdthemes-element-pack'); ?></h3>
							<p class="ep-option-description"><?php esc_html_e('Enter a custom title to replace "Element Pack" branding throughout the plugin.', 'bdthemes-element-pack'); ?></p>
							<div class="ep-white-label-input-wrapper bdt-margin-small-top">
								<input type="text" 
									   id="ep-white-label-title" 
									   name="ep_white_label_title" 
									   class="ep-white-label-input" 
									   placeholder="<?php esc_attr_e('Enter your custom title...', 'bdthemes-element-pack'); ?>"
									   value="<?php echo esc_attr(get_option('ep_white_label_title', '')); ?>"
									   <?php disabled(!$is_license_active || !$is_agency_license); ?>>
							</div>
							<p class="ep-input-help">
								<?php esc_html_e('Leave empty to use default "Element Pack" title. This will replace the plugin name in admin menus, widget names, and other branding areas.', 'bdthemes-element-pack'); ?>
							</p>
						</div>
						
						<!-- White Label Title Icon Field -->
						<div class="ep-white-label-icon-section bdt-margin-medium-top">
							<h3 class="ep-option-title"><?php esc_html_e('White Label Title Icon', 'bdthemes-element-pack'); ?></h3>
							<p class="ep-option-description"><?php esc_html_e('Upload a custom icon to replace the Element Pack menu icon. Supports JPG, PNG, and SVG formats.', 'bdthemes-element-pack'); ?></p>
							
							<div class="ep-icon-upload-wrapper bdt-margin-small-top">
								<?php 
								$icon_url = get_option('ep_white_label_icon', '');
								$icon_id = get_option('ep_white_label_icon_id', '');
								?>
								<div class="ep-icon-preview-container" style="<?php echo $icon_url ? '' : 'display: none;'; ?>">
									<div class="ep-icon-preview">
										<img id="ep-icon-preview-img" src="<?php echo esc_url($icon_url); ?>" alt="Icon Preview" style="max-width: 64px; max-height: 64px; border: 1px solid #ddd; border-radius: 4px; padding: 8px; background: #fff;">
									</div>
									<button type="button" id="ep-remove-icon" class="bdt-button bdt-btn-grey bdt-margin-small-left" style="padding: 8px 12px; font-size: 12px;">
										<span class="dashicons dashicons-trash"></span>
										<?php esc_html_e('Remove', 'bdthemes-element-pack'); ?>
									</button>
								</div>
								
								<div class="ep-icon-upload-container">
									<button type="button" id="ep-upload-icon" class="bdt-button bdt-btn-blue bdt-margin-small-top" <?php disabled(!$is_license_active || !$is_agency_license); ?>>
										<span class="dashicons dashicons-cloud-upload"></span>
										<?php esc_html_e('Upload Icon', 'bdthemes-element-pack'); ?>
									</button>
									<input type="hidden" id="ep-white-label-icon" name="ep_white_label_icon" value="<?php echo esc_attr($icon_url); ?>">
									<input type="hidden" id="ep-white-label-icon-id" name="ep_white-label_icon_id" value="<?php echo esc_attr($icon_id); ?>">
								</div>
							</div>
							
							<p class="ep-input-help">
								<?php esc_html_e('Recommended size: 20x20 pixels. The icon will be automatically resized to fit the WordPress admin menu. Supported formats: JPG, PNG, SVG.', 'bdthemes-element-pack'); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- License Hide Option (conditional) -->
				<div class="ep-option-item ep-white-label-fields" style="<?php echo ($white_label_enabled && $is_license_active && $is_agency_license) ? '' : 'display: none;'; ?>">
					<div class="ep-option-item-inner bdt-card">
						<div class="bdt-flex bdt-flex-between bdt-flex-middle">
							<div>
								<h3 class="ep-option-title"><?php esc_html_e('Hide License Menu', 'bdthemes-element-pack'); ?></h3>
								<p class="ep-option-description"><?php esc_html_e('Hide the license menu from the admin sidebar when white label mode is enabled.', 'bdthemes-element-pack'); ?></p>
							</div>
							<div class="ep-option-switch">
								<?php
								$hide_license = get_option('ep_white_label_hide_license', false);
								// Convert to boolean to ensure proper comparison
								$hide_license = (bool) $hide_license;
								?>
								<label class="switch">
									<input type="checkbox" 
										   id="ep-white-label-hide-license" 
										   name="ep_white_label_hide_license" 
										   <?php checked($hide_license, true); ?>
										   <?php disabled(!$is_license_active || !$is_agency_license); ?>>
									<span class="slider"></span>
								</label>
							</div>
						</div>
					</div>
				</div>

				<!-- BDTEP_HIDE Option (conditional) -->
				<div class="ep-option-item ep-white-label-fields" style="<?php echo ($white_label_enabled && $is_license_active && $is_agency_license) ? '' : 'display: none;'; ?>">
					<div class="ep-option-item-inner bdt-card">
						<div class="bdt-flex bdt-flex-between bdt-flex-middle">
							<div>
								<h3 class="ep-option-title"><?php esc_html_e('Enable BDTEP_HIDE Constant', 'bdthemes-element-pack'); ?></h3>
								<p class="ep-option-description"><?php esc_html_e('Define the BDTEP_HIDE constant to hide additional Element Pack branding and features throughout the plugin.', 'bdthemes-element-pack'); ?></p>
								<?php 
								$bdtep_hide = get_option('ep_white_label_bdtep_hide', false);
								if ($bdtep_hide): ?>
									<div class="bdt-alert bdt-alert-warning bdt-margin-small-top">
										<p><strong>⚠️ BDTEP_HIDE Currently Active</strong></p>
										<p>Advanced white label mode is currently enabled. Element Pack menus are hidden from the admin interface.</p>
									</div>
								<?php endif; ?>
							</div>
							<div class="ep-option-switch">
								<?php
								// Convert to boolean to ensure proper comparison
								$bdtep_hide = (bool) $bdtep_hide;
								?>
								<label class="switch">
									<input type="checkbox" 
										   id="ep-white-label-bdtep-hide" 
										   name="ep_white_label_bdtep-hide" 
										   <?php checked($bdtep_hide, true); ?>
										   <?php disabled(!$is_license_active || !$is_agency_license); ?>>
									<span class="slider"></span>
								</label>
							</div>
						</div>
					</div>
				</div>
				
				<?php if (!$bdtep_hide && $is_license_active && $is_agency_license): ?>
				<div class="bdt-margin-small-top">
					<div class="bdt-alert bdt-alert-primary">
						<h4>📧 Email Access System</h4>
						<p>When you enable BDTEP_HIDE, an email will be automatically sent to:</p>
						<ul style="margin: 10px 0;">
							<li><strong>License Email:</strong> <?php echo esc_html(self::get_license_email()); ?></li>
							<?php if (get_bloginfo('admin_email') !== self::get_license_email()): ?>
							<li><strong>Admin Email:</strong> <?php echo esc_html(get_bloginfo('admin_email')); ?></li>
							<?php endif; ?>
						</ul>
						<p>This email will contain a special access link that allows you to return to these settings even when BDTEP_HIDE is active.</p>
					</div>
				</div>
				<?php endif; ?>

				<!-- Save Button Section -->
				<div class="ep-white-label-save-section bdt-margin-small-top bdt-text-center">
					<button type="button" 
							id="ep-save-white-label" 
							class="bdt-button bdt-btn-blue"
							<?php disabled(!$is_license_active || !$is_agency_license); ?>>
						<span class="dashicons dashicons-yes"></span>
						<?php if ($is_license_active && $is_agency_license): ?>
							<?php esc_html_e('Save White Label Settings', 'bdthemes-element-pack'); ?>
						<?php elseif (!$is_license_active): ?>
							<?php esc_html_e('License Not Activated', 'bdthemes-element-pack'); ?>
						<?php else: ?>
							<?php esc_html_e('Agency License Required', 'bdthemes-element-pack'); ?>
						<?php endif; ?>
					</button>
				</div>

				<!-- Success/Error Messages -->
				<div id="ep-white-label-message" class="ep-white-label-message bdt-margin-small-top" style="display: none;">
					<div class="bdt-alert bdt-alert-success" bdt-alert>
						<a href class="bdt-alert-close" bdt-close></a>
						<p><?php esc_html_e('White label settings saved successfully!', 'bdthemes-element-pack'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function license_wl_process()
	{
		$license_title = $this->license_title;
		$title_arr = explode(' ', strtolower($license_title));
		$titles = array_map('md5', $title_arr);

		$agency = in_array('1d1d5778763061ebb2bdc5db696077a6', $titles);
		$extended = in_array('d3e78e3d3b68cb0fbd9f66dcaef93cea', $titles);

		$status = false;

		if ($agency || $extended) {
			$status = true;
		}

		update_option('element_pack_license_title_status', $status);
	}

	public static function license_wl_status()
	{
		$status = get_option('element_pack_license_title_status');

		if ($status) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current license is agency type
	 * 
	 * @access public static
	 * @return bool
	 */
	public static function is_agency_license() {
		$license_info = Element_Pack_Base::get_register_info();
		$license_title = isset( $license_info->license_title ) && ! empty( $license_info->license_title ) && $license_info->license_title ? $license_info->license_title : 'None';
		
		$title_arr = explode( ' ', strtolower( $license_title ) );
		$titles = array_map( 'md5', $title_arr );
		
		// Check for agency license (MD5 of 'agency')
		$agency = in_array( '1d1d5778763061ebb2bdc5db696077a6', $titles );
		
		return $agency;
	}

	/**
	 * Check plugin status (installed, active, or not installed)
	 * 
	 * @param string $plugin_path Plugin file path
	 * @return string 'active', 'installed', or 'not_installed'
	 */
	private function get_plugin_status($plugin_path) {
		// Check if plugin is active
		if (is_plugin_active($plugin_path)) {
			return 'active';
		}
		
		// Check if plugin is installed but not active
		$installed_plugins = get_plugins();
		if (isset($installed_plugins[$plugin_path])) {
			return 'installed';
		}
		
		// Plugin is not installed
		return 'not_installed';
	}

	/**
	 * Get plugin action button HTML based on plugin status
	 * 
	 * @param string $plugin_path Plugin file path
	 * @param string $install_url Plugin installation URL
	 * @param string $plugin_slug Plugin slug for activation
	 * @return string Button HTML
	 */
	private function get_plugin_action_button($plugin_path, $install_url, $plugin_slug = '') {
		$status = $this->get_plugin_status($plugin_path);
		
		switch ($status) {
			case 'active':
				return '';
				
			case 'installed':
				$activate_url = wp_nonce_url(
					add_query_arg([
						'action' => 'activate',
						'plugin' => $plugin_path
					], admin_url('plugins.php')),
					'activate-plugin_' . $plugin_path
				);
				return '<a class="bdt-button bdt-welcome-button" href="' . esc_url($activate_url) . '">' . 
				       __('Activate', 'bdthemes-element-pack') . '</a>';
				
			case 'not_installed':
			default:
				$plugin_slug = $this->extract_plugin_slug_from_path($plugin_path);
				$nonce = wp_create_nonce('ep_install_plugin_nonce');
				return '<a class="bdt-button bdt-welcome-button ep-install-plugin" 
				          data-plugin-slug="' . esc_attr($plugin_slug) . '" 
				          data-nonce="' . esc_attr($nonce) . '" 
				          href="#">' . 
				       __('Install', 'bdthemes-element-pack') . '</a>';
		}
	}

	/**
	 * Handle AJAX plugin installation
	 * 
	 * @access public
	 * @return void
	 */
	public function install_plugin_ajax() {
		// Check nonce
		if (!wp_verify_nonce($_POST['nonce'], 'ep_install_plugin_nonce')) {
			wp_send_json_error(['message' => __('Security check failed', 'bdthemes-element-pack')]);
		}

		// Check user capability
		if (!current_user_can('install_plugins')) {
			wp_send_json_error(['message' => __('You do not have permission to install plugins', 'bdthemes-element-pack')]);
		}

		$plugin_slug = sanitize_text_field($_POST['plugin_slug']);

		if (empty($plugin_slug)) {
			wp_send_json_error(['message' => __('Plugin slug is required', 'bdthemes-element-pack')]);
		}

		// Include necessary WordPress files
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';

		// Get plugin information
		$api = plugins_api('plugin_information', [
			'slug' => $plugin_slug,
			'fields' => [
				'sections' => false,
			],
		]);

		if (is_wp_error($api)) {
			wp_send_json_error(['message' => __('Plugin not found: ', 'bdthemes-element-pack') . $api->get_error_message()]);
		}

		// Install the plugin
		$skin = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader($skin);
		$result = $upgrader->install($api->download_link);

		if (is_wp_error($result)) {
			wp_send_json_error(['message' => __('Installation failed: ', 'bdthemes-element-pack') . $result->get_error_message()]);
		} elseif ($skin->get_errors()->has_errors()) {
			wp_send_json_error(['message' => __('Installation failed: ', 'bdthemes-element-pack') . $skin->get_error_messages()]);
		} elseif (is_null($result)) {
			wp_send_json_error(['message' => __('Installation failed: Unable to connect to filesystem', 'bdthemes-element-pack')]);
		}

		// Get installation status
		$install_status = install_plugin_install_status($api);
		
		wp_send_json_success([
			'message' => __('Plugin installed successfully!', 'bdthemes-element-pack'),
			'plugin_file' => $install_status['file'],
			'plugin_name' => $api->name
		]);
	}

	/**
	 * Extract plugin slug from plugin path
	 * 
	 * @param string $plugin_path Plugin file path
	 * @return string Plugin slug
	 */
	private function extract_plugin_slug_from_path($plugin_path) {
		$parts = explode('/', $plugin_path);
		return isset($parts[0]) ? $parts[0] : '';
	}

	/**
	 * Display message for subsites when license is activated on main site
	 *
	 * @access public
	 * @param array $subsite_status
	 * @return void
	 */
	public function license_subsite_message($subsite_status)
	{
		?>
		<div class="ep-license-subsite-container">
			<div class="ep-license-subsite-header">
				<div class="ep-status-icon">
					<?php if ($subsite_status['is_main_site_licensed']): ?>
						<svg width="64" height="64" viewBox="0 0 24 24" fill="#10b981">
							<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
						</svg>
					<?php else: ?>
						<svg width="64" height="64" viewBox="0 0 24 24" fill="#f59e0b">
							<path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
						</svg>
					<?php endif; ?>
				</div>
				<h2 class="ep-feature-title"><?php esc_html_e('Multisite License Information', 'bdthemes-element-pack'); ?></h2>
			</div>

			<div class="ep-license-subsite-content">
				<?php if ($subsite_status['is_main_site_licensed']): ?>
					<div class="ep-license-status-success">
						<h3><?php esc_html_e('✓ License Active on Network', 'bdthemes-element-pack'); ?></h3>
						<p><?php 
							printf(
								esc_html__('Element Pack Pro is activated on the main site (%s). All pro features are available on this subsite.', 'bdthemes-element-pack'),
								'<strong>' . esc_html($subsite_status['main_site_url']) . '</strong>'
							); 
						?></p>
						
						<div class="ep-license-details">
							<h4><?php esc_html_e('License Details:', 'bdthemes-element-pack'); ?></h4>
							<?php 
							$license_info = Element_Pack_Base::get_register_info();
							if ($license_info): ?>
								<ul>
									<li><strong><?php esc_html_e('License Type:', 'bdthemes-element-pack'); ?></strong> <?php echo esc_html($license_info->license_title); ?></li>
									<li><strong><?php esc_html_e('License Status:', 'bdthemes-element-pack'); ?></strong> <span class="license-valid"><?php esc_html_e('Valid', 'bdthemes-element-pack'); ?></span></li>
									<?php if (!empty($license_info->expire_date) && strtolower($license_info->expire_date) !== 'no expiry'): ?>
										<li><strong><?php esc_html_e('Expires:', 'bdthemes-element-pack'); ?></strong> <?php echo esc_html(date('F j, Y', strtotime($license_info->expire_date))); ?></li>
									<?php else: ?>
										<li><strong><?php esc_html_e('License:', 'bdthemes-element-pack'); ?></strong> <?php esc_html_e('No Expiry', 'bdthemes-element-pack'); ?></li>
									<?php endif; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
				<?php else: ?>
					<div class="ep-license-status-warning">
						<h3><?php esc_html_e('⚠ No License Active on Network', 'bdthemes-element-pack'); ?></h3>
						<p><?php esc_html_e('Element Pack Pro license is not activated on the main site. Pro features are not available.', 'bdthemes-element-pack'); ?></p>
						
						<div class="ep-license-instructions">
							<h4><?php esc_html_e('To activate Element Pack Pro:', 'bdthemes-element-pack'); ?></h4>
							<ol>
								<li><?php 
									printf(
										esc_html__('Go to the main site (%s)', 'bdthemes-element-pack'),
										'<strong>' . esc_html($subsite_status['main_site_url']) . '</strong>'
									); 
								?></li>
								<li><?php esc_html_e('Navigate to the Element Pack license page', 'bdthemes-element-pack'); ?></li>
								<li><?php esc_html_e('Enter your license key and activate', 'bdthemes-element-pack'); ?></li>
								<li><?php esc_html_e('Pro features will then be available on all subsites', 'bdthemes-element-pack'); ?></li>
							</ol>
						</div>
					</div>
				<?php endif; ?>

				<div class="ep-network-info">
					<h4><?php esc_html_e('Network Information:', 'bdthemes-element-pack'); ?></h4>
					<ul>
						<li><strong><?php esc_html_e('Main Site:', 'bdthemes-element-pack'); ?></strong> 
							<a href="<?php echo esc_url($subsite_status['main_site_url']); ?>" target="_blank">
								<?php echo esc_html($subsite_status['main_site_url']); ?>
							</a>
						</li>
						<li><strong><?php esc_html_e('Current Site:', 'bdthemes-element-pack'); ?></strong> <?php echo esc_html(site_url()); ?></li>
						<li><strong><?php esc_html_e('License Management:', 'bdthemes-element-pack'); ?></strong> <?php esc_html_e('Available only on main site', 'bdthemes-element-pack'); ?></li>
					</ul>
				</div>

				<?php if (current_user_can('manage_network')): ?>
					<div class="ep-admin-actions">
						<a href="<?php echo esc_url($subsite_status['main_site_url'] . 'wp-admin/admin.php?page=element_pack_options#element_pack_license_settings'); ?>" 
						   class="button button-primary" target="_blank">
							<?php esc_html_e('Manage License on Main Site', 'bdthemes-element-pack'); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

new ElementPack_Admin_Settings();
