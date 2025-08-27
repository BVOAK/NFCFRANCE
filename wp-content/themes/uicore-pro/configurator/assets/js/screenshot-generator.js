/**
 * Screenshot Generator pour Configurateur NFC
 * Génère des aperçus Canvas des cartes configurées
 */

if (typeof window.NFCScreenshotGenerator === 'undefined') {
    window.NFCScreenshotGenerator = class NFCScreenshotGenerator {
        constructor(configurator) {
            this.configurator = configurator;
            this.canvas = null;
            this.ctx = null;
            
            // Dimensions cartes (taille réelle)
            this.cardWidth = 450;
            this.cardHeight = 291;
            this.cardRadius = 16;
            
            console.log('📸 Screenshot Generator initialisé');
        }

        /**
         * Génère un screenshot de la configuration actuelle
         * @returns {Promise<string>} Base64 de l'image générée
         */
        async generateScreenshot() {
            try {
                console.log('📸 Génération screenshot...');
                
                // Créer canvas composite (2 cartes côte à côte)
                const compositeWidth = (this.cardWidth * 2) + 40; // 40px gap
                const compositeHeight = this.cardHeight + 40; // 20px padding top/bottom
                
                const canvas = document.createElement('canvas');
                canvas.width = compositeWidth;
                canvas.height = compositeHeight;
                const ctx = canvas.getContext('2d');
                
                // Fond transparent
                ctx.clearRect(0, 0, compositeWidth, compositeHeight);
                
                // Générer recto (gauche)
                const rectoImage = await this.generateCardImage('recto');
                ctx.drawImage(rectoImage, 20, 20);
                
                // Générer verso (droite) 
                const versoImage = await this.generateCardImage('verso');
                ctx.drawImage(versoImage, this.cardWidth + 40, 20);
                
                // Ajouter labels
                this.addLabels(ctx, compositeWidth);
                
                // Convertir en base64
                const screenshotBase64 = canvas.toDataURL('image/png', 1.0);
                
                console.log('✅ Screenshot généré');
                return screenshotBase64;
                
            } catch (error) {
                console.error('❌ Erreur génération screenshot:', error);
                throw error;
            }
        }

        /**
         * Génère l'image d'une face de carte
         * @param {string} side - 'recto' ou 'verso'
         * @returns {Promise<HTMLCanvasElement>} Canvas de la carte
         */
        async generateCardImage(side) {
            const canvas = document.createElement('canvas');
            canvas.width = this.cardWidth;
            canvas.height = this.cardHeight;
            const ctx = canvas.getContext('2d');
            
            // Fond de carte avec couleur
            this.drawCardBackground(ctx, side);
            
            if (side === 'recto') {
                await this.drawRectoContent(ctx);
            } else {
                await this.drawVersoContent(ctx);
            }
            
            return canvas;
        }

        /**
         * Dessine le fond de carte avec bordures arrondies
         */
        drawCardBackground(ctx, side) {
            const { selectedColor } = this.configurator.state;
            
            // Couleurs selon configuration
            const colors = {
                blanc: { bg: '#ffffff', border: '#e2e8f0', text: '#333333' },
                noir: { bg: '#1a1a1a', border: '#374151', text: '#ffffff' }
            };
            
            const color = colors[selectedColor] || colors.blanc;
            
            // Fond avec bordures arrondies
            ctx.save();
            this.roundRect(ctx, 0, 0, this.cardWidth, this.cardHeight, this.cardRadius);
            ctx.fillStyle = color.bg;
            ctx.fill();
            
            // Bordure
            ctx.strokeStyle = color.border;
            ctx.lineWidth = 2;
            ctx.stroke();
            ctx.restore();
            
            // Stocker couleur texte pour usage ultérieur
            ctx.textColor = color.text;
        }

        /**
         * Dessine le contenu du recto
         */
        async drawRectoContent(ctx) {
            const { image } = this.configurator.state;
            
            // Si image personnalisée, la dessiner
            if (image && image.data) {
                await this.drawCustomImage(ctx, image);
            } else {
                // Placeholder image
                this.drawImagePlaceholder(ctx);
            }
            
            // Symbole NFC
            this.drawNFCSymbol(ctx);
        }

        /**
         * Dessine l'image personnalisée avec transformations
         */
        async drawCustomImage(ctx, imageConfig) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => {
                    ctx.save();
                    
                    // Calculer transformations
                    const scale = (imageConfig.scale || 100) / 100;
                    const offsetX = (imageConfig.x || 0) * 2; // Ajustement pour canvas
                    const offsetY = (imageConfig.y || 0) * 2;
                    
                    // Centre de la carte
                    const centerX = this.cardWidth / 2;
                    const centerY = this.cardHeight / 2;
                    
                    // Transformer contexte
                    ctx.translate(centerX + offsetX, centerY + offsetY);
                    ctx.scale(scale, scale);
                    
                    // Dessiner image centrée
                    const drawWidth = Math.min(img.width, this.cardWidth * 0.8);
                    const drawHeight = (img.height * drawWidth) / img.width;
                    
                    ctx.drawImage(img, -drawWidth/2, -drawHeight/2, drawWidth, drawHeight);
                    
                    ctx.restore();
                    resolve();
                };
                img.onerror = reject;
                img.src = imageConfig.data;
            });
        }

        /**
         * Dessine le placeholder d'image
         */
        drawImagePlaceholder(ctx) {
            const centerX = this.cardWidth / 2;
            const centerY = this.cardHeight / 2;
            
            // Icône camera
            ctx.save();
            ctx.font = '48px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = ctx.textColor;
            ctx.globalAlpha = 0.3;
            ctx.fillText('📷', centerX, centerY - 10);
            
            // Texte
            ctx.font = '14px Arial';
            ctx.fillText('Votre image', centerX, centerY + 30);
            ctx.restore();
        }

        /**
         * Dessine le symbole NFC
         */
        drawNFCSymbol(ctx) {
            const symbolX = this.cardWidth - 50;
            const symbolY = this.cardHeight / 2;
            
            ctx.save();
            ctx.font = '24px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = ctx.textColor;
            ctx.globalAlpha = 0.8;
            
            // Utiliser symbole Unicode NFC ou texte
            ctx.fillText('NFC', symbolX, symbolY);
            ctx.restore();
        }

        /**
         * Dessine le contenu du verso
         */
        async drawVersoContent(ctx) {
            const { userInfo } = this.configurator.state;
            
            // Section utilisateur (gauche)
            this.drawUserInfo(ctx, userInfo);
            
            // Section QR Code (droite)
            await this.drawQRCode(ctx);
        }

        /**
         * Dessine les informations utilisateur
         */
        drawUserInfo(ctx, userInfo) {
            const { firstName, lastName } = userInfo;
            
            ctx.save();
            ctx.fillStyle = ctx.textColor;
            ctx.textAlign = 'left';
            
            // Prénom
            ctx.font = 'bold 20px Arial';
            ctx.fillText(firstName || 'Prénom', 40, this.cardHeight / 2 - 10);
            
            // Nom  
            ctx.font = 'bold 20px Arial';
            ctx.fillText(lastName || 'Nom', 40, this.cardHeight / 2 + 20);
            
            ctx.restore();
        }

        /**
         * Dessine le QR Code
         */
        async drawQRCode(ctx) {
            const qrSize = 100;
            const qrX = this.cardWidth - qrSize - 40;
            const qrY = (this.cardHeight - qrSize) / 2;
            
            // Fond QR
            ctx.save();
            ctx.fillStyle = ctx.textColor;
            ctx.globalAlpha = 0.1;
            ctx.fillRect(qrX, qrY, qrSize, qrSize);
            
            // Pattern QR simple
            ctx.globalAlpha = 1;
            ctx.fillStyle = ctx.textColor;
            
            // Coins QR
            const cornerSize = 20;
            // Coin haut-gauche
            ctx.fillRect(qrX + 5, qrY + 5, cornerSize, cornerSize);
            // Coin haut-droite  
            ctx.fillRect(qrX + qrSize - cornerSize - 5, qrY + 5, cornerSize, cornerSize);
            // Coin bas-gauche
            ctx.fillRect(qrX + 5, qrY + qrSize - cornerSize - 5, cornerSize, cornerSize);
            
            // Pattern central simplifié
            for (let i = 0; i < 8; i++) {
                for (let j = 0; j < 8; j++) {
                    if ((i + j) % 2 === 0) {
                        ctx.fillRect(qrX + 35 + i * 5, qrY + 35 + j * 5, 3, 3);
                    }
                }
            }
            
            ctx.restore();
        }

        /**
         * Ajoute les labels aux cartes
         */
        addLabels(ctx, compositeWidth) {
            ctx.save();
            ctx.font = 'bold 16px Arial';
            ctx.fillStyle = '#666666';
            ctx.textAlign = 'center';
            
            // Label "Recto"
            ctx.fillText('RECTO', this.cardWidth / 2 + 20, compositeHeight - 10);
            
            // Label "Verso" 
            ctx.fillText('VERSO', this.cardWidth + 40 + (this.cardWidth / 2), compositeHeight - 10);
            
            ctx.restore();
        }

        /**
         * Utilitaire pour dessiner rectangle avec bordures arrondies
         */
        roundRect(ctx, x, y, width, height, radius) {
            ctx.beginPath();
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + width - radius, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
            ctx.lineTo(x + width, y + height - radius);
            ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            ctx.lineTo(x + radius, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
        }

        /**
         * Génère un aperçu rapide pour le panier (miniature)
         */
        async generateThumbnail(maxWidth = 200) {
            const fullScreenshot = await this.generateScreenshot();
            
            // Créer canvas miniature
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            const img = new Image();
            return new Promise((resolve) => {
                img.onload = () => {
                    // Calculer dimensions proportionnelles
                    const scale = maxWidth / img.width;
                    canvas.width = maxWidth;
                    canvas.height = img.height * scale;
                    
                    // Dessiner miniature
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    
                    resolve(canvas.toDataURL('image/png', 0.8));
                };
                img.src = fullScreenshot;
            });
        }
    };
}