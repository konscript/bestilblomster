<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
// WooThemes Admin Interface

/*-----------------------------------------------------------------------------------

TABLE OF CONTENTS

- WooThemes Admin Interface - woothemes_add_admin
- WooThemes Reset Function - woo_reset_options
- Framework options panel - woothemes_options_page
- Framework Settings page - woothemes_framework_settings_page
- woo_admin_head
- woo_load_only
- Ajax Save Action - woo_ajax_callback
- Generates The Options - woothemes_machine
- WooThemes Uploader - woothemes_uploader_function
- WooThemes Theme Version Checker - woothemes_version_checker
- WooThemes Thumb Detection Notice - woo_thumb_admin_notice
- WooThemes Theme Update Admin Notice - woo_theme_update_notice

-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_update_options_filter' ) ) {
	function woo_update_options_filter( $new_value, $old_value ) {
		if ( !current_user_can( 'unfiltered_html' ) ) {
			// Options that get KSES'd
			foreach( woo_ksesed_option_keys() as $option ) {
				$new_value[$option] = wp_kses_post( $new_value[$option] );
			}
			// Options that cannot be set without unfiltered HTML
			foreach( woo_disabled_if_not_unfiltered_html_option_keys() as $option ) {
				$new_value[$option] = $old_value[$option];
			}
		}
		return $new_value;
	}
}

if ( ! function_exists( 'woo_prevent_option_update' ) ) {
	function woo_prevent_option_update( $new_value, $old_value ) {
		return $old_value;
	}
}

/**
 * This is the list of options that are run through KSES on save for users without
 * the unfiltered_html capability
 */
if ( ! function_exists( 'woo_ksesed_option_keys' ) ) {
	function woo_ksesed_option_keys() {
		return array();
	}
}

/**
 * This is the list of standalone options that are run through KSES on save for users without
 * the unfiltered_html capability
 */
if ( ! function_exists( 'woo_ksesed_standalone_options' ) ) {
	function woo_ksesed_standalone_options() {
		return array( 'woo_footer_left_text', 'woo_footer_right_text', 'woo_connect_content' );
	}
}

/**
 * This is the list of options that users without the unfiltered_html capability
 * are not able to update
 */
if ( ! function_exists( 'woo_disabled_if_not_unfiltered_html_option_keys' ) ) {
	function woo_disabled_if_not_unfiltered_html_option_keys() {
		return array( 'woo_google_analytics', 'woo_custom_css' );
	}
}

add_filter( 'pre_update_option_woo_options', 'woo_update_options_filter', 10, 2 );
foreach( woo_ksesed_standalone_options() as $o ) {
	add_filter( 'pre_update_option_' . $o, 'wp_kses_post' );
}
unset( $o );

/*-----------------------------------------------------------------------------------*/
/* WooThemes Admin Interface - woothemes_add_admin */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woothemes_add_admin' ) ) {
	function woothemes_add_admin() {

		global $query_string;
		global $current_user;
		$current_user_id = $current_user->user_login;
		$super_user = get_option( 'framework_woo_super_user' );

		$themename =  get_option( 'woo_themename' );
		$shortname =  get_option( 'woo_shortname' );

		// Reset the settings, sanitizing the various requests made.
		// Use a SWITCH to determine which settings to update.

		/* Make sure we're making a request.
   	------------------------------------------------------------*/

		if ( isset( $_REQUEST['page'] ) ) {

			// Sanitize page being requested.
			$_page = '';

			$_page = mysql_real_escape_string( strtolower( trim( strip_tags( $_REQUEST['page'] ) ) ) );

			// Sanitize action being requested.
			$_action = '';

			if ( isset( $_REQUEST['woo_save'] ) ) {

				$_action = mysql_real_escape_string( strtolower( trim( strip_tags( $_REQUEST['woo_save'] ) ) ) );

			} // End IF Statement

			// If the action is "reset", run the SWITCH.

			/* Perform settings reset.
  		------------------------------------------------------------*/

			if ( $_action == 'reset' ) {

				// Add nonce security check.
				if ( function_exists( 'check_ajax_referer' ) ) {
					if ( $_page == 'woothemes_seo' ) {
						check_ajax_referer( 'wooframework-seo-options-reset', '_ajax_nonce' );
					} else {
						check_ajax_referer( 'wooframework-theme-options-reset', '_ajax_nonce' );
					}
				} // End IF Statement

				switch ( $_page ) {

				case 'woothemes':

					$options =  get_option( 'woo_template' );
					woo_reset_options( $options, 'woothemes' );
					header( "Location: admin.php?page=woothemes&reset=true" );
					die;

					break;

				case 'woothemes_framework_settings':

					$options = get_option( 'woo_framework_template' );
					woo_reset_options( $options );
					header( "Location: admin.php?page=woothemes_framework_settings&reset=true" );
					die;

					break;

				case 'woothemes_seo':

					$options = get_option( 'woo_seo_template' );
					woo_reset_options( $options );
					header( "Location: admin.php?page=woothemes_seo&reset=true" );
					die;

					break;

				case 'woothemes_sbm':

					delete_option( 'sbm_woo_sbm_options' );
					header( "Location: admin.php?page=woothemes_sbm&reset=true" );
					die;

					break;

				} // End SWITCH Statement

			} // End IF Statement

		} // End IF Statement

		// Check all the Options, then if the no options are created for a relative sub-page... it's not created.
		if( get_option( 'framework_woo_backend_icon' ) ) { $icon = get_option( 'framework_woo_backend_icon' ); }
		else { $icon = get_template_directory_uri() . '/functions/images/woo-icon.png'; }

		if( function_exists( 'add_object_page' ) ) {
			add_object_page ( 'Page Title', $themename, 'manage_options', 'woothemes', 'woothemes_options_page', $icon );
		} else {
			add_menu_page ( 'Page Title', $themename, 'manage_options', 'woothemes_home', 'woothemes_options_page', $icon );
		}
		$woopage = add_submenu_page( 'woothemes', $themename, __( 'Theme Options', 'woothemes' ), 'manage_options', 'woothemes', 'woothemes_options_page' ); // Default

		// Framework Settings Menu Item
		$wooframeworksettings = '';
		if( $super_user == $current_user_id || empty( $super_user ) ) {
			$wooframeworksettings = add_submenu_page( 'woothemes', __( 'Framework Settings', 'woothemes' ), __( 'Framework Settings', 'woothemes' ), 'manage_options', 'woothemes_framework_settings', 'woothemes_framework_settings_page' );
		}

		// Woothemes Content Builder
		if ( function_exists( 'woothemes_content_builder_menu' ) ) {
			woothemes_content_builder_menu();
		}

		// Update Framework Menu Item
		if( $super_user == $current_user_id || empty( $super_user ) ) {
			$woothemepage = add_submenu_page( 'woothemes', 'WooFramework Update', 'Update Framework', 'manage_options', 'woothemes_framework_update', 'woothemes_framework_update_page' );
		}

		// Add framework functionaily to the head individually
		add_action( "admin_print_scripts-$woopage", 'woo_load_only' );
		add_action( "admin_print_scripts-$wooframeworksettings", 'woo_load_only' );

		// Load Framework CSS Files
		add_action( "admin_print_styles-$woopage", 'woo_framework_load_css' );
		add_action( "admin_print_styles-$wooframeworksettings", 'woo_framework_load_css' );

		// Add the non-JavaScript "save" to the load of each of the screens.
		add_action( "load-$woopage", 'woo_nonajax_callback' );
		add_action( "load-$wooframeworksettings", 'woo_nonajax_callback' );
	}
}

add_action( 'admin_menu', 'woothemes_add_admin', 10 );

/*-----------------------------------------------------------------------------------*/
/* WooThemes Reset Function - woo_reset_options */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_reset_options' ) ) {
	function woo_reset_options( $options, $page = '' ) {

		$excludes = array( 'blogname' , 'blogdescription' );

		foreach( $options as $option ) {

			if( isset( $option['id'] ) ) {
				$option_id = $option['id'];
				$option_type = $option['type'];

				//Skip assigned id's
				if( in_array( $option_id, $excludes ) ) { continue; }

				if( $option_type == 'multicheck' ) {
					foreach( $option['options'] as $option_key => $option_option ) {
						$del = $option_id . "_" . $option_key;
						delete_option( $del );
					}
				} else if( is_array( $option_type ) ) {
						foreach( $option_type as $inner_option ) {
							$option_id = $inner_option['id'];
							$del = $option_id;
							delete_option( $option_id );
						}
					} else {
					delete_option( $option_id );
				}
			}
		}
		//When Theme Options page is reset - Add the woo_options option
		if( $page == 'woothemes' ) {
			delete_option( 'woo_options' );
		}
	}
}

