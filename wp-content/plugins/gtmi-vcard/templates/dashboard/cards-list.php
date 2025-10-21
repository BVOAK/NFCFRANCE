<?php
/**
 * Dashboard - Cards List
 * Gestion des cartes vCard utilisateur
 * Design cohérent avec leads.php
 */

// 1. VÉRIFICATIONS SÉCURITÉ
if (!defined('ABSPATH'))
    exit;
if (!is_user_logged_in())
    wp_redirect(home_url('/login'));

// 2. LOGIQUE MÉTIER
$user_id = get_current_user_id();

// Utiliser la fonction existante de la classe dashboard
global $nfc_dashboard_manager;
$user_vcards = $nfc_dashboard_manager->get_user_vcards($user_id);

/**
 * FONCTION HELPER : Compter les contacts d'une vCard (même méthode que leads.php)
 */
function get_vcard_contacts_count($vcard_id)
{
    global $wpdb;

    // Utiliser le même format sérialisé que dans api/lead/find.php
    $exact_pattern = 'a:1:{i:0;s:' . strlen($vcard_id) . ':"' . $vcard_id . '";}';

    $count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm 
            ON p.ID = pm.post_id 
            AND pm.meta_key = 'linked_virtual_card'
            AND pm.meta_value = %s
        WHERE p.post_type = 'lead'
        AND p.post_status = 'publish'
    ", $exact_pattern));

    return intval($count);
}

// Détection des états
if (empty($user_vcards)) {
    echo '<div class="content-header">';
    echo '<h1 class="h3 mb-1">Mes cartes NFC</h1>';
    echo '<p class="text-muted mb-0">Gestion de vos cartes vCard</p>';
    echo '</div>';

    echo '<div class="alert alert-info mt-4">';
    echo '<h5><i class="fas fa-info-circle me-2"></i>Aucune carte NFC configurée</h5>';
    echo '<p>Commandez vos premiers produits NFC pour commencer à utiliser votre dashboard.</p>';
    echo '<a href="' . home_url('/boutique-nfc/') . '" class="btn btn-primary">';
    echo '<i class="fas fa-shopping-cart me-2"></i>Commander mes cartes NFC';
    echo '</a>';
    echo '</div>';
    return;
}

// Interface selon nombre de vCards
$is_multi_cards = count($user_vcards) > 1;
$page_title = $is_multi_cards ?
    "Mes cartes NFC" :
    "Ma carte NFC";

// Calculer les vraies stats globales (comme dans statistics.php)
$global_stats = [
    'total_cards' => count($user_vcards),
    'configured_cards' => 0,
    'total_views' => 0,
    'total_contacts' => 0
];

// Enrichir les données des cartes avec vraies stats
$enriched_vcards = [];
foreach ($user_vcards as $vcard) {
    $firstname = get_post_meta($vcard->ID, 'firstname', true);
    $lastname = get_post_meta($vcard->ID, 'lastname', true);
    $job_title = get_post_meta($vcard->ID, 'job_title', true);
    $company = get_post_meta($vcard->ID, 'company', true);

    // Calculer les vraies stats par carte (utilise les fonctions mutualisées)
    $card_contact_count = nfc_get_vcard_contacts_count($vcard->ID);
    $card_views = nfc_get_vcard_total_views($vcard->ID);

    $is_configured = !empty($firstname);
    if ($is_configured) {
        $global_stats['configured_cards']++;
    }

    $global_stats['total_views'] += $card_views;
    $global_stats['total_contacts'] += $card_contact_count;

    $enriched_vcards[] = [
        'vcard' => $vcard,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'full_name' => trim($firstname . ' ' . $lastname),
        'job_title' => $job_title,
        'company' => $company,
        'is_configured' => $is_configured,
        'views' => $card_views,
        'contacts' => $card_contact_count,
        'identifier' => $nfc_dashboard_manager->get_card_identifier($vcard->ID) ?: 'NFC-' . $vcard->ID,
        'created_date' => get_the_date('d/m/Y', $vcard->ID)
    ];
}

// Configuration JavaScript
$cards_config = [
    'user_id' => $user_id,
    'vcards' => $enriched_vcards,
    'is_multi_cards' => $is_multi_cards,
    'global_stats' => $global_stats,
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce')
];

?>

<!-- CSS - même structure que leads.php -->
<link rel="stylesheet" href="<?= plugin_dir_url(__FILE__) ?>../../assets/css/cards-list.css">

