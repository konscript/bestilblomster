<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Custom fields for WordPress write panels.
 *
 * Add custom fields to various post types "Add" and "Edit" screens within WordPress.
 * Also processes the custom fields as post meta when the post is saved.
 *
 * @package WordPress
 * @subpackage WooFramework
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * - woothemes_metabox_create()
 * - woothemes_metabox_create_fields()
 * - woothemes_metabox_handle()
 * - woothemes_metabox_add()
 * - woothemes_metabox_fieldtypes()
 * - woothemes_uploader_custom_fields()
 * - woo_custom_enqueue()
 * - woo_custom_enqueue_css()
 */

/**
 * woothemes_metabox_create()
 *
 * Create the markup for the meta box.
 *
 * @access public
 * @param object $post
 * @param array $callback
 * @return void
 */
function woothemes_metabox_create( $post, $callback ) {
    global $post;

	// Allow child themes/plugins to act here.
	do_action( 'woothemes_metabox_create', $post, $callback );
	
	$template_to_show = $callback['args'];

    $woo_metaboxes = get_option( 'woo_custom_template', array() );

	// Array sanity check.
	if ( ! is_array( $woo_metaboxes ) ) { $woo_metaboxes = array(); }

    // Determine whether or not to display general fields.
    $display_general_fields = true;
    if ( count( $woo_metaboxes ) <= 0 ) {
        $display_general_fields = false;
    }

    $output = '';
    
    // Add nonce for custom fields.
    $output .= wp_nonce_field( 'wooframework-custom-fields', 'wooframework-custom-fields-nonce', true, false );

    if ( $callback['id'] == 'woothemes-settings' ) {
	    // Add tabs.
	    $output .= '<div class="wooframework-tabs">' . "\n";
	    
	    $output .= '<ul class="tabber hide-if-no-js">' . "\n";
	    	if ( $display_general_fields ) {
                $output .= '<li class="wf-tab-general"><a href="#wf-tab-general">' . __( 'General Settings', 'woothemes' ) . '</a></li>' . "\n";
            }
	    	
	    	// Allow themes/plugins to add tabs to WooFramework custom fields.
	    	$output .= apply_filters( 'wooframework_custom_field_tab_headings', '' );
	    $output .= '</ul>' . "\n";
    }
    
    if ( $display_general_fields ) {
        $output .= woothemes_metabox_create_fields( $woo_metaboxes, $callback, 'general' );

    }
    
    // Allow themes/plugins to add tabs to WooFramework custom fields.
    $output = apply_filters( 'wooframework_custom_field_tab_content', $output );
    
    $output .= '</div>' . "\n";
    
    echo $output;
} // End woothemes_metabox_create()

/**
 * woothemes_metabox_create_fields()
 *
 * Create markup for custom fields based on the given arguments.
 * 
 * @access public
 * @since 5.3.0
 * @param array $metaboxes
 * @param array $callback
 * @param string $token (default: 'general')
 * @return string $output
 */
