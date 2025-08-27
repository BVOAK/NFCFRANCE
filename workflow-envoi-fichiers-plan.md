# Plan d'Action - Workflow Envoi Fichiers

## 🎯 **Objectif**
Système complet d'envoi de fichiers de personnalisation post-commande avec workflow de validation admin

---

## 📋 **Spécifications fonctionnelles**

### **Cas d'usage client :**
1. Client choisit "Commander et envoyer fichiers" (vs "Personnaliser en ligne")
2. Commande payée → Statut "En cours de validation" → Email automatique
3. Client accède espace upload → Envoie fichiers + commentaires
4. Admin valide/refuse → Email automatique → Statut commande mis à jour
5. Si refus → Client peut re-upload, si validation → Production lancée

### **Workflow statuts commande :**
```
Commande payée
    ↓
Statut "En cours de validation" 
    ↓ (Email automatique client)
Upload fichiers activé
    ↓ (Client envoie fichiers)
Statut "Fichiers reçus"
    ↓ (Email notification admin)
Validation admin (manuelle V1)
    ↓
┌─ Validation ✅ ─────────┐    ┌─ Refus ❌ ──────────────┐
│ Statut "Fichiers validés" │    │ Statut "Fichiers refusés" │
│ Upload désactivé          │    │ Upload réactivé           │  
│ Production lancée         │    │ Email correction client   │
└──────────────────────────┘    └──────────────────────────┘
```

---

## 🔧 **Architecture technique**

### **1. Plugin recommandé : "Checkout Files Upload for WooCommerce"**
**Avantages :**
- ✅ Gratuit et maintenu activement
- ✅ Upload après commande supporté
- ✅ Compatible HPOS (WooCommerce moderne)
- ✅ Validation admin intégrée
- ✅ Emails automatiques

**Alternative premium :** "File Upload for WooCommerce" ($79/an) si besoins avancés

### **2. Configuration par produit**
```php
// Produits nécessitant envoi fichiers
$file_upload_products = [
    572 => [ // Carte Bois personnalisée
        'formats' => ['PDF', 'AI', 'PNG', 'JPG', 'SVG'],
        'max_size' => '10MB',
        'max_files' => 3,
        'dpi_min' => 300,
        'instructions' => 'Formats vectoriels recommandés (AI, PDF). Images minimum 300 DPI.'
    ],
    573 => [ // Carte Metal personnalisée  
        'formats' => ['PDF', 'AI', 'PNG', 'JPG'],
        'max_size' => '10MB', 
        'max_files' => 2,
        'dpi_min' => 300,
        'instructions' => 'Gravure laser : couleurs converties en niveaux de gris.'
    ],
    581 => [ // Avis Google personnalisé
        'formats' => ['PDF', 'AI', 'PNG', 'JPG'],
        'max_size' => '5MB',
        'max_files' => 2, 
        'dpi_min' => 150,
        'instructions' => 'Logo + éléments graphiques pour page intermédiaire.'
    ]
];
```

