<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if (!function_exists( 'woo_options')) {
function woo_options() {

// THEME VARIABLES
$themename = 'Superstore';
$themeslug = 'superstore';

// STANDARD VARIABLES. DO NOT TOUCH!
$shortname = 'woo';
$manualurl = 'http://www.woothemes.com/support/theme-documentation/'.$themeslug.'/';

//Stylesheets Reader
$alt_stylesheet_path = get_template_directory() . '/styles/';
$alt_stylesheets = array();
if ( is_dir($alt_stylesheet_path) ) {
    if ($alt_stylesheet_dir = opendir($alt_stylesheet_path) ) {
        while ( ($alt_stylesheet_file = readdir($alt_stylesheet_dir)) !== false ) {
            if(stristr($alt_stylesheet_file, '.css') !== false) {
                $alt_stylesheets[] = $alt_stylesheet_file;
            }
        }
    }
}

// More Options
$slide_options = array();
$total_possible_slides = 10;
for ( $i = 1; $i <= $total_possible_slides; $i++ ) { $slide_options[] = $i; }

// Setup an array of slide-page terms for a dropdown.
$args = array( 'echo' => 0, 'hierarchical' => 1, 'taxonomy' => 'slide-page' );
$cats_dropdown = wp_dropdown_categories( $args );
$cats = array();

// Quick string hack to make sure we get the pages with the indents.
$cats_dropdown = str_replace( "<select name='cat' id='cat' class='postform' >", '', $cats_dropdown );
$cats_dropdown = str_replace( '</select>', '', $cats_dropdown );
$cats_split = explode( '</option>', $cats_dropdown );

$cats[] = __( 'Select a Slide Group:', 'woothemes' );

foreach ( $cats_split as $k => $v ) {
    $id = '';
    // Get the ID value.
    preg_match( '/value="(.*?)"/i', $v, $matches );

    if ( isset( $matches[1] ) ) {
        $id = $matches[1];
        $cats[$id] = trim( strip_tags( $v ) );
    }
}

$slide_groups = $cats;

// Setup an array of category terms for a dropdown.
$args = array( 'echo' => 0, 'hierarchical' => 1, 'taxonomy' => 'category' );
$cats_dropdown = wp_dropdown_categories( $args );
$cats = array();

// Quick string hack to make sure we get the pages with the indents.
$cats_dropdown = str_replace( "<select name='cat' id='cat' class='postform' >", '', $cats_dropdown );
$cats_dropdown = str_replace( '</select>', '', $cats_dropdown );
$cats_split = explode( '</option>', $cats_dropdown );

$cats[] = __( 'Select a Category:', 'woothemes' );

foreach ( $cats_split as $k => $v ) {
    $id = '';
    // Get the ID value.
    preg_match( '/value="(.*?)"/i', $v, $matches );

    if ( isset( $matches[1] ) ) {
        $id = $matches[1];
        $cats[$id] = trim( strip_tags( $v ) );
    }
}

$woo_categories = $cats;

// Setup an array of post_tag terms for a dropdown.
$args = array( 'echo' => 0, 'hierarchical' => 1, 'taxonomy' => 'post_tag' );
$cats_dropdown = wp_dropdown_categories( $args );
$cats = array();

// Quick string hack to make sure we get the pages with the indents.
$cats_dropdown = str_replace( "<select name='cat' id='cat' class='postform' >", '', $cats_dropdown );
$cats_dropdown = str_replace( '</select>', '', $cats_dropdown );
$cats_split = explode( '</option>', $cats_dropdown );

$cats[] = __( 'Select a Post Tag:', 'woothemes' );

foreach ( $cats_split as $k => $v ) {
    $id = '';
    // Get the ID value.
    preg_match( '/value="(.*?)"/i', $v, $matches );

    if ( isset( $matches[1] ) ) {
        $id = $matches[1];
        $cats[$id] = trim( strip_tags( $v ) );
    }
}

$woo_post_tags = $cats;

// Setup an array of numbers.
$woo_numbers = array();
for ( $i = 1; $i <= 20; $i++ ) {
    $woo_numbers[$i] = $i;
}

// Setup an array of pages for a dropdown.
$args = array( 'echo' => 0 );
$pages_dropdown = wp_dropdown_pages( $args );
$pages = array();

// Quick string hack to make sure we get the pages with the indents.
$pages_dropdown = str_replace( '<select name="page_id" id="page_id">', '', $pages_dropdown );
$pages_dropdown = str_replace( '</select>', '', $pages_dropdown );
$pages_split = explode( '</option>', $pages_dropdown );

$pages[] = __( 'Select a Page:', 'woothemes' );

foreach ( $pages_split as $k => $v ) {
    $id = '';
    // Get the ID value.
    preg_match( '/value="(.*?)"/i', $v, $matches );

    if ( isset( $matches[1] ) ) {
        $id = $matches[1];
        $pages[$id] = trim( strip_tags( $v ) );
    }
}

$woo_pages = $pages;

// THIS IS THE DIFFERENT FIELDS
$options = array();
$other_entries = array( '0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19' );

/* General */

$options[] = array( 'name' => __( 'General Settings', 'woothemes' ),
    				'type' => 'heading',
    				'icon' => 'general' );

$options[] = array( 'name' => __( 'Quick Start', 'woothemes' ),
    				'type' => 'subheading' );

$options[] = array( 'name' => __( 'Theme Stylesheet', 'woothemes' ),
    				'desc' => __( 'Select your themes alternative color scheme.', 'woothemes' ),
    				'id' => $shortname . '_alt_stylesheet',
    				'std' => 'default.css',
    				'type' => 'select',
    				'options' => $alt_stylesheets );

$options[] = array( 'name' => __( 'Custom Logo', 'woothemes' ),
    				'desc' => __( 'Upload a logo for your theme, or specify an image URL directly.', 'woothemes' ),
    				'id' => $shortname . '_logo',
    				'std' => '',
    				'type' => 'upload' );

$options[] = array( 'name' => __( 'Text Title', 'woothemes' ),
    				'desc' => sprintf( __( 'Enable text-based Site Title and Tagline. Setup title & tagline in %1$s.', 'woothemes' ), '<a href="' . esc_url( home_url() ) . '/wp-admin/options-general.php">' . __( 'General Settings', 'woothemes' ) . '</a>' ),
    				'id' => $shortname . '_texttitle',
    				'std' => 'false',
    				'class' => 'collapsed',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Site Title', 'woothemes' ),
    				'desc' => __( 'Change the site title typography.', 'woothemes' ),
    				'id' => $shortname . '_font_site_title',
    				'std' => array( 'size' => '1', 'unit' => 'em', 'face' => 'Helvetica', 'style' => '', 'color' => '#333333' ),
    				'class' => 'hidden',
    				'type' => 'typography' );

$options[] = array( 'name' => __( 'Site Description', 'woothemes' ),
    				'desc' => __( 'Enable the site description/tagline under site title.', 'woothemes' ),
    				'id' => $shortname . '_tagline',
    				'class' => 'hidden',
    				'std' => 'false',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Site Description', 'woothemes' ),
    				'desc' => __( 'Change the site description typography.', 'woothemes' ),
    				'id' => $shortname . '_font_tagline',
    				'std' => array( 'size' => '1', 'unit' => 'em', 'face' => 'Helvetica', 'style' => '', 'color' => '#999999' ),
    				'class' => 'hidden last',
    				'type' => 'typography' );

$options[] = array( 'name' => __( 'Custom Favicon', 'woothemes' ),
    				'desc' => sprintf( __( 'Upload a 16px x 16px %1$s that will represent your website\'s favicon.', 'woothemes' ), '<a href="http://www.faviconr.com/">'.__( 'ico image', 'woothemes' ).'</a>' ),
    				'id' => $shortname . '_custom_favicon',
    				'std' => '',
    				'type' => 'upload' );

$options[] = array( 'name' => __( 'Tracking Code', 'woothemes' ),
    				'desc' => __( 'Paste your Google Analytics (or other) tracking code here. This will be added into the footer template of your theme.', 'woothemes' ),
    				'id' => $shortname . '_google_analytics',
    				'std' => '',
    				'type' => 'textarea' );

$options[] = array( 'name' => __( 'Subscription Settings', 'woothemes' ),
    				'type' => 'subheading' );

$options[] = array( 'name' => __( 'RSS URL', 'woothemes' ),
    				'desc' => __( 'Enter your preferred RSS URL. (Feedburner or other)', 'woothemes' ),
    				'id' => $shortname . '_feed_url',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'E-Mail Subscription URL', 'woothemes' ),
    				'desc' => __( 'Enter your preferred E-mail subscription URL. (Feedburner or other)', 'woothemes' ),
    				'id' => $shortname . '_subscribe_email',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'Display Options', 'woothemes' ),
    				'type' => 'subheading' );

