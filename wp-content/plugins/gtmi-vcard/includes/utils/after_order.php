<?php
/**
 * NOUVEAU SYSTÈME DE TRAITEMENT DES COMMANDES
 * 
 * REMPLACER includes/utils/after_order.php par ce code complet
 */

add_action('woocommerce_order_status_changed', 'nfc_order_change_status', 10, 3);

/**
 * Hook principal - Traitement selon statut commande
 */
function nfc_order_change_status($order_id, $old_status, $new_status)
{
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }
    
    if ('processing' === $new_status || 'completed' === $new_status) {
        error_log("NFC: Order $order_id status changed to $new_status - Processing");
        nfc_process_order_products($order_id);
    }
}

/**
 * Hook paiement réussi
 */
function nfc_order_payment_success($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    error_log("NFC: Order $order_id payment successful - Processing");
    nfc_process_order_products($order_id);
}
add_action('woocommerce_thankyou', 'nfc_order_payment_success', 10, 1);

/**
 * FONCTION PRINCIPALE - Traiter les produits de la commande
 */
function nfc_process_order_products($order_id)
{
    error_log("NFC: Processing order $order_id");
    
    $order = wc_get_order($order_id);
    if (!$order) {
        error_log("NFC: Order $order_id not found");
        return;
    }
    
    // Éviter les doublons
    $already_processed = $order->get_meta('_nfc_processed');
    if ($already_processed) {
        error_log("NFC: Order $order_id already processed");
        return;
    }
    
    // Analyser les items de la commande
    $order_analysis = nfc_analyze_order_items($order);
    
    error_log("NFC: Order $order_id analysis: " . json_encode([
        'vcard_items' => count($order_analysis['vcard_items']),
        'vcard_total_quantity' => $order_analysis['vcard_total_quantity'],
        'google_items' => count($order_analysis['google_items']),
        'google_total_quantity' => $order_analysis['google_total_quantity']
    ]));
    
    $results = [
        'vcards_created' => [],
        'google_profiles_created' => [],
        'errors' => []
    ];
    
    // Traitement vCard : X produits = X vCards individuelles
    if ($order_analysis['vcard_total_quantity'] > 0) {
        $vcards_result = nfc_create_vcards_from_order($order, $order_analysis['vcard_items']);
        $results['vcards_created'] = $vcards_result['created'];
        $results['errors'] = array_merge($results['errors'], $vcards_result['errors']);
    }
    
    // Traitement Avis Google : X produits = 1 profil partagé
    if ($order_analysis['google_total_quantity'] > 0) {
        $google_result = nfc_create_google_profile_from_order($order, $order_analysis['google_items']);
        if ($google_result['profile_id']) {
            $results['google_profiles_created'][] = $google_result['profile_id'];
        }
        if ($google_result['error']) {
            $results['errors'][] = $google_result['error'];
        }
    }
    
    // Marquer comme traité
    $order->add_meta_data('_nfc_processed', date('Y-m-d H:i:s'));
    $order->add_meta_data('_nfc_processing_results', $results);
    $order->save();
    
    // Log résultat final
    error_log("NFC: Order $order_id processing complete - " . 
        count($results['vcards_created']) . " vCards, " . 
        count($results['google_profiles_created']) . " Google profiles, " . 
        count($results['errors']) . " errors");
}

/**
 * Analyser les items de la commande par catégorie
 */
function nfc_analyze_order_items($order)
{
    $analysis = [
        'vcard_items' => [],
        'vcard_total_quantity' => 0,
        'google_items' => [],
        'google_total_quantity' => 0,
        'other_items' => []
    ];
    
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        $product_type = nfc_detect_product_type($product_id);
        $quantity = $item->get_quantity();
        
        $item_data = [
            'item_id' => $item_id,
            'item' => $item,
            'product_id' => $product_id,
            'product_type' => $product_type,
            'quantity' => $quantity
        ];
        
        if ($product_type === 'vcard') {
            $analysis['vcard_items'][] = $item_data;
            $analysis['vcard_total_quantity'] += $quantity;
        } elseif (in_array($product_type, ['google_reviews_card', 'google_reviews_plaque'])) {
            $analysis['google_items'][] = $item_data;
            $analysis['google_total_quantity'] += $quantity;
        } else {
            $analysis['other_items'][] = $item_data;
        }
    }
    
    return $analysis;
}

