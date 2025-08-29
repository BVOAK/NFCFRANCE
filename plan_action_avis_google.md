    // Notifications email selon types créés
    if (!empty($created_profiles['vcards']) && !empty($created_profiles['google_reviews'])) {
        // Commande mixte : email combiné
        nfc_send_mixed_notification($order_id, $created_profiles);
    } elseif (!empty($created_profiles['google_reviews'])) {
        // Avis Google uniquement
        nfc_send_google_reviews_notification($order_id, $created_profiles['google_reviews'][0]);
    } elseif (!empty($created_profiles['vcards'])) {
        // vCard uniquement (utiliser fonction existante Document 1/2)
        gtmi_vcard_send_enterprise_notification($order_id, $created_profiles['vcards']);
    }
    
    error_log("NFC: Order $order_id processed - " . count($created_profiles['vcards']) . " vCards, " . count($created_profiles['google_reviews']) . " Google Reviews profiles");
}

// Remplacer hook existant
remove_action('woocommerce_thankyou', 'gtmi_vcard_order_payment_success');
add_action('woocommerce_thankyou', 'nfc_order_payment_success_extended', 10, 1);

/*
Validation :
Commande 5 vCard + 8 cartes AG + 3 plaques AG → 5 profils vCard + 1 profil AG + tous identifiants corrects.
*/
```

**Tâche 2.2 : Email notifications Avis Google (2h)**
```php
// Prompt Développement 2.2
/*
Contexte : Emails de notification pour profils Avis Google créés.

Tâche :
1. nfc_send_google_reviews_notification() pour Avis Google pur
2. nfc_send_mixed_notification() pour commandes mixtes
3. Templates email différenciés selon contexte
4. Liste identifiants + liens dashboard
5. Instructions configuration URL Google Business

Fonctions email à créer :
*/

function nfc_send_google_reviews_notification($order_id, $profile_id) {
    $order = wc_get_order($order_id);
    $profile = get_post($profile_id);
    
    if (!$order || !$profile) return false;
    
    $customer_email = $order->get_billing_email();
    $company_name = get_post_meta($profile_id, 'company_name', true);
    $total_elements = get_post_meta($profile_id, 'total_elements', true);
    $total_cards = get_post_meta($profile_id, 'total_cards', true);
    $total_plaques = get_post_meta($profile_id, 'total_plaques', true);
    
    // Récupérer tous identifiants
    global $wpdb;
    $elements = $wpdb->get_results($wpdb->prepare(
        "SELECT element_identifier, element_type FROM {$wpdb->prefix}google_reviews_elements 
         WHERE profile_id = %d ORDER BY element_type, element_position",
        $profile_id
    ));
    
    $subject = "Vos éléments NFC Avis Google sont prêts - NFC France";
    
    $message = "
        Bonjour,
        
        Vos éléments NFC pour collecter des avis Google sont maintenant disponibles !
        
        🏪 Établissement : {$company_name}
        📦 Commande #{$order_id}
        📱 {$total_cards} carte(s) + 🏷️ {$total_plaques} plaque(s) = {$total_elements} élément(s) au total
        
        📋 IDENTIFIANTS DE VOS ÉLÉMENTS :
        ";
    
    $cards = [];
    $plaques = [];
    
    foreach ($elements as $element) {
        if ($element->element_type === 'card') {
            $cards[] = $element->element_identifier;
        } else {
            $plaques[] = $element->element_identifier;
        }
    }
    
    if (!empty($cards)) {
        $message .= "\n        📱 CARTES (" . count($cards) . ") :\n";
        foreach ($cards as $card) {
            $message .= "        • $card\n";
        }
    }
    
    if (!empty($plaques)) {
        $message .= "\n        🏷️ PLAQUES (" . count($plaques) . ") :\n";
        foreach ($plaques as $plaque) {
            $message .= "        • $plaque\n";
        }
    }
    
    $dashboard_url = home_url('/mon-compte/dashboard-nfc/?profile=' . $profile_id);
    $profile_url = get_permalink($profile_id);
    
    $message .= "
        
        🎯 PROCHAINES ÉTAPES :
        
        1. Accédez à votre dashboard :
           {$dashboard_url}
           
        2. Configurez votre URL Google Business (obligatoire)
        
        3. Personnalisez l'emplacement de chaque élément :
           • Carte AG{$order_id}-1 → \"Table 1\"
           • Carte AG{$order_id}-2 → \"Table 2\"
           • Plaque AGP{$order_id}-1 → \"Vitrine\"
           • etc.
        
        4. Téléchargez vos QR codes pour impression
        
        💡 IMPORTANT : Vos éléments NFC redirigent tous vers la MÊME page d'avis Google.
        Vous pourrez suivre quel emplacement génère le plus d'avis grâce aux statistiques détaillées.
        
        🔗 URL publique de votre profil : {$profile_url}
        (Cette URL sera active une fois votre compte Google Business configuré)
        
        Besoin d'aide ? Consultez notre guide : https://nfcfrance.com/guide-avis-google
        
        Cordialement,
        L'équipe NFC France
    ";
    
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    
    $sent = wp_mail($customer_email, $subject, $message, $headers);
    
    if ($sent) {
        error_log("NFC: Google Reviews notification sent to $customer_email for profile $profile_id");
    } else {
        error_log("NFC: Failed to send Google Reviews notification to $customer_email for profile $profile_id");
    }
    
    return $sent;
}

function nfc_send_mixed_notification($order_id, $profiles) {
    $order = wc_get_order($order_id);
    $customer_email = $order->get_billing_email();
    
    $vcards_count = count($profiles['vcards']);
    $google_profiles_count = count($profiles['google_reviews']);
    
    $subject = "Vos produits NFC sont prêts (vCard + Avis Google) - NFC France";
    
    $message = "
        Bonjour,
        
        Votre commande multi-produits NFC est maintenant disponible !
        
        📦 Commande #{$order_id} - Produits mixtes
        
        📱 CARTES VCARD INDIVIDUELLES ({$vcards_count} profils) :
        Chaque carte a son propre profil complet avec ses statistiques et contacts.
        
        ";
    
    // Lister vCards (reprendre logique Document 1/2)
    foreach ($profiles['vcards'] as $vcard_id) {
        $identifier = get_post_meta($vcard_id, '_enterprise_identifier', true);
        $url = get_permalink($vcard_id);
        $message .= "        • {$identifier} : {$url}\n";
    }
    
    $message .= "
        
        ⭐ PROFIL AVIS GOOGLE PARTAGÉ ({$google_profiles_count} profil) :
        Tous vos éléments Avis Google pointent vers le même compte Google Business.
        
        ";
    
    // Détails profil Avis Google
    if (!empty($profiles['google_reviews'])) {
        $google_profile_id = $profiles['google_reviews'][0];
        $google_profile = get_post($google_profile_id);
        $total_elements = get_post_meta($google_profile_id, 'total_elements', true);
        
        $message .= "        • Profil : {$google_profile->post_title}\n";
        $message .= "        • Éléments : {$total_elements} cartes/plaques\n";
        $message .= "        • URL : " . get_permalink($google_profile_id) . "\n";
    }
    
    $message .= "
        
        🎯 ACCÈS À VOS DASHBOARDS :
        
        • Dashboard vCard Entreprise : " . home_url('/mon-compte/dashboard-nfc/vcards/') . "
        • Dashboard Avis Google : " . home_url('/mon-compte/dashboard-nfc/google-reviews/') . "
        
        📚 GUIDES COMPLETS :
        • Configuration vCard : https://nfcfrance.com/guide-vcard-entreprise
        • Configuration Avis Google : https://nfcfrance.com/guide-avis-google
        
        Cordialement,
        L'équipe NFC France
    ";
    
    return wp_mail($customer_email, $subject, $message, ['Content-Type: text/plain; charset=UTF-8']);
}

/*
Validation :
Emails corrects selon type commande, liens dashboard fonctionnels, instructions claires.
*/
```

#### **JOUR 3 : Dashboard Avis Google Base**

**Tâche 3.1 : Template configuration profil (4h)**
```php
// Prompt Développement 3.1
/*
Contexte : Page principale dashboard Avis Google pour configuration profil.

Tâche :
1. Template dashboard/google-reviews/profile-config.php
2. Configuration URL Google Business avec validation
3. Génération QR code automatique
4. Section mapping emplacements avec dropdowns
5. Stats globales en header

Template principal à créer :
*/

<?php
/**
 * Template : Configuration Profil Avis Google
 * Fichier : templates/dashboard/google-reviews/profile-config.php
 */

if (!defined('ABSPATH')) exit;

// Récupérer profil Avis Google
$profile_id = isset($_GET['profile']) ? intval($_GET['profile']) : 0;
if (!$profile_id) {
    echo '<div class="alert alert-danger">Profil Avis Google non trouvé.</div>';
    return;
}

$profile = get_post($profile_id);
$user_id = get_current_user_id();
$main_user_id = get_post_meta($profile_id, 'main_user_id', true);

// Vérifier propriété
if ($main_user_id != $user_id && !current_user_can('manage_options')) {
    wp_die('Accès non autorisé à ce profil.');
}

// Données profil
$company_name = get_post_meta($profile_id, 'company_name', true);
$google_business_url = get_post_meta($profile_id, 'google_business_url', true);
$total_cards = get_post_meta($profile_id, 'total_cards', true);
$total_plaques = get_post_meta($profile_id, 'total_plaques', true);
$total_elements = $total_cards + $total_plaques;
$order_id = get_post_meta($profile_id, 'order_id', true);

// Récupérer éléments avec stats
global $wpdb;
$elements = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}google_reviews_elements 
     WHERE profile_id = %d 
     ORDER BY element_type, element_position",
    $profile_id
), ARRAY_A);

// Stats 30 derniers jours
$stats_30d = nfc_get_google_reviews_stats($profile_id, 30);

// Status configuration
$is_configured = !empty($google_business_url);
$qr_code_url = $is_configured ? nfc_generate_google_reviews_qr($profile_id) : '';

