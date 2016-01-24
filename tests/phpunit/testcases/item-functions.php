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
		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

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
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
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
		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$meta = new stdClass();
		$meta->privacy = 'public';
		$expected_ids = array();

		$expected_ids['file_id'] = buddydrive_save_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->user_id,
			'title'            => 'screenshot-1.png',
			'content'          => 'foo bar file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
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
	 * @group update
	 * @group save
	 * @group groups
	 */
	public function test_buddydrive_update_item_for_groups() {
		$c  = $this->factory->user->create();
		$g1 = $this->factory->group->create( array( 'creator_id' => $c ) );
		$g2 = $this->factory->group->create( array( 'status' => 'private', 'creator_id' => $c  ) );

		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$meta           = new stdClass();
		$meta->privacy  = 'groups';
		$meta->groups   = array( $g1 );

		$ing1 = buddydrive_save_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $c,
			'title'            => 'screenshot-1.png',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
			'metas'            => $meta,
		) );

		$file_g1 = buddydrive_get_buddyfile( $ing1 );

		$this->assertEquals( array( $g1 ), $file_g1->group );

		buddydrive_update_item( array(
			'group' => array( $g1, $g2 ),
		), $file_g1 );

		$file_g1_g2 = buddydrive_get_buddyfile( $ing1 );

		$this->assertEquals( array( $g1, $g2 ), $file_g1_g2->group );

		buddydrive_update_item( array(
			'group' => array( $g2 ),
		), $file_g1_g2 );

		$file_g2 = buddydrive_get_buddyfile( $ing1 );

		$this->assertEquals( array( $g2 ), $file_g2->group );

		buddydrive_update_item( array(
			'group' => false,
		), $file_g2 );

		$file_none = buddydrive_get_buddyfile( $ing1 );

		$this->assertTrue( 'private' === $file_none->check_for );

		$folderg2 = buddydrive_add_item( array(
			'type'             => buddydrive_get_folder_post_type(),
			'user_id'          => $c,
			'title'            => 'folder',
			'privacy'          => 'groups',
			'groups'           => array( $g2 ),
		) );

		$childreng2 = buddydrive_add_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $c,
			'title'            => 'screenshot-2.png',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-2.png',
			'parent_folder_id' => $folderg2,
		) );

		$file_c = buddydrive_get_buddyfile( $childreng2 );

		$this->assertTrue( 'groups' === $file_c->check_for );
		$this->assertEquals( array( $g2 ), $file_c->group );
	}

	/**
	 * @group delete
	 * @group save
	 */
	public function test_buddydrive_delete_item() {
		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$expected_ids = array();

		$args = array(
			'type'      => buddydrive_get_file_post_type(),
			'user_id'   => $this->user_id,
			'title'     => 'screenshot-1.png',
			'content'   => 'foo file',
			'mime_type' => 'image/png',
			'guid'      => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
		);

		$expected_ids[] = buddydrive_save_item( $args );

		$args = array_merge( $args, array(
			'title'     => 'readme.txt',
			'content'   => 'bar file',
			'mime_type' => 'text/plain',
			'guid'      => trailingslashit( $upload_dir['url'] ) . 'readme.txt',
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
		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$expected_ids = array();
		$user_id = $this->factory->user->create();

		$args = array(
			'type'      => buddydrive_get_file_post_type(),
			'user_id'   => $user_id,
			'title'     => 'screenshot-1.png',
			'content'   => 'foo file',
			'mime_type' => 'image/png',
			'guid'      => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
		);

		$expected_ids[] = buddydrive_save_item( $args );

		$args = array_merge( $args, array(
			'title'     => 'readme.txt',
			'content'   => 'bar file',
			'mime_type' => 'text/plain',
			'guid'      => trailingslashit( $upload_dir['url'] ) . 'readme.txt',
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
		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$expected_id = buddydrive_save_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->user_id,
			'title'            => 'screenshot-1.png',
			'content'          => 'foo file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
		) );

		$count = buddydrive_delete_item( array( 'ids' => 0, 'user_id' => false ) );

		$this->assertEmpty( $count );

		$not_deleted = buddydrive_get_buddyfiles_by_ids( $expected_id );
		$this->assertTrue( ! empty( $not_deleted ) );
	}

	/**
	 * @group buddydrive_get_buddyfile
	 */
	public function test_buddydrive_get_buddyfile() {
		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$expected_id = buddydrive_save_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->user_id,
			'title'            => 'screenshot-1.png',
			'content'          => 'foo file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
		) );

		$properties = array( 'ID', 'user_id', 'title', 'content', 'post_parent', 'post_type', 'guid', 'mime_type', 'file', 'path', 'link', 'check_for' );

		$file = buddydrive_get_buddyfile( $expected_id );

		$this->assertEquals( $properties, array_intersect( $properties, array_keys( (array) $file ) ) );
	}

	/**
	 * @group buddydrive_get_sharing_options
	 */
	public function test_buddydrive_get_sharing_options() {
		add_filter( 'bp_is_active', '__return_false' );

		$options = buddydrive_get_sharing_options();

		remove_filter( 'bp_is_active', '__return_false' );

		$this->assertTrue( ! isset( $options['groups'] ) && ! isset( $options['friends'] ) && isset( $options['public'] ) );

		$options = buddydrive_get_sharing_options();

		$this->assertTrue( isset( $options['groups'] ) && isset( $options['friends'] ) && isset( $options['public'] ) );
	}

	/**
	 * @group buddydrive_check_download
	 * @group groups
	 */
	public function test_buddydrive_check_download() {
		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$c = $this->factory->user->create();

		$id = buddydrive_save_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->user_id,
			'title'            => 'screenshot-1.png',
			'content'          => 'foo file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
		) );

		$file = buddydrive_get_buddyfile( $id );

		$cd = buddydrive_check_download( $file, 0 );
		$this->assertTrue( is_wp_error( $cd ) );

		$cd = buddydrive_check_download( $file, $c );
		$this->assertTrue( is_wp_error( $cd ) );
		$this->assertTrue( buddydrive_check_download( $file, $this->user_id ) );

		buddydrive_update_item( array(
			'privacy' => 'password',
			'password' => 'bar',
		), $file );

		$file = buddydrive_get_buddyfile( $id );

		$cd = buddydrive_check_download( $file, 0 );
		$this->assertTrue( is_wp_error( $cd ) );

		$cd = buddydrive_check_download( $file, $c );
		$this->assertTrue( is_wp_error( $cd ) );
		$this->assertTrue( buddydrive_check_download( $file, $this->user_id ) );

		$file->pass_submitted = 'bar';
		$this->assertTrue( buddydrive_check_download( $file, 0 ) );
		$this->assertTrue( buddydrive_check_download( $file, $c ) );

		unset( $file->pass_submitted );

		buddydrive_update_item( array(
			'privacy' => 'public',
		), $file );

		$file = buddydrive_get_buddyfile( $id );
		$this->assertTrue( buddydrive_check_download( $file, 0 ) );
		$this->assertTrue( buddydrive_check_download( $file, $c ) );
		$this->assertTrue( buddydrive_check_download( $file, $this->user_id ) );

		$g1 = $this->factory->group->create( array( 'creator_id' => $c ) );
		$g2 = $this->factory->group->create( array( 'status' => 'hidden', 'creator_id' => $this->user_id ) );
		$g3 = $this->factory->group->create( array( 'status' => 'private', 'creator_id' => $c ) );
		$g4 = $this->factory->group->create( array( 'creator_id' => $this->user_id ) );

		buddydrive_update_item( array(
			'privacy' => 'groups',
			'group'  => array( $g2 )
		), $file );

		$file = buddydrive_get_buddyfile( $id );

		$cd = buddydrive_check_download( $file, 0 );
		$this->assertTrue( is_wp_error( $cd ) );

		$cd = buddydrive_check_download( $file, $c );
		$this->assertTrue( is_wp_error( $cd ) );

		$this->assertTrue( buddydrive_check_download( $file, $this->user_id ) );

		buddydrive_update_item( array(
			'privacy' => 'groups',
			'group'  => array( $g2, $g3 )
		), $file );

		$file = buddydrive_get_buddyfile( $id );

		$cd = buddydrive_check_download( $file, 0 );
		$this->assertTrue( is_wp_error( $cd ) );

		$this->assertTrue( buddydrive_check_download( $file, $c ) );
		$this->assertTrue( buddydrive_check_download( $file, $this->user_id ) );

		buddydrive_update_item( array(
			'privacy' => 'groups',
			'group'  => array( $g2, $g3, $g4 )
		), $file );

		$file = buddydrive_get_buddyfile( $id );

		$this->assertTrue( buddydrive_check_download( $file, 0 ) );
		$this->assertTrue( buddydrive_check_download( $file, $c ) );
		$this->assertTrue( buddydrive_check_download( $file, $this->user_id ) );

		buddydrive_update_item( array(
			'privacy' => 'friends',
		), $file );

		$file = buddydrive_get_buddyfile( $id );

		$cd = buddydrive_check_download( $file, 0 );
		$this->assertTrue( is_wp_error( $cd ) );

		$cd = buddydrive_check_download( $file, $c );
		$this->assertTrue( is_wp_error( $cd ) );

		friends_add_friend( $this->user_id, $c, true );
		$this->assertTrue( buddydrive_check_download( $file, $c ) );

		$this->assertTrue( buddydrive_check_download( $file, $this->user_id ) );
	}

	/**
	 * @group buddydrive_remove_item_from_group
	 * @group groups
	 */
	public function test_buddydrive_remove_item_from_group() {
		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$c = $this->factory->user->create();
		$g1 = $this->factory->group->create( array( 'creator_id' => $c ) );
		$g2 = $this->factory->group->create( array( 'status' => 'hidden', 'creator_id' => $this->user_id ) );

		$id = buddydrive_add_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $c,
			'title'            => 'screenshot-1.png',
			'content'          => 'foo file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
			'privacy'          => 'groups',
			'groups'           => array( $g1 ),
		) );

		buddydrive_remove_item_from_group( $id, $g1 );

		$file = buddydrive_get_buddyfile( $id );

		$this->assertTrue( empty( $file->group ) );
		$this->assertTrue( 'public' === $file->check_for );
		$this->assertTrue( 'buddydrive_groups' !== get_post_status( $id ) );

		buddydrive_update_item( array( 'privacy' => 'groups', 'group' => array( $g1, $g2 ) ), $file );

		buddydrive_remove_item_from_group( $id, $g1 );

		$file = buddydrive_get_buddyfile( $id );

		$this->assertEquals( array( $g2 ), $file->group );
		$this->assertTrue( 'groups' === $file->check_for );
		$this->assertTrue( 'buddydrive_groups' === get_post_status( $id ) );

		buddydrive_remove_item_from_group( $id, $g2 );

		$file = buddydrive_get_buddyfile( $id );

		$this->assertTrue( empty( $file->group ) );
		$this->assertTrue( 'private' === $file->check_for );
		$this->assertTrue( 'buddydrive_groups' !== get_post_status( $id ) );

		$folder_id = buddydrive_add_item( array(
			'type'             => buddydrive_get_folder_post_type(),
			'user_id'          => $c,
			'title'            => 'screenshot-1.png',
			'privacy'          => 'groups',
			'groups'           => array( $g2 ),
		) );

		buddydrive_update_item( array( 'parent_folder_id' => $folder_id ), $file );

		buddydrive_remove_item_from_group( $folder_id, $g2 );

		$folder = buddydrive_get_buddyfile( $folder_id, buddydrive_get_folder_post_type() );
		$file = buddydrive_get_buddyfile( $id );

		$this->assertTrue( $folder->post_status === $file->post_status && $file->post_status === 'buddydrive_private' );
		$this->assertTrue( empty( $folder->group ) && empty( $file->group ) );
	}

	/**
	 * @group buddydrive_remove_items_from_group
	 * @group groups
	 */
	public function test_buddydrive_remove_buddyfiles_from_group() {
		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$c  = $this->factory->user->create();
		$g1 = $this->factory->group->create( array( 'creator_id' => $c ) );
		$g2 = $this->factory->group->create( array( 'status' => 'hidden', 'creator_id' => $this->user_id ) );

		$f1 = buddydrive_add_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $c,
			'title'            => 'screenshot-1.png',
			'content'          => 'foo file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
			'privacy'          => 'groups',
			'groups'           => array( $g1 ),
		) );

		$f2 = buddydrive_add_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $c,
			'title'            => 'screenshot-2.png',
			'content'          => 'foo file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-2.png',
			'privacy'          => 'groups',
			'groups'           => array( $g1, $g2 ),
		) );

		$folder_id = buddydrive_add_item( array(
			'type'             => buddydrive_get_folder_post_type(),
			'user_id'          => $c,
			'title'            => 'directory',
			'privacy'          => 'groups',
			'groups'           => array( $g1 ),
		) );

		$f3 = buddydrive_add_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $c,
			'title'            => 'screenshot-1.png',
			'content'          => 'foo file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
			'parent_folder_id' => $folder_id,
		) );

		buddydrive_remove_buddyfiles_from_group( $g1 );

		$folder = buddydrive_get_buddyfile( $folder_id, buddydrive_get_folder_post_type() );
		$file1 = buddydrive_get_buddyfile( $f1 );
		$file2 = buddydrive_get_buddyfile( $f2 );
		$file3 = buddydrive_get_buddyfile( $f3 );

		$this->assertTrue( $file1->post_status === 'buddydrive_public' && $folder->post_status === $file3->post_status && $file3->post_status === 'buddydrive_public' );
		$this->assertTrue( empty( $folder->group ) && empty( $file1->group ) && empty( $file3->group ) );

		$this->assertEquals( array( $g2 ), $file2->group );
		$this->assertTrue( 'groups' === $file2->check_for );
		$this->assertTrue( 'buddydrive_groups' === get_post_status( $f2 ) );
	}

	/**
	 * @group buddydrive_count_user_files
	 */
	public function test_buddydrive_count_user_files() {
		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$meta = new stdClass();
		$meta->privacy = 'public';
		$expected_ids = array();

		$expected_ids['folder_puid'] = buddydrive_save_item( array(
			'type'  => buddydrive_get_folder_post_type(),
			'title' => 'foo',
			'content' => 'foo bar folder',
			'metas' => $meta
		) );

		$expected_ids['file_puid'] = buddydrive_save_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->user_id,
			'parent_folder_id' => $expected_ids['folder_puid'],
			'title'            => 'screenshot-1.png',
			'content'          => 'foo bar file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
			'metas'            => $meta,
		) );

		$meta->privacy = 'private';

		$expected_ids['file_prid'] = buddydrive_save_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->user_id,
			'title'            => 'screenshot-1.png',
			'content'          => 'foo bar file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-1.png',
			'metas'            => $meta,
		) );

		$this->set_current_user( 0 );
		$this->assertTrue( 1 === (int) buddydrive_count_user_files( $this->user_id ) );

		buddydrive()->__set( 'users_file_count', null );

		$this->set_current_user( $this->user_id );
		$this->assertTrue( 2 === (int) buddydrive_count_user_files( $this->user_id ) );
	}
}
