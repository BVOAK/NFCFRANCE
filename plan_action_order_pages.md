# Plan d'action : Pages order-received et admin commande

## 🎯 **OBJECTIF GLOBAL**
Améliorer l'affichage des configurations NFC sur les pages order-received (client) et commande admin (backend) avec accès complet aux assets.

---

## 📝 **ÉTAPE 1 : Diagnostic et analyse des bugs**

### **Tâche 1.1 : Analyse page order-received**
**Temps estimé :** 1h  
**Objectif :** Identifier pourquoi les infos NFC ne remontent pas

#### **Prompt de développement :**
```
Contexte : Page order-received ne affiche pas les infos de configuration NFC correctement

Analyser :
1. Le template order-received existant et voir s'il utilise les hooks NFC
2. Les fonctions get_customer_screenshot_url() et leur fonctionnement
3. Pourquoi le bouton "Voir en grand" retourne "Aucun screenshot trouvé"
4. Les permissions d'accès aux screenshots pour les clients

Vérifier si :
- Les métadonnées _nfc_config_complete sont bien sauvées en commande
- Les URLs de screenshot sont générées correctement
- Les nonces de sécurité sont valides
- Les fichiers screenshot existent physiquement

Livrable : 
- Diagnostic précis du problème
- Localisation exacte des bugs
```

### **Tâche 1.2 : Analyse page admin commande** 
**Temps estimé :** 1h
**Objectif :** Identifier pourquoi les téléchargements ne fonctionnent pas

#### **Prompt de développement :**
```
Contexte : Page admin commande, boutons de téléchargement non fonctionnels

Analyser :
1. Les fonctions get_download_urls() dans NFC_File_Handler
2. Les handlers AJAX pour download_logo() et download_screenshot()
3. Les URLs générées pour les téléchargements admin
4. Les permissions et nonces admin

Vérifier si :
- Les métadonnées images (_nfc_image_recto_data, _nfc_logo_verso_data) sont sauvées
- Les handlers AJAX sont bien enregistrés
- Les chemins de fichiers sont corrects
- Les conversions base64 vers fichiers fonctionnent

Livrable :
- Liste des fonctions cassées
- Corrections nécessaires pour les téléchargements
```

---

## 🔧 **ÉTAPE 2 : Corrections des bugs critiques**

### **Tâche 2.1 : Correction handlers screenshots clients**
**Temps estimé :** 2h  
**Fichiers concernés :** `nfc-customer-integration.php`, `nfc-file-handler.php`

#### **Prompt de développement :**
```
Contexte : Corriger l'accès aux screenshots pour les clients sur order-received

Objectifs :
1. Corriger get_customer_screenshot_url() pour générer des URLs valides
2. Fixer serve_customer_screenshot() pour servir les images
3. Vérifier les nonces et permissions client
4. S'assurer que les fichiers screenshot existent et sont accessibles

Implémentations :
- Débugger et corriger la génération d'URLs sécurisées
- Améliorer la gestion d'erreurs (fichier non trouvé, permissions)
- Ajouter des logs pour tracer les erreurs
- Tester avec différents statuts de commande

Tests à effectuer :
- URL de screenshot générée correctement
- Image s'affiche sur order-received
- Bouton "Voir en grand" fonctionne
- Permissions respectées (client propriétaire uniquement)

Livrable : Screenshots fonctionnels sur order-received
```

### **Tâche 2.2 : Correction handlers téléchargements admin**
**Temps estimé :** 2h  
**Fichiers concernés :** `nfc-file-handler.php`, `wc-integration.php`

#### **Prompt de développement :**
```
Contexte : Corriger les téléchargements admin (logos + screenshots)

Objectifs :
1. Fixer download_logo() pour télécharger les images recto/verso
2. Fixer download_screenshot() pour les aperçus
3. Améliorer get_download_urls() pour générer les bonnes URLs
4. Gérer les conversions base64 → fichiers temporaires

Implémentations :
- Corriger la génération de fichiers temporaires depuis base64
- Améliorer les headers de téléchargement
- Ajouter support logo verso en plus du recto
- Gérer les erreurs (données corrompues, fichiers manquants)

Structure URLs à générer :
- /wp-admin/admin-ajax.php?action=nfc_download_logo&order_id=X&item_id=Y&type=recto
- /wp-admin/admin-ajax.php?action=nfc_download_logo&order_id=X&item_id=Y&type=verso  
- /wp-admin/admin-ajax.php?action=nfc_download_screenshot&order_id=X&item_id=Y&type=full

Tests à effectuer :
- Boutons "Télécharger logo" fonctionnent
- Téléchargement screenshots admin
- Noms de fichiers corrects (ordre-123-logo-recto.png)
- Permissions admin respectées

Livrable : Téléchargements admin 100% fonctionnels
```

---

## 🎨 **ÉTAPE 3 : Amélioration page order-received**

### **Tâche 3.1 : Template order-received amélioré**
**Temps estimé :** 3h  
**Fichiers concernés :** Template order-received, hooks NFC

