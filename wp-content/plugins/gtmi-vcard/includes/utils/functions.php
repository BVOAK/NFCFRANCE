<?php
function gtmi_vcard_findall_custom_type_post_query(
  $post_type,
  $key_id,
  $value_id,
  $fields = 'all'
): WP_Query {
   $args = [
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => [
          [
            'key' => $key_id,
            'value' => $value_id,
            'compare' => '=',
             'type'    => 'NUMERIC',
          ],
        ],
        'fields' => $fields
      ];
      return new WP_Query( $args);
}

function gtmi_vcard_send_mail($email, $subject, $body, $headers = ['Content-Type: text/html; charset=UTF-8','From: NFC France <'.GTMI_VCARD_EMAIL_SENDER.'>']): void
{
  error_log( "GTMI_VCARD: send mail to  $email with subject $subject");
  $message = __( $body,  'gtmi_vcard');
  wp_mail( $email,  $subject,  $message,  $headers);
}

/**
 * Détection des produits NFC
 * À ajouter dans: wp-content/plugins/gtmi-vcard/includes/utils/functions.php
 */

if (!function_exists('gtmi_vcard_is_nfc_product')) {
    /**
     * Vérifie si un produit est un produit NFC
     * 
     * @param int $product_id ID du produit WooCommerce
     * @return bool True si c'est un produit NFC
     */
    function gtmi_vcard_is_nfc_product($product_id) {
        error_log("GTMI_VCard: Checking if product $product_id is NFC product");
        
        // Méthode 1: Par catégorie de produit (ADAPTE SELON TON SETUP)
        $product_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'slugs']);
        if (in_array('nfc', $product_categories) || in_array('carte-nfc', $product_categories)) {
            error_log("GTMI_VCard: Product $product_id is NFC (detected by category)");
            return true;
        }
        
        // Méthode 2: Par IDs de produits spécifiques (ADAPTE SELON TES PRODUITS)
        // Tu peux remplacer ces IDs par tes vrais produits NFC
        $nfc_product_ids = [571, 572, 573, 574, 575]; 
        if (in_array($product_id, $nfc_product_ids)) {
            error_log("GTMI_VCard: Product $product_id is NFC (detected by ID whitelist)");
            return true;
        }
        
        // Méthode 3: Par tag de produit
        $product_tags = wp_get_post_terms($product_id, 'product_tag', ['fields' => 'slugs']);
        if (in_array('nfc', $product_tags) || in_array('carte-virtuelle', $product_tags)) {
            error_log("GTMI_VCard: Product $product_id is NFC (detected by tags)");
            return true;
        }
        
        // Méthode 4: Par champ personnalisé ACF
        $is_nfc = get_post_meta($product_id, '_is_nfc_product', true);
        if ($is_nfc === 'yes' || $is_nfc === '1') {
            error_log("GTMI_VCard: Product $product_id is NFC (detected by meta field)");
            return true;
        }
        
        // Méthode 5: Par nom du produit (FALLBACK)
        $product = wc_get_product($product_id);
        if ($product) {
            $product_name = strtolower($product->get_name());
            $nfc_keywords = ['nfc', 'carte', 'vcard', 'virtuelle', 'digitale'];
            
            foreach ($nfc_keywords as $keyword) {
                if (strpos($product_name, $keyword) !== false) {
                    error_log("GTMI_VCard: Product $product_id is NFC (detected by name: '$product_name')");
                    return true;
                }
            }
        }
        
        error_log("GTMI_VCard: Product $product_id is NOT an NFC product");
        return false;
    }
}

/**
 * Fonction utilitaire pour marquer un produit comme NFC dans l'admin
 */
if (!function_exists('gtmi_vcard_mark_product_as_nfc')) {
    function gtmi_vcard_mark_product_as_nfc($product_id, $is_nfc = true) {
        update_post_meta($product_id, '_is_nfc_product', $is_nfc ? 'yes' : 'no');
        error_log("GTMI_VCard: Product $product_id marked as " . ($is_nfc ? 'NFC' : 'non-NFC'));
    }
}

/**
 * Interface admin pour marquer les produits NFC
 */
add_action('add_meta_boxes', 'gtmi_vcard_add_nfc_product_metabox');
function gtmi_vcard_add_nfc_product_metabox() {
    add_meta_box(
        'gtmi_vcard_nfc_settings',
        'Paramètres NFC',
        'gtmi_vcard_nfc_metabox_callback',
        'product',
        'side',
        'high'
    );
}