function woothemes_metabox_create_fields ( $metaboxes, $callback, $token = 'general' ) {
	global $post;

    if ( ! is_array( $metaboxes ) ) { return; }

	// $template_to_show = $callback['args'];
	$template_to_show = $token;
	
	$output = '';
	
	$output .= '<div id="wf-tab-' . esc_attr( $token ) . '">' . "\n";
	$output .= '<table class="woo_metaboxes_table">'."\n";
    foreach ( $metaboxes as $k => $woo_metabox ) {
    
    	// Setup CSS classes to be added to each table row.
    	$row_css_class = 'woo-custom-field';
    	if ( ( $k + 1 ) == count( $metaboxes ) ) { $row_css_class .= ' last'; }
    
    	$woo_id = 'woothemes_' . $woo_metabox['name'];
    	$woo_name = $woo_metabox['name'];

    	if ( function_exists( 'woothemes_content_builder_menu' ) ) {
    		$metabox_post_type_restriction = $woo_metabox['cpt'][$post->post_type];
    	} else {
    		$metabox_post_type_restriction = 'undefined';
    	}

    	if ( ( $metabox_post_type_restriction != '' ) && ( $metabox_post_type_restriction == 'true' ) ) {
    		$type_selector = true;
    	} elseif ( $metabox_post_type_restriction == 'undefined' ) {
    		$type_selector = true;
    	} else {
    		$type_selector = false;
    	}

   		$woo_metaboxvalue = '';

    	if ( $type_selector ) {

    		if( isset( $woo_metabox['type'] ) && ( in_array( $woo_metabox['type'], woothemes_metabox_fieldtypes() ) ) ) {

        	    	$woo_metaboxvalue = get_post_meta($post->ID,$woo_name,true);

				}
				
				// Make sure slashes are stripped before output.
				foreach ( array( 'label', 'desc', 'std' ) as $k ) {
					if ( isset( $woo_metabox[$k] ) && ( $woo_metabox[$k] != '' ) ) {
						$woo_metabox[$k] = stripslashes( $woo_metabox[$k] );
					}
				}
				
        	    if ( $woo_metaboxvalue == '' && isset( $woo_metabox['std'] ) ) {

        	        $woo_metaboxvalue = $woo_metabox['std'];
        	    } 
        	    
        	    // Add a dynamic CSS class to each row in the table.
        	    $row_css_class .= ' woo-field-type-' . strtolower( $woo_metabox['type'] );
        	    
				if( $woo_metabox['type'] == 'info' ) {

        	        $output .= "\t".'<tr class="' . $row_css_class . '" style="background:#f8f8f8; font-size:11px; line-height:1.5em;">';
        	        $output .= "\t\t".'<th class="woo_metabox_names"><label for="'. esc_attr( $woo_id ) .'">'.$woo_metabox['label'].'</label></th>'."\n";
        	        $output .= "\t\t".'<td style="font-size:11px;">'.$woo_metabox['desc'].'</td>'."\n";
        	        $output .= "\t".'</tr>'."\n";

        	    }
        	    elseif( $woo_metabox['type'] == 'text' ) {

        	    	$add_class = ''; $add_counter = '';
        	    	if($template_to_show == 'seo'){$add_class = 'words-count'; $add_counter = '<span class="counter">0 characters, 0 words</span>';}
        	        $output .= "\t".'<tr class="' . $row_css_class . '">';
        	        $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.esc_attr( $woo_id ).'">'.$woo_metabox['label'].'</label></th>'."\n";
        	        $output .= "\t\t".'<td><input class="woo_input_text '.$add_class.'" type="'.$woo_metabox['type'].'" value="'.esc_attr( $woo_metaboxvalue ).'" name="'.$woo_name.'" id="'.esc_attr( $woo_id ).'"/>';
        	        $output .= '<span class="woo_metabox_desc">'.$woo_metabox['desc'] .' '. $add_counter .'</span></td>'."\n";
        	        $output .= "\t".'</tr>'."\n";

        	    }

        	    elseif ( $woo_metabox['type'] == 'textarea' ) {

        	   		$add_class = ''; $add_counter = '';
        	    	if( $template_to_show == 'seo' ){ $add_class = 'words-count'; $add_counter = '<span class="counter">0 characters, 0 words</span>'; }
        	        $output .= "\t".'<tr class="' . $row_css_class . '">';
        	        $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.$woo_metabox.'">'.$woo_metabox['label'].'</label></th>'."\n";
        	        $output .= "\t\t".'<td><textarea class="woo_input_textarea '.$add_class.'" name="'.$woo_name.'" id="'.esc_attr( $woo_id ).'">' . esc_textarea(stripslashes($woo_metaboxvalue)) . '</textarea>';
        	        $output .= '<span class="woo_metabox_desc">'.$woo_metabox['desc'] .' '. $add_counter.'</span></td>'."\n";
        	        $output .= "\t".'</tr>'."\n";

        	    }

        	    elseif ( $woo_metabox['type'] == 'calendar' ) {

        	        $output .= "\t".'<tr class="' . $row_css_class . '">';
        	        $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.$woo_metabox.'">'.$woo_metabox['label'].'</label></th>'."\n";
        	        $output .= "\t\t".'<td><input class="woo_input_calendar" type="text" name="'.$woo_name.'" id="'.esc_attr( $woo_id ).'" value="'.esc_attr( $woo_metaboxvalue ).'">';
        	        $output .= "\t\t" . '<input type="hidden" name="datepicker-image" value="' . get_template_directory_uri() . '/functions/images/calendar.gif" />';
        	        $output .= '<span class="woo_metabox_desc">'.$woo_metabox['desc'].'</span></td>'."\n";
        	        $output .= "\t".'</tr>'."\n";

        	    }

        	    elseif ( $woo_metabox['type'] == 'time' ) {

        	        $output .= "\t".'<tr>';
        	        $output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
        	        $output .= "\t\t".'<td><input class="woo_input_time" type="' . $woo_metabox['type'] . '" value="' . esc_attr( $woo_metaboxvalue ) . '" name="' . $woo_name . '" id="' . esc_attr( $woo_id ) . '"/>';
        	        $output .= '<span class="woo_metabox_desc">' . $woo_metabox['desc'] . '</span></td>'."\n";
        	        $output .= "\t".'</tr>'."\n";

        	    }
				
				elseif ( $woo_metabox['type'] == 'time_masked' ) {

        	        $output .= "\t".'<tr>';
        	        $output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
        	        $output .= "\t\t".'<td><input class="woo_input_time_masked" type="' . $woo_metabox['type'] . '" value="' . esc_attr( $woo_metaboxvalue ) . '" name="' . $woo_name . '" id="' . esc_attr( $woo_id ) . '"/>';
        	        $output .= '<span class="woo_metabox_desc">' . $woo_metabox['desc'] . '</span></td>'."\n";
        	        $output .= "\t".'</tr>'."\n";

        	    }
        	    
        	    elseif ( $woo_metabox['type'] == 'select' ) {

        	        $output .= "\t".'<tr class="' . $row_css_class . '">';
        	        $output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
        	        $output .= "\t\t".'<td><select class="woo_input_select" id="' . esc_attr( $woo_id ) . '" name="' . esc_attr( $woo_name ) . '">';
        	        $output .= '<option value="">Select to return to default</option>';

        	        $array = $woo_metabox['options'];

        	        if( $array ) {

        	            foreach ( $array as $id => $option ) {
        	                $selected = '';

        	                if( isset( $woo_metabox['default'] ) )  {
								if( $woo_metabox['default'] == $option && empty( $woo_metaboxvalue ) ) { $selected = 'selected="selected"'; }
								else  { $selected = ''; }
							}

        	                if( $woo_metaboxvalue == $option ){ $selected = 'selected="selected"'; }
        	                else  { $selected = ''; }

        	                $output .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . $option . '</option>';
        	            }
        	        }

        	        $output .= '</select><span class="woo_metabox_desc">' . $woo_metabox['desc'] . '</span></td>'."\n";
        	        $output .= "\t".'</tr>'."\n";
        	    }
        	    elseif ( $woo_metabox['type'] == 'select2' ) {

        	        $output .= "\t".'<tr class="' . $row_css_class . '">';
        	        $output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
        	        $output .= "\t\t".'<td><select class="woo_input_select" id="' . esc_attr( $woo_id ) . '" name="' . esc_attr( $woo_name ) . '">';
        	        $output .= '<option value="">Select to return to default</option>';

        	        $array = $woo_metabox['options'];

        	        if( $array ) {

        	            foreach ( $array as $id => $option ) {
        	                $selected = '';

        	                if( isset( $woo_metabox['default'] ) )  {
								if( $woo_metabox['default'] == $id && empty( $woo_metaboxvalue ) ) { $selected = 'selected="selected"'; }
								else  { $selected = ''; }
							}

        	                if( $woo_metaboxvalue == $id ) { $selected = 'selected="selected"'; }
        	                else  {$selected = '';}

        	                $output .= '<option value="'. esc_attr( $id ) .'" '. $selected .'>' . $option . '</option>';
        	            }
        	        }

        	        $output .= '</select><span class="woo_metabox_desc">'.$woo_metabox['desc'].'</span></td>'."\n";
        	        $output .= "\t".'</tr>'."\n";
        	    }

        	    elseif ( $woo_metabox['type'] == 'checkbox' ){

        	        if( $woo_metaboxvalue == 'true' ) { $checked = ' checked="checked"'; } else { $checked=''; }

        	        $output .= "\t".'<tr class="' . $row_css_class . '">';
        	        $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.esc_attr( $woo_id ).'">'.$woo_metabox['label'].'</label></th>'."\n";
        	        $output .= "\t\t".'<td><input type="checkbox" '.$checked.' class="woo_input_checkbox" value="true"  id="'.esc_attr( $woo_id ).'" name="'. esc_attr( $woo_name ) .'" />';
        	        $output .= '<span class="woo_metabox_desc" style="display:inline">'.$woo_metabox['desc'].'</span></td>'."\n";
        	        $output .= "\t".'</tr>'."\n";
        	    }

        	    elseif ( $woo_metabox['type'] == 'radio' ) {

        	    $array = $woo_metabox['options'];

        	    if( $array ) {

        	    $output .= "\t".'<tr class="' . $row_css_class . '">';
        	    $output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
        	    $output .= "\t\t".'<td>';

        	        foreach ( $array as $id => $option ) {
        	            if($woo_metaboxvalue == $id) { $checked = ' checked'; } else { $checked=''; }

        	                $output .= '<input type="radio" '.$checked.' value="' . $id . '" class="woo_input_radio"  name="'. esc_attr( $woo_name ) .'" />';
        	                $output .= '<span class="woo_input_radio_desc" style="display:inline">'. $option .'</span><div class="woo_spacer"></div>';
        	            }
        	            $output .= "\t".'</tr>'."\n";
        	         }
        	    } elseif ( $woo_metabox['type'] == 'images' ) {

				$i = 0;
				$select_value = '';
				$layout = '';

				foreach ( $woo_metabox['options'] as $key => $option ) {
					 $i++;

					 $checked = '';
					 $selected = '';
					 if( $woo_metaboxvalue != '' ) {
					 	if ( $woo_metaboxvalue == $key ) { $checked = ' checked'; $selected = 'woo-meta-radio-img-selected'; }
					 }
					 else {
					 	if ( isset( $option['std'] ) && $key == $option['std'] ) { $checked = ' checked'; }
						elseif ( $i == 1 ) { $checked = ' checked'; $selected = 'woo-meta-radio-img-selected'; }
						else { $checked = ''; }

					 }

						$layout .= '<div class="woo-meta-radio-img-label">';
						$layout .= '<input type="radio" id="woo-meta-radio-img-' . $woo_name . $i . '" class="checkbox woo-meta-radio-img-radio" value="' . esc_attr($key) . '" name="' . $woo_name . '" ' . $checked . ' />';
						$layout .= '&nbsp;' . esc_html($key) . '<div class="woo_spacer"></div></div>';
						$layout .= '<img src="' . esc_url( $option ) . '" alt="" class="woo-meta-radio-img-img '. $selected .'" onClick="document.getElementById(\'woo-meta-radio-img-'. esc_js( $woo_metabox["name"] . $i ) . '\').checked = true;" />';
					}

				$output .= "\t".'<tr class="' . $row_css_class . '">';
				$output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
				$output .= "\t\t".'<td class="woo_metabox_fields">';
				$output .= $layout;
				$output .= '<span class="woo_metabox_desc">' . $woo_metabox['desc'] . '</span></td>'."\n";
        	    $output .= "\t".'</tr>'."\n";

				}

        	    elseif( $woo_metabox['type'] == 'upload' )
        	    {
					if( isset( $woo_metabox['default'] ) ) $default = $woo_metabox['default'];
					else $default = '';

        	    	// Add support for the WooThemes Media Library-driven Uploader Module // 2010-11-09.
        	    	if ( function_exists( 'woothemes_medialibrary_uploader' ) ) {

        	    		$_value = $default;

        	    		$_value = get_post_meta( $post->ID, $woo_metabox['name'], true );

        	    		$output .= "\t".'<tr class="' . $row_css_class . '">';
	    	            $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.$woo_metabox['name'].'">'.$woo_metabox['label'].'</label></th>'."\n";
	    	            $output .= "\t\t".'<td class="woo_metabox_fields">'. woothemes_medialibrary_uploader( $woo_metabox['name'], $_value, 'postmeta', $woo_metabox['desc'], $post->ID );
	    	            $output .= '</td>'."\n";
	    	            $output .= "\t".'</tr>'."\n";

        	    	} else {

	    	            $output .= "\t".'<tr class="' . $row_css_class . '">';
	    	            $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.esc_attr( $woo_id ).'">'.$woo_metabox['label'].'</label></th>'."\n";
	    	            $output .= "\t\t".'<td class="woo_metabox_fields">'. woothemes_uploader_custom_fields( $post->ID, $woo_name, $default, $woo_metabox['desc'] );
	    	            $output .= '</td>'."\n";
	    	            $output .= "\t".'</tr>'."\n";

        	        }
        	    }
        	    
        	    // Timestamp field.
        	    elseif ( $woo_metabox['type'] == 'timestamp' ) {
        	    	$woo_metaboxvalue = get_post_meta($post->ID,$woo_name,true);
        	    	
					// Default to current UNIX timestamp.
					if ( $woo_metaboxvalue == '' ) {
						$woo_metaboxvalue = time();
					}
					
        	        $output .= "\t".'<tr class="' . $row_css_class . '">';
        	        $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.$woo_metabox.'">'.$woo_metabox['label'].'</label></th>'."\n";
        	        $output .= "\t\t".'<td><input type="hidden" name="datepicker-image" value="' . admin_url( 'images/date-button.gif' ) . '" /><input class="woo_input_calendar" type="text" name="'.$woo_name.'[date]" id="'.esc_attr( $woo_id ).'" value="' . esc_attr( date( 'm/d/Y', $woo_metaboxvalue ) ) . '">';
        	        
        	        $output .= ' <span class="woo-timestamp-at">' . __( '@', 'woothemes' ) . '</span> ';
        	        
        	        $output .= '<select name="' . $woo_name . '[hour]" class="woo-select-timestamp">' . "\n";
						for ( $i = 0; $i <= 23; $i++ ) {
							
							$j = $i;
							if ( $i < 10 ) {
								$j = '0' . $i;
							}
							
							$output .= '<option value="' . $i . '"' . selected( date( 'H', $woo_metaboxvalue ), $j, false ) . '>' . $j . '</option>' . "\n";
						}
					$output .= '</select>' . "\n";
					
					$output .= '<select name="' . $woo_name . '[minute]" class="woo-select-timestamp">' . "\n";
						for ( $i = 0; $i <= 59; $i++ ) {
							
							$j = $i;
							if ( $i < 10 ) {
								$j = '0' . $i;
							}
							
							$output .= '<option value="' . $i . '"' . selected( date( 'i', $woo_metaboxvalue ), $j, false ) .'>' . $j . '</option>' . "\n";
						}
					$output .= '</select>' . "\n";
					/*
					$output .= '<select name="' . $woo_name . '[second]" class="woo-select-timestamp">' . "\n";
						for ( $i = 0; $i <= 59; $i++ ) {
							
							$j = $i;
							if ( $i < 10 ) {
								$j = '0' . $i;
							}
							
							$output .= '<option value="' . $i . '"' . selected( date( 's', $woo_metaboxvalue ), $j, false ) . '>' . $j . '</option>' . "\n";
						}
					$output .= '</select>' . "\n";
        	        */
        	        $output .= '<span class="woo_metabox_desc">'.$woo_metabox['desc'].'</span></td>'."\n";
        	        $output .= "\t".'</tr>'."\n";

        	    }
        } // End IF Statement
    }

    $output .= '</table>'."\n\n";
    $output .= '</div><!--/#wf-tab-' . $token . '-->' . "\n\n";
    
    return $output;
} // End woothemes_metabox_create_fields()

