<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS JS

$toggle = $settings['header_sd_toggle'];

$js .= '
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".uicore-drawer-toggle, .ui-header-drawer .ui-sd-backdrop").forEach(function(el) {
        el.addEventListener("click", function() {
            ui_show_sd();
        });
    });
});
';

if($toggle === 'hover'){
    $js .= '
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".uicore-drawer-toggle").forEach(function(el) {
            el.addEventListener("mouseenter", function() {
                ui_show_sd();
            });
        });
        document.querySelectorAll(".ui-drawer-content").forEach(function(el) {
            el.addEventListener("mouseleave", function() {
                ui_show_sd();
            });
        });
    });
 ';
}

//Toggle Function (common)
$js .= '
function ui_show_sd(elClassName) {
    elClassName = elClassName || ".ui-header-drawer";
    document.querySelectorAll(elClassName).forEach(function(el) {
        el.classList.toggle("ui-active");
    });
}
';

//used for filters drawer
$js .= '
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".ui-drawer-toggle, .ui-filters-drawer .ui-sd-backdrop").forEach(function(el) {
        el.addEventListener("click", function() {
            ui_show_sd(\'.ui-filters-drawer\');
        });
    });
});
';