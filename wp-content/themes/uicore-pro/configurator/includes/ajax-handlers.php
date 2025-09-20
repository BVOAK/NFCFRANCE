<?php
/**
 * Handlers Ajax pour le configurateur NFC - AVEC SCREENSHOT
 * Gère les appels JavaScript vers WordPress/WooCommerce + sauvegarde screenshots
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistrement des handlers Ajax
 */
function nfc_register_ajax_handlers()
{
    // Pour les utilisateurs connectés ET non connectés
    add_action('wp_ajax_nfc_add_to_cart', 'nfc_add_to_cart_handler');
    add_action('wp_ajax_nopriv_nfc_add_to_cart', 'nfc_add_to_cart_handler');

    add_action('wp_ajax_nfc_get_variations', 'nfc_get_variations_handler');
    add_action('wp_ajax_nopriv_nfc_get_variations', 'nfc_get_variations_handler');

    add_action('wp_ajax_nfc_save_config', 'nfc_save_config_handler');
    add_action('wp_ajax_nopriv_nfc_save_config', 'nfc_save_config_handler');

    add_action('wp_ajax_nfc_validate_image', 'nfc_validate_image_handler');
    add_action('wp_ajax_nopriv_nfc_validate_image', 'nfc_validate_image_handler');

    error_log('NFC: Ajax handlers enregistrés depuis ajax-handlers.php');
}
add_action('init', 'nfc_register_ajax_handlers');

/**
 * Handler pour ajout au panier avec configuration ET SCREENSHOT
 */
function nfc_add_to_cart_handler()
{
    error_log('NFC: === DÉBUT AJOUT PANIER AVEC SCREENSHOT ===');
    error_log('NFC: POST data: ' . print_r($_POST, true));

    // Vérification du nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nfc_configurator')) {
        error_log('NFC: Nonce invalide');
        wp_send_json_error('Nonce invalide');
        return;
    }

    $product_id = intval($_POST['product_id'] ?? 0);
    $variation_id = intval($_POST['variation_id'] ?? 0);
    $nfc_config = $_POST['nfc_config'] ?? '';

    error_log("NFC: Données reçues - Product: {$product_id}, Variation: {$variation_id}");

    // Vérifications de base
    if (!$product_id || !$variation_id) {
        error_log('NFC: Données produit manquantes');
        wp_send_json_error('Données produit manquantes');
        return;
    }

    // Vérifier que WooCommerce est actif
    if (!class_exists('WooCommerce')) {
        error_log('NFC: WooCommerce non actif');
        wp_send_json_error('WooCommerce non actif');
        return;
    }

    // Initialiser WooCommerce si nécessaire
    if (!WC()->session) {
        WC()->session = new WC_Session_Handler();
        WC()->session->init();
    }

    if (!WC()->cart) {
        WC()->cart = new WC_Cart();
    }

    // Vérifier que le produit existe
    $product = wc_get_product($product_id);
    if (!$product) {
        error_log('NFC: Produit introuvable');
        wp_send_json_error('Produit introuvable');
        return;
    }

    // Vérifier que la variation existe
    $variation = wc_get_product($variation_id);
    if (!$variation || !$variation->exists()) {
        error_log('NFC: Variation introuvable');
        wp_send_json_error('Variation introuvable');
        return;
    }

    // Vérifier le stock
    if (!$variation->is_in_stock()) {
        error_log('NFC: Produit en rupture de stock');
        wp_send_json_error('Produit en rupture de stock');
        return;
    }

    try {
        // Décoder la configuration NFC
        $config = json_decode(stripslashes($nfc_config), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('NFC: Configuration JSON invalide: ' . json_last_error_msg());
            wp_send_json_error('Configuration invalide');
            return;
        }

        error_log('NFC: Configuration décodée avec screenshot: ' . print_r(array_keys($config), true));

        // NOUVEAU : Valider la configuration avec screenshot
        $validation = nfc_validate_configuration($config);
        if (!$validation['valid']) {
            error_log('NFC: Configuration invalide: ' . $validation['message']);
            wp_send_json_error($validation['message']);
            return;
        }

        // NOUVEAU : Traiter le screenshot
        $screenshot_info = null;
        if (isset($config['screenshot'])) {
            error_log('NFC: Conservation des données screenshot base64...');
            
            // ✅ GARDER les données base64 complètes pour sauvegarde ultérieure
            $screenshot_base64 = [
                'full' => $config['screenshot']['full'] ?? null,
                'thumbnail' => $config['screenshot']['thumbnail'] ?? null,
                'generated_at' => $config['screenshot']['generated_at'] ?? date('c')
            ];
            
            // Conserver dans la config du panier
            $config['screenshot_base64_data'] = $screenshot_base64;
            
            error_log('NFC: ✅ Données base64 conservées - Full: ' . strlen($screenshot_base64['full']) . ' chars, Thumb: ' . strlen($screenshot_base64['thumbnail']) . ' chars');
        }

        // Ajouter au panier WooCommerce
        $cart_item_key = WC()->cart->add_to_cart(
            $product_id,
            1, // Quantité
            $variation_id,
            [], // Variation attributes (automatique)
            ['nfc_config' => $config] // Données personnalisées
        );

        if (!$cart_item_key) {
            error_log('NFC: Échec ajout panier WooCommerce');
            wp_send_json_error('Erreur lors de l\'ajout au panier');
            return;
        }

        error_log("NFC: ✅ SUCCÈS ajout panier avec screenshot - Key: {$cart_item_key}");

        // Succès
        wp_send_json_success([
            'message' => 'Produit ajouté au panier avec succès',
            'cart_item_key' => $cart_item_key,
            'cart_url' => wc_get_cart_url(),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'screenshot_processed' => $screenshot_info ? $screenshot_info['success'] : false
        ]);

    } catch (Exception $e) {
        error_log('NFC: Exception ajout panier: ' . $e->getMessage());
        error_log('NFC: Stack trace: ' . $e->getTraceAsString());
        wp_send_json_error('Erreur serveur: ' . $e->getMessage());
    }
}

