# Document 3/4 : Cahier des Charges Fonctionnel  
# Système Avis Google

## 🎯 **Vision Produit Précise**

### **Besoin Client Identifié**
Les **commerces et restaurants** veulent commander **plusieurs cartes/plaques Avis Google** pour collecter des avis clients, avec **toutes les cartes pointant vers le même compte Google Business**.

**Règle fondamentale :** **X cartes/plaques commandées = 1 profil Avis Google partagé**

### **Différence avec vCard Entreprise**
- **vCard :** 1 carte = 1 profil individuel complet
- **Avis Google :** X cartes = 1 profil partagé → 1 URL Google Business

---

## 👥 **Personas Détaillés**

### **Persona 1 : Sophie - Responsable Restaurant (15 tables)**
**Contexte :** Restaurant 15 tables + comptoir takeaway + terrasse  
**Besoin :** Collecter avis Google sur tous points de contact client

**Commande :** 12 cartes Avis Google + 3 plaques Avis Google

**Dashboard souhaité :**
```
┌─ AVIS GOOGLE - RESTAURANT LA TABLE ────────────┐
│ 🏪 Restaurant La Table Gourmande              │  
│ ⭐ Note Google : 4.8/5 (127 avis)             │
│ 📱 12 cartes + 🏷️ 3 plaques → 1 compte        │
│                                                │
│ URL Google Business :                          │
│ [https://g.page/r/restaurant/review] [✓]       │
│                                                │
│ LOCALISATION DES ÉLÉMENTS :                   │
│ AG2045-1  [Table 1    ▼] [23 scans]           │
│ AG2045-2  [Table 2    ▼] [18 scans]           │
│ AG2045-3  [Comptoir   ▼] [34 scans]           │
│ [...] 9 autres cartes                         │
│ AGP2045-1 [Vitrine    ▼] [12 scans]           │
│ AGP2045-2 [Terrasse   ▼] [8 scans]            │
│ AGP2045-3 [Bar        ▼] [15 scans]           │
│                                                │
│ [QR Codes] [Statistiques] [Configuration]      │
└────────────────────────────────────────────────┘
```

**Workflow quotidien :**
1. Sophie configure 1 URL Google Business unique
2. Toutes les cartes/plaques redirigent vers cette URL
3. Sophie suit quels emplacements génèrent le plus de scans
4. Sophie optimise placement selon performances

### **Persona 2 : Marc - Concession Auto (Multi-points contact)**
**Contexte :** Showroom + atelier + bureau commercial  
**Besoin :** Avis Google après vente ET après service

**Commande :** 8 cartes Avis Google

**Workflow :**
1. Marc configure URL avis concession  
2. Commercial donne carte après vente
3. Mécanicien donne carte après réparation
4. Marc suit : ventes vs service → quels départements génèrent plus d'avis

### **Persona 3 : Dr Martin - Cabinet Médical (4 praticiens)**
**Contexte :** Cabinet 4 praticiens, même établissement Google  
**Besoin :** Avis patients sur le cabinet (pas sur praticien individuel)

**Commande :** 4 cartes Avis Google (1 par praticien)

**Workflow :**
1. Dr Martin configure URL avis cabinet médical
2. Chaque praticien a sa carte physique  
3. Patient satisfait → scan carte → avis cabinet
4. Dr Martin suit quel praticien génère plus d'avis

---

## ⚙️ **Spécifications Fonctionnelles Exactes**

### **F1. Système de Commande Avis Google**

#### **F1.1 Catégories Produits WooCommerce**
```php
// Catégories produits étendues
'nfc_product_category' => [
    'vcard' => 'Carte vCard individuelle',           // Multi-profils
    'google_reviews_card' => 'Carte Avis Google',    // Profil partagé
    'google_reviews_plaque' => 'Plaque Avis Google', // Profil partagé  
    'other' => 'Autre produit NFC'
]

// Comportements par catégorie
'vcard' → quantity × 1 = X profils individuels (Document 1/2)
'google_reviews_card' → quantity × N = 1 profil partagé
'google_reviews_plaque' → quantity × N = 1 profil partagé (même que cartes)
```

