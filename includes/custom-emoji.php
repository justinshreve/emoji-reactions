<?php
/**
 * Returns a list of custom emoji
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Emoji_Reactions_Custom_Emoji {

	private static $instance;

	/**
	 * Only return one instance of the custom emoji class
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Emoji_Reactions_Custom_Emoji();
		}
		return self::$instance;
	}

	/**
	 * Returns an array of custom emoji name => image URL
	 * @return array
	 */
	public function get() {
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

}

Emoji_Reactions_Custom_Emoji::instance();
