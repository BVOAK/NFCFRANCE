# 📊 CAHIER DES CHARGES - Système Analytics Réelles NFC

**Version :** 1.0  
**Date :** 15/09/2025  
**Projet :** NFC France - Tracking Analytics Complet  
**Objectif :** Remplacer toutes les statistiques fictives par un système de tracking réel

---

## 🎯 **OBJECTIF PRINCIPAL**

Développer un **système d'analytics complet** qui :
- **Remplace toutes les simulations** par des données réelles
- **Tracke les vues, scans NFC, sources de trafic** en temps réel
- **Enregistre toutes les interactions** sur les vCards publiques
- **Fournit des statistiques précises** pour le dashboard

---

## 📋 **ANALYSE DES STATS ACTUELLES**

### **✅ Stats Déjà Réelles**
- **Contacts générés** : Table `posts` (type `lead`)
- **Activité récente** : Vrais contacts avec dates
- **Nombre de vCards** : Table `nfc_enterprise_cards`

### **❌ Stats Actuellement Fictives à Remplacer**
1. **Vues des profils** : Simulation basée sur `array_sum($vcard_ids) * 8`
2. **Scans NFC** : Calculé comme `40% des vues`
3. **Sources de trafic** : Pourcentages fixes (45% QR, 35% NFC, etc.)
4. **Évolution des vues** : Facteurs aléatoires et jours de semaine
5. **Géolocalisation** : Liste fixe de villes françaises
6. **Types d'appareils** : Simulation 70% mobile, 20% desktop

---

## 🏗️ **ARCHITECTURE TECHNIQUE**

### **F1. Base de Données - Nouvelle Table Analytics**

