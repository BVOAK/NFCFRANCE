<?php
/**
 * NFC Dashboard Manager - ÉTAPE 2A : Routing des pages
 * 
 * Ajout du système de routing pour charger dynamiquement les pages
 * selon le paramètre ?page=
 */

if (!defined('ABSPATH')) {
    exit;
}

class NFC_Dashboard_Manager
{

    private $plugin_url;
    private $plugin_path;
    private $current_page;
    private $allowed_pages;

    public function __construct()
    {
        $this->plugin_url = plugin_dir_url(dirname(__FILE__, 2));
        $this->plugin_path = plugin_dir_path(dirname(__FILE__, 2));

        // Pages autorisées
        $this->allowed_pages = [
            'overview' => 'Vue d\'ensemble',
            'vcard-edit' => 'Ma vCard',
            'qr-codes' => 'QR Codes',
            'contacts' => 'Mes contacts',
            'statistics' => 'Statistiques',
            'preview' => 'Aperçu public'
        ];

        error_log('NFC_Dashboard_Manager: Initialisation ÉTAPE 2A - Routing');
    }

    /**
     * Initialisation des hooks WordPress
     */
    public function init()
    {
        // Hooks WooCommerce Mon Compte (pour l'onglet seulement)
        add_filter('woocommerce_account_menu_items', [$this, 'add_dashboard_menu_item'], 40);
        add_action('woocommerce_account_nfc-dashboard_endpoint', [$this, 'dashboard_endpoint_fallback']);

        // Enregistrer l'endpoint
        add_action('init', [$this, 'add_dashboard_endpoint']);
        add_action('init', [$this, 'flush_rewrite_rules_maybe']);

        // ULTIMATE BYPASS : Interception au niveau parse_request
        add_action('parse_request', [$this, 'ultimate_bypass_dashboard'], 1);

        error_log('NFC_Dashboard_Manager: Étape 2A initialisée avec routing');
    }

    /**
     * ULTIMATE BYPASS - Interception parse_request (INCHANGÉ)
     */
    public function ultimate_bypass_dashboard($wp)
    {
        // Vérifier l'URL directement
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';

        // Chercher le pattern /mon-compte/nfc-dashboard
        if (!preg_match('#/mon-compte/nfc-dashboard/?#', $request_uri)) {
            return;
        }

        error_log('NFC_Dashboard: ULTIMATE BYPASS ACTIVÉ pour: ' . $request_uri);

        // Vérifier que l'utilisateur est connecté
        if (!is_user_logged_in()) {
            auth_redirect();
            exit;
        }

        // NOUVEAU : Déterminer la page courante
        $this->current_page = $this->get_current_page();

        // BYPASS TOTAL - Rendu avec routing
        $this->render_dashboard_with_routing();
        exit; // STOP WordPress complètement
    }

    /**
     * NOUVEAU : Déterminer la page courante
     */
    private function get_current_page()
    {
        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'overview';

        // Vérifier que la page est autorisée
        if (!array_key_exists($page, $this->allowed_pages)) {
            $page = 'overview';
        }

        error_log("NFC_Dashboard: Page courante détectée: {$page}");

        return $page;
    }

    /**
     * NOUVEAU : Rendu avec routing
     */
    private function render_dashboard_with_routing()
    {
        // Nettoyer tous les buffers de sortie
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Headers HTTP pour une page complète
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
        }

        // Récupérer l'utilisateur et ses vCards
        $current_user = wp_get_current_user();
        $user_vcards = $this->get_user_vcards($current_user->ID);

        if (empty($user_vcards)) {
            $this->render_no_vcard_with_assets();
            return;
        }

        $primary_vcard = $user_vcards[0];

        // Données utilisateur pour la sidebar
        $first_name = get_post_meta($primary_vcard->ID, 'first_name', true) ?: $current_user->first_name ?: 'User';
        $last_name = get_post_meta($primary_vcard->ID, 'last_name', true) ?: $current_user->last_name ?: 'NFC';

