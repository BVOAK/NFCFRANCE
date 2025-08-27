<?php
add_action( hook_name: 'wp_enqueue_scripts', callback: 'gtmi_vcard_enqueue_lead_exchange' );
add_action( hook_name: 'admin_enqueue_scripts', callback: 'gtmi_vcard_enqueue_virtual_card_admin_script', priority: 1 );
add_action( hook_name: 'admin_enqueue_scripts', callback: 'gtmi_vcard_enqueue_statistics_admin_script', priority: 1 );

/**
 * Load JS script at front in order to add a new lead
 * @return void
 */
function gtmi_vcard_enqueue_lead_exchange(): void {
    if ( is_singular(post_types: 'virtual_card') ) {
        wp_enqueue_script(
            handle: 'gtmi_vcard_lead_front_script',
            src: plugin_dir_url(file: __DIR__) . '../assets/js/lead.js',
            deps: [],
            ver: '1.0.0',
            args: true
        );
        wp_localize_script(
            handle: 'gtmi_vcard_lead_front_script',
            object_name: 'gtmiVCardLeadExchange',
            l10n:[
                'restUrl' => esc_url_raw(url: rest_url(path: 'gtmi_vcard/v1/lead')),
                'nonce'   => wp_create_nonce(action: 'wp_rest') // API REST security
            ]
        );
    }
}

/**
 * Load JS only on edition page of custom post type virtual_card
 *
 * @param string $hook current hook page.
 */
function gtmi_vcard_enqueue_virtual_card_admin_script( $hook ): void {
    global $post_type;

    if ( ('post.php' == $hook || 'post-new.php' == $hook) && 'virtual_card' == $post_type ) {
        wp_enqueue_script(
            handle: 'gtmi_vcard-virtual-card-admin-script',
            src: plugin_dir_url( file: __DIR__ ) . '../assets/js/virtual_card.js',
            deps: [],
            ver: '1.0.0',
            args: true
        );
    }
}

/**
 * Load JS only on edition page of custom post type virtual_card
 *
 * @param string $hook current hook page.
 */
function gtmi_vcard_enqueue_statistics_admin_script( $hook ): void {
    global $post_type;

    if ( 'edit.php' == $hook && 'statistics' == $post_type ) {
        wp_enqueue_script(
            handle: 'gtmi_vcard-statistics-admin-script',
            src: plugin_dir_url( file: __DIR__ ) . '../assets/js/statistics.js',
            deps: [],
            ver: '1.0.0',
            args: true
        );
    }
}