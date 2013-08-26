<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*---------------------------------------------------------------------------------*/
/* Best Selling Products widget */
/*---------------------------------------------------------------------------------*/
class Woo_Best_Selling_Products extends WP_Widget {
	var $settings = array( 'products_per_page' );

	function Woo_Best_Selling_Products() {
		$widget_ops = array( 'description' => 'Display best selling products (use in the homepage widget region)' );
		parent::WP_Widget( false, __( 'Superstore - Best Selling Products Loop', 'woothemes' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		$instance = $this->woo_enforce_defaults( $instance );
		extract( $args, EXTR_SKIP );
		extract( $instance, EXTR_SKIP );

			echo $before_widget;

				echo '<h1>' . __( 'Best Sellers', 'woothemes' ) . '</h1>';

	    		echo do_shortcode( '[best_selling_products per_page="' . apply_filters( 'products_per_page', $products_per_page, $instance, $this->id_base ) . '" columns="4" orderby="date" order="desc"]' );

			echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$new_instance = $this->woo_enforce_defaults( $new_instance );
		return $new_instance;
	}

	function woo_enforce_defaults( $instance ) {
		$defaults = $this->woo_get_settings();
		$instance = wp_parse_args( $instance, $defaults );
		$instance['products_per_page'] = strip_tags( $instance['products_per_page'] );
		if ( '' == $instance['products_per_page'] ) {
			$instance['products_per_page'] = __( '4', 'woothemes' );
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
		<p>
			<label for="<?php echo $this->get_field_id('products_per_page'); ?>"><?php _e('Number of best selling products to display (multiples of 4 recommended):','woothemes'); ?></label>
			<input type="number" name="<?php echo $this->get_field_name('products_per_page'); ?>" value="<?php echo esc_attr( $products_per_page ); ?>" size="3" style="width:40px;" id="<?php echo $this->get_field_id('products_per_page'); ?>" />
		</p>

<?php
	}
}

register_widget( 'Woo_Best_Selling_Products' );
