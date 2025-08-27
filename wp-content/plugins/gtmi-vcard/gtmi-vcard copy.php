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

require_once plugin_dir_path( __FILE__) . 'includes/admin/virtual_card/update.php';
register_activation_hook( __FILE__,  'gtmi_vcard_activation_plugin');
register_deactivation_hook( __FILE__,  'gtmi_vcard_deactivation_plugin');

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

/**
 * À AJOUTER dans le fichier gtmi-vcard.php
 * 
 * Ouvre le fichier gtmi-vcard.php et ajoute ce code À LA FIN, 
 * juste avant la dernière ligne (avant ?> s'il y en a une)
 */

/**
 * NOUVELLES ROUTES API pour vCard individuelle
 */
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
            $fields[$key] = is_array($value) && count($value) === 1 ? $value[0] : $value;
        }
    }
    
    // Ajouter les infos importantes même si elles ont un underscore
    //$fields['order_id'] = get_post_meta($vcard_id, 'order_id', true);
    $fields['order'] = get_field('order', $vcard_id);
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
            echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 10px;'>";
            echo "<h3>vCard #{$vcard->ID} : {$vcard->post_title}</h3>";
            echo "<p><strong>Statut:</strong> {$vcard->post_status}</p>";
            echo "<p><strong>Date:</strong> {$vcard->post_date}</p>";
            
            // Métadonnées
            $meta = get_post_meta($vcard->ID);
            echo "<h4>Métadonnées :</h4>";
            echo "<ul>";
            foreach ($meta as $key => $value) {
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

// UTILISATION: http://nfcfrance.loc/?debug_vcards=1

/**
 * FIX - Sauvegarde Order ID dans virtual_card
 * 
 * Le problème : order_id n'apparaît pas dans l'admin ACF
 * Solution : Vérifier les champs ACF et corriger la sauvegarde
 */

/**
 * ÉTAPE 1 : Vérifier les champs ACF existants
 * Ajoute ça temporairement dans gtmi-vcard.php pour debug
 */
function nfc_debug_acf_virtual_card() {
    if (isset($_GET['debug_acf_virtual_card']) && current_user_can('administrator')) {
        echo "<h2>Debug champs ACF Virtual Card</h2>";
        
        // Vérifier les groupes ACF pour virtual_card
        if (function_exists('acf_get_field_groups')) {
            $groups = acf_get_field_groups(['post_type' => 'virtual_card']);
            
            if (empty($groups)) {
                echo "<p style='color: red;'>❌ Aucun groupe ACF trouvé pour virtual_card</p>";
                echo "<p>Il faut importer le fichier docs/acf/virtual_card.v3.json</p>";
            } else {
                foreach ($groups as $group) {
                    echo "<h3>Groupe: {$group['title']}</h3>";
                    
                    $fields = acf_get_fields($group);
                    if ($fields) {
                        echo "<ul>";
                        foreach ($fields as $field) {
                            echo "<li><strong>{$field['name']}</strong> - {$field['label']} ({$field['type']})</li>";
                        }
                        echo "</ul>";
                    }
                }
            }
        } else {
            echo "<p style='color: red;'>❌ ACF non disponible</p>";
        }
        
        // Vérifier une vCard existante
        $vcards = get_posts(['post_type' => 'virtual_card', 'numberposts' => 1]);
        if (!empty($vcards)) {
            $vcard = $vcards[0];
            echo "<h3>Test vCard #{$vcard->ID}</h3>";
            
            echo "<h4>Champs ACF :</h4>";
            $acf_fields = get_fields($vcard->ID);
            if ($acf_fields) {
                foreach ($acf_fields as $name => $value) {
                    echo "<p><strong>$name:</strong> $value</p>";
                }
            } else {
                echo "<p>Aucun champ ACF</p>";
            }
            
            echo "<h4>Post Meta :</h4>";
            $meta = get_post_meta($vcard->ID);
            foreach ($meta as $key => $value) {
                $val = is_array($value) ? $value[0] : $value;
                echo "<p><strong>$key:</strong> $val</p>";
            }
        }
        
        wp_die();
    }
}
add_action('init', 'nfc_debug_acf_virtual_card');

/**
 * ÉTAPE 2 : Corriger la fonction de création dans after_order.php
 * 
 * Remplace la section de sauvegarde des métadonnées dans gtmi_vcard_create_from_order()
 * par ce code plus robuste :
 */

// Dans after_order.php, remplace la section de métadonnées par :
/*
// NOUVEAU CODE pour la sauvegarde (remplace l'ancien)
error_log("GTMI_VCard: Début sauvegarde métadonnées pour vCard $vcard_id");

// 1. TOUJOURS sauver en post_meta (fiable)
update_post_meta($vcard_id, 'order_id', $order_id);
update_post_meta($vcard_id, 'customer_id', $customer_id);
update_post_meta($vcard_id, 'firstname', $order->get_billing_first_name());
update_post_meta($vcard_id, 'lastname', $order->get_billing_last_name());
update_post_meta($vcard_id, 'email', $order->get_billing_email());
update_post_meta($vcard_id, 'mobile', $order->get_billing_phone());

// Société si présente
$company = $order->get_billing_company();
if ($company) {
    update_post_meta($vcard_id, 'society', $company);
}

// URL unique
$unique_url = gtmi_vcard_generate_unique_url($vcard_id);
update_post_meta($vcard_id, 'unique_url', $unique_url);

// Statut
update_post_meta($vcard_id, 'status', 'active');

error_log("GTMI_VCard: Post meta sauvegardé pour vCard $vcard_id");

// 2. Essayer ACF EN PLUS (si disponible)
if (function_exists('update_field')) {
    $acf_fields = [
        'firstname' => $order->get_billing_first_name(),
        'lastname' => $order->get_billing_last_name(),
        'email' => $order->get_billing_email(),
        'mobile' => $order->get_billing_phone(),
        'society' => $company,
        'order_id' => $order_id,
        'customer_id' => $customer_id,
        'unique_url' => $unique_url,
        'status' => 'active'
    ];
    
    foreach ($acf_fields as $field_name => $field_value) {
        if ($field_value) { // Ne pas sauver les valeurs vides
            $result = update_field($field_name, $field_value, $vcard_id);
            if ($result) {
                error_log("GTMI_VCard: ACF field '$field_name' mis à jour");
            } else {
                error_log("GTMI_VCard: ACF field '$field_name' ÉCHEC (champ n'existe peut-être pas)");
            }
        }
    }
} else {
    error_log("GTMI_VCard: ACF non disponible, utilisation post_meta uniquement");
}

error_log("GTMI_VCard: Fin sauvegarde métadonnées pour vCard $vcard_id");
*/

/**
 * ÉTAPE 3 : Fonction pour corriger les vCards existantes
 * Ajoute ça pour réparer les vCards qui n'ont pas d'order_id
 */
function nfc_fix_existing_vcards() {
    if (isset($_GET['fix_vcards']) && current_user_can('administrator')) {
        echo "<h2>Correction des vCards existantes</h2>";
        
        // Récupérer toutes les vCards sans order_id
        $vcards = get_posts([
            'post_type' => 'virtual_card',
            'numberposts' => -1,
            'meta_query' => [
                [
                    'key' => 'order_id',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ]);
        
        echo "<p>vCards sans order_id trouvées : " . count($vcards) . "</p>";
        
        foreach ($vcards as $vcard) {
            echo "<h3>vCard #{$vcard->ID} : {$vcard->post_title}</h3>";
            
            // Essayer de retrouver la commande par email
            $email = get_post_meta($vcard->ID, 'email', true);
            if (!$email) {
                $email = get_field('email', $vcard->ID); // ACF
            }
            
            if ($email) {
                // Chercher la commande avec cet email
                $orders = wc_get_orders([
                    'billing_email' => $email,
                    'limit' => 5,
                    'orderby' => 'date',
                    'order' => 'DESC'
                ]);
                
                if (!empty($orders)) {
                    $order = $orders[0]; // Prendre la plus récente
                    $order_id = $order->get_id();
                    
                    // Lier la vCard à cette commande
                    update_post_meta($vcard->ID, 'order_id', $order_id);
                    update_post_meta($vcard->ID, 'customer_id', $order->get_customer_id());
                    
                    // Essayer ACF aussi
                    if (function_exists('update_field')) {
                        update_field('order_id', $order_id, $vcard->ID);
                        update_field('customer_id', $order->get_customer_id(), $vcard->ID);
                    }
                    
                    echo "<p style='color: green;'>✅ Liée à la commande #{$order_id}</p>";
                } else {
                    echo "<p style='color: orange;'>⚠️ Aucune commande trouvée pour $email</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Pas d'email pour cette vCard</p>";
            }
        }
        
        wp_die();
    }
}
add_action('init', 'nfc_fix_existing_vcards');

/**
 * ÉTAPE 4 : Hook pour forcer la sauvegarde order_id dans l'admin
 * Quand on édite une vCard dans l'admin, s'assurer que order_id est bien affiché
 */
add_action('acf/save_post', 'nfc_ensure_order_id_visibility', 20);
function nfc_ensure_order_id_visibility($post_id) {
    // Seulement pour les virtual_card
    if (get_post_type($post_id) !== 'virtual_card') {
        return;
    }
    
    $order_id = get_post_meta($post_id, 'order_id', true);
    
    // Si order_id existe en post_meta mais pas en ACF, le copier
    if ($order_id && function_exists('get_field') && function_exists('update_field')) {
        $acf_order_id = get_field('order_id', $post_id);
        
        if (!$acf_order_id) {
            update_field('order_id', $order_id, $post_id);
            error_log("NFC: Copié order_id $order_id vers ACF pour vCard $post_id");
        }
    }
}

/**
 * ÉTAPE 5 : Affichage custom de order_id dans l'admin si ACF ne marche pas
 */
add_action('add_meta_boxes', 'nfc_add_order_info_metabox');
function nfc_add_order_info_metabox() {
    add_meta_box(
        'nfc_order_info',
        'Informations Commande',
        'nfc_order_info_metabox_callback',
        'virtual_card',
        'side',
        'high'
    );
}

function nfc_order_info_metabox_callback($post) {
    $order_id = get_post_meta($post->ID, 'order_id', true);
    $customer_id = get_post_meta($post->ID, 'customer_id', true);
    
    echo "<p><strong>Order ID:</strong> ";
    if ($order_id) {
        $order_url = admin_url("post.php?post=$order_id&action=edit");
        echo "<a href='$order_url' target='_blank'>#$order_id</a>";
    } else {
        echo "<span style='color: red;'>Non défini</span>";
    }
    echo "</p>";
    
    echo "<p><strong>Customer ID:</strong> ";
    if ($customer_id) {
        $user_url = admin_url("user-edit.php?user_id=$customer_id");
        echo "<a href='$user_url' target='_blank'>#$customer_id</a>";
    } else {
        echo "<span style='color: red;'>Non défini</span>";
    }
    echo "</p>";
    
    $email = get_post_meta($post->ID, 'email', true);
    if ($email) {
        echo "<p><strong>Email:</strong> $email</p>";
    }
}