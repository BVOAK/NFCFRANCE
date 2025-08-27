<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS JS

//this.children[0].style.display = 'inline-block';
$js .= "window.addEventListener('DOMContentLoaded', () => {";
if($settings['button_interaction'] === 'attract'){
  $js .= "
  jQuery('".$css_selectors."').on('mousemove',function(e) {
      const pos = this.getBoundingClientRect();
      const mx = e.clientX - pos.left - pos.width/2; 
      const my = e.clientY - pos.top - pos.height/2;
      
      this.style.transform = 'translate('+ mx * 0.15 +'px, '+ my * 0.3 +'px)';
      this.style.transform += 'rotate3d('+ mx * -0.1 +', '+ my * -0.3 +', 0, 12deg)';
      if(this.children[0]){
          this.children[0].style.transition = 'all .2s linear';
          this.children[0].style.transform = 'translate('+ mx * 0.07 +'px, '+ my * 0.14 +'px)';
      }
    }).on('mouseleave', function() {
      this.style.transform = 'translate3d(0px, 0px, 0px)';
      this.style.transform += 'rotate3d(0, 0, 0, 0deg)';
      if(this.children[0])
      this.children[0].style.transform = 'translate3d(0px, 0px, 0px)';
    })
  ";
}elseif(substr( $settings['button_interaction'], 0, 5 ) === "text "){
  $js .= "
  jQuery('".$css_selectors."').on('mouseenter',function(e) {
    var animClass = 'elementor-animation-".str_replace('text ', '',$settings['button_interaction'])."';
    if(!jQuery(this).hasClass(animClass)){
      
      if(jQuery(this).children('span').not('[class]') && !jQuery(this).children('span').not('[class]').children('elementor-button-text')){
        jQuery(this).children('span').not('[class]').addClass('elementor-button-text');
      }
      if(jQuery(this).children().length == 0 ){
        jQuery(this).wrapInner('<span class=\"elementor-button-text\"></span>');
      }
      var btnContent = jQuery(this).find('.elementor-button-text, .bdt-newsletter-btn-text, .bdt-scroll-button-text').addClass('elementor-button-text');
      btnContent.after(btnContent.clone())
      jQuery(this).find('.elementor-button-text').wrapAll('<span class=\"ui-btn-anim-wrapp\"></span>');
      setTimeout(() => {
        this.classList.add(animClass)
      }, 30)
    }
  })
  ";
}else{
  $js .= "
  jQuery('".$css_selectors."').on('mouseenter',function(e) {
    this.classList.add('elementor-animation-".$settings['button_interaction']."')
  })
  ";
}
$js .= "}, false);";