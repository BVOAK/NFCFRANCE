# 👁️ CAHIER DES CHARGES - Page Preview

**Version :** 1.0  
**Date :** 15/09/2025  
**Projet :** NFC France - Dashboard Unifié  
**Objectif :** Créer une page de preview fidèle des vCards publiques avec outils de test

---

## 🎯 **OBJECTIF PRINCIPAL**

Développer une page **Preview unique** qui :
- **Affiche un aperçu fidèle** de la vCard publique dans le dashboard
- **Permet le test en temps réel** des fonctionnalités (boutons, liens)
- **Simule différents appareils** (mobile, tablet, desktop)
- **Intègre des outils de debug** pour les développeurs
- **Utilise l'architecture standardisée** (PHP/JS/AJAX séparés)

---

## 📊 **ANALYSE FONCTIONNELLE**

### **A1. Modes de Preview**

#### **A1.1 Mode Standard (Utilisateur)**
```
┌─ CONTROLS BAR ──────────────────────────────────────────────────────────────┐
│  📱 Mobile | 📟 Tablet | 💻 Desktop | 🔄 Refresh | 🔗 Ouvrir public | ⚙️ Test │
└─────────────────────────────────────────────────────────────────────────────┘
┌─ PREVIEW FRAME ─────────────────────────────────────────────────────────────┐
│                                                                             │
│                    📱 APERÇU VCARD RESPONSIVE                               │
│                                                                             │
│                    Rendu IDENTIQUE à la version publique                   │
│                                                                             │
│                    - Boutons fonctionnels                                  │
│                    - Liens testables                                       │
│                    - Animations préservées                                 │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

#### **A1.2 Mode Debug (Développeur)**
```
┌─ SPLIT VIEW ────────────────────────────────────────────────────────────────┐
│                              │                                              │
│      📱 PREVIEW             │         🔧 DEBUG PANEL                     │
│                              │                                              │
│  Aperçu vCard              │  📊 Analytics temps réel                   │
│  + Overlays debug          │  🔍 Inspecteur HTML/CSS                    │
│                              │  📋 Console JavaScript                     │
│                              │  🌐 Simulation sources trafic              │
│                              │  📱 Tests compatibilité                    │
│                              │                                              │
└──────────────────────────────┴──────────────────────────────────────────────┘
```

### **A2. Fonctionnalités par Mode**

#### **A2.1 Fonctionnalités Standard**
1. **🖥️ Responsive Preview** : Mobile/Tablet/Desktop
2. **🔄 Refresh Temps Réel** : Mise à jour instantanée
3. **🔗 Test Boutons** : Email, téléphone, réseaux sociaux
4. **📱 Simulation QR/NFC** : Test sources de trafic
5. **👁️ Aperçu Public** : Ouverture nouvel onglet
6. **📊 Analytics Live** : Compteurs en temps réel

#### **A2.2 Fonctionnalités Debug**
1. **🔍 Inspecteur Elements** : HTML/CSS live
2. **📋 Console JavaScript** : Erreurs et warnings
3. **🌐 Simulation Sources** : QR Code, NFC scan, direct
4. **📱 Tests Compatibilité** : Multi-navigateurs
5. **⚡ Performance Metrics** : Temps de chargement
6. **🔧 Outils Développeur** : Variables exposées

### **A3. États de la Page**

#### **A3.1 État Chargement**
```
┌─ SKELETON UI ───────────────────────────────────────────────────────────────┐
│  ⬜⬜⬜ Controls placeholder                                                  │
│                                                                             │
│  ┌─ Preview Frame ──────────────────────────────────────────────────────┐   │
│  │                                                                      │   │
│  │                     🔄 Chargement...                                │   │
│  │                                                                      │   │
│  │              Génération aperçu en cours                            │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────┘
```

#### **A3.2 État Erreur**
```
┌─ ERROR STATE ───────────────────────────────────────────────────────────────┐
│  ⚠️ Erreur lors du chargement de l'aperçu                                  │
│                                                                             │
│  • vCard introuvable ou incomplète                                         │
│  • Problème de connectivité                                                │
│  • Configuration invalide                                                  │
│                                                                             │
│  [🔄 Réessayer]  [⚙️ Configurer vCard]                                    │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🏗️ **ARCHITECTURE TECHNIQUE**

