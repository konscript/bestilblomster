<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo - ShortLinks
 *
 * Base class for the WooDojo - ShortLinks feature.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Bundled
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 * 
 * var $service
 * var $token
 * var $settings_screen
 * 
 * - __construct()
 * - load_settings_screen()
 * - short_url_filter()
 * - shorten_url()
 * - shorten_url_tinyurl()
 * - shorten_url_bitly()
 */
class WooDojo_ShortLinks {
	
	/* Variable Declarations */
	var $service;
	var $token;
	var $settings_screen;
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct () {		
		/* Class Settings */
		$this->token = 'woodojo-shortlinks';
		$this->service = get_option( 'woodojo_shortlinks_service' );
		if( $this->service == '' ) $this->service = 'tinyurl';	
		if ( is_admin() ) {
	    	$this->name		= __( 'ShortLinks', 'woodojo' );
	    	$this->menu_label	= __( 'ShortLinks', 'woodojo' );
	    	$this->page_slug	= 'shortlinks';
	    }
		/* Settings Screen */
		$this->load_settings_screen();
		
		/* Filter the WP shortlink */
		add_filter('pre_get_shortlink', array( &$this, 'short_url_filter' ), 100, 3);
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
		$this->settings_screen = new WooDojo_ShortLinks_Settings();
		
		/* Setup shortlinks data */
		$this->settings_screen->token = $this->token;
		if ( is_admin() ) {
			$this->settings_screen->name = __( 'WooDojo ShortLinks', 'woodojo' );
			$this->settings_screen->menu_label = __( 'ShortLinks', 'woodojo' );
			$this->settings_screen->page_slug = 'woodojo-shortlinks-settings';
		}
		$this->settings_screen->setup_settings();
	} // End load_settings_screen()

	/**
	 * woodojo_short_url_register function.
	 * 
	 * @access public
	 * @param integer $id
	 * @param string $context
	 * @param array $allow_slugs
	 * @since 1.0.0
	 * @return void
	 */
	public function short_url_filter ($id,$context,$allow_slugs) {
		global $post, $pagenow;

		/* Don't generate shortlink when adding a new post */
		if ( is_admin() && $pagenow != 'post-new.php' ) {
	   		return $this->shorten_url($post->ID);
	   	} elseif ( $pagenow != 'post-new.php' ) {
	   		return $this->shorten_url($post->ID);
	   	} else return false;
	} // End short_url_filter()
	 
	/**
	 * shorten_url function.
	 * 
	 * @access public
	 * @param integer $post_id
	 * @since 1.0.0
	 * @return void
	 */
	public function shorten_url( $post_id = 0 ) {
		/* Setup service to use */
		$settings = $this->settings_screen->get_settings();
		$service = '';
		if ( isset($settings['service']) && $settings['service'] != '' ) {
			$service = $settings['service'];
		}
		if($service == '') $service = 'bitly';
	 	$function = 'shorten_url_'.$service;
	 	
	 	/* Generates short url depending on the service and adds post meta */
	 	if( function_exists( 'woodojo_'.$function ) || method_exists($this,$function ) ) {
	 		$saved = get_post_meta( $post_id, '_' . $function, true );
	 		if( $saved == '' ) {
	 			if( function_exists( 'woodojo_' . $function ) ) {
		 			$url = 'woodojo_' . $function( get_permalink( $post_id ) );
		 		} else {
		 			$url = $this->$function( get_permalink( $post_id ) );
		 		}
		 		add_post_meta( $post_id, '_' . $function, $url, true );
		 		return $url;
			}
	 		else return $saved;
	 	}
	 	else return false;
	} // End shorten_url()
	
	/**
	 * shorten_url_tinyurl function.
	 * 
	 * @access public
	 * @param string $url
	 * @since 1.0.0
	 * @return void
	 */
	public function shorten_url_tinyurl ( $url ) {
		$url = esc_url( 'http://tinyurl.com/api-create.php?url=' . urlencode( $url ) );
		$data = wp_remote_get( $url );
	    $body = $data['body'];
	    if($body == 'Error') {
	    	return false;
	    } else return $body;
	} // End shorten_url_tinyurl()
	
	/**
	 * shorten_url_bitly function.
	 * 
	 * @access public
	 * @param string $url
	 * @since 1.0.0
	 * @return void
	 */
	public function shorten_url_bitly ( $url ) {
		/* Setup Bitly API data */
	    $settings = $this->settings_screen->get_settings();
	    $login = '';
	    if ( isset($settings['bitly_login']) && $settings['bitly_login'] != '' ) {
	    	$login = esc_attr( $settings['bitly_login'] );
	    }
	    $api_key = '';
	    if ( isset($settings['bitly_api_key']) && $settings['bitly_api_key'] != '' ) {
	    	$api_key = esc_attr( $settings['bitly_api_key'] );
	    }
	    
	    if ( ( $login != '' ) && ( $api_key != '' ) ) {
	    	$url = 'http://api.bitly.com/v3/shorten/?login=' . urlencode( $login ) . '&apikey=' . urlencode( $api_key ) . '&format=json&longUrl=' . esc_url( $url );

	    	$json = wp_remote_get( $url );
	    	$data = json_decode( $json['body'] );
	    	if( $data->status_code == 200 ) {
	    		return $data->data->url;
	    	} else return false;
	    } else return false;
	} // End shorten_url_bitly()
} // End Class
?>