jQuery( document ).ready( function ( e ) {
	jQuery( '.sortable-tab-list' ).sortable();
	jQuery( '.sortable-tab-list' ).disableSelection();

	jQuery( '.sortable-tab-list' ).bind( 'sortstop', function ( e, ui ) {
		var orderString = '';

		jQuery( this ).find( '.tab' ).each( function ( i, e ) {
			if ( i > 0 ) { orderString += ','; }
			orderString += jQuery( this ).find( 'input' ).val();
		});

		jQuery( 'input[name="tab-order"]' ).attr( 'value', orderString );
	});
});