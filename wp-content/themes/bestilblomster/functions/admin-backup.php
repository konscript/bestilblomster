<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/**
 * WooThemes Theme Options Backup
 *
 * Backup your "Theme Options" to a downloadable text file.
 *
 * @version 1.0.0
 * @author Matty
 * @since 4.5.0
 *
 * @package WooFramework
 * @subpackage Module
 *
 * TABLE OF CONTENTS
 *
 * - var $admin_page
 * - var $token
 * 
 * - function __construct ()
 * - function init ()
 * - function register_admin_screen ()
 * - function admin_screen ()
 * - function admin_screen_help ()
 * - function admin_screen_logic ()
 * - function move_admin_menu ()
 * - function import ()
 * - function export ()
 * - function add_to_export_query ()
 * - function add_single_to_export_query ()
 * - function construct_database_query ()
 *
 * - Create $woo_backup Object
 */

class WooThemes_Backup {
	private $admin_page;
	private $token;
	
	public function __construct () {
		$this->admin_page = '';
		$this->token = 'woothemes-backup';
	} // End __construct()
	
	/**
	 * init()
	 *
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 */
	
	public function init () {
		if ( is_admin() && ( get_option( 'framework_woo_backupmenu_disable' ) != 'true' ) ) {
			// Register the admin screen.
			add_action( 'admin_menu', array( &$this, 'register_admin_screen' ), 20 );
			add_action( 'admin_menu', array( &$this, 'move_admin_menu' ), 99 );
		}
	} // End init()
	
	/**
	 * register_admin_screen()
	 *
	 * Register the admin screen within WordPress.
	 *
	 * @since 1.0.0
	 */
	
	public function register_admin_screen () {
			
		$this->admin_page = add_submenu_page('woothemes', __( 'WooThemes Settings Backup', 'woothemes' ), __( 'Backup Settings', 'woothemes' ), 'manage_options', $this->token, array( &$this, 'admin_screen' ) );
			
		// Admin screen logic.
		add_action( 'load-' . $this->admin_page, array( &$this, 'admin_screen_logic' ) );
		
		// Add contextual help.
		add_action( 'contextual_help', array( &$this, 'admin_screen_help' ), 10, 3 );
				
		add_action( 'admin_notices', array( &$this, 'admin_notices' ), 10 );
	
	} // End register_admin_screen()
	
	/**
	 * admin_screen()
	 *
	 * Load the admin screen.
	 *
	 * @since 1.0.0
	 */
	
	public function admin_screen () {
	
		$export_type = 'all';
		
		if ( isset( $_POST['export-type'] ) ) {
			$export_type = esc_attr( $_POST['export-type'] );
		}
?>
	<div class="wrap">
		<?php screen_icon( 'tools' ); ?>	
		<h2><?php _e( 'Backup Settings', 'woothemes' ); ?></h2>
		
		<h3><?php _e( 'Import Settings', 'woothemes' ); ?></h3>
		
		<p><?php _e( 'If you have settings in a backup file on your computer, the WooFramework can import those into this site. To get started, upload your backup file to import from below.', 'woothemes' ); ?></p>

		<div class="form-wrap">
			<form enctype="multipart/form-data" method="post" action="<?php echo admin_url( 'admin.php?page=' . $this->token ); ?>">
				<?php wp_nonce_field( 'woothemes-backup-import' ); ?>
				<label for="woothemes-import-file"><?php printf( __( 'Upload File: (Maximum Size: %s)', 'woothemes' ), ini_get( 'post_max_size' ) ); ?></label>
				<input type="file" id="woothemes-import-file" name="woothemes-import-file" size="25" />
				<input type="hidden" name="woothemes-backup-import" value="1" />
				<input type="submit" class="button" value="<?php _e( 'Upload File and Import', 'woothemes' ); ?>" />
			</form>
		</div><!--/.form-wrap-->
		
		<h3><?php _e( 'Export Settings', 'woothemes' ); ?></h3>
		
		<p><?php _e( 'When you click the button below, the WooFramework will create a text file for you to save to your computer.', 'woothemes' ); ?></p>
		<p><?php echo sprintf( __( 'This text file can be used to restore your settings here on "%s", or to easily setup another website with the same settings".', 'woothemes' ), get_bloginfo( 'name' ) ); ?></p>
			
		<form method="post" action="<?php echo admin_url( 'admin.php?page=' . $this->token ); ?>">
			<?php wp_nonce_field( 'woothemes-backup-export' ); ?>
			<p><label><input type="radio" name="export-type" value="all"<?php checked( 'all', $export_type ); ?>> <?php _e( 'All Settings', 'woothemes' ); ?></label>
<span class="description"><?php _e( 'This will contain all of the options listed below.', 'woothemes' ); ?></span></p>

			<p><label for="content"><input type="radio" name="export-type" value="theme"<?php checked( 'theme', $export_type ); ?>> <?php _e( 'Theme Options', 'woothemes' ); ?></label></p>
			<p><label for="content"><input type="radio" name="export-type" value="framework"<?php checked( 'framework', $export_type ); ?>> <?php _e( 'Framework Settings', 'woothemes' ); ?></label></p>
			
			<input type="hidden" name="woothemes-backup-export" value="1" />
			<input type="submit" class="button" value="<?php _e( 'Download Export File', 'woothemes' ); ?>" />
		</form>
		
	</div><!--/.wrap-->
<?php
	
	} // End admin_screen()
	