### **3. Contraintes techniques par matériau**
```php
// Configuration inspirée https://www.kipful.com/produit/carte-bois/
class NFC_Print_Constraints {
    
    public static function get_constraints($product_id) {
        $constraints = [
            572 => [ // Carte Bois (Kipful inspired)
                'material' => 'Bois naturel éco-responsable',
                'dimensions' => '85,60 × 53,98 mm (standard carte de visite)',
                'epaisseur' => '0,8 mm',
                'impression' => 'Gravure laser haute précision',
                'formats_acceptes' => ['PDF', 'AI', 'EPS', 'PNG', 'JPG'],
                'resolution_min' => '300 DPI',
                'couleurs' => 'Gravure monochrome (effet bois naturel)',
                'zone_impression' => 'Recto/Verso complet avec marges 2mm',
                'delai_production' => '3-5 jours ouvrés',
                'instructions' => [
                    'Éviter les aplats de couleur (rendu en gravure)',
                    'Privilégier contours nets et textes > 8pt',
                    'Zone NFC préservée (coin inférieur droit)'
                ]
            ],
            
            573 => [ // Carte Metal (Kipful inspired)  
                'material' => 'Acier inoxydable brossé',
                'dimensions' => '85,60 × 53,98 mm',
                'epaisseur' => '0,5 mm',
                'impression' => 'Gravure laser + anodisation',
                'formats_acceptes' => ['PDF', 'AI', 'EPS', 'PNG'],
                'resolution_min' => '300 DPI', 
                'couleurs' => 'Gravure laser (effet métal) + 1 couleur anodisation possible',
                'zone_impression' => 'Recto/Verso avec contraintes gravure',
                'delai_production' => '5-7 jours ouvrés',
                'instructions' => [
                    'Gravure : contours fins et textes précis recommandés',  
                    'Anodisation : 1 couleur unie possible (bleu, rouge, noir)',
                    'Éviter dégradés complexes'
                ]
            ],
            
            581 => [ // Avis Google personnalisé
                'material' => 'Support PVC + page web personnalisée', 
                'formats_acceptes' => ['PDF', 'PNG', 'JPG', 'SVG'],
                'resolution_min' => '150 DPI (web)',
                'couleurs' => 'RVB web standard',
                'utilisation' => 'Logo + éléments page intermédiaire avant redirection',
                'delai_production' => '1-2 jours ouvrés',
                'instructions' => [
                    'Logo format carré recommandé (500×500px min)',
                    'Couleurs de marque pour page intermédiaire', 
                    'Message personnalisé optionnel'
                ]
            ]
        ];
        
        return $constraints[$product_id] ?? null;
    }
}
```

---

## 🛒 **Intégration fiche produit**

### **Modification fiche produit WooCommerce :**
```php  
// functions.php ou plugin hook
add_action('woocommerce_single_product_summary', 'nfc_custom_product_buttons', 25);

function nfc_custom_product_buttons() {
    global $product;
    $product_id = $product->get_id();
    
    // Produits avec configurateur ET option fichiers
    $configurable_products = [571, 572, 573]; // PVC, Bois, Metal
    
    if (in_array($product_id, $configurable_products)) {
        // Masquer le bouton Add to Cart par défaut
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
        
        echo '<div class="nfc-product-options mt-4">';
        
        // Option 1: Configurateur  
        $configurator_url = get_configurator_url($product_id);
        echo '<a href="' . $configurator_url . '" class="btn btn-primary btn-lg w-100 mb-3">';
        echo '<i class="fas fa-magic me-2"></i>Personnaliser en ligne';
        echo '</a>';
        
        // Option 2: Envoi fichiers
        echo '<button type="button" class="btn btn-outline-primary btn-lg w-100" onclick="addToCartWithFiles(' . $product_id . ')">';
        echo '<i class="fas fa-upload me-2"></i>Commander et envoyer mes fichiers';
        echo '</button>';
        
        // Informations contraintes
        $constraints = NFC_Print_Constraints::get_constraints($product_id);
        if ($constraints) {
            echo '<div class="file-constraints mt-3 p-3 bg-light rounded">';
            echo '<small class="text-muted">';
            echo '<strong>Fichiers acceptés :</strong> ' . implode(', ', $constraints['formats_acceptes']) . '<br>';
            echo '<strong>Résolution :</strong> ' . $constraints['resolution_min'] . ' minimum<br>';
            echo '<strong>Délai :</strong> ' . $constraints['delai_production'];
            echo '</small>';
            echo '</div>';
        }
        
        echo '</div>';
    }
}

// JavaScript pour ajout panier avec marqueur "envoi fichiers"
function nfc_product_scripts() {
    if (is_product()) {
        ?>
        <script>
        function addToCartWithFiles(productId) {
            // Ajouter au panier avec meta "requires_files"
            jQuery.post(wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'), {
                product_id: productId,
                quantity: 1,
                'custom_data[requires_files]': true
            }, function(response) {
                if (response.error) {
                    alert('Erreur lors de l\'ajout au panier');
                } else {
                    // Rediriger vers panier avec message
                    window.location.href = wc_add_to_cart_params.cart_url + '?files_required=1';
                }
            });
        }
        </script>
        <?php
    }
}
add_action('wp_footer', 'nfc_product_scripts');
```

---

## 📧 **Système d'emails automatiques**

