<?php
defined('ABSPATH') || exit();

$css .="
.ui-cursor {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: var(--width);
  height: var(--height);
  transform: translate(calc(var(--x) - var(--width) / 2), calc(var(--y) - var(--height) / 2));
  transition-timing-function:cubic-bezier(0.18, 0.89, 0.32, 1.28);
  transition-property: width, height, transform;
  z-index: 99998;
  pointer-events: none;
  will-change: transform;
}
@media (pointer: fine) {
  .ui-cursor {
    display: block;
  }
}
.ui-cursor-main{
  transition-duration: 0.18s;
}
.ui-cursor-main::after {
  content:'';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 100%;
  border: 2px solid var(--border);
  background-color: var(--bg,transparent);
  opacity: var(--scale);
  transform: scale(var(--scale));
  transition: 0.3s cubic-bezier(0.25, 0.25, 0.42, 1) opacity, 0.3s cubic-bezier(0.25, 0.25, 0.42, 1) transform, 0.1s cubic-bezier(0.25, 0.25, 0.42, 1) border-radius;
}

body:not(body:hover) .ui-cursor::after {
  opacity: 0;
  transform: scale(0);
}
";

//&& ($json_settings['gen_cursor_style'] === 'border dual' || $json_settings['gen_cursor_style'] === 'solid small alt')
if($json_settings['gen_cursor_default'] === 'false'){
  $css .="
  body,a, input[type=\"submit\"], input[type=\"image\"], label[for], select, button, .link{
    cursor:none!important;
  }
  .ui-cursor {
    transition-timing-function:linear;
  }
  .ui-cursor-main{
    transition-duration: 0s;
  }
  ";
} 
if($json_settings['gen_cursor_style'] === 'solid small alt'){
  $css .="
  body{
    cursor: url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='10'><circle r='5' cx='5' cy='5'></circle></svg>\") 5 5,auto!important;
  }
  ";
} 

if($json_settings['gen_cursor_style'] === 'border dual'){
  $css .="
  .ui-cursor-main{
  transition-duration: 0.35s;
}
  .ui-dot-cursor{
    transition-duration: 0.05s;
    transition-timing-function:cubic-bezier(0.06, 0.37, 0, 0.72);
    width:10px;
    height:10px;
    border-radius:40px;
    transform: translate(calc(var(--x) - 5px),calc(var(--y) - 5px)) scale(var(--scale,1));
    background: ".$this->color($json_settings['gen_cursor_color'])."
  }
  .ui-cursor-main::after {
    transform: unset
  }
  ";
} 
