<?php
/**
 * @group item_classes
 */
class BuddyDrive_Item_Classes_Tests extends BuddyDrive_TestCase {

	public function setUp() {
		parent::setUp();

		$this->current_user = get_current_user_id();
		$this->user_id      = $this->factory->user->create();
		$this->set_current_user( $this->user_id );
		$this->expected_ids = array();
		$this->create_files();
	}

	public function create_files() {
		$args = array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->user_id,
			'title'            => 'screenshot-1.png',
			'content'          => 'foo file',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( buddydrive()->upload_url ) . 'screenshot-1.png',
		);

		$this->expected_ids['foo'] = buddydrive_save_item( $args );

		$args = array_merge( $args, array(
			'title'     => 'readme.txt',
			'content'   => 'bar file',
			'mime_type' => 'text/plain',
			'guid'      => trailingslashit( buddydrive()->upload_url ) . 'readme.txt',
		) );

		$this->expected_ids['bar'] = buddydrive_save_item( $args );
	}

	public function set_displayed_user_id( $user_id = 0 ) {
		return $this->displayed_user_id;
	}

	public function tearDown() {
		parent::tearDown();

		$this->set_current_user( $this->current_user );
	}

	/**
	 * @group get
	 */
	public function test_buddydrive_item_get_by_id() {
		$by_id = new BuddyDrive_Item();

		// Get by ID
		$by_id->get( array(
			'id'   => $this->expected_ids['foo'],
			'type' => buddydrive_get_file_post_type(),
		) );

		$this->assertTrue( (int) $by_id->query->found_posts === 1 );

		$file = wp_list_pluck( $by_id->query->posts, 'ID' );
		$this->assertTrue( $this->expected_ids['foo'] === (int) $file[0] );
	}

	/**
	 * @group get
	 */
	public function test_buddydrive_item_get_by_name() {
		$by_name = new BuddyDrive_Item();

		// Get by name
		$by_name->get( array(
			'name'   => 'readme-txt',
			'type'   => buddydrive_get_file_post_type(),
		) );

		$this->assertTrue( (int) $by_name->query->found_posts === 1 );

		$file = wp_list_pluck( $by_name->query->posts, 'ID' );
		$this->assertTrue( $this->expected_ids['bar'] === (int) $file[0] );
	}

	/**
	 * @group get
	 */
	public function test_buddydrive_item_get_by_user_id() {
		$user_id = $this->factory->user->create();
		$file_object = buddydrive_get_buddyfile( $this->expected_ids['foo'] );

		buddydrive_update_item( array(
			'user_id' => $user_id,
		), $file_object );

		add_filter( 'bp_current_user_can', '__return_true' );

		$by_this_user_id = new BuddyDrive_Item();

		// Get by name
		$by_this_user_id->get( array(
			'user_id'           => $this->user_id,
			'type'              => buddydrive_get_file_post_type(),
			'buddydrive_scope'  => 'admin',
		) );

		$this->assertTrue( (int) $by_this_user_id->query->found_posts === 1 );

		$file = wp_list_pluck( $by_this_user_id->query->posts, 'ID' );
		$this->assertTrue( $this->expected_ids['bar'] === (int) $file[0] );

		$by_user_id = new BuddyDrive_Item();

		// Get by name
		$by_user_id->get( array(
			'user_id'           => $user_id,
			'type'              => buddydrive_get_file_post_type(),
			'buddydrive_scope'  => 'admin',
		) );

		remove_filter( 'bp_current_user_can', '__return_true' );

		$file = wp_list_pluck( $by_user_id->query->posts, 'ID' );
		$this->assertTrue( $this->expected_ids['foo'] === (int) $file[0] );
	}

	/**
	 * @group get
	 * @group scope
	 */
	public function test_buddydrive_item_get_by_scope() {
		$u2 = $this->factory->user->create();

		// Admin
		$this->set_current_user( 1 );

		$by_scope = new BuddyDrive_Item();

		// Get by scope
		$by_scope->get( array(
			'type'              => buddydrive_get_file_post_type(),
			'buddydrive_scope'  => 'admin',
		) );

		// Admin should see everything
		$this->assertTrue( (int) $by_scope->query->found_posts === 2 );

		// Update the privacy of the file
		$file_object = buddydrive_get_buddyfile( $this->expected_ids['foo'] );

		buddydrive_update_item( array(
			'privacy' => 'public',
		), $file_object );

		// Any user
		$this->set_current_user( $u2 );
		$this->displayed_user_id = $this->user_id;

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$by_scope = new BuddyDrive_Item();

		// Get by scope
		$by_scope->get( array(
			'type'              => buddydrive_get_file_post_type(),
			'buddydrive_scope'  => 'files',
		) );

		$file = wp_list_pluck( $by_scope->query->posts, 'ID' );
		$this->assertTrue( $this->expected_ids['foo'] === (int) $file[0], 'only public files should be listed' );

		// The owner
		$this->set_current_user( $this->user_id );

		$by_scope = new BuddyDrive_Item();

		// Get by scope
		$by_scope->get( array(
			'type'              => buddydrive_get_file_post_type(),
			'buddydrive_scope'  => 'files',
		) );

		// Owner should see everything
		$this->assertTrue( (int) $by_scope->query->found_posts === 2 );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		// Any user
		$this->set_current_user( $u2 );

		// Update the privacy and owner of the file
		$file_object = buddydrive_get_buddyfile( $this->expected_ids['bar'] );

		buddydrive_update_item( array(
			'privacy' => 'public',
			'user_id' => $u2,
		), $file_object );

		$by_scope = new BuddyDrive_Item();

		// Get by scope
		$by_scope->get( array(
			'type'              => buddydrive_get_file_post_type(),
			'buddydrive_scope'  => 'public',
		) );

		// Custom loops should be able to list all public files
		$this->assertTrue( (int) $by_scope->query->found_posts === 2 );

		buddydrive_update_item( array(
			'privacy' => 'private',
		), $file_object );

		$by_scope = new BuddyDrive_Item();

		// Get by scope
		$by_scope->get( array(
			'type'              => buddydrive_get_file_post_type(),
			'buddydrive_scope'  => 'public',
		) );

		// Custom loops should be able to list all public files
		$this->assertTrue( (int) $by_scope->query->found_posts === 1 );
	}

	/**
	 * @group get
	 * @group groups
	 */
	public function test_buddydrive_item_get_by_scope_groups() {
		$c = $this->factory->user->create();
		$g = array(
			$this->factory->group->create( array( 'creator_id' => $c ) ),
			$this->factory->group->create( array( 'status' => 'hidden' ) ),
			$this->factory->group->create( array( 'status' => 'private', 'creator_id' => $c  ) ),
		);

		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$files = array();

		for ( $i = 0 ; $i < 10 ; $i++ ) {
			$meta           = new stdClass();
			$meta->privacy  = 'private';
			$meta->groups   = 0;
			$u = $c;

			if ( in_array( $i, array( 0, 4, 8 ) ) ) {
				$meta->privacy = 'public';
			} elseif ( in_array( $i, array( 1, 3 ) ) ) {
				$meta->privacy = 'groups';
				$meta->groups  = $g[ $i - 1 ];
			} elseif ( $i === 2 ) {
				$meta->privacy = 'groups';
				$meta->groups  = $g[ $i - 1 ];
				$u = $this->user_id;
			} elseif ( $i === 5 ) {
				$meta->privacy   = 'password';
				$meta->password  = 'password';
				$u               = $this->user_id;
			} elseif ( in_array( $i, array( 6, 7 ) ) ) {
				$meta->privacy   = 'members';
			}

			$files[ $i ] = buddydrive_save_item( array(
				'type'             => buddydrive_get_file_post_type(),
				'user_id'          => $u,
				'title'            => 'screenshot-' . $i . '.png',
				'mime_type'        => 'image/png',
				'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-' . $i . '.png',
				'metas'            => $meta,
			) );
		}

		$buddydrive_items = new BuddyDrive_Item();

		$user_id_viewables = array( $files[0], $files[1], $files[4], $files[6], $files[7], $files[8] );

		$this->displayed_user_id = $c;

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope' => 'files',
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEquals( wp_list_pluck( $buddydrive_items->query->posts, 'ID' ), $user_id_viewables );

		$this->set_current_user( $c );
		$c_viewables = array( $files[0], $files[1], $files[3], $files[4], $files[6], $files[7], $files[8], $files[9] );

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items = new BuddyDrive_Item();

		$buddydrive_items->get( array(
			'buddydrive_scope' => 'files',
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEquals( wp_list_pluck( $buddydrive_items->query->posts, 'ID' ), $c_viewables );

		$this->displayed_user_id = $this->user_id;
		groups_join_group( $g[1], $c );
		$c_viewables = array( $files[2], $files[5] );

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items = new BuddyDrive_Item();

		$buddydrive_items->get( array(
			'buddydrive_scope' => 'files',
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEquals( wp_list_pluck( $buddydrive_items->query->posts, 'ID' ), $c_viewables );
	}

	/**
	 * @group get
	 * @group parent
	 */
	public function test_buddydrive_item_get_by_parent_for_groups() {
		$c = $this->factory->user->create();
		$g = $this->factory->group->create( array( 'status' => 'hidden', 'creator_id' => $c ) );

		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$folder = buddydrive_add_item( array(
			'type'             => buddydrive_get_folder_post_type(),
			'user_id'          => $c,
			'title'            => 'folder',
			'privacy'          => 'groups',
			'groups'           => array( $g ),
		) );

		$file = buddydrive_add_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $c,
			'title'            => 'screenshot-2.png',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-2.png',
			'parent_folder_id' => $folder,
		) );

		$this->displayed_user_id = $c;

		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope' => 'files',
			'type'             => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEmpty( $buddydrive_items->query->posts );

		$buddydrive_items = new BuddyDrive_Item();

		$buddydrive_items->get( array(
			'buddydrive_scope' => 'groups',
			'type'             => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'group_id'          => $g,
		) );

		$this->assertEmpty( $buddydrive_items->query->posts );

		groups_join_group( $g, $this->user_id );

		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope' => 'files',
			'type'             => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEquals( wp_list_pluck( $buddydrive_items->query->posts, 'ID' ), array( $folder ) );

		$buddydrive_items = new BuddyDrive_Item();

		$buddydrive_items->get( array(
			'buddydrive_scope' => 'groups',
			'type'             => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'group_id'          => $g,
		) );

		$this->assertEquals( wp_list_pluck( $buddydrive_items->query->posts, 'ID' ), array( $folder ) );

		// Open folder in group
		$buddydrive_items = new BuddyDrive_Item();

		$buddydrive_items->get( array(
			'buddydrive_scope'  => 'groups',
			'buddydrive_parent' => $folder,
			'type'              => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'group_id'          => $g,
		) );

		$this->assertEquals( wp_list_pluck( $buddydrive_items->query->posts, 'ID' ), array( $file ) );

		// Open folder in user
		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope'  => 'files',
			'buddydrive_parent' => $folder,
			'type'              => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEquals( wp_list_pluck( $buddydrive_items->query->posts, 'ID' ), array( $file ) );

		groups_leave_group( $g, $this->user_id );

		// Open folder in group
		$buddydrive_items = new BuddyDrive_Item();

		$buddydrive_items->get( array(
			'buddydrive_scope'  => 'groups',
			'buddydrive_parent' => $folder,
			'type'              => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'group_id'          => $g,
		) );

		$this->assertEmpty( $buddydrive_items->query->posts );

		// Open folder in user
		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope'  => 'files',
			'buddydrive_parent' => $folder,
			'type'              => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEmpty( $buddydrive_items->query->posts );
	}

	/**
	 * @group get
	 * @group parent
	 */
	public function test_buddydrive_item_get_by_parent_for_private() {
		$c = $this->factory->user->create();

		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$folder = buddydrive_add_item( array(
			'type'             => buddydrive_get_folder_post_type(),
			'user_id'          => $c,
			'title'            => 'folder',
			'privacy'          => 'private',
		) );

		$file = buddydrive_add_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $c,
			'title'            => 'screenshot-2.png',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-2.png',
			'parent_folder_id' => $folder,
		) );

		$this->displayed_user_id = $c;

		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope' => 'files',
			'type'             => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEmpty( $buddydrive_items->query->posts );

		// Open folder in user
		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope'  => 'files',
			'buddydrive_parent' => $folder,
			'type'              => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEmpty( $buddydrive_items->query->posts );
	}

	/**
	 * @group get
	 * @group parent
	 */
	public function test_buddydrive_item_get_by_parent_for_public() {
		$c = $this->factory->user->create();

		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$folder = buddydrive_add_item( array(
			'type'             => buddydrive_get_folder_post_type(),
			'user_id'          => $c,
			'title'            => 'folder',
			'privacy'          => 'public',
		) );

		$file = buddydrive_add_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $c,
			'title'            => 'screenshot-2.png',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-2.png',
			'parent_folder_id' => $folder,
		) );

		$this->displayed_user_id = $c;

		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope' => 'files',
			'type'             => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEquals( wp_list_pluck( $buddydrive_items->query->posts, 'ID' ), array( $folder ) );

		// Open folder in user
		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope'  => 'files',
			'buddydrive_parent' => $folder,
			'type'              => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEquals( wp_list_pluck( $buddydrive_items->query->posts, 'ID' ), array( $file ) );
	}

	/**
	 * @group get
	 * @group parent
	 */
	public function test_buddydrive_item_get_by_parent_for_friends() {
		$c = $this->factory->user->create();

		// create the upload dir
		$upload_dir = buddydrive_get_upload_data();

		$folder = buddydrive_add_item( array(
			'type'             => buddydrive_get_folder_post_type(),
			'user_id'          => $c,
			'title'            => 'folder',
			'privacy'          => 'friends',
		) );

		$file = buddydrive_add_item( array(
			'type'             => buddydrive_get_file_post_type(),
			'user_id'          => $c,
			'title'            => 'screenshot-2.png',
			'mime_type'        => 'image/png',
			'guid'             => trailingslashit( $upload_dir['url'] ) . 'screenshot-2.png',
			'parent_folder_id' => $folder,
		) );

		$this->displayed_user_id = $c;

		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope' => 'files',
			'type'             => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEmpty( $buddydrive_items->query->posts );

		// Open folder in user
		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope'  => 'files',
			'buddydrive_parent' => $folder,
			'type'              => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEmpty( $buddydrive_items->query->posts );

		// They are now friends!
		friends_add_friend( $c, $this->user_id, true );

		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope' => 'files',
			'type'             => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEquals( wp_list_pluck( $buddydrive_items->query->posts, 'ID' ), array( $folder ) );

		// Open folder in user
		$buddydrive_items = new BuddyDrive_Item();

		add_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$buddydrive_items->get( array(
			'buddydrive_scope'  => 'files',
			'buddydrive_parent' => $folder,
			'type'              => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ),
			'user_id'          => $this->displayed_user_id,
		) );

		remove_filter( 'bp_displayed_user_id', array( $this, 'set_displayed_user_id' ), 10, 1 );

		$this->assertEquals( wp_list_pluck( $buddydrive_items->query->posts, 'ID' ), array( $file ) );
	}
}
