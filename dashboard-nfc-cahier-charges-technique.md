# Cahier des charges technique - Dashboard NFC Multi-tenant
**Extension du plugin gtmi_vcard avec interface client complète**

---

## 📋 Vue d'ensemble du projet

### Contexte
- **Plugin existant** : `gtmi_vcard` fonctionnel avec API REST complète
- **Objectif** : Développer l'interface frontend client + système multi-tenant
- **Intégration** : Extension du compte client WooCommerce

### Architecture cible
```
SYSTÈME NFC COMPLET
├── Plugin gtmi_vcard (existant) ✅
│   ├── API REST complète
│   ├── Custom Post Types (virtual_card, lead, statistics)
│   ├── Intégration WooCommerce
│   └── Gestion stats/leads
│
├── Dashboard Client (à développer)
│   ├── Interface simple (particulier)
│   ├── Interface entreprise (admin)
│   ├── Interface employé
│   └── Gestion QR codes
│
└── Système Multi-tenant (à développer)
    ├── Gestion des entreprises
    ├── Rôles et permissions
    ├── Attribution des cartes
    └── Facturation centralisée
```

---

## 🏗️ Architecture technique détaillée

### Stack technique
- **Backend** : PHP 8.1+, WordPress 6.x, WooCommerce 6.0+
- **Frontend** : Bootstrap 5.3, JavaScript ES6+, Chart.js
- **Base de données** : MySQL 5.7+ (extensions du schéma WP)
- **APIs** : REST WordPress + extensions custom
- **QR Codes** : Bibliothèque PHP QR Code + Canvas JS
- **Sécurité** : Nonces WordPress, validation, sanitization

### Arborescence complète du projet

