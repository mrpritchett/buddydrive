<?php
/**
 * BuddyDrive Item filters
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * filters wp_upload_dir to replace its datas by buddydrive ones
 *
 * @deprecated 1.3.0
 *
 * @param  array $upload_data  wp_upload dir datas
 * @uses   wp_parse_args() to merge datas
 * @return array  $r the filtered array
 */
function buddydrive_temporarly_filters_wp_upload_dir( $upload_data ) {
	$path = buddydrive()->upload_dir;
	$url  = buddydrive()->upload_url;

	$buddydrive_args = apply_filters( 'buddydrive_upload_datas',
		array(
			'path'    => $path,
			'url'     => $url,
			'subdir'  => false,
			'basedir' => $path,
			'baseurl' => $url,
		) );

	$r = wp_parse_args( $buddydrive_args, $upload_data );

	return $r;
}

/**
 * filters WordPress mime types
 *
 * @deprecated 1.3.0
 *
 * @param  array $allowed_file_types the WordPress mime types
 * @uses   buddydrive_allowed_file_types() to get the option defined by admin
 * @return array mime types allowed by BuddyDrive
 */
function buddydrive_allowed_upload_mimes( $allowed_file_types ) {
	return buddydrive_allowed_file_types( $allowed_file_types );
}

/**
 * Checks file uploaded size upon user's space left and max upload size
 *
 * @deprecated 1.3.0
 *
 * @param  array $file $_FILE array
 * @uses   buddydrive_get_user_space_left() to get user's space left
 * @uses   buddydrive_max_upload_size() to get max upload size
 * @return array $file the $_FILE array with an eventual error
 */
function buddydrive_check_upload_size( $file ) {
	// there's already an error
	if ( ! empty( $file['error'] ) ) {
		return $file;
	}

	// This codes are restricted to BuddyDrive
	$buddydrive_errors = array(
		9  => 1,
		10 => 1,
		11 => 1,
	);

	// what's left in user's quota ?
	$space_left = buddydrive_get_user_space_left( 'diff' );
	$file_size = filesize( $file['tmp_name'] );

	// File is bigger than space left
	if ( $space_left < $file_size ) {
		$file['error'] = 9;
	}

	// File is bigger than the max allowed for BuddyDrive files
	if ( $file_size > buddydrive_max_upload_size( true ) ) {
		$file['error'] = 10;
	}

	// No more space left
	if ( $space_left <= 0 ) {
		$file['error'] = 11;
	}

	if ( ! isset( $buddydrive_errors[ $file['error'] ] ) ) {
		return apply_filters( 'buddydrive_upload_errors', $file );
	}

	return $file;
}

/**
 * temporarly filters buddydrive_get_user_space_left to only output the quota with no html tags
 *
 * @param  string $output html
 * @param  string $quota  the space left without html tags
 * @return string $quota
 */
function buddydrive_filter_user_space_left( $output, $quota ) {
	if ( isset( $quota['percent'] ) ) {
		$output = $quota['percent'];
	}

	return $output;
}

/**
 * filters bp_get_message_get_recipient_usernames if needed
 *
 * @param  string $recipients the message recipients
 * @uses   friends_get_friend_user_ids() to get the friends list
 * @uses   bp_loggedin_user_id() to get the current logged in user
 * @uses   bp_core_get_username() to get the usernames of the friends.
 * @return string list of the usernames of the friends of the loggedin users
 */
function buddydrive_add_friend_to_recipients( $recipients ) {

	if ( empty( $_REQUEST['buddyitem'] ) )
		return $recipients;

	$ids = friends_get_friend_user_ids( bp_loggedin_user_id() );

	$usernames = false;

	foreach ( $ids as $id ) {
		$usernames[] = bp_core_get_username( $id );
	}

	if ( is_array( $usernames ) )
		return implode( ' ', $usernames );

	else
		return $recipients;

}

/**
 * removes the BuddyDrive directory page from wp header menu
 *
 * @param  array $args the menu args
 * @uses   buddydrive_get_slug() to get the slug of the BuddyDrive directory page
 * @uses   bp_core_get_directory_page_ids() to get an array of BP Components page ids
 * @return args  $args with a new arg to exclude BuddyDrive page.
 */
