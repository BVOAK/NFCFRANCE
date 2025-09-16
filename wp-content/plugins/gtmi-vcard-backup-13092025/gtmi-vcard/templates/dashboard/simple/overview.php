<?php
/**
 * Template: Vue d'ensemble Dashboard - VERSION SIMPLIFIÉE
 * 
 * Fichier: templates/dashboard/simple/overview.php
 * Page d'accueil simple et fonctionnelle
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables globales disponibles depuis le routing
global $nfc_vcard, $nfc_current_page;
$vcard = $nfc_vcard;

// Récupérer les données de la vCard
$vcard_id = $vcard->ID;
$user_id = get_current_user_id();
$current_user = wp_get_current_user();

// Récupérer les métadonnées vCard
$first_name = get_post_meta($vcard_id, 'first_name', true) ?: $current_user->first_name;
$last_name = get_post_meta($vcard_id, 'last_name', true) ?: $current_user->last_name;
$job_title = get_post_meta($vcard_id, 'job_title', true) ?: 'Utilisateur NFC';
$company = get_post_meta($vcard_id, 'company', true) ?: '';
$profile_image = get_post_meta($vcard_id, 'profile_image', true);

// Nom d'affichage
$display_name = trim($first_name . ' ' . $last_name) ?: $current_user->display_name ?: 'Utilisateur';

// URL publique de la vCard
$public_url = get_permalink($vcard_id);

// Configuration JavaScript
$overview_config = [
    'vcard_id' => $vcard_id,
    'api_url' => home_url('/wp-json/gtmi_vcard/v1/'),
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'public_url' => $public_url
];

?>

<div class="nfc-page-content">
    
    <!-- SALUTATION PERSONNALISÉE -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-primary bg-gradient text-white rounded-3 p-4">
                <h1 class="h3 mb-2">Bonjour <?php echo esc_html($display_name); ?></h1>
                <p class="mb-0 opacity-90">Bienvenue sur votre tableau de bord NFC France</p>
            </div>
        </div>
    </div>
    
    <!-- BLOC PRINCIPAL MA VCARD -->
    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="dashboard-card h-100">
                <div class="p-4 text-center">
                    <?php if ($profile_image): ?>
                        <img src="<?php echo esc_url($profile_image); ?>" 
                             alt="<?php echo esc_attr($display_name); ?>" 
                             class="rounded-circle mb-3" 
                             style="width: 100px; height: 100px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 100px; height: 100px;">
                            <span class="text-primary fw-bold fs-3">
                                <?php echo esc_html(strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1))); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <h5 class="mb-1"><?php echo esc_html($display_name); ?></h5>
                    <p class="text-muted mb-3"><?php echo esc_html($job_title); ?></p>
                    <?php if ($company): ?>
                        <p class="text-muted small mb-3"><?php echo esc_html($company); ?></p>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <a href="?page=vcard-edit" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>
                            Modifier ma vCard
                        </a>
                        <button onclick="window.open('<?php echo esc_js($public_url); ?>', '_blank')" 
                                class="btn btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>
                            Voir ma vCard
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- MES STATISTIQUES -->
        <div class="col-lg-8">
            <div class="dashboard-card h-100">
                <div class="card-header p-3 bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        Mes statistiques
                    </h5>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <!-- Collaborateur(s) -->
                        <div class="col-6">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <div class="text-success fs-4 fw-bold" id="stat-collaborators">-</div>
                                <small class="text-muted">Collaborateur(s)</small>
                            </div>
                        </div>
                        
                        <!-- Mobilité(s) -->
                        <div class="col-6">
                            <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                                <div class="text-info fs-4 fw-bold" id="stat-mobility">-</div>
                                <small class="text-muted">Mobilité(s)</small>
                            </div>
                        </div>
                        
                        <!-- Scans -->
                        <div class="col-6">
                            <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                <div class="text-warning fs-4 fw-bold" id="stat-scans">-</div>
                                <small class="text-muted">Scans</small>
                            </div>
                        </div>
                        
                        <!-- Échanges -->
                        <div class="col-6">
                            <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                                <div class="text-danger fs-4 fw-bold" id="stat-exchanges">-</div>
                                <small class="text-muted">Échanges</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="?page=statistics" class="btn btn-outline-primary w-100">
                            <i class="fas fa-chart-line me-2"></i>
                            Analyser mes statistiques
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- DERNIERS CONTACTS ET LIENS UTILES -->
    <div class="row">
        <!-- Mes dernières rencontres -->
        <div class="col-lg-8 mb-4">
            <div class="dashboard-card">
                <div class="card-header p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2 text-primary"></i>
                            Mes 3 dernières rencontres
                        </h5>
                        <a href="?page=contacts" class="btn btn-outline-primary btn-sm">
                            Afficher à mes rencontres
                        </a>
                    </div>
                </div>
                <div class="p-3">
                    <div id="recent-contacts-loading" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <p class="text-muted mt-2 mb-0">Chargement des contacts...</p>
                    </div>
                    
                    <div id="recent-contacts-list" style="display: none;">
                        <!-- Rempli par JavaScript -->
                    </div>
                    
                    <div id="no-recent-contacts" style="display: none;" class="text-center py-4">
                        <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune rencontre récente</p>
                        <small class="text-muted">Vos contacts apparaîtront ici après les premières interactions</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Liens utiles -->
        <div class="col-lg-4 mb-4">
            <div class="dashboard-card">
                <div class="card-header p-3 bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-link me-2 text-primary"></i>
                        Liens utiles
                    </h5>
                </div>
                <div class="p-3">
                    <div class="d-grid gap-2">
                        <a href="https://blog.nfc-france.com" target="_blank" class="btn btn-outline-primary btn-sm text-start">
                            <i class="fas fa-blog me-2"></i>
                            Comprendre mon résultat vCards
                        </a>
                        <a href="https://tutorials.nfc-france.com" target="_blank" class="btn btn-outline-primary btn-sm text-start">
                            <i class="fas fa-graduation-cap me-2"></i>
                            Tutoriels vidéos
                        </a>
                        <a href="mailto:support@nfc-france.com" class="btn btn-outline-primary btn-sm text-start">
                            <i class="fas fa-envelope me-2"></i>
                            Recevoir de l'aide
                        </a>
                        <a href="https://nfc-france.com/blog" target="_blank" class="btn btn-outline-primary btn-sm text-start">
                            <i class="fas fa-newspaper me-2"></i>
                            Découvrir le blog
                        </a>
                        <a href="mailto:hello@nfc-france.com" class="btn btn-outline-primary btn-sm text-start">
                            <i class="fas fa-paper-plane me-2"></i>
                            Contact par email
                        </a>
                        <a href="https://nfc-france.com/mentions-legales" target="_blank" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="fas fa-file-contract me-2"></i>
                            Sécurité avec vos données
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Configuration JavaScript -->
<script>
    // Configuration globale pour l'overview
    window.overviewConfig = <?php echo json_encode($overview_config); ?>;
    
    // Initialisation automatique
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Overview simplifié : Initialisation');
        initializeSimpleOverview();
    });
    
    /**
     * Initialiser l'overview simplifié
     */
    function initializeSimpleOverview() {
        console.log('📊 Chargement des données simplifiées...');
        
        // Charger les statistiques
        loadOverviewStats();
        
        // Charger les derniers contacts
        loadRecentContacts();
    }
    
    /**
     * Charger les statistiques d'overview
     */
    function loadOverviewStats() {
        // Vérifier que le module stats est disponible
        if (typeof window.NFCStatsCommons === 'undefined') {
            console.warn('⚠️ Module NFCStatsCommons non disponible, utilisation des valeurs par défaut');
            setDefaultStats();
            return;
        }
        
        const config = window.overviewConfig;
        
        // Charger les vraies stats
        window.NFCStatsCommons.loadRealStats(config.vcard_id, config.api_url)
            .then(statsData => {
                console.log('✅ Stats overview chargées:', statsData ? statsData.length : 0);
                
                const kpis = window.NFCStatsCommons.calculateKPIs(statsData || []);
                updateOverviewStats(kpis, statsData || []);
            })
            .catch(error => {
                console.error('❌ Erreur chargement stats overview:', error);
                setDefaultStats();
            });
    }
    
    /**
     * Mettre à jour les stats d'overview
     */
    function updateOverviewStats(kpis, statsData) {
        // Collaborateur(s) = visiteurs uniques
        document.getElementById('stat-collaborators').textContent = kpis.uniqueVisitors || 0;
        
        // Mobilité(s) = nombre de sources différentes
        const mobilityCount = kpis.topSources ? kpis.topSources.length : 0;
        document.getElementById('stat-mobility').textContent = mobilityCount;
        
        // Scans = total des vues
        document.getElementById('stat-scans').textContent = kpis.totalViews || 0;
        
        // Échanges = interactions
        document.getElementById('stat-exchanges').textContent = kpis.interactions || 0;
        
        console.log('📊 Stats overview mises à jour');
    }
    
    /**
     * Définir des stats par défaut
     */
    function setDefaultStats() {
        document.getElementById('stat-collaborators').textContent = '0';
        document.getElementById('stat-mobility').textContent = '0';
        document.getElementById('stat-scans').textContent = '0';
        document.getElementById('stat-exchanges').textContent = '0';
        
        console.log('📊 Stats par défaut appliquées');
    }
    
    /**
     * Charger les derniers contacts
     */
    function loadRecentContacts() {
        const config = window.overviewConfig;
        const loadingEl = document.getElementById('recent-contacts-loading');
        const listEl = document.getElementById('recent-contacts-list');
        const noContactsEl = document.getElementById('no-recent-contacts');
        
        // Appel API pour récupérer les contacts
        const contactsUrl = `${config.api_url}leads/${config.vcard_id}`;
        
        fetch(contactsUrl)
            .then(response => response.json())
            .then(data => {
                console.log('👥 Contacts récupérés:', data);
                
                if (loadingEl) loadingEl.style.display = 'none';
                
                if (data.success && data.data && data.data.length > 0) {
                    // Prendre les 3 derniers contacts
                    const recentContacts = data.data.slice(0, 3);
                    displayRecentContacts(recentContacts);
                    if (listEl) listEl.style.display = 'block';
                } else {
                    if (noContactsEl) noContactsEl.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('❌ Erreur chargement contacts:', error);
                if (loadingEl) loadingEl.style.display = 'none';
                if (noContactsEl) noContactsEl.style.display = 'block';
            });
    }
    
    /**
     * Afficher les derniers contacts
     */
    function displayRecentContacts(contacts) {
        const listEl = document.getElementById('recent-contacts-list');
        if (!listEl) return;
        
        const contactsHtml = contacts.map(contact => {
            const name = contact.name || 'Contact';
            const email = contact.email || '';
            const date = contact.created_at ? new Date(contact.created_at).toLocaleDateString('fr-FR') : '';
            
            return `
                <div class="d-flex align-items-center py-2 border-bottom">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                        <i class="fas fa-user text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-medium">${name}</div>
                        <small class="text-muted">${email}</small>
                    </div>
                    <small class="text-muted">${date}</small>
                </div>
            `;
        }).join('');
        
        listEl.innerHTML = contactsHtml;
        console.log('👥 Contacts affichés:', contacts.length);
    }
</script>