$options[] = array( 'name' => __( 'Custom CSS', 'woothemes' ),
    				'desc' => __( 'Quickly add some CSS to your theme by adding it to this block.', 'woothemes' ),
    				'id' => $shortname . '_custom_css',
    				'std' => '',
    				'type' => 'textarea' );

$options[] = array( 'name' => __( 'Post/Page Comments', 'woothemes' ),
    				'desc' => __( 'Select if you want to enable/disable comments on posts and/or pages.', 'woothemes' ),
    				'id' => $shortname . '_comments',
    				'std' => 'both',
    				'type' => 'select2',
    				'options' => array( 'post' => __( 'Posts Only', 'woothemes' ), 'page' => __( 'Pages Only', 'woothemes' ), 'both' => __( 'Pages / Posts', 'woothemes' ), 'none' => __( 'None', 'woothemes' ) ) );

$options[] = array( 'name' => __( 'Post Content', 'woothemes' ),
    				'desc' => __( 'Select if you want to show the full content or the excerpt on posts.', 'woothemes' ),
    				'id' => $shortname . '_post_content',
    				'type' => 'select2',
    				'options' => array( 'excerpt' => __( 'The Excerpt', 'woothemes' ), 'content' => __( 'Full Content', 'woothemes' ) ) );

$options[] = array( 'name' => __( 'Post Author Box', 'woothemes' ),
    				'desc' => sprintf( __( 'This will enable the post author box on the single posts page. Edit description in %1$s.', 'woothemes' ), '<a href="' . esc_url( home_url() ) . '/wp-admin/profile.php">' . __( 'Profile', 'woothemes' ) . '</a>' ),
    				'id' => $shortname . '_post_author',
    				'std' => 'true',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Display Breadcrumbs', 'woothemes' ),
    				'desc' => __( 'Display dynamic breadcrumbs on each page of your website.', 'woothemes' ),
    				'id' => $shortname . '_breadcrumbs_show',
    				'std' => 'true',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Display Pagination', 'woothemes' ),
    				'desc' => __( 'Display pagination on the blog.', 'woothemes' ),
    				'id' => $shortname . '_pagenav_show',
    				'std' => 'true',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Pagination Style', 'woothemes' ),
    				'desc' => __( 'Select the style of pagination you would like to use on the blog.', 'woothemes' ),
    				'id' => $shortname . '_pagination_type',
    				'type' => 'select2',
    				'options' => array( 'paginated_links' => __( 'Numbers', 'woothemes' ), 'simple' => __( 'Next/Previous', 'woothemes' ) ) );

/* Styling */

$options[] = array( 'name' => __( 'Styling', 'woothemes' ),
    				'type' => 'heading',
    				'icon' => 'styling' );

$options[] = array( 'name' => __( 'Background', 'woothemes' ),
    				'type' => 'subheading' );

$options[] = array( 'name' => __( 'Body Background Color', 'woothemes' ),
    				'desc' => __( 'Pick a custom color for background color of the theme e.g. #697e09', 'woothemes' ),
    				'id' => $shortname . '_body_color',
    				'std' => '',
    				'type' => 'color' );

$options[] = array( 'name' => __( 'Body background image', 'woothemes' ),
    				'desc' => __( 'Upload an image for the theme\'s background', 'woothemes' ),
    				'id' => $shortname . '_body_img',
    				'std' => '',
    				'type' => 'upload' );

$options[] = array( 'name' => __( 'Background image repeat', 'woothemes' ),
    				'desc' => __( 'Select how you would like to repeat the background-image', 'woothemes' ),
    				'id' => $shortname . '_body_repeat',
    				'std' => 'no-repeat',
    				'type' => 'select',
    				'options' => array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) );

$options[] = array( 'name' => __( 'Background image position', 'woothemes' ),
    				'desc' => __( 'Select how you would like to position the background', 'woothemes' ),
    				'id' => $shortname . '_body_pos',
    				'std' => 'top',
    				'type' => 'select',
    				'options' => array( 'top left', 'top center', 'top right', 'center left', 'center center', 'center right', 'bottom left', 'bottom center', 'bottom right' ) );

$options[] = array( 'name' => __( 'Background Attachment', 'woothemes' ),
    				'desc' => __( 'Select whether the background should be fixed or move when the user scrolls', 'woothemes' ),
    				'id' => $shortname.'_body_attachment',
    				'std' => 'scroll',
    				'type' => 'select',
    				'options' => array( 'scroll', 'fixed' ) );

$options[] = array( 'name' => __( 'Links', 'woothemes' ),
    				'type' => 'subheading' );

$options[] = array( 'name' => __( 'Link Color', 'woothemes' ),
    				'desc' => __( 'Pick a custom color for links or add a hex color code e.g. #697e09', 'woothemes' ),
    				'id' => $shortname . '_link_color',
    				'std' => '',
    				'type' => 'color' );

$options[] = array( 'name' => __( 'Link Hover Color', 'woothemes' ),
    				'desc' => __( 'Pick a custom color for links hover or add a hex color code e.g. #697e09', 'woothemes' ),
    				'id' => $shortname . '_link_hover_color',
    				'std' => '',
    				'type' => 'color' );

$options[] = array( 'name' => __( 'Button Color', 'woothemes' ),
    				'desc' => __( 'Pick a custom color for buttons or add a hex color code e.g. #697e09', 'woothemes' ),
    				'id' => $shortname . '_button_color',
    				'std' => '',
    				'type' => 'color' );

/* Typography */

$options[] = array( 'name' => __( 'Typography', 'woothemes' ),
    				'type' => 'heading',
    				'icon' => 'typography' );

$options[] = array( 'name' => __( 'Enable Custom Typography', 'woothemes' ) ,
    				'desc' => __( 'Enable the use of custom typography for your site. Custom styling will be output in your sites HEAD.', 'woothemes' ) ,
    				'id' => $shortname . '_typography',
    				'std' => 'false',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'General Typography', 'woothemes' ) ,
    				'desc' => __( 'Change the general font.', 'woothemes' ) ,
    				'id' => $shortname . '_font_body',
    				'std' => array( 'size' => '1.4', 'unit' => 'em', 'face' => 'FontSiteSans-Roman', 'style' => '', 'color' => '#3E3E3E' ),
    				'type' => 'typography' );

$options[] = array( 'name' => __( 'Navigation', 'woothemes' ) ,
    				'desc' => __( 'Change the navigation font.', 'woothemes' ),
    				'id' => $shortname . '_font_nav',
    				'std' => array( 'size' => '1', 'unit' => 'em', 'face' => 'FontSiteSans-Cond', 'style' => '', 'color' => '#3E3E3E' ),
    				'type' => 'typography' );