?>
<div class="nfc-google-reviews-config">
    
    <!-- Header profil -->
    <div class="dashboard-card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h5 mb-1">
                        <i class="fas fa-star me-2"></i>
                        <?php echo esc_html($company_name); ?> - Avis Google
                    </h2>
                    <small class="text-muted">
                        Commande #<?php echo $order_id; ?> • 
                        <?php echo $total_cards; ?> carte(s) + <?php echo $total_plaques; ?> plaque(s)
                    </small>
                </div>
                <div class="profile-status">
                    <?php if ($is_configured): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>Configuré
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>À configurer
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Stats globales -->
        <?php if ($is_configured): ?>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="stat-metric">
                        <div class="stat-value"><?php echo number_format($stats_30d['total_scans']); ?></div>
                        <div class="stat-label">Scans (30j)</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-metric">
                        <div class="stat-value"><?php echo number_format($stats_30d['total_redirections']); ?></div>
                        <div class="stat-label">Redirections</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-metric">
                        <div class="stat-value"><?php echo number_format($stats_30d['conversion_rate'], 1); ?>%</div>
                        <div class="stat-label">Taux conversion</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-metric">
                        <div class="stat-value"><?php echo esc_html($stats_30d['top_performer']); ?></div>
                        <div class="stat-label">Top performer</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Configuration URL Google Business -->
    <div class="dashboard-card mb-4">
        <div class="card-header">
            <h3 class="h6 mb-0">
                <i class="fas fa-link me-2"></i>
                Configuration Google Business
            </h3>
        </div>
        <div class="card-body">
            <form id="google-business-config-form" class="google-business-form">
                <input type="hidden" name="profile_id" value="<?php echo $profile_id; ?>">
                <input type="hidden" name="action" value="nfc_save_google_business_url">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('nfc_google_business_save'); ?>">
                
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label fw-medium">
                            URL de votre page d'avis Google Business *
                        </label>
                        <input type="url" 
                               name="google_business_url" 
                               class="form-control" 
                               value="<?php echo esc_attr($google_business_url); ?>"
                               placeholder="https://g.page/r/votre-etablissement/review"
                               required>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Comment trouver cette URL :</strong><br>
                            1. Google My Business → Votre fiche établissement<br>
                            2. Cliquez "Demander des avis clients"<br> 
                            3. Copiez l'URL raccourcie (g.page/r/...)
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <?php echo $is_configured ? 'Modifier' : 'Configurer'; ?>
                        </button>
                        <?php if ($is_configured): ?>
                        <button type="button" class="btn btn-outline-info" onclick="testGoogleUrl()">
                            <i class="fas fa-external-link-alt me-2"></i>Tester
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <?php if ($is_configured): ?>
            <div class="mt-3 p-3 bg-success bg-opacity-10 border border-success rounded">
                <i class="fas fa-check-circle text-success me-2"></i>
                <strong>URL configurée avec succès !</strong> Tous vos éléments NFC redirigent vers cette page.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($is_configured): ?>
    <!-- QR Code généré -->
    <div class="dashboard-card mb-4">
        <div class="card-header">
            <h3 class="h6 mb-0">
                <i class="fas fa-qrcode me-2"></i>
                QR Code généré automatiquement
            </h3>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="qr-code-display">
                        <img src="<?php echo esc_url($qr_code_url); ?>" 
                             alt="QR Code Avis Google" 
                             class="qr-code-image">
                    </div>
                </div>
                <div class="col-md-9">
                    <p class="mb-3">
                        <strong>Ce QR code pointe vers votre page d'avis Google Business.</strong><br>
                        Vous pouvez l'utiliser sur tous supports : cartes, plaques, affiches, etc.
                    </p>
                    <div class="qr-actions">
                        <a href="<?php echo esc_url($qr_code_url); ?>" 
                           download="qr-avis-google-<?php echo $profile_id; ?>.png" 
                           class="btn btn-outline-primary me-2">
                            <i class="fas fa-download me-2"></i>PNG (300x300)
                        </a>
                        <button class="btn btn-outline-primary me-2" onclick="downloadQR('svg')">
                            <i class="fas fa-download me-2"></i>SVG (vectoriel)
                        </button>
                        <button class="btn btn-outline-secondary" onclick="printQR()">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gestion des emplacements -->
    <div class="dashboard-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="h6 mb-0">
                <i class="fas fa-map-marker-alt me-2"></i>
                Gestion des emplacements (<?php echo count($elements); ?> éléments)
            </h3>
            <button class="btn btn-sm btn-outline-primary" onclick="autoConfigureElements()">
                <i class="fas fa-magic me-2"></i>Configuration automatique
            </button>
        </div>
        <div class="card-body">
            <div class="elements-mapping-form">
                <?php 
                $cards = array_filter($elements, fn($el) => $el['element_type'] === 'card');
                $plaques = array_filter($elements, fn($el) => $el['element_type'] === 'plaque');
                ?>
                
                <?php if (!empty($cards)): ?>
                <div class="elements-section mb-4">
                    <h5 class="h6 text-primary mb-3">
                        <i class="fas fa-credit-card me-2"></i>
                        Cartes (<?php echo count($cards); ?>)
                    </h5>
                    <div class="row">
                        <?php foreach ($cards as $card): ?>
                        <div class="col-md-6 mb-3">
                            <div class="element-mapping-item">
                                <label class="form-label fw-medium">
                                    <?php echo esc_html($card['element_identifier']); ?>
                                </label>
                                <div class="input-group">
                                    <select class="form-select element-label-select" 
                                            data-element-id="<?php echo $card['id']; ?>"
                                            onchange="updateElementLabel(this)">
                                        <option value="">Emplacement...</option>
                                        <option value="Table 1" <?php selected($card['element_label'], 'Table 1'); ?>>Table 1</option>
                                        <option value="Table 2" <?php selected($card['element_label'], 'Table 2'); ?>>Table 2</option>
                                        <option value="Table 3" <?php selected($card['element_label'], 'Table 3'); ?>>Table 3</option>
                                        <option value="Table 4" <?php selected($card['element_label'], 'Table 4'); ?>>Table 4</option>
                                        <option value="Table 5" <?php selected($card['element_label'], 'Table 5'); ?>>Table 5</option>
                                        <option value="Comptoir" <?php selected($card['element_label'], 'Comptoir'); ?>>Comptoir</option>
                                        <option value="Caisse" <?php selected($card['element_label'], 'Caisse'); ?>>Caisse</option>
                                        <option value="Accueil" <?php selected($card['element_label'], 'Accueil'); ?>>Accueil</option>
                                        <option value="Bar" <?php selected($card['element_label'], 'Bar'); ?>>Bar</option>
                                        <option value="custom">Personnalisé...</option>
                                    </select>
                                    <div class="input-group-text">
                                        <span class="scans-count" id="scans-<?php echo $card['id']; ?>">
                                            <?php echo $card['scans_count']; ?> scans
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($plaques)): ?>
                <div class="elements-section">
                    <h5 class="h6 text-info mb-3">
                        <i class="fas fa-square me-2"></i>
                        Plaques (<?php echo count($plaques); ?>)
                    </h5>
                    <div class="row">
                        <?php foreach ($plaques as $plaque): ?>
                        <div class="col-md-6 mb-3">
                            <div class="element-mapping-item">
                                <label class="form-label fw-medium">
                                    <?php echo esc_html($plaque['element_identifier']); ?>
                                </label>
                                <div class="input-group">
                                    <select class="form-select element-label-select"
                                            data-element-id="<?php echo $plaque['id']; ?>"
                                            onchange="updateElementLabel(this)">
                                        <option value="">Emplacement...</option>
                                        <option value="Vitrine" <?php selected($plaque['element_label'], 'Vitrine'); ?>>Vitrine</option>
                                        <option value="Entrée" <?php selected($plaque['element_label'], 'Entrée'); ?>>Entrée</option>
                                        <option value="Terrasse" <?php selected($plaque['element_label'], 'Terrasse'); ?>>Terrasse</option>
                                        <option value="Mur principal" <?php selected($plaque['element_label'], 'Mur principal'); ?>>Mur principal</option>
                                        <option value="Comptoir" <?php selected($plaque['element_label'], 'Comptoir'); ?>>Comptoir</option>
                                        <option value="Salon d'attente" <?php selected($plaque['element_label'], 'Salon d\'attente'); ?>>Salon d'attente</option>
                                        <option value="custom">Personnalisé...</option>
                                    </select>
                                    <div class="input-group-text">
                                        <span class="scans-count" id="scans-<?php echo $plaque['id']; ?>">
                                            <?php echo $plaque['scans_count']; ?> scans
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Analytics aperçu -->
    <?php if ($is_configured && $stats_30d['total_scans'] > 0): ?>
    <div class="dashboard-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="h6 mb-0">
                <i class="fas fa-chart-bar me-2"></i>
                Aperçu performance (30 derniers jours)
            </h3>
            <a href="?page=google-reviews-analytics&profile=<?php echo $profile_id; ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-chart-line me-2"></i>Analytics détaillées
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <canvas id="performanceChart" width="400" height="200"></canvas>
                </div>
                <div class="col-md-4">
                    <h6>Top 5 Emplacements</h6>
                    <div class="top-performers">
                        <?php foreach ($stats_30d['top_elements'] as $element): ?>
                        <div class="performer-item d-flex justify-content-between">
                            <span><?php echo esc_html($element['label']); ?></span>
                            <strong><?php echo $element['scans']; ?> scans</strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- JavaScript configuration -->
