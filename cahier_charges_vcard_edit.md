# ⚙️ CAHIER DES CHARGES - Page vCard Edit

**Version :** 1.0  
**Date :** 15/09/2025  
**Projet :** NFC France - Dashboard Unifié  
**Objectif :** Créer un éditeur vCard complet, intuitif et en temps réel

---

## 🎯 **OBJECTIF PRINCIPAL**

Développer une page **vCard Edit unique** qui :
- **Permet l'édition complète** de tous les champs vCard
- **Sauvegarde en temps réel** avec preview instantané
- **Gère les médias** : photos, logos, images personnalisées
- **Configuration avancée** : comportement scan, URLs personnalisées
- **Utilise l'architecture standardisée** (PHP/JS/AJAX séparés)

---

## 📊 **ANALYSE FONCTIONNELLE**

### **A1. Structure de l'Éditeur**

#### **A1.1 Layout Principal**
```
┌─ SIDEBAR PREVIEW (30%) ──┐  ┌─ FORMULAIRE ÉDITION (70%) ────────────────┐
│                          │  │                                            │
│  📱 APERÇU TEMPS RÉEL    │  │  📝 SECTIONS FORMULAIRE :                 │
│                          │  │                                            │
│  👤 Photo de profil      │  │  1️⃣ Informations personnelles            │
│  📛 Nom + Poste          │  │  2️⃣ Contact (email, tél, adresse)        │
│  🏢 Entreprise           │  │  3️⃣ Réseaux sociaux                       │
│  ☎️ Boutons contact      │  │  4️⃣ Médias (photos, logo, documents)     │
│  🌐 Réseaux sociaux      │  │  5️⃣ Comportement scan NFC                │
│  📎 Liens personnalisés  │  │  6️⃣ Personnalisation avancée             │
│                          │  │                                            │
│  🔄 Mise à jour auto     │  │  💾 Sauvegarde automatique                │
└──────────────────────────┘  └────────────────────────────────────────────┘
```

#### **A1.2 États de la Page**
1. **Mode Édition** : Formulaire actif + preview temps réel
2. **Mode Chargement** : Skeleton UI pendant récupération données
3. **Mode Sauvegarde** : Indicateurs de synchronisation
4. **Mode Erreur** : Messages d'erreur contextuels

### **A2. Sections du Formulaire**

#### **A2.1 Informations Personnelles**
```php
- Prénom* (obligatoire)
- Nom* (obligatoire)
- Poste/Fonction
- Département
- Description/Bio (textarea)
- Photo de profil (upload + crop)
```

#### **A2.2 Contact Professionnel**
```php
- Email principal*
- Email secondaire
- Téléphone principal*
- Téléphone secondaire (mobile)
- Site web
- Adresse complète (champs séparés)
```

#### **A2.3 Entreprise**
```php
- Nom entreprise*
- Logo entreprise (upload)
- Secteur d'activité
- Site web entreprise
- Adresse entreprise
- Description entreprise
```

#### **A2.4 Réseaux Sociaux**
```php
- LinkedIn (URL profil)
- Facebook (URL page)
- Instagram (username)
- Twitter/X (handle)
- YouTube (chaîne)
- TikTok (username)
- WhatsApp (numéro formaté)
- + Réseaux personnalisés
```

#### **A2.5 Comportement NFC/QR**
```php
- Action scan : [Ouvrir vCard | Rediriger URL]
- URL personnalisée (si redirection)
- Message d'accueil
- Call-to-action principal
- Boutons personnalisés (max 5)
```

#### **A2.6 Personnalisation**
```php
- Thème couleur (palette prédéfinie)
- Layout (minimal, standard, complet)
- Langue d'affichage
- Timezone
- Paramètres confidentialité
```

### **A3. Fonctionnalités Avancées**

#### **A3.1 Upload de Médias**
- **Drag & Drop** pour toutes les images
- **Crop automatique** format optimal
- **Compression intelligente** (<500KB)
- **Formats supportés** : JPG, PNG, SVG
- **Preview instantané** dans sidebar

#### **A3.2 Sauvegarde Temps Réel**
- **Auto-save** toutes les 3 secondes
- **Indicateur visuel** de synchronisation
- **Gestion conflits** si plusieurs onglets
- **Backup local** en cas de déconnexion

#### **A3.3 Preview Interactif**
- **Rendu identique** à la vCard publique
- **Mise à jour instantanée** à chaque modification
- **Test des boutons** fonctionnels
- **Responsive preview** (mobile/desktop)

---

## 🏗️ **ARCHITECTURE TECHNIQUE**

### **F1. Fichiers selon Standard**

