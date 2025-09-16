<?php
/**
 * Dashboard - vCard Edit
 * Architecture standardisée NFC France + Multi-vCard
 */

// 1. VÉRIFICATIONS SÉCURITÉ
if (!defined('ABSPATH')) exit;
if (!is_user_logged_in()) wp_redirect(home_url('/login'));

// 2. LOGIQUE MÉTIER
$user_id = get_current_user_id();
$current_user = wp_get_current_user();

// Récupérer les vCards de l'utilisateur
$user_vcards = [];
$posts = get_posts([
    'post_type' => 'virtual_card',
    'post_status' => 'publish', 
    'author' => $user_id,
    'numberposts' => -1
]);

foreach ($posts as $post) {
    $user_vcards[] = $post;
}

// Détection des états
if (empty($user_vcards)) {
    include 'partials/no-products-state.php';
    return;
}

// Déterminer quelle vCard éditer (LOGIQUE MULTI-VCARD)
$requested_vcard_id = isset($_GET['vcard_id']) ? intval($_GET['vcard_id']) : null;
$target_vcard = null;

if ($requested_vcard_id) {
    // Mode multi-vCard : vérifier que l'utilisateur possède cette vCard
    foreach ($user_vcards as $user_vcard) {
        if ($user_vcard->ID == $requested_vcard_id) {
            $target_vcard = $user_vcard;
            break;
        }
    }
    
    if (!$target_vcard) {
        echo '<div class="alert alert-danger mt-3">';
        echo '<h5><i class="fas fa-exclamation-triangle me-2"></i>Accès refusé</h5>';
        echo '<p>Cette vCard n\'existe pas ou ne vous appartient pas.</p>';
        echo '<a href="?page=cards-list" class="btn btn-primary">← Retour à mes cartes</a>';
        echo '</div>';
        return;
    }
} else {
    // Mode classique : utiliser la vCard globale ou première vCard
    global $nfc_vcard;
    if ($nfc_vcard) {
        $target_vcard = $nfc_vcard;
    } else {
        $target_vcard = $user_vcards[0];
    }
}

$vcard_id = $target_vcard->ID;

// Récupérer les données de la vCard
$vcard_fields = [
    'firstname' => get_post_meta($vcard_id, 'firstname', true) ?: $current_user->first_name,
    'lastname' => get_post_meta($vcard_id, 'lastname', true) ?: $current_user->last_name,
    'society' => get_post_meta($vcard_id, 'society', true) ?: '',
    'service' => get_post_meta($vcard_id, 'service', true) ?: '',
    'post' => get_post_meta($vcard_id, 'post', true) ?: '',
    'email' => get_post_meta($vcard_id, 'email', true) ?: $current_user->user_email,
    'phone' => get_post_meta($vcard_id, 'phone', true) ?: '',
    'mobile' => get_post_meta($vcard_id, 'mobile', true) ?: '',
    'website' => get_post_meta($vcard_id, 'website', true) ?: '',
    'linkedin' => get_post_meta($vcard_id, 'linkedin', true) ?: '',
    'twitter' => get_post_meta($vcard_id, 'twitter', true) ?: '',
    'instagram' => get_post_meta($vcard_id, 'instagram', true) ?: '',
    'facebook' => get_post_meta($vcard_id, 'facebook', true) ?: '',
    'pinterest' => get_post_meta($vcard_id, 'pinterest', true) ?: '',
    'youtube' => get_post_meta($vcard_id, 'youtube', true) ?: '',
    'description' => get_post_meta($vcard_id, 'description', true) ?: '',
    'profile_picture' => function_exists('get_field') ? get_field('profile_picture', $vcard_id) : get_post_meta($vcard_id, 'profile_picture', true),
    'cover_image' => function_exists('get_field') ? get_field('cover_image', $vcard_id) : get_post_meta($vcard_id, 'cover_image', true),
    'address' => get_post_meta($vcard_id, 'address', true) ?: '',
    'additional' => get_post_meta($vcard_id, 'additional', true) ?: '',
    'postcode' => get_post_meta($vcard_id, 'postcode', true) ?: '',
    'city' => get_post_meta($vcard_id, 'city', true) ?: '',
    'country' => get_post_meta($vcard_id, 'country', true) ?: 'France',
    'custom_url' => get_post_meta($vcard_id, 'custom_url', true) ?: '',
    'redirect_mode' => get_post_meta($vcard_id, 'redirect_mode', true) ?: 'vcard'
];

