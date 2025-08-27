<?php
/**
 * Template Layout Principal - Dashboard NFC
 * 
 * Fichier: templates/dashboard/layout/dashboard-layout.php
 * Layout avec sidebar navigation + contenu principal
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables disponibles depuis le dashboard manager
global $nfc_vcard, $nfc_current_page;
$vcard = $nfc_vcard;
$current_page = $nfc_current_page;

// Récupérer les données de la vCard
$vcard_id = $vcard->ID;
$vcard_title = $vcard->post_title;
$vcard_data = get_post_meta($vcard_id);

// Pages disponibles
$dashboard_pages = [
    'overview' => [
        'title' => __('Vue d\'ensemble', 'gtmi_vcard'),
        'icon' => 'fas fa-tachometer-alt',
        'description' => __('Tableau de bord principal', 'gtmi_vcard')
    ],
    'vcard-edit' => [
        'title' => __('Ma vCard', 'gtmi_vcard'),
        'icon' => 'fas fa-id-card',
        'description' => __('Modifier mes informations', 'gtmi_vcard')
    ],
    'qr-codes' => [
        'title' => __('QR Codes', 'gtmi_vcard'),
        'icon' => 'fas fa-qrcode',
        'description' => __('Générer et télécharger', 'gtmi_vcard')
    ],
    'contacts' => [
        'title' => __('Mes contacts', 'gtmi_vcard'),
        'icon' => 'fas fa-address-book',
        'description' => __('Contacts reçus', 'gtmi_vcard')
    ],
    'statistics' => [
        'title' => __('Statistiques', 'gtmi_vcard'),
        'icon' => 'fas fa-chart-bar',
        'description' => __('Analytics détaillées', 'gtmi_vcard')
    ]
];

$current_page_data = $dashboard_pages[$current_page] ?? $dashboard_pages['overview'];
?>

<div class="nfc-dashboard-wrapper">
    
    <!-- Header Dashboard -->
    <div class="nfc-dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="nfc-dashboard-title">
                        <i class="<?php echo esc_attr($current_page_data['icon']); ?>"></i>
                        <?php echo esc_html($current_page_data['title']); ?>
                    </h1>
                    <p class="nfc-dashboard-subtitle">
                        <?php echo esc_html($current_page_data['description']); ?>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="nfc-vcard-info">
                        <span class="badge bg-primary"><?php echo esc_html($vcard_title); ?></span>
                        <a href="<?php echo get_permalink($vcard_id); ?>" target="_blank" class="btn btn-outline-primary btn-sm ms-2">
                            <i class="fas fa-external-link-alt"></i> <?php _e('Voir ma carte', 'gtmi_vcard'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="nfc-dashboard-content">
        <div class="container-fluid">
            <div class="row">
                
                <!-- Sidebar Navigation -->
                <div class="col-lg-3 col-xl-2">
                    <div class="nfc-dashboard-sidebar">
                        <nav class="nfc-dashboard-nav">
                            <?php foreach ($dashboard_pages as $page_key => $page_data): ?>
                                <a href="<?php echo esc_url(wc_get_account_endpoint_url('nfc-dashboard') . '?page=' . $page_key); ?>" 
                                   class="nfc-nav-item <?php echo $current_page === $page_key ? 'active' : ''; ?>">
                                    <i class="<?php echo esc_attr($page_data['icon']); ?>"></i>
                                    <span><?php echo esc_html($page_data['title']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                        
                        <!-- Quick Stats Sidebar -->
                        <div class="nfc-sidebar-stats">
                            <h6><?php _e('Statistiques rapides', 'gtmi_vcard'); ?></h6>
                            <div class="nfc-quick-stat">
                                <span class="stat-label"><?php _e('Vues totales', 'gtmi_vcard'); ?></span>
                                <span class="stat-value" id="quick-stat-views">-</span>
                            </div>
                            <div class="nfc-quick-stat">
                                <span class="stat-label"><?php _e('Contacts', 'gtmi_vcard'); ?></span>
                                <span class="stat-value" id="quick-stat-contacts">-</span>
                            </div>
                            <div class="nfc-quick-stat">
                                <span class="stat-label"><?php _e('Cette semaine', 'gtmi_vcard'); ?></span>
                                <span class="stat-value" id="quick-stat-week">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contenu Principal -->
                <div class="col-lg-9 col-xl-10">
                    <div class="nfc-dashboard-main">
                        
                        <!-- Messages de notification -->
                        <div id="nfc-dashboard-messages"></div>
                        
                        <!-- Loading overlay -->
                        <div id="nfc-dashboard-loading" class="nfc-loading-overlay d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden"><?php _e('Chargement...', 'gtmi_vcard'); ?></span>
                            </div>
                        </div>
                        
                        <!-- Contenu de la page -->
                        <div id="nfc-dashboard-page-content" class="nfc-fade-in">
                            <?php
                            // Inclusion du template de page spécifique
                            $page_template = dirname(__FILE__) . '/../simple/' . $current_page . '.php';
                            
                            if (file_exists($page_template)) {
                                include $page_template;
                            } else {
                                // Page par défaut
                                include dirname(__FILE__) . '/../simple/dashboard.php';
                            }
                            ?>
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
</div>

<!-- Template JavaScript pour les notifications -->
<script type="text/template" id="nfc-notification-template">
    <div class="alert alert-{type} alert-dismissible fade show" role="alert">
        <i class="fas fa-{icon}"></i>
        <span class="message">{message}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</script>

<script type="text/javascript">
jQuery(document).ready(function($) {
    console.log('NFC Dashboard Layout chargé');
    console.log('Configuration:', window.nfcDashboardConfig);
    
    // Initialisation des stats rapides
    if (typeof window.NFCDashboard !== 'undefined') {
        window.NFCDashboard.loadQuickStats();
    }
});
</script>