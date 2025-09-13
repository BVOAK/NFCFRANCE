<?php
/**
 * Template : Section Profils Avis Google Dashboard Unifié
 * Fichier: templates/dashboard/sections/google-reviews-section.php
 * Variables disponibles: $products_summary
 */

if (!defined('ABSPATH')) exit;

$google_profiles = isset($products_summary['google_reviews_profiles']) ? $products_summary['google_reviews_profiles'] : [];
?>

<div class="dashboard-section" id="google-reviews-section">
    <div class="section-header">
        <h3>
            <i class="fas fa-star me-2 text-warning"></i>
            Mes Profils Avis Google
            <span class="badge bg-warning text-dark ms-2"><?php echo count($google_profiles); ?> profil(s)</span>
        </h3>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-warning btn-sm" onclick="exportGoogleAnalytics()">
                <i class="fas fa-download me-2"></i>Export Analytics
            </button>
            <a href="/boutique-nfc/?product_type=google_reviews" class="btn btn-warning btn-sm">
                <i class="fas fa-plus me-2"></i>Commander Avis Google
            </a>
        </div>
    </div>
    
    <div class="section-content">
        <?php if (empty($google_profiles)): ?>
            <div class="empty-state text-center py-5">
                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                <h5>Aucun profil Avis Google</h5>
                <p class="text-muted">Créez votre premier système de collecte d'avis Google pour améliorer votre réputation en ligne.</p>
                
                <div class="alert alert-info text-start mt-4">
                    <h6><i class="fas fa-info-circle me-2"></i>Comment ça fonctionne ?</h6>
                    <ul class="mb-0 small">
                        <li>Commandez des cartes ou plaques "Avis Google"</li>
                        <li>Configurez votre URL Google Business</li>
                        <li>Placez vos éléments NFC dans votre établissement</li>
                        <li>Vos clients scannent et laissent des avis facilement</li>
                        <li>Suivez les performances de chaque emplacement</li>
                    </ul>
                </div>
                
                <a href="/boutique-nfc/?product_type=google_reviews" class="btn btn-warning">
                    <i class="fas fa-shopping-cart me-2"></i>Commander maintenant
                </a>
                
                <div class="alert alert-secondary mt-3">
                    <small>
                        <i class="fas fa-wrench me-2"></i>
                        <strong>En développement :</strong> Cette section sera complètement implémentée dans la Phase 2 du développement.
                        L'architecture est prête pour accueillir les profils Avis Google avec tracking par emplacement et analytics détaillées.
                    </small>
                </div>
            </div>
        <?php else: ?>
            <!-- Interface profils Avis Google - À implémenter Phase 2 -->
            <div class="profiles-grid">
                <?php foreach ($google_profiles as $profile): ?>
                <div class="google-profile-card card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-building me-2"></i>
                            <?php echo esc_html($profile['business_name'] ?? 'Établissement'); ?>
                            <span class="badge bg-secondary ms-2"><?php echo $profile['elements_count'] ?? 0; ?> éléments</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="profile-details mb-3">
                            <?php if (!empty($profile['google_business_url'])): ?>
                                <p class="small mb-2">
                                    <strong>URL Google Business :</strong><br>
                                    <a href="<?php echo esc_url($profile['google_business_url']); ?>" target="_blank" class="text-primary">
                                        <?php echo esc_html(substr($profile['google_business_url'], 0, 50)) . '...'; ?>
                                        <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </p>
                            <?php endif; ?>
                            
                            <div class="stats-row row g-2">
                                <div class="col-4">
                                    <div class="stat-mini text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-primary"><?php echo number_format($profile['total_scans'] ?? 0); ?></div>
                                        <small class="text-muted">Scans</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-mini text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-success"><?php echo number_format($profile['redirections'] ?? 0); ?></div>
                                        <small class="text-muted">Redirections</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-mini text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-warning"><?php echo ($profile['conversion_rate'] ?? 0) . '%'; ?></div>
                                        <small class="text-muted">Conversion</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-actions d-flex gap-1 flex-wrap">
                            <button class="btn btn-primary btn-sm flex-fill" 
                                    onclick="configureGoogleProfile(<?php echo $profile['id']; ?>)">
                                <i class="fas fa-cog me-1"></i>Configurer
                            </button>
                            <button class="btn btn-success btn-sm flex-fill" 
                                    onclick="viewGoogleAnalytics(<?php echo $profile['id']; ?>)">
                                <i class="fas fa-chart-bar me-1"></i>Analytics
                            </button>
                            <button class="btn btn-info btn-sm flex-fill" 
                                    onclick="manageMapping(<?php echo $profile['id']; ?>)">
                                <i class="fas fa-map me-1"></i>Mapping
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Styles spécifiques à la section Avis Google */
.profiles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.google-profile-card {
    border: 1px solid #fed7aa;
    background: linear-gradient(135deg, #fffdf7 0%, #fef3c7 100%);
}

.google-profile-card .card-header {
    background: rgba(251, 191, 36, 0.1);
    border-bottom: 1px solid #fed7aa;
}

.stat-mini {
    min-height: 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

@media (max-width: 768px) {
    .profiles-grid {
        grid-template-columns: 1fr;
    }
    
    .card-actions {
        flex-direction: column;
    }
    
    .card-actions .btn {
        flex: none !important;
    }
}
</style>

<script>
function exportGoogleAnalytics() {
    alert('Export des analytics Avis Google - À implémenter Phase 2');
    // TODO: Implémenter l'export CSV des analytics Avis Google
}

function configureGoogleProfile(profileId) {
    alert('Configuration profil Avis Google #' + profileId + ' - À implémenter Phase 2');
    // TODO: Rediriger vers la page de configuration du profil Avis Google
    // window.location.href = '?page=google-reviews-config&profile=' + profileId;
}

function viewGoogleAnalytics(profileId) {
    alert('Analytics profil Avis Google #' + profileId + ' - À implémenter Phase 2');
    // TODO: Rediriger vers la page d'analytics détaillées
    // window.location.href = '?page=google-reviews-analytics&profile=' + profileId;
}

function manageMapping(profileId) {
    alert('Mapping emplacements #' + profileId + ' - À implémenter Phase 2');
    // TODO: Rediriger vers la page de mapping des emplacements
    // window.location.href = '?page=google-reviews-mapping&profile=' + profileId;
}
</script>