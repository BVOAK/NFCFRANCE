# 📋 Cahier des Charges - Système de Traitement des Commandes NFC

**Version :** 1.0  
**Date :** 13/09/2025  
**Projet :** NFC France - Extension gtmi-vcard  
**Objectif :** Automatiser la création de vCards et profils Avis Google selon les commandes WooCommerce

---

## 🎯 **OBJECTIF PRINCIPAL**

Développer un système automatique qui traite les commandes WooCommerce payées pour créer :
- **X cartes vCard commandées** = **X profils vCard individuels** 
- **X produits Avis Google commandés** = **1 profil Avis Google partagé**
- **Multi-commandes** = Ajout progressif au dashboard client

---

## 📊 **ANALYSE DES BESOINS**

### **Problème Actuel**
- ✅ Système existant crée 1 vCard par commande (fonction `gtmi_vcard_new()`)
- ❌ Ne gère pas les quantités multiples (commande 5 cartes = 1 seule vCard)
- ❌ Ne distingue pas vCard vs Avis Google
- ❌ Pas de support multi-commandes par client

### **Solution Requise**  
- ✅ Détection automatique du type de produit par catégorie WooCommerce
- ✅ Création multiple selon quantités commandées
- ✅ Dashboard adaptatif montrant tous les profils du client
- ✅ Identifiants physiques uniques pour chaque carte

---

## 🛒 **SPÉCIFICATIONS FONCTIONNELLES**

### **F1. Détection des Types de Produits**

#### **F1.1 Catégories WooCommerce**
| Catégorie Slug | Type Produit | Création |
|---|---|---|
| `carte-nfc-vcard` | Carte vCard | X produits = X profils individuels |
| `avis-google` | Avis Google | X produits = 1 profil partagé |

#### **F1.2 Fonction de Détection**
```php
function nfc_detect_product_type($product_id)
// Retour: 'vcard', 'google_reviews_card', ou false
```

**Algorithme :**
1. Récupérer catégories du produit via `wp_get_post_terms()`
2. Si slug `carte-nfc-vcard` → return `'vcard'`
3. Si slug `avis-google` → return `'google_reviews_card'`
4. Fallback fonction existante `gtmi_vcard_is_nfc_product()` → return `'vcard'`
5. Sinon → return `false`

### **F2. Traitement des Commandes**

#### **F2.1 Déclenchement Automatique**
**Hooks WordPress :**
- `woocommerce_order_status_changed` → Statut `processing` ou `completed`
- `woocommerce_thankyou` → Paiement confirmé

**Fonction principale :**
```php
function nfc_process_order_products($order_id)
```

#### **F2.2 Algorithme de Traitement**
```
1. Vérifier commande existe et n'est pas déjà traitée
2. Analyser tous les items de la commande
3. Grouper par type: vCard, Avis Google, autres
4. Traiter vCards: créer X profils individuels
5. Traiter Avis Google: créer 1 profil partagé  
6. Marquer commande comme traitée
7. Logger résultats
```

#### **F2.3 Structure Analyse Commande**
```php
$analysis = [
    'vcard_items' => [...],
    'vcard_total_quantity' => int,
    'google_items' => [...], 
    'google_total_quantity' => int,
    'other_items' => [...]
];
```

### **F3. Création vCards (Produits vCard)**

#### **F3.1 Logique Multi-cartes**
```
Commande #1023 avec :
- 3x Carte NFC PVC Blanche
- 2x Carte NFC PVC Noire
= 5 vCards individuelles créées
```

#### **F3.2 Génération Identifiants**
**Format :** `NFC{order_id}-{position}`

**Exemple commande #1023 :**
- Position 1 → `NFC1023-1`
- Position 2 → `NFC1023-2`  
- Position 3 → `NFC1023-3`
- Position 4 → `NFC1023-4`
- Position 5 → `NFC1023-5`

#### **F3.3 Données vCard**
**Post WordPress :**
```php
'post_type' => 'virtual_card'
'post_title' => 'vCard NFC1023-1 - Jean Dupont'
'post_author' => $customer_id
```

**Champs ACF/Meta :**
```php
'firstname' => $order->get_billing_first_name()
'lastname' => $order->get_billing_last_name()
'email' => $order->get_billing_email()
'mobile' => $order->get_billing_phone()
'society' => $order->get_billing_company()
'order_id' => $order_id
'customer_id' => $customer_id
'card_identifier' => 'NFC1023-1'
'card_position' => 1
'card_status' => 'pending'
'unique_url' => '/vcard/abc123def456'
```

