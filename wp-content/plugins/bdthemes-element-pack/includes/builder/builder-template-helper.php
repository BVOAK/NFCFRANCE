<?php

namespace ElementPack\Includes\Builder;

class Builder_Template_Helper {

	public static function isTemplateEditMode() {

		if ( get_post_type() == Meta::POST_TYPE ) {
			return true;
		}

		if ( isset( $_REQUEST[ Meta::POST_TYPE ] ) ) {
			return true;
		}
	}

	public static function separator() {
		return '|';
	}

	public static function templates( $single = false ) {

		$themes_item = [ 
			'header' => esc_html__( 'Header', 'bdthemes-element-pack' ),
			'footer' => esc_html__( 'Footer', 'bdthemes-element-pack' ),
		];
		$postItem    = [ 
			'single'  => esc_html__( 'Single', 'bdthemes-element-pack' ),
			'archive' => esc_html__( 'Archive', 'bdthemes-element-pack' ),
			'category' => esc_html__('Category', 'bdthemes-element-pack'),
			'tag' => esc_html__('Tag', 'bdthemes-element-pack'),
			'author' => esc_html__('Author', 'bdthemes-element-pack'),
			'date'    => esc_html__( 'Date', 'bdthemes-element-pack' ),
		];
		$pageItem    = [ 
			'single' => esc_html__( 'Single', 'bdthemes-element-pack' ),
			'404'    => esc_html__( 'Error 404', 'bdthemes-element-pack' ),
			'search' => esc_html__( 'Search', 'bdthemes-element-pack' ),
		];

		$templates = [ 
			// 'product' => $shopItem,
			'post'   => $postItem,
			'page'   => $pageItem,
			'themes' => $themes_item,
		];

		if ( $single ) {
			$separator = static::separator();
			$return    = [];

			if ( is_array( $templates ) && ! empty( $templates ) ) {

				foreach ( $templates as $keys => $items ) {

					if ( is_array( $items ) ) {

						foreach ( $items as $itemKey => $item ) {
							$return[ "{$keys}{$separator}{$itemKey}" ] = $item;
						}
					}
				}
			}

			return apply_filters(
				'bdthemes_templates_builder_all_templates',
				$return
			);
		}

		return $templates;
	}

	public static function templateForSelectDropdown() {
		return static::templates();
	}

	public static function getTemplateByIndex( $index ) {
		$index     = trim( $index );
		$templates = static::templates( true );

		return array_key_exists( $index, $templates ) ? $templates[ $index ] : false;
	}

	public static function getTemplatePostTypeByIndex( $index ) {
		$index = trim( $index );

		if ( $item = explode( static::separator(), $index ) ) {
			return get_post_type_object( $item[0] );
		}
	}

	public static function is_elementor_active() {
		return did_action( 'elementor/loaded' );
	}

	public static function getTemplate( $slug, $postType = false ) {

		if ( ! $postType ) {
			$postType = get_post_type();
		}
        
		$separator       = static::separator();
		$template        = strtolower( "{$postType}{$separator}{$slug}" );
		$enabledTemplate = strtolower( Meta::TEMPLATE_ID . $template );

		return get_option( $enabledTemplate );
	}

	public static function getTemplateId( $templateType ) {
		$metaIndex = strtolower( Meta::TEMPLATE_ID . $templateType );
		return intval( get_option( $metaIndex ) );
	}

	public static function searchTemplateOptions( $pattern ) {
		global $wpdb;

		$like_pattern = '%' . $wpdb->esc_like( $pattern ) . '%';
		$query        = $wpdb->prepare(
			"SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE %s",
			$like_pattern
		);

		$results = $wpdb->get_results( $query );

		return $results;
	}
}
