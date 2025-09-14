<?php
/**
 * Template Dashboard NFC - Page Leads Multi-Profils
 * Fichier: templates/dashboard/enterprise/leads.php
 * 
 * Fonctionnalités incluses:
 * - Export CSV
 * - Import CSV
 * - Ajout de contact manuel
 * - Stats globales
 * - Filtrage par profil vCard
 * - Filtres sur colonnes
 */

if (!defined('ABSPATH')) {
    exit;
}

// Récupérer l'utilisateur et ses vCards
$user_id = get_current_user_id();
$user_vcards = nfc_get_user_vcard_profiles($user_id);

// Filtre par vCard sélectionnée
$selected_vcard = isset($_GET['vcard_filter']) ? intval($_GET['vcard_filter']) : 0;

// Récupérer les stats globales
$global_stats = nfc_get_user_global_stats($user_id);

// Récupérer les contacts avec filtrage
$contacts = nfc_get_enterprise_contacts($user_id, $selected_vcard, 100);

// Préparer les options de filtre
$vcard_options = [0 => 'Tous les profils (' . $global_stats['total_leads'] . ')'];
foreach ($user_vcards as $vcard) {
    $leads_count = nfc_get_vcard_leads_count($vcard['vcard_id']);
    $name = nfc_format_vcard_full_name($vcard['vcard_data'] ?? []);
    $vcard_options[$vcard['vcard_id']] = $name . ' (' . $leads_count . ')';
}
?>

