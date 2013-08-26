<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo Administration Class
 *
 * All functionality pertaining to the administration sections of WooDojo.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Administration
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $model
 *
 * - __construct()
 * - admin_screen_register()
 * - admin_menu_order()
 * - admin_screen()
 * - admin_head()
 * - admin_page_load()
 * - admin_styles()
 * - admin_styles_global()
 * - admin_scripts()
 * - ajax_component_toggle()
 * - ajax_component_display_toggle()
 * - ajax_get_closed_components()
 */
class WooDojo_Admin extends WooDojo_Base {
	public $model;
	public $hook;
	private $whitelist;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		parent::__construct();		
		add_action( 'admin_menu', array( &$this, 'admin_screen_register' ) );
		add_action( 'wp_ajax_woodojo_component_toggle', array( &$this, 'ajax_component_toggle' ) );
		add_action( 'wp_ajax_woodojo_component_display_toggle', array( &$this, 'ajax_component_display_toggle' ) );
		add_action( 'wp_ajax_woodojo_get_closed_components', array( &$this, 'ajax_get_closed_components' ) );

		// Only these models and screens can be loaded.
		$this->whitelist = array( 'main', 'more-information' );
	} // End __construct()
	
	/**
	 * admin_screen_register function.
	 *
	 * @description Register the admin screen and run the necessary procedures.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_screen_register () {
		$hook = add_menu_page( $this->name, $this->name, 'manage_options', $this->token, array( $this, 'admin_screen' ), $this->assets_url . 'images/menu-icon.png' );
		
		add_action( 'load-' . $hook, array( $this, 'admin_page_load' ) );
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( $this, 'admin_menu_order' ) );

		add_action( 'admin_print_styles-' . $hook, array( $this, 'admin_styles' ) );
		add_action( 'admin_print_scripts-' . $hook, array( $this, 'admin_scripts' ) );
		
		// Global styles.
		add_action( 'admin_print_styles', array( $this, 'admin_styles_global' ) );

		do_action( $this->token . '_admin_menu' );
		
		$this->hook = $hook; // Store the hook for later use.
	} // End admin_screen_register()
	
	/**
	 * admin_menu_order function.
	 *
	 * @description Move the menu item to be the second item in the menu.
	 * @access public
	 * @since 1.0.0
	 * @param mixed $menu_order
	 * @return void
	 */
	public function admin_menu_order ( $menu_order ) {
		$new_menu_order = array();
		foreach ( $menu_order as $index => $item ) {
			if ( $item != $this->token )
				$new_menu_order[] = $item;

			if ( $index == 0 )
				$new_menu_order[] = $this->token;
		}
		return $new_menu_order;
	} // End admin_menu_order()
	
	/**
	 * admin_screen function.
	 *
	 * @description Load the main admin screen.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_screen () {
		$screen = 'main';
		if ( isset( $_GET['screen'] ) && ( '' != $_GET['screen'] ) ) {
			$screen = esc_attr( trim( $_GET['screen'] ) );
		}
		
		$filetoken = 'main';
		
		$filetoken = str_replace( ' ', '-', strtolower( $screen ) );
		
		if ( in_array( $filetoken, $this->whitelist ) && file_exists( $this->screens_path . $filetoken . '.php' ) ) {
			require_once( $this->screens_path . $filetoken . '.php' );
		} else {
			return false;
		}
	} // End admin_screen()

	/**
	 * admin_page_load function.
	 *
	 * @description Run when the admin screen loads.
	 * @access public
	 * @since 1.0.0
	 * @uses global $woodojo->settings
	 * @uses global $woodojo->api->refresh()
	 * @return void
	 */
	public function admin_page_load () {
		global $woodojo;

		require_once( 'model.class.php' );
		
		// Get the settings.
		$woodojo->settings = $woodojo->api->get_settings();

		$screen = 'main';
		if ( isset( $_GET['screen'] ) && ( '' != $_GET['screen'] ) ) {
			$screen = esc_attr( trim( $_GET['screen'] ) );
		}
		
		$default = 'WooDojo_Model';
		$filetoken = 'model';
		
		// Force refresh if necessary.
		if ( $screen == 'main' & $woodojo->settings->refresh == true ) {
			$woodojo->api->refresh();
		}

		$filetoken = str_replace( ' ', '-', strtolower( $screen ) );
		$classname = $default . '_' . str_replace( ' ', '', ucwords( str_replace( '-', ' ', $screen ) ) );
		
		if ( ( $default != $classname ) && in_array( $filetoken, $this->whitelist ) && file_exists( $this->models_path . $filetoken . '.class.php' ) ) {
			require_once( $this->models_path . $filetoken . '.class.php' );
		} else {
			return false;
		}
		
		$this->model = new $classname();
		$this->model->admin_page_hook = $this->hook; // Send the admin page hook to the model.		
	} // End admin_page_load()
	
	/**
	 * admin_styles function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_styles () {
		wp_register_style( $this->token . '-admin', $this->assets_url . 'css/admin.css', '', '1.2.4', 'screen' );
		wp_enqueue_style( $this->token . '-admin' );
	} // End admin_styles()
	
	/**
	 * admin_styles_global function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_styles_global () {
		wp_register_style( $this->token . '-global', $this->assets_url . 'css/global.css', '', '1.2.4', 'screen' );
		wp_enqueue_style( $this->token . '-global' );
	} // End admin_styles_global()
	
	/**
	 * admin_scripts function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_scripts () {
		wp_register_script( $this->token . '-admin', $this->assets_url . 'js/admin.js', array( 'jquery' ), '1.2.4', false );
		wp_enqueue_script( $this->token . '-admin' );
		
		$translation_strings = WooDojo_Utils::load_common_l10n();
		
		$ajax_vars = array( $this->token . '_component_toggle_nonce' => wp_create_nonce( $this->token . '_component_toggle_nonce' ) );

		$data = array_merge( $translation_strings, $ajax_vars );

		/* Specify variables to be made available to the admin.js file. */
		wp_localize_script( $this->token . '-admin', $this->token . '_localized_data', $data );
	} // End admin_scripts()
	
	/**
	 * ajax_component_toggle function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_component_toggle () {
		$nonce = $_POST[$this->token . '_component_toggle_nonce'];
		//Add nonce security to the request
		if ( ! wp_verify_nonce( $nonce, $this->token . '_component_toggle_nonce' ) )
			die();
		
		// Make sure our model is available.
		$this->admin_page_load();

		// Component activation.
		if ( isset( $_POST['task'] ) && ( $_POST['task'] == 'activate-component' ) ) {
			echo $this->model->activate_component( trim( esc_attr( $_POST['component'] ) ), trim( esc_attr( $_POST['type'] ) ), false );
		}
		
		// Component deactivation.
		if ( isset( $_POST['task'] ) && ( $_POST['task'] == 'deactivate-component' ) ) {
			echo $this->model->deactivate_component( trim( esc_attr( $_POST['component'] ) ), trim( esc_attr( $_POST['type'] ) ), false );
		}
		
		die(); // WordPress may print out a spurious zero without this can be particularly bad if using JSON
	} // End ajax_component_toggle()
	
	/**
	 * ajax_component_display_toggle function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_component_display_toggle () {
		$nonce = $_POST[$this->token . '_component_toggle_nonce'];
		//Add nonce security to the request
		if ( ! wp_verify_nonce( $nonce, $this->token . '_component_toggle_nonce' ) )
			die();
		
		// Get stored list of closed components.
		$closed = get_option( $this->token . '_closed_components', array() );
		
		$component = (array)$_POST['component'];
		array_map( 'esc_attr', $component );
		array_map( 'trim', $component );
		
		$status = esc_attr( trim( $_POST['status'] ) );
		
		foreach ( $component as $k => $v ) {
			if ( in_array( $v, $closed ) && ( $status == 'open' ) ) {
				foreach ( $closed as $i => $j ) {
					if ( $j == $v ) {
						unset( $closed[$i] );
						break;
					}
				}
			}
			
			if ( ( $status == 'closed' ) && ! in_array( $v, $closed ) ) {
				$closed[] = $v;
			}
		}

		// Update the database.
		echo update_option( $this->token . '_closed_components', $closed );
		
		die(); // WordPress may print out a spurious zero without this can be particularly bad if using JSON
	} // End ajax_component_toggle()
	
	/**
	 * ajax_get_closed_components function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_get_closed_components () {
		$nonce = $_POST[$this->token . '_component_toggle_nonce'];
		//Add nonce security to the request
		if ( ! wp_verify_nonce( $nonce, $this->token . '_component_toggle_nonce' ) )
			die();
		
		// Get stored list of closed components.
		$closed = get_option( $this->token . '_closed_components', array() );
		echo json_encode( $closed );
		die(); // WordPress may print out a spurious zero without this can be particularly bad if using JSON
	} // End ajax_get_closed_components()
}
?>