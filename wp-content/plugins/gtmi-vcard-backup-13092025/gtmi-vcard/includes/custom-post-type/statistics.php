<?php

add_action(hook_name: 'init', callback: 'gtmi_vcard_new_custom_type_statistics');
register_deactivation_hook(file: __FILE__, callback: 'gtmi_vcard_deactivate_statistics');
register_activation_hook(file: __FILE__, callback: 'gtmi_vcard_flush_rewrites_statistics');

/**
 * Create a Custom Post Type Stats
 */
function gtmi_vcard_new_custom_type_statistics(): void
{
    $labels = [
        'name'                  => _x(text: 'Stats', context: 'Post Type General Name', domain: 'gtmi-vcard'),
        'singular_name'         => _x(text: 'Statistics', context: 'Post Type Singular Name', domain: 'gtmi-vcard'),
        'menu_name'             => __(text: 'Statistics (VCard)', domain: 'gtmi-vcard'),
        'name_admin_bar'        => __(text: 'Statistics', domain: 'gtmi-vcard'),
        'archives'              => __(text: 'Archives of statistics', domain: 'gtmi-vcard'),
        'attributes'            => __(text: 'Attributs of statistics', domain: 'gtmi-vcard'),
        'parent_item_colon'     => __(text: 'Statistics Parent :', domain: 'gtmi-vcard'),
        'all_items'             => __(text: 'All Statistics', domain: 'gtmi-vcard'),
        'add_new_item'          => __(text: 'Add a new statistics', domain: 'gtmi-vcard'),
        'add_new'               => __(text: 'Add a new', domain: 'gtmi-vcard'),
        'new_item'              => __(text: 'New statistics', domain: 'gtmi-vcard'),
        'edit_item'             => __(text: 'Edit the statistics', domain: 'gtmi-vcard'),
        'update_item'           => __(text: 'Update the statistics', domain: 'gtmi-vcard'),
        'view_item'             => __(text: 'See the statistics', domain: 'gtmi-vcard'),
        'view_items'            => __(text: 'See statistics', domain: 'gtmi-vcard'),
        'search_items'          => __(text: 'Search a statistics', domain: 'gtmi-vcard'),
        'not_found'             => __(text: 'Statistics not found', domain: 'gtmi-vcard'),
        'not_found_in_trash'    => __(text: 'None statistics in the rubbish', domain: 'gtmi-vcard'),
        'featured_image'        => __(text: 'Profile picture', domain: 'gtmi-vcard'),
        'insert_into_item'      => __(text: 'Insert the statistics', domain: 'gtmi-vcard'),
        'uploaded_to_this_item' => __(text: 'Upload the statistics', domain: 'gtmi-vcard'),
        'items_list'            => __(text: 'List of statistics', domain: 'gtmi-vcard'),
        'items_list_navigation' => __(text: 'Navigation inside statistics', domain: 'gtmi-vcard'),
        'filter_items_list'     => __(text: 'Filter list of statistics', domain: 'gtmi-vcard'),
    ];
    $args = [
        'label'                 => __(text: 'statistics (VCard)', domain: 'gtmi-vcard'),
        'description'           => __(text: 'statistics following customer', domain: 'gtmi-vcard'),
        'labels'                => $labels,
        'supports'              => [ 'custom-fields'],
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-chart-pie',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'rewrite'               => [ 'slug' => 'statistics', 'with_front' => false ],
        ];
    register_post_type(post_type: 'statistics', args: $args);
}

// Flush rules when the module is activated
function gtmi_vcard_flush_rewrites_statistics()
{
    //Check statistics custom type is saved before flushing
    gtmi_vcard_new_custom_type_statistics();
    flush_rewrite_rules();
}

// Flush rules when the module is deactivated
function gtmi_vcard__deactivate_statistics(): void
{
    flush_rewrite_rules();
}