```
wp-content/
├── plugins/
│   └── gtmi-vcard/                           # Plugin existant
│       ├── gtmi-vcard.php                    # ✅ Fichier principal
│       ├── README.md                         # ✅ Documentation existante
│       │
│       ├── api/                              # ✅ API REST existante
│       │   ├── index.php
│       │   ├── lead/
│       │   │   ├── add.php
│       │   │   ├── find.php
│       │   │   └── index.php
│       │   ├── statistics/
│       │   │   ├── add.php
│       │   │   ├── find.php
│       │   │   └── index.php
│       │   └── virtual-card/
│       │       ├── index.php
│       │       └── update.php
│       │
│       ├── includes/                         # ✅ Classes existantes
│       │   ├── admin/
│       │   │   ├── index.php
│       │   │   ├── lead/find.php
│       │   │   ├── statistics/
│       │   │   │   ├── filter.php
│       │   │   │   └── find.php
│       │   │   └── virtual_card/
│       │   │       ├── add.php
│       │   │       ├── find.php
│       │   │       └── update.php
│       │   │
│       │   ├── custom-post-type/             # ✅ CPT existants
│       │   │   ├── index.php
│       │   │   ├── lead.php
│       │   │   ├── statistics.php
│       │   │   └── virtual_card.php
│       │   │
│       │   ├── utils/                        # ✅ Utilitaires existants
│       │   │   ├── after_order.php
│       │   │   ├── api.php
│       │   │   ├── exports.php
│       │   │   ├── functions.php
│       │   │   ├── index.php
│       │   │   ├── jwt.php
│       │   │   ├── load_js.php
│       │   │   ├── load_single_templates.php
│       │   │   └── unique_url.php
│       │   │
│       │   └── dashboard/                    # 🆕 NOUVEAU - Interface client
│       │       ├── class-dashboard-manager.php
│       │       ├── class-user-manager.php
│       │       ├── class-company-manager.php
│       │       ├── class-qr-manager.php
│       │       └── dashboard-hooks.php
│       │
│       ├── templates/                        # ✅ Templates existants + nouveaux
│       │   ├── single-virtual_card.php       # ✅ Template public existant
│       │   ├── single-lead.php               # ✅ Template lead existant
│       │   │
│       │   └── dashboard/                    # 🆕 NOUVEAU - Templates dashboard
│       │       ├── layout/
│       │       │   ├── dashboard-layout.php  # Layout principal
│       │       │   ├── sidebar.php           # Navigation sidebar
│       │       │   ├── header.php            # Header dashboard
│       │       │   └── breadcrumb.php        # Breadcrumb WooCommerce
│       │       │
│       │       ├── simple/                   # Dashboard compte simple
│       │       │   ├── dashboard.php         # Vue d'ensemble
│       │       │   ├── vcard-edit.php        # Édition vCard
│       │       │   ├── qr-codes.php          # Gestion QR codes
│       │       │   ├── contacts.php          # Liste contacts
│       │       │   ├── statistics.php        # Statistiques
│       │       │   └── preview.php           # Aperçu public
│       │       │
│       │       ├── company/                  # Dashboard entreprise
│       │       │   ├── dashboard.php         # Vue d'ensemble entreprise
│       │       │   ├── employees.php         # Gestion employés
│       │       │   ├── cards.php             # Toutes les cartes
│       │       │   ├── analytics.php         # Analytics globales
│       │       │   ├── billing.php           # Facturation
│       │       │   └── settings.php          # Paramètres entreprise
│       │       │
│       │       ├── employee/                 # Dashboard employé
│       │       │   ├── dashboard.php         # Vue employé
│       │       │   ├── my-card.php           # Ma carte uniquement
│       │       │   └── company-info.php      # Infos entreprise (lecture)
│       │       │
│       │       └── components/               # Composants réutilisables
│       │           ├── stats-cards.php       # Cards statistiques
│       │           ├── contact-list.php      # Liste contacts
│       │           ├── qr-preview.php        # Preview QR code
│       │           ├── vcard-form.php        # Formulaire vCard
│       │           └── user-menu.php         # Menu utilisateur
│       │
│       ├── assets/                           # ✅ Assets existants + nouveaux
│       │   ├── css/
│       │   │   └── dashboard.css             # 🆕 Styles dashboard complet
│       │   │
│       │   ├── js/                           # ✅ JS existants + nouveaux
│       │   │   ├── lead.js                   # ✅ Existant
│       │   │   ├── statistics.js             # ✅ Existant
│       │   │   ├── virtual_card.js           # ✅ Existant
│       │   │   │
│       │   │   └── dashboard/                # 🆕 NOUVEAU - JS dashboard
│       │   │       ├── dashboard-core.js     # Logique principale
│       │   │       ├── vcard-editor.js       # Éditeur vCard
│       │   │       ├── qr-generator.js       # Génération QR codes
│       │   │       ├── contact-manager.js    # Gestion contacts
│       │   │       ├── stats-charts.js       # Graphiques Chart.js
│       │   │       └── company-manager.js    # Gestion entreprise
│       │   │
│       │   └── libs/                         # 🆕 Bibliothèques externes
│       │       ├── qrcode.min.js             # Génération QR codes
│       │       ├── chart.min.js              # Chart.js
│       │       └── html2canvas.min.js        # Export images
│       │
│       ├── docs/                             # ✅ Documentation existante
│       │   ├── acf/                          # ✅ Exports ACF
│       │   │   ├── lead.v3.json
│       │   │   ├── statistics.v1.json
│       │   │   └── virtual_card.v3.json
│       │   │
│       │   ├── exports/                      # ✅ Exemples exports
│       │   │   ├── leads_export.csv
│       │   │   └── virtual_card_export.csv
│       │   │
│       │   ├── img/                          # ✅ Images doc API
│       │   │   ├── get_leads.png
│       │   │   ├── post_lead.png
│       │   │   └── ...
│       │   │
│       │   └── technical/                    # 🆕 Documentation technique
│       │       ├── database-schema.md        # Schéma BDD complet
│       │       ├── api-endpoints.md          # Documentation API étendue
│       │       ├── user-roles.md             # Rôles et permissions
│       │       ├── installation.md           # Guide installation
│       │       └── deployment.md             # Guide déploiement
│       │
│       └── uploads/                          # 🆕 Fichiers générés
│           ├── qr-codes/                     # QR codes générés
│           │   ├── [user-id]/
│           │   │   ├── qr-default.png
│           │   │   ├── qr-custom.svg
│           │   │   └── qr-print.pdf
│           │   └── ...
│           │
│           └── exports/                      # Exports utilisateur
│               ├── contacts-[user-id].csv
│               └── stats-[user-id].xlsx
│
└── themes/
    └── [theme-actuel]/
        ├── functions.php                     # 🔄 Hooks dashboard
        │
        └── woocommerce/                      # 🆕 Templates WooCommerce étendus
            └── myaccount/
                ├── dashboard.php             # 🔄 Ajout lien dashboard NFC
                └── nfc-dashboard.php         # 🆕 Point d'entrée dashboard
```

