<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
		<title><?php echo wp_get_document_title(); ?></title>
	<?php endif; ?>
	<?php wp_head();
	if(defined('UICORE_LIBRARY_MODE') && \UICORE_LIBRARY_MODE){
		include(get_stylesheet_directory() . '/settings.php');
	}
	?>
	<?php
	//Megamenu Custom Width;
		$settings = get_post_meta(get_the_ID(), 'tb_settings', true);
		if(isset($settings['width']) && $settings['width'] === 'custom'){
	?>
	<style id='megamenu-custom-width'>
	.elementor-edit-mode{
		max-width: <?php echo $settings['widthCustom']; ?>px;
		margin: 0 auto;
	}
	</style>
	<?php }

	?>
</head>
<body <?php body_class('ui-custom-lib-class'); ?>>
	<div class="uicore-body-content">
		<?php

		if (class_exists('\Elementor\Plugin')) {
			\Elementor\Plugin::$instance->modules_manager->get_modules( 'page-templates' )->print_content();
		} else {
			// Fallback for when Elementor is not active
			if (have_posts()) :
				while (have_posts()) : the_post();
					the_content();
				endwhile;
			endif;
		}
		
		wp_footer();
		?>	
	</div>
	</body>
</html>
