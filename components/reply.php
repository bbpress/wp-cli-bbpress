<?php
/**
 * Manage bbPress Replies.
 *
 * @since 1.0.0
 */
class BBPCLI_Reply extends BBPCLI_Component {

	/**
	 * Reply Object Default fields
	 *
	 * @var array
	 */
	protected $obj_fields = array(
		'ID',
		'post_title',
		'post_name',
		'post_date',
		'post_status',
	);

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
	 * [--status=<status>]
	 * : Status of the reply (publish, pending, spam, trash).
	 * ---
	 * default: publish
	 * ---
	 *
	 * [--silent=<silent>]
	 * : Whether to silent the reply creation.
	 * ---
	 * default: false
	 * ---
	 *
	 * [--porcelain]
	 * : Output only the new reply id.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp bbp reply create --title="Reply 01" --content="Content for reply" --user-id=39
	 *     $ wp bbp reply create --title="Reply" --user-id=45 --topic-id=120 --forum-id=2497
	 *     $ wp bbp reply create --title="Reply" --user-id=545 --topic-id=12 --forum-id=24 --status=pending
	 *
	 * @alias add
	 */
	public function create( $args, $assoc_args ) {
		$r = wp_parse_args( $assoc_args, array(
			'title'    => '',
			'content'  => '',
			'user-id'  => 1,
			'topic-id' => 0,
			'forum-id' => 0,
			'status'   => 'publish',
			'silent'   => false,
		) );

		if ( empty( $r['content'] ) ) {
			$r['content'] = sprintf( 'Content for the reply "%s"', $r['title'] );
		}

		// Fallback for reply status.
		if ( ! in_array( $r['status'], $this->reply_status(), true ) ) {
			$r['status'] = 'publish';
		}

		$reply_data = array(
			'post_parent'  => $r['topic-id'],
			'post_status'  => $r['status'],
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

		if ( ! is_numeric( $id ) ) {
			WP_CLI::error( 'Could not create reply.' );
		}

		if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'porcelain' ) ) {
			WP_CLI::line( $id );
		} else {
			WP_CLI::success( sprintf( 'Reply %d created: %s', $id, bbp_get_reply_permalink( $id ) ) );
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
	 *   - json
	 *   - csv
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp bbp reply get 456
	 *     $ wp bbp reply get 151 --fields=post_title
	 *
	 * @alias see
	 */
	public function get( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		$reply = bbp_get_reply( $reply_id, ARRAY_A );
		$reply['url'] = bbp_get_reply_permalink( $reply_id );

		if ( empty( $assoc_args['fields'] ) ) {
			$assoc_args['fields'] = array_keys( $reply );
		}

		$formatter = $this->get_formatter( $assoc_args );
		$formatter->display_item( $reply );
	}

	/**
	 * Delete a reply.
	 *
	 * ## OPTIONS
	 *
	 * <reply-id>...
	 * : One or more IDs of replies to delete.
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp reply delete 486 --yes
	 *     Success: Reply 486 successfully deleted.
	 */
	public function delete( $args, $assoc_args ) {
		$reply_id = $args[0];

		WP_CLI::confirm( 'Are you sure you want to delete this reply?', $assoc_args );

		parent::_delete( array( $reply_id ), $assoc_args, function ( $reply_id ) {
			// Check if reply exists.
			if ( ! bbp_is_reply( $reply_id ) ) {
				WP_CLI::error( 'No reply found by that ID.' );
			}

			wp_delete_post( $reply_id, true );

			if ( ! bbp_deleted_reply( $reply_id ) ) {
				return array( 'success', sprintf( 'Reply %d successfully deleted.', $reply_id ) );
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
	 * <reply-id>
	 * : Identifier for the reply to trash.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp reply trash 789
	 *     Success: Reply 789 successfully trashed.
	 */
	public function trash( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		wp_trash_post( $reply_id );

		if ( ! bbp_trashed_reply( $reply_id ) ) {
			WP_CLI::success( sprintf( 'Reply %d successfully trashed.', $reply_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not trash reply %d.', $reply_id ) );
		}
	}

	/**
	 * Untrash a reply.
	 *
	 * ## OPTIONS
	 *
	 * <reply-id>
	 * : Identifier for the reply to untrash.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp reply untrash 3938
	 *     Success: Reply 3938 successfully untrashed.
	 */
	public function untrash( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		wp_untrash_post( $reply_id );

		if ( ! bbp_untrashed_reply( $reply_id ) ) {
			WP_CLI::success( sprintf( 'Reply %d successfully untrashed.', $reply_id ) );
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

		$reply_post_type = bbp_get_reply_post_type();
		$query_args = wp_parse_args( $assoc_args, array(
			'post_type'   => $reply_post_type,
			'post_status' => 'any',
		) );

		if ( isset( $query_args['post_type'] ) && $reply_post_type !== $query_args['post_type'] ) {
			$query_args['post_type'] = $reply_post_type;
		}

		$query_args = self::process_csv_arguments_to_arrays( $query_args );

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
			$replies = array_map( function( $post ) {
				$post->url = get_permalink( $post->ID );
				return $post;
			}, $query->posts );
			$formatter->display_items( $replies );
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
	 * [--status=<status>]
	 * : The status of the generated replies. (publish, spam, pending, trash or mixed).
	 * ---
	 * default: publish
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
	 *     $ wp bbp reply generate --count=112 --topic-id=342
	 *     $ wp bbp reply generate --count=10 --status=mixed --topic-id=4584
	 */
	public function generate( $args, $assoc_args ) {
		$notify = \WP_CLI\Utils\make_progress_bar( 'Generating replies', $assoc_args['count'] );

		for ( $i = 0; $i < $assoc_args['count']; $i++ ) {
			$this->create( array(), array(
				'title'    => sprintf( 'Reply Title "%s"', $i ),
				'status'   => $this->random_reply_status( $assoc_args['status'] ),
				'topic-id' => $assoc_args['topic-id'],
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
	 *     Success: Reply 3938 successfully spammed.
	 */
	public function spam( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		$id = bbp_spam_reply( $reply_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Reply %d successfully spammed.', $reply_id ) );
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
	 *     Success: Reply 3938 successfully hammed.
	 */
	public function ham( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		$id = bbp_unspam_reply( $reply_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Reply %d successfully hammed.', $reply_id ) );
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
	 *     Success: Reply 3938 successfully approved.
	 */
	public function approve( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		$id = bbp_approve_reply( $reply_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Reply %d successfully approved.', $reply_id ) );
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
	 *     Success: Reply 3938 successfully unapproved.
	 */
	public function unapprove( $args, $assoc_args ) {
		$reply_id = $args[0];

		// Check if reply exists.
		if ( ! bbp_is_reply( $reply_id ) ) {
			WP_CLI::error( 'No reply found by that ID.' );
		}

		$id = bbp_unapprove_reply( $reply_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Reply %d successfully unapproved.', $reply_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not unapprove reply %d.', $reply_id ) );
		}
	}

	/**
	 * List of reply statuses.
	 *
	 * @since 1.0.0
	 *
	 * @return array An array of default reply status.
	 */
	protected function reply_status() {
		return array_keys( bbp_get_reply_statuses() );
	}

	/**
	 * Gets a randon reply status.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $status Reply status.
	 * @return string Random Reply Status.
	 */
	protected function random_reply_status( $status ) {
		$reply_status = $this->reply_status();

		$status = ( 'mixed' === $status )
			? $reply_status[ array_rand( $reply_status ) ]
			: $status;

		return $status;
	}
}

WP_CLI::add_command( 'bbp reply', 'BBPCLI_Reply' );
