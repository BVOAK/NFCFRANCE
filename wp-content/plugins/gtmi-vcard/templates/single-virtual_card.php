<?php
/**
 * Template public pour une vCard - Version corrigée selon mockup
 * 
 * Fichier: templates/single-virtual_card.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// === LOGIQUE DE REDIRECTION ===
$redirect_mode = get_post_meta(get_the_ID(), 'redirect_mode', true);
$custom_url = get_field('custom_url', get_the_ID());

if ($redirect_mode === 'custom' && strlen($custom_url) > 0) {
    if (filter_var($custom_url, FILTER_VALIDATE_URL)) {
        header('Location: ' . $custom_url, true, 301);
        exit();
    }
}

// === RÉCUPÉRATION DES DONNÉES vCard ===
$vcard_id = get_the_ID();

// Configuration pour le tracking
$tracking_config = [
    'vcard_id' => $vcard_id,
    'api_url' => home_url('/wp-json/gtmi_vcard/v1/'),
    'nonce' => wp_create_nonce('nfc_tracking_nonce'),
    'debug' => WP_DEBUG
];

$vcard_data = [
    'firstname' => get_post_meta($vcard_id, 'firstname', true) ?: '',
    'lastname' => get_post_meta($vcard_id, 'lastname', true) ?: '',
    'society' => get_post_meta($vcard_id, 'society', true) ?: '',
    'service' => get_post_meta($vcard_id, 'service', true) ?: '',
    'post' => get_post_meta($vcard_id, 'post', true) ?: '',
    'email' => get_post_meta($vcard_id, 'email', true) ?: '',
    'phone' => get_post_meta($vcard_id, 'phone', true) ?: '',
    'mobile' => get_post_meta($vcard_id, 'mobile', true) ?: '',
    'website' => get_post_meta($vcard_id, 'website', true) ?: '',
    'linkedin' => get_post_meta($vcard_id, 'linkedin', true) ?: '',
    'twitter' => get_post_meta($vcard_id, 'twitter', true) ?: '',
    'instagram' => get_post_meta($vcard_id, 'instagram', true) ?: '',
    'facebook' => get_post_meta($vcard_id, 'facebook', true) ?: '',
    'youtube' => get_post_meta($vcard_id, 'youtube', true) ?: '',
    'description' => get_post_meta($vcard_id, 'description', true) ?: '',
    'address' => get_post_meta($vcard_id, 'address', true) ?: '',
    'additional' => get_post_meta($vcard_id, 'additional', true) ?: '',
    'postcode' => get_post_meta($vcard_id, 'postcode', true) ?: '',
    'city' => get_post_meta($vcard_id, 'city', true) ?: '',
    'country' => get_post_meta($vcard_id, 'country', true) ?: '',
];

// Images
// Images - Version robuste
$profile_picture = get_post_meta($vcard_id, 'profile_picture', true);
if (empty($profile_picture) && function_exists('get_field')) {
    $acf_profile = get_field('profile_picture', $vcard_id);
    if (is_array($acf_profile) && isset($acf_profile['url'])) {
        $profile_picture = $acf_profile['url'];
    } elseif (is_string($acf_profile) && !empty($acf_profile)) {
        $profile_picture = $acf_profile;
    }
}

$cover_image = get_post_meta($vcard_id, 'cover_image', true);
if (empty($cover_image) && function_exists('get_field')) {
    $acf_cover = get_field('cover_image', $vcard_id);
    if (is_array($acf_cover) && isset($acf_cover['url'])) {
        $cover_image = $acf_cover['url'];
    } elseif (is_string($acf_cover) && !empty($acf_cover)) {
        $cover_image = $acf_cover;
    }
}

// DEBUG: Voir les données pour l'ID 3736
if ($vcard_id == 3736) {
    error_log("DEBUG ID 3736 - cover_image données:");
    error_log("  get_post_meta: " . var_export(get_post_meta($vcard_id, 'cover_image', true), true));
    if (function_exists('get_field')) {
        error_log("  get_field: " . var_export(get_field('cover_image', $vcard_id), true));
    }
    error_log("  $cover_image final: " . var_export($cover_image, true));
}

// Traitement des images ACF
if (is_array($profile_picture) && isset($profile_picture['url'])) {
    $profile_picture = $profile_picture['url'];
}
if (is_array($cover_image) && isset($cover_image['url'])) {
    $cover_image = $cover_image['url'];
}

// Variables dérivées
$full_name = trim($vcard_data['firstname'] . ' ' . $vcard_data['lastname']);
$job_title = $vcard_data['post'] ?: $vcard_data['service'];
$full_address = trim($vcard_data['address'] . ' ' . $vcard_data['additional'] . ' ' . $vcard_data['postcode'] . ' ' . $vcard_data['city']);
$phone_display = $vcard_data['phone'] ?: $vcard_data['mobile'];

// Initiales pour l'avatar
$initials = strtoupper(substr($vcard_data['firstname'], 0, 1) . substr($vcard_data['lastname'], 0, 1));
if (empty(trim($initials))) {
    $initials = 'UN';
}

// Enqueue des assets
$plugin_url = plugin_dir_url(dirname(__FILE__));
wp_enqueue_style('vcard-public', $plugin_url . 'assets/css/vcard-public.css', [], '2.0.0');
wp_enqueue_script('vcard-public', $plugin_url . 'assets/js/vcard-public.js', ['jquery'], '2.0.0', true);
wp_enqueue_script('nfc-trackers', $plugin_url . 'assets/js/vcard-trackers.js', ['jquery'], '1.0.0', true);

wp_enqueue_style('nfc-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', [], null);
wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', [], '5.3.0');
wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0');

wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', [], '5.3.0', true);

// Configuration JavaScript
wp_localize_script('vcard-public', 'vCardConfig', [
    'id' => $vcard_id,
    'name' => $full_name,
    'firstName' => $vcard_data['firstname'],
    'lastName' => $vcard_data['lastname'],
    'email' => $vcard_data['email'],
    'phone' => $phone_display,
    'company' => $vcard_data['society'],
    'title' => $job_title,
    'address' => $full_address,
    'website' => $vcard_data['website'],
    'description' => $vcard_data['description'],
    'linkedin' => $vcard_data['linkedin'],
    'instagram' => $vcard_data['instagram'],
    'twitter' => $vcard_data['twitter'],
    'facebook' => $vcard_data['facebook'],
    'youtube' => $vcard_data['youtube'],
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
]);

wp_localize_script('nfc-trackers', 'NFCTrackingConfig', $tracking_config);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($full_name ?: 'vCard NFC'); ?> - Carte de visite numérique</title>

    <!-- Meta tags pour partage social -->
    <meta name="description"
        content="<?php echo esc_attr($job_title . ($vcard_data['society'] ? ' chez ' . $vcard_data['society'] : '')); ?>">
    <meta property="og:title" content="<?php echo esc_attr($full_name); ?> - vCard NFC">
    <meta property="og:description"
        content="<?php echo esc_attr($job_title . ($vcard_data['society'] ? ' chez ' . $vcard_data['society'] : '')); ?>">
    <?php if ($profile_picture): ?>
        <meta property="og:image" content="<?php echo esc_url($profile_picture); ?>">
    <?php endif; ?>

    <script>
    // Configuration globale pour le tracking
    window.NFC_VCARD_ID = <?php echo json_encode($vcard_id); ?>;
    window.NFC_TRACKING_CONFIG = <?php echo json_encode($tracking_config); ?>;
    console.log('📊 vCard Tracking configuré pour ID:', <?php echo $vcard_id; ?>);

    // Force l'initialisation du tracking quand le DOM est prêt
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 DOM Ready - Initialisation tracking...');
        
        if (typeof window.NFCTracking !== 'undefined' && window.NFC_VCARD_ID) {
            window.NFCTracking.init(window.NFC_VCARD_ID);
            console.log('✅ Tracking initialisé manuellement pour vCard:', window.NFC_VCARD_ID);
        } else {
            console.error('❌ NFCTracking non disponible ou vCard ID manquant');
        }
    });
    </script>
        
    <?php wp_head(); ?>
</head>

<body <?php body_class('vcard-public'); ?>>

    <!-- Container principal mobile -->
    <div class="vcard-mobile-wrapper">

        <!-- Header avec image de couverture selon mockup -->
        <div class="vcard-header-section" <?php if ($cover_image): ?>style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('<?php echo esc_url($cover_image); ?>')"
            <?php endif; ?>>

            <!-- Boutons d'action flottants en haut à droite -->
            <div class="floating-action-buttons">
                <?php if ($phone_display): ?>
                    <button class="floating-btn phone-click" onclick="VCardPublic.callPhone()" title="Appeler" aria-label="Appeler">
                        <i class="fas fa-phone"></i>
                    </button>
                <?php endif; ?>

                <?php if ($vcard_data['email']): ?>
                    <button class="floating-btn email-click" onclick="VCardPublic.sendEmail()" title="Envoyer un email"
                        aria-label="Envoyer un email">
                        <i class="fas fa-envelope"></i>
                    </button>
                <?php endif; ?>

                <button class="floating-btn share-btn-header share-btn" onclick="VCardPublic.shareVCard()" title="Partager"
                    aria-label="Partager">
                    <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'gtmi-vcard/assets/img/export.svg' ?>"
                        alt="Export">
                </button>
            </div>
        </div>

        <!-- Carte principale avec profil selon mockup -->
        <div class="profile-main-card">

            <div class="row m-0">

                <!-- Photo de profil -->
                <div class="profile-photo-container p-0">
                    <?php if ($profile_picture): ?>
                        <img src="<?php echo esc_url($profile_picture); ?>" alt="<?php echo esc_attr($full_name); ?>"
                            class="profile-photo">
                    <?php else: ?>
                        <div class="profile-avatar-placeholder">
                            <?php echo esc_html($initials); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="profile-name">
                    <!-- Informations principales -->
                    <h1 class="user-name"><?php echo esc_html($full_name); ?></h1>

                    <?php if ($job_title): ?>
                        <div class="user-job-title"><?php echo esc_html($job_title); ?></div>
                    <?php endif; ?>

                    <?php if ($vcard_data['society']): ?>
                        <div class="user-company"><?php echo esc_html($vcard_data['society']); ?></div>
                    <?php endif; ?>
                </div>

            </div>

        </div>

        <!-- Contenu principal selon mockup -->
        <div class="vcard-main-content">

            <!-- Boutons principaux selon mockup exact -->
            <div class="action-buttons-container">
                <div class="col-6">
                    <button class="btn-add-contact" onclick="VCardPublic.downloadVCard()">
                        <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'gtmi-vcard/assets/img/profil-card.svg' ?>"
                            alt="Ajouter à mes contacts">
                        <span>Ajouter à mes contacts</span>
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn-share-contact" onclick="VCardPublic.openShareModal()">
                        <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'gtmi-vcard/assets/img/transfer.svg' ?>"
                            alt="Partager mes coordonnées">
                        <span>Partager mon contact</span>
                    </button>
                </div>
            </div>

            <!-- Description si présente -->
            <?php if ($vcard_data['description']): ?>
                <div class="description-block">
                    <?php echo nl2br(esc_html($vcard_data['description'])); ?>
                </div>
            <?php endif; ?>

            <!-- Liens utiles -->
            <div class="useful-links-section">
                <?php if ($vcard_data['website']): ?>
                    <div class="useful-link-item">
                        <a href="<?php echo esc_url($vcard_data['website']); ?>" target="_blank" class="link-button website-link" rel="noopener">
                            <div class="link-icon-container">
                                <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'gtmi-vcard/assets/img/website.svg' ?>"
                                    alt="Site Internet">
                            </div>
                            <span>Site Internet</span>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Bouton rendez-vous - COMMENTÉ pour développement ultérieur -->
                <!--
                <div class="useful-link-item">
                    <a href="#" class="link-button" onclick="VCardPublic.bookAppointment()">
                        <div class="link-icon-container">
                            <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'gtmi-vcard/assets/img/calendar.svg' ?>" alt="Prendre rendez-vous">
                        </div>
                        <span>Prendre rendez-vous</span>
                    </a>
                </div>
                -->
            </div>

            <!-- Réseaux sociaux selon mockup -->
            <?php
            $social_networks = [
                'linkedin' => ['icon' => 'fab fa-linkedin', 'color' => '#131329'],
                'instagram' => ['icon' => 'fab fa-instagram', 'color' => '#131329'],
                'twitter' => ['icon' => 'fab fa-x-twitter', 'color' => '#131329'],
                'facebook' => ['icon' => 'fab fa-facebook', 'color' => '#131329'],
                'youtube' => ['icon' => 'fab fa-youtube', 'color' => '#131329']
            ];

            $has_social = false;
            foreach ($social_networks as $network => $config) {
                if (!empty($vcard_data[$network])) {
                    $has_social = true;
                    break;
                }
            }
            ?>

            <?php if ($has_social): ?>
                <div class="social-networks-section">
                    <?php foreach ($social_networks as $network => $config): ?>
                        <?php if (!empty($vcard_data[$network])): ?>
                            <a href="<?php echo esc_url($vcard_data[$network]); ?>" target="_blank" class="social-link <?php echo $network.'-link' ?>" rel="noopener"
                                style="background-color: <?php echo $config['color']; ?>">
                                <i class="<?php echo $config['icon']; ?>"></i>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Étoile pour avis - À développer plus tard -->
                    <!--
                <a href="#" class="social-link star-link" style="background-color: #ffc107;">
                    <i class="fas fa-star"></i>
                </a>
                -->
                </div>
            <?php endif; ?>

            <!-- Section Coordonnées -->
            <div class="contact-details-section">
                <h3 class="section-title">Coordonnées</h3>

                <?php if ($vcard_data['email']): ?>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <a href="mailto:<?php echo esc_attr($vcard_data['email']); ?>" class="contact-link">
                            <?php echo esc_html($vcard_data['email']); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($phone_display): ?>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <a href="tel:<?php echo esc_attr($phone_display); ?>" class="contact-link">
                            <?php echo esc_html($phone_display) ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($full_address): ?>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <span class="contact-text">
                            <?php if ($vcard_data['address']): ?>        <?php echo esc_html($vcard_data['address']); ?>,<br /><?php endif; ?>
                            <?php if ($vcard_data['additional']): ?>        <?php echo esc_html($vcard_data['additional']); ?>,<br /><?php endif; ?>
                            <?php if ($vcard_data['city']): ?>        <?php echo esc_html($vcard_data['postcode']); ?>
                                <?php echo esc_html($vcard_data['city']); ?>    <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="footer">
            <a href="https://nfcfrance.com">Réalisé avec <img
                    src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'gtmi-vcard/assets/img/logo-nfcfrance-symbol.png' ?>"
                    alt="NFC France" width="20" /> <b>NFC France</b></a>
        </div>

    </div>

    <!-- Modal Bootstrap pour partage des coordonnées -->
    <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareContactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title fw-bold" id="shareContactModalLabel">
                        <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'gtmi-vcard/assets/img/transfer-title.svg' ?>"
                            alt="Partager mes coordonnées">
                        Partager mes coordonnées
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body pt-0">
                    <form id="shareContactForm">
                        <div class="mb-3">
                            <input type="text" class="form-control form-control-lg" name="firstName"
                                placeholder="Mon nom*" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control form-control-lg" name="lastName"
                                placeholder="Mon prénom*" required>
                        </div>
                        <div class="mb-3">
                            <input type="tel" class="form-control form-control-lg" name="phone" placeholder="Téléphone*"
                                required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control form-control-lg" name="email" placeholder="Email*"
                                required>
                        </div>
                        <div class="mb-4">
                            <input type="text" class="form-control form-control-lg" name="company"
                                placeholder="Société">
                        </div>
                        <div class="form-text mb-4 text-center">
                            <small>En validant le formulaire, vous acceptez de vous conformer à la
                                <a href="#" class="text-decoration-none">politique de confidentialité</a> et aux
                                <a href="#" class="text-decoration-none">conditions générales</a> de NFCFrance</small>
                        </div>
                        <button type="submit"
                            class="btn btn-dark btn-lg w-100 d-flex align-items-center justify-content-center">
                            Je partage mes coordonnées
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Message de notification -->
    <div id="notificationMessage" class="notification-message" style="display: none;"></div>

    <?php wp_footer(); ?>

    <script>
// Initialisation personnalisée du tracking si nécessaire
document.addEventListener('DOMContentLoaded', function() {
    
    // Vérifier que le tracking est bien initialisé
    if (window.NFCTracking && window.NFCTracking.vcard_id) {
        console.log('✅ Tracking NFC opérationnel');
        
        // Tracking d'événements personnalisés si nécessaire
        
        // Exemple: Track l'ouverture d'une modal
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
            button.addEventListener('click', function() {
                window.trackNFCEvent('modal_opened', {
                    modal_target: this.getAttribute('data-bs-target')
                });
            });
        });
        
        // Exemple: Track le scroll jusqu'en bas de page
        let hasScrolledToBottom = false;
        window.addEventListener('scroll', function() {
            if (!hasScrolledToBottom && 
                (window.innerHeight + window.scrollY) >= document.body.offsetHeight - 100) {
                window.trackNFCEvent('scrolled_to_bottom');
                hasScrolledToBottom = true;
            }
        });
        
        // Debug: Afficher les stats de session dans la console (en mode debug)
        <?php if (WP_DEBUG): ?>
        setInterval(function() {
            console.log('📊 Session stats:', window.getNFCSessionStats());
        }, 30000); // Toutes les 30 secondes
        <?php endif; ?>
        
    } else {
        console.error('❌ Tracking NFC non initialisé');
    }
});
</script>

<script>
// Désactiver temporairement l'ancien
window.NFC_VCARD_ID = undefined;
</script>

<script src="<?= plugin_dir_url(dirname(__FILE__)) ?>assets/js/nfc-analytics.js"></script>
<script>
window.nfcAnalyticsConfig = {
    vcard_id: <?= get_the_ID() ?>,
    ajax_url: '<?= admin_url('admin-ajax.php') ?>',
    nonce: '<?= wp_create_nonce('nfc_analytics') ?>',
    debug: true
};
</script>

<!-- Test AJAX Manual -->
<script>
console.log('🧪 Test AJAX manuel...');

// Test simple de l'endpoint
fetch('<?= admin_url('admin-ajax.php') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
        action: 'nfc_track_view',
        vcard_id: <?= get_the_ID() ?>,
        session_id: 'test_manual_123',
        traffic_source: 'direct',
        nonce: '<?= wp_create_nonce('nfc_analytics') ?>'
    })
})
.then(response => {
    console.log('📡 Réponse status:', response.status);
    return response.text();
})
.then(data => {
    console.log('📡 Réponse raw:', data);
    try {
        const json = JSON.parse(data);
        console.log('📡 Réponse JSON:', json);
    } catch(e) {
        console.error('❌ Pas du JSON valide');
    }
})
.catch(error => {
    console.error('❌ Erreur AJAX:', error);
});
</script>

</body>

</html>