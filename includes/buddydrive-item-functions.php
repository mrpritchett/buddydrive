<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Populates the translation array for js messages
 *
 * @return array the js translation
 */
function buddydrive_get_js_l10n() {
	$buddydrivel10n = array(
		'one_at_a_time'        => __( 'Please, add only one file at a time', 'buddydrive' ),
		'loading'              => __( 'loading..', 'buddydrive' ),
		'shared'               => __( 'Shared', 'buddydrive' ),
		'group_remove_error'   => __( 'Error: Item could not be removed from current group', 'buddydrive' ),
		'cbs_message'          => __( 'Please use the checkboxes to select one or more items', 'buddydrive' ),
		'cb_message'           => __( 'Please use the checkbox to select one item to edit', 'buddydrive' ),
		'confirm_delete'       => __( 'Are you sure you want to delete %d item(s) ?', 'buddydrive' ),
		'delete_error_message' => __( 'Error: Item(s) could not be deleted', 'buddydrive' ),
		'title_needed'         => __( 'The title is required', 'buddydrive' ),
		'group_needed'         => __( 'Please choose a group in the list', 'buddydrive' ),
		'pwd_needed'           => __( 'Please choose a password', 'buddydrive' ),
		'define_pwd'           => __( 'Define your password', 'buddydrive' ),
		'label_pwd'            => __( 'Password', 'buddydrive' ),
		'label_group'          => __( 'Choose the group', 'buddydrive' ),
	);

	return $buddydrivel10n;
}

/**
 * Displays the user's BuddyDrive root url or a link to it
 *
 * @param  boolean $user_id the id of the user
 * @uses buddydrive_get_user_buddydrive_url() to get the user's BuddyDrive url
 * @return string outputs the link to user's BuddyDrive
 */
function buddydrive_user_buddydrive_url( $linkonly = false ) {
	$url = buddydrive_get_user_buddydrive_url();

	if( ! empty($linkonly ) ) {
		echo $url;
	} else {
		$output = apply_filters( 'buddydrive_user_buddydrive_url', '<a href="'. esc_url( $url ) .'" title="' . esc_attr__( 'Choose or add a file from my profile', 'buddydrive' ) .'" class="buddydrive-profile"><i class="icon bd-icon-newfile"></i> ' . esc_html__( 'Manage files', 'buddydrive' ) .'</a>' );
		echo $output;
	}

}

/**
 * Builds the user's BuddyDrive root url
 *
 * @param  integer $user_id the id of the user
 * @uses bp_displayed_user_id() to get the displayed user id
 * @uses bp_loggedin_user_id() to get the current user id
 * @uses bp_core_get_user_domain to get the user's home page link
 * @uses buddydrive_get_slug() to get the slug of BuddyDrive
 * @return string $buddydrive_link the link to user's BuddyDrive
 */
function buddydrive_get_user_buddydrive_url( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		$displayed_user_id = bp_displayed_user_id();
		$user_id = !empty( $displayed_user_id ) ? $displayed_user_id : bp_loggedin_user_id();
	}

	$user_domain = bp_core_get_user_domain( $user_id );

	$buddydrive_link = trailingslashit( $user_domain . buddydrive_get_slug() );

	return $buddydrive_link;
}


/**
 * Builds the BuddyDrive Group url
 *
 * @since 2.0.0 Add the User ID Parameter
 *
 * @param integer $group_id the group id
 * @param integer $user_id the User ID
 *
 * @return string $buddydrive_link the link to user's BuddyDrive
 */
function buddydrive_get_group_buddydrive_url( $group_id = 0, $user_id = 0 ) {
	$buddydrive_link = false;

	if ( bp_is_group() ) {
		$group = groups_get_current_group();

	} elseif ( ! empty( $group_id ) ) {
		if ( is_array( $group_id ) ) {
			/**
			 * Link to the user's BuddyDrive in case there is more than
			 * one group.
			 */
			if ( count( $group_id ) > 1 && ! empty( $user_id ) ) {
				return buddydrive_get_user_buddydrive_url( $user_id );

			// Take the first !
			} else {
				$group_id = reset( $group_id );
			}
		}

		$group = groups_get_group( array( 'group_id' => $group_id ) );
	}

	if ( ! empty( $group ) ) {
		$group_link = bp_get_group_permalink( $group );

		$buddydrive_link = trailingslashit( $group_link . buddydrive_get_slug() );
	}

	return $buddydrive_link;

}


/**
 * Builds the link to the Shared by friends BuddyDrive
 *
 * @param  integer $user_id the id of the user
 * @uses bp_displayed_user_id() to get displayed user id
 * @uses bp_core_get_user_domain() to get the user's home page url
 * @uses buddydrive_get_slug() to get BuddyDrive slug
 * @uses buddydrive_get_friends_subnav_slug() to get BuddyDrive's friends subnav
 * @return string  $buddydrive_friends the url to the shared by friends BuddyDrive
 */
