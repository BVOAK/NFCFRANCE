<?php 
if ($json_settings['header_layout'] === 'left') {

    $css .= '
    @media (min-width: 1025px) {
        .uicore-navbar.uicore-left-menu {
            height:100vh;
            left: 0;
            padding: 0;
            position: fixed;
            top: 0
        }

        .uicore-navbar.uicore-left-menu .uicore-extra,.uicore-navbar.uicore-left-menu .uicore-nav,.uicore-navbar.uicore-left-menu.uicore-section .uicore-container {
            flex-direction: column
        }

        .uicore-navbar.uicore-left-menu .uicore-header-wrapper {
            height: 100%;
            padding: 0 40px!important
        }

        .uicore-navbar.uicore-left-menu .uicore-header-wrapper nav.uicore-container {
            padding: 0
        }

        .uicore-navbar.uicore-left-menu .uicore ul li {
            display: block
        }

        .uicore-navbar.uicore-left-menu .uicore-branding {
            margin: 35px 0
        }

        .uicore-navbar.uicore-left-menu .uicore-extra .uicore-btn {
            margin-left: 0
        }

        .uicore-navbar.uicore-left-menu nav {
            height: 100%
        }

        .uicore-navbar.uicore-left-menu nav>div:last-of-type {
            margin: 35px 0
        }

        .uicore-navbar.uicore-left-menu .uicore-extra {
            flex: 1;
            justify-content: flex-end
        }

        .uicore-navbar.uicore-left-menu .uicore-extra .uicore-socials a:first-child {
            padding-left: 0!important
        }

        .uicore-navbar.uicore-left-menu .uicore-extra .uicore-socials a:last-child {
            padding-right: 0!important
        }

        .uicore-navbar.uicore-left-menu .uicore-cta-wrapper,.uicore-navbar.uicore-left-menu .uicore-socials {
            padding-top: 2em
        }
    }';

    $css .= '
    @media (min-width: '.$br_points['lg'].'px) {
        .uicore-left-menu {
            width: '. $json_settings['header_side_width'] .'px
        }
        #uicore-page {
            padding-left: '. $json_settings['header_side_width'] .'px;
        }

        .uicore-custom-area {
            flex-direction:column;
        }
        .uicore-navbar .uicore-extra .uicore-socials,
        .uicore-custom-area .uicore-hca{
            margin-left:0!important;
        }

    }
    ';
    if($json_settings['header_content_align'] === 'left'){
        $css .= '
        @media (min-width: '.$br_points['lg'].'px) {
            .uicore-left-menu.uicore-section .uicore-container,
            .uicore-left-menu .uicore-extra,
            .uicore-left-menu .uicore-extra .uicore-btn  {
                align-items: normal;
            }
        }
        ';
    }
    if($json_settings['header_content_align'] === 'center'){
        $css .= '
        @media (min-width: '.$br_points['lg'].'px) {
            .uicore-left-menu .uicore-nav-menu {
                width: 100%;
                text-align: center;
            }
        }
        ';
    }


    if ($json_settings['header_border'] == 'true') {
        $css .= '
        .uicore-left-menu {
            border-right: 1px solid ' . $this->color($json_settings['header_borderc']) . ';
        }';
    }
}

if ( $json_settings['header_pill'] === 'menu' || $json_settings['header_pill'] === 'logo-menu') {
        if($json_settings['menu_interaction'] === 'button' || $json_settings['menu_interaction'] === 'magnet button') {
        $css .= '
        @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
            #wrapper-navbar .uicore-menu {
                padding:4px;
            }
            #wrapper-navbar .uicore-menu li > a:before {
                height: 100%;
            }
            #wrapper-navbar .uicore-nav ul.uicore-menu>li:last-child:not(.menu-item-has-children) a:before{
                right:0!important;
            }
            ul.uicore-menu::before{
                z-index: 0!important;
            }
        }
        ';
    }
}

