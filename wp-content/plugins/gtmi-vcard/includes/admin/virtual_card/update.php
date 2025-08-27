<?php

add_action('admin_head', 'hide_permalink_editor_css');
add_action( 'init', 'remove_editor_from_specific_post_types' );
add_action( 'init', 'disable_permalink_editing_for_post_type', 999 );
add_action( 'init', 'disable_quick_edit_for_virtual_card', 10 );
function remove_editor_from_specific_post_types(): void {
    // Deactivate edition
    remove_post_type_support( 'post', 'editor' );
    // Deactivate edition page
    remove_post_type_support( 'page', 'editor' );
    // Deactivate edition of virtual card custom post type
    remove_post_type_support( 'virtual_card', 'editor' );
}
function hide_permalink_editor_css(): void {
    echo '
    <style type="text/css">
        #edit-slug-box {
            display: none !important;
        }
        #posts-filter .inline.hide-if-no-js,
        .components-panel__body.edit-post-post-permalink {
            display: none !important;
        }
        .acf-field input[value*="/vcard/"],
        .acf-field input[type=number] {
            pointer-events: none;
            background: lightgrey;
        }

    </style>';
}

function disable_permalink_editing_for_post_type(): void {
    remove_post_type_support( 'virtual_card', 'slug' );
}
function disable_quick_edit_for_virtual_card(): void {
    // Deactivate rapid update
    remove_post_type_support( 'virtual_card', 'excerpt' );
}
