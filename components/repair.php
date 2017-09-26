<?php
/**
 * Repair Tools - bbPress.
 */
class BBPCLI_Repair extends BBPCLI_Component {

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

		$type = $args['type'];

		$repair = 'bbp_admin_repair_' . $type;
		if ( function_exists( $repair ) ) {
			WP_CLI::success( $repair() );
		} else {
			WP_CLI::error( 'There is no repair tool with that name.' );
		}
	}
}

WP_CLI::add_command( 'bbp repair', 'BBPCLI_Repair' );
