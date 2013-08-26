<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*---------------------------------------------------------------------------------*/
/* Adspace Widget */
/*---------------------------------------------------------------------------------*/

class Woo_AdWidget extends WP_Widget {
	var $settings = array( 'title', 'adcode', 'image', 'href', 'alt' );

	function Woo_AdWidget() {
		$widget_ops = array('description' => 'Use this widget to add any type of Ad as a widget.' );
		parent::WP_Widget(false, __('Woo - Adspace Widget', 'woothemes'),$widget_ops);      
	}

	function widget($args, $instance) {
		$settings = $this->woo_get_settings();
		extract( $args, EXTR_SKIP );
		$instance = wp_parse_args( $instance, $settings );
		extract( $instance, EXTR_SKIP );
		echo '<div class="adspace-widget widget">';

		if ( $title != '' )
			echo $before_title . apply_filters( 'widget_title', $title, $instance, $this->id_base ) . $after_title;

		if ( $adcode != '' ) {
			echo $adcode;
		} else {
			?><a href="<?php echo esc_url( $href ); ?>"><img src="<?php echo apply_filters( 'image', $image, $instance, $this->id_base ); ?>" alt="<?php echo esc_attr( $alt ); ?>" /></a><?php
		}
		echo '</div>';
	}

	function update( $new_instance, $old_instance ) {
		foreach ( array( 'title', 'alt', 'image', 'href' ) as $setting )
			$new_instance[$setting] = strip_tags( $new_instance[$setting] );
		// Users without unfiltered_html cannot update this arbitrary HTML field
		if ( !current_user_can( 'unfiltered_html' ) )
			$new_instance['adcode'] = $old_instance['adcode'];
		return $new_instance;
	}

	/**
	 * Provides an array of the settings with the setting name as the key and the default value as the value
	 * This cannot be called get_settings() or it will override WP_Widget::get_settings()
	 */
	function woo_get_settings() {
		// Set the default to a blank string
		$settings = array_fill_keys( $this->settings, '' );
		// Now set the more specific defaults
		return $settings;
	}

	function form($instance) {
		$instance = wp_parse_args( $instance, $this->woo_get_settings() );
		extract( $instance, EXTR_SKIP );
?>
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title (optional):','woothemes'); ?></label>
		<input type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" />
	</p>
<?php if ( current_user_can( 'unfiltered_html' ) ) : // Only show it to users who can edit it ?>
	<p>
		<label for="<?php echo $this->get_field_id('adcode'); ?>"><?php _e('Ad Code:','woothemes'); ?></label>
		<textarea name="<?php echo $this->get_field_name('adcode'); ?>" class="widefat" id="<?php echo $this->get_field_id('adcode'); ?>"><?php echo esc_textarea( $adcode ); ?></textarea>
	</p>
	<p><strong>or</strong></p>
<?php endif; ?>
	<p>
		<label for="<?php echo $this->get_field_id('image'); ?>"><?php _e('Image Url:','woothemes'); ?></label>
	<input type="text" name="<?php echo $this->get_field_name('image'); ?>" value="<?php echo esc_attr( $image ); ?>" class="widefat" id="<?php echo $this->get_field_id('image'); ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('href'); ?>"><?php _e('Link URL:','woothemes'); ?></label>
		<input type="text" name="<?php echo $this->get_field_name('href'); ?>" value="<?php echo esc_attr( $href ); ?>" class="widefat" id="<?php echo $this->get_field_id('href'); ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('alt'); ?>"><?php _e('Alt text:','woothemes'); ?></label>
		<input type="text" name="<?php echo $this->get_field_name('alt'); ?>" value="<?php echo esc_attr( $alt ); ?>" class="widefat" id="<?php echo $this->get_field_id('alt'); ?>" />
	</p>
<?php
	}
} 

register_widget( 'Woo_AdWidget' );
