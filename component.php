<?php
/**
 * Base component class.
 *
 * Borrowed for wp-cli-buddypress. Not sure if that's going to be needed yet.
 *
 * @since 1.0.0
 */
class BBPCLI_Component extends \WP_CLI\CommandWithDBObject {

	/**
	 * Get a random user id.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	protected function get_random_user_id() {
		global $wpdb;
		return $wpdb->get_var( "SELECT ID FROM $wpdb->users ORDER BY RAND() LIMIT 1" );
	}

	/**
	 * Verify a user ID by the passed identifier.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $i User ID, email or login.
	 * @return WP_User|false
	 */
	protected function get_user_id_from_identifier( $i ) {
		if ( is_numeric( $i ) ) {
			$user = get_user_by( 'id', $i );
		} elseif ( is_email( $i ) ) {
			$user = get_user_by( 'email', $i );
		} else {
			$user = get_user_by( 'login', $i );
		}

		return $user;
	}
}
