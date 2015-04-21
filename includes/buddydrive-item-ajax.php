<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Files are ajax uploaded !
 *
 * Adds some customization to the WordPress upload process.
 *
 * @uses check_admin_referer() for security reasons
 * @uses is_multisite() to check for multisite
 * @uses wp_handle_upload() to handle file upload
 * @uses bp_loggedin_user_id() to get current user id
 * @uses get_user_meta() to get some additional data about user (quota)
 * @uses update_user_meta() to update this data (quota)
 * @uses wp_kses() to sanitize content & pwd
 * @uses get_post_meta() to get some privacy data of the parent folder
 * @uses buddydrive_get_file_post_type() to get the BuddyFile post type
 * @uses buddydrive_save_item() to save the BuddyFile
 * @return int id of the the BuddyFile created
 */
function buddydrive_save_new_buddyfile() {

	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'buddydrive-form' );

	$output = '';
	$privacy = $group = $password = $parent = false;

	// In multisite, we need to remove some filters
	if ( is_multisite() ) {
		remove_filter( 'upload_mimes', 'check_upload_mimes' );
		remove_filter( 'upload_size_limit', 'upload_size_limit_filter' );
	}

	// temporarly overrides wp upload dir / wp mime types & wp upload size settings with BuddyDrive ones
	add_filter( 'upload_dir', 'buddydrive_temporarly_filters_wp_upload_dir', 10, 1);
	add_filter( 'upload_mimes', 'buddydrive_allowed_upload_mimes', 10, 1 );

	// Accents can be problematic.
	add_filter( 'sanitize_file_name', 'remove_accents', 10, 1 );

	$buddydrive_file = wp_handle_upload( $_FILES['buddyfile-upload'], array( 'action' => 'buddydrive_upload', 'upload_error_strings' => buddydrive_get_upload_error_strings() ) );

	if ( ! empty( $buddydrive_file ) && is_array( $buddydrive_file ) && empty( $buddydrive_file['error'] ) ) {
		/**
		 * file was uploaded !!
		 * Now we can create the buddydrive_file_post_type
		 *
		 */

		//let's take care of quota !
		$user_id = bp_loggedin_user_id();
		$user_total_space = get_user_meta( $user_id, '_buddydrive_total_space', true );
		$update_space = !empty( $user_total_space ) ? intval( $user_total_space ) + intval( $_FILES['buddyfile-upload']['size'] ) : intval( $_FILES['buddyfile-upload']['size'] );
		update_user_meta( $user_id, '_buddydrive_total_space', $update_space );


		$name = $_FILES['buddyfile-upload']['name'];
		$name_parts = pathinfo( $name );
		$name = trim( substr( $name, 0, -( 1 + strlen( $name_parts['extension'] ) ) ) );
		$content = !empty( $_POST['buddydesc'] ) ? wp_kses( $_POST['buddydesc'], array() ) : false;
		$meta = false;

		if ( ! empty( $_POST['buddyshared'] ) ) {

			$privacy  = ! empty( $_POST['buddyshared'] ) ? $_POST['buddyshared'] : 'private';
			$group    = ! empty( $_POST['buddygroup'] ) && 'groups' == $privacy ? $_POST['buddygroup'] : false;
			$password = ! empty( $_POST['buddypass'] ) ? wp_kses( $_POST['buddypass'], array() ) : false;

		}

		if ( ! empty( $_POST['buddyfolder'] ) ) {

			$parent = intval( $_POST['buddyfolder'] );

			$privacy = get_post_meta( $parent, '_buddydrive_sharing_option', true );

			if ( $privacy == 'groups' )
				$group = get_post_meta( $parent, '_buddydrive_sharing_groups', true );
		}

		$meta = new stdClass();

		$meta->privacy = $privacy;

		$meta->password = ! empty( $password ) ? $password : false ;

		$meta->groups = ! empty( $group ) ? $group : false;

		if ( ! empty( $_POST['customs'] ) ) {
			$meta->buddydrive_meta = json_decode( wp_unslash( $_POST['customs'] ) );
		}

		/*
		if the name is completely numeric, buddydrive_get_buddyfile
		will look for an  id instead of a post name, so to avoid this,
		we add a prefix to the name
		*/
		if ( is_numeric( $name ) )
			$name = 'f-' . $name;

		// Construct the buddydrive_file_post_type array
		$args = array(
			'type' => buddydrive_get_file_post_type(),
			'guid' => $buddydrive_file['url'],
			'title' => $name,
			'content' => $content,
			'mime_type' => $buddydrive_file['type'],
			'metas' => $meta
		);

		if ( ! empty( $parent ) )
			$args['parent_folder_id'] = $parent;

		$buddyfile_id = buddydrive_save_item( $args );

		echo $buddyfile_id;

	} else {
		echo '<div class="error-div"><a class="dismiss" href="#">' . __( 'Dismiss', 'buddydrive' ) . '</a><strong>' . sprintf( __( '&#8220;%s&#8221; has failed to upload due to an error : %s', 'buddydrive' ), esc_html( $_FILES['buddyfile-upload']['name'] ), $buddydrive_file['error'] ) . '</strong><br /></div>';
	}


	// let's restore wp upload dir settings !
	remove_filter( 'upload_dir', 'buddydrive_temporarly_filters_wp_upload_dir', 10, 1);
	remove_filter( 'upload_mimes', 'buddydrive_allowed_upload_mimes', 10, 1 );

	// Stop filtering.
	remove_filter( 'sanitize_file_name', 'remove_accents', 10, 1 );

	die();
}
add_action( 'wp_ajax_buddydrive_upload', 'buddydrive_save_new_buddyfile' );