<script>
// Configuration profil Google Business
document.getElementById('google-business-config-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sauvegarde...';
    
    fetch(window.nfcConfig.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Succès : recharger la page pour afficher QR code
            location.reload();
        } else {
            throw new Error(data.data || 'Erreur de sauvegarde');
        }
    })
    .catch(error => {
        alert('Erreur : ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Mise à jour label élément
function updateElementLabel(select) {
    const elementId = select.dataset.elementId;
    let newLabel = select.value;
    
    if (newLabel === 'custom') {
        newLabel = prompt('Emplacement personnalisé :');
        if (!newLabel) return;
    }
    
    // AJAX sauvegarde
    fetch(window.nfcConfig.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'nfc_update_element_label',
            element_id: elementId,
            label: newLabel,
            nonce: '<?php echo wp_create_nonce("nfc_update_element"); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Feedback visuel
            select.style.backgroundColor = '#d4edda';
            setTimeout(() => select.style.backgroundColor = '', 2000);
        }
    });
}

// Configuration automatique
function autoConfigureElements() {
    if (!confirm('Configurer automatiquement tous les emplacements ?\n(Table 1, Table 2, Vitrine, etc.)')) return;
    
    // À implémenter : logique auto-configuration
    alert('Fonctionnalité en développement');
}

// Test URL Google
function testGoogleUrl() {
    const url = document.querySelector('input[name="google_business_url"]').value;
    if (url) {
        window.open(url, '_blank');
    }
}

// Graphique performance (si données disponibles)
<?php if ($is_configured && !empty($stats_30d['chart_data'])): ?>
const chartData = <?php echo json_encode($stats_30d['chart_data']); ?>;
const ctx = document.getElementById('performanceChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Scans par emplacement',
            data: chartData.data,
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
<?php endif; ?>
</script>

/*
Validation :
Interface configuration complète, sauvegarde URL Google Business, mapping éléments, QR code généré.
*/
```

**Tâche 3.2 : Page publique redirection (2h)**
```php
// Prompt Développement 3.2
/*
Contexte : Template single-google_reviews_profile.php pour redirection vers Google Business.

Tâche :
1. Template single-google_reviews_profile.php
2. Tracking scan avec ?source=AG2045-5
3. Redirection 301 vers URL Google Business
4. Page d'attente si pas configuré
5. Gestion erreurs (URL invalide, etc.)

Template redirection à créer :
*/

<?php
/**
 * Template : Page Publique Profil Avis Google
 * Fichier : single-google_reviews_profile.php
 * 
 * Cette page redirige automatiquement vers Google Business
 * après avoir tracké la source du scan (optionnel)
 */

if (!defined('ABSPATH')) exit;

// Récupérer profil actuel
$profile_id = get_queried_object_id();
$profile = get_queried_object();

if (!$profile || $profile->post_type !== 'google_reviews_profile') {
    status_header(404);
    include get_404_template();
    exit;
}

// Paramètres de tracking
$source_identifier = sanitize_text_field($_GET['source'] ?? '');
$track_scan = !empty($source_identifier) && preg_match('/^AG[P]?\d+-\d+$/', $source_identifier);

// URL Google Business configurée
$google_business_url = get_post_meta($profile_id, 'google_business_url', true);

// Tracking du scan si identifiant fourni
if ($track_scan) {
    nfc_track_google_reviews_scan($profile_id, $source_identifier);
}

// Redirection vers Google Business si configuré
if (!empty($google_business_url)) {
    
    // Valider URL Google Business
    if (filter_var($google_business_url, FILTER_VALIDATE_URL) && 
        (strpos($google_business_url, 'g.page') !== false || 
         strpos($google_business_url, 'google.com') !== false)) {
        
        // Tracking de la redirection
        if ($track_scan) {
            nfc_track_google_reviews_redirect($profile_id, $source_identifier, $google_business_url);
        }
        
        // Redirection 301 vers Google Business
        wp_redirect($google_business_url, 301);
        exit;
        
    } else {
        // URL invalide : afficher erreur
        nfc_display_google_reviews_error('URL Google Business invalide');
        exit;
    }
    
} else {
    // Pas encore configuré : page d'attente
    nfc_display_google_reviews_pending($profile_id);
    exit;
}

/**
 * Tracking scan élément Avis Google
 */
function nfc_track_google_reviews_scan($profile_id, $source_identifier) {
    global $wpdb;
    
    // Récupérer infos élément
    $element = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}google_reviews_elements WHERE element_identifier = %s",
        $source_identifier
    ));
    
    if (!$element) return false;
    
    // Enregistrer tracking détaillé
    $wpdb->insert(
        $wpdb->prefix . 'google_reviews_tracking',
        [
            'profile_id' => $profile_id,
            'element_identifier' => $source_identifier,
            'element_type' => $element->element_type,
            'element_label' => $element->element_label ?: 'Non défini',
            'event_type' => 'scan',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'referer_url' => $_SERVER['HTTP_REFERER'] ?? ''
        ]
    );
    
    // Incrémenter compteur élément
    $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->prefix}google_reviews_elements 
         SET scans_count = scans_count + 1, last_scan_at = NOW() 
         WHERE id = %d",
        $element->id
    ));
    
    // Mettre à jour cache stats profil
    nfc_update_google_reviews_stats_cache($profile_id);
    
    error_log("NFC: Tracked Google Reviews scan - Profile $profile_id, Element $source_identifier");
    return true;
}

/**
 * Tracking redirection réussie
 */
function nfc_track_google_reviews_redirect($profile_id, $source_identifier, $destination_url) {
    global $wpdb;
    
    $wpdb->insert(
        $wpdb->prefix . 'google_reviews_tracking',
        [
            'profile_id' => $profile_id,
            'element_identifier' => $source_identifier,
            'element_type' => strpos($source_identifier, 'AGP') === 0 ? 'plaque' : 'card',
            'event_type' => 'redirect',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'referer_url' => $destination_url // URL Google Business
        ]
    );
}

/**
 * Page d'attente si profil pas configuré
 */
function nfc_display_google_reviews_pending($profile_id) {
    $company_name = get_post_meta($profile_id, 'company_name', true);
    $main_user_id = get_post_meta($profile_id, 'main_user_id', true);
    
    get_header();
    ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-cog fa-3x text-warning mb-3"></i>
                            <h2 class="h4">Configuration en cours</h2>
                        </div>
                        
                        <p class="lead mb-4">
                            Le profil Avis Google de <strong><?php echo esc_html($company_name); ?></strong> 
                            est en cours de configuration.
                        </p>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Cette page sera active dès que le propriétaire aura configuré son URL Google Business.
                        </div>
                        
                        <?php if (get_current_user_id() == $main_user_id): ?>
                        <div class="mt-4">
                            <a href="<?php echo home_url('/mon-compte/dashboard-nfc/?profile=' . $profile_id); ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-cog me-2"></i>Configurer maintenant
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 pt-4 border-top">
                            <small class="text-muted">
                                Propulsé par <strong>NFC France</strong> • 
                                <a href="https://nfcfrance.com" class="text-decoration-none">nfcfrance.com</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    get_footer();
}

/**
 * Page d'erreur URL invalide
 */
function nfc_display_google_reviews_error($message) {
    get_header();
    ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <h2 class="h4">Erreur de configuration</h2>
                        </div>
                        
                        <p class="lead mb-4"><?php echo esc_html($message); ?></p>
                        
                        <div class="alert alert-danger">
                            <i class="fas fa-tools me-2"></i>
                            Le propriétaire doit corriger la configuration de son profil Avis Google.
                        </div>
                        
                        <div class="mt-4 pt-4 border-top">
                            <small class="text-muted">
                                Besoin d'aide ? <a href="mailto:support@nfcfrance.com">support@nfcfrance.com</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    get_footer();
}

/*
Validation :
URLs /google-avis/abc123/?source=AG2045-5 → Tracking + redirection Google Business OK.
Pages d'attente et erreur fonctionnelles.
*/
```

---

### **PHASE 2 : Analytics et Finalisation (2 jours)**

#### **JOUR 4 : Analytics Détaillées**

**Tâche 4.1 : Fonctions analytics et stats (4h)**
```php
// Prompt Développement 4.1
/*
Contexte : Fonctions calcul statistiques Avis Google détaillées.

Tâche :
1. nfc_get_google_reviews_stats() avec période flexible
2. Calculs : total scans, redirections, top performers, tendances
3. Données graphiques Chart.js
4. Cache stats pour performance
5. Comparaisons temporelles

Fonctions analytics principales :
*/

