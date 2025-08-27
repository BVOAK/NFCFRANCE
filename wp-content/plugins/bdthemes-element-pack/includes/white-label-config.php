<?php

if ( ! defined( 'BDTEP_TITLE' ) ) {
    $white_label_title = get_option( 'ep_white_label_title' );
	define( 'BDTEP_TITLE', $white_label_title );
}

$hide_license = get_option( 'ep_white_label_hide_license', false );

if ( $hide_license ) {
    if ( ! defined( 'BDTEP_LO' ) ) {
        define( 'BDTEP_LO', true );
    }
}

$hide_ep = get_option( 'ep_white_label_bdtep_hide', false );

if ( $hide_ep ) {
    if ( ! defined( 'BDTEP_HIDE' ) ) {
        define( 'BDTEP_HIDE', true );
    }
}