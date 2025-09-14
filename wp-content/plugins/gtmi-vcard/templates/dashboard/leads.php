<?php
/**
 * Template: Gestion des contacts - Dashboard NFC
 * 
 * Fichier: templates/dashboard/leads.php
 * COPIE EXACTE de contacts.php qui fonctionne + adaptations multi-profils
 */

if (!defined('ABSPATH')) {
    exit;
}

// ================================================================================
// LOGIQUE MULTI-PROFILS SIMPLE
// ================================================================================

$user_id = get_current_user_id();

// Déterminer le mode et la vCard à utiliser
if (class_exists('NFC_Enterprise_Core')) {
    $user_vcards = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
    $is_multi_profile = count($user_vcards) > 1;
    
    if ($is_multi_profile) {
        // Mode multi : utiliser la première vCard pour l'API (temporaire)
        $vcard_id = $user_vcards[0]['vcard_id'];
        $page_title = "Tous mes contacts (" . count($user_vcards) . " profils)";
        error_log("🔧 Mode multi-profils détecté: " . count($user_vcards) . " vCards");
    } else if (!empty($user_vcards)) {
        // Mode simple
        $vcard_id = $user_vcards[0]['vcard_id'];
        $page_title = "Mes contacts";
        error_log("🔧 Mode simple: 1 vCard");
    } else {
        echo '<div class="alert alert-info">Aucune vCard trouvée. <a href="/boutique-nfc/">Commandez votre première carte</a></div>';
        return;
    }
} else {
    // Fallback ancien système
    global $nfc_vcard, $nfc_current_page;
    $vcard = $nfc_vcard;
    $vcard_id = $vcard->ID;
    $is_multi_profile = false;
    $page_title = "Mes contacts";
}

// ================================================================================
// SYSTÈME IDENTIQUE À CONTACTS.PHP
// ================================================================================

// Variables globales disponibles depuis le routing
global $nfc_vcard, $nfc_current_page;
$vcard = $nfc_vcard ?? (object)['ID' => $vcard_id];
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

// Configuration JavaScript IDENTIQUE à contacts.php
$contacts_config = [
    'vcard_id' => $vcard_id,
    'api_url' => home_url('/wp-json/gtmi_vcard/v1/'),
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'public_url' => $public_url,
    'user_name' => $full_name,
    'is_multi_profile' => $is_multi_profile ?? false, // 🆕 SEULE ADDITION
    'i18n' => [
        'loading' => __('Chargement...', 'gtmi_vcard'),
        'error' => __('Une erreur est survenue', 'gtmi_vcard'),
        'success' => __('Action réalisée avec succès', 'gtmi_vcard'),
        'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer ce contact ?', 'gtmi_vcard'),
        'confirm_delete_multiple' => __('Êtes-vous sûr de vouloir supprimer ces contacts ?', 'gtmi_vcard'),
        'no_contacts' => __('Aucun contact trouvé', 'gtmi_vcard'),
        'export_success' => __('Export réalisé avec succès', 'gtmi_vcard'),
        'import_success' => __('Import réalisé avec succès', 'gtmi_vcard')
    ]
];

error_log("🔧 LEADS CONFIG - vcard_id: " . $vcard_id);
error_log("🔧 LEADS CONFIG - is_multi_profile: " . ($is_multi_profile ? 'YES' : 'NO'));
?>

<!-- PAGE HEADER - IDENTIQUE à contacts.php -->
<div class="contacts-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="h3 mb-1">
                <i class="fas fa-users me-2 text-primary"></i>
                <?php echo esc_html($page_title ?? 'Mes contacts'); ?>
            </h2>
            <p class="text-muted mb-0">Gestion de vos contacts professionnels</p>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                    <i class="fas fa-plus me-1"></i>
                    Ajouter contact
                </button>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-1"></i>
                        Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="exportContacts('csv')">
                            <i class="fas fa-file-csv me-2"></i>CSV
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportContacts('pdf')">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </a></li>
                    </ul>
                </div>
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-upload me-1"></i>
                    Import
                </button>
            </div>
        </div>
    </div>
</div>

<!-- STATS CARDS - IDENTIQUE à contacts.php -->
<div class="row mb-4">
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

<!-- CONTROLS SECTION - IDENTIQUE à contacts.php -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="contactsSearch" 
                                   placeholder="Rechercher un contact...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="sourceFilter">
                            <option value="">Toutes sources</option>
                            <option value="web">Site web</option>
                            <option value="qr">QR Code</option>
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
                    <div class="col-md-4 text-end">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="viewList" checked>
                            <label class="btn btn-outline-secondary" for="viewList">
                                <i class="fas fa-table"></i>
                            </label>
                            <input type="radio" class="btn-check" name="viewMode" id="viewGrid">
                            <label class="btn btn-outline-secondary" for="viewGrid">
                                <i class="fas fa-th"></i>
                            </label>
                        </div>
                        
                        <button class="btn btn-outline-danger ms-2" id="deleteSelectedBtn" style="display: none;">
                            <i class="fas fa-trash me-1"></i>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LOADING STATE - IDENTIQUE -->
