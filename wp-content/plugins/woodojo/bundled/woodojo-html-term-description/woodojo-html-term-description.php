<?php
/**
 * Module Name: WooDojo - HTML Term Description
 * Module Description: The WooDojo HTML term description feature adds the ability to use html in term descriptions, as well as a visual editor to make input easier.
 * Module Version: 1.0.2
 *
 * @package WooDojo
 * @subpackage Downloadable
 * @author WooThemes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* Include Class */
require_once( 'classes/class-woodojo-html-term-description.php' );

/* Instantiate Class */
if ( class_exists( 'WooDojo' ) ) {
	$woodojo_html_term_description = new WooDojo_HTML_Term_Description();
}
?>