function buddydrive_get_friends_buddydrive_url( $user_id = 0 ) {
	if ( empty( $user_id ) )
		$user_id = bp_displayed_user_id();

	$user_domain = bp_core_get_user_domain( $user_id );

	$buddydrive_link = trailingslashit( $user_domain . buddydrive_get_slug() );

	$buddydrive_friends = trailingslashit( $buddydrive_link . buddydrive_get_friends_subnav_slug() );

	return $buddydrive_friends;
}

/**
 * Are we on a group's BuddyDrive ?
 *
 * @uses bp_is_groups_component() to check we're on the group component
 * @uses bp_is_single_item() to check we're in a single group
 * @uses bp_is_current_action() to check the acction is BuddyDrive
 * @return boolean true or false
 */
function buddydrive_is_group() {
	if ( bp_is_groups_component() && bp_is_single_item() && bp_is_current_action( buddydrive_get_slug() ) )
		return true;

	else return false;
}


/**
 * Are we on current user's BuddyDrive
 *
 * @uses is_user_logged_in() to check we have a loggedin user
 * @uses bp_is_my_profile() to check the current user is on his profile
 * @uses bp_current_action() to check he's on his BuddyDrive
 * @return boolean true or false
 */
function buddydrive_is_user_buddydrive() {
	if ( is_user_logged_in() && bp_is_my_profile() && bp_current_action() == 'files' )
		return true;

	else
		return false;
}

/**
 * Holds the variables we need while using ajax
 *
 * @return array the args to pass to the BuddyDrive Loop
 */
function buddydrive_querystring() {
	return apply_filters( 'buddydrive_querystring', array() );
}

/**
 * Update a user's upload space
 *
 * @since  1.3.0
 *
 * @param  int     $user_id  the ID of the user
 * @param  int     $bytes    the number of bytes to add to user's space
 * @return bool              true on success, false otherwise
 */
function buddydrive_update_user_space( $user_id = 0, $bytes = 0 ) {
	if ( empty( $user_id ) || empty( $bytes ) ) {
		return false;
	}

	// Get the user's uploaded bytes
	$user_total_space = get_user_meta( $user_id, '_buddydrive_total_space', true );

	if ( ! empty( $user_total_space ) ) {
		$user_total_space = intval( $user_total_space ) + intval( $bytes );
	} else {
		$user_total_space = intval( $bytes );
	}

	// no negative space!
	if ( $user_total_space < 0 ) {
		delete_user_meta( $user_id, '_buddydrive_total_space' );

	// Update user's space
	} else {
		update_user_meta( $user_id, '_buddydrive_total_space', $user_total_space );
	}

	return true;
}

/**
 * Upload a file
 *
 * @since  1.3.0
 *
 * @param  array   $file    the $_FILES var
 * @param  int     $user_id the ID of the user submitting the file
 * @return array            the upload result
 */
function buddydrive_upload_item( $file = array(), $user_id = 0 ) {
	if ( empty( $file ) || empty( $user_id ) ) {
		return false;
	}

	// In multisite, we need to remove some filters
	if ( is_multisite() ) {
		remove_filter( 'upload_mimes',      'check_upload_mimes'       );
		remove_filter( 'upload_size_limit', 'upload_size_limit_filter' );
	}

	// Accents can be problematic.
	add_filter( 'sanitize_file_name', 'remove_accents', 10, 1 );

	$buddydrive_attachment = new BuddyDrive_Attachment();
	$upload                = $buddydrive_attachment->upload( $file );

	// Restore/remove filters
	if ( is_multisite() ) {
		add_filter( 'upload_mimes',      'check_upload_mimes'       );
		add_filter( 'upload_size_limit', 'upload_size_limit_filter' );
	}

	// Others can deal with Accents in filename the way they want.
	remove_filter( 'sanitize_file_name', 'remove_accents', 10, 1 );

	$action_suffix = '_failed';

	/**
	 * file was uploaded !!
	 * Now we can update the user's space
	 */
	if ( isset( $upload['file'] ) && empty( $upload['error'] ) ) {
		$action_suffix = '_succeeded';

		buddydrive_update_user_space( $user_id, $file['buddyfile-upload']['size'] );
	}

	/**
	 * Allow actions once the file is processed
	 *
	 * Use buddydrive_upload_item_failed to do actions in case the file was not uploaded
	 * Use buddydrive_upload_item_succeeded to do actions in case the file was uploaded
	 *
	 * @since 1.3
	 *
	 * @param array $upload upload results
	 * @param array $file the file before being moved to upload dir
	 * @param int   $user_id the ID of the user who uploaded the file.
	 */
	do_action( "buddydrive_upload_item{$action_suffix}", $upload, $file, $user_id );

	return $upload;
}


/**
 * Saves or Updates a BuddyDrive item
 *
 * @param  array $args the different argument of the item to save
 * @uses bp_loggedin_user_id() to default to current user id
 * @uses wp_parse_args() to merge defaults and args array
 * @uses BuddyDrive_Item::save() to save data in DB
 * @return int the item id
 */
