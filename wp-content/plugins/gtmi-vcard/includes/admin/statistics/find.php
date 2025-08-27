<?php

add_filter(hook_name: 'manage_statistics_posts_columns', callback: 'gtmi_vcard_custom_new_columns_statistics');
add_action(hook_name: 'manage_statistics_posts_custom_column', callback: 'gtmi_vcard_custom_column_content_statistics', priority: 10, accepted_args: 2);

/**
 * Add custom columns
 *
 * @param array $columns existing columns
 * @return array new columns.
 */
function gtmi_vcard_custom_new_columns_statistics($columns): array
{
  return [
    'cb' => '<input type="checkbox" />', // for grouped actions
    'id' => __(text: 'Statistics ID', domain: 'gtmi_vcard'),
    'related_virtual_card' => __(text: 'Related Virtual Card', domain: 'gtmi_vcard'),
    'virtual_card_id' => __(text: 'Virtual Card ID', domain: 'gtmi_vcard'),
    'virtual_card_owner' => __(text: 'Virtual Card Owner', domain: 'gtmi_vcard'),
    'event' => __(text: 'Event', domain: 'gtmi_vcard'),
    'value' => __(text: 'Value', domain: 'gtmi_vcard'),
    'created_at' => __(text: 'Created at', domain: 'gtmi_vcard'),
    'last_action_at' => __(text: 'Created at', domain: 'gtmi_vcard'),
  ];
}
/**
 * Show custom columns with data of each virtual card
 *
 * @param string $column key.
 * @param int $post_id current post ID.
 */
function gtmi_vcard_custom_column_content_statistics($column, $post_id): void
{
  $vcard_id = get_field(selector: 'virtual_card_id')[0];
  switch ($column) {
    case 'id':
      echo esc_html(text: $post_id);
      break;
    case 'virtual_card_id':
      echo esc_html(text: $vcard_id);
      break;
    case 'virtual_card_owner':
      $fullname = get_field(selector: 'firstname', post_id: $vcard_id) . ' ' . get_field(selector: 'lastname', post_id: $vcard_id);
      echo $fullname;
      break;
    case 'related_virtual_card':
      $url = esc_url(url: get_permalink(post: $vcard_id));
      echo '<a href="' . esc_attr(text: $url) . '">' . $url . '</a>';
      break;
    case 'event':
      echo esc_html(text: get_field(selector: 'event', post_id: $post_id) ?? '-');
      break;
    case 'value':
      $value = get_field(selector: 'value', post_id: $post_id);
      echo esc_html(text: $value);
      break;
    case 'created_at':
      $dt = get_the_date(format: 'd/m/Y H:i:s', post: $post_id);
      echo esc_html(text: $dt);
      break;
    case 'last_action_at':
      $dt = get_the_modified_date(format: 'd/m/Y H:i:s', post: $post_id);
      echo esc_html(text: $dt);
      break;
  }
}