<?php

namespace ElementPack\Includes;


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WPML_ElementPack_Icon_Mobile_Menu extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'menu_items';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'menu_text',
		];
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_title( $field ) {
		switch ( $field ) {
			case 'menu_text':
				return esc_html__( 'Icon Mobile Menu: Menu Text', 'bdthemes-element-pack' );
			default:
				return '';
		}
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		switch ( $field ) {
			case 'menu_text':
				return 'LINE';
			default:
				return '';
		}
	}
} 