/**
 * Gets the latest created file once uploaded
 *
 * Fixes IE shit
 *
 * @uses the BuddyDrive loop to return the file created [description]
 * @uses buddydrive_get_template() to get the template needed on BP Default or other themes
 * @return string html (the table row)
 */
function buddydrive_fetch_created_file() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	$buddyfile_id = intval( $_POST['createdid'] );

	if ( ! empty( $buddyfile_id ) ) {
		if ( buddydrive_has_items ( 'id=' . $buddyfile_id ) ) {
			while ( buddydrive_has_items() ) {
				buddydrive_the_item();
				bp_get_template_part( 'buddydrive-entry' );
			}
		}
	}

	die();
}
add_action( 'wp_ajax_buddydrive_fetchfile', 'buddydrive_fetch_created_file' );


/**
 * Create a folder thanks to Ajax
 *
 * @uses check_admin_referer() for security reasons
 * @uses wp_kses() to sanitize pwd and title
 * @uses buddydrive_get_folder_post_type() to get folder post type
 * @uses buddydrive_save_item() to save the folder
 * @uses the BuddyDrive loop to retrieve the folder created
 * @uses buddydrive_get_template() to get the template for bp-default or any theme
 * @return string the folder created
 */
function buddydrive_save_new_buddyfolder() {

	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'buddydrive_actions', '_wpnonce_buddydrive_actions' );

	if( ! empty( $_POST['title'] ) ) {
		$buddydrive_title = $_POST['title'];

		if( ! empty( $_POST['sharing_option'] ) ) {
			$meta = new stdClass();

			$meta->privacy = $_POST['sharing_option'];

			if ( $_POST['sharing_option'] == 'password' )
				$meta->password = wp_kses( $_POST['sharing_pass'], array() );

			if ( $_POST['sharing_option'] == 'groups' )
				$meta->groups = $_POST['sharing_group'];
		}


		$args = array(
			'type'  => buddydrive_get_folder_post_type(),
			'title' => wp_kses( $buddydrive_title, array() ),
			'content' => '',
			'metas' => $meta
		);

		$buddyfolder_id = buddydrive_save_item( $args );

		if ( buddydrive_has_items ( 'id=' . $buddyfolder_id ) ) {
			while ( buddydrive_has_items() ) {
				buddydrive_the_item();
				bp_get_template_part( 'buddydrive-entry' );
			}
		}

	}

	die();
}
add_action( 'wp_ajax_buddydrive_createfolder', 'buddydrive_save_new_buddyfolder');


/**
 * Opens a folder and list the files attach to it depending on its privacy
 *
 * @uses buddydrive_get_buddyfile() to get the folder
 * @uses buddydrive_get_folder_post_type() to get the folder post type
 * @uses bp_is_active() to check if friends or groups components are actives
 * @uses friends_check_friendship() to check if current user is a friend of the folder owner
 * @uses groups_is_user_member() to check if the user is a member of the group the folder is attached to
 * @uses buddydrive_get_template() to get the template for bp-default or any theme
 * @return string the list of files
 */
