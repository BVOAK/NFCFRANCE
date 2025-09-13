<?php
/**
 * Fonctions Utilitaires NFC Enterprise
 * Fichier: wp-content/plugins/gtmi-vcard/includes/enterprise/enterprise-functions.php
 */

if (!defined('ABSPATH')) {
    exit;
}
/**
 * Récupère tous les profils vCard d'un utilisateur
 * NOUVEAU : Dashboard unique - pas de distinction simple/enterprise
 */
function nfc_get_user_vcard_profiles($user_id) {
    return NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
}

/**
 * Génère URL de renouvellement pour une carte
 */
function nfc_generate_renewal_url($card_identifier) {
    $base_url = home_url('/boutique-nfc/');
    $params = [
        'context' => 'renewal',
        'card_id' => $card_identifier
    ];
    
    return add_query_arg($params, $base_url);
}

/**
 * Génère URL d'ajout de cartes pour enterprise existante
 */
function nfc_generate_additional_cards_url($user_id) {
    $base_url = home_url('/boutique-nfc/');
    $params = [
        'context' => 'additional_cards',
        'user_id' => $user_id
    ];
    
    return add_query_arg($params, $base_url);
}

/**
 * Formate nom complet d'une vCard
 */
function nfc_format_vcard_full_name($vcard_data) {
    $firstname = $vcard_data['firstname'] ?? '';
    $lastname = $vcard_data['lastname'] ?? '';
    
    $full_name = trim($firstname . ' ' . $lastname);
    
    if (empty($full_name)) {
        return 'Profil à configurer';
    }
    
    return $full_name;
}

/**
 * Formate poste/service d'une vCard
 */
function nfc_format_vcard_position($vcard_data) {
    $service = $vcard_data['service'] ?? '';
    $society = $vcard_data['society'] ?? '';
    
    if (!empty($service) && !empty($society)) {
        return $service . ' - ' . $society;
    } elseif (!empty($service)) {
        return $service;
    } elseif (!empty($society)) {
        return $society;
    }
    
    return 'Poste à définir';
}

/**
 * Détermine le statut d'affichage d'une carte
 */
function nfc_get_card_display_status($card) {
    if (!$card['vcard_data']['is_configured']) {
        return [
            'status' => 'to_configure',
            'label' => 'À configurer',
            'color' => 'warning',
            'icon' => 'fas fa-exclamation-triangle'
        ];
    }
    
    if ($card['stats']['views'] > 0) {
        return [
            'status' => 'active',
            'label' => 'Active',
            'color' => 'success',
            'icon' => 'fas fa-check-circle'
        ];
    }
    
    return [
        'status' => 'configured',
        'label' => 'Configurée',
        'color' => 'info',
        'icon' => 'fas fa-info-circle'
    ];
}

/**
 * Calcule pourcentage de cartes configurées
 */
function nfc_calculate_configuration_percentage($cards) {
    if (empty($cards)) {
        return 0;
    }
    
    $configured = 0;
    foreach ($cards as $card) {
        if ($card['vcard_data']['is_configured']) {
            $configured++;
        }
    }
    
    return round(($configured / count($cards)) * 100);
}

/**
 * Génère données pour graphique dashboard
 */
function nfc_generate_dashboard_chart_data($cards, $period_days = 30) {
    $chart_data = [];
    $start_date = date('Y-m-d', strtotime("-{$period_days} days"));
    
    // Initialiser les dates
    for ($i = $period_days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $chart_data[$date] = [
            'date' => date('d/m', strtotime($date)),
            'views' => 0,
            'contacts' => 0
        ];
    }
    
    // Récupérer stats de toutes les cartes
    foreach ($cards as $card) {
        $vcard_stats = nfc_get_vcard_daily_stats($card['vcard_id'], $start_date);
        
        foreach ($vcard_stats as $stat) {
            $date = $stat['date'];
            if (isset($chart_data[$date])) {
                $chart_data[$date]['views'] += $stat['views'];
                $chart_data[$date]['contacts'] += $stat['contacts'];
            }
        }
    }
    
    return array_values($chart_data);
}

/**
 * Récupère stats quotidiennes d'une vCard
 */
