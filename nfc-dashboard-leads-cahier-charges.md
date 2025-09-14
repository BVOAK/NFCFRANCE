# 📋 CAHIER DES CHARGES - Page Leads Dashboard NFC

**Version :** 2.0  
**Date :** 14/09/2025  
**Projet :** NFC France - Dashboard Unifié  
**Objectif :** Créer une page Leads complète qui s'adapte selon les données utilisateur

---

## 🎯 **OBJECTIF PRINCIPAL**

Développer une page **Leads unique** qui :
- **S'adapte automatiquement** selon les vCards de l'utilisateur
- **Conserve le design existant** de `contacts.php`
- **Ajoute les fonctionnalités enterprise** (filtrage par profil, etc.)
- **Gère les états vides** (pas de vCards, pas de contacts)

---

## 📊 **ANALYSE DE L'EXISTANT**

### **✅ Ce qui fonctionne dans contacts.php :**
- Interface responsive avec cartes et tableau
- Système de pagination
- Modals pour ajout/édition
- JavaScript pour filtrage et tri
- CSS avec contacts-manager.css
- Export/Import CSV

### **❌ Ce qui doit être adapté :**
- Ajout du filtrage par profil vCard
- Gestion multi-profils utilisateur
- États vides intelligents
- JavaScript qui filtre les résultats automatiquement

---

## 🏗️ **SPÉCIFICATIONS TECHNIQUES**

### **F1. Structure des Fichiers**

#### **F1.1 Organisation des Templates**
```
wp-content/plugins/gtmi-vcard/templates/dashboard/
├── leads.php                    # 🆕 Template principal adaptatif
├── partials/
│   ├── leads-header.php         # Header avec stats
│   ├── leads-filters.php        # Barre de filtrage
│   ├── leads-table.php          # Tableau des contacts
│   ├── leads-modals.php         # Modals (ajout, import, détails)
│   └── no-products-state.php    # État vide (pas de vCards)
└── simple/
    └── contacts.php             # 📦 Ancien template (fallback)
```

#### **F1.2 Modification Dashboard Manager**
```php
// Dans class-dashboard-manager.php
private function render_contacts_page($vcard)
{
    // Template unique adaptatif
    $template_path = $this->plugin_path . 'templates/dashboard/leads.php';
    
    if (file_exists($template_path)) {
        include $template_path;
    } else {
        // Fallback vers ancien système
        $this->render_simple_contacts($vcard);
    }
}
```

### **F2. Logique Adaptative**

#### **F2.1 Détection des États**
```php
$user_id = get_current_user_id();
$user_vcards = nfc_get_user_vcard_profiles($user_id);

// État 1: Aucune vCard → Afficher invitation commande
if (empty($user_vcards)) {
    include 'partials/no-products-state.php';
    return;
}

// État 2: Avec vCards → Interface complète
$contacts = nfc_get_enterprise_contacts($user_id, $selected_vcard, 100);

// État 2a: Pas de contacts → État vide avec conseils
if (empty($contacts)) {
    include 'partials/empty-contacts-state.php';
} else {
    // État 2b: Avec contacts → Interface complète
    include 'partials/leads-table.php';
}
```

#### **F2.2 Interface selon Nombre de vCards**
```php
// 1 vCard → Interface simple (comme contacts.php existant)
if (count($user_vcards) === 1) {
    $show_profile_filter = false;
    $page_title = "Mes Contacts";
}

// Plusieurs vCards → Interface multi-profils
else {
    $show_profile_filter = true;
    $page_title = "Contacts Multi-Profils";
}
```

### **F3. Interface Utilisateur**

#### **F3.1 Header avec Stats (Identique à contacts.php)**
```html
<div class="contacts-header">
    <div class="row align-items-center">
        <div class="col">
            <h2><i class="fas fa-users me-2"></i>Mes Contacts</h2>
            <p class="text-muted mb-0">Gestion de vos contacts professionnels</p>
        </div>
        <div class="col-auto">
            <!-- Stats cards identiques à contacts.php -->
        </div>
    </div>
</div>
```

