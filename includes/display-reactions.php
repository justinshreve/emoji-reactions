<?php
/**
 *  Shows our actual reactions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Emoji_Reactions_Display_Reactions {

	private static $instance;

	/**
	 * Only return one instance of the class
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Emoji_Reactions_Display_Reactions();
		}
		return self::$instance;
	}

	/**
	 * Hook into WordPress
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ) );
		add_filter( 'emoji_reactions_reaction_area', array( $this, 'reactions' ), 10 );
	}

	/**
	 * Loads assets for the emoji picker
	 */
	public function load_assets() {
		wp_enqueue_style( 'emoji-reactions-display-reactions-css', plugins_url( 'assets/css/display-reactions.css' , dirname( __FILE__ ) ) );
		wp_enqueue_script( 'emoji-reactions-display-reactions-js', plugins_url( 'assets/js/display-reactions.js' , dirname( __FILE__ ) ), array( 'jquery' ) );

		// data for displaying the reactions area
		wp_localize_script( 'emoji-reactions-display-reactions-js', 'emojiData', array(
			'pluginURL' => plugins_url( '' , dirname( __FILE__ ) ),
			'custom'    => Emoji_Reactions_Utils::get_custom_emoji()
		) );
	}

	/**
	 * Returns an array of emoji for a post (by short name)
	 */
	public function get_reactions_for_post( $post_ID ) {
		$reactions = get_comments( array( 'type' => 'emoji-reaction', 'post_id' => $post_ID ) );

		if ( empty( $reactions ) || ! is_array( $reactions ) ) {
			return array();
		}

		$return = array();
		foreach ( $reactions as $reaction ) {
			$return[] = $reaction->comment_content;
		}

		return $return;
	}

	/**
	 * Outputs some initial emoji data for us to display
	 */
	public function reactions( $content ) {
		$content .= '<div id="emoji-for-' . intval( get_the_ID() ) . '" data-post="' . intval( get_the_ID() ) . '" data-emoji="' . implode( ',', $this->get_reactions_for_post( get_the_ID() ) ) .'" class="emoji-reactions-data"></div>';
		$content .= '<div id="emoji-error-' . intval( get_the_ID() ) . '" class="emoji-reactions-error"></div>';
		return $content;
	}

}

Emoji_Reactions_Display_Reactions::instance();
