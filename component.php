<?php
namespace bbPress\CLI\Command;

use WP_CLI;
use WP_CLI\CommandWithDBObject;

/**
 * Base component class.
 *
 * Stolen for wp-cli-buddypress.
 *
 * @since 1.0.0
 */
abstract class bbPressCommand extends CommandWithDBObject {

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

		if ( ! $user ) {
			\WP_CLI::error( sprintf( 'No user found by that username or ID (%).', $i ) );
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