	/**
	 * admin_screen_help()
	 *
	 * Add contextual help to the admin screen.
	 *
	 * @since 1.0.0
	 */
	
	public function admin_screen_help ( $contextual_help, $screen_id, $screen ) {
	
		// $contextual_help .= var_dump($screen); // use this to help determine $screen->id
		
		if ( $this->admin_page == $screen->id ) {
		
		$contextual_help =
		  '<h3>' . __( 'Welcome to the WooThemes Backup Manager.', 'woothemes' ) . '</h3>' .
		  '<p>' . __( 'Here are a few notes on using this screen.', 'woothemes' ) . '</p>' .
		  '<p>' . __( 'The backup manager allows you to backup or restore your "Theme Options" and other settings to or from a text file.', 'woothemes' ) . '</p>' .
		  '<p>' . __( 'To create a backup, simply select the setting type you\'d like to backup (or "All Settings") and hit the "Download Export File" button.', 'woothemes' ) . '</p>' .
		  '<p>' . __( 'To restore your settings from a backup, browse your computer for the file (under the "Import Settings" heading) and hit the "Upload File and Import" button. This will restore only the settings that have changed since the backup.', 'woothemes' ) . '</p>' .
		  
		  '<p><strong>' . __( 'Please note that only valid backup files generated through the WooThemes Backup Manager should be imported.', 'woothemes' ) . '</strong></p>' .

		  '<p><strong>' . __( 'Looking for assistance?', 'woothemes' ) . '</strong></p>' .
		  '<p>' . sprintf( __( 'Please post your query on the %sWooThemes Support Desk%s where we will do our best to assist you further.', 'woothemes' ), '<a href="http://support.woothemes.com/" target="_blank">', '</a>' ) . '</p>';
		
		} // End IF Statement
		
		return $contextual_help;
	
	} // End admin_screen_help()
	
	/**
	 * admin_notices()
	 *
	 * Display admin notices when performing backup/restore.
	 *
	 * @since 1.0.0
	 */
	
