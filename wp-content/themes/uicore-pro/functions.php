<?php

/**
 * uicore functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package uicore-theme
 */
defined('ABSPATH') || exit;

//Global Constants
define('UICORE_THEME_VERSION', '2.2.1');
define('UICORE_THEME_NAME', 'UiCore Pro');
define('UICORE_FRAMEWORK_VERSION', '6.2.1');

$uicore_includes = array(
    '/setup.php',
    '/default.php',
    '/template-tags.php',
    '/plugin-activation.php'
);

foreach ($uicore_includes as $file) {
    require_once get_template_directory() . '/inc' . $file;
}

//Required
if (!isset($content_width)) {
    $content_width = 1000;
}
if (is_singular() && !class_exists('\UiCore\Core')) {
    wp_enqueue_script("comment-reply");
}


//disable element pack self update
function uicore_disable_plugin_updates($value)
{

    $pluginsToDisable = [
        'bdthemes-element-pack/bdthemes-element-pack.php',
        'metform-pro/metform-pro.php'
    ];

    if (isset($value) && is_object($value)) {
        foreach ($pluginsToDisable as $plugin) {
            if (isset($value->response[$plugin])) {
                unset($value->response[$plugin]);
            }
        }
    }
    return $value;
}
add_filter('site_transient_update_plugins', 'uicore_disable_plugin_updates');


// WooCommerce Styles
function woocommerce_styles()
{
    wp_enqueue_style('custom-styles', get_template_directory_uri() . '/assets/css/custom.css', array(), '1.0', 'all');
}
add_action('wp_enqueue_scripts', 'woocommerce_styles');

function bootstrap_styles()
{
    wp_enqueue_style(
        'bootstrap-css',
        get_template_directory_uri() . '/assets/bootstrap/css/bootstrap.min.css',
        array(),
        '5.3.0'
    );
    wp_enqueue_script(
        'bootstrap-js',
        get_template_directory_uri() . '/assets/bootstrap/js/bootstrap.bundle.min.js',
        array('jquery'),
        '5.3.0',
        true
    );
}

add_action('wp_enqueue_scripts', 'bootstrap_styles');

// Ajouter FontAwesome
function soeasy_enqueue_fontawesome() {
    wp_enqueue_style(
        'fontawesome', 
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css',
        array(),
        '7.0.0'
    );
}
add_action('wp_enqueue_scripts', 'soeasy_enqueue_fontawesome');


/**
 * Initialisation minimale du configurateur NFC
 * À ajouter dans /wp-content/themes/uicore-pro/functions.php
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialisation du configurateur NFC
 */
function nfc_configurator_init()
{
    // Chemin des fichiers du configurateur
    $configurator_path = get_template_directory() . '/configurator/includes/';   

    // Charger tous les fichiers nécessaires
    $files_to_load = [
        'class-nfc-product.php',
        'class-nfc-configurator.php',
        'wc-integration.php',
        'ajax-handlers.php',
        'nfc-file-handler.php',
        'nfc-customer-integration.php',
        'class-nfc-product-button-manager.php',
        'class-nfc-button-renderer.php'
    ];

    foreach ($files_to_load as $file) {
        $file_path = $configurator_path . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
            error_log("NFC: Loaded {$file}");
        } else {
            error_log("NFC: Missing file {$file_path}");
        }
    }

    if (class_exists('NFC_Button_Renderer')) {
        new NFC_Button_Renderer();  // 🆕 MANQUANT !
        error_log('NFC: Button Renderer initialisé');
    }
    
    if (class_exists('NFC_WooCommerce_Integration')) {
        new NFC_WooCommerce_Integration();  // Existant
        error_log('NFC: WC Integration initialisé');  
    }

    error_log('NFC: Configurateur initialisé via fichiers séparés');
}
add_action('after_setup_theme', 'nfc_configurator_init');

/**
 * Créer le produit de base si nécessaire
 */
add_action('init', function () {
    if (isset($_GET['nfc_create_product']) && current_user_can('manage_options')) {
        $product_id = nfc_create_base_product();
        wp_die("Produit créé avec ID: {$product_id}");
    }
});

