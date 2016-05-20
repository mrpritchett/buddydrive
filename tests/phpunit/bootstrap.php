<?php

require_once getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/functions.php';

function _bootstrap_buddydrive() {

	if ( ! defined( 'BP_TESTS_DIR' ) ) {
		define( 'BP_TESTS_DIR', dirname( __FILE__ ) . '/../../../buddypress/tests/phpunit' );
	}

	if ( ! file_exists( BP_TESTS_DIR . '/bootstrap.php' ) )  {
		die( 'The BuddyPress Test suite could not be found' );
	}

	// Make sure BP is installed and loaded first
	require BP_TESTS_DIR . '/includes/loader.php';

	// load WP Idea Stream
	require dirname( __FILE__ ) . '/../../buddydrive.php';

	update_option( '_buddydrive_db_version', buddydrive_get_number_version() );
}
tests_add_filter( 'muplugins_loaded', '_bootstrap_buddydrive' );

require getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/bootstrap.php';
require BP_TESTS_DIR . '/includes/testcase.php';

// include our testcase
require( 'testcase.php' );
