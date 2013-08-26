<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo Updater Class
 *
 * The WooDojo automatic updater class.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $file
 *
 * - __construct()
 * - update_check()
 * - plugin_information()
 * - request()
 */
class WooDojo_Updater {
	public $file;
	private $api_url;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct ( $file ) {
		global $woodojo;

		$this->api_url = 'http://www.woothemes.com/woo-dojo-api/';
		$this->file = plugin_basename( $file );

		// Check For Updates
		add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'update_check' ) );

		// Check For Plugin Information
		add_filter( 'plugins_api', array( &$this, 'plugin_information' ), 10, 3 );
	} // End __construct()

	/**
	 * update_check function.
	 * 
	 * @access public
	 * @param object $transient
	 * @return object $transient
	 */
	public function update_check ( $transient ) {
	    // Check if the transient contains the 'checked' information
	    // If no, just return its value without hacking it
	    if( empty( $transient->checked ) )
	        return $transient;
	    
	    // The transient contains the 'checked' information
	    // Now append to it information form your own API
	    $args = array(
	        'action' => 'pluginupdatecheck',
	        'plugin_name' => $this->file,
	        'version' => $transient->checked[$this->file]
	    );

	    // Send request checking for an update
	    $response = $this->request( $args );

	    // If response is false, don't alter the transient
	    if( false !== $response ) {
	        $transient->response[$this->file] = $response;
	    }
	    return $transient;
	} // End update_check()
	
	/**
	 * plugin_information function.
	 * 
	 * @access public
	 * @return object $response
	 */
	public function plugin_information ( $false, $action, $args ) {	
		$transient = get_site_transient( 'update_plugins' );

		// Check if this plugins API is about this plugin
		if( $args->slug != dirname( $this->file ) ) {
			return $false;
		}

		// POST data to send to your API
		$args = array(
			'action' => 'plugininformation',
			'plugin_name' => $this->file, 
			'version' => $transient->checked[$this->file]
		);
		
		// Send request for detailed information
		$response = $this->request( $args );

		$response->sections = (array)$response->sections;
		$response->compatibility = (array)$response->compatibility;
		$response->tags = (array)$response->tags;
		$response->contributors = (array)$response->contributors;

		if ( count( $response->compatibility ) > 0 ) {
			foreach ( $response->compatibility as $k => $v ) {
				$response->compatibility[$k] = (array)$v;
			}
		}

		return $response;
	} // End plugin_information()

	/**
	 * request function.
	 * 
	 * @access public
	 * @param array $args
	 * @return object $response or boolean false
	 */
	public function request ( $args ) {
	    // Send request
	    $request = wp_remote_post( $this->api_url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => $args,
			'cookies' => array(), 
			'sslverify' => false
		    ) );

	    // Make sure the request was successful
	    if( is_wp_error( $request ) or wp_remote_retrieve_response_code( $request ) != 200 ) {
	        // Request failed
	        return false;
	    }
	    
	    // Read server response, which should be an object
	    if ( $request != '' ) {
	    	$response = json_decode( wp_remote_retrieve_body( $request ) );
	    } else {
	    	$response = false;
	    }

	    if( is_object( $response ) && isset( $response->payload ) ) {
	        return $response->payload;
	    } else {
	        // Unexpected response
	        return false;
	    }
	} // End prepare_request()
} // End Class
?>