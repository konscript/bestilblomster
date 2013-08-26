<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*---------------------------------------------------------------------------------*/
/* Subscribe widget */
/*---------------------------------------------------------------------------------*/
class Woo_Subscribe extends WP_Widget {
	var $settings = array( 'title', 'form', 'social', 'single', 'page' );

	function Woo_Subscribe() {
		$widget_ops = array( 'description' => 'Add a subscribe/connect widget.' );
		parent::WP_Widget( false, __( 'Woo - Subscribe / Connect', 'woothemes' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		$instance = $this->woo_enforce_defaults( $instance );
		extract( $args, EXTR_SKIP );
		extract( $instance, EXTR_SKIP );
		if ( !is_singular() || ($single != 'on' && is_single()) || ($page != 'on' && is_page()) ) {
		?>
			<?php echo $before_widget; ?>
			<?php woo_subscribe_connect('true', $title, $form, $social); ?>
			<?php echo $after_widget; ?>
		<?php
		}
	}

	function update($new_instance, $old_instance) {
		$new_instance = $this->woo_enforce_defaults( $new_instance );
		return $new_instance;
	}

	function woo_enforce_defaults( $instance ) {
		$defaults = $this->woo_get_settings();
		$instance = wp_parse_args( $instance, $defaults );
		$instance['title'] = strip_tags( $instance['title'] );
		if ( '' == $instance['title'] )
			$instance['title'] = __('Subscribe', 'woothemes');
		foreach ( array( 'form', 'social', 'single', 'page' ) as $checkbox ) {
			if ( 'on' != $instance[$checkbox] )
					$instance[$checkbox] = '';
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
		return $settings;
	}

	function form($instance) {
		$instance = $this->woo_enforce_defaults( $instance );
		extract( $instance, EXTR_SKIP );
?>
		<p><em>Setup this widget in your <a href="<?php echo admin_url( 'admin.php?page=woothemes' ); ?>">options panel</a> under <strong>Subscribe &amp; Connect</strong></em>.</p>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title (optional):','woothemes'); ?></label>
			<input type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" />
		</p>
		<p>
			<input id="<?php echo $this->get_field_id('form'); ?>" name="<?php echo $this->get_field_name('form'); ?>" type="checkbox" <?php checked( $form, 'on' ); ?>> <?php _e('Disable Subscription Form', 'woothemes'); ?></input>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id('social'); ?>" name="<?php echo $this->get_field_name('social'); ?>" type="checkbox" <?php checked( $social, 'on' ); ?>> <?php _e('Disable Social Icons', 'woothemes'); ?></input>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id('single'); ?>" name="<?php echo $this->get_field_name('single'); ?>" type="checkbox" <?php checked( $single, 'on' ); ?>> <?php _e('Disable in Posts', 'woothemes'); ?></input>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id('page'); ?>" name="<?php echo $this->get_field_name('page'); ?>" type="checkbox" <?php checked( $page, 'on' ); ?>> <?php _e('Disable in Pages', 'woothemes'); ?></input>
		</p>
<?php
	}
}

register_widget( 'Woo_Subscribe' );