function nfc_get_google_reviews_stats($profile_id, $days = 30) {
    $cache_key = "google_reviews_stats_{$profile_id}_{$days}";
    $cached = wp_cache_get($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    global $wpdb;
    $date_limit = date('Y-m-d', strtotime("-$days days"));
    
    // Stats générales période
    $general_stats = $wpdb->get_row($wpdb->prepare("
        SELECT 
            COUNT(*) as total_scans,
            COUNT(CASE WHEN event_type = 'redirect' THEN 1 END) as total_redirections,
            COUNT(DISTINCT element_identifier) as active_elements
        FROM {$wpdb->prefix}google_reviews_tracking 
        WHERE profile_id = %d AND created_at >= %s
    ", $profile_id, $date_limit), ARRAY_A);
    
    // Top performers par élément
    $top_elements = $wpdb->get_results($wpdb->prepare("
        SELECT 
            element_identifier,
            element_label,
            COUNT(*) as scans,
            COUNT(CASE WHEN event_type = 'redirect' THEN 1 END) as redirections
        FROM {$wpdb->prefix}google_reviews_tracking 
        WHERE profile_id = %d AND created_at >= %s 
        GROUP BY element_identifier, element_label
        ORDER BY scans DESC
        LIMIT 10
    ", $profile_id, $date_limit), ARRAY_A);
    
    // Évolution quotidienne
    $daily_evolution = $wpdb->get_results($wpdb->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as scans,
            COUNT(CASE WHEN event_type = 'redirect' THEN 1 END) as redirections
        FROM {$wpdb->prefix}google_reviews_tracking 
        WHERE profile_id = %d AND created_at >= %s
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ", $profile_id, $date_limit), ARRAY_A);
    
    // Répartition par type d'appareil
    $device_breakdown = $wpdb->get_results($wpdb->prepare("
        SELECT 
            CASE 
                WHEN user_agent LIKE '%%Mobile%%' THEN 'Mobile'
                WHEN user_agent LIKE '%%Tablet%%' THEN 'Tablette' 
                ELSE 'Desktop'
            END as device_type,
            COUNT(*) as scans
        FROM {$wpdb->prefix}google_reviews_tracking 
        WHERE profile_id = %d AND created_at >= %s
        GROUP BY device_type
        ORDER BY scans DESC
    ", $profile_id, $date_limit), ARRAY_A);
    
    // Analyse heures de pointe
    $hourly_distribution = $wpdb->get_results($wpdb->prepare("
        SELECT 
            HOUR(created_at) as hour,
            COUNT(*) as scans
        FROM {$wpdb->prefix}google_reviews_tracking 
        WHERE profile_id = %d AND created_at >= %s
        GROUP BY HOUR(created_at)
        ORDER BY hour
    ", $profile_id, $date_limit), ARRAY_A);
    
    // Calculs dérivés
    $total_scans = (int)$general_stats['total_scans'];
    $total_redirections = (int)$general_stats['total_redirections'];
    $conversion_rate = $total_scans > 0 ? ($total_redirections / $total_scans) * 100 : 0;
    
    // Top performer
    $top_performer = !empty($top_elements) ? 
        $top_elements[0]['element_label'] . ' (' . $top_elements[0]['scans'] . ' scans)' : 
        'Aucun scan';
    
    // Tendance vs période précédente
    $previous_period_stats = $wpdb->get_row($wpdb->prepare("
        SELECT COUNT(*) as prev_scans 
        FROM {$wpdb->prefix}google_reviews_tracking 
        WHERE profile_id = %d 
        AND created_at >= %s 
        AND created_at < %s
    ", $profile_id, date('Y-m-d', strtotime("-" . ($days * 2) . " days")), $date_limit), ARRAY_A);
    
    $trend = 0;
    if ($previous_period_stats && $previous_period_stats['prev_scans'] > 0) {
        $trend = (($total_scans - $previous_period_stats['prev_scans']) / $previous_period_stats['prev_scans']) * 100;
    }
    
    // Données pour graphiques
    $chart_data = [
        'daily_evolution' => [
            'labels' => array_column($daily_evolution, 'date'),
            'scans' => array_column($daily_evolution, 'scans'),
            'redirections' => array_column($daily_evolution, 'redirections')
        ],
        'top_elements' => [
            'labels' => array_column(array_slice($top_elements, 0, 5), 'element_label'),
            'data' => array_column(array_slice($top_elements, 0, 5), 'scans')
        ],
        'device_breakdown' => [
            'labels' => array_column($device_breakdown, 'device_type'),
            'data' => array_column($device_breakdown, 'scans')
        ],
        'hourly_heatmap' => $hourly_distribution
    ];
    
    $stats = [
        'total_scans' => $total_scans,
        'total_redirections' => $total_redirections,
        'conversion_rate' => $conversion_rate,
        'active_elements' => (int)$general_stats['active_elements'],
        'top_performer' => $top_performer,
        'trend' => round($trend, 1),
        'top_elements' => array_slice($top_elements, 0, 10),
        'daily_evolution' => $daily_evolution,
        'device_breakdown' => $device_breakdown,
        'hourly_distribution' => $hourly_distribution,
        'chart_data' => $chart_data,
        'period_days' => $days,
        'generated_at' => current_time('mysql')
    ];
    
    // Cache 1 heure
    wp_cache_set($cache_key, $stats, '', 3600);
    
    return $stats;
}

function nfc_update_google_reviews_stats_cache($profile_id) {
    // Invalider cache lors nouveau scan
    wp_cache_delete("google_reviews_stats_{$profile_id}_7");
    wp_cache_delete("google_reviews_stats_{$profile_id}_30");
    wp_cache_delete("google_reviews_stats_{$profile_id}_90");
    
    // Mettre à jour métadonnées rapides
    $stats_30d = nfc_get_google_reviews_stats($profile_id, 30);
    update_post_meta($profile_id, 'total_scans_30d', $stats_30d['total_scans']);
    update_post_meta($profile_id, 'total_redirections_30d', $stats_30d['total_redirections']);
    update_post_meta($profile_id, 'top_performer', $stats_30d['top_performer']);
    update_post_meta($profile_id, 'stats_updated_at', current_time('mysql'));
}

function nfc_generate_google_reviews_qr($profile_id) {
    $profile_url = get_permalink($profile_id);
    if (!$profile_url) return false;
    
    // Utiliser service QR externe ou bibliothèque PHP
    $qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/';
    $qr_params = [
        'size' => '300x300',
        'data' => $profile_url,
        'format' => 'png',
        'margin' => 10,
        'color' => '000000',
        'bgcolor' => 'ffffff'
    ];
    
    return $qr_api_url . '?' . http_build_query($qr_params);
}

/*
Validation :
Stats détaillées correctes, graphiques alimentés, cache fonctionnel, QR code généré.
*/
```

**Tâche 4.2 : Template analytics dashboard (4h)**
```php
// Prompt Développement 4.2
/*
Contexte : Page analytics complète avec graphiques Chart.js.

Tâche :
1. Template analytics-dashboard.php avec graphiques interactifs
2. Filtres période (7j, 30j, 90j)
3. Graphiques : évolution, top performers, appareils, heatmap heures
4. Tableau détaillé par élément
5. Export CSV des données

Template analytics à créer :
*/

<?php
/**
 * Template : Analytics Détaillées Avis Google
 * Fichier : templates/dashboard/google-reviews/analytics-dashboard.php
 */

if (!defined('ABSPATH')) exit;

$profile_id = isset($_GET['profile']) ? intval($_GET['profile']) : 0;
$period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : '30';

if (!$profile_id) {
    echo '<div class="alert alert-danger">Profil non spécifié.</div>';
    return;
}

$profile = get_post($profile_id);
$company_name = get_post_meta($profile_id, 'company_name', true);
$stats = nfc_get_google_reviews_stats($profile_id, (int)$period);

// Vérifier propriété
$user_id = get_current_user_id();
$main_user_id = get_post_meta($profile_id, 'main_user_id', true);
if ($main_user_id != $user_id && !current_user_can('manage_options')) {
    wp_die('Accès non autorisé');
}

$periods = [
    '7' => '7 derniers jours',
    '30' => '30 derniers jours', 
    '90' => '3 derniers mois'
];
?>

<div class="nfc-google-reviews-analytics">
    
    <!-- Header analytics -->
    <div class="analytics-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h4 mb-1">
                    <i class="fas fa-chart-line me-2"></i>
                    Analytics - <?php echo esc_html($company_name); ?>
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="?page=google-reviews-config&profile=<?php echo $profile_id; ?>">Configuration</a>
                        </li>
                        <li class="breadcrumb-item active">Analytics détaillées</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <select class="form-select" onchange="changePeriod(this.value)" style="min-width: 180px;">
                    <?php foreach ($periods as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php selected($period, $value); ?>>
                        <?php echo $label; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline-primary" onclick="exportAnalytics()">
                    <i class="fas fa-download me-2"></i>Export CSV
                </button>
            </div>
        </div>
    </div>

    <!-- KPIs principales -->
    <div class="analytics-kpis mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="kpi-card bg-primary">
                    <div class="kpi-icon">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <div class="kpi-content">
                        <div class="kpi-value"><?php echo number_format($stats['total_scans']); ?></div>
                        <div class="kpi-label">Scans totaux</div>
                        <div class="kpi-trend <?php echo $stats['trend'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $stats['trend'] >= 0 ? '+' : ''; ?><?php echo $stats['trend']; ?>%
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card bg-success">
                    <div class="kpi-icon">
                        <i class="fas fa-external-link-alt"></i>
                    </div>
                    <div class="kpi-content">
                        <div class="kpi-value"><?php echo number_format($stats['total_redirections']); ?></div>
                        <div class="kpi-label">Redirections</div>
                        <div class="kpi-sublabel">Vers Google Business</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card bg-info">
                    <div class="kpi-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="kpi-content">
                        <div class="kpi-value"><?php echo number_format($stats['conversion_rate'], 1); ?>%</div>
                        <div class="kpi-label">Taux conversion</div>
                        <div class="kpi-sublabel">Scans → Redirections</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card bg-warning">
                    <div class="kpi-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="kpi-content">
                        <div class="kpi-value"><?php echo $stats['active_elements']; ?></div>
                        <div class="kpi-label">Emplacements actifs</div>
                        <div class="kpi-sublabel">Avec au moins 1 scan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques principaux -->
    <div class="row mb-4">
        
        <!-- Évolution temporelle -->
        <div class="col-lg-8">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="h6 mb-0">Évolution quotidienne (<?php echo $period; ?> jours)</h3>
                </div>
                <div class="card-body">
                    <canvas id="evolutionChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Top performers -->
        <div class="col-lg-4">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="h6 mb-0">Top 5 Emplacements</h3>
                </div>
                <div class="card-body">
                    <canvas id="topPerformersChart" width="300" height="200"></canvas>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Graphiques secondaires -->
    <div class="row mb-4">
        
        <!-- Répartition appareils -->
        <div class="col-lg-6">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="h6 mb-0">Répartition par appareil</h3>
                </div>
                <div class="card-body">
                    <canvas id="devicesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Heatmap heures -->
        <div class="col-lg-6">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="h6 mb-0">Répartition horaire</h3>
                </div>
                <div class="card-body">
                    <canvas id="hourlyChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Tableau détaillé par élément -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="h6 mb-0">Performance détaillée par emplacement</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Identifiant</th>
                            <th>Emplacement</th>
                            <th>Type</th>
                            <th>Scans</th>
                            <th>Redirections</th>
                            <th>Taux conversion</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['top_elements'] as $element): ?>
                        <?php 
                        $conversion = $element['scans'] > 0 ? ($element['redirections'] / $element['scans']) * 100 : 0;
                        $performance_class = $element['scans'] >= 10 ? 'bg-success' : ($element['scans'] >= 5 ? 'bg-warning' : 'bg-secondary');
                        ?>
                        <tr>
                            <td>
                                <code class="text-primary"><?php echo esc_html($element['element_identifier']); ?></code>
                            </td>
                            <td>
                                <strong><?php echo esc_html($element['element_label'] ?: 'Non défini'); ?></strong>
                            </td>
                            <td>
                                <?php if (strpos($element['element_identifier'], 'AGP') === 0): ?>
                                    <span class="badge bg-info">Plaque</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Carte</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo number_format($element['scans']); ?></strong>
                            </td>
                            <td>
                                <?php echo number_format($element['redirections']); ?>
                            </td>
                            <td>
                                <?php echo number_format($conversion, 1); ?>%
                            </td>
                            <td>
                                <div class="performance-bar">
                                    <?php 
                                    $max_scans = max(array_column($stats['top_elements'], 'scans'));
                                    $percentage = $max_scans > 0 ? ($element['scans'] / $max_scans) * 100 : 0;
                                    ?>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar <?php echo $performance_class; ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo $percentage; ?>%">
                                            <?php echo $element['scans']; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Insights et recommandations -->
    <div class="dashboard-card mt-4">
        <div class="card-header">
            <h3 class="h6 mb-0">
                <i class="fas fa-lightbulb me-2"></i>
                Insights et recommandations
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success">✅ Points forts</h6>
                    <ul class="list-unstyled">
                        <?php if ($stats['conversion_rate'] >= 80): ?>
                        <li><i class="fas fa-check text-success me-2"></i>Excellent taux de conversion (<?php echo number_format($stats['conversion_rate'], 1); ?>%)</li>
                        <?php endif; ?>
                        
                        <?php if ($stats['trend'] > 10): ?>
                        <li><i class="fas fa-trending-up text-success me-2"></i>Forte croissance (+<?php echo $stats['trend']; ?>% vs période précédente)</li>
                        <?php endif; ?>
                        
                        <?php if (!empty($stats['top_elements']) && $stats['top_elements'][0]['scans'] >= 20): ?>
                        <li><i class="fas fa-star text-success me-2"></i>Top performer très actif (<?php echo $stats['top_elements'][0]['element_label']; ?>)</li>
                        <?php endif; ?>
                        
                        <li><i class="fas fa-mobile-alt text-success me-2"></i>Bonne adoption mobile (<?php 
                        $mobile_usage = 0;
                        foreach ($stats['device_breakdown'] as $device) {
                            if ($device['device_type'] === 'Mobile') $mobile_usage = round(($device['scans'] / $stats['total_scans']) * 100);
                        }
                        echo $mobile_usage; ?>% des scans)</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-warning">⚠️ Points d'amélioration</h6>
                    <ul class="list-unstyled">
                        <?php 
                        $low_performers = array_filter($stats['top_elements'], fn($el) => $el['scans'] < 3);
                        if (!empty($low_performers)): ?>
                        <li><i class="fas fa-arrow-down text-warning me-2"></i><?php echo count($low_performers); ?> emplacement(s) sous-exploité(s)</li>
                        <?php endif; ?>
                        
                        <?php if ($stats['conversion_rate'] < 70): ?>
                        <li><i class="fas fa-exclamation text-warning me-2"></i>Taux de conversion à améliorer (<?php echo number_format($stats['conversion_rate'], 1); ?>%)</li>
                        <?php endif; ?>
                        
                        <?php if ($stats['trend'] < -5): ?>
                        <li><i class="fas fa-trending-down text-warning me-2"></i>Baisse d'activité (<?php echo $stats['trend']; ?>%)</li>
                        <?php endif; ?>
                        
                        <li><i class="fas fa-map-marker-alt text-warning me-2"></i>Optimiser placement des éléments peu actifs</li>
                    </ul>
                </div>
            </div>
            
            <!-- Actions recommandées -->
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="mb-2">🎯 Actions recommandées :</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php if (!empty($low_performers)): ?>
                    <span class="badge bg-warning text-dark">Déplacer éléments peu actifs</span>
                    <?php endif; ?>
                    
                    <span class="badge bg-info">Ajouter signalétique "Laissez un avis"</span>
                    <span class="badge bg-success">Motiver équipe à présenter les cartes</span>
                    
                    <?php if ($stats['total_scans'] < 50): ?>
                    <span class="badge bg-primary">Augmenter visibilité des éléments NFC</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Configuration graphiques Chart.js -->
<script>
const analyticsData = <?php echo json_encode($stats['chart_data']); ?>;

// Graphique évolution temporelle
const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
new Chart(evolutionCtx, {
    type: 'line',
    data: {
        labels: analyticsData.daily_evolution.labels.reverse(),
        datasets: [
            {
                label: 'Scans',
                data: analyticsData.daily_evolution.scans.reverse(),
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Redirections',
                data: analyticsData.daily_evolution.redirections.reverse(),
                borderColor: 'rgba(75, 192, 192, 1)', 
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    afterLabel: function(context) {
                        if (context.datasetIndex === 0) {
                            const redirections = analyticsData.daily_evolution.redirections.reverse()[context.dataIndex];
                            const conversion = context.raw > 0 ? Math.round((redirections / context.raw) * 100) : 0;
                            return `Taux conversion: ${conversion}%`;
                        }
                    }
                }
            }
        }
    }
});

// Graphique top performers
const topPerformersCtx = document.getElementById('topPerformersChart').getContext('2d');
new Chart(topPerformersCtx, {
    type: 'doughnut',
    data: {
        labels: analyticsData.top_elements.labels,
        datasets: [{
            data: analyticsData.top_elements.data,
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56', 
                '#4BC0C0',
                '#9966FF'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Graphique répartition appareils
const devicesCtx = document.getElementById('devicesChart').getContext('2d');
new Chart(devicesCtx, {
    type: 'pie',
    data: {
        labels: analyticsData.device_breakdown.labels,
        datasets: [{
            data: analyticsData.device_breakdown.data,
            backgroundColor: [
                '#28a745',
                '#17a2b8', 
                '#ffc107'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Graphique répartition horaire
const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
const hourlyLabels = Array.from({length: 24}, (_, i) => i + 'h');
const hourlyData = new Array(24).fill(0);

// Remplir données horaires
analyticsData.hourly_heatmap.forEach(item => {
    hourlyData[item.hour] = item.scans;
});

new Chart(hourlyCtx, {
    type: 'bar',
    data: {
        labels: hourlyLabels,
        datasets: [{
            label: 'Scans par heure',
            data: hourlyData,
            backgroundColor: 'rgba(255, 99, 132, 0.8)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Fonctions utilitaires
function changePeriod(period) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('period', period);
    window.location.href = currentUrl.toString();
}

function exportAnalytics() {
    const params = new URLSearchParams({
        action: 'nfc_export_google_reviews_analytics',
        profile_id: <?php echo $profile_id; ?>,
        period: '<?php echo $period; ?>',
        nonce: '<?php echo wp_create_nonce('nfc_export_analytics'); ?>'
    });
    
    window.location.href = `${window.nfcConfig.ajax_url}?${params}`;
}
</script>

/*
Validation :
Analytics complètes avec graphiques interactifs, insights automatiques, export fonctionnel.
*/
```

#### **JOUR 5 : APIs REST et Finalisation**

**Tâche 5.1 : APIs REST Avis Google (3h)**
```php
// Prompt Développement 5.1
/*
Contexte : APIs REST complètes pour dashboard Avis Google.

Tâche :
1. Créer rest-google-reviews.php avec toutes les routes
2. Routes CRUD profil Avis Google
3. Routes tracking et analytics
4. Routes gestion éléments/emplacements
5. Authentification et permissions

Routes API à implémenter :
*/

<?php
/**
 * APIs REST Google Reviews
 * Fichier : includes/api/rest-google-reviews.php
 */

class NFC_Google_Reviews_REST_API {
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    public function register_routes() {
        $namespace = 'nfc-google-reviews/v1';
        
        // Routes profil Avis Google
        register_rest_route($namespace, '/profile/(?P<profile_id>\\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_profile'],
            'permission_callback' => [$this, 'check_profile_permission']
        ]);
        
        register_rest_route($namespace, '/profile/(?P<profile_id>\\d+)/config', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_profile_config'],
            'permission_callback' => [$this, 'check_profile_permission'],
            'args' => [
                'google_business_url' => [
                    'required' => true,
                    'validate_callback' => [$this, 'validate_google_business_url']
                ]
            ]
        ]);
        
        // Routes éléments/emplacements
        register_rest_route($namespace, '/profile/(?P<profile_id>\\d+)/elements', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_profile_elements'],
            'permission_callback' => [$this, 'check_profile_permission']
        ]);
        
        register_rest_route($namespace, '/element/(?P<element_id>\\d+)', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_element_label'],
            'permission_callback' => [$this, 'check_element_permission']
        ]);
        
        // Routes analytics
        register_rest_route($namespace, '/profile/(?P<profile_id>\\d+)/analytics', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_profile_analytics'],
            'permission_callback' => [$this, 'check_profile_permission'],
            'args' => [
                'period' => [
                    'required' => false,
                    'default' => 30,
                    'validate_callback' => [$this, 'validate_period']
                ]
            ]
        ]);
        
        // Routes tracking
        register_rest_route($namespace, '/track/scan/(?P<identifier>[a-zA-Z0-9-]+)', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'track_scan'],
            'permission_callback' => '__return_true' // Public pour tracking
        ]);
        
        // Routes utilitaires
        register_rest_route($namespace, '/qr-code/(?P<profile_id>\\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'generate_qr_code'],
            'permission_callback' => [$this, 'check_profile_permission']
        ]);
        
        register_rest_route($namespace, '/export/(?P<profile_id>\\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'export_analytics'],
            'permission_callback' => [$this, 'check_profile_permission']
        ]);
    }
    
    // Méthodes API principales
    public function get_profile($request) {
        $profile_id = $request['profile_id'];
        
        $profile = get_post($profile_id);
        if (!$profile || $profile->post_type !== 'google_reviews_profile') {
            return new WP_Error('not_found', 'Profile not found', ['status' => 404]);
        }
        
        $profile_data = [
            'id' => $profile_id,
            'title' => $profile->post_title,
            'company_name' => get_post_meta($profile_id, 'company_name', true),
            'google_business_url' => get_post_meta($profile_id, 'google_business_url', true),
            'order_id' => get_post_meta($profile_id, 'order_id', true),
            'total_cards' => get_post_meta($profile_id, 'total_cards', true),
            'total_plaques' => get_post_meta($profile_id, 'total_plaques', true),
            'total_elements' => get_post_meta($profile_id, 'total_elements', true),
            'status' => get_post_meta($profile_id, 'status', true),
            'public_url' => get_permalink($profile_id),
            'qr_code_url' => nfc_generate_google_reviews_qr($profile_id),
            'stats_30d' => [
                'total_scans' => get_post_meta($profile_id, 'total_scans_30d', true) ?: 0,
                'total_redirections' => get_post_meta($profile_id, 'total_redirections_30d', true) ?: 0,
                'top_performer' => get_post_meta($profile_id, 'top_performer', true) ?: 'Aucun scan'
            ]
        ];
        
        return rest_ensure_response([
            'success' => true,
            'data' => $profile_data
        ]);
    }
    
    public function update_profile_config($request) {
        $profile_id = $request['profile_id'];
        $google_business_url = sanitize_url($request['google_business_url']);
        
        // Valider et sauvegarder URL
        update_post_meta($profile_id, 'google_business_url', $google_business_url);
        update_post_meta($profile_id, 'status', 'configured');
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'google_business_url' => $google_business_url,
                'status' => 'configured',
                'qr_code_url' => nfc_generate_google_reviews_qr($profile_id)
            ]
        ]);
    }
    
    public function get_profile_elements($request) {
        $profile_id = $request['profile_id'];
        
        global $wpdb;
        $elements = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}google_reviews_elements 
             WHERE profile_id = %d 
             ORDER BY element_type, element_position",
            $profile_id
        ), ARRAY_A);
        
        return rest_ensure_response([
            'success' => true,
            'data' => $elements
        ]);
    }
    
    public function update_element_label($request) {
        $element_id = $request['element_id'];
        $new_label = sanitize_text_field($request['label']);
        
        global $wpdb;
        $result = $wpdb->update(
            $wpdb->prefix . 'google_reviews_elements',
            ['element_label' => $new_label],
            ['id' => $element_id],
            ['%s'],
            ['%d']
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update element label', ['status' => 500]);
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => ['element_id' => $element_id, 'new_label' => $new_label]
        ]);
    }
    
    public function get_profile_analytics($request) {
        $profile_id = $request['profile_id'];
        $period = $request['period'];
        
        $analytics = nfc_get_google_reviews_stats($profile_id, $period);
        
        return rest_ensure_response([
            'success' => true,
            'data' => $analytics
        ]);
    }
    
    public function track_scan($request) {
        $identifier = $request['identifier'];
        
        // Récupérer profil depuis identifiant
        global $wpdb;
        $element = $wpdb->get_row($wpdb->prepare(
            "SELECT profile_id FROM {$wpdb->prefix}google_reviews_elements WHERE element_identifier = %s",
            $identifier
        ));
        
        if (!$element) {
            return new WP_Error('not_found', 'Element not found', ['status' => 404]);
        }
        
        $tracked = nfc_track_google_reviews_scan($element->profile_id, $identifier);
        
        return rest_ensure_response([
            'success' => $tracked,
            'data' => ['identifier' => $identifier, 'tracked' => $tracked]
        ]);
    }
    
    public function generate_qr_code($request) {
        $profile_id = $request['profile_id'];
        $format = $request->get_param('format') ?: 'png';
        $size = $request->get_param('size') ?: '300';
        
        $qr_url = nfc_generate_google_reviews_qr($profile_id, $format, $size);
        
        return rest_ensure_response([
            'success' => true,
            'data' => ['qr_code_url' => $qr_url]
        ]);
    }
    
    public function export_analytics($request) {
        $profile_id = $request['profile_id'];
        $period = $request->get_param('period') ?: 30;
        $format = $request->get_param('format') ?: 'csv';
        
        $analytics = nfc_get_google_reviews_stats($profile_id, $period);
        
        if ($format === 'csv') {
            $csv_data = $this->format_analytics_csv($analytics);
            
            return rest_ensure_response([
                'success' => true,
                'data' => [
                    'format' => 'csv',
                    'content' => $csv_data,
                    'filename' => "analytics-google-reviews-{$profile_id}-{$period}j.csv"
                ]
            ]);
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => $analytics
        ]);
    }
    
    // Méthodes utilitaires
    private function format_analytics_csv($analytics) {
        $csv_lines = [];
        
        // Header
        $csv_lines[] = 'Date,Scans,Redirections,Taux Conversion (%)';
        
        // Données quotidiennes
        foreach ($analytics['daily_evolution'] as $day) {
            $conversion = $day['scans'] > 0 ? round(($day['redirections'] / $day['scans']) * 100, 1) : 0;
            $csv_lines[] = implode(',', [
                $day['date'],
                $day['scans'],
                $day['redirections'],
                $conversion
            ]);
        }
        
        $csv_lines[] = ''; // Ligne vide
        
        // Performance par élément
        $csv_lines[] = 'Identifiant,Emplacement,Type,Scans,Redirections,Conversion (%)';
        foreach ($analytics['top_elements'] as $element) {
            $conversion = $element['scans'] > 0 ? round(($element['redirections'] / $element['scans']) * 100, 1) : 0;
            $type = strpos($element['element_identifier'], 'AGP') === 0 ? 'Plaque' : 'Carte';
            
            $csv_lines[] = implode(',', [
                $element['element_identifier'],
                $element['element_label'],
                $type,
                $element['scans'],
                $element['redirections'],
                $conversion
            ]);
        }
        
        return implode("\n", $csv_lines);
    }
    
    // Permissions et validation
    public function check_profile_permission($request) {
        $profile_id = $request['profile_id'];
        $current_user_id = get_current_user_id();
        
        if (!$current_user_id) return false;
        if (current_user_can('manage_options')) return true;
        
        $main_user_id = get_post_meta($profile_id, 'main_user_id', true);
        return $main_user_id == $current_user_id;
    }
    
    public function check_element_permission($request) {
        $element_id = $request['element_id'];
        
        global $wpdb;
        $profile_id = $wpdb->get_var($wpdb->prepare(
            "SELECT profile_id FROM {$wpdb->prefix}google_reviews_elements WHERE id = %d",
            $element_id
        ));
        
        if (!$profile_id) return false;
        
        // Utiliser vérification profil
        $request['profile_id'] = $profile_id;
        return $this->check_profile_permission($request);
    }
    
    public function validate_google_business_url($value) {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Vérifier que c'est une URL Google valide
        return (strpos($value, 'g.page') !== false || 
                strpos($value, 'google.com') !== false ||
                strpos($value, 'business.google.com') !== false);
    }
    
    public function validate_period($value) {
        return is_numeric($value) && $value > 0 && $value <= 365;
    }
}

new NFC_Google_Reviews_REST_API();

/*
Validation :
Routes API complètes, authentification sécurisée, export CSV fonctionnel.
*/
```

**Tâche 5.2 : AJAX handlers et finalisation (3h)**
```php
// Prompt Développement 5.2
/*
Contexte : Handlers AJAX pour interactions dashboard Avis Google.

Tâche :
1. Handlers AJAX pour sauvegarde URL Google Business
2. Handlers mise à jour labels éléments
3. Handlers export analytics
4. Tests intégration complète
5. Documentation finale

Handlers AJAX à implémenter :
*/

// Handler sauvegarde URL Google Business
add_action('wp_ajax_nfc_save_google_business_url', 'handle_save_google_business_url');
function handle_save_google_business_url() {
    check_ajax_referer('nfc_google_business_save', 'nonce');
    
    $profile_id = intval($_POST['profile_id']);
    $google_business_url = sanitize_url($_POST['google_business_url']);
    
    if (!$profile_id || !$google_business_url) {
        wp_send_json_error('Données manquantes');
    }
    
    // Vérifier propriété
    $current_user_id = get_current_user_id();
    $main_user_id = get_post_meta($profile_id, 'main_user_id', true);
    
    if ($main_user_id != $current_user_id && !current_user_can('manage_options')) {
        wp_send_json_error('Accès non autorisé');
    }
    
    // Valider URL Google Business
    if (!filter_var($google_business_url, FILTER_VALIDATE_URL) ||
        (strpos($google_business_url, 'g.page') === false && 
         strpos($google_business_url, 'google.com') === false)) {
        wp_send_json_error('URL Google Business invalide');
    }
    
    // Sauvegarder
    update_post_meta($profile_id, 'google_business_url', $google_business_url);
    update_post_meta($profile_id, 'status', 'configured');
    
    wp_send_json_success([
        'google_business_url' => $google_business_url,
        'status' => 'configured',
        'qr_code_url' => nfc_generate_google_reviews_qr($profile_id)
    ]);
}

// Handler mise à jour label élément
add_action('wp_ajax_nfc_update_element_label', 'handle_update_element_label');
function handle_update_element_label() {
    check_ajax_referer('nfc_update_element', 'nonce');
    
    $element_id = intval($_POST['element_id']);
    $new_label = sanitize_text_field($_POST['label']);
    
    if (!$element_id || empty($new_label)) {
        wp_send_json_error('Données manquantes');
    }
    
    // Vérifier propriété via profil
    global $wpdb;
    $element = $wpdb->get_row($wpdb->prepare(
        "SELECT profile_id FROM {$wpdb->prefix}google_reviews_elements WHERE id = %d",
        $element_id
    ));
    
    if (!$element) {
        wp_send_json_error('Élément non trouvé');
    }
    
    $current_user_id = get_current_user_id();
    $main_user_id = get_post_meta($element->profile_id, 'main_user_id', true);
    
    if ($main_user_id != $current_user_id && !current_user_can('manage_options')) {
        wp_send_json_error('Accès non autorisé');
    }
    
    // Mettre à jour
    $result = $wpdb->update(
        $wpdb->prefix . 'google_reviews_elements',
        ['element_label' => $new_label],
        ['id' => $element_id],
        ['%s'],
        ['%d']
    );
    
    if ($result === false) {
        wp_send_json_error('Erreur de sauvegarde');
    }
    
    wp_send_json_success([
        'element_id' => $element_id,
        'new_label' => $new_label
    ]);
}

// Handler export analytics
add_action('wp_ajax_nfc_export_google_reviews_analytics', 'handle_export_google_reviews_analytics');
function handle_export_google_reviews_analytics() {
    check_ajax_referer('nfc_export_analytics', 'nonce');
    
    $profile_id = intval($_GET['profile_id']);
    $period = intval($_GET['period']) ?: 30;
    
    // Vérifier propriété
    $current_user_id = get_current_user_id();
    $main_user_id = get_post_meta($profile_id, 'main_user_id', true);
    
    if ($main_user_id != $current_user_id && !current_user_can('manage_options')) {
        wp_die('Accès non autorisé');
    }
    
    // Générer CSV
    $analytics = nfc_get_google_reviews_stats($profile_id, $period);
    $company_name = get_post_meta($profile_id, 'company_name', true);
    
    $csv_content = "# Analytics Avis Google - " . $company_name . "\n";
    $csv_content .= "# Période : " . $period . " derniers jours\n";
    $csv_content .= "# Généré le : " . current_time('d/m/Y H:i') . "\n\n";
    
    // Résumé
    $csv_content .= "RÉSUMÉ\n";
    $csv_content .= "Total scans," . $analytics['total_scans'] . "\n";
    $csv_content .= "Total redirections," . $analytics['total_redirections'] . "\n";
    $csv_content .= "Taux conversion (%)," . number_format($analytics['conversion_rate'], 2) . "\n";
    $csv_content .= "Éléments actifs," . $analytics['active_elements'] . "\n";
    $csv_content .= "Top performer," . $analytics['top_performer'] . "\n\n";
    
    // Évolution quotidienne
    $csv_content .= "ÉVOLUTION QUOTIDIENNE\n";
    $csv_content .= "Date,Scans,Redirections,Conversion (%)\n";
    
    foreach ($analytics['daily_evolution'] as $day) {
        $conversion = $day['scans'] > 0 ? round(($day['redirections'] / $day['scans']) * 100, 1) : 0;
        $csv_content .= implode(',', [
            $day['date'],
            $day['scans'],
            $day['redirections'],
            $conversion
        ]) . "\n";
    }
    
    $csv_content .= "\nPERFORMANCE PAR EMPLACEMENT\n";
    $csv_content .= "Identifiant,Emplacement,Type,Scans,Redirections,Conversion (%)\n";
    
    foreach ($analytics['top_elements'] as $element) {
        $conversion = $element['scans'] > 0 ? round(($element['redirections'] / $element['scans']) * 100, 1) : 0;
        $type = strpos($element['element_identifier'], 'AGP') === 0 ? 'Plaque' : 'Carte';
        
        $csv_content .= implode(',', [
            $element['element_identifier'],
            '"' . $element['element_label'] . '"',
            $type,
            $element['scans'],
            $element['redirections'],
            $conversion
        ]) . "\n";
    }
    
    // Headers téléchargement
    $filename = "analytics-avis-google-" . sanitize_file_name($company_name) . "-{$period}j-" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // BOM UTF-8 pour Excel
    echo "\xEF\xBB\xBF";
    echo $csv_content;
    exit;
}

/*
Validation :
Handlers AJAX fonctionnels, export CSV correct, gestion erreurs complète.
*/
```

---

## ✅ **Tests et Validation Finaux**

### **Tests Intégration Complète**
```php
// Test création profil Avis Google
function test_google_reviews_profile_creation() {
    // Commande 12 cartes + 3 plaques Avis Google
    $order_id = create_test_order([
        'google_reviews_card' => 12,
        'google_reviews_plaque' => 3
    ]);
    
    $profile_id = nfc_create_google_reviews_profile($order_id);
    
    assert_not_false($profile_id);
    assert_equals(15, get_post_meta($profile_id, 'total_elements', true));
    
    // Vérifier identifiants générés
    global $wpdb;
    $elements = $wpdb->get_results($wpdb->prepare(
        "SELECT element_identifier FROM {$wpdb->prefix}google_reviews_elements WHERE profile_id = %d",
        $profile_id
    ));
    
    assert_count(15, $elements);
    assert_contains('AG' . $order_id . '-1', array_column($elements, 'element_identifier'));
    assert_contains('AGP' . $order_id . '-1', array_column($elements, 'element_identifier'));
}

// Test redirection et tracking
function test_google_reviews_redirect_and_tracking() {
    $profile_id = create_test_google_reviews_profile();
    update_post_meta($profile_id, 'google_business_url', 'https://g.page/r/test/review');
    
    // Simuler scan avec source
    $_GET['source'] = 'AG2045-5';
    ob_start();
    include 'single-google_reviews_profile.php';
    $output = ob_get_clean();
    
    // Vérifier tracking enregistré
    global $wpdb;
    $tracked = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}google_reviews_tracking 
         WHERE element_identifier = %s AND event_type = 'scan'",
        'AG2045-5'
    ));
    
    assert_equals(1, $tracked);
}
```

### **Critères Performance**
- Dashboard Avis Google < 2s avec 50 éléments
- Redirection < 300ms vers Google Business  
- Analytics < 1s même avec 1000+ scans
- QR code généré instantanément

---

## 📚 **Documentation Utilisateur Finale**

### **Guide Configuration Avis Google**
1. **Trouver URL Google Business** → Google My Business → Demander avis → Copier lien
2. **Configurer profil** → Dashboard → Coller URL → Validation automatique
3. **Labelliser éléments** → Carte AG2045-1 → "Table 1" → Sauvegarde
4. **Télécharger QR codes** → Formats PNG/SVG → Impression
5. **Suivre performances** → Analytics → Optimiser emplacements

### **Support Client**
- **URL invalide** : Vérifier format g.page/r/... ou google.com/...
- **Pas de scans** : Vérifier visibilité cartes, ajouter signalétique
- **Faible conversion** : Optimiser placement, motiver équipe

---

*Plan d'action technique complet*  
*Système Avis Google v1*  
*Architecture complète et fonctionnelle*# Document 4/4 : Plan d'Action Technique  
# Système Avis Google

## 🎯 **Objectif Développement**
Implémenter le système "X cartes/plaques Avis Google = 1 profil partagé" avec dashboard spécialisé, tracking par emplacement et analytics détaillées.

---

## 📋 **Architecture Existante à Réutiliser**

### **Fonctions de base adaptables ✅**
```php
// Fonctions WooCommerce (à étendre)
gtmi_vcard_order_payment_success()                // Base création post-commande
gtmi_vcard_is_nfc_product()                      // Détection produit (à étendre)  
gtmi_vcard_send_ready_notification()             // Emails (à adapter)

// Système tracking existant (à réutiliser)
wp_statistics table                               // Base tracking (compatible)
gtmi_vcard REST APIs                             // Architecture API (à étendre)

// Dashboard framework (à adapter)  
gtmi-vcard/templates/dashboard/simple/           // Structure UI réutilisable
gtmi-vcard/assets/css/dashboard.css             // Styles de base
```

### **Nouvelles structures nécessaires 🆕**
```php
// Nouveau Custom Post Type
google_reviews_profile                           // Profils Avis Google partagés

// Nouvelles tables  
wp_google_reviews_elements                      // Cartes/plaques par profil
wp_google_reviews_tracking                      // Tracking scans détaillé  

// Nouveaux templates
templates/dashboard/google-reviews/             // Dashboard spécialisé Avis Google
templates/single-google_reviews_profile.php    // Page publique redirection

// Nouvelles APIs
rest-google-reviews.php                         // Endpoints spécialisés
```

---

## 🏗️ **Structure Fichiers de Développement**

### **Arborescence Proposée**
```
wp-content/plugins/gtmi-vcard/
├── includes/
│   ├── google-reviews/                         # 🆕 Logique Avis Google
│   │   ├── class-google-reviews-manager.php    # Gestionnaire principal
│   │   ├── class-google-reviews-profile.php    # Gestion profils
│   │   ├── class-google-reviews-tracking.php   # Système tracking
│   │   ├── google-reviews-functions.php        # Fonctions utilitaires
│   │   └── google-reviews-hooks.php            # Hooks WooCommerce
│   │
│   ├── utils/
│   │   └── after_order.php                     # 🔄 Étendre détection produits
│   │
│   └── api/
│       └── rest-google-reviews.php             # 🆕 APIs REST spécialisées
│
├── templates/
│   ├── dashboard/
│   │   └── google-reviews/                     # 🆕 Dashboard Avis Google
│   │       ├── profile-config.php              # Configuration profil
│   │       ├── elements-mapping.php            # Mapping emplacements  
│   │       ├── analytics-dashboard.php         # Analytics détaillées
│   │       └── qr-codes-generator.php          # Génération QR codes
│   │
│   └── single-google_reviews_profile.php      # 🆕 Page publique redirection
│
├── assets/
│   ├── css/
│   │   ├── google-reviews-dashboard.css        # 🆕 Styles dashboard AG
│   │   ├── analytics-charts.css                # 🆕 Styles graphiques
│   │   └── qr-codes-display.css               # 🆕 Styles QR codes
│   │
│   └── js/
│       ├── google-reviews-config.js            # 🆕 Configuration profil
│       ├── elements-mapping.js                 # 🆕 Gestion emplacements
│       ├── analytics-charts.js                 # 🆕 Graphiques Chart.js
│       └── qr-codes-generator.js              # 🆕 Génération QR
│
└── database/
    ├── migrations/
    │   ├── 003-create-google-reviews-tables.sql # Tables Avis Google
    │   └── 004-extend-product-categories.sql    # Catégories produits
    │
    └── google-reviews-schema.sql              # 🆕 Structure complète
```

---

## 📅 **Planning Développement Détaillé**

### **PHASE 1 : Architecture Base Avis Google (2-3 jours)**

#### **JOUR 1 : Base de Données et Custom Post Type**

**Tâche 1.1 : Tables et structures (3h)**
```sql
-- Prompt Développement 1.1
-- Créer tables pour système Avis Google

-- Table profils éléments NFC (cartes + plaques)
CREATE TABLE wp_google_reviews_elements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,                    -- Post google_reviews_profile
    order_id INT NOT NULL,                      -- Commande WooCommerce
    element_identifier VARCHAR(20) NOT NULL UNIQUE, -- AG2045-1, AGP2045-3
    element_type ENUM('card', 'plaque') NOT NULL,
    element_position INT NOT NULL,              -- 1,2,3... dans commande
    element_label VARCHAR(100) DEFAULT 'À configurer', -- "Table 1", "Vitrine"
    scans_count INT DEFAULT 0,
    last_scan_at DATETIME NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX (profile_id),
    INDEX (element_identifier),
    INDEX (order_id)
);

-- Table tracking détaillé par élément
CREATE TABLE wp_google_reviews_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,                    -- Post google_reviews_profile
    element_identifier VARCHAR(20) NOT NULL,    -- AG2045-5, AGP2045-1
    element_type ENUM('card', 'plaque') NOT NULL,
    element_label VARCHAR(100),                 -- "Table 5", "Vitrine"
    event_type ENUM('scan', 'redirect', 'error') DEFAULT 'scan',
    user_agent TEXT,
    ip_address VARCHAR(45),
    referer_url TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX (profile_id, element_identifier),
    INDEX (created_at),
    INDEX (event_type)
);

-- Extension catégories produits WooCommerce
ALTER TABLE wp_postmeta 
ADD INDEX meta_nfc_category (meta_key, meta_value) 
WHERE meta_key = 'nfc_product_category';
```

**Tâche 1.2 : Custom Post Type google_reviews_profile (2h)**
```php
// Prompt Développement 1.2
/*
Contexte : Créer Custom Post Type pour profils Avis Google partagés.

Tâche :
1. Créer CPT google_reviews_profile avec rewrite slug 'google-avis'
2. Métaboxes admin pour configuration
3. Champs ACF pour métadonnées profil
4. URL structure : /google-avis/{slug}/
5. Permissions et capabilities

Custom Post Type à créer :
*/

function register_google_reviews_profile_post_type() {
    register_post_type('google_reviews_profile', [
        'labels' => [
            'name' => 'Profils Avis Google',
            'singular_name' => 'Profil Avis Google',
            'add_new' => 'Ajouter profil',
            'add_new_item' => 'Ajouter nouveau profil',
            'edit_item' => 'Modifier profil',
            'new_item' => 'Nouveau profil',
            'view_item' => 'Voir profil',
            'search_items' => 'Rechercher profils',
            'not_found' => 'Aucun profil trouvé',
        ],
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => 'edit.php?post_type=virtual_card', // Sous-menu vCard
        'query_var' => true,
        'rewrite' => [
            'slug' => 'google-avis',
            'with_front' => false
        ],
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => ['title', 'custom-fields'],
        'show_in_rest' => true
    ]);
}
add_action('init', 'register_google_reviews_profile_post_type');

// Champs ACF profil Avis Google
if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group([
        'key' => 'group_google_reviews_profile',
        'title' => 'Configuration Profil Avis Google',
        'fields' => [
            [
                'key' => 'field_google_business_url',
                'label' => 'URL Google Business',
                'name' => 'google_business_url',
                'type' => 'url',
                'instructions' => 'URL de votre page d\'avis Google Business',
                'placeholder' => 'https://g.page/r/votre-etablissement/review'
            ],
            [
                'key' => 'field_company_name',
                'label' => 'Nom établissement',
                'name' => 'company_name', 
                'type' => 'text',
                'required' => 1
            ],
            [
                'key' => 'field_order_id',
                'label' => 'Commande liée',
                'name' => 'order_id',
                'type' => 'number',
                'readonly' => 1
            ],
            [
                'key' => 'field_main_user_id',
                'label' => 'Propriétaire',
                'name' => 'main_user_id', 
                'type' => 'user',
                'readonly' => 1
            ],
            [
                'key' => 'field_total_elements',
                'label' => 'Total éléments NFC',
                'name' => 'total_elements',
                'type' => 'number',
                'readonly' => 1
            ]
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'google_reviews_profile'
                ]
            ]
        ]
    ]);
}

