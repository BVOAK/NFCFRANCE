# Document 1/4 : Cahier des Charges
# Système Multi-cartes vCard Entreprise

## 🎯 **Vision Produit Précise**

### **Besoin Client Identifié**
Les **entreprises** veulent commander **plusieurs cartes vCard** (5, 10, 20 cartes) et obtenir **autant de profils vCard individuels et complets** que de cartes physiques achetées.

**Règle fondamentale :** **1 carte achetée = 1 vCard complète = 1 profil individuel**

### **Différence avec Système Actuel**
- **AVANT :** 1 commande = 1 carte = 1 vCard = 1 dashboard simple
- **APRÈS :** 1 commande = X cartes = X vCards = 1 dashboard entreprise gérant X profils

---

## 👥 **Personas Détaillés**

### **Persona 1 : Marc - Dirigeant PME (8 commerciaux)**
**Contexte :** Marc veut équiper ses 8 commerciaux avec leurs propres cartes NFC individuelles  

**Commande :** 8 cartes vCard → **8 profils vCard séparés** créés automatiquement

**Dashboard souhaité :**
```
┌─ MES CARTES (8 cartes) ────────────────────────┐
│                                                │
│ Jean Dupont - Commercial                       │
│ 👁️ 67 vues | 📞 23 contacts | ✅ Configuré    │
│ [Modifier vCard] [Stats] [Leads] [Renouveler]  │
│                                                │
│ Marie Martin - RH                              │
│ 👁️ 45 vues | 📞 12 contacts | ⚠️ À configurer │
│ [Modifier vCard] [Stats] [Leads] [Renouveler]  │
│                                                │
│ [...] 6 autres lignes similaires              │
└────────────────────────────────────────────────┘
```

**Workflow quotidien :**
1. Marc accède dashboard → Voit 8 cartes listées
2. Marc clique "Modifier vCard" Jean → Configure profil de Jean uniquement  
3. Jean utilise SA carte → SES stats/contacts séparés de Marie
4. Marc peut suivre performance individuelle de chaque commercial

### **Persona 2 : Sophie - Responsable Formation (15 formateurs)**
**Contexte :** Déploiement progressif, ajouts de cartes dans le temps

**Commande initiale :** 10 cartes vCard → **10 profils distincts**  
**6 mois plus tard :** 5 cartes supplémentaires → **5 nouveaux profils** ajoutés au dashboard existant

**Évolution dashboard :**
- **Début :** Dashboard 10 cartes
- **Après ajout :** Dashboard 15 cartes (10 + 5)  
- **Renouvellement :** Paul casse sa carte → Renouvellement avec même profil, même stats

---

## ⚙️ **Spécifications Fonctionnelles Exactes**

### **F1. Système de Commande Multi-cartes**

#### **F1.1 Logique de Création Post-Commande**
```
Commande #1023 : "Carte NFC vCard" × quantité 8

Système crée automatiquement :
├── vCard 1 : NFC1023-1 → Profil vCard individuel #501
├── vCard 2 : NFC1023-2 → Profil vCard individuel #502  
├── vCard 3 : NFC1023-3 → Profil vCard individuel #503
├── [...] 5 autres profils similaires
└── vCard 8 : NFC1023-8 → Profil vCard individuel #508

Chaque profil vCard hérite :
- Nom/Prénom du client (modifiable)
- Email de commande (modifiable)  
- Téléphone de commande (modifiable)
- Entreprise si renseignée (modifiable)
- URL publique unique générée
- Dashboard complet (stats, contacts, QR codes)
```

#### **F1.2 Identifiants Physiques et URLs**
```
Format identifiant : NFC{ORDER_ID}-{POSITION}
Exemples : NFC1023-1, NFC1023-2, NFC1023-3...

URLs publiques générées :
- nfcfrance.com/vcard/a1b2c3d4e5f6  (vCard #501)
- nfcfrance.com/vcard/g7h8i9j0k1l2  (vCard #502)
- nfcfrance.com/vcard/m3n4o5p6q7r8  (vCard #503)
- [...] chaque vCard a sa propre URL unique

Stats/Contacts séparés :
- vCard Jean : 67 vues, 23 contacts (isolés)
- vCard Marie : 45 vues, 12 contacts (isolés)  
- Pas de mélange entre profils
```

### **F2. Dashboard Entreprise Multi-cartes**

