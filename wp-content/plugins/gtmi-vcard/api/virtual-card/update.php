<?php
function gtmi_vcard_register_rest_routes_update_virtual_card(): void
{
    register_rest_route(route_namespace: 'gtmi_vcard/v1', route: '/vcard/(?P<id>\d+)', args: [
        'methods' => 'POST',
        'callback' => 'gtmi_vcard_update_virtual_card',
        'permission_callback' => '__return_true',
        'args' => [
            'id' => [
                'description' => 'ID of the virtual card',
                'type' => 'integer',
                'required' => true,
                'sanitize_callback' => 'absint',
            ]
        ]
    ]);
}
add_action(hook_name: 'rest_api_init', callback: 'gtmi_vcard_register_rest_routes_update_virtual_card');

/**
 * Create lead via API REST
 *
 * @param WP_REST_Request $request object REST
 * @return WP_REST_Response response object REST
 */
function gtmi_vcard_update_virtual_card(WP_REST_Request $request): WP_REST_Response
{
    if (!gtmi_vcard_update_vcard_permissions(order_id: $request->get_param(key: 'order'))) {
        return gtmi_vcard_api_response(message: 'Cannot update virtual card, permission denied', status: 403);
    }
    $id = (int) $request->get_param(key: 'id');

    $virtual_card_post = get_post(post: $id);
    if (!$virtual_card_post || 'virtual_card' !== $virtual_card_post->post_type) {
        return gtmi_vcard_api_response(message: 'Virtual card not found', success: 404);
    }

    $fields = [
        'firstname',
        'lastname',
        'email',
        'mobile',
        'society',
        'service',
        'post',
        'phone',
        'address',
        'additional',
        'postcode',
        'city',
        'country',
        'website',
        'linkedin',
        'facebook',
        'twitter',
        'instagram',
        'pinterest',
        'youtube',
        'custom_url',
        'profile_picture',
        'cover_image',
        'description',
        'card_status'
    ];

    // Get data from request
    $data_to_update = [];
    foreach ($fields as $numeric_key => $param_name) {
        $data_to_update[$param_name] = $request->get_param(key: $param_name);
    }
    // Update ACF fields in a loop
    foreach ($data_to_update as $field_name => $value) {
        if ($value && !empty($value)) {
            update_field(selector: $field_name, value: $value, post_id: $id);
        }
    }

    return gtmi_vcard_api_response(message: 'Virtual card updated successfully', success: true, data: get_fields(post_id: $id));

}
// TODO review and handle permissions
function gtmi_vcard_update_vcard_permissions(int $order_id): bool
{
    $order = wc_get_order(the_order: (int) $order_id);
    $order_user_id = $order->get_customer_id();
    $current_user = wp_get_current_user();
    // check is admin or can edit virtual card
    return current_user_can(capability: 'edit_posts') || true;// && $order_user_id === $current_user->ID;
}

function gtm_vcard_update_file(WP_REST_Request $request)
{
    $file_params = $request->get_file_params();
    $id = (int) $request->get_param(key: 'id');
    if (
        (isset($file_params['profile_picture']) && !empty($file_params['profile_picture']))
        || (isset($file_params['cover_image']) && !empty($file_params['cover_image']))
    ) {
        $file_info = $file_params['cover_image'] ?? $file_params['profile_picture'];
        $subdir = $file_params['cover_image'] ? 'cover' : 'profile';

        $upload_dir = wp_upload_dir();
        $new_file_path = $upload_dir['uploads'] . '/' . $subdir . '/' . $id . '.' . pathinfo(path: $file_info['name'], flags: PATHINFO_EXTENSION);
        return move_uploaded_file(from: $file_info['tmp_name'], to: $new_file_path);
    }

}