if ( $json_settings['header_layout'] === 'classic_center') {
     $css .= '
    .uicore-navbar nav.uicore ul.uicore-menu > li > ul.sub-menu{
        position:fixed;
    }
    .uicore-scrolled nav.uicore ul.sub-menu {
        top: var(--uicore-header--menu-typo-h,0);
    } 
    ';
}

 if (strpos($json_settings['header_layout'], 'ham') !== false) {
    $css .= '
        @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-is-ham .uicore-mobile-menu-wrapper:not(.uicore-ham-classic) .uicore-navigation-content .uicore-menu .menu-item-has-children>a:after {
            line-height: '. ($json_settings['menu_typo']['h'] * $json_settings['menu_typo']['s']) .  'px;
        }
        .uicore-mobile-menu-wrapper,
        .uicore-mobile-menu-wrapper li ul.sub-menu {
            --uicore-header--menu-typo-f:' . $this->fam($json_settings['menu_typo']['f']) . ';
            --uicore-header--menu-typo-w:' . $this->wt($json_settings['menu_typo']) . ';
            --uicore-header--menu-typo-h:' . $json_settings['menu_typo']['h'] . ';
            --uicore-header--menu-typo-ls:' . $json_settings['menu_typo']['ls'] . 'em;
            --uicore-header--menu-typo-t:' . $json_settings['menu_typo']['t'] . ';
            --uicore-header--menu-typo-st:' . $this->st($json_settings['menu_typo']) . ';
            --uicore-header--menu-typo-c:' . $this->color($json_settings['menu_typo']['c']) . ';
            --uicore-header--menu-typo-ch:' . $this->color($json_settings['menu_typo']['ch']) . ';
            --uicore-header--menu-typo-s:' . $json_settings['menu_typo']['s'] . 'px;
        }
        .uicore-navbar ul.sub-menu{
            transform:none!important
        }
    }
    ';
 }


 if($json_settings['animations_submenu'] != 'scale bg'){
    $css .= '
    .uicore-nav-menu .sub-menu:not(.uicore-megamenu){
        background-color:' . $this->color($json_settings['submenu_bg']) .';
    }
    .uicore-nav-menu .sub-menu:not(.uicore-megamenu) a, .uicore-nav-menu .sub-menu:not(.uicore-megamenu) li,
    .uicore-nav-menu .uicore-simple-megamenu:not(.uicore-megamenu) > .sub-menu > li.menu-item-has-children{
        color:' . $this->color($json_settings['submenu_color']['c']) . '!important;
    }
    .uicore-nav-menu .sub-menu:not(.uicore-megamenu) a:hover, .uicore-nav-menu:not(.uicore-megamenu) .sub-menu li:hover{
        color:' .$this->color($json_settings['submenu_color']['ch']) . '!important;
    }
    ';
}

$css .= '
@media only screen and (max-width: '.$json_settings['mobile_breakpoint'].'px) {
    .uicore-navbar.uicore-mobile-menu-wrapper {
        --uicore-header--menu-typo-f:' . $this->fam($json_settings['mmenu_typo']['f']) . ';
        --uicore-header--menu-typo-w:' . $this->wt($json_settings['mmenu_typo']) . ';
        --uicore-header--menu-typo-h:' . $json_settings['mmenu_typo']['h'] . ';
        --uicore-header--menu-typo-ls:' . $json_settings['mmenu_typo']['ls'] . 'em;
        --uicore-header--menu-typo-t:' . $json_settings['mmenu_typo']['t'] . ';
        --uicore-header--menu-typo-st:' . $this->st($json_settings['mmenu_typo']) . ';
        --uicore-header--menu-typo-c:' . $this->color($json_settings['mmenu_typo']['c']) . ';
        --uicore-header--menu-typo-ch:' . $this->color($json_settings['mmenu_typo']['ch']) . ';
        --uicore-header--menu-typo-s:' . $json_settings['mmenu_typo']['s'] . 'px;
    }
}
';

if ($json_settings['header_sticky'] == 'true') {
    $css .=
        '
        .ui-hide{
            pointer-events: none;
        }
        .ui-hide .uicore-header-wrapper{
            transform:translate3d(0,-35px,0);
            opacity: 0;
            transition: transform .3s cubic-bezier(0.41, 0.61, 0.36, 1.08), opacity .2s ease;
            pointer-events: none;
        }
        .logged-in.admin-bar .uicore-navbar.uicore-sticky {
            top: 31px;
        }
    ';
}