$options[] = array( 'name' => __( 'Page Title', 'woothemes' ) ,
    				'desc' => __( 'Change the page title.', 'woothemes' ) ,
    				'id' => $shortname . '_font_page_title',
    				'std' => array( 'size' => '1.4', 'unit' => 'em', 'face' => 'BergamoStd', 'style' => 'bold', 'color' => '#3E3E3E' ),
    				'type' => 'typography' );

$options[] = array( 'name' => __( 'Post Title', 'woothemes' ) ,
    				'desc' => __( 'Change the post title.', 'woothemes' ) ,
    				'id' => $shortname . '_font_post_title',
    				'std' => array( 'size' => '1.4', 'unit' => 'em', 'face' => 'BergamoStd', 'style' => 'bold', 'color' => '#3E3E3E' ),
    				'type' => 'typography' );

$options[] = array( 'name' => __( 'Post Meta', 'woothemes' ),
    				'desc' => __( 'Change the post meta.', 'woothemes' ) ,
    				'id' => $shortname . '_font_post_meta',
    				'std' => array( 'size' => '1', 'unit' => 'em', 'face' => 'BergamoStd', 'style' => '', 'color' => '#3E3E3E' ),
    				'type' => 'typography' );

$options[] = array( 'name' => __( 'Post Entry', 'woothemes' ) ,
    				'desc' => __( 'Change the post entry.', 'woothemes' ) ,
    				'id' => $shortname . '_font_post_entry',
    				'std' => array( 'size' => '1', 'unit' => 'em', 'face' => 'BergamoStd', 'style' => '', 'color' => '#3E3E3E' ),
    				'type' => 'typography' );

$options[] = array( 'name' => __( 'Widget Titles', 'woothemes' ) ,
    				'desc' => __( 'Change the widget titles.', 'woothemes' ) ,
    				'id' => $shortname . '_font_widget_titles',
    				'std' => array( 'size' => '1', 'unit' => 'em', 'face' => 'FontSiteSans-Cond', 'style' => 'bold', 'color' => '#3E3E3E' ),
    				'type' => 'typography' );

/* Layout */

$options[] = array( 'name' => __( 'Layout', 'woothemes' ),
    				'type' => 'heading',
    				'icon' => 'layout' );

$url =  get_template_directory_uri() . '/functions/images/';
$options[] = array( 'name' => __( 'Main Layout', 'woothemes' ),
    				'desc' => __( 'Select which layout you want for your site.', 'woothemes' ),
    				'id' => $shortname . '_site_layout',
    				'std' => 'layout-right-content',
    				'type' => 'images',
    				'options' => array(
    					'layout-left-content' => $url . '2cl.png',
    					'layout-right-content' => $url . '2cr.png' )
    				);

$options[] = array( 'name' => __( 'Category Exclude - Homepage', 'woothemes' ),
    				'desc' => __( 'Specify a comma seperated list of category IDs or slugs that you\'d like to exclude from your homepage (eg: uncategorized).', 'woothemes' ),
    				'id' => $shortname . '_exclude_cats_home',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'Category Exclude - Blog Page Template', 'woothemes' ),
    				'desc' => __( 'Specify a comma seperated list of category IDs or slugs that you\'d like to exclude from your \'Blog\' page template (eg: uncategorized).', 'woothemes' ),
    				'id' => $shortname . '_exclude_cats_blog',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'Business Template', 'woothemes' ),
                    'type' => 'heading',
                    'icon' => 'layout' );
$options[] = array( 'name' => __( 'Display WooSlider', 'woothemes' ),
                    'desc' => sprintf( __( 'Display a slider above the page content? Requires %sWooSlider%s plugin.', 'woothemes' ), '<a href="http://www.woothemes.com/products/wooslider/" title="' . __( 'Purchase WooSlider from WooThemes.com', 'woothemes' ) . '" target="_blank">', '</a>' ),
                    'id' => $shortname . '_business_display_slider',
                    'std' => 'true',
                    'type' => 'checkbox' );
$options[] = array( 'name' => __( 'Display Features', 'woothemes' ),
                    'desc' => sprintf( __( 'Display Features beneath the page content? Requires %sFeatures%s plugin.', 'woothemes' ), '<a href="http://wordpress.org/extend/plugins/features-by-woothemes/" title="' . __( 'Download \'Features by WooThemes\' from WordPress.org', 'woothemes' ) . '" target="_blank">', '</a>' ),
                    'id' => $shortname . '_business_display_features',
                    'std' => 'true',
                    'type' => 'checkbox' );
$options[] = array( 'name' => __( 'Display Testimonials', 'woothemes' ),
                    'desc' => sprintf( __( 'Display testimonials beneath the page content? Requires %sTestimonials%s plugin.', 'woothemes' ), '<a href="http://wordpress.org/extend/plugins/testimonials-by-woothemes/" title="' . __( 'Download \'Testimonials by WooThemes\' from WordPress.org', 'woothemes' ) . '" target="_blank">', '</a>' ),
                    'id' => $shortname . '_business_display_testimonials',
                    'std' => 'true',
                    'type' => 'checkbox' );
$options[] = array( 'name' => __( 'Display latest blog posts and sidebar', 'woothemes' ),
                    'desc' => __( 'Display your latest blog posts and primary sidebar beneath the business template content', 'woothemes' ),
                    'id' => $shortname . '_business_display_blog',
                    'std' => 'true',
                    'type' => 'checkbox' );

/* Featured Slider */
/* See top of file for logic pertaining to $slide_options and $slide_groups arrays. */

$options[] = array( 'name' => __( 'Featured Slider', 'woothemes' ),
                    'icon' => 'slider',
                    'type' => 'heading' );

$options[] = array( 'name' => __( 'Slider Content', 'woothemes' ),
                    'type' => 'subheading' );

$options[] = array( 'name' => __( 'Enable Featured Slider', 'woothemes' ),
                    'desc' => __( 'Enable the featured slider on the homepage.', 'woothemes' ),
                    'id' => $shortname . '_featured',
                    'std' => 'false',
                    'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Number of Slides', 'woothemes' ),
                    'desc' => __( 'Select the number of slides that should appear in the featured slider.', 'woothemes' ),
                    'id' => $shortname . '_featured_entries',
                    'std' => '3',
                    'type' => 'select',
                    'options' => $slide_options );

$options[] = array( 'name' => __( 'Slide Group', 'woothemes' ),
                    'desc' => __( 'Optionally choose to display only slides from a specific slide group.', 'woothemes' ),
                    'id' => $shortname . '_featured_slide_group',
                    'std' => '0',
                    'type' => 'select2',
                    'options' => $slide_groups );

$options[] = array( 'name' => __( 'Display Title On Video Slides', 'woothemes' ),
                    'desc' => __( 'If a slide has a video in the "Embed Code" field, display the slide title & content.', 'woothemes' ),
                    'id' => $shortname . '_featured_videotitle',
                    'std' => 'true',
                    'type' => 'checkbox');

$options[] = array( 'name' => __( 'Display Order', 'woothemes' ),
                    'desc' => __( 'Select which way you wish to order your slider posts.', 'woothemes' ),
                    'id' => $shortname . '_featured_order',
                    'std' => 'DESC',
                    'type' => 'select2',
                    'options' => array( 'DESC' => __( 'Newest to oldest', 'woothemes' ), 'ASC' => __( 'Oldest to newest', 'woothemes' ) ) );

$options[] = array( 'name' => __( 'Slider Settings', 'woothemes' ),
                    'type' => 'subheading' );