### **Templates emails (à customiser) :**
```php
// includes/emails/class-files-upload-emails.php
class NFC_Files_Upload_Emails {
    
    public static function send_files_request_email($order_id) {
        $order = wc_get_order($order_id);
        $customer_email = $order->get_billing_email();
        $customer_name = $order->get_billing_first_name();
        
        $upload_url = home_url('/my-account/orders/') . $order_id . '/?upload_files=1';
        
        $subject = 'Envoyez vos fichiers de personnalisation - Commande #' . $order_id;
        
        $message = "
        <h2>Bonjour {$customer_name},</h2>
        
        <p>Votre commande <strong>#{$order_id}</strong> a été confirmée !</p>
        
        <p>Pour finaliser la personnalisation de votre produit, merci d'envoyer vos fichiers d'impression :</p>
        
        <p style='text-align: center; margin: 30px 0;'>
            <a href='{$upload_url}' 
               style='background: #0040C1; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                📁 Envoyer mes fichiers
            </a>
        </p>
        
        <h3>Contraintes techniques :</h3>
        <ul>
            <li>✅ Formats acceptés : PDF, AI, PNG, JPG</li>
            <li>✅ Résolution minimum : 300 DPI</li>
            <li>✅ Taille maximum : 10 MB par fichier</li>
            <li>✅ Maximum 3 fichiers</li>
        </ul>
        
        <p><strong>⏱️ Délai :</strong> Merci d'envoyer vos fichiers sous 48h pour respecter les délais de production.</p>
        
        <p>Questions ? Répondez directement à cet email.</p>
        
        <p>L'équipe NFC France</p>
        ";
        
        return wp_mail($customer_email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
    }
    
    public static function send_admin_notification($order_id, $files) {
        $order = wc_get_order($order_id);
        $admin_email = get_option('admin_email');
        
        $subject = 'Nouveaux fichiers reçus - Commande #' . $order_id;
        
        $files_list = '';
        foreach($files as $file) {
            $files_list .= "<li>{$file['name']} ({$file['size']})</li>";
        }
        
        $validation_url = admin_url('post.php?post=' . $order_id . '&action=edit');
        
        $message = "
        <h2>Nouveaux fichiers de personnalisation</h2>
        
        <p>Le client a envoyé ses fichiers pour la commande <strong>#{$order_id}</strong></p>
        
        <p><strong>Client :</strong> {$order->get_billing_first_name()} {$order->get_billing_last_name()}</p>
        <p><strong>Produit :</strong> {$order->get_items()[0]->get_name()}</p>
        
        <h3>Fichiers reçus :</h3>
        <ul>{$files_list}</ul>
        
        <p style='text-align: center; margin: 30px 0;'>
            <a href='{$validation_url}' 
               style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px;'>
                👀 Valider les fichiers
            </a>
        </p>
        ";
        
        return wp_mail($admin_email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
    }
}
```

---

## 🎛️ **Interface admin validation**

