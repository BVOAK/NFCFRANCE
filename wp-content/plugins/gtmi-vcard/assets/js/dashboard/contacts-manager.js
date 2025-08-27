/**
 * JavaScript pour la gestion des contacts Dashboard NFC
 * 
 * Fichier: assets/js/dashboard/contacts-manager.js
 * Gestion complète des contacts avec CRUD, filtres et export
 * 
 * VERSION CORRIGÉE - Fix du problème de chargement
 */

(function($) {
    'use strict';

    /**
     * Objet principal NFCContacts
     */
    window.NFCContacts = {
        
        // Configuration (CORRECTION: utiliser la bonne variable)
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
        
        // Filtres actifs
        filters: {
            search: '',
            source: '',
            date: '',
            company: '',
            status: '',
            dateFrom: '',
            dateTo: '',
            sortBy: 'date_desc'
        },
        
        // Éléments DOM cachés
        elements: {
            loading: null,
            empty: null,
            error: null,
            tableView: null,
            gridView: null,
            pagination: null,
            searchInput: null,
            selectAll: null,
            bulkActions: null
        },

        /**
         * Initialisation principale
         */
        init: function() {
            console.log('📧 NFCContacts - Initialisation');
            console.log('📧 Configuration reçue:', this.config);
            
            // CORRECTION: Vérifier si on a bien la config
            if (!this.config || !this.config.vcard_id) {
                console.error('❌ Configuration NFCContacts manquante ou incomplète');
                this.showError('Configuration manquante');
                return;
            }
            
            if (this.isLoading) {
                console.warn('⚠️ NFCContacts déjà en cours de chargement');
                return;
            }

            this.cacheElements();
            this.bindEvents();
            this.loadContacts();
            
            console.log('✅ NFCContacts prêt avec vCard ID:', this.config.vcard_id);
        },

        /**
         * Cache des éléments DOM
         */
        cacheElements: function() {
            this.elements = {
                loading: document.getElementById('contactsLoading'),
                empty: document.getElementById('contactsEmpty'),
                error: document.getElementById('contactsError'),
                tableView: document.getElementById('contactsTableView'),
                gridView: document.getElementById('contactsGridView'),
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
                // Views
                tableViewBtn: document.getElementById('tableViewBtn'),
                gridViewBtn: document.getElementById('gridViewBtn')
            };
            
            // Vérifier que les éléments critiques existent
            const criticalElements = ['loading', 'empty', 'error', 'tableView', 'gridView'];
            for (const elementName of criticalElements) {
                if (!this.elements[elementName]) {
                    console.error(`❌ Élément DOM manquant: ${elementName}`);
                }
            }
        },

        /**
         * Liaison des événements
         */
        bindEvents: function() {
            const $ = jQuery;
            
            // Recherche
            if (this.elements.searchInput) {
                $(this.elements.searchInput).on('input', (e) => {
                    this.filters.search = e.target.value;
                    this.debounce(this.applyFilters.bind(this), 300)();
                });
            }

            // Filtres
            if (this.elements.sourceFilter) {
                $(this.elements.sourceFilter).on('change', (e) => {
                    this.filters.source = e.target.value;
                    this.applyFilters();
                });
            }

            if (this.elements.dateFilter) {
                $(this.elements.dateFilter).on('change', (e) => {
                    this.filters.date = e.target.value;
                    this.applyFilters();
                });
            }

            if (this.elements.sortFilter) {
                $(this.elements.sortFilter).on('change', (e) => {
                    this.filters.sortBy = e.target.value;
                    this.applyFilters();
                });
            }

            // Select all
            if (this.elements.selectAll) {
                $(this.elements.selectAll).on('change', (e) => {
                    this.toggleSelectAll(e.target.checked);
                });
            }
        },

        /**
         * Charger les contacts depuis l'API
         */

loadContacts: function() {
    console.log('📡 Chargement des contacts...');
    
    if (this.isLoading) {
        console.log('⚠️ Chargement déjà en cours');
        return;
    }

    this.isLoading = true;
    this.showLoadingState();
    
    // COPIER EXACTEMENT LE PATTERN DE STATISTICS.JS
    const apiUrl = `${this.config.api_url}leads/${this.config.vcard_id}`;
    console.log('🌐 URL API (copié de stats):', apiUrl);
    
    fetch(apiUrl, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
            // PAS de nonce, PAS de credentials - comme dans statistics.js
        }
    })
    .then(response => {
        console.log('📡 Réponse API Status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return response.json();
    })
    .then(apiData => {
        console.log('📦 Données brutes API:', apiData);
        
        // COPIER LA LOGIQUE DE STATISTICS.JS pour parser les données
        if (apiData.success && apiData.data && Array.isArray(apiData.data)) {
            if (apiData.data.length > 0) {
                console.log(`✅ ${apiData.data.length} contacts chargés depuis l'API !`);
                
                this.contacts = apiData.data;
                this.applyFilters();
                this.updateStats();
                this.hideLoadingState();
                
            } else {
                console.log('⚠️ Aucune donnée disponible dans l\'API');
                this.contacts = [];
                this.showEmptyState();
                this.hideLoadingState();
            }
        } else {
            console.log('⚠️ Structure API inattendue:', apiData);
            this.contacts = [];
            this.showEmptyState();
            this.hideLoadingState();
        }
    })
    .catch(error => {
        console.error('❌ Erreur API:', error);
        this.showError('Impossible de charger les contacts: ' + error.message);
        this.hideLoadingState();
    })
    .finally(() => {
        this.isLoading = false;
    });
},

        /**
         * Appliquer les filtres
         */
        applyFilters: function() {
            console.log('🔍 Application des filtres:', this.filters);
            
            let filtered = [...this.contacts];

            // Recherche textuelle
            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                filtered = filtered.filter(contact => {
                    return (
                        (contact.firstname || '').toLowerCase().includes(search) ||
                        (contact.lastname || '').toLowerCase().includes(search) ||
                        (contact.email || '').toLowerCase().includes(search) ||
                        (contact.mobile || '').toLowerCase().includes(search) ||
                        (contact.society || '').toLowerCase().includes(search)
                    );
                });
            }

            // Filtre source
            if (this.filters.source) {
                filtered = filtered.filter(contact => {
                    return contact.source === this.filters.source;
                });
            }

            // Filtre date
            if (this.filters.date) {
                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                
                filtered = filtered.filter(contact => {
                    const contactDate = new Date(contact.contact_datetime || contact.created_at);
                    
                    switch (this.filters.date) {
                        case 'today':
                            return contactDate >= today;
                        case 'week':
                            const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                            return contactDate >= weekAgo;
                        case 'month':
                            const monthAgo = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
                            return contactDate >= monthAgo;
                        default:
                            return true;
                    }
                });
            }

            // Tri
            filtered.sort((a, b) => {
                switch (this.filters.sortBy) {
                    case 'date_asc':
                        return new Date(a.contact_datetime || a.created_at) - new Date(b.contact_datetime || b.created_at);
                    case 'date_desc':
                        return new Date(b.contact_datetime || b.created_at) - new Date(a.contact_datetime || a.created_at);
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
         * Calculer la pagination
         */
        calculatePagination: function() {
            this.totalPages = Math.ceil(this.filteredContacts.length / this.itemsPerPage);
            if (this.currentPage > this.totalPages) {
                this.currentPage = Math.max(1, this.totalPages);
            }
        },

        /**
         * Rendu des contacts
         */
        renderContacts: function() {
            if (this.filteredContacts.length === 0) {
                this.showEmptyState();
                return;
            }

            this.hideAllStates();
            
            if (this.currentView === 'table') {
                this.renderTableView();
                this.elements.tableView.classList.remove('d-none');
            } else {
                this.renderGridView();
                this.elements.gridView.classList.remove('d-none');
            }
            
            this.renderPagination();
            this.elements.paginationWrapper?.classList.remove('d-none');
        },

        /**
         * Rendu vue tableau
         */
        renderTableView: function() {
            const tbody = document.getElementById('contactsTableBody');
            if (!tbody) return;

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
                        <td>${this.escapeHtml(contact.email || 'N/A')}</td>
                        <td>${this.escapeHtml(contact.mobile || 'N/A')}</td>
                        <td>${this.escapeHtml(contact.society || 'N/A')}</td>
                        <td>
                            <span class="badge bg-secondary">${this.getSourceLabel(contact.source || 'web')}</span>
                        </td>
                        <td>${formattedDate}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="NFCContacts.viewContact(${contact.id})" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="NFCContacts.deleteContact(${contact.id})" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        },

        /**
         * Rendu vue grille
         */
        renderGridView: function() {
            const container = document.getElementById('contactsGridBody');
            if (!container) return;

            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            const pageContacts = this.filteredContacts.slice(start, end);

            let html = '';
            pageContacts.forEach(contact => {
                const fullName = `${contact.firstname || ''} ${contact.lastname || ''}`.trim();
                const contactDate = contact.contact_datetime || contact.created_at;
                const formattedDate = contactDate ? this.formatDate(contactDate) : 'N/A';
                
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="contact-card">
                            <div class="contact-card-header">
                                <input type="checkbox" class="form-check-input contact-checkbox" 
                                       value="${contact.id}" onchange="NFCContacts.toggleContactSelection(${contact.id})">
                                <div class="contact-avatar">
                                    ${this.getContactInitials(fullName)}
                                </div>
                            </div>
                            <div class="contact-card-body">
                                <h6 class="contact-name">${this.escapeHtml(fullName)}</h6>
                                ${contact.post ? `<p class="contact-title text-muted">${this.escapeHtml(contact.post)}</p>` : ''}
                                ${contact.society ? `<p class="contact-company">${this.escapeHtml(contact.society)}</p>` : ''}
                                <div class="contact-details">
                                    ${contact.email ? `<div><i class="fas fa-envelope"></i> ${this.escapeHtml(contact.email)}</div>` : ''}
                                    ${contact.mobile ? `<div><i class="fas fa-phone"></i> ${this.escapeHtml(contact.mobile)}</div>` : ''}
                                </div>
                            </div>
                            <div class="contact-card-footer">
                                <span class="badge bg-secondary">${this.getSourceLabel(contact.source || 'web')}</span>
                                <small class="text-muted">${formattedDate}</small>
                                <div class="contact-actions">
                                    <button class="btn btn-sm btn-outline-primary" onclick="NFCContacts.viewContact(${contact.id})" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="NFCContacts.deleteContact(${contact.id})" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        },

        /**
         * Rendu de la pagination
         */
        renderPagination: function() {
            if (!this.elements.pagination) return;

            if (this.totalPages <= 1) {
                this.elements.paginationWrapper?.classList.add('d-none');
                return;
            }

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
            const maxVisible = 5;
            let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
            let end = Math.min(this.totalPages, start + maxVisible - 1);
            
            if (end - start + 1 < maxVisible) {
                start = Math.max(1, end - maxVisible + 1);
            }
            
            for (let i = start; i <= end; i++) {
                html += `
                    <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="NFCContacts.changePage(${i}); return false;">${i}</a>
                    </li>
                `;
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

        /**
         * Changer de page
         */
        changePage: function(page) {
            if (page < 1 || page > this.totalPages || page === this.currentPage) return;
            
            this.currentPage = page;
            this.renderContacts();
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        /**
         * Changer de vue (table/grid)
         */
        switchView: function(view) {
            this.currentView = view;
            
            // Mettre à jour les boutons
            this.elements.tableViewBtn?.classList.toggle('active', view === 'table');
            this.elements.gridViewBtn?.classList.toggle('active', view === 'grid');
            
            this.renderContacts();
        },

        /**
         * Mise à jour des statistiques
         */
        updateStats: function() {
            const total = this.contacts.length;
            
            // Total
            if (this.elements.totalContactsStat) {
                this.elements.totalContactsStat.textContent = total;
            }
            if (this.elements.contactsCounter) {
                this.elements.contactsCounter.textContent = total;
            }
            
            // Cette semaine
            const weekAgo = new Date();
            weekAgo.setDate(weekAgo.getDate() - 7);
            const newThisWeek = this.contacts.filter(contact => {
                const contactDate = new Date(contact.contact_datetime || contact.created_at);
                return contactDate >= weekAgo;
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
         * États d'affichage
         */
        showLoadingState: function() {
            this.hideAllStates();
            this.elements.loading?.classList.remove('d-none');
        },

        showEmptyState: function() {
            this.hideAllStates();
            this.elements.empty?.classList.remove('d-none');
        },

        showError: function(message) {
            this.hideAllStates();
            this.elements.error?.classList.remove('d-none');
            console.error('NFCContacts Error:', message);
        },

        hideLoadingState: function() {
            this.elements.loading?.classList.add('d-none');
        },

        hideAllStates: function() {
            this.elements.loading?.classList.add('d-none');
            this.elements.empty?.classList.add('d-none');
            this.elements.error?.classList.add('d-none');
            this.elements.tableView?.classList.add('d-none');
            this.elements.gridView?.classList.add('d-none');
            this.elements.paginationWrapper?.classList.add('d-none');
        },

        /**
         * Utilitaires
         */
        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        getSourceLabel: function(source) {
            const labels = {
                qr: 'QR Code',
                nfc: 'NFC',
                web: 'Web',
                manual: 'Manuel'
            };
            return labels[source] || source;
        },

        getContactInitials: function(name) {
            if (!name) return 'N/A';
            const parts = name.trim().split(' ');
            const initials = parts.map(part => part.charAt(0).toUpperCase()).join('');
            return initials.substring(0, 2) || 'N/A';
        },

        formatDate: function(dateString) {
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('fr-FR', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch (e) {
                return 'Date invalide';
            }
        },

        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Méthodes stub pour les fonctionnalités à venir
        viewContact: function(id) {
            console.log('Voir contact:', id);
            // TODO: Implémenter
        },

        /**
         * Supprimer un contact - IMPLÉMENTATION RÉELLE
         */
        deleteContact: function(id) {
            console.log('🗑️ Supprimer contact:', id);
            
            const contact = this.contacts.find(c => c.id == id);
            if (!contact) {
                console.error('❌ Contact introuvable:', id);
                return;
            }
            
            const contactName = `${contact.firstname || ''} ${contact.lastname || ''}`.trim();
            
            if (!confirm(`Êtes-vous sûr de vouloir supprimer ${contactName} ?`)) {
                return;
            }
            
            // Préparer la requête AJAX
            const formData = new FormData();
            formData.append('action', 'nfc_delete_contact');
            formData.append('contact_id', id);
            formData.append('nonce', this.config.nonce);
            
            fetch(this.config.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                console.log('📦 Réponse suppression:', data);
                
                if (data.success) {
                    console.log('✅ Contact supprimé avec succès');
                    
                    // Supprimer de la liste locale
                    this.contacts = this.contacts.filter(c => c.id != id);
                    this.applyFilters();
                    this.updateStats();
                    
                    // Notification de succès
                    this.showNotification('Contact supprimé avec succès', 'success');
                } else {
                    throw new Error(data.data.message || 'Erreur lors de la suppression');
                }
            })
            .catch(error => {
                console.error('❌ Erreur suppression:', error);
                this.showNotification('Erreur lors de la suppression: ' + error.message, 'error');
            });
        },

                /**
         * Afficher une notification
         */
        showNotification: function(message, type = 'info') {
            console.log(`🔔 Notification ${type}:`, message);
            
            // Créer la notification
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Supprimer après 5 secondes
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        },

        showAddModal: function() {
            console.log('Ajouter contact');
            // TODO: Implémenter
        },

        exportContacts: function() {
            console.log('Exporter contacts');
            // TODO: Implémenter
        },

        importContacts: function() {
            console.log('Importer contacts');
            // TODO: Implémenter
        },

        toggleContactSelection: function(id) {
            console.log('Toggle selection:', id);
            // TODO: Implémenter
        },

        toggleSelectAll: function(checked) {
            console.log('Toggle select all:', checked);
            // TODO: Implémenter
        },

        exportSelected: function() {
            console.log('Exporter sélection');
            // TODO: Implémenter
        },

        deleteSelected: function() {
            console.log('Supprimer sélection');
            // TODO: Implémenter
        }
    };

    /**
     * Initialisation automatique au chargement du DOM
     */
    $(document).ready(function() {
        // CORRECTION: Attendre un peu pour s'assurer que toutes les configs sont chargées
        setTimeout(() => {
            window.NFCContacts.init();
        }, 100);
    });

})(jQuery);