//animations
if($global_animations && strpos($json_settings['header_layout'], 'ham') !== false){
    $css .= '
    @media  (min-width: 1025px) {
        .uicore-navigation-content .uicore-extra > div {
            opacity: 0;
            transform: translate3d(0,4vw,0);
            transition: transform 1s '.$translateEase.', opacity 0.9s '. $opacityEase.';
        }
        .uicore-navigation-content .uicore-extra > div:nth-child(1) {
            transition-delay: 0.3s;
        }
        .uicore-navigation-content .uicore-extra > div:nth-child(2) {
            transition-delay: 0.6s;
        }
        .uicore-navigation-content .uicore-extra > div:nth-child(3) {
            transition-delay: 0.9s;
        }
        .uicore-navigation-content .uicore-extra > div:nth-child(4) {
            transition-delay: 1.2s;
        }
        .uicore-navigation-content .uicore-extra > div:nth-child(5) {
            transition-delay: 1.5s;
        }
        .uicore-menu li a {
            opacity: 0;
        }

        .uicore-menu li.uicore-visible > a {
            animation-name: uicoreFadeInUp, uicoreFadeIn !important;
            animation-timing-function:'.$translateEase.','. $opacityEase.';
            animation-duration: 1s;
            animation-fill-mode: forwards;
        }
    }
    ';
}
if($global_animations && $json_settings['animations_menu'] != 'none'){

    $css .= '
    @media  (min-width: 1025px) {
        ';
        if($json_settings['header_layout'] === 'center_creative'){
            $css .= '
            body:not(.elementor-editor-active) .ui-header-row1 > *,
            body:not(.elementor-editor-active) .ui-header-row2 > *{
            ';
        }else{
            $css .= '
            body:not(.elementor-editor-active) #wrapper-navbar .uicore-extra > *,
            body:not(.elementor-editor-active) .uicore-header-wrapper .uicore-branding,
            body:not(.elementor-editor-active) .uicore-header-wrapper nav .uicore-ham,
            body:not(.elementor-editor-active) .uicore-header-wrapper ul.uicore-menu > .menu-item > a {
            ';
        }
        $css .= '
        animation-delay: '.$json_settings['animations_menu_delay'].'ms;
        ';

    if($json_settings['animations_menu'] === 'fade'){
        $css .= '
            opacity: 0;
            animation-fill-mode: forwards;
            animation-duration: .6s;
            animation-name: uicoreFadeIn;
            animation-play-state: paused;
            animation-timing-function: '.$opacityEase.';
        ';
    }
    if($json_settings['animations_menu'] === 'fade down'){
        $css .= '
            opacity: 0;
            animation-fill-mode: forwards;
            animation-duration: 1s;
            animation-name: uicoreFadeInDown, uicoreFadeIn;
            animation-play-state: paused;
			animation-timing-function: '.$translateEase.','. $opacityEase.';
        ';
    }
    if($json_settings['animations_menu'] === 'fade up'){
        $css .= '
            opacity: 0;
			animation-fill-mode: forwards;
			animation-duration: 1s;
			animation-name: uicoreFadeInUp, uicoreFadeIn;
			animation-play-state: paused;
			animation-timing-function: '.$translateEase.','. $opacityEase.';
        ';
    }
    if( $json_settings['animations_menu_duration'] === 'fast'){
        $css .= '
            animation-duration: .6s;
        ';
    }
    if( $json_settings['animations_menu_duration'] === 'slow'){
        $css .= '
            animation-duration: 2s;
        ';
    }
    $css .= '}
    }';
}

//Sumbenu animations
if($global_animations && $json_settings['animations_submenu'] != 'none'){

    $css .= '
    @media  (min-width: 1025px) {
        .uicore-navbar ul.sub-menu {';

    if($json_settings['animations_submenu'] === 'fade'){
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.1s cubic-bezier(1, 0.4, 0.5, 0.9),transform 0.3s cubic-bezier(0.4, -0.37, 0.03, 1.29);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.5s cubic-bezier(0.68, 0.57, 0.6, 0.92), transform 0.8s cubic-bezier(0.47, 0.4, 0.43, 1);';
        }else{
            $css .= 'transition: opacity 0.3s;';
        }
    }
    if($json_settings['animations_submenu'] === 'fade down' || $json_settings['animations_submenu'] === 'website blur'){
        $css .= '
        transform: translate3d(0,-18px,0);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.2s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.6s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.6s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }else{
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.4s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }
    }
    if($json_settings['animations_submenu'] === 'fade up'){
        $css .= '
        transform: translate3d(0,18px,0);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.2s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.6s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.6s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }else{
            $css .= 'transition: opacity 0.3s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.4s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }
    }
    if($json_settings['animations_submenu'] === 'scale down'){
        $css .= '
        transform-origin: top center;
        transform: scaleY(0);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.2s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.5s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.5s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }else{
            $css .= 'transition: opacity 0.3s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.3s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }
    }
    if($json_settings['animations_submenu'] === 'fade left'){
        $css .= '
        transform: translate3d(-18px,0,0);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.2s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.6s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.6s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }else{
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.4s cubic-bezier(0.47, 0.4, 0.43, 1);';
        }
    }
    if($json_settings['animations_submenu'] === 'rotate'){
        $css .= '
        transform-origin: top center;
        transform: rotateX(-90deg);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.1s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.2s cubic-bezier(0.62, 0.19, 0.2, 1.55);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.6s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }else{
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.4s cubic-bezier(0.62, 0.19, 0.2, 1.55);';
        }
    }
    if($json_settings['animations_submenu'] === 'scale bg'){
        $css .= '
        transform: translate3d(0,10px,0);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.1s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.15s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.5s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.6s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }else{
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.25s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }
    }
    $css .= '}
    }';
}
if($json_settings['animations_submenu'] === 'scale bg'){
    $css .= '
    .uicore-navbar nav.uicore ul.sub-menu{
        box-shadow: none!important;
        padding-top:0;
        margin-top: -15px;
    }
    ';
}elseif($json_settings['animations_submenu'] === 'website blur'){
    //the initial transition is set via js to avoid the blur effect on page load (header-js.php)
    $css .= '
    .uicore-navbar nav.uicore ul.sub-menu, .uicore-navbar nav.uicore ul.sub-menu:not(.uicore-megamenu){
        box-shadow:none!important;
    }
    .uicore-blur-on #content{
        transition:all 0.7s cubic-bezier(0.55, 0.63, 0.37, 1.04)!important;
        filter: blur(30px) saturate(2);
        transform: scale(0.96) translateY(4.6875rem);
        transform-origin: 50% 0;
    }
    ';
}


