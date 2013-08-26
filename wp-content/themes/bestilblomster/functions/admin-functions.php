<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------

TABLE OF CONTENTS

- woo_image - Get Image from custom field
    - vt_resize - Resize post thumbnail
    - woo_get_youtube_video_image - Get thumbnail from YouTube
- Add default filters to woo_embed()
- woo_get_embed - Get Video
- Woo Show Page Menu
- Get the style path currently selected
- Get page ID
- Tidy up the image source url
- Show image in RSS feed
- Show analytics code footer
- Browser detection body_class() output
- Twitter's Blogger.js output for Twitter widgets
- Template Detector
- Framework Updater
	- WooFramework Update Page
 	- WooFramework Update Head
 	- WooFramework Version Getter
- Woo URL shortener
- SEO - woo_title()
- SEO - Strip slashes from the display of the website/page title
- SEO - woo_meta()
- Woo Text Trimmer
- Google Webfonts array
- Google Fonts Stylesheet Generator
- Enable Home link in WP Menus
- Buy Themes page
- Detects the Charset of String and Converts it to UTF-8
- WP Login logo
- WP Login logo URL
- WP Login logo title
- woo_pagination()
- woo_breadcrumbs()
-- woo_breadcrumbs_get_parents()
-- woo_breadcrumbs_get_term_parents()
- WordPress Admin Bar-related
-- Disable WordPress Admin Bar
-- Enhancements to the WordPress Admin Bar
- woo_prepare_category_ids_from_option()
- Move tracking code from footer to header.
- woo_get_dynamic_values()
- woo_get_posts_by_taxonomy()
- If the user has specified a "posts page", load the "Blog" page template there
- PressTrends API Integration
- WooDojo Download Banner
- wooframework_add_woodojo_banner()
- wooframework_ajax_banner_close()
- WooSEO Deprecation Banner
- wooframework_add_wooseo_banner()
- WooSidebars Deprecation Banner
- wooframework_add_woosbm_banner()
- Static Front Page Detection Banner
- wooframework_get_theme_version_data()
- wooframework_display_theme_version_data()
-----------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------*/
/* woo_image - Get Image from custom field  */
/*-----------------------------------------------------------------------------------*/

/*
This function retrieves/resizes the image to be used with the post in this order:

1. Image passed through parameter 'src'
2. WP Post Thumbnail (if option activated)
3. Custom field
4. First attached image in post (if option activated)
5. First inline image in post (if option activated)

Resize options (enabled in options panel):
- vt_resize() is used to natively resize #2 and #4
- Thumb.php is used to resize #1, #3, #4 (only if vt_resize is disabled) and #5

Parameters:
        $key = Custom field key eg. "image"
        $width = Set width manually without using $type
        $height = Set height manually without using $type
        $class = CSS class to use on the img tag eg. "alignleft". Default is "thumbnail"
        $quality = Enter a quality between 80-100. Default is 90
        $id = Assign a custom ID, if alternative is required.
        $link = Echo with anchor ( 'src'), without anchor ( 'img') or original image URL ( 'url').
        $repeat = Auto Img Function. Adjust amount of images to return for the post attachments.
        $offset = Auto Img Function. Offset the $repeat with assigned amount of objects.
        $before = Auto Img Function. Add Syntax before image output.
        $after = Auto Img Function. Add Syntax after image output.
        $single = (true/false) Force thumbnail to link to the post instead of the image.
        $force = Force smaller images to not be effected with image width and height dimensions (proportions fix)
        $return = Return results instead of echoing out.
		$src = A parameter that accepts a img url for resizing. (No anchor)
		$meta = Add a custom meta text to the image and anchor of the image.
		$alignment = Crop alignment for thumb.php (l, r, t, b)
		$size = Custom pre-defined size for WP Thumbnail (string)
		$noheight = Don't output the height on img tag (for responsive designs)
*/

if ( !function_exists('woo_image') ) {
function woo_image($args) {

	/* ------------------------------------------------------------------------- */
	/* SET VARIABLES */
	/* ------------------------------------------------------------------------- */

	global $post;
	global $woo_options;

	//Defaults
	$key = 'image';
	$width = null;
	$height = null;
	$class = '';
	$quality = 90;
	$id = null;
	$link = 'src';
	$repeat = 1;
	$offset = 0;
	$before = '';
	$after = '';
	$single = false;
	$force = false;
	$return = false;
	$is_auto_image = false;
	$src = '';
	$meta = '';
	$alignment = '';
	$size = '';
	$noheight = '';

	$alt = '';
	$img_link = '';

	$attachment_id = array();
	$attachment_src = array();

	if ( ! is_array( $args ) )
		parse_str( $args, $args );

	extract( $args );

    // Set post ID
    if ( empty( $id ) ) {
		$id = $post->ID;
    }

	$thumb_id = esc_html( get_post_meta( $id, '_thumbnail_id', true ) );

	// Set alignment
	if ( $alignment == '' )
		$alignment = esc_html( get_post_meta( $id, '_image_alignment', true ) );

	// Get standard sizes
	if ( ! $width && ! $height ) {
		$width = '100';
		$height = '100';
	}
	
	// Cast $width and $height to integer
	$width = intval( $width );
	$height = intval( $height );
	
	/* ------------------------------------------------------------------------- */
	/* FIND IMAGE TO USE */
	/* ------------------------------------------------------------------------- */

	// When a custom image is sent through
	if ( $src != '' ) {
		$custom_field = esc_url( $src );
		$link = 'img';

	// WP 2.9 Post Thumbnail support
	} elseif ( get_option( 'woo_post_image_support' ) == 'true' && ! empty( $thumb_id ) ) {

		if ( get_option( 'woo_pis_resize' ) == 'true' ) {

			if ( 0 == $height ) {
				$img_data = wp_get_attachment_image_src( $thumb_id, array( intval( $width ), 9999 ) );
				$height = $img_data[2];
			}

			// Dynamically resize the post thumbnail
			$vt_crop = get_option( 'woo_pis_hard_crop' );
			if ($vt_crop == 'true' ) $vt_crop = true; else $vt_crop = false;
			$vt_image = vt_resize( $thumb_id, '', $width, $height, $vt_crop );

			// Set fields for output
			$custom_field = esc_url( $vt_image['url'] );
			$width = $vt_image['width'];
			$height = $vt_image['height'];

		} else {
			// Use predefined size string
			if ( $size )
				$thumb_size = $size;
			else
				$thumb_size = array( $width, $height );

			$img_link = get_the_post_thumbnail( $id, $thumb_size, array( 'class' => 'woo-image ' . esc_attr( $class ) ) );
		}

	// Grab the image from custom field
	} else {
    	$custom_field = esc_url( get_post_meta( $id, $key, true ) );
	}

	// Automatic Image Thumbs - get first image from post attachment
	if ( empty( $custom_field ) && get_option( 'woo_auto_img' ) == 'true' && empty( $img_link ) && ! ( is_singular() && in_the_loop() && $link == 'src' ) ) {

        if( $offset >= 1 )
			$repeat = $repeat + $offset;

        $attachments = get_children( array(	'post_parent' => $id,
											'numberposts' => $repeat,
											'post_type' => 'attachment',
											'post_mime_type' => 'image',
											'order' => 'DESC',
											'orderby' => 'menu_order date')
											);

		// Search for and get the post attachment
		if ( ! empty( $attachments ) ) {
			$counter = -1;
			foreach ( $attachments as $att_id => $attachment ) {
				$counter++;
				if ( $counter < $offset )
					continue;

				if ( get_option( 'woo_post_image_support' ) == 'true' && get_option( 'woo_pis_resize' ) == 'true' ) {
					// Dynamically resize the post thumbnail
					$vt_crop = get_option( 'woo_pis_hard_crop' );
					if ( $vt_crop == 'true' ) $vt_crop = true; else $vt_crop = false;
					$vt_image = vt_resize( $att_id, '', $width, $height, $vt_crop );

					// Set fields for output
					$custom_field = esc_url( $vt_image['url'] );
					$width = $vt_image['width'];
					$height = $vt_image['height'];
				} else {
					$src = wp_get_attachment_image_src( $att_id, 'large', true );
					$custom_field = esc_url( $src[0] );
					$attachment_id[] = $att_id;
					$src_arr[] = $custom_field;
				}
				$thumb_id = $att_id;
				$is_auto_image = true;
			}

		// Get the first img tag from content
		} else {

			$first_img = '';
			$post = get_post( $id );
			ob_start();
			ob_end_clean();
			$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
			if ( !empty($matches[1][0]) ) {

				// Save Image URL
				$custom_field = esc_url( $matches[1][0] );

				// Search for ALT tag
				$output = preg_match_all( '/<img.+alt=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
				if ( !empty($matches[1][0]) ) {
					$alt = esc_attr( $matches[1][0] );
				}
			}

		}

	}

	// Check if there is YouTube embed
	if ( empty( $custom_field ) && empty( $img_link ) ) {
		$embed = esc_html( get_post_meta( $id, 'embed', true ) );
		if ( $embed )
	    	$custom_field = esc_url( woo_get_video_image( $embed ) );
	}

	// Return if there is no attachment or custom field set
	if ( empty( $custom_field ) && empty( $img_link ) ) {

		// Check if default placeholder image is uploaded
		$placeholder = get_option( 'framework_woo_default_image' );
		if ( $placeholder && !(is_singular() && in_the_loop()) ) {
			$custom_field = esc_url( $placeholder );

			// Resize the placeholder if
			if ( get_option( 'woo_post_image_support' ) == 'true' && get_option( 'woo_pis_resize' ) == 'true' ) {
				// Dynamically resize the post thumbnail
				$vt_crop = get_option( 'woo_pis_hard_crop' );
				if ($vt_crop == 'true' ) $vt_crop = true; else $vt_crop = false;
				$vt_image = vt_resize( '', $placeholder, $width, $height, $vt_crop );

				// Set fields for output
				$custom_field = esc_url( $vt_image['url'] );
				$width = $vt_image['width'];
				$height = $vt_image['height'];
			}
		} else {
	       return;
	    }
	}

	if(empty( $src_arr ) && empty( $img_link ) ) { $src_arr[] = $custom_field; }

	/* ------------------------------------------------------------------------- */
	/* BEGIN OUTPUT */
	/* ------------------------------------------------------------------------- */

    $output = '';

    // Set output height and width
    $set_width = ' width="' . esc_attr( $width ) . '" ';
    $set_height = '';

    if ( ! $noheight && 0 < $height )
    	$set_height = ' height="' . esc_attr( $height ) . '" ';

	// Set standard class
	if ( $class ) $class = 'woo-image ' . esc_attr( $class ); else $class = 'woo-image';

	// Do check to verify if images are smaller then specified.
	if($force == true){ $set_width = ''; $set_height = ''; }

	// WP Post Thumbnail
	if( ! empty( $img_link ) ) {

		if( $link == 'img' ) {  // Output the image without anchors
			$output .= wp_kses_post( $before );
			$output .= $img_link;
			$output .= wp_kses_post( $after );
		} elseif( $link == 'url' ) {  // Output the large image
			$src = wp_get_attachment_image_src( $thumb_id, 'large', true );
			$custom_field = esc_url( $src[0] );
			$output .= $custom_field;
		} else {  // Default - output with link
			if ( ( is_single() || is_page() ) && $single == false ) {
				$rel = 'rel="lightbox"';
				$href = false;
			} else {
				$href = get_permalink( $id );
				$rel = '';
			}

			$title = 'title="' . esc_attr( get_the_title( $id ) ) .'"';

			$output .= wp_kses_post( $before );
			if($href == false){
				$output .= $img_link;
			} else {
				$output .= '<a ' . $title . ' href="' . esc_url( $href ) . '" '. $rel .'>' . $img_link . '</a>';
			}

			$output .= wp_kses_post( $after );
		}
	}

	// Use thumb.php to resize. Skip if image has been natively resized with vt_resize.
	elseif ( get_option( 'woo_resize') == 'true' && empty( $vt_image['url'] ) ) {

		foreach( $src_arr as $key => $custom_field ) {

			// Clean the image URL
			$href = esc_url( $custom_field );
			$custom_field = cleanSource( $custom_field );

			// Check if WPMU and set correct path AND that image isn't external
			if ( function_exists( 'get_current_site') ) {
				get_current_site();
				//global $blog_id; Breaks with WP3 MS
				if ( !$blog_id ) {
					global $current_blog;
					$blog_id = $current_blog->blog_id;
				}
				if ( isset($blog_id) && $blog_id > 0 ) {
					$imageParts = explode( 'files/', $custom_field );
					if ( isset( $imageParts[1] ) )
						$custom_field = '/blogs.dir/' . $blog_id . '/files/' . $imageParts[1];
				}
			}

			//Set the ID to the Attachment's ID if it is an attachment
			if($is_auto_image == true){
				$quick_id = $attachment_id[$key];
			} else {
			 	$quick_id = $id;
			}

			//Set custom meta
			if ($meta) {
				$alt = $meta;
				$title = 'title="' . esc_attr( $meta ) . '"';
			} else {
				if ( ( $alt != '' ) || ! ( $alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) ) ) {
					$alt = esc_attr( get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
				} else {
					$alt = esc_attr( get_the_title( $quick_id ) );
				}
				$title = 'title="'. esc_attr( get_the_title( $quick_id ) ) .'"';
			}

			// Set alignment parameter
			if ( $alignment != '' )
				$alignment = '&amp;a=' . urlencode( $alignment );

			$img_url = esc_url( get_template_directory_uri() . '/functions/thumb.php?src=' . $custom_field . '&amp;w=' . $width . '&amp;h=' . $height . '&amp;zc=1&amp;q=' . $quality . $alignment );
			$img_link = '<img src="' . $img_url . '" alt="' . esc_attr( $alt ) . '" class="' . esc_attr( stripslashes( $class ) ) . '" ' . $set_width . $set_height . ' />';

			if( $link == 'img' ) {  // Just output the image
				$output .= wp_kses_post( $before );
				$output .= $img_link;
				$output .= wp_kses_post( $after );

			} elseif( $link == 'url' ) {  // Output the image without anchors

				if($is_auto_image == true){
					$src = wp_get_attachment_image_src($thumb_id, 'large', true);
					$custom_field = esc_url( $src[0] );
				}
				$output .= $href;

			} else {  // Default - output with link

				if ( ( is_single() || is_page() ) && $single == false ) {
					$rel = 'rel="lightbox"';
				} else {
					$href = get_permalink( $id );
					$rel = '';
				}

				$output .= wp_kses_post( $before );
				$output .= '<a ' . $title . ' href="' . esc_url( $href ) . '" ' . $rel . '>' . $img_link . '</a>';
				$output .= wp_kses_post( $after );
			}
		}

	// No dynamic resizing
	} else {
		foreach( $src_arr as $key => $custom_field ) {

			//Set the ID to the Attachment's ID if it is an attachment
			if( $is_auto_image == true && isset( $attachment_id[$key] ) ){
				$quick_id = $attachment_id[$key];
			} else {
			 	$quick_id = $id;
			}

			//Set custom meta
			if ($meta) {
				$alt = esc_attr( $meta );
				$title = 'title="'. esc_attr( $meta ) .'"';
			} else {
				if ($alt == '') $alt = esc_attr( get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
				$title = 'title="'. esc_attr( get_the_title( $quick_id ) ) .'"';
			}

			$img_link =  '<img src="'. esc_url( $custom_field ) . '" alt="' . esc_attr( $alt ) . '" ' . $set_width . $set_height . ' class="' . esc_attr( stripslashes( $class ) ) . '" />';

			if ( $link == 'img' ) {  // Just output the image
				$output .= wp_kses_post( $before );
				$output .= $img_link;
				$output .= wp_kses_post( $after );

			} elseif( $link == 'url' ) {  // Output the URL to original image
				if ( $vt_image['url'] || $is_auto_image ) {
					$src = wp_get_attachment_image_src( $thumb_id, 'full', true );
					$custom_field = esc_url( $src[0] );
				}
				$output .= $custom_field;

			} else {  // Default - output with link

				if ( ( is_single() || is_page() ) && $single == false ) {

					// Link to the large image if single post
					if ( $vt_image['url'] || $is_auto_image ) {
						$src = wp_get_attachment_image_src( $thumb_id, 'full', true );
						$custom_field = esc_url( $src[0] );
					}

					$href = $custom_field;
					$rel = 'rel="lightbox"';
				} else {
					$href = get_permalink( $id );
					$rel = '';
				}

				$output .= wp_kses_post( $before );
				$output .= '<a href="' . esc_url( $href ) . '" ' . $rel . ' ' . $title . '>' . $img_link . '</a>';
				$output .= wp_kses_post( $after );
			}
		}
	}

	// Remove no height attribute - IE fix when no height is set
	$output = str_replace( 'height=""', '', $output );
	$output = str_replace( 'height="0"', '', $output );

	// Return or echo the output
	if ( $return == TRUE )
		return $output;
	else
		echo $output; // Done

}
}

/* Get thumbnail from Video Embed code */
if ( ! function_exists( 'woo_get_video_image' ) ) {
function woo_get_video_image( $embed ) {
	$video_thumb = '';

	// YouTube - get the video code if this is an embed code (old embed)
	preg_match( '/youtube\.com\/v\/([\w\-]+)/', $embed, $match );

	// YouTube - if old embed returned an empty ID, try capuring the ID from the new iframe embed
	if( ! isset( $match[1] ) )
		preg_match( '/youtube\.com\/embed\/([\w\-]+)/', $embed, $match );

	// YouTube - if it is not an embed code, get the video code from the youtube URL
	if( ! isset( $match[1] ) )
		preg_match( '/v\=(.+)&/', $embed, $match );

	// YouTube - get the corresponding thumbnail images
	if( isset( $match[1] ) )
		$video_thumb = "http://img.youtube.com/vi/" . urlencode( $match[1] ) . "/0.jpg";

	// return whichever thumbnail image you would like to retrieve
	return $video_thumb;
} // End woo_get_video_image()
}


/*-----------------------------------------------------------------------------------*/
/* vt_resize - Resize images dynamically using wp built in functions
/*-----------------------------------------------------------------------------------*/
/*
 * Resize images dynamically using wp built in functions
 * Victor Teixeira
 *
 * php 5.2+
 *
 * Exemplo de uso:
 *
 * <?php
 * $thumb = get_post_thumbnail_id();
 * $image = vt_resize( $thumb, '', 140, 110, true );
 * ?>
 * <img src="<?php echo $image[url]; ?>" width="<?php echo $image[width]; ?>" height="<?php echo $image[height]; ?>" />
 *
 * @param int $attach_id
 * @param string $img_url
 * @param int $width
 * @param int $height
 * @param bool $crop
 * @return array
 */
if ( ! function_exists( 'vt_resize' ) ) {
	function vt_resize( $attach_id = null, $img_url = null, $width, $height, $crop = false ) {

		// Cast $width and $height to integer
		$width = intval( $width );
		$height = intval( $height );

		// this is an attachment, so we have the ID
		if ( $attach_id ) {
			$image_src = wp_get_attachment_image_src( $attach_id, 'full' );
			$file_path = get_attached_file( $attach_id );
		// this is not an attachment, let's use the image url
		} else if ( $img_url ) {
			$file_path = parse_url( esc_url( $img_url ) );
			$file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];

			//$file_path = ltrim( $file_path['path'], '/' );
			//$file_path = rtrim( ABSPATH, '/' ).$file_path['path'];

			$orig_size = getimagesize( $file_path );

			$image_src[0] = $img_url;
			$image_src[1] = $orig_size[0];
			$image_src[2] = $orig_size[1];
		}

		$file_info = pathinfo( $file_path );

		// check if file exists
		if ( !isset( $file_info['dirname'] ) && !isset( $file_info['filename'] ) && !isset( $file_info['extension'] )  )
			return;
		
		$base_file = $file_info['dirname'].'/'.$file_info['filename'].'.'.$file_info['extension'];
		if ( !file_exists($base_file) )
			return;

		$extension = '.'. $file_info['extension'];

		// the image path without the extension
		$no_ext_path = $file_info['dirname'].'/'.$file_info['filename'];

		$cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;

		// checking if the file size is larger than the target size
		// if it is smaller or the same size, stop right here and return
		if ( $image_src[1] > $width ) {
			// the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
			if ( file_exists( $cropped_img_path ) ) {
				$cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );

				$vt_image = array (
					'url' => $cropped_img_url,
					'width' => $width,
					'height' => $height
				);
				return $vt_image;
			}

			// $crop = false or no height set
			if ( $crop == false OR !$height ) {
				// calculate the size proportionaly
				$proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
				$resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;

				// checking if the file already exists
				if ( file_exists( $resized_img_path ) ) {
					$resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );

					$vt_image = array (
						'url' => $resized_img_url,
						'width' => $proportional_size[0],
						'height' => $proportional_size[1]
					);
					return $vt_image;
				}
			}

			// check if image width is smaller than set width
			$img_size = getimagesize( $file_path );
			if ( $img_size[0] <= $width ) $width = $img_size[0];
			
			// Check if GD Library installed
			if ( ! function_exists ( 'imagecreatetruecolor' ) ) {
			    echo 'GD Library Error: imagecreatetruecolor does not exist - please contact your webhost and ask them to install the GD library';
			    return;
			}

			// no cache files - let's finally resize it
			if ( function_exists( 'wp_get_image_editor' ) ) {
				$image = wp_get_image_editor( $file_path );
				if ( ! is_wp_error( $image ) ) {
					$image->resize( $width, $height, $crop );
					$save_data = $image->save();
					if ( isset( $save_data['path'] ) ) $new_img_path = $save_data['path'];
				}
			} else {
				$new_img_path = image_resize( $file_path, $width, $height, $crop );
			}		
			
			$new_img_size = getimagesize( $new_img_path );
			$new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );

			// resized output
			$vt_image = array (
				'url' => $new_img,
				'width' => $new_img_size[0],
				'height' => $new_img_size[1]
			);

			return $vt_image;
		}

		// default output - without resizing
		$vt_image = array (
			'url' => $image_src[0],
			'width' => $width,
			'height' => $height
		);

		return $vt_image;
	}
}

