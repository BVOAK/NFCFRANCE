# 🏗️ ARCHITECTURE STANDARD - Dashboard NFC France

**Version :** 1.0  
**Date :** 15/09/2025  
**Projet :** NFC France - Dashboard Unifié  
**Objectif :** Standardiser l'architecture de toutes les pages du dashboard

---

## 🎯 **PRINCIPE DIRECTEUR**

**Modèle de référence :** Page `leads.php` - Architecture propre et maintenable

**Règle d'or :** Une page = 3 fichiers distincts avec responsabilités claires

---

## 📋 **ARCHITECTURE STANDARDISÉE**

### **F1. Structure des Fichiers par Page**

#### **F1.1 Template Principal (.php)**
```
wp-content/plugins/gtmi-vcard/templates/dashboard/
├── leads.php           ✅ TERMINÉ
├── statistics.php      🎯 EN COURS
├── cards-list.php      📋 À FAIRE
├── vcard-edit.php      📋 À FAIRE
├── overview.php        📋 À FAIRE
└── avis-google.php     📋 À FAIRE
```

**Responsabilité :** PHP/HTML UNIQUEMENT
- Logique métier PHP
- Génération HTML
- Variables de configuration pour JS
- Aucun JavaScript inline

#### **F1.2 Manager JavaScript (.js)**
```
wp-content/plugins/gtmi-vcard/assets/js/dashboard/
├── contacts-manager.js     ✅ TERMINÉ
├── statistics-manager.js   🎯 EN COURS
├── cards-manager.js        📋 À FAIRE
├── vcard-editor.js         📋 À FAIRE
├── overview-manager.js     📋 À FAIRE
└── avis-manager.js         📋 À FAIRE
```

**Responsabilité :** JavaScript UNIQUEMENT
- Interface utilisateur
- Appels AJAX
- Gestion des événements
- Manipulation DOM

#### **F1.3 Handlers AJAX (ajax-handlers.php)**
```php
// Dans ajax-handlers.php - Fonctions par page
class NFC_Dashboard_Ajax {
    
    // ========== LEADS ==========
    public function get_user_leads() { }        ✅ TERMINÉ
    public function update_lead() { }           ✅ TERMINÉ
    public function delete_lead() { }           ✅ TERMINÉ
    
    // ========== STATISTICS ==========
    public function get_statistics_data() { }   🎯 EN COURS
    public function export_statistics() { }     🎯 EN COURS
    
    // ========== CARDS ==========
    public function get_user_cards() { }        📋 À FAIRE
    public function reorder_cards() { }         📋 À FAIRE
    
    // ========== VCARD EDIT ==========
    public function save_vcard_field() { }      📋 À FAIRE
    public function upload_vcard_image() { }    📋 À FAIRE
    
    // ========== OVERVIEW ==========
    public function get_dashboard_overview() { } 📋 À FAIRE
    
    // ========== AVIS GOOGLE ==========
    public function get_google_reviews() { }    📋 À FAIRE
    public function sync_reviews() { }          📋 À FAIRE
}
```

**Responsabilité :** AJAX UNIQUEMENT
- Traitement des requêtes AJAX
- Validation des données
- Retour JSON standardisé

---

## 🔧 **STANDARDS TECHNIQUES**

### **F2. Template PHP (.php)**

#### **F2.1 Structure Obligatoire**
```php
<?php
/**
 * Dashboard - [NOM_PAGE]
 * Architecture standardisée NFC France
 */

// 1. VÉRIFICATIONS SÉCURITÉ
if (!defined('ABSPATH')) exit;
if (!is_user_logged_in()) wp_redirect(home_url('/login'));

// 2. LOGIQUE MÉTIER
$user_id = get_current_user_id();
$user_vcards = nfc_get_user_vcard_profiles($user_id);

// Détection des états
if (empty($user_vcards)) {
    include 'partials/no-products-state.php';
    return;
}

// Variables pour JavaScript
$page_data = [
    'user_id' => $user_id,
    'vcards' => $user_vcards,
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce')
];

// 3. CHARGEMENT ASSETS
wp_enqueue_script('[nom-page]-manager', 
    plugin_dir_url(__FILE__) . '../../assets/js/dashboard/[nom-page]-manager.js',
    ['jquery'], '1.0.0', true
);
wp_localize_script('[nom-page]-manager', '[nomPage]Config', $page_data);

// 4. HTML UNIQUEMENT
?>

<div class="dashboard-[nom-page]">
    <!-- HTML de la page -->
</div>

<script>
// UNIQUEMENT les variables de config
const [nomPage]Config = <?= json_encode($page_data) ?>;
</script>
```

#### **F2.2 Standards PHP**
- **Variables camelCase** : `$pageData`, `$userVcards`
- **Fonctions snake_case** : `nfc_get_user_data()`
- **Classes PascalCase** : `NFC_Statistics_Manager`
- **Constantes UPPER_CASE** : `NFC_PLUGIN_VERSION`

### **F3. Manager JavaScript (.js)**