function nfc_create_base_product()
{
    // Vérifier si le produit existe déjà
    $existing_product = wc_get_product(571);
    if ($existing_product) {
        return 571;
    }

    // Créer l'attribut couleur si n'existe pas
    $attribute_name = 'pa_couleur';

    if (!taxonomy_exists($attribute_name)) {
        $attribute_data = [
            'attribute_label' => 'Couleur',
            'attribute_name' => 'couleur',
            'attribute_type' => 'select',
            'attribute_orderby' => 'menu_order',
            'attribute_public' => 0
        ];

        wc_create_attribute($attribute_data);

        // Créer les termes
        wp_insert_term('Blanc', $attribute_name, ['slug' => 'blanc']);
        wp_insert_term('Noir', $attribute_name, ['slug' => 'noir']);

        error_log('NFC: Attribut couleur créé');
    }

    // Créer le produit variable
    $product = new WC_Product_Variable();
    $product->set_name('Carte NFC Personnalisée');
    $product->set_status('publish');
    $product->set_description('Carte NFC personnalisable avec votre logo et vos informations');
    $product->set_short_description('Créez votre carte NFC unique avec notre configurateur en ligne');
    $product->set_manage_stock(false);
    $product->set_stock_status('instock');

    $product_id = $product->save();

    // Créer les variations
    $variations_data = [
        'blanc' => ['price' => 30.00, 'sku' => 'NFC-CARD-WHITE'],
        'noir' => ['price' => 30.00, 'sku' => 'NFC-CARD-BLACK']
    ];

    foreach ($variations_data as $color => $data) {
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($product_id);
        $variation->set_attributes([$attribute_name => $color]);
        $variation->set_regular_price($data['price']);
        $variation->set_sku($data['sku']);
        $variation->set_manage_stock(false);
        $variation->set_stock_status('instock');
        $variation->save();
    }

    error_log("NFC: Produit variable créé avec ID {$product_id}");
    return $product_id;
}

add_action('rest_api_init', function() {
    error_log('🔍 Test routes REST API');
    
    // Test direct de la fonction
    if (function_exists('gtmi_vcard_register_rest_routes_find_leads')) {
        error_log('✅ Fonction gtmi_vcard_register_rest_routes_find_leads existe');
        gtmi_vcard_register_rest_routes_find_leads();
    } else {
        error_log('❌ Fonction gtmi_vcard_register_rest_routes_find_leads N\'EXISTE PAS');
    }
    
    // Vérifier si la route est enregistrée
    $routes = rest_get_server()->get_routes();
    if (isset($routes['/gtmi_vcard/v1/leads/(?P<vcard_id>\\d+)'])) {
        error_log('✅ Route leads trouvée');
    } else {
        error_log('❌ Route leads NON trouvée');
        error_log('Routes disponibles: ' . print_r(array_keys($routes), true));
    }
});

// URL de test : ?test_nfc_config=571
add_action('init', function() {
    if (isset($_GET['test_nfc_config']) && current_user_can('administrator')) {
        require_once get_template_directory() . '/configurator/includes/class-nfc-product-button-manager.php';
        $manager = new NFC_Product_Button_Manager();
        
        $product_id = intval($_GET['test_nfc_config']);
        $config = $manager->get_product_button_config($product_id);
        
        echo '<pre style="background: #f1f1f1; padding: 20px; margin: 20px;">';
        echo "<h3>Test NFC Config - Produit {$product_id}</h3>";
        echo json_encode($config, JSON_PRETTY_PRINT);
        echo '</pre>';
        
        wp_die();
    }
});

///////////////////////////////////////////////////////////////

/**
 * CODE DE DEBUG POUR LE PANIER - SCREENSHOTS
 * À ajouter temporairement dans functions.php pour déboguer les screenshots du panier
 */