/**
 * woothemes_metabox_handle()
 *
 * Handle the saving of the custom fields.
 * 
 * @access public
 * @param int $post_id
 * @return void
 */
function woothemes_metabox_handle( $post_id ) {
    $pID = '';
    global $globals, $post;

    if ( 'page' == $_POST['post_type'] ) {  
        if ( ! current_user_can( 'edit_page', $post_id ) ) { 
            return $post_id;
        }
    } else {  
        if ( ! current_user_can( 'edit_post', $post_id ) ) { 
            return $post_id;
        }
    }

    $woo_metaboxes = get_option( 'woo_custom_template', array() );

    // Sanitize post ID.
    if( isset( $_POST['post_ID'] ) ) {
		$pID = intval( $_POST['post_ID'] );
    }

    // Don't continue if we don't have a valid post ID.
    if ( $pID == 0 ) return;

    $upload_tracking = array();

    if ( isset( $_POST['action'] ) && $_POST['action'] == 'editpost' ) {
        if ( ( get_post_type() != '' ) && ( get_post_type() != 'nav_menu_item' ) && wp_verify_nonce( $_POST['wooframework-custom-fields-nonce'], 'wooframework-custom-fields' ) ) {
            foreach ( $woo_metaboxes as $k => $woo_metabox ) { // On Save.. this gets looped in the header response and saves the values submitted
                if( isset( $woo_metabox['type'] ) && ( in_array( $woo_metabox['type'], woothemes_metabox_fieldtypes() ) ) ) {
    				$var = $woo_metabox['name'];

    				// Get the current value for checking in the script.
    			    $current_value = '';
    			    $current_value = get_post_meta( $pID, $var, true );

    				if ( isset( $_POST[$var] ) ) {
    					// Sanitize the input.
    					$posted_value = '';
    					$posted_value = $_POST[$var];

    					 // If it doesn't exist, add the post meta.
    					if(get_post_meta( $pID, $var ) == "") {
    						add_post_meta( $pID, $var, $posted_value, true );
    					}
    					// Otherwise, if it's different, update the post meta.
    					elseif( $posted_value != get_post_meta( $pID, $var, true ) ) {
    						update_post_meta( $pID, $var, $posted_value );
    					}
    					// Otherwise, if no value is set, delete the post meta.
    					elseif($posted_value == "") {
    						delete_post_meta( $pID, $var, get_post_meta( $pID, $var, true ) );
    					} // End IF Statement
    				} elseif ( ! isset( $_POST[$var] ) && $woo_metabox['type'] == 'checkbox' ) {
    					update_post_meta( $pID, $var, 'false' );
    				} else {
    					delete_post_meta( $pID, $var, $current_value ); // Deletes check boxes OR no $_POST
    				} // End IF Statement

                } else if ( $woo_metabox['type'] == 'timestamp' ) {
                	// Timestamp save logic.
                	
                	// It is assumed that the data comes back in the following format:
    				// date: month/day/year
    				// hour: int(2)
    				// minute: int(2)
    				// second: int(2)
    				
    				$var = $woo_metabox['name'];
    				
    				// Format the data into a timestamp.
    				$date = $_POST[$var]['date'];
    				
    				$hour = $_POST[$var]['hour'];
    				$minute = $_POST[$var]['minute'];
    				// $second = $_POST[$var]['second'];
    				$second = '00';
    				
    				$day = substr( $date, 3, 2 );
    				$month = substr( $date, 0, 2 );
    				$year = substr( $date, 6, 4 );
    				
    				$timestamp = mktime( $hour, $minute, $second, $month, $day, $year );
    				
    				update_post_meta( $pID, $var, $timestamp );
                } elseif( isset( $woo_metabox['type'] ) && $woo_metabox['type'] == 'upload' ) { // So, the upload inputs will do this rather
    				$id = $woo_metabox['name'];
    				$override['action'] = 'editpost';

    			    if(!empty($_FILES['attachement_'.$id]['name'])){ //New upload
    			    $_FILES['attachement_'.$id]['name'] = preg_replace( '/[^a-zA-Z0-9._\-]/', '', $_FILES['attachement_'.$id]['name']);
    			           $uploaded_file = wp_handle_upload($_FILES['attachement_' . $id ],$override);
    			           $uploaded_file['option_name']  = $woo_metabox['label'];
    			           $upload_tracking[] = $uploaded_file;
    			           update_post_meta( $pID, $id, $uploaded_file['url'] );
    			    } elseif ( empty( $_FILES['attachement_'.$id]['name'] ) && isset( $_POST[ $id ] ) ) {
    			       	// Sanitize the input.
    					$posted_value = '';
    					$posted_value = $_POST[$id];

    			        update_post_meta($pID, $id, $posted_value);
    			    } elseif ( $_POST[ $id ] == '' )  {
    			    	delete_post_meta( $pID, $id, get_post_meta( $pID, $id, true ) );
    			    } // End IF Statement

    			} // End IF Statement

                   // Error Tracking - File upload was not an Image
                   update_option( 'woo_custom_upload_tracking', $upload_tracking );
                } // End FOREACH Loop
            }
        }
} // End woothemes_metabox_handle()

