<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo Main Model
 *
 * The base Model for WooDojo.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Administration
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * private $components
 * var $sections
 * var $closed_components
 * var $admin_page_hook ( sent from the main admin class )
 * var $current_action_response
 *
 * - __construct()
 * - component_actions()
 * - admin_notices()
 * - get_section_links()
 * - get_closed_components()
 * - add_contextual_help()
 * - get_action_button()
 * - get_enable_button()
 * - get_disable_button()
 * - get_download_button()
 * - get_upgrade_link_url()
 */
class WooDojo_Model_Main extends WooDojo_Model {
	var $components;
	var $current_action_response;

	public function __construct() {
		parent::__construct();

		$this->products = array();

		$this->components = array(
								'bundled' => array(), 
								'downloadable' => array(), 
								'standalone' => array()
								);

		$this->sections = array(
								'bundled' => array(
														'name' => __( 'Bundled Features', 'woodojo' ), 
														'description' => __( 'Features bundled with WooDojo.', 'woodojo' )
													), 
								'downloadable' => array(
														'name' => __( 'Downloadable Features', 'woodojo' ), 
														'description' => __( 'Downloadable features to enhance your website.', 'woodojo' )
													), 
								'standalone' => array(
														'name' => __( 'WordPress Plugins', 'woodojo' ), 
														'description' => __( 'Plugins developed by WooThemes.', 'woodojo' )
													)
								);
			
		$this->closed_components = array();
						
		$this->load_components();

		$this->get_closed_components();

		$this->current_action_response = $this->component_actions();

		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		add_action( 'admin_head', array( $this, 'add_contextual_help' ) );	
	} // End __construct()
	
