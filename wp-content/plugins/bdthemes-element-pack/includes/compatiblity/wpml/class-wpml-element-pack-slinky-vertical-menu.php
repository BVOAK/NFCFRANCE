<?php
namespace ElementPack\Includes;

/**
 * Class WPML_ElementPack_Slinky_Vertical_Menu
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class WPML_ElementPack_Slinky_Vertical_Menu extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'menus';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return array(
			'menu_title'
		);
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title($field) {
		switch($field) {
			case 'menu_title':
				return esc_html__('Menu Title', 'bdthemes-element-pack');

			default:
				return '';
		}
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_editor_type($field) {
		switch($field) {
			case 'menu_title':
				return 'LINE';

			default:
				return '';
		}
	}
} 