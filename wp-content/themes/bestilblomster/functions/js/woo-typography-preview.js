/**
 * WooThemes Typography Live Preview
 * 2011-10-07.
 *
 * @description The code below is designed to generate a live preview using the
 * setting specified in a "custom typography" field in the WooFramework.
 *
 * @since 4.7.0
 */

(function ($) {

  wooTypographyPreview = {
  
  	/**
  	 * loadPreviewButtons()
  	 *
  	 * @description Setup a "preview" button next to each typography field.
  	 * @since 4.7.0
  	 */
  
    loadPreviewButtons: function () {
     
     var previewButtonHTML = '<a href="#" class="woo-typography-preview-button button submit-button" title="' + 'Preview your customized typography settings' + '"><span>' + '+' + '</span></a>';
     
     $( 'input.woo-typography-color' ).each( function ( i ) {
     	$( this ).after( previewButtonHTML );
     });
     
     // Register event handlers.
     wooTypographyPreview.handleEvents();
      
    }, // End loadPreviewButtons()
    
    /**
     * handleEvents()
     *
     * @description Handle the events.
     * @since 4.7.0
     */
    
    handleEvents: function () {
    	$( 'a.woo-typography-preview-button' ).live( 'click', function () {
    		wooTypographyPreview.generatePreview( $( this ) );
    		return false;
    	});
    	
    	$( 'a.preview_remove' ).live( 'click', function () {
    		wooTypographyPreview.closePreview( $( this ) );
    		return false;
    	});
    }, 
    
    /**
     * closePreview()
     *
     * @description Close the preview.
     * @since 4.7.0
     */
     
     closePreview: function ( target ) {
		target.parents( '.section' ).find( '.woo-typography-preview-button .refresh' ).removeClass( 'refresh' );
     	target.parents( '.typography-preview-container' ).remove();
     }, 
    
    /**
     * generatePreview()
     *
     * @description Generate the typography preview.
     * @since 4.7.0
     */
     
    generatePreview: function ( target ) {
    	var previewText = 'Grumpy wizards make toxic brew for the evil Queen and Jack.';
    	var previewHTML = '';
    	var previewStyles = '';
    	
    	// Get the control parent element.
    	var controls = target.parents( '.controls' );
    	var explain = target.parents( '.controls' ).next( '.explain' );
    	
    	var fontUnit = controls.find( '.woo-typography-unit' ).val();
    	
    	var sizeSelector = '.woo-typography-size-px';
    	if ( fontUnit == 'em' ) { sizeSelector = '.woo-typography-size-em'; }
    	
    	var fontSize = controls.find( sizeSelector ).val();
    	
    	var fontFace = controls.find( '.woo-typography-face' ).val();
    	var fontStyle = controls.find( '.woo-typography-style' ).val();
    	var fontColor = controls.find( '.woo-typography-color' ).val();
   		var lineHeight = ( parseInt( fontSize )  / 2 ) + parseInt( fontSize ); // Calculate pleasant line-height for the selected font size.

		// Fix the line-height if using "em".
		if ( fontUnit == 'em' ) { lineHeight = 1; }
		
		// Generate array of non-Google fonts.
		var nonGoogleFonts = new Array( 
										'Arial, sans-serif', 
										'Verdana, Geneva, sans-serif', 
										'&quot;Trebuchet MS&quot;, Tahoma, sans-serif', 
										'Georgia, serif', 
										'&quot;Times New Roman&quot;, serif', 
										'Tahoma, Geneva, Verdana, sans-serif', 
										'Palatino, &quot;Palatino Linotype&quot;, serif', 
										'&quot;Helvetica Neue&quot;, Helvetica, sans-serif', 
										'Calibri, Candara, Segoe, Optima, sans-serif', 
										'&quot;Myriad Pro&quot;, Myriad, sans-serif', 
										'&quot;Lucida Grande&quot;, &quot;Lucida Sans Unicode&quot;, &quot;Lucida Sans&quot;, sans-serif', 
										'&quot;Arial Black&quot;, sans-serif', 
										'&quot;Gill Sans&quot;, &quot;Gill Sans MT&quot;, Calibri, sans-serif', 
										'Geneva, Tahoma, Verdana, sans-serif', 
										'Impact, Charcoal, sans-serif', 
										'Courier, &quot;Courier New&quot;, monospace'
									);

		// Remove "current" class from previously modified typography field.
    	$( '.typography-preview' ).removeClass( 'current' );
    	
    	// Prepare selected fontFace for testing.
    	var fontFaceTest = fontFace.replace( /"/g, '&quot;' );

		// Load Google WebFonts, if we need to.    	
    	if ( jQuery.inArray( fontFaceTest, nonGoogleFonts ) == -1 ) { // -1 is returned if the item is not found in the array.

			// Prepare fontFace for use in the WebFont loader.
			var fontFaceString = fontFace;
			
			// Handle fonts that require specific weights when being included.
			switch ( fontFaceString ) {
				case 'Allan':
				case 'Cabin Sketch':
				case 'Corben':
				case 'UnifrakturCook':
					fontFaceString += ':700';
				break;
				
				case 'Buda':
				case 'Open Sans Condensed':
					fontFaceString += ':300';
				break;
				
				case 'Coda':
				case 'Sniglet':
					fontFaceString += ':800';
				break;
				
				case 'Raleway':
					fontFaceString += ':100';
				break;
			}
			
			
			fontFaceString += '::latin';
			fontFaceString = fontFaceString.replace( / /g, '+' );

			// Add the fontFace in quotes for use in the style declaration, if the selected font has a number in it.
			var specificFonts = new Array( 'Goudy Bookletter 1911' );
			
			if ( jQuery.inArray( fontFace, specificFonts ) > -1 ) {
				var fontFace = "'" + fontFace + "'";
			}

			WebFontConfig = {
			google: { families: [ fontFaceString ] }
			};
				
			if ( $( 'script.google-webfonts-script' ).length ) { $( 'script.google-webfonts-script' ).remove(); }
				
				(function() {
				var wf = document.createElement( 'script' );
				wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
				'://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
				wf.type = 'text/javascript';
				wf.async = 'true';
				var s = document.getElementsByTagName( 'script' )[0];
				s.parentNode.insertBefore( wf, s );
				
				$( wf ).addClass( 'google-webfonts-script' );
				
				})();
		
		}
		
    	// Construct styles.
    	previewStyles += 'font: ' + fontStyle + ' ' + fontSize + fontUnit + '/' + lineHeight + fontUnit + ' ' + fontFace + ';';
    	if ( fontColor ) { previewStyles += ' color: ' + fontColor + ';'; }
    	
    	// Construct preview HTML.
    	var previewHTMLInner = jQuery( '<div />' ).addClass( 'current' ).addClass( 'typography-preview' ).text( previewText ).before( '<a href="#" class="preview_remove button">' + 'Close Preview' + '</a>' );
    	
    	previewHTML = jQuery( '<div />' ).addClass( 'typography-preview-container' ).html( previewHTMLInner );
    	
    	// If no preview display is present, add one.
    	if ( ! explain.next( '.typography-preview-container' ).length ) {
    		previewHTML.find( '.typography-preview' ).attr( 'style', previewStyles );
    		explain.after( previewHTML );
    	} else {
    	// Otherwise, just update the styles of the existing preview.
    		explain.next( '.typography-preview-container' ).find( '.typography-preview' ).attr( 'style', previewStyles );
    	}
    	
    	// Set the button to "refresh" mode.
    	controls.find( '.woo-typography-preview-button span' ).addClass( 'refresh' );
    }

   
  }; // End wooTypographyPreview Object // Don't remove this, or the sky will fall on your head.

/*-----------------------------------------------------------------------------------*/
/* Execute the above methods in the wooTypographyPreview object.
/*-----------------------------------------------------------------------------------*/
  
	$(document).ready(function () {

		wooTypographyPreview.loadPreviewButtons();
	
	});
  
})(jQuery);