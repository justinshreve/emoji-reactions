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
		add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ) );
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
	 * Loads any front end assets that we need
	 */
	public function load_assets() {
		wp_enqueue_style( 'emoji-reactions-reaction-area-css', plugins_url( 'assets/css/reaction-area.css' , dirname( __FILE__ ) ) );
	}

}

Emoji_Reactions_Reaction_Area::instance();