function buddydrive_save_item( $args = '' ) {

	$defaults = array(
		'id'               => false,
		'type'             => '',
		'user_id'          => bp_loggedin_user_id(),
		'parent_folder_id' => 0,
		'title'            => false,
		'content'          => false,
		'mime_type'        => false,
		'guid'             => false,
		'metas'            => false,
	);

	$params = wp_parse_args( $args, $defaults );

	// Setup item to be added
	$buddydrive_item                   = new BuddyDrive_Item();
	$buddydrive_item->id               = (int) $params['id'];
	$buddydrive_item->type             = $params['type'];
	$buddydrive_item->user_id          = (int) $params['user_id'];
	$buddydrive_item->parent_folder_id = (int) $params['parent_folder_id'];
	$buddydrive_item->title            = $params['title'];
	$buddydrive_item->content          = $params['content'];
	$buddydrive_item->mime_type        = $params['mime_type'];
	$buddydrive_item->guid             = $params['guid'];
	$buddydrive_item->metas            = $params['metas'];

	if ( ! $buddydrive_item->save() ) {
		return false;
	}

	do_action( 'buddydrive_save_item', $buddydrive_item->id, $params );

	return $buddydrive_item->id;
}

/**
 * Add an item (folder or file) to the database
 *
 * @since 2.0.0
 *
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function buddydrive_add_item( $args = array() ) {
	$params = bp_parse_args( $args, array(
		'type'             => '',
		'user_id'          => bp_loggedin_user_id(),
		'parent_folder_id' => 0,
		'title'            => '',
		'content'          => '',
		'mime_type'        => false,
		'guid'             => '',
		'customs'          => false,
		'privacy'          => 'private',
		'groups'           => array(),
		'password'         => '',
	), 'buddydrive_add_item' );

	if ( empty( $params['type'] ) || empty( $params['title'] ) || empty( $params['user_id'] ) ) {
		return false;
	}

	// Init meta
	$meta = new stdClass();

	// Defaults to private
	if ( empty( $params['privacy'] ) ) {
		$meta->privacy  = 'private';
	} else {
		$meta->privacy  = $params['privacy'];
	}

	if ( ! empty( $params['parent_folder_id'] ) ) {
		$parent = (int) $params['parent_folder_id'];
		$meta->privacy = buddydrive_get_privacy( $parent );
	}

	if ( 'password' === $meta->privacy ) {
		if ( isset( $parent ) ) {
			$meta->password = get_post_field( 'post_password', $parent );
		} elseif ( ! empty( $params['password'] ) ) {
			$meta->password = $params['password'];
		} else {
			$meta->privacy = 'private';
		}
	}

	if ( 'groups' === $meta->privacy ) {
		if ( isset( $parent ) ) {
			$meta->groups = get_post_meta( $parent, '_buddydrive_sharing_groups' );
		} else if ( ! empty( $params['groups'] ) ) {
			$meta->groups  = wp_parse_id_list( $params['groups'] );
		} else {
			$meta->privacy = 'private';
		}
	}

	if ( ! empty( $params['customs'] ) ) {
		$meta->buddydrive_meta = $params['customs'];
	}

	if ( is_numeric( $params['title'] ) ) {
		$params['title'] = 'f-' . $name;
	}

	// Sanitize meta
	if ( isset( $meta->password ) ) {
		$meta->password = wp_kses( $meta->password, array() );
	}

	// Save the item
	$item_id = buddydrive_save_item( array(
		'type'             => $params['type'],
		'user_id'          => (int) $params['user_id'],
		'parent_folder_id' => (int) $params['parent_folder_id'],
		'title'            => wp_kses( $params['title'], array() ),
		'content'          => wp_kses( $params['content'], array() ),
		'mime_type'        => $params['mime_type'],
		'guid'             => esc_url_raw( $params['guid'] ),
		'metas'            => $meta,
	) );

	do_action( 'buddydrive_add_item', $item_id, $params, $args );

	return $item_id;
}


/**
 * Updates a BuddyDrive item
 *
 * @param array  $args the arguments to update
 * @param object $item the BuddyDrive item
 * @uses wp_parse_args() to merge defaults and args array
 * @uses buddydrive_get_file_post_type() to get the BuddyFile post type
 * @uses get_post_meta() to get privacy options
 * @uses buddydrive_save_item() to update the item
 * @return integer $modified the id of the item updated
 */
