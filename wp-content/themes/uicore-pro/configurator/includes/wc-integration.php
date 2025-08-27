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
    private $configurable_products = [571, 572, 573]; // IDs uniquement

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
        if (isset($cart_item['nfc_config'])) {
            // Si un screenshot thumbnail existe, l'utiliser
            if (isset($cart_item['nfc_config']['screenshot']['thumbnail'])) {
                $screenshot_url = $cart_item['nfc_config']['screenshot']['thumbnail'];
                $thumbnail = '<img src="' . esc_attr($screenshot_url) . '" alt="Aperçu personnalisé" style="width: 64px; height: auto; border-radius: 4px;">';
            } else {
                // Sinon, ajouter juste l'icône personnalisé
                $thumbnail = '<div style="position: relative;">' . $thumbnail;
                $thumbnail .= '<span style="position: absolute; top: -5px; right: -5px; background: #667eea; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; font-size: 12px; line-height: 20px;">🎨</span>';
                $thumbnail .= '</div>';
            }
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
        $has_nfc_items = false;

        foreach ($order->get_items() as $item_id => $item) {
            $config_data = $item->get_meta('_nfc_config_complete');
            if ($config_data) {
                if (!$has_nfc_items) {
                    echo '<h3 style="margin-top: 30px;">🎨 Cartes NFC Personnalisées</h3>';
                    $has_nfc_items = true;
                }

                $config = json_decode($config_data, true);
                $urls = NFC_File_Handler::get_download_urls($order->get_id(), $item_id);

                echo '<div class="nfc-admin-item" style="background: #f9f9f9; padding: 20px; margin: 15px 0; border-left: 4px solid #667eea; border-radius: 4px;">';

                // Titre de l'article
                echo '<h4 style="margin: 0 0 15px 0; color: #333;">' . esc_html($item->get_name()) . '</h4>';

                // Grille d'informations
                echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">';

                // Colonne gauche - Infos de base
                echo '<div>';
                echo '<h5 style="margin: 0 0 10px 0; color: #667eea;">📋 Configuration</h5>';
                echo '<table style="width: 100%; border-collapse: collapse;">';
                echo '<tr><td style="padding: 5px 10px 5px 0; font-weight: 600;">Couleur:</td><td>' . ucfirst($config['color'] ?? 'Non défini') . '</td></tr>';
                echo '<tr><td style="padding: 5px 10px 5px 0; font-weight: 600;">Nom:</td><td>' . esc_html(($config['user']['firstName'] ?? '') . ' ' . ($config['user']['lastName'] ?? '')) . '</td></tr>';

                if (isset($config['image']['name'])) {
                    echo '<tr><td style="padding: 5px 10px 5px 0; font-weight: 600;">Image:</td><td>' . esc_html($config['image']['name']) . '</td></tr>';

                    // Paramètres transformation
                    $scale = $item->get_meta('_nfc_image_scale') ?: 100;
                    $x = $item->get_meta('_nfc_image_x') ?: 0;
                    $y = $item->get_meta('_nfc_image_y') ?: 0;

                    echo '<tr><td style="padding: 5px 10px 5px 0; font-weight: 600;">Taille:</td><td>' . $scale . '%</td></tr>';
                    echo '<tr><td style="padding: 5px 10px 5px 0; font-weight: 600;">Position X:</td><td>' . ($x > 0 ? '+' : '') . $x . 'px</td></tr>';
                    echo '<tr><td style="padding: 5px 10px 5px 0; font-weight: 600;">Position Y:</td><td>' . ($y > 0 ? '+' : '') . $y . 'px</td></tr>';
                }
                echo '</table>';
                echo '</div>';

                // Colonne droite - Actions et aperçu
                echo '<div>';
                echo '<h5 style="margin: 0 0 10px 0; color: #667eea;">🔧 Actions</h5>';

                // Boutons de téléchargement
                if (isset($config['image']['name'])) {
                    echo '<p><a href="' . esc_url($urls['logo_download']) . '" class="button button-secondary" style="margin-right: 10px;">📷 Télécharger logo</a></p>';
                }

                // Screenshot actions
                $screenshot_info = $item->get_meta('_nfc_screenshot_info');
                if ($screenshot_info) {
                    echo '<p>';
                    echo '<a href="' . esc_url($urls['screenshot_view']) . '" class="button button-secondary" target="_blank" style="margin-right: 5px;">👁️ Voir aperçu</a>';
                    echo '<a href="' . esc_url($urls['screenshot_download']) . '" class="button button-secondary">💾 Télécharger</a>';
                    echo '</p>';

                    // Miniature screenshot si disponible
                    echo '<div style="margin-top: 10px;">';
                    echo '<img src="' . esc_url($urls['screenshot_view']) . '" style="max-width: 200px; height: auto; border: 1px solid #ddd; border-radius: 4px;" alt="Aperçu configuration">';
                    echo '</div>';
                }
                echo '</div>';

                echo '</div>'; // Fin grille

                // Section JSON (collapsible)
                echo '<details style="margin-top: 15px;">';
                echo '<summary style="cursor: pointer; color: #667eea; font-weight: 600;">📄 Configuration complète (JSON)</summary>';
                echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 4px; font-size: 11px; max-height: 200px; overflow: auto; margin-top: 10px;">' . esc_html(json_encode($config, JSON_PRETTY_PRINT)) . '</pre>';
                echo '</details>';

                echo '</div>'; // Fin nfc-admin-item
            }
        }
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook)
    {
        // Seulement sur les pages de commandes
        if (!in_array($hook, ['post.php', 'post-new.php', 'edit.php'])) {
            return;
        }

        global $post_type;
        if ($post_type !== 'shop_order') {
            return;
        }

        // Styles inline pour l'admin
        wp_add_inline_style('wp-admin', '
            .nfc-admin-item {
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .nfc-admin-item h4 {
                border-bottom: 1px solid #ddd;
                padding-bottom: 10px;
            }
            .nfc-admin-item .button {
                text-decoration: none !important;
            }
            .nfc-admin-item details summary {
                outline: none;
            }
            .nfc-admin-item details[open] summary {
                margin-bottom: 10px;
            }
        ');
    }

    /**
     * Personnalise les emails de commande  
     */
    /* public function customize_order_emails($order, $sent_to_admin, $plain_text, $email)
    {
        $has_nfc_items = false;

        foreach ($order->get_items() as $item) {
            if ($item->get_meta('_nfc_config_complete')) {
                $has_nfc_items = true;
                break;
            }
        }

        if ($has_nfc_items) {
            if ($plain_text) {
                echo "\n=== CARTES NFC PERSONNALISÉES ===\n";
                echo "Délai de production : 7-10 jours ouvrés\n\n";
            } else {
                echo '<div style="background: #f8f9fa; border: 1px solid #667eea; padding: 20px; margin: 20px 0;">';
                echo '<h3 style="color: #667eea;">🎨 Cartes NFC Personnalisées</h3>';
                echo '<p>Délai de production : <strong>7-10 jours ouvrés</strong></p>';
                echo '</div>';
            }
        }
    } */

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


}