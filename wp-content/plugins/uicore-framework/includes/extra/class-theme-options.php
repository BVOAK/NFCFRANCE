<?php

namespace UiCore;

defined('ABSPATH') || exit();

/**
 * Theme Options Manager
 *
 * @author Andrei Voica <andrei@uicore.co>
 * @since 3.0.0
 */
class ThemeOptions {

	private static $instance;

	private static $defaults_extra;
	private static $defaults_front;
	private static $defaults_admin;
	private static $defaults_admin_customizer;

	private static $options_extra;
	private static $options_front;
	private static $options_admin;
	private static $options_admin_customizer;

	private static $db_options_no_defaults_extra;
	private static $db_options_no_defaults_front;
	private static $db_options_no_defaults_admin;
	private static $db_options_no_defaults_admin_customizer;

	/**
	 * Init
	 *
	 * @return mixexd
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function get_instance() {
		/**
		* This function calculates the sum of two numbers.
		* Key is the name of the settings and value is the list with options
		*/
		self::$defaults_extra = \apply_filters('uicore_extra_settings', []);

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public function __construct() {
		// Set default Theme options used in admin
		add_action( 'after_setup_theme', array( $this, 'refresh_front_options' ) );

	}

	/**
	 * Set default Theme options used in admin
	 *
	 * @return array
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function admin_defaults() {
		self::$defaults_admin = [
			'advanced_mode'					=> 'true',
			'backgrounds' 					=> [
				'solid' => [ ],
				'gradient' => [ ],
			],
			'scheme'						=>'dark',
			'presets'						=> [],

			'animations_ham_color' 			=> 'Primary',
			'animations_menu' 				=> 'none',
			'animations_menu_duration' 		=> 'normal',
			'animations_menu_delay' 		=> '500',
			'animations_menu_delay_child' 	=> '200',
			'animations_submenu' => 'fade up',
			'animations_submenu_duration' => 'normal',
			'animations_topbanner' => 'none',
			'animations_topbanner_duration' => 'normal',
			'animations_topbanner_delay' => '500',
			'animations_topbanner_delay_child' => '200',
			'animations_title' => 'none',
			'animations_title_duration' => 'normal',
			'animations_title_delay' => '500',
			'animations_title_delay_child' => '200',
			'animations_footer' => 'none',
			'animations_footer_duration' => 'normal',
			'animations_footer_delay' => '0',
			'animations_footer_delay_child' => '200',
			'animations_blog' => 'none',
			'animations_blog_duration' => 'normal',
			'animations_blog_delay_child' => '200',
			'animations_portfolio' => 'none',
			'animations_portfolio_duration' => 'normal',
			'animations_portfolio_delay_child' => '200',
			'animations_shop' => 'none',
			'animations_shop_duration' => 'normal',
			'animations_shop_delay_child' => '200',

			'gen_boxed_w' => '1300',
			'gen_boxed_bg' => [
				'blur' => 'false',
				'color' => '#f6f6f6',
			],
			'gen_full_w' => '1170',
			'gen_bg' => [
				'type' => 'solid',
				'solid' => '#FFFFFF',
				'gradient' => [
				'angle' => '180',
				'color1' => '#F6F9FA',
				'color2' => '#FFFFFF',
				],
				'image' => [
				'url' => '',
				'attachment' => 'fixed',
				'position' => [
					'd' => 'center center',
					't' => 'center center',
					'm' => 'center center',
				],
				'repeat' => 'no-repeat',
				'size' => [
					'd' => 'cover',
					't' => 'cover',
					'm' => 'contain',
				],
				],
			],
			'gen_noise' => 'none',

			'gen_line' => 'false',
			'gen_line_width' => 'contained',
			'gen_line_offset' => '0',
			'gen_line_col' => [
				'd' => '6',
				't' => '4',
				'm' => '3'
			],
			'gen_line_color' => '#eeeeee',
			'gen_line_w' => '1',
			'gen_line_z' => '0',
			'gen_line_animation' => 'false',
			'gen_line_animation_color' => 'Primary',

			'gen_rtlm' => 'false',
			'gen_siteborder' => 'false',
			'gen_sitebordercolor' => 'Primary',
			'gen_siteborder_w' => '30',

			'gen_btop_radius' => '4',
			'gen_btop_bg' => [
				'blur' => 'false',
				'type' => 'White',
				'solid' => '#ffffff',
				'gradient' => [
				'angle' => '90',
				'color1' => '#19187C',
				'color2' => '#532df5',
				],
				'image' => [
				'url' => '',
				'attachment' => 'fixed',
				'position' => [
					'd' => 'bottom center',
					't' => 'center center',
					'm' => 'center center',
				],
				'repeat' => 'no-repeat',
				'size' => [
					'd' => 'cover',
					't' => 'cover',
					'm' => 'contain',
				],
				],
			],
			'gen_btop_color' => [
				'm' => 'Headline',
				'h' => 'Headline',
			],

			// 'gen_cursor' => 'true', moved to frontend
			'gen_cursor_default' => 'true',
			'gen_cursor_style' => 'border',
			'gen_cursor_hover' => 'scale',
			'gen_cursor_color' => 'Accent',

			'pFont' => [
				'f' => 'Inter',
				'st' => '600',
			],
			'sFont' => [
				'f' => 'Inter',
				'st' => '700',
			],
			'tFont' => [
				'f' => 'Inter',
				'st' => 'regular',
			],
			'aFont' => [
				'f' => 'Inter',
				'st' => '500',
			],
			'button_border_border' => 'none',
			'button_border_width' => '0',
			'button_border_radius' => '6',
			'button_padding' => [
				'd' => [
				'top' => '17',
				'right' => '40',
				'bottom' => '17',
				'left' => '40',
				],
				't' => [
				'top' => '17',
				'right' => '40',
				'bottom' => '17',
				'left' => '40',
				],
				'm' => [
				'top' => '13',
				'right' => '35',
				'bottom' => '13',
				'left' => '35',
				],
			],
			'button_background_color' => [
				'm' => 'Primary',
				'h' => 'Secondary',
			],
			'button_border_color' => [
				'm' => 'Primary',
				'h' => 'Secondary',
			],
			'button_typography_typography' => [
				'f' => 'Accent',
				's' => [
				'd' => '15',
				't' => '15',
				'm' => '14',
				],
				'h' => '1',
				'ls' => '0',
				't' => 'None',
				'st' => '500',
				'c' => '#FFFFFF',
				'ch' => '#FFFFFF',
			],
			'button_shadow' => [],
			'button_interaction' => 'none',
			'h1' => [
				'f' => 'Primary',
				's' => [
					'd' => ['value' => '72', 'unit' => 'px'],
					't' => ['value' => '60', 'unit' => 'px'],
					'm' => ['value' => '40', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.2', 'unit' => 'em'],
					't' => ['value' => '1.2', 'unit' => 'em'],
					'm' => ['value' => '1.2', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '-0.027', 'unit' => 'em'],
					't' => ['value' => '-0.027', 'unit' => 'em'],
					'm' => ['value' => '-0.027', 'unit' => 'em'],
				],
				't' => 'None',
				'st' => '600',
				'c' => 'Headline',
			],
			'h2' => [
				'f' => 'Secondary',
				's' => [
					'd' => ['value' => '48', 'unit' => 'px'],
					't' => ['value' => '34', 'unit' => 'px'],
					'm' => ['value' => '26', 'unit' => 'px'],
				],
				'h' => [
					'd'=> ['value' => '1.175', 'unit' => 'em'],
					't' => ['value' => '1.175', 'unit' => 'em'],
					'm' => ['value' => '1.175', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '-0.027', 'unit' => 'em'],
					't' => ['value' => '-0.027', 'unit' => 'em'],
					'm' => ['value' => '-0.027', 'unit' => 'em'],
				],
				't' => 'None',
				'st' => '700',
				'c' => 'Headline',
			],
			'h3' => [
				'f' => 'Primary',
				's' => [
					'd' => ['value' => '24', 'unit' => 'px'],
					't' => ['value' => '21', 'unit' => 'px'],
					'm' => ['value' => '20', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.2', 'unit' => 'em'],
					't' => ['value' => '1.2', 'unit' => 'em'],
					'm' => ['value' => '1.2', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '-0.027', 'unit' => 'em'],
					't' => ['value' => '-0.027', 'unit' => 'em'],
					'm' => ['value' => '-0.027', 'unit' => 'em'],
				],
				't' => 'None',
				'st' => '600',
				'c' => 'Headline',
			],
			'h4' => [
				'f' => 'Primary',
				's' => [
					'd' => ['value' => '21', 'unit' => 'px'],
					't' => ['value' => '18', 'unit' => 'px'],
					'm' => ['value' => '16', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.42', 'unit' => 'em'],
					't' => ['value' => '1.42', 'unit' => 'em'],
					'm' => ['value' => '1.42', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '-0.027', 'unit' => 'em'],
					't' => ['value' => '-0.027', 'unit' => 'em'],
					'm' => ['value' => '-0.027', 'unit' => 'em'],
				],
				't' => 'None',
				'st' => '600',
				'c' => 'Headline',
			],
			'h5' => [
				'f' => 'Primary',
				's' => [
					'd' => ['value' => '16', 'unit' => 'px'],
					't' => ['value' => '15', 'unit' => 'px'],
					'm' => ['value' => '14', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.187', 'unit' => 'em'],
					't' => ['value' => '1.187', 'unit' => 'em'],
					'm' => ['value' => '1.187', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '-0.015', 'unit' => 'em'],
					't' => ['value' => '-0.015', 'unit' => 'em'],
					'm' => ['value' => '-0.015', 'unit' => 'em'],
				],
				't' => 'None',
				'st' => '600',
				'c' => 'Accent',
			],
			'h6' => [
				'f' => 'Primary',
				's' => [
					'd' => ['value' => '14', 'unit' => 'px'],
					't' => ['value' => '13', 'unit' => 'px'],
					'm' => ['value' => '12', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.2', 'unit' => 'em'],
					't' => ['value' => '1.2', 'unit' => 'em'],
					'm' => ['value' => '1.2', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '-0.027', 'unit' => 'em'],
					't' => ['value' => '-0.027', 'unit' => 'em'],
					'm' => ['value' => '-0.027', 'unit' => 'em'],
				],
				't' => 'Uppercase',
				'st' => '600',
				'c' => 'Headline',
			],
			'p' => [
				'f' => 'Text',
				's' => [
					'd' => ['value' => '16', 'unit' => 'px'],
					't' => ['value' => '15', 'unit' => 'px'],
					'm' => ['value' => '14', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.875', 'unit' => 'em'],
					't' => ['value' => '1.875', 'unit' => 'em'],
					'm' => ['value' => '1.875', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '0', 'unit' => 'em'],
					't' => ['value' => '0', 'unit' => 'em'],
					'm' => ['value' => '0', 'unit' => 'em'],
				],
				't' => 'None',
				'st' => 'regular',
				'c' => 'Body',
			],
			'link_color' => [
				'm' => 'Primary',
				'h' => 'Secondary',
			],
			'header_ham_color' => [
				'm' => 'Body',
				'h' => 'Primary',
			],
			'header_bg' => [
				'blur' => 'false',
				'type' => 'solid',
				'solid' => '#ffffff',
				'gradient' => [
				'angle' => '90',
				'color1' => '#19187C',
				'color2' => '#532df5',
				],
				'image' => [
				'url' => '',
				'attachment' => 'fixed',
				'position' => [
					'd' => 'center center',
					't' => 'center center',
					'm' => 'center center',
				],
				'repeat' => 'no-repeat',
				'size' => [
					'd' => 'cover',
					't' => 'cover',
					'm' => 'contain',
				],
				],
			],
			'header_bg_blur' => 'true',
			'header_border' => 'false',
			'header_borderc' => '#E6E9EC',
			'header_shadow' => 'true',
			'header_padding' => '20',
			'header_2_padding' => '20',
			'header_side_width' => '300',
			'header_pill_radius' => '18',
            'header_pill_top_spacing' => '0',
			'header_padding_before_scroll' => '40',
			'header_logo_h' => '30',
			'header_content_align' => 'left',
			'header_transparent_color' => [
				'm' => '#FFFFFF',
				'h' => 'Primary',
			],
			'header_transparent_border' => 'true',
			'header_transparent_borderc' => 'rgba(255, 255, 255, 0.2)',
			'header_top_bg' => [
				'type' => 'Primary',
				'solid' => 'Primary',
				'gradient' => [
				'angle' => '180',
				'color1' => '#070707',
				'color2' => '#202020',
				],
				'image' => [
				'url' => '',
				'attachment' => 'scroll',
				'position' => [
					'd' => 'center center',
					't' => 'center center',
					'm' => 'center center',
				],
				'repeat' => 'repeat',
				'size' => [
					'd' => 'cover',
					't' => 'cover',
					'm' => 'contain',
				],
				],
			],
			'header_top_color' => '#FFFFFF',
			'header_top_fonts' => '12',
			'header_top_linkcolor' => [
				'm' => '#FFFFFF',
				'h' => 'rgba(255, 255, 255, 0.75)',
			],
			'header_top_padding' => '12',
			'header_topone_position' => 'left',
			'header_toptwo_position' => 'right',
			'menu_typo' => [
				'f' => 'Primary',
				's' => '14',
				'h' => '1.1',
				'ls' => '0',
				't' => 'None',
				'st' => '500',
				'c' => 'Headline',
				'ch' => 'Primary',
			],
			'menu_bg' => [
				'type' => 'solid',
				'solid' => 'rgba(0, 0, 0, 0.8)',
				'gradient' => [
				'angle' => '180',
				'color1' => '#070707',
				'color2' => '#202020',
				],
				'image' => [
				'url' => '',
				'attachment' => 'scroll',
				'position' => [
					'd' => 'center center',
					't' => 'center center',
					'm' => 'center center',
				],
				'repeat' => 'repeat',
				'size' => [
					'd' => 'cover',
					't' => 'cover',
					'm' => 'contain',
				],
				],
			],
			'header_sd_bg' => [
				'blur' => 'false',
				'type' => 'solid',
				'solid' => '#ffffff',
				'gradient' => [
				'angle' => '90',
				'color1' => '#19187C',
				'color2' => '#532df5',
				],
				'image' => [
				'url' => '',
				'attachment' => 'fixed',
				'position' => [
					'd' => 'bottom center',
					't' => 'center center',
					'm' => 'center center',
				],
				'repeat' => 'no-repeat',
				'size' => [
					'd' => 'cover',
					't' => 'cover',
					'm' => 'contain',
				],
				],
			],
			'header_cta_size'	=> 'medium',
			'menu_interaction' => 'none',
			'menu_interaction_color' => 'Primary',
			'menu_spacing' => '50',
			'menu_position' => 'right',
			'submenu_trigger' => 'hover',
			'submenu_bg' => 'Dark Neutral',
			'submenu_color' => [
				'f' => 'Primary',
				's' => '14',
				'h' => '1.1',
				'ls' => '0',
				't' => 'None',
				'st' => '500',
				'c' => 'Headline',
				'ch' => 'Primary',
			],
			'submenu_scolor' => '#222222',
			'mobile_sticky' => 'false',
			'mobile_pill' => 'false',
			'mobile_pill_radius' => '12',
			'mobile_logo_h' => '24',
			'mobile_menu_padding'=> '',
			'mobile_menu_bg' => [
				'blur' => 'false',
				'type' => 'solid',
				'solid' => '#ffffff',
				'gradient' => [
				'angle' => '90',
				'color1' => '#19187C',
				'color2' => '#532df5',
				],
				'image' => [
				'url' => '',
				'attachment' => 'fixed',
				'position' => [
					'd' => 'bottom center',
					't' => 'center center',
					'm' => 'center center',
				],
				'repeat' => 'no-repeat',
				'size' => [
					'd' => 'cover',
					't' => 'cover',
					'm' => 'contain',
				],
				],
			],
			'mmenu_typo' => [
				's' => '18',
				'h' => '1',
				'ls' => '0',
				't' => 'None',
				'st' => 'regular',
				'c' => 'Headline',
				'ch' => 'Primary',
				'f' => 'Primary',
			],
			'mmenu_center' => 'left',
			'footer_bg' => [
				'type' => 'Dark Neutral',
				'solid' => 'Dark Neutral',
				'gradient' => [
				'angle' => '90',
				'color1' => '#19187C',
				'color2' => '#532df5',
				],
				'image' => [
				'url' => '',
				'attachment' => 'fixed',
				'position' => [
					'd' => 'center center',
					't' => 'center center',
					'm' => 'center center',
				],
				'repeat' => 'no-repeat',
				'size' => [
					'd' => 'cover',
					't' => 'cover',
					'm' => 'contain',
				],
				],
			],
			'footer_padding' => [
				'd' => '80',
				't' => '50',
				'm' => '30',
			],
			'footer_title' => [
				's' => [
				'd' => '16',
				't' => '15',
				'm' => '14',
				],
				'h' => '1.2',
				'ls' => '0',
				't' => 'None',
				'st' => '600',
				'c' => '#FFFFFF',
				'f' => 'Primary',
			],
			'footer_text' => [
				's' => [
				'd' => '16',
				't' => '15',
				'm' => '14',
				],
				'h' => '1.875',
				'ls' => '0',
				't' => 'None',
				'st' => 'regular',
				'c' => 'Body',
				'f' => 'Text',
			],
			'footer_link' => [
				'm' => 'Body',
				'h' => 'Primary',
			],
			'copyrights_padding' => [
				'd' => '40',
				't' => '40',
				'm' => '30',
			],
			'copyrights_bg' => 'Dark Neutral',
			'copyrights_border' => 'true',
			'copyrights_borderc' => 'rgba(255, 255, 255, 0.2)',
			'copyrights_border_whide' => 'false',
			'copyrights_text' => 'Body',
			'copyrights_texts' => [
				'd' => '16',
				't' => '16',
				'm' => '16',
			],
			'copyrights_link' => [
				'm' => '#FFFFFF',
				'h' => 'Primary',
			],
			'pagetitle_padding' => [
				'd' => '100',
				't' => '75',
				'm' => '50',
			],
			'pagetitle_radius' => '0',
			'pagetitle_margin' => '0',
			'pagetitle_overlay' => [
				'blur' => 'false',
				'type' => 'none',
				'solid' => 'rgba(0, 0, 0, 0.5)',
				'gradient' => [
				'angle' => '180',
				'color1' => 'rgba(12, 12, 12, 0.5)',
				'color2' => '#0C0C0C',
				],
			],
			'pagetitle_color' => '#FFFFFF',
			'pagetitle_transform' => 'none',
			'pagetitle_width' => '50',
			'pagetitle_align' => 'left',
			'blog_title' => [
				'f' => 'Primary',
				's' => [
					'd' => '18',
					't' => '18',
					'm' => '16',
				],
				'h' => '1.44',
				'ls' => '0',
				't' => 'None',
				'st' => '600',
				'c' => 'Headline',
			],
			'blog_ex' => [
				'f' => 'Text',
				's' => [
				'd' => '14',
				't' => '13',
				'm' => '13',
				],
				'h' => '1.57',
				'ls' => '0',
				't' => 'None',
				'st' => 'regular',
				'c' => 'Headline',
			],
			'blog_img_radius' => '6',
			'blog_padding' => [
				'd' => '100',
				't' => '75',
				'm' => '45',
			],
			'blog_filters_align'	=> 'left',
			'blogs_pagetitle_width' => '75',
			'blogs_related' => 'false',
			'blogs_wide_align' => '2',
			'blogs_related_title' => 'Related Posts',
			'blogs_related_style' => 'grid',
			'blogs_related_filter' => 'tag',
			'blog_h1' => [
				'f' => 'Primary',
				's' => [
					'd' => ['value' => '72', 'unit' => 'px'],
					't' => ['value' => '48', 'unit' => 'px'],
					'm' => ['value' => '34', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.2', 'unit' => 'em'],
					't' => ['value' => '1.2', 'unit' => 'em'],
					'm' => ['value' => '1.2', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '-0.027', 'unit' => 'em'],
					't' => ['value' => '-0.027', 'unit' => 'em'],
					'm' => ['value' => '-0.027', 'unit' => 'em'],
				],
				't' => 'none',
				'st' => '600',
				'c' => 'Headline',
			],
			'blog_h2' => [
				'f' => 'Primary',
				's' => [
					'd' => ['value' => '40', 'unit' => 'px'],
					't' => ['value' => '34', 'unit' => 'px'],
					'm' => ['value' => '26', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.2', 'unit' => 'em'],
					't' => ['value' => '1.2', 'unit' => 'em'],
					'm' => ['value' => '1.2', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '-0.027', 'unit' => 'em'],
					't' => ['value' => '-0.027', 'unit' => 'em'],
					'm' => ['value' => '-0.027', 'unit' => 'em'],
				],
				't' => 'None',
				'st' => '600',
				'c' => 'Headline',
			],
			'blog_h3' => [
				'f' => 'Primary',
				's' => [
					'd' => ['value' => '34', 'unit' => 'px'],
					't' => ['value' => '26', 'unit' => 'px'],
					'm' => ['value' => '20', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.25', 'unit' => 'em'],
					't' => ['value' => '1.25', 'unit' => 'em'],
					'm' => ['value' => '1.25', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '0', 'unit' => 'em'],
					't' => ['value' => '0', 'unit' => 'em'],
					'm' => ['value' => '0', 'unit' => 'em'],
				],
				't' => 'None',
				'st' => '600',
				'c' => 'Headline',
			],
			'blog_h4' => [
				'f' => 'Primary',
				's' => [
					'd' => ['value' => '20', 'unit' => 'px'],
					't' => ['value' => '18', 'unit' => 'px'],
					'm' => ['value' => '16', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.1', 'unit' => 'em'],
					't' => ['value' => '1.1', 'unit' => 'em'],
					'm' => ['value' => '1.1', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '0', 'unit' => 'em'],
					't' => ['value' => '0', 'unit' => 'em'],
					'm' => ['value' => '0', 'unit' => 'em'],
				],
				't' => 'None',
				'st' => '600',
				'c' => 'Headline',
			],
			'blog_h5' => [
				'f' => 'Primary',
				's' => [
					'd' => ['value' => '16', 'unit' => 'px'],
					't' => ['value' => '15', 'unit' => 'px'],
					'm' => ['value' => '14', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.1', 'unit' => 'em'],
					't' => ['value' => '1.1', 'unit' => 'em'],
					'm' => ['value' => '1.1', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '0', 'unit' => 'em'],
					't' => ['value' => '0', 'unit' => 'em'],
					'm' => ['value' => '0', 'unit' => 'em'],
				],
				't' => 'None',
				'st' => '600',
				'c' => 'Headline',
			],
			'blog_h6' => [
				'f' => 'Text',
				's' => [
					'd' => ['value' => '14', 'unit' => 'px'],
					't' => ['value' => '13', 'unit' => 'px'],
					'm' => ['value' => '12', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.1', 'unit' => 'em'],
					't' => ['value' => '1.1', 'unit' => 'em'],
					'm' => ['value' => '1.1', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '0', 'unit' => 'em'],
					't' => ['value' => '0', 'unit' => 'em'],
					'm' => ['value' => '0', 'unit' => 'em'],
				],
				't' => 'Uppercase',
				'st' => 'regular',
				'c' => 'Headline',
			],
			'blog_p' => [
				'f' => 'Text',
				's' => [
					'd' => ['value' => '16', 'unit' => 'px'],
					't' => ['value' => '18', 'unit' => 'px'],
					'm' => ['value' => '16', 'unit' => 'px'],
				],
				'h' => [
					'd' => ['value' => '1.875', 'unit' => 'em'],
					't' => ['value' => '1.875', 'unit' => 'em'],
					'm' => ['value' => '1.875', 'unit' => 'em'],
				],
				'ls' => [
					'd' => ['value' => '0', 'unit' => 'em'],
					't' => ['value' => '0', 'unit' => 'em'],
					'm' => ['value' => '0', 'unit' => 'em'],
				],
				't' => 'None',
				'st' => 'regular',
				'c' => 'Body',
			],
			'blog_link_color' => [
				'm' => 'Primary',
				'h' => 'Secondary',
			],
			'portfolio_img_radius' => '6',
			'portfolio_padding' => [
				'd' => '100',
				't' => '75',
				'm' => '45',
			],


			'customcss' => ' /* CUSTOM CSS */',
			'customjs' => ' //CUSTOM JS',

