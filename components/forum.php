<?php
/**
 * Manage bbPress Forums.
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
	 *     $ wp bbp forum create --title="Forum Test 01" --content="Content for forum" --user-id=39
	 *     $ wp bbp forum create --title="Forum 02" --content="Another content for forum" --user-id=45 --status=closed
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
	 * Get a forum.
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
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp bbp forum get 6654
	 *     $ wp bbp forum get 4677 --field=content
	 */
	public function get( $args, $assoc_args ) {
		$forum_id = $args[0];

		// Check if forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		$forum = bbp_get_forum( $forum_id, ARRAY_A );

		if ( empty( $assoc_args['fields'] ) ) {
			$assoc_args['fields'] = array_keys( $forum );
		}

		$formatter = $this->get_formatter( $assoc_args );
		$formatter->display_item( $forum );
	}

	/**
	 * Delete a forum (its topics and replies).
	 *
	 * ## OPTIONS
	 *
	 * <forum-id>...
	 * : One or more IDs of forums to delete.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp forum delete 486
	 *     Success: Forum 486 and its topics and replies deleted.
	 */
	public function delete( $args, $assoc_args ) {
		$forum_id = $args[0];

		// Check if forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		parent::_delete( $args, $assoc_args, function ( $forum_id, $assoc_args ) {

			bbp_delete_forum_topics( $forum_id );

			wp_delete_post( $forum_id, true );

			$r = bbp_deleted_forum( $forum_id );

			if ( ! $r ) {
				return array( 'success', sprintf( 'Forum %d and its topics and replies deleted.', $forum_id ) );
			} else {
				return array( 'error', sprintf( 'Could not delete forum %d and its topics and replies.', $forum_id ) );
			}
		} );
	}

	/**
	 * Get a list of forums.
	 *
	 * ## OPTIONS
	 *
	 * [--<field>=<value>]
	 * : One or more args to pass to WP_Query.
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
	 *   - count
	 *   - json
	 *   - csv
	 *   - yaml
	 * ---
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
	 * @subcommand list
	 */
	public function _list( $_, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		$query_args = wp_parse_args( $assoc_args, array(
			'post_type' => bbp_get_forum_post_type(),
			'post_status' => bbp_get_public_status_id(),
		) );

		$query_args = self::process_csv_arguments_to_arrays( $query_args );

		$forum_post_type = bbp_get_forum_post_type();
		if ( isset( $query_args['post_type'] ) && $forum_post_type !== $query_args['post_type'] ) {
			$query_args['post_type'] = $forum_post_type;
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
	 * : Indentifier of the forum to trash.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp forum trash 789
	 *     Success: Forum 789 and its topics trashed.
	 */
	public function trash( $args, $assoc_args ) {
		$forum_id = $args[0];

		// Check if forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		bbp_trash_forum_topics( $forum_id );

		wp_trash_post( $forum_id );

		if ( ! bbp_trashed_forum( $forum_id ) ) {
			WP_CLI::success( sprintf( 'Forum %d and its topics trashed.', $forum_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not trash forum %d and its topics.', $forum_id ) );
		}
	}

	/**
	 * Untrash a forum.
	 *
	 * ## OPTIONS
	 *
	 * <forum-id>
	 * : Indentifier of the forum to untrash.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp forum untrash 3938
	 *     Success: Forum 3938 and its topics untrashed.
	 */
	public function untrash( $args, $assoc_args ) {
		$forum_id = $args[0];

		// Check if forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		wp_untrash_post( $forum_id );

		bbp_untrash_forum_topics( $forum_id );

		if ( ! bbp_untrashed_forum( $forum_id ) ) {
			WP_CLI::success( sprintf( 'Forum %d and its topics untrashed.', $forum_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not untrash forum %d and its topics.', $forum_id ) );
		}
	}

	/**
	 * Generate random forums (forums only).
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
	 *     $ wp bbp forum generate --count=50
	 *     $ wp bbp forum generate --count=20 --status=closed
	 *     $ wp bbp forum generate --count=15 --status=hidden
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
	 * Open a forum.
	 *
	 * ## OPTIONS
	 *
	 * <forum-id>
	 * : Indentifier of the forum to open.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp forum open 456
	 *     Success: Forum 456 successfully opened.
	 */
	public function open( $args, $assoc_args ) {
		$forum_id = $args[0];

		// Check if forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		$id = bbp_open_forum( $forum_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Forum %d successfully opened.', $forum_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not open forum %d.', $forum_id ) );
		}
	}

	/**
	 * Close a forum.
	 *
	 * ## OPTIONS
	 *
	 * <forum-id>
	 * : Indentifier of the forum to close.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp forum close 847
	 *     Success: Forum 847 successfully closed.
	 */
	public function close( $args, $assoc_args ) {
		$forum_id = $args[0];

		// Check if forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		$id = bbp_close_forum( $forum_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Forum %d successfully closed.', $forum_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not close the forum %d.', $forum_id ) );
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
	 *     $ wp bbp forum permalink 500
	 *     Success: Forum Permalink: http://site.com/forums/forum/forum-slug/
	 *
	 *     $ wp bbp forum url 456
	 *     Success: Forum Permalink: http://site.com/forums/forum/another-forum-slug/
	 *
	 * @alias url
	 */
	public function permalink( $args, $assoc_args ) {
		$forum_id = $args[0];

		// Check if forum exists.
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
