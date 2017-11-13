<?php

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode,
    WP_CLI\Process;

$steps->Given( '/^a bbPress install$/',
	function ( $world ) {
		$world->install_wp();
		$dest_dir = $world->variables['RUN_DIR'] . '/wp-content/plugins/bbpress/';
		if ( ! is_dir( $dest_dir ) ) {
			mkdir( $dest_dir );
		}

		$bbp_src_dir = getenv( 'BBP_SRC_DIR' );
		try {
			$world->copy_dir( $bbp_src_dir, $dest_dir );
			$world->proc( 'wp plugin activate bbpress' )->run_check();

		} catch ( Exception $e ) {};
	}
);
