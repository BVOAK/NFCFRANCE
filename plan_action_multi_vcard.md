# Document 2/4 : Plan d'Action Technique
# Système Multi-cartes vCard Entreprise

## 🎯 **Objectif Développement**
Implémenter le système "1 commande = X cartes = X vCards individuelles" avec dashboard entreprise complet, en s'appuyant sur l'architecture gtmi-vcard existante.

---

## 📋 **Architecture Existante à Réutiliser**

### **Fonctions opérationnelles ✅**
```php
// Fonctions de base (à étendre)
gtmi_vcard_create_from_order($order_id)           // Base création 1 vCard
gtmi_vcard_random_slug()                           // URLs uniques
gtmi_vcard_generate_unique_url($vcard_id)          // URLs publiques  
gtmi_vcard_is_nfc_product($product_id)            // Détection produit NFC
gtmi_vcard_send_ready_notification()              // Emails

// Structure dashboard (à adapter)
wp-content/plugins/gtmi-vcard/templates/dashboard/simple/
├── overview.php                                  // Vue d'ensemble actuelle
├── vcard-edit.php                               // Édition vCard actuelle
├── contacts.php                                 // Contacts actuels
└── statistics.php                               // Stats actuelles
```

### **Extensions nécessaires 🆕**
```php
// Nouvelles fonctions principales
nfc_enterprise_create_multiple_vcards($order_id)  // Création multiple vCards
nfc_get_user_enterprise_cards($user_id)          // Récup toutes cartes utilisateur
nfc_get_vcard_by_identifier($identifier)         // Accès vCard par ID physique
nfc_renew_enterprise_card($identifier)           // Renouvellement individuel
```

---

## 🏗️ **Structure Fichiers de Développement**

### **Arborescence Proposée**
```
wp-content/plugins/gtmi-vcard/
├── includes/
│   ├── enterprise/                               # 🆕 Logique entreprise
│   │   ├── class-enterprise-manager.php          # Gestionnaire principal
│   │   ├── class-enterprise-cards.php            # Gestion cartes multiples
│   │   ├── enterprise-functions.php              # Fonctions utilitaires
│   │   └── enterprise-hooks.php                  # Hooks WooCommerce
│   │
│   ├── utils/
│   │   └── after_order.php                       # 🔄 Étendre logique existante
│   │
│   └── api/
│       └── rest-enterprise.php                   # 🆕 APIs REST entreprise
│
├── templates/dashboard/
│   ├── enterprise/                               # 🆕 Templates entreprise
│   │   ├── cards-overview.php                    # Page "Mes cartes"
│   │   ├── profile-selector.php                  # Grille sélection profils
│   │   ├── profile-editor.php                    # Édition profil individuel
│   │   ├── contacts-filtered.php                 # Contacts avec filtres
│   │   └── statistics-enterprise.php             # Stats entreprise
│   │
│   └── simple/ (existant)
│       └── [fichiers actuels à adapter]
│
├── assets/
│   ├── css/
│   │   ├── dashboard-enterprise.css              # 🆕 Styles entreprise
│   │   └── profile-selector.css                  # 🆕 Styles grille profils
│   │
│   └── js/
│       ├── enterprise-dashboard.js               # 🆕 Interactions dashboard
│       ├── profile-selector.js                   # 🆕 Sélection profils
│       └── enterprise-ajax.js                    # 🆕 Requêtes AJAX
│
└── database/
    ├── migrations/
    │   ├── 001-create-enterprise-cards-table.sql # Table liaison
    │   └── 002-extend-vcard-metadata.sql         # Métadonnées étendues
    │
    └── upgrade.php                               # 🆕 Script migration auto
```

---

## 📅 **Planning Développement Détaillé**

### **PHASE 1 : Architecture Base (2-3 jours)**

#### **JOUR 1 : Base de Données et Fonctions Core**

**Tâche 1.1 : Table entreprise et migration (3h)**
```sql
-- Prompt Développement 1.1
-- Créer table liaison entreprise + script migration

CREATE TABLE wp_nfc_enterprise_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    vcard_id INT NOT NULL,              -- Post virtual_card existant
    card_position INT NOT NULL,         -- 1,2,3... dans la commande  
    card_identifier VARCHAR(20) NOT NULL UNIQUE, -- NFC1023-1, NFC1023-2
    card_status ENUM('pending', 'configured', 'active', 'inactive') DEFAULT 'pending',
    company_name VARCHAR(200),
    main_user_id INT NOT NULL,          -- User WordPress qui a commandé
    renewal_orders TEXT,                -- JSON des commandes renouvellement  
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX (order_id),
    INDEX (main_user_id),  
    INDEX (card_identifier)
);

-- Script migration vCards existantes → format entreprise
-- IMPORTANT : Préserver URLs et fonctionnement actuel
UPDATE wp_postmeta SET meta_key = '_enterprise_order_id' 
WHERE meta_key = 'order_id' AND post_id IN (SELECT ID FROM wp_posts WHERE post_type = 'virtual_card');

-- Créer entrées enterprise_cards pour vCards existantes  
INSERT INTO wp_nfc_enterprise_cards (order_id, vcard_id, card_position, card_identifier, card_status, main_user_id)
SELECT 
    pm_order.meta_value,
    pm_order.post_id,
    1,  -- Position 1 car anciennes commandes = 1 carte
    CONCAT('NFC', pm_order.meta_value, '-1'),
    'active',
    pm_user.meta_value
FROM wp_postmeta pm_order
JOIN wp_postmeta pm_user ON pm_user.post_id = pm_order.post_id AND pm_user.meta_key = 'customer_id'  
WHERE pm_order.meta_key = 'order_id';
```

**Tâche 1.2 : Fonction création multiple vCards (4h)**
```php
// Prompt Développement 1.2
/*
Contexte : Modifier after_order.php pour créer X vCards selon quantité commandée.

Tâche :
1. Étendre gtmi_vcard_order_payment_success() pour détecter quantité
2. Créer nfc_enterprise_create_multiple_vcards($order_id)
3. Boucle création : pour chaque quantité, créer 1 vCard complète
4. Générer identifiants : NFC{order_id}-{position}
5. Sauvegarder liaison dans wp_nfc_enterprise_cards
6. Test avec commande quantité 5

Fonction principale à créer :
*/

function nfc_enterprise_create_multiple_vcards($order_id) {
    $order = wc_get_order($order_id);
    $created_vcards = [];
    
    foreach ($order->get_items() as $item) {
        if (gtmi_vcard_is_nfc_product($item->get_product_id())) {
            $quantity = $item->get_quantity();
            
            // Créer X vCards pour cette quantité
            for ($position = 1; $position <= $quantity; $position++) {
                $vcard_id = nfc_create_single_enterprise_vcard($order_id, $position, $order);
                if ($vcard_id) {
                    $created_vcards[] = $vcard_id;
                }
            }
        }
    }
    
    return $created_vcards;
}

function nfc_create_single_enterprise_vcard($order_id, $position, $order) {
    // 1. Créer identifiant physique unique
    $identifier = "NFC{$order_id}-{$position}";
    
    // 2. Créer post virtual_card (réutiliser logique existante)
    $vcard_data = [
        'post_title' => "vCard #{$position} - " . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . " - Commande #{$order_id}",
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'virtual_card',
        'post_author' => $order->get_customer_id() ?: 1,
    ];
    
    $vcard_id = wp_insert_post($vcard_data);
    if (is_wp_error($vcard_id)) {
        error_log("Error creating enterprise vCard: " . $vcard_id->get_error_message());
        return false;
    }
    
    // 3. Métadonnées vCard de base (reprendre existant)
    update_post_meta($vcard_id, 'firstname', $order->get_billing_first_name());
    update_post_meta($vcard_id, 'lastname', $order->get_billing_last_name());
    update_post_meta($vcard_id, 'email', $order->get_billing_email());
    update_post_meta($vcard_id, 'mobile', $order->get_billing_phone());
    update_post_meta($vcard_id, 'society', $order->get_billing_company());
    
    // 4. Métadonnées entreprise (nouvelles)
    update_post_meta($vcard_id, '_enterprise_order_id', $order_id);
    update_post_meta($vcard_id, '_enterprise_position', $position);
    update_post_meta($vcard_id, '_enterprise_identifier', $identifier);
    update_post_meta($vcard_id, '_enterprise_main_user', $order->get_customer_id());
    update_post_meta($vcard_id, '_enterprise_company', $order->get_billing_company());
    update_post_meta($vcard_id, '_card_configured', false); // À configurer
    
    // 5. URL unique (réutiliser système existant)
    $unique_url = gtmi_vcard_generate_unique_url($vcard_id);
    update_post_meta($vcard_id, 'unique_url', $unique_url);
    update_post_meta($vcard_id, 'status', 'pending');
    
    // 6. Liaison entreprise
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'nfc_enterprise_cards',
        [
            'order_id' => $order_id,
            'vcard_id' => $vcard_id,
            'card_position' => $position,
            'card_identifier' => $identifier,
            'card_status' => 'pending',
            'company_name' => $order->get_billing_company(),
            'main_user_id' => $order->get_customer_id()
        ]
    );
    
    error_log("NFC Enterprise: Created vCard $vcard_id with identifier $identifier");
    return $vcard_id;
}

/*
Validation :
Commande quantité 5 → 5 posts virtual_card + 5 entrées enterprise_cards + 5 identifiants uniques
*/
```