/*-----------------------------------------------------------------------------------*/
/* Depreciated - woo_get_image - Get Image from custom field */
/*-----------------------------------------------------------------------------------*/

// Depreciated
function woo_get_image($key = 'image', $width = null, $height = null, $class = "thumbnail", $quality = 90,$id = null,$link = 'src',$repeat = 1,$offset = 0,$before = '', $after = '',$single = false, $force = false, $return = false) {
	// Run new function
	woo_image( 'key='.$key.'&width='.$width.'&height='.$height.'&class='.$class.'&quality='.$quality.'&id='.$id.'&link='.$link.'&repeat='.$repeat.'&offset='.$offset.'&before='.$before.'&after='.$after.'&single='.$single.'&fore='.$force.'&return='.$return );
	return;
} // End woo_get_image()

/*-----------------------------------------------------------------------------------*/
/* woo_embed - Get Video embed code from custom field */
/*-----------------------------------------------------------------------------------*/

/*
Get Video
This function gets the embed code from the custom field
Parameters:
        $key = Custom field key eg. "embed"
        $width = Set width manually without using $type
        $height = Set height manually without using $type
		$class = Custom class to apply to wrapping div
		$id = ID from post to pull custom field from
*/

if ( ! function_exists( 'woo_embed' ) ) {
function woo_embed($args) {
	//Defaults
	$key = 'embed';
	$width = null;
	$height = null;
	$class = 'video';
	$id = null;

	if ( ! is_array( $args ) )
		parse_str( $args, $args );

	extract( $args );

  if( empty( $id ) ) {
    global $post;
    $id = $post->ID;
  }

// Cast $width and $height to integer
$width = intval( $width );
$height = intval( $height );
	
$custom_field = esc_textarea( get_post_meta( $id, $key, true ) );

if ($custom_field) :
	$custom_field = html_entity_decode( $custom_field ); // Decode HTML entities.

    $org_width = $width;
    $org_height = $height;
    $calculated_height = '';
    $embed_width = '';
    $embed_height = '';

    // Get custom width and height
    $custom_width = esc_html( get_post_meta( $id, 'width', true ) );
    $custom_height = esc_html( get_post_meta( $id, 'height', true ) );

    //Dynamic Height Calculation
    if ($org_height == '' && $org_width != '') {
    	$raw_values = explode(  ' ', $custom_field);

    	foreach ( $raw_values as $raw ) {
    		$embed_params = explode( '=', $raw );
    		if ( $embed_params[0] == 'width' ) {
   		 		$embed_width = preg_replace( '/[^0-9]/', '', $embed_params[1]);
    		}
    		elseif ( $embed_params[0] == 'height' ) {
    			$embed_height = preg_replace( '/[^0-9]/', '', $embed_params[1]);
    		}
    	}

    	$float_width = floatval( $embed_width );
		$float_height = floatval( $embed_height );
		@$float_ratio = $float_height / $float_width;
		$calculated_height = intval( $float_ratio * $width );
    }

    // Set values: width="XXX", height="XXX"
    if ( ! $custom_width ) $width = 'width="' . esc_attr( $width ) . '"'; else $width = 'width="' . esc_attr( $custom_width ) . '"';
    if ( $height == '' ) { $height = 'height="' . esc_attr( $calculated_height ) . '"'; } else { if ( ! $custom_height ) { $height = 'height="' . esc_attr( $height ) . '"'; } else { $height = 'height="' . esc_attr( $custom_height ) . '"'; } }
    $custom_field = stripslashes($custom_field);
    $custom_field = preg_replace( '/width="([0-9]*)"/' , $width , $custom_field );
    $custom_field = preg_replace( '/height="([0-9]*)"/' , $height, $custom_field );

    // Set values: width:XXXpx, height:XXXpx
    if ( ! $custom_width ) $width = 'width:' . esc_attr( $org_width ) . 'px'; else $width = 'width:' . esc_attr( $custom_width ) . 'px';
    if (  $height == '' ) { $height = 'height:' . esc_attr( $calculated_height ) . 'px'; } else { if ( ! $custom_height ) { $height = 'height:' . esc_attr( $org_height ) . 'px'; } else { $height = 'height:' . esc_attr( $custom_height ) . 'px'; } }
    $custom_field = stripslashes($custom_field);
    $custom_field = preg_replace( '/width:([0-9]*)px/' , $width , $custom_field );
    $custom_field = preg_replace( '/height:([0-9]*)px/' , $height, $custom_field );

	// Suckerfish menu hack
	$custom_field = str_replace( '<embed ', '<param name="wmode" value="transparent"></param><embed wmode="transparent" ', $custom_field );
	$custom_field = str_replace( '<iframe ', '<iframe wmode="transparent" ', $custom_field );
	$custom_field = str_replace( '" frameborder="', '?wmode=transparent" frameborder="', $custom_field );

	// Find and sanitize video URL. Add "wmode=transparent" to URL.
	$video_url = preg_match( '/src=["\']?([^"\' ]*)["\' ]/is', $custom_field, $matches );
	if ( isset( $matches[1] ) ) {
		$custom_field = str_replace( $matches[0], 'src="' . esc_url( add_query_arg( 'wmode', 'transparent', $matches[1] ) ) . '"', $custom_field );
	}

	$output = '';
    $output .= '<div class="'. esc_attr( $class ) .'">' . $custom_field . '</div>';

	return apply_filters( 'woo_embed', $output );
else :
	return false;
endif;
}
}

/*-----------------------------------------------------------------------------------*/
/* Add default filters to woo_embed() */
/*-----------------------------------------------------------------------------------*/

add_filter( 'woo_embed', 'do_shortcode' );

/*-----------------------------------------------------------------------------------*/
/* Depreciated - woo_get_embed - Get Video embed code from custom field */
/*-----------------------------------------------------------------------------------*/
// Depreciated
function woo_get_embed($key = 'embed', $width, $height, $class = 'video', $id = null) {
	// Run new function
	return woo_embed( 'key='.$key.'&width='.$width.'&height='.$height.'&class='.$class.'&id='.$id );

}

/*-----------------------------------------------------------------------------------*/
/* Woo Show Page Menu */
/*-----------------------------------------------------------------------------------*/
// Show menu in header.php
// Exlude the pages from the slider
function woo_show_pagemenu( $exclude = '' ) {
    // Split the featured pages from the options, and put in an array
    if ( get_option( 'woo_ex_featpages') ) {
        $menupages = get_option( 'woo_featpages' );
        $exclude = $menupages . ',' . $exclude;
    }

    $pages = wp_list_pages( 'sort_column=menu_order&title_li=&echo=0&depth=1&exclude=' . $exclude );
    $pages = preg_replace( '%<a ([^>]+)>%U','<a $1><span>', $pages );
    $pages = str_replace( '</a>','</span></a>', $pages );
    echo $pages;
} // End woo_show_pagemenu()

/*-----------------------------------------------------------------------------------*/
/* Get the style path currently selected */
/*-----------------------------------------------------------------------------------*/
function woo_style_path() {
	$return = '';

	$style = $_REQUEST['style'];

	// Sanitize request input.
	$style = esc_attr( strtolower( trim( strip_tags( $style ) ) ) );

	if ( $style != '' ) {
		$style_path = $style;
	} else {
		$stylesheet = esc_attr( get_option( 'woo_alt_stylesheet' ) );

		// Prevent against an empty return to $stylesheet.
		if ( $stylesheet == '' ) {
			$stylesheet = 'default.css';
		}

		$style_path = str_replace( '.css', '', $stylesheet );
	}

	if ( $style_path == 'default' ) {
		$return = 'images';
	} else {
		$return = 'styles/' . $style_path;
	}

	echo esc_html( $return );
} // End woo_style_path()


/*-----------------------------------------------------------------------------------*/
/* Get page ID */
/*-----------------------------------------------------------------------------------*/
function get_page_id( $page_slug ) {
	$page_id = get_page_by_path( $page_slug );
    if ($page_id) {
        return $page_id->ID;
    } else {
        return null;
    }
} // End get_page_id()

/*-----------------------------------------------------------------------------------*/
/* Tidy up the image source url */
/*-----------------------------------------------------------------------------------*/
function cleanSource( $src ) {
	// remove slash from start of string
	if(strpos($src, "/") == 0) {
		$src = substr($src, -(strlen($src) - 1));
	}

	// Check if same domain so it doesn't strip external sites
	$host = str_replace( 'www.', '', $_SERVER['HTTP_HOST'] );
	if ( ! strpos( $src, $host ) )
		return $src;


	$regex = "/^((ht|f)tp(s|):\/\/)(www\.|)" . $host . "/i";
	$src = preg_replace ( $regex, '', $src );
	$src = htmlentities ( $src );

    // remove slash from start of string
    if ( strpos( $src, '/' ) === 0 ) {
        $src = substr ( $src, -( strlen( $src ) - 1 ) );
    }

	return $src;
} // End cleanSource()

/*-----------------------------------------------------------------------------------*/
/* Show image in RSS feed */
/* Original code by Justin Tadlock */
/*-----------------------------------------------------------------------------------*/
if ( get_option( 'woo_rss_thumb' ) == 'true' ) {
	if ( get_option( 'rss_use_excerpt' ) ) 
		add_filter( 'the_excerpt_rss', 'add_image_RSS' );
	else
		add_filter( 'the_content', 'add_image_RSS' );
}

function add_image_RSS( $content ) {

	global $post, $id;
	$blog_key = substr( md5( home_url( '/' ) ), 0, 16 );
	if ( ! is_feed() ) return $content;

	// Get the "image" from custom field
	//$image = get_post_meta($post->ID, 'image', $single = true);
	$image = woo_image( 'return=true&link=url' ); 
	$image_width = '240';

	// If there's an image, display the image with the content
	if( $image != '' ) {
		$content = '<p style="float:right; margin:0 0 10px 15px; width:' . esc_attr( $image_width ) . 'px;">
		<img src="' . esc_url( $image ) . '" width="' . esc_attr( $image_width ) . '" />
		</p>' . $content;
		return $content;
	} else {
		// If there's not an image, just display the content
		$content = $content;
		return $content;
	}
} // End add_image_RSS()



