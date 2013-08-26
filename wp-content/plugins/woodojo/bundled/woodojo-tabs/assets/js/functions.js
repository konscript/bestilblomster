jQuery(document).ready(function(){
	jQuery( '.widget_woodojo_tabs' ).find( '.nav a' ).click( function ( e ) {
		if ( jQuery( this ).parent( 'li' ).hasClass( 'active' ) ) { return false; }
		var thisTabber = jQuery( this ).parents( '.widget_woodojo_tabs' );
		var targetTab = jQuery( this ).attr( 'href' );
		jQuery( this ).parent( 'li' ).addClass( 'active' ).siblings( '.active' ).removeClass( 'active' );
		thisTabber.find( '.tab-pane' + targetTab ).addClass( 'active' ).siblings( '.active' ).removeClass( 'active' );
		return false;
	});
});