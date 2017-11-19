<?php
/**
 * Manage bbPress Favorites.
 *
 * @since 1.0.0
 */
class BBPCLI_Favorite extends BBPCLI_Component {

	/**
	 * Add a topic to user's favorites.
	 *
	 * ## OPTIONS
	 *
	 * --user-id=<user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * --topic-id=<topic-id>
	 * : Identifier for the topic.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp favorite add --user-id=5465 --topic-id=65476
	 *    Success: Favorite successfully added.
	 *
	 *    $ wp bbp favorite add --user-id=user_test --topic-id=354354
	 *    Success: Favorite successfully added.
	 *
	 * @alias create
	 */
	public function add( $args, $assoc_args ) {
		// Check if user exists.
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );
		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or ID.' );
		}

		// Check if topic exists.
		$topic_id = $assoc_args['topic-id'];
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		// True if added.
		if ( bbp_add_user_favorite( $user->ID, $topic_id ) ) {
			WP_CLI::success( 'Favorite successfully added.' );
		} else {
			WP_CLI::error( 'Could not add favorite.' );
		}
	}

	/**
	 * Remove a topic from user's favorites.
	 *
	 * ## OPTIONS
	 *
	 * --user-id=<user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * --topic-id=<topic-id>
	 * : Identifier for the topic.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp favorite remove --user-id=5465 --topic-id=65476
	 *    Success: Favorite successfully removed.
	 *
	 *    $ wp bbp favorite remove --user-id=user_test --topic-id=28468
	 *    Success: Favorite successfully removed.
	 *
	 * @alias delete
	 */
	public function remove( $args, $assoc_args ) {
		// Check if user exists.
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );
		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or ID.' );
		}

		// Check if topic exists.
		$topic_id = $assoc_args['topic-id'];
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		// True if removed.
		if ( bbp_remove_user_favorite( $user->ID, $topic_id ) ) {
			WP_CLI::success( 'Favorite successfully removed.' );
		} else {
			WP_CLI::error( 'Could not remove favorite.' );
		}
	}

	/**
	 * List users who favorited a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier of the topic.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: count
	 * options:
	 *   - ids
	 *   - count
	 *   - json
	 *   - haml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp bbp favorite list_users 456 --format=ids
	 *     54564 4564 454 545
	 *
	 *     $ wp bbp favorite list_users 354 --format=count
	 *     2
	 */
	public function list_users( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		$ids = bbp_get_topic_favoriters( $topic_id );

		if ( ! $ids ) {
			WP_CLI::error( 'Could not find any users.' );
		}

		if ( 'ids' === $formatter->format ) {
			echo implode( ' ', $ids ); // WPCS: XSS ok.
		} elseif ( 'count' === $formatter->format ) {
			$formatter->display_items( $ids );
		}
	}

	/**
	 * List topics a user favorited.
	 *
	 * ## OPTIONS
	 *
	 * <user-id>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
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
	 *   - haml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp bbp favorite list_topics 5456
	 *     $ wp bbp favorite list_topics 54464 --format=count
	 *
	 * @subcommand list
	 */
	public function _list( $args, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		// Check if user exists.
		$user = $this->get_user_id_from_identifier( $args[0] );
		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or ID.' );
		}

		$topics = bbp_get_user_favorites( array(
			'meta_query' => array( // WPCS: slow query ok.
				array(
					'key'     => '_bbp_favorite',
					'value'   => $user->ID,
					'compare' => 'NUMERIC',
				),
			),
		) );

		if ( 'ids' === $formatter->format ) {
			echo implode( ' ', bbp_get_user_favorites_topic_ids( $user->ID ) ); // WPCS: XSS ok.
		} elseif ( 'count' === $formatter->format ) {
			$formatter->display_items( $topics->posts );
		} else {
			$formatter->display_items( $topics->posts );
		}
	}
}

WP_CLI::add_command( 'bbp favorite', 'BBPCLI_Favorite' );
