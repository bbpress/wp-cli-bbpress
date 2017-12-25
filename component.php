<?php
/**
 * Base component class.
 *
 * Stolen for wp-cli-buddypress.
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

	/**
	 * String Sanitization.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $type String to sanitize.
	 * @return string Sanitized string.
	 */
	protected function sanitize_string( $type ) {
		return strtolower( str_replace( '-', '_', $type ) );
	}

	/**
	 * User arguments used on the Engagements commands.
	 *
	 * @since 1.0.0
	 *
	 * @param  int $user_id User ID.
	 * @return array
	 */
	protected function user_args( $user_id ) {
		return array(
			'meta_query' => array( // WPCS: slow query ok.
				array(
					'value' => $user_id,
				),
			),
		);
	}
}
