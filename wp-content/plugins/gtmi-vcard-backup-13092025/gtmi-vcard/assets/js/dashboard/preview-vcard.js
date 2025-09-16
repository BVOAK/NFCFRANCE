/**
 * vCard Editor JavaScript - Version épurée avec animations bouton
 * 
 * Fichier: assets/js/dashboard/vcard-editor.js
 */

(function ($) {
    'use strict';

    let quickStatsLoaded = false;

    /**
     * VCard Editor principal
     */
    window.VCardEditor = {

        // État de l'éditeur
        isDirty: false,
        isSaving: false,
        originalData: {},

        // Initialisation
        init: function () {
            console.log('📝 vCard Editor - Init');

            this.cacheElements();
            this.bindEvents();

            setTimeout(() => {
                this.forceBind();
            }, 2000);

            this.loadVCardData();
            this.initImageUpload();
            this.updateBehaviorPreview();

            // Charger les stats rapides
            setTimeout(() => {
                VCardEditUtils.loadQuickStats();
            }, 1500);
        },

        testSave: function () {
            console.log('🧪 TEST MANUEL saveVCard');
            this.saveVCard();
        },

        // Cache des éléments DOM
        cacheElements: function () {
            this.$form = $('#nfc-vcard-form');

            // 🔥 FIX: Tester plusieurs sélecteurs pour trouver le bouton
            this.$saveBtn = $('#saveVCard'); // Essayer direct par ID

            if (this.$saveBtn.length === 0) {
                // Fallback si pas trouvé par ID
                this.$saveBtn = this.$form.find('button[type="submit"]');
            }

            if (this.$saveBtn.length === 0) {
                // Autre fallback
                this.$saveBtn = $('button').filter(function () {
                    return $(this).text().includes('Enregistrer') || $(this).text().includes('Sauvegarder');
                });
            }

            // 🔥 DEBUG: Vérifier quel bouton on a trouvé
            console.log('🔍 DEBUG Bouton:', {
                'Par ID #saveVCard': $('#saveVCard').length,
                'Par form submit': this.$form.find('button[type="submit"]').length,
                'Par texte': $('button').filter(function () { return $(this).text().includes('Enregistrer'); }).length,
                'Bouton final sélectionné': this.$saveBtn.length,
                'ID du bouton': this.$saveBtn.attr('id'),
                'Classes du bouton': this.$saveBtn.attr('class'),
                'Texte du bouton': this.$saveBtn.text()
            });

            // Autres éléments...
            this.$profileImage = $('#profile-preview');
            this.$coverImage = $('#cover-preview');
            this.$uploadZone = $('#profile_image_input');
            this.$coverUploadZone = $('#cover_image_input');

            // Éléments Custom URL
            this.$redirectModeRadios = $('input[name="redirect_mode"]');
            this.$customUrlSection = $('#custom-url-section');
            this.$customUrlInput = $('#custom_url_input');
            this.$behaviorPreview = $('#behavior-preview');

            // Champs du formulaire (reste identique)
            this.fields = {
                firstname: $('input[name="firstname"]'),
                lastname: $('input[name="lastname"]'),
                society: $('input[name="society"]'),
                service: $('input[name="service"]'),
                post: $('input[name="post"]'),
                email: $('input[name="email"]'),
                phone: $('input[name="phone"]'),
                mobile: $('input[name="mobile"]'),
                website: $('input[name="website"]'),
                description: $('textarea[name="description"]'),
                linkedin: $('input[name="linkedin"]'),
                twitter: $('input[name="twitter"]'),
                facebook: $('input[name="facebook"]'),
                instagram: $('input[name="instagram"]'),
                pinterest: $('input[name="pinterest"]'),
                youtube: $('input[name="youtube"]'),
                address: $('input[name="address"]'),
                additional: $('input[name="additional"]'),
                postcode: $('input[name="postcode"]'),
                city: $('input[name="city"]'),
                country: $('input[name="country"]'),
                custom_url: $('input[name="custom_url"]')
            };
        },

        findSaveButton: function () {
            // Méthode alternative pour trouver le bouton
            let $btn = null;

            // Essayer par ID
            $btn = $('#saveVCard');
            if ($btn.length > 0) {
                console.log('✅ Bouton trouvé par ID #saveVCard');
                return $btn;
            }

            // Essayer par sélecteur form
            $btn = this.$form.find('button[type="submit"]');
            if ($btn.length > 0) {
                console.log('✅ Bouton trouvé par form submit');
                return $btn;
            }

            // Essayer par classe
            $btn = $('.btn').filter(function () {
                const text = $(this).text().toLowerCase();
                return text.includes('enregistrer') || text.includes('sauvegarder') || text.includes('save');
            });
            if ($btn.length > 0) {
                console.log('✅ Bouton trouvé par texte');
                return $btn;
            }

            // Essayer tous les boutons de la page
            $btn = $('button').filter(function () {
                const $this = $(this);
                return $this.attr('id') && $this.attr('id').toLowerCase().includes('save');
            });
            if ($btn.length > 0) {
                console.log('✅ Bouton trouvé par ID contenant save');
                return $btn;
            }

            console.error('❌ AUCUN bouton de sauvegarde trouvé !');
            console.log('🔍 Tous les boutons de la page:', $('button').map(function () {
                return {
                    id: $(this).attr('id'),
                    class: $(this).attr('class'),
                    text: $(this).text(),
                    type: $(this).attr('type')
                };
            }).get());

            return $();
        },

        // Bind des événements
        bindEvents: function () {
            const self = this;
            console.log('🔗 bindEvents appelé');

            // Soumission du formulaire
            this.$form.on('submit', function (e) {
                e.preventDefault();
                self.saveVCard();
            });

            // Changements dans les champs
            this.$form.on('input change', 'input, textarea', function () {
                self.handleFieldChange($(this));
            });

            // Toggle mode redirection
            this.$redirectModeRadios.on('change', function () {
                self.handleRedirectModeChange();
            });

            // Changement URL custom
            this.$customUrlInput.on('input', function () {
                self.validateCustomUrl();
                self.updateBehaviorPreview();
            });

            // 🔥 ÉVÉNEMENTS IMAGES - VERSION UNIFIÉE
            $('#profile_image_input').on('change', function (e) {
                console.log('📷 Image profil changée');
                self.handleProfileImageChange(e);
            });

            $('#cover_image_input').on('change', function (e) {
                console.log('🖼️ Image couverture changée');
                self.handleCoverImageChange(e);
            });

            // Raccourci clavier Ctrl+S
            $(document).on('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    self.saveVCard();
                }
            });

            // Prévenir la perte de données
            $(window).on('beforeunload', function () {
                if (self.isDirty && !self.isSaving) {
                    return 'Des modifications non sauvegardées seront perdues. Voulez-vous vraiment quitter ?';
                }
            });
        },

        validateImageFile: function (file) {
            if (!file) return false;

            // Validation type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type.toLowerCase())) {
                this.showBanner('❌ Format non supporté. Utilisez JPG, PNG, GIF ou WebP.', 'error');
                return false;
            }

            // Validation taille (2MB)
            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(1);
                this.showBanner(`❌ Fichier trop volumineux (${sizeMB}MB). Maximum : 2MB.`, 'error');
                return false;
            }

            return true;
        },

        previewProfileImage: function (file) {
            if (!file) return;

            console.log('📷 Preview profile:', file.name);

            // Validation basique
            if (!file.type.startsWith('image/')) {
                this.showBanner('❌ Veuillez sélectionner une image', 'error');
                return;
            }

            // Créer le preview avec FileReader
            const reader = new FileReader();

            reader.onload = function (e) {
                // Mettre à jour le #profile-preview
                $('#profile-preview').html(`
                    <img src="${e.target.result}" 
                        class="img-fluid rounded-circle border"
                        style="width: 120px; height: 120px; object-fit: cover;"
                        alt="Aperçu photo de profil">
                `);
                console.log('✅ Preview profile mis à jour');
            };

            reader.onerror = function () {
                console.error('❌ Erreur lecture fichier');
            };

            // Lire le fichier
            reader.readAsDataURL(file);

            // Marquer comme modifié pour activer le bouton
            this.isDirty = true;
            this.updateSaveButton();
        },

        previewCoverImage: function (file) {
            if (!file) return;

            console.log('🖼️ Preview cover:', file.name);

            // Validation basique
            if (!file.type.startsWith('image/')) {
                this.showBanner('❌ Veuillez sélectionner une image', 'error');
                return;
            }

            // Créer le preview avec FileReader
            const reader = new FileReader();

            reader.onload = function (e) {
                // Mettre à jour le #cover-preview
                $('#cover-preview').html(`
                    <img src="${e.target.result}" 
                        class="img-fluid rounded border" 
                        style="width: 100%; max-height: 120px; object-fit: cover;"
                        alt="Aperçu image de couverture">
                `);
                console.log('✅ Preview cover mis à jour');
            };

            reader.onerror = function () {
                console.error('❌ Erreur lecture fichier');
            };

            // Lire le fichier
            reader.readAsDataURL(file);

            // Marquer comme modifié
            this.isDirty = true;
            this.updateSaveButton();
        },

        handleProfileImageChange: function (e) {
            const file = e.target.files[0];
            if (!file) return;

            console.log('📷 Fichier profile sélectionné:', file.name, file.size);

            // Validation
            if (!this.validateImageFile(file)) {
                e.target.value = '';
                return;
            }

            // Aperçu immédiat avec FileReader
            const reader = new FileReader();
            const self = this;

            reader.onload = function (e) {
                $('#profile-preview').html(`
                    <img src="${e.target.result}" 
                        class="img-fluid rounded-circle border"
                        style="width: 120px; height: 120px; object-fit: cover;"
                        alt="Aperçu photo de profil">
                `);
                console.log('✅ Preview profile mis à jour');
            };

            reader.onerror = function () {
                console.error('❌ Erreur lecture fichier profile');
                self.showBanner('❌ Erreur lors de la lecture du fichier', 'error');
            };

            reader.readAsDataURL(file);

            // Marquer comme modifié
            this.isDirty = true;
            this.updateSaveButton();
        },


        handleCoverImageChange: function (e) {
            const file = e.target.files[0];
            if (!file) return;

            console.log('🖼️ Fichier cover sélectionné:', file.name, file.size);

            // Validation
            if (!this.validateImageFile(file)) {
                e.target.value = '';
                return;
            }

            // Aperçu immédiat avec FileReader
            const reader = new FileReader();
            const self = this;

            reader.onload = function (e) {
                $('#cover-preview').html(`
                    <img src="${e.target.result}" 
                        class="img-fluid rounded border" 
                        style="width: 100%; max-height: 120px; object-fit: cover;"
                        alt="Aperçu image de couverture">
                `);
                console.log('✅ Preview cover mis à jour');
            };

            reader.onerror = function () {
                console.error('❌ Erreur lecture fichier cover');
                self.showBanner('❌ Erreur lors de la lecture du fichier', 'error');
            };

            reader.readAsDataURL(file);

            // Marquer comme modifié
            this.isDirty = true;
            this.updateSaveButton();
        },


        forceBind: function () {
            console.log('🔧 Force binding événements...');

            // S'assurer que le bouton existe
            if (this.$saveBtn.length === 0) {
                this.$saveBtn = $('#saveVCard');
                console.log('🔍 Bouton re-trouvé:', this.$saveBtn.length);
            }

            // Détacher les anciens événements
            this.$saveBtn.off('click.vcard');
            this.$form.off('submit.vcard');

            // Attacher avec namespace pour éviter les doublons
            const self = this;

            this.$saveBtn.on('click.vcard', function (e) {
                console.log('🖱️ FORCE CLICK EVENT !');
                e.preventDefault();
                e.stopPropagation();
                self.saveVCard();
                return false;
            });

            this.$form.on('submit.vcard', function (e) {
                console.log('📝 FORCE SUBMIT EVENT !');
                e.preventDefault();
                e.stopPropagation();
                self.saveVCard();
                return false;
            });

            console.log('✅ Force binding terminé');
        },

        // Chargement des données vCard
        loadVCardData: function () {
            const self = this;
            this.originalData = {};
            $.each(this.fields, function (name, $field) {
                self.originalData[name] = $field.val();
            });
            console.log('📊 Données vCard chargées');
        },

        // Gestion changement de champ
        handleFieldChange: function ($field) {
            this.isDirty = true;
            this.updateSaveButton();

            // Validation temps réel
            const fieldName = $field.attr('name');
            if (fieldName === 'email') {
                this.validateEmail($field);
            } else if (fieldName && fieldName.includes('url')) {
                this.validateUrl($field);
            }
        },

        // === GESTION CUSTOM URL ===

        handleRedirectModeChange: function () {
            const redirectMode = this.$redirectModeRadios.filter(':checked').val();

            console.log('🔀 Mode redirect changé:', redirectMode); // Debug

            if (redirectMode === 'custom') {
                // ENLEVER d-none ET AFFICHER
                this.$customUrlSection.removeClass('d-none').slideDown(300);
                this.$customUrlInput.attr('required', true);
                console.log('✅ Section Custom URL affichée');
            } else {
                // AJOUTER d-none ET CACHER
                this.$customUrlSection.slideUp(300, function () {
                    $(this).addClass('d-none');
                });
                this.$customUrlInput.attr('required', false);
                console.log('❌ Section Custom URL cachée');
            }

            this.updateBehaviorPreview();
            this.isDirty = true;
            this.updateSaveButton();
        },

        validateCustomUrl: function () {
            const url = this.$customUrlInput.val().trim();
            const $input = this.$customUrlInput;

            $input.removeClass('is-valid is-invalid');
            $input.siblings('.invalid-feedback, .valid-feedback').remove();

            if (!url) return false;

            try {
                new URL(url);
                $input.addClass('is-valid');
                $input.after('<div class="valid-feedback">URL valide</div>');
                return true;
            } catch {
                $input.addClass('is-invalid');
                $input.after('<div class="invalid-feedback">URL invalide</div>');
                return false;
            }
        },

        updateBehaviorPreview: function () {
            const redirectMode = this.$redirectModeRadios.filter(':checked').val();
            let previewHtml = '';

            if (redirectMode === 'custom') {
                const customUrl = this.$customUrlInput.val().trim();
                if (customUrl) {
                    previewHtml = `
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="fas fa-external-link-alt text-info me-3"></i>
                            <div>
                                <strong>Redirection active vers :</strong><br>
                                <small class="text-break">${customUrl}</small>
                            </div>
                        </div>
                    `;
                } else {
                    previewHtml = `
                        <div class="alert alert-warning d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle text-warning me-3"></i>
                            <div>
                                <strong>Mode redirection sélectionné</strong><br>
                                <small>Veuillez saisir une URL de redirection</small>
                            </div>
                        </div>
                    `;
                }
            } else {
                previewHtml = `
                    <div class="alert alert-success d-flex align-items-center">
                        <i class="fas fa-id-card text-success me-3"></i>
                        <div>
                            <strong>Mode vCard classique</strong><br>
                            <small>Affichage de vos informations de contact</small>
                        </div>
                    </div>
                `;
            }

            this.$behaviorPreview.html(previewHtml);
        },

        // === GESTION DES IMAGES ===

        initImageUpload: function () {
            console.log('🖼️ Init upload images');
        },

        handleImageUpload: function (e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validation taille (2MB)
            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(1);
                // CHANGÉ: showMessage → showBanner
                this.showBanner(`Image trop lourde (${sizeMB}MB). Maximum : 2MB`, 'error');
                e.target.value = '';
                return;
            }

            if (!this.validateImageFile(file)) {
                // CHANGÉ: showMessage → showBanner
                this.showBanner('Format non supporté. Utilisez JPG, PNG ou GIF', 'error');
                e.target.value = '';
                return;
            }

            this.processImageFile(file);
        },

        validateImageFile: function (file) {
            const maxSize = 2 * 1024 * 1024; // 2MB
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

            if (!allowedTypes.includes(file.type)) {
                this.showBanner('❌ Format non supporté. Utilisez JPG, PNG ou GIF.', 'error');
                return false;
            }

            if (file.size > maxSize) {
                this.showBanner('❌ Fichier trop volumineux (max 2MB)', 'error');
                return false;
            }

            return true;
        },

        processImageFile: function (file) {
            const self = this;
            const reader = new FileReader();

            reader.onload = function (e) {
                self.$profileImage.html(`
                    <img src="${e.target.result}" 
                         class="img-fluid rounded-circle border"
                         style="width: 120px; height: 120px; object-fit: cover;">
                `);
            };

            reader.readAsDataURL(file);
            this.isDirty = true;
            this.updateSaveButton();
        },

        handleProfileImageUpload: function (e) {
            const file = e.target.files[0];
            if (!file) return;

            console.log('📷 Fichier profile sélectionné:', file.name, file.size, 'bytes');

            // Validation
            if (!this.validateImageFile(file)) {
                e.target.value = ''; // Reset input
                return;
            }

            // Aperçu immédiat
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#profile-preview').html(`
                    <img src="${e.target.result}" 
                        class="img-fluid rounded-circle border"
                        style="width: 120px; height: 120px; object-fit: cover;">
                `);
            };
            reader.readAsDataURL(file);

            // Marquer comme modifié
            this.isDirty = true;
            this.updateSaveButton();
        },


        // === VALIDATION ===

        validateEmail: function ($field) {
            const email = $field.val().trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            $field.removeClass('is-valid is-invalid');

            if (email && emailRegex.test(email)) {
                $field.addClass('is-valid');
            } else if (email) {
                $field.addClass('is-invalid');
            }
        },

        validateUrl: function ($field) {
            const url = $field.val().trim();

            $field.removeClass('is-valid is-invalid');

            if (url) {
                try {
                    new URL(url);
                    $field.addClass('is-valid');
                } catch {
                    $field.addClass('is-invalid');
                }
            }
        },

        // === ANIMATIONS BOUTON (NOUVEAU) ===

        animateSaveButton: function (state) {
            let $btn = this.$saveBtn;

            // 🔥 FIX: Si bouton pas trouvé, essayer de le retrouver
            if ($btn.length === 0) {
                console.warn('⚠️ Bouton pas trouvé, tentative de recherche...');
                $btn = this.findSaveButton();
                this.$saveBtn = $btn; // Sauvegarder pour la prochaine fois
            }

            if ($btn.length === 0) {
                console.error('❌ Impossible de trouver le bouton de sauvegarde !');
                return;
            }

            const $icon = $btn.find('i');
            const $text = $btn.find('.btn-text');

            console.log('🎬 animateSaveButton appelé:', state);
            console.log('🎬 Bouton trouvé:', $btn.length);
            console.log('🎬 Icône trouvée:', $icon.length);
            console.log('🎬 Texte trouvé:', $text.length);

            // Reset classes
            $btn.removeClass('btn-loading btn-success btn-error');

            switch (state) {
                case 'loading':
                    $btn.addClass('btn-loading').prop('disabled', true);
                    $icon.removeClass().addClass('fas fa-spinner fa-spin me-2');
                    if ($text.length) $text.text('Enregistrement...');
                    console.log('🔄 Animation: loading appliquée');
                    break;

                case 'success':
                    $btn.addClass('btn-success').prop('disabled', false);
                    $icon.removeClass().addClass('fas fa-check me-2');
                    if ($text.length) $text.text('Enregistré !');

                    setTimeout(() => {
                        this.animateSaveButton('normal');
                    }, 2000);

                    console.log('✅ Animation: success appliquée');
                    break;

                case 'error':
                    $btn.addClass('btn-error').prop('disabled', false);
                    $icon.removeClass().addClass('fas fa-exclamation-triangle me-2');
                    if ($text.length) $text.text('Erreur !');

                    setTimeout(() => {
                        this.animateSaveButton('normal');
                    }, 3000);

                    console.log('❌ Animation: error appliquée');
                    break;

                default: // normal
                    $btn.prop('disabled', this.isDirty ? false : true);
                    $icon.removeClass().addClass('fas fa-save me-2');
                    if ($text.length) {
                        $text.text(this.isDirty ? 'Enregistrer' : 'À jour');
                    }
                    console.log('🔄 Animation: normal appliquée');
                    break;
            }

            console.log('🎬 Classes finales du bouton:', $btn.attr('class'));
        },

        // === SAUVEGARDE ===

        saveVCard: function () {
            console.log('💾 saveVCard() appelée');

            if (this.isSaving) {
                console.log('⏳ Sauvegarde déjà en cours...');
                return;
            }

            this.isSaving = true;
            this.animateSaveButton('loading');

            const self = this;
            const config = this.getConfig();

            if (!config) {
                console.error('❌ Configuration manquante');
                this.animateSaveButton('error');
                this.isSaving = false;
                return;
            }

            const formData = this.buildFormData();

            // Ajouter les paramètres AJAX
            formData.append('action', 'nfc_save_vcard');
            formData.append('nonce', config.nonce);
            formData.append('vcard_id', config.vcard_id);

            $.ajax({
                url: config.ajax_url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log('✅ Réponse reçue:', response);

                    if (response.success) {
                        self.animateSaveButton('success');
                        self.isDirty = false;

                        // 🔥 NOUVEAU : Nettoyer les suppressions après succès
                        if (self.pendingDeletions && self.pendingDeletions.length > 0) {
                            console.log('🗑️ Suppressions appliquées:', self.pendingDeletions);
                            self.pendingDeletions = []; // Reset après succès
                        }

                        // Traiter les images uploadées et mettre à jour les aperçus
                        if (response.data && response.data.images) {
                            self.handleImageUploadSuccess(response.data.images);
                        }

                        self.showBanner('✅ vCard mise à jour avec succès !', 'success');
                    } else {
                        self.animateSaveButton('error');
                        let errorMessage = 'Erreur inconnue';
                        if (response.data && response.data.message) {
                            errorMessage = response.data.message;
                        }
                        self.showBanner('❌ Erreur : ' + errorMessage, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('❌ Erreur AJAX:', { xhr, status, error });
                    self.animateSaveButton('error');
                    self.showBanner('❌ Erreur de connexion : ' + error, 'error');
                },
                complete: function () {
                    self.isSaving = false;
                }
            });
        },


        updateImagePreviews: function (images) {
            if (images.profile_picture && images.profile_picture.success) {
                console.log('✅ Mise à jour aperçu profile:', images.profile_picture.url);
                $('#profile-preview').html(`
            <img src="${images.profile_picture.url}" 
                 class="img-fluid rounded-circle border"
                 style="width: 120px; height: 120px; object-fit: cover;">
        `);
            }

            if (images.cover_image && images.cover_image.success) {
                console.log('✅ Mise à jour aperçu cover:', images.cover_image.url);
                $('#cover-preview').html(`
            <img src="${images.cover_image.url}" 
                 class="img-fluid rounded border" 
                 style="width: 100%; max-height: 120px; object-fit: cover;">
        `);
            }
        },

        handleImageUploadSuccess: function (images) {
            console.log('📸 Traitement succès upload images:', images);

            if (images.profile_picture && images.profile_picture.success) {
                const profileUrl = images.profile_picture.url;
                console.log('✅ Profile picture uploadée:', profileUrl);

                // Mettre à jour l'aperçu avec l'URL finale du serveur
                $('#profile-preview').html(`
            <img src="${profileUrl}" 
                 class="img-fluid rounded-circle border"
                 style="width: 120px; height: 120px; object-fit: cover;"
                 alt="Photo de profil">
        `);

                // Reset l'input file
                $('#profile_image_input').val('');
            }

            if (images.cover_image && images.cover_image.success) {
                const coverUrl = images.cover_image.url;
                console.log('✅ Cover image uploadée:', coverUrl);

                // Mettre à jour l'aperçu
                $('#cover-preview').html(`
            <img src="${coverUrl}" 
                 class="img-fluid rounded border" 
                 style="width: 100%; max-height: 120px; object-fit: cover;"
                 alt="Image de couverture">
        `);

                // Reset l'input file
                $('#cover_image_input').val('');
            }
        },


        // En attendant, ajouter le FormData pour les images :
        buildFormData: function () {
            const formData = new FormData();

            // Ajouter tous les champs texte
            const textData = this.getFormData();
            for (let [key, value] of Object.entries(textData)) {
                formData.append(key, value);
            }

            // 🔥 GESTION DES SUPPRESSIONS
            if (this.pendingDeletions && this.pendingDeletions.length > 0) {
                console.log('🗑️ Suppressions en attente:', this.pendingDeletions);

                if (this.pendingDeletions.includes('profile_picture')) {
                    formData.append('delete_profile_picture', 'true');
                    console.log('🗑️ Suppression profile_picture marquée');
                }

                if (this.pendingDeletions.includes('cover_image')) {
                    formData.append('delete_cover_image', 'true');
                    console.log('🗑️ Suppression cover_image marquée');
                }
            }

            // Ajouter les nouvelles images si présentes
            const profileInput = document.getElementById('profile_image_input');
            const coverInput = document.getElementById('cover_image_input');

            // Profile image (seulement si pas marquée pour suppression)
            if (profileInput && profileInput.files.length > 0) {
                if (!this.pendingDeletions || !this.pendingDeletions.includes('profile_picture')) {
                    formData.append('profile_picture', profileInput.files[0]);
                    console.log('📤 Profile image ajoutée:', profileInput.files[0].name);
                } else {
                    console.log('⚠️ Profile image ignorée (marquée pour suppression)');
                }
            }

            // Cover image (seulement si pas marquée pour suppression)
            if (coverInput && coverInput.files.length > 0) {
                if (!this.pendingDeletions || !this.pendingDeletions.includes('cover_image')) {
                    formData.append('cover_image', coverInput.files[0]);
                    console.log('📤 Cover image ajoutée:', coverInput.files[0].name);
                } else {
                    console.log('⚠️ Cover image ignorée (marquée pour suppression)');
                }
            }

            return formData;
        },


        // Récupérer les données du formulaire
        getFormData: function () {
            const data = {};

            $.each(this.fields, function (key, $field) {
                if ($field && $field.length > 0) {
                    data[key] = $field.val().trim();
                }
            });

            const redirectMode = this.$redirectModeRadios.filter(':checked').val() || 'vcard';
            data.redirect_mode = redirectMode;

            return data;
        },

        // Obtenir la configuration
        getConfig: function () {
            return window.nfcDashboardConfig || window.vcardEditorConfig || null;
        },

        // Valider le formulaire
        validateForm: function (data) {
            // Validation basique
            if (!data.firstname || !data.lastname) {
                this.showBanner('Nom et prénom requis', 'error');
                return false;
            }

            if (data.redirect_mode === 'custom' && !data.custom_url) {
                this.showBanner('URL requise en mode redirection', 'error');
                return false;
            }

            return true;
        },

        // Mise à jour du bouton
        updateSaveButton: function () {
            if (!this.isSaving) {
                this.animateSaveButton('normal');
            }
        },

        // Message simple
        showMessage: function (message, type) {
            console.log(`📢 ${type.toUpperCase()}: ${message}`);
            alert(message); // Simple pour l'instant
        },

        showBanner: function (message, type = 'info', duration = 5000) {
            console.log('🎬 showBanner appelé:', message, type);

            // Créer ou réutiliser le container de bandeau
            let banner = document.getElementById('nfc-banner');
            if (!banner) {
                banner = document.createElement('div');
                banner.id = 'nfc-banner';

                // 🔥 FIX: Styles inline forcés pour éviter les conflits CSS
                banner.style.cssText = `
            position: fixed !important;
            top: 80px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            z-index: 99999 !important;
            min-width: 350px !important;
            max-width: 600px !important;
            padding: 1rem 1.5rem !important;
            border-radius: 8px !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2) !important;
            transition: all 0.3s ease !important;
            opacity: 0 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-size: 14px !important;
            line-height: 1.4 !important;
        `;
                document.body.appendChild(banner);
                console.log('🎬 Bandeau créé et ajouté au DOM');
            }

            // Configuration par type avec styles forcés
            const configs = {
                'success': {
                    bg: '#d4edda',
                    border: '#c3e6cb',
                    color: '#155724',
                    icon: '✅'
                },
                'error': {
                    bg: '#f8d7da',
                    border: '#f5c6cb',
                    color: '#721c24',
                    icon: '❌'
                },
                'warning': {
                    bg: '#fff3cd',
                    border: '#ffeaa7',
                    color: '#856404',
                    icon: '⚠️'
                },
                'info': {
                    bg: '#d1ecf1',
                    border: '#bee5eb',
                    color: '#0c5460',
                    icon: 'ℹ️'
                }
            };

            const config = configs[type] || configs['info'];

            // 🔥 FIX: Appliquer styles avec !important
            banner.style.background = config.bg + ' !important';
            banner.style.border = `2px solid ${config.border} !important`;
            banner.style.color = config.color + ' !important';

            banner.innerHTML = `
        <div style="display: flex !important; align-items: center !important; gap: 12px !important;">
            <span style="font-size: 18px !important; flex-shrink: 0 !important;">${config.icon}</span>
            <span style="font-weight: 500 !important; flex: 1 !important;">${message}</span>
            <button onclick="this.parentElement.parentElement.style.opacity='0'; setTimeout(() => this.parentElement.parentElement.remove(), 300);" 
                    style="margin-left: auto !important; background: none !important; border: none !important; font-size: 18px !important; cursor: pointer !important; padding: 4px !important; line-height: 1 !important; color: ${config.color} !important;">×</button>
        </div>
    `;

            console.log('🎬 Bandeau HTML mis à jour');

            // Animation d'entrée avec délai pour laisser le DOM se mettre à jour
            setTimeout(() => {
                banner.style.opacity = '1';
                banner.style.transform = 'translateX(-50%) translateY(0)';
                console.log('🎬 Animation entrée bandeau');
            }, 100);

            // Auto-suppression
            if (duration > 0) {
                setTimeout(() => {
                    banner.style.opacity = '0';
                    banner.style.transform = 'translateX(-50%) translateY(-20px)';
                    setTimeout(() => {
                        if (banner.parentNode) {
                            banner.remove();
                            console.log('🎬 Bandeau supprimé');
                        }
                    }, 300);
                }, duration);
            }

            console.log(`📢 Bandeau ${type} configuré et affiché`);
        },

    };

    // === UTILITAIRES ===

    window.VCardEditUtils = {

        loadQuickStats: function () {
            if (quickStatsLoaded) return;

            const config = window.nfcDashboardConfig || window.vcardEditorConfig || {};

            if (!config.vcard_id) {
                console.warn('⚠️ Pas de vCard ID pour charger les stats');
                return;
            }

            console.log('📊 Chargement stats rapides pour vCard', config.vcard_id);

            const statsUrl = `${config.api_url}statistics/${config.vcard_id}`;

            fetch(statsUrl)
                .then(response => {
                    console.log('📈 Response stats status:', response.status);
                    return response.json();
                })
                .then(response => {
                    console.log('📈 Stats reçues:', response);

                    // 🔥 FIX: Traiter la vraie structure de réponse
                    let viewsCount = 0;

                    if (response.success && response.data) {
                        if (Array.isArray(response.data)) {
                            viewsCount = response.data.length;
                            console.log('📊 Calcul vues depuis response.data:', viewsCount);
                        } else if (typeof response.data === 'object' && response.data.length) {
                            viewsCount = response.data.length;
                        }
                    } else if (Array.isArray(response)) {
                        // Fallback si pas de wrapper
                        viewsCount = response.length;
                    }

                    console.log('📊 Views count final:', viewsCount);

                    // 🔥 FIX: Mettre à jour avec le bon nombre
                    const viewsEl = document.getElementById('quick-views');
                    const contactsEl = document.getElementById('quick-contacts');

                    if (viewsEl) {
                        viewsEl.textContent = viewsCount;
                        console.log('✅ Stats vues mises à jour:', viewsCount);
                    } else {
                        console.warn('⚠️ Élément #quick-views introuvable');
                        // 🔥 DEBUG: Chercher les éléments disponibles
                        console.log('🔍 Éléments avec "views" dans l\'ID:', document.querySelectorAll('[id*="views"]'));
                    }

                    if (contactsEl) {
                        contactsEl.textContent = '0'; // À implémenter plus tard
                    } else {
                        console.warn('⚠️ Élément #quick-contacts introuvable');
                        console.log('🔍 Éléments avec "contacts" dans l\'ID:', document.querySelectorAll('[id*="contacts"]'));
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur chargement stats:', error);
                    const viewsEl = document.getElementById('quick-views');
                    const contactsEl = document.getElementById('quick-contacts');

                    if (viewsEl) viewsEl.textContent = 'Erreur';
                    if (contactsEl) contactsEl.textContent = 'Erreur';
                });

            quickStatsLoaded = true;
        },

        handleCoverImageUpload: function (e) {
            const file = e.target.files[0];
            if (!file) return;

            console.log('🖼️ Fichier cover sélectionné:', file.name, file.size, 'bytes');

            // Validation
            if (!this.validateImageFile(file)) {
                e.target.value = ''; // Reset input
                return;
            }

            // Aperçu immédiat
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#cover-preview').html(`
                    <img src="${e.target.result}" 
                        class="img-fluid rounded border" 
                        style="width: 100%; max-height: 120px; object-fit: cover;">
                `);
            };
            reader.readAsDataURL(file);

            // Marquer comme modifié
            this.isDirty = true;
            this.updateSaveButton();
        },
    };

    // === FONCTIONS GLOBALES ===

    window.resetForm = function () {
        if (window.VCardEditor && window.VCardEditor.isDirty) {
            if (!confirm('Des modifications seront perdues. Continuer ?')) return;
        }
        window.location.reload();
    };

    window.removeProfileImage = function () {
        if (!confirm('Supprimer la photo de profil ?')) return;

        console.log('🗑️ Suppression photo de profil demandée');

        // Calculer les initiales depuis les champs du formulaire
        const firstname = $('input[name="firstname"]').val() || 'U';
        const lastname = $('input[name="lastname"]').val() || 'N';
        const initials = firstname.charAt(0).toUpperCase() + lastname.charAt(0).toUpperCase();

        // Remettre l'état initial (initiales)
        $('#profile-preview').html(`
        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center border"
             style="width: 120px; height: 120px; margin: 0 auto;">
            <span class="text-primary fw-bold fs-2">${initials}</span>
        </div>
    `);

        // Reset l'input
        $('#profile_image_input').val('');

        // 🔥 CORRECTION : Marquer la suppression
        if (window.VCardEditor) {
            if (!window.VCardEditor.pendingDeletions) {
                window.VCardEditor.pendingDeletions = [];
            }

            // Éviter les doublons
            if (!window.VCardEditor.pendingDeletions.includes('profile_picture')) {
                window.VCardEditor.pendingDeletions.push('profile_picture');
            }

            window.VCardEditor.isDirty = true;
            window.VCardEditor.updateSaveButton();

            console.log('🗑️ Profile picture marquée pour suppression');
        }
    };

    window.removeCoverImage = function () {
        if (!confirm('Supprimer l\'image de couverture ?')) return;

        console.log('🗑️ Suppression image de couverture demandée');

        // Remettre l'état initial (placeholder)
        $('#cover-preview').html(`
        <div class="bg-light rounded border d-flex align-items-center justify-content-center"
             style="width: 100%; height: 120px;">
            <div class="text-center">
                <i class="fas fa-image fa-2x text-muted mb-2"></i>
                <div class="text-muted small">Aucune image de couverture</div>
            </div>
        </div>
    `);

        // Reset l'input
        $('#cover_image_input').val('');

        // 🔥 CORRECTION : Marquer la suppression (c'était manquant dans ton code)
        if (window.VCardEditor) {
            if (!window.VCardEditor.pendingDeletions) {
                window.VCardEditor.pendingDeletions = [];
            }

            // Éviter les doublons
            if (!window.VCardEditor.pendingDeletions.includes('cover_image')) {
                window.VCardEditor.pendingDeletions.push('cover_image');
            }

            window.VCardEditor.isDirty = true;
            window.VCardEditor.updateSaveButton();

            console.log('🗑️ Cover image marquée pour suppression');
        }
    };


    // === INITIALISATION ===

    $(document).ready(function () {
        console.log('📝 vCard Edit - Initialisation');

        if (window.VCardEditor) {
            window.VCardEditor.init();
        }

        console.log('✅ vCard Edit - Prêt');
    });

    // Exposer pour test manuel
    window.testVCardSave = function () {
        if (window.VCardEditor) {
            window.VCardEditor.testSave();
        } else {
            console.error('VCardEditor non disponible');
        }
    };

    console.log('🧪 Test manuel disponible: testVCardSave() dans la console');

    window.debugImageEvents = function () {
        console.log('🧪 DEBUG - Test des événements images');

        // Vérifier les éléments
        console.log('Éléments trouvés:');
        console.log('- Profile input:', $('#profile_image_input').length);
        console.log('- Cover input:', $('#cover_image_input').length);
        console.log('- Profile preview:', $('#profile-preview').length);
        console.log('- Cover preview:', $('#cover-preview').length);

        // Vérifier VCardEditor
        console.log('VCardEditor disponible:', typeof window.VCardEditor);
        if (window.VCardEditor) {
            console.log('- isDirty:', window.VCardEditor.isDirty);
            console.log('- pendingDeletions:', window.VCardEditor.pendingDeletions);
        }

        // Test manuel des fonctions
        console.log('Fonctions globales:');
        console.log('- removeProfileImage:', typeof window.removeProfileImage);
        console.log('- removeCoverImage:', typeof window.removeCoverImage);
    };

    // === FONCTION GLOBALE testCustomUrl() ===
    window.testCustomUrl = function () {
        console.log('🧪 Test Custom URL');

        const urlInput = document.getElementById('custom_url_input');
        if (!urlInput) {
            alert('❌ Champ URL non trouvé');
            return;
        }

        const url = urlInput.value.trim();

        if (!url) {
            alert('⚠️ Veuillez saisir une URL à tester');
            urlInput.focus();
            return;
        }

        // Validation de l'URL
        try {
            new URL(url);
        } catch (e) {
            alert('❌ URL invalide. Format requis : https://exemple.com');
            urlInput.focus();
            return;
        }

        // 🔥 MÉTHODE SIMPLE : Créer un lien et le cliquer
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';

        // Simuler un clic utilisateur
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        console.log('✅ URL ouverte:', url);

        // Message de confirmation après un court délai
        setTimeout(function () {
            console.log('🔗 Si l\'onglet ne s\'est pas ouvert, vérifiez que les pop-ups ne sont pas bloqués');
        }, 500);
    };

    window.copyToClipboard = function (url) {
        console.log('📋 Copie dans le presse-papier:', url);

        if (!url) {
            alert('❌ Aucune URL à copier');
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(function () {
                console.log('✅ URL copiée');
                alert('📋 URL copiée dans le presse-papier !');
            }).catch(function (err) {
                console.error('❌ Erreur copie:', err);
                alert('📋 Copiez cette URL manuellement :\n\n' + url);
            });
        } else {
            // Version fallback
            const textArea = document.createElement('textarea');
            textArea.value = url;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.select();

            try {
                document.execCommand('copy');
                alert('📋 URL copiée dans le presse-papier !');
            } catch (err) {
                alert('📋 Copiez cette URL manuellement :\n\n' + url);
            }

            document.body.removeChild(textArea);
        }
    };

    window.shareVCard = function() {
        console.log('📤 Partage vCard demandé');
        
        // Récupérer l'URL depuis plusieurs sources
        let vCardUrl = null;
        
        const publicUrlInput = document.getElementById('vcard-public-url');
        if (publicUrlInput && publicUrlInput.value) {
            vCardUrl = publicUrlInput.value;
        }
        
        if (!vCardUrl && window.overviewConfig && window.overviewConfig.public_url) {
            vCardUrl = window.overviewConfig.public_url;
        }
        
        if (!vCardUrl) {
            alert('❌ Impossible de déterminer l\'URL de votre vCard');
            return;
        }
        
        console.log('🔗 URL pour partage:', vCardUrl);
        
        // API Web Share si disponible
        if (navigator.share) {
            navigator.share({
                title: 'Ma vCard NFC',
                text: 'Découvrez mes informations de contact',
                url: vCardUrl
            }).catch((err) => {
                console.error('Erreur partage:', err);
                // Fallback vers copie
                copyUrlForShare(vCardUrl);
            });
        } else {
            // Fallback pour desktop
            copyUrlForShare(vCardUrl);
        }
    };

    function copyUrlForShare(url) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(() => {
                alert('✅ Lien vCard copié dans le presse-papier !');
            }).catch(() => {
                alert('📋 Copiez ce lien manuellement :\n\n' + url);
            });
        } else {
            alert('📋 Copiez ce lien pour partager votre vCard :\n\n' + url);
        }
    }


})(jQuery);