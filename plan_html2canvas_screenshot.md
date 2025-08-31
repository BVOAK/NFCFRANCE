# Plan d'action : Screenshot HTML2Canvas pour NFC France

## 🎯 **OBJECTIF**
Remplacer le système actuel de génération de screenshots par une vraie capture HTML2Canvas de l'aperçu du configurateur, avec affichage dans order-received et backend admin.

---

## 📋 **ÉTAPE 1 : Nettoyage complet (2h)**

### **Tâche 1.1 : Supprimer les fonctions screenshot obsolètes**
**Fichiers à nettoyer :**

1. **`screenshot-generator.js`**
   - Supprimer entièrement le fichier
   - Retirer l'inclusion dans `page-configurateur.php`

2. **`ajax-handlers.php`**
   - Supprimer `nfc_process_screenshot()`
   - Supprimer `nfc_save_base64_image()`
   - Simplifier `nfc_validate_configuration()` (supprimer validation screenshot)

3. **`nfc-file-handler.php`**
   - Supprimer `generate_screenshot_from_config()`
   - Supprimer `display_screenshot_file()`
   - Supprimer `serve_screenshot_file()`
   - Garder uniquement les fonctions logo (recto/verso)

4. **`functions.php`**
   - Supprimer tout le code de debug panier
   - Supprimer les hooks de test WooCommerce blocs

### **Tâche 1.2 : Nettoyer les métadonnées obsolètes**
**Actions :**
- Dans `wc-integration.php`, modifier `save_screenshot_metadata()` pour utiliser le nouveau format
- Supprimer les références à `_nfc_screenshot_info`
- Garder uniquement `_nfc_screenshot_data` pour les nouvelles données

---

## 🚀 **ÉTAPE 2 : Implémentation HTML2Canvas (3h)**

### **Tâche 2.1 : Nouveau module screenshot**
**Fichier : `configurator/assets/js/html2canvas-screenshot.js`**

```javascript
/**
 * Module de capture HTML2Canvas pour configurateur NFC
 */
if (typeof window.NFCScreenshotCapture === 'undefined') {
    window.NFCScreenshotCapture = class NFCScreenshotCapture {
        constructor(configurator) {
            this.configurator = configurator;
            this.previewSelector = '.preview-column';
        }

        async init() {
            // Charger html2canvas si nécessaire
            if (typeof html2canvas === 'undefined') {
                await this.loadHtml2Canvas();
            }
        }

        async loadHtml2Canvas() {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        async generateScreenshot() {
            const previewElement = document.querySelector(this.previewSelector);
            if (!previewElement) {
                throw new Error('Zone d\'aperçu configurateur non trouvée');
            }

            const canvas = await html2canvas(previewElement, {
                backgroundColor: '#ffffff',
                scale: 2, // Haute résolution
                useCORS: true,
                allowTaint: false,
                logging: false,
                width: previewElement.scrollWidth,
                height: previewElement.scrollHeight
            });

            return canvas.toDataURL('image/png', 1.0);
        }

        async generateThumbnail(maxWidth = 300) {
            const fullScreenshot = await this.generateScreenshot();
            
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    const scale = maxWidth / img.width;
                    canvas.width = maxWidth;
                    canvas.height = img.height * scale;
                    
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    resolve(canvas.toDataURL('image/png', 0.8));
                };
                img.src = fullScreenshot;
            });
        }
    };
}
```

### **Tâche 2.2 : Modifier le configurateur principal**
**Fichier : `configurator.js`**

```javascript
// Dans la classe NFCConfigurator, remplacer l'initialisation screenshot
async init() {
    // ... code existant ...
    
    // NOUVEAU : Screenshot HTML2Canvas
    this.screenshotCapture = new window.NFCScreenshotCapture(this);
    await this.screenshotCapture.init();
}

// Modifier addToCart()
async addToCart() {
    // ... validation existante ...
    
    try {
        // Générer le vrai screenshot
        const screenshot = await this.screenshotCapture.generateScreenshot();
        const thumbnail = await this.screenshotCapture.generateThumbnail(300);
        
        const configData = {
            // ... données existantes ...
            screenshot: {
                full: screenshot,
                thumbnail: thumbnail,
                generated_at: new Date().toISOString()
            }
        };
        
        // ... reste du code ...
    } catch (error) {
        console.error('Erreur capture:', error);
        // Continuer sans screenshot
    }
}
```

