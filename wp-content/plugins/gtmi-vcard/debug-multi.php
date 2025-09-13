<?php
/**
 * REMPLACER ENTIÈREMENT debug-multi.php par ce code
 * Fix définitif de la variable $product non définie
 */

// Charger WordPress
require_once('../../../wp-load.php');

// 🚨 FONCTION TEMPORAIRE - À SUPPRIMER APRÈS AVOIR FIXÉ functions.php
if (!function_exists('gtmi_vcard_is_nfc_product')) {
    function gtmi_vcard_is_nfc_product($product_id) {
        error_log("DEBUG: Checking product $product_id for NFC");
        
        $product = wc_get_product($product_id);
        if (!$product) {
            error_log("DEBUG: Product $product_id not found");
            return false;
        }
        
        $product_name = strtolower($product->get_name());
        error_log("DEBUG: Product name: '$product_name'");
        
        // Mots-clés NFC
        $nfc_keywords = ['nfc', 'carte', 'vcard', 'virtuelle', 'digital', 'porte', 'clés'];
        foreach ($nfc_keywords as $keyword) {
            if (strpos($product_name, $keyword) !== false) {
                error_log("DEBUG: Product $product_id ($product_name) IS NFC (keyword: $keyword)");
                return true;
            }
        }
        
        // IDs spécifiques (adapte selon tes produits)
        $nfc_ids = [571, 572, 573, 574, 575, 3294]; // Ajouté 3294 pour "Porte-clés NFC"
        if (in_array($product_id, $nfc_ids)) {
            error_log("DEBUG: Product $product_id IS NFC (by ID)");
            return true;
        }
        
        error_log("DEBUG: Product $product_id ($product_name) is NOT NFC");
        return false;
    }
}

// Vérifier les permissions
if (!current_user_can('administrator')) {
    wp_die('Accès non autorisé. Connectez-vous comme administrateur.');
}