//Mobile menu animations
$css .= '
@media  (max-width:'.$json_settings['mobile_breakpoint'].'px) {
    ';

if($json_settings['mmenu_animation'] === 'slide on top'){
    $css .= '
       .uicore-navigation-wrapper {
            transform: translate3d(-100%,0,0);
            transition: transform 0.3s cubic-bezier(0.31, 0.87, 0, 0.98);
        }
        .uicore-navigation-wrapper .uicore-toggle {
            opacity: 0;
        }
        .uicore-body-content {
            z-index: 2;
        }
        .uicore-mobile-nav-show .uicore-navigation-wrapper {
				transform: translate3d(0,0,0);
				z-index: 99;
				pointer-events: all;
        }
        .uicore-mobile-nav-show .uicore-navigation-wrapper nav {
            opacity: 1 !important;
            transition: all 0.2s $translateEase 0.4s;
            -webkit-transition: all 0.2s $translateEase 0.4s;
            -moz-transition: all 0.2s $translateEase 0.4s;
            -ms-transition: all 0.2s $translateEase 0.4s;
            -o-transition: all 0.2s $translateEase 0.4s;
        }
        .uicore-mobile-nav-show .uicore-navigation-wrapper .uicore-toggle {
            opacity: 1;
        }

    ';
}
if($json_settings['mmenu_animation'] === 'slide along'){
    $css .= '

       .uicore-navigation-wrapper {
            transform: translate3d(-60%,0,0);
            z-index: 0;
            transition: transform 0.55s cubic-bezier(0.31, 0.87, 0, 0.98);
        }
        .uicore-body-content {
            transition: transform 0.55s cubic-bezier(0.31, 0.87, 0, 0.98);
			z-index: 2;
			box-shadow: -25px 0 38px -28px rgb(0 0 0 / 25%);
        }
        .uicore-mobile-nav-show .uicore-navigation-wrapper nav {
            opacity: 1 !important;
            transition: all 0.2s $translateEase 0.25s;
        }
        .uicore-mobile-nav-show .uicore-body-content{
            transform: translate3d(107vw,0,0);
        }

    ';
}
if($json_settings['mmenu_animation'] === 'expand'){
    $css .='
    .uicore-mobile-menu-wrapper{
        transition: all 0.3s cubic-bezier(0.31, 0.87, 0, 0.98);
        max-height: 0;
    }
    ';
}

$css .= '
.uicore-mobile-nav-show .uicore-navigation-content {
    opacity: 1;
}
.uicore-mobile-nav-show .uicore-extra {
    opacity: 1 !important;
    transition: all 0.2s '.$translateEase.' 0.25s;
}
.uicore-mobile-nav-show .uicore-navigation-wrapper {
    transform: translate3d(0,0,0);
    pointer-events: all;
    opacity: 1;
}
';

$css .= '}';

if($json_settings['header_layout'] === 'ham center' || $json_settings['header_layout'] === 'ham creative') {
	$css .='
		.uicore-is-ham .uicore-navigation-content .uicore-menu .menu-item-has-children>a:after {
			line-height: inherit !important;
		}
	';
}
//mobile menu layouts
if($json_settings['mobile_layout'] === 'center') {
    $css .= '
    @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-header-wrapper > nav{
            display: grid!important;
            grid-template-columns: 1fr auto 1fr;
            gap: 10px;
        }
        .uicore-mobile-head-right {
            justify-self: flex-end;
        }
        .uicore-navbar .uicore-branding{
            padding-right: 0!important;
        }
    }';
}

