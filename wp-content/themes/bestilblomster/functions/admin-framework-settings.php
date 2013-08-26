<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------*/
/* Framework Settings page - woothemes_framework_settings_page */
/*-----------------------------------------------------------------------------------*/

function woothemes_framework_settings_page() {
    $manualurl =  get_option( 'woo_manual' );
	$shortname =  'framework_woo';

    //GET themes update RSS feed and do magic
	include_once(ABSPATH . WPINC . '/feed.php' );

	$pos = strpos( $manualurl, 'documentation' );
	$theme_slug = str_replace( "/", '', substr( $manualurl, ( $pos + 13 ) ) ); //13 for the word documentation

    //add filter to make the rss read cache clear every 4 hours
    add_filter( 'wp_feed_cache_transient_lifetime', create_function( '$a', 'return 14400;' ) );

	$framework_options = array();

	$framework_options[] = array( 	'name' => __( 'Admin Settings', 'woothemes' ),
									'icon' => 'general',
									'type' => 'heading' );

	$framework_options[] = array( 	'name' => __( 'Super User (username)', 'woothemes' ),
									'desc' => sprintf( __( 'Enter your %s to hide the Framework Settings and Update Framework from other users. Can be reset from the %s under %s.', 'woothemes' ), '<strong>' . __( 'username', 'woothemes' ) . '</strong>', '<a href="' . admin_url( 'options.php' ) . '">' . __( 'WP options page', 'woothemes' ) . '</a>', '<code>framework_woo_super_user</code>' ),
									'id' => $shortname . '_super_user',
									'std' => '',
									'class' => 'text',
									'type' => 'text' );

	$framework_options[] = array( 	'name' => __( 'Disable Backup Settings Menu Item', 'woothemes' ),
									'desc' => sprintf( __( 'Disable the %s menu item in the theme menu.', 'woothemes' ), '<strong>' . __( 'Backup Settings', 'woothemes' ) . '</strong>' ),
									'id' => $shortname . '_backupmenu_disable',
									'std' => '',
									'type' => 'checkbox' );

	$framework_options[] = array( 	'name' => __( 'Theme Update Notification', 'woothemes' ),
									'desc' => __( 'This will enable notices on your theme options page that there is an update available for your theme.', 'woothemes' ),
									'id' => $shortname . '_theme_version_checker',
									'std' => '',
									'type' => 'checkbox' );
									
	$framework_options[] = array( 	'name' => __( 'WooFramework Update Notification', 'woothemes' ),
									'desc' => __( 'This will enable notices on your theme options page that there is an update available for the WooFramework.', 'woothemes' ),
									'id' => $shortname . '_framework_version_checker',
									'std' => '',
									'type' => 'checkbox' );

	$framework_options[] = array( 	'name' => __( 'Theme Settings', 'woothemes' ),
									'icon' => 'general',
									'type' => 'heading' );

	$framework_options[] = array( 	'name' => __( 'Remove Generator Meta Tags', 'woothemes' ),
									'desc' => __( 'This disables the output of generator meta tags in the HEAD section of your site.', 'woothemes' ),
									'id' => $shortname . '_disable_generator',
									'std' => '',
									'type' => 'checkbox' );

	$framework_options[] = array( 	'name' => __( 'Image Placeholder', 'woothemes' ),
									'desc' => __( 'Set a default image placeholder for your thumbnails. Use this if you want a default image to be shown if you haven\'t added a custom image to your post.', 'woothemes' ),
									'id' => $shortname . '_default_image',
									'std' => '',
									'type' => 'upload' );

	$framework_options[] = array( 	'name' => __( 'Disable Shortcodes Stylesheet', 'woothemes' ),
									'desc' => __( 'This disables the output of shortcodes.css in the HEAD section of your site.', 'woothemes' ),
									'id' => $shortname . '_disable_shortcodes',
									'std' => '',
									'type' => 'checkbox' );

	$framework_options[] = array( 	'name' => __( 'Output "Tracking Code" Option in Header', 'woothemes' ),
									'desc' => sprintf( __( 'This will output the %s option in your header instead of the footer of your website.', 'woothemes' ), '<strong>' . __( 'Tracking Code', 'woothemes' ) . '</strong>' ),
									'id' => $shortname . '_move_tracking_code',
									'std' => 'false',
									'type' => 'checkbox' );

	$framework_options[] = array( 	'name' => __( 'Branding', 'woothemes' ),
									'icon' => 'misc',
									'type' => 'heading' );

	$framework_options[] = array( 	'name' => __( 'Options panel header', 'woothemes' ),
									'desc' => __( 'Change the header image for the WooThemes Backend.', 'woothemes' ),
									'id' => $shortname . '_backend_header_image',
									'std' => '',
									'type' => 'upload' );

	$framework_options[] = array( 	'name' => __( 'Options panel icon', 'woothemes' ),
									'desc' => __( 'Change the icon image for the WordPress backend sidebar.', 'woothemes' ),
									'id' => $shortname . '_backend_icon',
									'std' => '',
									'type' => 'upload' );

	$framework_options[] = array( 	'name' => __( 'WordPress login logo', 'woothemes' ),
									'desc' => __( 'Change the logo image for the WordPress login page.', 'woothemes' ) . '<br /><br />' . __( 'Optimal logo size is 274x63px', 'woothemes' ),
									'id' => $shortname . '_custom_login_logo',
									'std' => '',
									'type' => 'upload' );

	$framework_options[] = array( 	'name' => __( 'WordPress login URL', 'woothemes' ),
									'desc' => __( 'Change the URL that the logo image on the WordPress login page links to.', 'woothemes' ),
									'id' => $shortname . '_custom_login_logo_url',
									'std' => '',
									'class' => 'text',
									'type' => 'text' );
									
	$framework_options[] = array( 	'name' => __( 'WordPress login logo Title', 'woothemes' ),
									'desc' => __( 'Change the title of the logo image on the WordPress login page.', 'woothemes' ),
									'id' => $shortname . '_custom_login_logo_title',
									'std' => '',
									'class' => 'text',
									'type' => 'text' );

/*
	$framework_options[] = array( 	'name' => __( 'Font Stacks (Beta)', 'woothemes' ),
									'icon' => 'typography',
									'type' => 'heading' );

	$framework_options[] = array( 	'name' => __( 'Font Stack Builder', 'woothemes' ),
									'desc' => __( 'Use the font stack builder to add your own custom font stacks to your theme.
									To create a new stack, fill in the name and a CSS ready font stack.
									Once you have added a stack you can select it from the font menu on any of the
									Typography settings in your theme options.', 'woothemes' ),
									'id' => $shortname . '_font_stack',
									'std' => 'Added Font Stacks',
									'type' => 'string_builder" );
*/

	global $wp_version;

	if ( $wp_version >= '3.1' ) {

	$framework_options[] = array( 	'name' => __( 'WordPress Toolbar', 'woothemes' ),
									'icon' => 'header',
									'type' => 'heading' );

	$framework_options[] = array( 	'name' => __( 'Disable WordPress Toolbar', 'woothemes' ),
									'desc' => __( 'Disable the WordPress Toolbar.', 'woothemes' ),
									'id' => $shortname . '_admin_bar_disable',
									'std' => '',
									'type' => 'checkbox' );

	$framework_options[] = array( 	'name' => __( 'Enable the WooFramework Toolbar enhancements', 'woothemes' ),
									'desc' => __( 'Enable several WooFramework-specific enhancements to the WordPress Toolbar, such as custom navigation items for "Theme Options".', 'woothemes' ),
									'id' => $shortname . '_admin_bar_enhancements',
									'std' => '',
									'type' => 'checkbox' );

	}

	// PressTrends Integration
	if ( defined( 'WOO_PRESSTRENDS_THEMEKEY' ) ) {
		$framework_options[] = array( 	'name' => __( 'PressTrends', 'woothemes' ),
										'icon' => 'presstrends',
										'type' => 'heading' );
									
		$framework_options[] = array( 	'name' => __( 'Enable PressTrends Tracking', 'woothemes' ),
										'desc' => __( 'Enable sending of usage data to PressTrends.', 'woothemes' ),
										'id' => $shortname . '_presstrends_enable',
										'std' => 'false',
										'type' => 'checkbox' );
	
		$framework_options[] = array( 	'name' => __( 'What is PressTrends?', 'woothemes' ),
										'desc' => '',
										'id' => $shortname . '_presstrends_info',
										'std' => sprintf( __( 'PressTrends is a simple usage tracker that allows us to see how our customers are using WooThemes themes - so that we can help improve them for you. %sNone%s of your personal data is sent to PressTrends.%sFor more information, please view the PressTrends %s.', 'woothemes' ), '<strong>', '</strong>', '<br /><br />', '<a href="http://presstrends.io/privacy" target="_blank">' . __( 'privacy policy', 'woothemes' ) . '</a>' ),
										'type' => 'info' );
	}

    update_option( 'woo_framework_template', $framework_options );

	?>

    <div class="wrap" id="woo_container">
    <?php do_action( 'wooframework_wooframeworksettings_container_inside' ); ?>
    <div id="woo-popup-save" class="woo-save-popup"><div class="woo-save-save"><?php _e( 'Options Updated', 'woothemes' ); ?></div></div>
    <div id="woo-popup-reset" class="woo-save-popup"><div class="woo-save-reset"><?php _e( 'Options Reset', 'woothemes' ); ?></div></div>
        <form action='' enctype="multipart/form-data" id="wooform" method="post">
        <?php
	    	// Add nonce for added security.
	    	if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'wooframework-framework-options-update' ); } // End IF Statement

	    	$woo_nonce = '';

	    	if ( function_exists( 'wp_create_nonce' ) ) { $woo_nonce = wp_create_nonce( 'wooframework-framework-options-update' ); } // End IF Statement

	    	if ( $woo_nonce == '' ) {} else {

	    ?>
	    	<input type="hidden" name="_ajax_nonce" value="<?php echo $woo_nonce; ?>" />
	    <?php

	    	} // End IF Statement
	    ?>
            <div id="header">
                <div class="logo">
                <?php if( get_option( 'framework_woo_backend_header_image' ) ) { ?>
                <img alt="" src="<?php echo get_option( 'framework_woo_backend_header_image' ); ?>"/>
                <?php } else { ?>
                <img alt="WooThemes" src="<?php echo get_template_directory_uri(); ?>/functions/images/logo.png"/>
                <?php } ?>
                </div>
                <div class="theme-info">
                	<?php wooframework_display_theme_version_data(); ?>
                </div>
                <div class="clear"></div>
            </div>
            <div id="support-links">
               <ul>
				<li class="changelog"><a title="Theme Changelog" href="<?php echo esc_url( $manualurl ); ?>#Changelog"><?php _e( 'View Changelog', 'woothemes' ); ?></a></li>
				<li class="docs"><a title="Theme Documentation" href="<?php echo esc_url( $manualurl ); ?>"><?php _e( 'View Theme Documentation', 'woothemes' ); ?></a></li>
				<li class="forum"><a href="<?php echo esc_url( 'http://support.woothemes.com/' ); ?>" target="_blank"><?php _e( 'Visit Support Desk', 'woothemes' ); ?></a></li>
                <li class="right"><img style="display:none" src="<?php echo esc_url( get_template_directory_uri() . '/functions/images/loading-top.gif' ); ?>" class="ajax-loading-img ajax-loading-img-top" alt="Working..." /><a href="#" id="expand_options">[+]</a> <input type="submit" value="Save All Changes" class="button submit-button" /></li>
			</ul>
            </div>
            <?php $return = woothemes_machine( $framework_options ); ?>
            <div id="main">
                <div id="woo-nav">
                    <ul>
                        <?php echo $return[1]; ?>
                    </ul>
                </div>
                <div id="content">
   				<?php echo $return[0]; ?>
                </div>
                <div class="clear"></div>

            </div>
            <div class="save_bar_top">
            <input type="hidden" name="woo_save" value="save" />
            <img style="display:none" src="<?php echo get_template_directory_uri(); ?>/functions/images/loading-bottom.gif" class="ajax-loading-img ajax-loading-img-bottom" alt="<?php esc_attr_e( 'Working...', 'woothemes' ); ?>" />
            <input type="submit" value="<?php esc_attr_e( 'Save All Changes', 'woothemes' ); ?>" class="button submit-button" />
            </form>

            <form action="<?php echo esc_attr( $_SERVER['REQUEST_URI'] ) ?>" method="post" style="display:inline" id="wooform-reset">
            <?php
		    	// Add nonce for added security.
		    	if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'wooframework-framework-options-reset' ); } // End IF Statement

		    	$woo_nonce = '';

		    	if ( function_exists( 'wp_create_nonce' ) ) { $woo_nonce = wp_create_nonce( 'wooframework-framework-options-reset' ); } // End IF Statement

		    	if ( $woo_nonce == '' ) {} else {

		    ?>
		    	<input type="hidden" name="_ajax_nonce" value="<?php echo $woo_nonce; ?>" />
		    <?php

		    	} // End IF Statement
		    ?>
            <span class="submit-footer-reset">
<!--             <input name="reset" type="submit" value="<?php esc_attr_e( 'Reset Options', 'woothemes' ); ?>" class="button submit-button reset-button" onclick="return confirm( '<?php esc_attr_e( 'Click OK to reset. Any settings will be lost!', 'woothemes' ); ?>' );" /> -->
            <input type="hidden" name="woo_save" value="reset" />
            </span>
        	</form>


            </div>

    <div style="clear:both;"></div>
    </div><!--wrap-->
<?php } ?>