#### **F1.1 Template Principal**
```
📁 wp-content/plugins/gtmi-vcard/templates/dashboard/
└── vcard-edit.php                    # 🆕 Éditeur vCard complet
```

#### **F1.2 Manager JavaScript**
```
📁 wp-content/plugins/gtmi-vcard/assets/js/dashboard/
└── vcard-editor.js                   # 🆕 Gestion édition + preview
```

#### **F1.3 Handlers AJAX (dans ajax-handlers.php)**
```php
// 🆕 Nouvelles méthodes à ajouter
public function save_vcard_field()       // Sauvegarde champ individuel
public function upload_vcard_media()     // Upload images/médias
public function delete_vcard_media()     // Supprimer média
public function get_vcard_preview()      // Générer preview HTML
public function validate_vcard_data()    // Validation avant sauvegarde
public function duplicate_vcard()        // Dupliquer vCard existante
```

#### **F1.4 Assets Spécifiques**
```
📁 wp-content/plugins/gtmi-vcard/assets/
├── css/dashboard/vcard-edit.css      # 🆕 Styles éditeur
├── js/vendor/cropper.min.js          # 🆕 Crop images
└── js/vendor/debounce.min.js         # 🆕 Anti-spam auto-save
```

### **F2. Structure vcard-edit.php**

```php
<?php
/**
 * Dashboard - vCard Edit
 * Éditeur vCard complet avec preview temps réel
 */

// 1. VÉRIFICATIONS SÉCURITÉ
if (!defined('ABSPATH')) exit;
if (!is_user_logged_in()) wp_redirect(home_url('/login'));

// 2. LOGIQUE MÉTIER
$user_id = get_current_user_id();
$card_id = intval($_GET['card'] ?? 0);

// Déterminer la vCard à éditer
if ($card_id) {
    // Vérifier que l'utilisateur possède cette vCard
    $vcard = get_post($card_id);
    if (!$vcard || $vcard->post_author != $user_id) {
        wp_redirect('?page=cards-list');
        exit;
    }
} else {
    // Première vCard de l'utilisateur
    $user_vcards = $this->get_user_vcards($user_id);
    if (empty($user_vcards)) {
        wp_redirect('?page=cards-list');
        exit;
    }
    $vcard = $user_vcards[0];
    $card_id = $vcard->ID;
}

// Récupérer toutes les données existantes
$vcard_data = $this->get_complete_vcard_data($card_id);

// Configuration JavaScript
$editor_config = [
    'card_id' => $card_id,
    'vcard_data' => $vcard_data,
    'user_id' => $user_id,
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'upload_url' => admin_url('admin-ajax.php'),
    'max_file_size' => 5 * 1024 * 1024, // 5MB
    'allowed_types' => ['image/jpeg', 'image/png', 'image/svg+xml'],
    'auto_save_interval' => 3000, // 3 secondes
    'preview_url' => get_permalink($card_id)
];
?>

<!-- CSS et dépendances -->
<link rel="stylesheet" href="<?= plugin_dir_url(__FILE__) ?>../../assets/css/dashboard/vcard-edit.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">

<!-- Configuration JavaScript -->
<script>window.EDITOR_CONFIG = <?= json_encode($editor_config) ?>;</script>

<!-- HTML PRINCIPAL -->
<div class="dashboard-vcard-editor">
    <div class="row g-4">
        <!-- SIDEBAR PREVIEW -->
        <div class="col-xl-4">
            <div class="preview-container sticky-top">
                <div class="preview-header">
                    <h5><i class="fas fa-eye me-2"></i>Aperçu temps réel</h5>
                    <div class="preview-controls">
                        <button class="btn btn-sm btn-outline-primary" id="toggle-device">
                            <i class="fas fa-mobile-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" id="open-public">
                            <i class="fas fa-external-link-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="preview-frame" id="vcard-preview">
                    <!-- Preview généré dynamiquement -->
                </div>
                <div class="save-status" id="save-status">
                    <i class="fas fa-check-circle text-success"></i>
                    <span>Sauvegardé</span>
                </div>
            </div>
        </div>
        
        <!-- FORMULAIRE ÉDITION -->
        <div class="col-xl-8">
            <form id="vcard-edit-form" novalidate>
                <!-- Section 1: Informations personnelles -->
                <div class="editor-section">
                    <div class="section-header">
                        <h4><i class="fas fa-user me-2"></i>Informations personnelles</h4>
                        <p class="text-muted">Vos informations de base et photo de profil</p>
                    </div>
                    <div class="section-content">
                        <!-- Champs formulaire -->
                    </div>
                </div>
                
                <!-- Section 2: Contact -->
                <div class="editor-section">...</div>
                
                <!-- Section 3: Entreprise -->
                <div class="editor-section">...</div>
                
                <!-- Section 4: Réseaux sociaux -->
                <div class="editor-section">...</div>
                
                <!-- Section 5: Comportement -->
                <div class="editor-section">...</div>
                
                <!-- Section 6: Personnalisation -->
                <div class="editor-section">...</div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="<?= plugin_dir_url(__FILE__) ?>../../assets/js/dashboard/vcard-editor.js"></script>
```