---

## 📊 Base de données - Extensions

### Tables existantes (plugin actuel)
```sql
-- WordPress standard + WooCommerce + ACF
wp_posts                    # virtual_card, lead, statistics (CPT)
wp_postmeta                 # Champs ACF des CPT
wp_users                    # Utilisateurs WordPress
wp_usermeta                 # Métadonnées utilisateurs
```

### Nouvelles tables à créer
```sql
-- Table des entreprises
CREATE TABLE wp_nfc_companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    admin_user_id INT NOT NULL,              -- ID WordPress de l'admin
    logo_url VARCHAR(500),
    description TEXT,
    
    -- Configuration entreprise
    template_settings JSON,                  -- Templates imposés
    brand_colors JSON,                       -- Couleurs entreprise
    
    -- Abonnement
    subscription_plan VARCHAR(50) DEFAULT 'basic',
    max_employees INT DEFAULT 10,
    max_cards INT DEFAULT 50,
    
    -- Facturation
    billing_email VARCHAR(255),
    billing_address JSON,
    
    -- Métadonnées
    status ENUM('active', 'suspended', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_admin_user (admin_user_id),
    INDEX idx_status (status),
    INDEX idx_slug (slug),
    
    FOREIGN KEY (admin_user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
);

-- Table des relations utilisateurs-entreprises (Many-to-Many)
CREATE TABLE wp_nfc_user_companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                    -- ID WordPress
    company_id INT NOT NULL,
    
    -- Rôle dans l'entreprise
    role ENUM('admin', 'manager', 'employee') DEFAULT 'employee',
    
    -- Permissions spécifiques
    permissions JSON,                        -- {"can_edit_vcard": true, "can_view_analytics": false}
    
    -- Invitation
    invited_by INT,                          -- User ID qui a invité
    invitation_token VARCHAR(100),           -- Token d'invitation
    invited_at TIMESTAMP NULL,
    joined_at TIMESTAMP NULL,
    
    -- Statut
    status ENUM('pending', 'active', 'suspended', 'left') DEFAULT 'pending',
    
    -- Métadonnées
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_company (user_id, company_id),
    INDEX idx_user (user_id),
    INDEX idx_company (company_id),
    INDEX idx_status (status),
    INDEX idx_invitation_token (invitation_token),
    
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES wp_nfc_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES wp_users(ID) ON DELETE SET NULL
);

-- Table des abonnements (pour V2)
CREATE TABLE wp_nfc_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    plan_name VARCHAR(50) NOT NULL,
    
    -- Limites
    max_employees INT NOT NULL,
    max_cards INT NOT NULL,
    max_storage_mb INT NOT NULL,
    
    -- Facturation
    price_monthly DECIMAL(10,2),
    price_yearly DECIMAL(10,2),
    billing_cycle ENUM('monthly', 'yearly') DEFAULT 'monthly',
    
    -- Période
    starts_at DATE NOT NULL,
    ends_at DATE NOT NULL,
    
    -- Statut
    status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_company (company_id),
    INDEX idx_status (status),
    INDEX idx_dates (starts_at, ends_at),
    
    FOREIGN KEY (company_id) REFERENCES wp_nfc_companies(id) ON DELETE CASCADE
);

-- Extension des métadonnées vCard
-- Ajout de colonnes dans wp_postmeta pour les virtual_card
```

### Champs ACF étendus
```php
// Nouveaux champs pour virtual_card
'company_id'          => 'ID de l\'entreprise (si applicable)',
'assigned_user_id'    => 'ID utilisateur assigné',
'card_type'          => 'personal|business|corporate',
'template_id'        => 'ID template entreprise',
'brand_compliance'   => 'Respect charte graphique',

// Nouveaux champs pour statistics
'company_id'         => 'ID entreprise pour analytics groupées',
'device_type'        => 'mobile|desktop|tablet',
'location_data'      => 'Données géolocalisation (anonymisées)',
'referrer'           => 'Source du trafic',
```

---

## 🔐 Système de rôles et permissions

