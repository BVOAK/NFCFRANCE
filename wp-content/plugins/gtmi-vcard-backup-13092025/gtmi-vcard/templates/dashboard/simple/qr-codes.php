<?php
/**
 * Template: QR Codes
 * 
 * Fichier: templates/dashboard/simple/qr-codes.php
 * Gestion et téléchargement des QR codes
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables globales disponibles
global $nfc_vcard, $nfc_current_page;
$vcard = $nfc_vcard;

// Récupérer les données de la vCard
$vcard_id = $vcard->ID;
$vcard_title = $vcard->post_title;
$public_url = get_permalink($vcard_id);

// Récupérer le nom pour personnaliser l'URL
$first_name = get_post_meta($vcard_id, 'first_name', true);
$last_name = get_post_meta($vcard_id, 'last_name', true);
$display_name = trim($first_name . ' ' . $last_name) ?: $vcard_title;

// URL courte pour affichage
$short_url = str_replace(['http://', 'https://'], '', $public_url);
?>

<div class="nfc-page-content">
    
    <div class="row">
        <div class="col-12 mb-4">
            <h2 class="h4 text-dark">QR Codes</h2>
            <p class="text-muted">Gérez et téléchargez vos QR codes</p>
        </div>
    </div>
    
    <div class="row">
        
        <!-- Colonne gauche : Aperçu QR Code -->
        <div class="col-lg-6 mb-4">
            <div class="dashboard-card">
                <div class="card-header p-3">
                    <h3 class="h6 mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        Aperçu QR Code
                    </h3>
                </div>
                <div class="p-4 text-center">
                    
                    <!-- Container du QR Code -->
                    <div id="qr-code-container" class="bg-light rounded p-4 mb-3 d-inline-block" style="min-width: 200px; min-height: 200px;">
                        <!-- QR Code sera généré ici -->
                        <div id="qr-code-placeholder" class="d-flex align-items-center justify-content-center" style="height: 200px;">
                            <div class="text-muted">
                                <i class="fas fa-qrcode fa-5x mb-3 opacity-50"></i>
                                <p>Génération du QR Code...</p>
                            </div>
                        </div>
                        <div id="qr-code-display" class="d-none"></div>
                    </div>
                    
                    <!-- Informations du QR -->
                    <div class="qr-info">
                        <p class="text-muted small mb-1">QR Code vers votre vCard</p>
                        <p class="small fw-medium text-primary">
                            <i class="fas fa-link me-1"></i>
                            <?php echo esc_html($short_url); ?>
                        </p>
                        <p class="small text-muted">
                            Dernière génération : <span id="qr-generated-time">-</span>
                        </p>
                    </div>
                    
                    <!-- Bouton test -->
                    <div class="mt-3">
                        <a href="<?php echo esc_url($public_url); ?>" 
                           target="_blank" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-external-link-alt me-2"></i>
                            Tester le lien
                        </a>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <!-- Colonne droite : Téléchargements et options -->
        <div class="col-lg-6 mb-4">
            <div class="dashboard-card">
                <div class="card-header p-3">
                    <h3 class="h6 mb-0">
                        <i class="fas fa-download me-2"></i>
                        Téléchargements
                    </h3>
                </div>
                <div class="p-4">
                    
                    <!-- Boutons de téléchargement -->
                    <div class="d-grid gap-2 mb-4">
                        <button type="button" 
                                class="btn btn-primary" 
                                onclick="downloadQRCode('png')"
                                data-format="png">
                            <i class="fas fa-download me-2"></i>
                            PNG Haute qualité
                            <small class="d-block text-light opacity-75">Idéal pour le web (300x300px)</small>
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-primary" 
                                onclick="downloadQRCode('svg')"
                                data-format="svg">
                            <i class="fas fa-download me-2"></i>
                            SVG Vectoriel
                            <small class="d-block text-muted">Format vectoriel redimensionnable</small>
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-primary" 
                                onclick="downloadQRCode('pdf')"
                                data-format="pdf">
                            <i class="fas fa-download me-2"></i>
                            PDF Impression
                            <small class="d-block text-muted">Prêt pour l'impression professionnelle</small>
                        </button>
                    </div>
                    
                    <!-- Séparateur -->
                    <hr class="my-4">
                    
                    <!-- Options de personnalisation -->
                    <div class="customization-section">
                        <h6 class="small text-muted mb-3">
                            <i class="fas fa-palette me-1"></i>
                            Personnalisation
                        </h6>
                        
                        <div class="row g-2">
                            <!-- Taille -->
                            <div class="col-12">
                                <label class="form-label small fw-medium">Taille du QR Code</label>
                                <select id="qr-size" class="form-select form-select-sm" onchange="updateQRCode()">
                                    <option value="200">Petit (200x200px)</option>
                                    <option value="300" selected>Moyen (300x300px)</option>
                                    <option value="400">Grand (400x400px)</option>
                                    <option value="500">Très grand (500x500px)</option>
                                </select>
                            </div>
                            
                            <!-- Couleur -->
                            <div class="col-6">
                                <label class="form-label small fw-medium">Couleur</label>
                                <select id="qr-color" class="form-select form-select-sm" onchange="updateQRCode()">
                                    <option value="#000000" selected>Noir</option>
                                    <option value="#0040C1">Bleu NFC</option>
                                    <option value="#667eea">Bleu clair</option>
                                    <option value="#10b981">Vert</option>
                                    <option value="#ef4444">Rouge</option>
                                </select>
                            </div>
                            
                            <!-- Arrière-plan -->
                            <div class="col-6">
                                <label class="form-label small fw-medium">Arrière-plan</label>
                                <select id="qr-background" class="form-select form-select-sm" onchange="updateQRCode()">
                                    <option value="#ffffff" selected>Blanc</option>
                                    <option value="transparent">Transparent</option>
                                    <option value="#f8fafc">Gris clair</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Bouton régénérer -->
                        <div class="mt-3">
                            <button type="button" 
                                    class="btn btn-outline-secondary btn-sm w-100" 
                                    onclick="regenerateQRCode()">
                                <i class="fas fa-sync-alt me-2"></i>
                                Régénérer le QR Code
                            </button>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Statistiques QR Code -->
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            Statistiques QR Code
                        </h3>
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshQRStats()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Actualiser
                        </button>
                    </div>
                </div>
                <div class="p-4">
                    
                    <!-- Stats en grille -->
                    <div class="row text-center g-3" id="qr-stats-container">
                        <div class="col-md-3 col-6">
                            <div class="stat-item p-3 bg-primary bg-opacity-10 rounded">
                                <h4 class="text-primary mb-1" id="stat-total-scans">-</h4>
                                <small class="text-muted">Scans total</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item p-3 bg-success bg-opacity-10 rounded">
                                <h4 class="text-success mb-1" id="stat-week-scans">-</h4>
                                <small class="text-muted">Cette semaine</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item p-3 bg-warning bg-opacity-10 rounded">
                                <h4 class="text-warning mb-1" id="stat-today-scans">-</h4>
                                <small class="text-muted">Aujourd'hui</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item p-3 bg-info bg-opacity-10 rounded">
                                <h4 class="text-info mb-1" id="stat-conversion-rate">-</h4>
                                <small class="text-muted">Taux conversion</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Graphique des scans (placeholder) -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="small text-muted mb-3">Évolution des scans (7 derniers jours)</h6>
                        <div id="qr-scans-chart" style="height: 200px;">
                            <canvas id="qr-scans-canvas"></canvas>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <!-- Guide d'utilisation -->
    <div class="row mt-4">
        <div class="col-lg-8 mx-auto">
            <div class="dashboard-card">
                <div class="card-header p-3">
                    <h3 class="h6 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Comment utiliser votre QR Code
                    </h3>
                </div>
                <div class="p-4">
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-mobile-alt fa-2x text-primary"></i>
                            </div>
                            <h6>1. Scanner</h6>
                            <small class="text-muted">
                                Utilisez l'appareil photo de votre smartphone pour scanner le QR Code
                            </small>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-id-card fa-2x text-success"></i>
                            </div>
                            <h6>2. Voir la vCard</h6>
                            <small class="text-muted">
                                Vos contacts accèdent directement à votre carte de visite numérique
                            </small>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-address-book fa-2x text-info"></i>
                            </div>
                            <h6>3. Enregistrer</h6>
                            <small class="text-muted">
                                Ils peuvent sauvegarder vos informations directement dans leurs contacts
                            </small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <div class="d-flex">
                            <i class="fas fa-lightbulb me-3 mt-1"></i>
                            <div>
                                <strong>Astuce :</strong> Imprimez votre QR Code sur vos cartes de visite, flyers, ou affichez-le lors d'événements pour un partage rapide de vos informations de contact.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script type="text/javascript">
// Variables globales pour le QR Code
let qrCodeInstance = null;
let currentQRSettings = {
    size: 300,
    color: '#000000',
    background: '#ffffff'
};

jQuery(document).ready(function($) {
    console.log('📱 Page QR Codes chargée');
    
    // Générer le QR Code initial
    generateQRCode();
    
    // Charger les statistiques
    loadQRStats();
    
    // Initialiser le graphique
    initQRStatsChart();
});

/**
 * Générer le QR Code
 */
