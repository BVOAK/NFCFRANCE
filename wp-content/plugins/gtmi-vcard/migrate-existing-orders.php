<?php
/**
 * Script de Migration - Commandes Existantes vers Dashboard Enterprise
 * 
 * CRÉER CE FICHIER : wp-content/plugins/gtmi-vcard/migrate-existing-orders.php
 * ACCÉDER À : http://nfcfrance.loc/wp-content/plugins/gtmi-vcard/migrate-existing-orders.php
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Sécurité - Admin seulement
if (!current_user_can('administrator')) {
    wp_die('Accès non autorisé. Connectez-vous comme administrateur.');
}

// Style CSS
echo "<html><head><title>Migration Dashboard Enterprise</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .warning { color: orange; font-weight: bold; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; border-radius: 5px; }
    .btn { background: #0073aa; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin: 5px; display: inline-block; }
    .btn-danger { background: #d63638; }
    .btn-success { background: #00a32a; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; }
    table td, table th { border: 1px solid #ddd; padding: 8px; text-align: left; }
    table th { background-color: #f2f2f2; }
    .step { margin: 15px 0; padding: 10px; border-left: 4px solid #0073aa; background: #f8f9fa; }
</style></head><body>";

echo "<h1>📦 Migration Dashboard Enterprise</h1>";

// Variables
global $wpdb;
$action = $_GET['action'] ?? 'overview';

// Navigation
echo "<div class='section'>";
echo "<h2>🧭 Navigation</h2>";
echo "<a href='?action=overview' class='btn'>📊 Overview</a>";
echo "<a href='?action=analyze' class='btn'>🔍 Analyser</a>";
echo "<a href='?action=migrate' class='btn btn-success'>🚀 Migrer</a>";
echo "<a href='?action=verify' class='btn'>✅ Vérifier</a>";
echo "</div>";

switch ($action) {
    
    case 'overview':
        show_overview();
        break;
        
    case 'analyze':
        analyze_existing_data();
        break;
        
    case 'migrate':
        perform_migration();
        break;
        
    case 'verify':
        verify_migration();
        break;
        
    default:
        show_overview();
}

/**
 * Vue d'ensemble du système
 */
function show_overview() {
    global $wpdb;
    
    echo "<div class='section'>";
    echo "<h2>📊 Vue d'Ensemble du Système</h2>";
    
    // État des tables
    echo "<h3>🗄️ État des Tables</h3>";
    
    $enterprise_table = $wpdb->prefix . 'nfc_enterprise_cards';
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $enterprise_table)) === $enterprise_table;
    
    if ($table_exists) {
        echo "<div class='success'>✅ Table $enterprise_table existe</div>";
        $records_count = $wpdb->get_var("SELECT COUNT(*) FROM $enterprise_table");
        echo "<div class='info'>📊 Records existants: $records_count</div>";
    } else {
        echo "<div class='error'>❌ Table $enterprise_table manquante</div>";
        echo "<div class='warning'>⚠️ La table enterprise doit être créée avant la migration</div>";
    }
    
    // État des vCards existantes
    $vcards_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'virtual_card' AND post_status = 'publish'");
    echo "<div class='info'>📱 vCards existantes: $vcards_count</div>";
    
    // État des commandes WooCommerce
    if (function_exists('wc_get_orders')) {
        $orders = wc_get_orders(['limit' => -1, 'status' => ['completed', 'processing']]);
        $nfc_orders = 0;
        foreach ($orders as $order) {
            foreach ($order->get_items() as $item) {
                if (gtmi_vcard_is_nfc_product($item->get_product_id())) {
                    $nfc_orders++;
                    break;
                }
            }
        }
        echo "<div class='info'>🛒 Commandes NFC: $nfc_orders</div>";
    }
    
    echo "</div>";
    
    // Étapes de migration
    echo "<div class='section'>";
    echo "<h2>📋 Étapes de Migration</h2>";
    
    echo "<div class='step'>";
    echo "<h3>Étape 1: Analyser</h3>";
    echo "<p>Analyse des données existantes pour détecter les vCards et commandes à migrer.</p>";
    echo "<a href='?action=analyze' class='btn'>🔍 Analyser maintenant</a>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Étape 2: Migrer</h3>";
    echo "<p>Migration des vCards existantes vers le système enterprise.</p>";
    echo "<a href='?action=migrate' class='btn btn-success'>🚀 Migrer maintenant</a>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Étape 3: Vérifier</h3>";
    echo "<p>Vérification du succès de la migration.</p>";
    echo "<a href='?action=verify' class='btn'>✅ Vérifier maintenant</a>";
    echo "</div>";
    
    echo "</div>";
}