	/**
	 * component_actions function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function component_actions () {
		$response = false;

		// Component activation.
		if ( ( isset( $_GET['activate-component'] ) || isset( $_POST['activate-component'] ) ) && ( WooDojo_Utils::get_or_post( 'activate-component' ) != '' ) ) {
			$response = $this->activate_component( trim( esc_attr( WooDojo_Utils::get_or_post( 'activate-component' ) ) ), trim( esc_attr( WooDojo_Utils::get_or_post( 'component-type' ) ) ) );
		}
		
		// Component deactivation.
		if ( ( isset( $_GET['deactivate-component'] ) || isset( $_POST['deactivate-component'] ) ) && ( WooDojo_Utils::get_or_post( 'deactivate-component' ) != '' ) ) {
			$response = $this->deactivate_component( trim( esc_attr( WooDojo_Utils::get_or_post( 'deactivate-component' ) ) ), trim( esc_attr( WooDojo_Utils::get_or_post( 'component-type' ) ) ) );
		}
		
		// Component download.
		if ( ( isset( $_GET['download-component'] ) || isset( $_POST['download-component'] ) ) && ( WooDojo_Utils::get_or_post( 'download-component' ) != '' ) ) {
			$response = $this->download_component( trim( esc_attr( WooDojo_Utils::get_or_post( 'download-component' ) ) ), trim( esc_attr( WooDojo_Utils::get_or_post( 'component-type' ) ) ) );
		}

		// Component upgrade.
		if ( ( isset( $_GET['upgrade-component'] ) || isset( $_POST['upgrade-component'] ) ) && ( WooDojo_Utils::get_or_post( 'upgrade-component' ) != '' ) ) {
			$response = $this->upgrade_component( trim( esc_attr( WooDojo_Utils::get_or_post( 'upgrade-component' ) ) ), trim( esc_attr( WooDojo_Utils::get_or_post( 'component-type' ) ) ) );
		}

		return $response;
	} // End component_actions()

	/**
	 * admin_notices function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_notices () {
		$notice = '';

		// Successful Activation.
		if ( isset( $_GET['activated-component'] ) && ( $_GET['activated-component'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['activated-component']]->title;
			$notice = '<div id="message" class="success updated fade"><p>' . sprintf( __( '%s activated successfully.', 'woodojo' ), $name ) . '</p></div>' . "\n";
		}
		
		// Unsuccessful Activation.
		if ( isset( $_GET['activation-error'] ) && ( $_GET['activation-error'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['activation-error']]->title;
			$notice = '<div id="message" class="error"><p>' . sprintf( __( 'There was an error activating %s. Please try again.', 'woodojo' ), $name ) . '</p></div>' . "\n";
		}
		
		// Successful Deactivation.
		if ( isset( $_GET['deactivated-component'] ) && ( $_GET['deactivated-component'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['deactivated-component']]->title;
			$notice = '<div id="message" class="success updated fade"><p>' . sprintf( __( '%s deactivated successfully.', 'woodojo' ), $name ) . '</p></div>' . "\n";
		}
		
		// Unsuccessful Dectivation.
		if ( isset( $_GET['deactivation-error'] ) && ( $_GET['deactivation-error'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['deactivation-error']]->title;
			$notice = '<div id="message" class="error"><p>' . sprintf( __( 'There was an error deactivating %s. Please try again.', 'woodojo' ), $name ) . '</p></div>' . "\n";
		}
		
		// Successful Download.
		if ( isset( $_GET['downloaded-component'] ) && ( $_GET['downloaded-component'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['downloaded-component']]->title;
			$notice = '<div id="message" class="success updated fade">' . "\n";
			$notice .= '<p>' . sprintf( __( '%s downloaded successfully.', 'woodojo' ), $name ) . '</p>' . "\n";
			$notice .= '</div>' . "\n";
		}
		
		// Unsuccessful Download.
		if ( isset( $_GET['download-error'] ) && ( $_GET['download-error'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['download-error']]->title;
			$notice = '<div id="message" class="error">' . "\n";
			$notice .= '<p>' . sprintf( __( 'There was an error downloading %s. Please try again.', 'woodojo' ), $name ) . '</p>' . "\n";
			$notice .= $this->get_request_error();
			$notice .= '</div>' . "\n";
		}

		// Successful upgrade.
		if ( isset( $_GET['upgraded-component'] ) && ( $_GET['upgraded-component'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['upgraded-component']]->title;
			$notice = '<div id="message" class="success updated fade">' . "\n";
			$notice .= '<p>' . sprintf( __( '%s upgraded successfully.', 'woodojo' ), $name ) . '</p>' . "\n";
			$notice .= '</div>' . "\n";
		}
		
		// Unsuccessful upgrade.
		if ( isset( $_GET['upgrade-error'] ) && ( $_GET['upgrade-error'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['upgrade-error']]->title;
			$notice = '<div id="message" class="error">' . "\n";
			$notice .= '<p>' . sprintf( __( 'There was an error upgrading %s. Please try again.', 'woodojo' ), $name ) . '</p>' . "\n";
			$notice .= $this->get_request_error();
			$notice .= '</div>' . "\n";
		}
		
		echo $notice;
	} // End admin_notices()

	/**
	 * get_section_links function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function get_section_links () {
		$html = '';
		
		$total = 0;
		
		$sections = array(
						'all' => array( 'href' => '#all', 'name' => __( 'All', 'woodojo' ), 'class' => 'current all tab' )
					);
					
		foreach ( $this->sections as $k => $v ) {
			$total += count( $this->components[$k] );
			$sections[$k] = array( 'href' => '#' . esc_attr( $this->config->token . '-' . $k ), 'name' => $v['name'], 'class' => 'tab', 'count' => count( $this->components[$k] ) );
		}
		
		$sections['all']['count'] = $total;
		
		$sections = apply_filters( $this->config->token . '_main_section_links_array', $sections );
		
		$has_upgrades = 0;
		if ( isset( $this->components['downloadable'] ) ) {
			foreach ( $this->components['downloadable'] as $k => $v ) {
				if ( isset( $v->has_upgrade ) && $v->has_upgrade == true ) {
					$has_upgrades++;
				}
			}
		}

		if ( $has_upgrades > 0 ) {
			$sections['has-upgrade'] = array( 'href' => '#has-upgrade', 'name' => __( 'Updates Available', 'woodojo' ), 'class' => 'has-upgrade tab', 'count' => $has_upgrades );
		}

		$count = 1;
		foreach ( $sections as $k => $v ) {
			$count++;
			if ( $v['count'] > 0 ) {
				$html .= '<li><a href="' . $v['href'] . '"';
				if ( isset( $v['class'] ) && ( $v['class'] != '' ) ) { $html .= ' class="' . esc_attr( $v['class'] ) . '"'; }
				$html .= '>' . $v['name'] . '</a>';
				$html .= ' <span>(' . $v['count'] . ')</span>';
				if ( $count <= count( $sections ) ) { $html .= ' | '; }
				$html .= '</li>' . "\n";
			}
		}
		
		echo $html;
		
		do_action( $this->config->token . '_main_get_section_links' );
	} // End get_section_links()
	
	/**
	 * get_closed_components function.
	 *
	 * @description Return an array of the tokens of components that are closed.
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function get_closed_components () {
		$this->closed_components = get_option( $this->config->token . '_closed_components', array() );
	} // End get_closed_components()

	/**
	 * add_contextual_help function.
	 *
	 * @description Add contextual help to the current screen.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function add_contextual_help () {
		get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __( 'Overview', 'woodojo' ),
		'content'	=>
			'<p>' . __( 'This screen provides an overview of all features available through WooDojo. Features are also enabled, disabled and downloaded here.', 'woodojo' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
		'id'		=> 'bundled-features',
		'title'		=> __( 'Bundled Features', 'woodojo' ),
		'content'	=>
			'<p>' . __( 'Bundled features are immediately available and come packaged with WooDojo.', 'woodojo' ) . '</p>' .
			'<ul>' .
				'<li>' . __( 'Enabling or disabling a bundled feature can be done instantly. Simply click the "Enable" or "Disable" button for the desired feature under the "Bundled Features" section.', 'woodojo' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
		'id'		=> 'downloadable-features',
		'title'		=> __( 'Downloadable Features', 'woodojo' ),
		'content'	=>
			'<p>' . __( 'Downloadable features are downloaded from WooThemes.com and made available within WooDojo.', 'woodojo' ) . '</p>' .
			'<p>' . sprintf( __( 'Downloading features requires a freely available WooThemes.com account. If you don\'t have one, you can sign up for a WooThemes.com account %shere%s.', 'woodojo' ), '<a href="' . esc_url( admin_url( 'admin.php?page=' . $this->config->token . '&screen=register' ) ) . '">', '</a>' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
		'id'		=> 'wordpress-plugins',
		'title'		=> __( 'WordPress Plugins', 'woodojo' ),
		'content'	=>
			'<p>' . __( 'These are our standalone WordPress plugins, available directly through WooDojo.', 'woodojo' ) . '</p>' .
			'<p>' . __( 'Our existing WordPress plugins include the popular WooCommerce and the new WooSidebars plugin.', 'woodojo' ) . '</p>' .
			'<ul>' .
				'<li>' . __( 'Once a WordPress plugin is downloaded and enabled, it acts the same as any other WordPress plugin and can be activated or deactivated, either from within WooDojo or from the "Plugins" screen.', 'woodojo' ) . '</li>' .
			'</ul>'
		) );

		get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'woodojo' ) . '</strong></p>' .
		'<p><a href="http://support.woothemes.com/?ref=' . $this->config->token . '" target="_blank">' . __( 'Support Desk', 'woodojo' ) . '</a></p>' . 
		'<p><a href="http://dojodocs.woothemes.com/?ref=' . $this->config->token . '" target="_blank">' . __( 'WooDojo Documentation', 'woodojo' ) . '</a></p>'
		);
	} // End add_contextual_help()
	
	/**
	 * get_action_button function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @return string $button
	 */
	public function get_action_button ( $component, $type ) {
		$i = $component;
		$k = $type;

		$button = '';
		
		if ( ( $type == 'downloadable' || $type == 'standalone' ) && ( ! $this->is_downloaded_component( $component, $type ) ) ) {
			$button = $this->get_download_button( $component, $type );
		} else {
			if ( $this->is_active_component( $component, $type ) ) {
				$button = $this->get_disable_button( $component, $type );
			} else {
				$button = $this->get_enable_button( $component, $type );
			}
		}
		
		return $button;
	} // End get_action_button()
	
