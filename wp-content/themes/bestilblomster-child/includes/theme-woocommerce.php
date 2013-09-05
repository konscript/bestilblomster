<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $woo_options;

/*-----------------------------------------------------------------------------------*/
/* This theme supports WooCommerce, woo! */
/*-----------------------------------------------------------------------------------*/

add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
	add_theme_support( 'woocommerce' );
}

// Disable WooCommerce styles
if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) >= 0 ) {
	// WooCommerce 2.1 or above is active
	add_filter( 'woocommerce_enqueue_styles', '__return_false' );
} else {
	// WooCommerce is less than 2.1
	define( 'WOOCOMMERCE_USE_CSS', false );
}

// Remove default review stuff - the theme overrides it
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

// Load WooCommerce stylsheet
if ( ! is_admin() ) { add_action( 'wp_enqueue_scripts', 'woo_load_woocommerce_css', 20 ); }

if ( ! function_exists( 'woo_load_woocommerce_css' ) ) {
	function woo_load_woocommerce_css () {
		wp_register_style( 'woocommerce', esc_url( get_template_directory_uri() . '/css/woocommerce.css' ) );
		wp_enqueue_style( 'woocommerce' );
	} // End woo_load_woocommerce_css()
}

/*-----------------------------------------------------------------------------------*/
/* Products */
/*-----------------------------------------------------------------------------------*/

// Number of columns on product archives
add_filter( 'loop_shop_columns', 'wooframework_loop_columns' );
if ( ! function_exists( 'wooframework_loop_columns' ) ) {
	function wooframework_loop_columns() {
		global $woo_options;
		if ( ! isset( $woo_options['woocommerce_product_columns'] ) ) {
			$cols = 3;
		} else {
			$cols = $woo_options['woocommerce_product_columns'] + 2;
		}
		return $cols;
	} // End wooframework_loop_columns()
}

// Number of products per page
add_filter( 'loop_shop_per_page', 'wooframework_products_per_page' );

if ( ! function_exists( 'wooframework_products_per_page' ) ) {
	function wooframework_products_per_page() {
		global $woo_options;
		if ( isset( $woo_options['woocommerce_products_per_page'] ) ) {
			return $woo_options['woocommerce_products_per_page'];
		}
	} // End wooframework_products_per_page()
}

// Add the image wrap
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_product_thumbnail_wrap_open', 5, 2);
add_action( 'woocommerce_before_subcategory_title', 'woocommerce_product_thumbnail_wrap_open', 5, 2);

if (!function_exists('woocommerce_product_thumbnail_wrap_open')) {
	function woocommerce_product_thumbnail_wrap_open() {
		echo '<div class="img-wrap">';
	}
}

// Close image wrap
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_product_thumbnail_wrap_close', 15, 2);
add_action( 'woocommerce_before_subcategory_title', 'woocommerce_product_thumbnail_wrap_close', 15, 2);
if (!function_exists('woocommerce_product_thumbnail_wrap_close')) {
	function woocommerce_product_thumbnail_wrap_close() {
		echo '<span class="details-link"></span>';
		echo '</div> <!--/.wrap-->';
	}
}

// Move the price inside the img-wrap
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_price', 12 );

// Display product categories in the loop
add_action( 'woocommerce_after_shop_loop_item', 'superstore_product_loop_categories', 2 );

if (!function_exists('superstore_product_loop_categories')) {
	function superstore_product_loop_categories() {
		global $post;
		$terms_as_text = get_the_term_list( $post->ID, 'product_cat', '', ', ', '' );
		if ( ! is_product_category() ) {
			echo '<div class="categories">' . $terms_as_text . '</div>';
		}
	}
}

// display out-of-stock on product archive
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_stock', 10);
function woocommerce_template_loop_stock() {
	global $product;
 		if ( ! $product->managing_stock() && ! $product->is_in_stock() )
 		echo '<p class="stock out-of-stock">' . __( 'Out of stock', 'woothemes' ) . '</p>';
}

/*-----------------------------------------------------------------------------------*/
/* Single Product */
/*-----------------------------------------------------------------------------------*/

// Move tabs next to gallery
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 34 );

// Move short description into long description tab
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );

// Overwrite default tabs
add_filter( 'woocommerce_product_tabs', 'woo_overwrite_tabs', 11 );

