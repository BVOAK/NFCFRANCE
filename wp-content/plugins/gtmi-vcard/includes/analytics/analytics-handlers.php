<?php
/**
 * Handlers AJAX pour le système d'analytics NFC
 * Fichier: includes/analytics/analytics-handlers.php
 * 
 * Gestion backend du tracking en temps réel
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_Analytics_Handler {
    
    private $geo_cache = [];
    
    public function __construct() {
        $this->register_hooks();
    }
    
    private function register_hooks() {
        // Hooks pour utilisateurs connectés et non connectés
        add_action('wp_ajax_nfc_track_view', [$this, 'track_view']);
        add_action('wp_ajax_nopriv_nfc_track_view', [$this, 'track_view']);
        
        add_action('wp_ajax_nfc_track_action', [$this, 'track_action']);
        add_action('wp_ajax_nopriv_nfc_track_action', [$this, 'track_action']);
        
        add_action('wp_ajax_nfc_update_session', [$this, 'update_session']);
        add_action('wp_ajax_nopriv_nfc_update_session', [$this, 'update_session']);
        
        add_action('wp_ajax_nfc_end_session', [$this, 'end_session']);
        add_action('wp_ajax_nopriv_nfc_end_session', [$this, 'end_session']);
    }
    
    /**
     * Tracking d'une vue de page
     */
