jQuery( document ).ready( function( $ ) {

	/**
	 * Updates the reactions div (by postID) to display the list of emoji passed in a csv
	 */
	function displayEmoji( postID, csv ) {
		var emoji = csv.split( ',' );
		console.log( emoji );

		var counts = {};
		$.each( emoji, function( index, shortName ) {
			if ( 'undefined' === typeof counts[ shortName ] ) {
				counts[ shortName ] = 0;
			}
			counts[ shortName ]++;
		} );

		emojiReactionsLoadEmoji.done( function() {
			var display;
			$.each ( counts, function( shortName, count ) {
				if ( "custom_" === shortName.substring( 0, 7 ) ) {
					if ( 'undefined' === typeof emojiData.custom[ shortName.substring( 7 ) ] ) {
						return;
					}
					display = '<img class="emoji" draggable="false" alt="' + shortName.substring( 7 ) + '" src="' + emojiData.custom[ shortName.substring( 7 ) ] + '" />';
				} else {
					display = twemoji.parse( emojiReactionsMap[ shortName ] );
				}
				display = '<div class="emoji-reactions-button">' + display + '</div>';
				$( '#emoji-for-' + postID ).append( display );
			} );
		} );
		console.log( counts );
	}

	$( '.emoji-reactions-data' ).each( function( i, reaction ) {
		displayEmoji( $( reaction ).data( 'post' ), $( reaction ).data( 'emoji' ) )
	} );

} );
