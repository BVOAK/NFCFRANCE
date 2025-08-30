# Plan d'Action - Démo Vendredi 29 Août

## ⏰ **Temps disponible réel**
**Mercredi 27/08 14h → Vendredi 29/08 10h = 44 heures**
- Mercredi après-midi : 4h
- Jeudi : 8h  
- Vendredi matin : 2h
**Total développement : 14h effectives**

---

## 🎯 **Objectif démo : Impact visuel maximum**

### **Le client doit repartir en pensant :**
✅ "Site vitrine très professionnel"  
✅ "Dashboard client impressionnant"  
✅ "Système bien pensé pour tous les produits"  
✅ "Équipe qui maîtrise le développement"

---

## 📋 **Planning détaillé**

### **🔥 MERCREDI 27/08 (14h-18h) - 4h**
#### **Priorité 1 : Site vitrine Elementor (4h)**

**14h-16h : Homepage impact**
- [ ] **Hero section** inspirée Kipful (titre + CTA + visuel cartes)
- [ ] **Section produits** : 3 cartes (PVC, Bois, Metal) avec boutons adaptés
- [ ] **Stats écologiques** : "10 arbres sauvés, 2000L eau économisée"

**16h-18h : Page Boutique + Produits**  
- [ ] **Catalogue produits** avec filtres (vCard / Avis Google)
- [ ] **Fiches produits** avec 2 boutons :
  - "Personnaliser en ligne" (vers configurateur)  
  - "Commander et envoyer fichiers" (vers panier)
- [ ] **Integration widget** : masquer Add to Cart par défaut

---

### **⚡ JEUDI 28/08 (9h-17h) - 8h**  
#### **Priorité 2 : Dashboard adaptatif (8h)**

**9h-12h : Sélecteur multi-commandes**
- [ ] **Analyser commandes utilisateur** : détecter type produit (vCard/Avis Google)
- [ ] **Header dashboard** : dropdown sélection commande active
- [ ] **Interface conditionnelle** : masquer/afficher selon type produit

**12h-13h : Pause déjeuner**

**13h-16h : Dashboard Avis Google spécialisé**
- [ ] **Page "Configuration Avis"** : champ URL + preview
- [ ] **QR Code vers URL Google** : génération automatique  
- [ ] **Stats redirection** : compteurs basiques (scans → clics)
- [ ] **Interface simplifiée** : pas de vCard, juste URL + QR + stats

**16h-17h : Polish et responsive**
- [ ] **Tests multi-devices** : dashboard fluide sur mobile
- [ ] **Données de demo** : stats réalistes, comptes de test
- [ ] **Transitions CSS** : animations smooth

---

### **✅ VENDREDI 29/08 (8h-10h) - 2h**
#### **Priorité 3 : Finalisation démo**

**8h-9h : Tests complets**
- [ ] **Scénario complet** : Site → Commande → Dashboard
- [ ] **Comptes de test** : vCard + Avis Google avec vraies données
- [ ] **Performance** : temps chargement < 3s sur toutes les pages

**9h-10h : Préparation présentation**  
- [ ] **Script démo** : transitions fluides entre sections
- [ ] **Backup plan** : screenshots si problème technique
- [ ] **Questions/réponses** : arguments pour la suite du développement

---

## 🎪 **Scénario de démo (15 min)**

### **1. Site vitrine (3 min)**
```
"Voici notre nouveau site NFC France, inspiré des leaders du secteur"
→ Homepage : Hero + produits + écologie
→ Boutique : Catalogue vCard + Avis Google  
→ "Regardez, deux options : personnalisation en ligne ou envoi fichiers"
```

### **2. Dashboard vCard existant (4 min)**  
```  
"Le cœur du système : le dashboard client automatique"
→ Overview : stats, actions rapides
→ Edition vCard : modification temps réel
→ QR Codes : génération et téléchargement
→ "Tout ça est déjà opérationnel"
```

