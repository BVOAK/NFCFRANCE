<?php
namespace ElementPack\Modules\Member\Skins;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;
use ElementPack\Base\Module_Base;

use Elementor\Skin_Base as Elementor_Skin_Base;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class Skin_Phaedra extends Elementor_Skin_Base {
	protected function _register_controls_actions() {
		parent::_register_controls_actions();

		add_action( 'elementor/element/bdt-member/section_style/before_section_start', [ $this, 'register_phaedra_style_controls' ] );

	}

	public function get_id() {
		return 'bdt-phaedra';
	}

	public function get_title() {
		return esc_html__( 'Phaedra', 'bdthemes-element-pack' );
	}

	public function register_phaedra_style_controls( Module_Base $widget ) {
		$this->parent = $widget;

		$this->start_controls_section(
			'section_style_phaedra',
			[ 
				'label' => __( 'Phaedra', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'phaedra_overlay_color',
			[ 
				'label'     => __( 'Overlay Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .bdt-member .bdt-member-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render() {
		$phaedra_id = 'phaedra' . $this->parent->get_id();
		$settings   = $this->parent->get_settings_for_display();

		$image_mask = $settings['image_mask_popover'] == 'yes' ? ' bdt-image-mask' : '';
		$this->parent->add_render_attribute( 'skin-phaedra', 'class', 'bdt-member skin-phaedra bdt-transition-toggle' . $image_mask );

		if ( ( $settings['member_alternative_photo'] ) and ( ! empty ( $settings['alternative_photo']['url'] ) ) ) {
			$this->parent->add_render_attribute( 'skin-phaedra', 'class', [ 'bdt-position-relative', 'bdt-overflow-hidden', 'bdt-transition-toggle' ] );
			$this->parent->add_render_attribute( 'skin-phaedra', 'bdt-toggle', 'target: > div > .bdt-member-photo-flip; mode: hover; animation: bdt-animation-fade; queued: true; duration: 300;' );
		}

		if ( ! isset ( $settings['social_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['social_icon'] = 'fab fa-facebook-f';
		}

		?>
		<div <?php $this->parent->print_render_attribute_string( 'skin-phaedra' ); ?>>
			<?php

			if ( ! empty ( $settings['photo']['url'] ) ) :
				$photo_hover_animation = ( '' != $settings['photo_hover_animation'] ) ? ' bdt-transition-scale-' . $settings['photo_hover_animation'] : ''; ?>

				<div class="bdt-member-photo-wrapper">

					<?php if ( ( $settings['member_alternative_photo'] ) and ( ! empty ( $settings['alternative_photo']['url'] ) ) ) : ?>
						<div class="bdt-member-photo-flip bdt-position-absolute bdt-position-z-index">
							<?php echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $settings, 'alternative_photo' ) ); ?>
						</div>
					<?php endif; ?>

					<div class="bdt-member-photo">
						<div class="<?php echo esc_attr( $photo_hover_animation ); ?>">
							<?php echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $settings, 'photo' ) ); ?>
						</div>
					</div>

				</div>

			<?php endif; ?>

			<div class="bdt-member-overlay bdt-overlay-default bdt-position-cover bdt-transition-fade bdt-position-z-index">
				<div class="bdt-member-desc bdt-position-center bdt-text-center">
					<div class="bdt-member-content bdt-transition-slide-top-small">
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
					</div>

					<?php $this->parent->render_social_icons('bdt-transition-slide-bottom-small'); ?>

				</div>
			</div>
		</div>
		<?php
	}
}

