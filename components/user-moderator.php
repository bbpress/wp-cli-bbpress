<?php
/**
 * Manage bbPress Moderators.
 */
class BBPCLI_User_Moderator extends BBPCLI_Component {

	public function add( $args, $assoc_args ) {
		// bbp_add_moderator( $user_id );
	}

	public function remove( $args, $assoc_args ) {
		// bbp_remove_moderator( $user_id );
	}

	public function list( $args, $assoc_args ) {
		// bbp_get_moderators();
	}
}

WP_CLI::add_command( 'bbp user moderator', 'BBPCLI_User_Moderator' );
