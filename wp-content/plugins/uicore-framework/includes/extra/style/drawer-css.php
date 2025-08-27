<?php
defined('ABSPATH') || exit();

//only desktop
$css .= '
button.uicore-ham.uicore-drawer-toggle {
    display: flex;
    width: auto;
    gap: 10px;
    padding:0!important;
}
button.uicore-ham.uicore-drawer-toggle .bar,
button.uicore-ham.uicore-drawer-toggle .bars{
    transition: none!important;
    transition-delay: 0s!important;
}
button.uicore-ham.uicore-drawer-toggle .bar{
    background-color:currentColor!important;
}
.uicore-drawer-toggle .ui-text{
    line-height:1;
}
.ui-drawer{
    position: fixed;
    top: 0;
    bottom:0;
    left: 0;
    right:0;
    pointer-events:none;
	z-index: 100;
}
.ui-drawer-wrapp {
    display: block;
    position: fixed;
    top: 0;
    bottom:0;
    right:auto;
    left:auto;
    '.$json_settings['header_sd_position'] .': 0;
    z-index: 100;
    width: 450px;
    max-width: 87vw;
    height: 100vh!important;
    box-shadow: 0 0 50px rgb(0 0 0 / 15%);
    transform: translate3d('.($json_settings['header_sd_position'] === 'right' ? '' : '-' ).'100%,0,0);
    opacity: 0;
    transition: transform .85s cubic-bezier(.23,1,.32,1),opacity .6s step-end;
}
.ui-drawer-content {
    width: 100%;
    height: 100%;

    position: relative;
    overflow-y: auto;
    overscroll-behavior: contain;
    opacity: 0;
    transform: translate3d('.($json_settings['header_sd_position'] === 'right' ? '' : '-' ).'20%,0,0);
    transition: transform .85s,opacity .85s;
    transition-timing-function: cubic-bezier(.23,1,.32,1);

}
.ui-drawer-content:not(
  :has([class^="wp-block-uicore-"])
) {
    padding: 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.ui-sd-backdrop{
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    background: black;
    opacity: 0;
    transition: opacity .7s ease;
    cursor: url(\'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj48bGluZSB4MT0iOCIgeTE9IjgiIHgyPSIyNCIgeTI9IjI0IiBzdHJva2U9ImJsYWNrIiBzdHJva2Utd2lkdGg9IjIiLz48bGluZSB4MT0iMjQiIHkxPSI4IiB4Mj0iOCIgeTI9IjI0IiBzdHJva2U9ImJsYWNrIiBzdHJva2Utd2lkdGg9IjIiLz48L3N2Zz4=\'), auto;
}
.ui-drawer .ui-close{
    position: absolute;
    top: 10px;
    right: 10px;
    cursor:pointer;
	font-size: 30px;
    padding: 0px 10px 5px;
	background: transparent;
}
.ui-drawer.ui-active{
    pointer-events:all;
}
.ui-drawer.ui-active .ui-drawer-wrapp,
.ui-drawer.ui-active .ui-drawer-content {
    transition: transform .85s cubic-bezier(.23,1,.32,1),opacity .6s step-start;
    transform: translate3d(0,0,0);
    opacity: 1;
}
.ui-drawer.ui-active .ui-drawer-content {
    transition: transform .85s cubic-bezier(.23,1,.32,1),opacity .6s ease;
    transition-delay: .2s;
}
.ui-drawer.ui-active .ui-sd-backdrop {
    opacity: .4;
}
.ui-drawer-widget:not(
  :has(> :first-child[class^="wp-block-uicore-"])
) {
	padding-bottom: 20px;
}
.ui-drawer-widget .wp-block-separator.is-style-wide {
    border-bottom-width: 0;
}

@media (max-width: 767px) {
    .ui-drawer-wrapp {
        max-width:100vw;
    }
    .ui-drawer-content {
        padding:35px;
    }
}
';
$css .= $this->background($json_settings['header_sd_bg'] , '.ui-drawer-wrapp', 'min-width: '.$json_settings['mobile_breakpoint'].'px');