/**
 * Créer X vCards individuelles depuis les items vCard
 */
function nfc_create_vcards_from_order($order, $vcard_items)
{
    $order_id = $order->get_id();
    $customer_id = $order->get_customer_id() ?: 1;
    
    $results = [
        'created' => [],
        'errors' => []
    ];
    
    $position = 1;
    
    foreach ($vcard_items as $item_data) {
        $item = $item_data['item'];
        $quantity = $item_data['quantity'];
        
        // Créer X vCards pour cette ligne (selon quantité)
        for ($i = 1; $i <= $quantity; $i++) {
            $vcard_result = nfc_create_single_vcard($order, $position, $item);
            
            if ($vcard_result['success']) {
                $results['created'][] = [
                    'vcard_id' => $vcard_result['vcard_id'],
                    'identifier' => $vcard_result['identifier'],
                    'position' => $position
                ];
                error_log("NFC: vCard created - ID: {$vcard_result['vcard_id']}, Identifier: {$vcard_result['identifier']}");
            } else {
                $results['errors'][] = "Position $position: " . $vcard_result['error'];
                error_log("NFC: vCard creation failed for position $position: " . $vcard_result['error']);
            }
            
            $position++;
        }
    }
    
    return $results;
}

/**
 * Créer une vCard individuelle
 */
function nfc_create_single_vcard($order, $position, $item)
{
    $order_id = $order->get_id();
    $customer_id = $order->get_customer_id() ?: 1;
    
    // Générer identifiant unique
    $identifier = "NFC{$order_id}-{$position}";
    
    // Vérifier unicité
    if (nfc_identifier_exists($identifier)) {
        return [
            'success' => false,
            'error' => "Identifier $identifier already exists"
        ];
    }
    
    // Créer vCard WordPress
    $customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
    
    $vcard_data = [
        'post_title' => "vCard {$identifier} - {$customer_name}",
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'virtual_card',
        'post_author' => $customer_id,
    ];
    
    $vcard_id = wp_insert_post($vcard_data);
    
    if (is_wp_error($vcard_id)) {
        return [
            'success' => false,
            'error' => "WordPress error: " . $vcard_id->get_error_message()
        ];
    }
    
    // Remplir les champs de base
    nfc_fill_vcard_fields($vcard_id, $order, $identifier, $position);
    
    // Ajouter à la table enterprise si elle existe
    if (class_exists('NFC_Enterprise_Core')) {
        nfc_add_to_enterprise_table($vcard_id, $order_id, $position, $identifier, $customer_id);
    }
    
    return [
        'success' => true,
        'vcard_id' => $vcard_id,
        'identifier' => $identifier
    ];
}

/**
 * Créer un profil Avis Google partagé
 */
function nfc_create_google_profile_from_order($order, $google_items)
{
    // TODO: Implémenter la logique Avis Google plus tard
    error_log("NFC: Google Reviews profile creation - TODO");
    
    return [
        'profile_id' => null,
        'error' => 'Google Reviews creation not implemented yet'
    ];
}

/**
 * Remplir les champs de la vCard
 */