/*-----------------------------------------------------------------------------------*/
/* Framework options panel - woothemes_options_page */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woothemes_options_page' ) ) {
	function woothemes_options_page() {
		global $pagenow;

		$options =  get_option( 'woo_template' );
		$shortname =  get_option( 'woo_shortname' );
		$manualurl =  get_option( 'woo_manual' );

		//GET themes update RSS feed and do magic
		include_once( ABSPATH . WPINC . '/feed.php' );

		$pos = strpos( $manualurl, 'documentation' );
		$theme_slug = str_replace( "/", "", substr( $manualurl, ( $pos + 13 ) ) ); //13 for the word documentation

		//add filter to make the rss read cache clear every 4 hours
		//add_filter( 'wp_feed_cache_transient_lifetime', create_function( '$a', 'return 14400;' ) );
?>
<div class="wrap" id="woo_container">
<?php
	// Custom action at the top of the admin interface.
	$page = '';
	if ( isset( $_GET['page'] ) ) {
		$page = sanitize_user( esc_attr( strip_tags( $_GET['page'] ) ) );
	} 
	do_action( 'wooframework_container_inside' );
	if ( $page != '' ) {
		do_action( 'wooframework_container_inside-' . $page );
	}
?>
<div id="woo-popup-save" class="woo-save-popup"><div class="woo-save-save"><?php _e( 'Options Updated', 'woothemes' ); ?></div></div>
<div id="woo-popup-reset" class="woo-save-popup"><div class="woo-save-reset"><?php _e( 'Options Reset', 'woothemes' ); ?></div></div>
    <form action="" enctype="multipart/form-data" id="wooform" method="post">
    <?php
		// Add nonce for added security.
		if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'wooframework-theme-options-update' ); }

		$woo_nonce = '';

		if ( function_exists( 'wp_create_nonce' ) ) { $woo_nonce = wp_create_nonce( 'wooframework-theme-options-update' ); }

		if ( $woo_nonce == '' ) {} else {
?>
    	<input type="hidden" name="_ajax_nonce" value="<?php echo $woo_nonce; ?>" />
    <?php

		} // End IF Statement
?>
        <div id="header">
           <div class="logo">
				<?php if( get_option( 'framework_woo_backend_header_image' ) ) { ?>
                <img alt="" src="<?php echo esc_url( get_option( 'framework_woo_backend_header_image' ) ); ?>"/>
                <?php } else { ?>
                <img alt="WooThemes" src="<?php echo esc_url( get_template_directory_uri() . '/functions/images/logo.png' ); ?>"/>
                <?php } ?>
            </div>
            <div class="theme-info">
				<?php wooframework_display_theme_version_data(); ?>
			</div>
			<div class="clear"></div>
		</div>
        <?php
		// Rev up the Options Machine
		$return = apply_filters( 'woo_before_option_page', woothemes_machine( $options ) );
?>
		<div id="support-links">
			<ul>
				<li class="changelog"><a title="Theme Changelog" href="<?php echo esc_url( $manualurl ); ?>#Changelog"><?php _e( 'View Changelog', 'woothemes' ); ?></a></li>
				<li class="docs"><a title="Theme Documentation" href="<?php echo esc_url( $manualurl ); ?>"><?php _e( 'View Theme Documentation', 'woothemes' ); ?></a></li>
				<li class="forum"><a href="<?php echo esc_url( 'http://support.woothemes.com/' ); ?>" target="_blank"><?php _e( 'Visit Support Desk', 'woothemes' ); ?></a></li>
                <li class="right"><img style="display:none" src="<?php echo esc_url( get_template_directory_uri() . '/functions/images/loading-top.gif' ); ?>" class="ajax-loading-img ajax-loading-img-top" alt="Working..." /><a href="#" id="expand_options">[+]</a> <input type="submit" value="Save All Changes" class="button submit-button" /></li>
			</ul>
		</div>
        <div id="main">
	    	<?php if ( is_array( $return ) ) { ?>
	    	    <div id="woo-nav">
	    	    	<div id="woo-nav-shadow"></div><!--/#woo-nav-shadow-->
					<?php if ( isset( $return[1] ) ) { ?>
						<ul>
							<?php echo $return[1]; ?>
						</ul>
					<?php } ?>
				</div>
				<div id="content">
	    	    	<?php if ( isset( $return[0] ) ) { echo $return[0]; } /* Settings */ ?>
	    	    </div>
	    	    <div class="clear"></div>
			<?php } else { ?>
				<div id="woo-nav">
	    	    	<div id="woo-nav-shadow"></div><!--/#woo-nav-shadow-->
					<ul>
						<li class="top-level general current">
							<div class="arrow"><div></div></div><span class="icon"></span><a title="General Settings" href="#woo-option-error"><?php _e( 'Error', 'woothemes' ); ?></a>
						</li>
					</ul>
				</div>
				<div id="content">
					<div class="group" id="woo-option-error" style="display: block; ">
						<div class="section section-info">
							<h3 class="heading"><?php _e( 'An Error Occured', 'woothemes' ); ?></h3>
							<div class="option">
								<div class="controls">
									<p><?php _e( 'Something went wrong while trying to load your Theme Options panel.', 'woothemes' ); ?></p>
									<p><?php echo sprintf( __( 'Please reload the page, if this error persists, please get in touch with us through our %1$s.', 'woothemes' ), '<a href="' . esc_url( 'http://support.woothemes.com' ) . '" target="_blank">' . __( 'Support Desk', 'woothemes' ) . '</a>' ); ?></p>
								</div>
								<div class="explain"></div>
								<div class="clear"> </div>
							</div>
						</div>
					</div>
				</div>
				<div class="clear"></div>
			<?php } ?>
        </div>
        <div class="save_bar_top">
        <img style="display:none" src="<?php echo get_template_directory_uri(); ?>/functions/images/loading-bottom.gif" class="ajax-loading-img ajax-loading-img-bottom" alt="Working..." />
        <input type="hidden" name="woo_save" value="save" />
        <input type="submit" value="Save All Changes" class="button submit-button" />
        </form>

        <form action="" method="post" style="display: inline;" id="wooform-reset">
        <?php
		// Add nonce for added security.
		if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'wooframework-theme-options-reset' ); } // End IF Statement

		$woo_nonce = '';

		if ( function_exists( 'wp_create_nonce' ) ) { $woo_nonce = wp_create_nonce( 'wooframework-theme-options-reset' ); } // End IF Statement

		if ( $woo_nonce == '' ) {} else {

?>
	    	<input type="hidden" name="_ajax_nonce" value="<?php echo $woo_nonce; ?>" />
	    <?php

		} // End IF Statement
?>
            <span class="submit-footer-reset">
            <input name="reset" type="submit" value="Reset All Theme Options" class="button submit-button reset-button" onclick="return confirm( 'Click OK to reset all theme options. All settings will be lost!' );" />
            <input type="hidden" name="woo_save" value="reset" />
            </span>
        </form>

        </div>

<div style="clear:both;"></div>
</div><!--wrap-->

 <?php
	} // End woothemes_options_page()
}

/* woo_admin_head()
--------------------------------------------------------------------------------*/

function woo_admin_head() {
?>
		<script type="text/javascript">
			jQuery(document).ready( function() {
				// Create sanitary variable for use in the JavaScript conditional.
				<?php

	$is_reset = 'false';

	if( isset( $_REQUEST['reset'] ) ) {

		$is_reset = $_REQUEST['reset'];

		$is_reset = strtolower( strip_tags( trim( $is_reset ) ) );

	} else {

		$is_reset = 'false';

	} // End IF Statement

?>
			if( '<?php echo esc_js( $is_reset ); ?>' == 'true' ) {

				var reset_popup = jQuery( '#woo-popup-reset' );
				reset_popup.fadeIn();
				window.setTimeout(function() {
					   reset_popup.fadeOut();
					}, 2000);
			}

			//Update Message popup
			jQuery.fn.center = function () {
				this.animate({"top":( jQuery(window).height() - this.height() - 200 ) / 2+jQuery(window).scrollTop() + "px"},100);
				this.css( "left", 250 );
				return this;
			}

			jQuery( '#woo-popup-save' ).center();
			jQuery( '#woo-popup-reset' ).center();
			jQuery(window).scroll(function() {

				jQuery( '#woo-popup-save' ).center();
				jQuery( '#woo-popup-reset' ).center();

			});

			//Save everything else
			jQuery( '#wooform' ).submit(function() {

					function newValues() {
					  var serializedValues = jQuery( "#wooform *").not( '.woo-ignore').serialize();
					  return serializedValues;
					}
					jQuery( ":checkbox, :radio").click(newValues);
					jQuery( "select").change(newValues);
					jQuery( '.ajax-loading-img').fadeIn();
					var serializedReturn = newValues();

					// var ajax_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';

					 //var data = {data : serializedReturn};
					var data = {
						<?php if( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'woothemes' ) { ?>
						type: 'options',
						<?php } ?>
						<?php if( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'woothemes_framework_settings' ) { ?>
						type: 'framework',
						<?php } ?>
						<?php if( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'woothemes_seo' ) { ?>
						type: 'seo',
						<?php } ?>
						<?php if( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'woothemes_tumblog' ) { ?>
						type: 'tumblog',
						<?php } ?>

						action: 'woo_ajax_post_action',
						data: serializedReturn,

						<?php // Nonce Security ?>
						<?php if ( function_exists( 'wp_create_nonce' ) ) { $woo_nonce = wp_create_nonce( 'wooframework-theme-options-update' ); } // End IF Statement ?>

						_ajax_nonce: '<?php echo $woo_nonce; ?>'
					};

					jQuery.post(ajaxurl, data, function(response) {

						var success = jQuery( '#woo-popup-save' );
						var loading = jQuery( '.ajax-loading-img' );
						loading.fadeOut();
						success.fadeIn();
						window.setTimeout(function() {
						   success.fadeOut();
						}, 2000);
					});

					return false;

				});

			});
		</script>
<?php } // End woo_admin_head()

