<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo Instagram Widget
 *
 * A bundled WooDojo Instagram stream widget.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Bundled
 * @author WooThemes
 * @since 1.1.0
 *
 * TABLE OF CONTENTS
 *
 * var $woo_widget_cssclass
 * var $woo_widget_description
 * var $woo_widget_idbase
 * var $woo_widget_title
 * 
 * var $transient_expire_time
 * private $client_id
 * private $client_secret
 * private $api_url
 * 
 * - __construct()
 * - widget()
 * - update()
 * - form()
 * - get_stored_data()
 * - request_recent_photos()
 * - get_stored_profile_data()
 * - request_profile_data()
 * - request()
 * - get_access_token()
 * - enqueue_styles()
 * - enqueue_scripts()
 * - prepare_photos_html()
 * - generate_profile_box()
 * - determine_image_by_size()
 * - get_checkbox_settings()
 */
class WooDojo_Widget_Instagram extends WP_Widget {

	/* Variable Declarations */
	protected $woo_widget_cssclass;
	protected $woo_widget_description;
	protected $woo_widget_idbase;
	protected $woo_widget_title;

	protected $transient_expire_time;
	private $client_id;
	private $client_secret;
	private $api_url = 'https://api.instagram.com/';

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @uses WooDojo
	 * @return void
	 */
	public function __construct () {
		global $woodojo;

		/* Widget variable settings. */
		$this->woo_widget_cssclass = 'widget_woodojo_instagram';
		$this->woo_widget_description = __( 'This is a WooDojo bundled Instagram stream widget.', 'woodojo' );
		$this->woo_widget_idbase = 'woodojo_instagram';
		$this->woo_widget_title = __('WooDojo - Instagram', 'woodojo' );
		
		$this->transient_expire_time = 60 * 60 * 24; // 1 day.
		$this->client_id = '79a1ad0924854bad93558757ff86c7f7';
		$this->client_secret = '2feefd865b5643909395d81135af7840';

		/* Setup the assets URL in relation to WooDojo. */
		$this->assets_url = trailingslashit( $woodojo->base->components_url . 'woodojo-social-widgets/assets' );

		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

		/* Widget control settings. */
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => $this->woo_widget_idbase );

		/* Create the widget. */
		$this->WP_Widget( $this->woo_widget_idbase, $this->woo_widget_title, $widget_ops, $control_ops );

