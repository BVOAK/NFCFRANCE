# Configurateur NFC - Code Snippets

## 🎨 CSS Variables & Couleurs

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
  
  /* Dimensions cartes (85x55mm → échelle 4) */
  --card-width: 340px;
  --card-height: 220px;
  --card-radius: 16px;
  --logo-size: 80px;
}
```

## 🏗️ Structure HTML configurateur

```html
<div class="nfc-configurator">
  <header class="configurator-header">
    <div class="header-content">
      <h1>Configurateur NFC France</h1>
      <div class="price-display">
        <span class="currency">€</span>
        <span class="amount">30,00</span>
      </div>
    </div>
  </header>
  
  <main class="configurator-main">
    <section class="preview-section">
      <div class="cards-container">
        <div class="card-preview recto" data-side="recto"></div>
        <div class="card-preview verso" data-side="verso"></div>
      </div>
    </section>
    
    <aside class="config-panel">
      <div class="config-section color-selection">
        <h3>🎨 Couleur de la carte</h3>
        <div class="color-options">
          <label class="color-option">
            <input type="radio" name="card-color" value="blanc" checked>
            <span class="color-preview white"></span>
            <span class="color-label">Blanc</span>
          </label>
          <label class="color-option">
            <input type="radio" name="card-color" value="noir">
            <span class="color-preview black"></span>
            <span class="color-label">Noir</span>
          </label>
        </div>
      </div>
      
      <div class="config-section image-upload">
        <h3>📷 Image ou logo</h3>
        <div class="upload-zone" id="imageUploadZone">
          <p>Glissez votre image ici ou cliquez pour parcourir</p>
          <input type="file" id="imageInput" accept="image/*">
        </div>
        <div class="image-controls" style="display: none;">
          <label>Taille: <input type="range" id="imageScale" min="50" max="150" value="100"></label>
        </div>
      </div>
      
      <div class="config-section user-info">
        <h3>👤 Informations</h3>
        <div class="form-row">
          <input type="text" id="firstName" placeholder="Prénom">
          <input type="text" id="lastName" placeholder="Nom">
        </div>
      </div>
      
      <button class="add-to-cart-btn" id="addToCartBtn">
        Ajouter au panier - <span class="price">30,00€</span>
      </button>
    </aside>
  </main>
</div>
```

## 🎯 JavaScript - Classe principale

```javascript
class NFCConfigurator {
  constructor() {
    this.productId = new URLSearchParams(window.location.search).get('product_id');
    this.variations = {};
    this.selectedVariation = null;
    this.config = {
      color: 'blanc',
      user: { firstName: '', lastName: '' },
      image: null
    };
    
    this.init();
  }
  
  async init() {
    try {
      await this.loadVariations();
      this.bindEvents();
      this.setInitialColor('blanc');
    } catch (error) {
      console.error('Erreur initialisation configurateur:', error);
    }
  }
  
  async loadVariations() {
    const response = await fetch(`/wp-json/nfc/v1/product/${this.productId}/variations`);
    this.variations = await response.json();
  }
  
  bindEvents() {
    // Sélection couleur
    document.querySelectorAll('input[name="card-color"]').forEach(input => {
      input.addEventListener('change', (e) => this.changeColor(e.target.value));
    });
    
    // Upload image
    const uploadZone = document.getElementById('imageUploadZone');
    const imageInput = document.getElementById('imageInput');
    
    uploadZone.addEventListener('click', () => imageInput.click());
    uploadZone.addEventListener('dragover', this.handleDragOver.bind(this));
    uploadZone.addEventListener('drop', this.handleDrop.bind(this));
    imageInput.addEventListener('change', this.handleImageSelect.bind(this));
    
    // Formulaire utilisateur
    document.getElementById('firstName').addEventListener('input', this.updateUserInfo.bind(this));
    document.getElementById('lastName').addEventListener('input', this.updateUserInfo.bind(this));
    
    // Ajout panier
    document.getElementById('addToCartBtn').addEventListener('click', this.addToCart.bind(this));
  }
  
  changeColor(color) {
    this.config.color = color;
    this.selectedVariation = this.variations[color];
    this.updateVisuals();
    this.updatePrice();
  }
  
