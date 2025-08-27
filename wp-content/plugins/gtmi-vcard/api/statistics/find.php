<?php
add_action( 'rest_api_init',  'gtmi_vcard_register_rest_routes_get_statistics');
add_action( 'rest_api_init',  'gtmi_vcard_register_rest_routes_get_statistics_between_two_dates');

function gtmi_vcard_register_rest_routes_get_statistics(): void
{
  register_rest_route( 'gtmi_vcard/v1',  '/statistics/(?P<virtual_card_id>\d+)',  [
    'methods' => 'GET',
    'callback' => 'gtmi_vcard_get_statistics_of_vcard',
    'permission_callback' => '__return_true',
    'args' => [
      'virtual_card_id' => [
        'description' => 'ID of the virtual card to retrieve statistics for.',
        'type' => 'integer',
        'required' => true,
        'sanitize_callback' => 'absint',
      ],
    ],
  ]);
}

function gtmi_vcard_register_rest_routes_get_statistics_between_two_dates(): void
{
  register_rest_route( 'gtmi_vcard/v1',  '/statistics/(?P<virtual_card_id>\d+)/(?P<date_start>\d+)/(?P<date_end>\d+)/',  [
    'methods' => 'GET',
    'callback' => 'gtmi_vcard_get_statistics_of_vcard',
    'permission_callback' => '__return_true',
    'args' => [
      'virtual_card_id' => [
        'description' => 'ID of the virtual card to retrieve statistics for.',
        'type' => 'integer',
        'required' => true,
        'sanitize_callback' => 'absint',
      ],
      'date_start' => [
        'description' => 'date start',
        'type' => 'integer',
        'required' => true,
        'sanitize_callback' => 'absint',
      ],
      'date_end' => [
        'description' => 'date end',
        'type' => 'integer',
        'required' => true,
        'sanitize_callback' => 'absint',
      ],
    ],
  ]);
}

/**
 * Get statistics from vcard ID via API REST
 *
 * @param WP_REST_Request $request object REST
 * @return WP_REST_Response response object REST
 */
/* function gtmi_vcard_get_statistics_of_vcard(WP_REST_Request $request): WP_REST_Response
{
  global $wpdb;
  $virtual_card_id = (int) $request->get_param( 'virtual_card_id');
  $virtual_card_post = get_post( $virtual_card_id);
  
  if (!$virtual_card_post || 'virtual_card' !== $virtual_card_post->post_type) {
    return gtmi_vcard_api_response( false,  'Virtual Card not found',  404);
  }

  // Get date parameters for filtering (optional)
  $date_start = $request->get_param('date_start');
  $date_end = $request->get_param('date_end');

  // Build the query to get statistics posts
  $args = [
    'post_type' => 'statistics',
    'posts_per_page' => -1,
    'meta_query' => [
      [
        'key' => 'vcard_id',
        'value' => $virtual_card_id,
        'compare' => '='
      ]
    ],
    'orderby' => 'date',
    'order' => 'DESC'
  ];

  // Add date filtering if provided
  if ($date_start && $date_end) {
    $args['date_query'] = [
      [
        'after' => date('Y-m-d', $date_start),
        'before' => date('Y-m-d', $date_end),
        'inclusive' => true
      ]
    ];
  }

  $statistics_query = new WP_Query($args);
  $statistics = [];

  if ($statistics_query->have_posts()) {
    while ($statistics_query->have_posts()) {
      $statistics_query->the_post();
      $post_id = get_the_ID();
      
      $statistics[] = [
        'id' => $post_id,
        'vcard_id' => get_post_meta($post_id, 'vcard_id', true),
        'event' => get_post_meta($post_id, 'event', true),
        'ip_address' => get_post_meta($post_id, 'ip_address', true),
        'user_agent' => get_post_meta($post_id, 'user_agent', true),
        'referer' => get_post_meta($post_id, 'referer', true),
        'location' => get_post_meta($post_id, 'location', true),
        'created_at' => get_the_date('c'),
        'timestamp' => get_post_time('U')
      ];
    }
    wp_reset_postdata();
  }

  if (empty($statistics)) {
    return gtmi_vcard_api_response(
       true,
       "No statistics found for virtual card $virtual_card_id",
       []
    );
  }

  return gtmi_vcard_api_response(
     true,
     "Statistics retrieved successfully",
     $statistics,
     200
  );
}
 */
