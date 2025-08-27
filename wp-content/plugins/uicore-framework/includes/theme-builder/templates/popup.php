<?php

use UiCore\Settings;
use UiCore\ThemeBuilder\Common;

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
</head>
<body <?php body_class('ui-custom-lib-class'); ?>>
	<div class=".uicore-body-content">
		<iframe scrolling="no" src="<?php echo get_site_url(); ?>?ui-popup-preview=true" frameborder="0" class="ui-popup-background"></iframe>

		<?php
		ob_start();
		the_content();
		$content = ob_get_clean();

		Common::popup_markup($content, get_the_ID());

		wp_footer();
		?>
	</div>
</body>
</html>