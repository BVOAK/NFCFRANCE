<?php
/**
 * FIX pour api/lead/find.php 
 * Corriger la requête pour gérer le format array ACF
 */

error_log('🔍 DEBUG: Chargement api/lead/find.php');

function gtmi_vcard_register_rest_routes_find_leads(): void
{
  error_log('🔍 DEBUG: gtmi_vcard_register_rest_routes_find_leads() appelée');
  register_rest_route( 'gtmi_vcard/v1',  '/leads/(?P<vcard_id>\d+)',  [
    'methods' => 'GET',
    'callback' => 'gtmi_vcard_leads_of_vcard',
    'permission_callback' => '__return_true',
    'args' => [
      'vcard_id' => [
        'description' => 'ID of the virtual card to retrieve leads for.',
        'type' => 'integer', 
        'required' => true,
        'sanitize_callback' => 'absint',
      ],
    ],
  ]);
}
add_action( 'rest_api_init',  'gtmi_vcard_register_rest_routes_find_leads');
error_log('🔍 DEBUG: Hook rest_api_init ajouté pour leads');

function gtmi_vcard_leads_of_vcard(WP_REST_Request $request): WP_REST_Response
{

  error_log("🔍 DEBUG API Lead: Fonction appelée pour vCard " . $request->get_param('vcard_id'));

  $vcard_id = (int) $request->get_param('vcard_id');
  $virtual_card_post = get_post( $vcard_id);
  if (!$virtual_card_post || 'virtual_card' !== $virtual_card_post->post_type) {
    return  gtmi_vcard_api_response( 'Virtual Card not found',  404,  false);
  }

  // FIX: Utiliser LIKE pour gérer le format array sérialisé d'ACF
  $args = [
    'post_type' => 'lead',
    'meta_query' => [
      [
        'key' => 'linked_virtual_card',
        'value' => sprintf('"%d"', $vcard_id), // Chercher "1013" dans l'array
        'compare' => 'LIKE'
      ],
    ],
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC'
  ];

  $lead_query = new WP_Query($args);
  $leads = [];

  if ($lead_query->have_posts()) {
    while ($lead_query->have_posts()) {
      $lead_query->the_post();
      $lead_id = get_the_ID();
      
      // Récupérer tous les champs ACF
      $firstname = get_post_meta($lead_id, 'firstname', true);
      $lastname = get_post_meta($lead_id, 'lastname', true);
      $email = get_post_meta($lead_id, 'email', true);
      $mobile = get_post_meta($lead_id, 'mobile', true);
      $society = get_post_meta($lead_id, 'society', true);
      $post = get_post_meta($lead_id, 'post', true);
      $contact_datetime = get_post_meta($lead_id, 'contact_datetime', true);
      
      // Vérifier que ce lead est bien lié à notre vCard (double check)
      $linked_vcards = get_post_meta($lead_id, 'linked_virtual_card', true);
      $is_linked = false;
      
      if (is_array($linked_vcards)) {
        $is_linked = in_array($vcard_id, $linked_vcards);
      } elseif (is_string($linked_vcards)) {
        // Si c'est un array sérialisé, chercher l'ID dedans
        $is_linked = (strpos($linked_vcards, (string)$vcard_id) !== false);
      }
      
      if ($is_linked) {
        $leads[] = [
          'id' => $lead_id,
          'firstname' => $firstname,
          'lastname' => $lastname,
          'email' => $email,
          'mobile' => $mobile,
          'society' => $society,
          'post' => $post,
          'contact_datetime' => $contact_datetime,
          'vcard_id' => $vcard_id,
          'created_at' => get_the_date('c')
        ];
      }
    }
    wp_reset_postdata();
  }

  if (empty($leads)) {
    return gtmi_vcard_api_response(
       "No leads found for virtual card $vcard_id",
       true,
       []
    );
  }

  return gtmi_vcard_api_response(
     true,
     "Leads retrieved successfully",
     $leads,
     200
  );
}