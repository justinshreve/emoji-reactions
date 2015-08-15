jQuery( document ).ready( function( $ ) {

	jQuery( document ).on( 'emojiPicker.select', function( event, emoji, postID ) {
		var ajaxData = {
			'action'  : 'emoji_reactions_do_reaction',
			'post_ID' : postID,
			'emoji'   : emoji
		};

		console.log( ajaxData);
		console.log( emojiData.ajaxURL );

		$.post( emojiData.ajaxURL, ajaxData, function( response ) {
			if ( false === response.success ) {
				console.log( 'error' );
				console.log( response.data );
			}

			//console.log( error );
		} );
	} );

} );
