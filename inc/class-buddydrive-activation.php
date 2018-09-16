<?php
/**
 * BuddyDrive Activation
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The BuddyDrive Activation Class
 *
 * @package BuddyDrive
 * @since 3.0.0
 */
class BuddyDrive_Activation {

	/**
	 * Construct method to add some settings and hooks
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( 'BuddyDrive', 'buddydrive_on_activate' ) );
		register_deactivation_hook( __FILE__, array( 'BuddyDrive', 'buddydrive_on_deactivate' ) );
	}

	/**
	 * Create nav and subnav items.
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public static function buddydrive_on_activate() {

		// For network, as plugin is not yet activated, bail method won't help..
		if ( is_network_admin() && function_exists( 'buddypress' ) ) {
			$check = ! empty( $_REQUEST ) && 'activate' == $_REQUEST['action'] && $_REQUEST['plugin'] == buddydrive()->basename && bp_is_network_activated() && buddydrive::version_check();
		} else {
			$check = ! buddydrive::bail();
		}

		if ( empty( $check ) ) {
			return;
		}

		$directory_pages = bp_core_get_directory_page_ids();
		$buddydrive_slug = buddydrive_get_slug();

		if ( empty( $directory_pages[ $buddydrive_slug ] ) ) {
			// let's create a page and add it to BuddyPress directory pages
			$buddydrive_page_content = __( 'BuddyDrive uses this page to manage the downloads of your buddies files, please leave it as is. It will not show in your navigation bar.', 'buddydrive');

			$buddydrive_page_id = wp_insert_post( array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_title'     => buddydrive_get_name(),
				'post_content'   => $buddydrive_page_content,
				'post_name'      => $buddydrive_slug,
				'post_status'    => 'publish',
				'post_type'      => 'page'
			) );

			$directory_pages[ $buddydrive_slug ] = $buddydrive_page_id;
			bp_core_update_directory_page_ids( $directory_pages );
		}

		do_action( 'buddydrive_activation' );
	}


	/**
	 * Setup the Profile Screen
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public static function buddydrive_on_deactivate() {
		wp_delete_post( get_option( 'apollo_styleguide_post_id' ) );
		delete_option( 'apollo_styleguide_post_id' );
	}
}

$buddydrive_activation = new BuddyDrive_Activation();