### **F1. Fichiers selon Standard**

#### **F1.1 Template Principal**
```
📁 wp-content/plugins/gtmi-vcard/templates/dashboard/
└── preview.php                       # 🆕 Page preview avec iframe
```

#### **F1.2 Manager JavaScript**
```
📁 wp-content/plugins/gtmi-vcard/assets/js/dashboard/
└── preview-manager.js                # 🆕 Contrôle preview + debug
```

#### **F1.3 Handlers AJAX (dans ajax-handlers.php)**
```php
// 🆕 Nouvelles méthodes à ajouter
public function get_vcard_preview_html()  // Générer HTML preview
public function refresh_preview()         // Actualiser aperçu
public function log_preview_action()      // Logger actions test
public function get_debug_info()          // Infos debug vCard
public function simulate_traffic_source() // Simuler source trafic
public function test_vcard_links()        // Tester validité liens
```

#### **F1.4 CSS et Assets**
```
📁 wp-content/plugins/gtmi-vcard/assets/css/dashboard/
├── preview.css                       # 🆕 Styles page preview
└── preview-responsive.css            # 🆕 Breakpoints devices

📁 wp-content/plugins/gtmi-vcard/assets/js/vendor/
└── device-simulator.js               # 🆕 Simulation devices
```

### **F2. Structure preview.php**

