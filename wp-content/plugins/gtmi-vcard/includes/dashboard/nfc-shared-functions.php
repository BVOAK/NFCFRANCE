<?php
/**
 * Fonctions Communes NFC Dashboard
 * Fichier: wp-content/plugins/gtmi-vcard/includes/dashboard/nfc-shared-functions.php
 * 
 * Fonctions réutilisables pour Stats et Leads dans le dashboard multi-cartes
 */

if (!defined('ABSPATH')) {
    exit;
}

// ================================================================================
// FONCTIONS STATISTIQUES
// ================================================================================

/**
 * Récupère les statistiques rapides d'une vCard
 * 
 * @param int $vcard_id ID de la vCard
 * @param int $days Période en jours (défaut: 30)
 * @return array Stats formatées
 */
function nfc_get_vcard_quick_stats($vcard_id, $days = 30) {
    global $wpdb;
    
    // Date limite pour la période
    $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
    
    // Pattern exact pour les données ACF (format string)
    $exact_pattern = 'a:1:{i:0;s:' . strlen($vcard_id) . ':"' . $vcard_id . '";}';
    
    // Étape 1 : Trouver tous les posts statistics pour cette vCard
    $posts_query = "
        SELECT DISTINCT s.ID
        FROM {$wpdb->posts} s
        INNER JOIN {$wpdb->postmeta} pm_vcard 
            ON s.ID = pm_vcard.post_id 
            AND pm_vcard.meta_key = 'virtual_card_id'
            AND pm_vcard.meta_value = %s
        WHERE s.post_type = 'statistics'
        AND s.post_date >= %s
    ";
    
    $post_ids = $wpdb->get_col($wpdb->prepare($posts_query, $exact_pattern, $date_limit));
    
    if (empty($post_ids)) {
        return [
            'views' => 0,
            'clicks' => 0,
            'clicks_detail' => [],
            'total_interactions' => 0,
            'period_days' => $days,
            'last_activity' => null
        ];
    }
    
    // Étape 2 : Récupérer event et value pour chaque post trouvé
    $post_ids_string = implode(',', array_map('intval', $post_ids));
    
    $stats_query = "
        SELECT 
            p.ID,
            MAX(CASE WHEN pm.meta_key = 'event' THEN pm.meta_value END) as event_type,
            MAX(CASE WHEN pm.meta_key = 'value' THEN pm.meta_value END) as value
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
            AND pm.meta_key IN ('event', 'value')
        WHERE p.ID IN ($post_ids_string)
        GROUP BY p.ID
    ";
    
    $results = $wpdb->get_results($stats_query, ARRAY_A);
    
    // Initialiser les stats
    $stats = [
        'views' => 0,
        'clicks' => 0,
        'clicks_detail' => [],
        'total_interactions' => 0,
        'period_days' => $days,
        'last_activity' => null
    ];
    
    // Traiter les résultats
    foreach ($results as $result) {
        $event_type = $result['event_type'];
        $value = (int)$result['value'];
        
        if ($event_type === 'view') {
            $stats['views'] += $value;
        } elseif (strpos($event_type, 'click_') === 0) {
            $click_type = str_replace('click_', '', $event_type);
            $stats['clicks'] += $value;
            $stats['clicks_detail'][$click_type] = ($stats['clicks_detail'][$click_type] ?? 0) + $value;
        }
    }
    
    $stats['total_interactions'] = $stats['views'] + $stats['clicks'];
    
    // Récupérer la dernière activité
    if (!empty($post_ids)) {
        $last_activity_query = "
            SELECT MAX(post_date) as last_date
            FROM {$wpdb->posts} 
            WHERE ID IN ($post_ids_string)
        ";
        $stats['last_activity'] = $wpdb->get_var($last_activity_query);
    }
    
    return $stats;
}

/**
 * Récupère les statistiques globales d'un utilisateur (toutes ses vCards)
 * 
 * @param int $user_id ID de l'utilisateur
 * @param int $days Période en jours
 * @return array Stats globales
 */
