# Plan d'implémentation - Configurateur NFC

## 🚀 Phase 1: Préparation (30 min)
1. **Backup** des fichiers existants
2. **Test** du configurateur actuel (s'assurer que tout fonctionne)
3. **Analyse** des dependencies JS/CSS

## 🔧 Phase 2: Frontend - Interface Logo Verso (2h)

### Étape 2.1: HTML (30 min)
**Fichier: `configurator-page.php`**
```html
<!-- Ajouter dans la section verso -->
<div class="logo-verso-section mt-3">
    <label class="form-label fw-medium small">Logo verso (optionnel):</label>
    <div class="d-flex gap-2 align-items-center">
        <div class="upload-zone flex-grow-1 bg-light border p-2 text-center text-muted cursor-pointer" 
             id="logoVersoUploadZone">
            <span class="upload-text small">Sélectionner un logo...</span>
            <input type="file" id="logoVersoInput" accept="image/jpeg,image/png,image/svg+xml" class="d-none">
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" 
                onclick="document.getElementById('logoVersoInput').click()">
            Ajouter
        </button>
    </div>
    
    <!-- Contrôles logo verso (masqués par défaut) -->
    <div id="logoVersoControls" class="mt-2 d-none">
        <div class="d-flex align-items-center gap-2">
            <label class="small">Taille:</label>
            <input type="range" id="logoVersoScale" class="form-range flex-grow-1" 
                   min="50" max="150" value="100">
            <span id="logoVersoScaleValue" class="small text-muted">100%</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger mt-1" 
                id="removeLogoVersoBtn">
            Supprimer logo verso
        </button>
    </div>
</div>

<!-- Modifier les inputs nom/prénom -->
<div class="name-inputs mt-3">
    <div class="row">
        <div class="col-6">
            <input type="text" id="firstName" class="form-control form-control-sm" 
                   placeholder="Prénom (optionnel)">
        </div>
        <div class="col-6">  
            <input type="text" id="lastName" class="form-control form-control-sm" 
                   placeholder="Nom (optionnel)">
        </div>
    </div>
</div>
```

### Étape 2.2: CSS Logo Verso (30 min)
**Fichier: `configurator.css`**
```css
/* Zone logo verso dans l'aperçu */
.card-preview.verso .logo-verso-area {
    position: absolute;
    top: 15px;
    left: 15px;
    width: 50px;
    height: 50px;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-verso-placeholder {
    width: 100%;
    height: 100%;
    border: 2px dashed rgba(0,0,0,0.2);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    opacity: 0.4;
    transition: all 0.3s ease;
}

.card-preview.noir .logo-verso-placeholder {
    border-color: rgba(255,255,255,0.3);
}

#logoVersoImage {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 4px;
    transition: transform 0.3s ease;
}

/* Animation logo verso */
.logo-verso-area.has-logo .logo-verso-placeholder {
    display: none;
}

.logo-verso-area.has-logo #logoVersoImage {
    display: block !important;
}
```

### Étape 2.3: JavaScript Extension (1h)
**Nouveau fichier: `configurator-extended.js`**
```javascript
/**
 * Extension du Configurateur NFC pour logo verso + nom optionnel
 */
class NFCConfiguratorExtended extends window.NFCConfigurator {
    
    constructor() {
        super();
        this.initLogoVerso();
        this.overrideValidation();
        console.log('🔧 Configurateur étendu initialisé');
    }
    
    initLogoVerso() {
        // Éléments logo verso
        this.elements.logoVersoInput = document.getElementById('logoVersoInput');
        this.elements.logoVersoUploadZone = document.getElementById('logoVersoUploadZone');
        this.elements.logoVersoControls = document.getElementById('logoVersoControls');
        this.elements.logoVersoImage = document.getElementById('logoVersoImage');
        this.elements.logoVersoScale = document.getElementById('logoVersoScale');
        this.elements.logoVersoScaleValue = document.getElementById('logoVersoScaleValue');
        this.elements.removeLogoVersoBtn = document.getElementById('removeLogoVersoBtn');
        this.elements.logoVersoArea = document.querySelector('.logo-verso-area');
        
        // État logo verso
        this.state.logoVerso = {
            file: null,
            dataUrl: null,
            name: null,
            scale: 100
        };
        
        this.bindLogoVersoEvents();
    }
    
    bindLogoVersoEvents() {
        // Upload logo verso
        if (this.elements.logoVersoInput) {
            this.elements.logoVersoInput.addEventListener('change', (e) => {
                this.handleLogoVersoSelect(e);
            });
        }
        
        // Zone de drop logo verso
        if (this.elements.logoVersoUploadZone) {
            this.elements.logoVersoUploadZone.addEventListener('click', () => {
                this.elements.logoVersoInput.click();
            });
            
            this.elements.logoVersoUploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.currentTarget.classList.add('drag-over');
            });
            
            this.elements.logoVersoUploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                e.currentTarget.classList.remove('drag-over');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    this.processLogoVersoFile(files[0]);
                }
            });
        }
        
        // Slider taille logo verso
        if (this.elements.logoVersoScale) {
            this.elements.logoVersoScale.addEventListener('input', (e) => {
                const scale = parseInt(e.target.value);
                this.updateLogoVersoScale(scale);
            });
        }
        
        // Suppression logo verso
        if (this.elements.removeLogoVersoBtn) {
            this.elements.removeLogoVersoBtn.addEventListener('click', () => {
                this.removeLogoVerso();
            });
        }
    }
    
    handleLogoVersoSelect(e) {
        const file = e.target.files[0];
        if (file) {
            this.processLogoVersoFile(file);
        }
    }
    
    processLogoVersoFile(file) {
        const validation = this.validateImageFile(file);
        if (!validation.valid) {
            this.showError(validation.message);
            return;
        }
        
        console.log(`📷 Traitement logo verso: ${file.name}`);
        
        const reader = new FileReader();
        reader.onload = (e) => {
            this.setLogoVerso(e.target.result, file.name);
        };
        reader.onerror = () => {
            this.showError('Erreur lecture fichier logo verso');
        };
        reader.readAsDataURL(file);
    }
    
    setLogoVerso(dataUrl, fileName) {
        // Mettre à jour l'état
        this.state.logoVerso = {
            file: null,
            dataUrl: dataUrl,
            name: fileName,
            scale: 100
        };
        
        // Mettre à jour l'aperçu
        if (this.elements.logoVersoImage) {
            this.elements.logoVersoImage.src = dataUrl;
            this.elements.logoVersoImage.classList.remove('d-none');
        }
        
        if (this.elements.logoVersoArea) {
            this.elements.logoVersoArea.classList.add('has-logo');
        }
        
        // Afficher les contrôles
        if (this.elements.logoVersoControls) {
            this.elements.logoVersoControls.classList.remove('d-none');
        }
        
        // Mettre à jour le texte d'upload
        const uploadText = this.elements.logoVersoUploadZone?.querySelector('.upload-text');
        if (uploadText) {
            uploadText.textContent = fileName;
        }
        
        console.log('✅ Logo verso défini:', fileName);
    }
    
    updateLogoVersoScale(scale) {
        this.state.logoVerso.scale = scale;
        
        // Mettre à jour l'aperçu
        if (this.elements.logoVersoImage) {
            this.elements.logoVersoImage.style.transform = `scale(${scale / 100})`;
        }
        
        // Mettre à jour l'affichage du pourcentage
        if (this.elements.logoVersoScaleValue) {
            this.elements.logoVersoScaleValue.textContent = scale + '%';
        }
    }
    
    removeLogoVerso() {
        // Reset état
        this.state.logoVerso = {
            file: null,
            dataUrl: null,
            name: null,
            scale: 100
        };
        
        // Reset UI
        if (this.elements.logoVersoImage) {
            this.elements.logoVersoImage.src = '';
            this.elements.logoVersoImage.classList.add('d-none');
            this.elements.logoVersoImage.style.transform = 'scale(1)';
        }
        
        if (this.elements.logoVersoArea) {
            this.elements.logoVersoArea.classList.remove('has-logo');
        }
        
        if (this.elements.logoVersoControls) {
            this.elements.logoVersoControls.classList.add('d-none');
        }
        
        if (this.elements.logoVersoInput) {
            this.elements.logoVersoInput.value = '';
        }
        
        if (this.elements.logoVersoScale) {
            this.elements.logoVersoScale.value = 100;
        }
        
        if (this.elements.logoVersoScaleValue) {
            this.elements.logoVersoScaleValue.textContent = '100%';
        }
        
        // Restaurer texte upload
        const uploadText = this.elements.logoVersoUploadZone?.querySelector('.upload-text');
        if (uploadText) {
            uploadText.textContent = 'Sélectionner un logo...';
        }
        
        console.log('✅ Logo verso supprimé');
    }
    
    // OVERRIDE: Validation sans nom/prénom obligatoires
    overrideValidation() {
        this.validateConfiguration = function() {
            // Configuration toujours valide (nom/prénom optionnels)
            this.state.isValid = true;
            
            if (this.elements.addToCartBtn) {
                this.elements.addToCartBtn.disabled = false;
                this.elements.addToCartBtn.textContent = 'Ajouter au panier';
            }
            
            console.log('✅ Configuration valide (validation override)');
        };
    }
    
    // OVERRIDE: Inclure logo verso dans la configuration
    getConfiguration() {
        const config = super.getConfiguration();
        
        // Ajouter logo verso si présent
        if (this.state.logoVerso.dataUrl) {
            config.logoVerso = {
                name: this.state.logoVerso.name,
                data: this.state.logoVerso.dataUrl,
                scale: this.state.logoVerso.scale
            };
        }
        
        console.log('📦 Configuration étendue:', config);
        return config;
    }
}

// Initialiser automatiquement quand DOM prêt
document.addEventListener('DOMContentLoaded', function() {
    // Remplacer l'instance globale
    if (window.configurator) {
        window.configurator = new NFCConfiguratorExtended();
    }
});
```

## 🔧 Phase 3: Backend - Handlers PHP (1.5h)

### Étape 3.1: Extension Ajax Handlers (1h)
**Fichier: `ajax-handlers.php`**
```php
/**
 * MODIFICATION: Handler ajout panier avec logo verso
 */
function nfc_add_to_cart_handler() {
    error_log('NFC: === DÉBUT AJOUT PANIER ÉTENDU ===');
    error_log('NFC: POST data: ' . print_r($_POST, true));
    
    // ... code de validation existant ...
    
    $nfc_config = $_POST['nfc_config'] ?? [];
    
    // NOUVEAU: Traitement logo verso
    if (isset($nfc_config['logoVerso'])) {
        error_log('NFC: Logo verso détecté: ' . $nfc_config['logoVerso']['name']);
        
        // Validation logo verso (même que recto)
        $logo_verso = $nfc_config['logoVerso'];
        if (!isset($logo_verso['data']) || !isset($logo_verso['name'])) {
            wp_send_json_error('Données logo verso invalides');
            return;
        }
        
        // Validation format base64
        if (!preg_match('/^data:image\/(jpeg|png|svg\+xml);base64,/', $logo_verso['data'])) {
            wp_send_json_error('Format logo verso invalide');
            return;
        }
    }
    
    // MODIFICATION: Validation nom/prénom optionnels
    if (isset($nfc_config['user'])) {
        $user = $nfc_config['user'];
        // Plus de validation obligatoire pour firstName/lastName
        error_log('NFC: Utilisateur (optionnel): ' . 
                  ($user['firstName'] ?? 'N/A') . ' ' . 
                  ($user['lastName'] ?? 'N/A'));
    }
    
    // ... reste du code existant ...
}

/**
 * MODIFICATION: Affichage panier avec logo verso
 */
function nfc_display_cart_item_data($item_data, $cart_item) {
    if (isset($cart_item['nfc_config'])) {
        $config = $cart_item['nfc_config'];
        
        // Couleur (existant)
        $item_data[] = [
            'key' => 'Couleur',
            'value' => ucfirst($config['color'])
        ];
        
        // Image recto (existant)
        if (isset($config['image']) && !empty($config['image']['name'])) {
            $item_data[] = [
                'key' => 'Image recto',
                'value' => 'Logo: ' . $config['image']['name']
            ];
        }
        
        // NOUVEAU: Logo verso
        if (isset($config['logoVerso']) && !empty($config['logoVerso']['name'])) {
            $item_data[] = [
                'key' => 'Logo verso',
                'value' => 'Logo verso: ' . $config['logoVerso']['name']
            ];
        }
        
        // MODIFICATION: Nom optionnel
        if (isset($config['user'])) {
            $user = $config['user'];
            $name_parts = array_filter([
                $user['firstName'] ?? '',
                $user['lastName'] ?? ''
            ]);
            
            if (!empty($name_parts)) {
                $item_data[] = [
                    'key' => 'Nom sur la carte',
                    'value' => implode(' ', $name_parts)
                ];
            }
        }
    }
    
    return $item_data;
}

/**
 * MODIFICATION: Sauvegarde commande avec logo verso
 */
function nfc_save_order_item_meta($item, $cart_item_key, $values, $order) {
    if (isset($values['nfc_config'])) {
        $config = $values['nfc_config'];
        
        // Couleur (existant)
        $item->add_meta_data('_nfc_couleur', ucfirst($config['color']));
        
        // Image recto (existant)
        if (isset($config['image']) && !empty($config['image']['name'])) {
            $item->add_meta_data('_nfc_image_recto', $config['image']['name']);
            $item->add_meta_data('_nfc_image_recto_data', $config['image']);
        }
        
        // NOUVEAU: Logo verso
        if (isset($config['logoVerso']) && !empty($config['logoVerso']['name'])) {
            $item->add_meta_data('_nfc_logo_verso', $config['logoVerso']['name']);
            $item->add_meta_data('_nfc_logo_verso_data', $config['logoVerso']);
            error_log('NFC: Logo verso sauvé en commande: ' . $config['logoVerso']['name']);
        }
        
        // MODIFICATION: Nom optionnel
        if (isset($config['user'])) {
            $user = $config['user'];
            $name_parts = array_filter([
                $user['firstName'] ?? '',
                $user['lastName'] ?? ''
            ]);
            
            if (!empty($name_parts)) {
                $item->add_meta_data('_nfc_nom', implode(' ', $name_parts));
            } else {
                error_log('NFC: Pas de nom défini (optionnel)');
            }
        }
        
        // Sauvegarder configuration complète étendue
        $item->add_meta_data('_nfc_config_complete', json_encode($config));
        
        error_log('NFC: Métadonnées étendues sauvées en commande');
    }
}
```

## 🧪 Phase 4: Tests et ajustements (1h)

### Étape 4.1: Tests fonctionnels (30 min)
- [ ] Test upload logo verso
- [ ] Test aperçu logo verso temps réel
- [ ] Test redimensionnement logo verso
- [ ] Test suppression logo verso
- [ ] Test nom/prénom optionnels
- [ ] Test configuration minimale (couleur seule)
- [ ] Test ajout au panier
- [ ] Test métadonnées dans commandes

### Étape 4.2: Tests edge cases (30 min)
- [ ] Double logo (recto + verso)
- [ ] Uniquement logo verso (sans recto)
- [ ] Configuration sans nom/prénom
- [ ] Fichiers volumineux/invalides
- [ ] Navigation pendant upload

## 📝 Phase 5: Documentation et finalisation (1h)

### Étape 5.1: Documentation (30 min)
- Documenter les nouvelles fonctionnalités
- Mise à jour README configurateur
- Notes techniques pour maintenance

### Étape 5.2: Optimisations (30 min)
- Minification JS/CSS si nécessaire
- Tests performance
- Validation sécurité uploads

## 🎯 Checklist final

### Fonctionnalités
- [ ] ✅ Upload logo verso avec aperçu temps réel
- [ ] ✅ Redimensionnement logo verso
- [ ] ✅ Suppression logo verso
- [ ] ✅ Nom/prénom optionnels (plus de validation obligatoire)
- [ ] ✅ Sauvegarde double image dans panier/commandes
- [ ] ✅ Rétrocompatibilité avec configurations existantes

### Technique
- [ ] ✅ Code propre et commenté
- [ ] ✅ Gestion d'erreurs robuste
- [ ] ✅ Logs pour debug
- [ ] ✅ Validation sécurisée uploads
- [ ] ✅ Tests fonctionnels complets

## ⏱️ Estimation totale: **6 heures**
- Préparation: 0.5h
- Frontend: 2h
- Backend: 1.5h
- Tests: 1h
- Documentation: 1h