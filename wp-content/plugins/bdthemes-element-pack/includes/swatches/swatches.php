<?php
namespace ElementPack\VariationSwatches;

defined( 'ABSPATH' ) || exit;

use ElementPack\VariationSwatches\Helper;
use ElementPack\VariationSwatches\Admin\Term_Meta;
use ElementPack\Base\Singleton;

class Swatches {

	use Singleton;

	public function __construct() {
		add_action( 'init', [ $this, 'enqueue_scripts' ] );
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', [ $this, 'swatches_html' ], 100, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

	}

	public function admin_scripts() {
		wp_enqueue_media();		
	}
	public function enqueue_scripts() {

		$direction = is_rtl() ? '.rtl' : '';
		
		wp_register_style( 'ep-variation-swatches', BDTEP_URL . 'includes/swatches/assets/css/ep-variation-swatches' . $direction . '.css', [], BDTEP_VER );
		wp_register_script( 'ep-variation-swatches', BDTEP_URL . 'includes/swatches/assets/js/ep-variation-swatches.min.js', ['jquery'], BDTEP_VER );
		wp_register_script( 'ep-variation-swataches-term', BDTEP_URL . 'includes/swatches/assets/js/ep-variation-swataches-term.min.js', ['jquery', 'wp-color-picker', 'wp-util', 'jquery-serialize-object'], BDTEP_VER, true );
		wp_enqueue_style( 'ep-variation-swatches' );
		wp_enqueue_style( 'ep-font' );

		$inline_css = $this->inline_style();

		if ( ! empty( $inline_css ) ) {
			wp_add_inline_style( 'ep-variation-swatches', $this->inline_style() );
		}

		wp_enqueue_script( 'ep-variation-swatches' );

		$params = apply_filters( 'ep_variation_swatches_js_params', [
			'show_selected_label' => wc_string_to_bool( Helper::get_settings( 'show_selected_label' ) ),
		] );

		if ( ! empty( $params ) ) {
			wp_localize_script( 'ep-variation-swatches', 'ep_variation_swatches_params', $params );
		}

		do_action( 'ep_variation_swatches_enqueue_scripts' );
	}

	public function inline_style() {
		$options = get_option('element_pack_other_settings');
		$size = isset($options['ep_variation_swatches_size']) ? absint($options['ep_variation_swatches_size']) : 26;
	
		$current_size = get_option('ep_variation_swatches_size');
		if ($size !== $current_size) {
			update_option('ep_variation_swatches_size', $size);
		}
	
		$css = ':root { --ep-swatches-item-width: ' . $size . 'px; --ep-swatches-item-height: ' . $size . 'px; }';
	
		return apply_filters('ep_variation_swatches_css', $css);
	}
	

	public function swatches_html( $html, $args ) {
		$options   = $args['options'];
		$product   = $args['product'];
		$attribute = $args['attribute'];
		$name      = $args['name'] ? $args['name'] : wc_variation_attribute_name( $attribute );

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}

		if ( empty( $options ) ) {
			return $html;
		}
		$swatches_args = $this->get_swatches_args( $product->get_id(), $attribute );
		$swatches_args = wp_parse_args( $args, $swatches_args );

		if ( ! Helper::is_swatches_type( $swatches_args['swatches_type'] ) ) {
			return $html;
		}

		// Swatches html.
		$swatches_html = '';

		if ( $product && taxonomy_exists( $attribute ) ) {
			$terms = wc_get_product_terms(
				$product->get_id(),
				$attribute,
				[
					'fields' => 'all',
					'slug'   => $options,
				]
			);

			foreach ( $terms as $term ) {
				$swatches_html .= $this->get_term_swatches( $term, $swatches_args );
			}
		} else {
			foreach ( $options as $option ) {
				$swatches_html .= $this->get_term_swatches( $option, $swatches_args );
			}
		}