function nfc_get_vcard_daily_stats($vcard_id, $start_date) {
    global $wpdb;
    
    // Utiliser la table des stats existante
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT 
            DATE(p.post_date) as date,
            pm_event.meta_value as event,
            SUM(CAST(pm_value.meta_value AS UNSIGNED)) as total
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm_vcard ON p.ID = pm_vcard.post_id AND pm_vcard.meta_key = 'virtual_card_id'
        LEFT JOIN {$wpdb->postmeta} pm_event ON p.ID = pm_event.post_id AND pm_event.meta_key = 'event' 
        LEFT JOIN {$wpdb->postmeta} pm_value ON p.ID = pm_value.post_id AND pm_value.meta_key = 'value'
        WHERE p.post_type = 'statistics'
        AND pm_vcard.meta_value = %d
        AND DATE(p.post_date) >= %s
        GROUP BY DATE(p.post_date), pm_event.meta_value
        ORDER BY DATE(p.post_date) ASC
    ", $vcard_id, $start_date), ARRAY_A);
    
    // Organiser par date
    $daily_stats = [];
    foreach ($results as $result) {
        $date = $result['date'];
        if (!isset($daily_stats[$date])) {
            $daily_stats[$date] = [
                'date' => $date,
                'views' => 0,
                'contacts' => 0
            ];
        }
        
        if ($result['event'] === 'view') {
            $daily_stats[$date]['views'] = intval($result['total']);
        } elseif ($result['event'] === 'contact') {
            $daily_stats[$date]['contacts'] = intval($result['total']);
        }
    }
    
    return array_values($daily_stats);
}

/**
 * Gère la logique de renouvellement
 */
function nfc_handle_card_renewal($card_identifier, $new_order_id) {
    $card = NFC_Enterprise_Core::get_vcard_by_identifier($card_identifier);
    
    if (!$card) {
        return false;
    }
    
    // Mettre à jour l'historique de renouvellement
    $renewal_history = get_field('enterprise_renewal_history', $card['vcard_id']);
    $history = !empty($renewal_history) ? json_decode($renewal_history, true) : [];
    
    $history[] = [
        'date' => current_time('Y-m-d H:i:s'),
        'old_order_id' => $card['order_id'],
        'new_order_id' => $new_order_id,
        'status' => 'renewed'
    ];
    
    // Sauvegarder nouvel historique
    update_field('enterprise_renewal_history', json_encode($history), $card['vcard_id']);
    
    // Mettre à jour l'order_id dans la table enterprise
    global $wpdb;
    $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
    
    $wpdb->update(
        $table_name,
        ['order_id' => $new_order_id],
        ['card_identifier' => $card_identifier]
    );
    
    // Log
    error_log("NFC Enterprise: Card {$card_identifier} renewed with order {$new_order_id}");
    
    return true;
}

/**
 * Notifications email pour enterprise
 */
function nfc_send_enterprise_notification($order_id, $created_vcards) {
    $order = wc_get_order($order_id);
    if (!$order) return;
    
    $to = $order->get_billing_email();
    $subject = sprintf(
        'Vos %d cartes NFC sont prêtes - Commande #%d',
        count($created_vcards),
        $order_id
    );
    
    // Construire contenu email
    $content = "Bonjour " . $order->get_billing_first_name() . ",\n\n";
    $content .= "Vos " . count($created_vcards) . " cartes vCard ont été créées avec succès.\n\n";
    $content .= "Identifiants de vos cartes :\n";
    
    foreach ($created_vcards as $vcard) {
        $vcard_url = get_permalink($vcard['vcard_id']);
        $content .= "- {$vcard['identifier']} : {$vcard_url}\n";
    }
    
    $dashboard_url = home_url('/mon-compte/dashboard-nfc/');
    $content .= "\nAccédez à votre dashboard pour configurer vos profils :\n";
    $content .= $dashboard_url . "\n\n";
    $content .= "Cordialement,\nNFC France";
    
    wp_mail($to, $subject, $content);
}

// Hook pour envoyer email après création
add_action('nfc_enterprise_vcards_created', 'nfc_send_enterprise_notification', 10, 2);

/**
 * Helper pour debug/développement
 */
function nfc_debug_enterprise_data($user_id = null) {
    if (!current_user_can('administrator')) return;
    
    $user_id = $user_id ?: get_current_user_id();
    
    echo "<h3>Debug Enterprise Data - User #$user_id</h3>";
    
    $cards = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
    echo "<h4>Cartes trouvées : " . count($cards) . "</h4>";
    
    foreach ($cards as $card) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
        echo "<strong>Carte {$card['card_identifier']}</strong><br>";
        echo "vCard ID: {$card['vcard_id']}<br>";
        echo "Status: {$card['card_status']}<br>";
        echo "Nom: " . nfc_format_vcard_full_name($card['vcard_data']) . "<br>";
        echo "Stats: {$card['stats']['views']} vues, {$card['stats']['contacts']} contacts<br>";
        echo "</div>";
    }
    
    $global_stats = NFC_Enterprise_Core::get_user_global_stats($user_id);
    echo "<h4>Stats globales</h4>";
    echo "<pre>" . print_r($global_stats, true) . "</pre>";
}