function woo_overwrite_tabs( $tabs ) {
	unset( $tabs['reviews'] );
	unset( $tabs['description'] );
	// description tab shouldn't check for content
	$tabs['description'] = array(
		'title'    => __( 'Description', 'woocommerce' ),
		'priority' => 10,
		'callback' => 'woocommerce_product_description_tab'
		);
	return $tabs;
}

add_action( 'woocommerce_after_single_product_summary', 'superstore_product_reviews', 17 );
function superstore_product_reviews() {
	global $post;

	if ( ! comments_open() )
		return;

	$comments = get_comments(array(
		'post_id' => $post->ID,
		'status' => 'approve'
	));

	//if ( sizeof( $comments ) > 0 ) {

		comments_template();

	//}
}

// Display related products?
add_action( 'wp_head','wooframework_related_products' );
if ( ! function_exists( 'wooframework_related_products' ) ) {
	function wooframework_related_products() {
		global $woo_options;
		if ( isset( $woo_options['woocommerce_related_products'] ) &&  'false' == $woo_options['woocommerce_related_products'] ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
		}
	} // End wooframework_related_products()
}

if ( ! function_exists('woocommerce_output_related_products') && version_compare( WOOCOMMERCE_VERSION, "2.1" ) < 0 ) {
	function woocommerce_output_related_products() {
			// Display related products in correct layout.
			global $woo_options, $post;
			$single_layout = get_post_meta( $post->ID, '_layout', true );
			$products_max = $woo_options['woocommerce_related_products_maximum'] + 2;
			if ( $woo_options[ 'woocommerce_products_fullwidth' ] == 'true' && ( $single_layout != 'layout-left-content' && $single_layout != 'layout-right-content' ) ) {
				$products_cols = 4;
			} else {
				$products_cols = 3;
			}
		    woocommerce_related_products( $products_max, $products_cols );
	}
}

add_filter( 'woocommerce_output_related_products_args', 'superstore_related_products' );
function superstore_related_products() {
	global $woo_options, $post;
	$single_layout = get_post_meta( $post->ID, '_layout', true );
	$products_max = $woo_options['woocommerce_related_products_maximum'] + 2;
	if ( $woo_options[ 'woocommerce_products_fullwidth' ] == 'true' && ( $single_layout != 'layout-left-content' && $single_layout != 'layout-right-content' ) ) {
		$products_cols = 4;
	} else {
		$products_cols = 3;
	}
	$args = array(
		'posts_per_page' => $products_max,
		'columns'        => $products_cols,
	);
	return $args;
}

// Upsells
if ( ! function_exists( 'woo_upsell_display' ) ) {
	function woo_upsell_display() {
	    // Display up sells in correct layout.
		global $woo_options, $post;
		$single_layout = get_post_meta( $post->ID, '_layout', true );

		if ( $woo_options[ 'woocommerce_products_fullwidth' ] == 'true' && ( $single_layout != 'layout-left-content' && $single_layout != 'layout-right-content' ) ) {
			$products_cols = 4;
		} else {
			$products_cols = 3;
		}
	    woocommerce_upsell_display( -1, $products_cols );
	}
}

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
add_action( 'woocommerce_after_single_product_summary', 'woo_upsell_display', 15 );


// Custom place holder
add_filter( 'woocommerce_placeholder_img_src', 'wooframework_wc_placeholder_img_src' );

if ( ! function_exists( 'wooframework_wc_placeholder_img_src' ) ) {
	function wooframework_wc_placeholder_img_src( $src ) {
		global $woo_options;
		if ( isset( $woo_options['woo_placeholder_url'] ) && '' != $woo_options['woo_placeholder_url'] ) {
			$src = $woo_options['woo_placeholder_url'];
		}
		else {
			$src = get_template_directory_uri() . '/images/wc-placeholder.gif';
		}
		return esc_url( $src );
	} // End wooframework_wc_placeholder_img_src()
}

// If theme lightbox is enabled, disable the WooCommerce lightbox and make product images prettyPhoto galleries
add_action( 'wp_footer', 'woocommerce_prettyphoto' );
function woocommerce_prettyphoto() {
	global $woo_options;
	if ( $woo_options[ 'woo_enable_lightbox' ] == "true" ) {
		update_option( 'woocommerce_enable_lightbox', false );
		?>
			<script>
				jQuery(document).ready(function(){
					jQuery('.images a').attr('rel', 'prettyPhoto[product-gallery]');
				});
			</script>
		<?php
	}
}