### Rôles WordPress personnalisés
```php
// Rôles à créer
add_role('nfc_company_admin', 'Admin Entreprise NFC', [
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
    // Permissions spécifiques NFC
    'manage_nfc_company' => true,
    'invite_nfc_employees' => true,
    'view_nfc_analytics' => true,
    'manage_nfc_billing' => true,
]);

add_role('nfc_company_manager', 'Manager Entreprise NFC', [
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
    // Permissions limitées
    'invite_nfc_employees' => true,
    'view_nfc_analytics' => true,
    'manage_nfc_billing' => false,
]);

add_role('nfc_employee', 'Employé NFC', [
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
    // Permissions minimales
    'edit_own_nfc_vcard' => true,
    'view_own_nfc_stats' => true,
]);
```

### Matrice des permissions
| Action | Particulier | Admin Entreprise | Manager | Employé |
|--------|-------------|------------------|---------|---------|
| Modifier sa vCard | ✅ | ✅ | ✅ | ✅ |
| Voir ses stats | ✅ | ✅ | ✅ | ✅ |
| Gérer ses contacts | ✅ | ✅ | ✅ | ✅ |
| Inviter des employés | ❌ | ✅ | ✅ | ❌ |
| Voir stats entreprise | ❌ | ✅ | ✅ | ❌ |
| Gérer la facturation | ❌ | ✅ | ❌ | ❌ |
| Modifier templates | ❌ | ✅ | ❌ | ❌ |
| Supprimer des employés | ❌ | ✅ | ❌ | ❌ |

---

## 🛠️ APIs étendues

### API existantes (plugin actuel)
```php
// Virtual Card
GET    /wp-json/gtmi_vcard/v1/vcard/[ID]           # ✅ Récupérer vCard
POST   /wp-json/gtmi_vcard/v1/vcard/[ID]           # ✅ Modifier vCard

// Leads
GET    /wp-json/gtmi_vcard/v1/leads/[VCARD_ID]     # ✅ Récupérer leads
POST   /wp-json/gtmi_vcard/v1/lead                 # ✅ Ajouter lead

// Statistics
GET    /wp-json/gtmi_vcard/v1/statistics/[VCARD_ID]/[START]/[END]  # ✅ Stats période
GET    /wp-json/gtmi_vcard/v1/statistics/[VCARD_ID]                # ✅ Toutes stats
POST   /wp-json/gtmi_vcard/v1/statistics                           # ✅ Ajouter stat
```

### Nouvelles APIs à développer
```php
// User Management
GET    /wp-json/gtmi_vcard/v1/user/profile                    # Profil utilisateur
PUT    /wp-json/gtmi_vcard/v1/user/profile                    # Modifier profil
GET    /wp-json/gtmi_vcard/v1/user/vcards                     # vCards utilisateur
GET    /wp-json/gtmi_vcard/v1/user/dashboard                  # Données dashboard

// Company Management (Admin uniquement)
GET    /wp-json/gtmi_vcard/v1/company/[ID]                    # Infos entreprise
PUT    /wp-json/gtmi_vcard/v1/company/[ID]                    # Modifier entreprise
GET    /wp-json/gtmi_vcard/v1/company/[ID]/employees          # Liste employés
POST   /wp-json/gtmi_vcard/v1/company/[ID]/invite             # Inviter employé
DELETE /wp-json/gtmi_vcard/v1/company/[ID]/employee/[USER_ID] # Supprimer employé
GET    /wp-json/gtmi_vcard/v1/company/[ID]/vcards             # Toutes les vCards
GET    /wp-json/gtmi_vcard/v1/company/[ID]/analytics          # Analytics entreprise

// QR Code Management
GET    /wp-json/gtmi_vcard/v1/qr/[VCARD_ID]                   # Récupérer QR
POST   /wp-json/gtmi_vcard/v1/qr/[VCARD_ID]/generate          # Générer QR
GET    /wp-json/gtmi_vcard/v1/qr/[VCARD_ID]/download/[FORMAT] # Télécharger QR

// Permissions & Roles
GET    /wp-json/gtmi_vcard/v1/permissions/user                # Permissions utilisateur
GET    /wp-json/gtmi_vcard/v1/permissions/company/[ID]        # Permissions entreprise

// Exports
GET    /wp-json/gtmi_vcard/v1/export/contacts/[VCARD_ID]      # Export contacts CSV
GET    /wp-json/gtmi_vcard/v1/export/statistics/[VCARD_ID]    # Export stats Excel
GET    /wp-json/gtmi_vcard/v1/export/company/[ID]/analytics   # Export analytics entreprise
```

