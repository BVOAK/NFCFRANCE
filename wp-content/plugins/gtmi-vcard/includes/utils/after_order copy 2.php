<?php
/**
 * CORRECTION COMPLÈTE - after_order.php
 * 
 * Remplace complètement le contenu de includes/utils/after_order.php
 */

add_action('woocommerce_order_status_changed', 'gtmi_vcard_order_change_status', 10, 3);

/**
 * Throw when a new order is created and is status changed
 * When the order is paid, the status is processing
 *
 * @param int    $order_id   Order ID.
 * @param string $old_status Order old status.
 * @param string $new_status Order new status.
 */
function gtmi_vcard_order_change_status($order_id, $old_status, $new_status)
{
    $order = wc_get_order($order_id);
    if (! $order) {
        return;
    }
    
    // Debug log
    error_log("GTMI_VCard: Order $order_id status changed from $old_status to $new_status");
    
    if ('processing' === $new_status || 'completed' === $new_status) {
        error_log("GTMI_VCard: Order $order_id ready for vCard creation");
        
        // Créer la vCard automatiquement
        gtmi_vcard_create_from_order($order_id);
    }
}

/**
 * Throw when the payment is successful
 *
 * @param int $order_id Order ID
 */
function gtmi_vcard_order_payment_success($order_id)
{
    $order = wc_get_order($order_id);
    if (! $order) {
        return;
    }

    error_log("GTMI_VCard: Order $order_id payment successful");
    
    // Créer la vCard
    gtmi_vcard_create_from_order($order_id);
}

/**
 * NOUVELLE FONCTION - Créer vCard depuis commande
 * 
 * @param int $order_id ID de la commande
 * @return int|false ID de la vCard créée ou false si erreur
 */
function gtmi_vcard_create_from_order($order_id) {
    error_log("GTMI_VCard: Creating vCard for order $order_id");
    
    $order = wc_get_order($order_id);
    if (!$order) {
        error_log("GTMI_VCard: Order $order_id not found");
        return false;
    }
    
    // Vérifier si une vCard existe déjà pour cette commande
    $existing_vcard = get_posts([
        'post_type' => 'virtual_card',
        'meta_query' => [
            [
                'key' => 'order_id',
                'value' => $order_id,
                'compare' => '='
            ]
        ],
        'numberposts' => 1
    ]);
    
    if (!empty($existing_vcard)) {
        error_log("GTMI_VCard: vCard already exists for order $order_id");
        return $existing_vcard[0]->ID;
    }
    
    // Vérifier si la commande contient des produits NFC
    $items = $order->get_items();
    $has_nfc_product = false;
    
    foreach ($items as $item) {
        $product_id = $item->get_product_id();
        
        // Vérifier si c'est un produit NFC (adapte selon tes critères)
        if (gtmi_vcard_is_nfc_product($product_id)) {
            $has_nfc_product = true;
            break;
        }
    }
    
    if (!$has_nfc_product) {
        error_log("GTMI_VCard: Order $order_id has no NFC products");
        return false;
    }
    
    // Créer la vCard
    $customer_id = $order->get_customer_id();
    $customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
    
    $vcard_data = [
        'post_title'    => 'vCard - ' . $customer_name . ' - Commande #' . $order_id,
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'virtual_card',
        'post_author'   => $customer_id ?: 1,
    ];
    
    $vcard_id = wp_insert_post($vcard_data);
    
    if (is_wp_error($vcard_id)) {
        error_log("GTMI_VCard: Error creating vCard: " . $vcard_id->get_error_message());
        return false;
    }
    
    // Ajouter les métadonnées ACF
    if (function_exists('update_field')) {
        
        // Informations de base depuis la commande
        update_field('firstname', $order->get_billing_first_name(), $vcard_id);
        update_field('lastname', $order->get_billing_last_name(), $vcard_id);
        update_field('email', $order->get_billing_email(), $vcard_id);
        update_field('mobile', $order->get_billing_phone(), $vcard_id);
        
        // Informations société si disponibles
        $company = $order->get_billing_company();
        if ($company) {
            update_field('society', $company, $vcard_id);
        }
        
        // Lier à la commande
        update_field('order_id', $order_id, $vcard_id);
        update_field('customer_id', $customer_id, $vcard_id);
        
        // Générer URL unique
        $unique_url = gtmi_vcard_generate_unique_url($vcard_id);
        update_field('unique_url', $unique_url, $vcard_id);
        
        // Statut initial
        update_field('status', 'active', $vcard_id);
        
        error_log("GTMI_VCard: ACF fields updated for vCard $vcard_id");
        
    } else {
        error_log("GTMI_VCard: ACF not available, using post meta");
        
        // Fallback avec post meta si ACF n'est pas disponible
        update_post_meta($vcard_id, 'firstname', $order->get_billing_first_name());
        update_post_meta($vcard_id, 'lastname', $order->get_billing_last_name());
        update_post_meta($vcard_id, 'email', $order->get_billing_email());
        update_post_meta($vcard_id, 'mobile', $order->get_billing_phone());
        update_post_meta($vcard_id, 'order_id', $order_id);
        update_post_meta($vcard_id, 'customer_id', $customer_id);
    }
    
    // Envoyer email de notification au client
    gtmi_vcard_send_ready_notification($order->get_billing_email(), $vcard_id);
    
    error_log("GTMI_VCard: vCard $vcard_id created successfully for order $order_id");
    
    return $vcard_id;
}