#### **F1.2 Logique de Création Post-Commande**
```
Commande #2045 : 
├── "Carte Avis Google" × 12
└── "Plaque Avis Google" × 3

Système crée :
├── 1 profil Avis Google unique #801 (post_type: google_reviews_profile)
├── 15 identifiants physiques : AG2045-1 à AG2045-12 + AGP2045-1 à AGP2045-3  
└── 1 dashboard Avis Google (toutes cartes/plaques → même URL)

Données profil partagé :
- Nom : "Restaurant La Table Gourmande - Avis Google"
- URL Google Business : (à configurer par client)
- Total éléments : 15 (12 cartes + 3 plaques)
- Identifiants : [AG2045-1, AG2045-2, ..., AGP2045-1, AGP2045-2, AGP2045-3]
```

#### **F1.3 Identifiants Physiques Avis Google**
```
Format cartes : AG{ORDER_ID}-{POSITION}
Format plaques : AGP{ORDER_ID}-{POSITION}

Exemples commande #2045 :
- Cartes : AG2045-1, AG2045-2, AG2045-3, ..., AG2045-12
- Plaques : AGP2045-1, AGP2045-2, AGP2045-3

Avantages format :
- AG = Avis Google (identifiable immédiatement)
- AGP = Avis Google Plaque (distinction visuelle)
- Plus court que NFC1023-AG-1 (facilité saisie client)
```

### **F2. Dashboard Avis Google Spécialisé**

#### **F2.1 Architecture Dashboard Distinct**
**Le dashboard Avis Google est SÉPARÉ du système multi-cartes vCard.**

```
Dashboard Multi-cartes vCard (Document 1/2) :
└── Gère vCards individuelles entreprise

Dashboard Avis Google (ce document) :
└── Gère profils Avis Google partagés
```

#### **F2.2 Page Principale "Avis Google"**
**Interface Dashboard Avis Google :**
```
┌─ AVIS GOOGLE - RESTAURANT LA TABLE ────────────┐
│                                                │
│ 🏪 ÉTABLISSEMENT                               │
│ Restaurant La Table Gourmande - Paris 11ème    │
│ 📱 12 cartes + 🏷️ 3 plaques → 1 compte Google │
│                                                │
│ ⭐ CONFIGURATION COMPTE GOOGLE BUSINESS        │
│ ┌─────────────────────────────────────────────┐ │
│ │ URL page d'avis Google Business :          │ │
│ │ [g.page/r/restaurant-xyz/review] [Valider] │ │
│ │                                            │ │
│ │ 💡 Comment trouver votre URL :             │ │
│ │ 1. Google My Business → Votre fiche       │ │
│ │ 2. Clic "Demander des avis clients"       │ │
│ │ 3. Copiez l'URL raccourcie                │ │
│ │                                            │ │
│ │ ✅ URL configurée et validée               │ │
│ └─────────────────────────────────────────────┘ │
│                                                │
│ 🎯 QR CODE GÉNÉRÉ AUTOMATIQUEMENT             │
│ [████████████]                                │
│ [PNG] [SVG] [PDF Multi-tailles] [Impression]   │
│                                                │
│ 📍 GESTION DES EMPLACEMENTS                   │
│ ┌─────────────────────────────────────────────┐ │
│ │ CARTES (12 éléments) :                     │ │
│ │ AG2045-1  [Table 1      ▼] [23 scans] [📊] │ │
│ │ AG2045-2  [Table 2      ▼] [18 scans] [📊] │ │
│ │ AG2045-3  [Comptoir     ▼] [34 scans] [📊] │ │
│ │ AG2045-4  [Table 4      ▼] [12 scans] [📊] │ │
│ │ [...] 8 autres cartes à configurer         │ │
│ │                                            │ │
│ │ PLAQUES (3 éléments) :                     │ │
│ │ AGP2045-1 [Vitrine      ▼] [12 scans] [📊] │ │
│ │ AGP2045-2 [Terrasse     ▼] [8 scans]  [📊] │ │
│ │ AGP2045-3 [Bar          ▼] [15 scans] [📊] │ │
│ └─────────────────────────────────────────────┘ │
│                                                │
│ 📊 STATISTIQUES GLOBALES (30 jours)           │
│ • Total scans : 142                           │
│ • Redirections réussies : 128 (90%)           │  
│ • Top performer : Comptoir (34 scans)         │
│ • Pire performance : Table 8 (2 scans)        │
│                                                │
│ 🎯 RECOMMANDATIONS AUTOMATIQUES               │
│ • Déplacer carte Table 8 → Meilleure visibilité│
│ • Ajouter carte terrasse → Fort potentiel     │
│ • Pic d'activité : 12h-14h et 19h-21h        │
└────────────────────────────────────────────────┘
```