---

## 🎨 Interface utilisateur - Spécifications

### Design System
```css
:root {
    /* Couleurs principales */
    --nfc-primary: #0040C1;
    --nfc-secondary: #667eea;
    --nfc-success: #10b981;
    --nfc-warning: #f59e0b;
    --nfc-danger: #ef4444;
    
    /* Interface */
    --nfc-background: #f8fafc;
    --nfc-sidebar: #1a202c;
    --nfc-text: #1a202c;
    --nfc-text-light: #64748b;
    
    /* Dimensions */
    --sidebar-width: 280px;
    --header-height: 80px;
    --card-radius: 12px;
    
    /* Animations */
    --transition-fast: 0.15s ease;
    --transition-normal: 0.3s ease;
}
```

### Composants UI réutilisables
```php
// Templates de composants
dashboard/components/
├── stats-card.php              # Card statistique avec icône
├── user-avatar.php             # Avatar utilisateur généré
├── action-button.php           # Bouton d'action avec état
├── data-table.php              # Tableau avec pagination
├── form-field.php              # Champ de formulaire validé
├── modal-dialog.php            # Modal Bootstrap custom
├── breadcrumb.php              # Fil d'ariane WooCommerce
├── notification.php            # Messages de notification
└── loading-spinner.php         # États de chargement
```

### Pages et sections
```
Dashboard Simple (Particulier)
├── Vue d'ensemble              # Stats + actions rapides
├── Modifier ma vCard           # Formulaire complet
├── QR Codes                    # Génération + téléchargement
├── Mes contacts                # Liste + export
├── Statistiques                # Analytics détaillées
└── Aperçu public               # Preview vCard

Dashboard Entreprise (Admin)
├── Vue d'ensemble entreprise   # Stats consolidées
├── Gestion des employés        # Invitation + suppression
├── Toutes les vCards           # Gestion centralisée
├── Analytics globales          # Graphiques détaillés
├── Facturation                 # Abonnement + factures
└── Paramètres entreprise       # Configuration

Dashboard Employé
├── Ma vCard                    # Édition limitée
├── Mes contacts                # Contacts personnels
├── Mes statistiques            # Stats individuelles
└── Mon entreprise              # Infos lecture seule
```

---

## 🔄 Workflows et cas d'usage

### Workflow 1 : Utilisateur simple
```
1. Client commande carte NFC via configurateur
2. Commande validée → vCard créée automatiquement
3. Email avec lien dashboard envoyé
4. Client accède dashboard simple
5. Client modifie ses infos, télécharge QR
6. Gestion contacts et consultation stats
```

### Workflow 2 : Création entreprise
```
1. Admin crée compte entreprise
2. Définit templates et charte graphique
3. Invite employés par email
4. Commande cartes pour les employés
5. Attribution automatique des vCards
6. Employés reçoivent accès à leur dashboard
```

### Workflow 3 : Gestion employé
```
1. Admin invite employé (email)
2. Employé reçoit email d'invitation
3. Employé clique lien → compte WP créé
4. Admin commande carte pour employé
5. vCard créée avec template entreprise
6. Employé peut modifier infos dans limites template
```

### Workflow 4 : Facturation entreprise
```
1. Abonnement mensuel/annuel
2. Facturation centralisée à l'admin
3. Limites selon plan (nb employés, cartes)
4. Upgrade/downgrade possible
5. Suspension si non-paiement
```

---

## 🔧 Classes PHP principales à développer

### 1. Dashboard Manager
```php
class NFC_Dashboard_Manager {
    public function __construct();
    public function init_hooks();
    
    // Routing et accès
    public function handle_dashboard_access();
    public function get_user_dashboard_type($user_id);
    public function check_permissions($user_id, $action);
    
    // Rendu des pages
    public function render_dashboard($user_id, $page = 'overview');
    public function render_simple_dashboard($user_id);
    public function render_company_dashboard($user_id);
    public function render_employee_dashboard($user_id);
    
    // Assets
    public function enqueue_dashboard_assets();
    public function get_dashboard_config($user_id);
}
```