/*-----------------------------------------------------------------------------------*/
/* woo_load_only */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_load_only' ) ) {
	function woo_load_only() {

		add_action( 'admin_head', 'woo_admin_head', 10 );

		wp_register_script( 'jquery-ui-datepicker', get_template_directory_uri() . '/functions/js/ui.datepicker.js', array( 'jquery-ui-core' ) );
		wp_register_script( 'jquery-input-mask', get_template_directory_uri() . '/functions/js/jquery.maskedinput.js', array( 'jquery' ), '1.3' );
		wp_register_script( 'woo-scripts', get_template_directory_uri() . '/functions/js/woo-scripts.js', array( 'jquery' ) );
		wp_register_script( 'woo-admin-interface', get_template_directory_uri() . '/functions/js/woo-admin-interface.js', array( 'jquery' ), '5.3.5' );
		wp_register_script( 'colourpicker', get_template_directory_uri() . '/functions/js/colorpicker.js', array( 'jquery' ) );

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-input-mask' );
		wp_enqueue_script( 'woo-scripts' );
		wp_enqueue_script( 'colourpicker' );
		wp_enqueue_script( 'woo-admin-interface' );
		wp_enqueue_script( 'woo-custom-fields' );
		wp_enqueue_script( 'jquery-ui-slider' );

		// Register the typography preview JavaScript.
		wp_register_script( 'woo-typography-preview', get_template_directory_uri() . '/functions/js/woo-typography-preview.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'woo-typography-preview' );
	} // End woo_load_only()
}

/*-----------------------------------------------------------------------------------*/
/* woo_framework_load_css */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_framework_load_css' ) ) {
	function woo_framework_load_css () {
		wp_register_style( 'woo-admin-interface', get_template_directory_uri() . '/functions/admin-style.css', '', '5.3.10' );
		wp_register_style( 'jquery-ui-datepicker', get_template_directory_uri() . '/functions/css/jquery-ui-datepicker.css' );
		wp_register_style( 'colourpicker', get_template_directory_uri() . '/functions/css/colorpicker.css' );

		wp_enqueue_style( 'woo-admin-interface' );
		wp_enqueue_style( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'colourpicker' );
	} // End woo_framework_load_css()
}

/*-----------------------------------------------------------------------------------*/
/* Default Save Action - woo_options_save */
/*-----------------------------------------------------------------------------------*/

/**
 * woo_options_save()
 *
 * Save options to the database. Moved to a dedicated function.
 *
 * @since V4.6.0
 */

function woo_options_save ( $type, $data ) {
	global $wpdb; // this is how you get access to the database

	$status = false; // We set this to true if the settings have saved successfully.

	$save_type = $type;

	if ( $save_type == 'options' || $save_type == 'seo' || $save_type == 'tumblog' || $save_type == 'framework' ) {

		// Make sure to flush the rewrite rules.
		woo_flush_rewriterules();

		// $data = $_POST['data'];

		if ( is_array( $data ) ) {
			$output = $data; // $output variable used below during save.
		} else {
			parse_str( $data, $output );
		}

		// Remove the "woo_save" item from the output array.
		if ( isset( $output['woo_save'] ) && $output['woo_save'] == 'reset' ) { unset( $output['woo_save'] ); }

		// $data = stripslashes( $data ); // Remove slashes from the serialised string.

		//Pull options
		$options = get_option( 'woo_template' );
		if( $save_type == 'seo' ) {
			$options = get_option( 'woo_seo_template' ); } // Use SEO template on SEO page
		if( $save_type == 'tumblog' ) {
			$options = get_option( 'woo_tumblog_template' ); } // Use Tumblog template on Tumblog page
		if( $save_type == 'framework' ) {
			$options = get_option( 'woo_framework_template' ); } // Use Framework template on Framework Settings page


		foreach( $options as $option_array ) {

			if( isset( $option_array['id'] ) ) {
				$id = $option_array['id'];
			} else { $id = null;}
			$old_value = get_option( $id );
			$new_value = '';
			
			if ( ! current_user_can( 'unfiltered_html' ) && in_array( $id, woo_disabled_if_not_unfiltered_html_option_keys() ) ) { continue; } // Skip over the theme option if it's not being passed through.
			
			if( isset( $output[$id] ) ) {
				$new_value = $output[$option_array['id']];
			}

			if( isset( $option_array['id'] ) ) { // Non - Headings...

				$type = $option_array['type'];

				if ( is_array( $type ) ) {
					foreach( $type as $array ) {
						if( $array['type'] == 'text' ) {
							$id = $array['id'];
							$std = $array['std'];
							$new_value = $output[$id];
							if( $new_value == '' ) { $new_value = $std; }

							update_option( $id, stripslashes( $new_value ) );
						}
					}
				}
				elseif ( $type == 'text' && $save_type == 'seo' ) { // Text Save

					$new_value = $output[$id];
					if( $new_value == '' && $std != '' ) { $new_value = $std; }

					$new_value = stripslashes( stripslashes( $new_value ) );

					update_option( $id, $new_value );
				}
				elseif( $new_value == '' && $type == 'checkbox' ) { // Checkbox Save

					update_option( $id, 'false' );
				}
				elseif ( $new_value == 'true' && $type == 'checkbox' ) { // Checkbox Save

					update_option( $id, 'true' );
				}
				elseif( $type == 'multicheck' ) { // Multi Check Save

					$option_options = $option_array['options'];

					foreach ( $option_options as $options_id => $options_value ) {

						$multicheck_id = $id . "_" . $options_id;

						if( !isset( $output[$multicheck_id] ) ) {
							update_option( $multicheck_id, 'false' );
						}
						else{
							update_option( $multicheck_id, 'true' );
						}
					}
				}
				elseif( $type == 'typography' ) {
					$typography_array = array();

					foreach ( array( 'size', 'unit', 'face', 'style', 'color' ) as $v  ) {
						$value = '';
						$value = $output[$option_array['id'] . '_' . $v];
						if ( $v == 'face' ) {
							$typography_array[$v] = stripslashes( $value );
						} else {
							$typography_array[$v] = $value;
						}
					}
					
					update_option( $id, $typography_array );

				}
				elseif( $type == 'border' ) {

					$border_array = array();

					$border_array['width'] = $output[$option_array['id'] . '_width'];
					$border_array['style'] = $output[$option_array['id'] . '_style'];
					$border_array['color'] = $output[$option_array['id'] . '_color'];

					update_option( $id, $border_array );

				} else if ( $type == 'timestamp' ) {
					// It is assumed that the data comes back in the following format:
					// date: month/day/year
					// hour: int(2)
					// minute: int(2)
					// second: int(2)
					
					// Format the data into a timestamp.
					$date = $output[$option_array['id']]['date'];
					
					$hour = $output[$option_array['id']]['hour'];
					$minute = $output[$option_array['id']]['minute'];
					// $second = $output[$option_array['id']]['second'];
					$second = '00';
					
					$day = substr( $date, 3, 2 );
					$month = substr( $date, 0, 2 );
					$year = substr( $date, 6, 4 );
					
					$timestamp = mktime( $hour, $minute, $second, $month, $day, $year );
					 
					update_option( $id, stripslashes( $timestamp ) );
					
				} else {

					update_option( $id, stripslashes( $new_value ) );
				}
			}
		}

		// Assume that all has been completed and set $status to true.
		$status = true;
	}


	if( $save_type == 'options' || $save_type == 'framework' ) {
		/* Create, Encrypt and Update the Saved Settings */
		$woo_options = array();
		$data = array();
		if( $save_type == 'framework' ) {
			$options = get_option( 'woo_template' );
		}
		$count = 0;
		foreach( $options as $option ) {
			if( isset( $option['id'] ) ) {
				$count++;
				$option_id = $option['id'];
				$option_type = $option['type'];

				if( is_array( $option_type ) ) {
					$type_array_count = 0;
					foreach( $option_type as $inner_option ) {
						$option_id = $inner_option['id'];
						if ( isset( $data[$option_id] ) )
							$data[$option_id] .= get_option( $option_id );
						else
							$data[$option_id] = get_option( $option_id );
					}
				}
				else {
					$data[$option_id] = get_option( $option_id );
				}
			}
		}

		$output = "<ul>";

		foreach ( $data as $name => $value ) {

			if( is_serialized( $value ) ) {

				$value = unserialize( $value );
				$woo_array_option = $value;
				$temp_options = '';
				foreach( $value as $v ) {
					if( isset( $v ) )
						$temp_options .= $v . ',';

				}
				$value = $temp_options;
				$woo_array[$name] = $woo_array_option;
			} else {
				$woo_array[$name] = $value;
			}

			$output .= '<li><strong>' . esc_html( $name ) . '</strong> - ' . esc_html( $value ) . '</li>';
		}
		$output .= "</ul>";

		update_option( 'woo_options', $woo_array );

		// Assume that all has been completed and set $status to true.
		$status = true;
	}

	return $status;
} // End woo_options_save()