```php
<?php
/**
 * Dashboard - Preview
 * Aperçu vCard avec outils de test
 */

// 1. VÉRIFICATIONS SÉCURITÉ
if (!defined('ABSPATH')) exit;
if (!is_user_logged_in()) wp_redirect(home_url('/login'));

// 2. LOGIQUE MÉTIER
$user_id = get_current_user_id();
$card_id = intval($_GET['card'] ?? 0);
$debug_mode = isset($_GET['debug']) && current_user_can('manage_options');

// Déterminer la vCard à prévisualiser
if ($card_id) {
    // Vérifier que l'utilisateur possède cette vCard
    $vcard = get_post($card_id);
    if (!$vcard || $vcard->post_author != $user_id) {
        wp_redirect('?page=cards-list');
        exit;
    }
} else {
    // Première vCard de l'utilisateur
    $user_vcards = $this->get_user_vcards($user_id);
    if (empty($user_vcards)) {
        wp_redirect('?page=cards-list');
        exit;
    }
    $vcard = $user_vcards[0];
    $card_id = $vcard->ID;
}

// URLs et données
$public_url = get_permalink($card_id);
$preview_data = $this->get_vcard_preview_data($card_id);

// Configuration JavaScript
$preview_config = [
    'card_id' => $card_id,
    'public_url' => $public_url,
    'debug_mode' => $debug_mode,
    'preview_data' => $preview_data,
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_dashboard_nonce'),
    'devices' => [
        'mobile' => ['width' => 375, 'height' => 667, 'name' => 'iPhone SE'],
        'tablet' => ['width' => 768, 'height' => 1024, 'name' => 'iPad'],
        'desktop' => ['width' => 1200, 'height' => 800, 'name' => 'Desktop']
    ],
    'test_sources' => ['direct', 'qr_code', 'nfc_scan', 'social']
];
?>

<!-- CSS -->
<link rel="stylesheet" href="<?= plugin_dir_url(__FILE__) ?>../../assets/css/dashboard/preview.css">

<!-- Configuration JavaScript -->
<script>window.PREVIEW_CONFIG = <?= json_encode($preview_config) ?>;</script>

<!-- HTML PRINCIPAL -->
<div class="dashboard-preview <?= $debug_mode ? 'debug-mode' : '' ?>">
    
    <!-- CONTROLS BAR -->
    <div class="preview-controls">
        <div class="d-flex justify-content-between align-items-center">
            <div class="preview-title">
                <h4><i class="fas fa-eye me-2"></i>Aperçu vCard</h4>
                <span class="text-muted"><?= esc_html(get_post_meta($card_id, 'firstname', true) . ' ' . get_post_meta($card_id, 'lastname', true)) ?></span>
            </div>
            
            <div class="preview-actions">
                <!-- Device selector -->
                <div class="btn-group me-3" role="group">
                    <input type="radio" class="btn-check" name="device" id="device-mobile" value="mobile" checked>
                    <label class="btn btn-outline-primary" for="device-mobile">
                        <i class="fas fa-mobile-alt"></i> Mobile
                    </label>
                    
                    <input type="radio" class="btn-check" name="device" id="device-tablet" value="tablet">
                    <label class="btn btn-outline-primary" for="device-tablet">
                        <i class="fas fa-tablet-alt"></i> Tablet
                    </label>
                    
                    <input type="radio" class="btn-check" name="device" id="device-desktop" value="desktop">
                    <label class="btn btn-outline-primary" for="device-desktop">
                        <i class="fas fa-desktop"></i> Desktop
                    </label>
                </div>
                
                <!-- Actions -->
                <button class="btn btn-outline-secondary me-2" id="refresh-preview">
                    <i class="fas fa-sync-alt"></i> Actualiser
                </button>
                
                <button class="btn btn-primary me-2" id="open-public">
                    <i class="fas fa-external-link-alt"></i> Ouvrir public
                </button>
                
                <?php if (current_user_can('manage_options')): ?>
                <button class="btn btn-outline-warning" id="toggle-debug">
                    <i class="fas fa-bug"></i> Debug
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Traffic source simulator -->
        <div class="traffic-simulator mt-3">
            <label class="form-label">Simuler source de trafic :</label>
            <div class="btn-group btn-group-sm">
                <input type="radio" class="btn-check" name="source" id="source-direct" value="direct" checked>
                <label class="btn btn-outline-info" for="source-direct">🔗 Direct</label>
                
                <input type="radio" class="btn-check" name="source" id="source-qr" value="qr_code">
                <label class="btn btn-outline-info" for="source-qr">📱 QR Code</label>
                
                <input type="radio" class="btn-check" name="source" id="source-nfc" value="nfc_scan">
                <label class="btn btn-outline-info" for="source-nfc">🏷️ NFC Scan</label>
                
                <input type="radio" class="btn-check" name="source" id="source-social" value="social">
                <label class="btn btn-outline-info" for="source-social">📲 Social</label>
            </div>
        </div>
    </div>
    
    <!-- PREVIEW AREA -->
    <div class="preview-area">
        <?php if ($debug_mode): ?>
        <!-- Split view : Preview + Debug -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="preview-frame-container">
                    <div class="device-frame" id="device-frame">
                        <iframe id="preview-iframe" src="<?= esc_url($public_url) ?>?preview=1&source=direct"></iframe>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="debug-panel">
                    <!-- Debug content -->
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Standard view : Preview seul -->
        <div class="preview-frame-container">
            <div class="device-frame" id="device-frame">
                <iframe id="preview-iframe" src="<?= esc_url($public_url) ?>?preview=1&source=direct"></iframe>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- ANALYTICS LIVE -->
    <div class="preview-analytics mt-4">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="analytics-card">
                    <div class="analytics-value" id="live-views">0</div>
                    <div class="analytics-label">Vues aujourd'hui</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="analytics-card">
                    <div class="analytics-value" id="live-clicks">0</div>
                    <div class="analytics-label">Clics boutons</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="analytics-card">
                    <div class="analytics-value" id="live-shares">0</div>
                    <div class="analytics-label">Partages</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="analytics-card">
                    <div class="analytics-value" id="live-time">0s</div>
                    <div class="analytics-label">Temps moyen</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="<?= plugin_dir_url(__FILE__) ?>../../assets/js/dashboard/preview-manager.js"></script>
```

### **F3. Structure preview-manager.js**

