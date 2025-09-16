/**
 * Overview Manager - Dashboard NFC France
 * Gestion interface overview multi-vCard
 */
class OverviewManager {
    constructor() {
        this.config = window.OVERVIEW_CONFIG || {};
        this.charts = {};
        this.currentView = 'global';
        this.currentPeriod = '30d';
        this.currentProfile = null;
        
        console.log('🚀 Initialisation Overview Manager', this.config);
        this.init();
    }
    
    init() {
        if (!this.config.user_id) {
            console.error('❌ Configuration Overview manquante');
            return;
        }
        
        // Initialiser les gestionnaires d'événements
        this.setupEventHandlers();
        
        // Charger les données initiales
        this.loadInitialData();
        
        // Initialiser les graphiques
        this.initializeCharts();
        
        console.log('✅ Overview Manager initialisé');
    }
    
    setupEventHandlers() {
        console.log('🔧 Configuration des event handlers');
        
        // Sélecteur mode de vue
        const viewModeSelector = document.getElementById('viewModeSelector');
        if (viewModeSelector) {
            viewModeSelector.addEventListener('change', (e) => {
                this.switchViewMode(e.target.value);
            });
        }
        
        // Sélecteur de profil (apparaît en mode individual)
        const profileSelector = document.getElementById('profileSelector');
        if (profileSelector) {
            profileSelector.addEventListener('change', (e) => {
                this.currentProfile = e.target.value ? parseInt(e.target.value) : null;
                this.refreshData();
            });
        }
        
        // Sélecteur de période
        const periodSelector = document.getElementById('periodSelector');
        if (periodSelector) {
            periodSelector.addEventListener('change', (e) => {
                this.currentPeriod = e.target.value;
                this.refreshData();
            });
        }
        
        // Bouton export
        const exportBtn = document.getElementById('exportOverview');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.exportOverview();
            });
        }
        
        // Bouton voir toutes les cartes
        const viewAllBtn = document.getElementById('viewAllCards');
        if (viewAllBtn) {
            viewAllBtn.addEventListener('click', () => {
                this.showAllCards();
            });
        }
        
        console.log('✅ Event handlers configurés');
    }
    
    loadInitialData() {
        console.log('📊 Chargement des données Overview...');
        
        // Charger l'activité récente
        this.loadRecentActivity();
        
        // Charger les données des graphiques
        this.loadChartData();
    }
    
    loadRecentActivity() {
        console.log('🕒 Chargement activité récente...');
        
        const activityContainer = document.getElementById('recentActivity');
        if (!activityContainer) return;
        
        // Appel AJAX pour récupérer l'activité
        this.callAjax('nfc_get_dashboard_overview', {
            user_id: this.config.user_id,
            view_mode: this.currentView,
            period: this.currentPeriod,
            profile_id: this.currentProfile,
            section: 'activity'
        })
        .then(response => {
            if (response.success && response.data && response.data.recent_activity) {
                this.renderRecentActivity(response.data.recent_activity);
            } else {
                this.showEmptyActivity();
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement activité:', error);
            this.showErrorActivity();
        });
    }
    
    renderRecentActivity(activities) {
        const container = document.getElementById('recentActivity');
        if (!container) return;
        
        if (!activities || activities.length === 0) {
            this.showEmptyActivity();
            return;
        }
        
        let html = '<div class="list-group list-group-flush">';
        
        activities.forEach(activity => {
            const iconClass = activity.icon || 'user-plus';
            const colorClass = activity.color || 'success';
            
            html += `
                <div class="list-group-item border-0 px-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-${iconClass} text-${colorClass} me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">${this.escapeHtml(activity.contact_name)}</div>
                            <small class="text-muted">${this.escapeHtml(activity.contact_company)}</small>
                            ${activity.profile_name ? '<div><small class="badge bg-light text-dark">' + this.escapeHtml(activity.profile_name) + '</small></div>' : ''}
                        </div>
                        <small class="text-muted">${activity.time_ago}</small>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
        
        console.log('✅ Activité récente rendue:', activities.length, 'éléments');
    }
    
    showEmptyActivity() {
        const container = document.getElementById('recentActivity');
        if (!container) return;
        
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-clock fa-2x mb-3"></i>
                <p class="mb-0">Aucune activité récente</p>
                <small>Les nouvelles interactions apparaîtront ici</small>
            </div>
        `;
    }
    
    showErrorActivity() {
        const container = document.getElementById('recentActivity');
        if (!container) return;
        
        container.innerHTML = `
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle"></i>
                Erreur de chargement de l'activité
            </div>
        `;
    }
    
    initializeCharts() {
        console.log('📈 Initialisation des graphiques Chart.js...');
        
        if (typeof Chart === 'undefined') {
            console.error('❌ Chart.js non disponible');
            return;
        }
        
        // Graphique principal - évolution temporelle
        this.initPerformanceChart();
        
        // Graphique secondaire - adaptatif
        this.initSecondaryChart();
        
        console.log('✅ Graphiques initialisés');
    }
    
    initPerformanceChart() {
        const canvas = document.getElementById('performanceChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        this.charts.performance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Vues',
                        data: [],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Contacts',
                        data: [],
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Vues'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Contacts'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
        
        console.log('✅ Graphique performance initialisé');
    }
    
    initSecondaryChart() {
        const canvas = document.getElementById('secondaryChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        this.charts.secondary = new Chart(ctx, {
            type: this.currentView === 'individual' ? 'doughnut' : 'bar',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#0d6efd', '#198754', '#ffc107', 
                        '#dc3545', '#6f42c1', '#fd7e14'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: this.currentView === 'individual',
                        position: 'right'
                    }
                }
            }
        });
        
        console.log('✅ Graphique secondaire initialisé');
    }
    
    loadChartData() {
        console.log('📊 Chargement données graphiques...');
        
        this.callAjax('nfc_get_dashboard_overview', {
            user_id: this.config.user_id,
            view_mode: this.currentView,
            period: this.currentPeriod,
            profile_id: this.currentProfile,
            section: 'charts'
        })
        .then(response => {
            if (response.success && response.data && response.data.charts) {
                this.updateCharts(response.data.charts);
                this.updateKPI(response.data.kpi);
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement graphiques:', error);
            this.showChartError();
        });
    }
    
    updateCharts(chartData) {
        console.log('📈 Mise à jour des graphiques...', chartData);
        
        // Mettre à jour le graphique performance
        if (this.charts.performance && chartData.performance) {
            const perf = chartData.performance;
            
            this.charts.performance.data.labels = perf.labels || [];
            this.charts.performance.data.datasets[0].data = perf.views || [];
            this.charts.performance.data.datasets[1].data = perf.contacts || [];
            this.charts.performance.update();
            
            console.log('✅ Graphique performance mis à jour');
        }
        
        // Mettre à jour le graphique secondaire
        if (this.charts.secondary && chartData.secondary) {
            const sec = chartData.secondary;
            
            this.charts.secondary.data.labels = sec.labels || [];
            this.charts.secondary.data.datasets[0].data = sec.data || [];
            
            if (sec.colors) {
                this.charts.secondary.data.datasets[0].backgroundColor = sec.colors;
            }
            
            this.charts.secondary.update();
            
            console.log('✅ Graphique secondaire mis à jour');
        }
    }
    
    updateKPI(kpiData) {
        if (!kpiData) return;
        
        console.log('📊 Mise à jour des KPI...', kpiData);
        
        // Mettre à jour les valeurs
        this.updateKPIValue('stat-total-views', kpiData.total_views);
        this.updateKPIValue('stat-total-contacts', kpiData.total_contacts);
        this.updateKPIValue('stat-conversion-rate', kpiData.conversion_rate + '%');
        this.updateKPIValue('stat-active-cards', kpiData.active_cards + '/' + this.config.total_cards);
        
        // Mettre à jour les tendances
        this.updateTrend('views-trend', kpiData.growth_views);
        this.updateTrend('contacts-trend', kpiData.growth_contacts);
        this.updateTrend('conversion-trend', kpiData.growth_conversion);
        
        console.log('✅ KPI mis à jour');
    }
    
    updateKPIValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = this.formatNumber(value);
        }
    }
    
    updateTrend(elementId, growth) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        const isPositive = growth >= 0;
        const icon = isPositive ? 'arrow-up' : 'arrow-down';
        const sign = isPositive ? '+' : '';
        
        element.innerHTML = `<i class="fas fa-${icon}"></i> ${sign}${growth}%`;
        element.className = element.className.replace(/(text-success|text-danger)/, '');
        element.classList.add(isPositive ? 'text-success' : 'text-danger');
    }
    
    switchViewMode(mode) {
        console.log('🔄 Basculement mode vue:', mode);
        
        this.currentView = mode;
        
        // Afficher/masquer le sélecteur de profil
        const profileSelector = document.getElementById('profileSelector');
        if (profileSelector) {
            profileSelector.style.display = mode === 'individual' ? 'block' : 'none';
        }
        
        // Mettre à jour les titres des graphiques
        this.updateChartTitles(mode);
        
        // Recréer le graphique secondaire avec le bon type
        this.destroySecondaryChart();
        this.initSecondaryChart();
        
        // Recharger les données
        this.refreshData();
    }
    
    updateChartTitles(mode) {
        const chartTitle = document.getElementById('chart-title');
        const secondaryTitle = document.getElementById('secondary-chart-title');
        
        if (chartTitle) {
            chartTitle.textContent = mode === 'individual' ? 
                'Évolution du profil sélectionné' : 
                'Évolution Multi-Profils (30 jours)';
        }
        
        if (secondaryTitle) {
            secondaryTitle.textContent = mode === 'individual' ? 
                'Sources de trafic' : 
                'Top Performers';
        }
    }
    
    destroySecondaryChart() {
        if (this.charts.secondary) {
            this.charts.secondary.destroy();
            delete this.charts.secondary;
        }
    }
    
    refreshData() {
        console.log('🔄 Rafraîchissement des données...');
        
        this.loadRecentActivity();
        this.loadChartData();
    }
    
    exportOverview() {
        console.log('📤 Export des données overview...');
        
        // Placeholder pour l'export
        alert('Export en développement - données: ' + 
              this.currentView + ' mode, période ' + this.currentPeriod);
    }
    
    showAllCards() {
        console.log('📋 Affichage de toutes les cartes...');
        
        // Redirection vers la page cards-list
        window.location.href = '/mon-compte/nfc-dashboard/?page=cards-list';
    }
    
    showChartError() {
        const containers = ['performanceChart', 'secondaryChart'];
        containers.forEach(containerId => {
            const container = document.getElementById(containerId);
            if (container) {
                const parent = container.parentElement;
                parent.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle text-warning fa-2x mb-3"></i>
                        <p class="text-muted">Erreur de chargement du graphique</p>
                    </div>
                `;
            }
        });
    }
    
    // Méthode AJAX réutilisable
    callAjax(action, data = {}) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            const formData = new FormData();
            
            // Ajouter les données par défaut
            formData.append('action', action);
            formData.append('nonce', this.config.nonce);
            
            // Ajouter les données spécifiques
            Object.keys(data).forEach(key => {
                formData.append(key, data[key]);
            });
            
            xhr.open('POST', this.config.ajax_url);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            resolve(response);
                        } catch (e) {
                            reject('Erreur parsing JSON: ' + e.message);
                        }
                    } else {
                        reject('Erreur HTTP: ' + xhr.status);
                    }
                }
            };
            
            xhr.send(formData);
        });
    }
    
    // Utilitaires
    formatNumber(num) {
        if (typeof num !== 'number') return num;
        
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        
        return num.toLocaleString();
    }
    
    escapeHtml(text) {
        if (!text) return '';
        
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM chargé, initialisation Overview...');
    
    if (window.OVERVIEW_CONFIG) {
        window.overviewManager = new OverviewManager();
    } else {
        console.error('❌ OVERVIEW_CONFIG manquant');
    }
});

// Export global pour debugging
if (typeof window !== 'undefined') {
    window.OverviewManager = OverviewManager;
}