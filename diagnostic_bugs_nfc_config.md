# 🔍 Diagnostic Complet : Bugs Pages Order-Received et Admin

## **ÉTAPE 1.1 : Analyse page order-received (client)**

### **🚨 BUGS IDENTIFIÉS**

#### **Bug #1 : Problème de redirection dans serve_customer_screenshot()**
**Localisation :** `nfc-customer-integration.php:167-177`

```php
// Code actuel - PROBLÉMATIQUE
try {
    if (class_exists('NFC_File_Handler')) {
        $_GET['action'] = 'nfc_view_screenshot';
        $_GET['nonce'] = wp_create_nonce('nfc_admin_view'); // ❌ PROBLÈME
        
        $file_handler = new NFC_File_Handler();
        $file_handler->view_screenshot();
    }
}
```

**Problème :** La méthode réécrit $_GET['action'] et génère un nonce admin, mais `view_screenshot()` dans le File Handler vérifie les permissions différemment pour admin/client.

#### **Bug #2 : Logique de permissions incohérente**
**Localisation :** `nfc-file-handler.php:83-93`

```php
// Dans view_screenshot()
$nonce_admin = wp_verify_nonce($_GET['nonce'] ?? '', 'nfc_admin_view');
$nonce_customer = wp_verify_nonce($_GET['nonce'] ?? '', "nfc_customer_screenshot_{$order_id}_{$item_id}");
```

**Problème :** Le client génère un nonce avec nom "nfc_admin_view" mais la vérification customer attend "nfc_customer_screenshot_{$order_id}_{$item_id}".

#### **Bug #3 : Template view-order.php incomplet**
**Localisation :** `wp-content/themes/uicore-pro/woocommerce/myaccount/view-order.php:66-95`

**Problème :** Le template a du code pour afficher les cartes NFC mais il est tronqué et probablement non terminé.

---

## **ÉTAPE 1.2 : Analyse page admin commande**

### **🚨 BUGS IDENTIFIÉS**

#### **Bug #4 : Méthodes serve_logo_file() et serve_screenshot_file() manquantes**
**Localisation :** `nfc-file-handler.php:50, 77`

```php
// Méthodes appelées mais NON DÉFINIES
$this->serve_logo_file($order_id, $item_id);        // ❌ MANQUANTE
$this->serve_screenshot_file($order_id, $item_id, $type); // ❌ MANQUANTE
```

**Problème :** Les handlers `download_logo()` et `download_screenshot()` appellent des méthodes qui n'existent pas.

#### **Bug #5 : Méthode can_view_screenshot() manquante**
**Localisation :** `nfc-file-handler.php:74`

```php
if (!$this->can_view_screenshot()) { // ❌ MÉTHODE MANQUANTE
```

#### **Bug #6 : Pas de support logo verso**
**Analyse :** Le système ne gère que le logo recto. Pas de support pour télécharger séparément recto/verso comme prévu dans les URLs.

#### **Bug #7 : Gestion screenshot_info incomplète**
**Localisation :** `nfc-file-handler.php:226-250`

```php
$screenshot_info = $item->get_meta('_nfc_screenshot_info');
if (!$screenshot_info) {
    throw new Exception('Aucun screenshot trouvé'); // ❌ Pas de fallback
}
```

**Problème :** Pas de fallback vers d'autres métadonnées si `_nfc_screenshot_info` n'existe pas.

---

## **🎯 CORRECTIONS NÉCESSAIRES**

### **CORRECTION #1 : Fixer serve_customer_screenshot()**

```php
public function serve_customer_screenshot() {
    $order_id = intval($_GET['order_id'] ?? 0);
    $item_id = intval($_GET['item_id'] ?? 0);
    $type = sanitize_text_field($_GET['type'] ?? 'thumb');
    $nonce = sanitize_text_field($_GET['nonce'] ?? '');
    
    // Vérifier le nonce CLIENT (pas admin)
    if (!wp_verify_nonce($nonce, "nfc_customer_screenshot_{$order_id}_{$item_id}")) {
        wp_die('Accès non autorisé', 'Erreur', ['response' => 403]);
    }
    
    // Vérifier les permissions client
    if (!$this->can_customer_view_order($order_id)) {
        wp_die('Accès refusé', 'Erreur', ['response' => 403]);
    }
    
    try {
        // Utiliser une méthode spécifique client, pas admin
        $this->display_customer_screenshot($order_id, $item_id, $type);
    } catch (Exception $e) {
        error_log('NFC: Erreur screenshot client: ' . $e->getMessage());
        wp_die('Screenshot non disponible', 'Erreur', ['response' => 404]);
    }
}
```

### **CORRECTION #2 : Créer les méthodes manquantes dans NFC_File_Handler**

**Méthodes à créer :**
- `serve_logo_file($order_id, $item_id, $type = 'recto')`
- `serve_screenshot_file($order_id, $item_id, $type)`
- `can_view_screenshot()`
- `display_customer_screenshot()` (spécifique client)

### **CORRECTION #3 : Support logo verso**

Modifier `serve_logo_file()` pour accepter `$type = 'recto'|'verso'` et chercher dans :
- `_nfc_image_recto_data` pour recto
- `_nfc_logo_verso_data` pour verso

### **CORRECTION #4 : Template view-order.php complet**

Terminer l'implémentation du bloc NFC dans le template client.

---

## **📝 MÉTADONNÉES À VÉRIFIER**

### **Métadonnées Order Items attendues :**
- `_nfc_config_complete` : Configuration JSON complète ✅
- `_nfc_screenshot_info` : Infos des fichiers screenshot ❓
- `_nfc_image_recto_data` : Données base64 logo recto ❓
- `_nfc_logo_verso_data` : Données base64 logo verso ❓

### **Tests à effectuer :**
1. Vérifier que ces métadonnées sont sauvées lors de la commande
2. Tester la structure JSON de `_nfc_screenshot_info`
3. Vérifier le format base64 des images

---

## **🛠️ PLAN DE CORRECTIONS**

### **Phase 1 : Corrections critiques (2h)**
1. ✅ Fixer `serve_customer_screenshot()` dans Customer Integration
2. ✅ Créer les méthodes manquantes dans File Handler
3. ✅ Ajouter support logo verso
4. ✅ Implementer `can_view_screenshot()` et permissions

### **Phase 2 : Templates et UX (3h)**
1. ✅ Compléter template `view-order.php`
2. ✅ Ajouter lightbox pour screenshots
3. ✅ Interface admin complète dans les commandes
4. ✅ Tests end-to-end

---

## **💡 POINTS CLÉS IDENTIFIÉS**

1. **Architecture mixée** : La logique client/admin est mélangée, il faut séparer clairement
2. **Nonces incohérents** : Différentes conventions de nommage selon le contexte
3. **Métadonnées floues** : Plusieurs formats possibles, pas de standardisation
4. **Pas de fallbacks** : Erreurs brutales si les fichiers manquent
5. **Téléchargements cassés** : Méthodes core manquantes côté admin

**Conclusion :** Les bugs sont bien identifiés et localisés. On peut maintenant passer aux corrections méthodiquement.