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
            if (!this.isLoaded) {
                throw new Error('HTML2Canvas non initialisé. Appelez init() d\'abord.');
            }

            console.log('📸 Génération screenshot...');
            
            // Trouver la zone d'aperçu
            const previewElement = document.querySelector(this.previewSelector);
            if (!previewElement) {
                console.error('❌ Zone d\'aperçu non trouvée:', this.previewSelector);
                throw new Error(`Zone d'aperçu configurateur non trouvée (${this.previewSelector})`);
            }

            console.log('✅ Zone d\'aperçu trouvée:', previewElement);

            try {
                // Configuration optimisée pour capture NFC
                const canvas = await html2canvas(previewElement, {
                    backgroundColor: '#ffffff',
                    scale: 2, // Haute résolution pour impression
                    useCORS: true,
                    allowTaint: false,
                    logging: false,
                    width: previewElement.scrollWidth,
                    height: previewElement.scrollHeight,
                    // Options spécifiques pour capture propre
                    removeContainer: false,
                    foreignObjectRendering: false,
                    imageTimeout: 15000
                });

                const dataUrl = canvas.toDataURL('image/png', 1.0);
                console.log('✅ Screenshot généré:', dataUrl.length, 'caractères');
                
                return dataUrl;
            } catch (error) {
                console.error('❌ Erreur génération screenshot:', error);
                throw new Error(`Erreur capture: ${error.message}`);
            }
        }

        /**
         * Génère une miniature optimisée pour l'affichage
         */
        async generateThumbnail(maxWidth = 300) {
            console.log('🖼️ Génération thumbnail...');
            
            try {
                // Récupérer le screenshot full
                const fullScreenshot = await this.generateScreenshot();
                
                return new Promise((resolve, reject) => {
                    const img = new Image();
                    img.onload = () => {
                        try {
                            // Créer canvas pour redimensionnement
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            
                            // Calculer dimensions conservant les proportions
                            const scale = maxWidth / img.width;
                            canvas.width = maxWidth;
                            canvas.height = img.height * scale;
                            
                            // Dessiner image redimensionnée
                            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                            
                            // Qualité optimisée pour web
                            const thumbnail = canvas.toDataURL('image/png', 0.8);
                            console.log('✅ Thumbnail généré:', thumbnail.length, 'caractères');
                            
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