$options[] = array( 'name' => __( 'Animation Effect', 'woothemes' ),
                    'desc' => __( 'Select whether the featured slider should slide or fade.', 'woothemes' ),
                    'id' => $shortname . '_featured_animation',
                    'std' => 'fade',
                    'type' => 'select2',
                    'options' => array( 'fade' => __( 'Fade', 'woothemes' ), 'slide' => __( 'Slide', 'woothemes' ) ) );

$options[] = array( 'name' => __( 'Next / Previous Navigation', 'woothemes' ),
                    'desc' => __( 'Select to enable next/prev slider for the featured slider.', 'woothemes' ),
                    'id' => $shortname . '_featured_nextprev',
                    'std' => 'true',
                    'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Pagination Controls', 'woothemes' ),
                    'desc' => __( 'Select to enable pagination for the featured slider.', 'woothemes' ),
                    'id' => $shortname . '_featured_pagination',
                    'std' => 'false',
                    'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Pause On Hover', 'woothemes' ),
                    'desc' => __( 'Hovering over the featured slider will pause it.', 'woothemes' ),
                    'id' => $shortname . '_featured_hover',
                    'std' => 'true',
                    'type' => 'checkbox');

$options[] = array( 'name' => __( 'Pause On Action', 'woothemes' ),
                    'desc' => __( 'Using the featured slider navigation manually will pause it.', 'woothemes' ),
                    'id' => $shortname . '_featured_action',
                    'std' => 'true',
                    'type' => 'checkbox');

$options[] = array( 'name' => __( 'Auto-Animate Interval', 'woothemes' ),
                    'desc' => sprintf( __( 'The time in %1$sseconds%2$s each slide pauses for, before transitioning to the next %3$s(set to "Off" to disable automatic transitions).', 'woothemes' ), '<strong>', '</strong>', '<br /><br />' ),
                    'id' => $shortname . '_featured_speed',
                    'std' => '7',
                    'type' => 'select2',
                    'options' => array_merge( array( '0' => __( 'Off', 'woothemes' ) ), $slide_options ) );

/* Homepage */

$options[] = array( 'name' => __( 'Homepage', 'woothemes' ),
                    'icon' => 'homepage',
                    'type' => 'heading' );

$options[] = array( 'name' => __( 'Homepage Setup', 'woothemes' ),
                    'type' => 'subheading' );

$options[] = array( 'name' => __( 'Homepage Setup', 'woothemes' ),
                    'desc' => '',
                    'id' => $shortname . '_homepage_notice',
                    'std' => sprintf( __( 'You can optionally customise the homepage by adding widgets to the "Homepage" widgetized area on the "%sWidgets%s" screen with the "Woo - Component" widget.', 'woothemes' ), '<a href="' . esc_url( admin_url( 'widgets.php' ) ) . '">', '</a>' ) . '<br /><br />' . __( 'If you do so, this will override the options below.', 'woothemes' ),
                    'type' => 'info' );

if ( is_woocommerce_activated() ) {
$options[] = array( 'name' => __( 'Enable Product Categories', 'woothemes' ),
                    'desc' => __( 'Display product categories on the homepage.', 'woothemes' ),
                    'id' => $shortname . '_homepage_enable_product_categories',
                    'std' => 'true',
                    'type' => 'checkbox');

$options[] = array( 'name' => __( 'Enable Featured Products', 'woothemes' ),
                    'desc' => __( 'Display featured products on the homepage.', 'woothemes' ),
                    'id' => $shortname . '_homepage_enable_featured_products',
                    'std' => 'true',
                    'type' => 'checkbox');

$options[] = array( 'name' => __( 'Enable Recent Products', 'woothemes' ),
                    'desc' => __( 'Display recent products on the homepage.', 'woothemes' ),
                    'id' => $shortname . '_homepage_enable_recent_products',
                    'std' => 'true',
                    'type' => 'checkbox');
}

$options[] = array( 'name' => __( 'Enable Testimonials', 'woothemes' ),
                    'desc' => sprintf( __( 'Display testimonials on the homepage. Requires %sTestimonials%s plugin.', 'woothemes' ), '<a href="http://wordpress.org/extend/plugins/testimonials-by-woothemes/" title="' . __( 'Download \'Testimonials by WooThemes\' from WordPress.org', 'woothemes' ) . '" target="_blank">', '</a>' ),
                    'id' => $shortname . '_homepage_enable_testimonials',
                    'std' => 'true',
                    'type' => 'checkbox');

$options[] = array( 'name' => __( 'Enable Content Area', 'woothemes' ),
                    'desc' => __( 'Display the content area with either page content or a list of blog posts.', 'woothemes' ),
                    'id' => $shortname . '_homepage_enable_content',
                    'std' => 'true',
                    'type' => 'checkbox');

if ( is_woocommerce_activated() ) {
$options[] = array( 'name' => __( 'Product Categories', 'woothemes' ),
                    'type' => 'subheading' );

$options[] = array( 'name' => __( 'Number of Product Categories', 'woothemes' ),
                    'desc' => __( 'Select the number of product categories to display on the homepage.', 'woothemes' ),
                    'id' => $shortname . '_homepage_product_categories_limit',
                    'std' => '4',
                    'type' => 'select2',
                    'options' => $woo_numbers
                  );

$options[] = array( 'name' => __( 'Featured Products', 'woothemes' ),
                    'type' => 'subheading' );

$options[] = array( 'name' => __( 'Number of Products', 'woothemes' ),
                    'desc' => __( 'Select the number of featured products to display on the homepage.', 'woothemes' ),
                    'id' => $shortname . '_homepage_featured_products_limit',
                    'std' => '4',
                    'type' => 'select2',
                    'options' => $woo_numbers
                  );

$options[] = array( 'name' => __( 'Recent Products', 'woothemes' ),
                    'type' => 'subheading' );

$options[] = array( 'name' => __( 'Title', 'woothemes' ),
                    'desc' => __( 'Enter the title to display above the recent products on the homepage.', 'woothemes' ),
                    'id' => $shortname . '_homepage_recent_products_title',
                    'std' => '',
                    'type' => 'text' );

$options[] = array( 'name' => __( 'Number of Products', 'woothemes' ),
                    'desc' => __( 'Select the number of recent products to display on the homepage.', 'woothemes' ),
                    'id' => $shortname . '_homepage_recent_products_limit',
                    'std' => '4',
                    'type' => 'select2',
                    'options' => $woo_numbers
                  );
}

if ( function_exists( 'woothemes_testimonials' ) ) {
$options[] = array( 'name' => __( 'Testimonials', 'woothemes' ),
                    'type' => 'subheading' );

$options[] = array( 'name' => __( 'Number of Testimonials', 'woothemes' ),
                    'desc' => __( 'Select the number of testimonials to display on the homepage.', 'woothemes' ),
                    'id' => $shortname . '_homepage_number_of_testimonials',
                    'std' => '4',
                    'type' => 'select2',
                    'options' => $woo_numbers
                  );

$options[] = array( 'name' => __( 'Title', 'woothemes' ),
                    'desc' => __( 'Enter the title to display above the testimonials on the homepage.', 'woothemes' ),
                    'id' => $shortname . '_homepage_testimonials_area_title',
                    'std' => sprintf( __( 'What people think of %s', 'woothemes' ), get_bloginfo( 'name' ) ),
                    'type' => 'text' );
}

$options[] = array( 'name' => __( 'Content Area', 'woothemes' ),
                    'type' => 'subheading' );

