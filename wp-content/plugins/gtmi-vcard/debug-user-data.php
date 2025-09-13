<?php
/**
 * Script Debug User Data
 * 
 * CRÉER CE FICHIER : wp-content/plugins/gtmi-vcard/debug-user-data.php  
 * ACCÉDER À : http://nfcfrance.loc/wp-content/plugins/gtmi-vcard/debug-user-data.php
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Sécurité
if (!current_user_can('administrator')) {
    wp_die('Accès non autorisé. Admin requis.');
}

echo "<html><head><title>Debug User Data</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .warning { color: orange; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; }
    table td, table th { border: 1px solid #ddd; padding: 8px; text-align: left; }
    table th { background-color: #f2f2f2; }
</style></head><body>";

echo "<h1>🔍 Debug User Data - Dashboard NFC</h1>";

global $wpdb;

// Navigation
$action = $_GET['action'] ?? 'current_user';
$target_user = $_GET['user_id'] ?? null;

echo "<div class='section'>";
echo "<h2>🧭 Navigation</h2>";
echo "<a href='?action=current_user' style='margin-right: 10px; padding: 5px 10px; background: #0073aa; color: white; text-decoration: none;'>👤 User Actuel</a>";
echo "<a href='?action=all_users' style='margin-right: 10px; padding: 5px 10px; background: #0073aa; color: white; text-decoration: none;'>👥 Tous les Users</a>";
echo "<a href='?action=enterprise_data' style='padding: 5px 10px; background: #0073aa; color: white; text-decoration: none;'>🏢 Données Enterprise</a>";
echo "</div>";

switch ($action) {
    case 'current_user':
        debug_current_user();
        break;
        
    case 'all_users':
        debug_all_users();
        break;
        
    case 'enterprise_data':
        debug_enterprise_data();
        break;
        
    case 'specific_user':
        if ($target_user) {
            debug_specific_user($target_user);
        }
        break;
}

function debug_current_user() {
    $current_user_id = get_current_user_id();
    $user = get_user_by('ID', $current_user_id);
    
    echo "<div class='section'>";
    echo "<h2>👤 Utilisateur Actuel (celui avec lequel tu es connecté)</h2>";
    echo "<div class='info'>User ID: $current_user_id</div>";
    echo "<div class='info'>Login: " . $user->user_login . "</div>";
    echo "<div class='info'>Email: " . $user->user_email . "</div>";
    echo "<div class='info'>Rôles: " . implode(', ', $user->roles) . "</div>";
    echo "</div>";
    
    // Test des fonctions Dashboard
    echo "<div class='section'>";
    echo "<h2>🧪 Test Dashboard Functions pour User #$current_user_id</h2>";
    
    try {
        // Test nfc_get_user_products_summary
        if (function_exists('nfc_get_user_products_summary')) {
            $products_summary = nfc_get_user_products_summary($current_user_id);
            echo "<h3>nfc_get_user_products_summary($current_user_id):</h3>";
            echo "<pre>" . print_r($products_summary, true) . "</pre>";
            
            $vcard_count = count($products_summary['vcard_profiles'] ?? []);
            echo $vcard_count > 0 ? 
                "<div class='success'>✅ $vcard_count vCards trouvées</div>" : 
                "<div class='error'>❌ 0 vCard trouvée</div>";
        } else {
            echo "<div class='error'>❌ Fonction nfc_get_user_products_summary() manquante</div>";
        }
        
        // Test nfc_get_dashboard_type
        if (function_exists('nfc_get_dashboard_type')) {
            $dashboard_type = nfc_get_dashboard_type($current_user_id);
            echo "<div class='info'>📊 Dashboard Type: '$dashboard_type'</div>";
        } else {
            echo "<div class='error'>❌ Fonction nfc_get_dashboard_type() manquante</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erreur: " . $e->getMessage() . "</div>";
    }
    
    echo "</div>";
    
    // Vérifier les cartes enterprise pour ce user
    debug_user_enterprise_cards($current_user_id);
}

function debug_all_users() {
    global $wpdb;
    
    echo "<div class='section'>";
    echo "<h2>👥 Tous les Users avec des cartes Enterprise</h2>";
    
    $enterprise_table = $wpdb->prefix . 'nfc_enterprise_cards';
    
    $users_with_cards = $wpdb->get_results("
        SELECT 
            ec.main_user_id,
            COUNT(ec.id) as cards_count,
            u.user_login,
            u.user_email,
            MIN(ec.created_at) as first_card_date
        FROM $enterprise_table ec
        LEFT JOIN {$wpdb->users} u ON u.ID = ec.main_user_id
        GROUP BY ec.main_user_id
        ORDER BY cards_count DESC
    ");
    
    if (empty($users_with_cards)) {
        echo "<div class='warning'>⚠️ Aucun utilisateur avec des cartes enterprise</div>";
        return;
    }
    
    echo "<table>";
    echo "<tr><th>User ID</th><th>Login</th><th>Email</th><th>Cartes</th><th>Première carte</th><th>Actions</th></tr>";
    
    foreach ($users_with_cards as $user_data) {
        echo "<tr>";
        echo "<td>{$user_data->main_user_id}</td>";
        echo "<td>" . esc_html($user_data->user_login ?: 'N/A') . "</td>";
        echo "<td>" . esc_html($user_data->user_email ?: 'N/A') . "</td>";
        echo "<td><strong>{$user_data->cards_count}</strong></td>";
        echo "<td>" . ($user_data->first_card_date ? date('d/m/Y', strtotime($user_data->first_card_date)) : 'N/A') . "</td>";
        echo "<td>";
        echo "<a href='?action=specific_user&user_id={$user_data->main_user_id}' style='color: blue;'>🔍 Détails</a>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</div>";
}

function debug_enterprise_data() {
    global $wpdb;
    
    echo "<div class='section'>";
    echo "<h2>🏢 Données Enterprise - Vue Globale</h2>";
    
    $enterprise_table = $wpdb->prefix . 'nfc_enterprise_cards';
    
    // Stats globales
    $total_cards = $wpdb->get_var("SELECT COUNT(*) FROM $enterprise_table");
    $total_users = $wpdb->get_var("SELECT COUNT(DISTINCT main_user_id) FROM $enterprise_table");
    $total_orders = $wpdb->get_var("SELECT COUNT(DISTINCT order_id) FROM $enterprise_table");
    
    echo "<div class='info'>📊 Total cartes: $total_cards</div>";
    echo "<div class='info'>👥 Total utilisateurs: $total_users</div>";
    echo "<div class='info'>🛒 Total commandes: $total_orders</div>";
    
    // Dernières cartes créées
    echo "<h3>📱 Dernières cartes créées (10 dernières)</h3>";
    
    $recent_cards = $wpdb->get_results("
        SELECT 
            ec.*,
            u.user_login,
            p.post_title as vcard_title
        FROM $enterprise_table ec
        LEFT JOIN {$wpdb->users} u ON u.ID = ec.main_user_id
        LEFT JOIN {$wpdb->posts} p ON p.ID = ec.vcard_id
        ORDER BY ec.created_at DESC
        LIMIT 10
    ");
    
    if (!empty($recent_cards)) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Identifiant</th><th>User</th><th>vCard</th><th>Statut</th><th>Créé</th></tr>";
        
        foreach ($recent_cards as $card) {
            echo "<tr>";
            echo "<td>{$card->id}</td>";
            echo "<td><strong>{$card->card_identifier}</strong></td>";
            echo "<td>#{$card->main_user_id} - " . esc_html($card->user_login ?: 'N/A') . "</td>";
            echo "<td>#{$card->vcard_id} - " . esc_html($card->vcard_title ?: 'N/A') . "</td>";
            echo "<td>{$card->card_status}</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($card->created_at)) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
}

function debug_specific_user($user_id) {
    echo "<div class='section'>";
    echo "<h2>👤 Debug User #$user_id</h2>";
    
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        echo "<div class='error'>❌ User #$user_id introuvable</div>";
        return;
    }
    
    echo "<div class='info'>Login: " . $user->user_login . "</div>";
    echo "<div class='info'>Email: " . $user->user_email . "</div>";
    echo "</div>";
    
    debug_user_enterprise_cards($user_id);
    
    // Test des fonctions dashboard pour ce user
    echo "<div class='section'>";
    echo "<h2>🧪 Test Dashboard Functions pour User #$user_id</h2>";
    
    if (function_exists('nfc_get_user_products_summary')) {
        $products_summary = nfc_get_user_products_summary($user_id);
        echo "<h3>nfc_get_user_products_summary($user_id):</h3>";
        echo "<pre>" . print_r($products_summary, true) . "</pre>";
    }
    
    echo "</div>";
}

function debug_user_enterprise_cards($user_id) {
    global $wpdb;
    
    echo "<div class='section'>";
    echo "<h2>🏢 Cartes Enterprise pour User #$user_id</h2>";
    
    $enterprise_table = $wpdb->prefix . 'nfc_enterprise_cards';
    
    $user_cards = $wpdb->get_results($wpdb->prepare("
        SELECT 
            ec.*,
            p.post_title as vcard_title,
            pm_firstname.meta_value as firstname,
            pm_lastname.meta_value as lastname,
            pm_status.meta_value as vcard_status
        FROM $enterprise_table ec
        LEFT JOIN {$wpdb->posts} p ON p.ID = ec.vcard_id
        LEFT JOIN {$wpdb->postmeta} pm_firstname ON p.ID = pm_firstname.post_id AND pm_firstname.meta_key = 'firstname'
        LEFT JOIN {$wpdb->postmeta} pm_lastname ON p.ID = pm_lastname.post_id AND pm_lastname.meta_key = 'lastname'
        LEFT JOIN {$wpdb->postmeta} pm_status ON p.ID = pm_status.post_id AND pm_status.meta_key = 'card_status'
        WHERE ec.main_user_id = %d
        ORDER BY ec.card_position ASC
    ", $user_id));
    
    if (empty($user_cards)) {
        echo "<div class='warning'>⚠️ Aucune carte enterprise trouvée pour ce user</div>";
        echo "<div class='info'>💡 Cela explique pourquoi le dashboard affiche '0 produit configuré'</div>";
    } else {
        echo "<div class='success'>✅ " . count($user_cards) . " carte(s) trouvée(s)</div>";
        
        echo "<table>";
        echo "<tr><th>Identifiant</th><th>vCard</th><th>Nom</th><th>Statut</th><th>Commande</th><th>Créé</th></tr>";
        
        foreach ($user_cards as $card) {
            $full_name = trim(($card->firstname ?? '') . ' ' . ($card->lastname ?? ''));
            if (empty($full_name)) {
                $full_name = $card->vcard_title ?: 'N/A';
            }
            
            echo "<tr>";
            echo "<td><strong>{$card->card_identifier}</strong></td>";
            echo "<td>#{$card->vcard_id}</td>";
            echo "<td>" . esc_html($full_name) . "</td>";
            echo "<td>{$card->card_status}</td>";
            echo "<td>#{$card->order_id}</td>";
            echo "<td>" . date('d/m/Y', strtotime($card->created_at)) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
}

echo "</body></html>";
?>