// Display 40 images in galleries on single pages (to remove unnecessary last class)
add_filter( 'woocommerce_product_thumbnails_columns', 'woocommerce_custom_product_thumbnails_columns' );

if (!function_exists('woocommerce_custom_product_thumbnails_columns')) {
	function woocommerce_custom_product_thumbnails_columns() {
		return 40;
	}
}

// Display the ratings in the loop and on the single page
add_action( 'woocommerce_after_shop_loop_item', 'superstore_product_rating_overview', 9 );
add_action( 'woocommerce_single_product_summary', 'superstore_single_product_rating_overview', 32 );

if (!function_exists('superstore_product_rating_overview')) {
	function superstore_product_rating_overview() {
		global $product;
		$review_total = get_comments_number();
		if ( $review_total > 0 && get_option( 'woocommerce_enable_review_rating' ) !== 'no' ) {
			echo '<div class="rating-wrap">';
				echo '<a href="' . get_permalink() . '#reviews">';
					echo $product->get_rating_html();
					echo '<span class="review-count">';
						comments_number( '', __('1 review', 'woothemes'), __('% reviews', 'woothemes') );
					echo '</span>';
				echo '</a>';
			echo '</div>';
		}
	}
}

if (!function_exists('superstore_single_product_rating_overview')) {
	function superstore_single_product_rating_overview() {
		global $product;
		$review_total = get_comments_number();
		if ( $review_total > 0 && get_option( 'woocommerce_enable_review_rating' ) !== 'no' ) {
			echo '<div class="rating-wrap">';
				echo $product->get_rating_html();
				echo '<span class="review-count"><a href="#reviews">';
					comments_number( '', __('1 review', 'woothemes'), __('% reviews', 'woothemes') );
				echo '</a></span>';
			echo '</div>';
		}
	}
}

// Change the add to cart text
add_filter('add_to_cart_text', 'superstore_custom_cart_button_text');

function superstore_custom_cart_button_text() {
    return __('Add', 'woothemes');
}


/*-----------------------------------------------------------------------------------*/
/* Layout */
/*-----------------------------------------------------------------------------------*/

// Adjust markup on all woocommerce pages
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', 'woocommerce_theme_before_content', 10 );
add_action( 'woocommerce_after_main_content', 'woocommerce_theme_after_content', 20 );

if ( ! function_exists( 'woocommerce_theme_before_content' ) ) {
	function woocommerce_theme_before_content() {
		global $woo_options;
		if ( ! isset( $woo_options['woocommerce_product_columns'] ) ) {
			$columns = 'woocommerce-columns-3';
		} else {
			$columns = 'woocommerce-columns-' . ( $woo_options['woocommerce_product_columns'] + 2 );
		}
		?>
		<!-- #content Starts -->
		<?php woo_content_before(); ?>
	    <div id="content" class="col-full <?php echo esc_attr( $columns ); ?>">

	        <!-- #main Starts -->
	        <?php woo_main_before(); ?>
	        <div id="main" class="col-left">

	    <?php
	} // End woocommerce_theme_before_content()
}


if ( ! function_exists( 'woocommerce_theme_after_content' ) ) {
	function woocommerce_theme_after_content() {
		?>

			</div><!-- /#main -->
	        <?php woo_main_after(); ?>
	        <?php do_action( 'woocommerce_sidebar' ); ?>

	    </div><!-- /#content -->
		<?php woo_content_after(); ?>
	    <?php
	} // End woocommerce_theme_after_content()
}

// Header search form
add_action( 'woo_nav_before', 'woocommerce_search_widget', 30 );

function woocommerce_search_widget() {
	global $woo_options;
	if ( isset( $woo_options['woocommerce_header_search_form'] ) && 'true' == $woo_options['woocommerce_header_search_form'] ) {
		if ( version_compare( WOOCOMMERCE_VERSION, "2.0.0" ) >= 0 ) {
			the_widget('WC_Widget_Product_Search', 'title=' );
		} else {
			the_widget('WooCommerce_Widget_Product_Search', 'title=' );
		}
	}
} // End woocommerce_search_widget()