#### **Prompt de développement :**
```
Contexte : Créer un bloc "Configuration personnalisée" complet sur order-received

Objectifs :
1. Centraliser TOUTES les infos NFC dans un bloc unique et élégant
2. Afficher le screenshot en grand avec lightbox
3. Améliorer l'UX avec une présentation moderne
4. Ajouter informations logo verso et paramètres complets

Design requis :
- Header "🎨 Configuration personnalisée"  
- Screenshot en grand au centre (cliquable pour lightbox)
- Informations organisées : Couleur, Nom, Logo recto, Logo verso, Infos verso
- Timeline de production (commande → personnalisation → expédition)
- Responsive mobile

Fonctionnalités :
- Lightbox pour agrandir le screenshot
- Bouton "Télécharger l'aperçu" pour le client
- Statut de production en temps réel
- Design cohérent avec la charte NFC France

Structure HTML à créer :
```html
<div class="nfc-configuration-complete">
  <h3>🎨 Configuration personnalisée</h3>
  <div class="nfc-screenshot-showcase">
    <!-- Screenshot grand format -->
  </div>
  <div class="nfc-config-details">
    <!-- Toutes les infos de config -->
  </div>
  <div class="nfc-production-timeline">
    <!-- Timeline statut -->
  </div>
</div>
```

Tests à effectuer :
- Affichage correct sur desktop/mobile
- Lightbox fonctionnel 
- Toutes les infos de configuration remontent
- Design cohérent et professionnel

Livrable : Page order-received avec bloc configuration complet
```

---

## 🛠️ **ÉTAPE 4 : Amélioration page admin commande**

### **Tâche 4.1 : Interface admin enrichie**
**Temps estimé :** 3h  
**Fichiers concernés :** `wc-integration.php`

#### **Prompt de développement :**
```
Contexte : Améliorer l'affichage admin des commandes NFC pour reproduction des configurations

Objectifs :
1. Affichage complet de TOUTES les métadonnées de configuration
2. Boutons de téléchargement fonctionnels pour tous les assets
3. Aperçu screenshot intégré dans l'interface admin
4. Informations verso (logo + paramètres d'affichage)
5. Design professionnel et organisé

Interface à créer :
- Section "🎨 Cartes NFC Personnalisées" dans chaque commande
- Pour chaque item NFC :
  * Aperçu screenshot miniature
  * Tableau complet des paramètres
  * Boutons téléchargement (Logo recto, Logo verso, Screenshot)
  * Informations techniques (taille, position, échelle)

Fonctionnalités admin :
- Téléchargement logo recto avec nom "commande-123-item-456-recto.png"
- Téléchargement logo verso avec nom "commande-123-item-456-verso.png"
- Téléchargement screenshot HD "commande-123-item-456-apercu.png"
- Affichage paramètres techniques (positions X/Y, échelles, etc.)
- Bouton "Voir configuration JSON complète" (collapsible)

Layout admin :
```html
<div class="nfc-admin-config">
  <h4>Carte NFC - [NOM_PRODUIT]</h4>
  <div class="nfc-admin-grid">
    <div class="nfc-config-info">
      <!-- Tableau des paramètres -->
    </div>
    <div class="nfc-admin-preview">
      <!-- Screenshot + boutons téléchargement -->
    </div>
  </div>
</div>
```

Tests à effectuer :
- Tous les téléchargements fonctionnent
- Aperçus s'affichent correctement
- Informations complètes et exactes
- Interface intuitive pour l'équipe production

Livrable : Interface admin complète pour reproduction des cartes
```

---

## ✅ **ÉTAPE 5 : Tests et finitions**

### **Tâche 5.1 : Tests end-to-end**
**Temps estimé :** 2h

#### **Tests à effectuer :**
1. **Workflow client complet :**
   - Configuration dans le configurateur
   - Commande → Paiement → Order-received
   - Vérifier que toutes les infos s'affichent
   - Tester le lightbox screenshot

2. **Workflow admin complet :**
   - Ouvrir commande dans l'admin
   - Télécharger tous les assets (recto, verso, screenshot)
   - Vérifier que les fichiers sont corrects
   - Reproduire la configuration avec les assets

3. **Tests edge cases :**
   - Commande sans logo
   - Commande avec seulement logo recto
   - Commande avec logo recto + verso
   - Différents formats d'images (PNG, JPG)

### **Tâche 5.2 : Documentation et formation**
**Temps estimé :** 1h

#### **Livrables :**
- Documentation admin : "Comment télécharger les assets d'une commande NFC"
- Guide client : "Voir sa configuration sur order-received"
- Checklist de reproduction pour l'équipe production

---

## 📊 **RÉCAPITULATIF TEMPS**

| Étape | Tâches | Temps estimé |
|-------|--------|--------------|
| 1. Diagnostic | 2 tâches | 2h |
| 2. Corrections bugs | 2 tâches | 4h |
| 3. Page order-received | 1 tâche | 3h |
| 4. Page admin | 1 tâche | 3h |
| 5. Tests et doc | 2 tâches | 3h |
| **TOTAL** | **8 tâches** | **15h** |

---

## 🎯 **RÉSULTAT FINAL ATTENDU**

### **Page order-received (client) :**
- ✅ Bloc "Configuration personnalisée" complet avec toutes les infos
- ✅ Screenshot en grand format avec lightbox fonctionnel
- ✅ Design moderne et responsive
- ✅ Timeline de production

### **Page admin commande :**
- ✅ Interface complète pour reproduction des cartes
- ✅ Téléchargements fonctionnels : logo recto, logo verso, screenshot
- ✅ Toutes les métadonnées techniques affichées
- ✅ Aperçus intégrés dans l'interface admin
- ✅ Workflow optimisé pour l'équipe production NFC France

Les deux pages permettront un workflow fluide : le client voit sa configuration complète, et l'admin peut télécharger tous les assets pour reproduire exactement la carte commandée.