/**
 * vCard Editor - Manager JavaScript
 * Architecture standardisée NFC France
 */

class VCardEditor {
    constructor() {
        this.config = window.vcardEditConfig || {};
        this.form = null;
        this.saveButton = null;
        this.saveStatusBar = null;
        this.customUrlCheckbox = null;
        this.customUrlSection = null;
        this.saveTimeout = null;
        this.cropper = null;
        
        // État
        this.isDirty = false;
        this.isSaving = false;
        this.lastSaveData = null;
        
        this.init();
    }

    /**
     * Initialisation principale
     */
    init() {
        this.initDOMElements();
        this.initEventListeners();
        this.initAutoSave();
        this.initPreview();
        this.initFileUploads();
        this.initCustomUrl();
        
        console.log('✅ VCard Editor initialized', this.config);
    }

    /**
     * Initialisation des éléments DOM
     */
    initDOMElements() {
        this.form = document.getElementById('vcard-edit-form');
        this.saveButton = document.getElementById('save-btn');
        this.saveStatusBar = document.getElementById('save-status-bar');
        this.customUrlCheckbox = document.getElementById('enable_custom_url');
        this.customUrlSection = document.getElementById('custom_url_section');
        
        if (!this.form) {
            console.error('❌ Form vcard-edit-form not found');
            return;
        }
    }

