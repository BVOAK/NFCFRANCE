# Plan d'action - Dashboard Client NFC
**Extension du plugin gtmi_vcard avec interface utilisateur complète - MISE À JOUR 1**

---

## ✅ **RÉALISÉ - Structure de base (Jour 1)**

### 1. Classes PHP principales créées
- ✅ **NFC_Dashboard_Manager** : Classe principale avec intégration WooCommerce
- ✅ **Hooks WooCommerce** : Endpoint `nfc-dashboard` dans Mon Compte
- ✅ **API Integration** : Connexion aux endpoints existants du plugin
- ✅ **AJAX Handlers** : Gestion des actions dashboard côté serveur

### 2. Templates créés
- ✅ **Layout principal** : `templates/dashboard/layout/dashboard-layout.php`
- ✅ **Sidebar navigation** : Navigation entre les pages avec stats rapides
- ✅ **Structure responsive** : Compatible Bootstrap + UICore Pro

### 3. Assets frontend
- ✅ **CSS Dashboard** : `assets/css/dashboard.css` avec design system NFC
- ✅ **JavaScript Core** : `assets/js/dashboard/dashboard-core.js`
- ✅ **Intégration Chart.js** : Pour les graphiques statistiques

### 4. Fonctionnalités de base
- ✅ **Authentification** : Vérification utilisateur connecté
- ✅ **Association vCard** : Récupération automatique des vCards utilisateur
- ✅ **Navigation** : Système de routing entre pages dashboard
- ✅ **Notifications** : Système de messages utilisateur
- ✅ **Loading states** : États de chargement avec overlay

---

## 📂 **FICHIERS CRÉÉS**

### Structure ajoutée au plugin gtmi_vcard
```
gtmi-vcard/
├── includes/dashboard/
│   └── class-dashboard-manager.php          ✅ CRÉÉ
│
├── templates/dashboard/
│   └── layout/
│       └── dashboard-layout.php             ✅ CRÉÉ
│
└── assets/
    ├── css/
    │   └── dashboard.css                    ✅ CRÉÉ
    └── js/dashboard/
        └── dashboard-core.js                ✅ CRÉÉ
```

### Intégration dans gtmi-vcard.php
```php
// À AJOUTER dans gtmi-vcard.php
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/class-dashboard-manager.php';
```

---

## 🎯 **PROCHAINES ÉTAPES - Phase 1 Suite**

### **URGENT - Finalisation intégration (Jour 2)**
- [ ] **Inclure dashboard-manager** dans le fichier principal `gtmi-vcard.php`
- [ ] **Tester l'endpoint** `/mon-compte/nfc-dashboard/` 
- [ ] **Vérifier navigation** entre les pages
- [ ] **Debug API calls** avec vCards existantes

### **Page Vue d'ensemble (Jour 2-3)**
- [ ] Créer `templates/dashboard/simple/dashboard.php`
- [ ] Cards statistiques avec données réelles
- [ ] Actions rapides (liens vers autres pages)
- [ ] Aperçu vCard + lien public

### **Page Ma vCard (Jour 3-4)**
- [ ] Créer `templates/dashboard/simple/vcard-edit.php`
- [ ] Formulaire édition avec tous les champs ACF
- [ ] Preview temps réel de la vCard
- [ ] Sauvegarde via API POST
- [ ] Upload photo de profil

---

## 🔧 **CONFIGURATION REQUISE**

### Dans gtmi-vcard.php
```php
// AJOUTER cette ligne après les autres require_once
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/class-dashboard-manager.php';
```

### Flush des règles de réécriture
- Aller dans **WP Admin > Réglages > Permaliens**
- Cliquer "Enregistrer" pour flush les règles
- Ou désactiver/réactiver le plugin

### Test de l'intégration
1. Se connecter sur le site
2. Aller dans **Mon Compte**
3. Chercher l'onglet **"Dashboard NFC"**
4. Vérifier que ça charge sans erreur 404

---

## 📊 **APIs DISPONIBLES** (plugin existant)

