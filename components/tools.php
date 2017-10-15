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
	 *   - topic-hidden-reply-count
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *    wp bbp tools repair --type=topic-reply-count
	 *    wp bbp tools repair --type=topic-hidden-reply-count
	 *
	 * @synopsis [--type=<type>]
	 *
	 * @since 1.0
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
	 *   - user-engagements
	 *   - user-favorites
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *    wp bbp tools upgrade --type=user-engagements
	 *    wp bbp tools upgrade --type=user-favorites
	 *
	 * @synopsis [--type=<type>]
	 *
	 * @since 1.0
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
}

WP_CLI::add_command( 'bbp tools', 'BBPCLI_Tools' );
