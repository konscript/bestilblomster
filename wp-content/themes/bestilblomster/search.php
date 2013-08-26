<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Search Template
 *
 * The search template is used to display search results from the native WordPress search.
 *
 * If no search results are found, the user is assisted in refining their search query in
 * an attempt to produce an appropriate search results set for the user's search query.
 *
 * @package WooFramework
 * @subpackage Template
 */

 get_header();
 global $woo_options;
?>     
    <div id="content" class="col-full">
    
    	<?php woo_main_before(); ?>
    	
		<section id="main" class="col-left">
            	
		<?php if ( have_posts() ) : $count = 0; ?>
            
            <header class="archive-header"><h1><?php echo __( 'Search results:', 'woothemes' ) . ' '; the_search_query(); ?></h1></header>

			<div class="fix"></div>                
			        
			<?php woo_loop_before(); ?>
			
			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); $count++; ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to overload this in a child theme then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content', 'search' );
				?>

			<?php endwhile; ?>

			<?php else : ?>
	        
	            <article <?php post_class(); ?>>
	                <p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
	            </article><!-- /.post -->
	        
	        <?php endif; ?>
	        
	        <?php woo_loop_after(); ?>

			<?php woo_pagenav(); ?>
                
        </section><!-- /#main -->
        
        <?php woo_main_after(); ?>

        <?php get_sidebar(); ?>

    </div><!-- /#content -->
		
<?php get_footer(); ?>