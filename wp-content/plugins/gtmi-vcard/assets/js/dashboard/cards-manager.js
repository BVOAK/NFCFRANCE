/**
 * Cards Manager - Dashboard NFC France
 * Style cohérent avec contacts-manager.js
 */

class NFCCardsManager {
    constructor(config) {
        this.config = config;
        this.filteredCards = [...config.vcards];
        
        this.init();
    }
    
    init() {
        console.log('🚀 Initialisation Cards Manager');
        console.log('📊 Configuration:', this.config);
        
        this.setupEventListeners();
        this.setupSearch();
    }
    
    setupEventListeners() {
        // Recherche (même pattern que leads.php)
        const searchInput = document.getElementById('cardsSearch');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.filterCards(e.target.value);
            }, 300));
        }
        
        // QR Code modal
        const downloadQR = document.getElementById('downloadQR');
        if (downloadQR) {
            downloadQR.addEventListener('click', () => {
                this.downloadQRCode();
            });
        }
    }
    
    // === RECHERCHE ET FILTRES (même logique que contacts) ===
    setupSearch() {
        // Déjà configuré dans setupEventListeners
    }
    
    filterCards(searchTerm) {
        if (!this.config.is_multi_cards) return; // Pas de filtrage en mode single
        
        const tbody = document.getElementById('cardsTableBody');
        if (!tbody) return;
        
        const rows = tbody.querySelectorAll('tr');
        let visibleCount = 0;
        
        if (!searchTerm.trim()) {
            // Afficher toutes les cartes
            rows.forEach(row => {
                row.style.display = '';
                visibleCount++;
            });
        } else {
            const term = searchTerm.toLowerCase();
            
            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(term)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        console.log(`🔍 Filtrage: ${visibleCount} cartes affichées sur ${rows.length}`);
    }
    
    // === ACTIONS SUR LES CARTES ===
    async showQRModal(cardId) {
        try {
            console.log('📱 Génération QR pour carte:', cardId);
            
            const card = this.findCardById(cardId);
            if (!card) {
                this.showNotification('Carte introuvable', 'error');
                return;
            }
            
            // Générer le contenu du QR Code
            const qrContent = await this.generateQRContent(card);
            
            // Afficher dans la modal (même style que leads.php)
            this.displayQRModal(card, qrContent);
            
        } catch (error) {
            console.error('❌ Erreur génération QR:', error);
            this.showNotification('Erreur lors de la génération du QR Code', 'error');
        }
    }
    
    async generateQRContent(card) {
        // Simuler génération QR (à remplacer par vraie librairie QR)
        const publicUrl = `${window.location.origin}/?p=${card.vcard.ID}`;
        
        return `
            <div class="qr-preview mb-3">
                <div class="bg-light border rounded p-4 mx-auto" style="width: 200px; height: 200px;">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <i class="fas fa-qrcode fa-3x text-muted mb-2"></i>
                            <div class="small text-muted">QR Code pour<br>${card.full_name || card.identifier}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <small class="text-muted">URL: ${publicUrl}</small>
            </div>
        `;
    }
    
    displayQRModal(card, content) {
        const modal = document.getElementById('qrCodeModal');
        const contentDiv = document.getElementById('qrCodeContent');
        const title = modal.querySelector('.modal-title');
        
        if (!modal || !contentDiv || !title) {
            console.error('❌ Éléments modal QR introuvables');
            return;
        }
        
        title.innerHTML = `<i class="fas fa-qrcode me-2"></i>QR Code - ${card.full_name || card.identifier}`;
        contentDiv.innerHTML = content;
        
        // Stocker la carte pour le téléchargement
        this.currentQRCard = card;
        
        // Afficher la modal (Bootstrap 5)
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
    
    downloadQRCode() {
        if (!this.currentQRCard) {
            this.showNotification('Aucun QR Code à télécharger', 'warning');
            return;
        }
        
        // Simulation téléchargement (à implémenter avec vraie génération)
        const cardName = this.currentQRCard.full_name || this.currentQRCard.identifier;
        this.showNotification(`QR Code téléchargé pour ${cardName}`, 'success');
        
        console.log('💾 Téléchargement QR pour:', cardName);
    }
    
    // === UTILITAIRES ===
    findCardById(cardId) {
        return this.config.vcards.find(card => card.vcard.ID == cardId);
    }
    
    // === NOTIFICATIONS (même style que contacts) ===
    showNotification(message, type = 'info') {
        console.log(`🔔 ${type.toUpperCase()}: ${message}`);
        
        // Créer toast notification (style Bootstrap)
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${this.getBootstrapColor(type)} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${this.getIconForType(type)} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        // Ajouter au container de toasts (s'il existe)
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        const toastElement = document.createElement('div');
        toastElement.innerHTML = toastHtml;
        const toast = toastElement.firstElementChild;
        
        toastContainer.appendChild(toast);
        
        // Afficher le toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Supprimer après fermeture
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
    
    getBootstrapColor(type) {
        const colors = {
            'success': 'success',
            'error': 'danger',
            'warning': 'warning',
            'info': 'info'
        };
        return colors[type] || 'info';
    }
    
    getIconForType(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-triangle',
            'warning': 'exclamation-circle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
    
    // === DEBOUNCE UTILITY ===
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    if (typeof CARDS_CONFIG !== 'undefined') {
        console.log('🎯 Initialisation Cards Manager avec config:', CARDS_CONFIG);
        window.cardsManager = new NFCCardsManager(CARDS_CONFIG);
    } else {
        console.warn('⚠️ CARDS_CONFIG non trouvé - Cards Manager non initialisé');
    }
});