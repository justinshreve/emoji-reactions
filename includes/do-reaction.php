<?php
/**
 * Provides the the code to store a reaction
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Emoji_Reactions_Do_Reaction {

	private static $instance;

	/**
	 * Only return one instance of the react class
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Emoji_Reactions_Do_Reaction();
		}
		return self::$instance;
	}

	/**
	 * Hook into WordPress
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ) );
	}

	/**
	 * Loads assets for the emoji picker
	 */
	public function load_assets() {
		wp_enqueue_script( 'emoji-reactions-do-reaction-js', plugins_url( 'assets/js/do-reaction.js' , dirname( __FILE__ ) ), array( 'jquery' ) );

		// data for displaying the reactions area
		wp_localize_script( 'emoji-reactions-do-reaction-js', 'emoji18n', array(
			'ajaxURL' => admin_url( 'admin-ajax.php' ),
		) );

	}

}

Emoji_Reactions_Do_Reaction::instance();