function buddydrive_update_item( $args = '', $item = false ) {

	if ( empty( $item ) ) {
		return false;
	}

	$old_pass = false;
	if ( ! empty( $item->password ) ) {
		$old_pass = $item->password;
	}

	$old_group = false;
	if ( ! empty( $item->group ) ) {
		$old_group = $item->group;
	}

	$defaults = array(
		'id'               => $item->ID,
		'type'             => $item->post_type,
		'user_id'          => $item->user_id,
		'parent_folder_id' => $item->post_parent,
		'title'            => $item->title,
		'content'          => $item->content,
		'mime_type'        => $item->mime_type,
		'guid'             => $item->guid,
		'privacy'          => $item->check_for,
		'password'         => $old_pass,
		'group'            => $old_group,
		'buddydrive_meta'  => false
	);

	$params = wp_parse_args( $args, $defaults );

	// if the parent folder was set, then we need to define a default privacy status
	if ( ! empty( $item->post_parent ) && empty( $params['parent_folder_id'] ) ) {
		$params['privacy'] = 'private';
	} elseif ( ! empty( $params['parent_folder_id'] ) && $params['type'] === buddydrive_get_file_post_type() ) {
		$params['privacy'] = buddydrive_get_privacy( $params['parent_folder_id'] );
	}

	// building the meta object
	$meta = new stdClass();

	$meta->privacy = $params['privacy'];

	// Delete the thumbnail if the public file became private
	if ( 'public' === $item->check_for && 'public' !== $meta->privacy ) {
		buddydrive_delete_thumbnail( $item->ID );
	}

	if ( $meta->privacy === 'password' ) {
		if ( ! empty( $params['password'] ) ) {
			$meta->password = $params['password'];
		} elseif ( ! empty( $params['parent_folder_id'] ) ) {
			$meta->password = get_post_field( 'post_password', $params['parent_folder_id'] );
		}
	}

	if ( $meta->privacy === 'groups' ) {
		if ( ! empty( $params['group'] ) ) {
			$meta->groups = $params['group'];
		} elseif ( ! empty( $params['parent_folder_id'] ) ) {
			$meta->groups = get_post_meta( $params['parent_folder_id'], '_buddydrive_sharing_groups' );
		}
	}

	if ( ! empty( $params['buddydrive_meta'] ) ) {
		$meta->buddydrive_meta = $params['buddydrive_meta'];
	}

	// preparing the args for buddydrive_save_item
	$params['metas'] = $meta;
	// we dont need privacy, password and group as it's in $meta
	unset( $params['privacy'] );
	unset( $params['password'] );
	unset( $params['group'] );

	$modified = buddydrive_save_item( $params );

	if ( empty( $modified ) ) {
		return false;
	}

	// Remove all groups if privacy changed
	if ( ! empty( $old_group ) && 'groups' !== $meta->privacy ) {
		delete_post_meta( $item->ID, '_buddydrive_sharing_groups' );
	}

	do_action( 'buddydrive_update_item', $params, $args, $item );

	return $modified;
}


/**
 * Deletes one or more BuddyDrive Item(s)
 *
 * @param array $args the argument ( the ids to delete and the user_id to check upon  )
 * @uses bp_loggedin_user_id() to default to current user id
 * @uses wp_parse_args() to merge defaults and args array
 * @uses BuddyDrive_Item::delete() to remove datas from DB and files from sysfile
 * @return integer|boolean the number of deleted items or false
 */
function buddydrive_delete_item( $args = '' ) {
	$defaults = array(
		'ids'      => false,
		'user_id'  => bp_loggedin_user_id()
	);

	$params = wp_parse_args( $args, $defaults );

	if ( ! empty( $params['ids'] ) && ! is_array( $params['ids'] ) ) {
		$params['ids'] = explode( ',', $params['ids'] );
	}

	$buddydrive_item = new BuddyDrive_Item();

	if ( $items = $buddydrive_item->delete( $params['ids'], $params['user_id'] ) ) {
		return $items;
	} else {
		return false;
	}
}


/**
 * Returns BuddyDrive items datas for an array of ids
 *
 * @param array $ids the list of BuddyDrive items ids
 * @uses BuddyDrive_Item::get_buddydrive_by_ids() to query the DB for items
 * @return array BuddyDrive items
 */
function buddydrive_get_buddyfiles_by_ids( $ids = array() ) {
	if ( empty( $ids ) )
		return false;

	$buddydrive_item = new BuddyDrive_Item();

	return $buddydrive_item->get_buddydrive_by_ids( $ids );
}


/**
 * Removes all the BuddyDrive Items from a group if it's about to be deleted
 *
 * @param integer $group_id the group id
 * @uses groups_get_group() to get a group object for the group id
 * @uses BuddyDrive_Item::group_remove_items() to delete the group id options for the BuddyDrive items
 * @return boolean true or false
 */
function buddydrive_remove_buddyfiles_from_group( $group_id = 0 ) {
	$new_privacy     = 'private';

	$group = groups_get_group( array( 'group_id' => $group_id ) );

	if ( isset( $group->status ) && 'public' === $group->status ) {
		$new_privacy = 'public';
	}

	$buddydrive_item = new BuddyDrive_Item();

	return $buddydrive_item->group_remove_items( $group_id, $new_privacy );
}
add_action( 'groups_before_delete_group', 'buddydrive_remove_buddyfiles_from_group', 1 );


