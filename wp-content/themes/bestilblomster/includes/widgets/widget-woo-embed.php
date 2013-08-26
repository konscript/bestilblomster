<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*---------------------------------------------------------------------------------*/
/* Embed Widget */
/*---------------------------------------------------------------------------------*/

class Woo_EmbedWidget extends WP_Widget {
	var $settings = array( 'title', 'cat_id', 'width', 'height', 'limit', 'tag' );

	function Woo_EmbedWidget() {
		$widget_ops = array( 'description' => 'Display the Embed code from posts in tab like fashion.' );
		parent::WP_Widget( false, __( 'Woo - Embed/Video', 'woothemes' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		$instance = $this->woo_enforce_defaults( $instance );
		extract( $instance, EXTR_SKIP );

		if ( !empty( $tag ) )
			$myposts = get_posts( "numberposts=$limit&tag=$tag" );
		else
			$myposts = get_posts( "numberposts=$limit&cat=$cat_id" );

		$post_list = '';
		$count = 0;
		$active = 'active';
		$display = '';

		echo $before_widget;
		echo $before_title . apply_filters('widget_title', $title, $instance, $this->id_base) . $after_title;
		if ( isset( $myposts ) ) {
			foreach( $myposts as $mypost ) {
				$embed = woo_get_embed( 'embed', $width, $height, 'widget_video', $mypost->ID );
				if ( $embed ) {
					$count++;
					if ( $count > 1 ) {
						$active = '';
						$display = "style='display:none'";
					}
					echo '<div class="widget-video-unit" ' . $display . ' >';
					echo '<h4>' . get_the_title( $mypost->ID )  . "</h4>\n";
					echo $embed;
					$post_list .= "<li class='$active'><a href='#'>" . get_the_title( $mypost->ID ) . "</a></li>\n";
					echo '</div>';
				}
			}
		}
?>
		<ul class="widget-video-list">
			<?php echo $post_list; ?>
		</ul>
<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$new_instance = $this->woo_enforce_defaults( $new_instance );
		return $new_instance;
	}

	function woo_enforce_defaults( $instance ) {
		$defaults = $this->woo_get_settings();
		$instance = wp_parse_args( $instance, $defaults );
		$instance['cat_id'] = absint( $instance['cat_id'] );
		if ( $instance['cat_id'] < 1 )
			$instance['cat_id'] = '';
		// Enforce defaults if any of these three are empty
		foreach ( array( 'limit', 'width', 'height' ) as $setting ) {
			$instance[$setting] = absint( $instance[$setting] );
			if ( $instance[$setting] < 1 )
				$instance[$setting] = $defaults[$setting];
		}
		return $instance;
	}

	/**
	 * Provides an array of the settings with the setting name as the key and the default value as the value
	 * This cannot be called get_settings() or it will override WP_Widget::get_settings()
	 */
	function woo_get_settings() {
		// Set the default to a blank string
		$settings = array_fill_keys( $this->settings, '' );
		// Now set the more specific defaults
		$settings['limit']  = 10;
		$settings['width']  = 300;
		$settings['height'] = 200;
		return $settings;
	}

	function form( $instance ) {
		$instance = $this->woo_enforce_defaults( $instance );
		extract( $instance, EXTR_SKIP );
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','woothemes'); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('cat_id'); ?>"><?php _e('Category:','woothemes'); ?></label>
				<?php $cats = get_categories(); ?>
				<select name="<?php echo $this->get_field_name('cat_id'); ?>" class="widefat" id="<?php echo $this->get_field_id('cat_id'); ?>">
				<option value="">Disabled</option>
			<?php
				foreach ($cats as $cat){
					?><option value="<?php echo absint( $cat->cat_ID ); ?>" <?php selected( $cat->cat_ID, $cat_id ); ?>><?php echo esc_html( $cat->cat_name . ' (' . $cat->category_count . ')' ); ?></option><?php
				}
?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('tag'); ?>">Or <?php _e('Tag:','woothemes'); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('tag'); ?>" value="<?php echo esc_attr( $tag ); ?>" class="widefat" id="<?php echo $this->get_field_id('tag'); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Size:','woothemes'); ?></label>
				<input type="text" size="2" name="<?php echo $this->get_field_name('width'); ?>" value="<?php echo esc_attr( $width ); ?>" class="" id="<?php echo $this->get_field_id('width'); ?>" />
				W <input type="text" size="2" name="<?php echo $this->get_field_name('height'); ?>" value="<?php echo esc_attr( $height ); ?>" class="" id="<?php echo $this->get_field_id('height'); ?>" /> H
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Limit (optional):','woothemes'); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo esc_attr( $limit ); ?>" class="" id="<?php echo $this->get_field_id('limit'); ?>" />
			</p>

<?php
	}
}

register_widget( 'Woo_EmbedWidget' );

if ( is_active_widget( null, null, 'woo_embedwidget' ) == true ) {
	add_action( 'wp_footer','woo_widget_embed_js' );
}

function woo_widget_embed_js() {
?>
<!-- Woo Video Player Widget -->
<script type="text/javascript">
	jQuery(document).ready(function(){
		var list = jQuery('ul.widget-video-list');
		list.find('a').click(function(){
			var clickedTitle = jQuery(this).text();
			jQuery(this).parent().parent().find('li').removeClass('active');
			jQuery(this).parent().addClass('active');
			var videoHolders = jQuery(this).parent().parent().parent().children('.widget-video-unit');
			videoHolders.each(function(){
				if(clickedTitle == jQuery(this).children('h4').text()){
					videoHolders.hide();
					jQuery(this).show();
				}
			})
			return false;
		})
	})
</script>
<?php
}
