<?php
/**
 * BuddyDrive functions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * What is the version in db ?
 *
 * @uses get_option() to get the BuddyDrive version
 * @return string the version
 */
function buddydrive_get_db_version(){
	return get_option( '_buddydrive_version' );
}

function buddydrive_get_db_number_version() {
	return get_option( '_buddydrive_db_version', 0 );
}

/**
 * What is the version of the plugin.
 *
 * @uses buddydrive()
 * @return string the version of the plugin
 */
function buddydrive_get_version() {
	return buddydrive()->version;
}

function buddydrive_get_number_version() {
	return buddydrive()->db_version;
}

/**
 * Is it the first install ?
 *
 * @uses get_option() to get the BuddyDrive version
 * @return boolean true or false
 */
function buddydrive_is_install() {
	$buddydrive_version = get_option( '_buddydrive_version', '' );

	if ( empty( $buddydrive_version ) ) {
		return true;
	}

	return false;
}

/**
 * Do we need to eventually update ?
 *
 * @uses get_option() to get the BuddyDrive version
 * @return boolean true or false
 */
function buddydrive_is_update() {
	$buddydrive_version = get_option( '_buddydrive_version', '' );

	if ( ! empty( $buddydrive_version ) && version_compare( $buddydrive_version, buddydrive_get_version(), '<' ) ) {
		return true;
	}

	return false;
}

/**
 * displays the slug of the plugin
 *
 * @uses buddydrive_get_slug() to get it!
 */
function buddydrive_slug() {
	echo buddydrive_get_slug();
}

	/**
	 * Gets the slug of the plugin
	 *
	 * @uses buddydrive() to get plugin's globals
	 * @uses buddypress() to get directory pages global settings
	 * @return string the slug
	 */
	function buddydrive_get_slug() {
		$slug = isset( buddypress()->pages->buddydrive->slug ) ? buddypress()->pages->buddydrive->slug : buddydrive()->buddydrive_slug ;

		return apply_filters( 'buddydrive_get_slug', $slug );
	}

/**
 * displays the name of the plugin
 *
 * @uses buddydrive_get_name() to get it!
 */
function buddydrive_name() {
	echo buddydrive_get_name();
}

	/**
	 * Gets the name of the plugin
	 *
	 * @uses buddydrive() to get plugin's globals
	 * @uses buddypress() to get directory pages global settings
	 * @return string the name
	 */
	function buddydrive_get_name() {
		$name = isset( buddypress()->pages->buddydrive->slug ) ? buddypress()->pages->buddydrive->title : buddydrive()->buddydrive_name ;

		return apply_filters( 'buddydrive_get_name', $name );
	}

/**
 * Prints the user main subnav
 *
 * @since  BuddyDrive 1.1
 *
 * @uses buddydrive_get_user_subnav_name() to get the user's subnav name
 * @return string the subnav name
 */
function buddydrive_user_subnav_name() {
	echo buddydrive_get_user_subnav_name();
}

	/**
	 * Returns the BuddyDrive user's subnav name
	 *
	 * @since  BuddyDrive 1.1
	 *
	 * @uses bp_get_option() to get root blog preferences
	 * @return string the subnav name
	 */
	function buddydrive_get_user_subnav_name() {
		$user_subnav = bp_get_option( '_buddydrive_user_subnav_name', __( 'BuddyDrive Files', 'buddydrive' ) );

		return apply_filters( 'buddydrive_get_user_subnav_name', $user_subnav );
	}

/**
 * Prints the friends subnav
 *
 * @since  BuddyDrive 1.1
 *
 * @uses buddydrive_get_friends_subnav_name() to get the friends subnav name
 * @return string the subnav name
 */
function buddydrive_friends_subnav_name() {
	echo buddydrive_get_friends_subnav_name();
}

	/**
	 * Returns the BuddyDrive friends subnav name
	 *
	 * @since  BuddyDrive 1.1
	 *
	 * @uses bp_get_option() to get root blog preferences
	 * @return string the subnav name
	 */
	function buddydrive_get_friends_subnav_name() {
		$friends_subnav = bp_get_option( '_buddydrive_friends_subnav_name', __( 'Between Friends', 'buddydrive' ) );

		return apply_filters( 'buddydrive_get_friends_subnav_name', $friends_subnav );
	}