/**
 * woothemes_metabox_add()
 *
 * Add meta boxes for the WooFramework's custom fields.
 * 
 * @access public
 * @since 1.0.0
 * @return void
 */
function woothemes_metabox_add () {
	$woo_metaboxes = get_option( 'woo_custom_template', array() );
    if ( function_exists( 'add_meta_box' ) ) {
    	if ( function_exists( 'get_post_types' ) ) {
    		$custom_post_list = get_post_types();
    		// Get the theme name for use in multiple meta boxes.
    		$theme_name = get_option( 'woo_themename' );

			foreach ( $custom_post_list as $type ) {

				$settings = array(
									'id' => 'woothemes-settings',
									'title' => sprintf( __( '%s Custom Settings', 'woothemes' ), $theme_name ), 
									'callback' => 'woothemes_metabox_create',
									'page' => $type,
									'priority' => 'normal',
									'callback_args' => ''
								);

				// Allow child themes/plugins to filter these settings.
				$settings = apply_filters( 'woothemes_metabox_settings', $settings, $type, $settings['id'] );
				add_meta_box( $settings['id'], $settings['title'], $settings['callback'], $settings['page'], $settings['priority'], $settings['callback_args'] );
				// if(!empty($woo_metaboxes)) Temporarily Removed
			}
    	} else {
    		add_meta_box( 'woothemes-settings', sprintf( __( '%s Custom Settings', 'woothemes' ), $theme_name ), 'woothemes_metabox_create', 'post', 'normal' );
        	add_meta_box( 'woothemes-settings', sprintf( __( '%s Custom Settings', 'woothemes' ), $theme_name ), 'woothemes_metabox_create', 'page', 'normal' );
    	}
    }
} // End woothemes_metabox_add()

