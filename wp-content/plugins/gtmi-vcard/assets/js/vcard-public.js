/**
 * JavaScript pour vCard publique - CORRIGÉ pour correspondre au template existant
 * 
 * Fichier: assets/js/vcard-public.js
 */

(function ($) {
    'use strict';

    // Objet principal VCardPublic
    window.VCardPublic = {

        // Configuration (injectée par wp_localize_script)
        config: {},

        // Modal Bootstrap
        shareModal: null,

        /**
 * Initialisation
 */
        init: function () {
            console.log('🚀 VCardPublic initialisé');

            // Récupérer la configuration depuis PHP - VERSION CORRIGÉE
            this.loadConfig();

            // Initialiser le modal Bootstrap avec le bon ID du template
            this.initModal();

            console.log('✅ VCardPublic prêt');
        },

        /**
         * Charger la configuration depuis window.vCardConfig
         */
        loadConfig: function () {
            if (typeof vCardConfig !== 'undefined') {
                this.config = vCardConfig;
                console.log('✅ Configuration vCard chargée:', this.config);
            } else if (typeof window.vCardConfig !== 'undefined') {
                this.config = window.vCardConfig;
                console.log('✅ Configuration vCard chargée depuis window:', this.config);
            } else {
                console.warn('⚠️ Configuration vCard non trouvée, utilisation des valeurs par défaut');
                this.config = {
                    ajaxUrl: '/wp-admin/admin-ajax.php',
                    nonce: '',
                    id: 0,
                    name: 'Contact NFC'
                };
            }
        },

        /**
         * Initialiser le modal - CORRIGÉ pour ton template
         */
        initModal: function () {
            // TON TEMPLATE utilise l'ID "shareModal"
            const modalElement = document.getElementById('shareModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                this.shareModal = new bootstrap.Modal(modalElement);
                console.log('✅ Modal Bootstrap initialisé (ID: shareModal)');

                // TON TEMPLATE utilise l'ID "shareForm"
                const form = document.getElementById('shareContactForm');
                if (form) {
                    form.addEventListener('submit', (e) => this.handleShareSubmit(e));
                    console.log('✅ Formulaire de partage bindé (ID: shareContactForm)');
                } else {
                    console.error('❌ Formulaire shareForm non trouvé');
                }
            } else {
                console.error('❌ Modal shareModal non trouvé ou Bootstrap manquant');
            }
        },

        /**
         * Ouvrir le modal de partage - COMPATIBILITÉ AVEC TON TEMPLATE
         */
        openShareModal: function () {
            console.log('📝 Ouverture modal partage');

            if (this.shareModal) {
                this.shareModal.show();
                this.trackAction('share_modal_open');
            } else {
                console.error('❌ Modal de partage non disponible');
                this.showNotification('❌ Erreur lors de l\'ouverture du formulaire', 'error');
            }
        },

        /**
         * BOUTON 1: Télécharger la vCard (.vcf)
         */
        downloadVCard: function () {
            console.log('📇 Début téléchargement vCard');

            try {
                // Générer le contenu vCard
                const vcardContent = this.generateVCardContent();
                console.log('📝 Contenu vCard généré:', vcardContent);

                // Créer et télécharger le fichier
                const blob = new Blob([vcardContent], { type: 'text/vcard;charset=utf-8' });
                const url = window.URL.createObjectURL(blob);

                const a = document.createElement('a');
                a.href = url;
                a.download = this.sanitizeFilename(this.config.name || 'contact') + '.vcf';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                this.showNotification('📇 Contact ajouté ! Fichier téléchargé.', 'success');
                this.trackAction('vcard_download');

            } catch (error) {
                console.error('❌ Erreur génération vCard:', error);
                this.showNotification('❌ Erreur lors du téléchargement', 'error');
            }
        },

        /**
         * Alias pour compatibilité avec ton template
         */
        addToContacts: function () {
            this.downloadVCard();
        },

        /**
         * Générer le contenu du fichier vCard - CORRIGÉ pour ta config
         */
        generateVCardContent: function () {
            const config = this.config;

            // Extraire prénom/nom depuis le nom complet si nécessaire
            const nameParts = (config.name || '').split(' ');
            const firstName = nameParts[0] || '';
            const lastName = nameParts.slice(1).join(' ') || '';

            // Construire les lignes vCard selon le standard
            const vcardLines = [
                'BEGIN:VCARD',
                'VERSION:3.0',
                `FN:${config.name || ''}`,
                `N:${lastName};${firstName};;;`,
                config.company ? `ORG:${config.company}` : '',
                config.title ? `TITLE:${config.title}` : '',
                config.email ? `EMAIL:${config.email}` : '',
                config.phone ? `TEL:${config.phone}` : '',
                config.website ? `URL:${config.website}` : '',
                config.address ? `ADR:;;${config.address};;;;` : '',
                config.description ? `NOTE:${config.description}` : '',
                'END:VCARD'
            ];

            // Filtrer les lignes vides et joindre avec CRLF
            return vcardLines
                .filter(line => line && line.trim() !== '' && !line.endsWith(':'))
                .join('\r\n');
        },

        /**
         * BOUTON 2: Ouvrir le modal de partage
         */
        openShareModal: function () {
            console.log('📝 Ouverture modal partage');

            if (this.shareModal) {
                this.shareModal.show();
                this.trackAction('share_modal_open');
                console.log('✅ Modal affiché');
            } else {
                console.error('❌ Modal de partage non disponible');
                this.showNotification('❌ Erreur lors de l\'ouverture du formulaire', 'error');
            }
        },

        /**
  * Gérer la soumission du formulaire de partage - VERSION CORRIGÉE
  */
        handleShareSubmit: function (event) {
            event.preventDefault();

            const form = event.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;

            // Validation côté client
            if (!this.validateShareForm(form)) {
                return;
            }

            // État de chargement
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Envoi en cours...';
            submitBtn.disabled = true;

            // ✅ MÊME FORMAT que le dashboard
            const contactData = {
                firstname: form.querySelector('[name="firstName"]').value.trim(),
                lastname: form.querySelector('[name="lastName"]').value.trim(),
                email: form.querySelector('[name="email"]').value.trim(),
                mobile: form.querySelector('[name="phone"]').value.trim(),
                society: form.querySelector('[name="company"]').value.trim(),
                linked_virtual_card: this.config.id
            };

            // ✅ MÊME API que le dashboard
            const apiUrl = window.location.origin + '/wp-json/gtmi_vcard/v1/lead';

            // ✅ MÊME méthode que le dashboard
            fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(contactData)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.showNotification('✅ Merci ! Vos coordonnées ont été partagées.', 'success');
                        form.reset();
                        if (this.shareModal) {
                            this.shareModal.hide();
                        }
                    } else {
                        this.showNotification('❌ Erreur lors du partage', 'error');
                    }
                })
                .catch(error => {
                    this.showNotification('❌ Erreur de connexion. Veuillez réessayer.', 'error');
                })
                .finally(() => {
                    submitBtn.innerHTML = originalHTML;
                    submitBtn.disabled = false;
                });
        },

        /**
         * Valider le formulaire - CORRIGÉ pour tes IDs
         */
        validateShareForm: function (form) {
            const requiredFields = ['firstName', 'lastName', 'phone', 'email'];
            let isValid = true;

            // Nettoyer les classes d'erreur précédentes
            requiredFields.forEach(fieldName => {
                const input = form.querySelector(`[name="${fieldName}"]`);
                if (input) input.classList.remove('is-invalid');
            });

            // Valider les champs obligatoires
            requiredFields.forEach(fieldName => {
                const input = form.querySelector(`[name="${fieldName}"]`);
                if (!input || !input.value.trim()) {
                    if (input) input.classList.add('is-invalid');
                    isValid = false;
                    console.warn(`❌ Champ manquant: ${fieldName}`);
                }
            });

            // Validation email spécifique
            const emailInput = form.querySelector('[name="email"]');
            if (emailInput && emailInput.value.trim() && !this.isValidEmail(emailInput.value.trim())) {
                emailInput.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                this.showNotification('❌ Veuillez remplir tous les champs obligatoires', 'error');
            }

            return isValid;
        },

        /**
         * Valider une adresse email
         */
        isValidEmail: function (email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        /**
         * Nettoyer le nom de fichier
         */
        sanitizeFilename: function (filename) {
            return filename
                .replace(/[^a-z0-9]/gi, '_')
                .replace(/_+/g, '_')
                .replace(/^_|_$/g, '')
                .toLowerCase();
        },

        /**
         * Appeler le téléphone
         */
        callPhone: function () {
            if (this.config.phone) {
                window.location.href = 'tel:' + this.config.phone;
                this.trackAction('phone_call');
            }
        },

        /**
         * Envoyer un email
         */
        sendEmail: function () {
            if (this.config.email) {
                const subject = encodeURIComponent(`Contact depuis votre vCard - ${this.config.name}`);
                window.location.href = `mailto:${this.config.email}?subject=${subject}`;
                this.trackAction('email_send');
            }
        },

        /**
         * Partager la vCard via l'API Web Share ou fallback
         */
        shareVCard: function () {
            console.log('📤 Partage de la vCard demandé');

            // Données à partager
            const shareData = {
                title: `${this.config.name} - Carte de visite numérique`,
                text: `Découvrez ma carte de visite numérique`,
                url: window.location.href
            };

            // Vérifier si l'API Web Share est supportée
            if (navigator.share && navigator.canShare && navigator.canShare(shareData)) {
                console.log('✅ API Web Share disponible');

                navigator.share(shareData)
                    .then(() => {
                        console.log('✅ Partage réussi via API native');
                        this.trackAction('native_share_success');
                        this.showNotification('📤 Merci d\'avoir partagé !', 'success');
                    })
                    .catch((error) => {
                        console.log('❌ Partage annulé ou erreur:', error);

                        // Ne pas afficher d'erreur si l'utilisateur a juste annulé
                        if (error.name !== 'AbortError') {
                            console.error('Erreur lors du partage:', error);
                            this.fallbackShare();
                        }
                    });

            } else if (navigator.share) {
                // API Web Share disponible mais données non supportées
                console.log('⚠️ API Web Share disponible mais données non supportées');

                // Essayer avec moins de données
                const simpleShareData = {
                    url: window.location.href
                };

                navigator.share(simpleShareData)
                    .then(() => {
                        console.log('✅ Partage simple réussi');
                        this.trackAction('simple_share_success');
                        this.showNotification('📤 Merci d\'avoir partagé !', 'success');
                    })
                    .catch((error) => {
                        if (error.name !== 'AbortError') {
                            console.log('❌ Partage simple échoué, fallback');
                            this.fallbackShare();
                        }
                    });

            } else {
                // API Web Share non supportée, utiliser le fallback
                console.log('❌ API Web Share non supportée, utilisation du fallback');
                this.fallbackShare();
            }
        },

        /**
         * Fallback pour le partage
         */
        fallbackShare: function () {
            console.log('🔄 Utilisation du fallback de partage');

            // Détecter si on est sur mobile pour proposer des options appropriées
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

            if (isMobile) {
                // Sur mobile, proposer les apps de partage courantes
                this.showMobileShareOptions();
            } else {
                // Sur desktop, copier dans le presse-papier
                this.copyToClipboard();
            }
        },

        showMobileShareOptions: function () {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent(`Découvrez ma carte de visite numérique`);

            const shareOptions = [
                {
                    name: 'WhatsApp',
                    url: `https://wa.me/?text=${text}%20${url}`,
                    icon: '📱'
                },
                {
                    name: 'SMS',
                    url: `sms:?body=${text}%20${url}`,
                    icon: '💬'
                },
                {
                    name: 'Email',
                    url: `mailto:?subject=${encodeURIComponent(this.config.name + ' - vCard')}&body=${text}%20${url}`,
                    icon: '📧'
                },
                {
                    name: 'Copier le lien',
                    action: () => this.copyToClipboard(),
                    icon: '📋'
                }
            ];

            // Créer un modal de choix simple
            this.showShareOptionsModal(shareOptions);
        },

        showShareOptionsModal: function (options) {
            // Créer un modal simple avec les options
            const modal = document.createElement('div');
            modal.className = 'share-options-modal';
            modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    `;

            const content = document.createElement('div');
            content.className = 'share-options-content';
            content.style.cssText = `
        background: white;
        border-radius: 12px;
        padding: 20px;
        max-width: 300px;
        width: 100%;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    `;

            let html = '<h3 style="margin-top: 0; text-align: center;">Partager ma vCard</h3>';

            options.forEach(option => {
                if (option.url) {
                    html += `
                <a href="${option.url}" target="_blank" style="
                    display: block;
                    padding: 12px;
                    margin: 8px 0;
                    text-decoration: none;
                    color: #333;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    text-align: center;
                    transition: all 0.3s ease;
                " onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor='white'">
                    ${option.icon} ${option.name}
                </a>
            `;
                } else if (option.action) {
                    html += `
                <button onclick="window.VCardPublic.executeShareAction('${option.name}')" style="
                    display: block;
                    width: 100%;
                    padding: 12px;
                    margin: 8px 0;
                    background: white;
                    color: #333;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    text-align: center;
                    cursor: pointer;
                    transition: all 0.3s ease;
                " onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor='white'">
                    ${option.icon} ${option.name}
                </button>
            `;
                }
            });

            html += `
        <button onclick="this.closest('.share-options-modal').remove()" style="
            display: block;
            width: 100%;
            padding: 12px;
            margin: 15px 0 0 0;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        ">
            Annuler
        </button>
    `;

            content.innerHTML = html;
            modal.appendChild(content);
            document.body.appendChild(modal);

            // Fermer en cliquant à l'extérieur
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });

            // Stocker les actions pour les boutons
            this._shareActions = options.reduce((acc, option) => {
                if (option.action) {
                    acc[option.name] = option.action;
                }
                return acc;
            }, {});
        },

        executeShareAction: function (actionName) {
            if (this._shareActions && this._shareActions[actionName]) {
                this._shareActions[actionName]();
                // Fermer le modal
                const modal = document.querySelector('.share-options-modal');
                if (modal) modal.remove();
            }
        },

        copyToClipboard: function () {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href)
                    .then(() => {
                        this.showNotification('📋 Lien copié dans le presse-papier !', 'success');
                        this.trackAction('link_copied');
                    })
                    .catch(() => {
                        this.manualCopyFallback();
                    });
            } else {
                this.manualCopyFallback();
            }
        },




        /**
         * Fallback manuel pour la copie
         */
        manualCopyFallback: function () {
            const textArea = document.createElement('textarea');
            textArea.value = window.location.href;
            document.body.appendChild(textArea);
            textArea.select();

            try {
                document.execCommand('copy');
                this.showNotification('📋 Lien copié !', 'success');
                this.trackAction('manual_copy');
            } catch (err) {
                this.showNotification('📋 Lien à copier: ' + window.location.href, 'info');
            }

            document.body.removeChild(textArea);
        },

        /**
         * Prendre un rendez-vous (à développer plus tard)
         */
        bookAppointment: function () {
            console.log('📅 Fonctionnalité de rendez-vous à développer');
            this.showNotification('📅 Fonctionnalité bientôt disponible !', 'info');
        },

        /**
         * Ajouter aux favoris (étoile)
         */
        addToFavorites: function () {
            console.log('⭐ Fonctionnalité favoris à développer');
            this.showNotification('⭐ Fonctionnalité bientôt disponible !', 'info');
        },

        /**
 * Afficher une notification - VERSION AMÉLIORÉE
 */
        showNotification: function (message, type = 'success') {
            console.log(`📢 Notification ${type}:`, message);

            // Chercher d'abord l'élément notification dans ton template
            let notification = document.getElementById('notificationMessage') ||
                document.querySelector('.notification-message') ||
                document.querySelector('.alert');

            if (notification) {
                notification.textContent = message;
                notification.className = `notification-message alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'}`;
                notification.style.display = 'block';
                notification.style.opacity = '1';

                // Masquer après 4 secondes
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 300);
                }, 4000);
            } else {
                // Fallback : créer une notification temporaire
                this.createTempNotification(message, type);
            }
        },

        /**
         * Créer une notification temporaire si aucun élément n'existe
         */
        createTempNotification: function (message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        padding: 12px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        background: ${type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#007bff'};
        transition: all 0.3s ease;
    `;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Animation d'entrée
            setTimeout(() => notification.style.transform = 'translateX(0)', 10);

            // Suppression après 4 secondes
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => document.body.removeChild(notification), 300);
            }, 4000);
        },

        /**
         * Tracker les actions pour analytics
         */
        trackAction: function (action) {
            console.log('📊 Action trackée:', action);

            // Google Analytics si disponible
            if (typeof gtag !== 'undefined') {
                gtag('event', action, {
                    event_category: 'vcard_interaction',
                    event_label: this.config.id
                });
            }

            // Tu peux ajouter d'autres systèmes d'analytics ici
        }
    };

    // Initialisation automatique au chargement du DOM
    $(document).ready(function () {
        // Essayer d'initialiser immédiatement
        if (typeof vCardConfig !== 'undefined' || typeof window.vCardConfig !== 'undefined') {
            VCardPublic.init();
        } else {
            // Retry après 100ms si config pas encore disponible
            console.log('⏳ Configuration vCard pas encore disponible, retry...');
            setTimeout(function () {
                VCardPublic.init();
            }, 100);
        }
    });

})(jQuery);