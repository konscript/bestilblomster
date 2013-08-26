<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Template Name: Image Gallery
 *
 * The image gallery page template displays a styled
 * image grid of a maximum of 60 posts with images attached.
 *
 * @package WooFramework
 * @subpackage Template
 */
 
 global $woo_options;
 get_header();
?>
       
    <div id="content" class="page col-full">
    
    	<?php woo_main_before(); ?>
    	
		<section id="main" class="col-left fix">

            <article <?php post_class('image-gallery-item'); ?>>
				
				<header>
					<h1><?php the_title(); ?></h1>
				</header>
                
				<section class="entry">

		            <?php
		            	if ( have_posts() ) { the_post();
		            		the_content();
		            	}
		            ?>
               		<?php query_posts( 'showposts=60&post_type=post' ); ?>
                	<?php
                		if ( have_posts() ) {
                			while ( have_posts() ) { the_post();
                			$wp_query->is_home = false;
                				woo_image( 'single=true&class=thumbnail alignleft' );
                			}
                		}
                	?>	
                </section>

            </article><!-- /.post -->                
                                                            
		</section><!-- /#main -->
		
		<?php woo_main_after(); ?>
		
        <?php get_sidebar(); ?>

    </div><!-- /#content -->
		
<?php get_footer(); ?>