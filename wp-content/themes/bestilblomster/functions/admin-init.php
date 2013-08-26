<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;

/*-----------------------------------------------------------------------------------*/
/* WooThemes Framework Version & Theme Version */
/*-----------------------------------------------------------------------------------*/
function woo_version_init () {
    $woo_framework_version = '5.5.5';
    if ( get_option( 'woo_framework_version' ) != $woo_framework_version ) {
    	update_option( 'woo_framework_version', $woo_framework_version );
    }
} // End woo_version_init()

add_action( 'init', 'woo_version_init', 10 );

function woo_version () {
    $data = wooframework_get_theme_version_data();
	echo "\n<!-- Theme version -->\n";
    if ( isset( $data['is_child'] ) && true == $data['is_child'] ) echo '<meta name="generator" content="'. esc_attr( $data['child_theme_name'] . ' ' . $data['child_theme_version'] ) . '" />' ."\n";
    echo '<meta name="generator" content="'. esc_attr( $data['theme_name'] . ' ' . $data['theme_version'] ) . '" />' ."\n";
    echo '<meta name="generator" content="WooFramework '. esc_attr( $data['framework_version'] ) .'" />' ."\n";
} // End woo_version()

// Add or remove Generator meta tags
if ( ! is_admin() && get_option( 'framework_woo_disable_generator' ) == 'true' ) {
	remove_action( 'wp_head',  'wp_generator' );
} else {
	add_action( 'wp_head', 'woo_version', 10 );
}
/*-----------------------------------------------------------------------------------*/
/* Load the required Framework Files */
/*-----------------------------------------------------------------------------------*/

$functions_path = get_template_directory() . '/functions/';
$classes_path = $functions_path . 'classes/';

require_once ( $functions_path . 'admin-functions.php' );					// Custom functions and plugins
require_once ( $functions_path . 'admin-setup.php' );						// Options panel variables and functions
require_once ( $functions_path . 'admin-custom.php' );						// Custom fields
require_once ( $functions_path . 'admin-interface.php' );					// Admin Interfaces (options,framework, seo)
require_once ( $functions_path . 'admin-framework-settings.php' );			// Framework Settings
require_once ( $functions_path . 'admin-seo.php' );							// Framework SEO controls
require_once ( $functions_path . 'admin-sbm.php' ); 						// Framework Sidebar Manager
require_once ( $functions_path . 'admin-medialibrary-uploader.php' ); 		// Framework Media Library Uploader Functions // 2010-11-05.
require_once ( $functions_path . 'admin-hooks.php' );						// Definition of WooHooks

if ( get_option( 'framework_woo_woonav' ) == 'true' ) {
	require_once ( $functions_path . 'admin-custom-nav.php' );				// Woo Custom Navigation
}

require_once ( $functions_path . 'admin-shortcodes.php' );					// Woo Shortcodes

// Load certain files only in the WordPress admin.
if ( is_admin() ) {
    require_once ( $functions_path . 'admin-shortcode-generator.php' ); 		// Framework Shortcode generator // 2011-01-21.
    require_once ( $functions_path . 'admin-backup.php' ); 						// Theme Options Backup // 2011-08-26.
}
?>