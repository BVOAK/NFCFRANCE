# 🚀 PLAN D'ACTION - Dashboard Unifié NFC
**Objectif :** Dashboard unique gérant vCard ET Avis Google, suppression logique Simple/Enterprise

---

## 📅 **PHASE 1 : NETTOYAGE ET REFACTORING (1 jour)**

### **🔧 Étape 1.1 : Suppression Logique Simple/Enterprise**
**Durée :** 2h | **Prompt :**
```
Contexte : Supprimer toute la logique "Simple vs Enterprise" du code existant.

Objectif : Dashboard unique pour tous les utilisateurs (1 carte ou X cartes).

Tâches :
1. Supprimer functions: nfc_get_dashboard_type(), nfc_user_has_enterprise_cards(), nfc_get_user_cards()
2. Remplacer par nfc_get_user_vcard_profiles() (toujours des profils individuels)
3. Nettoyer tous les appels à ces fonctions obsolètes
4. Mettre à jour class-dashboard-manager.php pour utiliser la logique unifiée
5. Tests de régression pour vérifier qu'aucune fonctionnalité n'est cassée

Critères de validation :
✅ Aucune référence à "simple" ou "enterprise" dans les fonctions utilisateur
✅ Dashboard identique pour 1 carte ou 10 cartes (juste plus de lignes)
✅ Fonctions existantes continuent de fonctionner
```

### **🏗️ Étape 1.2 : Architecture Dashboard Unifié**
**Durée :** 3h | **Prompt :**
```
Contexte : Restructurer le dashboard pour gérer 2 sections distinctes: vCard + Avis Google.

Objectif : 1 dashboard avec 2 sections séparées, code modulaire et extensible.

Tâches :
1. Modifier class-dashboard-manager.php pour supporter 2 types de données:
   - get_user_vcard_profiles($user_id) → profils vCard individuels
   - get_user_google_reviews_profiles($user_id) → profils Avis Google partagés
2. Créer templates modulaires:
   - templates/dashboard/sections/vcard-profiles-section.php
   - templates/dashboard/sections/google-reviews-section.php
3. Interface utilisateur adaptative:
   - Si utilisateur a vCard: section "Mes Profils vCard" visible
   - Si utilisateur a Avis Google: section "Mes Profils Avis Google" visible
   - Si les deux: dashboard complet avec 2 sections
4. Navigation adaptée dans la sidebar
5. CSS pour les 2 types de sections

Structure finale:
```
Dashboard Marc (commande mixte: 5 vCard + 8 Avis Google)
├── Section "Mes Profils vCard" (5 lignes)
│   ├── Jean Dupont (NFC1234-1) [Modifier][Stats][Leads]
│   ├── Marie Martin (NFC1234-2) [Modifier][Stats][Leads]  
│   └── ...
└── Section "Mes Profils Avis Google" (1 ligne)
    └── Restaurant La Table (8 emplacements) [Configurer][Analytics][Mapping]
```

Critères de validation :
✅ Dashboard s'adapte selon les types de produits de l'utilisateur
✅ Code modulaire et extensible pour futurs types de produits
✅ UX claire et intuitive
✅ Aucune régression sur les fonctionnalités existantes
```

### **📊 Étape 1.3 : Fonctions Communes et Helpers**
**Durée :** 3h | **Prompt :**
```
Contexte : Créer fonctions communes réutilisables pour les 2 types de produits.

Objectif : Code DRY, maintenance facilité, éviter la duplication.

Tâches :
1. Créer nfc-shared-functions.php avec fonctions communes:
   - nfc_get_user_products_summary($user_id) → résumé des produits par type
   - nfc_format_product_status($status) → formatage uniforme des statuts
   - nfc_generate_product_url($type, $id) → URLs uniformes
   - nfc_get_product_stats_summary($type, $id) → stats rapides uniformes
2. Helpers d'interface:
   - nfc_render_status_badge($status) → badges HTML uniformes
   - nfc_render_action_buttons($type, $id) → boutons d'actions adaptés
   - nfc_render_stats_cards($stats) → cartes de statistiques réutilisables
3. Fonctions de validation:
   - nfc_user_can_access_product($user_id, $type, $product_id) → permissions
   - nfc_validate_product_data($type, $data) → validation des données
4. Documentation code et exemples d'utilisation

Architecture:
```
includes/
├── nfc-shared-functions.php (nouvelles fonctions communes)
├── enterprise/
│   ├── vcard-functions.php (spécifique vCard)
│   └── google-reviews-functions.php (spécifique Avis Google)
└── dashboard/
    ├── vcard-handlers.php (AJAX vCard)
    └── google-reviews-handlers.php (AJAX Avis Google)
