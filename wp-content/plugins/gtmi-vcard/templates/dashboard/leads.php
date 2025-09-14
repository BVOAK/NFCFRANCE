<?php
/**
 * Template: Gestion des leads/contacts - Dashboard NFC Multi-profils
 * 
 * Fichier: templates/dashboard/leads.php
 * Version adaptative basée sur contacts.php avec support multi-vCards
 */

if (!defined('ABSPATH')) {
    exit;
}

// ================================================================================
// FONCTIONS HELPER (si manquantes)
// ================================================================================

if (!function_exists('nfc_get_user_vcard_profiles')) {
    function nfc_get_user_vcard_profiles($user_id) {
        if (class_exists('NFC_Enterprise_Core')) {
            $profiles = NFC_Enterprise_Core::get_user_enterprise_cards($user_id);
            error_log("📊 DEBUG nfc_get_user_vcard_profiles - Trouvé " . count($profiles) . " profils pour user " . $user_id);
            return $profiles;
        }
        return [];
    }
}

if (!function_exists('nfc_get_vcard_contacts')) {
    function nfc_get_vcard_contacts($vcard_id) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, p.post_date,
                   pm1.meta_value as firstname,
                   pm2.meta_value as lastname, 
                   pm3.meta_value as email,
                   pm4.meta_value as mobile,
                   pm5.meta_value as society,
                   pm6.meta_value as linked_vcard
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'firstname'
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'lastname'
            LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'email'
            LEFT JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = 'mobile'
            LEFT JOIN {$wpdb->postmeta} pm5 ON p.ID = pm5.post_id AND pm5.meta_key = 'society'
            LEFT JOIN {$wpdb->postmeta} pm6 ON p.ID = pm6.post_id AND pm6.meta_key = 'linked_virtual_card'
            WHERE p.post_type = 'lead'
            AND p.post_status = 'publish'
            AND pm6.meta_value LIKE %s
            ORDER BY p.post_date DESC
        ", '%"' . $vcard_id . '"%'));
        
        return $results;
    }
}

if (!function_exists('nfc_format_vcard_full_name')) {
    function nfc_format_vcard_full_name($vcard_data) {
        $firstname = $vcard_data['firstname'] ?? '';
        $lastname = $vcard_data['lastname'] ?? '';
        return trim($firstname . ' ' . $lastname) ?: 'Profil sans nom';
    }
}

if (!function_exists('nfc_get_contacts_trend')) {
    function nfc_get_contacts_trend($vcard_id = 0) {
        return 0;
    }
}

// ================================================================================
// LOGIQUE D'ADAPTATION
// ================================================================================

$user_id = get_current_user_id();
$user_vcards = nfc_get_user_vcard_profiles($user_id);

