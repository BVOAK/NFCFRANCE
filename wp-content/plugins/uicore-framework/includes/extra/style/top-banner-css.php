<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

$css .=  '
.uicore-top-bar {
    padding-top: ' . $json_settings['header_top_padding'] . 'px;
    padding-bottom: ' . $json_settings['header_top_padding'] . 'px;
    font-size: ' . $json_settings['header_top_fonts'] . 'px;

}
#ui-banner-dismiss {
    padding: 0 ' . $json_settings['header_top_padding'] . 'px;
}
.uicore-top-bar a, .uicore-top-bar button{
    color: ' . $this->color($json_settings['header_top_linkcolor']['m']) . ';
}
.uicore-top-bar{
    color: ' . $this->color($json_settings['header_top_color']) .  ';
}
.uicore-top-bar a:hover, .uicore-top-bar button:hover{
    color: ' .   $this->color($json_settings['header_top_linkcolor']['h']) .   ';
}';

// Content align
if($json_settings['header_topone_position'] === 'center'){
    $css .= '
    .uicore-top-bar .ui-tb-col-1 {
        margin: 0 auto;
        text-align: center;
        float: none;
        justify-content: center;
    }
    ';
}
if($json_settings['header_topone_position'] === 'left'){
    $css .= '
    .uicore-top-bar .ui-tb-col-1 {
        text-align: left;
        float: left;
    }
    .uicore-top-bar .ui-tb-col-1 .uicore-social-icon:first-child,
    .uicore-top-bar .ui-tb-col-1 li:first-child a {
      padding-left: 0 !important;
    }
    ';
}
if($json_settings['header_topone_position'] === 'right'){
    $css .= '
    .uicore-top-bar .ui-tb-col-1 {
        text-align: right;
        float: right;
        justify-content: flex-end;
    }
    .uicore-top-bar .ui-tb-col-1 .uicore-social-icon:last-child,
    .uicore-top-bar .ui-tb-col-1 li:last-child a {
      padding-right: 0 !important;
    }
    ';
}
if($json_settings['header_toplayout'] === 'two columns' && $json_settings['header_toptwo_position'] === 'center'){
    $css .= '
    .uicore-top-bar .ui-tb-col-2 {
        margin: 0 auto;
        text-align: center;
        float: none;
        justify-content: center;
    }
    ';
}
if($json_settings['header_toplayout'] === 'two columns' && $json_settings['header_toptwo_position'] === 'left'){
    $css .= '
    .uicore-top-bar .ui-tb-col-2 {
        text-align: left;
        float: left;
    }
    .uicore-top-bar .ui-tb-col-2 .uicore-social-icon:first-child,
    .uicore-top-bar .ui-tb-col-2 li:first-child a {
      padding-left: 0;
    }
    ';
}
if($json_settings['header_toplayout'] === 'two columns' && $json_settings['header_toptwo_position'] === 'right'){
    $css .= '
    .uicore-top-bar .ui-tb-col-2 {
        text-align: right;
        float: right;
        justify-content: flex-end;
    }
    .uicore-top-bar .ui-tb-col-2 .uicore-social-icon:last-child,
    .uicore-top-bar .ui-tb-col-2 li:last-child a {
      padding-right: 0;
    }
    ';
}

//animations
if($global_animations && $json_settings['animations_topbanner'] != 'none'){

    $css .= '.uicore-top-bar .uicore-animate {';

    if($json_settings['animations_topbanner'] === 'fade'){
        $css .= '
            opacity: 0;
            animation-fill-mode: forwards;
            animation-duration: 1s;
            animation-name: uicoreFadeIn;
            animation-play-state: paused;
            animation-timing-function: '.$opacityEase.';
        ';
    }
    if($json_settings['animations_topbanner'] === 'fade down'){
        $css .= '
            opacity: 0;
			animation-fill-mode: forwards;
			animation-duration: 1.8s;
			animation-name: uicoreFadeInDown, uicoreFadeIn;
			animation-play-state: paused;
			animation-timing-function: '.$translateEase.','. $opacityEase.';
        ';
    }
    if($json_settings['animations_topbanner'] === 'fade up'){
        $css .= '
            opacity: 0;
			animation-fill-mode: forwards;
			animation-duration: 1.8s;
			animation-name: uicoreFadeInUp, uicoreFadeIn;
			animation-play-state: paused;
			animation-timing-function: '.$translateEase.','. $opacityEase.';
        ';
    }
    if( $json_settings['animations_topbanner_duration'] === 'fast'){
        $css .= '
            animation-duration: 1.3s;
        ';
    }
    if( $json_settings['animations_topbanner_duration'] === 'slow'){
        $css .= '
            animation-duration: 2.7s;
        ';
    }
    $css .= '}';
}

$css .= $this->background($json_settings['header_top_bg'] , '.uicore-top-bar');



//menu fix ( if is top bar and center creative) add an offset for submenu
if ( $json_settings['header_layout'] === 'center_creative') {
    $css .= '
    #wrapper-navbar.uicore-scrolled nav .menu-item-has-children {
        --uicore-submenu-offset: var(--uicore-header--menu-typo-h);
    }
    #wrapper-navbar.uicore-scrolled nav .menu-item-has-children:hover>.sub-menu {
        top: var(--uicore-submenu-offset);
    }
    ';
}