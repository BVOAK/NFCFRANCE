<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

if($json_settings['button_interaction'] === "text flip"){
    $css .= "
    .ui-btn-anim-wrapp .elementor-button-text:nth-child(2) {
        display: inline-block;
        position: absolute;
        width:100%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, 80%);
        opacity: 0;
    }
    
    .elementor-animation-flip:hover .ui-btn-anim-wrapp .elementor-button-text:nth-child(2) {
        transform: translate(-50%, 100%);
        opacity: 1;
    }
    
    .ui-btn-anim-wrapp {
        display: inline-block;
        order: 10;
    }
    
    .elementor-animation-flip:hover .ui-btn-anim-wrapp {
        transform: translateY(-150%);
    }
    
    .elementor-animation-flip:hover .elementor-button-text:nth-child(1) {
        opacity: 0;
    }
    
    .elementor-animation-flip .ui-btn-anim-wrapp, .elementor-animation-flip .ui-btn-anim-wrapp .elementor-button-text  {
        transition: opacity .6s, transform .8s;
        transition-timing-function: cubic-bezier(0.15, 0.85, 0.31, 1);
    }
    
    .elementor-animation-flip {
        position: relative;
    }
    ";
  }
  if($json_settings['button_interaction'] === "float"){
    $css .= "
    .elementor-animation-float:hover {
        transform: translateY(-10px);
    }
    ";

  }

  $css .= $this->shadow($json_settings['button_shadow'],$btn_css_selector);