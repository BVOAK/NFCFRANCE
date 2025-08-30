# Prompts Frontend - Configurateur NFC Étendu

## 🎯 PROMPT 1: Modification Template HTML
**Fichier à modifier :** `/wp-content/themes/uicore-pro/page-configurateur.php`

### Tâche 1.1: Aperçu Carte Verso
**Localisation :** Section carte verso dans la colonne preview

```html
<!-- TROUVER cette section dans le template -->
<div class="card-preview verso blanc shadow-lg" data-side="verso">
    <!-- Contenu actuel à modifier -->
</div>
```

**AJOUTER :**
1. **Zone logo verso** en haut à gauche de la carte
2. **Modifier l'affichage nom/prénom** pour gérer l'optionnel

**Code à intégrer :**
```html
<!-- Carte Verso MODIFIÉE -->
<div class="card-preview verso blanc shadow-lg" data-side="verso">
    <div class="card-content w-100 h-100 d-flex align-items-center justify-content-between p-5">
        
        <!-- NOUVEAU : Zone logo verso (en haut à gauche) -->
        <div class="logo-verso-area position-absolute" id="logoVersoArea" 
             style="top: 15px; left: 15px; width: 50px; height: 50px; z-index: 10;">
            <div class="logo-verso-placeholder text-center d-flex align-items-center justify-content-center w-100 h-100" 
                 id="logoVersoPlaceholder" 
                 style="border: 2px dashed rgba(0,0,0,0.2); border-radius: 6px; opacity: 0.4;">
                <span style="font-size: 20px;">🏢</span>
            </div>
            <img id="logoVersoImage" class="d-none w-100 h-100" 
                 style="object-fit: contain; border-radius: 4px;" 
                 alt="Logo verso">
        </div>

        <!-- Section nom utilisateur (reste de la logique existante) -->
        <div class="user-section flex-grow-1">
            <div class="user-names d-flex flex-column gap-1">
                <div class="user-firstname h4 fw-bold lh-1" id="versoUserFirstName">
                    Prénom
                </div>
                <div class="user-lastname h5 fw-medium lh-1" id="versoUserLastName">
                    Nom
                </div>
            </div>
        </div>

        <!-- Section QR (inchangée) -->
        <!-- ... contenu QR existant ... -->
    </div>
</div>
```

### Tâche 1.2: Section Configuration Logo Verso
**Localisation :** Colonne droite, après la section "Recto" et avant "Verso"

**AJOUTER cette nouvelle section :**
```html
<!-- NOUVELLE SECTION : Logo Verso (à insérer après section Recto) -->
<div class="config-section logo-verso-section mb-4">
    <h3 class="h5 fw-semibold mb-3">
        Logo verso (optionnel) :
    </h3>
    
    <div class="config-step mb-3">
        <div class="d-flex gap-3 align-items-center">
            <div class="upload-zone flex-grow-1 bg-light border p-3 text-center text-muted cursor-pointer" 
                 id="logoVersoUploadZone"
                 style="min-height: 60px; border-style: dashed !important; border-width: 2px !important;">
                <span class="upload-text small">Sélectionner un logo...</span>
                <input type="file" id="logoVersoInput" accept="image/jpeg,image/png,image/svg+xml" class="d-none">
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" 
                    onclick="document.getElementById('logoVersoInput').click()">
                Ajouter logo
            </button>
        </div>
    </div>

    <!-- Contrôles logo verso (masqués par défaut) -->
    <div id="logoVersoControls" class="mt-3 d-none">
        <label class="form-label fw-medium small">Ajuster le logo verso</label>
        
        <!-- Taille logo verso -->
        <div class="row align-items-center mb-3">
            <div class="col-3">
                <label for="logoVersoScale" class="form-label small fw-medium mb-0">Taille :</label>
            </div>
            <div class="col-7">
                <input type="range" id="logoVersoScale" min="50" max="150" value="100" class="form-range">
            </div>
            <div class="col-2 text-end">
                <span id="logoVersoScaleValue" class="small fw-semibold text-primary">100%</span>
            </div>
        </div>

        <!-- Bouton supprimer logo verso -->
        <div class="mt-3">
            <button type="button" class="btn btn-outline-danger btn-sm" id="removeLogoVersoBtn">
                <i class="fas fa-trash-alt me-1"></i>
                Supprimer logo verso
            </button>
        </div>
    </div>
</div>
```

