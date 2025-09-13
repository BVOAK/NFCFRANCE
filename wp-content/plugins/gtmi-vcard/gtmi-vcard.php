<?php

/**
 * Plugin Name: Virtual Card (VCard)
 * Description: Plugin to manage virtual card, leads and statistics.
 * Version: 1.2.3
 * Author: Glodie Tshimini
 * Requires at least: 6.7
 * Requires PHP: 8.1
 */

defined( 'ABSPATH') or die('Forbidden');

require_once plugin_dir_path(__FILE__) . 'includes/custom-post-type/virtual_card.php';
require_once plugin_dir_path( __FILE__) . 'includes/custom-post-type/lead.php';
require_once plugin_dir_path( __FILE__) . 'includes/custom-post-type/statistics.php';
require_once plugin_dir_path( __FILE__) . 'includes/utils/after_order.php';
require_once plugin_dir_path( __FILE__) . 'includes/utils/api.php';
require_once plugin_dir_path( __FILE__) . 'includes/utils/jwt.php';
require_once plugin_dir_path( __FILE__) . 'includes/utils/exports.php';
require_once plugin_dir_path( __FILE__) . 'includes/utils/unique_url.php';
require_once plugin_dir_path( __FILE__) . 'includes/utils/load_js.php';
require_once plugin_dir_path( __FILE__) . 'includes/utils/load_single_templates.php';
require_once plugin_dir_path( __FILE__) . 'includes/utils/functions.php';
require_once plugin_dir_path( __FILE__) . 'includes/admin/virtual_card/add.php';
require_once plugin_dir_path( __FILE__) . 'includes/admin/virtual_card/find.php';
require_once plugin_dir_path( __FILE__) . 'includes/admin/lead/find.php';
require_once plugin_dir_path( __FILE__) . 'includes/admin/statistics/find.php';
require_once plugin_dir_path( __FILE__) . 'includes/admin/statistics/filter.php';
require_once plugin_dir_path( __FILE__) . 'api/lead/index.php';
require_once plugin_dir_path( __FILE__) . 'api/virtual-card/index.php';
require_once plugin_dir_path( __FILE__) . 'api/statistics/index.php';
require_once plugin_dir_path( __FILE__) . 'includes/dashboard/class-dashboard-manager.php';
require_once plugin_dir_path( __FILE__) . 'includes/dashboard/ajax-handlers.php';
require_once plugin_dir_path( __FILE__) . 'includes/admin/virtual_card/update.php';

// 🆕 Chargement du système Enterprise Multi-cartes
require_once plugin_dir_path(__FILE__) . 'includes/enterprise/enterprise-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/enterprise/enterprise-functions.php';

// Hooks d'activation/désactivation
register_activation_hook( __FILE__,  'gtmi_vcard_activation_plugin');
register_deactivation_hook( __FILE__,  'gtmi_vcard_deactivation_plugin');

// 🆕 Hook d'activation pour créer tables BDD enterprise
register_activation_hook(__FILE__, 'nfc_enterprise_activate');

if (!defined( 'GTMI_VCARD_EMAIL_SENDER')) {
  define( 'GTMI_VCARD_EMAIL_SENDER',  'contact@nfcfrance.com');
}

function gtmi_vcard_activation_plugin(): bool
{
  if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
  }
  return gtmi_vcard_check_required_plugin('woocommerce/woocommerce.php', 'WooCommerce') &&
  gtmi_vcard_check_required_plugin('advanced-custom-fields/acf.php', 'ACF');
}

function gtmi_vcard_check_required_plugin($path, $name): bool
{
  // Check WooCommerce is activated
  if (!is_plugin_active($path)) {
    // deactivate this plugin
    deactivate_plugins(plugin_basename(__FILE__));

    wp_die(
      __("Require $name plugin activated", 'gtmi_vcard'),
      __("Error activation of required plugin $name", 'gtmi_vcard'),
      ['back_link' => true]
    );
    return false;
  }
  return true;
}

