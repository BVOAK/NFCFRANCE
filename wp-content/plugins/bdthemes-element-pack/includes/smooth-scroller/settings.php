<?php

namespace ElementPack\Includes\SmoothScroller;

use Elementor\Plugin;
use Elementor\Core\Kits\Documents\Kit;
use ElementPack\Includes\SmoothScroller\Settings_Contorls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SmoothScroller_Loader {

	private static $instance = null;

	private function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 99999 );
	}

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function enqueue_scripts() {
		wp_register_script( 'gsap', BDTEP_ASSETS_URL . 'vendor/js/gsap.min.js', [], '3.12.5', true );
		wp_register_script( 'lenis', BDTEP_URL . 'includes/smooth-scroller/assets/lenis.min.js', [ 'gsap' ], BDTEP_VER, true );
		wp_register_style( 'lenis', BDTEP_URL . 'includes/smooth-scroller/assets/lenis.min.css', [], BDTEP_VER );
		wp_register_script( 'bdt-smooth-scroller', BDTEP_URL . 'includes/smooth-scroller/assets/ep-smooth-scroller.js', [ 'jquery-core', 'lenis' ], BDTEP_VER, true );

		wp_enqueue_style( 'lenis' );
		wp_enqueue_script( 'gsap' );
		wp_enqueue_script( 'lenis' );
		wp_enqueue_script( 'bdt-smooth-scroller' );
	}
}

SmoothScroller_Loader::get_instance();