### **Tâche 2.3 : Mise à jour de la page configurateur**
**Fichier : `page-configurateur.php`**

```php
<!-- Ajouter html2canvas et nouveau module -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/configurator/assets/js/html2canvas-screenshot.js?v=1.0"></script>
```

---

## 📄 **ÉTAPE 3 : Affichage order-received (2h)**

### **Tâche 3.1 : Template order-received**
**Fichier : `woocommerce/order/order-details-item.php` (à créer)**

```php
<?php
// Template personnalisé pour afficher les items NFC
if (!defined('ABSPATH')) exit;

// Vérifier si c'est un produit NFC configuré
$nfc_config = $item->get_meta('_nfc_config_complete');
if (!$nfc_config) return;

$config = json_decode($nfc_config, true);
$screenshot_data = $item->get_meta('_nfc_screenshot_data');

if ($screenshot_data) {
    $screenshots = json_decode($screenshot_data, true);
    ?>
    <div class="nfc-order-config" style="margin: 15px 0; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h4>Configuration personnalisée</h4>
        
        <?php if (isset($screenshots['thumbnail'])): ?>
        <div class="nfc-screenshot-display" style="text-align: center; margin: 15px 0;">
            <img src="<?php echo esc_attr($screenshots['thumbnail']); ?>" 
                 alt="Aperçu configuration NFC" 
                 style="max-width: 300px; border: 2px solid #0040C1; border-radius: 8px; cursor: pointer;"
                 onclick="nfcShowFullScreenshot(this)" />
            <p><small>Cliquez pour agrandir</small></p>
        </div>
        <?php endif; ?>
        
        <div class="nfc-config-details">
            <p><strong>Couleur :</strong> <?php echo esc_html(ucfirst($config['color'] ?? 'Non défini')); ?></p>
            <?php if (isset($config['user'])): ?>
            <p><strong>Nom :</strong> <?php echo esc_html(($config['user']['firstName'] ?? '') . ' ' . ($config['user']['lastName'] ?? '')); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal pour affichage plein écran -->
    <div id="nfc-screenshot-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:10000;">
        <div style="display:flex; align-items:center; justify-content:center; height:100%;">
            <img id="nfc-screenshot-full" src="" style="max-width:90%; max-height:90%;" />
            <button onclick="document.getElementById('nfc-screenshot-modal').style.display='none'" 
                    style="position:absolute; top:20px; right:20px; color:white; background:none; border:none; font-size:24px;">×</button>
        </div>
    </div>
    
    <script>
    function nfcShowFullScreenshot(thumbnail) {
        const modal = document.getElementById('nfc-screenshot-modal');
        const fullImg = document.getElementById('nfc-screenshot-full');
        
        // Utiliser l'image full si disponible, sinon thumbnail
        <?php if (isset($screenshots['full'])): ?>
        fullImg.src = '<?php echo esc_js($screenshots['full']); ?>';
        <?php else: ?>
        fullImg.src = thumbnail.src;
        <?php endif; ?>
        
        modal.style.display = 'block';
    }
    </script>
    <?php
}
?>
```

### **Tâche 3.2 : Hook pour order-received**
**Fichier : `nfc-customer-integration.php`**

```php
// Ajouter hook pour afficher config NFC sur order-received
add_action('woocommerce_order_details_after_order_table', 'nfc_display_order_configurations');
function nfc_display_order_configurations($order) {
    foreach ($order->get_items() as $item_id => $item) {
        $nfc_config = $item->get_meta('_nfc_config_complete');
        if ($nfc_config) {
            include get_template_directory() . '/woocommerce/order/order-details-item.php';
        }
    }
}
```

---

## ⚙️ **ÉTAPE 4 : Interface admin backend (2h)**

### **Tâche 4.1 : Affichage admin commandes**
**Fichier : `wc-integration.php`**

