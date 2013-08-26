<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo - Custom Code
 *
 * Add custom CSS code or HTML in the <head> or before the closing </body> tag.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Bundled
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 * 
 * var $token
 * var $settings_screen
 * var $settings
 * 
 * - __construct()
 * - load_settings_screen()
 * - enqueue_custom_css()
 * - output_custom_css()
 * - output_custom_html()
 */
class WooDojo_CustomCode {
	
	/* Variable Declarations */
	var $token;
	var $settings_screen;
	var $settings;
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct () {
		/* Class Settings */
		$this->token = 'woodojo-custom-code';

		/* Settings Screen */
		$this->load_settings_screen();

		$this->settings = $this->settings_screen->get_settings();

		/* Output custom CSS optionally */
		if ( $this->settings['custom-css-enable'] == true && $this->settings['custom-css-code'] != '' ) {
			if ( isset( $_GET[$this->token] ) && ( $_GET[$this->token] == 'css' ) ) {
				add_action( 'template_redirect', array( &$this, 'output_custom_css' ), 0 );
			}
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_custom_css' ) );
		}

		/* Output custom HTML optionally */
		if ( $this->settings['custom-html-enable'] == true ) {
			if ( $this->settings['custom-html-code-head'] != '' ) {
				add_action( 'wp_head', array( &$this, 'output_custom_html' ), 100 );
			}
			if ( $this->settings['custom-html-code-footer'] != '' ) {
				add_action( 'wp_footer', array( &$this, 'output_custom_html' ), 100 );
			}
		}
	} // End __construct()
	
	/**
	 * load_settings_screen function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_settings_screen () {
		/* Settings Screen */
		require_once( 'settings.class.php' );
		$this->settings_screen = new WooDojo_CustomCode_Settings();
		
		/* Setup Data */
		$this->settings_screen->token = $this->token;
		if ( is_admin() ) {
			if ( current_user_can( 'unfiltered_html' ) ) {
				$this->settings_screen->name = __( 'WooDojo Custom CSS/HTML', 'woodojo' );
				$this->settings_screen->menu_label = __( 'Custom CSS/HTML', 'woodojo' );
			} else {
				$this->settings_screen->name = __( 'WooDojo Custom CSS', 'woodojo' );
				$this->settings_screen->menu_label = __( 'Custom CSS', 'woodojo' );
			}
			$this->settings_screen->page_slug = 'woodojo-custom-code';
		}
		$this->settings_screen->setup_settings();
	} // End load_settings_screen()

	/**
	 * enqueue_custom_css function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_custom_css () {
		wp_register_style( $this->token, home_url( '/?' . $this->token . '=css' ), '', '1.0.0', 'screen' );
		wp_enqueue_style( $this->token );
	} // End enqueue_custom_css()

	/**
	 * output_custom_css function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function output_custom_css () {
		header( 'Content-Type: text/css' );

		echo $this->settings['custom-css-code'];

		die();
	} // End output_custom_css()

	/**
	 * output_custom_html function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function output_custom_html () {
		switch ( current_filter() ) {
			case 'wp_head':
				echo html_entity_decode( $this->settings['custom-html-code-head'] );
			break;

			case 'wp_footer':
				echo html_entity_decode( $this->settings['custom-html-code-footer'] );
			break;
		}
	} // End output_custom_html()
} // End Class WooDojo_CustomCode
?>