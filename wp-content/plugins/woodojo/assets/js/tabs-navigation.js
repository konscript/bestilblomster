jQuery(document).ready( function($) {
	// Make sure each heading has a unique ID.
	jQuery( 'ul#settings-sections.subsubsub' ).find( 'a' ).each( function ( i ) {
		var id_value = jQuery( this ).attr( 'href' ).replace( '#', '' );
		jQuery( 'h3:contains("' + jQuery( this ).text() + '")' ).attr( 'id', id_value ).addClass( 'section-heading' );
	});

	jQuery( '#woodojo .subsubsub a.tab' ).click( function ( e ) {
		// Move the "current" CSS class.
		jQuery( this ).parents( '.subsubsub' ).find( '.current' ).removeClass( 'current' );
		jQuery( this ).addClass( 'current' );
	
		// If "All" is clicked, show all.
		if ( jQuery( this ).hasClass( 'all' ) ) {
			jQuery( '#woodojo h3, #woodojo form p, #woodojo table.form-table, p.submit' ).show();
			
			return false;
		}
		
		// If the link is a tab, show only the specified tab.
		var toShow = jQuery( this ).attr( 'href' );

		// Remove the first occurance of # from the selected string (will be added manually below).
		toShow = toShow.replace( '#', '', toShow );

		jQuery( '#woodojo h3, #woodojo form > p:not(".submit"), #woodojo table' ).hide();
		jQuery( 'h3#' + toShow ).show().nextUntil( 'h3.section-heading', 'p, table, table p' ).show();
		
		return false;
	});
});