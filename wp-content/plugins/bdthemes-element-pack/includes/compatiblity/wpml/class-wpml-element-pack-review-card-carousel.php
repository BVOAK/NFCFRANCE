<?php
namespace ElementPack\Includes;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class WPML_ElementPack_Review_Card_Carousel
 */
class WPML_ElementPack_Review_Card_Carousel extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'review_items';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return array( 'reviewer_name', 'reviewer_job_title', 'review_text' );
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch( $field ) {
			case 'reviewer_name':
				return esc_html__( 'Reviewer Name', 'bdthemes-element-pack' );

			case 'reviewer_job_title':
				return esc_html__( 'Job Title', 'bdthemes-element-pack' );

			case 'review_text':
				return esc_html__( 'Review Text', 'bdthemes-element-pack' );

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
			case 'reviewer_name':
				return 'LINE';

			case 'reviewer_job_title':
				return 'LINE';

			case 'review_text':
				return 'AREA';

			default:
				return '';
		}
	}

} 