### Tâche 1.3: Modification Section Verso (nom optionnel)
**Localisation :** Section verso existante avec les inputs nom/prénom

**MODIFIER les placeholders et retirer "required" :**
```html
<!-- Section Verso MODIFIÉE -->
<div class="name-inputs">
    <div class="row">
        <div class="col-6">
            <!-- MODIFIÉ : Plus de "required" + placeholder optionnel -->
            <input type="text" id="firstName" class="form-control form-control-sm" 
                   placeholder="Prénom (optionnel)">
        </div>
        <div class="col-6">
            <!-- MODIFIÉ : Plus de "required" + placeholder optionnel -->
            <input type="text" id="lastName" class="form-control form-control-sm" 
                   placeholder="Nom (optionnel)">
        </div>
    </div>
</div>
```

### Tâche 1.4: Modification Bouton Panier
**Localisation :** Bouton "Ajouter au panier"

**RETIRER l'attribut `disabled` :**
```html
<!-- Bouton MODIFIÉ (plus disabled par défaut) -->
<button type="button" class="btn btn-primary w-100 fw-semibold py-3" id="addToCartBtn">
    <i class="fas fa-shopping-cart me-2"></i>
    Ajouter au panier
</button>
```

---

## 🎨 PROMPT 2: Ajout CSS Logo Verso
**Fichier à modifier :** `/wp-content/themes/uicore-pro/configurator/assets/css/configurator.css`

### Tâche 2.1: CSS Logo Verso
**AJOUTER à la fin du fichier CSS :**

```css
/* ==========================================================================
   LOGO VERSO - Nouvelles fonctionnalités
   ========================================================================== */

/* Zone logo verso dans l'aperçu carte */
.logo-verso-area {
    transition: all 0.3s ease;
}

.logo-verso-placeholder {
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.1);
}

/* Couleur placeholder selon carte */
.card-preview.noir .logo-verso-placeholder {
    border-color: rgba(255,255,255,0.3) !important;
    background: rgba(0, 0, 0, 0.1);
}

/* Image logo verso */
#logoVersoImage {
    transition: transform 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* États logo verso */
.logo-verso-area.has-logo .logo-verso-placeholder {
    display: none !important;
}

.logo-verso-area.has-logo #logoVersoImage {
    display: block !important;
    opacity: 1;
}

/* Section configuration logo verso */
.config-section.logo-verso-section {
    background: rgba(248, 249, 250, 0.8);
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.3s ease;
}

.config-section.logo-verso-section:hover {
    background: rgba(248, 249, 250, 1);
    border-color: rgba(0, 123, 255, 0.3);
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
}

/* Zone d'upload logo verso */
#logoVersoUploadZone {
    transition: all 0.3s ease;
    cursor: pointer;
}

#logoVersoUploadZone:hover {
    border-color: var(--bs-primary) !important;
    background-color: rgba(13, 110, 253, 0.05) !important;
    transform: translateY(-1px);
}

#logoVersoUploadZone.drag-over {
    border-color: var(--bs-success) !important;
    background-color: rgba(25, 135, 84, 0.1) !important;
    transform: scale(1.02);
}

/* Contrôles logo verso */
#logoVersoControls {
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 6px;
    padding: 0.75rem;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Animation apparition logo verso */
.logo-verso-area.has-logo #logoVersoImage {
    animation: logoVersoAppear 0.4s ease-out;
}

@keyframes logoVersoAppear {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}

/* Responsive logo verso */
@media (max-width: 991.98px) {
    .logo-verso-area {
        width: 40px !important;
        height: 40px !important;
        top: 12px !important;
        left: 12px !important;
    }
}

@media (max-width: 767.98px) {
    .logo-verso-area {
        width: 35px !important;
        height: 35px !important;
        top: 10px !important;
        left: 10px !important;
    }
}
```

---

## 💻 PROMPT 3: Extension JavaScript
**Fichier à modifier :** `/wp-content/themes/uicore-pro/configurator/assets/js/configurator.js`

