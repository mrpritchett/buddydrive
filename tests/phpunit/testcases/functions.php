<?php
/**
 * @group functions
 */
class BuddyDrive_Functions_Tests extends BuddyDrive_TestCase {

	public function setUp() {
		parent::setUp();

		$this->upload_data = bp_upload_dir();
	}

	public function tearDown() {
		parent::tearDown();

		unset( $this->upload_data );
	}

	/**
	 * @group upload
	 */
	public function test_buddydrive_get_upload_data() {

		$expected = array(
			'dir' => trailingslashit( $this->upload_data['basedir'] ) . 'buddydrive',
			'url' => trailingslashit( $this->upload_data['baseurl'] ) . 'buddydrive'
		);

		$this->assertSame( $expected, array_intersect_key( buddydrive_get_upload_data(), array( 'dir' => true, 'url' => true ) ) );
	}

	public function filter_upload_dir( $upload_data = array() ) {
		return array(
			'dir' => trailingslashit( $this->upload_data['basedir'] ) . 'test_buddydrive',
			'url' => trailingslashit( $this->upload_data['baseurl'] ) . 'test_buddydrive'
		);
	}

	/**
	 * @group filters
	 * @group upload
	 */
	public function test_buddydrive_get_upload_data_change_dir() {
		$expected = $this->filter_upload_dir();

		add_filter( 'buddydrive_get_upload_data', array( $this, 'filter_upload_dir' ), 10, 1 );
		$tested = buddydrive_get_upload_data();
		remove_filter( 'buddydrive_get_upload_data', array( $this, 'filter_upload_dir' ), 10, 1 );

		$this->assertSame( $expected, $tested );
	}

	public function filter_error_strings_ko( $errors = array() ) {
		return array(
			0  => 'foo',
			9  => 'bar',
		);
	}

	public function filter_error_strings_ok( $errors = array() ) {
		return array(
			12 => 'taz',
		);
	}

	/**
	 * @group filters
	 * @group upload
	 */
	public function test_buddydrive_get_upload_error_strings() {
		$expected = buddydrive_get_upload_error_strings();

		add_filter( 'buddydrive_get_upload_error_strings', array( $this, 'filter_error_strings_ko' ), 10, 1 );
		$tested = buddydrive_get_upload_error_strings();
		remove_filter( 'buddydrive_get_upload_error_strings', array( $this, 'filter_error_strings_ko' ), 10, 1 );

		$this->assertSame( $expected, $tested );

		$expected[12] = 'taz';

		add_filter( 'buddydrive_get_upload_error_strings', array( $this, 'filter_error_strings_ok' ), 10, 1 );
		$tested = buddydrive_get_upload_error_strings();
		remove_filter( 'buddydrive_get_upload_error_strings', array( $this, 'filter_error_strings_ok' ), 10, 1 );

		$this->assertSame( $expected, $tested );
	}

	/**
	 * @group upgrade
	 */
	public function test_buddydrive_update_items_status() {
		global $wpdb;

		$this->factory->post->create_many( 10, array( 'post_type' => buddydrive_get_file_post_type() ) );
		$this->factory->post->create_many( 10, array( 'post_type' => buddydrive_get_folder_post_type() ) );

		$items = get_posts( array( 'numberposts' => -1, 'post_type' => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ) ) );
		$privacy = array();

		$i = 0;
		foreach ( $items as $item ) {
			if ( $i <= 5 ) {
				$meta_value = 'public';
			}

			if ( $i > 5 ) {
				$meta_value = 'private';
			}

			if ( $i > 9 ) {
				$meta_value = 'groups';
			}

			if ( $i > 14 ) {
				$meta_value = 'friends';
			}

			if ( $i > 17 ) {
				$meta_value = 'password';
			}

			$privacy[ $meta_value ][] = $item->ID;
			update_post_meta( $item->ID, '_buddydrive_sharing_option', $meta_value );

			$i += 1;
		}

		$updated  = buddydrive_update_items_status( 10 );
		$updated += buddydrive_update_items_status( 10 );

		$status = array(
			'public'   => wp_list_pluck( get_posts( array( 'numberposts' => -1, 'post_status' => 'buddydrive_public', 'post_type' => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ) ) ), 'ID' ),
			'private'  => wp_list_pluck( get_posts( array( 'numberposts' => -1, 'post_status' => 'buddydrive_private', 'post_type' => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ) ) ), 'ID' ),
			'groups'   => wp_list_pluck( get_posts( array( 'numberposts' => -1, 'post_status' => 'buddydrive_groups', 'post_type' => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ) ) ), 'ID' ),
			'friends'  => wp_list_pluck( get_posts( array( 'numberposts' => -1, 'post_status' => 'buddydrive_friends', 'post_type' => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ) ) ), 'ID' ),
			'password' => wp_list_pluck( get_posts( array( 'numberposts' => -1, 'post_status' => 'buddydrive_password', 'post_type' => array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ) ) ), 'ID' ),
		);

		$this->assertEquals( $privacy, $status );
		$this->assertEquals( $updated, 20 );
	}
}