```

Critères de validation :
✅ Fonctions communes utilisées dans les 2 systèmes
✅ Réduction significative de code dupliqué
✅ Interface utilisateur cohérente entre les 2 types
✅ Code documenté et maintenable
```

---

## 📅 **PHASE 2 : SYSTÈME AVIS GOOGLE (2 jours)**

### **🏢 Étape 2.1 : Custom Post Type Google Reviews**
**Durée :** 4h | **Prompt :**
```
Contexte : Implémenter le système complet Avis Google avec Custom Post Type.

Objectif : 1 profil partagé pour X cartes/plaques Avis Google.

Tâches :
1. Créer Custom Post Type 'google_reviews_profile':
   - Champs ACF: google_business_url, company_name, total_elements
   - URLs SEO: /avis-google/restaurant-la-table/
   - Permissions et capacités
2. Créer table wp_google_reviews_elements:
   - Liaison profil ↔ éléments physiques (AG1234-1, AG1234-2...)
   - Tracking par élément (scans, redirections, performance)
3. Finaliser process_google_reviews_products():
   - Création du post google_reviews_profile
   - Insertion des éléments dans la table
   - Métadonnées et configuration initiale
4. Système de redirection:
   - URL scan: /avis-google/AG1234-1/ → tracking puis redirect vers Google
   - Analytics par élément
5. Tests création profil Avis Google complet

Base de données:
```sql
-- Table des éléments NFC Avis Google
CREATE TABLE wp_google_reviews_elements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    element_identifier VARCHAR(20) UNIQUE, -- AG1234-1, AGP1234-2
    element_type ENUM('card', 'plaque'),
    element_position INT,
    element_label VARCHAR(100) DEFAULT 'À configurer',
    scans_count INT DEFAULT 0,
    redirections_count INT DEFAULT 0,
    last_scan_at DATETIME NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

Critères de validation :
✅ Commande 8 cartes Avis Google → 1 profil + 8 éléments en BDD
✅ URLs de scan fonctionnelles avec tracking
✅ Dashboard section "Avis Google" affiche le profil créé
✅ Intégration seamless avec le système existant
```

### **📈 Étape 2.2 : Dashboard Avis Google**
**Durée :** 4h | **Prompt :**
```
Contexte : Interface utilisateur complète pour gérer les profils Avis Google.

Objectif : Section dashboard spécialisée avec mapping et analytics.

Tâches :
1. Template section Avis Google:
   - Vue profil avec URL Google Business
   - Mapping des éléments (AG1234-1 → "Table terrasse")
   - Stats globales + détail par élément
   - Configuration URL Google Business
2. Interface de mapping:
   - Liste des identifiants avec champs libellés
   - Drag & drop pour organiser (optionnel)
   - Sauvegarde AJAX des modifications
3. Analytics spécialisés:
   - Graphiques scans par élément (Chart.js)
   - Top performers (quel emplacement génère plus de scans)
   - Évolution temporelle des redirections
4. Actions par profil:
   - [Configurer URL] → modal de configuration Google Business
   - [Analytics] → page dédiée avec graphiques détaillés
   - [Export] → CSV des données de scanning
5. Tests interface complète

