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
function nfc_register_ajax_handlers() {
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
function nfc_add_to_cart_handler() {
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
        $validation = nfc_validate_configuration_with_screenshot($config);
        if (!$validation['valid']) {
            error_log('NFC: Configuration invalide: ' . $validation['message']);
            wp_send_json_error($validation['message']);
            return;
        }
        
        // NOUVEAU : Traiter le screenshot
        $screenshot_info = null;
        if (isset($config['screenshot'])) {
            error_log('NFC: Traitement du screenshot...');
            $screenshot_info = nfc_process_screenshot($config['screenshot']);
            if ($screenshot_info['success']) {
                error_log('NFC: Screenshot traité avec succès');
                // Remplacer les données base64 par les infos du screenshot
                $config['screenshot_processed'] = $screenshot_info['data'];
                // Garder seulement thumbnail en base64 pour affichage rapide
                $config['screenshot'] = [
                    'thumbnail' => $config['screenshot']['thumbnail'],
                    'generated_at' => $config['screenshot']['generated_at']
                ];
            } else {
                error_log('NFC: Erreur traitement screenshot: ' . $screenshot_info['error']);
                // Continuer sans screenshot en cas d'erreur
            }
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
 * NOUVEAU : Traite et stocke le screenshot
 */
function nfc_process_screenshot($screenshot_data) {
    try {
        // Créer le dossier de stockage si nécessaire
        $upload_dir = wp_upload_dir();
        $nfc_dir = $upload_dir['basedir'] . '/nfc-screenshots/';
        
        if (!file_exists($nfc_dir)) {
            wp_mkdir_p($nfc_dir);
            // Ajouter .htaccess pour sécurité
            file_put_contents($nfc_dir . '.htaccess', "Order deny,allow\nDeny from all\n");
        }
        
        // Générer nom unique
        $timestamp = time();
        $random = wp_generate_password(8, false);
        $filename_base = "screenshot-{$timestamp}-{$random}";
        
        $result = [
            'success' => true,
            'data' => [
                'full_size' => null,
                'thumbnail' => null,
                'generated_at' => $screenshot_data['generated_at'] ?? date('c'),
                'storage_path' => $nfc_dir
            ]
        ];
        
        // Traiter screenshot full size
        if (isset($screenshot_data['full']) && !empty($screenshot_data['full'])) {
            $full_file = nfc_save_base64_image($screenshot_data['full'], $nfc_dir . $filename_base . '-full.png');
            if ($full_file) {
                $result['data']['full_size'] = [
                    'filename' => basename($full_file),
                    'path' => $full_file,
                    'size' => filesize($full_file)
                ];
                error_log('NFC: Screenshot full sauvegardé: ' . $full_file);
            }
        }
        
        // Traiter thumbnail  
        if (isset($screenshot_data['thumbnail']) && !empty($screenshot_data['thumbnail'])) {
            $thumb_file = nfc_save_base64_image($screenshot_data['thumbnail'], $nfc_dir . $filename_base . '-thumb.png');
            if ($thumb_file) {
                $result['data']['thumbnail'] = [
                    'filename' => basename($thumb_file),
                    'path' => $thumb_file,
                    'size' => filesize($thumb_file)
                ];
                error_log('NFC: Screenshot thumbnail sauvegardé: ' . $thumb_file);
            }
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log('NFC: Erreur traitement screenshot: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * NOUVEAU : Sauvegarde une image base64 en fichier
 */
function nfc_save_base64_image($base64_data, $file_path) {
    try {
        // Extraire les données base64
        if (preg_match('/^data:image\/\w+;base64,/', $base64_data)) {
            $data = substr($base64_data, strpos($base64_data, ',') + 1);
        } else {
            $data = $base64_data;
        }
        
        $data = base64_decode($data);
        if ($data === false) {
            throw new Exception('Données base64 invalides');
        }
        
        // Sauvegarder le fichier
        $result = file_put_contents($file_path, $data);
        if ($result === false) {
            throw new Exception('Impossible d\'écrire le fichier');
        }
        
        return $file_path;
        
    } catch (Exception $e) {
        error_log('NFC: Erreur sauvegarde image: ' . $e->getMessage());
        return false;
    }
}

/**
 * Handler pour récuperer les variations d'un produit
 */
function nfc_get_variations_handler() {
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
function nfc_save_config_handler() {
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
        $validation = nfc_validate_configuration_extended($config);
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
function nfc_validate_image_handler() {
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

/**
 * MODIFIÉ : Valide une configuration complète avec screenshot
 */
function nfc_validate_configuration_with_screenshot($config) {
    // Validation de base
    $basic_validation = nfc_validate_configuration($config);
    if (!$basic_validation['valid']) {
        return $basic_validation;
    }
    
    // Validation spécifique screenshot
    if (isset($config['screenshot'])) {
        // Vérifier que le screenshot a les bonnes données
        if (!isset($config['screenshot']['full']) || !isset($config['screenshot']['thumbnail'])) {
            return [
                'valid' => false,
                'message' => 'Données screenshot incomplètes'
            ];
        }
        
        // Vérifier format base64
        if (!nfc_validate_base64_image($config['screenshot']['full'])) {
            return [
                'valid' => false,
                'message' => 'Screenshot full invalide'
            ];
        }
        
        if (!nfc_validate_base64_image($config['screenshot']['thumbnail'])) {
            return [
                'valid' => false,
                'message' => 'Screenshot thumbnail invalide'
            ];
        }
        
        error_log('NFC: Screenshot validé avec succès');
    }
    
    return ['valid' => true];
}

/**
 * NOUVEAU : Valide une image base64
 */
function nfc_validate_base64_image($base64_data) {
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
 * Valide une configuration complète
 */
function nfc_validate_configuration($config) {
    // Vérifications requises
    $required_fields = ['color', 'user', 'variation_id'];
    
    foreach ($required_fields as $field) {
        if (!isset($config[$field])) {
            return [
                'valid' => false,
                'message' => "Champ requis manquant: {$field}"
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
    
    // Valider les informations utilisateur
    if (empty($config['user']['firstName']) || empty($config['user']['lastName'])) {
        return [
            'valid' => false,
            'message' => 'Prénom et nom sont requis'
        ];
    }
    
    // Valider le nom (pas de caractères spéciaux dangereux)
    $name_pattern = '/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u';
    if (!preg_match($name_pattern, $config['user']['firstName']) || 
        !preg_match($name_pattern, $config['user']['lastName'])) {
        return [
            'valid' => false,
            'message' => 'Caractères non autorisés dans le nom'
        ];
    }
    
    // Valider l'image si présente
    if (isset($config['image']) && !empty($config['image']['data'])) {
        $image_validation = nfc_validate_image_data($config['image']['data']);
        if (!$image_validation['valid']) {
            return $image_validation;
        }
    }
    
    // Valider l'ID de variation
    $variation = wc_get_product($config['variation_id']);
    if (!$variation || !$variation->exists()) {
        return [
            'valid' => false,
            'message' => 'Variation de produit invalide'
        ];
    }
    
    return ['valid' => true];
}

/**
 * Valide les données d'une image base64
 */
function nfc_validate_image_data($image_data) {
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
function nfc_add_cart_item_data($cart_item_data, $product_id, $variation_id) {
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
function nfc_display_cart_item_data($item_data, $cart_item) {
    if (isset($cart_item['nfc_config'])) {
        $config = $cart_item['nfc_config'];
        
        // Afficher les informations de configuration RECTO
        $item_data[] = [
            'key' => 'Couleur',
            'value' => ucfirst($config['color'])
        ];
        
        if (isset($config['user'])) {
            $item_data[] = [
                'key' => 'Nom sur la carte',
                'value' => $config['user']['firstName'] . ' ' . $config['user']['lastName']
            ];
        }
        
        if (isset($config['image']) && !empty($config['image']['name'])) {
            $item_data[] = [
                'key' => 'Image recto',
                'value' => 'Logo: ' . $config['image']['name']
            ];
        }
        
        // ✨ NOUVEAU : Afficher les informations VERSO
        if (isset($config['logoVerso']) && !empty($config['logoVerso']['name'])) {
            $item_data[] = [
                'key' => 'Logo verso',
                'value' => 'Logo: ' . $config['logoVerso']['name']
            ];
        }
        
        if (isset($config['showUserInfo'])) {
            $item_data[] = [
                'key' => 'Informations verso',
                'value' => $config['showUserInfo'] ? 'Affichées' : 'Masquées'
            ];
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
 * Hook pour sauvegarder les métadonnées dans la commande
 */
add_action('woocommerce_checkout_create_order_line_item', 'nfc_save_order_item_meta', 10, 4);
function nfc_save_order_item_meta($item, $cart_item_key, $values, $order) {
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
        if (isset($config['screenshot'])) {
            $item->add_meta_data('_nfc_screenshot_data', json_encode($config['screenshot']));
        }
        
        // Configuration complète pour référence (existant)
        $item->add_meta_data('_nfc_config_complete', json_encode($config));
        
        error_log('NFC: Métadonnées configuration (recto + verso) sauvegardées dans la commande');
    }
}

/**
 * ✨ NOUVELLE : Validation étendue de la configuration avec verso
 */
function nfc_validate_configuration_extended($config) {
    // Validation de base (existante)
    if (empty($config['color']) || empty($config['user'])) {
        return ['valid' => false, 'message' => 'Données de base manquantes'];
    }
    
    if (empty($config['user']['firstName']) || empty($config['user']['lastName'])) {
        return ['valid' => false, 'message' => 'Nom et prénom requis'];
    }
    
    // ✨ NOUVEAU : Validation verso
    if (isset($config['logoVerso'])) {
        // Vérifier la cohérence des données logo verso
        if (empty($config['logoVerso']['name']) || empty($config['logoVerso']['url'])) {
            return ['valid' => false, 'message' => 'Données logo verso incohérentes'];
        }
        
        // Vérifier que l'échelle est dans les limites
        $scale = $config['logoVerso']['scale'] ?? 100;
        if ($scale < 10 || $scale > 200) {
            return ['valid' => false, 'message' => 'Taille logo verso hors limites (10-200%)'];
        }
    }
    
    // Vérifier showUserInfo (doit être un booléen)
    if (isset($config['showUserInfo']) && !is_bool($config['showUserInfo'])) {
        return ['valid' => false, 'message' => 'Paramètre affichage utilisateur invalide'];
    }
    
    return ['valid' => true, 'message' => 'Configuration valide'];
}
