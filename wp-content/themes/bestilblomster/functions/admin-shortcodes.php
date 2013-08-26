<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------

TABLE OF CONTENTS

1. Woo Shortcodes
  1.1 Output shortcode JS in footer (in development)
2. Boxes
3. Buttons
4. Related Posts
5. Tweetmeme Button
6. Twitter Button
7. Digg Button
8. FaceBook Like Button
9. Columns
10. Horizontal Rule / Divider
11. Quote
12. Icon Links
13. jQuery Toggle (in development)
14. Facebook Share Button
15. Advanced Contact Form
16. Tabs
  16.1 A Single Tab
17. Dropcap
18. Highlight
19. Abbreviation
20. Typography (in development)
21. List Styles - Unordered List
22. List Styles - Ordered List
23. Social Icon
24. LinkedIn Button
  24.1 Load Javascript for LinkedIn Button
25. Google +1 Button
  25.1 Load Javascript for Google +1 Button
26. Twitter Follow Button
  26.1 Load Javascript for Twitter Follow Button
27. StumbleUpon Badge
28. Pinterest "Pin It" Button
  28.1 Load Javascript for Pinterest Button

-----------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------*/
/* 1. Woo Shortcodes  */
/*-----------------------------------------------------------------------------------*/

// Enable shortcodes in widget areas
add_filter( 'widget_text', 'do_shortcode' );

// Add stylesheet for shortcodes to HEAD (added to HEAD in admin-setup.php)
if ( ! function_exists( 'woo_shortcode_stylesheet' ) && get_option( 'framework_woo_disable_shortcodes' ) != 'true' ) {
	function woo_shortcode_stylesheet() {
		echo "\n" . "<!-- Woo Shortcodes CSS -->\n";
		echo '<link href="'. get_template_directory_uri() . '/functions/css/shortcodes.css" rel="stylesheet" type="text/css" />'."\n";
	} // End woo_shortcode_stylesheet()
}

// Replace WP autop formatting
if ( ! function_exists( 'woo_remove_wpautop' ) ) {
	function woo_remove_wpautop( $content ) {
		$content = do_shortcode( shortcode_unautop( $content ) );
		$content = preg_replace( '#^<\/p>|^<br \/>|<p>$#', '', $content );
		return $content;
	} // End woo_remove_wpautop()
}

/*-----------------------------------------------------------------------------------*/
/* 1.1 Output shortcode JS in footer */
/*-----------------------------------------------------------------------------------*/

add_action( 'wp_print_scripts', 'woo_register_shortcode_js', 10 );

function woo_register_shortcode_js () {
	wp_register_script( 'woo-shortcodes', get_template_directory_uri() . '/functions/js/shortcodes.js', array( 'jquery', 'jquery-ui-tabs' ), '5.0.0' );
} // End woo_register_shortcode_js()

add_action( 'wp_footer', 'woo_enqueue_shortcode_js', 10 );

function woo_enqueue_shortcode_js () {
	if ( ! is_admin() && defined( 'WOO_SHORTCODE_JS' ) ) {
		wp_enqueue_script( 'woo-shortcodes' );
		
		global $wp_scripts;
		$wp_scripts->to_do = array( 'woo-shortcodes' );
		
		wp_print_scripts();
	}
} // End woo_enqueue_shortcode_js()

/*-----------------------------------------------------------------------------------*/
/* 2. Boxes - box
/*-----------------------------------------------------------------------------------*/
/*

Optional arguments:
 - type: info, alert, tick, download, note
 - size: medium, large
 - style: rounded
 - border: none, full
 - icon: none OR full URL to a custom icon

*/
function woo_shortcode_box( $atts, $content = null ) {
   extract( shortcode_atts( array(	'type' => 'normal',
   									'size' => '',
   									'style' => '',
   									'border' => '',
   									'icon' => '' ), $atts ) );

   	$custom = '';
   	if ( $icon == 'none' )
   		$custom = ' style="padding-left:15px;background-image:none;"';
   	elseif ( $icon )
   		$custom = ' style="padding-left:50px;background-image:url( ' . esc_attr( esc_url( $icon ) ) . ' ); background-repeat:no-repeat; background-position:20px 45%;"';

   	return '<div class="woo-sc-box ' . esc_attr( $type ) . ' ' . esc_attr( $size ) . ' ' . esc_attr( $style ) . ' ' . esc_attr( $border ) . '"' . $custom . '>' . wp_kses_post( do_shortcode( woo_remove_wpautop( $content ) ) ) . '</div>';
} // End woo_shortcode_box()

add_shortcode( 'box', 'woo_shortcode_box' );

/*-----------------------------------------------------------------------------------*/
/* 3. Buttons - button
/*-----------------------------------------------------------------------------------*/
/*

Optional arguments:
 - size: small, large
 - style: info, alert, tick, download, note
 - color: red, green, black, grey OR custom hex color (e.g #000000)
 - border: border color (e.g. red or #000000)
 - text: black (for light color background on button)
 - class: custom class
 - link: button link (e.g http://www.woothemes.com)
 - window: true/false

*/
function woo_shortcode_button( $atts, $content = null ) {
   	extract( shortcode_atts( array(	'size' => '',
   									'style' => '',
   									'bg_color' => '',
   									'color' => '',
   									'border' => '',
   									'text' => '',
   									'class' => '',
   									'link' => '#',
   									'window' => '' ), $atts ) );


   	// Set custom background and border color
   	$color_output = '';
   	if ( $color ) {
   		$preset_colors = array( 'red', 'orange', 'green', 'aqua', 'teal', 'purple', 'pink', 'silver' );
   		if ( in_array( $color, $preset_colors ) ) {
	   		$class .= ' ' . $color;
   		} else {
		   	if ( $border ){
		   		$border_out = $border;
		   	} else {
		   		$border_out = $color;
		   	}

	   		$color_output = 'style="background:' . esc_attr( $color ) . ';border-color:' . esc_attr( $border_out ) . '"';

	   		// add custom class
	   		$class .= ' custom';
   		}
   	} else {
   		if ( $border )
		   		$border_out = $border;
		   	else
		   		$border_out = $bg_color;

	   		$color_output = 'style="background:' . esc_attr( $bg_color ) . ';border-color:' . esc_attr( $border_out ) . '"';

	   		// add custom class
	   		$class .= ' custom';
   	}

	$class_output = '';

	// Set text color
	if ( $text ) $class_output .= ' dark';
	// Set class
	if ( $class ) $class_output .= ' '.$class;
	// Set Size
	if ( $size ) $class_output .= ' '.$size;
	// Set window target
	if ( $window ) $window = 'target="_blank" ';

   	$output = '<a ' . $window . 'href="' . esc_attr( esc_url( $link ) ) . '" class="woo-sc-button' . esc_attr( $class_output ) . '" ' . $color_output . '><span class="woo-' . esc_attr( $style ) . '">' . wp_kses_post( woo_remove_wpautop( $content ) ) . '</span></a>';
   	return $output;
} // End woo_shortcode_button()

add_shortcode( 'button', 'woo_shortcode_button' );


/*-----------------------------------------------------------------------------------*/
/* 4. Related Posts - related_posts
/*-----------------------------------------------------------------------------------*/
/*

Optional arguments:
 - limit: number of posts to show (default: 5)
 - image: thumbnail size, 0 = off (default: 0)
*/

function woo_shortcode_related_posts ( $atts ) {
	global $post, $wp_version;
	
		wp_reset_query(); // Make sure we have a fresh query before we start.
	
		$defaults = array(
					'limit' => 5, 
					'image' => 0, 
					'float' => 'none'
				 );
	
		$atts = shortcode_atts( $defaults, $atts );
	
		// This function requires at least WordPress Version 3.1.
		if ( $wp_version < 3.1 ) {
			return _dep_woo_shortcode_related_posts( $atts );
		} else {
	
		// Sanitize float attribute.
		if ( isset( $atts['float'] ) && ! in_array( $atts['float'], array( 'none', 'left', 'right' ) ) ) { $atts['float'] = 'none'; }
	
		// Float translation array.
		$floats = array( 'none' => '', 'left' => 'fl', 'right' => 'fr' );
	
		$css_class = 'woo-sc-related-posts';
	
		extract( $atts );
		
		if ( $float != 'none' ) { $css_class .= ' ' . $floats[$float]; }
		
		$output = '';
		
		$post_type = get_post_type( $post->ID );
		
		$post_type_obj = get_post_type_object( $post_type );
		
		$taxonomies_string = 'post_tag, category';
		$taxonomies = array( 'post_tag', 'category' );
		
		if ( isset( $post_type_obj->taxonomies ) && count( $post_type_obj->taxonomies ) > 0 ) {
			$taxonomies_string = join( ', ', $post_type_obj->taxonomies );
			$taxonomies = $post_type_obj->taxonomies;
		}
	 	
	 	// Clean up our taxonomies for use in the query.
	 	if ( count( $taxonomies ) ) {
	 		foreach ( $taxonomies as $k => $v ) {
	 			$taxonomies[$k] = trim( $v );
	 		}
	 	}
	 	
	 	// Determine which terms we're going to relate to this entry.
	 	$related_terms = array();
	 	
	 	foreach ( $taxonomies as $t ) {
	 		$terms = get_the_terms( $post->ID, $t );
	 		
	 		if ( ! empty( $terms ) ) {
	 			foreach ( $terms as $k => $v ) {
	 				$related_terms[$t][$v->term_id] = $v->slug;
	 			}
	 		}
	 	}
		
		$specific_terms = array();
		foreach ( $related_terms as $k => $v ) {
			foreach ( $v as $i => $j ) {
				$specific_terms[] = $j;
			}
		}
		
		$query_args = array(
 						'limit' => $atts['limit'], 
 						'post_type' => $post_type, 
 						'taxonomies' => $taxonomies_string, 
 						'specific_terms' => $specific_terms, 
 						'order' => 'DESC', 
 						'orderby' => 'date', 
 						'exclude' => array( $post->ID )
 					);
		
		$posts = woo_get_posts_by_taxonomy( $query_args );
		
		if ( count( (array)$posts ) ) {
			
			$output .= '<div class="' . $css_class . '">' . "\n";
		
			$output .= '<ul>' . "\n";
			
			foreach ( $posts as $post ) {
				setup_postdata( $post );
				
				if ( $image <= 0 ) {
					$image_html = '';
				} else {
					$image_html = '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" class="thumbnail">' . woo_image( 'link=img&width=' . $image . '&height=' . esc_attr( $image ) . '&return=true&id=' . esc_attr( $post->ID ) ) . '</a>' . "\n";
				}
				
				$output .= '<li class="post-id-' . esc_attr( $post->ID ) . '">' . "\n" . $image_html . "\n" . '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" title="' . the_title_attribute( array( 'echo' => 0 ) ) . '" class="related-title"><span>' . get_the_title( $post->ID )  . '</span></a>' . "\n" . '</li>' . "\n";
			}
			
			$output .= '</ul>' . "\n";
			$output .= '<div class="fix"></div><!--/.fix-->' . "\n";
			$output .= '</div><!--/.woo-sc-related-posts-->';
		}
		
		wp_reset_postdata();
		
		return apply_filters( 'woo_shortcode_related_posts', $output, $atts );
	
	} // End IF Statement (version check)
} // End woo_shortcode_related_posts()

