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
		add_filter( 'emoji_reactions_reaction_area', array( $this, 'add_button' ), 20 );
		add_action( 'wp_footer', array( $this, 'popup_window' ), 30 );
	}

	/**
	 * Displays the button
	 */
	public function add_button( $content ) {
		$button = '<div class="emoji-reactions-reaction-area-add-button">';
		$button .= '+';
		$button .= '</div>';
		return $content . apply_filters( 'emoji_reactions_reaction_area_add_button', $button );
	}

	/**
	 * The popup window wrapper where we list all of our emoji
	 * We only want this div once, so we append it to the filter and move it around later
	 */
	public function popup_window() {
		$window = '<div id="emoji-reactions-popup-window">';
		$window .= '<div id="emoji-menu-header"></div>';
		$window .= '<div id="emoji-label"></div>';
		$window .= '<div id="emoji-list"></div>';
		$window .= '</div>';
		echo $window;
	}

}

Emoji_Reactions_List_Control::instance();
