<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo Instagram Profile Widget
 *
 * A bundled WooDojo Instagram profile widget.
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
 * - request_data()
 * - request()
 * - get_access_token()
 * - enqueue_styles()
 * - generate_profile_box()
 * - get_checkbox_settings()
 */
class WooDojo_Widget_InstagramProfile extends WP_Widget {

	/* Variable Declarations */
	var $woo_widget_cssclass;
	var $woo_widget_description;
	var $woo_widget_idbase;
	var $woo_widget_title;

	var $transient_expire_time;
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
		$this->woo_widget_cssclass = 'widget_woodojo_instagram_profile';
		$this->woo_widget_description = __( 'This is a WooDojo bundled Instagram profile widget.', 'woodojo' );
		$this->woo_widget_idbase = 'woodojo_instagram_profile';
		$this->woo_widget_title = __('WooDojo - Instagram Profile', 'woodojo' );
		
		$this->transient_expire_time = 60 * 60 * 24 * 7; // 1 week.
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
					'access_token' => esc_attr( $instance['access_token'] ), 
					'q' => strip_tags( sanitize_user( $instance['username'] ) ), 
					'count' => 1
					);

		$data = $this->get_stored_data( $args );

		$html .= $this->generate_profile_box( $data, $instance );

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
			}
		} else {

			/* The select box is returning a text value, so we escape it. */
			$instance['avatar_alignment'] = esc_attr( $new_instance['avatar_alignment'] );

			/* The checkbox is returning a Boolean (true/false), so we check for that. */
			$instance['logout'] = (bool) esc_attr( $new_instance['logout'] );

			$checkboxes = array_keys( $this->get_checkbox_settings() );

			/* The checkbox is returning a Boolean (true/false), so we check for that. */
			foreach ( $checkboxes as $k => $v ) {
				$instance[$v] = (bool) esc_attr( $new_instance[$v] );
			}

			if ( $instance['logout'] == true ) {
				$instance['access_token'] = '';

				// Clear the transient, forcing an update on next frontend page load.
				delete_transient( $this->id . '-profile' );

				$instance['logout'] = false;
			}
		}

		/* Strip tags for the username, and sanitize it as if it were a WordPress username. */
		$instance['username'] = strip_tags( sanitize_user( $new_instance['username'] ) );
		
		// Allow child themes/plugins to act here.
		$instance = apply_filters( $this->woo_widget_idbase . '_widget_save', $instance, $new_instance, $this );

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
						'title' => __( 'Instagram Profile', 'woodojo' ), 
						'access_token' => '', 
						'username' => '', 
						'password' => '', 
						'avatar_alignment' => 'left', 
						'logout' => 0, 
						'display_avatar' => 1, 
						'display_name' => 1, 
						'display_screen_name' => 1, 
						'display_description' => 1, 
						'display_website' => 1, 
						'display_media_count' => 1, 
						'display_followed_by_count' => 1, 
						'display_follows_count' => 1
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
		</p>
