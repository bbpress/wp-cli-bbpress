<?php

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode,
    WP_CLI\Process;

$steps->Given( '/^a bbPress install$/',
	function ( $world ) {
		$world->install_wp();
		try {
			$world->proc( 'wp plugin install bbpress --activate' )->run_check();

		} catch ( Exception $e ) {};
	}
);
