<?php
/**
 * 🔥 SOLUTION FINALE - Fonctions Communes NFC Dashboard
 * Fichier: wp-content/plugins/gtmi-vcard/includes/dashboard/nfc-shared-functions.php
 * 
 * PROBLÈME RÉSOLU : L'API utilise 'vcard_id' mais ACF définit 'virtual_card_id'
 * SOLUTION : Chercher avec les DEUX clés pour garantir la compatibilité
 */

if (!defined('ABSPATH')) {
    exit;
}

// ================================================================================
// FONCTIONS STATISTIQUES - VERSION FINALE CORRIGÉE
// ================================================================================

/**
 * 🔥 SOLUTION FINALE - Récupère les statistiques rapides d'une vCard
 * 
 * @param int $vcard_id ID de la vCard
 * @param int $days Période en jours (défaut: 30)
 * @return array Stats formatées
 */
function nfc_get_vcard_quick_stats($vcard_id, $days = 30) {
    global $wpdb;
    
    // Date limite
    $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
    
    // Pattern exact comme dans tes données
    $exact_pattern = 'a:1:{i:0;s:' . strlen($vcard_id) . ':"' . $vcard_id . '";}';
    
    // 🔥 FIX : Requête step-by-step au lieu de gros INNER JOIN
    
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
        error_log("🔥 DEBUG: Aucun post trouvé pour vCard $vcard_id avec pattern $exact_pattern");
        return [
            'views' => 0,
            'clicks' => 0,
            'clicks_detail' => [],
            'total_interactions' => 0,
            'period_days' => $days,
            'last_activity' => null
        ];
    }
    
    error_log("🔥 DEBUG: " . count($post_ids) . " posts trouvés pour vCard $vcard_id : " . implode(',', $post_ids));
    
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
    
    error_log("🔥 DEBUG: " . count($results) . " résultats avec event/value");
    
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
        
        error_log("🔥 DEBUG: Post {$result['ID']} - Event: $event_type - Value: $value");
        
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
 * 🔥 SOLUTION FINALE - Compte le nombre de leads d'une vCard
 * 
 * @param int $vcard_id ID de la vCard
 * @return int Nombre de leads
 */
function nfc_get_vcard_leads_count($vcard_id) {
    global $wpdb;
    
    // Pattern exact comme pour les stats
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
    
    error_log("🔥 DEBUG Leads vCard $vcard_id: $count leads trouvés avec pattern $exact_pattern");
    
    return (int)$count;
}


/**
 * 🔥 NOUVEAU - Debug complet pour identifier le format exact des données
 */