$options[] = array( 'name' => __( 'Content Type', 'woothemes' ),
                    'desc' => __( 'Determine whether to display the content of a specified page, or your recent blog posts.', 'woothemes' ),
                    'id' => $shortname . '_homepage_content_type',
                    'std' => 'posts',
                    'type' => 'select2',
                    'options' => array( 'posts' => __( 'Blog Posts', 'woothemes' ), 'page' => __( 'Page Content', 'woothemes' ) )
                  );

$options[] = array( 'name' => __( 'Page Content', 'woothemes' ),
                    'desc' => __( 'Select the page to display content from if the homepage content area is enabled.', 'woothemes' ),
                    'id' => $shortname . '_homepage_page_id',
                    'std' => '',
                    'type' => 'select2',
                    'options' => $woo_pages
                  );

$options[] = array( 'name' => __( 'Number of Blog Posts', 'woothemes' ),
                    'desc' => __( 'Select the number of posts to display if the content type is set to "Blog Posts".', 'woothemes' ),
                    'id' => $shortname . '_homepage_number_of_posts',
                    'std' => '5',
                    'type' => 'select2',
                    'options' => $woo_numbers
                  );

$options[] = array( 'name' => __( 'Posts Category', 'woothemes' ),
                    'desc' => __( 'Optionally select a category of posts to display if the content type is set to "Blog Posts".', 'woothemes' ),
                    'id' => $shortname . '_homepage_posts_category',
                    'std' => '',
                    'type' => 'select2',
                    'options' => $woo_categories
                    );

$options[] = array( 'name' => __( 'Show Sidebar', 'woothemes' ),
                        'desc' => __( 'Display the sidebar on the homepage.', 'woothemes' ),
                        'id' => $shortname.'_homepage_posts_sidebar',
                        'std' => 'true',
                        'type' => 'checkbox' );

/* WooCommerce */

if ( is_woocommerce_activated() ) {
    $options[] = array( 'name' => __( 'WooCommerce', 'woothemes' ),
    					'type' => 'heading',
    					'icon' => 'woocommerce' );

    $options[] = array( 'name' => __( 'General', 'woothemes' ),
    					'type' => 'subheading' );

    $options[] = array( 'name' => __( 'Custom Placeholder', 'woothemes' ),
                        'desc' => __( 'Upload a custom placeholder to be displayed when there is no product image.', 'woothemes' ),
                        'id' => $shortname . '_placeholder_url',
                        'std' => '',
                        'type' => 'upload' );

    $options[] = array( 'name' => __( 'Header Cart Link', 'woothemes' ),
                        'desc' => __( 'Display a link to the cart in the main navigation', 'woothemes' ),
                        'id' => $shortname.'commerce_header_cart_link',
                        'std' => 'true',
                        'type' => 'checkbox' );

    $options[] = array( 'name' => __( 'Header Product Search', 'woothemes' ),
                        'desc' => __( 'Display a product search form in the header', 'woothemes' ),
                        'id' => $shortname.'commerce_header_search_form',
                        'std' => 'true',
                        'type' => 'checkbox' );

    $options[] = array( 'name' => __( 'Distraction free checkout', 'woothemes' ),
                        'desc' => __( 'Hiding distracting elements like the navigation and footer can increase conversions at the checkout. If you enable this option it is recommended that your checkout use the Full Width page template to remove the sidebar as well.', 'woothemes' ),
                        'id' => $shortname.'commerce_hide_nav',
                        'std' => 'false',
                        'type' => 'checkbox' );

    $options[] = array( 'name' => __( 'Product Archives', 'woothemes' ),
                        'type' => 'subheading' );

    $options[] = array( 'name' => __( 'Shop archives full width?', 'woothemes' ),
                        'desc' => __( 'Display the product archive in a full-width single column format? (The sidebar is removed).', 'woothemes' ),
                        'id' => $shortname.'commerce_archives_fullwidth',
                        'std' => 'false',
                        'type' => 'checkbox' );

    $options[] = array( 'name' => __( 'Product columns', 'woothemes' ),
                        'desc' => __( 'Select how many columns of products you want on product archive pages.', 'woothemes' ),
                        'id' => $shortname . 'commerce_product_columns',
                        'std' => '3',
                        'type' => 'select2',
                        'options' => array( '2', '3', '4', '5' ) );

    $options[] = array( 'name' => __( 'Products per page', 'woothemes' ),
    					'desc' => __( 'How many products do you want to display on product archive pages?', 'woothemes' ),
    					'id' => $shortname.'commerce_products_per_page',
    					'std' => '12',
    					'type' => 'text' );

    $options[] = array( 'name' => __( 'Enable infinite scroll?', 'woothemes' ),
                        'desc' => __( 'Automatically loads the next set of products via AJAX when the user scrolls to the bottom of the page', 'woothemes' ),
                        'id' => $shortname.'commerce_archives_infinite_scroll',
                        'std' => 'true',
                        'type' => 'checkbox' );

    $options[] = array( 'name' => __( 'Product Details', 'woothemes' ),
                        'type' => 'subheading' );

    $options[] = array( 'name' => __( 'Display related products', 'woothemes' ),
    					'desc' => __( 'Display related products on the product details page', 'woothemes' ),
    					'id' => $shortname.'commerce_related_products',
    					'std' => 'true',
                        'class' => 'collapsed',
    					'type' => 'checkbox' );

    $options[] = array( 'name' => __( 'Maximum related products', 'woothemes' ),
                        'desc' => __( 'The maximum number of related products to display.', 'woothemes' ),
                        'id' => $shortname . 'commerce_related_products_maximum',
                        'std' => '3',
                        'type' => 'select2',
                        'class' => 'hidden last',
                        'options' => array( '2', '3', '4', '5', '6', '7', '8' ) );

    $options[] = array( 'name' => __( 'Product details pages full width?', 'woothemes' ),
                        'desc' => __( 'Display the product details in a full-width single column format? (The sidebar is removed)' ),
                        'id' => $shortname.'commerce_products_fullwidth',
                        'std' => 'false',
                        'type' => 'checkbox' );

}

/* Dynamic Images */

$options[] = array( 'name' => __( 'Dynamic Images', 'woothemes' ),
    				'type' => 'heading',
    				'icon' => 'image' );

$options[] = array( 'name' => __( 'Resizer Settings', 'woothemes' ),
    				'type' => 'subheading' );

$options[] = array( 'name' => __( 'Dynamic Image Resizing', 'woothemes' ),
    				'desc' => '',
    				'id' => $shortname . '_wpthumb_notice',
    				'std' => __( 'There are two alternative methods of dynamically resizing the thumbnails in the theme, <strong>WP Post Thumbnail</strong> or <strong>TimThumb - Custom Settings panel</strong>. We recommend using WP Post Thumbnail option.', 'woothemes' ),
    				'type' => 'info' );

