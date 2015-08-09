jQuery( document ).ready( function( $ ) {

	var emojiRaw = $.getJSON( emojiPickerData.pluginURL + '/emoji.json' ),
		emoji = {},
		emojiCategories = {},
		loadEmoji = $.Deferred();

	loadEmoji.promise();
	emojiRaw.done( processEmoji );

	$( document ).click( function() {
		$( '#emoji-reactions-popup-window' ).hide();
	} );

	$( '#emoji-reactions-popup-window' ).on( 'click', function( event ) {
		event.stopPropagation();
	} );

	$( '.emoji-reactions-reaction-area-add-button' ).on( 'click', function( event ) {
		var $popup = $( '#emoji-reactions-popup-window' ),
			$menuHeader = $( '#emoji-menu-header' );

		event.stopPropagation();

		$popup.hide();
		$popup.css( 'top', ( $(this).position().top - 400 ) + 'px' );
		$popup.css( 'left', ( $(this).position().left - 15 ) + 'px' );
		$popup.show();

		loadEmoji.done( function() {
			$menuHeader.html( '' );
			$.each( emojiCategories, function( key, data ) {
				$menuHeader.append( '<a class="emoji-group-tab emoji-group-tab-' + key + '" data-key="' + key + '" data-label="' + data.label + '" title="' + data.label + '">' + data.html + '</a>' );
			} );

			// default to people tab
			showEmojiTab( 'people' );

			$( '.emoji-group-tab, .emoji-group-tab-peopl' ).on( 'click', function() {
				showEmojiTab( $( this ).data( 'key' ) );
			} );
		} );
	} );


	function showEmojiTab( key ) {
		var $label = $( '#emoji-label' ),
			$list = $( '#emoji-list' );

		if ( 'custom' === key ) {
			showCustomEmojiTab();
			return;
		}

		$label.text( $( '.emoji-group-tab-' + key ).data( 'label' ) );
		$list.html( '' );

		$.each( emoji[ key ], function( id, emojiData ) {
			if ( '' === emojiData.name ) {
				return;
			}
			$list.append( '<a class="emoji-select" data-name="' + emojiData.short_name + '">' + twemoji.parse( toUnicode( emojiData.unified ) ) + '</a>' );
		} );
	}

	function showCustomEmojiTab() {
		var $label = $( '#emoji-label' ),
			$list = $( '#emoji-list' );

		$label.text( emojiPickerStrings[ 'custom' ] );
		$list.html( '' );

		$.each( emojiPickerData.custom, function( short_name, image_url ) {
			$list.append( '<a class="emoji-select" data-name="custom_' + short_name + '"><img src="' + image_url + '" title="' + short_name + '" class="emoji" /></a>' );
		} );
	}

function toUnicode(code) {
    var codes = code.split('-').map(function(value, index) {
      return parseInt(value, 16);
    });
    return String.fromCodePoint.apply(null, codes);
  }


	function processEmoji( emojiRaw ) {
		var byCategoryUnsorted = byCategory = {};

		function compareSortOrder( a,b ) {
			if ( a.sort_order < b.sort_order ) {
				return -1;
			}
			if (a.sort_order > b.sort_order) {
				return 1;
			}
			return 0;
		}

		// Separate by category
		$.each( emojiRaw, function( key, data ) {
			if ( null !== data.category ) {
				if ( 'undefined' === typeof byCategoryUnsorted[ data.category.toLowerCase() ] ) {
					byCategoryUnsorted[ data.category.toLowerCase() ] = [];
					byCategory[ data.category.toLowerCase() ] = [];
				}
				byCategoryUnsorted[ data.category.toLowerCase() ].push( data );
			}
		} );

		$.each( byCategoryUnsorted, function ( category, entriesUnsorted ) {
			var entries = entriesUnsorted.sort( compareSortOrder );
			byCategory[ category ] = entries;
		} );

		// Return in a specific order for how we will display them..
		emoji['people'] = byCategory['people'];
		emojiCategories['people'] = {
			'label': emojiPickerStrings[ 'people' ],
			'html': twemoji.parse( '&#x1F601;' )
		};

		emoji['nature'] = byCategory['nature'];
		emojiCategories['nature'] = {
			'label': emojiPickerStrings[ 'nature' ],
			'html': twemoji.parse( '&#x1F332;' )
		};

		emoji['foods']  = byCategory['foods'];
		emojiCategories['foods'] = {
			'label': emojiPickerStrings[ 'foods' ],
			'html': twemoji.parse( '&#x1F354;' )
		};

		emoji['celebration'] = byCategory['celebration'];
		emojiCategories['celebration'] = {
			'label': emojiPickerStrings[ 'celebration' ],
			'html': twemoji.parse( '&#x1F389;' )
		};

		emoji['activity'] = byCategory['activity'];
		emojiCategories['activity'] = {
			'label': emojiPickerStrings[ 'activity' ],
			'html': twemoji.parse( '&#x1F3C8;' )
		};

		emoji['places'] = byCategory['places'];
		emojiCategories['places'] = {
			'label': emojiPickerStrings[ 'places' ],
			'html': twemoji.parse( '&#x2708;' )
		};

		emoji['symbols'] = byCategory['symbols'];
		emojiCategories['symbols'] = {
			'label': emojiPickerStrings[ 'symbols' ],
			'html': twemoji.parse( '&#x1F4A1;' )
		};

		emojiCategories['custom'] = {
			'label': emojiPickerStrings[ 'custom' ],
			'html': twemoji.parse( '&#x270F;' )
		};

		loadEmoji.resolve();
	}

} );
