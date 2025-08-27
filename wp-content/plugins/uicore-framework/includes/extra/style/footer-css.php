<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

$css .= $this->background($json_settings['footer_bg'] , '.uicore-footer-wrapper .uicore-inner-footer', false, true, $br_points);

$css .= '
.uicore-footer-wrapper .uicore-inner-footer,
.uicore-footer-wrapper .uicore-inner-footer p{
    font-family: ' . $this->fam($json_settings['footer_text']['f']) . ';
    color: ' . $this->color($json_settings['footer_text']['c']) . ';
    font-size:' . $json_settings['footer_text']['s']['d'] . 'px;
    letter-spacing:' . $json_settings['footer_text']['ls'] . 'em;
    line-height:' . $json_settings['footer_text']['h'] . ';
    text-transform:' . $json_settings['footer_text']['t'] . ';
    font-weight:' . $this->wt($json_settings['footer_text']) . ';
    font-style:' . $this->st($json_settings['footer_text']) . ';

}
.uicore-footer-title{
    font-family: ' . $this->fam($json_settings['footer_title']['f']) . ';
    color: ' .$this->color( $json_settings['footer_title']['c']) . ';
    font-size:' . $json_settings['footer_title']['s']['d'] .  'px;
    letter-spacing:' .  $json_settings['footer_title']['ls'] . 'em;
    line-height:' .  $json_settings['footer_title']['h'] .  ';
    text-transform:' .  $json_settings['footer_title']['t'] .  ';
    font-weight:' . $this->wt($json_settings['footer_title']) . ';
    font-style:' . $this->st($json_settings['footer_title']) . ';
}
.uicore-footer-wrapper .uicore-inner-footer p {
    color: ' . $this->color($json_settings['footer_text']['c']) . ';
}
.uicore-footer-wrapper .uicore-inner-footer a{
    color:' . $this->color($json_settings['footer_link']['m']) .
    '
}
.uicore-footer-wrapper .uicore-inner-footer a:hover{
    color:' . $this->color($json_settings['footer_link']['h']) .
    '
}


@media (max-width: ' .$br_points['lg'] .'px) {
    .uicore-inner-footer{
        padding:calc(' .$json_settings['footer_padding']['t'] .'px * 0.5) 0px;
    }
    .uicore-inner-footer .uicore-footer-column{
        padding:calc(' .$json_settings['footer_padding']['t'] .'px * 0.5) 0px;
    }
    .uicore-footer-column .uicore-footer-widget:not(:first-child) {
        padding-top: ' . $json_settings['footer_padding']['t'] . 'px
    }
    .uicore-footer-title{
        font-size:' .$json_settings['footer_title']['s']['t'] .'px;
    }
}

@media (min-width: ' .$br_points['lg'] .'px) {
    .uicore-inner-footer{
        padding:calc(' .$json_settings['footer_padding']['d'] .'px * 0.5 ) 0px;
    }
    .uicore-inner-footer .uicore-footer-column{
        padding:calc(' .$json_settings['footer_padding']['d'] .'px * 0.5) 0px;
    }
}

@media (max-width: ' . $br_points['md'] . 'px) {
    .uicore-inner-footer{
        padding:calc(' . $json_settings['footer_padding']['m'] . 'px * 0.5) 0px;
    }
    .uicore-inner-footer .uicore-footer-column{
        padding:calc(' . $json_settings['footer_padding']['m'] . 'px * 0.5) 0px;
    }
    .uicore-footer-column .uicore-footer-widget:not(:first-child) {
        padding-top: calc(' . $json_settings['footer_padding']['m'] . 'px * 0.5)
    }
    .uicore-footer-title{
        font-size:' . $json_settings['footer_title']['s']['m'] . 'px;
    }

}
';

//animations
if($global_animations &&  $json_settings['animations_footer'] != 'none'){

    $css .= '.uicore-footer-wrapper .uicore-animate {';

    if($json_settings['animations_footer'] === 'fade'){
        $css .= '
            opacity: 0;
            animation-fill-mode: forwards;
            animation-duration: 1s;
            animation-name: uicoreFadeIn;
            animation-play-state: paused;
            animation-timing-function: '.$opacityEase.';
        ';
    }
    if($json_settings['animations_footer'] === 'fade down'){
        $css .= '
            opacity: 0;
			animation-fill-mode: forwards;
			animation-duration: 1.8s;
			animation-name: uicoreFadeInDown, uicoreFadeIn;
			animation-play-state: paused;
			animation-timing-function: '.$translateEase.','. $opacityEase.';
        ';
    }
    if($json_settings['animations_footer'] === 'fade up'){
        $css .= '
            opacity: 0;
			animation-fill-mode: forwards;
			animation-duration: 1.8s;
			animation-name: uicoreFadeInUp, uicoreFadeIn;
			animation-play-state: paused;
			animation-timing-function: '.$translateEase.','. $opacityEase.';
        ';
    }
    if( $json_settings['animations_footer_duration'] === 'fast'){
        $css .= '
            animation-duration: 1.3s;
        ';
    }
    if( $json_settings['animations_footer_duration'] === 'slow'){
        $css .= '
            animation-duration: 2.7s;
        ';
    }
    $css .= '}';
}