			'skin_top_banner' 			=> [
				'value' => 'default',
				'conditions' => [
					'header_top' => 'true',
				],
			],
			'skin_navbar' 				=> [
				'value' => 'default',
				'dynamic' => 'header_layout',
				'conditions' => [
					'header' => 'true',
				],
			],
			'skin_mobile_navbar' 		=> [
				'value' => 'default',
			],
			'skin_sidebars' 			=> [
				'value' => 'default',
				'conditions' => [
					'performance_widgets' => 'true'
				],
			],
			'skin_page_title' 			=> [
				'value' => 'default',
				'conditions' => [
					'pagetitle' => 'true',
				],
			],
			'skin_footer' 				=> [
				'value' => 'default',
				'conditions' => [
					'footer' => 'true',
					'copyrights' => 'true',
				],
			],
			'skin_blog' 				=> [
				'value' => 'default',
				'type' => 'blog',
			],
			'skin_portfolio' 			=> [
				'value' => 'default',
				'type' => 'portfolio',
			],
			'skin_ham' 					=> [
				'value' => 'default',
			],
			'skin_back_to_top' 			=> [
				'value' => 'default',
				'conditions' => [
					'gen_btop' => 'true',
				],
			],
			'skin_link' 				=> [
				'value' => 'default',
			],
			'skin_button' 				=> [
				'value' => 'default',
			],