```php
// Modifier display_enhanced_admin_order_meta()
public function display_enhanced_admin_order_meta($order) {
    $has_nfc_items = false;
    
    foreach ($order->get_items() as $item_id => $item) {
        $nfc_config = $item->get_meta('_nfc_config_complete');
        if ($nfc_config) {
            if (!$has_nfc_items) {
                echo '<h3>Configurations NFC</h3>';
                $has_nfc_items = true;
            }
            
            $this->display_admin_nfc_item($item, $item_id, $order->get_id());
        }
    }
}

private function display_admin_nfc_item($item, $item_id, $order_id) {
    $config = json_decode($item->get_meta('_nfc_config_complete'), true);
    $screenshot_data = $item->get_meta('_nfc_screenshot_data');
    
    echo '<div class="nfc-admin-item" style="margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">';
    echo '<h4>' . $item->get_name() . '</h4>';
    
    if ($screenshot_data) {
        $screenshots = json_decode($screenshot_data, true);
        
        if (isset($screenshots['thumbnail'])) {
            echo '<div class="nfc-admin-screenshot">';
            echo '<p><strong>Aperçu configuration :</strong></p>';
            echo '<img src="' . esc_attr($screenshots['thumbnail']) . '" style="max-width: 200px; border: 1px solid #ccc;" />';
            echo '<br><button type="button" class="button" onclick="nfcViewFullScreenshot(\'' . esc_js($screenshots['full'] ?? $screenshots['thumbnail']) . '\')">Voir en grand</button>';
            echo '</div>';
        }
    }
    
    // Détails configuration
    echo '<div class="nfc-config-details">';
    echo '<p><strong>Couleur :</strong> ' . esc_html(ucfirst($config['color'] ?? 'Non défini')) . '</p>';
    if (isset($config['user'])) {
        echo '<p><strong>Nom :</strong> ' . esc_html(($config['user']['firstName'] ?? '') . ' ' . ($config['user']['lastName'] ?? '')) . '</p>';
    }
    echo '</div>';
    
    echo '</div>';
}
```

### **Tâche 4.2 : JavaScript admin**
**Fichier : `admin-nfc-screenshot.js`**

```javascript
// Script pour l'admin WordPress
function nfcViewFullScreenshot(imageData) {
    // Créer modal admin
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.8); z-index: 999999;
        display: flex; align-items: center; justify-content: center;
    `;
    
    const img = document.createElement('img');
    img.src = imageData;
    img.style.cssText = 'max-width: 90%; max-height: 90%; border: 2px solid #fff;';
    
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '×';
    closeBtn.style.cssText = `
        position: absolute; top: 20px; right: 20px;
        color: white; background: none; border: none;
        font-size: 30px; cursor: pointer;
    `;
    closeBtn.onclick = () => document.body.removeChild(modal);
    
    modal.appendChild(img);
    modal.appendChild(closeBtn);
    document.body.appendChild(modal);
}
```

---

## 🧹 **ÉTAPE 5 : Tests et finalisation (1h)**

### **Tests à effectuer :**

1. **Test configurateur :**
   - Vérifier que html2canvas se charge
   - Tester génération screenshot de .preview-column
   - Valider ajout au panier avec nouveau screenshot

2. **Test commande :**
   - Valider commande
   - Vérifier données `_nfc_screenshot_data` en BDD
   - Tester requête SQL

3. **Test order-received :**
   - Vérifier affichage thumbnail
   - Tester modal plein écran
   - Valider responsive

4. **Test admin :**
   - Vérifier affichage dans commandes admin
   - Tester bouton "Voir en grand"
   - Valider toutes les métadonnées

### **Nettoyage final :**
- Supprimer tous les fichiers de debug temporaires
- Retirer les logs de développement
- Optimiser les performances

---

## 📊 **RÉCAPITULATIF**

| Étape | Durée | Livrables |
|-------|-------|-----------|
| 1. Nettoyage | 2h | Code propre, fonctions obsolètes supprimées |
| 2. HTML2Canvas | 3h | Nouveau système de capture |
| 3. Order-received | 2h | Affichage client avec modal |
| 4. Admin backend | 2h | Interface admin complète |
| 5. Tests | 1h | Système validé et optimisé |
| **TOTAL** | **10h** | **Screenshot HTML2Canvas fonctionnel** |

## 🎯 **RÉSULTAT ATTENDU**

- Screenshot fidèle à l'aperçu du configurateur
- Affichage propre sur order-received avec zoom
- Interface admin complète pour visualisation
- Système léger et performant
- Code maintenant et documenté