$options[] = array( 'name' => __( 'WP Post Thumbnail', 'woothemes' ),
    				'desc' => __( 'Use WordPress post thumbnail to assign a post thumbnail. Will enable the <strong>Featured Image panel</strong> in your post sidebar where you can assign a post thumbnail.', 'woothemes' ),
    				'id' => $shortname . '_post_image_support',
    				'std' => 'true',
    				'class' => 'collapsed',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'WP Post Thumbnail - Dynamic Image Resizing', 'woothemes' ),
    				'desc' => __( 'The post thumbnail will be dynamically resized using native WP resize functionality. <em>(Requires PHP 5.2+)</em>', 'woothemes' ),
    				'id' => $shortname . '_pis_resize',
    				'std' => 'true',
    				'class' => 'hidden',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'WP Post Thumbnail - Hard Crop', 'woothemes' ),
    				'desc' => __( 'The post thumbnail will be cropped to match the target aspect ratio (only used if "Dynamic Image Resizing" is enabled).', 'woothemes' ),
    				'id' => $shortname . '_pis_hard_crop',
    				'std' => 'true',
    				'class' => 'hidden last',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'TimThumb - Custom Settings Panel', 'woothemes' ),
    				'desc' => sprintf( __( 'This will enable the %1$s (thumb.php) script which dynamically resizes images added through the <strong>custom settings panel below the post</strong>. Make sure your themes <em>cache</em> folder is writable. %2$s', 'woothemes' ), '<a href="http://code.google.com/p/timthumb/">TimThumb</a>', '<a href="http://www.woothemes.com/2008/10/troubleshooting-image-resizer-thumbphp/">Need help?</a>' ),
    				'id' => $shortname . '_resize',
    				'std' => 'true',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Automatic Image Thumbnail', 'woothemes' ),
    				'desc' => __( 'If no thumbnail is specifified then the first uploaded image in the post is used.', 'woothemes' ),
    				'id' => $shortname . '_auto_img',
    				'std' => 'false',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Thumbnail Settings', 'woothemes' ),
    				'type' => 'subheading' );

$options[] = array( 'name' => __( 'Thumbnail Image Dimensions', 'woothemes' ),
    				'desc' => __( 'Enter an integer value i.e. 250 for the desired size which will be used when dynamically creating the images.', 'woothemes' ),
    				'id' => $shortname . '_image_dimensions',
    				'std' => '',
    				'type' => array(
    					array(  'id' => $shortname . '_thumb_w',
    						'type' => 'text',
    						'std' => 800,
    						'meta' => __( 'Width', 'woothemes' ) ),
    					array(  'id' => $shortname . '_thumb_h',
    						'type' => 'text',
    						'std' => 300,
    						'meta' => __( 'Height', 'woothemes' ) )
    				) );

$options[] = array( 'name' => __( 'Thumbnail Alignment', 'woothemes' ),
    				'desc' => __( 'Select how to align your thumbnails with posts.', 'woothemes' ),
    				'id' => $shortname . '_thumb_align',
    				'std' => 'aligncenter',
    				'type' => 'select2',
    				'options' => array( 'alignleft' => __( 'Left', 'woothemes' ), 'alignright' => __( 'Right', 'woothemes' ), 'aligncenter' => __( 'Center', 'woothemes' ) ) );

$options[] = array( 'name' => __( 'Single Post - Show Thumbnail', 'woothemes' ),
    				'desc' => __( 'Show the thumbnail in the single post page.', 'woothemes' ),
    				'id' => $shortname . '_thumb_single',
    				'class' => 'collapsed',
    				'std' => 'false',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Single Post - Thumbnail Dimensions', 'woothemes' ),
    				'desc' => __( 'Enter an integer value i.e. 250 for the image size. Max width is 576.', 'woothemes' ),
    				'id' => $shortname . '_image_dimensions',
    				'std' => '',
    				'class' => 'hidden last',
    				'type' => array(
    					array(  'id' => $shortname . '_single_w',
    						'type' => 'text',
    						'std' => 200,
    						'meta' => __( 'Width', 'woothemes' ) ),
    					array(  'id' => $shortname . '_single_h',
    						'type' => 'text',
    						'std' => 200,
    						'meta' => __( 'Height', 'woothemes' ) )
    				) );

$options[] = array( 'name' => __( 'Single Post - Thumbnail Alignment', 'woothemes' ),
    				'desc' => __( 'Select how to align your thumbnail with single posts.', 'woothemes' ),
    				'id' => $shortname . '_thumb_single_align',
    				'std' => 'alignright',
    				'type' => 'select2',
    				'class' => 'hidden',
    				'options' => array( 'alignleft' => __( 'Left', 'woothemes' ), 'alignright' => __( 'Right', 'woothemes' ), 'aligncenter' => __( 'Center', 'woothemes' ) ) );

$options[] = array( 'name' => __( 'Add thumbnail to RSS feed', 'woothemes' ),
    				'desc' => __( 'Add the the image uploaded via your Custom Settings panel to your RSS feed', 'woothemes' ),
    				'id' => $shortname . '_rss_thumb',
    				'std' => 'false',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Enable Lightbox', 'woothemes' ),
    				'desc' => __( 'Enable the PrettyPhoto lighbox script on images within your website\'s content.', 'woothemes' ),
    				'id' => $shortname . '_enable_lightbox',
    				'std' => 'false',
    				'type' => 'checkbox' );

/* Footer */

$options[] = array( 'name' => __( 'Footer Customization', 'woothemes' ),
    				'type' => 'heading',
    				'icon' => 'footer' );

$url =  get_template_directory_uri() . '/functions/images/';
$options[] = array( 'name' => __( 'Footer Widget Areas', 'woothemes' ),
    				'desc' => __( 'Select how many footer widget areas you want to display.', 'woothemes' ),
    				'id' => $shortname . '_footer_sidebars',
    				'std' => '4',
    				'type' => 'images',
    				'options' => array(
    					'0' => $url . 'layout-off.png',
    					'1' => $url . 'footer-widgets-1.png',
    					'2' => $url . 'footer-widgets-2.png',
    					'3' => $url . 'footer-widgets-3.png',
    					'4' => $url . 'footer-widgets-4.png' )
    				);

$options[] = array( 'name' => __( 'Custom Affiliate Link', 'woothemes' ),
    				'desc' => __( 'Add an affiliate link to the WooThemes logo in the footer of the theme.', 'woothemes' ),
    				'id' => $shortname . '_footer_aff_link',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'Enable Custom Footer (Left)', 'woothemes' ),
    				'desc' => __( 'Activate to add the custom text below to the theme footer.', 'woothemes' ),
    				'id' => $shortname . '_footer_left',
    				'std' => 'false',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Custom Text (Left)', 'woothemes' ),
    				'desc' => __( 'Custom HTML and Text that will appear in the footer of your theme.', 'woothemes' ),
    				'id' => $shortname . '_footer_left_text',
    				'std' => '',
    				'type' => 'textarea' );

$options[] = array( 'name' => __( 'Enable Custom Footer (Right)', 'woothemes' ),
    				'desc' => __( 'Activate to add the custom text below to the theme footer.', 'woothemes' ),
    				'id' => $shortname . '_footer_right',
    				'std' => 'false',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Custom Text (Right)', 'woothemes' ),
    				'desc' => __( 'Custom HTML and Text that will appear in the footer of your theme.', 'woothemes' ),
    				'id' => $shortname . '_footer_right_text',
    				'std' => '',
    				'type' => 'textarea' );

/* Subscribe & Connect */

$options[] = array( 'name' => __( 'Subscribe & Connect', 'woothemes' ),
    				'type' => 'heading',
    				'icon' => 'connect' );

$options[] = array( 'name' => __( 'Setup', 'woothemes' ),
    				'type' => 'subheading' );

$options[] = array( 'name' => __( 'Enable Subscribe & Connect - Single Post', 'woothemes' ),
    				'desc' => sprintf( __( 'Enable the subscribe & connect area on single posts. You can also add this as a %1$s in your sidebar.', 'woothemes' ), '<a href="' . esc_url( home_url() ) . '/wp-admin/widgets.php">widget</a>' ),
    				'id' => $shortname . '_connect',
    				'std' => 'false',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Subscribe Title', 'woothemes' ),
    				'desc' => __( 'Enter the title to show in your subscribe & connect area.', 'woothemes' ),
    				'id' => $shortname . '_connect_title',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'Text', 'woothemes' ),
    				'desc' => __( 'Change the default text in this area.', 'woothemes' ),
    				'id' => $shortname . '_connect_content',
    				'std' => '',
    				'type' => 'textarea' );

