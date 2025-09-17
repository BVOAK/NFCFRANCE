<?php
/**
 * SYSTÈME UNIFIÉ DE STATISTIQUES NFC
 * Harmonise les calculs entre Overview et Statistics
 * Utilise les vraies données de l'API REST
 */

class NFCStatsUnifiedCalculator {
    
    private $api_base_url;
    
    public function __construct() {
        // Récupérer l'URL de l'API depuis la config
        $this->api_base_url = home_url('/wp-json/nfc/v1/');
    }
    
    /**
     * ✅ CALCULER LES STATISTIQUES RÉELLES D'UNE VCARD
     */
    public function get_real_vcard_stats($vcard_id, $period_days = 30) {
        $stats = [
            'total_views' => 0,
            'total_contacts' => 0,
            'qr_scans' => 0,
            'nfc_taps' => 0,
            'social_clicks' => 0,
            'conversion_rate' => 0,
            'recent_activity' => []
        ];
        
        // 1. RÉCUPÉRER LES STATISTICS VIA API
        $statistics_data = $this->api_call("statistics/{$vcard_id}");
        
        if ($statistics_data && is_array($statistics_data)) {
            $recent_stats = array_filter($statistics_data, function($stat) use ($period_days) {
                $stat_date = strtotime($stat['created_at'] ?? '');
                $cutoff_date = strtotime("-{$period_days} days");
                return $stat_date >= $cutoff_date;
            });
            
            $stats['total_views'] = count($recent_stats);
            
            // Compter par type d'événement
            foreach ($recent_stats as $stat) {
                $event = $stat['event'] ?? 'unknown';
                switch ($event) {
                    case 'qr_scan':
                        $stats['qr_scans']++;
                        break;
                    case 'nfc_tap':
                        $stats['nfc_taps']++;
                        break;
                    case 'linkedin_click':
                    case 'facebook_click':
                    case 'twitter_click':
                    case 'instagram_click':
                        $stats['social_clicks']++;
                        break;
                }
            }
            
            // Activité récente (5 derniers événements)
            $stats['recent_activity'] = array_slice($statistics_data, 0, 5);
        }
        
        // 2. RÉCUPÉRER LES LEADS VIA API
        $leads_data = $this->api_call("leads/{$vcard_id}");
        
        if ($leads_data && is_array($leads_data)) {
            $recent_leads = array_filter($leads_data, function($lead) use ($period_days) {
                $lead_date = strtotime($lead['created_at'] ?? $lead['contact_datetime'] ?? '');
                $cutoff_date = strtotime("-{$period_days} days");
                return $lead_date >= $cutoff_date;
            });
            
            $stats['total_contacts'] = count($recent_leads);
        }
        
        // 3. CALCULER LE TAUX DE CONVERSION
        $stats['conversion_rate'] = $stats['total_views'] > 0 
            ? round(($stats['total_contacts'] / $stats['total_views']) * 100, 2) 
            : 0;
        
        return $stats;
    }
    
