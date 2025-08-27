<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

//align
if ($json_settings['pagetitle_align'] == 'center') {
    $css .= '
    .uicore-page-title .uicore{
        align-items: center;
    }
	.uicore-page-title div.uicore-animate {
		display: flex;
    	justify-content: center;
	}
    ';
} else {
	$css .= '.ui-breadcrumb {
		margin-left: 3px;
	}';
}
$css .= '
.uicore-page-title {
    text-align:' . $json_settings['pagetitle_align'] .';
}
  ';

//transparent header paddings
if ($json_settings['header_transparent'] === 'true') {
    if ($json_settings['header_layout'] === 'center_creative') {
        $css .= '
        @media  (min-width: 1024px) {
            .uicore-page-title{
                padding-top:'.( (intval( $json_settings['header_logo_h'])  + ( intval($json_settings['header_padding']) * 2 ) ) +  (($json_settings['header_2_padding'] * 2) + $json_settings['menu_typo']['s'] ) ).'px
            }
        }
        ';
    }
}
//overlay
if ($json_settings['pagetitle_overlay']['type'] === 'none') {
    $css .=
        '
    .uicore-page-title .uicore-overlay{
       display:none;
    } ';
}else{
    if(isset($json_settings['pagetitle_overlay']['blur']) && $json_settings['pagetitle_overlay']['blur'] === 'true'){
        $css .=
        '
    .uicore-page-title .uicore-overlay{
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    } ';
    }
}
if ($json_settings['pagetitle_overlay']['type'] === 'solid') {
    $css .=
        '
    .uicore-page-title .uicore-overlay{
        background-color: '  . $json_settings['pagetitle_overlay']['solid'] . ';
    } ';
}
if ($json_settings['pagetitle_overlay']['type'] == 'gradient') {
    $css .=
        '
    .uicore-page-title .uicore-overlay {
        background-image: linear-gradient(' .
        $json_settings['pagetitle_overlay']['gradient']['angle'] .
        'deg,' .
        $json_settings['pagetitle_overlay']['gradient']['color1'] .
        ', ' .
        $json_settings['pagetitle_overlay']['gradient']['color2'] .
        ');
    } ';
}

//background
$css .= $this->background($json_settings['pagetitle_bg'] , '.uicore-page-title', false, true, $br_points);
//fallback for feature image
if($json_settings['pagetitle_bg']['type'] != 'image'){
    $css .= '
    .uicore-page-title{
        background-size:cover;
        background-position:center;
    }';
}

$css .= '
@media ( max-width: ' .  $br_points['lg'] . 'px) {
    .uicore-page-title.uicore-section > .uicore{
        padding:' .  $json_settings['pagetitle_padding']['t'] . 'px 0px;
    }
}

@media (min-width: ' .$br_points['lg'] .'px) {
    .uicore-page-title.uicore-section > .uicore{
        padding:' . $json_settings['pagetitle_padding']['d'] . 'px 0px;
    }

    '.// Page title content width
    '.uicore-page-title h1.uicore-title,  .uicore-page-title a,  .uicore-page-title .uicore-desc-wrapp{
        max-width:' . $json_settings['pagetitle_width'] . '%;
    }
}

@media (max-width: ' .$br_points['md'] .'px) {
    .uicore-page-title.uicore-section > .uicore{
        padding:' .$json_settings['pagetitle_padding']['m'] .'px 0px;
    }
}
';

$css .= '
.uicore-page-title h1.uicore-title,  .uicore-page-title a,  .uicore-page-title p,  .uicore-page-title a:hover,
.uicore-page-title .uicore-entry-meta span,
.uicore-page-title .uicore-entry-meta .uicore-post-category{
    color:' .$this->color($json_settings['pagetitle_color']) . ';
}
.uicore-page-title h1.uicore-title{
    text-transform:' . $json_settings['pagetitle_transform'] . ';
}

.uicore-page-title {
    border-radius:' . $json_settings['pagetitle_radius'] . 'px;
    margin:' . $json_settings['pagetitle_margin']. 'px;
}
';

//animations
if($global_animations && $json_settings['animations_title'] != 'none'){

    $css .= '
    .ui-a-in-view.uicore-page-title .uicore-animate{
		animation-play-state: running;
	}


    .uicore-page-title .uicore-animate:nth-child(1){
        animation-delay:'.(int)$json_settings['animations_title_delay'].'ms;
    }
    .uicore-page-title .uicore-animate:nth-child(2){
        animation-delay:'.((int)$json_settings['animations_title_delay_child']  + (int)$json_settings['animations_title_delay']).'ms;
    }
    .uicore-page-title .uicore-animate:nth-child(3){
        animation-delay: '.(((int)$json_settings['animations_title_delay_child'] * 2) + (int)$json_settings['animations_title_delay']).'ms;
    }
    .uicore-page-title .uicore-animate:nth-child(4){
        animation-delay: '.(((int)$json_settings['animations_title_delay_child'] * 3) + (int)$json_settings['animations_title_delay']).'ms;
    }

    .uicore-page-title .uicore-animate {';

    if($json_settings['animations_title'] === 'fade'){
        $css .= '
            opacity: 0;
            animation-fill-mode: forwards;
            animation-duration: 1s;
            animation-name: uicoreFadeIn;
            animation-play-state: paused;
            animation-timing-function: '.$opacityEase.';
        ';
    }
    if($json_settings['animations_title'] === 'fade down'){
        $css .= '
            opacity: 0;
            animation-fill-mode: forwards;
            animation-duration: 1.8s;
            animation-name: uicoreFadeInDown, uicoreFadeIn;
            animation-play-state: paused;
			animation-timing-function: '.$translateEase.','. $opacityEase.';
        ';
    }
    if($json_settings['animations_title'] === 'fade up'){
        $css .= '
            opacity: 0;
			animation-fill-mode: forwards;
			animation-duration: 1.8s;
			animation-name: uicoreFadeInUp, uicoreFadeIn;
			animation-play-state: paused;
			animation-timing-function: '.$translateEase.','. $opacityEase.';
        ';
    }
    if( $json_settings['animations_title_duration'] === 'fast'){
        $css .= '
            animation-duration: 1.2s;
        ';
    }
    if( $json_settings['animations_title_duration'] === 'slow'){
        $css .= '
            animation-duration: 2.7s;
        ';
    }
    $css .= '} ';
}

if($json_settings['blogs_title'] === 'full screen'){
    $css .= '
    .single-post .uicore-page-title{
        height: calc(100vh - '. ((int)$json_settings['pagetitle_margin'] * 2).'px);
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .single-post .uicore-page-title.uicore-section>.uicore{
        margin-bottom:70px;
    }';
}
if($json_settings['blogs_excerpt_in_pt'] === 'true'){
    $css .= '
    .single-post .uicore-page-title p.uicore-description {
        font-size:120%;
        margin:-0.6em 0 1.3em 0;
        opacity:.88
    }';
}