// 🆕 FONCTION MANQUANTE - Désactivation du plugin
function gtmi_vcard_deactivation_plugin(): void
{
    // Nettoyage lors de la désactivation
    error_log('GTMI_VCard: Plugin désactivé');
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Tu peux ajouter ici d'autres actions de nettoyage si nécessaire
}

// 🆕 Scripts et styles pour dashboard enterprise
add_action('init', 'nfc_enterprise_dashboard_routing');

function nfc_enterprise_dashboard_routing() {
    if (isset($_GET['dashboard']) || is_page('mon-compte')) {
        add_action('wp_enqueue_scripts', 'nfc_enterprise_dashboard_scripts');
    }
}

function nfc_enterprise_dashboard_scripts() {
    wp_enqueue_style(
        'nfc-dashboard-enterprise',
        plugin_dir_url(__FILE__) . 'assets/css/dashboard-enterprise.css',
        [],
        '1.0.0'
    );
    
    wp_localize_script('jquery', 'nfcEnterprise', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('nfc_enterprise_action'),
        'userId' => get_current_user_id()
    ]);
}

// 🆕 AJAX Handlers enterprise
add_action('wp_ajax_nfc_get_card_data', 'nfc_ajax_get_card_data');
add_action('wp_ajax_nfc_update_card_status', 'nfc_ajax_update_card_status');

function nfc_ajax_get_card_data() {
    check_ajax_referer('nfc_enterprise_action', 'nonce');
    $card_identifier = sanitize_text_field($_POST['card_identifier']);
    $card = NFC_Enterprise_Core::get_vcard_by_identifier($card_identifier);
    
    if ($card) {
        wp_send_json_success($card);
    } else {
        wp_send_json_error('Carte non trouvée');
    }
}

function nfc_ajax_update_card_status() {
    check_ajax_referer('nfc_enterprise_action', 'nonce');
    $card_identifier = sanitize_text_field($_POST['card_identifier']);
    $new_status = sanitize_text_field($_POST['status']);
    $result = NFC_Enterprise_Core::update_card_status($card_identifier, $new_status);
    
    if ($result) {
        wp_send_json_success(['status' => $new_status]);
    } else {
        wp_send_json_error('Erreur lors de la mise à jour');
    }
}

// 🆕 Tests en mode développement
require_once plugin_dir_path(__FILE__) . 'tests/enterprise-test.php';

// Routes API REST vCard (existant)
add_action('rest_api_init', function() {
    error_log('GTMI_VCard: Enregistrement routes REST API vCard');
    
    // Route GET pour récupérer une vCard
    register_rest_route('gtmi_vcard/v1', '/vcard/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'gtmi_vcard_api_get_single',
        'permission_callback' => '__return_true',
        'args' => array(
            'id' => array(
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ),
        ),
    ));
    
    // Route POST pour modifier une vCard
    register_rest_route('gtmi_vcard/v1', '/vcard/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'gtmi_vcard_api_update_single',
        'permission_callback' => '__return_true',
        'args' => array(
            'id' => array(
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ),
        ),
    ));
});

/**
 * Fonction pour récupérer une vCard
 */
