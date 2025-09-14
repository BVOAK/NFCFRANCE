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
  
  // 🆕 NOUVEAU: Endpoint pour tous les leads d'un utilisateur multi-profils
  register_rest_route( 'gtmi_vcard/v1',  '/leads/user/(?P<user_id>\d+)',  [
    'methods' => 'GET',
    'callback' => 'gtmi_vcard_leads_of_user',
    'permission_callback' => function($request) {
        // Vérifier que l'utilisateur connecté peut accéder à ces données
        $requested_user_id = (int) $request->get_param('user_id');
        $current_user_id = get_current_user_id();
        
        if ($current_user_id !== $requested_user_id) {
            return new WP_Error('forbidden', 'Accès interdit', ['status' => 403]);
        }
        
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
add_action( 'rest_api_init',  'gtmi_vcard_register_rest_routes_find_leads');
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
    usort($all_leads, function($a, $b) {
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
function get_vcard_leads($vcard_id) {
    $args = [
        'post_type' => 'lead',
        'meta_query' => [
            [
                'key' => 'linked_virtual_card',
                'value' => sprintf('"%d"', $vcard_id),
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
            
            // Récupérer linked_virtual_card et le nettoyer
            $linked_vcard_raw = get_post_meta($lead_id, 'linked_virtual_card', true);
            $linked_vcard_clean = [];
            
            if ($linked_vcard_raw) {
                if (is_string($linked_vcard_raw) && strpos($linked_vcard_raw, 'a:') === 0) {
                    // PHP sérialisé
                    $unserialized = @unserialize($linked_vcard_raw);
                    if ($unserialized !== false && is_array($unserialized)) {
                        $linked_vcard_clean = array_values($unserialized);
                    }
                } else if (is_array($linked_vcard_raw)) {
                    $linked_vcard_clean = array_values($linked_vcard_raw);
                } else {
                    $linked_vcard_clean = [$vcard_id]; // Fallback
                }
            } else {
                $linked_vcard_clean = [$vcard_id]; // Fallback
            }
            
            $leads[] = [
                'id' => $lead_id,
                'ID' => $lead_id, // Pour compatibilité
                'post_title' => get_the_title(),
                'post_date' => get_the_date('Y-m-d H:i:s'),
                'firstname' => get_post_meta($lead_id, 'firstname', true),
                'lastname' => get_post_meta($lead_id, 'lastname', true),
                'email' => get_post_meta($lead_id, 'email', true),
                'mobile' => get_post_meta($lead_id, 'mobile', true),
                'society' => get_post_meta($lead_id, 'society', true),
                'post' => get_post_meta($lead_id, 'post', true),
                'contact_datetime' => get_post_meta($lead_id, 'contact_datetime', true),
                'created_at' => get_the_date('c'),
                'source' => get_post_meta($lead_id, 'source', true) ?: 'web',
                'linked_vcard' => $linked_vcard_clean,
                'vcard_id' => $vcard_id // Ajouter explicitement
            ];
        }
        wp_reset_postdata();
    }

    error_log("📊 get_vcard_leads pour vCard $vcard_id: " . count($leads) . " leads trouvés");
    return $leads;
}

?>