#### **F2.1 Page "Mes cartes" (Nouvelle)**
**Remplace :** Page actuelle dashboard simple  
**Objectif :** Vue d'ensemble de toutes les cartes de l'entreprise

**Interface Exacte :**
```
┌─ MES CARTES ENTREPRISE ────────────────────────┐
│ 🏢 ACME Corp (8 cartes actives)               │
│ 📊 Stats globales : 456 vues | 89 contacts    │
│                                                │
│ IDENTIFIANT    PROFIL VCARD           ACTIONS │
│ ─────────────────────────────────────────────── │
│ NFC1023-1     Jean Dupont             [●●●●]  │
│               Commercial - 67v/23c             │
│                                                │
│ NFC1023-2     Marie Martin            [●●●○]  │
│               RH - 45v/12c                     │
│                                                │
│ NFC1023-3     Paul Durand             [●○○○]  │  
│               À configurer - 0v/0c             │
│                                                │
│ [...] 5 autres lignes                         │
│                                                │
│ [+ Commander nouvelles cartes]                 │
└────────────────────────────────────────────────┘
```

**Boutons Actions [●●●●] :**
- **[Modifier la vCard]** → Page configuration profil individuel
- **[Voir les Stats]** → Analytics profil individuel  
- **[Voir les Leads]** → Contacts récupérés par ce profil
- **[Renouveler ma carte]** → Tunnel commande avec identifiant pré-rempli

#### **F2.2 Page "Modifier mes profils" (Évolution)**
**Remplace :** Page actuelle "Modifier ma carte"  
**Objectif :** Sélection visuelle + modification profil individuel

**Interface Étape 1 - Sélection :**
```
┌─ CHOISIR PROFIL À MODIFIER ────────────────────┐
│                                                │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐        │
│ │[Photo JD]│ │[Photo MM]│ │[Photo PD]│        │
│ │Jean      │ │Marie     │ │Paul      │        │  
│ │Dupont    │ │Martin    │ │Durand    │        │
│ │Commercial│ │RH        │ │Marketing │        │
│ │67 vues   │ │45 vues   │ │0 vue     │        │
│ │[Modifier]│ │[Modifier]│ │[Config]  │        │
│ └──────────┘ └──────────┘ └──────────┘        │
│                                                │
│ [...] 5 autres cartes en grille               │
└────────────────────────────────────────────────┘
```

**Interface Étape 2 - Modification Individuelle :**
```  
┌─ MODIFIER PROFIL : JEAN DUPONT ────────────────┐
│                                                │
│ 👤 INFORMATIONS PERSONNELLES                  │
│ Prénom : [Jean        ] Nom : [Dupont      ]  │
│ Poste  : [Commercial  ] Email: [jean@acme.com]│
│ [...] tous champs vCard actuels               │
│                                                │
│ 📱 APERÇU CARTE EN TEMPS RÉEL                 │
│ ┌─ Carte virtuelle Jean ──┐                   │
│ │ [Photo] Jean Dupont     │                   │  
│ │         Commercial      │                   │
│ │ 📞 0123456789          │                   │
│ │ 📧 jean@acme.com       │                   │  
│ └─────────────────────────┘                   │
│                                                │
│ [Enregistrer] [Aperçu public] [Retour liste]  │
└────────────────────────────────────────────────┘
```

#### **F2.3 Page "Mes contacts" (Évolution)**
**Ajout :** Colonne "Profil vCard lié" pour identifier origine du contact

**Interface :**
```
┌─ MES CONTACTS REÇUS ───────────────────────────┐
│ CONTACT          EMAIL              VCARD      │
│ ──────────────────────────────────────────────  │
│ Client Intéressé client@test.com   Jean Dupont │
│ Lead Prospect   lead@test.fr       Marie Martin│  
│ [...] autres contacts avec profil d'origine   │
│                                                │
│ Filtres : [Tous] [Jean] [Marie] [Paul] [...]   │
│ Export  : [CSV Tous] [CSV par profil]          │
└────────────────────────────────────────────────┘
```

#### **F2.4 Page "Statistiques" (Évolution)**  
**Ajout :** Sélecteur profil + stats globales entreprise

