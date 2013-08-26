<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * WooFramework Hooks.
 *
 * All hooks registered by the WooFramework.
 *
 * @package WordPress
 * @subpackage  WooFramework
 * @category  Core
 * @author  WooThemes
 * @since  2.0.0
 *
 * TABLE OF CONTENTS
 *
 * - woo_head()
 * - woo_top()
 * - woo_header_before()
 * - woo_header_inside()
 * - woo_header_after()
 * - woo_nav_before()
 * - woo_nav_inside()
 * - woo_nav_after()
 * - woo_content_before()
 * - woo_cotnent_after()
 * - woo_main_before()
 * - woo_main_after()
 * - woo_post_before()
 * - woo_post_after()
 * - woo_post_inside_before()
 * - woo_post_inside_after()
 * - woo_loop_before()
 * - woo_loop_after()
 * - woo_tumblog_content_before()
 * - woo_tumblog_content_after()
 * - woo_sidebar_before()
 * - woo_sidebar_inside_before()
 * - woo_sidebar_inside_after()
 * - woo_sidebar_after()
 * - woo_footer_top()
 * - woo_footer_before()
 * - woo_footer_inside()
 * - woo_footer_after()
 * - woo_foot()
 *
 * - woo_do_atomic()
 * - woo_apply_atomic()
 * - woo_get_query_context()
 */

// header.php
function woo_head() { woo_do_atomic( 'woo_head' ); }					
function woo_top() { woo_do_atomic( 'woo_top' ); }					
function woo_header_before() { woo_do_atomic( 'woo_header_before' ); }			
function woo_header_inside() { woo_do_atomic( 'woo_header_inside' ); }				
function woo_header_after() { woo_do_atomic( 'woo_header_after' ); }			
function woo_nav_before() { woo_do_atomic( 'woo_nav_before' ); }					
function woo_nav_inside() { woo_do_atomic( 'woo_nav_inside' ); }					
function woo_nav_after() { woo_do_atomic( 'woo_nav_after' ); }		

// Template files: 404, archive, single, page, sidebar, index, search
function woo_content_before() { woo_do_atomic( 'woo_content_before' ); }					
function woo_content_after() { woo_do_atomic( 'woo_content_after' ); }					
function woo_main_before() { woo_do_atomic( 'woo_main_before' ); }					
function woo_main_after() { woo_do_atomic( 'woo_main_after' ); }					
function woo_post_before() { woo_do_atomic( 'woo_post_before' ); }					
function woo_post_after() { woo_do_atomic( 'woo_post_after' ); }					
function woo_post_inside_before() { woo_do_atomic( 'woo_post_inside_before' ); }					
function woo_post_inside_after() { woo_do_atomic( 'woo_post_inside_after' ); }	
function woo_loop_before() { woo_do_atomic( 'woo_loop_before' ); }	
function woo_loop_after() { woo_do_atomic( 'woo_loop_after' ); }	

// Tumblog Functionality
function woo_tumblog_content_before() { woo_do_atomic( 'woo_tumblog_content_before', 'Before' ); }	
function woo_tumblog_content_after() { woo_do_atomic( 'woo_tumblog_content_after', 'After' ); }

// Sidebar
function woo_sidebar_before() { woo_do_atomic( 'woo_sidebar_before' ); }					
function woo_sidebar_inside_before() { woo_do_atomic( 'woo_sidebar_inside_before' ); }					
function woo_sidebar_inside_after() { woo_do_atomic( 'woo_sidebar_inside_after' ); }					
function woo_sidebar_after() { woo_do_atomic( 'woo_sidebar_after' ); }					

// footer.php
function woo_footer_top() { woo_do_atomic( 'woo_footer_top' ); }					
function woo_footer_before() { woo_do_atomic( 'woo_footer_before' ); }					
function woo_footer_inside() { woo_do_atomic( 'woo_footer_inside' ); }					
function woo_footer_after() { woo_do_atomic( 'woo_footer_after' ); }	
function woo_foot() { woo_do_atomic( 'woo_foot' ); }					

if ( ! function_exists( 'woo_do_atomic' ) ) {
/**
 * woo_do_atomic()
 * 
 * Adds contextual action hooks to the theme.  This allows users to easily add context-based content 
 * without having to know how to use WordPress conditional tags.  The theme handles the logic.
 *
 * An example of a basic hook would be 'woo_head'.  The woo_do_atomic() function extends that to 
 * give extra hooks such as 'woo_head_home', 'woo_head_singular', and 'woo_head_singular-page'.
 *
 * Major props to Ptah Dunbar for the do_atomic() function.
 * @link http://ptahdunbar.com/wordpress/smarter-hooks-context-sensitive-hooks
 *
 * @since 3.9.0
 * @uses woo_get_query_context() Gets the context of the current page.
 * @param string $tag Usually the location of the hook but defines what the base hook is.
 */
function woo_do_atomic( $tag = '', $args = '' ) {
	if ( !$tag ) return false;

	/* Do actions on the basic hook. */
	do_action( $tag, $args );
	/* Loop through context array and fire actions on a contextual scale. */
	foreach ( (array) woo_get_query_context() as $context )
		do_action( "{$tag}_{$context}", $args );		
} // End woo_do_atomic()
}

