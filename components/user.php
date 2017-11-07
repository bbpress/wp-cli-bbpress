<?php
/**
 * Manage bbPress Users.
 *
 * @since 1.0.0
 */
class BBPCLI_Users extends BBPCLI_Component {

	public function topics( $args, $assoc_args ) {
		$defaults = array(
			'author' => bbp_get_displayed_user_id()
		);

		// bbp_get_user_topics_started()
	}

	public function replies( $args, $assoc_args ) {
		$defaults = array(
			'author' => bbp_get_displayed_user_id(),
		);

		// bbp_get_user_replies_created()
	}

	/**
	 * Mark a user's topics and replies as spam
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp user moderator spam --user-id=465456
	 *    Success: User topics and replies marked as spam.
	 *
	 *    $ wp bbp user moderator spam --user-id=user_login
	 *    Success: User topics and replies marked as spam.
	 *
	 * @alias unham
	 */
	public function spam( $args, $assoc_args ) {
		$user = $this->get_user_id_from_identifier( $args[0] );

		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or id' );
		}

		if ( bbp_make_spam_user( $user->ID ) ) {
			WP_CLI::success( 'User topics and replies marked as spam.' );
		} else {
			WP_CLI::error( 'Could not mark topics and replies as spam.' );
		}
	}

	/**
	 * Mark a user's topics and replies as ham
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp user moderator ham --user-id=465456
	 *    Success: User topics and replies marked as ham.
	 *
	 *    $ wp bbp user moderator ham --user-id=user_login
	 *    Success: User topics and replies marked as ham.
	 *
	 * @alias unspam
	 */
	public function ham( $args, $assoc_args ) {
		$user = $this->get_user_id_from_identifier( $args[0] );

		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or id' );
		}

		if ( bbp_make_ham_user( $user->ID ) ) {
			WP_CLI::success( 'User topics and replies marked as ham.' );
		} else {
			WP_CLI::error( 'Could not mark topics and replies as ham.' );
		}
	}

	/**
	 * Set user role
	 *
	 * ## OPTIONS
	 *
	 * --user-id<user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * --role<role>
	 * : Role to set for the member.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp user moderator set_role --user-id=465456 --role=mod
	 *    Success: User topics and replies marked as ham.
	 *
	 *    $ wp bbp user moderator set_role --user-id=user_login --role=mod
	 *    Success: User topics and replies marked as ham.
	 */
	public function set_role( $args, $assoc_args ) {
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );

		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or id' );
		}

		$retval = bbp_set_user_role( $user->ID, $assoc_args['role'] );

		if ( is_string( $retval ) ) {
			WP_CLI::success( sprintf( 'New role for user set: %s', $retval ) );
		} else {
			WP_CLI::error( 'Could not set new role for user.' );
		}
	}

	/**
	 * Get URL of the user profile page
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp user moderator permalink --user-id=465456
	 *    Success: User profile page: https://site.com/
	 *
	 *    $ wp bbp user moderator url --user-id=user_login
	 *    Success: User profile page: https://site.com/
	 *
	 * @alias url
	 */
	public function permalink( $args, $assoc_args ) {
		$user = $this->get_user_id_from_identifier( $args[0] );

		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or id' );
		}

		$permalink = bbp_get_user_profile_url( $user->ID );

		if ( is_string( $permalink ) ) {
			WP_CLI::success( sprintf( 'User profile page: %s', $permalink ) );
		} else {
			WP_CLI::error( 'Could not find user profile page.' );
		}
	}
}

WP_CLI::add_command( 'bbp user', 'BBPCLI_Users' );