/**
 * woothemes_metabox_fieldtypes()
 *
 * Return a filterable array of supported field types.
 *
 * @access public
 * @author Matty
 * @return void
 */
function woothemes_metabox_fieldtypes() {
	return apply_filters( 'woothemes_metabox_fieldtypes', array( 'text', 'calendar', 'time', 'time_masked', 'select', 'select2', 'radio', 'checkbox', 'textarea', 'images' ) );
} // End woothemes_metabox_fieldtypes()

/**
 * woothemes_uploader_custom_fields()
 *
 * Create markup for outputting the custom upload field as a custom field.
 * 
 * @access public
 * @param int $pID
 * @param string $id
 * @param string $std
 * @param string $desc
 * @return void
 */
function woothemes_uploader_custom_fields( $pID, $id, $std, $desc ) {
    $upload = get_post_meta( $pID, $id, true );
	$href = cleanSource( $upload );
	$uploader = '';
    $uploader .= '<input class="woo_input_text" name="' . $id . '" type="text" value="' . esc_attr( $upload ) . '" />';
    $uploader .= '<div class="clear"></div>'."\n";
    $uploader .= '<input type="file" name="attachement_' . $id . '" />';
    $uploader .= '<input type="submit" class="button button-highlighted" value="Save" name="save"/>';
    if ( $href )
		$uploader .= '<span class="woo_metabox_desc">' . $desc . '</span></td>' . "\n" . '<td class="woo_metabox_image"><a href="' . $upload . '"><img src="' . get_template_directory_uri() . '/functions/thumb.php?src=' . $href . '&w=150&h=80&zc=1" alt="" /></a>';

return $uploader;
} // End woothemes_uploader_custom_fields()

