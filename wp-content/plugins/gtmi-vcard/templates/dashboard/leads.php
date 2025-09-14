<?php
/**
 * Template: Gestion des leads/contacts - Version simple qui FONCTIONNE
 * 
 * Fichier: templates/dashboard/leads.php
 * Basé directement sur le système de contacts.php qui marche
 */

if (!defined('ABSPATH')) {
    exit;
}

// ================================================================================
// FONCTIONS HELPER SIMPLES
// ================================================================================

if (!function_exists('nfc_get_user_vcard_profiles')) {
    function nfc_get_user_vcard_profiles($user_id) {
        if (class_exists('NFC_Enterprise_Core')) {
            return NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
        }
        return [];
    }
}

// ================================================================================
// LOGIQUE SIMPLE
// ================================================================================

$user_id = get_current_user_id();
$user_vcards = nfc_get_user_vcard_profiles($user_id);

if (empty($user_vcards)) {
    echo '<div class="alert alert-info">Aucune vCard trouvée. <a href="/boutique-nfc/">Commandez votre première carte</a></div>';
    return;
}

$is_multi_profile = count($user_vcards) > 1;

// SIMPLE : Toujours récupérer les contacts via l'API existante
if ($is_multi_profile) {
    $page_title = "Tous mes contacts (" . count($user_vcards) . " profils)";
    // Utiliser le endpoint USER qui fonctionne
    $api_endpoint = home_url("/wp-json/gtmi_vcard/v1/leads/user/{$user_id}");
    $primary_vcard_id = $user_vcards[0]['vcard_id']; // Pour la config JS
} else {
    $page_title = "Mes contacts";
    $primary_vcard_id = $user_vcards[0]['vcard_id'];
    $api_endpoint = home_url("/wp-json/gtmi_vcard/v1/leads/{$primary_vcard_id}");
}

// Variables globales pour compatibilité
global $nfc_vcard, $nfc_current_page;
$nfc_vcard = (object)['ID' => $primary_vcard_id];
$nfc_current_page = 'contacts';

// Configuration JavaScript SIMPLE comme contacts.php
$contacts_config = [
    'vcard_id' => $primary_vcard_id,
    'api_url' => home_url('/wp-json/gtmi_vcard/v1/'),
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'api_endpoint' => $api_endpoint, // 🎯 ENDPOINT DIRECT
    'is_multi_profile' => $is_multi_profile,
    'user_id' => $user_id
];

$current_user = wp_get_current_user();
$full_name = trim($current_user->first_name . ' ' . $current_user->last_name);

error_log("🔧 LEADS SIMPLE - Endpoint: " . $api_endpoint);
error_log("🔧 LEADS SIMPLE - Multi-profile: " . ($is_multi_profile ? 'YES' : 'NO'));
?>

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
                <button class="btn btn-outline-primary" onclick="showAddModal()">
                    <i class="fas fa-plus me-1"></i>
                    Ajouter contact
                </button>
                <button class="btn btn-primary" onclick="exportContacts()">
                    <i class="fas fa-download me-1"></i>
                    Exporter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- STATS CARDS - SIMPLE -->
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

<!-- CONTROLS SECTION -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <!-- Search -->
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="searchContacts" 
                                   placeholder="Rechercher un contact..." onkeyup="applyFilters()">
                        </div>
                    </div>
                    
                    <!-- Filtre source -->
                    <div class="col-md-2">
                        <select class="form-select" id="sourceFilter" onchange="applyFilters()">
                            <option value="">Toutes sources</option>
                            <option value="web">Site web</option>
                            <option value="qr">QR Code</option>
                            <option value="manual">Manuel</option>
                        </select>
                    </div>
                    
                    <!-- Actions -->
                    <div class="col-md-6 text-end">
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

<!-- LOADING STATE -->
<div id="contactsLoading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
    </div>
    <p class="text-muted mt-2">Chargement des contacts...</p>
</div>

<!-- EMPTY STATE -->
<div id="contactsEmpty" class="text-center py-5 d-none">
    <i class="fas fa-users fa-4x text-muted mb-3"></i>
    <h5 class="text-muted">Aucun contact trouvé</h5>
    <p class="text-muted">Vos contacts apparaîtront ici après les premières interactions</p>
