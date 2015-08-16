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

// Register the custom eomji custom post type
require_once( 'includes/register-custom-emoji-cpt.php' );
require_once( 'includes/utils.php' );
require_once( 'includes/prevent-reactions-in-comments.php' );

// Only load the admin files if we are in wp-admin
if ( is_admin() ) {
	require_once( 'includes/admin/custom-emoji.php' );
	require_once( 'includes/admin/discussion-settings.php' );
} else {
	require_once( 'includes/reaction-area.php' );
	require_once( 'includes/emoji-picker.php' );
}

// These are done on the front end, but have ajax hooks, so they need to be
// outside of the is_admin checks since ajax runs in admin-ajax.php
require_once( 'includes/do-reaction.php' );
require_once( 'includes/display-reactions.php' );
