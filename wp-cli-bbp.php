<?php

// Bail if WP-CLI is not present.
if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

WP_CLI::add_hook( 'before_wp_load', function() {
	require_once( __DIR__ . '/component.php' );
	require_once( __DIR__ . '/components/tool.php' );
	require_once( __DIR__ . '/components/user.php' );
	require_once( __DIR__ . '/components/moderator.php' );
	require_once( __DIR__ . '/components/forum.php' );
	require_once( __DIR__ . '/components/topic.php' );
	require_once( __DIR__ . '/components/reply.php' );
} );