/**
 * Analyse des données existantes
 */
function analyze_existing_data() {
    global $wpdb;
    
    echo "<div class='section'>";
    echo "<h2>🔍 Analyse des Données Existantes</h2>";
    
    // Récupérer toutes les vCards avec leurs métadonnées
    $vcards = $wpdb->get_results("
        SELECT 
            p.ID,
            p.post_title,
            p.post_date,
            pm_order.meta_value as order_id,
            pm_customer.meta_value as customer_id,
            pm_firstname.meta_value as firstname,
            pm_lastname.meta_value as lastname
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm_order ON p.ID = pm_order.post_id AND pm_order.meta_key = 'order_id'
        LEFT JOIN {$wpdb->postmeta} pm_customer ON p.ID = pm_customer.post_id AND pm_customer.meta_key = 'customer_id'
        LEFT JOIN {$wpdb->postmeta} pm_firstname ON p.ID = pm_firstname.post_id AND pm_firstname.meta_key = 'firstname'
        LEFT JOIN {$wpdb->postmeta} pm_lastname ON p.ID = pm_lastname.post_id AND pm_lastname.meta_key = 'lastname'
        WHERE p.post_type = 'virtual_card' AND p.post_status = 'publish'
        ORDER BY p.post_date DESC
    ");
    
    if (empty($vcards)) {
        echo "<div class='warning'>⚠️ Aucune vCard trouvée à migrer</div>";
        return;
    }
    
    echo "<div class='info'>📊 " . count($vcards) . " vCards trouvées à analyser</div>";
    
    // Tableau d'analyse
    echo "<h3>📋 Détail des vCards</h3>";
    echo "<table>";
    echo "<tr><th>vCard ID</th><th>Nom</th><th>Commande</th><th>Client</th><th>Date</th><th>État</th></tr>";
    
    $migratable = 0;
    $already_migrated = 0;
    
    foreach ($vcards as $vcard) {
        // Vérifier si déjà migrée
        $enterprise_table = $wpdb->prefix . 'nfc_enterprise_cards';
        $is_migrated = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $enterprise_table WHERE vcard_id = %d",
            $vcard->ID
        ));
        
        $status = $is_migrated ? '✅ Migrée' : '📋 À migrer';
        $status_class = $is_migrated ? 'success' : 'warning';
        
        if ($is_migrated) {
            $already_migrated++;
        } else {
            $migratable++;
        }
        
        $full_name = trim(($vcard->firstname ?? '') . ' ' . ($vcard->lastname ?? ''));
        if (empty($full_name)) {
            $full_name = $vcard->post_title;
        }
        
        echo "<tr>";
        echo "<td>{$vcard->ID}</td>";
        echo "<td>" . esc_html($full_name) . "</td>";
        echo "<td>" . ($vcard->order_id ?? 'N/A') . "</td>";
        echo "<td>" . ($vcard->customer_id ?? 'N/A') . "</td>";
        echo "<td>" . date('d/m/Y', strtotime($vcard->post_date)) . "</td>";
        echo "<td class='$status_class'>$status</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Résumé
    echo "<div class='section'>";
    echo "<h3>📊 Résumé de l'Analyse</h3>";
    echo "<div class='success'>✅ Déjà migrées: $already_migrated</div>";
    echo "<div class='warning'>📋 À migrer: $migratable</div>";
    echo "<div class='info'>🔢 Total: " . count($vcards) . "</div>";
    echo "</div>";
    
    if ($migratable > 0) {
        echo "<div class='section'>";
        echo "<p><strong>Prêt à migrer $migratable vCards</strong></p>";
        echo "<a href='?action=migrate' class='btn btn-success'>🚀 Lancer la migration</a>";
        echo "</div>";
    }
    
    echo "</div>";
}

/**
 * Perform the migration
 */
