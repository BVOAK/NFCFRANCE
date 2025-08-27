<?php
namespace ElementPack\VariationSwatches\Admin;

defined( 'ABSPATH' ) || exit;

use ElementPack\VariationSwatches\Variation_Swatches;
use ElementPack\Base\Singleton;

class Settings {
	use Singleton;
	const OPTION_NAME = 'ep_variation_swatches';

	protected $settings;

	public function get_settings() {
		return apply_filters( 'ep_variation_swatches_settings_default', [
			'shape'               => 'round',
			'size'                => ['width' => 30, 'height' => 30],
			'tooltip'             => 'yes',
			'auto_button'         => 'yes',
			'show_selected_label' => 'no',
		] );
	}

	public function get_default( $name = null ) {
		$settings = $this->get_settings();

		if (  ! $name ) {
			return (array) $settings;
		}

		if ( ! isset( $settings[ $name ] ) ) {
			return false;
		}

		// Check the theme support.
		if ( current_theme_supports( 'woocommerce' ) ) {
			$support = wc_get_theme_support( 'variation_swatches::' . $name );
		}

		if ( ! empty( $support ) ) {
			return $support;
		}

		return $settings[ $name ];
	}

	public function get_option( $name ) {
		$options = get_option('element_pack_other_settings');

		$shape = isset($options['ep_variation_swatches_shape']) ? $options['ep_variation_swatches_shape'] : false;
		$size = isset($options['ep_variation_swatches_size']) ? $options['ep_variation_swatches_size'] : false;	
		$tooltip = isset($options['ep_variation_swatches_tooltip']) ? $options['ep_variation_swatches_tooltip'] : false;

		if (isset( $shape )) {
			update_option( 'ep_variation_swatches_shape', $shape );
		}

		if (isset( $size )) {
			update_option( 'ep_variation_swatches_size', $size );
		}

		if (isset( $tooltip )) {
			update_option( 'ep_variation_swatches_tooltip', $tooltip );
		}		

		$value = get_option( $this->get_option_name( $name ) );

		return $value;
	}

	public function update_option( $name, $value ) {
		update_option( $this->get_option_name( $name ), $value );
	}

	public function get_shape_options() {
		$options = apply_filters( 'ep_variation_swatches_shape_options', [
			'round'   => esc_html__( 'Circle', 'bdthemes-element-pack' ),
			'rounded' => esc_html__( 'Rounded corners', 'bdthemes-element-pack' ),
			'square'  => esc_html__( 'Square', 'bdthemes-element-pack' ),
		] );

		if ( current_theme_supports( 'woocommerce' ) ) {
			$theme_style = wc_get_theme_support( 'variation_swatches::theme_style' );

			if ( $theme_style ) {
				$options = array_merge( [ 'default' => esc_html__( 'Theme Default', 'bdthemes-element-pack' ) ], $options );
			}
		}

		return $options;
	}

	public function get_option_name( $name ) {
		return self::OPTION_NAME . '_' . $name;
	}

	public function sanitize_type( $value ) {
		$types = array_keys( wc_get_attribute_types() );

		return in_array( $value, $types ) ? $value : '';
	}

	public function sanitize_shape( $value ) {
		$shapes = array_keys( $this->get_shape_options() );

		return in_array( $value, $shapes ) ? $value : '';
	}

	public function sanitize_size( $value ) {
		$width  = isset( $value['width'] ) ? $value['width'] : array_shift( $value );
		$height = isset( $value['height'] ) ? $value['height'] : array_shift( $value );

		return [
			'width'  => absint( $width ),
			'height' => absint( $height ),
		];
	}
}

Settings::instance();
