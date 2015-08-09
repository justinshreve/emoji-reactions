<?php
/**
 * Reaction area provides convenient hub to connect our reactions controls to
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Emoji_Reactions_Reaction_Area {

	public $actions = array( 'the_content' );
	public $priority = 1000;

	private static $instance;

	/**
	 * Only return one instance of the reaction area class
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Emoji_Reactions_Reaction_Area();
		}
		return self::$instance;
	}

	/**
	 * Hook into WordPress
	 */
	public function __construct() {
		$this->filter_vars();
		$this->load_assets();
		add_action( 'init',  array( $this, 'add_filters' ) );
	}

	/**
	 * Makes it so our settings (like actions and priority) can be changed
	 */
	public function filter_vars() {
		$this->actions = apply_filters( 'emoji_reactions_reaction_area_actions', $this->actions );
		$this->priority = apply_filters( 'emoji_reactions_reaction_area_priority', $this->priority );
	}

	/**
	 * Adds the reaction area filters to $actions
	 */
	public function add_filters() {
		foreach ( $this->actions as $action ) {
			add_filter( $action, array( $this, 'display' ), $this->priority );
		}

		add_filter( 'emoji_reactions_reaction_area', array( $this, 'before_display' ), -999 );
		add_filter( 'emoji_reactions_reaction_area', array( $this, 'after_display'  ),  999 );
	}

	/**
	 * Display the reaction area at the end of post's content
	 *
	 * @param string $content
	 * @return string Filtered content
	 */
	public function display( $content = '' ) {
		$filtered_content = apply_filters( 'emoji_reactions_reaction_area', $content );
		$empty_flair = $this->before_display( '' ) . $this->after_display( '' );

		if ( $content . $empty_flair == $filtered_content ) {
			return $content;
		}
		else {
			return $filtered_content;
		}
	}

	/**
	 * Prepend some HTML before the start of the reaction area
	 *
	 * @param string $content
	 * @return string
	 */
	public function before_display( $content = '' ) {
		return $content . apply_filters( 'emoji_reactions_reaction_area_before', '<div class="' . $this->get_css_classes() . '">' );
	}

	/**
	 * Append some HTML after 'post_flair'
	 *
	 * @param string $content
	 * @return string
	 */
	public function after_display( $content = '' ) {
		return $content . apply_filters( 'emoji_reactions_reaction_area_after', '</div>' );
	}

	/**
	 * Returns a string of CSS classes that the reaction area should be wrapped in
	 * @return string
	 */
	public function get_css_classes() {
		$classes = array( 'emoji-reactions-reaction-area' );
		return implode( ' ', apply_filters( 'emoji_reactions_reaction_area_css_classes', $classes ) );
	}

	/**
	 * Loads all of our custom emoji so we can pass it to Javascript
	 */
	public function get_custom_emoji() {
		$args = array(
			'posts_per_page' => -1,
			'post_type'      => 'custom-emoji',
			'post_status'    => 'publish',
		);
		$custom_emoji_wp = get_posts( $args );

		$custom_emoji = array();
		foreach ( $custom_emoji_wp as $custom_emoji_wp_single ) {
			$custom_emoji[ $custom_emoji_wp_single->post_title ] = wp_get_attachment_url( get_post_thumbnail_id( $custom_emoji_wp_single->ID ) );
		}

		return $custom_emoji;
	}

	/**
	 * Loads any front end assets that we need
	 */
	public function load_assets() {
		wp_enqueue_style( 'emoji-reactions-reaction-area-css', plugins_url( 'assets/css/reaction-area.css' , dirname( __FILE__ ) ) );
		wp_enqueue_script( 'emoji-reactions-reaction-area-js', plugins_url( 'assets/js/reaction-area.js' , dirname( __FILE__ ) ), array( 'jquery' ) );

		$custom_emoji = $this->get_custom_emoji();

		wp_localize_script( 'emoji-reactions-reaction-area-js', 'emojiReactionsData', array(
			'pluginURL' => plugins_url( '' , dirname( __FILE__ ) ),
			'custom'    => $custom_emoji,
		) );
		wp_localize_script( 'emoji-reactions-reaction-area-js', 'emojiReactionsStrings', array(
			'people' => esc_html__( 'People', 'emoji-reactions' ),
			'nature' => esc_html__( 'Nature', 'emoji-reactions' ),
			'foods' =>  esc_html__( 'Food & Drink', 'emoji-reactions' ),
			'celebration' => esc_html__( 'Celebration', 'emoji-reactions' ),
			'activity' => esc_html__( 'Activity', 'emoji-reactions' ),
			'places' => esc_html__( 'Travel & Places', 'emoji-reactions' ),
			'symbols' => esc_html__( 'Objects & Symbols', 'emoji-reactions' ),
			'custom' => esc_html__( 'Custom', 'emoji-reactions' ),
		) );
	}

}

Emoji_Reactions_Reaction_Area::instance();
