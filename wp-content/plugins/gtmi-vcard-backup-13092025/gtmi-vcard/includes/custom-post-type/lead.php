<?php
add_action('init', 'gtmi_vcard_new_custom_type_lead');
register_activation_hook(__FILE__, 'gtmi_vcard_flush_rewrites_lead');
add_action('admin_init', 'remove_default_fields_from_gtmi_vcard');


/**
 * Create a Custom Post Type lead
 */
function gtmi_vcard_new_custom_type_lead(): void
{
    $labels = [
        'name' => _x('Lead', 'Post Type General Name', 'gtmi-vcard'),
        'singular_name' => _x('Lead', 'Post Type Singular Name', 'gtmi-vcard'),
        'menu_name' => __('Leads (VCard)', 'gtmi-vcard'),
        'name_admin_bar' => __('Leads', 'gtmi-vcard'),
        'archives' => __('Archives of leads', 'gtmi-vcard'),
        'attributes' => __('Attributs of lead', 'gtmi-vcard'),
        'parent_item_colon' => __('Lead Parent :', 'gtmi-vcard'),
        'all_items' => __('All leads', 'gtmi-vcard'),
        'add_new_item' => __('Add a new lead', 'gtmi-vcard'),
        'add_new' => __('Add a new', 'gtmi-vcard'),
        'new_item' => __('New lead', 'gtmi-vcard'),
        'edit_item' => __('Edit the lead', 'gtmi-vcard'),
        'update_item' => __('Update the lead', 'gtmi-vcard'),
        'view_item' => __('See the lead', 'gtmi-vcard'),
        'view_items' => __('See leads', 'gtmi-vcard'),
        'search_items' => __('Search a lead', 'gtmi-vcard'),
        'not_found' => __('Lead not found', 'gtmi-vcard'),
        'not_found_in_trash' => __('None lead in the rubbish', 'gtmi-vcard'),
        'featured_image' => __('Profile picture', 'gtmi-vcard'),
        'insert_into_item' => __('Insert the lead', 'gtmi-vcard'),
        'uploaded_to_this_item' => __('Upload the lead', 'gtmi-vcard'),
        'items_list' => __('List of leads', 'gtmi-vcard'),
        'items_list_navigation' => __('Navigation inside leads', 'gtmi-vcard'),
        'filter_items_list' => __('Filter list of leads', 'gtmi-vcard'),
    ];
    $args = [
        'label' => __('Leads (VCard)', 'gtmi-vcard'),
        'description' => __('Leads following customer', 'gtmi-vcard'),
        'labels' => $labels,
        'supports' => ['custom-fields'],
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-groups',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'rewrite' => ['slug' => 'lead', 'with_front' => false], // custom URL
    ];
    register_post_type('lead', $args);
}

// Flush rules when the module is activated
function gtmi_vcard_flush_rewrites_lead()
{
    //Check lead custom type is saved before flushing
    gtmi_vcard_new_custom_type_lead();
    flush_rewrite_rules();
}

// Flush rules when the module is deactivated
function gtmi_vcard__deactivate_lead()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'gtmi_vcard_deactivate_lead');
function remove_default_fields_from_gtmi_vcard(): void
{
    global $pagenow;
    if (is_admin() && preg_match('@load-post.+@', $pagenow) && 'lead' === $query->get('post_type') && $query->is_main_query()) {
        remove_post_type_support('lead', 'title');
        remove_post_type_support('lead', 'editor');
    }
}