function nfc_fill_vcard_fields($vcard_id, $order, $identifier, $position)
{
    $order_id = $order->get_id();
    
    // Champs de base depuis commande
    $fields = [
        'firstname' => $order->get_billing_first_name(),
        'lastname' => $order->get_billing_last_name(),
        'email' => $order->get_billing_email(),
        'mobile' => $order->get_billing_phone(),
        'society' => $order->get_billing_company(),
        'address' => $order->get_billing_address_1(),
        'additional' => $order->get_billing_address_2(),
        'postcode' => $order->get_billing_postcode(),
        'city' => $order->get_billing_city(),
        'country' => $order->get_billing_country(),
        'order' => $order_id,
        'customer_id' => $order->get_customer_id(),
        'card_status' => 'pending', // À configurer
        'card_identifier' => $identifier,
        'card_position' => $position
    ];
    
    // Utiliser ACF si disponible, sinon post_meta
    if (function_exists('update_field')) {
        foreach ($fields as $key => $value) {
            if ($value) {
                update_field($key, $value, $vcard_id);
            }
        }
        
        // Générer URL unique
        $unique_url = nfc_generate_unique_url($vcard_id);
        update_field('unique_url', $unique_url, $vcard_id);
        update_field('url', get_permalink($vcard_id), $vcard_id);
        
    } else {
        foreach ($fields as $key => $value) {
            if ($value) {
                update_post_meta($vcard_id, $key, $value);
            }
        }
    }
    
    error_log("NFC: vCard fields filled for $identifier (vCard ID: $vcard_id)");
}

/**
 * Ajouter à la table enterprise
 */
function nfc_add_to_enterprise_table($vcard_id, $order_id, $position, $identifier, $customer_id)
{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
    
    $result = $wpdb->insert(
        $table_name,
        [
            'order_id' => $order_id,
            'vcard_id' => $vcard_id,
            'card_position' => $position,
            'card_identifier' => $identifier,
            'card_status' => 'pending',
            'main_user_id' => $customer_id,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ],
        ['%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s']
    );
    
    if ($result === false) {
        error_log("NFC: Failed to insert into enterprise table: " . $wpdb->last_error);
    } else {
        error_log("NFC: Added to enterprise table - $identifier");
    }
}

/**
 * Fonctions utilitaires
 */
function nfc_identifier_exists($identifier)
{
    global $wpdb;
    
    // Vérifier dans la table enterprise si elle existe
    $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    
    if ($table_exists) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE card_identifier = %s",
            $identifier
        ));
        return $exists > 0;
    }
    
    // Fallback: vérifier dans post_meta
    $exists = get_posts([
        'post_type' => 'virtual_card',
        'meta_query' => [
            [
                'key' => 'card_identifier',
                'value' => $identifier,
                'compare' => '='
            ]
        ],
        'numberposts' => 1,
        'fields' => 'ids'
    ]);
    
    return !empty($exists);
}

function nfc_generate_unique_url($vcard_id)
{
    // Générer URL aléatoire ou utiliser fonction existante
    if (function_exists('gtmi_vcard_generate_unique_url')) {
        return gtmi_vcard_generate_unique_url($vcard_id);
    }
    
    // Fallback simple
    return '/vcard/' . wp_generate_password(12, false);
}

// Vérifier WooCommerce
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Hooks activés
} else {
    function nfc_missing_woocommerce()
    {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('NFC System requires WooCommerce installed and activated.', 'gtmi_vcard'); ?></p>
        </div>
        <?php
    }
    add_action('admin_notices', 'nfc_missing_woocommerce');
}

/**
 * DEBUG - URL de test pour vérifier le système
 * Usage: /?test_nfc_order=1234
 */
add_action('init', function() {
    if (isset($_GET['test_nfc_order']) && current_user_can('administrator')) {
        $order_id = intval($_GET['test_nfc_order']);
        
        echo "<h2>🧪 Test NFC Order Processing</h2>";
        echo "<p><strong>Order ID:</strong> $order_id</p>";
        
        if ($order_id > 0) {
            nfc_process_order_products($order_id);
            echo "<p>✅ Order processed (check logs for details)</p>";
        } else {
            echo "<p>❌ Invalid order ID</p>";
        }
        
        echo "<p><a href='/wp-admin/edit.php?post_type=virtual_card'>→ Voir les vCards créées</a></p>";
        wp_die();
    }
});
?>