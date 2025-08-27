/**
 * WooCommerce Integration - Utilitaires WooCommerce
 * Version basique - sera étendu si nécessaire
 */

if (typeof window.WCIntegration === 'undefined') {
    window.WCIntegration = class WCIntegration {
        constructor() {
            console.log('🛒 WooCommerce Integration initialisé');
        }
        
        // Méthodes utilitaires WooCommerce
        static formatPrice(price) {
            return `${price.toFixed(2).replace('.', ',')}€`;
        }
        
        static getCartUrl() {
            return window.nfcConfig?.cartUrl || '/panier/';
        }
    };
}

/**
 * Simple déplacement du bouton "Personnaliser en ligne"
 * Remplace le bouton single_add_to_cart_button
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Vérifier si c'est un produit configurable (ID 571)
    const productIdInput = document.querySelector('input[name="product_id"]');
    if (!productIdInput || productIdInput.value !== '571') {
        return; // Pas le bon produit
    }
    
    // Trouver le bouton "Personnaliser en ligne" (généré par le shortcode)
    const configuratorButton = document.querySelector('.nfc-configurator-button');
    
    // Trouver le bouton "Ajouter au panier" à remplacer
    const addToCartButton = document.querySelector('.single_add_to_cart_button');
    
    if (configuratorButton && addToCartButton) {
        // Déplacer le bouton configurateur à la place du bouton panier
        const parentContainer = addToCartButton.parentNode;
        parentContainer.insertBefore(configuratorButton, addToCartButton);
        
        console.log('✅ Bouton "Personnaliser en ligne" déplacé');
    }
    
}); 