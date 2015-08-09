jQuery( document ).ready( function( $ ) {

	$( document ).click( function() {
		$( '#emoji-reactions-popup-window' ).hide();
	} );

	$( '#emoji-reactions-popup-window' ).on( 'click', function( event ) {
		event.stopPropagation();
	} );

	$( '.emoji-reactions-reaction-area-add-button' ).on( 'click', function( event ) {
		var $popup = $( '#emoji-reactions-popup-window' );

		event.stopPropagation();

		$popup.hide();
		$popup.css( 'top', ( $(this).position().top - 400 ) + 'px' );
		$popup.css( 'left', ( $(this).position().left - 15 ) + 'px' );
		$popup.show();
	} );


} );
