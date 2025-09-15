<?php
/**
 * Setup Base de Données Analytics NFC
 * Fichier: includes/analytics/analytics-database-setup.php
 * 
 * Création des tables pour le tracking réel des statistiques
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_Analytics_Database {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table principale analytics
        $analytics_table = $wpdb->prefix . 'nfc_analytics';
        
        $sql_analytics = "CREATE TABLE $analytics_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            vcard_id BIGINT(20) UNSIGNED NOT NULL,
            
            -- Informations session
            session_id VARCHAR(32) NOT NULL,
            visitor_ip VARCHAR(45) NOT NULL,
            user_agent TEXT,
            referer_url TEXT,
            
            -- Données géographiques
            country VARCHAR(2),
            region VARCHAR(100),
            city VARCHAR(100),
            latitude DECIMAL(10, 8),
            longitude DECIMAL(11, 8),
            
            -- Informations technique
            device_type ENUM('mobile', 'tablet', 'desktop') DEFAULT 'mobile',
            browser VARCHAR(50),
            os VARCHAR(50),
            screen_resolution VARCHAR(20),
            language VARCHAR(10),
            
            -- Source de trafic
            traffic_source ENUM('direct', 'qr_code', 'nfc_scan', 'social', 'email', 'referral', 'search', 'campaign') NOT NULL DEFAULT 'direct',
            utm_source VARCHAR(100),
            utm_medium VARCHAR(100),
            utm_campaign VARCHAR(100),
            source_detail VARCHAR(255), -- ID du QR, device NFC, etc.
            
            -- Métriques temporelles
            view_datetime DATETIME NOT NULL,
            session_duration INT UNSIGNED DEFAULT 0,
            page_load_time INT UNSIGNED DEFAULT 0,
            
            -- Interactions générales
            actions_count INT UNSIGNED DEFAULT 0,
            scroll_depth TINYINT UNSIGNED DEFAULT 0,
            time_on_page INT UNSIGNED DEFAULT 0,
            
            -- Flags
            is_bounce BOOLEAN DEFAULT TRUE,
            contact_shared BOOLEAN DEFAULT FALSE,
            is_mobile_app BOOLEAN DEFAULT FALSE,
            
            -- Métadonnées
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            INDEX idx_vcard_date (vcard_id, view_datetime),
            INDEX idx_session (session_id),
            INDEX idx_traffic_source (traffic_source),
            INDEX idx_device_type (device_type),
            INDEX idx_country (country),
            INDEX idx_created_date (DATE(created_at)),
            FOREIGN KEY (vcard_id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Table des actions détaillées
        $actions_table = $wpdb->prefix . 'nfc_actions';
        
        $sql_actions = "CREATE TABLE $actions_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            analytics_id BIGINT(20) UNSIGNED NOT NULL,
            vcard_id BIGINT(20) UNSIGNED NOT NULL,
            session_id VARCHAR(32) NOT NULL,
            
            -- Type d'action
            action_type ENUM('view', 'phone_click', 'email_click', 'website_click', 'social_click', 'download_vcard', 'share', 'contact_form', 'calendar_add') NOT NULL,
            action_value VARCHAR(500), -- URL cliquée, numéro appelé, etc.
            action_label VARCHAR(100), -- Label du bouton/lien
            
            -- Contexte de l'action
            element_id VARCHAR(100), -- ID de l'élément HTML
            element_class VARCHAR(200), -- Classes CSS
            element_text VARCHAR(200), -- Texte du lien/bouton
            
            -- Timing et contexte
            action_datetime DATETIME NOT NULL,
            time_since_page_load INT UNSIGNED, -- Millisecondes
            scroll_position TINYINT UNSIGNED DEFAULT 0,
            
            -- Données supplémentaires
            additional_data JSON, -- Données spécifiques selon l'action
            
            -- Métadonnées
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            INDEX idx_analytics (analytics_id),
            INDEX idx_vcard_action (vcard_id, action_type),
            INDEX idx_session_actions (session_id, action_datetime),
            INDEX idx_action_type (action_type),
            INDEX idx_datetime (action_datetime),
            FOREIGN KEY (analytics_id) REFERENCES $analytics_table(id) ON DELETE CASCADE,
            FOREIGN KEY (vcard_id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Table de configuration
        $config_table = $wpdb->prefix . 'nfc_analytics_config';
        
        $sql_config = "CREATE TABLE $config_table (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            config_key VARCHAR(100) NOT NULL UNIQUE,
            config_value TEXT,
            config_type ENUM('string', 'json', 'boolean', 'integer') DEFAULT 'string',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            UNIQUE KEY uk_config_key (config_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_analytics);
        dbDelta($sql_actions);
        dbDelta($sql_config);
        
        // Insérer les configurations par défaut
        self::insert_default_config();
        
        // Vérifier que les tables ont été créées
        if (self::verify_tables_created()) {
            error_log("✅ Tables analytics NFC créées avec succès");
            return true;
        } else {
            error_log("❌ Erreur lors de la création des tables analytics");
            return false;
        }
    }
    
    private static function insert_default_config() {
        global $wpdb;
        
        $config_table = $wpdb->prefix . 'nfc_analytics_config';
        
        $default_configs = [
            [
                'config_key' => 'analytics_enabled',
                'config_value' => '1',
                'config_type' => 'boolean',
                'description' => 'Activer/désactiver le tracking analytics'
            ],
            [
                'config_key' => 'geo_api_service',
                'config_value' => 'ipapi',
                'config_type' => 'string',
                'description' => 'Service de géolocalisation (ipapi, maxmind, none)'
            ],
            [
                'config_key' => 'data_retention_days',
                'config_value' => '395',
                'config_type' => 'integer',
                'description' => 'Durée de conservation des données en jours (13 mois)'
            ],
            [
                'config_key' => 'anonymize_ip',
                'config_value' => '1',
                'config_type' => 'boolean',
                'description' => 'Anonymiser les adresses IP'
            ],
            [
                'config_key' => 'track_user_agent',
                'config_value' => '1',
                'config_type' => 'boolean',
                'description' => 'Enregistrer les User-Agent'
            ],
            [
                'config_key' => 'excluded_ips',
                'config_value' => '["127.0.0.1", "::1"]',
                'config_type' => 'json',
                'description' => 'IPs exclues du tracking'
            ]
        ];
        
        foreach ($default_configs as $config) {
            $wpdb->insert(
                $config_table,
                $config,
                ['%s', '%s', '%s', '%s']
            );
        }
    }
    
    private static function verify_tables_created() {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'nfc_analytics',
            $wpdb->prefix . 'nfc_actions',
            $wpdb->prefix . 'nfc_analytics_config'
        ];
        
        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                return false;
            }
        }
        
        return true;
    }
    
    public static function drop_tables() {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'nfc_actions',
            $wpdb->prefix . 'nfc_analytics',
            $wpdb->prefix . 'nfc_analytics_config'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        error_log("🗑️ Tables analytics supprimées");
    }
    
    /**
     * Nettoyage automatique des anciennes données
     */
    public static function cleanup_old_data() {
        global $wpdb;
        
        $analytics_table = $wpdb->prefix . 'nfc_analytics';
        $actions_table = $wpdb->prefix . 'nfc_actions';
        
        // Récupérer la durée de rétention
        $retention_days = self::get_config('data_retention_days', 395);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        // Supprimer les anciennes données
        $deleted_actions = $wpdb->query($wpdb->prepare(
            "DELETE FROM $actions_table WHERE created_at < %s",
            $cutoff_date
        ));
        
        $deleted_analytics = $wpdb->query($wpdb->prepare(
            "DELETE FROM $analytics_table WHERE created_at < %s",
            $cutoff_date
        ));
        
        error_log("🧹 Cleanup analytics: {$deleted_analytics} vues et {$deleted_actions} actions supprimées");
        
        return [
            'deleted_analytics' => $deleted_analytics,
            'deleted_actions' => $deleted_actions
        ];
    }
    
    /**
     * Récupérer une configuration
     */
    public static function get_config($key, $default = null) {
        global $wpdb;
        
        $config_table = $wpdb->prefix . 'nfc_analytics_config';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT config_value, config_type FROM $config_table WHERE config_key = %s",
            $key
        ));
        
        if (!$result) {
            return $default;
        }
        
        // Conversion selon le type
        switch ($result->config_type) {
            case 'boolean':
                return (bool) $result->config_value;
            case 'integer':
                return (int) $result->config_value;
            case 'json':
                return json_decode($result->config_value, true);
            default:
                return $result->config_value;
        }
    }
    
    /**
     * Mettre à jour une configuration
     */
    public static function update_config($key, $value, $type = 'string') {
        global $wpdb;
        
        $config_table = $wpdb->prefix . 'nfc_analytics_config';
        
        // Convertir la valeur selon le type
        switch ($type) {
            case 'json':
                $value = json_encode($value);
                break;
            case 'boolean':
                $value = $value ? '1' : '0';
                break;
            case 'integer':
                $value = (string) intval($value);
                break;
        }
        
        return $wpdb->replace(
            $config_table,
            [
                'config_key' => $key,
                'config_value' => $value,
                'config_type' => $type
            ],
            ['%s', '%s', '%s']
        );
    }
}

// Fonction d'activation
function nfc_analytics_activate() {
    return NFC_Analytics_Database::create_tables();
}

// Fonction de désactivation
function nfc_analytics_deactivate() {
    // Ne pas supprimer les tables par défaut
    // NFC_Analytics_Database::drop_tables();
}

// Hook de nettoyage quotidien
add_action('nfc_analytics_daily_cleanup', [NFC_Analytics_Database::class, 'cleanup_old_data']);

// Programmer le nettoyage si pas déjà fait
if (!wp_next_scheduled('nfc_analytics_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'nfc_analytics_daily_cleanup');
}