<?php
/**
 * Test Basique Sécurisé - NFC Enterprise
 * 
 * CRÉER CE FICHIER : wp-content/plugins/gtmi-vcard/test-basic.php
 * ACCÉDER À : http://nfcfrance.loc/wp-content/plugins/gtmi-vcard/test-basic.php
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Vérifier les permissions
if (!current_user_can('administrator')) {
    wp_die('Accès non autorisé. Connectez-vous comme administrateur.');
}

echo "<html><head><title>Test Basique NFC Enterprise</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
    .test-btn { background: #0073aa; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin: 5px; display: inline-block; }
</style></head><body>";

echo "<h1>🔧 Test Basique NFC Enterprise</h1>";

// Test 1: Vérifications de base
echo "<div class='section'>";
echo "<h2>✅ Test 1: Vérifications Système</h2>";

$checks = [
    'NFC_Enterprise_Core existe' => class_exists('NFC_Enterprise_Core'),
    'nfc_get_dashboard_type() existe' => function_exists('nfc_get_dashboard_type'),
    'WooCommerce actif' => function_exists('wc_create_order'),
    'ACF actif' => function_exists('get_field')
];

foreach ($checks as $label => $result) {
    if ($result) {
        echo "<div class='success'>✅ $label</div>";
    } else {
        echo "<div class='error'>❌ $label</div>";
    }
}

echo "</div>";

// Test 2: Table BDD
echo "<div class='section'>";
echo "<h2>🗄️ Test 2: Base de Données</h2>";

global $wpdb;
$table_name = $wpdb->prefix . 'nfc_enterprise_cards';
$table_exists = $wpdb->get_var($wpdb->prepare(
    "SHOW TABLES LIKE %s", $table_name
)) === $table_name;

if ($table_exists) {
    echo "<div class='success'>✅ Table $table_name existe</div>";
    
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo "<div class='info'>📊 Enregistrements: $count</div>";
    
    // Afficher quelques enregistrements s'il y en a
    if ($count > 0) {
        $records = $wpdb->get_results("SELECT * FROM $table_name LIMIT 5", ARRAY_A);
        echo "<h3>Échantillon des données:</h3>";
        echo "<pre>" . print_r($records, true) . "</pre>";
    }
    
} else {
    echo "<div class='error'>❌ Table $table_name manquante</div>";
}

echo "</div>";

// Test 3: Fonctions de base (sans création de commande)
echo "<div class='section'>";
echo "<h2>⚡ Test 3: Fonctions de Base</h2>";

$user_id = get_current_user_id();

try {
    // Test récupération cartes (ne devrait pas échouer)
    $user_cards = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
    echo "<div class='success'>✅ get_user_enterprise_cards() : " . count($user_cards) . " cartes</div>";
    
    // Test détection type dashboard
    $dashboard_type = nfc_get_dashboard_type($user_id);
    echo "<div class='success'>✅ nfc_get_dashboard_type() : $dashboard_type</div>";
    
    // Test stats globales
    $global_stats = NFC_Enterprise_Core::get_user_global_stats($user_id);
    echo "<div class='success'>✅ get_user_global_stats() : OK</div>";
    echo "<div class='info'>Stats: " . json_encode($global_stats) . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur fonction de base: " . $e->getMessage() . "</div>";
}

echo "</div>";

// Test 4: Test manuel de création (optionnel)
if (isset($_GET['test_create'])) {
    echo "<div class='section'>";
    echo "<h2>🧪 Test 4: Création Manuelle</h2>";
    
    try {
        // Créer une commande très basique
        if (function_exists('wc_create_order')) {
            $order = wc_create_order();
            $order->set_billing_first_name('Test');
            $order->set_billing_last_name('Manual');
            $order->set_billing_email('test@nfcfrance.com');
            $order->set_customer_id($user_id);
            $order->save();
            
            echo "<div class='success'>✅ Commande test créée: #{$order->get_id()}</div>";
            
            // Tester la fonction de détection
            $nfc_items = [];
            foreach ($order->get_items() as $item_id => $item) {
                $product = $item->get_product();
                if ($product) {
                    // Supposer que c'est un produit NFC pour le test
                    $nfc_items[] = [
                        'item_id' => $item_id,
                        'item' => $item,
                        'product' => $product,
                        'quantity' => $item->get_quantity(),
                        'product_name' => $product->get_name()
                    ];
                }
            }
            
            echo "<div class='info'>Items détectés: " . count($nfc_items) . "</div>";
            
            // Nettoyer
            $order->delete(true);
            echo "<div class='info'>🧹 Commande test supprimée</div>";
            
        } else {
            echo "<div class='error'>❌ WooCommerce non disponible pour créer commande</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erreur test création: " . $e->getMessage() . "</div>";
    }
    
    echo "</div>";
}

// Menu d'actions
echo "<div class='section'>";
echo "<h2>🎯 Actions de Test</h2>";

if (!isset($_GET['test_create'])) {
    echo "<a href='?test_create=1' class='test-btn'>🧪 Tester Création Commande</a>";
}

echo "<a href='?' class='test-btn'>🔄 Recharger Tests</a>";

$main_test_url = home_url('?nfc_run_tests=1');
echo "<a href='$main_test_url' class='test-btn'>🚀 Lancer Tests Complets</a>";

echo "</div>";

// Informations système
echo "<div class='section'>";
echo "<h2>ℹ️ Informations Système</h2>";

echo "<ul>";
echo "<li><strong>WordPress:</strong> " . get_bloginfo('version') . "</li>";
echo "<li><strong>PHP:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Utilisateur:</strong> " . wp_get_current_user()->user_login . " (ID: $user_id)</li>";
echo "<li><strong>WP_DEBUG:</strong> " . (defined('WP_DEBUG') && WP_DEBUG ? 'Activé' : 'Désactivé') . "</li>";
echo "</ul>";

echo "</div>";

echo "<hr>";
echo "<p><strong>🎯 Objectif:</strong> Si tous ces tests de base passent, on peut passer aux tests complets de création de cartes.</p>";

echo "</body></html>";
?>