<?php
/**
 * Tools Component - bbPress.
 */
class BBPCLI_Tools extends BBPCLI_Component {

	/**
	 * Repair.
	 *
	 * ## OPTIONS
	 *
	 * [--type=<type>]
	 * : Name of the repair tool.
	 * ---
	 * options:
	 *   - topic-reply-count
	 *   - topic-voice-count
	 *   - topic-hidden-reply-count
	 *   - forum-topic-count
	 *   - topic-tag-count
	 *   - forum-reply-count
	 *   - user-topic-count
	 *   - user-reply-count
	 *   - user-favorites
	 *   - user-topic-subscriptions
	 *   - user-forum-subscriptions
	 *   - user-roles
	 *   - freshness
	 *   - sticky
	 *   - closed-topics
	 *   - forum-visibility
	 *   - forum-meta
	 *   - topic-meta
	 *   - reply-menu-order
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *    wp bbp tools repair --type=topic-reply-count
	 *    wp bbp tools repair --type=topic-hidden-reply-count
	 *
	 * @synopsis [--type=<type>]
	 *
	 * @since 1.0.0
	 */
	public function repair( $args, $assoc_args ) {
		$r = wp_parse_args( $assoc_args, array(
			'type' => '',
		) );

		// If no type added, bail it.
		if ( empty( $r['type'] ) ) {
			WP_CLI::error( 'You need to add a name of the repair tool.' );
		}

		// Repair function.
		$repair = 'bbp_admin_repair_' . sanitize_key( $r['type'] );

		if ( function_exists( $repair ) ) {
			$result = $repair();

			if ( 0 === $result[0] ) {
				WP_CLI::success( $result[1] );
			} else {
				WP_CLI::error( sprintf( 'Error: %s', $result[1] ) );
			}
		} else {
			WP_CLI::error( 'There is no repair tool with that name.' );
		}
	}

	/**
	 * Upgrade.
	 *
	 * ## OPTIONS
	 *
	 * [--type=<type>]
	 * : Name of the upgrade tool.
	 * ---
	 * options:
	 *   - group-forum-relationships
	 *   - user-engagements
	 *   - user-favorites
	 *   - user-topic-subscriptions
	 *   - user-forum-subscriptions
	 *   - remove-favorites-from_usermeta
	 *   - remove-topic-subscriptions-from-usermeta
	 *   - remove-forum-subscriptions-from-usermeta
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *    wp bbp tools upgrade --type=user-engagements
	 *    wp bbp tools upgrade --type=user-favorites
	 *
	 * @synopsis [--type=<type>]
	 *
	 * @since 1.0.0
	 */
	public function upgrade( $args, $assoc_args ) {
		$r = wp_parse_args( $assoc_args, array(
			'type' => '',
		) );

		// If no type added, bail it.
		if ( empty( $r['type'] ) ) {
			WP_CLI::error( 'You need to add a name of the upgrade tool.' );
		}

		// Repair function.
		$upgrade = 'bbp_admin_upgrade_' . sanitize_key( $r['type'] );

		if ( function_exists( $upgrade ) ) {
			$result = $upgrade();

			if ( 0 === $result[0] ) {
				WP_CLI::success( $result[1] );
			} else {
				WP_CLI::error( sprintf( 'Error: %s', $result[1] ) );
			}
		} else {
			WP_CLI::error( 'There is no upgrade tool with that name.' );
		}
	}

	/**
	 * Reset bbPress.
	 *
	 * ## OPTIONS
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message.
	 *
	 * ## EXAMPLE
	 *
	 *    wp bbp tools reset --yes
	 *    Success: bbPress reset.
	 *
	 * @synopsis [--yes>]
	 *
	 * @since 1.0.0
	 */
	public function reset( $_, $assoc_args ) {
		WP_CLI::confirm( 'Are you sure you want to reset bbPress?', $assoc_args );

		$reset = bbp_admin_reset_handler();

		if ( empty( $reset ) ) {
			WP_CLI::error( 'Could not reset bbPress. Please, try again.' );
		} else {
			WP_CLI::success( 'bbPress reset.' );
		}
	}

	/**
	 * List converters.
	 *
	 * ## EXAMPLE
	 *
	 *    wp bbp tools list_converters
	 *
	 * @since 1.0.0
	 */
	public function list_converters( $_, $assoc_args ) {
		echo implode( ', ', bbp_get_converters() ); // WPCS: XSS ok.
	}
}

WP_CLI::add_command( 'bbp tools', 'BBPCLI_Tools' );