### 2. User Manager
```php
class NFC_User_Manager {
    // Types d'utilisateur
    public function get_user_type($user_id);
    public function is_company_admin($user_id);
    public function is_employee($user_id);
    public function get_user_company($user_id);
    
    // Permissions
    public function user_can($user_id, $capability);
    public function get_user_permissions($user_id);
    public function get_accessible_vcards($user_id);
    
    // Profile management
    public function update_user_profile($user_id, $data);
    public function get_user_dashboard_data($user_id);
    public function get_user_statistics($user_id);
}
```

### 3. Company Manager
```php
class NFC_Company_Manager {
    // CRUD entreprise
    public function create_company($admin_user_id, $data);
    public function update_company($company_id, $data);
    public function delete_company($company_id);
    public function get_company($company_id);
    
    // Gestion employés
    public function invite_employee($company_id, $email, $role = 'employee');
    public function accept_invitation($invitation_token);
    public function remove_employee($company_id, $user_id);
    public function get_company_employees($company_id);
    
    // Attribution vCards
    public function assign_vcard($company_id, $user_id, $vcard_id);
    public function get_company_vcards($company_id);
    public function get_company_analytics($company_id);
    
    // Templates et branding
    public function set_company_template($company_id, $template_data);
    public function apply_company_branding($vcard_id);
}
```

### 4. QR Manager
```php
class NFC_QR_Manager {
    // Génération
    public function generate_qr_code($vcard_id, $options = []);
    public function customize_qr_design($vcard_id, $design_options);
    
    // Formats d'export
    public function export_png($vcard_id, $size = 'medium');
    public function export_svg($vcard_id);
    public function export_pdf($vcard_id, $format = 'business_card');
    
    // Gestion fichiers
    public function get_qr_file_path($vcard_id, $format);
    public function cleanup_old_qr_files();
    
    // Statistiques QR
    public function track_qr_scan($vcard_id, $data = []);
    public function get_qr_statistics($vcard_id);
}
```

---

## 📚 APIs et intégrations

### Intégration WooCommerce étendue
```php
// Hooks WooCommerce étendus
add_action('woocommerce_order_status_completed', 'nfc_handle_completed_order');
add_action('woocommerce_subscription_status_active', 'nfc_activate_company_subscription');
add_filter('woocommerce_account_menu_items', 'nfc_add_dashboard_menu_item');
add_action('woocommerce_account_nfc-dashboard_endpoint', 'nfc_dashboard_endpoint_content');

// Métadonnées commande étendues
function nfc_save_company_order_meta($order_id) {
    // Associer commande à l'entreprise
    // Gérer attribution multiple des cartes
    // Envoyer notifications aux employés
}
```

### Intégration emails WordPress
```php
// Templates d'emails étendus
templates/emails/
├── invitation-employee.php           # Invitation employé
├── vcard-ready.php                   # Carte prête
├── company-welcome.php               # Bienvenue entreprise
├── subscription-reminder.php         # Rappel abonnement
└── analytics-report.php              # Rapport mensuel

// Gestionnaire d'emails
class NFC_Email_Manager {
    public function send_employee_invitation($email, $company_name, $invitation_link);
    public function send_vcard_ready_notification($user_id, $vcard_id);
    public function send_monthly_analytics($company_id);
    public function send_subscription_reminder($company_id);
}
```

### APIs externes (optionnel V2)
```php
// Intégrations futures
class NFC_External_Integrations {
    // CRM Integration
    public function sync_with_hubspot($company_id);
    public function sync_with_salesforce($company_id);
    
    // Analytics
    public function push_to_google_analytics($event_data);
    public function generate_pdf_reports($company_id);
    
    // Payment
    public function handle_stripe_subscription($company_id);
    public function process_paypal_payment($order_id);
}
```

---

## 🚀 Plan de développement phasé

### Phase 1 : Foundation Dashboard Simple (Semaines 1-3)
#### Objectifs
- Interface dashboard simple fonctionnelle
- Intégration avec plugin existant
- Gestion QR codes basique

