<?php
register_activation_hook(__FILE__, 'gtmi_vcard_flush_rewrites_card');
register_deactivation_hook(__FILE__, 'gtmi_vcard_deactivate_vcard');
add_action('init', 'gtmi_vcard_new_custom_type_virtual_card');

/**
 * Create a Custom Post Type virtual_card
 */
function gtmi_vcard_new_custom_type_virtual_card()
{
    $labels = [
        'name'                  => _x('Virtual cards', 'Post Type General Name', 'gtmi-vcard'),
        'singular_name'         => _x('Virtual card', 'Post Type Singular Name', 'gtmi-vcard'),
        'menu_name'             => __('Virtual cards', 'gtmi-vcard'),
        'name_admin_bar'        => __('Virtual card', 'gtmi-vcard'),
        'archives'              => __('Archives of virtual cards', 'gtmi-vcard'),
        'attributes'            => __('Attributs of virtual card', 'gtmi-vcard'),
        'parent_item_colon'     => __('Virtual card Parent :', 'gtmi-vcard'),
        'all_items'             => __('All virtual cards', 'gtmi-vcard'),
        'add_new_item'          => __('Add a new virtual card', 'gtmi-vcard'),
        'add_new'               => __('Add a new', 'gtmi-vcard'),
        'new_item'              => __('New virtual card', 'gtmi-vcard'),
        'edit_item'             => __('Update the virtual card', 'gtmi-vcard'),
        'update_item'           => __('Mettre à jour la Carte', 'gtmi-vcard'),
        'view_item'             => __('See the virtual card', 'gtmi-vcard'),
        'view_items'            => __('See virtual cards', 'gtmi-vcard'),
        'search_items'          => __('Search a virtual card', 'gtmi-vcard'),
        'not_found'             => __('Virtual card not found', 'gtmi-vcard'),
        'not_found_in_trash'    => __('None virtual card in the rubbish', 'gtmi-vcard'),
        'featured_image'        => __('Profile picture', 'gtmi-vcard'),
        'set_featured_image'    => __('Put your profile picture', 'gtmi-vcard'),
        'remove_featured_image' => __('Delete your profile picture', 'gtmi-vcard'),
        'use_featured_image'    => __('Use as profile picture', 'gtmi-vcard'),
        'insert_into_item'      => __('Insert into virtual card', 'gtmi-vcard'),
        'uploaded_to_this_item' => __('Upload to this virtual card', 'gtmi-vcard'),
        'items_list'            => __('List of virtual cards', 'gtmi-vcard'),
        'items_list_navigation' => __('Navigation inside virtual cards', 'gtmi-vcard'),
        'filter_items_list'     => __('Filter list of virtual cards', 'gtmi-vcard'),
    ];
    $args = [
        'label'                 => __('Virtual cards (VCard)', 'gtmi-vcard'),
        'description'           => __('Virtual cards with user and busisness info', 'gtmi-vcard'),
        'labels'                => $labels,
        'supports'              => ['custom-fields'],
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-id-alt',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'rewrite'               => ['slug' => 'vcard', 'with_front' => false ], // custom URL
    ];
    register_post_type('virtual_card', $args);
}

// Flush rules when the module is activated
function gtmi_vcard_flush_rewrites_vcard()
{
    //Check virtual_card custom type is saved before flushing
    gtmi_vcard_new_custom_type_virtual_card();
    flush_rewrite_rules();
}

// Flush rules when the module is deactivated
function gtmi_vcard__deactivate_vcard()
{
    flush_rewrite_rules();
}