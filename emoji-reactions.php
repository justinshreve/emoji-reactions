<?php
/**
 * Plugin Name: Emoji Reactions
 * Plugin URI: http://wordpress.org/plugins/emoji-reactions/
 * Description: Give your readers more ways to provide feedback on your posts.
 * Author: Justin Shreve
 * Author URI: http://justin.gs
 * Version: 1.0.0-pre
 * License: GPLv2 or later
 * Text Domain: emoji-reactions
 * Domain Path: languages/
 */

class Emoji_Reactions_Register_CPT {
	public function __construct() {
		add_action( 'init', array( $this, 'register_custom_emoji_cpt' ) );
		add_action( 'admin_head', array( $this, 'move_featured_image_meta_box' ) );
		add_filter( 'enter_title_here', array( $this, 'change_default_title' ) );
		add_action( 'edit_form_before_permalink', array( $this, 'add_title_description' ) );
		add_filter( 'admin_post_thumbnail_html', array( $this, 'add_image_description' ), 10, 2 );
		add_filter( 'get_sample_permalink_html', array( $this, 'hide_permalink' ), -10, 4 );
		add_filter( 'get_shortlink', array( $this, 'hide_permalink' ), -10, 4 );
		add_action( 'admin_print_styles-post.php', array( $this, 'enqueue_admin_css' ) );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'enqueue_admin_css' ) );
		add_action( 'admin_print_styles-edit.php', array( $this, 'enqueue_admin_css' ) );
		add_action( 'admin_print_scripts-post.php', array( $this, 'enqueue_admin_js' ) );
		add_action( 'admin_print_scripts-post-new.php', array( $this, 'enqueue_admin_js' ) );
		add_action( 'admin_print_scripts-edit.php', array( $this, 'enqueue_admin_js' ) );
		add_filter( 'post_updated_messages', array( $this, 'updated_update_messages' ) );
		add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_post_updated_messages'), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'remove_action_links' ), 10, 2 );
		add_filter( 'bulk_actions-edit-custom-emoji', array( $this, 'bulk_actions' ) );
		add_filter( 'months_dropdown_results', array( $this, 'hide_date_dropdown') , 10, 2 );

		add_filter( 'manage_custom-emoji_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_custom-emoji_posts_custom_column', array( $this, 'render_columns' ), 2 );
	}

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

	public function register_custom_emoji_cpt() {
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
	 * Returns an array of labels to use in the WordPress admin UI
	 * @return array
	 * @todo esc_html__
	 */
	private function get_labels() {
		return array(
				'name'                  => _x( 'Custom Emoji', 'post type general name', 'eomji-reactions' ),
				'singular_name'         => _x( 'Custom Emoji', 'post type singular name', 'emoji-reactions' ),
				'menu_name'             => _x( 'Custom Emoji', 'admin menu', 'emoji-reactions' ),
				'name_admin_bar'        => _x( 'Custom Emoji', 'add new on admin bar', 'emoji-reactions' ),
				'add_new'               => _x( 'Create', 'book', 'emoji-reactions' ),
				'add_new_item'          => __( 'Create New Custom Emoji', 'emoji-reactions' ),
				'new_item'              => __( 'Create Custom Emoji', 'emoji-reactions' ),
				'edit_item'             => __( 'Edit Custom Emoji', 'emoji-reactions' ),
				'view_item'             => __( 'View Custom Emoji', 'emoji-reactions' ),
				'all_items'             => __( 'All Custom Emoji', 'emoji-reactions' ),
				'search_items'          => __( 'Search custom emoji', 'emoji-reactions' ),
				'not_found'             => __( 'No custom emoji found.', 'emoji-reactions' ),
				'not_found_in_trash'    => __( 'No custom emoji found in trash.', 'emoji-reactions' ),
				'set_featured_image'    => __( 'Set emoji image', 'emoji-reactions' ),
				'remove_featured_image' => __( 'Remove', 'emoji-reactions' ),
				'use_featured_image'    => __( 'Set emoji image', 'emoji-reactions' ),
		);
	}

	public function updated_update_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages['custom-emoji'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Custom emoji updated.', 'emoji-reactions' ),
			2  => __( 'Custom emoji updated.', 'emoji-reactions' ),
			3  => __( 'Custom emoji deleted.', 'emoji-reactions' ),
			4  => __( 'Custom emoji updated.', 'emoji-reactions' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Custom emoji restored to revision from %s', 'emoji-reactions' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Custom emoji published.', 'emoji-reactions' ),
			7  => __( 'Custom emoji saved.', 'emoji-reactions' ),
			8  => __( 'Custom emoji submitted.', 'emoji-reactions' ),
			9  => sprintf(
				__( 'Custom emoji scheduled for: <strong>%1$s</strong>.', 'custom-emojis' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'custom-emojis' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Custom emoji draft updated.', 'emoji-reactions' )
		);

		return $messages;
	}

	public function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {
		$bulk_messages['custom-emoji'] = array(
			'deleted'   => _n( '%s custom emoji permanently deleted.', '%s custom emoji permanently deleted.', $bulk_counts['deleted'], 'emoji-reactions' ),
			'trashed'   => _n( '%s custom emoji moved to the trash.', '%s custom emoji moved to the trash.', $bulk_counts['trashed'], 'emoji-reactions' ),
			'untrashed' => _n( '%s custom emoji restored from the trash.', '%s custom emoji restored from the trash.', $bulk_counts['untrashed'], 'emoji-reactions' ),
		);
		return $bulk_messages;
	}

	public function move_featured_image_meta_box() {
		// Bail if we are not on a custom emoji admin screen
		if ( ! $this->is_custom_emoji_screen() ) {
			return;
		}

		remove_meta_box( 'postimagediv', 'custom-emoji', 'side' );
		if ( current_user_can( 'upload_files' ) ) {
			add_meta_box('postimagediv', esc_html__( 'Image', 'emoji-reactions' ), 'post_thumbnail_meta_box', null, 'advanced', 'high' );
		}
	}

	public function change_default_title( $title ) {
		if ( $this->is_custom_emoji_screen() ) {
			$title = esc_html__( 'Choose a name' );
		}
		return $title;
	}

	// we use edit_form_before_permalink instead of after_title because it displays closer to the input box
	public function add_title_description() {
		// Bail if we are not on a custom emoji admin screen
		if ( ! $this->is_custom_emoji_screen() ) {
			return;
		} ?>
		<p class="description" id="custom-emoji-title-description"><?php esc_html_e( 'This is what you will enter to search for this emoji.', 'custom-emoji' ); ?></p><?php
	}

	public function add_image_description( $content, $post_id ) {
		// Bail if we are not on a custom emoji admin screen
		if ( ! $this->is_custom_emoji_screen() ) {
			return $content;
		}
		$content .= "\n<p class='description' id='custom-emoji-image-description'>";
		$content .= esc_html__( "Square images work best.", 'custom-emoji' );
		$content .= "</p>";
		return $content;
	}

	// Used to override two filters with 4 args
	public function hide_permalink( $return, $second, $third, $fourth ) {
		// Bail if we are not on a custom emoji admin screen
		if ( ! $this->is_custom_emoji_screen() ) {
			return $return;
		}
		return "";
	}

	public function remove_action_links( $actions ) {
		// Bail if we are not on a custom emoji admin screen
		if ( ! $this->is_custom_emoji_screen() ) {
			return $actions;
		}

		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['view'] );
	    return $actions;
	}

	public function bulk_actions( $actions ) {
		// Bail if we are not on a custom emoji admin screen
		if ( ! $this->is_custom_emoji_screen() ) {
			return $actions;
		}

		unset( $actions['edit'] );
		return $actions;
	}

	public function hide_date_dropdown( $months, $post_type ) {
		if ( 'custom-emoji' !== $post_type ) {
			return $months;
		}
		return array();
	}

	public function enqueue_admin_css() {
		// Bail if we are not on a custom emoji admin screen
		if ( ! $this->is_custom_emoji_screen() ) {
			return;
		}

		wp_enqueue_style( 'emoji-reactions-create-admin-css', plugins_url( 'assets/css/create-admin.css' , __FILE__ ) );
	}

	public function enqueue_admin_js() {
		// Bail if we are not on a custom emoji admin screen
		if ( ! $this->is_custom_emoji_screen() ) {
			return;
		}

		wp_enqueue_script( 'emoji-reactions-create-admin-js', plugins_url( 'assets/js/create-admin.js' , __FILE__ ) );
		wp_localize_script( 'emoji-reactions-create-admin-js', 'emojiReactionsStrings', array(
			'saveNewEmoji'  => esc_html__( 'Save New Emoji', 'emoji-reactions' ),
			'save'            => esc_html__( 'Save', 'emoji-reactions' ),
		) );
	}

	public function columns( $existing_columns ) {
		$columns          = array();
		$columns['cb']    = '<input type="checkbox" />';
		$columns['thumb'] = __( 'Image', 'emoji-reactions' );
		$columns['name']  = __( 'Name', 'emoji-reactions' );
		return $columns;
	}

	public function render_columns( $column ) {
		global $post;

		switch ( $column ) {
			case 'thumb' :
				echo '<a href="' . get_edit_post_link( $post->ID ) . '">' . the_post_thumbnail( array( 44, 44 ) ) . '</a>';
				break;
			case 'name' :
				$edit_link = get_edit_post_link( $post->ID );
				$title     = _draft_or_post_title();

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . $title .'</a>';
				echo '</strong>';
			default :
				break;
		}
	}
}

new Emoji_Reactions_Register_CPT;

require_once( 'includes/admin/custom-emoji.php' );