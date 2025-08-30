# Configurateur NFC France - Documentation finale

## 📋 Vue d'ensemble du projet

**Objectif :** Créer un configurateur de cartes NFC personnalisées avec gestion des variations WooCommerce pour une expérience utilisateur optimale et une intégration e-commerce native.

**Architecture :** Page produit SEO + Configurateur dédié + Variations WooCommerce

**Estimation développement :** 12-16 heures (2-3 jours)

---

## 🎯 Spécifications fonctionnelles finales

### 1. Workflow utilisateur complet

```
Page produit WC → [Personnaliser en ligne] → Configurateur → [Ajouter panier] → Panier avec variation
```

1. **Page produit WooCommerce** : Produit variable avec bouton "Personnaliser en ligne"
2. **Configurateur full-screen** : Interface dédiée `/configurateur?product_id=571`
3. **Configuration interactive** : Couleurs + image + informations utilisateur
4. **Ajout panier intelligent** : Variation WC + métadonnées personnalisation
5. **Tunnel standard WooCommerce** : Panier, commande, paiement

### 2. Structure produit WooCommerce

```php
Produit parent: "Carte NFC Personnalisée" (ID: 571)
├── Attribut: Couleur (pa_couleur)
│   ├── Terme: Blanc (slug: blanc)
│   └── Terme: Noir (slug: noir)
├── Variation 1: Carte Blanche (ID: 571001, SKU: NFC-CARD-WHITE, Prix: 30€)
└── Variation 2: Carte Noire (ID: 571002, SKU: NFC-CARD-BLACK, Prix: 30€)
```

### 3. Interface configurateur

**Layout desktop (1400px) :**
```
┌─────────────────────────────────────────────────────────────────┐
│ Header: NFC France | Configurateur | Prix: 30,00€               │
├─────────────────────────────────────────────────────────────────┤
│ ┌─────────────────────────┐ │ ┌───────────────────────────────┐ │
│ │      PREVIEW            │ │ │        CONFIGURATION          │ │
│ │                         │ │ │                               │ │
│ │ ┌─────────────────────┐ │ │ │ 🎨 Couleur de la carte        │ │
│ │ │     RECTO           │ │ │ │   ○ Blanc    ● Noir          │ │
│ │ │ [IMG] Jean Dupont   │ │ │ │                               │ │
│ │ └─────────────────────┘ │ │ │ 📷 Image ou logo              │ │
│ │                         │ │ │   [Zone upload]               │ │
│ │ ┌─────────────────────┐ │ │ │   [Curseur taille]            │ │
│ │ │     VERSO           │ │ │ │                               │ │
│ │ │ QR    Jean Dupont   │ │ │ │ 👤 Informations               │ │
│ │ │ CODE  nfcfrance.com │ │ │ │   Nom: [____] Prénom: [____]  │ │
│ │ └─────────────────────┘ │ │ │                               │ │
│ └─────────────────────────┘ │ │ [Ajouter au panier - 30,00€]  │ │
└─────────────────────────────┘ └───────────────────────────────┘ │
```

### 4. Fonctionnalités détaillées

#### 4.1 Gestion couleurs adaptative
- **Sélection couleur** → Changement variation WooCommerce
- **Update visuel instantané** : Fond carte + texte + QR code
- **Logique adaptative** :
  - Carte blanche → QR noir + texte noir
  - Carte noire → QR blanc + texte blanc

#### 4.2 Upload et manipulation image
- **Formats acceptés** : JPG, PNG, SVG (max 2MB)
- **Validation côté client** : Taille, type MIME, dimensions
- **Preview instantané** : Affichage sur carte recto
- **Redimensionnement** : Curseur 50%-150%
- **Contraintes** : Image reste dans les limites de la carte

