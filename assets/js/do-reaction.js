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
			// @todo show an actual error here
			if ( false === response.success ) {
				console.log( 'error' );
				console.log( response.data );
				return;
			}

			// @todo show our emoji / refresh the emoji display
		} );
	} );

} );
