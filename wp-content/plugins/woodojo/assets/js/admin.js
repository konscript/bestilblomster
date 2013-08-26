/**
 * WooDojo Admin JavaScript
 *
 * All JavaScript logic for the WooDojo admin interface.
 * @since 1.0.0
 *
 * - ajax_component_toggle()
 * - section_switcher()
 * - toggle_component_display()
 * - init_component_display_toggle()
 * - global_component_toggle()
 * - deep_copy()
 */

(function ($) {

  WooDojoAdmin = {
  
/**
 * ajax_component_toggle()
 *
 * @since 1.0.0
 */
 
 	ajax_component_toggle: function () {
 		$( 'input.button-primary.component-control-save:not(.download):not(.purchase)' ).click( function ( e ) {
 		var thisObj = $( this );
	 	var ajaxLoaderIcon = jQuery( this ).parent().find( '.ajax-loading' );
	 	ajaxLoaderIcon.css( 'visibility', 'visible' ).fadeTo( 'slow', 1, function () {
	 		var type = thisObj.parent().find( 'input[name="component-type"]' ).val();
	 		
	 		// Determine whether or not to activate/deactivate the component.
	 		var taskObj = thisObj.parent().find( 'input[name="deactivate-component"]' );
	 		
	 		if ( ! taskObj.length ) {
	 			var taskObj = thisObj.parent().find( 'input[name="activate-component"]' );
	 		}
	 		
	 		var taskType = taskObj.attr( 'name' );
	 		var componentToken = taskObj.val();
	 		
	 		var customStrings = {};

	 		// Perform the AJAX call.	
			jQuery.post(
				ajaxurl, 
				{ 
					action : 'woodojo_component_toggle', 
					woodojo_component_toggle_nonce : woodojo_localized_data.woodojo_component_toggle_nonce, 
					type: type, 
					task: taskType, 
					component: componentToken
				},
				function( response ) {
					ajaxLoaderIcon.fadeTo( 'slow', 0, function () {
						jQuery( this ).css( 'visibility', 'hidden' );

						customStrings = WooDojoAdmin.deep_copy( woodojo_localized_data ); // Make a true copy of the object, rather than by reference.
						
						// Do string replacement to include the component name in the message.
						var titleText = thisObj.parents( '.widget' ).find( '.widget-title .title' ).text();
						for ( i in customStrings ) {
							customStrings[i] = customStrings[i].replace( '%s ', titleText + ' ' );
							customStrings[i] = customStrings[i].replace( ' %s', ' ' + titleText );
						}
						
						if ( response == true ) {
							thisObj.toggleClass( 'enable' ).toggleClass( 'disable' );
							// Apply changes for deactivation (deactivation -> activation).
							if ( taskType == 'deactivate-component' ) {
								thisObj.attr( 'value', customStrings.enable );
								thisObj.parents( 'div.widget' ).removeClass( 'enabled' ).addClass( 'disabled' ).find( 'span.status-label' ).text( customStrings.disabled );
								thisObj.parents( 'div.widget' ).find( 'input[name="deactivate-component"]' ).attr( 'name', 'activate-component' );
								
								var noticeMessage = $( '<div />' ).addClass( 'updated' ).text( customStrings.disabled_successfully );
							} else {
							// Apply changes for activation (activation -> deactivation).
								thisObj.attr( 'value', woodojo_localized_data.disable );
								thisObj.parents( 'div.widget' ).removeClass( 'disabled' ).addClass( 'enabled' ).find( 'span.status-label' ).text( customStrings.enabled );
								thisObj.parents( 'div.widget' ).find( 'input[name="activate-component"]' ).attr( 'name', 'deactivate-component' );
								
								var noticeMessage = $( '<div />' ).addClass( 'updated' ).text( customStrings.enabled_successfully );
							}

							// Toggle the settings link, if it exists.
							if ( thisObj.siblings( '.settings-link' ).length ) {
								thisObj.siblings( '.settings-link' ).toggleClass( 'hidden' );
								if ( taskType == 'deactivate-component' ) {
									var settingsURL = thisObj.siblings( '.settings-link' ).find( 'a' ).attr( 'href' );
									var urlBits = settingsURL.split( '?' );
									if ( jQuery( '#adminmenu a[href*="' + urlBits[1] + '"]' ).length ) {
										jQuery( '#adminmenu a[href*="' + urlBits[1] + '"]' ).parent( 'li' ).remove();
									}
								}
							}
						} else {
							// There was an error. Notify the user.
							if ( taskType == 'deactivate-component' ) {
								var noticeMessage = $( '<div />' ).addClass( 'error' ).text( customStrings.diabled_error );
							} else {
								var noticeMessage = $( '<div />' ).addClass( 'error' ).text( customStrings.enabled_error );
							}
						}

						// Display the notice message after the button.
						thisObj.parents( '.module-inside' ).prepend( noticeMessage );
						noticeMessage.delay( 1000 ).fadeTo( 'slow', 0, function () {
							noticeMessage.remove();
						});
					});
				}	
			);
	 	});
	 	
	 	return false;
	 });
 	}, // End ajax_component_toggle()
 	
 	section_switcher: function () {
 		$( '#woodojo .subsubsub a.tab' ).click( function ( e ) {
 			// Move the "current" CSS class.
 			$( this ).parents( '.subsubsub' ).find( '.current' ).removeClass( 'current' );
 			$( this ).addClass( 'current' );
 		
 			// If "All" is clicked, show all.
 			if ( $( this ).hasClass( 'all' ) ) {
 				$( '#woodojo .widgets-holder-wrap' ).show();
 				$( '#woodojo .widgets-holder-wrap .widget' ).show();
 				
 				return false;
 			}

 			// If "Updates Available" is clicked, show only those with updates.
 			if ( $( this ).hasClass( 'has-upgrade' ) ) {
 				$( '#woodojo .widget' ).hide();
 				$( '#woodojo .widget.has-upgrade' ).show();

 				$( '.widgets-holder-wrap' ).each( function ( i ) {
 					if ( ! $( this ).find( '.has-upgrade' ).length ) {
 						$( this ).hide();
 					} else {
 						$( this ).show();
 					}
 				});
 				
 				return false;
 			} else {
 				$( '#woodojo .widget' ).show(); // Restore all widgets.
 			}
 			
 			// If the link is a tab, show only the specified tab.
 			var toShow = $( this ).attr( 'href' );
 			$( '.widgets-holder-wrap:not(' + toShow + ')' ).hide();
 			$( '.widgets-holder-wrap' + toShow ).show();
 			
 			return false;
 		});
 	}, // End section_switcher()
 	
 	/**
 	 * toggle_component_display function.
 	 *
 	 * @description Toggles a component open or closed and runs an AJAX call to save the information.
 	 * @access public
 	 * @param object obj
 	 */
 	toggle_component_display: function ( obj ) {
 		var status = 'closed';
 		
 		if ( obj.hasClass( 'closed' ) ) {
 			obj.addClass( 'open' ).removeClass( 'closed' );
 			status = 'open';
 		} else {
 			obj.addClass( 'closed' ).removeClass( 'open' );
 		}
 		
 		var componentToken = obj.attr( 'id' ).replace( '#', '' );

 		// Perform the AJAX call.	
		jQuery.post(
			ajaxurl, 
			{ 
				action : 'woodojo_component_display_toggle', 
				woodojo_component_toggle_nonce : woodojo_localized_data.woodojo_component_toggle_nonce, 
				component: componentToken, 
				status: status
			},
			function( response ) {}
		);
 	}, // End toggle_component_display()
 	
 	/**
 	 * init_component_display_toggle function.
 	 * 
 	 * @access public
 	 * @return void
 	 */
 	init_component_display_toggle: function () {
 		$( '#woodojo .widget-action[href="#close-component"]' ).click( function ( e ) {
 			WooDojoAdmin.toggle_component_display( $( this ).parents( '.widget' ) );
 			return false;
 		});
 	},  // End init_component_display_toggle()
 	
 	/**
 	 * global_component_toggle function.
 	 * 
 	 * @access public
 	 * @return void
 	 */
 	global_component_toggle: function () {
 		$( '#woodojo .open-close-all a' ).click( function ( e ) {
 			var status = 'closed';
 			
 			if ( $( this ).attr( 'href' ) == '#open-all' ) {
 				status = 'open';
 			}
 			
 			var components = [];
	 		$( '#woodojo .widget' ).each( function ( i ) {
	 			var obj = $( this );
	 			var componentToken = obj.attr( 'id' ).replace( '#', '' );
	 			components.push( componentToken );
	 			
	 			if ( status == 'open' ) {
		 			obj.addClass( 'open' ).removeClass( 'closed' );
		 		} else {
		 			obj.addClass( 'closed' ).removeClass( 'open' );
		 		}
	 		});
	
	 		// Perform the AJAX call.	
			jQuery.post(
				ajaxurl, 
				{ 
					action : 'woodojo_component_display_toggle', 
					woodojo_component_toggle_nonce : woodojo_localized_data.woodojo_component_toggle_nonce, 
					component: components, 
					status: status
				},
				function( response ) {}
			);
 			return false;
 		});
 	},  // End global_component_toggle()

 	/**
 	 * init_upgrade_confirmation function.
 	 * 
 	 * @access public
 	 * @return void
 	 */
 	init_upgrade_confirmation: function () {
 		$( '#woodojo .upgrade-link' ).click( function () {

 			var title = $( this ).parents( '.widget-title' ).find( '.title' ).text();

 			customStrings = WooDojoAdmin.deep_copy( woodojo_localized_data ); // Make a true copy of the object, rather than by reference.

 			customStrings['upgrade_confirmation'] = customStrings['upgrade_confirmation'].replace( '%s', title );

 			var confirmation = confirm( customStrings['upgrade_confirmation'] );

 			if ( ! confirmation ) {
 				return false;
 			}
 		});
 	}, // End init_upgrade_confirmation()
 	
 	/**
 	 * deep_copy function.
 	 *
 	 * @description Build a deep copy of an opject, rather than passing it by reference.
 	 * @source http://stackoverflow.com/questions/3284285/make-object-not-pass-by-reference
 	 * @access public
 	 * @param object obj
 	 * @return object retVal
 	 */
 	deep_copy: function ( obj ) {
	    if (typeof obj !== "object") return obj;
	    if (obj.constructor === RegExp) return obj;
	
	    var retVal = new obj.constructor();
	    for (var key in obj) {
	        if (!obj.hasOwnProperty(key)) continue;
	        retVal[key] = WooDojoAdmin.deep_copy(obj[key]);
	    }
	    return retVal;
	} // End deep_copy()
  
  }; // End WooDojoAdmin Object // Don't remove this, or the sky will fall on your head.

/**
 * Execute the above methods in the WooDojoAdmin object.
 *
 * @since 1.0.0
 */
	$(document).ready(function () {

		WooDojoAdmin.init_component_display_toggle();
		WooDojoAdmin.init_upgrade_confirmation();
		WooDojoAdmin.ajax_component_toggle();
		WooDojoAdmin.section_switcher();
		WooDojoAdmin.global_component_toggle();
	
	});
  
})(jQuery);