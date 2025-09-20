<?php
/**
 * NOUVEAU FICHIER : /configurator/includes/simple-ajax-handler.php
 * Handler Ajax simple pour les nouveaux boutons NFC
 * À ajouter dans ton wc-integration.php existant
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajouter cette fonction dans ta classe NFC_WooCommerce_Integration
 * ou créer un nouveau handler simple
 */
class NFC_Simple_Ajax_Handler
{
    public function __construct()
    {
        add_action('init', [$this, 'register_handlers']);
    }

    public function register_handlers()
    {
        // Handler pour ajout au panier simple
        add_action('wp_ajax_nfc_add_to_cart_simple', [$this, 'add_to_cart_simple']);
        add_action('wp_ajax_nopriv_nfc_add_to_cart_simple', [$this, 'add_to_cart_simple']);
    }

    /**
     * Handler Ajax pour ajout au panier simple
     * Version allégée et robuste
     */
    public function add_to_cart_simple()
    {
        // Log de début
        error_log('🚀 NFC Simple: Début ajout panier');
        
        try {
            // 1. SÉCURITÉ ET VALIDATION
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'nfc_simple_buttons')) {
                throw new Exception('Nonce invalide');
            }

            $product_id = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            $variation_id = intval($_POST['variation_id'] ?? 0);

            if (!$product_id) {
                throw new Exception('ID produit manquant');
            }

            if ($quantity < 1) {
                throw new Exception('Quantité invalide');
            }

            // 2. VÉRIFICATIONS PRODUIT
            $product = wc_get_product($product_id);
            if (!$product) {
                throw new Exception('Produit introuvable');
            }

            // 3. GESTION DES VARIATIONS
            $variation_attributes = [];
            
            if ($product->is_type('variable')) {
                if (!$variation_id) {
                    throw new Exception('Variation requise pour ce produit');
                }
                
                $variation = wc_get_product($variation_id);
                if (!$variation || !$variation->exists()) {
                    throw new Exception('Variation introuvable');
                }

                if (!$variation->is_in_stock()) {
                    throw new Exception('Cette variation n\'est pas en stock');
                }

                // Récupérer les attributs de variation depuis le POST
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'attribute_') === 0) {
                        $variation_attributes[$key] = sanitize_text_field($value);
                    }
                }

                error_log('🔍 Variation détectée: ' . $variation_id . ' avec attributs: ' . print_r($variation_attributes, true));
            }

            // 4. VÉRIFICATION STOCK
            if (!$product->is_in_stock()) {
                throw new Exception('Produit en rupture de stock');
            }

            // 5. INITIALISER WOOCOMMERCE
            if (!WC()->cart) {
                WC()->cart = new WC_Cart();
            }

            // 6. MÉTADONNÉES PERSONNALISÉES
            $cart_item_data = [
                'nfc_simple_button' => true,
                'nfc_added_at' => current_time('mysql'),
                'nfc_requires_files' => true // Pour les produits NFC
            ];

            // 7. AJOUT AU PANIER
            $cart_item_key = WC()->cart->add_to_cart(
                $product_id,
                $quantity,
                $variation_id,
                $variation_attributes,
                $cart_item_data
            );

            if (!$cart_item_key) {
                throw new Exception('Échec de l\'ajout au panier');
            }

            error_log('✅ NFC Simple: Produit ajouté avec succès - Key: ' . $cart_item_key);

            // 8. RÉPONSE DE SUCCÈS
            wp_send_json_success([
                'message' => 'Produit ajouté au panier avec succès !',
                'cart_item_key' => $cart_item_key,
                'cart_url' => wc_get_cart_url(),
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'product_name' => $product->get_name(),
                'variation_name' => isset($variation) ? $variation->get_name() : null,
                'quantity' => $quantity,
                'total_price' => WC()->cart->get_cart_total()
            ]);

        } catch (Exception $e) {
            error_log('❌ NFC Simple: Erreur ajout panier - ' . $e->getMessage());
            
            wp_send_json_error([
                'message' => $e->getMessage(),
                'error_code' => 'add_to_cart_failed'
            ]);
        }
    }

    /**
     * Validation des données reçues
     */
    private function validate_cart_data($data)
    {
        $required_fields = ['product_id', 'quantity'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Champ requis manquant: {$field}");
            }
        }

        // Validation de la quantité
        if (!is_numeric($data['quantity']) || $data['quantity'] < 1) {
            throw new Exception('Quantité invalide');
        }

        // Validation de l'ID produit
        if (!is_numeric($data['product_id']) || $data['product_id'] < 1) {
            throw new Exception('ID produit invalide');
        }

        return true;
    }

    /**
     * Récupère les attributs de variation depuis les données POST
     */
    private function extract_variation_attributes($post_data)
    {
        $attributes = [];
        
        foreach ($post_data as $key => $value) {
            if (strpos($key, 'attribute_') === 0 && !empty($value)) {
                $attributes[$key] = sanitize_text_field($value);
            }
        }
        
        return $attributes;
    }

    /**
     * Valide qu'une variation est compatible avec les attributs
     */
    private function validate_variation_attributes($variation_id, $attributes)
    {
        $variation = wc_get_product($variation_id);
        if (!$variation) {
            return false;
        }

        $variation_attributes = $variation->get_variation_attributes();
        
        foreach ($variation_attributes as $attr_name => $attr_value) {
            $post_attr_name = 'attribute_' . str_replace('attribute_', '', $attr_name);
            
            if (isset($attributes[$post_attr_name])) {
                if ($attr_value !== '' && $attributes[$post_attr_name] !== $attr_value) {
                    return false;
                }
            }
        }
        
        return true;
    }
}

// Initialiser le handler
new NFC_Simple_Ajax_Handler();

/**
 * ALTERNATIVE : Si tu préfères ajouter directement dans wc-integration.php
 * Ajoute simplement cette méthode à ta classe NFC_WooCommerce_Integration existante :
 */

/*
public function ajax_add_to_cart_simple()
{
    // Copier le contenu de add_to_cart_simple() ci-dessus
    // dans ta classe NFC_WooCommerce_Integration existante
    
    // Puis ajouter dans ton init() :
    // add_action('wp_ajax_nfc_add_to_cart_simple', [$this, 'ajax_add_to_cart_simple']);
    // add_action('wp_ajax_nopriv_nfc_add_to_cart_simple', [$this, 'ajax_add_to_cart_simple']);
}
*/