	public function admin_notices () {
	
		if ( ! isset( $_GET['page'] ) || ( $_GET['page'] != $this->token ) ) { return; }
	
		echo '<div id="import-notice" class="updated"><p>' . sprintf( __( 'Please note that this backup manager backs up only your settings and not your content. To backup your content, please use the %sWordPress Export Tool%s.', 'woothemes' ), '<a href="' . admin_url( 'export.php' ) . '">', '</a>' ) . '</p></div><!--/#import-notice .message-->' . "\n";
			
		if ( isset( $_GET['error'] ) && $_GET['error'] == 'true' ) {
			echo '<div id="message" class="error"><p>' . __( 'There was a problem importing your settings. Please Try again.', 'woothemes' ) . '</p></div>';
		} else if ( isset( $_GET['error-export'] ) && $_GET['error-export'] == 'true' ) {  
			echo '<div id="message" class="error"><p>' . __( 'There was a problem exporting your settings. Please Try again.', 'woothemes' ) . '</p></div>';
		} else if ( isset( $_GET['invalid'] ) && $_GET['invalid'] == 'true' ) {  
			echo '<div id="message" class="error"><p>' . __( 'The import file you\'ve provided is invalid. Please try again.', 'woothemes' ) . '</p></div>';
		} else if ( isset( $_GET['imported'] ) && $_GET['imported'] == 'true' ) {  
			echo '<div id="message" class="updated"><p>' . sprintf( __( 'Settings successfully imported. | Return to %sTheme Options%s', 'woothemes' ), '<a href="' . admin_url( 'admin.php?page=woothemes' ) . '">', '</a>' ) . '</p></div>';
		} // End IF Statement
		
	} // End admin_notices()
	
	/**
	 * admin_screen_logic()
	 *
	 * The processing code to generate the backup or restore from a previous backup.
	 *
	 * @since 1.0.0
	 */
	
	public function admin_screen_logic () {
		
		if ( ! isset( $_POST['woothemes-backup-export'] ) && isset( $_POST['woothemes-backup-import'] ) && ( $_POST['woothemes-backup-import'] == true ) ) {
			$this->import();
		}
		
		if ( ! isset( $_POST['woothemes-backup-import'] ) && isset( $_POST['woothemes-backup-export'] ) && ( $_POST['woothemes-backup-export'] == true ) ) {
			$this->export();
		}

	} // End admin_screen_logic()
	
	/**
	 * move_admin_menu()
	 *
	 * Reposition admin menu.
	 *
	 * @since 1.0.0
	 */
	 
	public function move_admin_menu () {
		global $submenu;
	
		if ( ! array_key_exists( 'woothemes', $submenu ) ) { return ; }
		
		$items_to_move = array();
		$first_item = array();
		$below_items = array();
		
		foreach ( $submenu['woothemes'] as $k => $s ) {
			if ( in_array( $s[2], array( 'woothemes-backup' ) ) ) {
				$items_to_move[] = $s;
				unset( $submenu['woothemes'][$k] );
			}
			
			if ( in_array( $s[2], array( 'woothemes_themes', 'woothemes_timthumb_update' ) ) ) {
				$below_items[] = $s;
				unset( $submenu['woothemes'][$k] );
			}
			
			if ( $k == 0 ) { $first_item[] = $s; unset( $submenu['woothemes'][$k]); }
		}
		
		sort( $items_to_move );
		
		$remaining_items = $submenu['woothemes'];
		
		// Grab the first item and unset it from the main array.
		$submenu['woothemes'] = array_merge( $first_item, $remaining_items, $items_to_move, $below_items );
	} // End move_admin_menu()
	
	/**
	 * import()
	 *
	 * Import settings from a backup file.
	 *
	 * @since 1.0.0
	 */
	 
	public function import() {
		check_admin_referer( 'woothemes-backup-import' ); // Security check.
		
		if ( ! isset( $_FILES['woothemes-import-file'] ) ) { return; } // We can't import the settings without a settings file.
		
		// Extract file contents
		$upload = file_get_contents( $_FILES['woothemes-import-file']['tmp_name'] );
		
		// Decode the JSON from the uploaded file
		$options = json_decode( $upload, true );
		
		// Check for errors
		if ( ! $options || $_FILES['woothemes-import-file']['error'] ) {
			wp_redirect( admin_url( 'admin.php?page=' . $this->token . '&error=true' ) );
			exit;
		}
		
		// Make sure this is a valid backup file.
		if ( ! isset( $options['woothemes-backup-validator'] ) ) {
			wp_redirect( admin_url( 'admin.php?page=' . $this->token . '&invalid=true' ) );
			exit;
		} else {
			unset( $options['woothemes-backup-validator'] ); // Now that we've checked it, we don't need the field anymore.
		}
		
		// Make sure the options are saved to the global options collection as well.
		$woo_options = get_option( 'woo_options' );

		$has_updated = false; // If this is set to true at any stage, we update the main options collection.
		
		// Cycle through data, import settings
		foreach ( (array)$options as $key => $settings ) {
			
			$settings = maybe_unserialize( $settings ); // Unserialize serialized data before inserting it back into the database.
			
			// We can run checks using get_option(), as the options are all cached. See wp-includes/functions.php for more information.
			if ( get_option( $key ) != $settings ) {
				update_option( $key, $settings );
			}
			
			if ( is_array( $woo_options ) ) {
				if ( isset( $woo_options[$key] ) && $woo_options[$key] != $settings ) {
					$woo_options[$key] = $settings;
					$has_updated = true;
				}
			}
		}
		
		if ( $has_updated == true ) {
			update_option( 'woo_options', $woo_options );
		}
		
		// Redirect, add success flag to the URI
		wp_redirect( admin_url( 'admin.php?page=' . $this->token . '&imported=true' ) );
		exit;
		
	} // End import()
	