#### 4.3 Données personnalisation
```json
{
  "variation_id": 571001,
  "color": "white",
  "user": {
    "firstName": "Jean",
    "lastName": "Dupont"
  },
  "image": {
    "data": "base64_encoded_image",
    "scale": 120,
    "position": {"x": 0, "y": 0}
  },
  "previews": {
    "recto": "base64_preview_recto",
    "verso": "base64_preview_verso"
  },
  "timestamp": 1640995200
}
```

---

## 🛠️ Architecture technique

### 1. Structure fichiers

```
/wp-content/themes/uicore/
├── configurator/
│   ├── index.php                 # Page configurateur
│   ├── assets/
│   │   ├── css/
│   │   │   └── configurator.css  # Styles configurateur
│   │   ├── js/
│   │   │   ├── configurator.js   # Logique principale
│   │   │   ├── canvas-handler.js # Gestion images/preview
│   │   │   └── wc-integration.js # Intégration WooCommerce
│   │   └── images/
│   │       ├── card-templates/   # Templates cartes
│   │       └── placeholders/     # Images par défaut
│   ├── includes/
│   │   ├── class-nfc-product.php     # Gestion produits variables
│   │   ├── class-nfc-configurator.php # Logique configurateur
│   │   ├── ajax-handlers.php          # Endpoints Ajax
│   │   └── wc-integration.php         # Hooks WooCommerce
│   └── templates/
│       ├── configurator-page.php     # Template page
│       └── card-preview.php          # Template preview
```

### 2. Intégration WooCommerce

#### 2.1 Produit variable automatique
```php
// Création du produit variable
class NFC_Product_Manager {
    public function create_variable_product() {
        // 1. Créer attribut "Couleur"
        // 2. Créer termes (blanc, noir)
        // 3. Créer produit parent variable
        // 4. Créer variations avec prix
        // 5. Marquer comme configurable
    }
}
```

#### 2.2 Gestion variations
```php
// Récupération variations pour JavaScript
function nfc_get_variations_config($product_id) {
    return [
        'white' => [
            'id' => 571001,
            'price' => 30.00,
            'sku' => 'NFC-CARD-WHITE',
            'stock' => 'instock'
        ],
        'black' => [
            'id' => 571002,
            'price' => 30.00,
            'sku' => 'NFC-CARD-BLACK',
            'stock' => 'instock'
        ]
    ];
}
```

#### 2.3 Ajout panier avec données
```php
// Hook WooCommerce pour ajouter métadonnées
add_filter('woocommerce_add_cart_item_data', 'nfc_add_cart_item_data');
function nfc_add_cart_item_data($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['nfc_config'])) {
        $cart_item_data['nfc_config'] = $_POST['nfc_config'];
        $cart_item_data['nfc_unique_key'] = uniqid(); // Force unicité
    }
    return $cart_item_data;
}
```

### 3. API endpoints

```php
// REST API pour configurateur
register_rest_route('nfc/v1', '/configurator/(?P<product_id>\d+)', [
    'methods' => 'GET',
    'callback' => 'nfc_get_configurator_data'
]);

register_rest_route('nfc/v1', '/save-config', [
    'methods' => 'POST',
    'callback' => 'nfc_save_configuration'
]);

register_rest_route('nfc/v1', '/add-to-cart', [
    'methods' => 'POST',
    'callback' => 'nfc_add_configured_to_cart'
]);
```

### 4. Frontend JavaScript

```javascript
class NFCConfigurator {
    constructor() {
        this.variations = window.nfcConfig.variations;
        this.selectedVariation = null;
        this.config = {
            color: 'white',
            user: {},
            image: null
        };
    }
    
    changeColor(color) {
        this.selectedVariation = this.variations[color];
        this.updateVisuals(color);
        this.updatePrice();
    }
    
    addToCart() {
        const data = {
            variation_id: this.selectedVariation.id,
            nfc_config: this.config,
            nonce: window.nfcConfig.nonce
        };
        
        this.ajaxAddToCart(data);
    }
}
```

---

## 🎨 Spécifications design

### 1. Couleurs et identité

