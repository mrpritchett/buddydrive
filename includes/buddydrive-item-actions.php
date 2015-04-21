<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Loads the css and javascript when on Friends/Group BuddyDrive
 *
 * @uses bp_is_current_component()  to check for BuddyDrive component
 * @uses buddydrive_is_group() to include the group case
 * @uses wp_enqueue_style() to load BuddyDrive style
 * @uses wp_enqueue_script() to load BuddyDrive script
 * @uses buddydrive_get_includes_url() to get the includes url
 * @uses wp_localize_script() to add some translation to js messages/output
 * @uses buddydrive_get_js_l10n() to get the translation
 * @uses buddydrive_is_user_buddydrive() to check we're not on loggedin user's BuddyDrive
 */
function buddydrive_file_enqueue_scripts() {
	if ( bp_is_current_component( 'buddydrive' ) || buddydrive_is_group()  ) {

		$budddrive_css = apply_filters( 'buddydrive_global_css', array(
			'stylesheet_uri' => buddydrive_get_includes_url() .'css/buddydrive.css',
			'deps'           => array( 'dashicons' ),
		) );

		// style is for every BuddyDrive screens
		wp_enqueue_style( 'buddydrive', $budddrive_css['stylesheet_uri'], $budddrive_css['deps'], buddydrive_get_version() );

		// in group and friends BuddyDrive, loads a specific script
		if ( ! buddydrive_is_user_buddydrive() ) {
			wp_enqueue_script('buddydrive-view', buddydrive_get_includes_url() .'js/buddydrive-view.js', array( 'jquery' ), buddydrive_get_version(), true );
			wp_localize_script( 'buddydrive-view', 'buddydrive_view', buddydrive_get_js_l10n() );
		}
	}
}
add_action( 'buddydrive_enqueue_scripts', 'buddydrive_file_enqueue_scripts' );

/**
 * Get a user's count viewable by others
 *
 * @since  1.2.2
 *
 * @param  int $count the number of files for the displayed user
 */