/**
 * Gets a single BuddyDrive items
 *
 * @param string|int $name the post name or the id of the item to get
 * @param string $type the BuddyDrive post type
 * @uses buddydrive_get_file_post_type() to default to the BuddyFile post type
 * @uses BuddyDrive_Item::get() to get the BuddyDrive item
 * @uses buddydrive_get_root_url() to get BuddyDrive root url
 * @uses get_post_meta() to get item's privacy options
 * @return object the BuddyDrive item
 */
function buddydrive_get_buddyfile( $name = false, $type = false ) {
	if ( empty( $name ) ) {
		return false;
	}

	if ( empty( $type ) ) {
		$type = buddydrive_get_file_post_type();
	}

	$buddydrive_file = new BuddyDrive_Item();

	if ( is_numeric( $name ) ) {
		$args = array( 'id' => $name, 'type' => $type );
	} else {
		$args = array( 'name' => $name, 'type' => $type );
	}

	$buddydrive_file->get( $args );

	if ( empty( $buddydrive_file->query->post->ID ) ) {
		return false;
	}

	$buddyfile          = $buddydrive_file->query->post;
	$buddyfile->user_id = $buddyfile->post_author;
	$buddyfile->title   = $buddyfile->post_title;
	$buddyfile->content = $buddyfile->post_content;

	// do we have a file ?
	if ( $buddyfile->post_type === buddydrive_get_file_post_type() ) {
		$buddyitem_slug       = 'file';
		$buddyfile->file      = basename( $buddyfile->guid );
		$buddyfile->path      = buddydrive()->upload_dir .'/'. $buddyfile->file;
		$buddyfile->mime_type = $buddyfile->post_mime_type;

	// Then it must be a folder
	} else {
		$buddyitem_slug = $buddyfile->mime_type = 'folder';
	}

	$slug            = trailingslashit( $buddyitem_slug .'/' . $buddyfile->post_name );
	$link            = buddydrive_get_root_url() .'/'. $slug;
	$buddyfile->link = $link;

	/* privacy */
	$buddydrive_status = get_post_status_object( $buddyfile->post_status );
	if ( isset( $buddydrive_status->buddydrive_privacy ) ) {
		$privacy = $buddydrive_status->buddydrive_privacy;
	} else {
		$privacy = get_post_meta( $buddyfile->ID, '_buddydrive_sharing_option', true );
	}

	// by default check for user_id
	if ( empty( $privacy ) ) {
		$privacy = 'private';
	}

	$buddyfile->check_for = $privacy;
	$core_stati           = wp_list_pluck( buddydrive_get_stati( true ), 'label', 'buddydrive_privacy' );

	if ( 'password' === $privacy ) {
		$buddyfile->password = $buddyfile->post_password;
	} elseif ( 'groups' === $privacy ) {
		// Get all groups
		$buddyfile->group = get_post_meta( $buddyfile->ID, '_buddydrive_sharing_groups' );
	} elseif ( ! isset( $core_stati[ $privacy ] ) ) {
		/**
		 * Filter here for custom privacy options
		 *
		 * @since 1.3.3
		 *
		 * @param string $value     By default 'private'.
		 * @param object $buddyfile The BuddyDrive file object.
		 */
		$buddyfile->check_for = apply_filters( 'buddydrive_get_buddyfile_check_for', $buddyfile->check_for, $buddyfile );
	}

	return $buddyfile;
}


/**
 * Removes a single BuddyDrive items from group
 *
 * @param int $item_id  the BuddyDrive item id
 * @param int $group_id the group id
 * @uses groups_get_group() to get the group object for the given group_id
 * @uses BuddyDrive_Item::remove_from_group() to delete the options in the DBs
 * @return boolean true or false
 */
function buddydrive_remove_item_from_group( $item_id = false , $group_id = false ) {
	$new_privacy     = 'private';
	$buddydrive_item = new BuddyDrive_Item();

	$group = groups_get_group( array( 'group_id' => $group_id ) );

	if ( isset( $group->status ) && 'public' === $group->status ) {
		$new_privacy = 'public';
	}

	return $buddydrive_item->remove_from_group( $item_id, $new_privacy, $group_id );
}


/**
 * Handles an embed BuddyDrive item
 *
 * @param array $matches the result of the preg_match
 * @param array $attr
 * @param string $url
 * @param array $rawattr
 * @uses is_multisite() to check for multisite config
 * @uses bp_get_root_blog_id() to get the root blog id
 * @uses switch_to_blog() to change for root blog id
 * @uses buddydrive_get_buddyfile() to get the BuddyDrive Item
 * @uses buddydrive_get_file_post_type() to get the BuddyFile post type
 * @uses wp_mime_type_icon() to get the WordPress crystal icon
 * @uses buddydrive_get_folder_post_type() to get the BuddyFolder post type
 * @uses buddydrive_get_group_buddydrive_url() to build the url to the BuddyDrive group
 * @uses buddydrive_get_user_buddydrive_url() to get the user's BuddyDrive url
 * @uses buddydrive_get_images_url() to get the image url of the plugin
 * @uses the BuddyDrive Loop and some tempkate tags
 * @uses wp_reset_postdata() to avoid some weird link..
 * @uses restore_current_blog() to restore the child blog.
 * @return string $embed the html output
 */
