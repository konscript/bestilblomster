<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Template Name: Full Width
 *
 * This template is a full-width version of the page.php template file. It removes the sidebar area.
 *
 * @package WooFramework
 * @subpackage Template
 */
	get_header();
	global $woo_options;
?>

    <div id="content" class="page col-full">

    	<?php woo_main_before(); ?>

		<section id="main" class="fullwidth">

        <?php
        	if ( have_posts() ) { $count = 0;
        		while ( have_posts() ) { the_post(); $count++;
        ?>
                <article <?php post_class(); ?>>

                    <section class="entry">

                    	<h1><?php the_title(); ?></h1>

	                	<?php the_content(); ?>
	               	</section><!-- /.entry -->

                </article><!-- /.post -->

			<?php
					} // End WHILE Loop
				} else {
			?>
				<article <?php post_class(); ?>>
                	<p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
                </article><!-- /.post -->
            <?php } ?>

		</section><!-- /#main -->

		<?php woo_main_after(); ?>

    </div><!-- /#content -->

<?php get_footer(); ?>