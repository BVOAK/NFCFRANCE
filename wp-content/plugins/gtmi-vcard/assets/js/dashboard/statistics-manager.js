/**
 * Statistics Manager - Dashboard NFC France
 * Architecture standardisée - Gestion interface et AJAX
 */

class NFCStatisticsManager {
    constructor(config) {
        this.config = config;
        this.currentPeriod = config.default_period;
        this.currentProfile = '';
        this.charts = {};
        this.data = null;
        
        console.log('📊 Configuration Statistics Manager:', config);
        this.init();
    }
    
    async init() {
        console.log('🚀 Initialisation Statistics Manager');
        this.bindEvents();
        await this.loadStatistics();
    }
    
    bindEvents() {
        console.log('🔗 Liaison des événements');
        
        // Filtre période
        const periodFilter = document.getElementById('periodFilter');
        if (periodFilter) {
            periodFilter.addEventListener('change', (e) => {
                console.log('📅 Changement période:', e.target.value);
                this.currentPeriod = e.target.value;
                this.loadStatistics();
            });
        }
        
        // Filtre profil (si multi-vCard)
        const profileFilter = document.getElementById('profileFilter');
        if (profileFilter) {
            profileFilter.addEventListener('change', (e) => {
                console.log('👤 Changement profil:', e.target.value);
                this.currentProfile = e.target.value;
                this.loadStatistics();
            });
        }
        
        // Export
        const exportBtn = document.getElementById('exportBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                console.log('📥 Export demandé');
                this.exportStatistics();
            });
        }
    }
    
    async loadStatistics() {
        console.log('📊 Chargement des statistiques...');
        this.showLoading(true);
        
        try {
            const data = await this.callAjax('get_statistics_data', {
                period: this.currentPeriod,
                profile: this.currentProfile
            });
            
            console.log('✅ Données statistiques reçues:', data);
            this.data = data;
            
            this.renderStatsCards();
            this.renderCharts();
            this.renderRecentActivity();
            
        } catch (error) {
            console.error('❌ Erreur chargement statistiques:', error);
            this.showNotification('Erreur lors du chargement des statistiques', 'error');
        } finally {
            this.showLoading(false);
        }
    }
    
    renderStatsCards() {
        console.log('📊 Rendu des cartes statistiques');
        
        if (!this.data || !this.data.stats) {
            console.warn('⚠️ Pas de données stats disponibles');
            return;
        }
        
        const { stats } = this.data;
        
        const cardsHtml = `
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-primary text-white">
                    <div class="stat-value">${this.formatNumber(stats.total_views || 0)}</div>
                    <div class="stat-label">Vues du profil</div>
                    <div class="stat-change ${this.getChangeClass(stats.views_change)}">
                        <i class="fas fa-arrow-${stats.views_change >= 0 ? 'up' : 'down'}"></i>
                        ${Math.abs(stats.views_change || 0)}% vs période précédente
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-success text-white">
                    <div class="stat-value">${this.formatNumber(stats.total_contacts || 0)}</div>
                    <div class="stat-label">Contacts générés</div>
                    <div class="stat-change ${this.getChangeClass(stats.contacts_change)}">
                        <i class="fas fa-arrow-${stats.contacts_change >= 0 ? 'up' : 'down'}"></i>
                        ${Math.abs(stats.contacts_change || 0)}% vs période précédente
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-info text-white">
                    <div class="stat-value">${this.formatNumber(stats.total_scans || 0)}</div>
                    <div class="stat-label">Scans NFC</div>
                    <div class="stat-change ${this.getChangeClass(stats.scans_change)}">
                        <i class="fas fa-arrow-${stats.scans_change >= 0 ? 'up' : 'down'}"></i>
                        ${Math.abs(stats.scans_change || 0)}% vs période précédente
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-warning text-white">
                    <div class="stat-value">${(stats.conversion_rate || 0).toFixed(1)}%</div>
                    <div class="stat-label">Taux conversion</div>
                    <div class="stat-change ${this.getChangeClass(stats.conversion_change)}">
                        <i class="fas fa-arrow-${stats.conversion_change >= 0 ? 'up' : 'down'}"></i>
                        ${Math.abs(stats.conversion_change || 0).toFixed(1)}% vs période précédente
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('statsCards').innerHTML = cardsHtml;
    }
    
    renderCharts() {
        console.log('📈 Rendu des graphiques');
        
        if (!this.data || !this.data.charts) {
            console.warn('⚠️ Pas de données graphiques disponibles');
            return;
        }
        
        // Détruire les graphiques existants
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });
        this.charts = {};
        
        const { charts } = this.data;
        
        this.createViewsChart(charts.views_evolution || []);
        this.createSourceChart(charts.traffic_sources || []);
        this.createContactsChart(charts.contacts_evolution || []);
    }
    
    createViewsChart(data) {
        const ctx = document.getElementById('viewsChart').getContext('2d');
        
        this.charts.views = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.date),
                datasets: [{
                    label: 'Vues',
                    data: data.map(item => item.views),
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
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => this.formatNumber(value)
                        }
                    }
                }
            }
        });
    }
    
    createSourceChart(data) {
        const ctx = document.getElementById('sourceChart').getContext('2d');
        
        const colors = ['#0d6efd', '#198754', '#dc3545', '#ffc107', '#6f42c1'];
        
        this.charts.source = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.source),
                datasets: [{
                    data: data.map(item => item.count),
                    backgroundColor: colors.slice(0, data.length),
                    borderWidth: 0
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
    
    createContactsChart(data) {
        const ctx = document.getElementById('contactsChart').getContext('2d');
        
        this.charts.contacts = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.date),
                datasets: [{
                    label: 'Contacts',
                    data: data.map(item => item.contacts),
                    backgroundColor: '#198754',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    
    renderRecentActivity() {
        console.log('🕒 Rendu activité récente');
        
        if (!this.data || !this.data.recent_activity) {
            document.getElementById('recentActivity').innerHTML = '<p class="text-muted">Aucune activité récente</p>';
            return;
        }
        
        const activities = this.data.recent_activity;
        
        const activityHtml = activities.map(activity => `
            <div class="d-flex align-items-center mb-3">
                <div class="bg-${activity.type === 'contact' ? 'primary' : 'success'} bg-opacity-10 rounded-circle p-2 me-3">
                    <i class="fas fa-${activity.type === 'contact' ? 'envelope' : 'eye'} text-${activity.type === 'contact' ? 'primary' : 'success'}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-medium">${activity.description}</div>
                    <small class="text-muted">${activity.time_ago}</small>
                </div>
            </div>
        `).join('');
        
        document.getElementById('recentActivity').innerHTML = activityHtml || '<p class="text-muted">Aucune activité récente</p>';
    }
    
    async exportStatistics() {
        console.log('📥 Export des statistiques');
        
        try {
            const data = await this.callAjax('export_statistics', {
                period: this.currentPeriod,
                profile: this.currentProfile,
                format: 'csv'
            });
            
            if (data.download_url) {
                // Télécharger le fichier
                const link = document.createElement('a');
                link.href = data.download_url;
                link.download = data.filename || 'statistiques.csv';
                link.click();
            } else if (data.csv_content) {
                // Créer le fichier CSV côté client
                const blob = new Blob([data.csv_content], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `statistiques_nfc_${this.currentPeriod}_${Date.now()}.csv`;
                link.click();
            }
            
            this.showNotification('Export réussi!', 'success');
            
        } catch (error) {
            console.error('❌ Erreur export:', error);
            this.showNotification('Erreur lors de l\'export', 'error');
        }
    }
    
    // === UTILITAIRES ===
    
    async callAjax(action, data = {}) {
        const ajaxData = {
            action: `nfc_${action}`,
            nonce: this.config.nonce,
            ...data
        };
        
        console.log(`🔗 Appel AJAX: ${action}`, ajaxData);
        
        try {
            const response = await fetch(this.config.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(ajaxData)
            });
            
            const result = await response.json();
            console.log(`✅ Réponse ${action}:`, result);
            
            if (!result.success) {
                throw new Error(result.data?.message || 'Erreur AJAX');
            }
            
            return result.data;
        } catch (error) {
            console.error(`❌ Erreur ${action}:`, error);
            throw error;
        }
    }
    
    showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        const chartLoadings = document.querySelectorAll('.chart-loading');
        
        if (show) {
            overlay?.classList.remove('d-none');
            chartLoadings.forEach(loading => loading.classList.remove('d-none'));
        } else {
            overlay?.classList.add('d-none');
            chartLoadings.forEach(loading => loading.classList.add('d-none'));
        }
    }
    
    showNotification(message, type = 'info') {
        console.log(`🔔 Notification ${type}:`, message);
        
        const container = document.getElementById('notificationContainer');
        if (!container) return;
        
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        container.appendChild(notification);
        
        // Auto-remove après 5 secondes
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toLocaleString();
    }
    
    getChangeClass(change) {
        if (!change) return '';
        return change >= 0 ? 'positive' : 'negative';
    }
}

// ===============================================================================
// INITIALISATION AUTOMATIQUE
// ===============================================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM Ready - Initialisation Statistics Manager');
    
    if (typeof statisticsConfig !== 'undefined') {
        window.statisticsManager = new NFCStatisticsManager(statisticsConfig);
    } else {
        console.error('❌ Configuration statisticsConfig non trouvée!');
    }
});

// Export pour utilisation externe si nécessaire
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NFCStatisticsManager;
}