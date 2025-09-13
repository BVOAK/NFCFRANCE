<?php
/**
 * Template : Section Profils vCard Dashboard Unifié
 * Fichier: templates/dashboard/sections/vcard-profiles-section.php
 * Variables disponibles: $products_summary
 */

if (!defined('ABSPATH')) exit;

$vcard_profiles = isset($products_summary['vcard_profiles']) ? $products_summary['vcard_profiles'] : [];
?>

<div class="dashboard-section" id="vcard-profiles-section">
    <div class="section-header">
        <h3>
            <i class="fas fa-id-card me-2 text-primary"></i>
            Mes Profils vCard
            <span class="badge bg-primary ms-2"><?php echo count($vcard_profiles); ?> profil(s)</span>
        </h3>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="exportVcardStats()">
                <i class="fas fa-download me-2"></i>Export Stats
            </button>
            <a href="/boutique-nfc/" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-2"></i>Commander vCards
            </a>
        </div>
    </div>
    
    <div class="section-content">
        <?php if (empty($vcard_profiles)): ?>
            <div class="empty-state text-center py-5">
                <i class="fas fa-id-card fa-3x text-muted mb-3"></i>
                <h5>Aucun profil vCard</h5>
                <p class="text-muted">Commandez vos premières cartes vCard pour commencer.</p>
                <a href="/boutique-nfc/" class="btn btn-primary">
                    <i class="fas fa-shopping-cart me-2"></i>Commander maintenant
                </a>
            </div>
        <?php else: ?>
            
            <!-- Stats globales -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stat-card text-center p-3 bg-light rounded">
                        <div class="h4 text-primary mb-1">
                            <?php 
                            // Calculer total vues depuis les profils
                            $total_views = 0;
                            foreach($vcard_profiles as $profile) {
                                $total_views += isset($profile['stats']['views']) ? $profile['stats']['views'] : 0;
                            }
                            echo number_format($total_views); 
                            ?>
                        </div>
                        <small class="text-muted">Vues totales</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card text-center p-3 bg-light rounded">
                        <div class="h4 text-success mb-1">
                            <?php 
                            // Calculer total contacts depuis les profils
                            $total_contacts = 0;
                            foreach($vcard_profiles as $profile) {
                                $total_contacts += isset($profile['stats']['contacts']) ? $profile['stats']['contacts'] : 0;
                            }
                            echo number_format($total_contacts); 
                            ?>
                        </div>
                        <small class="text-muted">Leads générés</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card text-center p-3 bg-light rounded">
                        <div class="h4 text-info mb-1"><?php echo count($vcard_profiles); ?></div>
                        <small class="text-muted">Profils</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card text-center p-3 bg-light rounded">
                        <div class="h4 text-warning mb-1">
                            <?php 
                            // Calculer profils configurés
                            $configured = 0;
                            foreach($vcard_profiles as $profile) {
                                if (isset($profile['vcard_data']['is_configured']) && $profile['vcard_data']['is_configured']) {
                                    $configured++;
                                }
                            }
                            echo count($vcard_profiles) > 0 ? round(($configured / count($vcard_profiles)) * 100) . '%' : '0%';
                            ?>
                        </div>
                        <small class="text-muted">Configurés</small>
                    </div>
                </div>
            </div>
            
            <!-- Tableau des profils -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Identifiant</th>
                            <th>Profil</th>
                            <th>Statut</th>
                            <th>Performance</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vcard_profiles as $profile): ?>
                        <?php 
                        // Données du profil (utiliser la structure enterprise existante)
                        $card_identifier = $profile['card_identifier'] ?? 'NFC' . $profile['vcard_id'];
                        $full_name = isset($profile['vcard_data']) ? nfc_format_vcard_full_name($profile['vcard_data']) : 'Profil à configurer';
                        $position = isset($profile['vcard_data']) ? nfc_format_vcard_position($profile['vcard_data']) : '';
                        $views = isset($profile['stats']['views']) ? $profile['stats']['views'] : 0;
                        $contacts = isset($profile['stats']['contacts']) ? $profile['stats']['contacts'] : 0;
                        $status_info = nfc_get_card_display_status($profile);
                        ?>
                        <tr>
                            <td>
                                <code class="bg-light px-2 py-1 rounded small"><?php echo esc_html($card_identifier); ?></code>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-bold"><?php echo esc_html($full_name); ?></div>
                                    <?php if ($position): ?>
                                        <small class="text-muted"><?php echo esc_html($position); ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo esc_attr($status_info['color']); ?>">
                                    <?php echo esc_html($status_info['label']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="text-primary fw-medium"><?php echo number_format($views); ?> vues</span><br>
                                    <span class="text-success fw-medium"><?php echo number_format($contacts); ?> leads</span>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="?page=vcard-edit&vcard_id=<?php echo $profile['vcard_id']; ?>" 
                                       class="btn btn-primary" title="Modifier le profil">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?page=statistics&vcard_id=<?php echo $profile['vcard_id']; ?>" 
                                       class="btn btn-info" title="Voir les statistiques">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    <a href="?page=contacts&vcard_id=<?php echo $profile['vcard_id']; ?>" 
                                       class="btn btn-success" title="Gérer les contacts">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <button class="btn btn-warning" 
                                            onclick="renewCard('<?php echo esc_js($card_identifier); ?>')" 
                                            title="Renouveler la carte">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<script>
function exportVcardStats() {
    alert('Export des statistiques vCard - À implémenter');
    // TODO: Implémenter l'export CSV des stats vCard
}

function renewCard(cardIdentifier) {
    if (confirm('Êtes-vous sûr de vouloir renouveler cette carte ?')) {
        // Rediriger vers l'URL de renouvellement
        const renewalUrl = '<?php echo home_url('/boutique-nfc/'); ?>?context=renewal&card_id=' + cardIdentifier;
        window.location.href = renewalUrl;
    }
}
</script>