if ( ! function_exists( 'woo_apply_atomic' ) ) {
/**
 * woo_apply_atomic()
 * 
 * Adds contextual filter hooks to the theme.  This allows users to easily filter context-based content 
 * without having to know how to use WordPress conditional tags. The theme handles the logic.
 *
 * An example of a basic hook would be 'woo_entry_meta'.  The woo_apply_atomic() function extends 
 * that to give extra hooks such as 'woo_entry_meta_home', 'woo_entry_meta_singular' and 'woo_entry_meta_singular-page'.
 *
 * @since 3.9.0
 * @uses woo_get_query_context() Gets the context of the current page.
 * @param string $tag Usually the location of the hook but defines what the base hook is.
 * @param mixed $value The value to be filtered.
 * @return mixed $value The value after it has been filtered.
 */
function woo_apply_atomic( $tag = '', $value = '' ) {
	if ( ! $tag ) return false;
	/* Get theme prefix. */
	$pre = 'woo';
	/* Apply filters on the basic hook. */
	$value = apply_filters( "{$pre}_{$tag}", $value );
	/* Loop through context array and apply filters on a contextual scale. */
	foreach ( (array)woo_get_query_context() as $context )
		$value = apply_filters( "{$pre}_{$context}_{$tag}", $value );
	/* Return the final value once all filters have been applied. */
	return $value;
} // End woo_apply_atomic()
}

if ( ! function_exists( 'woo_get_query_context' ) ) {
/**
 * woo_get_query_context()
 *
 * Retrieve the context of the queried template.
 *
 * @since 3.9.0
 * @return array $query_context
 */
function woo_get_query_context() {
	global $wp_query, $query_context;
	
	/* If $query_context->context has been set, don't run through the conditionals again. Just return the variable. */
	if ( is_object( $query_context ) && isset( $query_context->context ) && is_array( $query_context->context ) ) {
		return $query_context->context;
	}

	unset( $query_context );
	$query_context = new stdClass();
	$query_context->context = array();

	/* Front page of the site. */
	if ( is_front_page() ) {
		$query_context->context[] = 'home';
	}

	/* Blog page. */
	if ( is_home() && ! is_front_page() ) {
		$query_context->context[] = 'blog';

	/* Singular views. */
	} elseif ( is_singular() ) {
		$query_context->context[] = 'singular';
		$query_context->context[] = "singular-{$wp_query->post->post_type}";
	
		/* Page Templates. */
		if ( is_page_template() ) {
			$to_skip = array( 'page', 'post' );
		
			$page_template = basename( get_page_template() );
			$page_template = str_replace( '.php', '', $page_template );
			$page_template = str_replace( '.', '-', $page_template );
		
			if ( $page_template && ! in_array( $page_template, $to_skip ) ) {
				$query_context->context[] = $page_template;
			}
		}
		
		$query_context->context[] = "singular-{$wp_query->post->post_type}-{$wp_query->post->ID}";
	}

	/* Archive views. */
	elseif ( is_archive() ) {
		$query_context->context[] = 'archive';

		/* Taxonomy archives. */
		if ( is_tax() || is_category() || is_tag() ) {
			$term = $wp_query->get_queried_object();
			$query_context->context[] = 'taxonomy';
			$query_context->context[] = $term->taxonomy;
			$query_context->context[] = "{$term->taxonomy}-" . sanitize_html_class( $term->slug, $term->term_id );
		}

		/* User/author archives. */
		elseif ( is_author() ) {
			$query_context->context[] = 'user';
			$query_context->context[] = 'user-' . sanitize_html_class( get_the_author_meta( 'user_nicename', get_query_var( 'author' ) ), $wp_query->get_queried_object_id() );
		}

		/* Time/Date archives. */
		else {
			if ( is_date() ) {
				$query_context->context[] = 'date';
				if ( is_year() )
					$query_context->context[] = 'year';
				if ( is_month() )
					$query_context->context[] = 'month';
				if ( get_query_var( 'w' ) )
					$query_context->context[] = 'week';
				if ( is_day() )
					$query_context->context[] = 'day';
			}
			if ( is_time() ) {
				$query_context->context[] = 'time';
				if ( get_query_var( 'hour' ) )
					$query_context->context[] = 'hour';
				if ( get_query_var( 'minute' ) )
					$query_context->context[] = 'minute';
			}
		}
	}

	/* Search results. */
	elseif ( is_search() ) {
		$query_context->context[] = 'search';
	/* Error 404 pages. */
	} elseif ( is_404() ) {
		$query_context->context[] = 'error-404';
	}
	
	return $query_context->context;
} // End woo_get_query_context()
}
?>