// Variables utiles
$public_url = get_permalink($vcard_id);
$has_custom_url = !empty($vcard_fields['custom_url']) && $vcard_fields['redirect_mode'] === 'custom';
$is_multi_vcard_mode = !empty($requested_vcard_id);
$initials = substr($vcard_fields['firstname'], 0, 1) . substr($vcard_fields['lastname'], 0, 1);

// Variables pour JavaScript (selon standard) - SANS wp_localize_script
$vcard_edit_data = [
    'vcard_id' => $vcard_id,
    'user_id' => $user_id,
    'vcard_fields' => $vcard_fields,
    'is_multi_vcard_mode' => $is_multi_vcard_mode,
    'public_url' => $public_url,
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'upload_url' => admin_url('admin-ajax.php'),
    'max_file_size' => 5 * 1024 * 1024,
    'allowed_types' => ['image/jpeg', 'image/png', 'image/svg+xml'],
    'auto_save_interval' => 2000,
    'redirect_after_save' => $is_multi_vcard_mode ? '?page=cards-list' : null
];

?>

<!-- CSS DIRECT -->
<link rel="stylesheet" href="<?= plugin_dir_url(__FILE__) ?>../../assets/css/vcard-edit.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">

<!-- BREADCRUMB SI MULTI-VCARD -->
<?php if ($is_multi_vcard_mode): ?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="?page=cards-list"><i class="fas fa-arrow-left me-1"></i>Mes cartes</a>
        </li>
        <li class="breadcrumb-item active">Modifier vCard</li>
    </ol>
</nav>
<?php endif; ?>