**Interface :**
```
┌─ STATISTIQUES ENTREPRISE ──────────────────────┐
│ Vue : [Stats globales ▼] Période : [30 jours ▼]│
│      ├ Stats globales (tous profils)           │
│      ├ Jean Dupont                             │
│      ├ Marie Martin                            │
│      └ [...] autres profils                   │
│                                                │
│ 📊 GRAPHIQUES SELON SÉLECTION                 │
│ Si "Stats globales" → Consolidé tous profils   │
│ Si "Jean Dupont" → Stats Jean uniquement       │
└────────────────────────────────────────────────┘
```

#### **F2.5 Informations Entreprise (Nouveau)**
**Ajout :** Champ "Entreprise" dans profil client WooCommerce

**Interface Account WooCommerce :**
```
┌─ INFORMATIONS PERSONNELLES ────────────────────┐
│ Prénom : [Marc    ] Nom : [Dubois          ]  │
│ Email  : [marc@acme.com                    ]  │  
│ Entreprise : [ACME Corp                    ]  │ ← NOUVEAU
│ [...] autres champs habituels                 │
└────────────────────────────────────────────────┘
```

**Affichage Dashboard :**
Le nom d'entreprise apparaît dans header dashboard : "🏢 ACME Corp (8 cartes)"

### **F3. Fonctionnalités Avancées v1**

#### **F3.1 Renouvellement Carte Individuelle**
**Workflow :**
```
1. Marc dashboard → Ligne "Jean Dupont" → [Renouveler]
2. Redirection boutique avec formulaire pré-rempli :
   "Renouvellement carte Jean Dupont (NFC1023-1)"
3. Marc valide commande renouvellement  
4. Système : même profil vCard, même URL, stats conservées
5. NFC France encode nouvelle carte avec identifiant NFC1023-1
6. Jean utilise nouvelle carte → tout fonctionne comme avant
```

#### **F3.2 Ajout Cartes Supplémentaires**
**Workflow :**
```
1. Marc (8 cartes existantes) recrute 2 commerciaux
2. Marc commande 2 cartes supplémentaires  
3. Système détecte client existant → ajoute au dashboard
4. Dashboard passe de 8 à 10 cartes
5. Marc configure les 2 nouveaux profils
```

#### **F3.3 Gestion États Cartes**  
**États possibles :**
- **À configurer** : vCard créée, profil vide  
- **Configuré** : Profil rempli, prêt à utiliser
- **Actif** : Carte utilisée (stats > 0)
- **Inactif** : Carte désactivée temporairement

**Actions selon état :**
- À configurer → [Configurer profil]
- Configuré → [Modifier] [Activer] [Aperçu]  
- Actif → [Modifier] [Stats] [Leads] [Renouveler]
- Inactif → [Réactiver] [Supprimer définitivement]

---

## 🔄 **Workflows Métier Complets**

### **Workflow A : Première Commande Entreprise**
```
COMMANDE :
Marc → Boutique NFC France → "Carte vCard" × quantité 8 → Commande #1023

CRÉATION AUTOMATIQUE :  
🤖 Système détecte commande payée
🤖 Boucle 8 fois :
   ├ Crée post virtual_card #501 "vCard Jean - Commande #1023"  
   ├ Génère identifiant NFC1023-1
   ├ Génère URL unique /vcard/a1b2c3d4e5f6
   ├ Métadonnées : order_id=1023, position=1, identifier=NFC1023-1
   └ [...] répète pour positions 2-8

EMAIL NOTIFICATION :
📧 "Vos 8 cartes NFC sont prêtes"
   ├ Identifiants : NFC1023-1 à NFC1023-8
   ├ URLs : 8 liens uniques vers chaque vCard  
   └ Lien dashboard : /mon-compte/dashboard-nfc/

CONFIGURATION MARC :
1. Marc clique dashboard → Page "Mes cartes" (8 cartes à configurer)
2. Marc clique [Modifier] carte 1 → Configure Jean Dupont  
3. Marc clique [Modifier] carte 2 → Configure Marie Martin
4. Marc laisse cartes 3-8 pour plus tard

RÉSULTAT :
✅ Dashboard affiche 2 cartes configurées + 6 à configurer
✅ Jean et Marie peuvent utiliser leurs cartes
✅ Stats/contacts séparés par profil
```