function wp_embed_handler_buddydrive( $matches, $attr, $url, $rawattr ) {

	$link = $title = $icon = $content = $mime_type = $filelist = $hw_attr = false;
	$current_blog = get_current_blog_id();

	if ( is_multisite() && (int) $current_blog !== (int) bp_get_root_blog_id() ) {
		switch_to_blog( bp_get_root_blog_id() );
	}

	if ( $matches[1] == 'file' ) {
		$buddyfile = buddydrive_get_buddyfile( $matches[2], buddydrive_get_file_post_type() );

		if ( empty( $buddyfile ) ) {
			return '';
		}

		$link      = $buddyfile->link;
		$title     = $buddyfile->title;
		$content   = $buddyfile->content;
		$mime_type = $buddyfile->mime_type;
		$icon      = wp_mime_type_icon( $buddyfile->ID );

		if ( 'public' === $buddyfile->check_for ) {
			$thumbnail = buddydrive_get_thumbnail( $buddyfile->ID, 'thumburl', false );
			if ( ! empty( $thumbnail[0] ) ) {
				$icon = $thumbnail[0];
				$hw_attr = image_hwstring( $thumbnail[1], $thumbnail[2] );
			}
		}

	// It's a folfer
	} else {

		$buddyfile = buddydrive_get_buddyfile( $matches[2], buddydrive_get_folder_post_type() );

		if ( empty( $buddyfile ) ) {
			return '';
		}

		$buddydrive_root_link = ( $buddyfile->check_for == 'groups' ) ? buddydrive_get_group_buddydrive_url( $buddyfile->group ) : buddydrive_get_user_buddydrive_url( $buddyfile->user_id ) ;
		$link = $buddydrive_root_link .'?folder-'. $buddyfile->ID;
		$title = $buddyfile->title;
		$mime_type = $buddyfile->mime_type;
		$icon = buddydrive_get_images_url() . 'folder.png';
	}

	$embed = '<table style="width:auto"><tr>';

	$tdwidth = 'width:60px;';
	if ( ! empty( $hw_attr ) ) {
		$tdwidth = '';
	}

	$embed .= '<td style="vertical-align:middle;' . $tdwidth . '"><a href="' . esc_url( $link ) . '" title="' . esc_attr( $title ) . '"><img src="' . esc_url( $icon ) . '" alt="' . esc_attr( $mime_type ) . '" class="buddydrive-thumb" ' . $hw_attr . '></a></td>';
	$embed .= '<td style="vertical-align:middle"><h6 style="margin:0"><a href="' . esc_url( $link ) . '" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</a></h6>';

	if ( ! empty( $content ) ) {
		$embed .= '<p style="margin:0">'. esc_html( $content ). '</p>';
	}

	if ( $matches[1] == 'folder' ) {
		global $buddydrive_template;

		if ( buddydrive_has_items( array( 'buddydrive_parent' => $buddyfile->ID ) ) ) {
			$filelist = '<p style="margin-top:1em;margin-bottom:0">'. esc_html__( 'Files included in this folder :', 'buddydrive' ) .'</p><ul>';
			while ( buddydrive_has_items() ) {
				buddydrive_the_item();
				$filelist .= '<li><a href="' . esc_url( buddydrive_get_action_link() ) . '" title="' . esc_attr( buddydrive_get_item_title() ) . '">' . esc_html( buddydrive_get_item_title() ). '</a></li>';
			}
			$filelist .= '</ul>';
			$buddydrive_template = false;
		}
		wp_reset_postdata();
		$embed .= $filelist;

	}

	$embed .= '</td></tr></table>';

	if ( is_multisite() && (int) $current_blog !== (int) bp_get_root_blog_id() ) {
		restore_current_blog();
	}

	return apply_filters( 'embed_buddydrive', $embed, $matches, $attr, $url, $rawattr );
}

/**
 * Returns the user's quota
 *
 * First check for a user meta, if not set, fallback to user's role quota
 *
 * @since  version 1.1
 *
 * @param  integer $user_id the requested user's id
 * @global $wpdb the WordPress db class
 * @uses bp_loggedin_user_id() to get current user's id
 * @uses get_user_meta() to get user's preference
 * @uses bp_get_root_blog_id() to get the id of the blog where BuddyPress is activated
 * @uses bp_get_option() to get blog's preference
 * @return integer the user's quota
 */
function buddydrive_get_quota_by_user_id( $user_id = 0 ) {
	global $wpdb;

	if ( empty( $user_id ) )
		$user_id = bp_loggedin_user_id();

	$user_quota = intval( get_user_meta( $user_id, '_buddydrive_user_quota', true ) );

	if ( empty( $user_quota ) ) {
		// get's primary role for user
		$user_roles = get_user_meta( $user_id, $wpdb->get_blog_prefix( bp_get_root_blog_id() ) . 'capabilities', true );

		if ( ! empty( $user_roles ) ) {
			$user_roles = array_keys( $user_roles );
			$user_role  = is_array( $user_roles ) ? $user_roles[0] : bp_get_option('default_role');
		} else {
			$user_role = bp_get_option('default_role');
		}

		$option_user_quota = bp_get_option( '_buddydrive_user_quota', 1000 );

		if ( is_array( $option_user_quota ) )
			$user_quota = !empty( $option_user_quota[$user_role] ) ? $option_user_quota[$user_role] : 1000;
		else
			$user_quota = $option_user_quota;
	}

	return $user_quota;
}

