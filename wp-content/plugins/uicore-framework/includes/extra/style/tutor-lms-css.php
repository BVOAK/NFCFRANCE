<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

$css .='   
.tutor-container{
    width: 95%;
    padding-left: 10px!important;
    padding-right: 10px!important;
    max-width:' . $json_settings['gen_full_w'] . 'px;
}
@media (max-width: ' . $br_points['lg'] .'px) {
    .tutor-wrap:not(.tutor-user-public-profile){
        padding:75px 0px;
    }
}

@media (max-width: ' .  $br_points['md'] . 'px) {
    .tutor-wrap:not(.tutor-user-public-profile){
        padding:50px 0px;
    }
}

@media (min-width: ' . $br_points['lg'] .  'px) {
    .tutor-wrap:not(.tutor-user-public-profile){
        padding:100px 0px;
    }
}
';