/*
Validation :
CPT créé, URLs /google-avis/abc123/ fonctionnelles, champs ACF disponibles.
*/
```

**Tâche 1.3 : Extension détection produits (2h)**
```php
// Prompt Développement 1.3
/*
Contexte : Étendre système détection produits pour inclure Avis Google.

Tâche :
1. Modifier gtmi_vcard_is_nfc_product() → nfc_detect_product_category()
2. Support catégories : vcard, google_reviews_card, google_reviews_plaque
3. Interface admin produits WooCommerce
4. Migration produits existants
5. Tests avec vrais produits

Extension fonction détection :
*/

function nfc_detect_product_category($product_id) {
    // Méthode 1 : Champ ACF nfc_product_category (préféré)
    $category = get_post_meta($product_id, 'nfc_product_category', true);
    if ($category && in_array($category, ['vcard', 'google_reviews_card', 'google_reviews_plaque', 'other'])) {
        return $category;
    }
    
    // Méthode 2 : Fallback par nom/slug produit
    $product = wc_get_product($product_id);
    if (!$product) return false;
    
    $product_name = strtolower($product->get_name());
    $product_slug = $product->get_slug();
    
    // Détection par mots-clés
    if (strpos($product_name, 'avis google') !== false || strpos($product_slug, 'avis-google') !== false) {
        if (strpos($product_name, 'plaque') !== false || strpos($product_slug, 'plaque') !== false) {
            return 'google_reviews_plaque';
        } else {
            return 'google_reviews_card';
        }
    }
    
    if (strpos($product_name, 'vcard') !== false || strpos($product_name, 'carte nfc') !== false) {
        return 'vcard';
    }
    
    // Méthode 3 : Fallback par catégories WooCommerce
    $product_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'slugs']);
    if (in_array('avis-google', $product_categories)) {
        return 'google_reviews_card'; // Default carte si pas précisé
    }
    if (in_array('vcard', $product_categories) || in_array('carte-nfc', $product_categories)) {
        return 'vcard';
    }
    
    return false; // Pas un produit NFC
}

