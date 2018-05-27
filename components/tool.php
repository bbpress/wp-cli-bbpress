<?php
namespace bbPress\CLI\Command;

use WP_CLI;

/**
 * Manage bbPress Tools.
 *
 * @since 1.0.0
 */
class Tool extends bbPressCommand {

	/**
	 * Repair Tools.
	 *
	 * ## OPTIONS
	 *
	 * --type=<type>
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
	 *    $ wp bbp tool repair --type=topic-reply-count
	 *    $ wp bbp tool repair --type=topic-hidden-reply-count
	 */
	public function repair( $args, $assoc_args ) {
		$repair = 'bbp_admin_repair_' . $this->sanitize_string( $assoc_args['type'] );

		if ( ! function_exists( $repair ) ) {
			\WP_CLI::error( 'There is no repair tool with that name.' );
		}

		$result = $repair();

		if ( 0 === $result[0] ) {
			\WP_CLI::success( $result[1] );
		} else {
			\WP_CLI::error( $result[1] );
		}
	}

	/**
	 * Upgrade Tools.
	 *
	 * ## OPTIONS
	 *
	 * --type=<type>
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
	 *    $ wp bbp tool upgrade --type=user-engagements
	 *    $ wp bbp tool upgrade --type=user-favorites
	 */
	public function upgrade( $args, $assoc_args ) {
		$upgrade = 'bbp_admin_upgrade_' . $this->sanitize_string( $assoc_args['type'] );

		if ( ! function_exists( $upgrade ) ) {
			\WP_CLI::error( 'There is no upgrade tool with that name.' );
		}

		$result = $upgrade();

		if ( 0 === $result[0] ) {
			\WP_CLI::success( $result[1] );
		} else {
			\WP_CLI::error( $result[1] );
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
	 *    $ wp bbp tool reset --yes
	 *    Success: bbPress reset.
	 */
	public function reset( $_, $assoc_args ) {
		\WP_CLI::confirm( 'Are you sure you want to reset bbPress?', $assoc_args );

		bbp_admin_reset_database();

		\WP_CLI::success( 'bbPress reset.' );
	}
}
