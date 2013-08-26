<?php
// File Security Check
if ( ! function_exists( 'wp' ) && ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'You do not have sufficient permissions to access this page!' );
}
?><?php
/**
 * Index Template
 *
 * Here we setup all logic and XHTML that is required for the index template, used as both the homepage
 * and as a fallback template, if a more appropriate template file doesn't exist for a specific context.
 *
 * @package WooFramework
 * @subpackage Template
 */
	get_header();
	global $woo_options;

	$settings = array(
					'homepage_enable_product_categories' => 'true',
					'homepage_enable_featured_products' => 'true',
					'homepage_enable_recent_products' => 'true',
					'homepage_enable_testimonials' => 'true',
					'homepage_enable_content' => 'true',
					'homepage_product_categories_title' => '',
					'homepage_product_categories_limit' => 4,
					'homepage_featured_products_title' => '',
					'homepage_featured_products_limit' => 4,
					'homepage_recent_products_title' => '',
					'homepage_recent_products_limit' => 4,
					'homepage_number_of_testimonials' => 4,
					'homepage_testimonials_area_title' => '',
					'homepage_content_type' => 'posts',
					'homepage_page_id' => '',
					'homepage_posts_sidebar' => 'true'
					);
	$settings = woo_get_dynamic_values( $settings );

	$layout_class = 'col-left';
	if ( 'true' != $settings['homepage_posts_sidebar'] ) { $layout_class = 'full-width'; }
?>

    <div id="content" class="col-full">

    	<?php woo_main_before(); ?>

    	<div class="woocommerce woocommerce-wrap woocommerce-columns-4">
	    	<?php
	    		if ( ! dynamic_sidebar( 'homepage' ) ) {
	    			if ( 'true' == $settings['homepage_enable_product_categories'] && is_woocommerce_activated() ) {
	    				the_widget( 'Woo_Product_Categories', array( 'title' => stripslashes( $settings['homepage_product_categories_title'] ), 'categories_per_page' => intval( $settings['homepage_product_categories_limit'] ) ) );
	    			}

	    			if ( 'true' == $settings['homepage_enable_featured_products'] && is_woocommerce_activated() ) {
	    				the_widget( 'Woo_Featured_Products', array( 'title' => stripslashes( $settings['homepage_featured_products_title'] ), 'products_per_page' => intval( $settings['homepage_featured_products_limit'] ) ) );
	    			}

	    			if ( 'true' == $settings['homepage_enable_recent_products'] && is_woocommerce_activated() ) {
	    				the_widget( 'Woo_Recent_Products', array( 'title' => stripslashes( $settings['homepage_recent_products_title'] ), 'products_per_page' => intval( $settings['homepage_recent_products_limit'] ) ) );
	    			}

	    			if ( 'true' == $settings['homepage_enable_testimonials'] ) {
	    				do_action( 'woothemes_testimonials', array( 'title' => stripslashes( $settings['homepage_testimonials_area_title'] ), 'limit' => $settings['homepage_number_of_testimonials'], 'per_row' => 4 ) );
	    			}
	    		}
	    	?>
		</div><!--/.woocommerce-->
<?php if ( 'true' == $settings['homepage_enable_content'] ) { ?>
		<section id="main" class="<?php echo esc_attr( $layout_class ); ?>">
		<?php woo_loop_before(); ?>
<?php
	if ( 'page' == $settings['homepage_content_type'] && 0 < intval( $settings['homepage_page_id'] ) ) {
		global $post;
		$post = get_page( intval( $settings['homepage_page_id'] ) );
		setup_postdata( $post );
		get_template_part( 'content', 'page' );
		wp_reset_postdata();
	} else {
?>
		<?php
        	if ( have_posts() ) : $count = 0;
        ?>

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); $count++; ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to overload this in a child theme then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content', get_post_format() );
				?>

			<?php endwhile; ?>

		<?php else : ?>

            <article <?php post_class(); ?>>
                <p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
            </article><!-- /.post -->

        <?php endif; ?>
<?php } ?>
        <?php woo_loop_after(); ?>

		<?php
			if ( 'posts' == $settings['homepage_content_type'] ) {
				woo_pagenav();
			}
		?>

		</section><!-- /#main -->
<?php } ?>
		<?php woo_main_after(); ?>

        <?php if ( 'true' == $settings['homepage_posts_sidebar'] ) { get_sidebar(); } ?>

    </div><!-- /#content -->

<?php get_footer(); ?>