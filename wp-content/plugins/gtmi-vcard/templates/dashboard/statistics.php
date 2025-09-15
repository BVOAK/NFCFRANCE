<?php
/**
 * Dashboard - Statistics
 * Page statistiques adaptative multi-vCard
 * Architecture standardisée NFC France
 */

// Sécurité et vérifications
if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

// Helper functions
if (!function_exists('nfc_get_user_vcard_profiles')) {
    function nfc_get_user_vcard_profiles($user_id) {
        if (class_exists('NFC_Enterprise_Core')) {
            return NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
        }
        return [];
    }
}

if (!function_exists('nfc_format_vcard_full_name')) {
    function nfc_format_vcard_full_name($vcard_data) {
        if (empty($vcard_data)) return 'Profil non configuré';
        
        $firstname = $vcard_data['firstname'] ?? '';
        $lastname = $vcard_data['lastname'] ?? '';
        
        if (!empty($firstname) && !empty($lastname)) {
            return $firstname . ' ' . $lastname;
        } elseif (!empty($firstname)) {
            return $firstname;
        } elseif (!empty($lastname)) {
            return $lastname;
        }
        
        return 'Profil à configurer';
    }
}

// ================================================================================
// LOGIQUE MÉTIER
// ================================================================================

$user_id = get_current_user_id();
$user_vcards = nfc_get_user_vcard_profiles($user_id);

// Gestion des états
if (empty($user_vcards)) {
    ?>
    <div class="text-center py-5">
        <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
        <h3>Aucune statistique disponible</h3>
        <p class="text-muted mb-4">Commandez votre première carte NFC pour accéder aux statistiques de performance.</p>
        <a href="/boutique-nfc/" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Commander ma carte NFC
        </a>
    </div>
    <?php
    return;
}

// Interface selon nombre de vCards
$show_profile_filter = count($user_vcards) > 1;
$page_title = $show_profile_filter ? "Statistiques Multi-Profils" : "Mes Statistiques";

// Période par défaut
$default_period = '30d';
$available_periods = [
    '7d' => '7 derniers jours',
    '30d' => '30 derniers jours', 
    '3m' => '3 derniers mois',
    '6m' => '6 derniers mois',
    '1y' => '1 an'
];

// Configuration pour JavaScript
$stats_config = [
    'user_id' => $user_id,
    'vcards' => $user_vcards,
    'show_profile_filter' => $show_profile_filter,
    'default_period' => $default_period,
    'periods' => $available_periods,
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce')
];

// ================================================================================
// ASSETS
// ================================================================================

wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js', [], '3.9.1', true);
wp_enqueue_script('statistics-manager', 
    plugin_dir_url(__FILE__) . '../../assets/js/dashboard/statistics-manager.js',
    ['jquery', 'chart-js'], '1.0.0', true
);
wp_localize_script('statistics-manager', 'statisticsConfig', $stats_config);

wp_enqueue_style('statistics-css',
    plugin_dir_url(__FILE__) . '../../assets/css/dashboard/statistics.css',
    [], '1.0.0'
);

// Styles CSS communs du dashboard
wp_enqueue_style('nfc-dashboard-common', 
    plugin_dir_url(__FILE__) . '../../assets/css/dashboard-common.css',
    [], '1.0.0'
);
?>

<!-- ================================================================================ -->
<!-- HTML DE LA PAGE -->
<!-- ================================================================================ -->

<div class="dashboard-statistics">
    <!-- Header avec titre et contrôles -->
    <div class="statistics-header">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h2><i class="fas fa-chart-line me-2"></i><?= esc_html($page_title) ?></h2>
                <p class="text-muted mb-0">Analyse de performance de vos cartes NFC</p>
            </div>
            <div class="col-auto">
                <!-- Sélecteur de période -->
                <div class="d-flex gap-2 align-items-center">
                    <label for="periodFilter" class="form-label mb-0 me-2">Période :</label>
                    <select class="form-select" id="periodFilter" style="width: auto;">
                        <?php foreach ($available_periods as $key => $label): ?>
                            <option value="<?= esc_attr($key) ?>" <?= $key === $default_period ? 'selected' : '' ?>>
                                <?= esc_html($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Filtre profil et actions (si plusieurs vCards) -->
        <?php if ($show_profile_filter): ?>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="d-flex gap-2 align-items-center">
                    <label for="profileFilter" class="form-label mb-0 me-2">Profil :</label>
                    <select class="form-select" id="profileFilter" style="width: auto;">
                        <option value="">Tous les profils (<?= count($user_vcards) ?>)</option>
                        <?php foreach ($user_vcards as $vcard): ?>
                            <option value="<?= esc_attr($vcard['vcard_id']) ?>">
                                <?= esc_html(nfc_format_vcard_full_name($vcard['vcard_data'] ?? [])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-outline-primary" id="exportBtn">
                    <i class="fas fa-download me-2"></i>Exporter CSV
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Stats principales (4 cards) -->
    <div class="statistics-cards">
        <div class="row mb-4" id="statsCards">
            <!-- Loading placeholders -->
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-primary text-white">
                    <div class="stat-value placeholder-glow">
                        <span class="placeholder col-6"></span>
                    </div>
                    <div class="stat-label">Vues du profil</div>
                    <div class="stat-change">
                        <span class="placeholder col-8"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-success text-white">
                    <div class="stat-value placeholder-glow">
                        <span class="placeholder col-4"></span>
                    </div>
                    <div class="stat-label">Contacts générés</div>
                    <div class="stat-change">
                        <span class="placeholder col-7"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-info text-white">
                    <div class="stat-value placeholder-glow">
                        <span class="placeholder col-5"></span>
                    </div>
                    <div class="stat-label">Scans NFC</div>
                    <div class="stat-change">
                        <span class="placeholder col-6"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-warning text-white">
                    <div class="stat-value placeholder-glow">
                        <span class="placeholder col-4"></span>
                    </div>
                    <div class="stat-label">Taux conversion</div>
                    <div class="stat-change">
                        <span class="placeholder col-8"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="statistics-charts">
        <div class="row mb-4">
            <!-- Graphique évolution des vues -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Évolution des vues</h5>
                        <div class="chart-loading d-none">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="viewsChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Graphique sources de trafic -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Sources de trafic</h5>
                        <div class="chart-loading d-none">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="sourceChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Deuxième ligne de graphiques -->
        <div class="row">
            <!-- Graphique contacts générés -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Contacts générés</h5>
                        <div class="chart-loading d-none">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="contactsChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Tableau activité récente -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Activité récente</h5>
                    </div>
                    <div class="card-body">
                        <div id="recentActivity">
                            <!-- Placeholder -->
                            <div class="d-flex align-items-center mb-3 placeholder-glow">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-envelope text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="placeholder col-8"></div>
                                    <div class="placeholder col-6"></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3 placeholder-glow">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-eye text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="placeholder col-7"></div>
                                    <div class="placeholder col-5"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay global -->
    <div class="loading-overlay d-none" id="loadingOverlay">
        <div class="d-flex flex-column align-items-center">
            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Chargement des statistiques...</span>
            </div>
            <div class="text-muted">Chargement des statistiques...</div>
        </div>
    </div>

    <!-- Zone de notification -->
    <div class="notification-container" id="notificationContainer"></div>
</div>

<!-- Configuration JavaScript -->
<script>
// Variables globales pour le manager JavaScript
const statisticsConfig = <?= json_encode($stats_config) ?>;
</script>