<?php
function gtmi_vcard_findall_custom_type_post_query(
  $post_type,
  $key_id,
  $value_id,
  $fields = 'all'
): WP_Query {
   $args = [
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => [
          [
            'key' => $key_id,
            'value' => $value_id,
            'compare' => '=',
             'type'    => 'NUMERIC',
          ],
        ],
        'fields' => $fields
      ];
      return new WP_Query( $args);
}

function gtmi_vcard_send_mail($email, $subject, $body, $headers = ['Content-Type: text/html; charset=UTF-8','From: NFC France <'.GTMI_VCARD_EMAIL_SENDER.'>']): void
{
  error_log( "GTMI_VCARD: send mail to  $email with subject $subject");
  $message = __( $body,  'gtmi_vcard');
  wp_mail( $email,  $subject,  $message,  $headers);
}


