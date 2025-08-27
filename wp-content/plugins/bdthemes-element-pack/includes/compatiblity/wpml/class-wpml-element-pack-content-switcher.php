<?php
namespace ElementPack\Includes;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Class WPML_ElementPack_Content_Switcher
 */
class WPML_ElementPack_Content_Switcher extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'switcher_items';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'title',
			'content',
		];
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch( $field ) {
			case 'title':
				return esc_html__( 'Content Switcher: Title', 'bdthemes-element-pack' );

			case 'content':
				return esc_html__( 'Content Switcher: Content', 'bdthemes-element-pack' );

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

			case 'content':
				return 'AREA';

			default:
				return '';
		}
	}
} 