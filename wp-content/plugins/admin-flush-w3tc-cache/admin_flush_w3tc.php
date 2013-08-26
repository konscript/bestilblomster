<?php
/**
 * @package admin_flush_w3tc
 * @version 1.2
 */
/*
Plugin Name: Admin Flush W3TC Cache
Plugin URI: 
Description: Admin Flush W3TC Cache works with the W3 Total Cache plugin.  It simply adds an "Empty All Caches" option to every Admin page.
Author: Dan Horne
Version: 1.2
Author URI: 
*/

/* 
OK, let's be honest, this is just a repurposed hello_dolly.  Give me a break, it's my first plugin.
*/

//Find out if W3 Total Cache is installed and active.
$plugins = get_option('active_plugins');
$required_plugin = 'w3-total-cache/w3-total-cache.php';
$w3tc_active = FALSE;
if ( in_array( $required_plugin , $plugins ) ) {
    //W3 Total Cache is active.
	$w3tc_active = TRUE;
}

//Output the link HTML
function admin_flush_w3tc() {
    $url = wp_nonce_url(admin_url('admin.php?page=w3tc_general&w3tc_flush_all'), 'w3tc');
	$link = "<a onclick=\"document.location.href='" . $url . "';\">Empty All Caches</a>";
	echo "<p id='admin_flush_w3tc'>$link</p>";
}

// Execute when the admin_notices action is called, but only if W3TC is active
if ( $w3tc_active === TRUE ) {
	add_action( 'admin_notices', 'admin_flush_w3tc' );
}

// Style the link
function admin_flush_w3tc_css() {
	// This makes sure that the positioning is also good for right-to-left languages
	$x = is_rtl() ? 'left' : 'right';

	echo "
	<style type='text/css'>
	#admin_flush_w3tc {
		float: $x;
		padding-$x: 15px;
		padding-top: 5px;		
		margin: 0;
		font-size: 11px;
		cursor: pointer;
	}
	</style>
	";
}

// Add the CSS to the header, but only if W3TC is active
if ( $w3tc_active === TRUE ) {
	add_action( 'admin_head', 'admin_flush_w3tc_css' );
}

?>