**Tâche 1.3 : Fonctions récupération données (2h)**
```php
// Prompt Développement 1.3
/*
Contexte : Fonctions pour récupérer cartes entreprise d'un utilisateur.

Tâche :
1. nfc_get_user_enterprise_cards($user_id) → toutes cartes de l'utilisateur
2. nfc_get_vcard_by_identifier($identifier) → vCard par ID physique
3. nfc_get_enterprise_stats($user_id) → stats consolidées
4. Tests avec données de migration

Fonctions à implémenter :
*/

function nfc_get_user_enterprise_cards($user_id) {
    global $wpdb;
    
    $query = "
        SELECT 
            ec.id,
            ec.card_identifier,
            ec.card_position,
            ec.card_status,
            ec.order_id,
            ec.company_name,
            vc.ID as vcard_id,
            vc.post_title,
            pm_firstname.meta_value as firstname,
            pm_lastname.meta_value as lastname,
            pm_post.meta_value as job_title,
            pm_url.meta_value as public_url
        FROM {$wpdb->prefix}nfc_enterprise_cards ec
        LEFT JOIN {$wpdb->posts} vc ON vc.ID = ec.vcard_id
        LEFT JOIN {$wpdb->postmeta} pm_firstname ON pm_firstname.post_id = vc.ID AND pm_firstname.meta_key = 'firstname'
        LEFT JOIN {$wpdb->postmeta} pm_lastname ON pm_lastname.post_id = vc.ID AND pm_lastname.meta_key = 'lastname'  
        LEFT JOIN {$wpdb->postmeta} pm_post ON pm_post.post_id = vc.ID AND pm_post.meta_key = 'post'
        LEFT JOIN {$wpdb->postmeta} pm_url ON pm_url.post_id = vc.ID AND pm_url.meta_key = 'unique_url'
        WHERE ec.main_user_id = %s
        ORDER BY ec.order_id DESC, ec.card_position ASC
    ";
    
    $results = $wpdb->get_results($wpdb->prepare($query, $user_id), ARRAY_A);
    
    // Enrichir avec stats rapides
    foreach ($results as &$card) {
        $stats = nfc_get_vcard_quick_stats($card['vcard_id']);
        $card['views_30d'] = $stats['views'] ?? 0;
        $card['contacts_30d'] = $stats['contacts'] ?? 0;
    }
    
    return $results;
}

function nfc_get_vcard_by_identifier($identifier) {
    global $wpdb;
    
    $vcard_id = $wpdb->get_var($wpdb->prepare(
        "SELECT vcard_id FROM {$wpdb->prefix}nfc_enterprise_cards WHERE card_identifier = %s",
        $identifier
    ));
    
    return $vcard_id ? get_post($vcard_id) : null;
}

/*
Validation :
nfc_get_user_enterprise_cards(42) retourne array avec toutes cartes user 42
nfc_get_vcard_by_identifier('NFC1023-3') retourne post virtual_card correct
*/
```

#### **JOUR 2 : Dashboard Entreprise Core**

**Tâche 2.1 : Page "Mes cartes" (4h)**
```php
// Prompt Développement 2.1
/*
Contexte : Créer page principale dashboard entreprise listant toutes les cartes.

Tâche :
1. Template templates/dashboard/enterprise/cards-overview.php
2. Interface tableau avec colonnes : Identifiant, Profil, Stats, Actions
3. Boutons actions par ligne : [Modifier] [Stats] [Leads] [Renouveler]  
4. Stats globales en header
5. Intégration dans routing dashboard existant

Interface cible :
┌─ MES CARTES ENTREPRISE ────────────────────────┐
│ 🏢 ACME Corp (8 cartes)                       │
│ 📊 456 vues totales | 89 contacts             │
│                                                │
│ IDENTIFIANT  PROFIL            STATUT  ACTIONS│
│ NFC1023-1    Jean Dupont       Actif   [●●●●] │
│              Commercial - 67v/23c              │
│ NFC1023-2    Marie Martin      Config  [●●●○] │  
│              RH - 45v/12c                      │
│ [...] autres lignes                           │
└────────────────────────────────────────────────┘

Template PHP :
*/

<?php
/**
 * Template : Page "Mes cartes" Dashboard Entreprise
 * Fichier : templates/dashboard/enterprise/cards-overview.php
 */

if (!defined('ABSPATH')) exit;

// Récupérer cartes utilisateur
$user_id = get_current_user_id();
$user_cards = nfc_get_user_enterprise_cards($user_id);
$company_name = !empty($user_cards[0]['company_name']) ? $user_cards[0]['company_name'] : 'Mon Entreprise';

// Stats globales
$global_stats = nfc_calculate_global_stats($user_cards);
?>

<div class="nfc-enterprise-dashboard">
    
    <!-- Header entreprise -->
    <div class="dashboard-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">
                <i class="fas fa-building me-2"></i>
                <?php echo esc_html($company_name); ?>
                <span class="badge bg-primary ms-2"><?php echo count($user_cards); ?> carte(s)</span>
            </h2>
            <a href="/boutique-nfc/?context=additional_cards" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-2"></i>Commander plus de cartes
            </a>
        </div>
        
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="stat-metric">
                        <div class="stat-value"><?php echo $global_stats['total_views']; ?></div>
                        <div class="stat-label">Vues totales</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-metric">
                        <div class="stat-value"><?php echo $global_stats['total_contacts']; ?></div>
                        <div class="stat-label">Contacts récupérés</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-metric">
                        <div class="stat-value"><?php echo $global_stats['active_cards']; ?></div>
                        <div class="stat-label">Cartes actives</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-metric">
                        <div class="stat-value"><?php echo $global_stats['top_performer']; ?></div>
                        <div class="stat-label">Top performer</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau cartes -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="h6 mb-0">Mes cartes NFC</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Identifiant</th>
                            <th>Profil vCard</th>
                            <th>Statut</th>
                            <th>Stats (30j)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_cards as $card): ?>
                        <tr data-card-id="<?php echo $card['vcard_id']; ?>">
                            <td>
                                <div class="fw-bold text-primary"><?php echo esc_html($card['card_identifier']); ?></div>
                                <small class="text-muted">Commande #<?php echo $card['order_id']; ?></small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-placeholder me-2">
                                        <?php echo substr($card['firstname'] ?: 'N', 0, 1) . substr($card['lastname'] ?: 'C', 0, 1); ?>
                                    </div>
                                    <div>
                                        <div class="fw-medium">
                                            <?php echo esc_html(($card['firstname'] ?: '') . ' ' . ($card['lastname'] ?: '')); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo esc_html($card['job_title'] ?: 'Poste non défini'); ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $status_class = match($card['card_status']) {
                                    'pending' => 'bg-warning',
                                    'configured' => 'bg-info',
                                    'active' => 'bg-success',
                                    'inactive' => 'bg-secondary',
                                    default => 'bg-light text-dark'
                                };
                                $status_label = match($card['card_status']) {
                                    'pending' => 'À configurer',
                                    'configured' => 'Configuré',
                                    'active' => 'Actif',
                                    'inactive' => 'Inactif',
                                    default => 'Non défini'
                                };
                                ?>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php echo $status_label; ?>
                                </span>
                            </td>
                            <td>
                                <div class="stats-mini">
                                    <div><i class="fas fa-eye text-primary me-1"></i><?php echo $card['views_30d']; ?></div>
                                    <div><i class="fas fa-address-book text-success me-1"></i><?php echo $card['contacts_30d']; ?></div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editVcard(<?php echo $card['vcard_id']; ?>)">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <button class="btn btn-outline-info" onclick="viewStats(<?php echo $card['vcard_id']; ?>)">
                                        <i class="fas fa-chart-bar"></i> Stats  
                                    </button>
                                    <button class="btn btn-outline-success" onclick="viewContacts(<?php echo $card['vcard_id']; ?>)">
                                        <i class="fas fa-address-book"></i> Leads
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="renewCard('<?php echo $card['card_identifier']; ?>')">
                                        <i class="fas fa-refresh"></i> Renouveler
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function editVcard(vcardId) {
    window.location.href = `?page=profile-editor&vcard_id=${vcardId}`;
}

function viewStats(vcardId) {
    window.location.href = `?page=statistics&vcard_id=${vcardId}`;
}

function viewContacts(vcardId) {
    window.location.href = `?page=contacts&vcard_id=${vcardId}`;
}

function renewCard(identifier) {
    // Redirection boutique avec paramètres renouvellement
    window.location.href = `/boutique-nfc/?action=renew&identifier=${identifier}`;
}
</script>

/*
Validation :
Page affiche toutes cartes utilisateur, boutons fonctionnels, stats correctes.
*/
```