    /**
     * ✅ CALCULER LES STATISTIQUES CONSOLIDÉES MULTI-VCARD
     */
    public function get_multi_vcard_stats($vcard_ids, $period_days = 30) {
        $consolidated_stats = [
            'total_views' => 0,
            'total_contacts' => 0,
            'total_scans' => 0,
            'conversion_rate' => 0,
            'views_change' => 0,
            'contacts_change' => 0,
            'recent_activity' => []
        ];
        
        $all_activities = [];
        
        foreach ($vcard_ids as $vcard_id) {
            $vcard_stats = $this->get_real_vcard_stats($vcard_id, $period_days);
            
            // Consolider les métriques
            $consolidated_stats['total_views'] += $vcard_stats['total_views'];
            $consolidated_stats['total_contacts'] += $vcard_stats['total_contacts'];
            $consolidated_stats['total_scans'] += ($vcard_stats['qr_scans'] + $vcard_stats['nfc_taps']);
            
            // Collecter les activités
            $all_activities = array_merge($all_activities, $vcard_stats['recent_activity']);
        }
        
        // Calculer le taux de conversion consolidé
        $consolidated_stats['conversion_rate'] = $consolidated_stats['total_views'] > 0
            ? round(($consolidated_stats['total_contacts'] / $consolidated_stats['total_views']) * 100, 2)
            : 0;
        
        // Calculer les variations (période actuelle vs précédente)
        $previous_stats = $this->get_multi_vcard_stats($vcard_ids, $period_days * 2);
        
        if ($previous_stats['total_views'] > 0) {
            $consolidated_stats['views_change'] = round(
                (($consolidated_stats['total_views'] - $previous_stats['total_views']) / $previous_stats['total_views']) * 100, 
                1
            );
        }
        
        if ($previous_stats['total_contacts'] > 0) {
            $consolidated_stats['contacts_change'] = round(
                (($consolidated_stats['total_contacts'] - $previous_stats['total_contacts']) / $previous_stats['total_contacts']) * 100, 
                1
            );
        }
        
        // Trier les activités par date et prendre les 10 plus récentes
        usort($all_activities, function($a, $b) {
            return strtotime($b['created_at'] ?? '') - strtotime($a['created_at'] ?? '');
        });
        
        $consolidated_stats['recent_activity'] = array_slice($all_activities, 0, 10);
        
        return $consolidated_stats;
    }
    
    /**
     * ✅ APPEL API INTERNE
     */
    private function api_call($endpoint) {
        $url = $this->api_base_url . $endpoint;
        
        // Utiliser wp_remote_get pour les appels internes
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
        
        if (is_wp_error($response)) {
            error_log("❌ Erreur API call {$endpoint}: " . $response->get_error_message());
            return null;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Gérer les réponses avec wrapper success/data
        if (isset($data['success']) && $data['success']) {
            return $data['data'] ?? [];
        }
        
        // Gérer les réponses directes (array)
        if (is_array($data)) {
            return $data;
        }
        
        return null;
    }
    
    /**
     * ✅ FORMATER L'ACTIVITÉ RÉCENTE POUR L'AFFICHAGE
     */
    public function format_recent_activity($activities) {
        $formatted = [];
        
        foreach ($activities as $activity) {
            $formatted_item = [
                'type' => 'view',
                'icon' => 'eye',
                'color' => 'primary',
                'time' => $this->time_ago($activity['created_at'] ?? ''),
                'user' => $activity['ip_address'] ?? 'Visiteur anonyme'
            ];
            
            // Adapter selon le type d'événement
            switch ($activity['event'] ?? 'view') {
                case 'qr_scan':
                    $formatted_item['icon'] = 'qrcode';
                    $formatted_item['color'] = 'success';
                    $formatted_item['details'] = 'Scan QR Code';
                    break;
                    
                case 'nfc_tap':
                    $formatted_item['icon'] = 'wifi';
                    $formatted_item['color'] = 'info';
                    $formatted_item['details'] = 'Tap NFC';
                    break;
                    
                case 'linkedin_click':
                    $formatted_item['icon'] = 'linkedin';
                    $formatted_item['color'] = 'primary';
                    $formatted_item['details'] = 'Clic LinkedIn';
                    break;
                    
                case 'email_click':
                    $formatted_item['icon'] = 'envelope';
                    $formatted_item['color'] = 'warning';
                    $formatted_item['details'] = 'Clic Email';
                    break;
                    
                default:
                    $formatted_item['details'] = 'Vue du profil';
            }
            
            $formatted[] = $formatted_item;
        }
        
        return $formatted;
    }
    
    /**
     * ✅ CALCULER LE TEMPS ÉCOULÉ
     */
    private function time_ago($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'À l\'instant';
        if ($time < 3600) return floor($time/60) . ' min';
        if ($time < 86400) return floor($time/3600) . 'h';
        if ($time < 2592000) return floor($time/86400) . 'j';
        if ($time < 31536000) return floor($time/2592000) . ' mois';
        
        return floor($time/31536000) . ' an' . (floor($time/31536000) > 1 ? 's' : '');
    }
}

// ===============================================
// NOUVEAUX HANDLERS AJAX POUR STATISTICS.PHP
// ===============================================

/**
 * ✅ HANDLER AJAX: get_unified_statistics_data
 */
function ajax_get_unified_statistics_data() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $user_id = get_current_user_id();
    $period = sanitize_text_field($_POST['period'] ?? '30d');
    $profile_id = intval($_POST['profile'] ?? 0);
    
