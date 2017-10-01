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
	 *    wp bbp repair --type=topic-reply-count
	 *    wp bbp repair --type=topic-hidden-reply-count
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

			WP_CLI::success( $result[1] );
		} else {
			WP_CLI::error( 'There is no repair tool with that name.' );
		}
	}
}

WP_CLI::add_command( 'bbp tools', 'BBPCLI_Tools' );
