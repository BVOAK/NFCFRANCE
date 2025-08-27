<?php

defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS
if ( $json_settings['header_wide'] === 'true' && $json_settings['header_pill'] != 'compact') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        body {
            --uicore-header--wide-spacing:70px;
        }
        .uicore-boxed{
            --uicore-header--wide-spacing:50px;
        }
      }
    ';
}
//generic for all pill headers
if ( $json_settings['header_pill'] != 'false') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar{
            position:absolute;
            width:100%;
            border:10px solid transparent;
            transition: all .3s ease;
        }
        .uicore-navbar .uicore-header-wrapper{
            margin-top: '.$json_settings['header_pill_top_spacing'].'px;
        }
        .uicore-navbar.uicore-sticky{
            position:fixed;
        }
        .uicore-mobile-nav-show #wrapper-navbar{
            border:0 solid transparent;
        }
    }';
}else{
    //disable position absolute if is transparent enabled
    if( $json_settings['header_transparent'] === 'true') {
        $css .= '
        .uicore-navbar.uicore-transparent .uicore-header-wrapper{
            position: absolute;
            width: 100%;
        }';
    }
}

if ( $json_settings['header_pill'] === 'true' || $json_settings['header_pill'] === 'compact') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        #wrapper-navbar .uicore-header-wrapper,
        #wrapper-navbar .uicore-header-wrapper:before {
            border-radius: '.$json_settings['header_pill_radius'].'px
        }
    }
    ';

    if ( $json_settings['header_wide'] === 'true' && $json_settings['header_pill'] != 'compact') {
        $offset_val = $json_settings['gen_siteborder'] == 'true' ? ((float)$json_settings['gen_siteborder_w'] * 2 + 40) : 40;
        $css .= '
        @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
            #wrapper-navbar{
                margin: 20px;
                max-width: calc(var(--uicore-boxed-width) - '.$offset_val.'px);
                --uicore-header--wide-spacing:'.$json_settings['header_padding'].'px;
            }
        }
        ';
    }else{
        $offset_val = $json_settings['gen_siteborder'] == 'true' ? ((float)$json_settings['gen_siteborder_w'] * 2 + 20) : 20;
        $css .= '
        @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
            #wrapper-navbar{
                margin: 10px max(0px, calc(calc(calc(min(var(--uicore-boxed-width), 100vw) - var(--ui-container-size)) - '.$offset_val.'px) / 2));
                max-width:calc(var(--ui-container-size) + 20px);
                --uicore-header--wide-spacing:'.$json_settings['header_padding'].'px;
            }
            #wrapper-navbar.uicore .uicore-header-wrapper>.uicore.uicore-container{
                width:100%!important;
                max-width:100%!important;
                padding-left: '.$json_settings['header_padding'].'px!important;
                padding-right:'.$json_settings['header_padding'].'px!important;
            }
        }
        ';
    }

    if( $json_settings['header_transparent'] != 'true' && $json_settings['pagetitle']) {
        $css .= '#wrapper-navbar ~ #content header.uicore{
            padding-top:' . ( (float)$json_settings['header_logo_h'] + ( ((float)$json_settings['header_padding'] * 1.5) )) . 'px;
        }';
    }
}
if ( $json_settings['header_pill'] === 'compact') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar{
            width:auto!important;
            left: 50%;
            transform: translate3d(-50%, 0, 0);
            margin-left: 0!important;
        }
        #uicore-site-header-cart,
        .uicore-navbar a.uicore-btn{
            white-space: nowrap;
        }
    }';
}

