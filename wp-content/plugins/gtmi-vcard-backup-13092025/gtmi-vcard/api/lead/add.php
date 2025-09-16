<?php
add_action( 'rest_api_init',  'gtmi_vcard_register_rest_routes_add_lead');
function gtmi_vcard_register_rest_routes_add_lead(): void
{
    register_rest_route( 'gtmi_vcard/v1',  '/lead',  [
        'methods' => 'POST',
        'callback' => 'gtmi_vcard_associated_lead_to_vcard',
        'permission_callback' => '__return_true',
        'args' => [
            'firstname' => [
                'required' => true,
                'type' => 'string',
                'description' => 'Firstname',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'lastname' => [
                'required' => true,
                'type' => 'string',
                'description' => 'Last name',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'email' => [
                'required' => true,
                'type' => 'string',
                'format' => 'email',
                'description' => 'Email',
                'sanitize_callback' => 'sanitize_email',
            ],
            'mobile' => [
                'required' => false,
                'type' => 'string',
                'description' => 'Phone number',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'society' => [
                'required' => false,
                'type' => 'string',
                'description' => 'Name of the lead society',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'linked_virtual_card' => [
                'required' => true,
                'type' => 'integer',
                'description' => 'ID of virtual card associated',
                'sanitize_callback' => 'absint',
            ],
            'post' => [
                'required' => false,
                'type' => 'string',
                'description' => 'Job title/position',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);
}

/**
 * Create lead via API REST
 *
 * @param WP_REST_Request $request object REST
 * @return WP_REST_Response response object REST
 */
function gtmi_vcard_associated_lead_to_vcard(WP_REST_Request $request): WP_REST_Response
{
    // Get data from request
    $firstname = $request->get_param( 'firstname');
    $lastname = $request->get_param( 'lastname');
    $email = $request->get_param( 'email');
    $mobile = $request->get_param( 'mobile');
    $society = $request->get_param( 'society');
    $virtual_card_id = $request->get_param( 'linked_virtual_card');
    $dt = date( 'Y-m-d H:i:s');
    $post = $request->get_param( 'post');

    $virtual_card_post = get_post( $virtual_card_id);
    if (!$virtual_card_post || 'virtual_card' !== $virtual_card_post->post_type) {
        return gtmi_vcard_api_response( 'ID of virtual card not found',  false,  404);
    }

    $lead_post_args = [
        'post_title' => "$firstname $lastname",
        'post_type' => 'lead',
        'post_status' => 'publish',
    ];

    $lead_id = wp_insert_post( $lead_post_args,  true);

    if (is_wp_error( $lead_id)) {
        return new WP_REST_Response( [
            'success' => false,
            'message' => 'Internal error server: ' . $lead_id->get_error_message(),// TODO replace by a standard message
        ],  500);
        error_log( 'GTMI_VCARD: '.$lead_id->get_error_message());
        return gtmi_vcard_api_response( 'Internal error server, look precision at error log file',  false,  400);
    }
    // Save ACF fields
    update_field( 'firstname', $firstname,  $lead_id);
    update_field( 'lastname',  $lastname,  $lead_id);
    update_field( 'email',  $email,  $lead_id);
    update_field( 'mobile',  $mobile,  $lead_id);
    update_field( 'society',  $society,  $lead_id);
    update_field( 'linked_virtual_card',  array($virtual_card_id),  $lead_id);
    update_field( 'contact_datetime',  $dt,  $lead_id);
    update_field( 'post',  $post,  $lead_id);
    
    // Send mail to the owner of the virtual card
    $body = __( 'Congratulation',  'gtmi_vcard');
    $body .= "$firstname $lastname ";
    $body .= __( 'with following information contact you : ',  'gtmi_vcard');
    $body .= "$mobile, $email, $society";
    gtmi_vcard_send_mail($email,  __( 'new lead contact',  'gtmi_vcard'),  $body);
    // Success
    return gtmi_vcard_api_response(
         __( 'Lead created successfull !',  'gtmi_vcard'),
         true,
         ['lead_id' => $lead_id],
         201
    );
}