function generateQRCode() {
    const $ = jQuery;
    const vCardUrl = '<?php echo esc_js($public_url); ?>';
    
    console.log('🔄 Génération QR Code pour:', vCardUrl);
    
    // Vérifier si QRCode.js est disponible
    if (typeof QRCode === 'undefined') {
        console.log('⚠️ QRCode.js non disponible, chargement depuis CDN...');
        
        // Charger QRCode.js dynamiquement
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js';
        script.onload = function() {
            console.log('✅ QRCode.js chargé');
            generateQRCodeActual(vCardUrl);
        };
        document.head.appendChild(script);
    } else {
        generateQRCodeActual(vCardUrl);
    }
}

/**
 * Génération effective du QR Code
 */
function generateQRCodeActual(url) {
    const $ = jQuery;
    
    // Nettoyer le container
    $('#qr-code-display').empty().removeClass('d-none');
    $('#qr-code-placeholder').addClass('d-none');
    
    // Configuration du QR Code
    const options = {
        width: currentQRSettings.size,
        height: currentQRSettings.size,
        color: {
            dark: currentQRSettings.color,
            light: currentQRSettings.background === 'transparent' ? '#00000000' : currentQRSettings.background
        },
        margin: 2,
        errorCorrectionLevel: 'M'
    };
    
    // Générer le QR Code
    QRCode.toCanvas(document.createElement('canvas'), url, options, function(error, canvas) {
        if (error) {
            console.error('❌ Erreur génération QR:', error);
            showQRError();
            return;
        }
        
        console.log('✅ QR Code généré avec succès');
        
        // Afficher le QR Code
        $('#qr-code-display').html(canvas);
        
        // Mettre à jour l'heure de génération
        $('#qr-generated-time').text(new Date().toLocaleString('fr-FR'));
        
        // Stocker le canvas pour les téléchargements
        window.currentQRCanvas = canvas;
    });
}