#### **F2.3 Fonctionnalités Interface**

**Configuration URL Google Business :**
- Champ URL avec validation format Google
- Test automatique de l'URL (vérification redirection)
- Message d'aide contextuel pour trouver l'URL
- Sauvegarde instantanée AJAX

**Génération QR Code :**
- QR Code automatique basé sur URL configurée
- Formats multiples : PNG, SVG, PDF
- Tailles multiples : Petit (3cm), Moyen (5cm), Grand (8cm)  
- Template impression avec instructions

**Mapping Emplacements :**
- Liste tous identifiants physiques (cartes + plaques)
- Dropdown personnalisé par identifiant : "Table 1", "Comptoir", "Vitrine"
- Stats scans temps réel par emplacement
- Graphique barres performance par emplacement

### **F3. Workflow Technique Avis Google**

#### **F3.1 Utilisation Client Final**
```
Workflow utilisation :
1. Client satisfait au restaurant
2. Serveur/client scan carte Table 5 (AG2045-5)
3. Redirection automatique → URL Google Business configurée  
4. Client laisse avis 5⭐ sur Google
5. Tracking : +1 scan AG2045-5, +1 redirection réussie
6. Dashboard Sophie : Table 5 +1 dans statistiques
```

#### **F3.2 Page Publique Avis Google**
```php
// Template single-google_reviews_profile.php
<?php
$profile_id = get_queried_object_id();
$google_url = get_post_meta($profile_id, 'google_business_url', true);
$scan_identifier = $_GET['source'] ?? 'direct'; // AG2045-5, AGP2045-1...

if ($google_url) {
    // Tracker le scan si identifiant fourni
    if ($scan_identifier && preg_match('/^AG[P]?\d+-\d+$/', $scan_identifier)) {
        nfc_track_google_reviews_scan($profile_id, $scan_identifier);
    }
    
    // Redirection 301 vers Google Business
    wp_redirect($google_url, 301);
    exit;
} else {
    // Page d'attente si pas encore configuré
    display_google_reviews_pending_page();
}
?>
```

#### **F3.3 URLs Publiques Format**
```
Profil Avis Google : nfcfrance.com/google-avis/abc123def456/
Avec tracking source : nfcfrance.com/google-avis/abc123def456/?source=AG2045-5

Comportement :
- Scan carte Table 5 → URL avec ?source=AG2045-5
- Système track : profil X, source AG2045-5, timestamp  
- Redirection immédiate → Google Business
- Dashboard : +1 scan Table 5
```

### **F4. Système de Tracking Avancé**

#### **F4.1 Base de Données Tracking**
```sql
CREATE TABLE wp_google_reviews_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,                    -- Post google_reviews_profile
    element_identifier VARCHAR(20) NOT NULL,    -- AG2045-5, AGP2045-1
    element_type ENUM('card', 'plaque') NOT NULL,
    element_label VARCHAR(100),                 -- "Table 5", "Vitrine"
    event_type ENUM('scan', 'redirect', 'error') DEFAULT 'scan',
    user_agent TEXT,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX (profile_id, element_identifier),
    INDEX (created_at)
);
```

