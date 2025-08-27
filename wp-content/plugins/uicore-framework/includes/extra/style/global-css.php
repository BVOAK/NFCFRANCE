<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

//If the radius is bigger than 20px, we need to divide it by 2
$radius = (int)$json_settings['button_border_radius'];
$radius = $radius > 20 ? $radius / 2.6 : $radius;
$radius = $radius > 20 ? $radius / 1.5 : $radius;


$css .= '
:root body {
    --uicore-primary-color: ' . $json_settings['pColor'] . ';
    --uicore-secondary-color: ' . $json_settings['sColor'] . ';
    --uicore-accent-color: ' . $json_settings['aColor'] . ';
    --uicore-headline-color: ' . $json_settings['hColor'] . ';
    --uicore-body-color: ' . $json_settings['bColor'] . ';
    --uicore-dark-color: ' . $json_settings['dColor'] . ';
    --uicore-light-color: ' . $json_settings['lColor'] . ';
    --uicore-white-color: ' . $json_settings['wColor'] . ';
    --uicore-primary-font-family: "' . $json_settings['pFont']['f']. '";
    --uicore-secondary-font-family: "' . $json_settings['sFont']['f']. '";
    --uicore-accent-font-family: "' . $json_settings['aFont']['f']. '";
    --uicore-text-font-family: "' . $json_settings['tFont']['f']. '";

    --ui-border-color: '. $this->generateBorderColor($json_settings['gen_layout'] == 'boxed' ? $json_settings['gen_boxed_bg'] : $json_settings['gen_bg']).';
    
    --uicore-boxed-width: '.( $json_settings['gen_layout'] == 'boxed' ? $json_settings['gen_boxed_w'].'px' : '100%' ).';
    --ui-container-size: '. $json_settings['gen_full_w'] .'px;
    --ui-radius:'. (int)$radius .'px;
    --ui-radius-sm:'. (int)$radius / 2 .'px;
}';

if ($json_settings['gen_siteborder'] == 'true') {
    $css .=
        '
    @media (min-width: ' . $br_points['md'] . 'px) {
        #uicore-page{
            border: ' .  $json_settings['gen_siteborder_w'] . 'px solid  ' . $this->color($json_settings['gen_sitebordercolor']) . ';
            box-shadow: inset 0 0 0 1px ' . $this->color($json_settings['gen_sitebordercolor']) . ';
        }
    }
    ';
}


if ($json_settings['gen_layout'] == 'boxed') {
    $css .=
        '
    body.uicore-boxed .uicore-reveal .uicore-post-info,
    .uicore-fade-light .uicore-zoom-wrapper,
    .ui-simple-creative,
    .content-area,
    .uicore-body-content > footer {
        background-color:' . $this->color($json_settings['gen_boxed_bg']) . '
    }
    ';
    $css .=
    '
    body.uicore-boxed #uicore-page{
        max-width: ' .
        $json_settings['gen_boxed_w'] .
        'px;
        margin:0 auto;
        background-color:' . $this->color($json_settings['gen_boxed_bg']) .
        ';
    ';
    if(isset($json_settings['gen_boxed_bg']['blur']) && $json_settings['gen_boxed_bg']['blur'] === 'true'){
        $css .= '
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        ';
    }
    $css .='}
    ';

} else {
        //Backgrounds
    $css .= $this->background($json_settings['gen_bg'] ,
            '.uicore-reveal .uicore-post-info,
            .ui-simple-creative,
            .uicore-fade-light .uicore-zoom-wrapper,
            .content-area,
            .uicore-body-content > footer,
            .uicore-main-background');
}


$css .= '
    .uicore-page-link.current,
    .uicore-pagination ul li a,
    .comment-list .says,
    blockquote,
    dt,
    .comment-meta .fn {
        color:' . $this->color( $json_settings['h4']['c']) . '
    }

';


//Backgrounds
$css .= $this->background($json_settings['gen_bg'] , '.uicore-body-content');



$css .= '
    a{
        color: ' . $this->color($json_settings['link_color']['m']) . ';
    }
    a:hover{
        color: ' . $this->color($json_settings['link_color']['h']) . ';
    }
';



//maybe use those
$css .= '
.uicore-section.uicore-box nav.uicore-container,
.uicore-section.uicore-box > .uicore-container, .uicore-ham-creative .uicore-navigation-content,
.container-width .uicore-megamenu > .elementor,
#wrapper-navbar.uicore-section.uicore-box .uicore-container .uicore-megamenu .uicore-section.uicore-box .uicore-container,
#wrapper-navbar.uicore-section.uicore-section-full_width .uicore-container .uicore-megamenu .uicore-section.uicore-box .uicore-container,

.elementor-section-boxed:not(.elementor-inner-section) > .elementor-container {
    max-width: var(--container-max-width);
}
.uicore-section,
.elementor-section,
.ui-sortable > .e-con,
.elementor > .e-con {
    --container-max-width: min(95%, var(--ui-container-size));
}
#uicore-page {
	position: relative;
	z-index: 0;
}
';

