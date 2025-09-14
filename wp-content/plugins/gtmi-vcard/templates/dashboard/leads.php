<?php
/**
 * Template Dashboard Leads - Version Compatible contacts.php
 * Fichier: wp-content/plugins/gtmi-vcard/templates/dashboard/leads.php
 * 
 * Structure DOM IDENTIQUE à contacts.php pour éviter les conflits
 */

if (!defined('ABSPATH')) {
    exit;
}

// ================================================================================
// FONCTIONS MANQUANTES - Créées pour éviter les erreurs
// ================================================================================

if (!function_exists('nfc_get_vcard_contacts')) {
    function nfc_get_vcard_contacts($vcard_id, $limit = 1000) {
        global $wpdb;
        
        error_log("🔍 nfc_get_vcard_contacts() appelée pour vCard ID: " . $vcard_id);
        
        $exact_pattern = 'a:1:{i:0;s:' . strlen($vcard_id) . ':"' . $vcard_id . '";}';
        
        $query = "
            SELECT 
                l.ID,
                l.post_date as created_at,
                pm_firstname.meta_value as firstname,
                pm_lastname.meta_value as lastname, 
                pm_email.meta_value as email,
                pm_mobile.meta_value as mobile,
                pm_society.meta_value as society,
                pm_source.meta_value as source,
                pm_contact_datetime.meta_value as contact_datetime,
                %d as vcard_id
            FROM {$wpdb->posts} l
            LEFT JOIN {$wpdb->postmeta} pm_link ON l.ID = pm_link.post_id AND pm_link.meta_key = 'linked_virtual_card'
            LEFT JOIN {$wpdb->postmeta} pm_firstname ON l.ID = pm_firstname.post_id AND pm_firstname.meta_key = 'firstname'
            LEFT JOIN {$wpdb->postmeta} pm_lastname ON l.ID = pm_lastname.post_id AND pm_lastname.meta_key = 'lastname'
            LEFT JOIN {$wpdb->postmeta} pm_email ON l.ID = pm_email.post_id AND pm_email.meta_key = 'email'
            LEFT JOIN {$wpdb->postmeta} pm_mobile ON l.ID = pm_mobile.post_id AND pm_mobile.meta_key = 'mobile'
            LEFT JOIN {$wpdb->postmeta} pm_society ON l.ID = pm_society.post_id AND pm_society.meta_key = 'society'
            LEFT JOIN {$wpdb->postmeta} pm_source ON l.ID = pm_source.post_id AND pm_source.meta_key = 'source'
            LEFT JOIN {$wpdb->postmeta} pm_contact_datetime ON l.ID = pm_contact_datetime.post_id AND pm_contact_datetime.meta_key = 'contact_datetime'
            WHERE l.post_type = 'lead'
            AND l.post_status = 'publish'
            AND pm_link.meta_value = %s
            ORDER BY l.post_date DESC
        ";
        
        $results = $wpdb->get_results($wpdb->prepare($query, $vcard_id, $exact_pattern), ARRAY_A);
        
        error_log("📊 Contacts trouvés: " . count($results) . " pour vCard " . $vcard_id);
        
        return $results;
    }
}

if (!function_exists('nfc_get_contacts_trend')) {
    function nfc_get_contacts_trend($vcard_id = 0) {
        return 0;
    }
}

// ================================================================================
// LOGIQUE D'ADAPTATION - Identique à la version précédente
// ================================================================================

$user_id = get_current_user_id();
$user_vcards = nfc_get_user_vcard_profiles($user_id);
$selected_vcard_id = isset($_GET['vcard_id']) ? (int)$_GET['vcard_id'] : null;

if (empty($user_vcards)) {
    include plugin_dir_path(__FILE__) . 'partials/no-products-state.php';
    return;
}

$is_multi_profile = count($user_vcards) > 1;
$show_profile_filter = $is_multi_profile;
$current_vcard = null;
$contacts = [];

if ($is_multi_profile) {
    $page_title = "Contacts Multi-Profils";
    $page_subtitle = count($user_vcards) . " profils vCard configurés";
    
    if ($selected_vcard_id) {
        $contacts = nfc_get_vcard_contacts($selected_vcard_id);
        $current_vcard = array_filter($user_vcards, function($card) use ($selected_vcard_id) {
            return $card['vcard_id'] == $selected_vcard_id;
        });
        $current_vcard = reset($current_vcard) ?: null;
    } else {
        $contacts = nfc_get_enterprise_contacts($user_id, null, 1000);
    }
} else {
    $page_title = "Mes contacts"; 
    $page_subtitle = "Gérez les contacts reçus via votre vCard";
    $current_vcard = $user_vcards[0];
    $selected_vcard_id = $current_vcard['vcard_id'];
    $contacts = nfc_get_vcard_contacts($selected_vcard_id);
}

