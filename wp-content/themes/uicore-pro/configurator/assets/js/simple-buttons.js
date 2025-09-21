/**
 * NOUVEAU FICHIER : /configurator/assets/js/simple-buttons.js
 * JavaScript simple et robuste pour les boutons NFC
 * Remplace product-buttons.js
 */

class NFCSimpleButtons {
    constructor() {
        this.debug = window.nfcConfig?.debug || false;
        this.config = window.nfcConfig || {};
        this.products = new Map(); // Cache des données produits

        this.preventFormPostRedirect();

        this.init();
    }

    init() {
        this.log('🚀 NFCSimpleButtons initialisé');

        // Attendre que DOM soit prêt
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.bindEvents());
        } else {
            this.bindEvents();
        }
    }

    preventFormPostRedirect() {
        this.log('🛡️ Initialisation du nettoyage global POST-redirect');
        
        // 1. NETTOYER L'HISTORIQUE AU CHARGEMENT
        this.cleanHistoryOnLoad();
        
        // 2. INTERCEPTER LES SOUMISSIONS DE FORMULAIRES
        this.interceptFormSubmissions();
        
        // 3. GÉRER LES ÉVÉNEMENTS DE NAVIGATION
        this.handleNavigationEvents();
        
        // 4. NETTOYER AVANT DÉCHARGEMENT DE PAGE
        this.cleanBeforeUnload();
    }

    /**
     * Nettoie l'historique POST au chargement de la page
     */
    cleanHistoryOnLoad() {
        // Attendre que la page soit complètement chargée
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.performHistoryClean());
        } else {
            this.performHistoryClean();
        }
    }

    /**
     * Effectue le nettoyage de l'historique
     */
    performHistoryClean() {
        try {
            // Vérifier si on peut modifier l'historique
            if (!window.history || !window.history.replaceState) {
                this.log('⚠️ History API non supportée');
                return;
            }
            
            // Détecter si on vient d'une requête POST (indicateurs)
            const hasPostIndicators = 
                document.referrer.includes(window.location.hostname) ||
                window.performance?.navigation?.type === 1 || // TYPE_RELOAD
                window.location.search.includes('added-to-cart') ||
                sessionStorage.getItem('nfc_recent_post_action');
            
            if (hasPostIndicators) {
                // Construire une URL propre sans paramètres POST
                const cleanUrl = this.buildCleanUrl();
                
                // Remplacer l'entrée d'historique actuelle
                window.history.replaceState(
                    { nfc_cleaned: true, timestamp: Date.now() },
                    document.title,
                    cleanUrl
                );
                
                this.log('🧹 Historique POST nettoyé - URL propre:', cleanUrl);
                
                // Marquer que le nettoyage a été fait
                sessionStorage.setItem('nfc_history_cleaned', Date.now().toString());
            }
            
        } catch (error) {
            this.log('❌ Erreur nettoyage historique:', error);
        }
    }

    /**
     * Construit une URL propre sans paramètres de POST
     */
    buildCleanUrl() {
        const url = new URL(window.location);
        
        // Supprimer les paramètres typiques de POST WooCommerce
        const postParams = [
            'add-to-cart',
            'added-to-cart',
            'quantity',
            'variation_id',
            'wc-ajax',
            'nonce'
        ];
        
        postParams.forEach(param => {
            url.searchParams.delete(param);
        });
        
        // Supprimer aussi les attributs de variation
        for (const [key] of url.searchParams) {
            if (key.startsWith('attribute_')) {
                url.searchParams.delete(key);
            }
        }
        
        return url.pathname + (url.search || '');
    }

    /**
     * Intercepte les soumissions de formulaires pour marquer les actions POST
     */
    interceptFormSubmissions() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            
            // Détecter les formulaires WooCommerce
            if (this.isWooCommerceForm(form)) {
                this.log('📝 Soumission formulaire WooCommerce détectée');
                
                // Marquer qu'une action POST va avoir lieu
                sessionStorage.setItem('nfc_recent_post_action', Date.now().toString());
                
                // Si c'est un formulaire avec nos boutons NFC, on pourrait le bloquer
                // pour forcer l'utilisation de l'Ajax
                if (form.querySelector('.nfc-simple-buttons')) {
                    this.log('🛡️ Formulaire avec boutons NFC - Ajax préféré');
                    // Optionnel : bloquer la soumission classique
                    // e.preventDefault();
                    // this.handleNFCFormSubmission(form);
                }
            }
        });
    }

    
    /**
     * Vérifie si un formulaire est un formulaire WooCommerce
     */
    isWooCommerceForm(form) {
        return form.classList.contains('cart') ||
            form.classList.contains('variations_form') ||
            form.querySelector('input[name="add-to-cart"]') ||
            form.action.includes('wc-ajax') ||
            form.method.toLowerCase() === 'post' && form.querySelector('.single_add_to_cart_button');
    }

    /**
     * Gère les événements de navigation du navigateur
     */
    handleNavigationEvents() {
        // Écouter les changements d'historique (back/forward)
        window.addEventListener('popstate', (e) => {
            this.log('🔄 Navigation historique détectée');
            
            // Si l'état contient des données de nettoyage, tout va bien
            if (e.state && e.state.nfc_cleaned) {
                this.log('✅ Page déjà nettoyée');
            } else {
                // Nettoyer si nécessaire
                setTimeout(() => this.performHistoryClean(), 100);
            }
        });
        
        // Écouter les changements de page (pour SPAs)
        let lastUrl = window.location.href;
        new MutationObserver(() => {
            const currentUrl = window.location.href;
            if (currentUrl !== lastUrl) {
                lastUrl = currentUrl;
                this.log('🔄 Changement de page détecté');
                setTimeout(() => this.performHistoryClean(), 100);
            }
        }).observe(document, { subtree: true, childList: true });
    }

    /**
     * Nettoie avant le déchargement de la page
     */
    cleanBeforeUnload() {
        window.addEventListener('beforeunload', () => {
            // Nettoyer le sessionStorage des marqueurs temporaires
            const cleanupKeys = [
                'nfc_recent_post_action',
                'nfc_history_cleaned'
            ];
            
            cleanupKeys.forEach(key => {
                const timestamp = sessionStorage.getItem(key);
                if (timestamp) {
                    const age = Date.now() - parseInt(timestamp);
                    // Supprimer si plus vieux que 5 minutes
                    if (age > 5 * 60 * 1000) {
                        sessionStorage.removeItem(key);
                    }
                }
            });
        });
    }


    bindEvents() {
        // Boutons configurateur
        document.addEventListener('click', (e) => {
            const configuratorBtn = e.target.closest('.nfc-configurator-btn');
            if (configuratorBtn) {
                this.handleConfiguratorClick(e, configuratorBtn);
            }
        });

        // Boutons ajout panier
        document.addEventListener('click', (e) => {
            const cartBtn = e.target.closest('.nfc-addcart-btn');
            if (cartBtn) {
                this.handleCartClick(e, cartBtn);
            }
        });

        // Écouter les changements de variations WooCommerce
        this.bindWooCommerceEvents();
    }

    /**
     * Intégration avec WooCommerce variations
     */
    bindWooCommerceEvents() {
        // Événement quand une variation est trouvée (seulement pour produits variables)
        jQuery(document).on('found_variation', (event, variation) => {
            this.log('✅ Variation trouvée:', variation);
            this.updateButtonsState(variation);
        });

        // Événement quand variation est réinitialisée (seulement pour produits variables)
        jQuery(document).on('reset_data', (event) => {
            this.log('🔄 Variation réinitialisée');
            this.resetButtonsState();
        });

        // Changement de quantité (pour tous types de produits)
        jQuery(document).on('change', 'input.qty', (event) => {
            const quantity = parseInt(event.target.value) || 1;
            this.log('📊 Quantité changée:', quantity);
            this.updateQuantity(quantity);
        });

        // NOUVEAU : Pour produits simples, activer les boutons par défaut
        document.addEventListener('DOMContentLoaded', () => {
            // Chercher les produits simples et activer leurs boutons
            document.querySelectorAll('.nfc-simple-buttons').forEach(container => {
                const productId = container.dataset.productId;
                const productType = this.detectProductType(productId);

                if (productType === 'simple') {
                    const buttons = container.querySelectorAll('button, a');
                    buttons.forEach(btn => {
                        btn.removeAttribute('disabled');
                        this.log(`✅ Bouton activé pour produit simple ${productId}`);
                    });
                }
            });
        });
    }

    /**
     * Gestion du clic sur "Personnaliser en ligne"
     */
    handleConfiguratorClick(event, button) {
        event.preventDefault();

        const productId = button.closest('.nfc-simple-buttons')?.dataset.productId;
        if (!productId) {
            this.log('❌ Product ID non trouvé');
            return;
        }

        // Récupérer les données WooCommerce actuelles
        const wooData = this.getWooCommerceData(productId);

        // Construire l'URL du configurateur avec paramètres
        const configuratorUrl = this.buildConfiguratorUrl(productId, wooData);

        this.log('🎨 Redirection configurateur:', configuratorUrl);

        // Rediriger
        window.location.href = configuratorUrl;
    }

    /**
     * Gestion du clic sur "Ajouter au panier"
     */
    async handleCartClick(event, button) {
        event.preventDefault();

        const productId = button.closest('.nfc-simple-buttons')?.dataset.productId;
        if (!productId) {
            this.log('❌ Product ID non trouvé');
            return;
        }

        // Vérifier que les données WooCommerce sont valides
        const wooData = this.getWooCommerceData(productId);
        if (!this.validateWooData(wooData)) {
            alert(this.config.i18n?.selectVariation || 'Veuillez sélectionner une variation');
            return;
        }

        try {
            // Marquer le début d'une action Ajax
            sessionStorage.setItem('nfc_recent_post_action', Date.now().toString());
            
            // Afficher l'état loading
            this.setButtonLoading(button, true);

            // Appel Ajax pour ajouter au panier
            const result = await this.addToCart(productId, wooData);

            if (result.success) {
                this.log('✅ Ajout panier réussi:', result);
                this.showSuccess(button, result.data.message);

                // NOUVEAU : Nettoyage automatique + redirection propre
                this.handleSuccessfulCartAdd(result.data.cart_url);

            } else {
                throw new Error(result.data || 'Erreur inconnue');
            }

        } catch (error) {
            this.log('❌ Erreur ajout panier:', error);
            this.showError(button, error.message);
            
            // Nettoyer même en cas d'erreur
            this.cleanAfterError();
            
        } finally {
            this.setButtonLoading(button, false);
        }
    }

    /**
     * NOUVELLE MÉTHODE : Gère le succès d'ajout au panier avec nettoyage
     */
    handleSuccessfulCartAdd(cartUrl) {
        // Option 1 : Redirection immédiate avec nettoyage
        setTimeout(() => {
            // Nettoyer avant de partir
            const cleanCartUrl = cartUrl + (cartUrl.includes('?') ? '&' : '?') + 'nfc_clean=1';
            window.location.replace(cleanCartUrl);
        }, 1000);
        
        // Option 2 : Rester sur la page mais nettoyer
        /*
        setTimeout(() => {
            this.performHistoryClean();
            // Optionnel : recharger la page proprement
            // window.location.replace(window.location.pathname + window.location.search);
        }, 2000);
        */
    }

    /**
     * NOUVELLE MÉTHODE : Nettoie après une erreur
     */
    cleanAfterError() {
        setTimeout(() => {
            this.performHistoryClean();
            // Supprimer le marqueur d'action POST
            sessionStorage.removeItem('nfc_recent_post_action');
        }, 2000);
    }


    /**
     * Récupère les données du formulaire WooCommerce
     */
    getWooCommerceData(productId) {
        const form = document.querySelector(`form.variations_form[data-product_id="${productId}"]`);

        // NOUVEAU : Si pas de formulaire variations = produit simple
        if (!form) {
            this.log('⚠️ Pas de formulaire variations trouvé - Produit simple détecté');

            // Pour produit simple, chercher le formulaire cart standard
            const simpleForm = document.querySelector(`form.cart[data-product_id="${productId}"]`);

            if (simpleForm) {
                const quantityInput = simpleForm.querySelector('input[name="quantity"]');
                const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;

                this.log(`📦 Produit simple - Quantité: ${quantity}`);

                return {
                    quantity: quantity,
                    variation_id: null, // Pas de variation pour produit simple
                    attributes: {},
                    isSimpleProduct: true // Flag pour identifier le type
                };
            }

            // Fallback si aucun formulaire trouvé
            this.log('⚠️ Aucun formulaire WooCommerce trouvé, utilisation des valeurs par défaut');
            return {
                quantity: 1,
                variation_id: null,
                attributes: {},
                isSimpleProduct: true
            };
        }

        // EXISTANT : Logique pour produits variables
        const formData = new FormData(form);
        const data = {
            quantity: parseInt(formData.get('quantity')) || 1,
            variation_id: parseInt(form.querySelector('.variation_id').value) || null,
            attributes: {},
            isSimpleProduct: false
        };

        // Récupérer les attributs sélectionnés
        form.querySelectorAll('select[name^="attribute_"]').forEach(select => {
            if (select.value) {
                data.attributes[select.name] = select.value;
            }
        });

        this.log('📊 Données WooCommerce récupérées (produit variable):', data);
        return data;
    }

    /**
     * Valide les données WooCommerce
     */
    validateWooData(data) {
        // NOUVEAU : Pour produits simples, pas besoin de variation_id
        if (data.isSimpleProduct) {
            this.log('✅ Validation produit simple - OK');
            return data.quantity > 0;
        }

        // EXISTANT : Pour produits variables, vérifier variation_id
        const variationForm = document.querySelector('.variations_form');
        if (variationForm && !data.variation_id) {
            this.log('❌ Validation produit variable - variation_id manquant');
            return false;
        }

        return data.quantity > 0;
    }

    /**
     * Construit l'URL du configurateur avec paramètres
     */
    buildConfiguratorUrl(productId, wooData) {
        const baseUrl = `${this.config.homeUrl}/configurateur/`;
        const params = new URLSearchParams({
            product_id: productId,
            quantity: wooData.quantity
        });

        // MODIFIER : Ajouter variation_id seulement si ce n'est pas un produit simple
        if (wooData.variation_id && !wooData.isSimpleProduct) {
            params.append('variation_id', wooData.variation_id);
        }

        // Ajouter les attributs (seulement pour produits variables)
        if (!wooData.isSimpleProduct) {
            Object.entries(wooData.attributes).forEach(([key, value]) => {
                params.append(key, value);
            });
        }

        return `${baseUrl}?${params.toString()}`;
    }


    /**
     * Appel Ajax pour ajouter au panier
     */
    async addToCart(productId, wooData) {
        const formData = new FormData();
        formData.append('action', 'nfc_add_to_cart_simple');
        formData.append('product_id', productId);
        formData.append('quantity', wooData.quantity);
        formData.append('nonce', this.config.nonce);

        // MODIFIER : Ajouter variation_id seulement pour produits variables
        if (wooData.variation_id && !wooData.isSimpleProduct) {
            formData.append('variation_id', wooData.variation_id);
        }

        // MODIFIER : Ajouter les attributs seulement pour produits variables
        if (!wooData.isSimpleProduct) {
            Object.entries(wooData.attributes).forEach(([key, value]) => {
                formData.append(key, value);
            });
        }

        // Debug
        this.log('📨 Envoi AJAX:', {
            product_id: productId,
            quantity: wooData.quantity,
            variation_id: wooData.variation_id,
            isSimpleProduct: wooData.isSimpleProduct
        });

        const response = await fetch(this.config.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    }

    detectProductType(productId) {
        const variationForm = document.querySelector(`form.variations_form[data-product_id="${productId}"]`);
        const simpleForm = document.querySelector(`form.cart[data-product_id="${productId}"]`);

        if (variationForm) {
            this.log(`📦 Produit ${productId} détecté comme VARIABLE`);
            return 'variable';
        } else if (simpleForm) {
            this.log(`📦 Produit ${productId} détecté comme SIMPLE`);
            return 'simple';
        } else {
            this.log(`⚠️ Produit ${productId} - Type non déterminé`);
            return 'unknown';
        }
    }

    /**
     * Met à jour l'état des boutons selon la variation
     */
    updateButtonsState(variation) {
        const buttons = document.querySelectorAll('.nfc-simple-buttons');

        buttons.forEach(container => {
            const productId = container.dataset.productId;

            // Activer les boutons si variation valide
            const configuratorBtn = container.querySelector('.nfc-configurator-btn');
            const cartBtn = container.querySelector('.nfc-addcart-btn');

            if (variation.is_purchasable && variation.is_in_stock) {
                if (configuratorBtn) configuratorBtn.removeAttribute('disabled');
                if (cartBtn) cartBtn.removeAttribute('disabled');
            } else {
                if (configuratorBtn) configuratorBtn.setAttribute('disabled', 'disabled');
                if (cartBtn) cartBtn.setAttribute('disabled', 'disabled');
            }
        });
    }

    /**
     * Remet les boutons dans l'état initial
     */
    resetButtonsState() {
        const buttons = document.querySelectorAll('.nfc-simple-buttons button');
        buttons.forEach(btn => btn.setAttribute('disabled', 'disabled'));
    }

    /**
     * Met à jour la quantité stockée
     */
    updateQuantity(quantity) {
        this.currentQuantity = quantity;
    }

    /**
     * Gestion des états visuels des boutons
     */
    setButtonLoading(button, isLoading) {
        const textSpan = button.querySelector('.elementor-button-text');
        const loadingSpan = button.querySelector('.nfc-loading');

        if (isLoading) {
            button.disabled = true;
            if (textSpan) textSpan.style.display = 'none';
            if (loadingSpan) loadingSpan.style.display = 'flex';
        } else {
            button.disabled = false;
            if (textSpan) textSpan.style.display = 'block';
            if (loadingSpan) loadingSpan.style.display = 'none';
        }
    }

    showSuccess(button, message) {
        // Afficher un message de succès temporaire
        const originalText = button.querySelector('.elementor-button-text').textContent;
        button.querySelector('.elementor-button-text').textContent = message || 'Ajouté !';
        button.style.backgroundColor = '#28a745';

        setTimeout(() => {
            button.querySelector('.elementor-button-text').textContent = originalText;
            button.style.backgroundColor = '';
        }, 2000);
    }

    showError(button, message) {
        alert(message || this.config.i18n?.error || 'Une erreur est survenue');
    }

    /**
     * API publique pour initialiser un produit
     */
    initProduct(productId) {
        this.log(`📦 Initialisation produit ${productId}`);

        // Stocker les infos du produit
        this.products.set(productId, {
            id: productId,
            initialized: true,
            timestamp: Date.now()
        });
    }

    /**
     * Utilitaires
     */
    log(message, ...args) {
        if (this.debug) {
            console.log(`[NFCSimpleButtons] ${message}`, ...args);
        }
    }
}

// Auto-initialisation
window.NFCButtons = new NFCSimpleButtons();

// API globale pour compatibilité
window.NFCButtons.initProduct = function (productId) {
    window.NFCButtons.initProduct(productId);
};