  updateVisuals() {
    const recto = document.querySelector('.card-preview.recto');
    const verso = document.querySelector('.card-preview.verso');
    
    // Changement classe CSS pour couleur
    recto.className = `card-preview recto ${this.config.color}`;
    verso.className = `card-preview verso ${this.config.color}`;
    
    this.renderCardContent();
  }
  
  updatePrice() {
    if (this.selectedVariation) {
      const priceElements = document.querySelectorAll('.price');
      priceElements.forEach(el => {
        el.textContent = `${this.selectedVariation.price}€`;
      });
    }
  }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
  window.nfcConfigurator = new NFCConfigurator();
});
```

## 🔧 WooCommerce - Récupération variations

```php
// Endpoint API pour récupérer les variations
function nfc_get_product_variations($request) {
    $product_id = $request['product_id'];
    $product = wc_get_product($product_id);
    
    if (!$product || !$product->is_type('variable')) {
        return new WP_Error('invalid_product', 'Produit non trouvé ou non variable');
    }
    
    $variations_data = [];
    $variations = $product->get_available_variations();
    
    foreach ($variations as $variation) {
        $color_slug = '';
        foreach ($variation['attributes'] as $attr_name => $attr_value) {
            if (strpos($attr_name, 'couleur') !== false) {
                $color_slug = $attr_value;
                break;
            }
        }
        
        if ($color_slug) {
            $variations_data[$color_slug] = [
                'id' => $variation['variation_id'],
                'price' => $variation['display_price'],
                'sku' => $variation['sku'],
                'stock_status' => $variation['is_in_stock'] ? 'instock' : 'outofstock'
            ];
        }
    }
    
    return $variations_data;
}

// Enregistrement endpoint
add_action('rest_api_init', function() {
    register_rest_route('nfc/v1', '/product/(?P<product_id>\d+)/variations', [
        'methods' => 'GET',
        'callback' => 'nfc_get_product_variations',
        'permission_callback' => '__return_true'
    ]);
});
```

## 🛒 Ajout panier avec métadonnées

```php
// Hook pour ajouter les données de configuration au panier
add_filter('woocommerce_add_cart_item_data', 'nfc_add_cart_item_data', 10, 3);
function nfc_add_cart_item_data($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['nfc_config'])) {
        $config = json_decode(stripslashes($_POST['nfc_config']), true);
        
        // Validation des données
        if (nfc_validate_config($config)) {
            $cart_item_data['nfc_config'] = $config;
            $cart_item_data['nfc_unique_key'] = uniqid(); // Force l'unicité
        }
    }
    
    return $cart_item_data;
}

// Validation de la configuration
function nfc_validate_config($config) {
    // Vérifier couleur
    if (!in_array($config['color'], ['blanc', 'noir'])) {
        return false;
    }
    
    // Vérifier informations utilisateur
    if (empty($config['user']['firstName']) || empty($config['user']['lastName'])) {
        return false;
    }
    
    // Vérifier image si présente
    if (isset($config['image']['data']) && !empty($config['image']['data'])) {
        // Validation base64
        if (!preg_match('/^data:image\/(jpeg|png|svg\+xml);base64,/', $config['image']['data'])) {
            return false;
        }
    }
    
    return true;
}
```

## 📱 CSS Responsive

```css
/* Desktop large: 1400px+ */
@media (min-width: 1400px) {
  .configurator-main {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
  }
}

/* Desktop: 1200px-1399px */
@media (min-width: 1200px) and (max-width: 1399px) {
  .configurator-main {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 30px;
  }
}

/* Tablet: 768px-1199px */
@media (min-width: 768px) and (max-width: 1199px) {
  .configurator-main {
    display: flex;
    flex-direction: column;
  }
  
  .cards-container {
    flex-direction: row;
    justify-content: center;
    gap: 20px;
  }
  
  .card-preview {
    width: 300px;
    height: 190px;
  }
}

/* Mobile: <768px */
@media (max-width: 767px) {
  .cards-container {
    flex-direction: column;
    align-items: center;
    gap: 15px;
  }
  
  .card-preview {
    width: 280px;
    height: 180px;
  }
  
  .config-panel {
    margin-top: 20px;
  }
}
```

---

*Snippets mis à jour au fur et à mesure du développement*