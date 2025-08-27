# Plan d'action Dashboard Simple - Version 3
**Après insertion données SQL - Focus vCard 1013**

---

## 🎯 **État actuel**
- ✅ **Routing fonctionnel** : Navigation entre pages OK
- ✅ **Données de test** : 25 leads + 90 stats insérées pour vCard 1013
- ❌ **Problème** : Dashboard affiche vCard 1009 au lieu de 1013
- 🔄 **Page Overview** : Partiellement fonctionnelle avec données simulées

---

## 🚀 **TÂCHE 1 : Fix sélection vCard (30 min)**

### **Problème :**
```php
// Dans get_user_vcards() - Prend la première vCard trouvée
$vcards = get_posts([...]);
$primary_vcard = $vcards[0]; // ❌ Pas forcément la bonne
```

### **Solution :**
```php
// Prendre la vCard de la dernière commande
function get_user_primary_vcard($user_id) {
    // 1. Récupérer la dernière commande de l'utilisateur
    // 2. Trouver la vCard liée à cette commande
    // 3. Fallback sur la plus récente si pas de commande
}
```

### **Modification :**
- `class-dashboard-manager.php` → `get_user_vcards()`
- Logic : Order DESC par `post_date` ou liaison commande

### **Test :**
- Dashboard doit afficher vCard 1013
- APIs doivent remonter les 25 leads + 90 stats

---

## 🚀 **TÂCHE 2 : Overview.php avec vraies données (45 min)**

### **Objectif :**
Overview fonctionnel avec les vraies données insérées

### **Modifications :**
1. **Connexion APIs réelles** (stats/leads de vCard 1013)
2. **Calculs stats corrects** (25 leads, 90 stats sur 3 mois)
3. **Graphique avec vraies données** (Timeline Nov→Fév)
4. **Activité récente** basée sur vrais leads

### **Tests :**
- [ ] Stats remontent correctement
- [ ] Graphique affiche l'évolution réelle
- [ ] Contacts count = 25
- [ ] Growth percentages cohérents

---

## 🚀 **TÂCHE 3 : vCard-edit.php avec Custom URL (1h30)**

### **3A : Base vCard-edit (45 min)**
- Template `vcard-edit.php` fonctionnel
- Formulaire complet avec tous champs ACF
- Sauvegarde via AJAX
- Preview temps réel

### **3B : Custom URL integration (45 min)**
- Section "Configuration redirection"
- Toggle vCard / Custom URL
- Champ URL personnalisée
- JavaScript pour toggle interface

### **Modification single-virtual_card.php :**
```php
// Début du template
$custom_url_enabled = get_post_meta(get_the_ID(), 'custom_url_enabled', true);
$custom_url = get_post_meta(get_the_ID(), 'custom_url', true);

if ($custom_url_enabled && !empty($custom_url)) {
    // Track + Redirect
    wp_redirect($custom_url, 301);
    exit;
}
```

### **Tests :**
- [ ] Formulaire vCard sauvegarde
- [ ] Toggle Custom URL fonctionne
- [ ] Redirection effective si Custom URL activée

---

## 🚀 **TÂCHE 4 : Preview.php avec Custom URL (1h)**

### **Objectif :**
Page aperçu qui gère les 2 modes :
1. **Mode vCard** : Preview responsive de la vCard
2. **Mode Custom URL** : Info sur la redirection + test

### **Interface :**
```
┌─ APERÇU ──────────────────────────────────────┐
│ Mode actuel : ● vCard classique               │
│               ○ Redirection URL personnalisée │
│                                               │
│ [Preview responsive] OU [Info redirection]    │
└───────────────────────────────────────────────┘
```

### **Tests :**
- [ ] Preview vCard responsive (mobile/desktop)
- [ ] Info Custom URL si activée
- [ ] Bouton test redirection

---

## 📊 **Récapitulatif des livrables**

### **✅ Fonctionnel après les 4 tâches :**
1. **Dashboard routing** complet
2. **Overview** avec vraies données (25 leads, 90 stats)
3. **vCard-edit** avec Custom URL
4. **Preview** responsive + Custom URL
5. **Redirection** single-virtual_card.php

### **📂 Fichiers modifiés/créés :**
```
includes/dashboard/
├── class-dashboard-manager.php     # 🔄 Fix sélection vCard

templates/dashboard/simple/
├── overview.php                    # 🔄 Vraies données
├── vcard-edit.php                  # 🆕 Complet + Custom URL  
└── preview.php                     # 🆕 Responsive + Custom URL

templates/
└── single-virtual_card.php         # 🔄 Redirection Custom URL
```

---

## ⏱️ **Timeline estimée**

| Tâche | Durée | Cumul |
|-------|-------|-------|
| Fix sélection vCard | 30 min | 30 min |
| Overview vraies données | 45 min | 1h15 |
| vCard-edit + Custom URL | 1h30 | 2h45 |
| Preview + Custom URL | 1h | 3h45 |
| **TOTAL** | **3h45** | |

---

## 🎯 **Validation étape par étape**

### **Après TÂCHE 1 :**
- Dashboard affiche "vCard #1013"
- Debug montre les bonnes APIs connectées

### **Après TÂCHE 2 :**
- Stats Overview : 25 contacts, 90 vues
- Graphique avec timeline Nov→Fév
- Activité récente avec vrais noms

### **Après TÂCHE 3 :**
- Formulaire vCard sauvegarde
- Section Custom URL fonctionnelle
- single-virtual_card.php redirige si nécessaire

### **Après TÂCHE 4 :**
- Preview responsive complet
- Gestion des 2 modes (vCard/Custom URL)
- Tests de redirection

---

## 📏 **Longueur de conversation**

**Messages actuels :** ~20-25 messages  
**Limite optimale :** ~40-50 messages  
**Marge restante :** ~20-25 messages

### **Recommandation :**
- ✅ **On peut finir les 4 tâches** dans cette conversation
- ✅ **Suffisant pour Overview + vCard-edit + Preview**
- 🔄 **Nouveau chat pour pages Contacts/Statistics** (plus complexes)

---

## 🚀 **Prêt pour TÂCHE 1 ?**

Je commence par fixer la sélection de vCard pour qu'elle prenne 1013 au lieu de 1009 ?

**Code à modifier dans `class-dashboard-manager.php` :**
```php
public function get_user_vcards($user_id) {
    return get_posts([
        'post_type' => 'virtual_card',
        'meta_query' => [
            [
                'key' => 'customer_id', // ou 'user' selon ta structure
                'value' => $user_id,
                'compare' => '='
            ]
        ],
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'date',        // 🆕 Plus récente en premier
        'order' => 'DESC'           // 🆕 Descending
    ]);
}
```

**Tu confirmes qu'on attaque la TÂCHE 1 ?** 💪