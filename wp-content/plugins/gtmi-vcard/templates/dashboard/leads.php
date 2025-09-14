<?php
/**
 * Template Dashboard NFC - Page Leads Adaptative
 * Fichier: wp-content/plugins/gtmi-vcard/templates/dashboard/leads.php
 * 
 * S'adapte automatiquement selon les données de l'utilisateur :
 * - Avec vCards : Affiche la gestion complète des leads
 * - Sans vCards : Propose de commander des produits
 */

if (!defined('ABSPATH')) {
    exit;
}

// Récupérer l'utilisateur et ses données
$user_id = get_current_user_id();
$user_vcards = nfc_get_user_vcard_profiles($user_id);

// 🎯 LOGIQUE ADAPTATIVE
if (empty($user_vcards)) {
    // Pas de vCards → Afficher page d'invitation à commander
    include __DIR__ . '/partials/no-products-state.php';
    return;
}

// A des vCards → Afficher la gestion des leads
$selected_vcard = isset($_GET['vcard_filter']) ? intval($_GET['vcard_filter']) : 0;
$global_stats = nfc_get_user_global_stats($user_id);
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

    <!-- État Vide si Pas de Contacts -->
    <?php if (empty($contacts)): ?>
        <div class="empty-state text-center py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="empty-state-icon mb-4">
                        <i class="fas fa-users fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Aucun contact reçu</h4>
                    <p class="text-muted mb-4">
                        Vos contacts apparaîtront ici lorsque des personnes interagiront avec vos cartes NFC.
                    </p>
                    
                    <div class="row text-start">
                        <div class="col-md-6">
                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-share-alt text-primary"></i>
                                </div>
                                <h6>Partagez vos cartes</h6>
                                <p class="small text-muted">
                                    Distribuez vos cartes NFC lors de vos rencontres professionnelles
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-plus text-success"></i>
                                </div>
                                <h6>Ajout manuel</h6>
                                <p class="small text-muted">
                                    Vous pouvez aussi ajouter des contacts manuellement
                                </p>
                                <button class="btn btn-sm btn-success" onclick="showAddContactModal()">
                                    <i class="fas fa-plus me-1"></i>Ajouter un contact
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        
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
                            </th>
                            <th onclick="sortBy('firstname')" style="cursor: pointer;">
                                Prénom <i class="fas fa-sort ms-1" id="sort-firstname"></i>
                            </th>
                            <th onclick="sortBy('email')" style="cursor: pointer;">
                                Email <i class="fas fa-sort ms-1" id="sort-email"></i>
                            </th>
                            <th onclick="sortBy('mobile')" style="cursor: pointer;">
                                Téléphone <i class="fas fa-sort ms-1" id="sort-mobile"></i>
                            </th>
                            <th onclick="sortBy('society')" style="cursor: pointer;">
                                Entreprise <i class="fas fa-sort ms-1" id="sort-society"></i>
                            </th>
                            <th onclick="sortBy('vcard_source')" style="cursor: pointer;">
                                Profil Source <i class="fas fa-sort ms-1" id="sort-vcard_source"></i>
                            </th>
                            <th onclick="sortBy('post_date')" style="cursor: pointer;">
                                Date Contact <i class="fas fa-sort ms-1" id="sort-post_date"></i>
                            </th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="contactsTableBody">
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
                    </tbody>
                </table>
            </div>

            <!-- Actions en Lot -->
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
        </div>

    <?php endif; ?>

</div>

<!-- Modals et JavaScript identiques à la version précédente -->
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
                    <!-- Formulaire identique à la version précédente -->
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

<!-- Styles et JavaScript identiques -->
<style>
.empty-state {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 3rem;
}

.tip-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    text-align: center;
}

.tip-icon {
    margin-bottom: 1rem;
}

.tip-icon i {
    font-size: 2rem;
}

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
}

.nfc-leads-page {
    padding: 1.5rem;
}
</style>

<script>
// JavaScript simplifié pour cette version
let allContacts = <?= json_encode($contacts) ?>;
let filteredContacts = [...allContacts];

const leadsConfig = {
    user_id: <?= $user_id ?>,
    ajax_url: '<?= admin_url('admin-ajax.php') ?>',
    nonce: '<?= wp_create_nonce('nfc_dashboard_nonce') ?>',
    vcard_options: <?= json_encode($vcard_options ?? []) ?>
};

// Fonctions de base - Version simplifiée
function showAddContactModal() {
    const modal = new bootstrap.Modal(document.getElementById('addContactModal'));
    modal.show();
}

function filterByVCard(vcardId) {
    const url = new URL(window.location);
    if (vcardId == 0) {
        url.searchParams.delete('vcard_filter');
    } else {
        url.searchParams.set('vcard_filter', vcardId);
    }
    window.location = url;
}

function showNotification(message, type = 'info') {
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

// Placeholder functions - À compléter selon besoins
function filterContacts() { /* TODO */ }
function clearSearch() { /* TODO */ }
function toggleSelectAll() { /* TODO */ }
function viewContact(id) { /* TODO */ }
function editContact(id) { /* TODO */ }
function deleteContact(id) { /* TODO */ }
function exportContacts(filter) { /* TODO */ }
function showImportModal() { /* TODO */ }

console.log('✅ Page Leads adaptative initialisée');
</script>