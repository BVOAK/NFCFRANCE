<?php
/**
 * Template Dashboard NFC - Page Leads Multi-Profils
 * 
 * Fichier: templates/dashboard/leads.php
 * Template adaptatif pour contacts simple/multi-profils
 */

if (!defined('ABSPATH')) {
    exit;
}

// ================================================================================
// FONCTIONS UTILITAIRES
// ================================================================================

if (!function_exists('nfc_get_user_vcard_profiles')) {
    function nfc_get_user_vcard_profiles($user_id) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT ec.*, p.post_title, p.post_date
            FROM {$wpdb->prefix}nfc_enterprise_cards ec
            INNER JOIN {$wpdb->posts} p ON ec.vcard_id = p.ID
            WHERE ec.main_user_id = %d
            AND p.post_status = 'publish'
            ORDER BY ec.created_at DESC
        ", $user_id));
        
        $profiles = [];
        foreach ($results as $result) {
            $vcard_data = [];
            $meta_keys = ['firstname', 'lastname', 'position', 'company', 'email', 'mobile', 'society', 'service'];
            foreach ($meta_keys as $key) {
                $vcard_data[$key] = get_post_meta($result->vcard_id, $key, true);
            }
            $vcard_data['is_configured'] = !empty($vcard_data['firstname']) && !empty($vcard_data['lastname']);
            
            $profiles[] = [
                'id' => (int)$result->id,
                'order_id' => (int)$result->order_id,
                'vcard_id' => (int)$result->vcard_id,
                'card_position' => (int)$result->card_position,
                'card_identifier' => $result->card_identifier,
                'card_status' => $result->card_status,
                'company_name' => $result->company_name,
                'main_user_id' => (int)$result->main_user_id,
                'created_at' => $result->created_at,
                'updated_at' => $result->updated_at,
                'post_title' => $result->post_title,
                'post_date' => $result->post_date,
                'vcard_data' => $vcard_data,
                'vcard_url' => get_permalink($result->vcard_id),
                'stats' => ['views' => 0, 'contacts' => 0]
            ];
        }
        
        error_log("📊 DEBUG nfc_get_user_vcard_profiles - Trouvé " . count($profiles) . " profils pour user " . $user_id);
        return $profiles;
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
        
        error_log("📊 DEBUG nfc_get_vcard_contacts - Trouvé " . count($results) . " pour vCard " . $vcard_id);
        
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

// ================================================================================
// NETTOYAGE DES DONNÉES POUR JAVASCRIPT
// ================================================================================

if (!empty($contacts)) {
    foreach ($contacts as &$contact) {
        // Nettoyer linked_vcard qui contient des données sérialisées PHP
        if (isset($contact->linked_vcard) && is_string($contact->linked_vcard)) {
            // Si c'est du PHP sérialisé, le désérialiser et le convertir
            if (strpos($contact->linked_vcard, 'a:') === 0) {
                $unserialized = @unserialize($contact->linked_vcard);
                
                if ($unserialized !== false && is_array($unserialized)) {
                    // Convertir en array simple pour JavaScript
                    $contact->linked_vcard = array_values($unserialized);
                } else {
                    // Si échec de désérialisation, mettre un array vide
                    $contact->linked_vcard = [];
                }
            }
        }
        
        // Convertir l'objet en array pour JSON
        $contact = (array)$contact;
        
        // Nettoyer autres champs potentiellement problématiques
        $string_fields = ['post_title', 'firstname', 'lastname', 'email', 'mobile', 'society'];
        foreach ($string_fields as $field) {
            if (isset($contact[$field]) && is_string($contact[$field])) {
                $contact[$field] = wp_kses_post($contact[$field]);
            }
        }
    }
    unset($contact); // Libérer la référence
    
    error_log("✅ " . count($contacts) . " contacts nettoyés pour JavaScript");
}

// ================================================================================
// CONFIGURATION JAVASCRIPT SÉCURISÉE
// ================================================================================

$contacts_config = [
    'vcard_id' => $current_vcard ? $current_vcard['vcard_id'] : null,
    'user_id' => $user_id,
    'selected_vcard_id' => $selected_vcard_id,
    'is_multi_profile' => $is_multi_profile,
    'user_vcards' => $user_vcards,
    'ajax_url' => admin_url('admin-ajax.php'),
    'api_url' => home_url('/wp-json/gtmi_vcard/v1/'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'user_name' => $user_display_name,
    'initial_contacts' => $contacts
];

// Vérification finale du JSON
$json_test = json_encode($contacts_config, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_SLASHES);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("❌ ERREUR JSON finale: " . json_last_error_msg());
    
    // Fallback: enlever les contacts si problème
    $contacts_config['initial_contacts'] = [];
    error_log("🔄 Contacts supprimés de la config à cause d'erreur JSON");
}

error_log("✅ Configuration finale validée pour JavaScript");
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
                    Nouveau Contact
                </button>
            </div>
        </div>
    </div>
</div>

<!-- STATS CARDS - IDENTIQUES À contacts.php -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="h4 text-primary mb-1" id="totalContactsStat"><?php echo count($contacts); ?></div>
                <small class="text-muted">Total Contacts</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="h4 text-success mb-1" id="newContactsStat">0</div>
                <small class="text-muted">Cette semaine</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="h4 text-info mb-1" id="companiesStat">0</div>
                <small class="text-muted">Entreprises</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="h4 text-warning mb-1" id="qrSourceStat">0</div>
                <small class="text-muted">Via QR Code</small>
            </div>
        </div>
    </div>
</div>

<!-- FILTRES ET CONTRÔLES -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <!-- Recherche -->
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="contactsSearch" placeholder="Rechercher un contact...">
                        </div>
                    </div>
                    
                    <!-- 🆕 Filtre par profil (si multi-profils) -->
                    <?php if ($show_profile_filter): ?>
                    <div class="col-md-3">
                        <select class="form-select" id="profileFilter" onchange="filterByProfile()">
                            <option value="">Tous les profils (<?php echo count($contacts); ?>)</option>
                            <?php foreach ($user_vcards as $vcard): ?>
                                <option value="<?php echo $vcard['vcard_id']; ?>" <?php echo $selected_vcard_id == $vcard['vcard_id'] ? 'selected' : ''; ?>>
                                    <?php echo esc_html(nfc_format_vcard_full_name($vcard['vcard_data'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Filtres -->
                    <div class="col-md-2">
                        <select class="form-select" id="sourceFilter">
                            <option value="">Toutes sources</option>
                            <option value="web">Site web</option>
                            <option value="qr">QR Code</option>
                            <option value="manual">Manuel</option>
                        </select>
                    </div>
                    
                    <!-- Tri -->
                    <div class="col-md-2">
                        <select class="form-select" id="sortFilter">
                            <option value="date_desc">Plus récent</option>
                            <option value="date_asc">Plus ancien</option>
                            <option value="name_asc">Nom A-Z</option>
                            <option value="name_desc">Nom Z-A</option>
                        </select>
                    </div>
                    
                    <!-- Vue -->
                    <div class="col-md-1">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm active" id="tableViewBtn">
                                <i class="fas fa-list"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="gridViewBtn">
                                <i class="fas fa-th"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ÉTATS D'AFFICHAGE -->
<div id="contactsLoading" class="text-center py-5 d-none">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
    </div>
    <p class="mt-3 text-muted">Chargement des contacts...</p>
</div>

<div id="contactsEmpty" class="text-center py-5 d-none">
    <i class="fas fa-users fa-3x text-muted mb-3"></i>
    <h4>Aucun contact trouvé</h4>
    <p class="text-muted">Aucun contact ne correspond à vos critères de recherche.</p>
</div>

<div id="contactsError" class="alert alert-danger d-none" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Une erreur est survenue lors du chargement des contacts.
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
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prénom *</label>
                            <input type="text" class="form-control" name="firstname" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="lastname" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" name="mobile">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Entreprise</label>
                            <input type="text" class="form-control" name="society">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Poste</label>
                            <input type="text" class="form-control" name="post">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer
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
                    <i class="fas fa-upload me-2"></i>Importer des contacts
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Fichier CSV</label>
                    <input type="file" class="form-control" id="csvFile" accept=".csv" onchange="previewCSV()">
                    <div class="form-text">
                        Format attendu: Prénom, Nom, Email, Téléphone, Entreprise
                    </div>
                </div>
                <div id="csvPreview" class="d-none">
                    <h6>Aperçu</h6>
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

<!-- CONFIGURATION JAVASCRIPT -->
<script>
// Configuration globale pour contacts-manager.js
window.nfcContactsConfig = <?php echo json_encode($contacts_config, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_SLASHES); ?>;

console.log('📧 Configuration NFCContacts injectée:', window.nfcContactsConfig);

// Validation de configuration
function isValidConfig() {
    const config = window.nfcContactsConfig;
    
    if (!config || !config.api_url) {
        return false;
    }
    
    // Mode multi-profils sans filtre: vcard_id peut être null
    if (config.is_multi_profile && !config.selected_vcard_id) {
        return !!config.user_id;
    }
    
    // Mode simple ou avec filtre: vcard_id obligatoire
    return !!config.vcard_id;
}

if (isValidConfig()) {
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
                console.log('🔧 loadContacts() overridé - utilisation endpoint adaptatif');
                
                // Re-valider la configuration
                if (!isValidConfig()) {
                    this.showError('Configuration invalide');
                    return;
                }
                
                // Sauvegarder le contexte 'this' AVANT les appels asynchrones
                const self = this;
                
                self.isLoading = true;
                self.showLoadingState();
                
                // Déterminer l'URL selon le mode
                let apiUrl;
                const config = window.nfcContactsConfig;
                
                console.log('🔧 Détermination URL - mode:', {
                    is_multi_profile: config.is_multi_profile,
                    selected_vcard_id: config.selected_vcard_id,
                    user_id: config.user_id,
                    vcard_id: config.vcard_id
                });
                
                if (config.is_multi_profile && !config.selected_vcard_id) {
                    // Mode multi-profils global: endpoint user
                    apiUrl = `${config.api_url}leads/user/${config.user_id}`;
                    console.log('🌐 Mode multi_global - URL:', apiUrl);
                } else {
                    // Mode simple ou avec filtre: endpoint vcard
                    const vcardId = config.selected_vcard_id || config.vcard_id;
                    apiUrl = `${config.api_url}leads/vcard/${vcardId}`;
                    console.log('🌐 Mode vcard spécifique - URL:', apiUrl);
                }
                
                // Appel API SANS .bind() - utiliser 'self' dans les callbacks
                fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': config.nonce
                    }
                })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(function(data) {
                    console.log('✅ Données reçues:', data);
                    
                    if (data.success && Array.isArray(data.data)) {
                        self.contacts = data.data;
                        self.filteredContacts = [...self.contacts];
                        self.renderTable();
                        self.updateStats();
                        console.log('✅ ' + self.contacts.length + ' contacts chargés');
                    } else {
                        throw new Error(data.message || 'Format de données invalide');
                    }
                })
                .catch(function(error) {
                    console.error('❌ Erreur chargement contacts:', error);
                    self.showError('Erreur: ' + error.message);
                })
                .finally(function() {
                    self.isLoading = false;
                    self.hideLoadingState();
                });
            };
            
            // Override complet pour la colonne profil en mode multi
            <?php if ($show_profile_filter): ?>
            // AJOUT: Override renderTable pour afficher la colonne profil
            window.NFCContacts.originalRenderTable = window.NFCContacts.renderTable;
window.NFCContacts.renderTable = function() {
    console.log('🔧 renderTable() overridé pour colonne profil');
    
    // Appel de la méthode originale
    this.originalRenderTable();
    
    // Ajouter colonne profil si mode multi-profils
    const table = document.getElementById('contactsTable');
    if (table && window.nfcContactsConfig.is_multi_profile) {
        console.log('📊 Ajout colonne profil - contacts:', this.filteredContacts.length);
        
        // Ajouter header s'il n'existe pas
        const headerRow = table.querySelector('thead tr');
        if (headerRow && !headerRow.querySelector('.profile-header')) {
            const profileHeader = document.createElement('th');
            profileHeader.className = 'profile-header';
            profileHeader.textContent = 'Profil Source';
            // Insérer avant la colonne Actions (dernière)
            const actionHeader = headerRow.querySelector('th:last-child');
            if (actionHeader) {
                headerRow.insertBefore(profileHeader, actionHeader);
                console.log('✅ Header "Profil Source" ajouté');
            }
        }
        
        // Ajouter cellules profil pour chaque ligne
        const bodyRows = table.querySelectorAll('tbody tr');
        bodyRows.forEach((row, index) => {
            if (!row.querySelector('.profile-cell')) {
                const contact = this.filteredContacts[index];
                if (contact) {
                    const profileCell = document.createElement('td');
                    profileCell.className = 'profile-cell';
                    
                    console.log(`📧 Contact ${index}:`, {
                        vcard_id: contact.vcard_id,
                        vcard_source_name: contact.vcard_source_name,
                        id: contact.id || contact.ID
                    });
                    
                    // MÉTHODE 1: Utiliser vcard_source_name si disponible (depuis API)
                    if (contact.vcard_source_name) {
                        profileCell.textContent = contact.vcard_source_name;
                        console.log(`✅ Contact ${index}: nom depuis API: ${contact.vcard_source_name}`);
                    }
                    // MÉTHODE 2: Chercher dans user_vcards par vcard_id
                    else if (contact.vcard_id) {
                        const vcard = window.nfcContactsConfig.user_vcards.find(v => 
                            parseInt(v.vcard_id) === parseInt(contact.vcard_id)
                        );
                        
                        if (vcard && vcard.vcard_data) {
                            const firstName = vcard.vcard_data.firstname || '';
                            const lastName = vcard.vcard_data.lastname || '';
                            const fullName = (firstName + ' ' + lastName).trim();
                            profileCell.textContent = fullName || `Profil #${contact.vcard_id}`;
                            console.log(`✅ Contact ${index}: nom depuis config: ${fullName}`);
                        } else {
                            profileCell.textContent = `Profil #${contact.vcard_id}`;
                            console.warn(`⚠️ Contact ${index}: vCard ${contact.vcard_id} non trouvée dans config`);
                        }
                    }
                    // MÉTHODE 3: Analyser linked_vcard pour trouver le vcard_id
                    else if (contact.linked_vcard && Array.isArray(contact.linked_vcard) && contact.linked_vcard.length > 0) {
                        const linkedVcardId = contact.linked_vcard[0];
                        const vcard = window.nfcContactsConfig.user_vcards.find(v => 
                            parseInt(v.vcard_id) === parseInt(linkedVcardId)
                        );
                        
                        if (vcard && vcard.vcard_data) {
                            const firstName = vcard.vcard_data.firstname || '';
                            const lastName = vcard.vcard_data.lastname || '';
                            const fullName = (firstName + ' ' + lastName).trim();
                            profileCell.textContent = fullName || `Profil #${linkedVcardId}`;
                            console.log(`✅ Contact ${index}: nom depuis linked_vcard: ${fullName}`);
                        } else {
                            profileCell.textContent = `Profil #${linkedVcardId}`;
                            console.warn(`⚠️ Contact ${index}: linked vCard ${linkedVcardId} non trouvée`);
                        }
                    }
                    // FALLBACK: Impossible de déterminer
                    else {
                        profileCell.textContent = 'Profil inconnu';
                        console.warn(`⚠️ Contact ${index}: impossible de déterminer le profil source`, contact);
                    }
                    
                    // Insérer avant la colonne Actions (dernière)
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 0) {
                        row.insertBefore(profileCell, cells[cells.length - 1]);
                    }
                }
            }
        });
        
        console.log('✅ Colonnes profil ajoutées à toutes les lignes');
    }
};
            <?php endif; ?>
            
            console.log('✅ Override terminé, initialisation NFCContacts...');
            
            // Forcer l'initialisation avec la nouvelle configuration
            window.NFCContacts.config = window.nfcContactsConfig;
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
    if (!profileFilter) {
        console.log('⚠️ Élément profileFilter non trouvé');
        return;
    }
    
    const selectedVcardId = profileFilter.value;
    console.log('🔧 Filtre par profil:', selectedVcardId);
    
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

console.log('✅ Script leads.php avec configuration corrigée chargé');
</script>