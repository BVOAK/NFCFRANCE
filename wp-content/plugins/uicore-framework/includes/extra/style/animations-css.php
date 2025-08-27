<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS


if($global_animations && $json_settings['animations_ham'] !== 'fade in'){
    $css .= '
    .uicore-ham-reveal{
        background-color:' . $this->color($json_settings['animations_ham_color']) . ';
    }
    ';
}
if( $json_settings['animations_footer_delay'] && $json_settings['animations_footer'] !='none'){
    $css .= '
    .uicore-footer-wrapper .uicore-animate {
        animation-delay: '.$json_settings['animations_footer_delay'].'ms
    }
    ';
}

if( $json_settings['animations_shop_delay_child'] && $json_settings['animations_shop'] !='none'){
    $css .= '
    main {
        --uicore-animations--shop-delay: '.$json_settings['animations_shop_delay_child'].'ms
    }
    ';
}
