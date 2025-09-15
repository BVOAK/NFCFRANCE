/**
 * JavaScript pour la gestion des contacts Dashboard NFC - MULTI-PROFIL COMPATIBLE
 * 
 * Fichier: assets/js/dashboard/contacts-manager.js
 * VERSION FIXÉE pour supporter AJAX multi-profil + fallback API REST
 */

(function ($) {
    'use strict';

    /**
     * Objet principal NFCContacts - VERSION MULTI-PROFIL
     */
    window.NFCContacts = {

        // Configuration
        config: typeof nfcContactsConfig !== 'undefined' ? nfcContactsConfig : {},

        // État de l'application
        contacts: [],
        filteredContacts: [],
        selectedContacts: [],
        currentPage: 1,
        itemsPerPage: 25,
        totalPages: 1,
        isLoading: false,
        currentEditId: null,
        currentView: 'table',
        csvData: [],
        currentSort: { field: 'date', direction: 'desc' },

        // 🆕 NOUVEAU: Flag pour savoir si on utilise AJAX ou REST
        useAjax: false,

        // Filtres actifs
        filters: {
            search: '',
            source: '',
            date: '',
            company: '',
            status: '',
            dateFrom: '',
            dateTo: '',
            sortBy: 'date_desc',
            profile: '' // 🆕 NOUVEAU: Filtre par profil vCard
        },

        // Éléments DOM
        elements: {},

        /**
         * Initialisation principale - ADAPTÉE MULTI-PROFIL
         */
        init: function () {
            console.log('📧 NFCContacts - Initialisation MULTI-PROFIL');
            console.log('📧 Configuration reçue:', this.config);

            // 🛑 VÉRIFIER SI ON DOIT EMPÊCHER LE CHARGEMENT AUTO
            if (window.nfcContactsPreventAutoLoad) {
                console.log('🛑 Chargement automatique empêché par leads.php');
                return;
            }

            // VÉRIFIER si on a déjà NFCLeads qui fonctionne
            if (window.NFCLeads && window.NFCLeads.contacts && window.NFCLeads.contacts.length > 0) {
                console.log('🔄 NFCLeads détecté avec ' + window.NFCLeads.contacts.length + ' contacts, on prend le relais');
                this.takeOverFromNFCLeads();
                return;
            }

            // VÉRIFIER LA CONFIGURATION
            if (!this.config || (!this.config.vcard_id && !this.config.user_id)) {
                console.error('❌ Configuration NFCContacts manquante ou incomplète');
                this.showError('Configuration manquante');
                return;
            }

            if (this.isLoading) {
                console.warn('⚠️ NFCContacts déjà en cours de chargement');
                return;
            }

            // 🆕 DÉTECTER LE MODE (AJAX multi-profil ou REST simple)
            this.useAjax = this.config.use_ajax || this.config.is_multi_profile || false;
            console.log('🔧 Mode détecté:', this.useAjax ? 'AJAX Multi-profil' : 'REST API Simple');

            this.cacheElements();
            this.bindEvents();
            this.loadContacts();

            console.log('✅ NFCContacts prêt');
        },



        /**
         * Cache des éléments DOM - INCHANGÉ
         */
        cacheElements: function () {
            this.elements = {
                loading: document.getElementById('contactsLoading'),
                empty: document.getElementById('contactsEmpty'),
                error: document.getElementById('contactsError'),
                content: document.getElementById('contactsContent'),
                tableView: document.getElementById('contactsTableView'),
                gridView: document.getElementById('contactsGridView'),
                tableBody: document.getElementById('contactsTableBody'),
                pagination: document.getElementById('contactsPagination'),
                paginationWrapper: document.getElementById('contactsPaginationWrapper'),
                searchInput: document.getElementById('contactsSearch'),
                selectAll: document.getElementById('selectAll'),
                bulkActions: document.getElementById('bulkActions'),
                contactsCounter: document.getElementById('contactsCounter'),

                // Stats
                totalContactsStat: document.getElementById('totalContactsStat'),
                newContactsStat: document.getElementById('newContactsStat'),
                companiesStat: document.getElementById('companiesStat'),
                qrSourceStat: document.getElementById('qrSourceStat'),

                // Filtres
                sourceFilter: document.getElementById('sourceFilter'),
                dateFilter: document.getElementById('dateFilter'),
                sortFilter: document.getElementById('sortFilter'),
                profileFilter: document.getElementById('profileFilter'), // 🆕 NOUVEAU

                // Views
                tableViewBtn: document.getElementById('tableViewBtn'),
                gridViewBtn: document.getElementById('gridViewBtn')
            };

            // Vérifier les éléments critiques
            const criticalElements = ['loading', 'empty', 'error'];
            for (const elementName of criticalElements) {
                if (!this.elements[elementName]) {
                    console.error(`❌ Élément DOM manquant: ${elementName}`);
                }
            }
        },

        /**
         * Liaison des événements - ÉTENDUE MULTI-PROFIL
         */
        bindEvents: function () {
            // Recherche
            if (this.elements.searchInput) {
                this.elements.searchInput.addEventListener('input', () => {
                    this.filters.search = this.elements.searchInput.value.toLowerCase().trim();
                    this.applyFilters();
                });
            }

            // Filtres
            if (this.elements.sourceFilter) {
                this.elements.sourceFilter.addEventListener('change', () => {
                    this.filters.source = this.elements.sourceFilter.value;
                    this.applyFilters();
                });
            }

            if (this.elements.sortFilter) {
                this.elements.sortFilter.addEventListener('change', () => {
                    this.filters.sortBy = this.elements.sortFilter.value;
                    this.applyFilters();
                });
            }

            // 🆕 NOUVEAU: Filtre par profil
            if (this.elements.profileFilter) {
                this.elements.profileFilter.addEventListener('change', () => {
                    this.filters.profile = this.elements.profileFilter.value;
                    this.applyFilters();
                });
            }

            // Vue
            const viewButtons = document.querySelectorAll('input[name="viewMode"]');
            viewButtons.forEach(btn => {
                btn.addEventListener('change', () => {
                    this.currentView = btn.id === 'gridViewBtn' ? 'grid' : 'table';
                    this.renderContacts();
                });
            });

            // Sélection
            if (this.elements.selectAll) {
                this.elements.selectAll.addEventListener('change', (e) => {
                    this.toggleAllContacts(e.target.checked);
                });
            }
        },

        /**
         * Charger les contacts - ADAPTÉE MULTI-PROFIL
         */
        loadContacts: function () {
            console.log('📞 Chargement contacts...');
            this.showLoadingState();

            if (this.useAjax) {
                this.loadContactsViaAjax();
            } else {
                this.loadContactsViaRest();
            }
        },

        /**
         * 🆕 NOUVEAU: Chargement via AJAX (multi-profil)
         */
        loadContactsViaAjax: function () {
            console.log('📞 Chargement via AJAX multi-profil...');

            fetch(this.config.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'nfc_get_user_leads',
                    user_id: this.config.user_id,
                    nonce: this.config.nonce
                })
            })
                .then(response => response.json())
                .then(data => {
                    console.log('✅ Contacts reçus via AJAX:', data);

                    if (data.success) {
                        this.contacts = data.data || [];
                        this.processContactsData();
                    } else {
                        throw new Error(data.data || 'Erreur AJAX inconnue');
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur chargement AJAX:', error);
                    this.showError(error.message);
                });
        },

        /**
         * Chargement via REST API (simple vCard) - INCHANGÉ
         */
        loadContactsViaRest: function () {
            console.log('📞 Chargement via REST API simple...');

            const api_url = `${this.config.api_url}leads/${this.config.vcard_id}`;

            fetch(api_url)
                .then(response => response.json())
                .then(data => {
                    console.log('✅ Contacts reçus via REST:', data);

                    if (data.success) {
                        this.contacts = data.data || [];
                        this.processContactsData();
                    } else {
                        throw new Error(data.message || 'Erreur API inconnue');
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur chargement REST:', error);
                    this.showError(error.message);
                });
        },

        /**
         * Traitement des données contacts - COMMUN
         */
        processContactsData: function () {
            this.filteredContacts = [...this.contacts];
            this.updateStats();
            this.applyFilters();
            this.showContent();

            console.log('✅ Contacts traités:', this.contacts.length);
        },

        /**
         * Mettre à jour les stats - INCHANGÉ MAIS AMÉLIORER
         */
        updateStats: function () {
            const total = this.contacts.length;

            // Total
            if (this.elements.totalContactsStat) {
                this.elements.totalContactsStat.textContent = total;
            }

            // Cette semaine
            const oneWeekAgo = new Date();
            oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);

            const newThisWeek = this.contacts.filter(contact => {
                const contactDate = new Date(contact.created_at || contact.contact_datetime);
                return contactDate >= oneWeekAgo;
            }).length;

            if (this.elements.newContactsStat) {
                this.elements.newContactsStat.textContent = newThisWeek;
            }

            // Entreprises uniques
            const companies = new Set();
            this.contacts.forEach(contact => {
                if (contact.society) {
                    companies.add(contact.society);
                }
            });

            if (this.elements.companiesStat) {
                this.elements.companiesStat.textContent = companies.size;
            }

            // QR Code
            const qrContacts = this.contacts.filter(contact => contact.source === 'qr').length;
            if (this.elements.qrSourceStat) {
                this.elements.qrSourceStat.textContent = qrContacts;
            }
        },

        /**
         * Appliquer les filtres - ÉTENDUE MULTI-PROFIL
         */
        applyFilters: function () {
            let filtered = [...this.contacts];

            // Filtre recherche
            if (this.filters.search) {
                filtered = filtered.filter(contact => {
                    const searchText = [
                        contact.firstname,
                        contact.lastname,
                        contact.email,
                        contact.mobile,
                        contact.society
                    ].join(' ').toLowerCase();

                    return searchText.includes(this.filters.search);
                });
            }

            // Filtre source
            if (this.filters.source) {
                filtered = filtered.filter(contact => {
                    return (contact.source || 'web') === this.filters.source;
                });
            }

            // 🆕 NOUVEAU: Filtre profil
            if (this.filters.profile) {
                filtered = filtered.filter(contact => {
                    return contact.vcard_id == this.filters.profile;
                });
            }

            // Tri personnalisé
            filtered.sort((a, b) => {
                let valueA, valueB;

                switch (this.currentSort.field) {
                    case 'name':
                        valueA = `${a.lastname || ''} ${a.firstname || ''}`.trim().toLowerCase();
                        valueB = `${b.lastname || ''} ${b.firstname || ''}`.trim().toLowerCase();
                        break;
                    case 'email':
                        valueA = (a.email || '').toLowerCase();
                        valueB = (b.email || '').toLowerCase();
                        break;
                    case 'mobile':
                        valueA = (a.mobile || '').toLowerCase();
                        valueB = (b.mobile || '').toLowerCase();
                        break;
                    case 'society':
                        valueA = (a.society || '').toLowerCase();
                        valueB = (b.society || '').toLowerCase();
                        break;
                    case 'date':
                        valueA = new Date(a.created_at || a.contact_datetime || '1970-01-01');
                        valueB = new Date(b.created_at || b.contact_datetime || '1970-01-01');
                        break;
                    default:
                        return 0;
                }

                if (this.currentSort.field === 'date') {
                    // Tri par date
                    return this.currentSort.direction === 'asc' ? valueA - valueB : valueB - valueA;
                } else {
                    // Tri alphabétique
                    const comparison = valueA.localeCompare(valueB);
                    return this.currentSort.direction === 'asc' ? comparison : -comparison;
                }
            });

            this.filteredContacts = filtered;
            this.currentPage = 1;
            this.calculatePagination();
            this.renderContacts();
        },

        /**
         * Calculer la pagination - INCHANGÉ
         */
        calculatePagination: function () {
            this.totalPages = Math.ceil(this.filteredContacts.length / this.itemsPerPage);
            if (this.currentPage > this.totalPages) {
                this.currentPage = Math.max(1, this.totalPages);
            }
        },

        /**
         * Rendu des contacts - LÉGÈREMENT ADAPTÉ
         */
        renderContacts: function () {
            if (this.filteredContacts.length === 0) {
                this.showEmptyState();
                return;
            }

            this.hideAllStates();
            if (this.elements.content) {
                this.elements.content.classList.remove('d-none');
            }

            if (this.currentView === 'table') {
                this.renderTableView();
                if (this.elements.tableView) this.elements.tableView.classList.remove('d-none');
                if (this.elements.gridView) this.elements.gridView.classList.add('d-none');
            } else {
                this.renderGridView();
                if (this.elements.gridView) this.elements.gridView.classList.remove('d-none');
                if (this.elements.tableView) this.elements.tableView.classList.add('d-none');
            }

            this.renderPagination();
            if (this.elements.paginationWrapper) {
                this.elements.paginationWrapper.classList.remove('d-none');
            }
        },

        /**
         * Rendu vue tableau - ADAPTÉ MULTI-PROFIL
         */
        renderTableView: function () {
            if (!this.elements.tableBody) return;

            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            const pageContacts = this.filteredContacts.slice(start, end);

            let html = '';
            pageContacts.forEach(contact => {
                const fullName = `${contact.firstname || ''} ${contact.lastname || ''}`.trim();
                const contactDate = contact.contact_datetime || contact.created_at;
                const formattedDate = contactDate ? this.formatDate(contactDate) : 'N/A';

                html += `
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input contact-checkbox" 
                                   value="${contact.id}" onchange="NFCContacts.toggleContactSelection(${contact.id})">
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="contact-avatar me-2">
                                    ${this.getContactInitials(fullName)}
                                </div>
                                <div>
                                    <div class="fw-medium">${this.escapeHtml(fullName)}</div>
                                    ${contact.post ? `<small class="text-muted">${this.escapeHtml(contact.post)}</small>` : ''}
                                </div>
                            </div>
                        </td>
                        <td>${contact.email ? this.escapeHtml(contact.email) : '-'}</td>
                        <td>${contact.mobile ? this.escapeHtml(contact.mobile) : '-'}</td>
                        <td>${contact.society ? this.escapeHtml(contact.society) : '-'}</td>
                        <td>
                            <span class="badge bg-secondary">${this.getSourceLabel(contact.source || 'web')}</span>
                        </td>
                        ${this.config.is_multi_profile ? `
                        <td>
                            <small class="text-muted">${this.escapeHtml(contact.vcard_source_name || 'N/A')}</small>
                        </td>
                        ` : ''}
                        <td>
                            <small class="text-muted">${formattedDate}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="NFCContacts.viewContact(${contact.id})" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-secondary" onclick="NFCContacts.editContact(${contact.id})" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="NFCContacts.deleteContact(${contact.id})" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            this.elements.tableBody.innerHTML = html;
        },

        // ====== MÉTHODES UTILITAIRES (INCHANGÉES) ======

        getContactInitials: function (name) {
            return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().substring(0, 2) || '??';
        },

        escapeHtml: function (text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        formatDate: function (dateString) {
            try {
                return new Date(dateString).toLocaleDateString('fr-FR');
            } catch (e) {
                return 'N/A';
            }
        },

        getSourceLabel: function (source) {
            const labels = {
                'qr': 'QR Code',
                'nfc': 'NFC',
                'web': 'Site Web',
                'manual': 'Manuel'
            };
            return labels[source] || 'Autre';
        },

        // ====== ÉTATS D'AFFICHAGE ======

        showLoadingState: function () {
            this.hideAllStates();
            if (this.elements.loading) this.elements.loading.classList.remove('d-none');
        },

        showEmptyState: function () {
            this.hideAllStates();
            if (this.elements.empty) this.elements.empty.classList.remove('d-none');
        },

        showError: function (message) {
            this.hideAllStates();
            if (this.elements.error) this.elements.error.classList.remove('d-none');
            console.error('NFCContacts Error:', message);
        },

        showContent: function () {
            this.hideAllStates();
            if (this.elements.content) this.elements.content.classList.remove('d-none');
        },

        hideAllStates: function () {
            if (this.elements.loading) this.elements.loading.classList.add('d-none');
            if (this.elements.empty) this.elements.empty.classList.add('d-none');
            if (this.elements.error) this.elements.error.classList.add('d-none');
            if (this.elements.content) this.elements.content.classList.add('d-none');
        },

        // ====== ACTIONS UTILISATEUR - COMPLÈTES ======

        showAddModal: function () {
            console.log('➕ Afficher modal ajout contact');

            document.getElementById('contactModalTitle').textContent = 'Ajouter un contact';
            document.getElementById('contactForm').reset();
            document.getElementById('contactId').value = '';
            this.currentEditId = null;

            const modal = new bootstrap.Modal(document.getElementById('contactModal'));
            modal.show();
        },

        saveContact: function () {
            console.log('💾 Sauvegarder contact');

            const form = document.getElementById('contactForm');
            const formData = new FormData(form);

            // Validation
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Préparer les données
            const contactData = {
                firstname: formData.get('firstname'),
                lastname: formData.get('lastname'),
                email: formData.get('email') || '',
                mobile: formData.get('mobile') || '',
                society: formData.get('society') || '',
                post: formData.get('post') || '',
                source: formData.get('source') || 'manual'
            };

            // Gérer le profil vCard selon le mode
            if (this.config.is_multi_profile) {
                const selectedProfile = formData.get('profile_vcard');
                if (!selectedProfile) {
                    this.showNotification('Veuillez sélectionner un profil vCard', 'error');
                    this.resetSaveButton();
                    return;
                }
                contactData.linked_virtual_card = selectedProfile;
            } else {
                contactData.linked_virtual_card = this.config.vcard_id;
            }

            // Ajouter vcard_id selon le mode
            if (this.useAjax) {
                contactData.user_id = this.config.user_id;
                contactData.vcard_id = this.config.vcard_id; // Pour l'association
            } else {
                contactData.linked_virtual_card = this.config.vcard_id;
            }

            console.log('💾 Données à envoyer:', contactData);

            // Désactiver le bouton
            const saveBtn = document.getElementById('saveContactBtn');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...';
            saveBtn.disabled = true;

            const isEdit = this.currentEditId !== null;

            if (isEdit) {
                this.updateContactViaAjax(contactData);
            } else {
                this.createContactViaApi(contactData);
            }
        },

        createContactViaApi: function (contactData) {
            // Utiliser AJAX WordPress au lieu de l'API REST
            const ajaxData = {
                action: 'nfc_add_contact',
                nonce: this.config.nonce,
                ...contactData
            };

            fetch(this.config.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(ajaxData)
            })
                .then(response => response.json())
                .then(data => {
                    console.log('✅ Contact ajouté:', data);

                    if (data.success) {
                        this.closeModal('contactModal');
                        this.loadContacts();
                        this.showNotification('Contact ajouté avec succès!', 'success');
                    } else {
                        throw new Error(data.data.message || 'Erreur lors de l\'ajout');
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur ajout:', error);
                    this.showNotification('Erreur lors de l\'ajout: ' + error.message, 'error');
                })
                .finally(() => {
                    this.resetSaveButton();
                });
        },

        updateContactViaAjax: function (contactData) {
            const ajaxData = {
                action: 'nfc_update_lead',
                nonce: this.config.nonce,
                lead_id: this.currentEditId,
                ...contactData
            };

            fetch(this.config.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(ajaxData)
            })
                .then(response => response.json())
                .then(data => {
                    console.log('✅ Contact modifié:', data);

                    if (data.success) {
                        this.closeModal('contactModal');
                        this.loadContacts();
                        this.showNotification('Contact modifié avec succès!', 'success');
                    } else {
                        throw new Error(data.data || 'Erreur lors de la modification');
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur modification:', error);
                    this.showNotification('Erreur lors de la modification: ' + error.message, 'error');
                })
                .finally(() => {
                    this.resetSaveButton();
                });
        },

        viewContact: function (contactId) {
            console.log('👁️ Voir contact:', contactId);

            const contact = this.contacts.find(c => c.id == contactId);
            if (!contact) return;

            const fullName = `${contact.firstname || ''} ${contact.lastname || ''}`.trim();

            const content = `
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 100px; height: 100px; font-size: 32px;">
                        ${this.getContactInitials(fullName)}
                    </div>
                    <h4 class="mb-1">${this.escapeHtml(fullName)}</h4>
                    ${contact.post ? `<p class="text-muted mb-2">${this.escapeHtml(contact.post)}</p>` : ''}
                    ${contact.society ? `<p class="fw-medium text-primary">${this.escapeHtml(contact.society)}</p>` : ''}
                    
                    <div class="mt-3">
                        <span class="badge bg-${this.getSourceColor(contact.source)} fs-6">
                            ${this.getSourceLabel(contact.source || 'web')}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="row g-3">
                    ${contact.email ? `
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-envelope text-primary me-3" style="width: 20px;"></i>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <a href="mailto:${contact.email}" class="text-decoration-none fw-medium">
                                    ${this.escapeHtml(contact.email)}
                                </a>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    ${contact.mobile ? `
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-phone text-success me-3" style="width: 20px;"></i>
                            <div>
                                <small class="text-muted d-block">Téléphone</small>
                                <a href="tel:${contact.mobile}" class="text-decoration-none fw-medium">
                                    ${this.escapeHtml(contact.mobile)}
                                </a>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    ${this.config.is_multi_profile && contact.vcard_source_name ? `
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-id-card text-info me-3" style="width: 20px;"></i>
                            <div>
                                <small class="text-muted d-block">Profil vCard source</small>
                                <span class="fw-medium">${this.escapeHtml(contact.vcard_source_name)}</span>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar text-warning me-3" style="width: 20px;"></i>
                            <div>
                                <small class="text-muted d-block">Date de contact</small>
                                <span class="fw-medium">${this.formatDate(contact.created_at || contact.contact_datetime)}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${contact.email && contact.mobile ? `
                <div class="mt-4">
                    <h6 class="text-muted mb-3">Actions rapides</h6>
                    <div class="d-flex gap-2">
                        <a href="mailto:${contact.email}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>Envoyer email
                        </a>
                        <a href="tel:${contact.mobile}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-phone me-1"></i>Appeler
                        </a>
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `;

            document.getElementById('contactDetailsContent').innerHTML = content;
            this.currentEditId = contactId;

            const modal = new bootstrap.Modal(document.getElementById('contactDetailsModal'));
            modal.show();
        },

        // Ajouter cette fonction utilitaire
        getSourceColor: function (source) {
            const colors = {
                'qr': 'primary',
                'nfc': 'success',
                'web': 'info',
                'manual': 'secondary'
            };
            return colors[source] || 'secondary';
        },

        editContact: function (contactId) {
            console.log('✏️ Modifier contact:', contactId);

            const contact = this.contacts.find(c => c.id == contactId);
            if (!contact) return;

            document.getElementById('contactModalTitle').textContent = 'Modifier le contact';
            document.getElementById('contactId').value = contactId;
            document.getElementById('firstname').value = contact.firstname || '';
            document.getElementById('lastname').value = contact.lastname || '';
            document.getElementById('email').value = contact.email || '';
            document.getElementById('mobile').value = contact.mobile || '';
            document.getElementById('society').value = contact.society || '';
            document.getElementById('post').value = contact.post || '';
            document.getElementById('source').value = contact.source || 'web';

            this.currentEditId = contactId;

            const modal = new bootstrap.Modal(document.getElementById('contactModal'));
            modal.show();
        },

        editContactFromDetails: function () {
            const detailsModal = bootstrap.Modal.getInstance(document.getElementById('contactDetailsModal'));
            detailsModal.hide();

            setTimeout(() => {
                this.editContact(this.currentEditId);
            }, 300);
        },

        deleteContact: function (contactId) {
            console.log('🗑️ Supprimer contact:', contactId);

            const contact = this.contacts.find(c => c.id == contactId);
            if (!contact) return;

            const fullName = `${contact.firstname || ''} ${contact.lastname || ''}`.trim();

            if (!confirm(`Êtes-vous sûr de vouloir supprimer ${fullName} ?`)) {
                return;
            }

            const ajaxData = {
                action: 'nfc_delete_lead',
                nonce: this.config.nonce,
                lead_id: contactId
            };

            fetch(this.config.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(ajaxData)
            })
                .then(response => response.json())
                .then(data => {
                    console.log('✅ Contact supprimé:', data);

                    if (data.success) {
                        this.loadContacts();
                        this.showNotification('Contact supprimé avec succès!', 'success');
                    } else {
                        throw new Error(data.data || 'Erreur lors de la suppression');
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur suppression:', error);
                    this.showNotification('Erreur lors de la suppression: ' + error.message, 'error');
                });
        },

        deleteContactFromDetails: function () {
            const detailsModal = bootstrap.Modal.getInstance(document.getElementById('contactDetailsModal'));
            detailsModal.hide();

            setTimeout(() => {
                this.deleteContact(this.currentEditId);
            }, 300);
        },

        exportContacts: function () {
            console.log('📥 Exporter contacts');

            if (this.filteredContacts.length === 0) {
                this.showNotification('Aucun contact à exporter', 'warning');
                return;
            }

            // Créer le CSV
            const headers = ['Prénom', 'Nom', 'Email', 'Téléphone', 'Entreprise', 'Poste', 'Source', 'Date'];
            const csvContent = [
                headers.join(','),
                ...this.filteredContacts.map(contact => [
                    this.escapeCsv(contact.firstname || ''),
                    this.escapeCsv(contact.lastname || ''),
                    this.escapeCsv(contact.email || ''),
                    this.escapeCsv(contact.mobile || ''),
                    this.escapeCsv(contact.society || ''),
                    this.escapeCsv(contact.post || ''),
                    this.escapeCsv(contact.source || 'web'),
                    this.formatDate(contact.created_at || contact.contact_datetime)
                ].join(','))
            ].join('\n');

            // Télécharger
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `contacts_nfc_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();

            this.showNotification('Export CSV téléchargé!', 'success');
        },

        showImportModal: function () {
            console.log('📤 Afficher modal import');

            const modal = new bootstrap.Modal(document.getElementById('importModal'));
            modal.show();
        },

        // ====== UTILITAIRES ======

        closeModal: function (modalId) {
            const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
            if (modal) modal.hide();
        },

        resetSaveButton: function () {
            const saveBtn = document.getElementById('saveContactBtn');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
                saveBtn.disabled = false;
            }
        },

        showNotification: function (message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        },

        escapeCsv: function (text) {
            if (!text) return '';
            text = text.toString();
            if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                return '"' + text.replace(/"/g, '""') + '"';
            }
            return text;
        },

        renderPagination: function () {
            if (!this.elements.pagination || !this.elements.paginationWrapper) return;

            if (this.totalPages <= 1) {
                this.elements.paginationWrapper.classList.add('d-none');
                return;
            }

            this.elements.paginationWrapper.classList.remove('d-none');

            // Mise à jour des compteurs
            const start = (this.currentPage - 1) * this.itemsPerPage + 1;
            const end = Math.min(start + this.itemsPerPage - 1, this.filteredContacts.length);
            const total = this.filteredContacts.length;

            const startSpan = document.getElementById('contactsStart');
            const endSpan = document.getElementById('contactsEnd');
            const totalSpan = document.getElementById('contactsTotal');

            if (startSpan) startSpan.textContent = start;
            if (endSpan) endSpan.textContent = end;
            if (totalSpan) totalSpan.textContent = total;

            // Pagination simple
            let html = '';

            // Bouton précédent
            html += `
                <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="NFCContacts.changePage(${this.currentPage - 1}); return false;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `;

            // Pages
            for (let i = 1; i <= this.totalPages; i++) {
                if (i === this.currentPage || i === 1 || i === this.totalPages ||
                    (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                    html += `
                <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="NFCContacts.changePage(${i}); return false;">
                        ${i}
                    </a>
                </li>
            `;
                }
            }

            // Bouton suivant
            html += `
                <li class="page-item ${this.currentPage === this.totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="NFCContacts.changePage(${this.currentPage + 1}); return false;">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `;

            this.elements.pagination.innerHTML = html;
        },

        changePage: function (page) {
            if (page < 1 || page > this.totalPages) return;

            this.currentPage = page;
            this.renderContacts();

            // Scroll vers le haut
            if (this.elements.content) {
                this.elements.content.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        renderGridView: function () {
            if (!this.elements.gridView) return;

            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            const pageContacts = this.filteredContacts.slice(start, end);

            const container = document.getElementById('contactsGrid');
            if (!container) return;

            let html = '';
            pageContacts.forEach(contact => {
                const fullName = `${contact.firstname || ''} ${contact.lastname || ''}`.trim();
                const formattedDate = this.formatDate(contact.contact_datetime || contact.created_at);

                html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="contact-avatar mx-auto mb-2">
                                ${this.getContactInitials(fullName)}
                            </div>
                            <h6 class="card-title mb-1">${this.escapeHtml(fullName)}</h6>
                            ${contact.post ? `<small class="text-muted">${this.escapeHtml(contact.post)}</small>` : ''}
                        </div>
                        
                        <div class="contact-info small">
                            ${contact.email ? `<div class="mb-1"><i class="fas fa-envelope me-1"></i>${this.escapeHtml(contact.email)}</div>` : ''}
                            ${contact.mobile ? `<div class="mb-1"><i class="fas fa-phone me-1"></i>${this.escapeHtml(contact.mobile)}</div>` : ''}
                            ${contact.society ? `<div class="mb-1"><i class="fas fa-building me-1"></i>${this.escapeHtml(contact.society)}</div>` : ''}
                        </div>
                        
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <span class="badge bg-secondary">${this.getSourceLabel(contact.source || 'web')}</span>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="NFCContacts.viewContact(${contact.id})" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="NFCContacts.deleteContact(${contact.id})" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
            });

            container.innerHTML = html;
        },
        validateCsvFile: function (input) {
            console.log('📁 Validation fichier CSV');
            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                this.parseCsvContent(e.target.result);
            };
            reader.readAsText(file);
        },

        parseCsvContent: function (content) {
            const lines = content.split('\n').filter(line => line.trim());
            if (lines.length < 2) {
                this.showNotification('Fichier CSV vide ou invalide', 'error');
                return;
            }

            // Parser le CSV simple
            const headers = lines[0].split(',').map(h => h.trim());
            this.csvData = [];

            for (let i = 1; i < lines.length; i++) {
                const values = lines[i].split(',').map(v => v.trim());
                if (values.length >= 2) { // Au moins prénom + nom
                    this.csvData.push({
                        firstname: values[0] || '',
                        lastname: values[1] || '',
                        email: values[2] || '',
                        mobile: values[3] || '',
                        society: values[4] || '',
                        post: values[5] || '',
                        source: 'manual'
                    });
                }
            }

            // Afficher aperçu
            this.showCsvPreview();
        },

        showCsvPreview: function () {
            const preview = document.getElementById('csvPreview');
            const importBtn = document.getElementById('importBtn');

            if (this.csvData && this.csvData.length > 0) {
                console.log('📊 CSV parsé:', this.csvData.length, 'contacts');
                importBtn.disabled = false;
                // TODO: Afficher un aperçu visuel
            } else {
                console.log('❌ Aucun contact valide dans le CSV');
                importBtn.disabled = true;
            }
        },

        importCsv: function () {
            if (!this.csvData || this.csvData.length === 0) {
                this.showNotification('Aucun contact à importer', 'error');
                return;
            }

            console.log('📤 Import de', this.csvData.length, 'contacts');

            const importBtn = document.getElementById('importBtn');
            const originalText = importBtn.innerHTML;
            importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Import en cours...';
            importBtn.disabled = true;

            let imported = 0;
            let errors = 0;

            // Fonction pour importer un contact
            const importContact = (contactData, index) => {
                return new Promise((resolve, reject) => {
                    // Ajouter la vCard selon le mode
                    if (this.config.is_multi_profile) {
                        const selectedProfile = document.getElementById('import_profile_vcard');
                        contactData.linked_virtual_card = selectedProfile ? selectedProfile.value : this.config.vcard_id; // TODO: Permettre choix profil
                    } else {
                        contactData.linked_virtual_card = this.config.vcard_id;
                    }

                    const ajaxData = {
                        action: 'nfc_add_contact',
                        nonce: this.config.nonce,
                        ...contactData
                    };

                    fetch(this.config.ajax_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams(ajaxData)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                imported++;
                                console.log(`✅ Contact ${index + 1}/${this.csvData.length} importé`);
                            } else {
                                errors++;
                                console.error(`❌ Erreur contact ${index + 1}:`, data.data);
                            }
                            resolve();
                        })
                        .catch(error => {
                            errors++;
                            console.error(`❌ Erreur contact ${index + 1}:`, error);
                            resolve(); // Continue même en cas d'erreur
                        });
                });
            };

            // Importer tous les contacts séquentiellement (pour éviter la surcharge)
            const importAll = async () => {
                for (let i = 0; i < this.csvData.length; i++) {
                    await importContact(this.csvData[i], i);

                    // Petite pause entre chaque import
                    await new Promise(resolve => setTimeout(resolve, 100));
                }

                // Résultats finaux
                const message = `Import terminé: ${imported} contacts ajoutés`;
                if (errors > 0) {
                    this.showNotification(`${message}, ${errors} erreurs`, 'warning');
                } else {
                    this.showNotification(message, 'success');
                }

                // Recharger les contacts et fermer la modal
                this.loadContacts();
                this.closeModal('importModal');

                // Remettre le bouton
                importBtn.innerHTML = originalText;
                importBtn.disabled = false;
            };

            // Lancer l'import
            importAll().catch(error => {
                console.error('❌ Erreur import global:', error);
                this.showNotification('Erreur lors de l\'import', 'error');
                importBtn.innerHTML = originalText;
                importBtn.disabled = false;
            });
        },
        sortBy: function (field) {
            // Inverser la direction si même champ
            if (this.currentSort.field === field) {
                this.currentSort.direction = this.currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.currentSort.field = field;
                this.currentSort.direction = 'asc';
            }

            // Mettre à jour les icônes
            this.updateSortIcons();

            // Appliquer le tri
            this.applyFilters();
        },

        updateSortIcons: function () {
            // Reset toutes les icônes
            document.querySelectorAll('[id^="sort-"]').forEach(icon => {
                icon.className = 'fas fa-sort ms-1 text-muted';
            });

            // Mettre à jour l'icône active
            const activeIcon = document.getElementById(`sort-${this.currentSort.field}`);
            if (activeIcon) {
                const iconClass = this.currentSort.direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                activeIcon.className = `fas ${iconClass} ms-1 text-primary`;
            }
        }
    };

})(typeof jQuery !== 'undefined' ? jQuery : null);

// Auto-initialisation si pas empêchée
if (typeof window.nfcContactsPreventAutoLoad === 'undefined' || !window.nfcContactsPreventAutoLoad) {
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof NFCContacts !== 'undefined') {
            NFCContacts.init();
        }
    });
}