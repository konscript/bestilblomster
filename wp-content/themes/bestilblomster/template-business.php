<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Template Name: Business
 *
 * The template for displaying business elements such as features and testimonials
 *
 * @package WooFramework
 * @subpackage Template
 */

 global $woo_options;
 get_header();

/**
 * The Variables
 *
 * Setup default variables, overriding them if the "Theme Options" have been saved.
 */

	$settings = array(
        'thumb_w' => 768,
        'thumb_h' => 300,
        'thumb_align' => 'aligncenter',
        'business_display_slider' => 'true',
        'business_display_features' => 'true',
        'business_display_testimonials' => 'true',
        'business_display_blog' => 'true'
	);

	$settings = woo_get_dynamic_values( $settings );
?>
    <!-- #content Starts -->
    <div id="content" class="col-full">

        <?php woo_main_before(); ?>

        <div class="business">

        <?php
            // Display WooSlider if activated and specified in theme options
            if ( 'true' == $settings['business_display_slider'] ) {
                do_action( 'wooslider' );
            }
            ?>
            <?php if (have_posts()) : $count = 0; ?>
            <?php while (have_posts()) : the_post(); $count++; ?>

                <div <?php post_class(); ?>>

                    <h1 class="title"><?php the_title(); ?></h1>

                    <?php if ( get_the_content() != '' ) { ?>
                    <div class="entry">
                        <?php the_content(); ?>
                    </div><!-- /.entry -->
                    <?php } ?>

                </div><!-- /.post -->


            <?php endwhile; else: ?>
            <?php endif; ?>
            <?php
            echo '<div class="woocommerce-wrap">';
            // Display Features if activated and specified in theme options
            if ( 'true' == $settings['business_display_features'] ) {
                do_action( 'woothemes_features' );
            }
            // Display Features if activated and specified in theme options
            if ( 'true' == $settings['business_display_testimonials'] ) {
                do_action( 'woothemes_testimonials' );
            }
            echo '</div>';
        ?>

        </div><!--/.business-->

        <?php if ( 'true' == $settings['business_display_blog'] ) { ?>

        <section id="main" class="col-left">

		<?php woo_loop_before(); ?>

        <?php
        	if ( get_query_var( 'paged') ) { $paged = get_query_var( 'paged' ); } elseif ( get_query_var( 'page') ) { $paged = get_query_var( 'page' ); } else { $paged = 1; }

        	$query_args = array(
        						'post_type' => 'post',
        						'paged' => $paged
        					);

        	$query_args = apply_filters( 'woo_blog_template_query_args', $query_args ); // Do not remove. Used to exclude categories from displaying here.

        	remove_filter( 'pre_get_posts', 'woo_exclude_categories_homepage' );

        	query_posts( $query_args );

        	if ( have_posts() ) {
        		$count = 0;
        		while ( have_posts() ) { the_post(); $count++;

					/* Include the Post-Format-specific template for the content.
					 * If you want to overload this in a child theme then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content', get_post_format() );

        		} // End WHILE Loop

        	} else {
        ?>
            <article <?php post_class(); ?>>
                <p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
            </article><!-- /.post -->
        <?php } // End IF Statement ?>

        <?php woo_loop_after(); ?>

        <?php woo_pagenav(); ?>
		<?php wp_reset_query(); ?>

        </section><!-- /#main -->

        <?php woo_main_after(); ?>

		<?php get_sidebar(); ?>

        <?php } ?>

    </div><!-- /#content -->

<?php get_footer(); ?>