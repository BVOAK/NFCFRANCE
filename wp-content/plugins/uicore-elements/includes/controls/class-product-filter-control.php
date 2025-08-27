<?php

namespace UiCoreElements\Controls;

use UiCoreElements\Controls\Post_Filter;

defined('ABSPATH') || exit();

/**
 * Widgets Control Extender.
 *
 * Post_Filter class has pretty much everything we need. We only need to change the type name and also makes
 * sure prepare_fields(), from Post_Filter, will only print specific post types that are usefull for Woo.
 *
 * @since 1.0.11
 */

class Product_Filter extends Post_Filter
{
    public static function get_type()
    {
        return 'ui-e-product-filter';
    }

    protected function prepare_fields($fields, $only_products = true)
    {
        return parent::prepare_fields($fields, $only_products);
    }
}
\Elementor\Plugin::$instance->controls_manager->add_group_control('ui-e-product-filter', new Product_Filter());
