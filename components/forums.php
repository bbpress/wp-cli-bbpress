<?php
/**
 * Forums Component - bbPress.
 *
 * @since 1.0.0
 */
class BBPCLI_Forums extends BBPCLI_Component {
}

WP_CLI::add_command( 'bbp forums', 'BBPCLI_Forums', array(
	'before_invoke' => function() {
		if ( ! class_exists( 'bbPress' ) ) {
			WP_CLI::error( 'bbPress is not installed or active.' );
		}
	},
) );