//noise
if ($json_settings['gen_noise'] != 'none') {
    if($json_settings['gen_noise'] === 'soft'){
        $opacity = '.15';
    }else if($json_settings['gen_noise'] === 'medium'){
        $opacity = '.3';
    }else{
        $opacity = '.55';
    }
    $css .= '
    body:before{
        content: "";
        display: block;
        background-image: url('.get_home_url(null,'/wp-content/plugins/uicore-framework/assets/img/noise.webp').');
        opacity: '.$opacity.';
        background-repeat: repeat;
        background-size: 257px auto;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        pointer-events: none;
        z-index: 99999998;
    }
    ';
}

if($json_settings['gen_line'] === 'true'){
    $css .= '
    #uicore-page {
        position: relative;
        z-index: 0;
        --uicore-grid-line-offset: calc('. $json_settings['gen_line_offset'] .' * 1px);
        --uicore-grid-line-width: '. $json_settings['gen_line_w'] .'px;
        --uicore-grid-line-color: '. $this->color($json_settings['gen_line_color']) .';
        --uicore-grid-line-max-width: '. ($json_settings['gen_line_width'] == "contained" ? "min(95%, calc(var(--ui-container-size) + calc(var(--uicore-grid-line-offset) * 2)))" : "100%" ).';
    }
    @media (max-width: ' .$br_points['lg'] .'px) {
        #uicore-page {
            --uicore-grid-line-columns: '.( (int) $json_settings['gen_line_col']['t'] - 1 ).';
        }
    }
    @media (min-width: ' .$br_points['lg'] .'px) {
        #uicore-page {
            --uicore-grid-line-columns: '.( (int) $json_settings['gen_line_col']['d'] - 1 ).';
        }
    }
    @media (max-width: ' . $br_points['md'] . 'px) {
        #uicore-page {
            --uicore-grid-line-columns: '.( (int) $json_settings['gen_line_col']['m'] - 1 ).';
        }
    }
    #uicore-page::before{
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        margin-right: auto;
        margin-left: auto;
        pointer-events: none;
        z-index: '. $json_settings['gen_line_z'] .';
        min-height: 100vh;
        max-width: var(--uicore-grid-line-max-width, 100%);
        border-right: var(--uicore-grid-line-width) solid var(--uicore-grid-line-color);
        background: linear-gradient(to right, var(--uicore-grid-line-color, #eee) var(--uicore-grid-line-width, 1px), transparent 0);
        background-repeat: repeat-x;
        background-size: calc(100% / var(--uicore-grid-line-columns));
    }
    ';

    if($json_settings['gen_line_animation'] == 'true'){
        $css .= '
        .uicore-content::after,
        .uicore-content::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            margin-right: auto;
            margin-left: auto;
            pointer-events: none;
            z-index: '. ((int)$json_settings['gen_line_z'] + 1) .';
            min-height: 100vh;
            width: var(--uicore-grid-line-width);
            background: repeating-linear-gradient(0, ' . $this->color($json_settings['gen_line_animation_color']) . ', transparent 25vh, transparent 320vh);
            background-size: 100% 250%;
            box-shadow: 0 0 20px -16px rgba(' . $this->color($json_settings['gen_line_animation_color']) . ', 0.2);
        }

        .uicore-content::after {
            left: min(95%, calc(var(--ui-container-size) - 1px));
            animation: moveBackground 28s linear infinite;
        }

        .uicore-content::before {
            right: min(95%, calc(var(--ui-container-size) - 1px));
            left: 0;
            background-size: 100% 230%; 
            animation: moveBackground 35.5s linear infinite 1s;
        }

        @keyframes moveBackground {
            0% {
                background-position: 0 100%;
            }
            100% {
                background-position: 0 0;
            }
        }
        ';
    
    }
}
if($json_settings['gen_btop'] === 'true'){
    $css .= $this->background($json_settings['gen_btop_bg'] , '#uicore-back-to-top');
    $css .= '
    .uicore-back-to-top {
        position: fixed;
        right: 2em;
        bottom: 2em;
        display: inline-block;
        z-index: 999;
        transform: rotate(180deg) scale(1);
        font-size: 15px;
        line-height: 40px!important;
        width: 40px;
        text-align: center;
        color: ' . $this->color($json_settings['gen_btop_color']['m']) . ';
        border-radius: '. $json_settings['gen_btop_radius'] .'px;
        box-shadow: 1px 0 22px -9px rgba(0,0,0,.4);
        transition: all .3s cubic-bezier(0.61, -0.12, 0.08, 1.55);
        cursor: pointer;
    }
    .uicore-back-to-top:hover{
        transform:rotate(180deg) scale(1.1);
        box-shadow: 0 0 20px -9px rgba(0, 0, 0, 0.2);
        color: ' . $this->color($json_settings['gen_btop_color']['h']) . ';
    }
    .uicore-back-to-top:before {
        font-size: 100%;
    }
    .uicore-back-to-top:not(.uicore-visible){
        opacity: 0;
        pointer-events: none;
        transform: rotate(180deg) scale(.3);
    }';

}