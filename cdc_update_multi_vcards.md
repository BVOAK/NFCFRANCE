# Mise à jour Cahier des Charges - Dashboard NFC
**Gestion multi-vCards et Custom URL pour comptes simples**

---

## 🔄 **Nouvelles fonctionnalités identifiées**

### **1. Gestion Multi-vCards pour comptes simples**

#### **Cas d'usage :**
- **Commande multiple** : Client achète 5 cartes → 1 vCard unique partagée
- **Renouvellement** : Nouvelles cartes → Même vCard + mise à jour Order ID
- **Nouveau compte vCard** : Nouvelles cartes → Nouvelle vCard + sélecteur dans dashboard

#### **Workflow commande étendu :**
```
1. Client passe commande
2. ✅ NOUVEAU : Vérification si vCard existante
   ├── Si OUI → Choix utilisateur : Renouvellement OU Nouveau compte
   │   ├── Renouvellement → Mise à jour Order ID sur vCard existante
   │   └── Nouveau compte → Création nouvelle vCard
   └── Si NON → Création vCard (workflow actuel)
```

#### **Interface Dashboard étendue :**
```
┌─ DASHBOARD HEADER ─────────────────────────────┐
│ [Sélecteur vCard ▼] Ma vCard Pro               │
│ ├── Ma vCard Pro (Order #1012)                │
│ ├── Ma vCard Perso (Order #1008)              │
│ └── Ma vCard Événementiel (Order #1015)       │
└────────────────────────────────────────────────┘
```

---

### **2. Gestion Custom URL**

#### **Fonctionnalité :**
La vCard peut rediriger vers une URL personnalisée au lieu d'afficher le contenu vCard.

#### **Cas d'usage :**
- **Site web personnel** : Redirection vers portfolio
- **Google Avis** : Redirection vers page d'avis Google
- **Landing page** : Redirection vers page produit/service
- **Réseaux sociaux** : Redirection vers profil LinkedIn/Instagram

#### **Comportement technique :**
```php
// Logique de redirection dans single-virtual_card.php
if (!empty($custom_url)) {
    // Redirection 301 vers custom_url
    wp_redirect($custom_url, 301);
    exit;
} else {
    // Affichage vCard classique
    display_vcard_content();
}
```

#### **Interface Dashboard :**
```
┌─ CONFIGURATION VCARD ──────────────────────────┐
│ ○ Mode vCard (affichage des informations)      │
│ ● Mode Custom URL                              │
│                                                │
│ URL personnalisée :                            │
│ [https://monsite.com/contact    ] [Valider]    │
│                                                │
│ ⚠️ En mode Custom URL, votre carte redirige   │
│    vers cette adresse au lieu d'afficher      │
│    vos informations de contact.               │
└────────────────────────────────────────────────┘
```

---

## 🏗️ **Modifications techniques nécessaires**

### **1. Base de données - Extensions multi-vCards**

#### **Table wp_postmeta (virtual_card) - Nouveaux champs :**
```sql
-- Gestion multi-vCards
'is_primary'         => 'boolean', -- vCard principale de l'utilisateur
'vcard_type'         => 'string',  -- 'personal', 'professional', 'event', 'other'
'vcard_label'        => 'string',  -- Label personnalisé (ex: "Ma vCard Pro")

-- Custom URL
'custom_url_enabled' => 'boolean', -- Active/désactive custom URL
'custom_url'         => 'url',     -- URL de redirection personnalisée
'custom_url_stats'   => 'boolean', -- Tracker les stats même en custom URL
```

#### **Nouvelle table de liaison Order-vCard :**
```sql
CREATE TABLE wp_nfc_vcard_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vcard_id INT NOT NULL,                    -- ID de la vCard
    order_id INT NOT NULL,                    -- ID de la commande WooCommerce
    quantity INT DEFAULT 1,                  -- Nombre de cartes commandées
    is_renewal BOOLEAN DEFAULT FALSE,        -- Est-ce un renouvellement ?
    previous_order_id INT NULL,              -- ID commande précédente (renouvellement)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_vcard (vcard_id),
    INDEX idx_order (order_id),
    FOREIGN KEY (vcard_id) REFERENCES wp_posts(ID) ON DELETE CASCADE
);
```

### **2. APIs étendues**

#### **Nouvelles routes REST :**
```php
// Gestion multi-vCards
GET    /wp-json/gtmi_vcard/v1/user/vcards              # Toutes les vCards de l'utilisateur
POST   /wp-json/gtmi_vcard/v1/user/vcards/switch       # Changer de vCard active
PUT    /wp-json/gtmi_vcard/v1/vcard/[ID]/label         # Modifier le label de la vCard

// Custom URL
GET    /wp-json/gtmi_vcard/v1/vcard/[ID]/custom-url    # Config Custom URL
PUT    /wp-json/gtmi_vcard/v1/vcard/[ID]/custom-url    # Modifier Custom URL
POST   /wp-json/gtmi_vcard/v1/vcard/[ID]/test-url      # Tester l'URL custom
```

### **3. Interface Dashboard - Nouvelles pages**

#### **Page "Gestion des vCards" (si multi-vCards) :**
```
templates/dashboard/simple/
├── vcards-manager.php              # 🆕 Gestion multi-vCards
│   ├── Liste des vCards
│   ├── Création nouvelle vCard
│   ├── Définir vCard principale
│   └── Suppression vCard
│
└── custom-url.php                  # 🆕 Configuration Custom URL
    ├── Toggle vCard/Custom URL
    ├── Configuration URL
    ├── Test de redirection
    └── Statistiques Custom URL
```