function buddydrive_open_buddyfolder() {

	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	$buddyfolder_id = $_POST['folder'];
	$buddyfolder = buddydrive_get_buddyfile( $buddyfolder_id, buddydrive_get_folder_post_type() );
	$result = array();
	$access = false;
	$buddyscope = $_POST['scope'];

	if ( empty( $buddyfolder->ID ) ) {

		$result[] = '<tr id="no-buddyitems"><td colspan="5"><div id="message" class="info"><p>'. __( 'Sorry, this folder does not exist anymore.', 'buddydrive' ).'</p></div></td></tr>';

	} else {

		switch( $buddyfolder->check_for ) {
			case 'private' :
				$access = ( $buddyfolder->user_id == bp_loggedin_user_id() ) ? true : false;
				break;

			case 'public' :
				$access = true;
				break;

			case 'password' :
				$access = true;
				break;

			case 'friends' :
				if( ( bp_is_active( 'friends' ) && friends_check_friendship( $buddyfolder->user_id, bp_loggedin_user_id() ) ) || ( $buddyfolder->user_id == bp_loggedin_user_id() ) )
					$access = true;
				else
					$access = false;

				break;

			case 'groups' :
				if( bp_is_active( 'groups' ) && groups_is_user_member( bp_loggedin_user_id(), intval( $buddyfolder->group ) ) )
					$access = true;
				else if( $buddyfolder->user_id == bp_loggedin_user_id() )
					$access = true;
				else if( is_super_admin() )
					$access = true;
				else
					$access = false;
				break;
		}

		if ( ! empty( $access ) || bp_current_user_can( 'bp_moderate' ) ) {

			ob_start();
			bp_get_template_part( 'buddydrive-loop' );
			$result[] = ob_get_contents();
			ob_end_clean();

		} else {

			$result[] = '<tr id="no-access"><td colspan="5"><div id="message" class="info"><p>'. __( 'Sorry, this folder is private', 'buddydrive' ).'</p></div></td></tr>';

		}

		$name_required = !empty( $_POST['foldername'] ) ? 1 : 0;

		if ( ! empty( $name_required ) ) {
			$result[] = $buddyfolder->title;
		}

	}

	echo json_encode( $result );

	die();
}
add_action( 'wp_ajax_buddydrive_openfolder', 'buddydrive_open_buddyfolder');
add_action( 'wp_ajax_nopriv_buddydrive_openfolder', 'buddydrive_open_buddyfolder');


/**
 * Loads more files or folders in the BuddyDrive "explorer"
 *
 * @uses buddydrive_get_template() to load the BuddyDrive loop
 * @return string more items if there are some
 */
function buddydrive_load_more_items() {

	ob_start();
	bp_get_template_part( 'buddydrive-loop' );
	$result = ob_get_contents();
	ob_end_clean();

	echo $result;

	die();
}
add_action( 'wp_ajax_buddydrive_loadmore', 'buddydrive_load_more_items' );
add_action( 'wp_ajax_nopriv_buddydrive_loadmore', 'buddydrive_load_more_items' );
add_action( 'wp_ajax_buddydrive_filterby', 'buddydrive_load_more_items' );
add_action( 'wp_ajax_nopriv_buddydrive_filterby', 'buddydrive_load_more_items' );


/**
 * Same as previous except it's for the admin part of the plugin
 *
 * @uses buddydrive_admin_edit_files_loop() to list the files
 * @return string more files if there are any
 */
function buddydrive_admin_ajax_loadmore() {
	$folder_id = ! empty( $_POST['folder'] ) ? intval( $_POST['folder'] ) : -1;
	$paged     = ! empty( $_POST['page'] ) ? intval( $_POST['page'] ) : -1;

	ob_start();
	buddydrive_admin_edit_files_loop( $folder_id, $paged );
	$result = ob_get_contents();
	ob_end_clean();

	echo $result;

	die();
}
add_action( 'wp_ajax_buddydrive_adminloadmore', 'buddydrive_admin_ajax_loadmore');


/**
 * Displays a list of the user's group where BuddyDrive is activated
 *
 * @uses bp_loggedin_user_id() to get current user id
 * @uses buddydrive_get_select_user_group() to build the select box
 * @return string a select box
 */
