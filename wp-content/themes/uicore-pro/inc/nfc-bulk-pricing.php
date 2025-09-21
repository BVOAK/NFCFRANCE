<?php
/**
 * NFC Bulk Pricing System
 * Système de tarification dégressive avec ACF
 * 
 * Usage: Inclure dans functions.php avec require_once
 * Shortcode: [nfc_bulk_pricing] ou [nfc_bulk_pricing product_id="123"]
 * 
 * @version 1.0
 * @author NFC France
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

class NFC_Bulk_Pricing_System {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialisation des hooks WordPress
     */
    private function init_hooks() {
        // Hook principal pour modifier les prix dans le panier
        add_action('woocommerce_before_calculate_totals', [$this, 'apply_bulk_pricing'], 20, 1);
        
        // Shortcode pour afficher le tableau
        add_shortcode('nfc_bulk_pricing', [$this, 'bulk_pricing_shortcode']);
        
        // Support pour les page builders populaires
        add_action('init', [$this, 'register_page_builder_support']);
        
        // Affichage automatique désactivé par défaut (utiliser shortcode)
        // add_action('woocommerce_single_product_summary', [$this, 'auto_display_bulk_table'], 25);
    }
    
    /**
     * Appliquer la tarification dégressive dans le panier
     */
    public function apply_bulk_pricing($cart) {
        if (is_admin() && !defined('DOING_AJAX')) return;
        if (did_action('woocommerce_before_calculate_totals') >= 2) return;
        
        // Grouper les quantités par produit
        $product_quantities = $this->group_cart_quantities($cart);
        
        // Appliquer les prix dégressifs
        foreach ($product_quantities as $key => $data) {
            $this->apply_product_bulk_pricing($cart, $data);
        }
    }
    
    /**
     * Grouper les quantités du panier par produit
     */
    private function group_cart_quantities($cart) {
        $product_quantities = [];
        
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $variation_id = $cart_item['variation_id'];
            
            // Clé unique pour regroupement
            $key = $variation_id ? $variation_id : $product_id;
            
            if (!isset($product_quantities[$key])) {
                $product_quantities[$key] = [
                    'quantity' => 0,
                    'items' => [],
                    'product_id' => $product_id
                ];
            }
            
            $product_quantities[$key]['quantity'] += $cart_item['quantity'];
            $product_quantities[$key]['items'][] = $cart_item_key;
        }
        
        return $product_quantities;
    }
    
    /**
     * Appliquer prix dégressif à un produit spécifique
     */
    private function apply_product_bulk_pricing($cart, $data) {
        $total_qty = $data['quantity'];
        $product_id = $data['product_id'];
        
        // Vérifier si tarification dégressive activée
        if (!get_field('enable_bulk_pricing', $product_id)) return;
        
        // Récupérer les règles ACF
        $rules = get_field('bulk_pricing_rules', $product_id);
        if (!$rules) return;
        
        // Valider et nettoyer les règles
        $rules = $this->validate_rules($rules);
        
        // Trouver la règle applicable
        $applicable_rule = $this->find_applicable_rule($rules, $total_qty);
        if (!$applicable_rule) return;
        
        // Appliquer le nouveau prix
        foreach ($data['items'] as $cart_item_key) {
            $cart_item = $cart->get_cart()[$cart_item_key];
            $product = $cart_item['data'];
            
            // Récupérer le prix avec conversion sécurisée
            $original_price = $this->get_safe_product_price($product);
            
            // Si pas de prix valide, ignorer cet article
            if ($original_price <= 0) continue;
            
            $new_price = $this->calculate_bulk_price($original_price, $applicable_rule);
            
            if ($new_price !== $original_price && $applicable_rule['discount_type'] !== 'quote') {
                $product->set_price($new_price);
                
                // Marquer l'article avec infos de remise
                $cart->cart_contents[$cart_item_key]['nfc_bulk_applied'] = true;
                $cart->cart_contents[$cart_item_key]['nfc_bulk_rule'] = $applicable_rule;
                $cart->cart_contents[$cart_item_key]['nfc_original_price'] = $original_price;
                $cart->cart_contents[$cart_item_key]['nfc_bulk_savings'] = $original_price - $new_price;
            }
        }
    }
    
    /**
     * Trouver la règle applicable selon la quantité
     */
    private function find_applicable_rule($rules, $quantity) {
        $applicable_rule = null;
        $best_min = 0;
        
        foreach ($rules as $rule) {
            // Conversion et validation des valeurs texte
            $min_qty = $this->convert_to_number($rule['min_qty'], 1);
            $max_qty = $this->convert_to_number($rule['max_qty'], 0);
            
            // Vérifier si la quantité correspond
            if ($quantity >= $min_qty && $min_qty >= $best_min) {
                if ($max_qty == 0 || $quantity <= $max_qty) {
                    $applicable_rule = $rule;
                    $best_min = $min_qty;
                }
            }
        }
        
        return $applicable_rule;
    }
    
    /**
     * Calculer le nouveau prix selon la règle
     */
    private function calculate_bulk_price($original_price, $rule) {
        // Convertir le prix original en float pour éviter les erreurs de type
        $original_price = $this->convert_to_number($original_price, 0);
        
        // Si le prix est 0 ou négatif, retourner tel quel
        if ($original_price <= 0) {
            return $original_price;
        }
        
        $discount_type = sanitize_text_field($rule['discount_type'] ?? 'percentage');
        $discount_value = $this->convert_to_number($rule['discount_value'], 0);
        
        switch ($discount_type) {
            case 'percentage':
                // Limiter entre 0 et 100%
                $discount_value = max(0, min(100, $discount_value));
                return $original_price * (1 - ($discount_value / 100));
                
            case 'fixed':
                return max(0, $original_price - $discount_value);
                
            case 'fixed_price':
                return max(0, $discount_value);
                
            case 'quote':
                return $original_price; // Prix original, mais marqué "sur devis"
                
            default:
                return $original_price;
        }
    }
    
    /**
     * Shortcode pour afficher le tableau de prix dégressifs
     */
    public function bulk_pricing_shortcode($atts) {
        $atts = shortcode_atts([
            'show_title' => 'true',
            'title' => 'Tarifs dégressifs',
            'class' => 'nfc-bulk-pricing-table'
        ], $atts, 'nfc_bulk_pricing');
        
        // Déterminer l'ID du produit automatiquement
        $product_id = $this->get_current_product_id();
        
        if (!$product_id) return '';
        
        // Vérifier si tarification dégressive activée
        if (!get_field('enable_bulk_pricing', $product_id)) return '';
        
        $rules = get_field('bulk_pricing_rules', $product_id);
        if (!$rules) return '';
        
        // Valider et nettoyer les règles
        $rules = $this->validate_rules($rules);
        if (empty($rules)) return '';
        
        $product = wc_get_product($product_id);
        if (!$product) return '';
        
        $base_price = $this->get_safe_product_price($product);
        $note = get_field('bulk_pricing_note', $product_id);
        
        // Générer le HTML
        ob_start();
        ?>
        <div class="<?php echo esc_attr($atts['class']); ?>">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h4 class="nfc-bulk-title">
                    <i class="fas fa-chart-line"></i> 
                    <?php echo esc_html($atts['title']); ?>
                </h4>
            <?php endif; ?>
            
            <table class="nfc-bulk-table">
                <thead>
                    <tr>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Économie</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Ligne prix normal -->
                    <tr class="base-price">
                        <td>1<?php echo count($rules) > 0 ? ' - ' . ($rules[0]['min_qty'] - 1) : ''; ?></td>
                        <td><?php echo wc_price($base_price); ?></td>
                        <td>-</td>
                    </tr>
                    
                    <?php foreach ($rules as $rule): 
                        // Les valeurs sont déjà validées et converties
                        $min = $rule['min_qty'];
                        $max = $rule['max_qty'];
                        
                        // Format d'affichage de la quantité
                        if ($max == 0) {
                            $range = $min . '+';
                        } else {
                            $range = $min . ' - ' . $max;
                        }
                        
                        // Traitement selon le type
                        if ($rule['discount_type'] === 'quote'):
                            $quote_text = $rule['quote_text'];
                    ?>
                        <tr class="bulk-tier quote-tier">
                            <td><strong><?php echo $range; ?></strong></td>
                            <td><strong class="text-info"><?php echo esc_html($quote_text); ?></strong></td>
                            <td><em>Contactez-nous</em></td>
                        </tr>
                    <?php else:
                            $new_price = $this->calculate_bulk_price($base_price, $rule);
                            $savings = $base_price - $new_price;
                            $savings_percent = $base_price > 0 ? ($savings / $base_price) * 100 : 0;
                    ?>
                        <tr class="bulk-tier">
                            <td><strong><?php echo $range; ?></strong></td>
                            <td><strong class="discounted-price"><?php echo wc_price($new_price); ?></strong></td>
                            <td class="savings">
                                <strong>-<?php echo number_format($savings_percent, 0); ?>%</strong>
                                <small>(<?php echo wc_price($savings); ?>)</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($note): ?>
                <div class="nfc-bulk-note">
                    <?php echo wp_kses_post($note); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Détecter l'ID du produit courant selon le contexte
     */
    private function get_current_product_id() {
        global $post, $product;
        
        // 1. Priorité au produit WooCommerce global (page produit)
        if (is_object($product) && method_exists($product, 'get_id')) {
            return $product->get_id();
        }
        
        // 2. Si on est sur une page produit (single product)
        if (is_product() && $post) {
            return $post->ID;
        }
        
        // 3. Post courant (pour les page builders)
        if ($post && $post->post_type === 'product') {
            return $post->ID;
        }
        
        // 4. Via la query string (Elementor, Divi, etc.)
        if (isset($_GET['product_id'])) {
            return intval($_GET['product_id']);
        }
        
        // 5. Via l'ID de la page courante
        $current_id = get_the_ID();
        if ($current_id && get_post_type($current_id) === 'product') {
            return $current_id;
        }
        
        // 6. Dernier recours: chercher via le slug dans l'URL
        if (get_query_var('product')) {
            $product_post = get_page_by_path(get_query_var('product'), OBJECT, 'product');
            if ($product_post) {
                return $product_post->ID;
            }
        }
        
        return null;
    }
    
    /**
     * Convertir une valeur texte en nombre avec validation
     */
    private function convert_to_number($value, $default = 0) {
        // Si c'est déjà un nombre, le retourner directement
        if (is_numeric($value)) {
            return floatval($value);
        }
        
        // Si c'est un objet ou array, retourner défaut
        if (is_object($value) || is_array($value)) {
            return $default;
        }
        
        // Nettoyer la valeur
        $value = trim(strval($value));
        
        // Si vide, retourner la valeur par défaut
        if (empty($value)) {
            return $default;
        }
        
        // Remplacer virgule par point pour les décimaux
        $value = str_replace(',', '.', $value);
        
        // Supprimer tout ce qui n'est pas chiffre, point ou tiret
        $value = preg_replace('/[^0-9.\-]/', '', $value);
        
        // Si après nettoyage c'est vide, retourner défaut
        if (empty($value)) {
            return $default;
        }
        
        // Convertir en float
        $number = floatval($value);
        
        // S'assurer que c'est positif pour les quantités si défaut >= 0
        if ($default >= 0) {
            $number = max(0, $number);
        }
        
        return $number;
    }
    
    /**
     * Valider et nettoyer les règles ACF
     */
    private function validate_rules($rules) {
        if (!is_array($rules)) {
            return [];
        }
        
        $validated_rules = [];
        
        foreach ($rules as $rule) {
            // Vérifier que la règle est valide
            if (!isset($rule['min_qty']) || !isset($rule['discount_type'])) {
                continue;
            }
            
            $min_qty = $this->convert_to_number($rule['min_qty'], 1);
            
            // Ignorer les règles avec quantité minimum < 1
            if ($min_qty < 1) {
                continue;
            }
            
            $validated_rule = [
                'min_qty' => $min_qty,
                'max_qty' => $this->convert_to_number($rule['max_qty'], 0),
                'discount_type' => sanitize_text_field($rule['discount_type']),
                'discount_value' => $this->convert_to_number($rule['discount_value'], 0),
                'quote_text' => sanitize_text_field($rule['quote_text'] ?? 'Sur devis')
            ];
            
            $validated_rules[] = $validated_rule;
        }
        
        // Trier par quantité minimum croissante
        usort($validated_rules, function($a, $b) {
            return $a['min_qty'] - $b['min_qty'];
        });
        
        return $validated_rules;
    }
    
    /**
     * Récupérer le prix d'un produit de manière sécurisée
     */
    private function get_safe_product_price($product) {
        if (!$product || !is_object($product)) {
            return 0;
        }
        
        // Essayer différentes méthodes pour récupérer le prix
        $price = 0;
        
        // Méthode 1: Prix régulier
        if (method_exists($product, 'get_regular_price')) {
            $price = $product->get_regular_price();
        }
        
        // Méthode 2: Prix actuel si pas de prix régulier
        if (empty($price) && method_exists($product, 'get_price')) {
            $price = $product->get_price();
        }
        
        // Méthode 3: Prix via meta (fallback)
        if (empty($price)) {
            $price = get_post_meta($product->get_id(), '_regular_price', true);
        }
        
        // Convertir en nombre de manière sécurisée
        return $this->convert_to_number($price, 0);
    }
    
    /**
     * Support pour les page builders populaires
     */
    public function register_page_builder_support() {
        // Elementor support
        if (class_exists('Elementor\Plugin')) {
            add_action('elementor/widgets/widgets_registered', [$this, 'register_elementor_widget']);
        }
        
        // Divi support via shortcode (déjà supporté)
        
        // Beaver Builder support via shortcode (déjà supporté)
        
        // Visual Composer support via shortcode (déjà supporté)
    }
    
    /**
     * Widget Elementor (optionnel)
     */
    public function register_elementor_widget() {
        // Si tu veux créer un widget Elementor dédié plus tard
        // Pour l'instant le shortcode suffit
    }
    
    /**
     * Affichage automatique sur les pages produit
     */
    public function auto_display_bulk_table() {
        // Désactiver l'affichage automatique par défaut
        // Utiliser le shortcode dans le page builder à la place
        return;
        
        // Si tu veux réactiver l'affichage auto, décommenter:
        // echo $this->bulk_pricing_shortcode([]);
    }
}