$options[] = array( 'name' => __( 'Enable Related Posts', 'woothemes' ),
    				'desc' => __( 'Enable related posts in the subscribe area. Uses posts with the same <strong>tags</strong> to find related posts. Note: Will not show in the Subscribe widget.', 'woothemes' ),
    				'id' => $shortname . '_connect_related',
    				'std' => 'true',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Subscribe Settings', 'woothemes' ),
    				'type' => 'subheading' );

$options[] = array( 'name' => __( 'Subscribe By E-mail ID (Feedburner)', 'woothemes' ),
    				'desc' => sprintf( __( 'Enter your %1$s for the e-mail subscription form.', 'woothemes' ), '<a href="http://www.woothemes.com/tutorials/how-to-find-your-feedburner-id-for-email-subscription/">'.__( 'Feedburner ID', 'woothemes' ).'</a>' ),
    				'id' => $shortname . '_connect_newsletter_id',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'Subscribe By E-mail to MailChimp', 'woothemes', 'woothemes' ),
    				'desc' => sprintf( __( 'If you have a MailChimp account you can enter the %1$s to allow your users to subscribe to a MailChimp List.', 'woothemes' ), '<a href="http://woochimp.heroku.com" target="_blank">'.__( 'MailChimp List Subscribe URL', 'woothemes' ).'</a>' ),
    				'id' => $shortname . '_connect_mailchimp_list_url',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'Connect Settings', 'woothemes' ),
    				'type' => 'subheading' );

$options[] = array( 'name' => __( 'Enable RSS', 'woothemes' ),
    				'desc' => __( 'Enable the subscribe and RSS icon.', 'woothemes' ),
    				'id' => $shortname . '_connect_rss',
    				'std' => 'true',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Twitter URL', 'woothemes' ),
    				'desc' => sprintf( __( 'Enter your %1$s URL e.g. http://www.twitter.com/woothemes', 'woothemes' ), '<a href="http://www.twitter.com/">'.__( 'Twitter', 'woothemes' ).'</a>' ),
    				'id' => $shortname . '_connect_twitter',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'Facebook URL', 'woothemes' ),
    				'desc' => sprintf( __( 'Enter your %1$s URL e.g. http://www.facebook.com/woothemes', 'woothemes' ), '<a href="http://www.facebook.com/">'.__( 'Facebook', 'woothemes' ).'</a>' ),
    				'id' => $shortname . '_connect_facebook',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'YouTube URL', 'woothemes' ),
    				'desc' => sprintf( __( 'Enter your %1$s URL e.g. http://www.youtube.com/woothemes', 'woothemes' ), '<a href="http://www.youtube.com/">'.__( 'YouTube', 'woothemes' ).'</a>' ),
    				'id' => $shortname . '_connect_youtube',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'Flickr URL', 'woothemes' ),
    				'desc' => sprintf( __( 'Enter your %1$s URL e.g. http://www.flickr.com/woothemes', 'woothemes' ), '<a href="http://www.flickr.com/">'.__( 'Flickr', 'woothemes' ).'</a>' ),
    				'id' => $shortname . '_connect_flickr',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'LinkedIn URL', 'woothemes' ),
    				'desc' => sprintf( __( 'Enter your %1$s URL e.g. http://www.linkedin.com/in/woothemes', 'woothemes' ), '<a href="http://www.www.linkedin.com.com/">'.__( 'LinkedIn', 'woothemes' ).'</a>' ),
    				'id' => $shortname . '_connect_linkedin',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'Delicious URL', 'woothemes' ),
    				'desc' => sprintf( __( 'Enter your %1$s URL e.g. http://www.delicious.com/woothemes', 'woothemes' ), '<a href="http://www.delicious.com/">'.__( 'Delicious', 'woothemes' ).'</a>' ),
    				'id' => $shortname . '_connect_delicious',
    				'std' => '',
    				'type' => 'text' );

$options[] = array( 'name' => __( 'Google+ URL', 'woothemes' ),
    				'desc' => sprintf( __( 'Enter your %1$s URL e.g. https://plus.google.com/104560124403688998123/', 'woothemes' ), '<a href="http://plus.google.com/">'.__( 'Google+', 'woothemes' ).'</a>' ),
    				'id' => $shortname . '_connect_googleplus',
    				'std' => '',
    				'type' => 'text' );

/* Advertising */

$options[] = array( 'name' => __( 'Advertising', 'woothemes' ),
    				'type' => 'heading',
    				'icon' => 'ads' );

$options[] = array( 'name' => __( 'Top Ad (468x60px)', 'woothemes' ),
    				'type' => 'subheading' );

$options[] = array( 'name' => __( 'Enable Ad', 'woothemes' ),
    				'desc' => __( 'Enable the ad space', 'woothemes' ),
    				'id' => $shortname . '_ad_top',
    				'std' => 'false',
    				'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Adsense code', 'woothemes' ),
    				'desc' => __( 'Enter your adsense code (or other ad network code) here.', 'woothemes' ),
    				'id' => $shortname . '_ad_top_adsense',
    				'std' => '',
    				'type' => 'textarea' );

$options[] = array( 'name' => __( 'Image Location', 'woothemes' ),
    				'desc' => __( 'Enter the URL to the banner ad image location.', 'woothemes' ),
    				'id' => $shortname . '_ad_top_image',
    				'std' => 'http://www.woothemes.com/ads/468x60b.jpg',
    				'type' => 'upload' );

$options[] = array( 'name' => __( 'Destination URL', 'woothemes' ),
    				'desc' => __( 'Enter the URL where this banner ad points to.', 'woothemes' ),
    				'id' => $shortname . '_ad_top_url',
    				'std' => 'http://www.woothemes.com',
    				'type' => 'text' );

/* Contact Template Settings */

$options[] = array( 'name' => __( 'Contact Page', 'woothemes' ),
					'icon' => 'maps',
				    'type' => 'heading');

$options[] = array( 'name' => __( 'Contact Information', 'woothemes' ),
					'type' => 'subheading');

$options[] = array( 'name' => __( 'Enable Contact Information Panel', 'woothemes' ),
					'desc' => __( 'Enable the contact informal panel', 'woothemes' ),
					'id' => $shortname.'_contact_panel',
					'std' => 'false',
					'class' => 'collapsed',
					'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Location Name', 'woothemes' ),
					'desc' => __( 'Enter the location name. Example: London Office', 'woothemes' ),
					'id' => $shortname . '_contact_title',
					'std' => '',
					'class' => 'hidden',
					'type' => 'text' );

$options[] = array( 'name' => __( 'Location Address', 'woothemes' ),
					'desc' => __( "Enter your company's address", 'woothemes' ),
					'id' => $shortname . '_contact_address',
					'std' => '',
					'class' => 'hidden',
					'type' => 'textarea' );

$options[] = array( 'name' => __( 'Telephone', 'woothemes' ),
					'desc' => __( 'Enter your telephone number', 'woothemes' ),
					'id' => $shortname . '_contact_number',
					'std' => '',
					'class' => 'hidden',
					'type' => 'text' );

$options[] = array( 'name' => __( 'Fax', 'woothemes' ),
					'desc' => __( 'Enter your fax number', 'woothemes' ),
					'id' => $shortname . '_contact_fax',
					'std' => '',
					'class' => 'hidden last',
					'type' => 'text' );

$options[] = array( 'name' => __( 'Contact Form E-Mail', 'woothemes' ),
					'desc' => __( "Enter your E-mail address to use on the 'Contact Form' page Template.", 'woothemes' ),
					'id' => $shortname.'_contactform_email',
					'std' => '',
					'type' => 'text' );

