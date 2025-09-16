<?php

/**
 * Filters
 *  manage_{$post_type}_posts_columns: to define new columns.
 *  manage_{$post_type}_posts_custom_column: to fill new columns
 */
add_filter(hook_name: 'manage_virtual_card_posts_columns', callback: 'gtmi_vcard_custom_new_columns');
add_action(hook_name: 'manage_virtual_card_posts_custom_column', callback: 'gtmi_vcard_custom_column_content', priority: 10, accepted_args: 2);
add_action(hook_name: 'add_meta_boxes', callback: 'gtmi_vcard_add_leads_metabox');

/**
 * Add custom columns
 *
 * @param array $columns existing columns
 * @return array new columns.
 */
function gtmi_vcard_custom_new_columns($columns): array
{
  return [
    'cb' => '<input type="checkbox" />', // for grouped actions
    'order' => __(text: 'Order ID', domain: 'gtmi_vcard'),
    'virtual_card_id' => __(text: 'Virtual Card ID', domain: 'gtmi_vcard'),
    'identity' => __(text: 'Identity', domain: 'gtmi_vcard'),
    'email' => __(text: 'Email', domain: 'gtmi_vcard'),
    'society' => __(text: 'Society', domain: 'gtmi_vcard'),
    'url' => __(text: 'URL', domain: 'gtmi_vcard'),
    'card_status' => __(text: 'Card status', domain: 'gtmi_vcard'),
    'leads' => __(text: 'Leads', domain: 'gtmi_vcard'),
    'statistics' => __(text: 'Statistics', domain: 'gtmi_vcard')
  ];
}
/**
 * Show custom columns with data of each virtual card
 *
 * @param string $column key.
 * @param int $post_id current post ID.
 */
function gtmi_vcard_custom_column_content($column, $post_id): void
{
  switch ($column) {
    case 'identity':
      $firstname = get_field(selector: 'firstname', post_id: $post_id);
      $lastname = get_field(selector: 'lastname', post_id: $post_id);
      echo esc_html(text: "$firstname  $lastname");
      break;
    case 'url':
      $url = get_field(selector: 'url');
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
    case 'card_generated':
      $status = get_field(selector: 'card_generated', post_id: $post_id);
      echo esc_html(text: $status);
      break;
    case 'virtual_card_id':
      echo esc_html(text: $post_id);
      break;
    case 'order':
      $order = get_field(selector: 'order', post_id: $post_id);
      echo esc_html(text: $order);
      break;
    case 'card_status':
      $order = get_field(selector: 'card_status', post_id: $post_id);
      echo esc_html(text: $order);
      break;
    case 'leads':
      $leads_query = gtmi_vcard_findall_custom_type_post_query(
        post_type: 'lead',
        key_id: 'linked_virtual_card',
        value_id: $post_id,
        fields: 'ids'
      );
      $countLeads = 0;
      if ($leads_query->have_posts()) {
        $countLeads = $leads_query->found_posts;
      }
      echo '<a href="' . esc_url(url: admin_url(path: "post.php?post=$post_id")) . '&action=edit&meta_gtmi_vcard=leads">' . __(text: 'See', domain: 'gtmi_vcard') . ' ' . $countLeads . ' lead(s)';
      break;
    case 'statistics':
      echo '<a href="' . esc_url(url: admin_url(path: "edit.php")) . '?post_type=statistics&virtual_card_id=' . $post_id . '">' . __(text: 'See', domain: 'gtmi_vcard') . ' statistics</a>';
      break;
  }
}

/**
 * Ajoute la metabox pour afficher les leads associés à une virtual_card.
 */
function gtmi_vcard_add_leads_metabox(): void
{
  add_meta_box(
    id: 'gtmi_vcard_leads', // ID unique de la metabox
    title: esc_html__(text: 'Related leads', domain: 'gtmi_vcard'), // Titre de la metabox
    callback: 'gtmi_vcard_display_leads_metabox', // Fonction de callback pour afficher le contenu
    screen: 'virtual_card', // Le slug de votre Custom Post Type (CPT)
    context: 'normal', // Contexte (normal, advanced, side)
    priority: 'high' // Priorité (high, core, default, low)
  );
}

/**
 * Show leads in metabox
 *
 * @param WP_Post $post current virtual card object
 */
function gtmi_vcard_display_leads_metabox($post): void
{
  $virtual_card_id = $post->ID;
  $virtual_card_url = esc_url(url: get_permalink(post: $post->ID));
  $virtual_card_title = esc_html(text: $post->post_title);

  $leads_query = gtmi_vcard_findall_custom_type_post_query(
    post_type: 'lead',
    key_id: 'linked_virtual_card',
    value_id: $virtual_card_id,
    fields: 'ids'
  );

  if ($leads_query->have_posts()) {
    $total_leads = absint(maybeint: $leads_query->found_posts);
    $text_template = '<h2>%1$s <a href="%2$s">%3$s</a> %4$s %5$s %6$s</h2>';
    echo sprintf(
      $text_template,
      esc_html__(text: 'List of leads associated to', domain: 'gtmi_vcard'),
      $virtual_card_url,
      $virtual_card_title,
      esc_html__(text: 'total', domain: 'gtmi_vcard'),
      $total_leads,
      esc_html(text: _n(single: 'lead', plural: 'leads', number: $total_leads, domain: 'gtmi_vcard'))
    );
    echo '<ol>';
    while ($leads_query->have_posts()):
      $leads_query->the_post();
      $edit_link = get_edit_post_link(post: get_the_ID());
      echo '<li>';
      echo '<a href="' . esc_url(url: $edit_link) . '">' . esc_html(text: get_the_title()) . '</a>';
      echo '</li>';
    endwhile;
    echo '</ol>';
    wp_reset_postdata();
  } else {
    $current_vcard_owner = get_field('firstname') . ' ' . get_field('lastname');
    echo '<p>' . __(text: 'None lead is related to this virtual card of ', domain: 'gtmi_vcard') . ' ' . $current_vcard_owner . ' of ' . $virtual_card_url . '</p>';
  }
}


