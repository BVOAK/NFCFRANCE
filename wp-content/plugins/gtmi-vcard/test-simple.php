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

/**
 * TESTS API LEADS - À COPIER DANS test-simple.php
 * Pour identifier exactement où ça plante
 */

echo "<style>
.test-section { border: 1px solid #ddd; padding: 20px; margin: 20px 0; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { color: blue; }
</style>";

echo "<h1>🔍 TESTS API LEADS COMPLETS</h1>";

$user_id = get_current_user_id();
echo "<p><strong>User ID testé:</strong> $user_id</p>";

// ================================================================================
// TEST 1: Vérifier les fonctions enterprise
// ================================================================================
echo "<div class='test-section'>";
echo "<h2>🧪 TEST 1: Fonctions Enterprise</h2>";

if (function_exists('nfc_get_user_vcard_profiles')) {
    echo "<div class='success'>✅ nfc_get_user_vcard_profiles() existe</div>";
    
    $user_vcards = nfc_get_user_vcard_profiles($user_id);
    echo "<div class='info'>📊 Nombre de vCards: " . count($user_vcards) . "</div>";
    
    if (!empty($user_vcards)) {
        echo "<pre>" . print_r($user_vcards, true) . "</pre>";
        
        $first_vcard_id = $user_vcards[0]['vcard_id'];
        echo "<div class='info'>🎯 Première vCard ID: $first_vcard_id</div>";
    } else {
        echo "<div class='error'>❌ Aucune vCard trouvée pour cet utilisateur</div>";
    }
} else {
    echo "<div class='error'>❌ Fonction nfc_get_user_vcard_profiles() manquante</div>";
}

echo "</div>";

// ================================================================================
// TEST 2: Test API endpoint user
// ================================================================================
echo "<div class='test-section'>";
echo "<h2>🧪 TEST 2: API Endpoint User</h2>";

$api_url = home_url("/wp-json/gtmi_vcard/v1/leads/user/{$user_id}");
echo "<div class='info'>🌐 URL testée: $api_url</div>";

// Test avec authentification
$response = wp_remote_get($api_url, [
    'headers' => [
        'Authorization' => 'Bearer ' . wp_create_nonce('wp_rest'),
    ],
    'cookies' => $_COOKIE
]);

if (is_wp_error($response)) {
    echo "<div class='error'>❌ Erreur HTTP: " . $response->get_error_message() . "</div>";
} else {
    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    echo "<div class='info'>📊 Status Code: $status_code</div>";
    echo "<div class='info'>📊 Taille réponse: " . strlen($body) . " chars</div>";
    
    if ($status_code === 200) {
        echo "<div class='success'>✅ API répond correctement</div>";
        $data = json_decode($body, true);
        
        if ($data) {
            echo "<h3>Structure de la réponse:</h3>";
            echo "<pre>" . print_r($data, true) . "</pre>";
            
            if (isset($data['data']) && is_array($data['data'])) {
                echo "<div class='info'>📊 Nombre de leads retournés: " . count($data['data']) . "</div>";
            }
        } else {
            echo "<div class='error'>❌ Réponse JSON invalide</div>";
            echo "<pre>$body</pre>";
        }
    } else {
        echo "<div class='error'>❌ Erreur Status $status_code</div>";
        echo "<pre>$body</pre>";
    }
}

echo "</div>";

// ================================================================================
// TEST 3: Test API endpoint vcard classique
// ================================================================================
if (!empty($user_vcards)) {
    echo "<div class='test-section'>";
    echo "<h2>🧪 TEST 3: API Endpoint vCard Classique</h2>";
    
    $first_vcard_id = $user_vcards[0]['vcard_id'];
    $api_url_vcard = home_url("/wp-json/gtmi_vcard/v1/leads/{$first_vcard_id}");
    echo "<div class='info'>🌐 URL testée: $api_url_vcard</div>";
    
    $response_vcard = wp_remote_get($api_url_vcard, [
        'cookies' => $_COOKIE
    ]);
    
    if (is_wp_error($response_vcard)) {
        echo "<div class='error'>❌ Erreur HTTP: " . $response_vcard->get_error_message() . "</div>";
    } else {
        $status_code = wp_remote_retrieve_response_code($response_vcard);
        $body = wp_remote_retrieve_body($response_vcard);
        
        echo "<div class='info'>📊 Status Code: $status_code</div>";
        
        if ($status_code === 200) {
            echo "<div class='success'>✅ API vCard répond correctement</div>";
            $data = json_decode($body, true);
            
            if (isset($data['data']) && is_array($data['data'])) {
                echo "<div class='info'>📊 Nombre de leads vCard: " . count($data['data']) . "</div>";
            }
        } else {
            echo "<div class='error'>❌ Erreur Status $status_code</div>";
            echo "<pre>$body</pre>";
        }
    }
    
    echo "</div>";
}

// ================================================================================
// TEST 4: Test direct de la fonction get_vcard_leads
// ================================================================================
if (!empty($user_vcards)) {
    echo "<div class='test-section'>";
    echo "<h2>🧪 TEST 4: Fonction get_vcard_leads directe</h2>";
    
    $first_vcard_id = $user_vcards[0]['vcard_id'];
    
    if (function_exists('get_vcard_leads')) {
        echo "<div class='success'>✅ Fonction get_vcard_leads() existe</div>";
        
        $direct_leads = get_vcard_leads($first_vcard_id);
        echo "<div class='info'>📊 Leads directs trouvés: " . count($direct_leads) . "</div>";
        
        if (!empty($direct_leads)) {
            echo "<h3>Premier lead trouvé:</h3>";
            echo "<pre>" . print_r($direct_leads[0], true) . "</pre>";
        }
    } else {
        echo "<div class='error'>❌ Fonction get_vcard_leads() manquante</div>";
    }
    
    echo "</div>";
}

// ================================================================================
// TEST 5: Vérifier la base de données directement
// ================================================================================
echo "<div class='test-section'>";
echo "<h2>🧪 TEST 5: Vérification Base de Données</h2>";

global $wpdb;

// Compter tous les leads
$total_leads = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'lead' AND post_status = 'publish'");
echo "<div class='info'>📊 Total leads en BDD: $total_leads</div>";

if ($total_leads > 0) {
    // Examiner les linked_virtual_card
    $sample_leads = $wpdb->get_results("
        SELECT p.ID, p.post_title, pm.meta_value as linked_vcard
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'linked_virtual_card'
        WHERE p.post_type = 'lead' AND p.post_status = 'publish'
        LIMIT 5
    ");
    
    echo "<h3>Échantillon de leads (format linked_virtual_card):</h3>";
    foreach ($sample_leads as $lead) {
        echo "<div class='info'>Lead #{$lead->ID}: {$lead->post_title}</div>";
        echo "<div style='margin-left: 20px; font-family: monospace; font-size: 12px;'>linked_vcard: " . ($lead->linked_vcard ?: 'NULL') . "</div>";
    }
    
    // Si on a des vCards, tester le pattern ACF
    if (!empty($user_vcards)) {
        $first_vcard_id = $user_vcards[0]['vcard_id'];
        $exact_pattern = 'a:1:{i:0;s:' . strlen($first_vcard_id) . ':"' . $first_vcard_id . '";}';
        
        echo "<h3>Test pattern ACF pour vCard $first_vcard_id:</h3>";
        echo "<div style='font-family: monospace; font-size: 12px;'>Pattern recherché: $exact_pattern</div>";
        
        $matching_leads = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'linked_virtual_card'
            WHERE p.post_type = 'lead' AND pm.meta_value = %s
        ", $exact_pattern));
        
        echo "<div class='info'>📊 Leads correspondants au pattern: $matching_leads</div>";
        
        // Test pattern plus large
        $like_pattern = '%"' . $first_vcard_id . '"%';
        $like_leads = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'linked_virtual_card'
            WHERE p.post_type = 'lead' AND pm.meta_value LIKE %s
        ", $like_pattern));
        
        echo "<div class='info'>📊 Leads avec LIKE pattern: $like_leads</div>";
    }
}

echo "</div>";

// ================================================================================
// RÉSUMÉ
// ================================================================================
echo "<div class='test-section' style='background-color: #f0f0f0;'>";
echo "<h2>📋 RÉSUMÉ DES TESTS</h2>";
echo "<ul>";
echo "<li>User ID: $user_id</li>";
echo "<li>Nombre de vCards: " . (empty($user_vcards) ? '0' : count($user_vcards)) . "</li>";
echo "<li>Total leads BDD: $total_leads</li>";
echo "</ul>";

if (empty($user_vcards)) {
    echo "<div class='error'>🚨 PROBLÈME PRINCIPAL: Aucune vCard trouvée pour cet utilisateur</div>";
    echo "<p>Vérifiez que l'utilisateur a bien passé une commande et que le système enterprise fonctionne.</p>";
} elseif ($total_leads == 0) {
    echo "<div class='error'>🚨 PROBLÈME: Aucun lead en base de données</div>";
    echo "<p>Il faut créer des leads de test ou vérifier que les contacts sont bien enregistrés.</p>";
} else {
    echo "<div class='info'>💡 Continuez avec les résultats ci-dessus pour identifier le problème exact.</div>";
}

echo "</div>";

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