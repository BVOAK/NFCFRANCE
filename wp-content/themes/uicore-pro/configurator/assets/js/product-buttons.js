/**
 * JavaScript - Interactions pour les boutons produits NFC
 * Compatible avec UICore/Elementor et approche moderne ES6+
 * 
 * @package NFC_Configurator
 * @version 1.0.0
 * 
 * Fichier : configurator/assets/js/product-buttons.js
 */

(function() {
    'use strict';
    
    /**
     * Classe principale pour gérer les interactions des boutons NFC
     */
    class NFCProductButtons {
        
        constructor() {
            this.config = window.nfcButtons || {};
            this.debug = window.nfcConfig?.debug || false;
            this.timeouts = new Map(); // Gestion des timeouts
            
            this.log('🚀 NFCProductButtons initialisé');
            this.init();
        }
        
        /**
         * Initialisation des event listeners et configuration
         */
        init() {
            // Attendre que le DOM soit prêt
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setupEventListeners());
            } else {
                this.setupEventListeners();
            }
            
            // Detection environnement
            this.detectEnvironment();
            
            // Setup des notifications
            this.setupNotifications();
        }
        
        /**
         * Configuration des event listeners
         */
        setupEventListeners() {
            // Event delegation pour tous les boutons NFC
            document.addEventListener('click', (e) => this.handleButtonClick(e));
            
            // Prévenir la soumission de formulaire accidentelle
            document.addEventListener('submit', (e) => this.handleFormSubmit(e));
            
            // Gestion des raccourcis clavier (accessibilité)
            document.addEventListener('keydown', (e) => this.handleKeydown(e));
            
            this.log('✅ Event listeners configurés');
        }
        
        /**
         * Gestionnaire principal des clics sur boutons
         */
        handleButtonClick(e) {
            const button = e.target.closest('[data-nfc-button]');
            if (!button) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            const action = button.dataset.action;
            const productId = button.dataset.productId;
            
            this.log(`🔘 Bouton cliqué: ${action} pour produit ${productId}`, button);
            
            // Router vers la bonne action
            switch (action) {
                case 'configurator':
                    this.handleConfiguratorButton(button);
                    break;
                    
                case 'add-with-files':
                    this.handleAddWithFilesButton(button, productId);
                    break;
                    
                default:
                    this.log(`⚠️ Action inconnue: ${action}`);
            }
        }
        
        /**
         * Gestion du bouton configurateur (redirection)
         */
        handleConfiguratorButton(button) {
            const href = button.href;
            if (!href) {
                this.showNotification('Erreur : URL du configurateur manquante', 'error');
                return;
            }
            
            // Animation de sortie
            this.addButtonState(button, 'loading');
            
            // Redirection avec délai pour l'animation
            setTimeout(() => {
                window.location.href = href;
            }, 200);
            
            this.log(`🔗 Redirection vers configurateur: ${href}`);
        }
        
        /**
         * Gestion du bouton "Ajouter avec fichiers"
         */
        async handleAddWithFilesButton(button, productId) {
            if (!productId) {
                this.showNotification('Erreur : ID produit manquant', 'error');
                return;
            }
            
            // Vérifier si déjà en cours
            if (button.classList.contains('nfc-btn-loading')) {
                this.log('⏳ Ajout déjà en cours, ignoré');
                return;
            }
            
            try {
                // État de loading
                this.addButtonState(button, 'loading');
                this.log(`🛒 Ajout au panier avec fichiers: produit ${productId}`);
                
                // Appel AJAX
                const result = await this.addToCartWithFiles(productId);
                
                if (result.success) {
                    // Succès
                    this.addButtonState(button, 'success');
                    this.showNotification(result.message || this.config.i18n?.added || 'Ajouté au panier avec succès', 'success');
                    
                    // Redirection vers panier après 1.5s
                    setTimeout(() => {
                        this.redirectToCart(result.cart_url);
                    }, 1500);
                    
                } else {
                    // Erreur métier
                    this.removeButtonState(button);
                    this.showNotification(result.message || this.config.i18n?.error || 'Erreur lors de l\'ajout', 'error');
                }
                
            } catch (error) {
                // Erreur technique
                this.removeButtonState(button);
                this.showNotification(this.config.i18n?.error || 'Erreur de connexion', 'error');
                this.log('❌ Erreur ajout au panier:', error);
            }
        }
        
        /**
         * Appel AJAX pour ajouter au panier avec métadonnées fichiers
         */
        async addToCartWithFiles(productId) {
            const ajaxUrl = this.config.ajaxUrl || '/wp-admin/admin-ajax.php';
            const nonce = this.config.nonce;
            
            const formData = new FormData();
            formData.append('action', 'nfc_add_to_cart_with_files');
            formData.append('product_id', productId);
            formData.append('quantity', '1');
            formData.append('requires_files', 'true');
            
            if (nonce) {
                formData.append('nonce', nonce);
            }
            
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            this.log('📨 Réponse AJAX:', data);
            
            return data;
        }
        
        /**
         * Gestion des états visuels des boutons
         */
        addButtonState(button, state) {
            // Nettoyer les états précédents
            this.removeButtonState(button);
            
            switch (state) {
                case 'loading':
                    button.classList.add('nfc-btn-loading');
                    button.disabled = true;
                    
                    // Afficher spinner
                    const loadingSpan = button.querySelector('.nfc-btn-loading');
                    if (loadingSpan) {
                        loadingSpan.style.display = 'flex';
                    }
                    
                    // Masquer texte principal
                    const textSpan = button.querySelector('.nfc-btn-text');
                    if (textSpan) {
                        textSpan.style.opacity = '0';
                    }
                    break;
                    
                case 'success':
                    button.classList.add('nfc-btn-success');
                    
                    // Changer l'icône temporairement
                    const icon = button.querySelector('i');
                    if (icon) {
                        const originalClass = icon.className;
                        icon.className = 'fas fa-check';
                        
                        // Restaurer après animation
                        setTimeout(() => {
                            icon.className = originalClass;
                        }, 2000);
                    }
                    break;
            }
        }
        
        /**
         * Suppression des états visuels
         */
        removeButtonState(button) {
            button.classList.remove('nfc-btn-loading', 'nfc-btn-success');
            button.disabled = false;
            
            // Masquer spinner
            const loadingSpan = button.querySelector('.nfc-btn-loading');
            if (loadingSpan) {
                loadingSpan.style.display = 'none';
            }
            
            // Réafficher texte
            const textSpan = button.querySelector('.nfc-btn-text');
            if (textSpan) {
                textSpan.style.opacity = '1';
            }
        }
        
        /**
         * Redirection vers le panier
         */
        redirectToCart(cartUrl = null) {
            const url = cartUrl || this.config.cartUrl || '/panier/';
            
            this.log(`🛒 Redirection vers panier: ${url}`);
            
            // Animation de sortie
            const buttons = document.querySelectorAll('.nfc-product-buttons');
            buttons.forEach(container => {
                container.style.opacity = '0.7';
                container.style.transform = 'scale(0.95)';
            });
            
            setTimeout(() => {
                window.location.href = url;
            }, 300);
        }
        
        /**
         * Système de notifications toast
         */
        setupNotifications() {
            // Créer le container de notifications s'il n'existe pas
            if (!document.querySelector('.nfc-notifications')) {
                const container = document.createElement('div');
                container.className = 'nfc-notifications';
                container.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 99999;
                    pointer-events: none;
                `;
                document.body.appendChild(container);
            }
        }
        
        /**
         * Affichage d'une notification toast
         */
        showNotification(message, type = 'info', duration = 4000) {
            const container = document.querySelector('.nfc-notifications');
            if (!container) return;
            
            const notification = document.createElement('div');
            notification.className = `nfc-notification nfc-notification-${type}`;
            
            // Styles inline pour éviter les dépendances CSS
            const baseStyles = `
                background: white;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                padding: 16px 20px;
                margin-bottom: 12px;
                max-width: 320px;
                pointer-events: auto;
                transform: translateX(100%);
                transition: all 0.3s ease;
                border-left: 4px solid;
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 14px;
                line-height: 1.4;
            `;
            
            // Couleurs selon le type
            const typeStyles = {
                'success': 'border-left-color: #22c55e; color: #065f46;',
                'error': 'border-left-color: #ef4444; color: #991b1b;',
                'warning': 'border-left-color: #f59e0b; color: #92400e;',
                'info': 'border-left-color: #3b82f6; color: #1e40af;'
            };
            
            // Icônes selon le type
            const typeIcons = {
                'success': 'fas fa-check-circle',
                'error': 'fas fa-exclamation-circle',
                'warning': 'fas fa-exclamation-triangle',
                'info': 'fas fa-info-circle'
            };
            
            notification.style.cssText = baseStyles + (typeStyles[type] || typeStyles.info);
            notification.innerHTML = `
                <i class="${typeIcons[type] || typeIcons.info}"></i>
                <span>${this.escapeHtml(message)}</span>
                <button onclick="this.parentElement.remove()" style="
                    background: none;
                    border: none;
                    font-size: 16px;
                    cursor: pointer;
                    opacity: 0.7;
                    margin-left: auto;
                ">&times;</button>
            `;
            
            container.appendChild(notification);
            
            // Animation d'entrée
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            // Auto-dismiss
            const timeoutId = setTimeout(() => {
                this.hideNotification(notification);
            }, duration);
            
            // Stocker le timeout pour pouvoir l'annuler
            this.timeouts.set(notification, timeoutId);
            
            this.log(`📢 Notification ${type}: ${message}`);
        }
        
        /**
         * Masquage d'une notification
         */
        hideNotification(notification) {
            if (!notification.parentElement) return;
            
            // Annuler le timeout
            const timeoutId = this.timeouts.get(notification);
            if (timeoutId) {
                clearTimeout(timeoutId);
                this.timeouts.delete(notification);
            }
            
            // Animation de sortie
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';
            
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
        
        /**
         * Gestion des soumissions de formulaire
         */
        handleFormSubmit(e) {
            // Empêcher la soumission si bouton NFC en cours de traitement
            const form = e.target;
            const nfcButtons = form.querySelectorAll('.nfc-btn-loading');
            
            if (nfcButtons.length > 0) {
                e.preventDefault();
                this.log('⚠️ Soumission formulaire bloquée (bouton NFC en cours)');
            }
        }
        
        /**
         * Gestion des raccourcis clavier (accessibilité)
         */
        handleKeydown(e) {
            // Entrée ou espace sur bouton focalisé
            if ((e.key === 'Enter' || e.key === ' ') && e.target.matches('[data-nfc-button]')) {
                e.preventDefault();
                e.target.click();
            }
        }
        
        /**
         * Détection de l'environnement (Elementor, etc.)
         */
        detectEnvironment() {
            const environment = {
                isElementor: window.elementorFrontend !== undefined,
                isElementorEditor: window.elementor !== undefined,
                isTouch: 'ontouchstart' in window,
                userAgent: navigator.userAgent
            };
            
            this.environment = environment;
            this.log('🌍 Environnement détecté:', environment);
            
            // Adaptations spécifiques
            if (environment.isTouch) {
                document.documentElement.classList.add('nfc-touch-device');
            }
        }
        
        /**
         * Utilities
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        log(message, ...args) {
            if (this.debug) {
                console.log(`[NFCButtons] ${message}`, ...args);
            }
        }
        
        /**
         * API publique pour intégrations externes
         */
        static getInstance() {
            return window.nfcButtonsInstance;
        }
        
        /**
         * Méthode pour déclencher manuellement un ajout au panier
         */
        async triggerAddToCart(productId) {
            const button = document.querySelector(`[data-product-id="${productId}"][data-action="add-with-files"]`);
            if (button) {
                return this.handleAddWithFilesButton(button, productId);
            }
            throw new Error(`Bouton pour produit ${productId} introuvable`);
        }
    }
    
    /**
     * Auto-initialisation et gestion des erreurs globales
     */
    function initNFCButtons() {
        try {
            // Instancier et stocker globalement
            window.nfcButtonsInstance = new NFCProductButtons();
            
            // Event personnalisé pour les intégrations
            document.dispatchEvent(new CustomEvent('nfc-buttons-ready', {
                detail: window.nfcButtonsInstance
            }));
            
        } catch (error) {
            console.error('[NFCButtons] Erreur d\'initialisation:', error);
            
            // Fallback basique en cas d'erreur
            document.addEventListener('click', function(e) {
                const button = e.target.closest('[data-action="add-with-files"]');
                if (button) {
                    alert('Service temporairement indisponible. Veuillez réessayer.');
                }
            });
        }
    }
    
    /**
     * Point d'entrée principal
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNFCButtons);
    } else {
        initNFCButtons();
    }
    
    /**
     * Intégration Elementor (si présent)
     */
    if (window.elementorFrontend) {
        window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function() {
            // Réinitialiser pour les nouveaux éléments Elementor
            if (window.nfcButtonsInstance) {
                window.nfcButtonsInstance.log('🔄 Réinitialisation post-Elementor');
            }
        });
    }
    
})();