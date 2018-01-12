<?php
/**
 * Manage bbPress Topics.
 *
 * @since 1.0.0
 */
class BBPCLI_Topic extends BBPCLI_Component {

	/**
	 * Object fields
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
	 * Create a topic.
	 *
	 * ## OPTIONS
	 *
	 * --title=<title>
	 * : Topic title.
	 *
	 * [--content=<content>]
	 * : Topic content.
	 * ---
	 * default: 'Content for topic: "[title]"'
	 * ---
	 *
	 * [--user-id=<user-id>]
	 * : Identifier of the user.
	 * ---
	 * default: 1
	 * ---
	 *
	 * [--forum-id=<forum-id>]
	 * : Identifier of the Forum the topic is for.
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--status=<status>]
	 * : Status of the topic (publish, closed, spam, trash, pending).
	 * ---
	 * Default: publish
	 * ---
	 *
	 * [--silent=<silent>]
	 * : Whether to silent the topic creation.
	 * ---
	 * default: false
	 * ---
	 *
	 * [--porcelain]
	 * : Output only the new topic id.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp bbp topic create --title="Topic 01" --content="Content for topic" --user-id=39
	 *     $ wp bbp topic create --title="Topic" --user-id=45 --forum-id=2497
	 *
	 * @alias add
	 */
	public function create( $args, $assoc_args ) {
		$r = wp_parse_args( $assoc_args, array(
			'title'    => '',
			'content'  => '',
			'user-id'  => 1,
			'forum-id' => 0,
			'status'   => 'publish',
			'silent'   => false,
		) );

		if ( empty( $r['content'] ) ) {
			$r['content'] = sprintf( 'Content for the topic: "%s"', $r['title'] );
		}

		// Fallback for topic status.
		if ( ! in_array( $r['status'], $this->topic_status(), true ) ) {
			$r['status'] = 'publish';
		}

		$topic_data = array(
			'post_parent'  => $r['forum-id'],
			'post_title'   => $r['title'],
			'post_content' => $r['content'],
			'post_author'  => $r['user-id'],
			'post_status'  => $r['status'],
		);

		$topic_meta = array(
			'forum_id' => $r['forum-id'],
		);

		$id = bbp_insert_topic( $topic_data, $topic_meta );

		if ( ! is_numeric( $id ) ) {
			WP_CLI::error( 'Could not create topic.' );
		}

		if ( $r['silent'] ) {
			return;
		}

		if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'porcelain' ) ) {
			WP_CLI::line( $id );
		} else {
			WP_CLI::success( sprintf( 'Topic %d created: %s', $id, bbp_get_topic_permalink( $id ) ) );
		}
	}

	/**
	 * Get a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier for the topic.
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
	 *     $ wp bbp topic get 456
	 *     $ wp bbp topic get 151 --fields=post_title
	 *
	 * @alias see
	 */
	public function get( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		$topic = bbp_get_topic( $topic_id, ARRAY_A );
		$topic['url'] = bbp_get_topic_permalink( $topic_id );

		if ( empty( $assoc_args['fields'] ) ) {
			$assoc_args['fields'] = array_keys( $topic );
		}

		$formatter = $this->get_formatter( $assoc_args );
		$formatter->display_item( $topic );
	}

	/**
	 * Delete a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>...
	 * : One or more IDs of topics to delete.
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp topic delete 486
	 *     Success: Topic 486 successfully deleted.
	 *
	 * @alias remove
	 */
	public function delete( $args, $assoc_args ) {
		$topic_id = $args[0];

		WP_CLI::confirm( 'Are you sure you want to delete this topic?', $assoc_args );

		parent::_delete( array( $topic_id ), $assoc_args, function ( $topic_id ) {
			// Check if topic exists.
			if ( ! bbp_is_topic( $topic_id ) ) {
				WP_CLI::error( 'No topic found by that ID.' );
			}

			wp_delete_post( $topic_id, true );

			if ( ! bbp_deleted_topic( $topic_id ) ) {
				return array( 'success', sprintf( 'Topic %d successfully deleted.', $topic_id ) );
			} else {
				return array( 'error', sprintf( 'Could not delete %d topic.', $topic_id ) );
			}
		} );
	}

	/**
	 * Trash a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier of the topic to trash.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp topic trash 789
	 *     Success: Topic 789 successfully trashed.
	 */
	public function trash( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		wp_trash_post( $topic_id );

		if ( ! bbp_trashed_topic( $topic_id ) ) {
			WP_CLI::success( sprintf( 'Topic %d successfully trashed.', $topic_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not trash topic %d.', $topic_id ) );
		}
	}

	/**
	 * Untrash a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier of the topic to untrash.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp topic untrash 3938
	 *     Success: Topic 3938 successfully untrashed.
	 */
	public function untrash( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		wp_untrash_post( $topic_id );

		if ( ! bbp_untrashed_topic( $topic_id ) ) {
			WP_CLI::success( sprintf( 'Topic %d successfully untrashed.', $topic_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not untrash topic %d.', $topic_id ) );
		}
	}

	/**
	 * Get a list of topics.
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
	 *     # List ids of all topics
	 *     $ wp bbp topic list --format=ids
	 *     15 25 34 37 198
	 *
	 *     # List total count of topics
	 *     $ wp bbp topic list --format=count
	 *     451
	 *
	 * @subcommand list
	 */
	public function _list( $_, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		$topic_post_type = bbp_get_topic_post_type();
		$query_args = wp_parse_args( $assoc_args, array(
			'post_type' => $topic_post_type,
		) );

		if ( isset( $query_args['post_type'] ) && $topic_post_type !== $query_args['post_type'] ) {
			$query_args['post_type'] = $topic_post_type;
		}

		$query_args = self::process_csv_arguments_to_arrays( $query_args );

		if ( 'ids' === $formatter->format ) {
			$query_args['fields'] = 'ids';
			$query = new WP_Query( $query_args );
			echo implode( ' ', $query->posts ); // WPCS: XSS ok.
		} elseif ( 'count' === $formatter->format ) {
			$query_args['fields'] = 'ids';
			$query = new WP_Query( $query_args );
			$formatter->display_items( $query->found_posts );
		} else {
			$query = new WP_Query( $query_args );
			$topics = array_map( function( $post ) {
				$post->url = get_permalink( $post->ID );
				return $post;
			}, $query->posts );
			$formatter->display_items( $topics );
		}
	}

	/**
	 * Open a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier of the topic to open.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp topic open 456
	 *     Success: Topic 456 successfully opened.
	 */
	public function open( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		// Check if topic is already open.
		if ( bbp_is_topic_open( $topic_id ) ) {
			WP_CLI::error( 'Topic is already opened.' );
		}

		$id = bbp_open_topic( $topic_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Topic %d successfully opened.', $topic_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not open topic %d.', $topic_id ) );
		}
	}

	/**
	 * Close a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier of the topic to close.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp topic close 456
	 *     Success: Topic 456 successfully closed.
	 */
	public function close( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		// Check if topic is already closed.
		if ( bbp_is_topic_closed( $topic_id ) ) {
			WP_CLI::error( 'Topic is already closed.' );
		}

		$id = bbp_close_topic( $topic_id );

		if ( is_numeric( $id ) ) {
			WP_CLI::success( sprintf( 'Topic %d successfully closed.', $topic_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not close topic %d.', $topic_id ) );
		}
	}

	/**
	 * Generate random topics (topics only).
	 *
	 * ## OPTIONS
	 *
	 * [--count=<number>]
	 * : How many topics to generate.
	 * ---
	 * default: 100
	 * ---
	 *
	 * [--forum-id=<forum-id>]
	 * : Identifier of the forum the topic is for.
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--status=<status>]
	 * : Topic Status (publish, closed, spam, trash, pending or mixed).
	 * ---
	 * Default: publish
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp bbp topic generate --count=50
	 *     $ wp bbp topic generate --count=50 --forum-id=342
	 *     $ wp bbp topic generate --count=10 --forum-id=4543 --status=mixed
	 */
	public function generate( $args, $assoc_args ) {
		$notify = \WP_CLI\Utils\make_progress_bar( 'Generating topics', $assoc_args['count'] );

		for ( $i = 0; $i < $assoc_args['count']; $i++ ) {
			$this->create( array(), array(
				'title'    => sprintf( 'Topic Title "%s"', $i ),
				'forum-id' => $assoc_args['forum-id'],
				'status'   => $this->random_topic_status( $assoc_args['status'] ),
				'silent'   => true,
			) );

			$notify->tick();
		}

		$notify->finish();
	}

	/**
	 * Spam a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier of the topic to spam.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp topic spam 3938
	 *     Success: Topic 3938 successfully spammed.
	 *
	 * @alias unham
	 */
	public function spam( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		bbp_spam_topic_replies( $topic_id );
		bbp_spam_topic_tags( $topic_id );

		if ( is_numeric( bbp_spam_topic( $topic_id ) ) ) {
			WP_CLI::success( sprintf( 'Topic %d successfully spammed.', $topic_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not spam topic %d.', $topic_id ) );
		}
	}

	/**
	 * Ham a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier of the topic to ham.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp topic ham 3938
	 *     Success: Topic 3938 successfully hammed.
	 *
	 * @alias unspam
	 */
	public function ham( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		bbp_unspam_topic_replies( $topic_id );
		bbp_unspam_topic_tags( $topic_id );

		if ( is_numeric( bbp_unspam_topic( $topic_id ) ) ) {
			WP_CLI::success( sprintf( 'Topic %d successfully hammed.', $topic_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not ham topic %d.', $topic_id ) );
		}
	}

	/**
	 * Stick a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier of the topic to stick.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp topic stick 465
	 *     Success: Topic 465 successfully sticked.
	 */
	public function stick( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		if ( bbp_stick_topic( $topic_id, true ) ) {
			WP_CLI::success( sprintf( 'Topic %d successfully sticked.', $topic_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not stick topic %d.', $topic_id ) );
		}
	}

	/**
	 * Unstick a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier of the topic to unstick.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp topic unstick 465
	 *     Success: Topic 465 successfully unsticked.
	 */
	public function unstick( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		// It always returns true.
		if ( bbp_unstick_topic( $topic_id ) ) {
			WP_CLI::success( sprintf( 'Topic %d successfully unsticked.', $topic_id ) );
		}
	}

	/**
	 * Approve a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier of the topic to approve.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp topic approve 3938
	 *     Success: Topic 3938 successfully approved.
	 */
	public function approve( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		if ( is_numeric( bbp_approve_topic( $topic_id ) ) ) {
			WP_CLI::success( sprintf( 'Topic %d successfully approved.', $topic_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not approve topic %d.', $topic_id ) );
		}
	}

	/**
	 * Unapprove a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier of the topic to unapprove.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp topic unapprove 3938
	 *     Success: Topic 3938 successfully unapproved.
	 */
	public function unapprove( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		if ( is_numeric( bbp_unapprove_topic( $topic_id ) ) ) {
			WP_CLI::success( sprintf( 'Topic %d successfully unapproved.', $topic_id ) );
		} else {
			WP_CLI::error( sprintf( 'Could not unapprove topic %d.', $topic_id ) );
		}
	}

	/**
	 * List of topic status.
	 *
	 * @since 1.0.0
	 *
	 * @return array An array of default topic status.
	 */
	protected function topic_status() {
		return array_keys( bbp_get_topic_statuses() );
	}

	/**
	 * Get a randon topic status.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $status Topic status.
	 * @return string Random Topic Status.
	 */
	protected function random_topic_status( $status ) {
		$reply_status = $this->topic_status();

		$status = ( 'mixed' === $status )
			? $reply_status[ array_rand( $reply_status ) ]
			: $status;

		return $status;
	}
}

WP_CLI::add_command( 'bbp topic', 'BBPCLI_Topic' );
