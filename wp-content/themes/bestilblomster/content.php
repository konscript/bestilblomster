<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The default template for displaying content
 */

	global $woo_options;

/**
 * The Variables
 *
 * Setup default variables, overriding them if the "Theme Options" have been saved.
 */

 	$settings = array(
					'thumb_w' => 800,
					'thumb_h' => 300,
					'thumb_align' => 'aligncenter'
					);

	$settings = woo_get_dynamic_values( $settings );

?>

	<article <?php post_class(); ?>>



		<div class="post-content">

			<?php
		    	woo_image( 'width=' . $settings['thumb_w'] . '&height=' . $settings['thumb_h'] . '&class=thumbnail ' . $settings['thumb_align'] );
		    ?>

			<section class="entry">

				<header class="post-header">

		            <h1><a href="<?php the_permalink(); ?>" title="<?php esc_attr_e( 'Continue Reading &rarr;', 'woothemes' ); ?>"><?php the_title(); ?></a></h1>

		        </header>

				<?php if ( isset( $woo_options['woo_post_content'] ) && $woo_options['woo_post_content'] == 'content' ) { the_content( __( 'Continue Reading &rarr;', 'woothemes' ) ); } else { the_excerpt(); } ?>
				<footer class="post-more">
				<?php if ( isset( $woo_options['woo_post_content'] ) && $woo_options['woo_post_content'] == 'excerpt' ) { ?>
					<span class="read-more"><a href="<?php the_permalink(); ?>" title="<?php esc_attr_e( 'Continue Reading &rarr;', 'woothemes' ); ?>"><?php _e( 'Continue Reading &rarr;', 'woothemes' ); ?></a></span>
				<?php } ?>
				</footer>
			</section>

		</div><!--/.post-content-->

		<?php woo_post_meta(); ?>

	</article><!-- /.post -->