#### **F3.4 Table Enterprise (si existe)**
```sql
INSERT INTO wp_nfc_enterprise_cards SET
    order_id = 1023,
    vcard_id = 456,
    card_position = 1,
    card_identifier = 'NFC1023-1',
    card_status = 'pending',
    main_user_id = 42,
    created_at = NOW()
```

### **F4. Création Profils Avis Google (Produits Avis Google)**

#### **F4.1 Logique Profil Partagé**
```
Commande #2045 avec :
- 8x Carte Avis Google
- 4x Plaque Avis Google  
= 1 profil Avis Google (12 éléments)
```

#### **F4.2 Identifiants Éléments**
**Format :** `AG{order_id}-{element}`

**Exemple commande #2045 :**
- 12 éléments → `AG2045-1` à `AG2045-12`
- 1 profil partagé pour tous les éléments

#### **F4.3 Données Profil Avis Google**
**Post WordPress :**
```php
'post_type' => 'google_reviews_profile'
'post_title' => 'Restaurant La Table - Avis Google - Commande #2045'
'post_author' => $customer_id
```

**Table Éléments :**
```sql
CREATE TABLE wp_google_reviews_elements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    profile_id INT, -- ID du post google_reviews_profile
    order_id INT,
    element_identifier VARCHAR(20), -- AG2045-1
    element_type ENUM('card', 'plaque'),
    element_position INT,
    location_label VARCHAR(100), -- 'Table 1', 'Comptoir'
    scan_count INT DEFAULT 0,
    main_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### **F5. Gestion Multi-commandes**

#### **F5.1 Comportement vCard**
```
Client Jean (user_id=42) :

Commande #1001 → NFC1001-1, NFC1001-2 (2 vCards)
Commande #1023 → NFC1023-1, NFC1023-2, NFC1023-3 (3 vCards)

Dashboard Jean = 5 vCards total
```

#### **F5.2 Comportement Avis Google**
```
Client Restaurant (user_id=15) :

Commande #2001 → 1 profil "Restaurant XYZ" (5 éléments)  
Commande #2045 → Extension profil existant (8 éléments supplémentaires)

Dashboard Restaurant = 1 profil, 13 éléments total
```

---

## 🏗️ **SPÉCIFICATIONS TECHNIQUES**

### **T1. Architecture Fichiers**

#### **T1.1 Fichier Principal**
**Chemin :** `wp-content/plugins/gtmi-vcard/includes/utils/after_order.php`

**Contenu :**
- Hook handlers WooCommerce
- Fonction principale `nfc_process_order_products()`
- Logique création vCard et Avis Google
- Fonctions utilitaires

#### **T1.2 Fonctions Principales**
```php
nfc_process_order_products($order_id)           // Traitement principal
nfc_analyze_order_items($order)                 // Analyse items commande
nfc_create_vcards_from_order($order, $items)    // Création multiple vCards
nfc_create_google_profile_from_order($order, $items) // Profil Avis Google
nfc_create_single_vcard($order, $position, $item)    // vCard individuelle
nfc_detect_product_type($product_id)            // Détection type produit
nfc_identifier_exists($identifier)              // Vérif unicité ID
```

### **T2. Base de Données**

#### **T2.1 Tables Utilisées**
```sql
-- Posts vCard (existant)
wp_posts WHERE post_type = 'virtual_card'

-- Métadonnées vCard (existant)  
wp_postmeta

-- Table Enterprise (existante, optionnelle)
wp_nfc_enterprise_cards

-- Posts Profils Avis Google (nouveau)
wp_posts WHERE post_type = 'google_reviews_profile'

-- Éléments Avis Google (nouveau)
wp_google_reviews_elements
```

#### **T2.2 Contraintes Base**
- **Identifiants uniques** : Vérification avant insertion
- **User cohérent** : Même customer_id dans tous les enregistrements liés
- **Traçabilité** : Lien order_id preserved dans tous les records

### **T3. Gestion des Erreurs**

#### **T3.1 Erreurs Possibles**
- Commande inexistante ou invalide
- Produit sans catégorie détectable  
- Échec création post WordPress
- Conflit d'identifiant unique
- Table BDD manquante

#### **T3.2 Logging**
**Format :** `error_log("NFC: [Context] Message")`

**Exemples :**
```php
error_log("NFC: Processing order 1023");
error_log("NFC: Order 1023 analysis: 5 vCards, 0 Google");
error_log("NFC: vCard created - ID: 456, Identifier: NFC1023-1");
error_log("NFC: Failed to create vCard for position 3: WordPress error");
```

#### **T3.3 Métadonnées Commande**
```php
$order->add_meta_data('_nfc_processed', '2025-09-13 16:30:00');
$order->add_meta_data('_nfc_processing_results', [
    'vcards_created' => [...],
    'google_profiles_created' => [...],
    'errors' => [...]
]);
```

---

## 🎮 **INTERFACE DASHBOARD IMPACT**

### **Dashboard Adaptatif**
Le dashboard client doit s'adapter selon les types de produits :

#### **Client vCard uniquement**
```
Dashboard → Overview (stats globales)
         → Mes cartes (liste vCards)  
         → vCard-edit (formulaire individuel)
