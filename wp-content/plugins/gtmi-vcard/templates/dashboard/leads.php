<?php
/**
 * LEADS.PHP - VERSION PROPRE HTML SEULEMENT
 * Tout le JavaScript est géré par contacts-manager.js
 */

if (!defined('ABSPATH')) {
    exit;
}

// ================================================================================
// LOGIQUE PHP SEULEMENT
// ================================================================================

$user_id = get_current_user_id();
$user_vcards = function_exists('nfc_get_user_vcard_profiles') ? nfc_get_user_vcard_profiles($user_id) : [];

if (empty($user_vcards)) {
    ?>
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
        <h3>Aucune carte NFC trouvée</h3>
        <p class="text-muted mb-4">Commandez votre première carte NFC pour commencer à recevoir des contacts.</p>
        <a href="/boutique-nfc/" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Commander ma carte NFC
        </a>
    </div>
    <?php
    return;
}

$is_multi_profile = count($user_vcards) > 1;
$page_title = $is_multi_profile ? "Tous mes contacts (" . count($user_vcards) . " profils)" : "Mes contacts";
$primary_vcard_id = $user_vcards[0]['vcard_id'];

// Variables globales pour compatibilité avec contacts-manager.js
global $nfc_vcard, $nfc_current_page;
$nfc_vcard = (object)['ID' => $primary_vcard_id];
$nfc_current_page = 'contacts';
?>

<!-- CSS -->
<link rel="stylesheet" href="<?php echo plugin_dir_url(dirname(dirname(__FILE__))); ?>assets/css/contacts-manager.css">

<!-- PAGE HEADER -->
<div class="contacts-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="h3 mb-1">
                <i class="fas fa-users me-2 text-primary"></i>
                <?php echo esc_html($page_title); ?>
            </h2>
            <p class="text-muted mb-0">Gestion de vos contacts professionnels</p>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="NFCContacts.showAddModal()">
                    <i class="fas fa-plus me-1"></i>Ajouter
                </button>
                <button class="btn btn-primary" onclick="NFCContacts.exportContacts()">
                    <i class="fas fa-download me-1"></i>Exporter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- STATS CARDS -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-primary mb-2">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <h3 class="h4 mb-1" id="totalContactsStat">0</h3>
                <p class="text-muted small mb-0">Total contacts</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-success mb-2">
                    <i class="fas fa-calendar-plus fa-2x"></i>
                </div>
                <h3 class="h4 mb-1" id="newContactsStat">0</h3>
                <p class="text-muted small mb-0">Cette semaine</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-info mb-2">
                    <i class="fas fa-building fa-2x"></i>
                </div>
                <h3 class="h4 mb-1" id="companiesStat">0</h3>
                <p class="text-muted small mb-0">Entreprises</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-warning mb-2">
                    <i class="fas fa-qrcode fa-2x"></i>
                </div>
                <h3 class="h4 mb-1" id="qrSourceStat">0</h3>
                <p class="text-muted small mb-0">Via QR Code</p>
            </div>
        </div>
    </div>
</div>