/*-----------------------------------------------------------------------------------*/
/* Non-AJAX Save Action - woo_nonajax_callback()
/*
/* This action is hooked on load of the various screens.
/* The hook is done when the pages are registered.
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_nonajax_callback' ) ) {
	function woo_nonajax_callback() {
		if ( isset( $_POST['_ajax_nonce'] ) && isset( $_POST['woo_save'] ) && ( $_POST['woo_save'] == 'save' ) ) {

			$nonce_key = 'wooframework-theme-options-update';

			switch ( $_REQUEST['page'] ) {
			case 'woothemes':
				$type = 'options';
				$nonce_key = 'wooframework-theme-options-update';
				break;

			case 'woothemes_framework_settings':
				$type = 'framework';
				$nonce_key = 'wooframework-framework-options-update';
				break;

			case 'woothemes_seo':
				$type = 'seo';
				$nonce_key = 'wooframework-seo-options-update';
				break;

			case 'woothemes_tumblog':
				$type = 'tumblog';
				break;

			default:
				$type = '';
			}

			// check security with nonce.
			if ( function_exists( 'check_admin_referer' ) ) { check_admin_referer( $nonce_key, '_ajax_nonce' ); } // End IF Statement

			// Remove non-options fields from the $_POST.
			$fields_to_remove = array( '_wpnonce', '_wp_http_referer', '_ajax_nonce', 'woo_save' );

			$data = array();

			foreach ( $_POST as $k => $v ) {
				if ( in_array( $k, $fields_to_remove ) ) {} else {
					$data[$k] = $v;
				}
			}

			$status = woo_options_save( $type, $data );

			if ( $status ) {
				add_action( 'admin_notices', 'woo_admin_message_success', 0 );
			} else {
				add_action( 'admin_notices', 'woo_admin_message_error', 0 );
			}

		} // End IF Statement
	} // End woo_nonajax_callback()
}

/*-----------------------------------------------------------------------------------*/
/* AJAX Save Action - woo_ajax_callback() */
/*-----------------------------------------------------------------------------------*/

add_action( 'wp_ajax_woo_ajax_post_action', 'woo_ajax_callback' );

if ( ! function_exists( 'woo_ajax_callback' ) ) {
	function woo_ajax_callback() {
		// check security with nonce.
		if ( function_exists( 'check_ajax_referer' ) ) { check_ajax_referer( 'wooframework-theme-options-update', '_ajax_nonce' ); } // End IF Statement

		$data = maybe_unserialize( $_POST['data'] );

		woo_options_save( $_POST['type'], $data );

		die();
	} // End woo_ajax_callback()
}

/*-----------------------------------------------------------------------------------*/
/* Admin Messages */
/*-----------------------------------------------------------------------------------*/

function woo_admin_message_success () {
	echo '<div class="updated fade" style="display: block !important;"><p>' . __( 'Options Saved Successfully', 'woothemes' ) . '</p></div><!--/.updated fade-->' . "\n";
} // End woo_admin_message_success()

function woo_admin_message_error () {
	echo '<div class="error fade" style="display: block !important;"><p>' . __( 'There was an error while saving your options. Please try again.', 'woothemes' ) . '</p></div><!--/.error fade-->' . "\n";
} // End woo_admin_message_error()

function woo_admin_message_reset () {
	echo '<div class="updated fade" style="display: block !important;"><p>' . __( 'Options Reset Successfully', 'woothemes' ) . '</p></div><!--/.updated fade-->' . "\n";
} // End woo_admin_message_reset()

