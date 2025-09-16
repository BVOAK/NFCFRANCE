<?php
add_action(hook_name: 'restrict_manage_posts', callback: 'gtmi_vcard_filter_add_dates_from_and_to');
add_action(hook_name: 'pre_get_posts', callback: 'gtmi_vcard_filter_statistics_by_id_virtual_card');
add_action(hook_name: 'pre_get_posts', callback: 'gtmi_vcard_statistics_by_date_interval');
/**
 * Show only statistics associated to virtual card id value in URL parameters
 * @param mixed $query
 * @return void
 */
function gtmi_vcard_filter_statistics_by_id_virtual_card($query): void
{
  if (is_admin() && $query->is_main_query() && $query->get('post_type') == 'statistics') {
    if (isset($_GET['virtual_card_id'])) {
      $vcard_id = (int) htmlentities(string: $_GET['virtual_card_id']);
      $query->set('meta_query', [
        [
          'key' => 'virtual_card_id',
          'value' => $vcard_id,
          'compare' => '=',
          'type' => 'NUMERIC',
        ],
      ]);
    }
  }
}

function gtmi_vcard_filter_add_dates_from_and_to(): void
{
  global $typenow;
  if ('statistics' === $typenow) {
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
    $vcard_id = isset($_GET['virtual_card_id']) ? sanitize_text_field($_GET['virtual_card_id']) : '';
    $vcard_id = (int) $vcard_id;
    $event = isset($_GET['event']) ? sanitize_text_field($_GET['event']) : '';
    $events = gtmi_vcard_statistics_get_distinct_events();
    ?>
    <input type="text" name="date_from" onfocus="(this.type='date')" onblur="(this.type='text')"
      placeholder="<?= __(text: 'From', domain: 'gtmi_vcard'); ?>" value="<?php echo esc_attr(text: $date_from); ?>" />
    <input type="text" name="date_to" onfocus="(this.type='date')" name="date_to"
      placeholder="<?= __(text: 'To', domain: 'gtmi_vcard'); ?>" value="<?php echo esc_attr(text: $date_to); ?>" />
    <select name="event">
      <option value="">
        <?= __(text: 'All Events', domain: 'gtmi_vcard'); ?>
      </option>
      <?php
      foreach ($events as $evt) {
        echo (strtolower(string: $evt) === strtolower(string: $event)) ? '<option selected ' : '<option';
        echo ' value="' . esc_attr($evt) . '">' . esc_html($evt) . '</option>';
      }
      ?>
    </select>
    <?php
    if ($vcard_id != 0) {
      echo "<input type='hidden' name='virtual_card_id' value='$vcard_id'>";
    }
  }
}

function gtmi_vcard_statistics_by_date_interval($query): void
{
  global $pagenow;
  if (is_admin() && 'edit.php' === $pagenow && 'statistics' === $query->get('post_type') && $query->is_main_query()) {

    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
    $event = isset($_GET['event']) ? sanitize_text_field($_GET['event']) : '';

    if (!empty($date_from) || !empty($date_to)) {
      $date_query_args = [
        'relation' => 'AND',
      ];

      if (!empty($date_from)) {
        $date_query_args[] = [
          'after' => $date_from,
          'inclusive' => true,
          'column' => 'post_date',
        ];
      }

      if (!empty($date_to)) {
        $date_query_args[] = [
          'before' => $date_to,
          'inclusive' => true,
          'column' => 'post_date',
        ];
      }
      $query->set('date_query', $date_query_args);
    }
    if (!empty($event)) {
        $meta_query = $query->get('meta_query'); // Récupère les meta_query existantes si elles existent
        if (!is_array($meta_query)) {
          $meta_query = [];
        }

        $meta_query[] = [
          'key' => 'event',
          'value' => $event,
          'compare' => '=',
        ];
        $query->set('meta_query', $meta_query);
      }
  }
}

function gtmi_vcard_statistics_get_distinct_events()
{
  global $wpdb;
  $acf_field_name = 'event';
  $post_type = 'statistics';


  $query = $wpdb->prepare(
    "SELECT DISTINCT pm.meta_value
         FROM {$wpdb->postmeta} AS pm
         LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
         WHERE pm.meta_key = %s
         AND p.post_type = %s
         AND p.post_status = 'publish'
         ORDER BY pm.meta_value ASC",
    $acf_field_name,
    $post_type
  );
  return $wpdb->get_col($query);
}