<div id="contactsLoading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
    </div>
    <p class="text-muted mt-2">Chargement des contacts...</p>
</div>

<!-- EMPTY STATE - IDENTIQUE -->
<div id="contactsEmpty" class="text-center py-5 d-none">
    <i class="fas fa-users fa-4x text-muted mb-3"></i>
    <h5 class="text-muted">Aucun contact trouvé</h5>
    <p class="text-muted">Vos contacts apparaîtront ici après les premières interactions</p>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
        <i class="fas fa-plus me-2"></i>
        Ajouter votre premier contact
    </button>
</div>

<!-- CONTENU PRINCIPAL - IDENTIQUE -->
<div class="row">
    <div class="col-12">
        <!-- Table View -->
        <div id="contactsTableView" class="card border-0 shadow-sm d-none">
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
                                <th>Date</th>
                                <th class="text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="contactsTableBody">
                            <!-- Rempli par JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white" id="contactsPaginationWrapper" style="display: none;">
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
        
        <!-- Grid View - IDENTIQUE -->
        <div id="contactsGridView" class="d-none">
            <div id="contactsGrid" class="row">
                <!-- Rempli par JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- MODALS - Modals complètes de contacts.php -->

<!-- Modal Ajout Contact -->
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addContactForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prénom *</label>
                            <input type="text" class="form-control" name="firstname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="lastname" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" name="mobile">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Entreprise</label>
                        <input type="text" class="form-control" name="society">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Poste</label>
                        <input type="text" class="form-control" name="post">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
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
            <form id="importForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Fichier CSV</label>
                        <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                        <div class="form-text">
                            Format requis: prénom, nom, email, téléphone, entreprise, poste
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </div>
            </form>
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
                <!-- Rempli par JavaScript -->
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

<!-- JAVASCRIPT EXACT DE CONTACTS.PHP -->
<script type="text/javascript">
// Configuration et état - IDENTIQUE à contacts.php
const contactsConfig = <?php echo wp_json_encode($contacts_config); ?>;

let contacts = [];
let filteredContacts = [];
let currentPage = 1;
let selectedContacts = [];
let csvData = [];
let currentContactId = null;
const itemsPerPage = 10;

console.log('🔄 Configuration contacts (leads):', contactsConfig);

// Initialisation - IDENTIQUE
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 DOMContentLoaded - Init contacts avec config:', contactsConfig);
    loadContacts();
    initEventListeners();
});

// Charger les contacts - IDENTIQUE à contacts.php
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
            console.log('📦 Type de data:', typeof data);
            console.log('📦 data.data:', data.data);
            console.log('📦 Array.isArray(data):', Array.isArray(data));
            console.log('📦 Array.isArray(data.data):', Array.isArray(data.data));
            
            // Traitement des données avec debug détaillé
            if (Array.isArray(data)) {
                contacts = data;
                console.log('📦 Cas 1: data est un array direct');
            } else if (data.data && Array.isArray(data.data)) {
                contacts = data.data;
                console.log('📦 Cas 2: data.data est un array');
            } else if (data.success && data.data && Array.isArray(data.data)) {
                contacts = data.data;
                console.log('📦 Cas 3: structure API avec success');
            } else {
                contacts = [];
                console.log('📦 Cas 4: aucun format reconnu, array vide');
                console.log('📦 Structure complète de data:', JSON.stringify(data, null, 2));
            }
            
            console.log('📦 Contacts finalement traités:', contacts.length, contacts);
            
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

// Initialiser les event listeners - IDENTIQUE
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

// Appliquer les filtres - IDENTIQUE
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

