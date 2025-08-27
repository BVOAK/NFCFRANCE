<?php
namespace ElementPack\Modules\UserRegister\Skins;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;

use Elementor\Skin_Base as Elementor_Skin_Base;
use ElementPack\Element_Pack_Loader;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Modal extends Elementor_Skin_Base {

	public function get_id() {
		return 'bdt-modal';
	}

	public function get_title() {
		return __( 'Modal', 'bdthemes-element-pack' );
	}

	public function render() {
		$settings    = $this->parent->get_settings_for_display();
		$id          = 'modal' . $this->parent->get_id();
		$current_url = remove_query_arg( 'fake_arg' );
		$button_size = $settings['modal_button_size'];
		$button_animation = $settings['modal_button_hover_animation'];

		$this->parent->add_render_attribute(
			[
				'modal-button' => [
					'class' => [
						'elementor-button',
						'bdt-button-modal',
						'elementor-size-' . esc_attr($button_size),
						$button_animation ? 'elementor-animation-' . esc_attr($button_animation) : ''
					],
					'href' => wp_logout_url( $current_url )
				]
			]
		);

		if ( is_user_logged_in() && ! Element_Pack_Loader::elementor()->editor->is_edit_mode() ) {
			if ( $settings['show_logged_in_message'] ) {

					$this->parent->add_render_attribute(
						[
							'user_register' => [
								'class' => 'bdt-user-register bdt-user-register-skin-dropdown',
							]
						]
					);
					if (isset($settings['password_strength']) && 'yes' == $settings['password_strength']) {
						$this->parent->add_render_attribute(
							[
								'user_register' => [
									'data-settings' => [
										wp_json_encode(
											array_filter([
												"id"                  => 'bdt-user-register' . $this->parent->get_id(),
												"passStrength"    => true,
												"forceStrongPass" => 'yes' == $settings['force_strong_password']  ? true : false,
											])
										),
									],
								],
							]
						);
					}

				?>
				<div id="<?php echo esc_attr($id); ?>" <?php $this->parent->print_render_attribute_string('user_register'); ?>>
					<a <?php $this->parent->print_render_attribute_string( 'modal-button' ); ?>>
						<?php $this->render_text(); ?>
					</a>
				</div>
				<?php
			}

			return;
		}

		$this->parent->form_fields_render_attributes();

		$this->parent->add_render_attribute(
			[
				'modal-button-settings' => [
					'class' => [
						'elementor-button',
						'bdt-button-modal',
						'elementor-size-' . esc_attr($button_size),
						$button_animation ? 'elementor-animation-' . esc_attr($button_animation) : ''
					],
					'href'       => 'javascript:void(0)',
					'data-bdt-toggle' => 'target: #' . esc_attr($id)
				]
			]
		);

			$this->parent->add_render_attribute(
				[
					'user_register' => [
						'class' => 'bdt-user-register bdt-user-register-skin-modal',
					]
				]
			);
			if (isset($settings['password_strength']) && 'yes' == $settings['password_strength']) {
				$this->parent->add_render_attribute(
					[
						'user_register' => [
							'data-settings' => [
								wp_json_encode(
									array_filter([
										"id"                  => 'bdt-user-register' . $this->parent->get_id(),
										"passStrength"    => true,
										"forceStrongPass" => 'yes' == $settings['force_strong_password']  ? true : false,
									])
								),
							],
						],
					]
				);
			}

		?>
		<div <?php $this->parent->print_render_attribute_string('user_register'); ?>>

			<a <?php $this->parent->print_render_attribute_string( 'modal-button-settings' ); ?>>
				<?php $this->render_text(); ?>
			</a>
			<div id="<?php echo esc_attr($id); ?>" class="bdt-flex-top bdt-user-register-modal bdt-modal-<?php echo esc_attr($settings['modal_custom_width']); ?>" data-bdt-modal>
				<div class="bdt-modal-dialog bdt-margin-auto-vertical">
					<?php if ($settings['modal_close_button']) : ?>
						<button class="bdt-modal-close-default" type="button" data-bdt-close></button>
					<?php endif; ?>
					<?php if ($settings['modal_header']) : ?>
					<div class="bdt-modal-header">
			            <h2 class="bdt-modal-title"><span class="ep-icon-user-circle-o"></span> <?php esc_html_e('User Registration', 'bdthemes-element-pack'); ?></h2>
			        </div>
					<?php endif; ?>
					<div class="elementor-form-fields-wrapper bdt-modal-body">
						<?php $this->parent->user_register_form(); ?>
					</div>

                    <div class="bdt-recaptcha-text bdt-text-center">
                        This site is protected by reCAPTCHA and the Google <br class="bdt-visible@s">
                        <a href="https://policies.google.com/privacy">Privacy Policy</a> and
                        <a href="https://policies.google.com/terms">Terms of Service</a> apply.
                    </div>
				</div>
    
                
			</div>
		</div>
		<?php

	}

	protected function render_text() {		
		$settings = $this->parent->get_settings_for_display();

		$this->parent->add_render_attribute('button-icon', 'class', ['bdt-modal-button-icon', 'bdt-flex-align-' . esc_attr($settings['modal_button_icon_align'])]);

		if ( is_user_logged_in() && ! Element_Pack_Loader::elementor()->editor->is_edit_mode() ) {
			$button_text = esc_html__( 'Logout', 'bdthemes-element-pack' );
		} else {
			$button_text = $settings['modal_button_text'];
		}

		$modal_button_icon = $settings['user_register_modal_icon'];

		if ( ! isset( $settings['modal_button_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['modal_button_icon'] = 'fas fa-user';
		}

		$migrated  = isset( $settings['__fa4_migrated']['user_register_modal_icon'] );
		$is_new    = empty( $settings['modal_button_icon'] ) && Icons_Manager::is_migration_allowed();
		
		?>
		<span class="elementor-button-content-wrapper">
			<?php if ( ! empty( $modal_button_icon['value'] ) ) : ?>
				<span <?php $this->parent->print_render_attribute_string('button-icon'); ?>>

					<?php if ( $is_new || $migrated ) :
						Icons_Manager::render_icon( (array) $modal_button_icon, [ 'aria-hidden' => 'true', 'class' => 'fa-fw' ] );
					else : ?>
						<i class="<?php echo esc_attr( $settings['modal_button_icon'] ); ?>" aria-hidden="true"></i>
					<?php endif; ?>

				</span>
			<?php else : ?>
				<?php $this->parent->add_render_attribute('button-icon', 'class', [ 'bdt-hidden@l' ]); ?>
				<span <?php $this->parent->print_render_attribute_string('button-icon'); ?>>
					<i class="ep-icon-lock" aria-hidden="true"></i>
				</span>

			<?php endif; ?>

			<span class="elementor-button-text bdt-visible@l">
				<?php echo esc_html($button_text); ?>
			</span>
		</span>
		<?php
	}

}

