<?php
namespace ElementPack\Includes;

/**
 * Class WPML_ElementPack_Image_Stack
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class WPML_ElementPack_Image_Stack extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'image_stack_items';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return array( 'tooltip_text' );
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch( $field ) {
			case 'tooltip_text':
				return esc_html__( 'Image Stack: Tooltip Text', 'bdthemes-element-pack' );

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
			case 'tooltip_text':
				return 'LINE';

			default:
				return '';
		}
	}
} 