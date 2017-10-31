<?php
/**
 * Forums Component - bbPress.
 *
 * @since 1.0.0
 */
class BBPCLI_Forums extends BBPCLI_Component {

	/**
	 * Opens a forum.
	 *
	 * ## OPTIONS
	 *
	 * <forum-id>
	 * : Identifier for the forum.
	 *
	 * ## EXAMPLE
	 *
	 *    $ wp bbp forum open 456
	 */
	public function open( $args, $assoc_args ) {
		$forum_id = bbp_open_forum( $args[0] );

		if ( is_numeric( $forum_id ) ) {
			WP_CLI::success( 'Forum opened.' );
		} else {
			WP_CLI::error( 'Could not open the forum.' );
		}
	}

	/**
	 * Closes a forum.
	 *
	 * ## OPTIONS
	 *
	 * <forum-id>
	 * : Identifier for the forum.
	 *
	 * ## EXAMPLE
	 *
	 *    $ wp bbp forum close 847
	 */
	public function close( $args, $assoc_args ) {
		$forum_id = bbp_close_forum( $args[0] );

		if ( is_numeric( $forum_id ) ) {
			WP_CLI::success( 'Forum closed.' );
		} else {
			WP_CLI::error( 'Could not close the forum.' );
		}
	}

	/**
	 * Get the permalink of a forum.
	 *
	 * ## OPTIONS
	 *
	 * <forum-id>
	 * : Identifier for the forum.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp forum permalink 500
	 */
	public function permalink( $args, $assoc_args ) {
		$permalink = bbp_get_forum_permalink( $args[0] );

		if ( is_string( $permalink ) ) {
			WP_CLI::success( sprintf( 'Forum Permalink: %s', $permalink ) );
		} else {
			WP_CLI::error( 'No permalink found for the forum.' );
		}
	}
}

WP_CLI::add_command( 'bbp forum', 'BBPCLI_Forums', array(
	'before_invoke' => function() {
		if ( ! class_exists( 'bbPress' ) ) {
			WP_CLI::error( 'bbPress is not installed or active.' );
		}
	},
) );