function perform_migration() {
    global $wpdb;
    
    echo "<div class='section'>";
    echo "<h2>🚀 Migration en Cours</h2>";
    
    // Vérifier table enterprise
    $enterprise_table = $wpdb->prefix . 'nfc_enterprise_cards';
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $enterprise_table)) === $enterprise_table;
    
    if (!$table_exists) {
        echo "<div class='error'>❌ Table $enterprise_table manquante. Activation du plugin enterprise required.</div>";
        return;
    }
    
    // Récupérer vCards non migrées
    $vcards_to_migrate = $wpdb->get_results("
        SELECT 
            p.ID as vcard_id,
            p.post_title,
            pm_order.meta_value as order_id,
            pm_customer.meta_value as customer_id
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm_order ON p.ID = pm_order.post_id AND pm_order.meta_key = 'order_id'
        LEFT JOIN {$wpdb->postmeta} pm_customer ON p.ID = pm_customer.post_id AND pm_customer.meta_key = 'customer_id'
        WHERE p.post_type = 'virtual_card' 
        AND p.post_status = 'publish'
        AND p.ID NOT IN (
            SELECT vcard_id FROM $enterprise_table WHERE vcard_id IS NOT NULL
        )
        ORDER BY p.ID ASC
    ");
    
    if (empty($vcards_to_migrate)) {
        echo "<div class='info'>✅ Aucune vCard à migrer - toutes sont déjà migrées</div>";
        return;
    }
    
    echo "<div class='info'>📊 Migration de " . count($vcards_to_migrate) . " vCards</div>";
    
    $migrated_count = 0;
    $errors = [];
    
    foreach ($vcards_to_migrate as $vcard) {
        echo "<div style='margin: 10px 0; padding: 8px; background: #f0f8ff; border-left: 3px solid #0073aa;'>";
        echo "🔄 Migration vCard #{$vcard->vcard_id}...";
        
        try {
            $order_id = $vcard->order_id ?: 9999; // Fallback si pas de commande
            $customer_id = $vcard->customer_id ?: 1; // Fallback admin
            
            // Générer identifiant unique
            $identifier = "NFC{$order_id}-1"; // Position 1 pour les anciennes vCards
            
            // Vérifier unicité de l'identifiant
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $enterprise_table WHERE card_identifier = %s",
                $identifier
            ));
            
            if ($existing) {
                $identifier = "NFC{$order_id}-" . $vcard->vcard_id; // Fallback avec vcard ID
            }
            
            // Insérer dans table enterprise
            $result = $wpdb->insert(
                $enterprise_table,
                [
                    'order_id' => $order_id,
                    'vcard_id' => $vcard->vcard_id,
                    'card_position' => 1,
                    'card_identifier' => $identifier,
                    'card_status' => 'configured', // Anciennes cartes sont considérées configurées
                    'company_name' => '',
                    'main_user_id' => $customer_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ],
                ['%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s']
            );
            
            if ($result !== false) {
                // Ajouter métadonnées enterprise à la vCard
                update_post_meta($vcard->vcard_id, '_enterprise_order_id', $order_id);
                update_post_meta($vcard->vcard_id, '_enterprise_position', 1);
                update_post_meta($vcard->vcard_id, '_card_identifier', $identifier);
                update_post_meta($vcard->vcard_id, '_is_enterprise_card', 'yes');
                
                echo " ✅ Succès (ID: $identifier)";
                $migrated_count++;
            } else {
                $error_msg = $wpdb->last_error ?: 'Erreur inconnue';
                echo " ❌ Erreur: $error_msg";
                $errors[] = "vCard #{$vcard->vcard_id}: $error_msg";
            }
            
        } catch (Exception $e) {
            echo " ❌ Exception: " . $e->getMessage();
            $errors[] = "vCard #{$vcard->vcard_id}: " . $e->getMessage();
        }
        
        echo "</div>";
    }
    
    // Résumé final
    echo "<div class='section'>";
    echo "<h3>📊 Résumé de la Migration</h3>";
    echo "<div class='success'>✅ vCards migrées avec succès: $migrated_count</div>";
    
    if (!empty($errors)) {
        echo "<div class='error'>❌ Erreurs rencontrées: " . count($errors) . "</div>";
        echo "<details><summary>Voir les erreurs</summary>";
        echo "<pre>" . implode("\n", $errors) . "</pre>";
        echo "</details>";
    }
    
    if ($migrated_count > 0) {
        echo "<p><strong>🎉 Migration terminée !</strong></p>";
        echo "<a href='?action=verify' class='btn'>✅ Vérifier les résultats</a>";
        echo "<a href='/mon-compte/nfc-dashboard/' class='btn btn-success'>🚀 Tester le Dashboard</a>";
    }
    
    echo "</div>";
    echo "</div>";
}

