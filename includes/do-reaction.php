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
			die( -1 );
		}

		$post_ID = $_REQUEST['post_ID'];
		$emoji   = $_REQUEST['emoji'];

		if ( ! $this->can_current_user_react_to_post( $post_ID ) ) {
			wp_send_json_error( esc_html__( 'Post is not available.', 'emoji-reactions' ) );
			die( -1 );
		}

		$post = get_post( $post_ID );

		$emoji_raw_json = file_get_contents( __DIR__ . '/../emoji.json' );
		$emoji_json = json_decode( $emoji_raw_json );
		$is_valid_emoji = false;

		foreach ( $emoji_json as $single_emoji_object ) {
			if ( $emoji === $single_emoji_object->short_name ) {
				$is_valid_emoji = true;
				break;
			}
		}

		if ( false === $is_valid_emoji ) {
			wp_send_json_error( esc_html__( 'Provided emoji not recognized.', 'emoji-reactions' ) );
			die( -1 );
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

	private function can_current_user_react_to_post( $post_ID ) {

		$post = get_post( $post_ID );
		if ( ! $post || is_wp_error( $post ) ) {
			return false;
		}

		if ( 'inherit' === $post->post_status ) {
			$parent_post = get_post( $post->post_parent );
			$post_status_obj = get_post_status_object( $parent_post->post_status );
		} else {
			$post_status_obj = get_post_status_object( $post->post_status );
		}

		if ( ! $post_status_obj->public ) {
			if ( is_user_logged_in() ) {
				if ( $post_status_obj->protected ) {
					if ( ! current_user_can( 'edit_post', $post->ID ) ) {
						return false;
					}
				} elseif ( $post_status_obj->private ) {
					if ( ! current_user_can( 'read_post', $post->ID ) ) {
						return false;
					}
				} elseif ( 'trash' === $post->post_status ) {
					if ( ! current_user_can( 'edit_post', $post->ID ) ) {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		if ( strlen( $post->post_password ) && ! current_user_can( 'edit_post', $post->ID ) ) {
			return false;
		}

		return true;

	}

}

Emoji_Reactions_Do_Reaction::instance();