$css .='
.uicore-menu .sub-menu .ui-has-description > a{
        display: grid!important;
        grid-template-columns: auto 1fr;
        grid-template-rows: auto;
        align-items: center;
  }
  .uicore-menu .sub-menu .ui-has-description > a img,
  .uicore-menu .sub-menu .ui-has-description > a .ui-svg-wrapp{
       grid-area: 1 / 1 / 3 / 2;
        max-height:2.6em
  }
  .uicore-menu .sub-menu .ui-has-description>a .ui-svg-wrapp {
      height: 100%;
      width: 100%;
      position: relative;
      min-height: 38px;
      min-width: 53px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-right: solid 15px transparent;
  }
  .uicore-menu .sub-menu .ui-has-description > a .ui-svg-wrapp:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: var(--ui-radius);
      background-color: currentColor;
    opacity: 0.1;
  }
  .uicore-menu .menu-item-has-children ul .custom-menu-desc{
    margin-top:0;
    max-width: 300px;
    grid-area: 2 / 2 / 2 / 3;
  }
';

if($json_settings['menu_interaction'] === 'underline'){
    $css .= '
    @media only screen and (min-width: ' . $br_points['md'] . 'px) {
        .uicore-menu .ui-menu-item-wrapper {
            position: relative;
        }
        .uicore-menu .ui-menu-item-wrapper:before {
            content: \'\';
            position: absolute;
            z-index: -1;
            bottom: -5px;
            width: 100%;
            height: 2px;
            opacity: .75;
            transform: scale3d(0, 1, 1);
            transform-origin: 100% 50%;
            transition: transform 0.3s;
            transition-timing-function: cubic-bezier(0.2, 1, 0.3, 1);
            background: var(--uicore-header--menu-effect-bg);
        }
        .uicore-menu li.current-menu-item>a .ui-menu-item-wrapper:before {
            transform: scale3d(1, 1, 1);
        }
        .uicore-menu li.menu-item:hover>a .ui-menu-item-wrapper:before {
            transform: scale3d(1, 1, 1);
            transform-origin: 0% 50%;
            transition-timing-function: ease;
        }
        .uicore-nav-menu a.uicore-social-icon:before {
            font-size: 90%;
        }
        .uicore-nav-menu a.uicore-social-icon,
        .uicore-social-icon {
            padding: 0 10px !important;
        }
        .uicore-extra .uicore-custom-area:not(:last-child):after {
            content: "";
            width: 2px;
            height: calc(var(--uicore-header--menu-typo-s) * 1.5);
            background: var(--uicore-header--menu-effect-bg);
            margin-left: 25px;
            align-self: center;
            opacity: .3;
        }
        .uicore-menu li li:not(ui-has-description) > a .ui-menu-item-wrapper:before {
            content:none;
        }
    }
    ';
}elseif($json_settings['menu_interaction'] === 'button' || $json_settings['menu_interaction'] === 'magnet button'){
    $css .= '
    @media only screen and (min-width: ' . $br_points['md'] . 'px) {
        .uicore-menu li > a{
            position:relative;
        }
        .uicore-menu li > a:before{
            content: \'\';
            position: absolute;
            left: 0;
            right: 0;
            top: 51%;
            height: 2.4rem;
            background-color: transparent;
            border-radius: var(--ui-radius);
            z-index: -1;
            transform: translateY(-50%);
            transition: background-color .3s ease;
        }
        .uicore-simple-megamenu:not(.uicore-megamenu)>.sub-menu>li.menu-item-has-children>a:before{
            content: unset;
        }
        .uicore-menu .sub-menu li > a:before{
            left: 16px;
            right: 16px;
            top: 0px;
            bottom: 0px;
            transform: unset;
            height: auto;
        }
        .uicore-menu li.current-menu-item > a:before {
            background-color: var(--uicore-header--menu-effect-bg);
        }
        .uicore-menu ul.sub-menu{
            border-radius: clamp(0px, var(--ui-radius), 10px);
            box-shadow: 8px 25px 65px -10px rgb(0 0 0 / 10%) !important;
        }
        .uicore-navbar .uicore ul.sub-menu:not(.uicore-megamenu) li a {
            padding: 12px 25px;
        }
        .uicore-menu li>a svg {
            margin-right: 0;
        }
        .uicore-navbar nav.uicore ul.sub-menu:not(.uicore-megamenu) {
            padding: 15px 0;
        }
    }
    ';
    if($json_settings['menu_interaction'] === 'button' ){
        $css .= '
        .uicore-menu li.menu-item:hover > a:before {
            background-color: var(--uicore-header--menu-effect-bg);
        }
        ';
    }
	if ( $no_padding_right ) {
	    // $css .=
	    //     '
		// 	#wrapper-navbar .uicore-nav ul.uicore-menu > li:last-child:not(.menu-item-has-children) a:before {
		// 		right:calc(10px - var(--uicore-header--menu-spaceing))
		// 	}';
        $css .= '
        .uicore-menu > li:last-child > a:before{
            right: calc(-1 * var(--uicore-header--menu-spaceing));
        }
        ';
	}
}elseif($json_settings['menu_interaction'] === 'text flip'){
    $css .= '
    @media only screen and (min-width: ' . $br_points['md'] . 'px) {
        .ui-flip-anim-wrapp .ui-menu-item-wrapper:nth-child(2) {
            display: inline-block;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, 90%);
            opacity: 0;
        }
        .ui-anim-flip:hover > a .ui-flip-anim-wrapp .ui-menu-item-wrapper:nth-child(2) {
            transform: translate(-50%, 70%);
            opacity: 1;
        }
        .uicore-menu .ui-menu-item-wrapper,
        .ui-flip-anim-wrapp {
            display: inline-block;
            line-height: 1;
            position:relative;
        }
        .ui-anim-flip:hover > a .ui-flip-anim-wrapp {
            transform: translateY(-100%);
        }
        .ui-anim-flip:hover > a .ui-menu-item-wrapper:nth-child(1) {
            opacity: 0;
        }
        .ui-anim-flip > a .ui-flip-anim-wrapp, .ui-anim-flip > a .ui-flip-anim-wrapp .ui-menu-item-wrapper  {
            transition: opacity .4s, transform .7s;
            transition-timing-function: cubic-bezier(0.15, 0.85, 0.31, 1);
        }
    }
    @media only screen and (max-width: ' . $br_points['md'] . 'px) {
        .ui-flip-anim-wrapp .ui-menu-item-wrapper:nth-child(2) {
            opacity: 0 !important;
        }
    }
    ';
}
if($json_settings['menu_interaction'] === 'magnet button'){
    $css .= '
    @media only screen and (min-width: ' . $br_points['md'] . 'px) {
        ul.uicore-menu::before {
            --transition: 0.18s;
            content: "";
            position: fixed;
            pointer-events: none;
            top: var(--item-active-y);
            left: var(--item-active-x);
            height: var(--item-active-height);
            width: var(--item-active-width);
            opacity: var(--intent, 0);
            z-index: -1;
            border-radius: var(--item-active-radius);
            background: var(--uicore-header--menu-effect-bg, currentColor);
            transition:
                all var(--transition),
                top var(--transition),
                left var(--transition),
            height var(--transition),
            opacity var(--transition),
            color var(--transition),
            width var(--transition);
        transition-timing-function:cubic-bezier(0.53, 0.67, 0.45, 0.91);
        }
        ul.uicore-menu:has(> li:not(.current-menu-item) > a:is(:focus-visible, :hover)) {
            --intent: 1;
        }
    }
    ';
}


