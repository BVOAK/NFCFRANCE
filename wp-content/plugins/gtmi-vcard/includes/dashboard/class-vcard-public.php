<?php
/**
 * Classe pour la gestion des vCards publiques - VERSION CORRIGÉE
 * 
 * Fichier: includes/class-vcard-public.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_VCard_Public
{
    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Nom de la table des contacts partagés
     */
    private static $table_name = null;

    /**
     * Initialisation de la classe
     */
    public static function init()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructeur
     */
    private function __construct()
    {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'nfc_shared_contacts';

        $this->setup_hooks();
        $this->maybe_create_table();
    }

    /**
     * Configuration des hooks WordPress
     */
    private function setup_hooks()
    {
        // Hook d'activation du plugin
        add_action('init', [$this, 'maybe_create_table']);

        // Actions personnalisées
        add_action('nfc_contact_shared', [$this, 'on_contact_shared'], 10, 2);
    }


    /**
     * Envoyer un email de notification au propriétaire de la vCard
     */
    private function send_notification_email($vcard, $lead_id, $first_name, $last_name, $email, $phone, $company) {
        // Récupérer le propriétaire de la vCard
        $owner_id = $vcard->post_author;
        $owner = get_userdata($owner_id);
        
        if (!$owner || !$owner->user_email) {
            error_log('❌ Propriétaire vCard introuvable pour notification');
            return;
        }
        
        // Préparer l'email
        $subject = '📇 Nouveau contact partagé via votre vCard - ' . get_bloginfo('name');
        $message = sprintf(
            "Bonjour,\n\nUn nouveau contact a partagé ses coordonnées via votre carte de visite numérique :\n\n" .
            "👤 %s %s\n📧 %s\n📞 %s\n%s\n" .
            "Vous pouvez consulter ce contact dans votre dashboard : %s\n\n" .
            "Cordialement,\n%s",
            $first_name,
            $last_name,
            $email,
            $phone,
            $company ? "🏢 $company\n" : "",
            admin_url('admin.php?page=nfc-dashboard'),
            get_bloginfo('name')
        );
        
        $sent = wp_mail($owner->user_email, $subject, $message);
        
        if ($sent) {
            error_log("📧 Email de notification envoyé à " . $owner->user_email);
        } else {
            error_log("❌ Échec envoi email de notification à " . $owner->user_email);
        }
    }

    /**
     * Obtenir l'IP du client
     */
    private function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        } else {
            return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
        }
    }

    /**
     * Créer la table des contacts si elle n'existe pas
     */
    public function maybe_create_table() {
        global $wpdb;
        
        $table_name = self::$table_name;
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                vcard_id bigint(20) NOT NULL,
                first_name tinytext NOT NULL,
                last_name tinytext NOT NULL,
                email varchar(100) NOT NULL,
                phone varchar(20) DEFAULT '',
                company tinytext DEFAULT '',
                ip_address varchar(45) DEFAULT '',
                user_agent text DEFAULT '',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY vcard_id (vcard_id),
                KEY email (email),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            
            error_log("✅ Table $table_name créée");
        }
    }

    /**
     * Action personnalisée quand un contact est partagé
     */
    public function on_contact_shared($lead_id, $vcard_id) {
        error_log("🎯 Action personnalisée : contact $lead_id partagé pour vCard $vcard_id");
        
        // Ici tu peux ajouter d'autres actions :
        // - Envoyer à un CRM
        // - Déclencher une automation
        // - Envoyer un webhook
        // - etc.
    }
}

// Initialiser la classe SEULEMENT si elle n'est pas déjà initialisée
if (!class_exists('NFC_VCard_Public') || !NFC_VCard_Public::init()) {
    NFC_VCard_Public::init();
}