// Fonction d'activation du plugin (à appeler dans le main plugin file)
function nfc_enterprise_activate() {
    NFC_Enterprise_Core::create_database_tables();
    flush_rewrite_rules();
}

/**
 * Récupère tous les profils Avis Google d'un utilisateur
 */
function nfc_get_user_google_reviews_profiles($user_id) {
    // Pour l'instant retourne un array vide, on implémentera plus tard
    return [];
}

/**
 * Résumé des produits d'un utilisateur pour le dashboard
 */
function nfc_get_user_products_summary($user_id) {
    $vcard_profiles = nfc_get_user_vcard_profiles($user_id);
    $google_reviews_profiles = nfc_get_user_google_reviews_profiles($user_id);
    
    return [
        'vcard_profiles' => $vcard_profiles,
        'google_reviews_profiles' => $google_reviews_profiles,
        'has_vcard' => !empty($vcard_profiles),
        'has_google_reviews' => !empty($google_reviews_profiles),
        'total_products' => count($vcard_profiles) + count($google_reviews_profiles)
    ];
}

function nfc_get_dashboard_type($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    // Récupérer le résumé des produits
    $products_summary = nfc_get_user_products_summary($user_id);
    
    $vcard_count = count($products_summary['vcard_profiles'] ?? []);
    $google_reviews_count = count($products_summary['google_reviews_profiles'] ?? []);
    
    // Logique de détermination du type
    if ($vcard_count == 0 && $google_reviews_count == 0) {
        return 'empty';
    } elseif ($vcard_count == 1 && $google_reviews_count == 0) {
        return 'simple';
    } elseif ($vcard_count > 1 && $google_reviews_count == 0) {
        return 'enterprise';
    } elseif ($vcard_count == 0 && $google_reviews_count > 0) {
        return 'google_reviews';
    } else {
        return 'mixed'; // vCard + Avis Google
    }
}

/**
 * Détermine si un utilisateur a un dashboard entreprise
 * (plus de 1 vCard ou des profils Avis Google)
 * 
 * @param int $user_id
 * @return bool
 */
function nfc_user_has_enterprise_dashboard($user_id = null) {
    $dashboard_type = nfc_get_dashboard_type($user_id);
    return in_array($dashboard_type, ['enterprise', 'mixed', 'google_reviews']);
}

/**
 * Récupère le titre principal du dashboard selon le type
 * 
 * @param int $user_id
 * @return string
 */
function nfc_get_dashboard_title($user_id = null) {
    $dashboard_type = nfc_get_dashboard_type($user_id);
    
    switch ($dashboard_type) {
        case 'empty':
            return 'Aucun produit NFC configuré';
            
        case 'simple':
            return 'Ma vCard NFC';
            
        case 'enterprise':
            return 'Mes vCards Entreprise';
            
        case 'google_reviews':
            return 'Mes Profils Avis Google';
            
        case 'mixed':
            return 'Mes Produits NFC';
            
        default:
            return 'Dashboard NFC';
    }
}

/**
 * Génère un message d'aide selon le type de dashboard
 * 
 * @param int $user_id
 * @return string
 */
function nfc_get_dashboard_help_message($user_id = null) {
    $dashboard_type = nfc_get_dashboard_type($user_id);
    
    switch ($dashboard_type) {
        case 'empty':
            return 'Commandez vos premiers produits NFC pour commencer.';
            
        case 'simple':
            return 'Configurez votre profil et suivez vos performances.';
            
        case 'enterprise':
            $products_summary = nfc_get_user_products_summary($user_id);
            $count = count($products_summary['vcard_profiles'] ?? []);
            return "Gérez vos $count profils vCard et suivez leurs performances.";
            
        case 'google_reviews':
            return 'Configurez vos profils Avis Google et optimisez vos emplacements.';
            
        case 'mixed':
            return 'Gérez tous vos produits NFC depuis cette interface.';
            
        default:
            return 'Bienvenue sur votre dashboard NFC.';
    }
}