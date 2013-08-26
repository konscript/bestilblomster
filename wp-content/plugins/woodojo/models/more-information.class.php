<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo More Information Model
 *
 * The model for the "More Information" popup.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Administration
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $admin_page_hook ( sent from the main admin class )
 * var $component ( array to hold component data )
 * var $component_type
 * var $component_token
 * var $screenshots
 *
 * - __construct()
 * - parse_component_data()
 * - get_component_data()
 * - display_screen()
 * - get_screenshots()
 * - enqueue_scripts()
 * - enqueue_styles()
 */
class WooDojo_Model_MoreInformation extends WooDojo_Model {
	var $component;
	private $component_type;
	private $component_token;

	var $screenshots;
	
	function __construct() {
		parent::__construct();
		$this->component = array();
		$this->screenshots = array();
		
		add_action( 'admin_print_scripts', array( &$this, 'enqueue_scripts' ) );
		add_action( 'admin_print_styles', array( &$this, 'enqueue_styles' ) );
		
		add_action( 'admin_head', array( &$this, 'display_screen' ) );
	} // End __construct()
	
	/**
	 * parse_component_data function.
	 * 
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function parse_component_data () {
		if ( isset( $_GET['component'] ) && ( $_GET['component'] != '' ) ) {
			$this->component_token = strtolower( strip_tags( trim( $_GET['component'] ) ) );
		}
		
		if ( isset( $_GET['type'] ) && ( $_GET['type'] != '' ) && in_array( $_GET['type'], array( 'standalone', 'downloadable', 'bundled' ) ) ) {
			$this->component_type = strtolower( strip_tags( trim( $_GET['type'] ) ) );
		}
	} // End parse_component_data()
	
	/**
	 * get_component_data function.
	 * 
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function get_component_data () {
		$this->component = $this->components[$this->component_type][$this->component_token];
	} // End get_component_data()
	
	/**
	 * display_screen function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function display_screen () {
		$this->load_components();
		
		$this->parse_component_data();
		
		if ( isset( $this->component_token ) && isset( $this->component_type ) ) {
			$this->get_component_data();

			$this->screenshots = $this->get_screenshots();
			
			require_once( $this->config->screens_path . 'more-information.php' );
			
			exit;
		}
	} // End display_screen()
	
	/**
	 * get_screenshots function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @uses WooDojo_Utils::glob_php()
	 * @uses global $woodojo->settings->screenshot_url
	 * @return array $screenshots
	 */
	public function get_screenshots () {
		global $woodojo;

		$screenshots = array();

		if ( isset( $this->component->screenshot_url ) && ( $this->component->screenshot_url != '' ) ) {
			$screenshots = explode( ',', $this->component->screenshot_url );
		}

		if ( is_array( $screenshots ) && count( $screenshots ) > 0 ) {
			foreach ( $screenshots as $k => $v ) {
				$screenshots[$k] = esc_url( $woodojo->settings->screenshot_url . $v );
			}
		}

		$screenshots_path = trailingslashit( $this->config->assets_path . 'screenshots/' . $this->component_token );
		$screenshots_url = trailingslashit( $this->config->assets_url . 'screenshots/' . $this->component_token );

		if ( ( count( $screenshots ) == 0 ) && is_dir( $screenshots_path ) ) {
			$files = WooDojo_Utils::glob_php( '*.jpg', GLOB_MARK, $screenshots_path );

			if ( count( $files ) > 0 ) {
				foreach ( $files as $k => $v ) {
					$screenshots[] = $screenshots_url . basename( $v );
				}
			}
		}

		return $screenshots;
	} // End get_screenshots()

	/**
	 * enqueue_scripts function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->config->token . '-flexslider', $this->config->assets_url . 'js/jquery.flexslider-min.js', array( 'jquery' ), '1.8.0' );
		wp_register_script( $this->config->token . '-flexslider-setup', $this->config->assets_url . 'js/flexslider-setup.js', array( 'jquery', $this->config->token . '-flexslider' ), time() );
		
		wp_enqueue_script( $this->config->token . '-flexslider-setup' );
	} // End enqueue_scripts()
	
	/**
	 * enqueue_styles function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->config->token . '-flexslider', $this->config->assets_url . 'css/flexslider.css', '', '1.0.0', 'screen' );
		
		wp_enqueue_style( $this->config->token . '-flexslider' );
	} // End enqueue_styles()
} // End Class
?>