//_ham-nav.scss
if ( strpos( $json_settings['header_layout'], 'ham' ) !== false ) {
$css .= '
    .uicore-is-ham .uicore-navigation-wrapper {
        display: block !important;
    }
    .uicore-is-ham .uicore-mobile-head-right {
        display: flex !important;
    }
    @media (min-width: 99999999999px) {
    
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-branding.uicore-desktop {
            display: block;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-container, .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-extra, .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-menu-container.uicore-nav {
            padding: 0 var(--uicore-header--wide-spacing);
            width: 100% !important;
            max-width: 100% !important;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-extra {
            padding-bottom: var(--uicore-header--wide-spacing);
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-extra div:last-child {
            margin-bottom: 0 !important;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-branding.uicore-mobile, .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-custom-area.uicore-only-mobile {
            display: none !important;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-custom-area.uicore-only-desktop {
            display: flex;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-navigation-content .uicore-extra {
            align-items: flex-start;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-navigation-content .uicore-extra a.uicore-btn {
            align-self: start;
            margin: 0;
            width: auto;
            display: block;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-navigation-content .uicore-extra .uicore-custom-area .uicore-hca {
            margin-right: 4vw;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-navigation-content .uicore-extra .uicore-custom-area .uicore-hca .uicore-hca-title {
            padding-bottom: 10px;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-navigation-content .uicore-extra .uicore-custom-area .uicore-hca ul {
            display: flex;
            flex-direction: column;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-navigation-content .uicore-extra .uicore-social-icon {
            font-size: 1.2em;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-navigation-content .uicore-extra .uicore-social-icon:first-child {
            padding-left: 0 !important;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-navigation-content .uicore-extra .uicore-social-icon:last-child {
            padding-right: 0 !important;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-menu a {
            color: inherit;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-menu-container.uicore-nav {
            box-sizing: content-box;
            overflow: auto scroll;
            max-width: calc(100% - calc(2 * var(--uicore-header--wide-spacing))) !important;
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-menu-container.uicore-nav::-webkit-scrollbar {
            display: none;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .menu {
            display: flex;
            flex-direction: column;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-menu {
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-menu li a {
            padding: var(--uicore-header--menu-spaceing) 2em var(--uicore-header--menu-spaceing) 0;
            white-space: nowrap;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-menu .sub-menu {
            padding: 0 0 0 1em;
            width: auto;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-menu .sub-menu.uicore-active {
            right: auto;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-menu .menu-item-has-children {
            position: relative !important;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper .uicore-menu .menu-item-has-children > a:after {
            right: 0 !important;
            line-height: 1;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-classic .sub-menu:hover, .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-classic .uicore-menu .menu-item-has-children:hover > .sub-menu {
            display: block;
            opacity: 1;
            transform: translate3d(0, 0, 0);
            pointer-events: all;
            left: 100%;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-center .uicore-menu {
            margin: 0 auto;
            text-align: center;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-center .uicore-menu li a {
            padding-right: 0;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-center .uicore-menu .sub-menu {
            display: none;
            position: relative;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-center .uicore-menu .sub-menu li {
            opacity: 0.65;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-center .uicore-menu .sub-menu.uicore-active {
            right: 0 !important;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-center .uicore-custom-area .uicore-hca:last-child {
            margin-right: 0;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-center .uicore-extra {
            align-items: center;
            text-align: center;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-creative .uicore-navigation-content {
            flex-direction: row;
            margin: 0 auto;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-creative .uicore-navigation-content .uicore-menu .sub-menu {
            display: none;
            position: relative;
            padding: 0;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-creative .uicore-navigation-content .uicore-menu .sub-menu li {
            opacity: 0.65;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-creative .uicore-navigation-content .uicore-menu .sub-menu.uicore-active {
            right: 0 !important;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-creative .uicore-navigation-content .uicore-custom-area {
            flex-direction: column;
            margin-bottom: 0;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-creative .uicore-navigation-content .uicore-custom-area .uicore-hca {
            margin-bottom: 25px;
            margin-right: 0;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-creative .uicore-navigation-content .uicore-extra {
            max-width: 35% !important;
            position: relative;
            justify-content: center;
            padding-bottom: 0;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-center .uicore-navigation-content .uicore-menu .menu-item-has-children > a:after, .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-creative .uicore-navigation-content .uicore-menu .menu-item-has-children > a:after {
            font-family: inherit;
            content: "+";
            right: auto !important;
            left: auto;
            top: auto;
            display: inline-block;
            padding-left: 2em;
            transform: none;
        }
        .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-center .uicore-navigation-content .uicore-menu .menu-item-has-children:hover > a:after, .uicore-is-ham .uicore-mobile-menu-wrapper.uicore-ham-creative .uicore-navigation-content .uicore-menu .menu-item-has-children:hover > a:after {
            opacity: 1;
        }

        .uicore-mobile-nav-show .uicore-ham-classic .uicore-menu .sub-menu {
            left: 100%;
            position: absolute;
            top: 0;
        }
        .uicore-is-ham .uicore-ham-reveal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            display: block;
            pointer-events: none;
            transform: scaleX(0);
            animation-duration: 1s;
            transform-origin: right center;
            z-index: 100000000000000000000;
            animation-timing-function: cubic-bezier(0.87, 0, 0.13, 1);
        }

        .uicore-ham-creative .uicore-navigation-content {
            max-width: 100% !important;
        }

    }
    @keyframes uiCoreAnimationsHamReveal {
        0% {
            transform: scaleX(0);
            transform-origin: left center;
        }
        60% {
            transform: scaleX(1);
            transform-origin: left center;
        }
        61% {
            transform: scaleX(1.1);
            transform-origin: right center;
        }
        100% {
            transform: scaleX(0);
            transform-origin: right center;
        }
    }
    ';
}

$css .= '
@media (max-width: 99999999999px) {

    .uicore-mobile-nav-show .uicore-mobile-menu-wrapper .uicore-navigation-content .uicore-extra > div {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
    .uicore-mobile-nav-show #wrapper-navbar .uicore-ham {
        opacity: 0 !important;
    }
    .uicore-btn {
        display: block;
    }
    .uicore-cta-wrapper {
        width: 100%;
    }
    .uicore-branding.uicore-desktop, .uicore-only-desktop {
        display: none !important;
    }
}';