        // Variables globales pour les templates
        global $nfc_vcard, $nfc_current_page;
        $nfc_vcard = $primary_vcard;
        $nfc_current_page = $this->current_page;

        // NOUVEAU TEMPLATE avec routing
        $this->render_dashboard_template($primary_vcard, $this->current_page, $current_user, $first_name, $last_name);
    }

    /**
     * NOUVEAU : Template avec routing intégré
     */
    private function render_dashboard_template($vcard, $current_page, $current_user, $first_name, $last_name)
    {
        $vcard_id = $vcard->ID;
        $profile_image = get_post_meta($vcard_id, 'profile_image', true);

        // Configuration JavaScript
        $dashboard_config = [
            'vcard_id' => $vcard_id,
            'ajax_url' => admin_url('admin-ajax.php'),
            'api_url' => home_url('/wp-json/gtmi_vcard/v1/'),
            'current_page' => $current_page,
            'user_id' => $current_user->ID,
            'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
            'public_url' => get_permalink($vcard_id),
            'home_url' => home_url()
        ];

        // Pages disponibles
        $dashboard_pages = [
            'overview' => ['title' => 'Vue d\'ensemble', 'icon' => 'fas fa-home'],
            'vcard-edit' => ['title' => 'Ma vCard', 'icon' => 'fas fa-id-card'],
            'qr-codes' => ['title' => 'Codes QR', 'icon' => 'fas fa-qrcode'],
            'contacts' => ['title' => 'Mes contacts', 'icon' => 'fas fa-users'],
            'statistics' => ['title' => 'Statistiques', 'icon' => 'fas fa-chart-line'],
            'preview' => ['title' => 'Aperçu public', 'icon' => 'fas fa-eye']
        ];

        $current_page_data = $dashboard_pages[$current_page] ?? $dashboard_pages['overview'];
        ?>
        <!DOCTYPE html>
        <html lang="fr">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($current_page_data['title']); ?> - Dashboard NFC</title>

            <!-- Favicon -->
            <link rel="icon" type="image/x-icon" href="<?php echo get_site_icon_url(); ?>">

            <!-- ASSETS EXTERNES -->
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

            <!-- CSS Dashboard -->
            <link href="<?php echo $this->plugin_url; ?>assets/css/dashboard.css?v=<?php echo time(); ?>" rel="stylesheet">
            <?php if ($current_page === 'vcard-edit') { ?>
                <link href="<?php echo $this->plugin_url; ?>assets/css/vcard-edit.css?v=<?php echo time(); ?>" rel="stylesheet">
            <?php } ?>
            <?php if ($current_page === 'contacts') { ?>
                <link href="<?php echo $this->plugin_url; ?>assets/css/contacts-manager.css?v=<?php echo time(); ?>"
                    rel="stylesheet">
            <?php } ?>
            <?php if ($current_page === 'statitics') { ?>
                <link href="<?php echo $this->plugin_url; ?>assets/css/statitics.css?v=<?php echo time(); ?>" rel="stylesheet">
            <?php } ?>
        </head>

        <body class="nfc-dashboard-body">
            <div class="nfc-dashboard-app">

                <!-- SIDEBAR -->
                <nav class="nfc-sidebar" id="nfcSidebar">
                    <div class="nfc-sidebar-header">
                        <a href="?page=overview" class="nfc-sidebar-logo">
                            Votre dashboard
                    </div>

                    <div class="nfc-sidebar-nav">
                        <div class="nfc-nav-section">
                            <div class="nfc-nav-section-title">Dashboard</div>
                            <div class="nfc-nav-item">
                                <a href="?page=overview"
                                    class="nfc-nav-link <?php echo $current_page === 'overview' ? 'active' : ''; ?>">
                                    <i class="fas fa-home"></i>
                                    Vue d'ensemble
                                </a>
                            </div>
                        </div>

                        <div class="nfc-nav-section">
                            <div class="nfc-nav-section-title">Ma vCard</div>
                            <div class="nfc-nav-item">
                                <a href="?page=vcard-edit"
                                    class="nfc-nav-link <?php echo $current_page === 'vcard-edit' ? 'active' : ''; ?>">
                                    <i class="fas fa-id-card"></i>
                                    Modifier ma vCard
                                </a>
                            </div>
                            <!-- <div class="nfc-nav-item">
                                <a href="?page=qr-codes"
                                    class="nfc-nav-link <?php echo $current_page === 'qr-codes' ? 'active' : ''; ?>">
                                    <i class="fas fa-qrcode"></i>
                                    QR Codes
                                    <span class="nfc-nav-badge">2</span>
                                </a>
                            </div> -->
                            <div class="nfc-nav-item">
                                <a href="?page=preview"
                                    class="nfc-nav-link <?php echo $current_page === 'preview' ? 'active' : ''; ?>">
                                    <i class="fas fa-eye"></i>
                                    Aperçu public
                                </a>
                            </div>
                        </div>

                        <div class="nfc-nav-section">
                            <div class="nfc-nav-section-title">Contacts & Stats</div>
                            <div class="nfc-nav-item">
                                <a href="?page=contacts"
                                    class="nfc-nav-link <?php echo $current_page === 'contacts' ? 'active' : ''; ?>">
                                    <i class="fas fa-users"></i>
                                    Mes contacts
                                    <span class="nfc-nav-badge">12</span>
                                </a>
                            </div>
                            <div class="nfc-nav-item">
                                <a href="?page=statistics"
                                    class="nfc-nav-link <?php echo $current_page === 'statistics' ? 'active' : ''; ?>">
                                    <i class="fas fa-chart-line"></i>
                                    Statistiques
                                </a>
                            </div>
                        </div>

                        <div class="nfc-nav-section">
                            <div class="nfc-nav-section-title">Mon compte</div>
                            <div class="nfc-nav-item">
                                <a href="<?php echo wc_get_account_endpoint_url('dashboard'); ?>" class="nfc-nav-link">
                                    <i class="fas fa-user"></i>
                                    Mon compte
                                </a>
                            </div>
                            <div class="nfc-nav-item">
                                <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" class="nfc-nav-link">
                                    <i class="fas fa-shopping-bag"></i>
                                    Mes commandes
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="nfc-sidebar-user">
                        <div class="nfc-sidebar-user-info">
                            <div class="nfc-sidebar-user-avatar">
                                <?php echo esc_html(strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1))); ?>
                            </div>
                            <div class="nfc-sidebar-user-details">
                                <h6><?php echo esc_html($first_name . ' ' . $last_name); ?></h6>
                                <small><?php echo esc_html($current_user->user_email); ?></small>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- CONTENU PRINCIPAL -->
                <main class="nfc-main-content">
                    <header class="nfc-main-header d-md-none d-sm-block">
                        <div class="nfc-page-title">
                            <button class="nfc-mobile-menu-btn me-3" onclick="toggleSidebar()">
                                <i class="fas fa-bars"></i>
                            </button>
                            <!-- <h1>
                                <?php echo esc_html($current_page_data['title']); ?>
                            </h1> -->
                        </div>

                        <!-- <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-outline-primary btn-sm" onclick="shareVCard()">
                                <i class="fas fa-share-alt me-2"></i>
                                Partager
                            </button>
                            <a href="<?php echo wc_get_account_endpoint_url('dashboard'); ?>"
                                class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour
                            </a>
                        </div> -->
                    </header>

                    <!-- ZONE CONTENU AVEC ROUTING -->
                    <div class="nfc-content-area fade-in">
                        <?php $this->render_page_content($vcard, $current_page); ?>
                    </div>
                </main>
            </div>

            <!-- Loading overlay -->
            <div id="nfc-dashboard-loading"
                class="position-fixed top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center"
                style="background: rgba(0, 0, 0, 0.7); z-index: 9999;">
                <div class="bg-white rounded-3 shadow p-4 text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mb-0 fw-medium">Chargement...</p>
                </div>
            </div>

            <!-- SCRIPTS EXTERNES -->
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
            <script src="<?php echo $this->plugin_url; ?>assets/js/dashboard/stats-commons.js?v=<?php echo time(); ?>"></script>
            <?php if ($current_page === 'overview'): ?>
                <script src="<?php echo $this->plugin_url; ?>assets/js/dashboard/overview.js?v=<?php echo time(); ?>"></script>
            <?php endif; ?>
            <?php if ($current_page === 'vcard-edit') { ?>
                <script src="<?php echo $this->plugin_url; ?>assets/js/dashboard/vcard-editor.js?v=<?php echo time(); ?>"></script>
            <?php } ?>
            <?php if ($current_page === 'contacts') { ?>
                <script type="text/javascript">
                    // Configuration globale pour NFCContacts
                    window.nfcContactsConfig = {
                        vcard_id: <?php echo json_encode($vcard->ID); ?>,
                        ajax_url: <?php echo json_encode(admin_url('admin-ajax.php')); ?>,
                        api_url: <?php echo json_encode(home_url('/wp-json/gtmi_vcard/v1/')); ?>,
                        nonce: <?php echo json_encode(wp_create_nonce('nfc_dashboard_nonce')); ?>,
                        public_url: <?php echo json_encode(get_permalink($vcard->ID)); ?>,
                        user_name: <?php echo json_encode($first_name . ' ' . $last_name); ?>,
                        i18n: {
                            loading: <?php echo json_encode(__('Chargement...', 'gtmi_vcard')); ?>,
                            error: <?php echo json_encode(__('Une erreur est survenue', 'gtmi_vcard')); ?>,
                            success: <?php echo json_encode(__('Action réalisée avec succès', 'gtmi_vcard')); ?>,
                            confirm_delete: <?php echo json_encode(__('Êtes-vous sûr de vouloir supprimer ce contact ?', 'gtmi_vcard')); ?>,
                            confirm_delete_multiple: <?php echo json_encode(__('Êtes-vous sûr de vouloir supprimer ces contacts ?', 'gtmi_vcard')); ?>,
                            no_contacts: <?php echo json_encode(__('Aucun contact trouvé', 'gtmi_vcard')); ?>,
                            search_placeholder: <?php echo json_encode(__('Rechercher un contact...', 'gtmi_vcard')); ?>
                        }
                    };

                    // Debug immédiat
                    console.log('📧 Configuration NFCContacts injectée AVANT script:', window.nfcContactsConfig);

                    // Vérification que tout est là
                    if (window.nfcContactsConfig.vcard_id && window.nfcContactsConfig.api_url) {
                        console.log('✅ Configuration NFCContacts valide');
                    } else {
                        console.error('❌ Configuration NFCContacts invalide:', window.nfcContactsConfig);
                    }
                </script>
                <script
                    src="<?php echo $this->plugin_url; ?>assets/js/dashboard/contacts-manager.js?v=<?php echo time(); ?>"></script>

            <?php } ?>
            <?php if ($current_page === 'statistics') { ?>
                <script>
                    // Configuration STATS_CONFIG directement ici
                    window.STATS_CONFIG = {
                        vcard_id: <?php echo json_encode($vcard->ID); ?>,
                        ajax_url: <?php echo json_encode(admin_url('admin-ajax.php')); ?>,
                        api_url: <?php echo json_encode(home_url('/wp-json/gtmi_vcard/v1/')); ?>,
                        nonce: <?php echo json_encode(wp_create_nonce('nfc_dashboard_nonce')); ?>,
                        public_url: <?php echo json_encode(get_permalink($vcard->ID)); ?>,
                        colors: {
                            primary: '#0040C1',
                            secondary: '#667eea',
                            success: '#10b981',
                            info: '#3b82f6',
                            warning: '#f59e0b',
                            danger: '#ef4444'
                        }
                    };
                    console.log('📊 STATS_CONFIG injecté:', window.STATS_CONFIG);
                </script>
                <script src="<?php echo $this->plugin_url; ?>assets/js/dashboard/statistics.js?v=<?php echo time(); ?>"></script>

            <?php } ?>

            <!-- Configuration JavaScript -->
            <script type="text/javascript">
                window.nfcDashboardConfig = <?php echo json_encode($dashboard_config, JSON_UNESCAPED_UNICODE); ?>;

                function toggleSidebar() {
                    const sidebar = document.getElementById('nfcSidebar');
                    if (sidebar) {
                        sidebar.classList.toggle('show');
                    }
                }

                function shareVCard() {
                    const url = window.nfcDashboardConfig.public_url;
                    if (navigator.share) {
                        navigator.share({
                            title: 'Ma vCard NFC',
                            text: 'Découvrez mes informations de contact',
                            url: url
                        });
                    } else {
                        navigator.clipboard.writeText(url).then(() => {
                            alert('URL copiée : ' + url);
                        });
                    }
                }

                document.addEventListener('click', function (e) {
                    const sidebar = document.getElementById('nfcSidebar');
                    const menuBtn = document.querySelector('.nfc-mobile-menu-btn');

                    if (window.innerWidth <= 1024 &&
                        sidebar && menuBtn &&
                        !sidebar.contains(e.target) &&
                        !menuBtn.contains(e.target) &&
                        sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                });

                console.log('🚀 Dashboard NFC - ÉTAPE 2A : Routing fonctionnel');
                console.log('📊 Configuration:', window.nfcDashboardConfig);
            </script>
        </body>

        </html>
        <?php
    }

    /**
     * NOUVEAU : Render du contenu selon la page
     */
    private function render_page_content($vcard, $current_page)
    {
        echo '<div class="container-fluid">';

        switch ($current_page) {
            case 'overview':
                $this->render_overview_page($vcard);
                break;

            case 'vcard-edit':
                $this->render_vcard_edit_page($vcard);
                break;

            case 'qr-codes':
                $this->render_qr_codes_page($vcard);
                break;

            case 'contacts':
                $this->render_contacts_page($vcard);
                break;

            case 'statistics':
                $this->render_statistics_page($vcard);
                break;

            case 'preview':
                $this->render_preview_page($vcard);
                break;

            default:
                $this->render_overview_page($vcard);
        }

        echo '</div>';
    }

    /**
     * PAGES INDIVIDUELLES - VERSION TEST
     */
    private function render_overview_page($vcard) {
        $user_id = get_current_user_id();
        $products_summary = nfc_get_user_products_summary($user_id);
        
        echo '<div class="dashboard-overview">';
        
        // Header avec résumé
        $this->render_dashboard_header($products_summary);
        
        // Sections selon les produits de l'utilisateur
        if ($products_summary['has_vcard']) {
            $this->render_vcard_section($products_summary);
        }
        
        if ($products_summary['has_google_reviews']) {
            $this->render_google_reviews_section($products_summary);
        }
        
        // Si aucun produit
        if (!$products_summary['has_vcard'] && !$products_summary['has_google_reviews']) {
            $this->render_no_products_message();
        }
        
        echo '</div>';
    }

    private function render_dashboard_header($products_summary) {
        ?>
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1>Bienvenue sur votre Dashboard NFC</h1>
                <p>Gérez tous vos profils depuis cette interface unifiée.</p>
            </div>
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-icon">👤</div>
                    <div class="summary-content">
                        <h3><?php echo count($products_summary['vcard_profiles']); ?></h3>
                        <p>Profils vCard</p>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">⭐</div>
                    <div class="summary-content">
                        <h3><?php echo count($products_summary['google_reviews_profiles']); ?></h3>
                        <p>Profils Avis Google</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_vcard_section($products_summary) {
        include $this->plugin_path . 'templates/dashboard/sections/vcard-profiles-section.php';
    }

    private function render_google_reviews_section($products_summary) {
        include $this->plugin_path . 'templates/dashboard/sections/google-reviews-section.php';
    }

    private function render_vcard_edit_page($vcard)
    {
        $template_path = $this->plugin_path . 'templates/dashboard/simple/vcard-edit.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            $this->render_test_page('vcard-edit', 'Édition vCard', $vcard);
        }
    }

    private function render_qr_codes_page($vcard)
    {
        $template_path = $this->plugin_path . 'templates/dashboard/simple/qr-codes.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            $this->render_test_page('qr-codes', 'QR Codes', $vcard);
        }
    }

    private function render_contacts_page($vcard)
    {
        $template_path = $this->plugin_path . 'templates/dashboard/simple/contacts.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            $this->render_test_page('contacts', 'Contacts', $vcard);
        }
    }

    private function render_statistics_page($vcard)
    {
        $template_path = $this->plugin_path . 'templates/dashboard/simple/statistics.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            $this->render_test_page('statistics', 'Statistiques', $vcard);
        }
    }

    private function render_preview_page($vcard)
    {
        $template_path = $this->plugin_path . 'templates/dashboard/simple/preview.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            $this->render_test_page('preview', 'Aperçu public', $vcard);
        }
    }

    /**
     * Page de test générique (fallback)
     */
    private function render_test_page($page_key, $page_title, $vcard)
    {
        ?>
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card p-4">
                    <h3>✅ ÉTAPE 2A : Page "<?php echo esc_html($page_title); ?>" chargée</h3>
                    <p><strong>Page courante :</strong> <?php echo esc_html($page_key); ?></p>
                    <p><strong>vCard ID :</strong> <?php echo esc_html($vcard->ID); ?></p>
                    <p><strong>Template recherché :</strong> templates/dashboard/simple/<?php echo esc_html($page_key); ?>.php
                    </p>

                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Routing fonctionnel !</strong> La navigation entre les pages fonctionne.
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Prochaine étape :</strong> Intégrer le template PHP spécifique pour cette page.
                    </div>

                    <!-- TEST DE NAVIGATION -->
                    <div class="mt-4">
                        <h5>🧪 Test de navigation :</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="?page=overview" class="btn btn-outline-primary btn-sm">Vue d'ensemble</a>
                            <a href="?page=vcard-edit" class="btn btn-outline-primary btn-sm">Ma vCard</a>
                            <a href="?page=qr-codes" class="btn btn-outline-primary btn-sm">QR Codes</a>
                            <a href="?page=contacts" class="btn btn-outline-primary btn-sm">Contacts</a>
                            <a href="?page=statistics" class="btn btn-outline-primary btn-sm">Statistics</a>
                            <a href="?page=preview" class="btn btn-outline-primary btn-sm">Preview</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Page si pas de vCard - avec assets (INCHANGÉ)
     */
    private function render_no_vcard_with_assets()
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Dashboard NFC - Aucune carte</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="<?php echo $this->plugin_url; ?>assets/css/dashboard.css" rel="stylesheet">
        </head>

        <body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-body text-center p-5">
                                <i class="fas fa-credit-card fa-3x text-primary mb-3"></i>
                                <h3>Aucune carte NFC trouvée</h3>
                                <p class="text-muted">Vous devez commander une carte NFC pour accéder au dashboard.</p>
                                <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="btn btn-primary">
                                    Commander une carte NFC
                                </a>
                                <hr>
                                <a href="<?php echo wc_get_account_endpoint_url('dashboard'); ?>" class="btn btn-link">
                                    Retour à Mon Compte
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>

        </html>
        <?php
    }

    // Méthodes utilitaires (INCHANGÉES)
    public function add_dashboard_menu_item($menu_items)
    {
        $logout = array_slice($menu_items, -1, 1, true);
        $menu_items = array_slice($menu_items, 0, -1, true);
        $menu_items['nfc-dashboard'] = __('Dashboard NFC', 'gtmi_vcard');
        return array_merge($menu_items, $logout);
    }

    public function add_dashboard_endpoint()
    {
        add_rewrite_endpoint('nfc-dashboard', EP_ROOT | EP_PAGES);
    }

    public function flush_rewrite_rules_maybe()
    {
        if (get_option('nfc_dashboard_flush_rules') == 'yes') {
            flush_rewrite_rules();
            delete_option('nfc_dashboard_flush_rules');
        }
    }

    public function dashboard_endpoint_fallback()
    {
        echo '<div class="alert alert-info">Dashboard Étape 2A actif avec routing</div>';
    }

    public function get_user_vcards($user_id) {
        $enterprise_cards = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
        
        if (!empty($enterprise_cards)) {
            // Convertir au format attendu par le dashboard
            $vcards = [];
            foreach ($enterprise_cards as $card) {
                $vcards[] = get_post($card['vcard_id']);
            }
            return array_filter($vcards); // Supprimer les nulls
        }
        
        // Fallback pour anciennes vCards
        return get_posts([
            'post_type' => 'virtual_card',
            'author' => $user_id,
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
    }

    /**
     * NOUVEAU: Récupérer les vCards via les commandes WooCommerce
     */
    private function get_vcards_from_user_orders($user_id)
    {
        if (!function_exists('wc_get_orders')) {
            return [];
        }

        // Récupérer les commandes de l'utilisateur (plus récentes en premier)
        $orders = wc_get_orders([
            'customer_id' => $user_id,
            'status' => ['completed', 'processing'],
            'limit' => 10,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

        $vcards = [];

        foreach ($orders as $order) {
            // Parcourir les items de la commande
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();

                // Chercher une vCard liée à cette commande/produit
                $vcard_args = [
                    'post_type' => 'virtual_card',
                    'meta_query' => [
                        'relation' => 'OR',
                        [
                            'key' => 'order_id',
                            'value' => $order->get_id(),
                            'compare' => '='
                        ],
                        [
                            'key' => 'woocommerce_order_id',
                            'value' => $order->get_id(),
                            'compare' => '='
                        ]
                    ],
                    'posts_per_page' => -1
                ];

                $order_vcards = get_posts($vcard_args);

                if (!empty($order_vcards)) {
                    $vcards = array_merge($vcards, $order_vcards);
                    error_log("🛒 Dashboard: vCard trouvée via commande {$order->get_id()}");
                }
            }
        }

        // Supprimer les doublons et retourner
        $unique_vcards = [];
        $seen_ids = [];

        foreach ($vcards as $vcard) {
            if (!in_array($vcard->ID, $seen_ids)) {
                $unique_vcards[] = $vcard;
                $seen_ids[] = $vcard->ID;
            }
        }

        return $unique_vcards;
    }

    /**
     * AMÉLIORATION: Helper pour debug/log des vCards
     */
    private function log_vcard_info($vcard, $context = '')
    {
        if (!$vcard)
            return;

        $order_id = get_post_meta($vcard->ID, 'order_id', true) ?: get_post_meta($vcard->ID, 'woocommerce_order_id', true);
        $first_name = get_post_meta($vcard->ID, 'first_name', true);
        $last_name = get_post_meta($vcard->ID, 'last_name', true);

        error_log("📋 {$context} vCard {$vcard->ID}: {$first_name} {$last_name} (Order: {$order_id})");
    }
}

// Initialisation
$nfc_dashboard_manager = new NFC_Dashboard_Manager();
$nfc_dashboard_manager->init();

// Hook d'activation
register_activation_hook(dirname(__FILE__, 2) . '/gtmi-vcard.php', function () {
    add_option('nfc_dashboard_flush_rules', 'yes');
});