<?php
/**
 * Fancy Product Designer - Module simplifié pour mode standard
 * Version optimisée sans UICore Ajax
 * 
 * @package FPD_Validation
 * @version 2.0.0 - Mode Standard
 */

// Sécurité - Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale FPD Validation - Version simplifiée
 */
class FPD_Validation {
    
    /**
     * Version du module
     */
    const VERSION = '2.0.0';
    
    /**
     * Configuration par défaut
     */
    private $config = array(
        'container_selector' => '#fancy-product-designer-571',
        'button_selector' => '.single_add_to_cart_button',
        'custom_anchor' => '#custom',
        'auto_detect_delay' => 8000, // Réduit à 8 secondes
        'load_on_all_products' => true,
        'standard_form_mode' => true // NOUVEAU: Mode formulaire standard
    );
    
    /**
     * Instance unique (Singleton)
     */
    private static $instance = null;
    
    /**
     * Constructor privé pour Singleton
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialisation du module
     */
    private function init() {
        // Hooks WordPress
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'add_debug_info'));
        add_action('wp_head', array($this, 'preload_assets'));
        add_filter('body_class', array($this, 'add_body_classes'));
        
        // Hook de compatibilité
        add_action('plugins_loaded', array($this, 'check_dependencies'));
        
        // Hooks personnalisés
        add_action('fpd_customization_completed', array($this, 'handle_customization_completed'), 10, 2);
        
        // NOUVEAU: Hooks mode standard
        add_action('wp_footer', array($this, 'add_standard_mode_script'));
    }
    
    /**
     * Enqueue des assets CSS et JS
     */
    public function enqueue_assets() {
        // Charger seulement si nécessaire
        if (!$this->should_load_assets()) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'fpd-validation-styles',
            get_stylesheet_directory_uri() . '/assets/css/fancyproductdesigner.css',
            array(),
            self::VERSION,
            'all'
        );
        
        // JS - Version simplifiée pour mode standard
        wp_enqueue_script(
            'fpd-validation-script',
            get_stylesheet_directory_uri() . '/assets/js/fancyproductdesigner.js',
            array('jquery'),
            self::VERSION,
            true
        );
        
        // Configuration pour le script JS
        wp_localize_script('fpd-validation-script', 'fpdConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'productId' => get_the_ID(),
            'nonce' => wp_create_nonce('fpd_validation_nonce'),
            'messages' => $this->get_messages(),
            'selectors' => $this->get_selectors(),
            'config' => array(
                'autoDetectDelay' => $this->config['auto_detect_delay'],
                'debug' => $this->is_debug_enabled(),
                'standardMode' => true, // NOUVEAU: Indiquer le mode standard
                'disableAjaxCart' => true // NOUVEAU: Désactiver Ajax panier
            )
        ));
    }
    
    /**
     * Script spécifique au mode standard
     */
    public function add_standard_mode_script() {
        if (!$this->should_load_assets()) {
            return;
        }
        
        ?>
        <script>
        // Configuration FPD Mode Standard
        jQuery(document).ready(function($) {
            
            // Marquer le mode standard
            $('body').addClass('fpd-standard-mode');
            
            // Améliorer l'UX du bouton lors de la soumission
            $(document).on('submit', 'form.cart', function() {
                const $form = $(this);
                const $button = $form.find('.single_add_to_cart_button');
                
                // Vérifier si c'est un produit FPD
                if ($('input[name="fpd_product"]').length > 0) {
                    console.log('📝 Soumission formulaire FPD en mode standard');
                    
                    // Ajouter un indicateur de chargement
                    $button.addClass('loading').prop('disabled', true);
                    
                    // Optionnel: Changer le texte temporairement
                    const originalText = $button.find('.elementor-button-text').text();
                    $button.find('.elementor-button-text').text('Ajout en cours...');
                    
                    // Restaurer après 3 secondes (au cas où)
                    setTimeout(function() {
                        $button.removeClass('loading').prop('disabled', false);
                        $button.find('.elementor-button-text').text(originalText);
                    }, 3000);
                }
            });
            
            console.log('✅ FPD Mode Standard initialisé');
        });
        </script>
        <?php
    }
    
    /**
     * Vérifier si on doit charger les assets
     */
    private function should_load_assets() {
        // Ne pas charger sur les pages admin
        if (is_admin()) {
            return false;
        }
        
        // Charger seulement sur les pages produit
        if (!is_product()) {
            return false;
        }
        
        // Vérifier si le produit a FPD activé
        if (!$this->config['load_on_all_products'] && !$this->has_fpd_enabled()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Vérifier si le produit a FPD activé
     */
    public function has_fpd_enabled() {
        global $product;
        
        // S'assurer qu'on a un produit valide
        if (!is_object($product) || !is_a($product, 'WC_Product')) {
            $product = wc_get_product(get_the_ID());
        }
        
        if (!is_object($product)) {
            return false;
        }
        
        // Vérifier les métadonnées FPD
        $fpd_data = $product->get_meta('_fpd_data');
        $fpd_enabled = $product->get_meta('_fpd_enabled');
        
        // Vérifier aussi la présence du container FPD
        $has_container = false;
        if (is_product()) {
            ob_start();
            the_content();
            $content = ob_get_clean();
            $has_container = strpos($content, $this->config['container_selector']) !== false;
        }
        
        return !empty($fpd_data) || !empty($fpd_enabled) || $has_container;
    }
    
    /**
     * Messages traduisibles - Version simplifiée
     */
    private function get_messages() {
        return array(
            'customize' => __('🎨 Personnaliser la carte', 'fpd-validation'),
            'customizing' => __('Personnalisation en cours...', 'fpd-validation'),
            'addToCart' => __('Ajouter au panier', 'fpd-validation'),
            'addToCartCustomized' => __('✅ Valider la personnalisation et ajouter au panier', 'fpd-validation'),
            'helpText' => __('👆 Personnalisez votre carte ci-dessus', 'fpd-validation'),
            'successText' => __('✅ Votre carte est prête !', 'fpd-validation'),
            'standardMode' => __('Mode formulaire standard activé', 'fpd-validation')
        );
    }
    
    /**
     * Sélecteurs CSS configurables
     */
    private function get_selectors() {
        return array(
            'container' => $this->config['container_selector'],
            'button' => $this->config['button_selector'],
            'customAnchor' => $this->config['custom_anchor']
        );
    }
    
    /**
     * Ajouter des classes CSS au body
     */
    public function add_body_classes($classes) {
        if (is_product()) {
            $classes[] = 'fpd-product-page';
            $classes[] = 'fpd-standard-form-mode'; // NOUVEAU
            
            if ($this->has_fpd_enabled()) {
                $classes[] = 'has-fpd-validation';
            }
        }
        return $classes;
    }
    
    /**
     * Précharger les assets pour optimiser les performances
     */
    public function preload_assets() {
        if ($this->should_load_assets()) {
            ?>
            <link rel="preload" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/fancyproductdesigner.css" as="style">
            <link rel="preload" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/fancyproductdesigner.js" as="script">
            <?php
        }
    }
    
    /**
     * Informations de debug - Version simplifiée
     */
    public function add_debug_info() {
        if ($this->should_load_assets() && $this->is_debug_enabled()) {
            ?>
            <script>
            console.group('FPD Validation Debug - Mode Standard');
            console.log('Version:', '<?php echo self::VERSION; ?>');
            console.log('Product ID:', <?php echo get_the_ID(); ?>);
            console.log('Has FPD:', <?php echo $this->has_fpd_enabled() ? 'true' : 'false'; ?>);
            console.log('Standard Mode:', true);
            console.log('Ajax Disabled:', true);
            console.log('Config:', <?php echo json_encode($this->config); ?>);
            console.groupEnd();
            </script>
            <?php
        }
    }
    
    /**
     * Vérifier si le debug est activé
     */
    private function is_debug_enabled() {
        return defined('WP_DEBUG') && WP_DEBUG && current_user_can('administrator');
    }
    
    /**
     * Vérifier les dépendances
     */
    public function check_dependencies() {
        // Vérifier WooCommerce
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'missing_woocommerce_notice'));
        }
        
        // Vérifier jQuery
        if (!wp_script_is('jquery', 'registered')) {
            add_action('admin_notices', array($this, 'missing_jquery_notice'));
        }
    }
    
    /**
     * Notice si WooCommerce manque
     */
    public function missing_woocommerce_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('FPD Validation nécessite WooCommerce pour fonctionner.', 'fpd-validation'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Notice si jQuery manque
     */
    public function missing_jquery_notice() {
        ?>
        <div class="notice notice-warning">
            <p><?php _e('FPD Validation nécessite jQuery pour fonctionner correctement.', 'fpd-validation'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Gérer la completion de personnalisation
     */
    public function handle_customization_completed($product_id, $user_id) {
        // Log ou actions personnalisées après personnalisation
        if ($this->is_debug_enabled()) {
            error_log("FPD Validation (Standard): Personnalisation terminée - Produit: {$product_id}, Utilisateur: {$user_id}");
        }
    }
    
    /**
     * Obtenir la configuration
     */
    public function get_config($key = null) {
        if ($key !== null) {
            return isset($this->config[$key]) ? $this->config[$key] : null;
        }
        return $this->config;
    }
    
    /**
     * Modifier la configuration
     */
    public function set_config($key, $value) {
        $this->config[$key] = $value;
    }
    
    /**
     * Méthodes utilitaires publiques
     */
    
    /**
     * Forcer le rechargement des assets (pour debug)
     */
    public static function force_reload_assets() {
        wp_cache_delete('fpd_validation_version');
        return true;
    }
    
    /**
     * Obtenir les statistiques d'utilisation
     */
    public static function get_usage_stats() {
        return array(
            'version' => self::VERSION,
            'mode' => 'standard',
            'ajax_disabled' => true,
            'products_with_fpd' => 0, // À implémenter
            'customizations_today' => 0, // À implémenter
        );
    }
}

/**
 * Fonctions d'aide globales - Version simplifiée
 */

/**
 * Obtenir l'instance FPD Validation
 */
function fpd_validation() {
    return FPD_Validation::get_instance();
}

/**
 * Vérifier si FPD Validation est actif
 */
function is_fpd_validation_active() {
    return class_exists('FPD_Validation');
}

/**
 * Hook personnalisé pour la completion
 */
function fpd_trigger_customization_completed($product_id, $user_id = null) {
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    
    do_action('fpd_customization_completed', $product_id, $user_id);
}

/**
 * Configuration personnalisée via filter
 */
function fpd_custom_config($config) {
    return apply_filters('fpd_validation_config', $config);
}

/**
 * Initialisation du module
 */
function fpd_validation_init() {
    // Charger les traductions
    load_textdomain('fpd-validation', get_stylesheet_directory() . '/languages/fpd-validation-' . get_locale() . '.mo');
    
    // Initialiser la classe principale
    FPD_Validation::get_instance();
}

// Hook d'initialisation
add_action('init', 'fpd_validation_init');

/**
 * Hooks de désinstallation/nettoyage
 */
register_deactivation_hook(__FILE__, function() {
    wp_cache_flush();
});

/**
 * Compatibilité avec les anciens appels
 */
if (!function_exists('has_fancy_product_designer')) {
    function has_fancy_product_designer() {
        return fpd_validation()->has_fpd_enabled();
    }
}

/**
 * Debug - Raccourcis pour la console (Version standard)
 */
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('wp_footer', function() {
        if (is_product() && current_user_can('administrator')) {
            ?>
            <script>
            // Raccourcis debug globaux pour mode standard
            window.fpdDebug = {
                version: '<?php echo FPD_Validation::VERSION; ?>',
                mode: 'standard',
                reload: function() {
                    location.reload();
                },
                info: function() {
                    console.log('FPD Validation Info (Standard):', <?php echo json_encode(FPD_Validation::get_usage_stats()); ?>);
                },
                testForm: function() {
                    const $form = jQuery('form.cart');
                    const $button = $form.find('.single_add_to_cart_button');
                    console.log('Form method:', $form.attr('method'));
                    console.log('Button type:', $button.attr('type'));
                    console.log('Has FPD field:', jQuery('input[name="fpd_product"]').length > 0);
                }
            };
            console.log('FPD Debug (Standard Mode) disponible via window.fpdDebug');
            </script>
            <?php
        }
    });
}

// Fin du fichier - pas de tag PHP fermant pour éviter les espaces