/**
 * Vérifier si un produit est un produit NFC
 * 
 * @param int $product_id ID du produit
 * @return bool
 */
function gtmi_vcard_is_nfc_product($product_id) {
    
    // Méthode 1: Par catégorie de produit
    $product_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'slugs']);
    if (in_array('nfc', $product_categories) || in_array('carte-nfc', $product_categories)) {
        return true;
    }
    
    // Méthode 2: Par IDs de produits spécifiques (ADAPTE SELON TES PRODUITS)
    $nfc_product_ids = [571, 572, 573]; // Remplace par les vrais IDs de tes produits NFC
    if (in_array($product_id, $nfc_product_ids)) {
        return true;
    }
    
    // Méthode 3: Par tag de produit
    $product_tags = wp_get_post_terms($product_id, 'product_tag', ['fields' => 'slugs']);
    if (in_array('nfc', $product_tags) || in_array('carte-virtuelle', $product_tags)) {
        return true;
    }
    
    // Méthode 4: Par champ personnalisé
    $is_nfc = get_post_meta($product_id, '_is_nfc_product', true);
    if ($is_nfc === 'yes' || $is_nfc === '1') {
        return true;
    }
    
    error_log("GTMI_VCard: Product $product_id is not an NFC product");
    return false;
}

/**
 * Générer URL unique pour la vCard
 * 
 * @param int $vcard_id ID de la vCard
 * @return string URL unique
 */
function gtmi_vcard_generate_unique_url($vcard_id) {
    // Générer un hash unique
    $hash = md5($vcard_id . time() . wp_generate_password(12, false));
    
    // Mettre à jour le slug du post
    wp_update_post([
        'ID' => $vcard_id,
        'post_name' => $hash
    ]);
    
    $url = home_url('/vcard/' . $hash . '/');
    error_log("GTMI_VCard: Generated unique URL for vCard $vcard_id: $url");
    
    return $url;
}

/**
 * Envoyer notification par email
 * 
 * @param string $email Email du client
 * @param int $vcard_id ID de la vCard
 */
function gtmi_vcard_send_ready_notification($email, $vcard_id) {
    $vcard_url = get_field('unique_url', $vcard_id) ?: get_permalink($vcard_id);
    
    $subject = 'Votre carte NFC est prête - NFC France';
    
    $message = "
        Bonjour,
        
        Votre carte NFC personnalisée est maintenant disponible !
        
        Vous pouvez dès maintenant :
        • Personnaliser vos informations : {$vcard_url}
        • Télécharger vos QR codes
        • Commencer à partager votre carte virtuelle
        
        Lien de votre carte : {$vcard_url}
        
        Cordialement,
        L'équipe NFC France
    ";
    
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    
    $sent = wp_mail($email, $subject, nl2br($message), $headers);
    
    if ($sent) {
        error_log("GTMI_VCard: Notification email sent to $email for vCard $vcard_id");
    } else {
        error_log("GTMI_VCard: Failed to send notification email to $email for vCard $vcard_id");
    }
}

/**
 * FONCTION DE TEST MANUEL
 * URL: http://nfcfrance.loc/?test_vcard_creation=123
 */
function gtmi_vcard_test_creation() {
    if (isset($_GET['test_vcard_creation']) && current_user_can('administrator')) {
        $order_id = intval($_GET['test_vcard_creation']);
        
        echo "<h2>Test création vCard pour commande $order_id</h2>";
        
        $vcard_id = gtmi_vcard_create_from_order($order_id);
        
        if ($vcard_id) {
            echo "<p style='color: green;'>✅ vCard créée avec succès !</p>";
            echo "<p><strong>ID vCard:</strong> $vcard_id</p>";
            echo "<p><strong>Lien admin:</strong> <a href='" . admin_url("post.php?post=$vcard_id&action=edit") . "'>Voir dans l'admin</a></p>";
            
            $vcard_url = get_field('unique_url', $vcard_id) ?: get_permalink($vcard_id);
            echo "<p><strong>URL publique:</strong> <a href='$vcard_url' target='_blank'>$vcard_url</a></p>";
        } else {
            echo "<p style='color: red;'>❌ Erreur lors de la création de la vCard</p>";
        }
        
        echo "<p><a href='" . admin_url('edit.php?post_type=virtual_card') . "'>Voir toutes les vCards</a></p>";
        
        wp_die();
    }
}
add_action('init', 'gtmi_vcard_test_creation');

// Hook pour le paiement réussi
add_action('woocommerce_thankyou', 'gtmi_vcard_order_payment_success', 10, 1);

// Vérification WooCommerce (code existant conservé)
if (in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Code existant OK
} else {
    // Warning message when woocommerce is not activated
    function gtmi_vcard_missing_woocommerce(): void
    {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('This plugin requires WooCommerce installed and actived.', 'gtmi_vcard'); ?></p>
        </div>
        <?php
    }
    add_action('admin_notices', 'gtmi_vcard_missing_woocommerce');
}