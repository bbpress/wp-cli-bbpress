<?php
/**
 * Manage bbPress Subscriptions.
 *
 * @since 1.0.0
 */
class BBPCLI_Subscription extends BBPCLI_Component {

	/**
	 * Add a user subscription.
	 *
	 * ## OPTIONS
	 *
	 * --user-id=<user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * --object-id=<object-id>
	 * : Identifier for the object (forum, topic, or something else).
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp subscription add --user-id=5465 --object-id=65476
	 *    Success: Subscription successfully added.
	 *
	 *    $ wp bbp subscription subscribe --user-id=user_test --object-id=22211 --type=topic
	 *    Success: Subscription successfully added.
	 *
	 * @alias subscribe
	 */
	public function add( $args, $assoc_args ) {
		// Check if user exists.
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );
		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or ID.' );
		}

		// True if added.
		if ( bbp_add_user_subscription( $user->ID, $assoc_args['object-id'] ) ) {
			WP_CLI::success( 'Subscription successfully  added.' );
		} else {
			WP_CLI::error( 'Could not add the subscription.' );
		}
	}

	/**
	 * Remove a user subscription.
	 *
	 * ## OPTIONS
	 *
	 * --user-id=<user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * --object-id=<object-id>
	 * : Identifier for the object (forum, topic, or something else).
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp subscription remove --user-id=5465 --object-id=65476
	 *    Success: Subscription successfully removed.
	 *
	 *    $ wp bbp subscription unsubscribe --user-id=user_login --object-id=4646
	 *    Success: Subscription successfully removed.
	 *
	 * @alias unsubscribe
	 */
	public function remove( $args, $assoc_args ) {
		// Check if user exists.
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );
		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or ID.' );
		}

		// True if added.
		if ( bbp_remove_user_subscription( $user->ID, $assoc_args['object-id'] ) ) {
			WP_CLI::success( 'Subscription successfully removed.' );
		} else {
			WP_CLI::error( 'Could not remove the subscription.' );
		}
	}

	/**
	 * List users who subscribed to an object.
	 *
	 * ## OPTIONS
	 *
	 * --object-id=<object-id>
	 * : Identifier for the object (forum or topic).
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
	 *     $ wp bbp subscription list_users --object-id=334938
	 *     3
	 *
	 *     $ wp bbp subscription list_users --object-id=242 --format=ids
	 *     65 5454 5454 545
	 */
	public function list_users( $args, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		$ids = bbp_get_subscribers( $assoc_args['object-id'] );

		if ( ! $ids ) {
			if ( 'ids' === $formatter->format ) {
				echo implode( ' ', $ids ); // WPCS: XSS ok.
			} elseif ( 'count' === $formatter->format ) {
				$formatter->display_items( count( $ids ) );
			}
		} else {
			WP_CLI::error( 'Could not find any users.' );
		}
	}

	/**
	 * List forums or topics a user is subscribed to.
	 *
	 * ## OPTIONS
	 *
	 * --user-id=<user-id>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * --object=<object>
	 * : Type of object (forum or topic).
	 * ---
	 * default: forum
	 * ---
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
	 *     $ wp bbp subscription list --user-id=323 --object=forum
	 *     $ wp bbp subscription list --user-id=864 --object=topic --format=count
	 *
	 * @subcommand list
	 */
	public function _list( $args, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		// Check if user exists.
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );
		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or ID.' );
		}

		$query_args = array(
			'meta_query' => array( // WPCS: slow query ok.
				array(
					'key'     => '_bbp_subscription',
					'value'   => $user->ID,
					'compare' => 'NUMERIC',
				),
			),
		);

		$objects = ( 'forum' === $assoc_args['object'] )
			? bbp_get_user_forum_subscriptions( $query_args )
			: bbp_get_user_topic_subscriptions( $query_args );

		if ( 'ids' === $formatter->format ) {
			echo implode( ' ', wp_list_pluck( $objects->posts, 'ID' ) ); // WPCS: XSS ok.
		} elseif ( 'count' === $formatter->format ) {
			$formatter->display_items( $objects->posts );
		} else {
			$formatter->display_items( $objects->posts );
		}
	}
}

WP_CLI::add_command( 'bbp subscription', 'BBPCLI_Subscription' );
