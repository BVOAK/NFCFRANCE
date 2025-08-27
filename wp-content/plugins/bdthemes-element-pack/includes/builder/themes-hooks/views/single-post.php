<?php


defined('ABSPATH') || exit;

get_header();



if (class_exists('Elementor\Plugin')) {
	$template = \ElementPack\Builder\Activator::template_ids();
	echo bdt_templates_render_elementor_content( $template[1] );
}


get_footer();
