/**
 * Module de capture HTML2Canvas pour configurateur NFC France
 * Capture la vraie zone d'aperçu pour créer des screenshots fidèles
 */

if (typeof window.NFCScreenshotCapture === 'undefined') {
    window.NFCScreenshotCapture = class NFCScreenshotCapture {
        constructor(configurator) {
            this.configurator = configurator;
            this.previewSelector = '.preview-column'; // Zone d'aperçu à capturer
            this.isLoaded = false;

            console.log('📸 NFCScreenshotCapture initialisé');
        }

        /**
         * Initialisation - Charge html2canvas si nécessaire
         */
        async init() {
            console.log('🔄 Chargement HTML2Canvas...');

            try {
                // Vérifier si html2canvas est déjà chargé
                if (typeof html2canvas === 'undefined') {
                    await this.loadHtml2Canvas();
                }

                this.isLoaded = true;
                console.log('✅ HTML2Canvas prêt pour capture');
            } catch (error) {
                console.error('❌ Erreur chargement HTML2Canvas:', error);
                throw error;
            }
        }

        /**
         * Charge html2canvas depuis CDN
         */
        async loadHtml2Canvas() {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                script.onload = () => {
                    console.log('📦 HTML2Canvas chargé depuis CDN');
                    resolve();
                };
                script.onerror = () => {
                    console.error('❌ Erreur chargement HTML2Canvas CDN');
                    reject(new Error('Impossible de charger HTML2Canvas'));
                };
                document.head.appendChild(script);
            });
        }

        /**
         * Génère un screenshot haute résolution de la zone d'aperçu
         */
        async generateScreenshot() {
            if (!this.initialized) {
                throw new Error('Module non initialisé. Appelez init() d\'abord.');
            }

            console.log('📸 Génération screenshot avec correctifs...');

            // Trouver la zone d'aperçu
            const previewElement = document.querySelector(this.previewSelector);
            if (!previewElement) {
                console.error('❌ Zone d\'aperçu non trouvée:', this.previewSelector);
                throw new Error(`Zone d'aperçu configurateur non trouvée (${this.previewSelector})`);
            }

            console.log('✅ Zone d\'aperçu trouvée:', previewElement);

            // ✨ NOUVEAU : Préparer les éléments avant capture
            const originalStates = this.prepareElementsForCapture();

            try {
                // Configuration améliorée pour capture NFC
                const canvas = await html2canvas(previewElement, {
                    backgroundColor: '#ffffff',
                    scale: 2, // Haute résolution pour impression
                    useCORS: true,
                    allowTaint: false,
                    logging: false,
                    width: previewElement.scrollWidth,
                    height: previewElement.scrollHeight,

                    // ✨ CORRECTIONS pour les transforms et images
                    ignoreElements: (element) => {
                        // Ignorer les éléments problématiques
                        return element.classList.contains('screenshot-ignore');
                    },

                    // Préserver les transformations
                    foreignObjectRendering: false,
                    imageTimeout: 15000,

                    // ✨ CORRECTION principale : onclone callback
                    onclone: (clonedDoc, element) => {
                        console.log('🔧 Correction du clone pour screenshot...');

                        // Corriger le logo verso spécifiquement
                        this.fixLogoVersoInClone(clonedDoc);

                        // Corriger autres éléments transformés si nécessaire
                        this.fixTransformedElements(clonedDoc);
                    }
                });

                const dataUrl = canvas.toDataURL('image/png', 1.0);
                console.log('✅ Screenshot généré avec corrections:', dataUrl.length, 'caractères');

                return dataUrl;
            } catch (error) {
                console.error('❌ Erreur génération screenshot:', error);
                throw new Error(`Erreur capture: ${error.message}`);
            } finally {
                // ✨ NOUVEAU : Restaurer les états originaux
                this.restoreElementsAfterCapture(originalStates);
            }
        }

        /**
         * ✨ NOUVEAU : Restaure les états après capture
         */
        restoreElementsAfterCapture(originalStates) {
            console.log('🔄 Restauration des éléments après capture...');

            originalStates.forEach(state => {
                if (state.property === 'style') {
                    state.element.style.cssText = state.value;
                } else {
                    state.element[state.property] = state.value;
                }
            });

            console.log('✅ États restaurés');
        }

        /**
         * ✨ NOUVEAU : Corrige le logo verso dans le clone HTML2Canvas
         */
        fixLogoVersoInClone(clonedDoc) {
            const clonedLogoVerso = clonedDoc.querySelector('#logoVersoImage');
            if (!clonedLogoVerso || clonedLogoVerso.classList.contains('d-none')) {
                return;
            }

            console.log('🔧 Correction logo verso dans clone...');

            // Obtenir le logo original pour référence
            const originalLogo = document.querySelector('#logoVersoImage');
            if (!originalLogo) return;

            // Copier les dimensions calculées du logo original
            const computedStyle = window.getComputedStyle(originalLogo);

            // Appliquer les styles corrigés au clone
            clonedLogoVerso.style.transform = 'none';
            clonedLogoVerso.style.width = computedStyle.width;
            clonedLogoVerso.style.height = computedStyle.height;
            clonedLogoVerso.style.objectFit = 'contain';
            clonedLogoVerso.style.objectPosition = 'left';
            clonedLogoVerso.style.maxWidth = 'none';
            clonedLogoVerso.style.maxHeight = 'none';

            console.log('✅ Logo verso corrigé dans clone:', {
                width: clonedLogoVerso.style.width,
                height: clonedLogoVerso.style.height
            });
        }

        /**
         * ✨ NOUVEAU : Corrige d'autres éléments transformés
         */
        fixTransformedElements(clonedDoc) {
            // Corriger d'autres transforms problématiques si nécessaire
            const transformedElements = clonedDoc.querySelectorAll('[style*="transform"]');

            transformedElements.forEach(element => {
                const transform = element.style.transform;

                // Éviter les transforms complexes qui cassent la mise en page
                if (transform.includes('matrix') || transform.includes('translate3d')) {
                    console.log('🔧 Simplification transform complexe:', transform);

                    // Simplifier ou supprimer les transforms problématiques
                    if (transform.includes('scale(')) {
                        const scaleMatch = transform.match(/scale\(([^)]+)\)/);
                        if (scaleMatch) {
                            element.style.transform = `scale(${scaleMatch[1]})`;
                        }
                    }
                }
            });
        }

        /**
         * ✨ NOUVEAU : Prépare les éléments avant capture
         */
        prepareElementsForCapture() {
            const originalStates = [];

            console.log('🔧 Préparation éléments pour capture...');

            // Sauvegarder et optimiser le logo verso
            const logoVersoImg = document.querySelector('#logoVersoImage');
            if (logoVersoImg && !logoVersoImg.classList.contains('d-none')) {
                const originalStyle = logoVersoImg.style.cssText;
                originalStates.push({
                    element: logoVersoImg,
                    property: 'style',
                    value: originalStyle
                });

                // ✨ CORRECTION : Remplacer transform par width/height explicites
                const computedStyle = window.getComputedStyle(logoVersoImg);
                const currentTransform = logoVersoImg.style.transform;

                if (currentTransform && currentTransform.includes('scale(')) {
                    // Extraire le facteur de scale
                    const scaleMatch = currentTransform.match(/scale\(([^)]+)\)/);
                    if (scaleMatch) {
                        const scaleFactor = parseFloat(scaleMatch[1]);

                        // Obtenir les dimensions naturelles
                        const naturalWidth = logoVersoImg.naturalWidth;
                        const naturalHeight = logoVersoImg.naturalHeight;

                        if (naturalWidth && naturalHeight) {
                            // Appliquer les dimensions calculées directement
                            logoVersoImg.style.transform = 'none';
                            logoVersoImg.style.width = Math.round(naturalWidth * scaleFactor) + 'px';
                            logoVersoImg.style.height = Math.round(naturalHeight * scaleFactor) + 'px';
                            logoVersoImg.style.objectFit = 'contain';
                            logoVersoImg.style.objectPosition = 'left';

                            console.log(`🔧 Logo verso: scale(${scaleFactor}) → ${logoVersoImg.style.width} x ${logoVersoImg.style.height}`);
                        }
                    }
                }
            }

            // Sauvegarder et optimiser d'autres éléments si nécessaire
            const transformedElements = document.querySelectorAll('[style*="transform"]');
            transformedElements.forEach(element => {
                if (element !== logoVersoImg) { // Éviter double traitement
                    const originalStyle = element.style.cssText;
                    originalStates.push({
                        element: element,
                        property: 'style',
                        value: originalStyle
                    });
                }
            });

            return originalStates;
        }

        /**
 * ✨ AMÉLIORATION : Génération thumbnail optimisée
 */
        async generateThumbnail(maxWidth = 300) {
            console.log('🖼️ Génération thumbnail optimisée...');

            try {
                // Récupérer le screenshot full corrigé
                const fullScreenshot = await this.generateScreenshot();

                return new Promise((resolve, reject) => {
                    const img = new Image();
                    img.onload = () => {
                        try {
                            // Créer canvas pour redimensionnement
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');

                            // ✨ AMÉLIORATION : Préserver les proportions exactes
                            const aspectRatio = img.height / img.width;
                            canvas.width = maxWidth;
                            canvas.height = Math.round(maxWidth * aspectRatio);

                            // ✨ AMÉLIORATION : Interpolation haute qualité
                            ctx.imageSmoothingEnabled = true;
                            ctx.imageSmoothingQuality = 'high';

                            // Dessiner image redimensionnée
                            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                            // Qualité optimisée pour web
                            const thumbnail = canvas.toDataURL('image/png', 0.9);
                            console.log('✅ Thumbnail optimisé généré:', {
                                dimensions: `${canvas.width}x${canvas.height}`,
                                size: `${thumbnail.length} caractères`
                            });

                            resolve(thumbnail);
                        } catch (error) {
                            console.error('❌ Erreur création thumbnail:', error);
                            reject(error);
                        }
                    };

                    img.onerror = () => {
                        console.error('❌ Erreur chargement image pour thumbnail');
                        reject(new Error('Impossible de charger l\'image pour thumbnail'));
                    };

                    img.src = fullScreenshot;
                });
            } catch (error) {
                console.error('❌ Erreur génération thumbnail:', error);
                throw error;
            }
        }

        /**
         * Génère les deux formats (full + thumbnail) en une fois
         */
        async generateBothFormats(thumbnailWidth = 300) {
            console.log('📸 Génération screenshot complet (full + thumbnail)...');

            try {
                const full = await this.generateScreenshot();
                const thumbnail = await this.generateThumbnail(thumbnailWidth);

                const result = {
                    full: full,
                    thumbnail: thumbnail,
                    generated_at: new Date().toISOString(),
                    dimensions: {
                        full: this.getImageDimensions(full),
                        thumbnail: this.getImageDimensions(thumbnail)
                    }
                };

                console.log('✅ Screenshot complet généré:', result);
                return result;
            } catch (error) {
                console.error('❌ Erreur génération complète:', error);
                throw error;
            }
        }

        /**
         * Utilitaire pour récupérer les dimensions d'une image base64
         */
        getImageDimensions(base64Data) {
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    resolve({
                        width: img.width,
                        height: img.height
                    });
                };
                img.src = base64Data;
            });
        }

        /**
         * Méthode de test pour vérifier que tout fonctionne
         */
        async testCapture() {
            console.log('🧪 Test de capture...');

            try {
                await this.init();
                const screenshot = await this.generateScreenshot();
                const thumbnail = await this.generateThumbnail();

                console.log('✅ Test réussi!');
                console.log('- Screenshot:', screenshot.length, 'caractères');
                console.log('- Thumbnail:', thumbnail.length, 'caractères');

                return {
                    success: true,
                    full: screenshot,
                    thumbnail: thumbnail
                };
            } catch (error) {
                console.error('❌ Test échoué:', error);
                return {
                    success: false,
                    error: error.message
                };
            }
        }
    };

    console.log('📦 Module NFCScreenshotCapture défini');
}