### **Workflow B : Utilisation Quotidienne**
```
UTILISATION JEAN :
👤 Jean rencontre prospect → Donne sa carte NFC1023-1  
👤 Prospect scan → URL Jean uniquement (/vcard/a1b2c3d4e5f6)
👤 Prospect voit profil Jean (nom, poste, contacts Jean)
👤 Prospect laisse ses coordonnées → Contact enregistré "profil Jean"

UTILISATION MARIE :
👤 Marie rencontre candidat → Donne sa carte NFC1023-2
👤 Candidat scan → URL Marie uniquement (/vcard/g7h8i9j0k1l2)  
👤 Candidat voit profil Marie (infos RH, contacts Marie)
👤 Candidat laisse coordonnées → Contact "profil Marie"

SUIVI MARC :  
📊 Marc dashboard → Stats globales : 112 vues (67 Jean + 45 Marie)
📊 Marc clique [Stats] Jean → Analytics Jean uniquement
📊 Marc clique [Leads] Marie → Contacts Marie uniquement  
📊 Aucun mélange de données entre profils
```

### **Workflow C : Renouvellement Après 1 An**
```
SITUATION :
Jean utilise carte NFC1023-1 depuis 1 an → 250 vues, 67 contacts
Carte physique abîmée → Besoin renouvellement

RENOUVELLEMENT :
1. Marc dashboard → Ligne "Jean Dupont" → [Renouveler]
2. Redirect boutique → Formulaire pré-rempli "Renouvellement NFC1023-1"  
3. Marc commande → Nouvelle commande #2156 liée à profil Jean existant
4. NFC France reçoit : "Encoder carte avec identifiant NFC1023-1"  
5. NFC France encode nouvelle carte physique → Même identifiant

CONTINUITÉ :
👤 Jean reçoit nouvelle carte physique  
👤 Jean utilise → Même URL /vcard/a1b2c3d4e5f6
👤 Profil identique, stats conservées (250 vues + nouvelles)  
👤 Contacts historiques préservés
✅ Continuité totale pour l'utilisateur final
```

---

## 📊 **Architecture Technique**

### **Base de Données**  

#### **Table wp_nfc_enterprise_cards :**
```sql
CREATE TABLE wp_nfc_enterprise_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,              -- Commande WooCommerce
    vcard_id INT NOT NULL,              -- Post virtual_card
    card_position INT NOT NULL,         -- Position dans commande (1,2,3...)
    card_identifier VARCHAR(20) NOT NULL, -- NFC1023-1, NFC1023-2...
    card_status ENUM('pending', 'configured', 'active', 'inactive') DEFAULT 'pending',
    company_name VARCHAR(200),          -- Nom entreprise
    main_user_id INT NOT NULL,          -- Marc (qui a commandé)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (card_identifier),
    INDEX (order_id, main_user_id)
);
```

#### **Extension virtual_card métadonnées :**
```php  
// Nouvelles métadonnées par vCard
'_enterprise_order_id' => 1023,           // Commande principale
'_enterprise_position' => 1,              // Position dans commande  
'_enterprise_identifier' => 'NFC1023-1',  // ID physique unique
'_enterprise_main_user' => 42,            // Marc (admin)
'_enterprise_company' => 'ACME Corp',     // Entreprise
'_card_configured' => true,               // Profil configuré ou non
'_renewal_history' => [2156, 3021],       // Commandes renouvellement
```

### **Workflow de Création**

