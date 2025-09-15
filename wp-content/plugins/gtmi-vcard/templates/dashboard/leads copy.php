<?php
/**
 * LEADS.PHP - VERSION FINALE QUI FONCTIONNE
 * Utilise AJAX au lieu de REST API pour éviter les problèmes d'auth
 */

if (!defined('ABSPATH')) {
    exit;
}

// ================================================================================
// FONCTIONS HELPER
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
// LOGIQUE PRINCIPALE
// ================================================================================

$user_id = get_current_user_id();
$user_vcards = nfc_get_user_vcard_profiles($user_id);

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

// Variables globales pour compatibilité
global $nfc_vcard, $nfc_current_page;
$nfc_vcard = (object)['ID' => $primary_vcard_id];
$nfc_current_page = 'contacts';

// Configuration JavaScript
$contacts_config = [
    'vcard_id' => $primary_vcard_id,
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'is_multi_profile' => $is_multi_profile,
    'user_id' => $user_id,
    'use_ajax' => true // Flag pour utiliser AJAX au lieu de REST
];
?>

<!-- CSS -->
<link rel="stylesheet" href="<?php echo plugin_dir_url(dirname(dirname(__FILE__))); ?>assets/css/contacts-manager.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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
                <button class="btn btn-outline-primary" onclick="NFCLeads.showAddModal()">
                    <i class="fas fa-plus me-1"></i>Ajouter
                </button>
                <button class="btn btn-primary" onclick="NFCLeads.exportContacts()">
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
    <button class="btn btn-primary" onclick="NFCLeads.showAddModal()">
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
                        <button class="btn btn-outline-primary btn-sm" onclick="NFCLeads.exportSelected()">
                            <i class="fas fa-download me-1"></i>Exporter
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="NFCLeads.deleteSelected()">
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