function buddydrive_view_add_script_data( $count = 0 ) {
	global $wp_scripts;

	if ( ! bp_is_user() || bp_is_my_profile() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return;
	}

	$data = $wp_scripts->get_data( 'buddydrive-view', 'data' );

 	$count_data = array(
		'id'         => 'user-' . buddydrive_get_slug(),
		'count'      => $count,
	);

 	// Extend the script data
 	$script = 'var BuddyDriveFilesCount = ' . json_encode( $count_data ) . ';';

 	if ( $data ) {
 		$script = "$data\n$script";
 	}

 	$wp_scripts->add_data( 'buddydrive-view', 'data', $script );
}
add_action( 'buddydrive_has_items_catch_total_count', 'buddydrive_view_add_script_data', 10, 1 );

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
 * @uses bp_displayed_user_id() to be sure we're not on a profile
 * @uses bp_is_current_component() to check for BuddyDrive component
 * @uses bp_current_action() to check if current action is file / folder
 * @uses esc_url()
 * @uses wp_get_referer() to eventually redirect the user
 * @uses bp_action_variable() to get the name of the file / folder
 * @uses buddydrive_get_buddyfile() to get the file / folder object
 * @uses buddydrive_get_folder_post_type() to get the folder post type
 * @uses bp_loggedin_user_id() to get current user id
 * @uses is_super_admin() as super admin can download anything
 * @uses bp_core_add_message() to eventually display a warning message to user
 * @uses buddydrive_get_user_buddydrive_url() to construct the user's BuddyDrive url
 * @uses bp_core_redirect() to redirect user if needed
 * @uses friends_check_friendship() to check if the current user is friend with the file owner
 * @uses bp_is_active() to check a BuddyPress component is active
 * @uses groups_is_user_member() to check if the current user is member of the group of the file
 * @uses groups_get_group() to get the group object of the group the file / folder is attached to
 * @uses bp_get_group_permalink() to build the group link
 * @uses buddydrive_get_group_buddydrive_url() to build the link to the BuddyDrive of the group
 * @uses site_url() to redirect to home if nothing match
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

		$can_donwload = false;

		if ( ! empty( $buddydrive_file->check_for ) ) {

			switch( $buddydrive_file->check_for ) {

				case 'private' :
					if ( $buddydrive_file->user_id == bp_loggedin_user_id() || is_super_admin() )
						$can_donwload = true;
					break;

				case 'password' :
					if ( $buddydrive_file->user_id == bp_loggedin_user_id() || is_super_admin() )
						$can_donwload = true;
					elseif ( empty( $_POST['buddyfile-form'] ) ) {
						bp_core_add_message( __( 'This file is password protected', 'buddydrive' ), 'error' );
						add_action( 'buddydrive_directory_content', 'buddydrive_file_password_form' );
						$can_donwload = false;
					} else {
						//check admin referer

						if ( $buddydrive_file->password == $_POST['buddyfile-form']['password']  )
							$can_donwload = true;

						else {
							$redirect = buddydrive_get_user_buddydrive_url( $buddydrive_file->user_id );
							bp_core_add_message( __( 'Wrong password', 'buddydrive' ), 'error' );
							bp_core_redirect( $redirect );
							$can_donwload = false;
						}

					}
					break;

				case 'public' :
					$can_donwload = true;
					break;

				case 'friends' :
					if ( $buddydrive_file->user_id == bp_loggedin_user_id() || is_super_admin() )
						$can_donwload = true;
					elseif ( bp_is_active( 'friends' ) && friends_check_friendship( $buddydrive_file->user_id, bp_loggedin_user_id() ) )
						$can_donwload = true;
					else {
						$redirect = buddydrive_get_user_buddydrive_url( $buddydrive_file->user_id );
						bp_core_add_message( __( 'You must be a friend of this member to download the file', 'buddydrive' ), 'error' );
						bp_core_redirect( $redirect );
						$can_donwload = false;
					}
					break;

				case 'groups' :
					if ( $buddydrive_file->user_id == bp_loggedin_user_id() || is_super_admin() )
						$can_donwload = true;
					elseif ( ! bp_is_active( 'groups' ) ) {
						bp_core_add_message( __( 'Group component is deactivated, please contact the administrator.', 'buddydrive' ), 'error' );
						bp_core_redirect( buddydrive_get_root_url() );
						$can_donwload = false;
					}
					elseif ( groups_is_user_member( bp_loggedin_user_id(), intval( $buddydrive_file->group ) ) )
						$can_donwload = true;
					else {
						$group = groups_get_group( array( 'group_id' => $buddydrive_file->group ) );

						if ( 'hidden' == $group->status )
							$redirect = wp_get_referer();

						else
							$redirect = bp_get_group_permalink( $group );

						bp_core_add_message( __( 'You must be member of the group to download the file', 'buddydrive' ), 'error' );
						bp_core_redirect( $redirect );
						$can_donwload = false;
					}
					break;
			}

		} else {
			if ( $buddydrive_file->user_id == bp_loggedin_user_id() || is_super_admin() )
				$can_donwload = true;
		}

		// we have a file! let's force download.
		if ( file_exists( $buddydrive_file_path ) && !empty( $can_donwload ) ){
			do_action( 'buddydrive_file_downloaded', $buddydrive_file );
			status_header( 200 );
			header( 'Cache-Control: cache, must-revalidate' );
			header( 'Pragma: public' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Length: ' . filesize( $buddydrive_file_path ) );
			header( 'Content-Disposition: attachment; filename='.$buddydrive_file_name );
			header( 'Content-Type: ' .$buddydrive_file_mime );
			readfile( $buddydrive_file_path );
			die();
		}

	} else if ( ! bp_displayed_user_id() && bp_is_current_component( 'buddydrive' ) && 'folder' == bp_current_action() ) {

		$buddyfolder_name = bp_action_variable( 0 );

		$buddyfolder = buddydrive_get_buddyfile( $buddyfolder_name, buddydrive_get_folder_post_type() );

		if ( empty( $buddyfolder ) ) {
			bp_core_add_message( __( 'OOps, we could not find your folder.', 'buddydrive' ), 'error' );
			bp_core_redirect( buddydrive_get_root_url() );
		}

		// in case of the folder, we open it on the user's BuddyDrive or the group one
		$buddydrive_root_link = ( $buddyfolder->check_for == 'groups' ) ? buddydrive_get_group_buddydrive_url( $buddyfolder->group ) : buddydrive_get_user_buddydrive_url( $buddyfolder->user_id ) ;
		$link = $buddydrive_root_link .'?folder-'. $buddyfolder->ID;
		bp_core_redirect( $link );
	}
}
add_action( 'buddydrive_actions', 'buddydrive_file_downloader', 1 );

/**
 * Adds post datas to include a file / folder to a private message
 *
 * @uses buddydrive_get_buddyfile() gets the BuddyFile Object
 * @uses buddydrive_get_file_post_type() gets the BuddyFile post type
 * @uses buddydrive_get_name() so that it's possible to brand the plugin
 * @uses buddydrive_get_folder_post_type() gets the BuddyFolder post type
 * @uses bp_loggedin_user_id() to get current user id
 * @uses buddydrive_get_user_buddydrive_url() to build the folder url on user's BuddyDrive
 * @return string html output and inputs
 */
