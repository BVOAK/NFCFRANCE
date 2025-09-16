<?php
/**
 * Template: Aperçu public vCard avec iframe - Dashboard NFC
 * 
 * Fichier: templates/dashboard/preview/preview.php
 * Version iframe simplifiée avec simulation mobile
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables globales disponibles depuis le routing
global $nfc_vcard, $nfc_current_page;
$vcard = $nfc_vcard;
$vcard_id = $vcard->ID;

// URL publique de la vCard pour l'iframe
$public_url = get_permalink($vcard_id);

// Ajouter un paramètre pour désactiver la navigation dans l'iframe si besoin
$iframe_url = add_query_arg('preview', 'iframe', $public_url);

// Récupérer quelques données pour l'affichage
$vcard_title = $vcard->post_title;
$first_name = get_post_meta($vcard_id, 'first_name', true) ?: get_post_meta($vcard_id, 'firstname', true);
$last_name = get_post_meta($vcard_id, 'last_name', true) ?: get_post_meta($vcard_id, 'lastname', true);
$full_name = trim($first_name . ' ' . $last_name) ?: $vcard_title;

wp_enqueue_style('vcard-public', $plugin_url . 'assets/css/preview-vcard.css', [], '2.0.0');
wp_enqueue_script('vcard-public', $plugin_url . 'assets/js/preview-vcard.js', ['jquery'], '2.0.0', true);

?>

<div class="nfc-page-content">
    
    <!-- HEADER DE PAGE -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <h2 class="h4 text-dark mb-1">
                        <i class="fas fa-eye text-primary me-2"></i>
                        Aperçu public
                    </h2>
                    <p class="text-muted mb-0">
                        Prévisualisation de votre carte de visite : <strong><?php echo esc_html($full_name); ?></strong>
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-primary btn-sm" onclick="copyPreviewLink()" title="Copier le lien">
                        <i class="fas fa-copy me-1"></i>
                        Copier lien
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshIframe()" title="Actualiser">
                        <i class="fas fa-sync-alt me-1"></i>
                        Actualiser
                    </button>
                    <a href="<?php echo esc_url($public_url); ?>" target="_blank" class="btn btn-primary btn-sm" title="Voir en public">
                        <i class="fas fa-external-link-alt me-1"></i>
                        Voir en ligne
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- DEVICE SELECTOR -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-center gap-4 flex-wrap">
                        <span class="fw-medium text-muted me-2">Simulation :</span>
                        
                        <div class="btn-group" role="group" aria-label="Device selector">
                            <input type="radio" class="btn-check" name="device" id="device-mobile" value="mobile" checked>
                            <label class="btn btn-outline-secondary" for="device-mobile">
                                <i class="fas fa-mobile-alt me-1"></i>
                                Mobile
                            </label>
                            
                            <input type="radio" class="btn-check" name="device" id="device-tablet" value="tablet">
                            <label class="btn btn-outline-secondary" for="device-tablet">
                                <i class="fas fa-tablet-alt me-1"></i>
                                Tablette
                            </label>
                            
                            <input type="radio" class="btn-check" name="device" id="device-desktop" value="desktop">
                            <label class="btn btn-outline-secondary" for="device-desktop">
                                <i class="fas fa-desktop me-1"></i>
                                Bureau
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTAINER DE PREVIEW AVEC SIMULATION -->
    <div class="preview-container d-flex justify-content-center align-items-start py-4">
        <div class="device-frame mobile" id="deviceFrame">
            
            <!-- Device header (notch et status bar mobile) -->
            <div class="device-header">
                <div class="device-notch"></div>
                <div class="device-status-bar">
                    <span class="time">9:41</span>
                    <div class="device-status-icons">
                        <i class="fas fa-signal"></i>
                        <i class="fas fa-wifi"></i>
                        <i class="fas fa-battery-three-quarters"></i>
                    </div>
                </div>
            </div>
            
            <!-- Device screen avec iframe -->
            <div class="device-screen">
                <iframe 
                    id="vcardIframe" 
                    src="<?php echo esc_url($iframe_url); ?>" 
                    frameborder="0" 
                    scrolling="auto"
                    title="Aperçu de la vCard <?php echo esc_attr($full_name); ?>"
                    loading="lazy">
                    <p>Votre navigateur ne supporte pas les iframes. <a href="<?php echo esc_url($public_url); ?>" target="_blank">Voir la vCard directement</a>.</p>
                </iframe>
                
                <!-- Loader pendant le chargement -->
                <div class="iframe-loader" id="iframeLoader">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2 text-muted">Chargement de votre vCard...</p>
                </div>
            </div>
            
        </div>
    </div>

    <!-- ACTIONS RAPIDES -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-lg-3 col-6">
                            <button class="btn btn-light w-100 h-100 preview-action-btn" onclick="copyPreviewLink()">
                                <i class="fas fa-copy d-block mb-2 fs-4 text-primary"></i>
                                <small class="fw-medium">Copier le lien</small>
                            </button>
                        </div>
                        <div class="col-lg-3 col-6">
                            <button class="btn btn-light w-100 h-100 preview-action-btn" onclick="shareVCard()">
                                <i class="fas fa-share-alt d-block mb-2 fs-4 text-success"></i>
                                <small class="fw-medium">Partager</small>
                            </button>
                        </div>
                        <div class="col-lg-3 col-6">
                            <button class="btn btn-light w-100 h-100 preview-action-btn" onclick="generateQRCode()">
                                <i class="fas fa-qrcode d-block mb-2 fs-4 text-warning"></i>
                                <small class="fw-medium">QR Code</small>
                            </button>
                        </div>
                        <div class="col-lg-3 col-6">
                            <a href="<?php echo esc_url($public_url); ?>" target="_blank" class="btn btn-light w-100 h-100 preview-action-btn text-decoration-none">
                                <i class="fas fa-external-link-alt d-block mb-2 fs-4 text-info"></i>
                                <small class="fw-medium">Voir en public</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INFO URL PUBLIQUE -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-light d-flex align-items-center border">
                <i class="fas fa-info-circle text-info me-3"></i>
                <div class="flex-grow-1">
                    <strong>URL publique de votre vCard :</strong>
                    <div class="font-monospace small mt-1 user-select-all" id="vcardUrl"><?php echo esc_html($public_url); ?></div>
                </div>
                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyPreviewLink()" title="Copier l'URL">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>
    </div>

</div>

<!-- CSS intégré pour la simulation mobile -->
<style>
/* Container principal */
.preview-container {
    background: #babace;
    border-radius: 20px;
    min-height: 500px;
    padding: 40px 20px;
    margin: 0 auto;
}

