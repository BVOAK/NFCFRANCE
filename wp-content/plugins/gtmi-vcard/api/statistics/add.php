<?php

function gtmi_vcard_register_rest_routes_add_stats(): void
{
  register_rest_route( 'gtmi_vcard/v1',  '/statistics',  [
    'methods' => ['POST', 'GET'],
    'callback' => 'gtmi_vcard_add_stats',
    'permission_callback' => '__return_true',
    'args' => [
      'virtual_card_id' => [
        'required' => true,
        'type' => 'integer',
        'description' => 'Virtual Card ID',
        'sanitize_callback' => 'absint',
      ],
      'event' => [
        'required' => true,
        'type' => 'string',
        'description' => 'Event like click on whatsApp, linkedin button or visited virtual card',
        'sanitize_callback' => 'sanitize_text_field',
      ]
    ],
  ]);
}
add_action( 'rest_api_init',  'gtmi_vcard_register_rest_routes_add_stats');

/**
 * Create or increment value of specific event via API REST
 *
 * @param WP_REST_Request $request object REST
 * @return WP_REST_Response response object REST
 */
function gtmi_vcard_add_stats(WP_REST_Request $request): WP_REST_Response
{
  // Get data from request
  $virtual_card_id = (int) $request->get_param( 'virtual_card_id');
  $event = strtolower( $request->get_param( 'event'));
  $value = 1;
  
  $virtual_card_post = get_post( $virtual_card_id);
  if (!$virtual_card_post || 'virtual_card' !== $virtual_card_post->post_type) {
    return gtmi_vcard_api_response( 'Virtual Card not found',  404,  false);
  }

  // Récupérer les données de tracking supplémentaires
  $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
  $referer = $_SERVER['HTTP_REFERER'] ?? '';

  // CORRECTION: Chercher avec la bonne clé 'vcard_id' au lieu de 'virtual_card_id'
  $args = [
    'post_type' => 'statistics',
    'posts_per_page' => -1,
    'meta_query' => [
      'relation' => 'AND',
      [
        'key' => 'vcard_id', // ✅ CHANGÉ: était 'virtual_card_id'
        'value' => (int) $virtual_card_id,
        'compare' => '=',
        'type' => 'NUMERIC',
      ],
      [
        'key' => 'event',
        'value' => $event,
        'compare' => '=',
        'type' => 'CHAR',
      ],
    ],
    'date_query' => [
      [
        'column' => 'post_date',
        'after' => date( 'Y-m-d') . ' 00:00:00',
        'before' => date( 'Y-m-d') . ' 23:59:59',
        'inclusive' => true,
      ]
    ]
  ];

  $status = 200;
  $existing_stats_today = new WP_Query( $args);
  
  if ($existing_stats_today->found_posts === 1) {
    // Incrémenter le post existant
    $post_id = (int) $existing_stats_today->post->ID;
    $current_value = get_post_meta($post_id, 'value', true) ?: 1;
    $new_value = $current_value + 1;
    
    // Mettre à jour la valeur
    update_post_meta($post_id, 'value', $new_value);
    
    error_log("📊 API Stats: Incrémenté post {$post_id}, nouvelle valeur: {$new_value}");
    
    return gtmi_vcard_api_response(
       true,
       "Statistics incremented for virtual card $virtual_card_id",
       ['post_id' => $post_id, 'value' => $new_value],
       200
    );
    
  } else {
    // Créer un nouveau post avec les BONNES métadonnées
    $post_data = [
      'post_status' => 'publish',
      'post_type' => 'statistics',
      'post_author' => 1,
      'post_title' => "Stat - " . ucfirst($event) . " - " . date('Y-m-d H:i:s'),
    ];
    
    $post_id = wp_insert_post($post_data, true);
    
    if (is_wp_error($post_id)) {
      return gtmi_vcard_api_response(
         false,
         'Error creating statistics post: ' . $post_id->get_error_message(),
         500
      );
    }
    
    // ✅ CORRECTION: Utiliser 'vcard_id' au lieu de 'virtual_card_id'
    $meta_data = [
      'vcard_id' => $virtual_card_id,
      'event' => $event,
      'value' => 1,
      'ip_address' => $ip_address,
      'user_agent' => $user_agent,
      'referer' => $referer,
      'location' => '', // Peut être enrichi plus tard
    ];
    
    // Ajouter toutes les métadonnées
    foreach ($meta_data as $key => $value) {
      update_post_meta($post_id, $key, $value);
    }
    
    error_log("📊 API Stats: Nouveau post créé {$post_id} pour vCard {$virtual_card_id}, event: {$event}");
    
    return gtmi_vcard_api_response(
       true,
       "Statistics created for virtual card $virtual_card_id",
       ['post_id' => $post_id, 'event' => $event, 'vcard_id' => $virtual_card_id],
       201
    );
  }
}