<?php
	} else {
?>
	<!-- Widget Avatar Alignment: Select Input -->
	<p>
		<label for="<?php echo $this->get_field_id( 'avatar_alignment' ); ?>"><?php _e( 'Avatar Alignment:', 'woothemes' ); ?></label>
		<select name="<?php echo $this->get_field_name( 'avatar_alignment' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'avatar_alignment' ); ?>">
			<option value="left"<?php selected( $instance['avatar_alignment'], 'left' ); ?>><?php _e( 'Left', 'woodojo' ); ?></option>
			<option value="centre"<?php selected( $instance['avatar_alignment'], 'centre' ); ?>><?php _e( 'Centre', 'woodojo' ); ?></option>
			<option value="right"<?php selected( $instance['avatar_alignment'], 'right' ); ?>><?php _e( 'Right', 'woodojo' ); ?></option>         
		</select>
	</p>
	<?php foreach ( $checkboxes as $k => $v ) { ?>
	<!-- Widget <?php echo $v; ?>: Checkbox Input -->
	<p>
		<input id="<?php echo $this->get_field_id( $k ); ?>" name="<?php echo $this->get_field_name( $k ); ?>" type="checkbox"<?php checked( $instance[$k], 1 ); ?> />
    	<label for="<?php echo $this->get_field_id( $k ); ?>"><?php echo $v; ?></label>
	</p>
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
		$data = array();
		$transient_key = $this->id . '-profile';
		
		if ( false === ( $data = get_transient( $transient_key ) ) ) {
			$response = $this->request_data( $args );

			if ( isset( $response->data ) ) {
				$data = $response;
				set_transient( $transient_key, $data, $this->transient_expire_time );
			}
		}

		return $data;
	} // End get_stored_data()

	/**
	 * Retrieve profile data for the specified user.
	 * @param  array $args
	 * @return array
	 */
	public function request_data ( $args ) {
		$data = array();

		$response = $this->request( 'v1/users/search/', $args, 'get' );

		if( is_wp_error( $response ) ) {
		   $data = new StdClass;
		} else {
		   if ( isset( $response->meta->code ) && ( $response->meta->code == 200 ) ) {		   		
		   		$user_id = $response->data[0]->id;

		   		if ( $user_id != '' ) {
		   			$response = $this->request( 'v1/users/' . intval( $user_id ) . '/', $args, 'get' );
		   			if ( isset( $response->meta->code ) && ( $response->meta->code == 200 ) ) {
		   				$data = $response;
		   			}
		   		}
		   }
		}

		return $data;
	} // End request_data()

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
	} // End enqueue_styles()

	/**
	 * generate_profile_box function.
	 * @param  object $data The data returned from the API.
	 * @return string       The HTML for the profile box.
	 */
	private function generate_profile_box ( $data, $instance ) {
		$html = '';

		if ( isset( $data->data->id ) ) {
			if ( isset( $data->meta->code ) && $data->meta->code == 200 ) {
				// Determine whether or not we have stats.
				$has_stats = false;

				if (
					( $instance['display_media_count'] == 1 && isset( $data->data->counts->media ) ) || 
					( $instance['display_followed_by_count'] == 1 && isset( $data->data->counts->followed_by ) ) || 
					( $instance['display_follows_count'] == 1 && isset( $data->data->counts->follows ) )
				   ) {
					$has_stats = true;
				}

				$html .= '<div class="profile-box">' . "\n";
				if ( $instance['display_avatar'] == true && isset( $data->data->profile_picture ) ) {
					$html .= '<img src="' . esc_url( $data->data->profile_picture ) . '"  alt="' . $data->data->username . '" title="' . $data->data->username . '" class="avatar align' . esc_attr( $instance['avatar_alignment'] ) . '" />' . "\n";
				}
				if ( $instance['display_name'] == true && isset( $data->data->full_name ) ) {
					$html .= '<h4 class="name">' . $data->data->full_name;
					if ( $instance['display_screen_name'] == true && isset( $data->data->username ) ) {
						$html .= ' (' . $data->data->username . ')';
					}
					$html .= '</h4>' . "\n";
				} else {
					if ( $instance['display_screen_name'] == true && isset( $data->data->username ) ) {
						$html .= '<h4 class="name">' . $data->data->username . '</h4>' . "\n";
					}
				}
				$html .= '<div class="profile-content">' . "\n";
				if ( $instance['display_website'] == 1 && $data->data->website != '' ) {
					$html .= '<span class="website"><a href="' . esc_url( $data->data->website ) . '">' . $data->data->website . '</a></span>' . "\n";
				}
				if ( $instance['display_description'] == 1 && $data->data->bio != '' ) {
					$html .= '<p class="bio">' . $data->data->bio . '</p>' . "\n";
				}
				$html .= '</div><!--/.profile-content-->' . "\n";

				if ( $has_stats == true ) {
					$html .= '<div class="stats">' . "\n";
				}

				if ( $instance['display_media_count'] == 1 && isset( $data->data->counts->media ) ) {
					$html .= '<p class="media stat"><span class="number">' . $data->data->counts->media . '</span> <span class="stat-label">' . __( 'Media', 'woodojo' ) . '</span></p>' . "\n";
				}

				if ( $instance['display_followed_by_count'] == 1 && isset( $data->data->counts->followed_by ) ) {
					$html .= '<p class="followed_by stat"><span class="number">' . $data->data->counts->followed_by . '</span> <span class="stat-label">' . __( 'Followed By', 'woodojo' ) . '</span></p>' . "\n";
				}

				if ( $instance['display_follows_count'] == 1 && isset( $data->data->counts->follows ) ) {
					$html .= '<p class="follows stat"><span class="number">' . $data->data->counts->follows . '</span> <span class="stat-label">' . __( 'Follows', 'woodojo' ) . '</span></p>' . "\n";
				}
				
				if ( $has_stats == true ) {
					$html .= '</div>' . "\n";
				}

				$html .= '</div><!--/.profile-box-->' . "\n";
			}

		}

		return $html;
	} // End generate_profile_box()

	/**
	 * Return an array of key/value pairs for use with checkboxes.
	 * @return array
	 */
	private function get_checkbox_settings () {
		return array(
					'display_avatar' => __( 'Display Avatar', 'woodojo' ), 
					'display_name' => __( 'Display Name', 'woodojo' ), 
					'display_screen_name' => __( 'Display Screen Name', 'woodojo' ), 
					'display_description' => __( 'Display Description', 'woodojo' ), 
					'display_website' => __( 'Display Website', 'woodojo' ), 
					'display_media_count' => __( 'Display Media Count', 'woodojo' ), 
					'display_followed_by_count' => __( 'Display Followed By Count', 'woodojo' ), 
					'display_follows_count' => __( 'Display Follows Count', 'woodojo' )
					);
	} // End get_checkbox_settings()
} // End Class
?>