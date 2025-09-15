<?php
/**
 * TEMPLATE LEADS.PHP - VERSION CORRIGÉE
 * Tous les éléments DOM nécessaires pour contacts-manager.js
 */

if (!defined('ABSPATH')) {
    exit;
}

// Logique de base inchangée
$user_id = get_current_user_id();
$user_vcards = nfc_get_user_vcard_profiles($user_id);

if (empty($user_vcards)) {
    echo '<div class="alert alert-info">Aucune vCard trouvée. <a href="/boutique-nfc/">Commandez votre première carte</a></div>';
    return;
}

$is_multi_profile = count($user_vcards) > 1;
$page_title = $is_multi_profile ? "Tous mes contacts (" . count($user_vcards) . " profils)" : "Mes contacts";
$primary_vcard_id = $user_vcards[0]['vcard_id'];

// Configuration JavaScript
$contacts_config = [
    'vcard_id' => $primary_vcard_id,
    'api_url' => home_url('/wp-json/gtmi_vcard/v1/'),
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'is_multi_profile' => $is_multi_profile,
    'user_id' => $user_id
];

// Variables globales pour compatibilité
global $nfc_vcard, $nfc_current_page;
$nfc_vcard = (object)['ID' => $primary_vcard_id];
$nfc_current_page = 'contacts';
?>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- CSS OBLIGATOIRE -->
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

<!-- FILTRES ET CONTRÔLES -->
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
                
                <!-- Filtre Date -->
                <div class="col-md-2">
                    <label class="form-label">Période</label>
                    <select class="form-select" id="dateFilter">
                        <option value="">Toutes dates</option>
                        <option value="today">Aujourd'hui</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                    </select>
                </div>
                
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
                
                <!-- Actions -->
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
<!-- Loading -->
<div id="contactsLoading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="text-muted mt-3">Chargement des contacts...</p>
</div>

<!-- Empty -->
<div id="contactsEmpty" class="text-center py-5 d-none">
    <i class="fas fa-users fa-4x text-muted mb-3"></i>
    <h4>Aucun contact trouvé</h4>
    <p class="text-muted">Commencez à partager votre carte NFC pour recevoir des contacts.</p>
    <button class="btn btn-primary" onclick="NFCContacts.showAddModal()">
        <i class="fas fa-plus me-2"></i>Ajouter un contact manuel
    </button>
</div>

<!-- Error -->
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
                    <!-- Rempli par JavaScript -->
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
                            <!-- Généré par JavaScript -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Vue Grille -->
    <div id="contactsGridView" class="d-none">
        <div id="contactsGrid" class="row">
            <!-- Rempli par JavaScript -->
        </div>
    </div>
</div>

<!-- JAVASCRIPT CONFIGURATION -->
<script>
// Configuration pour le JavaScript
window.nfcContactsConfig = <?php echo json_encode($contacts_config); ?>;

// Variable pour empêcher le double chargement
window.nfcContactsPreventAutoLoad = true;

console.log('🔧 Configuration Leads injectée:', window.nfcContactsConfig);
</script>

<!-- CHARGEMENT DU JAVASCRIPT -->
<script src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))); ?>assets/js/dashboard/contacts-manager.js"></script>

<!-- DÉMARRAGE MANUEL -->
<script>
jQuery(document).ready(function($) {
    console.log('🚀 Leads.php - Démarrage manuel NFCContacts');
    
    // Réinitialiser la configuration
    if (typeof NFCContacts !== 'undefined') {
        NFCContacts.config = window.nfcContactsConfig;
        NFCContacts.init();
    } else {
        console.error('❌ NFCContacts non trouvé');
    }
});
</script>

<!-- MODALS (À AJOUTER) -->
<!-- TODO: Copier les modals de contacts.php ici -->