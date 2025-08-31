<?php
/**
 * NFC Customer Integration - Espace client avec aperçus
 * Gère l'affichage des screenshots dans l'espace client WooCommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_Customer_Integration
{

    public function __construct()
    {
        add_action('init', [$this, 'init']);
    }

    public function init()
    {
        // Hooks espace client WooCommerce
        add_filter('woocommerce_account_orders_columns', [$this, 'add_orders_columns']);
        add_action('woocommerce_my_account_my_orders_column_nfc-preview', [$this, 'orders_column_content']);

        // Détail commande
        add_action('woocommerce_order_item_meta_end', [$this, 'display_item_screenshot'], 10, 4);
        add_action('woocommerce_view_order', [$this, 'add_order_custom_styles']);

        // Emails clients
        add_action('woocommerce_email_order_item_meta', [$this, 'email_item_screenshot'], 15, 4);

        // Scripts et styles front-end
        add_action('wp_enqueue_scripts', [$this, 'enqueue_customer_assets']);

        error_log('NFC: Customer Integration initialisé');
    }

    /**
     * Ajoute une colonne aperçu dans l'historique des commandes
     */
    public function add_orders_columns($columns)
    {
        // Insérer la colonne aperçu après "Commande"
        $new_columns = [];
        foreach ($columns as $key => $label) {
            $new_columns[$key] = $label;
            if ($key === 'order-number') {
                $new_columns['nfc-preview'] = 'Aperçu';
            }
        }

        return $new_columns;
    }

    /**
     * Contenu de la colonne aperçu
     */
    public function orders_column_content($order)
    {
        $nfc_items = $this->get_order_nfc_items($order);

        if (empty($nfc_items)) {
            echo '<span class="nfc-no-preview">—</span>';
            return;
        }

        // Afficher miniature du premier item NFC
        $first_item = reset($nfc_items);
        $thumbnail_url = $this->get_customer_screenshot_url($order->get_id(), $first_item['item_id'], 'thumb');

        if ($thumbnail_url) {
            $count = count($nfc_items);
            $badge_text = $count > 1 ? "+{$count}" : "NFC";

            echo '<div class="nfc-order-preview">';
            echo '<img src="' . esc_url($thumbnail_url) . '" alt="Aperçu personnalisé" class="nfc-thumb-small">';
            echo '<span class="nfc-badge">' . esc_html($badge_text) . '</span>';
            echo '</div>';
        } else {
            echo '<span class="nfc-custom-badge">🎨 Personnalisé</span>';
        }
    }

    /**
     * Affiche le screenshot dans le détail d'un produit de commande
     */
    public function display_item_screenshot($item_id, $item, $order, $plain_text = false)
    {
        // Vérifier que c'est un produit NFC configuré
        $config_data = $item->get_meta('_nfc_config_complete');
        if (!$config_data) {
            return;
        }

        // Vérifier les permissions client
        if (!$this->can_customer_view_order($order->get_id())) {
            return;
        }

        if ($plain_text) {
            // Version texte pour emails
            $config = json_decode($config_data, true);
            echo "\n--- Configuration personnalisée ---\n";
            echo "Couleur: " . ucfirst($config['color'] ?? 'Non défini') . "\n";
            if (isset($config['user'])) {
                echo "Nom: " . $config['user']['firstName'] . ' ' . $config['user']['lastName'] . "\n";
            }
            if (isset($config['image']['name'])) {
                echo "Logo: " . $config['image']['name'] . "\n";
            }
            echo "---\n";
            return;
        }

        // Version HTML pour espace client
        $this->render_item_screenshot_html($item_id, $item, $order);
    }

    /**
     * Rendu HTML du screenshot d'un produit
     */
    private function render_item_screenshot_html($item_id, $item, $order)
    {
        $config_data = $item->get_meta('_nfc_config_complete');
        $config = json_decode($config_data, true);

        $screenshot_url = $this->get_customer_screenshot_url($order->get_id(), $item_id, 'full');
        $thumbnail_url = $this->get_customer_screenshot_url($order->get_id(), $item_id, 'thumb');

        echo '<div class="nfc-customer-config" style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #0040C1;">';

        // Header
        echo '<h4 style="margin: 0 0 15px 0; color: #0040C1; font-size: 16px;">🎨 Configuration personnalisée</h4>';

        // Layout responsive
        echo '<div class="nfc-config-layout" style="display: grid; grid-template-columns: 1fr; gap: 15px;">';

        // Screenshot (mobile-first)
        if ($screenshot_url || $thumbnail_url) {
            echo '<div class="nfc-screenshot-section">';
            echo '<h5 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Aperçu de votre carte :</h5>';

            $display_url = $screenshot_url ?: $thumbnail_url;
            echo '<div class="nfc-screenshot-container" style="text-align: center;">';
            echo '<img src="' . esc_url($display_url) . '" alt="Aperçu configuration" class="nfc-screenshot-full" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">';

            // Bouton agrandir si disponible
            if ($screenshot_url && $screenshot_url !== $thumbnail_url) {
                echo '<div style="margin-top: 10px;">';
                echo '<a href="' . esc_url($screenshot_url) . '" target="_blank" class="button" style="font-size: 12px; padding: 5px 15px;">🔍 Voir en grand</a>';
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }

        // Détails configuration
        echo '<div class="nfc-config-details">';
        echo '<h5 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Détails :</h5>';
        echo '<div class="nfc-config-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">';

        // Couleur
        echo '<div class="nfc-detail-item">';
        echo '<strong>Couleur :</strong><br>';
        echo '<span class="nfc-color-badge" style="display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 12px; ' . $this->get_color_badge_style($config['color'] ?? 'blanc') . '">';
        echo ucfirst($config['color'] ?? 'Non défini');
        echo '</span>';
        echo '</div>';

        // Nom
        if (isset($config['user'])) {
            echo '<div class="nfc-detail-item">';
            echo '<strong>Nom sur la carte :</strong><br>';
            echo esc_html($config['user']['firstName'] . ' ' . $config['user']['lastName']);
            echo '</div>';
        }

        // Logo
        if (isset($config['image']['name'])) {
            echo '<div class="nfc-detail-item">';
            echo '<strong>Logo :</strong><br>';
            echo esc_html($config['image']['name']);
            echo '</div>';
        }

        echo '</div>'; // fin config-grid
        echo '</div>'; // fin config-details
        echo '</div>'; // fin config-layout

        // Statut production (si disponible)
        $this->display_production_status($order, $item);

        echo '</div>'; // fin customer-config
    }

    /**
     * Affiche le statut de production
     */
    private function display_production_status($order, $item)
    {
        $order_status = $order->get_status();

        echo '<div class="nfc-production-status" style="margin-top: 15px; padding: 10px; border-radius: 6px; font-size: 12px;">';

        switch ($order_status) {
            case 'processing':
                echo '<div style="background: #fff3cd; color: #856404; border: 1px solid #ffeaa7;">';
                echo '⏳ <strong>En préparation</strong> - Votre carte est en cours de personnalisation';
                echo '</div>';
                break;

            case 'completed':
                echo '<div style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">';
                echo '✅ <strong>Expédiée</strong> - Votre carte personnalisée a été envoyée';
                echo '</div>';
                break;

            default:
                echo '<div style="background: #e2e3e5; color: #6c757d; border: 1px solid #ced4da;">';
                echo '📋 <strong>Commande reçue</strong> - Délai de production : 7-10 jours ouvrés';
                echo '</div>';
                break;
        }

        echo '</div>';
    }

    /**
     * Style CSS pour les badges couleur
     */
    private function get_color_badge_style($color)
    {
        $styles = [
            'blanc' => 'background: #f8f9fa; color: #495057; border: 1px solid #dee2e6;',
            'noir' => 'background: #343a40; color: #ffffff; border: 1px solid #343a40;'
        ];

        return $styles[$color] ?? $styles['blanc'];
    }

    /**
     * Screenshot dans les emails clients
     */
    public function email_item_screenshot($item_id, $item, $order, $sent_to_admin = false)
    {
        // Seulement pour les emails clients
        if ($sent_to_admin) {
            return;
        }

        $config_data = $item->get_meta('_nfc_config_complete');
        if (!$config_data) {
            return;
        }

        $config = json_decode($config_data, true);
        $thumbnail_url = $this->get_customer_screenshot_url($order->get_id(), $item_id, 'thumb');

        echo '<div style="margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #0040C1;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #0040C1; font-size: 16px;">🎨 Votre carte personnalisée</h4>';

        if ($thumbnail_url) {
            echo '<div style="text-align: center; margin: 10px 0;">';
            echo '<img src="' . esc_url($thumbnail_url) . '" alt="Aperçu de votre carte" style="max-width: 300px; height: auto; border: 1px solid #ddd; border-radius: 4px;">';
            echo '</div>';
        }

        echo '<p style="margin: 5px 0; font-size: 14px; color: #666;">';
        echo '<strong>Couleur :</strong> ' . ucfirst($config['color'] ?? 'Non défini') . '<br>';
        if (isset($config['user'])) {
            echo '<strong>Nom :</strong> ' . esc_html($config['user']['firstName'] . ' ' . $config['user']['lastName']) . '<br>';
        }
        if (isset($config['image']['name'])) {
            echo '<strong>Logo :</strong> ' . esc_html($config['image']['name']) . '<br>';
        }
        echo '</p>';

        echo '<p style="margin: 10px 0 0 0; font-size: 12px; color: #856404; background: #fff3cd; padding: 8px; border-radius: 4px;">';
        echo '⏱️ <strong>Délai de production :</strong> 7-10 jours ouvrés';
        echo '</p>';

        echo '</div>';
    }

    /**
     * Génère une URL sécurisée pour le screenshot client
     */
    public function get_customer_screenshot_url($order_id, $item_id, $type = 'thumb')
    {
        // Vérifier les permissions
        if (!$this->can_customer_view_order($order_id)) {
            return false;
        }

        // Générer nonce valide 24h
        $nonce = wp_create_nonce("nfc_customer_screenshot_{$order_id}_{$item_id}");

        return admin_url('admin-ajax.php') . '?' . http_build_query([
            'action' => 'nfc_customer_screenshot',
            'order_id' => $order_id,
            'item_id' => $item_id,
            'type' => $type,
            'nonce' => $nonce
        ]);
    }

    /**
     * Vérifie si un client peut voir une commande
     */
    public function can_customer_view_order($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        // Admin a toujours accès
        if (current_user_can('manage_woocommerce')) {
            return true;
        }

        // Client propriétaire de la commande
        $current_user_id = get_current_user_id();
        if ($current_user_id && $order->get_customer_id() == $current_user_id) {
            return true;
        }

        // Accès invité avec clé de commande (pour emails)
        if (!$current_user_id) {
            $order_key = $_GET['key'] ?? '';
            if ($order_key && $order->get_order_key() === $order_key) {
                return true;
            }
        }

        // ✅ NOUVEAU: Pour les visiteurs, vérifier aussi dans l'URL si la clé est passée
        if (!$current_user_id) {
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($request_uri, 'key=' . $order->get_order_key()) !== false) {
                return true;
            }
        }

        error_log("NFC: Accès refusé pour commande {$order_id}, user ID: {$current_user_id}");

        return false;
    }

    /**
     * Récupère les items NFC d'une commande
     */
    public function get_order_nfc_items($order)
    {
        $nfc_items = [];

        foreach ($order->get_items() as $item_id => $item) {
            $config_data = $item->get_meta('_nfc_config_complete');
            if ($config_data) {
                $nfc_items[] = [
                    'item_id' => $item_id,
                    'item' => $item,
                    'config' => json_decode($config_data, true)
                ];
            }
        }

        return $nfc_items;
    }

    /**
     * Styles CSS pour l'espace client
     */
    public function add_order_custom_styles()
    {
        ?>
        <style>
            /* Styles espace client NFC - Mobile First */
            .nfc-order-preview {
                position: relative;
                display: inline-block;
            }

            .nfc-thumb-small {
                width: 40px;
                height: auto;
                border-radius: 4px;
                border: 1px solid #ddd;
            }

            .nfc-badge {
                position: absolute;
                top: -5px;
                right: -5px;
                background: #0040C1;
                color: white;
                font-size: 10px;
                padding: 2px 6px;
                border-radius: 10px;
                font-weight: bold;
            }

            .nfc-custom-badge {
                background: #f0f0f0;
                color: #666;
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 11px;
            }

            .nfc-no-preview {
                color: #ccc;
                font-size: 18px;
            }

            .nfc-customer-config {
                margin-top: 15px !important;
            }

            .nfc-screenshot-full {
                transition: transform 0.3s ease;
            }

            .nfc-screenshot-full:hover {
                transform: scale(1.02);
            }

            .nfc-config-grid {
                gap: 15px;
            }

            .nfc-detail-item {
                min-height: 40px;
            }

            .nfc-production-status {
                border-radius: 6px;
            }

            /* Responsive Desktop */
            @media (min-width: 768px) {
                .nfc-config-layout {
                    grid-template-columns: 300px 1fr !important;
                    align-items: start;
                }

                .nfc-thumb-small {
                    width: 50px;
                }

                .nfc-config-grid {
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                }
            }

            /* Table des commandes responsive */
            @media (max-width: 767px) {
                .woocommerce-orders-table .nfc-order-preview {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
            }
        </style>
        <?php
    }

    /**
     * Scripts et styles front-end
     */
    public function enqueue_customer_assets()
    {
        // Seulement sur les pages WooCommerce espace client
        if (!is_account_page() && !is_order_received_page()) {
            return;
        }

        // Script pour interactions client (optionnel)
        wp_add_inline_script('jquery', "
            jQuery(document).ready(function($) {
                // Tooltip sur les miniatures
                $('.nfc-thumb-small').hover(function() {
                    $(this).css('transform', 'scale(1.1)');
                }, function() {
                    $(this).css('transform', 'scale(1)');
                });
                
                // Lazy loading des screenshots
                if ('IntersectionObserver' in window) {
                    const observer = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                if (img.dataset.src) {
                                    img.src = img.dataset.src;
                                    img.removeAttribute('data-src');
                                    observer.unobserve(img);
                                }
                            }
                        });
                    });
                    
                    document.querySelectorAll('img[data-src]').forEach(function(img) {
                        observer.observe(img);
                    });
                }
            });
        ");
    }
}

// Initialisation
new NFC_Customer_Integration();