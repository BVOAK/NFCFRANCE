/**
 * vCard Editor - Manager JavaScript
 * Architecture standardisée NFC France + Corrections syntaxe
 */

class VCardEditor {
    constructor() {
        this.config = window.vcardEditConfig || {};
        this.form = null;
        this.saveButton = null;
        this.saveButtonHeader = null;
        this.saveStatusBar = null;
        this.customUrlCheckbox = null;
        this.customUrlSection = null;
        this.saveTimeout = null;
        this.cropper = null;

        // État
        this.isDirty = false;
        this.isSaving = false;
        this.lastSaveData = null;
        this.pendingDeletions = [];

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
        this.initImageUploads();
        this.initCustomUrl();

        console.log('✅ VCard Editor initialized', this.config);
    }

    /**
     * Initialisation des éléments DOM
     */
    initDOMElements() {
        this.form = document.getElementById('vcard-edit-form');
        this.saveButton = document.getElementById('save-btn');
        this.saveButtonHeader = document.getElementById('save-btn-header');
        this.saveStatusBar = document.getElementById('save-status-bar');
        this.customUrlCheckbox = document.getElementById('enable_custom_url');
        this.customUrlSection = document.getElementById('custom_url_section');

        if (!this.form) {
            console.error('❌ Form vcard-edit-form not found');
            return;
        }

        console.log('✅ DOM elements initialized');
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

        // Sync des boutons save
        if (this.saveButtonHeader && this.saveButton) {
            this.saveButtonHeader.addEventListener('click', () => {
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

        console.log('✅ Event listeners initialized');
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
            const redirectMode = this.customUrlCheckbox && this.customUrlCheckbox.checked ? 'custom' : 'vcard';
            formData.append('redirect_mode', redirectMode);

            // Gestion des suppressions d'images
            if (this.pendingDeletions && this.pendingDeletions.length > 0) {
                if (this.pendingDeletions.includes('profile_picture')) {
                    formData.append('delete_profile_picture', 'true');
                }
                if (this.pendingDeletions.includes('cover_image')) {
                    formData.append('delete_cover_image', 'true');
                }
            }

            const response = await this.callAjax('save_vcard_data', formData);

            if (response.success) {
                this.isDirty = false;
                this.pendingDeletions = []; // Reset suppressions
                this.lastSaveData = this.getFormData();

                if (showFeedback) {
                    this.showSaveStatus('success', response.data && response.data.message || 'Modifications sauvegardées');

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
                    this.showSaveStatus('error', response.data && response.data.message || 'Erreur lors de la sauvegarde');
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
            for (let pair of data.entries()) {
                formData.append(pair[0], pair[1]);
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
        const buttons = [this.saveButton, this.saveButtonHeader].filter(btn => btn);

        buttons.forEach(button => {
            switch (state) {
                case 'saving':
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sauvegarde...';
                    break;
                case 'normal':
                default:
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
                    break;
            }
        });
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

        if (icon && text) {
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
        // Récupérer les valeurs actuelles
        const firstname = document.getElementById('firstname') ? document.getElementById('firstname').value : '';
        const lastname = document.getElementById('lastname') ? document.getElementById('lastname').value : '';
        const post = document.getElementById('post') ? document.getElementById('post').value : '';
        const society = document.getElementById('society') ? document.getElementById('society').value : '';
        const email = document.getElementById('email') ? document.getElementById('email').value : '';
        const phone = document.getElementById('phone') ? document.getElementById('phone').value : '';
        const description = document.getElementById('description') ? document.getElementById('description').value : '';

        // Mettre à jour les éléments du preview
        const previewName = document.querySelector('.preview-name');
        const previewJob = document.querySelector('.preview-job');
        const previewCompany = document.querySelector('.preview-company');

        const fullName = `${firstname} ${lastname}`.trim() || 'Nom Prénom';

        if (previewName) {
            previewName.textContent = fullName;
        }

        if (previewJob) {
            previewJob.textContent = post || '';
            previewJob.style.display = post ? 'block' : 'none';
        }

        if (previewCompany) {
            previewCompany.textContent = society || '';
            previewCompany.style.display = society ? 'block' : 'none';
        }

        // Mettre à jour les contacts dans le preview
        this.updatePreviewContacts();
    }

    /**
     * Mettre à jour les contacts du preview
     */
    updatePreviewContacts() {
        const email = document.getElementById('email') ? document.getElementById('email').value : '';
        const phone = document.getElementById('phone') ? document.getElementById('phone').value : '';
        const previewContacts = document.querySelector('.preview-contacts');

        if (previewContacts) {
            let contactsHtml = '';

            if (email) {
                contactsHtml += `
                    <div class="preview-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>${email}</span>
                    </div>
                `;
            }

            if (phone) {
                contactsHtml += `
                    <div class="preview-contact-item">
                        <i class="fas fa-phone"></i>
                        <span>${phone}</span>
                    </div>
                `;
            }

            previewContacts.innerHTML = contactsHtml;
        }
    }

    /**
     * Initialiser les uploads d'images
     */
    initImageUploads() {
        // Upload photo de profil
        const profileInput = document.getElementById('profile_image_input');
        if (profileInput) {
            profileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    this.handleImageUpload(e.target.files[0], 'profile');
                }
            });
        }

        // Upload photo de couverture
        const coverInput = document.getElementById('cover_image_input');
        if (coverInput) {
            coverInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    this.handleImageUpload(e.target.files[0], 'cover');
                }
            });
        }

        console.log('✅ Image uploads initialized');
    }

    /**
 * Gérer l'upload d'image - CORRECTION DOMException
 */
    async handleImageUpload(file, type) {
        console.log(`📸 Uploading ${type} image:`, file.name);
        console.log('📁 File object:', file);

        // Validation
        if (!this.validateFile(file)) return;

        try {
            // 🔥 CRÉER LE FormData IMMÉDIATEMENT (avant toute manipulation du file)
            const formData = new FormData();
            const fieldName = type === 'profile' ? 'profile_picture' : 'cover_image';

            formData.append('file', file);
            formData.append('vcard_id', this.config.vcard_id);
            formData.append('field_name', fieldName);
            formData.append('action', 'upload_vcard_image');
            formData.append('nonce', this.config.nonce);

            // 🔥 DEBUG: Vérifier le FormData
            console.log('📤 FormData contents:');
            for (let pair of formData.entries()) {
                console.log(`  ${pair[0]}:`, pair[1]);
            }

            // Upload via fetch
            const response = await fetch(this.config.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('📥 Upload response:', result);

            if (result.success) {
                const imageUrl = result.data.url;
                console.log(`✅ ${type} uploaded:`, imageUrl);

                if (imageUrl) {
                    // 🔥 MAINTENANT faire le preview APRÈS l'upload réussi
                    this.showImagePreviewFromUrl(imageUrl, type);
                    this.updateImagePreview(fieldName, imageUrl);
                    this.showNotification('Image mise à jour avec succès', 'success');
                    this.markAsDirty();
                    return { url: imageUrl };
                }
            } else {
                console.error(`❌ ${type} upload error:`, result.data);
                this.showNotification('error', `Erreur lors de l'upload ${type}: ${result.data?.message || 'Erreur inconnue'}`);
            }

        } catch (error) {
            console.error(`❌ ${type} upload error:`, error);
            this.showNotification('error', `Erreur de connexion: ${error.message}`);
        }
    }

    /**
     * Afficher preview depuis URL (après upload réussi)
     */
    showImagePreviewFromUrl(imageUrl, type) {
        console.log(`🖼️ Preview from URL: ${imageUrl} for ${type}`);

        const previewId = type === 'profile' ? '#profile-preview' : '#cover-preview';
        const previewImg = document.querySelector(`${previewId} img`);

        if (previewImg) {
            previewImg.src = imageUrl;
            previewImg.style.display = 'block';
        }

        // Mettre à jour le preview principal si c'est la photo de profil
        if (type === 'profile') {
            const mainPreview = document.querySelector('.preview-profile-image');
            if (mainPreview) {
                mainPreview.src = imageUrl;
            }
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
    /**
 * Preview immédiat (optionnel) - VERSION SÉCURISÉE  
 */
    showImagePreview(file, type) {
        // Créer un nouveau FileReader pour éviter les conflits
        const reader = new FileReader();

        reader.onload = (e) => {
            const previewId = type === 'profile' ? '#profile-preview' : '#cover-preview';
            const previewImg = document.querySelector(`${previewId} img`);

            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                // Ajouter une classe pour indiquer que c'est temporaire
                previewImg.classList.add('preview-temp');
            }
        };

        // Utiliser readAsDataURL au lieu de readAsArrayBuffer
        reader.readAsDataURL(file);
    }

    /**
     * Mettre à jour preview image avec URL finale
     */
    updateImagePreview(fieldName, imageUrl) {
        console.log(`🖼️ Updating preview for ${fieldName}:`, imageUrl);

        // Mettre à jour l'input hidden si il existe
        const hiddenInput = document.querySelector(`input[name="${fieldName}"]`);
        if (hiddenInput) {
            hiddenInput.value = imageUrl || '';
        }

        // Mettre à jour l'aperçu visuel
        const previewImg = document.querySelector(`#${fieldName}-preview img, .${fieldName}-preview img`);
        if (previewImg && imageUrl) {
            previewImg.src = imageUrl;
            previewImg.style.display = 'block';
        }

        // Mettre à jour le preview principal si c'est la photo de profil
        if (fieldName === 'profile_picture') {
            const mainPreview = document.querySelector('.preview-profile-image');
            if (mainPreview && imageUrl) {
                mainPreview.src = imageUrl;
            }
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
        for (let pair of formData.entries()) {
            data[pair[0]] = pair[1];
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
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
}

// Fonctions globales pour compatibilité avec l'existant
window.removeProfileImage = function () {
    if (!confirm('Supprimer la photo de profil ?')) return;

    console.log('🗑️ Suppression photo de profil demandée');

    // Calculer les initiales depuis les champs du formulaire
    const firstname = document.getElementById('firstname') ? document.getElementById('firstname').value : 'U';
    const lastname = document.getElementById('lastname') ? document.getElementById('lastname').value : 'N';
    const initials = firstname.charAt(0).toUpperCase() + lastname.charAt(0).toUpperCase();

    // Remettre l'état initial (initiales)
    const profilePreview = document.getElementById('profile-preview');
    if (profilePreview) {
        profilePreview.innerHTML = `
            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center border"
                 style="width: 120px; height: 120px; margin: 0 auto;">
                <span class="text-primary fw-bold fs-2">${initials}</span>
            </div>
        `;
    }

    // Reset l'input
    const profileInput = document.getElementById('profile_image_input');
    if (profileInput) profileInput.value = '';

    // Marquer la suppression
    if (window.vCardEditor) {
        if (!window.vCardEditor.pendingDeletions.includes('profile_picture')) {
            window.vCardEditor.pendingDeletions.push('profile_picture');
        }
        window.vCardEditor.markAsDirty();
    }

    // Mettre à jour le preview principal
    const previewAvatar = document.querySelector('.preview-avatar');
    if (previewAvatar) {
        previewAvatar.innerHTML = `<span class="initials">${initials}</span>`;
    }
};

window.removeCoverImage = function () {
    if (!confirm('Supprimer l\'image de couverture ?')) return;

    console.log('🗑️ Suppression image de couverture demandée');

    // Remettre l'état initial (placeholder)
    const coverPreview = document.getElementById('cover-preview');
    if (coverPreview) {
        coverPreview.innerHTML = `
            <div class="bg-light rounded border d-flex align-items-center justify-content-center"
                 style="width: 100%; height: 120px;">
                <div class="text-center">
                    <i class="fas fa-image fa-2x text-muted mb-2"></i>
                    <div class="text-muted small">Aucune image de couverture</div>
                </div>
            </div>
        `;
    }

    // Reset l'input
    const coverInput = document.getElementById('cover_image_input');
    if (coverInput) coverInput.value = '';

    // Marquer la suppression
    if (window.vCardEditor) {
        if (!window.vCardEditor.pendingDeletions.includes('cover_image')) {
            window.vCardEditor.pendingDeletions.push('cover_image');
        }
        window.vCardEditor.markAsDirty();
    }
};

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', function () {
    window.vCardEditor = new VCardEditor();
});