if ( $json_settings['header_pill'] === 'menu' || $json_settings['header_pill'] === 'logo-menu') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        #wrapper-navbar .uicore-menu {
            border-radius: '.$json_settings['header_pill_radius'].'px;
            align-self: center;
            padding:0 calc(var(--uicore-header--items-gap) / '.((float)$json_settings['header_pill_radius'] > 12 ? "1.2" : "2").');
        }
        .uicore-nav-menu .uicore-nav .uicore-menu>.menu-item>a{
            line-height:clamp(36px,'.(float)$json_settings['button_typography_typography']['s']['d']* 2.8.'px,66px);
        }
        .uicore-menu li > a:before{
            z-index: 0!important;
        }
        body #wrapper-navbar .uicore-nav ul.uicore-menu li:last-child:not(.menu-item-has-children) a {
            padding-right: var(--uicore-header--menu-spaceing)!important;
        }

    }';
    if ($json_settings['header_shadow'] === 'true') {
        $css .= '
        #wrapper-navbar .uicore-menu {
            box-shadow:0px 3px 10px 0 rgb(0 0 0 / 3%), -2px 3px 90px -20px rgb(0 0 0 / 26%);
        }';
    }
}
if (  $json_settings['header_pill'] === 'logo-menu') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-nav-menu .uicore-logo-pill a{
            height:clamp(36px,'.(float)$json_settings['button_typography_typography']['s']['d']* 2.8.'px,66px);
            display: flex;
            align-items: center;
        }
        .uicore-nav-menu .uicore-logo-pill img{
            display:block;
            margin-right:calc(var(--uicore-header--items-gap) / 2);
            max-height:clamp(26px,calc('.(float)$json_settings['button_typography_typography']['s']['d']* 1.7.'px - calc(var(--uicore-header--items-gap) / 2)),56px);
        }

    }';
}

//generic for all pill headers MOBILE
if ( $json_settings['mobile_pill'] != 'false') {
    $css .= '
    @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
        #wrapper-navbar:not(.uicore-sticky){
            position:absolute;
        }
        #wrapper-navbar{
            width:100%;
            border:10px solid transparent;
            transition: all .3s ease
        }
        .uicore-mobile-nav-show #wrapper-navbar{
            border:0 solid transparent;
        }
        .uicore-navbar.uicore-transparent .uicore-header-wrapper{
            position: absolute;
            width: 100%;
        }
    }';

    if($json_settings['mobile_pill'] === 'true'){
        $css .= '
        @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
            #wrapper-navbar .uicore-header-wrapper,
            #wrapper-navbar .uicore-header-wrapper:before {
                border-radius: '.$json_settings['mobile_pill_radius'].'px
            }
        }';
    }
}else{
    //disable position absolute if is transparent enabled
    if( $json_settings['header_transparent'] === 'true') {
        $css .= '
        @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
            .uicore-navbar.uicore-transparent .uicore-header-wrapper{
                position: absolute;
                width: 100%;
            }
        }';
    }
}




//classic center
if ( $json_settings['header_layout'] === 'classic_center') {
    $css .= '
    .uicore-branding{
        padding-right:0px!important;
    }

    .uicore-h-classic .uicore-nav-menu{
        position:absolute;
        left:var(--uicore-header--wide-spacing,10px);
    }
    .uicore-h-classic .uicore-socials{
        margin: 0 -10px;
    }
    
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-h-classic .uicore.uicore-extra,
        .uicore-navbar .uicore-ham.uicore-toggle{
            position:absolute;
            right:var(--uicore-header--wide-spacing,10px);
        }
        .uicore-nav-menu .uicore-nav .uicore-menu > .menu-item:first-child > a {
            padding-left:0!important;
        }
        ';

        if($json_settings['menu_interaction'] === 'button') {
            $css .= '
            .uicore-nav-menu .uicore-nav .uicore-menu > .menu-item:first-child > a:before {
                left: calc(10px - var(--uicore-header--menu-spaceing));
            }';
        }
        $css .= '
		.uicore-h-classic nav.uicore{
	        position:relative;
	        justify-content: center;
	    }
    }
    ';
}

//center creative
if ( $json_settings['header_layout'] === 'center_creative') {
    $css .= '
    div[class^=\'ui-header-row\'], div[class*=\' ui-header-row\']{
        display:flex;
        justify-content: center;
        position:relative;
    }
    .uicore-h-classic .uicore-socials{
        margin: 0 -10px;
    }
    .ui-header-left,
    .ui-header-right{
        position:absolute;
        top: 0;
        bottom: 0;
        align-items: center;
        gap:var(--uicore-header--items-gap);
        left:0;
        display:none;
    }
    .ui-header-right{
        left:auto;
        right:0
    }
    .uicore-navbar nav.uicore ul.sub-menu{
        position:fixed;
    }
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-h-classic nav.uicore{
            position:relative;
            justify-content: center;
        }
        .ui-header-left,
        .ui-header-right{
            display:flex
        }
        .uicore-h-classic .uicore-header-wrapper nav.uicore{
            display:block;
        }
        .uicore-h-classic .uicore.uicore-extra,
        .uicore-navbar .uicore-ham.uicore-toggle{
            position:absolute;
            right:var(--uicore-header--wide-spacing,10px);
        }
        .uicore-navbar .uicore-menu-container{
            --uicore-header--menu-typo-h: '. (($json_settings['header_2_padding'] * 2) + $json_settings['menu_typo']['s'] ).'px;
        }
        .uicore-scrolled .uicore-header-wrapper {
            top: calc(var(--uicore-header--menu-typo-h,0) * -1);
        }
        .uicore-navbar .uicore-header-wrapper {
            transition: transform .3s ease,top .2s ease-in, opacity .3s ease;
        }
    }
    ';
}