**Tâche 2.2 : Grille sélection profils (3h)**
```php
// Prompt Développement 2.2
/*
Contexte : Page sélection visuelle des profils à modifier (remplace "Modifier ma carte").

Tâche :
1. Template templates/dashboard/enterprise/profile-selector.php
2. Grille cartes avec photos/noms/stats
3. Bouton [Modifier] par carte → redirection vers éditeur
4. Responsive design mobile/desktop  
5. États visuels selon statut carte

Interface cible :
┌─ CHOISIR PROFIL À MODIFIER ────────────────────┐
│ ┌────────────┐ ┌────────────┐ ┌────────────┐  │
│ │ [Photo JD] │ │ [Photo MM] │ │ [Avatar PD]│  │
│ │ Jean       │ │ Marie      │ │ Paul       │  │
│ │ Dupont     │ │ Martin     │ │ Durand     │  │
│ │ Commercial │ │ RH Manager │ │ À configurer│  │
│ │ 67 vues    │ │ 45 vues    │ │ 0 vue      │  │
│ │ [Modifier] │ │ [Modifier] │ │ [Configurer]│  │
│ └────────────┘ └────────────┘ └────────────┘  │
└────────────────────────────────────────────────┘
*/

<?php
/**
 * Template : Sélection Profils vCard
 * Fichier : templates/dashboard/enterprise/profile-selector.php
 */

if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$user_cards = nfc_get_user_enterprise_cards($user_id);
?>

<div class="nfc-profile-selector">
    
    <div class="page-header mb-4">
        <h2 class="h4 mb-2">
            <i class="fas fa-users me-2"></i>
            Choisir le profil à modifier
        </h2>
        <p class="text-muted">Sélectionnez la carte que vous souhaitez configurer ou modifier.</p>
    </div>

    <div class="profiles-grid">
        <?php foreach ($user_cards as $card): ?>
        <div class="profile-card <?php echo $card['card_status']; ?>" data-vcard-id="<?php echo $card['vcard_id']; ?>">
            
            <!-- Photo profil -->
            <div class="profile-avatar">
                <?php 
                $profile_picture = get_post_meta($card['vcard_id'], 'profile_picture', true);
                if ($profile_picture): ?>
                    <img src="<?php echo esc_url($profile_picture); ?>" alt="Photo <?php echo esc_attr($card['firstname']); ?>">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?php echo substr($card['firstname'] ?: 'N', 0, 1) . substr($card['lastname'] ?: 'C', 0, 1); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Infos profil -->
            <div class="profile-info">
                <h3 class="profile-name">
                    <?php echo esc_html(($card['firstname'] ?: 'Prénom') . ' ' . ($card['lastname'] ?: 'Nom')); ?>
                </h3>
                <div class="profile-job">
                    <?php echo esc_html($card['job_title'] ?: 'Poste à définir'); ?>
                </div>
                <div class="profile-identifier">
                    <?php echo esc_html($card['card_identifier']); ?>
                </div>
            </div>
            
            <!-- Stats rapides -->
            <div class="profile-stats">
                <span class="stat-item">
                    <i class="fas fa-eye"></i> <?php echo $card['views_30d']; ?> vue(s)
                </span>
                <span class="stat-item">
                    <i class="fas fa-address-book"></i> <?php echo $card['contacts_30d']; ?> contact(s)
                </span>
            </div>
            
            <!-- Statut badge -->
            <div class="profile-status">
                <?php
                $status_info = match($card['card_status']) {
                    'pending' => ['class' => 'status-pending', 'label' => 'À configurer', 'icon' => 'fas fa-cog'],
                    'configured' => ['class' => 'status-configured', 'label' => 'Configuré', 'icon' => 'fas fa-check-circle'],
                    'active' => ['class' => 'status-active', 'label' => 'Actif', 'icon' => 'fas fa-check-circle'],
                    'inactive' => ['class' => 'status-inactive', 'label' => 'Inactif', 'icon' => 'fas fa-pause-circle'],
                    default => ['class' => 'status-unknown', 'label' => 'Non défini', 'icon' => 'fas fa-question-circle']
                };
                ?>
                <span class="status-badge <?php echo $status_info['class']; ?>">
                    <i class="<?php echo $status_info['icon']; ?>"></i>
                    <?php echo $status_info['label']; ?>
                </span>
            </div>
            
            <!-- Action button -->
            <div class="profile-action">
                <?php if ($card['card_status'] === 'pending'): ?>
                    <button class="btn btn-primary btn-configure" onclick="configureProfile(<?php echo $card['vcard_id']; ?>)">
                        <i class="fas fa-cog me-2"></i>Configurer
                    </button>
                <?php else: ?>
                    <button class="btn btn-outline-primary btn-edit" onclick="editProfile(<?php echo $card['vcard_id']; ?>)">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </button>
                <?php endif; ?>
            </div>
            
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function configureProfile(vcardId) {
    window.location.href = `?page=profile-editor&vcard_id=${vcardId}&mode=configure`;
}

function editProfile(vcardId) {
    window.location.href = `?page=profile-editor&vcard_id=${vcardId}&mode=edit`;
}
</script>

/*
Validation :
Grille affiche toutes cartes, boutons rediriger correctement vers éditeur individuel.
*/
```