#### **F4.2 Analytics Détaillées**
**Page Analytics Avancées :**
```
┌─ ANALYTICS AVIS GOOGLE - 30 DERNIERS JOURS ───┐
│                                                │
│ 📊 PERFORMANCE PAR EMPLACEMENT                │
│ ┌─────────────────────────────────────────────┐ │
│ │ Comptoir (AG2045-3)    ████████████ 34     │ │
│ │ Table 1 (AG2045-1)     ████████ 23         │ │
│ │ Table 2 (AG2045-2)     ██████ 18           │ │
│ │ Bar (AGP2045-3)        █████ 15            │ │
│ │ Vitrine (AGP2045-1)    ████ 12             │ │
│ │ Terrasse (AGP2045-2)   ███ 8              │ │
│ │ Table 8 (AG2045-8)     █ 2                 │ │
│ └─────────────────────────────────────────────┘ │
│                                                │
│ 🕐 ANALYSE TEMPORELLE                         │
│ • Pic matin : 11h30-12h30 (déjeuner)          │
│ • Pic soir : 19h30-21h00 (dîner)             │
│ • Jour record : Samedi (+45% vs moyenne)      │
│ • Moins actif : Mardi midi (-30%)             │
│                                                │
│ 📱 RÉPARTITION PAR APPAREIL                   │
│ • Mobile : 82% (116 scans)                    │
│ • Desktop : 12% (17 scans)                    │
│ • Tablette : 6% (9 scans)                     │
│                                                │
│ 🎯 INSIGHTS ET RECOMMANDATIONS                │
│ • 🔥 Hot spot : Comptoir performe 3× mieux    │
│ • ⚠️ Attention : Table 8 sous-exploitée       │
│ • 💡 Opportunité : Ajouter carte terrasse bar │
│ • 📈 Tendance : +25% scans vs mois dernier    │
│                                                │
│ 📊 COMPARAISON PÉRIODES                       │
│ • Cette semaine : 35 scans (+12%)             │
│ • Semaine dernière : 31 scans                 │
│ • Même période mois dernier : 28 scans        │
└────────────────────────────────────────────────┘
```

#### **F4.3 Export et Rapports**
- **Export CSV détaillé** : Date, heure, emplacement, appareil, IP
- **Rapport hebdomadaire** : Email automatique avec insights  
- **Comparaison concurrentielle** : Performance vs moyennes secteur (si données disponibles)
- **Alertes intelligentes** : Chute performance emplacement, pic inhabituel

---

## 🔄 **Workflows Détaillés**

### **Workflow A : Restaurant Pure Avis Google (Cas Sophie)**
```
COMMANDE :
Sophie → Boutique NFC France → "Carte Avis Google" × 12 + "Plaque Avis Google" × 3 → Commande #2045

CRÉATION AUTOMATIQUE :
🤖 Système détecte produits Avis Google uniquement
🤖 Crée 1 profil google_reviews_profile #801 "Restaurant La Table - Avis Google"
🤖 Génère 15 identifiants : AG2045-1 à AG2045-12 + AGP2045-1 à AGP2045-3
🤖 URL publique unique : /google-avis/def456ghi789/
🤖 Email Sophie : "Vos 15 éléments Avis Google sont prêts"

EMAIL NOTIFICATION :
📧 "Vos éléments Avis Google sont prêts - Restaurant La Table"
   ├ 12 cartes : AG2045-1 à AG2045-12
   ├ 3 plaques : AGP2045-1 à AGP2045-3  
   ├ Dashboard : /mon-compte/avis-google/
   └ "Configurez votre URL Google Business pour activer"

CONFIGURATION SOPHIE :
1. Sophie dashboard → Page "Avis Google Restaurant La Table"
2. Sophie saisit URL : https://g.page/r/restaurant-la-table/review
3. Sophie labellise éléments :
   • AG2045-1 → "Table 1" 
   • AG2045-2 → "Table 2"
   • AG2045-3 → "Comptoir"
   • [...] 
   • AGP2045-1 → "Vitrine"
   • AGP2045-2 → "Terrasse"  
   • AGP2045-3 → "Bar"
4. Sophie télécharge QR codes pour chaque emplacement
5. Sophie place cartes/plaques selon mapping

UTILISATION QUOTIDIENNE :
👤 Client satisfait table 5 → scan AG2045-5 → Google Avis → Note 5⭐
👤 Client takeaway → scan comptoir AG2045-3 → Google Avis → Note 4⭐  
👤 Client terrasse → scan plaque AGP2045-2 → Google Avis → Note 5⭐
📊 Dashboard Sophie : +3 scans (Table 5: +1, Comptoir: +1, Terrasse: +1)

RÉSULTAT MENSUEL :
✅ 142 scans au total, 128 redirections réussies (90%)
✅ Top performer : Comptoir (34 scans), Moins actif : Table 8 (2 scans)
✅ Sophie optimise placement : déplace carte Table 8 vers zone plus visible
✅ +12 nouveaux avis Google ce mois vs mois précédent
```

