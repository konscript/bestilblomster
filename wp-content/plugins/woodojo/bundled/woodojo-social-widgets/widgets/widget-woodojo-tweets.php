<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo Tweets Widget
 *
 * A bundled WooDojo Tweets stream widget.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Bundled
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * protected $woo_widget_cssclass
 * protected $woo_widget_description
 * protected $woo_widget_idbase
 * protected $woo_widget_title
 * 
 * protected $transient_expire_time
 * 
 * - __construct()
 * - widget()
 * - update()
 * - form()
 * - get_stored_data()
 * - request_tweets()
 * - enqueue_styles()
 * - find_mentions()
 * - link_mentions()
 */
class WooDojo_Widget_Tweets extends WP_Widget {

	/* Variable Declarations */
	protected $woo_widget_cssclass;
	protected $woo_widget_description;
	protected $woo_widget_idbase;
	protected $woo_widget_title;

	protected $transient_expire_time;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @uses WooDojo
	 * @return void
	 */
	public function __construct () {
		/* Widget variable settings. */
		$this->woo_widget_cssclass = 'widget_woodojo_tweets';
		$this->woo_widget_description = __( 'This is a WooDojo bundled tweets widget.', 'woodojo' );
		$this->woo_widget_idbase = 'woodojo_tweets';
		$this->woo_widget_title = __( 'WooDojo - Tweets', 'woodojo' );
		
		$this->transient_expire_time = 60 * 60; // 1 hour.

		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

		/* Widget control settings. */
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => $this->woo_widget_idbase );