function gtmi_vcard_get_statistics_of_vcard(WP_REST_Request $request): WP_REST_Response
{
    global $wpdb;
    $virtual_card_id = (int) $request->get_param( 'virtual_card_id');
    $virtual_card_post = get_post( $virtual_card_id);
    
    if (!$virtual_card_post || 'virtual_card' !== $virtual_card_post->post_type) {
        return gtmi_vcard_api_response( false,  'Virtual Card not found',  404);
    }

    // DEBUG: Compter tous les posts statistics pour cette vCard
    $debug_query = "
        SELECT COUNT(*) as total_stats
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'statistics'
        AND p.post_status = 'publish'
        AND pm.meta_key = 'vcard_id'
        AND pm.meta_value = %s
    ";
    
    $total_stats = $wpdb->get_var($wpdb->prepare($debug_query, $virtual_card_id));
    error_log("🔍 DEBUG API: Total posts statistics pour vCard {$virtual_card_id}: {$total_stats}");

    // Get date parameters for filtering (optional)
    $date_start = $request->get_param('date_start');
    $date_end = $request->get_param('date_end');

    // Build the query to get statistics posts
    $args = [
        'post_type' => 'statistics',
        'posts_per_page' => -1,
        'post_status' => 'publish', // AJOUT EXPLICITE
        'meta_query' => [
            [
                'key' => 'vcard_id',
                'value' => $virtual_card_id,
                'compare' => '='
            ]
        ],
        'orderby' => 'date',
        'order' => 'DESC'
    ];

    // DEBUG: Logger la requête
    error_log("🔍 DEBUG API: Args query: " . print_r($args, true));

    // Add date filtering if provided
    if ($date_start && $date_end) {
        $args['date_query'] = [
            [
                'after' => date('Y-m-d', $date_start),
                'before' => date('Y-m-d', $date_end),
                'inclusive' => true
            ]
        ];
    }

    $statistics_query = new WP_Query($args);
    
    // DEBUG: Logger le nombre de résultats
    error_log("🔍 DEBUG API: Résultats trouvés: " . $statistics_query->found_posts);
    
    $statistics = [];

    if ($statistics_query->have_posts()) {
        while ($statistics_query->have_posts()) {
            $statistics_query->the_post();
            $post_id = get_the_ID();
            
            // DEBUG: Logger chaque post trouvé
            error_log("🔍 DEBUG API: Post ID {$post_id}, Date: " . get_the_date('Y-m-d H:i:s'));
            
            $statistics[] = [
                'id' => $post_id,
                'vcard_id' => get_post_meta($post_id, 'vcard_id', true),
                'event' => get_post_meta($post_id, 'event', true),
                'ip_address' => get_post_meta($post_id, 'ip_address', true),
                'user_agent' => get_post_meta($post_id, 'user_agent', true),
                'referer' => get_post_meta($post_id, 'referer', true),
                'location' => get_post_meta($post_id, 'location', true),
                'created_at' => get_the_date('c'),
                'timestamp' => get_post_time('U')
            ];
        }
        wp_reset_postdata();
    }

    if (empty($statistics)) {
        return gtmi_vcard_api_response(
             true,
             "No statistics found for virtual card $virtual_card_id (but found $total_stats in DB)",
             []
        );
    }

    return gtmi_vcard_api_response(
         true,
         "Statistics retrieved successfully (found " . count($statistics) . " of $total_stats total)",
         $statistics,
         200
    );
}