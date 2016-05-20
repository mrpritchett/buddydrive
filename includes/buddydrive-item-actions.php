<?php
/**
 * BuddyDrive Item actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register css and script to be used by the BuddyDrive Editor
 *
 * @since 1.3.0
 */
function buddydrive_register_public_file_scripts() {
	$min = '.min';
	if ( defined( 'SCRIPT_DEBUG' ) && true == SCRIPT_DEBUG )  {
		$min = '';
	}

	/**
	 * Filter here if you wish to override and adapt the Editor css to your needs
	 *
	 * @param array {
	 *   $file url to the css of the Editor
	 *   $deps an array of your dependencies if needed, an empty array otherwise
	 * }
	 */
	$css = apply_filters( 'buddydrive_register_public_file_css', array(
		'file' => buddydrive_get_includes_url() . "css/buddydrive-public{$min}.css",
		'deps' => array( 'dashicons' ),
	) );

	// Register the style
	wp_register_style(
		'buddydrive-public-style',
		$css['file'],
		$css['deps'],
		buddydrive_get_version()
	);

	// Register the script
	wp_register_script(
		'buddydrive-public-js',
		buddydrive_get_includes_url() . "js/buddydrive-public{$min}.js",
		array(),
		buddydrive_get_version(),
		true
	);

	if ( ! buddydrive_use_deprecated_ui() ) {
		buddydrive_register_ui_cssjs();
	}
}
add_action( 'buddydrive_register_scripts', 'buddydrive_register_public_file_scripts' );

/**
 * Resets WordPress post data
 *
 * @uses wp_reset_postdata()
 */
function buddydrive_reset_post_data() {
	wp_reset_postdata();
}
add_action( 'buddydrive_after_loop', 'buddydrive_reset_post_data', 1 );

/**
 * Manages file downloads based on the privacy of the file/folder
 *
 * @since  1.0.0
 *
 * @return binary the file! (or redirects to the folder)
 */
function buddydrive_file_downloader() {

	if ( ! bp_displayed_user_id() && bp_is_current_component( 'buddydrive' ) && 'file' == bp_current_action() ) {

		$redirect = esc_url( wp_get_referer() );

		$buddyfile_name = bp_action_variable( 0 );

		$buddydrive_file = buddydrive_get_buddyfile( $buddyfile_name );

		if ( empty( $buddydrive_file ) ) {
			bp_core_add_message( __( 'OOps, we could not find your file.', 'buddydrive' ), 'error' );
			bp_core_redirect( buddydrive_get_root_url() );
		}

		$buddydrive_file_path = $buddydrive_file->path;
		$buddydrive_file_name = $buddydrive_file->file;
		$buddydrive_file_mime = $buddydrive_file->mime_type;

		// if the file belongs to a folder, we need to get the folder's privacy settings
		if ( ! empty( $buddydrive_file->post_parent ) ){
			$parent = $buddydrive_file->post_parent;

			$buddydrive_file = buddydrive_get_buddyfile( $parent, buddydrive_get_folder_post_type() );
		}

		// Attach the submitted password to the file if submitted
		if ( isset( $_POST['buddyfile-form']['password'] ) ) {
			$buddydrive_file->pass_submitted = $_POST['buddyfile-form']['password'];
		}

		$can_download = buddydrive_check_download( $buddydrive_file, bp_loggedin_user_id() );

		// Oops...
		if ( is_wp_error( $can_download ) ) {
			if ( 401 === $can_download->get_error_data() ) {
				wp_die( __( 'You are not allowed to download this file.', 'buddydrive' ) , __( 'Unauthorized access', 'buddydrive' ), 401 );
			} elseif ( 403 === $can_download->get_error_data() ) {
				add_action( 'buddydrive_directory_content', 'buddydrive_file_password_form' );
			} else {
				bp_core_add_message( $can_download->get_error_message(), 'error' );
				bp_core_redirect( $can_download->get_error_data() );
			}

		// Download the file !
		} else {
			// we have a file! let's force download.
			if ( file_exists( $buddydrive_file_path ) && true === $can_download ){
				do_action( 'buddydrive_file_downloaded', $buddydrive_file );
				status_header( 200 );
				header( 'Cache-Control: cache, must-revalidate' );
				header( 'Pragma: public' );
				header( 'Content-Description: File Transfer' );
				header( 'Content-Length: ' . filesize( $buddydrive_file_path ) );
				header( 'Content-Disposition: attachment; filename='.$buddydrive_file_name );
				header( 'Content-Type: ' .$buddydrive_file_mime );
				/**
				 * Files larger than the WordPress memory limit will cause out of memory errors
				 * if output buffering is on.  Check to see if the ob level is > 0 and if it is
				 * we send the buffer and then end ob.  We do not restart ob, since we die() after.
				 */
				while ( ob_get_level() > 0 ) {
					ob_end_flush();
				}

				readfile( $buddydrive_file_path );
				die();
			}
		}

	} else if ( ! bp_displayed_user_id() && bp_is_current_component( 'buddydrive' ) && 'folder' == bp_current_action() ) {

		$buddyfolder_name = bp_action_variable( 0 );

		$buddyfolder = buddydrive_get_buddyfile( $buddyfolder_name, buddydrive_get_folder_post_type() );

		if ( empty( $buddyfolder ) ) {
			bp_core_add_message( __( 'OOps, we could not find your folder.', 'buddydrive' ), 'error' );
			bp_core_redirect( buddydrive_get_root_url() );
		}

		// in case of the folder, we open it on the user's BuddyDrive or the group one
		if ( 'groups' === $buddyfolder->check_for )  {
			$buddydrive_root_link = buddydrive_get_group_buddydrive_url( $buddyfolder->group, $buddyfolder->user_id );
		} else {
			$buddydrive_root_link = buddydrive_get_user_buddydrive_url( $buddyfolder->user_id );
		}

		if ( buddydrive_use_deprecated_ui() ) {
			$link = $buddydrive_root_link . '?folder-' . $buddyfolder->ID;
		} else {
			// On new UI friends or members can add files to folders they don't own
			if ( is_user_logged_in() ) {
				if ( 'friends' === $buddyfolder->check_for && bp_is_active( 'friends' ) && friends_check_friendship( $buddyfolder->user_id, bp_loggedin_user_id() ) ) {
					$buddydrive_root_link = trailingslashit( buddydrive_get_user_buddydrive_url( bp_loggedin_user_id() ) . buddydrive_get_friends_subnav_slug() );
				} elseif ( 'members' === $buddyfolder->check_for && in_array( bp_loggedin_user_id(), $buddyfolder->members ) ) {
					$buddydrive_root_link = trailingslashit( buddydrive_get_user_buddydrive_url( bp_loggedin_user_id() ) . 'members' );
				}
			}

			$link = trailingslashit( $buddydrive_root_link ) . '#view/' . $buddyfolder->ID;
		}

		bp_core_redirect( $link );
	}
}
add_action( 'buddydrive_actions', 'buddydrive_file_downloader', 1 );

