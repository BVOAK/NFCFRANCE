<?php
defined("ABSPATH") || exit();
//INCLUDED IN CLASS JS

$js .= "
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.uicore-cart-icon.uicore-link').forEach(function(icon) {
        icon.addEventListener('click', function() {
            var parent = icon.parentNode;
            if (parent && parent.tagName && parent.tagName.toLowerCase() === 'a') {
                return;
            }
            document.body.classList.add('uicore-cart-active');
        });
    });

    var cartWrapper = document.getElementById('cart-wrapper');
    if (cartWrapper) {
        cartWrapper.addEventListener('click', function() {
            document.body.classList.remove('uicore-cart-active');
        });
    }

    var cartClose = document.getElementById('uicore-cart-close');
    if (cartClose) {
        cartClose.addEventListener('click', function() {
            document.body.classList.remove('uicore-cart-active');
        });
    }

    document.body.addEventListener('added_to_cart', function(e) {
        if (document.getElementById('uicore-site-header-cart')) {
            document.body.classList.add('uicore-cart-active');
        } else {
            var a = document.createElement('a');
            a.setAttribute('href', wc_add_to_cart_params.cart_url);
            a.textContent = wc_add_to_cart_params.i18n_view_cart;
            var div = document.createElement('div');
            div.className = 'uicore-added_to_cart';
            div.appendChild(a);
            document.body.appendChild(div);
            setTimeout(function () {
                var el = div;
                var opacity = 1;
                var fade = setInterval(function () {
                    if (opacity <= 0.05) {
                        clearInterval(fade);
                        if (el.parentNode) el.parentNode.removeChild(el);
                    } else {
                        opacity -= 0.05;
                        el.style.opacity = opacity;
                    }
                }, 25);
            }, 7000);
        }
    });
});
";