    /**
     * Initialisation des événements
     */
    initEventListeners() {
        // Soumission du formulaire
        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveVCard(true);
            });
        }

        // Auto-save sur changement des champs
        const formInputs = this.form.querySelectorAll('input, textarea, select');
        formInputs.forEach(input => {
            input.addEventListener('input', () => this.markAsDirty());
            input.addEventListener('change', () => this.markAsDirty());
        });

        // Boutons preview
        const toggleDeviceBtn = document.getElementById('toggle-device');
        const openPublicBtn = document.getElementById('open-public');
        
        if (toggleDeviceBtn) {
            toggleDeviceBtn.addEventListener('click', () => this.toggleDevicePreview());
        }
        
        if (openPublicBtn) {
            openPublicBtn.addEventListener('click', () => this.openPublicView());
        }
    }

    /**
     * Marquer le formulaire comme modifié
     */
    markAsDirty() {
        this.isDirty = true;
        this.scheduleAutoSave();
        this.updatePreview();
    }

    /**
     * Initialiser l'auto-save
     */
    initAutoSave() {
        // Sauvegarder avant de quitter la page
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty && !this.isSaving) {
                e.preventDefault();
                e.returnValue = 'Vous avez des modifications non sauvegardées.';
                return e.returnValue;
            }
        });
    }

    /**
     * Programmer l'auto-save
     */
    scheduleAutoSave() {
        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
        }
        
        this.saveTimeout = setTimeout(() => {
            if (this.isDirty && !this.isSaving) {
                this.saveVCard(false); // Auto-save silencieux
            }
        }, this.config.auto_save_interval || 2000);
    }

    /**
     * Sauvegarde vCard
     * @param {boolean} showFeedback - Afficher le feedback utilisateur
     */
    async saveVCard(showFeedback = true) {
        if (this.isSaving) return;
        
        this.isSaving = true;
        
        if (showFeedback) {
            this.updateSaveButton('saving');
        }
        
        try {
            const formData = new FormData(this.form);
            
            // Ajouter le mode de redirection
            const redirectMode = this.customUrlCheckbox?.checked ? 'custom' : 'vcard';
            formData.append('redirect_mode', redirectMode);
            
            const response = await this.callAjax('save_vcard_data', formData);
            
            if (response.success) {
                this.isDirty = false;
                this.lastSaveData = this.getFormData();
                
                if (showFeedback) {
                    this.showSaveStatus('success', response.data?.message || 'Modifications sauvegardées');
                    
                    // Redirection si en mode multi-vCard
                    if (this.config.is_multi_vcard_mode && this.config.redirect_after_save) {
                        setTimeout(() => {
                            window.location.href = this.config.redirect_after_save;
                        }, 1500);
                    }
                }
                
                this.updateSaveIndicator('saved');
                
            } else {
                if (showFeedback) {
                    this.showSaveStatus('error', response.data?.message || 'Erreur lors de la sauvegarde');
                }
                this.updateSaveIndicator('error');
            }
            
        } catch (error) {
            console.error('❌ Save error:', error);
            if (showFeedback) {
                this.showSaveStatus('error', 'Erreur de connexion');
            }
            this.updateSaveIndicator('error');
        } finally {
            this.isSaving = false;
            if (showFeedback) {
                this.updateSaveButton('normal');
            }
        }
    }

    /**
     * Appel AJAX standardisé
     */
    async callAjax(action, data = {}) {
        const formData = new FormData();
        
        if (data instanceof FormData) {
            // Si c'est déjà un FormData, l'utiliser tel quel
            for (let [key, value] of data.entries()) {
                formData.append(key, value);
            }
        } else {
            // Sinon, ajouter les données
            Object.keys(data).forEach(key => {
                formData.append(key, data[key]);
            });
        }
        
        formData.append('action', action);
        formData.append('nonce', this.config.nonce);
        
        const response = await fetch(this.config.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        return await response.json();
    }

    /**
     * Mettre à jour le bouton de sauvegarde
     */
    updateSaveButton(state) {
        if (!this.saveButton) return;
        
        switch (state) {
            case 'saving':
                this.saveButton.disabled = true;
                this.saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sauvegarde...';
                break;
            case 'normal':
            default:
                this.saveButton.disabled = false;
                this.saveButton.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
                break;
        }
    }

    /**
     * Afficher le statut de sauvegarde
     */
    showSaveStatus(type, message) {
        if (!this.saveStatusBar) return;
        
        this.saveStatusBar.className = `alert alert-${type === 'success' ? 'success' : 'danger'} mb-4`;
        const statusText = this.saveStatusBar.querySelector('#save-status-text');
        if (statusText) {
            statusText.textContent = message;
        }
        
        this.saveStatusBar.classList.remove('d-none');
        
        setTimeout(() => {
            this.saveStatusBar.classList.add('d-none');
        }, type === 'success' ? 3000 : 5000);
    }

    /**
     * Mettre à jour l'indicateur de sauvegarde
     */
    updateSaveIndicator(state) {
        const saveStatus = document.getElementById('save-status');
        if (!saveStatus) return;
        
        const icon = saveStatus.querySelector('i');
        const text = saveStatus.querySelector('span');
        
        switch (state) {
            case 'saving':
                icon.className = 'fas fa-spinner fa-spin text-warning';
                text.textContent = 'Sauvegarde...';
                break;
            case 'saved':
                icon.className = 'fas fa-check-circle text-success';
                text.textContent = 'Synchronisé';
                break;
            case 'error':
                icon.className = 'fas fa-exclamation-triangle text-danger';
                text.textContent = 'Erreur';
                break;
        }
    }

    /**
     * Initialiser le preview temps réel
     */
    initPreview() {
        this.updatePreview();
        
        // Écouter les changements pour mettre à jour le preview
        const previewFields = ['firstname', 'lastname', 'post', 'society', 'email', 'phone'];
        previewFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', () => this.updatePreview());
            }
        });
    }

    /**
     * Mettre à jour le preview en temps réel
     */
    updatePreview() {
        const previewFrame = document.getElementById('vcard-preview');
        if (!previewFrame) return;
        
        // Récupérer les valeurs actuelles
        const firstname = document.getElementById('firstname')?.value || '';
        const lastname = document.getElementById('lastname')?.value || '';
        const post = document.getElementById('post')?.value || '';
        const society = document.getElementById('society')?.value || '';
        const email = document.getElementById('email')?.value || '';
        const phone = document.getElementById('phone')?.value || '';
        const description = document.getElementById('description')?.value || '';
        
        // Générer le HTML du preview
        const fullName = `${firstname} ${lastname}`.trim() || 'Nom Prénom';
        const initials = `${firstname.charAt(0)}${lastname.charAt(0)}`.toUpperCase();
        
        const previewHtml = `
            <div class="preview-card">
                <div class="preview-header">
                    <div class="preview-avatar">
                        <span class="initials">${initials}</span>
                    </div>
                </div>
                <div class="preview-content">
                    <h3 class="preview-name">${fullName}</h3>
                    ${post ? `<p class="preview-job">${post}</p>` : ''}
                    ${society ? `<p class="preview-company">${society}</p>` : ''}
                    ${description ? `<p class="preview-description">${description}</p>` : ''}
                    <div class="preview-contacts">
                        ${email ? `
                            <div class="preview-contact-item">
                                <i class="fas fa-envelope"></i>
                                <span>${email}</span>
                            </div>
                        ` : ''}
                        ${phone ? `
                            <div class="preview-contact-item">
                                <i class="fas fa-phone"></i>
                                <span>${phone}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        
        previewFrame.innerHTML = previewHtml;
    }

    /**
     * Initialiser les uploads de fichiers
     */
    initFileUploads() {
        const uploadZone = document.getElementById('profile-upload-zone');
        const fileInput = document.getElementById('profile_picture');
        
        if (!uploadZone || !fileInput) return;
        
        // Click sur la zone
        uploadZone.addEventListener('click', () => {
            fileInput.click();
        });
        
        // Drag & Drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('drag-over');
        });
        
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('drag-over');
        });
        
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleFileUpload(files[0]);
            }
        });
        
        // Changement de fichier
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.handleFileUpload(e.target.files[0]);
            }
        });
    }

    /**
     * Gérer l'upload de fichier
     */
    async handleFileUpload(file) {
        // Validation
        if (!this.validateFile(file)) return;
        
        // Afficher preview immédiat
        this.showImagePreview(file);
        
        try {
            // Upload via AJAX
            const formData = new FormData();
            formData.append('file', file);
            formData.append('vcard_id', this.config.vcard_id);
            
            const response = await this.callAjax('upload_vcard_image', formData);
            
            if (response.success) {
                // Mettre à jour le preview avec l'URL finale
                this.updateImagePreview(response.data.url);
                this.markAsDirty();
            } else {
                this.showNotification('error', 'Erreur lors de l\'upload');
            }
            
        } catch (error) {
            console.error('❌ Upload error:', error);
            this.showNotification('error', 'Erreur de connexion');
        }
    }

    /**
     * Valider le fichier
     */
    validateFile(file) {
        // Taille
        if (file.size > this.config.max_file_size) {
            this.showNotification('error', 'Fichier trop volumineux (max 5MB)');
            return false;
        }
        
        // Type
        if (!this.config.allowed_types.includes(file.type)) {
            this.showNotification('error', 'Format non supporté');
            return false;
        }
        
        return true;
    }

    /**
     * Afficher preview image immédiat
     */
    showImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const uploadZone = document.getElementById('profile-upload-zone');
            if (uploadZone) {
                uploadZone.innerHTML = `
                    <div class="upload-preview">
                        <img src="${e.target.result}" alt="Preview" class="preview-image">
                        <div class="upload-overlay">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Upload en cours...</p>
                        </div>
                    </div>
                `;
            }
        };
        reader.readAsDataURL(file);
    }

    /**
     * Mettre à jour preview image avec URL finale
     */
    updateImagePreview(imageUrl) {
        const uploadZone = document.getElementById('profile-upload-zone');
        if (uploadZone) {
            uploadZone.innerHTML = `
                <div class="upload-preview">
                    <img src="${imageUrl}" alt="Photo de profil" class="preview-image">
                    <div class="upload-actions">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="vCardEditor.removeImage()">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        }
        
        // Mettre à jour le preview principal
        const previewAvatar = document.querySelector('.preview-avatar');
        if (previewAvatar) {
            previewAvatar.innerHTML = `<img src="${imageUrl}" alt="Photo de profil">`;
        }
    }

    /**
     * Initialiser gestion URL personnalisée
     */
    initCustomUrl() {
        if (!this.customUrlCheckbox || !this.customUrlSection) return;
        
        this.customUrlCheckbox.addEventListener('change', () => {
            if (this.customUrlCheckbox.checked) {
                this.customUrlSection.style.display = 'block';
            } else {
                this.customUrlSection.style.display = 'none';
            }
            this.markAsDirty();
        });
    }

    /**
     * Basculer preview mobile/desktop
     */
    toggleDevicePreview() {
        const previewFrame = document.getElementById('vcard-preview');
        if (previewFrame) {
            previewFrame.classList.toggle('mobile-preview');
        }
    }

    /**
     * Ouvrir vue publique
     */
    openPublicView() {
        window.open(this.config.public_url, '_blank');
    }

    /**
     * Récupérer les données du formulaire
     */
    getFormData() {
        const formData = new FormData(this.form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        return data;
    }

    /**
     * Afficher notification
     */
    showNotification(type, message) {
        // Créer une notification toast
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-supprimer après 5 secondes
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    /**
     * Supprimer image
     */
    async removeImage() {
        try {
            const response = await this.callAjax('remove_vcard_image', {
                vcard_id: this.config.vcard_id
            });
            
            if (response.success) {
                // Réinitialiser la zone d'upload
                const uploadZone = document.getElementById('profile-upload-zone');
                if (uploadZone) {
                    uploadZone.innerHTML = `
                        <div class="upload-placeholder">
                            <i class="fas fa-camera fa-2x mb-2"></i>
                            <p>Cliquez ou glissez pour uploader</p>
                            <small class="text-muted">JPG, PNG, SVG - Max 5MB</small>
                        </div>
                    `;
                }
                
                // Réinitialiser le preview
                const previewAvatar = document.querySelector('.preview-avatar');
                if (previewAvatar) {
                    const firstname = document.getElementById('firstname')?.value || '';
                    const lastname = document.getElementById('lastname')?.value || '';
                    const initials = `${firstname.charAt(0)}${lastname.charAt(0)}`.toUpperCase();
                    previewAvatar.innerHTML = `<span class="initials">${initials}</span>`;
                }
                
                this.markAsDirty();
                
            } else {
                this.showNotification('error', 'Erreur lors de la suppression');
            }
            
        } catch (error) {
            console.error('❌ Remove image error:', error);
            this.showNotification('error', 'Erreur de connexion');
        }
    }
}

// Fonctions globales pour compatibilité
function previewVCard() {
    if (window.vCardEditor) {
        window.vCardEditor.openPublicView();
    }
}

function resetForm() {
    if (confirm('Êtes-vous sûr de vouloir annuler toutes vos modifications ?')) {
        location.reload();
    }
}

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    window.vCardEditor = new VCardEditor();
});