<!-- MODALS (basiques pour l'instant) -->
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

<!-- JAVASCRIPT -->
<script>
// Configuration globale
window.nfcLeadsConfig = <?php echo json_encode($contacts_config); ?>;

console.log('🔧 Configuration Leads:', window.nfcLeadsConfig);

// Objet principal NFCLeads
window.NFCLeads = {
    config: window.nfcLeadsConfig,
    contacts: [],
    filteredContacts: [],
    currentPage: 1,
    itemsPerPage: 25,
    totalPages: 1,
    
    // Initialisation
    init: function() {
        console.log('🚀 NFCLeads - Initialisation');
        this.cacheElements();
        this.bindEvents();
        this.loadContacts();
    },
    
    // Cache des éléments DOM
    cacheElements: function() {
        this.elements = {
            loading: document.getElementById('contactsLoading'),
            empty: document.getElementById('contactsEmpty'),
            error: document.getElementById('contactsError'),
            content: document.getElementById('contactsContent'),
            tableView: document.getElementById('contactsTableView'),
            gridView: document.getElementById('contactsGridView'),
            tableBody: document.getElementById('contactsTableBody'),
            pagination: document.getElementById('contactsPagination'),
            paginationWrapper: document.getElementById('contactsPaginationWrapper'),
            
            // Filtres
            searchInput: document.getElementById('contactsSearch'),
            sourceFilter: document.getElementById('sourceFilter'),
            profileFilter: document.getElementById('profileFilter'),
            sortFilter: document.getElementById('sortFilter'),
            
            // Stats
            totalContactsStat: document.getElementById('totalContactsStat'),
            newContactsStat: document.getElementById('newContactsStat'),
            companiesStat: document.getElementById('companiesStat'),
            qrSourceStat: document.getElementById('qrSourceStat')
        };
    },
    
    // Liaison des événements
    bindEvents: function() {
        // Filtres
        if (this.elements.searchInput) {
            this.elements.searchInput.addEventListener('input', () => this.applyFilters());
        }
        if (this.elements.sourceFilter) {
            this.elements.sourceFilter.addEventListener('change', () => this.applyFilters());
        }
        if (this.elements.profileFilter) {
            this.elements.profileFilter.addEventListener('change', () => this.applyFilters());
        }
        if (this.elements.sortFilter) {
            this.elements.sortFilter.addEventListener('change', () => this.applyFilters());
        }
        
        // Vue
        const viewButtons = document.querySelectorAll('input[name="viewMode"]');
        viewButtons.forEach(btn => {
            btn.addEventListener('change', () => this.changeView());
        });
    },
    
   /*  // Charger les contacts via AJAX
    loadContacts: function() {
        console.log('📞 Chargement contacts via AJAX...');
        
        this.showLoadingState();
        
        fetch(this.config.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'nfc_get_user_leads',
                user_id: this.config.user_id,
                nonce: this.config.nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('✅ Contacts reçus:', data);
            console.log('📊 Nombre de contacts dans data.data:', data.data ? data.data.length : 0);
            
            if (data.success) {
                this.contacts = data.data || [];
                this.filteredContacts = [...this.contacts];
                
                console.log('📊 this.contacts.length:', this.contacts.length);
                console.log('📊 this.filteredContacts.length:', this.filteredContacts.length);
                
                this.updateStats();
                this.applyFilters();
                this.showContent();
            } else {
                throw new Error(data.data || 'Erreur inconnue');
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement contacts:', error);
            this.showError(error.message);
        });
    }, */
    
    // Mettre à jour les stats
    updateStats: function() {
        const total = this.contacts.length;
        
        // Total
        if (this.elements.totalContactsStat) {
            this.elements.totalContactsStat.textContent = total;
        }
        
        // Cette semaine
        const oneWeekAgo = new Date();
        oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
        
        const newThisWeek = this.contacts.filter(contact => {
            const contactDate = new Date(contact.created_at || contact.contact_datetime);
            return contactDate >= oneWeekAgo;
        }).length;
        
        if (this.elements.newContactsStat) {
            this.elements.newContactsStat.textContent = newThisWeek;
        }
        
        // Entreprises
        const companies = new Set();
        this.contacts.forEach(contact => {
            if (contact.society) {
                companies.add(contact.society);
            }
        });
        
        if (this.elements.companiesStat) {
            this.elements.companiesStat.textContent = companies.size;
        }
        
        // QR Code
        const qrContacts = this.contacts.filter(contact => contact.source === 'qr').length;
        if (this.elements.qrSourceStat) {
            this.elements.qrSourceStat.textContent = qrContacts;
        }
    },
    
    // Appliquer les filtres
    applyFilters: function() {
        let filtered = [...this.contacts];
        
        // Filtre recherche
        const searchValue = this.elements.searchInput?.value.toLowerCase().trim();
        if (searchValue) {
            filtered = filtered.filter(contact => {
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
        const sourceValue = this.elements.sourceFilter?.value;
        if (sourceValue) {
            filtered = filtered.filter(contact => contact.source === sourceValue);
        }
        
        // Filtre profil (si multi-profil)
        const profileValue = this.elements.profileFilter?.value;
        if (profileValue) {
            filtered = filtered.filter(contact => contact.vcard_id == profileValue);
        }
        
        // Tri
        const sortValue = this.elements.sortFilter?.value || 'date_desc';
        filtered.sort((a, b) => {
            switch (sortValue) {
                case 'date_asc':
                    return new Date(a.created_at || a.contact_datetime) - new Date(b.created_at || b.contact_datetime);
                case 'date_desc':
                    return new Date(b.created_at || b.contact_datetime) - new Date(a.created_at || a.contact_datetime);
                case 'name_asc':
                    return (a.lastname || '').localeCompare(b.lastname || '');
                case 'name_desc':
                    return (b.lastname || '').localeCompare(a.lastname || '');
                default:
                    return 0;
            }
        });
        
        this.filteredContacts = filtered;
        this.currentPage = 1;
        this.calculatePagination();
        this.renderContacts();
    },
    
    // Calculer pagination
    calculatePagination: function() {
        this.totalPages = Math.ceil(this.filteredContacts.length / this.itemsPerPage);
        if (this.currentPage > this.totalPages) {
            this.currentPage = Math.max(1, this.totalPages);
        }
    },
    
    // Rendu des contacts
    renderContacts: function() {
        if (this.filteredContacts.length === 0) {
            this.showEmptyState();
            return;
        }
        
        this.hideAllStates();
        this.elements.content?.classList.remove('d-none');
        
        const viewMode = document.querySelector('input[name="viewMode"]:checked')?.id;
        
        if (viewMode === 'gridViewBtn') {
            this.renderGridView();
            this.elements.gridView?.classList.remove('d-none');
            this.elements.tableView?.classList.add('d-none');
        } else {
            this.renderTableView();
            this.elements.tableView?.classList.remove('d-none');
            this.elements.gridView?.classList.add('d-none');
        }
        
        this.renderPagination();
    },
    
    // Rendu vue tableau
    renderTableView: function() {
        if (!this.elements.tableBody) return;
        
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        const pageContacts = this.filteredContacts.slice(start, end);
        
        let html = '';
        pageContacts.forEach(contact => {
            const fullName = `${contact.firstname || ''} ${contact.lastname || ''}`.trim();
            const contactDate = contact.contact_datetime || contact.created_at;
            const formattedDate = contactDate ? new Date(contactDate).toLocaleDateString('fr-FR') : 'N/A';
            
            html += `
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input contact-checkbox" value="${contact.id}">
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="contact-avatar me-2">
                                ${this.getInitials(fullName)}
                            </div>
                            <div>
                                <div class="fw-medium">${this.escapeHtml(fullName)}</div>
                                ${contact.post ? `<small class="text-muted">${this.escapeHtml(contact.post)}</small>` : ''}
                            </div>
                        </div>
                    </td>
                    <td>${contact.email ? this.escapeHtml(contact.email) : '-'}</td>
                    <td>${contact.mobile ? this.escapeHtml(contact.mobile) : '-'}</td>
                    <td>${contact.society ? this.escapeHtml(contact.society) : '-'}</td>
                    <td>
                        <span class="badge bg-secondary">${this.getSourceLabel(contact.source || 'web')}</span>
                    </td>
                    ${this.config.is_multi_profile ? `
                    <td>
                        <small class="text-muted">${this.escapeHtml(contact.vcard_source_name || 'N/A')}</small>
                    </td>
                    ` : ''}
                    <td>
                        <small class="text-muted">${formattedDate}</small>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="NFCLeads.viewContact(${contact.id})" title="Voir">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="NFCLeads.deleteContact(${contact.id})" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        this.elements.tableBody.innerHTML = html;
    },
    
    // Rendu vue grille
    renderGridView: function() {
        // TODO: Implémenter la vue grille
        console.log('Vue grille à implémenter');
    },
    
    // Rendu pagination
    renderPagination: function() {
        // TODO: Implémenter la pagination
        console.log('Pagination à implémenter');
    },
    
    // États d'affichage
    showLoadingState: function() {
        this.hideAllStates();
        this.elements.loading?.classList.remove('d-none');
    },
    
    showEmptyState: function() {
        this.hideAllStates();
        this.elements.empty?.classList.remove('d-none');
    },
    
    showError: function(message) {
        this.hideAllStates();
        this.elements.error?.classList.remove('d-none');
        console.error('NFCLeads Error:', message);
    },
    
    showContent: function() {
        this.hideAllStates();
        this.elements.content?.classList.remove('d-none');
    },
    
    hideAllStates: function() {
        this.elements.loading?.classList.add('d-none');
        this.elements.empty?.classList.add('d-none');
        this.elements.error?.classList.add('d-none');
        this.elements.content?.classList.add('d-none');
    },
    
    // Utilitaires
    getInitials: function(name) {
        return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().substring(0, 2) || '?';
    },
    
    escapeHtml: function(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
    
    getSourceLabel: function(source) {
        const labels = {
            'qr': 'QR Code',
            'nfc': 'NFC',
            'web': 'Site Web',
            'manual': 'Manuel'
        };
        return labels[source] || 'Autre';
    },
    
    // Actions (à implémenter)
    changeView: function() {
        this.renderContacts();
    },
    
    viewContact: function(contactId) {
        console.log('Voir contact:', contactId);
        // TODO: Implémenter
    },
    
    deleteContact: function(contactId) {
        console.log('Supprimer contact:', contactId);
        // TODO: Implémenter
    },
    
    showAddModal: function() {
        console.log('Ajouter contact');
        // TODO: Implémenter
    },
    
    exportContacts: function() {
        console.log('Exporter contacts');
        // TODO: Implémenter
    }
};

// Démarrage
jQuery(document).ready(function() {
    console.log('🚀 Démarrage NFCLeads');
    NFCLeads.init();
});
</script>