**Tâche 2.3 : Éditeur profil individuel (4h)**
```php
// Prompt Développement 2.3
/*
Contexte : Page édition d'une vCard individuelle avec aperçu temps réel.

Tâche :
1. Template templates/dashboard/enterprise/profile-editor.php
2. Formulaire complet reprenant vcard-edit.php actuel
3. Aperçu carte temps réel côté droit
4. Sauvegarde AJAX sans rechargement
5. Navigation retour vers sélecteur

Interface cible :
┌─ MODIFIER PROFIL : JEAN DUPONT ────────────────┐
│ [← Retour liste] [Aperçu public] [Enregistrer] │
│                                                │
│ ┌─ FORMULAIRE ──┐ ┌─ APERÇU TEMPS RÉEL ─────┐ │
│ │ Prénom: Jean  │ │ ┌─ Carte virtuelle ──┐  │ │
│ │ Nom: Dupont   │ │ │ [Photo] Jean Dupont │  │ │
│ │ Email: ...    │ │ │ Commercial          │  │ │
│ │ Téléphone: ..│ │ │ 📞 0123456789      │  │ │
│ │ [...] autres  │ │ │ 📧 jean@acme.com   │  │ │  
│ │ champs        │ │ └─────────────────────┘  │ │
│ └───────────────┘ └──────────────────────────┘ │
└────────────────────────────────────────────────┘

Réutiliser maximum template vcard-edit.php existant.
*/

<?php
/**
 * Template : Éditeur Profil vCard Individuel  
 * Fichier : templates/dashboard/enterprise/profile-editor.php
 */

if (!defined('ABSPATH')) exit;

// Récupérer vCard à éditer
$vcard_id = isset($_GET['vcard_id']) ? intval($_GET['vcard_id']) : 0;
$mode = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'edit';

if (!$vcard_id) {
    wp_redirect(add_query_arg('page', 'profile-selector'));
    exit;
}

// Vérifier propriété
$user_id = get_current_user_id(); 
$card_info = nfc_get_card_info($vcard_id);
if (!$card_info || $card_info['main_user_id'] != $user_id) {
    wp_die('Accès non autorisé');
}

// Récupérer données vCard (reprendre logique vcard-edit.php)
$vcard_fields = [
    'firstname' => get_post_meta($vcard_id, 'firstname', true) ?: '',
    'lastname' => get_post_meta($vcard_id, 'lastname', true) ?: '',
    'society' => get_post_meta($vcard_id, 'society', true) ?: '',
    'post' => get_post_meta($vcard_id, 'post', true) ?: '',
    'email' => get_post_meta($vcard_id, 'email', true) ?: '',
    'mobile' => get_post_meta($vcard_id, 'mobile', true) ?: '',
    'phone' => get_post_meta($vcard_id, 'phone', true) ?: '',
    'website' => get_post_meta($vcard_id, 'website', true) ?: '',
    'linkedin' => get_post_meta($vcard_id, 'linkedin', true) ?: '',
    'description' => get_post_meta($vcard_id, 'description', true) ?: '',
    'profile_picture' => get_post_meta($vcard_id, 'profile_picture', true) ?: ''
];
?>

<div class="nfc-profile-editor">
    
    <!-- Header navigation -->
    <div class="editor-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <a href="?page=profile-selector" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                </a>
                <h2 class="h4 mb-0">
                    <?php echo $mode === 'configure' ? 'Configurer profil' : 'Modifier profil'; ?> : 
                    <span class="text-primary"><?php echo esc_html($card_info['card_identifier']); ?></span>
                </h2>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo get_permalink($vcard_id); ?>" target="_blank" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-external-link-alt me-2"></i>Aperçu public
                </a>
                <button type="submit" form="vcard-form" class="btn btn-primary btn-sm">
                    <i class="fas fa-save me-2"></i>Enregistrer
                </button>
            </div>
        </div>
    </div>

    <!-- Layout formulaire + aperçu -->
    <div class="row">
        
        <!-- Formulaire (col gauche) -->
        <div class="col-lg-8">
            <form id="vcard-form" class="vcard-form">
                <input type="hidden" name="vcard_id" value="<?php echo $vcard_id; ?>">
                <input type="hidden" name="action" value="nfc_save_vcard_profile">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('nfc_save_vcard'); ?>">
                
                <!-- Section Informations personnelles -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-user me-2"></i>
                            Informations personnelles
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Prénom *</label>
                                <input type="text" name="firstname" class="form-control" 
                                       value="<?php echo esc_attr($vcard_fields['firstname']); ?>"
                                       onchange="updatePreview()" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Nom *</label>
                                <input type="text" name="lastname" class="form-control"
                                       value="<?php echo esc_attr($vcard_fields['lastname']); ?>"
                                       onchange="updatePreview()" required>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Entreprise</label>
                                <input type="text" name="society" class="form-control"
                                       value="<?php echo esc_attr($vcard_fields['society']); ?>"
                                       onchange="updatePreview()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Poste / Fonction</label>
                                <input type="text" name="post" class="form-control"
                                       value="<?php echo esc_attr($vcard_fields['post']); ?>"
                                       onchange="updatePreview()">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Contact -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-phone me-2"></i>
                            Informations de contact
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Email *</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?php echo esc_attr($vcard_fields['email']); ?>"
                                       onchange="updatePreview()" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Téléphone mobile</label>
                                <input type="tel" name="mobile" class="form-control"
                                       value="<?php echo esc_attr($vcard_fields['mobile']); ?>"
                                       onchange="updatePreview()">
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Téléphone fixe</label>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?php echo esc_attr($vcard_fields['phone']); ?>"
                                       onchange="updatePreview()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Site web</label>
                                <input type="url" name="website" class="form-control"
                                       value="<?php echo esc_attr($vcard_fields['website']); ?>"
                                       onchange="updatePreview()">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Réseaux sociaux -->  
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-share-alt me-2"></i>
                            Réseaux sociaux
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">LinkedIn</label>
                                <input type="url" name="linkedin" class="form-control"
                                       value="<?php echo esc_attr($vcard_fields['linkedin']); ?>"
                                       placeholder="https://linkedin.com/in/votre-profil"
                                       onchange="updatePreview()">
                            </div>
                            <!-- Autres réseaux sociaux... -->
                        </div>
                    </div>
                </div>

                <!-- Section Description -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-align-left me-2"></i>
                            Description / Bio
                        </h3>
                    </div>
                    <div class="card-body">
                        <textarea name="description" class="form-control" rows="4"
                                  onchange="updatePreview()"
                                  placeholder="Présentez-vous en quelques lignes..."><?php echo esc_textarea($vcard_fields['description']); ?></textarea>
                    </div>
                </div>

            </form>
        </div>
        
        <!-- Aperçu temps réel (col droite) -->
        <div class="col-lg-4">
            <div class="preview-sticky">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-eye me-2"></i>
                            Aperçu temps réel
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="vcard-preview" class="vcard-preview-container">
                            <!-- Aperçu vCard généré dynamiquement -->
                            <div class="vcard-preview">
                                <div class="vcard-header">
                                    <div class="vcard-avatar">
                                        <?php if ($vcard_fields['profile_picture']): ?>
                                            <img src="<?php echo esc_url($vcard_fields['profile_picture']); ?>" alt="Photo profil">
                                        <?php else: ?>
                                            <div class="avatar-placeholder" id="preview-avatar">
                                                <?php echo substr($vcard_fields['firstname'] ?: 'P', 0, 1) . substr($vcard_fields['lastname'] ?: 'N', 0, 1); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vcard-identity">
                                        <h4 id="preview-name"><?php echo esc_html(($vcard_fields['firstname'] ?: 'Prénom') . ' ' . ($vcard_fields['lastname'] ?: 'Nom')); ?></h4>
                                        <div id="preview-job" class="vcard-job"><?php echo esc_html($vcard_fields['post'] ?: 'Poste'); ?></div>
                                        <div id="preview-company" class="vcard-company"><?php echo esc_html($vcard_fields['society'] ?: 'Entreprise'); ?></div>
                                    </div>
                                </div>
                                
                                <div class="vcard-contact">
                                    <div id="preview-email" class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo esc_html($vcard_fields['email'] ?: 'email@exemple.com'); ?></span>
                                    </div>
                                    <div id="preview-mobile" class="contact-item">
                                        <i class="fas fa-mobile-alt"></i>
                                        <span><?php echo esc_html($vcard_fields['mobile'] ?: '0123456789'); ?></span>
                                    </div>
                                    <?php if ($vcard_fields['website']): ?>
                                    <div id="preview-website" class="contact-item">
                                        <i class="fas fa-globe"></i>
                                        <span><?php echo esc_html($vcard_fields['website']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($vcard_fields['description']): ?>
                                <div class="vcard-description">
                                    <p id="preview-description"><?php echo esc_html($vcard_fields['description']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Infos techniques -->
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <strong>Identifiant :</strong> <?php echo esc_html($card_info['card_identifier']); ?><br>
                                <strong>URL publique :</strong> <a href="<?php echo get_permalink($vcard_id); ?>" target="_blank" class="text-decoration-none">Ouvrir</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
// Mise à jour aperçu temps réel
function updatePreview() {
    const firstname = document.querySelector('input[name="firstname"]').value || 'Prénom';
    const lastname = document.querySelector('input[name="lastname"]').value || 'Nom';
    const post = document.querySelector('input[name="post"]').value || 'Poste';
    const society = document.querySelector('input[name="society"]').value || 'Entreprise';
    const email = document.querySelector('input[name="email"]').value || 'email@exemple.com';
    const mobile = document.querySelector('input[name="mobile"]').value || '0123456789';
    const description = document.querySelector('textarea[name="description"]').value;
    
    // Mise à jour éléments preview
    document.getElementById('preview-name').textContent = `${firstname} ${lastname}`;
    document.getElementById('preview-job').textContent = post;
    document.getElementById('preview-company').textContent = society;
    document.getElementById('preview-email').querySelector('span').textContent = email;
    document.getElementById('preview-mobile').querySelector('span').textContent = mobile;
    document.getElementById('preview-description').textContent = description;
    
    // Mise à jour avatar initiales
    const avatar = document.getElementById('preview-avatar');
    if (avatar) {
        avatar.textContent = firstname.charAt(0) + lastname.charAt(0);
    }
}

// Sauvegarde AJAX
document.getElementById('vcard-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.querySelector('button[type="submit"]');
    
    // Loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sauvegarde...';
    
    fetch(window.nfcConfig.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success feedback
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Sauvegardé !';
            
            // Marquer carte comme configurée
            if (data.data.status_changed) {
                setTimeout(() => {
                    window.location.href = '?page=profile-selector&configured=' + formData.get('vcard_id');
                }, 1500);
            }
        } else {
            throw new Error(data.data || 'Erreur de sauvegarde');
        }
    })
    .catch(error => {
        alert('Erreur : ' + error.message);
    })
    .finally(() => {
        // Reset button
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-primary');
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
        }, 2000);
    });
});
</script>

/*
Validation :
Formulaire complet fonctionnel, aperçu temps réel, sauvegarde AJAX, navigation fluide.
*/
```

