/**
 * Script de tracking pour vCards publiques
 * 
 * Fichier: assets/js/vcard-trackers.js
 * Enregistre automatiquement les interactions utilisateur via l'API REST
 */

window.NFCTracking = {
    
    // Configuration
    vcard_id: null,
    api_url: '/wp-json/gtmi_vcard/v1/',
    tracked_events: [],
    session_start: null,
    
    /**
     * Initialiser le tracking
     */
    init: function(vcardId) {
        this.vcard_id = vcardId;
        this.session_start = Date.now();
        
        if (!vcardId) {
            console.error('❌ NFC Tracking: vCard ID manquant');
            return;
        }
        
        console.log('📊 NFC Tracking initialisé pour vCard', vcardId);
        
        // Track la vue de page immédiatement
        this.trackEvent('page_view');
        
        // Bind les événements sur les éléments
        this.bindTrackingEvents();
        
        // Track la durée de session
        this.startSessionTracking();
        
        // Check pour QR et NFC depuis les paramètres URL
        this.trackSourceFromURL();
    },
    
    /**
     * Envoyer un événement à l'API REST
     */
    trackEvent: function(eventType, additionalData = {}) {
        if (!this.vcard_id) {
            console.warn('⚠️ Tracking: vCard ID non défini');
            return;
        }
        
        const eventData = {
            virtual_card_id: this.vcard_id,
            event: eventType,
            ...additionalData
        };
        
        console.log('📈 Tracking event:', eventType, eventData);
        
        // Appel à l'API REST gtmi_vcard
        fetch(`${this.api_url}statistics`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(eventData)
        })
        .then(response => {
            if (response.ok) {
                console.log('✅ Event tracked:', eventType);
                this.tracked_events.push(eventType);
            } else {
                console.warn('⚠️ Tracking failed:', response.status);
            }
        })
        .catch(error => {
            console.error('❌ Tracking error:', error);
        });
    },
    
    /**
     * Bind des événements sur les éléments de la vCard
     */
    bindTrackingEvents: function() {
        const self = this;
        
        // Tracking des clics téléphone
        document.querySelectorAll('a[href^="tel:"], button[onclick*="callPhone"], .phone-click, .call-button').forEach(element => {
            element.addEventListener('click', function(e) {
                self.trackEvent('phone_click', {
                    phone_number: this.href ? this.href.replace('tel:', '') : 'unknown'
                });
            });
        });
        
        // Tracking des clics email
        document.querySelectorAll('a[href^="mailto:"], button[onclick*="sendEmail"], .email-click, .email-button').forEach(element => {
            element.addEventListener('click', function(e) {
                self.trackEvent('email_click', {
                    email: this.href ? this.href.replace('mailto:', '') : 'unknown'
                });
            });
        });
        
        // Tracking des liens sociaux
        document.querySelectorAll('a[href*="linkedin.com"], .linkedin-link').forEach(element => {
            element.addEventListener('click', function(e) {
                self.trackEvent('linkedin_click', {
                    url: this.href || 'unknown'
                });
            });
        });
        
        document.querySelectorAll('a[href*="instagram.com"], .instagram-link').forEach(element => {
            element.addEventListener('click', function(e) {
                self.trackEvent('instagram_click', {
                    url: this.href || 'unknown'
                });
            });
        });
        
        document.querySelectorAll('a[href*="twitter.com"], a[href*="x.com"], .twitter-link').forEach(element => {
            element.addEventListener('click', function(e) {
                self.trackEvent('twitter_click', {
                    url: this.href || 'unknown'
                });
            });
        });
        
        document.querySelectorAll('a[href*="facebook.com"], .facebook-link').forEach(element => {
            element.addEventListener('click', function(e) {
                self.trackEvent('facebook_click', {
                    url: this.href || 'unknown'
                });
            });
        });
        
        // Tracking des sites web
        document.querySelectorAll('a[href^="http"]:not([href*="linkedin"]):not([href*="instagram"]):not([href*="twitter"]):not([href*="facebook"]), .website-link').forEach(element => {
            element.addEventListener('click', function(e) {
                self.trackEvent('website_click', {
                    url: this.href || 'unknown'
                });
            });
        });
        
        // Tracking du bouton "Ajouter aux contacts"
        document.querySelectorAll('.add-contact, .contact-btn, .btn-add-contact, [data-action="add-contact"], button[onclick*="downloadVCard"]').forEach(element => {
            element.addEventListener('click', function(e) {
                self.trackEvent('contact_click');
            });
        });
        
        // Tracking des partages
        document.querySelectorAll('.share-btn, .btn-share-contact, [data-action="share"], button[onclick*="share"]').forEach(element => {
            element.addEventListener('click', function(e) {
                self.trackEvent('social_share');
            });
        });
        
        // Tracking des clics sur l'adresse
        document.querySelectorAll('.address-click, [data-action="address"]').forEach(element => {
            element.addEventListener('click', function(e) {
                self.trackEvent('address_click');
            });
        });
        
        console.log('📌 Tracking events bound');
    },
    
    /**
     * Détecter la source depuis l'URL
     */
    trackSourceFromURL: function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Détecter scan QR
        const fromQR = urlParams.get('qr') || urlParams.get('from') === 'qr' || urlParams.get('source') === 'qr';
        if (fromQR) {
            this.trackEvent('qr_scan');
        }
        
        // Détecter tap NFC
        const fromNFC = urlParams.get('nfc') || urlParams.get('from') === 'nfc' || urlParams.get('source') === 'nfc';
        if (fromNFC) {
            this.trackEvent('nfc_tap');
        }
        
        // Détecter autres sources
        const source = urlParams.get('utm_source') || urlParams.get('source');
        if (source && source !== 'qr' && source !== 'nfc') {
            this.trackEvent('referral_visit', {
                source: source
            });
        }
    },
    
    /**
     * Tracking de la durée de session
     */
    startSessionTracking: function() {
        const self = this;
        
        // Track quand l'utilisateur quitte la page
        window.addEventListener('beforeunload', function() {
            const duration = Math.floor((Date.now() - self.session_start) / 1000);
            
            if (duration > 5) { // Minimum 5 secondes
                // Utiliser sendBeacon pour garantir l'envoi
                navigator.sendBeacon(
                    `${self.api_url}statistics`,
                    JSON.stringify({
                        virtual_card_id: self.vcard_id,
                        event: 'session_end',
                        duration: duration
                    })
                );
                
                console.log('📊 Session terminée:', duration + 's');
            }
        });
        
        // Tracking de l'engagement (scroll, clics, etc.)
        let interactions = 0;
        let lastInteraction = Date.now();
        
        ['click', 'scroll', 'keydown', 'touchstart'].forEach(eventType => {
            document.addEventListener(eventType, function() {
                interactions++;
                lastInteraction = Date.now();
                
                // Track l'engagement tous les 10 interactions
                if (interactions % 10 === 0) {
                    self.trackEvent('engagement', {
                        interaction_count: interactions,
                        time_spent: Math.floor((Date.now() - self.session_start) / 1000)
                    });
                }
            });
        });
        
        // Tracking de l'inactivité (plus de 30 secondes sans interaction)
        setInterval(function() {
            if (Date.now() - lastInteraction > 30000) {
                self.trackEvent('session_idle', {
                    idle_duration: Math.floor((Date.now() - lastInteraction) / 1000)
                });
                lastInteraction = Date.now(); // Reset pour éviter le spam
            }
        }, 30000);
    },
    
    /**
     * Tracking manuel pour des événements spécifiques
     */
    trackCustomEvent: function(eventName, data = {}) {
        this.trackEvent(eventName, data);
    },
    
    /**
     * Obtenir les stats de la session courante
     */
    getSessionStats: function() {
        return {
            vcard_id: this.vcard_id,
            session_duration: Math.floor((Date.now() - this.session_start) / 1000),
            tracked_events: this.tracked_events,
            events_count: this.tracked_events.length
        };
    }
};

// Auto-initialisation si vCard ID disponible
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 vcard-trackers.js - DOM Ready');
    
    // Attendre un peu que tous les scripts soient chargés
    setTimeout(function() {
        if (typeof window.NFC_VCARD_ID !== 'undefined' && window.NFC_VCARD_ID) {
            if (typeof window.NFCTracking !== 'undefined') {
                window.NFCTracking.init(window.NFC_VCARD_ID);
                console.log('📊 Tracking auto-initialisé pour vCard:', window.NFC_VCARD_ID);
            } else {
                console.error('❌ window.NFCTracking non défini');
            }
        } else {
            console.log('⚠️ NFC_VCARD_ID non trouvé, tracking non initialisé');
        }
    }, 100); // Petit délai pour s'assurer que tout est chargé
});

// Exposer des fonctions utilitaires globales
window.trackNFCEvent = function(eventName, data) {
    if (window.NFCTracking) {
        window.NFCTracking.trackCustomEvent(eventName, data);
    }
};

window.getNFCSessionStats = function() {
    if (window.NFCTracking) {
        return window.NFCTracking.getSessionStats();
    }
    return null;
};