/* Device Frames - basé sur ton design existant */
.device-frame {
    background: #000;
    border-radius: 30px;
    padding: 8px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.device-frame.mobile {
    width: 375px;
    height: 667px;
    max-width: 100%;
}

.device-frame.tablet {
    width: 768px;
    height: 600px;
    border-radius: 20px;
    max-width: 100%;
}

.device-frame.desktop {
    width: 1000px;
    height: 600px;
    border-radius: 10px;
    padding: 5px;
    background: #f8f9fa;
    max-width: 100%;
}

/* Device Header (notch et status bar) */
.device-header {
    background: #000;
    height: 30px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    font-weight: 600;
}

.device-notch {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 140px;
    height: 20px;
    background: #000;
    border-radius: 0 0 15px 15px;
    z-index: 10;
}

.device-status-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 0 20px;
    position: relative;
    z-index: 5;
}

.device-status-icons {
    display: flex;
    gap: 8px;
    font-size: 11px;
}

/* Masquer le header pour tablet/desktop */
.device-frame.tablet .device-header,
.device-frame.desktop .device-header {
    display: none;
}

/* Device Screen avec iframe */
.device-screen {
    background: white;
    height: calc(100% - 30px);
    border-radius: 22px;
    overflow: hidden;
    position: relative;
}

.device-frame.tablet .device-screen {
    height: 100%;
    border-radius: 15px;
}

.device-frame.desktop .device-screen {
    height: 100%;
    border-radius: 5px;
}

/* Iframe */
#vcardIframe {
    width: 100%;
    height: 100%;
    border: none;
    background: white;
}

/* Loader iframe */
.iframe-loader {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 10;
    transition: opacity 0.3s ease;
}

.iframe-loader.hidden {
    opacity: 0;
    pointer-events: none;
}

/* Actions buttons */
.preview-action-btn {
    transition: all 0.2s ease;
    min-height: 80px;
    border-radius: 12px !important;
}

.preview-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Responsive */
@media (max-width: 768px) {
    .preview-container {
        padding: 20px 10px;
    }
    
    .device-frame.mobile {
        transform: scale(0.85);
        margin: -50px auto;
    }
    
    .device-frame.tablet {
        transform: scale(0.7);
        margin: -100px auto;
    }
    
    .device-frame.desktop {
        transform: scale(0.6);
        margin: -120px auto;
    }
}