if ($json_settings['header_layout'] === 'classic' || $json_settings['header_layout'] === 'classic_center') {
    $css .= '.uicore-transparent ~ #content header.uicore{
        padding-top:' . ( (float)$json_settings['header_logo_h'] + ( ((float)$json_settings['header_padding'] * 1.5) )) . 'px;
    }';
}

//Shadow
if ($json_settings['header_shadow'] === 'true' && $json_settings['header_pill'] != 'menu' && $json_settings['header_pill'] != 'logo-menu') {
    $css .= '
    #wrapper-navbar .uicore-header-wrapper:before {
        box-shadow: -2px 3px 90px -20px rgb(0 0 0 / 25%);
    }';
}

// Remove logo right-padding in centered headers
if ($json_settings['header_layout'] === 'classic_center' || $json_settings['header_layout'] === 'center_creative') {
    $css .= '.uicore-branding{
        padding-right:0px!important;
    }';
}

// Desktop menu align
if ($json_settings['header_layout'] === 'classic' || $json_settings['header_layout'] === 'classic_center' || strpos($json_settings['header_layout'], 'ham') !== false) {
    $css .= '.uicore-transparent ~ #content header.uicore{
        padding-top:' . ( (float)$json_settings['header_logo_h'] + ( ((float)$json_settings['header_padding'] * 1.5) )) . 'px;
    }';

    if($json_settings['menu_position'] === 'left' || $json_settings['header_pill'] === 'logo-menu'){
        $css .= '
        .uicore-navbar nav .uicore-nav {
            display: flex;
            justify-content: flex-start;
        }
        .uicore-navbar nav .uicore-nav .uicore-socials {
            display: flex;
          }
        ';
    }
    if($json_settings['menu_position'] === 'center' && $json_settings['header_pill'] != 'logo-menu'){
        $css .= '
        .uicore-navbar nav .uicore-nav {
            display: flex;
            justify-content: center;
        }
        ';
    }
}

// Mobile menu align
if($json_settings['mmenu_center'] === 'center'){
    $css .= '
    .uicore-navigation-wrapper {
        text-align: center;
    }
    ';
}
if($json_settings['mmenu_center'] === 'right'){
    $css .= '
    .uicore-navigation-wrapper {
        text-align: right;
    }
    .uicore-navigation-wrapper ul .menu-item-has-children > a {
        padding-right: 35px !important;
    }
	.uicore-navigation-wrapper .uicore-menu-container ul .menu-item-has-children>a:after {
		left: 15px;
		right: auto;
	}
    ';
}


if ($json_settings['header_bg']['blur'] === 'true') {
    $css .= '.uicore-header-wrapper:before {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }';
}
$css_selector_border = '.uicore-header-wrapper';
if($json_settings['header_pill'] === 'logo-menu'){
    $css_selector_border = '.uicore-menu';
}
if ($json_settings['header_transparent_border'] == 'true' && $json_settings['header_layout'] == 'classic') {
    $css .= '.uicore-navbar '. $css_selector_border .'
    {
        box-shadow:0 0px 0 1px transparent;
    }';
}

//for case when you want border only for nontransparent header
if ($json_settings['header_transparent_border'] == 'false' && $json_settings['header_layout'] == 'classic') {
    $css .= '
    .uicore-navbar.uicore-transparent:not(.uicore-scrolled) '.$css_selector_border.'{
        box-shadow:0 0px 0 1px  transparent;
    }';
}

if ($json_settings['header_border'] == 'true') {

    //FOR CLASSIC MENU ONLY
    if ($json_settings['header_layout'] == 'classic') {
        $css .= '
        .uicore-navbar '.$css_selector_border.'{
            box-shadow:0 0px 0 1px ' . $this->color($json_settings['header_borderc']) . ';
        }';
    }
}