// Header account
add_action( 'woo_nav_before', 'superstore_user', 40 );
function superstore_user() {
	global $current_user;
	$url_myaccount 		= get_permalink( woocommerce_get_page_id( 'myaccount' ) );
	$url_editaddress 	= get_permalink( woocommerce_get_page_id( 'edit_address' ) );
	if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) >= 0 ) {
		// WooCommerce 2.1 or above is active
		$url_changepass = woocommerce_customer_edit_account_url();
	} else {
		// WooCommerce is less than 2.1
		$url_changepass = get_permalink( woocommerce_get_page_id( 'change_password' ) );
	}
	$url_vieworder 		= get_permalink( woocommerce_get_page_id( 'view_order' ) );

	?>
	<div class="account <?php if ( is_user_logged_in() ) { echo 'logged-in'; } else { echo 'logged-out'; } ?>">
	
		<nav class="account-links">
			<ul>
				<?php if ( woocommerce_get_page_id( 'myaccount' ) !== -1 ) { ?>
					<li class="my-account"><a href="<?php echo $url_myaccount; ?>" class="tiptip" title="<?php if ( is_user_logged_in() ) {  _e('My Account', 'woothemes' ); } else { _e( 'Log In', 'woothemes' ); } ?>"><span><?php if ( is_user_logged_in() ) { _e('My Account', 'woothemes' ); } else { _e( 'Log In', 'woothemes' ); } ?></span></a></li>
				<?php } ?>

				<?php if ( ! is_user_logged_in() && woocommerce_get_page_id( 'myaccount' ) !== -1 && get_option('woocommerce_enable_myaccount_registration')=='yes' ) { ?>
					<li class="register"><a href="<?php echo $url_myaccount; ?>" class="tiptip" title="<?php _e( 'Register', 'woothemes' ); ?>"><span><?php _e( 'Register', 'woothemes' ); ?></span></a></li>
				<?php } ?>

				<?php if ( is_user_logged_in() ) { ?>

					<?php if ( woocommerce_get_page_id( 'edit_address' ) !== -1 ) { ?>
						<li class="edit-address"><a href="<?php echo $url_editaddress; ?>" class="tiptip" title="<?php _e( 'Edit Address', 'woothemes' ); ?>"><span><?php _e( 'Edit Address', 'woothemes' ); ?></span></a></li>
					<?php } ?>

					<li class="edit-password"><a href="<?php echo $url_changepass; ?>" class="tiptip" title="<?php _e( 'Change Password', 'woothemes' ); ?>"><span><?php _e( 'Change Password', 'woothemes' ); ?></span></a></li>

					<?php if ( woocommerce_get_page_id( 'view_order' ) !== -1 ) { ?>
						<li class="logout"><a href="<?php echo wp_logout_url( $_SERVER['REQUEST_URI'] ); ?>" class="tiptip" title="<?php _e( 'Logout', 'woothemes' ); ?>"><span><?php _e( 'Logout', 'woothemes' ); ?></span></a></li>
					<?php } ?>

				<?php } ?>
			</ul>
		</nav>
	<?php

	echo '</div>';
}

// Remove WC breadcrumb (we're using the WooFramework breadcrumb)
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );

// Customise the breadcrumb
add_filter( 'woo_breadcrumbs_args', 'woo_custom_breadcrumbs_args', 10 );

if (!function_exists('woo_custom_breadcrumbs_args')) {
	function woo_custom_breadcrumbs_args ( $args ) {
		$textdomain = 'woothemes';
		$title = get_bloginfo( 'name' );
		$args = array('separator' => ' ', 'before' => '', 'show_home' => __( $title, $textdomain ),);
		return $args;
	} // End woo_custom_breadcrumbs_args()
}


// Remove WC sidebar
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// Add the WC sidebar in the right place and remove it from shop archives if specified
add_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

if ( ! function_exists( 'woocommerce_get_sidebar' ) ) {
	function woocommerce_get_sidebar() {
		global $woo_options, $post;

		// Display the sidebar if full width option is disabled on archives
		if ( ! is_product() ) {
			if ( isset( $woo_options['woocommerce_archives_fullwidth'] ) && 'false' == $woo_options['woocommerce_archives_fullwidth'] ) {
				get_sidebar('shop');
			}
		}

		// Display the sidebar on product details page if the full width option is not enabled.
		$single_layout = get_post_meta( $post->ID, '_layout', true );

		if ( is_product() ) {
			if ( $woo_options[ 'woocommerce_products_fullwidth' ] == 'false' || ( $woo_options[ 'woocommerce_products_fullwidth' ] == 'true' && $single_layout != "" && $single_layout != "layout-full" && $single_layout != "layout-default" ) ) {
				get_sidebar('shop');
			}
		}

	} // End woocommerce_get_sidebar()
}