$options[] = array( 'name' => __( 'Your Twitter username', 'woothemes' ),
					'desc' => __( 'Enter your Twitter username. Example: woothemes', 'woothemes' ),
					'id' => $shortname . '_contact_twitter',
					'std' => '',
					'type' => 'text' );

$options[] = array( 'name' => __( 'Enable Subscribe and Connect', 'woothemes' ),
					'desc' => __( 'Enable the subscribe and connect functionality on the contact page template', 'woothemes' ),
					'id' => $shortname.'_contact_subscribe_and_connect',
					'std' => 'false',
					'type' => 'checkbox' );

$options[] = array( 'name' => __( 'Maps', 'woothemes' ),
					'type' => 'subheading');

$options[] = array( 'name' => __( 'Contact Form Google Maps Coordinates', 'woothemes' ),
					'desc' => sprintf( __( 'Enter your Google Map coordinates to display a map on the Contact Form page template and a link to it on the Contact Us widget. You can get these details from %1$s', 'woothemes' ), '<a href="http://itouchmap.com/latlong.html" target="_blank">'.__( 'Google Maps', 'woothemes' ).'</a>' ),
					'id' => $shortname . '_contactform_map_coords',
					'std' => '',
					'type' => 'text' );

$options[] = array( 'name' => __( 'Disable Mousescroll', 'woothemes' ),
					'desc' => __( 'Turn off the mouse scroll action for all the Google Maps on the site. This could improve usability on your site.', 'woothemes' ),
					'id' => $shortname . '_maps_scroll',
					'std' => '',
					'type' => 'checkbox');

$options[] = array( 'name' => __( 'Map Height', 'woothemes' ),
					'desc' => __( 'Height in pixels for the maps displayed on Single.php pages.', 'woothemes' ),
					'id' => $shortname . '_maps_single_height',
					'std' => '250',
					'type' => 'text');

$options[] = array( 'name' => __( 'Default Map Zoom Level', 'woothemes' ),
					'desc' => __( 'Set this to adjust the default in the post & page edit backend.', 'woothemes' ),
					'id' => $shortname . '_maps_default_mapzoom',
					'std' => '9',
					'type' => 'select2',
					'options' => $other_entries);

$options[] = array( 'name' => __( 'Default Map Type', 'woothemes' ),
					'desc' => __( 'Set this to the default rendered in the post backend.', 'woothemes' ),
					'id' => $shortname . '_maps_default_maptype',
					'std' => 'G_NORMAL_MAP',
					'type' => 'select2',
					'options' => array( 'G_NORMAL_MAP' => __( 'Normal', 'woothemes' ), 'G_SATELLITE_MAP' => __( 'Satellite', 'woothemes' ),'G_HYBRID_MAP' => __( 'Hybrid', 'woothemes' ), 'G_PHYSICAL_MAP' => __( 'Terrain', 'woothemes' ) ) );

$options[] = array( 'name' => __( 'Map Callout Text', 'woothemes' ),
					'desc' => __( 'Text or HTML that will be output when you click on the map marker for your location.', 'woothemes' ),
					'id' => $shortname . '_maps_callout_text',
					'std' => '',
					'type' => 'textarea');

// Add extra options through function
if ( function_exists( 'woo_options_add') )
	$options = woo_options_add($options);

if ( get_option( 'woo_template') != $options) update_option( 'woo_template',$options);
if ( get_option( 'woo_themename') != $themename) update_option( 'woo_themename',$themename);
if ( get_option( 'woo_shortname') != $shortname) update_option( 'woo_shortname',$shortname);
if ( get_option( 'woo_manual') != $manualurl) update_option( 'woo_manual',$manualurl);

// Woo Metabox Options
// Start name with underscore to hide custom key from the user
global $post;
$woo_metaboxes = array();

// Shown on both posts and pages


// Show only on specific post types or page

if ( ( get_post_type() == 'post') || ( !get_post_type() ) ) {

	// TimThumb is enabled in options
	if ( get_option( 'woo_resize') == 'true' ) {

		$woo_metaboxes[] = array (	'name' => 'image',
									'label' => __( 'Image', 'woothemes' ),
									'type' => 'upload',
									'desc' => __( 'Upload an image or enter an URL.', 'woothemes' ) );

		$woo_metaboxes[] = array (	'name' => '_image_alignment',
									'std' => __( 'Center', 'woothemes' ),
									'label' => __( 'Image Crop Alignment', 'woothemes' ),
									'type' => 'select2',
									'desc' => __( 'Select crop alignment for resized image', 'woothemes' ),
									'options' => array(	'c' => 'Center',
														't' => 'Top',
														'b' => 'Bottom',
														'l' => 'Left',
														'r' => 'Right'));
	// TimThumb disabled in the options
	} else {

		$woo_metaboxes[] = array (	'name' => '_timthumb-info',
									'label' => __( 'Image', 'woothemes' ),
									'type' => 'info',
									'desc' => sprintf( __( '%1$s is disabled. Use the %2$s panel in the sidebar instead, or enable TimThumb in the options panel.', 'woothemes' ), '<strong>'.__( 'TimThumb', 'woothemes' ).'</strong>', '<strong>'.__( 'Featured Image', 'woothemes' ).'</strong>' ) ) ;

	}

	$woo_metaboxes[] = array (  'name'  => 'embed',
					            'std'  => '',
					            'label' => __( 'Embed Code', 'woothemes' ),
					            'type' => 'textarea',
					            'desc' => __( 'Enter the video embed code for your video (YouTube, Vimeo or similar)', 'woothemes' ) );

} // End post

$woo_metaboxes[] = array (	'name' => '_layout',
							'std' => 'normal',
							'label' => __( 'Layout', 'woothemes' ),
							'type' => 'images',
							'desc' => __( 'Select the layout you want on this specific post/page.', 'woothemes' ),
							'options' => array(
										'layout-default' => $url . 'layout-off.png',
										'layout-full' => get_template_directory_uri() . '/functions/images/' . '1c.png',
										'layout-left-content' => get_template_directory_uri() . '/functions/images/' . '2cl.png',
										'layout-right-content' => get_template_directory_uri() . '/functions/images/' . '2cr.png'));


if ( get_post_type() == 'slide' || ! get_post_type() ) {
        $woo_metaboxes[] = array (
                                    'name' => 'url',
                                    'label' => __( 'Slide URL', 'woothemes' ),
                                    'type' => 'text',
                                    'desc' => sprintf( __( 'Enter an URL to link the slider title to a page e.g. %s (optional)', 'woothemes' ), 'http://yoursite.com/pagename/' )
                                    );

        $woo_metaboxes[] = array (
                                    'name'  => 'embed',
                                    'std'  => '',
                                    'label' => __( 'Embed Code', 'woothemes' ),
                                    'type' => 'textarea',
                                    'desc' => __( 'Enter the video embed code for your video (YouTube, Vimeo or similar)', 'woothemes' )
                                    );
} // End Slide

// Add extra metaboxes through function
if ( function_exists( 'woo_metaboxes_add' ) )
	$woo_metaboxes = woo_metaboxes_add( $woo_metaboxes );

if ( get_option( 'woo_custom_template' ) != $woo_metaboxes) update_option( 'woo_custom_template', $woo_metaboxes );

} // END woo_options()
} // END function_exists()

// Add options to admin_head
add_action( 'admin_head', 'woo_options' );

//Enable WooSEO on these Post types
$seo_post_types = array( 'post', 'page' );
define( 'SEOPOSTTYPES', serialize( $seo_post_types ));

//Global options setup
add_action( 'init', 'woo_global_options' );
function woo_global_options(){
	// Populate WooThemes option in array for use in theme
	global $woo_options;
	$woo_options = get_option( 'woo_options' );
}