/**
 * Admin Downloads - Gestion des boutons de téléchargement dans l'admin commandes
 * Gère les téléchargements de screenshots HTML2Canvas et logos
 */

(function($) {
    'use strict';

    const NFC_AdminDownloads = {
        
        init() {
            console.log('🔧 NFC Admin Downloads initialisé');
            console.log('📝 nfcAdminAjax config:', nfcAdminAjax);
            console.log('📍 Page actuelle:', window.location.href);
            console.log('🎯 jQuery version:', $.fn.jquery);
            this.bindEvents();
        },

        bindEvents() {
            // Bouton téléchargement screenshot
            $(document).on('click', '.nfc-download-screenshot', this.downloadScreenshot.bind(this));
            
            // Boutons téléchargement logos
            $(document).on('click', '.nfc-download-logo-recto', this.downloadLogoRecto.bind(this));
            $(document).on('click', '.nfc-download-logo-verso', this.downloadLogoVerso.bind(this));
            
            // ✨ FIX: Event listener pour les thumbnails screenshot
            $(document).on('click', '.nfc-screenshot-thumbnail', (e) => {
                const fullScreenshot = $(e.currentTarget).data('full-screenshot');
                if (fullScreenshot) {
                    this.viewScreenshot(fullScreenshot);
                }
            });
            
            // Modal screenshot (créer la fonction globale)
            window.nfcAdminViewScreenshot = this.viewScreenshot.bind(this);
        },

        /**
         * Téléchargement screenshot HTML2Canvas
         */
        downloadScreenshot(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const orderId = $button.data('order-id');
            const itemId = $button.data('item-id');
            
            if (!orderId || !itemId) {
                alert('❌ Données manquantes pour le téléchargement');
                return;
            }
            
            console.log('📸 Début téléchargement screenshot:', {orderId, itemId});
            
            // Feedback visuel
            const originalText = $button.text();
            $button.text('⏳ Téléchargement...');
            $button.prop('disabled', true);
            
            // ✅ FIX: Utiliser POST au lieu de GET pour WordPress AJAX
            $.ajax({
                url: nfcAdminAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'nfc_download_screenshot',
                    order_id: orderId,
                    item_id: itemId,
                    _wpnonce: nfcAdminAjax.nonce
                },
                xhrFields: {
                    responseType: 'blob' // Important pour les fichiers binaires
                },
                success: (data, textStatus, xhr) => {
                    console.log('✅ Screenshot reçu, déclenchement téléchargement...');
                    
                    // Créer un blob URL et déclencher le téléchargement
                    const blob = new Blob([data], {type: 'image/png'});
                    const url = window.URL.createObjectURL(blob);
                    
                    // Nom de fichier depuis les headers si disponible
                    let filename = 'nfc-screenshot.png';
                    const contentDisposition = xhr.getResponseHeader('Content-Disposition');
                    if (contentDisposition) {
                        const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                        if (filenameMatch) {
                            filename = filenameMatch[1];
                        }
                    }
                    
                    // Déclencher téléchargement
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = filename;
                    link.style.display = 'none';
                    
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // Nettoyer l'URL blob
                    window.URL.revokeObjectURL(url);
                    
                    console.log('✅ Screenshot téléchargé avec succès');
                },
                error: (xhr, textStatus, errorThrown) => {
                    console.error('❌ Erreur téléchargement screenshot:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        textStatus,
                        errorThrown
                    });
                    
                    // Essayer de lire le message d'erreur
                    let errorMessage = `Erreur ${xhr.status}`;
                    if (xhr.responseText) {
                        errorMessage += `: ${xhr.responseText.substring(0, 100)}`;
                    }
                    
                    alert(`❌ Erreur lors du téléchargement du screenshot\n${errorMessage}`);
                },
                complete: () => {
                    // Restaurer bouton
                    $button.text(originalText);
                    $button.prop('disabled', false);
                }
            });
        },

        /**
         * Téléchargement logo recto
         */
        downloadLogoRecto(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const orderId = $button.data('order-id');
            const itemId = $button.data('item-id');
            
            this.downloadLogo($button, orderId, itemId, 'recto');
        },

        /**
         * Téléchargement logo verso
         */
        downloadLogoVerso(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const orderId = $button.data('order-id');
            const itemId = $button.data('item-id');
            
            this.downloadLogo($button, orderId, itemId, 'verso');
        },

        /**
         * Téléchargement logos (générique)
         */
        downloadLogo($button, orderId, itemId, type) {
            if (!orderId || !itemId || !type) {
                alert('❌ Données manquantes pour le téléchargement');
                return;
            }
            
            // Feedback visuel
            const originalText = $button.text();
            $button.text('⏳ Téléchargement...');
            $button.prop('disabled', true);
            
            // Construire URL (adapter selon l'existant)
            const downloadUrl = `${nfcAdminAjax.ajax_url}?action=nfc_download_logo_${type}&order_id=${orderId}&item_id=${itemId}&_wpnonce=${nfcAdminAjax.nonce}`;
            
            // Déclencher téléchargement
            this.triggerDownload(downloadUrl, `nfc-logo-${type}-commande-${orderId}-item-${itemId}`)
                .then(() => {
                    console.log(`✅ Logo ${type} téléchargé avec succès`);
                })
                .catch((error) => {
                    console.error(`❌ Erreur téléchargement logo ${type}:`, error);
                    alert(`❌ Erreur lors du téléchargement du logo ${type}`);
                })
                .finally(() => {
                    // Restaurer bouton
                    $button.text(originalText);
                    $button.prop('disabled', false);
                });
        },

        /**
         * Déclenche un téléchargement de fichier
         */
        triggerDownload(url, fallbackFilename = 'nfc-download') {
            return new Promise((resolve, reject) => {
                try {
                    // Méthode 1: Lien invisible
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = fallbackFilename;
                    link.style.display = 'none';
                    
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // Délai pour laisser le temps au téléchargement
                    setTimeout(resolve, 500);
                    
                } catch (error) {
                    // Méthode 2: Fallback avec window.open
                    console.warn('Fallback vers window.open pour téléchargement');
                    const popup = window.open(url, '_blank');
                    
                    if (popup) {
                        // Fermer automatiquement après téléchargement
                        setTimeout(() => {
                            try {
                                popup.close();
                            } catch (e) {
                                // Ignore si impossible de fermer
                            }
                        }, 2000);
                        
                        resolve();
                    } else {
                        reject(new Error('Popup bloquée'));
                    }
                }
            });
        },

        /**
         * Modal pour visualiser screenshot en grand
         */
        viewScreenshot(imageData) {
            // Créer modal si n'existe pas
            let modal = document.getElementById('nfc-admin-screenshot-modal');
            
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'nfc-admin-screenshot-modal';
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.85);
                    z-index: 999999;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    box-sizing: border-box;
                `;
                
                const content = document.createElement('div');
                content.style.cssText = `
                    position: relative;
                    max-width: 90vw;
                    max-height: 90vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                `;
                
                const img = document.createElement('img');
                img.id = 'nfc-admin-screenshot-img';
                img.style.cssText = `
                    max-width: 100%;
                    max-height: 100%;
                    border: 3px solid #0040C1;
                    border-radius: 8px;
                    background: white;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                `;
                
                const closeBtn = document.createElement('button');
                closeBtn.innerHTML = '×';
                closeBtn.style.cssText = `
                    position: absolute;
                    top: -15px;
                    right: -15px;
                    width: 40px;
                    height: 40px;
                    border: none;
                    border-radius: 50%;
                    background: #ff4444;
                    color: white;
                    font-size: 24px;
                    font-weight: bold;
                    cursor: pointer;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                    transition: all 0.2s ease;
                `;
                
                closeBtn.onmouseover = () => closeBtn.style.background = '#ff6666';
                closeBtn.onmouseout = () => closeBtn.style.background = '#ff4444';
                closeBtn.onclick = () => {
                    modal.style.display = 'none';
                };
                
                content.appendChild(img);
                content.appendChild(closeBtn);
                modal.appendChild(content);
                document.body.appendChild(modal);
                
                // Fermeture par clic sur fond
                modal.onclick = (e) => {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                    }
                };
                
                // Fermeture par Echap
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && modal.style.display === 'flex') {
                        modal.style.display = 'none';
                    }
                });
            }
            
            // Afficher l'image
            const img = modal.querySelector('#nfc-admin-screenshot-img');
            img.src = imageData;
            modal.style.display = 'flex';
            
            console.log('📸 Modal screenshot affiché');
        }
    };

    // Initialisation au chargement
    $(document).ready(() => {
        NFC_AdminDownloads.init();
    });

})(jQuery);