/**
 * JavaScript pour la gestion des contacts Dashboard NFC - MULTI-PROFIL COMPATIBLE
 * 
 * Fichier: assets/js/dashboard/contacts-manager.js
 * VERSION FIXÉE pour supporter AJAX multi-profil + fallback API REST
 */

(function($) {
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
        init: function() {
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
        cacheElements: function() {
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
        bindEvents: function() {
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
        loadContacts: function() {
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
        loadContactsViaAjax: function() {
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
        loadContactsViaRest: function() {
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
        processContactsData: function() {
            this.filteredContacts = [...this.contacts];
            this.updateStats();
            this.applyFilters();
            this.showContent();
            
            console.log('✅ Contacts traités:', this.contacts.length);
        },

        /**
         * Mettre à jour les stats - INCHANGÉ MAIS AMÉLIORER
         */
        updateStats: function() {
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
        applyFilters: function() {
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
            
            // Tri
            filtered.sort((a, b) => {
                switch (this.filters.sortBy) {
                    case 'date_asc':
                        return new Date(a.created_at || a.contact_datetime) - new Date(b.created_at || b.contact_datetime);
                    case 'date_desc':
                        return new Date(b.created_at || b.contact_datetime) - new Date(a.created_at || a.contact_datetime);
                    case 'name_asc':
                        return (a.lastname || '').localeCompare(b.lastname || '');
                    case 'name_desc':
                        return (b.lastname || '').localeCompare(a.lastname || '');
                    default:
                        return 0;
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
        calculatePagination: function() {
            this.totalPages = Math.ceil(this.filteredContacts.length / this.itemsPerPage);
            if (this.currentPage > this.totalPages) {
                this.currentPage = Math.max(1, this.totalPages);
            }
        },

        /**
         * Rendu des contacts - LÉGÈREMENT ADAPTÉ
         */
        renderContacts: function() {
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
        renderTableView: function() {
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

        getContactInitials: function(name) {
            return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().substring(0, 2) || '??';
        },

        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        formatDate: function(dateString) {
            try {
                return new Date(dateString).toLocaleDateString('fr-FR');
            } catch (e) {
                return 'N/A';
            }
        },

        getSourceLabel: function(source) {
            const labels = {
                'qr': 'QR Code',
                'nfc': 'NFC',
                'web': 'Site Web',
                'manual': 'Manuel'
            };
            return labels[source] || 'Autre';
        },

        // ====== ÉTATS D'AFFICHAGE ======

        showLoadingState: function() {
            this.hideAllStates();
            if (this.elements.loading) this.elements.loading.classList.remove('d-none');
        },

        showEmptyState: function() {
            this.hideAllStates();
            if (this.elements.empty) this.elements.empty.classList.remove('d-none');
        },

        showError: function(message) {
            this.hideAllStates();
            if (this.elements.error) this.elements.error.classList.remove('d-none');
            console.error('NFCContacts Error:', message);
        },

        showContent: function() {
            this.hideAllStates();
            if (this.elements.content) this.elements.content.classList.remove('d-none');
        },

        hideAllStates: function() {
            if (this.elements.loading) this.elements.loading.classList.add('d-none');
            if (this.elements.empty) this.elements.empty.classList.add('d-none');
            if (this.elements.error) this.elements.error.classList.add('d-none');
            if (this.elements.content) this.elements.content.classList.add('d-none');
        },

        // ====== ACTIONS UTILISATEUR (À COMPLÉTER) ======

        renderGridView: function() {
            // TODO: Implémenter si nécessaire
            console.log('Vue grille à implémenter');
        },

        renderPagination: function() {
            // TODO: Implémenter si nécessaire
            console.log('Pagination à implémenter');
        },

        toggleContactSelection: function(contactId) {
            // TODO: Implémenter
            console.log('Toggle contact:', contactId);
        },

        toggleAllContacts: function(checked) {
            // TODO: Implémenter
            console.log('Toggle all contacts:', checked);
        },

        viewContact: function(contactId) {
            console.log('Voir contact:', contactId);
            // TODO: Implémenter
        },

        editContact: function(contactId) {
            console.log('Modifier contact:', contactId);
            // TODO: Implémenter
        },

        deleteContact: function(contactId) {
            console.log('Supprimer contact:', contactId);
            // TODO: Implémenter
        }
    };

})(typeof jQuery !== 'undefined' ? jQuery : null);

// Auto-initialisation si pas empêchée
if (typeof window.nfcContactsPreventAutoLoad === 'undefined' || !window.nfcContactsPreventAutoLoad) {
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof NFCContacts !== 'undefined') {
            NFCContacts.init();
        }
    });
}