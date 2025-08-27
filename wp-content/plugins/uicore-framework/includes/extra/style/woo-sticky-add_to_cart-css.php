<?php
$css .= '
.uicore-sticky-add-to-cart{
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    padding: 16px 0;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow:0px -2px 55px -29px rgb(0 0 0 / 10%), 0px -1px 3px rgb(0 0 0 / 1%);
    transform: translateY(100%);
    transition: transform 0.3s;
    --uicore-swatch-size: 26px;
    --uicore-woo-single-add-to-cart-height: 44px;
}
.uicore-sticky-add-to-cart.uicore-show{
    transform: translateY(0);
}
.uicore-sticky-add-to-cart .uicore-container{
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}
.uicore-sticky-add-to-cart h3{
    font-size: 1.5em;
    margin: 0;
}
.uicore-sticky-add-to-cart form.cart{
    display: flex;
    justify-content: center;
    align-items: center;
}
.single-product.woocommerce .uicore-sticky-add-to-cart .cart button.single_add_to_cart_button{
    width: auto;
}
.uicore-sticky-add-to-cart form.cart .variations tbody,
.woocommerce.single-product .uicore-sticky-add-to-cart form.cart .variations .value{
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 0 !important;
}
.woocommerce.single-product .uicore-sticky-add-to-cart form.cart .variations,
.uicore-sticky-add-to-cart .single_variation_wrap,
.uicore-sticky-add-to-cart .uicore-swatches-wrp{
    margin: 0;
}
.uicore-sticky-add-to-cart form.cart .variations tbody tr{
    margin-right: 13px;
}
.uicore-sticky-add-to-cart form.cart .variations tbody .label{
    display: none;
}
.uicore-sticky-add-to-cart form.cart .woocommerce-variation{
    display: none!important;
}
.woocommerce.single-product .uicore-sticky-add-to-cart form.cart .variations select{
    margin: 2px 0 0;
    height: 34px;
    line-height: 34px;
    padding-top: 0;
}
.woocommerce.single-product .uicore-sticky-add-to-cart form.cart .variations select.uicore-is-button{
    display:block!important;
}
.woocommerce.single-product .uicore-sticky-add-to-cart form.cart .variations select.uicore-is-button + .uicore-swatches-wrp,
.uicore-sticky-add-to-cart .mobile-add-to-cart{
    display: none;
}
@media (max-width: 768px){
    .uicore-sticky-add-to-cart{
        padding: 10px 0;
    }
    .uicore-sticky-add-to-cart h3,
    .uicore-sticky-add-to-cart form.cart{
        display: none;
    }
    .uicore-sticky-add-to-cart .mobile-add-to-cart{
        display: block;
        width: 100%;
    }
}
';
