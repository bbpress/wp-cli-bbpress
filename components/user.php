<?php
/**
 * Manage bbPress Users.
 */
class BBPCLI_Users extends BBPCLI_Component {

	public function url( $args, $assoc_args ) {
		// $url = bbp_get_user_profile_url( $user_id = 0 );
	}

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

	public function spam( $args, $assoc_args ) {
		// bbp_make_spam_user( $user_id );
	}

	public function ham( $args, $assoc_args ) {
		// bbp_make_ham_user( $user_id );
	}

	public function update_role( $args, $assoc_args ) {
		// bbp_set_user_role( $user_id, $new_role );
	}
}

WP_CLI::add_command( 'bbp user', 'BBPCLI_Users' );