// 🔍 HOOK 1: Debug des données dans le panier 
add_action('woocommerce_cart_loaded_from_session', 'nfc_debug_cart_screenshots');
function nfc_debug_cart_screenshots() {
    if (!WC()->cart->is_empty()) {
        error_log('🔍 === DEBUG PANIER SCREENSHOTS ===');
        
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['nfc_config'])) {
                $config = $cart_item['nfc_config'];
                
                error_log("📦 Item panier: {$cart_item_key}");
                error_log("   - Produit: " . $cart_item['data']->get_name());
                error_log("   - A config NFC: OUI");
                
                // Debug screenshot original
                if (isset($config['screenshot'])) {
                    $original_screenshot = $config['screenshot'];
                    error_log("   - Screenshot original:");
                    error_log("     * Full: " . (isset($original_screenshot['full']) ? strlen($original_screenshot['full']) . ' chars' : 'NON'));
                    error_log("     * Thumbnail: " . (isset($original_screenshot['thumbnail']) ? strlen($original_screenshot['thumbnail']) . ' chars' : 'NON'));
                    error_log("     * Generated_at: " . ($original_screenshot['generated_at'] ?? 'NON'));
                }
                
                // Debug screenshot_base64_data (ajouté par notre correction)
                if (isset($config['screenshot_base64_data'])) {
                    $base64_data = $config['screenshot_base64_data'];
                    error_log("   - Screenshot base64 data:");
                    error_log("     * Full: " . (isset($base64_data['full']) ? strlen($base64_data['full']) . ' chars' : 'NON'));
                    error_log("     * Thumbnail: " . (isset($base64_data['thumbnail']) ? strlen($base64_data['thumbnail']) . ' chars' : 'NON'));
                    error_log("     * Generated_at: " . ($base64_data['generated_at'] ?? 'NON'));
                } else {
                    error_log("   - ❌ PAS de screenshot_base64_data dans config");
                }
                
                // Debug autres données importantes
                error_log("   - Couleur: " . ($config['color'] ?? 'NON'));
                error_log("   - Logo recto: " . (isset($config['image']['name']) ? $config['image']['name'] : 'NON'));
                error_log("   - Logo verso: " . (isset($config['logoVerso']['name']) ? $config['logoVerso']['name'] : 'NON'));
                
                error_log("---");
            }
        }
        error_log('🔍 === FIN DEBUG PANIER ===');
    }
}

// 🔍 HOOK 2: Debug de la thumbnail dans le panier (voir si elle s'affiche)
add_filter('woocommerce_cart_item_thumbnail', 'nfc_debug_cart_thumbnail', 999, 3);
function nfc_debug_cart_thumbnail($thumbnail, $cart_item, $cart_item_key) {
    if (isset($cart_item['nfc_config'])) {
        error_log("🖼️ DEBUG THUMBNAIL pour item: {$cart_item_key}");
        error_log("   - Thumbnail original: " . substr($thumbnail, 0, 100) . "...");
        
        // Vérifier si on a un screenshot thumbnail
        if (isset($cart_item['nfc_config']['screenshot']['thumbnail'])) {
            $screenshot_url = $cart_item['nfc_config']['screenshot']['thumbnail'];
            error_log("   - Screenshot thumbnail trouvé: " . substr($screenshot_url, 0, 100) . "...");
            
            // Créer nouvelle thumbnail avec screenshot
            $new_thumbnail = '<img src="' . esc_attr($screenshot_url) . '" 
                alt="Configuration personnalisée" 
                class="nfc-cart-screenshot" 
                style="max-width:64px;height:auto;border:2px solid #007cba;">';
            
            error_log("   - ✅ Thumbnail remplacée par screenshot");
            return $new_thumbnail;
        } else {
            error_log("   - ❌ Pas de screenshot thumbnail disponible");
        }
    }
    
    return $thumbnail;
}

// 🔍 HOOK 3: Debug des métadonnées affichées dans le panier
add_filter('woocommerce_get_item_data', 'nfc_debug_cart_item_data', 999, 2);
function nfc_debug_cart_item_data($item_data, $cart_item) {
    if (isset($cart_item['nfc_config'])) {
        error_log("📝 DEBUG ITEM DATA pour config NFC");
        error_log("   - Item data actuel: " . print_r($item_data, true));
        
        $config = $cart_item['nfc_config'];
        
        // Ajouter info de debug sur le screenshot
        if (isset($config['screenshot_base64_data'])) {
            $item_data[] = [
                'key' => '🔍 DEBUG Screenshot',
                'value' => 'Base64 data disponible ✅'
            ];
        } elseif (isset($config['screenshot'])) {
            $item_data[] = [
                'key' => '🔍 DEBUG Screenshot',
                'value' => 'Screenshot original seulement ⚠️'
            ];
        } else {
            $item_data[] = [
                'key' => '🔍 DEBUG Screenshot',
                'value' => 'Aucun screenshot ❌'
            ];
        }
        
        // Info de debug sur la config complète
        $item_data[] = [
            'key' => '🔍 DEBUG Config',
            'value' => 'Taille JSON: ' . strlen(json_encode($config)) . ' chars'
        ];
        
        error_log("   - Item data avec debug: " . print_r($item_data, true));
    }
    
    return $item_data;
}