<!-- Configuration JavaScript -->
<script>
    window.CARDS_CONFIG = <?= json_encode($cards_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>

<!-- HEADER - même structure que leads.php -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><?= esc_html($page_title) ?></h1>
            <p class="text-muted mb-0">
                <?= $is_multi_cards ? 'Gestion de votre parc de cartes NFC' : 'Gestion de votre carte NFC' ?>
            </p>
        </div>
        <?php if ($is_multi_cards): ?>
            <div class="d-md-flex d-none">
                <a href="<?= home_url('/boutique-nfc/') ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Commander plus
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- STATS CARDS - même design que leads.php -->
<?php if ($is_multi_cards): ?>
    <div class="row mb-4 d-md-flex d-none">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-id-card fa-2x"></i>
                    </div>
                    <h3 class="h4 mb-1"><?= $global_stats['total_cards'] ?></h3>
                    <p class="text-muted small mb-0">Total cartes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h3 class="h4 mb-1"><?= $global_stats['configured_cards'] ?></h3>
                    <p class="text-muted small mb-0">Configurées</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-eye fa-2x"></i>
                    </div>
                    <h3 class="h4 mb-1"><?= number_format($global_stats['total_views']) ?></h3>
                    <p class="text-muted small mb-0">Vues totales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-address-book fa-2x"></i>
                    </div>
                    <h3 class="h4 mb-1"><?= number_format($global_stats['total_contacts']) ?></h3>
                    <p class="text-muted small mb-0">Contacts générés</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- CONTENU PRINCIPAL - design inspiré de leads.php -->
<div class="dashboard-cards-main">

    <?php if ($is_multi_cards): ?>
        <!-- MODE MULTI-CARTES : Tableau style leads.php -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 d-md-block d-none">
                        <i class="fas fa-table me-2"></i>Liste des cartes (<?= count($user_vcards) ?>)
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="cardsSearch"
                                placeholder="Rechercher une carte...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <!-- Entêtes du tableau Bootstrap -->
                    <div class="d-none d-lg-block mb-3">
                        <div class="row bg-light border rounded-top py-3 fw-bold text-muted text-uppercase"
                            style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <div class="col-lg-2">Identifiant</div>
                            <div class="col-lg-3">Profil</div>
                            <div class="col-lg-2">Statut</div>
                            <div class="col-lg-3">Performances</div>
                            <div class="col-lg-2 text-center">Actions</div>
                        </div>
                    </div>

                    <!-- Container des cartes avec Bootstrap Grid -->
                    <div class="border border-top-0 rounded-bottom bg-white" id="cardsContainer">
                        <?php foreach ($enriched_vcards as $index => $card_data): ?>
                            <?php $vcard = $card_data['vcard']; ?>

                            <!-- Desktop: Row comme ligne de tableau -->
                            <div class="row border-bottom py-3 mx-0 card-fade-in d-none d-lg-flex align-items-center"
                                data-card-id="<?= $vcard->ID ?>" style="animation-delay: <?= $index * 100 ?>ms;">

                                <!-- Colonne Identifiant -->
                                <div class="col-lg-2">
                                    <div class="card-identifier"><?= esc_html($card_data['identifier']) ?></div>
                                    <small class="text-muted card-date">Créée le
                                        <?= esc_html($card_data['created_date']) ?></small>
                                </div>

                                <!-- Colonne Profil -->
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px; font-size: 0.9rem; font-weight: 600;">
                                            <?= esc_html(substr($card_data['firstname'] ?: 'N', 0, 1) . substr($card_data['lastname'] ?: 'A', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <a href="<?= '?page=vcard-edit&vcard_id=' . $vcard->ID ?>"
                                                class="profile-name-link">
                                                <div class="fw-medium">
                                                    <?= esc_html($card_data['full_name'] ?: 'Profil à configurer') ?>
                                                </div>
                                            </a>
                                            <?php if ($card_data['job_title'] || $card_data['company']): ?>
                                                <small class="text-muted">
                                                    <?= esc_html($card_data['job_title']) ?>
                                                    <?= $card_data['job_title'] && $card_data['company'] ? ' • ' : '' ?>
                                                    <?= esc_html($card_data['company']) ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">Configuration en attente</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Colonne Statut -->
                                <!-- <div class="col-lg-2">
                <span class="badge bg-<?= $card_data['is_configured'] ? 'success' : 'warning' ?> bg-opacity-10 text-<?= $card_data['is_configured'] ? 'success' : 'warning' ?>">
                    <i class="fas fa-<?= $card_data['is_configured'] ? 'check-circle' : 'exclamation-triangle' ?> me-1"></i>
                    <?= $card_data['is_configured'] ? 'Configurée' : 'À configurer' ?>
                </span>
                <br>
                <small class="text-muted mt-1">
                    <i class="fas fa-sync me-1"></i>Synchronisée
                </small>
            </div> -->

                                <!-- Colonne Performances -->
                                <div class="col-lg-2">
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <i class="fas fa-eye me-1"></i><?= number_format($card_data['views']) ?> vues
                                        </span>
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-address-book me-1"></i><?= number_format($card_data['contacts']) ?>
                                            contacts
                                        </span>
                                    </div>
                                </div>

                                <!-- Colonne Actions -->
                                <div class="col-lg-2 text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= '?page=vcard-edit&vcard_id=' . $vcard->ID ?>" class="btn btn-primary px-3"
                                            title="Configurer">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= get_permalink($vcard->ID) ?>" target="_blank"
                                            class="btn btn-outline-secondary" title="Aperçu public">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-secondary"
                                            onclick="cardsManager.showQRModal(<?= $vcard->ID ?>)" title="QR Code">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                        <a href="<?= '?page=statistics&vcard_id=' . $vcard->ID ?>" class="btn btn-outline-info"
                                            title="Statistiques">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        <a href="<?= '?page=contacts&vcard_id=' . $vcard->ID ?>" class="btn btn-outline-success"
                                            title="Contacts">
                                            <i class="fas fa-address-book"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile/Tablette: Card Bootstrap -->
                            <div class="card mb-3 mx-2 d-lg-none card-fade-in" data-card-id="<?= $vcard->ID ?>"
                                style="animation-delay: <?= $index * 100 ?>ms;">
                                <div class="card-body p-3">

                                    <!-- Header avec identifiant et sync -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <div class="card-identifier"><?= esc_html($card_data['identifier']) ?></div>
                                            <small class="text-muted card-date">Créée le
                                                <?= esc_html($card_data['created_date']) ?></small>
                                        </div>
                                        <small class="text-success d-flex align-items-center">
                                            <i class="fas fa-sync me-1"></i>Synchronisée
                                        </small>
                                    </div>

                                    <!-- Section profil -->
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                            style="width: 48px; height: 48px; font-size: 1.1rem; font-weight: 600;">
                                            <?= esc_html(substr($card_data['firstname'] ?: 'N', 0, 1) . substr($card_data['lastname'] ?: 'A', 0, 1)) ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <a href="<?= '?page=vcard-edit&vcard_id=' . $vcard->ID ?>"
                                                class="profile-name-link">
                                                <h6 class="mb-1">
                                                    <?= esc_html($card_data['full_name'] ?: 'Profil à configurer') ?>
                                                </h6>
                                            </a>
                                            <?php if ($card_data['job_title'] || $card_data['company']): ?>
                                                <small class="text-muted">
                                                    <?= esc_html($card_data['job_title']) ?>
                                                    <?= $card_data['job_title'] && $card_data['company'] ? ' • ' : '' ?>
                                                    <?= esc_html($card_data['company']) ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">Configuration en attente</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <!-- <div class="mb-3">
                                        <span
                                            class="badge bg-<?= $card_data['is_configured'] ? 'success' : 'warning' ?> bg-opacity-10 text-<?= $card_data['is_configured'] ? 'success' : 'warning' ?>">
                                            <i
                                                class="fas fa-<?= $card_data['is_configured'] ? 'check-circle' : 'exclamation-triangle' ?> me-1"></i>
                                            <?= $card_data['is_configured'] ? 'Configurée' : 'À configurer' ?>
                                        </span>
                                    </div> -->

                                    <!-- Métriques Bootstrap Grid -->
                                    <div class="d-flex text-center mb-3">
                                        <div class="col-4 p-1">
                                            <div class="border rounded p-2">
                                                <div class="h5 text-primary mb-0"><?= number_format($card_data['views']) ?>
                                                </div>
                                                <small class="text-muted text-uppercase"
                                                    style="font-size: 0.6rem; letter-spacing: 0.5px;">Vues</small>
                                            </div>
                                        </div>
                                        <div class="col-4 p-1">
                                            <div class="border rounded p-2">
                                                <div class="h5 text-success mb-0"><?= number_format($card_data['contacts']) ?>
                                                </div>
                                                <small class="text-muted text-uppercase"
                                                    style="font-size: 0.6rem; letter-spacing: 0.5px;">Contacts</small>
                                            </div>
                                        </div>
                                        <div class="col-4 p-1">
                                            <div class="border rounded p-2">
                                                <?php
                                                $conversion_rate = $card_data['views'] > 0 ? round(($card_data['contacts'] / $card_data['views']) * 100) : 0;
                                                ?>
                                                <div class="h5 text-info mb-0"><?= $conversion_rate ?>%</div>
                                                <small class="text-muted text-uppercase"
                                                    style="font-size: 0.6rem; letter-spacing: 0.5px;">Taux</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions Bootstrap Grid -->
                                    <div class="d-flex flex-wrap justify-content-around">
                                        <div class="col-12 p-1">
                                            <a href="<?= '?page=vcard-edit&vcard_id=' . $vcard->ID ?>"
                                                class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-edit me-2"></i>Configurer
                                            </a>
                                        </div>
                                        <div class="col-4 p-1">
                                            <a href="<?= get_permalink($vcard->ID) ?>" target="_blank"
                                                class="btn btn-outline-secondary w-100" title="Aperçu"
                                                <?= !$card_data['is_configured'] ? 'style="opacity: 0.5;"' : '' ?>>
                                                <i class="fas fa-eye me-1"></i>
                                            </a>
                                        </div>
                                        <!-- <div class="col-4">
                                            <button class="btn btn-outline-secondary w-100"
                                                onclick="cardsManager.showQRModal(<?= $vcard->ID ?>)" title="QR Code">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                        </div> -->
                                        <div class="col-4 p-1">
                                            <a href="<?= '?page=statistics&vcard_id=' . $vcard->ID ?>"
                                                class="btn btn-outline-info w-100" title="Stats" <?= !$card_data['is_configured'] ? 'style="opacity: 0.5;"' : '' ?>>
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                        </div>
                                        <div class="col-4 p-1">
                                            <a href="<?= '?page=contacts&vcard_id=' . $vcard->ID ?>"
                                                class="btn btn-outline-success w-100" title="Contacts"
                                                <?= !$card_data['is_configured'] ? 'style="opacity: 0.5;"' : '' ?>>
                                                <i class="fas fa-address-book"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- MODE CARTE UNIQUE : Vue détaillée style leads.php -->
    <?php $card_data = $enriched_vcards[0];
    $vcard = $card_data['vcard']; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                            style="width: 64px; height: 64px; font-size: 24px;">
                            <?= esc_html(substr($card_data['firstname'] ?: 'N', 0, 1) . substr($card_data['lastname'] ?: 'A', 0, 1)) ?>
                        </div>
                        <div>
                            <h4 class="mb-1 text-dark">
                                <?= esc_html($card_data['full_name'] ?: 'Profil à configurer') ?>
                            </h4>
                            <?php if ($card_data['job_title'] || $card_data['company']): ?>
                                <p class="text-muted mb-2">
                                    <?= esc_html($card_data['job_title']) ?>
                                    <?= $card_data['job_title'] && $card_data['company'] ? ' • ' : '' ?>
                                    <?= esc_html($card_data['company']) ?>
                                </p>
                            <?php endif; ?>
                            <div class="d-flex gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-eye me-1"></i><?= number_format($card_data['views']) ?> vues
                                </span>
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-address-book me-1"></i><?= number_format($card_data['contacts']) ?>
                                    contacts
                                </span>
                                <span
                                    class="badge bg-<?= $card_data['is_configured'] ? 'success' : 'warning' ?> bg-opacity-10 text-<?= $card_data['is_configured'] ? 'success' : 'warning' ?>">
                                    <?= $card_data['is_configured'] ? 'Configurée' : 'À configurer' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex flex-column gap-2">
                        <a href="<?= '?page=vcard-edit&vcard_id=' . $vcard->ID ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Configurer ma carte
                        </a>
                        <div class="btn-group">
                            <a href="<?= get_permalink($vcard->ID) ?>" target="_blank"
                                class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-eye me-1"></i>Aperçu
                            </a>
                            <button class="btn btn-outline-secondary btn-sm"
                                onclick="cardsManager.showQRModal(<?= $vcard->ID ?>)">
                                <i class="fas fa-qrcode me-1"></i>QR Code
                            </button>
                            <a href="<?= '?page=statistics&vcard_id=' . $vcard->ID ?>" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-chart-bar me-1"></i>Stats
                            </a>
                            <a href="<?= '?page=contacts&vcard_id=' . $vcard->ID ?>" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-address-book me-1"></i>Contacts
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

</div>

<!-- MODAL QR CODE - même style que leads.php -->
<div class="modal fade" id="qrCodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i>QR Code
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="qrCodeContent">
                <!-- Généré par JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="downloadQR">
                    <i class="fas fa-download me-2"></i>Télécharger
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="<?= plugin_dir_url(__FILE__) ?>../../assets/js/dashboard/cards-manager.js"></script>