/**
 * Vérifier la migration
 */
function verify_migration() {
    global $wpdb;
    
    echo "<div class='section'>";
    echo "<h2>✅ Vérification de la Migration</h2>";
    
    $enterprise_table = $wpdb->prefix . 'nfc_enterprise_cards';
    
    // Compter les cartes migrées
    $migrated_cards = $wpdb->get_results("
        SELECT 
            ec.*,
            p.post_title,
            pm_firstname.meta_value as firstname,
            pm_lastname.meta_value as lastname
        FROM $enterprise_table ec
        LEFT JOIN {$wpdb->posts} p ON p.ID = ec.vcard_id
        LEFT JOIN {$wpdb->postmeta} pm_firstname ON p.ID = pm_firstname.post_id AND pm_firstname.meta_key = 'firstname'
        LEFT JOIN {$wpdb->postmeta} pm_lastname ON p.ID = pm_lastname.post_id AND pm_lastname.meta_key = 'lastname'
        ORDER BY ec.created_at DESC
        LIMIT 20
    ");
    
    if (empty($migrated_cards)) {
        echo "<div class='warning'>⚠️ Aucune carte enterprise trouvée</div>";
        return;
    }
    
    echo "<div class='success'>✅ " . count($migrated_cards) . " cartes dans le système enterprise (20 dernières affichées)</div>";
    
    // Tableau de vérification
    echo "<h3>📋 Cartes Migrées (vérification)</h3>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Identifiant</th><th>vCard</th><th>Nom</th><th>Statut</th><th>Créée</th></tr>";
    
    foreach ($migrated_cards as $card) {
        $full_name = trim(($card->firstname ?? '') . ' ' . ($card->lastname ?? ''));
        if (empty($full_name)) {
            $full_name = $card->post_title;
        }
        
        echo "<tr>";
        echo "<td>{$card->id}</td>";
        echo "<td><strong>{$card->card_identifier}</strong></td>";
        echo "<td>#{$card->vcard_id}</td>";
        echo "<td>" . esc_html($full_name) . "</td>";
        echo "<td>{$card->card_status}</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($card->created_at)) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Test des fonctions
    echo "<h3>🧪 Test des Fonctions</h3>";
    
    $user_id = get_current_user_id();
    
    try {
        if (function_exists('nfc_get_user_products_summary')) {
            $products_summary = nfc_get_user_products_summary($user_id);
            $vcard_count = count($products_summary['vcard_profiles'] ?? []);
            echo "<div class='success'>✅ nfc_get_user_products_summary(): $vcard_count vCards trouvées</div>";
        } else {
            echo "<div class='error'>❌ Fonction nfc_get_user_products_summary() manquante</div>";
        }
        
        if (function_exists('nfc_get_dashboard_type')) {
            $dashboard_type = nfc_get_dashboard_type($user_id);
            echo "<div class='success'>✅ nfc_get_dashboard_type(): '$dashboard_type'</div>";
        } else {
            echo "<div class='error'>❌ Fonction nfc_get_dashboard_type() manquante</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erreur lors du test: " . $e->getMessage() . "</div>";
    }
    
    // Liens de test
    echo "<div class='section'>";
    echo "<h3>🔗 Tests Recommandés</h3>";
    echo "<p>Maintenant que la migration est effectuée, testez:</p>";
    echo "<a href='/mon-compte/nfc-dashboard/' class='btn btn-success'>🚀 Dashboard Principal</a>";
    echo "<a href='/mon-compte/nfc-dashboard/?page=overview' class='btn'>📊 Page Overview</a>";
    echo "<a href='?action=overview' class='btn'>🔙 Retour Overview</a>";
    echo "</div>";
    
    echo "</div>";
}

echo "</body></html>";
?>