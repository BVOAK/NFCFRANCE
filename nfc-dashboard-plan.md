# Plan d'action - Dashboard Client NFC
**Extension du plugin gtmi_vcard avec interface utilisateur complète**

---

## 📋 Vue d'ensemble du projet

### Objectifs principaux
- Développer l'interface frontend manquante du plugin gtmi_vcard
- Créer un dashboard client intégré à WooCommerce
- Préparer l'architecture pour la gestion multi-tenant (entreprises)

### Architecture actuelle (existante)
- ✅ Plugin backend complet (API REST, custom post types, stats)
- ✅ Intégration WooCommerce (création auto vCard à la commande)
- ✅ Gestion leads et statistiques
- ❌ Interface client frontend (à développer)
- ❌ Gestion QR codes (à développer)

---

## 🎯 Phase 1 : MVP Dashboard Simple (2-3 semaines)

### 1.1 Finalisation Layout Principal (3-4 jours)
**Objectif :** Interface de base avec navigation

**Livrables :**
- [ ] Template principal avec sidebar navigation
- [ ] Header avec breadcrumb WooCommerce
- [ ] Navigation responsive (desktop + mobile)
- [ ] Système de routing entre pages
- [ ] Intégration CSS/JS avec thème existant

**Fichiers à créer :**
```
/templates/client/
├── client-dashboard.php      # Layout principal
├── client-header.php         # Header dédié
├── client-navigation.php     # Sidebar navigation
└── assets/
    ├── client-dashboard.css
    └── client-dashboard.js
```

### 1.2 Page "Ma vCard" (4-5 jours)
**Objectif :** Édition du profil vCard

**Fonctionnalités :**
- [ ] Formulaire d'édition des infos personnelles
- [ ] Upload/modification photo de profil
- [ ] Preview de la vCard en temps réel
- [ ] Sauvegarde via API existante (`PUT /wp-json/gtmi_vcard/v1/vcard/[ID]`)
- [ ] Gestion des erreurs et validation

**Champs à gérer :**
- Nom, prénom, fonction, entreprise
- Photo de profil
- Téléphone, email, site web
- Réseaux sociaux (LinkedIn, etc.)
- Description personnelle

### 1.3 Page "QR Codes" (3-4 jours)
**Objectif :** Gestion et téléchargement des QR codes

**Fonctionnalités :**
- [ ] Génération automatique QR code (URL vCard)
- [ ] Preview du QR code avec différents styles
- [ ] Téléchargement multiple formats (PNG, SVG, PDF)
- [ ] Personnalisation design (couleurs, logo)
- [ ] Statistiques de scan

**Intégration technique :**
- Bibliothèque de génération QR (PHP + JS)
- Stockage des QR codes dans `/uploads/nfc-qrcodes/`
- Tracking des scans via API statistiques

### 1.4 Page "Contacts" (2-3 jours)
**Objectif :** Gestion des leads reçus

**Fonctionnalités :**
- [ ] Liste des contacts reçus via API (`GET /wp-json/gtmi_vcard/v1/leads/[VCARD_ID]`)
- [ ] Filtres et recherche
- [ ] Export CSV des contacts
- [ ] Actions sur les contacts (marquer lu, supprimer)
- [ ] Pagination et tri

### 1.5 Page "Statistiques" (2-3 jours)
**Objectif :** Analytics détaillées

**Fonctionnalités :**
- [ ] Graphiques de vues par période
- [ ] Top interactions (LinkedIn, email, téléphone)
- [ ] Répartition géographique des vues
- [ ] Comparaison périodes (semaine, mois)
- [ ] Export des données

**Intégration :**
- API statistiques existante
- Bibliothèque Chart.js pour les graphiques
- Filtres par dates

### 1.6 Intégration et tests (3-4 jours)
- [ ] Tests avec données réelles
- [ ] Optimisation performance
- [ ] Corrections bugs
- [ ] Documentation utilisateur
- [ ] Formation/démo client

---

## 🏢 Phase 2 : Architecture Multi-tenant (3-4 semaines)

### 2.1 Extension Base de données (1 semaine)
**Objectif :** Préparer la structure pour les entreprises

**Nouvelles tables :**
```sql
-- Table des entreprises
wp_nfc_companies (
    id, name, admin_user_id, logo_url, 
    template_settings, subscription_plan, 
    max_employees, created_at
)

-- Relations utilisateurs-entreprises
wp_nfc_user_companies (
    user_id, company_id, role, 
    invited_at, joined_at, status
)
```

**Migration :**
- [ ] Script de migration des vCards existantes
- [ ] Conservation compatibilité API
- [ ] Tests de non-régression

### 2.2 Système de rôles et permissions (1 semaine)
**Objectif :** Gestion des accès multi-niveaux

**Rôles WordPress personnalisés :**
- [ ] `nfc_company_admin` : Admin entreprise
- [ ] `nfc_employee` : Employé d'entreprise
- [ ] Permissions granulaires par rôle

**Classes PHP :**
```php
class NFC_User_Manager {
    public function get_user_type($user_id);
    public function can_manage_company($user_id, $company_id);
    public function get_user_vcards($user_id);
    public function get_company_employees($company_id);
}

class NFC_Company_Manager {
    public function create_company($admin_user_id, $data);
    public function invite_employee($email, $company_id);
    public function assign_vcard($user_id, $vcard_id);
}
```

### 2.3 Dashboard Entreprise (1.5 semaines)
**Objectif :** Interface admin entreprise

