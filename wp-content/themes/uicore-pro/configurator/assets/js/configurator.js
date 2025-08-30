/**
 * Configurateur NFC - AVEC SCREENSHOT GENERATOR INTÉGRÉ
 * Adapté à la nouvelle structure Bootstrap + génération d'aperçus
 */

if (typeof window.NFCConfigurator === 'undefined') {
    window.NFCConfigurator = class NFCConfigurator {
        constructor() {
            // Vérifier que la config existe
            if (!window.nfcConfig) {
                console.error('Configuration NFC manquante');
                return;
            }

            this.config = window.nfcConfig;
            this.productId = this.config.productId;
            this.variations = this.config.variations;

            // État de l'application
            this.state = {
                selectedColor: 'blanc',
                selectedVariation: this.variations['blanc'],
                userInfo: {
                    firstName: '',
                    lastName: ''
                },
                image: null,
                isValid: false
            };

            // Éléments DOM
            this.elements = {};

            // NOUVEAU : Screenshot generator
            this.screenshotGenerator = null;

            this.init();
        }

        /**
         * Initialisation du configurateur
         */
        async init() {
            try {
                console.log('🚀 Initialisation du configurateur NFC');

                // Cache des éléments DOM
                this.cacheElements();

                // Charger le QR Code SVG
                await this.loadQRCodeSVG();

                // NOUVEAU : Initialiser screenshot generator
                this.screenshotGenerator = new window.NFCScreenshotGenerator(this);

                // Bind les événements
                this.bindEvents();

                // État initial
                this.setInitialState();

                // Validation initiale
                this.validateConfiguration();

                console.log('✅ Configurateur initialisé avec succès');

            } catch (error) {
                console.error('❌ Erreur initialisation configurateur:', error);
                this.showError('Erreur lors du chargement du configurateur: ' + error.message);
            }
        }

        /**
         * Cache les éléments DOM selon la nouvelle structure
         */
        cacheElements() {
            this.elements = {
                // Sélection couleur
                colorInputs: document.querySelectorAll('input[name="card-color"]'),

                // Cartes preview
                rectoCard: document.querySelector('.card-preview.recto'),
                versoCard: document.querySelector('.card-preview.verso'),

                // Informations utilisateur
                firstNameInput: document.getElementById('firstName'),
                lastNameInput: document.getElementById('lastName'),
                versoUserFirstName: document.getElementById('versoUserFirstName'),
                versoUserLastName: document.getElementById('versoUserLastName'),

                // Upload image
                imageUploadZone: document.getElementById('imageUploadZone'),
                imageInput: document.getElementById('imageInput'),
                imageControls: document.getElementById('imageControls'),
                imageAdjustStep: document.getElementById('imageAdjustStep'),
                imageMask: document.getElementById('imageMask'),
                cardImage: document.getElementById('cardImage'),

                // Contrôles image
                imageScale: document.getElementById('imageScale'),
                imageX: document.getElementById('imageX'),
                imageY: document.getElementById('imageY'),
                removeImageBtn: document.getElementById('removeImageBtn'),

                // QR Code
                qrCode: document.getElementById('qrCode'),

                // Bouton ajout panier
                addToCartBtn: document.getElementById('addToCartBtn'),
                loadingOverlay: document.getElementById('loadingOverlay')
            };

            // Debug éléments manquants
            Object.entries(this.elements).forEach(([key, element]) => {
                if (!element || (element.length === 0 && key === 'colorInputs')) {
                    console.warn(`❌ Élément manquant: ${key}`);
                }
            });
        }

        /**
         * Charge le QR Code SVG
         */
        async loadQRCodeSVG() {
            try {
                const svgPath = `${window.location.origin}/wp-content/themes/uicore-pro/configurator/assets/images/qrcode.svg`;
                console.log('📡 Chargement QR SVG depuis:', svgPath);

                const response = await fetch(svgPath);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status} - ${response.statusText}`);
                }

                this.qrCodeSVG = await response.text();
                console.log('✅ QR Code SVG chargé');

                // Injecter le SVG dans les cartes verso
                this.updateQRCodeDisplay();

            } catch (error) {
                console.error('❌ Erreur chargement QR SVG:', error);
                // Fallback en cas d'erreur
                this.qrCodeSVG = '<svg width="80" height="80" viewBox="0 0 100 100"><rect width="80" height="80" fill="currentColor" opacity="0.1"/><text x="50" y="50" text-anchor="middle" dy="0.3em" font-size="12" fill="currentColor">QR CODE</text></svg>';
                this.updateQRCodeDisplay();
            }
        }

        /**
         * Met à jour l'affichage du QR Code SVG
         */
        updateQRCodeDisplay() {
            if (this.elements.qrCode && this.qrCodeSVG) {
                this.elements.qrCode.innerHTML = this.qrCodeSVG;
                console.log('✅ QR Code SVG injecté');
            }
        }

        /**
         * Bind tous les événements
         */
        bindEvents() {
            // Sélection couleur
            this.elements.colorInputs.forEach(input => {
                input.addEventListener('change', (e) => {
                    if (e.target.checked) {
                        this.changeColor(e.target.value);
                    }
                });
            });

            // Informations utilisateur
            if (this.elements.firstNameInput) {
                this.elements.firstNameInput.addEventListener('input', (e) => {
                    this.updateUserInfo('firstName', e.target.value);
                });
            }

            if (this.elements.lastNameInput) {
                this.elements.lastNameInput.addEventListener('input', (e) => {
                    this.updateUserInfo('lastName', e.target.value);
                });
            }

            // Upload d'image
            if (this.elements.imageUploadZone) {
                this.elements.imageUploadZone.addEventListener('click', () => {
                    this.elements.imageInput.click();
                });

                this.elements.imageUploadZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    this.elements.imageUploadZone.classList.add('border-primary');
                });

                this.elements.imageUploadZone.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    this.elements.imageUploadZone.classList.remove('border-primary');
                });

                this.elements.imageUploadZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    this.elements.imageUploadZone.classList.remove('border-primary');
                    this.handleImageDrop(e);
                });
            }

            if (this.elements.imageInput) {
                this.elements.imageInput.addEventListener('change', (e) => {
                    this.handleImageSelect(e);
                });
            }

            // Contrôles image
            if (this.elements.imageScale) {
                this.elements.imageScale.addEventListener('input', (e) => {
                    console.log('🎚️ Slider taille:', e.target.value);
                    this.updateImageTransform();
                });
            }

            if (this.elements.imageX) {
                this.elements.imageX.addEventListener('input', (e) => {
                    console.log('🎚️ Slider X:', e.target.value);
                    this.updateImageTransform();
                });
            }

            if (this.elements.imageY) {
                this.elements.imageY.addEventListener('input', (e) => {
                    console.log('🎚️ Slider Y:', e.target.value);
                    this.updateImageTransform();
                });
            }

            if (this.elements.removeImageBtn) {
                this.elements.removeImageBtn.addEventListener('click', () => {
                    this.removeImage();
                });
            }

            // Bouton ajout au panier
            if (this.elements.addToCartBtn) {
                this.elements.addToCartBtn.addEventListener('click', () => {
                    this.addToCart();
                });
            }
        }

        /**
         * Définit l'état initial
         */
        setInitialState() {
            // Sélectionner blanc par défaut
            const blancInput = document.querySelector('input[value="blanc"]');
            if (blancInput) {
                blancInput.checked = true;
            }

            this.changeColor('blanc');

            // Focus sur le premier champ
            if (this.elements.firstNameInput) {
                setTimeout(() => {
                    this.elements.firstNameInput.focus();
                }, 500);
            }
        }

        /**
         * Change la couleur de la carte et met à jour le QR
         */
        changeColor(color) {
            console.log(`🎨 Changement couleur: ${color}`);

            if (!this.variations[color]) {
                console.error(`Variation non trouvée pour la couleur: ${color}`);
                return;
            }

            // Mettre à jour l'état
            this.state.selectedColor = color;
            this.state.selectedVariation = this.variations[color];

            // Mettre à jour les visuels des cartes
            this.updateCardVisuals(color);

            // Revalider
            this.validateConfiguration();

            console.log(`✅ Couleur changée: ${color}`);
        }

        /**
         * Met à jour les visuels des cartes
         */
        updateCardVisuals(color) {
            // Changer les classes CSS des cartes
            if (this.elements.rectoCard) {
                this.elements.rectoCard.className = `card-preview recto ${color} shadow-lg`;
            }

            if (this.elements.versoCard) {
                this.elements.versoCard.className = `card-preview verso ${color} shadow-lg`;
                
                // Mise à jour du style selon la couleur
                if (color === 'noir') {
                    this.elements.versoCard.style.background = '#1a1a1a';
                    this.elements.versoCard.style.color = '#ffffff';
                } else {
                    this.elements.versoCard.style.background = '#ffffff';
                    this.elements.versoCard.style.color = '#333333';
                }
            }

            console.log(`🎯 Visuels cartes mis à jour: ${color}`);
        }

        /**
         * Met à jour les informations utilisateur
         */
        updateUserInfo(field, value) {
            this.state.userInfo[field] = value.trim();
            this.updateCardUserInfo();
            this.validateConfiguration();
        }

        /**
         * Met à jour l'affichage du nom sur les cartes
         */
        updateCardUserInfo() {
            const { firstName, lastName } = this.state.userInfo;

            if (this.elements.versoUserFirstName) {
                this.elements.versoUserFirstName.textContent = firstName || 'Prénom';
            }

            if (this.elements.versoUserLastName) {
                this.elements.versoUserLastName.textContent = lastName || 'Nom';
            }
        }

        /**
         * Gère la sélection d'image
         */
        handleImageSelect(e) {
            const file = e.target.files[0];
            if (file) {
                this.processImageFile(file);
            }
        }

        /**
         * Gère le drop d'image
         */
        handleImageDrop(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.processImageFile(files[0]);
            }
        }

        /**
         * Traite le fichier image
         */
        processImageFile(file) {
            const validation = this.validateImageFile(file);
            if (!validation.valid) {
                this.showError(validation.message);
                return;
            }

            console.log(`📷 Traitement image: ${file.name}`);

            const reader = new FileReader();
            reader.onload = (e) => {
                this.setImage(e.target.result, file.name);
            };
            reader.onerror = () => {
                this.showError('Erreur lors de la lecture du fichier');
            };
            reader.readAsDataURL(file);
        }

        /**
         * Valide un fichier image
         */
        validateImageFile(file) {
            const maxSize = 2 * 1024 * 1024; // 2MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];

            if (!allowedTypes.includes(file.type)) {
                return {
                    valid: false,
                    message: 'Format non supporté. Utilisez JPG, PNG ou SVG.'
                };
            }

            if (file.size > maxSize) {
                return {
                    valid: false,
                    message: 'Fichier trop volumineux. Maximum 2MB.'
                };
            }

            return { valid: true };
        }

        /**
         * Définit l'image - RÉVÉLATION AVEC BOOTSTRAP
         */
        setImage(dataUrl, fileName) {
            this.state.image = {
                data: dataUrl,
                name: fileName,
                scale: 25,
                x: 0,
                y: 0
            };

            console.log('📷 setImage appelé:', fileName);

            // Afficher l'image
            this.displayImageOnCard();

            // RÉVÉLER LES CONTRÔLES AVEC BOOTSTRAP
            console.log('🔧 Révélation des contrôles Bootstrap...');

            if (this.elements.removeImageBtn) {
                this.elements.removeImageBtn.classList.remove('d-none');
                this.elements.removeImageBtn.classList.add('d-block');
                console.log('✅ removeImageBtn révélé avec Bootstrap');
            }

            // Initialiser les sliders
            if (this.elements.imageScale) this.elements.imageScale.value = 25;
            if (this.elements.imageX) this.elements.imageX.value = 0;
            if (this.elements.imageY) this.elements.imageY.value = 0;

            // Mettre à jour les labels
            this.updateControlLabels();

            // Mettre à jour le texte de l'upload zone
            const uploadText = this.elements.imageUploadZone?.querySelector('.upload-text');
            if (uploadText) {
                uploadText.textContent = fileName;
            }

            this.validateConfiguration();

            console.log('✅ setImage terminé - contrôles révélés');
        }

        /**
         * Affiche l'image sur la carte
         */
        displayImageOnCard() {
            if (!this.state.image || !this.elements.imageMask || !this.elements.cardImage) {
                return;
            }

            // Masquer le placeholder
            this.elements.imageMask.classList.add('has-image');

            // Configurer l'image
            this.elements.cardImage.src = this.state.image.data;
            this.elements.cardImage.classList.remove('d-none');
            this.elements.cardImage.style.opacity = '1';

            // Appliquer les transformations
            this.updateImageTransform();
        }

        /**
         * Met à jour les transformations de l'image
         */
        updateImageTransform() {
            if (!this.state.image || !this.elements.cardImage) {
                return;
            }

            // Récupérer les valeurs
            const scale = this.elements.imageScale ? parseInt(this.elements.imageScale.value) : this.state.image.scale;
            const x = this.elements.imageX ? parseInt(this.elements.imageX.value) : this.state.image.x;
            const y = this.elements.imageY ? parseInt(this.elements.imageY.value) : this.state.image.y;

            // Mettre à jour l'état
            this.state.image.scale = scale;
            this.state.image.x = x;
            this.state.image.y = y;

            // Appliquer la transformation
            const scaleValue = scale / 100;
            const translateX = -50 + x;
            const translateY = -50 + y;

            this.elements.cardImage.style.transform =
                `translate(${translateX}%, ${translateY}%) scale(${scaleValue})`;

            // Mettre à jour les labels
            this.updateControlLabels();

            console.log(`🎯 Transform: scale(${scaleValue}) translate(${translateX}%, ${translateY}%)`);
        }

        /**
         * Met à jour les labels des contrôles
         */
        updateControlLabels() {
            // Label taille
            const scaleValue = document.querySelector('.scale-value');
            if (scaleValue && this.elements.imageScale) {
                scaleValue.textContent = `${this.elements.imageScale.value}%`;
            }

            // Labels position
            const xValue = document.querySelector('.position-value-x');
            if (xValue && this.elements.imageX) {
                xValue.textContent = this.elements.imageX.value;
            }

            const yValue = document.querySelector('.position-value-y');
            if (yValue && this.elements.imageY) {
                yValue.textContent = this.elements.imageY.value;
            }
        }

        /**
         * Supprime l'image - MASQUAGE BOOTSTRAP
         */
        removeImage() {
            this.state.image = null;

            console.log('🗑️ Suppression image...');

            // Restaurer le placeholder
            if (this.elements.imageMask) {
                this.elements.imageMask.classList.remove('has-image');
            }

            if (this.elements.cardImage) {
                this.elements.cardImage.classList.add('d-none');
                this.elements.cardImage.src = '';
            }

            // MASQUER LE BOUTON SUPPRIMER AVEC BOOTSTRAP
            if (this.elements.removeImageBtn) {
                this.elements.removeImageBtn.classList.remove('d-block');
                this.elements.removeImageBtn.classList.add('d-none');
            }

            // Reset des sliders
            if (this.elements.imageScale) this.elements.imageScale.value = 25;
            if (this.elements.imageX) this.elements.imageX.value = 0;
            if (this.elements.imageY) this.elements.imageY.value = 0;

            // Restaurer le texte d'upload
            const uploadText = this.elements.imageUploadZone?.querySelector('.upload-text');
            if (uploadText) {
                uploadText.textContent = 'Sélectionner un fichier...';
            }

            // Reset input file
            if (this.elements.imageInput) {
                this.elements.imageInput.value = '';
            }

            this.validateConfiguration();

            console.log('✅ Image supprimée');
        }

        /**
         * Valide la configuration
         */
        validateConfiguration() {
            const { firstName, lastName } = this.state.userInfo;
            const isValid = firstName.length > 0 && lastName.length > 0;

            this.state.isValid = isValid;

            if (this.elements.addToCartBtn) {
                this.elements.addToCartBtn.disabled = !isValid;
            }
        }

        /**
         * NOUVEAU : Ajoute au panier avec screenshot
         */
        async addToCart() {
            if (!this.state.isValid) {
                this.showError('Veuillez remplir tous les champs requis');
                return;
            }

            console.log('🛒 Ajout au panier avec screenshot...');
            this.showLoading(true);

            try {
                // GÉNÉRER LE SCREENSHOT
                console.log('📸 Génération du screenshot...');
                const screenshot = await this.screenshotGenerator.generateScreenshot();
                const thumbnail = await this.screenshotGenerator.generateThumbnail(300);
                
                console.log('✅ Screenshot généré');

                // Préparer les données de configuration
                const configData = {
                    variation_id: this.state.selectedVariation.id,
                    color: this.state.selectedColor,
                    user: this.state.userInfo,
                    image: this.state.image,
                    screenshot: {
                        full: screenshot,
                        thumbnail: thumbnail,
                        generated_at: new Date().toISOString()
                    },
                    timestamp: Date.now()
                };

                console.log('📦 Données config préparées');

                // Appel Ajax
                const response = await this.ajaxCall('nfc_add_to_cart', {
                    product_id: this.productId,
                    variation_id: this.state.selectedVariation.id,
                    nfc_config: JSON.stringify(configData),
                    nonce: this.config.nonce
                });

                if (response.success) {
                    console.log('✅ Ajouté au panier avec succès (avec screenshot)');
                    window.location.href = this.config.cartUrl;
                } else {
                    throw new Error(response.data || 'Erreur ajout panier');
                }

            } catch (error) {
                console.error('❌ Erreur ajout panier:', error);
                this.showError('Erreur: ' + error.message);
                this.showLoading(false);
            }
        }

        /**
         * Appel Ajax générique
         */
        async ajaxCall(action, data) {
            const formData = new FormData();
            formData.append('action', action);

            for (const [key, value] of Object.entries(data)) {
                formData.append(key, value);
            }

            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            return await response.json();
        }

        /**
         * Affiche/masque le loading avec Bootstrap
         */
        showLoading(show) {
            if (this.elements.loadingOverlay) {
                if (show) {
                    this.elements.loadingOverlay.classList.remove('d-none');
                    this.elements.loadingOverlay.classList.add('d-flex');
                } else {
                    this.elements.loadingOverlay.classList.remove('d-flex');
                    this.elements.loadingOverlay.classList.add('d-none');
                }
            }
        }

        /**
         * Messages utilisateur
         */
        showError(message) {
            console.error('❌', message);
            alert('Erreur: ' + message);
        }

        showSuccess(message) {
            console.log('✅', message);
            alert('Succès: ' + message);
        }
    };
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    console.log('🔥 Initialisation configurateur avec screenshot...');

    try {
        // Vérifier que le screenshot generator est disponible
        if (typeof window.NFCScreenshotGenerator === 'undefined') {
            console.error('❌ NFCScreenshotGenerator non trouvé ! Inclure screenshot-generator.js');
            return;
        }

        window.nfcConfigurator = new window.NFCConfigurator();
        console.log('✅ Configurateur avec screenshot initialisé');
    } catch (error) {
        console.error('❌ Erreur:', error);
        alert('Erreur fatale: ' + error.message);
    }
});


/**
 * ==========================================================================
 * EXTENSION LOGO VERSO + CHECKBOX INFORMATIONS + NOM OPTIONNEL
 * Extension de la classe NFCConfigurator existante
 * ==========================================================================
 */

(function() {
    'use strict';
    
    // Attendre que le DOM soit prêt et la classe de base disponible
    document.addEventListener('DOMContentLoaded', function() {
        // Délai pour s'assurer que la classe principale est initialisée
        setTimeout(function() {
            if (typeof window.NFCConfigurator !== 'undefined' && window.configurator) {
                console.log('🔧 Extension configurateur - Initialisation...');
                extendNFCConfigurator();
            } else {
                console.warn('⚠️ Classe NFCConfigurator non trouvée pour extension');
            }
        }, 200);
    });

    function extendNFCConfigurator() {
        const configurator = window.configurator;
        
        // ===================================================================
        // EXTENSION 1: INITIALISATION LOGO VERSO
        // ===================================================================
        
        // Sauvegarder l'initialisation originale
        const originalInit = configurator.initialize || configurator.init || function() {};
        
        // Nouvelle initialisation étendue
        configurator.initialize = function() {
            // Appeler l'init original
            originalInit.call(this);
            
            // Ajouter nos extensions
            this.initLogoVerso();
            this.initCheckboxInformations();
            
            console.log('✅ Extension logo verso + checkbox informations initialisée');
        };
        
        // ===================================================================
        // EXTENSION 2: MÉTHODES LOGO VERSO
        // ===================================================================
        
        configurator.initLogoVerso = function() {
            console.log('📷 Initialisation logo verso...');
            
            // Éléments DOM logo verso
            this.elements = this.elements || {};
            this.elements.logoVersoInput = document.getElementById('logoVersoInput');
            this.elements.logoVersoUploadZone = document.getElementById('logoVersoUploadZone');
            this.elements.logoVersoControls = document.getElementById('logoVersoControls');
            this.elements.logoVersoImage = document.getElementById('logoVersoImage');
            this.elements.logoVersoScale = document.getElementById('logoVersoScale');
            this.elements.logoVersoScaleValue = document.getElementById('logoVersoScaleValue');
            this.elements.removeLogoVersoBtn = document.getElementById('removeLogoVersoBtn');
            this.elements.logoVersoArea = document.getElementById('logoVersoArea');
            
            // État logo verso
            this.state = this.state || {};
            this.state.logoVerso = {
                dataUrl: null,
                name: null,
                scale: 100
            };
            
            this.bindLogoVersoEvents();
        };
        
        configurator.bindLogoVersoEvents = function() {
            const self = this;
            
            // Upload logo verso
            if (this.elements.logoVersoInput) {
                this.elements.logoVersoInput.addEventListener('change', function(e) {
                    self.handleLogoVersoSelect(e);
                });
            }
            
            // Click zone upload
            if (this.elements.logoVersoUploadZone) {
                this.elements.logoVersoUploadZone.addEventListener('click', function() {
                    if (self.elements.logoVersoInput) {
                        self.elements.logoVersoInput.click();
                    }
                });
                
                // Drag & Drop logo verso
                this.elements.logoVersoUploadZone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.add('drag-over');
                });
                
                this.elements.logoVersoUploadZone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.remove('drag-over');
                });
                
                this.elements.logoVersoUploadZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.remove('drag-over');
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        self.processLogoVersoFile(files[0]);
                    }
                });
            }
            
            // Slider taille logo verso
            if (this.elements.logoVersoScale) {
                this.elements.logoVersoScale.addEventListener('input', function(e) {
                    const scale = parseInt(e.target.value);
                    self.updateLogoVersoScale(scale);
                });
            }
            
            // Bouton supprimer logo verso
            if (this.elements.removeLogoVersoBtn) {
                this.elements.removeLogoVersoBtn.addEventListener('click', function() {
                    self.removeLogoVerso();
                });
            }
            
            console.log('🔗 Événements logo verso bindés');
        };
        
        configurator.handleLogoVersoSelect = function(e) {
            const file = e.target.files[0];
            if (file) {
                console.log('📁 Logo verso sélectionné:', file.name);
                this.processLogoVersoFile(file);
            }
        };
        
        configurator.processLogoVersoFile = function(file) {
            // Utiliser la validation existante de la classe de base
            const validation = this.validateImageFile ? this.validateImageFile(file) : { valid: true };
            if (!validation.valid) {
                this.showError ? this.showError(validation.message) : console.error(validation.message);
                return;
            }
            
            console.log(`📷 Traitement logo verso: ${file.name}`);
            
            // Ajouter classe loading
            if (this.elements.logoVersoUploadZone) {
                this.elements.logoVersoUploadZone.classList.add('uploading');
            }
            
            const self = this;
            const reader = new FileReader();
            
            reader.onload = function(e) {
                self.setLogoVerso(e.target.result, file.name);
                
                // Retirer classe loading
                if (self.elements.logoVersoUploadZone) {
                    self.elements.logoVersoUploadZone.classList.remove('uploading');
                }
            };
            
            reader.onerror = function() {
                console.error('Erreur lecture logo verso');
                if (self.elements.logoVersoUploadZone) {
                    self.elements.logoVersoUploadZone.classList.remove('uploading');
                }
            };
            
            reader.readAsDataURL(file);
        };
        
        configurator.setLogoVerso = function(dataUrl, fileName) {
            console.log('🎨 Définition logo verso:', fileName);
            
            // Mettre à jour l'état
            this.state.logoVerso = {
                dataUrl: dataUrl,
                name: fileName,
                scale: 100
            };
            
            // Mettre à jour l'aperçu
            if (this.elements.logoVersoImage) {
                this.elements.logoVersoImage.src = dataUrl;
                this.elements.logoVersoImage.classList.remove('d-none');
            }
            
            if (this.elements.logoVersoArea) {
                this.elements.logoVersoArea.classList.add('has-logo');
            }
            
            // Afficher les contrôles
            if (this.elements.logoVersoControls) {
                this.elements.logoVersoControls.classList.remove('d-none');
            }
            
            // Mettre à jour texte upload
            const uploadText = this.elements.logoVersoUploadZone?.querySelector('.upload-text');
            if (uploadText) {
                uploadText.textContent = `✅ ${fileName}`;
                uploadText.style.color = '#198754';
            }
            
            console.log('✅ Logo verso défini avec succès');
        };
        
        configurator.updateLogoVersoScale = function(scale) {
            this.state.logoVerso.scale = scale;
            
            // Mettre à jour l'aperçu
            if (this.elements.logoVersoImage) {
                this.elements.logoVersoImage.style.transform = `scale(${scale / 100})`;
            }
            
            // Mettre à jour affichage pourcentage
            if (this.elements.logoVersoScaleValue) {
                this.elements.logoVersoScaleValue.textContent = scale + '%';
            }
            
            console.log(`🔧 Taille logo verso: ${scale}%`);
        };
        
        configurator.removeLogoVerso = function() {
            console.log('🗑️ Suppression logo verso...');
            
            // Reset état
            this.state.logoVerso = {
                dataUrl: null,
                name: null,
                scale: 100
            };
            
            // Reset UI
            if (this.elements.logoVersoImage) {
                this.elements.logoVersoImage.src = '';
                this.elements.logoVersoImage.classList.add('d-none');
                this.elements.logoVersoImage.style.transform = 'scale(1)';
            }
            
            if (this.elements.logoVersoArea) {
                this.elements.logoVersoArea.classList.remove('has-logo');
            }
            
            if (this.elements.logoVersoControls) {
                this.elements.logoVersoControls.classList.add('d-none');
            }
            
            if (this.elements.logoVersoInput) {
                this.elements.logoVersoInput.value = '';
            }
            
            if (this.elements.logoVersoScale) {
                this.elements.logoVersoScale.value = 100;
            }
            
            if (this.elements.logoVersoScaleValue) {
                this.elements.logoVersoScaleValue.textContent = '100%';
            }
            
            // Restaurer texte upload
            const uploadText = this.elements.logoVersoUploadZone?.querySelector('.upload-text');
            if (uploadText) {
                uploadText.textContent = 'Sélectionner un logo...';
                uploadText.style.color = '';
            }
            
            console.log('✅ Logo verso supprimé');
        };
        
        // ===================================================================
        // EXTENSION 3: GESTION CHECKBOX INFORMATIONS
        // ===================================================================
        
        configurator.initCheckboxInformations = function() {
            console.log('☑️ Initialisation checkbox informations...');
            
            this.elements.checkboxInformations = document.getElementById('checkboxInformations');
            this.elements.userSection = document.querySelector('.card-preview.verso .user-section');
            
            if (this.elements.checkboxInformations) {
                const self = this;
                this.elements.checkboxInformations.addEventListener('change', function() {
                    self.toggleUserInformations();
                });
                
                // Initialiser l'état selon la checkbox
                this.toggleUserInformations();
            }
        };
        
        configurator.toggleUserInformations = function() {
            const isChecked = this.elements.checkboxInformations?.checked;
            
            // Fallback pour navigateurs sans support :has()
            if (isChecked) {
                document.body.classList.remove('checkbox-off');
                if (this.elements.userSection) {
                    this.elements.userSection.classList.remove('hidden');
                }
            } else {
                document.body.classList.add('checkbox-off');
                if (this.elements.userSection) {
                    this.elements.userSection.classList.add('hidden');
                }
            }
            
            console.log('☑️ Informations utilisateur:', isChecked ? 'affichées' : 'masquées');
        };
        
        // ===================================================================
        // EXTENSION 4: OVERRIDE VALIDATION - NOM OPTIONNEL
        // ===================================================================
        
        // Sauvegarder l'ancienne validation
        const originalValidate = configurator.validateConfiguration;
        
        configurator.validateConfiguration = function() {
            console.log('✅ Validation étendue - nom/prénom optionnels');
            
            // Configuration toujours valide maintenant
            this.state = this.state || {};
            this.state.isValid = true;
            
            // Débloquer bouton panier
            this.elements = this.elements || {};
            if (this.elements.addToCartBtn) {
                this.elements.addToCartBtn.disabled = false;
                this.elements.addToCartBtn.classList.remove('disabled');
                
                // S'assurer du texte correct
                const btnText = this.elements.addToCartBtn.querySelector('span') || this.elements.addToCartBtn;
                if (btnText && !this.elements.addToCartBtn.classList.contains('loading')) {
                    // Garder le contenu existant s'il y a une icône
                    if (!btnText.innerHTML.includes('fa-shopping-cart')) {
                        btnText.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Ajouter au panier';
                    }
                }
            }
            
            console.log('🎯 Validation étendue - toujours valide');
        };
        
        // ===================================================================
        // EXTENSION 5: OVERRIDE GETCONFIGURATION - INCLURE LOGO VERSO
        // ===================================================================
        
        // Sauvegarder l'ancienne méthode
        const originalGetConfig = configurator.getConfiguration;
        
        configurator.getConfiguration = function() {
            // Récupérer config de base
            const config = originalGetConfig ? originalGetConfig.call(this) : {};
            
            console.log('📦 Configuration de base:', config);
            
            // Ajouter logo verso si présent
            if (this.state.logoVerso && this.state.logoVerso.dataUrl) {
                config.logoVerso = {
                    name: this.state.logoVerso.name,
                    data: this.state.logoVerso.dataUrl,
                    scale: this.state.logoVerso.scale
                };
                console.log('📷 Logo verso ajouté à la configuration');
            }
            
            // S'assurer que les noms peuvent être vides (optionnels)
            if (config.user) {
                config.user.firstName = (config.user.firstName || '').trim();
                config.user.lastName = (config.user.lastName || '').trim();
            }
            
            // Ajouter état checkbox informations
            if (this.elements.checkboxInformations) {
                config.showUserInfo = this.elements.checkboxInformations.checked;
            }
            
            console.log('📦 Configuration finale étendue:', config);
            return config;
        };
        
        // ===================================================================
        // INITIALISATION FINALE
        // ===================================================================
        
        // Réinitialiser avec les extensions
        if (configurator.initialize) {
            configurator.initialize();
        }
        
        // Override validation immédiatement pour débloquer le bouton
        configurator.validateConfiguration();
        
        console.log('🎉 Extension configurateur NFC complète !');
        
        // Exposer méthodes debug
        window.debugConfiguratorExtended = function() {
            console.log('🔍 État configurateur étendu:', {
                logoVerso: configurator.state?.logoVerso || null,
                showUserInfo: configurator.elements?.checkboxInformations?.checked || false,
                isValid: configurator.state?.isValid || false,
                userInfo: configurator.state?.userInfo || null
            });
        };
    }
    
})();