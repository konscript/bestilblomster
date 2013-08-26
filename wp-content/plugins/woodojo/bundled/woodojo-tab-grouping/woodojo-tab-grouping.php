<?php
/**
 * Module Name: WooDojo - Tab Grouping
 * Module Description: Create groups of tabs, with a custom tab order.
 * Module Version: 1.0.2
 *
 * @package WooDojo
 * @subpackage Downloadable
 * @author Matty
 * @since 1.0.0
*/

 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

 /* Instantiate The Feature */
 if ( class_exists( 'WooDojo' ) ) {
 	global $woodojo_tab_grouping;
 	require_once( 'classes/class-woodojo-tab-grouping.php' );
 	$woodojo_tab_grouping = new WooDojo_Tab_Grouping( __FILE__ );
 }
?>