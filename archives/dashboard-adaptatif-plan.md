# Plan d'Action - Dashboard Adaptatif

## 🎯 **Objectif**
Dashboard client qui s'adapte automatiquement selon le type de produit acheté (vCard / Avis Google / Multi-produits)

---

## 📋 **Spécifications fonctionnelles**

### **Cas d'usage :**
1. **Client vCard uniquement** → Dashboard complet actuel
2. **Client Avis Google uniquement** → Dashboard simplifié (URL + QR + stats)
3. **Client multi-produits** → Sélecteur + interface adaptée

### **Interface utilisateur :**
```
┌─ HEADER DASHBOARD ──────────────────────────────┐
│ Mon Dashboard NFC    [Sélecteur commande ▼]     │
│                      ├ vCard Pro (Cmd #1023)    │
│                      └ Avis Google (Cmd #1056)  │
└─────────────────────────────────────────────────┘

┌─ CONTENU ADAPTATIF ─────────────────────────────┐
│ Si vCard sélectionnée:                          │
│ ├ Vue d'ensemble                               │
│ ├ Ma vCard                                     │
│ ├ QR Codes                                     │
│ ├ Mes contacts                                 │
│ └ Statistiques                                 │
│                                                │
│ Si Avis Google sélectionnée:                   │
│ ├ Vue d'ensemble (simplifiée)                  │
│ ├ Configuration URL                            │
│ ├ QR Code Avis                                 │
│ └ Statistiques redirection                     │
└─────────────────────────────────────────────────┘
```

---

## 🔧 **Architecture technique**

### **1. Détection automatique des produits**
```php
// includes/dashboard/class-product-detector.php
class NFC_Product_Detector {
    
    public static function get_user_products($user_id) {
        $orders = wc_get_orders([
            'customer' => $user_id,
            'status' => ['completed', 'processing']
        ]);
        
        $products = [];
        foreach($orders as $order) {
            foreach($order->get_items() as $item) {
                $product_type = self::detect_product_type($item->get_product_id());
                
                if ($product_type) {
                    $products[] = [
                        'order_id' => $order->get_id(),
                        'product_id' => $item->get_product_id(),
                        'product_name' => $item->get_name(),
                        'product_type' => $product_type,
                        'order_date' => $order->get_date_created(),
                        'vcard_id' => get_post_meta($order->get_id(), '_vcard_id', true)
                    ];
                }
            }
        }
        
        return $products;
    }
    
    private static function detect_product_type($product_id) {
        // IDs des produits vCard
        $vcard_products = [571, 572, 573]; // PVC, Bois, Metal
        
        // IDs des produits Avis Google  
        $google_review_products = [580, 581]; // Carte Avis, Plaque Avis
        
        if (in_array($product_id, $vcard_products)) {
            return 'vcard';
        }
        
        if (in_array($product_id, $google_review_products)) {
            return 'google_reviews';
        }
        
        return false;
    }
}
```

### **2. Gestionnaire de session produit actif**
```php
// includes/dashboard/class-dashboard-session.php
class NFC_Dashboard_Session {
    
    public static function get_active_product($user_id) {
        // Récupérer le produit actif depuis la session
        $active_order_id = isset($_GET['order_id']) ? 
            intval($_GET['order_id']) : 
            get_user_meta($user_id, '_nfc_active_order', true);
            
        $user_products = NFC_Product_Detector::get_user_products($user_id);
        
        // Si pas d'ordre spécifique, prendre le plus récent
        if (!$active_order_id || !self::order_exists_in_products($active_order_id, $user_products)) {
            $active_order_id = $user_products[0]['order_id'] ?? null;
        }
        
        // Sauvegarder le choix
        update_user_meta($user_id, '_nfc_active_order', $active_order_id);
        
        return self::find_product_by_order($active_order_id, $user_products);
    }
    
    public static function switch_active_product($user_id, $order_id) {
        update_user_meta($user_id, '_nfc_active_order', $order_id);
        wp_redirect(add_query_arg('order_id', $order_id));
        exit;
    }
}
```

### **3. Templates conditionnels**
```php
// templates/dashboard/adaptive-dashboard.php
$current_user = wp_get_current_user();
$active_product = NFC_Dashboard_Session::get_active_product($current_user->ID);
$user_products = NFC_Product_Detector::get_user_products($current_user->ID);

if (!$active_product) {
    // Rediriger vers création de produit ou erreur
    wp_redirect(home_url('/boutique-nfc'));
    exit;
}
?>

<!-- Header avec sélecteur -->
<div class="dashboard-header">
    <?php if (count($user_products) > 1): ?>
    <div class="product-selector">
        <label>Produit actif :</label>
        <select onchange="switchProduct(this.value)">
            <?php foreach($user_products as $product): ?>
            <option value="<?= $product['order_id'] ?>" 
                    <?= $product['order_id'] == $active_product['order_id'] ? 'selected' : '' ?>>
                <?= $product['product_name'] ?> (Commande #<?= $product['order_id'] ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
</div>

<!-- Contenu adaptatif -->
<?php if ($active_product['product_type'] === 'vcard'): ?>
    <?php include 'dashboard-vcard.php'; ?>
<?php elseif ($active_product['product_type'] === 'google_reviews'): ?>
    <?php include 'dashboard-google-reviews.php'; ?>
<?php endif; ?>
```

