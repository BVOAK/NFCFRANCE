<?php

use ElementPack\Includes\Builder\Builder_Integration;

defined('ABSPATH') || exit;

get_header();


if (class_exists('Elementor\Plugin')) {
    echo Elementor\Plugin::instance()->frontend->get_builder_content(Builder_Integration::instance()->current_template_id, false);
}


get_footer();
