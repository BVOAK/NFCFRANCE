<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS
// ANY UPDATE HERE SHOULD ALSO BE CONSIDERED AS A POSSIBLE UPDATE OF WIDGETS STYLES
if($json_settings['woos_swatch_radius'] === ''){
    $sw_radius = 0;
    $sw_big_radius = 0;
}elseif($json_settings['woos_swatch_radius'] === 'small'){
    $sw_radius = 4;
    $sw_big_radius = 4;
}elseif($json_settings['woos_swatch_radius'] === 'medium'){
    $sw_radius = 8;
    $sw_big_radius = 6;
}elseif($json_settings['woos_swatch_radius'] === 'large'){
    $sw_radius = 12;
    $sw_big_radius = 100;
}

//btn radius needs to be img radius - 20% to match the image radius (we use this on badge too)
$inner_items_radius = (int)$json_settings['woo_img_radius'] - (int)($json_settings['woo_img_radius'] * 0.2);
$outer_items_radius = (int)$json_settings['woo_img_radius'] + (int)($json_settings['woo_img_radius'] * 0.2);

/*
** Important: Any update here should also be considered as a possible update of elementor widgets styles
*/

$css .= '
@media (max-width: ' . $br_points['lg'] .'px) {
    .single-product main.uicore,
    .woocommerce-page:not(.elementor-page) main.uicore{
        padding:' . $json_settings['woocommerce_padding']['t'] . 'px 0px;
    }
}


@media (max-width: ' .  $br_points['md'] . 'px) {
    .single-product main.uicore,
    .woocommerce-page:not(.elementor-page) main.uicore{
        padding:' .  $json_settings['woocommerce_padding']['m'] . 'px 0px;
    }
}


@media (min-width: ' . $br_points['lg'] .  'px) {
    .single-product main.uicore,
    .woocommerce-page:not(.elementor-page) main.uicore{
        padding:' .  $json_settings['woocommerce_padding']['d'] . 'px 0px;
    }
}
.woocommerce-page input[type=radio] {
    padding: 0!important;
}
.woocommerce ul.products li.product a img{
    margin:0;
}
.woocommerce ul.products .woocommerce-loop-product__link{
    position: relative;
}

.woocommerce span.onsale,
.woocommerce ul.products .woocommerce-loop-product__link .uicore-zoom-wrapper > span,
.woocommerce ul.products .woocommerce-loop-product__link > span{
    border-radius: '.$inner_items_radius.'px;
}
body {
    --uicore-woo-single-add-to-cart-height: ' . $json_settings['woos_add_to_cart_height'] . 'px;
    --uicore-woo-summary-width: ' . $json_settings['woos_summary_width'] . '%;
    --uicore-swatch-size : ' . $json_settings['woos_swatch_size'] . 'px;
    --uicore-swatch-radius : ' . $sw_radius . 'px;
    --uicore-swatch-big-radius : ' . $sw_big_radius . 'px;
    --uicore-swatch-border-width : ' . $json_settings['woos_swatch_border'] . 'px;
    --uicore-swatch-border: ' . $this->generateRGB($json_settings['woos_swatch_border_color']) . ';
    --ui-shop-grid-gap: ' . $json_settings['woo_grid_gap'] . 'px;
}

';

