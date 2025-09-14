<?php
/**
 * FIX pour api/lead/find.php 
 * Corriger la requête pour gérer le format array ACF
 */

error_log('🔍 DEBUG: Chargement api/lead/find.php');

/**
 * Correction api/lead/find.php - Ajouter endpoint multi-profils
 */

error_log('🔍 DEBUG: Chargement api/lead/find.php');

function gtmi_vcard_register_rest_routes_find_leads(): void
{
  error_log('🔍 DEBUG: gtmi_vcard_register_rest_routes_find_leads() appelée');

  // Endpoint existant pour une vCard spécifique
  register_rest_route('gtmi_vcard/v1', '/leads/(?P<vcard_id>\d+)', [
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

  // 🆕 NOUVEAU: Endpoint pour tous les leads d'un utilisateur multi-profils
  register_rest_route('gtmi_vcard/v1', '/leads/user/(?P<user_id>\d+)', [
      'methods' => 'GET',
      'callback' => 'gtmi_vcard_leads_of_user',
      'permission_callback' => function ($request) {
          error_log('🔍 DEBUG Permission API - Début vérification');
          
          // Récupérer les IDs
          $requested_user_id = (int) $request->get_param('user_id');
          $current_user_id = get_current_user_id();
          
          error_log("🔍 DEBUG Permission - requested_user_id: {$requested_user_id}");
          error_log("🔍 DEBUG Permission - current_user_id: {$current_user_id}");
          error_log("🔍 DEBUG Permission - is_user_logged_in: " . (is_user_logged_in() ? 'YES' : 'NO'));
          
          // Vérifier si l'utilisateur est connecté
          if (!is_user_logged_in()) {
              error_log('❌ DEBUG Permission - Utilisateur non connecté');
              return new WP_Error('unauthorized', 'Utilisateur non connecté', ['status' => 401]);
          }
          
          // Vérifier que c'est bien le même utilisateur
          if ($current_user_id !== $requested_user_id) {
              error_log("❌ DEBUG Permission - IDs différents: current={$current_user_id}, requested={$requested_user_id}");
              return new WP_Error('forbidden', 'Accès interdit - IDs différents', ['status' => 403]);
          }
          
          error_log('✅ DEBUG Permission - Autorisation accordée');
          return true;
      },
      'args' => [
          'user_id' => [
              'description' => 'ID of the user to retrieve all leads for.',
              'type' => 'integer',
              'required' => true,
              'sanitize_callback' => 'absint',
          ],
      ],
  ]);
}
add_action('rest_api_init', 'gtmi_vcard_register_rest_routes_find_leads');
error_log('🔍 DEBUG: Hook rest_api_init ajouté pour leads');

function gtmi_vcard_leads_of_vcard(WP_REST_Request $request): WP_REST_Response
{
  error_log("🔍 DEBUG API Lead: Fonction appelée pour vCard " . $request->get_param('vcard_id'));

  $vcard_id = (int) $request->get_param('vcard_id');
  $virtual_card_post = get_post($vcard_id);

  if (!$virtual_card_post || 'virtual_card' !== $virtual_card_post->post_type) {
    return gtmi_vcard_api_response('Virtual Card not found', 404, false);
  }

  $leads = get_vcard_leads($vcard_id);

  if (empty($leads)) {
    return gtmi_vcard_api_response(
      true,
      "No leads found for virtual card $vcard_id",
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

function gtmi_vcard_leads_of_user(WP_REST_Request $request): WP_REST_Response
{
  $user_id = (int) $request->get_param('user_id');
  error_log("🔍 DEBUG API Lead: Fonction appelée pour USER " . $user_id);

  // Récupérer toutes les vCards de l'utilisateur
  if (!function_exists('nfc_get_user_vcard_profiles')) {
    return gtmi_vcard_api_response('Function nfc_get_user_vcard_profiles not found', 500, false);
  }

  $user_vcards = nfc_get_user_vcard_profiles($user_id);

  if (empty($user_vcards)) {
    return gtmi_vcard_api_response(
      true,
      "No vCards found for user $user_id",
      []
    );
  }

  error_log("🔍 Multi-profils détecté: " . count($user_vcards) . " vCards pour user " . $user_id);

  $all_leads = [];

  foreach ($user_vcards as $vcard) {
    $current_vcard_id = $vcard['vcard_id'];
    $vcard_leads = get_vcard_leads($current_vcard_id);

    // Ajouter le vcard_id et le nom du profil source à chaque contact
    foreach ($vcard_leads as &$lead) {
      $lead['vcard_id'] = $current_vcard_id;
      $lead['vcard_source_name'] = nfc_format_vcard_full_name($vcard['vcard_data'] ?? []);
      $lead['linked_vcard_original'] = $lead['linked_vcard'] ?? null; // Backup
      $lead['linked_vcard'] = [$current_vcard_id]; // Standardiser pour JavaScript
    }
    unset($lead); // Libérer la référence

    $all_leads = array_merge($all_leads, $vcard_leads);
  }

  // Trier par date décroissante
  usort($all_leads, function ($a, $b) {
    $date_a = strtotime($a['created_at'] ?? $a['contact_datetime'] ?? '1970-01-01');
    $date_b = strtotime($b['created_at'] ?? $b['contact_datetime'] ?? '1970-01-01');
    return $date_b - $date_a;
  });

  error_log("🔍 Total contacts multi-profils retournés: " . count($all_leads));

  return gtmi_vcard_api_response(
    true,
    "All user leads retrieved successfully",
    $all_leads,
    200
  );
}


// 🆕 FONCTION HELPER: Récupérer les leads d'une vCard
function get_vcard_leads($vcard_id)
{
  error_log("🔍 get_vcard_leads appelée pour vcard_id: $vcard_id");

  global $wpdb;

  // 🎯 CORRECTION: Utiliser le format sérialisé exact pour ACF
  $exact_pattern = 'a:1:{i:0;s:' . strlen($vcard_id) . ':"' . $vcard_id . '";}';

  error_log("🔍 Pattern ACF recherché: $exact_pattern");

  // Requête SQL directe plus fiable que WP_Query pour ACF sérialisé
  $query = "
        SELECT 
            l.ID,
            l.post_title,
            l.post_date as created_at,
            pm_firstname.meta_value as firstname,
            pm_lastname.meta_value as lastname, 
            pm_email.meta_value as email,
            pm_mobile.meta_value as mobile,
            pm_society.meta_value as society,
            pm_post.meta_value as post,
            pm_contact_datetime.meta_value as contact_datetime,
            pm_source.meta_value as source,
            pm_link.meta_value as linked_vcard
        FROM {$wpdb->posts} l
        INNER JOIN {$wpdb->postmeta} pm_link 
            ON l.ID = pm_link.post_id 
            AND pm_link.meta_key = 'linked_virtual_card'
            AND pm_link.meta_value = %s
        LEFT JOIN {$wpdb->postmeta} pm_firstname ON l.ID = pm_firstname.post_id AND pm_firstname.meta_key = 'firstname'
        LEFT JOIN {$wpdb->postmeta} pm_lastname ON l.ID = pm_lastname.post_id AND pm_lastname.meta_key = 'lastname'
        LEFT JOIN {$wpdb->postmeta} pm_email ON l.ID = pm_email.post_id AND pm_email.meta_key = 'email'
        LEFT JOIN {$wpdb->postmeta} pm_mobile ON l.ID = pm_mobile.post_id AND pm_mobile.meta_key = 'mobile'
        LEFT JOIN {$wpdb->postmeta} pm_society ON l.ID = pm_society.post_id AND pm_society.meta_key = 'society'
        LEFT JOIN {$wpdb->postmeta} pm_post ON l.ID = pm_post.post_id AND pm_post.meta_key = 'post'
        LEFT JOIN {$wpdb->postmeta} pm_contact_datetime ON l.ID = pm_contact_datetime.post_id AND pm_contact_datetime.meta_key = 'contact_datetime'
        LEFT JOIN {$wpdb->postmeta} pm_source ON l.ID = pm_source.post_id AND pm_source.meta_key = 'source'
        WHERE l.post_type = 'lead'
        AND l.post_status = 'publish'
        ORDER BY l.post_date DESC
    ";

  $results = $wpdb->get_results($wpdb->prepare($query, $exact_pattern), ARRAY_A);

  $leads = [];
  foreach ($results as $row) {
    $leads[] = [
      'id' => (int) $row['ID'],
      'ID' => (int) $row['ID'], // Compatibilité
      'post_title' => $row['post_title'] ?: 'Lead sans nom',
      'post_date' => $row['created_at'],
      'firstname' => $row['firstname'] ?: '',
      'lastname' => $row['lastname'] ?: '',
      'email' => $row['email'] ?: '',
      'mobile' => $row['mobile'] ?: '',
      'society' => $row['society'] ?: '',
      'post' => $row['post'] ?: '',
      'contact_datetime' => $row['contact_datetime'] ?: $row['created_at'],
      'created_at' => $row['created_at'],
      'source' => $row['source'] ?: 'web',
      'linked_vcard' => [$vcard_id], // Format standardisé pour JS
      'vcard_id' => $vcard_id
    ];
  }

  error_log("📊 get_vcard_leads CORRIGÉ - Trouvé " . count($leads) . " leads pour vCard $vcard_id");

  // 🔍 DEBUG: Afficher les premiers résultats
  if (!empty($leads)) {
    error_log("📊 Premier lead trouvé: " . json_encode($leads[0]));
  }

  return $leads;
}

/**
 * 🔍 FONCTION DEBUG: Pour examiner le format réel des données
 */
function debug_linked_vcard_format($vcard_id) {
    global $wpdb;
    
    error_log("🔍 DEBUG: Examen format linked_virtual_card pour vcard_id: $vcard_id");
    
    // Récupérer tous les leads avec leurs données linked_virtual_card
    $results = $wpdb->get_results("
        SELECT 
            p.ID,
            p.post_title,
            pm.meta_value as linked_vcard_raw
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'linked_virtual_card'
        WHERE p.post_type = 'lead'
        AND p.post_status = 'publish'
        LIMIT 10
    ");
    
    foreach ($results as $lead) {
        error_log("🔍 Lead {$lead->ID} ({$lead->post_title}): " . $lead->linked_vcard_raw);
        
        // Tester si ça matche notre vCard
        if (strpos($lead->linked_vcard_raw, '"' . $vcard_id . '"') !== false) {
            error_log("✅ Lead {$lead->ID} CONTIENT vcard_id $vcard_id");
        }
    }
    
    // Tester les différents patterns
    $patterns_to_test = [
        'a:1:{i:0;s:' . strlen($vcard_id) . ':"' . $vcard_id . '";}', // Sérialisé PHP exact
        'a:1:{i:0;i:' . $vcard_id . ';}', // Sérialisé PHP avec int
        '"' . $vcard_id . '"', // Simple LIKE
        $vcard_id // Valeur brute
    ];
    
    foreach ($patterns_to_test as $index => $pattern) {
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'linked_virtual_card'
            WHERE p.post_type = 'lead'
            AND pm.meta_value = %s
        ", $pattern));
        
        error_log("🔍 Pattern $index ('$pattern'): $count résultats");
    }
}