#### **F3.1 Structure Obligatoire**
```javascript
/**
 * [NOM_PAGE] Manager - Dashboard NFC France
 * Architecture standardisée
 */

class NFC[NomPage]Manager {
    constructor(config) {
        this.config = config;
        this.data = [];
        this.filters = {};
        this.currentView = 'default';
        
        this.init();
    }
    
    init() {
        console.log('🚀 Initialisation [NomPage] Manager');
        this.bindEvents();
        this.loadData();
    }
    
    bindEvents() {
        // Événements de la page
    }
    
    async loadData() {
        // Chargement des données via AJAX
    }
    
    render() {
        // Rendu de l'interface
    }
    
    // === GESTION AJAX ===
    async callAjax(action, data = {}) {
        const ajaxData = {
            action: `nfc_${action}`,
            nonce: this.config.nonce,
            ...data
        };
        
        try {
            const response = await fetch(this.config.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(ajaxData)
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.data?.message || 'Erreur AJAX');
            }
            
            return result.data;
        } catch (error) {
            console.error(`❌ Erreur ${action}:`, error);
            this.showNotification(error.message, 'error');
            throw error;
        }
    }
    
    showNotification(message, type = 'info') {
        // Système de notifications unifié
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    if (typeof [nomPage]Config !== 'undefined') {
        window.[nomPage]Manager = new NFC[NomPage]Manager([nomPage]Config);
    }
});
```

#### **F3.2 Standards JavaScript**
- **Classes PascalCase** : `NFCStatisticsManager`
- **Méthodes camelCase** : `loadStatistics()`, `exportData()`
- **Variables camelCase** : `currentFilter`, `selectedPeriod`
- **Constantes UPPER_CASE** : `DEFAULT_PERIOD`, `API_ENDPOINTS`

### **F4. Handlers AJAX (ajax-handlers.php)**

#### **F4.1 Structure Obligatoire**
```php
/**
 * [Action] - [Description]
 * @return void JSON Response
 */
public function [action_name]() {
    // 1. VÉRIFICATION NONCE
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    // 2. VALIDATION DONNÉES
    $param1 = sanitize_text_field($_POST['param1'] ?? '');
    $param2 = intval($_POST['param2'] ?? 0);
    
    if (!$param1 || !$param2) {
        wp_send_json_error(['message' => 'Paramètres manquants']);
        return;
    }
    
    // 3. LOGIQUE MÉTIER
    try {
        $result = $this->process_data($param1, $param2);
        
        error_log("✅ Action réussie: {$action_name}");
        wp_send_json_success([
            'message' => 'Action réussie',
            'data' => $result
        ]);
        
    } catch (Exception $e) {
        error_log("❌ Erreur {$action_name}: " . $e->getMessage());
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
```

#### **F4.2 Standards Retour JSON**
```php
// SUCCESS
wp_send_json_success([
    'message' => 'Message utilisateur',
    'data' => $data,           // Données utiles
    'meta' => $meta_info       // Informations complémentaires
]);

// ERROR
wp_send_json_error([
    'message' => 'Message d\'erreur utilisateur',
    'code' => 'ERROR_CODE',   // Code d'erreur technique
    'debug' => $debug_info     // Info debug (dev uniquement)
]);
```

---

## 📦 **ASSETS ET DÉPENDANCES**

### **F5. Chargement Uniforme**

#### **F5.1 CSS Commun**
```php
// Dans chaque template
wp_enqueue_style('nfc-dashboard-common', 
    plugin_dir_url(__FILE__) . '../../assets/css/dashboard-common.css'
);
```

#### **F5.2 JavaScript Dépendances**
```php
// Dépendances standard pour tous les managers
$dependencies = ['jquery', 'bootstrap'];

// Dépendances spécifiques selon la page
if ($page === 'statistics') {
    $dependencies[] = 'chart-js';
}

wp_enqueue_script('[page]-manager', $script_url, $dependencies, '1.0.0', true);
```

---

## ✅ **CHECKLIST VALIDATION**

### **Pour chaque nouvelle page :**

#### **Template PHP :**
- [ ] Vérifications sécurité en place
- [ ] Logique métier séparée du HTML
- [ ] Variables de config pour JS
- [ ] Aucun JavaScript inline
- [ ] Gestion des états vides

#### **Manager JavaScript :**
- [ ] Classe principale avec init()
- [ ] Méthode callAjax() standardisée
- [ ] Gestion d'erreurs uniforme
- [ ] Notifications utilisateur
- [ ] Pas de variables globales

#### **Handlers AJAX :**
- [ ] Vérification nonce
- [ ] Validation des paramètres
- [ ] Retour JSON standardisé
- [ ] Gestion d'erreurs
- [ ] Logs pour debug

#### **Intégration :**
- [ ] Chargement des assets correct
- [ ] Pas de conflits avec autres pages
- [ ] Performance optimisée
- [ ] Compatible multi-vCard

---

## 🎯 **ROADMAP PAGES**

1. **statistics.php** 🎯 **PRIORITÉ 1**
2. **cards-list.php** 📋 Gestion des cartes utilisateur
3. **vcard-edit.php** 📋 Édition profil vCard
4. **overview.php** 📋 Dashboard principal multi-profils
5. **avis-google.php** 📋 Système avis Google

---

*Cette architecture standardisée garantit la maintenabilité, la cohérence et l'évolutivité de tout le dashboard NFC France.*