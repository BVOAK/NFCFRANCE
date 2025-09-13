<?php
/**
 * NFC Enterprise Core - Structure BDD et Fonctions Multi-cartes
 * Fichier: wp-content/plugins/gtmi-vcard/includes/enterprise/enterprise-core.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_Enterprise_Core 
{
    /**
     * Initialisation du système enterprise
     */
    public static function init() 
    {
        add_action('init', [__CLASS__, 'create_database_tables']);
        
        // Hooks pour compatibilité avec système existant (sécurisés)
        add_action('init', function() {
            remove_action('woocommerce_order_status_processing', 'gtmi_vcard_new', 10);
            remove_action('woocommerce_order_status_completed', 'gtmi_vcard_new', 10);
        }, 20);
        
        // ✅ CORRECTION: Nouveau hook unifié (une seule méthode)
        add_action('woocommerce_order_status_processing', [__CLASS__, 'process_order_vcards'], 10);
        add_action('woocommerce_order_status_completed', [__CLASS__, 'process_order_vcards'], 10);
        
        // ✅ AJOUT: Initialiser le système au démarrage de WordPress
        add_action('plugins_loaded', [__CLASS__, 'initialize_system']);
    }

    /**
     * ✅ AJOUT: Initialise le système au bon moment
     */
    public static function initialize_system() 
    {
        // Ne rien faire pour l'instant, juste s'assurer que la classe est prête
        error_log('NFC Enterprise: Système initialisé');
    }

    /**
     * Création des tables BDD pour système enterprise
     */
    public static function create_database_tables() 
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            vcard_id bigint(20) NOT NULL,
            card_position int(11) NOT NULL,
            card_identifier varchar(50) NOT NULL,
            card_status varchar(20) NOT NULL DEFAULT 'pending',
            company_name varchar(255) DEFAULT NULL,
            main_user_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY card_identifier (card_identifier),
            KEY order_id (order_id),
            KEY main_user_id (main_user_id),
            KEY vcard_id (vcard_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Traite les commandes - Détecte si simple ou multi-cartes
     */
    public static function process_order_vcards($order_id) 
    {
        if (!function_exists('wc_get_order')) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // ✅ CORRECTION: Vérifier duplication dans notre table enterprise
        global $wpdb;
        $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
        $existing_enterprise_cards = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE order_id = %d", $order_id
        ));
        
        if ($existing_enterprise_cards > 0) {
            error_log("NFC Enterprise: Order $order_id already processed in enterprise system ($existing_enterprise_cards cards)");
            return;
        }

        // ✅ Si l'ancien système a déjà traité, migrer vers le nouveau
        $existing_vcard = $order->get_meta('_gtmi_vcard_vcard_id');
        if ($existing_vcard) {
            error_log("NFC Enterprise: Order $order_id processed by old system, migrating vCard $existing_vcard");
            self::migrate_legacy_vcard_to_enterprise($order, $existing_vcard);
            return;
        }

        // Analyser les items de la commande
        $nfc_items = self::get_nfc_items_from_order($order);
        
        if (empty($nfc_items)) {
            return;
        }

        $total_cards = 0;
        foreach ($nfc_items as $item) {
            $total_cards += $item['quantity'];
        }

        if ($total_cards === 1) {
            // Commande simple - utiliser système existant étendu
            self::create_single_vcard($order, $nfc_items[0]);
        } else {
            // Commande multiple - nouveau système enterprise
            self::create_enterprise_vcards($order, $nfc_items);
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE: Migre une vCard legacy vers le système enterprise
     */
    private static function migrate_legacy_vcard_to_enterprise($order, $vcard_id) 
    {
        $order_id = $order->get_id();
        
        // Vérifier que la vCard existe
        $vcard = get_post($vcard_id);
        if (!$vcard || $vcard->post_type !== 'virtual_card') {
            error_log("NFC Enterprise: vCard $vcard_id not found for migration");
            return;
        }

        // Générer identifiant enterprise
        $identifier = self::generate_card_identifier($order_id, 1);
        
        // Ajouter à la table enterprise
        $result = self::save_enterprise_card_record([
            'order_id' => $order_id,
            'vcard_id' => $vcard_id,
            'card_position' => 1,
            'card_identifier' => $identifier,
            'card_status' => 'configured', // Legacy est toujours configurée
            'company_name' => $order->get_billing_company(),
            'main_user_id' => $order->get_customer_id() ?: 1
        ]);

        if ($result) {
            // Ajouter métadonnées enterprise
            self::update_vcard_enterprise_meta($vcard_id, [
                'enterprise_order_id' => $order_id,
                'enterprise_position' => 1,
                'card_identifier' => $identifier,
                'is_enterprise_card' => 'yes'
            ]);

            error_log("NFC Enterprise: Legacy vCard $vcard_id migrated successfully with identifier $identifier");
        } else {
            error_log("NFC Enterprise: Failed to migrate legacy vCard $vcard_id");
        }
    }

    /**
     * Extrait les items NFC de la commande
     */
    private static function get_nfc_items_from_order($order) 
    {
        $nfc_items = [];
        
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            
            // Vérifier si c'est un produit NFC (utilise fonction existante)
            if (function_exists('gtmi_vcard_is_nfc_product') && 
                gtmi_vcard_is_nfc_product($product->get_id())) {
                
                $nfc_items[] = [
                    'item_id' => $item_id,
                    'item' => $item,
                    'product' => $product,
                    'quantity' => $item->get_quantity(),
                    'product_name' => $product->get_name()
                ];
            }
        }
        
        return $nfc_items;
    }

    /**
     * Crée une vCard simple (compatibilité avec existant)
     */
    private static function create_single_vcard($order, $nfc_item) 
    {
        // Utiliser la fonction existante mais l'enregistrer aussi dans notre table
        $vcard_id = self::create_vcard_post($order, 1, $nfc_item['product_name']);
        
        if ($vcard_id) {
            // Enregistrer dans table enterprise pour uniformité
            $identifier = self::generate_card_identifier($order->get_id(), 1);
            
            self::save_enterprise_card_record([
                'order_id' => $order->get_id(),
                'vcard_id' => $vcard_id,
                'card_position' => 1,
                'card_identifier' => $identifier,
                'card_status' => 'configured',
                'company_name' => $order->get_billing_company(),
                'main_user_id' => $order->get_customer_id() ?: 1
            ]);

            // Sauvegarder les métadonnées enterprise dans la vCard
            self::update_vcard_enterprise_meta($vcard_id, [
                'enterprise_order_id' => $order->get_id(),
                'enterprise_position' => 1,
                'card_identifier' => $identifier,
                'is_enterprise_card' => 'yes'
            ]);

            // Compatibilité avec existant
            $order->add_meta_data('_gtmi_vcard_vcard_id', $vcard_id, true);
            $order->save();

            error_log("NFC Enterprise: Single vCard $vcard_id created for order " . $order->get_id());
        }
    }

    /**
     * Crée plusieurs vCards pour commande enterprise
     */
    private static function create_enterprise_vcards($order, $nfc_items) 
    {
        $order_id = $order->get_id();
        $created_vcards = [];
        $position = 1;

        foreach ($nfc_items as $nfc_item) {
            $quantity = $nfc_item['quantity'];
            $product_name = $nfc_item['product_name'];

            // Créer autant de vCards que demandé
            for ($i = 1; $i <= $quantity; $i++) {
                $vcard_id = self::create_vcard_post($order, $position, $product_name);
                
                if ($vcard_id) {
                    $identifier = self::generate_card_identifier($order_id, $position);
                    
                    // Enregistrer dans table enterprise
                    self::save_enterprise_card_record([
                        'order_id' => $order_id,
                        'vcard_id' => $vcard_id,
                        'card_position' => $position,
                        'card_identifier' => $identifier,
                        'card_status' => 'pending', // À configurer
                        'company_name' => $order->get_billing_company(),
                        'main_user_id' => $order->get_customer_id() ?: 1
                    ]);

                    // Métadonnées enterprise vCard
                    self::update_vcard_enterprise_meta($vcard_id, [
                        'enterprise_order_id' => $order_id,
                        'enterprise_position' => $position,
                        'card_identifier' => $identifier,
                        'is_enterprise_card' => 'yes'
                    ]);

                    $created_vcards[] = [
                        'vcard_id' => $vcard_id,
                        'identifier' => $identifier,
                        'position' => $position
                    ];

                    error_log("NFC Enterprise: vCard $vcard_id created (position $position, identifier $identifier)");
                }
                
                $position++;
            }
        }

        // Sauvegarder référence commande (pour compatibilité)
        if (!empty($created_vcards)) {
            $first_vcard = $created_vcards[0]['vcard_id'];
            $order->add_meta_data('_gtmi_vcard_vcard_id', $first_vcard, true);
            $order->add_meta_data('_gtmi_enterprise_vcards', $created_vcards, true);
            $order->add_meta_data('_gtmi_enterprise_total_cards', count($created_vcards), true);
            $order->save();

            error_log("NFC Enterprise: " . count($created_vcards) . " vCards created for order $order_id");

            // Déclencher email notification
            do_action('nfc_enterprise_vcards_created', $order_id, $created_vcards);
        }
    }

    /**
     * Crée un post vCard (réutilise logique existante)
     */
    private static function create_vcard_post($order, $position, $product_name) 
    {
        $post_title = sprintf(
            __('Virtual card #%d for %s %s (Order #%d)', 'gtmi_vcard'),
            $position,
            $order->get_billing_first_name(),
            $order->get_billing_last_name(),
            $order->get_id()
        );

        $virtual_card_args = [
            'post_title' => wp_strip_all_tags($post_title),
            'post_status' => 'publish',
            'post_type' => 'virtual_card',
            'post_author' => $order->get_customer_id() ?: 1,
        ];

        $vcard_id = wp_insert_post($virtual_card_args);
        
        if (is_wp_error($vcard_id)) {
            error_log('NFC Enterprise: Error creating vCard - ' . $vcard_id->get_error_message());
            return false;
        }

        // Remplir champs ACF (réutilise fonction existante)
        if (function_exists('gtmi_vcard_fill_acf')) {
            gtmi_vcard_fill_acf($order, $vcard_id);
        }

        return $vcard_id;
    }

    /**
     * Génère identifiant unique pour carte physique
     */
    private static function generate_card_identifier($order_id, $position) 
    {
        return "NFC{$order_id}-{$position}";
    }

    /**
     * Sauvegarde enregistrement dans table enterprise
     */
    private static function save_enterprise_card_record($data) 
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
        
        return $wpdb->insert($table_name, $data);
    }

    /**
     * Met à jour métadonnées enterprise sur vCard
     */
    private static function update_vcard_enterprise_meta($vcard_id, $meta_data) 
    {
        foreach ($meta_data as $key => $value) {
            // Utiliser post_meta classique ET ACF si disponible
            update_post_meta($vcard_id, $key, $value);
            
            if (function_exists('update_field')) {
                update_field($key, $value, $vcard_id);
            }
        }
    }

    /**
     * Récupère toutes les cartes enterprise d'un utilisateur
     */
    public static function get_user_enterprise_cards($user_id) 
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT ec.*, p.post_title, p.post_date
            FROM $table_name ec
            LEFT JOIN {$wpdb->posts} p ON ec.vcard_id = p.ID
            WHERE ec.main_user_id = %d
            ORDER BY ec.created_at DESC, ec.card_position ASC
        ", $user_id), ARRAY_A);

        // Enrichir avec données ACF
        foreach ($results as &$card) {
            if ($card['vcard_id']) {
                $card['vcard_data'] = self::get_vcard_acf_data($card['vcard_id']);
                $card['vcard_url'] = get_permalink($card['vcard_id']);
                $card['stats'] = self::get_vcard_basic_stats($card['vcard_id']);
            }
        }

        return $results;
    }

    /**
     * Récupère données ACF d'une vCard
     */
    private static function get_vcard_acf_data($vcard_id) 
    {
        return [
            'firstname' => get_field('firstname', $vcard_id) ?: '',
            'lastname' => get_field('lastname', $vcard_id) ?: '',
            'society' => get_field('society', $vcard_id) ?: '',
            'service' => get_field('service', $vcard_id) ?: '',
            'email' => get_field('email', $vcard_id) ?: '',
            'mobile' => get_field('mobile', $vcard_id) ?: '',
            'is_configured' => self::is_vcard_configured($vcard_id)
        ];
    }

    /**
     * Vérifie si vCard est configurée (a des données personnalisées)
     */
    private static function is_vcard_configured($vcard_id) 
    {
        $firstname = get_field('firstname', $vcard_id);
        $lastname = get_field('lastname', $vcard_id);
        
        return !empty($firstname) || !empty($lastname);
    }

    /**
     * Récupère stats basiques d'une vCard
     */
    public static function get_vcard_basic_stats($vcard_id) 
    {
        // Utiliser système existant si disponible
        if (function_exists('gtmi_vcard_get_statistics')) {
            return gtmi_vcard_get_statistics($vcard_id);
        }

        // Fallback basique
        $args = [
            'post_type' => 'statistics',
            'meta_query' => [
                [
                    'key' => 'virtual_card_id',
                    'value' => $vcard_id,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => -1
        ];

        $stats_posts = get_posts($args);
        $total_views = 0;
        $total_contacts = 0;

        foreach ($stats_posts as $stat) {
            $event = get_field('event', $stat->ID);
            $value = get_field('value', $stat->ID) ?: 1;

            if ($event === 'view') {
                $total_views += $value;
            } elseif ($event === 'contact') {
                $total_contacts += $value;
            }
        }

        return [
            'views' => $total_views,
            'contacts' => $total_contacts
        ];
    }

    /**
     * Récupère vCard par identifiant physique
     */
    public static function get_vcard_by_identifier($identifier) 
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
        
        $card = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE card_identifier = %s
        ", $identifier), ARRAY_A);

        if ($card && $card['vcard_id']) {
            $card['vcard_data'] = self::get_vcard_acf_data($card['vcard_id']);
            $card['vcard_url'] = get_permalink($card['vcard_id']);
            $card['stats'] = self::get_vcard_basic_stats($card['vcard_id']);
        }

        return $card;
    }

    /**
     * Met à jour statut d'une carte enterprise
     */
    public static function update_card_status($card_identifier, $new_status) 
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'nfc_enterprise_cards';
        
        return $wpdb->update(
            $table_name,
            ['card_status' => $new_status],
            ['card_identifier' => $card_identifier]
        );
    }

    /**
     * Calcule stats globales pour un utilisateur
     */
    public static function get_user_global_stats($user_id) 
    {
        $cards = self::get_user_enterprise_cards($user_id);
        
        $total_views = 0;
        $total_contacts = 0;
        $configured_cards = 0;
        $active_cards = 0;

        foreach ($cards as $card) {
            if (isset($card['stats'])) {
                $total_views += $card['stats']['views'];
                $total_contacts += $card['stats']['contacts'];
            }

            if ($card['vcard_data']['is_configured']) {
                $configured_cards++;
            }

            if ($card['stats']['views'] > 0) {
                $active_cards++;
            }
        }

        return [
            'total_cards' => count($cards),
            'configured_cards' => $configured_cards,
            'active_cards' => $active_cards,
            'total_views' => $total_views,
            'total_contacts' => $total_contacts
        ];
    }
}

// Initialiser le système
NFC_Enterprise_Core::init();