		/* Load in assets for the widget. */
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
	} // End Constructor

	/**
	 * widget function.
	 * 
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {
		// Twitter handle is required.
		if ( ! isset( $instance['access_token'] ) || ( $instance['access_token'] == '' ) ) { return; }

		extract( $args, EXTR_SKIP );
		
		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base );
		$limit = $instance['limit'];
		if ( intval( $limit ) <= 0 ) { $limit = 5; }

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) {
		
			echo $before_title . $title . $after_title;
		
		} // End IF Statement
		
		/* Widget content. */
		
		// Add actions for plugins/themes to hook onto.
		do_action( $this->woo_widget_cssclass . '_top' );
		
		// Load widget content here.
		$html = '';
		
		$args = array(
					'access_token' => $instance['access_token'], 
					'count' => $limit
					);

		$data = $this->get_stored_data( $args );

		$photos_html = $this->prepare_photos_html( $data, $instance );

		if ( $photos_html != '' ) {
			$html .= $photos_html;
		}

		echo $html; // If using the $html variable to store the output, you need this. ;)

		// Add actions for plugins/themes to hook onto.
		do_action( $this->woo_widget_cssclass . '_bottom' );

		/* After widget (defined by themes). */
		echo $after_widget;

	} // End widget()

	/**
	 * update function.
	 * 
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array $instance
	 */
	public function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		if ( isset( $new_instance['username'] ) && isset( $new_instance['password'] ) && ( $new_instance['username'] != '' ) && ( $new_instance['password'] != '' ) ) {
			// Authenticate and store access token,
			$response_data = $this->get_access_token( $new_instance['username'], $new_instance['password'] );
			if ( is_object( $response_data ) && isset( $response_data->access_token ) ) {
				$instance['access_token'] = $response_data->access_token;
				$instance['profile_data'] = $response_data->user;
			}
		} else {

			/* Save the text inputs. */
			$instance['limit'] = intval( $new_instance['limit'] );
			if ( $instance['limit'] <= 0 ) { $instance['limit'] = 5; }

			$instance['custom_image_size'] = intval( $new_instance['custom_image_size'] );
			if ( $instance['custom_image_size'] <= 0 ) {
				$instance['custom_image_size'] = '';
			}

			/* The select box is returning a text value, so we escape it. */
			$instance['image_size'] = esc_attr( $new_instance['image_size'] );
			$instance['float'] = esc_attr( $new_instance['float'] );

			/* The checkbox is returning a Boolean (true/false), so we check for that. */
			$instance['logout'] = (bool) esc_attr( $new_instance['logout'] );

			$checkboxes = array_keys( $this->get_checkbox_settings() );

			/* The checkbox is returning a Boolean (true/false), so we check for that. */
			foreach ( $checkboxes as $k => $v ) {
				$instance[$v] = (bool) esc_attr( $new_instance[$v] );
			}

			if ( $instance['logout'] == true ) {
				$instance['access_token'] = '';
				$instance['profile_data'] = '';

				// Clear the transient, forcing an update on next frontend page load.
				delete_transient( $this->id . '-recent-photos' );

				$instance['logout'] = false;
			}
		}

		/* Strip tags for the username, and sanitize it as if it were a WordPress username. */
		$instance['username'] = strip_tags( sanitize_user( $new_instance['username'] ) );
		
		// Allow child themes/plugins to act here.
		$instance = apply_filters( $this->woo_widget_idbase . '_widget_save', $instance, $new_instance, $this );
		
		if ( $new_instance['limit'] != $old_instance['limit'] ) {
			// Clear the transient, forcing an update on next frontend page load.
			delete_transient( $this->id . '-recent-photos' );
		}

		return $instance;
	} // End update()

   /**
    * form function.
    * 
    * @access public
    * @param array $instance
    * @return void
    */
    public function form ( $instance ) {
		/* Set up some default widget settings. */
		/* Make sure all keys are added here, even with empty string values. */
		$defaults = array(
						'title' => __( 'Instagram', 'woodojo' ), 
						'access_token' => '', 
						'username' => '', 
						'password' => '', 
						'limit' => 5, 
						'float' => 'left', 
						'image_size' => 'thumbnail', 
						'custom_image_size' => 75, 
						'logout' => 0, 
						'link_to_fullsize' => 1, 
						'enable_thickbox' => 1
					);
		
		// Allow child themes/plugins to filter here.
		$defaults = apply_filters( $this->woo_widget_idbase . '_widget_defaults', $defaults, $this );
		
		$instance = wp_parse_args( (array) $instance, $defaults );

		$checkboxes = $this->get_checkbox_settings();
?>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (optional):', 'woodojo' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>"  value="<?php echo $instance['title']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" />
		</p>
<?php
	if ( $instance['access_token'] == '' ) {
?>
		<!-- Widget Username: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e( 'Username (required):', 'woodojo' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'username' ); ?>"  value="<?php echo $instance['username']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" />
		</p>
		<!-- Widget Password: Password Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'password' ); ?>"><?php _e( 'Password (required):', 'woodojo' ); ?></label>
			<input type="password" name="<?php echo $this->get_field_name( 'password' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'password' ); ?>" />
		</p>
		<!-- Widget <?php echo $v; ?>: Checkbox Input -->
		<p>
	<?php foreach ( $checkboxes as $k => $v ) { ?>
		<input id="<?php echo $this->get_field_id( $k ); ?>" name="<?php echo $this->get_field_name( $k ); ?>" type="hidden" value="<?php echo esc_attr( $instance[$k] ); ?>" />
	<?php } ?>
		<input id="<?php echo $this->get_field_id( 'custom_image_size' ); ?>" name="<?php echo $this->get_field_name( 'custom_image_size' ); ?>" type="hidden" value="<?php echo esc_attr( $instance['custom_image_size'] ); ?>" />
		<input id="<?php echo $this->get_field_id( 'float' ); ?>" name="<?php echo $this->get_field_name( 'float' ); ?>" type="hidden" value="<?php echo esc_attr( $instance['float'] ); ?>" />
		</p>
<?php
	} else {
?>
	<!-- Widget Limit: Text Input -->
	<p>
		<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit (default: 5):', 'woodojo' ); ?></label>
		<input type="text" name="<?php echo $this->get_field_name( 'limit' ); ?>"  value="<?php echo $instance['limit']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" />
	</p>
	<!-- Widget Float: Select Input -->
	<p>
		<label for="<?php echo $this->get_field_id( 'float' ); ?>"><?php _e( 'Float Images:', 'woodojo' ); ?></label>
		<select name="<?php echo $this->get_field_name( 'float' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'float' ); ?>">
			<option value="none"<?php selected( $instance['float'], 'none' ); ?>><?php _e( 'None', 'woodojo' ); ?></option>
			<option value="left"<?php selected( $instance['float'], 'left' ); ?>><?php _e( 'Left', 'woodojo' ); ?></option>
			<option value="right"<?php selected( $instance['float'], 'right' ); ?>><?php _e( 'Right', 'woodojo' ); ?></option>         
		</select>
	</p>
	<!-- Widget Image Size: Select Input -->
	<p>
		<label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e( 'Image Size:', 'woodojo' ); ?></label>
		<select name="<?php echo $this->get_field_name( 'image_size' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'image_size' ); ?>">
			<option value="thumbnail"<?php selected( $instance['image_size'], 'thumbnail' ); ?>><?php _e( 'Thumbnail (150x150)', 'woodojo' ); ?></option>
			<option value="low_resolution"<?php selected( $instance['image_size'], 'low_resolution' ); ?>><?php _e( 'Low Resolution (306x306)', 'woodojo' ); ?></option>
			<option value="standard_resolution"<?php selected( $instance['image_size'], 'standard_resolution' ); ?>><?php _e( 'Standard Resolution (612x612)', 'woodojo' ); ?></option>         
		</select>
	</p>
	<!-- Widget Custom Image Size: Text Input -->
	<p>
		<label for="<?php echo $this->get_field_id( 'custom_image_size' ); ?>"><?php _e( 'Custom Image Size (max: 612):', 'woodojo' ); ?></label>
		<input type="text" name="<?php echo $this->get_field_name( 'custom_image_size' ); ?>"  value="<?php echo $instance['custom_image_size']; ?>" class="widefat" style="width: 30%;" maxlength="3" id="<?php echo $this->get_field_id( 'custom_image_size' ); ?>" /> <?php _e( 'px', 'woodojo' ); ?>
	</p>
	<?php foreach ( $checkboxes as $k => $v ) { ?>
	<!-- Widget <?php echo $v; ?>: Checkbox Input -->
	<p>
		<input id="<?php echo $this->get_field_id( $k ); ?>" name="<?php echo $this->get_field_name( $k ); ?>" type="checkbox"<?php checked( $instance[$k], 1 ); ?> />
    	<label for="<?php echo $this->get_field_id( $k ); ?>"><?php echo $v; ?></label>
	</p>
	<?php
		if ( $k == 'enable_thickbox' ) {
			echo '<p><small>(' . __( 'Requires the "Link to full size image" option', 'woodojo' ) . ')</small></p>' . "\n";
		}
	?>
	<?php } ?>
	<hr />
	<p><?php printf( __( 'Currently logged in as %s.', 'woodojo' ), '<strong>' . $instance['username'] . '</strong>' ); ?></p>
	<input type="hidden" name="<?php echo $this->get_field_name( 'username' ); ?>"  value="<?php echo $instance['username']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" />
	<!-- Widget Logout: Checkbox Input -->
	<p>
		<input id="<?php echo $this->get_field_id( 'logout' ); ?>" name="<?php echo $this->get_field_name( 'logout' ); ?>" type="checkbox"<?php checked( $instance['logout'], 1 ); ?> />
    	<label for="<?php echo $this->get_field_id( 'logout' ); ?>"><?php _e( 'Logout?', 'woodojo' ); ?></label>
	</p>
<?php
	} // End IF Statement
		
		// Allow child themes/plugins to act here.
		do_action( $this->woo_widget_idbase . '_widget_settings', $instance, $this );

	} // End form()
	
	/**
	 * Retrieve stored data, or query for new data.
	 * @param  array $args
	 * @return array
	 */
	public function get_stored_data ( $args ) {
		$data = '';
		$transient_key = $this->id . '-recent-photos';
		
		if ( false === ( $data = get_transient( $transient_key ) ) ) {
			$response = $this->request_recent_photos( $args );

			if ( isset( $response->data ) ) {
				$data = json_encode( $response );
				set_transient( $transient_key, $data, $this->transient_expire_time );
			}
		}

		return json_decode( $data );
	} // End get_stored_data()

	/**
	 * Retrieve recent photos for the specified user.
	 * @param  array $args
	 * @return array
	 */
	public function request_recent_photos ( $args ) {
		$data = array();

		$response = $this->request( 'v1/users/self/media/recent', $args, 'get' );

		if( is_wp_error( $response ) ) {
		   $data = new StdClass;
		} else {
		   if ( isset( $response->meta->code ) && ( $response->meta->code == 200 ) ) {
		   		$data = $response;
		   }
		}

		return $data;
	} // End request_recent_photos()

	/**
	 * Make a request to the API.
	 * @param  string $endpoint The endpoint of the API to be called.
	 * @param  array  $params   Array of parameters to pass to the API.
	 * @return object           The response from the API.
	 */
	private function request ( $endpoint, $params = array(), $method = 'post' ) {
		$return = '';

		if ( $method == 'get' ) {
			$url = $this->api_url . $endpoint;

			if ( count( $params ) > 0 ) {
				$url .= '?';
				$count = 0;
				foreach ( $params as $k => $v ) {
					$count++;

					if ( $count > 1 ) {
						$url .= '&';
					}

					$url .= $k . '=' . $v;
				}
			}

			$response = wp_remote_get( $url,
				array(
					'sslverify' => apply_filters( 'https_local_ssl_verify', false )
				)
			);
		} else {
			$response = wp_remote_post( $this->api_url . $endpoint,
				array(
					'body' => $params,
					'sslverify' => apply_filters( 'https_local_ssl_verify', false )
				)
			);
		}

		if ( ! is_wp_error( $response ) ) {
			$return = json_decode( $response['body'] );
		}

		return $return;
	} // End request()

	/**
	 * Request an access token from the API.
	 * @param  string $username The username.
	 * @param  string $password The password.
	 * @return string           Access token.
	 */
	private function get_access_token ( $username, $password ) {
		$args = array(
				'username' => $username,
				'password' => $password,
				'grant_type' => 'password',
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret
			);

		$response = $this->request( 'oauth/access_token', $args );

		return $response;
	} // End get_access_token()

	/**
	 * enqueue_styles function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( 'woodojo-social-widgets', $this->assets_url . 'css/style.css' );
		wp_enqueue_style( 'woodojo-social-widgets' );

		$instance = $this->get_settings();
		if ( isset( $instance[$this->number] ) ) $instance = $instance[$this->number];

		if ( isset( $instance['enable_thickbox'] ) && $instance['enable_thickbox'] == true ) {
			wp_enqueue_style( 'thickbox' );
		}
	} // End enqueue_styles()

	/**
	 * enqueue_scripts function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts () {
		$instance = $this->get_settings();
		if ( isset( $instance[$this->number] ) ) $instance = $instance[$this->number];

		if ( isset( $instance['enable_thickbox'] ) && $instance['enable_thickbox'] == true ) {
			wp_enqueue_script( 'thickbox' );
		}
	} // End enqueue_scripts()

	/**
	 * Prepare the returned data into HTML.
	 * @param  object $data The data retrieved from the API.
	 * @param  array $instance The settings for the current widget instance.
	 * @return string       The rendered HTML.
	 */
	private function prepare_photos_html ( $data, $instance ) {
		$html = '';

		if ( is_object( $data ) && isset( $data->data ) && is_array( $data->data ) && ( count( $data->data ) > 0 ) ) {
			$html .= '<ul class="instagram-photos align' . strtolower( $instance['float'] ) . '">' . "\n";

			$params = '';
			$anchor_params = '';
			$size_token = $instance['image_size'];

			if ( $instance['custom_image_size'] == '' || in_array( $instance['custom_image_size'], array( 150, 306, 612 ) ) ) {} else {
				$size_token = $this->determine_image_by_size( $instance['custom_image_size'] );
				$params = ' style=" width: ' . intval( $instance['custom_image_size'] ) . 'px; height: ' . intval( $instance['custom_image_size'] ) . 'px;"';
			}

			$class = 'instagram-photo-link';

			if ( $instance['enable_thickbox'] == true ) {
				$class .= ' thickbox';
				$anchor_params .= ' rel="instagram-thickbox-' . $this->number . '"';
			}

			foreach ( $data->data as $k => $v ) {
				$caption = '';
				if ( isset( $v->caption->text ) && ( $v->caption->text != '' ) ) {
					$caption = $v->caption->text;
				}

				if ( $caption == '' ) {
					$caption = sprintf( __( 'Instagram by %s', 'woodojo' ), $v->user->full_name );
				}

				$html .= '<li>' . "\n";
				if ( $instance['link_to_fullsize'] == true ) {
					$html .= '<a href="' . esc_url( $v->images->standard_resolution->url ) . '" title="' . esc_attr( $caption ) . '" class="' . esc_attr( $class ) . '"' . $anchor_params . '>' . "\n";
				}
					$html .= '<img src="' . esc_url( $v->images->$size_token->url ) . '"' . $params . ' alt="' . esc_attr( $caption ) . '" />' . "\n";
				if ( $instance['link_to_fullsize'] == true ) {
					$html .= '</a>' . "\n";
				}
				$html .= '</li>' . "\n";
			}
			$html .= '</ul>' . "\n";
		}

		return $html;
	} // End prepare_photos_html()

	/**
	 * Determine which of the 3 image sizes should be used, based on a specified custom image size.
	 * @param  string $size The size to be used.
	 * @return string The token of the image to be used.
	 */
	private function determine_image_by_size ( $size ) {
		$token = 'thumbnail';

		if ( $size <= 150 ) { $token = 'thumbnail'; }
		if ( $size <= 306 && $size > 150 ) { $token = 'low_resolution'; }
		if ( ( $size <= 612 || $size > 612 ) && $size > 306 ) { $token = 'standard_resolution'; }

		return $token;
	} // End determine_image_by_size()

	/**
	 * Return an array of key/value pairs for use with checkboxes.
	 * @return array
	 */
	private function get_checkbox_settings () {
		return array(
					'link_to_fullsize' => __( 'Link to full size image', 'woodojo' ), 
					'enable_thickbox' => __( 'Enable thickbox', 'woodojo' )
					);
	} // End get_checkbox_settings()
} // End Class
?>