<?php

namespace UiCoreElements\Controls;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Base;

defined('ABSPATH') || exit();

/**
 * Widgets Control Extender
 */
class Post_Filter extends Group_Control_Base
{
    protected static $fields;

    public static function get_type()
    {
        return 'uicore-posts-filter';
    }

    public static function on_export_remove_setting_from_element($element, $control_id)
    {
        unset($element['settings'][$control_id . '_posts_ids']);
        unset($element['settings'][$control_id . '_authors']);

        foreach (self::get_post_types() as $post_type => $label) {
            $taxonomy_filter_args = [
                'show_in_nav_menus' => true,
                'object_type' => [$post_type],
            ];

            $taxonomies = get_taxonomies($taxonomy_filter_args, 'objects');

            foreach ($taxonomies as $taxonomy => $object) {
                unset($element['settings'][$control_id . '_' . $taxonomy . '_ids']);
            }
        }

        return $element;
    }

    protected function init_fields()
    {

        //Extra
        require_once UICORE_ELEMENTS_INCLUDES . '/controls/class-query.php';

        $fields = [];

        $fields['post_type'] = [
            'label' => __('Source', 'uicore-elements'),
            'type' => Controls_Manager::SELECT,
        ];

        return $fields;
    }

    /**
     *  Prepare the fields options for the control.
     *
     * @param array $fields The fields options.
     * @param bool $only_products If true, the 'Source' will offer only Woocommerce usefull options.
     */
    protected function prepare_fields($fields, $only_products = false)
    {

        $args = $this->get_args();

        $post_types = self::get_post_types($args, $only_products);

        $post_types_options = $post_types;


        $fields['post_type']['options'] = $post_types_options;

        $fields['post_type']['default'] = key($post_types);

        $taxonomy_filter_args = [
            'show_in_nav_menus' => true,
        ];

        if (!empty($args['post_type'])) {
            $taxonomy_filter_args['object_type'] = [$args['post_type']];
        }

        $taxonomies = get_taxonomies($taxonomy_filter_args, 'objects');

        foreach ($taxonomies as $taxonomy => $object) {
            $taxonomy_args = [
                'label' => $object->label,
                'type' => 'elements_query',
                'label_block' => true,
                'multiple' => true,
                'object_type' => $taxonomy,
                'options' => [],
                'condition' => [
                    'post_type' => $object->object_type,
                ],
            ];

            $options = [];

            $taxonomy_args['type'] = Controls_Manager::SELECT2;

            $terms = get_terms([
                'taxonomy' => $object->name,
                'hide_empty' => false,
            ]);

            foreach ($terms as $term) {
                $options[$term->term_id] = $term->name;
            }

            $taxonomy_args['options'] = $options;

            $fields[$taxonomy . '_ids'] = $taxonomy_args;
        }

        return parent::prepare_fields($fields);
    }

    private static function get_post_types($args = [], $only_products = false)
    {

        if ($only_products) {
            return [
                'product' => esc_html__('Custom', 'uicore-elements'),
                'current' => esc_html__('Current', 'uicore-elements'),
                'related' => esc_html__('Related', 'uicore-elements')
            ];
        }

        $post_type_args = [
            'show_in_nav_menus' => true,
        ];

        if (!empty($args['post_type'])) {
            $post_type_args['name'] = $args['post_type'];
        }

        $_post_types = get_post_types($post_type_args, 'objects');

        $post_types = [];

        foreach ($_post_types as $post_type => $object) {
            $post_types[$post_type] = $object->label;
        }

        unset($post_types['page']);
        unset($post_types['e-landing-page']);
        unset($post_types['uicore-tb']);

        if (!$only_products) {
            unset($post_types['product']);
        }

        $post_types['current'] = esc_html__('Current', 'uicore-elements');
        $post_types['related'] = esc_html__('Related', 'uicore-elements');

        return $post_types;
    }

    protected function get_default_options()
    {
        return [
            'popover' => false,
        ];
    }
}
\Elementor\Plugin::$instance->controls_manager->add_group_control('uicore-posts-filter', new Post_Filter());