function nfc_get_user_global_stats($user_id, $days = 30) {
    // Récupérer toutes les vCards de l'utilisateur
    $user_vcards = nfc_get_user_vcard_profiles($user_id);
    
    $global_stats = [
        'total_cards' => count($user_vcards),
        'total_views' => 0,
        'total_clicks' => 0,
        'total_leads' => 0,
        'total_interactions' => 0,
        'top_performer' => null,
        'performance_by_card' => [],
        'period_days' => $days
    ];
    
    $top_performance = 0;
    
    foreach ($user_vcards as $card) {
        $vcard_id = $card['vcard_id'];
        $card_stats = nfc_get_vcard_quick_stats($vcard_id, $days);
        $card_leads = nfc_get_vcard_leads_count($vcard_id);
        
        // Cumuler les stats globales
        $global_stats['total_views'] += $card_stats['views'];
        $global_stats['total_clicks'] += $card_stats['clicks'];
        $global_stats['total_leads'] += $card_leads;
        
        // Performance de cette carte
        $card_performance = $card_stats['views'] + $card_stats['clicks'] + ($card_leads * 2);
        
        $global_stats['performance_by_card'][$vcard_id] = [
            'card_name' => nfc_format_vcard_full_name($card['vcard_data'] ?? []),
            'views' => $card_stats['views'],
            'clicks' => $card_stats['clicks'],
            'leads' => $card_leads,
            'performance_score' => $card_performance
        ];
        
        // Détecter le top performer
        if ($card_performance > $top_performance) {
            $top_performance = $card_performance;
            $global_stats['top_performer'] = [
                'vcard_id' => $vcard_id,
                'name' => nfc_format_vcard_full_name($card['vcard_data'] ?? []),
                'score' => $card_performance
            ];
        }
    }
    
    $global_stats['total_interactions'] = $global_stats['total_views'] + $global_stats['total_clicks'];
    
    return $global_stats;
}

// ================================================================================
// FONCTIONS LEADS/CONTACTS
// ================================================================================

/**
 * Compte le nombre de leads d'une vCard
 * 
 * @param int $vcard_id ID de la vCard
 * @return int Nombre de leads
 */
function nfc_get_vcard_leads_count($vcard_id) {
    global $wpdb;
    
    // Pattern exact pour les données ACF (format string)
    $exact_pattern = 'a:1:{i:0;s:' . strlen($vcard_id) . ':"' . $vcard_id . '";}';
    
    $leads_count_query = "
        SELECT COUNT(DISTINCT l.ID) as leads_count
        FROM {$wpdb->posts} l
        INNER JOIN {$wpdb->postmeta} pm_link 
            ON l.ID = pm_link.post_id 
            AND pm_link.meta_key = 'linked_virtual_card'
            AND pm_link.meta_value = %s
        WHERE l.post_type = 'lead'
        AND l.post_status = 'publish'
    ";
    
    $count = $wpdb->get_var($wpdb->prepare($leads_count_query, $exact_pattern));
    
    return (int)$count;
}

/**
 * Récupère les leads d'un utilisateur avec filtrage optionnel par vCard
 * 
 * @param int $user_id ID de l'utilisateur
 * @param int $vcard_filter ID vCard pour filtrer (0 = tous)
 * @param int $limit Nombre maximum de résultats
 * @return array Liste des leads
 */
function nfc_get_enterprise_contacts($user_id, $vcard_filter = 0, $limit = 50) {
    global $wpdb;
    
    // Récupérer les IDs des vCards de l'utilisateur
    $user_vcards = nfc_get_user_vcard_profiles($user_id);
    $vcard_ids = array_column($user_vcards, 'vcard_id');
    
    if (empty($vcard_ids)) {
        return [];
    }
    
    // Construire les patterns de recherche pour chaque vCard
    $vcard_like_conditions = [];
    $query_params = [];
    
    foreach ($vcard_ids as $vcard_id) {
        if ($vcard_filter > 0 && $vcard_id != $vcard_filter) {
            continue; // Skip cette vCard si on filtre
        }
        
        // Pattern ACF pour cette vCard
        $pattern = 'a:1:{i:0;s:' . strlen($vcard_id) . ':"' . $vcard_id . '";}';
        $vcard_like_conditions[] = 'pm_link.meta_value = %s';
        $query_params[] = $pattern;
    }
    
    if (empty($vcard_like_conditions)) {
        return [];
    }
    
    $vcard_where = '(' . implode(' OR ', $vcard_like_conditions) . ')';
    
    // Ajouter la limite
    $query_params[] = (int)$limit;
    
    $contacts_query = "
        SELECT DISTINCT l.ID, l.post_title, l.post_date,
               pm_firstname.meta_value as firstname,
               pm_lastname.meta_value as lastname,
               pm_email.meta_value as email,
               pm_mobile.meta_value as mobile,
               pm_society.meta_value as society,
               pm_post.meta_value as post_title,
               pm_vcard.meta_value as linked_vcard
        FROM {$wpdb->posts} l
        INNER JOIN {$wpdb->postmeta} pm_link 
            ON l.ID = pm_link.post_id 
            AND pm_link.meta_key = 'linked_virtual_card'
            AND {$vcard_where}
        LEFT JOIN {$wpdb->postmeta} pm_firstname ON l.ID = pm_firstname.post_id AND pm_firstname.meta_key = 'firstname'
        LEFT JOIN {$wpdb->postmeta} pm_lastname ON l.ID = pm_lastname.post_id AND pm_lastname.meta_key = 'lastname'
        LEFT JOIN {$wpdb->postmeta} pm_email ON l.ID = pm_email.post_id AND pm_email.meta_key = 'email'
        LEFT JOIN {$wpdb->postmeta} pm_mobile ON l.ID = pm_mobile.post_id AND pm_mobile.meta_key = 'mobile'
        LEFT JOIN {$wpdb->postmeta} pm_society ON l.ID = pm_society.post_id AND pm_society.meta_key = 'society'
        LEFT JOIN {$wpdb->postmeta} pm_post ON l.ID = pm_post.post_id AND pm_post.meta_key = 'post'
        LEFT JOIN {$wpdb->postmeta} pm_vcard ON l.ID = pm_vcard.post_id AND pm_vcard.meta_key = 'linked_virtual_card'
        WHERE l.post_type = 'lead'
        AND l.post_status = 'publish'
        ORDER BY l.post_date DESC
        LIMIT %d
    ";
    
    $results = $wpdb->get_results($wpdb->prepare($contacts_query, ...$query_params), ARRAY_A);
    
    return $results;
}

