# 📊 CAHIER DES CHARGES - Page Statistics Dashboard

**Version :** 1.0  
**Date :** 15/09/2025  
**Projet :** NFC France - Dashboard Unifié  
**Objectif :** Créer une page Statistics complète et adaptative selon les vCards utilisateur

---

## 🎯 **OBJECTIF PRINCIPAL**

Développer une page **Statistics unique** qui :
- **S'adapte automatiquement** selon les vCards de l'utilisateur
- **Affiche des statistiques consolidées** ou par profil vCard
- **Utilise l'architecture standardisée** (PHP/JS/AJAX séparés)
- **Intègre des graphiques interactifs** pour visualiser les données

---

## 📊 **ANALYSE FONCTIONNELLE**

### **A1. États d'Affichage**

#### **État 1 : Aucune vCard (Redirection)**
```php
if (empty($user_vcards)) {
    include 'partials/no-products-state.php';
    return;
}
```

#### **État 2 : Une seule vCard**
- **Interface simple** : Stats de la vCard unique
- **Titre** : "Mes Statistiques"
- **Pas de filtre** par profil

#### **État 3 : Plusieurs vCards**
- **Interface multi-profils** : Stats consolidées + filtrage
- **Titre** : "Statistiques Multi-Profils"
- **Filtre par profil** avec vue "Tous les profils"

### **A2. Métriques à Afficher**

#### **A2.1 Statistiques Principales (Cards)**
```
┌─ VUES PROFIL ────────┐  ┌─ CONTACTS GÉNÉRÉS ──┐  ┌─ SCANS NFC ─────────┐  ┌─ TAUX CONVERSION ───┐
│                      │  │                      │  │                      │  │                      │
│    1,247            │  │      89             │  │     156             │  │    7.1%             │
│  👁️ Cette période    │  │  📧 Nouveaux leads   │  │  📱 Scans physiques  │  │  📈 Vues → Contacts  │
│  +12% vs précédente │  │  +5 cette semaine   │  │  +23 ce mois        │  │  +1.2% ce mois      │
└──────────────────────┘  └──────────────────────┘  └──────────────────────┘  └──────────────────────┘
```

#### **A2.2 Graphiques de Tendances**
1. **Vues du profil** (Line Chart - 30 derniers jours)
2. **Contacts générés** (Bar Chart - Par semaine)
3. **Sources de trafic** (Pie Chart - QR, NFC, Direct)
4. **Heures d'activité** (Heatmap - Jours/Heures)

#### **A2.3 Tableaux de Données**
1. **Top Sources** (QR Code, NFC, Partage direct)
2. **Activité récente** (Dernières vues/contacts)
3. **Performance par période** (Jour, Semaine, Mois)

---

## 🏗️ **ARCHITECTURE TECHNIQUE**

### **F1. Fichiers à Créer**

#### **F1.1 Template Principal**
```
📁 wp-content/plugins/gtmi-vcard/templates/dashboard/
└── statistics.php                    # 🆕 Template adaptatif
```

#### **F1.2 Manager JavaScript**
```
📁 wp-content/plugins/gtmi-vcard/assets/js/dashboard/
└── statistics-manager.js             # 🆕 Gestion interface + AJAX
```

#### **F1.3 Handlers AJAX (dans ajax-handlers.php)**
```php
// 🆕 Nouvelles méthodes à ajouter
public function get_statistics_data()    // Récupérer stats période
public function export_statistics()      // Export CSV/PDF
public function get_chart_data()         // Données graphiques
```

#### **F1.4 CSS Spécifique**
```
📁 wp-content/plugins/gtmi-vcard/assets/css/dashboard/
└── statistics.css                    # 🆕 Styles graphiques
```

### **F2. Structure statistics.php**

