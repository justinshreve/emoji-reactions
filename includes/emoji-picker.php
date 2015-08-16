<?php
/**
 * Provides the front end code for the emoji picker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Emoji_Reactions_Emoji_Picker {

	private static $instance;

	/**
	 * Only return one instance of the custom emoji admin class
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Emoji_Reactions_Emoji_Picker();
		}
		return self::$instance;
	}

	/**
	 * Hook into WordPress
	 */
	public function __construct() {
		add_filter( 'emoji_reactions_reaction_area', array( $this, 'add_button' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ) );
		add_action( 'wp_footer', array( $this, 'popup_window' ), 30 );
	}

	/**
	 * Displays the button
	 */
	public function add_button( $content ) {
		$button = '<div class="emoji-reactions-button emoji-reactions-add-button" data-id="' . esc_attr( get_the_ID() ) . '" title="' . esc_html__( 'React', 'emoji-reactions' ) . '">';
		$button .= '&#x1f600;<span>+</span>';
		$button .= '</div>';
		return $content . apply_filters( 'emoji_reactions_reaction_area_add_button', $button );
	}

	/**
	 * The popup window wrapper where we list all of our emoji
	 * We only want this div once, so we append it to the filter and move it around later
	 */
	public function popup_window() {
		$window = '<div id="emoji-reactions-popup-window">';
		$window .= '<div id="emoji-menu-header"></div>';
		$window .= '<div id="emoji-label"></div>';
		$window .= '<div id="emoji-list"></div>';
		$window .= '</div>';
		echo $window;
	}

	/**
	 * Loads assets for the emoji picker
	 */
	public function load_assets() {
		wp_enqueue_style( 'emoji-reactions-emoji-picker-css', plugins_url( 'assets/css/emoji-picker.css' , dirname( __FILE__ ) ) );
		wp_enqueue_script( 'emoji-reactions-emoji-picker-js', plugins_url( 'assets/js/emoji-picker.js' , dirname( __FILE__ ) ), array( 'jquery' ) );

		// data for displaying the reactions area
		wp_localize_script( 'emoji-reactions-emoji-picker-js', 'emojiPickerData', array(
			'pluginURL' => plugins_url( '' , dirname( __FILE__ ) ),
			'custom'    => Emoji_Reactions_Utils::get_custom_emoji()
		) );

		// i18n strings
		wp_localize_script( 'emoji-reactions-emoji-picker-js', 'emojiPickerStrings', array(
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

Emoji_Reactions_Emoji_Picker::instance();
