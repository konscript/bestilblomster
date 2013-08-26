<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo API Class
 *
 * All functionality pertaining to the WooDojo API interactions.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category API
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $api_url
 * var $products_expire_time
 *
 * - __construct()
 * - request()
 * - get_products()
 * - get_products_by_type()
 * - request_remote_file()
 * - get_settings()
 * - refresh()
 */
class WooDojo_API {
	public $token;
	public $api_url;
	private $products_expire_time;
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct ( $token ) {
		$this->token = $token;
		$this->api_url = 'http://www.woothemes.com/woo-dojo-api/';
		$this->products_expire_time = 60 * 60 * 24 * 7; // 1 week.
		$this->settings_expire_time = 60 * 60 * 12; // 12 hours.
	} // End __construct()
	
	/**
	 * request function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param array $params
	 * @uses global $woodojo->base->token
	 * @return array $data
	 */
	public function request ( $params = array() ) {
		global $woodojo;

		$params['woodojo-version'] = $woodojo->version;

		$response = wp_remote_post( $this->api_url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(
				'user-agent'	=> 'WooDojo/' . $woodojo->version,
			),
			'body' => $params,
			'cookies' => array()
		    )
		);

		if( is_wp_error( $response ) ) {
		  $data = new StdClass();
		  $data->response->code = 0;
		  $data->response->message = __( 'WooDojo Request Error', 'woodojo' );
		} else {
			$data = $response['body'];
		}
		
		$data = json_decode( $data );

		delete_transient( $woodojo->base->token . '-request-error' );
		// Store errors in a transient, to be cleared on each request.
		if ( isset( $data->response->code ) && ( $data->response->code == 0 ) ) {
			set_transient( $woodojo->base->token . '-request-error', $data->response->message );
		}
		
		return $data;
	} // End request()
	
	/**
	 * get_products function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @uses $this->request()
	 * @uses global $woodojo->base->token
	 * @return array $products
	 */
	private function get_products () {
		global $woodojo;
		
		$products = array();
		$transient_key = $woodojo->base->token . '-products';
		
		if ( false === ( $products = get_transient( $transient_key ) ) ) {
			$args = array( 'action' => 'getwoodojoproducts' );
			$response = $this->request( $args );
			
			if ( isset( $response->response->code ) && ( $response->response->code == 1 ) && ( isset( $response->payload ) ) ) {
				$products = (array)$response->payload;
				set_transient( $transient_key, $products, $this->products_expire_time );
			}
		}

		return $products;
	} // End get_products()
	
	/**
	 * get_products_by_type function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $type (default: 'bundled')
	 * @return array $response
	 */
	public function get_products_by_type ( $type = 'bundled' ) {
		if ( ! in_array( $type, array( 'standalone', 'downloadable', 'bundled' ) ) ) { return array(); }
		
		$response = array();
		$products = $this->get_products();
		
		if ( count( (array)$products ) > 0 ) {
			foreach ( (array)$products as $k => $v ) {
				if ( isset( $v->type ) && ( $v->type == $type ) ) {
					$slug = $v->slug;
					
					$filepath = $slug . '/' . $slug . '.php';
					$v->filepath = $filepath;
					$response[$slug] = $v;
				}
			}
		}

		return $response;
	} // End get_products_by_type()
	
	/**
	 * request_remote_file function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param int $id
	 * @uses global $woodojo->base->token
	 * @return string $path
	 */
	public function request_remote_file ( $id ) {
		global $woodojo;

		$path = '';
		
		$args = array( 'action' => 'getwoodojodownload', 'product_id' => intval( $id ) );
		$args['token'] = get_transient( $woodojo->base->token . '-token' );
		$args['secret'] = get_transient( $woodojo->base->token . '-secret' );
	
		$response = $this->request( $args );

		// Check if the download is allowed or if there was an error.
		if ( isset( $response->response->code ) && ( $response->response->code == 1 ) && isset( $response->payload->download_url ) ) {
			$path = esc_url( $response->payload->download_url );
		}

		return $path;
	} // End request_remote_file()

	/**
	 * get_settings function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @uses $this->request()
	 * @return array $settings
	 */
	public function get_settings () {
		$settings = array();
		$transient_key = $this->token . '-settings';

		if ( false === ( $settings = get_transient( $transient_key ) ) ) {

			$args = array( 'action' => 'woodojosettings' );
			$response = $this->request( $args );

			if ( ( $response->response->code == 1 ) && ( isset( $response->payload ) ) ) {
				$settings = $response->payload;

				set_transient( $transient_key, $settings, $this->settings_expire_time );
			}
		}

		return $settings;
	} // End get_settings()

	/**
	 * refresh function.
	 *
	 * @description Refresh everything (product data).
	 * @access public
	 * @since 1.0.0
	 * @uses global $woodojo->base->token
	 * @return boolean $is_refreshed
	 */
	public function refresh () {
		global $woodojo;

		$is_refreshed = false;

		delete_transient( $woodojo->base->token . '-products' );

		$this->get_products();

		$settings = $this->get_settings();

		$settings->refresh = 0;
		$is_refreshed = set_transient( $woodojo->base->token . '-settings', $settings, $this->settings_expire_time );

		return $is_refreshed;
	} // End refresh()
}
?>