// Headers et styles
echo "<html><head><title>Debug Multi-cartes NFC</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; background: #f5f5f5; }
    .success { color: #27ae60; font-weight: bold; background: #d5edda; padding: 10px; border-left: 4px solid #27ae60; margin: 5px 0; }
    .error { color: #e74c3c; font-weight: bold; background: #f8d7da; padding: 10px; border-left: 4px solid #e74c3c; margin: 5px 0; }
    .info { color: #3498db; background: #d1ecf1; padding: 10px; border-left: 4px solid #3498db; margin: 5px 0; }
    .warning { color: #f39c12; background: #fff3cd; padding: 10px; border-left: 4px solid #f39c12; margin: 5px 0; }
    .section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; background: white; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .section h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
    .debug-btn { background: #3498db; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; font-weight: bold; }
    .debug-btn:hover { background: #2980b9; text-decoration: none; color: white; }
    .debug-btn.danger { background: #e74c3c; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    table th, table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    table th { background: #f8f9fa; font-weight: bold; }
</style></head><body>";

echo "<h1>🐞 Debug Multi-cartes NFC Enterprise</h1>";

// Menu d'actions
echo "<div class='section'>";
echo "<h2>🎮 Actions Debug</h2>";
if (!isset($_GET['action'])) {
    echo "<a href='?action=test_create' class='debug-btn'>🧪 Test Création Multi-cartes</a>";
    echo "<a href='?action=analyze_products' class='debug-btn'>🔍 Analyser Produits</a>";
} else {
    echo "<a href='?' class='debug-btn'>🔄 Menu Principal</a>";
}
echo "</div>";

// Action: Test création multi-cartes
if (isset($_GET['action']) && $_GET['action'] === 'test_create') {
    echo "<div class='section'>";
    echo "<h2>🧪 Test Création Commande Multi-cartes</h2>";
    
    try {
        // ÉTAPE 1: Créer commande test
        $order = wc_create_order();
        if (!$order) {
            throw new Exception("Impossible de créer la commande test");
        }
        
        $order->set_billing_first_name('Debug');
        $order->set_billing_last_name('Multi Test');
        $order->set_billing_email('debug-multi-' . time() . '@nfcfrance.com');
        $order->set_billing_company('Debug Corp Multi');
        $order->set_billing_phone('0123456789');
        $order->set_customer_id(get_current_user_id());
        
        echo "<h3>🔍 Recherche Produit NFC</h3>";
        
        // ÉTAPE 2: Trouver produit NFC (FIX: variables bien définies)
        $products = wc_get_products(['limit' => 50, 'status' => 'publish']);
        $selected_product = null;
        $selected_product_id = null;
        
        if (empty($products)) {
            throw new Exception("Aucun produit trouvé dans le catalogue WooCommerce");
        }
        
        // Chercher produit NFC avec fonction de détection
        if (function_exists('gtmi_vcard_is_nfc_product')) {
            foreach ($products as $test_product) {
                if (gtmi_vcard_is_nfc_product($test_product->get_id())) {
                    $selected_product_id = $test_product->get_id();
                    $selected_product = $test_product;
                    echo "<div class='success'>✅ Produit NFC trouvé: {$test_product->get_name()} (ID: {$selected_product_id})</div>";
                    break;
                }
            }
        }
        
        // Fallback si pas trouvé
        if (!$selected_product) {
            $selected_product = $products[0];
            $selected_product_id = $selected_product->get_id();
            echo "<div class='warning'>⚠️ Utilisation produit fallback: {$selected_product->get_name()} (ID: {$selected_product_id})</div>";
        }
        
        // ÉTAPE 3: Ajouter 5x produit à la commande (FIX: variables bien définies)
        echo "<h3>📦 Ajout Produit à la Commande</h3>";
        $order->add_product($selected_product, 5);
        $order->calculate_totals();
        $order->set_status('processing');
        $order->save();
        
        echo "<div class='success'>✅ Commande #{$order->get_id()} créée avec 5 × {$selected_product->get_name()}</div>";
        
        // ÉTAPE 4: Analyser items
        echo "<h3>📋 Analyse des Items de Commande</h3>";
        $total_quantity = 0;
        $nfc_items_found = 0;
        
        foreach ($order->get_items() as $item_id => $item) {
            $item_product = $item->get_product();
            if ($item_product) {
                $quantity = $item->get_quantity();
                $total_quantity += $quantity;
                
                echo "<div class='info'>";
                echo "<p><strong>Produit:</strong> {$item_product->get_name()} × $quantity</p>";
                
                if (function_exists('gtmi_vcard_is_nfc_product')) {
                    $is_nfc = gtmi_vcard_is_nfc_product($item_product->get_id());
                    echo "<p><strong>Est NFC:</strong> " . ($is_nfc ? 'OUI' : 'NON') . "</p>";
                    if ($is_nfc) {
                        $nfc_items_found += $quantity;
                    }
                }
                echo "</div>";
            }
        }
        
        echo "<div class='info'><strong>Total cartes attendues:</strong> $nfc_items_found</div>";
        
        // ÉTAPE 5: Traitement enterprise
        echo "<h3>⚙️ Traitement Enterprise</h3>";

if (class_exists('NFC_Enterprise_Core')) {
    echo "<div class='info'>📝 Début traitement NFC_Enterprise_Core...</div>";
    
    // ✅ CORRECTIF: Compter directement en base avant traitement
    global $wpdb;
    $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
    $user_id = $order->get_customer_id() ?: get_current_user_id();
    
    $count_before_db = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE main_user_id = %d",
        $user_id
    ));
    
    echo "<div class='info'>📊 Cartes avant traitement (BDD directe): $count_before_db</div>";
    
    // Traitement
    NFC_Enterprise_Core::process_order_vcards($order->get_id());
    
    // ✅ CORRECTIF: Compter directement en base après traitement
    $count_after_db = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE main_user_id = %d",
        $user_id
    ));
    
    // ✅ CORRECTIF: Compter les cartes spécifiques à cette commande
    $count_for_this_order = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE order_id = %d",
        $order->get_id()
    ));
    
    $cards_created_count = $count_after_db - $count_before_db;
    
    echo "<div class='success'>✅ Traitement terminé</div>";
    echo "<div class='info'>📊 Cartes après traitement (BDD directe): $count_after_db</div>";
    echo "<div class='info'>📊 Cartes créées (différence): $cards_created_count</div>";
    echo "<div class='info'>📊 Cartes pour cette commande: $count_for_this_order</div>";
    
    // ✅ CORRECTIF: Récupérer les cartes directement de la BDD pour cette commande
    $order_cards_db = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE order_id = %d ORDER BY card_position ASC",
        $order->get_id()
    ), ARRAY_A);
    
    echo "<h3>📊 Résultat Final (Données BDD Directes)</h3>";
    
    $final_count = count($order_cards_db);
    
    if ($final_count === 5) {
        echo "<div class='success'>🎉 <strong>SUCCESS!</strong> $final_count cartes créées pour cette commande (attendu: 5)</div>";
    } elseif ($final_count > 0) {
        echo "<div class='warning'>⚠️ <strong>Partiellement réussi:</strong> $final_count cartes créées (attendu: 5)</div>";
    } else {
        echo "<div class='error'>❌ <strong>ÉCHEC:</strong> Aucune carte créée</div>";
    }
    
    if (!empty($order_cards_db)) {
        echo "<h4>📋 Détails des Cartes Créées (BDD Directe)</h4>";
        echo "<table>";
        echo "<tr><th>ID</th><th>vCard ID</th><th>Identifiant</th><th>Position</th><th>Statut</th><th>Date Création</th></tr>";
        
        foreach ($order_cards_db as $card) {
            echo "<tr>";
            echo "<td>{$card['id']}</td>";
            echo "<td>{$card['vcard_id']}</td>";
            echo "<td>{$card['card_identifier']}</td>";
            echo "<td>{$card['card_position']}</td>";
            echo "<td>{$card['card_status']}</td>";
            echo "<td>{$card['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Debug comparaison get_user_enterprise_cards vs BDD directe
        echo "<h4>🔍 Debug: Comparaison Méthodes Récupération</h4>";
        
        $cards_via_method = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
        $cards_method_for_order = array_filter($cards_via_method, function($card) use ($order) {
            return $card['order_id'] == $order->get_id();
        });
        
        echo "<p><strong>Via get_user_enterprise_cards():</strong> " . count($cards_method_for_order) . " cartes pour cette commande</p>";
        echo "<p><strong>Via requête BDD directe:</strong> $final_count cartes pour cette commande</p>";
        
        if (count($cards_method_for_order) !== $final_count) {
            echo "<div class='error'>❌ PROBLÈME: Différence entre les deux méthodes de récupération!</div>";
            echo "<div class='info'>💡 La méthode get_user_enterprise_cards() ne retourne pas les nouvelles cartes</div>";
            
            // Debug plus poussé
            echo "<h5>Debug Méthode get_user_enterprise_cards()</h5>";
            echo "<p>Total cartes via méthode: " . count($cards_via_method) . "</p>";
            if (!empty($cards_via_method)) {
                echo "<p>Identifiants via méthode: ";
                $identifiers = array_column($cards_via_method, 'card_identifier');
                echo implode(', ', $identifiers) . "</p>";
            }
            
            echo "<h5>Debug BDD Directe</h5>";
            echo "<p>Identifiants via BDD: ";
            $db_identifiers = array_column($order_cards_db, 'card_identifier');
            echo implode(', ', $db_identifiers) . "</p>";
            
        } else {
            echo "<div class='success'>✅ Cohérence entre les deux méthodes</div>";
        }
        
        // Test des positions
        echo "<h4>🔍 Debug Positions</h4>";
        $positions = array_column($order_cards_db, 'card_position');
        $positions = array_map('intval', $positions); // Convertir en int
        sort($positions);
        $expected_positions = range(1, $final_count);
        
        echo "<p><strong>Positions trouvées:</strong> " . implode(', ', $positions) . "</p>";
        echo "<p><strong>Positions attendues:</strong> " . implode(', ', $expected_positions) . "</p>";
        
        if ($positions === $expected_positions) {
            echo "<div class='success'>✅ Positions correctes</div>";
        } else {
            echo "<div class='error'>❌ Positions incorrectes</div>";
        }
    }
    
} else {
    echo "<div class='error'>❌ Classe NFC_Enterprise_Core introuvable</div>";
}
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ <strong>Erreur:</strong> " . $e->getMessage() . "</div>";
        echo "<div class='error'>📍 <strong>Ligne:</strong> " . $e->getLine() . " dans " . basename($e->getFile()) . "</div>";
    }
    
    echo "</div>";
}