// Initialiser le système
new NFC_Bulk_Pricing_System();

/**
 * Fonctions utilitaires publiques
 */

/**
 * Récupérer les règles de prix dégressifs d'un produit
 */
function nfc_get_bulk_pricing_rules($product_id) {
    if (!get_field('enable_bulk_pricing', $product_id)) {
        return false;
    }
    
    $rules = get_field('bulk_pricing_rules', $product_id);
    if (!$rules) return false;
    
    // Utiliser la méthode de validation de la classe
    $bulk_system = new NFC_Bulk_Pricing_System();
    return $bulk_system->get_validated_rules($rules);
}

/**
 * Vérifier si un produit a des prix dégressifs
 */
function nfc_has_bulk_pricing($product_id) {
    return get_field('enable_bulk_pricing', $product_id) && get_field('bulk_pricing_rules', $product_id);
}

/**
 * Calculer le prix pour une quantité donnée
 */
function nfc_get_bulk_price($product_id = null, $quantity = 1) {
    // Auto-détection du produit si non fourni
    if (!$product_id) {
        $bulk_system = new NFC_Bulk_Pricing_System();
        $product_id = $bulk_system->get_current_product_id();
    }
    
    if (!$product_id) return false;
    
    $rules = nfc_get_bulk_pricing_rules($product_id);
    if (!$rules) return false;
    
    $product = wc_get_product($product_id);
    if (!$product) return false;
    
    $bulk_system = new NFC_Bulk_Pricing_System();
    $original_price = $bulk_system->get_product_price_safe($product);
    $applicable_rule = $bulk_system->find_applicable_rule($rules, $quantity);
    
    if (!$applicable_rule) return $original_price;
    
    return $bulk_system->calculate_bulk_price($original_price, $applicable_rule);
}