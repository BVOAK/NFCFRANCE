<?php
/**
 * Test Simple Enterprise - Fichier indépendant
 * 
 * CRÉER CE FICHIER : wp-content/plugins/gtmi-vcard/test-simple.php
 * ACCÉDER À : http://nfcfrance.loc/wp-content/plugins/gtmi-vcard/test-simple.php
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Vérifier les permissions
if (!current_user_can('administrator')) {
    wp_die('Accès non autorisé. Connectez-vous comme administrateur.');
}

echo "<html><head><title>Test Simple NFC Enterprise</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🔧 Test Simple NFC Enterprise System</h1>";

// Test 1: Vérifier que les classes existent
echo "<div class='section'>";
echo "<h2>📋 Test 1: Classes et Fonctions</h2>";

if (class_exists('NFC_Enterprise_Core')) {
    echo "<div class='success'>✅ Classe NFC_Enterprise_Core existe</div>";
} else {
    echo "<div class='error'>❌ Classe NFC_Enterprise_Core introuvable</div>";
    echo "<p>Vérifiez que le fichier includes/enterprise/enterprise-core.php est bien chargé.</p>";
}

if (function_exists('nfc_get_dashboard_type')) {
    echo "<div class='success'>✅ Fonction nfc_get_dashboard_type() existe</div>";
} else {
    echo "<div class='error'>❌ Fonction nfc_get_dashboard_type() introuvable</div>";
}

if (function_exists('nfc_enterprise_activate')) {
    echo "<div class='success'>✅ Fonction nfc_enterprise_activate() existe</div>";
} else {
    echo "<div class='error'>❌ Fonction nfc_enterprise_activate() introuvable</div>";
}

echo "</div>";

// Test 2: Vérifier la table BDD
echo "<div class='section'>";
echo "<h2>🗄️ Test 2: Base de Données</h2>";

global $wpdb;
$table_name = $wpdb->prefix . 'nfc_enterprise_cards';
$table_exists = $wpdb->get_var($wpdb->prepare(
    "SHOW TABLES LIKE %s", $table_name
)) === $table_name;

if ($table_exists) {
    echo "<div class='success'>✅ Table $table_name existe</div>";
    
    // Compter les enregistrements
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo "<div class='info'>📊 Nombre d'enregistrements: $count</div>";
    
    // Afficher structure
    $columns = $wpdb->get_results("DESCRIBE $table_name", ARRAY_A);
    echo "<h3>Structure de la table:</h3>";
    echo "<pre>";
    foreach ($columns as $column) {
        echo sprintf("%-25s %s\n", $column['Field'], $column['Type']);
    }
    echo "</pre>";
    
} else {
    echo "<div class='error'>❌ Table $table_name n'existe pas</div>";
    
    echo "<p><strong>Pour créer la table:</strong></p>";
    echo "<ol>";
    echo "<li>Désactivez le plugin gtmi-vcard</li>";
    echo "<li>Réactivez-le (cela déclenchera la création de table)</li>";
    echo "<li>Ou appelez manuellement NFC_Enterprise_Core::create_database_tables()</li>";
    echo "</ol>";
    
    if (class_exists('NFC_Enterprise_Core')) {
        echo "<p><a href='?create_table=1' style='background: #0073aa; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;'>Créer la table maintenant</a></p>";
    }
}

echo "</div>";

// Test 3: Vérifier plugins requis
echo "<div class='section'>";
echo "<h2>🔌 Test 3: Plugins Requis</h2>";

if (function_exists('wc_create_order')) {
    echo "<div class='success'>✅ WooCommerce actif</div>";
} else {
    echo "<div class='error'>❌ WooCommerce non actif</div>";
}

if (function_exists('get_field')) {
    echo "<div class='success'>✅ ACF actif</div>";
} else {
    echo "<div class='error'>❌ ACF non actif</div>";
}

echo "</div>";

// Test 4: Test création table si demandé
if (isset($_GET['create_table']) && class_exists('NFC_Enterprise_Core')) {
    echo "<div class='section'>";
    echo "<h2>🔨 Test 4: Création de Table</h2>";
    
    try {
        NFC_Enterprise_Core::create_database_tables();
        
        // Vérifier si créée
        $table_exists_now = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s", $table_name
        )) === $table_name;
        
        if ($table_exists_now) {
            echo "<div class='success'>✅ Table créée avec succès!</div>";
            echo "<p><a href='?' style='background: #00a32a; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px;'>Recharger la page</a></p>";
        } else {
            echo "<div class='error'>❌ Échec création table</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erreur: " . $e->getMessage() . "</div>";
    }
    
    echo "</div>";
}

// Test 5: Informations environnement
echo "<div class='section'>";
echo "<h2>🔍 Test 5: Environnement</h2>";

echo "<ul>";
echo "<li><strong>WordPress:</strong> " . get_bloginfo('version') . "</li>";
echo "<li><strong>PHP:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>WP_DEBUG:</strong> " . (defined('WP_DEBUG') && WP_DEBUG ? 'Activé' : 'Désactivé') . "</li>";
echo "<li><strong>Utilisateur actuel:</strong> " . wp_get_current_user()->user_login . " (ID: " . get_current_user_id() . ")</li>";
echo "<li><strong>Plugin Path:</strong> " . plugin_dir_path(dirname(__FILE__)) . "</li>";
echo "</ul>";

// Vérifier fichiers
$files_to_check = [
    'includes/enterprise/enterprise-core.php',
    'includes/enterprise/enterprise-functions.php', 
    'assets/css/dashboard-enterprise.css',
    'tests/enterprise-test.php'
];

echo "<h3>Fichiers Enterprise:</h3>";
echo "<ul>";
foreach ($files_to_check as $file) {
    $full_path = plugin_dir_path(dirname(__FILE__)) . $file;
    if (file_exists($full_path)) {
        echo "<li><span class='success'>✅</span> $file</li>";
    } else {
        echo "<li><span class='error'>❌</span> $file <em>(manquant)</em></li>";
    }
}
echo "</ul>";

echo "</div>";

// Test 6: Si tout fonctionne, test rapide de création
if ($table_exists && class_exists('NFC_Enterprise_Core')) {
    echo "<div class='section'>";
    echo "<h2>⚡ Test 6: Test Rapide Fonctionnel</h2>";
    
    $user_id = get_current_user_id();
    
    try {
        // Test récupération cartes utilisateur
        $user_cards = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
        echo "<div class='info'>📋 Cartes utilisateur: " . count($user_cards) . "</div>";
        
        // Test détection type dashboard
        $user_cards = nfc_get_user_vcard_profiles($user_id);
        $dashboard_info = count($user_cards) . " profils vCard";
        echo "<div class='info'>🖥️ Type dashboard: $dashboard_type</div>";
        
        // Test stats globales
        $global_stats = NFC_Enterprise_Core::get_user_global_stats($user_id);
        echo "<div class='info'>📊 Stats globales:</div>";
        echo "<pre>" . print_r($global_stats, true) . "</pre>";
        
        echo "<div class='success'>✅ Fonctions de base opérationnelles!</div>";
        
        if (count($user_cards) == 0) {
            echo "<p><strong>Note:</strong> Aucune carte trouvée. C'est normal si tu n'as pas encore passé de commande test.</p>";
            echo "<p>Pour tester complètement, il faut créer une commande WooCommerce avec plusieurs cartes NFC.</p>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erreur test fonctionnel: " . $e->getMessage() . "</div>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>✅ Si tous les tests de base passent:</strong> Le système est bien installé!</p>";
echo "<p><strong>📍 Prochaine étape:</strong> Créer une commande test avec plusieurs cartes pour tester le workflow complet.</p>";

echo "</body></html>";
?>