add_shortcode( 'related_posts', 'woo_shortcode_related_posts' );

/*-----------------------------------------------------------------------------------*/
/* Deprecated: Related Posts Shortcode.
/*
/* Used for WordPress version 3.0 or less.
/*-----------------------------------------------------------------------------------*/

function _dep_woo_shortcode_related_posts( $atts ) { trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s of the WooFramework! Please upgrade your WordPress to the latest version to use the latest functionality.', 'woothemes' ), '_dep_woo_shortcode_related_posts', '5.4' ) ); }

/*-----------------------------------------------------------------------------------*/
/* 5. Tweetmeme button - tweetmeme
/*-----------------------------------------------------------------------------------*/
/*

Source: http://help.tweetmeme.com/2009/04/06/tweetmeme-button/

Optional arguments:
 - link: specify URL directly
 - style: compact
 - source: username
 - float: none, left, right (default: left)

*/
function woo_shortcode_tweetmeme($atts, $content = null) {
   	extract(shortcode_atts(array(	'link' => '',
   									'style' => '',
   									'source' => '',
   									'float' => 'left'), $atts));
	$output = '';

	if ( $link == '' ) {
		global $post;
		$link = get_permalink( $post->ID );
	}

	if ( $link )
		$output .= "tweetmeme_url = '" . esc_url( $link ) . "';";

	if ( $style )
		$output .= "tweetmeme_style = 'compact';";

	if ( $source )
		$output .= "tweetmeme_source = '" . esc_attr( $source ) . "';";

	if ( $link OR $style )
		$output = '<script type="text/javascript">' . $output . '</script>';

	$output .= '<div class="woo-tweetmeme '. esc_attr( $float ) .'"><script type="text/javascript" src="' . esc_url( 'http://tweetmeme.com/i/scripts/button.js' ) . '"></script></div>';
	return $output;

} // End woo_shortcode_tweetmeme()

add_shortcode( 'tweetmeme', 'woo_shortcode_tweetmeme' );

/*-----------------------------------------------------------------------------------*/
/* 6. Twitter button - twitter
/*-----------------------------------------------------------------------------------*/
/*

Source: http://twitter.com/goodies/tweetbutton

Optional arguments:
 - style: vertical, horizontal, none ( default: vertical )
 - url: specify URL directly
 - source: username to mention in tweet
 - related: related account
 - text: optional tweet text (default: title of page)
 - float: none, left, right (default: left)
 - lang: fr, de, es, js (default: english)
 - use_post_url: automatically retrieve the URL to the specific post (useful on archive screens)
*/
function woo_shortcode_twitter($atts, $content = null) {
   	global $post;
   	extract(shortcode_atts(array(	'url' => '',
   									'style' => '',
   									'source' => '',
   									'text' => '',
   									'related' => '',
   									'lang' => '',
   									'float' => 'left', 
   									'use_post_url' => 'false', 
   									'recommend' => '', 
   									'hashtag' => '', 
   									'size' => '', 
   									 ), $atts));
	$output = '';

	if ( $url )
		$output .= ' data-url="' . esc_url( $url ) . '"';

	if ( $source )
		$output .= ' data-via="' . esc_attr( $source ) . '"';

	if ( $text )
		$output .= ' data-text="' . esc_attr( $text ) . '"';

	if ( $related )
		$output .= ' data-related="' . esc_attr( $related ) . '"';
		
	if ( $hashtag )
		$output .= ' data-hashtags="' . esc_attr( $hashtag ) . '"';

	if ( $size )
		$output .= ' data-size="' . esc_attr( $size ) . '"';

	if ( $lang )
		$output .= ' data-lang="' . esc_attr( $lang ) . '"';
		
	if ( $style != '' ) {
		$output .= 'data-count="' . esc_attr( $style ) . '"';
	}
		
	if ( $use_post_url == 'true' && $url == '' ) {
		$output .= ' data-url="' . get_permalink( $post->ID ) . '"';
	}

	$output = '<div class="woo-sc-twitter ' . esc_attr( $float ) . '"><a href="' . esc_url( 'http://twitter.com/share' ) . '" class="twitter-share-button"'. $output .'>' . __( 'Tweet', 'woothemes' ) . '</a><script type="text/javascript" src="' . esc_url ( 'http://platform.twitter.com/widgets.js' ) . '"></script></div>';
	return $output;

} // End woo_shortcode_twitter()

add_shortcode( 'twitter', 'woo_shortcode_twitter' );

/*-----------------------------------------------------------------------------------*/
/* 7. Digg Button - digg
/*-----------------------------------------------------------------------------------*/
/*

Source: http://about.digg.com/button

Optional arguments:
 - link: specify URL directly
 - title: specify a title (must add link also)
 - style: medium, large, compact, icon (default: medium)
 - float: none, left, right (default: left)
*/
function woo_shortcode_digg($atts, $content = null) {
   	extract(shortcode_atts(array(	'link' => '',
   									'title' => '',
   									'style' => 'medium',
   									'float' => 'left' ), $atts));
	$output = "
	<script type=\"text/javascript\">
	(function() {
	var s = document.createElement( 'SCRIPT'), s1 = document.getElementsByTagName( 'SCRIPT')[0];
	s.type = 'text/javascript';
	s.async = true;
	s.src = 'http://widgets.digg.com/buttons.js';
	s1.parentNode.insertBefore(s, s1);
	})();
	</script>
	";

	// Add custom URL
	if ( $link ) {
		// Add custom title
		if ( $title )
			$title = '&amp;title=' . $title;

		$link = ' href="' . esc_url( 'http://digg.com/submit?url='. $link . $title ) . '"';
	}

	if ( $link == '' ) {
		global $post;
		$link = get_permalink( $post->ID );
	}

	if ( $style == "large" )
		$style = "Large";
	elseif ( $style == "compact" )
		$style = "Compact";
	elseif ( $style == "icon" )
		$style = "Icon";
	else
		$style = "Medium";

	$output .= '<div class="woo-digg ' . esc_attr( $float ) . '"><a class="DiggThisButton Digg' . esc_attr( $style ) . '"' . esc_url( $link ) . '></a></div>';
	return $output;

} // End woo_shortcode_digg()

add_shortcode( 'digg', 'woo_shortcode_digg' );


/*-----------------------------------------------------------------------------------*/
/* 8. Facebook Like Button - fblike
/*-----------------------------------------------------------------------------------*/
/*

Source: http://developers.facebook.com/docs/reference/plugins/like

Optional arguments:
 - float: none (default), left, right
 - url: link you want to share (default: current post ID)
 - style: standard (default), button
 - showfaces: true or false (default)
 - width: 450
 - verb: like (default) or recommend
 - colorscheme: light (default), dark
 - font: arial (default), lucida grande, segoe ui, tahoma, trebuchet ms, verdana

*/
function woo_shortcode_fblike($atts, $content = null) {
   	extract(shortcode_atts(array(	'float' => 'none',
   									'url' => '',
   									'style' => 'standard',
   									'showfaces' => 'false',
   									'width' => '450',
   									'verb' => 'like',
   									'colorscheme' => 'light',
   									'font' => 'arial', 
   									'locale' => 'en_US' ), $atts));

	global $post;

	if ( ! $post ) {
		$post = new stdClass();
		$post->ID = 0;
	}

	$allowed_styles = array( 'standard', 'button_count', 'box_count' );

	if ( ! in_array( $style, $allowed_styles ) ) { $style = 'standard'; } // End IF Statement

	if ( ! $url ) {
		$url = get_permalink( $post->ID );
	}

	$height = '65';
	if ( $showfaces == 'true')
		$height = '100';

	if ( ! $width || ! is_numeric( $width ) ) { $width = 450; } // End IF Statement

	// Set the width to "auto" if "showfaces" is off and the default width is still set.
	$widthpx = $width . 'px';
	if ( $width == 450 && $showfaces == 'false' ) { $widthpx = 'auto'; }
	
	// Set the height to 20 if "showfaces" is disabled and the style is either "standard" or "button_count".
	if ( $showfaces == 'false' && ( $style != 'box_count' ) ) { $height = 25; }

	switch ( $float ) {
		case 'left':
			$float = 'fl';
		break;

		case 'right':
			$float = 'fr';
		break;

		default:
		break;
	}
	
	$src_url = 'http://www.facebook.com/plugins/like.php?href=' . esc_url( $url ) . '&amp;layout=' . urlencode( $style ) . '&amp;show_faces=' . urlencode( $showfaces ) . '&amp;width=' . urlencode( $width ) . '&amp;action=' . urlencode( $verb ) . '&amp;colorscheme=' . urlencode( $colorscheme ) . '&amp;font=' . urlencode( $font ) . '&locale=' . urlencode( $locale ) . '';
	$output = '
<div class="woo-fblike ' . esc_attr( $float ) . '">
<iframe src="' . esc_url( $src_url ) . '" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:' . esc_attr( $widthpx ) . '; height:' . esc_attr( $height ) . 'px;"></iframe>
</div>
	';
	return $output;
} // End woo_shortcode_fblike()

add_shortcode( 'fblike', 'woo_shortcode_fblike' );


/*-----------------------------------------------------------------------------------*/
/* 9. Columns
/*-----------------------------------------------------------------------------------*/
/*

Description:

Columns are named with this convention Xcol_Y where X is the total number of columns and Y is
the number of columns you want this column to span. Add _last behind the shortcode if it is the
last column.

*/

/* ============= Two Columns ============= */

function woo_shortcode_twocol_one($atts, $content = null) {
   return '<div class="twocol-one">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'twocol_one', 'woo_shortcode_twocol_one' );

function woo_shortcode_twocol_one_last($atts, $content = null) {
   return '<div class="twocol-one last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'twocol_one_last', 'woo_shortcode_twocol_one_last' );


/* ============= Three Columns ============= */

function woo_shortcode_threecol_one($atts, $content = null) {
   return '<div class="threecol-one">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'threecol_one', 'woo_shortcode_threecol_one' );

function woo_shortcode_threecol_one_last($atts, $content = null) {
   return '<div class="threecol-one last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'threecol_one_last', 'woo_shortcode_threecol_one_last' );

function woo_shortcode_threecol_two($atts, $content = null) {
   return '<div class="threecol-two">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'threecol_two', 'woo_shortcode_threecol_two' );

function woo_shortcode_threecol_two_last($atts, $content = null) {
   return '<div class="threecol-two last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'threecol_two_last', 'woo_shortcode_threecol_two_last' );

/* ============= Four Columns ============= */

function woo_shortcode_fourcol_one($atts, $content = null) {
   return '<div class="fourcol-one">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fourcol_one', 'woo_shortcode_fourcol_one' );

function woo_shortcode_fourcol_one_last($atts, $content = null) {
   return '<div class="fourcol-one last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fourcol_one_last', 'woo_shortcode_fourcol_one_last' );

function woo_shortcode_fourcol_two($atts, $content = null) {
   return '<div class="fourcol-two">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fourcol_two', 'woo_shortcode_fourcol_two' );

function woo_shortcode_fourcol_two_last($atts, $content = null) {
   return '<div class="fourcol-two last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fourcol_two_last', 'woo_shortcode_fourcol_two_last' );

function woo_shortcode_fourcol_three($atts, $content = null) {
   return '<div class="fourcol-three">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fourcol_three', 'woo_shortcode_fourcol_three' );

function woo_shortcode_fourcol_three_last($atts, $content = null) {
   return '<div class="fourcol-three last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fourcol_three_last', 'woo_shortcode_fourcol_three_last' );

/* ============= Five Columns ============= */

function woo_shortcode_fivecol_one($atts, $content = null) {
   return '<div class="fivecol-one">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fivecol_one', 'woo_shortcode_fivecol_one' );

function woo_shortcode_fivecol_one_last($atts, $content = null) {
   return '<div class="fivecol-one last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fivecol_one_last', 'woo_shortcode_fivecol_one_last' );

function woo_shortcode_fivecol_two($atts, $content = null) {
   return '<div class="fivecol-two">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fivecol_two', 'woo_shortcode_fivecol_two' );

function woo_shortcode_fivecol_two_last($atts, $content = null) {
   return '<div class="fivecol-two last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fivecol_two_last', 'woo_shortcode_fivecol_two_last' );

function woo_shortcode_fivecol_three($atts, $content = null) {
   return '<div class="fivecol-three">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fivecol_three', 'woo_shortcode_fivecol_three' );

function woo_shortcode_fivecol_three_last($atts, $content = null) {
   return '<div class="fivecol-three last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fivecol_three_last', 'woo_shortcode_fivecol_three_last' );

function woo_shortcode_fivecol_four($atts, $content = null) {
   return '<div class="fivecol-four">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fivecol_four', 'woo_shortcode_fivecol_four' );

function woo_shortcode_fivecol_four_last($atts, $content = null) {
   return '<div class="fivecol-four last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'fivecol_four_last', 'woo_shortcode_fivecol_four_last' );


/* ============= Six Columns ============= */

function woo_shortcode_sixcol_one($atts, $content = null) {
   return '<div class="sixcol-one">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'sixcol_one', 'woo_shortcode_sixcol_one' );

function woo_shortcode_sixcol_one_last($atts, $content = null) {
   return '<div class="sixcol-one last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'sixcol_one_last', 'woo_shortcode_sixcol_one_last' );

function woo_shortcode_sixcol_two($atts, $content = null) {
   return '<div class="sixcol-two">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'sixcol_two', 'woo_shortcode_sixcol_two' );

function woo_shortcode_sixcol_two_last($atts, $content = null) {
   return '<div class="sixcol-two last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'sixcol_two_last', 'woo_shortcode_sixcol_two_last' );

function woo_shortcode_sixcol_three($atts, $content = null) {
   return '<div class="sixcol-three">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'sixcol_three', 'woo_shortcode_sixcol_three' );

function woo_shortcode_sixcol_three_last($atts, $content = null) {
   return '<div class="sixcol-three last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'sixcol_three_last', 'woo_shortcode_sixcol_three_last' );

function woo_shortcode_sixcol_four($atts, $content = null) {
   return '<div class="sixcol-four">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'sixcol_four', 'woo_shortcode_sixcol_four' );

function woo_shortcode_sixcol_four_last($atts, $content = null) {
   return '<div class="sixcol-four last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'sixcol_four_last', 'woo_shortcode_sixcol_four_last' );

function woo_shortcode_sixcol_five($atts, $content = null) {
   return '<div class="sixcol-five">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'sixcol_five', 'woo_shortcode_sixcol_five' );

function woo_shortcode_sixcol_five_last($atts, $content = null) {
   return '<div class="sixcol-five last">' . woo_remove_wpautop($content) . '</div>';
}
add_shortcode( 'sixcol_five_last', 'woo_shortcode_sixcol_five_last' );


/*-----------------------------------------------------------------------------------*/
/* 10. Horizontal Rule / Divider - hr - divider
/*-----------------------------------------------------------------------------------*/
/*
Description: Use to separate text.
*/
function woo_shortcode_hr($atts, $content = null) {
   return '<div class="woo-sc-hr"></div>';
} // End woo_shortcode_hr()
add_shortcode( 'hr', 'woo_shortcode_hr' );

function woo_shortcode_divider($atts, $content = null) {
   return '<div class="woo-sc-divider"></div>';
} // End woo_shortcode_divider()
add_shortcode( 'divider', 'woo_shortcode_divider' );

function woo_shortcode_divider_flat($atts, $content = null) {
   return '<div class="woo-sc-divider flat"></div>';
} // End woo_shortcode_divider_flat()
add_shortcode( 'divider_flat', 'woo_shortcode_divider_flat' );


/*-----------------------------------------------------------------------------------*/
/* 11. Quote - quote
/*-----------------------------------------------------------------------------------*/
/*

Optional arguments:
 - style: boxed
 - float: left, right

*/
function woo_shortcode_quote($atts, $content = null) {
   	extract(shortcode_atts(array(	'style' => '',
   									'float' => ''), $atts));
   $class = '';
   if ( $style )
   		$class .= ' '.$style;
   if ( $float )
   		$class .= ' '.$float;

   return '<div class="woo-sc-quote' . esc_attr( $class ) . '"><p>' . woo_remove_wpautop($content) . '</p></div>';
} // End woo_shortcode_quote()
add_shortcode( 'quote', 'woo_shortcode_quote' );

/*-----------------------------------------------------------------------------------*/
/* 12. Icon links - ilink
/*-----------------------------------------------------------------------------------*/
/*

Optional arguments:
 - style: download, note, tick, info, alert
 - url: the url for your link
 - icon: add an url to a custom icon
 - title: optional title attribute

*/
function woo_shortcode_ilink( $atts, $content = null ) {
   	extract( shortcode_atts( array( 'style' => 'info', 'url' => '', 'icon' => '', 'title' => '' ), $atts ) );

	$atts = '';
   	if ( $icon != '' ) {
   		$atts .= ' style="background: url( ' . esc_url( $icon ) . ') no-repeat left 40%;"';
   	}
   	if ( $title != '' ) {
   		$atts .= ' title="' . esc_attr( $title ) . '"';
   	}
   	
   	return '<span class="woo-sc-ilink"><a class="' . esc_attr( $style ) . '" href="' . esc_url( $url ) . '"' . $atts . '>' . woo_remove_wpautop( $content ) . '</a></span>';
} // End woo_shortcode_ilink()
add_shortcode( 'ilink', 'woo_shortcode_ilink' );

/*-----------------------------------------------------------------------------------*/
/* 13. jQuery Toggle
/*-----------------------------------------------------------------------------------*/
/*

}

Optional arguments:
 - title: The text in the main trigger
 - hide: Hide the toggle box on load
 - display_main_trigger: Display the main trigger on the toggle

*/
function woo_shortcode_toggle ( $atts, $content = null ) {
		// Instruct the shortcode JavaScript to load.
		if ( ! defined( 'WOO_SHORTCODE_JS' ) ) { define( 'WOO_SHORTCODE_JS', 'load' ); }

		$defaults = array(
							'title_open' => __( 'Hide the Content', 'woothemes' ),
							'title_closed' => __( 'Show the Content', 'woothemes' ),
							'hide' => 'yes',
							'display_main_trigger' => 'yes',
							'style' => 'default',
							'border' => 'yes',
							'excerpt_length' => '0',
							'include_excerpt_html' => 'no',
							'read_more_text' => __( 'Read More', 'woothemes' ),
							'read_less_text' => __( 'Read Less', 'woothemes' )
						);

		extract( shortcode_atts( $defaults, $atts ) );

		$title = '';
		$class = '';

		$class_open = ' toggle-' . sanitize_title( $title_open );

		$class_closed = ' toggle-' . sanitize_title( $title_closed );

		if ( $hide == 'yes' ) {
			$class .= $class_closed . ' closed'; $title = $title_closed;
		} else {
			$class .= $class_open . ' open'; $title = $title_open;
		} // End IF Statement

		$main_trigger = '';

		if ( $display_main_trigger == 'yes' ) {

			$main_trigger = '<h4 class="toggle-trigger"><a href="#">' . $title . '</a></h4>' . "\n";

		} // End IF Statement

		// Add the alternate style to the CSS class.
		$class .= ' ' . $style;

		// Add the border class, if necessary.
		if ( $border == 'yes' ) { $class .= ' border'; } // End IF Statement

		// If the excerpt length is greater than 0, apply the excerpt logic.
		$excerpt_length = intval( $excerpt_length );

		if ( $excerpt_length > 0 ) {
			$orig_content = $content;

			if ( $include_excerpt_html == 'no' ) {
				$content = strip_tags( $content );
			}

			$excerpt = substr( $content, 0, $excerpt_length );

			$more_link = '<a href="#read-more" class="more-link read-more" readless="' . esc_attr( $read_less_text ) . '">' . $read_more_text . '</a>';

			$content = '<span class="excerpt">' . $excerpt . '</span><!--/.excerpt-->' . "\n" . $more_link . "\n" . '<span class="more-text closed">' . substr( $content, $excerpt_length, strlen( $content ) ) . '</span><!--/.more-text-->' . "\n";
		}

		return '<div class="shortcode-toggle' . esc_attr( $class ) . '">' . $main_trigger . '<div class="toggle-content">' . do_shortcode( $content ) . '</div><!--/.toggle-content-->' . "\n" . '<input type="hidden" name="title_open" value="' . esc_attr( $title_open ) . '" /><input type="hidden" name="title_closed" value="' . esc_attr( $title_closed ) . '" />' . '</div><!--/.shortcode-toggle-->';
} // End woo_shortcode_toggle()

add_shortcode( 'toggle', 'woo_shortcode_toggle', 99 );

/*-----------------------------------------------------------------------------------*/
/* 14. Facebook Share Button - fbshare
/*-----------------------------------------------------------------------------------*/
/*

Source: http://developers.facebook.com/docs/share

Optional arguments:
 - type: box_count, button_count, button (default), icon_link, or icon
 - float: none, left, right (default: left)

*/
function woo_shortcode_fbshare($atts, $content = null) {
   	extract( shortcode_atts( array( 'url' => '', 'type' => 'button', 'float' => 'left' ), $atts ) );

	global $post;

	if ( isset( $url ) && $url == '' && isset( $post ) ) { $url = get_permalink( $post->ID ); } // End IF Statement

	$output = '
<div class="woo-fbshare ' . esc_attr( $float ) . '">
<a name="fb_share" type="' . esc_attr(  $type ) . '" share_url="' . esc_url( $url ) . '">' . woo_remove_wpautop( $content ) . '</a>
<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript">
</script>
</div>
	';
	return $output;
} // End woo_shortcode_fbshare()
add_shortcode( 'fbshare', 'woo_shortcode_fbshare' );


/*-----------------------------------------------------------------------------------*/
/* 15. Advanced Contact Form - contact_form
/*-----------------------------------------------------------------------------------*/
/*

Optional arguments:
 - email: The e-mail address to which the form will send (defaults to woo_contactform_email).
 - subject: The subject of the e-mail (defaults to "Message via the contact form".
 - button_text: Optionally change the text of the "submit" button.

 - Advanced form fields functionality, for creating dynamic form fields:
 --- Text Input: text_fieldname="Text Field Label|Optional Default Text"
 --- Select Box: select_fieldname="Select Box Label|key=value,key=value,key=value"
 --- Textarea Input: textarea_fieldname="Textarea Field Label|Optional Default Text|Optional Number of Rows|Optional Number of Columns"
 --- Checkbox Input: checkbox_fieldname="Checkbox Field Label|Value of the Checkbox|Checked By Default"
 --- Radio Button Input: radio_fieldname="Radio Field Label|key=value,key=value,key=value|Optional Default Value"

*/

function woo_shortcode_contactform ( $atts, $content = null ) {
		$defaults = array(
						'email' => get_option( 'woo_contactform_email'),
						'subject' => __( 'Message via the contact form', 'woothemes' ),
						'button_text' => apply_filters( 'woo_contact_form_button_text', __( 'Submit', 'woothemes' ) ), 
						'show_default_fields' => 'yes'
						);

		extract( shortcode_atts( $defaults, $atts ) );

		// Extract the dynamic fields as well, if they don't have a value in $defaults.
		$html = '';
		$dynamic_atts = array();
		$formatted_dynamic_atts = array();
		$error_messages = array();

		if ( is_array( $atts ) ) {
			foreach ( $atts as $k => $v ) {
				${$k} = $v;

				$dynamic_atts[$k] = ${$k};
			}
		}

		// Parse dynamic fields.
		if ( count( $dynamic_atts ) ) {
			foreach ( $dynamic_atts as $k => $v ) {

				/* Parse the radio buttons.
				--------------------------------------------------*/

				if ( substr( $k, 0, 6 ) == 'radio_' ) {
					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The options.
					if ( array_key_exists( 1, $params ) ) { $options_string = $params[1]; } else { $options_string = ''; } // End IF Statement

					// The default value.
					if ( array_key_exists( 2, $params ) ) { $default_value = $params[2]; } else { $default_value = ''; } // End IF Statement

					// Format the options.
					$options = array();

					if ( $options_string ) {

						$options_raw = explode( ',', $options_string );

						if ( count( $options_raw ) ) {

							foreach ( $options_raw as $o ) {

								$o = trim( $o );

								$is_formatted = strpos( $o, '=' );

								// It's not formatted how we'd like it, so just add the value is both the value and label.
								if ( $is_formatted === false ) {

									$options[$o] = $o;

								// That's more like it. A separate value and label.
								} else {

									$option_data = explode( '=', $o );

									$options[$option_data[0]] = $option_data[1];

								} // End IF Statement

							} // End FOREACH Loop

						} // End IF Statement

					} // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'options' => $options, 'default_value' => $default_value );

				} // End IF Statement

				/* Parse the radio buttons.
				--------------------------------------------------*/

				if ( substr( $k, 0, 6 ) == 'radio_' ) {

					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The options.
					if ( array_key_exists( 1, $params ) ) { $options_string = $params[1]; } else { $options_string = ''; } // End IF Statement

					// The default value.
					if ( array_key_exists( 2, $params ) ) { $default_value = $params[2]; } else { $default_value = ''; } // End IF Statement

					// Format the options.
					$options = array();

					if ( $options_string ) {

						$options_raw = explode( ',', $options_string );

						if ( count( $options_raw ) ) {

							foreach ( $options_raw as $o ) {

								$o = trim( $o );

								$is_formatted = strpos( $o, '=' );

								// It's not formatted how we'd like it, so just add the value is both the value and label.
								if ( $is_formatted === false ) {

									$options[$o] = $o;

								// That's more like it. A separate value and label.
								} else {

									$option_data = explode( '=', $o );

									$options[$option_data[0]] = $option_data[1];

								} // End IF Statement

							} // End FOREACH Loop

						} // End IF Statement

					} // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'options' => $options, 'default_value' => $default_value );

				} // End IF Statement

				/* Parse the checkbox inputs.
				--------------------------------------------------*/

				if ( substr( $k, 0, 9 ) == 'checkbox_' ) {

					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The value of the checkbox.
					if ( array_key_exists( 1, $params ) ) { $value = $params[1]; } else { $value = ''; } // End IF Statement

					// Checked by default?
					if ( array_key_exists( 1, $params ) ) { $checked = $params[2]; } else { $checked = ''; } // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'value' => $value, 'checked' => $checked );

				} // End IF Statement

				/* Parse the text inputs.
				--------------------------------------------------*/

				if ( substr( $k, 0, 5 ) == 'text_' ) {

					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The default text.
					if ( array_key_exists( 1, $params ) ) { $default_text = $params[1]; } else { $default_text = ''; } // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'default_text' => $default_text );

				} // End IF Statement

				/* Parse the select boxes.
				--------------------------------------------------*/

				if ( substr( $k, 0, 7 ) == 'select_' ) {

					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The options.
					if ( array_key_exists( 1, $params ) ) { $options_string = $params[1]; } else { $options_string = ''; } // End IF Statement

					// Format the options.
					$options = array();

					if ( $options_string ) {

						$options_raw = explode( ',', $options_string );

						if ( count( $options_raw ) ) {

							foreach ( $options_raw as $o ) {

								$o = trim( $o );

								$is_formatted = strpos( $o, '=' );

								// It's not formatted how we'd like it, so just add the value is both the value and label.
								if ( $is_formatted === false ) {

									$options[$o] = $o;

								// That's more like it. A separate value and label.
								} else {

									$option_data = explode( '=', $o );

									$options[$option_data[0]] = $option_data[1];

								} // End IF Statement

							} // End FOREACH Loop

						} // End IF Statement

					} // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'options' => $options );

				} // End IF Statement

				/* Parse the textarea inputs.
				--------------------------------------------------*/

				if ( substr( $k, 0, 9 ) == 'textarea_' ) {

					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The default text.
					if ( array_key_exists( 1, $params ) ) { $default_text = $params[1]; } else { $default_text = ''; } // End IF Statement

					// The number of rows.
					if ( array_key_exists( 2, $params ) ) { $number_of_rows = $params[2]; } else { $number_of_rows = 10; } // End IF Statement

					// The number of columns.
					if ( array_key_exists( 3, $params ) ) { $number_of_columns = $params[3]; } else { $number_of_columns = 10; } // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'default_text' => $default_text, 'number_of_rows' => $number_of_rows, 'number_of_columns' => $number_of_columns );

				} // End IF Statement

			} // End FOREACH Loop

		} // End IF Statement

		/*--------------------------------------------------
		 * Form Processing.
		 *
		 * Here is where we validate the POST'ed data and
		 * format it for sending in an e-mail.
		 *
		 * We then send the e-mail and notify the user.
		--------------------------------------------------*/

		$emailSent = false;

		if ( ( count( $_POST ) > 2 ) && isset( $_POST['submitted'] ) ) {

			$fields_to_skip = array( 'checking', 'submitted', 'sendCopy' );
			$default_fields = array( 'contactName' => '', 'contactEmail' => '', 'contactMessage' => '' );
			$error_responses = array(
									'contactName' => __( 'Please enter your name', 'woothemes' ),
									'contactEmail' => __( 'Please enter your email address (and please make sure it\'s valid)', 'woothemes' ),
									'contactMessage' => __( 'Please enter your message', 'woothemes' )
									);

			$posted_data = $_POST;

			// Check if we're using the default fields.
			if ( $show_default_fields != 'no' ) {
				// Check for errors.
				foreach ( array_keys( $default_fields ) as $d ) {
					if ( !isset ( $_POST[$d] ) || $_POST[$d] == '' || ( $d == 'contactEmail' && ! is_email( $_POST[$d] ) ) ) {
						$error_messages[$d] = esc_html( $error_responses[$d] );
					} // End IF Statement
				} // End FOREACH Loop
			} else {
				$default_fields = array( 'contactName' => get_bloginfo( 'name' ), 'contactEmail' => get_bloginfo( 'admin_email' ), 'contactMessage' => '' );
			}

			// If we have errors, don't do anything. Otherwise, run the processing code.

			if ( count( $error_messages ) ) {} else {
				// Setup e-mail variables.
				$message_fromname = $default_fields['contactName'];
				$message_fromemail = strtolower( $default_fields['contactEmail'] );
				$message_subject = $subject;
				$message_body = $default_fields['contactMessage'] . "\n\r\n\r";

				// Filter out skipped fields and assign default fields.
				foreach ( $posted_data as $k => $v ) {
					if ( in_array( $k, $fields_to_skip ) ) {
						unset( $posted_data[$k] );
					} // End IF Statement

					if ( in_array( $k, array_keys( $default_fields ) ) ) {
						$default_fields[$k] = $v;

						unset( $posted_data[$k] );
					} // End IF Statement
				} // End FOREACH Loop

				// Okay, so now we're left with only the dynamic fields. Assign to a fresh variable.
				$dynamic_fields = $posted_data;

				// Format the default fields into the $message_body.

				foreach ( $default_fields as $k => $v ) {
					if ( $v == '' ) {} else {
						$message_body .= str_replace( 'contact', '', $k ) . ': ' . $v . "\n\r";
					} // End IF Statement
				} // End FOREACH Loop

				// Format the dynamic fields into the $message_body.

				foreach ( $dynamic_fields as $k => $v ) {
					if ( $v == '' ) {} else {
						$value = '';

						if ( substr( $k, 0, 7 ) == 'select_' || substr( $k, 0, 6 ) == 'radio_' ) {
							$message_body .= $formatted_dynamic_atts[$k]['label'] . ': ' . $formatted_dynamic_atts[$k]['options'][$v] . "\n\r";
						} else {
							$message_body .= $formatted_dynamic_atts[$k]['label'] . ': ' . $v . "\n\r";
						} // End IF Statement
					} // End IF Statement
				} // End FOREACH Loop

				// Send the e-mail.
				$headers = __( 'From: ', 'woothemes') . $default_fields['contactName'] . ' <' . $default_fields['contactEmail'] . '>' . "\r\n" . __( 'Reply-To: ', 'woothemes' ) . $default_fields['contactEmail'];

				$emailSent = wp_mail($email, $subject, $message_body, $headers);

				// Send a copy of the e-mail to the sender, if specified.
				if ( isset( $_POST['sendCopy'] ) && $_POST['sendCopy'] == 'true' ) {
					$headers = __( 'From: ', 'woothemes') . $default_fields['contactName'] . ' <' . $default_fields['contactEmail'] . '>' . "\r\n" . __( 'Reply-To: ', 'woothemes' ) . $default_fields['contactEmail'];

					$emailSent = wp_mail($default_fields['contactEmail'], $subject, $message_body, $headers);
				} // End IF Statement

			} // End IF Statement ( count( $error_messages ) )

		} // End IF Statement

		/* Generate the form HTML.
		--------------------------------------------------*/

		$html .= '<div class="post contact-form">' . "\n";

		/* Display message HTML if necessary.
		--------------------------------------------------*/

		// Success messages
		if( isset( $emailSent ) && $emailSent == true ) {
			$html .= do_shortcode( '[box type="tick"]' . __( 'Your email was successfully sent.', 'woothemes' ) . '[/box]' );
			$html .= '<span class="has_sent hide"></span>' . "\n";
		}

		// Error messages
		if( count( $error_messages ) ) {
			$html .= do_shortcode( '[box type="alert"]' . __( 'There were one or more errors while submitting the form.', 'woothemes' ) . '[/box]' );
		}

        // No e-mail address supplied.
        if( $email == '' ) {
			$html .= do_shortcode( '[box type="alert"]' . __( 'E-mail has not been setup properly. Please add your contact e-mail!', 'woothemes' ) . '[/box]' );
		}

		if ( $email == '' ) {} else {
			$html .= '<form action="" id="contactForm" method="post">' . "\n";
				$html .= '<fieldset class="forms">' . "\n";

			/* Parse the "static" form fields.
			--------------------------------------------------*/
			if ( $show_default_fields != 'no' ) {
				$contactName = '';
				if( isset( $_POST['contactName'] ) ) { $contactName = $_POST['contactName']; }

				$contactEmail = '';
				if( isset( $_POST['contactEmail'] ) ) { $contactEmail = $_POST['contactEmail']; }

				$contactMessage = '';
				if( isset( $_POST['contactMessage'] ) ) { $contactMessage = stripslashes( $_POST['contactMessage'] ); }

				$html .= '<p><label for="contactName">' . __( 'Name', 'woothemes' ) . '</label>' . "\n";
				$html .= '<input type="text" name="contactName" id="contactName" value="' . esc_attr( $contactName ) . '" class="txt requiredField" />' . "\n";

				if( array_key_exists( 'contactName', $error_messages ) ) {
					$html .= '<span class="error">' . esc_html( $error_messages['contactName'] ) . '</span>' . "\n";
				}

				$html .= '</p>' . "\n";

				$html .= '<p><label for="contactEmail">' . __( 'Email', 'woothemes' ) . '</label>' . "\n";
				$html .= '<input type="text" name="contactEmail" id="contactEmail" value="' . esc_attr( $contactEmail ) . '" class="txt requiredField email" />' . "\n";

				if( array_key_exists( 'contactEmail', $error_messages ) ) {
					$html .= '<span class="error">' . esc_html( $error_messages['contactEmail'] ) . '</span>' . "\n";
				}

				$html .= '</p>' . "\n";

				$html .= '<p class="textarea"><label for="contactMessage">' . __( 'Message', 'woothemes' ) . '</label>' . "\n";
				$html .= '<textarea name="contactMessage" id="contactMessage" rows="20" cols="30" class="textarea requiredField">' . esc_textarea( $contactMessage ) . '</textarea>' . "\n";

				if( array_key_exists( 'contactMessage', $error_messages ) ) {
					$html .= '<span class="error">' . esc_html( $error_messages['contactMessage'] ) . '</span>' . "\n";
				}

				$html .= '</p>' . "\n";
			} // End static fields check

			/* Parse dynamic fields into HTML.
			--------------------------------------------------*/

			if ( count( $formatted_dynamic_atts ) ) {

				foreach ( $formatted_dynamic_atts as $k => $v ) {

					/* Parse the radio buttons.
					--------------------------------------------------*/

					if ( substr( $k, 0, 6 ) == 'radio_' ) {
						/* Generate Select Box Field HTML.
						----------------------------------------------*/

						${$k} = $v['default_value'];
						if ( isset( $_POST[$k] ) ) { ${$k} = trim( strip_tags( $_POST[$k] ) ); } // End IF Statement

						$html .= '<p><label for="' . esc_attr( $k ) . '">' . esc_html( $v['label'] ) . '</label>' . "\n";

							$html .= '<span class="woo-radio-container fl">' . "\n";

							foreach ( $v['options'] as $value => $label ) {
								$html .= '<input type="radio" name="' . esc_attr( $k ) . '" class="radio-button woo-input-radio" value="' . esc_attr( $value ) . '"' . checked( $value, ${$k}, false ) . ' />&nbsp;' . esc_html( $label ) . '<br />' . "\n";
							}

							$html .= '</span><!--/.woo-radio-container-->' . "\n";
					}

					/* Parse the checkbox inputs.
					--------------------------------------------------*/

					if ( substr( $k, 0, 9 ) == 'checkbox_' ) {
						/* Generate Checkbox Input Field HTML.
						----------------------------------------------*/

						${$k} = $v['value'];
						if ( isset( $_POST[$k] ) ) { ${$k} = trim( strip_tags( $_POST[$k] ) ); } // End IF Statement

						$checked = 0;
						if ( array_key_exists( 'checked', $v ) && $v['checked'] == 'yes' ) { $checked = ${$k}; }

						$html .= '<p class="inline">' . "\n";
						$html .= '<input type="checkbox" value="' . esc_attr( ${$k} ) . '" name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" class="checkbox input-checkbox woo-input-checkbox"' . checked( $checked, ${$k}, false ) . ' />' . "\n";
						$html .= '<label for="' . esc_attr( $k ) . '">' . esc_html( $v['label'] ) . '</label></p>' . "\n";
					}

					/* Parse the text inputs.
					--------------------------------------------------*/

					if ( substr( $k, 0, 5 ) == 'text_' ) {
						/* Generate Text Input Field HTML.
						----------------------------------------------*/

						${$k} = $v['default_text'];
						if ( isset( $_POST[$k] ) ) { ${$k} = trim( strip_tags( $_POST[$k] ) ); } // End IF Statement

						$html .= '<p><label for="' . esc_attr( $k ) . '">' . esc_html( $v['label'] ) . '</label>' . "\n";
						$html .= '<input type="text" value="' . esc_attr( ${$k} ) . '" name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" class="txt input-text textfield woo-input-text" /></p>' . "\n";
					}

					/* Parse the select boxes.
					--------------------------------------------------*/

					if ( substr( $k, 0, 7 ) == 'select_' ) {
						/* Generate Select Box Field HTML.
						----------------------------------------------*/

						${$k} = '';
						if ( isset( $_POST[$k] ) ) { ${$k} = trim( strip_tags( $_POST[$k] ) ); } // End IF Statement

						$html .= '<p><label for="' . esc_attr( $k ) . '">' . esc_html( $v['label'] ) . '</label>' . "\n";
						$html .= '<select name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" class="select selectfield woo-select">' . "\n";

							foreach ( $v['options'] as $value => $label ) {
								$selected = '';
								if ( $value == ${$k} ) { $selected = ' selected="selected"'; } // End IF Statement

								$html .= '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . esc_html( $label ) . '</option>' . "\n";
							}

						$html .= '</select></p>' . "\n";
					}

					/* Parse the textarea inputs.
					--------------------------------------------------*/

					if ( substr( $k, 0, 9 ) == 'textarea_' ) {
						/* Generate Textarea Input Field HTML.
						----------------------------------------------*/

						${$k} = $v['default_text'];
						if ( isset( $_POST[$k] ) ) { ${$k} = trim( strip_tags( $_POST[$k] ) ); } // End IF Statement

						$html .= '<p><label for="' . esc_attr( $k ) . '">' . esc_html( $v['label'] ) . '</label>' . "\n";
						$html .= '<textarea rows="' . esc_attr( $v['number_of_rows'] ) . '" cols="' . esc_attr( $v['number_of_columns'] ) . '" name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" class="input-textarea textarea woo-textarea">' . esc_textarea( ${$k} ) . '</textarea></p>' . "\n";

					}
				} // End FOREACH Loop
			} // End IF Statement

			/* The end of the form.
			----------------------------------------------*/

			$sendCopy = '';
			if(isset($_POST['sendCopy']) && $_POST['sendCopy'] == true) {
				$sendCopy = ' checked="checked"';
			}

			$html .= '<p class="inline"><input type="checkbox" name="sendCopy" id="sendCopy" value="true"' . esc_attr( $sendCopy ) . ' /><label for="sendCopy">' . __( 'Send a copy of this email to yourself', 'woothemes' ) . '</label></p>' . "\n";

			$checking = '';
			if(isset($_POST['checking'])) {
				$checking = $_POST['checking'];
			}

			$html .= '<p class="screenReader"><label for="checking" class="screenReader">' . __('If you want to submit this form, do not enter anything in this field', 'woothemes') . '</label><input type="text" name="checking" id="checking" class="screenReader" value="' . esc_attr( $checking ) . '" /></p>' . "\n";
			$html .= '<p class="buttons"><input type="hidden" name="submitted" id="submitted" value="true" /><input class="submit button" type="submit" value="' . esc_attr( $button_text ) . '" /></p>';
				$html .= '</fieldset>' . "\n";
			$html .= '</form>' . "\n";
			$html .= '</div><!--/.post .contact-form-->' . "\n";
			$html .= '<div class="fix"></div>' . "\n";
		} // End IF Statement ( $email == '' )

		return $html;
} // End woo_shortcode_contactform()

add_shortcode( 'contact_form', 'woo_shortcode_contactform' );

/*-----------------------------------------------------------------------------------*/
/* 16. Tabs - [tabs][/tabs]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_tabs ( $atts, $content = null ) {
	// Instruct the shortcode JavaScript to load.
	if ( ! defined( 'WOO_SHORTCODE_JS' ) ) { define( 'WOO_SHORTCODE_JS', 'load' ); }

	$defaults = array( 'style' => 'default', 'title' => '', 'css' => '', 'id' => '' );

	extract( shortcode_atts( $defaults, $atts ) );

	// If no unique ID is set, set the ID as a random number between 1 and 100 (to make sure each tab group is unique).
	if ( $id == '' ) { $id = rand( 1, 100 ); }
	if ( $css != '' ) { $css = ' ' . $css; }

	// Extract the tab titles for use in the tabber widget.
	preg_match_all( '/tab title="([^\"]+)"/i', $content, $matches, PREG_OFFSET_CAPTURE );

	$tab_titles = array();
	$tabs_class = 'tab_titles';

	if ( isset( $matches[1] ) ) { $tab_titles = $matches[1]; } // End IF Statement

	$titles_html = '';

	if ( count( $tab_titles ) ) {
		if ( $title ) { $titles_html .= '<h4 class="tab_header"><span>' . esc_html( $title ) . '</span></h4>'; $tabs_class .= ' has_title'; } // End IF Statement

		$titles_html .= '<ul class="' . esc_attr( $tabs_class ) . '">' . "\n";

			$counter = 1;

			foreach ( $tab_titles as $t ) {
				$titles_html .= '<li class="nav-tab"><a href="#tab-' . esc_attr( $counter ) . '">' . esc_html( $t[0] ) . '</a></li>' . "\n";
				$counter++;
			} // End FOREACH Loop

		$titles_html .= '</ul>' . "\n";
	} // End IF Statement

	return '<div id="tabs-' . esc_attr( $id ) . '" class="shortcode-tabs ' . esc_attr( $style ) . esc_attr( $css ) . '">' . $titles_html . do_shortcode( $content ) . "\n" . '<div class="fix"></div><!--/.fix-->' . "\n" . '</div><!--/.tabs-->';
} // End woo_shortcode_tabs()

add_shortcode( 'tabs', 'woo_shortcode_tabs', 90 );

/*-----------------------------------------------------------------------------------*/
/* 16.1 A Single Tab - [tab title="The title goes here"][/tab]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_tab_single ( $atts, $content = null ) {
		$defaults = array( 'title' => 'Tab' );

		extract( shortcode_atts( $defaults, $atts ) );

		$class = '';

		if ( $title != 'Tab' ) {
			$class = ' tab-' . sanitize_title( $title );
		}

		return '<div class="tab' . esc_attr( $class ) . '">' . do_shortcode( $content ) . '</div><!--/.tab-->';
} // End woo_shortcode_tab_single()

add_shortcode( 'tab', 'woo_shortcode_tab_single', 99 );

/*-----------------------------------------------------------------------------------*/
/* 17. Dropcap - [dropcap][/dropcap]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_dropcap ( $atts, $content = null ) {
	$defaults = array();

	extract( shortcode_atts( $defaults, $atts ) );

	return '<span class="dropcap">' . $content . '</span><!--/.dropcap-->';
} // End woo_shortcode_dropcap()

add_shortcode( 'dropcap', 'woo_shortcode_dropcap' );

/*-----------------------------------------------------------------------------------*/
/* 18. Highlight - [highlight][/highlight]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_highlight ( $atts, $content = null ) {
	$defaults = array();

	extract( shortcode_atts( $defaults, $atts ) );

	return '<span class="shortcode-highlight">' . $content . '</span><!--/.shortcode-highlight-->';
} // End woo_shortcode_highlight()

add_shortcode( 'highlight', 'woo_shortcode_highlight' );

/*-----------------------------------------------------------------------------------*/
/* 19. Abbreviation - [abbr][/abbr]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_abbreviation ( $atts, $content = null ) {
	$defaults = array( 'title' => '' );

	extract( shortcode_atts( $defaults, $atts ) );

	return '<abbr title="' . esc_attr( $title ) . '">' . $content . '</abbr>';
} // End woo_shortcode_abbreviation()

add_shortcode( 'abbr', 'woo_shortcode_abbreviation' );

/*-----------------------------------------------------------------------------------*/
/* 20. Typography - [typography font="" size="" color=""][/typography]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_typography ( $atts, $content = null ) {
	global $google_fonts, $woo_used_google_fonts;

	// Get just the names of the Google fonts.
	$google_font_names = array();

	if ( count( $google_fonts ) ) {
		foreach ( $google_fonts as $g ) {
			$google_font_names[] = $g['name'];
		} // End FOREACH Loop
	} // End IF Statement

	// Build array of usable typefaces.
	$fonts_whitelist = array(
						'Arial, Helvetica, sans-serif',
						'Verdana, Geneva, sans-serif',
						'|Trebuchet MS|, Tahoma, sans-serif',
						'Georgia, |Times New Roman|, serif',
						'Tahoma, Geneva, Verdana, sans-serif',
						'Palatino, |Palatino Linotype|, serif',
						'|Helvetica Neue|, Helvetica, sans-serif',
						'Calibri, Candara, Segoe, Optima, sans-serif',
						'|Myriad Pro|, Myriad, sans-serif',
						'|Lucida Grande|, |Lucida Sans Unicode|, |Lucida Sans|, sans-serif',
						'|Arial Black|, sans-serif',
						'|Gill Sans|, |Gill Sans MT|, Calibri, sans-serif',
						'Geneva, Tahoma, Verdana, sans-serif',
						'Impact, Charcoal, sans-serif'
						);

	$fonts_whitelist = array(); // Temporarily remove the default fonts.

	$defaults = array( 'font' => 'Arial, Helvetica, sans-serif', 'size' => '12', 'color' => '#000000', 'size_format' => 'px' );

	extract( shortcode_atts( $defaults, $atts ) );

	// Run checks to make sure it's an allowed font stack.
	if ( in_array( $font, $fonts_whitelist ) || in_array( $font, $google_font_names ) ) {
		if ( in_array( $font, $google_font_names ) ) {

			// Set up global array of used Google fonts for processing later
			if( ! $woo_used_google_fonts ) { $woo_used_google_fonts = array(); }

			// Add to array of used Google fonts
			if ( ! in_array( $font, $woo_used_google_fonts ) ) {
				$woo_used_google_fonts[] = $font;
			} // End IF Statement

			$font = "'" . $font . "'";
		} // End IF Statement
	} else {
		$font = 'Arial, Helvetica, sans-serif';
	} // End IF Statement

	// $font = str_replace( '|', '"', $font );

	return '<span class="shortcode-typography" style="font-family: ' . esc_attr( $font ) . '; font-size: ' . esc_attr( $size . $size_format ) . '; color: ' . esc_attr( $color ) . ';">' . do_shortcode( $content ) . '</span>';
} // End woo_shortcode_typography()

add_shortcode( 'typography', 'woo_shortcode_typography' );

add_action( 'wp_head', 'woo_shortcode_typography_loadgooglefonts', 0 );

function woo_shortcode_typography_loadgooglefonts ( $font = false , $id = false ) {
	if ( $font ) {
		// If a specific font is requested, just enqueue that font.
		$variations = array(
							'Raleway' => ':100',
							'Coda' => ':800',
							'UnifrakturCook' => ':bold',
							'Allan' => ':bold',
							'Sniglet' => ':800',
							'Cabin' => ':bold',
							'Corben' => ':bold',
							'Buda' => ':light'
							);

		$f = $font;

		$f = str_replace( ' ', '+', $f );

		$f_include = $f;

		if ( in_array( $f, array_keys( $variations ) ) ) {
			$f_include = $f . $variations[$f];
		} // End IF Statement

		if( ! $id ) {
			$id = 'woo-googlefont-' . sanitize_title( $f );
		}

		wp_enqueue_style( $id , 'http'. ( is_ssl() ? 's' : '' ) .'://fonts.googleapis.com/css?family=' . $f_include . '' , array() , '3.6' , 'screen' );
	} // End IF Statement
} // End woo_shortcode_typography_loadgooglefonts()

/*-----------------------------------------------------------------------------------*/
/* 21. List Styles - Unordered List - [unordered_list style=""][/unordered_list]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_unorderedlist ( $atts, $content = null ) {
	$defaults = array( 'style' => 'default' );

	extract( shortcode_atts( $defaults, $atts ) );

	return '<div class="shortcode-unorderedlist ' . esc_attr( $style ) . '">' . do_shortcode( $content ) . '</div>' . "\n";
} // End woo_shortcode_unorderedlist()

add_shortcode( 'unordered_list', 'woo_shortcode_unorderedlist' );

/*-----------------------------------------------------------------------------------*/
/* 22. List Styles - Ordered List - [ordered_list style=""][/ordered_list]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_orderedlist ( $atts, $content = null ) {
	$defaults = array( 'style' => 'default' );

	extract( shortcode_atts( $defaults, $atts ) );

	return '<div class="shortcode-orderedlist ' . esc_attr( $style ) . '">' . do_shortcode( $content ) . '</div>' . "\n";
} // End woo_shortcode_orderedlist()

add_shortcode( 'ordered_list', 'woo_shortcode_orderedlist' );

/*-----------------------------------------------------------------------------------*/
/* 23. Social Icon - [social_icon url="" float="" icon_url="" title="" profile_type="" window=""]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_socialicon ( $atts, $content = null ) {
	$defaults = array( 'url' => '', 'float' => 'none', 'icon_url' => '', 'title' => '', 'profile_type' => '', 'window' => 'no', 'rel' => '' );

	extract( shortcode_atts( $defaults, $atts ) );

	if ( ! $url ) { return; } // End IF Statement - Don't run the shortcode if no URL has been supplied.

	// Attempt to determine the location of the social profile.
	// If no location is found, a default icon will be used.

	$_default_icon = '';

	$_supported_profiles = array(
									'facebook' => 'facebook.com',
									'twitter' => 'twitter.com',
									'youtube' => 'youtube.com',
									'delicious' => 'delicious.com',
									'flickr' => 'flickr.com',
									'linkedin' => 'linkedin.com', 
									'googleplus' => 'plus.google.com'
								);

	$_profile_to_display = '';
	$_alt_text = '';
	$_classes = 'social-icon';

	$_profile_match = false;

	// If they've specified an icon, skip the automation.

	if ( $profile_type != '' ) {
		$_profile_match = true;
		$_profile_to_display = $profile_type;
		if ( $title ) { $_alt_text = $title; } else { $_alt_text = ucwords( $_profile_to_display ); $_alt_text = sprintf( __( 'My %s Profile', 'woothemes' ), $_alt_text ); } // End IF Statement
		$_profile_class = ' social-icon-' . $_profile_to_display;

		if ( $icon_url ) {
			$_img_url = $icon_url;
		} else {
			$_img_url = trailingslashit( get_template_directory_uri() ) . 'functions/images/ico-social-' . $_profile_to_display . '.png';
		}
	} // End IF Statement

	// Create a special scenario for use with the RSS feed for this website.
	if ( $url == 'feed' ) {
		$_profile_match = true;
		$_profile_to_display = 'rss';
		if ( $title ) { $_alt_text = $title; } else { $_alt_text = __( 'Subscribe to our RSS feed', 'woothemes' ); } // End IF Statement
		$_classes .= ' social-icon-subscribe';
		$url = get_bloginfo( 'rss2_url' );

		if ( $icon_url ) {
			$_img_url = $icon_url;
		} else {
			$_img_url = trailingslashit( get_template_directory_uri() ) . 'functions/images/ico-social-' . $_profile_to_display . '.png';
		}
	} else {
		foreach ( $_supported_profiles as $k => $v ) {
			if ( $_profile_match == true ) { break; } // End IF Statement - Break out of the loop if we already have a match.

			// Get host name from URL
			preg_match( '@^(?:http://)?([^/]+)@i', $url, $matches );
			$host = $matches[1];

			if ( $host == $v ) {

				$_profile_match = true;
				$_profile_to_display = $k;
				if ( $title ) { $_alt_text = $title; } else { $_alt_text = ucwords( $_profile_to_display ); $_alt_text = sprintf( __( 'My %s Profile', 'woothemes' ), $_alt_text ); } // End IF Statement
				$_profile_class = ' social-icon-' . $_profile_to_display;

				if ( $icon_url ) {
					$_img_url = $icon_url;
				} else {
					$_img_url = trailingslashit( get_template_directory_uri() ) . 'functions/images/ico-social-' . $_profile_to_display . '.png';
				}
			} else {
				$_profile_to_display = 'default';
				if ( $title ) { $_alt_text = $title; } else { $_alt_text = ucwords( $matches[1] ); $_alt_text = sprintf( __( 'My %s Profile', 'woothemes' ), $_alt_text ); } // End IF Statement

				$_host_bits = explode( '.', $matches[1] );
				$_profile_class = ' social-icon-' . $_host_bits[0];

				if ( $icon_url ) {
					$_img_url = $icon_url;
				} else {
					$_img_url = trailingslashit( get_template_directory_uri() ) . 'functions/images/ico-social-' . $_profile_to_display . '.png';

					// Check if an image has been added for this social icon.
					if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'images/ico-social-' . $_host_bits[0] . '.png' ) ) {
						$_img_url = trailingslashit( get_stylesheet_directory_uri() ) . 'images/ico-social-' . $_host_bits[0] . '.png';
					}
				} // End IF Statement
			} // End IF Statement
		} // End FOREACH Loop

		$_classes .= $_profile_class;

		// Determine the floating CSS class to be used.
		switch ( $float ) {
			case 'left':
				$_classes .= ' fl';
			break;

			case 'right':
				$_classes .= ' fr';
			break;

			default:
			break;
		}
	} // End IF Statement

	$target = '';
	if ( $window == 'yes' ) { $target = ' target="_blank"'; } // End IF Statement
	if ( $rel != '' ) { $rel = ' rel="' . esc_attr( $rel ) . '"'; }

	return '<a href="' . esc_attr( $url ) . '" title="' . esc_attr( $_alt_text ) . '"' . $target . $rel . '><img src="' . esc_url( $_img_url ) . '" alt="' . esc_attr( $_alt_text ) . '" class="' . esc_attr( $_classes ) . '" /></a>' . "\n";
} // End woo_shortcode_socialicon()

add_shortcode( 'social_icon', 'woo_shortcode_socialicon' );

/*-----------------------------------------------------------------------------------*/
/* 24. LinkedIn Button - [linkedin_share url="" style=""]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_linkedin_share ( $atts, $content = null ) {

	$defaults = array( 'url' => '', 'style' => 'none', 'float' => 'none' );

	extract( shortcode_atts( $defaults, $atts ) );

	$allowed_floats = array( 'left' => 'fl', 'right' => 'fr', 'none' => '' );
	$allowed_styles = array( 'top' => ' data-counter="top"', 'right' => ' data-counter="right"', 'none' => '' );

	if ( ! in_array( $float, array_keys( $allowed_floats ) ) ) { $float = 'none'; }
	if ( ! in_array( $style, array_keys( $allowed_styles ) ) ) { $style = 'none'; }

	if ( $url ) { $url = ' data-url="' . esc_url( $url ) . '"'; }

	$output = '';

	if ( $float == 'none' ) {} else { $output .= '<div class="shortcode-linkedin_share ' . esc_attr( $allowed_floats[$float] ) . '">' . "\n"; }

	$output .= '<script type="IN/Share" ' . $url . $allowed_styles[$style] . '></script>' . "\n";

	if ( $float == 'none' ) {} else { $output .= '</div><!--/.shortcode-linkedin_share-->' . "\n"; }

	// Enqueue the LinkedIn button JavaScript from their API.
	add_action( 'wp_footer', 'woo_shortcode_linkedin_js' );
	add_action( 'woo_shortcode_generator_preview_footer', 'woo_shortcode_linkedin_js' );

	return $output . "\n";
} // End woo_shortcode_linkedin_share()

add_shortcode( 'linkedin_share', 'woo_shortcode_linkedin_share' );

/*-----------------------------------------------------------------------------------*/
/* 24.1 Load Javascript for LinkedIn Button
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_linkedin_js () {
	echo '<script src="http://platform.linkedin.com/in.js" type="text/javascript"></script>' . "\n";
} // End woo_shortcode_linkedin_js()

/*-----------------------------------------------------------------------------------*/
/* 25. Google +1 Button - [google_plusone]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_google_plusone ( $atts, $content = null ) {
	global $post;

	$defaults = array(
						'size' => '',
						'language' => '',
						'count' => '',
						'href' => '',
						'callback' => '',
						'float' => 'none', 
						'annotation' => 'none'
					);

	$atts = shortcode_atts( $defaults, $atts );

	extract( $atts );

	$params = array();

	$allowed_floats = array( 'left' => ' fl', 'right' => ' fr', 'none' => '' );
	if ( ! in_array( $float, array_keys( $allowed_floats ) ) ) { $float = 'none'; }
	
	if ( ! in_array( $annotation, array( 'bubble', 'inline', 'none' ) ) ) { $annotation = 'none'; } 

	// A friendly-looking array of supported languages, along with their codes.
	$supported_languages = array(
		'ar' => 'Arabic', 
		'bg' => 'Bulgarian', 
		'ca' => 'Catalan', 
		'zh-CN' => 'Chinese (Simplified)', 
		'zh-TW' => 'Chinese (Traditional)', 
		'hr' => 'Croatian', 
		'cs' => 'Czech', 
		'da' => 'Danish', 
		'nl' => 'Dutch', 
		'en-US' => 'English (US)', 
		'en-GB' => 'English (UK)', 
		'et' => 'Estonian', 
		'fil' => 'Filipino', 
		'fi' => 'Finnish', 
		'fr' => 'French', 
		'de' => 'German', 
		'el' => 'Greek', 
		'iw' => 'Hebrew', 
		'hi' => 'Hindi', 
		'hu' => 'Hungarian', 
		'id' => 'Indonesian', 
		'it' => 'Italian', 
		'ja' => 'Japanese', 
		'ko' => 'Korean', 
		'lv' => 'Latvian', 
		'lt' => 'Lithuanian', 
		'ms' => 'Malay', 
		'no' => 'Norwegian', 
		'fa' => 'Persian', 
		'pl' => 'Polish', 
		'pt-BR' => 'Portuguese (Brazil)', 
		'pt-PT' => 'Portuguese (Portugal)', 
		'ro' => 'Romanian', 
		'ru' => 'Russian', 
		'sr' => 'Serbian', 
		'sv' => 'Swedish', 
		'sk' => 'Slovak', 
		'sl' => 'Slovenian', 
		'es' => 'Spanish', 
		'es-419' => 'Spanish (Latin America)', 
		'th' => 'Thai', 
		'tr' => 'Turkish', 
		'uk' => 'Ukrainian', 
		'vi' => 'Vietnamese'
	);

	$output = '';
	$tag_atts = '';

	// Make sure we only have Google +1 attributes in our array, after parsing the "float" parameter.
	unset( $atts['float'] );

	if ( $atts['href'] == '' & isset( $post->ID ) ) {
		$atts['href'] = get_permalink( $post->ID );
	}

	foreach ( $atts as $k => $v ) {
		if ( ${$k} != '' ) {
			$tag_atts .= ' data-' . esc_attr( $k ) . '="' . esc_attr( ${$k} ) . '"';
		}
	}

	$output = '<div class="shortcode-google-plusone' . esc_attr( $allowed_floats[$float] ) . '"><div class="g-plusone" ' . $tag_atts . '></div></div><!--/.shortcode-google-plusone-->' . "\n";
	
	// Parameters to pass to Google PlusOne JavaScript.
	if ( in_array( $atts['language'] , array_values( $supported_languages ) ) ) {
		$language = '';
		
		foreach ( $supported_languages as $k => $v ) {
			if ( $v == $atts['language'] ) {
				$language = $k;
				break;
			}
		}
		
		$params = array( 'language' => $language );
	}

	// Enqueue the Google +1 button JavaScript from their API.
	woo_shortcode_google_plusone_js( $params );

	return $output . "\n";
} // End woo_shortcode_google_plusone()

add_shortcode( 'google_plusone', 'woo_shortcode_google_plusone' );

/*-----------------------------------------------------------------------------------*/
/* 25.1 Load Javascript for Google +1 Button
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_google_plusone_js ( $params ) {
	echo '<script src="https://apis.google.com/js/plusone.js" type="text/javascript">' . "\n";
	if ( isset( $params['language'] ) && ( $params['language'] != '' ) ) {
		echo ' {lang: \'' . esc_js( $params['language'] ) . '\'}' . "\n";
	}
	echo '</script>' . "\n";
	echo '<script type="text/javascript">gapi.plusone.go();</script>' . "\n";
} // End woo_shortcode_google_plusone_js()

/*-----------------------------------------------------------------------------------*/
/* 26. Twitter Follow Button - [twitter_follow]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_twitter_follow ( $atts, $content = null ) {

	global $post;

	if ( ! isset( $atts['username'] ) || ( $atts['username'] == '' ) ) { return; } // We can't continue without the username.

	$defaults = array(
						'username' => '', 
						'button_color' => 'blue',
						'text_color' => '',
						'link_color' => '',
						'width' => '',
						'align' => '',
						'language' => '',
						'count' => '',
						'float' => 'none', 
						'show_screen_name' => 'true', 
						'size' => 'medium'
					);

	$atts = shortcode_atts( $defaults, $atts );

	extract( $atts );

	$allowed_floats = array( 'left' => ' fl', 'right' => ' fr', 'none' => '' );
	if ( ! in_array( $float, array_keys( $allowed_floats ) ) ) { $float = 'none'; }
	
	$allowed_langs = array( 'en', 'fr', 'de', 'it', 'es', 'ko', 'ja' );
	if ( ! in_array( $language, array_keys( $allowed_langs ) ) ) { $language = ''; }

	$output = '';
	$tag_atts = '';

	// Make sure we only have Google +1 attributes in our array, after parsing the "float" parameter.
	unset( $atts['float'] );
	unset( $atts['username'] );

	// Setup array of attributes and the value keys containing the data for each.
	$att_keys = array(
						'button_color' => 'data-button', 
						'text_color' => 'data-text-color', 
						'link_color' => 'data-link-color', 
						'width' => 'data-width', 
						'align' => 'data-align', 
						'language' => 'data-lang', 
						'count' => 'data-show-count', 
						'show_screen_name' => 'data-show-screen-name', 
						'size' => 'data-size'
					);

	foreach ( $atts as $k => $v ) {
		if ( ${$k} != '' ) {
			$tag_atts .= ' ' . esc_attr( $att_keys[$k] ) . '="' . esc_attr( ${$k} ) . '"';
		}
	}

	$output = '<div class="shortcode-twitter-follow' . esc_attr( $allowed_floats[$float] ) . '">' . "\n";
	$output .= '<a href="'. esc_url( 'https://twitter.com/' . $username ) . '" class="twitter-follow-button"' . $tag_atts . '>' . __( 'Follow', 'woothemes' ) . ' ' . $username . '</a>' . "\n";
	$output .= woo_shortcode_twitter_follow_js( false );
	$output .= '</div><!--/.shortcode-twitter-follow-->' . "\n";

	return $output . "\n";
} // End woo_shortcode_twitter_follow()

add_shortcode( 'twitter_follow', 'woo_shortcode_twitter_follow' );

/*-----------------------------------------------------------------------------------*/
/* 26.1 Load Javascript for Twitter Follow Button
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_twitter_follow_js ( $echo = true ) {
	$html = '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>' . "\n";
	if ( true == $echo ) { echo $html; } else { return $html; }
} // End woo_shortcode_twitter_follow_js()

/*-----------------------------------------------------------------------------------*/
/* 27. StumbleUpon Badge - [stumbleupon]
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_stumbleupon ( $atts, $content = null ) {
	global $post;
	
	$defaults = array(
						'design' => 'horizontal_large',
						'float' => 'none', 
						'url' => '', 
						'use_post' => 'false'
					);

	$atts = shortcode_atts( $defaults, $atts );

	extract( $atts );

	$allowed_floats = array( 'left' => ' fl', 'right' => ' fr', 'none' => '' );
	if ( ! in_array( $float, array_keys( $allowed_floats ) ) ) { $float = 'none'; }
	
	$allowed_designs = array( 'horizontal_large' => '1', 'horizontal_medium' => '2', 'horizontal_small' => '3', 'icon_small' => '4', 'vertical_count' => '5', 'icon_large' => '6' );
	if ( ! in_array( $design, array_keys( $allowed_designs ) ) ) { $design = 'horizontal_large'; }

	$output = '';
	
	$url_call = '';
	
	// Use the custom URL, if it has been specified.
	if ( $atts['url'] != '' ) {
		$url_call = '&r=' . esc_url( $url );
	}
	
	// Use the URL to the current $post in the loop, if no custom URL is set and if instructed to do so.
	if ( $url_call == '' && $atts['use_post'] == 'true' ) {
		$url_call = '&r=' . esc_url( get_permalink( $post ) );
	}

	$output = apply_filters( 'woo_shortcode_stumbleupon', '<div class="shortcode-stumbleupon' . esc_attr( $allowed_floats[$float] ) . '"><script src="' . esc_url( 'http://www.stumbleupon.com/hostedbadge.php?s=' . $allowed_designs[$design] . $url_call ) . '"></script></div><!--/.shortcode-stumbleupon-->' . "\n", $atts );

	return $output . "\n";
} // End woo_shortcode_stumbleupon()

add_shortcode( 'stumbleupon', 'woo_shortcode_stumbleupon' );

/*-----------------------------------------------------------------------------------*/
/* 28. Pinterest "Pin It" Button [pinterest] */
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_pinterest ( $atts, $content = null ) {
	global $post;
	
	$defaults = array(
						'count' => 'horizontal',
						'float' => 'none', 
						'url' => '', 
						'image_url' => '', 
						'description' => '', 
						'use_post' => 'false'
					);

	$atts = shortcode_atts( $defaults, $atts );

	extract( $atts );

	$allowed_floats = array( 'left' => ' fl', 'right' => ' fr', 'none' => '' );
	if ( ! in_array( $float, array_keys( $allowed_floats ) ) ) { $float = 'none'; }
	
	$allowed_counts = array( 'horizontal', 'vertical', 'none' );
	if ( ! in_array( $count, array_keys( $allowed_counts ) ) ) { $count = 'horizontal'; }

	$output = '';

	// Use the custom URL, if it has been specified.
	if ( $atts['url'] != '' ) {
		$url = esc_url( $atts['url'] );
	} else {
		// Use the URL to the current $post in the loop.
		$url = esc_url( get_permalink( $post ) );
	}
	
	// Use the custom image URL, if it has been specified.
	if ( $atts['image_url'] != '' ) {
		$image_url = esc_url( $atts['image_url'] );
	} else {
		// Use the image of the current $post in the loop.
		$image_url = esc_url( woo_image( 'link=url&return=true' ) );
	}
	
	// Use the custom description, if it has been specified.
	if ( $atts['description'] != '' ) {
		$description = esc_attr( $atts['description'] );
	} else {
		// Use the excerpt of the current $post in the loop, if no description is set and if instructed to do so.
		if ( $atts['use_post'] == 'true' ) {
			$description = esc_attr( strip_shortcodes( apply_filters( 'get_the_excerpt', $post->post_excerpt ) ) );
		}
	}

	$output = apply_filters( 'woo_shortcode_pinterest', '<div class="shortcode-pinterest' . esc_attr( $allowed_floats[$float] ) . '"><a href="' . esc_url( 'http://pinterest.com/pin/create/button/?url=' . esc_url( $url ) . '&media=' . esc_url( $image_url ) . '&description=' . $description ) . '" class="pin-it-button" count-layout="' . esc_attr( $count ) . '">' . __( 'Pin It', 'woothemes' ) . '</a></div><!--/.shortcode-pinterest-->' . "\n", $atts );

	// Enqueue the Pinterest button JavaScript from their API.
	add_action( 'wp_footer', 'woo_shortcode_pinterest_javascript' );
	add_action( 'woo_shortcode_generator_preview_footer', 'woo_shortcode_pinterest_javascript' );

	return $output . "\n";
} // End woo_shortcode_pinterest()

add_shortcode( 'pinterest', 'woo_shortcode_pinterest' );

/*-----------------------------------------------------------------------------------*/
/* 28.1 Load Javascript for Pinterest Button
/*-----------------------------------------------------------------------------------*/

function woo_shortcode_pinterest_javascript () {
	echo '<script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js"></script>' . "\n";
} // End woo_shortcode_pinterest_javascript()

/*-----------------------------------------------------------------------------------*/
/* THE END */
/*-----------------------------------------------------------------------------------*/
?>