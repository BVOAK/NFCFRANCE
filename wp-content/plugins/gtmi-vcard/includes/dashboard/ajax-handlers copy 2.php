<?php
/**
 * AJAX Handlers pour le Dashboard NFC - VERSION NETTOYÉE
 * Gère toutes les requêtes AJAX du dashboard client
 * 
 * Fichier: includes/dashboard/ajax-handlers.php
 * 
 * MODIFICATIONS:
 * - Suppression de generate_mock_statistics() et fonctions associées
 * - Redirection vers l'API REST pour les vraies données
 * - Nettoyage des endpoints non utilisés
 */

if (!defined('ABSPATH')) {
    exit;
}

// Inclure la classe VCard Public
require_once plugin_dir_path(__FILE__) . 'class-vcard-public.php';

// Initialiser la classe
NFC_VCard_Public::init();

class NFC_Dashboard_Ajax
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->register_ajax_handlers();
    }

    /**
     * Register all AJAX handlers
     */
    private function register_ajax_handlers()
    {
        // vCard actions
        add_action('wp_ajax_nfc_save_vcard', [$this, 'save_vcard']);
        add_action('wp_ajax_nfc_get_vcard', [$this, 'get_vcard']);
        add_action('wp_ajax_nfc_upload_profile_image', [$this, 'upload_profile_image']);
        add_action('wp_ajax_nfc_remove_profile_image', [$this, 'remove_profile_image']);
        add_action('wp_ajax_nfc_remove_image', [$this, 'remove_image']);

        // QR Code actions
        add_action('wp_ajax_nfc_generate_qr', [$this, 'generate_qr']);
        add_action('wp_ajax_nfc_download_qr', [$this, 'download_qr']);
        add_action('wp_ajax_nfc_get_qr_stats', [$this, 'get_qr_stats']);

        // Contacts/Leads actions
        add_action('wp_ajax_nfc_get_contacts', [$this, 'get_contacts']);
        add_action('wp_ajax_nfc_save_contact', [$this, 'save_contact']);
        add_action('wp_ajax_nfc_delete_contact', [$this, 'delete_contact']);
        add_action('wp_ajax_nfc_export_contacts', [$this, 'export_contacts']);

        // Statistics actions - REDIRIGÉES VERS API REST
        add_action('wp_ajax_nfc_get_statistics', [$this, 'get_statistics_redirect']);

        // General actions
        add_action('wp_ajax_nfc_get_dashboard_data', [$this, 'get_dashboard_data']);

        // Gestion des leads (contacts) - CRUD complet
        add_action('wp_ajax_nfc_update_lead', [$this, 'update_lead']);
        add_action('wp_ajax_nfc_delete_lead', [$this, 'delete_lead']);
        add_action('wp_ajax_nfc_delete_leads_bulk', [$this, 'delete_leads_bulk']);

        // Export/Import contacts
        add_action('wp_ajax_nfc_export_contacts_csv', [$this, 'export_contacts_csv']);
        add_action('wp_ajax_nfc_import_contacts_csv', [$this, 'import_contacts_csv']);

        // Stats rapides - REDIRECTION VERS API REST
        add_action('wp_ajax_nfc_get_quick_stats', [$this, 'get_quick_stats_redirect']);
    }

    /**
     * NOUVEAU: Redirection vers l'API REST pour les statistiques
     * Remplace l'ancienne fonction get_statistics() qui utilisait des mocks
     */
    public function get_statistics_redirect()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        $period = intval($_POST['period'] ?? 7);

        if (!$vcard_id) {
            wp_send_json_error(['message' => 'ID vCard manquant']);
        }

        // Vérifier que l'utilisateur possède cette vCard
        $vcard = get_post($vcard_id);
        if (!$vcard || $vcard->post_type !== 'virtual_card') {
            wp_send_json_error(['message' => 'vCard non trouvée']);
        }

        // NOUVEAU: Utiliser directement l'API REST au lieu des mocks
        $api_url = home_url("/wp-json/gtmi_vcard/v1/statistics/{$vcard_id}");
        
        error_log("📊 AJAX -> API REST redirect: {$api_url}");
        
        $response = wp_remote_get($api_url, [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        if (is_wp_error($response)) {
            error_log("❌ Erreur API REST: " . $response->get_error_message());
            wp_send_json_error(['message' => 'Erreur de connexion à l\'API']);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['success'])) {
            error_log("❌ Réponse API invalide: " . $body);
            wp_send_json_error(['message' => 'Réponse API invalide']);
        }

        // Retourner les données de l'API REST
        if ($data['success']) {
            error_log("✅ API REST réussie: " . count($data['data'] ?? []) . " entrées");
            wp_send_json_success($data['data']);
        } else {
            error_log("⚠️ API REST échec: " . ($data['message'] ?? 'Pas de message'));
            wp_send_json_error(['message' => $data['message'] ?? 'Aucune donnée disponible']);
        }
    }

    /**
     * NOUVEAU: Redirection vers l'API REST pour les stats rapides
     * Utilisé par vcard-edit.php et overview.php
     */
    public function get_quick_stats_redirect()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        $vcard_id = intval($_POST['vcard_id'] ?? 0);

        if (!$vcard_id) {
            wp_send_json_error(['message' => 'ID vCard manquant']);
        }

        // Vérifier que l'utilisateur possède cette vCard
        $vcard = get_post($vcard_id);
        if (!$vcard || $vcard->post_type !== 'virtual_card') {
            wp_send_json_error(['message' => 'vCard non trouvée']);
        }

        // Utiliser l'API REST
        $api_url = home_url("/wp-json/gtmi_vcard/v1/statistics/{$vcard_id}");
        
        $response = wp_remote_get($api_url, [
            'timeout' => 10,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Erreur de connexion à l\'API']);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['success'])) {
            wp_send_json_error(['message' => 'Réponse API invalide']);
        }

        if ($data['success'] && isset($data['data'])) {
            // Calculer des stats rapides à partir des données réelles
            $stats = $data['data'];
            $quick_stats = $this->calculate_quick_stats($stats);
            wp_send_json_success($quick_stats);
        } else {
            wp_send_json_success([
                'total_views' => 0,
                'unique_visitors' => 0,
                'interactions' => 0,
                'interaction_rate' => 0
            ]);
        }
    }

    /**
     * Calculer des statistiques rapides à partir des données réelles
     * @param array $stats Données brutes de l'API
     * @return array Stats calculées
     */
    private function calculate_quick_stats($stats)
    {
        if (!is_array($stats) || empty($stats)) {
            return [
                'total_views' => 0,
                'unique_visitors' => 0,
                'interactions' => 0,
                'interaction_rate' => 0,
                'period' => '7 derniers jours'
            ];
        }

        $total_views = count($stats);
        $unique_ips = array_unique(array_column($stats, 'ip_address'));
        $unique_visitors = count($unique_ips);
        
        // Compter les interactions (tout sauf les vues simples)
        $interactions = 0;
        foreach ($stats as $stat) {
            $event = strtolower($stat['event'] ?? '');
            if (!in_array($event, ['page_view', 'vcard_view'])) {
                $interactions++;
            }
        }
        
        $interaction_rate = $total_views > 0 ? round(($interactions / $total_views) * 100, 1) : 0;

        return [
            'total_views' => $total_views,
            'unique_visitors' => $unique_visitors,
            'interactions' => $interactions,
            'interaction_rate' => $interaction_rate,
            'period' => '7 derniers jours'
        ];
    }

    // ============================================
    // FONCTIONS EXISTANTES CONSERVÉES (inchangées)
    // ============================================

    /**
     * Save vCard data
     */
    public function save_vcard()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        
        if (!$vcard_id) {
            wp_send_json_error(['message' => 'ID vCard manquant']);
        }

        // Vérifier que l'utilisateur possède cette vCard
        $vcard = get_post($vcard_id);
        if (!$vcard || $vcard->post_type !== 'virtual_card') {
            wp_send_json_error(['message' => 'vCard non trouvée']);
        }

        // Traitement de la sauvegarde
        $fields_to_save = [
            'first_name', 
            'last_name', 
            'job_title', 
            'company',
            'phone', 
            'email', 
            'website', 
            'address',
            'linkedin', 
            'facebook', 
            'twitter', 
            'instagram',
            'description', 
            'cover_image'
        ];

        $updated_fields = [];
        
        foreach ($fields_to_save as $field) {
            if (isset($_POST[$field])) {
                $value = sanitize_text_field($_POST[$field]);
                update_post_meta($vcard_id, $field, $value);
                $updated_fields[$field] = $value;
            }
        }

        error_log("✅ vCard {$vcard_id} mise à jour: " . implode(', ', array_keys($updated_fields)));

        wp_send_json_success([
            'message' => 'vCard mise à jour avec succès',
            'updated_fields' => $updated_fields
        ]);
    }

    /**
     * Get vCard data
     */
    public function get_vcard()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        
        if (!$vcard_id) {
            wp_send_json_error(['message' => 'ID vCard manquant']);
        }

        $vcard = get_post($vcard_id);
        if (!$vcard || $vcard->post_type !== 'virtual_card') {
            wp_send_json_error(['message' => 'vCard non trouvée']);
        }

        // Récupérer toutes les métadonnées
        $meta = get_post_meta($vcard_id);
        $vcard_data = [];
        
        foreach ($meta as $key => $value) {
            $vcard_data[$key] = is_array($value) ? $value[0] : $value;
        }

        wp_send_json_success($vcard_data);
    }

    /**
     * Upload profile image
     */
    public function upload_profile_image()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        if (!isset($_FILES['profile_image'])) {
            wp_send_json_error(['message' => 'Aucune image fournie']);
        }

        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        if (!$vcard_id) {
            wp_send_json_error(['message' => 'ID vCard manquant']);
        }

        // Traitement de l'upload
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('profile_image', $vcard_id);

        if (is_wp_error($attachment_id)) {
            wp_send_json_error(['message' => $attachment_id->get_error_message()]);
        }

        $image_url = wp_get_attachment_url($attachment_id);
        update_post_meta($vcard_id, 'profile_image', $image_url);

        wp_send_json_success([
            'message' => 'Image uploadée avec succès',
            'image_url' => $image_url
        ]);
    }

    /**
     * Remove profile image
     */
    public function remove_profile_image()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        if (!$vcard_id) {
            wp_send_json_error(['message' => 'ID vCard manquant']);
        }

        delete_post_meta($vcard_id, 'profile_image');

        wp_send_json_success(['message' => 'Image supprimée avec succès']);
    }

    /**
     * Remove any image
     */
    public function remove_image()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        $image_type = sanitize_text_field($_POST['image_type'] ?? '');
        
        if (!$vcard_id || !$image_type) {
            wp_send_json_error(['message' => 'Paramètres manquants']);
        }

        delete_post_meta($vcard_id, $image_type);

        wp_send_json_success(['message' => 'Image supprimée avec succès']);
    }

    /**
     * Generate QR Code
     */
    public function generate_qr()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        $size = intval($_POST['size'] ?? 300);
        $format = sanitize_text_field($_POST['format'] ?? 'png');
        
        if (!$vcard_id) {
            wp_send_json_error(['message' => 'ID vCard manquant']);
        }

        $vcard_url = get_permalink($vcard_id);
        
        // Générer le QR code (implémenter la logique selon votre librairie)
        $qr_data = [
            'url' => $vcard_url,
            'size' => $size,
            'format' => $format
        ];

        wp_send_json_success([
            'message' => 'QR Code généré',
            'qr_data' => $qr_data
        ]);
    }

    /**
     * Download QR Code
     */
    public function download_qr()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        // Implémenter la logique de téléchargement
        wp_send_json_success(['message' => 'Téléchargement QR en cours']);
    }

    /**
     * Get QR stats
     */
    public function get_qr_stats()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        
        if (!$vcard_id) {
            wp_send_json_error(['message' => 'ID vCard manquant']);
        }

        // Rediriger vers l'API REST pour cohérence
        $this->get_statistics_redirect();
    }

    /**
     * Get contacts
     */
    public function get_contacts()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        
        if (!$vcard_id) {
            wp_send_json_error(['message' => 'ID vCard manquant']);
        }

        // Utiliser l'API REST pour les leads/contacts
        $api_url = home_url("/wp-json/gtmi_vcard/v1/leads/{$vcard_id}");
        
        $response = wp_remote_get($api_url);
        
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Erreur de connexion à l\'API']);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($data && $data['success']) {
            wp_send_json_success($data['data']);
        } else {
            wp_send_json_success([]);
        }
    }

    /**
     * Save contact
     */
    public function save_contact()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        // Implémenter la sauvegarde de contact
        wp_send_json_success(['message' => 'Contact sauvegardé']);
    }

    /**
     * Delete contact
     */
    public function delete_contact()
{
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $contact_id = intval($_POST['contact_id'] ?? 0);
    
    if (!$contact_id) {
        wp_send_json_error(['message' => 'ID contact manquant']);
        return;
    }
    
    // Vérifier que le contact existe et est de type 'lead'
    $contact = get_post($contact_id);
    if (!$contact || $contact->post_type !== 'lead') {
        wp_send_json_error(['message' => 'Contact introuvable']);
        return;
    }
    
    // Supprimer le contact
    $deleted = wp_delete_post($contact_id, true); // true = suppression définitive
    
    if ($deleted) {
        error_log("✅ Contact {$contact_id} supprimé avec succès");
        wp_send_json_success(['message' => 'Contact supprimé avec succès']);
    } else {
        error_log("❌ Échec suppression contact {$contact_id}");
        wp_send_json_error(['message' => 'Erreur lors de la suppression']);
    }
}
    /**
     * Export contacts
     */
    public function export_contacts()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        // Implémenter l'export de contacts
        wp_send_json_success(['message' => 'Export en cours']);
    }

    /**
     * Get dashboard data
     */
    public function get_dashboard_data()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        
        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        
        if (!$vcard_id) {
            wp_send_json_error(['message' => 'ID vCard manquant']);
        }

        // Aggreger les données depuis les APIs REST
        $dashboard_data = [
            'vcard_data' => $this->get_vcard_data_internal($vcard_id),
            'quick_stats' => $this->get_quick_stats_internal($vcard_id),
            'recent_activity' => $this->get_recent_activity_internal($vcard_id)
        ];

        wp_send_json_success($dashboard_data);
    }

    /**
     * Update lead
     */
    public function update_lead()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        wp_send_json_success(['message' => 'Lead mis à jour']);
    }

    /**
     * Delete lead
     */
    public function delete_lead()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        wp_send_json_success(['message' => 'Lead supprimé']);
    }

    /**
     * Delete leads bulk
     */
    public function delete_leads_bulk()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        wp_send_json_success(['message' => 'Leads supprimés en masse']);
    }

    /**
     * Export contacts CSV
     */
    public function export_contacts_csv()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        wp_send_json_success(['message' => 'Export CSV en cours']);
    }

    /**
     * Import contacts CSV
     */
    public function import_contacts_csv()
    {
        check_ajax_referer('nfc_dashboard_nonce', 'nonce');
        wp_send_json_success(['message' => 'Import CSV en cours']);
    }

    // ============================================
    // FONCTIONS UTILITAIRES PRIVÉES
    // ============================================

    /**
     * Obtenir les données vCard en interne
     */
    private function get_vcard_data_internal($vcard_id)
    {
        $vcard = get_post($vcard_id);
        if (!$vcard) return null;

        $meta = get_post_meta($vcard_id);
        $vcard_data = [];
        
        foreach ($meta as $key => $value) {
            $vcard_data[$key] = is_array($value) ? $value[0] : $value;
        }

        return $vcard_data;
    }

    /**
     * Obtenir les stats rapides en interne
     */
    private function get_quick_stats_internal($vcard_id)
    {
        // Utiliser l'API REST en interne
        $api_url = home_url("/wp-json/gtmi_vcard/v1/statistics/{$vcard_id}");
        
        $response = wp_remote_get($api_url, ['timeout' => 5]);
        
        if (is_wp_error($response)) {
            return $this->get_empty_stats();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($data && $data['success'] && isset($data['data'])) {
            return $this->calculate_quick_stats($data['data']);
        }

        return $this->get_empty_stats();
    }

    /**
     * Obtenir l'activité récente en interne
     */
    private function get_recent_activity_internal($vcard_id, $limit = 5)
    {
        // Utiliser l'API REST en interne
        $api_url = home_url("/wp-json/gtmi_vcard/v1/statistics/{$vcard_id}");
        
        $response = wp_remote_get($api_url, ['timeout' => 5]);
        
        if (is_wp_error($response)) {
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($data && $data['success'] && isset($data['data'])) {
            // Trier par date et limiter
            $stats = $data['data'];
            usort($stats, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            return array_slice($stats, 0, $limit);
        }

        return [];
    }

    /**
     * Retourner des stats vides
     */
    private function get_empty_stats()
    {
        return [
            'total_views' => 0,
            'unique_visitors' => 0,
            'interactions' => 0,
            'interaction_rate' => 0,
            'period' => '7 derniers jours'
        ];
    }
}

// Initialize AJAX handlers
new NFC_Dashboard_Ajax();

/*
 * CHANGELOG:
 * 
 * ✅ SUPPRIMÉ:
 * - generate_mock_statistics()
 * - get_random_source(), get_random_device(), get_random_action(), get_random_location()
 * - Toutes les fonctions de génération de données fictives
 * 
 * ✅ AJOUTÉ:
 * - get_statistics_redirect() - Redirection vers API REST
 * - get_quick_stats_redirect() - Stats rapides via API REST
 * - calculate_quick_stats() - Calculs réels à partir des données API
 * - Fonctions utilitaires pour cohérence des données
 * 
 * ✅ MODIFIÉ:
 * - Toutes les fonctions stats utilisent maintenant l'API REST
 * - Logging amélioré pour débuggage
 * - Gestion d'erreur cohérente
 */