/*-----------------------------------------------------------------------------------*/
/* Show analytics code in footer */
/*-----------------------------------------------------------------------------------*/
function woo_analytics(){
	$output = get_option( 'woo_google_analytics' );
	if ( $output != '' )
		echo stripslashes( $output ) . "\n";
} // End woo_analytics()
add_action( 'wp_footer','woo_analytics' );



/*-----------------------------------------------------------------------------------*/
/* Browser detection body_class() output */
/*-----------------------------------------------------------------------------------*/
add_filter( 'body_class','browser_body_class' );
function browser_body_class( $classes ) {
	global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

	if($is_lynx) $classes[] = 'lynx';
	elseif($is_gecko) $classes[] = 'gecko';
	elseif($is_opera) $classes[] = 'opera';
	elseif($is_NS4) $classes[] = 'ns4';
	elseif($is_safari) $classes[] = 'safari';
	elseif($is_chrome) $classes[] = 'chrome';
	elseif($is_IE) {
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$browser = substr( "$browser", 25, 8);
		if ($browser == "MSIE 7.0"  ) {
			$classes[] = 'ie7';
			$classes[] = 'ie';
		} elseif ($browser == "MSIE 6.0" ) {
			$classes[] = 'ie6';
			$classes[] = 'ie';
		} elseif ($browser == "MSIE 8.0" ) {
			$classes[] = 'ie8';
			$classes[] = 'ie';
		} elseif ($browser == "MSIE 9.0" ) {
			$classes[] = 'ie9';
			$classes[] = 'ie';
		} else {
			$classes[] = 'ie';
		}
	}
	else $classes[] = 'unknown';

	if( $is_iphone ) $classes[] = 'iphone';

	// Alternative style body class.
	$style = get_option( 'woo_alt_stylesheet', 'default' );
	$style = str_replace( '.css', '', $style );
	if ( '' != $style ) {
		$classes[] = 'alt-style-' . esc_attr( $style );
	}
	return $classes;
} // End browser_body_class()

/*-----------------------------------------------------------------------------------*/
/* Twitter's Blogger.js output for Twitter widgets */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_twitter_script' ) ) {
	function woo_twitter_script($unique_id,$username,$limit) {
	?>
	<script type="text/javascript">
	<!--//--><![CDATA[//><!--

	    function twitterCallback2(twitters) {

	      var statusHTML = [];
	      for (var i=0; i<twitters.length; i++){
	        var username = twitters[i].user.screen_name;
	        var status = twitters[i].text.replace(/((https?|s?ftp|ssh)\:\/\/[^"\s\<\>]*[^.,;'">\:\s\<\>\)\]\!])/g, function(url) {
	          return '<a href="'+url+'">'+url+'</a>';
	        }).replace(/\B@([_a-z0-9]+)/ig, function(reply) {
	          return  reply.charAt(0)+'<a href="http://twitter.com/'+reply.substring(1)+'">'+reply.substring(1)+'</a>';
	        });
	        statusHTML.push( '<li><span class="content">'+status+'</span> <a style="font-size:85%" class="time" href="http://twitter.com/'+username+'/statuses/'+twitters[i].id_str+'">'+relative_time(twitters[i].created_at)+'</a></li>' );
	      }
	      document.getElementById( 'twitter_update_list_<?php echo esc_attr( $unique_id ); ?>').innerHTML = statusHTML.join( '' );
	    }

	    function relative_time(time_value) {
	      var values = time_value.split( " " );
	      time_value = values[1] + " " + values[2] + ", " + values[5] + " " + values[3];
	      var parsed_date = Date.parse(time_value);
	      var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
	      var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
	      delta = delta + (relative_to.getTimezoneOffset() * 60);

	      if (delta < 60) {
	        return '<?php esc_attr_e( 'less than a minute ago', 'woothemes' ); ?>';
	      } else if(delta < 120) {
	        return '<?php esc_attr_e( 'about a minute ago', 'woothemes' ); ?>';
	      } else if(delta < (60*60)) {
	        return (parseInt(delta / 60)).toString() + ' <?php esc_attr_e( 'minutes ago', 'woothemes' ); ?>';
	      } else if(delta < (120*60)) {
	        return 'about an hour ago';
	      } else if(delta < (24*60*60)) {
	        return 'about ' + (parseInt(delta / 3600)).toString() + ' <?php esc_attr_e( 'hours ago', 'woothemes' ); ?>';
	      } else if(delta < (48*60*60)) {
	        return '1 day ago';
	      } else {
	        return (parseInt(delta / 86400)).toString() + ' <?php esc_attr_e( 'days ago', 'woothemes' ); ?>';
	      }
	    }
	//-->!]]>
	</script>
	<script type="text/javascript" src="http<?php if (is_ssl()) { echo 's'; } ?>://api.twitter.com/1/statuses/user_timeline/<?php echo esc_attr( $username ); ?>.json?callback=twitterCallback2&amp;count=<?php echo esc_attr( $limit ); ?>&amp;include_rts=t"></script>
	<?php
	} // End woo_twitter_script()
}

/*-----------------------------------------------------------------------------------*/
/* Deprecated: Template Detector */
/*-----------------------------------------------------------------------------------*/
function woo_active_template( $filename = null ) {

	trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s of the WooFramework! Please upgrade your Theme to the latest version.', 'woothemes' ), 'woo_active_template', '5.4' ) );
	return false; // No $filename argument was set
	
} // End woo_active_template()

/*-----------------------------------------------------------------------------------*/
/* WooFramework Update Page */
/*-----------------------------------------------------------------------------------*/

function woothemes_framework_update_page() {

	// Clear transients.
	delete_transient( 'woo_framework_critical_update' );
	delete_transient( 'woo_framework_critical_update_data' );
	delete_transient( 'wooframework_version_data' );

        $method = get_filesystem_method();
        
        $to = ABSPATH . 'wp-content/themes/' . get_option( 'template' ) . '/functions/';
        if(isset($_POST['password'])){

            $cred = $_POST;
            $filesystem = WP_Filesystem($cred);

        }
        elseif(isset($_POST['woo_ftp_cred'])){

             $cred = unserialize(base64_decode($_POST['woo_ftp_cred']));
             $filesystem = WP_Filesystem($cred);

        } else {

           $filesystem = WP_Filesystem();

        };
        $url = admin_url( 'admin.php?page=woothemes_framework_update' );
        ?>
            <div class="wrap themes-page">
        <?php
            if($filesystem == false){

            request_filesystem_credentials ( $url );

            }  else {

            // Clear the transient to force a fresh update.
            delete_transient( 'wooframework_version_data' );
            	
            $localversion = esc_html( get_option( 'woo_framework_version' ) );
            $remoteversion = woo_get_fw_version();
            
            // Test if new version
            $upd = false;
			$loc = explode( '.',$localversion);
			$rem = explode( '.',$remoteversion['version']);

            if( $loc[0] < $rem[0] )
            	$upd = true;
            elseif ( $loc[1] < $rem[1] )
            	$upd = true;
            elseif( $loc[2] < $rem[2] )
            	$upd = true;

            ?>
            <?php screen_icon( 'tools' ); ?>
            <h2>Framework Update</h2>
            <span style="display:none"><?php echo $method; ?></span>
            <form method="post"  enctype="multipart/form-data" id="wooform" action="<?php /* echo $url; */ ?>">

                <?php if( $upd ) { ?>
                <?php wp_nonce_field( 'update-options' ); ?>
                <h3>A new version of WooFramework is available.</h3>
                <p>This updater will download and extract the latest WooFramework files to your current theme's functions folder. </p>
                <p>We recommend backing up your theme files and updating WordPress to latest version before proceeding.</p>
                <p>&rarr; <strong>Your version:</strong> <?php echo $localversion; ?></p>

                <p>&rarr; <strong>Current Version:</strong> <?php echo $remoteversion['version']; ?></p>

                <input type="submit" class="button" value="Update Framework" />
                <?php } else { ?>
                <h3>You have the latest version of WooFramework</h3>
                <p>&rarr; <strong>Your version:</strong> <?php echo $localversion; ?></p>
                <?php } ?>
                <input type="hidden" name="woo_update_save" value="save" />
                <input type="hidden" name="woo_ftp_cred" value="<?php echo esc_attr( base64_encode(serialize($_POST))); ?>" />

            </form>
            <?php } ?>
            </div>
            <?php
};

/*-----------------------------------------------------------------------------------*/
/* WooFramework Update Head */
/*-----------------------------------------------------------------------------------*/

function woothemes_framework_update_head() {
  if( isset( $_REQUEST['page'] ) ) {
	// Sanitize page being requested.
	$_page = esc_attr( $_REQUEST['page'] );

	if( $_page == 'woothemes_framework_update' ) {
		//Setup Filesystem
		$method = get_filesystem_method();

		if( isset( $_POST['woo_ftp_cred'] ) ) {
			$cred = unserialize( base64_decode( $_POST['woo_ftp_cred'] ) );
			$filesystem = WP_Filesystem($cred);
		} else {
		   $filesystem = WP_Filesystem();
		}

		if( $filesystem == false && $_POST['upgrade'] != 'Proceed' ) {

			function woothemes_framework_update_filesystem_warning() {
					$method = get_filesystem_method();
					echo "<div id='filesystem-warning' class='updated fade'><p>Failed: Filesystem preventing downloads. ( ". $method .")</p></div>";
				}
				add_action( 'admin_notices', 'woothemes_framework_update_filesystem_warning' );
				return;
		}
		if(isset($_REQUEST['woo_update_save'])){

			// Sanitize action being requested.
			$_action = esc_attr( $_REQUEST['woo_update_save'] );

		if( $_action == 'save' ) {

		$temp_file_addr = download_url( esc_url( 'http://www.woothemes.com/updates/framework.zip' ) );

		if ( is_wp_error($temp_file_addr) ) {

			$error = esc_html( $temp_file_addr->get_error_code() );

			if( $error == 'http_no_url' ) {
			//The source file was not found or is invalid
				function woothemes_framework_update_missing_source_warning() {
					echo "<div id='source-warning' class='updated fade'><p>Failed: Invalid URL Provided</p></div>";
				}
				add_action( 'admin_notices', 'woothemes_framework_update_missing_source_warning' );
			} else {
				function woothemes_framework_update_other_upload_warning() {
					echo "<div id='source-warning' class='updated fade'><p>Failed: Upload - $error</p></div>";
				}
				add_action( 'admin_notices', 'woothemes_framework_update_other_upload_warning' );

			}

			return;

		  }
		//Unzip it
		global $wp_filesystem;
		$to = $wp_filesystem->wp_content_dir() . "/themes/" . get_option( 'template' ) . "/functions/";

		$dounzip = unzip_file($temp_file_addr, $to);

		unlink($temp_file_addr); // Delete Temp File

		if ( is_wp_error($dounzip) ) {

			//DEBUG
			$error = esc_html( $dounzip->get_error_code() );
			$data = $dounzip->get_error_data($error);
			//echo $error. ' - ';
			//print_r($data);

			if($error == 'incompatible_archive') {
				//The source file was not found or is invalid
				function woothemes_framework_update_no_archive_warning() {
					echo "<div id='woo-no-archive-warning' class='updated fade'><p>Failed: Incompatible archive</p></div>";
				}
				add_action( 'admin_notices', 'woothemes_framework_update_no_archive_warning' );
			}
			if($error == 'empty_archive') {
				function woothemes_framework_update_empty_archive_warning() {
					echo "<div id='woo-empty-archive-warning' class='updated fade'><p>Failed: Empty Archive</p></div>";
				}
				add_action( 'admin_notices', 'woothemes_framework_update_empty_archive_warning' );
			}
			if($error == 'mkdir_failed') {
				function woothemes_framework_update_mkdir_warning() {
					echo "<div id='woo-mkdir-warning' class='updated fade'><p>Failed: mkdir Failure</p></div>";
				}
				add_action( 'admin_notices', 'woothemes_framework_update_mkdir_warning' );
			}
			if($error == 'copy_failed') {
				function woothemes_framework_update_copy_fail_warning() {
					echo "<div id='woo-copy-fail-warning' class='updated fade'><p>Failed: Copy Failed</p></div>";
				}
				add_action( 'admin_notices', 'woothemes_framework_update_copy_fail_warning' );
			}

			return;

		}

		function woothemes_framework_updated_success() {
			echo "<div id='framework-upgraded' class='updated fade'><p>New framework successfully downloaded, extracted and updated.</p></div>";
		}
		
		add_action( 'admin_notices', 'woothemes_framework_updated_success' );

		}
	}
	} //End user input save part of the update
 }
}

add_action( 'admin_head', 'woothemes_framework_update_head' );

/*-----------------------------------------------------------------------------------*/
/* WooFramework Version Getter */
/*-----------------------------------------------------------------------------------*/

function woo_get_fw_version( $url = '', $check_if_critical = false ) {

	if( ! empty( $url ) ) {
		$fw_url = $url;
	} else {
    	$fw_url = 'http://www.woothemes.com/updates/functions-changelog.txt';
    }
    
    $output = array( 'version' => '', 'is_critical' => false );
    
    $version_data = get_transient( 'wooframework_version_data' );

	if ( $version_data != '' && $check_if_critical == false ) { return $version_data; }

	$temp_file_addr = download_url( $fw_url );
	if( ! is_wp_error( $temp_file_addr ) && $file_contents = file( $temp_file_addr ) ) {
        foreach ( $file_contents as $line_num => $line ) {
            $current_line =  $line;

            if( $line_num > 1 ) {    // Not the first or second... dodgy :P

                if ( preg_match( '/^[0-9]/', $line ) ) {
                
						// Do critical update check.
						if ( $check_if_critical && ( strtolower( trim( substr( $line, -10 ) ) ) == 'critical' ) ) {
							$output['is_critical'] = true;
						}

                        $current_line = stristr( $current_line, 'version' );
                        $current_line = preg_replace( '~[^0-9,.]~','',$current_line );
                        $output['version'] = $current_line;
                        break;
                }
            }
        }
        unlink( $temp_file_addr );
    } else {
        $output['version'] = get_option( 'woo_framework_version' );
    }
    
    // Set the transient containing the latest version number.
	set_transient( 'wooframework_version_data', $output , 60*60*24 );
	
	return $output;
} // End woo_get_fw_version()


/*-----------------------------------------------------------------------------------*/
/* WooFramework Version Checker */
/*-----------------------------------------------------------------------------------*/

function woo_framework_version_checker( $local_version, $check_if_critical = false ) {
	$data = array( 'is_update' => false, 'version' => '1.0.0', 'status' => 'none' );
	
	if ( ! $local_version ) { return $data; }
	
	$version_data = woo_get_fw_version( '', $check_if_critical );
	
	$check = version_compare( $version_data['version'], $local_version ); // Returns 1 if there is an update available.
	
	if ( $check == 1 ) {
		$data['is_update'] = true;
		$data['version'] = $version_data['version'];
		$data['is_critical'] = $version_data['is_critical'];
	}
	
	return $data;
} // End woo_framework_version_checker()

/*-----------------------------------------------------------------------------------*/
/* Woo URL shortener */
/*-----------------------------------------------------------------------------------*/

function woo_short_url($url) {
	$service = get_option( 'woo_url_shorten' );
	$bitlyapilogin = get_option( 'woo_bitly_api_login' );;
	$bitlyapikey = get_option( 'woo_bitly_api_key' );;
	if (isset($service)) {
		switch ($service)
		{
    		case 'TinyURL':
    			$shorturl = getTinyUrl($url);
    			break;
    		case 'Bit.ly':
    			if (isset($bitlyapilogin) && isset($bitlyapikey) && ($bitlyapilogin != '') && ($bitlyapikey != '')) {
    				$shorturl = make_bitly_url($url,$bitlyapilogin,$bitlyapikey,'json' );
    			}
    			else {
    				$shorturl = getTinyUrl($url);
    			}
    			break;
    		case 'Off':
    			$shorturl = $url;
    			break;
    		default:
    			$shorturl = $url;
    			break;
    	}
	}
	else {
		$shorturl = $url;
	}
	return $shorturl;
}

//TinyURL
function getTinyUrl($url) {
	$tinyurl = file_get_contents_curl( "http://tinyurl.com/api-create.php?url=".$url);
	return $tinyurl;
}

