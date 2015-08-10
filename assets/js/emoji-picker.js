jQuery( document ).ready( function( $ ) {

emojiPicker = {

	emoji: {},
	emojiCategories: {},

	init: function() {
		emojiPicker.windowOpener();
	},

	/**
	 * jQuery( document ).on( 'emojiPicker.click', function( event, emoji ) {
	 *     console.log( emoji ); // the key
     * } );
	 */
	attachClick: function() {
		$( '.emoji-select' ).on( 'click', function() {
			var name = $(this).data( 'name' );
			jQuery( document ).trigger( 'emojiPicker.click', [ name ] );
		} );
	},

	windowOpener: function() {
		$( document ).click( function() {
			$( '#emoji-reactions-popup-window' ).hide();
		} );

		$( '#emoji-reactions-popup-window' ).on( 'click', function( event ) {
			event.stopPropagation();
		} );

		$( '.emoji-reactions-add-button' ).on( 'click', function( event ) {
			var $popup = $( '#emoji-reactions-popup-window' ),
				$menuHeader = $( '#emoji-menu-header' );

			event.stopPropagation();

			$popup.hide();
			$popup.css( 'top', ( $(this).position().top - 400 ) + 'px' );
			$popup.css( 'left', ( $(this).position().left - 15 ) + 'px' );
			$popup.show();

			loadEmoji.done( function() {
				$menuHeader.html( '' );
				$.each( emojiPicker.emojiCategories, function( key, data ) {
					$menuHeader.append( '<a class="emoji-group-tab emoji-group-tab-' + key + '" data-key="' + key + '" data-label="' + data.label + '" title="' + data.label + '">' + data.html + '</a>' );
				} );

				// default to people tab
				emojiPicker.showEmojiTab( 'people' );

				$( '.emoji-group-tab' ).on( 'click', function() {
					emojiPicker.showEmojiTab( $( this ).data( 'key' ) );
				} );
			} );
		} );
	},

	showEmojiTab: function( key ) {
		var $label = $( '#emoji-label' ),
			$list = $( '#emoji-list' );

		if ( 'custom' === key ) {
			emojiPicker.showCustomEmojiTab();
			return;
		}

		$label.text( $( '.emoji-group-tab-' + key ).data( 'label' ) );
		$list.html( '' );

		$.each( emojiPicker.emoji[ key ], function( id, emojiData ) {
			if ( '' === emojiData.name ) {
				return;
			}
			$list.append( '<a class="emoji-select" data-name="' + emojiData.short_name + '">' + twemoji.parse( emojiPicker.toUnicode( emojiData.unified ) ) + '</a>' );
		} );

		emojiPicker.attachClick();
	},

	showCustomEmojiTab: function() {
		var $label = $( '#emoji-label' ),
			$list = $( '#emoji-list' );

		$label.text( emojiPickerStrings[ 'custom' ] );
		$list.html( '' );

		$.each( emojiPickerData.custom, function( short_name, image_url ) {
			$list.append( '<a class="emoji-select" data-name="custom_' + short_name + '"><img src="' + image_url + '" title="' + short_name + '" class="emoji" /></a>' );
		} );

		emojiPicker.attachClick();
	},

	processEmoji: function( raw ) {
		var byCategoryUnsorted = byCategory = {};

		// Separate by category
		$.each( raw, function( key, data ) {
			if ( null !== data.category ) {
				if ( 'undefined' === typeof byCategoryUnsorted[ data.category.toLowerCase() ] ) {
					byCategoryUnsorted[ data.category.toLowerCase() ] = [];
					byCategory[ data.category.toLowerCase() ] = [];
				}
				byCategoryUnsorted[ data.category.toLowerCase() ].push( data );
			}
		} );

		$.each( byCategoryUnsorted, function ( category, entriesUnsorted ) {
			var entries = entriesUnsorted.sort( emojiPicker.compareSortOrder );
			byCategory[ category ] = entries;
		} );

		// Return in a specific order for how we will display them..
		emojiPicker.emoji['people'] = byCategory['people'];
		emojiPicker.emojiCategories['people'] = {
			'label': emojiPickerStrings[ 'people' ],
			'html': twemoji.parse( '&#x1F601;' )
		};

		emojiPicker.emoji['nature'] = byCategory['nature'];
		emojiPicker.emojiCategories['nature'] = {
			'label': emojiPickerStrings[ 'nature' ],
			'html': twemoji.parse( '&#x1F332;' )
		};

		emojiPicker.emoji['foods']  = byCategory['foods'];
		emojiPicker.emojiCategories['foods'] = {
			'label': emojiPickerStrings[ 'foods' ],
			'html': twemoji.parse( '&#x1F354;' )
		};

		emojiPicker.emoji['celebration'] = byCategory['celebration'];
		emojiPicker.emojiCategories['celebration'] = {
			'label': emojiPickerStrings[ 'celebration' ],
			'html': twemoji.parse( '&#x1F389;' )
		};

		emojiPicker.emoji['activity'] = byCategory['activity'];
		emojiPicker.emojiCategories['activity'] = {
			'label': emojiPickerStrings[ 'activity' ],
			'html': twemoji.parse( '&#x1F3C8;' )
		};

		emojiPicker.emoji['places'] = byCategory['places'];
		emojiPicker.emojiCategories['places'] = {
			'label': emojiPickerStrings[ 'places' ],
			'html': twemoji.parse( '&#x2708;' )
		};

		emojiPicker.emoji['symbols'] = byCategory['symbols'];
		emojiPicker.emojiCategories['symbols'] = {
			'label': emojiPickerStrings[ 'symbols' ],
			'html': twemoji.parse( '&#x1F4A1;' )
		};

		emojiPicker.emojiCategories['custom'] = {
			'label': emojiPickerStrings[ 'custom' ],
			'html': twemoji.parse( '&#x270F;' )
		};

		loadEmoji.resolve();
	},

	// Utility functions

	toUnicode: function( code ) {
		var codes = code.split( '-' ).map( function( value, index ) {
			return parseInt(value, 16);
		} );
		return String.fromCodePoint.apply( null, codes );
	},

	compareSortOrder: function( a,b ) {
		if ( a.sort_order < b.sort_order ) {
			return -1;
		}
		if ( a.sort_order > b.sort_order ) {
			return 1;
		}
		return 0;
	}

};

emojiRaw = $.getJSON( emojiPickerData.pluginURL + '/emoji.json' );
loadEmoji = $.Deferred();
loadEmoji.promise();
emojiRaw.done( emojiPicker.processEmoji );
emojiPicker.init();

} );
