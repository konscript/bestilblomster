<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo Twitter Profile Widget
 *
 * A bundled WooDojo Twitter Profile widget.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Bundled
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $woo_widget_cssclass
 * var $woo_widget_description
 * var $woo_widget_idbase
 * var $woo_widget_title
 * 
 * var $transient_expire_time
 * 
 * - __construct()
 * - widget()
 * - update()
 * - form()
 * - get_stored_data()
 * - request_profile_data()
 * - get_checkbox_settings()
 * - enqueue_styles()
 */
class WooDojo_Widget_TwitterProfile extends WP_Widget {

	/* Variable Declarations */
	var $woo_widget_cssclass;
	var $woo_widget_description;
	var $woo_widget_idbase;
	var $woo_widget_title;

	var $transient_expire_time;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @uses WooDojo
	 * @return void
	 */
	function __construct () {
		global $woodojo;

		/* Widget variable settings. */
		$this->woo_widget_cssclass = 'widget_woodojo_twitterprofile';
		$this->woo_widget_description = __( 'This is a WooDojo bundled Twitter profile widget.', 'woodojo' );
		$this->woo_widget_idbase = 'woodojo_twitterprofile';
		$this->woo_widget_title = __('WooDojo - Twitter Profile', 'woodojo' );
		
		$this->transient_expire_time = 60 * 60 * 24 * 7; // 1 week.

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
	function widget( $args, $instance ) {
		// Twitter handle is required.
		if ( ! isset( $instance['twitter_handle'] ) || ( $instance['twitter_handle'] == '' ) ) { return; }

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
					'username' => $instance['twitter_handle']
					);

		$data = $this->get_stored_data( $args );

		// Determine whether or not we have stats.
		$has_stats = false;

		if (
			( $instance['display_friends_count'] == true && isset( $data->friends_count ) ) || 
			( $instance['display_follower_count'] == true && isset( $data->followers_count ) ) || 
			( $instance['display_status_count'] == true && isset( $data->statuses_count ) )
		   ) {
			$has_stats = true;
		}

		if ( $instance['display_avatar'] == true && isset( $data->profile_image_url ) ) {
			if ( is_ssl() ) {
				$avatar_url = $data->profile_image_url_https;
			} else {
				$avatar_url = $data->profile_image_url;
			}
			$html .= '<img src="' . esc_url( $avatar_url ) . '" alt="' . $data->screen_name . '" title="' . $data->screen_name . '" class="avatar align' . esc_attr( $instance['avatar_alignment'] ) . '" />' . "\n";
		}

		if ( $instance['display_name'] == true && isset( $data->name ) ) {
			$html .= '<h4 class="name">' . $data->name;
			if ( $instance['display_screen_name'] == true && isset( $data->screen_name ) ) {
				$html .= ' (' . $data->screen_name . ')';
			}
			$html .= '</h4>' . "\n";
		} else {
			if ( $instance['display_screen_name'] == true && isset( $data->screen_name ) ) {
				$html .= '<h4 class="name">' . $data->screen_name . '</h4>' . "\n";
			}
		}

		if ( $instance['display_description'] == true || $instance['display_location'] == true ) {
			$html .= '<p class="profile-info">' . "\n";
		}

		if ( $instance['display_description'] == true && isset( $data->description ) ) {
			$html .= '<span class="description">' . $data->description . '</span>' . "\n";
		}

		if ( $instance['display_location'] == true && isset( $data->location ) ) {
			if ( $instance['display_description'] == true && isset( $data->description ) ) {
				$html .= '<br />';
			}
			$html .= '<span class="location">' . $data->location . '</span>' . "\n";
		}

		if ( $instance['display_description'] == true || $instance['display_location'] == true ) {
			$html .= '</p>' . "\n";
		}
		
		if ( $has_stats == true ) {
			$html .= '<div class="stats">' . "\n";
		}

		if ( $instance['display_friends_count'] == true && isset( $data->friends_count ) ) {
			$html .= '<p class="friends stat"><span class="number">' . $data->friends_count . '</span> <span class="stat-label">' . __( 'friends', 'woodojo' ) . '</span></p>' . "\n";
		}

		if ( $instance['display_follower_count'] == true && isset( $data->followers_count ) ) {
			$html .= '<p class="followers stat"><span class="number">' . $data->followers_count . '</span> <span class="stat-label">' . __( 'followers', 'woodojo' ) . '</span></p>' . "\n";
		}

		if ( $instance['display_status_count'] == true && isset( $data->statuses_count ) ) {
			$html .= '<p class="statuses stat"><span class="number">' . $data->statuses_count . '</span> <span class="stat-label">' . __( 'tweets', 'woodojo' ) . '</span></p>' . "\n";
		}
		
		if ( $has_stats == true ) {
			$html .= '</div>' . "\n";
		}

		if ( $instance['display_tweeting_since'] == true && isset( $data->statuses_count ) ) {
			$html .= '<p class="tweeting-since">' . __( 'Tweeting since', 'woodojo' ) . ' <span class="date">' . date( get_option( 'date_format' ), strtotime( $data->created_at ) ) . '</span>' . '</p>' . "\n";
		}

		if ( $instance['include_follow_link'] != false ) {
			$html .= '<p class="follow-link"><a href="' . esc_url( 'http://twitter.com/' . urlencode( $instance['twitter_handle'] ) ) . '">' . sprintf( __( 'Follow %s on Twitter', 'woodojo' ), $instance['twitter_handle'] ) . '</a></p>' . "\n";
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
	function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		/* Strip tags for the Twitter username, and sanitize it as if it were a WordPress username. */
		$instance['twitter_handle'] = strip_tags( sanitize_user( $new_instance['twitter_handle'] ) );

		/* The select box is returning a text value, so we escape it. */
		$instance['avatar_alignment'] = esc_attr( $new_instance['avatar_alignment'] );

		$checkboxes = array_keys( $this->get_checkbox_settings() );

		/* The checkbox is returning a Boolean (true/false), so we check for that. */
		foreach ( $checkboxes as $k => $v ) {
			$instance[$v] = (bool) esc_attr( $new_instance[$v] );
		}
		
		// Allow child themes/plugins to act here.
		$instance = apply_filters( $this->woo_widget_idbase . '_widget_save', $instance, $new_instance, $this );
		
		// Clear the transient, forcing an update on next frontend page load.
		delete_transient( $this->id . '-profile' );

		return $instance;
	} // End update()

   /**
    * form function.
    * 
    * @access public
    * @param array $instance
    * @return void
    */
   function form ( $instance ) {
		/* Set up some default widget settings. */
		/* Make sure all keys are added here, even with empty string values. */
		$defaults = array(
						'title' => __( 'Twitter Profile', 'woodojo' ), 
						'twitter_handle' => '', 
						'display_avatar' => 1, 
						'display_name' => 1, 
						'display_screen_name' => 1, 
						'display_description' => 1, 
						'display_location' => 1, 
						'display_status_count' => 1, 
						'display_follower_count' => 1, 
						'display_friends_count' => 1, 
						'display_tweeting_since' => 1, 
						'avatar_alignment' => 'left', 
						'include_follow_link' => 1
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
		<!-- Widget Twitter Handle: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_handle' ); ?>"><?php _e( 'Twitter Username (required):', 'woodojo' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'twitter_handle' ); ?>"  value="<?php echo $instance['twitter_handle']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'twitter_handle' ); ?>" />
		</p>
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
<?php
		
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
			$response = $this->request_profile_data( $args );

			if ( isset( $response->id ) ) {
				$data = $response;
				set_transient( $transient_key, $data, $this->transient_expire_time );
			}
		}

		return $data;
	} // End get_stored_data()

	/**
	 * Retrieve Twitter profile data for a specified username.
	 * @param  array $args
	 * @return array
	 */
	public function request_profile_data ( $args ) {
		$data = array();
		
		$url = 'https://twitter.com/users/' . urlencode( $args['username'] ) . '.json';

		$response = wp_remote_get( $url, array(
			'method' => 'GET',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array(),
			'cookies' => array(), 
			'sslverify' => false
		    )
		);

		if( is_wp_error( $response ) ) {
		   $data = array();
		} else {
		   $response = json_decode( $response['body'] );

			if ( isset( $response->id ) ) {
				$data = $response;
			}
		}

		return $data;
	} // End request_profile_data()

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
					'display_location' => __( 'Display Location', 'woodojo' ), 
					'display_status_count' => __( 'Display Tweet Count', 'woodojo' ), 
					'display_follower_count' => __( 'Display Followers Count', 'woodojo' ), 
					'display_friends_count' => __( 'Display Friends Count', 'woodojo' ), 
					'display_tweeting_since' => __( 'Display "Tweeting Since"', 'woodojo' ), 
					'include_follow_link' => __( 'Include "Follow" Link', 'woodojo' )
					);
	} // End get_checkbox_settings()

	/**
	 * enqueue_styles function.
	 * 
	 * @access public
	 * @since 1.0.1
	 * @return void
	 */
	function enqueue_styles () {
		wp_register_style( 'woodojo-social-widgets', $this->assets_url . 'css/style.css' );
		wp_enqueue_style( 'woodojo-social-widgets' );
	} // End enqueue_styles()
} // End Class
?>