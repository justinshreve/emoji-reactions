<?php
/**
 * Provides the front end control/widget for listing which emoji can be used
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Emoji_Reactions_List_Control {

	private static $instance;

	/**
	 * Only return one instance of the custom emoji admin class
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Emoji_Reactions_List_Control();
		}
		return self::$instance;
	}

	/**
	 * Hook into WordPress
	 */
	public function __construct() {
		add_filter(
			'emoji_reactions_reaction_area',
			array( $this, 'add_button' ),
			apply_filters( 'emoji_reactions_reaction_area_add_button_priority', 20 )
		);
	}

	/**
	 * Displays the button
	 */
	public function add_button( $content ) {
		$button = '<div class="emoji-reactions-reaction-area-add-button">';
		$button .= '+';
		$button .= '</div>';
		return $content . apply_filters( 'emoji_reactions_add_button', $button );
	}

}

Emoji_Reactions_List_Control::instance();