if ($json_settings['header_transparent_border'] == 'true') {
    $css .=
        '
    .uicore-transparent:not(.uicore-scrolled) '.$css_selector_border.' {
        box-shadow:0 0px 0 1px ' . $this->color( $json_settings['header_transparent_borderc']) .
        ';
    } ';
}



if ($json_settings['header_transparent_border'] == 'true' && $json_settings['header_layout'] == 'classic') {
    $css .= '.uicore-transparent ~ #content header.uicore{
        padding-top:' . ( intval($json_settings['header_logo_h']) + ( (intval($json_settings['header_padding']) * 2) )) . 'px;
    }';
}

$no_padding_right = ((
    $json_settings['menu_position'] === 'right' &&
    ($json_settings['header_layout'] === 'classic' || $json_settings['header_layout'] === 'classic_center') &&
    $json_settings['header_cta'] === 'false' &&
    $json_settings['header_search'] === 'false' &&
    $json_settings['header_icons'] === 'false' &&
    $json_settings['woo'] === 'false'
) && ($json_settings['header_pill'] != 'logo-menu' || $json_settings['header_pill'] != 'menu'));

//remove menu item last padding for right
if ( $no_padding_right ) {
    $css .=
        '
    #wrapper-navbar .uicore-nav ul.uicore-menu li:last-child:not(.menu-item-has-children) a {
        padding-right:0!important;
    } ';
}

//build the class for background for default and pill headers
$selector = '.uicore-navbar .uicore-header-wrapper:before';
if($json_settings['header_pill'] === 'menu' || $json_settings['header_pill'] === 'logo-menu'){
    $selector = '.uicore-navbar .uicore-menu';
}

$css .= $this->background($json_settings['header_bg'] , '.uicore-mobile-menu-wrapper:before, .uicore-wrapper.uicore-search.uicore-section, '.$selector);
$css .= $this->background($json_settings['mobile_menu_bg'] , '.uicore-navigation-wrapper', 'max-width: '.$json_settings['mobile_breakpoint'].'px');
 //Hmabourger menu
 if (strpos($json_settings['header_layout'], 'ham') !== false) {
    $menu_item_lh = 1;
    $css .= $this->background($json_settings['menu_bg'] , '.uicore-navigation-wrapper', 'min-width: '.$json_settings['mobile_breakpoint'].'px');
    $css.= '
    .uicore-navbar:not(.uicore-transparent) .uicore-ham .bar,
    .uicore-transparent-color .uicore-ham .bar,
    .uicore-transparent.uicore-scrolled .uicore-ham .bar{
        background-color: '. $this->color($json_settings['header_ham_color']['m']) .'
    }
    .uicore-navbar:not(.uicore-transparent) .uicore-ham:hover .bar,
    .uicore-transparent-color .uicore-ham:hover .bar,
    .uicore-transparent.uicore-scrolled .uicore-ham:hover .bar{
        background-color: '. $this->color($json_settings['header_ham_color']['h']) .'
    }
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        body {
            --uicore-header--wide-spacing:70px;
        }
    }
    ';
}else{
    $menu_item_lh = (intval( $json_settings['header_logo_h'])  + ( intval($json_settings['header_padding']) * 2 ) ).'px';
    if($json_settings['header_layout'] === 'left'){
        $menu_item_lh = (intval($json_settings['menu_spacing'])  + intval($json_settings['menu_typo']['s']) ).'px';
    }
    $css.= '
    .uicore-cart-icon.uicore_hide_desktop #uicore-site-header-cart {
        color: var(--uicore-header--menu-typo-c);
    }
    @media only screen and (min-width: 1025px) {
        .uicore-navbar .uicore-extra {
            margin-left: 25px;
        }
    }
    ';
}



