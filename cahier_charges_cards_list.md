# 📋 CAHIER DES CHARGES - Page Cards List

**Version :** 1.0  
**Date :** 15/09/2025  
**Projet :** NFC France - Dashboard Unifié  
**Objectif :** Créer une page de gestion des cartes vCard utilisateur complète

---

## 🎯 **OBJECTIF PRINCIPAL**

Développer une page **Cards List unique** qui :
- **Affiche toutes les cartes** de l'utilisateur dans un tableau interactif
- **S'adapte automatiquement** selon le nombre de cartes (mono vs multi-cartes)
- **Permet la gestion** : configuration, statistiques, réorganisation
- **Utilise l'architecture standardisée** (PHP/JS/AJAX séparés)

---

## 📊 **ANALYSE FONCTIONNELLE**

### **A1. États d'Affichage**

#### **État 1 : Aucune vCard (Redirection CTA)**
```php
if (empty($user_vcards)) {
    include 'partials/no-products-state.php';
    return;
}
```

#### **État 2 : Une seule vCard (Mode Simple)**
- **Interface épurée** : Focus sur la vCard unique
- **Titre** : "Ma carte NFC"
- **Actions directes** : Configurer, Voir aperçu, QR Code

#### **État 3 : Plusieurs vCards (Mode Entreprise)**
- **Interface tableau** : Vue d'ensemble de toutes les cartes
- **Titre** : "Mes cartes NFC (X cartes)"
- **Actions groupées** : Tri, filtres, actions en lot

### **A2. Données à Afficher**

#### **A2.1 Informations par Carte**
```
┌─ IDENTIFIANT ────────┐  ┌─ PROFIL ─────────────┐  ┌─ STATUT ─────────┐  ┌─ PERFORMANCES ──┐
│                      │  │                      │  │                  │  │                  │
│  NFC-2024-0001      │  │  👤 Marie Dupont     │  │  ✅ Configurée   │  │  👁️ 247 vues    │
│  🏷️ Marketing        │  │  📧 Marketing Dir.   │  │  🔄 Synchronisée │  │  📧 12 contacts  │
│  📅 Créée: 15/09    │  │  🏢 ACME Corp        │  │  ⚡ Active       │  │  📈 +15% ce mois │
└──────────────────────┘  └──────────────────────┘  └──────────────────┘  └──────────────────┘
```

#### **A2.2 Actions par Carte**
1. **👁️ Aperçu public** → Ouvre dans nouvel onglet
2. **⚙️ Configurer** → Redirection vers vcard-edit
3. **📊 Statistiques** → Modal ou redirection vers statistics
4. **📱 QR Code** → Modal de génération/téléchargement
5. **🔄 Synchroniser** → AJAX sync avec carte physique
6. **⚠️ Désactiver/Activer** → Toggle statut

#### **A2.3 Stats Globales (Header)**
```
📊 Vues totales: 1,247  |  📧 Contacts: 89  |  📱 Cartes actives: 6/8  |  📈 Croissance: +15%
```

---

## 🏗️ **ARCHITECTURE TECHNIQUE**

### **F1. Fichiers selon Standard**

#### **F1.1 Template Principal**
```
📁 wp-content/plugins/gtmi-vcard/templates/dashboard/
└── cards-list.php                    # 🆕 Template adaptatif
```

#### **F1.2 Manager JavaScript**
```
📁 wp-content/plugins/gtmi-vcard/assets/js/dashboard/
└── cards-manager.js                  # 🆕 Gestion interface + AJAX
```

#### **F1.3 Handlers AJAX (dans ajax-handlers.php)**
```php
// 🆕 Nouvelles méthodes à ajouter
public function get_user_cards()         // Récupérer cartes utilisateur
public function update_card_status()     // Activer/désactiver carte
public function sync_card()              // Synchroniser avec physique
public function reorder_cards()          // Réorganiser l'ordre
public function get_card_stats()         // Stats rapides par carte
public function generate_card_qr()       // Générer QR Code
```

#### **F1.4 CSS Spécifique**
```
📁 wp-content/plugins/gtmi-vcard/assets/css/dashboard/
└── cards-list.css                    # 🆕 Styles tableau cartes
```

### **F2. Structure cards-list.php**

```php
<?php
/**
 * Dashboard - Cards List
 * Gestion des cartes vCard utilisateur
 */

// 1. VÉRIFICATIONS SÉCURITÉ
if (!defined('ABSPATH')) exit;
if (!is_user_logged_in()) wp_redirect(home_url('/login'));

// 2. LOGIQUE MÉTIER
$user_id = get_current_user_id();
$user_vcards = $this->get_user_vcards($user_id);

// Détection des états
if (empty($user_vcards)) {
    include 'partials/no-products-state.php';
    return;
}

// Interface selon nombre de vCards
$is_multi_cards = count($user_vcards) > 1;
$page_title = $is_multi_cards ? 
    "Mes cartes NFC (" . count($user_vcards) . ")" : 
    "Ma carte NFC";

// Stats globales
$global_stats = $this->calculate_global_card_stats($user_vcards);

// Configuration JavaScript
$cards_config = [
    'user_id' => $user_id,
    'vcards' => $user_vcards,
    'is_multi_cards' => $is_multi_cards,
    'global_stats' => $global_stats,
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce')
];
?>

<!-- CSS et JS -->
<link rel="stylesheet" href="<?= plugin_dir_url(__FILE__) ?>../../assets/css/dashboard/cards-list.css">
<script>window.CARDS_CONFIG = <?= json_encode($cards_config) ?>;</script>

<!-- HTML de la page -->
<div class="dashboard-cards-list">
    <!-- Header avec stats globales -->
    <div class="cards-header">...</div>
    
    <!-- Tableau des cartes ou vue simple -->
    <?php if ($is_multi_cards): ?>
        <div class="cards-table-container">...</div>
    <?php else: ?>
        <div class="single-card-view">...</div>
    <?php endif; ?>
</div>

<script src="<?= plugin_dir_url(__FILE__) ?>../../assets/js/dashboard/cards-manager.js"></script>
```

