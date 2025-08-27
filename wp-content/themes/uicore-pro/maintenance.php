<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package uicore-theme
 */

get_header();
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<section class="utility-page maintenance-page">
				<img src="<?php echo esc_url(get_template_directory_uri()) ?>/assets/img/bg-404.png" alt="<?php esc_attr_e('The site is currently down for maintenance', 'uicore-pro')?>" class="error-404-img">
				<h1 class="maintenance-title"><?php esc_html_e('The site is currently down for maintenance.', 'uicore-pro'); ?></h1>
				<p><?php esc_html_e('We apologise for any inconveniences caused.', 'uicore-pro'); ?></p>
			</section><!-- .utility-page -->
		</main><!-- #main -->
	</div><!-- #primary -->
<?php
get_footer();
