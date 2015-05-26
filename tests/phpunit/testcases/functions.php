<?php
/**
 * @group files
 */
class BuddyDrive_Files_Tests extends BuddyDrive_TestCase {

	public function setUp() {
		parent::setUp();

		$this->current_user = get_current_user_id();
		$this->user_id      = $this->factory->user->create();
		$this->set_current_user( $this->user_id );
		$this->file         = trailingslashit( buddydrive()->plugin_dir ) . 'screenshot-1.png';
	}

	public function tearDown() {
		parent::tearDown();

		$this->set_current_user( $this->current_user );
		unset( $this->file );
	}

	public function restrict_mimes( $mimes = array() ) {
		return array_intersect_key( $mimes, array( 'png' => true ) );
	}

	/**
	 * @group upload
	 */
	public function test_buddydrive_upload_item() {
		$reset_files = $_FILES;
		$reset_post = $_POST;

		$tmp_name = wp_tempnam( $this->file );

		copy( $this->file, $tmp_name );

		$_POST['action'] = 'buddydrive_upload';
		$_FILES['buddyfile-upload'] = array(
			'tmp_name' => $tmp_name,
			'name'     => 'screenshot-1.png',
			'type'     => 'image/png',
			'error'    => 0,
			'size'     => filesize( $this->file )
		);

		// Upload the file
		$upload = buddydrive_upload_item( $_FILES, bp_loggedin_user_id() );

		$this->assertTrue( file_exists( $upload['file'] ) );
		$this->assertTrue( (int) get_user_meta( $this->user_id, '_buddydrive_total_space', true ) === (int) $_FILES['buddyfile-upload']['size'] );

		// clean up!
		$_FILES = $reset_files;
		$_POST = $reset_post;
	}

	/**
	 * @group upload
	 * @group check_mimes
	 */
	public function test_buddydrive_upload_item_mimes() {
		$reset_files = $_FILES;
		$reset_post = $_POST;

		$file = trailingslashit( buddydrive()->plugin_dir ) . 'readme.txt';

		$tmp_name = wp_tempnam( $file );

		copy( $file, $tmp_name );

		$_POST['action'] = 'buddydrive_upload';
		$_FILES['buddyfile-upload'] = array(
			'tmp_name' => $tmp_name,
			'name'     => 'readme.txt',
			'type'     => 'text/plain',
			'error'    => 0,
			'size'     => filesize( $file )
		);

		add_filter( 'upload_mimes', array( $this, 'restrict_mimes' ), 10, 1 );

		// Upload the file
		$upload = buddydrive_upload_item( $_FILES, bp_loggedin_user_id() );

		remove_filter( 'upload_mimes', array( $this, 'restrict_mimes' ), 10, 1 );

		$this->assertTrue( ! empty( $upload['error'] ) );

		// clean up!
		$_FILES = $reset_files;
		$_POST = $reset_post;
	}

	/**
	 * @group upload
	 * @group check_user_space
	 */
	public function test_buddydrive_upload_item_nospace() {
		$reset_files = $_FILES;
		$reset_post = $_POST;

		$tmp_name = wp_tempnam( $this->file );

		copy( $this->file, $tmp_name );

		$_POST['action'] = 'buddydrive_upload';
		$_FILES['buddyfile-upload'] = array(
			'tmp_name' => $tmp_name,
			'name'     => 'screenshot-1.png',
			'type'     => 'image/png',
			'error'    => 0,
			'size'     => filesize( $this->file )
		);

		update_user_meta( $this->user_id, '_buddydrive_total_space', 1000 * 1024 * 1024 );

		// Upload the file
		$upload = buddydrive_upload_item( $_FILES, bp_loggedin_user_id() );

		$this->assertTrue( ! empty( $upload['error'] ) );

		// clean up!
		$_FILES = $reset_files;
		$_POST = $reset_post;
	}

	/**
	 * @group upload
	 * @group check_upload_limit
	 */
	public function test_buddydrive_upload_item_upload_limit() {
		$reset_files = $_FILES;
		$reset_post = $_POST;

		$tmp_name = wp_tempnam( $this->file );

		copy( $this->file, $tmp_name );

		$_POST['action'] = 'buddydrive_upload';
		$_FILES['buddyfile-upload'] = array(
			'tmp_name' => $tmp_name,
			'name'     => 'screenshot-1.png',
			'type'     => 'image/png',
			'error'    => 0,
			'size'     => filesize( $this->file )
		);

		add_filter( 'upload_size_limit', '__return_zero' );

		// Upload the file
		$upload = buddydrive_upload_item( $_FILES, bp_loggedin_user_id() );

		remove_filter( 'upload_size_limit', '__return_zero' );

		$this->assertTrue( ! empty( $upload['error'] ) );

		// clean up!
		$_FILES = $reset_files;
		$_POST = $reset_post;
	}
}