### **F3. Structure vcard-editor.js**

```javascript
class NFCVCardEditor {
    constructor(config) {
        this.config = config;
        this.isDirty = false;
        this.autoSaveTimer = null;
        this.croppers = {};
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializePreview();
        this.startAutoSave();
        this.loadExistingData();
    }
    
    setupEventListeners() {
        // Champs formulaire
        $('#vcard-edit-form').on('input change', 'input, textarea, select', 
            this.handleFieldChange.bind(this));
        
        // Upload de médias
        $('.media-upload').on('change', this.handleMediaUpload.bind(this));
        $('.media-drop-zone').on('dragover drop', this.handleDragDrop.bind(this));
        
        // Boutons preview
        $('#toggle-device').on('click', this.togglePreviewDevice.bind(this));
        $('#open-public').on('click', this.openPublicPreview.bind(this));
        
        // Validation en temps réel
        $('input[required]').on('blur', this.validateField.bind(this));
    }
    
    handleFieldChange(event) {
        const field = event.target;
        const fieldName = field.name;
        const fieldValue = field.value;
        
        // Marquer comme modifié
        this.isDirty = true;
        this.updateSaveStatus('saving');
        
        // Mettre à jour preview immédiatement
        this.updatePreview(fieldName, fieldValue);
        
        // Programmer sauvegarde
        this.scheduleAutoSave();
    }
    
    async updatePreview(fieldName, fieldValue) {
        // Mise à jour instantanée du preview
        const previewElement = document.querySelector(`#vcard-preview [data-field="${fieldName}"]`);
        if (previewElement) {
            previewElement.textContent = fieldValue;
        }
    }
    
    async saveField(fieldName, fieldValue) {
        try {
            const response = await this.callAjax('nfc_save_vcard_field', {
                card_id: this.config.card_id,
                field_name: fieldName,
                field_value: fieldValue
            });
            
            if (response.success) {
                this.updateSaveStatus('saved');
                this.isDirty = false;
            } else {
                throw new Error(response.data.message);
            }
            
        } catch (error) {
            this.updateSaveStatus('error');
            this.showNotification('Erreur de sauvegarde: ' + error.message, 'error');
        }
    }
    
    async handleMediaUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validation
        if (!this.validateFile(file)) return;
        
        // Afficher cropper
        this.showImageCropper(file, event.target.dataset.field);
    }
    
    showImageCropper(file, fieldName) {
        // Modal avec Cropper.js pour redimensionnement
        const modal = this.createCropperModal(file, fieldName);
        modal.show();
    }
    
    async uploadCroppedImage(croppedBlob, fieldName) {
        const formData = new FormData();
        formData.append('action', 'nfc_upload_vcard_media');
        formData.append('card_id', this.config.card_id);
        formData.append('field_name', fieldName);
        formData.append('image', croppedBlob, 'image.jpg');
        formData.append('nonce', this.config.nonce);
        
        try {
            const response = await fetch(this.config.upload_url, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Mettre à jour preview avec nouvelle image
                this.updateImagePreview(fieldName, result.data.url);
                this.showNotification('Image mise à jour', 'success');
            } else {
                throw new Error(result.data.message);
            }
            
        } catch (error) {
            this.showNotification('Erreur upload: ' + error.message, 'error');
        }
    }
    
    startAutoSave() {
        setInterval(() => {
            if (this.isDirty) {
                this.saveAllChanges();
            }
        }, this.config.auto_save_interval);
    }
    
    async saveAllChanges() {
        const formData = new FormData(document.getElementById('vcard-edit-form'));
        
        try {
            const response = await this.callAjax('nfc_save_vcard_field', {
                card_id: this.config.card_id,
                form_data: Object.fromEntries(formData.entries())
            });
            
            if (response.success) {
                this.updateSaveStatus('saved');
                this.isDirty = false;
            }
            
        } catch (error) {
            this.updateSaveStatus('error');
        }
    }
    
    updateSaveStatus(status) {
        const statusEl = document.getElementById('save-status');
        const statusMap = {
            saving: { icon: 'fa-spinner fa-spin', text: 'Sauvegarde...', class: 'text-warning' },
            saved: { icon: 'fa-check-circle', text: 'Sauvegardé', class: 'text-success' },
            error: { icon: 'fa-exclamation-triangle', text: 'Erreur', class: 'text-danger' }
        };
        
        const config = statusMap[status];
        statusEl.innerHTML = `<i class="fas ${config.icon} ${config.class}"></i><span>${config.text}</span>`;
    }
    
    // Méthodes utilitaires
    validateFile(file) { /* Validation fichier */ }
    createCropperModal(file, fieldName) { /* Modal cropper */ }
    showNotification(message, type) { /* Toast notification */ }
    callAjax(action, data) { /* Appel AJAX standardisé */ }
}
```

### **F4. Handlers AJAX Principaux**

```php
/**
 * Sauvegarder un champ vCard
 */