/**
 * Update the privacy of children files linked to a folder if updated
 *
 * @param  array $params associtive array of parameters
 * @param  array $args
 * @param  object $item the folder object
 * @uses buddydrive_get_folder_post_type() to check it's a folder
 * @uses BuddyDrive_Item::update_children() to update the files
 */
function buddydrive_update_children( $params, $args, $item ) {
	if ( $item->post_type != buddydrive_get_folder_post_type() ) {
		return;
	}

	$parent_id = (int) $params['id'];
	$metas = $params['metas'];

	/**
	 * This should be improved...
	 */
	$buddydrive_update_children = new BuddyDrive_Item();
	$buddydrive_update_children->update_children( $parent_id, $metas, $item->user_id );

}
add_action( 'buddydrive_update_item', 'buddydrive_update_children', 1, 3 ) ;

/**
 * Removes Buddyfiles, BuddyFolders and files of a deleted user
 *
 * @param  int $user_id the id of the deleted user
 * @uses buddydrive_delete_item() to remove user's BuddyDrive content
 */
function buddydrive_remove_user( $user_id ) {
	buddydrive_delete_item( array( 'user_id' => $user_id ) );
}
add_action( 'wpmu_delete_user',  'buddydrive_remove_user', 11, 1 );
add_action( 'delete_user',       'buddydrive_remove_user', 11, 1 );
add_action( 'bp_make_spam_user', 'buddydrive_remove_user', 11, 1 );

/**
 * Hooks the create complete group action to eventually enable BuddyDrive for the group just created
 *
 * @since version 1.1
 *
 * @param  integer $group_id the group id
 * @uses bp_get_option() to get blog's preference
 * @uses groups_update_groupmeta() to save a new meta for the group
 */
function buddydrive_maybe_enable_group( $group_id = 0 ) {
	if ( empty( $group_id ) )
		return;

	$enable_group = bp_get_option( '_buddydrive_auto_group', 0 );

	if ( ! empty( $enable_group ) ) {
		groups_update_groupmeta( $group_id, '_buddydrive_enabled', 1 );
	}
}
add_action( 'groups_group_create_complete', 'buddydrive_maybe_enable_group', 10, 1 );
