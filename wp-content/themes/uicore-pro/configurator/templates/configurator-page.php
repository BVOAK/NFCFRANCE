<?php
/**
 * Template Page Configurateur NFC
 * 
 * Accessible via /configurateur?product_id=571
 * Template basé sur page.php d'UiCore avec layout personnalisé
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier que le produit ID est valide
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if (!$product_id) {
    wp_die('ID produit manquant');
}

// Charger la classe produit et vérifier
require_once get_template_directory() . '/configurator/includes/class-nfc-product.php';
$nfc_product = new NFC_Product_Manager();

if (!$nfc_product->can_be_configured($product_id)) {
    wp_die('Produit non configurable');
}

// Récupérer les données du produit
$product_data = $nfc_product->get_product_data();
if (is_wp_error($product_data)) {
    wp_die('Erreur produit : ' . $product_data->get_error_message());
}

// Configuration pour JavaScript
$nfc_config = [
    'productId' => $product_id,
    'variations' => $product_data['variations'],
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_configurator'),
    'homeUrl' => home_url(),
    'cartUrl' => wc_get_cart_url()
];

// Charger header comme page.php
get_header();

// Styles configurateur
wp_enqueue_style('nfc-configurator', get_template_directory_uri() . '/configurator/assets/css/configurator.css', [], '1.0.0');
?>

<div id="primary" class="content-area">
    <?php if (class_exists('\UiCore\Core')): ?>
        <!-- Template UiCore avec header personnalisé -->
        <div class="nfc-configurator-header">
            <div class="uicore-container">
                <h1 class="nfc-page-title">Personnaliser votre carte :</h1>
            </div>
        </div>
        
        <div class="nfc-configurator-wrapper" id="nfcConfiguratorWrapper">
            <div class="uicore-container">
                <div class="nfc-configurator" id="nfcConfigurator">
                    
                    <!-- Layout deux colonnes selon mockup -->
                    <div class="configurator-layout">
                        
                        <!-- Colonne gauche : Aperçu -->
                        <div class="preview-column">
                            <div class="preview-header">
                                <h2>Aperçu</h2>
                            </div>
                            
                            <div class="cards-stack">
                                <!-- Carte Recto -->
                                <div class="card-preview recto blanc" data-side="recto">
                                    <div class="card-content">
                                        <!-- Image masquée couvrant toute la carte -->
                                        <div class="image-mask" id="imageMask">
                                            <div class="image-placeholder" id="imagePlaceholder">
                                                <span>📷</span>
                                                <p>Glissez votre image ici</p>
                                            </div>
                                            <img id="cardImage" style="display: none;" alt="Image personnalisée">
                                        </div>
                                        
                                        <!-- Symbole NFC -->
                                        <div class="nfc-symbol" id="nfcSymbol">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M20,2H4A2,2 0 0,0 2,4V20A2,2 0 0,0 4,22H20A2,2 0 0,0 22,20V4A2,2 0 0,0 20,2M20,20H4V4H20V20M18,6H16V9H13V11H16V13H13V15H16V18H18V6M6,18H8V15H11V13H8V11H11V9H8V6H6V18Z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Carte Verso -->
                                <div class="card-preview verso blanc" data-side="verso">
                                    <div class="card-content">
                                        <!-- Nom utilisateur à gauche -->
                                        <div class="user-section">
                                            <div class="user-names">
                                                <div class="user-firstname" id="versoUserFirstName">Prénom</div>
                                                <div class="user-lastname" id="versoUserLastName">Nom</div>
                                            </div>
                                        </div>
                                        
                                        <!-- QR Code à droite -->
                                        <div class="qr-section">
                                            <div class="qr-code" id="qrCode">
                                                <div class="qr-placeholder">
                                                    <div class="qr-pattern"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Colonne droite : Configuration -->
                        <div class="config-column">
                            
                            <!-- Sélection support -->
                            <div class="config-section support-selection">
                                <h3>Choisissez votre support :</h3>
                                <div class="support-options">
                                    <label class="support-option">
                                        <input type="radio" name="card-color" value="blanc" checked>
                                        <span class="support-preview white"></span>
                                    </label>
                                    <label class="support-option">
                                        <input type="radio" name="card-color" value="noir">
                                        <span class="support-preview black"></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Section Recto -->
                            <div class="config-section recto-section">
                                <h3>Recto :</h3>
                                
                                <div class="config-step">
                                    <label class="step-label">1. Insérer une image ou un logo :</label>
                                    
                                    <div class="upload-area">
                                        <div class="upload-zone" id="imageUploadZone">
                                            <span class="upload-text">Sélectionner un fichier...</span>
                                            <input type="file" id="imageInput" accept="image/jpeg,image/png,image/svg+xml" hidden>
                                        </div>
                                        <button type="button" class="upload-button" onclick="document.getElementById('imageInput').click()">
                                            Ajouter une image
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="config-step" id="imageAdjustStep" style="display: none;">
                                    <label class="step-label">2. Ajuster l'image ou le logo</label>
                                    
                                    <div class="image-controls" id="imageControls">
                                        <div class="slider-control">
                                            <input type="range" id="imageScale" min="100" max="200" value="100">
                                        </div>
                                        <div class="position-controls" style="display: none;">
                                            <input type="range" id="imageX" min="-50" max="50" value="0">
                                            <input type="range" id="imageY" min="-50" max="50" value="0">
                                        </div>
                                        <button type="button" class="remove-image-btn" id="removeImageBtn" style="display: none;">
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Section Verso -->
                            <div class="config-section verso-section">
                                <h3>Verso :</h3>
                                
                                <div class="name-inputs">
                                    <div class="input-group">
                                        <input type="text" id="lastName" placeholder="Nom" required>
                                    </div>
                                    <div class="input-group">  
                                        <input type="text" id="firstName" placeholder="Prénom" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Bouton Ajout Panier -->
                            <div class="config-section cart-section">
                                <button type="button" class="add-to-cart-btn" id="addToCartBtn" disabled>
                                    Ajouter au panier
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Loading overlay -->
                    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
                        <div class="loading-content">
                            <div class="spinner"></div>
                            <p>Ajout au panier en cours...</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Fallback si UiCore non actif -->
        <main class="uicore-container">
            <p>UiCore requis pour le configurateur</p>
        </main>
    <?php endif; ?>
</div>

<!-- Configuration JavaScript -->
<script type="text/javascript">
    window.nfcConfig = <?php echo json_encode($nfc_config); ?>;
</script>

<!-- Scripts configurateur -->
<script src="<?php echo get_template_directory_uri(); ?>/configurator/assets/js/canvas-handler.js?v=1.1"></script>
<script src="<?php echo get_template_directory_uri(); ?>/configurator/assets/js/wc-integration.js?v=1.1"></script>
<script src="<?php echo get_template_directory_uri(); ?>/configurator/assets/js/configurator.js?v=1.1"></script>

<script>
    // Initialisation après chargement complet
    document.addEventListener('DOMContentLoaded', function() {
        // Fade in du configurateur
        setTimeout(() => {
            document.getElementById('nfcConfigurator').classList.add('loaded');
            document.getElementById('nfcConfiguratorWrapper').classList.add('loaded');
        }, 100);
    });
</script>

<?php get_footer(); ?>

<div class="nfc-configurator" id="nfcConfigurator">
    
    <!-- Header -->
    <header class="configurator-header">
        <div class="header-content">
            <div class="header-left">
                <a href="<?php echo home_url(); ?>" class="logo-link">
                    <span class="logo-text">NFC France</span>
                </a>
                <span class="separator">|</span>
                <h1 class="page-title">Configurateur</h1>
            </div>
            
            <div class="header-right">
                <div class="price-display">
                    <span class="price-label">Prix :</span>
                    <span class="price-amount" id="currentPrice">30,00€</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="configurator-main">
        
        <!-- Section Preview -->
        <section class="preview-section">
            <div class="preview-header">
                <h2>Aperçu de votre carte</h2>
                <p>Voici comment apparaîtra votre carte NFC personnalisée</p>
            </div>
            
            <div class="cards-container">
                <!-- Carte Recto -->
                <div class="card-wrapper">
                    <h3 class="card-title">Recto</h3>
                    <div class="card-preview recto blanc" data-side="recto">
                        <div class="card-content">
                            <div class="logo-area" id="logoArea">
                                <div class="logo-placeholder">
                                    <span>📷</span>
                                    <p>Votre logo</p>
                                </div>
                            </div>
                            <div class="user-info">
                                <div class="user-name" id="displayName">Votre nom</div>
                                <div class="user-title">nfcfrance.com</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carte Verso -->
                <div class="card-wrapper">
                    <h3 class="card-title">Verso</h3>
                    <div class="card-preview verso blanc" data-side="verso">
                        <div class="card-content">
                            <div class="qr-section">
                                <div class="qr-code" id="qrCode">
                                    <div class="qr-placeholder">QR</div>
                                </div>
                            </div>
                            <div class="contact-info">
                                <div class="contact-name" id="contactName">Votre nom</div>
                                <div class="contact-website">nfcfrance.com</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Panel Configuration -->
        <aside class="config-panel">
            
            <!-- Sélection Couleur -->
            <div class="config-section color-selection">
                <h3 class="section-title">
                    <span class="icon">🎨</span>
                    Couleur de la carte
                </h3>
                
                <div class="color-options">
                    <label class="color-option">
                        <input type="radio" name="card-color" value="blanc" checked>
                        <span class="color-preview white"></span>
                        <span class="color-label">Blanc</span>
                    </label>
                    <label class="color-option">
                        <input type="radio" name="card-color" value="noir">
                        <span class="color-preview black"></span>
                        <span class="color-label">Noir</span>
                    </label>
                </div>
            </div>

            <!-- Upload Image -->
            <div class="config-section image-upload">
                <h3 class="section-title">
                    <span class="icon">📷</span>
                    Image ou logo
                </h3>
                
                <div class="upload-zone" id="imageUploadZone">
                    <div class="upload-content">
                        <div class="upload-icon">📁</div>
                        <p class="upload-text">Glissez votre image ici<br>ou cliquez pour parcourir</p>
                        <p class="upload-specs">JPG, PNG, SVG • Max 2MB</p>
                    </div>
                    <input type="file" id="imageInput" accept="image/jpeg,image/png,image/svg+xml" hidden>
                </div>
                
                <div class="image-controls" id="imageControls" style="display: none;">
                    <div class="control-group">
                        <label for="imageScale">Taille :</label>
                        <input type="range" id="imageScale" min="50" max="150" value="100">
                        <span class="scale-value">100%</span>
                    </div>
                    <button type="button" class="remove-image-btn" id="removeImageBtn">
                        Supprimer l'image
                    </button>
                </div>
            </div>

            <!-- Informations Utilisateur -->
            <div class="config-section user-info">
                <h3 class="section-title">
                    <span class="icon">👤</span>
                    Vos informations
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="firstName">Prénom *</label>
                        <input type="text" id="firstName" placeholder="Jean" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Nom *</label>
                        <input type="text" id="lastName" placeholder="Dupont" required>
                    </div>
                </div>
                
                <div class="form-note">
                    <small>* Ces informations apparaîtront sur votre carte</small>
                </div>
            </div>

            <!-- Bouton Ajout Panier -->
            <div class="config-section cart-section">
                <button type="button" class="add-to-cart-btn" id="addToCartBtn" disabled>
                    <span class="btn-icon">🛒</span>
                    <span class="btn-text">Ajouter au panier</span>
                    <span class="btn-price" id="btnPrice">30,00€</span>
                </button>
                
                <div class="cart-note">
                    <small>✅ Livraison 7-10 jours ouvrés</small>
                </div>
            </div>

        </aside>
    </main>


</div>
</div>

<!-- Configuration JavaScript -->
<script type="text/javascript">
    window.nfcConfig = <?php echo json_encode($nfc_config); ?>;
</script>

<!-- Scripts configurateur -->
<script src="<?php echo get_template_directory_uri(); ?>/configurator/assets/js/canvas-handler.js?v=1.0"></script>
<script src="<?php echo get_template_directory_uri(); ?>/configurator/assets/js/wc-integration.js?v=1.0"></script>
<script src="<?php echo get_template_directory_uri(); ?>/configurator/assets/js/configurator.js?v=1.0"></script>

<script>
    // Initialisation après chargement complet
    document.addEventListener('DOMContentLoaded', function() {
        // Fade in du configurateur
        setTimeout(() => {
            document.getElementById('nfcConfigurator').classList.add('loaded');
            document.getElementById('nfcConfiguratorWrapper').classList.add('loaded');
        }, 100);
    });
</script>

<?php get_footer(); ?>