### Endpoints testés et fonctionnels
```
GET  /wp-json/gtmi_vcard/v1/vcard/[ID]           # Récupérer vCard
POST /wp-json/gtmi_vcard/v1/vcard/[ID]           # Modifier vCard
GET  /wp-json/gtmi_vcard/v1/leads/[VCARD_ID]     # Contacts reçus
GET  /wp-json/gtmi_vcard/v1/statistics/[VCARD_ID] # Statistiques
```

### Structure vCard (champs ACF disponibles)
- Infos personnelles : nom, prénom, fonction, entreprise
- Contact : email, téléphone, site web
- Réseaux sociaux : LinkedIn, etc.
- Photo de profil
- Description

---

## 🎨 **DESIGN SYSTEM DÉFINI**

### Couleurs NFC France
```css
--nfc-primary: #0040C1;      /* Bleu principal */
--nfc-secondary: #667eea;    /* Bleu secondaire */
--nfc-success: #10b981;      /* Vert succès */
--nfc-background: #f8fafc;   /* Fond gris clair */
--nfc-sidebar: #1a202c;      /* Sidebar sombre */
```

### Layout responsive
- **Desktop** : Sidebar gauche + contenu principal
- **Mobile** : Navigation horizontale + contenu full-width
- **Compatibilité** : Bootstrap 5 + UICore Pro

---

## ⚠️ **POINTS D'ATTENTION**

### 1. Intégration plugin existant
- **NE PAS modifier** les fichiers existants du plugin
- **SEULEMENT ajouter** les nouveaux fichiers dans `/includes/dashboard/`
- **Respecter** l'architecture API existante

### 2. Performance
- **Lazy loading** des stats pour éviter la surcharge
- **Cache** des données fréquemment utilisées
- **Optimisation** des requêtes API

### 3. Sécurité
- **Vérification permissions** : chaque utilisateur ne voit que ses vCards
- **Nonces WordPress** pour tous les formulaires
- **Sanitization** de toutes les données utilisateur

---

## 🧪 **TESTS À EFFECTUER**

### Tests critiques Phase 1
- [ ] **Accès dashboard** depuis Mon Compte WooCommerce
- [ ] **Navigation** entre les pages sans erreur 404
- [ ] **APIs** : récupération vCard, stats, contacts
- [ ] **Responsive** : mobile + desktop
- [ ] **Permissions** : utilisateur voit seulement ses vCards

### URLs de test
```
http://nfcfrance.loc/mon-compte/nfc-dashboard/
http://nfcfrance.loc/mon-compte/nfc-dashboard/?page=vcard-edit
http://nfcfrance.loc/wp-json/gtmi_vcard/v1/vcard/[ID]
```

---

## 📈 **MÉTRIQUES DE SUCCÈS Phase 1**

### Critères d'acceptation MVP
- [x] ✅ **Structure** : Classes et templates créés
- [ ] **Intégration** : Accessible depuis WooCommerce
- [ ] **Navigation** : Toutes les pages chargent
- [ ] **API** : Données vCard récupérées et affichées
- [ ] **Design** : Interface cohérente et responsive
- [ ] **Performance** : < 3s de chargement initial

### KPIs techniques
- **0 erreur 404** sur les pages dashboard
- **API response time** < 500ms
- **Mobile compatibility** 100%
- **Browser support** : Chrome, Firefox, Safari, Edge

---

## 🚀 **PROCHAINE SESSION**

### Actions immédiates
1. **Intégrer dashboard-manager.php** dans le plugin principal
2. **Tester l'endpoint** Mon Compte > Dashboard NFC
3. **Créer page Vue d'ensemble** avec vraies donnée
4. **Debug** les appels API si nécessaire

### Fichiers à créer ensuite
- `templates/dashboard/simple/dashboard.php` (Vue d'ensemble)
- `assets/js/dashboard/vcard-editor.js` (Éditeur vCard)
- `templates/dashboard/simple/vcard-edit.php` (Formulaire)

**PRÊT POUR LA SUITE ! Structure solide en place 🎯**

---

*Mise à jour 1 - Dashboard Client NFC*  
*Structure de base complète - Intégration WooCommerce prête*  
*Prochaine étape : Finalisation endpoints + Pages principales*