#### **F3.2 Filtres Avancés (Extension de contacts.php)**
```html
<div class="contacts-filters">
    <!-- Filtre existant de contacts.php -->
    <div class="search-box">...</div>
    
    <!-- 🆕 NOUVEAU : Filtre par profil (si plusieurs vCards) -->
    <?php if ($show_profile_filter): ?>
    <div class="profile-filter">
        <select class="form-select" id="profileFilter">
            <option value="">Tous les profils (<?= $total_contacts ?>)</option>
            <?php foreach ($user_vcards as $vcard): ?>
                <option value="<?= $vcard['vcard_id'] ?>">
                    <?= nfc_format_vcard_full_name($vcard['vcard_data']) ?> (<?= count_contacts($vcard['vcard_id']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
</div>
```

#### **F3.3 Tableau (Identique + Colonne Profil)**
```html
<table class="table" id="contactsTable">
    <thead>
        <tr>
            <!-- Colonnes existantes de contacts.php -->
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Entreprise</th>
            
            <!-- 🆕 NOUVEAU : Colonne profil source (si plusieurs vCards) -->
            <?php if ($show_profile_filter): ?>
            <th>Profil Source</th>
            <?php endif; ?>
            
            <th>Actions</th>
        </tr>
    </thead>
    <!-- Corps du tableau identique -->
</table>
```

### **F4. JavaScript et Interactions**

#### **F4.1 Conservation du JavaScript contacts.php**
```javascript
// Conserver TOUTES les fonctions de contacts.php :
- NFCContacts.init()
- NFCContacts.loadContacts()  
- NFCContacts.applyFilters()
- NFCContacts.renderTable()
- NFCContacts.exportCSV()
- NFCContacts.importCSV()
// etc.
```

#### **F4.2 Extensions JavaScript**
```javascript
// 🆕 NOUVEAU : Filtrage par profil
function filterByProfile(profileId) {
    // Appliquer le filtre par profil
    // Conserver les autres filtres actifs
    NFCContacts.applyFilters();
}

// 🆕 NOUVEAU : Adapter les exports selon profil
function exportContacts(scope) {
    const activeProfile = document.getElementById('profileFilter').value;
    
    switch(scope) {
        case 'current':
            // Exporter avec filtres actuels
            break;
        case 'profile':
            // Exporter profil spécifique
            break;
        case 'all':
            // Exporter tout
            break;
    }
}
```

#### **F4.3 Correction du Bug de Filtrage**
```javascript
// PROBLÈME IDENTIFIÉ : Les contacts disparaissent après chargement
// CAUSE : Filtres automatiques qui s'appliquent mal
// SOLUTION : Réinitialiser les filtres au chargement

document.addEventListener('DOMContentLoaded', function() {
    // Désactiver les filtres automatiques au début
    NFCContacts.disableAutoFilter = true;
    
    // Charger les contacts
    NFCContacts.loadContacts();
    
    // Réactiver les filtres après chargement
    setTimeout(() => {
        NFCContacts.disableAutoFilter = false;
    }, 100);
});
```

---

## 🚀 **PLAN DE DÉVELOPPEMENT (1 jour)**

### **PHASE 1 : Template Principal (3h)**

#### **Étape 1.1 : Créer leads.php (1h)**
**Prompt Développement :**
```
Contexte : Créer le template principal leads.php basé sur contacts.php existant.

Objectif : Interface identique à contacts.php + logique adaptative.

Tâches :
1. Copier la structure HTML de contacts.php
2. Ajouter la logique de détection d'état :
   - Pas de vCards → no-products-state.php
   - Pas de contacts → empty-contacts-state.php  
   - Avec contacts → interface complète
3. Conserver exactement :
   - Les classes CSS existantes
   - La structure des modals
   - Les boutons et actions
   - Le système de pagination
4. Ajouter uniquement :
   - Filtre par profil (si plusieurs vCards)
   - Colonne "Profil Source" (si plusieurs vCards)
   - Logique d'adaptation du titre

Fichiers à créer :
- templates/dashboard/leads.php
- partials/no-products-state.php
- partials/empty-contacts-state.php

Interface exactement identique à contacts.php existant.
```