### **3. Dashboard Avis Google (3 min)**
```
"Nouveauté : dashboard spécialisé pour les cartes Avis Google"  
→ Sélecteur de commande : "Le client peut avoir les deux types"
→ Interface URL : "Plus simple, juste l'URL de leur page d'avis"
→ QR vers Google : "Redirection directe vers leurs avis"
```

### **4. Vision développement (3 min)**
```
"Workflow envoi fichiers : pour personnalisation sans configurateur"
→ Mockup du système : commande → email → upload → validation
→ "Configurateurs Bois/Metal : en attente de vos contraintes techniques"  
→ "E-commerce complet : SumUp + livraison pour le 15 septembre"
```

### **5. Questions & suite (2 min)**
```
→ Feedback sur l'approche multi-produits
→ Validation des priorités développement  
→ Planning post-démo → livraison 15/09
```

---

## 🔧 **Spécifications techniques**

### **Détection type produit (dashboard adaptatif) :**
```php
// Dans class-dashboard-manager.php
function get_user_product_types($user_id) {
    $orders = wc_get_orders(['customer' => $user_id]);
    $product_types = [];
    
    foreach($orders as $order) {
        foreach($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            
            // IDs des produits vCard (571 = PVC, autres à ajouter)
            if (in_array($product_id, [571, 572, 573])) {
                $product_types['vcard'][] = [
                    'order_id' => $order->get_id(),
                    'product_name' => $item->get_name(),
                    'type' => 'vcard'
                ];
            }
            
            // IDs des produits Avis Google (à définir)  
            if (in_array($product_id, [580, 581])) {
                $product_types['google_reviews'][] = [
                    'order_id' => $order->get_id(), 
                    'product_name' => $item->get_name(),
                    'type' => 'google_reviews'
                ];
            }
        }
    }
    
    return $product_types;
}
```

### **Interface sélecteur dashboard :**
```html
<!-- Header dashboard avec sélecteur -->
<div class="dashboard-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Mon Dashboard NFC</h1>
        
        <?php if (count($user_products) > 1): ?>
        <div class="dashboard-selector">
            <label class="form-label">Commande active :</label>
            <select class="form-select" onchange="switchDashboard(this.value)">
                <?php foreach($user_products as $type => $products): ?>
                    <?php foreach($products as $product): ?>
                    <option value="<?= $product['order_id'] ?>">
                        <?= $product['product_name'] ?> (Commande #<?= $product['order_id'] ?>)
                    </option>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>
</div>
```

---

## 🚨 **Risques et plan B**

### **Si dashboard adaptatif pas prêt :**
- [ ] **Montrer concept** avec screenshots/mockups
- [ ] **Demo sur 2 comptes séparés** (un vCard, un Avis Google)

### **Si site vitrine incomplet :**  
- [ ] **Focus sur homepage** uniquement mais parfaite
- [ ] **Maquettes Figma** pour les autres pages

### **Si problèmes techniques :**
- [ ] **Version locale** prête en backup
- [ ] **Vidéos de demo** pré-enregistrées
- [ ] **Slides PowerPoint** avec captures d'écran

---

## ✅ **Critères de validation démo**

### **Minimum vital pour impressionner :**
- [ ] Site vitrine homepage professionnel
- [ ] Dashboard vCard fluide et sans bugs
- [ ] Concept dashboard Avis Google clair  
- [ ] Vision technique cohérente pour la suite

### **Bonus si temps disponible :**
- [ ] Boutons produits fonctionnels (masquage Add to Cart)
- [ ] Sélecteur multi-commandes opérationnel
- [ ] Page Boutique avec filtres

---

## 📞 **Besoins de ta part**

### **Avant mercredi soir :**
1. **Accès Elementor** : site de dev disponible ?
2. **IDs produits** : quels IDs pour Avis Google vs vCard ?  
3. **Assets visuels** : logo NFC France, photos cartes ?

### **Validation jeudi matin :**
1. **Aperçu site vitrine** : feedback rapide sur direction
2. **Logique dashboard** : validation de l'approche technique

**C'est parti pour un sprint intensif ! 🚀**

---

*Plan démo v1.0 - Focus impact maximum*  
*14h de dev pour impressionner*