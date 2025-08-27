<?php
/**
 * NFC File Handler - Gestion des téléchargements admin
 * Génère et sert les fichiers images/screenshots à la demande
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_File_Handler {
    
    private $upload_dir;
    private $screenshots_dir;
    private $logos_dir;
    
    public function __construct() {
        $this->init_directories();
        add_action('init', [$this, 'init']);
    }
    
    /**
     * Initialisation
     */
    public function init() {
        // Routes de téléchargement
        add_action('wp_ajax_nfc_download_logo', [$this, 'download_logo']);
        add_action('wp_ajax_nfc_download_screenshot', [$this, 'download_screenshot']);
        add_action('wp_ajax_nfc_view_screenshot', [$this, 'view_screenshot']);
        
        // Pas d'accès public (admin seulement)
        
        error_log('NFC: File Handler initialisé');
    }
    
    /**
     * Initialise les dossiers de stockage
     */
    private function init_directories() {
        $upload_dir = wp_upload_dir();
        $this->upload_dir = $upload_dir['basedir'];
        $this->screenshots_dir = $this->upload_dir . '/nfc-screenshots/';
        $this->logos_dir = $this->upload_dir . '/nfc-logos/';
        
        // Créer les dossiers si nécessaire
        $this->ensure_directory_exists($this->screenshots_dir);
        $this->ensure_directory_exists($this->logos_dir);
    }
    
    /**
     * S'assure qu'un dossier existe avec sécurité
     */
    private function ensure_directory_exists($dir) {
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
            
            // Ajouter .htaccess pour sécurité
            $htaccess_content = "Order deny,allow\nDeny from all\nAllow from 127.0.0.1\n";
            file_put_contents($dir . '.htaccess', $htaccess_content);
            
            // Ajouter index.php vide
            file_put_contents($dir . 'index.php', '<?php // Silence is golden');
        }
    }
    
    /**
     * Télécharge le logo d'une commande
     */
    public function download_logo() {
        // Vérifier permissions admin
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Accès refusé', 'Erreur', ['response' => 403]);
        }
        
        // Vérifier nonce
        if (!wp_verify_nonce($_GET['nonce'] ?? '', 'nfc_admin_download')) {
            wp_die('Nonce invalide', 'Erreur', ['response' => 403]);
        }
        
        $order_id = intval($_GET['order_id'] ?? 0);
        $item_id = intval($_GET['item_id'] ?? 0);
        
        if (!$order_id || !$item_id) {
            wp_die('Paramètres manquants', 'Erreur', ['response' => 400]);
        }
        
        try {
            $this->serve_logo_file($order_id, $item_id);
        } catch (Exception $e) {
            error_log('NFC: Erreur download logo: ' . $e->getMessage());
            wp_die('Erreur lors du téléchargement: ' . $e->getMessage(), 'Erreur', ['response' => 500]);
        }
    }
    
    /**
     * Télécharge le screenshot d'une commande
     */
    public function download_screenshot() {
        // Vérifier permissions admin
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Accès refusé', 'Erreur', ['response' => 403]);
        }
        
        // Vérifier nonce
        if (!wp_verify_nonce($_GET['nonce'] ?? '', 'nfc_admin_download')) {
            wp_die('Nonce invalide', 'Erreur', ['response' => 403]);
        }
        
        $order_id = intval($_GET['order_id'] ?? 0);
        $item_id = intval($_GET['item_id'] ?? 0);
        $type = sanitize_text_field($_GET['type'] ?? 'full'); // 'full' ou 'thumb'
        
        if (!$order_id || !$item_id) {
            wp_die('Paramètres manquants', 'Erreur', ['response' => 400]);
        }
        
        try {
            $this->serve_screenshot_file($order_id, $item_id, $type);
        } catch (Exception $e) {
            error_log('NFC: Erreur download screenshot: ' . $e->getMessage());
            wp_die('Erreur lors du téléchargement: ' . $e->getMessage(), 'Erreur', ['response' => 500]);
        }
    }
    
    /**
     * Affiche le screenshot dans le navigateur
     */
    public function view_screenshot() {
        // Vérifier permissions admin OU client
        if (!$this->can_view_screenshot()) {
            wp_die('Accès refusé', 'Erreur', ['response' => 403]);
        }
        
        // Vérifier nonce (admin ou client)
        $nonce_admin = wp_verify_nonce($_GET['nonce'] ?? '', 'nfc_admin_view');
        $order_id = intval($_GET['order_id'] ?? 0);
        $item_id = intval($_GET['item_id'] ?? 0);
        $nonce_customer = wp_verify_nonce($_GET['nonce'] ?? '', "nfc_customer_screenshot_{$order_id}_{$item_id}");
        
        if (!$nonce_admin && !$nonce_customer) {
            wp_die('Nonce invalide', 'Erreur', ['response' => 403]);
        }
        
        $type = sanitize_text_field($_GET['type'] ?? 'thumb');
        
        if (!$order_id || !$item_id) {
            wp_die('Paramètres manquants', 'Erreur', ['response' => 400]);
        }
        
        try {
            $this->display_screenshot_file($order_id, $item_id, $type);
        } catch (Exception $e) {
            error_log('NFC: Erreur view screenshot: ' . $e->getMessage());
            wp_die('Erreur lors de l\'affichage: ' . $e->getMessage(), 'Erreur', ['response' => 500]);
        }
    }
    
    /**
     * NOUVEAU : Vérifie les permissions de visualisation (admin ou client)
     */
    private function can_view_screenshot() {
        // Admin a toujours accès
        if (current_user_can('manage_woocommerce')) {
            return true;
        }
        
        // Vérifier accès client
        $order_id = intval($_GET['order_id'] ?? 0);
        if ($order_id) {
            $order = wc_get_order($order_id);
            if ($order) {
                // Client propriétaire
                $current_user_id = get_current_user_id();
                if ($current_user_id && $order->get_customer_id() == $current_user_id) {
                    return true;
                }
                
                // Accès invité avec clé de commande
                if (!$current_user_id) {
                    $order_key = $_GET['key'] ?? '';
                    if ($order_key && $order->get_order_key() === $order_key) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Sert le fichier logo
     */
    private function serve_logo_file($order_id, $item_id) {
        // Récupérer les données de la commande
        $order = wc_get_order($order_id);
        if (!$order) {
            throw new Exception('Commande introuvable');
        }
        
        $item = $order->get_item($item_id);
        if (!$item) {
            throw new Exception('Article introuvable');
        }
        
        // Récupérer les données image
        $image_data = $item->get_meta('_nfc_image_data');
        if (!$image_data) {
            throw new Exception('Aucune image personnalisée trouvée');
        }
        
        // Décoder l'image
        if (is_string($image_data)) {
            $image_data = json_decode($image_data, true);
        }
        
        if (!isset($image_data['data']) || !isset($image_data['name'])) {
            throw new Exception('Données image corrompues');
        }
        
        // Générer le fichier à partir du base64
        $file_path = $this->generate_logo_file($order_id, $item_id, $image_data);
        
        // Servir le fichier
        $this->serve_file($file_path, $image_data['name'], 'logo');
    }
    
    /**
     * Sert le fichier screenshot
     */
    private function serve_screenshot_file($order_id, $item_id, $type) {
        // Récupérer les données de la commande
        $order = wc_get_order($order_id);
        if (!$order) {
            throw new Exception('Commande introuvable');
        }
        
        $item = $order->get_item($item_id);
        if (!$item) {
            throw new Exception('Article introuvable');
        }
        
        // Récupérer les infos screenshot
        $screenshot_info = $item->get_meta('_nfc_screenshot_info');
        if (!$screenshot_info) {
            throw new Exception('Aucun screenshot trouvé');
        }
        
        if (is_string($screenshot_info)) {
            $screenshot_info = json_decode($screenshot_info, true);
        }
        
        // Vérifier si le fichier existe déjà
        $field_key = $type === 'thumb' ? 'thumbnail' : 'full_size';
        if (isset($screenshot_info[$field_key]['path']) && file_exists($screenshot_info[$field_key]['path'])) {
            $file_path = $screenshot_info[$field_key]['path'];
            $filename = $screenshot_info[$field_key]['filename'];
        } else {
            throw new Exception('Fichier screenshot non trouvé');
        }
        
        // Servir le fichier
        $this->serve_file($file_path, $filename, 'screenshot');
    }
    
    /**
     * Affiche le screenshot dans le navigateur
     */
    private function display_screenshot_file($order_id, $item_id, $type) {
        // Même logique que serve_screenshot_file mais avec headers différents
        $order = wc_get_order($order_id);
        if (!$order) {
            throw new Exception('Commande introuvable');
        }
        
        $item = $order->get_item($item_id);
        if (!$item) {
            throw new Exception('Article introuvable');
        }
        
        $screenshot_info = $item->get_meta('_nfc_screenshot_info');
        if (!$screenshot_info) {
            throw new Exception('Aucun screenshot trouvé');
        }
        
        if (is_string($screenshot_info)) {
            $screenshot_info = json_decode($screenshot_info, true);
        }
        
        $field_key = $type === 'thumb' ? 'thumbnail' : 'full_size';
        if (isset($screenshot_info[$field_key]['path']) && file_exists($screenshot_info[$field_key]['path'])) {
            $file_path = $screenshot_info[$field_key]['path'];
        } else {
            throw new Exception('Fichier screenshot non trouvé');
        }
        
        // Afficher dans le navigateur
        $this->display_file($file_path);
    }
    
    /**
     * Génère le fichier logo à partir des données base64
     */
    private function generate_logo_file($order_id, $item_id, $image_data) {
        $filename = "order-{$order_id}-item-{$item_id}-logo";
        
        // Déterminer l'extension
        $extension = 'png'; // Par défaut
        if (preg_match('/^data:image\/(\w+);base64,/', $image_data['data'], $matches)) {
            $type = $matches[1];
            $extension = $type === 'jpeg' ? 'jpg' : $type;
        }
        
        $file_path = $this->logos_dir . $filename . '.' . $extension;
        
        // Si le fichier existe déjà et est récent (moins de 1h), le réutiliser
        if (file_exists($file_path) && (time() - filemtime($file_path)) < 3600) {
            return $file_path;
        }
        
        // Extraire et décoder les données base64
        $base64_data = substr($image_data['data'], strpos($image_data['data'], ',') + 1);
        $decoded_data = base64_decode($base64_data);
        
        if ($decoded_data === false) {
            throw new Exception('Données image invalides');
        }
        
        // Sauvegarder le fichier
        $result = file_put_contents($file_path, $decoded_data);
        if ($result === false) {
            throw new Exception('Impossible de créer le fichier logo');
        }
        
        return $file_path;
    }
    
    /**
     * Sert un fichier avec headers de téléchargement
     */
    private function serve_file($file_path, $filename, $type) {
        if (!file_exists($file_path)) {
            throw new Exception('Fichier non trouvé');
        }
        
        // Nettoyer le nom de fichier
        $safe_filename = sanitize_file_name($filename);
        
        // Headers de téléchargement
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $safe_filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        // Nettoyer le buffer de sortie
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Lire et envoyer le fichier
        readfile($file_path);
        
        // Nettoyage optionnel (supprimer fichier temporaire après 1h)
        if ($type === 'logo' && (time() - filemtime($file_path)) > 3600) {
            @unlink($file_path);
        }
        
        exit;
    }
    
    /**
     * Affiche un fichier dans le navigateur
     */
    private function display_file($file_path) {
        if (!file_exists($file_path)) {
            throw new Exception('Fichier non trouvé');
        }
        
        // Déterminer le type MIME
        $mime_type = 'image/png'; // Par défaut
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $mime_type = 'image/jpeg';
                break;
            case 'png':
                $mime_type = 'image/png';
                break;
        }
        
        // Headers d'affichage
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: max-age=3600'); // Cache 1h
        
        // Nettoyer le buffer de sortie
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Lire et envoyer le fichier
        readfile($file_path);
        exit;
    }
    
    /**
     * Génère les URLs de téléchargement pour un article de commande
     */
    public static function get_download_urls($order_id, $item_id) {
        $nonce_download = wp_create_nonce('nfc_admin_download');
        $nonce_view = wp_create_nonce('nfc_admin_view');
        $admin_ajax_url = admin_url('admin-ajax.php');
        
        return [
            'logo_download' => add_query_arg([
                'action' => 'nfc_download_logo',
                'order_id' => $order_id,
                'item_id' => $item_id,
                'nonce' => $nonce_download
            ], $admin_ajax_url),
            
            'screenshot_download' => add_query_arg([
                'action' => 'nfc_download_screenshot',
                'order_id' => $order_id,
                'item_id' => $item_id,
                'type' => 'full',
                'nonce' => $nonce_download
            ], $admin_ajax_url),
            
            'screenshot_view' => add_query_arg([
                'action' => 'nfc_view_screenshot',
                'order_id' => $order_id,
                'item_id' => $item_id,
                'type' => 'thumb',
                'nonce' => $nonce_view
            ], $admin_ajax_url),
            
            'screenshot_view_full' => add_query_arg([
                'action' => 'nfc_view_screenshot',
                'order_id' => $order_id,
                'item_id' => $item_id,
                'type' => 'full',
                'nonce' => $nonce_view
            ], $admin_ajax_url)
        ];
    }
    
    /**
     * Nettoyage périodique des fichiers temporaires
     */
    public function cleanup_old_files() {
        $directories = [$this->logos_dir, $this->screenshots_dir];
        $max_age = 7 * 24 * 60 * 60; // 7 jours
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) continue;
            
            $files = glob($dir . '*');
            foreach ($files as $file) {
                if (is_file($file) && (time() - filemtime($file)) > $max_age) {
                    @unlink($file);
                }
            }
        }
        
        error_log('NFC: Nettoyage fichiers temporaires effectué');
    }
}

// Initialisation
new NFC_File_Handler();

// Planifier le nettoyage quotidien
if (!wp_next_scheduled('nfc_cleanup_files')) {
    wp_schedule_event(time(), 'daily', 'nfc_cleanup_files');
}

add_action('nfc_cleanup_files', function() {
    $file_handler = new NFC_File_Handler();
    $file_handler->cleanup_old_files();
});