function buddydrive_list_user_groups() {

	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( ! bp_is_active('groups') )
		exit();

	$user_id  = ! empty( $_POST['userid'] ) ? intval( $_POST['userid'] ) : bp_loggedin_user_id() ;
	$group_id = ! empty( $_POST['groupid'] ) ? intval( $_POST['groupid'] ) : false ;
	$name     = ! empty( $_POST['selectname'] ) ? $_POST['selectname'] : false ;


	$output = buddydrive_get_select_user_group( $user_id, $group_id, $name );

	echo $output;
	die();
}
add_action( 'wp_ajax_buddydrive_getgroups', 'buddydrive_list_user_groups' );


/**
 * Ajax deletes folder or files
 *
 * @uses check_admin_referer() for security reasons
 * @uses buddydrive_delete_item() deletes the post_type (file or folder)
 * @return array the result with the item ids deleted
 */
function buddydrive_delete_items() {

	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'buddydrive_actions', '_wpnonce_buddydrive_actions' );

	$items = $_POST['items'];

	$items = substr( $items, 0, strlen( $items ) - 1 );

	$items = explode( ',', $items );

	$items_nbre = buddydrive_delete_item( array( 'ids' => $items ) );

	if ( ! empty( $items_nbre) )
		echo json_encode( array( 'result' => $items_nbre, 'items' => $items ) );
	else
		echo json_encode( array( 'result' => 0 ) );

	die();

}
add_action( 'wp_ajax_buddydrive_deleteitems', 'buddydrive_delete_items');


/**
 * Loads a form to edit a file or a folder
 *
 * @uses buddydrive_get_buddyfile() to get the item to edit
 * @uses buddydrive_get_folder_post_type() to get the BuddyFolder post type
 * @uses buddydrive_get_file_post_type() to get the BuddyFile post type
 * @uses wp_kses() to sanitize data
 * @uses buddydrive_select_sharing_options() to display the privacy choices
 * @uses buddydrive_select_user_group() to display the groups available
 * @uses buddydrive_select_folder_options() to display the available folders
 * @return string the edit form
 */
function buddydrive_edit_form() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	$item_id = !empty( $_POST['buddydrive_item'] ) ? intval( $_POST['buddydrive_item'] ) : false ;

	if ( ! empty( $item_id ) ) {
		$item  = buddydrive_get_buddyfile( $item_id, array( buddydrive_get_folder_post_type(), buddydrive_get_file_post_type() ) );
		?>
		<form class="standard-form" id="buddydrive-item-edit-form">

			<div id="buddyitem-description">
				<input type="hidden" id="buddydrive-item-id" value="<?php echo $item->ID;?>">
				<label for="buddydrive-item-title"><?php esc_html_e( 'Name', 'buddydrive' );?></label>
				<input type="text" name="buddydrive-edit[item-title]" id="buddydrive-item-title" value="<?php echo esc_attr( stripslashes( $item->title ) ) ?>" />

				<?php if ( $item->post_type == buddydrive_get_file_post_type() ) :?>
					<label for="buddydrive-item-content"><?php esc_html_e( 'Description', 'buddydrive' );?></label>
					<textarea name="buddydrive-edit[item-content]" id="buddydrive-item-content" maxlength="140"><?php echo wp_kses( stripslashes( $item->content ), array() );?></textarea>
					<?php if ( has_action( 'buddydrive_uploader_custom_fields' ) ) :?>
						<div id="buddydrive-custom-step-edit" class="buddydrive-step hide">
							<?php do_action( 'buddydrive_uploader_custom_fields', $item->ID ) ;?>
						</div>
					<?php endif;?>
				<?php endif;?>

			</div>

			<?php if ( empty( $item->post_parent ) ) :?>

				<div id="buddydrive-privacy-section-options">
					<label for="buddydrive-sharing-option"><?php esc_html_e( 'Item Sharing options', 'buddydrive' );?></label>
					<?php buddydrive_select_sharing_options( 'buddyitem-sharing-options', $item->check_for, 'buddydrive-edit[sharing]' );?>

					<div id="buddydrive-admin-privacy-detail">
						<?php if( $item->check_for == 'password' ):?>
							<label for="buddydrive-password"><?php esc_html_e( 'Password', 'buddydrive' );?></label>
							<input type="text" value="<?php echo esc_attr( stripslashes( $item->password ) ) ?>" name="buddydrive-edit[password]" id="buddypass"/>
						<?php elseif( $item->check_for == 'groups' ) :?>
							<label for="buddygroup"><?php esc_html_e( 'Choose the group', 'buddydrive' );?></label>
							<?php buddydrive_select_user_group( $item->user_id, $item->group, 'buddydrive-edit[buddygroup]' );?>
						<?php endif;?>
					</div>
				</div>

			<?php else :?>

				<div id="buddyitem-sharing-option">
					<label for="buddydrive-sharing-option"><?php esc_html_e( 'Edit your sharing options', 'buddydrive' );?></label>
					<p><?php esc_html_e( 'Privacy of this item rely on its parent folder', 'buddydrive' );?></p>
				</div>

			<?php endif;?>

			<?php if ( $item->post_type == buddydrive_get_file_post_type() ) :?>

				<div class="buddyitem-folder-section" id="buddyitem-folder-section-options">
					<label for="buddyitem-folder-option"><?php esc_html_e( 'Folder', 'buddydrive' );?></label>
					<?php buddydrive_select_folder_options( $item->user_id, $item->post_parent, 'buddydrive-edit[folder]' );?>
				</div>

			<?php endif;?>

			<p class="buddydrive-action folder"><input type="submit" value="<?php esc_html_e('Edit Item', 'buddydrive');?>" name="buddydrive_edit[submit]">&nbsp;<a href="#" class="cancel-item button"><?php esc_html_e( 'Cancel', 'buddydrive' );?></a></p>

		</form>
		<?php
	}
	die();

}
add_action( 'wp_ajax_buddydrive_editform', 'buddydrive_edit_form' );