@media (max-width: 480px) {
    .device-frame.mobile {
        transform: scale(0.75);
        margin: -70px auto;
    }
    
    .device-frame.tablet,
    .device-frame.desktop {
        transform: scale(0.5);
        margin: -150px auto;
    }
}

/* Animation au chargement */
.device-frame {
    animation: deviceSlideIn 0.6s ease-out;
}

@keyframes deviceSlideIn {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>

<!-- JavaScript pour les interactions -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('📱 Preview vCard Dashboard - Initialisation');
    
    // Configuration
    const config = {
        vcard_id: <?php echo $vcard_id; ?>,
        public_url: '<?php echo esc_js($public_url); ?>',
        iframe_url: '<?php echo esc_js($iframe_url); ?>',
        full_name: '<?php echo esc_js($full_name); ?>'
    };
    
    // Éléments DOM
    const deviceFrame = document.getElementById('deviceFrame');
    const iframe = document.getElementById('vcardIframe');
    const loader = document.getElementById('iframeLoader');
    const deviceInputs = document.querySelectorAll('input[name="device"]');
    
    // Device selector
    deviceInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.checked) {
                switchDevice(this.value);
            }
        });
    });
    
    // Gestion du chargement de l'iframe
    iframe.addEventListener('load', function() {
        console.log('✅ Iframe vCard chargée');
        if (loader) {
            loader.classList.add('hidden');
        }
    });
    
    // Gestion des erreurs iframe
    iframe.addEventListener('error', function() {
        console.error('❌ Erreur chargement iframe');
        if (loader) {
            loader.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-warning fs-1 mb-3"></i>
                    <p class="text-muted">Erreur de chargement</p>
                    <button class="btn btn-sm btn-primary" onclick="refreshIframe()">Réessayer</button>
                </div>
            `;
        }
    });
    
    // Fonctions globales
    window.switchDevice = function(deviceType) {
        console.log('📱 Changement device:', deviceType);
        
        deviceFrame.className = 'device-frame ' + deviceType;
        
        // Animation smooth
        deviceFrame.style.transform = 'scale(0.98)';
        setTimeout(() => {
            deviceFrame.style.transform = '';
        }, 200);
    };
    
    window.refreshIframe = function() {
        console.log('🔄 Actualisation iframe');
        
        if (loader) {
            loader.classList.remove('hidden');
        }
        
        // Recharger l'iframe
        iframe.src = iframe.src;
    };
    
    window.copyPreviewLink = function() {
        const url = config.public_url;
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                showMessage('🔗 Lien copié dans le presse-papier !', 'success');
            }).catch(() => {
                fallbackCopyLink(url);
            });
        } else {
            fallbackCopyLink(url);
        }
    };
    
    window.shareVCard = function() {
        const shareData = {
            title: config.full_name + ' - vCard NFC',
            text: 'Découvrez ma carte de visite numérique',
            url: config.public_url
        };
        
        if (navigator.share && navigator.canShare && navigator.canShare(shareData)) {
            navigator.share(shareData)
                .then(() => console.log('✅ Partage réussi'))
                .catch(err => console.log('Partage annulé:', err));
        } else {
            // Fallback: copier le lien
            copyPreviewLink();
        }
    };
    
    window.generateQRCode = function() {
        // Ouvrir un service de génération QR en externe
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=400x400&format=png&data=${encodeURIComponent(config.public_url)}`;
        
        // Créer un lien de téléchargement
        const link = document.createElement('a');
        link.href = qrUrl;
        link.download = `qr-code-${config.full_name.toLowerCase().replace(/\s+/g, '-')}.png`;
        link.target = '_blank';
        link.click();
        
        showMessage('📱 QR Code généré et téléchargé !', 'info');
    };
    
    // Utilitaires
    function fallbackCopyLink(url) {
        const textArea = document.createElement('textarea');
        textArea.value = url;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            showMessage('🔗 Lien copié !', 'success');
        } catch (err) {
            showMessage('❌ Impossible de copier le lien', 'error');
        }
        
        document.body.removeChild(textArea);
    }
    
    function showMessage(message, type = 'info') {
        // Utiliser le système de notification du dashboard s'il existe
        if (window.NFCDashboard && typeof window.NFCDashboard.showMessage === 'function') {
            window.NFCDashboard.showMessage(message, type);
            return;
        }
        
        // Fallback: notification Bootstrap
        const alertClass = type === 'error' ? 'danger' : type;
        const alert = document.createElement('div');
        alert.className = `alert alert-${alertClass} alert-dismissible position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Auto-remove
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 4000);
    }
    
    console.log('✅ Preview Dashboard initialisé avec config:', config);
});
</script>