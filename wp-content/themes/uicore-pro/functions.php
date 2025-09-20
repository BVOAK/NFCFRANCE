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
        //'class-nfc-product-button-manager.php',
        //'class-nfc-button-renderer.php',
        'class-nfc-simple-buttons.php',
        'simple-ajax-handler.php'
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