<div class="dashboard-vcard-editor">
    
    <!-- HEADER DE PAGE AVEC BOUTON SAVE EN HAUT -->
    <div class="content-header mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="mb-2 mb-md-0">
                <h1 class="h3 mb-1">
                    <i class="fas fa-id-card text-primary me-2"></i>
                    <?= $is_multi_vcard_mode ? 'Modifier la vCard' : 'Modifier ma vCard' ?>
                </h1>
                <p class="text-muted mb-0">
                    <?= $is_multi_vcard_mode ? 'vCard ID: ' . $vcard_id . ' - ' : '' ?>
                    Personnalisez vos informations de contact
                    <?php if ($has_custom_url): ?>
                        <span class="badge bg-warning ms-2">
                            <i class="fas fa-external-link-alt me-1"></i>Mode redirection
                        </span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= esc_url($public_url) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt me-2"></i>Voir ma vCard
                </a>
                <!-- BOUTON SAVE EN HAUT -->
                <button type="button" class="btn btn-primary" id="save-btn-header">
                    <i class="fas fa-save me-2"></i>Enregistrer
                </button>
                <?php if ($is_multi_vcard_mode): ?>
                    <a href="?page=cards-list" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- STATUT DE SAUVEGARDE -->
    <div id="save-status-bar" class="alert alert-success d-none mb-4">
        <i class="fas fa-check-circle me-2"></i>
        <span id="save-status-text">Sauvegardé</span>
    </div>

    <!-- LAYOUT 2 COLONNES -->
    <div class="row g-4">

        <!-- FORMULAIRE PRINCIPAL (70%) -->
        <div class="col-xl-8">
            <form id="vcard-edit-form" method="post" enctype="multipart/form-data" novalidate>
                
                <!-- Champs cachés -->
                <input type="hidden" name="vcard_id" value="<?= esc_attr($vcard_id) ?>">
                <input type="hidden" name="action" value="save_vcard_data">
                <input type="hidden" name="nonce" value="<?= wp_create_nonce('nfc_dashboard_nonce') ?>">

                <!-- SECTION 1: INFORMATIONS PERSONNELLES -->
                <div class="editor-section card mb-4">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">
                            <i class="fas fa-user text-primary me-2"></i>
                            Informations personnelles
                        </h4>
                        <p class="text-muted mb-0 small">Vos informations de base</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="firstname" class="form-label">
                                    Prénom <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="firstname" 
                                       name="firstname" 
                                       value="<?= esc_attr($vcard_fields['firstname']) ?>" 
                                       required
                                       data-field="firstname">
                                <div class="invalid-feedback">Veuillez saisir votre prénom</div>
                            </div>
                            <div class="col-md-6">
                                <label for="lastname" class="form-label">
                                    Nom <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="lastname" 
                                       name="lastname" 
                                       value="<?= esc_attr($vcard_fields['lastname']) ?>" 
                                       required
                                       data-field="lastname">
                                <div class="invalid-feedback">Veuillez saisir votre nom</div>
                            </div>
                            <div class="col-md-6">
                                <label for="post" class="form-label">Poste / Fonction</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="post" 
                                       name="post" 
                                       value="<?= esc_attr($vcard_fields['post']) ?>" 
                                       placeholder="Ex: Développeur, Commercial..."
                                       data-field="post">
                            </div>
                            <div class="col-md-6">
                                <label for="service" class="form-label">Département / Service</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="service" 
                                       name="service" 
                                       value="<?= esc_attr($vcard_fields['service']) ?>" 
                                       placeholder="Ex: IT, Ventes, Marketing..."
                                       data-field="service">
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description / Bio</label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="3" 
                                          placeholder="Décrivez-vous en quelques mots..."
                                          data-field="description"><?= esc_textarea($vcard_fields['description']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: CONTACT PROFESSIONNEL -->
                <div class="editor-section card mb-4">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">
                            <i class="fas fa-phone text-primary me-2"></i>
                            Contact professionnel
                        </h4>
                        <p class="text-muted mb-0 small">Moyens de contact principal</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= esc_attr($vcard_fields['email']) ?>" 
                                       required
                                       data-field="email">
                                <div class="invalid-feedback">Veuillez saisir un email valide</div>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?= esc_attr($vcard_fields['phone']) ?>" 
                                       placeholder="01 23 45 67 89"
                                       data-field="phone">
                            </div>
                            <div class="col-md-6">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="mobile" 
                                       name="mobile" 
                                       value="<?= esc_attr($vcard_fields['mobile']) ?>" 
                                       placeholder="06 12 34 56 78"
                                       data-field="mobile">
                            </div>
                            <div class="col-md-6">
                                <label for="website" class="form-label">Site web personnel</label>
                                <input type="url" 
                                       class="form-control" 
                                       id="website" 
                                       name="website" 
                                       value="<?= esc_attr($vcard_fields['website']) ?>" 
                                       placeholder="https://monsite.com"
                                       data-field="website">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: ENTREPRISE -->
                <div class="editor-section card mb-4">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">
                            <i class="fas fa-building text-primary me-2"></i>
                            Entreprise
                        </h4>
                        <p class="text-muted mb-0 small">Informations de votre société</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="society" class="form-label">Nom de l'entreprise</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="society" 
                                       name="society" 
                                       value="<?= esc_attr($vcard_fields['society']) ?>" 
                                       placeholder="Ex: NFC France"
                                       data-field="society">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: ADRESSE -->
                <div class="editor-section card mb-4">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            Adresse
                        </h4>
                        <p class="text-muted mb-0 small">Localisation géographique</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="address" class="form-label">Adresse</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="address" 
                                       name="address" 
                                       value="<?= esc_attr($vcard_fields['address']) ?>" 
                                       placeholder="123 rue de la Paix"
                                       data-field="address">
                            </div>
                            <div class="col-12">
                                <label for="additional" class="form-label">Complément d'adresse</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="additional" 
                                       name="additional" 
                                       value="<?= esc_attr($vcard_fields['additional']) ?>" 
                                       placeholder="Bâtiment A, 3ème étage..."
                                       data-field="additional">
                            </div>
                            <div class="col-md-4">
                                <label for="postcode" class="form-label">Code postal</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="postcode" 
                                       name="postcode" 
                                       value="<?= esc_attr($vcard_fields['postcode']) ?>" 
                                       placeholder="75001"
                                       data-field="postcode">
                            </div>
                            <div class="col-md-4">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="city" 
                                       name="city" 
                                       value="<?= esc_attr($vcard_fields['city']) ?>" 
                                       placeholder="Paris"
                                       data-field="city">
                            </div>
                            <div class="col-md-4">
                                <label for="country" class="form-label">Pays</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="country" 
                                       name="country" 
                                       value="<?= esc_attr($vcard_fields['country']) ?>" 
                                       placeholder="France"
                                       data-field="country">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: RÉSEAUX SOCIAUX -->
                <div class="editor-section card mb-4">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">
                            <i class="fab fa-linkedin text-primary me-2"></i>
                            Réseaux sociaux
                        </h4>
                        <p class="text-muted mb-0 small">Vos profils sur les réseaux</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="linkedin" class="form-label">
                                    <i class="fab fa-linkedin me-1"></i> LinkedIn
                                </label>
                                <input type="url" 
                                       class="form-control" 
                                       id="linkedin" 
                                       name="linkedin" 
                                       value="<?= esc_attr($vcard_fields['linkedin']) ?>" 
                                       placeholder="https://linkedin.com/in/votre-profil"
                                       data-field="linkedin">
                            </div>
                            <div class="col-md-6">
                                <label for="twitter" class="form-label">
                                    <i class="fab fa-twitter me-1"></i> Twitter / X
                                </label>
                                <input type="url" 
                                       class="form-control" 
                                       id="twitter" 
                                       name="twitter" 
                                       value="<?= esc_attr($vcard_fields['twitter']) ?>" 
                                       placeholder="https://twitter.com/votre-handle"
                                       data-field="twitter">
                            </div>
                            <div class="col-md-6">
                                <label for="facebook" class="form-label">
                                    <i class="fab fa-facebook me-1"></i> Facebook
                                </label>
                                <input type="url" 
                                       class="form-control" 
                                       id="facebook" 
                                       name="facebook" 
                                       value="<?= esc_attr($vcard_fields['facebook']) ?>" 
                                       placeholder="https://facebook.com/votre-page"
                                       data-field="facebook">
                            </div>
                            <div class="col-md-6">
                                <label for="instagram" class="form-label">
                                    <i class="fab fa-instagram me-1"></i> Instagram
                                </label>
                                <input type="url" 
                                       class="form-control" 
                                       id="instagram" 
                                       name="instagram" 
                                       value="<?= esc_attr($vcard_fields['instagram']) ?>" 
                                       placeholder="https://instagram.com/votre-profil"
                                       data-field="instagram">
                            </div>
                            <div class="col-md-6">
                                <label for="youtube" class="form-label">
                                    <i class="fab fa-youtube me-1"></i> YouTube
                                </label>
                                <input type="url" 
                                       class="form-control" 
                                       id="youtube" 
                                       name="youtube" 
                                       value="<?= esc_attr($vcard_fields['youtube']) ?>" 
                                       placeholder="https://youtube.com/c/votre-chaine"
                                       data-field="youtube">
                            </div>
                            <div class="col-md-6">
                                <label for="pinterest" class="form-label">
                                    <i class="fab fa-pinterest me-1"></i> Pinterest
                                </label>
                                <input type="url" 
                                       class="form-control" 
                                       id="pinterest" 
                                       name="pinterest" 
                                       value="<?= esc_attr($vcard_fields['pinterest']) ?>" 
                                       placeholder="https://pinterest.com/votre-profil"
                                       data-field="pinterest">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 6: COMPORTEMENT NFC/QR -->
                <div class="editor-section card mb-4">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">
                            <i class="fas fa-link text-primary me-2"></i>
                            Comportement scan NFC
                        </h4>
                        <p class="text-muted mb-0 small">Action lors du scan de votre carte</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="enable_custom_url" 
                                           name="enable_custom_url" 
                                           <?= $vcard_fields['redirect_mode'] === 'custom' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="enable_custom_url">
                                        <strong>Activer la redirection personnalisée</strong>
                                        <br><small class="text-muted">
                                            Au lieu d'afficher votre vCard, rediriger vers une URL personnalisée
                                        </small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12" id="custom_url_section" style="<?= $vcard_fields['redirect_mode'] === 'custom' ? '' : 'display:none;' ?>">
                                <label for="custom_url" class="form-label">URL de redirection</label>
                                <input type="url" 
                                       class="form-control" 
                                       id="custom_url" 
                                       name="custom_url" 
                                       value="<?= esc_attr($vcard_fields['custom_url']) ?>" 
                                       placeholder="https://monsite.com/ma-page"
                                       data-field="custom_url">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    URL complète vers laquelle rediriger (doit commencer par https://)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOUTONS D'ACTION BAS -->
                <div class="editor-actions">
                    <div class="d-flex gap-3 justify-content-between flex-wrap">
                        <div>
                            <?php if ($is_multi_vcard_mode): ?>
                                <a href="?page=cards-list" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Retour à mes cartes
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="previewVCard()">
                                <i class="fas fa-eye me-2"></i>Aperçu
                            </button>
                            <button type="submit" class="btn btn-primary" id="save-btn">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <!-- SIDEBAR PREVIEW (30%) -->
        <div class="col-xl-4">
            
            <!-- PHOTO DE PROFIL -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-camera me-2"></i>Photo de profil
                    </h5>
                    <small class="text-muted">Votre photo pour la vCard</small>
                </div>
                <div class="card-body text-center">
                    <div class="profile-image-container mb-3" id="profile-preview-container">
                        <div id="profile-preview">
                            <?php if (!empty($vcard_fields['profile_picture'])): ?>
                                <img src="<?= esc_url($vcard_fields['profile_picture']) ?>"
                                    class="img-fluid rounded-circle border"
                                    style="width: 120px; height: 120px; object-fit: cover;" alt="Photo de profil">
                            <?php else: ?>
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center border"
                                    style="width: 120px; height: 120px; margin: 0 auto;">
                                    <span class="text-primary fw-bold fs-2"><?= esc_html($initials) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <input type="file" id="profile_image_input" name="profile_picture" accept="image/jpeg,image/png,image/gif" style="display: none;">

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('profile_image_input').click()">
                            <i class="fas fa-upload me-2"></i>
                            <?= !empty($vcard_fields['profile_picture']) ? 'Changer la photo' : 'Ajouter une photo' ?>
                        </button>
                        <?php if (!empty($vcard_fields['profile_picture'])): ?>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeProfileImage()">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        <?php endif; ?>
                    </div>

                    <small class="text-muted d-block mt-2">
                        <strong>Formats :</strong> JPG, PNG, GIF<br>
                        <strong>Taille max :</strong> 2MB<br>
                        <strong>Recommandé :</strong> 400x400px
                    </small>
                </div>
            </div>

            <!-- PHOTO DE COUVERTURE -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-image me-2"></i>Photo de couverture
                    </h5>
                    <small class="text-muted">Image d'arrière-plan de votre vCard</small>
                </div>
                <div class="card-body text-center">
                    <div class="cover-image-container mb-3" id="cover-preview-container">
                        <div id="cover-preview">
                            <?php if (!empty($vcard_fields['cover_image'])): ?>
                                <img src="<?= esc_url($vcard_fields['cover_image']) ?>"
                                    class="img-fluid rounded border"
                                    style="width: 100%; max-height: 120px; object-fit: cover;" alt="Image de couverture">
                            <?php else: ?>
                                <div class="bg-light rounded border d-flex align-items-center justify-content-center"
                                    style="width: 100%; height: 120px;">
                                    <div class="text-center">
                                        <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                        <div class="text-muted small">Aucune image de couverture</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <input type="file" id="cover_image_input" name="cover_image" accept="image/jpeg,image/png,image/gif" style="display: none;">

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('cover_image_input').click()">
                            <i class="fas fa-upload me-2"></i>
                            <?= !empty($vcard_fields['cover_image']) ? 'Changer l\'image' : 'Ajouter une image' ?>
                        </button>
                        <?php if (!empty($vcard_fields['cover_image'])): ?>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCoverImage()">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        <?php endif; ?>
                    </div>

                    <small class="text-muted d-block mt-2">
                        <strong>Formats :</strong> JPG, PNG, GIF<br>
                        <strong>Taille max :</strong> 2MB<br>
                        <strong>Recommandé :</strong> 800x300px
                    </small>
                </div>
            </div>

            <!-- URL PUBLIQUE -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-link me-2"></i>URL publique
                    </h5>
                    <small class="text-muted">Lien vers votre vCard</small>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" id="vcard-public-url" class="form-control form-control-sm" value="<?= esc_url($public_url) ?>" readonly>
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyToClipboard('<?= esc_js($public_url) ?>')" title="Copier l'URL">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button class="btn btn-outline-primary btn-sm" type="button" onclick="window.open('<?= esc_js($public_url) ?>', '_blank')" title="Ouvrir">
                            <i class="fas fa-external-link-alt"></i>
                        </button>
                    </div>
                    
                    <?php if ($has_custom_url): ?>
                        <div class="mt-2">
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Cette URL redirige vers : <strong><?= esc_html($vcard_fields['custom_url']) ?></strong>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PREVIEW TEMPS RÉEL -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i>Aperçu temps réel
                    </h5>
                    <div class="preview-controls mt-2">
                        <button class="btn btn-sm btn-outline-light" id="toggle-device">
                            <i class="fas fa-mobile-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-light" id="open-public">
                            <i class="fas fa-external-link-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="vcard-preview" class="vcard-preview-frame">
                        <div class="preview-card">
                            <div class="preview-header">
                                <div class="preview-avatar">
                                    <?php if ($vcard_fields['profile_picture']): ?>
                                        <img src="<?= esc_url($vcard_fields['profile_picture']) ?>" alt="Photo de profil">
                                    <?php else: ?>
                                        <span class="initials"><?= esc_html($initials) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="preview-content">
                                <h3 class="preview-name"><?= esc_html(trim($vcard_fields['firstname'] . ' ' . $vcard_fields['lastname'])) ?></h3>
                                <?php if ($vcard_fields['post']): ?>
                                    <p class="preview-job"><?= esc_html($vcard_fields['post']) ?></p>
                                <?php endif; ?>
                                <?php if ($vcard_fields['society']): ?>
                                    <p class="preview-company"><?= esc_html($vcard_fields['society']) ?></p>
                                <?php endif; ?>
                                <div class="preview-contacts">
                                    <?php if ($vcard_fields['email']): ?>
                                        <div class="preview-contact-item">
                                            <i class="fas fa-envelope"></i>
                                            <span><?= esc_html($vcard_fields['email']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($vcard_fields['phone']): ?>
                                        <div class="preview-contact-item">
                                            <i class="fas fa-phone"></i>
                                            <span><?= esc_html($vcard_fields['phone']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <div class="save-status" id="save-status">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Synchronisé</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- SCRIPTS DIRECT (pas de wp_enqueue) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="<?= plugin_dir_url(__FILE__) ?>../../assets/js/dashboard/vcard-editor-manager.js?v=<?= time() ?>"></script>

<script>
// Configuration JavaScript DIRECTE
window.vcardEditConfig = <?= json_encode($vcard_edit_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

// Fonctions globales pour compatibilité
function previewVCard() {
    window.open(window.vcardEditConfig.public_url, '_blank');
}

function resetForm() {
    if (confirm('Êtes-vous sûr de vouloir annuler toutes vos modifications ?')) {
        location.reload();
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Notification de succès
        const notification = document.createElement('div');
        notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            URL copiée dans le presse-papiers !
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }).catch(function() {
        alert('Impossible de copier l\'URL');
    });
}

// Sync des boutons save
document.addEventListener('DOMContentLoaded', function() {
    const saveBtn = document.getElementById('save-btn');
    const saveBtnHeader = document.getElementById('save-btn-header');
    
    if (saveBtn && saveBtnHeader) {
        saveBtnHeader.addEventListener('click', function() {
            saveBtn.click();
        });
    }
});
</script>