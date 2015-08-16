<?php
/**
 * Provides some useful shared utility functions for both admin and front end and between classes
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Emoji_Reactions_Utils {

	/**
	 * Returns an array of custom emoji name => image URL
	 * @return array
	 */
	public static function get_custom_emoji() {
		$custom_emoji_wp = get_posts( array(
			'posts_per_page' => -1,
			'post_type'      => 'custom-emoji',
			'post_status'    => 'publish',
		) );

		$custom_emoji = array();

		foreach ( $custom_emoji_wp as $custom_emoji_wp_single ) {
			$custom_emoji[ $custom_emoji_wp_single->post_title ] = wp_get_attachment_url( get_post_thumbnail_id( $custom_emoji_wp_single->ID ) );
		}

		$custom_emoji = apply_filters( 'emoji_reactions_custom_emoji', $custom_emoji );
		return $custom_emoji;
	}

	/**
	 * Returns true if the provided $emoji shorthand is a valid emoji or false if not
	 * Checks against both custom emoji and standard emoji
	 * @param  string  $emoji
	 * @return boolean
	 */
	public static function is_valid_emoji( $emoji ) {
		if ( "custom_" === substr( $emoji, 0, 7 ) ) {
			$emoji = substr( $emoji, 7 );
		}
		$custom_emoji = Emoji_Reactions_Utils::get_custom_emoji();
		if ( array_key_exists( $emoji, $custom_emoji ) ) {
			return true;
		}

		$emoji_raw_json = file_get_contents( __DIR__ . '/../emoji.json' );
		$emoji_json = json_decode( $emoji_raw_json );
		$is_valid_emoji = false;

		foreach ( $emoji_json as $single_emoji_object ) {
			if ( $emoji === $single_emoji_object->short_name ) {
				$is_valid_emoji = true;
				break;
			}
		}

		return $is_valid_emoji;
	}

	/**
	 * Returns true if a post can be "reacted" upon, false if not
	 */
	public static function can_react_to_post( $post_ID ) {
		$post = get_post( $post_ID );
		if ( ! $post || is_wp_error( $post ) ) {
			return false;
		}

		if ( 'inherit' === $post->post_status ) {
			$parent_post = get_post( $post->post_parent );
			$post_password = $parent_post->post_password;
			$post_status_obj = get_post_status_object( $parent_post->post_status );
			$post_status = $parent_post->post_status;
		} else {
			$post_status_obj = get_post_status_object( $post->post_status );
			$post_status = $post->post_status;
			$post_password = $post->post_password;
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
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		if ( in_array( $post_status, array( 'draft', 'pending', 'future', 'trash' ) ) ) {
			return false;
		}

		if ( strlen( $post_password ) && ! current_user_can( 'read_post', $post->ID ) ) {
			return false;
		}

		if ( 'off' === get_option( 'emoji_reactions_allow_guest_reactions', 'off' ) && ! is_user_logged_in() ) {
			return false;
		}

		return true;
	}

}