if ( ! function_exists( 'woo_custom_enqueue' ) ) {
/**
 * woo_custom_enqueue()
 * 
 * Enqueue JavaScript files used with the custom fields.
 *
 * @access public
 * @param string $hook
 * @since 2.6.0
 * @return void
 */
function woo_custom_enqueue ( $hook ) {
	wp_register_script( 'jquery-ui-datepicker', get_template_directory_uri() . '/functions/js/ui.datepicker.js', array( 'jquery-ui-core' ) );
	wp_register_script( 'jquery-input-mask', get_template_directory_uri() . '/functions/js/jquery.maskedinput.js', array( 'jquery' ), '1.3' );
	wp_register_script( 'woo-custom-fields', get_template_directory_uri() . '/functions/js/woo-custom-fields.js', array( 'jquery', 'jquery-ui-tabs' ) );
		
  	if ( in_array( $hook, array( 'post.php', 'post-new.php', 'page-new.php', 'page.php' ) ) ) {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-input-mask' );
  		wp_enqueue_script( 'woo-custom-fields' );
  	}
} // End woo_custom_enqueue()
}

if ( ! function_exists( 'woo_custom_enqueue_css' ) ) {
/**
 * woo_custom_enqueue_css()
 *
 * Enqueue CSS files used with the custom fields.
 *
 * @access public
 * @author Matty
 * @since 4.8.0
 * @return void
 */
function woo_custom_enqueue_css () {
	global $pagenow;
	wp_register_style( 'woo-custom-fields', get_template_directory_uri() . '/functions/css/woo-custom-fields.css' );
	wp_register_style( 'jquery-ui-datepicker', get_template_directory_uri() . '/functions/css/jquery-ui-datepicker.css' );
	
	if ( in_array( $pagenow, array( 'post.php', 'post-new.php', 'page-new.php', 'page.php' ) ) ) {
		wp_enqueue_style( 'woo-custom-fields' );
		wp_enqueue_style( 'jquery-ui-datepicker' );
	}
} // End woo_custom_enqueue_css()
}

/**
 * Specify action hooks for the functions above.
 *
 * @access public
 * @since 1.0.0
 * @return void
 */
add_action( 'admin_enqueue_scripts', 'woo_custom_enqueue', 10, 1 );
add_action( 'admin_print_styles', 'woo_custom_enqueue_css', 10 );
add_action( 'edit_post', 'woothemes_metabox_handle', 10 );
add_action( 'admin_menu', 'woothemes_metabox_add', 10 ); // Triggers woothemes_metabox_create()
?>