/**
 * Calcule la tendance d'évolution (pour affichage +X cette semaine)
 * 
 * @param int $vcard_id ID de la vCard
 * @param int $days Période à analyser
 * @return int Nombre d'éléments nouveaux dans la période
 */
function nfc_get_contacts_trend($vcard_id, $days = 7) {
    global $wpdb;
    
    $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
    $exact_pattern = 'a:1:{i:0;s:' . strlen($vcard_id) . ':"' . $vcard_id . '";}';
    
    $trend_query = "
        SELECT COUNT(DISTINCT l.ID) as new_count
        FROM {$wpdb->posts} l
        INNER JOIN {$wpdb->postmeta} pm_link 
            ON l.ID = pm_link.post_id 
            AND pm_link.meta_key = 'linked_virtual_card'
            AND pm_link.meta_value = %s
        WHERE l.post_type = 'lead'
        AND l.post_date >= %s
    ";
    
    $count = $wpdb->get_var($wpdb->prepare($trend_query, $exact_pattern, $date_limit));
    
    return (int)$count;
}

// ================================================================================
// FONCTIONS UTILITY
// ================================================================================

/**
 * Formate le nom complet depuis les métadonnées vCard
 * 
 * @param array $vcard_data Données de la vCard
 * @return string Nom formaté
 */
function nfc_format_vcard_full_name($vcard_data) {
    if (empty($vcard_data)) return 'Non configuré';
    
    $firstname = $vcard_data['firstname'] ?? $vcard_data['_firstname'] ?? '';
    $lastname = $vcard_data['lastname'] ?? $vcard_data['_lastname'] ?? '';
    
    if (is_array($firstname)) $firstname = $firstname[0] ?? '';
    if (is_array($lastname)) $lastname = $lastname[0] ?? '';
    
    $fullname = trim($firstname . ' ' . $lastname);
    return !empty($fullname) ? $fullname : 'Non configuré';
}

/**
 * Génère les boutons d'action pour une vCard
 * 
 * @param int $product_id ID de la vCard
 * @param array $options Options d'affichage
 * @return string HTML des boutons
 */
function nfc_generate_vcard_action_buttons($product_id, $options = []) {
    $buttons = '';
    
    if (isset($options['show_edit']) && $options['show_edit']) {
        $buttons .= '<a href="?page=vcard-edit&vcard_id=' . $product_id . '" class="btn btn-primary btn-sm me-1">';
        $buttons .= '<i class="fas fa-edit me-1"></i>Modifier</a>';
        
        $buttons .= '<a href="?page=statistics&vcard_id=' . $product_id . '" class="btn btn-outline-info btn-sm me-1">';
        $buttons .= '<i class="fas fa-chart-bar me-1"></i>Stats</a>';
        
        $buttons .= '<a href="?page=contacts&vcard_id=' . $product_id . '" class="btn btn-outline-success btn-sm me-1">';
        $buttons .= '<i class="fas fa-users me-1"></i>Leads</a>';
        
        if (isset($options['identifier'])) {
            $buttons .= '<a href="' . nfc_generate_renewal_url($options['identifier']) . '" class="btn btn-outline-warning btn-sm">';
            $buttons .= '<i class="fas fa-refresh me-1"></i>Renouveler</a>';
        }
    }
    
    return $buttons;
}

/**
 * Génère l'URL de renouvellement d'une carte
 * 
 * @param string $identifier Identifiant de la carte
 * @return string URL de renouvellement
 */
function nfc_generate_renewal_url($identifier) {
    // URL vers la boutique avec paramètre de renouvellement
    $base_url = get_permalink(wc_get_page_id('shop'));
    return add_query_arg([
        'renewal' => $identifier,
        'action' => 'renew_card'
    ], $base_url);
}