<?php

namespace ElementPack\Modules\Member\Skins;

use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;

use Elementor\Skin_Base as Elementor_Skin_Base;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class Skin_Band extends Elementor_Skin_Base {

	public function get_id() {
		return 'bdt-band';
	}

	public function get_title() {
		return __( 'Band', 'bdthemes-element-pack' );
	}

	public function render() {
		$settings = $this->parent->get_settings_for_display();

		if ( ! isset ( $settings['social_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['social_icon'] = 'fab fa-facebook-f';
		}

		$image_mask = $settings['image_mask_popover'] == 'yes' ? ' bdt-image-mask' : '';
		$this->parent->add_render_attribute( 'image-wrap', 'class', 'bdt-member-photo-wrapper' . $image_mask );

		?>

		<div class="bdt-member skin-band bdt-transition-toggle">
			<div class="bdt-member-item-wrapper">
				<?php if ( ! empty ( $settings['photo']['url'] ) ) :
					$photo_hover_animation = ( '' != $settings['photo_hover_animation'] ) ? ' bdt-transition-scale-' . $settings['photo_hover_animation'] : ''; ?>

					<div <?php $this->parent->print_render_attribute_string( 'image-wrap' ); ?>>

						<?php if ( ( $settings['member_alternative_photo'] ) and ( ! empty ( $settings['alternative_photo']['url'] ) ) ) : ?>
							<div class="bdt-position-relative bdt-overflow-hidden"
								bdt-toggle="target: > .bdt-member-photo-flip; mode: hover; animation: bdt-animation-fade; queued: true; duration: 300;">

								<div class="bdt-member-photo-flip bdt-position-absolute bdt-position-z-index">
									<?php echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $settings, 'alternative_photo' ) ); ?>
								</div>
							<?php endif; ?>

							<div class="bdt-member-photo">
								<div class="<?php echo esc_attr( $photo_hover_animation ); ?>">
									<?php echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $settings, 'photo' ) ); ?>
								</div>
							</div>

							<?php if ( ( $settings['member_alternative_photo'] ) and ( ! empty ( $settings['alternative_photo']['url'] ) ) ) : ?>
							</div>
						<?php endif; ?>

						<?php $this->parent->render_social_icons(''); ?>

					</div>
				<?php endif; ?>

				<div class="bdt-member-content">
					<?php if ( ! empty ( $settings['name'] ) ) : ?>
						<span class="bdt-member-name">
							<?php echo wp_kses( $settings['name'], element_pack_allow_tags( 'title' ) ); ?>
						</span>
					<?php endif; ?>
					<?php if ( ! empty ( $settings['role'] ) ) : ?>
						<span class="bdt-member-role">
							<?php echo wp_kses( $settings['role'], element_pack_allow_tags( 'title' ) ); ?>
						</span>
					<?php endif; ?>
					<?php if ( ! empty ( $settings['description_text'] ) ) : ?>
						<div class="bdt-member-text bdt-content-wrap">
							<?php echo wp_kses( $settings['description_text'], element_pack_allow_tags( 'text' ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
