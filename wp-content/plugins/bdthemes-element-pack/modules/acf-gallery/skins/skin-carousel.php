<?php
namespace ElementPack\Modules\AcfGallery\Skins;

use Elementor\Skin_Base as Elementor_Skin_Base;
use ElementPack\Traits\Global_Widget_Controls;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Carousel extends Elementor_Skin_Base {
	use Global_Widget_Controls;
	
	public function get_id() {
		return 'bdt-carousel';
	}

	public function get_title() {
		return __( 'Carousel', 'bdthemes-element-pack' );
	}

	public function render_gallery_image($settings, $images) {

		$this->parent->add_render_attribute('advanced-image-gallery-item', 'class', ['bdt-ep-advanced-image-gallery-item', 'bdt-transition-toggle']);

		$this->parent->add_render_attribute('advanced-image-gallery-inner', 'class', 'bdt-ep-advanced-image-gallery-inner bdt-image-mask');
		
		if ($settings['tilt_show']) {
			$this->parent->add_render_attribute('advanced-image-gallery-inner', 'data-tilt', '');
		}

		foreach ( $images as $index => $image ) : ?>
				
			<div <?php $this->parent->print_render_attribute_string( 'advanced-image-gallery-item' ); ?>>
				<div <?php $this->parent->print_render_attribute_string( 'advanced-image-gallery-inner' ); ?>>
					<?php
					$this->parent->render_thumbnail($image);
					if ($settings['show_lightbox'] or $settings['show_caption'] )  :
						$this->parent->render_overlay($image);
					endif;
					?>
				</div>

				<?php if ($settings['show_caption'] and 'yes' == $settings['caption_all_time'])  : ?>
					<?php $this->parent->render_caption($image); ?>
				<?php endif; ?>
			</div>

		<?php endforeach;
	}

	public function render() {
		$settings = $this->parent->get_settings_for_display();
		$id       = $this->parent->get_id();

		// ACF - Gallery fields.
		$images = get_field( $settings['field'] );
		if (empty($images)) {
			return;
		}

		$this->render_aig_skin_carousel_header(); // Global header from trait.
		$this->render_gallery_image($settings, $images);
		$this->render_aig_skin_carousel_footer($settings, $images); // Global header from trait.
	}
}

