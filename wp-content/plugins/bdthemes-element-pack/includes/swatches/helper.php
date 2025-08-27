<?php
namespace ElementPack\VariationSwatches;

defined( 'ABSPATH' ) || exit;

class Helper {
	public static function get_swatches_types() {
		return [
			'color'  => esc_html__( 'Color', 'bdthemes-element-pack' ),
			'image'  => esc_html__( 'Image', 'bdthemes-element-pack' ),
			'label'  => esc_html__( 'Label', 'bdthemes-element-pack' ),
			'button' => esc_html__( 'Button', 'bdthemes-element-pack' ),
		];
	}

	public static function is_swatches_type( $type ) {
		return array_key_exists( $type, self::get_swatches_types() );
	}
	public static function get_swatches_meta( $product_id = null ) {
		$product_id = $product_id ? $product_id : get_the_ID();

		return \ElementPack\VariationSwatches\Admin\Product_Data::instance()->get_meta( $product_id );
	}
	public static function get_settings( $name ) {
		return \ElementPack\VariationSwatches\Admin\Settings::instance()->get_option( $name );
	}
	public static function is_default( $value ) {
		return empty( $value ) || 'default' == $value;
	}

	public static function get_attribute_taxonomy( $attribute_name ) {
		$attribute_slug     = wc_attribute_taxonomy_slug( $attribute_name );
		$taxonomies         = wc_get_attribute_taxonomies();
		$attribute_taxonomy = wp_list_filter( $taxonomies, [ 'attribute_name' => $attribute_slug ] );
		$attribute_taxonomy = ! empty( $attribute_taxonomy ) ? array_shift( $attribute_taxonomy ) : null;

		return $attribute_taxonomy;
	}
	public static function attribute_is_swatches( $taxonomy, $context = 'view' ) {
		if ( ! is_object( $taxonomy ) || empty( $taxonomy->attribute_type ) ) {
			return false;
		}

		$is_swatches = self::is_swatches_type( $taxonomy->attribute_type );

		// If this is a check of admin edit area.
		if ( 'view' !== $context ) {
			return $is_swatches && 'button' !== $taxonomy->attribute_type;
		}

		return $is_swatches;
	}

	public static function get_image( $attachment_id, $size, $force_crop = false ) {
		if ( is_string( $size ) || ! $force_crop ) {
			return wp_get_attachment_image_src( $attachment_id, $size );
		}

		$width     = $size[0];
		$height    = $size[1];
		$image_src = wp_get_attachment_image_src( $attachment_id, 'full' );
		$file_path = get_attached_file( $attachment_id );

		if ( $file_path ) {
			$file_info = pathinfo( $file_path );
			$extension = '.' . $file_info['extension'];

			if ( $image_src[1] >= $width || $image_src[2] >= $height ) {
				$no_ext_path      = $file_info['dirname'] . '/' . $file_info['filename'];
				$cropped_img_path = $no_ext_path . '-' . $width . 'x' . $height . $extension;

				// the file is larger, check if the resized version already exists
				if ( file_exists( $cropped_img_path ) ) {
					$cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );

					return [
						0 => $cropped_img_url,
						1 => $width,
						2 => $height,
					];
				}
				
				$image_editor = wp_get_image_editor( $file_path );

				if ( is_wp_error( $image_editor ) || is_wp_error( $image_editor->resize( $width, $height, true ) ) ) {
					return false;
				}

				$new_img_path = $image_editor->generate_filename();

				if ( is_wp_error( $image_editor->save( $new_img_path ) ) ) {
					false;
				}

				if ( ! is_string( $new_img_path ) ) {
					return false;
				}

				$new_img_size = getimagesize( $new_img_path );
				$new_img      = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );

				return [
					0 => $new_img,
					1 => $new_img_size[0],
					2 => $new_img_size[1],
				];
			}
		}

		return false;
	}
}