/*-----------------------------------------------------------------------------------*/
/* Generates The Options - woothemes_machine */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woothemes_machine' ) ) {
	function woothemes_machine( $options ) {
		$counter = 0;
		$menu = '';
		$output = '';
		
		// Create an array of menu items - multi-dimensional, to accommodate sub-headings.
		$menu_items = array();
		$headings = array();
		
		foreach ( $options as $k => $v ) {
			if ( $v['type'] == 'heading' || $v['type'] == 'subheading' ) {
				$headings[] = $v;
			}
		}
		
		$prev_heading_key = 0;
		
		foreach ( $headings as $k => $v ) {
			$token = 'woo-option-' . preg_replace( '/[^a-zA-Z0-9\s]/', '', strtolower( trim( str_replace( ' ', '', $v['name'] ) ) ) );
			
			// Capture the token.
			$v['token'] = $token;
			
			if ( $v['type'] == 'heading' ) {
				$menu_items[$token] = $v;
				$prev_heading_key = $token;
			}
			
			if ( $v['type'] == 'subheading' ) {
				$menu_items[$prev_heading_key]['children'][] = $v;
			}
		}

		// Loop through the options.
		foreach ( $options as $k => $value ) {

			$counter++;
			$val = '';
			//Start Heading
			if ( $value['type'] != 'heading' && $value['type'] != 'subheading' ) {
				$class = ''; if( isset( $value['class'] ) ) { $class = ' ' . $value['class']; }
				$output .= '<div class="section section-' . esc_attr( $value['type'] ) . esc_attr( $class ) .'">'."\n";
				$output .= '<h3 class="heading">'. esc_html( $value['name'] ) .'</h3>'."\n";
				$output .= '<div class="option">'."\n" . '<div class="controls">'."\n";

			}
			//End Heading
			
			$select_value = '';
			switch ( $value['type'] ) {

			case 'text':
				$val = $value['std'];
				$std = esc_html( get_option( $value['id'] ) );
				if ( $std != "" ) { $val = $std; }
				$val = stripslashes( $val ); // Strip out unwanted slashes.
				$output .= '<input class="woo-input" name="'. esc_attr( $value['id'] ) .'" id="'. esc_attr( $value['id'] ) .'" type="'. esc_attr( $value['type'] ) .'" value="'. esc_attr( $val ) .'" />';
				break;

			case 'select':
				$output .= '<div class="select_wrapper"><select class="woo-input" name="'. esc_attr( $value['id'] ) .'" id="'. esc_attr( $value['id'] ) .'">';

				$select_value = stripslashes( get_option( $value['id'] ) );

				foreach ( $value['options'] as $option ) {

					$selected = '';

					if( $select_value != '' ) {
						if ( $select_value == $option ) { $selected = ' selected="selected"';}
					} else {
						if ( isset( $value['std'] ) )
							if ( $value['std'] == $option ) { $selected = ' selected="selected"'; }
					}

					$output .= '<option'. $selected .'>';
					$output .= esc_html( $option );
					$output .= '</option>';

				}
				$output .= '</select></div>';

				break;
			
			case 'select2':
				$output .= '<div class="select_wrapper">' . "\n";

				if ( is_array( $value['options'] ) ) {
					$output .= '<select class="woo-input" name="'. esc_attr( $value['id'] ) .'" id="'. esc_attr( $value['id'] ) .'">';

					$select_value = stripslashes( get_option( $value['id'] ) );


					foreach ( $value['options'] as $option => $name ) {

						$selected = '';

						if( $select_value != '' ) {
							if ( $select_value == $option ) { $selected = ' selected="selected"';}
						} else {
							if ( isset( $value['std'] ) )
								if ( $value['std'] == $option ) { $selected = ' selected="selected"'; }
						}

						$output .= '<option'. $selected .' value="'.esc_attr( $option ).'">';
						$output .= esc_html( $name );
						$output .= '</option>';

					}
					$output .= '</select>' . "\n";
				}

				$output .= '</div>';

				break;
			
			case 'calendar':
				$val = $value['std'];
				$std = get_option( $value['id'] );
				if ( $std != "" ) { $val = $std; }
				$output .= '<input class="woo-input-calendar" type="text" name="'.esc_attr( $value['id'] ).'" id="'.esc_attr( $value['id']).'" value="'.esc_attr( $val ).'">';
				$output .= '<input type="hidden" name="datepicker-image" value="' . get_template_directory_uri() . '/functions/images/calendar.gif" />';

				break;
				
			case 'time':
				$val = $value['std'];
				$std = get_option( $value['id'] );
				if ( $std != "" ) { $val = $std; }
				$output .= '<input class="woo-input-time" name="'. esc_attr( $value['id'] ) .'" id="'. esc_attr( $value['id'] ) .'" type="text" value="'. esc_attr( $val ) .'" />';
				break;
			
			case 'time_masked':
				$val = $value['std'];
				$std = get_option( $value['id'] );
				if ( $std != "" ) { $val = $std; }
				$output .= '<input class="woo-input-time-masked" name="'. esc_attr( $value['id'] ) .'" id="'. esc_attr( $value['id'] ) .'" type="text" value="'. esc_attr( $val ) .'" />';
				break;
	
			case 'textarea':
				$cols = '8';
				$ta_value = '';

				if( isset( $value['std'] ) ) {

					$ta_value = $value['std'];

					if( isset( $value['options'] ) ) {
						$ta_options = $value['options'];
						if( isset( $ta_options['cols'] ) ) {
							$cols = $ta_options['cols'];
						} else { $cols = '8'; }
					}

				}
				$std = get_option( $value['id'] );
				if( $std != "" ) { $ta_value = stripslashes( $std ); }
				$output .= '<textarea ' . ( ! current_user_can( 'unfiltered_html' ) && in_array( $value['id'], woo_disabled_if_not_unfiltered_html_option_keys() ) ? 'disabled="disabled" ' : '' ) . 'class="woo-input" name="'. esc_attr( $value['id'] ) .'" id="'. esc_attr( $value['id'] ) .'" cols="'. esc_attr( $cols ) .'" rows="8">'.esc_textarea( $ta_value ).'</textarea>';


				break;
				
			case "radio":
				$select_value = get_option( $value['id'] );

				if ( is_array( $value['options'] ) ) {
					foreach ( $value['options'] as $key => $option ) {

						$checked = '';
						if( $select_value != '' ) {
							if ( $select_value == $key ) { $checked = ' checked'; }
						} else {
							if ( $value['std'] == $key ) { $checked = ' checked'; }
						}
						$output .= '<div class="radio-wrapper"><input class="woo-input woo-radio" type="radio" name="'. esc_attr( $value['id'] ) .'" value="'. esc_attr( $key ) .'" '. $checked .' /><label>' . esc_html( $option ) .'</label></div>';

					}
				}

				break;
				
			case "checkbox":
				$std = $value['std'];

				$saved_std = get_option( $value['id'] );

				$checked = '';

				if( ! empty( $saved_std ) ) {
					if( $saved_std == 'true' ) {
						$checked = 'checked="checked"';
					} else {
						$checked = '';
					}
				}
				elseif( $std == 'true' ) {
					$checked = 'checked="checked"';
				}
				else {
					$checked = '';
				}
				$output .= '<input type="checkbox" class="checkbox woo-input" name="'.  esc_attr( $value['id'] ) .'" id="'. esc_attr( $value['id'] ) .'" value="true" '. $checked .' />';

				break;
				
			case "multicheck":
				$std =  $value['std'];

				if ( is_array( $value['options'] ) ) {
					foreach ( $value['options'] as $key => $option ) {

						$woo_key = $value['id'] . '_' . $key;
						$saved_std = get_option( $woo_key );

						if ( ! empty( $saved_std ) ) {
							if ( $saved_std == 'true' ) {
								$checked = 'checked="checked"';
							} else {
								$checked = '';
							}
						} elseif ( $std == $key ) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						$output .= '<input type="checkbox" class="checkbox woo-input" name="'. esc_attr( $woo_key ) .'" id="'. esc_attr( $woo_key ) .'" value="true" '. $checked .' /><label for="'. esc_attr( $woo_key ) .'">'. esc_html( $option ) .'</label><br />';

					}
				}
				break;
			
			case "multicheck2":
				$std =  explode( ',', $value['std'] );

				if ( is_array( $value['options'] ) ) {
					foreach ( $value['options'] as $key => $option ) {

						$woo_key = $value['id'] . '_' . $key;
						$saved_std = get_option( $woo_key );

						if( ! empty( $saved_std ) )
						{
							if( $saved_std == 'true' ) {
								$checked = 'checked="checked"';
							} else {
								$checked = '';
							}
						}
						elseif ( in_array( $key, $std ) ) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						$output .= '<input type="checkbox" class="checkbox woo-input" name="'. esc_attr( $woo_key ) .'" id="'. esc_attr( $woo_key ) .'" value="true" '. $checked .' /><label for="'. esc_attr( $woo_key ) .'">'. esc_html( $option ) .'</label><br />';

					}
				}
				break;
				
			case "upload":
				$output .= woothemes_medialibrary_uploader( $value['id'], $value['std'], null ); // New AJAX Uploader using Media Library
				break;
				
			case "upload_min":
				$output .= woothemes_medialibrary_uploader( $value['id'], $value['std'], 'min' ); // New AJAX Uploader using Media Library
				break;
				
			case "color":
				$val = $value['std'];
				$stored  = get_option( $value['id'] );
				if ( $stored != "" ) { $val = $stored; }
				$output .= '<div id="' . esc_attr( $value['id'] ) . '_picker" class="colorSelector"><div></div></div>';
				$output .= '<input class="woo-color" name="'. esc_attr( $value['id'] ) .'" id="'. esc_attr( $value['id'] ) .'" type="text" value="'. esc_attr( $val ) .'" />';
				break;

			case "typography":
				$default = $value['std'];
				$typography_stored = get_option( $value['id'] );

				if ( ! is_array( $typography_stored ) || empty( $typography_stored ) ) {
					$typography_stored = $default;
				}

				/* Font Size */
				$val = $default['size'];
				if ( $typography_stored['size'] != '' ) {
					$val = $typography_stored['size'];
				}
				if ( $typography_stored['unit'] == 'px' ) {
					$show_px = '';
					$show_em = ' style="display:none" ';
					$name_px = ' name="'. esc_attr( $value['id'].'_size') . '" ';
					$name_em = '';
				} else if ( $typography_stored['unit'] == 'em' ) {
					$show_em = '';
					$show_px = 'style="display:none"';
					$name_em = ' name="'. esc_attr( $value['id'].'_size') . '" ';
					$name_px = '';
				} else {
					$show_px = '';
					$show_em = ' style="display:none" ';
					$name_px = ' name="'. esc_attr( $value['id'].'_size') . '" ';
					$name_em = '';
				}
				$output .= '<select class="woo-typography woo-typography-size woo-typography-size-px"  id="'. esc_attr( $value['id'].'_size_px') . '" '. $name_px . $show_px .'>';
				for ( $i = 9; $i < 71; $i++ ) {
					if( $val == strval( $i ) ) { $active = 'selected="selected"'; } else { $active = ''; }
					$output .= '<option value="'. esc_attr( $i ) .'" ' . $active . '>'. esc_html( $i ) .'</option>'; }
				$output .= '</select>';

				$output .= '<select class="woo-typography woo-typography-size woo-typography-size-em" id="'. esc_attr( $value['id'].'_size_em' ) . '" '. $name_em . $show_em.'>';
				$em = 0.5;
				for ( $i = 0; $i < 39; $i++ ) {
					if ( $i <= 24 )   // up to 2.0em in 0.1 increments
						$em = $em + 0.1;
					elseif ( $i >= 14 && $i <= 24 )  // Above 2.0em to 3.0em in 0.2 increments
						$em = $em + 0.2;
					elseif ( $i >= 24 )  // Above 3.0em in 0.5 increments
						$em = $em + 0.5;
					if( $val == strval( $em ) ) { $active = 'selected="selected"'; } else { $active = ''; }
					//echo ' '. $value['id'] .' val:'.floatval($val). ' -> ' . floatval($em) . ' $<br />' ;
					$output .= '<option value="'. esc_attr( $em ) .'" ' . $active . '>'. esc_html( $em ) .'</option>'; }
				$output .= '</select>';

				/* Font Unit */
				$val = $default['unit'];
				if ( $typography_stored['unit'] != '' ) { $val = $typography_stored['unit']; }
				$em = ''; $px = '';
				if( $val == 'em' ) { $em = 'selected="selected"'; }
				if( $val == 'px' ) { $px = 'selected="selected"'; }
				$output .= '<select class="woo-typography woo-typography-unit" name="'. esc_attr( $value['id'] ) .'_unit" id="'. esc_attr( $value['id'].'_unit' ) . '">';
				$output .= '<option value="px" '. $px .'">px</option>';
				$output .= '<option value="em" '. $em .'>em</option>';
				$output .= '</select>';

				/* Font Face */
				$val = $default['face'];
				if ( $typography_stored['face'] != "" )
					$val = $typography_stored['face'];

				$font01 = '';
				$font02 = '';
				$font03 = '';
				$font04 = '';
				$font05 = '';
				$font06 = '';
				$font07 = '';
				$font08 = '';
				$font09 = '';
				$font10 = '';
				$font11 = '';
				$font12 = '';
				$font13 = '';
				$font14 = '';
				$font15 = '';
				$font16 = '';
				$font17 = '';

				if ( strpos( $val, 'Arial, sans-serif' ) !== false ) { $font01 = 'selected="selected"'; }
				if ( strpos( $val, 'Verdana, Geneva' ) !== false ) { $font02 = 'selected="selected"'; }
				if ( strpos( $val, 'Trebuchet' ) !== false ) { $font03 = 'selected="selected"'; }
				if ( strpos( $val, 'Georgia' ) !== false ) { $font04 = 'selected="selected"'; }
				if ( strpos( $val, 'Times New Roman' ) !== false ) { $font05 = 'selected="selected"'; }
				if ( strpos( $val, 'Tahoma, Geneva' ) !== false ) { $font06 = 'selected="selected"'; }
				if ( strpos( $val, 'Palatino' ) !== false ) { $font07 = 'selected="selected"'; }
				if ( strpos( $val, 'Helvetica' ) !== false ) { $font08 = 'selected="selected"'; }
				if ( strpos( $val, 'Calibri' ) !== false ) { $font09 = 'selected="selected"'; }
				if ( strpos( $val, 'Myriad' ) !== false ) { $font10 = 'selected="selected"'; }
				if ( strpos( $val, 'Lucida' ) !== false ) { $font11 = 'selected="selected"'; }
				if ( strpos( $val, 'Arial Black' ) !== false ) { $font12 = 'selected="selected"'; }
				if ( strpos( $val, 'Gill' ) !== false ) { $font13 = 'selected="selected"'; }
				if ( strpos( $val, 'Geneva, Tahoma' ) !== false ) { $font14 = 'selected="selected"'; }
				if ( strpos( $val, 'Impact' ) !== false ) { $font15 = 'selected="selected"'; }
				if ( strpos( $val, 'Courier' ) !== false ) { $font16 = 'selected="selected"'; }
				if ( strpos( $val, 'Century Gothic' ) !== false ) { $font17 = 'selected="selected"'; }

				$output .= '<select class="woo-typography woo-typography-face" name="'. esc_attr( $value['id'].'_face' ) . '" id="'. esc_attr( $value['id'].'_face') . '">';
				$output .= '<option value="Arial, sans-serif" '. $font01 .'>Arial</option>';
				$output .= '<option value="Verdana, Geneva, sans-serif" '. $font02 .'>Verdana</option>';
				$output .= '<option value="&quot;Trebuchet MS&quot;, Tahoma, sans-serif"'. $font03 .'>Trebuchet</option>';
				$output .= '<option value="Georgia, serif" '. $font04 .'>Georgia</option>';
				$output .= '<option value="&quot;Times New Roman&quot;, serif"'. $font05 .'>Times New Roman</option>';
				$output .= '<option value="Tahoma, Geneva, Verdana, sans-serif"'. $font06 .'>Tahoma</option>';
				$output .= '<option value="Palatino, &quot;Palatino Linotype&quot;, serif"'. $font07 .'>Palatino</option>';
				$output .= '<option value="&quot;Helvetica Neue&quot;, Helvetica, sans-serif" '. $font08 .'>Helvetica*</option>';
				$output .= '<option value="Calibri, Candara, Segoe, Optima, sans-serif"'. $font09 .'>Calibri*</option>';
				$output .= '<option value="&quot;Myriad Pro&quot;, Myriad, sans-serif"'. $font10 .'>Myriad Pro*</option>';
				$output .= '<option value="&quot;Lucida Grande&quot;, &quot;Lucida Sans Unicode&quot;, &quot;Lucida Sans&quot;, sans-serif"'. $font11 .'>Lucida</option>';
				$output .= '<option value="&quot;Arial Black&quot;, sans-serif" '. $font12 .'>Arial Black</option>';
				$output .= '<option value="&quot;Gill Sans&quot;, &quot;Gill Sans MT&quot;, Calibri, sans-serif" '. $font13 .'>Gill Sans*</option>';
				$output .= '<option value="Geneva, Tahoma, Verdana, sans-serif" '. $font14 .'>Geneva*</option>';
				$output .= '<option value="Impact, Charcoal, sans-serif" '. $font15 .'>Impact</option>';
				$output .= '<option value="Courier, &quot;Courier New&quot;, monospace" '. $font16 .'>Courier</option>';
				$output .= '<option value="&quot;Century Gothic&quot;, sans-serif" '. $font17 .'>Century Gothic</option>';

				// Google webfonts
				global $google_fonts;
				sort( $google_fonts );

				$output .= '<option value="">-- Google Fonts --</option>';
				foreach ( $google_fonts as $key => $gfont ) :
					$font[$key] = '';
				if ( $val == $gfont['name'] ) { $font[$key] = 'selected="selected"'; }
				$name = $gfont['name'];
				$output .= '<option value="'.esc_attr( $name ).'" '. $font[$key] .'>'.esc_html( $name ).'</option>';
				endforeach;

				// Custom Font stack
				$new_stacks = get_option( 'framework_woo_font_stack' );
				if( !empty( $new_stacks ) ) {
					$output .= '<option value="">-- Custom Font Stacks --</option>';
					foreach( $new_stacks as $name => $stack ) {
						if ( strpos( $val, $stack ) !== false ) { $fontstack = 'selected="selected"'; } else { $fontstack = ''; }
						$output .= '<option value="'. stripslashes( htmlentities( $stack ) ) .'" '.$fontstack.'>'. str_replace( '_', ' ', $name ).'</option>';
					}
				}

				$output .= '</select>';

				/* Font Weight */
				$val = $default['style'];
				if ( $typography_stored['style'] != "" ) { $val = $typography_stored['style']; }
				$thin = ''; $thinitalic = ''; $normal = ''; $italic = ''; $bold = ''; $bolditalic = '';
				if( $val == '300' ) { $thin = 'selected="selected"'; }
				if( $val == '300 italic' ) { $thinitalic = 'selected="selected"'; }
				if( $val == 'normal' ) { $normal = 'selected="selected"'; }
				if( $val == 'italic' ) { $italic = 'selected="selected"'; }
				if( $val == 'bold' ) { $bold = 'selected="selected"'; }
				if( $val == 'bold italic' ) { $bolditalic = 'selected="selected"'; }

				$output .= '<select class="woo-typography woo-typography-style" name="'. esc_attr( $value['id'].'_style' ) . '" id="'. esc_attr( $value['id'].'_style' ) . '">';
				$output .= '<option value="300" '. $thin .'>Thin</option>';
				$output .= '<option value="300 italic" '. $thinitalic .'>Thin/Italic</option>';
				$output .= '<option value="normal" '. $normal .'>Normal</option>';
				$output .= '<option value="italic" '. $italic .'>Italic</option>';
				$output .= '<option value="bold" '. $bold .'>Bold</option>';
				$output .= '<option value="bold italic" '. $bolditalic .'>Bold/Italic</option>';
				$output .= '</select>';

				/* Font Color */
				$val = $default['color'];
				if ( $typography_stored['color'] != "" ) { $val = $typography_stored['color']; }
				$output .= '<div id="' . esc_attr( $value['id'] . '_color_picker' ) .'" class="colorSelector"><div></div></div>';
				$output .= '<input class="woo-color woo-typography woo-typography-color" name="'. esc_attr( $value['id'] .'_color' ) . '" id="'. esc_attr( $value['id'] .'_color' ) . '" type="text" value="'. esc_attr( $val ) .'" />';

				break;

			case "border":
				$default = $value['std'];
				$border_stored = get_option( $value['id'] );

				/* Border Width */
				$val = $default['width'];
				if ( $border_stored['width'] != "" ) { $val = $border_stored['width']; }
				$output .= '<select class="woo-border woo-border-width" name="'. esc_attr( $value['id'].'_width' ) . '" id="'. esc_attr( $value['id'].'_width' ) . '">';
				for ( $i = 0; $i < 21; $i++ ) {
					if( $val == $i ) { $active = 'selected="selected"'; } else { $active = ''; }
					$output .= '<option value="'. esc_attr( $i ) .'" ' . $active . '>'. esc_html( $i ) .'px</option>'; }
				$output .= '</select>';

				/* Border Style */
				$val = $default['style'];
				if ( $border_stored['style'] != "" ) { $val = $border_stored['style']; }
				$solid = ''; $dashed = ''; $dotted = '';
				if( $val == 'solid' ) { $solid = 'selected="selected"'; }
				if( $val == 'dashed' ) { $dashed = 'selected="selected"'; }
				if( $val == 'dotted' ) { $dotted = 'selected="selected"'; }

				$output .= '<select class="woo-border woo-border-style" name="'. esc_attr( $value['id'].'_style' ) . '" id="'. esc_attr( $value['id'].'_style' ) . '">';
				$output .= '<option value="solid" '. $solid .'>Solid</option>';
				$output .= '<option value="dashed" '. $dashed .'>Dashed</option>';
				$output .= '<option value="dotted" '. $dotted .'>Dotted</option>';
				$output .= '</select>';

				/* Border Color */
				$val = $default['color'];
				if ( $border_stored['color'] != "" ) { $val = $border_stored['color']; }
				$output .= '<div id="' . esc_attr( $value['id'] . '_color_picker' ) . '" class="colorSelector"><div></div></div>';
				$output .= '<input class="woo-color woo-border woo-border-color" name="'. esc_attr( $value['id'] .'_color' ) . '" id="'. esc_attr( $value['id'] .'_color' ) . '" type="text" value="'. esc_attr( $val ) .'" />';

				break;

			case "images":
				$i = 0;
				$select_value = get_option( $value['id'] );

				foreach ( $value['options'] as $key => $option ) {
					$i++;

					$checked = '';
					$selected = '';
					if( $select_value != '' ) {
						if ( $select_value == $key ) { $checked = ' checked'; $selected = 'woo-radio-img-selected'; }
					} else {
						if ( $value['std'] == $key ) { $checked = ' checked'; $selected = 'woo-radio-img-selected'; }
						elseif ( $i == 1  && !isset( $select_value ) ) { $checked = ' checked'; $selected = 'woo-radio-img-selected'; }
						elseif ( $i == 1  && $value['std'] == '' ) { $checked = ' checked'; $selected = 'woo-radio-img-selected'; }
						else { $checked = ''; }
					}

					$output .= '<span>';
					$output .= '<input type="radio" id="woo-radio-img-' . $value['id'] . $i . '" class="checkbox woo-radio-img-radio" value="'. esc_attr( $key ) .'" name="'. esc_attr( $value['id'] ).'" '.$checked.' />';
					$output .= '<span class="woo-radio-img-label">'. esc_html( $key ) .'</span>';
					$output .= '<img src="'.esc_attr( $option ).'" alt="" class="woo-radio-img-img '. $selected .'" onClick="document.getElementById(\'woo-radio-img-'. $value['id'] . $i.'\').checked = true;" />';
					$output .= '</span>';

				}

				break;

			case "info":
				$default = $value['std'];
				$output .= $default;
				break;
			
			// Timestamp field.
			case 'timestamp':
				$val = get_option( $value['id'] );
				
				if ( $val == '' ) {
					$val = time();
				}
				
				$output .= '<input type="hidden" name="datepicker-image" value="' . admin_url( 'images/date-button.gif' ) . '" />' . "\n";
				
				$output .= '<span class="time-selectors">' . "\n";
				$output .= ' <span class="woo-timestamp-at">' . __( '@', 'woothemes' ) . '</span> ';
				
				$output .= '<select name="' . esc_attr( $value['id'] . '[hour]' ) . '" class="woo-select-timestamp">' . "\n";
					for ( $i = 0; $i <= 23; $i++ ) {
						
						$j = $i;
						if ( $i < 10 ) {
							$j = '0' . $i;
						}
						
						$output .= '<option value="' . esc_attr( $i ) . '"' . selected( date( 'H', $val ), $j, false ) . '>' . esc_html( $j ) . '</option>' . "\n";
					}
				$output .= '</select>' . "\n";
				
				$output .= '<select name="' . $value['id'] . '[minute]" class="woo-select-timestamp">' . "\n";
					for ( $i = 0; $i <= 59; $i++ ) {
						
						$j = $i;
						if ( $i < 10 ) {
							$j = '0' . $i;
						}
						
						$output .= '<option value="' . esc_attr( $i ) . '"' . selected( date( 'i', $val ), $j, false ) .'>' . esc_html( $j ) . '</option>' . "\n";
					}
				$output .= '</select>' . "\n";
				/*
				$output .= '<select name="' . $value['id'] . '[second]" class="woo-select-timestamp">' . "\n";
					for ( $i = 0; $i <= 59; $i++ ) {
						
						$j = $i;
						if ( $i < 10 ) {
							$j = '0' . $i;
						}
						
						$output .= '<option value="' . $i . '"' . selected( date( 's', $val ), $j, false ) . '>' . $j . '</option>' . "\n";
					}
				$output .= '</select>' . "\n";
				*/
				
				$output .= '</span><!--/.time-selectors-->' . "\n";
				
				$output .= '<input class="woo-input-calendar" type="text" name="' . esc_attr( $value['id'] . '[date]' ) . '" id="'.esc_attr( $value['id'] ).'" value="' . esc_attr( date( 'm/d/Y', $val ) ) . '">';
			break;

			case 'slider':
				$val = $value['std'];
				$std = get_option( $value['id'] );
				if ( $std != "" ) { $val = $std; }
				$val = stripslashes( $val ); // Strip out unwanted slashes.
				$output .= '<div class="ui-slide" id="'. esc_attr( $value['id'] .'_div' ) . '" min="'. esc_attr( $value['min'] ) .'" max="'. esc_attr( $value['max'] ) .'" inc="'. esc_attr( $value['increment'] ) .'"></div>';
				$output .= '<input readonly="readonly" class="woo-input" name="'. esc_attr( $value['id'] ) .'" id="'. esc_attr( $value['id'] ) .'" type="'. esc_attr( $value['type'] ) .'" value="'. esc_attr( $val ) .'" />';
			break;

			case "heading":
				if( $counter >= 2 ) {
					$output .= '</div>'."\n";
				}
				$jquery_click_hook = preg_replace( '/[^a-zA-Z0-9\s]/', '', strtolower( $value['name'] ) );
				// $jquery_click_hook = preg_replace( '/[^\p{L}\p{N}]/u', '', strtolower( $value['name'] ) ); // Regex for UTF-8 languages.
				$jquery_click_hook = str_replace( ' ', '', $jquery_click_hook );

				$jquery_click_hook = "woo-option-" . $jquery_click_hook;
				$menu .= '<li class="'.esc_attr( $value['icon'] ).'"><a title="'. esc_attr( $value['name'] ) .'" href="#'.  $jquery_click_hook  .'">'.  esc_html( $value['name'] ) .'</a></li>';
				$output .= '<div class="group" id="'. esc_attr( $jquery_click_hook ) .'"><h1 class="subtitle">'. esc_html( $value['name'] ) .'</h1>'."\n";
				break;
			
			case "subheading":
				if( $counter >= 2 ) {
					$output .= '</div>'."\n";
				}
				$jquery_click_hook = preg_replace( '/[^a-zA-Z0-9\s]/', '', strtolower( $value['name'] ) );
				// $jquery_click_hook = preg_replace( '/[^\p{L}\p{N}]/u', '', strtolower( $value['name'] ) ); // Regex for UTF-8 languages.
				$jquery_click_hook = str_replace( ' ', '', $jquery_click_hook );

				$jquery_click_hook = "woo-option-" . $jquery_click_hook;
				$menu .= '<li><a title="' . esc_attr( $value['name'] ) . '" href="#' . $jquery_click_hook . '">' . esc_html( $value['name'] ) . '</a></li>';
				$output .= '<div class="group" id="'. esc_attr( $jquery_click_hook ) .'"><h1 class="subtitle">'. esc_html( $value['name'] ).'</h1>'."\n";
				break;
			}

			// if TYPE is an array, formatted into smaller inputs... ie smaller values
			if ( is_array( $value['type'] ) ) {
				foreach( $value['type'] as $array ) {

					$id = $array['id'];
					$std = $array['std'];
					$saved_std = get_option( $id );
					if( $saved_std != $std ) {$std = $saved_std;}
					$meta = $array['meta'];

					if( $array['type'] == 'text' ) { // Only text at this point

						$output .= '<input class="input-text-small woo-input" name="'. esc_attr( $id ) .'" id="'. esc_attr( $id ) .'" type="text" value="'. esc_attr( $std ) .'" />';
						$output .= '<span class="meta-two">'. esc_html( $meta ) .'</span>';
					}
				}
			}
			if ( $value['type'] != "heading" && $value['type'] != "subheading" ) {
				if ( $value['type'] != "checkbox" )
				{
					$output .= '<br/>';
				}
				$explain_value = ( isset( $value['desc'] ) ) ? $value['desc'] : '';
				if ( !current_user_can( 'unfiltered_html' ) && isset( $value['id'] ) && in_array( $value['id'], woo_disabled_if_not_unfiltered_html_option_keys() ) )
					$explain_value .= '<br /><br /><b>' . esc_html( __( 'You are not able to update this option because you lack the <code>unfiltered_html</code> capability.', 'woothemes' ) ) . '</b>';
				$output .= '</div><div class="explain">'. $explain_value .'</div>'."\n";
				$output .= '<div class="clear"> </div></div></div>'."\n";
			}

		}

		//Checks if is not the Content Builder page
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] != 'woothemes_content_builder' ) {
			$output .= '</div>';
		}
		
		// Override the menu with a new multi-level menu.
		if ( count( $menu_items ) > 0 ) {
			$menu = '';
			foreach ( $menu_items as $k => $v ) {
				$class = '';
				if ( isset( $v['icon'] ) && ( $v['icon'] != '' ) ) {
					$class = $v['icon'];
				}
				
				if ( isset( $v['children'] ) && ( count( $v['children'] ) > 0 ) ) {
					$class .= ' has-children';
				}
				
				$menu .= '<li class="top-level ' . $class . '">' . "\n" . '<div class="arrow"><div></div></div>'; 
				if ( isset( $v['icon'] ) && ( $v['icon'] != '' ) )
					$menu .= '<span class="icon"></span>';
				$menu .= '<a title="' . esc_attr( $v['name'] ) . '" href="#' . $v['token'] . '">' . esc_html( $v['name'] ) . '</a>' . "\n";
				
				if ( isset( $v['children'] ) && ( count( $v['children'] ) > 0 ) ) {
					$menu .= '<ul class="sub-menu">' . "\n";
						foreach ( $v['children'] as $i => $j ) {
							$menu .= '<li class="icon">' . "\n" . '<a title="' . esc_attr( $j['name'] ) . '" href="#' . $j['token'] . '">' . esc_html( $j['name'] ) . '</a></li>' . "\n";
						}
					$menu .= '</ul>' . "\n";
				}
				$menu .= '</li>' . "\n";

			}
		}

		return array( $output, $menu, $menu_items );
	} // End woothemes_machine()
}