/**
 * Prints the friends slug
 *
 * @since  BuddyDrive 1.1
 *
 * @uses buddydrive_get_friends_subnav_slug() to get the friends subnav slug
 * @return string the subnav slug
 */
function buddydrive_friends_subnav_slug() {
	echo buddydrive_get_friends_subnav_slug();
}

	/**
	 * Returns the BuddyDrive friends subnav slug
	 *
	 * @since  BuddyDrive 1.1
	 *
	 * @uses bp_get_option() to get root blog preferences
	 * @return string the subnav slug
	 */
	function buddydrive_get_friends_subnav_slug() {
		$friends_slug = bp_get_option( '_buddydrive_friends_subnav_slug', 'friends' );

		return apply_filters( 'buddydrive_get_friends_subnav_slug', $friends_slug );
	}

/**
 * displays file post type of the plugin
 *
 * @uses buddydrive_get_file_post_type() to get it!
 */
function buddydrive_file_post_type() {
	echo buddydrive_get_file_post_type();
}

	/**
	 * Gets the file post type of the plugin
	 *
	 * @uses buddydrive()
	 * @return string the file post type
	 */
	function buddydrive_get_file_post_type() {
		return buddydrive()->buddydrive_file_post_type;
	}

/**
 * displays folder post type of the plugin
 *
 * @uses buddydrive_get_folder_post_type() to get it!
 */
function buddydrive_folder_post_type() {
	echo buddydrive_get_folder_post_type();
}

	/**
	 * Gets the folder post type of the plugin
	 *
	 * @uses buddydrive()
	 * @return string the folder post type
	 */
	function buddydrive_get_folder_post_type() {
		return buddydrive()->buddydrive_folder_post_type;
	}

/**
 * What is the path to the includes dir ?
 *
 * @uses  buddydrive()
 * @return string the path
 */
function buddydrive_get_includes_dir() {
	return buddydrive()->includes_dir;
}

/**
 * What is the path of the plugin dir ?
 *
 * @uses  buddydrive()
 * @return string the path
 */
function buddydrive_get_plugin_dir() {
	return buddydrive()->plugin_dir;
}

/**
 * What is the url to the plugin dir ?
 *
 * @uses  buddydrive()
 * @return string the url
 */
function buddydrive_get_plugin_url() {
	return buddydrive()->plugin_url;
}

/**
 * What is the url of includes dir ?
 *
 * @uses  buddydrive()
 * @return string the url
 */
function buddydrive_get_includes_url() {
	return buddydrive()->includes_url;
}

/**
 * What is the url to the images dir ?
 *
 * @uses  buddydrive()
 * @return string the url
 */
function buddydrive_get_images_url() {
	return buddydrive()->images_url;
}

/**
 * What is the root url for BuddyDrive ?
 *
 * @uses buddydrive_get_root_url() to get it
 */
function buddydrive_root_url() {
	echo buddydrive_get_root_url();
}

	/**
	 * Gets the root url for BuddyDrive
	 *
	 * @uses bp_get_root_domain() to get the root blog domain
	 * @uses buddydrive_get_slug() to get BuddyDrive Slug
	 * @return strin the url
	 */
	function buddydrive_get_root_url() {
		$root_domain_url = bp_get_root_domain();
		$buddydrive_slug = buddydrive_get_slug();
		$buddydrive_root_url = trailingslashit( $root_domain_url ) . $buddydrive_slug;
		return $buddydrive_root_url;
	}

/**
 * Builds an array for BuddyDrive upload data
 *
 * @uses BuddyDrive_Attachment() to get BuddyDrive basedir and baseurl
 * @return array
 */
function buddydrive_get_upload_data() {
	if ( ! class_exists( 'BuddyDrive_Attachment' ) ) {
		return false;
	}

	$buddydrive_attachment = new BuddyDrive_Attachment();
	return (array) $buddydrive_attachment->get_upload_data();
}

