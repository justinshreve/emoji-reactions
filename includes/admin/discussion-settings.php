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
	}

	/**
	 * Displays a checkbox for the "guest reactions" setting
	 */
	public function guest_reaction_setting() {
		$this->show_checkbox( 'emoji_reactions_allow_guest_reactions' );
		esc_html_e( 'Enable to allow logged out users to leave reactions.', 'emoji-reactions' );
	}

	public function show_checkbox( $name ) {
		echo '<input name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" type="checkbox" value="on" ' . checked( 'on', get_option( $name ), false ) . ' />';
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

}

Emoji_Reactions_Discussion_Settings_Admin::instance();