---

## 📱 **Dashboard Avis Google spécialisé**

### **Interface simplifiée :**
```php
// templates/dashboard/dashboard-google-reviews.php
<div class="google-reviews-dashboard">
    
    <!-- Vue d'ensemble -->
    <div class="overview-section mb-4">
        <div class="row">
            <div class="col-md-8">
                <h2>Configuration Avis Google</h2>
                <p class="text-muted">Configurez votre redirection vers Google Avis</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="stats-quick">
                    <div class="stat-item">
                        <h3 id="total-scans">-</h3>
                        <small>Scans totaux</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Configuration URL -->
    <div class="row">
        <div class="col-lg-8">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>URL Google Avis</h3>
                </div>
                <div class="card-body">
                    <form id="google-reviews-form">
                        <div class="mb-3">
                            <label class="form-label">URL de votre page d'avis Google</label>
                            <input type="url" 
                                   class="form-control" 
                                   id="google-reviews-url"
                                   value="<?= get_post_meta($active_product['vcard_id'], 'custom_url', true) ?>"
                                   placeholder="https://g.page/r/[VOTRE-LIEU]/review?rc">
                            <small class="form-text text-muted">
                                Trouvez cette URL dans votre Google Business Profile
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="track-redirections" checked>
                                <label class="form-check-label" for="track-redirections">
                                    Tracker les redirections pour les statistiques
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                        
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="testRedirection()">
                            <i class="fas fa-external-link me-2"></i>Tester la redirection
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- QR Code -->
            <div class="dashboard-card mb-4">
                <div class="card-header">
                    <h3>QR Code Avis</h3>
                </div>
                <div class="card-body text-center">
                    <div id="qr-code-container" class="mb-3">
                        <!-- QR Code généré -->
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="downloadQR('png')">
                            Télécharger PNG
                        </button>
                        <button class="btn btn-outline-success" onclick="downloadQR('pdf')">
                            Télécharger PDF
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Stats rapides -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Statistiques</h3>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item mb-3">
                            <h4 class="text-primary" id="total-redirections">-</h4>
                            <small>Redirections vers Google</small>
                        </div>
                        <div class="stat-item mb-3">
                            <h4 class="text-success" id="conversion-rate">-</h4>
                            <small>Taux de conversion</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
```

---

## ⚙️ **Intégration avec le système existant**

### **1. Modification class-dashboard-manager.php**
```php
// Remplacer la logique actuelle par :
public function render_user_dashboard() {
    $current_user = wp_get_current_user();
    $active_product = NFC_Dashboard_Session::get_active_product($current_user->ID);
    
    if (!$active_product) {
        $this->render_no_product_page();
        return;
    }
    
    // Charger le template adaptatif
    include $this->plugin_path . 'templates/dashboard/adaptive-dashboard.php';
}
```

### **2. APIs étendues pour Avis Google**
```php
// api/google-reviews/update.php
class NFC_Google_Reviews_API {
    
    public static function update_google_reviews_url($vcard_id, $url) {
        // Utiliser le champ custom_url existant
        update_post_meta($vcard_id, 'custom_url', sanitize_url($url));
        update_post_meta($vcard_id, 'custom_url_enabled', true);
        
        return [
            'success' => true,
            'message' => 'URL Google Avis mise à jour',
            'public_url' => get_permalink($vcard_id)
        ];
    }
    
    public static function get_google_reviews_stats($vcard_id) {
        // Statistiques de redirection depuis les logs
        return [
            'total_scans' => get_post_meta($vcard_id, '_total_views', true) ?: 0,
            'total_redirections' => get_post_meta($vcard_id, '_google_redirections', true) ?: 0,
            'conversion_rate' => 0 // Calculé
        ];
    }
}
```

---

## 📊 **Planning développement (Post-démo)**

### **Semaine 1 (2-6 septembre) - 4 jours**
- [ ] **Architecture produit detector** (1 jour)
- [ ] **Système de session adaptatif** (1 jour)  
- [ ] **Templates dashboard Google Reviews** (1 jour)
- [ ] **APIs et intégration** (1 jour)

### **Tests et validation (1 jour)**
- [ ] Cas multi-produits complets
- [ ] Performance avec plusieurs commandes  
- [ ] UX fluide de changement de produit

---

## ✅ **Critères de validation**

### **Fonctionnel :**
- [ ] Client avec vCard → Dashboard complet
- [ ] Client avec Avis Google → Dashboard simplifié  
- [ ] Client multi-produits → Sélecteur fonctionnel
- [ ] Changement de produit sans rechargement de page

### **Technique :**
- [ ] Pas de régression sur dashboard vCard existant
- [ ] Session produit persistante
- [ ] APIs compatibles avec système actuel
- [ ] Performance optimisée (< 3s chargement)

---

## 🔗 **Dépendances**

### **Pré-requis :**
- Dashboard vCard actuel stable
- IDs produits Avis Google définis
- Tests avec vraies commandes

### **Intégrations futures :**
- Workflow envoi fichiers (détection type produit)
- Configurateurs adaptatifs par matériau
- Analytics consolidées multi-produits

---

*Plan dashboard adaptatif v1.0*  
*Architecture évolutive et modulaire*