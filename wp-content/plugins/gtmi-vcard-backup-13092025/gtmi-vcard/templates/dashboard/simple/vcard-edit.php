<?php
/**
 * Template: Édition vCard FINALISÉE avec Custom URL
 * 
 * Fichier: templates/dashboard/simple/vcard-edit.php
 * Formulaire d'édition complet avec upload image, preview temps réel, Custom URL
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables globales disponibles
global $nfc_vcard, $nfc_current_page;
$vcard = $nfc_vcard;

// Récupérer les données de la vCard
$vcard_id = $vcard->ID;
$user_id = get_current_user_id();
$current_user = wp_get_current_user();

// Récupérer les champs de la vCard avec fallbacks
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
    // Custom URL
    'custom_url' => get_post_meta($vcard_id, 'custom_url', true) ?: '',
    'redirect_mode' => get_post_meta($vcard_id, 'redirect_mode', true) ?: 'vcard'
];

// Variables utiles
$public_url = get_permalink($vcard_id);
$has_custom_url = !empty($vcard_fields['custom_url']) && $vcard_fields['redirect_mode'] === 'custom';
$initials = substr($vcard_fields['firstname'], 0, 1) . substr($vcard_fields['lastname'], 0, 1);
?>

<div class="nfc-page-content">

    <!-- HEADER DE PAGE -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <h2 class="h4 text-dark mb-1">
                        <i class="fas fa-id-card text-primary me-2"></i>
                        Modifier ma vCard
                    </h2>
                    <p class="text-muted mb-0">
                        Personnalisez vos informations de contact
                        <?php if ($has_custom_url): ?>
                            <span class="badge bg-warning ms-2">
                                <i class="fas fa-external-link-alt me-1"></i>Mode redirection
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?php echo esc_url($public_url); ?>" target="_blank"
                        class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-external-link-alt me-2"></i>
                        Voir ma vCard
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetForm()">
                        <i class="fas fa-undo me-2"></i>
                        Annuler
                    </button>
                    <button type="submit" form="nfc-vcard-form" id="saveVCard" class="btn btn-secondary btn-sm">
                        <i class="fas fa-save me-2"></i>
                        <span class="btn-text">Enregistrer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- FORMULAIRE PRINCIPAL -->
    <div class="row">
        <div class="col-lg-8">
            <form id="nfc-vcard-form" method="post" enctype="multipart/form-data">

                <!-- CONFIGURATION COMPORTEMENT EN PREMIER -->
                <div class="dashboard-card mb-4">
                    <div class="card-header p-3">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Configuration du comportement
                        </h3>
                        <small class="text-muted">Que se passe-t-il quand on scanne votre carte ?</small>
                    </div>
                    <div class="p-4">

                        <div class="mb-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="redirect_mode" id="mode_vcard"
                                    value="vcard" <?php checked($vcard_fields['redirect_mode'], 'vcard'); ?>>
                                <label class="form-check-label fw-medium" for="mode_vcard">
                                    <i class="fas fa-id-card text-primary me-2"></i>
                                    <strong>Afficher ma vCard</strong> (recommandé)
                                    <br>
                                    <small class="text-muted">Les visiteurs verront vos informations de contact
                                        formatées</small>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="redirect_mode" id="mode_custom"
                                    value="custom" <?php checked($vcard_fields['redirect_mode'], 'custom'); ?>>
                                <label class="form-check-label fw-medium" for="mode_custom">
                                    <i class="fas fa-external-link-alt text-warning me-2"></i>
                                    <strong>Rediriger vers une URL personnalisée</strong>
                                    <br>
                                    <small class="text-muted">Les visiteurs seront redirigés vers votre site web ou page
                                        spécifique</small>
                                </label>
                            </div>
                        </div>

                        <div id="custom-url-section"
                            class="<?php echo ($vcard_fields['redirect_mode'] === 'custom') ? '' : 'd-none'; ?>">
                            <label class="form-label fw-medium">URL de redirection</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-link"></i>
                                </span>
                                <input type="url" name="custom_url" id="custom_url_input" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['custom_url']); ?>"
                                    placeholder="https://monsite.com/contact">
                                <button class="btn btn-outline-secondary" type="button" onclick="testCustomUrl()"
                                    title="Tester l'URL">
                                    <i class="fas fa-external-link-alt"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                En mode redirection, vos informations de contact ne seront pas affichées.
                                Assurez-vous que l'URL est correcte et accessible.
                            </div>
                        </div>

                        <!-- Preview du comportement -->
                        <div id="behavior-preview" class="mt-4">
                            <!-- Sera rempli par JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- INFORMATIONS PERSONNELLES -->
                <div class="dashboard-card mb-4">
                    <div class="card-header p-3">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-user me-2"></i>
                            Informations personnelles
                        </h3>
                        <small class="text-muted">Vos coordonnées principales</small>
                    </div>
                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium required">Prénom</label>
                                <input type="text" name="firstname" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['firstname']); ?>"
                                    placeholder="Votre prénom" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium required">Nom</label>
                                <input type="text" name="lastname" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['lastname']); ?>" placeholder="Votre nom"
                                    required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium required">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['email']); ?>"
                                    placeholder="votre@email.com" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Téléphone</label>
                                <input type="tel" name="phone" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['phone']); ?>"
                                    placeholder="+33 1 23 45 67 89">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Mobile</label>
                                <input type="tel" name="mobile" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['mobile']); ?>"
                                    placeholder="+33 6 12 34 56 78">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Site web</label>
                                <input type="url" name="website" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['website']); ?>"
                                    placeholder="https://monsite.com">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INFORMATIONS PROFESSIONNELLES -->
                <div class="dashboard-card mb-4">
                    <div class="card-header p-3">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-briefcase me-2"></i>
                            Informations professionnelles
                        </h3>
                        <small class="text-muted">Votre activité et entreprise</small>
                    </div>
                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Entreprise</label>
                                <input type="text" name="society" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['society']); ?>"
                                    placeholder="Nom de votre entreprise">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Service</label>
                                <input type="text" name="service" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['service']); ?>"
                                    placeholder="Département ou service">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Poste / Fonction</label>
                            <input type="text" name="post" class="form-control"
                                value="<?php echo esc_attr($vcard_fields['post']); ?>"
                                placeholder="Votre titre professionnel">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" class="form-control" rows="4"
                                placeholder="Décrivez votre activité, vos compétences, vos services..."><?php echo esc_textarea($vcard_fields['description']); ?></textarea>
                            <div class="form-text">Cette description apparaîtra sur votre vCard publique.</div>
                        </div>
                    </div>
                </div>

                <!-- RÉSEAUX SOCIAUX -->
                <div class="dashboard-card mb-4">
                    <div class="card-header p-3">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-share-alt me-2"></i>
                            Réseaux sociaux
                        </h3>
                        <small class="text-muted">Vos profils sur les réseaux sociaux</small>
                    </div>
                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">
                                    <i class="fab fa-linkedin text-primary me-2"></i>LinkedIn
                                </label>
                                <input type="url" name="linkedin" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['linkedin']); ?>"
                                    placeholder="https://linkedin.com/in/votre-profil">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">
                                    <i class="fab fa-twitter text-info me-2"></i>Twitter / X
                                </label>
                                <input type="url" name="twitter" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['twitter']); ?>"
                                    placeholder="https://twitter.com/votre-compte">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">
                                    <i class="fab fa-facebook text-primary me-2"></i>Facebook
                                </label>
                                <input type="url" name="facebook" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['facebook']); ?>"
                                    placeholder="https://facebook.com/votre-page">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">
                                    <i class="fab fa-instagram text-danger me-2"></i>Instagram
                                </label>
                                <input type="url" name="instagram" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['instagram']); ?>"
                                    placeholder="https://instagram.com/votre-compte">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">
                                    <i class="fab fa-pinterest text-danger me-2"></i>Pinterest
                                </label>
                                <input type="url" name="pinterest" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['pinterest']); ?>"
                                    placeholder="https://pinterest.com/votre-compte">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">
                                    <i class="fab fa-youtube text-danger me-2"></i>YouTube
                                </label>
                                <input type="url" name="youtube" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['youtube']); ?>"
                                    placeholder="https://youtube.com/votre-chaine">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ADRESSE -->
                <div class="dashboard-card mb-4">
                    <div class="card-header p-3">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Adresse
                        </h3>
                        <small class="text-muted">Votre localisation géographique</small>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Adresse</label>
                            <input type="text" name="address" class="form-control"
                                value="<?php echo esc_attr($vcard_fields['address']); ?>"
                                placeholder="Numéro et nom de rue">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Complément d'adresse</label>
                            <input type="text" name="additional" class="form-control"
                                value="<?php echo esc_attr($vcard_fields['additional']); ?>"
                                placeholder="Bâtiment, étage, appartement...">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-medium">Code postal</label>
                                <input type="text" name="postcode" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['postcode']); ?>" placeholder="75001">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-medium">Ville</label>
                                <input type="text" name="city" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['city']); ?>" placeholder="Paris">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-medium">Pays</label>
                                <input type="text" name="country" class="form-control"
                                    value="<?php echo esc_attr($vcard_fields['country']); ?>" placeholder="France">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INPUTS CACHÉS POUR LES IMAGES -->
                <!-- Ces inputs permettent de synchroniser les uploads de la sidebar avec le formulaire -->
                <input type="hidden" id="profile_picture_data" name="profile_picture_data" value="">
                <input type="hidden" id="cover_image_data" name="cover_image_data" value="">
                <input type="hidden" id="profile_picture_action" name="profile_picture_action" value="">
                <input type="hidden" id="cover_image_action" name="cover_image_action" value="">

                <!-- Input file caché pour profile (synchronisé avec celui de la sidebar) -->
                <input type="file" id="hidden_profile_input" name="profile_picture"
                    accept="image/jpeg,image/png,image/gif" style="display: none;">

                <!-- Input file caché pour cover (synchronisé avec celui de la sidebar) -->
                <input type="file" id="hidden_cover_input" name="cover_image" accept="image/jpeg,image/png,image/gif"
                    style="display: none;">

            </form>
        </div>


        <!-- SIDEBAR DROITE -->
        <div class="col-lg-4">

            <!-- PHOTO DE PROFIL -->
            <div class="dashboard-card mb-4">
                <div class="card-header p-3">
                    <h3 class="h6 mb-0">
                        <i class="fas fa-camera me-2"></i>
                        Photo de profil
                    </h3>
                    <small class="text-muted">Votre photo pour la vCard</small>
                </div>
                <div class="p-4 text-center">
                    <!-- 🔥 NOUVEAU : Container fixe avec ID -->
                    <div class="profile-image-container mb-3" id="profile-preview-container">
                        <!-- 🔥 NOUVEAU : Contenu dynamique dans un conteneur -->
                        <div id="profile-preview">
                            <?php if (!empty($vcard_fields['profile_picture'])): ?>
                                <img src="<?php echo esc_url($vcard_fields['profile_picture']); ?>"
                                    class="img-fluid rounded-circle border"
                                    style="width: 120px; height: 120px; object-fit: cover;" alt="Photo de profil">
                            <?php else: ?>
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center border"
                                    style="width: 120px; height: 120px; margin: 0 auto;">
                                    <span class="text-primary fw-bold fs-2"><?php echo esc_html($initials); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <input type="file" id="profile_image_input" name="profile_picture"
                        accept="image/jpeg,image/png,image/gif" style="display: none;">

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm"
                            onclick="document.getElementById('profile_image_input').click()">
                            <i class="fas fa-upload me-2"></i>
                            <?php echo !empty($vcard_fields['profile_picture']) ? 'Changer la photo' : 'Ajouter une photo'; ?>
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
            <div class="dashboard-card mb-4">
                <div class="card-header p-3">
                    <h3 class="h6 mb-0">
                        <i class="fas fa-image me-2"></i>
                        Photo de couverture
                    </h3>
                    <small class="text-muted">Image d'arrière-plan de votre vCard</small>
                </div>
                <div class="p-4 text-center">
                    <!-- 🔥 NOUVEAU : Container fixe avec ID -->
                    <div class="cover-image-container mb-3" id="cover-preview-container">
                        <!-- 🔥 NOUVEAU : Contenu dynamique dans un conteneur -->
                        <div id="cover-preview">
                            <?php if (!empty($vcard_fields['cover_image'])): ?>
                                <img src="<?php echo esc_url($vcard_fields['cover_image']); ?>"
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

                    <input type="file" id="cover_image_input" name="cover_image" accept="image/jpeg,image/png,image/gif"
                        style="display: none;">

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm"
                            onclick="document.getElementById('cover_image_input').click()">
                            <i class="fas fa-upload me-2"></i>
                            <?php echo !empty($vcard_fields['cover_image']) ? 'Changer l\'image' : 'Ajouter une image'; ?>
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

            <div class="dashboard-card mb-4">
                <div class="card-header p-3">
                    <h3 class="h6 mb-0">
                        <i class="fas fa-link me-2"></i>
                        URL publique
                    </h3>
                    <small class="text-muted">Lien vers votre vCard</small>
                </div>
                <div class="p-4">
                    <div class="input-group">
                        <input type="text" id="vcard-public-url" class="form-control form-control-sm"
                            value="<?php echo esc_url($public_url); ?>" readonly>
                        <button class="btn btn-outline-secondary btn-sm" type="button"
                            onclick="copyToClipboard('<?php echo esc_js($public_url); ?>')" title="Copier l'URL">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button class="btn btn-outline-primary btn-sm" type="button"
                            onclick="window.open('<?php echo esc_js($public_url); ?>', '_blank')" title="Ouvrir">
                            <i class="fas fa-external-link-alt"></i>
                        </button>
                    </div>
                    <div class="form-text mt-2">
                        <i class="fas fa-share me-1"></i>
                        Partagez cette URL pour que vos contacts puissent accéder à votre vCard
                    </div>
                </div>
            </div>

            <!-- STATISTIQUES RAPIDES -->
            <div class="dashboard-card mb-4">
                <div class="card-header p-3">
                    <h3 class="h6 mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Statistiques rapides
                    </h3>
                </div>
                <div class="p-3">
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <div class="fw-bold text-primary fs-5" id="quick-views">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                            <small class="text-muted">Vues</small>
                            <div class="small" id="quick-views-period">
                                <span class="text-muted">-</span>
                            </div>
                        </div>
                        <div class="col-4 border-end">
                            <div class="fw-bold text-success fs-5" id="quick-contacts">
                                <div class="spinner-border spinner-border-sm text-success" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                            <small class="text-muted">Visiteurs</small>
                            <div class="small" id="quick-contacts-growth">
                                <span class="text-muted">-</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning fs-5" id="quick-interactions">
                                <div class="spinner-border spinner-border-sm text-warning" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                            <small class="text-muted">Interactions</small>
                            <div class="small" id="quick-interaction-rate">
                                <span class="text-muted">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="?page=statistics" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-chart-bar me-2"></i>
                            Voir toutes les statistiques
                        </a>
                    </div>

                    <!-- Section debug temporaire -->
                    <div class="mt-2" id="quick-stats-debug" style="display: none;">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="debug-message">Chargement des données...</span>
                        </small>
                    </div>
                </div>
            </div>

            <!-- ACTIONS RAPIDES -->
            <div class="dashboard-card">
                <div class="card-header p-3">
                    <h3 class="h6 mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Actions rapides
                    </h3>
                </div>
                <div class="p-3">
                    <div class="d-grid gap-2">
                        <a href="?page=qr-codes" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-qrcode me-2"></i>
                            Télécharger QR Code
                        </a>
                        <a href="?page=preview" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-eye me-2"></i>
                            Aperçu responsive
                        </a>
                        <a href="?page=contacts" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-users me-2"></i>
                            Gérer mes contacts
                        </a>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="shareVCard()">
                            <i class="fas fa-share-alt me-2"></i>
                            Partager ma vCard
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>


<script>
/**
 * JavaScript pour les statistiques rapides de vcard-edit
 * À ajouter dans vcard-editor.js ou dans un script séparé
 */

