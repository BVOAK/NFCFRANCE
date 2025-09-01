<?php
/**
 * Intégration WooCommerce pour le configurateur NFC - ENHANCED ADMIN
 * Focus : shortcode bouton + panier + commandes + AFFICHAGE ADMIN AMÉLIORÉ
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-nfc-product-button-manager.php';

class NFC_WooCommerce_Integration
{

    // Configuration produits configurables
    private $button_manager;

    public function __construct()
    {
        // Instancier le button manager (GARDER)
        $this->button_manager = new NFC_Product_Button_Manager();
        add_action('init', [$this, 'init']);
    }

    public function init()
    {
        // ✅ GARDER : Hooks panier/commandes essentiels
        add_filter('woocommerce_add_to_cart_validation', [$this, 'validate_add_to_cart'], 10, 3);
        add_filter('woocommerce_cart_item_name', [$this, 'modify_cart_item_name'], 10, 2);
        add_filter('woocommerce_cart_item_thumbnail', [$this, 'modify_cart_item_thumbnail'], 10, 2);
        add_action('woocommerce_order_status_completed', [$this, 'handle_completed_order']);

        // ✅ GARDER : Admin et emails
        add_action('woocommerce_admin_order_data_after_order_details', [$this, 'display_enhanced_admin_order_meta']);
        add_action('woocommerce_email_order_details', [$this, 'customize_order_emails'], 5, 4);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);

        // ✅ GARDER : API REST
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // ✅ GARDER : Nettoyage session
        add_action('wp_logout', [$this, 'cleanup_session_data']);
        add_action('wp_login', [$this, 'cleanup_session_data']);

        // 🆕 AJOUTER : Handlers AJAX pour les nouveaux boutons
        add_action('wp_ajax_nfc_add_to_cart_with_files', [$this, 'ajax_add_to_cart_with_files']);
        add_action('wp_ajax_nopriv_nfc_add_to_cart_with_files', [$this, 'ajax_add_to_cart_with_files']);

        add_action('wp', [$this, 'debug_button_integration']);

        // 🆕 CRITIQUE : Hook pour sauvegarder les screenshots en métadonnées de commande
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'save_screenshot_metadata'], 10, 4);

        $this->init_download_handlers();
    }

    /**
     * Vérifie si un produit est configurable
     */
    public function is_configurable_product($product_id)
    {
        // Utiliser le button manager au lieu des IDs hardcodés
        return $this->button_manager->is_configurable_product($product_id);
    }

    /**
     * Modifie la miniature dans le panier
     */
    public function modify_cart_item_thumbnail($thumbnail, $cart_item)
    {
        if (isset($cart_item['nfc_config']['screenshot_base64_data']['thumbnail'])) {
            $screenshot_url = $cart_item['nfc_config']['screenshot_base64_data']['thumbnail'];
            $thumbnail = '<img src="' . esc_attr($screenshot_url) . '" 
                alt="Configuration personnalisée" 
                class="nfc-screenshot" 
                style="max-width:64px;height:auto;">';
        }
        return $thumbnail;
    }

    /**
     * Gère les commandes terminées
     */
    public function handle_completed_order($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order)
            return;

        $has_nfc_items = false;

        foreach ($order->get_items() as $item) {
            if ($item->get_meta('_nfc_config_complete')) {
                $has_nfc_items = true;
                do_action('nfc_order_item_ready_for_production', $item, $order);
            }
        }

        if ($has_nfc_items) {
            $order->add_order_note('✅ Commande NFC personnalisée - Prête pour production');
        }
    }

    /**
     * ENHANCED ADMIN ORDER DISPLAY - Version complète avec fichiers
     */
    public function display_enhanced_admin_order_meta($order)
    {
        foreach ($order->get_items() as $item_id => $item) {
            $config_data = $item->get_meta('_nfc_config_complete');
            if (!$config_data)
                continue;

            $config = json_decode($config_data, true);
            if (!$config)
                continue;

            echo '<div class="nfc-admin-order-display" style="background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #0040C1;">';
            echo '<h3 style="margin-top: 0; color: #0040C1;">🎨 Configuration NFC - ' . esc_html($item->get_name()) . '</h3>';

            // Grid à 2 colonnes
            echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">';

            // ===== COLONNE 1: Détails config =====
            echo '<div>';
            echo '<h4 style="margin: 0 0 10px 0; color: #667eea;">📋 Détails Configuration</h4>';
            echo '<table style="width: 100%; font-size: 13px;">';
            echo '<tr><td style="padding: 5px 10px 5px 0; font-weight: 600;">Couleur:</td><td style="color: #0040C1; font-weight: 600;">' . ucfirst($config['color'] ?? 'Non défini') . '</td></tr>';

            if (isset($config['user'])) {
                $full_name = trim(($config['user']['firstName'] ?? '') . ' ' . ($config['user']['lastName'] ?? ''));
                if (!empty($full_name)) {
                    echo '<tr><td style="padding: 5px 10px 5px 0; font-weight: 600;">Nom:</td><td>' . esc_html($full_name) . '</td></tr>';
                }
            }

            // Logo recto
            if (isset($config['image']['name'])) {
                echo '<tr><td style="padding: 5px 10px 5px 0; font-weight: 600;">Logo recto:</td><td>' . esc_html($config['image']['name']) . '</td></tr>';

                if (isset($config['image']['scale'])) {
                    echo '<tr><td style="padding: 5px 10px 5px 0; font-weight: 600;">Taille:</td><td>' . $config['image']['scale'] . '%</td></tr>';
                }
            }

            // Logo verso (si présent)
            if (isset($config['logoVerso']['name'])) {
                echo '<tr><td style="padding: 5px 10px 5px 0; font-weight: 600; color: #e74c3c;">Logo verso:</td><td>' . esc_html($config['logoVerso']['name']) . '</td></tr>';
            }

            echo '</table>';
            echo '</div>';

            // ===== COLONNE 2: Preview + Actions =====
            echo '<div>';
            echo '<h4 style="margin: 0 0 10px 0; color: #667eea;">🖼️ Aperçu & Actions</h4>';

            // ✨ NOUVEAU : Aperçu screenshot HTML2Canvas
            $screenshot_data = $item->get_meta('_nfc_screenshot_data');
            if ($screenshot_data) {
                $screenshots = json_decode($screenshot_data, true);

                if (isset($screenshots['thumbnail']) && !empty($screenshots['thumbnail'])) {
                    echo '<div class="nfc-screenshot-preview" style="margin-bottom: 15px; text-align: center;">';
                    echo '<img src="' . esc_attr($screenshots['thumbnail']) . '" ';
                    echo 'alt="Aperçu configuration" ';
                    echo 'style="max-width: 200px; max-height: 150px; border: 2px solid #0040C1; border-radius: 6px; cursor: pointer;" ';
                    echo 'class="nfc-screenshot-thumbnail" ';
                    echo 'data-full-screenshot="' . esc_attr($screenshots['full'] ?? $screenshots['thumbnail']) . '" ';
                    echo 'title="Cliquez pour voir en grand" />';
                    echo '<br><small style="color: #666;">Cliquez pour agrandir</small>';
                    echo '</div>';
                }
            }

            // ✨ NOUVEAU : Boutons de téléchargement améliorés
            echo '<div class="nfc-admin-actions">';

            // Bouton screenshot principal
            if ($screenshot_data) {
                echo '<a href="#" ';
                echo 'class="button button-primary nfc-download-screenshot" ';
                echo 'data-order-id="' . esc_attr($order->get_id()) . '" ';
                echo 'data-item-id="' . esc_attr($item_id) . '" ';
                echo 'style="display: block; margin-bottom: 8px; text-align: center;">';
                echo '🖼️ Télécharger Screenshot</a>';
            }

            // Bouton logos (si présents)
            if (isset($config['image']['name'])) {
                echo '<a href="#" ';
                echo 'class="button button-secondary nfc-download-logo-recto" ';
                echo 'data-order-id="' . esc_attr($order->get_id()) . '" ';
                echo 'data-item-id="' . esc_attr($item_id) . '" ';
                echo 'style="display: block; margin-bottom: 5px; text-align: center;">';
                echo '📷 Télécharger Logo Recto</a>';
            }

            if (isset($config['logoVerso']['name'])) {
                echo '<a href="#" ';
                echo 'class="button button-secondary nfc-download-logo-verso" ';
                echo 'data-order-id="' . esc_attr($order->get_id()) . '" ';
                echo 'data-item-id="' . esc_attr($item_id) . '" ';
                echo 'style="display: block; margin-bottom: 5px; text-align: center;">';
                echo '📷 Télécharger Logo Verso</a>';
            }

            // Infos techniques
            if ($screenshot_data) {
                $screenshots = json_decode($screenshot_data, true);
                echo '<div style="margin-top: 15px; padding: 10px; background: #fff; border-radius: 4px; font-size: 11px; color: #666;">';
                echo '<strong>Infos techniques :</strong><br>';
                echo '• Méthode: ' . ($screenshots['capture_method'] ?? 'Legacy') . '<br>';
                echo '• Généré: ' . ($screenshots['generated_at'] ?? 'Inconnu') . '<br>';
                echo '• Version: ' . ($screenshots['version'] ?? '1.0');
                echo '</div>';
            }

            echo '</div>'; // .nfc-admin-actions
            echo '</div>'; // colonne 2
            echo '</div>'; // grid
            echo '</div>'; // .nfc-admin-order-display
        }
    }

    public function init_download_handlers()
    {
        // Téléchargement screenshot
        add_action('wp_ajax_nfc_download_screenshot', [$this, 'ajax_download_screenshot']);

        // ✨ FIX: Ajouter les handlers logos
        add_action('wp_ajax_nfc_download_logo_recto', [$this, 'ajax_download_logo_recto']);
        add_action('wp_ajax_nfc_download_logo_verso', [$this, 'ajax_download_logo_verso']);

        error_log('NFC: Handlers AJAX téléchargement enregistrés');

        // Debug temporaire
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_action('wp_loaded', [$this, 'debug_ajax_handlers']);
        }
    }


    public function ajax_download_screenshot()
    {
        error_log('NFC: ajax_download_screenshot appelé');

        // Vérifications sécurité
        if (!current_user_can('edit_shop_orders')) {
            error_log('NFC: User cannot edit_shop_orders');
            wp_die('Accès non autorisé', 'Erreur', ['response' => 403]);
        }

        // ✨ FIX: Vérifier le nonce correct
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'nfc_admin_downloads')) {
            error_log('NFC: Nonce invalide: ' . ($_GET['_wpnonce'] ?? 'manquant'));
            wp_die('Nonce invalide', 'Erreur', ['response' => 403]);
        }

        $order_id = intval($_GET['order_id'] ?? 0);
        $item_id = intval($_GET['item_id'] ?? 0);

        error_log("NFC: Tentative téléchargement screenshot - Order: {$order_id}, Item: {$item_id}");

        if (!$order_id || !$item_id) {
            error_log('NFC: Paramètres manquants');
            wp_die('Paramètres manquants', 'Erreur', ['response' => 400]);
        }

        // Récupérer la commande
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log("NFC: Commande {$order_id} non trouvée");
            wp_die('Commande non trouvée', 'Erreur', ['response' => 404]);
        }

        // Récupérer l'item
        $item = null;
        foreach ($order->get_items() as $order_item_id => $order_item) {
            if ($order_item_id == $item_id) {
                $item = $order_item;
                break;
            }
        }

        if (!$item) {
            error_log("NFC: Item {$item_id} non trouvé dans commande {$order_id}");
            wp_die('Article non trouvé', 'Erreur', ['response' => 404]);
        }

        // Récupérer les données screenshot
        $screenshot_data = $item->get_meta('_nfc_screenshot_data');
        if (!$screenshot_data) {
            error_log("NFC: Pas de screenshot_data pour item {$item_id}");
            wp_die('Aucun screenshot disponible', 'Erreur', ['response' => 404]);
        }

        $screenshots = json_decode($screenshot_data, true);
        $screenshot_base64 = $screenshots['full'] ?? '';

        if (empty($screenshot_base64)) {
            error_log('NFC: Screenshot base64 vide');
            wp_die('Données screenshot corrompues', 'Erreur', ['response' => 500]);
        }

        // Extraire les données base64
        if (strpos($screenshot_base64, 'data:image/png;base64,') === 0) {
            $screenshot_base64 = substr($screenshot_base64, strlen('data:image/png;base64,'));
        }

        $image_data = base64_decode($screenshot_base64);
        if (!$image_data) {
            error_log('NFC: Impossible de décoder base64');
            wp_die('Impossible de décoder l\'image', 'Erreur', ['response' => 500]);
        }

        // Générer nom de fichier
        $config_data = $item->get_meta('_nfc_config_complete');
        $config = $config_data ? json_decode($config_data, true) : [];

        $filename = 'nfc-screenshot-commande-' . $order_id . '-item-' . $item_id;
        if (isset($config['user']['firstName'], $config['user']['lastName'])) {
            $name = sanitize_file_name($config['user']['firstName'] . '-' . $config['user']['lastName']);
            $filename = 'nfc-screenshot-' . $name . '-commande-' . $order_id;
        }
        $filename .= '.png';

        error_log("NFC: Téléchargement {$filename} - " . strlen($image_data) . ' bytes');

        // Headers de téléchargement
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($image_data));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Servir l'image
        echo $image_data;
        exit;
    }

    /**
     * ✨ NOUVEAU: Handler téléchargement logo recto
     */
    public function ajax_download_logo_recto()
    {
        error_log('NFC: ajax_download_logo_recto appelé');

        // Vérifications sécurité
        if (!current_user_can('edit_shop_orders')) {
            wp_die('Accès non autorisé', 'Erreur', ['response' => 403]);
        }

        // Vérifier nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'nfc_admin_downloads')) {
            wp_die('Nonce invalide', 'Erreur', ['response' => 403]);
        }

        $order_id = intval($_GET['order_id'] ?? 0);
        $item_id = intval($_GET['item_id'] ?? 0);

        if (!$order_id || !$item_id) {
            wp_die('Paramètres manquants', 'Erreur', ['response' => 400]);
        }

        // Déléguer au file handler existant avec paramètres GET
        $_GET['type'] = 'recto';
        $_GET['nonce'] = wp_create_nonce('nfc_admin_download'); // Nonce attendu par file handler

        // Appeler le handler existant
        $file_handler = new NFC_File_Handler();
        $file_handler->download_logo();
    }


    /**
     * ✨ NOUVEAU: Handler téléchargement logo verso
     */
    public function ajax_download_logo_verso()
    {
        error_log('NFC: ajax_download_logo_verso appelé');

        // Vérifications sécurité
        if (!current_user_can('edit_shop_orders')) {
            wp_die('Accès non autorisé', 'Erreur', ['response' => 403]);
        }

        // Vérifier nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'nfc_admin_downloads')) {
            wp_die('Nonce invalide', 'Erreur', ['response' => 403]);
        }

        $order_id = intval($_GET['order_id'] ?? 0);
        $item_id = intval($_GET['item_id'] ?? 0);

        if (!$order_id || !$item_id) {
            wp_die('Paramètres manquants', 'Erreur', ['response' => 400]);
        }

        // Déléguer au file handler existant avec paramètres GET
        $_GET['type'] = 'verso';
        $_GET['nonce'] = wp_create_nonce('nfc_admin_download'); // Nonce attendu par file handler

        // Appeler le handler existant
        $file_handler = new NFC_File_Handler();
        $file_handler->download_logo();
    }



    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook)
    {
        // ✅ FIX: Hook correct pour WooCommerce HPOS
        global $pagenow, $post_type;

        // Vérifier la nouvelle page des commandes (HPOS)
        $is_orders_page = (
            $pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'wc-orders'
        ) || (
            $pagenow === 'post.php' && $post_type === 'shop_order'
        ) || (
            $pagenow === 'edit.php' && $post_type === 'shop_order'
        );

        if (!$is_orders_page) {
            return;
        }

        error_log('🔧 NFC Admin styles chargés sur: ' . $hook . ' - Page: ' . $pagenow);

        // CSS admin
        wp_enqueue_style(
            'nfc-admin-orders',
            get_template_directory_uri() . '/configurator/assets/css/admin-orders.css',
            [],
            '1.3'
        );

        // ✅ FIX: Nom de fichier correct (underscore, pas tiret)
        wp_enqueue_script(
            'nfc-admin-downloads',
            get_template_directory_uri() . '/configurator/assets/js/admin-downloads.js', // ← Underscore !
            ['jquery'],
            '1.1',
            true
        );

        wp_localize_script('nfc-admin-downloads', 'nfcAdminAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nfc_admin_downloads')
        ]);
    }

    /**
     * API REST pour variations
     */
    public function register_rest_routes()
    {
        register_rest_route('nfc/v1', '/product/(?P<product_id>\d+)/variations', [
            'methods' => 'GET',
            'callback' => [$this, 'get_product_variations'],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * API: Récupère variations
     */
    public function get_product_variations($request)
    {
        $product_id = $request['product_id'];

        if (!$this->is_configurable_product($product_id)) {
            return new WP_Error('not_configurable', 'Produit non configurable', ['status' => 400]);
        }

        try {
            $nfc_product = new NFC_Product_Manager();
            $product_data = $nfc_product->get_product_data($product_id);

            if (is_wp_error($product_data)) {
                return $product_data;
            }

            return rest_ensure_response($product_data['variations']);

        } catch (Exception $e) {
            return new WP_Error('server_error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Nettoyage session
     */
    public function cleanup_session_data()
    {
        if (session_id()) {
            foreach ($_SESSION as $key => $value) {
                if (
                    strpos($key, 'nfc_config_') === 0 &&
                    isset($value['timestamp']) &&
                    (time() - $value['timestamp']) > (24 * 60 * 60)
                ) {
                    unset($_SESSION[$key]);
                }
            }
        }
    }


    /**
     * Handler AJAX pour ajouter au panier avec métadonnées fichiers
     * 
     * @return void
     */
    /**
     * Handler AJAX pour ajouter au panier avec métadonnées fichiers - VERSION DEBUG
     */
    public function ajax_add_to_cart_with_files()
    {

        try {
            // 1. RÉCUPÉRATION DES DONNÉES (simplifié)
            $product_id = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);

            if (!$product_id) {
                wp_send_json_error(['message' => 'ID produit manquant'], 400);
            }

            // 2. VÉRIFIER PRODUIT (simplifié)
            $product = wc_get_product($product_id);
            if (!$product) {
                wp_send_json_error(['message' => 'Produit introuvable'], 404);
            }


            // 3. AJOUT AU PANIER (simplifié)
            $cart_item_data = [
                'nfc_requires_files' => true,
                'nfc_added_via' => 'button_test'
            ];

            // S'assurer que WC est chargé
            if (!function_exists('WC') || !WC()->cart) {
                wp_send_json_error(['message' => 'Panier non disponible'], 500);
            }

            // Ajouter le paramètre pour la validation
            $_POST['nfc_requires_files'] = $_POST['requires_files'];

            // 🆕 GESTION DES VARIATIONS
            if ($product->is_type('variable')) {

                $available_variations = $product->get_available_variations();

                if (empty($available_variations)) {
                    wp_send_json_error(['message' => 'Aucune variation disponible pour ce produit'], 400);
                }

                // Prendre la première variation disponible
                $first_variation = $available_variations[0];
                $variation_id = $first_variation['variation_id'];
                $variation_attributes = $first_variation['attributes'];

                $cart_item_key = WC()->cart->add_to_cart(
                    $product_id,
                    $quantity,
                    $variation_id,         // ← ID de la variation
                    $variation_attributes, // ← Attributs (couleur, etc.)
                    $cart_item_data
                );

            } else {
                // Produit simple
                $cart_item_key = WC()->cart->add_to_cart(
                    $product_id,
                    $quantity,
                    0,
                    [],
                    $cart_item_data
                );
            }

            if (!$cart_item_key) {
                wp_send_json_error(['message' => 'Échec ajout au panier'], 500);
            }


            // 4. RÉPONSE DE SUCCÈS
            wp_send_json_success([
                'message' => 'Produit ajouté avec succès !',
                'cart_item_key' => $cart_item_key,
                'cart_url' => wc_get_cart_url(),
                'cart_count' => WC()->cart->get_cart_contents_count()
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => 'Erreur: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Modification de la validation d'ajout au panier (mise à jour)
     * 
     * @param bool $passed
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public function validate_add_to_cart($passed, $product_id, $quantity)
    {

        // Utiliser le nouveau button manager au lieu des IDs hardcodés
        if ($this->button_manager->is_configurable_product($product_id)) {

            // Si ajout via AJAX avec métadonnées, autoriser
            if (wp_doing_ajax() && isset($_POST['nfc_requires_files'])) {
                return $passed;
            }

            // Si configuration NFC présente, autoriser
            if (isset($_POST['nfc_config'])) {
                return $passed;
            }

            // Sinon, bloquer
            wc_add_notice(
                __('Ce produit nécessite une personnalisation. Utilisez les boutons "Personnaliser" ou "Envoyer fichiers".', 'nfc-configurator'),
                'error'
            );

            return false;
        }

        return $passed;
    }

    /**
     * Modification du nom dans le panier (mise à jour)
     * 
     * @param string $name
     * @param array $cart_item
     * @return string
     */
    public function modify_cart_item_name($name, $cart_item)
    {
        // Ancienne logique (garder pour compatibilité)
        if (isset($cart_item['nfc_config'])) {
            $config = $cart_item['nfc_config'];
            $name .= '<br><small>🎨 Personnalisé (' . ucfirst($config['color'] ?? 'standard') . ')</small>';
        }

        // Nouvelle logique pour fichiers
        if (isset($cart_item['nfc_requires_files']) && $cart_item['nfc_requires_files']) {
            $name .= '<br><small>📎 ' . __('Envoi de fichiers requis', 'nfc-configurator') . '</small>';

            // Ajouter bouton d'upload si pas encore fait
            if (!isset($cart_item['nfc_files_uploaded']) || !$cart_item['nfc_files_uploaded']) {
                $name .= '<br><small class="nfc-upload-prompt">';
                $name .= '<a href="#" class="nfc-upload-trigger" data-cart-key="' . esc_attr($cart_item['key'] ?? '') . '">';
                $name .= '📁 ' . __('Cliquez pour envoyer vos fichiers', 'nfc-configurator');
                $name .= '</a></small>';
            }
        }

        return $name;
    }

    /**
     * Méthode utilitaire pour obtenir les contraintes de fichiers
     * 
     * @param int $product_id
     * @return array
     */
    public function get_product_file_constraints($product_id)
    {
        return $this->button_manager->get_file_constraints($product_id);
    }

    /**
     * Méthode pour marquer les fichiers comme uploadés
     * 
     * @param string $cart_item_key
     * @param array $file_data
     * @return bool
     */
    public function mark_files_uploaded($cart_item_key, $file_data)
    {
        $cart = WC()->cart->get_cart();

        if (isset($cart[$cart_item_key])) {
            WC()->cart->cart_contents[$cart_item_key]['nfc_files_uploaded'] = true;
            WC()->cart->cart_contents[$cart_item_key]['nfc_uploaded_files'] = $file_data;
            WC()->cart->set_session();

            return true;
        }

        return false;
    }

    /**
     * Hook pour afficher les informations de fichiers dans les emails
     * 
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     * @param WC_Email $email
     */
    public function customize_order_emails($order, $sent_to_admin, $plain_text, $email)
    {
        // Logique existante + ajout info fichiers
        foreach ($order->get_items() as $item) {
            $requires_files = $item->get_meta('nfc_requires_files');
            if ($requires_files) {
                if (!$plain_text) {
                    echo '<p><strong>' . __('⚠️ Attention :', 'nfc-configurator') . '</strong> ';
                    echo __('Ce produit nécessite l\'envoi de fichiers de personnalisation.', 'nfc-configurator') . '</p>';
                } else {
                    echo __('ATTENTION : Ce produit nécessite l\'envoi de fichiers de personnalisation.', 'nfc-configurator') . "\n\n";
                }
            }
        }
    }


    /**
     * Handler pour debug des boutons (développement uniquement)
     */
    public function debug_button_integration()
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG || !current_user_can('administrator')) {
            return;
        }

        if (isset($_GET['debug_nfc_integration'])) {
            $product_id = intval($_GET['debug_nfc_integration']);

            echo '<div style="background: #f1f1f1; padding: 20px; margin: 20px; border-radius: 8px;">';
            echo "<h3>Debug NFC Integration - Produit {$product_id}</h3>";

            // Test button manager
            echo '<h4>Button Manager :</h4>';
            $config = $this->button_manager->get_product_button_config($product_id);
            echo '<pre>' . json_encode($config, JSON_PRETTY_PRINT) . '</pre>';

            // Test ACF
            echo '<h4>ACF Direct :</h4>';
            $acf_data = [
                'nfc_product_type' => get_field('nfc_product_type', $product_id),
                'nfc_has_configurator' => get_field('nfc_has_configurator', $product_id),
                'nfc_requires_files' => get_field('nfc_requires_files', $product_id),
            ];
            echo '<pre>' . json_encode($acf_data, JSON_PRETTY_PRINT) . '</pre>';

            // Test URLs AJAX
            echo '<h4>URLs AJAX :</h4>';
            echo '<p><strong>AJAX URL :</strong> ' . admin_url('admin-ajax.php') . '</p>';
            echo '<p><strong>Nonce :</strong> ' . wp_create_nonce('nfc_buttons') . '</p>';

            echo '</div>';
            wp_die();
        }
    }


    /**
     * ✨ NOUVEAU : Affichage détaillé verso dans l'admin commandes
     */
    public function display_verso_admin_details($config)
    {
        echo '<div class="nfc-verso-details" style="margin-top: 15px; padding: 10px; background: #f9f9f9; border-radius: 4px;">';
        echo '<h4 style="margin-top: 0; color: #0073aa;">📱 Configuration Verso</h4>';

        // Logo verso
        if (isset($config['logoVerso']) && !empty($config['logoVerso']['name'])) {
            echo '<p><strong>Logo verso :</strong> ' . esc_html($config['logoVerso']['name']);

            // Détails techniques du logo
            if (isset($config['logoVerso']['scale'])) {
                echo ' <span style="color: #666;">(' . $config['logoVerso']['scale'] . '% de taille</span>';
            }
            if (isset($config['logoVerso']['x'], $config['logoVerso']['y'])) {
                echo ' <span style="color: #666;">• Position: ' . $config['logoVerso']['x'] . ', ' . $config['logoVerso']['y'] . ')</span>';
            }
            echo '</p>';
        } else {
            echo '<p><strong>Logo verso :</strong> <em>Aucun logo</em></p>';
        }

        // Affichage informations utilisateur
        if (isset($config['showUserInfo'])) {
            $status = $config['showUserInfo'] ? 'Affichées' : 'Masquées';
            $color = $config['showUserInfo'] ? '#46b450' : '#dc3232';
            echo '<p><strong>Informations utilisateur :</strong> <span style="color: ' . $color . ';">' . $status . '</span></p>';
        }

        echo '</div>';
    }


    /**
     * 🆕 CRITIQUE : Sauvegarde les données screenshot du panier vers les métadonnées de commande
     */
    public function save_screenshot_metadata($item, $cart_item_key, $values, $order)
    {
        error_log("💾 Sauvegarde métadonnées screenshot pour item {$item->get_id()}");

        // Vérifier si des données de configuration NFC existent
        if (!isset($values['nfc_config'])) {
            return;
        }

        $config = $values['nfc_config'];

        // ✨ NOUVEAU : Sauvegarder les données screenshot HTML2Canvas
        if (isset($config['screenshot'])) {
            $screenshot_data = [
                'full' => $config['screenshot']['full'] ?? '',
                'thumbnail' => $config['screenshot']['thumbnail'] ?? '',
                'generated_at' => $config['screenshot']['generated_at'] ?? date('Y-m-d H:i:s'),
                'capture_method' => 'html2canvas', // ← Nouveau flag
                'version' => '2.0'
            ];

            $item->add_meta_data('_nfc_screenshot_data', json_encode($screenshot_data));
            error_log("✅ Screenshot data sauvegardé: " . strlen($screenshot_data['full']) . " bytes full, " . strlen($screenshot_data['thumbnail']) . " bytes thumb");
        }

        // Sauvegarder config complète (existant)
        $item->add_meta_data('_nfc_config_complete', json_encode($config));

        // Marquer comme ayant un screenshot disponible
        $item->add_meta_data('_has_nfc_screenshot', 'yes');

        error_log("💾 Métadonnées screenshot HTML2Canvas sauvegardées pour commande");
    }

    /**
     * ✨ DEBUG: Méthode pour vérifier que les handlers sont bien enregistrés
     * À appeler temporairement dans init() pour debug
     */
    public function debug_ajax_handlers()
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        global $wp_filter;

        $handlers_to_check = [
            'wp_ajax_nfc_download_screenshot',
            'wp_ajax_nfc_download_logo_recto',
            'wp_ajax_nfc_download_logo_verso'
        ];

        foreach ($handlers_to_check as $handler) {
            if (isset($wp_filter[$handler])) {
                error_log("NFC: Handler {$handler} enregistré ✅");
            } else {
                error_log("NFC: Handler {$handler} MANQUANT ❌");
            }
        }
    }


}