//Bit.ly
function make_bitly_url($url,$login,$appkey,$format = 'xml',$version = '2.0.1')
{
	//create the URL
	$bitly = 'http://api.bit.ly/shorten?version='.$version.'&longUrl='.urlencode($url).'&login='.$login.'&apiKey='.$appkey.'&format='.$format;

	//get the url
	//could also use cURL here
	$response = file_get_contents_curl($bitly);

	//parse depending on desired format
	if(strtolower($format) == 'json')
	{
		$json = @json_decode($response,true);
		return $json['results'][$url]['shortUrl'];
	}
	else //xml
	{
		$xml = simplexml_load_string($response);
		return 'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
	}
}

//Alternative CURL function
function file_get_contents_curl($url) {
	if ( $url == '' || $url == null ) { return ''; }
	$data = '';
	
	$response = wp_remote_get( $url );

	if ( is_wp_error( $response ) ) {
		$data  = $url;
	} else {
		$data = $response['body'];
	}
	
	return $data;
} // End file_get_contents_curl()

// Checks for presence of the cURL extension.
function _iscurlinstalled() {
	if  (in_array  ( 'curl', get_loaded_extensions())) {
		if (function_exists( 'curl_init')) {
			return true;
		} else {
			return false;
		}
	}
	else{
		if (function_exists( 'curl_init')) {
			return true;
		} else {
			return false;
		}
	}
}

/*-----------------------------------------------------------------------------------*/
/* woo_title() */
/*-----------------------------------------------------------------------------------*/

function woo_title () {
	$sep = '|';
	$raw_title = wp_title( $sep, false, 'right' );
	$title = $raw_title . get_bloginfo( 'name' );

	// Allow child themes/plugins to filter the title value.
	$title = apply_filters( 'woo_title', $title, $sep, $raw_title );

	// Display the formatted title.
	echo $title;
} // End woo_title()

/*-----------------------------------------------------------------------------------*/
/* woo_meta() */
/*-----------------------------------------------------------------------------------*/

function woo_meta () {
	echo '<meta http-equiv="Content-Type" content="'. esc_attr( get_bloginfo( 'html_type' ) ) . '; charset=' . esc_attr( get_bloginfo( 'charset' ) ) . '" />' . "\n";

	do_action( 'woo_meta' );
} // End woo_meta()

/*-----------------------------------------------------------------------------------*/
/* Woo Text Trimmer */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_text_trim' ) ) {
	function woo_text_trim( $text, $words = 50 ) {
		$matches = preg_split( "/\s+/", $text, $words + 1);
		$sz = count($matches);
		if ($sz > $words)
		{
			unset($matches[$sz-1]);
			return implode( ' ',$matches)." ...";
		}
		return $text;
	} // End woo_text_trim()
}

/*-----------------------------------------------------------------------------------*/
/* Google Webfonts Array */
/* Documentation:
/*
/* name: The name of the Google Font.
/* variant: The Google Font API variants available for the font.
/*-----------------------------------------------------------------------------------*/

