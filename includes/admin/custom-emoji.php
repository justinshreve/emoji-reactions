<?php
/**
 * Provides an admin interface for managing custom emoji
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @todo esc_html__
 * @todo rename all assets for this script to custom-emoji-admin.css/js
 */
class Emoji_Reactions_Custom_Emoji_Admin {

	/**
	 * Hook into WordPress.. alot
	 */
	public function __construct() {
		// custom post type related ui strings
		add_filter( 'post_updated_messages', array( $this, 'updated_update_messages' ) );
		add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_post_updated_messages'), 10, 2 );

		// hide and change various wp admin elements to make our admin experience nicer
		add_action( 'admin_head', array( $this, 'move_featured_image_meta_box' ) );
		add_filter( 'enter_title_here', array( $this, 'change_default_title' ) );
		add_action( 'edit_form_before_permalink', array( $this, 'add_title_description' ) );
		add_filter( 'admin_post_thumbnail_html', array( $this, 'add_image_description' ), 10, 2 );
		add_filter( 'get_sample_permalink_html', array( $this, 'hide_permalink' ), -10, 4 );
		add_filter( 'get_shortlink', array( $this, 'hide_permalink' ), -10, 4 );
		add_filter( 'post_row_actions', array( $this, 'remove_action_links' ), 10, 2 );
		add_filter( 'bulk_actions-edit-custom-emoji', array( $this, 'bulk_actions' ) );
		add_filter( 'months_dropdown_results', array( $this, 'hide_date_dropdown') , 10, 2 );
		add_filter( 'manage_custom-emoji_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_custom-emoji_posts_custom_column', array( $this, 'render_columns' ), 2 );

		// load all of our assets
		$pages = array( 'post', 'post-new', 'edit' );
		foreach ( $pages as $page ) {
			add_action( 'admin_print_styles-' . $page . '.php', array( $this, 'enqueue_admin_css' ) );
			add_action( 'admin_print_scripts-' . $page . '.php', array( $this, 'enqueue_admin_js' ) );
		}
	}

