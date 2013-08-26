<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Template Name: Archives Page
 *
 * The archives page template displays a conprehensive archive of the current
 * content of your website on a single page. 
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
			
			<article <?php post_class(); ?>>
			    
			    <header>
			    	<h1><?php the_title(); ?></h1>
			    </header>
			    
			    <section class="entry fix">
		            
		            <?php woo_loop_before(); ?>
		            
		            <?php
		            	if ( have_posts() ) { the_post();
		            		the_content();
		            	}
		            ?>
				    <h3><?php _e( 'The Last 30 Posts', 'woothemes' ); ?></h3>
																	  
				    <ul>											  
				        <?php
				        	query_posts( 'showposts=30' );
				        	if ( have_posts() ) {
				        		while ( have_posts() ) { the_post();
				        ?>
				            <?php $wp_query->is_home = false; ?>	  
				            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> - <?php the_time( get_option( 'date_format' ) ); ?> - <?php echo $post->comment_count; ?> <?php _e( 'comments', 'woothemes' ); ?></li>
				        <?php
				        		}
				        	}
				        	wp_reset_query();
				        ?>					  
				    </ul>	
				    
				    <?php woo_loop_after(); ?>										  
					
					<div id="archive-categories" class="fl" style="width:50%">												  
					    <h3><?php _e( 'Categories', 'woothemes' ); ?></h3>	  
					    <ul>											  
					        <?php wp_list_categories( 'title_li=&hierarchical=0&show_count=1' ); ?>	
					    </ul>											  
					</div><!--/#archive-categories-->			     												  

					<div id="archive-dates" class="fr" style="width:50%">												  
					    <h3><?php _e( 'Monthly Archives', 'woothemes' ); ?></h3>
																		  
					    <ul>											  
					        <?php wp_get_archives( 'type=monthly&show_post_count=1' ); ?>	
					    </ul>
					</div><!--/#archive-dates-->	 												  

				</section><!-- /.entry -->
			    			
			</article><!-- /.post -->                 
                
        </section><!-- /#main -->
        
        <?php woo_main_after(); ?>

        <?php get_sidebar(); ?>

    </div><!-- /#content -->
		
<?php get_footer(); ?>