```php
<?php
/**
 * Dashboard - Statistics
 * Page statistiques adaptative multi-vCard
 */

// Sécurité et vérifications
if (!defined('ABSPATH')) exit;
if (!is_user_logged_in()) wp_redirect(home_url('/login'));

// Logique métier
$user_id = get_current_user_id();
$user_vcards = nfc_get_user_vcard_profiles($user_id);

// Gestion des états
if (empty($user_vcards)) {
    include 'partials/no-products-state.php';
    return;
}

// Interface selon nombre de vCards
$show_profile_filter = count($user_vcards) > 1;
$page_title = $show_profile_filter ? "Statistiques Multi-Profils" : "Mes Statistiques";

// Période par défaut
$default_period = '30d'; // 30 derniers jours
$available_periods = [
    '7d' => '7 derniers jours',
    '30d' => '30 derniers jours', 
    '3m' => '3 derniers mois',
    '1y' => '1 an'
];

// Configuration pour JavaScript
$stats_config = [
    'user_id' => $user_id,
    'vcards' => $user_vcards,
    'show_profile_filter' => $show_profile_filter,
    'default_period' => $default_period,
    'periods' => $available_periods,
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce')
];

// Assets
wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js');
wp_enqueue_script('statistics-manager', 
    plugin_dir_url(__FILE__) . '../../assets/js/dashboard/statistics-manager.js',
    ['jquery', 'chart-js'], '1.0.0', true
);
wp_localize_script('statistics-manager', 'statisticsConfig', $stats_config);

wp_enqueue_style('statistics-css',
    plugin_dir_url(__FILE__) . '../../assets/css/dashboard/statistics.css'
);
?>

<!-- HTML de la page -->
<div class="dashboard-statistics">
    <!-- Header avec titre et période -->
    <div class="statistics-header">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h2><i class="fas fa-chart-line me-2"></i><?= $page_title ?></h2>
                <p class="text-muted mb-0">Analyse de performance de vos cartes NFC</p>
            </div>
            <div class="col-auto">
                <!-- Filtre période -->
                <select class="form-select" id="periodFilter">
                    <?php foreach ($available_periods as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $key === $default_period ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Filtre profil (si plusieurs vCards) -->
        <?php if ($show_profile_filter): ?>
        <div class="row mb-4">
            <div class="col-md-6">
                <select class="form-select" id="profileFilter">
                    <option value="">Tous les profils</option>
                    <?php foreach ($user_vcards as $vcard): ?>
                        <option value="<?= $vcard['vcard_id'] ?>">
                            <?= nfc_format_vcard_full_name($vcard['vcard_data']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-outline-primary" id="exportBtn">
                    <i class="fas fa-download me-2"></i>Exporter
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Stats principales (4 cards) -->
    <div class="statistics-cards">
        <div class="row mb-4" id="statsCards">
            <!-- Généré par JavaScript -->
        </div>
    </div>

    <!-- Graphiques -->
    <div class="statistics-charts">
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Évolution des vues</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="viewsChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sources de trafic</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="sourceChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Contacts générés</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="contactsChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Activité par heure</h5>
                    </div>
                    <div class="card-body">
                        <div id="heatmapChart">
                            <!-- Généré par JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading states -->
    <div class="loading-overlay d-none" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    </div>
</div>
```

---

## 🔧 **FONCTIONNALITÉS DÉTAILLÉES**

### **F3. Métriques Calculées**

#### **F3.1 Vues Profil**
```sql
-- Compter les vues par période
SELECT COUNT(*) as total_views
FROM vcard_analytics 
WHERE vcard_id IN (user_vcards)
  AND view_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
```

#### **F3.2 Contacts Générés**
```sql
-- Compter les nouveaux leads
SELECT COUNT(*) as total_contacts
FROM posts p
WHERE post_type = 'lead'
  AND post_status = 'publish'
  AND post_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  AND EXISTS (
    SELECT 1 FROM postmeta pm 
    WHERE pm.post_id = p.ID 
      AND pm.meta_key = 'linked_virtual_card' 
      AND pm.meta_value IN (user_vcards)
  )
```

#### **F3.3 Taux de Conversion**
```php
$conversion_rate = ($total_contacts / $total_views) * 100;
```

### **F4. JavaScript Manager Structure**

```javascript
class NFCStatisticsManager {
    constructor(config) {
        this.config = config;
        this.currentPeriod = config.default_period;
        this.currentProfile = '';
        this.charts = {};
        this.data = null;
        
        this.init();
    }
    
    async init() {
        console.log('📊 Initialisation Statistics Manager');
        this.bindEvents();
        await this.loadStatistics();
        this.renderCharts();
    }
    
    bindEvents() {
        // Filtre période
        document.getElementById('periodFilter').addEventListener('change', (e) => {
            this.currentPeriod = e.target.value;
            this.loadStatistics();
        });
        
        // Filtre profil (si multi-vCard)
        const profileFilter = document.getElementById('profileFilter');
        if (profileFilter) {
            profileFilter.addEventListener('change', (e) => {
                this.currentProfile = e.target.value;
                this.loadStatistics();
            });
        }
        
        // Export
        document.getElementById('exportBtn')?.addEventListener('click', () => {
            this.exportStatistics();
        });
    }
    
    async loadStatistics() {
        this.showLoading(true);
        
        try {
            const data = await this.callAjax('get_statistics_data', {
                period: this.currentPeriod,
                profile: this.currentProfile
            });
            
            this.data = data;
            this.renderStatsCards();
            this.updateCharts();
            
        } catch (error) {
            this.showNotification('Erreur lors du chargement des statistiques', 'error');
        } finally {
            this.showLoading(false);
        }
    }
    
    renderStatsCards() {
        const { stats } = this.data;
        
        const cardsHtml = `
            <div class="col-md-3">
                <div class="stat-card bg-primary text-white">
                    <div class="stat-value">${stats.total_views.toLocaleString()}</div>
                    <div class="stat-label">Vues du profil</div>
                    <div class="stat-change ${stats.views_change >= 0 ? 'positive' : 'negative'}">
                        <i class="fas fa-arrow-${stats.views_change >= 0 ? 'up' : 'down'}"></i>
                        ${Math.abs(stats.views_change)}% vs période précédente
                    </div>
                </div>
            </div>
            <!-- Autres cards... -->
        `;
        
        document.getElementById('statsCards').innerHTML = cardsHtml;
    }
    
    renderCharts() {
        // Chart.js pour les graphiques
        this.createViewsChart();
        this.createSourceChart();
        this.createContactsChart();
        this.createHeatmap();
    }
    
    // ... autres méthodes
}
```