/**
 * Handles Plugin activation
 *
 * @uses bp_core_get_directory_page_ids() to get the BuddyPress component page ids
 * @uses buddydrive_get_slug() to get BuddyDrive slug
 * @uses wp_insert_post() to eventually create a new page for BuddyDrive
 * @uses buddydrive_get_name() to get BuddyDrive plugin name
 * @uses bp_core_update_directory_page_ids() to update the BuddyPres component pages ids
 */
function buddydrive_activation() {
	// For network, as plugin is not yet activated, bail method won't help..
	if ( is_network_admin() && function_exists( 'buddypress' ) ) {
		$check = ! empty( $_REQUEST ) && 'activate' == $_REQUEST['action'] && $_REQUEST['plugin'] == buddydrive()->basename && bp_is_network_activated() && buddydrive::version_check();
	} else {
		$check = ! buddydrive::bail();
	}

	if ( empty( $check ) )
		return;

	// let's check for BuddyDrive page in directory pages first !
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
 * Handles plugin deactivation
 *
 * @uses bp_core_get_directory_page_ids() to get the BuddyPress component page ids
 * @uses buddydrive_get_slug() to get BuddyDrive slug
 * @uses wp_delete_post() to eventually delete the BuddyDrive page
 * @uses bp_core_update_directory_page_ids() to update the BuddyPres component pages ids
 */
function buddydrive_deactivation() {
	// Bail if config does not match what we need
	if ( buddydrive::bail() )
		return;

	$directory_pages = bp_core_get_directory_page_ids();
	$buddydrive_slug = buddydrive_get_slug();

	if ( ! empty( $directory_pages[$buddydrive_slug] ) ) {
		// let's remove the page as the plugin is deactivated.

		$buddydrive_page_id = $directory_pages[$buddydrive_slug];
		wp_delete_post( $buddydrive_page_id, true );

		unset( $directory_pages[$buddydrive_slug] );
		bp_core_update_directory_page_ids( $directory_pages );
	}


	do_action( 'buddydrive_deactivation' );
}

/**
 * Welcome screen step one : set transient
 *
 * @uses buddydrive_is_install() to check of first install
 * @uses set_transient() to temporarly save some data to db
 */
function buddydrive_add_activation_redirect() {
	// Bail if activating from network, or bulk
	if ( isset( $_GET['activate-multi'] ) )
		return;

	// Record that this is a new installation, so we show the right
	// welcome message
	if ( buddydrive_is_install() ) {
		set_transient( '_buddydrive_is_new_install', true, 30 );
	}

	// Add the transient to redirect
	set_transient( '_buddydrive_activation_redirect', true, 30 );
}

/**
 * Welcome screen step two
 *
 * @uses get_transient()
 * @uses delete_transient()
 * @uses wp_safe_redirect to redirect to the Welcome screen
 * @uses add_query_arg() to add some arguments to the url
 * @uses bp_get_admin_url() to build the admin url
 */
function buddydrive_do_activation_redirect() {
	// Bail if no activation redirect
	if ( ! get_transient( '_buddydrive_activation_redirect' ) )
		return;

	// Delete the redirect transient
	delete_transient( '_buddydrive_activation_redirect' );

	// Bail if activating from network, or bulk
	if ( isset( $_GET['activate-multi'] ) ) {
		return;
	}

	$query_args = array( 'page' => 'buddydrive-about' );

	if ( get_transient( '_buddydrive_is_new_install' ) ) {
		$query_args['is_new_install'] = '1';
		delete_transient( '_buddydrive_is_new_install' );
	}

	// Redirect to BuddyDrive about page
	wp_safe_redirect( add_query_arg( $query_args, bp_get_admin_url( 'index.php' ) ) );
}


/**
 * Checks plugin version against db and updates
 *
 * @uses buddydrive_is_install() to see if first install
 * @uses buddydrive_get_db_version() to get db version
 * @uses buddydrive_get_version() to get BuddyDrive plugin version
 */
function buddydrive_check_version() {
	// Bail if config does not match what we need
	if ( buddydrive::bail() ) {
		return;
	}

	if ( version_compare( buddydrive_get_db_version(), buddydrive_get_version(), '=' ) ) {
		return;
	}

	if ( buddydrive_is_install() ) {
		// Set the DB Version
		update_option( '_buddydrive_db_version', buddydrive_get_number_version() );

	} else if ( buddydrive_is_update() ) {
		// Older versions had private as default privacy
		if ( 200 === buddydrive_get_number_version() ) {
			bp_add_option( '_buddydrive_default_privacy', 'buddydrive_private' );
		}
	}

	// Finally upgrade plugin version
	update_option( '_buddydrive_version', buddydrive_get_version() );
}
add_action( 'buddydrive_admin_init', 'buddydrive_check_version' );


/**
 * Returns the BuddyDrive Max upload size
 *
 * @param  boolean $bytes do we want it in bytes ?
 * @uses wp_max_upload_size() to get the config max upload size
 * @uses bp_get_option() to get the admin settings for BuddyDrive
 * @return int the max upload size
 */
function buddydrive_max_upload_size( $bytes = false ) {
	$max_upload = wp_max_upload_size();
	$max_upload_mo = $max_upload / 1024 / 1024;

	$buddydrive_max_upload  = bp_get_option( '_buddydrive_max_upload', $max_upload_mo );
	$buddydrive_max_upload = intval( $buddydrive_max_upload );

	if ( empty( $bytes ) )
		return $buddydrive_max_upload;
	else
		return $buddydrive_max_upload * 1024 * 1024;

}

/**
 * Tells if a value is checked in an array
 *
 * @param  string $value the value to check
 * @param  array $array where too check ?
 * @uses checked() to activate the checkbox
 * @return boolean|string (false or 'checked')
 */
function buddydrive_array_checked( $value = false, $array = false ) {

	if ( empty( $value ) || empty( $array ) )
		return false;

	$array = array_flip( $array );

	if ( in_array( $value, $array ) )
		return checked( true );

}

/**
 * What are the mime types allowed by admin ?
 *
 * @param  array $allowed_file_types WordPress default
 * @uses bp_get_option() to get the choice of the admin
 * @return array the mime types allowed by admin
 */
function buddydrive_allowed_file_types( $allowed_file_types ) {
	// Get allowed extensions
	$allowed_ext = buddydrive_get_allowed_upload_exts();

	if ( empty( $allowed_ext ) || ! is_array( $allowed_ext ) || count( $allowed_ext ) < 1 ) {
		return $allowed_file_types;
	}

	$allowed_ext = array_flip( $allowed_ext );
	$allowed_ext = array_intersect_key( $allowed_file_types, $allowed_ext );

	return $allowed_ext;
}

/**
 * Get allowed upload extensions
 *
 * @since 1.3
 *
 * @uses   buddydrive_allowed_file_types() to get the option defined by admin
 * @return array a list of allowed extensions.
 */
function buddydrive_get_allowed_upload_exts() {
	$bd_exts = bp_get_option( '_buddydrive_allowed_extensions', array() );
	return (array) apply_filters( 'buddydrive_get_allowed_upload_types', $bd_exts );
}

/**
 * Waits before checking if a 404 was a BuddyDrive file.
 *
 * @since  version 1.1
 *
 * @uses is_404() to check it's a 404
 * @uses bp_get_root_domain() to get the blog's url where BuddyPress is running
 * @uses esc_url() to sanitize url
 * @uses buddydrive() to get the BuddyDrive globals
 * @uses buddydrive_get_root_url() to get the plugin's root url
 * @uses bp_core_redirect() to redirect to the BuddyDrive item
 */
function buddydrive_maybe_redirect_oldlink() {

	if ( ! is_404() )
		return;

	$root_domain_url = bp_get_root_domain();
	$maybe_buddydrive = trailingslashit( $root_domain_url . esc_url( $_SERVER['REQUEST_URI'] ) );

	$buddydrive_slug = buddydrive()->buddydrive_slug;
	$buddydrive_old_root_url = trailingslashit( $root_domain_url ) . $buddydrive_slug;

	if ( strpos( $maybe_buddydrive, $buddydrive_old_root_url ) === 0 ) {

		$buddydrive_new_url = str_replace( $buddydrive_old_root_url, buddydrive_get_root_url(), $maybe_buddydrive );

		bp_core_redirect( $buddydrive_new_url );

	}
}

/**
 * Returns an array of upload errors
 *
 * @since  1.2.2
 *
 * Takes WordPress ones + BuddyDrive ones index 9 to 11
 * @uses apply_filters call 'buddydrive_get_upload_error_strings' to add custom errors
 *                     You'll need to start at index 12.
 * @return array list of BuddyDrive errors
 */
function buddydrive_get_upload_error_strings() {
	$custom_errors = apply_filters( 'buddydrive_get_upload_error_strings', array() );

	$upload_errors = array(
		9  => __( 'Not enough space left to upload your file', 'buddydrive' ),
		10 => sprintf( __('This file is too big. Files must be less than %s MB in size.', 'buddydrive' ), buddydrive_max_upload_size() ),
		11 => __( 'You have used your space quota. Please delete files before uploading.', 'buddydrive' ),
	);

	if ( ! empty( $custom_errors ) && ! array_intersect_key( $upload_errors, $custom_errors ) ) {
		foreach ( $custom_errors as $key_error => $error_message ) {
			// Custom errors need to start at 12 index.
			if ( $key_error < 12 ) {
				continue;
			}

			$upload_errors[ $key_error ] = $error_message;
		}
	}

	return $upload_errors;
}

/**
 * Get BuddyDrive Items available post stati
 *
 * @since 2.0.0
 *
 * @param bool $no_filter True to get an unfiltered version of available stati.
 *                        False otherwise. Defaults False.
 * @return array
 */
function buddydrive_get_stati( $no_filter = false ) {
	$stati = array(
		'buddydrive_public' => array(
			'label'                     => _x( 'Public', 'file or folder status', 'buddydrive' ),
			'public'                    => true,
			'show_in_admin_status_list' => false,
			'show_in_admin_all_list'    => false,
			'buddydrive_settings'       => true,
			'buddydrive_privacy'        => 'public',
		),
		'buddydrive_private' => array(
			'label'                     => _x( 'Private', 'file or folder status', 'buddydrive' ),
			'private'                   => true,
			'show_in_admin_status_list' => false,
			'show_in_admin_all_list'    => false,
			'buddydrive_settings'       => true,
			'buddydrive_privacy'        => 'private',
		),
		'buddydrive_password' => array(
			'label'                     => _x( 'Password protected', 'file or folder status', 'buddydrive' ),
			'protected'                 => true,
			'show_in_admin_status_list' => false,
			'show_in_admin_all_list'    => false,
			'buddydrive_settings'       => false,
			'buddydrive_privacy'        => 'password',
		),
		'buddydrive_friends' => array(
			'label'                     => _x( 'Restricted to friends', 'file or folder status', 'buddydrive' ),
			'protected'                 => true,
			'show_in_admin_status_list' => false,
			'show_in_admin_all_list'    => false,
			'buddydrive_settings'       => false,
			'buddydrive_privacy'        => 'friends',
		),
		'buddydrive_groups' => array(
			'label'                     => _x( 'Restricted to a group', 'file or folder status', 'buddydrive' ),
			'protected'                 => true,
			'show_in_admin_status_list' => false,
			'show_in_admin_all_list'    => false,
			'buddydrive_settings'       => false,
			'buddydrive_privacy'        => 'groups',
		),
		'buddydrive_members' => array(
			'label'                     => _x( 'Restricted to members', 'file or folder status', 'buddydrive' ),
			'protected'                 => true,
			'show_in_admin_status_list' => false,
			'show_in_admin_all_list'    => false,
			'buddydrive_settings'       => false,
			'buddydrive_privacy'        => 'members',
		),
	);

	if ( true === $no_filter ) {
		return $stati;
	} else {
		return apply_filters( 'buddydrive_get_stati', $stati );
	}
}

/**
 * Get BuddyDrive privacy out of a status or for a BuddyDrive item ID
 *
 * @since 2.0.0
 *
 * @param  int|string  $status A BuddyDrive Item ID or the name of the status
 * @return string The validated privacy
 */
function buddydrive_get_privacy( $status = false ) {
	if ( is_numeric( $status ) ) {
		$status = get_post_status( $status );
	}

	if ( ! $status ) {
		return false;
	}

	$status_object = get_post_stati( array( 'name' => $status ), 'objects' );
	$status_object = reset( $status_object );

	if ( ! empty( $status_object->buddydrive_privacy ) ) {
		return $status_object->buddydrive_privacy;
	} else {
		return false;
	}
}

/**
 * Get BuddyDrive default privacy
 *
 * @since 2.0.0
 *
 * @param  string $default The default post status.
 * @return string The default privacy
 */
function buddydrive_get_default_privacy( $default = 'buddydrive_public' ) {
	return apply_filters( 'buddydrive_get_default_privacy', buddydrive_get_privacy( bp_get_option( '_buddydrive_default_privacy', $default ) ) );
}

/**
 * Get visible groups for the current user
 *
 * @since 2.0.0
 *
 * @return array A list of visible group IDs
 */
function buddydrive_get_visible_groups() {
	global $wpdb;
	$bp = buddypress();

	// Get all public groups
	$visible_groups = $wpdb->get_col( "SELECT id FROM {$bp->groups->table_name} WHERE status = 'public'" );

	if ( is_user_logged_in() ) {
		$current_user_groups = groups_get_user_groups( bp_loggedin_user_id() );

		if ( ! empty( $current_user_groups['groups'] ) ) {
			$visible_groups = array_unique( array_merge( $visible_groups, $current_user_groups['groups'] ) );
		}
	}

	return $visible_groups;
}

/**
 * Update BuddyDrive Items stati
 * Migration tool specific to 2.0.0. Transform the 'publish' status to the new one.
 *
 * @since 2.0.0
 *
 * @param  int $per_page the number of BuddyDrive items to upgrade
 * @return int The number of upgraded BuddyDrive items
 */
function buddydrive_update_items_status( $per_page = false ) {
	global $wpdb;

	$buddydrive_stati = buddydrive_get_stati( true );
	$privacy          = array();
	foreach ( $buddydrive_stati as $key_status => $status ) {
		$privacy[ $status['buddydrive_privacy'] ] = $key_status;
	}

	$sql = array(
		'select' => "SELECT p.ID as post_id, m.meta_value FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m on( p.ID = m.post_id )",
		'where'  => array(
			'status'   => "p.post_status = 'publish'",
			'meta_key' => $wpdb->prepare( 'meta_key = %s', '_buddydrive_sharing_option' ),
		),
	);

	if ( ! empty( $per_page ) ) {
		$sql['limit'] = $wpdb->prepare( 'LIMIT %d', $per_page );
	}

	$sql['where'] = 'WHERE ' . join( ' AND ', $sql['where'] );

	$items = $wpdb->get_results( join( ' ', $sql ) );

	$updated = 0;

	if ( empty( $items ) ) {
		return $updated;
	}

	foreach ( $items as $item ) {
		if ( ! isset( $privacy[ $item->meta_value ] ) ) {
			$status = 'buddydrive_private';
		} else {
			$status = $privacy[ $item->meta_value ];
		}

		$update_r = (int) $wpdb->update( $wpdb->posts, array( 'post_status' => $status ), array( 'ID' => $item->post_id ), array( '%s' ), array( '%d' ) );

		// Log an error if the update failed
		if ( empty( $update_r ) ) {
			error_log( sprintf( 'The item ID %s could not be updated to the status %s.', $item->post_id, $status ) );

		// Increment the count if it succeeded
		} else {
			$updated += $update_r;
		}
	}

	return $updated;
}

/**
 * List upgrade routines according to a DB version
 *
 * @since 2.0.0
 *
 * @return array The list of upgrade routines to perform.
 */
function buddydrive_get_upgrade_tasks() {
	global $wpdb;

	$routines = array(
		'200' => array(
			array(
				'action_id' => 'upgrade_item_stati',
				'count'     => $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m on( p.ID = m.post_id ) WHERE p.post_status = 'publish' AND m.meta_key = %s", '_buddydrive_sharing_option' ),
				'message'   => _x( 'Status of files and folders - %d item(s) to upgrade', 'Upgrader feedback message', 'buddydrive' ),
				'callback'  => 'buddydrive_update_items_status'
			),
			array(
				'action_id' => 'upgrade_db_version',
				'count'     => 1,
				'message'   => _x( 'Database version - 1 item to update', 'Upgrader feedback message', 'buddydrive' ),
				'callback'  => ''
			),
		),
	);

	$tasks = array();

	// Only keep the upgrade routine we need to perform according
	// to the current db version
	foreach ( $routines as $db_version => $list ) {
		if ( (int) $db_version > (int) buddydrive_get_db_number_version() || (int) buddydrive_get_db_number_version() <= 210 ) {
			$tasks = array_merge( $tasks, $list );
		}
	}

	return $tasks;
}

/**
 * Whether to use the deprecated UI or not. Defaults to not!
 *
 * @since 2.0.0
 *
 * @return bool True to use the deprecated UI. False otherwise.
 */
function buddydrive_use_deprecated_ui() {
	return apply_filters( 'buddydrive_use_deprecated_ui', false );
}

/**
 * Register the scripts and styles for the new BuddyDrive UI
 *
 * @since 2.0.0
 */
function buddydrive_register_ui_cssjs() {
	$min          = '.min';
	$bd_version   = buddydrive_get_version();
	$includes_url = buddydrive_get_includes_url();

	if ( defined( 'SCRIPT_DEBUG' ) && true == SCRIPT_DEBUG )  {
		$min = '';
	}

	// Register the App style
	wp_register_style(
		'buddydrive-app-style',
		$includes_url . "css/buddydrive-app{$min}.css",
		array( 'dashicons' ),
		$bd_version
	);

	if ( bp_is_current_component( 'buddydrive' ) || buddydrive_is_group() ) {
		$front_end_style_path = bp_locate_template( 'css/buddydrive.css', false );

		if ( $front_end_style_path ) {
			$front_end_style_uri = str_replace( array( get_theme_root(), buddydrive_get_plugin_dir() ), array( get_theme_root_uri(), buddydrive_get_plugin_url() ), $front_end_style_path );

			// Validate the uri
			if ( parse_url( $front_end_style_uri, PHP_URL_HOST ) ) {
				wp_register_style(
					'buddydrive-front-end-style',
					$front_end_style_uri,
					array( 'buddydrive-app-style' ),
					$bd_version
				);
			}
		}
	}

	// Define UI Scrips
	$ui_scripts = apply_filters( 'buddydrive_register_ui_get_scripts', array(
		'buddydrive-models-js' => array(
			'url'     => $includes_url . "js/buddydrive-models{$min}.js",
			'deps'    => array( 'jquery', 'json2', 'wp-backbone' ),
			'version' => $bd_version,
			'footer'  => true,
		),
		'buddydrive-views-js' => array(
			'url'     => $includes_url . "js/buddydrive-views{$min}.js",
			'deps'    => array( 'buddydrive-models-js' ),
			'version' => $bd_version,
			'footer'  => true,
		),
		'buddydrive-app-js' => array(
			'url'     => $includes_url . "js/buddydrive-app{$min}.js",
			'deps'    => array( 'buddydrive-views-js' ),
			'version' => $bd_version,
			'footer'  => true,
		),
	) );

	// Register scripts
	foreach( $ui_scripts as $handle => $script ) {
		wp_register_script(
			$handle,
			$script['url'],
			$script['deps'],
			$script['version'],
			$script['footer']
		);
	}
}

/**
 * Temporarly add the BuddyDrive templates location to BuddyPress template stack
 *
 * @since 2.0.0
 *
 * @param array $stack the BuddyPress templates stack
 * @return array the BuddyPress templates stack
 */
function buddydrive_set_template_stack( $stack = array() ) {
	if ( empty( $stack ) ) {
		$stack = array( buddydrive_get_plugin_dir() . 'templates' );
	} else {
		$stack[] = buddydrive_get_plugin_dir() . 'templates';
	}

	return $stack;
}

/**
 * Get the BuddyDrive asset template part
 *
 * @since 2.0.0
 */
function buddydrive_get_asset_template_part( $slug ) {
	add_filter( 'bp_locate_template_and_load', '__return_true'                        );
	add_filter( 'bp_get_template_stack',       'buddydrive_set_template_stack', 10, 1 );

	bp_get_template_part( 'assets/buddydrive/' . $slug );

	remove_filter( 'bp_locate_template_and_load', '__return_true'                        );
	remove_filter( 'bp_get_template_stack',       'buddydrive_set_template_stack', 10, 1 );
}

/**
 * This is a temporary capability checking function
 *
 * It's not used everywhere and will be replaced with a better one in a future release.
 *
 * @since 2.0.0
 *
 * @param string $capability The capability to check
 * @param array  $args       Additionnal args to help us decide whether current user can
 * @return bool True if the current user can, false otherwise.
 */
function buddydrive_current_user_can( $capability = 'buddydrive_upload', $args = array() ) {
	$can     = false;
	$user_id = bp_loggedin_user_id();

	// Upload/Creare folder
	if ( 'buddydrive_upload' === $capability ) {
		if ( bp_is_user() ) {
			$can = (int) bp_displayed_user_id() === (int) $user_id;
		} elseif ( bp_is_group() ) {
			$can = (bool) groups_is_user_member( $user_id, bp_get_current_group_id() );
		} else {
			$can = bp_current_user_can( 'bp_moderate' );
		}

	// Delete files/folders or remove files from folders
	} elseif ( 'buddydrive_delete' === $capability || 'buddydrive_remove_parent' === $capability ) {
		// Admins can always delete
		$can = bp_current_user_can( 'bp_moderate' );

		if ( ! empty( $args['owner_id'] ) && (int) $args['owner_id'] === (int) $user_id ) {
			$can = true;
		}

		if ( 'buddydrive_remove_parent' === $capability && ! empty( $args['parent_owner_id'] ) && (int) $args['parent_owner_id'] === (int) $user_id ) {
			$can = true;
		}
	} elseif ( 'buddydrive_share' === $capability ) {
		// We need the BuddyDrive item
		if ( ! empty( $args['item'] ) && is_a( $args['item'], 'WP_Post' ) ) {
			switch ( $args['item']->post_status ) {

				// anybody can share
				case 'buddydrive_public' :
					$can = true;
					break;

				case 'buddydrive_friends'  :
				case 'buddydrive_password' :
				case 'buddydrive_members'  :
					$can = (int) $args['item']->user_id === (int) $user_id || bp_current_user_can( 'bp_moderate' );
					break;

				case 'buddydrive_groups'   :
					if ( bp_is_group() ) {
						$group_id = bp_get_current_group_id();

						$can = in_array( $group_id, (array) $args['item']->group ) && groups_is_user_member( $user_id, $group_id );
					} else {
						$can = (int) $args['item']->user_id === (int) $user_id || bp_current_user_can( 'bp_moderate' );
					}
					break;

				default:
					$can = false;
					break;
			}
		} else {
			$can = bp_current_user_can( 'bp_moderate' );
		}
	} elseif ( 'buddydrive_edit' === $capability || 'buddydrive_bulk_edit' === $capability ) {
		$can = bp_current_user_can( 'bp_moderate' );

		if ( ! $can && ! empty( $args['item'] ) && is_a( $args['item'], 'WP_Post' ) ) {
			$can = (int) $args['item']->user_id === (int) $user_id;

			if ( 'buddydrive_bulk_edit' === $capability && ! $can && bp_is_my_profile() && ! empty( $args['item']->post_parent ) ) {
				$can = (int) $user_id === (int) get_post_field( 'post_author', $args['item']->post_parent );
			}
		}

		if ( 'buddydrive_bulk_edit' === $capability && ! $can && bp_is_group() ) {
			$can = (bool) groups_is_user_admin( $user_id, bp_get_current_group_id() );
		}
	} elseif ( 'buddydrive_remove_group' === $capability ) {
		$can = bp_current_user_can( 'bp_moderate' );

		if ( ! $can && ! empty( $args['item'] ) && is_a( $args['item'], 'WP_Post' ) ) {
			$can = (int) $args['item']->user_id === (int) $user_id;
		}

		if ( ! $can && ! empty( $args['group_id'] ) ) {
			$can = (bool) groups_is_user_admin( $user_id, $args['group_id'] );
		}
	}

	return apply_filters( 'buddydrive_current_user_can', $can, $capability, $user_id, $args );
}