Interface utilisateur:
```
Section "Mes Profils Avis Google"
├── Restaurant La Table (8 éléments) [Configurer][Analytics][Export]
│   ├── URL Google: https://g.page/r/restaurant-la-table/review
│   ├── Mapping: AG1234-1→"Table 1", AG1234-2→"Table 2"...
│   ├── Stats: 142 scans (87% conversion), Top: "Comptoir" (34 scans)
│   └── Actions: [Modifier mapping][Voir analytics détaillées]
```

Critères de validation :
✅ Interface intuitive pour mapper les emplacements
✅ Analytics temps réel fonctionnels
✅ Configuration Google Business URL persistante
✅ Export des données fonctionnel
```

---

## 📅 **PHASE 3 : INTÉGRATION ET POLISH (1 jour)**

### **🔗 Étape 3.1 : Intégration Dashboard Complet**
**Durée :** 4h | **Prompt :**
```
Contexte : Finaliser l'intégration dashboard complet avec les 2 sections.

Objectif : Expérience utilisateur fluide et cohérente.

Tâches :
1. Révision class-dashboard-manager.php:
   - Support complet des 2 types de sections
   - Navigation adaptative selon les produits utilisateur
   - Gestion des états (pas de produits, produits mixtes, etc.)
2. Unification CSS et JavaScript:
   - Styles cohérents entre vCard et Avis Google
   - Composants réutilisables (badges, boutons, cartes stats)
   - Transitions fluides entre sections
3. Gestion des cas d'usage:
   - Utilisateur avec seulement vCard
   - Utilisateur avec seulement Avis Google  
   - Utilisateur avec les 2 types
   - Nouvel utilisateur sans produits
4. Tests d'intégration complets:
   - Commande vCard seule → dashboard section vCard
   - Commande Avis Google seule → dashboard section Avis Google
   - Commande mixte → dashboard complet 2 sections
5. Responsive design et optimisations UX

Critères de validation :
✅ Dashboard s'adapte parfaitement selon les produits
✅ Navigation fluide et intuitive
✅ Design cohérent et moderne
✅ Aucun bug sur tous les cas d'usage
```

### **🧪 Étape 3.2 : Tests et Optimisations**
**Durée :** 4h | **Prompt :**
```
Contexte : Tests complets et optimisations finales.

Objectif : Système robuste prêt pour production.

Tâches :
1. Tests fonctionnels complets:
   - Création commandes tous types de produits
   - Fonctionnalités dashboard (modification, stats, exports)
   - Compatibilité avec vCards existantes (migration)
   - Tests de performance avec nombreuses cartes
2. Optimisations:
   - Caching des requêtes lourdes
   - Lazy loading des analytics
   - Compression assets CSS/JS
   - Optimisation requêtes BDD
3. Sécurité:
   - Validation des permissions utilisateur
   - Protection CSRF sur toutes les actions AJAX
   - Sanitisation des données entrantes
   - Tests de sécurité basiques
4. Documentation:
   - Guide utilisateur (screenshots + explications)
   - Documentation technique pour maintenance
   - Changelog et notes de version
5. Préparation déploiement production

Critères de validation :
✅ Tous les tests passent sans erreur
✅ Performance acceptable (dashboard < 2s)
✅ Sécurité validée
✅ Documentation complète
✅ Prêt pour production
```

---

## ⚡ **RÉSUMÉ EXÉCUTIF**

**Durée totale :** 4 jours
**Résultat :** Dashboard unifié gérant vCard + Avis Google, code clean et maintenable

**Livrables :**
- ✅ Dashboard unique adaptable selon les produits utilisateur
- ✅ Système vCard multi-profils complet
- ✅ Système Avis Google profils partagés complet  
- ✅ Code modulaire et extensible
- ✅ Interface utilisateur moderne et intuitive
- ✅ Tests et documentation complets

**Architecture finale :**
```
Dashboard Utilisateur
├── Section "Mes Profils vCard" (si présent)
│   └── 1 ligne par carte = 1 profil individuel
└── Section "Mes Profils Avis Google" (si présent)  
    └── 1 ligne par commande = 1 profil partagé
```