function gtmi_vcard_api_get_single($request) {
    $vcard_id = intval($request['id']);
    
    error_log("GTMI_VCard API: Tentative récupération vCard $vcard_id");
    
    // Vérifier que le post existe
    $vcard = get_post($vcard_id);
    
    if (!$vcard) {
        error_log("GTMI_VCard API: Post $vcard_id introuvable");
        return new WP_Error('not_found', 'Post non trouvé', array('status' => 404));
    }
    
    if ($vcard->post_type !== 'virtual_card') {
        error_log("GTMI_VCard API: Post $vcard_id n'est pas une virtual_card (type: {$vcard->post_type})");
        return new WP_Error('wrong_type', 'Ce post n\'est pas une vCard', array('status' => 400));
    }
    
    // Récupérer les métadonnées
    $meta = get_post_meta($vcard_id);
    $fields = array();
    
    // Convertir les meta en format propre
    foreach ($meta as $key => $value) {
        // Enlever les underscores des clés privées WordPress
        if (strpos($key, '_') !== 0) {
            $fields[$key] = is_array($value) && count($value) === 1 ?
                $value[0] : $value;
        }
    }
    
    // Ajouter les infos importantes même si elles ont un underscore
    $fields['order_id'] = get_post_meta($vcard_id, 'order_id', true);
    $fields['customer_id'] = get_post_meta($vcard_id, 'customer_id', true);
    $fields['unique_url'] = get_post_meta($vcard_id, 'unique_url', true);
    
    // Essayer ACF si disponible
    if (function_exists('get_fields')) {
        $acf_fields = get_fields($vcard_id);
        if ($acf_fields) {
            $fields = array_merge($fields, $acf_fields);
        }
    }
    
    $response = array(
        'success' => true,
        'message' => 'vCard récupérée avec succès',
        'data' => array(
            'id' => $vcard_id,
            'title' => $vcard->post_title,
            'slug' => $vcard->post_name,
            'status' => $vcard->post_status,
            'created_at' => $vcard->post_date,
            'updated_at' => $vcard->post_modified,
            'public_url' => get_permalink($vcard_id),
            'fields' => $fields
        )
    );
    
    error_log("GTMI_VCard API: vCard $vcard_id récupérée avec succès");
    
    return rest_ensure_response($response);
}

/**
 * Fonction pour modifier une vCard
 */
function gtmi_vcard_api_update_single($request) {
    $vcard_id = intval($request['id']);
    $params = $request->get_params();
    
    error_log("GTMI_VCard API: Modification vCard $vcard_id");
    
    $vcard = get_post($vcard_id);
    if (!$vcard || $vcard->post_type !== 'virtual_card') {
        return new WP_Error('not_found', 'vCard non trouvée', array('status' => 404));
    }
    
    $updated_fields = array();
    
    foreach ($params as $field_name => $field_value) {
        if (in_array($field_name, ['id', 'route'])) {
            continue;
        }
        
        // Sauvegarder en post_meta
        update_post_meta($vcard_id, $field_name, $field_value);
        $updated_fields[$field_name] = $field_value;
        
        // Essayer ACF aussi si disponible
        if (function_exists('update_field')) {
            update_field($field_name, $field_value, $vcard_id);
        }
    }
    
    return rest_ensure_response(array(
        'success' => true,
        'message' => 'vCard mise à jour',
        'data' => array(
            'id' => $vcard_id,
            'updated_fields' => $updated_fields
        )
    ));
}

/**
 * DEBUG - Vérifier les vCards existantes
 */
function gtmi_vcard_debug_vcards() {
    if (isset($_GET['debug_vcards']) && current_user_can('administrator')) {
        echo "<h2>Debug vCards existantes</h2>";
        
        $vcards = get_posts(array(
            'post_type' => 'virtual_card',
            'numberposts' => 10,
            'post_status' => 'any'
        ));
        
        echo "<p>Nombre de vCards trouvées : " . count($vcards) . "</p>";
        
        foreach ($vcards as $vcard) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
            echo "<h3>vCard #{$vcard->ID}</h3>";
            echo "<p>Titre: {$vcard->post_title}</p>";
            echo "<p>Status: {$vcard->post_status}</p>";
            
            $fields = get_post_meta($vcard->ID);
            echo "<h4>Métadonnées:</h4><ul>";
            foreach ($fields as $key => $value) {
                $val = is_array($value) ? implode(', ', $value) : $value;
                echo "<li><strong>$key:</strong> $val</li>";
            }
            echo "</ul>";
            
            // Test API
            $api_url = home_url("/wp-json/gtmi_vcard/v1/vcard/{$vcard->ID}");
            echo "<p><a href='$api_url' target='_blank'>Tester API pour cette vCard</a></p>";
            
            echo "</div>";
        }
        
        wp_die();
    }
}
add_action('init', 'gtmi_vcard_debug_vcards');