// 🔍 HOOK 4: Debug console JavaScript dans le panier
add_action('wp_footer', 'nfc_add_cart_debug_js');
function nfc_add_cart_debug_js() {
    if (is_cart() || is_checkout()) {
        ?>
        <script>
        console.log('🔍 NFC Cart Debug activé');
        
        // Debug des images dans le panier
        document.addEventListener('DOMContentLoaded', function() {
            const cartThumbnails = document.querySelectorAll('.product-thumbnail img, .nfc-cart-screenshot');
            console.log('🖼️ Thumbnails trouvées dans le panier:', cartThumbnails.length);
            
            cartThumbnails.forEach((img, index) => {
                console.log(`   ${index + 1}. Src:`, img.src);
                console.log(`      Alt:`, img.alt);
                console.log(`      Classes:`, img.className);
                
                // Tester si l'image se charge bien
                img.onload = () => console.log(`   ✅ Image ${index + 1} chargée`);
                img.onerror = () => console.error(`   ❌ Erreur chargement image ${index + 1}`);
            });
            
            // Debug des données meta affichées
            const metaData = document.querySelectorAll('.woocommerce-cart-item .woocommerce-item-meta');
            console.log('📝 Métadonnées trouvées:', metaData.length);
            
            metaData.forEach((meta, index) => {
                console.log(`   Meta ${index + 1}:`, meta.textContent);
            });
        });
        </script>
        <?php
    }
}

// 📋 FONCTION UTILITAIRE: Test manuel des données du panier
function nfc_test_cart_data() {
    if (!WC()->cart->is_empty()) {
        echo "<h3>🔍 DEBUG PANIER NFC</h3>";
        echo "<pre style='background:#f0f0f0;padding:15px;margin:15px 0;font-size:12px;'>";
        
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['nfc_config'])) {
                echo "📦 ITEM: {$cart_item_key}\n";
                echo "   Produit: " . $cart_item['data']->get_name() . "\n";
                
                $config = $cart_item['nfc_config'];
                
                // Test screenshot
                if (isset($config['screenshot'])) {
                    echo "   Screenshot original: ✅\n";
                    echo "     - Full: " . (isset($config['screenshot']['full']) ? 'OUI (' . strlen($config['screenshot']['full']) . ' chars)' : 'NON') . "\n";
                    echo "     - Thumbnail: " . (isset($config['screenshot']['thumbnail']) ? 'OUI (' . strlen($config['screenshot']['thumbnail']) . ' chars)' : 'NON') . "\n";
                }
                
                if (isset($config['screenshot_base64_data'])) {
                    echo "   Screenshot base64_data: ✅\n";
                    echo "     - Full: " . (isset($config['screenshot_base64_data']['full']) ? 'OUI (' . strlen($config['screenshot_base64_data']['full']) . ' chars)' : 'NON') . "\n";
                    echo "     - Thumbnail: " . (isset($config['screenshot_base64_data']['thumbnail']) ? 'OUI (' . strlen($config['screenshot_base64_data']['thumbnail']) . ' chars)' : 'NON') . "\n";
                } else {
                    echo "   Screenshot base64_data: ❌ MANQUANT\n";
                }
                
                echo "   Couleur: " . ($config['color'] ?? 'NON') . "\n";
                echo "   Logo recto: " . (isset($config['image']['name']) ? $config['image']['name'] : 'NON') . "\n";
                echo "   Logo verso: " . (isset($config['logoVerso']['name']) ? $config['logoVerso']['name'] : 'NON') . "\n";
                echo "\n";
            }
        }
        
        echo "</pre>";
    } else {
        echo "<p>🛒 Panier vide</p>";
    }
}

// Pour afficher le debug, ajoute ceci dans ton template de panier ou visite /?nfc_debug_cart=1
if (isset($_GET['nfc_debug_cart'])) {
    add_action('wp_footer', 'nfc_test_cart_data');
}