public function track_view() {
    error_log("🧪 DEBUG 1: Début track_view");
    
    try {
        // Vérification basique
        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        error_log("🧪 DEBUG 2: vcard_id=$vcard_id, session_id=$session_id");
        
        if (!$vcard_id || !$session_id) {
            error_log("🧪 DEBUG 3: Données manquantes");
            wp_send_json_error(['message' => 'Données manquantes']);
            return;
        }
        
        // Vérifier que la vCard existe
        $post = get_post($vcard_id);
        error_log("🧪 DEBUG 4: Post trouvé: " . ($post ? "OUI" : "NON"));
        
        if (!$post || get_post_type($vcard_id) !== 'virtual_card') {
            error_log("🧪 DEBUG 5: vCard non trouvée ou mauvais type");
            wp_send_json_error(['message' => 'vCard non trouvée']);
            return;
        }
        
        // Test base de données
        global $wpdb;
        $analytics_table = $wpdb->prefix . 'nfc_analytics';
        error_log("🧪 DEBUG 6: Table: $analytics_table");
        
        // Test simple d'insertion
        $test_data = [
            'vcard_id' => $vcard_id,
            'session_id' => $session_id,
            'visitor_ip' => '127.0.0.1',
            'traffic_source' => 'direct',
            'device_type' => 'desktop',
            'view_datetime' => current_time('mysql'),
            'created_at' => current_time('mysql')
        ];
        
        error_log("🧪 DEBUG 7: Tentative d'insertion avec données: " . print_r($test_data, true));
        
        $result = $wpdb->insert($analytics_table, $test_data);
        
        error_log("🧪 DEBUG 8: Résultat insertion: " . ($result ? "SUCCESS" : "FAILED"));
        error_log("🧪 DEBUG 9: Erreur SQL éventuelle: " . $wpdb->last_error);
        error_log("🧪 DEBUG 10: Insert ID: " . $wpdb->insert_id);
        
        if ($result === false) {
            error_log("❌ Erreur insertion analytics: " . $wpdb->last_error);
            wp_send_json_error(['message' => 'Erreur base de données: ' . $wpdb->last_error]);
            return;
        }
        
        wp_send_json_success([
            'analytics_id' => $wpdb->insert_id,
            'message' => 'Vue enregistrée'
        ]);
        
    } catch (Exception $e) {
        error_log("❌ Exception track_view: " . $e->getMessage());
        wp_send_json_error(['message' => 'Erreur serveur: ' . $e->getMessage()]);
    }
}
    
    /**
     * Tracking d'une action utilisateur
     */
    public function track_action() {
        try {
            $analytics_id = intval($_POST['analytics_id'] ?? 0);
            $vcard_id = intval($_POST['vcard_id'] ?? 0);
            $session_id = sanitize_text_field($_POST['session_id'] ?? '');
            $action_type = sanitize_text_field($_POST['action_type'] ?? '');
            
            if (!$analytics_id || !$vcard_id || !$action_type) {
                wp_send_json_error(['message' => 'Données manquantes']);
                return;
            }
            
            global $wpdb;
            $actions_table = $wpdb->prefix . 'nfc_actions';
            
            $action_data = [
                'analytics_id' => $analytics_id,
                'vcard_id' => $vcard_id,
                'session_id' => $session_id,
                'action_type' => $action_type,
                'action_value' => substr(sanitize_text_field($_POST['action_value'] ?? ''), 0, 500),
                'time_since_page_load' => intval($_POST['time_since_page_load'] ?? 0),
                'scroll_position' => intval($_POST['scroll_depth'] ?? 0),
                'action_datetime' => current_time('mysql'),
                'additional_data' => json_encode($_POST['additional_data'] ?? []),
                'created_at' => current_time('mysql')
            ];
            
            $result = $wpdb->insert($actions_table, $action_data);
            
            if ($result === false) {
                error_log("❌ Erreur insertion action: " . $wpdb->last_error);
                wp_send_json_error(['message' => 'Erreur base de données']);
                return;
            }
            
            // Mettre à jour le compteur d'actions dans analytics
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}nfc_analytics 
                 SET actions_count = actions_count + 1, 
                     is_bounce = FALSE,
                     updated_at = %s
                 WHERE id = %d",
                current_time('mysql'),
                $analytics_id
            ));
            
            wp_send_json_success(['message' => 'Action enregistrée']);
            
        } catch (Exception $e) {
            error_log("❌ Erreur track_action: " . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur serveur']);
        }
    }
    
    /**
     * Mise à jour d'une session active
     */
    public function update_session() {
        try {
            $analytics_id = intval($_POST['analytics_id'] ?? 0);
            $session_duration = intval($_POST['session_duration'] ?? 0);
            $scroll_depth = intval($_POST['scroll_depth'] ?? 0);
            $actions_count = intval($_POST['actions_count'] ?? 0);
            
            if (!$analytics_id) {
                wp_send_json_error(['message' => 'Analytics ID manquant']);
                return;
            }
            
            global $wpdb;
            
            $wpdb->update(
                $wpdb->prefix . 'nfc_analytics',
                [
                    'session_duration' => $session_duration,
                    'scroll_depth' => min($scroll_depth, 100), // Max 100%
                    'time_on_page' => $session_duration,
                    'updated_at' => current_time('mysql')
                ],
                ['id' => $analytics_id],
                ['%d', '%d', '%d', '%s'],
                ['%d']
            );
            
            wp_send_json_success(['message' => 'Session mise à jour']);
            
        } catch (Exception $e) {
            error_log("❌ Erreur update_session: " . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur serveur']);
        }
    }
    
    /**
     * Fin de session
     */
    public function end_session() {
        try {
            $analytics_id = intval($_POST['analytics_id'] ?? 0);
            $session_duration = intval($_POST['session_duration'] ?? 0);
            $total_actions = intval($_POST['total_actions'] ?? 0);
            $max_scroll_depth = intval($_POST['max_scroll_depth'] ?? 0);
            $is_bounce = filter_var($_POST['is_bounce'] ?? true, FILTER_VALIDATE_BOOLEAN);
            
            if (!$analytics_id) {
                wp_die('Analytics ID manquant');
            }
            
            global $wpdb;
            
            $wpdb->update(
                $wpdb->prefix . 'nfc_analytics',
                [
                    'session_duration' => $session_duration,
                    'scroll_depth' => min($max_scroll_depth, 100),
                    'time_on_page' => $session_duration,
                    'actions_count' => $total_actions,
                    'is_bounce' => $is_bounce,
                    'updated_at' => current_time('mysql')
                ],
                ['id' => $analytics_id],
                ['%d', '%d', '%d', '%d', '%d', '%s'],
                ['%d']
            );
            
            wp_die('Session terminée');
            
        } catch (Exception $e) {
            error_log("❌ Erreur end_session: " . $e->getMessage());
            wp_die('Erreur serveur');
        }
    }
    
    /**
     * Utilitaires
     */
    
    private function is_tracking_enabled() {
        if (!class_exists('NFC_Analytics_Database')) {
            return false;
        }
        return NFC_Analytics_Database::get_config('analytics_enabled', true);
    }
    
    private function get_client_ip() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    private function is_ip_excluded($ip) {
        if (!class_exists('NFC_Analytics_Database')) {
            return false;
        }
        
        $excluded_ips = NFC_Analytics_Database::get_config('excluded_ips', []);
        return in_array($ip, $excluded_ips);
    }
    
    private function should_anonymize_ip() {
        if (!class_exists('NFC_Analytics_Database')) {
            return true;
        }
        return NFC_Analytics_Database::get_config('anonymize_ip', true);
    }
    
    private function anonymize_ip($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // IPv4: remplacer le dernier octet par 0
            return preg_replace('/\.\d+$/', '.0', $ip);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // IPv6: garder seulement les 64 premiers bits
            return substr($ip, 0, strpos($ip, ':', strpos($ip, ':', strpos($ip, ':', strpos($ip, ':') + 1) + 1) + 1)) . '::';
        }
        
        return $ip;
    }
    
    private function parse_utm_data($utm_raw) {
        if (!is_array($utm_raw)) {
            return [];
        }
        
        return [
            'source' => sanitize_text_field($utm_raw['source'] ?? ''),
            'medium' => sanitize_text_field($utm_raw['medium'] ?? ''),
            'campaign' => sanitize_text_field($utm_raw['campaign'] ?? ''),
            'term' => sanitize_text_field($utm_raw['term'] ?? ''),
            'content' => sanitize_text_field($utm_raw['content'] ?? '')
        ];
    }
    
    private function parse_device_info($device_raw) {
        if (!is_array($device_raw)) {
            return [];
        }
        
        return [
            'device_type' => sanitize_text_field($device_raw['device_type'] ?? 'unknown'),
            'browser' => sanitize_text_field($device_raw['browser'] ?? ''),
            'os' => sanitize_text_field($device_raw['os'] ?? ''),
            'screen_resolution' => sanitize_text_field($device_raw['screen_resolution'] ?? ''),
            'language' => sanitize_text_field($device_raw['language'] ?? ''),
            'user_agent' => $device_raw['user_agent'] ?? ''
        ];
    }
    
    private function get_geolocation($ip) {
        // Cache pour éviter les appels multiples
        if (isset($this->geo_cache[$ip])) {
            return $this->geo_cache[$ip];
        }
        
        // IPs locales ou exclues
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $this->geo_cache[$ip] = [];
        }
        
        if (!class_exists('NFC_Analytics_Database')) {
            return [];
        }
        
        $geo_service = NFC_Analytics_Database::get_config('geo_api_service', 'ipapi');
        
        if ($geo_service === 'none') {
            return $this->geo_cache[$ip] = [];
        }
        
        try {
            if ($geo_service === 'ipapi') {
                return $this->geo_cache[$ip] = $this->get_geolocation_ipapi($ip);
            }
            
            // Autres services de géolocalisation...
            
        } catch (Exception $e) {
            error_log("❌ Erreur géolocalisation pour {$ip}: " . $e->getMessage());
        }
        
        return $this->geo_cache[$ip] = [];
    }
    
    private function get_geolocation_ipapi($ip) {
        $url = "http://ipapi.co/{$ip}/json/";
        
        $response = wp_remote_get($url, [
            'timeout' => 3,
            'headers' => [
                'User-Agent' => 'NFC-Analytics/1.0'
            ]
        ]);
        
        if (is_wp_error($response)) {
            return [];
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || isset($data['error'])) {
            return [];
        }
        
        return [
            'country' => $data['country_code'] ?? null,
            'region' => $data['region'] ?? null,
            'city' => $data['city'] ?? null,
            'latitude' => isset($data['latitude']) ? floatval($data['latitude']) : null,
            'longitude' => isset($data['longitude']) ? floatval($data['longitude']) : null
        ];
    }
    
    private function detect_mobile_app($user_agent) {
        $app_indicators = ['Mobile App', 'NFC-App', 'vCard-App'];
        
        foreach ($app_indicators as $indicator) {
            if (stripos($user_agent, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
}

// Initialisation
new NFC_Analytics_Handler();