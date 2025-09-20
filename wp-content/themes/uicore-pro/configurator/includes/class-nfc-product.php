<?php
/**
 * Classe de gestion des produits NFC - VERSION CLEAN
 * Focus uniquement sur la gestion des produits et variations
 * UI et intégrations dans wc-integration.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_Product_Manager
{

    // Configuration produits configurables - CENTRALISÉE
    private $configurable_products = [
        571 => [
            'type' => 'card',
            'name' => 'Carte NFC Standard',
            'attribute' => 'pa_couleur',
            'materials' => ['blanc', 'noir'],
            'variation_ids' => [
                'blanc' => 834,
                'noir' => 835
            ]
        ],
        // Prêt pour extension
        // 572 => [
        //     'type' => 'metal',
        //     'name' => 'Carte NFC Métal',
        //     'attribute' => 'pa_finition',
        //     'materials' => ['argent', 'noir']
        // ],
        // 573 => [
        //     'type' => 'wood',
        //     'name' => 'Carte NFC Bois',
        //     'attribute' => 'pa_essence',
        //     'materials' => ['chene', 'bambou']
        // ]
    ];

    public function __construct()
    {
        add_action('init', [$this, 'init']);
    }

    public function init()
    {
        // Vérifier si WooCommerce est actif
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }

        // Hook pour le script de déplacement du bouton
        add_action('wp_enqueue_scripts', [$this, 'enqueue_button_mover_script']);
    }

    /**
     * Vérifie si un produit est configurable
     */
    public function can_be_configured($product_id)
    {
        return isset($this->configurable_products[$product_id]);
    }

    /**
     * Récupère la configuration d'un produit
     */
    public function get_product_config($product_id)
    {
        return $this->configurable_products[$product_id] ?? null;
    }

    /**
     * Récupère tous les produits configurables
     */
    public function get_configurable_products()
    {
        return $this->configurable_products;
    }

    /**
     * Vérifie et retourne les données du produit variable
     */
    public function get_product_data($product_id = null)
    {
        // CORRECTION : Si pas d'ID spécifié, retourner une erreur
        if (!$product_id) {
            return new WP_Error('missing_product_id', 'ID produit requis');
        }

        // CORRECTION : Toujours utiliser le $product_id fourni
        $product = wc_get_product($product_id);

        if (!$product) {
            return new WP_Error('product_not_found', 'Produit ID ' . $product_id . ' non trouvé');
        }

        if (!$product->is_type('variable')) {
            return new WP_Error('not_variable', 'Le produit doit être de type variable');
        }

        if (!$this->can_be_configured($product_id)) {
            return new WP_Error('not_configurable', 'Produit non configurable');
        }

        $config = $this->get_product_config($product_id);

        // DEBUG : Log pour vérifier le produit traité
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("📦 NFC Product Manager - Traitement produit {$product_id}");
            error_log("📦 Config trouvée: " . print_r($config, true));
        }

        $variations_data = $this->get_variations_data($product, $config);

        // DEBUG : Log des variations trouvées
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("📦 Variations générées: " . print_r($variations_data, true));
        }

        return [
            'id' => $product_id,
            'name' => $product->get_name(),
            'type' => $product->get_type(),
            'status' => $product->get_status(),
            'config' => $config,
            'variations' => $variations_data
        ];
    }

    public function debug_product($product_id)
    {
        error_log("🔍 DEBUG Produit {$product_id}:");

        // Vérifier si configurable
        $is_configurable = $this->can_be_configured($product_id);
        error_log("- Configurable: " . ($is_configurable ? 'OUI' : 'NON'));

        if (!$is_configurable) {
            error_log("- Produits configurables autorisés: " . implode(', ', array_keys($this->configurable_products)));
            return;
        }

        // Vérifier le produit WooCommerce
        $product = wc_get_product($product_id);
        if (!$product) {
            error_log("- ❌ Produit WooCommerce introuvable");
            return;
        }

        error_log("- ✅ Produit WC trouvé: " . $product->get_name());
        error_log("- Type: " . $product->get_type());
        error_log("- Status: " . $product->get_status());

        // Vérifier les variations
        if ($product->is_type('variable')) {
            $variations = $product->get_available_variations();
            error_log("- Variations WC: " . count($variations));

            foreach ($variations as $i => $variation) {
                error_log("  Variation {$i}: ID={$variation['variation_id']}, Attributs=" . print_r($variation['attributes'], true));
            }

            // Tester get_variations_data
            $config = $this->get_product_config($product_id);
            $variations_data = $this->get_variations_data($product, $config);
            error_log("- Variations NFC: " . print_r($variations_data, true));
        }
    }

    /**
     * Récupère les données des variations avec matériaux
     */
    private function get_variations_data($product, $config)
    {
        $variations_data = [];
        $variations = $product->get_available_variations();
        $attribute_name = $config['attribute'];

        // NOUVEAU : Si on a des IDs prédéfinis, les utiliser en priorité
        if (isset($config['variation_ids'])) {
            foreach ($config['variation_ids'] as $material => $variation_id) {
                $variation_obj = wc_get_product($variation_id);

                if ($variation_obj && $variation_obj->exists()) {
                    $variations_data[$material] = [
                        'id' => $variation_id,
                        'price' => floatval($variation_obj->get_price()),
                        'price_formatted' => wc_price($variation_obj->get_price()),
                        'sku' => $variation_obj->get_sku() ?: '',
                        'stock_status' => $variation_obj->is_in_stock() ? 'instock' : 'outofstock',
                        'attributes' => $variation_obj->get_variation_attributes(),
                        'material' => $material
                    ];

                    error_log("✅ Variation {$material} configurée avec ID {$variation_id}");
                } else {
                    error_log("❌ Variation ID {$variation_id} pour {$material} non trouvée");
                }
            }

            return $variations_data;
        }

        // FALLBACK : Logique existante si pas d'IDs prédéfinis
        foreach ($variations as $variation) {
            $variation_obj = wc_get_product($variation['variation_id']);

            // Récupérer le matériau de cette variation
            $attributes = $variation_obj->get_variation_attributes();
            $material_slug = '';

            // Chercher l'attribut matériau
            foreach ($attributes as $attr_name => $attr_value) {
                if (
                    strpos($attr_name, str_replace('pa_', '', $attribute_name)) !== false ||
                    $attr_name === $attribute_name
                ) {
                    $material_slug = $attr_value;
                    break;
                }
            }

            if ($material_slug && in_array($material_slug, $config['materials'])) {
                $variations_data[$material_slug] = [
                    'id' => $variation['variation_id'],
                    'price' => floatval($variation['display_price']),
                    'price_formatted' => wc_price($variation['display_price']),
                    'sku' => $variation['sku'] ?: '',
                    'stock_status' => $variation['is_in_stock'] ? 'instock' : 'outofstock',
                    'attributes' => $variation['attributes'],
                    'material' => $material_slug
                ];
            }
        }

        return $variations_data;
    }

    /**
     * Script pour déplacer le bouton configurateur - CLEAN
     */
    public function enqueue_button_mover_script()
    {
        if (!is_product()) {
            return;
        }

        global $post;
        if (!$post || !$this->can_be_configured($post->ID)) {
            return;
        }

        // Script inline minimal pour déplacer le bouton
        /* wp_add_inline_script('jquery', "
            jQuery(document).ready(function($) {
                var productId = '{$post->ID}';
                var buttonMoved = false;

                function moveConfiguratorButton() {
                    if (buttonMoved) return;

                    var configuratorButton = $('.nfc-configurator-button');
                    var addToCartButton = $('.single_add_to_cart_button');

                    if (configuratorButton.length && addToCartButton.length) {
                        addToCartButton.after(configuratorButton);
                        addToCartButton.hide();
                        buttonMoved = true;
                        console.log('✅ Bouton configurateur déplacé');
                    }
                }

                // Essayer immédiatement
                moveConfiguratorButton();

                // Réessayer après variations change
                $(document).on('found_variation', function() {
                    setTimeout(moveConfiguratorButton, 100);
                });
            });
        "); */
    }

    /**
     * Récupère une variation spécifique par matériau
     */
    public function get_variation_by_material($product_id, $material)
    {
        $product = wc_get_product($product_id);
        if (!$product || !$this->can_be_configured($product_id)) {
            return false;
        }

        $config = $this->get_product_config($product_id);
        $variations = $this->get_variations_data($product, $config);

        return isset($variations[$material]) ? $variations[$material] : false;
    }

    /**
     * Récupère tous les produits configurables avec leurs URLs
     */
    public function get_all_configurable_products()
    {
        $products = [];

        foreach ($this->configurable_products as $product_id => $config) {
            $product = wc_get_product($product_id);

            if ($product && $product->exists()) {
                $products[$product_id] = [
                    'id' => $product_id,
                    'name' => $product->get_name(),
                    'config' => $config,
                    'url' => get_permalink($product_id),
                    'configurator_url' => home_url('/configurateur?product_id=' . $product_id)
                ];
            }
        }

        return $products;
    }

    /**
     * Vérifie la structure de TOUS les produits configurables
     */
    public function verify_all_products_structure()
    {
        $results = [];

        foreach ($this->configurable_products as $product_id => $config) {
            $results[$product_id] = $this->verify_product_structure($product_id);
        }

        return $results;
    }

    /**
     * Vérifie la structure d'un produit spécifique
     */
    public function verify_product_structure($product_id)
    {
        if (!$this->can_be_configured($product_id)) {
            return [
                'status' => 'error',
                'message' => 'Produit non configurable'
            ];
        }

        $product_data = $this->get_product_data($product_id);

        if (is_wp_error($product_data)) {
            return [
                'status' => 'error',
                'message' => $product_data->get_error_message()
            ];
        }

        $config = $this->get_product_config($product_id);
        $variations = $product_data['variations'];
        $missing_materials = [];

        foreach ($config['materials'] as $material) {
            if (!isset($variations[$material])) {
                $missing_materials[] = $material;
            }
        }

        if (!empty($missing_materials)) {
            return [
                'status' => 'warning',
                'message' => 'Variations manquantes pour : ' . implode(', ', $missing_materials),
                'data' => $product_data
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Structure du produit correcte',
            'data' => $product_data
        ];
    }

    /**
     * Crée un nouveau produit configurable - HELPER DÉVELOPPEMENT
     */
    public function create_configurable_product($name, $type, $materials, $price = 30.00)
    {
        // Créer l'attribut produit
        $attribute_name = 'pa_' . $type;

        // Vérifier si l'attribut existe
        $attribute_id = wc_attribute_taxonomy_id_by_name($attribute_name);

        if (!$attribute_id) {
            // Créer l'attribut
            $attribute_id = wc_create_attribute([
                'name' => ucfirst($type),
                'slug' => $attribute_name,
                'type' => 'select',
                'order_by' => 'menu_order',
                'has_archives' => false
            ]);
        }

        // Créer les termes
        foreach ($materials as $material) {
            if (!term_exists($material, $attribute_name)) {
                wp_insert_term($material, $attribute_name);
            }
        }

        // Créer le produit variable
        $product = new WC_Product_Variable();
        $product->set_name($name);
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_description('Produit NFC personnalisable via configurateur en ligne.');
        $product->set_short_description('Personnalisez ce produit avec vos couleurs, logo et informations.');

        // Définir l'attribut
        $attributes = [];
        $attribute = new WC_Product_Attribute();
        $attribute->set_id($attribute_id);
        $attribute->set_name($attribute_name);
        $attribute->set_options($materials);
        $attribute->set_visible(true);
        $attribute->set_variation(true);
        $attributes[] = $attribute;

        $product->set_attributes($attributes);
        $product_id = $product->save();

        // Créer les variations
        foreach ($materials as $material) {
            $variation = new WC_Product_Variation();
            $variation->set_parent_id($product_id);
            $variation->set_attributes([$attribute_name => $material]);
            $variation->set_status('publish');
            $variation->set_catalog_visibility('visible');
            $variation->set_regular_price($price);
            $variation->set_price($price);
            $variation->set_manage_stock(false);
            $variation->set_stock_status('instock');
            $variation->set_sku('NFC-' . strtoupper($type) . '-' . strtoupper($material));
            $variation->save();
        }

        return $product_id;
    }

    /**
     * Statistiques des produits configurables
     */
    public function get_configurator_stats()
    {
        global $wpdb;

        $stats = [
            'total_products' => count($this->configurable_products),
            'products' => [],
            'total_orders' => 0
        ];

        foreach ($this->configurable_products as $product_id => $config) {
            // Compter les commandes pour ce produit
            $order_count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(DISTINCT order_id) 
                FROM {$wpdb->prefix}woocommerce_order_items oi
                JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
                WHERE oim.meta_key = '_product_id' AND oim.meta_value = %d
            ", $product_id));

            $stats['products'][$product_id] = [
                'name' => $config['name'],
                'type' => $config['type'],
                'orders' => intval($order_count)
            ];

            $stats['total_orders'] += intval($order_count);
        }

        return $stats;
    }

    /**
     * Notice si WooCommerce n'est pas installé
     */
    public function woocommerce_missing_notice()
    {
        ?>
        <div class="notice notice-error">
            <p><strong>NFC Configurator :</strong> WooCommerce doit être installé et activé pour utiliser le configurateur.</p>
        </div>
        <?php
    }
}

// Initialisation
new NFC_Product_Manager();