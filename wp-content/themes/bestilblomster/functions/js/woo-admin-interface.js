/**
 * WooThemes Admin Interface JavaScript
 *
 * All JavaScript logic for the theme options admin interface.
 * @since 4.8.0
 *
 */

(function ($) {

  woothemesAdminInterface = {
  
/**
 * toggle_nav_tabs()
 *
 * @since 4.8.0
 */
 
 	toggle_nav_tabs: function () {
 		var flip = 0;
	
		$( '#expand_options' ).click( function(){
			if( flip == 0 ){
				flip = 1;
				$( '#woo_container #woo-nav' ).hide();
				$( '#woo_container #content' ).width( 785 );
				$( '#woo_container .group' ).add( '#woo_container .group h1' ).show();

				$(this).text( '[-]' );

			} else {
				flip = 0;
				$( '#woo_container #woo-nav' ).show();
				$( '#woo_container #content' ).width( 595 );
				$( '#woo_container .group' ).add( '#woo_container .group h1' ).hide();
				$( '#woo_container .group:first' ).show();
				$( '#woo_container #woo-nav li' ).removeClass( 'current' );
				$( '#woo_container #woo-nav li:first' ).addClass( 'current' );

				$(this).text( '[+]' );

			}

		});
 	}, // End toggle_nav_tabs()

/**
 * load_first_tab()
 *
 * @since 4.8.0
 */
 
 	load_first_tab: function () {
 		$( '.group' ).hide();
 		$( '.group:has(".section"):first' ).fadeIn(); // Fade in the first tab containing options (not just the first tab).
 	}, // End load_first_tab()
 	
/**
 * open_first_menu()
 *
 * @since 5.0.0
 */
 
 	open_first_menu: function () {
 		$( '#woo-nav li.current.has-children:first ul.sub-menu' ).slideDown().addClass( 'open' ).children( 'li:first' ).addClass( 'active' ).parents( 'li.has-children' ).addClass( 'open' );
 	}, // End open_first_menu()
 	
/**
 * toggle_nav_menus()
 *
 * @since 5.0.0
 */
 
 	toggle_nav_menus: function () {
 		$( '#woo-nav li.has-children > a' ).click( function ( e ) {
 			if ( $( this ).parent().hasClass( 'open' ) ) { return false; }
 			
 			$( '#woo-nav li.top-level' ).removeClass( 'open' ).removeClass( 'current' );
 			$( '#woo-nav li.active' ).removeClass( 'active' );
 			if ( $( this ).parents( '.top-level' ).hasClass( 'open' ) ) {} else {
 				$( '#woo-nav .sub-menu.open' ).removeClass( 'open' ).slideUp().parent().removeClass( 'current' );
 				$( this ).parent().addClass( 'open' ).addClass( 'current' ).find( '.sub-menu' ).slideDown().addClass( 'open' ).children( 'li:first' ).addClass( 'active' );
 			}
 			
 			// Find the first child with sections and display it.
 			var clickedGroup = $( this ).parent().find( '.sub-menu li a:first' ).attr( 'href' );
 			
 			if ( clickedGroup != '' ) {
 				$( '.group' ).hide();
 				$( clickedGroup ).fadeIn();
 			}
 			return false;
 		});
 	}, // End toggle_nav_menus()
 	
/**
 * toggle_collapsed_fields()
 *
 * @since 4.8.0
 */
 
 	toggle_collapsed_fields: function () {
		$( '.group .collapsed' ).each(function(){
			$( this ).find( 'input:checked' ).parent().parent().parent().nextAll().each( function(){
				if ($( this ).hasClass( 'last' ) ) {
					$( this ).removeClass( 'hidden' );
					return false;
				}
				$( this ).filter( '.hidden' ).removeClass( 'hidden' );
				
				$( '.group .collapsed input:checkbox').click(function ( e ) {
					woothemesAdminInterface.unhide_hidden( $( this ).attr( 'id' ) );
				});

			});
		});
 	}, // End toggle_collapsed_fields()

/**
 * setup_nav_highlights()
 *
 * @since 4.8.0
 */
 
 	setup_nav_highlights: function () {
	 	// Highlight the first item by default.
	 	$( '#woo-nav li.top-level:first' ).addClass( 'current' ).addClass( 'open' );
		
		// Default single-level logic.
		$( '#woo-nav li.top-level' ).not( '.has-children' ).find( 'a' ).click( function ( e ) {
			var thisObj = $( this );
			var clickedGroup = thisObj.attr( 'href' );
			
			if ( clickedGroup != '' ) {
				$( '#woo-nav .open' ).removeClass( 'open' );
				$( '.sub-menu' ).slideUp();
				$( '#woo-nav .active' ).removeClass( 'active' );
				$( '#woo-nav li.current' ).removeClass( 'current' );
				thisObj.parent().addClass( 'current' );
				
				$( '.group' ).hide();
				$( clickedGroup ).fadeIn();
				
				return false;
			}
		});
		
		$( '#woo-nav li:not(".has-children") > a:first' ).click( function( evt ) {
			var parentObj = $( this ).parent( 'li' );
			var thisObj = $( this );
			
			var clickedGroup = thisObj.attr( 'href' );
			
			if ( $( this ).parents( '.top-level' ).hasClass( 'open' ) ) {} else {
				$( '#woo-nav li.top-level' ).removeClass( 'current' ).removeClass( 'open' );
				$( '#woo-nav .sub-menu' ).removeClass( 'open' ).slideUp();
				$( this ).parents( 'li.top-level' ).addClass( 'current' );
			}
		
			$( '.group' ).hide();
			$( clickedGroup ).fadeIn();
		
			evt.preventDefault();
			return false;
		});
		
		// Sub-menu link click logic.
		$( '.sub-menu a' ).click( function ( e ) {
			var thisObj = $( this );
			var parentMenu = $( this ).parents( 'li.top-level' );
			var clickedGroup = thisObj.attr( 'href' );
			
			if ( $( '.sub-menu li a[href="' + clickedGroup + '"]' ).hasClass( 'active' ) ) {
				return false;
			}
			
			if ( clickedGroup != '' ) {
				parentMenu.addClass( 'open' );
				$( '.sub-menu li, .flyout-menu li' ).removeClass( 'active' );
				$( this ).parent().addClass( 'active' );
				$( '.group' ).hide();
				$( clickedGroup ).fadeIn();
			}
			
			return false;
		});
 	}, // End setup_nav_highlights()

/**
 * setup_custom_typography()
 *
 * @since 4.8.0
 */
 
 	setup_custom_typography: function () {
	 	$( 'select.woo-typography-unit' ).change( function(){
			var val = $( this ).val();
			var parent = $( this ).parent();
			var name = parent.find( '.woo-typography-size-px' ).attr( 'name' );
			if( name == '' ) { var name = parent.find( '.woo-typography-size-em' ).attr( 'name' ); }
			
			if( val == 'px' ) {
				var name = parent.find( '.woo-typography-size-em' ).attr( 'name' );
				parent.find( '.woo-typography-size-em' ).hide().removeAttr( 'name' );
				parent.find( '.woo-typography-size-px' ).show().attr( 'name', name );
			}
			else if( val == 'em' ) {
				var name = parent.find( '.woo-typography-size-px' ).attr( 'name' );
				parent.find( '.woo-typography-size-px' ).hide().removeAttr( 'name' );
				parent.find( '.woo-typography-size-em' ).show().attr( 'name', name );
			}
		
		});
 	}, // End setup_custom_typography()

/**
 * setup_custom_ui_slider()
 *
 * @since 5.3.5
 */
 
 	setup_custom_ui_slider: function () {

		$('div.ui-slide').each(function(i){

			if( $(this).attr('min') != undefined && $(this).attr('max') != undefined ) {

				$(this).slider( { 
								min: parseInt($(this).attr('min')), 
								max: parseInt($(this).attr('max')), 
								value: parseInt($(this).next("input").val()),
								step: parseInt($(this).attr('inc')) ,
								slide: function( event, ui ) {
									$( this ).next("input").val(ui.value);
								}
							});

				$(this).removeAttr('min').removeAttr('max').removeAttr('inc');

			}

		});

 	}, // End setup_custom_ui_slider()

/**
 * init_flyout_menus()
 *
 * @since 5.0.0
 */
 
 	init_flyout_menus: function () {
 		// Only trigger flyouts on menus with closed sub-menus.
 		$( '#woo-nav li.has-children' ).each ( function ( i ) {
 			$( this ).hover(
	 			function () {
	 				if ( $( this ).find( '.flyout-menu' ).length == 0 ) {
		 				var flyoutContents = $( this ).find( '.sub-menu' ).html();
		 				var flyoutMenu = $( '<div />' ).addClass( 'flyout-menu' ).html( '<ul>' + flyoutContents + '</ul>' );
		 				$( this ).append( flyoutMenu );
	 				}
	 			}, 
	 			function () {
	 				// $( '#woo-nav .flyout-menu' ).remove();
	 			}
	 		);
 		});
 		
 		// Add custom link click logic to the flyout menus, due to custom logic being required.
 		$( '.flyout-menu a' ).live( 'click', function ( e ) {
 			var thisObj = $( this );
 			var parentObj = $( this ).parent();
 			var parentMenu = $( this ).parents( '.top-level' );
 			var clickedGroup = $( this ).attr( 'href' );
 			
 			if ( clickedGroup != '' ) {
	 			$( '.group' ).hide();
	 			$( clickedGroup ).fadeIn();
	 			
	 			// Adjust the main navigation menu.
	 			$( '#woo-nav li' ).removeClass( 'open' ).removeClass( 'current' ).find( '.sub-menu' ).slideUp().removeClass( 'open' );
	 			parentMenu.addClass( 'open' ).addClass( 'current' ).find( '.sub-menu' ).slideDown().addClass( 'open' );
	 			$( '#woo-nav li.active' ).removeClass( 'active' );
	 			$( '#woo-nav a[href="' + clickedGroup + '"]' ).parent().addClass( 'active' );
 			}
 			
 			return false;
 		});
 	}, // End init_flyout_menus()

/**
 * banner_advert_close()
 *
 * @since 5.3.4
 */

	banner_advert_close: function () {
		$( '.wooframework-banner' ).each( function ( i ) {
			if ( $( this ).find( '.close-banner a' ).length ) {
				$( this ).find( '.close-banner a' ).click( function ( e ) {
					var answer = confirm( 'Are you sure you\'d like to close this banner?' + "\n" + 'Before closing this banner, make sure you have saved your theme options.' );
					if ( answer ) {} else {
						return false;
					}
				});
			}
		});
	},  // End banner_advert_close()

/**
 * unhide_hidden()
 *
 * @since 4.8.0
 * @see toggle_collapsed_fields()
 */
 
 	unhide_hidden: function ( obj ) {
 		obj = $( '#' + obj ); // Get the jQuery object.
 		
		if ( obj.attr( 'checked' ) ) {
			obj.parent().parent().parent().nextAll().slideDown().removeClass( 'hidden' ).addClass( 'visible' );
		} else {
			obj.parent().parent().parent().nextAll().each( function(){
				if ( $( this ).filter( '.last' ).length ) {
					$( this ).slideUp().addClass( 'hidden' );
				return false;
				}
				$( this ).slideUp().addClass( 'hidden' );
			});
		}
 	} // End unhide_hidden()
  
  }; // End woothemesAdminInterface Object // Don't remove this, or the sky will fall on your head.

/**
 * Execute the above methods in the woothemesAdminInterface object.
 *
 * @since 4.8.0
 */
	$(document).ready(function () {
	
		woothemesAdminInterface.toggle_nav_tabs();
		woothemesAdminInterface.load_first_tab();
		woothemesAdminInterface.toggle_collapsed_fields();
		woothemesAdminInterface.setup_nav_highlights();
		woothemesAdminInterface.toggle_nav_menus();
		woothemesAdminInterface.init_flyout_menus();
		woothemesAdminInterface.open_first_menu();
		woothemesAdminInterface.banner_advert_close();
		woothemesAdminInterface.setup_custom_typography();
		woothemesAdminInterface.setup_custom_ui_slider();
	
	});
  
})(jQuery);