/**
 * Updates an item
 *
 * @uses check_admin_referer() for security reasons
 * @uses buddydrive_get_buddyfile() to get the item
 * @uses buddydrive_get_folder_post_type() to get the BuddyFolder post type
 * @uses buddydrive_get_file_post_type() to get the BuddyFile post type
 * @uses wp_kses() to sannitize data
 * @uses buddydrive_update_item() to update the item (folder or file)
 * @uses the BuddyDrive Loop to get the item updated
 * @uses buddydrive_get_template() to get the template for bp-default or any theme
 * @return array containing the updated item
 */
function buddydrive_ajax_update_item(){
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'buddydrive_actions', '_wpnonce_buddydrive_actions' );

	$item_id = intval( $_POST['id'] );

	$item = buddydrive_get_buddyfile( $item_id, array( buddydrive_get_folder_post_type(), buddydrive_get_file_post_type() ) );

	if ( empty( $item->title ) ) {
		echo json_encode(array(0));
		die();
	}

	$args = array();

	if ( ! empty( $_POST['title'] ) )
		$args['title'] = wp_kses( $_POST['title'], array() );
	if ( ! empty( $_POST['content'] ) )
		$args['content'] = wp_kses( $_POST['content'], array() );
	if ( ! empty( $_POST['sharing'] ) )
		$args['privacy'] = $_POST['sharing'];
	if ( ! empty( $_POST['password'] ) )
		$args['password'] = wp_kses( $_POST['password'], array() );
	if ( ! empty( $_POST['group'] ) )
		$args['group'] = $_POST['group'];

	$args['parent_folder_id'] = !empty( $_POST['folder'] ) ? intval( $_POST['folder'] ) : 0 ;

	// We need to check if the parent folder is attached to a group.
	if ( ! empty( $args['parent_folder_id'] ) ) {
		$maybe_in_group = get_post_meta( $args['parent_folder_id'], '_buddydrive_sharing_groups', true );

		if ( ! empty( $maybe_in_group ) )
			$args['group'] = intval( $maybe_in_group );
	}

	if ( ! empty( $_POST['customs'] ) ) {
		$args['buddydrive_meta'] = json_decode( wp_unslash( $_POST['customs'] ) );
	}

	$updated = buddydrive_update_item( $args, $item );

	$result = array();

	if ( ! empty( $updated ) ) {

		if ( buddydrive_has_items ( 'id=' . $updated ) ) {
			ob_start();
			while ( buddydrive_has_items() ) {
				buddydrive_the_item();
				bp_get_template_part( 'buddydrive-entry', false );
			}
			$result[] = ob_get_contents();
			ob_end_clean();
		}
		$result[] = $args['parent_folder_id'];
		echo json_encode($result);
	}
	else
		echo json_encode(array(0));

	die();
}
add_action( 'wp_ajax_buddydrive_updateitem', 'buddydrive_ajax_update_item' );


