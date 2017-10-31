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
	 * : Forum title.
	 *
	 * [--content=<content>]
	 * : Forum content.
	 * ---
	 * default: 'Content for forum "[title]"'
	 * ---
	 *
	 * [--user-id=<user-id>]
	 * : Identifier of the user.
	 * ---
	 * default: 1
	 * ---
	 *
	 * [--status=<status>]
	 * : Forum status (open, close, hidden).
	 * ---
	 * default: open
	 * ---
	 *
	 * [--silent=<silent>]
	 * : Whether to silent the forum creation.
	 * ---
	 * default: false
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp forum create --title="Forum Test 01" --content="Content for forum" --user-id=39
	 *    $ wp bbp forum create --title="Forum 02" --content="Another content for forum" --user-id=45 --status=closed
	 */
	public function create( $args, $assoc_args ) {
		$r = wp_parse_args( $assoc_args, array(
			'title'   => '',
			'content' => '',
			'user-id'  => 1,
			'status'  => 'open',
			'silent'  => false,
		) );

		if ( empty( $r['content'] ) ) {
			$r['content'] = sprintf( 'Content for the forum "%s"', $r['title'] );
		}

		$id = bbp_insert_forum( array(
			'post_title'   => $r['title'],
			'post_content' => $r['content'],
			'post_author'  => $r['user-id'],
		), $r['status'] );

		if ( $r['silent'] ) {
			return;
		}

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Forum %d created: %s', $id, bbp_get_forum_permalink( $id ) ) );
		} else {
			WP_CLI::error( 'Could not create forum.' );
		}
	}

	/**
	 * Gets a forum.
	 *
	 * ## OPTIONS
	 *
	 * <forum-id>
	 * : Identifier for the forum.
	 *
	 * [--field=<field>]
	 * : Instead of returning the whole forum, returns the value of a single field.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields. Defaults to all fields.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *   $ wp bbp forum get 6654
	 *   $ wp bbp forum get 4677 --field=content
	 */
	public function get( $args, $assoc_args ) {
		$forum_id = $args[0];

		// Check that forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		$forum = bbp_get_forum( $forum_id, ARRAY_A );

		if ( empty( $assoc_args['fields'] ) ) {
			$assoc_args['fields'] = array_keys( $forum );
		}

		$formatter = $this->get_formatter( $assoc_args );
		$formatter->display_items( $forum );
	}

	/**
	 * Delete a forum.
	 *
	 * ## OPTIONS
	 *
	 * <forum-id>
	 * : Identifier for the forum.
	 *
	 * ## EXAMPLE
	 *
	 *   $ wp bbp forum delete 486
	 */
	public function delete( $args, $assoc_args ) {
		$forum_id = $args[0];

		// Check that forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		bbp_delete_forum_topics( $forum_id );

		if ( ! bbp_deleted_forum( $forum_id ) ) {
			WP_CLI::success( 'Forum and its topics and replies deleted.' );
		} else {
			WP_CLI::error( 'Could not delete the forum and its topics and replies.' );
		}
	}

	/**
	 * Get a list of forums.
	 *
	 * ## OPTIONS
	 *
	 * [--<field>=<value>]
	 * : One or more args to pass to WP_Query.
	 *
	 * [--field=<field>]
	 * : Prints the value of a single field for each forum.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific object fields.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - ids
	 *   - json
	 *   - count
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each forum:
	 *
	 * * ID
	 * * post_title
	 * * post_name
	 * * post_date
	 * * post_status
	 *
	 * ## EXAMPLES
	 *
	 *     # List ids of all forums
	 *     $ wp bbp forum list --format=ids
	 *     15 25 34 37 198
	 *
	 *     # List total count of forums
	 *     $ wp bbp forum list --format=count
	 *     451
	 *
	 *     # List given forums
	 *     $ wp bbp forum list --post__in=2,1
	 *     +----+--------------+-------------+---------------------+-------------+
	 *     | ID | post_title   | post_name   | post_date           | post_status |
	 *     +----+--------------+-------------+---------------------+-------------+
	 *     | 2  | Forum Title  | lorem-ipsum | 2016-06-01 14:34:36 | publish     |
	 *     | 1  | Other Forum  | hello-world | 2016-06-01 14:31:12 | publish     |
	 *     +----+--------------+-------------+---------------------+-------------+
	 *
	 * @subcommand list
	 */
	public function _list( $args, $assoc_args ) {

		$formatter = $this->get_formatter( $assoc_args );

		$query_args = wp_parse_args( $assoc_args, array(
			'post_type'    => bbp_get_forum_post_type(),
			'post_status'  => bbp_get_public_status_id(),
		) );

		if ( isset( $query_args['post_type'] ) && bbp_get_forum_post_type() !== $query_args['post_type'] ) {
			$query_args['post_type'] = bbp_get_forum_post_type();
		}

		if ( 'ids' === $formatter->format ) {
			$query_args['fields'] = 'ids';
			$query = new WP_Query( $query_args );
			echo implode( ' ', $query->posts ); // WPCS: XSS ok.
		} elseif ( 'count' === $formatter->format ) {
			$query_args['fields'] = 'ids';
			$query = new WP_Query( $query_args );
			$formatter->display_items( $query->posts );
		} else {
			$query = new WP_Query( $query_args );
			$formatter->display_items( $query->posts );
		}
	}

	/**
	 * Trash a forum.
	 *
	 * ## OPTIONS
	 *
	 * <forum-id>
	 * : Identifier for the forum.
	 *
	 * ## EXAMPLE
	 *
	 *   $ wp bbp forum trash 789
	 */
	public function trash( $args, $assoc_args ) {
		$forum_id = $args[0];

		// Check that forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		bbp_trash_forum_topics( $forum_id );

		if ( ! bbp_trashed_forum( $forum_id ) ) {
			WP_CLI::success( 'All forum topics trashed.' );
		} else {
			WP_CLI::error( 'Could not trash forum topics.' );
		}
	}

	/**
	 * Untrash a forum.
	 *
	 * ## OPTIONS
	 *
	 * <forum-id>
	 * : Identifier for the forum.
	 *
	 * ## EXAMPLE
	 *
	 *   $ wp bbp forum untrash 789
	 */
	public function untrash( $args, $assoc_args ) {
		$forum_id = $args[0];

		// Check that forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		bbp_untrash_forum_topics( $forum_id );

		if ( ! bbp_untrashed_forum( $forum_id ) ) {
			WP_CLI::success( 'All forum topics untrashed.' );
		} else {
			WP_CLI::error( 'Could not untrash forum topics.' );
		}
	}

	/**
	 * Generate random forums.
	 *
	 * ## OPTIONS
	 *
	 * [--count=<number>]
	 * : How many forums to generate.
	 * ---
	 * default: 100
	 * ---
	 *
	 * [--status=<status>]
	 * : Forum status (open, close, hidden).
	 * ---
	 * default: open
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *   $ wp bbp forum generate --count=50
	 *   $ wp bbp forum generate --count=20 --status=closed
	 *   $ wp bbp forum generate --count=15 --status=hidden
	 */
	public function generate( $args, $assoc_args ) {
		$r = wp_parse_args( $assoc_args, array(
			'count'  => 100,
			'status' => 'open',
		) );

		$notify = \WP_CLI\Utils\make_progress_bar( 'Generating forums', $r['count'] );

		for ( $i = 0; $i < $r['count']; $i++ ) {
			$this->create( array(), array(
				'title'   => sprintf( 'Test Forum - #%d', $i ),
				'content' => sprintf( 'Content for the forum - #%d', $i ),
				'status'  => $r['status'],
				'silent'  => true,
			) );

			$notify->tick();
		}

		$notify->finish();
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
		$forum_id = $args[0];

		// Check that forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		$id = bbp_open_forum( $forum_id );

		if ( is_numeric( $id ) ) {
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
		$forum_id = $args[0];

		// Check that forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		$id = bbp_close_forum( $forum_id );

		if ( is_numeric( $id ) ) {
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
		$forum_id = $args[0];

		// Check that forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		$permalink = bbp_get_forum_permalink( $forum_id );

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
