<?php

add_filter(hook_name: 'manage_lead_posts_columns', callback: 'gtmi_vcard_custom_new_columns_lead');
add_action(hook_name: 'manage_lead_posts_custom_column', callback: 'gtmi_vcard_custom_column_content_lead', priority: 10, accepted_args: 2);
/**
 * Add custom columns
 *
 * @param array $columns existing columns
 * @return array new columns.
 */
function gtmi_vcard_custom_new_columns_lead($columns): array
{
  return [
    'cb' => '<input type="checkbox" />', // for grouped actions
    'identity' => __(text: 'Identity', domain: 'gtmi_vcard'),
    'email' => __(text: 'Email', domain: 'gtmi_vcard'),
    'society' => __(text: 'Society', domain: 'gtmi_vcard'),
    'post' => __(text: 'Post', domain: 'gtmi_vcard'),
    'virtual_card_id' => __(text: 'Linked virtual card ID', domain: 'gtmi_vcard'),
    'linked_virtual_card' => __(text: 'Linked virtual card URL', domain: 'gtmi_vcard'),
    'contact_datetime' => __(text: 'Contact datetime', domain: 'gtmi_vcard'),
    'date' => __(text: 'Date created', domain: 'gtmi_vcard'),
  ];
}
/**
 * Show custom columns with data of each virtual card
 *
 * @param string $column key.
 * @param int $post_id current post ID.
 */
function gtmi_vcard_custom_column_content_lead($column, $post_id): void
{
  $vcard_id = get_field(selector: 'linked_virtual_card')[0];
  switch ($column) {
    case 'identity':
      $firstname = get_field(selector: 'firstname', post_id: $post_id);
      $lastname = get_field(selector: 'lastname', post_id: $post_id);
      echo esc_html(text: $firstname . ' ' . $lastname);
      break;
    case 'virtual_card_id':
      echo esc_attr(text: $vcard_id);
      break;
    case 'linked_virtual_card':
      $url = esc_url(url: get_permalink(post: $vcard_id));
      echo '<a href="' . esc_attr(text: $url) . '">' . $url . '</a>';
      break;
    case 'email':
      $email = get_field(selector: 'email', post_id: $post_id);
      if ($email) {
        echo '<a href="mailto:' . esc_attr(text: $email) . '">' . esc_html(text: $email) . '</a>';
      } else {
        echo '-';
      }
      break;
    case 'society':
      echo esc_html(text: get_field(selector: 'society', post_id: $post_id) ?? '-');
      break;
    case 'post':
      $status = get_field(selector: 'post', post_id: $post_id);
      echo esc_html(text: $status);
      break;
    case 'contact_datetime':
      $dt = get_field(selector: 'contact_datetime', post_id: $post_id);
      echo esc_html(text: $dt);
      break;
  }
}