#### **Modifications pages existantes :**
```php
// vcard-edit.php - Ajouts
├── Section "Configuration de redirection"
├── Toggle vCard classique / Custom URL
├── Champ URL personnalisée
└── Preview comportement

// overview.php - Ajouts  
├── Sélecteur vCard (si plusieurs)
├── Indicateur mode actuel (vCard/Custom)
└── Stats globales vs par vCard

// qr-codes.php - Ajouts
├── QR pointant vers vCard OU Custom URL
├── Génération QR spécifique par mode
└── Avertissement si Custom URL active
```

---

## 📅 **Planning de développement mis à jour**

### **Phase 1 : Dashboard Simple (ACTUELLE)**
- ✅ Interface dashboard de base
- ✅ Gestion vCard unique
- 🔄 **AJOUT : Custom URL basique**

### **Phase 2 : Custom URL (NOUVELLE)**
```
Semaine N+1 (3-4h)
├── Logique de redirection single-virtual_card.php
├── Interface toggle dans vcard-edit.php  
├── API custom URL
└── Tests et validation
```

### **Phase 3 : Multi-vCards (FUTURE - V2)**
```
Phase future (2-3 semaines)
├── Système de détection commande existante
├── Interface choix renouvellement/nouveau
├── Sélecteur vCards dans dashboard
├── Table de liaison Order-vCard
└── Migration données existantes
```

---

## 🔧 **Implémentation Custom URL - Phase 1**

### **Modifications immédiates :**

#### **1. Champ ACF Custom URL :**
```json
{
    "key": "field_custom_url_enabled",
    "label": "Mode Custom URL",
    "name": "custom_url_enabled",
    "type": "true_false",
    "default_value": 0
},
{
    "key": "field_custom_url",
    "label": "URL personnalisée",
    "name": "custom_url",
    "type": "url",
    "conditional_logic": [
        [
            {
                "field": "field_custom_url_enabled",
                "operator": "==",
                "value": "1"
            }
        ]
    ]
}
```

#### **2. Modification single-virtual_card.php :**
```php
// Début du template
$custom_url_enabled = get_post_meta(get_the_ID(), 'custom_url_enabled', true);
$custom_url = get_post_meta(get_the_ID(), 'custom_url', true);

if ($custom_url_enabled && !empty($custom_url)) {
    // Logger la visite pour les stats
    do_action('nfc_track_custom_url_redirect', get_the_ID(), $custom_url);
    
    // Redirection 301
    wp_redirect($custom_url, 301);
    exit;
}

// Sinon, affichage vCard normal...
```

#### **3. Section dans vcard-edit.php :**
```php
<!-- Section Custom URL -->
<div class="dashboard-card mt-4">
    <div class="card-header p-3">
        <h3 class="h6 mb-0">
            <i class="fas fa-external-link-alt me-2"></i>
            Configuration de redirection
        </h3>
    </div>
    <div class="p-4">
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" 
                   id="customUrlEnabled" name="custom_url_enabled"
                   <?php checked(get_post_meta($vcard_id, 'custom_url_enabled', true)); ?>>
            <label class="form-check-label" for="customUrlEnabled">
                Activer la redirection vers une URL personnalisée
            </label>
        </div>
        
        <div id="customUrlSection" class="<?php echo get_post_meta($vcard_id, 'custom_url_enabled', true) ? '' : 'd-none'; ?>">
            <label class="form-label fw-medium">URL de redirection</label>
            <input type="url" name="custom_url" class="form-control" 
                   value="<?php echo esc_attr(get_post_meta($vcard_id, 'custom_url', true)); ?>"
                   placeholder="https://monsite.com/contact">
            <small class="text-muted">
                Quand cette option est activée, votre carte NFC redirigera vers cette URL 
                au lieu d'afficher vos informations de contact.
            </small>
        </div>
    </div>
</div>
```

---

## ✅ **Actions immédiates**

### **Pour continuer le développement actuel :**

1. **Finaliser Overview.php** avec les données de test
2. **Implémenter Custom URL** (3-4h de dev)
3. **Tester les redirections** 
4. **Valider l'interface**

### **Pour Phase 2 (Multi-vCards) :**

1. **Analyser les commandes existantes** et patterns d'usage
2. **Définir UX précise** du sélecteur vCards
3. **Créer la table de liaison** Order-vCard
4. **Développer le workflow** de commande étendu

---

## 🎯 **Priorités mises à jour**

### **IMMÉDIAT (cette semaine) :**
- ✅ Dashboard Overview fonctionnel avec vraies données
- 🔄 Custom URL implementation
- 🔄 Interface Custom URL dans vcard-edit

### **COURT TERME (semaine prochaine) :**
- 📄 Pages Contacts et Statistics
- 🧪 Tests complets Custom URL
- 📖 Documentation utilisateur

### **MOYEN TERME (dans 1 mois) :**
- 🔄 Système multi-vCards complet
- 👥 Interface sélection vCards
- 🛠️ Migration données existantes

---

*Mise à jour CDC v2.1 - Gestion multi-vCards et Custom URL*  
*Évolutions majeures pour comptes simples*  
*Compatible avec développement actuel*