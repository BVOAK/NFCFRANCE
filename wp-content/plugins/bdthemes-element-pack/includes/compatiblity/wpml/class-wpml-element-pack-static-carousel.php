<?php
namespace ElementPack\Includes;


if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Class WPML_ElementPack_Slider
 */
class WPML_ElementPack_Static_Carousel extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'carousel_items';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return array( 'title', 'sub_title', 'text' );
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch( $field ) {

			case 'title':
				return esc_html__( 'Title', 'bdthemes-element-pack' );

			case 'sub_title':
				return esc_html__( 'Sub Title', 'bdthemes-element-pack' );

			case 'text':
				return esc_html__( 'Text', 'bdthemes-element-pack' );

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
			case 'title':
				return 'LINE';

			case 'sub_title':
				return 'LINE';

			case 'text':
				return 'AREA';

			default:
				return '';
		}
	}

}
