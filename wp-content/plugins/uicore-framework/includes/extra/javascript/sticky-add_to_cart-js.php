<?php

$js .= "
jQuery(document).one('scroll click', function() {
    let cartFormEl = jQuery('form.cart');
    if (!jQuery('.uicore-sticky-add-to-cart').length && cartFormEl.length) {
        uiaddStickyCart(cartFormEl);
        uihandleScroll(cartFormEl);
        uisyncVariations(cartFormEl);
    }
});

function uiaddStickyCart(cartFormEl) {
    // Create the main element with classes
    var main = jQuery('<div>', {
        class: 'uicore-sticky-add-to-cart uicore-section uicore-box uicore-main-background'
    });

    // Create the container and add it to the main element
    var container = jQuery('<div>', { class: 'uicore-container' }).appendTo(main);

    // Get the product title and add it to the container
    var titleElement = jQuery('.product_title').length ? jQuery('.product_title') : jQuery('.uicore-page-title .uicore-title');
    if (titleElement.length) {
        jQuery('<h3>').text(titleElement.text()).appendTo(container);
    }

    // Clone the cart form and add it to the container
    var cartForm = cartFormEl.clone(true);
    if (cartForm.length) {
        container.append(cartForm);
    }

    // Copy form add to cart text single_add_to_cart_button
    var addToCartText = jQuery('.single_add_to_cart_button').first().text();
    var mobileAddToCart = jQuery('<div>', { class: 'elementor-button mobile-add-to-cart' }).text(addToCartText).appendTo(container);

    // Scroll to initial form on button click
    mobileAddToCart.on('click', function() {
        jQuery('html, body').animate({
            scrollTop: cartFormEl.offset().top - 80
        }, 500);
    });

    // Add the main element to the body
    jQuery('body').append(main);
}

function uihandleScroll(cartFormEl) {
    var main = jQuery('.uicore-sticky-add-to-cart');

    jQuery(window).on('scroll', function() {
        // Top position of the cart form
        var cartFormTop = cartFormEl.offset().top;
        // Scroll position (top of the viewport)
        var scrollPosition = jQuery(window).scrollTop();
        // Height of the viewport
        var windowHeight = jQuery(window).height();

        // Check if the form is completely out of view (scrolled past)
        if (scrollPosition > cartFormTop + cartFormEl.outerHeight()) {
            main.addClass('uicore-show');
        } else {
            main.removeClass('uicore-show');
        }
    }).trigger('scroll'); // Trigger scroll to check the initial position
}

function uisyncVariations(cartFormEl) {
    // Sync variation from both forms
    cartFormEl.find('.variations select').on('change', function() {
        var el = jQuery(this);
        var val = el.val();
        var attr = el.attr('name');
        jQuery('.variations select[name=\"' + attr + '\"]').not(el).val(val);

        // Apply selected class on swatch
        const swatchAttr = attr.replace('attribute_', '');
        jQuery('.uicore-swatch[data-attribute-name=\"' + swatchAttr + '\"]').removeClass('selected');
        jQuery('.uicore-swatch[data-attribute-name=\"' + swatchAttr + '\"][data-value=\"' + val + '\"]').addClass('selected');
    });
    // Sync quantity from both forms
    cartFormEl.find('.quantity input').on('change', function() {
        var val = jQuery(this).val();
        jQuery('.quantity input').not(this).val(val);
    });
}

";