### **Workflow B : Commande Mixte vCard + Avis Google**
```
COMMANDE :
Marc (Concession Auto) → 5 cartes vCard commerciaux + 8 cartes Avis Google → Commande #3012

CRÉATION AUTOMATIQUE :
🤖 Système détecte produits MIXTES  
🤖 Crée 5 profils vCard individuels : Jean, Marie, Paul, Sophie, Pierre
🤖 Crée 1 profil Avis Google : "Concession ACME Auto - Avis Google"
🤖 Identifiants mixtes : NFC3012-1 à NFC3012-5 + AG3012-1 à AG3012-8

DASHBOARD MARC :
Marc accède → 2 sections DISTINCTES :
├── Section "Multi-cartes vCard" → 5 commerciaux (Document 1/2)
└── Section "Avis Google" → 8 cartes concession (ce document)

CONFIGURATION MARC :
1. Marc configure 5 profils vCard commerciaux individuellement  
2. Marc configure 1 profil Avis Google concession
3. Marc distribue : vCard aux commerciaux, cartes Avis Google aux services

UTILISATION :
👤 Commercial Jean utilise sa vCard NFC3012-1 → prospect → contact Jean
👤 Client satisfait après vente → scan AG3012-1 → avis concession
👤 Client après SAV → scan AG3012-5 → avis concession  
📊 Marc suit : performance commerciaux (vCard) + satisfaction clients (Avis Google) séparément

RÉSULTAT :
✅ 2 dashboards spécialisés pour 2 besoins distincts
✅ Analytics séparées : génération leads vs image de marque
✅ Pas de mélange données entre vCard individuelles et Avis Google partagé
```

### **Workflow C : Évolution Cabinet Médical**
```
SITUATION INITIALE :
Dr Martin commande 4 cartes Avis Google → 1 profil "Cabinet Dr Martin"

ÉVOLUTION 6 MOIS PLUS TARD :
Cabinet s'agrandit → 2 nouveaux praticiens → Commande 2 cartes supplémentaires

TRAITEMENT ÉVOLUTION :
🤖 Système détecte client existant avec profil Avis Google  
🤖 Ajoute 2 nouveaux identifiants : AG4567-1, AG4567-2 au profil existant
🤖 Dashboard passe de 4 à 6 cartes → même URL Google Business
🤖 Email : "2 nouvelles cartes ajoutées à votre profil Avis Google"

CONFIGURATION ÉVOLUTIVE :
1. Dr Martin dashboard → Voit maintenant 6 cartes  
2. Dr Martin labellise nouvelles cartes : "Dr Dubois", "Dr Moreau"
3. Même URL Google Business pour toutes les cartes
4. Stats consolidées : historique 4 cartes + nouvelles performances

RÉSULTAT :
✅ Extension profil sans création nouveau compte
✅ Historique préservé + nouvelles fonctionnalités
✅ Gestion centralisée 6 praticiens → 1 cabinet
```

