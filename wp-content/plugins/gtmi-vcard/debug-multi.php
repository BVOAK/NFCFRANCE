<?php
/**
 * Script de Debug Multi-cartes NFC Enterprise - Version Complète
 * 
 * CRÉER CE FICHIER : wp-content/plugins/gtmi-vcard/debug-multi.php
 * ACCÉDER À : http://nfcfrance.loc/wp-content/plugins/gtmi-vcard/debug-multi.php
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
        $nfc_keywords = ['nfc', 'carte', 'vcard', 'virtuelle', 'digital'];
        foreach ($nfc_keywords as $keyword) {
            if (strpos($product_name, $keyword) !== false) {
                error_log("DEBUG: Product $product_id ($product_name) IS NFC (keyword: $keyword)");
                return true;
            }
        }
        
        // IDs spécifiques (adapte selon tes produits)
        $nfc_ids = [571, 572, 573, 574, 575];
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
    body { 
        font-family: Arial, sans-serif; 
        margin: 20px; 
        line-height: 1.6; 
        background: #f5f5f5;
    }
    .success { 
        color: #27ae60; 
        font-weight: bold; 
        background: #d5edda;
        padding: 10px;
        border-left: 4px solid #27ae60;
        margin: 5px 0;
    }
    .error { 
        color: #e74c3c; 
        font-weight: bold; 
        background: #f8d7da;
        padding: 10px;
        border-left: 4px solid #e74c3c;
        margin: 5px 0;
    }
    .info { 
        color: #3498db; 
        background: #d1ecf1;
        padding: 10px;
        border-left: 4px solid #3498db;
        margin: 5px 0;
    }
    .warning {
        color: #f39c12;
        background: #fff3cd;
        padding: 10px;
        border-left: 4px solid #f39c12;
        margin: 5px 0;
    }
    .section { 
        margin: 20px 0; 
        padding: 20px; 
        border: 1px solid #ddd; 
        background: white;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .section h2 {
        margin-top: 0;
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    .section h3 {
        color: #34495e;
        margin-top: 20px;
    }
    pre { 
        background: #f8f9fa; 
        padding: 15px; 
        border-radius: 4px; 
        overflow-x: auto;
        border: 1px solid #e9ecef;
    }
    .debug-btn { 
        background: #3498db; 
        color: white; 
        padding: 12px 20px; 
        text-decoration: none; 
        border-radius: 5px; 
        margin: 5px; 
        display: inline-block;
        font-weight: bold;
        transition: background 0.3s;
    }
    .debug-btn:hover {
        background: #2980b9;
        text-decoration: none;
        color: white;
    }
    .debug-btn.danger {
        background: #e74c3c;
    }
    .debug-btn.danger:hover {
        background: #c0392b;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
    }
    table th, table td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }
    table th {
        background: #f8f9fa;
        font-weight: bold;
    }
    table tr:nth-child(even) {
        background: #f8f9fa;
    }
    .highlight {
        background: #fff3cd !important;
        border-left: 4px solid #ffc107 !important;
    }
</style></head><body>";

echo "<h1>🐞 Debug Multi-cartes NFC Enterprise</h1>";
echo "<p><strong>Objectif:</strong> Analyser et corriger la création des cartes multiples</p>";

// Menu d'actions principal
echo "<div class='section'>";
echo "<h2>🎮 Actions Debug</h2>";

if (!isset($_GET['action'])) {
    echo "<a href='?action=test_create' class='debug-btn'>🧪 Test Création Multi-cartes</a>";
    echo "<a href='?action=analyze_products' class='debug-btn'>🔍 Analyser Produits</a>";
    echo "<a href='?action=check_functions' class='debug-btn'>⚙️ Vérifier Fonctions</a>";
    echo "<a href='?action=view_logs' class='debug-btn'>📝 Voir Logs</a>";
    echo "<a href='?action=cleanup' class='debug-btn danger'>🗑️ Nettoyer Tests</a>";
} else {
    echo "<a href='?' class='debug-btn'>🔄 Menu Principal</a>";
}

echo "</div>";

// Action: Test création multi-cartes
if (isset($_GET['action']) && $_GET['action'] === 'test_create') {
    echo "<div class='section'>";
    echo "<h2>🧪 Test Création Commande Multi-cartes</h2>";
    
    try {
        // ✅ ÉTAPE 1: Créer commande test
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
        
        // ✅ ÉTAPE 2: Trouver produit NFC de manière sécurisée
        $products = wc_get_products(['limit' => 50, 'status' => 'publish']);
        $product_id = null;
        $product = null;
        
        if (empty($products)) {
            throw new Exception("Aucun produit trouvé dans le catalogue WooCommerce");
        }
        
        // Chercher d'abord avec la fonction de détection
        if (function_exists('gtmi_vcard_is_nfc_product')) {
            foreach ($products as $test_product) {
                if (gtmi_vcard_is_nfc_product($test_product->get_id())) {
                    $product_id = $test_product->get_id();
                    $product = $test_product;
                    echo "<div class='success'>✅ Produit NFC trouvé: {$test_product->get_name()} (ID: $product_id)</div>";
                    break;
                }
            }
        }
        
        // Fallback par nom si pas trouvé
        if (!$product) {
            foreach ($products as $test_product) {
                $name = strtolower($test_product->get_name());
                if (strpos($name, 'nfc') !== false || strpos($name, 'carte') !== false || strpos($name, 'vcard') !== false) {
                    $product_id = $test_product->get_id();
                    $product = $test_product;
                    echo "<div class='info'>ℹ️ Produit candidat trouvé par nom: {$test_product->get_name()} (ID: $product_id)</div>";
                    break;
                }
            }
        }
        
        // Dernier recours : prendre le premier produit
        if (!$product) {
            $product = $products[0];
            $product_id = $product->get_id();
            echo "<div class='warning'>⚠️ Utilisation produit fallback: {$product->get_name()} (ID: $product_id)</div>";
        }
        
        // ✅ ÉTAPE 3: Ajouter 5 × ce produit à la commande
        echo "<h3>📦 Ajout Produit à la Commande</h3>";
        $order->add_product($product, 5);
        $order->calculate_totals();
        $order->set_status('processing');
        $order->save();
        
        echo "<div class='success'>✅ Commande #{$order->get_id()} créée avec 5 × {$product->get_name()}</div>";
        
        // ✅ ÉTAPE 4: Analyser les items de la commande
        echo "<h3>📋 Analyse des Items de Commande</h3>";
        $total_quantity = 0;
        $nfc_items_found = 0;
        
        foreach ($order->get_items() as $item_id => $item) {
            $item_product = $item->get_product();
            if ($item_product) {
                $quantity = $item->get_quantity();
                $total_quantity += $quantity;
                
                echo "<div class='info'>";
                echo "<p><strong>Item ID:</strong> $item_id</p>";
                echo "<p><strong>Produit:</strong> {$item_product->get_name()} (ID: {$item_product->get_id()})</p>";
                echo "<p><strong>Quantité:</strong> $quantity</p>";
                
                // Test fonction de détection NFC
                if (function_exists('gtmi_vcard_is_nfc_product')) {
                    $is_nfc = gtmi_vcard_is_nfc_product($item_product->get_id());
                    echo "<p><strong>Est un produit NFC:</strong> " . ($is_nfc ? 'OUI' : 'NON') . "</p>";
                    if ($is_nfc) {
                        $nfc_items_found += $quantity;
                    }
                } else {
                    echo "<p><strong>Fonction gtmi_vcard_is_nfc_product:</strong> N'existe pas</p>";
                }
                echo "</div>";
            }
        }
        
        echo "<div class='info'><strong>Total quantité:</strong> $total_quantity | <strong>Items NFC détectés:</strong> $nfc_items_found</div>";
        
        // ✅ ÉTAPE 5: Déclencher le traitement et observer
        echo "<h3>⚙️ Déclenchement du Traitement Enterprise</h3>";
        
        if (class_exists('NFC_Enterprise_Core')) {
            echo "<div class='info'>📝 Début traitement NFC_Enterprise_Core...</div>";
            
            // Capturer les logs avant traitement
            $user_id = $order->get_customer_id() ?: get_current_user_id();
            $cards_before = class_exists('NFC_Enterprise_Core') ? 
                NFC_Enterprise_Core::get_user_enterprise_cards($user_id) : [];
            $count_before = count($cards_before);
            
            // Traitement
            NFC_Enterprise_Core::process_order_vcards($order->get_id());
            
            // Vérifier après traitement
            $cards_after = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
            $count_after = count($cards_after);
            $cards_created_count = $count_after - $count_before;
            
            echo "<div class='success'>✅ Traitement terminé. Cartes avant: $count_before, après: $count_after, créées: $cards_created_count</div>";
            
        } else {
            echo "<div class='error'>❌ Classe NFC_Enterprise_Core introuvable</div>";
        }
        
        // ✅ ÉTAPE 6: Vérifier le résultat final
        echo "<h3>📊 Résultat Final</h3>";
        
        if (class_exists('NFC_Enterprise_Core')) {
            $cards_created = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
            $cards_for_order = array_filter($cards_created, function($card) use ($order) {
                return $card['order_id'] == $order->get_id();
            });
            
            $final_count = count($cards_for_order);
            
            if ($final_count === 5) {
                echo "<div class='success'>✅ <strong>SUCCESS!</strong> $final_count cartes créées pour cette commande (attendu: 5)</div>";
            } elseif ($final_count > 0) {
                echo "<div class='warning'>⚠️ <strong>Partiellement réussi:</strong> $final_count cartes créées (attendu: 5)</div>";
            } else {
                echo "<div class='error'>❌ <strong>ÉCHEC:</strong> Aucune carte créée</div>";
            }
            
            if (!empty($cards_for_order)) {
                echo "<h4>📋 Détails des Cartes Créées</h4>";
                echo "<table>";
                echo "<tr><th>vCard ID</th><th>Identifiant</th><th>Position</th><th>Statut</th><th>Date Création</th></tr>";
                
                foreach ($cards_for_order as $card) {
                    echo "<tr>";
                    echo "<td>{$card['vcard_id']}</td>";
                    echo "<td>{$card['card_identifier']}</td>";
                    echo "<td>{$card['card_position']}</td>";
                    echo "<td>{$card['card_status']}</td>";
                    echo "<td>{$card['created_at']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            // ✅ ÉTAPE 7: Debug des positions si problème
            if ($final_count > 0) {
                echo "<h4>🔍 Debug Positions</h4>";
                $positions = array_column($cards_for_order, 'card_position');
                sort($positions);
                $expected_positions = range(1, $final_count);
                
                echo "<p><strong>Positions trouvées:</strong> " . implode(', ', $positions) . "</p>";
                echo "<p><strong>Positions attendues:</strong> " . implode(', ', $expected_positions) . "</p>";
                
                if ($positions === $expected_positions) {
                    echo "<div class='success'>✅ Positions correctes</div>";
                } else {
                    echo "<div class='error'>❌ Positions incorrectes</div>";
                    
                    // Debug types
                    foreach ($cards_for_order as $card) {
                        $pos_type = gettype($card['card_position']);
                        echo "<p>Carte {$card['vcard_id']}: Position = {$card['card_position']} (type: $pos_type)</p>";
                    }
                }
            }
            
        } else {
            echo "<div class='error'>❌ Impossible de vérifier les cartes (classe manquante)</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ <strong>Erreur:</strong> " . $e->getMessage() . "</div>";
        echo "<div class='error'>📍 <strong>Fichier:</strong> " . $e->getFile() . " ligne " . $e->getLine() . "</div>";
    }
    
    echo "</div>";
}

// Action: Analyser les produits
if (isset($_GET['action']) && $_GET['action'] === 'analyze_products') {
    echo "<div class='section'>";
    echo "<h2>🔍 Analyse des Produits du Catalogue</h2>";
    
    $products = wc_get_products(['limit' => 50, 'status' => 'publish']);
    
    if (empty($products)) {
        echo "<div class='error'>❌ Aucun produit trouvé dans le catalogue</div>";
    } else {
        echo "<p><strong>Produits trouvés:</strong> " . count($products) . "</p>";
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Slug</th><th>Prix</th><th>Catégories</th><th>Est NFC?</th></tr>";
        
        $nfc_count = 0;
        foreach ($products as $product) {
            $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
            $categories_str = implode(', ', $categories);
            
            $is_nfc = function_exists('gtmi_vcard_is_nfc_product') ? 
                gtmi_vcard_is_nfc_product($product->get_id()) : false;
            
            if ($is_nfc) $nfc_count++;
            
            $row_class = $is_nfc ? 'highlight' : '';
            
            echo "<tr class='$row_class'>";
            echo "<td>{$product->get_id()}</td>";
            echo "<td>{$product->get_name()}</td>";
            echo "<td>{$product->get_slug()}</td>";
            echo "<td>" . wc_price($product->get_price()) . "</td>";
            echo "<td>$categories_str</td>";
            echo "<td>" . ($is_nfc ? '✅ OUI' : '❌ Non') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<div class='success'>✅ Produits NFC détectés: $nfc_count</div>";
        
        if ($nfc_count === 0) {
            echo "<div class='warning'>⚠️ Aucun produit NFC détecté. Vérifiez la fonction gtmi_vcard_is_nfc_product()</div>";
        }
    }
    
    echo "</div>";
}

// Action: Vérifier les fonctions
if (isset($_GET['action']) && $_GET['action'] === 'check_functions') {
    echo "<div class='section'>";
    echo "<h2>⚙️ Vérification des Fonctions et Classes</h2>";
    
    $checks = [
        'Classes' => [
            'NFC_Enterprise_Core' => class_exists('NFC_Enterprise_Core'),
            'WC_Order' => class_exists('WC_Order'),
            'WC_Product' => class_exists('WC_Product')
        ],
        'Fonctions Critiques' => [
            'gtmi_vcard_is_nfc_product' => function_exists('gtmi_vcard_is_nfc_product'),
            'wc_create_order' => function_exists('wc_create_order'),
            'wc_get_products' => function_exists('wc_get_products'),
            'get_current_user_id' => function_exists('get_current_user_id')
        ],
        'Méthodes NFC_Enterprise_Core' => [
            'process_order_vcards' => method_exists('NFC_Enterprise_Core', 'process_order_vcards'),
            'get_user_enterprise_cards' => method_exists('NFC_Enterprise_Core', 'get_user_enterprise_cards'),
            'get_nfc_items_from_order' => method_exists('NFC_Enterprise_Core', 'get_nfc_items_from_order'),
            'create_multiple_vcards' => method_exists('NFC_Enterprise_Core', 'create_multiple_vcards')
        ]
    ];
    
    foreach ($checks as $category => $functions) {
        echo "<h3>$category</h3>";
        echo "<table>";
        echo "<tr><th>Fonction/Classe</th><th>Status</th></tr>";
        
        foreach ($functions as $name => $exists) {
            $status = $exists ? "<span style='color: green;'>✅ Existe</span>" : "<span style='color: red;'>❌ Manquante</span>";
            echo "<tr><td>$name</td><td>$status</td></tr>";
        }
        
        echo "</table>";
    }
    
    // Vérification base de données
    echo "<h3>🗄️ Base de Données</h3>";
    global $wpdb;
    $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
    
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    
    if ($table_exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo "<div class='success'>✅ Table $table_name existe ($count enregistrements)</div>";
        
        // Afficher structure
        $columns = $wpdb->get_results("DESCRIBE $table_name");
        echo "<h4>Structure de la table:</h4>";
        echo "<table>";
        echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col->Field}</td><td>{$col->Type}</td><td>{$col->Null}</td><td>{$col->Default}</td></tr>";
        }
        echo "</table>";
        
    } else {
        echo "<div class='error'>❌ Table $table_name n'existe pas</div>";
        echo "<div class='info'>💡 Exécutez l'activation du plugin pour créer les tables</div>";
    }
    
    echo "</div>";
}

// Action: Nettoyer les tests
if (isset($_GET['action']) && $_GET['action'] === 'cleanup') {
    echo "<div class='section'>";
    echo "<h2>🗑️ Nettoyage des Données de Test</h2>";
    
    if (isset($_GET['confirm'])) {
        try {
            // Supprimer commandes de test
            $test_orders = wc_get_orders([
                'limit' => 50,
                'meta_query' => [
                    [
                        'key' => '_billing_company',
                        'value' => 'Debug Corp%',
                        'compare' => 'LIKE'
                    ]
                ]
            ]);
            
            $deleted_orders = 0;
            foreach ($test_orders as $order) {
                wp_delete_post($order->get_id(), true);
                $deleted_orders++;
            }
            
            // Nettoyer table enterprise (si elle existe)
            global $wpdb;
            $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
                $deleted_cards = $wpdb->query("DELETE FROM $table_name WHERE company_name LIKE 'Debug Corp%'");
                echo "<div class='success'>✅ $deleted_cards cartes de test supprimées</div>";
            }
            
            echo "<div class='success'>✅ $deleted_orders commandes de test supprimées</div>";
            echo "<div class='success'>✅ Nettoyage terminé</div>";
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erreur lors du nettoyage: " . $e->getMessage() . "</div>";
        }
        
    } else {
        echo "<div class='warning'>⚠️ Cette action supprimera toutes les commandes et cartes de test</div>";
        echo "<a href='?action=cleanup&confirm=1' class='debug-btn danger' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer toutes les données de test ?\")'>🗑️ Confirmer Suppression</a>";
    }
    
    echo "</div>";
}

// Action: Voir les logs
if (isset($_GET['action']) && $_GET['action'] === 'view_logs') {
    echo "<div class='section'>";
    echo "<h2>📝 Logs Système</h2>";
    
    $log_file = WP_CONTENT_DIR . '/debug.log';
    
    if (file_exists($log_file) && is_readable($log_file)) {
        $lines = file($log_file);
        $recent_lines = array_slice($lines, -50); // 50 dernières lignes
        
        $nfc_logs = array_filter($recent_lines, function($line) {
            return strpos($line, 'NFC') !== false || strpos($line, 'DEBUG') !== false;
        });
        
        if (!empty($nfc_logs)) {
            echo "<h3>Logs NFC récents:</h3>";
            echo "<pre>";
            foreach ($nfc_logs as $line) {
                echo htmlspecialchars($line);
            }
            echo "</pre>";
        } else {
            echo "<div class='info'>ℹ️ Aucun log NFC récent trouvé</div>";
        }
        
        echo "<div class='info'>📁 Fichier de log: $log_file</div>";
        echo "<div class='info'>📊 Taille: " . filesize($log_file) . " bytes</div>";
        echo "<div class='info'>🕐 Dernière modification: " . date('Y-m-d H:i:s', filemtime($log_file)) . "</div>";
        
    } else {
        echo "<div class='error'>❌ Fichier de log non accessible: $log_file</div>";
        echo "<div class='info'>💡 Assurez-vous que WP_DEBUG_LOG est activé dans wp-config.php</div>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<div class='info'>";
echo "<h3>🎯 Résumé des Objectifs</h3>";
echo "<ul>";
echo "<li><strong>Problème principal:</strong> Les commandes avec quantité > 1 ne créent qu'1 carte au lieu de X cartes</li>";
echo "<li><strong>Fonction manquante:</strong> gtmi_vcard_is_nfc_product() doit être définie</li>";
echo "<li><strong>Positions incorrectes:</strong> Vérifier le type des données (int vs string)</li>";
echo "<li><strong>Classes requises:</strong> NFC_Enterprise_Core avec toutes ses méthodes</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>