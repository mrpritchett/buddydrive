<?php
/**
 * @group admin
 */
class BuddyDrive_Admin_Tests extends BuddyDrive_TestCase {

	public function setUp() {
		parent::setUp();

		$this->current_user = get_current_user_id();
		$this->set_current_user( 1 );

		$this->suffix = '';

		if ( is_multisite() ) {
			$this->suffix = '-network';
		}

		if ( ! function_exists( 'buddydrive_admin' ) ) {
			require_once( buddydrive()->includes_dir . 'admin/buddydrive-admin.php' );
		}

		// Load the admin
		buddydrive_admin();
	}

	public function tearDown() {
		parent::tearDown();

		$this->set_current_user( $this->current_user );
		unset( $GLOBALS['current_screen'], $this->suffix );
	}

	public function test_has_htaccess() {
		set_current_screen( 'plugins' . $this->suffix );

		$notice_hook = bp_core_do_network_admin() ? 'network_admin_notices' : 'admin_notices' ;

		do_action( $notice_hook );

		$this->assertTrue( file_exists( buddydrive()->upload_dir . '/.htaccess' ) );
	}
}