	/**
	 * export()
	 *
	 * Export settings to a backup file.
	 *
	 * @since 1.0.0
	 * @uses global $wpdb
	 */
	 
	public function export() {
		global $wpdb;
		check_admin_referer( 'woothemes-backup-export' ); // Security check.
		
		$export_options = array( 'all', 'theme', 'framework' );		
		
		if ( ! in_array( strip_tags( $_POST['export-type'] ), $export_options ) ) { return; } // No invalid exports, please.
		
		$export_type = esc_attr( strip_tags( $_POST['export-type'] ) );
		
		$settings = array();
		
		$query = $this->construct_database_query( $export_type );
		
		// Error trapping for the export.
		if ( $query == '' ) {
			wp_redirect( admin_url( 'admin.php?page=' . $this->token . '&error-export=true' ) );
			return;
		}
		
		// If we get to this stage, all is safe so run the query.
		$results = $wpdb->get_results( $query );

		foreach ( $results as $result ) {
		
		     $settings[$result->option_name] = $result->option_value;
		
		} // End FOREACH Loop
		
		// Remove the "blogname" and "blogdescription" fields
		unset( $settings['blogname'] );
		unset( $settings['blogdescription'] );
		
		if ( ! $settings ) { return; }
	
		// Add our custom marker, to ensure only valid files are imported successfully.
		$settings['woothemes-backup-validator'] = date( 'Y-m-d h:i:s' );
	
		// Generate the export file.
	    $output = json_encode( (array)$settings );
	
	    header( 'Content-Description: File Transfer' );
	    header( 'Cache-Control: public, must-revalidate' );
	    header( 'Pragma: hack' );
	    header( 'Content-Type: text/plain' );
	    header( 'Content-Disposition: attachment; filename="' . $this->token . '-' . date( 'Ymd-His' ) . '.json"' );
	    header( 'Content-Length: ' . strlen( $output ) );
	    echo $output;
	    exit;
			
	} // End export()
	
	/**
	 * add_to_export_query()
	 *
	 * Loop through an array of options and add them to the MySQL SELECT query string.
	 *
	 * @since 1.0.0
	 * @param $options array
	 * @param $count int
	 * @return $query array ( string, count )
	 */
	 
	public function add_to_export_query ( $options, $count ) {
		$query = array();
		$query_inner = '';
		
		foreach( $options as $option ) {

			if( isset( $option['id'] ) ) {
				$count++;
				$option_id = $option['id'];
				
				$option_id = esc_attr( $option_id );
				$option_id = sanitize_title( $option_id );

				if( $count > 1 ) { $query_inner .= ' OR '; }
				$query_inner .= "option_name = '$option_id'";

				// Width/Height-type fields
				if ( is_array( $option['type'] ) ) {
					foreach ( $option['type'] as $o ) {
						if( $count > 1 ){ $query_inner .= ' OR '; }
						if ( isset( $o['id'] ) ) {
							$option_id = $o['id'];
							
							$option_id = esc_attr( $option_id );
							$option_id = sanitize_title( $option_id );	
							
							$query_inner .= "option_name = '$option_id'";
						}
					}
				}
				
				// Multicheck fields
				if ( ! is_array( $option['type'] ) && $option['type'] == 'multicheck' ) {
					foreach ( $option['options'] as $k => $v ) {
						if( $count > 1 ){ $query_inner .= ' OR '; }
						if ( ! is_numeric( $k ) ) {
							$option_id = $option['id'] . '_' . $k;
							
							$option_id = esc_attr( $option_id );
							$option_id = sanitize_title( $option_id );
							
							$query_inner .= "option_name = '$option_id'";
						}
					}
				}
			}
		}
		
		$query['string'] = $query_inner;
		$query['count'] = $count;
		
		return $query;
	} // End add_to_export_query()