### Tâche 3.1: Extension de la classe NFCConfigurator
**AJOUTER à la fin du fichier JavaScript :**

```javascript
/**
 * EXTENSION LOGO VERSO + NOM OPTIONNEL
 * Ajouter après la définition de la classe NFCConfigurator
 */

// Extension de la classe existante
(function() {
    'use strict';
    
    // Sauvegarder les méthodes originales
    const originalInit = window.NFCConfigurator.prototype.initialize;
    const originalValidate = window.NFCConfigurator.prototype.validateConfiguration;
    const originalGetConfig = window.NFCConfigurator.prototype.getConfiguration;
    
    // Étendre l'initialisation
    window.NFCConfigurator.prototype.initialize = function() {
        // Appeler l'init original
        if (originalInit) originalInit.call(this);
        
        // Ajouter logo verso
        this.initLogoVerso();
        
        console.log('🔧 Extension logo verso initialisée');
    };
    
    // Nouvelle méthode: initialisation logo verso
    window.NFCConfigurator.prototype.initLogoVerso = function() {
        // Éléments logo verso
        this.elements.logoVersoInput = document.getElementById('logoVersoInput');
        this.elements.logoVersoUploadZone = document.getElementById('logoVersoUploadZone');
        this.elements.logoVersoControls = document.getElementById('logoVersoControls');
        this.elements.logoVersoImage = document.getElementById('logoVersoImage');
        this.elements.logoVersoScale = document.getElementById('logoVersoScale');
        this.elements.logoVersoScaleValue = document.getElementById('logoVersoScaleValue');
        this.elements.removeLogoVersoBtn = document.getElementById('removeLogoVersoBtn');
        this.elements.logoVersoArea = document.getElementById('logoVersoArea');
        
        // État logo verso
        this.state.logoVerso = {
            dataUrl: null,
            name: null,
            scale: 100
        };
        
        this.bindLogoVersoEvents();
    };
    
    // Binding événements logo verso
    window.NFCConfigurator.prototype.bindLogoVersoEvents = function() {
        const self = this;
        
        // Upload logo verso
        if (this.elements.logoVersoInput) {
            this.elements.logoVersoInput.addEventListener('change', function(e) {
                self.handleLogoVersoSelect(e);
            });
        }
        
        // Click upload zone
        if (this.elements.logoVersoUploadZone) {
            this.elements.logoVersoUploadZone.addEventListener('click', function() {
                self.elements.logoVersoInput.click();
            });
            
            // Drag & Drop
            this.elements.logoVersoUploadZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.currentTarget.classList.add('drag-over');
            });
            
            this.elements.logoVersoUploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                e.currentTarget.classList.remove('drag-over');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    self.processLogoVersoFile(files[0]);
                }
            });
        }
        
        // Slider taille
        if (this.elements.logoVersoScale) {
            this.elements.logoVersoScale.addEventListener('input', function(e) {
                self.updateLogoVersoScale(parseInt(e.target.value));
            });
        }
        
        // Bouton supprimer
        if (this.elements.removeLogoVersoBtn) {
            this.elements.removeLogoVersoBtn.addEventListener('click', function() {
                self.removeLogoVerso();
            });
        }
    };
    
    // Gestion sélection fichier logo verso
    window.NFCConfigurator.prototype.handleLogoVersoSelect = function(e) {
        const file = e.target.files[0];
        if (file) {
            this.processLogoVersoFile(file);
        }
    };
    
    // Traitement fichier logo verso
    window.NFCConfigurator.prototype.processLogoVersoFile = function(file) {
        // Utiliser validation existante
        const validation = this.validateImageFile(file);
        if (!validation.valid) {
            this.showError(validation.message);
            return;
        }
        
        const self = this;
        const reader = new FileReader();
        
        reader.onload = function(e) {
            self.setLogoVerso(e.target.result, file.name);
        };
        
        reader.onerror = function() {
            self.showError('Erreur lecture logo verso');
        };
        
        reader.readAsDataURL(file);
    };
    
    // Définir logo verso
    window.NFCConfigurator.prototype.setLogoVerso = function(dataUrl, fileName) {
        this.state.logoVerso = {
            dataUrl: dataUrl,
            name: fileName,
            scale: 100
        };
        
        // Mettre à jour aperçu
        if (this.elements.logoVersoImage) {
            this.elements.logoVersoImage.src = dataUrl;
            this.elements.logoVersoImage.classList.remove('d-none');
        }
        
        if (this.elements.logoVersoArea) {
            this.elements.logoVersoArea.classList.add('has-logo');
        }
        
        // Afficher contrôles
        if (this.elements.logoVersoControls) {
            this.elements.logoVersoControls.classList.remove('d-none');
        }
        
        // Mettre à jour texte upload
        const uploadText = this.elements.logoVersoUploadZone.querySelector('.upload-text');
        if (uploadText) {
            uploadText.textContent = '✅ ' + fileName;
        }
        
        console.log('✅ Logo verso défini:', fileName);
    };
    
    // Mettre à jour taille logo verso
    window.NFCConfigurator.prototype.updateLogoVersoScale = function(scale) {
        this.state.logoVerso.scale = scale;
        
        if (this.elements.logoVersoImage) {
            this.elements.logoVersoImage.style.transform = `scale(${scale / 100})`;
        }
        
        if (this.elements.logoVersoScaleValue) {
            this.elements.logoVersoScaleValue.textContent = scale + '%';
        }
    };
    
    // Supprimer logo verso
    window.NFCConfigurator.prototype.removeLogoVerso = function() {
        this.state.logoVerso = { dataUrl: null, name: null, scale: 100 };
        
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
        
        const uploadText = this.elements.logoVersoUploadZone.querySelector('.upload-text');
        if (uploadText) {
            uploadText.textContent = 'Sélectionner un logo...';
        }
        
        console.log('✅ Logo verso supprimé');
    };
    
    // Override validation - nom optionnel
    window.NFCConfigurator.prototype.validateConfiguration = function() {
        // Configuration toujours valide maintenant
        this.state.isValid = true;
        
        if (this.elements.addToCartBtn) {
            this.elements.addToCartBtn.disabled = false;
        }
        
        console.log('✅ Validation étendue - toujours valide');
    };
    
    // Override getConfiguration - inclure logo verso
    window.NFCConfigurator.prototype.getConfiguration = function() {
        const config = originalGetConfig ? originalGetConfig.call(this) : {};
        
        // Ajouter logo verso
        if (this.state.logoVerso && this.state.logoVerso.dataUrl) {
            config.logoVerso = this.state.logoVerso;
        }
        
        // S'assurer que les noms peuvent être vides
        if (config.user) {
            config.user.firstName = (config.user.firstName || '').trim();
            config.user.lastName = (config.user.lastName || '').trim();
        }
        
        console.log('📦 Configuration étendue:', config);
        return config;
    };
    
})();
```