#### Livrables
- [ ] Layout principal avec navigation sidebar
- [ ] Page édition vCard connectée à l'API
- [ ] Page gestion QR codes avec génération
- [ ] Page contacts avec liste et export
- [ ] Page statistiques avec graphiques Chart.js
- [ ] Intégration complète avec plugin existant

#### Architecture technique
```php
includes/dashboard/
├── class-dashboard-manager.php        # Gestionnaire principal
├── class-user-manager.php             # Gestion utilisateurs simples
├── class-qr-manager.php               # Génération QR codes
└── dashboard-hooks.php                # Hooks WordPress/WooCommerce

templates/dashboard/simple/
├── dashboard.php                      # Vue d'ensemble
├── vcard-edit.php                     # Édition vCard
├── qr-codes.php                       # Gestion QR
├── contacts.php                       # Liste contacts
└── statistics.php                     # Analytics

assets/
├── css/dashboard.css                  # Styles complets
└── js/dashboard/
    ├── dashboard-core.js               # Logique principale
    ├── vcard-editor.js                 # Éditeur vCard
    ├── qr-generator.js                 # QR codes
    └── stats-charts.js                 # Graphiques
```

### Phase 2 : Multi-tenant Foundation (Semaines 4-6)
#### Objectifs
- Extension base de données
- Système rôles et permissions
- Dashboard entreprise basique

#### Livrables
- [ ] Tables BDD multi-tenant créées
- [ ] Rôles WordPress personnalisés
- [ ] Système d'invitation employés
- [ ] Dashboard admin entreprise MVP
- [ ] Attribution vCards aux employés

#### Architecture technique
```php
includes/dashboard/
├── class-company-manager.php          # Gestion entreprises
├── class-permission-manager.php       # Permissions granulaires
└── class-invitation-manager.php       # Système invitations

templates/dashboard/company/
├── dashboard.php                      # Vue entreprise
├── employees.php                      # Gestion employés
├── cards.php                          # Toutes les cartes
└── settings.php                       # Paramètres

templates/dashboard/employee/
├── dashboard.php                      # Vue employé limitée
└── my-card.php                        # Carte assignée
```

### Phase 3 : Fonctionnalités avancées (Semaines 7-9)
#### Objectifs
- Analytics avancées
- Templates d'entreprise
- Facturation et abonnements

#### Livrables
- [ ] Analytics consolidées entreprise
- [ ] Système templates et branding
- [ ] Gestion abonnements
- [ ] Facturation automatisée
- [ ] Exports avancés (PDF, Excel)

#### Architecture technique
```php
includes/dashboard/
├── class-analytics-manager.php        # Analytics avancées
├── class-template-manager.php         # Templates entreprise
├── class-subscription-manager.php     # Abonnements
└── class-export-manager.php           # Exports avancés

templates/dashboard/company/
├── analytics.php                      # Analytics détaillées
├── billing.php                        # Facturation
└── templates.php                      # Gestion templates
```

---

## 🧪 Tests et qualité

### Tests unitaires PHP
```php
tests/
├── unit/
│   ├── TestDashboardManager.php
│   ├── TestUserManager.php
│   ├── TestCompanyManager.php
│   ├── TestQRManager.php
│   └── TestPermissions.php
│
├── integration/
│   ├── TestWooCommerceIntegration.php
│   ├── TestAPIEndpoints.php
│   └── TestEmailSystem.php
│
└── fixtures/
    ├── sample-companies.json
    ├── sample-users.json
    └── sample-vcards.json
```

### Tests end-to-end (Cypress)
```javascript
cypress/integration/
├── dashboard-simple.spec.js           # Tests dashboard simple
├── company-management.spec.js         # Tests entreprise
├── employee-workflow.spec.js          # Tests employé
├── qr-generation.spec.js              # Tests QR codes
└── permissions.spec.js                # Tests permissions
```

### Performance et sécurité
```php
// Benchmarks performance
- Dashboard load: < 2s
- API response: < 500ms
- QR generation: < 1s
- Database queries: optimisées avec index

// Sécurité
- Validation données: sanitize_text_field(), wp_verify_nonce()
- Permissions: current_user_can(), custom capabilities
- CSRF protection: nonces WordPress
- SQL injection: $wpdb->prepare()
- XSS protection: esc_html(), esc_attr()
```

---

## 📦 Déploiement et maintenance