### **F3. Structure cards-manager.js**

```javascript
class NFCCardsManager {
    constructor(config) {
        this.config = config;
        this.selectedCards = new Set();
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadCardsData();
    }
    
    setupEventListeners() {
        // Actions par carte
        $(document).on('click', '.btn-configure-card', this.configureCard.bind(this));
        $(document).on('click', '.btn-preview-card', this.previewCard.bind(this));
        $(document).on('click', '.btn-qr-card', this.generateQR.bind(this));
        $(document).on('click', '.btn-stats-card', this.showStats.bind(this));
        
        // Actions groupées
        $(document).on('change', '.card-checkbox', this.updateSelection.bind(this));
        $('#bulk-actions').on('change', this.handleBulkAction.bind(this));
    }
    
    async loadCardsData() {
        // Charger données fraîches des cartes
    }
    
    async configureCard(cardId) {
        // Rediriger vers vcard-edit
        window.location.href = `?page=vcard-edit&card=${cardId}`;
    }
    
    async previewCard(cardId) {
        // Ouvrir aperçu public
        const card = this.findCard(cardId);
        window.open(card.public_url, '_blank');
    }
    
    async generateQR(cardId) {
        // Modal génération QR
    }
    
    async showStats(cardId) {
        // Modal stats rapides ou redirection
    }
    
    async updateCardStatus(cardId, status) {
        // AJAX pour activer/désactiver
    }
    
    async syncCard(cardId) {
        // AJAX synchronisation
    }
}
```

### **F4. Handlers AJAX**

```php
/**
 * Récupérer les cartes utilisateur avec données enrichies
 */
public function get_user_cards() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    try {
        $user_id = get_current_user_id();
        $cards = $this->get_user_vcards($user_id);
        
        $enriched_cards = [];
        foreach ($cards as $card) {
            $enriched_cards[] = [
                'id' => $card->ID,
                'identifier' => $this->get_card_identifier($card->ID),
                'profile' => $this->get_card_profile_summary($card->ID),
                'status' => $this->get_card_status($card->ID),
                'stats' => $this->get_card_quick_stats($card->ID),
                'urls' => [
                    'public' => get_permalink($card->ID),
                    'edit' => '?page=vcard-edit&card=' . $card->ID,
                    'qr' => $this->get_qr_url($card->ID)
                ]
            ];
        }
        
        wp_send_json_success([
            'cards' => $enriched_cards,
            'total' => count($enriched_cards),
            'global_stats' => $this->calculate_global_card_stats($cards)
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

/**
 * Mettre à jour le statut d'une carte
 */
public function update_card_status() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $card_id = intval($_POST['card_id'] ?? 0);
    $status = sanitize_text_field($_POST['status'] ?? '');
    
    if (!$card_id || !in_array($status, ['active', 'inactive'])) {
        wp_send_json_error(['message' => 'Paramètres invalides']);
        return;
    }
    
    try {
        update_post_meta($card_id, 'card_status', $status);
        
        wp_send_json_success([
            'message' => 'Statut mis à jour',
            'card_id' => $card_id,
            'new_status' => $status
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
```

---

## ⚡ **PLAN DE DÉVELOPPEMENT**

### **Phase 1 : Structure Base (3h)**
1. **Créer cards-list.php** avec logique adaptative
2. **Créer cards-manager.js** avec classe de base
3. **Ajouter handlers AJAX** de base
4. **Test affichage** simple sans interactions

### **Phase 2 : Tableau Interactif (3h)**
1. **Implémenter tableau** responsive avec toutes les colonnes
2. **Ajouter actions par carte** (aperçu, configuration)
3. **Système de tri et filtres**
4. **Tests multi-cartes**

### **Phase 3 : Actions Avancées (2h)**
1. **Modal QR Code** avec génération
2. **Stats rapides** par carte
3. **Actions groupées** (sélection multiple)
4. **Synchronisation** cartes physiques

### **Phase 4 : Finitions (1h)**
1. **Animations et transitions** fluides
2. **Gestion d'erreurs** complète
3. **Tests responsive** mobile
4. **Optimisations performance**

---

## ✅ **CRITÈRES DE VALIDATION**

### **Fonctionnalités :**
- [ ] Affichage adaptatif selon nombre de cartes
- [ ] Tableau interactif avec tri/filtres
- [ ] Actions par carte fonctionnelles
- [ ] Stats globales précises
- [ ] Modal QR Code opérationnelle

### **Technique :**
- [ ] Architecture standardisée respectée
- [ ] Performance optimisée (< 2s chargement)
- [ ] Compatible multi-navigateurs
- [ ] Responsive design parfait
- [ ] Gestion d'erreurs complète

### **UX :**
- [ ] Interface intuitive et claire
- [ ] Chargement progressif avec loading states
- [ ] Notifications utilisateur appropriées
- [ ] Cohérence visuelle avec dashboard
- [ ] Actions rapides et fluides

---

*Cette page Cards List sera la référence pour la gestion centralisée de toutes les cartes vCard utilisateur.*