if($json_settings['header_pill'] != 'menu' && $json_settings['header_pill'] != 'logo-menu' ){
    $css .= '
    .uicore-transparent:not(.uicore-scrolled) {
        --uicore-header--menu-typo-c:' . $this->color($json_settings['header_transparent_color']['m']) . ';
        --uicore-header--menu-typo-ch:' . $this->color($json_settings['header_transparent_color']['h']) . ';
    }
    ';
}else{
    $css .= '
    .uicore-transparent:not(.uicore-scrolled) .uicore-extra{
        --uicore-header--menu-typo-c:' . $this->color($json_settings['header_transparent_color']['m']) . ';
        --uicore-header--menu-typo-ch:' . $this->color($json_settings['header_transparent_color']['h']) . ';
    }';
}
$css .='
body .uicore-transparent-color nav,
.uicore-navbar {
    --uicore-header--logo-h:' . $json_settings['header_logo_h'] . 'px;
    --uicore-header--logo-padding:' . $json_settings['header_padding'] . 'px;
    --uicore-header--menu-spaceing:' . (intval($json_settings['menu_spacing']) / 2 ).'px;

    --uicore-header--menu-typo-f:' . $this->fam($json_settings['menu_typo']['f']) . ';
    --uicore-header--menu-typo-w:' . $this->wt($json_settings['menu_typo']) . ';
    --uicore-header--menu-typo-h:' . $menu_item_lh .';
    --uicore-header--menu-typo-ls:' . $json_settings['menu_typo']['ls'] . 'em;
    --uicore-header--menu-typo-t:' . $json_settings['menu_typo']['t'] . ';
    --uicore-header--menu-typo-st:' . $this->st($json_settings['menu_typo']) . ';
    --uicore-header--menu-typo-c:' . $this->color($json_settings['menu_typo']['c']) . ';
    --uicore-header--menu-typo-ch:' . $this->color($json_settings['menu_typo']['ch']) . ';
    --uicore-header--menu-typo-s:' . $json_settings['menu_typo']['s'] . 'px;

    --uicore-header--items-gap:25px;

}';
if($json_settings['animations_submenu'] != 'scale bg'){
    $css .= '
    @media only screen and (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar li ul {
            --uicore-header--menu-typo-f:' . $this->fam($json_settings['submenu_color']['f']) . ';
            --uicore-header--menu-typo-w:' . $this->wt($json_settings['submenu_color']) . ';
            --uicore-header--menu-typo-h:' . $menu_item_lh .';
            --uicore-header--menu-typo-ls:' . $json_settings['submenu_color']['ls'] . 'em;
            --uicore-header--menu-typo-t:' . $json_settings['submenu_color']['t'] . ';
            --uicore-header--menu-typo-st:' . $this->st($json_settings['submenu_color']) . ';
            --uicore-header--menu-typo-c:' . $this->color($json_settings['submenu_color']['c']) . ';
            --uicore-header--menu-typo-ch:' . $this->color($json_settings['submenu_color']['ch']) . ';
            --uicore-header--menu-typo-s:' . $json_settings['submenu_color']['s'] . 'px;
        }
    }
    ';
}


$css .= '
.uicore-ham .bar,
#mini-nav .uicore-ham .bar{
    background-color: var(--uicore-header--menu-typo-c);

}
@media only screen and (min-width: 1025px) {
    .uicore-shrink:not(.uicore-scrolled) {
        --uicore-header--logo-padding:' . $json_settings['header_padding_before_scroll'] . 'px;
        --uicore-header--menu-typo-h:' . (intval( $json_settings['header_logo_h'])  + ( intval($json_settings['header_padding_before_scroll']) * 2 ) ) . 'px;
    }
}

@media (max-width: ' . $br_points['md'] . 'px) {
    .uicore-navbar{
        --uicore-header--logo-h:' . $json_settings['mobile_logo_h'] . 'px;
    }
    #wrapper-navbar nav{
        max-width:95%;
    }
}';

 // CTA on MOBILE
$css .= '@media (max-width: ' . $br_points['md'] . 'px) {
    .uicore-navbar .uicore-btn{
        font-size: ' . $json_settings['mmenu_typo']['s'] . 'px;
        font-weight: ' . $this->wt($json_settings['mmenu_typo']) .';
        font-style: ' . $this->st($json_settings['mmenu_typo']) . ';
        font-family: ' . $this->fam($json_settings['menu_typo']['f']) .';
        letter-spacing: ' . $json_settings['mmenu_typo']['ls'] . 'em;
        text-transform: ' . $json_settings['mmenu_typo']['t'] .';
    }
}
.uicore-menu-left #uicore-page nav div .uicore ul a{
    padding: calc(' . $json_settings['menu_spacing'] . 'px / 2) 0;
}
.uicore-menu-left #uicore-page nav div.uicore-extra .uicore-btn{
    margin: ' . $json_settings['header_padding'] . 'px auto;
}