	/**
	 * Reports if the currently visible UI screen is a custom emoji management screen (so we do not overwrite other pages)
	 * @return boolean
	 */
	private function is_custom_emoji_screen() {
		$screen = get_current_screen();
		if ( ! empty ( $screen ) && 'custom-emoji' === $screen->post_type ) {
			return true;
		}

		// catches autosave so we can properly hide permalink preview
		if ( ! empty( $_POST['action'] ) && 'sample-permalink' === $_POST['action'] ) {
			$post = get_post( $_REQUEST['post_id'] );
			$post_type = get_post_type( $post );
			if ( 'custom-emoji' === $post_type ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * "Updated" Status Messages (after a custom emoji is saved/updated)
	 */
	public function updated_update_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages['custom-emoji'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => esc_html__( 'Custom emoji updated.', 'emoji-reactions' ),
			2  => esc_html__( 'Custom emoji updated.', 'emoji-reactions' ),
			3  => esc_html__( 'Custom emoji deleted.', 'emoji-reactions' ),
			4  => esc_html__( 'Custom emoji updated.', 'emoji-reactions' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Custom emoji restored to revision from %s', 'emoji-reactions' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => esc_html__( 'Custom emoji published.', 'emoji-reactions' ),
			7  => esc_html__( 'Custom emoji saved.', 'emoji-reactions' ),
			8  => esc_html__( 'Custom emoji submitted.', 'emoji-reactions' ),
			9  => sprintf(
				__( 'Custom emoji scheduled for: <strong>%1$s</strong>.', 'custom-emojis' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'custom-emojis' ), strtotime( $post->post_date ) )
			),
			10 => esc_html__( 'Custom emoji draft updated.', 'emoji-reactions' )
		);

		return $messages;
	}

	/**
	 * Messages to show when custom emoji are updated via action links/bulk edit
	 */
	public function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {
		$bulk_messages['custom-emoji'] = array(
			'deleted'   => esc_html( _n( '%s custom emoji permanently deleted.', '%s custom emoji permanently deleted.', $bulk_counts['deleted'], 'emoji-reactions' ) ),
			'trashed'   => esc_html( _n( '%s custom emoji moved to the trash.', '%s custom emoji moved to the trash.', $bulk_counts['trashed'], 'emoji-reactions' ) ),
			'untrashed' => esc_html( _n( '%s custom emoji restored from the trash.', '%s custom emoji restored from the trash.', $bulk_counts['untrashed'], 'emoji-reactions' ) ),
		);
		return $bulk_messages;
	}

	/**
	 * Moves the "featured image" metabox to the main/"advanced" column and renames it to image
	 */
	public function move_featured_image_meta_box() {
		if ( ! $this->is_custom_emoji_screen() ) {
			return;
		}

		remove_meta_box( 'postimagediv', 'custom-emoji', 'side' );
		if ( current_user_can( 'upload_files' ) ) {
			add_meta_box('postimagediv', esc_html__( 'Image', 'emoji-reactions' ), 'post_thumbnail_meta_box', null, 'advanced', 'high' );
		}
	}

	/**
	 * Renames the "title" box on the create new emoji screen to be more friendly
	 */
	public function change_default_title( $title ) {
		if ( $this->is_custom_emoji_screen() ) {
			$title = esc_html__( 'Choose a name', 'emoji-reactions' );
		}
		return $title;
	}

	/**
	 * Adds a helpful description under the "title" input field
	 * we use edit_form_before_permalink instead of after_title because it displays closer to the input box
	 */
	public function add_title_description() {
		if ( ! $this->is_custom_emoji_screen() ) {
			return;
		} ?>
		<p class="description" id="custom-emoji-title-description"><?php esc_html_e( 'This is what you will enter to search for this emoji.', 'emoji-reactions' ); ?></p><?php
	}

	/**
	 * Adds a helpful description under the "image" input area
	 */
	public function add_image_description( $content, $post_id ) {
		if ( ! $this->is_custom_emoji_screen() ) {
			return $content;
		}
		$content .= "\n<p class='description' id='custom-emoji-image-description'>";
		$content .= esc_html__( "Square images work best.", 'emoji-reactions' );
		$content .= "</p>";
		return $content;
	}

	/**
	 * Hides the permalink and shortlink areas
	 * Both filters take 4 args so we will just wipe them out with one function
	 */
	public function hide_permalink( $return, $second, $third, $fourth ) {
		if ( ! $this->is_custom_emoji_screen() ) {
			return $return;
		}
		return "";
	}

	/**
	 * Removes the quick edit and view action links from the list table
	 */
	public function remove_action_links( $actions ) {
		if ( ! $this->is_custom_emoji_screen() ) {
			return $actions;
		}

		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['view'] );
	    return $actions;
	}

	/**
	 * Removes the edit option from bulk actions, since we are turning off quick edit
	 */
	public function bulk_actions( $actions ) {
		if ( ! $this->is_custom_emoji_screen() ) {
			return $actions;
		}

		unset( $actions['edit'] );
		return $actions;
	}

	/**
	 * Hides the date dropdown filter
	 */
	public function hide_date_dropdown( $months, $post_type ) {
		if ( 'custom-emoji' !== $post_type ) {
			return $months;
		}
		return array();
	}

	/**
	 * Load's the admin CSS for our custom emoji screens
	 */
	public function enqueue_admin_css() {
		if ( ! $this->is_custom_emoji_screen() ) {
			return;
		}

		wp_enqueue_style( 'emoji-reactions-create-admin-css', plugins_url( 'assets/css/create-admin.css' , dirname ( dirname( __FILE__ ) ) ) );
	}

	/**
	 * Load's the admin JS for our custom emoji screens
	 */
	public function enqueue_admin_js() {
		if ( ! $this->is_custom_emoji_screen() ) {
			return;
		}

		wp_enqueue_script( 'emoji-reactions-create-admin-js', plugins_url( 'assets/js/create-admin.js' , dirname ( dirname ( __FILE__ ) ) ) );
		wp_localize_script( 'emoji-reactions-create-admin-js', 'emojiReactionsStrings', array(
			'saveNewEmoji'    => esc_html__( 'Save New Emoji', 'emoji-reactions' ),
			'save'            => esc_html__( 'Save', 'emoji-reactions' ),
		) );
	}

	/**
	 * Reduce the list table to just a checkbox, image, and name
	 */
	public function columns( $existing_columns ) {
		$columns          = array();
		$columns['cb']    = '<input type="checkbox" />';
		$columns['image'] = esc_html__( 'Image', 'emoji-reactions' );
		$columns['name']  = esc_html__( 'Name', 'emoji-reactions' );
		return $columns;
	}

	/**
	 * Define how the image and name fields are outputed
	 */
	public function render_columns( $column ) {
		global $post;
		switch ( $column ) {
			case 'image' :
				echo '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . the_post_thumbnail( array( 44, 44 ) ) . '</a>';
				break;
			case 'name' :
				echo '<strong><a class="row-title" href="' . esc_url( get_edit_post_link( $post->ID ) ) .'">' . esc_html( _draft_or_post_title() ) .'</a></strong>';
			default :
				break;
		}
	}

}

new Emoji_Reactions_Custom_Emoji_Admin;