/**
 * Post an activity to the group
 *
 * @uses check_admin_referer() for security reasons
 * @uses bp_loggedin_user_id() to get the current user id
 * @uses buddydrive_get_folder_post_type() to get the BuddyFolder post type
 * @uses buddydrive_get_file_post_type() to get the BuddyFile post type
 * @uses get_post_meta() to get item extra data (privacy)
 * @uses buddydrive_get_buddyfile() to get item
 * @uses groups_get_group() to get the group
 * @uses bp_core_get_userlink() to get link to user's profile
 * @uses buddydrive_get_name() so that it's possible to brand the plugin
 * @uses bp_get_group_permalink() to build the group permalink
 * @uses groups_record_activity() to finaly record the activity
 * @return int 1 or string an error message
 */
function buddydrive_share_in_group() {

	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'buddydrive_actions', '_wpnonce_buddydrive_actions' );

	$buddyitem = intval( $_POST['itemid'] );

	if ( empty( $buddyitem ) ) {
		_e( 'this is embarassing, it did not work :(', 'buddydrive' );
		die();
	}

	if ( ! bp_is_active( 'groups' ) ) {
		_e( 'Group component is deactivated, please contact the administrator.', 'buddydrive' );
		die();
	}

	$link = $_POST['url'] ;
	$result = false;
	$user_id = bp_loggedin_user_id();
	$item_type = ( 'folder' == $_POST['itemtype'] ) ? buddydrive_get_folder_post_type() : buddydrive_get_file_post_type();

	if ( ! empty( $buddyitem ) ) {
		$group_id = get_post_meta( $buddyitem, '_buddydrive_sharing_groups', true );

		if ( empty( $group_id ) ) {
			$buddyfile = buddydrive_get_buddyfile( $buddyitem, $item_type );
			$parent_id = $buddyfile->post_parent;
			$group_id = get_post_meta( $parent_id, '_buddydrive_sharing_groups', true );
		}

		if ( ! empty( $group_id ) ) {
			$group = groups_get_group( array( 'group_id' => $group_id ) );

			$action  = $activity_action  = sprintf( __( '%1$s shared a %2$s Item in the group %3$s', 'buddydrive'), bp_core_get_userlink( $user_id ), esc_html( buddydrive_get_name() ), '<a href="' . esc_url( bp_get_group_permalink( $group ) ) . '">' . esc_html( $group->name ) . '</a>' );
			$content = $link;
			$args = array(
					'user_id'   => $user_id,
					'action'    => $action,
					'content'   => $content,
					'type'      => 'activity_update',
					'component' => 'groups',
					'item_id'   => $group_id
			);

			$result = groups_record_activity( $args );
		}

	}
	if ( ! empty( $result ) )
		echo 1;
	else
		_e( 'this is embarassing, it did not work :(', 'buddydrive' );
	die();

}
add_action( 'wp_ajax_buddydrive_groupupdate', 'buddydrive_share_in_group' );


/**
 * Post an activity in user's profile
 *
 * @uses check_admin_referer() for security reasons
 * @uses bp_loggedin_user_id() to get the current user id
 * @uses buddydrive_get_folder_post_type() to get the BuddyFolder post type
 * @uses buddydrive_get_name() so that it's possible to brand the plugin
 * @uses buddydrive_get_file_post_type() to get the BuddyFile post type
 * @uses buddydrive_get_buddyfile() to get item
 * @uses bp_core_get_userlink() to get link to user's profile
 * @uses bp_activity_add() to finaly record the activity without updating the latest meta
 * @return int 1 or string an error message
 */
