<?php
/**
 * Fonctions Communes NFC Dashboard
 * Fichier: wp-content/plugins/gtmi-vcard/includes/nfc-shared-functions.php
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
    
    // Requête pour récupérer les statistiques
    $stats_query = "
        SELECT 
            pm_event.meta_value as event_type,
            SUM(CAST(pm_value.meta_value AS UNSIGNED)) as total_value,
            COUNT(DISTINCT s.ID) as event_count
        FROM {$wpdb->posts} s
        INNER JOIN {$wpdb->postmeta} pm_vcard 
            ON s.ID = pm_vcard.post_id 
            AND pm_vcard.meta_key = 'virtual_card_id'
            AND pm_vcard.meta_value LIKE %s
        INNER JOIN {$wpdb->postmeta} pm_event 
            ON s.ID = pm_event.post_id 
            AND pm_event.meta_key = 'event'
        INNER JOIN {$wpdb->postmeta} pm_value 
            ON s.ID = pm_value.post_id 
            AND pm_value.meta_key = 'value'
        WHERE s.post_type = 'statistics'
        AND s.post_date >= %s
        GROUP BY pm_event.meta_value
    ";
    
    $search_pattern = '%"' . $vcard_id . '"%';
    $results = $wpdb->get_results($wpdb->prepare($stats_query, $search_pattern, $date_limit), ARRAY_A);
    
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
        $total_value = (int)$result['total_value'];
        
        if ($event_type === 'view') {
            $stats['views'] = $total_value;
        } elseif (strpos($event_type, 'click_') === 0) {
            $click_type = str_replace('click_', '', $event_type);
            $stats['clicks'] += $total_value;
            $stats['clicks_detail'][$click_type] = $total_value;
        }
    }
    
    $stats['total_interactions'] = $stats['views'] + $stats['clicks'];
    
    // Récupérer la dernière activité
    $last_activity_query = "
        SELECT MAX(s.post_date) as last_date
        FROM {$wpdb->posts} s
        INNER JOIN {$wpdb->postmeta} pm_vcard 
            ON s.ID = pm_vcard.post_id 
            AND pm_vcard.meta_key = 'virtual_card_id'
            AND pm_vcard.meta_value LIKE %s
        WHERE s.post_type = 'statistics'
    ";
    
    $last_date = $wpdb->get_var($wpdb->prepare($last_activity_query, $search_pattern));
    $stats['last_activity'] = $last_date;
    
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
        $card_performance = $card_stats['views'] + $card_stats['clicks'] + ($card_leads * 2); // Les leads comptent double
        
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
    
    $leads_count_query = "
        SELECT COUNT(DISTINCT l.ID) as leads_count
        FROM {$wpdb->posts} l
        INNER JOIN {$wpdb->postmeta} pm_link 
            ON l.ID = pm_link.post_id 
            AND pm_link.meta_key = 'linked_virtual_card'
            AND pm_link.meta_value LIKE %s
        WHERE l.post_type = 'lead'
        AND l.post_status = 'publish'
    ";
    
    $search_pattern = '%"' . $vcard_id . '"%';
    $count = $wpdb->get_var($wpdb->prepare($leads_count_query, $search_pattern));
    
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
    
    // Construire la clause WHERE pour les vCards
    $vcard_placeholders = implode(',', array_fill(0, count($vcard_ids), '%s'));
    $vcard_like_conditions = [];
    $query_params = [];
    
    foreach ($vcard_ids as $vcard_id) {
        if ($vcard_filter > 0 && $vcard_id != $vcard_filter) {
            continue; // Skip cette vCard si on filtre
        }
        $vcard_like_conditions[] = 'pm_link.meta_value LIKE %s';
        $query_params[] = '%"' . $vcard_id . '"%';
    }
    
    if (empty($vcard_like_conditions)) {
        return [];
    }
    
    $vcard_where = '(' . implode(' OR ', $vcard_like_conditions) . ')';
    
    // Requête principale
    $contacts_query = "
        SELECT 
            l.ID as lead_id,
            l.post_title,
            l.post_date as contact_date,
            pm_fn.meta_value as firstname,
            pm_ln.meta_value as lastname,
            pm_em.meta_value as email,
            pm_mob.meta_value as mobile,
            pm_soc.meta_value as society,
            pm_post.meta_value as post,
            pm_dt.meta_value as contact_datetime,
            pm_link.meta_value as linked_vcard_raw
        FROM {$wpdb->posts} l
        INNER JOIN {$wpdb->postmeta} pm_link 
            ON l.ID = pm_link.post_id 
            AND pm_link.meta_key = 'linked_virtual_card'
        LEFT JOIN {$wpdb->postmeta} pm_fn ON l.ID = pm_fn.post_id AND pm_fn.meta_key = 'firstname'
        LEFT JOIN {$wpdb->postmeta} pm_ln ON l.ID = pm_ln.post_id AND pm_ln.meta_key = 'lastname'
        LEFT JOIN {$wpdb->postmeta} pm_em ON l.ID = pm_em.post_id AND pm_em.meta_key = 'email'
        LEFT JOIN {$wpdb->postmeta} pm_mob ON l.ID = pm_mob.post_id AND pm_mob.meta_key = 'mobile'
        LEFT JOIN {$wpdb->postmeta} pm_soc ON l.ID = pm_soc.post_id AND pm_soc.meta_key = 'society'
        LEFT JOIN {$wpdb->postmeta} pm_post ON l.ID = pm_post.post_id AND pm_post.meta_key = 'post'
        LEFT JOIN {$wpdb->postmeta} pm_dt ON l.ID = pm_dt.post_id AND pm_dt.meta_key = 'contact_datetime'
        WHERE l.post_type = 'lead'
        AND l.post_status = 'publish'
        AND {$vcard_where}
        ORDER BY l.post_date DESC
        LIMIT %d
    ";
    
    $query_params[] = $limit;
    $contacts = $wpdb->get_results($wpdb->prepare($contacts_query, ...$query_params), ARRAY_A);
    
    // Enrichir les données avec les infos vCard
    foreach ($contacts as &$contact) {
        // Extraire l'ID vCard du champ sérialisé  
        $linked_vcard_raw = $contact['linked_vcard_raw'];
        preg_match('/s:4:"(\d+)"/', $linked_vcard_raw, $matches);
        $vcard_id = isset($matches[1]) ? (int)$matches[1] : 0;
        
        $contact['vcard_id'] = $vcard_id;
        
        // Trouver les infos de la vCard correspondante
        $vcard_info = null;
        foreach ($user_vcards as $card) {
            if ($card['vcard_id'] == $vcard_id) {
                $vcard_info = $card;
                break;
            }
        }
        
        if ($vcard_info) {
            $contact['vcard_firstname'] = $vcard_info['vcard_data']['firstname'] ?? '';
            $contact['vcard_lastname'] = $vcard_info['vcard_data']['lastname'] ?? '';
            $contact['vcard_full_name'] = nfc_format_vcard_full_name($vcard_info['vcard_data'] ?? []);
        } else {
            $contact['vcard_firstname'] = '';
            $contact['vcard_lastname'] = '';
            $contact['vcard_full_name'] = 'vCard introuvable';
        }
        
        // Formatage des données
        $contact['full_name'] = trim(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? ''));
        if (empty($contact['full_name'])) {
            $contact['full_name'] = 'Contact sans nom';
        }
        
        $contact['formatted_date'] = date('d/m/Y H:i', strtotime($contact['contact_date']));
    }
    
    return $contacts;
}

/**
 * Groupe les contacts par profil vCard pour les statistiques
 * 
 * @param array $contacts Liste des contacts
 * @return array Contacts groupés par vCard ID
 */