#### **JOUR 3 : Adaptation Dashboard Existant**

**Tâche 3.1 : Adaptation contacts avec filtres (3h)**
```php
// Prompt Développement 3.1
/*
Contexte : Modifier page contacts pour filtrer par profil vCard d'origine.

Tâche :
1. Modifier template contacts.php pour multi-cartes
2. Ajouter colonne "Profil vCard lié" 
3. Filtres par profil dans dropdown
4. Export CSV par profil ou global
5. Statistiques contacts par profil

Interface cible :
┌─ MES CONTACTS REÇUS ───────────────────────────┐
│ Filtre : [Tous les profils ▼] [Export CSV ▼]  │
│          ├ Tous (89 contacts)                  │
│          ├ Jean Dupont (34 contacts)           │
│          ├ Marie Martin (28 contacts)          │
│          └ Paul Durand (27 contacts)           │
│                                                │
│ NOM           EMAIL           PROFIL    DATE   │
│ Client Pro    client@test.fr  Jean D.   12/01  │
│ Lead Qualifié lead@acme.com   Marie M.  11/01  │  
└────────────────────────────────────────────────┘
*/

<?php
/**
 * Template : Contacts Multi-profils
 * Fichier : templates/dashboard/enterprise/contacts-filtered.php  
 */

if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$user_cards = nfc_get_user_enterprise_cards($user_id);
$selected_vcard = isset($_GET['vcard_filter']) ? intval($_GET['vcard_filter']) : 0;

// Récupérer contacts (avec filtre si sélectionné)
$contacts = nfc_get_enterprise_contacts($user_id, $selected_vcard);
$contacts_by_profile = nfc_group_contacts_by_profile($contacts);
?>

<div class="nfc-enterprise-contacts">
    
    <!-- Header avec filtres -->
    <div class="contacts-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">
                <i class="fas fa-address-book me-2"></i>
                Mes contacts reçus
                <span class="badge bg-primary ms-2"><?php echo count($contacts); ?> contact(s)</span>
            </h2>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <select class="form-select" onchange="filterContacts(this.value)">
                        <option value="0" <?php selected($selected_vcard, 0); ?>>
                            Tous les profils (<?php echo count($contacts); ?>)
                        </option>
                        <?php foreach ($user_cards as $card): ?>
                        <?php $card_contacts = $contacts_by_profile[$card['vcard_id']] ?? []; ?>
                        <option value="<?php echo $card['vcard_id']; ?>" <?php selected($selected_vcard, $card['vcard_id']); ?>>
                            <?php echo esc_html($card['firstname'] . ' ' . $card['lastname']); ?>
                            (<?php echo count($card_contacts); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?action=export_contacts&format=csv&vcard=<?php echo $selected_vcard; ?>">
                            <i class="fas fa-file-csv me-2"></i>CSV Sélection actuelle
                        </a></li>
                        <li><a class="dropdown-item" href="?action=export_contacts&format=csv&vcard=0">
                            <i class="fas fa-file-csv me-2"></i>CSV Tous profils
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats rapides par profil -->
    <?php if ($selected_vcard == 0): ?>
    <div class="contacts-stats mb-4">
        <div class="row">
            <?php foreach ($user_cards as $card): ?>
            <?php $card_contacts = $contacts_by_profile[$card['vcard_id']] ?? []; ?>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($card_contacts); ?></div>
                    <div class="stat-label"><?php echo esc_html($card['firstname'] . ' ' . $card['lastname']); ?></div>
                    <div class="stat-trend">
                        <?php 
                        $trend = nfc_get_contacts_trend($card['vcard_id'], 7);
                        echo $trend > 0 ? "+{$trend} cette semaine" : "Aucun nouveau";
                        ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tableau contacts -->
    <div class="dashboard-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Contact</th>
                            <th>Coordonnées</th>
                            <th>Profil vCard</th>
                            <th>Date de capture</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?php echo esc_html($contact['firstname'] . ' ' . $contact['lastname']); ?></div>
                                <?php if ($contact['society']): ?>
                                <small class="text-muted"><?php echo esc_html($contact['society']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?php echo esc_html($contact['email']); ?></div>
                                <?php if ($contact['mobile']): ?>
                                <small class="text-muted"><?php echo esc_html($contact['mobile']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-mini me-2">
                                        <?php echo substr($contact['vcard_firstname'] ?: 'N', 0, 1) . substr($contact['vcard_lastname'] ?: 'C', 0, 1); ?>
                                    </div>
                                    <div>
                                        <div class="fw-medium"><?php echo esc_html($contact['vcard_firstname'] . ' ' . $contact['vcard_lastname']); ?></div>
                                        <small class="text-muted"><?php echo esc_html($contact['vcard_identifier']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo date('d/m/Y', strtotime($contact['created_at'])); ?></div>
                                <small class="text-muted"><?php echo date('H:i', strtotime($contact['created_at'])); ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewContactDetail(<?php echo $contact['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="exportContact(<?php echo $contact['id']; ?>)">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterContacts(vcardId) {
    const currentUrl = new URL(window.location);
    if (vcardId > 0) {
        currentUrl.searchParams.set('vcard_filter', vcardId);
    } else {
        currentUrl.searchParams.delete('vcard_filter');
    }
    window.location.href = currentUrl.toString();
}

function viewContactDetail(contactId) {
    // Modal ou page détail contact
    // À implémenter selon besoins
}

function exportContact(contactId) {
    window.location.href = `?action=export_contact&id=${contactId}&format=vcard`;
}
</script>

/*
Validation :
Filtrage par profil fonctionne, colonne profil affichée, export par profil opérationnel.
*/
```