if (empty($contacts)) {
    $empty_state_data = [
        'user_vcards' => $user_vcards,
        'is_multi_profile' => $is_multi_profile,
        'current_vcard' => $current_vcard
    ];
    include plugin_dir_path(__FILE__) . 'partials/empty-contacts-state.php';
    return;
}

// Configuration utilisateur
$current_user = wp_get_current_user();
$user_display_name = '';

if ($current_vcard && isset($current_vcard['vcard_data'])) {
    $user_display_name = nfc_format_vcard_full_name($current_vcard['vcard_data']);
} else if (!empty($user_vcards)) {
    $user_display_name = nfc_format_vcard_full_name($user_vcards[0]['vcard_data']);
}

if (empty($user_display_name)) {
    $user_display_name = trim($current_user->first_name . ' ' . $current_user->last_name);
}

error_log("📊 DEBUG Leads - Contacts récupérés: " . count($contacts));

// Configuration JavaScript identique à contacts.php
$contacts_config = [
    'vcard_id' => $current_vcard['vcard_id'],
    'is_multi_profile' => $is_multi_profile,
    'user_vcards' => $user_vcards,
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'user_name' => $user_display_name,
    'initial_contacts' => $contacts
];

// Ne PAS charger d'assets CSS/JS car contacts-manager.js se charge automatiquement
?>

<!-- STRUCTURE DOM IDENTIQUE À contacts.php -->

<!-- HEADER SECTION - IDENTIQUE À contacts.php -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1 text-primary">
                    <i class="fas fa-users me-2"></i>
                    <?php echo esc_html($page_title); ?>
                </h1>
                <p class="text-muted mb-0">
                    <?php echo esc_html($page_subtitle); ?>
                    <?php if (!$is_multi_profile && $user_display_name): ?>
                        : <strong><?php echo esc_html($user_display_name); ?></strong>
                    <?php endif; ?>
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-secondary btn-sm" onclick="exportContacts()" title="Exporter les contacts">
                    <i class="fas fa-download me-1"></i>
                    Exporter CSV
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="importContacts()" title="Importer des contacts">
                    <i class="fas fa-upload me-1"></i>
                    Importer
                </button>
                <button class="btn btn-primary btn-sm" onclick="showAddContactModal()" title="Ajouter un contact">
                    <i class="fas fa-plus me-1"></i>
                    Ajouter contact
                </button>
            </div>
        </div>
    </div>
</div>

<!-- STATS CARDS - IDENTIQUE À contacts.php -->
<div class="row g-3 mb-4">
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

<!-- CONTROLS SECTION - IDENTIQUE À contacts.php + AJOUT FILTRE PROFIL -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    
                    <!-- RECHERCHE - ID IDENTIQUE À contacts.php -->
                    <div class="col-md-4">
                        <div class="position-relative">
                            <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" class="form-control ps-5" id="contactsSearch" 
                                   placeholder="Rechercher un contact..." autocomplete="off">
                        </div>
                    </div>
                    
                    <!-- 🆕 FILTRE PAR PROFIL (si multi-profils) -->
                    <?php if ($show_profile_filter): ?>
                    <div class="col-md-2">
                        <select class="form-select" id="profileFilter" onchange="filterByProfile()">
                            <option value="">Tous les profils (<?php echo count($contacts); ?>)</option>
                            <?php foreach ($user_vcards as $vcard): ?>
                                <option value="<?php echo esc_attr($vcard['vcard_id']); ?>" 
                                        <?php selected($selected_vcard_id, $vcard['vcard_id']); ?>>
                                    <?php 
                                    echo esc_html(nfc_format_vcard_full_name($vcard['vcard_data'])); 
                                    $vcard_contacts = nfc_get_vcard_contacts($vcard['vcard_id']);
                                    echo ' (' . count($vcard_contacts) . ')';
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <!-- FILTRES - IDS IDENTIQUES À contacts.php -->
                    <div class="<?php echo $show_profile_filter ? 'col-md-2' : 'col-md-3'; ?>">
                        <select class="form-select" id="sourceFilter">
                            <option value="">Toutes sources</option>
                            <option value="web">Site Web</option>
                            <option value="qr">QR Code</option>
                            <option value="nfc">NFC</option>
                            <option value="manual">Manuel</option>
                        </select>
                    </div>
                    
                    <div class="<?php echo $show_profile_filter ? 'col-md-2' : 'col-md-3'; ?>">
                        <select class="form-select" id="dateFilter">
                            <option value="">Toute période</option>
                            <option value="today">Aujourd'hui</option>
                            <option value="week">Cette semaine</option>
                            <option value="month">Ce mois</option>
                            <option value="quarter">Ce trimestre</option>
                        </select>
                    </div>
                    
                    <!-- BOUTONS VUE - IDS IDENTIQUES À contacts.php -->
                    <div class="<?php echo $show_profile_filter ? 'col-md-2' : 'col-md-2'; ?>">
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary active" id="tableViewBtn" 
                                    title="Vue tableau">
                                <i class="fas fa-list"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="gridViewBtn" 
                                    title="Vue grille">
                                <i class="fas fa-th"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CONTENEUR PRINCIPAL - IDS IDENTIQUES À contacts.php -->
