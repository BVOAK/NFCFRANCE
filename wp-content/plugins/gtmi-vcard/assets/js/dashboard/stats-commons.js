/**
 * Module commun pour les statistiques NFC
 * 
 * Fichier: assets/js/dashboard/stats-commons.js
 * Fonctions extraites de statistics.js pour réutilisation dans overview et vcard-edit
 */

window.NFCStatsCommons = {
    
    // Configuration par défaut
    config: {
        api_url: '/wp-json/gtmi_vcard/v1/',
        colors: {
            primary: '#0040C1',
            secondary: '#667eea',
            success: '#10b981',
            info: '#3b82f6',
            warning: '#f59e0b',
            danger: '#ef4444'
        }
    },
    
    /**
     * Charger les vraies statistiques depuis l'API REST
     * @param {number} vcardId - ID de la vCard
     * @param {string} apiUrl - URL de base de l'API (optionnel)
     * @returns {Promise} - Promise avec les données transformées
     */
    loadRealStats: function(vcardId, apiUrl = null) {
        return new Promise((resolve, reject) => {
            if (!vcardId) {
                console.error('❌ NFCStatsCommons: vCard ID manquant');
                reject(new Error('vCard ID manquant'));
                return;
            }
            
            const baseUrl = apiUrl || this.config.api_url;
            const statsUrl = `${baseUrl}statistics/${vcardId}`;
            
            console.log('📊 NFCStatsCommons: Chargement stats pour vCard', vcardId);
            console.log('🔗 API URL:', statsUrl);
            
            fetch(statsUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('📡 Réponse API Status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                return response.json();
            })
            .then(apiData => {
                console.log('📊 Données brutes API:', apiData);
                
                if (apiData.success && apiData.data && Array.isArray(apiData.data)) {
                    if (apiData.data.length > 0) {
                        console.log(`✅ ${apiData.data.length} statistiques chargées depuis l'API !`);
                        
                        // Transformer les données API
                        const transformedData = this.transformRealAPIData(apiData.data);
                        resolve(transformedData);
                    } else {
                        console.log('⚠️ Aucune donnée disponible dans l\'API');
                        resolve([]);
                    }
                } else {
                    console.log('⚠️ Structure API inattendue:', apiData);
                    resolve([]);
                }
            })
            .catch(error => {
                console.error('❌ Erreur API:', error);
                reject(error);
            });
        });
    },
    
    /**
     * Transformer les données API en format utilisable
     * @param {Array} realData - Données brutes de l'API
     * @returns {Array} - Données transformées
     */
    transformRealAPIData: function(realData) {
        console.log('🔄 Transformation des données API réelles...');
        
        return realData.map((item) => {
            // Convertir la date
            const createdDate = new Date(item.created_at);
            
            // Mapper les événements aux actions
            let action = 'view';
            let source = 'direct_link';
            let device = 'unknown';
            
            if (item.event) {
                switch (item.event.toLowerCase()) {
                    case 'page_view':
                    case 'vcard_view':
                        action = 'view';
                        break;
                    case 'phone_click':
                    case 'tel_click':
                        action = 'phone_click';
                        break;
                    case 'email_click':
                        action = 'email_click';
                        break;
                    case 'share_contact':
                    case 'vcard_download':
                        action = 'share_contact';
                        break;
                    case 'linkedin_click':
                    case 'facebook_click':
                    case 'twitter_click':
                    case 'instagram_click':
                        action = 'social_click';
                        break;
                    case 'qr_scan':
                        action = 'view';
                        source = 'qr_code';
                        break;
                    case 'nfc_tap':
                        action = 'view';
                        source = 'nfc_tap';
                        break;
                    default:
                        action = 'view';
                }
            }
            
            // Déterminer le device depuis user_agent
            if (item.user_agent) {
                const ua = item.user_agent.toLowerCase();
                if (ua.includes('mobile') || ua.includes('android') || ua.includes('iphone')) {
                    device = 'mobile';
                } else if (ua.includes('tablet') || ua.includes('ipad')) {
                    device = 'tablet';
                } else {
                    device = 'desktop';
                }
            }
            
            // Anonymiser l'IP pour la localisation
            let location = 'Inconnu';
            if (item.location) {
                location = item.location;
            } else if (item.ip_address && item.ip_address !== 'unknown') {
                const ipParts = item.ip_address.split('.');
                if (ipParts.length === 4) {
                    location = `IP ${ipParts[0]}.${ipParts[1]}.xxx.xxx`;
                } else {
                    location = 'Local (127.0.0.1)';
                }
            }
            
            return {
                id: item.id,
                created_at: item.created_at,
                timestamp: item.timestamp || Math.floor(createdDate.getTime() / 1000),
                action: action,
                source: source,
                device: device,
                location: location,
                duration: Math.floor(Math.random() * 180) + 30, // Durée simulée entre 30-210s
                ip_address: item.ip_address,
                user_agent: item.user_agent,
                original_event: item.event, // Garder l'événement original pour debug
                is_real_data: true
            };
        });
    },
    
    /**
     * Calculer les KPIs principaux
     * @param {Array} statsData - Données transformées
     * @returns {Object} - Objet avec les KPIs calculés
     */
    calculateKPIs: function(statsData) {
        console.log('📊 Calcul des KPIs pour', statsData.length, 'entrées');
        
        if (!Array.isArray(statsData) || statsData.length === 0) {
            return {
                totalViews: 0,
                uniqueVisitors: 0,
                interactions: 0,
                interactionRate: 0,
                avgDuration: 0,
                topSources: [],
                deviceBreakdown: { mobile: 0, desktop: 0, tablet: 0 }
            };
        }
        
        // Calculs de base
        const totalViews = statsData.length;
        const uniqueVisitors = new Set(statsData.map(stat => stat.ip_address)).size;
        const interactions = statsData.filter(stat => stat.action !== 'view').length;
        const interactionRate = totalViews > 0 ? (interactions / totalViews * 100) : 0;
        
        // Durée moyenne (basée sur les données simulées pour l'instant)
        const avgDuration = statsData.reduce((sum, stat) => sum + (stat.duration || 0), 0) / totalViews;
        
        // Top sources
        const sourceCounts = {};
        statsData.forEach(stat => {
            sourceCounts[stat.source] = (sourceCounts[stat.source] || 0) + 1;
        });
        
        const topSources = Object.entries(sourceCounts)
            .sort(([,a], [,b]) => b - a)
            .slice(0, 5)
            .map(([source, count]) => ({
                source: this.getSourceLabel(source),
                count: count,
                percentage: (count / totalViews * 100).toFixed(1)
            }));
        
        // Répartition devices
        const deviceBreakdown = { mobile: 0, desktop: 0, tablet: 0 };
        statsData.forEach(stat => {
            if (deviceBreakdown.hasOwnProperty(stat.device)) {
                deviceBreakdown[stat.device]++;
            }
        });
        
        console.log('📊 KPIs calculés:', {
            totalViews,
            uniqueVisitors,
            interactions,
            interactionRate: interactionRate.toFixed(1) + '%'
        });
        
        return {
            totalViews,
            uniqueVisitors,
            interactions,
            interactionRate: parseFloat(interactionRate.toFixed(1)),
            avgDuration: Math.round(avgDuration),
            topSources,
            deviceBreakdown
        };
    },
    
    /**
     * Calculer la croissance par rapport à une période précédente
     * @param {Array} statsData - Données transformées
     * @param {number} period - Période en jours (défaut: 7)
     * @returns {Object} - Objet avec les données de croissance
     */
    calculateGrowth: function(statsData, period = 7) {
        console.log(`📈 Calcul de croissance sur ${period} jours`);
        
        if (!Array.isArray(statsData) || statsData.length === 0) {
            return {
                viewsGrowth: 0,
                visitorsGrowth: 0,
                interactionsGrowth: 0
            };
        }
        
        const now = new Date();
        const periodStart = new Date(now.getTime() - (period * 24 * 60 * 60 * 1000));
        const prevPeriodStart = new Date(now.getTime() - (period * 2 * 24 * 60 * 60 * 1000));
        
        // Stats période actuelle
        const currentStats = statsData.filter(stat => {
            const statDate = new Date(stat.created_at);
            return statDate >= periodStart;
        });
        
        // Stats période précédente
        const prevStats = statsData.filter(stat => {
            const statDate = new Date(stat.created_at);
            return statDate >= prevPeriodStart && statDate < periodStart;
        });
        
        // Calculs de croissance
        const currentViews = currentStats.length;
        const prevViews = prevStats.length;
        const viewsGrowth = prevViews > 0 ? 
            ((currentViews - prevViews) / prevViews * 100) : 
            (currentViews > 0 ? 100 : 0);
        
        const currentVisitors = new Set(currentStats.map(s => s.ip_address)).size;
        const prevVisitors = new Set(prevStats.map(s => s.ip_address)).size;
        const visitorsGrowth = prevVisitors > 0 ? 
            ((currentVisitors - prevVisitors) / prevVisitors * 100) : 
            (currentVisitors > 0 ? 100 : 0);
        
        const currentInteractions = currentStats.filter(s => s.action !== 'view').length;
        const prevInteractions = prevStats.filter(s => s.action !== 'view').length;
        const interactionsGrowth = prevInteractions > 0 ? 
            ((currentInteractions - prevInteractions) / prevInteractions * 100) : 
            (currentInteractions > 0 ? 100 : 0);
        
        console.log('📈 Croissance calculée:', {
            views: viewsGrowth.toFixed(1) + '%',
            visitors: visitorsGrowth.toFixed(1) + '%',
            interactions: interactionsGrowth.toFixed(1) + '%'
        });
        
        return {
            viewsGrowth: parseFloat(viewsGrowth.toFixed(1)),
            visitorsGrowth: parseFloat(visitorsGrowth.toFixed(1)),
            interactionsGrowth: parseFloat(interactionsGrowth.toFixed(1)),
            currentViews,
            prevViews,
            currentVisitors,
            prevVisitors
        };
    },
    
    /**
     * Obtenir les dernières activités
     * @param {Array} statsData - Données transformées
     * @param {number} limit - Nombre d'activités à retourner
     * @returns {Array} - Dernières activités
     */
    getRecentActivity: function(statsData, limit = 5) {
        if (!Array.isArray(statsData) || statsData.length === 0) {
            return [];
        }
        
        return statsData
            .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
            .slice(0, limit)
            .map(stat => ({
                ...stat,
                timeAgo: this.getTimeAgo(new Date(stat.created_at)),
                actionLabel: this.getActionLabel(stat.action)
            }));
    },
    
    /**
     * Obtenir le label d'une source
     * @param {string} source - Source technique
     * @returns {string} - Label lisible
     */
    getSourceLabel: function(source) {
        const labels = {
            'qr_code': 'QR Code',
            'nfc_tap': 'NFC',
            'direct_link': 'Lien direct',
            'social_media': 'Réseaux sociaux',
            'email': 'Email',
            'referral': 'Référence'
        };
        return labels[source] || source;
    },
    
    /**
     * Obtenir le label d'une action
     * @param {string} action - Action technique
     * @returns {string} - Label lisible
     */
    getActionLabel: function(action) {
        const labels = {
            'view': 'Consultation',
            'phone_click': 'Appel téléphone',
            'email_click': 'Envoi email',
            'share_contact': 'Partage contact',
            'social_click': 'Clic réseau social'
        };
        return labels[action] || action;
    },
    
    /**
     * Calculer le temps écoulé depuis une date
     * @param {Date} date - Date à comparer
     * @returns {string} - Temps écoulé en français
     */
    getTimeAgo: function(date) {
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Il y a quelques secondes';
        if (diffInSeconds < 3600) return `Il y a ${Math.floor(diffInSeconds / 60)}min`;
        if (diffInSeconds < 86400) return `Il y a ${Math.floor(diffInSeconds / 3600)}h`;
        if (diffInSeconds < 604800) return `Il y a ${Math.floor(diffInSeconds / 86400)}j`;
        
        return date.toLocaleDateString('fr-FR');
    },
    
    /**
     * Formater une durée en secondes
     * @param {number} seconds - Durée en secondes
     * @returns {string} - Durée formatée
     */
    formatDuration: function(seconds) {
        if (seconds < 60) return `${Math.round(seconds)}s`;
        if (seconds < 3600) return `${Math.floor(seconds / 60)}min ${Math.round(seconds % 60)}s`;
        return `${Math.floor(seconds / 3600)}h ${Math.floor((seconds % 3600) / 60)}min`;
    },
    
    /**
     * Afficher une notification
     * @param {string} message - Message à afficher
     * @param {string} type - Type de notification (success, error, info, warning)
     */
    showNotification: function(message, type = 'info') {
        // Si jQuery est disponible
        if (typeof $ !== 'undefined') {
            const iconClass = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            }[type] || 'fa-info-circle';
            
            const notification = $(`
                <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    <i class="fas ${iconClass} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            
            $('body').append(notification);
            
            setTimeout(() => {
                notification.alert('close');
            }, 5000);
        } else {
            // Fallback console
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }
};

// Initialisation automatique si la configuration globale existe
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.nfcDashboardConfig !== 'undefined') {
        window.NFCStatsCommons.config.api_url = window.nfcDashboardConfig.api_url || window.NFCStatsCommons.config.api_url;
    }
    
    console.log('✅ NFCStatsCommons initialisé');
});