function buddydrive_hide_item( $args ) {

	$buddydrive_slug = buddydrive()->buddydrive_slug;

	$directory_pages = bp_core_get_directory_page_ids();

	if ( empty( $args['exclude'] ) ) {
		$args['exclude'] = $directory_pages[$buddydrive_slug];
	} else {
		$args['exclude'] .= ',' . $directory_pages[$buddydrive_slug];
	}

	return $args;
}
add_filter( 'wp_page_menu_args', 'buddydrive_hide_item', 20, 1 );

/**
 * Prevent BuddyDrive directory page from showing in the Pages meta box of the Menu Administration screen.
 *
 * @since BuddyDrive (1.2.0)
 *
 * @uses bp_is_root_blog() checks if current blog is root blog.
 * @uses buddypress() gets BuddyPress main instance
 *
 * @param object $object The post type object used in the meta box
 * @return object The $object, with a query argument to remove register and activate pages id.
 */
function buddydrive_hide_from_nav_menu_admin( $object = null ) {

	// Bail if not the root blog
	if ( ! bp_is_root_blog() ) {
		return $object;
	}

	if ( 'page' != $object->name ) {
		return $object;
	}

	$bp = buddypress();

	if ( ! empty( $bp->pages->buddydrive ) ) {
		$object->_default_query['post__not_in'] = array( $bp->pages->buddydrive->id );
	}

	return $object;
}
add_filter( 'nav_menu_meta_box_object', 'buddydrive_hide_from_nav_menu_admin', 11, 1 );

/**
 * Adds buddydrive's slug to the groups forbidden names
 *
 * @since  version 1.1
 *
 * @param  array  $names the groups forbidden names
 * @uses buddydrive_get_slug() to get the plugin's slug
 * @return array        the same names + buddydrive's slug.
 */
function buddydrive_add_to_group_forbidden_names( $names = array() ) {

	$names[] = buddydrive_get_slug();
	return $names;
}
add_filter( 'groups_forbidden_names', 'buddydrive_add_to_group_forbidden_names' );

/**
 * Add some custom strings to the BP Uploader
 *
 * @since 1.3.0
 *
 * @param array $strings the BP Uploader strings
 * @return array
 */
function buddydrive_editor_strings( $strings = array() ) {
	$buddydrive = buddydrive();

	$strings['buddydrive_insert'] = esc_html__( 'Insert', 'buddydrive' );
	if ( ! empty( $buddydrive->editor_id ) ) {
		$strings['buddydrive_editor_id'] = esc_html( $buddydrive->editor_id );
	}

	return $strings;
}

/**
 * Add some custom ssettings to the BP Uploader
 *
 * @since 1.3.0
 *
 * @param array $strings the BP Uploader settings
 * @return array
 */
function buddydrive_editor_settings( $settings = array() ) {
	if ( isset( $settings['defaults'] ) && ! isset( $settings['defaults']['multi_selection'] ) ) {
		$settings['defaults']['multi_selection'] = false;
	}

	return $settings;
}

/**
 * Prepare script data for the Public Editor
 *
 * @since 2.0.0
 *
 * @param array $script_data the BP Uploader script datas
 * @return array
 */
 function buddydrive_editor_script_data( $script_data = array() ) {
 	$public_data = array_merge( $script_data, array(
		'extra_css' => array( 'buddydrive-public-style' ),
		'extra_js'  => array( 'buddydrive-public-js' )
 	) );

 	if ( ! empty( $public_data['bp_params'] ) ) {
 		$public_data['bp_params']['privacy'] = 'public';
 	}

 	return $public_data;
 }

/**
 * Only keep the Thumbnail sizes for BuddyDrive public files
 *
 * @since 1.3.0
 *
 * @param array $sizes the WordPress available sizes (thumbnail, large, medium)
 * @return array       an array only containing the thumbnail size
 */
function buddydrive_public_restrict_image_sizes( $sizes = array() ) {
	return array_intersect_key( $sizes, array( 'thumbnail' => true ) );
}

/**
 * Change the relative path to match with WordPress upload organisation
 *
 * @since 1.3.0
 *
 * @param  string $new_path the relative path to the protected file
 * @return string           the path to the public file
 */
function buddydrive_public_relative_path( $new_path = '', $path = '' ) {
	$bp_upload_dir = bp_upload_dir();
	$bd_relative = ltrim( str_replace( $bp_upload_dir['basedir'], '',buddydrive()->upload_dir ), '/' );

	return ltrim( str_replace( $bd_relative, '', $new_path ), '/' );
}
