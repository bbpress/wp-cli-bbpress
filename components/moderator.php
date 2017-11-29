<?php
/**
 * Manage bbPress Moderators.
 *
 * @since 1.0.0
 */
class BBPCLI_Moderator extends BBPCLI_Component {

	/**
	 * Add a forum moderator
	 *
	 * ## OPTIONS
	 *
	 * --forum-id=<forum-id>
	 * : Indentifier of the forum.
	 *
	 * --user-id=<user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp user moderator add --forum-id=545646 --user-id=465456
	 *    Success: Member added as a moderator.
	 *
	 *    $ wp bbp user moderator add --forum-id=465465 --user-id=user_login
	 *    Success: Member added as a moderator.
	 *
	 * @alias create
	 */
	public function add( $args, $assoc_args ) {
		$forum_id = $assoc_args['forum-id'];

		// Check if forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );

		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or id' );
			return;
		}

		if ( bbp_add_moderator( $forum_id, $user->ID ) ) {
			WP_CLI::success( 'Member added as a moderator.' );
		} else {
			WP_CLI::error( 'Could not add the moderator.' );
		}
	}

	/**
	 * Remove a forum moderator
	 *
	 * ## OPTIONS
	 *
	 * --forum-id=<forum-id>
	 * : Indentifier of the forum.
	 *
	 * --user-id=<user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp user moderator remove --forum-id=456456 --user-id=4995
	 *    Success: Member removed as a moderator.
	 *
	 *    $ wp bbp user moderator remove --forum-id=64654 --user-id=user_login
	 *    Success: Member removed as a moderator.
	 *
	 * @alias delete
	 */
	public function remove( $args, $assoc_args ) {
		$forum_id = $assoc_args['forum-id'];

		// Check if forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );

		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or ID.' );
			return;
		}

		if ( bbp_remove_moderator( $forum_id, $user->ID ) ) {
			WP_CLI::success( 'Member removed as a moderator.' );
		} else {
			WP_CLI::error( 'Could not add the user as a moderator.' );
		}
	}

	/**
	 * List forum moderators
	 *
	 * ## OPTIONS
	 *
	 * --forum-id=<forum-id>
	 * : Indentifier of the forum.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 *  ---
	 * default: table
	 * options:
	 *   - table
	 *   - ids
	 *   - json
	 *   - count
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp user moderator list --forum-id=456456 --format=count
	 *    6
	 *
	 *    $ wp bbp user moderator list --forum-id=45456 --format=ids
	 *    5421 454
	 *
	 * @subcommand list
	 */
	public function _list( $_, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		$forum_id = $assoc_args['forum-id'];

		// Check if forum exists.
		if ( ! bbp_is_forum( $forum_id ) ) {
			WP_CLI::error( 'No forum found by that ID.' );
		}

		$moderators = bbp_get_moderators( $forum_id );

		if ( 'ids' === $formatter->format ) {
			echo implode( ' ', bbp_get_moderator_ids( $forum_id ) ); // WPCS: XSS ok.
		} elseif ( 'count' === $formatter->format ) {
			$formatter->display_items( $moderators );
		} else {
			$formatter->display_items( $moderators );
		}
	}
}

WP_CLI::add_command( 'bbp moderator', 'BBPCLI_Moderator' );