/**
 * Handler pour récuperer les variations d'un produit
 */
function nfc_get_variations_handler()
{
    // Vérification du nonce
    if (!wp_verify_nonce($_POST['nonce'], 'nfc_configurator')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    $product_id = intval($_POST['product_id']);

    if (!$product_id) {
        wp_send_json_error('ID produit manquant');
        return;
    }

    try {
        $nfc_product = new NFC_Product_Manager();
        $product_data = $nfc_product->get_product_data();

        if (is_wp_error($product_data)) {
            wp_send_json_error($product_data->get_error_message());
            return;
        }

        wp_send_json_success($product_data['variations']);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la récupération des variations: ' . $e->getMessage());
    }
}

/**
 * Handler pour sauvegarder une configuration
 */
function nfc_save_config_handler()
{
    // Vérification du nonce
    if (!wp_verify_nonce($_POST['nonce'], 'nfc_configurator')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    $config_data = $_POST['config_data'];

    if (empty($config_data)) {
        wp_send_json_error('Données de configuration manquantes');
        return;
    }

    try {
        // Décoder les données JSON
        $config = json_decode(stripslashes($config_data), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Données JSON invalides');
            return;
        }

        // Valider la configuration
        $validation = nfc_validate_configuration($config, ['skip_screenshot' => true]);
        if (!$validation['valid']) {
            wp_send_json_error($validation['message']);
            return;
        }

        // Sauvegarder en session (temporaire)
        if (!session_id()) {
            session_start();
        }
        $_SESSION['nfc_config_' . $config['variation_id']] = $config;

        wp_send_json_success([
            'message' => 'Configuration sauvegardée',
            'config_id' => $config['variation_id']
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
    }
}

/**
 * Handler pour valider une image uploadée
 */
function nfc_validate_image_handler()
{
    // Vérification du nonce
    if (!wp_verify_nonce($_POST['nonce'], 'nfc_configurator')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    $image_data = $_POST['image_data'];

    if (empty($image_data)) {
        wp_send_json_error('Données image manquantes');
        return;
    }

    try {
        $validation = nfc_validate_image_data($image_data);

        if (!$validation['valid']) {
            wp_send_json_error($validation['message']);
            return;
        }

        wp_send_json_success([
            'message' => 'Image valide',
            'size' => $validation['size'],
            'type' => $validation['type']
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la validation: ' . $e->getMessage());
    }
}

function nfc_validate_configuration($config, $options = [])
{
    // Options par défaut
    $options = array_merge([
        'skip_screenshot' => false,
        'skip_images' => false
    ], $options);

    // ========================================
    // VALIDATIONS TECHNIQUES OBLIGATOIRES
    // ========================================

    // Couleur et variation_id sont essentiels pour WooCommerce
    $required_fields = ['color', 'variation_id'];

    foreach ($required_fields as $field) {
        if (!isset($config[$field])) {
            return [
                'valid' => false,
                'message' => "Champ technique requis: {$field}"
            ];
        }
    }

    // Valider la couleur
    $allowed_colors = ['blanc', 'noir'];
    if (!in_array($config['color'], $allowed_colors)) {
        return [
            'valid' => false,
            'message' => 'Couleur non autorisée'
        ];
    }

    // Valider l'ID de variation
    $variation = wc_get_product($config['variation_id']);
    if (!$variation || !$variation->exists()) {
        return [
            'valid' => false,
            'message' => 'Variation de produit invalide'
        ];
    }

    // ========================================
    // VALIDATIONS OPTIONNELLES (SI DONNÉES PRÉSENTES)
    // ========================================

    // Structure user doit exister (même si vide)
    if (!isset($config['user']) || !is_array($config['user'])) {
        return [
            'valid' => false,
            'message' => 'Structure utilisateur manquante'
        ];
    }

    // Si nom/prénom renseignés, les valider
    if (isset($config['user']['firstName']) && !empty($config['user']['firstName'])) {
        $name_pattern = '/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u';
        if (!preg_match($name_pattern, $config['user']['firstName'])) {
            return [
                'valid' => false,
                'message' => 'Caractères non autorisés dans le prénom'
            ];
        }
    }

    if (isset($config['user']['lastName']) && !empty($config['user']['lastName'])) {
        $name_pattern = '/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u';
        if (!preg_match($name_pattern, $config['user']['lastName'])) {
            return [
                'valid' => false,
                'message' => 'Caractères non autorisés dans le nom'
            ];
        }
    }

    // ========================================
    // VALIDATION LOGO VERSO (SI PRÉSENT)
    // ========================================

    if (isset($config['logoVerso']) && is_array($config['logoVerso'])) {
        // Si logo verso existe, vérifier cohérence minimale
        if (empty($config['logoVerso']['name'])) {
            return [
                'valid' => false,
                'message' => 'Logo verso: nom de fichier manquant'
            ];
        }

        // Vérifier l'échelle si spécifiée
        if (isset($config['logoVerso']['scale'])) {
            $scale = intval($config['logoVerso']['scale']);
            if ($scale < 10 || $scale > 200) {
                return [
                    'valid' => false,
                    'message' => 'Logo verso: échelle invalide (10-200%)'
                ];
            }
        }
    }

    // ========================================
    // VALIDATION IMAGE RECTO (SI PRÉSENTE)
    // ========================================

    if (!$options['skip_images'] && isset($config['image']) && is_array($config['image'])) {
        // Valider image recto si présente
        if (isset($config['image']['data']) && !empty($config['image']['data'])) {
            $image_validation = nfc_validate_image_data($config['image']['data']);
            if (!$image_validation['valid']) {
                return [
                    'valid' => false,
                    'message' => 'Image recto: ' . $image_validation['message']
                ];
            }
        }
    }

    // ========================================
    // VALIDATION SCREENSHOT (SI PRÉSENT)
    // ========================================

    if (!$options['skip_screenshot'] && isset($config['screenshot'])) {
        error_log('NFC: Screenshot présent dans config');
    }

    // ✅ TOUT OK
    return [
        'valid' => true,
        'message' => 'Configuration valide'
    ];
}


/**
 * NOUVEAU : Valide une image base64
 */
function nfc_validate_base64_image($base64_data)
{
    if (empty($base64_data)) {
        return false;
    }

    // Vérifier format base64 image
    if (!preg_match('/^data:image\/(png|jpe?g);base64,/', $base64_data)) {
        return false;
    }

    // Tester décodage
    $data = substr($base64_data, strpos($base64_data, ',') + 1);
    $decoded = base64_decode($data);

    return $decoded !== false && strlen($decoded) > 100; // Au moins 100 bytes
}

/**
 * Valide les données d'une image base64
 */
function nfc_validate_image_data($image_data)
{
    // Vérifier le format base64
    if (!preg_match('/^data:image\/(jpeg|png|svg\+xml);base64,/', $image_data, $matches)) {
        return [
            'valid' => false,
            'message' => 'Format d\'image non supporté'
        ];
    }

    $image_type = $matches[1];
    $allowed_types = ['jpeg', 'png', 'svg+xml'];

    if (!in_array($image_type, $allowed_types)) {
        return [
            'valid' => false,
            'message' => 'Type d\'image non autorisé'
        ];
    }

    // Extraire les données base64
    $base64_data = substr($image_data, strpos($image_data, ',') + 1);
    $decoded_data = base64_decode($base64_data);

    if ($decoded_data === false) {
        return [
            'valid' => false,
            'message' => 'Données base64 invalides'
        ];
    }

    // Vérifier la taille (max 2MB)
    $size = strlen($decoded_data);
    $max_size = 2 * 1024 * 1024; // 2MB

    if ($size > $max_size) {
        return [
            'valid' => false,
            'message' => 'Image trop volumineuse (max 2MB)'
        ];
    }

    // Vérifier que c'est vraiment une image (sauf SVG)
    if ($image_type !== 'svg+xml') {
        $image_info = getimagesizefromstring($decoded_data);
        if ($image_info === false) {
            return [
                'valid' => false,
                'message' => 'Fichier image corrompu'
            ];
        }

        // Vérifier les dimensions (max 2000x2000)
        if ($image_info[0] > 2000 || $image_info[1] > 2000) {
            return [
                'valid' => false,
                'message' => 'Dimensions d\'image trop importantes (max 2000x2000)'
            ];
        }
    }

    return [
        'valid' => true,
        'size' => $size,
        'type' => $image_type
    ];
}

/**
 * Hook pour nettoyer les données de configuration lors de l'ajout au panier
 */
add_filter('woocommerce_add_cart_item_data', 'nfc_add_cart_item_data', 10, 3);
function nfc_add_cart_item_data($cart_item_data, $product_id, $variation_id)
{
    if (isset($cart_item_data['nfc_config'])) {
        // Nettoyer et sécuriser les données
        $config = $cart_item_data['nfc_config'];

        // Échapper les données utilisateur
        if (isset($config['user'])) {
            $config['user']['firstName'] = sanitize_text_field($config['user']['firstName']);
            $config['user']['lastName'] = sanitize_text_field($config['user']['lastName']);
        }

        // Ajouter un identifiant unique pour éviter la fusion des articles
        $cart_item_data['nfc_config'] = $config;
        $cart_item_data['nfc_unique_key'] = uniqid('nfc_', true);

        error_log('NFC: Métadonnées configuration ajoutées au panier');
    }

    return $cart_item_data;
}

/**
 * Hook pour afficher les données de configuration dans le panier
 */
add_filter('woocommerce_get_item_data', 'nfc_display_cart_item_data', 10, 2);
function nfc_display_cart_item_data($item_data, $cart_item)
{
    if (isset($cart_item['nfc_config'])) {
        $config = $cart_item['nfc_config'];

        // Afficher les informations de configuration RECTO
        $item_data[] = [
            'key' => 'Couleur',
            'value' => ucfirst($config['color'])
        ];

        if (isset($config['user'])) {
            $firstName = trim($config['user']['firstName'] ?? '');
            $lastName = trim($config['user']['lastName'] ?? '');
            
            if (!empty($firstName) || !empty($lastName)) {
                $fullName = trim($firstName . ' ' . $lastName);
                $item_data[] = [
                    'key' => 'Nom sur la carte',
                    'value' => $fullName
                ];
            }
        }

        if (isset($config['image']) && !empty($config['image']['name'])) {
            $item_data[] = [
                'key' => 'Image recto',
                'value' => $config['image']['name']
            ];
        }

        // ✨ NOUVEAU : Afficher les informations VERSO
        if (isset($config['logoVerso']) && !empty($config['logoVerso']['name'])) {
            $item_data[] = [
                'key' => 'Image verso',
                'value' => $config['logoVerso']['name']
            ];
        }

        if (isset($config['showUserInfo'])) {
            $firstName = trim($config['user']['firstName'] ?? '');
            $lastName = trim($config['user']['lastName'] ?? '');
            
            if ($config['showUserInfo']) {
                if (empty($firstName) && empty($lastName)) {
                    $item_data[] = [
                        'key' => 'Infos verso',
                        'value' => 'Aucune donnée'
                    ];
                } else {
                    $item_data[] = [
                        'key' => 'Infos verso',
                        'value' => 'Affichées'
                    ];
                }
            } else {
                $item_data[] = [
                    'key' => 'Infos verso',
                    'value' => 'Masquées'
                ];
            }
        }

        // Screenshot thumbnail (existant)
        if (isset($config['screenshot']) && !empty($config['screenshot']['thumbnail'])) {
            $item_data[] = [
                'key' => 'Aperçu',
                'value' => '<img src="' . esc_attr($config['screenshot']['thumbnail']) . '" style="max-width: 150px; height: auto; border: 1px solid #ddd; border-radius: 4px;" alt="Aperçu configuration">'
            ];
        }

        error_log('NFC: Données configuration (recto + verso) affichées dans le panier');
    }

    return $item_data;
}

/**
 * ✅ MASQUER les attributs de variation WooCommerce SEULEMENT si config NFC
 */
add_filter('woocommerce_display_item_meta', 'nfc_hide_variation_attributes', 10, 3);
function nfc_hide_variation_attributes($html, $item, $args) 
{
    // Detecter si l'item a une config NFC
    $has_nfc_config = false;
    
    if (is_array($item) && isset($item['nfc_config'])) {
        // Item du panier
        $has_nfc_config = true;
    } elseif (method_exists($item, 'get_meta') && $item->get_meta('_nfc_config')) {
        // Item de commande
        $has_nfc_config = true;
    }
    
    // ✅ SEULEMENT pour les items avec config NFC
    if ($has_nfc_config) {
        // Masquer les attributs de variation automatiques
        // Notre fonction nfc_display_cart_item_data s'occupera de l'affichage
        return ''; 
    }
    
    return $html; // Comportement normal pour les items sans config
}

/**
 * Hook pour sauvegarder les métadonnées dans la commande
 */
add_action('woocommerce_checkout_create_order_line_item', 'nfc_save_order_item_meta', 10, 4);
function nfc_save_order_item_meta($item, $cart_item_key, $values, $order)
{
    if (isset($values['nfc_config'])) {
        $config = $values['nfc_config'];

        // Sauvegarder les métadonnées RECTO (existantes)
        $item->add_meta_data('_nfc_couleur', ucfirst($config['color']));

        if (isset($config['user'])) {
            $item->add_meta_data('_nfc_nom', $config['user']['firstName'] . ' ' . $config['user']['lastName']);
        }

        if (isset($config['image']) && !empty($config['image']['name'])) {
            $item->add_meta_data('_nfc_image_recto', $config['image']['name']);
            // Sauvegarder les données complètes de l'image recto
            $item->add_meta_data('_nfc_image_recto_data', json_encode($config['image']));
        }

        // ✨ NOUVEAU : Sauvegarder les métadonnées VERSO
        if (isset($config['logoVerso']) && !empty($config['logoVerso']['name'])) {
            $item->add_meta_data('_nfc_logo_verso', $config['logoVerso']['name']);
            // Sauvegarder les données complètes du logo verso
            $item->add_meta_data('_nfc_logo_verso_data', json_encode($config['logoVerso']));
        }

        if (isset($config['showUserInfo'])) {
            $item->add_meta_data('_nfc_show_user_info', $config['showUserInfo'] ? 'Oui' : 'Non');
        }

        // ✨ NOUVEAU : Métadonnées lisibles pour l'admin
        $verso_summary = [];
        if (isset($config['logoVerso']) && !empty($config['logoVerso']['name'])) {
            $verso_summary[] = 'Logo: ' . $config['logoVerso']['name'];
        }
        if (isset($config['showUserInfo'])) {
            $verso_summary[] = 'Infos: ' . ($config['showUserInfo'] ? 'Affichées' : 'Masquées');
        }

        if (!empty($verso_summary)) {
            $item->add_meta_data('Configuration verso', implode(' • ', $verso_summary));
        }

        // Screenshot (existant)
        if (isset($config['screenshot_processed'])) {
            $item->add_meta_data('_nfc_screenshot_info', json_encode($config['screenshot_processed']));
            error_log('NFC: Screenshot info structurées sauvées : ' . json_encode(array_keys($config['screenshot_processed'])));
        }

        // AUSSI sauver les données brutes en backup
        if (isset($config['screenshot'])) {
            $item->add_meta_data('_nfc_screenshot_data', json_encode($config['screenshot']));
            error_log('NFC: Screenshot data brutes sauvées : ' . json_encode(array_keys($config['screenshot'])));
        }

        // Configuration complète pour référence (existant)
        $item->add_meta_data('_nfc_config_complete', json_encode($config));

        error_log('NFC: Métadonnées configuration (recto + verso) sauvegardées dans la commande');
    }
}

/**
 * ✅ NOUVEAU HANDLER : Bouton "Ajouter au panier avec fichiers"
 * Action: nfc_add_to_cart_with_files (appelée par product-buttons.js)
 */
add_action('wp_ajax_nfc_add_to_cart_with_files', 'nfc_add_to_cart_with_files_handler');
add_action('wp_ajax_nopriv_nfc_add_to_cart_with_files', 'nfc_add_to_cart_with_files_handler');

function nfc_add_to_cart_with_files_handler() {
    error_log('🛒 NFC: Handler nfc_add_to_cart_with_files appelé');
    error_log('🛒 NFC: POST data: ' . print_r($_POST, true));
    
    // ✅ CORRECTION : Vérification du BON nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nfc_buttons')) {
        error_log('❌ NFC: Nonce invalide - reçu: ' . ($_POST['nonce'] ?? 'aucun'));
        error_log('❌ NFC: Nonce attendu: nfc_buttons');
        wp_send_json_error('Nonce invalide');
        return;
    }
    
    // Récupération des données
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $requires_files = ($_POST['requires_files'] ?? '') === 'true';
    
    error_log("📦 NFC: Ajout produit {$product_id}, qty: {$quantity}, files: " . ($requires_files ? 'oui' : 'non'));
    
    // Validations de base
    if (!$product_id) {
        error_log('❌ NFC: ID produit manquant');
        wp_send_json_error('ID produit manquant');
        return;
    }
    
    if ($quantity < 1 || $quantity > 100) {
        error_log('❌ NFC: Quantité invalide: ' . $quantity);
        wp_send_json_error('Quantité invalide (1-100)');
        return;
    }
    
    // Vérifier le produit
    $product = wc_get_product($product_id);
    if (!$product || !$product->exists()) {
        error_log('❌ NFC: Produit introuvable: ' . $product_id);
        wp_send_json_error('Produit introuvable');
        return;
    }
    
    // Vérifier le stock
    if (!$product->is_in_stock()) {
        error_log('❌ NFC: Produit en rupture: ' . $product_id);
        wp_send_json_error('Produit en rupture de stock');
        return;
    }
    
    // Vérifier quantité disponible
    if ($product->managing_stock() && $quantity > $product->get_stock_quantity()) {
        error_log('❌ NFC: Stock insuffisant: demandé=' . $quantity . ', disponible=' . $product->get_stock_quantity());
        wp_send_json_error('Stock insuffisant');
        return;
    }
    
    // Initialiser WooCommerce si nécessaire
    if (!class_exists('WooCommerce') || !WC()->cart) {
        error_log('❌ NFC: WooCommerce non initialisé');
        wp_send_json_error('Panier non disponible');
        return;
    }
    
    try {
        // ✅ Métadonnées pour le panier
        $cart_item_data = [
            'nfc_requires_files' => $requires_files,
            'nfc_added_via' => 'files_button',
            'nfc_unique_key' => uniqid('files_', true) // Éviter fusion articles
        ];
        
        // ✅ Gestion des variations (cartes couleurs)
        $variation_id = 0;
        $variation_attributes = [];
        
        if ($product->is_type('variable')) {
            error_log('🎨 NFC: Produit variable détecté');
            $available_variations = $product->get_available_variations();
            
            if (!empty($available_variations)) {
                // Prendre la première variation disponible
                $first_variation = $available_variations[0];
                $variation_id = $first_variation['variation_id'];
                $variation_attributes = $first_variation['attributes'];
                
                error_log('🎨 NFC: Variation sélectionnée: ' . $variation_id);
                error_log('🎨 NFC: Attributs: ' . print_r($variation_attributes, true));
            } else {
                error_log('❌ NFC: Aucune variation disponible');
                wp_send_json_error('Aucune variation disponible');
                return;
            }
        }
        
        // ✅ Ajouter au panier WooCommerce
        $cart_item_key = WC()->cart->add_to_cart(
            $product_id,
            $quantity,
            $variation_id,
            $variation_attributes,
            $cart_item_data
        );
        
        if (!$cart_item_key) {
            error_log('❌ NFC: Échec ajout au panier WooCommerce');
            wp_send_json_error('Échec ajout au panier');
            return;
        }
        
        error_log('✅ NFC: Succès ajout panier - Key: ' . $cart_item_key);
        
        // ✅ Réponse de succès
        wp_send_json_success([
            'message' => "Produit ajouté avec succès ! (Quantité: {$quantity})",
            'cart_item_key' => $cart_item_key,
            'cart_url' => wc_get_cart_url(),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'product_id' => $product_id,
            'quantity' => $quantity,
            'variation_id' => $variation_id
        ]);
        
    } catch (Exception $e) {
        error_log('❌ NFC: Exception ajout panier: ' . $e->getMessage());
        error_log('❌ NFC: Stack trace: ' . $e->getTraceAsString());
        wp_send_json_error('Erreur serveur: ' . $e->getMessage());
    }
}