		if ( ! empty( $swatches_html ) ) {
			$classes       = [
				'ep-variation-swatches',
				'ep-variation-swatches--' . $swatches_args['swatches_type'],
				'ep-variation-swatches--' . $swatches_args['swatches_shape']
			];

			if ( $swatches_args['swatches_tooltip'] ) {
				$classes[] = 'ep-variation-swatches--has-tooltip';
			}

			$swatches_html = '<ul class="ep-variation-swatches__wrapper" data-attribute_name="' . esc_attr( $name ) . '" role="group">' . $swatches_html . '</ul>';
			$html          = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $html . $swatches_html . '</div>';
		}

		return apply_filters( 'ep_variation_swatches_html', $html, $args );
	}

	public function get_term_swatches( $term, $args ) {
		$type  = $args['swatches_type'];
		$value = is_object( $term ) ? $term->slug : $term;
		$name  = is_object( $term ) ? $term->name : $term;
		$name  = apply_filters( 'woocommerce_variation_option_name', $name, ( is_object( $term ) ? $term : null ), $args['attribute'], $args['product'] );
		$size  = ! empty( $args['swatches_size'] ) ? sprintf( '--ep-swatches-item-width: %1$dpx; --ep-swatches-item-height: %2$dpx;', absint( $args['swatches_size']['width'] ), absint( $args['swatches_size']['height'] ) ) : '';
		$html  = '';

		if ( is_object( $term ) ) {
			$selected = sanitize_title( $args['selected'] ) == $value;
		} else {
			// This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
			$selected = sanitize_title( $args['selected'] ) === $args['selected'] ? $args['selected'] == sanitize_title( $value ) : $args['selected'] == $value;
		}

		$data = $this->get_attribute_swatches_data( $term, $args );

		$class = [
			'ep-variation-swatches__item',
			'ep-variation-swatches__item-' . $value,
		];

		if ( $selected ) {
			$class[] = 'selected';
		}

		if ( ! empty( $args['swatches_class'] ) ) {
			$class[] = $args['swatches_class'];
		}

		switch ( $type ) {
			case 'color':
				$color = '--ep-swatches-item-color:' . $data['value'];
				$html  = sprintf(
					'<li class="%s" style="%s" aria-label="%s" data-value="%s" tabindex="0" role="button" aria-pressed="false">
						<span class="ep-variation-swatches__name">%s</span>
					</li>',
					esc_attr( implode( ' ', $class ) ),
					esc_attr( $size . $color ),
					esc_attr( $name ),
					esc_attr( $value ),
					esc_html( $name )
				);
				break;

			case 'image':
				$html = sprintf(
					'<li class="%s" style="%s" aria-label="%s" data-value="%s" tabindex="0" role="button" aria-pressed="false">
						<img src="%s" alt="%s">
						<span class="ep-variation-swatches__name">%s</span>
					</li>',
					esc_attr( implode( ' ', $class ) ),
					esc_attr( $size ),
					esc_attr( $name ),
					esc_attr( $value ),
					esc_url( $data['image_src'] ),
					esc_attr( ! empty( $data['image_alt'] ) ? $data['image_alt'] : $name ),
					esc_html( $name )
				);
				break;

			case 'label':
				$html = sprintf(
					'<li class="%s" style="%s" aria-label="%s" data-value="%s" tabindex="0" role="button" aria-pressed="false">
						<span class="ep-variation-swatches__name">%s</span>
					</li>',
					esc_attr( implode( ' ', $class ) ),
					esc_attr( $size ),
					esc_attr( $name ),
					esc_attr( $value ),
					esc_html( $data['value'] ? $data['value'] : $name )
				);
				break;

			case 'button':
				$html = sprintf(
					'<li class="%s" style="%s" aria-label="%s" data-value="%s" tabindex="0" role="button" aria-pressed="false">
						<span class="ep-variation-swatches__name">%s</span>
					</li>',
					esc_attr( implode( ' ', $class ) ),
					esc_attr( $size ),
					esc_attr( $name ),
					esc_attr( $value ),
					esc_html( $name )
				);
				break;
		}

		return apply_filters( 'ep_variation_swatches_' . $type . '_html', $html, $args, $data, $term );
	}

	public function get_swatches_args( $product_id, $attribute ) {
		$swatches_meta = Helper::get_swatches_meta( $product_id );
		$attribute_key = sanitize_title( $attribute );

		if ( ! empty( $swatches_meta[ $attribute_key ] ) ) {
			$swatches_args = [
				'swatches_type'       => $swatches_meta[ $attribute_key ]['type'],
				'swatches_shape'      => $swatches_meta[ $attribute_key ]['shape'],
				'swatches_size'       => 'custom' == $swatches_meta[ $attribute_key ]['size'] ? $swatches_meta[ $attribute_key ]['custom_size'] : '',
				'swatches_attributes' => $swatches_meta[ $attribute_key ]['swatches'],
			];

			if ( Helper::is_default( $swatches_args['swatches_type'] ) ) {
				$swatches_args['swatches_type'] = taxonomy_exists( $attribute ) ? Helper::get_attribute_taxonomy( $attribute )->attribute_type : 'select';
				$swatches_args['swatches_attributes'] = [];

				// converts select options to buttons.
				if ( 'select' == $swatches_args['swatches_type'] && wc_string_to_bool( Helper::get_settings( 'auto_button' ) ) ) {
					$swatches_args['swatches_type'] = 'button';
				}
			} else {
				$swatches_args['swatches_edited'] = true;
			}

			if ( Helper::is_default( $swatches_args['swatches_shape'] ) ) {
				$swatches_args['swatches_shape'] = Helper::get_settings( 'shape' );
			}
		} else {
			$swatches_args = [
				'swatches_type'       => taxonomy_exists( $attribute ) ? Helper::get_attribute_taxonomy( $attribute )->attribute_type : 'select',
				'swatches_shape'      => Helper::get_settings( 'shape' ),
				'swatches_size'       => '',
				'swatches_attributes' => [],
			];

			// convert select options to buttons.
			if ( 'select' == $swatches_args['swatches_type'] && wc_string_to_bool( Helper::get_settings( 'auto_button' ) ) ) {
				$swatches_args['swatches_type'] = 'button';
			}
		}

		$swatches_args['swatches_tooltip']    = wc_string_to_bool( Helper::get_settings( 'tooltip' ) );
		$swatches_args['swatches_image_size'] = $swatches_args['swatches_size'] ? $swatches_args['swatches_size'] : Helper::get_settings( 'size' );

		return apply_filters( 'ep_variation_swatches_item_args', $swatches_args, $attribute, $product_id, );
	}

	public function get_attribute_swatches_data( $term, $args ) {
		$type = isset( $args['swatches_type'] ) ? $args['swatches_type'] : 'select';
		$data = [
			'type'  => $type,
			'value' => '',
		];

		if ( ! Helper::is_swatches_type( $type ) ) {
			return $data;
		}

		$key = is_object( $term ) ? $term->term_id : sanitize_title( $term );

		if ( isset( $args['swatches_attributes'][ $key ] ) && isset( $args['swatches_attributes'][ $key ][ $type ] ) ) {
			$value = $args['swatches_attributes'][ $key ][ $type ];
		} else {
			$value = is_object( $term ) ? Term_Meta::instance()->get_meta( $term->term_id, $type ) : '';
		}

		$data['value'] = $value;

		$dimension = $args['swatches_image_size'];

		if ( 'image' == $type ) {
			if ( ! $value ) {
				$image_src = wc_placeholder_img_src( 'thumbnail' );
			} else {
				$image     = Helper::get_image( $value, $dimension, false );
				$image_src = $image ? $image[0] : wc_placeholder_img_src( 'thumbnail' );
			}

			$data['image_src'] = $image_src;

			if ( ! empty( $args['product'] ) ) {
				$product = $args['product'];
				$name    = is_object( $term ) ? $term->name : $term;

				$data['image_alt'] = $product->get_title() . ' - ' . $name;
			}
		}

		return apply_filters( 'ep_variation_swatches_attribute_swatches_data', $data, $term, $args );
	}
}
