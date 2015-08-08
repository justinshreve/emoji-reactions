<?php
/**
 * Registers a custom post type for storing custom emoji
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Emoji_Reactions_Register_Custom_Emoji_CPT {

	/**
	 * We want to register everything on WordPress' init
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Actually does the registering of the cpt
	 */
	public function register() {
		$labels = $this->get_labels();

		$args = array(
			'label'               => esc_html__( 'Custom Emoji', 'emoji-reactions' ),
			'labels'              => $labels,
			'description'         => esc_html__( 'Custom emoji that readers can select when reacting to your posts.', 'emoji-reactions' ),
			'public'              => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_nav_menus'   => false,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => false,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'    => 'post',
			'supports'           => array( 'title', 'thumbnail' )
		);

		register_post_type( 'custom-emoji', $args );
	}

	/**
	 * Returns an array of labels to use in the WordPress admin UI when managing the custom emoji cpt
	 */
	private function get_labels() {
		return array(
			'name'                  => esc_html( _x( 'Custom Emoji', 'post type general name', 'eomji-reactions' ) ),
			'singular_name'         => esc_html( _x( 'Custom Emoji', 'post type singular name', 'emoji-reactions' ) ),
			'menu_name'             => esc_html( _x( 'Custom Emoji', 'admin menu', 'emoji-reactions' ) ),
			'name_admin_bar'        => esc_html( _x( 'Custom Emoji', 'add new on admin bar', 'emoji-reactions' ) ),
			'add_new'               => esc_html( _x( 'Create', 'book', 'emoji-reactions' ) ),
			'add_new_item'          => esc_html__( 'Create New Custom Emoji', 'emoji-reactions' ),
			'new_item'              => esc_html__( 'Create Custom Emoji', 'emoji-reactions' ),
			'edit_item'             => esc_html__( 'Edit Custom Emoji', 'emoji-reactions' ),
			'view_item'             => esc_html__( 'View Custom Emoji', 'emoji-reactions' ),
			'all_items'             => esc_html__( 'All Custom Emoji', 'emoji-reactions' ),
			'search_items'          => esc_html__( 'Search custom emoji', 'emoji-reactions' ),
			'not_found'             => esc_html__( 'No custom emoji found.', 'emoji-reactions' ),
			'not_found_in_trash'    => esc_html__( 'No custom emoji found in trash.', 'emoji-reactions' ),
			'set_featured_image'    => esc_html__( 'Set emoji image', 'emoji-reactions' ),
			'remove_featured_image' => esc_html__( 'Remove', 'emoji-reactions' ),
			'use_featured_image'    => esc_html__( 'Set emoji image', 'emoji-reactions' ),
		);
	}

}

new Emoji_Reactions_Register_Custom_Emoji_CPT;
