<?php
/**
 * Forums Component - bbPress.
 *
 * @since 1.0.0
 */
class BBPCLI_Forums extends BBPCLI_Component {

	/**
	 * Create a forum.
	 *
	 * ## OPTIONS
	 *
	 * [--title=<title>]
	 * : Title of the forum.
	 *
	 * [--content=<content>]
	 * : Forum content. Default: 'Content for forum "[title]"'
	 *
	 * [--author=<author>]
	 * : ID of the forum author. Default: 1.
	 *
	 * [--status=<status>]
	 * : Forum status (open, close, hidden). Default: open.
	 *
	 * [--silent=<silent>]
	 * : Whether to silent the group creation. Default: false.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp forum create --title="Forum Test 01" --content="Content for forum" --author=39
	 *    $ wp bbp forum create --title="Forum Test 01" --content="Content for forum" --author=45 --status=closed
	 */
	public function create( $args, $assoc_args ) {
		$r = wp_parse_args( $assoc_args, array(
			'title'   => '',
			'content' => '',
			'author'  => 1,
			'status'  => 'open',
			'silent'  => false,
		) );

		if ( empty( $r['content'] ) ) {
			$r['content'] = sprintf( 'Content for the forum "%s"', $r['title'] );
		}

		$id = bbp_insert_forum( array(
			'post_title'   => $r['title'],
			'post_content' => $r['content'],
			'post_author'  => $r['author'],
		), $r['status'] );

		if ( $r['silent'] ) {
			return;
		}

		if ( is_numeric( $id ) ) {
			$permalink = bbp_get_forum_permalink( $id );
			WP_CLI::success( sprintf( 'Forum %d created: %s', $id, $permalink ) );
		} else {
			WP_CLI::error( 'Could not create forum.' );
		}
	}

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