// Action: Analyser produits
if (isset($_GET['action']) && $_GET['action'] === 'analyze_products') {
    echo "<div class='section'>";
    echo "<h2>🔍 Analyse des Produits</h2>";
    
    $products = wc_get_products(['limit' => 50, 'status' => 'publish']);
    
    if (empty($products)) {
        echo "<div class='error'>❌ Aucun produit trouvé</div>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Prix</th><th>Est NFC?</th></tr>";
        
        foreach ($products as $product) {
            $is_nfc = function_exists('gtmi_vcard_is_nfc_product') ? 
                gtmi_vcard_is_nfc_product($product->get_id()) : false;
                
            $highlight = $is_nfc ? 'style="background: #fff3cd;"' : '';
            
            echo "<tr $highlight>";
            echo "<td>{$product->get_id()}</td>";
            echo "<td>{$product->get_name()}</td>";
            echo "<td>" . wc_price($product->get_price()) . "</td>";
            echo "<td>" . ($is_nfc ? '✅ OUI' : '❌ Non') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<div class='info'>";
echo "<h3>🎯 Points de Contrôle</h3>";
echo "<ul>";
echo "<li><strong>Fonction gtmi_vcard_is_nfc_product():</strong> " . (function_exists('gtmi_vcard_is_nfc_product') ? '✅ OK' : '❌ Manquante') . "</li>";
echo "<li><strong>Classe NFC_Enterprise_Core:</strong> " . (class_exists('NFC_Enterprise_Core') ? '✅ OK' : '❌ Manquante') . "</li>";
echo "<li><strong>Tables BDD:</strong> À vérifier avec action 'check_functions'</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>