**Tâche 3.2 : Statistiques entreprise avec sélecteur (3h)**
```php  
// Prompt Développement 3.2
/*
Contexte : Page statistiques avec sélecteur profil + stats globales.

Tâche :
1. Modifier template statistics.php pour multi-profils
2. Sélecteur dropdown "Voir stats de : [Profil]"
3. Mode "Stats globales" = consolidation tous profils
4. Mode "Profil individuel" = stats du profil sélectionné
5. Graphiques Chart.js adaptatifs

Interface cible :
┌─ STATISTIQUES ENTREPRISE ──────────────────────┐
│ Vue : [Stats globales ▼] Période : [30j ▼]     │
│       ├ Stats globales (tous)                  │
│       ├ Jean Dupont                            │  
│       └ Marie Martin                           │
│                                                │
│ 📊 GRAPHIQUES SELON SÉLECTION                 │
│ [Graphique barres si global / Ligne si individuel]│
└────────────────────────────────────────────────┘
*/

<?php
/**
 * Template : Statistiques Multi-profils  
 * Fichier : templates/dashboard/enterprise/statistics-enterprise.php
 */

if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$user_cards = nfc_get_user_enterprise_cards($user_id);
$selected_vcard = isset($_GET['vcard_stats']) ? intval($_GET['vcard_stats']) : 0;
$period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : '30d';

// Récupérer données selon sélection
if ($selected_vcard > 0) {
    $stats_data = nfc_get_vcard_detailed_stats($selected_vcard, $period);
    $selected_card = array_filter($user_cards, fn($card) => $card['vcard_id'] == $selected_vcard)[0] ?? null;
} else {
    $stats_data = nfc_get_enterprise_global_stats($user_id, $period);
    $selected_card = null;
}
?>

<div class="nfc-enterprise-statistics">
    
    <!-- Header contrôles -->
    <div class="stats-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">
                <i class="fas fa-chart-bar me-2"></i>
                Statistiques et analytics
            </h2>
            <div class="d-flex gap-3">
                <!-- Sélecteur profil -->
                <select class="form-select" onchange="changeStatsView(this.value)" style="min-width: 200px;">
                    <option value="0" <?php selected($selected_vcard, 0); ?>>
                        📊 Stats globales (tous profils)
                    </option>
                    <?php foreach ($user_cards as $card): ?>
                    <option value="<?php echo $card['vcard_id']; ?>" <?php selected($selected_vcard, $card['vcard_id']); ?>>
                        👤 <?php echo esc_html($card['firstname'] . ' ' . $card['lastname']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <!-- Sélecteur période -->
                <select class="form-select" onchange="changePeriod(this.value)" style="min-width: 150px;">
                    <option value="7d" <?php selected($period, '7d'); ?>>7 derniers jours</option>
                    <option value="30d" <?php selected($period, '30d'); ?>>30 derniers jours</option>
                    <option value="90d" <?php selected($period, '90d'); ?>>3 derniers mois</option>
                    <option value="1y" <?php selected($period, '1y'); ?>>12 derniers mois</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Titre contextuel -->
    <div class="stats-context mb-4">
        <?php if ($selected_vcard > 0): ?>
        <div class="alert alert-info">
            <i class="fas fa-user me-2"></i>
            Statistiques de <strong><?php echo esc_html($selected_card['firstname'] . ' ' . $selected_card['lastname']); ?></strong>
            (<?php echo esc_html($selected_card['card_identifier']); ?>)
        </div>
        <?php else: ?>
        <div class="alert alert-primary">
            <i class="fas fa-chart-bar me-2"></i>
            Statistiques consolidées de <strong>toutes vos cartes</strong> (<?php echo count($user_cards); ?> profils)
        </div>
        <?php endif; ?>
    </div>

    <!-- KPIs Header -->
    <div class="stats-kpis mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon">
                        <i class="fas fa-eye text-primary"></i>
                    </div>
                    <div class="kpi-content">
                        <div class="kpi-value"><?php echo number_format($stats_data['total_views']); ?></div>
                        <div class="kpi-label">Vues totales</div>
                        <div class="kpi-change <?php echo $stats_data['views_trend'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $stats_data['views_trend'] >= 0 ? '+' : ''; ?><?php echo $stats_data['views_trend']; ?>%
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon">
                        <i class="fas fa-address-book text-success"></i>
                    </div>
                    <div class="kpi-content">
                        <div class="kpi-value"><?php echo number_format($stats_data['total_contacts']); ?></div>
                        <div class="kpi-label">Contacts récupérés</div>
                        <div class="kpi-change <?php echo $stats_data['contacts_trend'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $stats_data['contacts_trend'] >= 0 ? '+' : ''; ?><?php echo $stats_data['contacts_trend']; ?>%
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon">
                        <i class="fas fa-percentage text-warning"></i>
                    </div>
                    <div class="kpi-content">
                        <div class="kpi-value"><?php echo number_format($stats_data['conversion_rate'], 1); ?>%</div>
                        <div class="kpi-label">Taux conversion</div>
                        <div class="kpi-change <?php echo $stats_data['conversion_trend'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $stats_data['conversion_trend'] >= 0 ? '+' : ''; ?><?php echo number_format($stats_data['conversion_trend'], 1); ?>%
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon">
                        <i class="fas fa-trophy text-info"></i>
                    </div>
                    <div class="kpi-content">
                        <div class="kpi-value">
                            <?php if ($selected_vcard > 0): ?>
                                #<?php echo $stats_data['ranking']; ?>
                            <?php else: ?>
                                <?php echo $stats_data['best_performer']; ?>
                            <?php endif; ?>
                        </div>
                        <div class="kpi-label">
                            <?php echo $selected_vcard > 0 ? 'Classement' : 'Meilleur profil'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        
        <!-- Graphique principal -->
        <div class="col-lg-8">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="h6 mb-0">
                        <?php if ($selected_vcard > 0): ?>
                            Évolution - <?php echo esc_html($selected_card['firstname'] . ' ' . $selected_card['lastname']); ?>
                        <?php else: ?>
                            Comparaison profils (<?php echo $period; ?>)
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="mainChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Graphique secondaire -->  
        <div class="col-lg-4">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="h6 mb-0">Répartition sources</h3>
                </div>
                <div class="card-body">
                    <canvas id="sourcesChart" width="300" height="200"></canvas>
                </div>
            </div>
        </div>
        
    </div>

    <?php if ($selected_vcard == 0): ?>
    <!-- Tableau détaillé profils (mode global uniquement) -->
    <div class="dashboard-card mt-4">
        <div class="card-header">
            <h3 class="h6 mb-0">Détail par profil</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Profil</th>
                            <th>Vues</th>
                            <th>Contacts</th>
                            <th>Taux conversion</th>
                            <th>Évolution</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats_data['profiles_breakdown'] as $profile): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-mini me-2">
                                        <?php echo substr($profile['firstname'] ?: 'N', 0, 1) . substr($profile['lastname'] ?: 'C', 0, 1); ?>
                                    </div>
                                    <div>
                                        <div class="fw-medium"><?php echo esc_html($profile['firstname'] . ' ' . $profile['lastname']); ?></div>
                                        <small class="text-muted"><?php echo esc_html($profile['identifier']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo number_format($profile['views']); ?></td>
                            <td><?php echo number_format($profile['contacts']); ?></td>
                            <td><?php echo number_format($profile['conversion_rate'], 1); ?>%</td>
                            <td>
                                <span class="trend <?php echo $profile['trend'] >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo $profile['trend'] >= 0 ? '+' : ''; ?><?php echo $profile['trend']; ?>%
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewProfileStats(<?php echo $profile['vcard_id']; ?>)">
                                    <i class="fas fa-chart-line"></i> Voir détail
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Configuration graphiques
const chartData = <?php echo json_encode($stats_data['chart_data']); ?>;
const isGlobalView = <?php echo $selected_vcard == 0 ? 'true' : 'false'; ?>;

// Graphique principal
const mainCtx = document.getElementById('mainChart').getContext('2d');
let mainChart;

if (isGlobalView) {
    // Mode global : Graphique barres comparatif profils  
    mainChart = new Chart(mainCtx, {
        type: 'bar',
        data: {
            labels: chartData.profiles.map(p => p.name),
            datasets: [
                {
                    label: 'Vues',
                    data: chartData.profiles.map(p => p.views),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Contacts',  
                    data: chartData.profiles.map(p => p.contacts),
                    backgroundColor: 'rgba(75, 192, 192, 0.8)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
} else {
    // Mode individuel : Graphique ligne évolution temporelle
    mainChart = new Chart(mainCtx, {
        type: 'line',
        data: {
            labels: chartData.timeline.map(t => t.date),
            datasets: [
                {
                    label: 'Vues quotidiennes',
                    data: chartData.timeline.map(t => t.views),
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Contacts quotidiens',
                    data: chartData.timeline.map(t => t.contacts),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Graphique sources
const sourcesCtx = document.getElementById('sourcesChart').getContext('2d');
const sourcesChart = new Chart(sourcesCtx, {
    type: 'doughnut',
    data: {
        labels: chartData.sources.map(s => s.name),
        datasets: [{
            data: chartData.sources.map(s => s.value),
            backgroundColor: [
                '#FF6384',
                '#36A2EB', 
                '#FFCE56',
                '#4BC0C0',
                '#9966FF'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Navigation
function changeStatsView(vcardId) {
    const currentUrl = new URL(window.location);
    if (vcardId > 0) {
        currentUrl.searchParams.set('vcard_stats', vcardId);
    } else {
        currentUrl.searchParams.delete('vcard_stats');
    }
    window.location.href = currentUrl.toString();
}

function changePeriod(period) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('period', period);
    window.location.href = currentUrl.toString();
}

function viewProfileStats(vcardId) {
    changeStatsView(vcardId);
}
</script>

/*
Validation :
Sélecteur profils fonctionne, graphiques adaptatifs selon sélection, stats globales vs individuelles.
*/
```