---

## 📊 **Architecture Technique Détaillée**

### **Custom Post Type google_reviews_profile**
```php
// Structure profil Avis Google
register_post_type('google_reviews_profile', [
    'labels' => [
        'name' => 'Profils Avis Google',
        'singular_name' => 'Profil Avis Google'
    ],
    'public' => true,
    'has_archive' => true,
    'rewrite' => ['slug' => 'google-avis'],
    'supports' => ['title', 'custom-fields'],
    'show_in_rest' => true
]);

// Métadonnées profil Avis Google
'google_business_url' => 'https://g.page/r/restaurant-xyz/review',
'company_name' => 'Restaurant La Table Gourmande',
'order_id' => 2045,
'main_user_id' => 42,                    // Sophie (propriétaire)
'total_cards' => 12,                     // Nombre cartes physiques
'total_plaques' => 3,                    // Nombre plaques physiques
'total_elements' => 15,                  // Total éléments NFC
'elements_mapping' => [                  // JSON mapping ID → Label
    'AG2045-1' => 'Table 1',
    'AG2045-2' => 'Table 2',
    'AG2045-3' => 'Comptoir',
    'AGP2045-1' => 'Vitrine',
    'AGP2045-2' => 'Terrasse',
    'AGP2045-3' => 'Bar'
],
'total_scans_30d' => 142,                // Cache stats rapides
'total_redirections_30d' => 128,
'top_performer' => 'Comptoir (34 scans)',
'status' => 'configured'                 // pending, configured, active
```

### **Table de Liaison Éléments NFC**
```sql
CREATE TABLE wp_google_reviews_elements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,              -- Post google_reviews_profile
    order_id INT NOT NULL,                -- Commande WooCommerce
    element_identifier VARCHAR(20) NOT NULL UNIQUE, -- AG2045-1, AGP2045-3
    element_type ENUM('card', 'plaque') NOT NULL,
    element_position INT NOT NULL,        -- 1,2,3... dans la commande
    element_label VARCHAR(100) DEFAULT 'À configurer', -- "Table 1", "Vitrine"
    scans_count INT DEFAULT 0,
    last_scan_at DATETIME NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX (profile_id),
    INDEX (element_identifier)
);
```

### **Workflow de Création Technique**
```php
function nfc_create_google_reviews_profile($order_id) {
    $order = wc_get_order($order_id);
    $google_items = [];
    
    // Récupérer tous items Avis Google (cartes + plaques)
    foreach ($order->get_items() as $item) {
        $product_category = get_product_nfc_category($item->get_product_id());
        if (in_array($product_category, ['google_reviews_card', 'google_reviews_plaque'])) {
            $google_items[] = [
                'item' => $item,
                'type' => $product_category === 'google_reviews_card' ? 'card' : 'plaque',
                'quantity' => $item->get_quantity()
            ];
        }
    }
    
    if (empty($google_items)) return false;
    
    // Créer profil unique pour tous items Avis Google
    $profile_id = wp_insert_post([
        'post_title' => ($order->get_billing_company() ?: 'Client') . ' - Avis Google - Commande #' . $order_id,
        'post_type' => 'google_reviews_profile',
        'post_status' => 'publish',
        'post_author' => $order->get_customer_id() ?: 1
    ]);
    
    // Compter total éléments et créer identifiants
    $total_cards = 0;
    $total_plaques = 0;
    $elements_details = [];
    
    foreach ($google_items as $google_item) {
        $item = $google_item['item'];
        $type = $google_item['type'];
        $quantity = $google_item['quantity'];
        
        if ($type === 'card') $total_cards += $quantity;
        if ($type === 'plaque') $total_plaques += $quantity;
        
        // Générer identifiants pour cette ligne
        for ($pos = 1; $pos <= $quantity; $pos++) {
            $identifier = $type === 'card' ? 
                "AG{$order_id}-{$pos}" : 
                "AGP{$order_id}-{$pos}";
                
            $elements_details[] = [
                'identifier' => $identifier,
                'type' => $type,
                'position' => $pos,
                'label' => 'À configurer'
            ];
            
            // Sauvegarder élément en base
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'google_reviews_elements',
                [
                    'profile_id' => $profile_id,
                    'order_id' => $order_id,
                    'element_identifier' => $identifier,
                    'element_type' => $type,
                    'element_position' => $pos
                ]
            );
        }
    }
    
    // Métadonnées profil
    update_post_meta($profile_id, 'order_id', $order_id);
    update_post_meta($profile_id, 'main_user_id', $order->get_customer_id());
    update_post_meta($profile_id, 'company_name', $order->get_billing_company());
    update_post_meta($profile_id, 'total_cards', $total_cards);
    update_post_meta($profile_id, 'total_plaques', $total_plaques);
    update_post_meta($profile_id, 'total_elements', $total_cards + $total_plaques);
    update_post_meta($profile_id, 'elements_details', $elements_details);
    update_post_meta($profile_id, 'status', 'pending');
    
    error_log("NFC: Created Google Reviews profile $profile_id for order $order_id");
    return $profile_id;
}
```

