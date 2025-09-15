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
            'overview' => 'Vue d\'ensemble',        // Stats globales
            'cards-list' => 'Mes cartes',          // Liste des vCards
            'vcard-edit' => 'Ma vCard',            // Édition vCard spécifique
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
        </head>

        <?php
        // NOUVEAU : Récupérer produits utilisateur pour menu adaptatif
        $user_vcards = $this->get_user_vcards($current_user->ID);
        $vcard_count = count($user_vcards);
        ?>

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

                        <?php if ($vcard_count > 0): ?>
                            <div class="nfc-nav-section">
                                <div class="nfc-nav-section-title">
                                    Mes cartes vCard
                                    <?php if ($vcard_count > 1): ?>
                                        <span class="nfc-nav-count"><?php echo $vcard_count; ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($vcard_count === 1): ?>
                                    <!-- Interface simple : 1 seule vCard -->
                                    <div class="nfc-nav-item">
                                        <a href="?page=qr-codes"
                                            class="nfc-nav-link <?php echo $current_page === 'qr-codes' ? 'active' : ''; ?>">
                                            <i class="fas fa-qrcode"></i>
                                            QR Codes
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <!-- Interface multi-cartes : plusieurs vCards -->
                                    <div class="nfc-nav-item">
                                        <a href="?page=cards-list"
                                            class="nfc-nav-link <?php echo $current_page === 'cards-list' ? 'active' : ''; ?>">
                                            <i class="fas fa-id-card-alt"></i>
                                            Mes cartes
                                            <span class="nfc-nav-badge"><?php echo $vcard_count; ?></span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

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
            <!-- <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script> -->
            <script src="<?php echo $this->plugin_url; ?>assets/js/dashboard/stats-commons.js?v=<?php echo time(); ?>"></script>
            <?php if ($current_page === 'overview'): ?>
                <script src="<?php echo $this->plugin_url; ?>assets/js/dashboard/overview.js?v=<?php echo time(); ?>"></script>
            <?php endif; ?>
            <?php if ($current_page === 'vcard-edit') { ?>
                <script src="<?php echo $this->plugin_url; ?>assets/js/dashboard/vcard-editor.js?v=<?php echo time(); ?>"></script>
            <?php } ?>>

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

            case 'cards-list':
                $this->render_cards_list_page($vcard);  // NOUVEAU - Liste des vCards
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


    private function render_cards_list_page($vcard)
    {
        // Passer référence du dashboard manager pour accès aux fonctions
        global $nfc_dashboard_manager;
        $nfc_dashboard_manager = $this;
        
        // Utiliser le nouveau template standardisé
        $template_path = $this->plugin_path . 'templates/dashboard/cards-list.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback vers l'ancienne version si le nouveau template n'existe pas encore
            $this->render_cards_list_page_legacy($vcard);
        }
    }

    /**
     * PAGES INDIVIDUELLES - VERSION TEST
     */
    private function render_overview_page($vcard)
    {
        $user_id = get_current_user_id();
        $user_vcards = $this->get_user_vcards($user_id);

        if (empty($user_vcards)) {
            // Aucune vCard → Empty state
            echo '<div class="content-header">';
            echo '<h1 class="h3 mb-1">Vue d\'ensemble</h1>';
            echo '<p class="text-muted mb-0">Aperçu de vos performances NFC</p>';
            echo '</div>';

            echo '<div class="alert alert-info mt-4">';
            echo '<h5><i class="fas fa-info-circle me-2"></i>Aucune carte NFC configurée</h5>';
            echo '<p>Commandez vos premiers produits NFC pour commencer à voir vos statistiques.</p>';
            echo '<a href="' . home_url('/boutique-nfc/') . '" class="btn btn-primary">📱 Commander mes cartes</a>';
            echo '</div>';
            return;
        }

        // Interface Overview avec stats (comme mockup)
        echo '<div class="content-header">';
        echo '<div class="d-flex justify-content-between align-items-center">';
        echo '<div>';
        echo '<h1 class="h3 mb-1">Vue d\'ensemble</h1>';
        echo '<p class="text-muted mb-0">Aperçu de vos performances NFC</p>';
        echo '</div>';
        echo '<a href="?page=cards-list" class="btn btn-primary">';
        echo '<i class="fas fa-id-card me-2"></i>Gérer mes cartes';
        echo '</a>';
        echo '</div>';
        echo '</div>';

        echo '<div class="content-body">';

        // Stats Cards comme dans le mockup
        echo '<div class="row mb-4">';

        echo '<div class="col-md-3 mb-3">';
        echo '<div class="stat-card">';
        echo '<div class="stat-value" id="total-views">--</div>';
        echo '<div class="stat-label">Vues totales</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="col-md-3 mb-3">';
        echo '<div class="stat-card">';
        echo '<div class="stat-value" id="total-contacts">--</div>';
        echo '<div class="stat-label">Contacts générés</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="col-md-3 mb-3">';
        echo '<div class="stat-card">';
        echo '<div class="stat-value">' . count($user_vcards) . '</div>';
        echo '<div class="stat-label">Cartes actives</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="col-md-3 mb-3">';
        echo '<div class="stat-card">';
        echo '<div class="stat-value" id="conversion-rate">--%</div>';
        echo '<div class="stat-label">Taux de conversion</div>';
        echo '</div>';
        echo '</div>';

        echo '</div>'; // row

        // Graphique placeholder (à développer plus tard)
        echo '<div class="row">';
        echo '<div class="col-12">';
        echo '<div class="dashboard-card">';
        echo '<div class="card-header">';
        echo '<h3 class="h6 mb-0"><i class="fas fa-chart-line me-2"></i>Évolution des performances</h3>';
        echo '</div>';
        echo '<div class="card-body">';
        echo '<div class="chart-placeholder">Graphique des performances (à développer)</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '</div>'; // content-body

        // TODO: Charger les vraies stats via AJAX
        echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Charger les stats globales
        loadGlobalStats(' . $user_id . ');
    });
    
    function loadGlobalStats(userId) {
        // TODO: Appel AJAX pour récupérer les vraies stats
        console.log("Loading global stats for user " + userId);
    }
    </script>';
    }

    private function render_cards_list_page_legacy($vcard)
    {
        $user_id = get_current_user_id();
        $user_vcards = $this->get_user_vcards($user_id);

        if (empty($user_vcards)) {
            echo '<div class="content-header">';
            echo '<h1 class="h3 mb-1">Mes cartes</h1>';
            echo '<p class="text-muted mb-0">Gérez vos cartes de visite NFC</p>';
            echo '</div>';

            echo '<div class="alert alert-warning mt-4">';
            echo '<h5><i class="fas fa-exclamation-triangle me-2"></i>Aucune carte trouvée</h5>';
            echo '<p>Vous n\'avez pas encore de cartes NFC. Commandez-en pour commencer.</p>';
            echo '<a href="' . home_url('/boutique-nfc/') . '" class="btn btn-primary">📱 Commander mes cartes</a>';
            echo '</div>';
            return;
        }

        echo '<div class="content-header">';
        echo '<div class="d-flex justify-content-between align-items-center">';
        echo '<div>';
        echo '<h1 class="h3 mb-1">Mes cartes (' . count($user_vcards) . ')</h1>';
        echo '<p class="text-muted mb-0">Gérez vos cartes de visite NFC</p>';
        echo '</div>';
        echo '<a href="' . home_url('/boutique-nfc/') . '" class="btn btn-primary">';
        echo '<i class="fas fa-plus me-2"></i>Commander d\'autres cartes';
        echo '</a>';
        echo '</div>';
        echo '</div>';

        echo '<div class="content-body">';
        echo '<div class="dashboard-card">';
        echo '<div class="table-responsive">';
        echo '<table class="table cards-table mb-0">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Identifiant</th>';
        echo '<th>Profil</th>';
        echo '<th>Statut</th>';
        echo '<th>Performances</th>';
        echo '<th class="text-end">Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($user_vcards as $index => $vcard) {
            $vcard_id = $vcard->ID;

            // Récupérer les données
            $firstname = get_post_meta($vcard_id, 'firstname', true) ?: '';
            $lastname = get_post_meta($vcard_id, 'lastname', true) ?: '';
            $service = get_post_meta($vcard_id, 'service', true) ?: '';
            $society = get_post_meta($vcard_id, 'society', true) ?: '';
            $email = get_post_meta($vcard_id, 'email', true) ?: '';

            // Identifiant (essayer de récupérer depuis enterprise sinon fallback)
            $card_identifier = $this->get_card_identifier($vcard_id);
            if (!$card_identifier) {
                $card_identifier = 'NFC' . $vcard_id . '-1'; // Fallback
            }

            // Nom complet
            $full_name = trim($firstname . ' ' . $lastname);
            if (empty($full_name)) {
                $full_name = $vcard->post_title ?: "Carte #" . ($index + 1);
            }

            // Position
            $position = '';
            if (!empty($service) && !empty($society)) {
                $position = $service . ' - ' . $society;
            } elseif (!empty($service)) {
                $position = $service;
            } elseif (!empty($society)) {
                $position = $society;
            } else {
                $position = 'Poste à définir';
            }

            // Statut
            $is_configured = !empty($firstname) && !empty($lastname) && !empty($email);
            if ($is_configured) {
                $status_badge = '<span class="badge bg-success">✅ Configurée</span>';
            } else {
                $status_badge = '<span class="badge bg-warning">⚠️ À configurer</span>';
            }

            echo '<tr>';

            // Identifiant
            echo '<td>';
            echo '<div class="card-identifier">' . esc_html($card_identifier) . '</div>';
            echo '</td>';

            // Profil
            echo '<td>';
            echo '<div class="profile-info">';
            echo '<div class="profile-avatar">';
            echo strtoupper(substr($full_name, 0, 2));
            echo '</div>';
            echo '<div class="ms-3">';
            echo '<div class="fw-medium">' . esc_html($full_name) . '</div>';
            echo '<small class="text-muted">' . esc_html($position) . '</small>';
            echo '</div>';
            echo '</div>';
            echo '</td>';

            // Statut
            echo '<td>' . $status_badge . '</td>';

            // Performances (placeholder)
            echo '<td>';
            echo '<div class="d-flex gap-3">';
            echo '<small><i class="fas fa-eye text-primary me-1"></i>-- vues</small>';
            echo '<small><i class="fas fa-address-book text-success me-1"></i>-- contacts</small>';
            echo '</div>';
            echo '</td>';

            // Actions
            echo '<td class="text-end">';
            echo '<div class="btn-group btn-group-sm">';

            // Bouton Modifier (avec vcard_id)
            echo '<a href="?page=vcard-edit&vcard_id=' . $vcard_id . '" class="btn btn-primary">';
            echo '<i class="fas fa-edit me-1"></i>Modifier';
            echo '</a>';

            echo '<a href="?page=statistics&vcard_id=' . $vcard_id . '" class="btn btn-outline-info">';
            echo '<i class="fas fa-chart-bar me-1"></i>Stats';
            echo '</a>';

            echo '<a href="?page=contacts&vcard_id=' . $vcard_id . '" class="btn btn-outline-success">';
            echo '<i class="fas fa-address-book me-1"></i>Leads';
            echo '</a>';

            echo '<a href="?page=preview&vcard_id=' . $vcard_id . '" class="btn btn-outline-secondary">';
            echo '<i class="fas fa-eye me-1"></i>Aperçu';
            echo '</a>';

            echo '</div>'; // btn-group
            echo '</td>';

            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>'; // table-responsive
        echo '</div>'; // dashboard-card
        echo '</div>'; // content-body
    }


    /**
     * NOUVEAU : Déterminer le type de dashboard selon les produits
     */
    private function determine_dashboard_type($products_summary)
    {
        $vcard_count = count($products_summary['vcard_profiles'] ?? []);
        $google_count = count($products_summary['google_reviews_profiles'] ?? []);

        // Aucun produit → Interface simple avec message
        if ($vcard_count === 0 && $google_count === 0) {
            return 'simple';
        }

        // 1 seule vCard et pas d'Avis Google → Interface simple existante
        if ($vcard_count === 1 && $google_count === 0) {
            return 'simple';
        }

        // Plusieurs vCards et pas d'Avis Google → Interface multi-vCard
        if ($vcard_count > 1 && $google_count === 0) {
            return 'multi_vcard';
        }

        // Seulement Avis Google → Interface Avis Google
        if ($vcard_count === 0 && $google_count > 0) {
            return 'google_reviews_only';
        }

        // vCard + Avis Google → Interface unifiée
        if ($vcard_count > 0 && $google_count > 0) {
            return 'unified';
        }

        // Fallback
        return 'simple';
    }

    private function render_simple_overview($vcard, $products_summary)
    {
        // Utiliser le template existant si 1 vCard
        if (!empty($products_summary['vcard_profiles']) && count($products_summary['vcard_profiles']) === 1) {
            $template_path = $this->plugin_path . 'templates/dashboard/simple/overview.php';
            if (file_exists($template_path)) {
                include $template_path;
                return;
            }
        }

        // Message si aucune vCard
        echo '<div class="empty-state text-center py-5">';
        echo '<i class="fas fa-box-open fa-3x text-muted mb-3"></i>';
        echo '<h4>Aucun produit NFC configuré</h4>';
        echo '<p class="text-muted">Commandez vos premiers produits NFC pour commencer.</p>';
        echo '<a href="/boutique-nfc/" class="btn btn-primary">';
        echo '<i class="fas fa-shopping-cart me-2"></i>Découvrir nos produits';
        echo '</a>';
        echo '</div>';
    }

    /**
     * Interface multi-profils vCard
     */
    private function render_multi_vcard_overview($products_summary)
    {
        $vcard_profiles = $products_summary['vcard_profiles'] ?? [];

        ?>
        <div class="multi-vcard-overview">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h4 mb-1">
                                <i class="fas fa-id-card me-2 text-primary"></i>
                                Mes Cartes vCard
                                <span class="badge bg-primary ms-2"><?php echo count($vcard_profiles); ?> carte(s)</span>
                            </h2>
                            <p class="text-muted mb-0">Gérez tous vos profils vCard individuels</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="/boutique-nfc/" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus me-2"></i>Commander plus
                            </a>
                            <button class="btn btn-primary btn-sm" onclick="alert('Export global à implémenter')">
                                <i class="fas fa-download me-2"></i>Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats globales -->
            <div class="row mb-4">
                <?php
                $total_views = array_sum(array_column($vcard_profiles, 'views') ?: []);
                $total_contacts = array_sum(array_column($vcard_profiles, 'contacts') ?: []);
                $configured_cards = array_filter($vcard_profiles, function ($p) {
                    return isset($p['vcard_data']['is_configured']) && $p['vcard_data']['is_configured'];
                });
                ?>
                <div class="col-md-3">
                    <div class="dashboard-card p-3 text-center">
                        <div class="h4 text-primary mb-1"><?php echo number_format($total_views); ?></div>
                        <small class="text-muted">Vues totales</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card p-3 text-center">
                        <div class="h4 text-success mb-1"><?php echo number_format($total_contacts); ?></div>
                        <small class="text-muted">Leads générés</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card p-3 text-center">
                        <div class="h4 text-info mb-1">
                            <?php echo count($configured_cards); ?>/<?php echo count($vcard_profiles); ?>
                        </div>
                        <small class="text-muted">Configurées</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card p-3 text-center">
                        <div class="h4 text-warning mb-1">
                            <?php echo count($vcard_profiles) > 0 ? round((count($configured_cards) / count($vcard_profiles)) * 100) : 0; ?>%
                        </div>
                        <small class="text-muted">Progression</small>
                    </div>
                </div>
            </div>

            <!-- Liste des profils selon cahier des charges -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">Mes Profils vCard</h5>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($vcard_profiles as $profile): ?>
                        <?php
                        $full_name = isset($profile['vcard_data']) ? nfc_format_vcard_full_name($profile['vcard_data']) : 'Profil à configurer';
                        $position = isset($profile['vcard_data']) ? nfc_format_vcard_position($profile['vcard_data']) : '';
                        $views = isset($profile['stats']['views']) ? $profile['stats']['views'] : 0;
                        $contacts = isset($profile['stats']['contacts']) ? $profile['stats']['contacts'] : 0;
                        $status_info = nfc_get_card_display_status($profile);
                        ?>
                        <div class="border-bottom p-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="profile-avatar me-3">
                                            <?php
                                            $initials = '';
                                            if (isset($profile['vcard_data']['firstname'])) {
                                                $initials .= substr($profile['vcard_data']['firstname'], 0, 1);
                                            }
                                            if (isset($profile['vcard_data']['lastname'])) {
                                                $initials .= substr($profile['vcard_data']['lastname'], 0, 1);
                                            }
                                            echo $initials ?: 'NC';
                                            ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo esc_html($full_name); ?></div>
                                            <?php if ($position): ?>
                                                <small class="text-muted"><?php echo esc_html($position); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="small">
                                            👁️ <?php echo number_format($views); ?> vues |
                                            📞 <?php echo number_format($contacts); ?> contacts
                                        </div>
                                        <span class="badge bg-<?php echo esc_attr($status_info['color']); ?> mt-1">
                                            <?php echo esc_html($status_info['label']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="?page=vcard-edit&vcard_id=<?php echo $profile['vcard_id']; ?>"
                                            class="btn btn-primary btn-sm">
                                            Modifier vCard
                                        </a>
                                        <div class="btn-group">
                                            <a href="?page=statistics&vcard_id=<?php echo $profile['vcard_id']; ?>"
                                                class="btn btn-info btn-sm">Stats</a>
                                            <a href="?page=contacts&vcard_id=<?php echo $profile['vcard_id']; ?>"
                                                class="btn btn-success btn-sm">Leads</a>
                                            <button class="btn btn-warning btn-sm"
                                                onclick="renewCard('<?php echo esc_js($profile['card_identifier']); ?>')">
                                                Renouveler
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <script>
            function renewCard(identifier) {
                if (confirm('Renouveler la carte ' + identifier + ' ?')) {
                    window.location.href = '/boutique-nfc/?context=renewal&card_id=' + identifier;
                }
            }
        </script>
        <?php
    }

    /**
     *  Interface Avis Google seulement (préparation)
     */
    private function render_google_reviews_only_overview($products_summary)
    {
        echo '<div class="alert alert-info">';
        echo '<h5><i class="fas fa-star me-2"></i>Interface Avis Google</h5>';
        echo '<p>Cette interface sera développée dans la Phase 2.</p>';
        echo '<p><strong>Profils détectés :</strong> ' . count($products_summary['google_reviews_profiles']) . '</p>';
        echo '</div>';
    }

    /**
     * Interface unifiée vCard + Avis Google (préparation)
     */
    private function render_unified_overview($products_summary)
    {
        echo '<div class="alert alert-success">';
        echo '<h5><i class="fas fa-layer-group me-2"></i>Dashboard Unifié</h5>';
        echo '<p>Interface complète vCard + Avis Google - Sera développée en Phase 3.</p>';
        echo '<p><strong>vCards :</strong> ' . count($products_summary['vcard_profiles']) .
            ' | <strong>Avis Google :</strong> ' . count($products_summary['google_reviews_profiles']) . '</p>';
        echo '</div>';
    }

    private function render_dashboard_header($products_summary)
    {
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

    private function render_vcard_section($products_summary)
    {
        include $this->plugin_path . 'templates/dashboard/sections/vcard-profiles-section.php';
    }

    private function render_google_reviews_section($products_summary)
    {
        include $this->plugin_path . 'templates/dashboard/sections/google-reviews-section.php';
    }

    private function render_vcard_edit_page($vcard)
    {
        // Vérifier si un vcard_id spécifique est demandé
        $requested_vcard_id = isset($_GET['vcard_id']) ? intval($_GET['vcard_id']) : null;

        if ($requested_vcard_id) {
            // Vérifier que l'utilisateur peut accéder à cette vCard
            $user_id = get_current_user_id();
            $user_vcards = $this->get_user_vcards($user_id);

            $target_vcard = null;
            foreach ($user_vcards as $user_vcard) {
                if ($user_vcard->ID == $requested_vcard_id) {
                    $target_vcard = $user_vcard;
                    break;
                }
            }

            if (!$target_vcard) {
                echo '<div class="alert alert-danger">';
                echo '<h5>Accès refusé</h5>';
                echo '<p>Cette vCard n\'existe pas ou ne vous appartient pas.</p>';
                echo '<a href="?page=cards-list" class="btn btn-primary">← Retour à mes cartes</a>';
                echo '</div>';
                return;
            }

            // Utiliser la vCard spécifique
            $vcard = $target_vcard;
        }

        // Breadcrumb si venu de cards-list
        if ($requested_vcard_id) {
            echo '<nav aria-label="breadcrumb" class="mb-3">';
            echo '<ol class="breadcrumb">';
            echo '<li class="breadcrumb-item">';
            echo '<a href="?page=cards-list"><i class="fas fa-arrow-left me-1"></i>Mes cartes</a>';
            echo '</li>';
            echo '<li class="breadcrumb-item active">Modifier vCard</li>';
            echo '</ol>';
            echo '</nav>';
        }

        // Charger le template vcard-edit existant
        $template_path = $this->plugin_path . 'templates/dashboard/simple/vcard-edit.php';
        if (file_exists($template_path)) {
            // Passer les bonnes variables globales
            global $nfc_vcard;
            $nfc_vcard = $vcard;

            include $template_path;
        } else {
            echo '<div class="alert alert-warning">Template vcard-edit.php introuvable</div>';
        }
    }

    public function get_card_identifier($vcard_id)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nfc_enterprise_cards';

        return $wpdb->get_var($wpdb->prepare(
            "SELECT card_identifier FROM $table_name WHERE vcard_id = %d",
            $vcard_id
        ));
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
        // Passer référence du dashboard manager pour accès aux fonctions
        global $nfc_dashboard_manager;
        $nfc_dashboard_manager = $this;
        
        // 🔥 SIMPLE : Toujours utiliser le même template
        $template_path = $this->plugin_path . 'templates/dashboard/leads.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback vers l'ancien si le nouveau n'existe pas
            $fallback_path = $this->plugin_path . 'templates/dashboard/simple/contacts.php';
            if (file_exists($fallback_path)) {
                include $fallback_path;
            } else {
                $this->render_test_page('contacts', 'Contacts', $vcard);
            }
        }
    }

    private function render_statistics_page($vcard)
    {
        $template_path = $this->plugin_path . 'templates/dashboard/statistics.php';
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

    public function get_user_vcards($user_id)
    {
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