    try {
        // Récupérer les vCards utilisateur
        $user_vcards = nfc_get_user_vcard_profiles($user_id);
        
        if (empty($user_vcards)) {
            wp_send_json_error(['message' => 'Aucune vCard trouvée']);
            return;
        }
        
        // Déterminer les vCards à analyser
        $target_vcards = $profile_id 
            ? [$profile_id] 
            : array_column($user_vcards, 'vcard_id');
        
        // Convertir période en jours
        $period_days = match($period) {
            '7d' => 7,
            '30d' => 30,
            '3m' => 90,
            '1y' => 365,
            default => 30
        };
        
        // Calculer les statistiques réelles
        $calculator = new NFCStatsUnifiedCalculator();
        
        if (count($target_vcards) === 1) {
            $stats = $calculator->get_real_vcard_stats($target_vcards[0], $period_days);
        } else {
            $stats = $calculator->get_multi_vcard_stats($target_vcards, $period_days);
        }
        
        // Formater l'activité récente
        $formatted_activity = $calculator->format_recent_activity($stats['recent_activity']);
        
        wp_send_json_success([
            'stats' => [
                'total_views' => $stats['total_views'],
                'total_contacts' => $stats['total_contacts'],
                'total_scans' => $stats['total_scans'] ?? ($stats['qr_scans'] + $stats['nfc_taps']),
                'conversion_rate' => $stats['conversion_rate'],
                'views_change' => $stats['views_change'] ?? 0,
                'contacts_change' => $stats['contacts_change'] ?? 0
            ],
            'activity' => $formatted_activity,
            'period' => $period,
            'profile' => $profile_id,
            'debug' => [
                'target_vcards' => $target_vcards,
                'period_days' => $period_days,
                'raw_stats' => $stats
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("❌ Erreur get_unified_statistics_data: " . $e->getMessage());
        wp_send_json_error(['message' => 'Erreur lors du calcul des statistiques: ' . $e->getMessage()]);
    }
}

// Enregistrer le handler AJAX
add_action('wp_ajax_nfc_get_unified_statistics', 'ajax_get_unified_statistics_data');

/**
 * ✅ HANDLER AJAX: get_unified_overview_data (pour harmoniser Overview)
 */
function ajax_get_unified_overview_data() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $user_id = get_current_user_id();
    $vcard_id = intval($_POST['vcard_id'] ?? 0);
    
    try {
        $calculator = new NFCStatsUnifiedCalculator();
        $stats = $calculator->get_real_vcard_stats($vcard_id, 30);
        
        // Formater pour la compatibilité avec Overview
        wp_send_json_success([
            'kpis' => [
                'totalViews' => $stats['total_views'],
                'todayViews' => 0, // À calculer si nécessaire
                'weekViews' => $stats['total_views'], // Approximation
                'interactions' => $stats['social_clicks']
            ],
            'contacts' => $stats['total_contacts'],
            'activity' => $calculator->format_recent_activity($stats['recent_activity'])
        ]);
        
    } catch (Exception $e) {
        error_log("❌ Erreur get_unified_overview_data: " . $e->getMessage());
        wp_send_json_error(['message' => 'Erreur lors du calcul: ' . $e->getMessage()]);
    }
}

add_action('wp_ajax_nfc_get_unified_overview', 'ajax_get_unified_overview_data');
?>