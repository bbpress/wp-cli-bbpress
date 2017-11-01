<?php

/**
 * Load the bbPress.
 */
function _load_loader() {
	if ( defined( 'BBP_TESTS_DIR' ) ) {
		require( BBP_TESTS_DIR . '/includes/loader.php' );
	}
}
tests_add_filter( 'muplugins_loaded', '_load_loader' );
