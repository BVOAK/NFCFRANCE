<?php

/**
 * Created a new card when woocommerce order is processing
 *
 * @param int $order_id Order ID
 */
function gtmi_vcard_new($order_id)
{
    if (!function_exists( 'wc_get_order')) {
        return;
    }
    $order = wc_get_order( $order_id);
    if (!$order || ($order->get_status() !== 'processing' && $order->get_status() !== 'completed')) {
        return;
    }
    // Avoid duplication
    $vcard_id = $order->get_meta( '_gtmi_vcard_vcard_id');
    if ($vcard_id) {
        error_log( "GTMI_VCARD: virtual card is already created for the order $order_id with vcard ID: $vcard_id");
        return;
    }
    // Get data
    $customer_id = $order->get_customer_id();
    $post_title = sprintf(
        __('Virtual card for %s %s (Order #%d)', 'gtmi_vcard'),
        $order->get_billing_first_name(),
        $order->get_billing_last_name(),
        $order_id
    );

    $virtual_card_args = [
        'post_title' => wp_strip_all_tags( $post_title),
        'post_status' => 'publish',
        'post_type' => 'virtual_card',
        'post_author' => $customer_id ?: 1, // current customer or first admin
    ];
    // Create
    $new_vcard_id = wp_insert_post( $virtual_card_args);
    if (is_wp_error( $new_vcard_id)) {
        error_log( 'GTMI_VCARD: Error failed to create a new virtual card ' . $new_vcard_id->get_error_message());
        return;
    }

    // fill ACF fields
    gtmi_vcard_fill_acf( $order,  $new_vcard_id);

    // Associated order to the new virtual card with meta data
    $order->add_meta_data( '_gtmi_vcard_vcard_id',  $new_vcard_id,  true);
    $order->save();

    error_log( "GTMI_VCARD: virtual card ID $new_vcard_id created for the order $order_id");
}

function gtmi_vcard_fill_acf($order, $post_id): void
{
    $order_id = $order->get_id(); // Méthode correcte WooCommerce
    
    update_field( 'firstname',  $order->get_billing_first_name(),  $post_id);
    update_field( 'lastname',  $order->get_billing_last_name(),  $post_id);
    update_field( 'email',  $order->get_billing_email(),  $post_id);
    update_field( 'society',  $order->get_billing_company(),  $post_id);
    update_field( 'mobile',  $order->get_billing_phone(),  $post_id);
    update_field( 'address',  $order->get_billing_address_1(),  $post_id);
    update_field( 'additional',  $order->get_billing_address_2(),  $post_id);
    update_field( 'postcode',  $order->get_billing_postcode(),  $post_id);
    update_field( 'city',  $order->get_billing_city(),  $post_id);
    update_field( 'country',  $order->get_billing_country(),  $post_id);
    
    // LIGNE CORRIGÉE
    update_field( 'order',  $order->get_id(),  $post_id);
    
    update_field( 'card_status',  $order->get_status() ?? 'processing',  $post_id);
    update_field( 'url',  esc_url( get_permalink( $post_id)),  $post_id);
    
    // Debug pour vérifier
    error_log("GTMI_VCard: Order ID $order_id saved for vCard $post_id");
}