function gtmi_vcard_nfc_metabox_callback($post) {
    $is_nfc = get_post_meta($post->ID, '_is_nfc_product', true);
    wp_nonce_field('gtmi_vcard_nfc_settings', 'gtmi_vcard_nfc_nonce');
    ?>
    <p>
        <label>
            <input type="checkbox" name="gtmi_vcard_is_nfc" value="yes" <?php checked($is_nfc, 'yes'); ?>>
            <strong>Ce produit est un produit NFC</strong>
        </label>
    </p>
    <p class="description">
        Cochez cette case si ce produit doit générer des cartes vCard après commande.
    </p>
    <?php
}

add_action('save_post', 'gtmi_vcard_save_nfc_metabox');
function gtmi_vcard_save_nfc_metabox($post_id) {
    if (!isset($_POST['gtmi_vcard_nfc_nonce']) || 
        !wp_verify_nonce($_POST['gtmi_vcard_nfc_nonce'], 'gtmi_vcard_nfc_settings')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_product', $post_id)) return;
    
    $is_nfc = isset($_POST['gtmi_vcard_is_nfc']) ? 'yes' : 'no';
    update_post_meta($post_id, '_is_nfc_product', $is_nfc);
}

/**
 * Détecte le type de produit NFC selon les catégories
 */
function nfc_detect_product_type($product_id) {
    // Récupérer les slugs des catégories du produit
    $categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'slugs']);
    
    if (empty($categories)) {
        return false;
    }
    
    error_log("NFC: Product $product_id categories: " . implode(', ', $categories));
    
    // Vérifier si le produit est dans la catégorie vCard
    if (in_array('carte-nfc-vcard', $categories)) {
        error_log("NFC: Product $product_id detected as vCard via category");
        return 'vcard';
    }
    
    // Vérifier si le produit est dans la catégorie Avis Google  
    if (in_array('avis-google', $categories)) {
        error_log("NFC: Product $product_id detected as Google Reviews via category");
        return 'google_reviews_card'; // Par défaut carte (tu peux ajuster plus tard)
    }
    
    error_log("NFC: Product $product_id is not in a NFC category");
    return false;
}

/**
 * Vérifie si un produit est de type NFC (compatibilité)
 */
function gtmi_vcard_is_nfc_product($product_id) {
    return nfc_detect_product_type($product_id) !== false;
}

/**
 * Vérifie si un produit est de type vCard
 */
function nfc_is_vcard_product($product_id) {
    return nfc_detect_product_type($product_id) === 'vcard';
}

/**
 * Vérifie si un produit est de type Avis Google
 */
function nfc_is_google_reviews_product($product_id) {
    return nfc_detect_product_type($product_id) === 'google_reviews_card';
}

/**
 * Fonction pour debug - afficher le type des produits
 */
function nfc_debug_product_types() {
    if (!current_user_can('administrator')) {
        return;
    }
    
    $products = wc_get_products(['limit' => 20, 'status' => 'publish']);
    
    echo "<div style='background: white; padding: 20px; margin: 20px; border: 1px solid #ccc;'>";
    echo "<h3>🔍 Debug Types de Produits</h3>";
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='border: 1px solid #ccc; padding: 8px;'>ID</th>";
    echo "<th style='border: 1px solid #ccc; padding: 8px;'>Nom</th>";
    echo "<th style='border: 1px solid #ccc; padding: 8px;'>Catégories</th>";
    echo "<th style='border: 1px solid #ccc; padding: 8px;'>Type Détecté</th>";
    echo "</tr>";
    
    foreach ($products as $product) {
        $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
        $categories_str = implode(', ', $categories);
        $detected_type = nfc_detect_product_type($product->get_id());
        
        $type_labels = [
            'vcard' => '👤 vCard',
            'google_reviews_card' => '⭐ Avis Google'
        ];
        
        $type_display = $detected_type ? $type_labels[$detected_type] : '❌ Non-NFC';
        
        echo "<tr>";
        echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$product->get_id()}</td>";
        echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$product->get_name()}</td>";
        echo "<td style='border: 1px solid #ccc; padding: 8px;'>$categories_str</td>";
        echo "<td style='border: 1px solid #ccc; padding: 8px;'>$type_display</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</div>";
}

// URL de debug : ?nfc_debug_products=1
add_action('init', function() {
    if (isset($_GET['nfc_debug_products']) && current_user_can('administrator')) {
        add_action('wp_footer', 'nfc_debug_product_types');
    }
});

?>