function nfc_group_contacts_by_profile($contacts) {
    $grouped = [];
    
    foreach ($contacts as $contact) {
        $vcard_id = $contact['vcard_id'];
        if (!isset($grouped[$vcard_id])) {
            $grouped[$vcard_id] = [];
        }
        $grouped[$vcard_id][] = $contact;
    }
    
    return $grouped;
}

// ================================================================================
// FONCTIONS UTILITAIRES
// ================================================================================

/**
 * Formate un badge de statut HTML
 * 
 * @param string $status Statut à formatter
 * @return string HTML du badge
 */
function nfc_render_status_badge($status) {
    $badges = [
        'active' => '<span class="badge bg-success">Actif</span>',
        'configured' => '<span class="badge bg-info">Configuré</span>', 
        'pending' => '<span class="badge bg-warning">En attente</span>',
        'inactive' => '<span class="badge bg-secondary">Inactif</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-light">Inconnu</span>';
}

/**
 * Génère les boutons d'actions pour un produit
 * 
 * @param string $type Type de produit (vcard, google_reviews)
 * @param int $product_id ID du produit
 * @param array $options Options supplémentaires
 * @return string HTML des boutons
 */
function nfc_render_action_buttons($type, $product_id, $options = []) {
    $buttons = '';
    
    if ($type === 'vcard') {
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
 * Calcule la tendance d'évolution (pour affichage +X cette semaine)
 * 
 * @param int $vcard_id ID de la vCard
 * @param int $days Période à analyser
 * @return int Nombre d'éléments nouveaux dans la période
 */
function nfc_get_contacts_trend($vcard_id, $days = 7) {
    global $wpdb;
    
    $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
    
    $trend_query = "
        SELECT COUNT(DISTINCT l.ID) as new_count
        FROM {$wpdb->posts} l
        INNER JOIN {$wpdb->postmeta} pm_link 
            ON l.ID = pm_link.post_id 
            AND pm_link.meta_key = 'linked_virtual_card'
            AND pm_link.meta_value LIKE %s
        WHERE l.post_type = 'lead'
        AND l.post_date >= %s
    ";
    
    $search_pattern = '%"' . $vcard_id . '"%';
    $count = $wpdb->get_var($wpdb->prepare($trend_query, $search_pattern, $date_limit));
    
    return (int)$count;
}

/**
 * Debug - Affiche les données utilisateur enterprise
 * Fonction temporaire pour le développement
 */
function nfc_debug_enterprise_data($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px; border: 1px solid #ccc;'>";
    echo "<h3>Debug Enterprise Data - User #$user_id</h3>";
    
    $cards = nfc_get_user_vcard_profiles($user_id);
    echo "<h4>Cartes trouvées : " . count($cards) . "</h4>";
    
    foreach ($cards as $card) {
        $stats = nfc_get_vcard_quick_stats($card['vcard_id']);
        $leads_count = nfc_get_vcard_leads_count($card['vcard_id']);
        
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px;'>";
        echo "<strong>vCard ID: {$card['vcard_id']}</strong><br>";
        echo "Nom: " . nfc_format_vcard_full_name($card['vcard_data'] ?? []) . "<br>";
        echo "Stats 30j: {$stats['views']} vues, {$stats['clicks']} clics<br>";
        echo "Leads: {$leads_count}<br>";
        echo "</div>";
    }
    
    $global_stats = nfc_get_user_global_stats($user_id);
    echo "<h4>Stats globales (30j)</h4>";
    echo "Vues totales: {$global_stats['total_views']}<br>";
    echo "Clics totaux: {$global_stats['total_clicks']}<br>";
    echo "Leads totaux: {$global_stats['total_leads']}<br>";
    if ($global_stats['top_performer']) {
        echo "Top performer: {$global_stats['top_performer']['name']} (score: {$global_stats['top_performer']['score']})<br>";
    }
    
    echo "</div>";
}