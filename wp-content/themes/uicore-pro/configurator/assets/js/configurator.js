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
    
    console.log('🔧 FIX Configurateur - Chargement...');
    
    // Initialisation immédiate après DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📦 DOM Ready - Initialisation fix configurateur...');
        
        // Attendre un peu que tout soit chargé
        setTimeout(function() {
            initConfiguratorFix();
        }, 500);
    });
    
    function initConfiguratorFix() {
        console.log('🚀 Initialisation fix configurateur...');
        
        // ===================================================================
        // FIX 1: UPLOAD LOGO VERSO - Événements directs
        // ===================================================================
        
        const logoVersoInput = document.getElementById('logoVersoInput');
        const logoVersoUploadZone = document.getElementById('logoVersoUploadZone');
        const logoVersoControls = document.getElementById('logoVersoControls');
        const logoVersoImage = document.getElementById('logoVersoImage');
        const logoVersoScale = document.getElementById('logoVersoScale');
        const logoVersoScaleValue = document.getElementById('logoVersoScaleValue');
        const removeLogoVersoBtn = document.getElementById('removeLogoVersoBtn');
        const logoVersoArea = document.getElementById('logoVersoArea');
        
        // État logo verso
        let logoVersoState = {
            dataUrl: null,
            name: null,
            scale: 100
        };
        
        console.log('📷 Éléments logo verso trouvés:', {
            input: !!logoVersoInput,
            uploadZone: !!logoVersoUploadZone,
            controls: !!logoVersoControls,
            image: !!logoVersoImage
        });
        
        // FIX: Click sur zone upload
        if (logoVersoUploadZone && logoVersoInput) {
            logoVersoUploadZone.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('📁 Click zone upload logo verso');
                logoVersoInput.click();
            });
        }
        
        // FIX: Click sur bouton "Ajouter logo" 
        const addLogoBtn = document.querySelector('button[onclick*="logoVersoInput"]');
        if (addLogoBtn && logoVersoInput) {
            // Retirer l'ancien onclick
            addLogoBtn.removeAttribute('onclick');
            
            addLogoBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🔘 Click bouton ajouter logo verso');
                logoVersoInput.click();
            });
        }
        
        // FIX: Input change
        if (logoVersoInput) {
            logoVersoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    console.log('📁 Fichier sélectionné:', file.name);
                    processLogoVersoFile(file);
                } else {
                    console.log('❌ Aucun fichier sélectionné');
                }
            });
        }
        
        // Drag & Drop
        if (logoVersoUploadZone) {
            logoVersoUploadZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('drag-over');
            });
            
            logoVersoUploadZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('drag-over');
            });
            
            logoVersoUploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('drag-over');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    console.log('📁 Fichier droppé:', files[0].name);
                    processLogoVersoFile(files[0]);
                }
            });
        }
        
        // Fonction traitement fichier
        function processLogoVersoFile(file) {
            console.log('🔄 Traitement fichier:', file.name, file.size, 'bytes');
            
            // Validation basique
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format non supporté. Utilisez JPG, PNG ou SVG.');
                return;
            }
            
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                alert('Fichier trop volumineux. Maximum 5MB.');
                return;
            }
            
            // Loading état
            if (logoVersoUploadZone) {
                logoVersoUploadZone.classList.add('uploading');
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                console.log('✅ Fichier lu avec succès');
                setLogoVerso(e.target.result, file.name);
                
                // Retirer loading
                if (logoVersoUploadZone) {
                    logoVersoUploadZone.classList.remove('uploading');
                }
            };
            
            reader.onerror = function() {
                console.error('❌ Erreur lecture fichier');
                alert('Erreur lors de la lecture du fichier');
                if (logoVersoUploadZone) {
                    logoVersoUploadZone.classList.remove('uploading');
                }
            };
            
            reader.readAsDataURL(file);
        }
        
        // Fonction définir logo verso
        function setLogoVerso(dataUrl, fileName) {
            console.log('🎨 Définition logo verso:', fileName);
            
            // Mettre à jour état
            logoVersoState = {
                dataUrl: dataUrl,
                name: fileName,
                scale: 100
            };
            
            // Mettre à jour aperçu
            if (logoVersoImage) {
                logoVersoImage.src = dataUrl;
                logoVersoImage.classList.remove('d-none');
                console.log('🖼️ Image logo verso mise à jour');
            }
            
            if (logoVersoArea) {
                logoVersoArea.classList.add('has-logo');
            }
            
            // Afficher contrôles
            if (logoVersoControls) {
                logoVersoControls.classList.remove('d-none');
                console.log('🎛️ Contrôles logo verso affichés');
            }
            
            // Mettre à jour texte upload
            const uploadText = logoVersoUploadZone?.querySelector('.upload-text');
            if (uploadText) {
                uploadText.textContent = `✅ ${fileName}`;
                uploadText.style.color = '#198754';
            }
            
            // Exposer état global
            if (!window.nfcConfiguratorState) {
                window.nfcConfiguratorState = {};
            }
            window.nfcConfiguratorState.logoVerso = logoVersoState;
            
            console.log('✅ Logo verso défini avec succès');
        }
        
        // Slider taille
        if (logoVersoScale) {
            logoVersoScale.addEventListener('input', function(e) {
                const scale = parseInt(e.target.value);
                updateLogoVersoScale(scale);
            });
        }
        
        function updateLogoVersoScale(scale) {
            logoVersoState.scale = scale;
            
            if (logoVersoImage) {
                logoVersoImage.style.transform = `scale(${scale / 100})`;
            }
            
            if (logoVersoScaleValue) {
                logoVersoScaleValue.textContent = scale + '%';
            }
            
            // Mettre à jour état global
            if (window.nfcConfiguratorState && window.nfcConfiguratorState.logoVerso) {
                window.nfcConfiguratorState.logoVerso.scale = scale;
            }
            
            console.log(`🔧 Taille logo verso: ${scale}%`);
        }
        
        // Bouton supprimer
        if (removeLogoVersoBtn) {
            removeLogoVersoBtn.addEventListener('click', function(e) {
                e.preventDefault();
                removeLogoVerso();
            });
        }
        
        function removeLogoVerso() {
            console.log('🗑️ Suppression logo verso...');
            
            // Reset état
            logoVersoState = {
                dataUrl: null,
                name: null,
                scale: 100
            };
            
            // Reset UI
            if (logoVersoImage) {
                logoVersoImage.src = '';
                logoVersoImage.classList.add('d-none');
                logoVersoImage.style.transform = 'scale(1)';
            }
            
            if (logoVersoArea) {
                logoVersoArea.classList.remove('has-logo');
            }
            
            if (logoVersoControls) {
                logoVersoControls.classList.add('d-none');
            }
            
            if (logoVersoInput) {
                logoVersoInput.value = '';
            }
            
            if (logoVersoScale) {
                logoVersoScale.value = 100;
            }
            
            if (logoVersoScaleValue) {
                logoVersoScaleValue.textContent = '100%';
            }
            
            // Restaurer texte
            const uploadText = logoVersoUploadZone?.querySelector('.upload-text');
            if (uploadText) {
                uploadText.textContent = 'Sélectionner un logo...';
                uploadText.style.color = '';
            }
            
            // Reset état global
            if (window.nfcConfiguratorState) {
                window.nfcConfiguratorState.logoVerso = null;
            }
            
            console.log('✅ Logo verso supprimé');
        }
        
        // ===================================================================
        // FIX 2: BOUTON PANIER - Force activation
        // ===================================================================
        
        const addToCartBtn = document.getElementById('addToCartBtn');
        
        function forceActivateCartButton() {
            if (addToCartBtn) {
                addToCartBtn.disabled = false;
                addToCartBtn.classList.remove('disabled');
                
                // Retirer attribut disabled du DOM si présent
                addToCartBtn.removeAttribute('disabled');
                
                console.log('🛒 Bouton panier activé de force');
                console.log('🛒 État bouton:', {
                    disabled: addToCartBtn.disabled,
                    hasDisabledClass: addToCartBtn.classList.contains('disabled'),
                    hasDisabledAttr: addToCartBtn.hasAttribute('disabled')
                });
            } else {
                console.warn('❌ Bouton panier non trouvé');
            }
        }
        
        // Activer immédiatement
        forceActivateCartButton();
        
        // Réactiver périodiquement (au cas où autre script le désactive)
        setInterval(forceActivateCartButton, 1000);
        
        // ===================================================================
        // FIX 3: CHECKBOX INFORMATIONS - Confirmation fonctionnement
        // ===================================================================
        
        const checkboxInformations = document.getElementById('checkboxInformations');
        const userSection = document.querySelector('.card-preview.verso .user-section');
        
        if (checkboxInformations) {
            console.log('☑️ Checkbox informations trouvée');
            
            checkboxInformations.addEventListener('change', function() {
                const isChecked = this.checked;
                
                // CSS classes pour compatibilité
                if (isChecked) {
                    document.body.classList.remove('checkbox-off');
                    userSection?.classList.remove('hidden');
                } else {
                    document.body.classList.add('checkbox-off');  
                    userSection?.classList.add('hidden');
                }
                
                console.log('☑️ Informations utilisateur:', isChecked ? 'affichées' : 'masquées');
            });
            
            // État initial
            const isChecked = checkboxInformations.checked;
            if (!isChecked) {
                document.body.classList.add('checkbox-off');
                userSection?.classList.add('hidden');
            }
        }
        
        // ===================================================================
        // FIX 4: EXTENSION GETCONFIGURATION GLOBALE
        // ===================================================================
        
        // Override de la fonction getConfiguration si elle existe
        if (window.configurator && typeof window.configurator.getConfiguration === 'function') {
            const originalGetConfig = window.configurator.getConfiguration;
            
            window.configurator.getConfiguration = function() {
                const config = originalGetConfig.call(this);
                
                // Ajouter logo verso
                if (window.nfcConfiguratorState?.logoVerso?.dataUrl) {
                    config.logoVerso = window.nfcConfiguratorState.logoVerso;
                    console.log('📷 Logo verso ajouté à la configuration');
                }
                
                // Ajouter checkbox état
                if (checkboxInformations) {
                    config.showUserInfo = checkboxInformations.checked;
                }
                
                // S'assurer que noms sont optionnels
                if (config.user) {
                    config.user.firstName = (config.user.firstName || '').trim();
                    config.user.lastName = (config.user.lastName || '').trim();
                }
                
                console.log('📦 Configuration finale:', config);
                return config;
            };
        }
        
        // ===================================================================
        // DEBUG ET ÉTAT GLOBAL
        // ===================================================================
        
        // Exposer état global
        window.nfcConfiguratorState = window.nfcConfiguratorState || {};
        
        // Fonction debug
        window.debugConfiguratorFix = function() {
            console.log('🔍 Debug configurateur fix:', {
                logoVerso: window.nfcConfiguratorState?.logoVerso || null,
                checkboxState: checkboxInformations?.checked || false,
                cartButtonDisabled: addToCartBtn?.disabled || false,
                elements: {
                    logoVersoInput: !!logoVersoInput,
                    logoVersoUploadZone: !!logoVersoUploadZone,
                    addToCartBtn: !!addToCartBtn,
                    checkboxInformations: !!checkboxInformations
                }
            });
        };
        
        // Test automatique
        setTimeout(function() {
            console.log('🧪 Test automatique configurateur fix...');
            window.debugConfiguratorFix();
            
            // Test click upload zone
            if (logoVersoUploadZone) {
                console.log('✅ Zone upload logo verso prête');
            }
            
            // Test bouton panier
            if (addToCartBtn && !addToCartBtn.disabled) {
                console.log('✅ Bouton panier activé');
            } else {
                console.warn('❌ Problème bouton panier');
            }
            
        }, 1000);
        
        console.log('✅ Fix configurateur initialisé avec succès !');
    }
    
})();