.uicore-mobile-menu-wrapper-show .uicore-navigation-wrapper{
    color:' . $this->color($json_settings['mmenu_typo']['c']) . ';
}
.uicore-navigation-content{
    height: calc(100% - ' .( intval($json_settings['mobile_logo_h']) + (intval($json_settings['header_padding']) * 2) ) . 'px);
}
@media only screen and (max-width: '.$json_settings['mobile_breakpoint'].'px) {
    .uicore-navbar .uicore-branding {
        margin: ' .($json_settings['mobile_menu_padding']  != '' ? 
            ($json_settings['mobile_menu_padding'].'px') : 
            'calc(var(--uicore-header--logo-padding) * 0.7)') . '  0;
    }
}
';
if ($json_settings['header_sticky'] == 'true' && $json_settings['performance_widgets'] == 'true') {
    $css .=
        '
    .uicore-sidebar .uicore-sticky{
        top: calc(calc(' . $json_settings['header_logo_h'] . 'px + calc(' . $json_settings['header_padding'] . 'px * 2)) + 60px);
    }
    ';
}

if($json_settings['mobile_sticky'] === 'true' && $json_settings['header_sticky'] != 'true'){
    $css .= '
    @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar {
           position: sticky;
            width: 100%;
            top: 0;
        }
    }
    ';
}
if($json_settings['mobile_sticky'] === 'false' && $json_settings['header_sticky'] === 'true'){
    $css .= '
    @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar.uicore-sticky {
           position: relative;
        }
    }
    ';
}

//extras
if($json_settings['header_cta'] === 'true'){
    $css .= '
    @media  (min-width:'.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar .uicore-cta-wrapper a {';
                if($json_settings['header_cta_size'] === 'small'){
                    $css .= 'padding:clamp(8px,.7em,13px) clamp(11px,1.3em,32px);';
                }elseif($json_settings['header_cta_size'] === 'medium'){
                    $css .= 'padding:clamp(12px,.9em,18px) clamp(24px,1.9em,26px)';
                }elseif($json_settings['header_cta_size'] === 'full'){
                    $css .= 'padding:0 clamp(24px,3em,40px);
                    line-height: calc(var(--uicore-header--menu-typo-h) + 2px)!important;
                    border-radius: 0!important;
                    ';

                }else{
                    $css .= 'padding:clamp(24px,1.8em,22px) clamp(36px,3.6em,72px);';
                }
                $css.='
          }
    }
    ';

}
if($json_settings['header_search'] === 'true'){
    $css .= '
    .uicore-wrapper.uicore-search.uicore-section {
        height: 100vh;
        position: fixed;
        right:0;
        left: 0;
        top: 0;
        opacity: 0;
        pointer-events: none;
        transition: all .4s ' . $opacityEase . ';
        justify-content: center;
        align-content: center;
        align-items: center;
        display: flex;
    }
    .uicore-search .search-field {
        font-size: 2em !important;
        background: transparent;
        border: none;
    }
    .uicore-search .uicore-close.uicore-i-close {
        position: absolute;
        right: 0;
        top: 0;
        cursor: pointer;
        font-size: 20px;
        padding: 20px;
    }
    .uicore-search-active {
        overflow: hidden !important;
    }
    .uicore-search-active .uicore-wrapper.uicore-search.uicore-section {
        opacity: 1;
        pointer-events: all;
        z-index: 999;
    }
    .win.uicore-search-active {
        margin-right: 17px;
    }
    .uicore-wrapper .search-field, .uicore-wrapper .search-field::placeholder,
    .uicore-close.uicore-i-close {
        color:' . $this->color($json_settings['menu_typo']['c']) . ';
    }
    ';

}


if($json_settings['mobile_extra_content'] === 'cta') {
    $css .= '
    @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-header-wrapper > nav > div .uicore-cta-wrapper a{
            padding: 8px 14px;
            font-size:13px;
            line-height:16px
        }
    }';
}


//Submenu icon/img/description layout

$css .='
.container-width .uicore-megamenu>.elementor,
.custom-width .uicore-megamenu>.elementor {
    width: 100%;
}
';
$css .='
ul.uicore-menu {
    --uicore-header--menu-effect-bg: ' . $this->color($json_settings['menu_interaction_color']) . ';
}
';


if($json_settings['submenu_trigger'] === 'click' && strpos($json_settings['header_layout'], 'ham') == false){
    $css .= "
    .uicore-navbar nav .menu-item-has-children .sub-menu .menu-item-has-children:hover>.sub-menu,
    .uicore-navbar nav.uicore ul.sub-menu, .uicore-navbar nav.uicore ul.sub-menu:not(.uicore-megamenu){
      display:none;
    }
    ";
}