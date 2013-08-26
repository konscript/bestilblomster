<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo Tabs Widget
 *
 * A bundled WooDojo Tabs widget.
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
 * - __construct()
 * - widget()
 * - update()
 * - form()
 * - enqueue_styles()
 * - enqueue_scripts()
 * - tab_content_latest()
 * - tab_content_popular()
 * - tab_content_comments()
 * - tab_content_tags()
 * - tab_content_default(|)
 * - get_image()
 */
class WooDojo_Widget_Tabs extends WP_Widget {

	/* Variable Declarations */
	var $woo_widget_cssclass;
	var $woo_widget_description;
	var $woo_widget_idbase;
	var $woo_widget_title;
	
	var $assets_url;

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
		$this->woo_widget_cssclass = 'widget_woodojo_tabs';
		$this->woo_widget_description = __( 'This is a WooDojo bundled tabs widget.', 'woodojo' );
		$this->woo_widget_idbase = 'woodojo_tabs';
		$this->woo_widget_title = __('WooDojo - Tabs', 'woodojo' );
		
		/* Setup the assets URL in relation to WooDojo. */
		$this->assets_url = trailingslashit( $woodojo->base->components_url . 'woodojo-tabs/assets' );
		
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
		$html = '';
		
		extract( $args, EXTR_SKIP );
		
		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base );

		/* Setup tab pieces to be loaded in below. */
		$tabs = array(
						'latest' => __( 'Latest', 'woodojo' ), 
						'popular' => __( 'Popular', 'woodojo' ), 
						'comments' => __( 'Comments', 'woodojo' ), 
						'tags' => __( 'Tags', 'woodojo' )
					);
		
		// Allow child themes/plugins to filter here.
		$tabs = apply_filters( 'woodojo_tabs_headings', $tabs );

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
		
		if ( count( $tabs ) > 0 ) {
			$tab_content = '';
			$tab_links = '';
			
			// Setup the various tabs.
			$tab_links .= '<ul class="nav nav-tabs">' . "\n";
			$count = 0;
			foreach ( $tabs as $k => $v ) {
				$count++;
				$class = '';
				
				if ( $count == 1 ) { $class = ' first active'; }
				if ( $count == count( $tabs ) ) { $class = ' last'; }
				
				$tab_links .= '<li class="tab-heading-' . esc_attr( $k ) . $class . '"><a href="#tab-pane-' . esc_attr( $k ) . '">' . $v . '</a></li>' . "\n";
				
				$tab_content .= '<div id="tab-pane-' . esc_attr( $k ) . '" class="tab-pane tab-pane-' . esc_attr( $k ) . $class . '">' . "\n";
					
					// Tab functions check for functions of the convention "woodojo_tabs_x" or, if non exists, 
					// a method in this class called "tab_content_x". If none, a default method is used to prevent errors.
					// Parameters: array or arguments: 1: number of posts, 2: dimensions of image
					
					$tab_args = array( 'limit' => intval( $instance['limit'] ), 'image_dimension' => intval( $instance['image_dimension'] ) );
					
					if ( function_exists( 'woodojo_tabs_' . esc_attr( $k ) ) ) {
						$tab_content .= call_user_func_array( 'woodojo_tabs_' . esc_attr( $k ), $tab_args );
					} else {
						if ( method_exists( $this, 'tab_content_' . esc_attr( $k ) ) ) {
							$tab_content .= call_user_func_array( array( $this, 'tab_content_' . esc_attr( $k ) ), $tab_args );
						} else {
							$tab_content .= $this->tab_content_default( $k );
						}
					}

				$tab_content .= '</div><!--/.tab-pane-->' . "\n";
			}
			$tab_links .= '</ul>' . "\n";
		
			
		
			$html .= '<div class="tabbable tabs-' . esc_attr( $instance['tab_position'] ) . '">' . "\n";
				if ( $instance['tab_position'] != 'below' ) { $html .= $tab_links; }
				$html .= '<div class="tab-content image-align-' . $instance['image_alignment'] . '">' . "\n" . $tab_content . '</div><!--/.tab-content-->' . "\n";
				if ( $instance['tab_position'] == 'below' ) { $html .= $tab_links; }
			$html .= '</div><!--/.tabbable-->' . "\n";
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

		/* The select box is returning a text value, so we escape it. */
		$instance['tab_position'] = esc_attr( $new_instance['tab_position'] );
		$instance['image_alignment'] = esc_attr( $new_instance['image_alignment'] );
		
		/* Escape the text string and convert to an integer. */
		$instance['limit'] = intval( strip_tags( $new_instance['limit'] ) );
		$instance['image_dimension'] = intval( strip_tags( $new_instance['image_dimension'] ) );
		
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
						'title' => __( 'Tabs', 'woodojo' ), 
						'tab_position' => 'above', 
						'limit' => 5, 
						'image_dimension' => 45, 
						'image_alignment' => 'left'
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
		<!-- Widget Tab Position: Select Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'tab_position' ); ?>"><?php _e( 'Tab Position:', 'woodojo' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'tab_position' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'tab_position' ); ?>">
				<option value="above"<?php selected( $instance['tab_position'], 'above' ); ?>><?php _e( 'Above', 'woodojo' ); ?></option>
				<option value="below"<?php selected( $instance['tab_position'], 'below' ); ?>><?php _e( 'Below', 'woodojo' ); ?></option>
				<option value="left"<?php selected( $instance['tab_position'], 'left' ); ?>><?php _e( 'Left', 'woodojo' ); ?></option>
				<option value="right"<?php selected( $instance['tab_position'], 'right' ); ?>><?php _e( 'Right', 'woodojo' ); ?></option>         
			</select>
		</p>
		<!-- Widget Limit: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit:', 'woodojo' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'limit' ); ?>"  value="<?php echo $instance['limit']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" />
		</p>
		<!-- Widget Image Dimension: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'image_dimension' ); ?>"><?php _e( 'Image Dimension:', 'woodojo' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'image_dimension' ); ?>"  value="<?php echo $instance['image_dimension']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'image_dimension' ); ?>" />
		</p>
		<!-- Widget Image Alignment: Select Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'image_alignment' ); ?>"><?php _e( 'Image Alignment:', 'woodojo' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'image_alignment' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'image_alignment' ); ?>">
				<option value="left"<?php selected( $instance['image_alignment'], 'left' ); ?>><?php _e( 'Left', 'woodojo' ); ?></option>
				<option value="right"<?php selected( $instance['image_alignment'], 'right' ); ?>><?php _e( 'Right', 'woodojo' ); ?></option>         
			</select>
		</p>
		<p><small><?php
			if ( function_exists( 'woo_image' ) ) {
				_e( 'woo_image() will be used to display thumbnails.', 'woodojo' );
			} else {
				if ( current_theme_supports( 'post-thumbnails' ) ) {
					_e( 'The "featured image" will be used as thumbnails.', 'woodojo' );
				} else {
					_e( 'Post thumbnails are not supported by your theme. Thumbnails will not be displayed.', 'woodojo' );
				}
			}
		?></small></p>