```javascript
class NFCPreviewManager {
    constructor(config) {
        this.config = config;
        this.currentDevice = 'mobile';
        this.currentSource = 'direct';
        this.debugMode = config.debug_mode;
        this.liveStats = { views: 0, clicks: 0, shares: 0, time: 0 };
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializePreview();
        this.startLiveAnalytics();
        
        if (this.debugMode) {
            this.initializeDebugPanel();
        }
    }
    
    setupEventListeners() {
        // Device switcher
        $('input[name="device"]').on('change', this.switchDevice.bind(this));
        
        // Traffic source simulator
        $('input[name="source"]').on('change', this.switchTrafficSource.bind(this));
        
        // Actions
        $('#refresh-preview').on('click', this.refreshPreview.bind(this));
        $('#open-public').on('click', this.openPublicView.bind(this));
        $('#toggle-debug').on('click', this.toggleDebugMode.bind(this));
        
        // Iframe events
        $('#preview-iframe').on('load', this.onIframeLoad.bind(this));
    }
    
    switchDevice(event) {
        this.currentDevice = event.target.value;
        this.updateDeviceFrame();
        this.logAction('device_switch', { device: this.currentDevice });
    }
    
    updateDeviceFrame() {
        const device = this.config.devices[this.currentDevice];
        const frame = document.getElementById('device-frame');
        const iframe = document.getElementById('preview-iframe');
        
        // Appliquer les dimensions
        frame.style.width = device.width + 'px';
        frame.style.height = device.height + 'px';
        frame.className = `device-frame device-${this.currentDevice}`;
        
        // Animation de transition
        frame.classList.add('transitioning');
        setTimeout(() => {
            frame.classList.remove('transitioning');
        }, 300);
        
        console.log(`📱 Switched to ${device.name} (${device.width}x${device.height})`);
    }
    
    switchTrafficSource(event) {
        this.currentSource = event.target.value;
        this.updatePreviewUrl();
        this.logAction('source_switch', { source: this.currentSource });
    }
    
    updatePreviewUrl() {
        const iframe = document.getElementById('preview-iframe');
        const newUrl = new URL(this.config.public_url);
        
        // Ajouter paramètres
        newUrl.searchParams.set('preview', '1');
        newUrl.searchParams.set('source', this.currentSource);
        newUrl.searchParams.set('device', this.currentDevice);
        newUrl.searchParams.set('_t', Date.now()); // Cache busting
        
        iframe.src = newUrl.toString();
        
        console.log(`🌐 Updated preview URL: ${newUrl.toString()}`);
    }
    
    async refreshPreview() {
        console.log('🔄 Refreshing preview...');
        
        // Afficher loading
        this.showLoading(true);
        
        try {
            // Récupérer données fraîches
            const response = await this.callAjax('nfc_refresh_preview', {
                card_id: this.config.card_id
            });
            
            if (response.success) {
                // Recharger iframe
                this.updatePreviewUrl();
                this.showNotification('Aperçu actualisé', 'success');
            } else {
                throw new Error(response.data.message);
            }
            
        } catch (error) {
            this.showNotification('Erreur actualisation: ' + error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }
    
    openPublicView() {
        const url = new URL(this.config.public_url);
        url.searchParams.set('source', this.currentSource);
        
        window.open(url.toString(), '_blank');
        
        this.logAction('public_open', { 
            source: this.currentSource,
            device: this.currentDevice 
        });
    }
    
    toggleDebugMode() {
        this.debugMode = !this.debugMode;
        
        if (this.debugMode) {
            this.initializeDebugPanel();
            document.querySelector('.dashboard-preview').classList.add('debug-mode');
        } else {
            document.querySelector('.dashboard-preview').classList.remove('debug-mode');
        }
        
        console.log(`🔧 Debug mode: ${this.debugMode ? 'ON' : 'OFF'}`);
    }
    
    initializeDebugPanel() {
        if (!this.debugMode) return;
        
        const debugPanel = document.querySelector('.debug-panel');
        if (!debugPanel) return;
        
        debugPanel.innerHTML = `
            <div class="debug-tabs">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-tab="analytics">📊 Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-tab="console">📋 Console</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-tab="inspector">🔍 Inspector</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-tab="network">🌐 Network</a>
                    </li>
                </ul>
            </div>
            <div class="debug-content">
                <div class="debug-tab-content" id="debug-analytics">
                    <!-- Real-time analytics -->
                </div>
                <div class="debug-tab-content" id="debug-console" style="display: none;">
                    <!-- JavaScript console -->
                </div>
                <div class="debug-tab-content" id="debug-inspector" style="display: none;">
                    <!-- HTML/CSS inspector -->
                </div>
                <div class="debug-tab-content" id="debug-network" style="display: none;">
                    <!-- Network requests -->
                </div>
            </div>
        `;
        
        this.setupDebugTabs();
    }
    
    startLiveAnalytics() {
        // Polling analytics en temps réel
        setInterval(() => {
            this.updateLiveStats();
        }, 5000); // Toutes les 5 secondes
    }
    
    async updateLiveStats() {
        try {
            const response = await this.callAjax('nfc_get_live_stats', {
                card_id: this.config.card_id
            });
            
            if (response.success) {
                this.liveStats = response.data;
                this.renderLiveStats();
            }
            
        } catch (error) {
            console.warn('⚠️ Erreur live stats:', error);
        }
    }
    
    renderLiveStats() {
        document.getElementById('live-views').textContent = this.liveStats.views || 0;
        document.getElementById('live-clicks').textContent = this.liveStats.clicks || 0;
        document.getElementById('live-shares').textContent = this.liveStats.shares || 0;
        document.getElementById('live-time').textContent = (this.liveStats.avg_time || 0) + 's';
    }
    
    onIframeLoad() {
        console.log('✅ Preview iframe loaded');
        this.showLoading(false);
        
        // Tenter d'accéder au contenu iframe (si même origine)
        try {
            const iframeDoc = document.getElementById('preview-iframe').contentDocument;
            if (iframeDoc) {
                this.setupIframeInteractions(iframeDoc);
            }
        } catch (error) {
            console.log('ℹ️ Cross-origin iframe, interactions limitées');
        }
    }
    
    setupIframeInteractions(iframeDoc) {
        // Écouter les clics dans l'iframe
        iframeDoc.addEventListener('click', (event) => {
            const element = event.target;
            
            // Logger les clics sur les éléments interactifs
            if (element.tagName === 'A' || element.tagName === 'BUTTON') {
                this.logAction('iframe_click', {
                    element: element.tagName,
                    href: element.href || '',
                    text: element.textContent.trim()
                });
            }
        });
        
        // Détecter le scroll
        iframeDoc.addEventListener('scroll', () => {
            const scrollPercent = Math.round(
                (iframeDoc.documentElement.scrollTop / 
                (iframeDoc.documentElement.scrollHeight - iframeDoc.documentElement.clientHeight)) * 100
            );
            
            if (scrollPercent > 0) {
                this.logAction('iframe_scroll', { percent: scrollPercent });
            }
        });
    }
    
    async logAction(action, data = {}) {
        try {
            await this.callAjax('nfc_log_preview_action', {
                card_id: this.config.card_id,
                action: action,
                data: data,
                device: this.currentDevice,
                source: this.currentSource
            });
            
            console.log(`📝 Logged action: ${action}`, data);
            
        } catch (error) {
            console.warn('⚠️ Erreur log action:', error);
        }
    }
    
    showLoading(show) {
        const iframe = document.getElementById('preview-iframe');
        const frame = document.getElementById('device-frame');
        
        if (show) {
            frame.classList.add('loading');
            iframe.style.opacity = '0.5';
        } else {
            frame.classList.remove('loading');
            iframe.style.opacity = '1';
        }
    }
    
    showNotification(message, type = 'info') {
        // Toast notification simple
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : 'info'}-circle me-2"></i>
            ${message}
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    async callAjax(action, data = {}) {
        const requestData = {
            action: action,
            nonce: this.config.nonce,
            ...data
        };
        
        const response = await fetch(this.config.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(requestData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        return result;
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    if (typeof PREVIEW_CONFIG !== 'undefined') {
        window.previewManager = new NFCPreviewManager(PREVIEW_CONFIG);
    }
});
```

