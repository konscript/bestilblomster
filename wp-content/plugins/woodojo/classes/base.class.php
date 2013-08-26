<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo Base Class
 *
 * All functionality pertaining to both the administration and frontend sections of WooDojo.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Administration
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $name
 * var $token
 *
 * var $plugin_path
 * var $plugin_url
 *
 * var $assets_path
 * var $assets_url
 *
 * var $screens_path
 * var $screens_url
 *
 * var $components_path
 * var $components_url
 *
 * var $downloads_path
 * var $downloads_url
 * 
 * var $backups_path
 * var $backups_url
 *	
 * var $models_path
 * var $models_url
 *
 * - __construct()
 * - init_component_loaders()
 * - load_active_components()
 * - get_directory_by_type()
 */
class WooDojo_Base {
	var $name;
	var $token;
	
	var $plugin_path;
	var $plugin_url;
	
	var $assets_path;
	var $assets_url;
	
	var $screens_path;
	var $screens_url;
	
	var $components_path;
	var $components_url;

	var $downloads_path;
	var $downloads_url;

	var $backups_path;
	var $backups_url;
	
	var $models_path;
	var $models_url;

	private $loaded_components;
	
	/**
	 * Class Constructor.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		/* Setup default name and token. */
		$this->name = __( 'WooDojo', 'woodojo' );
		$this->token = 'woodojo';

		/* Setup plugin path and URL. */
		$this->plugin_path = trailingslashit( str_replace( '/classes', '', plugin_dir_path( __FILE__ ) ) );
		$this->plugin_url = trailingslashit( str_replace( '/classes', '', plugins_url( plugin_basename( dirname( __FILE__ ) ) ) ) );

		/* Cater for Windows systems where / is not present. */
		$this->plugin_path = trailingslashit( str_replace( 'classes', '', $this->plugin_path ) );
		$this->plugin_url = trailingslashit( str_replace( 'classes', '', $this->plugin_url ) );
		
		/* Setup assets path and URL. */
		$this->assets_path = trailingslashit( $this->plugin_path . 'assets' );
		$this->assets_url = trailingslashit( $this->plugin_url . 'assets' );
		
		/* Setup screens path and URL. */
		$this->screens_path = trailingslashit( $this->plugin_path . 'screens' );
		$this->screens_url = trailingslashit( $this->plugin_url . 'screens' );
		
		/* Setup bundled components path and URL. */
		$this->components_path = trailingslashit( $this->plugin_path . 'bundled' );
		$this->components_url = trailingslashit( $this->plugin_url . 'bundled' );
		
		/* Setup downloadable components path and URL. */
		$this->downloads_path = trailingslashit( trailingslashit( WP_PLUGIN_DIR ) . $this->token . '-downloads' );
		$this->downloads_url = trailingslashit( trailingslashit( WP_PLUGIN_URL ) . $this->token . '-downloads' );

		/* Setup component backups path and URL. */
		$this->backups_path = trailingslashit( $this->downloads_path . $this->token . '-backups' );
		$this->backups_url = trailingslashit( $this->downloads_url . $this->token . '-backups' );
		
		/* Setup models path and URL. */
		$this->models_path = trailingslashit( $this->plugin_path . 'models' );
		$this->models_url = trailingslashit( $this->plugin_url . 'models' );

		/* Keep track of loaded components. */
		$this->loaded_components = array();
		
		add_action( 'plugins_loaded', array( &$this, 'init_component_loaders' ) );
	} // End __construct()
	
	/**
	 * Initialise the component loaders.
	 *
	 * @description Load active components.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init_component_loaders () {
		$this->load_active_components( 'bundled' );
		$this->load_active_components( 'downloadable' );
	} // End init_component_loaders()
	
	/**
	 * Load the active components of a given type.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $type
	 * @return void
	 */
	public function load_active_components ( $type = 'bundled' ) {
		$components = get_option( $this->token . '_' . $type . '_active', array() );
		
		$path = $this->get_directory_by_type( $type );
		
		if ( is_array( $components ) && count( $components ) > 0 ) {
			do_action( $this->token . '_load_' . $type . '_components_before' ); // eg: woodojo_load_bundled_components_before
			foreach ( $components as $k => $v ) {
				do_action( $this->token . '_load_' . $type . '_component_' . $k . '_before' ); // eg: woodojo_load_bundled_component_woo-tabs_before
				if ( file_exists( $path . $v ) && ! in_array( $v, $this->loaded_components ) ) {
					require_once( $path . $v );
					$this->loaded_components[] = $v;
				}
				do_action( $this->token . '_load_' . $type . '_component_' . $k . '_after' ); // eg: woodojo_load_bundled_component_woo-tabs_after
			}
			do_action( $this->token . '_load_' . $type . '_components_after' ); // eg: woodojo_load_bundled_components_after
		}
	} // End load_active_components()
	
	/**
	 * Retrieve the directory path for a given type of component.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $type (default: 'bundled')
	 * @return string $path
	 */
	public function get_directory_by_type ( $type = 'bundled' ) {
		$path = '';
		switch ( $type ) {
			case 'standalone':
				$path = trailingslashit( WP_PLUGIN_DIR );
			break;
			
			case 'downloadable':
				$path = $this->downloads_path;
			break;
			
			case 'bundled':
			default:
				$path = $this->components_path;
			break;
		}
		
		return $path;
	} // End get_directory_by_type()
} // End Class
?>