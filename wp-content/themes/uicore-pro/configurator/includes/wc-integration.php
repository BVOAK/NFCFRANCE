<?php
/**
 * Intégration WooCommerce pour le configurateur NFC - ENHANCED ADMIN
 * Focus : shortcode bouton + panier + commandes + AFFICHAGE ADMIN AMÉLIORÉ
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-nfc-product-button-manager.php';

class NFC_WooCommerce_Integration {
    
    // Configuration produits configurables
    private $button_manager;
    private $configurable_products = [571, 572, 573]; // IDs uniquement
    
    public function __construct() {
        $this->button_manager = new NFC_Product_Button_Manager();
        add_action('init', [$this, 'init']);
    }
    
    public function init() {
        // SHORTCODE BOUTON
        add_shortcode('nfc_configurator_button', [$this, 'configurator_button_shortcode']);
        
        // HOOKS PANIER/COMMANDES (essentiels)
        add_filter('woocommerce_add_to_cart_validation', [$this, 'validate_add_to_cart'], 10, 3);
        add_filter('woocommerce_cart_item_name', [$this, 'modify_cart_item_name'], 10, 2);
        add_filter('woocommerce_cart_item_thumbnail', [$this, 'modify_cart_item_thumbnail'], 10, 2);
        add_action('woocommerce_order_status_completed', [$this, 'handle_completed_order']);
        
        // ENHANCED ADMIN DISPLAY
        add_action('woocommerce_admin_order_data_after_order_details', [$this, 'display_enhanced_admin_order_meta']);
        add_action('woocommerce_email_order_details', [$this, 'customize_order_emails'], 5, 4);
        
        // STYLES ADMIN
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        
        // API REST (pour configurateur)
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        
        // Nettoyage session
        add_action('wp_logout', [$this, 'cleanup_session_data']);
        add_action('wp_login', [$this, 'cleanup_session_data']);
    }
    
    /**
     * Vérifie si un produit est configurable
     */
    public function is_configurable_product($product_id) {
        //return in_array(intval($product_id), $this->configurable_products);
        return $this->button_manager->is_configurable_product($product_id);
    }
    
    /**
     * SHORTCODE BOUTON CONFIGURATEUR - COMPATIBLE ELEMENTOR + TRADUCTION
     */
    public function configurator_button_shortcode($atts) {
        global $product;
        
        $atts = shortcode_atts([
            'product_id' => $product ? $product->get_id() : 0,
            'text' => __('Personnaliser en ligne', 'nfc-configurator'),
            'size' => 'sm', // xs, sm, md, lg, xl
            'animation' => 'flip', // fade, grow, shrink, pulse, float, flip, etc.
            'class' => '' // Classes additionnelles
        ], $atts);
        
        $product_id = intval($atts['product_id']);
        
        if (!$this->is_configurable_product($product_id)) {
            return '';
        }
        
        $configurator_url = home_url('/configurateur?product_id=' . $product_id);
        
        // Classes Elementor
        $elementor_classes = [
            'elementor-button',
            'elementor-button-link',
            'elementor-size-' . esc_attr($atts['size'])
        ];
        
        // Animation si spécifiée
        if (!empty($atts['animation'])) {
            $elementor_classes[] = 'elementor-animation-' . esc_attr($atts['animation']);
        }
        
        // Classes additionnelles
        if (!empty($atts['class'])) {
            $elementor_classes[] = esc_attr($atts['class']);
        }
        
        // Classe NFC pour identification
        $elementor_classes[] = 'nfc-configurator-button';
        
        $button_text = esc_html($atts['text']);
        
        ob_start();
        ?>
        <div class="nfc-configurator-button-wrapper">
            <a class="<?php echo implode(' ', $elementor_classes); ?>" 
               href="<?php echo esc_url($configurator_url); ?>"
               data-product-id="<?php echo $product_id; ?>">
               <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZlcnNpb249IjEuMSIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHdpZHRoPSI1MTIiIGhlaWdodD0iNTEyIiB4PSIwIiB5PSIwIiB2aWV3Qm94PSIwIDAgNjgyLjY2NyA2ODIuNjY3IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIgNTEyIiB4bWw6c3BhY2U9InByZXNlcnZlIj48Zz48ZGVmcz48Y2xpcFBhdGggaWQ9ImEiIGNsaXBQYXRoVW5pdHM9InVzZXJTcGFjZU9uVXNlIj48cGF0aCBkPSJNMCA1MTJoNTEyVjBIMFoiIGZpbGw9IiMwMDAwMDAiIG9wYWNpdHk9IjEiIGRhdGEtb3JpZ2luYWw9IiMwMDAwMDAiPjwvcGF0aD48L2NsaXBQYXRoPjwvZGVmcz48ZyBjbGlwLXBhdGg9InVybCgjYSkiIHRyYW5zZm9ybT0ibWF0cml4KDEuMzMzMzMgMCAwIC0xLjMzMzMzIDAgNjgyLjY2NykiPjxwYXRoIGQ9Ik0wIDBjMC0xNDEuMzg1LTExNC42MTUtMjU2LTI1Ni0yNTZTLTUxMi0xNDEuMzg1LTUxMiAwczExNC42MTUgMjU2IDI1NiAyNTZTMCAxNDEuMzg1IDAgMCIgc3R5bGU9ImZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTpub25lIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg1MTIgMjU2KSIgZmlsbD0iI2ZmYzY0MCIgZGF0YS1vcmlnaW5hbD0iI2ZmYzY0MCI+PC9wYXRoPjxwYXRoIGQ9Ik0wIDBjNDkuMzcyIDU1LjcwNCA5Ny41NTkgMjQ1Ljg0NiAxMTAuNjk2IDMwMC43MDkgMS42NDkgNi44ODUtNC41NSAxMy4wODUtMTEuNDM2IDExLjQzNi01NC44NjMtMTMuMTM3LTI0NS4wMDUtNjEuMzI0LTMwMC43MDktMTEwLjY5Ni02NS4zMzktNTcuOTExLTgwLjAzNy0xNzEuNDIxLTgwLjAzNy0xNzEuNDIxbDU1LjAzMi01NS4wMzMgNTUuMDMzLTU1LjAzMlMtNTcuOTExLTY1LjMzOSAwIDAiIHN0eWxlPSJmaWxsLW9wYWNpdHk6MTtmaWxsLXJ1bGU6bm9uemVybztzdHJva2U6bm9uZSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNDAxLjAzNSAxOTkuNTg2KSIgZmlsbD0iI2ZmZmZmZiIgZGF0YS1vcmlnaW5hbD0iI2ZmZmZmZiI+PC9wYXRoPjxwYXRoIGQ9Ik0wIDBjNDcuNTgzIDQyLjE3NCAxOTMuMjQ4IDgzLjQ3OSAyNjguOTA5IDEwMi44MTZhMjE2NS4yNjcgMjE2NS4yNjcgMCAwIDEgNy44ODEgMzEuOGMxLjY0OCA2Ljg4NS00LjU1MSAxMy4wODQtMTEuNDM2IDExLjQzNUMyMTAuNDkgMTMyLjkxNCAyMC4zNDkgODQuNzI3LTM1LjM1NSAzNS4zNTVjLTY1LjMzOS01Ny45MTEtODAuMDM3LTE3MS40MjEtODAuMDM3LTE3MS40MjFsMzUuMzU1LTM1LjM1NVMtNjUuMzM5LTU3LjkxMSAwIDAiIHN0eWxlPSJmaWxsLW9wYWNpdHk6MTtmaWxsLXJ1bGU6bm9uemVybztzdHJva2U6bm9uZSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjM0Ljk0MSAzNjUuNjgpIiBmaWxsPSIjZjJmMmYyIiBkYXRhLW9yaWdpbmFsPSIjZjJmMmYyIj48L3BhdGg+PHBhdGggZD0iTTAgMGMyNS41MjYtNTUuNDU2IDcwLjI5LTEwMC4yMiAxMjUuNzQ2LTEyNS43NDZsNzQuNjQgNzQuNjRMNzQuNjQgNzQuNjRaIiBzdHlsZT0iZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDIzLjM0MiAxNDkuMDg4KSIgZmlsbD0iIzQ2NTM2NSIgZGF0YS1vcmlnaW5hbD0iIzQ2NTM2NSI+PC9wYXRoPjxwYXRoIGQ9Im0wIDAgODMuNzcxIDgzLjc3MS0zNS4zNTUgMzUuMzU1LTc0LjY0MS03NC42NEEyNTUuODc2IDI1NS44NzYgMCAwIDEgMCAwIiBzdHlsZT0iZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDQ5LjU2NyAxMDQuNjAyKSIgZmlsbD0iIzNlNDk1OSIgZGF0YS1vcmlnaW5hbD0iIzNlNDk1OSI+PC9wYXRoPjxwYXRoIGQ9Im0wIDAtMTM4Ljk3MiAxMzguOTcyYy00LjE0MyA0LjE0My00LjE0MyAxMC44NiAwIDE1LjAwM2w0NC4wMjEgNDQuMDJjNC4xNDMgNC4xNDMgMTAuODYgNC4xNDMgMTUuMDAzIDBMNTkuMDI0IDU5LjAyNGM0LjE0My00LjE0MyA0LjE0My0xMC44NiAwLTE1LjAwM0wxNS4wMDMgMEMxMC44Ni00LjE0MyA0LjE0My00LjE0MyAwIDAiIHN0eWxlPSJmaWxsLW9wYWNpdHk6MTtmaWxsLXJ1bGU6bm9uemVybztzdHJva2U6bm9uZSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjAwLjgzIDYxLjg1OCkiIGZpbGw9IiMyMWQ4ZGUiIGRhdGEtb3JpZ2luYWw9IiMyMWQ4ZGUiPjwvcGF0aD48cGF0aCBkPSJtMCAwIDQ0LjAyMSA0NC4wMjFjNC4xNDMgNC4xNDMgMTAuODYgNC4xNDMgMTUuMDAzIDBMMjMuNjY4IDc5LjM3NmMtNC4xNDMgNC4xNDMtMTAuODYgNC4xNDMtMTUuMDAzIDBsLTQ0LjAyLTQ0LjAyMWMtNC4xNDMtNC4xNDMtNC4xNDMtMTAuODYgMC0xNS4wMDNMMC0xNS4wMDNDLTQuMTQzLTEwLjg2LTQuMTQzLTQuMTQzIDAgMCIgc3R5bGU9ImZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTpub25lIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg5Ny4yMTMgMTgwLjQ3NykiIGZpbGw9IiMxNmM2Y2MiIGRhdGEtb3JpZ2luYWw9IiMxNmM2Y2MiPjwvcGF0aD48cGF0aCBkPSJNMCAwYzQ5LjM3MiA1NS43MDQgOTcuNTU5IDI0NS44NDYgMTEwLjY5NiAzMDAuNzEgMS42NDkgNi44ODUtNC41NSAxMy4wODQtMTEuNDM1IDExLjQzNS0zNi40ODUtOC43MzYtMTMyLjc4Ny0zMi45NzUtMjA5LjYtNjIuNzUzIDU5LjUxMyAyMC45NzggNzUuNDM0LTYwLjc5MyAxOS4yMjItMTIxLjI0OEMtMTQ3LjMyOSA2Ny42OS0xMjAuNDM4IDIuNjE2LTc3LjY3MS0xLjk2OGM0MC4zMDktNC4zMjEgMzcuNzIxLTMxLjUwMSAxNS4zMTItNDQuMDI2Qy0zOS42MTUtMzQuNTUxLTE3LjMyMS0xOS41NDIgMCAwIiBzdHlsZT0iZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDQwMS4wMzUgMTk5LjU4NikiIGZpbGw9IiNmZjZkM2IiIGRhdGEtb3JpZ2luYWw9IiNmZjZkM2IiPjwvcGF0aD48cGF0aCBkPSJNMCAwYzU2LjAwNSAyMC40MTcgMTE4Ljc3OSAzNy42OTcgMTYwLjM5MyA0OC4zMzIgMy4zIDEyLjkxMyA1Ljk2NSAyMy44MDIgNy44OCAzMS44IDEuNjQ5IDYuODg1LTQuNTUgMTMuMDg0LTExLjQzNiAxMS40MzYtMzYuNDg0LTguNzM3LTEzMi43ODYtMzIuOTc2LTIwOS41OTktNjIuNzUzQy0yMy41MDkgMzkuMTI2LTQuNzk5IDI0LjYwNSAwIDAiIHN0eWxlPSJmaWxsLW9wYWNpdHk6MTtmaWxsLXJ1bGU6bm9uemVybztzdHJva2U6bm9uZSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMzQzLjQ1OCA0MjAuMTY0KSIgZmlsbD0iI2YxNTQyNCIgZGF0YS1vcmlnaW5hbD0iI2YxNTQyNCI+PC9wYXRoPjwvZz48L2c+PC9zdmc+" />
                <span class="elementor-button-content-wrapper">
                    <span class="ui-btn-anim-wrapp d-flex">
                        <span class="elementor-button-text"><?php echo $button_text; ?></span>
                        <span class="elementor-button-text"><?php echo $button_text; ?></span>
                    </span>
                </span>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Valide l'ajout au panier pour produits configurables
     */
    public function validate_add_to_cart($passed, $product_id, $quantity) {
        if ($this->is_configurable_product($product_id)) {
            if (!isset($_POST['nfc_config'])) {
                wc_add_notice('Ce produit doit être personnalisé via le configurateur.', 'error');
                return false;
            }
        }
        return $passed;
    }
    
    /**
     * Modifie le nom dans le panier
     */
    public function modify_cart_item_name($name, $cart_item) {
        if (isset($cart_item['nfc_config'])) {
            $config = $cart_item['nfc_config'];
            $name .= '<br><small>🎨 Personnalisé (' . ucfirst($config['color']) . ')</small>';
        }
        return $name;
    }
    
    /**
     * Modifie la miniature dans le panier
     */
    public function modify_cart_item_thumbnail($thumbnail, $cart_item) {
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
    public function handle_completed_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
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
    public function display_enhanced_admin_order_meta($order) {
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
    public function enqueue_admin_styles($hook) {
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
    public function customize_order_emails($order, $sent_to_admin, $plain_text, $email) {
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
    }
    
    /**
     * API REST pour variations
     */
    public function register_rest_routes() {
        register_rest_route('nfc/v1', '/product/(?P<product_id>\d+)/variations', [
            'methods' => 'GET',
            'callback' => [$this, 'get_product_variations'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * API: Récupère variations
     */
    public function get_product_variations($request) {
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
    public function cleanup_session_data() {
        if (session_id()) {
            foreach ($_SESSION as $key => $value) {
                if (strpos($key, 'nfc_config_') === 0 && 
                    isset($value['timestamp']) && 
                    (time() - $value['timestamp']) > (24 * 60 * 60)) {
                    unset($_SESSION[$key]);
                }
            }
        }
    }
}

// Initialisation
new NFC_WooCommerce_Integration();