/**
 * Afficher une erreur de génération QR
 */
function showQRError() {
    const $ = jQuery;
    
    $('#qr-code-display').html(`
        <div class="text-center text-danger p-4">
            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
            <p>Erreur lors de la génération du QR Code</p>
            <button class="btn btn-outline-primary btn-sm" onclick="generateQRCode()">
                <i class="fas fa-retry me-1"></i>Réessayer
            </button>
        </div>
    `).removeClass('d-none');
    
    $('#qr-code-placeholder').addClass('d-none');
}

/**
 * Mettre à jour le QR Code selon les options
 */
function updateQRCode() {
    const $ = jQuery;
    
    // Récupérer les nouveaux paramètres
    currentQRSettings.size = parseInt($('#qr-size').val());
    currentQRSettings.color = $('#qr-color').val();
    currentQRSettings.background = $('#qr-background').val();
    
    console.log('🎨 Mise à jour QR avec:', currentQRSettings);
    
    // Régénérer
    generateQRCode();
}

/**
 * Régénérer le QR Code
 */
function regenerateQRCode() {
    console.log('🔄 Régénération QR Code...');
    
    // Réafficher le placeholder temporairement
    jQuery('#qr-code-placeholder').removeClass('d-none');
    jQuery('#qr-code-display').addClass('d-none');
    
    // Régénérer après un court délai
    setTimeout(() => {
        generateQRCode();
    }, 500);
}

/**
 * Télécharger le QR Code
 */
function downloadQRCode(format) {
    const $ = jQuery;
    
    if (!window.currentQRCanvas) {
        alert('QR Code non disponible. Veuillez attendre la génération.');
        return;
    }
    
    console.log(`💾 Téléchargement QR Code format: ${format}`);
    
    const vCardName = '<?php echo esc_js($display_name); ?>';
    const fileName = `qr-code-${vCardName.toLowerCase().replace(/\s+/g, '-')}-${Date.now()}`;
    
    switch (format) {
        case 'png':
            downloadCanvasAsPNG(window.currentQRCanvas, fileName);
            break;
            
        case 'svg':
            downloadQRAsSVG(fileName);
            break;
            
        case 'pdf':
            downloadQRAsPDF(fileName);
            break;
            
        default:
            console.error('Format non supporté:', format);
    }
}

/**
 * Télécharger canvas comme PNG
 */
