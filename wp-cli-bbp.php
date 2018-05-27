<?php
namespace bbPress\CLI;

use WP_CLI;

// Bail if WP-CLI is not present.
if ( ! defined( '\WP_CLI' ) ) {
	return;
}

WP_CLI::add_hook( 'before_wp_load', function() {
	$commands = array(
		'user'         => 'User',
		'topic'        => 'Topic',
		'forum'        => 'Forum',
		'reply'        => 'Reply',
		'subscription' => 'Subscription',
		'favorite'     => 'Favorite',
		'engagement'   => 'Engagement',
		'moderator'    => 'Moderator',
	);

	// Load the commands.
	require_once( __DIR__ . '/component.php' );
	require_once( __DIR__ . '/components/tool.php' );
	foreach ( $commands as $key => $class ) {
		require_once( __DIR__ . '/components/' . $key . '.php' );
	}

	foreach ( $commands as $key => $class ) {
		WP_CLI::add_command( 'bbp ' . $key, __NAMESPACE__ . '\\Command\\' . $class, array(
			'before_invoke' => function() {
				if ( ! class_exists( 'bbPress' ) ) {
					WP_CLI::error( 'The bbPress plugin is not active.' );
				}
			},
		) );
	}

	WP_CLI::add_command( 'bbp tool', __NAMESPACE__ . '\\Command\\Tool', array(
		'before_invoke' => function() {
			if ( ! class_exists( 'bbPress' ) ) {
				WP_CLI::error( 'The bbPress plugin is not active.' );
			}

			require_once( bbpress()->includes_dir . 'admin/tools/common.php' );
			require_once( bbpress()->includes_dir . 'admin/tools/repair.php' );
			require_once( bbpress()->includes_dir . 'admin/tools/upgrade.php' );
			require_once( bbpress()->includes_dir . 'admin/tools/reset.php' );
		},
	) );
} );
