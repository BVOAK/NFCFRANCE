<?php

namespace ElementPack\Includes;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WPML_ElementPack_Brand_Grid extends WPML_Module_With_Items {

	public function get_items_field() {
		return 'brand_items';
	}

	public function get_fields() {
		return array('brand_name');
	}

	protected function get_title( $field ) {
		switch ( $field ) {
			case 'brand_name':
				return esc_html__( 'Brand Name', 'bdthemes-element-pack' );

			default:
				return '';
		}
	}

	protected function get_editor_type( $field ) {
		switch ( $field ) {
			case 'brand_name':
				return 'LINE';
				
			default:
				return '';
		}
	}
} 