<div class="nfc-leads-page">
    
    <!-- Header avec Stats Globales -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2><i class="fas fa-users me-2"></i>Mes Contacts</h2>
                <p class="text-muted mb-0">Gestion centralisée de tous vos contacts professionnels</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-cards d-flex gap-3">
                <div class="stat-card bg-primary">
                    <div class="stat-number"><?= $global_stats['total_leads'] ?></div>
                    <div class="stat-label">Total Contacts</div>
                </div>
                <div class="stat-card bg-info">
                    <div class="stat-number"><?= count($user_vcards) ?></div>
                    <div class="stat-label">Profils Actifs</div>
                </div>
                <div class="stat-card bg-success">
                    <div class="stat-number"><?= nfc_get_contacts_trend($selected_vcard ?: null, 7) ?></div>
                    <div class="stat-label">Cette Semaine</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre d'Actions -->
    <div class="actions-bar mb-4">
        <div class="row align-items-center">
            
            <!-- Filtre par Profil -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-filter"></i></span>
                    <select class="form-select" id="vcardFilter" onchange="filterByVCard(this.value)">
                        <?php foreach ($vcard_options as $vcard_id => $label): ?>
                            <option value="<?= $vcard_id ?>" <?= $selected_vcard == $vcard_id ? 'selected' : '' ?>>
                                <?= esc_html($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Barre de Recherche -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" 
                           placeholder="Rechercher un contact..." onkeyup="filterContacts()">
                    <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Boutons d'Actions -->
            <div class="col-md-4">
                <div class="btn-group ms-auto" role="group">
                    <button type="button" class="btn btn-success" onclick="showAddContactModal()">
                        <i class="fas fa-plus me-1"></i>Ajouter
                    </button>
                    <button type="button" class="btn btn-info" onclick="showImportModal()">
                        <i class="fas fa-upload me-1"></i>Importer
                    </button>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" 
                                data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportContacts('current')">
                                <i class="fas fa-file-csv me-2"></i>Filtre Actuel
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportContacts('all')">
                                <i class="fas fa-globe me-2"></i>Tous les Contacts
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php foreach ($user_vcards as $vcard): ?>
                                <li><a class="dropdown-item" href="#" onclick="exportContacts(<?= $vcard['vcard_id'] ?>)">
                                    <i class="fas fa-user me-2"></i><?= esc_html(nfc_format_vcard_full_name($vcard['vcard_data'] ?? [])) ?>
                                </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des Contacts -->
    <div class="contacts-table-container">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="contactsTable">
                <thead class="table-dark">
                    <tr>
                        <th width="30">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th onclick="sortBy('lastname')" style="cursor: pointer;">
                            Nom <i class="fas fa-sort ms-1" id="sort-lastname"></i>
                            <input type="text" class="form-control form-control-sm mt-1" 
                                   placeholder="Filtrer..." onkeyup="filterColumn('lastname', this.value)">
                        </th>
                        <th onclick="sortBy('firstname')" style="cursor: pointer;">
                            Prénom <i class="fas fa-sort ms-1" id="sort-firstname"></i>
                            <input type="text" class="form-control form-control-sm mt-1" 
                                   placeholder="Filtrer..." onkeyup="filterColumn('firstname', this.value)">
                        </th>
                        <th onclick="sortBy('email')" style="cursor: pointer;">
                            Email <i class="fas fa-sort ms-1" id="sort-email"></i>
                            <input type="text" class="form-control form-control-sm mt-1" 
                                   placeholder="Filtrer..." onkeyup="filterColumn('email', this.value)">
                        </th>
                        <th onclick="sortBy('mobile')" style="cursor: pointer;">
                            Téléphone <i class="fas fa-sort ms-1" id="sort-mobile"></i>
                            <input type="text" class="form-control form-control-sm mt-1" 
                                   placeholder="Filtrer..." onkeyup="filterColumn('mobile', this.value)">
                        </th>
                        <th onclick="sortBy('society')" style="cursor: pointer;">
                            Entreprise <i class="fas fa-sort ms-1" id="sort-society"></i>
                            <select class="form-select form-select-sm mt-1" onchange="filterColumn('society', this.value)">
                                <option value="">Toutes</option>
                                <?php 
                                $companies = array_unique(array_filter(array_column($contacts, 'society')));
                                foreach ($companies as $company): ?>
                                    <option value="<?= esc_attr($company) ?>"><?= esc_html($company) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </th>
                        <th onclick="sortBy('vcard_source')" style="cursor: pointer;">
                            Profil Source <i class="fas fa-sort ms-1" id="sort-vcard_source"></i>
                            <select class="form-select form-select-sm mt-1" onchange="filterColumn('vcard_source', this.value)">
                                <option value="">Tous</option>
                                <?php foreach ($user_vcards as $vcard): ?>
                                    <option value="<?= $vcard['vcard_id'] ?>">
                                        <?= esc_html(nfc_format_vcard_full_name($vcard['vcard_data'] ?? [])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </th>
                        <th onclick="sortBy('post_date')" style="cursor: pointer;">
                            Date Contact <i class="fas fa-sort ms-1" id="sort-post_date"></i>
                            <input type="date" class="form-control form-control-sm mt-1" 
                                   onchange="filterColumn('post_date', this.value)">
                        </th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody id="contactsTableBody">
                    <?php if (empty($contacts)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p class="mb-0">Aucun contact trouvé</p>
                                <small>Commencez par ajouter des contacts ou ajustez vos filtres</small>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contacts as $contact): ?>
                            <?php 
                            // Déterminer le profil source
                            $source_vcard = null;
                            foreach ($user_vcards as $vcard) {
                                $pattern = 'a:1:{i:0;s:' . strlen($vcard['vcard_id']) . ':"' . $vcard['vcard_id'] . '";}';
                                if ($contact['linked_vcard'] === $pattern) {
                                    $source_vcard = $vcard;
                                    break;
                                }
                            }
                            ?>
                            <tr data-contact-id="<?= $contact['ID'] ?>">
                                <td>
                                    <input type="checkbox" class="contact-checkbox" value="<?= $contact['ID'] ?>">
                                </td>
                                <td><?= esc_html($contact['lastname'] ?: '-') ?></td>
                                <td><?= esc_html($contact['firstname'] ?: '-') ?></td>
                                <td>
                                    <?php if ($contact['email']): ?>
                                        <a href="mailto:<?= esc_attr($contact['email']) ?>" class="text-decoration-none">
                                            <?= esc_html($contact['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($contact['mobile']): ?>
                                        <a href="tel:<?= esc_attr($contact['mobile']) ?>" class="text-decoration-none">
                                            <?= esc_html($contact['mobile']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc_html($contact['society'] ?: '-') ?></td>
                                <td>
                                    <?php if ($source_vcard): ?>
                                        <span class="badge bg-secondary">
                                            <?= esc_html(nfc_format_vcard_full_name($source_vcard['vcard_data'] ?? [])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($contact['post_date'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary" 
                                                onclick="viewContact(<?= $contact['ID'] ?>)" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" 
                                                onclick="editContact(<?= $contact['ID'] ?>)" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deleteContact(<?= $contact['ID'] ?>)" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Actions en Lot -->
        <?php if (!empty($contacts)): ?>
            <div class="bulk-actions mt-3" id="bulkActionsBar" style="display: none;">
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
        <?php endif; ?>
    </div>

</div>

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
                                <label class="form-label">Prénom *</label>
                                <input type="text" class="form-control" name="firstname" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nom *</label>
                                <input type="text" class="form-control" name="lastname" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" name="mobile">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Entreprise</label>
                                <input type="text" class="form-control" name="society">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Poste</label>
                                <input type="text" class="form-control" name="post">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Associer au Profil *</label>
                        <select class="form-select" name="linked_virtual_card" required>
                            <option value="">Choisir un profil...</option>
                            <?php foreach ($user_vcards as $vcard): ?>
                                <option value="<?= $vcard['vcard_id'] ?>">
                                    <?= esc_html(nfc_format_vcard_full_name($vcard['vcard_data'] ?? [])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Notes additionnelles..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import CSV -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload me-2"></i>Importer des Contacts
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Fichier CSV *</label>
                    <input type="file" class="form-control" id="csvFile" accept=".csv" 
                           onchange="handleFileSelect(event)">
                    <div class="form-text">
                        Format attendu : Prénom, Nom, Email, Téléphone, Entreprise, Poste<br>
                        <a href="#" onclick="downloadTemplate()">📄 Télécharger le modèle CSV</a>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Associer tous les contacts au Profil *</label>
                    <select class="form-select" id="importProfileSelect" required>
                        <option value="">Choisir un profil...</option>
                        <?php foreach ($user_vcards as $vcard): ?>
                            <option value="<?= $vcard['vcard_id'] ?>">
                                <?= esc_html(nfc_format_vcard_full_name($vcard['vcard_data'] ?? [])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="csvPreview" class="d-none">
                    <h6>Aperçu (5 premiers contacts) :</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Prénom</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                </tr>
                            </thead>
                            <tbody id="csvPreviewBody"></tbody>
                        </table>
                    </div>
                    <div class="alert alert-info" id="csvStats"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="importBtn" onclick="importCSV()" disabled>
                    <i class="fas fa-upload me-1"></i>Importer
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
                <h5 class="modal-title">
                    <i class="fas fa-user me-2"></i>Détails du Contact
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contactDetailsContent">
                <!-- Content loaded via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="editCurrentContact()">
                    <i class="fas fa-edit me-1"></i>Modifier
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Styles CSS Spécifiques -->
<style>
.stats-cards {
    display: flex;
    gap: 1rem;
}

.stat-card {
    background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 100%);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    text-align: center;
    min-width: 120px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.stat-card.bg-info {
    background: linear-gradient(135deg, var(--bs-info) 0%, var(--bs-info-dark) 100%);
}

.stat-card.bg-success {
    background: linear-gradient(135deg, var(--bs-success) 0%, var(--bs-success-dark) 100%);
}

.stat-number {
    font-size: 1.75rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

.actions-bar {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.contacts-table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table th {
    background-color: #343a40 !important;
    color: white;
    border: none;
    font-weight: 500;
    position: relative;
}

.table th input,
.table th select {
    margin-top: 0.5rem;
    font-size: 0.8rem;
}

.table tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
}

.bulk-actions {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 0;
}

.nfc-leads-page {
    padding: 1.5rem;
}

@media (max-width: 768px) {
    .stats-cards {
        flex-wrap: wrap;
    }
    
    .stat-card {
        min-width: 100px;
        padding: 0.75rem 1rem;
    }
}
</style>

<!-- JavaScript -->
<script>
// Variables globales
let allContacts = <?= json_encode($contacts) ?>;
let filteredContacts = [...allContacts];
let csvData = [];
let sortDirection = {};
let columnFilters = {};

// Configuration
const leadsConfig = {
    user_id: <?= $user_id ?>,
    ajax_url: '<?= admin_url('admin-ajax.php') ?>',
    nonce: '<?= wp_create_nonce('nfc_dashboard_nonce') ?>',
    vcard_options: <?= json_encode($vcard_options) ?>
};

// ================================================================================
// INITIALISATION
// ================================================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initialisation page Leads');
    initializeEventListeners();
    updateDisplay();
});

function initializeEventListeners() {
    // Form submission
    document.getElementById('addContactForm').addEventListener('submit', handleAddContact);
    
    // Selection management
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('contact-checkbox')) {
            updateBulkActions();
        }
    });
}

// ================================================================================
// FILTRAGE ET RECHERCHE
// ================================================================================

function filterByVCard(vcardId) {
    const url = new URL(window.location);
    if (vcardId == 0) {
        url.searchParams.delete('vcard_filter');
    } else {
        url.searchParams.set('vcard_filter', vcardId);
    }
    window.location = url;
}

function filterContacts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    filteredContacts = allContacts.filter(contact => {
        const searchString = [
            contact.firstname || '',
            contact.lastname || '',
            contact.email || '',
            contact.mobile || '',
            contact.society || '',
            contact.post_title || ''
        ].join(' ').toLowerCase();
        
        return searchString.includes(searchTerm);
    });
    
    applyColumnFilters();
    updateTableDisplay();
}

function filterColumn(column, value) {
    columnFilters[column] = value.toLowerCase();
    applyColumnFilters();
    updateTableDisplay();
}

function applyColumnFilters() {
    filteredContacts = allContacts.filter(contact => {
        for (const [column, filterValue] of Object.entries(columnFilters)) {
            if (!filterValue) continue;
            
            let contactValue = '';
            switch (column) {
                case 'vcard_source':
                    // Logique pour filtrer par profil source
                    continue;
                case 'post_date':
                    contactValue = contact.post_date ? contact.post_date.split(' ')[0] : '';
                    break;
                default:
                    contactValue = (contact[column] || '').toLowerCase();
            }
            
            if (!contactValue.includes(filterValue)) {
                return false;
            }
        }
        return true;
    });
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    columnFilters = {};
    
    // Clear column filters
    document.querySelectorAll('th input, th select').forEach(input => {
        input.value = '';
    });
    
    filteredContacts = [...allContacts];
    updateTableDisplay();
}

// ================================================================================
// TRI
// ================================================================================

function sortBy(column) {
    const currentDirection = sortDirection[column] || 'asc';
    const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
    sortDirection[column] = newDirection;
    
    // Update sort icons
    document.querySelectorAll('[id^="sort-"]').forEach(icon => {
        icon.className = 'fas fa-sort ms-1';
    });
    
    const icon = document.getElementById(`sort-${column}`);
    if (icon) {
        icon.className = `fas fa-sort-${newDirection === 'asc' ? 'up' : 'down'} ms-1`;
    }
    
    // Sort data
    filteredContacts.sort((a, b) => {
        let aVal = a[column] || '';
        let bVal = b[column] || '';
        
        if (column === 'post_date') {
            aVal = new Date(aVal);
            bVal = new Date(bVal);
        } else {
            aVal = aVal.toString().toLowerCase();
            bVal = bVal.toString().toLowerCase();
        }
        
        if (newDirection === 'asc') {
            return aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
        } else {
            return aVal > bVal ? -1 : aVal < bVal ? 1 : 0;
        }
    });
    
    updateTableDisplay();
}

// ================================================================================
// AFFICHAGE
// ================================================================================

function updateDisplay() {
    updateTableDisplay();
    updateBulkActions();
}

function updateTableDisplay() {
    const tbody = document.getElementById('contactsTableBody');
    
    if (filteredContacts.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <p class="mb-0">Aucun contact trouvé</p>
                    <small>Essayez d'ajuster vos filtres de recherche</small>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = filteredContacts.map(contact => `
        <tr data-contact-id="${contact.ID}">
            <td>
                <input type="checkbox" class="contact-checkbox" value="${contact.ID}">
            </td>
            <td>${escapeHtml(contact.lastname || '-')}</td>
            <td>${escapeHtml(contact.firstname || '-')}</td>
            <td>
                ${contact.email ? `<a href="mailto:${escapeHtml(contact.email)}">${escapeHtml(contact.email)}</a>` : '-'}
            </td>
            <td>
                ${contact.mobile ? `<a href="tel:${escapeHtml(contact.mobile)}">${escapeHtml(contact.mobile)}</a>` : '-'}
            </td>
            <td>${escapeHtml(contact.society || '-')}</td>
            <td>
                <span class="badge bg-secondary">Profil Source</span>
            </td>
            <td>
                <small class="text-muted">
                    ${new Date(contact.post_date).toLocaleDateString('fr-FR')}
                </small>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewContact(${contact.ID})" title="Voir">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-warning" onclick="editContact(${contact.ID})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteContact(${contact.ID})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ================================================================================
// SÉLECTION ET ACTIONS EN LOT
// ================================================================================

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.contact-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

function updateBulkActions() {
    const selected = document.querySelectorAll('.contact-checkbox:checked');
    const bulkBar = document.getElementById('bulkActionsBar');
    const countEl = document.getElementById('selectedCount');
    
    if (selected.length > 0) {
        bulkBar.style.display = 'block';
        countEl.textContent = selected.length;
    } else {
        bulkBar.style.display = 'none';
    }
}

function clearSelection() {
    document.querySelectorAll('.contact-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

function deleteBulk() {
    const selected = Array.from(document.querySelectorAll('.contact-checkbox:checked'))
                          .map(cb => cb.value);
    
    if (selected.length === 0) return;
    
    if (!confirm(`Supprimer ${selected.length} contact(s) sélectionné(s) ?`)) {
        return;
    }
    
    // AJAX call to delete multiple contacts
    showNotification('Suppression en cours...', 'info');
    
    fetch(leadsConfig.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'nfc_delete_leads_bulk',
            nonce: leadsConfig.nonce,
            contact_ids: JSON.stringify(selected)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`${selected.length} contact(s) supprimé(s)`, 'success');
            // Remove from local data and refresh
            allContacts = allContacts.filter(c => !selected.includes(c.ID.toString()));
            filteredContacts = filteredContacts.filter(c => !selected.includes(c.ID.toString()));
            updateDisplay();
            clearSelection();
        } else {
            throw new Error(data.data || 'Erreur de suppression');
        }
    })
    .catch(error => {
        showNotification('Erreur: ' + error.message, 'error');
    });
}

// ================================================================================
// GESTION DES CONTACTS
// ================================================================================

function showAddContactModal() {
    const modal = new bootstrap.Modal(document.getElementById('addContactModal'));
    modal.show();
}

function handleAddContact(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', 'nfc_save_contact');
    formData.append('nonce', leadsConfig.nonce);
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Enregistrement...';
    
    fetch(leadsConfig.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Contact ajouté avec succès!', 'success');
            
            // Add to local data
            allContacts.push(data.data);
            filteredContacts = [...allContacts];
            updateDisplay();
            
            // Close modal and reset form
            bootstrap.Modal.getInstance(document.getElementById('addContactModal')).hide();
            event.target.reset();
        } else {
            throw new Error(data.data || 'Erreur d\'ajout');
        }
    })
    .catch(error => {
        showNotification('Erreur: ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Enregistrer';
    });
}

function viewContact(contactId) {
    const contact = allContacts.find(c => c.ID == contactId);
    if (!contact) return;
    
    document.getElementById('contactDetailsContent').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Informations Personnelles</h6>
                <p><strong>Nom :</strong> ${escapeHtml(contact.firstname || '')} ${escapeHtml(contact.lastname || '')}</p>
                <p><strong>Email :</strong> ${contact.email ? `<a href="mailto:${escapeHtml(contact.email)}">${escapeHtml(contact.email)}</a>` : '-'}</p>
                <p><strong>Téléphone :</strong> ${contact.mobile ? `<a href="tel:${escapeHtml(contact.mobile)}">${escapeHtml(contact.mobile)}</a>` : '-'}</p>
            </div>
            <div class="col-md-6">
                <h6>Informations Professionnelles</h6>
                <p><strong>Entreprise :</strong> ${escapeHtml(contact.society || '-')}</p>
                <p><strong>Poste :</strong> ${escapeHtml(contact.post_title || '-')}</p>
                <p><strong>Contact via :</strong> Profil Source</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Historique</h6>
                <p><strong>Premier contact :</strong> ${new Date(contact.post_date).toLocaleDateString('fr-FR', {
                    year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                })}</p>
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('contactDetailsModal'));
    modal.show();
}

function editContact(contactId) {
    // TODO: Implement edit functionality
    showNotification('Fonctionnalité d\'édition en développement', 'info');
}

function deleteContact(contactId) {
    if (!confirm('Supprimer ce contact ?')) return;
    
    showNotification('Suppression en cours...', 'info');
    
    fetch(leadsConfig.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'nfc_delete_lead',
            nonce: leadsConfig.nonce,
            contact_id: contactId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Contact supprimé', 'success');
            
            // Remove from local data
            allContacts = allContacts.filter(c => c.ID != contactId);
            filteredContacts = filteredContacts.filter(c => c.ID != contactId);
            updateDisplay();
        } else {
            throw new Error(data.data || 'Erreur de suppression');
        }
    })
    .catch(error => {
        showNotification('Erreur: ' + error.message, 'error');
    });
}

// ================================================================================
// EXPORT / IMPORT
// ================================================================================

function exportContacts(filter) {
    let contactsToExport;
    let filename = 'contacts-nfc';
    
    switch (filter) {
        case 'current':
            contactsToExport = filteredContacts;
            filename += '-filtrés';
            break;
        case 'all':
            contactsToExport = allContacts;
            filename += '-tous';
            break;
        default:
            // Export specific vCard
            contactsToExport = allContacts.filter(c => {
                // Logic to filter by vCard ID
                return true; // TODO: Implement vCard filtering
            });
            filename += '-profil-' + filter;
    }
    
    if (contactsToExport.length === 0) {
        showNotification('Aucun contact à exporter', 'warning');
        return;
    }
    
    // Generate CSV
    const headers = ['Prénom', 'Nom', 'Email', 'Téléphone', 'Entreprise', 'Poste', 'Date Contact'];
    const csvContent = [
        headers.join(','),
        ...contactsToExport.map(contact => [
            contact.firstname || '',
            contact.lastname || '',
            contact.email || '',
            contact.mobile || '',
            contact.society || '',
            contact.post_title || '',
            new Date(contact.post_date).toLocaleDateString('fr-FR')
        ].map(field => `"${field}"`).join(','))
    ].join('\n');
    
    // Download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `${filename}-${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification(`Export de ${contactsToExport.length} contact(s) réalisé!`, 'success');
}

function exportSelected() {
    const selected = Array.from(document.querySelectorAll('.contact-checkbox:checked'))
                          .map(cb => cb.value);
    
    if (selected.length === 0) {
        showNotification('Aucun contact sélectionné', 'warning');
        return;
    }
    
    const contactsToExport = allContacts.filter(c => selected.includes(c.ID.toString()));
    exportContacts('selected', contactsToExport);
}

function showImportModal() {
    const modal = new bootstrap.Modal(document.getElementById('importModal'));
    modal.show();
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        parseCSV(e.target.result);
    };
    reader.readAsText(file);
}

function parseCSV(csvText) {
    const lines = csvText.split('\n').filter(line => line.trim());
    if (lines.length < 2) {
        showNotification('Fichier CSV invalide', 'error');
        return;
    }
    
    const headers = lines[0].split(',').map(h => h.trim().toLowerCase());
    const columnMap = {
        firstname: ['firstname', 'prénom', 'prenom', 'first_name'],
        lastname: ['lastname', 'nom', 'last_name'],
        email: ['email', 'mail', 'e-mail'],
        mobile: ['mobile', 'phone', 'telephone', 'tel', 'téléphone'],
        society: ['society', 'company', 'entreprise', 'société'],
        post: ['post', 'poste', 'position', 'job', 'titre']
    };
    
    const mapping = {};
    Object.keys(columnMap).forEach(field => {
        for (let i = 0; i < headers.length; i++) {
            if (columnMap[field].includes(headers[i])) {
                mapping[field] = i;
                break;
            }
        }
    });
    
    csvData = [];
    for (let i = 1; i < lines.length; i++) {
        const values = lines[i].split(',').map(v => v.trim().replace(/^"(.*)"$/, '$1'));
        
        const contact = {
            firstname: mapping.firstname !== undefined ? values[mapping.firstname] : '',
            lastname: mapping.lastname !== undefined ? values[mapping.lastname] : '',
            email: mapping.email !== undefined ? values[mapping.email] : '',
            mobile: mapping.mobile !== undefined ? values[mapping.mobile] : '',
            society: mapping.society !== undefined ? values[mapping.society] : '',
            post: mapping.post !== undefined ? values[mapping.post] : ''
        };
        
        if (contact.email || (contact.firstname && contact.lastname)) {
            csvData.push(contact);
        }
    }
    
    showCSVPreview();
}

function showCSVPreview() {
    const preview = document.getElementById('csvPreview');
    const tbody = document.getElementById('csvPreviewBody');
    const stats = document.getElementById('csvStats');
    
    if (csvData.length === 0) {
        preview.classList.add('d-none');
        document.getElementById('importBtn').disabled = true;
        return;
    }
    
    tbody.innerHTML = csvData.slice(0, 5).map(contact => `
        <tr>
            <td>${escapeHtml(contact.firstname)}</td>
            <td>${escapeHtml(contact.lastname)}</td>
            <td>${escapeHtml(contact.email)}</td>
            <td>${escapeHtml(contact.mobile)}</td>
        </tr>
    `).join('');
    
    stats.textContent = `${csvData.length} contact(s) trouvé(s) dans le fichier`;
    preview.classList.remove('d-none');
    document.getElementById('importBtn').disabled = false;
}

function importCSV() {
    const profileId = document.getElementById('importProfileSelect').value;
    if (!profileId) {
        showNotification('Veuillez sélectionner un profil', 'warning');
        return;
    }
    
    if (csvData.length === 0) return;
    
    const importBtn = document.getElementById('importBtn');
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Import en cours...';
    
    // Process in batches
    let processed = 0;
    let errors = 0;
    
    const processBatch = async (startIndex) => {
        const batch = csvData.slice(startIndex, startIndex + 5);
        if (batch.length === 0) {
            // Finished
            importBtn.innerHTML = '<i class="fas fa-upload me-1"></i>Importer';
            importBtn.disabled = false;
            
            if (errors === 0) {
                showNotification(`${processed} contact(s) importé(s) avec succès!`, 'success');
            } else {
                showNotification(`${processed} contact(s) importé(s), ${errors} erreur(s)`, 'warning');
            }
            
            // Reset and refresh
            csvData = [];
            document.getElementById('csvFile').value = '';
            document.getElementById('csvPreview').classList.add('d-none');
            
            // Refresh page or reload contacts
            setTimeout(() => location.reload(), 1000);
            
            return;
        }
        
        // Import current batch
        const promises = batch.map(contact => {
            const formData = new FormData();
            formData.append('action', 'nfc_save_contact');
            formData.append('nonce', leadsConfig.nonce);
            formData.append('firstname', contact.firstname);
            formData.append('lastname', contact.lastname);
            formData.append('email', contact.email);
            formData.append('mobile', contact.mobile);
            formData.append('society', contact.society);
            formData.append('post', contact.post);
            formData.append('linked_virtual_card', profileId);
            
            return fetch(leadsConfig.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    processed++;
                } else {
                    errors++;
                }
            })
            .catch(() => errors++);
        });
        
        await Promise.all(promises);
        
        // Update progress
        const progress = Math.round(((startIndex + batch.length) / csvData.length) * 100);
        importBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>Import... ${progress}%`;
        
        // Process next batch
        setTimeout(() => processBatch(startIndex + 5), 500);
    };
    
    processBatch(0);
}

function downloadTemplate() {
    const template = [
        'Prénom,Nom,Email,Téléphone,Entreprise,Poste',
        'Jean,Dupont,jean.dupont@example.com,0123456789,Acme Corp,Directeur',
        'Marie,Martin,marie.martin@example.com,0987654321,Tech Solutions,Chef de projet'
    ].join('\n');
    
    const blob = new Blob([template], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', 'modele-contacts-nfc.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// ================================================================================
// UTILITAIRES
// ================================================================================

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}

function showNotification(message, type = 'info') {
    // Simple notification system
    const alertClass = type === 'error' ? 'danger' : type;
    const notification = document.createElement('div');
    notification.className = `alert alert-${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

console.log('✅ Page Leads initialisée avec succès');
</script>