/**
 * Return the list of available sharing options
 *
 * @since  1.2.2
 *
 * @uses   bp_is_active() to check for BuddyPress active components
 * @uses   apply_filters() call 'buddydrive_get_sharing_options' to restrict options
 * @return array available sharing options
 */
function buddydrive_get_sharing_options() {
	$options = wp_list_pluck( buddydrive_get_stati(), 'label', 'buddydrive_privacy' );

	if ( ! bp_is_active( 'friends' ) ) {
		unset( $options['friends'] );
	}

	if ( ! bp_is_active( 'groups' ) ){
		unset( $options['groups'] );
	}

	return apply_filters( 'buddydrive_get_sharing_options', $options );
}

/**
 * Get a user's file count
 *
 * @since 1.2.2
 * @since 2.0.0 Add the $status parameter
 *
 * @param  int $user_id the user id
 * @return int          the files count
 */
function buddydrive_count_user_files( $user_id = 0, $status = 'any' ) {
	$bd = buddydrive();

	if ( empty( $user_id ) ) {
		$user_id = bp_loggedin_user_id();
	}

	if ( empty( $user_id ) || ! bp_is_root_blog() ) {
		return false;
	}

	$catched_count = $bd->__get( 'users_file_count' );

	// Count only once per page load.
	if ( ! empty( $catched_count[ $user_id ] ) ) {
		return $catched_count[ $user_id ];
	}

	$count = BuddyDrive_Item::count_user_items( $user_id, buddydrive_get_file_post_type(), $status );

	if ( is_array( $catched_count ) ) {
		$catched_count[ $user_id ] = $count;
	} else {
		$catched_count = array( $user_id => $count );
	}

	$bd->__set( 'users_file_count', $catched_count );

	return $count;
}

/**
 * Set a file thumbnail (if public)
 *
 * @since 1.3.0
 *
 * @param int $buddyfile_id the file ID
 * @param string whether to get the url or the patch to the thumbnail
 * @return string|bool the url to the created thumbnail, false if errors
 */
function buddydrive_set_thumbnail( $buddyfile_id = 0, $buddyfile = array() ) {
	if ( empty( $buddyfile_id ) || empty( $buddyfile['type'] ) || 0 !== strpos( $buddyfile['type'], 'image/' ) ) {
		return false;
	}

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once( ABSPATH . "/wp-admin/includes/image.php" );
	}

	// Temporary filter sizes & relative path
	add_filter( 'intermediate_image_sizes_advanced', 'buddydrive_public_restrict_image_sizes', 10, 1 );
	add_filter( '_wp_relative_upload_path',          'buddydrive_public_relative_path',        10, 2 );

	$file_metada = wp_generate_attachment_metadata( $buddyfile_id, $buddyfile['file'] );

	// Remove it so no other attachments will be affected
	remove_filter( 'intermediate_image_sizes_advanced', 'buddydrive_public_restrict_image_sizes', 10, 1 );
	remove_filter( '_wp_relative_upload_path',          'buddydrive_public_relative_path',        10, 2 );

	if ( ! empty( $file_metada['sizes']['thumbnail']['file'] ) ) {
		wp_update_attachment_metadata( $buddyfile_id, $file_metada );
		$old_location = trailingslashit( buddydrive()->upload_dir ) . $file_metada['sizes']['thumbnail']['file'];
		$new_location = trailingslashit( buddydrive()->thumbdir ) . $file_metada['sizes']['thumbnail']['file'];

		if ( file_exists( $old_location ) && is_dir( buddydrive()->thumbdir ) ) {
			@ rename( $old_location, $new_location );
		}

		return trailingslashit( buddydrive()->thumburl ) . $file_metada['sizes']['thumbnail']['file'];
	} else {
		return false;
	}
}

/**
 * Get a file thumbnail (if public)
 *
 * @since 1.3.0
 * @since 1.3.3 Make sure Attachment metadata is an array
 *
 * @param int $buddyfile_id the file ID
 * @param string whether to get the url or the patch to the thumbnail
 */
function buddydrive_get_thumbnail( $buddyfile_id = 0, $type = 'thumburl', $link_only = true ) {
	if ( empty( $buddyfile_id ) || ( 'thumbdir' !== $type && 'thumburl' !== $type ) ) {
		return false;
	}

	$file_metada = wp_get_attachment_metadata( $buddyfile_id );
	if ( ! is_array( $file_metada ) || empty( $file_metada['sizes']['thumbnail']['file'] ) ) {
		return false;
	}

	$link = trailingslashit( buddydrive()->{$type} ) . $file_metada['sizes']['thumbnail']['file'];

	if ( ! empty( $link_only ) ) {
		return $link;
	} else {
		return array( $link, $file_metada['sizes']['thumbnail']['width'], $file_metada['sizes']['thumbnail']['height'] );
	}
}

