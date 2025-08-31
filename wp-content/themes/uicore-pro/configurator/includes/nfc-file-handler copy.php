<?php
/**
 * NFC File Handler - Gestion des téléchargements admin
 * Génère et sert les fichiers images/screenshots à la demande
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_File_Handler
{

    private $upload_dir;
    private $screenshots_dir;
    private $logos_dir;

    public function __construct()
    {
        $this->init_directories();
        add_action('init', [$this, 'init']);
    }

    /**
     * Initialisation
     */
    public function init()
    {
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
    private function init_directories()
    {
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
    private function ensure_directory_exists($dir)
    {
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
    public function download_logo()
    {
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
        $type = sanitize_text_field($_GET['type'] ?? 'recto'); // ✅ NOUVEAU: support recto/verso

        if (!$order_id || !$item_id) {
            wp_die('Paramètres manquants', 'Erreur', ['response' => 400]);
        }

        try {
            $this->serve_logo_file($order_id, $item_id, $type); // ✅ NOUVEAU: passer le type
        } catch (Exception $e) {
            error_log('NFC: Erreur download logo: ' . $e->getMessage());
            wp_die('Erreur lors du téléchargement: ' . $e->getMessage(), 'Erreur', ['response' => 500]);
        }
    }

    /**
     * Télécharge le screenshot d'une commande
     */
    public function download_screenshot()
    {
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
    public function view_screenshot()
    {
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
    private function can_view_screenshot()
    {
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
    private function serve_logo_file($order_id, $item_id, $type = 'recto')
    {
        error_log("NFC DEBUG: serve_logo_file appelé - order:{$order_id}, item:{$item_id}, type:{$type}");

        // Récupérer les données de la commande
        $order = wc_get_order($order_id);
        if (!$order) {
            throw new Exception('Commande introuvable');
        }

        $item = $order->get_item($item_id);
        if (!$item) {
            throw new Exception('Article introuvable');
        }

        // ✅ DEBUG : Voir toutes les métadonnées
        $all_meta = $item->get_meta_data();
        error_log("NFC DEBUG: Toutes les métadonnées de l'item:");
        foreach ($all_meta as $meta) {
            error_log("  - " . $meta->key . " = " . substr($meta->value, 0, 100) . "...");
        }

        // Choisir les bonnes métadonnées selon le type
        $meta_key = '';
        switch ($type) {
            case 'verso':
                $meta_key = '_nfc_logo_verso_data';
                $image_data = $item->get_meta($meta_key);
                error_log("NFC DEBUG: Recherche verso dans {$meta_key} = " . ($image_data ? 'TROUVÉ' : 'VIDE'));

                if (!$image_data) {
                    error_log("NFC DEBUG: Verso vide, recherche dans config complète...");
                    $config_data = $item->get_meta('_nfc_config_complete');
                    if ($config_data) {
                        $config = json_decode($config_data, true);
                        error_log("NFC DEBUG: Config complète - clés: " . implode(', ', array_keys($config)));

                        if (isset($config['logoVerso'])) {
                            error_log("NFC DEBUG: logoVerso trouvé - clés: " . implode(', ', array_keys($config['logoVerso'])));
                            if (!empty($config['logoVerso']['data'])) {
                                $image_data = $config['logoVerso'];
                                error_log("NFC DEBUG: Logo verso récupéré depuis config, taille data: " . strlen($config['logoVerso']['data']));
                            } else {
                                error_log("NFC DEBUG: logoVerso.data est vide");
                            }
                        } else {
                            error_log("NFC DEBUG: Pas de logoVerso dans la config");
                        }
                    } else {
                        error_log("NFC DEBUG: Pas de config complète");
                    }
                }
                break;
            case 'recto':
            default:
                // Essayer plusieurs clés possibles pour recto
                $meta_key = '_nfc_image_recto_data';
                $image_data = $item->get_meta($meta_key);
                if (!$image_data) {
                    $meta_key = '_nfc_image_data'; // Fallback existant
                }
                break;
        }

        if (!$image_data) {
            error_log("NFC DEBUG: ÉCHEC - Aucune donnée image trouvée pour {$type}");
            throw new Exception("Aucun logo {$type} trouvé pour cet article");
        }

        error_log("NFC DEBUG: Données image récupérées, génération du fichier...");

        if (!$image_data) {
            throw new Exception("Aucun logo {$type} trouvé pour cet article");
        }

        // Décoder si c'est une chaîne JSON
        if (is_string($image_data)) {
            $image_data = json_decode($image_data, true);
        }

        if (!isset($image_data['data']) || !$image_data['data']) {
            throw new Exception('Données image corrompues');
        }

        // Déterminer le nom de fichier
        $filename = $image_data['name'] ?? "order-{$order_id}-logo-{$type}";

        // Générer le fichier à partir du base64
        $file_path = $this->generate_logo_file($order_id, $item_id, $image_data, $type); // ✅ NOUVEAU: passer le type

        // Servir le fichier avec le bon nom
        $download_filename = "commande-{$order_id}-{$type}-{$filename}";
        $this->serve_file($file_path, $download_filename, 'logo');
    }

    /**
     * Sert le fichier screenshot
     */
private function serve_screenshot_file($order_id, $item_id, $type)
{
    error_log("NFC DEBUG: serve_screenshot_file - order:{$order_id}, item:{$item_id}, type:{$type}");
    
    $order = wc_get_order($order_id);
    if (!$order) {
        throw new Exception('Commande introuvable');
    }

    $item = $order->get_item($item_id);
    if (!$item) {
        throw new Exception('Article introuvable');
    }

    // ✅ DEBUG COMPLET POUR COMMANDE 3466
    if ($order_id == 3466) {
        error_log("=== DEBUG COMPLET COMMANDE 3466 ITEM {$item_id} ===");
        
        // Voir TOUTES les métadonnées
        $all_meta = $item->get_meta_data();
        error_log("TOUTES LES MÉTADONNÉES:");
        foreach ($all_meta as $meta) {
            error_log("  - " . $meta->key . " = " . substr($meta->value, 0, 100) . "...");
        }
        
        // Voir _nfc_screenshot_data spécifiquement
        $screenshot_data = $item->get_meta('_nfc_screenshot_data');
        error_log("_nfc_screenshot_data: " . ($screenshot_data ? 'EXISTS (' . strlen($screenshot_data) . ' chars)' : 'NULL'));
        if ($screenshot_data) {
            if (is_string($screenshot_data)) {
                $decoded = json_decode($screenshot_data, true);
                if ($decoded) {
                    error_log("Screenshot data clés: " . implode(', ', array_keys($decoded)));
                    if (isset($decoded['thumbnail'])) {
                        error_log("Thumbnail length: " . strlen($decoded['thumbnail']));
                        error_log("Thumbnail start: " . substr($decoded['thumbnail'], 0, 50));
                    }
                    if (isset($decoded['full'])) {
                        error_log("Full length: " . strlen($decoded['full']));
                        error_log("Full start: " . substr($decoded['full'], 0, 50));
                    }
                } else {
                    error_log("Screenshot data JSON decode failed: " . json_last_error_msg());
                }
            } else {
                error_log("Screenshot data type: " . gettype($screenshot_data));
                error_log("Screenshot data value: " . print_r($screenshot_data, true));
            }
        }
        
        // Voir _nfc_screenshot_info
        $screenshot_info = $item->get_meta('_nfc_screenshot_info');
        error_log("_nfc_screenshot_info: " . ($screenshot_info ? 'EXISTS' : 'NULL'));
        
        // Voir _nfc_config_complete
        $config_complete = $item->get_meta('_nfc_config_complete');
        error_log("_nfc_config_complete: " . ($config_complete ? 'EXISTS (' . strlen($config_complete) . ' chars)' : 'NULL'));
        if ($config_complete) {
            $config = json_decode($config_complete, true);
            if ($config && isset($config['screenshot'])) {
                error_log("Config contient screenshot avec clés: " . implode(', ', array_keys($config['screenshot'])));
                if (isset($config['screenshot']['thumbnail'])) {
                    error_log("Config screenshot thumbnail length: " . strlen($config['screenshot']['thumbnail']));
                }
                if (isset($config['screenshot']['full'])) {
                    error_log("Config screenshot full length: " . strlen($config['screenshot']['full']));
                }
            } else {
                error_log("Config ne contient pas de screenshot");
            }
        }
        
        error_log("=== FIN DEBUG 3466 ===");
    }

    $file_path = null;
    $filename = "commande-{$order_id}-apercu-{$type}.png";

    // ✅ MÉTHODE 1: Fichiers physiques 
    $screenshot_info = $item->get_meta('_nfc_screenshot_info');
    if ($screenshot_info) {
        error_log("NFC: Tentative méthode 1 - screenshot_info");
        if (is_string($screenshot_info)) {
            $screenshot_info = json_decode($screenshot_info, true);
        }
        $field_key = $type === 'thumb' ? 'thumbnail' : 'full_size';
        if (isset($screenshot_info[$field_key]['path']) && file_exists($screenshot_info[$field_key]['path'])) {
            $file_path = $screenshot_info[$field_key]['path'];
            $filename = $screenshot_info[$field_key]['filename'];
            error_log("NFC: ✅ Méthode 1 réussie: {$file_path}");
        } else {
            error_log("NFC: ❌ Méthode 1 - fichier physique non trouvé");
        }
    } else {
        error_log("NFC: ❌ Méthode 1 - pas de screenshot_info");
    }

    // ✅ MÉTHODE 2: PRIORITÉ Base64
    if (!$file_path) {
        error_log("NFC: Tentative méthode 2 - base64 data");
        $screenshot_data_raw = $item->get_meta('_nfc_screenshot_data');
        
        if ($screenshot_data_raw) {
            // Décoder le JSON
            $screenshot_data = json_decode($screenshot_data_raw, true);
            if (!$screenshot_data) {
                error_log("NFC: ❌ Erreur décodage JSON screenshot data: " . json_last_error_msg());
            } else {
                $base64_data = null;
                if ($type === 'thumb' && isset($screenshot_data['thumbnail']) && !empty($screenshot_data['thumbnail'])) {
                    $base64_data = $screenshot_data['thumbnail'];
                    error_log("NFC: ✅ Base64 thumbnail trouvé (" . strlen($base64_data) . " chars)");
                } elseif ($type === 'full' && isset($screenshot_data['full']) && !empty($screenshot_data['full'])) {
                    $base64_data = $screenshot_data['full'];
                    error_log("NFC: ✅ Base64 full trouvé (" . strlen($base64_data) . " chars)");
                }
                
                if ($base64_data) {
                    try {
                        $file_path = $this->create_temp_file_from_base64($base64_data, $order_id, $item_id, $type);
                        error_log("NFC: ✅ Méthode 2 réussie: {$file_path}");
                    } catch (Exception $e) {
                        error_log("NFC: ❌ Méthode 2 échouée: " . $e->getMessage());
                    }
                }
            }
        } else {
            error_log("NFC: ❌ Métadonnée _nfc_screenshot_data introuvable");
        }
    }

    // ✅ MÉTHODE 3: Config fallback
    if (!$file_path) {
        error_log("NFC: Tentative méthode 3 - génération config");
        $config_data = $item->get_meta('_nfc_config_complete');
        if ($config_data) {
            $file_path = $this->generate_screenshot_from_config($order_id, $item_id, $config_data, $type);
            error_log("NFC: ⚠️ Méthode 3 utilisée (placeholder): {$file_path}");
        } else {
            error_log("NFC: ❌ Méthode 3 - pas de config_complete");
        }
    }

    if (!$file_path || !file_exists($file_path)) {
        error_log("NFC: ❌ ÉCHEC TOTAL - aucun fichier disponible");
        throw new Exception('Aucun screenshot disponible');
    }

    error_log("NFC: 🎯 FINAL - Utilisation du fichier: {$file_path}");

    // Servir le fichier
    $this->serve_file($file_path, $filename, 'screenshot');
}

    private function create_temp_file_from_base64($base64_data, $order_id, $item_id, $type)
    {
        // Supprimer le préfixe data:image si présent
        if (strpos($base64_data, 'data:image') === 0) {
            $base64_data = substr($base64_data, strpos($base64_data, ',') + 1);
        }

        // Décoder les données
        $image_data = base64_decode($base64_data);
        if (!$image_data) {
            throw new Exception('Données base64 invalides');
        }

        // Créer dossier temporaire si nécessaire
        $temp_dir = $this->screenshots_dir . 'temp/';
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
            // Sécuriser le dossier
            file_put_contents($temp_dir . '.htaccess', "Order deny,allow\nDeny from all\nAllow from 127.0.0.1\n");
        }

        // Nom de fichier temporaire
        $filename = "temp-{$order_id}-{$item_id}-{$type}-" . md5($base64_data) . ".png";
        $file_path = $temp_dir . $filename;

        // Vérifier si le fichier existe déjà (cache)
        if (file_exists($file_path) && (time() - filemtime($file_path)) < 3600) {
            return $file_path; // Utiliser le cache 1h
        }

        // Sauvegarder les données
        if (file_put_contents($file_path, $image_data) === false) {
            throw new Exception('Impossible de créer le fichier temporaire');
        }

        return $file_path;
    }


    /**
     * ✅ NOUVEAU: Génère un screenshot à la volée depuis la config (fallback)
     */
    private function generate_screenshot_from_config($order_id, $item_id, $config_data, $type = 'full')
    {
        $config = json_decode($config_data, true);
        if (!$config) {
            throw new Exception('Configuration invalide');
        }

        // Dimensions selon le type
        $width = $type === 'thumb' ? 400 : 800;
        $height = $type === 'thumb' ? 250 : 500;

        // Créer l'image avec les vraies couleurs NFC
        $image = imagecreate($width, $height);

        // Couleurs de la carte selon la config
        $card_colors = [
            'noir' => ['bg' => [45, 45, 45], 'text' => [255, 255, 255]],
            'blanc' => ['bg' => [250, 250, 250], 'text' => [45, 45, 45]],
            'bleu' => ['bg' => [0, 64, 193], 'text' => [255, 255, 255]],
            'rouge' => ['bg' => [220, 38, 127], 'text' => [255, 255, 255]]
        ];

        $color_config = $card_colors[$config['color']] ?? $card_colors['blanc'];

        // Couleurs
        $bg_color = imagecolorallocate($image, ...$color_config['bg']);
        $text_color = imagecolorallocate($image, ...$color_config['text']);
        $accent_color = imagecolorallocate($image, 100, 150, 255);

        // Fond de carte
        imagefill($image, 0, 0, $bg_color);

        // Bordures arrondies simulées
        $border_color = imagecolorallocate($image, max(0, $color_config['bg'][0] - 30), max(0, $color_config['bg'][1] - 30), max(0, $color_config['bg'][2] - 30));
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $border_color);
        imagerectangle($image, 1, 1, $width - 2, $height - 2, $border_color);

        // Logo NFC France (simulé)
        $logo_text = "NFC FRANCE";
        $x = 20;
        $y = 20;
        imagestring($image, 5, $x, $y, $logo_text, $accent_color);

        // Nom de l'utilisateur si présent
        if (isset($config['user'])) {
            $user_name = trim(($config['user']['firstName'] ?? '') . ' ' . ($config['user']['lastName'] ?? ''));
            if ($user_name) {
                $y += 40;
                imagestring($image, 4, $x, $y, $user_name, $text_color);
            }
        }

        // Informations sur la carte
        $y += 40;
        imagestring($image, 3, $x, $y, "Couleur: " . ucfirst($config['color']), $text_color);

        if (isset($config['image']['name'])) {
            $y += 25;
            $logo_name = substr($config['image']['name'], 0, 30);
            imagestring($image, 3, $x, $y, "Logo: " . $logo_name, $text_color);
        }

        // Numéro de commande en bas
        $bottom_text = "Commande #{$order_id}";
        $x_bottom = $width - (strlen($bottom_text) * 8) - 20;
        $y_bottom = $height - 25;
        imagestring($image, 2, $x_bottom, $y_bottom, $bottom_text, $accent_color);

        // Sauvegarder le fichier
        $filename = "generated-{$order_id}-{$item_id}-{$type}-" . md5($config_data) . ".png";
        $file_path = $this->screenshots_dir . $filename;

        if (!imagepng($image, $file_path)) {
            imagedestroy($image);
            throw new Exception('Impossible de sauvegarder le screenshot généré');
        }

        imagedestroy($image);
        return $file_path;
    }

    /**
     * ✅ NOUVEAU: Méthode spécifique pour clients (appelée depuis NFC_Customer_Integration)
     */
    public function display_customer_screenshot($order_id, $item_id, $type = 'thumb')
    {
        try {
            $this->display_screenshot_file($order_id, $item_id, $type);
        } catch (Exception $e) {
            // En cas d'erreur, essayer de générer depuis la config
            $order = wc_get_order($order_id);
            $item = $order->get_item($item_id);
            $config_data = $item->get_meta('_nfc_config_complete');

            if ($config_data) {
                $file_path = $this->generate_screenshot_from_config($order_id, $item_id, $config_data, $type);
                $this->display_file($file_path);
            } else {
                throw $e; // Re-lancer l'erreur originale
            }
        }
    }

    /**
     * Affiche le screenshot dans le navigateur
     */
private function display_screenshot_file($order_id, $item_id, $type)
{
    error_log("NFC DEBUG: display_screenshot_file - order:{$order_id}, item:{$item_id}, type:{$type}");
    
    $order = wc_get_order($order_id);
    if (!$order) {
        throw new Exception('Commande introuvable');
    }

    $item = $order->get_item($item_id);
    if (!$item) {
        throw new Exception('Article introuvable');
    }

    $file_path = null;

    // ✅ MÉTHODE 1: Fichiers physiques (_nfc_screenshot_info) - nouvelles commandes
    $screenshot_info = $item->get_meta('_nfc_screenshot_info');
    if ($screenshot_info) {
        error_log("NFC: Tentative méthode 1 - screenshot_info");
        if (is_string($screenshot_info)) {
            $screenshot_info = json_decode($screenshot_info, true);
        }

        $field_key = $type === 'thumb' ? 'thumbnail' : 'full_size';
        if (isset($screenshot_info[$field_key]['path']) && file_exists($screenshot_info[$field_key]['path'])) {
            $file_path = $screenshot_info[$field_key]['path'];
            error_log("NFC: ✅ Méthode 1 réussie: {$file_path}");
        }
    }

    // ✅ MÉTHODE 2: PRIORITÉ ABSOLUE - Base64 depuis _nfc_screenshot_data
    if (!$file_path) {
        error_log("NFC: Tentative méthode 2 - base64 data");
        $screenshot_data = $item->get_meta('_nfc_screenshot_data');
        
        if ($screenshot_data) {
            if (is_string($screenshot_data)) {
                $screenshot_data = json_decode($screenshot_data, true);
            }
            
            $base64_data = null;
            if ($type === 'thumb' && isset($screenshot_data['thumbnail'])) {
                $base64_data = $screenshot_data['thumbnail'];
                error_log("NFC: Base64 thumbnail trouvé (" . strlen($base64_data) . " chars)");
            } elseif ($type === 'full' && isset($screenshot_data['full'])) {
                $base64_data = $screenshot_data['full'];
                error_log("NFC: Base64 full trouvé (" . strlen($base64_data) . " chars)");
            }
            
            if ($base64_data) {
                try {
                    $file_path = $this->create_temp_file_from_base64($base64_data, $order_id, $item_id, $type);
                    error_log("NFC: ✅ Méthode 2 réussie: {$file_path}");
                } catch (Exception $e) {
                    error_log("NFC: ❌ Méthode 2 échouée: " . $e->getMessage());
                }
            }
        }
    }

    // ✅ MÉTHODE 3: Fallback config (SEULEMENT si base64 pas trouvé)
    if (!$file_path) {
        error_log("NFC: Tentative méthode 3 - génération config");
        $config_data = $item->get_meta('_nfc_config_complete');
        if ($config_data) {
            // D'abord essayer de récupérer depuis la config complète
            $config = json_decode($config_data, true);
            if ($config && isset($config['screenshot'])) {
                error_log("NFC: Config contient screenshot, tentative récupération base64");
                
                $base64_from_config = null;
                if ($type === 'thumb' && isset($config['screenshot']['thumbnail'])) {
                    $base64_from_config = $config['screenshot']['thumbnail'];
                } elseif ($type === 'full' && isset($config['screenshot']['full'])) {
                    $base64_from_config = $config['screenshot']['full'];
                }
                
                if ($base64_from_config) {
                    try {
                        $file_path = $this->create_temp_file_from_base64($base64_from_config, $order_id, $item_id, $type);
                        error_log("NFC: ✅ Méthode 3 base64 réussie: {$file_path}");
                    } catch (Exception $e) {
                        error_log("NFC: ❌ Méthode 3 base64 échouée: " . $e->getMessage());
                    }
                }
            }
            
            // Si pas de base64 dans config, générer placeholder
            if (!$file_path) {
                $file_path = $this->generate_screenshot_from_config($order_id, $item_id, $config_data, $type);
                error_log("NFC: ⚠️ Méthode 3 placeholder: {$file_path}");
            }
        }
    }

    if (!$file_path || !file_exists($file_path)) {
        throw new Exception('Aucun screenshot disponible après tous les fallbacks');
    }

    // Afficher le fichier
    $this->display_file($file_path);
}


    /**
     * ✅ NOUVEAU: Affiche un fichier directement dans le navigateur avec headers optimisés
     */
    private function display_file($file_path)
    {
        if (!file_exists($file_path)) {
            throw new Exception('Fichier non trouvé pour affichage');
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
            case 'gif':
                $mime_type = 'image/gif';
                break;
            case 'webp':
                $mime_type = 'image/webp';
                break;
        }

        // Headers d'affichage optimisés
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: public, max-age=3600, must-revalidate'); // Cache 1h
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file_path)) . ' GMT');

        // Headers de sécurité
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');

        // Nettoyer tout buffer de sortie existant
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Lire et envoyer le fichier
        if (!readfile($file_path)) {
            throw new Exception('Impossible de lire le fichier');
        }

        exit;
    }

    /**
     * Génère le fichier logo à partir des données base64
     */
    private function generate_logo_file($order_id, $item_id, $image_data, $type = 'recto')
    {
        $filename = "order-{$order_id}-item-{$item_id}-{$type}"; // ✅ NOUVEAU: inclure le type

        // Déterminer l'extension
        $extension = 'png'; // Par défaut
        if (isset($image_data['data']) && preg_match('/^data:image\/(\w+);base64,/', $image_data['data'], $matches)) {
            $image_type = $matches[1];
            $extension = $image_type === 'jpeg' ? 'jpg' : $image_type;
        }

        $file_path = $this->logos_dir . $filename . '.' . $extension;

        // Si le fichier existe déjà et est récent (moins de 1h), le réutiliser
        if (file_exists($file_path) && (time() - filemtime($file_path)) < 3600) {
            return $file_path;
        }

        // ✅ AMÉLIORÉ: Extraction base64 plus robuste
        $data = $image_data['data'];
        if (strpos($data, 'data:') === 0) {
            $base64_data = substr($data, strpos($data, ',') + 1);
        } else {
            $base64_data = $data; // Déjà en base64 pur
        }

        $decoded_data = base64_decode($base64_data);

        if ($decoded_data === false) {
            throw new Exception('Données image base64 invalides');
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
    private function serve_file($file_path, $filename, $type)
    {
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
     * Génère les URLs de téléchargement pour un article de commande
     */
    public static function get_download_urls($order_id, $item_id)
    {
        $nonce_download = wp_create_nonce('nfc_admin_download');
        $nonce_view = wp_create_nonce('nfc_admin_view');
        $admin_ajax_url = admin_url('admin-ajax.php');

        return [
            // ✅ NOUVEAU: URLs séparées recto/verso
            'logo_recto_download' => add_query_arg([
                'action' => 'nfc_download_logo',
                'order_id' => $order_id,
                'item_id' => $item_id,
                'type' => 'recto',
                'nonce' => $nonce_download
            ], $admin_ajax_url),

            'logo_verso_download' => add_query_arg([
                'action' => 'nfc_download_logo',
                'order_id' => $order_id,
                'item_id' => $item_id,
                'type' => 'verso',
                'nonce' => $nonce_download
            ], $admin_ajax_url),

            'screenshot_download' => add_query_arg([
                'action' => 'nfc_download_screenshot',
                'order_id' => $order_id,
                'item_id' => $item_id,
                'type' => 'full',
                'nonce' => $nonce_download
            ], $admin_ajax_url),

            // ✅ NOUVEAU: Screenshot thumb séparé
            'screenshot_thumb_download' => add_query_arg([
                'action' => 'nfc_download_screenshot',
                'order_id' => $order_id,
                'item_id' => $item_id,
                'type' => 'thumb',
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
    public function cleanup_old_files()
    {
        $directories = [$this->logos_dir, $this->screenshots_dir];
        $max_age = 7 * 24 * 60 * 60; // 7 jours

        foreach ($directories as $dir) {
            if (!is_dir($dir))
                continue;

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

add_action('nfc_cleanup_files', function () {
    $file_handler = new NFC_File_Handler();
    $file_handler->cleanup_old_files();
});