---

## ✅ **Critères d'Acceptation Fonctionnels**

### **CA1. Commande et Création**
- [ ] ✅ Commande "12 cartes + 3 plaques Avis Google" → 1 profil créé + 15 identifiants
- [ ] ✅ Identifiants format AG2045-1 à AG2045-12 + AGP2045-1 à AGP2045-3
- [ ] ✅ Email reçu liste tous identifiants + lien dashboard Avis Google
- [ ] ✅ URL publique unique générée : /google-avis/abc123def/

### **CA2. Dashboard Avis Google**
- [ ] ✅ Page configuration URL Google Business fonctionnelle
- [ ] ✅ QR Code généré automatiquement pointe vers URL configurée
- [ ] ✅ Mapping 15 éléments → emplacements personnalisables
- [ ] ✅ Stats temps réel par emplacement (scans individuels)

### **CA3. Tracking et Redirection**
- [ ] ✅ Scan AG2045-5 → URL avec ?source=AG2045-5 → Redirection Google → +1 scan Table 5
- [ ] ✅ Dashboard analytics : performance par emplacement temps réel
- [ ] ✅ Export CSV : Date, emplacement, appareil, stats détaillées
- [ ] ✅ Recommandations automatiques : top/flop performers

### **CA4. Commandes Mixtes**  
- [ ] ✅ Commande "5 vCard + 8 Avis Google" → 2 sections dashboard distinctes
- [ ] ✅ Pas de mélange données vCard individuelles vs Avis Google partagé
- [ ] ✅ Navigation fluide entre dashboard vCard et Avis Google

### **CA5. Évolution et Ajouts**
- [ ] ✅ Client existant Avis Google + commande supplémentaire → ajout au profil existant  
- [ ] ✅ Historique scans préservé lors ajout nouveaux éléments
- [ ] ✅ Dashboard unique même avec commandes multiples dans le temps

### **CA6. Performance et UX**
- [ ] ✅ Dashboard fluide avec 50+ éléments (cartes + plaques)
- [ ] ✅ Redirection < 500ms vers Google Business
- [ ] ✅ Analytics temps réel sans latence
- [ ] ✅ Interface mobile responsive pour configuration

---

## 🚫 **Limitations v1**

### **Non inclus v1 :**
- **API Google Business** : Pas de récupération automatique avis/notes (complexité OAuth2)
- **Multi-comptes Google** : 1 profil = 1 URL Google Business maximum
- **Géofencing** : Pas de détection localisation géographique scans
- **A/B Testing** : Pas de test performances emplacements automatisé
- **Intégration CRM** : Pas de sync avis Google vers outils externes

### **Architecture préparée v2 :**
- Authentification Google Business API
- Support multi-établissements chaînes  
- Analytics prédictives IA
- Intégrations tierces (Zapier, etc.)

