<?php
/**
 * Module Name: WooDojo - Widget Preview
 * Module Description: Easily enable "preview mode" while adding, styling and setting up widgets on your website.
 * Module Version: 1.0.1
 *
 * @package WooDojo
 * @subpackage Downloadable
 * @author Matty
 * @since 1.0.0
*/

 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

 /* Instantiate WooDojo Widget Preview */
 if ( class_exists( 'WooDojo' ) && ! class_exists( 'WooDojo_Widget_Preview' ) ) {
 	require_once( 'classes/class-woodojo-widget-preview.php' );
 	$woodojo_widget_preview = new WooDojo_Widget_Preview();
 }
?>