/*-----------------------------------------------------------------------------------*/
/* WooThemes Uploader - woothemes_uploader_function */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woothemes_uploader_function' ) ) {
	function woothemes_uploader_function( $id, $std, $mod ) {
		return woothemes_medialibrary_uploader( $id, $std, $mod );
	} // End woothemes_uploader_function()
}

/*-----------------------------------------------------------------------------------*/
/* Woothemes Theme Version Checker - woothemes_version_checker */
/* @local_version is the installed theme version number */
/*-----------------------------------------------------------------------------------*/

function woothemes_do_not_cache_feeds( &$feed ) { $feed->enable_cache( false ); } // End woothemes_do_not_cache_feeds()
function woothemes_http_request_args( $r ) { $r['timeout'] = 15; return $r; } // End woothemes_http_request_args()
function woothemes_http_api_curl( $handle ) { curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 15 ); curl_setopt( $handle, CURLOPT_TIMEOUT, 15 ); } // End woothemes_http_api_curl()

if ( ! function_exists( 'woothemes_version_checker' ) ) {
	function woothemes_version_checker ( $local_version ) {
		add_action( 'wp_feed_options', 'woothemes_do_not_cache_feeds' );
		add_filter( 'http_request_args', 'woothemes_http_request_args', 100, 1 );
		add_action( 'http_api_curl', 'woothemes_http_api_curl', 100, 1 );

		// Get a SimplePie feed object from the specified feed source.
		$theme_name = str_replace( "-", "", strtolower( get_option( 'woo_themename' ) ) );

		// Use a transient to store the current theme version data for 24 hours.
		$latest_version_via_rss = '';

		$version_data = get_transient( $theme_name . '_version_data' );

		if( $version_data ) {
			$latest_version_via_rss = $version_data;
		}

		// If the transient has expired, run the check.
		if ( $latest_version_via_rss == '' ) {
			$feed_url = 'http://www.woothemes.com/?feed=updates&theme=' . $theme_name;
			
			$rss = fetch_feed( $feed_url );

			// Of the RSS is failed somehow.
			if ( is_wp_error( $rss ) ) {
				// Return without notification
				// return;
				$latest_version_via_rss = $local_version;
			} else {
				//Figure out how many total items there are, but limit it to 5.
				$maxitems = $rss->get_item_quantity( 5 );
	
				// Build an array of all the items, starting with element 0 (first element).
				$rss_items = $rss->get_items( 0, $maxitems );
				if ( $maxitems == 0 ) { $latest_version_via_rss = 0; }
				else {
					// Loop through each feed item and display each item as a hyperlink.
					foreach ( $rss_items as $item ) :
						$latest_version_via_rss = $item->get_title();
					break; // Take only the first version number. Break away when we have it.
					endforeach;
				}
			}
		} // End Version Check

		// Set the transient containing the latest version number.
		set_transient( $theme_name . '_version_data', $latest_version_via_rss , 60*60*24 );

		//Check if version is the latest - assume standard structure x.x.x
		$pieces_rss = array();
		if ( isset( $latest_version_via_rss['version'] ) ) $pieces_rss = explode( '.', $latest_version_via_rss['version'] );
		$pieces_local = explode( '.', $local_version );

		//account for null values in second position x.2.x
		if( isset( $pieces_rss[0] ) && $pieces_rss[0] != 0 ) {
			if ( ! isset( $pieces_rss[1] ) )
				$pieces_rss[1] = '0';

			if ( ! isset( $pieces_local[1] ) )
				$pieces_local[1] = '0';

			//account for null values in third position x.x.3
			if ( ! isset( $pieces_rss[2] ) )
				$pieces_rss[2] = '0';

			if ( ! isset( $pieces_local[2] ) )
				$pieces_local[2] = '0';

			//do the comparisons
			$version_sentinel = false;
			$status = 'bugfix';

			// Setup update statuses
			$statuses = array(
				'new_version' => __( 'New Version', 'woothemes' ),
				'new_feature' => __( 'New Feature', 'woothemes' ),
				'bugfix' => __( 'Bugfix', 'woothemes' )
			);

			// New version
			if ( $pieces_rss[0] > $pieces_local[0] ) {
				$version_sentinel = true;
				$status = 'new_version';
			}
			// New feature
			if ( ( $pieces_rss[1] > $pieces_local[1] ) && ( $version_sentinel == false ) && ( $pieces_rss[0] == $pieces_local[0] ) ) {
				$version_sentinel = true;
				$status = 'new_feature';
			}
			// Bugfix
			if ( ( $pieces_rss[2] > $pieces_local[2] ) && ( $version_sentinel == false ) && ( $pieces_rss[0] == $pieces_local[0] ) && ( $pieces_rss[1] == $pieces_local[1] ) ) {
				$version_sentinel = true;
				$status = 'bugfix';
			}

			return array( 'is_update' => $version_sentinel, 'version' => $latest_version_via_rss, 'status' => $statuses[$status], 'theme_name' => $theme_name );


			//set version checker message
			if ( $version_sentinel == true ) {
				$update_message = '<div class="update_available status-' . $status . '">' . __( 'Theme update is available', 'woothemes' ) . ' (v.' . $latest_version_via_rss['version'] . ') - <a href="http://www.woothemes.com/products/">' . __( 'Get the new version', 'woothemes' ) . '</a>.<p>' . sprintf( __( 'Update Type: %s', 'woothemes' ), $statuses[$status] ) . '</p></div>';
			}
			else {
				$update_message = '';
			}
		} else {
			$update_message = '';
		}
		return $update_message;
	}
} // End woothemes_version_checker()

