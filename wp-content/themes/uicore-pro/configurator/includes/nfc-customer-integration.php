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

        // Emails clients
        add_action('woocommerce_email_order_item_meta', [$this, 'email_item_screenshot'], 15, 4);

        // Scripts et styles front-end
        add_action('wp_enqueue_scripts', [$this, 'enqueue_customer_assets']);

        add_action('wp_ajax_nfc_customer_screenshot', [$this, 'serve_customer_screenshot']);
        add_action('wp_ajax_nopriv_nfc_customer_screenshot', [$this, 'serve_customer_screenshot']);

        error_log('NFC: Customer Integration initialisé');
    }


    /**
     * Ajoute une colonne aperçu dans l'historique des commandes
     */
    public function add_orders_columns($columns)
    {
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
     * Contenu de la colonne aperçu (simplifié)
     */
    public function orders_column_content($order)
    {
        $nfc_items = $this->get_order_nfc_items($order);

        if (empty($nfc_items)) {
            echo '<span class="nfc-no-preview">—</span>';
            return;
        }

        $count = count($nfc_items);
        if ($count === 1) {
            echo '<span class="nfc-single-badge">Personnalisé</span>';
        } else {
            echo '<span class="nfc-multi-badge">' . $count . ' cartes</span>';
        }
    }

    /**
     * Sert les screenshots aux clients autorisés
     */
    public function serve_customer_screenshot()
    {
        $order_id = intval($_GET['order_id'] ?? 0);
        $item_id = intval($_GET['item_id'] ?? 0);
        $type = sanitize_text_field($_GET['type'] ?? 'thumb');
        $nonce = sanitize_text_field($_GET['nonce'] ?? '');

        if (!wp_verify_nonce($nonce, "nfc_customer_screenshot_{$order_id}_{$item_id}")) {
            wp_die('Accès non autorisé', 'Erreur', ['response' => 403]);
        }

        if (!$this->can_customer_view_order($order_id)) {
            wp_die('Accès refusé', 'Erreur', ['response' => 403]);
        }

        try {
            if (class_exists('NFC_File_Handler')) {
                $file_handler = new NFC_File_Handler();
                $file_handler->display_customer_screenshot($order_id, $item_id, $type);
            } else {
                wp_die('Service non disponible', 'Erreur', ['response' => 500]);
            }
        } catch (Exception $e) {
            error_log('NFC: Erreur screenshot client: ' . $e->getMessage());
            wp_die('Screenshot non disponible', 'Erreur', ['response' => 404]);
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
     * Screenshot dans les emails clients (simplifié)
     */
    public function email_item_screenshot($item_id, $item, $order, $sent_to_admin = false)
    {
        if ($sent_to_admin) {
            return;
        }

        $config_data = $item->get_meta('_nfc_config_complete');
        if (!$config_data) {
            return;
        }

        $config = json_decode($config_data, true);
        
        echo "\n--- Configuration personnalisée ---\n";
        if (isset($config['color'])) {
            echo "Couleur: " . ucfirst($config['color']) . "\n";
        }
        if (isset($config['user'])) {
            $fullName = trim(($config['user']['firstName'] ?? '') . ' ' . ($config['user']['lastName'] ?? ''));
            if ($fullName) {
                echo "Nom: " . $fullName . "\n";
            }
        }
        if (isset($config['image']['name']) && !empty($config['image']['name'])) {
            echo "Logo: " . $config['image']['name'] . "\n";
        }
        echo "Délai de production: 7-10 jours ouvrés\n";
        echo "--------------------------------\n";
    }

    /**
     * Génère une URL sécurisée pour le screenshot client
     */
    public function get_customer_screenshot_url($order_id, $item_id, $type = 'thumb')
    {
        if (!$this->can_customer_view_order($order_id)) {
            return false;
        }

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
        if (!$order_id) return false;

        $order = wc_get_order($order_id);
        if (!$order) return false;

        // Admin peut toujours voir
        if (current_user_can('manage_woocommerce')) {
            return true;
        }

        // Client connecté propriétaire de la commande
        if (is_user_logged_in() && $order->get_customer_id() === get_current_user_id()) {
            return true;
        }

        // Invité avec la bonne clé de commande
        if (!is_user_logged_in()) {
            $order_key = $_GET['key'] ?? '';
            return $order_key && $order->get_order_key() === $order_key;
        }

        return false;
    }

    /**
     * Récupère les items NFC d'une commande
     */
    public function get_order_nfc_items($order)
    {
        $nfc_items = [];
        foreach ($order->get_items() as $item_id => $item) {
            if ($item->get_meta('_nfc_config_complete')) {
                $nfc_items[] = [
                    'item_id' => $item_id,
                    'item' => $item
                ];
            }
        }
        return $nfc_items;
    }

    /**
     * Assets simplifiés
     */
    public function enqueue_customer_assets()
    {
        if (is_account_page()) {
            // CSS minimal pour les badges dans l'historique
            $css = "
                .nfc-no-preview { color: #999; }
                .nfc-single-badge { 
                    background: #0040C1; 
                    color: white; 
                    padding: 2px 8px; 
                    border-radius: 12px; 
                    font-size: 11px; 
                    font-weight: 600; 
                }
                .nfc-multi-badge { 
                    background: #e62e26; 
                    color: white; 
                    padding: 2px 8px; 
                    border-radius: 12px; 
                    font-size: 11px; 
                    font-weight: 600; 
                }
            ";
            wp_add_inline_style('woocommerce-general', $css);
        }
    }

}

// Initialisation
new NFC_Customer_Integration();