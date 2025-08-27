/**
 * Configurateur NFC - Adapté à la nouvelle structure Bootstrap
 * Compatible avec la structure modifiée de page-configurateur.php
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

            // L'imageAdjustStep est maintenant toujours visible selon ta structure
            // On révèle juste les contrôles et le bouton supprimer
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
         * Ajoute au panier
         */
        async addToCart() {
            if (!this.state.isValid) {
                this.showError('Veuillez remplir tous les champs requis');
                return;
            }

            console.log('🛒 Ajout au panier...');
            this.showLoading(true);

            try {
                const configData = {
                    variation_id: this.state.selectedVariation.id,
                    color: this.state.selectedColor,
                    user: this.state.userInfo,
                    image: this.state.image,
                    timestamp: Date.now()
                };

                const response = await this.ajaxCall('nfc_add_to_cart', {
                    product_id: this.productId,
                    variation_id: this.state.selectedVariation.id,
                    nfc_config: JSON.stringify(configData),
                    nonce: this.config.nonce
                });

                if (response.success) {
                    console.log('✅ Ajouté au panier avec succès');
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
    console.log('🔥 Initialisation configurateur...');

    try {
        window.nfcConfigurator = new window.NFCConfigurator();
        console.log('✅ Configurateur initialisé');
    } catch (error) {
        console.error('❌ Erreur:', error);
        alert('Erreur fatale: ' + error.message);
    }
});