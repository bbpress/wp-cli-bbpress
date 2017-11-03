<?php
/**
 * Manage bbPress Replies.
 *
 * @since 1.0.0
 */
class BBPCLI_Replies extends BBPCLI_Component {

	/**
	 * Create a reply.
	 *
	 * ## OPTIONS
	 *
	 * [--title=<title>]
	 * : Reply title.
	 *
	 * [--content=<content>]
	 * : Reply content.
	 * ---
	 * default: 'Content for reply "[title]"'
	 * ---
	 *
	 * [--user-id=<user-id>]
	 * : Identifier of the user.
	 * ---
	 * default: 1
	 * ---
	 *
	 * [--topic-id=<topic-id>]
	 * : Identifier of the Topic the replied is for.
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--forum-id=<forum-id>]
	 * : Identifier of the Forum the replied is for.
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--silent=<silent>]
	 * : Whether to silent the reply creation.
	 * ---
	 * default: false
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp bbp reply create --title="Reply 01" --content="Content for reply" --user-id=39
	 *     $ wp bbp reply create --title="Reply" --user-id=45 --topic-id=120 --forum-id=2497
	 */
	public function create( $args, $assoc_args ) {
		$r = wp_parse_args( $assoc_args, array(
			'title'    => '',
			'content'  => '',
			'user-id'  => 1,
			'topic-id' => 0,
			'forum-id' => 0,
			'silent'   => false,
		) );

		if ( empty( $r['content'] ) ) {
			$r['content'] = sprintf( 'Content for the reply "%s"', $r['title'] );
		}

		$reply_data = array(
			'post_parent'  => $r['topic-id'],
			'post_title'   => $r['title'],
			'post_content' => $r['content'],
			'post_author'  => $r['user-id'],
		);

		$reply_meta = array(
			'forum_id'  => $r['forum-id'],
			'topic_id'  => $r['topic-id'],
		);

		$id = bbp_insert_reply( $reply_data, $reply_meta );

		if ( $r['silent'] ) {
			return;
		}

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Reply %d created: %s', $id, bbp_get_reply_permalink( $id ) ) );
		} else {
			WP_CLI::error( 'Could not create reply.' );
		}
	}

	/**
	 * Get a reply.
	 *
	 * ## OPTIONS
	 *
	 * <reply-id>
	 * : Identifier for the reply to get.
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
	 *     $ wp bbp reply get 456
	 *     $ wp bbp reply get 151 --fields=post_title
	 */
	public function get( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		$reply = bbp_get_reply( $reply_id, ARRAY_A );

		if ( empty( $assoc_args['fields'] ) ) {
			$assoc_args['fields'] = array_keys( $reply );
		}

		$formatter = $this->get_formatter( $assoc_args );
		$formatter->display_items( $reply );
	}

	/**
	 * Delete a reply.
	 *
	 * ## OPTIONS
	 *
	 * <reply-id>...
	 * : One or more IDs of replies to delete.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp reply delete 486
	 *     Success: Reply 486 deleted.
	 */
	public function delete( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		parent::_delete( $args, $assoc_args, function ( $reply_id, $assoc_args ) {

			wp_delete_post( $reply_id, true );

			$r = bbp_deleted_reply( $reply_id );

			if ( ! $r ) {
				return array( 'success', sprintf( 'Reply %d deleted.', $reply_id ) );
			} else {
				return array( 'error', sprintf( 'Could not delete %d reply.', $reply_id ) );
			}
		} );
	}

	/**
	 * Trash a reply.
	 *
	 * ## OPTIONS
	 *
	 * <reply-id>...
	 * : One or more IDs of replies to trash.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp reply trash 789
	 *     Success: Reply 789 trashed.
	 */
	public function trash( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		wp_trash_post( $reply_id );

		if ( ! bbp_trashed_reply( $reply_id ) ) {
			WP_CLI::success( sprintf( 'Reply %d trashed.', $reply_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not trash reply %d.', $reply_id ) );
		}
	}

	/**
	 * Untrash a reply.
	 *
	 * ## OPTIONS
	 *
	 * <reply-id>...
	 * : One or more IDs of replies to untrash.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp reply untrash 3938
	 *     Success: Reply 3938 untrashed.
	 */
	public function untrash( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		wp_untrash_post( $reply_id );

		if ( ! bbp_untrashed_reply( $reply_id ) ) {
			WP_CLI::success( sprintf( 'Reply %d untrashed.', $reply_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not untrash reply %d.', $reply_id ) );
		}
	}

	/**
	 * Get a list of replies.
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
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each reply:
	 *
	 * * ID
	 * * post_title
	 * * post_name
	 * * post_date
	 * * post_status
	 *
	 * ## EXAMPLES
	 *
	 *     # List ids of all replies
	 *     $ wp bbp reply list --format=ids
	 *     15 25 34 37 198
	 *
	 *     # List total count of replies
	 *     $ wp bbp reply list --format=count
	 *     451
	 *
	 * @subcommand list
	 */
	public function _list( $_, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		$query_args = wp_parse_args( $assoc_args, array(
			'post_type'      => bbp_get_reply_post_type(),
			'post_status'    => bbp_get_public_status_id(),
			'posts_per_page' => -1,
		) );

		$query_args = self::process_csv_arguments_to_arrays( $query_args );

		$reply_post_type = bbp_get_reply_post_type();
		if ( isset( $query_args['post_type'] ) && $reply_post_type !== $query_args['post_type'] ) {
			$query_args['post_type'] = $reply_post_type;
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
	 * Generate random replies.
	 *
	 * ## OPTIONS
	 *
	 * [--count=<number>]
	 * : How many replies to generate.
	 * ---
	 * default: 100
	 * ---
	 *
	 * [--topic-id=<topic-id>]
	 * : Identifier of the topic the replied is for.
	 * ---
	 * default: 0
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp bbp reply generate --count=50
	 *     $ wp bbp reply generate --count=50 --topic-id=342
	 */
	public function generate( $args, $assoc_args ) {
		$r = wp_parse_args( $assoc_args, array(
			'topic-id' => 0,
			'count'    => 100,
		) );

		$notify = \WP_CLI\Utils\make_progress_bar( 'Generating replies', $r['count'] );

		for ( $i = 0; $i < $r['count']; $i++ ) {
			$this->create( array(), array(
				'title'    => sprintf( 'Reply Title "%s"', $i ),
				'topic-id' => $r['topic-id'],
				'silent'   => true,
			) );

			$notify->tick();
		}

		$notify->finish();
	}

	/**
	 * Spam a reply.
	 *
	 * ## OPTIONS
	 *
	 * <reply-id>
	 * : Identifier of the reply to spam.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp reply spam 3938
	 *     Success: Reply 3938 spammed.
	 */
	public function spam( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		$id = bbp_spam_reply( $reply_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Reply %d spammed.', $reply_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not spam reply %d.', $reply_id ) );
		}
	}

	/**
	 * Ham a reply.
	 *
	 * ## OPTIONS
	 *
	 * <reply-id>
	 * : Identifier of the reply to ham.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp reply ham 3938
	 *     Success: Reply 3938 hammed.
	 */
	public function ham( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		$id = bbp_unspam_reply( $reply_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Reply %d hammed.', $reply_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not ham reply %d.', $reply_id ) );
		}
	}

	/**
	 * Approve a reply.
	 *
	 * ## OPTIONS
	 *
	 * <reply-id>
	 * : Identifier of the reply to approve.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp reply approve 3938
	 *     Success: Reply 3938 approved.
	 */
	public function approve( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		$id = bbp_approve_reply( $reply_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Reply %d approved.', $reply_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not approve reply %d.', $reply_id ) );
		}
	}

	/**
	 * Unapprove a reply.
	 *
	 * ## OPTIONS
	 *
	 * <reply-id>
	 * : Identifier of the reply to unapprove.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp reply unapprove 3938
	 *     Success: Reply 3938 unapproved.
	 */
	public function unapprove( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		$id = bbp_unapprove_reply( $reply_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Reply %d unapprove.', $reply_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not unapprove reply %d.', $reply_id ) );
		}
	}

	/**
	 * Get the permalink of a reply.
	 *
	 * ## OPTIONS
	 *
	 * <reply-id>
	 * : Identifier for the reply.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp bbp reply permalink 165
	 *     Success: Reply Permalink: http://site.com/forums/reply/reply-slug/
	 *
	 *     $ wp bbp reply ul 398
	 *     Success: Reply Permalink: http://site.com/forums/reply/another-reply-slug/
	 *
	 * @alias url
	 */
	public function permalink( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		$permalink = bbp_get_reply_permalink( $reply_id );

		if ( is_string( $permalink ) ) {
			WP_CLI::success( sprintf( 'Reply Permalink: %s', $permalink ) );
		} else {
			WP_CLI::error( 'No permalink found for the reply.' );
		}
	}
}

WP_CLI::add_command( 'bbp reply', 'BBPCLI_Replies', array(
	'before_invoke' => function() {
		if ( ! class_exists( 'bbPress' ) ) {
			WP_CLI::error( 'bbPress is not installed or active.' );
		}
	},
) );
