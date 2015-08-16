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

		$ip_address = preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] );
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 ) : '';

		$data = array(
			'comment_post_ID'   => $post_ID,
			'comment_content'   => $emoji,
			'comment_type'      => 'emoji-reaction',
			'comment_approved'  => 1,
			'comment_author_IP' => $ip_address,
			'comment_agent'     => $user_agent,
		);

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			if ( $current_user->user_login !== $current_user->display_name ) {
				$user_name = $current_user->display_name;
			} else {
				$user_name = $current_user->user_login;
			}
			$data['user_id'] = get_current_user_id();
			$data['comment_author'] = $user_name;
			$data['comment_author_email'] = $current_user->user_email;
		} else {
			$data['comment_author'] = esc_html__( 'Guest', 'emoji-reactions' );
		}

		$id = wp_insert_comment( $data );

		if ( ! $id ) {
			wp_send_json_error( esc_html__( 'Reaction failed to post.', 'emoji-reactions' ) );
		}

		wp_send_json( array( 'success' => true, 'reaction_ID' => $id ) );
		die;
	}

}

Emoji_Reactions_Do_Reaction::instance();
