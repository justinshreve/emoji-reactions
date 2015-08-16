<?php
/**
 *  Prevents reactions from showing in comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Emoji_Reactions_Prevent_Reactions_In_Comments {

	private static $instance;

	/**
	 * Only return one instance of the class
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Emoji_Reactions_Prevent_Reactions_In_Comments();
		}
		return self::$instance;
	}

	/**
	 * Hook into WordPress
	 */
	public function __construct() {
		add_filter( 'get_comments_number', array( $this, 'comment_count' ) );
		add_action( 'pre_get_comments', array( $this, 'hide' ) );
	}

	/**
	 * Hides the 'emoji-reaction' comment type from displaying in the comment areas
	 */
	public function hide( $query ) {
		if ( 'emoji-reaction' !== $query->query_vars['type'] ) {
			$query->query_vars['type__not_in'] = array_merge(
				(array) $query->query_vars['type__not_in'],
				array( 'emoji-reaction' )
			);
		}
	}

	/**
	 * Don't count reactions when determining
	 * the number of comments on a post.
	 */
	public function comment_count( $count ) {
		global $id;
		$comment_count = 0;
		$comments = get_approved_comments( $id );
		foreach ( $comments as $comment ) {
			if (  'emoji-reaction' !== $comment->comment_type ) {
				$comment_count++;
			}
		}
		return $comment_count;
	}

}

Emoji_Reactions_Prevent_Reactions_In_Comments::instance();