public function save_vcard_field() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $card_id = intval($_POST['card_id'] ?? 0);
    $field_name = sanitize_text_field($_POST['field_name'] ?? '');
    $field_value = sanitize_textarea_field($_POST['field_value'] ?? '');
    
    if (!$card_id || !$field_name) {
        wp_send_json_error(['message' => 'Paramètres manquants']);
        return;
    }
    
    // Vérifier propriété
    if (!$this->user_owns_card($card_id)) {
        wp_send_json_error(['message' => 'Non autorisé']);
        return;
    }
    
    try {
        // Validation spécifique selon le champ
        $validated_value = $this->validate_field_value($field_name, $field_value);
        
        // Sauvegarde
        update_post_meta($card_id, $field_name, $validated_value);
        
        // Log activité
        $this->log_vcard_change($card_id, $field_name, $validated_value);
        
        wp_send_json_success([
            'message' => 'Champ sauvegardé',
            'field_name' => $field_name,
            'field_value' => $validated_value
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

/**
 * Upload média vCard
 */
public function upload_vcard_media() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $card_id = intval($_POST['card_id'] ?? 0);
    $field_name = sanitize_text_field($_POST['field_name'] ?? '');
    
    if (!$card_id || !$field_name || empty($_FILES['image'])) {
        wp_send_json_error(['message' => 'Paramètres manquants']);
        return;
    }
    
    if (!$this->user_owns_card($card_id)) {
        wp_send_json_error(['message' => 'Non autorisé']);
        return;
    }
    
    try {
        // Upload avec WordPress
        $uploaded = $this->handle_media_upload($_FILES['image'], $card_id, $field_name);
        
        // Sauvegarde en meta
        update_post_meta($card_id, $field_name, $uploaded['url']);
        
        wp_send_json_success([
            'message' => 'Image uploadée',
            'url' => $uploaded['url'],
            'attachment_id' => $uploaded['id']
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
```

---

## ⚡ **PLAN DE DÉVELOPPEMENT**

### **Phase 1 : Structure Base (4h)**
1. **Créer vcard-edit.php** avec layout 2 colonnes
2. **Formulaire sections** de base sans interactions
3. **Preview statique** avec données existantes
4. **CSS de base** responsive

### **Phase 2 : Édition Temps Réel (4h)**
1. **Auto-save système** avec debouncing
2. **Preview dynamique** mise à jour instantanée
3. **Validation champs** en temps réel
4. **Handlers AJAX** sauvegarde

### **Phase 3 : Gestion Médias (3h)**
1. **Upload drag & drop** images
2. **Intégration Cropper.js** redimensionnement
3. **Compression automatique** images
4. **Preview images** instantané

### **Phase 4 : Fonctionnalités Avancées (2h)**
1. **Comportement scan** configuration
2. **URLs personnalisées** validation
3. **Thèmes couleur** preview
4. **Export/Import** configuration

### **Phase 5 : Finitions (1h)**
1. **Animations fluides** transitions
2. **Gestion erreurs** complète
3. **Tests multi-navigateurs**
4. **Optimisations performance**

---

## ✅ **CRITÈRES DE VALIDATION**

### **Fonctionnalités :**
- [ ] Édition tous champs vCard standards
- [ ] Sauvegarde temps réel fonctionnelle
- [ ] Preview mise à jour instantanée
- [ ] Upload/crop images opérationnel
- [ ] Validation formulaire complète

### **Technique :**
- [ ] Architecture standardisée respectée
- [ ] Performance fluide (< 1s auto-save)
- [ ] Gestion erreurs robuste
- [ ] Compatible mobile/desktop
- [ ] Sécurité upload fichiers

### **UX :**
- [ ] Interface intuitive et moderne
- [ ] Feedback visuel constant
- [ ] Pas de perte de données
- [ ] Responsive parfait
- [ ] Animations fluides

---

*Cette page vCard Edit sera la référence pour l'édition intuitive et complète des profils vCard utilisateur.*