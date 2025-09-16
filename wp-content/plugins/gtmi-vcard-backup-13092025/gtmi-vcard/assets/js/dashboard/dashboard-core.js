/**
 * NFC Dashboard Core JavaScript - Version complète
 * 
 * Gère les interactions principales du dashboard client
 * Fichier: assets/js/dashboard/dashboard-core.js
 */

if (typeof window.NFCDashboard === 'undefined') {
    
    window.NFCDashboard = {
        
        // Configuration
        config: null,
        
        // État
        currentVCardId: null,
        currentPage: null,
        isLoading: false,
        
        /**
         * Initialisation
         */
        init: function() {
            console.log('🚀 NFC Dashboard Core - Initialisation');
            
            // Récupérer la configuration
            this.config = window.nfcDashboardConfig || {};
            this.currentVCardId = this.config.vcard_id;
            this.currentPage = this.config.current_page || 'overview';
            
            if (!this.currentVCardId) {
                console.error('❌ Aucune vCard ID trouvée dans la configuration');
                return;
            }
            
            console.log(`✅ Dashboard initialisé pour vCard ${this.currentVCardId} - Page: ${this.currentPage}`);
            
            // Bind des événements
            this.bindEvents();
            
            // Charger les données initiales
            this.loadInitialData();
            
            // Auto-refresh des stats toutes les 5 minutes
            this.startAutoRefresh();
        },
        
        /**
         * Bind des événements globaux
         */
        bindEvents: function() {
            const self = this;
            const $ = jQuery;
            
            // Navigation mobile
            this.bindMobileNavigation();
            
            // Gestion des erreurs globales AJAX
            $(document).ajaxError(function(event, xhr, settings, error) {
                console.error('Erreur AJAX:', error);
                self.showMessage('Erreur de connexion au serveur', 'error');
                self.hideLoading();
            });
            
            // Loading automatique sur les formulaires
            $(document).on('submit', '.nfc-form', function(e) {
                self.showLoading();
            });
            
            // Click outside sidebar pour fermer sur mobile
            $(document).on('click', function(e) {
                if (window.innerWidth <= 1024) {
                    const sidebar = document.getElementById('nfcSidebar');
                    const menuBtn = document.querySelector('.nfc-mobile-menu-btn');
                    
                    if (sidebar && menuBtn && 
                        !sidebar.contains(e.target) && 
                        !menuBtn.contains(e.target) && 
                        sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                }
            });
            
            console.log('✅ Événements globaux bindés');
        },
        
        /**
         * Navigation mobile
         */
        bindMobileNavigation: function() {
            // Fonction globale pour le toggle mobile
            window.toggleSidebar = function() {
                const sidebar = document.getElementById('nfcSidebar');
                if (sidebar) {
                    sidebar.classList.toggle('show');
                }
            };
            
            // Fonction globale pour partager vCard
            window.shareVCard = function() {
                const vCardUrl = window.NFCDashboard.config.public_url || 
                                `${window.location.origin}/vcard/${window.NFCDashboard.currentVCardId}`;
                
                if (navigator.share) {
                    navigator.share({
                        title: 'Ma vCard NFC',
                        text: 'Découvrez mes informations de contact',
                        url: vCardUrl
                    }).catch(console.error);
                } else {
                    navigator.clipboard.writeText(vCardUrl).then(() => {
                        window.NFCDashboard.showMessage('URL copiée dans le presse-papiers', 'success');
                    }).catch(() => {
                        alert('URL de la vCard : ' + vCardUrl);
                    });
                }
            };
        },
        
        /**
         * Chargement des données initiales
         */
        loadInitialData: function() {
            console.log('📊 Chargement des données initiales...');
            
            // Charger selon la page active
            switch (this.currentPage) {
                case 'overview':
                    this.loadDashboardStats();
                    break;
                case 'vcard-edit':
                    this.loadVCardData();
                    break;
                case 'contacts':
                    this.loadContactsData();
                    break;
                case 'statistics':
                    this.loadStatisticsData();
                    break;
                case 'qr-codes':
                    this.loadQRData();
                    break;
            }
            
            // Charger les stats de la sidebar dans tous les cas
            this.loadSidebarStats();
        },
        
        /**
         * Stats pour la sidebar
         */
        loadSidebarStats: function() {
            console.log('📊 Chargement stats sidebar...');
            
            // Charger statistiques
            this.apiCall('statistics', 'GET', null, (response) => {
                if (response && Array.isArray(response)) {
                    this.updateSidebarStats(response);
                }
            });
            
            // Charger contacts
            this.apiCall('contacts', 'GET', null, (response) => {
                if (response && Array.isArray(response)) {
                    this.updateSidebarContacts(response);
                }
            });
        },
        
        /**
         * Mise à jour stats sidebar
         */
        updateSidebarStats: function(stats) {
            const $ = jQuery;
            
            // Calculs des stats
            const totalViews = stats.length;
            const oneWeekAgo = new Date();
            oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
            
            const weekViews = stats.filter(stat => {
                const statDate = new Date(stat.created_at || stat.date);
                return statDate >= oneWeekAgo;
            }).length;
            
            // Mise à jour des badges dans la sidebar
            $('.nfc-nav-link').each(function() {
                const $link = $(this);
                const href = $link.attr('href');
                
                if (href && href.includes('page=statistics')) {
                    $link.find('.nfc-nav-badge').text(totalViews);
                } else if (href && href.includes('page=qr-codes')) {
                    // Badge pour QR codes (simulated)
                    $link.find('.nfc-nav-badge').text('2');
                }
            });
            
            console.log(`✅ Sidebar stats mise à jour: ${totalViews} vues totales, ${weekViews} cette semaine`);
        },
        
        /**
         * Mise à jour contacts sidebar
         */
        updateSidebarContacts: function(contacts) {
            const $ = jQuery;
            
            const totalContacts = contacts.length;
            
            // Mise à jour badge contacts
            $('.nfc-nav-link').each(function() {
                const $link = $(this);
                const href = $link.attr('href');
                
                if (href && href.includes('page=contacts')) {
                    $link.find('.nfc-nav-badge').text(totalContacts);
                }
            });
            
            console.log(`✅ Sidebar contacts mise à jour: ${totalContacts} contacts`);
        },
        
        /**
         * Chargement stats dashboard (page overview)
         */
        loadDashboardStats: function() {
            console.log('📊 Chargement stats dashboard overview...');
            
            Promise.all([
                this.apiCallPromise('statistics'),
                this.apiCallPromise('contacts')
            ]).then(([statsResponse, contactsResponse]) => {
                const stats = Array.isArray(statsResponse) ? statsResponse : [];
                const contacts = Array.isArray(contactsResponse) ? contactsResponse : [];
                
                this.updateDashboardOverview(stats, contacts);
            }).catch(error => {
                console.error('Erreur chargement dashboard stats:', error);
                this.showMessage('Erreur lors du chargement des statistiques', 'error');
            });
        },
        
        /**
         * Mise à jour vue d'ensemble dashboard
         */
        updateDashboardOverview: function(stats, contacts) {
            const $ = jQuery;
            
            // Calculs de base
            const oneWeekAgo = new Date();
            oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
            const today = new Date();
            
            const weekStats = stats.filter(stat => {
                const statDate = new Date(stat.created_at || stat.date);
                return statDate >= oneWeekAgo;
            });
            
            const todayContacts = contacts.filter(contact => {
                const contactDate = new Date(contact.created_at || contact.date);
                return contactDate.toDateString() === today.toDateString();
            });
            
            // QR scans simulés basés sur les stats
            const qrScans = Math.floor(stats.length * 0.6); // 60% des vues viennent du QR
            const socialShares = Math.floor(stats.length * 0.1); // 10% sont des partages
            
            // Mise à jour de l'affichage
            $('#stat-views-week').text(weekStats.length);
            $('#stat-new-contacts').text(contacts.length);
            $('#stat-qr-scans').text(qrScans);
            $('#stat-social-shares').text(socialShares);
            
            // Calculs de croissance (simulés)
            const viewsGrowth = Math.floor(Math.random() * 20) + 5;
            const contactsToday = todayContacts.length;
            const qrGrowth = Math.floor(viewsGrowth * 0.8);
            
            $('#stat-views-growth').text(`+${viewsGrowth}%`);
            $('#stat-contacts-today').text(`+${contactsToday} aujourd'hui`);
            $('#stat-qr-growth').text(`+${qrGrowth}%`);
            $('#stat-shares-growth').text('0%');
            
            console.log(`✅ Dashboard overview mis à jour: ${weekStats.length} vues/semaine, ${contacts.length} contacts, ${qrScans} QR scans`);
        },
        
        /**
         * Appel API générique avec Promise
         */
        apiCallPromise: function(endpoint, method = 'GET', data = null) {
            return new Promise((resolve, reject) => {
                this.apiCall(endpoint, method, data, resolve, reject);
            });
        },
        
        /**
         * Appel API générique
         */
        apiCall: function(endpoint, method = 'GET', data = null, callback = null, errorCallback = null) {
            const $ = jQuery;
            
            // Déterminer l'URL selon l'endpoint
            let apiUrl = '';
            
            switch (endpoint) {
                case 'vcard':
                    apiUrl = `${this.config.api_url}vcard/${this.currentVCardId}`;
                    break;
                case 'statistics':
                    apiUrl = `${this.config.api_url}statistics/${this.currentVCardId}`;
                    break;
                case 'contacts':
                    apiUrl = `${this.config.api_url}leads/${this.currentVCardId}`;
                    break;
                default:
                    apiUrl = `${this.config.api_url}${endpoint}`;
            }
            
            console.log(`🌐 API Call: ${method} ${apiUrl}`);
            
            const ajaxOptions = {
                url: apiUrl,
                method: method,
                dataType: 'json',
                timeout: 10000,
                success: (response) => {
                    console.log(`✅ Réponse API ${endpoint}:`, response);
                    if (callback) callback(response);
                },
                error: (xhr, status, error) => {
                    console.error(`❌ Erreur API ${endpoint}:`, { xhr, status, error });
                    if (errorCallback) {
                        errorCallback(error);
                    } else {
                        this.showMessage(`Erreur lors du chargement de ${endpoint}`, 'error');
                    }
                }
            };
            
            // Ajouter les données si POST/PUT
            if (data && (method === 'POST' || method === 'PUT')) {
                ajaxOptions.data = JSON.stringify(data);
                ajaxOptions.contentType = 'application/json';
            }
            
            $.ajax(ajaxOptions);
        },
        
        /**
         * AJAX pour actions dashboard WordPress
         */
        dashboardAjax: function(action, data = {}, callback = null) {
            const $ = jQuery;
            
            const ajaxData = {
                action: `nfc_${action}`,
                vcard_id: this.currentVCardId,
                nonce: this.config.nonce,
                ...data
            };
            
            console.log(`🔄 Dashboard AJAX: nfc_${action}`, ajaxData);
            
            $.post(this.config.ajax_url, ajaxData)
                .done((response) => {
                    console.log(`✅ Réponse Dashboard ${action}:`, response);
                    if (callback) callback(response);
                })
                .fail((xhr, status, error) => {
                    console.error(`❌ Erreur Dashboard ${action}:`, error);
                    this.showMessage(`Erreur lors de l'action ${action}`, 'error');
                });
        },
        
        /**
         * Chargement données vCard
         */
        loadVCardData: function() {
            console.log('📄 Chargement données vCard...');
            
            this.apiCall('vcard', 'GET', null, (response) => {
                if (response && response.success && response.data) {
                    this.updateVCardForm(response.data);
                }
            });
        },
        
        /**
         * Chargement données contacts
         */
        loadContactsData: function() {
            console.log('👥 Chargement données contacts...');
            
            this.apiCall('contacts', 'GET', null, (response) => {
                if (response && Array.isArray(response)) {
                    this.updateContactsList(response);
                }
            });
        },
        
        /**
         * Chargement données statistiques
         */
        loadStatisticsData: function() {
            console.log('📈 Chargement données statistiques...');
            
            this.apiCall('statistics', 'GET', null, (response) => {
                if (response && Array.isArray(response)) {
                    this.updateStatisticsCharts(response);
                }
            });
        },
        
        /**
         * Chargement données QR
         */
        loadQRData: function() {
            console.log('📱 Chargement données QR...');
            // Spécifique à la page QR codes
        },
        
        /**
         * Auto-refresh des données
         */
        startAutoRefresh: function() {
            // Refresh toutes les 5 minutes
            setInterval(() => {
                console.log('🔄 Auto-refresh des stats...');
                this.loadSidebarStats();
                
                // Refresh de la page courante aussi
                if (this.currentPage === 'overview') {
                    this.loadDashboardStats();
                }
            }, 5 * 60 * 1000);
        },
        
        /**
         * Afficher une notification/message
         */
        showMessage: function(message, type = 'info', duration = 5000) {
            console.log(`📢 Message ${type}: ${message}`);
            
            // Essayer d'utiliser les notifications Bootstrap si disponibles
            if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                this.showBootstrapToast(message, type, duration);
            } else {
                // Fallback simple
                this.showSimpleNotification(message, type, duration);
            }
        },
        
        /**
         * Toast Bootstrap
         */
        showBootstrapToast: function(message, type, duration) {
            const $ = jQuery;
            
            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0" 
                     role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                                data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            // Ajouter le toast au container
            let $container = $('.toast-container');
            if ($container.length === 0) {
                $container = $('<div class="toast-container position-fixed top-0 end-0 p-3"></div>');
                $('body').append($container);
            }
            
            const $toast = $(toastHtml);
            $container.append($toast);
            
            const toast = new bootstrap.Toast($toast[0], {
                delay: duration
            });
            
            toast.show();
            
            // Supprimer après fermeture
            $toast.on('hidden.bs.toast', function() {
                $toast.remove();
            });
        },
        
        /**
         * Notification simple en fallback
         */
        showSimpleNotification: function(message, type, duration) {
            // Simple alert en fallback
            const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
            
            if (duration > 0) {
                // Pour les messages temporaires, on peut créer un élément simple
                const notification = document.createElement('div');
                notification.innerHTML = `${icon} ${message}`;
                notification.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: ${type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : '#3b82f6'};
                    color: white;
                    padding: 1rem;
                    border-radius: 8px;
                    z-index: 10000;
                    max-width: 300px;
                `;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, duration);
            } else {
                alert(`${icon} ${message}`);
            }
        },
        
        /**
         * Loading
         */
        showLoading: function() {
            this.isLoading = true;
            const $ = jQuery;
            $('#nfc-dashboard-loading').removeClass('d-none').addClass('d-flex');
            console.log('⏳ Loading affiché');
        },
        
        hideLoading: function() {
            this.isLoading = false;
            const $ = jQuery;
            $('#nfc-dashboard-loading').removeClass('d-flex').addClass('d-none');
            console.log('✅ Loading masqué');
        },
        
        /**
         * Utilitaires
         */
        formatDate: function(dateString, full = false) {
            const date = new Date(dateString);
            const options = full ? {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            } : {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            return date.toLocaleDateString('fr-FR', options);
        },
        
        formatNumber: function(number) {
            return new Intl.NumberFormat('fr-FR').format(number);
        },
        
        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        isValidUrl: function(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        },
        
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        refreshCurrentPage: function() {
            console.log('🔄 Refresh de la page courante');
            this.loadInitialData();
        }
    };
}

/**
 * Initialisation automatique au chargement du DOM
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('📱 DOM Ready - Configuration dashboard:', window.nfcDashboardConfig);
    
    // Initialiser seulement si on est sur le dashboard
    if (window.nfcDashboardConfig && window.nfcDashboardConfig.vcard_id) {
        window.NFCDashboard.init();
    } else {
        console.log('⚠️ Configuration dashboard manquante ou vCard ID manquante');
        console.log('Configuration reçue:', window.nfcDashboardConfig);
    }
});

/**
 * Export global
 */
window.NFCDashboard = window.NFCDashboard;