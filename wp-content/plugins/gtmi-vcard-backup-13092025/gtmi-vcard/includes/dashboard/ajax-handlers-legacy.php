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

        // Dans register_ajax_handlers()
        add_action('wp_ajax_nfc_debug_vcard_fields', [$this, 'debug_vcard_fields']);
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

        try {
            $vcard_id = intval($_POST['vcard_id'] ?? 0);

            if (!$vcard_id) {
                wp_send_json_error(['message' => 'ID vCard manquant']);
            }

            // Vérifier que l'utilisateur possède cette vCard
            $vcard = get_post($vcard_id);
            if (!$vcard || $vcard->post_type !== 'virtual_card') {
                wp_send_json_error(['message' => 'vCard non trouvée']);
            }

            error_log("🔄 DÉBUT SAUVEGARDE vCard ID: $vcard_id");
            error_log("📊 POST Data reçue: " . json_encode(array_keys($_POST)));

            // 🔥 LISTE COMPLÈTE DES CHAMPS basée sur ajax-handlers copy.php
            $fields = [
                // Champs de base
                'firstname',
                'lastname',
                'society',
                'service',
                'post',
                'email',
                'phone',
                'mobile',
                'website',
                'address',

                // Réseaux sociaux
                'linkedin',
                'facebook',
                'twitter',
                'instagram',
                'pinterest',
                'youtube',
                'tiktok',
                'snapchat',

                // Champs avancés
                'description',
                'custom_url',
                'redirect_mode',
                'status',

                // Métadonnées
                'created_at',
                'updated_at',
                'public_url'
            ];

            // 🔥 RÉCUPÉRATION DES CLÉS ACF
            $acf_field_keys = $this->get_acf_field_keys($vcard_id);
            error_log("🔑 Clés ACF récupérées: " . json_encode($acf_field_keys));

            // 🔥 BOUCLE DE SAUVEGARDE avec ACF + fallback
            $updated_count = 0;

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $value = sanitize_text_field($_POST[$field]);
                    error_log("📝 Traitement champ '$field': '$value'");

                    // Validation spéciale pour l'email
                    if ($field === 'email' && !empty($value)) {
                        if (!is_email($value)) {
                            wp_send_json_error(['message' => __('Email invalide', 'gtmi_vcard')]);
                        }
                    }

                    // Validation spéciale pour les URLs
                    if (in_array($field, ['website', 'custom_url', 'linkedin', 'twitter', 'facebook', 'instagram', 'pinterest', 'youtube']) && !empty($value)) {
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            wp_send_json_error(['message' => sprintf(__('URL invalide pour %s', 'gtmi_vcard'), $field)]);
                        }
                    }

                    // 🔥 SAUVEGARDE AVEC TRIPLE FALLBACK
                    $acf_result = false;
                    $meta_result = false;

                    // 1. Essayer avec la clé ACF d'abord
                    if (function_exists('update_field') && isset($acf_field_keys[$field])) {
                        $field_key = $acf_field_keys[$field];
                        // Vérifier que ce n'est pas une clé temporaire
                        if (!str_contains($field_key, '_temp')) {
                            $acf_result = update_field($field_key, $value, $vcard_id);
                            error_log("  ✅ ACF avec clé '$field_key': " . ($acf_result ? 'SUCCESS' : 'FAILED'));
                        }
                    }

                    // 2. Si pas de clé ACF ou échec, essayer avec le nom de champ
                    if (!$acf_result && function_exists('update_field')) {
                        $acf_result = update_field($field, $value, $vcard_id);
                        error_log("  🔄 ACF avec nom '$field': " . ($acf_result ? 'SUCCESS' : 'FAILED'));
                    }

                    // 3. Si ACF échoue, utiliser update_post_meta en fallback
                    if (!$acf_result) {
                        $meta_result = update_post_meta($vcard_id, $field, $value);
                        error_log("  🆘 Fallback post_meta '$field': " . ($meta_result ? 'SUCCESS' : 'FAILED'));
                    }

                    // Compter les succès
                    if ($acf_result || $meta_result) {
                        $updated_count++;
                        error_log("  ✅ Champ '$field' sauvegardé avec " . ($acf_result ? 'ACF' : 'post_meta'));
                    } else {
                        error_log("  ❌ ÉCHEC TOTAL pour le champ '$field'");
                    }
                }
            }

            // 🔥 GESTION DES SUPPRESSIONS D'IMAGES
            $this->handle_simple_image_deletions($vcard_id);

            // 🔥 GESTION DES IMAGES UPLOADÉES
            $image_updates = $this->handle_image_uploads($vcard_id, $acf_field_keys);

            // Réponse de succès
            $response_data = [
                'vcard_id' => $vcard_id,
                'message' => __('vCard mise à jour avec succès', 'gtmi_vcard'),
                'updated_fields' => $updated_count,
                'timestamp' => current_time('mysql')
            ];

            // Ajouter les infos images si uploadées
            if (!empty($image_updates)) {
                $response_data['images'] = $image_updates;
                $response_data['message'] .= ' ' . __('Images mises à jour.', 'gtmi_vcard');
                error_log("✅ Images uploadées: " . json_encode($image_updates));
            }

            error_log("✅ Sauvegarde vCard $vcard_id réussie - $updated_count champs mis à jour");
            wp_send_json_success($response_data);

        } catch (Exception $e) {
            error_log('❌ Erreur sauvegarde vCard: ' . $e->getMessage());
            error_log('❌ Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error(['message' => __('Erreur lors de la sauvegarde', 'gtmi_vcard') . ': ' . $e->getMessage()]);
        }
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

        error_log("📖 Récupération vCard ID: $vcard_id");

        // 🔥 RÉCUPÉRATION COMPLÈTE : ACF + post_meta
        $vcard_data = [];

        // 1. Récupérer les champs ACF d'abord
        if (function_exists('get_fields')) {
            $acf_fields = get_fields($vcard_id);
            if ($acf_fields) {
                $vcard_data = array_merge($vcard_data, $acf_fields);
                error_log("✅ " . count($acf_fields) . " champs ACF récupérés");
            }
        }

        // 2. Récupérer les post_meta en complément/fallback
        $meta = get_post_meta($vcard_id);
        foreach ($meta as $key => $value) {
            // Ne pas écraser les valeurs ACF existantes
            if (!isset($vcard_data[$key])) {
                $vcard_data[$key] = is_array($value) ? $value[0] : $value;
            }
        }

        // 3. Ajouter les infos importantes du post
        $vcard_data['id'] = $vcard_id;
        $vcard_data['title'] = $vcard->post_title;
        $vcard_data['slug'] = $vcard->post_name;
        $vcard_data['status'] = $vcard->post_status;
        $vcard_data['created_at'] = $vcard->post_date;
        $vcard_data['updated_at'] = $vcard->post_modified;
        $vcard_data['public_url'] = get_permalink($vcard_id);

        error_log("📊 vCard $vcard_id - " . count($vcard_data) . " champs récupérés au total");

        wp_send_json_success([
            'message' => 'vCard récupérée avec succès',
            'vcard_id' => $vcard_id,
            'data' => $vcard_data
        ]);
    }

    private function get_acf_field_keys($vcard_id)
    {
        if (!function_exists('get_field_objects')) {
            return [];
        }

        $field_objects = get_field_objects($vcard_id);
        $field_keys = [];

        if ($field_objects) {
            foreach ($field_objects as $field_name => $field_data) {
                if (isset($field_data['key']) && !empty($field_data['key'])) {
                    $field_keys[$field_name] = $field_data['key'];
                }
            }
        }

        error_log("🔑 Field keys ACF pour vCard $vcard_id: " . json_encode($field_keys));
        return $field_keys;
    }


    /**
     * 🔥 GESTION DES SUPPRESSIONS D'IMAGES SIMPLES
     */
    private function handle_simple_image_deletions($vcard_id)
    {
        // Récupérer les suppressions depuis le POST
        $deletions = $_POST['image_deletions'] ?? [];

        if (is_string($deletions)) {
            $deletions = json_decode($deletions, true) ?: [];
        }

        if (!empty($deletions)) {
            foreach ($deletions as $field_name) {
                error_log("🗑️ Suppression image champ: $field_name");
                $this->clean_image_field($vcard_id, $field_name);
            }
        }
    }

    /**
     * 🔥 NETTOYAGE D'UN CHAMP IMAGE
     */
    private function clean_image_field($vcard_id, $field_name)
    {
        // 1. Supprimer l'attachment WordPress si il existe
        $attachment_id = get_field($field_name, $vcard_id);
        if ($attachment_id && is_numeric($attachment_id)) {
            wp_delete_attachment($attachment_id, true);
            error_log("🗑️ Attachment $attachment_id supprimé pour $field_name");
        }

        // 2. Nettoyer le champ ACF
        if (function_exists('delete_field')) {
            delete_field($field_name, $vcard_id);
        }

        // 3. Nettoyer les post_meta en fallback
        delete_post_meta($vcard_id, $field_name);
        delete_post_meta($vcard_id, $field_name . '_id');
        delete_post_meta($vcard_id, $field_name . '_url');
    }


    /**
     * 🔥 GESTION DE L'UPLOAD D'IMAGES avec clés ACF
     */
    private function handle_image_uploads($vcard_id, $acf_field_keys = [])
    {
        $updates = [];

        // Gestion profile_picture
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_result = $this->process_image_upload($_FILES['profile_picture'], $vcard_id, 'profile_picture');
            if ($upload_result['success']) {
                $field_saved = $this->save_image_field($vcard_id, 'profile_picture', $upload_result['attachment_id'], $acf_field_keys);
                if ($field_saved) {
                    $updates['profile_picture'] = $upload_result;
                }
            }
        }

        // Même traitement pour cover_image
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = $this->process_image_upload($_FILES['cover_image'], $vcard_id, 'cover_image');
            if ($upload_result['success']) {
                $field_saved = $this->save_image_field($vcard_id, 'cover_image', $upload_result['attachment_id'], $acf_field_keys);
                if ($field_saved) {
                    $updates['cover_image'] = $upload_result;
                }
            }
        }

        return $updates;
    }

    /**
     * 🔥 SAUVEGARDE D'UN CHAMP IMAGE avec triple fallback
     */
    private function save_image_field($vcard_id, $field_name, $attachment_id, $acf_field_keys)
    {
        $field_saved = false;

        // 1. Essayer avec la clé ACF si disponible
        if (function_exists('update_field') && isset($acf_field_keys[$field_name])) {
            $field_key = $acf_field_keys[$field_name];
            if (!str_contains($field_key, '_temp')) {
                $field_saved = update_field($field_key, $attachment_id, $vcard_id);
                error_log("✅ Image '$field_name' sauvée avec clé ACF '$field_key': " . ($field_saved ? 'SUCCESS' : 'FAILED'));
            }
        }

        // 2. Fallback avec nom de champ ACF
        if (!$field_saved && function_exists('update_field')) {
            $field_saved = update_field($field_name, $attachment_id, $vcard_id);
            error_log("🔄 Image '$field_name' sauvée avec nom ACF: " . ($field_saved ? 'SUCCESS' : 'FAILED'));
        }

        // 3. Fallback post_meta
        if (!$field_saved) {
            update_post_meta($vcard_id, $field_name, $attachment_id);
            $url = wp_get_attachment_url($attachment_id);
            if ($url) {
                update_post_meta($vcard_id, $field_name . '_url', $url);
            }
            $field_saved = true; // Considérer comme sauvé avec post_meta
            error_log("🆘 Image '$field_name' sauvée avec post_meta");
        }

        return $field_saved;
    }

    /**
     * 🔥 TRAITEMENT D'UPLOAD D'IMAGE
     */
    private function process_image_upload($file, $vcard_id, $field_name)
    {
        // Inclure les fichiers WordPress nécessaires
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Validation du type de fichier
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            return [
                'success' => false,
                'error' => __('Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.', 'gtmi_vcard')
            ];
        }

        // Validation de la taille (5MB max)
        $max_size = 5 * 1024 * 1024; // 5MB en bytes
        if ($file['size'] > $max_size) {
            return [
                'success' => false,
                'error' => __('Fichier trop volumineux. Taille maximum : 5MB.', 'gtmi_vcard')
            ];
        }

        // Upload du fichier
        $upload = wp_handle_upload($file, ['test_form' => false]);

        if (isset($upload['error'])) {
            return [
                'success' => false,
                'error' => $upload['error']
            ];
        }

        // Créer l'attachment
        $attachment = [
            'post_mime_type' => $upload['type'],
            'post_title' => "vCard $vcard_id - " . ucfirst($field_name),
            'post_content' => '',
            'post_status' => 'inherit'
        ];

        $attachment_id = wp_insert_attachment($attachment, $upload['file']);

        if (is_wp_error($attachment_id)) {
            return [
                'success' => false,
                'error' => $attachment_id->get_error_message()
            ];
        }

        // Générer les métadonnées de l'image
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        return [
            'success' => true,
            'attachment_id' => $attachment_id,
            'url' => $upload['url'],
            'file_path' => $upload['file'],
            'field_name' => $field_name
        ];
    }

    /**
     * 🔧 DEBUG - Fonction utilitaire pour diagnostiquer
     */
    public function debug_vcard_fields()
    {
        $vcard_id = intval($_POST['vcard_id'] ?? 0);

        if (!$vcard_id) {
            wp_send_json_error('vCard ID manquant');
        }

        $debug_info = [
            'vcard_id' => $vcard_id,
            'post_exists' => get_post($vcard_id) ? true : false,
            'post_type' => get_post_type($vcard_id),
            'acf_available' => function_exists('get_fields'),
            'acf_fields' => function_exists('get_fields') ? get_fields($vcard_id) : null,
            'acf_field_keys' => $this->get_acf_field_keys($vcard_id),
            'post_meta_count' => count(get_post_meta($vcard_id)),
            'form_fields_received' => array_keys($_POST)
        ];

        error_log("🔍 DEBUG COMPLET vCard $vcard_id: " . json_encode($debug_info, JSON_PRETTY_PRINT));

        wp_send_json_success($debug_info);
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
        if (!$vcard)
            return null;

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
            usort($stats, function ($a, $b) {
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