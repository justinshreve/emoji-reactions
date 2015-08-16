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

		add_action( 'wp_ajax_emoji_reactions_do_reaction', array( $this, 'do_reaction' ) );
		add_action( 'wp_ajax_nopriv_emoji_reactions_do_reaction', array( $this, 'do_reaction' ) );
	}

	/**
	 * Loads assets for the emoji picker
	 */
	public function load_assets() {
		wp_enqueue_script( 'emoji-reactions-do-reaction-js', plugins_url( 'assets/js/do-reaction.js' , dirname( __FILE__ ) ), array( 'jquery' ) );

		$ajax_url = admin_url( 'admin-ajax.php' );
		$ajax_url = str_replace( array( 'https://', 'http://' ), '//', $ajax_url );

		// data for displaying the reactions area
		wp_localize_script( 'emoji-reactions-do-reaction-js', 'emojiData', array(
			'ajaxURL' => $ajax_url,
		) );
	}

	/**
	 * Sends our reaction to the server
	 */
	public function do_reaction() {
		if ( empty( $_REQUEST['post_ID'] ) || empty( $_REQUEST['emoji'] ) ) {
			wp_send_json_error( esc_html__( 'The required post ID and emoji fields were not provided.', 'emoji-reactions' ) );
		}

		$post_ID = $_REQUEST['post_ID'];
		$emoji   = $_REQUEST['emoji'];

		if ( ! Emoji_Reactions_Utils::can_react_to_post( $post_ID ) ) {
			wp_send_json_error( esc_html__( 'Post is not available.', 'emoji-reactions' ) );
		}

		if ( ! Emoji_Reactions_Utils::is_valid_emoji( $emoji ) ) {
			wp_send_json_error( esc_html__( 'Provided emoji not recognized.', 'emoji-reactions' ) );
		}

		$data = array(
		    'comment_post_ID' => $post_ID,
		    'comment_content' => $emoji,
		    'comment_type' => 'emoji-reaction',
		);
		$id = wp_insert_comment( $data );

		error_log( print_r ( $data, 1 ) );
		error_log( print_r ( $id, 1 ) );

		die;
	}

}

Emoji_Reactions_Do_Reaction::instance();
