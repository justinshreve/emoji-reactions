<?php
/**
 * Provides an admin interface for our settings (under discussions)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Emoji_Reactions_Discussion_Settings_Admin {

	private static $instance;

	/**
	 * Only return one instance of the discussion settings admin class
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Emoji_Reactions_Discussion_Settings_Admin();
		}
		return self::$instance;
	}

	/**
	 * Hook into WordPress.. a
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'add_setting_section' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
	}

	/**
	 * Register our settings
	 */
	public function register_settings() {
		register_setting( 'discussion', 'emoji_reactions_allow_guest_reactions', array( $this, 'validate_checkbox' ) );
		register_setting( 'discussion', 'emoji_reactions_num_per_user_per_post', array( $this, 'validate_number' ) );
	}

	/**
	 * Creates a settings area for our discussion settings
	 */
	public function add_setting_section() {
		add_settings_section(
			'emoji_reactions_setting_section',
			esc_html__( 'Emoji Reactions', 'emoji-reactions' ),
			array( $this, 'section_description' ),
			'discussion'
		);
	}

	// Show no description
	public function section_description() { }

	/**
	 * Adds all of our emoji settings
	 */
	public function add_settings() {
	 	add_settings_field(
			'emoji_reactions_allow_guest_reactions',
			esc_html__( 'Allow Guest Reactions', 'emoji-reactions' ),
			array( $this, 'guest_reaction_setting' ),
			'discussion',
			'emoji_reactions_setting_section'
		);

 	 	add_settings_field(
 			'emoji_reactions_num_per_user_per_post',
 			esc_html__( '# of Reactions Per User Per Post', 'emoji-reactions' ),
 			array( $this, 'num_per_user_per_post_setting' ),
 			'discussion',
 			'emoji_reactions_setting_section'
 		);
	}

	/**
	 * Displays a checkbox for the "guest reactions" setting
	 */
	public function guest_reaction_setting() {
		$this->show_checkbox( 'emoji_reactions_allow_guest_reactions' );
		esc_html_e( 'Enable to allow logged out users to leave reactions.', 'emoji-reactions' );
	}

	/**
	 * Displays an input field for the "number of reactions per user per post" setting
	 */
	public function num_per_user_per_post_setting() {
		$this->show_number_input( 'emoji_reactions_num_per_user_per_post' );
		esc_html_e( 'Limits the number of reactions allowed per user per post. For logged in users, this is based on user ID. For guests this is based on IP address. Set to 0 to allow unlimited reactions.', 'emoji-reactions' );
	}

	/**
	 * Displays a checkbox
	 */
	public function show_checkbox( $name ) {
		echo '<input name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" type="checkbox" value="on" ' . checked( 'on', get_option( $name ), false ) . ' />';
	}

	/**
	 * Shows a number input field
	 */
	public function show_number_input( $name ) {
		echo '<input name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" type="number" style="width:50px;" value="' . intval( get_option( $name, 10 ) ) . '" min="0" step="1">';
	}

	/**
	* Validates checkbox options
	*/
	function validate_checkbox( $input ) {
		error_log( print_r ( $input, 1 ) );
		if ( ! $input || 'off' === $input ) {
			return 'off';
		}
		return 'on';
	}

	/**
	 * Validates a number field
	 */
	function validate_number( $input ) {
		return intval( absint( $input ) );
	}

}

Emoji_Reactions_Discussion_Settings_Admin::instance();