```

#### **Client Avis Google uniquement**  
```
Dashboard → Overview (stats globales)
         → Mes profils Avis Google
         → Configuration Avis Google
```

#### **Client Mixte**
```
Dashboard → Overview (stats globales)
         → Mes cartes vCard
         → Mes profils Avis Google
```

---

## ✅ **CRITÈRES DE VALIDATION**

### **V1. Tests Fonctionnels**

#### **V1.1 Test vCard Multiple**
```
1. Commande 3 cartes NFC vCard
2. Paiement confirmé 
3. Vérifier: 3 posts virtual_card créés
4. Vérifier: Identifiants NFC1023-1, NFC1023-2, NFC1023-3
5. Vérifier: Dashboard client affiche 3 cartes
6. Vérifier: Bouton [Modifier] fonctionne pour chaque carte
```

#### **V1.2 Test Avis Google**
```
1. Commande 5 produits Avis Google
2. Paiement confirmé
3. Vérifier: 1 post google_reviews_profile créé
4. Vérifier: 5 entrées dans wp_google_reviews_elements  
5. Vérifier: Identifiants AG2045-1 à AG2045-5
6. Vérifier: Dashboard client affiche 1 profil Avis Google
```

#### **V1.3 Test Multi-commandes**
```
1. Client fait commande 1: 2 vCards
2. Dashboard: 2 vCards visibles
3. Client fait commande 2: 3 vCards  
4. Dashboard: 5 vCards visibles (2+3)
5. Vérifier: Aucune duplication
6. Vérifier: Identifiants cohérents par commande
```

#### **V1.4 Test Commande Mixte**
```
1. Commande: 2 vCards + 3 Avis Google
2. Vérifier: 2 vCards individuelles créées
3. Vérifier: 1 profil Avis Google créé (3 éléments)
4. Vérifier: Dashboard montre les 2 sections
```

### **V2. Tests Techniques**

#### **V2.1 Unicité Identifiants**
```php
// Test collision identifiants
nfc_identifier_exists('NFC1023-1') // doit retourner false si pas utilisé
// Après création
nfc_identifier_exists('NFC1023-1') // doit retourner true
```

#### **V2.2 Gestion Erreurs**
```php
// Commande inexistante
nfc_process_order_products(999999) // doit logger erreur, pas crash
// Produit sans catégorie  
nfc_detect_product_type(999999) // doit retourner false
```

#### **V2.3 Performance**
```
- Traitement commande 100 vCards < 30 secondes
- Pas de timeout PHP
- Logs détaillés pour debug
```

---

## 🎯 **PROCHAINES PHASES**

### **Phase 1 : vCard Multiple (Priorité 1)**
- ✅ Implémentation complète système vCard multiple
- ✅ Tests et validation
- ✅ Intégration dashboard existant

### **Phase 2 : Avis Google Basic (Priorité 2)** 
- 🔄 Création Custom Post Type `google_reviews_profile`
- 🔄 Table `wp_google_reviews_elements`
- 🔄 Logique création profil partagé
- 🔄 Dashboard section Avis Google

### **Phase 3 : Avis Google Avancé (Priorité 3)**
- 🔄 Gestion emplacements/labels
- 🔄 Tracking individuel par élément
- 🔄 Analytics détaillées  
- 🔄 Interface mapping éléments

---

## 🔧 **MAINTENANCE ET ÉVOLUTIONS**

### **Compatibilité**
- **WordPress :** 6.0+
- **WooCommerce :** 7.0+
- **PHP :** 8.1+
- **ACF :** 6.0+ (optionnel)

### **Migration Données**
- Système compatible avec vCards existantes
- Migration automatique via script dédié si nécessaire
- Préservation URLs et fonctionnalités actuelles

### **Logs et Debug**
- URL test: `/?test_nfc_order=1234`
- Debug fonction: `nfc_debug_product_types()`
- Logs centralisés dans `error_log`

---

**📧 Contact Technique :** Développeur NFC France  
**📅 Dernière Mise à Jour :** 13/09/2025