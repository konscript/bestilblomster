<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
// Sidebar Manager has been removed as of WooFramework version 5.5.0.

if ( ! function_exists( 'woo_sidebar' ) ) {
function woo_sidebar( $id = 1 ) {
	return dynamic_sidebar( $id );
} // End woo_sidebar()
}

if ( ! function_exists( 'woo_active_sidebar' ) ) {
function woo_active_sidebar( $id ) {
	if( is_active_sidebar( $id ) )
		return true;

	return false;
} // End woo_active_sidebar()
}
?>