### **F5. Handlers AJAX**

```php
/**
 * Récupérer les données statistiques
 */
public function get_statistics_data() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $user_id = get_current_user_id();
    $period = sanitize_text_field($_POST['period'] ?? '30d');
    $profile_id = intval($_POST['profile'] ?? 0);
    
    try {
        // Récupérer les vCards utilisateur
        $user_vcards = nfc_get_user_vcard_profiles($user_id);
        
        if (empty($user_vcards)) {
            wp_send_json_error(['message' => 'Aucune vCard trouvée']);
            return;
        }
        
        // Filtrer par profil si spécifié
        $target_vcards = $profile_id ? [$profile_id] : array_column($user_vcards, 'vcard_id');
        
        // Calculer les statistiques
        $stats = $this->calculate_statistics($target_vcards, $period);
        $charts_data = $this->get_charts_data($target_vcards, $period);
        
        wp_send_json_success([
            'stats' => $stats,
            'charts' => $charts_data,
            'period' => $period,
            'profile' => $profile_id
        ]);
        
    } catch (Exception $e) {
        error_log("❌ Erreur get_statistics_data: " . $e->getMessage());
        wp_send_json_error(['message' => 'Erreur lors du calcul des statistiques']);
    }
}

/**
 * Calculer les statistiques principales
 */
private function calculate_statistics($vcard_ids, $period) {
    // Convertir période en jours
    $days = $this->period_to_days($period);
    
    // Calculer vues, contacts, conversions, etc.
    // ... logique de calcul
    
    return [
        'total_views' => $total_views,
        'total_contacts' => $total_contacts,
        'total_scans' => $total_scans,
        'conversion_rate' => $conversion_rate,
        'views_change' => $views_change,
        'contacts_change' => $contacts_change
    ];
}
```

---

## ⚡ **PLAN DE DÉVELOPPEMENT**

### **Phase 1 : Structure Base (2h)**
1. **Créer statistics.php** avec logique adaptative
2. **Créer statistics-manager.js** avec classe de base
3. **Ajouter handlers AJAX** dans ajax-handlers.php
4. **Test affichage** sans données réelles

### **Phase 2 : Données et Calculs (2h)**
1. **Implémenter calcul des métriques** réelles
2. **Connecter API analytics** existante
3. **Tester avec données utilisateur** réelles
4. **Optimiser requêtes** de performance

### **Phase 3 : Graphiques (2h)**
1. **Intégrer Chart.js** pour visualisations
2. **Créer graphiques interactifs** (Line, Bar, Pie)
3. **Ajouter heatmap** activité
4. **Tests responsive** sur mobile

### **Phase 4 : Finitions (1h)**
1. **Export CSV/PDF** des statistiques
2. **Animations et transitions** fluides
3. **Gestion d'erreurs** complète
4. **Tests multi-navigateurs**

---

## ✅ **CRITÈRES DE VALIDATION**

### **Fonctionnalités :**
- [ ] Affichage adaptatif selon nombre de vCards
- [ ] Calcul correct de toutes les métriques
- [ ] Graphiques interactifs fonctionnels
- [ ] Filtrages période et profil opérationnels
- [ ] Export des données

### **Technique :**
- [ ] Architecture standardisée respectée
- [ ] Performance optimisée (< 2s chargement)
- [ ] Compatible multi-navigateurs
- [ ] Responsive design
- [ ] Gestion d'erreurs complète

### **UX :**
- [ ] Interface intuitive et claire
- [ ] Chargement progressif avec loading states
- [ ] Notifications utilisateur appropriées
- [ ] Cohérence visuelle avec le dashboard

---

*Cette page Statistics sera la référence pour l'architecture standardisée du dashboard, alliant performance technique et expérience utilisateur optimale.*