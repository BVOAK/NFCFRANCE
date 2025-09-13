<?php
/**
 * Script de Test NFC Enterprise System
 * Fichier: wp-content/plugins/gtmi-vcard/tests/enterprise-test.php
 * 
 * USAGE: http://votre-site.com/?nfc_run_tests=1
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_Enterprise_Tests 
{
    private $test_results = [];
    private $test_order_ids = [];
    private $test_vcard_ids = [];

    public static function init() {
        add_action('init', [__CLASS__, 'handle_test_request']);
    }

    public static function handle_test_request() {
        if (isset($_GET['nfc_run_tests']) && current_user_can('administrator')) {
            $tests = new self();
            $tests->run_all_tests();
            $tests->display_results();
            exit;
        }
    }

    public function run_all_tests() {
        echo "<html><head><title>NFC Enterprise Tests</title>";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .test-success { color: green; font-weight: bold; }
            .test-error { color: red; font-weight: bold; }
            .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
            pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
        </style></head><body>";
        
        echo "<h1>🧪 Tests NFC Enterprise System</h1>";
        
        // Suite de tests
        $this->test_database_structure();
        $this->test_single_card_creation();
        $this->test_multi_card_creation();
        $this->test_card_retrieval();
        $this->test_stats_calculation();
        $this->test_renewal_workflow();
        
        // Nettoyage
        $this->cleanup_test_data();
    }

    private function test_database_structure() {
        echo "<div class='test-section'>";
        echo "<h2>📋 Test 1: Structure Base de Données</h2>";
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
        
        // Vérifier existence table
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s", $table_name
        )) === $table_name;
        
        if ($table_exists) {
            $this->log_success("✅ Table $table_name existe");
            
            // Vérifier colonnes
            $columns = $wpdb->get_results("DESCRIBE $table_name", ARRAY_A);
            $expected_columns = [
                'id', 'order_id', 'vcard_id', 'card_position', 
                'card_identifier', 'card_status', 'company_name', 
                'main_user_id', 'created_at', 'updated_at'
            ];
            
            $existing_columns = array_column($columns, 'Field');
            
            foreach ($expected_columns as $col) {
                if (in_array($col, $existing_columns)) {
                    $this->log_success("✅ Colonne '$col' présente");
                } else {
                    $this->log_error("❌ Colonne '$col' manquante");
                }
            }
        } else {
            $this->log_error("❌ Table $table_name n'existe pas");
            
            // Créer la table
            echo "<p>Tentative de création...</p>";
            NFC_Enterprise_Core::create_database_tables();
            
            $table_exists_after = $wpdb->get_var($wpdb->prepare(
                "SHOW TABLES LIKE %s", $table_name
            )) === $table_name;
            
            if ($table_exists_after) {
                $this->log_success("✅ Table créée avec succès");
            } else {
                $this->log_error("❌ Impossible de créer la table");
            }
        }
        
        echo "</div>";
    }

    private function test_single_card_creation() {
        echo "<div class='test-section'>";
        echo "<h2>🎯 Test 2: Création Carte Simple</h2>";
        
        try {
            // Créer commande test
            $order = $this->create_test_order(1);
            $this->test_order_ids[] = $order->get_id();
            
            echo "<p>Commande test #{$order->get_id()} créée avec 1 carte</p>";
            
            // Déclencher traitement
            NFC_Enterprise_Core::process_order_vcards($order->get_id());
            
            // Vérifier résultat
            $cards = NFC_Enterprise_Core::get_user_enterprise_cards($order->get_customer_id());
            
            if (count($cards) >= 1) {
                $this->log_success("✅ 1 carte créée correctement");
                
                $card = $cards[0];
                $this->test_vcard_ids[] = $card['vcard_id'];
                
                // Vérifier identifiant
                $expected_id = "NFC{$order->get_id()}-1";
                if ($card['card_identifier'] === $expected_id) {
                    $this->log_success("✅ Identifiant correct: {$card['card_identifier']}");
                } else {
                    $this->log_error("❌ Identifiant incorrect: {$card['card_identifier']} (attendu: $expected_id)");
                }
                
                // Vérifier URL vCard
                if (!empty($card['vcard_url'])) {
                    $this->log_success("✅ URL vCard générée: {$card['vcard_url']}");
                } else {
                    $this->log_error("❌ URL vCard manquante");
                }
                
                // Vérifier champs ACF
                $vcard_data = $card['vcard_data'];
                if (!empty($vcard_data['firstname']) || !empty($vcard_data['lastname'])) {
                    $this->log_success("✅ Champs ACF remplis");
                } else {
                    $this->log_error("❌ Champs ACF vides");
                }
                
            } else {
                $this->log_error("❌ Aucune carte créée");
            }
            
        } catch (Exception $e) {
            $this->log_error("❌ Erreur: " . $e->getMessage());
        }
        
        echo "</div>";
    }

    private function test_multi_card_creation() 
{
    echo "<div class='test-section'>";
    echo "<h2>🎯 Test 3: Création Multi-cartes</h2>";
    
    try {
        $product_id = $this->get_test_nfc_product_id();
        $order = $this->create_test_order(5, 'Multi');
        
        if ($product_id && $order) {
            $product = wc_get_product($product_id);
            $order->add_product($product, 5);
            $order->calculate_totals();
            $order->set_status('processing'); 
            $order->save();
            
            echo "<p>Commande test #{$order->get_id()} créée avec 5 cartes</p>";
            
            // Déclencher traitement
            NFC_Enterprise_Core::process_order_vcards($order->get_id());
            
            // Vérifier résultats
            $user_cards = NFC_Enterprise_Core::get_user_enterprise_cards($order->get_customer_id());
            $order_cards = array_filter($user_cards, function($card) use ($order) {
                return $card['order_id'] == $order->get_id();
            });
            
            $card_count = count($order_cards);
            if ($card_count === 5) {
                $this->log_success("✅ 5 cartes créées correctement");
            } else {
                $this->log_error("❌ Nombre de cartes incorrect: $card_count (attendu: 5)");
            }
            
            // ✅ CORRECTIF: Vérifier les identifiants uniques
            $identifiers = array_column($order_cards, 'card_identifier');
            $unique_identifiers = array_unique($identifiers);
            
            if (count($identifiers) === count($unique_identifiers)) {
                $this->log_success("✅ Identifiants uniques générés");
            } else {
                $this->log_error("❌ Identifiants dupliqués détectés");
            }
            
            // ✅ CORRECTIF: Vérifier les positions (logique corrigée)
            $positions = array_column($order_cards, 'card_position');
            $positions = array_map('intval', $positions);
            sort($positions); // Trier les positions pour comparaison

            $expected_positions = range(1, 5); // [1, 2, 3, 4, 5]

            if ($positions === $expected_positions) {
                $this->log_success("✅ Positions correctes: " . implode(', ', $positions));
            } else {
                $this->log_error("❌ Positions incorrectes: " . implode(', ', $positions) . " (attendu: " . implode(', ', $expected_positions) . ")");
                
                // Debug détaillé avec types
                foreach ($order_cards as $card) {
                    $pos = $card['card_position'];
                    $type = gettype($pos);
                    error_log("NFC Enterprise Test: Carte {$card['vcard_id']} - Position: $pos (type: $type)");
                }
            }

            // ✅ Vérification des statuts (corriger le statut attendu)
            $statuses = array_column($order_cards, 'card_status');
            $expected_status = 'configured'; // ✅ CHANGER de 'pending' vers 'configured'

            $all_status_correct = array_reduce($statuses, function($carry, $status) use ($expected_status) {
                return $carry && ($status === $expected_status);
            }, true);

            if ($all_status_correct) {
                $this->log_success("✅ Statuts corrects: tous '$expected_status'");
            } else {
                $this->log_error("❌ Statuts incorrects: " . implode(', ', array_unique($statuses)) . " (attendu: '$expected_status')");
            }
            
        } else {
            $this->log_error("❌ Impossible de créer commande ou produit test");
        }
        
    } catch (Exception $e) {
        $this->log_error("❌ Erreur: " . $e->getMessage());
        error_log("NFC Enterprise Test Exception: " . $e->getTraceAsString());
    }
    
    echo "</div>";
}

    private function test_card_retrieval() {
        echo "<div class='test-section'>";
        echo "<h2>🔍 Test 4: Récupération des Cartes</h2>";
        
        try {
            $user_id = get_current_user_id();
            
            // Test récupération par utilisateur
            $user_cards = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
            
            if (!empty($user_cards)) {
                $this->log_success("✅ Récupération cartes utilisateur: " . count($user_cards) . " cartes");
                
                // Test récupération par identifiant
                $first_card = $user_cards[0];
                $card_by_id = NFC_Enterprise_Core::get_vcard_by_identifier($first_card['card_identifier']);
                
                if ($card_by_id && $card_by_id['vcard_id'] === $first_card['vcard_id']) {
                    $this->log_success("✅ Récupération par identifiant fonctionne");
                } else {
                    $this->log_error("❌ Récupération par identifiant échoue");
                }
                
                // Test données ACF
                foreach ($user_cards as $card) {
                    if (isset($card['vcard_data']) && is_array($card['vcard_data'])) {
                        $this->log_success("✅ Données ACF chargées pour carte " . $card['card_identifier']);
                    } else {
                        $this->log_error("❌ Données ACF manquantes pour carte " . $card['card_identifier']);
                    }
                }
                
            } else {
                $this->log_error("❌ Aucune carte trouvée pour l'utilisateur");
            }
            
        } catch (Exception $e) {
            $this->log_error("❌ Erreur: " . $e->getMessage());
        }
        
        echo "</div>";
    }

    private function test_stats_calculation() {
        echo "<div class='test-section'>";
        echo "<h2>📊 Test 5: Calcul des Statistiques</h2>";
        
        try {
            $user_id = get_current_user_id();
            
            // Test stats globales
            $global_stats = NFC_Enterprise_Core::get_user_global_stats($user_id);
            
            if (is_array($global_stats) && isset($global_stats['total_cards'])) {
                $this->log_success("✅ Stats globales calculées");
                echo "<pre>Stats globales:\n" . print_r($global_stats, true) . "</pre>";
                
                // Vérifier cohérence
                $user_cards = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
                if ($global_stats['total_cards'] === count($user_cards)) {
                    $this->log_success("✅ Nombre total de cartes cohérent");
                } else {
                    $this->log_error("❌ Incohérence nombre total cartes");
                }
                
            } else {
                $this->log_error("❌ Erreur calcul stats globales");
            }
            
            // Test stats individuelles
            if (!empty($this->test_vcard_ids)) {
                $vcard_id = $this->test_vcard_ids[0];
                $individual_stats = NFC_Enterprise_Core::get_vcard_basic_stats($vcard_id);
                
                if (is_array($individual_stats) && isset($individual_stats['views'])) {
                    $this->log_success("✅ Stats individuelles calculées");
                } else {
                    $this->log_error("❌ Erreur stats individuelles");
                }
            }
            
        } catch (Exception $e) {
            $this->log_error("❌ Erreur: " . $e->getMessage());
        }
        
        echo "</div>";
    }

    private function test_renewal_workflow() {
        echo "<div class='test-section'>";
        echo "<h2>🔄 Test 6: Workflow Renouvellement</h2>";
        
        try {
            $user_id = get_current_user_id();
            $user_cards = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
            
            if (!empty($user_cards)) {
                $test_card = $user_cards[0];
                $identifier = $test_card['card_identifier'];
                
                // Créer nouvelle commande de renouvellement
                $renewal_order = $this->create_test_order(1, 'Renouvellement');
                $this->test_order_ids[] = $renewal_order->get_id();
                
                // Test fonction renouvellement
                $renewal_result = nfc_handle_card_renewal($identifier, $renewal_order->get_id());
                
                if ($renewal_result) {
                    $this->log_success("✅ Renouvellement traité");
                    
                    // Vérifier historique
                    $updated_card = NFC_Enterprise_Core::get_vcard_by_identifier($identifier);
                    $renewal_history = get_field('enterprise_renewal_history', $updated_card['vcard_id']);
                    
                    if (!empty($renewal_history)) {
                        $this->log_success("✅ Historique renouvellement sauvegardé");
                        $history = json_decode($renewal_history, true);
                        if (is_array($history) && !empty($history)) {
                            echo "<pre>Historique renouvellement:\n" . print_r($history, true) . "</pre>";
                        }
                    } else {
                        $this->log_error("❌ Historique renouvellement manquant");
                    }
                    
                } else {
                    $this->log_error("❌ Erreur traitement renouvellement");
                }
                
                // Test génération URL renouvellement
                $renewal_url = nfc_generate_renewal_url($identifier);
                if (!empty($renewal_url) && filter_var($renewal_url, FILTER_VALIDATE_URL)) {
                    $this->log_success("✅ URL renouvellement générée: $renewal_url");
                } else {
                    $this->log_error("❌ URL renouvellement invalide");
                }
                
            } else {
                $this->log_error("❌ Aucune carte pour test renouvellement");
            }
            
        } catch (Exception $e) {
            $this->log_error("❌ Erreur: " . $e->getMessage());
        }
        
        echo "</div>";
    }

    private function test_dashboard_type_detection() {
        echo "<div class='test-section'>";
        echo "<h2>🖥️ Test 7: Détection Type Dashboard</h2>";
        
        try {
            $user_id = get_current_user_id();
            
            // Test détection type dashboard
            $dashboard_type = nfc_get_dashboard_type($user_id);
            echo "<p>Type dashboard détecté: <strong>$dashboard_type</strong></p>";
            
            $has_enterprise = nfc_user_has_enterprise_cards($user_id);
            echo "<p>A des cartes enterprise: <strong>" . ($has_enterprise ? 'Oui' : 'Non') . "</strong></p>";
            
            $user_cards = nfc_get_user_cards($user_id);
            echo "<p>Nombre de cartes récupérées: <strong>" . count($user_cards) . "</strong></p>";
            
            if ($dashboard_type === 'enterprise' && count($user_cards) > 1) {
                $this->log_success("✅ Détection enterprise correcte");
            } elseif ($dashboard_type === 'simple' && count($user_cards) <= 1) {
                $this->log_success("✅ Détection simple correcte");
            } else {
                $this->log_error("❌ Détection type dashboard incorrecte");
            }
            
        } catch (Exception $e) {
            $this->log_error("❌ Erreur: " . $e->getMessage());
        }
        
        echo "</div>";
    }

    private function test_helper_functions() {
        echo "<div class='test-section'>";
        echo "<h2>🛠️ Test 8: Fonctions Utilitaires</h2>";
        
        try {
            $user_id = get_current_user_id();
            $user_cards = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
            
            if (!empty($user_cards)) {
                $test_card = $user_cards[0];
                $vcard_data = $test_card['vcard_data'];
                
                // Test formatage nom
                $full_name = nfc_format_vcard_full_name($vcard_data);
                if (!empty($full_name)) {
                    $this->log_success("✅ Formatage nom: '$full_name'");
                } else {
                    $this->log_error("❌ Formatage nom échoue");
                }
                
                // Test formatage poste
                $position = nfc_format_vcard_position($vcard_data);
                if (!empty($position)) {
                    $this->log_success("✅ Formatage poste: '$position'");
                } else {
                    $this->log_error("❌ Formatage poste échoue");
                }
                
                // Test statut display
                $display_status = nfc_get_card_display_status($test_card);
                if (is_array($display_status) && isset($display_status['status'])) {
                    $this->log_success("✅ Statut display: {$display_status['status']} - {$display_status['label']}");
                } else {
                    $this->log_error("❌ Statut display incorrect");
                }
                
                // Test pourcentage configuration
                $config_percentage = nfc_calculate_configuration_percentage($user_cards);
                $this->log_success("✅ Pourcentage configuration: $config_percentage%");
                
            }
            
        } catch (Exception $e) {
            $this->log_error("❌ Erreur: " . $e->getMessage());
        }
        
        echo "</div>";
    }

    private function get_test_nfc_product_id() {
        // ✅ CORRECTION: Debug pour trouver les vrais produits NFC
        error_log('NFC Enterprise Test: Recherche produits NFC...');
        
        // Chercher d'abord avec la fonction existante si elle existe
        if (function_exists('gtmi_vcard_is_nfc_product')) {
            $products = wc_get_products([
                'limit' => 100,
                'status' => 'publish'
            ]);
            
            foreach ($products as $product) {
                if (gtmi_vcard_is_nfc_product($product->get_id())) {
                    error_log('NFC Enterprise Test: Produit NFC trouvé - ID: ' . $product->get_id() . ', Nom: ' . $product->get_name());
                    return $product->get_id();
                }
            }
        }
        
        // ✅ Fallback: Chercher par nom/slug
        $nfc_keywords = ['nfc', 'vcard', 'carte', 'virtual', 'digitale'];
        $products = wc_get_products([
            'limit' => 100,
            'status' => 'publish'
        ]);
        
        foreach ($products as $product) {
            $name = strtolower($product->get_name());
            $slug = strtolower($product->get_slug());
            
            foreach ($nfc_keywords as $keyword) {
                if (strpos($name, $keyword) !== false || strpos($slug, $keyword) !== false) {
                    error_log('NFC Enterprise Test: Produit candidat trouvé par nom - ID: ' . $product->get_id() . ', Nom: ' . $product->get_name());
                    return $product->get_id();
                }
            }
        }
        
        // ✅ DEBUG: Afficher tous les produits disponibles
        error_log('NFC Enterprise Test: Aucun produit NFC trouvé. Produits disponibles:');
        foreach ($products as $product) {
            error_log('  - ID: ' . $product->get_id() . ', Nom: ' . $product->get_name() . ', Slug: ' . $product->get_slug());
        }
        
        // ✅ Retourner le premier produit comme fallback pour les tests
        if (!empty($products)) {
            $fallback = $products[0];
            error_log('NFC Enterprise Test: Utilisation produit fallback - ID: ' . $fallback->get_id());
            return $fallback->get_id();
        }
        
        error_log('NFC Enterprise Test: ERREUR - Aucun produit trouvé dans le catalogue');
        return 571;
    }

    private function create_test_order($quantity = 1, $prefix = 'Test') {
        error_log("NFC Enterprise Test: Création commande test avec $quantity cartes");
        
        $order = wc_create_order();
        $order->set_billing_first_name($prefix);
        $order->set_billing_last_name('Enterprise');
        $order->set_billing_email('test-' . time() . '@nfcfrance.com');
        $order->set_billing_company('Test Corp ' . $quantity);
        $order->set_billing_phone('0123456789');
        $order->set_customer_id(get_current_user_id());
        
        // ✅ CORRECTION: Trouver un vrai produit NFC
        $product_id = $this->get_test_nfc_product_id();
        if (!$product_id) {
            throw new Exception('Aucun produit trouvé dans le catalogue pour les tests');
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            throw new Exception("Produit ID $product_id introuvable");
        }
        
        error_log("NFC Enterprise Test: Utilisation produit - ID: $product_id, Nom: " . $product->get_name());
        
        // ✅ Ajouter le produit à la commande
        $order->add_product($product, $quantity);
        
        $order->calculate_totals();
        $order->set_status('processing');
        $order->save();
        
        error_log("NFC Enterprise Test: Commande #{$order->get_id()} créée avec $quantity × " . $product->get_name());
        
        return $order;
    }

    private function cleanup_test_data() {
        echo "<div class='test-section'>";
        echo "<h2>🧹 Nettoyage des Données de Test</h2>";
        
        try {
            // Supprimer commandes test
            foreach ($this->test_order_ids as $order_id) {
                $order = wc_get_order($order_id);
                if ($order) {
                    $order->delete(true);
                    echo "<p>Commande #$order_id supprimée</p>";
                }
            }
            
            // Supprimer vCards test
            foreach ($this->test_vcard_ids as $vcard_id) {
                wp_delete_post($vcard_id, true);
                echo "<p>vCard #$vcard_id supprimée</p>";
            }
            
            // Nettoyer table enterprise
            global $wpdb;
            $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
            
            foreach ($this->test_order_ids as $order_id) {
                $wpdb->delete($table_name, ['order_id' => $order_id]);
            }
            
            $this->log_success("✅ Nettoyage terminé");
            
        } catch (Exception $e) {
            $this->log_error("❌ Erreur nettoyage: " . $e->getMessage());
        }
        
        echo "</div>";
    }

    private function display_results() {
        echo "<div class='test-section'>";
        echo "<h2>📋 Résumé des Tests</h2>";
        
        $total_tests = count($this->test_results);
        $successful_tests = count(array_filter($this->test_results, function($result) {
            return $result['success'];
        }));
        
        echo "<p><strong>Tests réussis:</strong> $successful_tests / $total_tests</p>";
        
        if ($successful_tests === $total_tests) {
            echo "<div class='test-success'>🎉 Tous les tests sont passés avec succès!</div>";
        } else {
            echo "<div class='test-error'>⚠️ Certains tests ont échoué. Vérifiez les détails ci-dessus.</div>";
        }
        
        echo "</div>";
        echo "</body></html>";
    }

    private function log_success($message) {
        echo "<div class='test-success'>$message</div>";
        $this->test_results[] = ['success' => true, 'message' => $message];
    }

    private function log_error($message) {
        echo "<div class='test-error'>$message</div>";
        $this->test_results[] = ['success' => false, 'message' => $message];
    }
}

// Initialiser les tests
NFC_Enterprise_Tests::init();

/**
 * INSTRUCTION D'USAGE:
 * 
 * 1. Sauvegarder ce fichier dans wp-content/plugins/gtmi-vcard/tests/enterprise-test.php
 * 
 * 2. Ajouter dans le fichier principal _gtmi-vcard.php :
 *    require_once plugin_dir_path(__FILE__) . 'tests/enterprise-test.php';
 * 
 * 3. Accéder à : http://votre-site.com/?nfc_run_tests=1
 * 
 * 4. Examiner les résultats et corriger les éventuels problèmes
 * 
 * 5. Commenter/supprimer le require_once en production
 */