// Configuration pour les stats rapides
window.vcardEditStatsConfig = {
    vcard_id: <?php echo json_encode($vcard_id); ?>,
    api_url: <?php echo json_encode(home_url('/wp-json/gtmi_vcard/v1/')); ?>,
    refresh_interval: 30000 // Refresh toutes les 30 secondes
};

// Variable pour éviter les chargements multiples
let quickStatsLoaded = false;
let quickStatsInterval = null;

/**
 * Charger les statistiques rapides
 */
function loadQuickStatsReal() {
    if (quickStatsLoaded) return;
    
    console.log('📊 vCard Edit: Chargement des stats rapides...');
    
    // Vérifier que NFCStatsCommons est disponible
    if (typeof window.NFCStatsCommons === 'undefined') {
        console.error('❌ NFCStatsCommons non disponible');
        displayQuickStatsError('Module stats non chargé');
        return;
    }
    
    const config = window.vcardEditStatsConfig;
    
    if (!config.vcard_id) {
        console.warn('⚠️ Pas de vCard ID pour charger les stats');
        displayQuickStatsError('ID vCard manquant');
        return;
    }
    
    // Afficher le debug
    showQuickStatsDebug('Connexion à l\'API...');
    
    // Charger via le module commun
    window.NFCStatsCommons.loadRealStats(config.vcard_id, config.api_url)
        .then(statsData => {
            console.log('✅ Stats rapides chargées:', statsData.length, 'entrées');
            
            // Calculer les KPIs
            const kpis = window.NFCStatsCommons.calculateKPIs(statsData);
            const growth = window.NFCStatsCommons.calculateGrowth(statsData, 7);
            
            // Mettre à jour l'affichage
            updateQuickStatsDisplay(kpis, growth, statsData.length);
            
            // Marquer comme chargé
            quickStatsLoaded = true;
            
            // Masquer le debug
            hideQuickStatsDebug();
            
            console.log('✅ Stats rapides mises à jour');
        })
        .catch(error => {
            console.error('❌ Erreur chargement stats rapides:', error);
            displayQuickStatsError('Erreur de connexion');
            hideQuickStatsDebug();
        });
}

