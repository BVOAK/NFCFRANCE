<?php
/**
 * Template: Gestion des contacts - Dashboard NFC
 * 
 * Fichier: templates/dashboard/simple/contacts.php
 * Gestion complète des leads/contacts avec CRUD
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables globales disponibles depuis le routing
global $nfc_vcard, $nfc_current_page;
$vcard = $nfc_vcard;
$vcard_id = $vcard->ID;

// Données utilisateur pour le contexte
$current_user = wp_get_current_user();
$first_name = get_post_meta($vcard_id, 'first_name', true) ?: $current_user->first_name;
$last_name = get_post_meta($vcard_id, 'last_name', true) ?: $current_user->last_name;
$full_name = trim($first_name . ' ' . $last_name);

// URL publique de la vCard pour contexte
$public_url = get_permalink($vcard_id);

// Enqueue des assets spécifiques à cette page
$plugin_url = plugin_dir_url(dirname(dirname(dirname(__FILE__))));

// Configuration JavaScript
$contacts_config = [
    'vcard_id' => $vcard_id,
    'api_url' => home_url('/wp-json/gtmi_vcard/v1/'),
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'public_url' => $public_url,
    'user_name' => $full_name,
    'i18n' => [
        'loading' => __('Chargement...', 'gtmi_vcard'),
        'error' => __('Une erreur est survenue', 'gtmi_vcard'),
        'success' => __('Action réalisée avec succès', 'gtmi_vcard'),
        'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer ce contact ?', 'gtmi_vcard'),
        'confirm_delete_multiple' => __('Êtes-vous sûr de vouloir supprimer ces contacts ?', 'gtmi_vcard'),
        'no_contacts' => __('Aucun contact trouvé', 'gtmi_vcard'),
        'search_placeholder' => __('Rechercher un contact...', 'gtmi_vcard'),
    ]
];

//wp_localize_script('nfc-contacts-manager', 'nfcContactsConfig', $contacts_config);
?>

<!-- HEADER SECTION - LAYOUT ORIGINAL -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1 text-primary">
                    <i class="fas fa-users me-2"></i>
                    Mes contacts
                </h1>
                <p class="text-muted mb-0">
                    Gérez les contacts reçus via votre vCard : <strong><?php echo esc_html($full_name); ?></strong>
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

<!-- STATS CARDS - LAYOUT ORIGINAL -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-primary mb-2">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <h3 class="h4 mb-1" id="totalContacts">0</h3>
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
                <h3 class="h4 mb-1" id="newContacts">0</h3>
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
                <h3 class="h4 mb-1" id="totalCompanies">0</h3>
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
                <h3 class="h4 mb-1" id="qrContacts">0</h3>
                <p class="text-muted small mb-0">Via QR Code</p>
            </div>
        </div>
    </div>
</div>

<!-- CONTROLS SECTION - LAYOUT ORIGINAL -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <!-- Recherche -->
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" 
                                   id="contactsSearch" 
                                   placeholder="Rechercher un contact...">
                        </div>
                    </div>
                    
                    <!-- Filtres -->
                    <div class="col-md-2">
                        <select class="form-select" id="sourceFilter">
                            <option value="">Toutes sources</option>
                            <option value="qr">QR Code</option>
                            <option value="nfc">NFC</option>
                            <option value="web">Web</option>
                            <option value="manual">Manuel</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="dateFilter">
                            <option value="">Toutes dates</option>
                            <option value="today">Aujourd'hui</option>
                            <option value="week">Cette semaine</option>
                            <option value="month">Ce mois</option>
                        </select>
                    </div>
                    
                    <!-- Toggle views -->
                    <div class="col-md-2">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="viewList" checked>
                            <label class="btn btn-outline-secondary" for="viewList">
                                <i class="fas fa-list"></i>
                            </label>
                            <input type="radio" class="btn-check" name="viewMode" id="viewGrid">
                            <label class="btn btn-outline-secondary" for="viewGrid">
                                <i class="fas fa-th"></i>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Actions groupées -->
                    <div class="col-md-2">
                        <button class="btn btn-outline-danger w-100 d-none" id="bulkDeleteBtn" onclick="deleteSelectedContacts()">
                            <i class="fas fa-trash me-1"></i>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CONTENT AREA - LAYOUT ORIGINAL -->
<div class="row">
    <div class="col-12">
        
        <!-- Loading State -->
        <div class="text-center py-5" id="contactsLoading">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="text-muted">Chargement de vos contacts...</p>
        </div>

        <div class="text-center py-5 d-none" id="contactsError">
    <div class="mb-4">
        <i class="fas fa-exclamation-triangle fa-4x text-danger"></i>
    </div>
    <h5 class="text-danger mb-2">Erreur de chargement</h5>
    <p class="text-muted mb-4" id="contactsErrorMessage">
        Une erreur est survenue lors du chargement des contacts.
    </p>
    <button class="btn btn-primary" onclick="NFCContacts.loadContacts()">
        <i class="fas fa-redo me-2"></i>
        Réessayer
    </button>
</div>

        <!-- Table View -->
        <div class="contacts-table-view d-none" id="contactsTableView">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Entreprise</th>
                                <th>Source</th>
                                <th>Date</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="contactsTableBody">
                            <!-- Content generated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Grid View -->
        <div class="contacts-grid-view d-none" id="contactsGridView">
            <div class="row g-3" id="contactsGrid">
                <!-- Content generated by JavaScript -->
            </div>
        </div>

        <!-- Empty State -->
        <div class="contacts-empty text-center py-5 d-none" id="contactsEmpty">
            <div class="mb-4">
                <i class="fas fa-users-slash fa-4x text-muted"></i>
            </div>
            <h5 class="text-muted mb-2">Aucun contact trouvé</h5>
            <p class="text-muted mb-4">
                Les personnes qui scanneront votre QR Code ou consulteront votre vCard apparaîtront ici.
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <button class="btn btn-primary" onclick="showAddContactModal()">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter un contact
                </button>
                <a href="?page=qr-codes" class="btn btn-outline-primary">
                    <i class="fas fa-qrcode me-2"></i>
                    Générer un QR Code
                </a>
                <a href="<?php echo esc_url($public_url); ?>" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-external-link-alt me-2"></i>
                    Voir ma vCard
                </a>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4 d-none" id="contactsPaginationWrapper">
            <nav>
                <ul class="pagination" id="contactsPagination">
                    <!-- Pagination generated by JavaScript -->
                </ul>
            </nav>
        </div>

    </div>
</div>

<!-- MODALES -->

<!-- Modal Ajouter/Modifier Contact -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalTitle">Ajouter un contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm">
                    <input type="hidden" id="contactId" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="firstname" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="firstname" name="firstname" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastname" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lastname" name="lastname" required>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="mobile" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="mobile" name="mobile">
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label for="society" class="form-label">Entreprise</label>
                            <input type="text" class="form-control" id="society" name="society">
                        </div>
                        <div class="col-md-6">
                            <label for="post" class="form-label">Poste</label>
                            <input type="text" class="form-control" id="post" name="post">
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label for="source" class="form-label">Source</label>
                        <select class="form-select" id="source" name="source">
                            <option value="manual">Ajout manuel</option>
                            <option value="web">Site web</option>
                            <option value="qr">QR Code</option>
                            <option value="nfc">NFC</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveContactBtn" onclick="saveContact()">
                    <i class="fas fa-save me-2"></i>
                    Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import CSV -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importer des contacts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="csvFile" class="form-label">Fichier CSV</label>
                    <input type="file" class="form-control" id="csvFile" accept=".csv" onchange="handleFileSelect(event)">
                    <div class="form-text">
                        Format attendu : prénom, nom, email, téléphone, entreprise, poste
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Format CSV attendu :</strong><br>
                    <code>firstname,lastname,email,mobile,society,post</code><br>
                    <small>La première ligne doit contenir les en-têtes.</small>
                </div>
                
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
                    <i class="fas fa-upload me-2"></i>
                    Importer
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
                <button type="button" class="btn btn-primary" id="editContactFromDetails" onclick="editContactFromDetails()">
                    <i class="fas fa-edit me-2"></i>
                    Modifier
                </button>
                <button type="button" class="btn btn-danger" id="deleteContactFromDetails" onclick="deleteContactFromDetails()">
                    <i class="fas fa-trash me-2"></i>
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// Configuration et état
const contactsConfig = {
    vcard_id: <?php echo $vcard_id; ?>,
    api_url: '<?php echo home_url('/wp-json/gtmi_vcard/v1/'); ?>',
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('nfc_dashboard_nonce'); ?>'
};

let contacts = [];
let filteredContacts = [];
let currentPage = 1;
let selectedContacts = [];
let csvData = [];
let currentContactId = null;
const itemsPerPage = 10;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 DOMContentLoaded - Init contacts avec config:', contactsConfig);
    loadContacts();
    initEventListeners();
});

// Charger les contacts
function loadContacts() {
    console.log('📡 loadContacts() appelée');
    const url = `${contactsConfig.api_url}leads/${contactsConfig.vcard_id}`;
    console.log('🌐 URL construite:', url);
    
    document.getElementById('contactsLoading').classList.remove('d-none');
    
    fetch(url)
        .then(response => {
            console.log('📡 Response reçue:', response.status, response.statusText);
            return response.json();
        })
        .then(data => {
            console.log('📦 Données contacts reçues:', data);
            contacts = Array.isArray(data) ? data : (data.data || []);
            console.log('📦 Contacts traités:', contacts);
            
            applyFilters();
            updateStats();
            
            document.getElementById('contactsLoading').classList.add('d-none');
            console.log('✅ Chargement terminé');
        })
        .catch(error => {
            console.error('❌ Erreur chargement contacts:', error);
            document.getElementById('contactsLoading').classList.add('d-none');
            document.getElementById('contactsEmpty').classList.remove('d-none');
        });
}

// Initialiser les event listeners
function initEventListeners() {
    console.log('🎯 Init event listeners');
    
    // Recherche
    const searchInput = document.getElementById('contactsSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            console.log('🔍 Recherche:', e.target.value);
            applyFilters();
        });
    }
    
    // Filtres
    document.getElementById('sourceFilter')?.addEventListener('change', applyFilters);
    document.getElementById('dateFilter')?.addEventListener('change', applyFilters);
    
    // Toggle views
    document.querySelectorAll('input[name="viewMode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('👁️ Vue changée:', this.id);
            renderContacts();
        });
    });
    
    // Select all
    document.getElementById('selectAll')?.addEventListener('change', function() {
        toggleSelectAll(this.checked);
    });
}

// Appliquer les filtres
function applyFilters() {
    console.log('🔍 applyFilters() appelée');
    
    filteredContacts = [...contacts];
    
    // Recherche
    const searchValue = document.getElementById('contactsSearch')?.value?.toLowerCase() || '';
    if (searchValue) {
        filteredContacts = filteredContacts.filter(contact => {
            const searchText = [
                contact.firstname,
                contact.lastname, 
                contact.email,
                contact.mobile,
                contact.society
            ].join(' ').toLowerCase();
            
            return searchText.includes(searchValue);
        });
    }
    
    // Filtre source
    const sourceValue = document.getElementById('sourceFilter')?.value || '';
    if (sourceValue) {
        filteredContacts = filteredContacts.filter(contact => {
            return (contact.source || 'web') === sourceValue;
        });
    }
    
    // Filtre date
    const dateValue = document.getElementById('dateFilter')?.value || '';
    if (dateValue) {
        const now = new Date();
        filteredContacts = filteredContacts.filter(contact => {
            const contactDate = new Date(contact.contact_datetime || contact.created_at);
            
            switch (dateValue) {
                case 'today':
                    return contactDate.toDateString() === now.toDateString();
                case 'week':
                    const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                    return contactDate >= weekAgo;
                case 'month':
                    const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                    return contactDate >= monthAgo;
                default:
                    return true;
            }
        });
    }
    
    console.log('🔍 Contacts filtrés:', filteredContacts.length);
    currentPage = 1;
    renderContacts();
}

// Afficher les contacts
function renderContacts() {
    console.log('🎨 renderContacts() appelée');
    
    if (filteredContacts.length === 0) {
        document.getElementById('contactsTableView').classList.add('d-none');
        document.getElementById('contactsGridView').classList.add('d-none');
        document.getElementById('contactsEmpty').classList.remove('d-none');
        document.getElementById('contactsPaginationWrapper').classList.add('d-none');
        return;
    }
    
    document.getElementById('contactsEmpty').classList.add('d-none');
    
    const viewMode = document.querySelector('input[name="viewMode"]:checked')?.id;
    console.log('👁️ Vue actuelle:', viewMode);
    
    if (viewMode === 'viewList') {
        renderTableView();
    } else {
        renderGridView();
    }
    
    renderPagination();
}

// Vue tableau
function renderTableView() {
    console.log('📊 renderTableView()');
    
    document.getElementById('contactsTableView').classList.remove('d-none');
    document.getElementById('contactsGridView').classList.add('d-none');
    
    const tbody = document.getElementById('contactsTableBody');
    const start = (currentPage - 1) * itemsPerPage;
    const pageContacts = filteredContacts.slice(start, start + itemsPerPage);
    
    tbody.innerHTML = pageContacts.map(contact => `
        <tr>
            <td>
                <input type="checkbox" class="form-check-input contact-checkbox" 
                       value="${contact.id}" onchange="toggleContactSelection(${contact.id})">
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 32px; height: 32px; font-size: 12px;">
                            ${getInitials(contact.firstname, contact.lastname)}
                        </div>
                    </div>
                    <div>
                        <div class="fw-medium">${contact.firstname || ''} ${contact.lastname || ''}</div>
                        ${contact.post ? `<small class="text-muted">${contact.post}</small>` : ''}
                    </div>
                </div>
            </td>
            <td>${contact.email || 'N/A'}</td>
            <td>${contact.mobile || 'N/A'}</td>
            <td>${contact.society || 'N/A'}</td>
            <td><span class="badge bg-secondary">${getSourceLabel(contact.source || 'web')}</span></td>
            <td>${formatDate(contact.contact_datetime || contact.created_at)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewContact(${contact.id})" title="Voir">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-secondary" onclick="editContact(${contact.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteContact(${contact.id})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    console.log('📊 Table rendue avec', pageContacts.length, 'contacts');
}

// Vue grille
function renderGridView() {
    console.log('🎛️ renderGridView()');
    
    document.getElementById('contactsTableView').classList.add('d-none');
    document.getElementById('contactsGridView').classList.remove('d-none');
    
    const grid = document.getElementById('contactsGrid');
    const start = (currentPage - 1) * itemsPerPage;
    const pageContacts = filteredContacts.slice(start, start + itemsPerPage);
    
    grid.innerHTML = pageContacts.map(contact => `
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <input type="checkbox" class="form-check-input me-2 contact-checkbox" 
                               value="${contact.id}" onchange="toggleContactSelection(${contact.id})">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 48px; height: 48px;">
                            ${getInitials(contact.firstname, contact.lastname)}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${contact.firstname || ''} ${contact.lastname || ''}</h6>
                            ${contact.society ? `<small class="text-muted d-block">${contact.society}</small>` : ''}
                            <span class="badge bg-secondary mt-1">${getSourceLabel(contact.source || 'web')}</span>
                        </div>
                    </div>
                    
                    <div class="contact-info small">
                        ${contact.email ? `
                            <div class="mb-2">
                                <i class="fas fa-envelope text-muted me-2"></i>
                                <a href="mailto:${contact.email}" class="text-decoration-none">${contact.email}</a>
                            </div>
                        ` : ''}
                        
                        ${contact.mobile ? `
                            <div class="mb-2">
                                <i class="fas fa-phone text-muted me-2"></i>
                                <a href="tel:${contact.mobile}" class="text-decoration-none">${contact.mobile}</a>
                            </div>
                        ` : ''}
                        
                        <div class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            ${formatDate(contact.contact_datetime || contact.created_at)}
                        </div>
                    </div>
                    
                    <div class="mt-3 d-flex gap-1">
                        <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="viewContact(${contact.id})">
                            <i class="fas fa-eye me-1"></i> Détails
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="editContact(${contact.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteContact(${contact.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    console.log('🎛️ Grid rendue avec', pageContacts.length, 'contacts');
}

// Pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredContacts.length / itemsPerPage);
    const pagination = document.getElementById('contactsPagination');
    const wrapper = document.getElementById('contactsPaginationWrapper');
    
    if (totalPages <= 1) {
        wrapper.classList.add('d-none');
        return;
    }
    
    wrapper.classList.remove('d-none');
    
    let html = '';
    
    // Previous
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Pages
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;
    
    pagination.innerHTML = html;
}

// Changer de page
function changePage(page) {
    const totalPages = Math.ceil(filteredContacts.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    renderContacts();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Mettre à jour les stats
function updateStats() {
    console.log('📈 updateStats()');
    
    document.getElementById('totalContacts').textContent = contacts.length;
    
    // Stats cette semaine
    const weekAgo = new Date();
    weekAgo.setDate(weekAgo.getDate() - 7);
    
    const newThisWeek = contacts.filter(contact => {
        const contactDate = new Date(contact.contact_datetime || contact.created_at);
        return contactDate >= weekAgo;
    }).length;
    
    document.getElementById('newContacts').textContent = newThisWeek;
    
    // Entreprises
    const companies = [...new Set(contacts.map(c => c.society).filter(Boolean))];
    document.getElementById('totalCompanies').textContent = companies.length;
    
    // QR Codes
    const qrContacts = contacts.filter(c => (c.source || 'web') === 'qr').length;
    document.getElementById('qrContacts').textContent = qrContacts;
    
    console.log('📈 Stats mises à jour:', {
        total: contacts.length,
        newWeek: newThisWeek,
        companies: companies.length,
        qr: qrContacts
    });
}

// === FONCTIONS CRUD ===

// Afficher modal d'ajout
function showAddContactModal() {
    console.log('➕ Afficher modal ajout contact');
    
    document.getElementById('contactModalTitle').textContent = 'Ajouter un contact';
    document.getElementById('contactForm').reset();
    document.getElementById('contactId').value = '';
    currentContactId = null;
    
    const modal = new bootstrap.Modal(document.getElementById('contactModal'));
    modal.show();
}

// Sauvegarder contact
function saveContact() {
    console.log('💾 Sauvegarder contact');
    
    const form = document.getElementById('contactForm');
    const formData = new FormData(form);
    
    // Validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // CORRECTION : Format exact attendu par l'API
    const contactData = {
        firstname: formData.get('firstname'),
        lastname: formData.get('lastname'),
        email: formData.get('email'),
        mobile: formData.get('mobile') || '',
        society: formData.get('society') || '',
        post: formData.get('post') || '',
        linked_virtual_card: contactsConfig.vcard_id  // INTEGER, pas array
    };
    
    console.log('💾 Données à envoyer:', contactData);
    
    // Désactiver le bouton
    const saveBtn = document.getElementById('saveContactBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...';
    saveBtn.disabled = true;
    
    // CORRECTION : Seulement POST pour l'ajout (pas de PUT disponible)
    const isEdit = currentContactId !== null;
    
    if (isEdit) {
        // Pour la modification, utiliser WordPress AJAX
        modifyContactViaAjax(contactData);
    } else {
        // Pour l'ajout, utiliser l'API POST
        const url = `${contactsConfig.api_url}lead`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',  // CORRECTION
            },
            body: new URLSearchParams(contactData)  // CORRECTION : URLSearchParams au lieu de JSON
        })
        .then(response => {
            console.log('📡 Status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('✅ Contact ajouté:', data);
            
            if (data.success) {
                // Fermer la modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
                modal.hide();
                
                // Recharger les contacts
                loadContacts();
                
                // Notification
                showNotification('Contact ajouté avec succès!', 'success');
            } else {
                throw new Error(data.message || 'Erreur lors de l\'ajout');
            }
        })
        .catch(error => {
            console.error('❌ Erreur ajout:', error);
            showNotification('Erreur lors de l\'ajout: ' + error.message, 'error');
        })
        .finally(() => {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }
}

// NOUVELLE FONCTION : Modifier contact via AJAX WordPress
function modifyContactViaAjax(contactData) {
    console.log('✏️ Modification via AJAX WordPress');
    
    const ajaxData = {
        action: 'nfc_update_lead',
        nonce: contactsConfig.nonce,
        lead_id: currentContactId,
        ...contactData
    };
    
    fetch(contactsConfig.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(ajaxData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Contact modifié:', data);
        
        if (data.success) {
            // Fermer la modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
            modal.hide();
            
            // Recharger les contacts
            loadContacts();
            
            // Notification
            showNotification('Contact modifié avec succès!', 'success');
        } else {
            throw new Error(data.data || 'Erreur lors de la modification');
        }
    })
    .catch(error => {
        console.error('❌ Erreur modification:', error);
        showNotification('Erreur lors de la modification: ' + error.message, 'error');
    })
    .finally(() => {
        const saveBtn = document.getElementById('saveContactBtn');
        saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
        saveBtn.disabled = false;
    });
}

// Voir contact
function viewContact(contactId) {
    console.log('👁️ Voir contact:', contactId);
    
    const contact = contacts.find(c => c.id == contactId);
    if (!contact) return;
    
    const content = `
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px; font-size: 24px;">
                        ${getInitials(contact.firstname, contact.lastname)}
                    </div>
                    <h4 class="mt-3 mb-1">${contact.firstname || ''} ${contact.lastname || ''}</h4>
                    ${contact.post ? `<p class="text-muted">${contact.post}</p>` : ''}
                    ${contact.society ? `<p class="text-muted"><i class="fas fa-building me-2"></i>${contact.society}</p>` : ''}
                </div>
            </div>
            <div class="col-md-6">
                <div class="contact-details">
                    ${contact.email ? `
                        <div class="mb-3">
                            <label class="form-label fw-medium">Email</label>
                            <div>
                                <a href="mailto:${contact.email}" class="text-decoration-none">
                                    <i class="fas fa-envelope me-2 text-muted"></i>${contact.email}
                                </a>
                            </div>
                        </div>
                    ` : ''}
                    
                    ${contact.mobile ? `
                        <div class="mb-3">
                            <label class="form-label fw-medium">Téléphone</label>
                            <div>
                                <a href="tel:${contact.mobile}" class="text-decoration-none">
                                    <i class="fas fa-phone me-2 text-muted"></i>${contact.mobile}
                                </a>
                            </div>
                        </div>
                    ` : ''}
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Source</label>
                        <div>
                            <span class="badge bg-secondary">${getSourceLabel(contact.source || 'web')}</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Date de contact</label>
                        <div>
                            <i class="fas fa-clock me-2 text-muted"></i>
                            ${formatDate(contact.contact_datetime || contact.created_at)}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contactDetailsContent').innerHTML = content;
    currentContactId = contactId;
    
    const modal = new bootstrap.Modal(document.getElementById('contactDetailsModal'));
    modal.show();
}

// Modifier contact
function editContact(contactId) {
    console.log('✏️ Modifier contact:', contactId);
    
    const contact = contacts.find(c => c.id == contactId);
    if (!contact) return;
    
    document.getElementById('contactModalTitle').textContent = 'Modifier le contact';
    document.getElementById('contactId').value = contactId;
    document.getElementById('firstname').value = contact.firstname || '';
    document.getElementById('lastname').value = contact.lastname || '';
    document.getElementById('email').value = contact.email || '';
    document.getElementById('mobile').value = contact.mobile || '';
    document.getElementById('society').value = contact.society || '';
    document.getElementById('post').value = contact.post || '';
    document.getElementById('source').value = contact.source || 'web';
    
    currentContactId = contactId;
    
    const modal = new bootstrap.Modal(document.getElementById('contactModal'));
    modal.show();
}

// Modifier depuis les détails
function editContactFromDetails() {
    const detailsModal = bootstrap.Modal.getInstance(document.getElementById('contactDetailsModal'));
    detailsModal.hide();
    
    setTimeout(() => {
        editContact(currentContactId);
    }, 300);
}

// CORRECTION : Suppression via AJAX WordPress
function deleteContact(contactId) {
    console.log('🗑️ Supprimer contact:', contactId);
    
    const contact = contacts.find(c => c.id == contactId);
    if (!contact) return;
    
    if (!confirm(`Êtes-vous sûr de vouloir supprimer ${contact.firstname} ${contact.lastname} ?`)) {
        return;
    }
    
    // Utiliser AJAX WordPress au lieu de l'API REST
    const ajaxData = {
        action: 'nfc_delete_lead',
        nonce: contactsConfig.nonce,
        lead_id: contactId
    };
    
    fetch(contactsConfig.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(ajaxData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Contact supprimé:', data);
        
        if (data.success) {
            loadContacts();
            showNotification('Contact supprimé avec succès!', 'success');
        } else {
            throw new Error(data.data || 'Erreur lors de la suppression');
        }
    })
    .catch(error => {
        console.error('❌ Erreur suppression:', error);
        showNotification('Erreur lors de la suppression: ' + error.message, 'error');
    });
}

// Supprimer depuis les détails
function deleteContactFromDetails() {
    const detailsModal = bootstrap.Modal.getInstance(document.getElementById('contactDetailsModal'));
    detailsModal.hide();
    
    setTimeout(() => {
        deleteContact(currentContactId);
    }, 300);
}

// === SÉLECTION MULTIPLE ===

// Toggle sélection contact
function toggleContactSelection(contactId) {
    const index = selectedContacts.indexOf(contactId);
    if (index > -1) {
        selectedContacts.splice(index, 1);
    } else {
        selectedContacts.push(contactId);
    }
    
    updateBulkActions();
}

// Toggle select all
function toggleSelectAll(checked) {
    const checkboxes = document.querySelectorAll('.contact-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checked;
        const contactId = parseInt(cb.value);
        if (checked && !selectedContacts.includes(contactId)) {
            selectedContacts.push(contactId);
        } else if (!checked) {
            const index = selectedContacts.indexOf(contactId);
            if (index > -1) {
                selectedContacts.splice(index, 1);
            }
        }
    });
    
    updateBulkActions();
}

// Mettre à jour les actions groupées
function updateBulkActions() {
    const bulkBtn = document.getElementById('bulkDeleteBtn');
    if (selectedContacts.length > 0) {
        bulkBtn.classList.remove('d-none');
        bulkBtn.innerHTML = `<i class="fas fa-trash me-1"></i>Supprimer (${selectedContacts.length})`;
    } else {
        bulkBtn.classList.add('d-none');
    }
}

// Supprimer contacts sélectionnés
function deleteSelectedContacts() {
    if (selectedContacts.length === 0) return;
    
    if (!confirm(`Êtes-vous sûr de vouloir supprimer ${selectedContacts.length} contact(s) ?`)) {
        return;
    }
    
    console.log('🗑️ Suppression groupée:', selectedContacts);
    
    // CORRECTION : Utiliser AJAX WordPress pour la suppression groupée
    const ajaxData = {
        action: 'nfc_delete_leads_bulk',
        nonce: contactsConfig.nonce,
        lead_ids: selectedContacts
    };
    
    fetch(contactsConfig.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(ajaxData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Suppression groupée réussie:', data);
        
        if (data.success) {
            selectedContacts = [];
            loadContacts();
            showNotification(data.data.message || 'Contacts supprimés avec succès!', 'success');
        } else {
            throw new Error(data.data || 'Erreur lors de la suppression groupée');
        }
    })
    .catch(error => {
        console.error('❌ Erreur suppression groupée:', error);
        showNotification('Erreur lors de la suppression groupée: ' + error.message, 'error');
    });
}

// === IMPORT / EXPORT ===

// Import CSV
function importContacts() {
    console.log('📥 Import contacts');
    
    const modal = new bootstrap.Modal(document.getElementById('importModal'));
    modal.show();
}

// Gérer sélection fichier
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const csv = e.target.result;
        parseCSV(csv);
    };
    reader.readAsText(file);
}

// Parser CSV
function parseCSV(csvText) {
    console.log('📝 Parse CSV');
    
    const lines = csvText.split('\n');
    if (lines.length < 2) {
        showNotification('Fichier CSV invalide', 'error');
        return;
    }
    
    // Headers
    const headers = lines[0].split(',').map(h => h.trim().toLowerCase());
    
    // Mapping des colonnes
    const columnMap = {
        firstname: ['firstname', 'prénom', 'prenom', 'first_name'],
        lastname: ['lastname', 'nom', 'last_name'],
        email: ['email', 'mail', 'e-mail'],
        mobile: ['mobile', 'phone', 'telephone', 'tel', 'téléphone'],
        society: ['society', 'company', 'entreprise', 'société'],
        post: ['post', 'poste', 'position', 'job', 'titre']
    };
    
    // Trouver les index des colonnes
    const mapping = {};
    Object.keys(columnMap).forEach(field => {
        for (let i = 0; i < headers.length; i++) {
            if (columnMap[field].includes(headers[i])) {
                mapping[field] = i;
                break;
            }
        }
    });
    
    console.log('🗂️ Mapping colonnes:', mapping);
    
    // Parser les données
    csvData = [];
    for (let i = 1; i < lines.length; i++) {
        const line = lines[i].trim();
        if (!line) continue;
        
        const values = line.split(',').map(v => v.trim().replace(/^"(.*)"$/, '$1'));
        
        const contact = {
            firstname: mapping.firstname !== undefined ? values[mapping.firstname] : '',
            lastname: mapping.lastname !== undefined ? values[mapping.lastname] : '',
            email: mapping.email !== undefined ? values[mapping.email] : '',
            mobile: mapping.mobile !== undefined ? values[mapping.mobile] : '',
            society: mapping.society !== undefined ? values[mapping.society] : '',
            post: mapping.post !== undefined ? values[mapping.post] : '',
            source: 'manual',
            linked_virtual_card: [contactsConfig.vcard_id],
            contact_datetime: new Date().toISOString()
        };
        
        // Validation basique
        if (contact.email || (contact.firstname && contact.lastname)) {
            csvData.push(contact);
        }
    }
    
    console.log('📊 Contacts parsés:', csvData.length);
    
    // Afficher l'aperçu
    showCSVPreview();
}

// Afficher aperçu CSV
function showCSVPreview() {
    const preview = document.getElementById('csvPreview');
    const tbody = document.getElementById('csvPreviewBody');
    const stats = document.getElementById('csvStats');
    
    if (csvData.length === 0) {
        preview.classList.add('d-none');
        document.getElementById('importBtn').disabled = true;
        return;
    }
    
    // Afficher les 5 premiers
    const previewData = csvData.slice(0, 5);
    tbody.innerHTML = previewData.map(contact => `
        <tr>
            <td>${contact.firstname}</td>
            <td>${contact.lastname}</td>
            <td>${contact.email}</td>
            <td>${contact.mobile}</td>
        </tr>
    `).join('');
    
    stats.textContent = `${csvData.length} contact(s) trouvé(s) dans le fichier`;
    
    preview.classList.remove('d-none');
    document.getElementById('importBtn').disabled = false;
}

// CORRECTION : Import CSV avec bon format
function importCSV() {
    console.log('📥 Import CSV - Démarrage');
    
    if (csvData.length === 0) return;
    
    const importBtn = document.getElementById('importBtn');
    const originalText = importBtn.innerHTML;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Import en cours...';
    importBtn.disabled = true;
    
    // Importer par lots de 5 pour éviter la surcharge
    const batchSize = 5;
    let processed = 0;
    let errors = 0;
    
    const processBatch = async (startIndex) => {
        const batch = csvData.slice(startIndex, startIndex + batchSize);
        
        const promises = batch.map(contactData => {
            // CORRECTION : Format pour l'API
            const apiData = {
                firstname: contactData.firstname,
                lastname: contactData.lastname,
                email: contactData.email,
                mobile: contactData.mobile || '',
                society: contactData.society || '',
                linked_virtual_card: contactsConfig.vcard_id  // INTEGER
            };
            
            return fetch(`${contactsConfig.api_url}lead`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(apiData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    processed++;
                    console.log(`✅ Contact importé: ${processed}/${csvData.length}`);
                } else {
                    errors++;
                    console.error('❌ Erreur import contact:', data.message);
                }
            })
            .catch(error => {
                errors++;
                console.error('❌ Erreur import contact:', error);
            });
        });
        
        await Promise.all(promises);
        
        // Continuer avec le lot suivant
        if (startIndex + batchSize < csvData.length) {
            await processBatch(startIndex + batchSize);
        }
    };
    
    processBatch(0)
        .then(() => {
            console.log('✅ Import terminé:', { processed, errors });
            
            // Fermer la modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
            modal.hide();
            
            // Recharger les contacts
            loadContacts();
            
            // Message de résultat
            if (errors === 0) {
                showNotification(`${processed} contact(s) importé(s) avec succès!`, 'success');
            } else {
                showNotification(`${processed} contact(s) importé(s), ${errors} erreur(s)`, 'warning');
            }
            
            // Reset
            csvData = [];
            document.getElementById('csvFile').value = '';
            document.getElementById('csvPreview').classList.add('d-none');
        })
        .finally(() => {
            importBtn.innerHTML = originalText;
            importBtn.disabled = false;
        });
}

// Export CSV
function exportContacts() {
    console.log('📤 Export CSV');
    
    if (contacts.length === 0) {
        showNotification('Aucun contact à exporter', 'warning');
        return;
    }
    
    // Créer le CSV
    const headers = ['firstname', 'lastname', 'email', 'mobile', 'society', 'post', 'source', 'contact_date'];
    const csvContent = [
        headers.join(','),
        ...contacts.map(contact => [
            contact.firstname || '',
            contact.lastname || '',
            contact.email || '',
            contact.mobile || '',
            contact.society || '',
            contact.post || '',
            contact.source || 'web',
            formatDate(contact.contact_datetime || contact.created_at)
        ].map(field => `"${field}"`).join(','))
    ].join('\n');
    
    // Télécharger
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `contacts-nfc-${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification('Export réalisé avec succès!', 'success');
    }
}

// === UTILITAIRES ===

// Obtenir initiales
function getInitials(firstname, lastname) {
    const first = (firstname || '').charAt(0).toUpperCase();
    const last = (lastname || '').charAt(0).toUpperCase();
    return (first + last) || 'N/A';
}

// Libellé source
function getSourceLabel(source) {
    const labels = {
        qr: 'QR Code',
        nfc: 'NFC',
        web: 'Web',
        manual: 'Manuel'
    };
    return labels[source] || source;
}

// Formater date
function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (e) {
        return 'Date invalide';
    }
}

// Notification
function showNotification(message, type = 'info') {
    console.log(`🔔 Notification ${type}:`, message);
    
    // Créer la notification
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Supprimer après 5 secondes
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}
</script>