<?php
namespace ElementPack\Modules\Member\Skins;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;

use Elementor\Skin_Base as Elementor_Skin_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Flip extends Elementor_Skin_Base {

	public function get_id() {
		return 'bdt-flip';
	}

	public function get_title() {
		return __( 'Flip', 'bdthemes-element-pack' );
	}

	public function render() {
		$calm_id  = 'flip' . $this->parent->get_id();
        $settings = $this->parent->get_settings_for_display();
        $alternative_image = '';

        $image_mask = $settings['image_mask_popover'] == 'yes' ? ' bdt-image-mask' : '';
		$this->parent->add_render_attribute( 'skin-flip', 'class', 'bdt-member skin-flip bdt-transition-toggle bdt-inline' . $image_mask );

		if ( ! isset( $settings['social_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['social_icon'] = 'fab fa-facebook-f';
        }
        
        $member_image = Group_Control_Image_Size::get_attachment_image_src( $settings['photo']['id'], 'thumbnail_size', $settings);
        if ( ! $member_image ) {
            $member_image = $settings['photo']['url'];
        }

        if ( 'yes' == $settings['member_alternative_photo'] ) {
            $alternative_image = Group_Control_Image_Size::get_attachment_image_src( $settings['alternative_photo']['id'], 'thumbnail_size', $settings);
            if ( ! $alternative_image ) {
                $alternative_image = $settings['alternative_photo']['url'];
            }
        }
        

		?>
		<div <?php $this->parent->print_render_attribute_string( 'skin-flip' ); ?>>

            <div class="bdt-skin-flip-layer bdt-skin-flip-front" style="background-image: url('<?php echo esc_url($member_image); ?>');">
				<div class="bdt-skin-flip-layer-overlay">
					<div class="bdt-skin-flip-layer-inner">
                        
                        <div class="bdt-member-content bdt-position-bottom-center">
                            <?php if ( ! empty( $settings['name'] ) ) : ?>
                                <span class="bdt-member-name"><?php echo wp_kses( $settings['name'], element_pack_allow_tags('title') ); ?></span>
                            <?php endif; ?>

                            <?php if ( ! empty( $settings['role'] ) ) : ?>
                                <span class="bdt-member-role"><?php echo wp_kses( $settings['role'], element_pack_allow_tags('title') ); ?></span>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>

            <div class="bdt-skin-flip-layer bdt-skin-flip-back" style="background-image: url('<?php echo esc_url($alternative_image); ?>');">
				<div class="bdt-skin-flip-layer-overlay">
					<div class="bdt-skin-flip-layer-inner">
                        
                        <?php $this->parent->render_social_icons('bdt-position-bottom-center'); ?>

                        <?php if ( ! empty( $settings['description_text'] ) ) : ?>
                        <div class="bdt-member-text bdt-position-center"><?php echo wp_kses( $settings['description_text'], element_pack_allow_tags('text') ); ?></div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

		</div>
		<?php
	}
}

