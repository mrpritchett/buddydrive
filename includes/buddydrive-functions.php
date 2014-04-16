<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * What is the version in db ?
 *
 * @uses get_option() to get the BuddyDrive version
 * @return string the version
 */
function buddydrive_get_db_version(){
	return get_option( '_buddydrive_version' );
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

/**
 * Is it the first install ?
 *
 * @uses get_option() to get the BuddyDrive version
 * @return boolean true or false
 */
function buddydrive_is_install() {
	$buddydrive_version = get_option( '_buddydrive_version', '' );
	
	if( empty( $buddydrive_version ) )
		return true;
	else
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
	
	if( !empty( $buddydrive_version ) )
		return true;
	else
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
		$friends_subnav = bp_get_option( '_buddydrive_friends_subnav_name', __( 'Shared by Friends', 'buddydrive' ) );

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
 * @uses wp_upload_dir() to get WordPress basedir and baseurl
 * @return array
 */
function buddydrive_get_upload_data() {
	$upload_datas = wp_upload_dir();
	
	$buddydrive_dir = $upload_datas["basedir"] .'/buddydrive';
	$buddydrive_url = $upload_datas["baseurl"] .'/buddydrive';
	$buddydrive_upload_data = array( 'dir' => $buddydrive_dir, 'url' => $buddydrive_url );
	
	//finally returns $buddydrive_upload_data, you can filter if you know what you're doing!
	return apply_filters( 'buddydrive_get_upload_data', $buddydrive_upload_data );
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
	if ( isset( $_GET['activate-multi'] ) )
		return;

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
	if ( buddydrive::bail() )
		return;

	if ( buddydrive_is_install() || version_compare( buddydrive_get_db_version(), buddydrive_get_version(), '<' ) ) {
		
		update_option( '_buddydrive_version', buddydrive_get_version() );

	}
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
	
	$allowed_ext = bp_get_option( '_buddydrive_allowed_extensions' );

	if ( empty( $allowed_ext ) || ! is_array( $allowed_ext ) || count( $allowed_ext ) < 1 )
		return $allowed_file_types;

	$allowed_ext = array_flip( $allowed_ext );
	$allowed_ext = array_intersect_key( $allowed_file_types, $allowed_ext );

	return $allowed_ext;
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