function buddydrive_attached_file_to_message() {

	if ( ! empty( $_REQUEST['buddyitem'] ) ) {

		$link = $buddytype = $password = false;
		$buddyitem = buddydrive_get_buddyfile( $_REQUEST['buddyitem'], array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ) );

		if ( ! empty( $buddyitem->ID ) ){

			if ( $buddyitem->user_id != bp_loggedin_user_id() ) {
				?>
				<div id="message" class="error"><p><?php _e( 'Cheating ?', 'buddydrive' );?></p></div>
				<?php
				return;
			}

			$link = $buddyitem->link;

			if ( $buddyitem->post_type == buddydrive_get_file_post_type() ) {
				$displayed_link = $buddyitem->link;
				$buddytype = buddydrive_get_name() . ' File';

				if ( ! empty( $buddyitem->post_parent ) ) {
					$parent = buddydrive_get_buddyfile( $buddyitem->post_parent, buddydrive_get_folder_post_type() );
					$password = !empty( $parent->password ) ? $parent->password : false ;
				} else
					$password = !empty( $buddyitem->password  ) ? $buddyitem->password : false ;

			} else {
				$displayed_link = buddydrive_get_user_buddydrive_url( bp_loggedin_user_id() ) . '?folder-'.$buddyitem->ID ;
				$buddytype = buddydrive_get_name() . ' Folder';
				$password = !empty( $buddyitem->password  ) ? $buddyitem->password : false ;
			}
			?>
			<p>
				<label for="buddyitem-link"><?php printf( __( '%s attached : %s', 'buddydrive' ), esc_html( $buddytype ), '<a href="' . esc_url( $displayed_link ) . '">'. esc_html( $buddyitem->title ). '</a>' );?></label>
				<input type="hidden" value="<?php echo esc_url( $link );?>" id="buddyitem-link" name="_buddyitem_link">
				<input type="hidden" value="<?php echo esc_attr( $buddyitem->ID );?>" id="buddyitem-id" name="_buddyitem_id">

				<?php if ( ! empty( $password ) ) :?>
					<input type="checkbox" name="_buddyitem_pass" value="1" checked> <?php _e('Automatically add the password in the message', 'buddydrive');?>
				<?php endif;?>
			</p>
			<?php
		}
	}
}

/**
 * adds a hook to include previous function and a filter to eventually add friends recipients
 *
 * @uses bp_is_active() to check a BuddyPress component is active
 */
function buddydrive_messages_screen_compose() {

	if ( ! empty( $_REQUEST['buddyitem'] ) ) {

		add_action( 'bp_after_messages_compose_content', 'buddydrive_attached_file_to_message' );

		if ( ! empty( $_REQUEST['friends'] ) && bp_is_active( 'friends' ) )
			add_filter( 'bp_get_message_get_recipient_usernames', 'buddydrive_add_friend_to_recipients', 10, 1 );

	}

}
add_action( 'messages_screen_compose', 'buddydrive_messages_screen_compose' );

/**
 * Adds the link to the file or list of files at the bottom of the message
 *
 * @param  string $message the content of the  private message
 * @uses buddydrive_get_buddyfile() to get the file or folder object
 * @uses buddydrive_get_file_post_type() to get the file post type
 * @uses buddydrive_get_folder_post_type() to get the folder post type
 * @return string $message with the link to the file/folder
 */
function buddydrive_update_message_content( $message ) {

	if ( ! empty( $_POST['_buddyitem_link'] ) ){

		$password = $password_check = false;

		if ( ! empty( $_POST['_buddyitem_pass'] ) ) {

			$buddyitem = buddydrive_get_buddyfile( $_REQUEST['_buddyitem_id'], array( buddydrive_get_file_post_type(), buddydrive_get_folder_post_type() ) );

			if ( ! empty( $buddyitem->post_parent ) ) {
				$parent = buddydrive_get_buddyfile( $buddyitem->post_parent, buddydrive_get_folder_post_type() );
				$password_check = $parent->password;
			} else
				$password_check = $buddyitem->password;

			$password = ! empty( $password_check ) ? '<p>'.sprintf( __('Password : %s', 'buddydrive'), $password_check ) .'</p>' : false;

		}

		$message->message .= "\n" . $_POST['_buddyitem_link'] . "\n" . $password ;
	}

}
add_action( 'messages_message_before_save', 'buddydrive_update_message_content', 10, 1 );

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
	if ( $item->post_type != buddydrive_get_folder_post_type() )
		return;

	$parent_id = intval( $params['id'] );
	$metas = $params['metas'];

	$buddydrive_update_children = new BuddyDrive_Item();
	$buddydrive_update_children->update_children( $parent_id, $metas );

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

	if ( ! empty( $enable_group ) )
		groups_update_groupmeta( $group_id, '_buddydrive_enabled', 1 );
}
add_action( 'groups_group_create_complete', 'buddydrive_maybe_enable_group', 10, 1 );