### **F4. Handlers AJAX Principaux**

```php
/**
 * Générer HTML preview vCard
 */
public function get_vcard_preview_html() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $card_id = intval($_POST['card_id'] ?? 0);
    $device = sanitize_text_field($_POST['device'] ?? 'mobile');
    $source = sanitize_text_field($_POST['source'] ?? 'direct');
    
    if (!$card_id || !$this->user_owns_card($card_id)) {
        wp_send_json_error(['message' => 'Non autorisé']);
        return;
    }
    
    try {
        // Générer HTML complet de la vCard
        $vcard_html = $this->render_vcard_html($card_id, [
            'device' => $device,
            'source' => $source,
            'preview_mode' => true
        ]);
        
        wp_send_json_success([
            'html' => $vcard_html,
            'device' => $device,
            'source' => $source
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

/**
 * Actualiser aperçu
 */
public function refresh_preview() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $card_id = intval($_POST['card_id'] ?? 0);
    
    if (!$card_id || !$this->user_owns_card($card_id)) {
        wp_send_json_error(['message' => 'Non autorisé']);
        return;
    }
    
    try {
        // Vider les caches
        $this->clear_vcard_cache($card_id);
        
        // Récupérer données fraîches
        $fresh_data = $this->get_vcard_preview_data($card_id);
        
        wp_send_json_success([
            'message' => 'Preview actualisé',
            'data' => $fresh_data,
            'timestamp' => current_time('timestamp')
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

/**
 * Logger action preview
 */
public function log_preview_action() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $card_id = intval($_POST['card_id'] ?? 0);
    $action = sanitize_text_field($_POST['action'] ?? '');
    $data = $_POST['data'] ?? [];
    $device = sanitize_text_field($_POST['device'] ?? '');
    $source = sanitize_text_field($_POST['source'] ?? '');
    
    if (!$card_id || !$action) {
        wp_send_json_error(['message' => 'Paramètres manquants']);
        return;
    }
    
    try {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'nfc_preview_logs',
            [
                'card_id' => $card_id,
                'user_id' => get_current_user_id(),
                'action' => $action,
                'action_data' => json_encode($data),
                'device' => $device,
                'source' => $source,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'created_at' => current_time('mysql')
            ]
        );
        
        wp_send_json_success(['message' => 'Action logged']);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

/**
 * Obtenir statistiques temps réel
 */
public function get_live_stats() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $card_id = intval($_POST['card_id'] ?? 0);
    
    if (!$card_id || !$this->user_owns_card($card_id)) {
        wp_send_json_error(['message' => 'Non autorisé']);
        return;
    }
    
    try {
        global $wpdb;
        
        $today = date('Y-m-d');
        $analytics_table = $wpdb->prefix . 'nfc_analytics';
        
        // Stats du jour
        $daily_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as views,
                COUNT(DISTINCT session_id) as unique_visitors,
                AVG(session_duration) as avg_time
            FROM {$analytics_table}
            WHERE vcard_id = %d 
              AND DATE(view_datetime) = %s
        ", $card_id, $today));
        
        // Clics boutons (si tracking activé)
        $button_clicks = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->prefix}nfc_actions
            WHERE vcard_id = %d 
              AND DATE(action_datetime) = %s
              AND action_type IN ('phone_click', 'email_click', 'website_click')
        ", $card_id, $today));
        
        wp_send_json_success([
            'views' => intval($daily_stats->views ?? 0),
            'unique_visitors' => intval($daily_stats->unique_visitors ?? 0),
            'clicks' => intval($button_clicks ?? 0),
            'avg_time' => intval($daily_stats->avg_time ?? 0),
            'shares' => 0 // TODO: Implémenter tracking partages
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

/**
 * Tester validité des liens vCard
 */
public function test_vcard_links() {
    check_ajax_referer('nfc_dashboard_nonce', 'nonce');
    
    $card_id = intval($_POST['card_id'] ?? 0);
    
    if (!$card_id || !$this->user_owns_card($card_id)) {
        wp_send_json_error(['message' => 'Non autorisé']);
        return;
    }
    
    try {
        $vcard_data = $this->get_complete_vcard_data($card_id);
        $test_results = [];
        
        // Tester email
        if ($email = $vcard_data['email'] ?? '') {
            $test_results['email'] = [
                'value' => $email,
                'valid' => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
                'type' => 'email'
            ];
        }
        
        // Tester téléphone
        if ($phone = $vcard_data['phone'] ?? '') {
            $test_results['phone'] = [
                'value' => $phone,
                'valid' => preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $phone),
                'type' => 'phone'
            ];
        }
        
        // Tester URLs
        $url_fields = ['website', 'linkedin', 'facebook', 'instagram'];
        foreach ($url_fields as $field) {
            if ($url = $vcard_data[$field] ?? '') {
                $test_results[$field] = [
                    'value' => $url,
                    'valid' => filter_var($url, FILTER_VALIDATE_URL) !== false,
                    'type' => 'url',
                    'reachable' => $this->test_url_reachable($url)
                ];
            }
        }
        
        wp_send_json_success([
            'tests' => $test_results,
            'total' => count($test_results),
            'valid' => count(array_filter($test_results, fn($test) => $test['valid']))
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

/**
 * Helper: Tester si URL est accessible
 */
private function test_url_reachable($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code >= 200 && $http_code < 400;
}
```

