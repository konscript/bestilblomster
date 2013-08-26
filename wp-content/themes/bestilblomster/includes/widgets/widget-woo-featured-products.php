<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*---------------------------------------------------------------------------------*/
/* Featured Products widget */
/*---------------------------------------------------------------------------------*/

class Woo_Featured_Products extends WP_Widget {
	var $settings = array( 'products_per_page' );

	function Woo_Featured_Products() {
		$widget_ops = array( 'description' => 'Display featured products (use in the homepage widget region)' );
		parent::WP_Widget( false, __( 'Superstore - Featured Products Loop', 'woothemes' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		$instance = $this->woo_enforce_defaults( $instance );
		extract( $args, EXTR_SKIP );
		extract( $instance, EXTR_SKIP );

			echo $before_widget;

				if ( is_woocommerce_activated() ) {

					global $loop, $woocommerce;

					$i = 1;

					$args = array(
						'post_type' => 'product',
						'posts_per_page' => apply_filters( 'products_per_page', $products_per_page, $instance, $this->id_base ),
						'meta_query' => array(
							array('key' => '_visibility',
								'value' => array(
									'catalog', 'visible'
									),
								'compare' => 'IN'
								),
							array(
								'key' => '_featured',
								'value' => 'yes'
								)
							)
						);

					echo '<ul class="products featured">';

					$loop = new WP_Query( $args );

					if ( function_exists( 'get_product' ) ) {
						$product = get_product( $loop->post->ID );
					} else {
						$product = new WC_Product( $loop->post->ID );
					}

					while ( $loop->have_posts() ) : $loop->the_post(); $product; ?>

						<li class="product featured <?php if ( $i % 2 == 0 ) { echo 'last'; } else { echo 'first'; } ?>">

							<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

							<a href="<?php the_permalink(); ?>">

								<?php
									do_action( 'woocommerce_before_shop_loop_item_title' );
								?>

								<h3><?php the_title(); ?></h3>



							</a>

							<?php
								echo '<div class="excerpt">' . superstore_truncate( get_the_excerpt(), 11 ) . '</div>';
								do_action( 'woocommerce_after_shop_loop_item_title' );
								do_action( 'woocommerce_after_shop_loop_item' );
							?>

						</li>

					<?php

					$i++;

					endwhile;

					echo '</ul>';

				}

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
			<label for="<?php echo $this->get_field_id('products_per_page'); ?>"><?php _e('Number of featured products to display:','woothemes'); ?></label>
			<input type="number" name="<?php echo $this->get_field_name('products_per_page'); ?>" value="<?php echo esc_attr( $products_per_page ); ?>" size="3" style="width:40px;" id="<?php echo $this->get_field_id('products_per_page'); ?>" />
		</p>

<?php
	}
}

register_widget( 'Woo_Featured_Products' );