/**
 * Delete a file thumbnail (if public)
 *
 * @since 1.3.0
 *
 * @param int $buddyfile_id the file ID
 */
function buddydrive_delete_thumbnail( $buddyfile_id = 0 ) {
	if ( empty( $buddyfile_id ) ) {
		return false;
	}

	$thumbnail_path = buddydrive_get_thumbnail( $buddyfile_id, 'thumbdir' );

	if ( ! empty( $thumbnail_path ) ) {
		delete_post_meta( $buddyfile_id, '_wp_attachment_metadata' );

		if ( file_exists( $thumbnail_path ) ) {
			@unlink( $thumbnail_path );
		}
	}
}

/**
 * Does the current group supports BuddyDrive ?
 *
 * @since 1.3.0
 *
 * @return bool true if the current group does, false otherwise
 */
function buddydrive_current_group_is_enabled() {
	if ( ! bp_is_group() ) {
		return false;
	}

	return (bool) apply_filters( 'buddydrive_current_group_is_enabled', groups_get_groupmeta( bp_get_current_group_id(), '_buddydrive_enabled' ) );
}

function buddydrive_check_download( $file = null, $user_id = 0 ) {
	if ( empty( $file ) || empty( $file->check_for ) ) {
		return new WP_Error( 'empty_request', __( 'Sorry, unknown file or unknown user.', 'buddydrive' ), 401 );
	}

	// Owners and super admins can always download
	if ( (int) $file->user_id === (int) $user_id || is_super_admin() ) {
		return true;
	}

	// Init download cap.
	$can_download = false;

	switch ( $file->check_for ) {
		case 'public'  :
			$can_download = true;
		break;

		case 'password' :
			if ( ! isset( $file->pass_submitted ) ) {
				$can_download = new WP_Error( 'empty_password', __( 'This file is password protected', 'buddydrive' ), 403 );

			} else {
				if ( $file->password === $file->pass_submitted ) {
					$can_download = true;
				} else {
					$can_download = new WP_Error( 'wrong_password', __( 'Wrong password', 'buddydrive' ), 401 );
				}
			}
		break;

		case 'friends' :
			if ( bp_is_active( 'friends' ) && friends_check_friendship( $file->user_id, $user_id ) ) {
				$can_download = true;
			} else {
				$can_download = new WP_Error( 'not_friend', __( 'You must be a friend of this member to download the file', 'buddydrive' ), buddydrive_get_user_buddydrive_url( $file->user_id ) );
			}
		break;

		case 'groups' :
			if ( ! bp_is_active( 'groups' ) ) {
				$can_download = new WP_Error( 'groups_inactive', __( 'Group component is deactivated, please contact the administrator.', 'buddydrive' ), buddydrive_get_root_url() );
			} else {
				// Validate & get groups
				$groups = groups_get_groups( array( 'include' => $file->group, 'show_hidden' => true, 'per_page' => false ) );

				if ( ! empty( $groups['groups'] ) ) {
					$group_stati = array_unique( wp_list_pluck( $groups['groups'], 'status' ) );
					if ( ! array_diff( array( 'public' ), $group_stati  ) ) {
						$can_download = true;

					// Check the the user is at least member of one of the group
					} else {
						foreach ( $groups['groups'] as $group )  {
							if ( groups_is_user_member( $user_id, $group->id ) ) {
								$can_download = true;
								break;
							}
						}
					}

					if ( ! $can_download ) {
						if ( 1 === count( $groups['groups'] ) && 'hidden' !== $groups['groups'][0]->status ) {
							$redirect = bp_get_group_permalink( $groups['groups'][0] );
						} else {
							$redirect = bp_get_groups_directory_permalink();
						}

						$can_download = new WP_Error( 'not_in_groups', __( 'You must be a member of the group to download the file', 'buddydrive' ), $redirect );
					}
				} else {
					$can_download = new WP_Error( 'unfound_groups', __( 'The groups the file is attached to do not match any existing groups.', 'buddydrive' ), buddydrive_get_root_url() );
				}
			}
		break;

		case 'private' :
		default        :
			$can_download = new WP_Error( 'unauthorized_download', __( 'Sorry, you are not allowed to download this file.', 'buddydrive' ), 401 );

			/**
			 * Filter here for custom privacy options
			 *
			 * @since 1.3.3
			 *
			 * @param bool   $can_download    True if the file can be downloaded, false otherwise.
			 * @param object $buddydrive_file The BuddyDrive file object.
			 */
			$can_download = apply_filters( 'buddydrive_file_downloader_can_download', $can_download, $file );
		break;
	}

	return $can_download;
}