function nfc_debug_vcard_data_complete($vcard_id) {
    global $wpdb;
    
    echo "<div style='background: #fffbf0; padding: 15px; margin: 10px; border: 2px solid #f0c674;'>";
    echo "<h3>🔍 DEBUG COMPLET Format Données vCard #$vcard_id</h3>";
    
    // 1. Vérifier posts statistics avec toutes les clés
    echo "<h4>1. Posts Statistics - Recherche Multi-clés</h4>";
    
    $meta_keys = ['vcard_id', 'virtual_card_id'];
    foreach ($meta_keys as $meta_key) {
        echo "<h5>Recherche avec clé: <code>$meta_key</code></h5>";
        
        $stats_query = "
            SELECT s.ID, s.post_title, s.post_date, 
                   pm_vcard.meta_value as vcard_value, 
                   pm_event.meta_value as event_type, 
                   pm_value.meta_value as value
            FROM {$wpdb->posts} s
            LEFT JOIN {$wpdb->postmeta} pm_vcard ON s.ID = pm_vcard.post_id AND pm_vcard.meta_key = %s
            LEFT JOIN {$wpdb->postmeta} pm_event ON s.ID = pm_event.post_id AND pm_event.meta_key = 'event'
            LEFT JOIN {$wpdb->postmeta} pm_value ON s.ID = pm_value.post_id AND pm_value.meta_key = 'value'
            WHERE s.post_type = 'statistics'
            AND (pm_vcard.meta_value = %s OR pm_vcard.meta_value LIKE %s)
            ORDER BY s.post_date DESC
            LIMIT 5
        ";
        
        $results = $wpdb->get_results($wpdb->prepare($stats_query, 
            $meta_key,
            $vcard_id,
            '%"' . $vcard_id . '"%'
        ), ARRAY_A);
        
        echo "<div style='margin-left: 20px;'>";
        if (empty($results)) {
            echo "<p style='color: #d63384;'>❌ Aucun résultat avec la clé <code>$meta_key</code></p>";
        } else {
            echo "<p style='color: #198754;'>✅ " . count($results) . " résultats trouvés avec <code>$meta_key</code></p>";
            foreach ($results as $result) {
                echo "<div style='border: 1px solid #ddd; padding: 8px; margin: 4px; background: white; font-size: 12px;'>";
                echo "<strong>ID:</strong> {$result['ID']} | <strong>Date:</strong> {$result['post_date']}<br>";
                echo "<strong>{$meta_key}:</strong> <code>" . esc_html($result['vcard_value']) . "</code><br>";
                echo "<strong>Event:</strong> {$result['event_type']} | <strong>Value:</strong> {$result['value']}<br>";
                echo "</div>";
            }
        }
        echo "</div>";
    }
    
    // 2. Vérifier toutes les métadonnées d'un post statistics récent
    echo "<h4>2. Analyse Métadonnées Post Statistics</h4>";
    
    $recent_stats = $wpdb->get_results("
        SELECT ID, post_title, post_date 
        FROM {$wpdb->posts} 
        WHERE post_type = 'statistics' 
        ORDER BY post_date DESC 
        LIMIT 3
    ");
    
    foreach ($recent_stats as $stat) {
        echo "<h5>Post #{$stat->ID} - {$stat->post_title}</h5>";
        
        $meta_data = $wpdb->get_results($wpdb->prepare("
            SELECT meta_key, meta_value 
            FROM {$wpdb->postmeta} 
            WHERE post_id = %d
        ", $stat->ID), ARRAY_A);
        
        echo "<div style='margin-left: 20px; background: #f8f9fa; padding: 10px;'>";
        foreach ($meta_data as $meta) {
            echo "<div><strong>{$meta['meta_key']}:</strong> <code>" . esc_html($meta['meta_value']) . "</code></div>";
        }
        echo "</div>";
    }
    
    // 3. Test des fonctions corrigées
    echo "<h4>3. Test Fonctions Corrigées</h4>";
    
    $stats = nfc_get_vcard_quick_stats($vcard_id);
    $leads_count = nfc_get_vcard_leads_count($vcard_id);
    
    echo "<div style='background: #e8f4f8; padding: 15px; margin: 10px;'>";
    echo "<h5>Résultats des fonctions corrigées :</h5>";
    echo "<strong>Vues (30j):</strong> {$stats['views']}<br>";
    echo "<strong>Clics (30j):</strong> {$stats['clicks']}<br>";
    echo "<strong>Leads total:</strong> {$leads_count}<br>";
    echo "<strong>Dernière activité:</strong> {$stats['last_activity']}<br>";
    echo "</div>";
    
    echo "</div>";
}

/**
 * 🔥 FIX - Debug enterprise data avec diagnostic complet
 */
function nfc_debug_enterprise_data($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px; border: 1px solid #ccc;'>";
    echo "<h3>🔥 Debug Enterprise Data SOLUTION FINALE - User #$user_id</h3>";
    
    // Test avec les vCards de test
    $test_vcards = [1013, 3736, 3737, 3738, 3739, 3740];
    
    echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";
    
    foreach ($test_vcards as $vcard_id) {
        echo "<div>";
        echo "<h4>🧪 Test vCard #$vcard_id</h4>";
        
        // Debug complet pour cette vCard
        nfc_debug_vcard_data_complete($vcard_id);
        
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
}

// ================================================================================
// RESTE DES FONCTIONS (optimisées)
// ================================================================================

/**
 * Récupère les statistiques globales d'un utilisateur (toutes ses vCards)
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
// FONCTIONS UTILITY 
// ================================================================================

/**
 * Formate le nom complet depuis les métadonnées vCard
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
 * Calcule la tendance d'évolution
 */
function nfc_get_contacts_trend($vcard_id, $days = 7) {
    global $wpdb;
    
    $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
    
    // Recherche multi-format pour les leads (format string ACF)
    $search_conditions = [
        "pm_link.meta_value = %s",
        "pm_link.meta_value LIKE %s", 
        "pm_link.meta_value LIKE %s",
        "pm_link.meta_value LIKE %s",
        "pm_link.meta_value LIKE %s"
    ];
    
    $query_params = [
        $vcard_id,
        '%"' . $vcard_id . '"%',
        '%:' . $vcard_id . ';%',
        'a:1:{i:0;i:' . $vcard_id . ';}',
        $date_limit
    ];
    
    $where_clause = '(' . implode(' OR ', $search_conditions) . ')';
    
    $trend_query = "
        SELECT COUNT(DISTINCT l.ID) as new_count
        FROM {$wpdb->posts} l
        INNER JOIN {$wpdb->postmeta} pm_link 
            ON l.ID = pm_link.post_id 
            AND pm_link.meta_key = 'linked_virtual_card'
            AND {$where_clause}
        WHERE l.post_type = 'lead'
        AND l.post_date >= %s
    ";
    
    $count = $wpdb->get_var($wpdb->prepare($trend_query, ...$query_params));
    
    return (int)$count;
}


function nfc_test_sql_direct($vcard_id = 3736) {
    global $wpdb;
    
    echo "<div style='background: #fff3cd; padding: 15px; margin: 10px; border: 2px solid #ffc107;'>";
    echo "<h3>🔥 TEST SQL DIRECT vCard #$vcard_id</h3>";
    
    // 1. TEST SIMPLE : chercher exactement ce qu'on sait qui existe
    echo "<h4>1. Test avec pattern exact connu</h4>";
    
    $exact_pattern = 'a:1:{i:0;s:4:"' . $vcard_id . '";}';
    echo "<p>Pattern exact cherché : <code>$exact_pattern</code></p>";
    
    $simple_query = "
        SELECT s.ID, s.post_title, s.post_date,
               pm_vcard.meta_value as vcard_value,
               pm_event.meta_value as event_type,
               pm_value.meta_value as value
        FROM {$wpdb->posts} s
        INNER JOIN {$wpdb->postmeta} pm_vcard 
            ON s.ID = pm_vcard.post_id 
            AND pm_vcard.meta_key = 'virtual_card_id'
            AND pm_vcard.meta_value = %s
        INNER JOIN {$wpdb->postmeta} pm_event 
            ON s.ID = pm_event.post_id 
            AND pm_event.meta_key = 'event'
        INNER JOIN {$wpdb->postmeta} pm_value 
            ON s.ID = pm_value.post_id 
            AND pm_value.meta_key = 'value'
        WHERE s.post_type = 'statistics'
        ORDER BY s.post_date DESC
    ";
    
    $results = $wpdb->get_results($wpdb->prepare($simple_query, $exact_pattern), ARRAY_A);
    
    echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px;'>";
    echo "<strong>Résultats du test direct :</strong><br>";
    if (empty($results)) {
        echo "<span style='color: red;'>❌ AUCUN résultat avec pattern exact</span><br>";
        echo "<strong>Query SQL finale :</strong><br>";
        echo "<pre style='background: #e9ecef; padding: 5px; font-size: 11px;'>" . $wpdb->last_query . "</pre>";
    } else {
        echo "<span style='color: green;'>✅ " . count($results) . " résultat(s) trouvé(s) !</span><br>";
        foreach ($results as $result) {
            echo "<div style='border: 1px solid #ddd; padding: 5px; margin: 5px; background: white;'>";
            echo "<strong>ID:</strong> {$result['ID']} | <strong>Event:</strong> {$result['event_type']} | <strong>Value:</strong> {$result['value']}<br>";
            echo "<strong>vCard meta:</strong> <code>" . esc_html($result['vcard_value']) . "</code><br>";
            echo "</div>";
        }
    }
    echo "</div>";
    
    // 2. TEST AVEC LIKE pour voir si ça marche
    echo "<h4>2. Test avec LIKE pattern</h4>";
    
    $like_pattern = '%' . $exact_pattern . '%';
    echo "<p>Pattern LIKE : <code>$like_pattern</code></p>";
    
    $like_query = str_replace('pm_vcard.meta_value = %s', 'pm_vcard.meta_value LIKE %s', $simple_query);
    $like_results = $wpdb->get_results($wpdb->prepare($like_query, $like_pattern), ARRAY_A);
    
    echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px;'>";
    if (empty($like_results)) {
        echo "<span style='color: red;'>❌ AUCUN résultat avec LIKE</span><br>";
    } else {
        echo "<span style='color: green;'>✅ " . count($like_results) . " résultat(s) avec LIKE !</span><br>";
    }
    echo "</div>";
    
    // 3. TEST ENCORE PLUS SIMPLE : voir tous les posts statistics récents
    echo "<h4>3. Tous les posts statistics récents</h4>";
    
    $all_stats = $wpdb->get_results("
        SELECT s.ID, s.post_title, pm.meta_key, pm.meta_value
        FROM {$wpdb->posts} s
        LEFT JOIN {$wpdb->postmeta} pm ON s.ID = pm.post_id
        WHERE s.post_type = 'statistics'
        AND pm.meta_key IN ('virtual_card_id', 'vcard_id', 'event', 'value')
        ORDER BY s.ID DESC
        LIMIT 20
    ");
    
    echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px; max-height: 300px; overflow-y: scroll;'>";
    $current_post = null;
    foreach ($all_stats as $stat) {
        if ($current_post != $stat->ID) {
            if ($current_post !== null) echo "</div>";
            echo "<div style='border: 1px solid #ddd; padding: 5px; margin: 5px; background: white;'>";
            echo "<strong>Post #{$stat->ID} - {$stat->post_title}</strong><br>";
            $current_post = $stat->ID;
        }
        echo "<code>{$stat->meta_key}:</code> " . esc_html($stat->meta_value) . "<br>";
    }
    if ($current_post !== null) echo "</div>";
    echo "</div>";
    
    echo "</div>";
}

/**
 * 🔥 TEST RAPIDE pour vérifier
 */
function nfc_test_stats_fix($vcard_id = 3736) {
    echo "<div style='background: #d4edda; padding: 15px; margin: 10px; border: 2px solid #28a745;'>";
    echo "<h3>🔥 TEST SOLUTION CORRIGÉE - vCard #$vcard_id</h3>";
    
    $stats = nfc_get_vcard_quick_stats($vcard_id);
    $leads = nfc_get_vcard_leads_count($vcard_id);
    
    echo "<div style='background: white; padding: 15px; margin: 10px; border: 1px solid #ddd;'>";
    echo "<h4>Résultats :</h4>";
    echo "<strong>Vues (30j):</strong> {$stats['views']}<br>";
    echo "<strong>Clics (30j):</strong> {$stats['clicks']}<br>";
    echo "<strong>Détail clics:</strong> " . json_encode($stats['clicks_detail']) . "<br>";
    echo "<strong>Total interactions:</strong> {$stats['total_interactions']}<br>";
    echo "<strong>Leads:</strong> {$leads}<br>";
    echo "<strong>Dernière activité:</strong> {$stats['last_activity']}<br>";
    echo "</div>";
    
    echo "</div>";
}