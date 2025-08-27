<?php

function gtmi_vcard_api_response($message, $success = true, $data = [], $status = 200): WP_REST_Response
{
    return new WP_REST_Response(data: [
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], status: $status);
}

function gtmi_vcard_api_increment_view($virtual_card_id)
{
    $url = get_rest_url(null, "gtmi_vcard/v1/statistics?event=view&virtual_card_id=$virtual_card_id");
    $response = wp_remote_get(url: $url);
    if (is_wp_error($response)) {
        error_log(message: "GTMI_VCARD: failed to increment view for virtual card $virtual_card_id " . date(format: 'd-m-Y H:i:s'));
    }
}