/**
 * Fancy Product Designer - Version simplifiée mode standard
 * Plus d'Ajax UICore - Formulaire standard uniquement
 */

(function($) {
    'use strict';
    
    // Configuration
    const FPD_CONFIG = {
        containerSelector: '#fancy-product-designer-571',
        originalButtonSelector: '.single_add_to_cart_button',
        customAnchor: '#custom'
    };
    
    /**
     * Gestionnaire FPD simplifié pour mode standard
     */
    class FPDStandardMode {
        constructor() {
            this.isCustomizing = false;
            this.isCustomized = false;
            this.$originalButton = null;
            this.$customizeButton = null;
            
            this.init();
        }
        
        /**
         * Initialisation
         */
        init() {
            if (this.shouldRun()) {
                this.setupElements();
                this.createCustomizeButton();
                this.setInitialState();
                this.bindEvents();
                
                console.log('FPD Standard Mode: Initialisé - Mode formulaire standard');
            }
        }
        
        /**
         * Vérifier si on doit lancer le script
         */
        shouldRun() {
            const hasContainer = $(FPD_CONFIG.containerSelector).length > 0;
            const hasButton = $(FPD_CONFIG.originalButtonSelector).length > 0;
            
            if (!hasContainer) console.log('FPD: Container FPD non trouvé');
            if (!hasButton) console.log('FPD: Bouton original non trouvé');
            
            return hasContainer && hasButton;
        }
        
        /**
         * Configuration des éléments DOM
         */
        setupElements() {
            this.$originalButton = $(FPD_CONFIG.originalButtonSelector).first();
            console.log('FPD: Bouton original trouvé');
        }
        
        /**
         * Créer le bouton "Personnaliser"
         */
        createCustomizeButton() {
            // Supprimer les doublons existants
            $('.fpd-customize-btn').remove();
            
            // Cloner le bouton original sans les classes Ajax
            this.$customizeButton = this.$originalButton.clone(true, true)
                .removeClass('ajax_add_to_cart add_to_cart_button uicore-ajax-add-to-cart')
                .addClass('fpd-customize-btn')
                .attr('type', 'button')
                .removeAttr('name data-product_id value data-ajax')
                .off()
                .html('<span class="ui-btn-anim-wrapp"><span class="elementor-button-text">🎨 Personnaliser la carte</span><span class="elementor-button-text">🎨 Personnaliser la carte</span></span>');
            
            this.$originalButton.before(this.$customizeButton);
            console.log('FPD: Bouton personnaliser créé (mode standard)');
        }
        
        /**
         * État initial
         */
        setInitialState() {
            this.$originalButton.hide();
            this.$customizeButton.show();
            console.log('FPD: État initial - Mode standard');
        }
        
        /**
         * État personnalisation
         */
        setCustomizingState() {
            this.isCustomizing = true;
            
            this.$customizeButton
                .show()
                .prop('disabled', true)
                .addClass('fpd-loading')
                .find('.elementor-button-text')
                .html('<span class="fpd-loader"></span> Personnalisation en cours...');
            
            this.$originalButton.hide();
            console.log('FPD: État personnalisation');
        }
        
        /**
         * État final - Mode standard
         */
        setFinalState() {
            this.isCustomized = true;
            this.isCustomizing = false;
            
            // Sauvegarder PUIS afficher le bouton
            this.saveFPDCustomization().then(() => {
                this.$originalButton
                    .addClass('fpd-customized')
                    .show()
                    .find('.elementor-button-text')
                    .html('✅ Valider la personnalisation et ajouter au panier');
                
                this.$customizeButton.hide();
                console.log('FPD: État final - Prêt pour soumission standard');
            }).catch((error) => {
                console.error('FPD: Erreur sauvegarde:', error);
                // Afficher quand même avec un message d'erreur
                this.$originalButton
                    .addClass('fpd-customized')
                    .show()
                    .find('.elementor-button-text')
                    .html('⚠️ Ajouter au panier (personnalisation non sauvée)');
                
                this.$customizeButton.hide();
            });
        }
        
        /**
         * Scroll vers le designer FPD
         */
        scrollToDesigner() {
            let target = document.querySelector(FPD_CONFIG.customAnchor) || 
                        document.querySelector(FPD_CONFIG.containerSelector);
            
            if (target) {
                console.log('FPD: Scroll vers le designer');
                target.setAttribute('data-fpd-modified', 'true');
                
                target.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
                
                this.highlightDesigner(target);
                this.setCustomizingState();
            }
        }
        
        /**
         * Effet de highlight
         */
        highlightDesigner(target) {
            setTimeout(() => {
                target.style.outline = '3px solid #007cba';
                target.style.backgroundColor = 'rgba(0, 124, 186, 0.1)';
                
                setTimeout(() => {
                    target.style.outline = 'none';
                    target.style.backgroundColor = 'transparent';
                }, 3000);
            }, 500);
        }
        
        /**
         * Bind des événements
         */
        bindEvents() {
            // Clic sur "Personnaliser"
            this.$customizeButton.on('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.scrollToDesigner();
                return false;
            });
            
            // Détection automatique de personnalisation
            this.bindAutoDetection();
            
            console.log('FPD: Événements bindés (mode standard)');
        }
        
        /**
         * Détection automatique de personnalisation
         */
        bindAutoDetection() {
            // Timer après première interaction
            $(document).one('click', FPD_CONFIG.containerSelector, () => {
                if (!this.isCustomized) {
                    console.log('FPD: Première interaction détectée');
                    
                    setTimeout(() => {
                        if (!this.isCustomized) {
                            console.log('FPD: Auto-validation personnalisation');
                            this.setFinalState();
                        }
                    }, 8000); // Réduit à 8 secondes
                }
            });
            
            // Détection Ajax FPD
            $(document).ajaxSuccess((event, xhr, settings) => {
                if (!this.isCustomized && settings.url && 
                    (settings.url.includes('fpd_custom_uplod_file') || 
                     settings.url.includes('fpd_save_product') ||
                     settings.url.includes('fpd_add_element'))) {
                    console.log('FPD: Personnalisation détectée via Ajax');
                    this.setFinalState();
                }
            });
            
            // Observer DOM
            this.observeFPDChanges();
        }
        
        /**
         * Observer les changements DOM
         */
        observeFPDChanges() {
            const container = document.querySelector(FPD_CONFIG.containerSelector);
            
            if (container) {
                const observer = new MutationObserver((mutations) => {
                    if (!this.isCustomized) {
                        mutations.forEach((mutation) => {
                            if (mutation.type === 'childList' && 
                                (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0)) {
                                console.log('FPD: Changement DOM détecté');
                                clearTimeout(this.changeTimeout);
                                this.changeTimeout = setTimeout(() => {
                                    if (!this.isCustomized) {
                                        this.setFinalState();
                                    }
                                }, 2000); // Réduit à 2 secondes
                            }
                        });
                    }
                });
                
                observer.observe(container, { 
                    childList: true, 
                    subtree: true 
                });
                
                this.observer = observer;
            }
        }
        
        /**
         * Sauvegarder la personnalisation FPD - Version simplifiée
         */
        saveFPDCustomization() {
            return new Promise((resolve, reject) => {
                
                if (!this.checkFPDHasContent()) {
                    console.warn('FPD: Pas de contenu à sauvegarder');
                    const fallbackId = 'fpd_empty_' + Date.now();
                    this.updateFormField(fallbackId);
                    resolve({ fpd_id: fallbackId, message: 'Pas de personnalisation' });
                    return;
                }
                
                try {
                    this.getFPDData()
                        .then(productData => {
                            console.log('FPD: Données récupérées:', productData);
                            return this.saveToServer(productData);
                        })
                        .then(result => {
                            console.log('FPD: Sauvegarde réussie:', result);
                            resolve(result);
                        })
                        .catch(error => {
                            console.warn('FPD: Erreur sauvegarde:', error);
                            const fallbackId = 'fpd_fallback_' + this.getProductId() + '_' + Date.now();
                            this.updateFormField(fallbackId);
                            resolve({ fpd_id: fallbackId, message: 'Sauvegarde fallback' });
                        });
                        
                } catch (error) {
                    console.error('FPD: Erreur critique:', error);
                    const errorId = 'fpd_error_' + Date.now();
                    this.updateFormField(errorId);
                    resolve({ fpd_id: errorId, message: 'Erreur de sauvegarde' });
                }
            });
        }
        
        /**
         * Vérifier si FPD a du contenu
         */
        checkFPDHasContent() {
            const container = document.querySelector(FPD_CONFIG.containerSelector);
            if (!container) return false;
            
            if (this.isCustomizing || this.isCustomized) {
                console.log('FPD: Personnalisation détectée via workflow');
                return true;
            }
            
            const selectors = [
                '.fpd-element',
                '.fpd-text-element', 
                '.fpd-image-element',
                '[class*="fpd-"]',
                'canvas',
                'svg',
                '.fabric-canvas',
                '[data-custom="true"]',
                '.fpd-user-added'
            ];
            
            let totalElements = 0;
            selectors.forEach(selector => {
                const elements = container.querySelectorAll(selector);
                totalElements += elements.length;
            });
            
            console.log('FPD: Total éléments trouvés:', totalElements);
            return totalElements > 0 || this.wasCustomizationTriggered();
        }
        
        /**
         * Vérifier si personnalisation déclenchée
         */
        wasCustomizationTriggered() {
            const container = document.querySelector(FPD_CONFIG.containerSelector);
            if (!container) return false;
            
            const hasModifications = container.getAttribute('data-fpd-modified') === 'true' ||
                                   container.querySelector('.fpd-modified') ||
                                   container.querySelector('[data-modified="true"]');
            
            console.log('FPD: Modifications détectées:', hasModifications);
            return hasModifications;
        }
        
        /**
         * Récupérer les données FPD - Version simplifiée
         */
        getFPDData() {
            return new Promise((resolve, reject) => {
                console.log('FPD: Tentative récupération données...');
                
                // Méthode 1: API FPD globale
                if (typeof window.fancyProductDesigner !== 'undefined') {
                    try {
                        if (window.fancyProductDesigner.getProduct) {
                            const productData = window.fancyProductDesigner.getProduct();
                            console.log('FPD: Données via API globale:', productData);
                            if (productData && productData.length > 0) {
                                resolve(this.formatFPDData(productData));
                                return;
                            }
                        }
                    } catch (e) {
                        console.warn('FPD: API globale échouée:', e);
                    }
                }
                
                // Méthode 2: Container FPD
                const container = document.querySelector(FPD_CONFIG.containerSelector);
                if (container) {
                    const fpdInstances = [
                        container.fancyProductDesigner,
                        container.fpd,
                        container.FPD,
                        window.jQuery && window.jQuery(container).data('fancy-product-designer')
                    ];
                    
                    for (let instance of fpdInstances) {
                        if (instance && typeof instance.getProduct === 'function') {
                            try {
                                const productData = instance.getProduct();
                                console.log('FPD: Données via container:', productData);
                                if (productData && productData.length > 0) {
                                    resolve(this.formatFPDData(productData));
                                    return;
                                }
                            } catch (e) {
                                console.warn('FPD: Instance container échouée:', e);
                            }
                        }
                    }
                }
                
                // Méthode 3: Extraction manuelle du DOM
                console.log('FPD: Tentative extraction manuelle...');
                const manualData = this.extractFPDFromDOM();
                if (manualData && manualData.views && manualData.views.length > 0) {
                    resolve(manualData);
                    return;
                }
                
                // Fallback: données minimales
                console.warn('FPD: Aucune donnée récupérable, utilisation fallback');
                resolve({
                    views: [],
                    product_id: this.getProductId(),
                    customized: true,
                    timestamp: Date.now(),
                    note: 'Personnalisation détectée mais données non récupérables'
                });
            });
        }
        
        /**
         * Extraire les données FPD du DOM - Version simplifiée
         */
        extractFPDFromDOM() {
            const container = document.querySelector(FPD_CONFIG.containerSelector);
            if (!container) return null;
            
            const views = [];
            const canvases = container.querySelectorAll('canvas');
            
            console.log('FPD: Extraction DOM - Canvas:', canvases.length);
            
            if (canvases.length > 0) {
                canvases.forEach((canvas, index) => {
                    const view = {
                        title: 'Vue ' + (index + 1),
                        elements: [],
                        thumbnail: canvas.toDataURL ? canvas.toDataURL('image/png') : ''
                    };
                    
                    // Essayer de récupérer les objets Fabric.js
                    if (canvas.fabric && canvas.fabric.getObjects) {
                        const fabricObjects = canvas.fabric.getObjects();
                        fabricObjects.forEach(obj => {
                            const element = {
                                type: obj.type || 'unknown',
                                left: obj.left || 0,
                                top: obj.top || 0,
                                width: obj.width || 0,
                                height: obj.height || 0
                            };
                            
                            if (obj.type === 'image' && obj.src) {
                                element.source = obj.src;
                            } else if (obj.type === 'text' && obj.text) {
                                element.text = obj.text;
                                element.fontFamily = obj.fontFamily;
                                element.fontSize = obj.fontSize;
                                element.fill = obj.fill;
                            }
                            
                            view.elements.push(element);
                        });
                    }
                    
                    views.push(view);
                });
            }
            
            return {
                views: views,
                product_id: this.getProductId(),
                timestamp: Date.now(),
                extraction_method: 'DOM'
            };
        }
        
        /**
         * Formatter les données FPD
         */
        formatFPDData(rawData) {
            if (rawData.views) {
                return rawData;
            }
            
            if (Array.isArray(rawData)) {
                return {
                    views: rawData,
                    product_id: this.getProductId(),
                    timestamp: Date.now()
                };
            }
            
            return {
                views: [rawData],
                product_id: this.getProductId(),
                timestamp: Date.now(),
                raw_data: rawData
            };
        }
        
        /**
         * Sauvegarder sur le serveur via Ajax
         */
        saveToServer(productData) {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('action', 'fpd_save_customization');
                formData.append('product_id', this.getProductId());
                formData.append('fpd_data', JSON.stringify(productData));
                formData.append('nonce', this.getNonce());
                
                fetch(this.getAjaxUrl(), {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('FPD: Sauvegarde réussie:', data);
                        this.updateFormField(data.data.fpd_id);
                        resolve(data.data);
                    } else {
                        console.error('FPD: Erreur serveur:', data);
                        reject(data.message || 'Erreur de sauvegarde');
                    }
                })
                .catch(error => {
                    console.error('FPD: Erreur Ajax:', error);
                    reject(error);
                });
            });
        }
        
        /**
         * Mettre à jour le champ caché du formulaire - Version simplifiée
         */
        updateFormField(fpdId) {
            console.log('FPD: Mise à jour champ formulaire avec ID:', fpdId);
            
            // Trouver ou créer le champ caché
            let $fpdField = $('input[name="fpd_product"]');
            
            if ($fpdField.length) {
                $fpdField.val(fpdId);
                console.log('FPD: Champ existant mis à jour');
            } else {
                const $newField = $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'fpd_product')
                    .val(fpdId);
                
                const $form = $('form.cart');
                if ($form.length) {
                    $form.append($newField);
                    console.log('FPD: Nouveau champ créé dans form.cart');
                } else {
                    this.$originalButton.parent().append($newField);
                    console.log('FPD: Nouveau champ créé près du bouton');
                }
            }
            
            // Ajouter les autres champs FPD nécessaires
            this.ensureStandardFormFields();
            
            // Vérification
            const finalValue = $('input[name="fpd_product"]').val();
            console.log('FPD: Valeur finale du champ:', finalValue);
        }
        
        /**
         * S'assurer que tous les champs FPD sont présents
         */
        ensureStandardFormFields() {
            const $form = $('form.cart');
            
            const requiredFields = [
                'fpd_remove_cart_item',
                'fpd_print_order',
                'fpd_product_price'
            ];
            
            requiredFields.forEach(fieldName => {
                if ($form.find(`input[name="${fieldName}"]`).length === 0) {
                    $form.append(`<input type="hidden" name="${fieldName}" value="">`);
                }
            });
            
            console.log('FPD: Champs formulaire standard assurés');
        }
        
        /**
         * Utilitaires
         */
        getProductId() {
            return $('input[name="add-to-cart"], input[name="product_id"]').val() || 
                   this.$originalButton.data('product_id') || 
                   window.fpdConfig?.productId || 
                   571;
        }
        
        getAjaxUrl() {
            return window.fpdConfig?.ajaxUrl || '/wp-admin/admin-ajax.php';
        }
        
        getNonce() {
            return window.fpdConfig?.nonce || '';
        }
        
        /**
         * Méthodes de debug et contrôle
         */
        forceCustomizing() {
            this.setCustomizingState();
        }
        
        forceCompleted() {
            this.setFinalState();
        }
        
        reset() {
            this.isCustomizing = false;
            this.isCustomized = false;
            
            this.$originalButton
                .removeClass('fpd-customized')
                .hide()
                .find('.elementor-button-text')
                .html('Ajouter au panier');
            
            this.$customizeButton
                .removeClass('fpd-loading')
                .prop('disabled', false)
                .show()
                .find('.elementor-button-text')
                .html('🎨 Personnaliser la carte');
            
            // Supprimer le champ FPD
            $('input[name="fpd_product"]').remove();
            
            if (this.observer) {
                this.observer.disconnect();
            }
            console.log('FPD: Reset à l\'état initial');
        }
        
        getState() {
            return {
                mode: 'standard',
                isCustomizing: this.isCustomizing,
                isCustomized: this.isCustomized,
                originalButtonVisible: this.$originalButton.is(':visible'),
                originalButtonHasClass: this.$originalButton.hasClass('fpd-customized'),
                customizeButtonVisible: this.$customizeButton.is(':visible'),
                formFieldValue: $('input[name="fpd_product"]').val() || 'non défini'
            };
        }
    }
    
    /**
     * Initialisation automatique
     */
    $(document).ready(function() {
        // Attendre le chargement complet
        setTimeout(() => {
            if ($(FPD_CONFIG.containerSelector).length && $(FPD_CONFIG.originalButtonSelector).length) {
                
                // Créer l'instance
                window.FPDInstance = new FPDStandardMode();
                
                // Exposer les méthodes de debug
                window.debugFPD = {
                    customizing: () => window.FPDInstance.forceCustomizing(),
                    completed: () => window.FPDInstance.forceCompleted(),
                    reset: () => window.FPDInstance.reset(),
                    state: () => window.FPDInstance.getState(),
                    testForm: () => {
                        const $form = $('form.cart');
                        console.log('Form method:', $form.attr('method'));
                        console.log('Form action:', $form.attr('action'));
                        console.log('Has FPD field:', $('input[name="fpd_product"]').length > 0);
                        console.log('FPD field value:', $('input[name="fpd_product"]').val());
                    }
                };
                
                console.log('FPD Standard Mode: Prêt ! Debug: window.debugFPD.state()');
                
            } else {
                console.log('FPD: Éléments requis non trouvés');
            }
        }, 1000); // Réduit à 1 seconde
    });
    
})(jQuery);