#### **F1.1 Table `wp_nfc_analytics`**
```sql
CREATE TABLE wp_nfc_analytics (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    vcard_id BIGINT(20) UNSIGNED NOT NULL,
    
    -- Informations visite
    session_id VARCHAR(32) NOT NULL,
    visitor_ip VARCHAR(45) NOT NULL,
    user_agent TEXT,
    referer_url TEXT,
    
    -- Données géographiques
    country VARCHAR(2),
    region VARCHAR(100),
    city VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    
    -- Informations technique
    device_type ENUM('mobile', 'tablet', 'desktop') NOT NULL,
    browser VARCHAR(50),
    os VARCHAR(50),
    screen_resolution VARCHAR(20),
    
    -- Source de trafic
    traffic_source ENUM('direct', 'qr_code', 'nfc_scan', 'social', 'email', 'referral', 'search') NOT NULL,
    utm_source VARCHAR(100),
    utm_medium VARCHAR(100),
    utm_campaign VARCHAR(100),
    
    -- Métriques temporelles
    view_datetime DATETIME NOT NULL,
    session_duration INT UNSIGNED DEFAULT 0,
    
    -- Interactions
    actions_performed TEXT, -- JSON des actions
    contact_shared BOOLEAN DEFAULT FALSE,
    
    -- Index et contraintes
    PRIMARY KEY (id),
    INDEX idx_vcard_date (vcard_id, view_datetime),
    INDEX idx_session (session_id),
    INDEX idx_traffic_source (traffic_source),
    FOREIGN KEY (vcard_id) REFERENCES wp_posts(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### **F1.2 Table `wp_nfc_actions`**
```sql
CREATE TABLE wp_nfc_actions (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    analytics_id BIGINT(20) UNSIGNED NOT NULL,
    vcard_id BIGINT(20) UNSIGNED NOT NULL,
    
    -- Type d'action
    action_type ENUM('view', 'phone_click', 'email_click', 'website_click', 'social_click', 'download_vcard', 'share') NOT NULL,
    action_value VARCHAR(255), -- URL, numéro de téléphone, etc.
    
    -- Timing
    action_datetime DATETIME NOT NULL,
    time_on_page INT UNSIGNED, -- Secondes
    
    -- Contexte
    element_clicked VARCHAR(100), -- ID ou classe de l'élément
    scroll_depth TINYINT UNSIGNED, -- Pourcentage de scroll
    
    PRIMARY KEY (id),
    INDEX idx_vcard_action (vcard_id, action_type),
    INDEX idx_datetime (action_datetime),
    FOREIGN KEY (analytics_id) REFERENCES wp_nfc_analytics(id) ON DELETE CASCADE,
    FOREIGN KEY (vcard_id) REFERENCES wp_posts(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **F2. Système de Tracking Frontend**

#### **F2.1 JavaScript de Tracking - nfc-analytics.js**
```javascript
/**
 * NFC Analytics Tracker
 * Système de tracking complet pour les vCards
 */
class NFCAnalytics {
    constructor(vcard_id, config) {
        this.vcard_id = vcard_id;
        this.config = config;
        this.session_id = this.generateSessionId();
        this.start_time = Date.now();
        this.actions = [];
        this.scroll_depth = 0;
        
        this.init();
    }
    
    init() {
        this.detectTrafficSource();
        this.trackPageView();
        this.bindEvents();
        this.startSessionTracking();
    }
    
    // Détection automatique de la source de trafic
    detectTrafficSource() {
        const urlParams = new URLSearchParams(window.location.search);
        const referrer = document.referrer;
        
        // 1. Paramètres UTM
        if (urlParams.get('utm_source')) {
            this.traffic_source = 'campaign';
            this.utm_data = {
                source: urlParams.get('utm_source'),
                medium: urlParams.get('utm_medium'),
                campaign: urlParams.get('utm_campaign')
            };
            return;
        }
        
        // 2. Paramètre spécial NFC
        if (urlParams.get('source') === 'nfc') {
            this.traffic_source = 'nfc_scan';
            return;
        }
        
        // 3. Paramètre QR Code
        if (urlParams.get('source') === 'qr' || urlParams.get('qr')) {
            this.traffic_source = 'qr_code';
            return;
        }
        
        // 4. Analyse du referrer
        if (referrer) {
            if (referrer.includes('facebook') || referrer.includes('instagram') || 
                referrer.includes('linkedin') || referrer.includes('twitter')) {
                this.traffic_source = 'social';
            } else if (referrer.includes('google') || referrer.includes('bing')) {
                this.traffic_source = 'search';
            } else if (referrer.includes('mail') || referrer.includes('gmail')) {
                this.traffic_source = 'email';
            } else {
                this.traffic_source = 'referral';
            }
        } else {
            this.traffic_source = 'direct';
        }
    }
    
    // Enregistrer la vue de page
    trackPageView() {
        const data = {
            action: 'nfc_track_view',
            vcard_id: this.vcard_id,
            session_id: this.session_id,
            traffic_source: this.traffic_source,
            utm_data: this.utm_data || {},
            device_info: this.getDeviceInfo(),
            nonce: this.config.nonce
        };
        
        this.sendAnalytics(data);
    }
    
    // Informations sur l'appareil
    getDeviceInfo() {
        const ua = navigator.userAgent;
        
        return {
            user_agent: ua,
            screen_resolution: `${screen.width}x${screen.height}`,
            device_type: this.detectDeviceType(),
            browser: this.detectBrowser(),
            os: this.detectOS(),
            language: navigator.language
        };
    }
    
    // Événements trackés automatiquement
    bindEvents() {
        // Clics sur téléphone
        document.querySelectorAll('a[href^="tel:"]').forEach(link => {
            link.addEventListener('click', () => {
                this.trackAction('phone_click', link.href);
            });
        });
        
        // Clics sur email
        document.querySelectorAll('a[href^="mailto:"]').forEach(link => {
            link.addEventListener('click', () => {
                this.trackAction('email_click', link.href);
            });
        });
        
        // Clics sur réseaux sociaux
        document.querySelectorAll('a[href*="facebook"], a[href*="instagram"], a[href*="linkedin"]').forEach(link => {
            link.addEventListener('click', () => {
                this.trackAction('social_click', link.href);
            });
        });
        
        // Scroll tracking
        let maxScroll = 0;
        window.addEventListener('scroll', () => {
            const scrolled = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
            if (scrolled > maxScroll) {
                maxScroll = scrolled;
                this.scroll_depth = Math.max(this.scroll_depth, scrolled);
            }
        });
        
        // Tracking de sortie
        window.addEventListener('beforeunload', () => {
            this.trackSessionEnd();
        });
    }
    
    trackAction(action_type, value = '', element = '') {
        const action = {
            action_type,
            value,
            element,
            timestamp: Date.now(),
            scroll_depth: this.scroll_depth
        };
        
        this.actions.push(action);
        
        // Envoyer l'action immédiatement
        this.sendAnalytics({
            action: 'nfc_track_action',
            vcard_id: this.vcard_id,
            session_id: this.session_id,
            action_data: action,
            nonce: this.config.nonce
        });
    }
    
    sendAnalytics(data) {
        // Utiliser sendBeacon pour les performances
        if (navigator.sendBeacon) {
            const formData = new FormData();
            Object.keys(data).forEach(key => {
                formData.append(key, typeof data[key] === 'object' ? JSON.stringify(data[key]) : data[key]);
            });
            navigator.sendBeacon(this.config.ajax_url, formData);
        } else {
            // Fallback vers fetch
            fetch(this.config.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data)
            }).catch(() => {}); // Ignore les erreurs
        }
    }
}
```

#### **F2.2 Détection de Source NFC**

**Modification des URLs NFC physiques :**
```
https://nfcfrance.com/vcard/jean-dupont/?source=nfc&device_id=NFC001
```

**Modification des QR Codes :**
```
https://nfcfrance.com/vcard/jean-dupont/?source=qr&qr_id=QR123
```

### **F3. Modifications Backend**

#### **F3.1 Nouveau fichier analytics-handlers.php**
```php
<?php
/**
 * Handlers pour le système d'analytics NFC
 */

class NFC_Analytics_Handler {
    
    public function __construct() {
        add_action('wp_ajax_nfc_track_view', [$this, 'track_view']);
        add_action('wp_ajax_nopriv_nfc_track_view', [$this, 'track_view']);
        
        add_action('wp_ajax_nfc_track_action', [$this, 'track_action']);
        add_action('wp_ajax_nopriv_nfc_track_action', [$this, 'track_action']);
    }
    
    public function track_view() {
        $vcard_id = intval($_POST['vcard_id'] ?? 0);
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        $traffic_source = sanitize_text_field($_POST['traffic_source'] ?? 'direct');
        
        if (!$vcard_id || !$session_id) {
            wp_die('Données manquantes');
        }
        
        global $wpdb;
        
        // Géolocalisation via IP
        $geo_data = $this->get_geolocation($_SERVER['REMOTE_ADDR']);
        
        // Analyser User Agent
        $device_info = $this->parse_user_agent($_SERVER['HTTP_USER_AGENT']);
        
        $analytics_data = [
            'vcard_id' => $vcard_id,
            'session_id' => $session_id,
            'visitor_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referer_url' => $_SERVER['HTTP_REFERER'] ?? '',
            'traffic_source' => $traffic_source,
            'country' => $geo_data['country'] ?? null,
            'region' => $geo_data['region'] ?? null,
            'city' => $geo_data['city'] ?? null,
            'device_type' => $device_info['device_type'],
            'browser' => $device_info['browser'],
            'os' => $device_info['os'],
            'view_datetime' => current_time('mysql')
        ];
        
        $wpdb->insert($wpdb->prefix . 'nfc_analytics', $analytics_data);
        
        wp_die('OK');
    }
    
    private function get_geolocation($ip) {
        // Intégration avec service de géolocalisation
        // Option 1: ipapi.co (gratuit)
        // Option 2: MaxMind GeoIP
        // Option 3: Service interne
        
        $response = wp_remote_get("http://ipapi.co/{$ip}/json/");
        
        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return [
                'country' => $data['country_code'] ?? null,
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null
            ];
        }
        
        return [];
    }
}

new NFC_Analytics_Handler();
```

#### **F3.2 Modification single-virtual_card.php**

**À ajouter dans le header du template :**
```php
<?php
// Analytics tracking setup
$vcard_id = get_the_ID();
$analytics_config = [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nfc_analytics')
];
?>

<!-- Analytics CSS et JS -->
<script src="<?= plugin_dir_url(__FILE__) ?>../assets/js/nfc-analytics.js"></script>

<script>
// Initialiser le tracking
document.addEventListener('DOMContentLoaded', function() {
    window.nfcAnalytics = new NFCAnalytics(<?= $vcard_id ?>, <?= json_encode($analytics_config) ?>);
});
</script>
```

### **F4. Nouvelles Fonctions de Statistiques**

#### **F4.1 Remplacement dans ajax-handlers.php**
```php
/**
 * NOUVELLES FONCTIONS - 100% RÉELLES
 */

private function get_total_views($vcard_ids, $start_date, $end_date = null) {
    global $wpdb;
    
    $analytics_table = $wpdb->prefix . 'nfc_analytics';
    
    $where_date = $end_date ? 
        "AND view_datetime BETWEEN %s AND %s" :
        "AND view_datetime >= %s";
    
    $placeholders = implode(',', array_fill(0, count($vcard_ids), '%d'));
    
    $sql = "
        SELECT COUNT(*) as total
        FROM {$analytics_table}
        WHERE vcard_id IN ({$placeholders})
        {$where_date}
    ";
    
    $params = array_merge($vcard_ids, [$start_date]);
    if ($end_date) {
        $params[] = $end_date;
    }
    
    return intval($wpdb->get_var($wpdb->prepare($sql, $params)) ?: 0);
}

private function get_traffic_sources($vcard_ids, $period) {
    global $wpdb;
    
    $analytics_table = $wpdb->prefix . 'nfc_analytics';
    $days = $this->period_to_days($period);
    $start_date = date('Y-m-d', strtotime("-{$days} days"));
    
    $placeholders = implode(',', array_fill(0, count($vcard_ids), '%d'));
    
    $sources = $wpdb->get_results($wpdb->prepare("
        SELECT 
            traffic_source as source,
            COUNT(*) as count
        FROM {$analytics_table}
        WHERE vcard_id IN ({$placeholders})
          AND view_datetime >= %s
        GROUP BY traffic_source
        ORDER BY count DESC
    ", array_merge($vcard_ids, [$start_date])));
    
    return array_map(function($row) {
        return [
            'source' => ucfirst(str_replace('_', ' ', $row->source)),
            'count' => intval($row->count)
        ];
    }, $sources);
}

private function get_real_views_evolution($vcard_ids, $days) {
    global $wpdb;
    
    $analytics_table = $wpdb->prefix . 'nfc_analytics';
    $placeholders = implode(',', array_fill(0, count($vcard_ids), '%d'));
    
    $evolution = [];
    
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        
        $views = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$analytics_table}
            WHERE vcard_id IN ({$placeholders})
              AND DATE(view_datetime) = %s
        ", array_merge($vcard_ids, [$date])));
        
        $evolution[] = [
            'date' => date('d/m', strtotime($date)),
            'views' => intval($views ?: 0)
        ];
    }
    
    return $evolution;
}
```

---

## ⚡ **PLAN DE DÉVELOPPEMENT**

### **Phase 1 : Infrastructure Base (3h)**
1. **Créer les tables BDD** (wp_nfc_analytics, wp_nfc_actions)
2. **Développer nfc-analytics.js** (tracking frontend)
3. **Créer analytics-handlers.php** (backend tracking)
4. **Tests de tracking** sur une vCard

### **Phase 2 : Intégration Template (2h)**
1. **Modifier single-virtual_card.php** (ajouter tracking)
2. **Modifier générateur QR** (URLs avec paramètres)
3. **Modifier URLs NFC physiques** (paramètre source=nfc)
4. **Tests sources de trafic**

### **Phase 3 : Remplacement Stats Dashboard (2h)**
1. **Remplacer get_total_views()** par version réelle
2. **Remplacer get_traffic_sources()** par données BDD
3. **Remplacer get_views_evolution()** par vraies données
4. **Supprimer toutes les simulations**

### **Phase 4 : Fonctionnalités Avancées (2h)**
1. **Géolocalisation par IP** (intégration service)
2. **Tracking des actions** (clics, téléchargements)
3. **Analytics temps réel** (dashboard live)
4. **Export données avancé**

---

## 🔒 **CONFORMITÉ RGPD**

### **Données Collectées**
- **Anonymisation IP** après géolocalisation
- **Pas de cookies** persistants
- **Session ID** généré côté client
- **Opt-out possible** via paramètre URL

### **Conservation**
- **Données analytics** : 13 mois maximum
- **Suppression automatique** des anciennes données
- **Droit à l'effacement** implémenté

---

## ✅ **CRITÈRES DE VALIDATION**

### **Tracking**
- [ ] Toutes les vues enregistrées en BDD
- [ ] Sources de trafic détectées correctement
- [ ] Actions utilisateur trackées
- [ ] Géolocalisation fonctionnelle

### **Dashboard**
- [ ] Aucune simulation restante
- [ ] Graphiques basés sur vraies données
- [ ] Performance < 2s chargement
- [ ] Données temps réel

### **Technique**
- [ ] Tables BDD optimisées
- [ ] Tracking non-bloquant
- [ ] Compatible RGPD
- [ ] Monitoring erreurs

---

*Ce système fournira des analytics précises et exploitables pour optimiser les performances des vCards NFC.*