**Pages spécifiques entreprise :**
- [ ] Dashboard global entreprise
- [ ] Gestion des employés (invitation, suppression)
- [ ] Attribution des cartes vCard
- [ ] Templates d'entreprise
- [ ] Analytics consolidées
- [ ] Facturation centralisée

### 2.4 Dashboard Employé (0.5 semaine)
**Objectif :** Interface simplifiée employé

**Fonctionnalités :**
- [ ] Accès à sa vCard uniquement
- [ ] Contacts personnels
- [ ] Stats individuelles
- [ ] Vue lecture seule infos entreprise

---

## 🔧 Spécifications techniques

### Architecture système
```
WordPress + WooCommerce
├── Plugin gtmi_vcard (existant)
│   ├── API REST ✅
│   ├── Custom Post Types ✅
│   ├── Gestion commandes ✅
│   └── Stats/leads ✅
│
├── Extension Dashboard (à développer)
│   ├── Templates frontend
│   ├── Assets CSS/JS
│   ├── Intégration WP/WC
│   └── Gestion multi-tenant
│
└── Intégration
    ├── Hooks WooCommerce
    ├── Pages compte client
    └── Routing personnalisé
```

### Stack technique
- **Backend :** PHP 8.1+, WordPress 6.x, WooCommerce
- **Frontend :** Bootstrap 5.3, JavaScript ES6+, Chart.js
- **QR Codes :** Bibliothèque PHP QR Code + Canvas JS
- **APIs :** REST existantes + extensions
- **Sécurité :** Nonces WordPress, validation, sanitization

---

## 📊 Estimation temps et ressources

### Phase 1 : MVP Simple
| Tâche | Estimation | Priorité |
|-------|------------|----------|
| Layout principal | 3-4 jours | 🔴 Critique |
| Page Ma vCard | 4-5 jours | 🔴 Critique |
| Page QR Codes | 3-4 jours | 🟡 Important |
| Page Contacts | 2-3 jours | 🟡 Important |
| Page Statistiques | 2-3 jours | 🟢 Normal |
| Tests et intégration | 3-4 jours | 🔴 Critique |
| **Total Phase 1** | **17-23 jours** | **~3-4 semaines** |

### Phase 2 : Multi-tenant
| Tâche | Estimation | Priorité |
|-------|------------|----------|
| Extension BDD | 5-7 jours | 🔴 Critique |
| Système rôles | 5-7 jours | 🔴 Critique |
| Dashboard Entreprise | 7-10 jours | 🟡 Important |
| Dashboard Employé | 2-3 jours | 🟡 Important |
| **Total Phase 2** | **19-27 jours** | **~4-5 semaines** |

---

## 🎯 Jalons et livrables

### Jalon 1 : Layout et navigation (Semaine 1)
**Livrable :** Interface de base fonctionnelle
- Template principal avec sidebar
- Navigation entre pages
- Intégration WooCommerce

### Jalon 2 : Pages principales (Semaines 2-3)
**Livrable :** Dashboard simple complet
- Toutes les pages fonctionnelles
- Intégration API complète
- Tests utilisateur

### Jalon 3 : Architecture multi-tenant (Semaines 4-6)
**Livrable :** Système entreprise de base
- Structure BDD étendue
- Rôles et permissions
- Dashboard entreprise MVP

### Jalon 4 : Finalisation (Semaines 7-8)
**Livrable :** Système complet
- Toutes fonctionnalités entreprise
- Tests complets
- Documentation

---

## 🔍 Risques et dépendances

### Risques techniques
| Risque | Impact | Probabilité | Mitigation |
|--------|--------|-------------|------------|
| Conflits thème WordPress | Moyen | Moyenne | Tests sur environnement de production |
| Performance avec multi-tenant | Élevé | Faible | Architecture optimisée, cache |
| Compatibilité navigateurs | Faible | Moyenne | Tests cross-browser |
| Sécurité accès entreprise | Élevé | Faible | Audit sécurité, tests permissions |

### Dépendances
- **Plugin ACF** : Requis pour les champs personnalisés
- **WooCommerce** : Base du système de commandes
- **Thème compatible** : Bootstrap ou framework CSS
- **Serveur PHP 8.1+** : Fonctionnalités modernes

---

## ✅ Critères de validation

### Phase 1 : MVP Simple
- [ ] Client peut accéder à son dashboard depuis son compte WooCommerce
- [ ] Client peut modifier sa vCard et voir les changements en temps réel
- [ ] Client peut télécharger son QR code en différents formats
- [ ] Client peut consulter ses contacts et statistiques
- [ ] Interface responsive sur mobile/desktop
- [ ] Performance < 3s de chargement

### Phase 2 : Multi-tenant
- [ ] Admin entreprise peut inviter des employés
- [ ] Admin peut attribuer des cartes à ses employés
- [ ] Employé accède uniquement à sa vCard
- [ ] Admin voit les stats consolidées de l'entreprise
- [ ] Facturation centralisée fonctionne
- [ ] Permissions respectées selon les rôles

---

## 🚀 Prochaines étapes immédiates

### Cette semaine
1. **Finaliser layout principal** avec sidebar navigation
2. **Commencer page "Ma vCard"** avec formulaire d'édition
3. **Tester intégration** avec plugin existant

### Validation needed
- [ ] Design system final (couleurs, polices, composants)
- [ ] Spécifications détaillées page "Ma vCard"
- [ ] Architecture QR codes (stockage, formats)
- [ ] Intégration thème WordPress existant

**Prêt à démarrer la Phase 1 ! 🎯**