// Afficher les contacts - IDENTIQUE
function renderContacts() {
    console.log('🎨 renderContacts() appelée');
    
    if (filteredContacts.length === 0) {
        document.getElementById('contactsTableView').classList.add('d-none');
        document.getElementById('contactsGridView').classList.add('d-none');
        document.getElementById('contactsEmpty').classList.remove('d-none');
        document.getElementById('contactsPaginationWrapper').style.display = 'none';
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

// Vue tableau - IDENTIQUE
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
            <td>
                <span class="badge bg-secondary">${getSourceLabel(contact.source || 'web')}</span>
            </td>
            <td>${formatDate(contact.contact_datetime || contact.created_at)}</td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" onclick="viewContactDetails(${contact.id})" title="Voir">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteContact(${contact.id})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    console.log('📊 Table rendue avec', pageContacts.length, 'contacts');
}

// Vue grille - IDENTIQUE
function renderGridView() {
    console.log('🎛️ renderGridView()');
    
    document.getElementById('contactsTableView').classList.add('d-none');
    document.getElementById('contactsGridView').classList.remove('d-none');
    
    const container = document.getElementById('contactsGrid');
    const start = (currentPage - 1) * itemsPerPage;
    const pageContacts = filteredContacts.slice(start, start + itemsPerPage);
    
    container.innerHTML = pageContacts.map(contact => `
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <input type="checkbox" class="form-check-input me-2 contact-checkbox" 
                               value="${contact.id}" onchange="toggleContactSelection(${contact.id})">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 40px; height: 40px;">
                            ${getInitials(contact.firstname, contact.lastname)}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="card-title mb-0">${contact.firstname || ''} ${contact.lastname || ''}</h6>
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
                        <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="viewContactDetails(${contact.id})">
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

// Pagination - IDENTIQUE
function renderPagination() {
    const totalPages = Math.ceil(filteredContacts.length / itemsPerPage);
    const pagination = document.getElementById('contactsPagination');
    const wrapper = document.getElementById('contactsPaginationWrapper');
    
    if (totalPages <= 1) {
        wrapper.style.display = 'none';
        return;
    }
    
    wrapper.style.display = 'block';
    
    // Update counters
    const start = Math.min((currentPage - 1) * itemsPerPage + 1, filteredContacts.length);
    const end = Math.min(currentPage * itemsPerPage, filteredContacts.length);
    
    document.getElementById('contactsStart').textContent = start;
    document.getElementById('contactsEnd').textContent = end;
    document.getElementById('contactsTotal').textContent = filteredContacts.length;
    
    let html = '';
    
    // Previous
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Précédent</a>
        </li>
    `;
    
    // Pages
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage || Math.abs(i - currentPage) <= 2 || i === 1 || i === totalPages) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (Math.abs(i - currentPage) === 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Suivant</a>
        </li>
    `;
    
    pagination.innerHTML = html;
}

// Mettre à jour les stats - IDENTIQUE
function updateStats() {
    document.getElementById('totalContacts').textContent = contacts.length;
    
    // Cette semaine
    const thisWeek = contacts.filter(contact => {
        const contactDate = new Date(contact.contact_datetime || contact.created_at);
        const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
        return contactDate >= weekAgo;
    }).length;
    
    document.getElementById('newContacts').textContent = thisWeek;
    
    // Entreprises uniques
    const companies = new Set();
    contacts.forEach(contact => {
        if (contact.society) {
            companies.add(contact.society);
        }
    });
    
    document.getElementById('totalCompanies').textContent = companies.size;
    
    // QR Code
    const qrContacts = contacts.filter(contact => contact.source === 'qr').length;
    document.getElementById('qrContacts').textContent = qrContacts;
}

// FONCTIONS UTILITAIRES - IDENTIQUES
function changePage(page) {
    if (page >= 1 && page <= Math.ceil(filteredContacts.length / itemsPerPage)) {
        currentPage = page;
        renderContacts();
    }
}

function getInitials(firstname, lastname) {
    const f = (firstname || '').charAt(0).toUpperCase();
    const l = (lastname || '').charAt(0).toUpperCase();
    return f + l || '?';
}

function getSourceLabel(source) {
    switch (source) {
        case 'qr': return 'QR Code';
        case 'manual': return 'Manuel';
        default: return 'Site web';
    }
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

function toggleContactSelection(id) {
    console.log('Toggle selection:', id);
    // TODO: Implémenter
}

function toggleSelectAll(checked) {
    console.log('Toggle select all:', checked);
    // TODO: Implémenter
}

function viewContactDetails(id) {
    console.log('Voir détails contact:', id);
    // TODO: Implémenter modal détails
}

function editContact(id) {
    console.log('Éditer contact:', id);
    // TODO: Implémenter édition
}

function deleteContact(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce contact ?')) {
        console.log('Supprimer contact:', id);
        // TODO: Implémenter suppression
    }
}

function exportContacts(type) {
    console.log('Export contacts type:', type);
    // TODO: Implémenter export
}

function editContactFromDetails() {
    console.log('Éditer depuis détails');
    // TODO: Implémenter
}

function deleteContactFromDetails() {
    console.log('Supprimer depuis détails');
    // TODO: Implémenter
}

console.log('✅ Script contacts (leads) chargé et prêt');
</script>