if($json_settings['woocommerce_sidebar_id'] !== 'none'){
    $css .= '
    .uicore-sidebar-toggle{
        display: flex;
        float: left;
        align-items: center;
        justify-content: center;
        background: #DDD;
        padding: 10px 5px;
        border-radius: 25px;
        margin-bottom: 15px;
        min-width: 155px;
        margin-right: 10px;
        cursor: pointer;
    }
    @media (max-width: ' .  $br_points['md'] . 'px) {
        .uicore-sidebar-toggle{
            display: none;
        }
    }

    .uicore-sidebar{
        transition: width 0.6s ease,
        transform 0.4s ease,
        opacity 0.2s ease;
    }
    .uicore-sidebar.sidebar-hidden{
        width: 0;
        opacity: 0;
        padding: 0 !important;
        transform: translateX(-50%);
    }
    .uicore-archive.content-expanded{
        width: 100%;
    }

    .filters-toggle-icon{
        display: block;
        border-top: 2px solid #000000;
        width: 20px;
        height: 9px;
        border-bottom: 2px solid #000000;
        position: relative;
    }
    .filters-toggle-icon span.line{
        border: 2px solid #000000;
        border-radius: 50px;
        background-color: #fff;
        position: absolute;
        height: 6px;
        width: 6px;
        transition: transform 0.3s ease;
        display: block;
        content: "";
    }
    .filters-toggle-icon span.line.top{
        top: -4px;
        left: 3px;
    }
    .filters-toggle-icon span.line.bottom{
        bottom: -4px;
        left: 3px;
    }

    .uicore-sidebar-toggle .text-wrap{
        line-height: 1em;
        display: flex;
        gap: 5px;
        margin-left: 10px;
    }

    .uicore-header-elements-wrp.uicore-right{
        display: flex;
        justify-content: flex-end;
    }



    .ui-filters-drawer .ui-drawer-wrapp{
        background:white;
    }
    .ui-filters-buton{
        position:fixed;
        left: 20px;
        bottom: 20px;
        border-radius: var(--ui-radius);
        background:white;
        box-shadow: 0 0 10px rgba(0,0,0,.1);
        width: 50px;
        height: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
    }
    .ui-filters-drawer aside.uicore-sidebar{
        padding:0;
        width: 100%;
    }
    .ui-filters-drawer .ui-drawer-content {
        justify-content: flex-start;
    }
    @media screen and (max-width: ' .  $br_points['md'] . 'px){
        .uicore-woo > aside.uicore-sidebar{
            display:none;
        }
    }
    @media screen and (min-width: ' .  $br_points['md'] . 'px){
        .ui-drawer-toggle {
            display:none;
        }

    }
    ';

    if($json_settings['woocommerce_sidebar'] === 'side drawer'){
        $css .= '
            @media screen and (min-width:' .  $br_points['md'] . 'px){
                .ui-filters-buton {
                    display:flex;
                }
                .uicore-sidebar-toggle{
                    display: block;
                }

            }
        ';
    }
}

/*
*SINGLE PRODUCT
*/

//category display in single product
if($json_settings['woos_category'] === 'false'){
    $css .= '
    .woocommerce .product_meta .posted_in{
        display:none!important;
    }
    ';
}
//tags display in single product
if($json_settings['woos_tags'] === 'false'){
    $css .= '
    .woocommerce .product_meta .tagged_as{
        display:none!important;
    }
    ';
}
//sku display in single product
if($json_settings['woos_sku'] === 'false'){
    $css .= '
    .woocommerce .product_meta .sku_wrapper{
        display:none!important;
    }
    ';

}

// Column gallery
if($json_settings['woos_gallery_radius'] != '0') {
    $css .= '
    .woocommerce .flex-control-thumbs li img,
    .woocommerce-product-gallery .woocommerce-product-gallery__image{
        border-radius: ' . $json_settings['woos_gallery_radius'] . 'px;
        overflow: hidden;
    }
    ';
    $outer_items_radius = (int)$json_settings['woos_gallery_radius'] - (int)($json_settings['woos_gallery_radius'] * 0.2);
    $css .= '
    .woocommerce .uicore-summary-gallery .onsale{
        border-radius: '.$outer_items_radius.'px;
    }';

}
if( in_array($json_settings['woos_product_gallery'], ['grid_column', 'grid_column_2']) ) {
    $css .= '
    div.product{
        --uicore-gallery-gap: '.$json_settings['woos_gallery_gap'].'px;
    }
    ';
}else{
    $css .= '
    .woocommerce div.product div.images .flex-control-thumbs {
        overflow: visible;
    }
    .woocommerce div.product .flex-control-thumbs {
        display: flex;
        flex-flow: wrap;
        gap: 15px;
    }
    ';
    if($json_settings['woos_product_gallery'] === ''){
        $css .= '
        .woocommerce div.product div.images .flex-control-thumbs li {
            margin-top:15px;
            width:calc((100% - 45px) / 4)
        }
        ';
    }
}

