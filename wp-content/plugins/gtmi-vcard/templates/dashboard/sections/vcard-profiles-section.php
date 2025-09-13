<?php
/**
 * Section Profils vCard du Dashboard
 */
if (!defined('ABSPATH')) exit;

$vcard_profiles = $data['vcard_profiles'] ?? [];

if (!empty($vcard_profiles)): ?>
<div class="dashboard-section" id="vcard-profiles-section">
    <div class="section-header">
        <h2><i class="fas fa-id-card"></i> Mes Profils vCard</h2>
        <p class="section-description">
            Gérez vos cartes de visite numériques. Chaque carte = 1 profil individuel.
        </p>
    </div>
    
    <div class="profiles-grid">
        <?php foreach ($vcard_profiles as $profile): ?>
        <div class="profile-card vcard-profile">
            <div class="profile-header">
                <h3><?php echo esc_html($profile['card_identifier']); ?></h3>
                <span class="profile-type">vCard</span>
            </div>
            <div class="profile-info">
                <p class="profile-name"><?php echo esc_html($profile['firstname'] . ' ' . $profile['lastname']); ?></p>
                <p class="profile-company"><?php echo esc_html($profile['company_name'] ?? 'Entreprise'); ?></p>
            </div>
            <div class="profile-stats">
                <span class="stat">👁️ <?php echo $profile['views_30d'] ?? 0; ?> vues</span>
                <span class="stat">📞 <?php echo $profile['contacts_30d'] ?? 0; ?> contacts</span>
            </div>
            <div class="profile-actions">
                <a href="?page=vcard-edit&vcard_id=<?php echo $profile['vcard_id']; ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> Modifier
                </a>
                <a href="?page=statistics&vcard_id=<?php echo $profile['vcard_id']; ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-chart-line"></i> Stats
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

// ✅ Créer templates/dashboard/sections/google-reviews-section.php
<?php
/**
 * Section Profils Avis Google du Dashboard
 */
if (!defined('ABSPATH')) exit;

$google_reviews_profiles = $data['google_reviews_profiles'] ?? [];

if (!empty($google_reviews_profiles)): ?>
<div class="dashboard-section" id="google-reviews-section">
    <div class="section-header">
        <h2><i class="fas fa-star"></i> Mes Profils Avis Google</h2>
        <p class="section-description">
            Gérez vos systèmes de collecte d'avis Google. Chaque profil = plusieurs emplacements.
        </p>
    </div>
    
    <div class="profiles-grid">
        <?php foreach ($google_reviews_profiles as $profile): ?>
        <div class="profile-card google-reviews-profile">
            <div class="profile-header">
                <h3><?php echo esc_html($profile['company_name']); ?></h3>
                <span class="profile-type">Avis Google</span>
            </div>
            <div class="profile-info">
                <p class="profile-elements"><?php echo $profile['total_elements']; ?> éléments NFC</p>
                <p class="profile-url"><?php echo esc_html($profile['google_business_url'] ?: 'URL à configurer'); ?></p>
            </div>
            <div class="profile-stats">
                <span class="stat">📊 <?php echo $profile['scans_30d'] ?? 0; ?> scans</span>
                <span class="stat">⭐ <?php echo $profile['redirections_30d'] ?? 0; ?> avis</span>
            </div>
            <div class="profile-actions">
                <a href="?page=google-reviews-config&profile_id=<?php echo $profile['profile_id']; ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-cog"></i> Configurer
                </a>
                <a href="?page=google-reviews-analytics&profile_id=<?php echo $profile['profile_id']; ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>