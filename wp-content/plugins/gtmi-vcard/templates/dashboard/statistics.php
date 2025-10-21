<?php
/**
 * Dashboard - Statistics avec scripts directs
 * Template: templates/dashboard/statistics.php
 */

// Sécurité et vérifications
if (!defined('ABSPATH')) exit;
if (!is_user_logged_in()) wp_redirect(home_url('/login'));

// Logique métier
$user_id = get_current_user_id();
if (class_exists('NFC_Enterprise_Core')) {
    $enterprise_cards = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
    if (!empty($enterprise_cards)) {
        $user_vcards = [];
        foreach ($enterprise_cards as $card) {
            $post = get_post($card['vcard_id']);
            if ($post) {
                $user_vcards[] = $post;
            }
        }
    } else {
        $user_vcards = [];
    }
} else {
    // Fallback
    $user_vcards = get_posts([
        'post_type' => 'virtual_card',
        'author' => $user_id,
        'post_status' => 'publish',
        'posts_per_page' => -1
    ]);
}

// Gestion des états
if (empty($user_vcards)) {
    include 'partials/no-products-state.php';
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
?>

<!-- CSS Directs -->
<link rel="stylesheet" href="<?= plugin_dir_url(__FILE__) ?>../../assets/css/statistics.css?v=<?= time() ?>">

<!-- JavaScript Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<!-- Configuration JavaScript -->
<script>
// Configuration globale pour le Statistics Manager
window.STATISTICS_CONFIG = <?= json_encode($stats_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
console.log('📊 Configuration Statistics chargée:', window.STATISTICS_CONFIG);
</script>

<!-- ================================================================================ -->
<!-- HTML DE LA PAGE -->
<!-- ================================================================================ -->

<div class="dashboard-statistics">
    <!-- Header avec titre et contrôles -->
    <div class="statistics-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="h3 mb-1"><i class="fas fa-chart-line me-2 text-primary"></i><?= esc_html($page_title) ?></h2>
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

        <!-- Filtre profil (si plusieurs vCards) -->
        <?php if ($show_profile_filter): ?>
        <div class="row mt-3 mb-4">
            <div class="col-md-6">
                <select class="form-select" id="profileFilter">
                    <option value="">Tous les profils</option>
                    <?php foreach ($user_vcards as $vcard): ?>
                        <?php 
                        // ✅ CORRECTION: $vcard est un objet WP_Post, pas un array
                        $vcard_id = $vcard->ID;
                        $firstname = get_post_meta($vcard_id, 'firstname', true) ?: get_post_meta($vcard_id, 'first_name', true) ?: '';
                        $lastname = get_post_meta($vcard_id, 'lastname', true) ?: get_post_meta($vcard_id, 'last_name', true) ?: '';
                        $full_name = trim($firstname . ' ' . $lastname) ?: 'Profil #' . $vcard_id;
                        ?>
                        <option value="<?= esc_attr($vcard_id) ?>">
                            <?= esc_html($full_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-outline-primary" id="exportBtn">
                    <i class="fas fa-download me-2"></i>Exporter
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Stats principales -->
    <div class="row" id="statsCards">
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-primary text-white loading">
                <div class="stat-value">...</div>
                <div class="stat-label">Vues du profil</div>
                <div class="stat-change">Chargement...</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-success text-white loading">
                <div class="stat-value">...</div>
                <div class="stat-label">Contacts générés</div>
                <div class="stat-change">Chargement...</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-info text-white loading">
                <div class="stat-value">...</div>
                <div class="stat-label">Scans NFC</div>
                <div class="stat-change">Chargement...</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-warning text-white loading">
                <div class="stat-value">...</div>
                <div class="stat-label">Taux conversion</div>
                <div class="stat-change">Chargement...</div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Évolution des vues</h5>
                </div>
                <div class="card-body">
                    <canvas id="viewsChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sources de trafic</h5>
                </div>
                <div class="card-body">
                    <canvas id="sourcesChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des appareils -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Types d'appareils</h5>
                </div>
                <div class="card-body">
                    <div id="deviceTypes">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                            <span class="ms-2">Chargement...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Activité récente</h5>
                </div>
                <div class="card-body">
                    <div id="recentActivity">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                            <span class="ms-2">Chargement...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= plugin_dir_url(__FILE__) ?>../../assets/js/dashboard/statistics-manager.js?v=<?= time() ?>"></script>