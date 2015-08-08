jQuery( document ).ready( function( $ ) {
	$( '.post-type-custom-emoji #submitdiv h3 span' ).text( emojiReactionsStrings.saveNewEmoji );
	$( '.post-type-custom-emoji #publishing-action #publish').val( emojiReactionsStrings.save );
	$(' .post-type-custom-emoji #postimagediv-hide' ).parent().hide();
} );