</div>

<!-- CONTENU PRINCIPAL -->
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
                                    <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll(this.checked)">
                                </th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Entreprise</th>
                                <th>Source</th>
                                <?php if ($is_multi_profile): ?>
                                <th>Profil Source</th>
                                <?php endif; ?>
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
        
        <!-- Grid View -->
        <div id="contactsGridView" class="d-none">
            <div id="contactsGrid" class="row">
                <!-- Rempli par JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- MODALS - Simplifié pour le test -->
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Fonctionnalité à implémenter</p>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// ================================================================================
// SYSTÈME SIMPLE BASÉ SUR CONTACTS.PHP QUI FONCTIONNE
// ================================================================================

// Configuration (comme contacts.php)
const contactsConfig = <?php echo wp_json_encode($contacts_config); ?>;

let contacts = [];
let filteredContacts = [];
let currentPage = 1;
let selectedContacts = [];
const itemsPerPage = 10;

console.log('🔧 Configuration leads simple:', contactsConfig);

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 DOMContentLoaded - Init leads simple');
    loadContacts();
    initEventListeners();
});

// Charger les contacts - SIMPLE
function loadContacts() {
    console.log('📡 loadContacts() appelée');
    
    // 🔧 FIX TEMPORAIRE : Utiliser toujours l'endpoint vCard simple pour éviter le 403
    // URL selon le mode
    let url;
    if (contactsConfig.is_multi_profile) {
        // TEMPORAIRE : utiliser la première vCard pour éviter l'erreur 403
        url = `${contactsConfig.api_url}leads/${contactsConfig.vcard_id}`;
        console.log('⚠️ Mode temporaire: utilisation vCard simple pour éviter 403');
    } else {
        url = `${contactsConfig.api_url}leads/${contactsConfig.vcard_id}`;
    }
    
    console.log('🌐 URL API (temporaire):', url);
    
    fetch(url)
        .then(response => {
            console.log('📡 Response:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('📦 Données reçues:', data);
            
            // Traiter les données selon le format de l'API
            if (data.data) {
                contacts = Array.isArray(data.data) ? data.data : [];
            } else {
                contacts = Array.isArray(data) ? data : [];
            }
            
            console.log('✅ Contacts traités:', contacts.length);
            
            filteredContacts = [...contacts];
            updateStats();
            applyFilters();
        })
        .catch(error => {
            console.error('❌ Erreur chargement:', error);
            document.getElementById('contactsLoading').classList.add('d-none');
            alert('Erreur lors du chargement des contacts: ' + error.message);
        });
}

// Mettre à jour les stats
function updateStats() {
    document.getElementById('totalContacts').textContent = contacts.length;
    
    // Calculer stats simples
    const thisWeek = contacts.filter(contact => {
        const contactDate = new Date(contact.contact_datetime || contact.created_at);
        const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
        return contactDate >= weekAgo;
    }).length;
    
    document.getElementById('newContacts').textContent = thisWeek;
    
    const companies = new Set(contacts.map(c => c.society).filter(Boolean));
    document.getElementById('totalCompanies').textContent = companies.size;
    
    const qrContacts = contacts.filter(c => c.source === 'qr').length;
    document.getElementById('qrContacts').textContent = qrContacts;
}

// Appliquer les filtres - SIMPLE
function applyFilters() {
    console.log('🔍 Filtres appliqués');
    
    filteredContacts = [...contacts];
    
    // Filtre recherche
    const searchValue = document.getElementById('searchContacts').value.toLowerCase();
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
    const sourceValue = document.getElementById('sourceFilter').value;
    if (sourceValue) {
        filteredContacts = filteredContacts.filter(contact => {
            return (contact.source || 'web') === sourceValue;
        });
    }
    
    console.log('🔍 Contacts filtrés:', filteredContacts.length);
    currentPage = 1;
    renderContacts();
}

// Afficher les contacts - SIMPLE
function renderContacts() {
    console.log('🎨 renderContacts() appelée');
    
    document.getElementById('contactsLoading').classList.add('d-none');
    
    if (filteredContacts.length === 0) {
        document.getElementById('contactsTableView').classList.add('d-none');
        document.getElementById('contactsGridView').classList.add('d-none');
        document.getElementById('contactsEmpty').classList.remove('d-none');
        document.getElementById('contactsPaginationWrapper').style.display = 'none';
        return;
    }
    
    document.getElementById('contactsEmpty').classList.add('d-none');
    
    const viewMode = document.querySelector('input[name="viewMode"]:checked').id;
    
    if (viewMode === 'viewList') {
        renderTableView();
    } else {
        renderGridView();
    }
    
    renderPagination();
}

// Vue tableau - SIMPLE
function renderTableView() {
    console.log('📊 renderTableView()');
    
    document.getElementById('contactsTableView').classList.remove('d-none');
    document.getElementById('contactsGridView').classList.add('d-none');
    
    const tbody = document.getElementById('contactsTableBody');
    const start = (currentPage - 1) * itemsPerPage;
    const pageContacts = filteredContacts.slice(start, start + itemsPerPage);
    
    tbody.innerHTML = pageContacts.map(contact => {
        const fullName = `${contact.firstname || ''} ${contact.lastname || ''}`.trim();
        const formattedDate = formatDate(contact.contact_datetime || contact.created_at);
        
        return `
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
                            <div class="fw-medium">${fullName}</div>
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
                ${contactsConfig.is_multi_profile ? `<td>${getProfileName(contact)}</td>` : ''}
                <td>${formattedDate}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewContact(${contact.id})" title="Voir">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteContact(${contact.id})" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Vue grille - SIMPLE
function renderGridView() {
    document.getElementById('contactsTableView').classList.add('d-none');
    document.getElementById('contactsGridView').classList.remove('d-none');
    
    const container = document.getElementById('contactsGrid');
    const start = (currentPage - 1) * itemsPerPage;
    const pageContacts = filteredContacts.slice(start, start + itemsPerPage);
    
    container.innerHTML = pageContacts.map(contact => {
        const fullName = `${contact.firstname || ''} ${contact.lastname || ''}`.trim();
        return `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">${fullName}</h6>
                        <p class="card-text">${contact.email || 'N/A'}</p>
                        <p class="card-text"><small class="text-muted">${contact.society || 'N/A'}</small></p>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Pagination - SIMPLE
function renderPagination() {
    const totalPages = Math.ceil(filteredContacts.length / itemsPerPage);
    
    if (totalPages <= 1) {
        document.getElementById('contactsPaginationWrapper').style.display = 'none';
        return;
    }
    
    document.getElementById('contactsPaginationWrapper').style.display = 'block';
    
    // Update counters
    const start = Math.min((currentPage - 1) * itemsPerPage + 1, filteredContacts.length);
    const end = Math.min(currentPage * itemsPerPage, filteredContacts.length);
    
    document.getElementById('contactsStart').textContent = start;
    document.getElementById('contactsEnd').textContent = end;
    document.getElementById('contactsTotal').textContent = filteredContacts.length;
    
    // Generate pagination
    const pagination = document.getElementById('contactsPagination');
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

// FONCTIONS UTILITAIRES
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

function getProfileName(contact) {
    // Pour l'instant, retourner le nom du profil depuis les données
    return contact.vcard_source_name || 'Profil inconnu';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

// Event listeners
function initEventListeners() {
    document.querySelectorAll('input[name="viewMode"]').forEach(radio => {
        radio.addEventListener('change', renderContacts);
    });
}

// Fonctions d'action (stub pour l'instant)
function showAddModal() {
    console.log('Ajouter contact');
    document.getElementById('addContactModal')?.classList.add('show');
}

function exportContacts() {
    console.log('Exporter contacts');
    alert('Export en cours de développement');
}

function viewContact(id) {
    console.log('Voir contact:', id);
    alert('Détails du contact ' + id);
}

function deleteContact(id) {
    if (confirm('Supprimer ce contact ?')) {
        console.log('Supprimer contact:', id);
        alert('Suppression en cours de développement');
    }
}

function toggleContactSelection(id) {
    console.log('Toggle selection:', id);
}

function toggleSelectAll(checked) {
    console.log('Toggle select all:', checked);
}

console.log('✅ Script leads simple chargé et prêt');
</script>