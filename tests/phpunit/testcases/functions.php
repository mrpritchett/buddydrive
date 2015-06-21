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
}
