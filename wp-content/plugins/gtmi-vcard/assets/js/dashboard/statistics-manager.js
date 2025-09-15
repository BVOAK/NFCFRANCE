/**
 * Statistics Manager - Version corrigée
 * Fichier: assets/js/dashboard/statistics-manager.js
 */

class NFCStatisticsManager {
    constructor(config) {
        this.config = config;
        this.data = null;
        this.charts = {};
        
        console.log('📊 Statistics Manager initialisé avec config:', config);
        
        this.init();
    }
    
    init() {
        console.log('🚀 Initialisation Statistics Manager');
        
        this.setupEventListeners();
        this.showLoading(true);
        
        // Chargement initial avec délai
        setTimeout(() => {
            this.loadStatistics();
        }, 100);
    }
    
    setupEventListeners() {
        // Changement de période
        $('#periodFilter').on('change', (e) => {
            console.log('📅 Changement période:', e.target.value);
            this.loadStatistics();
        });
        
        // Changement de profil
        $('#profileFilter').on('change', (e) => {
            console.log('👤 Changement profil:', e.target.value);
            this.loadStatistics();
        });
        
        // Export
        $('#exportBtn').on('click', () => {
            this.exportStatistics();
        });
    }
    
    async loadStatistics() {
        console.log('📊 Chargement des statistiques...');
        
        this.showLoading(true);
        
        try {
            const period = $('#periodFilter').val() || this.config.default_period;
            const profile = $('#profileFilter').val() || '';
            
            const response = await this.makeAjaxRequest('nfc_get_statistics_data', {
                period: period,
                profile: profile
            });
            
            console.log('✅ Réponse serveur:', response);
            
            if (response.success) {
                this.data = response.data;
                this.renderAll();
            } else {
                throw new Error(response.data?.message || 'Erreur inconnue');
            }
            
        } catch (error) {
            console.error('❌ Erreur chargement statistiques:', error);
            this.showError('Erreur lors du chargement des statistiques: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }
    
    async makeAjaxRequest(action, data = {}) {
        const requestData = {
            action: action,
            nonce: this.config.nonce,
            ...data
        };
        
        console.log('🔄 Requête AJAX:', action, requestData);
        
        try {
            const response = await fetch(this.config.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(requestData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            // ✅ CORRECTION: Lire la réponse comme texte d'abord pour débugger
            const responseText = await response.text();
            console.log('📝 Réponse brute:', responseText.substring(0, 200) + '...');
            
            // Essayer de parser le JSON
            try {
                return JSON.parse(responseText);
            } catch (jsonError) {
                console.error('❌ Erreur parsing JSON:', jsonError);
                console.error('📄 Réponse complète:', responseText);
                throw new Error('Réponse serveur invalide (pas du JSON)');
            }
            
        } catch (error) {
            console.error('❌ Erreur requête AJAX:', error);
            throw error;
        }
    }
    
    renderAll() {
        console.log('🎨 Rendu de tous les éléments');
        
        this.renderStatsCards();
        this.renderCharts();
        this.renderRecentActivity();
    }
    
    renderStatsCards() {
        console.log('📊 Rendu des cartes statistiques');
        
        if (!this.data?.stats) {
            console.warn('⚠️ Pas de données stats disponibles');
            return;
        }
        
        const { stats } = this.data;
        
        // Carte 1 - Vues
        this.updateStatCard('#statsCards .stat-card:eq(0)', {
            value: this.formatNumber(stats.total_views || 0),
            label: 'Vues du profil',
            change: stats.views_change || 0
        });
        
        // Carte 2 - Contacts
        this.updateStatCard('#statsCards .stat-card:eq(1)', {
            value: this.formatNumber(stats.contacts_generated || 0),
            label: 'Contacts générés',
            change: stats.contacts_change || 0
        });
        
        // Carte 3 - Scans NFC
        this.updateStatCard('#statsCards .stat-card:eq(2)', {
            value: this.formatNumber(stats.nfc_scans || 0),
            label: 'Scans NFC',
            change: stats.scans_change || 0
        });
        
        // Carte 4 - Conversion
        this.updateStatCard('#statsCards .stat-card:eq(3)', {
            value: (stats.conversion_rate || 0).toFixed(1) + '%',
            label: 'Taux conversion',
            change: stats.conversion_change || 0
        });
    }
    
    updateStatCard(selector, data) {
        const card = $(selector);
        if (card.length === 0) return;
        
        card.removeClass('loading');
        card.find('.stat-value').text(data.value);
        card.find('.stat-label').text(data.label);
        
        const changeEl = card.find('.stat-change');
        const changeClass = data.change >= 0 ? 'positive' : 'negative';
        const changeIcon = data.change >= 0 ? 'up' : 'down';
        
        changeEl.removeClass('positive negative')
               .addClass(changeClass)
               .html(`<i class="fas fa-arrow-${changeIcon}"></i> ${Math.abs(data.change)}% vs période précédente`);
    }
    
    renderCharts() {
        console.log('📈 Rendu des graphiques');
        
        if (!this.data?.charts) {
            console.warn('⚠️ Pas de données graphiques disponibles');
            return;
        }
        
        this.renderViewsChart();
        this.renderSourcesChart();
    }
    
    renderViewsChart() {
        const ctx = document.getElementById('viewsChart');
        if (!ctx) return;
        
        // Détruire le graphique existant
        if (this.charts.views) {
            this.charts.views.destroy();
        }
        
        const evolution = this.data.charts.views_evolution || [];
        
        this.charts.views = new Chart(ctx, {
            type: 'line',
            data: {
                labels: evolution.map(item => item.date),
                datasets: [{
                    label: 'Vues',
                    data: evolution.map(item => item.views),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }
    
    renderSourcesChart() {
        const ctx = document.getElementById('sourcesChart');
        if (!ctx) return;
        
        // Détruire le graphique existant
        if (this.charts.sources) {
            this.charts.sources.destroy();
        }
        
        const sources = this.data.charts.traffic_sources || [];
        
        this.charts.sources = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: sources.map(item => item.source),
                datasets: [{
                    data: sources.map(item => item.count),
                    backgroundColor: [
                        '#0d6efd',
                        '#198754',
                        '#ffc107',
                        '#dc3545',
                        '#0dcaf0'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    renderRecentActivity() {
        console.log('📋 Rendu activité récente');
        
        const container = $('#recentActivity');
        
        if (!this.data?.recent_activity?.length) {
            container.html('<p class="text-muted text-center">Aucune activité récente</p>');
            return;
        }
        
        let activityHtml = '<div class="table-responsive"><table class="table"><tbody>';
        
        this.data.recent_activity.forEach(activity => {
            activityHtml += `
                <tr>
                    <td>
                        <strong>${activity.type}</strong><br>
                        <small class="text-muted">${activity.date}</small>
                    </td>
                    <td>${activity.description}</td>
                </tr>
            `;
        });
        
        activityHtml += '</tbody></table></div>';
        container.html(activityHtml);
    }
    
    showLoading(show) {
        if (show) {
            $('#statsCards .stat-card').addClass('loading');
            $('#statsCards .stat-value').text('...');
            $('#statsCards .stat-change').text('Chargement...');
        } else {
            $('#statsCards .stat-card').removeClass('loading');
        }
    }
    
    showError(message) {
        console.error('❌ Affichage erreur:', message);
        
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('.dashboard-statistics').prepend(alertHtml);
    }
    
    formatNumber(num) {
        return new Intl.NumberFormat('fr-FR').format(num);
    }
    
    exportStatistics() {
        console.log('📥 Export des statistiques');
        // TODO: Implémenter l'export
    }
}

// ===============================================================================
// INITIALISATION AUTOMATIQUE
// ===============================================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM Ready - Initialisation Statistics Manager');
    
    // ✅ CORRECTION: Utiliser STATISTICS_CONFIG au lieu de statisticsConfig
    if (typeof STATISTICS_CONFIG !== 'undefined') {
        window.statisticsManager = new NFCStatisticsManager(STATISTICS_CONFIG);
    } else {
        console.error('❌ Configuration STATISTICS_CONFIG non trouvée!');
        console.log('Variables disponibles:', Object.keys(window));
    }
});