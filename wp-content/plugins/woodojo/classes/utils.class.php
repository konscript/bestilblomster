<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo Utils Class
 *
 * A class containing various WooDojo utilities.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * - load_common_l10n()
 * - glob_php()
 * - create_zip()
 * - get_or_post()
 */
class WooDojo_Utils {
	/**
	 * load_common_l10n function.
	 *
	 * @description Load common translatable strings, commonly made available to JavaScript files.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_common_l10n () {
		$strings = array(
						'disable' => __( 'Deactivate', 'woodojo' ), 
						'enable' => __( 'Activate', 'woodojo' ), 
						'disabled' => __( 'Deactivated', 'woodojo' ), 
						'enabled' => __( 'Activated', 'woodojo' ), 
						'enabled_successfully' => __( '%s Activated Successfully', 'woodojo' ), 
						'disabled_successfully' => __( '%s Deactivated Successfully', 'woodojo' ), 
						'enabled_error' => __( 'There was an error activating %s', 'woodojo' ), 
						'disabled_error' => __( 'There was an error deactivating %s', 'woodojo' ), 
						'upgrade_confirmation' => __( 'Are you sure you\'d like to update %s?', 'woodojo' )
						);
		
		return $strings;
	} // End load_common_l10n()

	/**
	 * glob_php function.
	 *
	 * @description Returns an array of all PHP files in the specified absolute path.
	 * Equivalent to glob( "$absolute_path/*.php" ).
	 * @since  1.0.0
	 * @access public
	 * @static
	 * @param string $absolute_path The absolute path of the directory to search.
	 * @return array Array of absolute paths to the PHP files.
	 */
	public static function glob_php ( $pattern, $flags = 0, $path = '' ) {
	    if ( ! $path && ( $dir = dirname( $pattern ) ) != '.' ) {
	    	
	        if ($dir == '\\' || $dir == '/') { $dir = ''; } // End IF Statement
	
	        return self::glob_php(basename( $pattern ), $flags, $dir . '/' );
	    
	    } // End IF Statement
	    
	    $paths = glob( $path . '*', GLOB_ONLYDIR | GLOB_NOSORT );
	    $files = glob( $path . $pattern, $flags );

	    if ( $files == '' ) {
	    	$files = array();
	    }

	    if ( is_array( $paths ) && count( $paths ) > 0 ) {
		    foreach ( $paths as $p ) {
		   		if ( is_array( $files ) ) {
		   			$files = array_merge( $files, self::glob_php( $pattern, $flags, $p . '/' ) );
		   		}	
		    } // End FOREACH Loop
		}
	    
	    return $files;
	} // End glob_php()

	/**
	 * create_zip function.
	 *
	 * @description Create a ZIP file from a given array of files.
	 * @since  1.0.0
	 * @access public
	 * @static
	 * @param array $files
	 * @param string $root_path
	 * @param string $destination
	 * @param boolean $overwrite
	 * @return boolean
	 */
	public static function create_zip ( $files = array(), $root_path, $destination = '', $overwrite = false ) {
		//if the zip file already exists and overwrite is false, return false
		if( file_exists( $destination) && ! $overwrite ) { return false; }
		//vars
		$valid_files = array();
		//if files were passed in...
		if(is_array($files)) {
			//cycle through each file
			foreach($files as $file) {
				//make sure the file exists
				if(file_exists($root_path . $file)) {
					$valid_files[] = $file;
				}
			}
		}
		//if we have good files...
		if( count( $valid_files ) ) {
			//create the archive
			$zip = new ZipArchive();
			if( $zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true ) {
				return false;
			}
			//add the files
			foreach( $valid_files as $file ) {
				$zip->addFile( $root_path . $file, $file );
			}
			//debug
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
			
			//close the zip -- done!
			$zip->close();
			
			//check to make sure the file exists
			return file_exists( $destination );
		} else {
			return false;
		}
	} // End create_zip()

	/**
	 * Determine whether a value should be called from $_GET or from $_POST.
	 * @access public
	 * @since  1.1.3
	 * @param  string $field The field to look for.
	 * @return string        The response.
	 */
	public static function get_or_post ( $field ) {
		$response = '';
		$field = trim( strip_tags( $field ) );
		if ( isset( $_GET[$field] ) && $_GET[$field] != '' ) {
			$response = esc_attr( $_GET[$field] );
		} else if ( isset( $_POST[$field] ) && $_POST[$field] != '' ) {
			$response = esc_attr( $_POST[$field] );
		}

		return $response;
	} // End get_or_post()

}
?>