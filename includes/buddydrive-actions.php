<?php
/**
 * BuddyDrive Actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


// BuddyPress / WordPress actions to BuddyDrive ones
add_action( 'bp_init',                  'buddydrive_init',                     14 );
add_action( 'bp_ready',                 'buddydrive_ready',                    10 );
add_action( 'bp_setup_current_user',    'buddydrive_setup_current_user',       10 );
add_action( 'bp_setup_theme',           'buddydrive_setup_theme',              10 );
add_action( 'bp_after_setup_theme',     'buddydrive_after_setup_theme',        10 );
add_action( 'bp_enqueue_scripts',       'buddydrive_register_scripts',          1 );
add_action( 'bp_admin_enqueue_scripts', 'buddydrive_register_scripts',          1 );
add_action( 'bp_enqueue_scripts',       'buddydrive_enqueue_scripts',          10 );
add_action( 'bp_setup_admin_bar',       'buddydrive_setup_admin_bar',          10 );
add_action( 'bp_actions',               'buddydrive_actions',                  10 );
add_action( 'bp_screens',               'buddydrive_screens',                  10 );
add_action( 'admin_init',               'buddydrive_admin_init',               10 );
add_action( 'admin_head',               'buddydrive_admin_head',               10 );
add_action( 'buddydrive_admin_init',    'buddydrive_do_activation_redirect',    1 );
add_action( 'buddydrive_admin_init',    'buddydrive_admin_register_settings',  11 );
add_action( 'bp_template_redirect',     'buddydrive_maybe_redirect_oldlink',  100 );


function buddydrive_init(){
	do_action( 'buddydrive_init' );
}

function buddydrive_ready(){
	do_action( 'buddydrive_ready' );
}

function buddydrive_setup_current_user(){
	do_action( 'buddydrive_setup_current_user' );
}

function buddydrive_setup_theme(){
	do_action( 'buddydrive_setup_theme' );
}

function buddydrive_after_setup_theme(){
	do_action( 'buddydrive_after_setup_theme' );
}

function buddydrive_register_scripts() {
	do_action( 'buddydrive_register_scripts' );
}

function buddydrive_enqueue_scripts(){
	do_action( 'buddydrive_enqueue_scripts' );
}

function buddydrive_setup_admin_bar(){
	do_action( 'buddydrive_setup_admin_bar' );
}

function buddydrive_actions(){
	do_action( 'buddydrive_actions' );
}

function buddydrive_screens(){
	do_action( 'buddydrive_screens' );
}

function buddydrive_admin_init() {
	do_action( 'buddydrive_admin_init' );
}

function buddydrive_admin_head() {
	do_action( 'buddydrive_admin_head' );
}

function buddydrive_admin_register_settings() {
	do_action( 'buddydrive_admin_register_settings' );
}

// Activation redirect
add_action( 'buddydrive_activation', 'buddydrive_add_activation_redirect' );
