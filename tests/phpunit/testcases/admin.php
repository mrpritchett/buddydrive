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
	}

	public function tearDown() {
		parent::tearDown();

		$this->set_current_user( $this->current_user );
		unset( $GLOBALS['current_screen'], $this->suffix );
	}

	public function test_has_htaccess() {
		if ( ! function_exists( 'buddydrive_admin' ) ) {
			require_once( buddydrive()->includes_dir . 'admin/buddydrive-admin.php' );
		}

		// Load the admin
		buddydrive_admin();

		set_current_screen( 'plugins' . $this->suffix );

		$notice_hook = bp_core_do_network_admin() ? 'network_admin_notices' : 'admin_notices' ;

		do_action( $notice_hook );

		$this->assertTrue( file_exists( buddydrive()->upload_dir . '/.htaccess' ) );
	}

	/**
	 * @group bulk_delete
	 */
	public function test_buddydrive_bulk_delete_items() {
		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$expected_ids = array();
		$quota_left = 0;
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();

		$items_users = array(
			'screenshot-1.png' => $u1,
			'screenshot-2.png' => $u1,
			'screenshot-3.png' => $u2,
		);

		$args = array(
			'type'      => buddydrive_get_file_post_type(),
			'mime_type' => 'image/png',
		);

		foreach ( $items_users as $item => $user ) {
			$f = trailingslashit( buddydrive()->plugin_dir ) . $item;
			copy( $f, trailingslashit( $upload_dir['dir'] ) . $item );

			$fs = filesize( $item );
			buddydrive_update_user_space( $user, filesize( $item ) );

			if ( 'screenshot-1.png' === $item ) {
				$quota_left = $fs;
			}

			$args = array_merge( $args, array(
				'user_id' => $user,
				'title'   => $item,
				'guid'    => trailingslashit( $upload_dir['url'] ) . $item,
			) );

			$expected_ids[ $item ] = buddydrive_save_item( $args );
		}

		unset( $expected_ids[ 'screenshot-1.png' ] );

		$count = buddydrive_delete_item( array( 'ids' => $expected_ids ) );

		$this->assertTrue( $count === count( $expected_ids ) );

		$not_deleted = buddydrive_get_buddyfiles_by_ids( $expected_ids );
		$this->assertTrue( empty( $not_deleted ) );

		$this->assertEquals( $quota_left, (int) get_user_meta( $u1, '_buddydrive_total_space', true ), 'u1 quota should be set' );
		$this->assertEmpty( (int) get_user_meta( $u2, '_buddydrive_total_space', true ), 'u2 quota should be 0' );
	}
}