### Guide d'installation
```bash
# 1. Sauvegarde
wp db export backup-$(date +%Y%m%d).sql

# 2. Mise à jour plugin
wp plugin update gtmi-vcard

# 3. Migration BDD
wp eval-file migrations/001-create-companies-table.php
wp eval-file migrations/002-create-user-companies-table.php

# 4. Import ACF
wp acf import docs/acf/companies.json

# 5. Création rôles
wp eval "do_action('nfc_create_custom_roles');"

# 6. Permissions fichiers
chmod 755 uploads/qr-codes/
chmod 644 assets/css/dashboard.css
```

### Checklist de déploiement
- [ ] Tests unitaires passent
- [ ] Tests e2e passent
- [ ] Performance vérifiée
- [ ] Sécurité auditée
- [ ] Documentation à jour
- [ ] Migration BDD testée
- [ ] Rollback plan préparé

### Monitoring et logs
```php
// Logs application
error_log("NFC_DASHBOARD: User {$user_id} accessed company {$company_id}");

// Métriques à surveiller
- Temps de réponse pages
- Erreurs JavaScript
- Échecs API
- Usage stockage QR codes
- Performances base de données
```

---

## 📖 Documentation utilisateur

### Guides utilisateur
```
docs/user/
├── guide-compte-simple.md             # Guide particulier
├── guide-admin-entreprise.md          # Guide admin entreprise
├── guide-employe.md                   # Guide employé
├── faq.md                              # Questions fréquentes
└── troubleshooting.md                  # Résolution problèmes
```

### Documentation développeur
```
docs/developer/
├── api-reference.md                   # Référence API complète
├── hooks-filters.md                   # Hooks WordPress disponibles
├── customization.md                   # Guide personnalisation
├── contributing.md                    # Guide contribution
└── architecture.md                    # Architecture détaillée
```

---

## 🔮 Roadmap future (V3+)

### Fonctionnalités avancées
- **Mobile app** : Application mobile native
- **NFC physique** : Programmation cartes NFC
- **IA/ML** : Analytics prédictives
- **Intégrations** : CRM, marketing automation
- **White label** : Solution revendable
- **Multi-langue** : Support international

### Évolutions techniques
- **API GraphQL** : API moderne
- **PWA** : Progressive Web App
- **Microservices** : Architecture distribuée
- **CDN** : Distribution globale
- **Blockchain** : Vérification authenticité

---

## ✅ Critères d'acceptation

### Phase 1 : Dashboard Simple
- [ ] Client peut accéder dashboard depuis WooCommerce
- [ ] Client peut modifier sa vCard via formulaire intuitif
- [ ] Client peut générer et télécharger QR codes (PNG, SVG, PDF)
- [ ] Client peut consulter et exporter ses contacts
- [ ] Client peut visualiser ses statistiques avec graphiques
- [ ] Interface 100% responsive (mobile/tablet/desktop)
- [ ] Performance < 3s de chargement initial
- [ ] Compatible IE11+, Chrome, Firefox, Safari

### Phase 2 : Multi-tenant
- [ ] Admin entreprise peut créer son compte
- [ ] Admin peut inviter des employés par email
- [ ] Admin peut attribuer des vCards à ses employés
- [ ] Employé reçoit accès limité à sa vCard uniquement
- [ ] Admin voit analytics consolidées de l'entreprise
- [ ] Permissions respectées selon les rôles
- [ ] Système d'invitation sécurisé fonctionnel

### Phase 3 : Fonctionnalités avancées
- [ ] Templates d'entreprise appliqués automatiquement
- [ ] Facturation centralisée opérationnelle
- [ ] Exports avancés (PDF, Excel) fonctionnels
- [ ] Analytics avancées avec tableaux de bord
- [ ] Système d'abonnements avec limites respectées
- [ ] Notifications email automatiques
- [ ] Documentation complète disponible

---

## 🎯 Conclusion

Ce cahier des charges technique fournit la feuille de route complète pour développer le dashboard client NFC multi-tenant à partir du plugin existant. L'approche phasée permet une livraison incrémentale avec validation utilisateur à chaque étape.

**Prêt pour le développement !** 🚀

---

*Document technique v1.0 - Dashboard NFC Multi-tenant*  
*Base : Plugin gtmi_vcard existant*  
*Architecture évolutive et scalable*