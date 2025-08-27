<?php
namespace ElementPack\Includes;

/**
 * Class WPML_ElementPack_Static_Grid_Tab
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class WPML_ElementPack_Static_Grid_Tab extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'static_tabs_item';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return array(
			'title',
			'text',
			'readmore_text'
		);
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title($field) {
		switch($field) {
			case 'title':
				return esc_html__('Title', 'bdthemes-element-pack');

			case 'text':
				return esc_html__('Text', 'bdthemes-element-pack');

			case 'readmore_text':
				return esc_html__('Read More Text', 'bdthemes-element-pack');

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
			case 'title':
				return 'LINE';

			case 'readmore_text':
				return 'LINE';

			case 'text':
				return 'AREA';

			default:
				return '';
		}
	}
} 