// 🎯 FORCER selected_vcard_id à NULL pour toujours afficher tous les contacts en mode multi
$selected_vcard_id = null;

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
    
    // Toujours afficher TOUS les contacts en mode multi-profils
    $contacts = nfc_get_enterprise_contacts($user_id, null, 1000);
} else {
    $page_title = "Mes contacts"; 
    $page_subtitle = "Gérez les contacts reçus via votre vCard";
    $current_vcard = $user_vcards[0];
    $single_vcard_id = $current_vcard['vcard_id'];
    $contacts = nfc_get_vcard_contacts($single_vcard_id);
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

// Nettoyer les contacts pour le JavaScript
$contacts_cleaned = [];
foreach ($contacts as $contact) {
    $contacts_cleaned[] = [
        'id' => $contact->ID ?? $contact['ID'],
        'ID' => $contact->ID ?? $contact['ID'],
        'post_title' => $contact->post_title ?? $contact['post_title'] ?? 'Contact sans nom',
        'firstname' => $contact->firstname ?? $contact['firstname'] ?? '',
        'lastname' => $contact->lastname ?? $contact['lastname'] ?? '',
        'email' => $contact->email ?? $contact['email'] ?? '',
        'mobile' => $contact->mobile ?? $contact['mobile'] ?? '',
        'society' => $contact->society ?? $contact['society'] ?? '',
        'source' => $contact->source ?? $contact['source'] ?? 'web',
        'contact_datetime' => $contact->contact_datetime ?? $contact['contact_datetime'] ?? '',
        'created_at' => $contact->post_date ?? $contact['created_at'] ?? date('Y-m-d H:i:s'),
        'linked_vcard' => $contact->linked_vcard ?? $contact['linked_vcard'] ?? []
    ];
}

error_log("✅ " . count($contacts_cleaned) . " contacts nettoyés pour JavaScript");

// Variables globales disponibles depuis le routing
global $nfc_vcard, $nfc_current_page;
$vcard = $nfc_vcard ?? (object)['ID' => $current_vcard['vcard_id'] ?? 0];
$vcard_id = $vcard->ID;

// URL publique de la vCard pour contexte
$public_url = $current_vcard ? get_permalink($current_vcard['vcard_id']) : '';

// Enqueue des assets spécifiques à cette page
$plugin_url = plugin_dir_url(dirname(dirname(dirname(__FILE__))));

// ================================================================================
// CONFIGURATION JAVASCRIPT UNIFIÉE
// ================================================================================

$unified_config = [
    // Données de base
    'vcard_id' => $selected_vcard_id ?: ($current_vcard ? $current_vcard['vcard_id'] : 0),
    'user_id' => $user_id,
    'current_page' => $nfc_current_page ?? 'contacts',
    
    // URLs et sécurité
    'api_url' => home_url('/wp-json/gtmi_vcard/v1/'),
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'public_url' => $public_url,
    
    // Contexte multi-profils
    'is_multi_profile' => $is_multi_profile,
    'selected_vcard_id' => $selected_vcard_id,
    'show_profile_filter' => $show_profile_filter,
    'user_vcards' => $user_vcards,
    'vcards_count' => count($user_vcards),
    
    // Mode de fonctionnement
    'force_mode' => $selected_vcard_id ? 'single_vcard' : 'multi_global',
    'stable_endpoint' => $selected_vcard_id ? false : true,
    'prevent_auto_load' => true,
    
    // Données et stats
    'contacts_count' => count($contacts),
    'initial_contacts' => $contacts_cleaned, // 🎯 Passer les contacts PHP au JavaScript
    'user_name' => $user_display_name,
    
    // Debug et config
    'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
    
    // Textes i18n pour compatibilité
    'i18n' => [
        'loading' => 'Chargement...',
        'error' => 'Une erreur est survenue',
        'success' => 'Action réalisée avec succès',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer ce contact ?',
        'confirm_delete_multiple' => 'Êtes-vous sûr de vouloir supprimer ces contacts ?',
        'no_contacts' => 'Aucun contact trouvé',
        'export_success' => 'Export réalisé avec succès',
        'import_success' => 'Import réalisé avec succès'
    ]
];

error_log("✅ Configuration finale validée pour JavaScript");
?>

<!-- PAGE HEADER - Structure identique à contacts.php -->
<div class="contacts-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="h3 mb-1">
                <i class="fas fa-users me-2 text-primary"></i>
                <?php echo esc_html($page_title); ?>
            </h2>
            <p class="text-muted mb-0"><?php echo esc_html($page_subtitle); ?></p>
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
                        <li><a class="dropdown-item" href="#" onclick="exportContacts('current')">
                            <i class="fas fa-filter me-2"></i>Filtres actuels
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportContacts('all')">
                            <i class="fas fa-users me-2"></i>Tous les contacts
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STATS CARDS - Structure identique à contacts.php -->
<div class="row mb-4" id="contactsStatsRow">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-primary mb-2">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <h3 class="h4 mb-1" id="totalContacts"><?php echo count($contacts); ?></h3>
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

<!-- CONTROLS SECTION - Structure identique à contacts.php -->
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
                                   placeholder="Rechercher un contact...">
                        </div>
                    </div>
                    
                    <!-- 🆕 Filtre par profil (si multi-profils) -->
                    <?php if ($show_profile_filter): ?>
                    <div class="col-md-3">
                        <select class="form-select" id="profileFilter" onchange="filterByProfile()">
                            <option value="">Tous les profils (<?php echo count($contacts); ?>)</option>
                            <?php foreach ($user_vcards as $vcard): ?>
                                <option value="<?php echo $vcard['vcard_id']; ?>">
                                    <?php echo esc_html(nfc_format_vcard_full_name($vcard['vcard_data'] ?? [])); ?>
                                    (<?php echo count(array_filter($contacts, function($c) use ($vcard) {
                                        return strpos($c->linked_vcard ?? '', '"' . $vcard['vcard_id'] . '"') !== false;
                                    })); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Filters -->
                    <div class="col-md-2">
                        <select class="form-select" id="sourceFilter">
                            <option value="">Toutes sources</option>
                            <option value="web">Site web</option>
                            <option value="qr">QR Code</option>
                            <option value="manual">Manuel</option>
                        </select>
                    </div>
                    
                    <!-- Actions -->
                    <div class="col-md-3 text-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary" id="viewToggleTable">
                                <i class="fas fa-table"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="viewToggleGrid">
                                <i class="fas fa-th"></i>
                            </button>
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
<div id="contactsLoading" class="text-center py-5 d-none">
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

<!-- ERROR STATE -->
<div id="contactsError" class="alert alert-danger d-none" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <span id="contactsErrorMessage">Une erreur est survenue lors du chargement des contacts.</span>
</div>

<!-- CONTENU PRINCIPAL -->
<div class="row">
    <div class="col-12">
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
                            <!-- Rempli par JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination - ID IDENTIQUE -->
            <div class="card-footer bg-white" id="contactsPaginationWrapper">
                <div class="row align-items-center">
                    <div class="col">
                        <small class="text-muted" id="contactsCount">
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
        
        <!-- Grid View - ID IDENTIQUE À contacts.php -->
        <div id="contactsGridView" class="d-none">
            <div id="contactsGrid" class="row">
                <!-- Rempli par JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- MODALS - Structure identique à contacts.php -->

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

<!-- Modal Édition Contact -->
<div class="modal fade" id="editContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editContactForm">
                <input type="hidden" id="editContactId" name="contact_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="editFirstname" name="firstname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="editLastname" name="lastname" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" id="editMobile" name="mobile">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Entreprise</label>
                        <input type="text" class="form-control" id="editSociety" name="society">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Poste</label>
                        <input type="text" class="form-control" id="editPost" name="post">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Sauvegarder</button>
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
                    <i class="fas fa-edit me-2"></i>Modifier
                </button>
                <button type="button" class="btn btn-danger" id="deleteContactFromDetails" onclick="deleteContactFromDetails()">
                    <i class="fas fa-trash me-2"></i>Supprimer
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

<!-- JAVASCRIPT UNIFIÉ -->
<script type="text/javascript">
// 🎯 CONFIGURATION UNIFIÉE
window.nfcContactsConfig = <?php echo wp_json_encode($unified_config); ?>;

// Compatibilité
window.contactsConfig = window.nfcContactsConfig;

console.log('🔧 Configuration unifiée chargée:', window.nfcContactsConfig);

// Empêcher le chargement automatique de contacts-manager.js
window.nfcContactsPreventAutoLoad = true;

// Override DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 DOMContentLoaded leads.php avec configuration unifiée');
    
    const waitForNFCContacts = setInterval(() => {
        if (window.NFCContacts && window.NFCContacts.loadContacts) {
            clearInterval(waitForNFCContacts);
            
            console.log('📧 NFCContacts détecté, application configuration unifiée...');
            
            // Appliquer configuration
            window.NFCContacts.config = window.nfcContactsConfig;
            
            // Override loadContacts pour utiliser les données PHP
            window.NFCContacts.loadContacts = function() {
                console.log('🔧 loadContacts() avec données PHP directes');
                
                const config = window.nfcContactsConfig;
                const self = this;
                
                // Utiliser les données PHP directement
                if (config.initial_contacts && config.initial_contacts.length > 0) {
                    console.log('📦 Utilisation des contacts PHP:', config.initial_contacts.length);
                    
                    self.contacts = config.initial_contacts;
                    self.filteredContacts = [...self.contacts];
                    
                    // Rendu immédiat
                    if (typeof self.renderContacts === 'function') {
                        self.renderContacts();
                    }
                    if (typeof self.updateStats === 'function') {
                        self.updateStats();
                    }
                    if (typeof self.updatePagination === 'function') {
                        self.updatePagination();
                    }
                    
                    console.log('✅ Contacts affichés depuis PHP:', self.contacts.length);
                    return;
                }
                
                // Fallback vide si pas de données
                console.log('⚠️ Aucun contact dans les données PHP');
                self.contacts = [];
                self.filteredContacts = [];
                
                if (typeof self.renderContacts === 'function') {
                    self.renderContacts();
                }
            };
            
            // Démarrer le chargement
            console.log('🚀 Démarrage avec données PHP...');
            window.NFCContacts.loadContacts();
            
            console.log('✅ Configuration unifiée appliquée');
        }
    }, 50);
    
    setTimeout(() => {
        clearInterval(waitForNFCContacts);
        if (!window.NFCContacts) {
            console.error('❌ NFCContacts non trouvé après 10 secondes');
        }
    }, 10000);
});