---

### **PHASE 2 : Renouvellement et Finalisation (1-2 jours)**

#### **JOUR 4 : Renouvellement et APIs**

**Tâche 4.1 : Système renouvellement (4h)**
```php
// Prompt Développement 4.1
/*
Contexte : Implémenter renouvellement carte individuelle avec conservation données.

Tâche :
1. Fonction nfc_renew_enterprise_card($identifier) 
2. Redirection boutique avec paramètres pré-remplis
3. Hook traitement commande renouvellement
4. Conservation URL, stats, contacts existants
5. Interface tunnel commande adapté

Workflow cible :
1. Dashboard → [Renouveler] carte Jean → Redirect boutique
2. Formulaire pré-rempli "Renouvellement NFC1023-1 - Jean Dupont"
3. Client valide → Commande #2156 → Lien avec vCard existante  
4. NFC France : "Encoder carte avec identifiant NFC1023-1"
5. Jean utilise → Même URL, même profil, stats continuent

Fonctions à implémenter :
*/

// Fonction redirection renouvellement
function nfc_generate_renewal_url($card_identifier) {
    $card_info = nfc_get_card_by_identifier($card_identifier);
    if (!$card_info) return false;
    
    // Paramètres URL boutique
    $params = [
        'action' => 'renew',
        'identifier' => $card_identifier,
        'vcard_id' => $card_info['vcard_id'],
        'name' => $card_info['firstname'] . ' ' . $card_info['lastname'],
        'renewal_token' => wp_create_nonce('nfc_renewal_' . $card_identifier)
    ];
    
    return add_query_arg($params, home_url('/boutique-nfc/'));
}

// Hook renouvellement post-commande
add_action('woocommerce_thankyou', 'nfc_process_renewal_order', 5);
function nfc_process_renewal_order($order_id) {
    $order = wc_get_order($order_id);
    
    // Vérifier si commande de renouvellement
    $renewal_identifier = $order->get_meta('_nfc_renewal_identifier');
    if (!$renewal_identifier) return;
    
    $existing_vcard = nfc_get_vcard_by_identifier($renewal_identifier);
    if (!$existing_vcard) return;
    
    // Mettre à jour historique renouvellements
    $renewal_history = get_post_meta($existing_vcard->ID, '_renewal_orders', true) ?: [];
    $renewal_history[] = [
        'order_id' => $order_id,
        'renewed_at' => current_time('mysql'),
        'previous_order' => get_post_meta($existing_vcard->ID, '_enterprise_order_id', true)
    ];
    
    update_post_meta($existing_vcard->ID, '_renewal_orders', $renewal_history);
    update_post_meta($existing_vcard->ID, '_last_renewal_order', $order_id);
    
    // Email confirmation renouvellement
    nfc_send_renewal_notification($order, $existing_vcard, $renewal_identifier);
    
    error_log("NFC: Carte {$renewal_identifier} renouvelée via commande {$order_id}");
}

// Email renouvellement
function nfc_send_renewal_notification($order, $vcard, $identifier) {
    $customer_email = $order->get_billing_email();
    $vcard_url = get_permalink($vcard->ID);
    $profile_name = get_post_meta($vcard->ID, 'firstname', true) . ' ' . get_post_meta($vcard->ID, 'lastname', true);
    
    $subject = "Renouvellement carte {$identifier} confirmé - NFC France";
    
    $message = "
        Bonjour,
        
        Le renouvellement de votre carte NFC {$identifier} ({$profile_name}) est confirmé !
        
        Commande de renouvellement : #{$order->get_id()}
        
        Votre profil reste identique :
        • Même URL : {$vcard_url}
        • Même QR code
        • Statistiques et contacts conservés
        
        NFC France va encoder votre nouvelle carte physique avec le même identifiant.
        Vous pourrez l'utiliser dès réception sans aucune configuration.
        
        Cordialement,
        L'équipe NFC France
    ";
    
    wp_mail($customer_email, $subject, $message);
}

/*
Validation :
Bouton renouvellement → URL correcte → Commande → Conservation données → Email confirmation.
*/
```

