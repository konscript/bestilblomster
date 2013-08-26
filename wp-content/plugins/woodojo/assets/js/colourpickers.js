jQuery(document).ready( function($) {
	jQuery( 'input.colourpicker-input' ).each( function () {
	
		// Get the colourpicker field's ID value.
		var idValue = jQuery( this ).attr( 'id' );
		
		if ( idValue ) {
			jQuery( '#default-' + idValue ).find( '.colour' ).wrapInner('<a href="#" />');

			jQuery( '#picker-' + idValue ).farbtastic( '#' + idValue );
			jQuery( '#select-' + idValue ).click( function () {
				jQuery( '#picker-' + idValue ).toggle();
				return false;
			});

			jQuery( 'input#' + idValue ).parents( 'table' ).click( function () {
				jQuery( '#picker-' + idValue ).hide();
			});
			jQuery( '#default-' + idValue ).click( function () {
				jQuery( '#picker-' + idValue ).hide();
			});

			if ( jQuery( '#default-' + idValue ).length ) {
				jQuery( '#default-' + idValue ).find( '.colour a' ).click( function () {
					var defaultColour = jQuery( this ).text();
					jQuery( '#' + idValue ).val( defaultColour ).css( 'background-color', defaultColour );
					return false;
				});
			}
		}
	
	});
});