/*-----------------------------------------------------------------------------------*/
/* Woothemes Thumb Detection Notice - woo_thumb_admin_notice */
/*-----------------------------------------------------------------------------------*/
function woo_thumb_admin_notice() {
	
	if ( get_user_setting( 'wooframeworkhidebannerwootimthumb', '0' ) == '1' ) { return; }
	global $current_user;
	$current_user_id = $current_user->user_login;
	$super_user = get_option( 'framework_woo_super_user' );
	if( $super_user == $current_user_id || empty( $super_user ) ) {
		// Test for old timthumb scripts
		$thumb_php_test = file_exists(  get_template_directory() . '/thumb.php' );
		$timthumb_php_test = file_exists(  get_template_directory() . '/timthumb.php' );
		
		if ( $thumb_php_test || $timthumb_php_test ) {
			echo '<div class="error">
    			   <p><strong>' . __( 'ATTENTION: A possible old version of the TimThumb script was detected in your theme folder. Please remove the following files from your theme as a security precaution', 'woothemes' ) . ':</strong></p>';
    		if ( $thumb_php_test ) { echo '<p><strong>- thumb.php</strong></p>'; }
    		if ( $timthumb_php_test ) { echo '<p><strong>- timthumb.php</strong></p>'; }
    		echo '</div>';

		}
	} // End If Statement
} // End woo_thumb_admin_notice()

