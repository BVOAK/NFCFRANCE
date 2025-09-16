<?php

function gtmi_vcard_jwt_get_token(): void
{
  add_filter('jwt_auth_token_before_sign', function ($token, $user) {
    $token['customer_id'] = get_user_meta($user->ID, 'customer_id', true);
    $token['customer_email'] = get_user_meta($user->ID, 'customer_email', true);
    var_dump($token);
    return $token;
  }, 10, 2);
}