// Remplacer ancienne fonction
function gtmi_vcard_is_nfc_product($product_id) {
    $category = nfc_detect_product_category($product_id);
    return $category !== false;
}

// Interface admin produits
function add_nfc_product_category_metabox() {
    add_meta_box(
        'nfc_product_settings',
        'Configuration NFC',
        'nfc_product_category_metabox_callback',
        'product',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'add_nfc_product_category_metabox');

function nfc_product_category_metabox_callback($post) {
    $current_category = get_post_meta($post->ID, 'nfc_product_category', true);
    
    wp_nonce_field('nfc_product_category_save', 'nfc_product_category_nonce');
    ?>
    <p>
        <label><strong>Type de produit NFC :</strong></label><br>
        <select name="nfc_product_category" style="width: 100%;">
            <option value="">Non-NFC</option>
            <option value="vcard" <?php selected($current_category, 'vcard'); ?>>Carte vCard (profils individuels)</option>
            <option value="google_reviews_card" <?php selected($current_category, 'google_reviews_card'); ?>>Carte Avis Google</option>
            <option value="google_reviews_plaque" <?php selected($current_category, 'google_reviews_plaque'); ?>>Plaque Avis Google</option>
            <option value="other" <?php selected($current_category, 'other'); ?>>Autre produit NFC</option>
        </select>
    </p>
    <p class="description">
        <strong>vCard :</strong> 1 carte = 1 profil individuel<br>
        <strong>Avis Google :</strong> X cartes = 1 profil partagé
    </p>
    <?php
}

function save_nfc_product_category_metabox($post_id) {
    if (!isset($_POST['nfc_product_category_nonce']) || 
        !wp_verify_nonce($_POST['nfc_product_category_nonce'], 'nfc_product_category_save')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_product', $post_id)) return;
    
    $category = sanitize_text_field($_POST['nfc_product_category']);
    if ($category) {
        update_post_meta($post_id, 'nfc_product_category', $category);
    } else {
        delete_post_meta($post_id, 'nfc_product_category');
    }
}
add_action('save_post', 'save_nfc_product_category_metabox');

/*
Validation :
Fonction détection étendue, interface admin fonctionnelle, catégorisation produits correcte.
*/
```

#### **JOUR 2 : Création Profils Avis Google**

**Tâche 2.1 : Logique création post-commande (4h)**
```php
// Prompt Développement 2.1
/*
Contexte : Étendre after_order.php pour créer profils Avis Google.

Tâche :
1. Modifier gtmi_vcard_order_payment_success() pour multi-types
2. Créer nfc_create_google_reviews_profile($order_id)
3. Générer identifiants AG{order}-{pos} et AGP{order}-{pos}
4. Sauvegarder éléments dans wp_google_reviews_elements
5. Test commande mixte : 5 vCard + 8 Avis Google

Fonction principale création Avis Google :
*/

function nfc_create_google_reviews_profile($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return false;
    
    $google_items = [];
    
    // Identifier tous items Avis Google dans la commande
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        $product_category = nfc_detect_product_category($product_id);
        
        if (in_array($product_category, ['google_reviews_card', 'google_reviews_plaque'])) {
            $google_items[] = [
                'item_id' => $item_id,
                'item' => $item,
                'product_id' => $product_id,
                'category' => $product_category,
                'quantity' => $item->get_quantity(),
                'type' => $product_category === 'google_reviews_card' ? 'card' : 'plaque'
            ];
        }
    }
    
    if (empty($google_items)) {
        error_log("NFC: No Google Reviews items found in order $order_id");
        return false;
    }
    
    // Créer profil Avis Google unique pour tous items
    $company_name = $order->get_billing_company() ?: ($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
    
    $profile_id = wp_insert_post([
        'post_title' => $company_name . ' - Avis Google - Commande #' . $order_id,
        'post_type' => 'google_reviews_profile',
        'post_status' => 'publish',
        'post_author' => $order->get_customer_id() ?: 1,
        'post_name' => sanitize_title($company_name . '-avis-google-' . $order_id) // Slug pour URL
    ]);
    
    if (is_wp_error($profile_id)) {
        error_log("NFC: Error creating Google Reviews profile: " . $profile_id->get_error_message());
        return false;
    }
    
    // Compter totaux et créer identifiants
    $total_cards = 0;
    $total_plaques = 0;
    $all_elements = [];
    
    // Compteurs positions séparés par type
    $card_position = 1;
    $plaque_position = 1;
    
    foreach ($google_items as $google_item) {
        $item = $google_item['item'];
        $type = $google_item['type'];
        $quantity = $google_item['quantity'];
        
        if ($type === 'card') $total_cards += $quantity;
        if ($type === 'plaque') $total_plaques += $quantity;
        
        // Générer identifiants pour cette ligne de commande
        for ($i = 1; $i <= $quantity; $i++) {
            if ($type === 'card') {
                $identifier = "AG{$order_id}-{$card_position}";
                $position = $card_position++;
            } else {
                $identifier = "AGP{$order_id}-{$plaque_position}";
                $position = $plaque_position++;
            }
            
            $element_data = [
                'identifier' => $identifier,
                'type' => $type,
                'position' => $position,
                'label' => 'À configurer'
            ];
            
            $all_elements[] = $element_data;
            
            // Sauvegarder élément en base  
            global $wpdb;
            $result = $wpdb->insert(
                $wpdb->prefix . 'google_reviews_elements',
                [
                    'profile_id' => $profile_id,
                    'order_id' => $order_id,
                    'element_identifier' => $identifier,
                    'element_type' => $type,
                    'element_position' => $position,
                    'element_label' => 'À configurer',
                    'status' => 'active'
                ]
            );
            
            if ($result === false) {
                error_log("NFC: Error inserting element $identifier: " . $wpdb->last_error);
            }
        }
    }
    
    // Sauvegarder métadonnées profil
    update_post_meta($profile_id, 'order_id', $order_id);
    update_post_meta($profile_id, 'main_user_id', $order->get_customer_id());
    update_post_meta($profile_id, 'company_name', $company_name);
    update_post_meta($profile_id, 'total_cards', $total_cards);
    update_post_meta($profile_id, 'total_plaques', $total_plaques);
    update_post_meta($profile_id, 'total_elements', $total_cards + $total_plaques);
    update_post_meta($profile_id, 'elements_list', $all_elements); // JSON pour référence rapide
    update_post_meta($profile_id, 'google_business_url', ''); // À configurer par client
    update_post_meta($profile_id, 'status', 'pending'); // pending, configured, active
    
    // Stats initiales (cache)
    update_post_meta($profile_id, 'total_scans_30d', 0);
    update_post_meta($profile_id, 'total_redirections_30d', 0);
    update_post_meta($profile_id, 'top_performer', '');
    
    error_log("NFC: Created Google Reviews profile $profile_id for order $order_id ($total_cards cards + $total_plaques plaques)");
    
    return $profile_id;
}

// Modifier hook principal pour supporter multi-types
function nfc_order_payment_success_extended($order_id) {
    if (!$order_id) return;
    
    error_log("NFC: Processing order $order_id for multi-type products");
    
    $order = wc_get_order($order_id);
    $created_profiles = [
        'vcards' => [],
        'google_reviews' => []
    ];
    
    $has_vcard_items = false;
    $has_google_reviews_items = false;
    
    // Analyser types de produits dans la commande
    foreach ($order->get_items() as $item) {
        $product_category = nfc_detect_product_category($item->get_product_id());
        
        if ($product_category === 'vcard') {
            $has_vcard_items = true;
        } elseif (in_array($product_category, ['google_reviews_card', 'google_reviews_plaque'])) {
            $has_google_reviews_items = true;
        }
    }
    
    // Créer vCards individuelles (Document 1/2)
    if ($has_vcard_items) {
        $vcards = nfc_enterprise_create_multiple_vcards($order_id);
        $created_profiles['vcards'] = $vcards ?: [];
    }
    
    // Créer profil Avis Google partagé (ce document)
    if ($has_google_reviews_items) {
        $google_profile = nfc_create_google_reviews_profile($order_id);
        if ($google_profile) {
            $created_profiles['google_reviews'][] = $google_profile;
        }
    }
    
    // Notifications email selon types créés
    if (!empty($created_profiles['vcards']) && !empty($created_profiles['google_reviews