#### **Hook WooCommerce étendu :**
```php
function nfc_enterprise_order_success($order_id) {
    $order = wc_get_order($order_id);
    
    foreach ($order->get_items() as $item) {
        if (is_vcard_product($item->get_product_id())) {
            $quantity = $item->get_quantity();
            
            // Créer X vCards selon quantité
            for ($position = 1; $position <= $quantity; $position++) {
                create_enterprise_vcard($order_id, $position, $order);
            }
        }
    }
    
    // Email notification avec tous identifiants
    send_enterprise_notification($order_id);
}

function create_enterprise_vcard($order_id, $position, $order) {
    // Identifiant physique unique
    $identifier = "NFC{$order_id}-{$position}";
    
    // Créer post virtual_card individuel
    $vcard_id = wp_insert_post([
        'post_title' => "vCard #{$position} - {$order->get_billing_first_name()} {$order->get_billing_last_name()} - Commande #{$order_id}",
        'post_type' => 'virtual_card',
        'post_status' => 'publish'
    ]);
    
    // Métadonnées vCard (reprendre existant)
    update_post_meta($vcard_id, 'firstname', $order->get_billing_first_name());
    update_post_meta($vcard_id, 'lastname', $order->get_billing_last_name());
    update_post_meta($vcard_id, 'email', $order->get_billing_email());
    update_post_meta($vcard_id, 'mobile', $order->get_billing_phone());
    
    // Métadonnées entreprise (nouvelles)  
    update_post_meta($vcard_id, '_enterprise_order_id', $order_id);
    update_post_meta($vcard_id, '_enterprise_position', $position);
    update_post_meta($vcard_id, '_enterprise_identifier', $identifier);
    update_post_meta($vcard_id, '_enterprise_main_user', $order->get_customer_id());
    update_post_meta($vcard_id, '_enterprise_company', $order->get_billing_company());
    
    // URL unique (réutiliser système existant)
    $unique_url = gtmi_vcard_generate_unique_url($vcard_id);
    update_post_meta($vcard_id, 'unique_url', $unique_url);
    
    // Enregistrer liaison entreprise
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'nfc_enterprise_cards',
        [
            'order_id' => $order_id,
            'vcard_id' => $vcard_id,  
            'card_position' => $position,
            'card_identifier' => $identifier,
            'company_name' => $order->get_billing_company(),
            'main_user_id' => $order->get_customer_id()
        ]
    );
    
    return $vcard_id;
}
```

---

## ✅ **Critères d'Acceptation Fonctionnels**

### **CA1. Commande Multi-cartes**
- [ ] ✅ Commande "Carte vCard" × 5 → 5 posts virtual_card créés
- [ ] ✅ 5 identifiants physiques uniques : NFC1023-1, NFC1023-2, NFC1023-3, NFC1023-4, NFC1023-5
- [ ] ✅ 5 URLs publiques différentes générées automatiquement  
- [ ] ✅ Email reçu liste les 5 identifiants + 5 liens + accès dashboard

### **CA2. Dashboard Entreprise "Mes cartes"**
- [ ] ✅ Page liste 5 lignes distinctes (1 par carte)
- [ ] ✅ Colonne identifiant affiche NFC1023-1, NFC1023-2...
- [ ] ✅ Colonne profil affiche nom/poste de chaque vCard
- [ ] ✅ Boutons [Modifier] [Stats] [Leads] [Renouveler] fonctionnels par ligne

### **CA3. Page "Modifier mes profils"**  
- [ ] ✅ Grille visuelle 5 cartes avec aperçus
- [ ] ✅ Clic [Modifier] → Page configuration vCard individuelle  
- [ ] ✅ Modification Jean n'affecte pas Marie (isolation profils)
- [ ] ✅ Aperçu carte temps réel pendant modification

### **CA4. Stats et Contacts Séparés**
- [ ] ✅ Page stats Jean → Analytics Jean uniquement
- [ ] ✅ Page contacts → Filtre par profil vCard d'origine  
- [ ] ✅ Stats globales = somme de tous profils
- [ ] ✅ Pas de mélange données entre profils

### **CA5. Renouvellement**
- [ ] ✅ Bouton [Renouveler] Jean → Formulaire pré-rempli NFC1023-1
- [ ] ✅ Renouvellement conserve URL, stats, contacts de Jean
- [ ] ✅ Historique renouvellements visible dashboard

### **CA6. Évolutivité**  
- [ ] ✅ Client 5 cartes + commande 3 cartes = dashboard 8 cartes
- [ ] ✅ Migration vCards existantes → format entreprise sans perte
- [ ] ✅ Performance fluide dashboard avec 50 cartes

---

## 🚫 **Limitations v1**

### **Non inclus v1 :**
- **Accès collaborateurs** : Jean ne peut pas encore gérer son propre profil
- **Permissions granulaires** : Pas de rôles admin/utilisateur différenciés
- **Workflow approbation** : Pas de validation avant publication modifications
- **Bulk operations** : Pas de modification en masse de profils
- **Templates profils** : Pas de modèles pré-configurés

### **Architecture préparée v2 :**
- Table utilisateurs avec champ enterprise_id
- Système permissions par profil  
- APIs accès collaborateur
- Notifications email automatiques

---

*Cahier des charges fonctionnel Système Multi-cartes vCard Entreprise v1*  
*Spécifications exactes pour développement sans ambiguïté*