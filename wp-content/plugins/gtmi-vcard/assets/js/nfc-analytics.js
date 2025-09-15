/**
 * NFC Analytics Tracker
 * Fichier: assets/js/nfc-analytics.js
 * 
 * Système de tracking complet pour les vCards NFC
 * Remplace toutes les statistiques fictives par des données réelles
 */

class NFCAnalytics {
    constructor(vcard_id, config = {}) {
        this.vcard_id = vcard_id;
        this.config = {
            ajax_url: config.ajax_url || '/wp-admin/admin-ajax.php',
            nonce: config.nonce || '',
            debug: config.debug || false,
            auto_track: config.auto_track !== false, // Tracking automatique par défaut
            ...config
        };
        
        // État de la session
        this.session_id = this.generateSessionId();
        this.analytics_id = null;
        this.start_time = Date.now();
        this.last_activity = Date.now();
        this.actions = [];
        this.max_scroll_depth = 0;
        this.is_active = true;
        
        // Détection automatique
        this.traffic_source = null;
        this.utm_data = {};
        this.device_info = {};
        
        this.log('🚀 NFCAnalytics initialisé', { vcard_id, config });
        
        if (this.config.auto_track) {
            this.init();
        }
    }
    
    /**
     * Initialisation du tracking
     */
    init() {
        try {
            this.detectTrafficSource();
            this.gatherDeviceInfo();
            this.trackPageView();
            this.bindEvents();
            this.startSessionMonitoring();
            
            this.log('✅ Tracking initialisé avec succès');
        } catch (error) {
            this.log('❌ Erreur initialisation tracking:', error);
        }
    }
    
