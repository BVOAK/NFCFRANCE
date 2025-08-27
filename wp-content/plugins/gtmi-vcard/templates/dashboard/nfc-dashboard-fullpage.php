<?php
/**
 * Template Dashboard NFC Full-page - VERSION NETTOYÉE
 * 
 * Fichier: templates/dashboard/nfc-dashboard-fullpage.php
 * Template avec CSS et JS externalisés
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables globales
global $nfc_vcard, $nfc_current_page;
$vcard = $nfc_vcard;
$current_page = $nfc_current_page;
$current_user = wp_get_current_user();

// Récupérer les données utilisateur pour la sidebar
$first_name = get_post_meta($vcard->ID, 'first_name', true) ?: $current_user->first_name;
$last_name = get_post_meta($vcard->ID, 'last_name', true) ?: $current_user->last_name;
$user_email = $current_user->user_email;
$profile_image = get_post_meta($vcard->ID, 'profile_image', true);

// Pages disponibles avec icons et badges
$dashboard_pages = [
    'overview' => [
        'title' => __('Vue d\'ensemble', 'gtmi_vcard'),
        'icon' => 'fas fa-home',
        'section' => 'main'
    ],
    'vcard-edit' => [
        'title' => __('Ma vCard', 'gtmi_vcard'),
        'icon' => 'fas fa-id-card',
        'section' => 'vcard'
    ],
    'qr-codes' => [
        'title' => __('Codes QR', 'gtmi_vcard'),
        'icon' => 'fas fa-qrcode',
        'section' => 'vcard',
        'badge' => '2'
    ],
    'contacts' => [
        'title' => __('Mes contacts', 'gtmi_vcard'),
        'icon' => 'fas fa-users',
        'section' => 'contacts',
        'badge' => '12'
    ],
    'statistics' => [
        'title' => __('Statistiques', 'gtmi_vcard'),
        'icon' => 'fas fa-chart-line',
        'section' => 'contacts'
    ],
    'preview' => [
        'title' => __('Aperçu public', 'gtmi_vcard'),
        'icon' => 'fas fa-eye',
        'section' => 'vcard'
    ]
];

$current_page_data = $dashboard_pages[$current_page] ?? $dashboard_pages['overview'];

// Enqueue des assets
wp_enqueue_style('nfc-dashboard', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/dashboard.css', [], '1.0.0');
wp_enqueue_script('nfc-dashboard-core', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/dashboard/dashboard-core.js', ['jquery'], '1.0.0', true);

// Fonts et libraries externes
wp_enqueue_style('nfc-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', [], null);
wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', [], '5.3.0');
wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0');

wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', [], '5.3.0', true);
wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', [], '3.9.1', true);
wp_enqueue_script('qrcode', 'https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js', [], '1.5.3', true);
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($current_page_data['title']); ?> - Dashboard NFC | <?php bloginfo('name'); ?></title>
    
    <!-- WordPress Head -->
    <?php wp_head(); ?>
</head>

<body class="nfc-dashboard-body">

<div class="nfc-dashboard-app">
    
    <!-- SIDEBAR -->
    <nav class="nfc-sidebar" id="nfcSidebar">
        
        <!-- HEADER SIDEBAR -->
        <div class="nfc-sidebar-header">
            <a href="<?php echo home_url(); ?>" class="nfc-sidebar-logo">
                <i class="fas fa-credit-card"></i>
                NFC France
            </a>
        </div>

        <!-- NAVIGATION -->
        <div class="nfc-sidebar-nav">
            
            <!-- Section Dashboard -->
            <div class="nfc-nav-section">
                <div class="nfc-nav-section-title">Dashboard</div>
                <div class="nfc-nav-item">
                    <a href="?page=overview" class="nfc-nav-link <?php echo $current_page === 'overview' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        Vue d'ensemble
                    </a>
                </div>
            </div>

            <!-- Section Ma vCard -->
            <div class="nfc-nav-section">
                <div class="nfc-nav-section-title">Ma vCard</div>
                
                <div class="nfc-nav-item">
                    <a href="?page=vcard-edit" class="nfc-nav-link <?php echo $current_page === 'vcard-edit' ? 'active' : ''; ?>">
                        <i class="fas fa-id-card"></i>
                        Modifier ma vCard
                    </a>
                </div>
                
                <div class="nfc-nav-item">
                    <a href="?page=qr-codes" class="nfc-nav-link <?php echo $current_page === 'qr-codes' ? 'active' : ''; ?>">
                        <i class="fas fa-qrcode"></i>
                        QR Codes
                        <span class="nfc-nav-badge">2</span>
                    </a>
                </div>
                
                <div class="nfc-nav-item">
                    <a href="?page=preview" class="nfc-nav-link <?php echo $current_page === 'preview' ? 'active' : ''; ?>">
                        <i class="fas fa-eye"></i>
                        Aperçu public
                    </a>
                </div>
            </div>

            <!-- Section Contacts & Stats -->
            <div class="nfc-nav-section">
                <div class="nfc-nav-section-title">Contacts & Stats</div>
                
                <div class="nfc-nav-item">
                    <a href="?page=contacts" class="nfc-nav-link <?php echo $current_page === 'contacts' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        Mes contacts
                        <span class="nfc-nav-badge">12</span>
                    </a>
                </div>
                
                <div class="nfc-nav-item">
                    <a href="?page=statistics" class="nfc-nav-link <?php echo $current_page === 'statistics' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        Statistiques
                    </a>
                </div>
            </div>

            <!-- Section Mon compte -->
            <div class="nfc-nav-section">
                <div class="nfc-nav-section-title">Mon compte</div>
                
                <div class="nfc-nav-item">
                    <a href="<?php echo wc_get_account_endpoint_url('dashboard'); ?>" class="nfc-nav-link">
                        <i class="fas fa-user"></i>
                        Mon compte WooCommerce
                    </a>
                </div>
                
                <div class="nfc-nav-item">
                    <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" class="nfc-nav-link">
                        <i class="fas fa-shopping-bag"></i>
                        Mes commandes
                    </a>
                </div>
                
                <div class="nfc-nav-item">
                    <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="nfc-nav-link">
                        <i class="fas fa-store"></i>
                        Boutique
                    </a>
                </div>
            </div>
        </div>

        <!-- USER INFO -->
        <div class="nfc-sidebar-user">
            <div class="nfc-sidebar-user-info">
                <?php if ($profile_image): ?>
                    <img src="<?php echo esc_url($profile_image); ?>" 
                         alt="Avatar" class="nfc-sidebar-user-avatar">
                <?php else: ?>
                    <div class="nfc-sidebar-user-avatar">
                        <?php echo esc_html(strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1))); ?>
                    </div>
                <?php endif; ?>
                <div class="nfc-sidebar-user-details">
                    <h6><?php echo esc_html($first_name . ' ' . $last_name); ?></h6>
                    <small><?php echo esc_html($user_email); ?></small>
                </div>
            </div>
        </div>
        
    </nav>

    <!-- CONTENU PRINCIPAL -->
    <main class="nfc-main-content">
        
        <!-- HEADER PRINCIPAL -->
        <header class="nfc-main-header">
            <div class="nfc-page-title">
                <button class="nfc-mobile-menu-btn me-3" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>
                    <i class="<?php echo esc_attr($current_page_data['icon']); ?>"></i>
                    <?php echo esc_html($current_page_data['title']); ?>
                </h1>
                <p class="nfc-page-subtitle">
                    <?php 
                    $page_subtitles = [
                        'overview' => 'Aperçu de votre activité NFC',
                        'vcard-edit' => 'Personnalisez vos informations de contact',
                        'qr-codes' => 'Gérez et téléchargez vos QR codes',
                        'contacts' => 'Gérez vos contacts reçus',
                        'statistics' => 'Analytics détaillées de votre vCard',
                        'preview' => 'Aperçu de votre vCard publique'
                    ];
                    echo esc_html($page_subtitles[$current_page] ?? 'Dashboard NFC France'); 
                    ?>
                </p>
            </div>

            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-primary btn-sm" onclick="shareVCard()">
                    <i class="fas fa-share-alt me-2"></i>
                    Partager
                </button>
                
                <a href="<?php echo wc_get_account_endpoint_url('dashboard'); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour Mon Compte
                </a>
            </div>
        </header>

        <!-- ZONE CONTENU -->
        <div class="nfc-content-area fade-in">
            <?php $this->render_page_content_ultimate($vcard, $current_page); ?>
        </div>
        
    </main>
    
</div>

<!-- Loading overlay -->
<div id="nfc-dashboard-loading" class="position-fixed top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center" 
     style="background: rgba(0, 0, 0, 0.7); z-index: 9999;">
    <div class="bg-white rounded-3 shadow p-4 text-center">
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="mb-0 fw-medium">Chargement...</p>
    </div>
</div>

<!-- Configuration JavaScript -->
<script type="text/javascript">
    // Configuration Dashboard
    window.nfcDashboardConfig = {
        vcard_id: <?php echo $vcard->ID; ?>,
        ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
        api_url: '<?php echo home_url('/wp-json/gtmi_vcard/v1/'); ?>',
        current_page: '<?php echo $current_page; ?>',
        user_id: <?php echo $current_user->ID; ?>,
        nonce: '<?php echo wp_create_nonce('nfc_dashboard_nonce'); ?>',
        public_url: '<?php echo get_permalink($vcard->ID); ?>',
        home_url: '<?php echo home_url(); ?>'
    };

    console.log('🎛️ Configuration Dashboard chargée:', window.nfcDashboardConfig);
</script>

<?php wp_footer(); ?>

</body>
</html>