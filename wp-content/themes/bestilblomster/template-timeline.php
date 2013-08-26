<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Template Name: Timeline
 *
 * The timeline page template displays a user-friendly timeline of the
 * posts on your website.
 *
 * @package WooFramework
 * @subpackage Template
 */

 global $woo_options;
 get_header();
?>

    <div id="content" class="page col-full">

    	<?php woo_main_before(); ?>

		<section id="main" class="col-left">

		<?php
			query_posts( 'posts_per_page=-1' );
			$dates_array 			= array();
			$year_array 			= array();
			$i 						= 0;
			$prev_post_ts    		= null;
			$prev_post_year  		= null;
			$distance_multiplier	=  9;
		?>
			<article <?php post_class(); ?>>

				<section id="archives" class="entry">

					<header>
						<h1><?php the_title(); ?></h1>
					</header>

				<?php while ( have_posts() ) { the_post();

					$post_ts    =  strtotime( $post->post_date );
					$post_year  =  date( 'Y', $post_ts );

					/* Handle the first year as a special case */
					if ( is_null( $prev_post_year ) ) {
						?>
						<h3 class="archive_year"><?php echo $post_year; ?></h3>
						<ul class="archives_list">
						<?php
					}
					else if ( $prev_post_year != $post_year ) {
						/* Close off the OL */
						?>
						</ul>
						<?php

						$working_year  =  $prev_post_year;

						/* Print year headings until we reach the post year */
						while ( $working_year > $post_year ) {
							$working_year--;
							?>
							<h3 class="archive_year"><?php echo $working_year; ?></h3>
							<?php
						}

						/* Open a new ordered list */
						?>
						<ul class="archives_list">
						<?php
					}

					/* Compute difference in days */
					if ( ! is_null( $prev_post_ts ) && $prev_post_year == $post_year ) {
						$dates_diff  =  ( date( 'z', $prev_post_ts ) - date( 'z', $post_ts ) ) * $distance_multiplier;
					}
					else {
						$dates_diff  =  0;
					}
				?>
					<li>
						<span class="date"><?php the_time( 'F j' ); ?><sup><?php the_time( 'S' ); ?></sup></span> <span class="linked"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></span> <span class="comments"><?php comments_popup_link( __( 'Leave a comment', 'woothemes' ), __( '1 comment', 'woothemes' ), __( '% comments', 'woothemes' ) ); ?></span>
					</li>
				<?php
						/* For subsequent iterations */
						$prev_post_ts    =  $post_ts;
						$prev_post_year  =  $post_year;
					} // End WHILE Loop

					/* If we've processed at least *one* post, close the ordered list */
					if ( ! is_null( $prev_post_ts ) ) {
				?>
				</ul>
				<?php } ?>

				</section><!--entry-->

			</article><!--post-->

        </section><!-- /#main -->

        <?php wp_reset_query(); ?>

        <?php woo_main_after(); ?>

        <?php get_sidebar(); ?>

    </div><!-- /#content -->

<?php get_footer(); ?>