/**
 * Mettre à jour l'affichage des stats rapides
 */
function updateQuickStatsDisplay(kpis, growth, totalEntries) {
    // Vues totales
    const viewsEl = document.getElementById('quick-views');
    if (viewsEl) {
        viewsEl.textContent = kpis.totalViews;
    }
    
    // Période et croissance des vues
    const viewsPeriodEl = document.getElementById('quick-views-period');
    if (viewsPeriodEl) {
        if (growth.viewsGrowth !== 0) {
            viewsPeriodEl.innerHTML = `<span class="text-${growth.viewsGrowth > 0 ? 'success' : 'danger'}">
                ${growth.viewsGrowth > 0 ? '↗' : '↘'} ${Math.abs(growth.viewsGrowth)}%
            </span>`;
        } else {
            viewsPeriodEl.innerHTML = '<span class="text-muted">7 derniers jours</span>';
        }
    }
    
    // Visiteurs uniques (contacts)
    const contactsEl = document.getElementById('quick-contacts');
    if (contactsEl) {
        contactsEl.textContent = kpis.uniqueVisitors;
    }
    
    // Croissance des visiteurs
    const contactsGrowthEl = document.getElementById('quick-contacts-growth');
    if (contactsGrowthEl) {
        if (growth.visitorsGrowth !== 0) {
            contactsGrowthEl.innerHTML = `<span class="text-${growth.visitorsGrowth > 0 ? 'success' : 'danger'}">
                ${growth.visitorsGrowth > 0 ? '↗' : '↘'} ${Math.abs(growth.visitorsGrowth)}%
            </span>`;
        } else {
            contactsGrowthEl.innerHTML = '<span class="text-muted">Uniques</span>';
        }
    }
    
    // Interactions
    const interactionsEl = document.getElementById('quick-interactions');
    if (interactionsEl) {
        interactionsEl.textContent = kpis.interactions;
    }
    
    // Taux d'interaction
    const interactionRateEl = document.getElementById('quick-interaction-rate');
    if (interactionRateEl) {
        interactionRateEl.innerHTML = `<span class="text-primary">${kpis.interactionRate}%</span>`;
    }
    
    console.log('📊 Quick stats affichées:', {
        vues: kpis.totalViews,
        visiteurs: kpis.uniqueVisitors,
        interactions: kpis.interactions,
        taux: kpis.interactionRate + '%'
    });
}

