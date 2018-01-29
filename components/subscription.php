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
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );

		// True if added.
		if ( bbp_add_user_subscription( $user->ID, $assoc_args['object-id'] ) ) {
			WP_CLI::success( 'Subscription successfully added.' );
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
	 * [--yes]
	 * : Answer yes to the confirmation message.
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
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );

		WP_CLI::confirm( 'Are you sure you want to remove this subscription?', $assoc_args );

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
	 * <object-id>
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
	 *     $ wp bbp subscription list_users 334938
	 *     3
	 *
	 *     $ wp bbp subscription list_users 242 --format=ids
	 *     65 5454 5454 545
	 */
	public function list_users( $args, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		$ids = bbp_get_subscribers( $args[0] );

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
	 * List forums or topics a user is subscribed to.
	 *
	 * ## OPTIONS
	 *
	 * --user-id=<user>
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

		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );

		$objects = ( 'forum' === $assoc_args['object'] )
			? bbp_get_user_forum_subscriptions( $this->user_args( $user->ID ) )
			: bbp_get_user_topic_subscriptions( $this->user_args( $user->ID ) );

		if ( empty( $objects ) ) {
			WP_CLI::error( 'There is no posts.' );
		}

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