```css
:root {
  /* Couleurs principales NFC France */
  --nfc-primary: #667eea;
  --nfc-secondary: #007cba;
  --nfc-success: #10b981;
  --nfc-background: #f8fafc;
  --nfc-border: #e2e8f0;
  --nfc-text: #1a202c;
  --nfc-text-light: #64748b;
  
  /* Couleurs cartes */
  --card-white-bg: #ffffff;
  --card-white-text: #333333;
  --card-black-bg: #1a1a1a;
  --card-black-text: #ffffff;
}
```

### 2. Dimensions cartes (format 85x55mm)

```css
.card-preview {
  width: 340px;  /* 85mm × 4 */
  height: 220px; /* 55mm × 4 */
  border-radius: 16px; /* 4mm × 4 */
  aspect-ratio: 85/55;
}

/* Zone image utilisateur */
.logo-area {
  width: 80px;   /* 20mm × 4 */
  height: 80px;  /* 20mm × 4 */
  border-radius: 12px; /* 3mm × 4 */
}
```

### 3. Responsive breakpoints

```css
/* Desktop large: 1400px+ */
.configurator { grid-template-columns: 1fr 400px; }

/* Desktop: 1200px-1399px */
.configurator { grid-template-columns: 1fr 380px; }

/* Tablet: 768px-1199px */
.configurator { 
  grid-template-columns: 1fr;
  .cards-container { flex-direction: row; }
}

/* Mobile: <768px */
.configurator {
  grid-template-columns: 1fr;
  .cards-container { flex-direction: column; }
  .card-preview { width: 300px; height: 190px; }
}
```

---

## ⚙️ Configuration et installation

### 1. Prérequis

**WordPress :**
- WordPress 5.8+
- WooCommerce 6.0+
- PHP 8.0+
- MySQL 5.7+

**Thème :**
- UiCore Pro (ou compatible)
- Support des custom post types
- jQuery activé

### 2. Installation étape par étape

#### Étape 1 : Structure fichiers (15 min)
```bash
# Créer l'arborescence dans le thème
mkdir -p wp-content/themes/uicore/configurator/{assets/{css,js,images},includes,templates}
```

#### Étape 2 : Produit WooCommerce (30 min)
```php
// Dans functions.php - une seule fois
add_action('init', function() {
    if (isset($_GET['nfc_setup'])) {
        $nfc_product = new NFC_Product_Manager();
        $product_id = $nfc_product->create_variable_product();
        echo "Produit créé avec ID: " . $product_id;
        exit;
    }
});
```

#### Étape 3 : Configuration WordPress (15 min)
```php
// Page configurateur
$configurator_page = wp_insert_post([
    'post_title' => 'Configurateur NFC',
    'post_name' => 'configurateur',
    'post_status' => 'publish',
    'post_type' => 'page',
    'page_template' => 'configurator/templates/configurator-page.php'
]);
```

#### Étape 4 : Tests (30 min)
- Test création produit : `/wp-admin?nfc_setup=1`
- Test configurateur : `/configurateur?product_id=571`
- Test ajout panier : Configuration → Panier
- Test commande : Panier → Checkout → Commande

### 3. Configuration avancée

#### 3.1 Personnalisation couleurs
```php
// Ajouter d'autres couleurs (V2)
add_filter('nfc_available_colors', function($colors) {
    $colors['blue'] = [
        'label' => 'Bleu',
        'hex' => '#3b82f6',
        'price' => 35.00
    ];
    return $colors;
});
```

#### 3.2 Validation personnalisée
```php
// Validation upload images
add_filter('nfc_validate_image', function($file) {
    if ($file['size'] > 2 * 1024 * 1024) {
        return new WP_Error('file_too_large', 'Image trop volumineuse');
    }
    return true;
});
```

---

## 🚀 Plan de développement détaillé

### Phase 1 : Foundation (Jour 1 - 4h)

**1.1 Structure et architecture (1h)**
- [x] Créer l'arborescence fichiers
- [x] Configuration WordPress de base
- [x] Page configurateur et routing