---

## ⚡ **PLAN DE DÉVELOPPEMENT**

### **Phase 1 : Structure Base (3h)**
1. **Créer preview.php** avec iframe responsive
2. **Device switcher** fonctionnel
3. **Controls bar** avec actions de base
4. **CSS responsive** pour différents devices

### **Phase 2 : Simulation Avancée (3h)**
1. **Traffic source simulation** avec paramètres URL
2. **Live refresh** preview sans rechargement page
3. **Device frames** avec animations transitions
4. **Analytics temps réel** basiques

### **Phase 3 : Mode Debug (3h)**
1. **Debug panel** avec onglets
2. **Console JavaScript** capture erreurs
3. **Network monitoring** requêtes
4. **Performance metrics** temps chargement

### **Phase 4 : Fonctionnalités Avancées (2h)**
1. **Test automatique liens** validation URLs
2. **Screenshot generator** pour documentation
3. **Sharing tools** partage aperçu
4. **Export preview** HTML statique

### **Phase 5 : Finitions (1h)**
1. **Animations fluides** device switching
2. **Error handling** robuste
3. **Mobile optimization** contrôles
4. **Performance optimization**

---

## ✅ **CRITÈRES DE VALIDATION**

### **Fonctionnalités :**
- [ ] Aperçu fidèle vCard publique
- [ ] Device switching responsive
- [ ] Simulation sources trafic
- [ ] Analytics temps réel
- [ ] Mode debug fonctionnel

