<?php
/**
 * Module Name: WooDojo - Custom Code
 * Module Description: The WooDojo Custom Code feature adds the facility to easy add custom CSS code to your website, as well as custom HTML code in the <head> section or before the </body> tag.
 * Module Version: 1.0.2
 * Module Settings: woodojo-custom-code
 *
 * @package WooDojo
 * @subpackage Bundled
 * @author WooThemes
 * @since 1.0.0
 */
 
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

 /* Include Class */
 require_once( 'classes/woodojo-custom-code.class.php' );
 /* Instantiate Class */
 if ( class_exists( 'WooDojo' ) ) {
 	$woodojo_custom_code = new WooDojo_CustomCode();
 }
?>