			'performance_smart_preload'	=> 'false',
			'performance_inline_critical'=> 'false',

			'performance_widgets'		=> 'true',
			'performance_animations'	=> 'true',
			'performance_ugly_animations'=> 'false',
			'proxy'						=> 'false'
		];

		return apply_filters('uicore_settings_default_admin', self::$defaults_admin);
	}


	/**
	 * Set default Theme options used in admin
	 *
	 * @return array
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.2
	 */
	public static function admin_customizer_defaults() {

		if ( ! is_null( self::$defaults_admin_customizer ) ) {
			return self::$defaults_admin_customizer;
		}

		self::$defaults_admin_customizer = [
			'admin_customizer'		=> 'false',
			'theme_name'			=> '',
			'admin_icon'			=> '',
			'to_logo'				=> '',
			'to_color'				=> '#532df5',
			'to_content'			=> '',
			'to_documentation'		=> 'false',
			'wp_background'			=> '#f0f0f1',
			'wp_form_background'	=> '#ffffff',
			'wp_logo'				=> '',
			'wp_logo_height'		=> '40',
			'wp_text_color'			=> '#50575e',
			'wp_text_bg_color'		=> '#50575e',
			'wp_button_background'	=> '#2271b1',

			//ONLY FROM MENU
			'to_import'				=> 'false',
            'disable_library'       => 'false',
			'to_plugins'			=> 'false',
			'to_performance'		=> 'false',
			'to_updates'			=> 'false',
			'to_reset'				=> 'false',
			'to_system'				=> 'false',
			'to_typo'				=> 'false',
			'to_custom'				=> 'false',
			'to_reset'				=> 'false',
			'to_ep'					=> 'false',
		];

		return self::$defaults_admin_customizer;
	}

	/**
	 * Set default Theme Options used in Fontend
	 *
	 * @return array
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function front_defaults() {
		self::$defaults_front = [
			'gen_cursor' 				=> 'false',

			//DISABLE ALL
			'disable_blog'				=> 'false',
			'disable_portfolio'			=> 'false',
			'disable_woocomerce'		=> 'false',
			'disable_tb'				=> 'false',


			'settings_version'			=> '0',
			'animations'				=> 'true',
			'animations_ham'			=> 'fade in',
			'animations_submmenu' 		=> 'slide',

			//COLORS ARE HERE ONLY FOR TUTOR LMS; TODO - do a better job :D
			'pColor'					=> '#00C49A',
			'sColor'					=> '#532DF5',
			'aColor'					=> '#D1345B',
			'hColor'					=> '#070707',
			'bColor'					=> '#6E7A84',
			'dColor'					=> '#070707',
			'lColor'					=> '#F8FCFC',
			'wColor'					=> '#FFFFFF',

			'fav'						=> '',
			'gen_layout'				=> 'full width',
			'gen_btop'					=> 'true',
			'gen_btopm'					=> 'false',
			'gen_themecolor'			=> 'true',
			'gen_themecolorcode'		=> 'Primary',
			'gen_maintenance'			=> 'false',
			'gen_maintenance_page'		=> [
				'name'	=> 'default',
				'id'	=> 0
			],
			'gen_404'					=> [
				'name'	=> 'default',
				'id'	=> 0
			],

			'header_top'				=> 'true',
			'header_top_dismissable'	=> 'false',
			'header_top_token'			=> 'ak2al8gog',
			'header_toplayout'			=> 'two columns',
			'header_topone'				=> 'custom',
			'header_topone_content'		=> '<p>Learn as if you will live forever, live like you will die tomorrow.</p>',
			'header_toptwo'				=> 'custom',
			'header_toptwo_content'		=> '[uicore-icon icon=phone]   +1 555 87 89 56[/uicore-icon][uicore-icon icon=map-pin left=35px]   80 Harrison Lane, FL 32547[/uicore-icon]',
			'header_top_dismissable'	=> 'false',
			'header_top_sticky'			=> 'false',

			'header'					=> 'true',
			'header_layout'      		=> 'classic',
			'header_wide'				=> 'false',
			'header_shrink'				=> 'false',
			'header_transparent'		=> 'false',
			'mmenu_animation'			=> 'fade',
			'menu_active'				=> 'false',
			'menu_focus'				=> 'false',
			'header_wide'				=> 'false',
			'header_pill' 				=> 'false',
			'header_sticky'				=> 'true',
			'header_sticky_smart'		=> 'true',
			'header_search'				=> 'false',
			'header_icons'				=> 'false',
			'woo'						=> 'false',
			'header_cta'				=> 'true',
			'header_ctalink'			=> '#',
			'header_ctatarget'			=> '_self',
			'header_cta_inverted'		=> 'true',
			'header_ctatext'			=> 'Get a Quote',
			'header_side_drawer'		=> 'false',
			'header_sd_text'			=> '',
			'header_sd_toggle'			=> 'hover',
			'header_sd_position'		=> 'right',
			'header_custom_desktop'		=> 'false',
			'header_custom_desktop'		=> 'false',
			'header_custom_mobile'		=> 'false',
			'mmenu_logo'				=> 'false',
			'menu_logo'					=> 'none',
			'mobile_breakpoint' 		=> '1025',
			'mobile_back' 				=> 'Back',
			'mobile_layout' 			=> 'default',
			'mobile_extra_content' 		=> 'cta',

			'footer'					=> 'true',
			'footer_layout'				=> 'five',
			'footer_wide'				=> 'false',
			'copyrights'				=> 'true',
			'copyrights_content'		=> '<p>&copy; <a href="https://www.uicore.co/" target="_blank" rel="noopener">UiCore</a> [year]. All Rights Reserved.</p>',
			'copyrights_icons' 			=> 'false',

			'logo'						=> 'https://uicore.co/themeforest/uicore-logo.png',
			'logoS'						=> '',
			'logoMobile'				=> '',
			'logoSMobile'				=> '',

			'pagetitle'					=> 'true',
			'pagetitle_tag'				=> 'h1',
			'pagetitle_i'				=> 'true',
			'pagetitle_breadcrumbs'		=> 'true',
			'pagetitle_bg' 				=> [
				'type' 			=> 'Dark Neutral',
				'solid' 		=> 'Dark Neutral',
				'gradient' 		=> [
					'angle' 		=> '90',
					'color1' 		=> '#ffffff',
					'color2' 		=> '#222222',
				],
				'image' 		=> [
					'url' 			=> '',
					'attachment' 	=> 'scroll',
					'position' 		=> [
						'd' => 'bottom center',
						't' => 'center center',
						'm' => 'center center',
					],
					'repeat' 		=> 'no-repeat',
					'size' 			=> [
						'd' => 'cover',
						't' => 'cover',
						'm' => 'contain',
					],
				],
			],

			'blog_layout'				=> 'grid',
			'blog_item_style' 			=> 'simple',
			'blog_hover_effect'			=> 'zoom',
			'blog_ratio'				=> 'landscape',
			'blog_col'					=> '3',
			'blog_col_space'			=> 'large',
			'blog_category'				=> 'true',
			'blog_excerpt'				=> 'true',
			'blog_author'				=> 'true',
			'blog_readtime'				=> 'true',
			'blog_date'					=> 'true',
			'blog_sidebar_id'			=> 'none',
			'blog_sidebar'				=> 'left',
			'blog_sidebars'				=> 'true',
			'blog_posts_number'			=> '12',
			'blog_excerpt_length'		=> '22',
			'blogs_progress'			=> 'true',
			'blog_filters'				=> 'false',
			'blog_filters_all_text'		=> 'All Categories',

			'blogs_title'				=> 'default page title',
			'blogs_excerpt_in_pt'		=> 'true',
			'blogs_author'				=> 'true',
			'blogs_date'				=> 'true',
			'blogs_readtime'			=> 'true',
			'blogs_date_type'			=> 'published',
			'blogs_category'			=> 'true',
			'blogs_sidebar_id'			=> 'none',
			'blogs_sidebar'				=> 'left',
			'blogs_sidebars'			=> 'true',
			'blogs_narrow'				=> 'true',
			'blogs_navigation'			=> 'false',
			'blogs_navigation'			=> 'false',
			'blogs_loop_navigation'		=> 'false',
			'blogs_tags'				=> 'true',
			'blogs_img'					=> 'true',
			'blogs_breadcrumb'			=> 'false',
			'blogs_author_box'			=> 'false',
			'blogs_author_style'		=> 'simple',
			'blogs_related'				=> 'false',
			'blogs_related_filter'		=> 'random',
			'blogs_related_style'		=> 'list',
			'blogs_styles_tb'			=> 'true',

			'portfolio_page'			=> [
				'name'	=> 'default',
				'id'	=> 0
			],
			'portfolio_sidebar_id'		=> 'none',
			'portfolio_sidebar'			=> 'left',
			'portfolio_sidebars'		=> 'true',
			'portfolios_sidebar_id'		=> 'none',
			'portfolios_sidebar'		=> 'left',
			'portfolios_sidebars'		=> 'true',
			'portfolios_navigation'		=> 'false',
			'portfolios_loop_navigation'=> 'false',
			'portfolio_full_width'		=> 'false',
			'portfolio_posts_number'	=> '12',
			'portfolio_layout'			=> 'masonry',
			'portfolio_hover_effect'	=> 'zoom',
			'portfolio_ratio'			=> 'square',
			'portfolio_col'				=> '3',
			'portfolio_col_space' 		=> 'large',
			'portfolio_justified_size'	=> 'medium',

			'typekit' 					=> [
				'id' => '',
				'fonts' => [],
			],
			'customFonts' 				=> [],

			'social_fb' 				=> '',
			'social_tw'					=> '',
			'social_yt' 				=> '',
			'social_in' 				=> '',
			'social_lk' 				=> '',
			'social_pn' 				=> '',
			'social_th' 				=> '',
			'social_snapchat' 			=> '',
			'social_reddit' 			=> '',
			'social_tiktok'				=> '',
			'social_whatsapp' 			=> '',
			'social_vimeo' 				=> '',
			'social_wechat' 			=> '',
			'social_messenger' 			=> '',
			'social_discord' 			=> '',
			'social_telegram' 			=> '',
			'social_opensea' 			=> '',

			'performance_emojy'			=> 'true',
			'performance_fa'			=> 'true',
			'performance_block_style'	=> 'true',
			'performance_eicon'			=> 'true',
			'performance_animations'	=> 'true',
			'performance_fonts'			=> 'true',
			'performance_embed'			=> 'true',
			'performance_preload_img'	=> 'true',
			'performance_preload'		=> [
				[
					'url' => '',
					'as'  => ''
				]
				],
			'header_content'			=> '',
			'footer_content'			=> '',
		];

		return apply_filters('uicore_settings_default_front', self::$defaults_front);
	}

	/**
	 * Get fontend Options (default combined with changed options)
	 *
	 * @return array
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function get_extra_options_all() {
		if(!is_array(self::$options_extra)){
			self::refresh_extra_options();
		}
		return self::$options_extra;
	}

	/**
	 * Get fontend Options (default combined with changed options)
	 *
	 * @return array
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function get_front_options_all() {
		if(!is_array(self::$options_front)){
			self::refresh_front_options();
		}
		return \apply_filters('_uicore_front_options_all',self::$options_front);
	}

	/**
	 * Get admin Options (default combined with changed options)
	 *
	 * @return array
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function get_admin_options_all() {
		if(!is_array(self::$options_admin)){
			self::refresh_admin_options();
		}
		return self::$options_admin;
	}

	/**
	 * Get admin_customizer Options (default combined with changed options)
	 *
	 * @return array
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function get_admin_customizer_options_all() {
		if(!is_array(self::$options_admin_customizer)){
			self::refresh_admin_customizer_options();
		}
		return self::$options_admin_customizer;
	}


	/**
	 * Update Extra static array
	 *
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function refresh_extra_options() {
		/**
		* This function calculates the sum of two numbers.
		* Key is the name of the settings and value is the list with options
		*/
		self::$defaults_extra = \apply_filters('uicore_extra_settings', []);
		self::$options_extra = [];
		$options = [];
		foreach(self::$defaults_extra as $module => $settings){
			$data = get_option($module);
			$data = $data ? wp_parse_args($data, $settings) : $settings;
			$options = \wp_parse_args($data, $options);
			self::$options_extra = wp_parse_args(self::$options_extra, $data);
		}
	}

	/**
	 * Update Frontend static array
	 *
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function refresh_front_options() {
		self::$options_front = wp_parse_args(
			self::get_front_db_options(),
			self::front_defaults()
		);
	}


	/**
	 * Update Admin static array
	 *
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function refresh_admin_options() {
		self::$options_admin = wp_parse_args(
			self::get_admin_db_options(),
			self::admin_defaults()
		);
	}


	/**
	 * Update Admin static array
	 *
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.2
	 */
	public static function refresh_admin_customizer_options() {
		self::$options_admin_customizer = wp_parse_args(
			self::get_admin_customizer_db_options(),
			self::admin_customizer_defaults()
		);
	}

	/**
	 * Save NonDefault options in db and return the new settings array ( add version '0' to mark that the file were not generated )
	 *
	 * @return mixed
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function update_all($settings, $version = 'random' ) {

		if(!is_array(self::$db_options_no_defaults_front)){
			self::$db_options_no_defaults_front = [];
		}
		foreach(self::front_defaults() as $key => $value){
			if(isset($settings[$key]) && Settings::is_not_default($value, $settings[$key])){
				self::$db_options_no_defaults_front[$key] = $settings[$key];
			}else{
				if(is_array(self::$db_options_no_defaults_front) && isset(self::$db_options_no_defaults_front[$key])){
					unset(self::$db_options_no_defaults_front[$key]);
				}
			}
		}

		if(!is_array(self::$db_options_no_defaults_admin)){
			self::$db_options_no_defaults_admin = [];
		}
		foreach(self::admin_defaults() as $key => $value){
			if(isset($settings[$key]) && Settings::is_not_default($value, $settings[$key])){
				self::$db_options_no_defaults_admin[$key] = $settings[$key];
			}else{
				if(is_array(self::$db_options_no_defaults_admin) && isset(self::$db_options_no_defaults_admin[$key])){
					unset(self::$db_options_no_defaults_admin[$key]);
				}
			}
		}
		// unset(self::$db_options_no_defaults_admin['skin_portfolio']);

		if(!is_array(self::$db_options_no_defaults_admin_customizer)){
			self::$db_options_no_defaults_admin_customizer = [];
		}
		foreach(self::admin_customizer_defaults() as $key => $value){
			if(isset($settings[$key]) && Settings::is_not_default($value, $settings[$key])){
				self::$db_options_no_defaults_admin_customizer[$key] = $settings[$key];
			}else{
				if(is_array(self::$db_options_no_defaults_admin_customizer) && isset(self::$db_options_no_defaults_admin_customizer[$key])){
					unset(self::$db_options_no_defaults_admin_customizer[$key]);
				}
			}
		}

		/**
		* This function calculates the sum of two numbers.
		* Key is the name of the settings and value is the list with options
		*/
		self::$defaults_extra = \apply_filters('uicore_extra_settings', []);

		//add extra options to db array
		$final_extra_settings = [];
		if(\is_array(self::$defaults_extra) && !empty(self::$defaults_extra)){


			foreach(self::$defaults_extra as $module => $defaults){
				//HANDLE EACH MODULE SAME AS FRONT AND ADMIN
				if( (isset(self::$db_options_no_defaults_extra[$module]) && !is_array(self::$db_options_no_defaults_extra[$module])) || !isset(self::$db_options_no_defaults_extra[$module])){
					self::$db_options_no_defaults_extra[$module] = [];
				}
				foreach($defaults as $key => $value){
					if(isset($settings[$key]) && Settings::is_not_default($value, $settings[$key])){
						self::$db_options_no_defaults_extra[$module][$key] = $settings[$key];
					}else{
						if(is_array(self::$db_options_no_defaults_extra[$module]) && isset(self::$db_options_no_defaults_extra[$module][$key])){
							unset(self::$db_options_no_defaults_extra[$module][$key]);
						}
					}
				}
				$autoload_module = \apply_filters('_uicore_'.$module.'_autoload', false);
				update_option($module, self::$db_options_no_defaults_extra[$module], $autoload_module);
				$final_extra_settings = wp_parse_args($final_extra_settings, \wp_parse_args( self::$db_options_no_defaults_extra[$module], self::$defaults_extra[$module]));
			}

		}


		//generate new assets version
		if($version === 'random'){
			$version = rand(1000, 9999);
		}
		self::$db_options_no_defaults_front['settings_version'] = $version;

		update_option( UICORE_SETTINGS, self::$db_options_no_defaults_front,true);
		update_option( UICORE_SETTINGS.'_admin', self::$db_options_no_defaults_admin,false);
		update_option( UICORE_SETTINGS.'_admin_customizer', self::$db_options_no_defaults_admin_customizer, false);

		Helper::activate_ep();

		$final_settings = wp_parse_args(
			wp_parse_args(
				self::$db_options_no_defaults_front,
				self::front_defaults()
			),
			wp_parse_args(
				wp_parse_args(
					self::$db_options_no_defaults_admin,
					self::admin_defaults()
				),
				wp_parse_args(
					self::$db_options_no_defaults_admin_customizer,
					self::admin_customizer_defaults()
				)
			)
		);
		return \wp_parse_args($final_extra_settings, $final_settings);
	}

	/**
	 * Get all settings from static array
	 *
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function get_all() {
		$settings = wp_parse_args(
			self::get_front_options_all(),
			wp_parse_args(
				self::get_admin_options_all(),
				self::get_admin_customizer_options_all()
			)
		);
		return \wp_parse_args(self::get_extra_options_all(),$settings);
	}

	/**
	 * Get all settings from static array
	 *
	 * @return array
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function get_all_defaults() {
		return wp_parse_args(
			self::front_defaults(),
			wp_parse_args(
				self::admin_defaults(),
				self::admin_customizer_defaults()
			)
		);
	}

	/**
	 * Get Frontend Settings from db
	 *
	 * @return mixed
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function get_front_db_options() {
		self::$db_options_no_defaults_front = get_option( UICORE_SETTINGS, [] );
		return self::$db_options_no_defaults_front;
	}

	/**
	 * Get Admin Settings from db
	 *
	 * @return mixed
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function get_admin_db_options() {
		self::$db_options_no_defaults_admin = get_option( UICORE_SETTINGS.'_admin', [] );
		return self::$db_options_no_defaults_admin;
	}

	/**
	 * Get Admin Customizer Settings from db
	 *
	 * @return mixed
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 3.0.0
	 */
	public static function get_admin_customizer_db_options() {
		self::$db_options_no_defaults_admin_customizer = get_option( UICORE_SETTINGS.'_admin_customizer', [] );
		return self::$db_options_no_defaults_admin_customizer;
	}
}
// add_action( 'after_setup_theme', array( 'UiCore\ThemeOptions', 'get_instance' ) );
ThemeOptions::get_instance();
