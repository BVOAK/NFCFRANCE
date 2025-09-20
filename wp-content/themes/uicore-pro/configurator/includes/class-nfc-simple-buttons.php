<?php
/**
 * NOUVEAU FICHIER : /configurator/includes/class-nfc-simple-buttons.php
 * Système simple et robuste pour les boutons NFC
 * Remplace class-nfc-button-renderer.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_Simple_Buttons
{
    private $version = '2.0.0';

    public function __construct()
    {
        add_action('init', [$this, 'init']);
    }

    public function init()
    {
        // Enregistrer le shortcode
        add_shortcode('nfc_product_buttons', [$this, 'render_buttons']);
        
        // Enqueue des assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Shortcode principal [nfc_product_buttons]
     * Compatible avec tes theme builders Elementor
     */
    public function render_buttons($atts)
    {
        // Valeurs par défaut
        $atts = shortcode_atts([
            'product_id' => 0,          // 0 = auto-détection
            'layout' => 'vertical',     // vertical|horizontal
            'show_quantity' => 'false', // true|false
        ], $atts);

        // Auto-détection du produit
        $product_id = $this->detect_product_id($atts['product_id']);
        
        if (!$product_id) {
            return $this->debug_output('❌ Produit non détecté');
        }

        // Vérifier si configurable
        if (!$this->is_configurable_product($product_id)) {
            return $this->debug_output("❌ Produit {$product_id} non configurable");
        }

        // Récupérer le produit WooCommerce
        $product = wc_get_product($product_id);
        if (!$product) {
            return $this->debug_output("❌ Produit {$product_id} introuvable");
        }

        // Déterminer le type de boutons à afficher
        $button_type = $this->get_button_type($product_id);

        // Générer le HTML
        return $this->generate_buttons_html($product, $button_type, $atts);
    }

    /**
     * Auto-détection du produit (compatible Elementor)
     */
    private function detect_product_id($provided_id)
    {
        // Si ID fourni
        if ($provided_id > 0) {
            return intval($provided_id);
        }

        // Variable globale (page produit classique)
        global $product;
        if ($product && $product->get_id()) {
            return $product->get_id();
        }

        // Page produit simple
        if (is_product()) {
            return get_the_ID();
        }

        // URL parameter (configurateur)
        if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
            return intval($_GET['product_id']);
        }

        return 0;
    }

    /**
     * Vérifie si un produit est configurable
     * Utilise ta logique existante de class-nfc-product.php
     */
    private function is_configurable_product($product_id)
    {
        // Récupérer la classe existante
        if (class_exists('NFC_Product_Manager')) {
            $nfc_product = new NFC_Product_Manager();
            return $nfc_product->can_be_configured($product_id);
        }

        // Fallback sur les IDs hardcodés si classe manquante
        $configurable_ids = [571, 572, 573];
        return in_array($product_id, $configurable_ids);
    }

    /**
     * Détermine le type de boutons à afficher
     */
    private function get_button_type($product_id)
    {
        // Pour l'instant, tous les produits configurables ont un configurateur
        // Tu peux étendre cette logique selon tes besoins
        return 'configurator';
    }

    /**
     * Génère le HTML des boutons
     */
    private function generate_buttons_html($product, $button_type, $atts)
    {
        $product_id = $product->get_id();
        
        ob_start();
        ?>
        <div class="nfc-simple-buttons" 
             data-product-id="<?php echo esc_attr($product_id); ?>"
             data-layout="<?php echo esc_attr($atts['layout']); ?>">
            
            <?php if ($button_type === 'configurator'): ?>
                <?php echo $this->render_configurator_button($product); ?>
            <?php else: ?>
                <?php echo $this->render_cart_button($product); ?>
            <?php endif; ?>
            
        </div>

        <!-- Script inline pour récupérer les données WooCommerce -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.NFCButtons = window.NFCButtons || {};
            window.NFCButtons.initProduct(<?php echo $product_id; ?>);
        });
        </script>
        <?php
        
        return ob_get_clean();
    }

    /**
     * Bouton "Personnaliser en ligne"
     */
    private function render_configurator_button($product)
    {
        $product_id = $product->get_id();
        $configurator_url = home_url("/configurateur/?product_id={$product_id}");
        
        ob_start();
        ?>
        <a href="<?php echo esc_url($configurator_url); ?>" 
           class="elementor-button elementor-button-link elementor-size-md nfc-configurator-btn"
           data-action="configurator"
           id="nfc-configurator-<?php echo $product_id; ?>">
            
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/brush.svg" 
                 alt="" style="width: 30px; margin-right: 15px;">
            
            <span class="elementor-button-content-wrapper">
                <span class="ui-btn-anim-wrapp d-flex">
                    <span class="elementor-button-text">Personnaliser en ligne</span>
                </span>
            </span>
        </a>
        <?php
        
        return ob_get_clean();
    }

    /**
     * Bouton "Ajouter au panier" (avec fichiers)
     */
    private function render_cart_button($product)
    {
        $product_id = $product->get_id();
        
        ob_start();
        ?>
        <button type="button" 
                class="elementor-button elementor-button-link elementor-size-md nfc-addcart-btn"
                data-action="add-to-cart"
                data-product-id="<?php echo esc_attr($product_id); ?>"
                id="nfc-addcart-<?php echo $product_id; ?>">
            
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/add-to-cart.svg" 
                 alt="" style="width: 30px; margin-right: 15px;">
            
            <span class="elementor-button-content-wrapper">
                <span class="ui-btn-anim-wrapp d-flex">
                    <span class="elementor-button-text">Ajouter au panier</span>
                </span>
            </span>
            
            <!-- Loading state (caché par défaut) -->
            <span class="nfc-loading" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i>
                Ajout en cours...
            </span>
        </button>
        <?php
        
        return ob_get_clean();
    }

    /**
     * Enqueue des assets CSS/JS
     */
    public function enqueue_assets()
    {
        // Uniquement sur les pages qui ont le shortcode
        if (!$this->should_load_assets()) {
            return;
        }

        // CSS
        wp_enqueue_style(
            'nfc-simple-buttons',
            get_template_directory_uri() . '/configurator/assets/css/simple-buttons.css',
            [],
            $this->version
        );

        // JavaScript
        wp_enqueue_script(
            'nfc-simple-buttons',
            get_template_directory_uri() . '/configurator/assets/js/simple-buttons.js',
            ['jquery'],
            $this->version,
            true
        );

        // Configuration JS
        wp_localize_script('nfc-simple-buttons', 'nfcConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nfc_simple_buttons'),
            'cartUrl' => wc_get_cart_url(),
            'homeUrl' => home_url(),
            'i18n' => [
                'adding' => 'Ajout en cours...',
                'added' => 'Ajouté au panier !',
                'error' => 'Erreur lors de l\'ajout',
                'selectVariation' => 'Veuillez sélectionner une variation'
            ]
        ]);
    }

    /**
     * Détermine si on doit charger les assets
     */
    private function should_load_assets()
    {
        global $post;
        
        // Toujours charger sur les pages produits configurables
        if (is_product()) {
            return $this->is_configurable_product(get_the_ID());
        }
        
        // Charger si le shortcode est présent dans le contenu
        if ($post && has_shortcode($post->post_content, 'nfc_product_buttons')) {
            return true;
        }
        
        // Charger sur la page configurateur
        if (is_page_template('page-configurateur.php')) {
            return true;
        }
        
        return false;
    }

    /**
     * Debug output (uniquement si WP_DEBUG activé)
     */
    private function debug_output($message)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return "<!-- NFC Debug: {$message} -->";
        }
        return '';
    }
}

// Initialiser la classe
new NFC_Simple_Buttons();