/**
 * Afficher un message d'erreur dans les stats rapides
 */
function displayQuickStatsError(message) {
    const elements = ['quick-views', 'quick-contacts', 'quick-interactions'];
    elements.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.innerHTML = '<span class="text-danger">-</span>';
        }
    });
    
    showQuickStatsDebug(message, 'danger');
}

/**
 * Afficher le message de debug
 */
function showQuickStatsDebug(message, type = 'info') {
    const debugEl = document.getElementById('quick-stats-debug');
    const messageEl = document.getElementById('debug-message');
    
    if (debugEl && messageEl) {
        messageEl.textContent = message;
        debugEl.style.display = 'block';
        
        // Ajouter une classe de couleur selon le type
        debugEl.className = `mt-2 text-${type}`;
    }
}

/**
 * Masquer le message de debug
 */
function hideQuickStatsDebug() {
    const debugEl = document.getElementById('quick-stats-debug');
    if (debugEl) {
        setTimeout(() => {
            debugEl.style.display = 'none';
        }, 2000);
    }
}

/**
 * Démarrer le refresh automatique
 */
function startQuickStatsRefresh() {
    if (quickStatsInterval) {
        clearInterval(quickStatsInterval);
    }
    
    const config = window.vcardEditStatsConfig;
    if (config.refresh_interval > 0) {
        quickStatsInterval = setInterval(() => {
            console.log('🔄 Refresh automatique des stats rapides');
            quickStatsLoaded = false; // Forcer le rechargement
            loadQuickStatsReal();
        }, config.refresh_interval);
    }
}

/**
 * Arrêter le refresh automatique
 */
function stopQuickStatsRefresh() {
    if (quickStatsInterval) {
        clearInterval(quickStatsInterval);
        quickStatsInterval = null;
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    // Attendre que NFCStatsCommons soit chargé
    setTimeout(() => {
        loadQuickStatsReal();
        startQuickStatsRefresh();
    }, 1000);
});

// Nettoyer au déchargement de la page
window.addEventListener('beforeunload', function() {
    stopQuickStatsRefresh();
});

// Exposer les fonctions pour usage externe
window.VCardEditStats = {
    load: loadQuickStatsReal,
    refresh: function() {
        quickStatsLoaded = false;
        loadQuickStatsReal();
    },
    startRefresh: startQuickStatsRefresh,
    stopRefresh: stopQuickStatsRefresh
};
</script>