function downloadCanvasAsPNG(canvas, fileName) {
    const link = document.createElement('a');
    link.download = fileName + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
    
    console.log('✅ PNG téléchargé:', fileName);
    
    if (typeof window.NFCDashboard !== 'undefined') {
        window.NFCDashboard.showNotification('QR Code PNG téléchargé', 'success');
    }
}

/**
 * Télécharger comme SVG (placeholder)
 */
function downloadQRAsSVG(fileName) {
    // Pour l'instant, convertir le canvas en PNG
    // TODO: Implémenter la génération SVG native
    if (window.currentQRCanvas) {
        downloadCanvasAsPNG(window.currentQRCanvas, fileName + '-svg');
    }
    
    console.log('⚠️ SVG téléchargé comme PNG (temporaire)');
    
    if (typeof window.NFCDashboard !== 'undefined') {
        window.NFCDashboard.showNotification('QR Code téléchargé (format PNG)', 'info');
    }
}

/**
 * Télécharger comme PDF (placeholder)
 */
function downloadQRAsPDF(fileName) {
    // Pour l'instant, convertir le canvas en PNG
    // TODO: Implémenter la génération PDF avec jsPDF
    if (window.currentQRCanvas) {
        downloadCanvasAsPNG(window.currentQRCanvas, fileName + '-pdf');
    }
    
    console.log('⚠️ PDF téléchargé comme PNG (temporaire)');
    
    if (typeof window.NFCDashboard !== 'undefined') {
        window.NFCDashboard.showNotification('QR Code téléchargé (format PNG)', 'info');
    }
}

/**
 * Charger les statistiques QR
 */
function loadQRStats() {
    console.log('📊 Chargement stats QR...');
    
    if (typeof window.NFCDashboard === 'undefined') {
        // Données simulées pour la démo
        updateQRStats({
            total: 89,
            week: 12,
            today: 3,
            conversion: 67
        });
        return;
    }
    
    // Charger via API
    window.NFCDashboard.apiCall('statistics', 'GET', null, function(response) {
        if (response.success && response.data) {
            const stats = calculateQRStats(response.data);
            updateQRStats(stats);
        } else {
            // Fallback avec données simulées
            updateQRStats({
                total: 0,
                week: 0,
                today: 0,
                conversion: 0
            });
        }
    });
}

/**
 * Calculer les stats QR depuis les données API
 */
function calculateQRStats(statsData) {
    if (!Array.isArray(statsData)) {
        return { total: 0, week: 0, today: 0, conversion: 0 };
    }
    
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
    
    const todayStats = statsData.filter(stat => {
        const statDate = new Date(stat.created_at || stat.date);
        return statDate >= today;
    });
    
    const weekStats = statsData.filter(stat => {
        const statDate = new Date(stat.created_at || stat.date);
        return statDate >= weekAgo;
    });
    
    return {
        total: statsData.length,
        week: weekStats.length,
        today: todayStats.length,
        conversion: statsData.length > 0 ? Math.round((todayStats.length / statsData.length) * 100) : 0
    };
}

/**
 * Mettre à jour l'affichage des stats
 */
function updateQRStats(stats) {
    const $ = jQuery;
    
    $('#stat-total-scans').text(stats.total);
    $('#stat-week-scans').text(stats.week);
    $('#stat-today-scans').text(stats.today);
    $('#stat-conversion-rate').text(stats.conversion + '%');
    
    console.log('📊 Stats QR mises à jour:', stats);
}

/**
 * Actualiser les stats
 */
function refreshQRStats() {
    console.log('🔄 Actualisation stats QR...');
    loadQRStats();
    
    if (typeof window.NFCDashboard !== 'undefined') {
        window.NFCDashboard.showNotification('Statistiques actualisées', 'success');
    }
}

/**
 * Initialiser le graphique des scans
 */
function initQRStatsChart() {
    if (typeof Chart === 'undefined') {
        console.log('⚠️ Chart.js non disponible pour le graphique QR');
        return;
    }
    
    const ctx = document.getElementById('qr-scans-canvas');
    if (!ctx) return;
    
    // Données simulées pour 7 derniers jours
    const data = {
        labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        datasets: [{
            label: 'Scans QR',
            data: [2, 5, 1, 8, 3, 6, 4],
            borderColor: '#0040C1',
            backgroundColor: 'rgba(0, 64, 193, 0.1)',
            tension: 0.4,
            fill: true
        }]
    };
    
    window.qrStatsChart = new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    console.log('📈 Graphique QR Stats initialisé');
}
</script>