// Remove pagination (we're using the WooFramework default pagination)
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
add_action( 'woocommerce_after_shop_loop', 'woocommerceframework_pagination', 10 );

if ( ! function_exists( 'woocommerceframework_pagination' ) ) {
function woocommerceframework_pagination() {
	if ( is_search() && is_post_type_archive() ) {
		add_filter( 'woo_pagination_args', 'woocommerceframework_add_search_fragment', 10 );
		add_filter( 'woo_pagination_args_defaults', 'woocommerceframework_woo_pagination_defaults', 10 );
	}
	woo_pagination();
} // End woocommerceframework_pagination()
}

if ( ! function_exists( 'woocommerceframework_add_search_fragment' ) ) {
function woocommerceframework_add_search_fragment ( $settings ) {
	$settings['add_fragment'] = '&post_type=product';

	return $settings;
} // End woocommerceframework_add_search_fragment()
}

if ( ! function_exists( 'woocommerceframework_woo_pagination_defaults' ) ) {
function woocommerceframework_woo_pagination_defaults ( $settings ) {
	$settings['use_search_permastruct'] = false;

	return $settings;
} // End woocommerceframework_woo_pagination_defaults()
}

// Add a class to the body if full width shop archives are specified or if the nav should be hidden
add_filter( 'body_class','wooframework_layout_body_class', 10 );		// Add layout to body_class output
if ( ! function_exists( 'wooframework_layout_body_class' ) ) {
	function wooframework_layout_body_class( $wc_classes ) {
		global $woo_options, $post;

		$layout = '';
		$nav_visibility = '';
		$single_layout = get_post_meta( $post->ID, '_layout', true );

		// Add layout-full class if full width option is enabled
		if ( isset( $woo_options['woocommerce_archives_fullwidth'] ) && 'true' == $woo_options['woocommerce_archives_fullwidth'] && ( is_shop() || is_post_type_archive( 'product' ) || is_tax( get_object_taxonomies( 'product' ) ) ) ) {
			$layout = 'layout-full';
		}
		if ( ( $woo_options[ 'woocommerce_products_fullwidth' ] == "true" && is_product() ) && ( $single_layout != 'layout-left-content' && $single_layout != 'layout-right-content' ) ) {
			$layout = 'layout-full';
		}

		// Add nav-hidden class if specified in theme options
		if ( isset( $woo_options['woocommerce_hide_nav'] ) && 'true' == $woo_options['woocommerce_hide_nav'] && ( is_checkout() ) ) {
			$nav_visibility = 'nav-hidden';
		}

		// Add classes to body_class() output
		$wc_classes[] = $layout;
		$wc_classes[] = $nav_visibility;

		return $wc_classes;
	} // End woocommerce_layout_body_class()
}

add_filter('add_to_cart_fragments', 'header_add_to_cart_fragment');

function header_add_to_cart_fragment( $fragments ) {
	global $woocommerce;

	ob_start();

	superstore_cart_button();

	$fragments['a.cart-contents'] = ob_get_clean();

	return $fragments;

}

function superstore_cart_button() {
	global $woocommerce;
	?>
	<a class="cart-contents" href="<?php echo esc_url( $woocommerce->cart->get_cart_url() ); ?>" title="<?php _e( 'Vis kurv', 'woothemes' ); ?>"><?php echo $woocommerce->cart->get_cart_total(); ?> <span class="contents"><?php echo $woocommerce->cart->cart_contents_count;?></span></a>
	<?php
}

function superstore_mini_cart() {
	global $woocommerce;
	?>

	<ul class="cart">
		<li class="container <?php if ( is_cart() ) echo 'active'; ?>">

       		<?php

       		superstore_cart_button();

       		if ( version_compare( WOOCOMMERCE_VERSION, "2.0.0" ) >= 0 ) {
				the_widget( 'WC_Widget_Cart', 'title=' );
			} else {
				the_widget( 'WooCommerce_Widget_Cart', 'title=' );
			}

       		?>
		</li>
	</ul>

	<script>

    jQuery(function(){
		jQuery('ul.cart a.cart-contents, .added_to_cart').tipTip({
			defaultPosition: "top",
			delay: 0
		});
	});

	</script>

	<?php
}