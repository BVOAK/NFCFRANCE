<?php
/**
 * Template Name: Configurateur NFC Simple
 * 
 * Version simple sans UiCore pour debug
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier que le produit ID est valide
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 571;

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
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurateur NFC - Test Simple</title>
    
    <!-- Styles configurateur -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/configurator/assets/css/configurator.css?v=2.0">
    
    <?php wp_head(); ?>
</head>
<body>

<h1>Configurateur NFC - Version Simple</h1>

<div class="nfc-configurator-wrapper" id="nfcConfiguratorWrapper">
    <div class="nfc-configurator" id="nfcConfigurator">
        
        <!-- Layout deux colonnes -->
        <div class="configurator-layout">
            
            <!-- Colonne gauche : Aperçu -->
            <div class="preview-column">
                <h2>Aperçu</h2>
                
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

<!-- Configuration JavaScript -->
<script type="text/javascript">
    window.nfcConfig = <?php echo json_encode($nfc_config); ?>;
    console.log('🎛️ Configuration chargée VERSION SIMPLE:', window.nfcConfig);
</script>

<!-- Script configurateur INLINE pour bypasser le cache -->
<script>
// Version INLINE 3.0 - Bypass cache complet
console.log('🚀 Script INLINE 3.0 chargé');

if (typeof window.NFCConfigurator === 'undefined') {
    window.NFCConfigurator = class NFCConfigurator {
        constructor() {
            console.log('🏗️ Constructeur INLINE 3.0 démarré');
            
            if (!window.nfcConfig) {
                console.error('❌ Configuration NFC manquante');
                return;
            }
            
            this.config = window.nfcConfig;
            this.productId = this.config.productId;
            this.variations = this.config.variations;
            
            this.state = {
                selectedColor: 'blanc',
                selectedVariation: this.variations['blanc'],
                userInfo: { firstName: '', lastName: '' },
                image: null,
                isValid: false
            };
            
            this.elements = {};
            this.init();
        }
        
        async init() {
            console.log('🚀 Init INLINE 3.0 démarré');
            
            try {
                this.cacheElements();
                this.bindEvents();
                this.setInitialState();
                this.validateConfiguration();
                console.log('✅ Configurateur INLINE 3.0 initialisé');
            } catch (error) {
                console.error('❌ Erreur init INLINE 3.0:', error);
            }
        }
        
        cacheElements() {
            this.elements = {
                colorInputs: document.querySelectorAll('input[name="card-color"]'),
                imageUploadZone: document.getElementById('imageUploadZone'),
                imageInput: document.getElementById('imageInput'),
                imageMask: document.getElementById('imageMask'),
                cardImage: document.getElementById('cardImage'),
                firstNameInput: document.getElementById('firstName'),
                lastNameInput: document.getElementById('lastName'),
                versoUserFirstName: document.getElementById('versoUserFirstName'),
                versoUserLastName: document.getElementById('versoUserLastName'),
                addToCartBtn: document.getElementById('addToCartBtn'),
                rectoCard: document.querySelector('.card-preview.recto'),
                versoCard: document.querySelector('.card-preview.verso')
            };
            console.log('✅ Éléments INLINE 3.0 cachés');
        }
        
        bindEvents() {
            console.log('🔗 Binding INLINE 3.0...');
            
            // Couleurs
            this.elements.colorInputs.forEach(input => {
                input.addEventListener('change', (e) => {
                    if (e.target.checked) {
                        this.changeColor(e.target.value);
                    }
                });
            });
            
            // Upload
            if (this.elements.imageInput) {
                this.elements.imageInput.addEventListener('change', (e) => {
                    console.log('📂 INLINE 3.0 - Change input file');
                    this.handleImageSelect(e);
                });
            }
            
            if (this.elements.imageUploadZone) {
                this.elements.imageUploadZone.addEventListener('click', () => {
                    console.log('👆 INLINE 3.0 - Click upload zone');
                    this.elements.imageInput.click();
                });
            }
            
            // Nom/prénom
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
            
            console.log('✅ Events INLINE 3.0 bindés');
        }
        
        handleImageSelect(e) {
            console.log('📁 INLINE 3.0 - handleImageSelect');
            const file = e.target.files[0];
            if (file) {
                console.log('✅ INLINE 3.0 - Fichier sélectionné:', file.name);
                this.processImageFile(file);
            }
        }
        
        processImageFile(file) {
            console.log('📷 INLINE 3.0 - Traitement image:', file.name);
            
            // Validation simple
            if (file.size > 2 * 1024 * 1024) {
                alert('Fichier trop gros (max 2MB)');
                return;
            }
            
            const allowedTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format non supporté (JPG, PNG, SVG uniquement)');
                return;
            }
            
            console.log('✅ INLINE 3.0 - Validation OK');
            
            // Lecture fichier
            const reader = new FileReader();
            reader.onload = (e) => {
                console.log('✅ INLINE 3.0 - Fichier lu');
                this.setImage(e.target.result, file.name);
            };
            reader.readAsDataURL(file);
        }
        
        setImage(dataUrl, fileName) {
            console.log('🎯 INLINE 3.0 - setImage:', fileName);
            
            this.state.image = {
                data: dataUrl,
                name: fileName,
                scale: 100
            };
            
            console.log('✅ INLINE 3.0 - Image stockée');
            this.displayImageOnCard();
        }
        
        displayImageOnCard() {
            console.log('🖼️ INLINE 3.0 - Affichage image');
            
            if (!this.state.image || !this.elements.imageMask || !this.elements.cardImage) {
                console.warn('❌ INLINE 3.0 - Éléments manquants');
                return;
            }
            
            // Appliquer l'image
            this.elements.imageMask.classList.add('has-image');
            this.elements.cardImage.src = this.state.image.data;
            this.elements.cardImage.style.display = 'block';
            this.elements.cardImage.style.opacity = '1';
            
            console.log('✅ INLINE 3.0 - Image appliquée');
            console.log('- src défini:', !!this.elements.cardImage.src);
            console.log('- display:', this.elements.cardImage.style.display);
            console.log('- has-image class:', this.elements.imageMask.classList.contains('has-image'));
        }
        
        changeColor(color) {
            console.log('🎨 INLINE 3.0 - Changement couleur:', color);
            this.state.selectedColor = color;
            this.state.selectedVariation = this.variations[color];
            
            if (this.elements.rectoCard) {
                this.elements.rectoCard.className = `card-preview recto ${color}`;
            }
            if (this.elements.versoCard) {
                this.elements.versoCard.className = `card-preview verso ${color}`;
            }
        }
        
        updateUserInfo(field, value) {
            this.state.userInfo[field] = value.trim();
            this.updateCardUserInfo();
            this.validateConfiguration();
        }
        
        updateCardUserInfo() {
            const { firstName, lastName } = this.state.userInfo;
            
            if (this.elements.versoUserFirstName) {
                this.elements.versoUserFirstName.textContent = firstName || 'Prénom';
            }
            if (this.elements.versoUserLastName) {
                this.elements.versoUserLastName.textContent = lastName || 'Nom';
            }
        }
        
        setInitialState() {
            this.changeColor('blanc');
        }
        
        validateConfiguration() {
            const { firstName, lastName } = this.state.userInfo;
            const isValid = firstName.length > 0 && lastName.length > 0;
            
            this.state.isValid = isValid;
            
            if (this.elements.addToCartBtn) {
                this.elements.addToCartBtn.disabled = !isValid;
            }
            
            console.log(`🔍 INLINE 3.0 - Validation: ${isValid ? 'OK' : 'KO'}`);
        }
    };
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    console.log('🔥 Init INLINE 3.0...');
    window.nfcConfigurator = new window.NFCConfigurator();
});
</script>

<?php wp_footer(); ?>

</body>
</html>