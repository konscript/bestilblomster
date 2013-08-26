<?php
/**
 * Module Name: WooDojo - ShortLinks
 * Module Description: Another classic WooDojo feature, WooDojo - ShortLinks automatically generates short URLs for your posts, using your URL shortening service of choice.
 * Module Version: 1.0.3
 * Module Settings: woodojo-shortlinks-settings
 *
 * @package WooDojo
 * @subpackage Bundled
 * @author Patrick
 * @since 1.0.0
 */
 
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}
 
 /* Include Shortlinks Class*/
 require_once( 'classes/woodojo-shortlinks.class.php' );
 /* Instantiate Shortlinks */
 if ( class_exists( 'WooDojo' ) ) {
 	$woodojo_shortlinks = new WooDojo_ShortLinks();
 }
?>