### **Metabox commande WooCommerce étendue :**
```php
// admin/class-order-files-metabox.php
add_action('add_meta_boxes', 'nfc_add_order_files_metabox');

function nfc_add_order_files_metabox() {
    add_meta_box(
        'nfc_order_files',
        'Fichiers de personnalisation',
        'nfc_order_files_metabox_content',
        'shop_order',
        'normal',
        'high'
    );
}

function nfc_order_files_metabox_content($post) {
    $order = wc_get_order($post->ID);
    $uploaded_files = get_post_meta($post->ID, '_uploaded_files', true) ?: [];
    $files_status = get_post_meta($post->ID, '_files_status', true) ?: 'pending';
    
    if (empty($uploaded_files)) {
        echo '<p>Aucun fichier envoyé pour cette commande.</p>';
        return;
    }
    
    echo '<div class="nfc-files-validation">';
    
    // Liste des fichiers
    echo '<h4>Fichiers reçus (' . count($uploaded_files) . ') :</h4>';
    echo '<div class="files-list">';
    
    foreach($uploaded_files as $file) {
        $file_url = wp_upload_dir()['baseurl'] . '/order-files/' . $file['filename'];
        $file_path = wp_upload_dir()['basedir'] . '/order-files/' . $file['filename'];
        
        echo '<div class="file-item mb-3 p-3 border rounded">';
        echo '<div class="d-flex justify-content-between align-items-center">';
        echo '<div>';
        echo '<strong>' . esc_html($file['original_name']) . '</strong><br>';
        echo '<small class="text-muted">Taille: ' . size_format(filesize($file_path)) . ' | Envoyé le: ' . $file['upload_date'] . '</small>';
        echo '</div>';
        echo '<div>';
        echo '<a href="' . $file_url . '" target="_blank" class="btn btn-sm btn-outline-primary">Télécharger</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Actions de validation
    echo '<div class="validation-actions mt-4">';
    echo '<h4>Actions :</h4>';
    
    echo '<div class="btn-group" role="group">';
    echo '<button type="button" class="btn btn-success" onclick="validateFiles(' . $post->ID . ')">✅ Valider les fichiers</button>';
    echo '<button type="button" class="btn btn-danger" onclick="rejectFiles(' . $post->ID . ')">❌ Refuser les fichiers</button>';
    echo '</div>';
    
    echo '<div class="mt-3">';
    echo '<label>Message au client (optionnel) :</label>';
    echo '<textarea class="form-control" id="validation-message" rows="3" placeholder="Message de validation ou corrections demandées..."></textarea>';
    echo '</div>';
    
    echo '</div>';
    
    echo '</div>';
    
    // JavaScript pour les actions
    ?>
    <script>
    function validateFiles(orderId) {
        const message = document.getElementById('validation-message').value;
        
        jQuery.post(ajaxurl, {
            action: 'nfc_validate_order_files',
            order_id: orderId,
            message: message,
            validation: 'approve'
        }, function(response) {
            if (response.success) {
                alert('Fichiers validés ! Email envoyé au client.');
                location.reload();
            } else {
                alert('Erreur lors de la validation.');
            }
        });
    }
    
    function rejectFiles(orderId) {
        const message = document.getElementById('validation-message').value;
        if (!message) {
            alert('Veuillez indiquer les corrections à apporter.');
            return;
        }
        
        jQuery.post(ajaxurl, {
            action: 'nfc_validate_order_files',
            order_id: orderId,
            message: message,
            validation: 'reject'
        }, function(response) {
            if (response.success) {
                alert('Fichiers refusés. Email de correction envoyé.');
                location.reload();
            } else {
                alert('Erreur lors du refus.');
            }
        });
    }
    </script>
    <?php
}
```

---

## 📊 **Planning développement (Post-démo)**

### **Semaine 2 (9-13 septembre) - 5 jours**

**Jour 1-2 : Configuration plugin et boutons produits**
- [ ] Installation/config "Checkout Files Upload"
- [ ] Boutons fiches produits (Personnaliser / Envoyer fichiers)
- [ ] Contraintes techniques par produit

**Jour 3-4 : Workflow validation admin**  
- [ ] Emails automatiques (demande + notifications)
- [ ] Interface admin validation commandes
- [ ] Gestion statuts commandes étendus

**Jour 5 : Intégration dashboard client**
- [ ] Page upload dans My Account
- [ ] Interface suivi statut fichiers
- [ ] Tests complets workflow

---

## ✅ **Critères de validation**

### **Workflow complet fonctionnel :**
- [ ] Client peut choisir entre configurateur et envoi fichiers
- [ ] Emails automatiques envoyés aux bons moments
- [ ] Admin peut valider/refuser avec commentaires
- [ ] Statuts commandes mis à jour automatiquement
- [ ] Upload réactivé en cas de refus

### **UX optimisée :**
- [ ] Instructions claires par type de produit
- [ ] Preview fichiers côté client
- [ ] Interface admin intuitive
- [ ] Messages d'erreur explicites

---

## 🔗 **Intégrations futures**

### **Dashboard adaptatif :**
- Statut fichiers visible depuis dashboard client
- Historique des envois par commande

### **Configurateurs :**
- Option "Envoyer fichiers" depuis configurateur
- Migration configuation → fichiers si besoin

---

*Plan workflow fichiers v1.0*  
*Système complet post-commande*