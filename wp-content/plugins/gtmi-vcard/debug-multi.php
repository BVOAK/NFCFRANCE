<?php
/**
 * Script de Debug Multi-cartes - Analyser pourquoi ça ne marche pas
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
        
        // Pour les tests, considérer TOUS les produits comme NFC
        // (à adapter selon tes vrais produits)
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }
        
        $product_name = strtolower($product->get_name());
        
        // Mots-clés NFC
        $nfc_keywords = ['nfc', 'carte', 'vcard', 'virtuelle', 'digital'];
        foreach ($nfc_keywords as $keyword) {
            if (strpos($product_name, $keyword) !== false) {
                error_log("DEBUG: Product $product_id ($product_name) IS NFC");
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
    wp_die('Accès non autorisé');
}

echo "<html><head><title>Debug Multi-cartes NFC</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
    .debug-btn { background: #e74c3c; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin: 5px; display: inline-block; }
</style></head><body>";

echo "<h1>🐞 Debug Multi-cartes NFC Enterprise</h1>";

// Test 1: Créer commande et analyser le comportement
if (isset($_GET['create_test'])) {
    echo "<div class='section'>";
    echo "<h2>🧪 Test Création Commande Multi-cartes</h2>";
    
    try {
        // Créer commande test avec quantité 5
        $order = wc_create_order();
        $order->set_billing_first_name('Debug');
        $order->set_billing_last_name('Multi');
        $order->set_billing_email('debug-' . time() . '@nfcfrance.com');
        $order->set_billing_company('Debug Corp Multi');
        $order->set_billing_phone('0123456789');
        $order->set_customer_id(get_current_user_id());
        
        // Trouver produit NFC
        $products = wc_get_products(['limit' => 50, 'status' => 'publish']);
        $product_id = null;
        foreach ($products as $product) {
            $name = strtolower($product->get_name());
            if (strpos($name, 'nfc') !== false || strpos($name, 'carte') !== false) {
                $product_id = $product->get_id();
                break;
            }
        }
        
        if (!$product_id && !empty($products)) {
            $product_id = $products[0]->get_id();
        }
        
        if ($product_id) {
            $product = wc_get_product($product_id);
            
            // ✅ ÉTAPE 1: Ajouter 5 × ce produit
            $order->add_product($product, 5);
            $order->calculate_totals();
            $order->set_status('processing');
            $order->save();
            
            echo "<div class='success'>✅ Commande #{$order->get_id()} créée avec 5 × {$product->get_name()}</div>";
            
            // ✅ ÉTAPE 2: Analyser les items de la commande
            echo "<h3>📋 Analyse des Items de Commande</h3>";
            foreach ($order->get_items() as $item_id => $item) {
                $item_product = $item->get_product();
                echo "<p><strong>Item ID:</strong> $item_id</p>";
                echo "<p><strong>Produit:</strong> {$item_product->get_name()} (ID: {$item_product->get_id()})</p>";
                echo "<p><strong>Quantité:</strong> {$item->get_quantity()}</p>";
                
                // Test fonction de détection NFC
                if (function_exists('gtmi_vcard_is_nfc_product')) {
                    $is_nfc = gtmi_vcard_is_nfc_product($item_product->get_id());
                    echo "<p><strong>Est un produit NFC:</strong> " . ($is_nfc ? 'OUI' : 'NON') . "</p>";
                } else {
                    echo "<p><strong>Fonction gtmi_vcard_is_nfc_product:</strong> N'existe pas</p>";
                }
                echo "<hr>";
            }
            
            // ✅ ÉTAPE 3: Tester get_nfc_items_from_order manuellement
            echo "<h3>🔍 Test get_nfc_items_from_order</h3>";
            
            // Simuler la fonction
            $nfc_items_debug = [];
            foreach ($order->get_items() as $item_id => $item) {
                $item_product = $item->get_product();
                
                if (function_exists('gtmi_vcard_is_nfc_product') && 
                    gtmi_vcard_is_nfc_product($item_product->get_id())) {
                    
                    $nfc_items_debug[] = [
                        'item_id' => $item_id,
                        'item' => $item,
                        'product' => $item_product,
                        'quantity' => $item->get_quantity(),
                        'product_name' => $item_product->get_name()
                    ];
                }
            }
            
            echo "<p><strong>Items NFC détectés:</strong> " . count($nfc_items_debug) . "</p>";
            
            $total_cards_debug = 0;
            foreach ($nfc_items_debug as $item) {
                $total_cards_debug += $item['quantity'];
                echo "<p>Item: {$item['product_name']} × {$item['quantity']}</p>";
            }
            
            echo "<p><strong>Total cartes calculé:</strong> $total_cards_debug</p>";
            
            // ✅ ÉTAPE 4: Déclencher traitement et observer
            echo "<h3>⚙️ Déclenchement du Traitement</h3>";
            
            // Capturer les logs en mémoire temporairement
            ob_start();
            error_log("=== DEBUG START FOR ORDER {$order->get_id()} ===");
            
            NFC_Enterprise_Core::process_order_vcards($order->get_id());
            
            error_log("=== DEBUG END FOR ORDER {$order->get_id()} ===");
            $output = ob_get_clean();
            
            // ✅ ÉTAPE 5: Vérifier le résultat
            echo "<h3>📊 Résultat Final</h3>";
            
            $cards_created = NFC_Enterprise_Core::get_user_enterprise_cards($order->get_customer_id());
            $cards_for_order = array_filter($cards_created, function($card) use ($order) {
                return $card['order_id'] == $order->get_id();
            });
            
            echo "<p><strong>Cartes créées pour cette commande:</strong> " . count($cards_for_order) . "</p>";
            
            if (!empty($cards_for_order)) {
                echo "<h4>Détail des cartes:</h4>";
                foreach ($cards_for_order as $card) {
                    echo "<p>- Carte {$card['card_identifier']} (vCard #{$card['vcard_id']}, Position {$card['card_position']})</p>";
                }
            }
            
            // ✅ ÉTAPE 6: Vérifier métadonnées commande
            echo "<h3>🔧 Métadonnées de Commande</h3>";
            $existing_vcard = $order->get_meta('_gtmi_vcard_vcard_id');
            echo "<p><strong>_gtmi_vcard_vcard_id:</strong> " . ($existing_vcard ?: 'Non défini') . "</p>";
            
            $enterprise_vcards = $order->get_meta('_gtmi_enterprise_vcards');
            echo "<p><strong>_gtmi_enterprise_vcards:</strong> " . (empty($enterprise_vcards) ? 'Non défini' : 'Défini (' . count($enterprise_vcards) . ' cartes)') . "</p>";
            
            // ✅ ÉTAPE 7: Nettoyer (optionnel)
            if (!isset($_GET['keep'])) {
                $order->delete(true);
                echo "<div class='info'>🧹 Commande test supprimée</div>";
                
                // Nettoyer aussi les vCards créées
                foreach ($cards_for_order as $card) {
                    wp_delete_post($card['vcard_id'], true);
                }
                
                // Nettoyer table enterprise
                global $wpdb;
                $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
                $wpdb->delete($table_name, ['order_id' => $order->get_id()]);
            } else {
                echo "<div class='info'>📌 Commande conservée pour inspection</div>";
            }
            
        } else {
            echo "<div class='error'>❌ Aucun produit trouvé dans le catalogue</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erreur: " . $e->getMessage() . "</div>";
    }
    
    echo "</div>";
}

// Menu d'actions
echo "<div class='section'>";
echo "<h2>🎮 Actions Debug</h2>";

if (!isset($_GET['create_test'])) {
    echo "<a href='?create_test=1' class='debug-btn'>🧪 Créer Commande Test</a>";
    echo "<a href='?create_test=1&keep=1' class='debug-btn'>🧪 Créer et Garder</a>";
}

echo "<a href='?' class='debug-btn'>🔄 Reset</a>";

$logs_url = admin_url('admin.php?page=wc-status&tab=logs');
echo "<a href='$logs_url' class='debug-btn' target='_blank'>📝 Voir Logs WP</a>";

echo "</div>";

// Analyse des fonctions disponibles
echo "<div class='section'>";
echo "<h2>🔍 Analyse des Fonctions</h2>";

$functions_to_check = [
    'NFC_Enterprise_Core::process_order_vcards' => method_exists('NFC_Enterprise_Core', 'process_order_vcards'),
    'NFC_Enterprise_Core::get_nfc_items_from_order' => method_exists('NFC_Enterprise_Core', 'get_nfc_items_from_order'),
    'NFC_Enterprise_Core::migrate_legacy_vcard_to_enterprise' => method_exists('NFC_Enterprise_Core', 'migrate_legacy_vcard_to_enterprise'),
    'gtmi_vcard_is_nfc_product' => function_exists('gtmi_vcard_is_nfc_product')
];

foreach ($functions_to_check as $func => $exists) {
    if ($exists) {
        echo "<div class='success'>✅ $func existe</div>";
    } else {
        echo "<div class='error'>❌ $func manquante</div>";
    }
}

echo "</div>";

// Info sur les logs récents
echo "<div class='section'>";
echo "<h2>📝 Logs Récents (5 dernières minutes)</h2>";

$log_files = [
    'debug.log' => ABSPATH . 'wp-content/debug.log',
    'error.log' => ini_get('error_log')
];

foreach ($log_files as $name => $path) {
    if ($path && file_exists($path)) {
        $modified = filemtime($path);
        $age = time() - $modified;
        
        if ($age < 300) { // 5 minutes
            echo "<p><strong>$name:</strong> Modifié il y a " . ($age) . " secondes</p>";
            
            $lines = file($path);
            $recent_lines = array_slice($lines, -20); // 20 dernières lignes
            
            echo "<h4>Dernières lignes:</h4><pre>";
            foreach ($recent_lines as $line) {
                if (strpos($line, 'NFC Enterprise') !== false) {
                    echo htmlspecialchars($line);
                }
            }
            echo "</pre>";
        }
    }
}

echo "</div>";

echo "<hr>";
echo "<p><strong>🎯 Objectif:</strong> Identifier pourquoi les commandes multi-cartes ne créent qu'1 carte au lieu de 5.</p>";

echo "</body></html>";
?>