		/* Create the widget. */
		$this->WP_Widget( $this->woo_widget_idbase, $this->woo_widget_title, $widget_ops, $control_ops );
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
					'username' => sanitize_user( strip_tags( $instance['twitter_handle'] ) ), 
					'limit' => intval( $instance['limit'] ), 
					'include_retweets' => (bool)$instance['include_retweets'], 
					'exclude_replies' => (bool)$instance['exclude_replies']
					);

		$tweets = $this->get_stored_data( $args );

		if ( is_array( $tweets ) && ( count( $tweets ) > 0 ) ) {
			$html .= '<ul class="tweets">' . "\n";
			foreach ( $tweets as $k => $v ) {
				$text = $v->text;

				if ( $v->truncated == false ) {
					$text = make_clickable( $text );

					// Optionally find and link up mentions.
					if ( isset( $instance['link_mentions'] ) && ( 1 == $instance['link_mentions'] ) ) {
						$mentions = $this->find_mentions( $text );
						if ( is_array( $mentions ) ) {
							foreach ( $mentions as $i => $j ) {
								$text = str_replace( '@' . $j, '<a href="' . esc_url( 'http://twitter.com/' . $j ) . '" title="' . sprintf( esc_attr__( '@%s on Twitter', 'woodojo' ), $j ) . '">' . '@' . $j . '</a>', $text );
							}
						}
					}
				}

				$html .= '<li class="tweet-number-' . esc_attr( ( $k + 1 ) ) . '">' . "\n";
				$html .= $text . "\n";
				$html .= '<small class="time-ago"><a href="' . esc_url( 'https://twitter.com/' . urlencode( $instance['twitter_handle'] ) . '/status/' . $v->id_str ) . '">' . human_time_diff( strtotime( $v->created_at ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'woodojo' ) . '</a>';
				if ( isset( $v->retweeted_status ) && isset( $v->retweeted_status->id_str ) && isset( $v->retweeted_status->user->screen_name ) ) {
					$html .= ' ' . __( 'retweeted via', 'woodojo' ) . ' <a href="' . esc_url( 'https://twitter.com/' . urlencode( $v->retweeted_status->user->screen_name ) . '/status/' . $v->retweeted_status->id_str ) . '">' . $v->retweeted_status->user->screen_name . '</a>';
				}
				$html .= '</small>' . "\n";
				$html .= '</li>' . "\n";
			}
			$html .= '</ul>' . "\n";
		}

		if ( $instance['include_follow_link'] != false ) {
			$html .= '<p class="follow-link"><a href="' . esc_url( 'http://twitter.com/' . urlencode( $instance['twitter_handle'] ) ) . '">' . sprintf( __( 'Follow %s on Twitter', 'woodojo' ), $instance['twitter_handle'] ) . '</a></p>';
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

		/* Strip tags for the Twitter username, and sanitize it as if it were a WordPress username. */
		$instance['twitter_handle'] = strip_tags( sanitize_user( $new_instance['twitter_handle'] ) );
		
		/* Escape the text string and convert to an integer. */
		$instance['limit'] = intval( strip_tags( $new_instance['limit'] ) );

		/* The checkbox is returning a Boolean (true/false), so we check for that. */
		$instance['include_retweets'] = (bool) esc_attr( $new_instance['include_retweets'] );
		$instance['exclude_replies'] = (bool) esc_attr( $new_instance['exclude_replies'] );
		$instance['link_mentions'] = (bool) esc_attr( $new_instance['link_mentions'] );
		$instance['include_follow_link'] = (bool) esc_attr( $new_instance['include_follow_link'] );
		
		// Allow child themes/plugins to act here.
		$instance = apply_filters( $this->woo_widget_idbase . '_widget_save', $instance, $new_instance, $this );
		
		// Clear the transient, forcing an update on next frontend page load.
		delete_transient( $this->id . '-tweets' );

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
						'title' => __( 'Tweets', 'woodojo' ), 
						'twitter_handle' => '', 
						'limit' => 5, 
						'include_retweets' => 0, 
						'exclude_replies' => 0, 
						'link_mentions' => 0, 
						'include_follow_link' => 1
					);
		
		// Allow child themes/plugins to filter here.
		$defaults = apply_filters( $this->woo_widget_idbase . '_widget_defaults', $defaults, $this );
		
		$instance = wp_parse_args( (array) $instance, $defaults );
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
		<!-- Widget Limit: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit:', 'woodojo' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'limit' ); ?>"  value="<?php echo $instance['limit']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" />
		</p>
		<!-- Widget Include Retweets: Checkbox Input -->
		<p>
			<input id="<?php echo $this->get_field_id( 'include_retweets' ); ?>" name="<?php echo $this->get_field_name( 'include_retweets' ); ?>" type="checkbox"<?php checked( $instance['include_retweets'], 1 ); ?> />
        	<label for="<?php echo $this->get_field_id( 'include_retweets' ); ?>"><?php _e( 'Include Retweets', 'woodojo' ); ?></label>
		</p>
		<!-- Widget Exclude Replies: Checkbox Input -->
		<p>
			<input id="<?php echo $this->get_field_id( 'exclude_replies' ); ?>" name="<?php echo $this->get_field_name( 'exclude_replies' ); ?>" type="checkbox"<?php checked( $instance['exclude_replies'], 1 ); ?> />
        	<label for="<?php echo $this->get_field_id( 'exclude_replies' ); ?>"><?php _e( 'Exclude Replies', 'woodojo' ); ?></label>
		</p>
		<!-- Widget Link Mentions: Checkbox Input -->
		<p>
			<input id="<?php echo $this->get_field_id( 'link_mentions' ); ?>" name="<?php echo $this->get_field_name( 'link_mentions' ); ?>" type="checkbox"<?php checked( $instance['link_mentions'], 1 ); ?> />
        	<label for="<?php echo $this->get_field_id( 'link_mentions' ); ?>"><?php _e( 'Link @mentions', 'woodojo' ); ?></label>
		</p>
		<!-- Widget Include Follow Link: Checkbox Input -->
		<p>
			<input id="<?php echo $this->get_field_id( 'include_follow_link' ); ?>" name="<?php echo $this->get_field_name( 'include_follow_link' ); ?>" type="checkbox"<?php checked( $instance['include_follow_link'], 1 ); ?> />
        	<label for="<?php echo $this->get_field_id( 'include_follow_link' ); ?>"><?php _e( 'Include Follow Link', 'woodojo' ); ?></label>
		</p>
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
		$transient_key = $this->id . '-tweets';

		if ( false === ( $data = get_transient( $transient_key ) ) ) {
			$response = $this->request_tweets( $args );

			if ( ! is_wp_error( $response ) && is_array( $response ) && isset( $response[0]->user->id ) ) {
				$data = $response;
				set_transient( $transient_key, $data, $this->transient_expire_time );
				update_option( $transient_key, $data );
			} else {
				$data = get_option( $transient_key, array() );
			}
		}

		return $data;
	} // End get_stored_data()

	/**
	 * Retrieve tweets for a specified username.
	 * @param  array $args
	 * @return array
	 */
	public function request_tweets ( $args ) {

		if( !isset( $args['username']) || $args['username'] == '' ){
			return array();
		}

		$data = array();
		
		$url = 'https://api.twitter.com/1/statuses/user_timeline.json';

		$url = add_query_arg( array( 'id' => $args['username'] ), $url );

		if ( $args['limit'] != '' ) { $url = add_query_arg( array( 'count' => intval( $args['limit'] ) ) ,$url ); }
		if ( $args['include_retweets'] == true ) { $url = add_query_arg( array( 'include_rts' => 1 ) ,$url ); }
		if ( $args['exclude_replies'] == true ) { $url = add_query_arg( array( 'exclude_replies' => 1 ), $url ); }

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
			if ( isset( $response->error ) ) {
				$data = array();
			} else if ( isset( $response[0]->user->id ) ) {
				$data = $response;
			}
		}

		return $data;
	} // End request_tweets()

	/**
	 * enqueue_styles function.
	 * 
	 * @access public
	 * @since 1.0.1
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( 'woodojo-social-widgets', $this->assets_url . 'css/style.css' );
		wp_enqueue_style( 'woodojo-social-widgets' );
	} // End enqueue_styles()

	/**
	 * Find @mentions in provided content.
	 * @param  string $str the content to search through
	 * @return array
	 */
	private function find_mentions ( $str ) {
	    $pattern = "/@([A-Za-z0-9_]+)/";
	    $str = trim( $str );
	    $all_names = array();
	    preg_match_all( $pattern, $str, $matches );

	    if( $matches ) {
	        $counter = 0;
	        $count = 0;
	        $name_list = array();
	        if ( is_array( $matches[1] ) ) {
	        	foreach ( $matches[1] as $k => $v ) {
	        		$name_list[$counter++] = $v;
	        	}
	        } else {
	        	$name_list[$counter++] = $matches[1];
	        }
	 
	        do {
	            if ( isset( $matches[2] ) ) {
	            	preg_match( $pattern, $matches[2], $more_matches );
		            $name_list[$counter++]  = $more_matches[1];
		            $count = count($more_matches);
		            $matches[2]=$more_matches[($count-1)];
		            $more_matches = "";
		        }
	        } while( $count>=3 );
	 
	        if( ! empty( $name_list ) ) {
	            $all_names = array();
	            $i = 0;
	            foreach ( $name_list as $key => $value ) {
	                if (!is_null($value) && (!in_array($value, $all_names))) {
	                    $all_names[$i] = $value;
	                    $i++;
	                }
	            }
	        }
	        return $all_names;
	    } else {
	    	return false;
	    }
	} // End find_mentions()
} // End Class
?>