	/**
	 * get_enable_button function.
	 * 
	 * @access private
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @return string $html
	 */
	private function get_enable_button ( $component, $type ) {
		$id = $this->components[$type][$component]->product_id;

		$html = '';
		$html .= '<input type="submit" id="button-component-' . $component . '-activate" class="button-primary component-control-save enable" value="' . esc_attr__( 'Activate', 'woodojo' ) . '" />' . "\n";
		$html .= '<input type="hidden" name="activate-component" id="component-' . $component . '-activate" value="' .  esc_attr( $component ) . '" />' . "\n";
		$html .= '<input type="hidden" name="component" value="' . esc_attr( $component ) . '" />' . "\n";
		$html .= '<input type="hidden" name="component_id" value="' . esc_attr( $id ) . '" />' . "\n";
		return $html;
	} // End get_enable_button()
	
	/**
	 * get_disable_button function.
	 * 
	 * @access private
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @return string $html
	 */
	private function get_disable_button ( $component, $type ) {
		$id = $this->components[$type][$component]->product_id;

		$html = '';
		$html .= '<input type="submit" id="button-component-' . $component . '-deactivate" class="button-primary component-control-save disable" value="' . esc_attr__( 'Deactivate', 'woodojo' ) . '" />' . "\n";
		$html .= '<input type="hidden" name="deactivate-component" id="component-' . $component . '-deactivate" value="' . esc_attr( $component ) . '" />' . "\n";
		$html .= '<input type="hidden" name="component" value="' . esc_attr( $component ) . '" />' . "\n";
		$html .= '<input type="hidden" name="component_id" value="' . esc_attr( $id ) . '" />' . "\n";
		return $html;
	} // End get_disable_button()
	
	/**
	 * get_download_button function.
	 * 
	 * @access private
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @return string $html
	 */
	private function get_download_button ( $component, $type ) {
		$id = $this->components[$type][$component]->product_id;

		$html = '';
		$html .= '<input type="submit" id="button-component-' . $component . '-download" class="button-primary component-control-save download" value="' . esc_attr__( 'Download & Activate', 'woodojo' ) . '" />' . "\n";
		$html .= '<input type="hidden" name="download-component" id="component-' . $component . '-download" value="' . esc_attr( $component ) . '" />' . "\n";
		$html .= '<input type="hidden" name="component" value="' . esc_attr( $component ) . '" />' . "\n";
		$html .= '<input type="hidden" name="component_id" value="' . esc_attr( $id ) . '" />' . "\n";
		return $html;
	} // End get_download_button()
	
	/**
	 * get_upgrade_link_url function.
	 * 
	 * @access private
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @return string $html
	 */
	public function get_upgrade_link_url ( $component, $type ) {
		$id = $this->components[$type][$component]->product_id;
		$html = admin_url( 'admin.php?page=' . $this->config->token . '&upgrade-component=' . urlencode( $component ) . '&component=' . urlencode( $component ) . '&component-type=' . $type . '&component_id=' . $id );
		return $html;
	} // End get_upgrade_link_url()
} // End Class
?>