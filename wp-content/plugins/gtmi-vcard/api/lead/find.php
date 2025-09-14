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
    $virtual_card_post = get_post($vcard_id);
    
    if (!$virtual_card_post || 'virtual_card' !== $virtual_card_post->post_type) {
        return gtmi_vcard_api_response('Virtual Card not found', 404, false);
    }

    // 🆕 NOUVEAU: Détecter si utilisateur multi-profils
    $current_user_id = get_current_user_id();
    
    if ($current_user_id > 0 && function_exists('nfc_get_user_vcard_profiles')) {
        $user_vcards = nfc_get_user_vcard_profiles($current_user_id);
        
        // Si multi-profils ET pas de filtre spécifique, retourner TOUS les contacts
        if (count($user_vcards) > 1 && !isset($_GET['single_profile'])) {
            error_log("🔍 Multi-profils détecté: " . count($user_vcards) . " vCards pour user " . $current_user_id);
            
            $all_leads = [];
            
            foreach ($user_vcards as $vcard) {
                $current_vcard_id = $vcard['vcard_id'];
                $vcard_leads = get_vcard_leads($current_vcard_id);
                
                // Ajouter le nom du profil source à chaque contact
                foreach ($vcard_leads as $lead) {
                    $lead['vcard_id'] = $current_vcard_id;
                    $lead['vcard_source_name'] = nfc_format_vcard_full_name($vcard['vcard_data'] ?? []);
                    $all_leads[] = $lead;
                }
            }
            
            error_log("🔍 Total contacts multi-profils: " . count($all_leads));
            
            return gtmi_vcard_api_response(
                true,
                "Leads retrieved successfully",
                $all_leads,
                200
            );
        }
    }

    // 🔄 COMPORTEMENT NORMAL: Une seule vCard
    error_log("🔍 Mode profil unique pour vCard " . $vcard_id);
    $leads = get_vcard_leads($vcard_id);
    
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
            
            $leads[] = [
                'id' => $lead_id,
                'ID' => $lead_id, // Pour compatibilité
                'firstname' => get_post_meta($lead_id, 'firstname', true),
                'lastname' => get_post_meta($lead_id, 'lastname', true),
                'email' => get_post_meta($lead_id, 'email', true),
                'mobile' => get_post_meta($lead_id, 'mobile', true),
                'society' => get_post_meta($lead_id, 'society', true),
                'post' => get_post_meta($lead_id, 'post', true),
                'contact_datetime' => get_post_meta($lead_id, 'contact_datetime', true),
                'created_at' => get_the_date('c'),
                'source' => get_post_meta($lead_id, 'source', true) ?: 'web'
            ];
        }
        wp_reset_postdata();
    }

    return $leads;
}