---

## 🧪 PROMPT 4: Test Frontend
**Tâche de validation :**

### Tests à effectuer :
1. **Aperçu logo verso** : Vérifier que la zone apparaît dans la carte verso
2. **Upload logo verso** : Tester sélection fichier + drag & drop
3. **Contrôles logo verso** : Slider taille + bouton suppression
4. **Nom optionnel** : Vérifier que le bouton panier n'est plus bloqué
5. **Validation** : Configuration valide même sans nom/prénom
6. **Console logs** : Vérifier les messages de debug

### Checklist de validation :
- [ ] Zone logo verso visible dans aperçu carte verso
- [ ] Section configuration logo verso présente
- [ ] Upload logo verso fonctionne
- [ ] Aperçu temps réel logo verso
- [ ] Slider redimensionnement logo verso
- [ ] Bouton suppression logo verso
- [ ] Placeholders nom/prénom "optionnel"
- [ ] Bouton panier toujours actif
- [ ] Pas d'erreurs console JavaScript
- [ ] Logs d'extension visibles dans console

---

## 📋 Ordre d'exécution recommandé :
1. **PROMPT 1** - Modifier le template HTML
2. **PROMPT 2** - Ajouter le CSS
3. **PROMPT 3** - Étendre le JavaScript  
4. **PROMPT 4** - Tester le frontend

Chaque prompt peut être exécuté indépendamment, mais dans cet ordre pour éviter les erreurs JavaScript.