<!-- FILTRES -->
<div class="contacts-filters mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row align-items-end">
                <!-- Recherche -->
                <div class="col-md-4">
                    <label class="form-label">Rechercher</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="contactsSearch" placeholder="Nom, email, téléphone...">
                    </div>
                </div>
                
                <!-- Filtre Source -->
                <div class="col-md-2">
                    <label class="form-label">Source</label>
                    <select class="form-select" id="sourceFilter">
                        <option value="">Toutes sources</option>
                        <option value="qr">QR Code</option>
                        <option value="nfc">NFC</option>
                        <option value="web">Site web</option>
                        <option value="manual">Manuel</option>
                    </select>
                </div>
                
                <!-- Filtre Profil (si multi-profil) -->
                <?php if ($is_multi_profile): ?>
                <div class="col-md-2">
                    <label class="form-label">Profil source</label>
                    <select class="form-select" id="profileFilter">
                        <option value="">Tous profils</option>
                        <?php foreach ($user_vcards as $vcard): ?>
                            <option value="<?php echo $vcard['vcard_id']; ?>">
                                <?php echo esc_html(nfc_format_vcard_full_name($vcard['vcard_data'] ?? [])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <!-- Tri -->
                <div class="col-md-2">
                    <label class="form-label">Trier par</label>
                    <select class="form-select" id="sortFilter">
                        <option value="date_desc">Plus récents</option>
                        <option value="date_asc">Plus anciens</option>
                        <option value="name_asc">Nom A-Z</option>
                        <option value="name_desc">Nom Z-A</option>
                    </select>
                </div>
                
                <!-- Vue -->
                <div class="col-md-2 text-end">
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="viewMode" id="tableViewBtn" checked>
                        <label class="btn btn-outline-secondary" for="tableViewBtn">
                            <i class="fas fa-list"></i>
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="gridViewBtn">
                        <label class="btn btn-outline-secondary" for="gridViewBtn">
                            <i class="fas fa-th"></i>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ÉTATS D'AFFICHAGE -->
<div id="contactsLoading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="text-muted mt-3">Chargement des contacts...</p>
</div>

<div id="contactsEmpty" class="text-center py-5 d-none">
    <i class="fas fa-users fa-4x text-muted mb-3"></i>
    <h4>Aucun contact trouvé</h4>
    <p class="text-muted">Commencez à partager votre carte NFC pour recevoir des contacts.</p>
    <button class="btn btn-primary" onclick="NFCContacts.showAddModal()">
        <i class="fas fa-plus me-2"></i>Ajouter un contact manuel
    </button>
</div>

<div id="contactsError" class="alert alert-danger d-none">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Erreur de chargement</strong>
    <p class="mb-0">Impossible de charger les contacts. Veuillez rafraîchir la page.</p>
</div>

<!-- CONTENU PRINCIPAL -->
<div id="contactsContent" class="d-none">
    
    <!-- Actions en lot -->
    <div id="bulkActions" class="card mb-3 d-none">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col">
                    <span id="contactsCounter">0 contact(s) sélectionné(s)</span>
                </div>
                <div class="col-auto">
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm" onclick="NFCContacts.exportSelected()">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="NFCContacts.deleteSelected()">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Vue Tableau -->
    <div id="contactsTableView" class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="contactsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Entreprise</th>
                        <th>Source</th>
                        <?php if ($is_multi_profile): ?>
                        <th>Profil</th>
                        <?php endif; ?>
                        <th>Date</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="contactsTableBody">
                    <!-- Rempli par contacts-manager.js -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer bg-white d-none" id="contactsPaginationWrapper">
            <div class="row align-items-center">
                <div class="col">
                    <small class="text-muted">
                        Affichage de <span id="contactsStart">0</span> à <span id="contactsEnd">0</span> 
                        sur <span id="contactsTotal">0</span> contacts
                    </small>
                </div>
                <div class="col-auto">
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="contactsPagination">
                            <!-- Généré par contacts-manager.js -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Vue Grille -->
    <div id="contactsGridView" class="d-none">
        <div id="contactsGrid" class="row">
            <!-- Rempli par contacts-manager.js -->
        </div>
    </div>
</div>

<!-- MODALS - TODO: Copier de contacts.php -->
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Fonctionnalité en développement...</p>
            </div>
        </div>
    </div>
</div>

<!-- CONFIGURATION POUR contacts-manager.js -->
<script>
// Configuration globale pour contacts-manager.js
window.nfcContactsConfig = {
    vcard_id: <?php echo json_encode($primary_vcard_id); ?>,
    user_id: <?php echo json_encode($user_id); ?>,
    api_url: <?php echo json_encode(home_url('/wp-json/gtmi_vcard/v1/')); ?>,
    ajax_url: <?php echo json_encode(admin_url('admin-ajax.php')); ?>,
    nonce: <?php echo json_encode(wp_create_nonce('nfc_dashboard_nonce')); ?>,
    is_multi_profile: <?php echo json_encode($is_multi_profile); ?>,
    use_ajax: <?php echo json_encode($is_multi_profile); ?>, // Utiliser AJAX si multi-profil
    user_vcards: <?php echo json_encode($user_vcards); ?>
};

console.log('🔧 Configuration nfcContactsConfig injectée:', window.nfcContactsConfig);
</script>