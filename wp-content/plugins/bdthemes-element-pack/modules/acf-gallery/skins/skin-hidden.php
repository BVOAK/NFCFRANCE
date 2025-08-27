<?php
namespace ElementPack\Modules\AcfGallery\Skins;

use Elementor\Skin_Base as Elementor_Skin_Base;
use ElementPack\Traits\Global_Widget_Controls;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Hidden extends Elementor_Skin_Base {
	use Global_Widget_Controls;
	public function get_id() {
		return 'bdt-hidden';
	}

	public function get_title() {
		return __( 'Hidden', 'bdthemes-element-pack' );
	}

	public function render_gallery_image($settings, $images) {

		$this->parent->add_render_attribute('advanced-image-gallery-item', 'class', ['bdt-ep-advanced-image-gallery-item', 'bdt-transition-toggle']);
		$this->parent->add_render_attribute('advanced-image-gallery-inner', 'class', 'bdt-ep-advanced-image-gallery-inner');
		
		if ($settings['tilt_show']) {
			$this->parent->add_render_attribute('advanced-image-gallery-inner', 'data-tilt', '');
		}

		foreach ( $images as $index => $image ) :			
			$this->parent->link_only($image);
		endforeach;
	}

	public function render() {
		$settings = $this->parent->get_settings_for_display();

		// ACF - Gallery fields.
		$images = get_field( $settings['field'] );
		if (empty($images)) {
			return;
		}

		$this->render_aig_skin_hidden_header(); // Global function from trait	
		$this->render_gallery_image($settings, $images);
		$this->parent->render_footer();
	}
}