function buddydrive_share_in_profile() {

	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'buddydrive_actions', '_wpnonce_buddydrive_actions' );

	$buddyitem = intval( $_POST['itemid'] );

	if ( empty( $buddyitem ) ) {
		_e( 'this is embarassing, it did not work :(', 'buddydrive' );
		die();
	}

	$link = $_POST['url'] ;
	$result = false;
	$user_id = bp_loggedin_user_id();
	$item_type = ( 'folder' == $_POST['itemtype'] ) ? buddydrive_get_folder_post_type() : buddydrive_get_file_post_type();

	if ( ! empty( $buddyitem ) ) {

		$buddyfile = buddydrive_get_buddyfile( $buddyitem, $item_type );

		if ( empty( $buddyfile->ID ) || $buddyfile->check_for != 'public' ) {
			// no item or not a public one ??
			_e( 'We could not find your BuddyDrive item or its privacy is not set to public', 'buddydrive');
			die();
		}

		$action  = sprintf( __( '%1$s shared a %2$s Item', 'buddydrive'), bp_core_get_userlink( $user_id ), buddydrive_get_name() );
		$content = $link;
		$args = array(
				'user_id'      => $user_id,
				'action'       => $action,
				'content'      => $content,
				'primary_link' => bp_core_get_userlink( $user_id, false, true ),
				'component'    => 'activity',
				'type'         => 'activity_update'
		);

		$result = bp_activity_add( $args );

	}
	if ( ! empty( $result ) )
		echo 1;
	else
		echo _e( 'this is embarassing, it did not work :(', 'buddydrive' );
	die();

}
add_action( 'wp_ajax_buddydrive_profileupdate', 'buddydrive_share_in_profile' );


/**
 * Updates the display of the quota of the current user
 *
 * @uses buddydrive_get_user_space_left() to get the quota
 * @return string the quota
 */
function buddydrive_update_quota(){
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	add_filter( 'buddydrive_get_user_space_left', 'buddydrive_filter_user_space_left', 10, 2 );

	echo buddydrive_get_user_space_left();

	remove_filter( 'buddydrive_get_user_space_left', 'buddydrive_filter_user_space_left', 10, 2 );

	die();
}
add_action( 'wp_ajax_buddydrive_updatequota', 'buddydrive_update_quota');


/**
 * Filters the querystring
 *
 * Ajax Scope is admin, so we need to uses this trick to have the data we're requesting
 *
 * @param  array $args the arguments of the BuddyDrive query
 * @return array the merge $args with post args
 */
function buddydrive_ajax_querystring( $args = false ) {
	$args = array();

	$filter = ! empty( $_COOKIE['buddydrive_filter'] ) ? $_COOKIE['buddydrive_filter'] : false;

	if ( ! empty( $_POST['buddydrive_filter'] ) ) {
		$filter = $_POST['buddydrive_filter'];
	}

	if ( ! empty( $filter ) ) {
		switch( $filter ) {
			case 'modified' :
				$args['orderby'] = 'modified';
				$args['order']   = 'DESC';
			break;
			default:
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
			break;
		}
	}

	if( !empty( $_POST['page'] ) )
		$args['paged'] = $_POST['page'];

	if( !empty( $_POST['folder'] ) )
		$args['buddydrive_parent'] = $_POST['folder'];

	if( !empty( $_POST['exclude'] ) )
		$args['exclude'] = $_POST['exclude'];

	if( !empty( $_POST['scope'] ) ) {
		$args['buddydrive_scope'] = $_POST['scope'];

		if( $args['buddydrive_scope'] == 'groups' && !empty( $_POST['group'] ) )
			$args['group_id'] = $_POST['group'];
	}

	return $args;
}
add_filter( 'buddydrive_querystring', 'buddydrive_ajax_querystring', 1, 1 );


/**
 * Removes an item from the group (group admins may wish to)
 *
 * @uses check_admin_referer() for security reasons
 * @uses buddydrive_remove_item_from_group() to unattached the file or folder from the group
 * @return int the result
 */
function buddydrive_remove_from_group() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'buddydrive_actions', '_wpnonce_buddydrive_actions' );

	$item_id = $_POST['itemid'];
	$group_id = $_POST['groupid'];

	if ( empty( $item_id ) || empty( $group_id ) )
		echo 0;
	else {
		$removed = buddydrive_remove_item_from_group( $item_id, $group_id );
		echo $removed;
	}

	die();

}
add_action( 'wp_ajax_buddydrive_removefromgroup', 'buddydrive_remove_from_group' );
