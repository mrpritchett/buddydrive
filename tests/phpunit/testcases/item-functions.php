<?php
/**
 * @group item_functions
 */
class BuddyDrive_Item_Functions_Tests extends BuddyDrive_TestCase {

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

		bp_update_option( '_buddydrive_allowed_extensions',  array( 'png' ) );

		// Upload the file
		$upload = buddydrive_upload_item( $_FILES, bp_loggedin_user_id() );
		$this->assertTrue( ! empty( $upload['error'] ) );

		bp_update_option( '_buddydrive_allowed_extensions',  array( 'png', 'txt|asc|c|cc|h|srt' ) );

		$upload = buddydrive_upload_item( $_FILES, bp_loggedin_user_id() );
		$this->assertTrue( file_exists( $upload['file'] ) );

		bp_delete_option( '_buddydrive_allowed_extensions' );

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

	/**
	 * @group save
	 */
	public function test_buddydrive_save_item() {
		$meta = new stdClass();
		$meta->privacy = 'public';
		$expected_ids = array();

		$expected_ids['folder_id'] = buddydrive_save_item( array(
			'type'  => buddydrive_get_folder_post_type(),
			'title' => 'foo',
			'content' => 'foo bar folder',
			'metas' => $meta
		) );

		$expected_ids['file_id'] = buddydrive_save_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->user_id,
			'parent_folder_id' => $expected_ids['folder_id'],
			'title'            => 'screenshot-1.png',
			'content'          => 'foo bar file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( buddydrive()->upload_url ) . 'screenshot-1.png',
			'metas'            => $meta,
		) );

		$requested_ids = wp_list_pluck( buddydrive_get_buddyfiles_by_ids( $expected_ids ), 'ID' );
		$this->assertSame( array_values( $expected_ids ), array_map( 'intval', $requested_ids ) );

		$file_object = buddydrive_get_buddyfile( $expected_ids['file_id'] );
		$this->assertTrue( (int) $file_object->post_parent === (int) $expected_ids['folder_id'] );
	}

	/**
	 * @group save
	 * @group update
	 */
	public function test_buddydrive_update_item() {
		$meta = new stdClass();
		$meta->privacy = 'public';
		$expected_ids = array();

		$expected_ids['file_id'] = buddydrive_save_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->user_id,
			'title'            => 'screenshot-1.png',
			'content'          => 'foo bar file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( buddydrive()->upload_url ) . 'screenshot-1.png',
			'metas'            => $meta,
		) );

		$file_object = buddydrive_get_buddyfile( $expected_ids['file_id'] );
		$this->assertTrue( 'public' === $file_object->check_for );

		$meta->privacy = 'private';

		$expected_ids['folder_id'] = buddydrive_save_item( array(
			'type'  => buddydrive_get_folder_post_type(),
			'title' => 'foo',
			'content' => 'foo bar folder',
			'metas' => $meta
		) );

		buddydrive_update_item( array(
			'parent_folder_id' => $expected_ids['folder_id'],
		), $file_object );

		$file_object = buddydrive_get_buddyfile( $expected_ids['file_id'] );
		$this->assertTrue( (int) $file_object->post_parent === (int) $expected_ids['folder_id'] );
		$this->assertTrue( 'private' === $file_object->check_for );

		$folder_object = buddydrive_get_buddyfile( $expected_ids['folder_id'], buddydrive_get_folder_post_type() );

		buddydrive_update_item( array(
			'privacy' => 'public',
		), $folder_object );

		$file_object = buddydrive_get_buddyfile( $expected_ids['file_id'] );
		$this->assertTrue( 'public' === $file_object->check_for );
	}

	/**
	 * @group delete
	 * @group save
	 */
	public function test_buddydrive_delete_item() {
		$expected_ids = array();

		$args = array(
			'type'      => buddydrive_get_file_post_type(),
			'user_id'   => $this->user_id,
			'title'     => 'screenshot-1.png',
			'content'   => 'foo file',
			'mime_type' => 'image/png',
			'guid'      => trailingslashit( buddydrive()->upload_url ) . 'screenshot-1.png',
		);

		$expected_ids[] = buddydrive_save_item( $args );

		$args = array_merge( $args, array(
			'title'     => 'readme.txt',
			'content'   => 'bar file',
			'mime_type' => 'text/plain',
			'guid'      => trailingslashit( buddydrive()->upload_url ) . 'readme.txt',
		) );

		$expected_ids[] = buddydrive_save_item( $args );

		$count = buddydrive_delete_item( array( 'ids' => $expected_ids, 'user_id' => false ) );

		$this->assertTrue( $count === count( $expected_ids ) );

		$not_deleted = buddydrive_get_buddyfiles_by_ids( $expected_ids );
		$this->assertTrue( empty( $not_deleted ) );
	}

	/**
	 * @group delete
	 */
	public function test_buddydrive_delete_user() {
		$expected_ids = array();
		$user_id = $this->factory->user->create();

		$args = array(
			'type'      => buddydrive_get_file_post_type(),
			'user_id'   => $user_id,
			'title'     => 'screenshot-1.png',
			'content'   => 'foo file',
			'mime_type' => 'image/png',
			'guid'      => trailingslashit( buddydrive()->upload_url ) . 'screenshot-1.png',
		);

		$expected_ids[] = buddydrive_save_item( $args );

		$args = array_merge( $args, array(
			'title'     => 'readme.txt',
			'content'   => 'bar file',
			'mime_type' => 'text/plain',
			'guid'      => trailingslashit( buddydrive()->upload_url ) . 'readme.txt',
		) );

		$expected_ids[] = buddydrive_save_item( $args );

		wp_delete_user( $user_id );

		$not_deleted = buddydrive_get_buddyfiles_by_ids( $expected_ids );
		$this->assertTrue( empty( $not_deleted ) );
	}

	/**
	 * @group delete
	 */
	public function test_buddydrive_delete_item_zero() {
		$expected_id = buddydrive_save_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->user_id,
			'title'            => 'screenshot-1.png',
			'content'          => 'foo file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( buddydrive()->upload_url ) . 'screenshot-1.png',
		) );

		$count = buddydrive_delete_item( array( 'ids' => 0, 'user_id' => false ) );

		$this->assertEmpty( $count );

		$not_deleted = buddydrive_get_buddyfiles_by_ids( $expected_id );
		$this->assertTrue( ! empty( $not_deleted ) );
	}
}