    /**
     * Génération d'un ID de session unique
     */
    generateSessionId() {
        return 'nfc_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    /**
     * Détection automatique de la source de trafic
     */
    detectTrafficSource() {
        const urlParams = new URLSearchParams(window.location.search);
        const referrer = document.referrer;
        
        this.log('🔍 Détection source de trafic', { urlParams: Object.fromEntries(urlParams), referrer });
        
        // 1. Source NFC explicite
        if (urlParams.get('source') === 'nfc' || urlParams.get('nfc')) {
            this.traffic_source = 'nfc_scan';
            this.source_detail = urlParams.get('device_id') || urlParams.get('nfc') || 'unknown';
            return;
        }
        
        // 2. Source QR Code explicite
        if (urlParams.get('source') === 'qr' || urlParams.get('qr')) {
            this.traffic_source = 'qr_code';
            this.source_detail = urlParams.get('qr_id') || urlParams.get('qr') || 'unknown';
            return;
        }
        
        // 3. Campagne UTM
        if (urlParams.get('utm_source')) {
            this.traffic_source = 'campaign';
            this.utm_data = {
                source: urlParams.get('utm_source'),
                medium: urlParams.get('utm_medium'),
                campaign: urlParams.get('utm_campaign'),
                term: urlParams.get('utm_term'),
                content: urlParams.get('utm_content')
            };
            return;
        }
        
        // 4. Analyse du referrer
        if (referrer && referrer !== window.location.href) {
            const ref_domain = new URL(referrer).hostname.toLowerCase();
            
            // Réseaux sociaux
            if (ref_domain.includes('facebook') || ref_domain.includes('fb.') ||
                ref_domain.includes('instagram') || ref_domain.includes('linkedin') ||
                ref_domain.includes('twitter') || ref_domain.includes('t.co') ||
                ref_domain.includes('youtube') || ref_domain.includes('tiktok')) {
                this.traffic_source = 'social';
                this.source_detail = ref_domain;
                return;
            }
            
            // Moteurs de recherche
            if (ref_domain.includes('google') || ref_domain.includes('bing') ||
                ref_domain.includes('yahoo') || ref_domain.includes('duckduckgo') ||
                ref_domain.includes('yandex') || ref_domain.includes('baidu')) {
                this.traffic_source = 'search';
                this.source_detail = ref_domain;
                return;
            }
            
            // Email
            if (ref_domain.includes('mail') || ref_domain.includes('outlook') ||
                ref_domain.includes('gmail') || referrer.includes('email')) {
                this.traffic_source = 'email';
                this.source_detail = ref_domain;
                return;
            }
            
            // Référence externe
            this.traffic_source = 'referral';
            this.source_detail = ref_domain;
            return;
        }
        
        // 5. Accès direct par défaut
        this.traffic_source = 'direct';
        this.source_detail = 'direct_access';
    }
    
    /**
     * Collecte des informations sur l'appareil
     */
    gatherDeviceInfo() {
        const ua = navigator.userAgent;
        
        this.device_info = {
            user_agent: ua,
            screen_resolution: `${screen.width}x${screen.height}`,
            viewport_size: `${window.innerWidth}x${window.innerHeight}`,
            device_type: this.detectDeviceType(ua),
            browser: this.detectBrowser(ua),
            os: this.detectOS(ua),
            language: navigator.language || navigator.userLanguage,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            connection: this.getConnectionInfo(),
            touch_support: 'ontouchstart' in window
        };
        
        this.log('📱 Informations appareil collectées', this.device_info);
    }
    
    /**
     * Détection du type d'appareil
     */
    detectDeviceType(ua) {
        ua = ua.toLowerCase();
        
        if (ua.includes('tablet') || 
            (ua.includes('android') && !ua.includes('mobile')) ||
            ua.includes('ipad')) {
            return 'tablet';
        }
        
        if (ua.includes('mobile') || ua.includes('iphone') || 
            ua.includes('android') || ua.includes('webos') ||
            ua.includes('blackberry') || ua.includes('windows phone')) {
            return 'mobile';
        }
        
        return 'desktop';
    }
    
    /**
     * Détection du navigateur
     */
    detectBrowser(ua) {
        ua = ua.toLowerCase();
        
        if (ua.includes('firefox')) return 'Firefox';
        if (ua.includes('chrome') && !ua.includes('chromium')) return 'Chrome';
        if (ua.includes('safari') && !ua.includes('chrome')) return 'Safari';
        if (ua.includes('edge')) return 'Edge';
        if (ua.includes('opera')) return 'Opera';
        if (ua.includes('msie') || ua.includes('trident')) return 'Internet Explorer';
        
        return 'Unknown';
    }
    
    /**
     * Détection du système d'exploitation
     */
    detectOS(ua) {
        ua = ua.toLowerCase();
        
        if (ua.includes('windows nt 10')) return 'Windows 10';
        if (ua.includes('windows nt 6.3')) return 'Windows 8.1';
        if (ua.includes('windows nt 6.2')) return 'Windows 8';
        if (ua.includes('windows nt 6.1')) return 'Windows 7';
        if (ua.includes('windows')) return 'Windows';
        if (ua.includes('mac os x')) return 'macOS';
        if (ua.includes('android')) return 'Android';
        if (ua.includes('iphone') || ua.includes('ipad')) return 'iOS';
        if (ua.includes('linux')) return 'Linux';
        
        return 'Unknown';
    }
    
    /**
     * Informations sur la connexion
     */
    getConnectionInfo() {
        if ('connection' in navigator) {
            const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            return {
                effective_type: conn.effectiveType,
                downlink: conn.downlink,
                rtt: conn.rtt
            };
        }
        return null;
    }
    
    /**
     * Enregistrement de la vue de page
     */
    async trackPageView() {
        const view_data = {
            action: 'nfc_track_view',
            vcard_id: this.vcard_id,
            session_id: this.session_id,
            traffic_source: this.traffic_source,
            source_detail: this.source_detail || '',
            utm_data: this.utm_data,
            device_info: this.device_info,
            page_url: window.location.href,
            page_title: document.title,
            nonce: this.config.nonce
        };
        
        this.log('👁️ Tracking vue de page', view_data);
        
        try {
            const response = await this.sendAnalytics(view_data);
            if (response && response.analytics_id) {
                this.analytics_id = response.analytics_id;
                this.log('✅ Vue de page enregistrée', { analytics_id: this.analytics_id });
            }
        } catch (error) {
            this.log('❌ Erreur tracking vue:', error);
        }
    }
    
    /**
     * Liaison des événements de tracking automatique
     */
    bindEvents() {
        this.log('🔗 Liaison des événements de tracking');
        
        // Clics sur liens téléphone
        this.bindPhoneClicks();
        
        // Clics sur liens email
        this.bindEmailClicks();
        
        // Clics sur réseaux sociaux
        this.bindSocialClicks();
        
        // Clics sur site web
        this.bindWebsiteClicks();
        
        // Tracking du scroll
        this.bindScrollTracking();
        
        // Tracking de sortie de page
        this.bindPageExit();
        
        // Tracking d'activité (heartbeat)
        this.bindActivityTracking();
    }
    
    /**
     * Tracking des clics téléphone
     */
    bindPhoneClicks() {
        document.querySelectorAll('a[href^="tel:"]').forEach(link => {
            link.addEventListener('click', (e) => {
                const phone = link.href.replace('tel:', '');
                this.trackAction('phone_click', phone, {
                    element: this.getElementInfo(link),
                    link_text: link.textContent.trim()
                });
            });
        });
    }
    
    /**
     * Tracking des clics email
     */
    bindEmailClicks() {
        document.querySelectorAll('a[href^="mailto:"]').forEach(link => {
            link.addEventListener('click', (e) => {
                const email = link.href.replace('mailto:', '');
                this.trackAction('email_click', email, {
                    element: this.getElementInfo(link),
                    link_text: link.textContent.trim()
                });
            });
        });
    }
    
    /**
     * Tracking des clics réseaux sociaux
     */
    bindSocialClicks() {
        const social_selectors = [
            'a[href*="facebook.com"]',
            'a[href*="instagram.com"]',
            'a[href*="linkedin.com"]',
            'a[href*="twitter.com"]',
            'a[href*="youtube.com"]',
            'a[href*="tiktok.com"]'
        ];
        
        document.querySelectorAll(social_selectors.join(', ')).forEach(link => {
            link.addEventListener('click', (e) => {
                const platform = this.extractSocialPlatform(link.href);
                this.trackAction('social_click', link.href, {
                    platform: platform,
                    element: this.getElementInfo(link),
                    link_text: link.textContent.trim()
                });
            });
        });
    }
    
    /**
     * Tracking des clics vers site web
     */
    bindWebsiteClicks() {
        document.querySelectorAll('a[href^="http"]:not([href*="tel:"]):not([href*="mailto:"])').forEach(link => {
            if (!this.isSocialLink(link.href)) {
                link.addEventListener('click', (e) => {
                    this.trackAction('website_click', link.href, {
                        element: this.getElementInfo(link),
                        link_text: link.textContent.trim(),
                        is_external: !link.href.includes(window.location.hostname)
                    });
                });
            }
        });
    }
    
    /**
     * Tracking du scroll
     */
    bindScrollTracking() {
        let scroll_timer = null;
        
        window.addEventListener('scroll', () => {
            const scroll_percent = Math.round(
                (window.pageYOffset / (document.documentElement.scrollHeight - window.innerHeight)) * 100
            );
            
            if (scroll_percent > this.max_scroll_depth) {
                this.max_scroll_depth = Math.min(scroll_percent, 100);
                
                // Envoyer les jalons de scroll (25%, 50%, 75%, 100%)
                if ([25, 50, 75, 100].includes(this.max_scroll_depth)) {
                    clearTimeout(scroll_timer);
                    scroll_timer = setTimeout(() => {
                        this.trackAction('scroll_milestone', this.max_scroll_depth + '%', {
                            scroll_depth: this.max_scroll_depth
                        });
                    }, 1000);
                }
            }
        });
    }
    
    /**
     * Tracking de sortie de page
     */
    bindPageExit() {
        window.addEventListener('beforeunload', () => {
            this.trackSessionEnd();
        });
        
        // Tracking de la perte de focus (changement d'onglet)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.trackAction('page_hidden', '', {
                    time_on_page: Date.now() - this.start_time,
                    scroll_depth: this.max_scroll_depth
                });
            } else {
                this.trackAction('page_visible', '', {});
            }
        });
    }
    
    /**
     * Tracking d'activité (heartbeat)
     */
    bindActivityTracking() {
        let activity_timer = setInterval(() => {
            if (this.is_active && Date.now() - this.last_activity < 30000) { // 30 secondes d'inactivité max
                this.updateSessionActivity();
            }
        }, 15000); // Toutes les 15 secondes
        
        // Détecter l'activité
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, () => {
                this.last_activity = Date.now();
            }, { passive: true });
        });
    }
    
    /**
     * Tracking d'une action spécifique
     */
    async trackAction(action_type, action_value = '', additional_data = {}) {
        if (!this.analytics_id) {
            this.log('⚠️ Analytics ID manquant pour action:', action_type);
            return;
        }
        
        const action_data = {
            action: 'nfc_track_action',
            analytics_id: this.analytics_id,
            vcard_id: this.vcard_id,
            session_id: this.session_id,
            action_type: action_type,
            action_value: action_value,
            time_since_page_load: Date.now() - this.start_time,
            scroll_depth: this.max_scroll_depth,
            additional_data: additional_data,
            nonce: this.config.nonce
        };
        
        this.log(`🎯 Action trackée: ${action_type}`, action_data);
        
        this.actions.push({
            type: action_type,
            value: action_value,
            timestamp: Date.now(),
            data: additional_data
        });
        
        try {
            await this.sendAnalytics(action_data);
        } catch (error) {
            this.log('❌ Erreur tracking action:', error);
        }
    }
    
    /**
     * Mise à jour de l'activité de session
     */
    async updateSessionActivity() {
        if (!this.analytics_id) return;
        
        const activity_data = {
            action: 'nfc_update_session',
            analytics_id: this.analytics_id,
            session_duration: Date.now() - this.start_time,
            scroll_depth: this.max_scroll_depth,
            actions_count: this.actions.length,
            nonce: this.config.nonce
        };
        
        try {
            await this.sendAnalytics(activity_data);
        } catch (error) {
            this.log('❌ Erreur mise à jour session:', error);
        }
    }
    
    /**
     * Tracking de fin de session
     */
    trackSessionEnd() {
        if (!this.analytics_id) return;
        
        const session_data = {
            action: 'nfc_end_session',
            analytics_id: this.analytics_id,
            session_duration: Date.now() - this.start_time,
            total_actions: this.actions.length,
            max_scroll_depth: this.max_scroll_depth,
            is_bounce: this.actions.length === 0,
            nonce: this.config.nonce
        };
        
        // Utiliser sendBeacon pour l'envoi asynchrone lors de la fermeture
        if (navigator.sendBeacon) {
            const form_data = new FormData();
            Object.keys(session_data).forEach(key => {
                form_data.append(key, typeof session_data[key] === 'object' 
                    ? JSON.stringify(session_data[key]) 
                    : session_data[key]);
            });
            
            navigator.sendBeacon(this.config.ajax_url, form_data);
            this.log('📡 Fin de session envoyée via sendBeacon');
        }
    }
    
    /**
     * Envoi des données analytics
     */
    async sendAnalytics(data) {
        try {
            const response = await fetch(this.config.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.data?.message || 'Erreur inconnue');
            }
        } catch (error) {
            this.log('❌ Erreur envoi analytics:', error);
            throw error;
        }
    }
    
    /**
     * Utilitaires
     */
    getElementInfo(element) {
        return {
            id: element.id || '',
            class: element.className || '',
            tag: element.tagName.toLowerCase(),
            text: element.textContent?.trim().substring(0, 100) || ''
        };
    }
    
    extractSocialPlatform(url) {
        if (url.includes('facebook')) return 'facebook';
        if (url.includes('instagram')) return 'instagram';
        if (url.includes('linkedin')) return 'linkedin';
        if (url.includes('twitter')) return 'twitter';
        if (url.includes('youtube')) return 'youtube';
        if (url.includes('tiktok')) return 'tiktok';
        return 'other';
    }
    
    isSocialLink(url) {
        return ['facebook', 'instagram', 'linkedin', 'twitter', 'youtube', 'tiktok']
            .some(platform => url.includes(platform));
    }
    
    log(message, data = null) {
        if (this.config.debug) {
            console.log(`[NFCAnalytics] ${message}`, data || '');
        }
    }
    
    /**
     * API publique pour tracking manuel
     */
    track(action_type, action_value = '', additional_data = {}) {
        return this.trackAction(action_type, action_value, additional_data);
    }
    
    /**
     * Obtenir les statistiques de la session actuelle
     */
    getSessionStats() {
        return {
            session_id: this.session_id,
            analytics_id: this.analytics_id,
            start_time: this.start_time,
            duration: Date.now() - this.start_time,
            actions_count: this.actions.length,
            max_scroll_depth: this.max_scroll_depth,
            traffic_source: this.traffic_source,
            device_type: this.device_info.device_type
        };
    }
}

// Export pour utilisation globale
window.NFCAnalytics = NFCAnalytics;

// Auto-initialisation si configuration présente
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.nfcAnalyticsConfig !== 'undefined') {
        window.nfcAnalytics = new NFCAnalytics(
            window.nfcAnalyticsConfig.vcard_id,
            window.nfcAnalyticsConfig
        );
    }
});