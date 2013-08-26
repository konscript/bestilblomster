/**
 *
 * Style Select
 *
 * Replace Select text
 * Dependencies: jQuery
 *
 */
(function ($) {
  styleSelect = {
    init: function () {
      $( '.select_wrapper').each(function () {
        $(this).prepend( '<span>' + $(this).find( '.woo-input option:selected').text() + '</span>' );
      });
      $( 'select.woo-input').live( 'change', function () {
        $(this).prev( 'span').replaceWith( '<span>' + $(this).find( 'option:selected').text() + '</span>' );
      });
      $( 'select.woo-input').bind($.browser.msie ? 'click' : 'change', function(event) {
        $(this).prev( 'span').replaceWith( '<span>' + $(this).find( 'option:selected').text() + '</span>' );
      }); 
    }
  };
})(jQuery);

jQuery(document).ready(function() { 
styleSelect.init();
});
