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
		$defaults = array(
			'type' => '',
		);

		$r = wp_parse_args( $assoc_args, $defaults );

		// If no type added, bail it.
		if ( empty( $r['type'] ) ) {
			WP_CLI::error( 'You need to add a name of the repair tool.' );
		}

		// Convert to underscore.
		$type = str_replace( '-', '_', $r['type'] );

		// Repair function.
		$repair = 'bbp_admin_repair_' . $type;

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
		$defaults = array(
			'type' => '',
		);

		$r = wp_parse_args( $assoc_args, $defaults );

		// If no type added, bail it.
		if ( empty( $r['type'] ) ) {
			WP_CLI::error( 'You need to add a name of the upgrade tool.' );
		}

		// Convert to underscore.
		$type = str_replace( '-', '_', $r['type'] );

		// Repair function.
		$upgrade = 'bbp_admin_upgrade_' . $type;

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
