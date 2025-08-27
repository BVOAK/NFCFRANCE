<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS JS

if($settings['gen_cursor_style'] === 'border dual'){
  $js .= "
  let cursorDot = document.createElement('div');
  cursorDot.className = 'ui-cursor ui-dot-cursor';
  document.body.append(cursorDot);
  ";
}
if($settings['gen_cursor_style'] === 'border' || $settings['gen_cursor_style'] === 'border dual'){
  $cursor_bg = $hover_bg = 'tarnsparent';
}else{
  $cursor_bg = $hover_bg = $cursor_color ;
}


$blend_mode = $hover_scale = '';
if($settings['gen_cursor_hover'] === 'difference'){
  $blend_mode = 'difference';
}
if($settings['gen_cursor_hover'] === 'scale'){
  $hover_scale = '1.9';
}

if($settings['gen_cursor_style'] === 'solid small' || $settings['gen_cursor_style'] === 'solid small alt'){
  $cursor_size = '12';

  if($settings['gen_cursor_hover'] === 'difference'){
    $blend_mode = 'difference';
    $hover_scale = '1.6';
  }
  if($settings['gen_cursor_hover'] === 'scale'){
    $hover_scale = '4';
  }

}else{
  $cursor_size = '42';
}

$js .= "

  const updateProperties = (elem, state) => {

    elem.style.setProperty('--x', state.x + 'px');
    elem.style.setProperty('--y', state.y + 'px');
    elem.style.setProperty('--width', state.width + 'px');
    elem.style.setProperty('--height', state.height + 'px');
    elem.style.setProperty('--scale', state.scale);
    elem.style.setProperty('mix-blend-mode', state.blend);
    elem.style.setProperty('--border', state.border);
    elem.style.setProperty('--bg', state.bg);
    elem.style.setProperty('opacity', state.hover);
  
  };
  
  document.querySelectorAll('.ui-cursor').forEach(cursor => {
  
    const createState = e => {
  
      const defaultState = {
        x: e.clientX,
        y: e.clientY,
        width: '".$cursor_size."',
        height: '".$cursor_size."',
        blend: 'unset',
        border: '".$cursor_color."',
        bg: '".$cursor_bg."',
        hover: 1,
        scale: 1
      };
  
      const computedState = {};

      if (window.ui_onElement) {
        computedState.scale = '".$hover_scale."';
        computedState.blend = '".$blend_mode."';
        computedState.bg = '".$hover_bg."';
        computedState.hover = '".( $blend_mode === 'difference' ? '1' : '0.7' )."';
      }

      return {
        ...defaultState,
        ...computedState 
      };
  
    };

    if(!document.getElementsByClassName('e-preview--show-hidden-elements').length){
      document.addEventListener('mousemove', e => {
        const state = createState(e);
        updateProperties(cursor, state);
      });
    }
  
  });

  document.querySelectorAll(
    'a, input[type=\"submit\"], input[type=\"image\"], label[for], select, button, .link'
  ).forEach(el => {

    el.addEventListener('mouseenter', () => window.ui_onElement = true)
		el.addEventListener('mouseleave', () => window.ui_onElement = false)
  });
  
";
