<?php
/**
 * Template Name: Configurateur NFC
 * 
 * Template avec Bootstrap + boutons Elementor/UICore + Traductions
 * Layout Bootstrap avec composants UICore pour les boutons
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier le produit
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 571;

// Charger les classes
require_once get_template_directory() . '/configurator/includes/class-nfc-product.php';
$nfc_product = new NFC_Product_Manager();

if (!$nfc_product->can_be_configured($product_id)) {
    wp_die(__('Produit non configurable', 'nfc-configurator'));
}

// Récupérer les données du produit
$product_data = $nfc_product->get_product_data($product_id);
if (is_wp_error($product_data)) {
    wp_die(__('Erreur produit : ', 'nfc-configurator') . $product_data->get_error_message());
}

// Configuration JavaScript avec traductions
$nfc_config = [
    'productId' => $product_id,
    'variations' => $product_data['variations'],
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_configurator'),
    'homeUrl' => home_url(),
    'cartUrl' => wc_get_cart_url(),
    'themeUrl' => get_template_directory_uri(),
    'i18n' => [
        'loading' => __('Chargement...', 'nfc-configurator'),
        'error' => __('Erreur', 'nfc-configurator'),
        'success' => __('Succès', 'nfc-configurator'),
        'addingToCart' => __('Ajout au panier en cours...', 'nfc-configurator'),
        'selectColor' => __('Choisissez votre support :', 'nfc-configurator'),
        'uploadImage' => __('Insérer une image ou un logo :', 'nfc-configurator'),
        'adjustImage' => __('Ajuster l\'image ou le logo', 'nfc-configurator'),
        'userInfo' => __('Verso :', 'nfc-configurator'),
        'addToCart' => __('Ajouter au panier', 'nfc-configurator'),
        'preview' => __('Aperçu', 'nfc-configurator'),
        'dragImage' => __('Glissez votre image ici', 'nfc-configurator'),
        'qrLoading' => __('Chargement QR...', 'nfc-configurator')
    ]
];

get_header();

// Enqueue styles
wp_enqueue_style('nfc-configurator', get_template_directory_uri() . '/configurator/assets/css/configurator.css', [], '3.2.0');
?>

<div id="primary" class="content-area">

    <!-- Wrapper principal Bootstrap -->
    <div class="nfc-configurator-wrapper" id="nfcConfiguratorWrapper">
        <div class="container-fluid">

            <div class="nfc-configurator vh-hero" id="nfcConfigurator">

                <!-- Layout Bootstrap 50/50 -->
                <div class="row align-items-center">

                    <!-- Colonne Preview (gauche) -->
                    <div class="col-lg-5 p-0">
                        <div
                            class="preview-column d-flex flex-column align-items-center justify-content-center vh-hero">

                            <!-- Header preview -->
                            <div class="preview-header mb-4 text-center">
                                <p class="h5 fw-semibold text-dark mb-0">
                                    <?php echo esc_html__('Aperçu', 'nfc-configurator'); ?>
                                </p>
                            </div>

                            <!-- Stack de cartes -->
                            <div class="cards-stack d-flex flex-column gap-4 align-items-center">

                                <!-- Carte Recto -->
                                <div class="card-preview recto blanc shadow-lg" data-side="recto">
                                    <div
                                        class="card-content w-100 h-100 d-flex align-items-center justify-content-center position-relative">
                                        <!-- Image masquée -->
                                        <div class="image-mask position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                                            id="imageMask">
                                            <div class="image-placeholder text-center opacity-75" id="imagePlaceholder">
                                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/picture.svg"
                                                    alt="Picture">
                                                <p class="mb-0 fw-medium">
                                                    <?php echo esc_html__('Votre image', 'nfc-configurator'); ?>
                                                </p>
                                            </div>
                                            <img id="cardImage" class="d-none position-absolute"
                                                style="top: 50%; left: 50%; transform: translate(-50%, -50%) scale(0.25);"
                                                alt="<?php echo esc_attr__('Image personnalisée', 'nfc-configurator'); ?>">
                                        </div>

                                        <!-- Symbole NFC -->
                                        <div class="nfc-symbol position-absolute" id="nfcSymbol">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nfc-symbol.svg"
                                                alt="NFC Symbol">
                                        </div>
                                    </div>
                                </div>

                                <!-- Carte Verso -->
                                <div class="card-preview verso blanc shadow-lg" data-side="verso">
                                    <div
                                        class="card-content w-100 h-100 d-flex align-items-center justify-content-between p-5">

                                        <!-- NOUVEAU : Zone logo verso (en haut à gauche) -->
                                        <div class="logo-verso-area position-absolute" id="logoVersoArea">
                                            <div class="logo-verso-placeholder text-center d-flex align-items-center justify-content-center w-100 h-100"
                                                id="logoVersoPlaceholder"
                                                style="border: 2px dashed rgba(0,0,0,0.2); border-radius: 6px; opacity: 0.4;">
                                                <span style="font-size: 20px;">🏢</span>
                                            </div>
                                            <img id="logoVersoImage" class="d-none w-100 h-100"
                                                style="object-fit: contain; border-radius: 4px;" alt="Logo verso">
                                        </div>

                                        <!-- Nom à gauche -->
                                        <div class="user-section flex-grow-1">
                                            <div class="user-names d-flex flex-column gap-1">
                                                <div class="user-firstname h4 fw-bold lh-1" id="versoUserFirstName">
                                                    <?php echo esc_html__('Prénom', 'nfc-configurator'); ?>
                                                </div>
                                                <div class="user-lastname h4 fw-bold lh-1" id="versoUserLastName">
                                                    <?php echo esc_html__('Nom', 'nfc-configurator'); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- QR Code à droite -->
                                        <div class="qr-section d-flex flex-column align-items-center gap-2 ms-4">
                                            <div class="qr-code d-flex align-items-center justify-content-center rounded"
                                                id="qrCode">
                                                <div class="qr-loading text-center"
                                                    style="font-size: 12px; color: #666;">
                                                    <?php echo esc_html__('Chargement QR...', 'nfc-configurator'); ?>
                                                </div>
                                            </div>
                                            <div class="qr-website small fw-medium opacity-75">nfcfrance.com</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Colonne Configuration (droite) -->
                    <div class="col-lg-7 config-column vh-hero overflow-y-auto">
                        <div class="p-5 w-100">

                            <!-- Header Bootstrap -->
                            <h1 class="display-5 fw-bold text-dark mb-5 mt-0">
                                <?php echo esc_html__('Personnaliser votre carte :', 'nfc-configurator'); ?>
                            </h1>

                            <div class="config-form col-md-6">

                                <!-- Sélection support -->
                                <div class="config-section mb-4 pb-4 border-bottom">
                                    <h3 class="h5 fw-semibold text-dark mb-3">
                                        <?php echo esc_html__('Choisissez votre support :', 'nfc-configurator'); ?>
                                    </h3>
                                    <div class="support-options d-flex gap-3">
                                        <label
                                            class="support-option d-flex align-items-center gap-2 p-2 rounded-pill cursor-pointer">
                                            <input type="radio" name="card-color" value="blanc" checked class="d-none">
                                            <span class="support-preview rounded-circle border"
                                                style="background: #ffffff; border-color: #ccc !important;"></span>
                                        </label>
                                        <label
                                            class="support-option d-flex align-items-center gap-2 p-2 rounded-pill cursor-pointer">
                                            <input type="radio" name="card-color" value="noir" class="d-none">
                                            <span class="support-preview rounded-circle border"
                                                style="background: #1a1a1a; border-color: #333 !important;"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="accordion" id="accordionRV">

                                    <!-- Section Recto -->
                                    <div class="accordion-item" id="sectionRecto">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseRecto" aria-expanded="true"
                                                aria-controls="collapseRecto">
                                                <h3 class="h5 fw-semibold text-dark m-0">
                                                    <?php echo esc_html__('Recto :', 'nfc-configurator'); ?>
                                                </h3>
                                            </button>
                                        </h2>
                                        <div id="collapseRecto" class="accordion-collapse collapse show"
                                            data-bs-parent="#accordionRV">
                                            <div class="accordion-body p-4">

                                                <!-- Upload -->
                                                <div class="mb-3">
                                                    <label class="form-label fw-medium small">
                                                        <?php echo esc_html__('1. Insérer une image ou un logo :', 'nfc-configurator'); ?>
                                                    </label>

                                                    <div class="d-flex gap-3 align-items-center">
                                                        <div class="upload-zone flex-grow-1 bg-light border p-2 text-center text-muted cursor-pointer"
                                                            id="imageUploadZone">
                                                            <span class="upload-text small">
                                                                <?php echo esc_html__('Sélectionner un fichier...', 'nfc-configurator'); ?>
                                                            </span>
                                                            <input type="file" id="imageInput"
                                                                accept="image/jpeg,image/png,image/svg+xml"
                                                                class="d-none">
                                                        </div>
                                                        <button type="button"
                                                            class="btn btn-outline-danger btn-sm float-end d-none remove-image-btn"
                                                            id="removeImageBtn">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                        <!-- Bouton UICore/Elementor -->
                                                        <button type="button"
                                                            class="upload-button elementor-button elementor-button-link elementor-size-sm elementor-animation-flip p-3 px-4"
                                                            onclick="document.getElementById('imageInput').click()">
                                                            <span class="elementor-button-content-wrapper">
                                                                <span class="ui-btn-anim-wrapp">
                                                                    <span class="elementor-button-text">
                                                                        <?php echo esc_html__('Ajouter', 'nfc-configurator'); ?>
                                                                    </span>
                                                                    <span class="elementor-button-text">
                                                                        <?php echo esc_html__('Ajouter', 'nfc-configurator'); ?>
                                                                    </span>
                                                                </span>
                                                            </span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Contrôles image -->
                                                <div id="imageAdjustStep" class="mt-3">
                                                    <label class="form-label fw-medium small">
                                                        <?php echo esc_html__('2. Ajuster l\'image ou le logo', 'nfc-configurator'); ?>
                                                    </label>

                                                    <div class="image-controls bg-light rounded border"
                                                        id="imageControls">

                                                        <!-- Contrôle taille -->
                                                        <div class="row align-items-center mb-3">
                                                            <div class="col-3">
                                                                <label for="imageScale"
                                                                    class="form-label small fw-medium mb-0">
                                                                    <?php echo esc_html__('Taille :', 'nfc-configurator'); ?>
                                                                </label>
                                                            </div>
                                                            <div class="col-7">
                                                                <input type="range" id="imageScale" min="10" max="200"
                                                                    value="25" class="form-range">
                                                            </div>
                                                            <div class="col-2 text-end">
                                                                <span
                                                                    class="scale-value small fw-semibold text-primary">25%</span>
                                                            </div>
                                                        </div>

                                                        <!-- Position X -->
                                                        <div class="row align-items-center mb-3">
                                                            <div class="col-3">
                                                                <label for="imageX"
                                                                    class="form-label small fw-medium mb-0">
                                                                    <?php echo esc_html__('Position X :', 'nfc-configurator'); ?>
                                                                </label>
                                                            </div>
                                                            <div class="col-7">
                                                                <input type="range" id="imageX" min="-50" max="50"
                                                                    value="0" class="form-range">
                                                            </div>
                                                            <div class="col-2 text-end">
                                                                <span
                                                                    class="position-value position-value-x small fw-semibold text-primary">0</span>
                                                            </div>
                                                        </div>

                                                        <!-- Position Y -->
                                                        <div class="row align-items-center mb-3">
                                                            <div class="col-3">
                                                                <label for="imageY"
                                                                    class="form-label small fw-medium mb-0">
                                                                    <?php echo esc_html__('Position Y :', 'nfc-configurator'); ?>
                                                                </label>
                                                            </div>
                                                            <div class="col-7">
                                                                <input type="range" id="imageY" min="-50" max="50"
                                                                    value="0" class="form-range">
                                                            </div>
                                                            <div class="col-2 text-end">
                                                                <span
                                                                    class="position-value position-value-y small fw-semibold text-primary">0</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Verso -->
                                    <div class="accordion-item" id="sectionVerso">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVerso" aria-expanded="false" aria-controls="collapseVerso">
                                                <h3 class="h5 fw-semibold text-dark m-0">
                                                    <?php echo esc_html__('Verso :', 'nfc-configurator'); ?>
                                                </h3>
                                            </button>
                                        </h2>
                                        <div id="collapseVerso" class="accordion-collapse collapse"
                                            data-bs-parent="#accordionRV">
                                            <div class="accordion-body p-4">

                                                <div class="mb-3 config-step">
                                                    <label class="form-label fw-medium small">3. Insérer un logo
                                                        :</label>

                                                    <div class="d-flex gap-3 align-items-center">
                                                        <div class="upload-zone flex-grow-1 bg-light border p-2 text-center text-muted cursor-pointer"
                                                            id="logoVersoUploadZone">
                                                            <span class="upload-text small">Sélectionner un
                                                                logo...</span>
                                                            <input type="file" id="logoVersoInput"
                                                                accept="image/jpeg,image/png,image/svg+xml"
                                                                class="d-none">
                                                        </div>
                                                        <button type="button"
                                                            class="btn btn-outline-danger btn-sm float-end d-none remove-image-btn"
                                                            id="removeLogoVersoBtn">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                        <!-- Bouton UICore/Elementor -->
                                                        <button type="button"
                                                            class="upload-button elementor-button elementor-button-link elementor-size-sm elementor-animation-flip p-3 px-4"
                                                            onclick="document.getElementById('logoVersoInput').click()">
                                                            <span class="elementor-button-content-wrapper">
                                                                <span class="ui-btn-anim-wrapp">
                                                                    <span class="elementor-button-text">
                                                                        Ajouter </span>
                                                                    <span class="elementor-button-text">
                                                                        Ajouter </span>
                                                                </span>
                                                            </span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Contrôles logo verso (masqués par défaut) -->
                                                <div id="imageAdjustStepVerso" class="mt-3">
                                                    <label class="form-label fw-medium small">4. Ajuster le logo</label>
                                                    <div id="logoVersoControls"
                                                        class="image-controls bg-light rounded border">

                                                        <!-- Taille logo verso -->
                                                        <div class="row align-items-center mb-3">
                                                            <div class="col-3">
                                                                <label for="logoVersoScale"
                                                                    class="form-label small fw-medium mb-0">Taille
                                                                    :</label>
                                                            </div>
                                                            <div class="col-7">
                                                                <input type="range" id="logoVersoScale" min="50"
                                                                    max="150" value="100" class="form-range">
                                                            </div>
                                                            <div class="col-2 text-end">
                                                                <span id="logoVersoScaleValue"
                                                                    class="scale-value small fw-semibold text-primary">100%</span>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <hr class="mt-4 mb-4" />

                                                <div class="mb-3 config-step mt-3 checkbox-wrapper-6">

                                                    <div class="w-100">
                                                        <label class="form-label fw-medium small">5. Renseigner vos
                                                            informations (optionnel)                                                    </label>    
                                                        <div class="checkbox-wrapper-3 float-end">
                                                            <input class="tgl tgl-light" id="checkboxInformations" checked="" type="checkbox"/>
                                                            <label for="checkboxInformations" class="toggle"><span></span></label>
                                                        </div>
                                                    </div>


                                                    <div class="row">
                                                        <div class="col-6">
                                                            <input type="text" id="lastName"
                                                                class="form-control rounded-pill bg-light p-2 px-3"
                                                                placeholder="<?php echo esc_attr__('Nom', 'nfc-configurator'); ?>"
                                                                required>
                                                        </div>
                                                        <div class="col-6">
                                                            <input type="text" id="firstName"
                                                                class="form-control rounded-pill bg-light p-2 px-3"
                                                                placeholder="<?php echo esc_attr__('Prénom', 'nfc-configurator'); ?>"
                                                                required>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Bouton Ajout Panier UICore/Elementor -->
                                <div class="config-section mt-4">
                                    <button type="button"
                                        class="add-to-cart-btn elementor-button elementor-button-link w-100 elementor-animation-flip"
                                        id="addToCartBtn">
                                        <span class="elementor-button-content-wrapper">
                                            <span class="ui-btn-anim-wrapp">
                                                <span class="elementor-button-text">
                                                    <?php echo esc_html__('Ajouter au panier', 'nfc-configurator'); ?>
                                                </span>
                                                <span class="elementor-button-text">
                                                    <?php echo esc_html__('Ajouter au panier', 'nfc-configurator'); ?>
                                                </span>
                                            </span>
                                        </span>
                                    </button>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <!-- Loading overlay Bootstrap -->
                <div class="loading-overlay position-fixed top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center"
                    id="loadingOverlay" style="background: rgba(0, 0, 0, 0.7); z-index: 9999;">
                    <div class="loading-content bg-white rounded-3 shadow p-4 text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span
                                class="visually-hidden"><?php echo esc_html__('Chargement...', 'nfc-configurator'); ?></span>
                        </div>
                        <p class="mb-0 fw-medium">
                            <?php echo esc_html__('Ajout au panier en cours...', 'nfc-configurator'); ?>
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>

<!-- Configuration JavaScript avec traductions -->
<script type="text/javascript">
    window.nfcConfig = <?php echo json_encode($nfc_config, JSON_UNESCAPED_UNICODE); ?>;
    console.log('🎛️ Configuration chargée:', window.nfcConfig);
</script>

<!-- Scripts configurateur -->
<script src="<?php echo get_template_directory_uri(); ?>/configurator/assets/js/canvas-handler.js?v=3.2"></script>
<script src="<?php echo get_template_directory_uri(); ?>/configurator/assets/js/wc-integration.js?v=3.2"></script>
<script src="<?php echo get_template_directory_uri(); ?>/configurator/assets/js/configurator.js?v=3.2"></script>

<?php get_footer(); ?>