<div class="row">
    <div class="col-12">
        
        <!-- Loading State - ID IDENTIQUE -->
        <div id="contactsLoading" class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="text-muted mt-2 mb-0">Chargement des contacts...</p>
            </div>
        </div>

        <!-- Empty State - ID IDENTIQUE -->
        <div id="contactsEmpty" class="card border-0 shadow-sm" style="display: none;">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Aucun contact trouvé</h4>
                <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                <button class="btn btn-outline-primary" onclick="clearAllFilters()">
                    <i class="fas fa-refresh me-1"></i>Réinitialiser les filtres
                </button>
            </div>
        </div>

        <!-- Error State - ID IDENTIQUE -->
        <div id="contactsError" class="card border-0 shadow-sm" style="display: none;">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h4 class="text-danger">Erreur de chargement</h4>
                <p class="text-muted">Une erreur est survenue lors du chargement des contacts</p>
            </div>
        </div>

        <!-- Table View - ID IDENTIQUE À contacts.php -->
        <div id="contactsTableView" class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="contactsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Entreprise</th>
                                <th>Source</th>
                                
                                <!-- 🆕 Colonne profil source (si multi-profils) -->
                                <?php if ($show_profile_filter): ?>
                                <th>Profil Source</th>
                                <?php endif; ?>
                                
                                <th>Date</th>
                                <th class="text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="contactsTableBody">
                            <!-- Rempli par JavaScript contacts-manager.js -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination - ID IDENTIQUE -->
            <div class="card-footer bg-white" id="contactsPaginationWrapper">
                <div class="row align-items-center">
                    <div class="col">
                        <small class="text-muted" id="contactsCounter">
                            Affichage des résultats
                        </small>
                    </div>
                    <div class="col-auto">
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="contactsPagination">
                                <!-- Pagination générée par JavaScript -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid View - ID IDENTIQUE À contacts.php -->
        <div id="contactsGridView" class="row" style="display: none;">
            <!-- Rempli par JavaScript contacts-manager.js -->
        </div>

        <!-- Actions en Lot - ID IDENTIQUE -->
        <div class="bulk-actions mt-3" id="bulkActions" style="display: none;">
            <div class="alert alert-info d-flex align-items-center">
                <div class="me-3">
                    <span id="selectedCount">0</span> contact(s) sélectionné(s)
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteBulk()">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="exportSelected()">
                        <i class="fas fa-download me-1"></i>Exporter
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" onclick="clearSelection()">
                    Désélectionner
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODALS IDENTIQUES À contacts.php -->
<!-- Modal Ajouter Contact -->
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Ajouter un Contact
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addContactForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="firstname" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="lastname" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" name="mobile">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Entreprise</label>
                                <input type="text" class="form-control" name="society">
                            </div>
                        </div>
                        
                        <?php if ($show_profile_filter): ?>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Associer au profil <span class="text-danger">*</span></label>
                                <select class="form-select" name="vcard_id" required>
                                    <option value="">Choisir un profil...</option>
                                    <?php foreach ($user_vcards as $vcard): ?>
                                        <option value="<?php echo esc_attr($vcard['vcard_id']); ?>"
                                                <?php selected($selected_vcard_id, $vcard['vcard_id']); ?>>
                                            <?php echo esc_html(nfc_format_vcard_full_name($vcard['vcard_data'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="vcard_id" value="<?php echo esc_attr($selected_vcard_id); ?>">
                        <?php endif; ?>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Notes (optionnel)</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Informations supplémentaires..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Ajouter le Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import CSV -->
<div class="modal fade" id="importContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload me-2"></i>Importer des Contacts
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Fichier CSV</label>
                    <input type="file" class="form-control" id="csvFile" accept=".csv">
                    <div class="form-text">
                        Format attendu: Prénom,Nom,Email,Téléphone,Entreprise<br>
                        <small class="text-muted">Assurez-vous de définir les en-têtes.</small>
                    </div>
                </div>
                
                <?php if ($show_profile_filter): ?>
                <div class="mb-3">
                    <label class="form-label">Associer tous les contacts au profil</label>
                    <select class="form-select" id="importVcardId" required>
                        <option value="">Choisir un profil...</option>
                        <?php foreach ($user_vcards as $vcard): ?>
                            <option value="<?php echo esc_attr($vcard['vcard_id']); ?>"
                                    <?php selected($selected_vcard_id, $vcard['vcard_id']); ?>>
                                <?php echo esc_html(nfc_format_vcard_full_name($vcard['vcard_data'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div id="csvPreview" class="mt-3 d-none">
                    <h6>Aperçu (5 premiers contacts) :</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Prénom</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                </tr>
                            </thead>
                            <tbody id="csvPreviewBody">
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small" id="csvStats"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="importBtn" onclick="importCSV()" disabled>
                    <i class="fas fa-upload me-2"></i>Importer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails Contact -->
<div class="modal fade" id="contactDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails du contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contactDetailsContent">
                <!-- Contact details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="editContactFromModal()">
                    <i class="fas fa-edit me-1"></i>Modifier
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CONFIGURATION JAVASCRIPT POUR contacts-manager.js -->
<script>
// Configuration globale pour contacts-manager.js (EXACTEMENT comme dans class-dashboard-manager.php)
window.nfcContactsConfig = <?php echo json_encode($contacts_config); ?>;
console.log('🔧 KEYS de config reçues:', Object.keys(window.nfcContactsConfig));

console.log('📧 Configuration NFCContacts injectée AVANT script:', window.nfcContactsConfig);
console.log('🔧 DEBUG - user_id reçu:', window.nfcContactsConfig.user_id);
console.log('🔧 DEBUG - is_multi_profile:', window.nfcContactsConfig.is_multi_profile);
console.log('🔧 DEBUG - selected_vcard_id:', window.nfcContactsConfig.selected_vcard_id);

// Vérification que tout est là
if (window.nfcContactsConfig.vcard_id && window.nfcContactsConfig.api_url) {
    console.log('✅ Configuration NFCContacts valide');
} else {
    console.error('❌ Configuration NFCContacts invalide:', window.nfcContactsConfig);
}

// 🔧 OVERRIDE COMPLET pour utiliser le nouvel endpoint multi-profils
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Override loadContacts() pour nouvel endpoint multi-profils...');
    
    // Attendre que contacts-manager.js soit chargé
    const waitForNFCContacts = setInterval(() => {
        if (window.NFCContacts && window.NFCContacts.loadContacts) {
            clearInterval(waitForNFCContacts);
            
            console.log('📧 NFCContacts détecté, override en cours...');
            
            // OVERRIDE COMPLET de loadContacts pour utiliser le nouvel endpoint
            window.NFCContacts.loadContacts = function() {
                console.log('🔧 loadContacts() overridé - utilisation endpoint multi-profils');
                
                this.isLoading = true;
                this.showLoadingState();
                
                // 🆕 Déterminer l'URL selon le contexte avec vérifications
                let apiUrl;
                const config = window.nfcContactsConfig;
                
                console.log('🔧 Détermination URL - config:', {
                    is_multi_profile: config.is_multi_profile,
                    selected_vcard_id: config.selected_vcard_id,
                    user_id: config.user_id,
                    vcard_id: config.vcard_id
                });
                
                if (config.is_multi_profile && !config.selected_vcard_id) {
                    // Mode multi-profils global : utiliser endpoint user
                    if (!config.user_id) {
                        console.error('❌ user_id manquant pour endpoint multi-profils');
                        this.showError('Configuration user_id manquante');
                        return;
                    }
                    apiUrl = `${config.api_url}leads/user/${config.user_id}`;
                    console.log('🌐 Mode multi-profils global - URL:', apiUrl);
                } else {
                    // Mode profil unique ou filtré : utiliser endpoint classique
                    const vcardId = config.selected_vcard_id || config.vcard_id;
                    if (!vcardId) {
                        console.error('❌ vcard_id manquant pour endpoint classique');
                        this.showError('Configuration vcard_id manquante');
                        return;
                    }
                    apiUrl = `${config.api_url}leads/${vcardId}`;
                    console.log('🌐 Mode profil unique/filtré - URL:', apiUrl);
                }
                
                fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                }.bind(this))
                .then(response => {
                    console.log('📡 Réponse API Status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    return response.json();
                })
                .then(data => {
                    console.log('📦 Données brutes API:', data);
                    
                    this.isLoading = false;
                    this.hideLoadingState();
                    
                    if (data && Array.isArray(data.data)) {
                        // Traitement des contacts reçus
                        this.contacts = data.data.map(contact => ({
                            id: parseInt(contact.ID || contact.id),
                            firstname: contact.firstname || '',
                            lastname: contact.lastname || '',
                            email: contact.email || '',
                            mobile: contact.mobile || '',
                            society: contact.society || '',
                            source: contact.source || 'web',
                            contact_datetime: contact.contact_datetime || contact.created_at,
                            created_at: contact.created_at,
                            vcard_id: contact.vcard_id || 0,
                            vcard_source_name: contact.vcard_source_name || ''
                        }));
                        
                        this.filteredContacts = [...this.contacts];
                        
                        console.log('✅', this.contacts.length, 'contacts chargés depuis l\'API !');
                        
                        if (this.contacts.length > 0) {
                            if (this.renderTable) {
                                this.renderTable();
                            }
                            if (this.updateStats) {
                                this.updateStats();
                            }
                            if (this.renderPagination) {
                                this.renderPagination();
                            }
                        } else {
                            this.showEmptyState();
                        }
                    } else {
                        console.warn('⚠️ Format de données API inattendu:', data);
                        this.showEmptyState(); // Plutôt qu'erreur pour données vides
                    }
                }.bind(this))
                .catch(error => {
                    console.error('❌ Erreur API:', error);
                    this.isLoading = false;
                    this.showError('Erreur de chargement: ' + error.message);
                });
            };
            
            // OVERRIDE du renderTable pour ajouter colonne profil source
            <?php if ($is_multi_profile): ?>
            const originalRenderTable = window.NFCContacts.renderTable;
            window.NFCContacts.renderTable = function() {
                console.log('🔧 renderTable() overridé pour multi-profils');
                
                // D'abord appeler le rendu original
                if (originalRenderTable) {
                    originalRenderTable.call(this);
                }
                
                // Puis ajouter nos colonnes personnalisées
                const tbody = document.getElementById('contactsTableBody');
                if (tbody && this.filteredContacts) {
                    const rows = tbody.querySelectorAll('tr');
                    rows.forEach((row, index) => {
                        const pageIndex = (this.currentPage - 1) * this.itemsPerPage + index;
                        const contact = this.filteredContacts[pageIndex];
                        
                        if (contact && contact.vcard_source_name) {
                            // Insérer la cellule profil source avant la dernière cellule (actions)
                            const cells = row.querySelectorAll('td');
                            if (cells.length > 0) {
                                const profileCell = document.createElement('td');
                                profileCell.innerHTML = `<small class="text-muted">${contact.vcard_source_name}</small>`;
                                row.insertBefore(profileCell, cells[cells.length - 1]);
                            }
                        }
                    });
                }
            };
            <?php endif; ?>
            
            console.log('✅ Override terminé, initialisation NFCContacts...');
            
            // Forcer l'initialisation
            window.NFCContacts.init();
        }
    }, 100);
    
    // Timeout de sécurité
    setTimeout(() => {
        clearInterval(waitForNFCContacts);
        if (!window.NFCContacts) {
            console.error('❌ NFCContacts non trouvé après 10 secondes');
        }
    }, 10000);
});

// 🆕 FONCTION PERSONNALISÉE pour le filtre par profil
function filterByProfile() {
    const profileFilter = document.getElementById('profileFilter');
    if (!profileFilter) return;
    
    const selectedVcardId = profileFilter.value;
    
    if (selectedVcardId) {
        const url = new URL(window.location);
        url.searchParams.set('vcard_id', selectedVcardId);
        window.location.href = url.toString();
    } else {
        const url = new URL(window.location);
        url.searchParams.delete('vcard_id');
        window.location.href = url.toString();
    }
}

console.log('✅ Script d\'override leads.php avec endpoint multi-profils chargé');
</script>