### **Technique :**
- [ ] Architecture standardisée respectée
- [ ] Iframe sécurisé et performant
- [ ] Gestion erreurs cross-origin
- [ ] Responsive parfait
- [ ] Logging actions comprehensive

### **UX :**
- [ ] Interface intuitive et moderne
- [ ] Transitions fluides entre devices
- [ ] Feedback visuel constant
- [ ] Debug tools accessibles
- [ ] Performance < 2s chargement

### **Debug :**
- [ ] Console JavaScript intégrée
- [ ] Network requests monitoring
- [ ] Performance metrics précises
- [ ] HTML/CSS inspector
- [ ] Test automatique liens

---

## 🔧 **TABLES DATABASE SUPPLÉMENTAIRES**

### **Table preview_logs**
```sql
CREATE TABLE wp_nfc_preview_logs (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    card_id BIGINT(20) UNSIGNED NOT NULL,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    action_data JSON,
    device VARCHAR(20),
    source VARCHAR(20),
    user_agent TEXT,
    ip_address VARCHAR(45),
    created_at DATETIME NOT NULL,
    
    PRIMARY KEY (id),
    INDEX idx_card_action (card_id, action),
    INDEX idx_user_date (user_id, created_at)
);
```

---

*Cette page Preview sera la référence pour tester et valider les vCards avant mise en production, avec des outils de debug professionnels.*