	/**
	 * add_single_to_export_query()
	 *
	 * Add a single item to the MySQL SELECT query string.
	 *
	 * @since 1.0.0
	 * @param $option_id string
	 * @param $count int
	 * @return $query array ( string, count )
	 */
	 
	public function add_single_to_export_query ( $option_id, $count ) {
		$query = array();
		$query_inner = '';
		
		$option_id = esc_attr( $option_id );
		$option_id = sanitize_title( $option_id );
		
		if( $count > 1 ) { $query_inner .= ' OR '; }
		$query_inner .= "option_name = '$option_id'";
		
		$query['string'] = $query_inner;
		$query['count'] = $count;
		
		return $query;
	} // End add_single_to_export_query()
	
	/**
	 * construct_database_query()
	 *
	 * Constructs the database query based on the export type.
	 *
	 * @since 1.0.0
	 * @param $export_type string
	 * @uses global $wpdb
	 */
	
	public function construct_database_query ( $export_type ) {
		global $wpdb;
		
		$query = '';
		$query_inner = '';
		$count = 0;
	
		// Begin populating settings to be exported.
		switch ( $export_type ) {
		
			// All Settings
			case 'all':
				
				// Theme Options
				$options = get_option( 'woo_template' );
				
				if ( is_array( $options ) ) {
					$query = $this->add_to_export_query( $options, $count );
					
					$query_inner .= $query['string'];
					$count = $query['count'];
				}
				
				// Framework Settings
				$options = get_option( 'woo_framework_template' );
				
				if ( is_array( $options ) ) {
					// Remove the "framework_woo_export_options" and "framework_woo_import_options" items before constructing the query.
					foreach ( (array) $options as $k => $v ) {
						if ( isset( $options[$k]['id'] ) && in_array( $options[$k]['id'], array( 'framework_woo_import_options', 'framework_woo_export_options' ) ) ) {
							unset( $options[$k] );
						}
					}
					
					$query = $this->add_to_export_query( $options, $count );
					
					$query_inner .= $query['string'];
					$count = $query['count'];
				}
			break;
		
			// Theme Options
			case 'theme':
			
				$options = get_option( 'woo_template' );
				
				if ( is_array( $options ) ) {
					$query = $this->add_to_export_query( $options, $count );
					
					$query_inner .= $query['string'];
					$count = $query['count'];
				}
			
			break;
			
			// Framework Settings
			case 'framework':
			
				$options = get_option( 'woo_framework_template' );
				
				if ( is_array( $options ) ) {
					// Remove the "framework_woo_export_options" and "framework_woo_import_options" items before constructing the query.
					foreach ( (array) $options as $k => $v ) {
						if ( isset( $options[$k]['id'] ) && in_array( $options[$k]['id'], array( 'framework_woo_import_options', 'framework_woo_export_options' ) ) ) {
							unset( $options[$k] );
						}
					}
					
					$query = $this->add_to_export_query( $options, $count );
					
					$query_inner .= $query['string'];
					$count = $query['count'];
				}
			
			break;
		}
		
		// Allow child themes/plugins to add their own data to the exporter.
		$query_inner = apply_filters( 'wooframework_export_query_inner', $query_inner );
		
		if ( $query_inner != '' ) {
			$query = 'SELECT option_name, option_value FROM ' . $wpdb->options . ' WHERE ' . $query_inner;
		}
		
		return $query;
	
	} // End construct_database_query()
} // End Class

/**
 * Create $woo_backup Object.
 *
 * @since 1.0.0
 * @uses WooThemes_Backup
 */

$woo_backup = new WooThemes_Backup();
$woo_backup->init();
?>