**Tâche 4.2 : APIs REST entreprise (3h)**
```php
// Prompt Développement 4.2
/*
Contexte : APIs REST pour dashboard entreprise et interactions AJAX.

Tâche :
1. Créer rest-enterprise.php avec routes API
2. Routes CRUD pour cartes entreprise
3. Routes stats et analytics
4. Routes gestion renouvellements
5. Authentification et sécurité

Routes à implémenter :
*/

<?php
/**
 * APIs REST Entreprise
 * Fichier : includes/api/rest-enterprise.php
 */

class NFC_Enterprise_REST_API {
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    public function register_routes() {
        $namespace = 'nfc-enterprise/v1';
        
        // Routes cartes utilisateur
        register_rest_route($namespace, '/user/(?P<user_id>\\d+)/cards', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_user_cards'],
            'permission_callback' => [$this, 'check_user_permission'],
            'args' => [
                'user_id' => ['required' => true, 'validate_callback' => 'is_numeric']
            ]
        ]);
        
        // Routes profil vCard individuel
        register_rest_route($namespace, '/vcard/(?P<vcard_id>\\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_vcard_profile'],
            'permission_callback' => [$this, 'check_vcard_permission']
        ]);
        
        register_rest_route($namespace, '/vcard/(?P<vcard_id>\\d+)', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_vcard_profile'],
            'permission_callback' => [$this, 'check_vcard_permission']
        ]);
        
        // Routes statistiques
        register_rest_route($namespace, '/stats/global/(?P<user_id>\\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_global_stats'],
            'permission_callback' => [$this, 'check_user_permission']
        ]);
        
        register_rest_route($namespace, '/stats/vcard/(?P<vcard_id>\\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_vcard_stats'],
            'permission_callback' => [$this, 'check_vcard_permission']
        ]);
        
        // Routes contacts
        register_rest_route($namespace, '/contacts/(?P<user_id>\\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_user_contacts'],
            'permission_callback' => [$this, 'check_user_permission'],
            'args' => [
                'vcard_filter' => ['required' => false, 'validate_callback' => 'is_numeric']
            ]
        ]);
        
        // Routes renouvellement
        register_rest_route($namespace, '/renew/(?P<identifier>[a-zA-Z0-9-]+)', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'generate_renewal_url'],
            'permission_callback' => [$this, 'check_renewal_permission']
        ]);
    }
    
    // Méthodes API
    public function get_user_cards($request) {
        $user_id = $request['user_id'];
        
        try {
            $cards = nfc_get_user_enterprise_cards($user_id);
            
            return rest_ensure_response([
                'success' => true,
                'data' => $cards,
                'total' => count($cards)
            ]);
            
        } catch (Exception $e) {
            return new WP_Error('api_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function get_vcard_profile($request) {
        $vcard_id = $request['vcard_id'];
        
        $vcard = get_post($vcard_id);
        if (!$vcard || $vcard->post_type !== 'virtual_card') {
            return new WP_Error('not_found', 'vCard not found', ['status' => 404]);
        }
        
        // Récupérer toutes métadonnées
        $profile_data = [
            'id' => $vcard_id,
            'title' => $vcard->post_title,
            'firstname' => get_post_meta($vcard_id, 'firstname', true),
            'lastname' => get_post_meta($vcard_id, 'lastname', true),
            'email' => get_post_meta($vcard_id, 'email', true),
            'mobile' => get_post_meta($vcard_id, 'mobile', true),
            'phone' => get_post_meta($vcard_id, 'phone', true),
            'society' => get_post_meta($vcard_id, 'society', true),
            'post' => get_post_meta($vcard_id, 'post', true),
            'website' => get_post_meta($vcard_id, 'website', true),
            'linkedin' => get_post_meta($vcard_id, 'linkedin', true),
            'description' => get_post_meta($vcard_id, 'description', true),
            'profile_picture' => get_post_meta($vcard_id, 'profile_picture', true),
            'public_url' => get_permalink($vcard_id),
            'identifier' => get_post_meta($vcard_id, '_enterprise_identifier', true),
            'status' => get_post_meta($vcard_id, '_card_configured', true) ? 'configured' : 'pending'
        ];
        
        return rest_ensure_response([
            'success' => true,
            'data' => $profile_data
        ]);
    }
    
    public function update_vcard_profile($request) {
        $vcard_id = $request['vcard_id'];
        $params = $request->get_params();
        
        // Champs autorisés à modifier
        $allowed_fields = [
            'firstname', 'lastname', 'email', 'mobile', 'phone',
            'society', 'post', 'website', 'linkedin', 'description'
        ];
        
        $updated_fields = [];
        
        foreach ($allowed_fields as $field) {
            if (isset($params[$field])) {
                $old_value = get_post_meta($vcard_id, $field, true);
                $new_value = sanitize_text_field($params[$field]);
                
                update_post_meta($vcard_id, $field, $new_value);
                
                if ($old_value !== $new_value) {
                    $updated_fields[] = $field;
                }
            }
        }
        
        // Marquer comme configuré si c'était en attente
        $was_pending = !get_post_meta($vcard_id, '_card_configured', true);
        if ($was_pending && !empty($updated_fields)) {
            update_post_meta($vcard_id, '_card_configured', true);
            
            // Mettre à jour statut dans table enterprise
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'nfc_enterprise_cards',
                ['card_status' => 'configured'],
                ['vcard_id' => $vcard_id]
            );
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'updated_fields' => $updated_fields,
                'status_changed' => $was_pending,
                'new_status' => 'configured'
            ]
        ]);
    }
    
    public function get_global_stats($request) {
        $user_id = $request['user_id'];
        $period = $request->get_param('period') ?: '30d';
        
        $stats = nfc_get_enterprise_global_stats($user_id, $period);
        
        return rest_ensure_response([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    public function get_vcard_stats($request) {
        $vcard_id = $request['vcard_id'];
        $period = $request->get_param('period') ?: '30d';
        
        $stats = nfc_get_vcard_detailed_stats($vcard_id, $period);
        
        return rest_ensure_response([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    public function get_user_contacts($request) {
        $user_id = $request['user_id'];
        $vcard_filter = $request->get_param('vcard_filter') ?: 0;
        
        $contacts = nfc_get_enterprise_contacts($user_id, $vcard_filter);
        
        return rest_ensure_response([
            'success' => true,
            'data' => $contacts,
            'total' => count($contacts)
        ]);
    }
    
    public function generate_renewal_url($request) {
        $identifier = $request['identifier'];
        
        $renewal_url = nfc_generate_renewal_url($identifier);
        if (!$renewal_url) {
            return new WP_Error('not_found', 'Card not found', ['status' => 404]);
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'renewal_url' => $renewal_url,
                'identifier' => $identifier
            ]
        ]);
    }
    
    // Permissions
    public function check_user_permission($request) {
        $user_id = $request['user_id'];
        $current_user_id = get_current_user_id();
        
        return $current_user_id && ($current_user_id == $user_id || current_user_can('manage_options'));
    }
    
    public function check_vcard_permission($request) {
        $vcard_id = $request['vcard_id'];
        $current_user_id = get_current_user_id();
        
        if (!$current_user_id) return false;
        if (current_user_can('manage_options')) return true;
        
        // Vérifier propriété vCard
        global $wpdb;
        $owner_id = $wpdb->get_var($wpdb->prepare(
            "SELECT main_user_id FROM {$wpdb->prefix}nfc_enterprise_cards WHERE vcard_id = %d",
            $vcard_id
        ));
        
        return $owner_id == $current_user_id;
    }
    
    public function check_renewal_permission($request) {
        $identifier = $request['identifier'];
        $current_user_id = get_current_user_id();
        
        if (!$current_user_id) return false;
        
        $card_info = nfc_get_card_by_identifier($identifier);
        return $card_info && $card_info['main_user_id'] == $current_user_id;
    }
}

new NFC_Enterprise_REST_API();

/*
Validation :
Routes API fonctionnelles, authentification sécurisée, réponses JSON correctes.
*/
```

#### **JOUR 5 : Tests et Documentation**

**Tâche 5.1 : Tests intégration complète (4h)**
- Tests workflow commande multi-cartes 
- Tests dashboard avec vraies données
- Tests renouvellement bout en bout
- Tests performance avec 20+ cartes
- Correction bugs identifiés

**Tâche 5.2 : Migration production et documentation (3h)**
- Script migration vCards existantes
- Documentation utilisateur (guide dashboard)
- Documentation technique (APIs, hooks)
- Formation support client

---

## ✅ **Validation Finale**

### **Tests Acceptation**
```php
// Test création multi-cartes
function test_multi_card_creation() {
    // Commande 5 cartes → 5 vCards + 5 identifiants + 5 URLs
    $order_id = create_test_order(['quantity' => 5]);
    $vcards = nfc_get_order_vcards($order_id);
    
    assert_count(5, $vcards);
    assert_unique_identifiers($vcards);
    assert_unique_urls($vcards);
}

// Test dashboard entreprise
function test_enterprise_dashboard() {
    $user_id = create_user_with_cards(8);
    $cards = nfc_get_user_enterprise_cards($user_id);
    
    assert_count(8, $cards);
    assert_valid_dashboard_data($cards);
}

// Test renouvellement
function test_card_renewal() {
    $identifier = 'NFC1023-1';
    $original_stats = get_vcard_stats_by_identifier($identifier);
    
    process_renewal_order($identifier);
    
    $new_stats = get_vcard_stats_by_identifier($identifier);
    assert_stats_preserved($original_stats, $new_stats);
}
```

### **Critères Performance**
- Dashboard < 2s avec 20 cartes
- Création 10 cartes < 5s  
- APIs REST < 500ms
- Pas de régression vCards existantes

---

*Plan d'action technique complet*  
*Système Multi-cartes vCard Entreprise v1*  
*Prêt pour développement immédiat*