**1.2 Produit WooCommerce (1.5h)**
- [x] Classe `NFC_Product_Manager`
- [x] Création produit variable + variations
- [x] Attributs et termes de couleur
- [x] Configuration métadonnées

**1.3 Page configurateur base (1.5h)**
- [x] Template PHP de base
- [x] Structure HTML/CSS foundation
- [x] Grid layout responsive
- [x] Header avec prix dynamique

### Phase 2 : Interface utilisateur (Jour 1-2 - 6h)

**2.1 Preview cartes (2h)**
- [x] HTML structure recto/verso
- [x] CSS styles cartes blanc/noir
- [x] Contraintes dimensions 85x55mm
- [x] Animations et transitions

**2.2 Sélecteur couleurs (1h)**
- [x] Interface radio buttons stylés
- [x] Logique changement couleur
- [x] Update prix en temps réel
- [x] Sauvegarde variation sélectionnée

**2.3 Upload images (2h)**
- [x] Zone drop + input file
- [x] Validation côté client
- [x] Preview image uploadée
- [x] Redimensionnement avec slider

**2.4 Formulaire utilisateur (1h)**
- [x] Champs nom/prénom
- [x] Validation temps réel
- [x] Update preview instantané
- [x] Gestion erreurs

### Phase 3 : Logique métier (Jour 2-3 - 4h)

**3.1 Configuration JavaScript (1.5h)**
- [x] Classe `NFCConfigurator`
- [x] Gestion état application
- [x] Communication avec variations WC
- [x] Sauvegarde configuration

**3.2 Intégration WooCommerce (2h)**
- [x] Ajax handlers WordPress
- [x] Ajout panier avec variation
- [x] Métadonnées personnalisation
- [x] Hooks affichage panier/commande

**3.3 Génération preview (0.5h)**
- [x] Canvas HTML5 pour rendu
- [x] Export base64 pour sauvegarde
- [x] Optimisation images

### Phase 4 : Finalisation (Jour 3 - 2h)

**4.1 Tests et optimisation (1h)**
- [x] Tests multi-navigateurs
- [x] Validation mobile/tablet
- [x] Performance et cache
- [x] Gestion erreurs

**4.2 Documentation (1h)**
- [x] Guide d'installation
- [x] Documentation développeur
- [x] Guide utilisateur admin
- [x] Troubleshooting

---

## 📊 Métriques et performance

### 1. Objectifs performance

**Temps de chargement :**
- Page configurateur : < 2s
- Changement couleur : < 100ms
- Upload image : < 3s (2MB)
- Génération preview : < 500ms

**Optimisations :**
- CSS critical path inline
- JavaScript defer/async
- Images WebP + fallback
- Cache browser agressif

### 2. Métriques UX

**Taux de conversion :**
- Abandon configurateur : < 15%
- Configuration → Panier : > 80%
- Panier → Commande : > 60%

**Temps utilisateur :**
- Configuration moyenne : < 2 min
- Upload + ajustement : < 30s
- Décision couleur : < 10s

### 3. Suivi analytics

```javascript
// Events Google Analytics
gtag('event', 'configurator_start', {
  'product_id': productId,
  'source': 'product_page'
});

gtag('event', 'color_selected', {
  'color': selectedColor,
  'variation_id': variationId
});

gtag('event', 'image_uploaded', {
  'file_size': fileSize,
  'file_type': fileType
});

gtag('event', 'configuration_completed', {
  'total_time': configTime,
  'final_variation': variationId
});
```

---

## 🔧 Maintenance et évolutions

### 1. Version 2 prévue (multi-produits)

**Architecture modulaire :**
```php
// ACF Fields pour produits configurables
'nfc_product_config' => [
  'template_type' => 'card|bracelet|keychain',
  'dimensions' => ['width' => 85, 'height' => 55],
  'has_recto' => true,
  'has_verso' => true,
  'available_colors' => ['white', 'black', 'blue'],
  'image_zones' => [...]
];
```

