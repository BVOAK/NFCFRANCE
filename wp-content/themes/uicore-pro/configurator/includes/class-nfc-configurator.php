<?php
/**
 * Classe principale du configurateur NFC
 * Gère la logique métier côté serveur
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_Configurator {
    
    private $product_manager;
    
    public function __construct() {
        $this->product_manager = new NFC_Product_Manager();
        
        add_action('init', [$this, 'init']);
    }
    
    public function init() {
        // Hooks pour personnaliser WooCommerce
        add_action('woocommerce_before_single_product_summary', [$this, 'maybe_add_configurator_notice']);
        
        // Hooks pour l'affichage des métadonnées
        add_filter('woocommerce_order_item_display_meta_key', [$this, 'translate_meta_keys']);
        add_filter('woocommerce_order_item_display_meta_value', [$this, 'format_meta_values'], 10, 2);
    }
       
    
    /**
     * Ajoute une notice sur les produits configurables
     */
    public function maybe_add_configurator_notice() {
        global $product;
        
        if ($product && $this->product_manager->can_be_configured($product->get_id())) {
            echo '<div class="nfc-product-notice" style="
                background: #e8f4fd; 
                border: 1px solid #667eea; 
                border-radius: 6px; 
                padding: 15px; 
                margin: 20px 0;
                color: #1a202c;
            ">';
            echo '<p style="margin: 0;"><strong>🎨 Produit personnalisable</strong><br>';
            echo 'Créez votre carte NFC unique avec vos couleurs et votre logo grâce à notre configurateur en ligne.</p>';
            echo '</div>';
        }
    }
    
    /**
     * Traduit les clés de métadonnées pour l'affichage
     */
    public function translate_meta_keys($key) {
        $translations = [
            '_nfc_couleur' => 'Couleur de la carte',
            '_nfc_nom' => 'Nom sur la carte',
            '_nfc_image' => 'Image personnalisée'
        ];
        
        return isset($translations[$key]) ? $translations[$key] : $key;
    }
    
    /**
     * Formate les valeurs de métadonnées pour l'affichage
     */
    public function format_meta_values($value, $meta) {
        switch ($meta->key) {
            case '_nfc_couleur':
                return ucfirst($value);
                
            case '_nfc_image':
                if (strpos($value, 'Logo personnalisé:') === false) {
                    return 'Logo personnalisé: ' . $value;
                }
                return $value;
                
            default:
                return $value;
        }
    }
    
    /**
     * Génère un aperçu de configuration
     */
    public function generate_config_preview($config) {
        if (!is_array($config)) {
            return false;
        }
        
        $preview = [
            'color' => isset($config['color']) ? ucfirst($config['color']) : 'Non défini',
            'user_name' => 'Non défini',
            'has_image' => false
        ];
        
        // Nom utilisateur
        if (isset($config['user']) && is_array($config['user'])) {
            $firstName = $config['user']['firstName'] ?? '';
            $lastName = $config['user']['lastName'] ?? '';
            if ($firstName && $lastName) {
                $preview['user_name'] = $firstName . ' ' . $lastName;
            }
        }
        
        // Image
        if (isset($config['image']) && is_array($config['image'])) {
            $preview['has_image'] = !empty($config['image']['data']);
            $preview['image_name'] = $config['image']['name'] ?? 'Image sans nom';
        }
        
        return $preview;
    }
    
    /**
     * Exporte une configuration pour la production
     */
    public function export_config_for_production($config) {
        if (!is_array($config)) {
            return false;
        }
        
        $export = [
            'version' => '1.0',
            'created_at' => date('Y-m-d H:i:s'),
            'product_id' => $config['product_id'] ?? null,
            'variation_id' => $config['variation_id'] ?? null,
            'specifications' => [
                'color' => $config['color'] ?? 'blanc',
                'dimensions' => '85x55mm',
                'format' => 'ISO/IEC 14443 Type A'
            ],
            'personalization' => [
                'user_name' => '',
                'image' => null
            ]
        ];
        
        // Nom utilisateur
        if (isset($config['user']) && is_array($config['user'])) {
            $firstName = $config['user']['firstName'] ?? '';
            $lastName = $config['user']['lastName'] ?? '';
            $export['personalization']['user_name'] = trim($firstName . ' ' . $lastName);
        }
        
        // Image
        if (isset($config['image']) && is_array($config['image']) && !empty($config['image']['data'])) {
            $export['personalization']['image'] = [
                'name' => $config['image']['name'] ?? 'logo.jpg',
                'data' => $config['image']['data'],
                'scale' => $config['image']['scale'] ?? 100
            ];
        }
        
        return $export;
    }
    
    /**
     * Valide qu'une configuration est complète pour la production
     */
    public function validate_config_for_production($config) {
        $errors = [];
        
        // Vérifications de base
        if (empty($config['color'])) {
            $errors[] = 'Couleur manquante';
        }
        
        if (empty($config['variation_id'])) {
            $errors[] = 'Variation de produit manquante';
        }
        
        // Informations utilisateur
        if (empty($config['user']['firstName']) || empty($config['user']['lastName'])) {
            $errors[] = 'Nom et prénom requis';
        }
        
        // Validation de l'image si présente
        if (isset($config['image']) && !empty($config['image']['data'])) {
            if (!$this->validate_image_for_production($config['image'])) {
                $errors[] = 'Image non valide pour la production';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Valide une image pour la production
     */
    private function validate_image_for_production($image) {
        // Vérifications basiques
        if (empty($image['data']) || empty($image['name'])) {
            return false;
        }
        
        // Vérifier le format base64
        if (!preg_match('/^data:image\/(jpeg|png|svg\+xml);base64,/', $image['data'])) {
            return false;
        }
        
        // Vérifier la taille (minimum pour impression)
        $base64_data = substr($image['data'], strpos($image['data'], ',') + 1);
        $decoded_data = base64_decode($base64_data);
        
        if (strlen($decoded_data) < 1024) { // Moins de 1KB = trop petit
            return false;
        }
        
        return true;
    }
    
    /**
     * Génère un ID unique pour une configuration
     */
    public function generate_config_id($config) {
        $base = $config['variation_id'] . '-' . $config['color'];
        
        if (isset($config['user'])) {
            $base .= '-' . sanitize_title($config['user']['firstName'] . $config['user']['lastName']);
        }
        
        if (isset($config['timestamp'])) {
            $base .= '-' . $config['timestamp'];
        }
        
        return 'nfc-' . substr(md5($base), 0, 8);
    }
    
    /**
     * Récupère les statistiques des configurations
     */
    public function get_config_stats() {
        global $wpdb;
        
        // Récupérer les métadonnées des commandes
        $results = $wpdb->get_results("
            SELECT meta_value 
            FROM {$wpdb->prefix}woocommerce_order_itemmeta 
            WHERE meta_key = '_nfc_couleur'
        ");
        
        $stats = [
            'total_configurations' => count($results),
            'colors' => ['blanc' => 0, 'noir' => 0],
            'last_updated' => current_time('mysql')
        ];
        
        foreach ($results as $result) {
            if (isset($stats['colors'][$result->meta_value])) {
                $stats['colors'][$result->meta_value]++;
            }
        }
        
        return $stats;
    }
}

// Initialisation
new NFC_Configurator();