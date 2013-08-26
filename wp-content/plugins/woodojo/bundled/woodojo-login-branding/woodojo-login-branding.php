<?php
/**
	* Module Name: WooDojo - Login Branding
	* Module Description: Another classic WooDojo feature, WooDojo - Login Branding automatically rebrands your Login screen with a custom logo.
	* Module Version: 1.0.2
	* Module Settings: woodojo-login-branding
	*
	* @package WooDojo
	* @subpackage Bundled
	* @author Patrick
	* @since 1.0.0
*/

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

 /* Instantiate Login Branding */
 if ( class_exists( 'WooDojo' ) ) {
	/* Include Login Branding Class*/
 	require_once('classes/woodojo-login-branding.class.php');
 	$woodojo_login_branding = new WooDojo_Login_Branding();
 }