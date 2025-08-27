<?php
require_once 'meta.php';
require_once 'builder-cpt.php';
require_once 'builder-integration.php';
require_once 'themes-hooks/themes/astra.php';
require_once 'themes-hooks/themes/bbtheme.php';
require_once 'themes-hooks/themes/generatepress.php';
require_once 'themes-hooks/themes/genesis.php';
require_once 'themes-hooks/themes/neve.php';
require_once 'themes-hooks/themes/oceanwp.php';
require_once 'themes-hooks/theme-support.php';
require_once 'themes-hooks/activator.php';

function bdt_templates_render_elementor_content( $content_id ) {

	$elementor_instance = \Elementor\Plugin::instance();
	$has_css            = false;

	/**
	 * CSS Print Method Internal and Exteral option support for Header and Footer Builder.
	 */
	if ( ( 'internal' === get_option( 'elementor_css_print_method' ) ) || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
		$has_css = true;
	}

	return $elementor_instance->frontend->get_builder_content_for_display( $content_id, $has_css );
}

function bdt_templates_get_singular_list( ) {
	$query_args = array(
		'post_status'    => 'publish',
		'posts_per_page' => 15,
		'post_type'      => 'any',
	);

	if ( isset( $_GET['ids'] ) ) {
		$ids                    = explode( ',', $_GET['ids'] );
		$query_args['post__in'] = $ids;
	}
	if ( isset( $_GET['s'] ) ) {
		$query_args['s'] = $_GET['s'];
	}

	$query   = new \WP_Query( $query_args );
	$options = array();
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) {
			$query->the_post();
			$options[] = array(
				'id'   => get_the_ID(),
				'text' => get_the_title(),
			);
		}
	endif;

	return array( 'results' => $options );
	wp_reset_postdata();
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'bdt/v1', '/get-singular-list', array(
		'methods'             => 'GET',
		'callback'            => 'bdt_templates_get_singular_list',
		'permission_callback' => '__return_true',
	) );
} );