---

## 🔗 **Intégration avec vCard (Champ Avis Google)**

### **F5. Champ Avis Google dans Profils vCard**
**Besoin identifié :** Les commerciaux avec vCard individuelle veulent aussi promouvoir les avis de leur entreprise.

#### **F5.1 Nouveau Champ ACF vCard**
```php
// Champ ACF à ajouter dans groupe vCard
[
    'key' => 'field_google_reviews_url',
    'label' => 'URL Avis Google entreprise',
    'name' => 'google_reviews_url', 
    'type' => 'url',
    'instructions' => 'URL de la page d\'avis Google de votre entreprise',
    'placeholder' => 'https://g.page/r/votre-entreprise/review'
],
[
    'key' => 'field_google_reviews_enabled',
    'label' => 'Afficher bouton avis Google',
    'name' => 'google_reviews_enabled',
    'type' => 'true_false',
    'default_value' => 0
]
```

#### **F5.2 Interface Édition vCard**
```
┌─ PROFIL JEAN DUPONT - COMMERCIAL ─────────────┐
│ [...champs vCard habituels...]                │
│                                                │
│ 📝 PROMOTION AVIS ENTREPRISE                  │
│ ┌─────────────────────────────────────────────┐ │
│ │ ☑️ Afficher bouton "Avis Google" sur ma vCard│ │
│ │                                            │ │
│ │ URL avis Google entreprise :               │ │
│ │ [g.page/r/acme-corp/review] [Tester]       │ │
│ │                                            │ │
│ │ 💡 Les visiteurs de votre vCard pourront   │ │
│ │    laisser un avis sur votre entreprise    │ │
│ └─────────────────────────────────────────────┘ │
└────────────────────────────────────────────────┘
```

#### **F5.3 Affichage vCard Publique**
```
┌─ VCARD PUBLIQUE JEAN DUPONT ──────────────────┐
│ [Photo] Jean Dupont                           │
│         Commercial - ACME Corp                │
│ 📞 0123456789  📧 jean@acme.com              │
│                                                │
│ [Télécharger vCard] [LinkedIn] [Site web]     │
│                                                │
│ ⭐ VOTRE AVIS COMPTE                          │
│ [⭐ Laisser un avis sur ACME Corp]            │ ← NOUVEAU
│                                                │
│ 📊 "Aidez-nous à améliorer nos services"      │
└────────────────────────────────────────────────┘
```

**Workflow :**
1. Jean configure son URL avis entreprise dans sa vCard
2. Prospect visite vCard Jean → voit infos Jean + bouton avis ACME Corp
3. Prospect satisfait clique → avis Google sur entreprise (pas sur Jean)
4. Win-win : Jean aide l'entreprise, entreprise bénéficie du réseau Jean

---

## 💼 **Impact Business et ROI**

### **Pour NFC France :**
- **Nouveau marché** : Restauration, retail, services locaux (×5 potentiel vs B2B)
- **Commandes volumes** : Restaurants commandent 10-50 cartes vs 1-5 en B2B
- **Récurrence renforcée** : Collecte avis = besoin permanent
- **Différenciation** : Seule plateforme avec analytics par emplacement

### **Pour les Clients Commerces :**
- **ROI prouvé** : +15% avis Google moyens constatés avec NFC vs QR papier
- **Insights business** : Emplacements performants vs sous-exploités  
- **Simplicité** : 1 URL pour toutes cartes vs gestion multiple
- **Évolutivité** : Ajout emplacements sans complexité technique

### **Vs Concurrence :**
- **QR codes papier** : Pas de stats, pas de tracking, pas d'optimisation
- **Solutions génériques** : 1 QR = 1 URL, pas de consolidation
- **Plateformes avis** : Payantes par avis, nous c'est par carte physique

---

*Cahier des charges fonctionnel Système Avis Google v1*  
*Spécifications exactes pour développement sans ambiguïté*