**Templates dynamiques :**
```
/configurator/templates/
├── products/
│   ├── card-85x55.php      # Cartes de visite
│   ├── bracelet-silicone.php # Bracelets
│   └── keychain-round.php   # Porte-clés
└── base-product.php        # Template parent
```

### 2. Fonctionnalités futures

**V2.1 - Couleurs étendues :**
- Ajout couleurs bleu, rouge, vert
- Prix différenciés par couleur
- Stocks séparés par variation

**V2.2 - Upload avancé :**
- Crop/rotation d'images
- Filtres et effets
- Import depuis réseaux sociaux

**V2.3 - Personnalisation texte :**
- Polices personnalisées
- Couleurs de texte
- Positionnement libre

### 3. Monitoring

**Logs application :**
```php
// Monitoring performances
function nfc_log_performance($action, $duration) {
    error_log("NFC_PERF: {$action} completed in {$duration}ms");
}

// Monitoring erreurs
function nfc_log_error($error, $context = []) {
    error_log("NFC_ERROR: {$error} " . json_encode($context));
}
```

**Alertes critiques :**
- Taux d'erreur upload > 5%
- Temps de réponse > 3s
- Abandon configurateur > 20%

---

## ⚠️ Limitations et contraintes

### 1. Limitations techniques V1

**Navigateurs supportés :**
- Chrome 80+ (95% de support)
- Firefox 75+ (90% de support)
- Safari 13+ (85% de support)
- Edge 80+ (95% de support)

**Fonctionnalités limitées :**
- Upload images : 2MB maximum
- Formats : JPG, PNG, SVG uniquement
- Couleurs : Blanc et noir seulement
- Produit : Cartes 85x55mm uniquement

### 2. Contraintes business

**Production :**
- Délai livraison : 7-10 jours ouvrés
- Commande minimum : 1 unité
- Modification après commande : Non possible

**Support client :**
- Validation design avant production
- Fichiers haute résolution requis
- Conformité logo/image

### 3. Évolutivité

**Scalabilité :**
- Architecture modulaire préparée V2
- Base de données optimisée
- Cache et performance

**Maintenance :**
- Mise à jour WordPress/WooCommerce
- Sécurité upload fichiers
- Monitoring performance

---

## 📞 Support et documentation

### 1. Guide développeur

**Hooks disponibles :**
```php
// Personnaliser la validation
add_filter('nfc_validate_config', 'my_validation');

// Modifier le rendu preview
add_filter('nfc_render_preview', 'my_preview');

// Ajouter des métadonnées commande
add_action('nfc_order_created', 'my_order_meta');
```

**Debugging :**
```php
// Activer les logs détaillés
define('NFC_DEBUG', true);

// Forcer rechargement assets
add_query_arg('nfc_reload', '1', $url);
```

### 2. Guide administrateur

**Configuration produit :**
1. Aller dans WooCommerce > Produits
2. Modifier "Carte NFC Personnalisée"
3. Vérifier variations blanc/noir
4. Ajuster prix si nécessaire

**Gestion commandes :**
- Métadonnées personnalisation visibles
- Aperçu cartes en commande
- Export données pour production

### 3. Troubleshooting

**Problèmes courants :**

| Problème | Cause | Solution |
|----------|-------|----------|
| Configurateur ne charge pas | Produit non variable | Vérifier variations |
| Prix ne s'affiche pas | Cache | Vider cache navigateur |
| Upload impossible | Permissions | Vérifier droits WordPress |
| Variation incorrecte | JavaScript erreur | Console navigateur |

**Contacts support :**
- Documentation : `/configurator/docs/`
- Logs : `/wp-content/debug.log`
- Support : support@nfcfrance.com

---

*Documentation version 1.0 - Configurateur NFC France*  
*Estimation totale : 12-16h de développement*  
*Architecture évolutive pour V2 multi-produits*