// Available Google webfont names
$google_fonts = array(	array( 'name' => "Cantarell", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Cardo", 'variant' => ''),
						array( 'name' => "Crimson Text", 'variant' => ''),
						array( 'name' => "Droid Sans", 'variant' => ':r,b'),
						array( 'name' => "Droid Sans Mono", 'variant' => ''),
						array( 'name' => "Droid Serif", 'variant' => ':r,b,i,bi'),
						array( 'name' => "IM Fell DW Pica", 'variant' => ':r,i'),
						array( 'name' => "Inconsolata", 'variant' => ''),
						array( 'name' => "Josefin Sans", 'variant' => ':400,400italic,700,700italic'),
						array( 'name' => "Josefin Slab", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Lobster", 'variant' => ''),
						array( 'name' => "Molengo", 'variant' => ''),
						array( 'name' => "Nobile", 'variant' => ':r,b,i,bi'),
						array( 'name' => "OFL Sorts Mill Goudy TT", 'variant' => ':r,i'),
						array( 'name' => "Old Standard TT", 'variant' => ':r,b,i'),
						array( 'name' => "Reenie Beanie", 'variant' => ''),
						array( 'name' => "Tangerine", 'variant' => ':r,b'),
						array( 'name' => "Vollkorn", 'variant' => ':r,b'),
						array( 'name' => "Yanone Kaffeesatz", 'variant' => ':r,b'),
						array( 'name' => "Cuprum", 'variant' => ''),
						array( 'name' => "Neucha", 'variant' => ''),
						array( 'name' => "Neuton", 'variant' => ''),
						array( 'name' => "PT Sans", 'variant' => ':r,b,i,bi'),
						array( 'name' => "PT Sans Caption", 'variant' => ':r,b'),
						array( 'name' => "PT Sans Narrow", 'variant' => ':r,b'),
						array( 'name' => "Philosopher", 'variant' => ''),
						array( 'name' => "Allerta", 'variant' => ''),
						array( 'name' => "Allerta Stencil", 'variant' => ''),
						array( 'name' => "Arimo", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Arvo", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Bentham", 'variant' => ''),
						array( 'name' => "Coda", 'variant' => ':800'),
						array( 'name' => "Cousine", 'variant' => ''),
						array( 'name' => "Covered By Your Grace", 'variant' => ''),
			 			array( 'name' => "Geo", 'variant' => ''),
						array( 'name' => "Just Me Again Down Here", 'variant' => ''),
						array( 'name' => "Puritan", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Raleway", 'variant' => ':100'),
						array( 'name' => "Tinos", 'variant' => ':r,b,i,bi'),
						array( 'name' => "UnifrakturCook", 'variant' => ':bold'),
						array( 'name' => "UnifrakturMaguntia", 'variant' => ''),
						array( 'name' => "Mountains of Christmas", 'variant' => ''),
						array( 'name' => "Lato", 'variant' => ':400,700,400italic'),
						array( 'name' => "Orbitron", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Allan", 'variant' => ':bold'),
						array( 'name' => "Anonymous Pro", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Copse", 'variant' => ''),
						array( 'name' => "Kenia", 'variant' => ''),
						array( 'name' => "Ubuntu", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Vibur", 'variant' => ''),
						array( 'name' => "Sniglet", 'variant' => ':800'),
						array( 'name' => "Syncopate", 'variant' => ''),
						array( 'name' => "Cabin", 'variant' => ':400,400italic,700,700italic,'),
						array( 'name' => "Merriweather", 'variant' => ''),
						array( 'name' => "Maiden Orange", 'variant' => ''),
						array( 'name' => "Just Another Hand", 'variant' => ''),
						array( 'name' => "Kristi", 'variant' => ''),
						array( 'name' => "Corben", 'variant' => ':b'),
						array( 'name' => "Gruppo", 'variant' => ''),
						array( 'name' => "Buda", 'variant' => ':light'),
						array( 'name' => "Lekton", 'variant' => ''),
						array( 'name' => "Luckiest Guy", 'variant' => ''),
						array( 'name' => "Crushed", 'variant' => ''),
						array( 'name' => "Chewy", 'variant' => ''),
						array( 'name' => "Coming Soon", 'variant' => ''),
						array( 'name' => "Crafty Girls", 'variant' => ''),
						array( 'name' => "Fontdiner Swanky", 'variant' => ''),
						array( 'name' => "Permanent Marker", 'variant' => ''),
						array( 'name' => "Rock Salt", 'variant' => ''),
						array( 'name' => "Sunshiney", 'variant' => ''),
						array( 'name' => "Unkempt", 'variant' => ''),
						array( 'name' => "Calligraffitti", 'variant' => ''),
						array( 'name' => "Cherry Cream Soda", 'variant' => ''),
						array( 'name' => "Homemade Apple", 'variant' => ''),
						array( 'name' => "Irish Growler", 'variant' => ''),
						array( 'name' => "Kranky", 'variant' => ''),
						array( 'name' => "Schoolbell", 'variant' => ''),
						array( 'name' => "Slackey", 'variant' => ''),
						array( 'name' => "Walter Turncoat", 'variant' => ''),
						array( 'name' => "Radley", 'variant' => ''),
						array( 'name' => "Meddon", 'variant' => ''),
						array( 'name' => "Kreon", 'variant' => ':r,b'),
						array( 'name' => "Dancing Script", 'variant' => ''),
						array( 'name' => "Goudy Bookletter 1911", 'variant' => ''),
						array( 'name' => "PT Serif Caption", 'variant' => ':r,i'),
						array( 'name' => "PT Serif", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Astloch", 'variant' => ':b'),
						array( 'name' => "Bevan", 'variant' => ''),
						array( 'name' => "Anton", 'variant' => ''),
						array( 'name' => "Expletus Sans", 'variant' => ':b'),
						array( 'name' => "VT323", 'variant' => ''),
						array( 'name' => "Pacifico", 'variant' => ''),
						array( 'name' => "Candal", 'variant' => ''),
						array( 'name' => "Architects Daughter", 'variant' => ''),
						array( 'name' => "Indie Flower", 'variant' => ''),
						array( 'name' => "League Script", 'variant' => ''),
						array( 'name' => "Quattrocento", 'variant' => ''),
						array( 'name' => "Amaranth", 'variant' => ''),
						array( 'name' => "Irish Grover", 'variant' => ''),
						array( 'name' => "Oswald", 'variant' => ':400,300,700'),
						array( 'name' => "EB Garamond", 'variant' => ''),
						array( 'name' => "Nova Round", 'variant' => ''),
						array( 'name' => "Nova Slim", 'variant' => ''),
						array( 'name' => "Nova Script", 'variant' => ''),
						array( 'name' => "Nova Cut", 'variant' => ''),
						array( 'name' => "Nova Mono", 'variant' => ''),
						array( 'name' => "Nova Oval", 'variant' => ''),
						array( 'name' => "Nova Flat", 'variant' => ''),
						array( 'name' => "Terminal Dosis Light", 'variant' => ''),
						array( 'name' => "Michroma", 'variant' => ''),
						array( 'name' => "Miltonian", 'variant' => ''),
						array( 'name' => "Miltonian Tattoo", 'variant' => ''),
						array( 'name' => "Annie Use Your Telescope", 'variant' => ''),
						array( 'name' => "Dawning of a New Day", 'variant' => ''),
						array( 'name' => "Sue Ellen Francisco", 'variant' => ''),
						array( 'name' => "Waiting for the Sunrise", 'variant' => ''),
						array( 'name' => "Special Elite", 'variant' => ''),
						array( 'name' => "Quattrocento Sans", 'variant' => ''),
						array( 'name' => "Smythe", 'variant' => ''),
						array( 'name' => "The Girl Next Door", 'variant' => ''),
						array( 'name' => "Aclonica", 'variant' => ''),
						array( 'name' => "News Cycle", 'variant' => ''),
						array( 'name' => "Damion", 'variant' => ''),
						array( 'name' => "Wallpoet", 'variant' => ''),
						array( 'name' => "Over the Rainbow", 'variant' => ''),
						array( 'name' => "MedievalSharp", 'variant' => ''),
						array( 'name' => "Six Caps", 'variant' => ''),
						array( 'name' => "Swanky and Moo Moo", 'variant' => ''),
						array( 'name' => "Bigshot One", 'variant' => ''),
						array( 'name' => "Francois One", 'variant' => ''),
						array( 'name' => "Sigmar One", 'variant' => ''),
						array( 'name' => "Carter One", 'variant' => ''),
						array( 'name' => "Holtwood One SC", 'variant' => ''),
						array( 'name' => "Paytone One", 'variant' => ''),
						array( 'name' => "Monofett", 'variant' => ''),
						array( 'name' => "Rokkitt", 'variant' => ':400,700'),
						array( 'name' => "Megrim", 'variant' => ''),
						array( 'name' => "Judson", 'variant' => ':r,ri,b'),
						array( 'name' => "Didact Gothic", 'variant' => ''),
						array( 'name' => "Play", 'variant' => ':r,b'),
						array( 'name' => "Ultra", 'variant' => ''),
						array( 'name' => "Metrophobic", 'variant' => ''),
						array( 'name' => "Mako", 'variant' => ''),
						array( 'name' => "Shanti", 'variant' => ''),
						array( 'name' => "Caudex", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Jura", 'variant' => ''),
						array( 'name' => "Ruslan Display", 'variant' => ''),
						array( 'name' => "Brawler", 'variant' => ''),
						array( 'name' => "Nunito", 'variant' => ''),
						array( 'name' => "Wire One", 'variant' => ''),
						array( 'name' => "Podkova", 'variant' => ''),
						array( 'name' => "Muli", 'variant' => ''),
						array( 'name' => "Maven Pro", 'variant' => ':400,500,700'),
						array( 'name' => "Tenor Sans", 'variant' => ''),
						array( 'name' => "Limelight", 'variant' => ''),
						array( 'name' => "Playfair Display", 'variant' => ''),
						array( 'name' => "Artifika", 'variant' => ''),
						array( 'name' => "Lora", 'variant' => ''),
						array( 'name' => "Kameron", 'variant' => ':r,b'),
						array( 'name' => "Cedarville Cursive", 'variant' => ''),
						array( 'name' => "Zeyada", 'variant' => ''),
						array( 'name' => "La Belle Aurore", 'variant' => ''),
						array( 'name' => "Shadows Into Light", 'variant' => ''),
						array( 'name' => "Lobster Two", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Nixie One", 'variant' => ''),
						array( 'name' => "Redressed", 'variant' => ''),
						array( 'name' => "Bangers", 'variant' => ''),
						array( 'name' => "Open Sans Condensed", 'variant' => ':300italic,400italic,700italic,400,300,700'),
						array( 'name' => "Open Sans", 'variant' => ':r,i,b,bi'),
						array( 'name' => "Varela", 'variant' => ''),
						array( 'name' => "Goblin One", 'variant' => ''),
						array( 'name' => "Asset", 'variant' => ''),
						array( 'name' => "Gravitas One", 'variant' => ''),
						array( 'name' => "Hammersmith One", 'variant' => ''),
						array( 'name' => "Stardos Stencil", 'variant' => ''),
						array( 'name' => "Love Ya Like A Sister", 'variant' => ''),
						array( 'name' => "Loved by the King", 'variant' => ''),
						array( 'name' => "Bowlby One SC", 'variant' => ''),
						array( 'name' => "Forum", 'variant' => ''),
						array( 'name' => "Patrick Hand", 'variant' => ''),
						array( 'name' => "Varela Round", 'variant' => ''),
						array( 'name' => "Yeseva One", 'variant' => ''),
						array( 'name' => "Give You Glory", 'variant' => ''),
						array( 'name' => "Modern Antiqua", 'variant' => ''),
						array( 'name' => "Bowlby One", 'variant' => ''),
						array( 'name' => "Tienne", 'variant' => ''),
						array( 'name' => "Istok Web", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Yellowtail", 'variant' => ''),
						array( 'name' => "Pompiere", 'variant' => ''),
						array( 'name' => "Unna", 'variant' => ''),
						array( 'name' => "Rosario", 'variant' => ''),
						array( 'name' => "Leckerli One", 'variant' => ''),
						array( 'name' => "Snippet", 'variant' => ''),
						array( 'name' => "Ovo", 'variant' => ''),
						array( 'name' => "IM Fell English", 'variant' => ':r,i'),
						array( 'name' => "IM Fell English SC", 'variant' => ''),
						array( 'name' => "Gloria Hallelujah", 'variant' => ''),
						array( 'name' => "Kelly Slab", 'variant' => ''),
						array( 'name' => "Black Ops One", 'variant' => ''),
						array( 'name' => "Carme", 'variant' => ''),
						array( 'name' => "Aubrey", 'variant' => ''),
						array( 'name' => "Federo", 'variant' => ''),
						array( 'name' => "Delius", 'variant' => ''),
						array( 'name' => "Rochester", 'variant' => ''),
						array( 'name' => "Rationale", 'variant' => ''),
						array( 'name' => "Abel", 'variant' => ''),
						array( 'name' => "Marvel", 'variant' => ':r,b,i,bi'),
						array( 'name' => "Actor", 'variant' => ''),
						array( 'name' => "Delius Swash Caps", 'variant' => ''),
						array( 'name' => "Smokum", 'variant' => ''),
						array( 'name' => "Tulpen One", 'variant' => ''),
						array( 'name' => "Coustard", 'variant' => ':r,b'),
						array( 'name' => "Andika", 'variant' => ''),
						array( 'name' => "Alice", 'variant' => ''),
						array( 'name' => "Questrial", 'variant' => ''),
						array( 'name' => "Comfortaa", 'variant' => ':r,b'),
						array( 'name' => "Geostar", 'variant' => ''),
						array( 'name' => "Geostar Fill", 'variant' => ''),
						array( 'name' => "Volkhov", 'variant' => ''),
						array( 'name' => "Voltaire", 'variant' => ''),
						array( 'name' => "Montez", 'variant' => ''),
						array( 'name' => "Short Stack", 'variant' => ''),
						array( 'name' => "Vidaloka", 'variant' => ''),
						array( 'name' => "Aldrich", 'variant' => ''),
						array( 'name' => "Numans", 'variant' => ''),
						array( 'name' => "Days One", 'variant' => ''),
						array( 'name' => "Gentium Book Basic", 'variant' => ''),
						array( 'name' => "Monoton", 'variant' => ''),
						array( 'name' => "Alike", 'variant' => ''),
						array( 'name' => "Delius Unicase", 'variant' => ''),
						array( 'name' => "Abril Fatface", 'variant' => ''),
						array( 'name' => "Dorsa", 'variant' => ''),
						array( 'name' => "Antic", 'variant' => ''),
						array( 'name' => "Passero One", 'variant' => ''),
						array( 'name' => "Fanwood Text", 'variant' => ''),
						array( 'name' => "Prociono", 'variant' => ''),
						array( 'name' => "Merienda One", 'variant' => ''),
						array( 'name' => "Changa One", 'variant' => ''),
						array( 'name' => "Julee", 'variant' => ''),
						array( 'name' => "Prata", 'variant' => ''),
						array( 'name' => "Adamina", 'variant' => ''),
						array( 'name' => "Sorts Mill Goudy", 'variant' => ''),
						array( 'name' => "Terminal Dosis", 'variant' => ''),
						array( 'name' => "Sansita One", 'variant' => ''),
						array( 'name' => "Chivo", 'variant' => ''),
						array( 'name' => "Spinnaker", 'variant' => ''),
						array( 'name' => "Poller One", 'variant' => ''),
						array( 'name' => "Alike Angular", 'variant' => ''),
						array( 'name' => "Gochi Hand", 'variant' => ''),
						array( 'name' => "Poly", 'variant' => ''),
						array( 'name' => "Andada", 'variant' => ''),
						array( 'name' => "Federant", 'variant' => ''),
						array( 'name' => "Ubuntu Condensed", 'variant' => ''),
						array( 'name' => "Ubuntu Mono", 'variant' => ''),
						array( 'name' => "Sancreek", 'variant' => ''),
						array( 'name' => "Coda", 'variant' => ''),
						array( 'name' => "Rancho", 'variant' => ''),
						array( 'name' => "Satisfy", 'variant' => ''),
						array( 'name' => "Pinyon Script", 'variant' => ''),
						array( 'name' => "Vast Shadow", 'variant' => ''),
						array( 'name' => "Marck Script", 'variant' => ''),
						array( 'name' => "Salsa", 'variant' => ''),
						array( 'name' => "Amatic SC", 'variant' => ''),
						array( 'name' => "Quicksand", 'variant' => ''),
						array( 'name' => "Linden Hill", 'variant' => ''),
						array( 'name' => "Corben", 'variant' => ''),
						array( 'name' => "Creepster Caps", 'variant' => ''),
						array( 'name' => "Butcherman Caps", 'variant' => ''),
						array( 'name' => "Eater Caps", 'variant' => ''),
						array( 'name' => "Nosifer Caps", 'variant' => ''),
						array( 'name' => "Atomic Age", 'variant' => ''),
						array( 'name' => "Contrail One", 'variant' => ''),
						array( 'name' => "Jockey One", 'variant' => ''),
						array( 'name' => "Cabin Sketch", 'variant' => ':r,b'),
						array( 'name' => "Cabin Condensed", 'variant' => ':r,b'),
						array( 'name' => "Fjord One", 'variant' => ''),
						array( 'name' => "Rametto One", 'variant' => ''),
						array( 'name' => "Mate", 'variant' => ':r,i'),
						array( 'name' => "Mate SC", 'variant' => ''),
						array( 'name' => "Arapey", 'variant' => ':r,i'),
						array( 'name' => "Supermercado One", 'variant' => ''),
						array( 'name' => "Petrona", 'variant' => ''),
						array( 'name' => "Lancelot", 'variant' => ''),
						array( 'name' => "Convergence", 'variant' => ''),
						array( 'name' => "Cutive", 'variant' => ''),
						array( 'name' => "Karla", 'variant' => ':400,400italic,700,700italic'),
						array( 'name' => "Bitter", 'variant' => ':r,i,b'),
						array( 'name' => "Asap", 'variant' => ':400,700,400italic,700italic'),
						array( 'name' => "Bree Serif", 'variant' => '')
						
);


/*-----------------------------------------------------------------------------------*/
/* Google Webfonts Stylesheet Generator */
/*-----------------------------------------------------------------------------------*/
/*
INSTRUCTIONS: Needs to be loaded for the Google Fonts options to work for font options. Add this to
the specific themes includes/theme-actions.php or functions.php:

add_action( 'wp_head', 'woo_google_webfonts' );
*/

if ( ! function_exists( 'woo_google_webfonts' ) ) {
	function woo_google_webfonts() {
		global $google_fonts;
		$fonts = '';
		$output = '';

		// Setup Woo Options array
		global $woo_options;

		// Go through the options
		if ( !empty($woo_options) ) {
			foreach ( $woo_options as $option ) {
				// Check if option has "face" in array
				if ( is_array($option) && isset($option['face']) ) {
					// Go through the google font array
					foreach ($google_fonts as $font) {
						// Check if the google font name exists in the current "face" option
						if ( $option['face'] == $font['name'] AND !strstr($fonts, $font['name']) ) {
							// Add google font to output
							$fonts .= $font['name'].$font['variant']."|";
						} // End If Statement
					} // End Foreach Loop
				} // End If Statement
			} // End Foreach Loop

			// Output google font css in header
			if ( $fonts ) {
				$fonts = str_replace( " ","+",$fonts);
				$output .= "\n<!-- Google Webfonts -->\n";
				$output .= '<link href="http'. ( is_ssl() ? 's' : '' ) .'://fonts.googleapis.com/css?family=' . $fonts .'" rel="stylesheet" type="text/css" />'."\n";
				$output = str_replace( '|"','"',$output);

				echo $output;
			}
		}
	} // End woo_google_webfonts()
}


/*-----------------------------------------------------------------------------------*/
/* Enable Home link in WP Menus
/*-----------------------------------------------------------------------------------*/
if ( !function_exists( 'woo_home_page_menu_args' ) ) {
	function woo_home_page_menu_args( $args ) {
		$args['show_home'] = true;
		return $args;
	} // End woo_home_page_menu_args()
	add_filter( 'wp_page_menu_args', 'woo_home_page_menu_args' );
}

/*---------------------------------------------------------------------------------*/
/* Detects the Charset of String and Converts it to UTF-8 */
/*---------------------------------------------------------------------------------*/
if ( !function_exists( 'woo_encoding_convert') ) {
	function woo_encoding_convert($str_to_convert) {
		if ( function_exists( 'mb_detect_encoding') ) {
			$str_lang_encoding = mb_detect_encoding($str_to_convert);
			//if no encoding detected, assume UTF-8
			if (!$str_lang_encoding) {
				//UTF-8 assumed
				$str_lang_converted_utf = $str_to_convert;
			} else {
				//Convert to UTF-8
				$str_lang_converted_utf = mb_convert_encoding($str_to_convert, 'UTF-8', $str_lang_encoding);
			}
		} else {
			$str_lang_converted_utf = $str_to_convert;
		}

		return $str_lang_converted_utf;
	}
}

/*---------------------------------------------------------------------------------*/
/* WP Login logo */
/*---------------------------------------------------------------------------------*/
if ( !function_exists( 'woo_custom_login_logo' ) ) {
	function woo_custom_login_logo() {
		$logo = get_option( 'framework_woo_custom_login_logo' );
	    $dimensions = @getimagesize( $logo );
	    $background_size = 'background-size: auto;';
	    if ( 0 >= $dimensions[1] ) {
	    	$dimensions[1] = '67';
	    	$background_size = '';
	    }

		echo '<style type="text/css">body #login h1 a { background-image:url( ' . esc_url( $logo ) . ' ); height: ' . intval( $dimensions[1] ) . 'px; width: auto; ' . $background_size . ' }</style>';
	} // End woo_custom_login_logo()
	if ( '' != get_option( 'framework_woo_custom_login_logo') ) {
		add_action( 'login_head', 'woo_custom_login_logo' );
	}
}

/*---------------------------------------------------------------------------------*/
/* WP Login logo URL */
/*---------------------------------------------------------------------------------*/
if ( ! function_exists( 'woo_custom_login_logo_url' ) ) {
	function woo_custom_login_logo_url( $text ) {
		return get_option( 'framework_woo_custom_login_logo_url' ); // Escaping via esc_url() is done in wp-login.php.
	} // End woo_custom_login_logo_url()
	
	if ( '' != get_option( 'framework_woo_custom_login_logo_url' ) ) {
		add_filter( 'login_headerurl', 'woo_custom_login_logo_url', 10 );
	}
}

/*---------------------------------------------------------------------------------*/
/* WP Login logo title */
/*---------------------------------------------------------------------------------*/
if ( ! function_exists( 'woo_custom_login_logo_title' ) ) {
	function woo_custom_login_logo_title( $text ) {
		return get_option( 'framework_woo_custom_login_logo_title' ); // Escaping via esc_attr() is done in wp-login.php.
	} // End woo_custom_login_logo_title()
	
	if ( '' != get_option( 'framework_woo_custom_login_logo_title' ) ) {
		add_filter( 'login_headertitle', 'woo_custom_login_logo_title', 10 );
	}
}

/*-----------------------------------------------------------------------------------*/
/* woo_pagination() - Custom loop pagination function  */
/*-----------------------------------------------------------------------------------*/
/*
/* Additional documentation: http://codex.wordpress.org/Function_Reference/paginate_links
/*
/* Params:
/*
/* Arguments Array:
/*
/* 'base' (optional) 				- The query argument on which to determine the pagination (for advanced users)
/* 'format' (optional) 				- The format in which the query argument is formatted in it's raw format (for advanced users)
/* 'total' (optional) 				- The total amount of pages
/* 'current' (optional) 			- The current page number
/* 'prev_next' (optional) 			- Whether to include the previous and next links in the list or not.
/* 'prev_text' (optional) 			- The previous page text. Works only if 'prev_next' argument is set to true.
/* 'next_text' (optional) 			- The next page text. Works only if 'prev_next' argument is set to true.
/* 'show_all' (optional) 			- If set to True, then it will show all of the pages instead of a short list of the pages near the current page. By default, the 'show_all' is set to false and controlled by the 'end_size' and 'mid_size' arguments.
/* 'end_size' (optional) 			- How many numbers on either the start and the end list edges.
/* 'mid_size' (optional) 			- How many numbers to either side of current page, but not including current page.
/* 'add_fragment' (optional) 		- An array of query args to add using add_query_arg().
/* 'type' (optional) 				- Controls format of the returned value. Possible values are:
									  'plain' - A string with the links separated by a newline character.
									  'array' - An array of the paginated link list to offer full control of display.
									  'list' - Unordered HTML list.
/* 'before' (optional) 				- The HTML to display before the paginated links.
/* 'after' (optional) 				- The HTML to display after the paginated links.
/* 'echo' (optional) 				- Whether or not to display the paginated links (alternative is to "return").
/* 'use_search_permastruct' (optiona;) - Whether or not to use the "pretty" URL permastruct for search URLs.
/*
/* Query Parameter (optional) 		- Specify a custom query which you'd like to paginate.
/*
/*-----------------------------------------------------------------------------------*/
/**
 * woo_pagination() is used for paginating the various archive pages created by WordPress. This is not
 * to be used on single.php or other single view pages.
 *
 * @since 3.7.0
 * @uses paginate_links() Creates a string of paginated links based on the arguments given.
 * @param array $args Arguments to customize how the page links are output.
 * @param object $query An optional custom query to paginate.
 */

if ( ! function_exists( 'woo_pagination' ) ) {
	function woo_pagination( $args = array(), $query = '' ) {
		global $wp_rewrite, $wp_query;

		do_action( 'woo_pagination_start' );

		if ( $query ) {

			$wp_query = $query;

		} // End IF Statement

		/* If there's not more than one page, return nothing. */
		if ( 1 >= $wp_query->max_num_pages )
			return;

		/* Get the current page. */
		$current = ( get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1 );

		/* Get the max number of pages. */
		$max_num_pages = intval( $wp_query->max_num_pages );

		/* Set up some default arguments for the paginate_links() function. */
		$defaults = array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'total' => $max_num_pages,
			'current' => $current,
			'prev_next' => true,
			'prev_text' => __( '&larr; Previous', 'woothemes' ), // Translate in WordPress. This is the default.
			'next_text' => __( 'Next &rarr;', 'woothemes' ), // Translate in WordPress. This is the default.
			'show_all' => false,
			'end_size' => 1,
			'mid_size' => 1,
			'add_fragment' => '',
			'type' => 'plain',
			'before' => '<div class="pagination woo-pagination">', // Begin woo_pagination() arguments.
			'after' => '</div>',
			'echo' => true, 
			'use_search_permastruct' => true
		);

		/* Allow themes/plugins to filter the default arguments. */
		$defaults = apply_filters( 'woo_pagination_args_defaults', $defaults );

		/* Add the $base argument to the array if the user is using permalinks. */
		if( $wp_rewrite->using_permalinks() && ! is_search() )
			$defaults['base'] = user_trailingslashit( trailingslashit( get_pagenum_link() ) . 'page/%#%' );

		/* Force search links to use raw permastruct for more accurate multi-word searching. */
		if ( is_search() )
			$defaults['use_search_permastruct'] = false;

		/* If we're on a search results page, we need to change this up a bit. */
		if ( is_search() ) {
		/* If we're in BuddyPress, or the user has selected to do so, use the default "unpretty" URL structure. */
			if ( class_exists( 'BP_Core_User' ) || $defaults['use_search_permastruct'] == false ) {
				$search_query = get_query_var( 's' );
				$paged = get_query_var( 'paged' );
				$base = add_query_arg( 's', urlencode( $search_query ) );
				$base = add_query_arg( 'paged', '%#%' );
				$defaults['base'] = $base;
			} else {
				$search_permastruct = $wp_rewrite->get_search_permastruct();
				if ( ! empty( $search_permastruct ) ) {
					$base = get_search_link();
					$base = add_query_arg( 'paged', '%#%', $base );
					$defaults['base'] = $base;
				}
			}
		}

		/* Merge the arguments input with the defaults. */
		$args = wp_parse_args( $args, $defaults );

		/* Allow developers to overwrite the arguments with a filter. */
		$args = apply_filters( 'woo_pagination_args', $args );

		/* Don't allow the user to set this to an array. */
		if ( 'array' == $args['type'] )
			$args['type'] = 'plain';

		/* Make sure raw querystrings are displayed at the end of the URL, if using pretty permalinks. */
		$pattern = '/\?(.*?)\//i';

		preg_match( $pattern, $args['base'], $raw_querystring );

		if( $wp_rewrite->using_permalinks() && $raw_querystring )
			$raw_querystring[0] = str_replace( '', '', $raw_querystring[0] );
			@$args['base'] = str_replace( $raw_querystring[0], '', $args['base'] );
			@$args['base'] .= substr( $raw_querystring[0], 0, -1 );
		
		/* Get the paginated links. */
		$page_links = paginate_links( $args );

		/* Remove 'page/1' from the entire output since it's not needed. */
		$page_links = str_replace( array( '&#038;paged=1\'', '/page/1\'' ), '\'', $page_links );

		/* Wrap the paginated links with the $before and $after elements. */
		$page_links = $args['before'] . $page_links . $args['after'];

		/* Allow devs to completely overwrite the output. */
		$page_links = apply_filters( 'woo_pagination', $page_links );

		do_action( 'woo_pagination_end' );

		/* Return the paginated links for use in themes. */
		if ( $args['echo'] )
			echo $page_links;
		else
			return $page_links;
	} // End woo_pagination()
} // End IF Statement

/*-----------------------------------------------------------------------------------*/
/* woo_breadcrumbs() - Custom breadcrumb generator function  */
/*
/* Params:
/*
/* Arguments Array:
/*
/* 'separator' 			- The character to display between the breadcrumbs.
/* 'before' 			- HTML to display before the breadcrumbs.
/* 'after' 				- HTML to display after the breadcrumbs.
/* 'front_page' 		- Include the front page at the beginning of the breadcrumbs.
/* 'show_home' 			- If $show_home is set and we're not on the front page of the site, link to the home page.
/* 'echo' 				- Specify whether or not to echo the breadcrumbs. Alternative is "return".
/* 'show_posts_page'	- If a static front page is set and there is a posts page, toggle whether or not to display that page's tree.
/*
/*-----------------------------------------------------------------------------------*/
/**
 * The code below is inspired by Justin Tadlock's Hybrid Core.
 *
 * woo_breadcrumbs() shows a breadcrumb for all types of pages.  Themes and plugins can filter $args or input directly.
 * Allow filtering of only the $args using get_the_breadcrumb_args.
 *
 * @since 3.7.0
 * @param array $args Mixed arguments for the menu.
 * @return string Output of the breadcrumb menu.
 */
function woo_breadcrumbs( $args = array() ) {
	global $wp_query, $wp_rewrite;

	/* Create an empty variable for the breadcrumb. */
	$breadcrumb = '';

	/* Create an empty array for the trail. */
	$trail = array();
	$path = '';

	/* Set up the default arguments for the breadcrumb. */
	$defaults = array(
		'separator' => '&gt;',
		'before' => '<span class="breadcrumb-title">' . __( 'You are here:', 'woothemes' ) . '</span>',
		'after' => false,
		'front_page' => true,
		'show_home' => __( 'Home', 'woothemes' ),
		'echo' => true, 
		'show_posts_page' => true
	);

	/* Allow singular post views to have a taxonomy's terms prefixing the trail. */
	if ( is_singular() )
		$defaults["singular_{$wp_query->post->post_type}_taxonomy"] = false;

	/* Apply filters to the arguments. */
	$args = apply_filters( 'woo_breadcrumbs_args', $args );

	/* Parse the arguments and extract them for easy variable naming. */
	extract( wp_parse_args( $args, $defaults ) );

	/* If $show_home is set and we're not on the front page of the site, link to the home page. */
	if ( !is_front_page() && $show_home )
		$trail[] = '<a href="' . esc_url( home_url() ) . '" title="' . esc_attr( get_bloginfo( 'name' ) ) . '" rel="home" class="trail-begin">' . esc_html( $show_home ) . '</a>';

	/* If viewing the front page of the site. */
	if ( is_front_page() ) {
		if ( !$front_page )
			$trail = false;
		elseif ( $show_home )
			$trail['trail_end'] = "{$show_home}";
	}

	/* If viewing the "home"/posts page. */
	elseif ( is_home() ) {
		$home_page = get_page( $wp_query->get_queried_object_id() );
		$trail = array_merge( $trail, woo_breadcrumbs_get_parents( $home_page->post_parent, '' ) );
		$trail['trail_end'] = get_the_title( $home_page->ID );
	}

	/* If viewing a singular post (page, attachment, etc.). */
	elseif ( is_singular() ) {

		/* Get singular post variables needed. */
		$post = $wp_query->get_queried_object();
		$post_id = absint( $wp_query->get_queried_object_id() );
		$post_type = $post->post_type;
		$parent = $post->post_parent;

		/* If a custom post type, check if there are any pages in its hierarchy based on the slug. */
		if ( 'page' !== $post_type && 'post' !== $post_type ) {

			$post_type_object = get_post_type_object( $post_type );

			/* If $front has been set, add it to the $path. */
			if ( 'post' == $post_type || 'attachment' == $post_type || ( $post_type_object->rewrite['with_front'] && $wp_rewrite->front ) )
				$path .= trailingslashit( $wp_rewrite->front );

			/* If there's a slug, add it to the $path. */
			if ( !empty( $post_type_object->rewrite['slug'] ) )
				$path .= $post_type_object->rewrite['slug'];

			/* If there's a path, check for parents. */
			if ( !empty( $path ) && '/' != $path )
				$trail = array_merge( $trail, woo_breadcrumbs_get_parents( '', $path ) );

			/* If there's an archive page, add it to the trail. */
			if ( !empty( $post_type_object->has_archive ) && function_exists( 'get_post_type_archive_link' ) )
				$trail[] = '<a href="' . get_post_type_archive_link( $post_type ) . '" title="' . esc_attr( $post_type_object->labels->name ) . '">' . $post_type_object->labels->name . '</a>';
		}

		/* If the post type path returns nothing and there is a parent, get its parents. */
		if ( empty( $path ) && 0 !== $parent || 'attachment' == $post_type )
			$trail = array_merge( $trail, woo_breadcrumbs_get_parents( $parent, '' ) );

		/* Toggle the display of the posts page on single blog posts. */		
		if ( 'post' == $post_type && $show_posts_page == true && 'page' == get_option( 'show_on_front' ) ) {
			$posts_page = get_option( 'page_for_posts' );
			if ( $posts_page != '' && is_numeric( $posts_page ) ) {
				$trail = array_merge( $trail, woo_breadcrumbs_get_parents( $posts_page, '' ) );
			}
		}

		/* Display terms for specific post type taxonomy if requested. */
		if ( isset( $args["singular_{$post_type}_taxonomy"] ) && $terms = get_the_term_list( $post_id, $args["singular_{$post_type}_taxonomy"], '', ', ', '' ) )
			$trail[] = $terms;

		/* End with the post title. */
		$post_title = get_the_title( $post_id ); // Force the post_id to make sure we get the correct page title.
		if ( !empty( $post_title ) )
			$trail['trail_end'] = $post_title;
	}

	/* If we're viewing any type of archive. */
	elseif ( is_archive() ) {

		/* If viewing a taxonomy term archive. */
		if ( is_tax() || is_category() || is_tag() ) {

			/* Get some taxonomy and term variables. */
			$term = $wp_query->get_queried_object();
			$taxonomy = get_taxonomy( $term->taxonomy );

			/* Get the path to the term archive. Use this to determine if a page is present with it. */
			if ( is_category() )
				$path = get_option( 'category_base' );
			elseif ( is_tag() )
				$path = get_option( 'tag_base' );
			else {
				if ( $taxonomy->rewrite['with_front'] && $wp_rewrite->front )
					$path = trailingslashit( $wp_rewrite->front );
				$path .= $taxonomy->rewrite['slug'];
			}

			/* Get parent pages by path if they exist. */
			if ( $path )
				$trail = array_merge( $trail, woo_breadcrumbs_get_parents( '', $path ) );

			/* If the taxonomy is hierarchical, list its parent terms. */
			if ( is_taxonomy_hierarchical( $term->taxonomy ) && $term->parent )
				$trail = array_merge( $trail, woo_breadcrumbs_get_term_parents( $term->parent, $term->taxonomy ) );

			/* Add the term name to the trail end. */
			$trail['trail_end'] = $term->name;
		}

		/* If viewing a post type archive. */
		elseif ( function_exists( 'is_post_type_archive' ) && is_post_type_archive() ) {

			/* Get the post type object. */
			$post_type_object = get_post_type_object( get_query_var( 'post_type' ) );

			/* If $front has been set, add it to the $path. */
			if ( $post_type_object->rewrite['with_front'] && $wp_rewrite->front )
				$path .= trailingslashit( $wp_rewrite->front );

			/* If there's a slug, add it to the $path. */
			if ( !empty( $post_type_object->rewrite['archive'] ) )
				$path .= $post_type_object->rewrite['archive'];

			/* If there's a path, check for parents. */
			if ( !empty( $path ) && '/' != $path )
				$trail = array_merge( $trail, woo_breadcrumbs_get_parents( '', $path ) );

			/* Add the post type [plural] name to the trail end. */
			$trail['trail_end'] = $post_type_object->labels->name;
		}

		/* If viewing an author archive. */
		elseif ( is_author() ) {

			/* If $front has been set, add it to $path. */
			if ( !empty( $wp_rewrite->front ) )
				$path .= trailingslashit( $wp_rewrite->front );

			/* If an $author_base exists, add it to $path. */
			if ( !empty( $wp_rewrite->author_base ) )
				$path .= $wp_rewrite->author_base;

			/* If $path exists, check for parent pages. */
			if ( !empty( $path ) )
				$trail = array_merge( $trail, woo_breadcrumbs_get_parents( '', $path ) );

			/* Add the author's display name to the trail end. */
			$trail['trail_end'] = get_the_author_meta( 'display_name', get_query_var( 'author' ) );
		}

		/* If viewing a time-based archive. */
		elseif ( is_time() ) {

			if ( get_query_var( 'minute' ) && get_query_var( 'hour' ) )
				$trail['trail_end'] = get_the_time( __( 'g:i a', 'woothemes' ) );

			elseif ( get_query_var( 'minute' ) )
				$trail['trail_end'] = sprintf( __( 'Minute %1$s', 'woothemes' ), get_the_time( __( 'i', 'woothemes' ) ) );

			elseif ( get_query_var( 'hour' ) )
				$trail['trail_end'] = get_the_time( __( 'g a', 'woothemes' ) );
		}

		/* If viewing a date-based archive. */
		elseif ( is_date() ) {

			/* If $front has been set, check for parent pages. */
			if ( $wp_rewrite->front )
				$trail = array_merge( $trail, woo_breadcrumbs_get_parents( '', $wp_rewrite->front ) );

			if ( is_day() ) {
				$trail[] = '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( esc_attr__( 'Y', 'woothemes' ) ) . '">' . get_the_time( __( 'Y', 'woothemes' ) ) . '</a>';
				$trail[] = '<a href="' . get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) . '" title="' . get_the_time( esc_attr__( 'F', 'woothemes' ) ) . '">' . get_the_time( __( 'F', 'woothemes' ) ) . '</a>';
				$trail['trail_end'] = get_the_time( __( 'j', 'woothemes' ) );
			}

			elseif ( get_query_var( 'w' ) ) {
				$trail[] = '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( esc_attr__( 'Y', 'woothemes' ) ) . '">' . get_the_time( __( 'Y', 'woothemes' ) ) . '</a>';
				$trail['trail_end'] = sprintf( __( 'Week %1$s', 'woothemes' ), get_the_time( esc_attr__( 'W', 'woothemes' ) ) );
			}

			elseif ( is_month() ) {
				$trail[] = '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( esc_attr__( 'Y', 'woothemes' ) ) . '">' . get_the_time( __( 'Y', 'woothemes' ) ) . '</a>';
				$trail['trail_end'] = get_the_time( __( 'F', 'woothemes' ) );
			}

			elseif ( is_year() ) {
				$trail['trail_end'] = get_the_time( __( 'Y', 'woothemes' ) );
			}
		}
	}

	/* If viewing search results. */
	elseif ( is_search() )
		$trail['trail_end'] = sprintf( __( 'Search results for &quot;%1$s&quot;', 'woothemes' ), esc_attr( get_search_query() ) );

	/* If viewing a 404 error page. */
	elseif ( is_404() )
		$trail['trail_end'] = __( '404 Not Found', 'woothemes' );

	/* Allow child themes/plugins to filter the trail array. */
	$trail = apply_filters( 'woo_breadcrumbs_trail', $trail, $args );

	/* Connect the breadcrumb trail if there are items in the trail. */
	if ( is_array( $trail ) ) {

		/* Open the breadcrumb trail containers. */
		$breadcrumb = '<div class="breadcrumb breadcrumbs woo-breadcrumbs"><div class="breadcrumb-trail">';

		/* If $before was set, wrap it in a container. */
		if ( !empty( $before ) )
			$breadcrumb .= '<span class="trail-before">' . wp_kses_post( $before ) . '</span> ';

		/* Wrap the $trail['trail_end'] value in a container. */
		if ( !empty( $trail['trail_end'] ) )
			$trail['trail_end'] = '<span class="trail-end">' . wp_kses_post( $trail['trail_end'] ) . '</span>';

		/* Format the separator. */
		if ( !empty( $separator ) )
			$separator = '<span class="sep">' . wp_kses_post( $separator ) . '</span>';

		/* Join the individual trail items into a single string. */
		$breadcrumb .= join( " {$separator} ", $trail );

		/* If $after was set, wrap it in a container. */
		if ( !empty( $after ) )
			$breadcrumb .= ' <span class="trail-after">' . wp_kses_post( $after ) . '</span>';

		/* Close the breadcrumb trail containers. */
		$breadcrumb .= '</div></div>';
	}

	/* Allow developers to filter the breadcrumb trail HTML. */
	$breadcrumb = apply_filters( 'woo_breadcrumbs', $breadcrumb );

	/* Output the breadcrumb. */
	if ( $echo )
		echo $breadcrumb;
	else
		return $breadcrumb;
} // End woo_breadcrumbs()

/*-----------------------------------------------------------------------------------*/
/* woo_breadcrumbs_get_parents() - Retrieve the parents of the current page/post */
/*-----------------------------------------------------------------------------------*/
/**
 * Gets parent pages of any post type or taxonomy by the ID or Path.  The goal of this function is to create
 * a clear path back to home given what would normally be a "ghost" directory.  If any page matches the given
 * path, it'll be added.  But, it's also just a way to check for a hierarchy with hierarchical post types.
 *
 * @since 3.7.0
 * @param int $post_id ID of the post whose parents we want.
 * @param string $path Path of a potential parent page.
 * @return array $trail Array of parent page links.
 */
function woo_breadcrumbs_get_parents( $post_id = '', $path = '' ) {
	/* Set up an empty trail array. */
	$trail = array();

	/* If neither a post ID nor path set, return an empty array. */
	if ( empty( $post_id ) && empty( $path ) )
		return $trail;

	/* If the post ID is empty, use the path to get the ID. */
	if ( empty( $post_id ) ) {

		/* Get parent post by the path. */
		$parent_page = get_page_by_path( $path );

		/* ********************************************************************
		Modification: The above line won't get the parent page if
		the post type slug or parent page path is not the full path as required
		by get_page_by_path. By using get_page_with_title, the full parent
		trail can be obtained. This may still be buggy for page names that use
		characters or long concatenated names.
		Author: Byron Rode
		Date: 06 June 2011
		******************************************************************* */

		if( empty( $parent_page ) )
		        // search on page name (single word)
			$parent_page = get_page_by_title ( $path );

		if( empty( $parent_page ) )
			// search on page title (multiple words)
			$parent_page = get_page_by_title ( str_replace( array('-', '_'), ' ', $path ) );

		/* End Modification */

		/* If a parent post is found, set the $post_id variable to it. */
		if ( !empty( $parent_page ) )
			$post_id = $parent_page->ID;
	}

	/* If a post ID and path is set, search for a post by the given path. */
	if ( $post_id == 0 && !empty( $path ) ) {

		/* Separate post names into separate paths by '/'. */
		$path = trim( $path, '/' );
		preg_match_all( "/\/.*?\z/", $path, $matches );

		/* If matches are found for the path. */
		if ( isset( $matches ) ) {

			/* Reverse the array of matches to search for posts in the proper order. */
			$matches = array_reverse( $matches );

			/* Loop through each of the path matches. */
			foreach ( $matches as $match ) {

				/* If a match is found. */
				if ( isset( $match[0] ) ) {

					/* Get the parent post by the given path. */
					$path = str_replace( $match[0], '', $path );
					$parent_page = get_page_by_path( trim( $path, '/' ) );

					/* If a parent post is found, set the $post_id and break out of the loop. */
					if ( !empty( $parent_page ) && $parent_page->ID > 0 ) {
						$post_id = $parent_page->ID;
						break;
					}
				}
			}
		}
	}

	/* While there's a post ID, add the post link to the $parents array. */
	while ( $post_id ) {
		/* Get the post by ID. */
		$page = get_page( $post_id );

		/* Add the formatted post link to the array of parents. */
		$parents[]  = '<a href="' . get_permalink( $post_id ) . '" title="' . esc_attr( get_the_title( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a>';

		/* Set the parent post's parent to the post ID. */
		$post_id = $page->post_parent;
	}

	/* If we have parent posts, reverse the array to put them in the proper order for the trail. */
	if ( isset( $parents ) )
		$trail = array_reverse( $parents );

	/* Return the trail of parent posts. */
	return $trail;
} // End woo_breadcrumbs_get_parents()

/*-----------------------------------------------------------------------------------*/
/* woo_breadcrumbs_get_term_parents() - Retrieve the parents of the current term */
/*-----------------------------------------------------------------------------------*/
/**
 * Searches for term parents of hierarchical taxonomies.  This function is similar to the WordPress
 * function get_category_parents() but handles any type of taxonomy.
 *
 * @since 3.7.0
 * @param int $parent_id The ID of the first parent.
 * @param object|string $taxonomy The taxonomy of the term whose parents we want.
 * @return array $trail Array of links to parent terms.
 */
function woo_breadcrumbs_get_term_parents( $parent_id = '', $taxonomy = '' ) {
	/* Set up some default arrays. */
	$trail = array();
	$parents = array();

	/* If no term parent ID or taxonomy is given, return an empty array. */
	if ( empty( $parent_id ) || empty( $taxonomy ) )
		return $trail;

	/* While there is a parent ID, add the parent term link to the $parents array. */
	while ( $parent_id ) {

		/* Get the parent term. */
		$parent = get_term( $parent_id, $taxonomy );

		/* Add the formatted term link to the array of parent terms. */
		$parents[] = '<a href="' . get_term_link( $parent, $taxonomy ) . '" title="' . esc_attr( $parent->name ) . '">' . $parent->name . '</a>';

		/* Set the parent term's parent as the parent ID. */
		$parent_id = $parent->parent;
	}

	/* If we have parent terms, reverse the array to put them in the proper order for the trail. */
	if ( !empty( $parents ) )
		$trail = array_reverse( $parents );

	/* Return the trail of parent terms. */
	return $trail;
} // End woo_breadcrumbs_get_term_parents()

/*-----------------------------------------------------------------------------------*/
/* WordPress Admin Bar-related */
/*-----------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------*/
/* Disable WordPress Admin Bar */
/*-----------------------------------------------------------------------------------*/

$woo_admin_bar_disable = get_option( 'framework_woo_admin_bar_disable' );

if ( $woo_admin_bar_disable == 'true' ) {
	add_filter( 'show_admin_bar', '__return_false' );

	add_action( 'admin_print_scripts-profile.php', 'woo_hide_admin_bar_prefs' );

	function woo_hide_admin_bar_prefs () { ?>
	<style type="text/css">
	    .show-admin-bar { display: none; }
	</style>
	<?php
	} // End woo_hide_admin_bar_prefs()
}

/*-----------------------------------------------------------------------------------*/
/* Enhancements to the WordPress Admin Bar */
/*-----------------------------------------------------------------------------------*/

if ( $woo_admin_bar_disable != 'true' && is_user_logged_in() && current_user_can( 'manage_options' ) ) {
	$woo_admin_bar_enhancements = get_option( 'framework_woo_admin_bar_enhancements' );
	if ( $woo_admin_bar_enhancements == 'true' ) {
		add_action( 'admin_bar_menu', 'woo_admin_bar_menu', 20 );
	}
} // End IF Statement

/*-----------------------------------------------------------------------------------*/
/* woo_admin_bar_menu() - Add menu items to the admin bar. */
/*-----------------------------------------------------------------------------------*/

function woo_admin_bar_menu () {
	global $wp_admin_bar, $current_user;
    $current_user_id = $current_user->user_login;
    $super_user = get_option( 'framework_woo_super_user' );

	$theme_data = wooframework_get_theme_version_data();

	$menu_label = __( 'WooThemes', 'woothemes' );

	// Customise menu label to the child theme's name.
	if ( is_array( $theme_data ) && array_key_exists( 'theme_name', $theme_data ) ) {
		$menu_label = $theme_data['theme_name'];
	}

	// Main WooThemes Menu Item
	$wp_admin_bar->add_menu( array( 'id' => 'woothemes', 'title' => $menu_label, 'href' => admin_url('admin.php?page=woothemes') ) );

	// Theme Options
	$wp_admin_bar->add_menu( array( 'parent' => 'woothemes', 'id' => 'woothemes-theme-options', 'title' => __( 'Theme Options', 'woothemes' ), 'href' => admin_url( 'admin.php?page=woothemes' ) ) );

	if ( ( $super_user == $current_user_id ) || empty( $super_user ) ) {
		$wp_admin_bar->add_group( array( 'parent' => 'woothemes', 'id' => 'woothemes-super-user', 'meta' => array( 'class' => 'ab-sub-secondary' ) ) );

		// Framework Settings
		$wp_admin_bar->add_menu( array( 'parent' => 'woothemes-super-user', 'id' => 'woothemes-framework-settings', 'title' => __( 'Framework Settings', 'woothemes' ), 'href' => admin_url( 'admin.php?page=woothemes_framework_settings' ) ) );

		// Update Framework
		$wp_admin_bar->add_menu( array( 'parent' => 'woothemes-super-user', 'id' => 'woothemes-update-framework', 'title' => __( 'Update Framework', 'woothemes' ), 'href' => admin_url( 'admin.php?page=woothemes_framework_update' ) ) );

		// Theme Version Data Display
		if ( true == $theme_data['is_child'] ) {
			$child_theme_name = sprintf( __( 'Child Theme: %s %s', 'woothemes' ), $theme_data['child_theme_name'], $theme_data['child_theme_version'] );
			$wp_admin_bar->add_menu( array( 'parent' => 'woothemes-super-user', 'id' => 'woothemes-child-theme-version-data', 'title' => $child_theme_name ) );
		}

		$theme_name = sprintf( __( 'Theme: %s %s', 'woothemes' ), $theme_data['theme_name'], $theme_data['theme_version'] );
		$wp_admin_bar->add_menu( array( 'parent' => 'woothemes-super-user', 'id' => 'woothemes-theme-version-data', 'title' => $theme_name ) );

		$framework_version = sprintf( __( 'WooFramework: %s', 'woothemes' ), $theme_data['framework_version'] );
		$wp_admin_bar->add_menu( array( 'parent' => 'woothemes-super-user', 'id' => 'woothemes-framework-version-data', 'title' => $framework_version ) );
	} // End IF Statement
} // End woo_admin_bar_menu()

/*-----------------------------------------------------------------------------------*/
/* woo_prepare_category_ids_from_option()
 *
 * Setup an array of category IDs, from a given theme option.
 * Attempt to transform category slugs into ID values as well.
 *
 * Params: String $option
 * Return: Array $cats
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_prepare_category_ids_from_option' ) ) {
	function woo_prepare_category_ids_from_option ( $option ) {
		$cats = array();

		$stored_cats = get_option( $option );

		$cats_raw = explode( ',', $stored_cats );

		if ( is_array( $cats_raw ) && ( count( $cats_raw ) > 0 ) ) {
			foreach ( $cats_raw as $k => $v ) {
				$value = trim( $v );

				if ( is_numeric( $value ) ) {
					$cats_raw[$k] = $value;
				} else {
					$cat_obj = get_category_by_slug( $value );
					if ( isset( $cat_obj->term_id ) ) {
						$cats_raw[$k] = $cat_obj->term_id;
					}
				}

				$cats = $cats_raw;
			}
		}

		return $cats;
	} // End woo_prepare_category_ids_from_option()
}

/*-----------------------------------------------------------------------------------*/
/* Move tracking code from footer to header */
/*-----------------------------------------------------------------------------------*/
	
add_action( 'init', 'woo_move_tracking_code', 20 );

function woo_move_tracking_code () {
	$move_code = get_option( 'framework_woo_move_tracking_code' );
	
	if ( ! is_admin() && isset( $move_code ) && ( $move_code == 'true' ) ) {
		remove_action( 'wp_footer', 'woo_analytics' );
		add_action( 'wp_head', 'woo_analytics', 10 );
	}
} // End woo_move_tracking_code()


/*-----------------------------------------------------------------------------------*/
/* woo_get_dynamic_value() */
/* Replace values in a provided array with theme options, if available. */
/*
/* $settings array should resemble: $settings = array( 'theme_option_without_woo_' => 'default_value' );
/*
/* @since 4.4.4 */
/*-----------------------------------------------------------------------------------*/

function woo_get_dynamic_values ( $settings ) {
	global $woo_options;
	
	if ( is_array( $woo_options ) ) {
		foreach ( $settings as $k => $v ) {
			if ( isset( $woo_options['woo_' . $k] ) && ( $woo_options['woo_' . $k] != '' ) ) { $settings[$k] = $woo_options['woo_' . $k]; }
		}
	}
	
	return $settings;
} // End woo_get_dynamic_values()

/*-----------------------------------------------------------------------------------*/
/* woo_get_posts_by_taxonomy()
/*
/* Selects posts based on specified taxonomies.
/*
/* @since 4.5.0
/* @param array $args
/* @return array $posts
/*-----------------------------------------------------------------------------------*/
 
 function woo_get_posts_by_taxonomy ( $args = null ) {
 	global $wp_query;
 	
 	$posts = array();
 	
 	/* Parse arguments, and declare individual variables for each. */
 	
 	$defaults = array(
 						'limit' => 5, 
 						'post_type' => 'any', 
 						'taxonomies' => 'post_tag, category', 
 						'specific_terms' => '', 
 						'relationship' => 'OR', 
 						'order' => 'DESC', 
 						'orderby' => 'date', 
 						'operator' => 'IN', 
 						'exclude' => ''
 					);
 					
 	$args = wp_parse_args( $args, $defaults );
 	
 	extract( $args, EXTR_SKIP );
 	
 	// Make sure the order value is safe.
 	if ( ! in_array( $order, array( 'ASC', 'DESC' ) ) ) { $order = $defaults['order']; }
 	
 	// Make sure the orderby value is safe.
 	if ( ! in_array( $orderby, array( 'none', 'id', 'author', 'title', 'date', 'modified', 'parent', 'rand', 'comment_count', 'menu_order' ) ) ) { $orderby = $defaults['orderby']; }
 	
 	// Make sure the operator value is safe.
 	if ( ! in_array( $operator, array( 'IN', 'NOT IN', 'AND' ) ) ) { $orderby = $defaults['operator']; }
 	
 	// Convert our post types to an array.
 	if ( ! is_array( $post_type ) ) { $post_type = explode( ',', $post_type ); }
 	
 	// Convert our taxonomies to an array.
 	if ( ! is_array( $taxonomies ) ) { $taxonomies = explode( ',', $taxonomies ); }
 	
 	// Convert exclude to an array.
 	if ( ! is_array( $exclude ) && ( $exclude != '' ) ) { $exclude = explode( ',', $exclude ); }
 	
 	if ( ! count( (array)$taxonomies ) ) { return; }
 	
 	// Clean up our taxonomies for use in the query.
 	if ( count( $taxonomies ) ) {
 		foreach ( $taxonomies as $k => $v ) {
 			$taxonomies[$k] = trim( $v );
 		}
 	}
 	
 	// Determine which terms we're going to relate to this entry.
 	$related_terms = array();
 	
 	foreach ( $taxonomies as $t ) {
 		$terms = get_terms( $t, 'orderby=id&hide_empty=1' );
 		
 		if ( ! empty( $terms ) ) {
 			foreach ( $terms as $k => $v ) {
 				$related_terms[$t][$v->term_id] = $v->slug;
 			}
 		}
 	}
 	
 	// If specific terms are available, use those.
 	if ( ! is_array( $specific_terms ) ) { $specific_terms = explode( ',', $specific_terms ); }
 	
 	if ( count( $specific_terms ) ) {
 		foreach ( $specific_terms as $k => $v ) {
 			$specific_terms[$k] = trim( $v );
 		}
 	}
 	
 	// Look for posts with the same terms.
 	
 	// Setup query arguments.
 	$query_args = array();
 	
 	if ( $post_type ) { $query_args['post_type'] = $post_type; }
 	
 	if ( $limit ) {
 		$query_args['posts_per_page'] = $limit;
 		// $query_args['nopaging'] = true;
 	}
 	
 	// Setup specific posts to exclude.
 	if ( count( $exclude ) > 0 ) {
 		$query_args['post__not_in'] = $exclude;
 	}
 	
 	$query_args['order'] = $order;
 	$query_args['orderby'] = $orderby;
 	
 	$query_args['tax_query'] = array();
 	
 	// Setup for multiple taxonomies.
 	
 	if ( count( $related_terms ) > 1 ) {
 		$query_args['tax_query']['relation'] = $args['relationship'];
 	}
 	
 	// Add the taxonomies to the query arguments.
 	
 	foreach ( (array)$related_terms as $k => $v ) {
 		$terms_for_search = array_values( $v );
 	
 		if ( count( $specific_terms ) ) {
 			$specific_terms_by_tax = array();
 			
 			foreach ( $specific_terms as $i => $j ) {
 				if ( in_array( $j, array_values( $v ) ) ) {
 					$specific_terms_by_tax[] = $j;
 				}
 			}
 			
 			if ( count( $specific_terms_by_tax ) ) {
 				$terms_for_search = $specific_terms_by_tax;
 			}
 		}
 	
 		$query_args['tax_query'][] = array(
			'taxonomy' => $k,
			'field' => 'slug',
			'terms' => $terms_for_search, 
			'operator' => $operator
		);
 	}
 	
 	if ( empty( $query_args['tax_query'] ) ) { return; }
 	
 	$query_saved = $wp_query;
 	
 	$query = new WP_Query( $query_args );
 	
 	if ( $query->have_posts() ) {
 		while( $query->have_posts() ) {
 			$query->the_post();
 			
 			$posts[] = $query->post;
 		}
 	}
 	
 	$query = $query_saved;
 	
 	wp_reset_query();
 
 	return $posts;
 } // End woo_get_posts_by_taxonomy()

/*-----------------------------------------------------------------------------------*/
/* If the user has specified a "posts page", load the "Blog" page template there */
/*-----------------------------------------------------------------------------------*/

add_filter( 'template_include', 'woo_load_posts_page_blog_template', 10 );
	
if ( ! function_exists( 'woo_load_posts_page_blog_template' ) ) {
	function woo_load_posts_page_blog_template ( $template ) {
		if ( 'page' == get_option( 'show_on_front' ) && ( '' != get_option( 'page_for_posts' ) ) && is_home() ) {
			$tpl = locate_template( array( 'template-blog.php' ) );
			if ( $tpl != '' ) { $template = $tpl; }
		}
		return $template;
	} // End woo_load_posts_page_blog_template()
}

/*-----------------------------------------------------------------------------------*/
/* PressTrends API Integration */
/*-----------------------------------------------------------------------------------*/

/**
 * woo_presstrends function.
 *
 * @description Send data to the PressTrends API.
 * @access public
 * @return void
 */

if ( defined( 'WOO_PRESSTRENDS_THEMEKEY' ) ) {
	if ( get_option( 'framework_woo_presstrends_enable', 'false' ) == 'true' ) {
		add_action( 'admin_footer', 'woo_presstrends', 100 );
	}
}

function woo_presstrends () {
	if ( ! defined( 'WOO_PRESSTRENDS_THEMEKEY' ) ) { return; }

	// Add your PressTrends API Keys
	$api_key = 'ypvilflyjb7yyht8as1u2k0no3rxbgl2p4a9';
	$auth = WOO_PRESSTRENDS_THEMEKEY;

	// Check if we have cached data.
	$data = get_transient( 'woo_presstrends_data' );

	if ( ! $data || $data == '' ) {
		global $wpdb;

		// Don't edit below
		$api_base = 'http://api.presstrends.io/index.php/api/sites/add/auth/';
		$url = $api_base . $auth . '/api/' . $api_key . '/';

		$count_posts    = wp_count_posts();
		$count_pages    = wp_count_posts( 'page' );
		$comments_count = wp_count_comments();

		// Custom WooFramework way of getting theme data.
		$theme_data = wooframework_get_theme_version_data();
		$theme_name = $theme_data['theme_name'];
		$theme_version = $theme_data['theme_version'];

		$plugin_name = '&';
		foreach ( get_plugins() as $plugin_info ) {
			$plugin_name .= $plugin_info['Name'] . '&';
		}
		$posts_with_comments = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0" );
		$data                = array(
			'url'             => stripslashes( str_replace( array( 'http://', '/', ':' ), '', site_url() ) ),
			'posts'           => $count_posts->publish,
			'pages'           => $count_pages->publish,
			'comments'        => $comments_count->total_comments,
			'approved'        => $comments_count->approved,
			'spam'            => $comments_count->spam,
			'pingbacks'       => $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" ),
			'post_conversion' => ( $count_posts->publish > 0 && $posts_with_comments > 0 ) ? number_format( ( $posts_with_comments / $count_posts->publish ) * 100, 0, '.', '' ) : 0,
			'theme_version'   => $theme_version,
			'theme_name'      => $theme_name,
			'site_name'       => str_replace( ' ', '', get_bloginfo( 'name' ) ),
			'plugins'         => count( get_option( 'active_plugins' ) ),
			'plugin'          => urlencode( $plugin_name ),
			'wpversion'       => get_bloginfo( 'version' ),
			'api_version'	  => '2.4'
		);

		foreach ( $data as $k => $v ) {
			$url .= $k . '/' . $v . '/';
		}

		// Perform the remote request.
		$response = wp_remote_get( $url );
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			// Silence is golden.
		} else {
			set_transient( 'woo_presstrends_data', $data, 60*60*24 );
		}
	}
} // End woo_presstrends()

/*-----------------------------------------------------------------------------------*/
/* THE END */
/*-----------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------*/
/* WooDojo Download Banner */
/*-----------------------------------------------------------------------------------*/

if ( is_admin() && current_user_can( 'install_plugins' ) && ! class_exists( 'WooDojo' ) ) {
	add_action( 'wooframework_container_inside', 'wooframework_add_woodojo_banner' );
}

if ( defined( 'WOO_PRESSTRENDS_THEMEKEY' ) && is_admin() && current_user_can( 'switch_themes' ) && isset( $_GET['activated'] ) && ( $_GET['activated'] == 'true' ) ) {
	add_action( 'wooframework_container_inside', 'wooframework_add_presstrends_banner' );
}

add_action( 'wp_ajax_wooframework_banner_close', 'wooframework_ajax_banner_close' );

/**
 * Add a WooDojo banner on the Theme Options screen.
 * @since 5.3.4
 * @return void
 */
function wooframework_add_woodojo_banner () {
	if ( get_user_setting( 'wooframeworkhidebannerwoodojo', '0' ) == '1' ) { return; }

	$close_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=wooframework_banner_close&banner=woodojo' ), 'wooframework_banner_close' );
	$html = '';
	
	$html .= '<div id="woodojo-banner" class="wooframework-banner">' . "\n";
	$html .= '<span class="main">' . __( 'Enhance your theme with WooDojo.', 'woothemes' ) . '</span>' . "\n";
	$html .= '<span>' . __( 'WooDojo is a powerful WooThemes features suite for enhancing your website. Learn more.', 'woothemes' ) . '</span>' . "\n";
	$html .= '<a class="button-primary" href="' . esc_url( 'http://woothemes.com/woodojo/' ) . '" title="' . esc_attr__( 'Get WooDojo', 'woothemes' ) . '" target="_blank">' . __( 'Get WooDojo', 'woothemes' ) . '</a>' . "\n";
	$html .= '<span class="close-banner"><a href="' . $close_url . '">' . __( 'Close', 'woothemes' ) . '</a></span>' . "\n";
	$html .= '</div>' . "\n";
	
	echo $html;
} // End wooframework_add_woodojo_banner()

/**
 * Add a PressTrends banner on the Theme Options screen on first activation.
 * @since 5.3.4
 * @return void
 */
function wooframework_add_presstrends_banner () {
	if ( get_user_setting( 'wooframeworkhidebannerpresstrends', '0' ) == '1' ) { return; }

	$close_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=wooframework_banner_close&banner=presstrends' ), 'wooframework_banner_close' );
	$html = '';
	
	$html .= '<div id="presstrends-banner" class="wooframework-banner">' . "\n";
	$html .= '<span class="main">' . __( 'Enable PressTrends', 'woothemes' ) . '</span>' . "\n";
	$html .= '<span class="info">' . sprintf( __( 'PressTrends is a simple usage tracker that allows us to see how our customers are using WooThemes themes - so that we can help improve them for you. %sNone%s of your personal data is sent to PressTrends.', 'woothemes' ), '<br /><strong>', '</strong>' ) . '</span>' . "\n";
	$html .= '<a class="button-primary" href="' . esc_url( admin_url( 'admin.php?page=woothemes_framework_settings' ) ) . '" title="' . esc_attr__( 'Enable PressTrends', 'woothemes' ) . '">' . __( 'Enable PressTrends', 'woothemes' ) . '</a>' . "\n";
	$html .= '<span class="close-banner"><a href="' . $close_url . '">' . __( 'Close', 'woothemes' ) . '</a></span>' . "\n";
	$html .= '</div>' . "\n";
	
	echo $html;
} // End wooframework_add_presstrends_banner()

/**
 * wooframework_ajax_banner_close function.
 * 
 * @access public
 * @since 1.0.0
 */
function wooframework_ajax_banner_close () {
	if( ! current_user_can( 'install_plugins' ) ) wp_die( __( 'You do not have sufficient permissions to access this page.', 'woothemes' ) );
	
	if( ! check_admin_referer( 'wooframework_banner_close' ) ) wp_die( __( 'You have taken too long. Please go back and retry.', 'woothemes' ) );
	
	$banner = ( isset( $_GET['banner'] ) ) ? $_GET['banner'] : '';
	
	if( ! $banner ) die;

	// Run the update.
	$response = set_user_setting( 'wooframeworkhidebanner' . $banner, '1' );

	$sendback = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'ids' ), wp_get_referer() );
	wp_safe_redirect( $sendback );
	exit;
} // End toggle_notifications_status()

/*-----------------------------------------------------------------------------------*/
/* WooSEO Deprecation Banner */
/*-----------------------------------------------------------------------------------*/

if ( is_admin() && current_user_can( 'install_plugins' ) && isset( $_GET['page'] ) && ( $_GET['page'] == 'woothemes' || $_GET['page'] == 'woothemes_framework_settings' ) ) {
	add_action( 'wooframework_container_inside', 'wooframework_add_wooseosbm_banner' );
	add_action( 'wooframework_wooframeworksettings_container_inside', 'wooframework_add_wooseosbm_banner' );
}

/**
 * Add a WooSEO Deprecation banner on the WooSEO Options screen.
 * @since 5.4.0
 * @return void
 */
function wooframework_add_wooseosbm_banner () {
	if ( get_user_setting( 'wooframeworkhidebannerwooseosbmremoved', '0' ) == '1' ) { return; }

	$close_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=wooframework_banner_close&banner=wooseosbmremoved' ), 'wooframework_banner_close' );
	$html = '';
	
	$html .= '<div id="woodeprecate-banner" class="wooframework-banner">' . "\n";
	$html .= '<span class="main">' . __( 'WooSEO and Sidebar Manager have been removed from version 5.5.0 of the WooFramework.', 'woothemes' ) . '</span>' . "\n";
	$html .= '<span>' . sprintf( __( 'For your SEO needs, we encourage you to use the %1$s.', 'woothemes' ), '<a href="' . esc_url( 'http://wordpress.org/extend/plugins/wordpress-seo/' ) . '" title="' . esc_attr__( 'Get WordPress SEO', 'woothemes' ) . '" target="_blank">' . __( 'WordPress SEO Plugin', 'woothemes' ) . '</a>' ) . '</span><span>' . __( 'If you need help moving your existing SEO data, WordPress SEO has a built-in importer to move your data over.', 'woothemes' ) . '</span>' . "\n";
	$html .= '<br /><br /><span>' . sprintf( __( 'While the Sidebar Manager has been removed, we encourage you to download %1$s in our free plugin, %2$s. There is also a Sidebar Manager to WooSidebars Converter plugin, available through WooDojo.', 'woothemes' ), '<a href="' . esc_url( 'http://www.woothemes.com/woosidebars/' ) . '" title="' . esc_attr__( 'Get WooSidebars', 'woothemes' ) . '" target="_blank">' . __( 'WooSidebars', 'woothemes' ) . '</a>', '<a href="' . esc_url( 'http://www.woothemes.com/woodojo/' ) . '" title="' . esc_attr__( 'Get WooDojo', 'woothemes' ) . '" target="_blank">' . __( 'WooDojo', 'woothemes' ) . '</a>' ) . '</span>' . "\n";
	$html .= '<span class="close-banner"><a href="' . $close_url . '">' . __( 'Close', 'woothemes' ) . '</a></span>' . "\n";
	$html .= '</div>' . "\n";
	
	echo $html;
} // End wooframework_add_wooseosbm_banner()

/*-----------------------------------------------------------------------------------*/
/* Timthumb Detection Banner */
/*-----------------------------------------------------------------------------------*/

if ( is_admin() && current_user_can( 'install_plugins' ) ) {
	add_action( 'wooframework_wooframeworksettings_container_inside', 'wooframework_add_wootimthumb_banner' );
	add_action( 'wooframework_container_inside', 'wooframework_add_wootimthumb_banner' );
}

/**
 * Add a Timthumb Detection banner on all WooThemes Options screens.
 * @since 5.4.0
 * @return void
 */
function wooframework_add_wootimthumb_banner () {
	if ( get_user_setting( 'wooframeworkhidebannerwootimthumb', '0' ) == '1' ) { return; }
	
	// Test for old timthumb scripts
	$thumb_php_test = file_exists(  get_template_directory() . '/thumb.php' );
	$timthumb_php_test = file_exists(  get_template_directory() . '/timthumb.php' );
		
	if ( $thumb_php_test || $timthumb_php_test ) {
		$theme_dir = str_replace( WP_CONTENT_DIR, '', get_template_directory() );
		$close_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=wooframework_banner_close&banner=wootimthumb' ), 'wooframework_banner_close' );
		$html = '';
		
		$html .= '<div id="woodeprecate-banner" class="wooframework-banner">' . "\n";
		$html .= '<span class="main">' . __( 'ATTENTION: Insecure Version of Timthumb Image Resize Script Detected', 'woothemes' ) . '</span>' . "\n";
		$html .= '<span>' . __( "A possible old version of the TimThumb script was detected in your theme folder. Please remove the following files from your theme as a security precaution", 'woothemes' ) . ':</span>' . "\n";
		if ( $thumb_php_test ) { $html .= '<span><strong>- thumb.php</strong> ( found at ' . $theme_dir . '/thumb.php' . ' )</span>' . "\n"; }
    	if ( $timthumb_php_test ) { $html .= '<span><strong>- timthumb.php</strong> ( found at ' . $theme_dir . '/timthumb.php' . ' )</span>' . "\n"; }
		$html .= '<span class="close-banner"><a href="' . $close_url . '">' . __( 'Close', 'woothemes' ) . '</a></span>' . "\n";
		$html .= '</div>' . "\n";
		
		echo $html;
	} else {
		return;
	}
	
} // End wooframework_add_wootimthumb_banner()

/*-----------------------------------------------------------------------------------*/
/* Static Front Page Detection Banner */
/*-----------------------------------------------------------------------------------*/

if ( is_admin() && current_user_can( 'manage_options' ) && ( 0 < intval( get_option( 'page_on_front' ) ) ) ) {
	add_action( 'wooframework_container_inside', 'wooframework_add_static_front_page_banner' );
}

/**
 * Add a Static Front Page Detection banner on all WooThemes Options screens.
 * @since 5.5.2
 * @return void
 */
function wooframework_add_static_front_page_banner () {
	if ( get_user_setting( 'wooframeworkhidebannerstaticfrontpage', '0' ) == '1' ) { return; }
	$theme_data = wooframework_get_theme_version_data();
	$close_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=wooframework_banner_close&banner=staticfrontpage' ), 'wooframework_banner_close' );
	$html = '';
	$html .= '<div id="staticfrontpage-banner" class="wooframework-banner">' . "\n";
	$html .= '<span class="main">' . sprintf( __( 'You have setup a static front page in %1$sSettings > Reading%2$s.  Please set it to show "Your latest posts" if you want to display the default homepage in %3$s.', 'woothemes' ), '<strong><a href="' . esc_url( admin_url( 'options-reading.php' ) ) . '">', '</a></strong>', $theme_data['theme_name'], '<strong>', '</strong>' ) . '</span>' . "\n";
	$html .= '<span class="close-banner"><a href="' . $close_url . '">' . __( 'Close', 'woothemes' ) . '</a></span>' . "\n";
	$html .= '</div>' . "\n";
	
	echo $html;
} // End wooframework_add_static_front_page_banner()

/**
 * Get the version data for the currently active theme.
 * @since  5.4.2
 * @return array [theme_version, theme_name, framework_version, is_child, child_theme_version, child_theme_name]
 */
if ( ! function_exists( 'wooframework_get_theme_version_data' ) ) {
function wooframework_get_theme_version_data () {
	$response = array(
					'theme_version' => '', 
					'theme_name' => '', 
					'framework_version' => get_option( 'woo_framework_version' ), 
					'is_child' => is_child_theme(), 
					'child_theme_version' => '', 
					'child_theme_name' => ''
					);

	if ( function_exists( 'wp_get_theme' ) ) {
		$theme_data = wp_get_theme();
		if ( true == $response['is_child'] ) {
			$response['theme_version'] = $theme_data->parent()->Version;
			$response['theme_name'] = $theme_data->parent()->Name;

			$response['child_theme_version'] = $theme_data->Version;
			$response['child_theme_name'] = $theme_data->Name;
		} else {
			$response['theme_version'] = $theme_data->Version;
			$response['theme_name'] = $theme_data->Name;
		}
	} else {
		$theme_data = get_theme_data( get_template_directory() . '/style.css' );
		$response['theme_version'] = $theme_data['Version'];
		$response['theme_name'] = $theme_data['Name'];

		if ( true == $response['is_child'] ) {
			$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
			$response['child_theme_version'] = $theme_data['Version'];
			$response['child_theme_name'] = $theme_data['Name'];
		}
	}

	return $response;
} // End wooframework_get_theme_version_data()
}

if ( ! function_exists( 'wooframework_display_theme_version_data' ) ) {
/**
 * Display the version data for the currently active theme.
 * @since  5.4.2
 * @return void
 */
function wooframework_display_theme_version_data ( $echo = true ) {
	$data = wooframework_get_theme_version_data();
	$html = '';

	// Theme Version
	if ( true == $data['is_child'] ) {
		$html .= '<span class="theme">' . esc_html( $data['child_theme_name'] . ' ' . $data['child_theme_version'] ) . '</span>' . "\n";
		$html .= '<span class="parent-theme">' . esc_html( $data['theme_name'] . ' ' . $data['theme_version'] ) . '</span>' . "\n";
	} else {
		$html .= '<span class="theme">' . esc_html( $data['theme_name'] . ' ' . $data['theme_version'] ) . '</span>' . "\n";
	}
	
	// Framework Version
	$html .= '<span class="framework">' . esc_html( sprintf( __( 'Framework %s', 'woothemes' ), $data['framework_version'] ) ) . '</span>' . "\n";

	if ( true == $echo ) { echo $html; } else { return $html; }
} // End wooframework_display_theme_version_data()
}

if ( ! function_exists( 'wooframework_load_google_fonts' ) ) {
function wooframework_load_google_fonts() {
	global $woo_used_google_fonts;

	if( $woo_used_google_fonts && is_array( $woo_used_google_fonts ) ) {
		$fonts = '';
		$c = 0;
		foreach( $woo_used_google_fonts as $font ) {
			if( $c > 0 ) {
				$fonts .= '|';
			} else {
				++$c;
			}
			$fonts .= $font;
		}

		if( '' != $fonts ) {
			woo_shortcode_typography_loadgooglefonts( $fonts , 'woo-used-google-fonts' );
		}
	}
} // End wooframework_load_google_fonts()
}
add_action( 'wp_footer' , 'wooframework_load_google_fonts' );
?>