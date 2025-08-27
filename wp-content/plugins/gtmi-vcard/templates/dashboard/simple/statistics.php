<?php
/**
 * Template: Page Statistiques - Version avec JS externalisé
 */

if (!defined('ABSPATH')) {
    exit;
}

global $nfc_vcard, $nfc_current_page;
$vcard = $nfc_vcard;
$vcard_id = $vcard->ID;

// Données utilisateur pour le contexte
$current_user = wp_get_current_user();
$first_name = get_post_meta($vcard_id, 'firstname', true) ?: $current_user->first_name;
$last_name = get_post_meta($vcard_id, 'lastname', true) ?: $current_user->last_name;
$full_name = trim($first_name . ' ' . $last_name) ?: 'Utilisateur';

// Configuration pour l'API
$stats_config = [
    'vcard_id' => $vcard_id,
    'ajax_url' => admin_url('admin-ajax.php'),
    'api_url' => home_url('/wp-json/gtmi_vcard/v1/'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'public_url' => get_permalink($vcard_id),
    'colors' => [
        'primary' => '#0040C1',
        'secondary' => '#667eea',
        'success' => '#10b981',
        'info' => '#3b82f6',
        'warning' => '#f59e0b',
        'danger' => '#ef4444'
    ]
];

?>

<!-- PAGE STATISTIQUES -->
<div class="nfc-page-content">
    
    <!-- HEADER DE PAGE -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="h4 text-dark mb-1">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Statistiques de <?php echo esc_html($full_name); ?>
                    </h2>
                    <p class="text-muted mb-0">Analytics détaillées de votre vCard #<?php echo $vcard_id; ?></p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshStats()">
                        <i class="fas fa-sync-alt me-1"></i>
                        Actualiser
                    </button>
                    <button class="btn btn-success btn-sm" onclick="exportStats()">
                        <i class="fas fa-download me-1"></i>
                        Exporter
                    </button>
                    <a href="<?php echo get_permalink($vcard_id); ?>" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>
                        Voir vCard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTRES DE PÉRIODE -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            Période d'analyse
                        </h6>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="period" id="period7" value="7" checked>
                            <label class="btn btn-outline-primary" for="period7">7 jours</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period30" value="30">
                            <label class="btn btn-outline-primary" for="period30">30 jours</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period90" value="90">
                            <label class="btn btn-outline-primary" for="period90">3 mois</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period365" value="365">
                            <label class="btn btn-outline-primary" for="period365">1 an</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs PRINCIPAUX -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 h-100 stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                            <i class="fas fa-eye fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Vues totales</h6>
                            <h3 class="mb-0 counter" id="totalViews">
                                <span class="loading-dots">•••</span>
                            </h3>
                            <small class="text-success" id="viewsGrowth">
                                <i class="fas fa-spinner fa-spin me-1"></i>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 h-100 stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-success bg-opacity-10 text-success rounded p-3 me-3">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Visiteurs uniques</h6>
                            <h3 class="mb-0 counter" id="uniqueVisitors">
                                <span class="loading-dots">•••</span>
                            </h3>
                            <small class="text-success" id="visitorsGrowth">
                                <i class="fas fa-spinner fa-spin me-1"></i>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 h-100 stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-info bg-opacity-10 text-info rounded p-3 me-3">
                            <i class="fas fa-mouse-pointer fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Taux d'interaction</h6>
                            <h3 class="mb-0 counter" id="interactionRate">
                                <span class="loading-dots">•••</span>
                            </h3>
                            <small class="text-danger" id="interactionGrowth">
                                <i class="fas fa-spinner fa-spin me-1"></i>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 h-100 stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Durée moyenne</h6>
                            <h3 class="mb-0 counter" id="avgDuration">
                                <span class="loading-dots">•••</span>
                            </h3>
                            <small class="text-muted" id="durationGrowth">
                                <i class="fas fa-spinner fa-spin me-1"></i>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques principaux -->
    <div class="row g-3 mb-4">
        <!-- Évolution des vues -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Évolution des vues</h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="viewType" id="viewDaily" value="daily" checked>
                            <label class="btn btn-outline-secondary" for="viewDaily">Jour</label>
                            
                            <input type="radio" class="btn-check" name="viewType" id="viewWeekly" value="weekly">
                            <label class="btn btn-outline-secondary" for="viewWeekly">Semaine</label>
                            
                            <input type="radio" class="btn-check" name="viewType" id="viewMonthly" value="monthly">
                            <label class="btn btn-outline-secondary" for="viewMonthly">Mois</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-loading text-center py-5" id="viewsChartLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="text-muted mt-3 mb-0">Chargement des données...</p>
                    </div>
                    <div class="chart-container" style="position: relative; height: 300px; display: none;" id="viewsChartContainer">
                        <canvas id="viewsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Répartition des sources -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Sources de trafic</h5>
                </div>
                <div class="card-body">
                    <div class="chart-loading text-center py-5" id="sourcesChartLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <div class="chart-container" style="position: relative; height: 300px; display: none;" id="sourcesChartContainer">
                        <canvas id="sourcesChart"></canvas>
                    </div>
                    <div id="sourcesLegend" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats détaillées -->
    <div class="row g-3 mb-4">
        <!-- Appareils -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Types d'appareils</h5>
                </div>
                <div class="card-body">
                    <div class="chart-loading text-center py-4" id="devicesChartLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div class="chart-container" style="position: relative; height: 200px; display: none;" id="devicesChartContainer">
                        <canvas id="devicesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Heures d'affluence -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Heures d'affluence</h5>
                </div>
                <div class="card-body">
                    <div class="chart-loading text-center py-4" id="hoursChartLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div class="chart-container" style="position: relative; height: 200px; display: none;" id="hoursChartContainer">
                        <canvas id="hoursChart"></canvas>
                    </div>
                    <div id="peakInfo" class="mt-2 small text-muted" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactions et localisations -->
    <div class="row g-3 mb-4">
        <!-- Dernières interactions -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Activité récente</h5>
                </div>
                <div class="card-body">
                    <div class="chart-loading text-center py-4" id="interactionsLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 mb-0">Chargement des interactions...</p>
                    </div>
                    <div id="interactionsList" style="display: none;">
                        <!-- Rempli par JavaScript -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top localisations -->
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top localisations</h5>
                </div>
                <div class="card-body">
                    <div class="chart-loading text-center py-4" id="locationsLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div id="topLocations" style="display: none;">
                        <!-- Rempli par JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- À ajouter temporairement dans statistics.php -->
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info">
            <h6>🧪 Debug Tracking</h6>
            <button class="btn btn-sm btn-primary" onclick="testTrackingConnection()">
                Tester connexion API
            </button>
            <button class="btn btn-sm btn-success" onclick="loadStatistics()">
                Forcer reload données
            </button>
            <div id="debug-output" class="mt-2"></div>
        </div>
    </div>
</div>

<script>
function testTrackingConnection() {
    const apiUrl = `${STATS_CONFIG.api_url}statistics/${STATS_CONFIG.vcard_id}`;
    document.getElementById('debug-output').innerHTML = 'Test en cours...';
    
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            document.getElementById('debug-output').innerHTML = 
                `<strong>Résultat API:</strong><br>
                Status: ${data.success ? 'SUCCESS' : 'ERROR'}<br>
                Données: ${data.data ? data.data.length + ' entrées' : 'Aucune'}<br>
                Message: ${data.message || 'Pas de message'}`;
        })
        .catch(error => {
            document.getElementById('debug-output').innerHTML = 
                `<strong>Erreur:</strong> ${error.message}`;
        });
}
</script>