#### **Étape 1.2 : Créer les partials (1h)**
**Prompt Développement :**
```
Contexte : Créer les états vides et composants réutilisables.

Objectif : États adaptatifs selon situation utilisateur.

Tâches :
1. no-products-state.php :
   - Message "Aucune carte NFC"
   - Lien vers boutique
   - Design cohérent avec contacts.php
2. empty-contacts-state.php :
   - Message "Aucun contact reçu"
   - Conseils pour recevoir des contacts
   - Bouton "Ajouter contact manuel"
3. Structure réutilisable :
   - Même classes CSS que contacts.php
   - Icons Font Awesome identiques
   - Responsive design

Design s'intègre parfaitement dans contacts.php existant.
```

#### **Étape 1.3 : Adapter le JavaScript (1h)**
**Prompt Développement :**
```
Contexte : Adapter le JavaScript de contacts.php pour le multi-profils.

Objectif : Fonctionnalités identiques + filtrage par profil.

Tâches :
1. Copier TOUT le JavaScript de contacts.php
2. Identifier et corriger le bug de filtrage automatique :
   - Désactiver filtres au chargement initial
   - Réactiver après rendu complet des contacts
3. Ajouter fonction filterByProfile() :
   - Compatible avec filtres existants
   - Ne reset pas les autres filtres
4. Adapter les exports :
   - Export par profil
   - Export avec filtres actuels
   - Conserver exports existants
5. Tests de non-régression :
   - Toutes les fonctions de contacts.php marchent
   - Pas de conflits avec nouveaux filtres

JavaScript 100% compatible avec contacts.php + nouvelles fonctions.
```

### **PHASE 2 : Intégration Dashboard (1h)**

#### **Étape 2.1 : Modifier Dashboard Manager (30min)**
**Prompt Développement :**
```
Contexte : Intégrer leads.php dans le dashboard manager.

Objectif : Routing automatique vers nouveau template.

Tâche :
- Modifier render_contacts_page() dans class-dashboard-manager.php
- Pointer vers templates/dashboard/leads.php
- Fallback vers contacts.php si problème
- Logs pour debug

Code minimal, maximum compatibilité.
```

#### **Étape 2.2 : Tests et Debug (30min)**
**Prompt Développement :**
```
Contexte : Tester la page avec différents profils utilisateur.

Objectif : Fonctionnement parfait dans tous les cas.

Tests :
1. Utilisateur sans vCards → no-products-state
2. Utilisateur 1 vCard sans contacts → empty-contacts-state  
3. Utilisateur 1 vCard avec contacts → interface simple
4. Utilisateur plusieurs vCards → interface multi-profils
5. Filtres et exports fonctionnent
6. JavaScript sans erreurs

Debug et corrections mineures.
```

---

## ✅ **CRITÈRES DE VALIDATION**

### **Design et UX**
- [ ] Interface **identique visuellement** à contacts.php existant
- [ ] CSS contacts-manager.css appliqué correctement
- [ ] Responsive design fonctionnel
- [ ] Modals et interactions identiques

### **Fonctionnalités**
- [ ] Toutes les fonctions de contacts.php préservées
- [ ] Filtrage par profil vCard (si plusieurs)
- [ ] Export/Import CSV fonctionnels
- [ ] États vides adaptatifs
- [ ] JavaScript sans régressions

### **Compatibilité**
- [ ] Utilisateurs existants : aucun changement visible
- [ ] Utilisateurs multi-vCards : nouvelles fonctionnalités
- [ ] Fallback vers contacts.php si erreur
- [ ] Performance identique ou meilleure

### **Code**
- [ ] Architecture propre avec partials
- [ ] Code réutilisable et maintenable
- [ ] Logs pour debugging
- [ ] Documentation des modifications

---

## 📁 **LIVRABLES ATTENDUS**

1. **templates/dashboard/leads.php** - Template principal adaptatif
2. **partials/no-products-state.php** - État sans vCards
3. **partials/empty-contacts-state.php** - État sans contacts  
4. **Modification class-dashboard-manager.php** - Routing
5. **Tests complets** - Tous les cas d'usage validés

---

## 🎯 **PROCHAINES ÉTAPES APRÈS LEADS**

1. **Page Statistics** avec même approche adaptative
2. **Page Overview** unifiée multi-profils
3. **Système Avis Google** (Phase 2)

---

*Ce cahier des charges assure une évolution en douceur du dashboard existant vers un système multi-profils, sans casser l'expérience utilisateur actuelle.*