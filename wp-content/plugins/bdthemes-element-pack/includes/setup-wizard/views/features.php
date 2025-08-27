<?php
/**
 * Integration Step
 */

namespace ElementPack\SetupWizard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$widget_map     = \ElementPack\Includes\Setup_Wizard::get_widget_map();
$active_modules = get_option( 'element_pack_active_modules', array() );


?>

<div class="bdt-wizard-step bdt-setup-wizard-features" data-step="features">
	<!-- <h2>Choose your features</h2>
	<p>You may enable the widgets and extensions you need for your current project while keeping others turned off.</p> -->
	<form method="post" action="admin-ajax.php?action=element_pack_settings_save" id="ep_setup_wizard_modules">
		<input type="hidden" name="_wp_http_referer" value="/wp-admin/admin.php?page=element_pack_options">
		<input type="hidden" name="id" value="element_pack_active_modules">
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'element-pack-settings-save-nonce' ) ); ?>">
		<input type="hidden" name="action" value="element_pack_settings_save">

		<div class="bdt-features-list">
			<div class="widget-filter bdt-flex bdt-flex-wrap bdt-flex-between bdt-flex-middle">
				<div class="category-dropdown">
					<label for="category-select"><?php esc_html_e('Filter by:', 'bdthemes-element-pack'); ?></label>
					<select id="category-select">
						<option value="all"><?php esc_html_e('All', 'bdthemes-element-pack'); ?></option>
						<option value="new"><?php esc_html_e('New', 'bdthemes-element-pack'); ?></option>
						<option value="post"><?php esc_html_e('Post', 'bdthemes-element-pack'); ?></option>
						<option value="custom"><?php esc_html_e('Custom', 'bdthemes-element-pack'); ?></option>
						<option value="gallery"><?php esc_html_e('Gallery', 'bdthemes-element-pack'); ?></option>
						<option value="slider"><?php esc_html_e('Slider', 'bdthemes-element-pack'); ?></option>
						<option value="carousel"><?php esc_html_e('Carousel', 'bdthemes-element-pack'); ?></option>
						<option value="template-builder"><?php esc_html_e('Template Builder', 'bdthemes-element-pack'); ?></option>
						<option value="others"><?php esc_html_e('Others', 'bdthemes-element-pack'); ?></option>
					</select>
				</div>
				<div class="input-btn-wrap bdt-flex bdt-flex-wrap bdt-flex-between">
					<input type="text" placeholder="<?php esc_attr_e('Search widgets...', 'bdthemes-element-pack'); ?>" class="widget-search" value="">
					<div class="bulk-action-buttons bdt-flex">
						<button class="bulk-action activate"><?php esc_html_e('Activate All', 'bdthemes-element-pack'); ?></button>
						<button class="bulk-action deactivate"><?php esc_html_e('Deactivate All', 'bdthemes-element-pack'); ?></button>
					</div>
				</div>
			</div>
			
			<div class="widget-list-container">
				<ul class="widget-list">
					<?php foreach ( $widget_map as $widget ) : ?>
						<?php
						$is_checked = isset( $active_modules[ $widget['name'] ] ) && 'on' === $active_modules[ $widget['name'] ] ? 'checked' : '';
						?>
						<li class="<?php echo esc_attr( $widget['widget_type'] ); ?>"
							data-type="<?php echo isset( $widget['content_type'] ) ? esc_attr( $widget['content_type'] ) : ''; ?>"
							data-label="<?php echo esc_attr( strtolower( $widget['label'] ) ); ?>">
							<div class="widget-item-clickable bdt-flex bdt-flex-middle bdt-flex-between">
								<span class="bdt-flex"><?php echo esc_html( $widget['label'] ); ?></span>
								<label class="switch">
									<input type="hidden" name="element_pack_active_modules[<?php echo esc_attr( $widget['name'] ); ?>]" value="off">
									<input type="checkbox" name="element_pack_active_modules[<?php echo esc_attr( $widget['name'] ); ?>]" <?php echo esc_html( $is_checked ); ?> value="on" class="checkbox" id="bdt_ep_element_pack_active_modules[<?php echo esc_attr( $widget['name'] ); ?>]">
									<span class="slider"></span>
								</label>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		
		<div class="wizard-navigation bdt-margin-top">
			<button class="bdt-button bdt-button-primary element-pack-settings-save-btn" type="submit" id="save-and-continue">
				<?php esc_html_e('Save and Continue', 'bdthemes-element-pack'); ?>
			</button>
			<div class="bdt-close-button bdt-margin-left bdt-wizard-next" data-step="integration"><?php esc_html_e('Skip', 'bdthemes-element-pack'); ?></div>
		</div>
	</form>

	<div class="bdt-wizard-navigation">
		<button class="bdt-button bdt-button-secondary bdt-wizard-prev" data-step="welcome">
			<span><i class="dashicons dashicons-arrow-left-alt"></i></span>
			<?php esc_html_e( 'Previous Step', 'bdthemes-element-pack' ); ?>
		</button>
	</div>
</div>