// Fonction de filtrage par profil (pour compatibilité)
function filterByProfile() {
    const profileFilter = document.getElementById('profileFilter');
    if (!profileFilter) return;
    
    const selectedVcardId = profileFilter.value;
    console.log('🔧 Filtre par profil:', selectedVcardId);
    
    if (selectedVcardId) {
        const url = new URL(window.location);
        url.searchParams.set('filter', 'vcard');
        url.searchParams.set('vcard_id', selectedVcardId);
        window.location.href = url.toString();
    } else {
        const url = new URL(window.location);
        url.searchParams.delete('filter');
        url.searchParams.delete('vcard_id');
        window.location.href = url.toString();
    }
}

// Fonctions de compatibilité avec l'interface
function exportContacts(scope) {
    console.log('📤 Export contacts:', scope);
    // Sera implémenté par contacts-manager.js
    if (window.NFCContacts && typeof window.NFCContacts.exportCSV === 'function') {
        window.NFCContacts.exportCSV();
    }
}

function viewContact(id) {
    console.log('👁️ Voir contact:', id);
    // Sera implémenté par contacts-manager.js
}

function editContact(id) {
    console.log('✏️ Éditer contact:', id);
    // Sera implémenté par contacts-manager.js
}

function deleteContact(id) {
    console.log('🗑️ Supprimer contact:', id);
    // Sera implémenté par contacts-manager.js
}

function editContactFromDetails() {
    console.log('✏️ Éditer depuis détails');
    // Sera implémenté par contacts-manager.js
}

function deleteContactFromDetails() {
    console.log('🗑️ Supprimer depuis détails');
    // Sera implémenté par contacts-manager.js
}

console.log('✅ Script leads.php avec configuration unifiée chargé');
</script>