// Two Columns gallery
if($json_settings['woos_product_gallery'] === 'grid_column_2') {
    $css .= '
    div.product{
        --uicore-gallery-columns: 2;
    }
    ';
}

//rating style
if($json_settings['woos_rating_style'] === 'bar'){
    $css .= '
    .woocommerce .star-rating,
    .woocommerce .woocommerce-product-rating a {
        font-size: 13px;
        color: var(--uicore-headline-color);
        vertical-align: text-bottom;
    }
    .woocommerce .star-rating{
        letter-spacing: 3px;
        width: 84px;
        color:#ffb62b;
        height: 16px;
        border: 1px solid var(--uicore-headline-color);
        border-radius: 4px;
        position:relative;
        margin-top: 9px!important;
    }
    .woocommerce .star-rating:before{
        color:transparent;
    }
    .woocommerce .star-rating  span{
       top:auto;
       font-family: var(--uicore-typography--p-f);
       letter-spacing: 0.5px;
    }
    .woocommerce .star-rating > span{
        top:1px;
        left:1px;
        bottom:1px;
        padding:0;
        border-radius: 4px;
    }
    .woocommerce .star-rating span:before {
        background: var(--uicore-headline-color);
        border-radius: 4px;
        color: transparent;
        font-family: WooCommerce;
    }
    .woocommerce .star-rating strong.rating{
        position: absolute;
        color: white;
        font-size: 11px;
        left: 2px;
        line-height: 12px;
    }
    .woocommerce .star-rating strong.rating:after{
        content: "/5.0";
    }
    .woocommerce .comment-text .star-rating {
        margin-top: 1px;
    }


    .woocommerce .stars > span{
        border: 1px solid var(--uicore-headline-color);
        border-radius: 4px;
        display: inline-flex;
        padding: 0 1px;
    }
    .woocommerce p.stars a {
        height: calc(1em - 3px);
        margin-top: 1px;
        margin-bottom: 1px;
        width: 2em;
        color: transparent;
    }
    .woocommerce .stars .star-1 {
        background-color: #e0e0e0;
        border-radius: 3px 0 0 3px;
    }
    .woocommerce .stars .star-2 { background-color: #d0d0d0; }
    .woocommerce .stars .star-3 { background-color: #c0c0c0; }
    .woocommerce .stars .star-4 { background-color: #b0b0b0; }
    .woocommerce .stars .star-5 {
        background-color: #a0a0a0;
        border-radius: 0 3px 3px 0;
    }

    .woocommerce .stars:hover a{
        background-color: var(--uicore-headline-color)!important;
    }

    .woocommerce .stars a:hover ~ a,
    .woocommerce .stars a:focus ~ a {
        background-color: transparent!important; /* Keep right-side segments dimmed when hovering */
    }
    ';
}else{
    $css .= '
    .woocommerce .woocommerce-product-rating {
        line-height: 1;
        margin-bottom: 1.2em !important;
        display: flex;
        align-items: center;

    }
    .woocommerce .woocommerce-product-rating a {
        font-size: 13px;
        color: var(--uicore-headline-color);
    }
    .woocommerce .star-rating{
        color:#ffb62b;
        font-size: 13px!important;
        letter-spacing: 3px;
        width: 82px;
        margin-top: 0!important;
    }
    .comment-form-rating a{
        color: var(--uicore-headline-color);
    }
    ';
}

// Share product
if($json_settings['woos_share'] === 'true'){
    $css .= '
    .uicore-share-product{
        margin-top: 1.4rem;
    }
    .uicore-share-product a:first-child{
        padding-left: 0 !important;
    }
    ';
}
//Single typography (title, price, description)
$css .= '
.single-product .product_title{
    font-family:' . $this->fam($json_settings['woos_title']['f']) . ';
    font-weight:' . $this->wt($json_settings['woos_title']) . ';
    font-size:' . $json_settings['woos_title']['s']['d'] . 'px;
    line-height:' . $json_settings['woos_title']['h'] . 'em;
    text-transform:' . $json_settings['woos_title']['t'] . ';
    letter-spacing:' . $json_settings['woos_title']['ls'] . 'em;
    color:' . $this->color($json_settings['woos_title']['c']) . ';
    font-style:' . $this->st($json_settings['woos_title']) . ';
}
.single-product .summary p.price,
.single-product .woocommerce-breadcrumb{
    margin-bottom: 1rem;
}
.single-product .summary .price,
.single-product .summary .price ins,
.single-product .summary .price del{
    font-family:' . $this->fam($json_settings['woos_price']['f']) . ';
    font-weight:' . $this->wt($json_settings['woos_price']) . ';
    font-size:' . $json_settings['woos_price']['s']['d'] . 'px!important;
    line-height:' . $json_settings['woos_price']['h'] . 'em;
    text-transform:' . $json_settings['woos_price']['t'] . ';
    letter-spacing:' . $json_settings['woos_price']['ls'] . 'em;
    color:' . $this->color($json_settings['woos_price']['c']) . '!important;
    font-style:' . $this->st($json_settings['woos_price']) . ';
}
.single-product .summary .price del,
.single-product .summary .price ins{
    text-decoration-color:' . $this->color($json_settings['woos_price']['c']) . ';
}
.single-product .woocommerce-product-details__short-description{
    font-family:' . $this->fam($json_settings['woos_excerpt']['f']) . ';
    font-weight:' . $this->wt($json_settings['woos_excerpt']) . ';
    font-size:' . $json_settings['woos_excerpt']['s']['d'] . 'px;
    line-height:' . $json_settings['woos_excerpt']['h'] . 'em;
    text-transform:' . $json_settings['woos_excerpt']['t'] . ';
    letter-spacing:' . $json_settings['woos_excerpt']['ls'] . 'em;
    color:' . $this->color($json_settings['woos_excerpt']['c']) . ';
    font-style:' . $this->st($json_settings['woos_excerpt']) . ';
}
@media (max-width: ' . $br_points['lg'] . 'px) {
    .single-product .product_title{
        font-size:' . $json_settings['woos_title']['s']['t'] . 'px;
    }
    .single-product .summary .price{
        font-size:' . $json_settings['woos_price']['s']['t'] . 'px!important;
    }
    .single-product .woocommerce-product-details__short-description{
        font-size:' . $json_settings['woos_excerpt']['s']['t'] . 'px;
    }
}
@media (max-width: ' . $br_points['md'] . 'px) {
    .single-product .product_title{
        font-size:' . $json_settings['woos_title']['s']['m'] . 'px;
    }
    .single-product .summary .price{
        font-size:' . $json_settings['woos_price']['s']['m'] . 'px!important;
    }
    .single-product .woocommerce-product-details__short-description{
        font-size:' . $json_settings['woos_excerpt']['s']['m'] . 'px;
    }
}
';
if($json_settings['woos_tabs_position'] === 'below_gallery'){
    $css .= '
    @media screen and (max-width: ' . $br_points['md'] . 'px) {
        .uicore-summary-wrapp {
            flex-direction: column-reverse!important;
        }
    }
    ';
}
//horizontal tabs besides gallery
if($json_settings['woos_tabs_style'] == '' && $json_settings['woos_tabs_position'] != 'below_gallery'){
    $css .= '
    .woocommerce div.product .woocommerce-tabs ul.tabs{
        padding-top: 50px;
    }
    ';
}

if($json_settings['woos_tabs_style'] == '' && $json_settings['woos_tabs_position'] != ''){
    $css .= '
    .woocommerce div.product .woocommerce-tabs ul.tabs{
        width: auto;
        left: 0;
        right: 0;
        margin-inline-start: 0 !important;
        margin-inline-end: 0 !important;
        border-bottom: none;
        justify-content: flex-start;
    }
    ';
}












//Archive styles
$css .= '
    li.product a > img,
    li.product .uicore-zoom-wrapper{
        border-radius: '.$json_settings['woo_img_radius'].'px;
    }
    .woocommerce ul.products li.product .woocommerce-loop-product__title{
        font-family:' . $this->fam($json_settings['woo_title']['f']) . ';
        font-weight:' . $this->wt($json_settings['woo_title']) . ';
        line-height:' . $json_settings['woo_title']['h'] . 'em;
        text-transform:' . $json_settings['woo_title']['t'] . ';
        letter-spacing:' . $json_settings['woo_title']['ls'] . 'em;
        color:' . $this->color($json_settings['woo_title']['c']) . ';
        font-style:' . $this->st($json_settings['woo_title']) . ';
    }
    .woocommerce ul.products li.product span.price{
        font-family:' . $this->fam($json_settings['woo_price']['f']) . ';
        font-weight:' . $this->wt($json_settings['woo_price']) . ';
        line-height:' . $json_settings['woo_price']['h'] . 'em;
        text-transform:' . $json_settings['woo_price']['t'] . ';
        letter-spacing:' . $json_settings['woo_price']['ls'] . 'em;
        color:' . $this->color($json_settings['woo_price']['c']) . '!important;
        font-style:' . $this->st($json_settings['woo_price']) . ';
    }

';

if($json_settings['woo_align_center'] === 'true'){
    $css .= '
    .woocommerce ul.products li.product > *:not(.woocommerce-LoopProduct-link),
    .woocommerce ul.products li.product .uicore-reveal-wrapper,
    .woocommerce ul.products li.product .star-rating,
    .woocommerce ul.products li.product .woocommerce-loop-product__title,
    .woocommerce ul.products li.product span.price{
        text-align: center;
        margin-left: auto;
        margin-right: auto;
        display: block;
    }
    ';
}

//Archive typography (title, price)
$css .= '

.woocommerce ul.products li.product .woocommerce-loop-product__title{
    font-size:' .  $json_settings['woo_title']['s']['d'] . 'px;
    padding-bottom: .3em
}
.woocommerce ul.products li.product  span.price{
    font-size:' . $json_settings['woo_price']['s']['d'] . 'px!important;
}

@media (max-width: ' . $br_points['lg'] .'px) {
    .woocommerce ul.products li.product .woocommerce-loop-product__title{
        font-size:' . $json_settings['woo_title']['s']['t']. 'px;
    }
    .woocommerce ul.products li.product span.price{
        font-size:' . $json_settings['woo_price']['s']['t'] . 'px;
    }
}


@media (max-width: ' .  $br_points['md'] . 'px) {
    .woocommerce ul.products li.product .woocommerce-loop-product__title{
        font-size:' . $json_settings['woo_title']['s']['m'] . 'px;
    }
    .woocommerce ul.products li.product span.price{
        font-size:' . $json_settings['woo_price']['s']['m'] . 'px;
    }
}
';

//items styles
if($json_settings['woo_item_style'] === 'boxed'){
    $css .= '
    .woocommerce ul.products li.product{
        border-radius:'.$outer_items_radius.'px;
        border: 1px solid #eaeaea;
        padding: 12px;
    }
    ';
    //if btn style is hover we need to add a border to the button
    if($json_settings['woo_add_to_cart_style'] === 'btn_hover'){
        $css .= '
        .woocommerce ul.products li.product a.button{
            max-width: calc(100% - 44px);
            left: 22px;
        }
        ';
    }
    if($json_settings['woo_hover_effect'] === 'transform'){
        $css .= '
        ul.products li.product:hover{
            box-shadow: 0 14px 20px -16px rgba(0,0,0,0.1);
        }
        ';
    }
}else if($json_settings['woo_item_style'] === 'shadow' && $json_settings['woo_hover_effect'] === 'zoom'){
    $css .= '
    .woocommerce ul.products li.product .uicore-zoom-wrapper{
        box-shadow: 0 7px 20px 0 rgb(0 0 0 / 6%);
        transition: box-shadow .3s cubic-bezier(.23,1,.32,1);
    }
    ';
}
if($json_settings['woo_item_style'] === 'shadow'){
    $css .= '
    ul.products li.product:before{
        content: "";
        border-radius:'.$outer_items_radius.'px;
        position: absolute;
        top: -15px;
        left: -15px;
        right: -15px;
        bottom: -15px;
        box-shadow: 0 0 0 rgba(0, 0, 0, .15);
        transform: scale(.92);
        opacity: 0;
        transition: box-shadow .5s, transform .5s, opacity .5s;
        transition-timing-function: cubic-bezier(0.19, 0.84, 0.27, 1);
        pointer-events: none;
    }
    ul.products li.product:hover:before{
        box-shadow: 0 6px 20px -7px rgba(0, 0, 0, .15);
        transform: scale(1);
        opacity: 1;
    }


    .woocommerce ul.products li.product a img{
        box-shadow: 0 7px 20px 0 rgb(0 0 0 / 6%);
        transition: box-shadow .3s cubic-bezier(0.19, 0.84, 0.27, 1);
    }
    .woocommerce ul.products li.product:hover a img{
        box-shadow: 0 0 0 -7px rgba(0, 0, 0, .00);
    }

    ';
}

//hover effect
if($json_settings['woo_hover_effect'] === 'change_image'){
    $css .= '
    .woocommerce ul.products .uicore-zoom-wrapper{
        overflow: hidden;
        position: relative;
    }
    li.product .uicore-hover-image {
        transition: transform 0.3s cubic-bezier(.2,.75,.5,1),opacity 0.4s cubic-bezier(.2,.75,.5,1),box-shadow .3s cubic-bezier(.19,.84,.27,1)!important;
        position: absolute;
        top: 0;
        opacity: 0;
        transform: scale(1);
    }

    li.product:hover .uicore-hover-image {
        opacity: 1;
        transform: scale(1.1);
    }
    ';
}
if($json_settings['woo_hover_effect'] === 'zoom'){
    $css .= '
        .woocommerce ul.products li.product a .uicore-zoom-wrapper img {
            transform: scale(1);
            background-position: 50%;
            transition-duration: 0.8s;
            transition-property: transform, box-shadow;
            transition-timing-function: cubic-bezier(0.075, 0.82, 0.165, 1);
            -webkit-transform: scale(1);
            -moz-transform: scale(1);
            -ms-transform: scale(1);
            -o-transform: scale(1);
        }

        .woocommerce ul.products li.product a:hover img {
            transform: scale(1.2);
            -webkit-transform: scale(1.2);
            -moz-transform: scale(1.2);
            -ms-transform: scale(1.2);
            -o-transform: scale(1.2);
        }

        li.product .uicore-zoom-wrapper {
            overflow: hidden;
            height: 0;
            padding-bottom: 100%;
        }
    ';
}
if($json_settings['woo_hover_effect'] === 'transform' && $json_settings['woo_item_style'] === 'zoom'){
    $css .= '
         ul.products li.product a > *:not(.woocommerce-loop-product__title) {
            transition: all 0.3s cubic-bezier(0.19, 0.84, 0.27, 1);
            box-shadow: 0 0 0 -10px rgba(0,0,0,0.0);
        }
        ul.products li.product:hover a > *:not(.woocommerce-loop-product__title):not(.star-rating) {
            transform: translateY(-10px);
        }
        .woocommerce ul.products li.product:hover a img {
            box-shadow: 0 14px 20px -12px rgba(0,0,0,0.07);
        }
    ';
}
if($json_settings['woo_hover_effect'] === 'transform'){
    $css .= '
    ul.products li.product{
        transition: transform .3s cubic-bezier(0.19, 0.84, 0.27, 1), box-shadow .3s cubic-bezier(0.19, 0.84, 0.27, 1);
        box-shadow: 0 0 0 -10px rgba(0,0,0,0.0);
    }
    ul.products li.product:hover{
       transform: translateY(-10px);
    }
    ';
}


//add to cart button
if($json_settings['woo_add_to_cart_style'] === 'reveal' || $json_settings['woo_add_to_cart_style'] === 'link'){
    $css .= '
    .woocommerce ul.products li.product .button.product_type_simple,
    .woocommerce ul.products li.product .button.product_type_variable,
    .woocommerce ul.products li.product .button.product_type_grouped,
    .woocommerce ul.products li.product .button.product_type_external {
        color: var(--uicore-primary-color)!important;
    }

    .woocommerce ul.products li.product .button.product_type_simple:hover,
    .woocommerce ul.products li.product .button.product_type_variable:hover,
    .woocommerce ul.products li.product .button.product_type_grouped:hover,
    .woocommerce ul.products li.product .button.product_type_external:hover {
        color: var(--uicore-secondary-color);
    }

    .woocommerce ul.products li.product .added_to_cart,
    .woocommerce ul.products li.product .button.product_type_external,
    .woocommerce ul.products li.product .button.product_type_grouped,
    .woocommerce ul.products li.product .button.product_type_simple,
    .woocommerce ul.products li.product .button.product_type_variable {
        border: none;
        padding: 0;
        font-size: 0.88em;
        line-height: 30px;
        margin-top: 0;
    }
    .woocommerce ul.products li.product .button {
        background-color: transparent !important;
        border: none !important;
    }
    .woocommerce ul.products li.product .button.product_type_external:hover,
    .woocommerce ul.products li.product .button.product_type_grouped:hover,
    .woocommerce ul.products li.product .button.product_type_simple:hover,
    .woocommerce ul.products li.product .button.product_type_variable:hover {
        background-color: transparent;
    }
    .woocommerce .uicore-reveal a.button,
    .woocommerce .uicore-reveal button {
        background-color: transparent !important;
    }
    .woocommerce .uicore-reveal a.button:hover,
    .woocommerce .uicore-reveal button:hover {
        background-color: transparent !important;
    }

    ';
    if($json_settings['woo_add_to_cart_style'] === 'reveal'){
        $css .= '
            .uicore-reveal .price {
                line-height: 30px !important;
            }
            .woocommerce .uicore-reveal-wrapper {
                overflow: hidden;
                height: 30px;
            }
            .woocommerce .uicore-reveal {
                padding-right: 5px;
                height: 60px;
                line-height: 30px;
                transition: transform 0.3s ease;
            }
            .product:hover > div > .uicore-reveal {
                transform: translate3d(0, -30px, 0);
            }
            .product:hover > div > .uicore-reveal a {
                bottom: 0 !important;
            }
            .woocommerce ul.products li.product .uicore-reveal .button {
                margin-top: 0;
                position: absolute;
            }
            .woocommerce ul.products li.product .uicore-reveal .price {
                margin-bottom: 0;
                font-size: 14px;
            }
            .woocommerce ul.products li.product .uicore-reveal .price + a {
                position: relative !important;
            }

    ';
    }
}elseif($json_settings['woo_add_to_cart_style'] === 'btn' || $json_settings['woo_add_to_cart_style'] === 'btn_hover'){
    $css .= '
        .woocommerce ul.products li.product .button{
            width: 100%;
            text-align: center;
            padding:.618em 1em;
            border-radius: '.$inner_items_radius.'px;

        }
    ';
    if( $json_settings['woo_add_to_cart_style'] === 'btn_hover' ){
        $css .= '
        .woocommerce ul.products li.product .button{
            position: absolute;
            transform: translate3d(0, -100%, 0);
            max-width: calc(100% - 20px);
            left: 10px;
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.19, 0.84, 0.27, 1);
            margin-top:0;
        }
        .woocommerce ul.products li.product:hover .button{
            opacity: 1;
            transform: translate3d(0,calc(-100% - 10px),0);
        }
        ';
    }else{
        $css .= '
        .woocommerce ul.products li.product .button{
            margin-top: 10px;
        }
        ';
    }
}

if($json_settings['woo_rating'] === 'true'){
    $css .= '
    ul.products  li.product .star-rating{
        margin-bottom: -7px;
        margin-top: 16px!important;
    }
    ';
}
if($json_settings['woo_swatches'] === 'true'){
    $css .= '
    li.product .uicore-swatches-wrp{
        --uicore-swatch-size: 24px;
        gap: 7px;
        margin-top:8px;
    }
    ';
}
if($json_settings['woo_quick_desc'] === 'true'){
    $css .= '
    .woocommerce ul.products li.product .woocommerce-product-details__short-description p {
        margin-bottom:0;
    }
    .woocommerce ul.products li.product .woocommerce-product-details__short-description{
        margin-top: 0.6em;
        font-family:' . $this->fam($json_settings['woo_description']['f']) . ';
        font-weight:' . $this->wt($json_settings['woo_description']) . ';
        line-height:' . $json_settings['woo_description']['h'] . 'em;
        text-transform:' . $json_settings['woo_description']['t'] . ';
        letter-spacing:' . $json_settings['woo_description']['ls'] . 'em;
        color:' . $this->color($json_settings['woo_description']['c']) . ';
        font-style:' . $this->st($json_settings['woo_description']) . ';
        font-size:' . $json_settings['woo_description']['s']['d'] . 'px;
    }

    @media (max-width: ' . $br_points['lg'] .'px) {
        .woocommerce ul.products li.product  .woocommerce-product-details__short-description{
            font-size:' . $json_settings['woo_description']['s']['t'] . 'px;
        }
    }
    @media (max-width: ' .  $br_points['md'] . 'px) {
        .woocommerce ul.products li.product  .woocommerce-product-details__short-description{
            font-size:' . $json_settings['woo_description']['s']['m'] . 'px;
        }
    }
    ';
}

//sidebar to top
if($json_settings['woocommerce_sidebar'] === 'top'){
    $css .= '
    .uicore-woo{
        flex-direction: column-reverse;
    }
    .uicore-woo .uicore-sidebar-content{
        display: flex;
        gap:30px;
        transition:transform 0.4s ease;
    }
    .uicore-woo .uicore-sidebar-content .uicore-widget{
        max-width: 25%;
        width: 100%;
        min-width: 15%;
    }
    .uicore-woo .uicore-sidebar{
        width: 100%;
        transition: all .05s ease-out;
    }
    .uicore-sidebar.sidebar-hidden {
        width: 100%;
        padding: 0 !important;
        transform: none;
        pointer-events: none;
    }
    .uicore-sidebar.sidebar-hidden .uicore-sidebar-content{
        transform: translateY(100px);
    }

    ';
}

//hide dinamic add to cart
if($json_settings['woos_ajax_add_to_cart'] === 'true'){
    $css .= '.single_add_to_cart_button:not(.uicore-main-add-to-cart) {
        display: none !important;
    }';
}

//animations
$css .= $this->grid_animation('shop');