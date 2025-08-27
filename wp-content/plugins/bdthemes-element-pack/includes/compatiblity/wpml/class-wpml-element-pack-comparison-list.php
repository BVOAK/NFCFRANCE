<?php
namespace ElementPack\Includes;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Class WPML_ElementPack_Comparison_List
 */
class WPML_ElementPack_Comparison_List extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return ['comparison_header_list', 'comparison_list'];
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'header_title',
			'header_sub_title',
			'header_button_text',
			'title',
			'description',
		];
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch( $field ) {
			case 'header_title':
				return esc_html__( 'Header Title', 'bdthemes-element-pack' );

			case 'header_sub_title':
				return esc_html__( 'Header Additional Text', 'bdthemes-element-pack' );

			case 'header_button_text':
				return esc_html__( 'Header Button Text', 'bdthemes-element-pack' );

			case 'title':
				return esc_html__( 'Feature Title', 'bdthemes-element-pack' );

			case 'description':
				return esc_html__( 'Feature Description', 'bdthemes-element-pack' );

			default:
				return '';
		}
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		switch( $field ) {
			case 'header_title':
			case 'header_button_text':
			case 'title':
				return 'LINE';

			case 'header_sub_title':
			case 'description':
				return 'AREA';

			default:
				return '';
		}
	}
} 