/**
 * WooThemes Custom Fields JavaScript
 *
 * All JavaScript logic for fields in the post meta box.
 * @since 4.8.0
 *
 */

(function ($) {

  woothemesCustomFields = {
  
/**
 * adjust_form_encoding()
 *
 * @since 4.8.0
 */
 
 	adjust_form_encoding: function () {
 		$( 'form#post' ).attr( 'enctype','multipart/form-data' ).attr( 'encoding','multipart/form-data' );
 	}, // End adjust_form_encoding()
 	
/**
 * setup_datepickers()
 *
 * @since 4.8.0
 */
 
 	setup_datepickers: function () {
 		if ( $( '.woo-input-calendar, .woo_input_calendar' ).length ) {
	 		$( '.woo-input-calendar, .woo_input_calendar' ).each(function () {
	 			var buttonImageURL = $( this ).parent().find( 'input[name=datepicker-image]' ).val();
	 			$( this ).next( 'input[name=datepicker-image]' ).remove();
	 			
				$( '#' + $( this ).attr( 'id' ) ).datepicker( { showOn: 'button', buttonImage: buttonImageURL, buttonImageOnly: true, showAnim: 'slideDown' } );
			});
		}
 	}, // End setup_datepickers()
 	
/**
 * setup_timefields()
 *
 * @since 4.8.0
 */
 
 	setup_timefields: function () {
 		if ( $( '.woo_input_time_masked' ).length ) {
	 		$( '.woo_input_time_masked' ).each( function (){
				$( '#' + $( this ).attr( 'id' )).mask( '99:99' );
			});
		}
 	}, // End setup_timefields() 	
/**
 * setup_wordcounters()
 *
 * @since 4.8.0
 */
 
 	setup_wordcounters: function () {
 		if ( $( '.words-count' ).length ) {
	 		$( '.words-count' ).each( function() {
				var s = ''; var s2 = '';
			    var length = $( this ).val().length;
			    var w_length = $( this ).val().split(/\b[\s,\.-:;]*/).length;
				
			    if( length != 1 ) { s = 's'; }
			    if( w_length != 1 ) { s2 = 's'; }
			    if( $( this ).val() == '' ) { s2 = 's'; w_length = '0'; }
			
			    $( this ).parent().find( '.counter' ).html( length + ' character'+ s + ', ' + w_length + ' word' + s2 );
			
			    $( this ).keyup( function() {
			    var s = ''; var s2 = '';
			        var new_length = $( this ).val().length;
			        var word_length = $( this ).val().split(/\b[\s,\.-:;]*/).length;
			
			        if( new_length != 1 ) { s = 's'; }
			        if( word_length != 1 ){ s2 = 's'; }
			        if( $( this ).val() == '' ) { s2 == 's'; word_length = '0';}
			
			        $( this ).parent().find( '.counter' ).html( new_length + ' character' + s + ', ' + word_length + ' word' + s2 );
			    });
			});
		}
 	}, // End setup_wordcounters()

/**
 * setup_image_selectors()
 *
 * @since 4.8.0
 */
 
 	setup_image_selectors: function () {
 		if ( $( '.woo-meta-radio-img-img, .woo-radio-img-img' ).length ) {
	 		$( '.woo-meta-radio-img-img, .woo-radio-img-img' ).click( function() {
				
				$( this ).parent().parent().find( '.woo-meta-radio-img-img' ).removeClass( 'woo-meta-radio-img-selected' );
				$( this ).parent().parent().find( '.woo-radio-img-img' ).removeClass( 'woo-radio-img-selected' );
				$( this ).addClass( 'woo-meta-radio-img-selected' ).addClass( 'woo-radio-img-selected' );

			});
			$( '.woo-meta-radio-img-label, .woo-meta-radio-img-radio, .woo-radio-img-label, .woo-radio-img-radio' ).hide();
			$( '.woo-meta-radio-img-img, .woo-radio-img-img' ).show();
		}
 	}, // End setup_image_selectors()
 	
/**
 * setup_colourpickers()
 *
 * @since 4.8.0
 */
 
 	setup_colourpickers: function () {
 		if ( jQuery().ColorPicker && $( '.section-typography, .section-border, .section-color' ).length ) {
 			$( '.section-typography, .section-border, .section-color' ).each( function () {
 				
 				var option_id = $( this ).find( '.woo-color' ).attr( 'id' );
				var color = $( this ).find( '.woo-color' ).val();
				var picker_id = option_id += '_picker';
 				
 				if ( $( this ).hasClass( 'section-typography' ) || $( this ).hasClass( 'section-border' ) ) {
					option_id += '_color';
				}
 				
	 			$( '#' + picker_id ).children( 'div' ).css( 'backgroundColor', color );
				$( '#' + picker_id ).ColorPicker({
					color: color,
					onShow: function ( colpkr ) {
						jQuery( colpkr ).fadeIn( 200 );
						return false;
					},
					onHide: function ( colpkr ) {
						jQuery( colpkr ).fadeOut( 200 );
						return false;
					},
					onChange: function ( hsb, hex, rgb ) {
						$( '#' + picker_id ).children( 'div' ).css( 'backgroundColor', '#' + hex );
						$( '#' + picker_id ).next( 'input' ).attr( 'value', '#' + hex );
					
					}
				});
 			});
 		}
 	}, // End setup_colourpickers()

/**
 * setup_field_tabber()
 *
 * @since 5.3.0
 */

  	setup_field_tabber: function () {
  		$( '.wooframework-tabs' ).tabs();
  	}, // End setup_field_tabber()
 	
/**
 * setup_upload_titletest()
 *
 * @since 4.8.0
 * @deprecated
 */
 
 	setup_upload_titletest: function () {
 		if ( $( 'input#title' ).length ) {
			var val = $( 'input#title' ).attr( 'value' );
			if(val == ''){
				$( '.woo_metabox_fields .button-highlighted' ).after( '<em class="woo_red_note">Please add a Title before uploading a file</em>' );
			};
		}
 	} // End setup_upload_titletest()
  
  }; // End woothemesCustomFields Object // Don't remove this, or the sky will fall on your head.

/**
 * Execute the above methods in the woothemesCustomFields object.
 *
 * @since 4.8.0
 */
	$(document).ready(function () {
	
		woothemesCustomFields.adjust_form_encoding();
		woothemesCustomFields.setup_datepickers();
		woothemesCustomFields.setup_timefields();
		woothemesCustomFields.setup_wordcounters();
		woothemesCustomFields.setup_image_selectors();
		woothemesCustomFields.setup_colourpickers();
		woothemesCustomFields.setup_upload_titletest();
		woothemesCustomFields.setup_field_tabber();
		
	});
  
})(jQuery);