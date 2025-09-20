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
        // Événement quand une variation est trouvée
        jQuery(document).on('found_variation', (event, variation) => {
            this.log('✅ Variation trouvée:', variation);
            this.updateButtonsState(variation);
        });

        // Événement quand variation est réinitialisée
        jQuery(document).on('reset_data', (event) => {
            this.log('🔄 Variation réinitialisée');
            this.resetButtonsState();
        });

        // Changement de quantité
        jQuery(document).on('change', 'input.qty', (event) => {
            const quantity = parseInt(event.target.value) || 1;
            this.log('📊 Quantité changée:', quantity);
            this.updateQuantity(quantity);
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
            // Afficher l'état loading
            this.setButtonLoading(button, true);
            
            // Appel Ajax pour ajouter au panier
            const result = await this.addToCart(productId, wooData);
            
            if (result.success) {
                this.log('✅ Ajout panier réussi:', result);
                this.showSuccess(button, result.data.message);
                
                // Optionnel : rediriger vers le panier
                setTimeout(() => {
                    window.location.href = result.data.cart_url;
                }, 1000);
                
            } else {
                throw new Error(result.data || 'Erreur inconnue');
            }
            
        } catch (error) {
            this.log('❌ Erreur ajout panier:', error);
            this.showError(button, error.message);
        } finally {
            this.setButtonLoading(button, false);
        }
    }

    /**
     * Récupère les données du formulaire WooCommerce
     */
    getWooCommerceData(productId) {
        const form = document.querySelector(`form.variations_form[data-product_id="${productId}"]`);
        
        if (!form) {
            this.log('⚠️ Formulaire WooCommerce non trouvé, utilisation des valeurs par défaut');
            return {
                quantity: 1,
                variation_id: null,
                attributes: {}
            };
        }

        // Récupérer les données du formulaire
        const formData = new FormData(form);
        const data = {
            quantity: parseInt(formData.get('quantity')) || 1,
            variation_id: parseInt(form.querySelector('.variation_id').value) || null,
            attributes: {}
        };

        // Récupérer les attributs sélectionnés
        form.querySelectorAll('select[name^="attribute_"]').forEach(select => {
            if (select.value) {
                data.attributes[select.name] = select.value;
            }
        });

        this.log('📊 Données WooCommerce récupérées:', data);
        return data;
    }

    /**
     * Valide les données WooCommerce
     */
    validateWooData(data) {
        // Pour un produit variable, on a besoin d'une variation_id
        const variationForm = document.querySelector('.variations_form');
        if (variationForm && !data.variation_id) {
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

        // Ajouter variation_id si présent
        if (wooData.variation_id) {
            params.append('variation_id', wooData.variation_id);
        }

        // Ajouter les attributs
        Object.entries(wooData.attributes).forEach(([key, value]) => {
            params.append(key, value);
        });

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

        if (wooData.variation_id) {
            formData.append('variation_id', wooData.variation_id);
        }

        // Ajouter les attributs
        Object.entries(wooData.attributes).forEach(([key, value]) => {
            formData.append(key, value);
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
window.NFCButtons.initProduct = function(productId) {
    window.NFCButtons.initProduct(productId);
};