<?php
/**
 * BuddyDrive Profile
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The BuddyDrive Profile Class
 *
 * @package BuddyDrive
 * @since 3.0.0
 */
class BuddyDrive_Profile {

	/**
	 * Construct method to add some settings and hooks
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function __construct() {
		add_action( 'bp_setup_nav', array( $this, 'buddydrive_profile_tab' ), 100 );
	}

	/**
	 * Create nav and subnav items.
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function buddydrive_profile_tab() {
		global $bp;

		bp_core_new_nav_item( array(
			'name' => 'BuddyDrive',
			'slug' => 'buddydrive',
			'parent_url'      => $bp->displayed_user->domain,
			'parent_slug'     => $bp->profile->slug,
			'screen_function' => array( $this, 'buddydrive_profile_screen' ),
			'default_subnav_slug' => 'buddydrive_files',
			'position' => 70,
		) );

		bp_core_new_subnav_item( array(
			'name'              => 'BuddyDrive Files',
			'slug'              => 'buddydrive_files',
			'parent_url'        => trailingslashit( bp_displayed_user_domain() . 'buddydrive' ),
			'parent_slug'       => 'buddydrive',
			'screen_function'   => array( $this, 'buddydrive_profile_files_screen' ),
			'position'          => 100,
			'user_has_access'   => bp_is_my_profile()
		) );

		bp_core_new_subnav_item( array(
			'name'              => 'Between Members',
			'slug'              => 'between_members',
			'parent_url'        => trailingslashit( bp_displayed_user_domain() . 'buddydrive' ),
			'parent_slug'       => 'buddydrive',
			'screen_function'   => array( $this, 'buddydrive_profile_between_members_screen' ),
			'position'          => 150,
			'user_has_access'   => bp_is_my_profile()
		) );

	}

	/**
	 * Setup the Profile Screen
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function buddydrive_profile_screen() {
		add_filter( 'bp_template_title', array( $this, 'buddydrive_profile_title' ) );
		add_action( 'bp_template_content', array( $this, 'buddydrive_profile_content' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Setup the Profile Screen Title
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function buddydrive_profile_title() {
		echo 'BuddyDrive';
	}

	/**
	 * Setup the Profile Screen Content
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function buddydrive_profile_content() {
		echo 'Content';
	}

	/**
	 * Setup the Profile Files Screen
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function buddydrive_profile_files_screen() {
		add_filter( 'bp_template_title', array( $this, 'buddydrive_profile_files_title' ) );
		add_action( 'bp_template_content', array( $this, 'buddydrive_profile_files_content' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Setup the Profile Files Screen Title
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function buddydrive_profile_files_title() {
		echo 'BuddyDrive';
	}

	/**
	 * Setup the Profile Files Screen Content
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function buddydrive_profile_files_content() {
		echo 'Content';
	}

	/**
	 * Setup the Profile Between Members Screen
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function buddydrive_profile_between_members_screen() {
		add_filter( 'bp_template_title', array( $this, 'buddydrive_profile_between_members_title' ) );
		add_action( 'bp_template_content', array( $this, 'buddydrive_profile_between_members_content' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Setup the Profile Between Members Screen Title
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function buddydrive_profile_between_members_title() {
		echo 'BuddyDrive';
	}

	/**
	 * Setup the Profile Between Members Screen Content
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function buddydrive_profile_between_members_content() {
		echo 'Content';
	}
}

$buddydrive_profile = new BuddyDrive_Profile();
