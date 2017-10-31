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
	 *    $ wp bp forum open 456
	 */
	public function open( $args, $assoc_args ) {
		// Forum ID.
		$forum_id = $args[0];

		$user = bbp_open_forum( $forum_id );

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
	 *    $ wp bp forum close 847
	 */
	public function close( $args, $assoc_args ) {
		// Forum ID.
		$forum_id = $args[0];

		$user = bbp_close_forum( $forum_id );

		if ( is_numeric( $forum_id ) ) {
			WP_CLI::success( 'Forum closed.' );
		} else {
			WP_CLI::error( 'Could not close the forum.' );
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
