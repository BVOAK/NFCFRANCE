<?php

namespace ElementPack\Includes;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WPML_ElementPack_Advanced_Calculator extends WPML_Module_With_Items {

	public function get_items_field() {
		return 'form_fields';
	}

	public function get_fields() {
		return array( 'field_label', 'placeholder', 'field_options', 'field_value' );
	}

	protected function get_title( $field ) {
		switch ( $field ) {
			case 'field_label':
				return esc_html__( 'Field Label', 'bdthemes-element-pack' );

			case 'placeholder':
				return esc_html__( 'Placeholder', 'bdthemes-element-pack' );

			case 'field_options':
				return esc_html__( 'Options', 'bdthemes-element-pack' );

			case 'field_value':
				return esc_html__( 'Default Value', 'bdthemes-element-pack' );

			default:
				return '';
		}
	}

	protected function get_editor_type( $field ) {
		switch ( $field ) {
			case 'field_label':
				return 'LINE';

			case 'placeholder':
				return 'LINE';

			case 'field_options':
				return 'AREA';

			case 'field_value':
				return 'LINE';

			default:
				return '';
		}
	}
} 