<?php
		
		// Allow child themes/plugins to act here.
		do_action( $this->woo_widget_idbase . '_widget_settings', $instance, $this );
	} // End form()
	
	/**
	 * enqueue_styles function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->woo_widget_idbase, $this->assets_url . 'css/style.css' );
		
		wp_enqueue_style( $this->woo_widget_idbase );
	} // End enqueue_styles()
	
	/**
	 * enqueue_scripts function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->woo_widget_idbase, $this->assets_url . 'js/functions.js', array( 'jquery' ), '1.0.0' );
		
		wp_enqueue_script( $this->woo_widget_idbase );
	} // End enqueue_styles()
	
	/**
	 * tab_content_latest function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param int $limit
	 * @param int $image_dimension
	 * @return void
	 */
	public function tab_content_latest ( $limit, $image_dimension ) {
		global $post;
		$html = '';
		
		$html .= '<ul class="latest">' . "\n";
		$latest = get_posts( 'ignore_sticky_posts=1&numberposts=' . $limit . '&orderby=post_date&order=desc' );
		foreach( $latest as $post ) {
			setup_postdata($post);
			$html .= '<li>' . "\n";
			if ( $image_dimension > 0 ) {
				$html .= $this->get_image( $image_dimension, $post );
			}
			$html .= '<a title="' . the_title_attribute( array( 'echo' => false ) ) . '" href="' . esc_url( get_permalink( $post ) ) . '">' . get_the_title() . '</a>' . "\n";
			$html .= '<span class="meta">' . get_the_time( get_option( 'date_format' ) ) . '</span>' . "\n";
			$html .= '</li>' . "\n";
		}
		$html .= '</ul>' . "\n";
		wp_reset_query();
		
		return $html;
	} // End tab_content_latest()
	
	/**
	 * tab_content_popular function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param int $limit
	 * @param int $image_dimension
	 * @return void
	 */
	public function tab_content_popular ( $limit, $image_dimension ) {
		global $post;
		$html = '';
		
		$html .= '<ul class="popular">' . "\n";
		$popular = get_posts( 'ignore_sticky_posts=1&numberposts=' . $limit . '&orderby=comment_count&order=desc' );
		foreach( $popular as $post ) {
			setup_postdata($post);
			$html .= '<li>' . "\n";
			if ( $image_dimension > 0 ) {
				$html .= $this->get_image( $image_dimension, $post );
			}
			$html .= '<a title="' . the_title_attribute( array( 'echo' => false ) ) . '" href="' . esc_url( get_permalink( $post ) ) . '">' . get_the_title() . '</a>' . "\n";
			$html .= '<span class="meta">' . get_the_time( get_option( 'date_format' ) ) . '</span>' . "\n";
			$html .= '</li>' . "\n";
		}
		$html .= '</ul>' . "\n";
		wp_reset_query();
		
		return $html;
	} // End tab_content_popular()
	
	/**
	 * tab_content_comments function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param int $limit
	 * @param int $image_dimension
	 * @return void
	 */
	public function tab_content_comments ( $limit, $image_dimension ) {
		global $wpdb;
		$html = '';
		
		$comments = get_comments( array( 'number' => $limit, 'status' => 'approve' ) );
		if ( $comments ) {
			$html .= '<ul class="comments">' . "\n";
			foreach( $comments as $c ) {
				$html .= '<li>' . "\n";
				if ( $image_dimension > 0 ) {
					$html .= get_avatar( $c, $image_dimension );
				}
				$html .= '<a title="' . esc_attr( $c->comment_author . ' ' . __( 'on', 'woodojo' ) . ' ' . get_the_title( $c->comment_post_ID ) ) . '" href="' . esc_url( get_comment_link( $c->comment_ID ) ) . '">' . esc_html( $c->comment_author ) . '</a>' . "\n";
				$html .= '<span class="comment-content">' . stripslashes( substr( esc_html( $c->comment_content ), 0, 50 ) ) . '</span>' . "\n";
				$html .= '</li>' . "\n";
			}
 			$html .= '</ul>' . "\n";
 		}
 		
 		return $html;
	} // End tab_content_comments()
	
	/**
	 * tab_content_tags function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param int $limit
	 * @param int $image_dimension
	 * @return void
	 */
	public function tab_content_tags ( $limit, $image_dimension ) {
		return wp_tag_cloud( array( 'echo' => false, 'smallest' => 12, 'largest' => 20 ) );
	} // End tab_content_tags()
	
	/**
	 * tab_content_default function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $token (default: '')
	 * @return void
	 */
	public function tab_content_default ( $token = '' ) {
		// Silence is golden.
	} // End tab_content_default()
	
	/**
	 * get_image function.
	 * 
	 * @access public
	 * @param int $dimension
	 * @param object $post
	 * @return string $html
	 */
	public function get_image ( $dimension, $post ) {
		$html = '';
		
		if ( function_exists( 'woo_image' ) ) {
			$html = woo_image( 'return=true&width=' . $dimension . '&height=' . $dimension . '&class=thumbnail&single=true' );
		} else {
			if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $post->ID ) ) {
				$html = get_the_post_thumbnail( $post->ID, array( $dimension, $dimension ), array( 'class' => 'thumbnail' ) );
			}
		}
		
		return $html;
	} // End get_image()
} // End Class
?>