add_action( 'admin_notices', 'woo_thumb_admin_notice' );

/*-----------------------------------------------------------------------------------*/
/* WooThemes Theme Update Admin Notice - woo_theme_update_notice */
/*-----------------------------------------------------------------------------------*/

global $pagenow;
if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'woothemes' ) {
	if ( get_option( 'framework_woo_theme_version_checker' ) == 'true' ) { add_action( 'admin_notices', 'woo_theme_update_notice', 10 ); }
	if ( get_option( 'framework_woo_framework_version_checker' ) == 'true' ) { add_action( 'admin_notices', 'woo_framework_update_notice', 10 ); }

	add_action( 'admin_notices', 'woo_framework_critical_update_notice', 8 ); // Periodically check for critical WooFramework updates.
}

/**
 * woo_theme_update_notice()
 *
 * Notify users of theme updates, if necessary.
 *
 * @since 4.7.0
 */
if ( ! function_exists( 'woo_theme_update_notice' ) ) {
	function woo_theme_update_notice () {
		$data = wooframework_get_theme_version_data();
		$local_version = $data['theme_version'];
		$update_data = woothemes_version_checker( $local_version );

		if ( ! isset( $update_data['version'] ) || ! is_string( $update_data['version'] ) ) { return; }

		$html = '';

		if ( is_array( $update_data ) && $update_data['is_update'] == true ) {
			$html = '<div id="theme_update" class="updated fade"><p>' . sprintf( __( 'Theme update is available (v%s). %sDownload new version%s (%sSee Changelog%s)', 'woothemes' ), $update_data['version'], '<a href="http://www.woothemes.com/products/">', '</a>', '<a href="http://www.woothemes.com/changelogs/' . $update_data['theme_name'] . '/changelog.txt" target="_blank" title="Changelog">', '</a>' ) . '</p></div>';
		}

		if ( $html != '' ) { echo $html; }
	} // End woo_theme_update_notice()
}

/*-----------------------------------------------------------------------------------*/
/* WooThemes Framework Update Notice - woo_framework_update_notice */
/*-----------------------------------------------------------------------------------*/
/**
 * woo_framework_update_notice function.
 *
 * @description Notify users of framework updates, if necessary.
 * @since 4.8.0
 * @access public
 * @return void
 */
if ( ! function_exists( 'woo_framework_update_notice' ) ) {
	function woo_framework_update_notice () {
		$local_version = get_option( 'woo_framework_version' );
		if ( $local_version == '' ) { return; }
		$update_data = woo_framework_version_checker( $local_version );

		$html = '';

		if ( is_array( $update_data ) && $update_data['is_update'] == true ) {
			$html = '<div id="wooframework_update" class="updated fade"><p>' . sprintf( __( 'WooFramework update is available (v%s). %sDownload new version%s (%sSee Changelog%s)', 'woothemes' ), $update_data['version'], '<a href="' . admin_url( 'admin.php?page=woothemes_framework_update' ) . '">', '</a>', '<a href="http://www.woothemes.com/updates/functions-changelog.txt" target="_blank" title="Changelog">', '</a>' ) . '</p></div>';
		}

		if ( $html != '' ) { echo $html; }
	} // End woo_framework_update_notice()
}

/*-----------------------------------------------------------------------------------*/
/* WooThemes Framework Critical Update Notice - woo_framework_critical_update_notice */
/*-----------------------------------------------------------------------------------*/
/**
 * woo_framework_critical_update_notice function.
 *
 * @description Notify users of critical framework updates, if necessary.
 * @since 4.8.0
 * @access public
 * @return void
 */
if ( ! function_exists( 'woo_framework_critical_update_notice' ) ) {
	function woo_framework_critical_update_notice () {
		// Determine if the check has happened.
		$critical_update = get_transient( 'woo_framework_critical_update' );
		$critical_update_data = get_transient( 'woo_framework_critical_update_data' );

		if ( ! $critical_update || ! is_array( $critical_update_data ) ) {

			$local_version = get_option( 'woo_framework_version' );
			if ( $local_version == '' ) { return; }

			$update_data = woo_framework_version_checker( $local_version, true );

			// Set this to "has been checked" for 2 weeks.
			set_transient( 'woo_framework_critical_update', true, 60*60*336 );

			// Cache the data as well.
			set_transient( 'woo_framework_critical_update_data', $update_data, 60*60*336 );
		} else {
			$update_data = $critical_update_data;
		}

		$html = '';

		// Generate output based on returned/stored data.
		if ( is_array( $update_data ) && $update_data['is_update'] == true && $update_data['is_critical'] == true ) {

			// Remove the generic update notice.
			remove_action( 'admin_notices', 'woo_framework_update_notice', 10 );

			$html = '<div id="wooframework_important_update" class="error fade"><p>' . sprintf( __( 'An important WooFramework update is available (v%s). %sDownload new version%s (%sSee Changelog%s)', 'woothemes' ), $update_data['version'], '<a href="' . admin_url( 'admin.php?page=woothemes_framework_update' ) . '">', '</a>', '<a href="http://www.woothemes.com/updates/functions-changelog.txt" target="_blank" title="Changelog">', '</a>' ) . '</p></div>';
		}

		if ( $html != '' ) { echo $html; }
	} // End woo_framework_critical_update_notice()
}
?>