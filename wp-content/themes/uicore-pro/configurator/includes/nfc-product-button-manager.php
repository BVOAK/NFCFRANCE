<?php
/**
 * Gestionnaire des boutons de produits NFC
 * 
 * Classe responsable de l'analyse des ACF et de la génération de configuration
 * pour les boutons des produits NFC France.
 * 
 * Cette classe ne gère QUE la logique métier, pas le rendu HTML.
 * 
 * @package NFC_Configurator
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_Product_Button_Manager {
    
    /**
     * Configuration par défaut pour les produits sans ACF
     */
    private const DEFAULT_CONFIG = [
        'product_type' => 'other',
        'buttons' => ['standard'],
        'configurator_url' => null,
        'layout' => 'single',
        'primary_action' => 'standard',
        'secondary_action' => null
    ];
    
    /**
     * URLs par défaut du configurateur selon le type de produit
     */
    private const DEFAULT_CONFIGURATOR_URLS = [
        'vcard' => '/configurateur?type=vcard',
        'google_reviews' => '/configurateur?type=avis',
        'other' => '/configurateur'
    ];
    
    public function __construct() {
        // Log d'initialisation en mode debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('NFC_Product_Button_Manager: Classe initialisée');
        }
    }
    
    /**
     * Récupère la configuration complète des boutons pour un produit
     * 
     * @param int $product_id ID du produit WooCommerce
     * @return array Configuration des boutons
     */
    public function get_product_button_config($product_id) {
        // Vérifier que le produit existe et est valide
        if (!$this->is_valid_product($product_id)) {
            error_log("NFC_Product_Button_Manager: Produit {$product_id} invalide ou inexistant");
            return $this->get_fallback_config($product_id);
        }
        
        // Récupérer les champs ACF
        $acf_data = $this->get_product_acf_data($product_id);
        
        // Analyser et construire la configuration
        $config = $this->build_button_configuration($product_id, $acf_data);
        
        // Log de la configuration générée en mode debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("NFC_Product_Button_Manager: Config produit {$product_id} - " . json_encode($config));
        }
        
        return $config;
    }
    
    /**
     * Vérifie si un produit peut être configuré (a un configurateur)
     * 
     * @param int $product_id ID du produit
     * @return bool
     */
    public function is_configurable_product($product_id) {
        if (!$this->is_valid_product($product_id)) {
            return false;
        }
        
        $has_configurator = get_field('nfc_has_configurator', $product_id);
        
        // Fallback sur les anciens IDs hardcodés pour compatibilité
        if ($has_configurator === null) {
            $legacy_configurable = [571, 572, 573];
            return in_array(intval($product_id), $legacy_configurable);
        }
        
        return (bool) $has_configurator;
    }
    
    /**
     * Récupère l'URL du configurateur pour un produit
     * 
     * @param int $product_id ID du produit
     * @return string URL du configurateur
     */
    public function get_configurator_url($product_id) {
        if (!$this->is_configurable_product($product_id)) {
            return '';
        }
        
        // URL personnalisée définie dans ACF
        $custom_url = get_field('nfc_configurator_url', $product_id);
        if (!empty($custom_url)) {
            return $this->normalize_url($custom_url);
        }
        
        // URL par défaut selon le type de produit
        $product_type = get_field('nfc_product_type', $product_id) ?: 'other';
        $default_url = self::DEFAULT_CONFIGURATOR_URLS[$product_type] ?? self::DEFAULT_CONFIGURATOR_URLS['other'];
        
        return home_url($default_url . '&product_id=' . $product_id);
    }
    
    /**
     * Vérifie si un produit accepte l'upload de fichiers
     * 
     * @param int $product_id ID du produit
     * @return bool
     */
    public function has_file_upload($product_id) {
        if (!$this->is_valid_product($product_id)) {
            return false;
        }
        
        return (bool) get_field('nfc_requires_files', $product_id);
    }
    
    /**
     * Détermine le layout des boutons (single ou dual)
     * 
     * @param int $product_id ID du produit
     * @return string 'single' ou 'dual'
     */
    public function get_button_layout($product_id) {
        $config = $this->get_product_button_config($product_id);
        return $config['layout'];
    }
    
    /**
     * Récupère les contraintes de fichiers pour un produit
     * 
     * @param int $product_id ID du produit
     * @return array|null Contraintes de fichiers ou null
     */
    public function get_file_constraints($product_id) {
        if (!$this->has_file_upload($product_id)) {
            return null;
        }
        
        $constraints = get_field('nfc_file_constraints', $product_id);
        
        // Structure par défaut si pas de contraintes définies
        if (empty($constraints)) {
            return [
                'formats_acceptes' => ['PDF', 'PNG', 'JPG'],
                'resolution_min' => '300 DPI',
                'delai_production' => '5-7 jours ouvrés'
            ];
        }
        
        return $constraints;
    }
    
    /**
     * Vérifie si un produit est valide et publié
     * 
     * @param int $product_id ID du produit
     * @return bool
     */
    private function is_valid_product($product_id) {
        if (!$product_id || !is_numeric($product_id)) {
            return false;
        }
        
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return false;
        }
        
        // Vérifier que le produit est publié
        return $product->get_status() === 'publish';
    }
    
    /**
     * Récupère les données ACF d'un produit
     * 
     * @param int $product_id ID du produit
     * @return array Données ACF
     */
    private function get_product_acf_data($product_id) {
        return [
            'product_type' => get_field('nfc_product_type', $product_id) ?: 'other',
            'has_configurator' => (bool) get_field('nfc_has_configurator', $product_id),
            'requires_files' => (bool) get_field('nfc_requires_files', $product_id),
            'dashboard_template' => get_field('nfc_dashboard_template', $product_id) ?: 'none',
            'configurator_url' => get_field('nfc_configurator_url', $product_id),
            'file_constraints' => get_field('nfc_file_constraints', $product_id)
        ];
    }
    
    /**
     * Construit la configuration des boutons basée sur les ACF
     * 
     * @param int $product_id ID du produit
     * @param array $acf_data Données ACF du produit
     * @return array Configuration des boutons
     */
    private function build_button_configuration($product_id, $acf_data) {
        $product_type = $acf_data['product_type'];
        $has_configurator = $acf_data['has_configurator'];
        $requires_files = $acf_data['requires_files'];
        
        // Déterminer les boutons nécessaires selon la logique métier
        $buttons = $this->determine_buttons($product_type, $has_configurator, $requires_files);
        
        // Configuration de base
        $config = [
            'product_type' => $product_type,
            'buttons' => $buttons,
            'configurator_url' => $has_configurator ? $this->get_configurator_url($product_id) : null,
            'layout' => count($buttons) > 1 ? 'dual' : 'single',
            'primary_action' => $buttons[0] ?? 'standard',
            'secondary_action' => $buttons[1] ?? null,
            'file_constraints' => $requires_files ? $this->get_file_constraints($product_id) : null
        ];
        
        return $config;
    }
    
    /**
     * Détermine quels boutons afficher selon la logique métier
     * 
     * @param string $product_type Type de produit
     * @param bool $has_configurator Le produit a-t-il un configurateur
     * @param bool $requires_files Le produit accepte-t-il les fichiers
     * @return array Liste des types de boutons
     */
    private function determine_buttons($product_type, $has_configurator, $requires_files) {
        // Produits accessoires → toujours bouton standard
        if ($product_type === 'accessory') {
            return ['standard'];
        }
        
        // Produits vCard ou Avis Google
        $buttons = [];
        
        // Ordre de priorité : configurateur d'abord, puis fichiers
        if ($has_configurator) {
            $buttons[] = 'configurator';
        }
        
        if ($requires_files) {
            $buttons[] = 'files';
        }
        
        // Si aucune option spécifique, bouton standard
        if (empty($buttons)) {
            $buttons[] = 'standard';
        }
        
        return $buttons;
    }
    
    /**
     * Configuration de fallback en cas d'erreur ou produit invalide
     * 
     * @param int $product_id ID du produit
     * @return array Configuration par défaut
     */
    private function get_fallback_config($product_id) {
        // Compatibilité avec anciens IDs hardcodés
        $legacy_configurable = [571, 572, 573];
        
        if (in_array(intval($product_id), $legacy_configurable)) {
            return [
                'product_type' => 'vcard',
                'buttons' => ['configurator'],
                'configurator_url' => home_url('/configurateur?product_id=' . $product_id),
                'layout' => 'single',
                'primary_action' => 'configurator',
                'secondary_action' => null
            ];
        }
        
        return self::DEFAULT_CONFIG;
    }
    
    /**
     * Normalise une URL (ajoute le domaine si relatif)
     * 
     * @param string $url URL à normaliser
     * @return string URL normalisée
     */
    private function normalize_url($url) {
        // Si l'URL commence par /, c'est une URL relative
        if (strpos($url, '/') === 0) {
            return home_url($url);
        }
        
        // Si l'URL contient déjà le protocole, la retourner telle quelle
        if (strpos($url, 'http') === 0) {
            return $url;
        }
        
        // Sinon, considérer comme relative et ajouter le domaine
        return home_url('/' . ltrim($url, '/'));
    }
    
    /**
     * Méthode utilitaire pour debug - affiche la config d'un produit
     * 
     * @param int $product_id ID du produit
     * @return void
     */
    public function debug_product_config($product_id) {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $config = $this->get_product_button_config($product_id);
        $acf_data = $this->get_product_acf_data($product_id);
        
        error_log("=== DEBUG NFC Product {$product_id} ===");
        error_log("ACF Data: " . json_encode($acf_data, JSON_PRETTY_PRINT));
        error_log("Button Config: " . json_encode($config, JSON_PRETTY_PRINT));
        error_log("=== END DEBUG ===");
    }
}