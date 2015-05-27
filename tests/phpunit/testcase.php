<?php
/**
 * Include BuddyDrive Factory
 */
require_once dirname( __FILE__ ) . '/factory.php';

/**
 * Requires BuddyPress unit testcase
 */
if ( class_exists( 'BP_UnitTestCase' ) ) :
class BuddyDrive_TestCase extends BP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$this->factory = new BuddyDrive_UnitTest_Factory;
	}

	public function tearDown() {
		parent::tearDown();

		$dir = buddydrive()->upload_dir;

		$d = glob( $dir . '/*' );

		if ( ! empty( $d ) ) {
			foreach ( $d as $file ) {
				@unlink( $file );
			}
		}

		if ( file_exists( $dir . '/.htaccess' ) ) {
			@unlink( $dir . '/.htaccess' );
		}

		if ( is_dir( $dir) ) {
			rmdir( $dir );
		}
	}
}
else :

die( 'The BP_UnitTestCase class does not exist' );

endif;
