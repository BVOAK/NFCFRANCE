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
                logoVerso: null,
                showUserInfo: true,
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
                // Bind les événements
                this.bindEvents();
                // État initial
                this.setInitialState();

            } catch (error) {
                console.error('❌ Erreur initialisation configurateur:', error);
                this.showError('Erreur lors du chargement du configurateur: ' + error.message);
            }

            // Screenshot HTML2Canvas
            console.log('🔄 Initialisation module screenshot HTML2Canvas...');
            try {
                if (typeof window.NFCScreenshotCapture === 'undefined') {
                    throw new Error('Module NFCScreenshotCapture non trouvé');
                }

                this.screenshotCapture = new window.NFCScreenshotCapture(this);
                await this.screenshotCapture.init();
                console.log('✅ Module screenshot HTML2Canvas prêt');
            } catch (error) {
                console.error('⚠️ Erreur init screenshot:', error);
                this.screenshotCapture = null; // Continuer sans screenshot
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

                // Upload logo verso
                logoVersoArea: document.getElementById('logoVersoArea'),
                logoVersoPlaceholder: document.getElementById('logoVersoPlaceholder'),
                logoVersoImage: document.getElementById('logoVersoImage'),
                logoVersoUploadZone: document.getElementById('logoVersoUploadZone'),
                logoVersoInput: document.getElementById('logoVersoInput'),

                // Contrôles logo verso
                logoVersoScale: document.getElementById('logoVersoScale'),
                logoVersoRemoveBtn: document.getElementById('removeLogoVersoBtn'),

                // QR Code
                qrCode: document.getElementById('qrCode'),

                // Checkbox informations utilisateur
                checkboxInformations: document.getElementById('checkboxInformations'),
                userSection: document.querySelector('.card-preview.verso .user-section'),

                // Affichage noms verso
                contactName: document.querySelector('.contact-name') || document.getElementById('contactName'),
                userFirstName: document.querySelector('.user-firstname'),
                userLastName: document.querySelector('.user-lastname'),

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

        validateConfiguration() {
            let isValid = true;
            let errors = [];

            // ========================================
            // SEULES VALIDATIONS OBLIGATOIRES :
            // ========================================

            // 1. Couleur sélectionnée (essentiel pour le produit)
            if (!this.state.selectedColor) {
                isValid = false;
                errors.push('Veuillez choisir une couleur de carte');
            }

            // 2. Variation WooCommerce existe (technique)
            if (!this.state.selectedVariation || !this.state.selectedVariation.id) {
                isValid = false;
                errors.push('Erreur technique: variation produit non trouvée');
            }

            // ========================================
            // VALIDATIONS OPTIONNELLES (si données présentes) :
            // ========================================

            // Si nom renseigné, vérifier qu'il est valide
            if (this.state.userInfo.firstName || this.state.userInfo.lastName) {
                const namePattern = /^[a-zA-ZÀ-ÿ\s\-'\.]*$/; // Caractères autorisés + vide

                if (this.state.userInfo.firstName && !namePattern.test(this.state.userInfo.firstName)) {
                    isValid = false;
                    errors.push('Caractères non autorisés dans le prénom');
                }

                if (this.state.userInfo.lastName && !namePattern.test(this.state.userInfo.lastName)) {
                    isValid = false;
                    errors.push('Caractères non autorisés dans le nom');
                }
            }

            // Si logo verso uploadé, vérifier cohérence
            if (this.state.logoVerso) {
                if (!this.state.logoVerso.data || !this.state.logoVerso.name) {
                    isValid = false;
                    errors.push('Problème avec le logo verso');
                }

                const scale = this.state.logoVerso.scale || 100;
                if (scale < 10 || scale > 200) {
                    isValid = false;
                    errors.push('Taille du logo verso invalide (10-200%)');
                }
            }

            // Si image recto uploadée, vérifier cohérence  
            if (this.state.image) {
                if (!this.state.image.data || !this.state.image.name) {
                    isValid = false;
                    errors.push('Problème avec l\'image recto');
                }
            }

            // Mettre à jour l'état
            this.state.isValid = isValid;

            // Debug
            if (!isValid) {
                console.log('❌ Configuration invalide:', errors);
            } else {
                console.log('✅ Configuration valide');
            }

            return { isValid, errors };
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
                    this.state.userInfo.firstName = e.target.value;
                    this.updateUserDisplays();
                });
            }

            if (this.elements.lastNameInput) {
                this.elements.lastNameInput.addEventListener('input', (e) => {
                    this.state.userInfo.lastName = e.target.value;
                    this.updateUserDisplays();
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

            // Upload logo verso
            if (this.elements.logoVersoUploadZone) {
                this.elements.logoVersoUploadZone.addEventListener('click', () => {
                    this.elements.logoVersoInput.click();
                });

                this.elements.logoVersoUploadZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    this.elements.logoVersoUploadZone.classList.add('border-primary');
                });

                this.elements.logoVersoUploadZone.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    this.elements.logoVersoUploadZone.classList.remove('border-primary');
                });

                this.elements.logoVersoUploadZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    this.elements.logoVersoUploadZone.classList.remove('border-primary');
                    this.handleLogoVersoImageDrop(e);
                });
            }

            if (this.elements.logoVersoInput) {
                this.elements.logoVersoInput.addEventListener('change', (e) => {
                    this.handleLogoVersoImageSelect(e);
                });
            }

            // Contrôles logo verso (position/taille)
            if (this.elements.logoVersoScale) {
                this.elements.logoVersoScale.addEventListener('input', (e) => {
                    console.log('🎚️ Slider taille logo verso:', e.target.value);
                    this.updateLogoVersoTransform();
                });
            }

            if (this.elements.logoVersoRemoveBtn) {
                this.elements.logoVersoRemoveBtn.addEventListener('click', () => {
                    this.removeLogoVerso();
                });
            }

            // ✨ Checkbox "Afficher mes informations"
            if (this.elements.checkboxInformations) {
                this.elements.checkboxInformations.addEventListener('change', (e) => {
                    console.log('📋 Toggle infos utilisateur:', e.target.checked);
                    this.toggleUserInfo(e.target.checked);
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

            // Checkbox informations cochée par défaut
            if (this.elements.checkboxInformations) {
                this.elements.checkboxInformations.checked = true;
                this.state.showUserInfo = true;
            }

            this.updateUserDisplays();
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
            //this.validateConfiguration();

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

            this.updateLogoVersoColor(color);
        }

        /**
         * Met à jour les informations utilisateur
         */
        updateUserInfo(field, value) {
            this.state.userInfo[field] = value.trim();
            this.updateCardUserInfo();
            //this.validateConfiguration();
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
                this.processImageFile(file, 'recto');
            }
        }

        /**
         * Gère le drop d'image
         */
        handleImageDrop(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.processImageFile(files[0], 'recto');
            }
        }

        /**
         * Traite le fichier image
         */
        processImageFile(file, type = 'recto') {
            const validation = this.validateImageFile(file);
            if (!validation.valid) {
                this.showError(validation.message);
                return;
            }

            console.log(`📷 Traitement image ${type}: ${file.name}`);

            const reader = new FileReader();
            reader.onload = (e) => {
                this.setImage(e.target.result, file.name, type);
            };
            reader.onerror = () => {
                this.showError(`Erreur lors de la lecture du fichier ${type}`);
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


        setImage(dataUrl, fileName, type = 'recto') {
            console.log(`📷 setImage appelé pour ${type}:`, fileName);

            // Configuration selon le type
            const isVerso = type === 'verso';
            const stateKey = isVerso ? 'logoVerso' : 'image';
            const defaultScale = isVerso ? 100 : 25;

            // Stocker dans le bon endroit du state
            this.state[stateKey] = {
                data: dataUrl,
                name: fileName,
                scale: defaultScale,
                x: 0,
                y: 0
            };

            if (isVerso) {
                // Affichage spécifique verso
                this.displayLogoVersoOnCard();
                this.revealLogoVersoControls(fileName);
            } else {
                // Affichage recto (existant)
                this.displayImageOnCard();
                this.revealRectoControls(fileName);
            }

            this.validateConfiguration();

            console.log(`✅ setImage terminé pour ${type} - base64 stocké`);
        }


        displayLogoVersoOnCard() {
            if (!this.state.logoVerso || !this.elements.logoVersoImage) {
                return;
            }

            // Masquer le placeholder
            if (this.elements.logoVersoPlaceholder) {
                this.elements.logoVersoPlaceholder.classList.add('d-none');
            }

            // Configurer l'image
            this.elements.logoVersoImage.src = this.state.logoVerso.data;
            this.elements.logoVersoImage.classList.remove('d-none');
            this.elements.logoVersoImage.style.opacity = '1';

            // Appliquer les transformations
            this.updateLogoVersoTransform();
        }


        revealLogoVersoControls(fileName) {
            console.log('🔧 Révélation des contrôles logo verso...');

            // Révéler bouton supprimer
            if (this.elements.logoVersoRemoveBtn) {
                this.elements.logoVersoRemoveBtn.classList.remove('d-none');
                this.elements.logoVersoRemoveBtn.classList.add('d-block');
            }

            // Initialiser le slider à 100%
            if (this.elements.logoVersoScale) {
                this.elements.logoVersoScale.value = 100;
            }

            // Mettre à jour le texte de l'upload zone
            if (this.elements.logoVersoUploadZone) {
                const uploadText = this.elements.logoVersoUploadZone.querySelector('.upload-text');
                if (uploadText) {
                    uploadText.textContent = fileName;
                }
            }

            console.log('✅ Contrôles logo verso révélés');
        }

        revealRectoControls(fileName) {
            console.log('🔧 Révélation des contrôles Bootstrap...');

            if (this.elements.removeImageBtn) {
                this.elements.removeImageBtn.classList.remove('d-none');
                this.elements.removeImageBtn.classList.add('d-block');
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

            console.log('✅ Contrôles recto révélés');
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

            //this.validateConfiguration();

            console.log('✅ Image supprimée');
        }

        /**
         * Gestion du drop d'image pour logo verso
         */
        handleLogoVersoImageDrop(e) {
            const files = Array.from(e.dataTransfer.files);
            const file = files.find(f => f.type.startsWith('image/'));

            if (file) {
                this.processLogoVersoImage(file);
            }
        }


        handleLogoVersoImageSelect(e) {
            const file = e.target.files[0];
            if (file) {
                this.processImageFile(file, 'verso');
                e.target.value = ''; // Reset pour permettre re-sélection
            }
        }

        handleLogoVersoImageDrop(e) {
            const files = Array.from(e.dataTransfer.files);
            const file = files.find(f => f.type.startsWith('image/'));
            if (file) {
                this.processImageFile(file, 'verso');
            }
        }

        /**
         * Mise à jour transformation logo verso
         */
        updateLogoVersoTransform() {
            if (!this.state.logoVerso || !this.elements.logoVersoImage) return;

            const scale = this.elements.logoVersoScale ?
                this.elements.logoVersoScale.value : 100;

            this.state.logoVerso.scale = scale;

            const transform = `scale(${scale / 100})`;
            this.elements.logoVersoImage.style.transform = transform;

            // ✅ CORRECTION 2: Mettre à jour l'affichage du pourcentage
            const scaleValueElement = document.getElementById('logoVersoScaleValue');
            if (scaleValueElement) {
                scaleValueElement.textContent = scale + '%';
            }

            console.log('🔄 Logo verso transform:', { scale, transform });
        }


        /**
         * Suppression logo verso
         */
        removeLogoVerso() {
            console.log('🗑️ Suppression logo verso');

            // Libérer URL temporaire
            if (this.state.logoVerso?.url) {
                URL.revokeObjectURL(this.state.logoVerso.url);
            }

            // Reset état
            this.state.logoVerso = null;

            // Masquer image
            if (this.elements.logoVersoImage) {
                this.elements.logoVersoImage.classList.add('d-none');
                this.elements.logoVersoImage.src = '';
            }

            // Afficher placeholder
            if (this.elements.logoVersoPlaceholder) {
                this.elements.logoVersoPlaceholder.classList.remove('d-none');
            }

            // Masquer bouton supprimer
            if (this.elements.logoVersoRemoveBtn) {
                this.elements.logoVersoRemoveBtn.classList.remove('d-block');
                this.elements.logoVersoRemoveBtn.classList.add('d-none');
            }

            // Reset sliders
            if (this.elements.logoVersoScale) this.elements.logoVersoScale.value = 100;

            // Reset input file
            if (this.elements.logoVersoInput) {
                this.elements.logoVersoInput.value = '';
            }

            console.log('✅ Logo verso supprimé');
        }

        /**
         * ✨ NOUVELLE : Met à jour la couleur du logo verso selon la carte
         */
        updateLogoVersoColor(color) {
            if (!this.elements.logoVersoArea) return;

            const placeholder = this.elements.logoVersoPlaceholder;
            if (placeholder) {
                if (color === 'noir') {
                    placeholder.style.borderColor = 'rgba(255,255,255,0.3)';
                    placeholder.style.background = 'rgba(0, 0, 0, 0.1)';
                } else {
                    placeholder.style.borderColor = 'rgba(0,0,0,0.125)';
                    placeholder.style.background = 'rgba(255, 255, 255, 0.1)';
                }
            }
        }


        /**
         * Toggle affichage informations utilisateur
         */
        toggleUserInfo(show) {
            console.log('👤 Toggle user info:', show);

            this.state.showUserInfo = show;

            if (this.elements.userSection) {
                if (show) {
                    // ✨ AFFICHER la section avec placeholders si vide
                    this.elements.userSection.classList.remove('hidden');

                    // Forcer l'affichage des placeholders si les champs sont vides
                    this.updateUserDisplays();

                } else {
                    // MASQUER complètement la section
                    this.elements.userSection.classList.add('hidden');
                }
            }

            // Alternative pour navigateurs supportant :has()
            document.body.classList.toggle('checkbox-off', !show);
        }

        /**
         * ✨ NOUVELLE : Met à jour tous les affichages utilisateur (recto + verso)
         */
        updateUserDisplays() {
            const { firstName, lastName } = this.state.userInfo;
            const fullName = `${firstName} ${lastName}`.trim();

            // Mise à jour recto (inchangé)
            if (this.elements.displayName) {
                this.elements.displayName.textContent = fullName || 'Votre nom';
            }

            // ✨ NOUVEAU : Mise à jour verso AVEC logique checkbox
            if (this.state.showUserInfo) {
                // Checkbox COCHÉE = Afficher avec placeholders
                if (this.elements.contactName) {
                    this.elements.contactName.textContent = fullName || 'Votre nom';
                }

                if (this.elements.userFirstName) {
                    this.elements.userFirstName.textContent = firstName || 'Prénom';
                }

                if (this.elements.userLastName) {
                    this.elements.userLastName.textContent = lastName || 'Nom';
                }

            } else {
                // Checkbox DÉCOCHÉE = Masquer (géré par CSS via .hidden)
                // Les textes restent mais sont cachés visuellement
            }
        }


        /**
         * NOUVEAU : Ajoute au panier avec screenshot
         */
        async addToCart() {
            if (!this.state.isValid) {
                // Forcer une validation avant d'échouer
                const validation = this.validateConfiguration();
                if (!validation.isValid) {
                    this.showError('Veuillez corriger: ' + validation.errors.join(', '));
                    return;
                }
                // Si validation OK maintenant, continuer
                this.state.isValid = true;
            }

            console.log('🛒 Ajout au panier avec screenshot...');
            this.showLoading(true);

            try {
                // GÉNÉRER LE SCREENSHOT
                let screenshotData = null;

                if (this.screenshotCapture) {
                    try {
                        console.log('📸 Génération screenshot HTML2Canvas...');
                        screenshotData = await this.screenshotCapture.generateBothFormats(300);
                        console.log('✅ Screenshot HTML2Canvas généré');
                    } catch (screenshotError) {
                        console.error('⚠️ Erreur screenshot HTML2Canvas:', screenshotError);
                        // Continuer sans screenshot plutôt que planter
                    }
                } else {
                    console.warn('⚠️ Module screenshot non disponible');
                }

                // Préparer les données (avec ou sans screenshot)
                const configData = {
                    variation_id: this.state.selectedVariation.id,
                    color: this.state.selectedColor,
                    user: this.state.userInfo,
                    image: this.state.image && this.state.image.data ? this.state.image : null,
                    logoVerso: this.state.logoVerso,
                    showUserInfo: this.state.showUserInfo,
                    timestamp: Date.now()
                };

                // 🔍 DEBUG : Vérifier les données avant envoi
                console.log('🛒 ConfigData debug:', {
                    hasImageRecto: !!configData.image?.data,
                    hasLogoVerso: !!configData.logoVerso?.data,
                    hasScreenshot: !!screenshotData,
                    imageRectoDetails: configData.image ? {
                        name: configData.image.name,
                        dataLength: configData.image.data?.length || 0
                    } : 'null',
                    logoVersoDetails: configData.logoVerso ? {
                        name: configData.logoVerso.name,
                        hasData: !!configData.logoVerso.data,
                        dataLength: configData.logoVerso.data?.length || 0
                    } : 'null',
                    screenshotDetails: screenshotData ? {
                        hasFull: !!screenshotData.full,
                        hasThumbnail: !!screenshotData.thumbnail,
                        fullLength: screenshotData.full?.length || 0,
                        thumbnailLength: screenshotData.thumbnail?.length || 0
                    } : 'null'
                });

                // Ajouter screenshot seulement s'il existe
                if (screenshotData) {
                    configData.screenshot = {
                        full: screenshotData.full,
                        thumbnail: screenshotData.thumbnail,
                        generated_at: screenshotData.generated_at
                    };
                }

                console.log('📦 Données config préparées (avec screenshot:', !!screenshotData, ')');


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
 * 🧪 Méthode de test pour vérifier le screenshot
 * À appeler depuis la console : configurator.testScreenshot()
 */
        async testScreenshot() {
            if (!this.screenshotCapture) {
                console.error('❌ Module screenshot non initialisé');
                return;
            }

            try {
                console.log('🧪 Test screenshot depuis configurateur...');
                const result = await this.screenshotCapture.testCapture();

                if (result.success) {
                    console.log('✅ Test réussi!');
                    // Afficher les images dans la console pour debug
                    console.log('🖼️ Aperçu screenshot:');
                    console.log('Full:', result.full.substring(0, 100) + '...');
                    console.log('Thumbnail:', result.thumbnail.substring(0, 100) + '...');
                } else {
                    console.error('❌ Test échoué:', result.error);
                }

                return result;
            } catch (error) {
                console.error('❌ Erreur test:', error);
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
        window.nfcConfigurator = new window.NFCConfigurator();
        console.log('✅ Configurateur avec screenshot initialisé');
    } catch (error) {
        console.error('❌ Erreur:', error);
        alert('Erreur fatale: ' + error.message);
    }
});