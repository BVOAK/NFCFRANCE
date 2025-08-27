<?php
/**
 * Gestionnaire de rendu HTML pour les boutons produits NFC
 * 
 * Responsabilités :
 * - Shortcodes [nfc_product_buttons] et [nfc_configurator_button]
 * - Templates HTML des boutons  
 * - Intégration Elementor Builder
 * - Gestion des assets CSS/JS
 * 
 * @package NFC_Configurator
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_Button_Renderer {
    
    /**
     * Instance du gestionnaire de boutons
     * @var NFC_Product_Button_Manager
     */
    private $button_manager;
    
    /**
     * Version des assets pour cache busting
     * @var string
     */
    private $version = '1.0.0';
    
    public function __construct() {
        // Vérifier que la classe manager existe
        if (!class_exists('NFC_Product_Button_Manager')) {
            error_log('NFC_Button_Renderer: NFC_Product_Button_Manager class not found');
            return;
        }
        
        // Instancier le button manager
        $this->button_manager = new NFC_Product_Button_Manager();
        
        // Initialiser
        add_action('init', [$this, 'init']);
        
        // Log d'initialisation
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('NFC_Button_Renderer: Classe initialisée');
        }
    }
    
    /**
     * Initialisation des hooks et shortcodes
     */
    public function init() {
        $this->register_shortcodes();
        $this->enqueue_assets();
        
        // Hook pour debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_action('wp_footer', [$this, 'debug_render_handler']);
        }
    }
    
    /**
     * Enregistrement des shortcodes
     */
    public function register_shortcodes() {
        // Nouveau shortcode principal
        add_shortcode('nfc_product_buttons', [$this, 'product_buttons_shortcode']);
        
        // Ancien shortcode (compatibilité)
        add_shortcode('nfc_configurator_button', [$this, 'configurator_button_shortcode']);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('NFC_Button_Renderer: Shortcodes enregistrés');
        }
    }
    
    /**
     * Gestion des assets CSS/JS
     */
    public function enqueue_assets() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_button_assets']);
    }
    
    /**
     * Nouveau shortcode principal [nfc_product_buttons]
     * Compatible Elementor Builder avec auto-détection du produit
     * 
     * @param array $atts Attributs du shortcode
     * @return string HTML des boutons
     */
    public function product_buttons_shortcode($atts) {
        global $product;
        
        // Valeurs par défaut
        $atts = shortcode_atts([
            'product_id' => 0,              // 0 = auto-détection
            'layout' => 'vertical',          // 'vertical' | 'horizontal'
            'size' => 'md',                 // 'xs' | 'sm' | 'md' | 'lg' | 'xl'
            'primary_style' => 'solid',      // Style bouton principal
            'secondary_style' => 'outline',  // Style bouton secondaire
            'class' => ''                   // Classes CSS additionnelles
        ], $atts);
        
        // AUTO-DÉTECTION du produit (pour Elementor Builder)
        $product_id = intval($atts['product_id']);
        
        if (!$product_id) {
            // Méthode 1 : Variable globale $product (pages produits)
            if ($product && $product->get_id()) {
                $product_id = $product->get_id();
            }
            // Méthode 2 : URL parameter (configurateur vers produit)
            elseif (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
                $product_id = intval($_GET['product_id']);
            }
            // Méthode 3 : Post actuel (si on est sur une page produit)
            elseif (is_product()) {
                $product_id = get_the_ID();
            }
            // Méthode 4 : Hook Elementor pour template de produit
            elseif (class_exists('ElementorPro\Modules\ThemeBuilder\Module')) {
                $queried_object = get_queried_object();
                if ($queried_object && isset($queried_object->ID)) {
                    $product_id = $queried_object->ID;
                }
            }
        }
        
        if (!$product_id) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('NFC Shortcode: Impossible de détecter le produit automatiquement. Context: ' . (is_product() ? 'product page' : get_post_type()));
            }
            return '';
        }
        
        // Log de debug pour vérifier la détection
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("NFC Shortcode: Produit auto-détecté: {$product_id}");
        }
        
        // Récupérer la configuration via le button manager
        $config = $this->button_manager->get_product_button_config($product_id);
        
        // Ajouter l'ID du produit dans la config pour les templates
        $config['product_id'] = $product_id;
        
        // Si pas de boutons spéciaux, ne rien afficher (laisser WooCommerce gérer)
        if (empty($config['buttons']) || (in_array('standard', $config['buttons']) && count($config['buttons']) === 1)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("NFC Shortcode: Produit {$product_id} utilise le bouton standard WooCommerce");
            }
            return '';
        }
        
        // Générer le HTML selon le layout
        if ($config['layout'] === 'dual') {
            return $this->render_dual_buttons($config, $atts);
        } else {
            return $this->render_single_button($config, $atts);
        }
    }
    
    /**
     * Rendu d'un seul bouton
     * 
     * @param array $config Configuration des boutons
     * @param array $atts Attributs du shortcode
     * @return string HTML du bouton
     */
    private function render_single_button($config, $atts) {
        $primary_action = $config['primary_action'];
        $product_id = $config['product_id'] ?? 0;
        
        // Déterminer les propriétés du bouton
        $button_props = $this->get_button_properties($primary_action, $config);
        
        // Classes CSS
        $css_classes = $this->get_button_css_classes($atts, 'single', 'primary');
        
        ob_start();
        ?>
        <div class="nfc-product-buttons nfc-single-button nfc-layout-<?php echo esc_attr($atts['layout']); ?> <?php echo esc_attr($atts['class']); ?>">
            <?php if ($primary_action === 'configurator'): ?>
                <a href="<?php echo esc_url($config['configurator_url']); ?>" 
                   class="<?php echo esc_attr($css_classes); ?>"
                   data-product-id="<?php echo esc_attr($product_id); ?>"
                   data-action="configurator"
                   data-nfc-button="configurator">
                    <i class="<?php echo esc_attr($button_props['icon']); ?>"></i>
                    <span class="nfc-btn-text"><?php echo esc_html($button_props['text']); ?></span>
                </a>
            <?php elseif ($primary_action === 'files'): ?>
                <button type="button" 
                        class="<?php echo esc_attr($css_classes); ?>"
                        data-product-id="<?php echo esc_attr($product_id); ?>"
                        data-action="add-with-files"
                        data-nfc-button="files">
                    <i class="<?php echo esc_attr($button_props['icon']); ?>"></i>
                    <span class="nfc-btn-text"><?php echo esc_html($button_props['text']); ?></span>
                    <span class="nfc-btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                        <?php echo esc_html__('Ajout en cours...', 'nfc-configurator'); ?>
                    </span>
                </button>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Rendu de deux boutons (configurateur + fichiers)
     * 
     * @param array $config Configuration des boutons
     * @param array $atts Attributs du shortcode
     * @return string HTML des boutons
     */
    private function render_dual_buttons($config, $atts) {
        $product_id = $config['product_id'] ?? 0;
        
        // Propriétés des boutons
        $primary_props = $this->get_button_properties($config['primary_action'], $config);
        $secondary_props = $this->get_button_properties($config['secondary_action'], $config);
        
        // Classes CSS
        $primary_css = $this->get_button_css_classes($atts, 'dual', 'primary');
        $secondary_css = $this->get_button_css_classes($atts, 'dual', 'secondary');
        
        ob_start();
        ?>
        <div class="nfc-product-buttons nfc-dual-buttons nfc-layout-<?php echo esc_attr($atts['layout']); ?> <?php echo esc_attr($atts['class']); ?>">
            
            <!-- Bouton principal (configurateur) -->
            <a href="<?php echo esc_url($config['configurator_url']); ?>" 
               class="<?php echo esc_attr($primary_css); ?>"
               data-product-id="<?php echo esc_attr($product_id); ?>"
               data-action="configurator"
               data-nfc-button="configurator">
                <i class="<?php echo esc_attr($primary_props['icon']); ?>"></i>
                <span class="nfc-btn-text"><?php echo esc_html($primary_props['text']); ?></span>
            </a>
            
            <!-- Bouton secondaire (fichiers) -->
            <button type="button" 
                    class="<?php echo esc_attr($secondary_css); ?>"
                    data-product-id="<?php echo esc_attr($product_id); ?>"
                    data-action="add-with-files"
                    data-nfc-button="files">
                <i class="<?php echo esc_attr($secondary_props['icon']); ?>"></i>
                <span class="nfc-btn-text"><?php echo esc_html($secondary_props['text']); ?></span>
                <span class="nfc-btn-loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                    <?php echo esc_html__('Ajout en cours...', 'nfc-configurator'); ?>
                </span>
            </button>
            
        </div>
        
        <!-- Informations contraintes fichiers -->
        <?php if (!empty($config['file_constraints'])): ?>
            <div class="nfc-file-constraints" style="margin-top: 1rem;">
                <details class="nfc-constraints-details">
                    <summary class="nfc-constraints-summary">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            <?php echo esc_html__('Informations fichiers', 'nfc-configurator'); ?>
                        </small>
                    </summary>
                    <div class="nfc-constraints-content" style="padding: 0.75rem; background: #f8f9fa; border-radius: 4px; margin-top: 0.5rem;">
                        <?php $this->render_file_constraints($config['file_constraints']); ?>
                    </div>
                </details>
            </div>
        <?php endif; ?>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Récupère les propriétés d'un bouton (texte, icône)
     * 
     * @param string $action_type Type d'action (configurator, files, standard)
     * @param array $config Configuration du produit
     * @return array Propriétés du bouton
     */
    private function get_button_properties($action_type, $config) {
        switch ($action_type) {
            case 'configurator':
                return [
                    'text' => __('Personnaliser en ligne', 'nfc-configurator'),
                    'icon' => 'fas fa-magic nfc-icon-configurator'
                ];
                
            case 'files':
                return [
                    'text' => __('Commander et envoyer fichiers', 'nfc-configurator'),
                    'icon' => 'fas fa-upload nfc-icon-upload'
                ];
                
            case 'standard':
            default:
                return [
                    'text' => __('Ajouter au panier', 'nfc-configurator'),
                    'icon' => 'fas fa-shopping-cart nfc-icon-cart'
                ];
        }
    }
    
    /**
     * Génère les classes CSS d'un bouton
     * 
     * @param array $atts Attributs du shortcode
     * @param string $layout Layout des boutons (single/dual)
     * @param string $button_type Type de bouton (primary/secondary)
     * @return string Classes CSS
     */
    private function get_button_css_classes($atts, $layout, $button_type) {
        $classes = ['nfc-btn'];
        
        // Classe de base selon le type
        if ($button_type === 'primary') {
            $classes[] = 'nfc-btn-primary';
            $classes[] = 'nfc-btn-' . $atts['primary_style'];
        } else {
            $classes[] = 'nfc-btn-secondary';
            $classes[] = 'nfc-btn-' . $atts['secondary_style'];
        }
        
        // Taille
        $classes[] = 'nfc-size-' . $atts['size'];
        
        // Classes Elementor si dans Elementor
        if (defined('ELEMENTOR_VERSION') || $this->is_elementor_context()) {
            $classes[] = 'elementor-button';
            $classes[] = 'elementor-button-link';
            $classes[] = 'elementor-size-' . $atts['size'];
            
            // Animation Elementor par défaut
            $classes[] = 'elementor-animation-grow';
        }
        
        return implode(' ', $classes);
    }
    
    /**
     * Rendu des contraintes de fichiers
     * 
     * @param array $constraints Contraintes des fichiers
     * @return void
     */
    private function render_file_constraints($constraints) {
        ?>
        <div class="nfc-file-info">
            <?php if (!empty($constraints['formats_acceptes'])): ?>
                <p class="nfc-constraint-item">
                    <strong><?php echo esc_html__('Formats acceptés :', 'nfc-configurator'); ?></strong>
                    <?php echo esc_html(implode(', ', $constraints['formats_acceptes'])); ?>
                </p>
            <?php endif; ?>
            
            <?php if (!empty($constraints['taille_max'])): ?>
                <p class="nfc-constraint-item">
                    <strong><?php echo esc_html__('Taille max :', 'nfc-configurator'); ?></strong>
                    <?php echo esc_html($constraints['taille_max']); ?>
                </p>
            <?php endif; ?>
            
            <?php if (!empty($constraints['resolution_min'])): ?>
                <p class="nfc-constraint-item">
                    <strong><?php echo esc_html__('Résolution minimum :', 'nfc-configurator'); ?></strong>
                    <?php echo esc_html($constraints['resolution_min']); ?> DPI
                </p>
            <?php endif; ?>
            
            <?php if (!empty($constraints['nb_fichiers_max'])): ?>
                <p class="nfc-constraint-item">
                    <strong><?php echo esc_html__('Nombre de fichiers max :', 'nfc-configurator'); ?></strong>
                    <?php echo esc_html($constraints['nb_fichiers_max']); ?>
                </p>
            <?php endif; ?>
            
            <?php if (!empty($constraints['instructions_client'])): ?>
                <p class="nfc-constraint-instructions">
                    <em><?php echo esc_html($constraints['instructions_client']); ?></em>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Shortcode de compatibilité avec l'ancien système
     * 
     * @param array $atts Attributs du shortcode
     * @return string HTML du bouton
     */
    public function configurator_button_shortcode($atts) {
        // Log de dépréciation en mode debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('NFC: Shortcode [nfc_configurator_button] is deprecated, use [nfc_product_buttons]');
        }
        
        // Mapper les anciens attributs vers les nouveaux
        $mapped_atts = [
            'product_id' => $atts['product_id'] ?? 0,
            'layout' => 'vertical',
            'size' => $atts['size'] ?? 'md',
            'primary_style' => 'solid',
            'secondary_style' => 'outline',
            'class' => $atts['class'] ?? ''
        ];
        
        // Rediriger vers le nouveau système
        return $this->product_buttons_shortcode($mapped_atts);
    }
    
    /**
     * Enqueue des assets CSS/JS
     */
    public function enqueue_button_assets() {
        // Vérifier si on a besoin des assets
        if (!$this->should_load_assets()) {
            return;
        }
        
        // CSS des boutons
        wp_enqueue_style(
            'nfc-product-buttons',
            get_template_directory_uri() . '/configurator/assets/css/product-buttons.css',
            [],
            $this->version
        );
        
        // JavaScript des interactions
        wp_enqueue_script(
            'nfc-product-buttons',
            get_template_directory_uri() . '/configurator/assets/js/product-buttons.js',
            ['jquery'],
            $this->version,
            true
        );
        
        // Configuration JavaScript
        wp_localize_script('nfc-product-buttons', 'nfcButtons', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nfc_buttons'),
            'cartUrl' => wc_get_cart_url(),
            'i18n' => [
                'adding' => __('Ajout en cours...', 'nfc-configurator'),
                'added' => __('Ajouté au panier avec succès', 'nfc-configurator'),
                'error' => __('Erreur lors de l\'ajout', 'nfc-configurator'),
                'fileError' => __('Erreur lors de l\'upload des fichiers', 'nfc-configurator')
            ]
        ]);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('NFC_Button_Renderer: Assets CSS/JS enqueueés');
        }
    }
    
    /**
     * Détermine si on doit charger les assets
     * 
     * @return bool
     */
    private function should_load_assets() {
        global $post;
        
        // Toujours charger sur les pages produits
        if (is_product()) {
            return true;
        }
        
        // Charger si shortcode détecté dans le contenu
        if ($post && has_shortcode($post->post_content, 'nfc_product_buttons')) {
            return true;
        }
        
        // Charger si ancien shortcode détecté
        if ($post && has_shortcode($post->post_content, 'nfc_configurator_button')) {
            return true;
        }
        
        // Charger si on est dans Elementor (preview/edit)
        if ($this->is_elementor_context()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Détecte si on est dans un contexte Elementor
     * 
     * @return bool
     */
    private function is_elementor_context() {
        return (
            defined('ELEMENTOR_VERSION') && (
                \Elementor\Plugin::$instance->preview->is_preview_mode() ||
                \Elementor\Plugin::$instance->editor->is_edit_mode() ||
                (isset($_GET['elementor-preview']) && !empty($_GET['elementor-preview']))
            )
        );
    }
    
    /**
     * Handler pour debug du rendu (développement)
     */
    public function debug_render_handler() {
        if (isset($_GET['test_nfc_render']) && current_user_can('administrator')) {
            $product_id = intval($_GET['test_nfc_render']);
            $this->debug_button_render($product_id);
        }
    }
    
    /**
     * Méthode utilitaire pour tester le rendu - DÉVELOPPEMENT UNIQUEMENT
     * 
     * @param int $product_id ID du produit à tester
     * @return void
     */
    public function debug_button_render($product_id) {
        $config = $this->button_manager->get_product_button_config($product_id);
        
        echo '<div style="position: fixed; top: 50px; right: 20px; background: #f1f1f1; padding: 20px; border-radius: 8px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">';
        echo "<h3 style='margin-top: 0;'>Test Rendu Boutons - Produit {$product_id}</h3>";
        
        echo '<h4>Configuration :</h4>';
        echo '<pre style="font-size: 11px; max-height: 200px; overflow: auto;">' . json_encode($config, JSON_PRETTY_PRINT) . '</pre>';
        
        echo '<h4>Rendu HTML :</h4>';
        echo '<div style="border: 1px dashed #ccc; padding: 15px; background: white; margin: 10px 0;">';
        
        // Test du shortcode avec différents layouts
        echo '<p><strong>Layout Vertical :</strong></p>';
        echo do_shortcode("[nfc_product_buttons product_id='{$product_id}' layout='vertical']");
        
        echo '<p><strong>Layout Horizontal :</strong></p>';
        echo do_shortcode("[nfc_product_buttons product_id='{$product_id}' layout='horizontal']");
        
        echo '</div>';
        echo '<button onclick="this.parentElement.remove()" style="position: absolute; top: 5px; right: 10px; background: #ccc; border: none; padding: 5px 8px; cursor: pointer;">×</button>';
        echo '</div>';
    }
}