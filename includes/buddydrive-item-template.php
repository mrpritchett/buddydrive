<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Echoes the right link to BuddyDrive root folder regarding to context
 *
 * @uses bp_is_user() to check for user's buddydrive
 * @uses bp_current_action() to check for BuddyDrive nav
 * @uses buddydrive_get_user_buddydrive_url() to print the BuuddyBox user's url
 * @uses buddydrive_get_friends_subnav_slug() to get friends subnav slug
 * @uses buddydrive_get_friends_buddydrive_url() to print the Shared by friends BuddyDrive Url
 * @uses buddydrive_is_group() to check for the BuddyDrive group area
 * @uses buddydrive_get_group_buddydrive_url() to print the BuddyDrive group's url
 * @return the right url
 */
function buddydrive_component_home_url() {
	if ( bp_is_user() && bp_current_action() == 'files' )
		echo buddydrive_get_user_buddydrive_url();
	else if ( bp_is_user() && bp_current_action() == buddydrive_get_friends_subnav_slug() )
		echo buddydrive_get_friends_buddydrive_url();
	else if ( buddydrive_is_group() )
		echo buddydrive_get_group_buddydrive_url();
}


/**
 * Displays a select box to help user chooses the privacy option
 * 
 * @param string $id       the id of the select box
 * @param string $selected if an option have been selected (edit form)
 * @param string $name     the name of the select boc
 * @uses selected() to activate an option if $selected is defined
 * @uses bp_is_active() to check for friends or groups component
 * @return the select box
 */
function buddydrive_select_sharing_options( $id = 'buddydrive-sharing-options', $selected = false, $name = false ) {
	?>
	<select id="<?php echo $id;?>" <?php if ( ! empty( $name ) ) echo 'name="'.$name.'"';?>>
		<option value="private" <?php selected( $selected, 'private' );?>><?php _e( 'Private', 'buddydrive' );?></option>
		<option value="password" <?php selected( $selected, 'password' );?>><?php _e( 'Password protected', 'buddydrive' );?></option>
		<option value="public" <?php selected( $selected, 'public' );?>><?php _e( 'Public', 'buddydrive' );?></option>

		<?php if ( bp_is_active( 'friends' ) ):?>
			<option value="friends" <?php selected( $selected, 'friends' );?>><?php _e( 'Friends only', 'buddydrive' );?></option>
		<?php endif;?>
		<?php if ( bp_is_active( 'groups' ) ):?>
			<option value="groups" <?php selected( $selected, 'groups' );?>><?php _e( 'One of my groups', 'buddydrive' );?></option>
		<?php endif;?>
	</select>
	<?php
	
}

/**
 * Displays the select box to choose a folder to attach the BuddyFile to.
 * 
 * @param  int $user_id the user id
 * @param  int $selected the id of the folder in case of edit form
 * @param  string $name  the name of the select box
 * @uses buddydrive_get_select_folder_options() to get the select box
 */
function buddydrive_select_folder_options( $user_id = false, $selected = false, $name = false ) {
	echo buddydrive_get_select_folder_options( $user_id, $selected, $name );
}

	/**
	 * Builds the folder select box to attach the BuddyFile to
	 * @param  int $user_id the user id
 	 * @param  int $selected the id of the folder in case of edit form
 	 * @param  string $name  the name of the select box
 	 * @uses bp_loggedin_user_id() to get current user id
 	 * @uses buddydrive_get_folder_post_type() to get BuddyFolder post type
 	 * @uses The BuddyDrive loop and some template tags
 	 * @uses selected() to activate a folder if $selected is defined
	 * @return string  the select box
	 */
	function buddydrive_get_select_folder_options( $user_id = false, $selected = false, $name = false ) {
		if ( empty( $user_id ) )
			$user_id = bp_loggedin_user_id();
			
		if ( ! empty( $name ) )
			$name = 'name="'.$name.'"';
		
		$output = __( 'No folder available', 'buddydrive' );
		
		$buddydrive_args = array(
				'user_id'	      => $user_id,
				'per_page'	      => false,
				'paged'		      => false,
				'type'            => buddydrive_get_folder_post_type()
		);
			
		if ( buddydrive_has_items( $buddydrive_args ) ) {
			
			$output = '<select id="folders" '.$name.'>';
			
			$output .= '<option value="0" '.selected( $selected, 0, false ).'>'. __( 'Root folder', 'buddydrive' ).'</option>';
			
			while ( buddydrive_has_items() ) {
				buddydrive_the_item();
				$output .= '<option value="'.buddydrive_get_item_id().'" '. selected( $selected, buddydrive_get_item_id(), false ) .'>'.buddydrive_get_item_title().'</option>';
			}
			
			$output .= '</select>';
		}
			
		return apply_filters( 'buddydrive_get_select_folder_options', $output, $buddydrive_args );
		
	}


/**
 * Displays a select box to choose the group to attach the BuddyDrive Item to
 * 
 * @param  int $user_id  the user id
 * @param  int $selected the group id in case of edit form
 * @param  string $name  the name of the select box
 * @uses buddydrive_get_select_user_group() to get the select box
 */
function buddydrive_select_user_group( $user_id = false, $selected = false, $name = false ) {
	echo buddydrive_get_select_user_group( $user_id, $selected, $name );
}

	/**
	 * Builds the select box to choose the group to attach the BuddyDrive Item to
	 * @param  int $user_id  the user id
 	 * @param  int $selected the group id in case of edit form
 	 * @param  string $name  the name of the select box
 	 * @uses bp_loggedin_user_id() to get current user id
 	 * @uses groups_get_groups() to list the groups of the user
 	 * @uses groups_get_groupmeta() to check group enabled BuddyDrive
 	 * @uses selected() to eventually activate a group
	 * @return string the select box
	 */
	function buddydrive_get_select_user_group( $user_id = false, $selected = false, $name = false ) {
		if ( empty( $user_id ) )
			$user_id = bp_loggedin_user_id();

		$name = ! empty( $name ) ? ' name="'.$name.'"' : false ;

		$output = __( 'No group available for BuddyDrive', 'buddydrive' );
		
		if ( ! bp_is_active( 'groups' ) )
			return $output;
		
		$user_groups = groups_get_groups( array( 'user_id' => $user_id, 'show_hidden' => true, 'per_page' => false ) );

		$buddydrive_groups = false;

		// checking for available buddydrive groups
		if ( ! empty( $user_groups['groups'] ) ) {
			foreach( $user_groups['groups'] as $group ) {
				if ( 1 == groups_get_groupmeta( $group->id, '_buddydrive_enabled' ) )
					$buddydrive_groups[]= array( 'group_id' => $group->id, 'group_name' => $group->name );
			}
		}

		// building the select box
		if ( ! empty( $buddydrive_groups ) && is_array( $buddydrive_groups ) ) {
			$output = '<select id="buddygroup"'.$name.'>' ;
			foreach ( $buddydrive_groups as $buddydrive_group ) {
				$output .= '<option value="'.$buddydrive_group['group_id'].'" '. selected( $selected, $buddydrive_group['group_id'], false ) .'>'.$buddydrive_group['group_name'].'</option>';
			}
			$output .= '</select>';
		}

		return apply_filters( 'buddydrive_get_select_user_group', $output );
	}


/**
 * Displays the form to create a new folder
 * 
 * @uses buddydrive_select_sharing_options() to display the privacy select box
 */
function buddydrive_folder_form() {
	?>
	<form class="standard-form" action="" method="post" id="buddydrive-folder-editor-form">
		
		<div id="buddyfolder-first-step">
			<label for="buddyfolder-sharing-options"><?php _e( 'Define your sharing options', 'buddydrive' );?></label>
			<?php buddydrive_select_sharing_options( 'buddyfolder-sharing-options' );?>
			<div id="buddyfolder-sharing-details"></div>
			<input type="hidden" id="buddyfolder-sharing-settings" value="private">
			<p class="buddydrive-action"><a href="#" class="next-step button"><?php _e( 'Next Step', 'buddydrive' );?></a></p>
		</div>
		<div id="buddyfolder-second-step" class="hide">
			<label for="buddydrive-folder-title"><?php _e( 'Create your folder', 'buddydrive' );?></label>
			<input type="text" placeholder="<?php _e( 'Name of your folder', 'buddydrive' );?>" id="buddydrive-folder-title" name="buddydrive_folder[title]">
			<p class="buddydrive-action folder"><input type="submit" value="<?php _e( 'Add folder', 'buddydrive' );?>" name="buddydrive_folder[submit]">&nbsp;<a href="#" class="cancel-folder button"><?php _e( 'Cancel', 'buddydrive' );?></a></p>
		</div>
	</form>
	<?php
}


/**
 * Displays the form to upload a new file
 * 
 * @uses BuddyDrive_Uploader() class
 */
function buddydrive_upload_form() {
	return new BuddyDrive_Uploader();
}


/**
 * Displays the space a user is using with his files
 * 
 * @param  string $type    html or a diff
 * @param  int $user_id the user id
 */
function buddydrive_user_used_quota( $type = false, $user_id = false ) {
	echo buddydrive_get_user_space_left( $type, $user_id );
}

	/**
	 * Gets the space a user is using with his files
	 * 
	 * @param  string $type    html or a diff
	 * @param  int $user_id the user id
	 * @uses bp_loggedin_user_id() to get current user id
	 * @uses buddydrive_get_quota_by_user_id() to get quota for user
	 * @uses get_user_meta() to get user's space used so far
	 * @return int|string   the space left or html to display it
	 */
	function buddydrive_get_user_space_left( $type = false, $user_id = false ){
		if ( empty( $user_id ) )
			$user_id = bp_loggedin_user_id();

		$max_space = buddydrive_get_quota_by_user_id( $user_id );
		$max_space = intval( $max_space ) * 1024 * 1024 ;

		$used_space = get_user_meta( $user_id, '_buddydrive_total_space', true );
		$used_space = intval( $used_space );
		$quota = number_format( ( $used_space / $max_space ) * 100, 2  );

		if ( $type == 'diff' )
			return $max_space - $used_space;
		else
			return apply_filters( 'buddydrive_get_user_space_left', sprintf( __( '<span id="buddy-quota">%s</span>&#37; used', 'buddydrive' ), $quota ), $quota );

	}


/**
 * BuddyDrive Loop : do we have items for the query asked
 * 
 * @param  array $args the arguments of the query
 * @global object $buddydrive_template
 * @uses buddydrive_get_folder_post_type() to get BuddyFolder post type
 * @uses buddydrive_get_file_post_type() to get BuddyFile post type
 * @uses bp_displayed_user_id() to default to current displayed user
 * @uses bp_current_action() to get the current action ( files / friends / admin)
 * @uses bp_is_active() to check if groups component is active
 * @uses buddydrive_is_group() are we on a group's BuddyDrive ?
 * @uses wp_parse_args() to merge defaults and args
 * @uses BuddyDrive_Item::get() to request the DB
 * @uses BuddyDrive_Item::have_posts to know if BuddyItems matched the query
 * @return the result of the query
 */
function buddydrive_has_items( $args = '' ) {
	global $buddydrive_template;

	// This keeps us from firing the query more than once
	if ( empty( $buddydrive_template ) ) {

		$defaulttype = array( buddydrive_get_folder_post_type(), buddydrive_get_file_post_type() );
		$user = $group_id = $buddyscope = false;
		
		if ( bp_displayed_user_id() )
			$user = bp_displayed_user_id();

		$buddyscope = bp_current_action();

		if ( $buddyscope == buddydrive_get_friends_subnav_slug() )
			$buddyscope = 'friends';

		if ( is_admin() )
			$buddyscope = 'admin';

		if ( bp_is_active( 'groups' ) && buddydrive_is_group() ) {
			$group = groups_get_current_group();
			
			$group_id = $group->id;
			$buddyscope = 'groups';
		}
		
		/***
		 * Set the defaults for the parameters you are accepting via the "buddydrive_has_items()"
		 * function call
		 */
		$defaults = array(
				'id'                => false,
				'name'              => false,
				'group_id'	        => $group_id,
				'user_id'	        => $user,
				'per_page'	        => 10,
				'paged'		        => 1,
				'type'              => $defaulttype,
				'buddydrive_scope'  => $buddyscope,
				'search'            => false,
				'buddydrive_parent' => 0,
				'exclude'           => 0,
				'orderby' 		    => 'title', 
				'order'             => 'ASC'
			);
		
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
			
		$buddydrive_template = new BuddyDrive_Item();


		if ( ! empty( $search ) )
			$buddydrive_template->get( array( 'per_page' => $per_page, 'paged' => $paged, 'type' => $type, 'buddydrive_scope' => $buddydrive_scope, 'search' => $search, 'orderby' => $orderby, 'order' => $order ) );
		else
			$buddydrive_template->get( array( 'id' => $id, 'name' => $name, 'group_id' => $group_id, 'user_id' => $user_id, 'per_page' => $per_page, 'paged' => $paged, 'type' => $type, 'buddydrive_scope' => $buddydrive_scope, 'buddydrive_parent' => $buddydrive_parent, 'exclude' => $exclude, 'orderby' => $orderby, 'order' => $order ) );
		
	}

	return $buddydrive_template->have_posts();
}


/**
 * BuddyDrive Loop : do we have more items
 *
 * @global object $buddydrive_template
 * @return boolean true or false
 */
function buddydrive_has_more_items() {
	global $buddydrive_template;
	
	$total_items = intval( $buddydrive_template->query->found_posts );
	$pag_num = intval( $buddydrive_template->query->query_vars['posts_per_page'] );
	$pag_page = intval( $buddydrive_template->query->query_vars['paged'] );
	
	$remaining_pages = floor( ( $total_items - 1 ) / ( $pag_num * $pag_page ) );
	$has_more_items  = (int) $remaining_pages ? true : false;

	return apply_filters( 'buddydrive_has_more_items', $has_more_items );
}

/**
 * BuddyDrive Loop : get the item's data
 *
 * @global object $buddydrive_template
 * @return object the item's data
 */
function buddydrive_the_item() {
	global $buddydrive_template;
	return $buddydrive_template->query->the_post();
}

/**
 * Displays the id of the BuddyDrive item
 * 
 * @uses buddydrive_get_item_id() to get the item id
 */
function buddydrive_item_id() {
	echo buddydrive_get_item_id();
}

	/**
	 * Gets the item id
	 *
	 * @global object $buddydrive_template
	 * @return int the item id
	 */
	function buddydrive_get_item_id() {
		global $buddydrive_template;
		
		return $buddydrive_template->query->post->ID;
	}

/**
 * Displays the parent id of the BuddyDrive item
 * 
 * @uses buddydrive_get_parent_item_id() to get the parent item id
 */
function buddydrive_parent_item_id() {
	echo buddydrive_get_parent_item_id();
}

	/**
	 * Gets the parent item id
	 *
	 * @global object $buddydrive_template
	 * @return int the parent item id
	 */
	function buddydrive_get_parent_item_id() {
		global $buddydrive_template;
		
		return $buddydrive_template->query->post->post_parent;
	}

/**
 * Displays the title of the BuddyDrive item
 * 
 * @uses buddydrive_get_item_title() to get the title of the item
 */
function buddydrive_item_title() {
	echo buddydrive_get_item_title();
}

	/**
	 * Gets the title of the BuddyDrive item
	 *
	 * @global object $buddydrive_template
	 * @return string the title of the item
	 */
	function buddydrive_get_item_title() {
		global $buddydrive_template;
		
		return apply_filters('buddydrive_get_item_title', $buddydrive_template->query->post->post_title );
	}

/**
 * Displays the description of the BuddyDrive item
 * 
 * @uses buddydrive_get_item_description() to get the description of the item
 */
function buddydrive_item_description() {
	echo buddydrive_get_item_description();
}

	/**
	 * Gets the description of the BuddyDrive item
	 *
	 * @global object $buddydrive_template
	 * @return string the description of the item
	 */
	function buddydrive_get_item_description() {
		global $buddydrive_template;
		
		return apply_filters( 'buddydrive_get_item_description', $buddydrive_template->query->post->post_content );
	}

/**
 * Do we have a file ?
 *
 * @global object $buddydrive_template
 * @uses buddydrive_get_file_post_type() to get the BuddyFile post type
 * @return boolean true or false
 */
function buddydrive_is_buddyfile() {
	global $buddydrive_template;
	
	$is_buddyfile = false;
	
	if ( $buddydrive_template->query->post->post_type == buddydrive_get_file_post_type() )
		$is_buddyfile = true;
		
	return $is_buddyfile;
}

/**
 * Displays the action link (download or open folder) of the BuddyDrive item
 * 
 * @uses buddydrive_get_action_link() to get the action link of the item
 */
function buddydrive_action_link() {
	echo buddydrive_get_action_link();
}

	/**
	 * Gets the action link of the BuddyDrive item
	 *
	 * @global object $buddydrive_template
	 * @uses buddydrive_is_buddyfile() to check for a file
	 * @return string the action link of the item
	 */
	function buddydrive_get_action_link() {
		global $buddydrive_template;
		
		$buddyslug = 'folder';
		
		if ( buddydrive_is_buddyfile() )
			$buddyslug = 'file';

		$slug = trailingslashit( $buddyslug.'/' . $buddydrive_template->query->post->post_name );
			
		$link = buddydrive_get_root_url() .'/'. $slug;
		
		return apply_filters( 'buddydrive_get_action_link', $link );
	}

/**
 * Displays an action link class for the BuddyDrive item
 * 
 * @uses buddydrive_get_action_link_class() to get the action link class of the item
 */	
function buddydrive_action_link_class() {
	echo buddydrive_get_action_link_class();
}

	/**
	 * Gets the action link class for the BuddyDrive item
	 *
	 * @global object $buddydrive_template
	 * @uses buddydrive_is_buddyfile() to check for a file
	 * @return string the action link class for the item
	 */
	function buddydrive_get_action_link_class() {
		$class = array();
		
		$class[] =  buddydrive_is_buddyfile() ? 'buddyfile' : 'buddyfolder';
		
		$class = apply_filters( 'buddydrive_get_action_link_class', $class );
		
		return implode( ' ', $class );
	}

/**
 * Displays an attribute to identify a folder or a file
 * 
 * @uses buddydrive_get_item_attribute() to get the attribute of the item
 */	
function buddydrive_item_attribute() {
	echo buddydrive_get_item_attribute();
}

	/**
	 * Gets the attribute to identify a folder or a file
	 *
	 * @global object $buddydrive_template
	 * @uses buddydrive_is_buddyfile() to check for a file
	 * @return string the attribute for the item
	 */
	function buddydrive_get_item_attribute() {
		
		$data_attr = false;
		
		if ( ! buddydrive_is_buddyfile() )
			$data_attr = ' data-folder="'.buddydrive_get_item_id().'"';
		else
			$data_attr = ' data-file="'.buddydrive_get_item_id().'"';
			
		return apply_filters( 'buddydrive_get_item_attribute', $data_attr );
	}

/**
 * Displays the user id of the owner of a BuddyDrive item
 * 
 * @uses buddydrive_get_owner_id() to get owner's id
 */	
function buddydrive_owner_id() {
	echo buddydrive_get_owner_id();
}

	/**
	 * Gets the user id of the owner of a BuddyDrive item
	 *
	 * @global object $buddydrive_template
	 * @return int the owner's id
	 */
	function buddydrive_get_owner_id() {
		global $buddydrive_template;

		return apply_filters( 'buddydrive_get_owner_id', $buddydrive_template->query->post->post_author );
	}

/**
 * Displays the avatar of the owner of a BuddyDrive item
 * 
 * @uses buddydrive_get_show_owner_avatar() to get avatar of the owner
 */	
function buddydrive_owner_avatar() {
	echo buddydrive_get_show_owner_avatar();
}

	/**
	 * Gets the avatar of the owner
	 *
	 * @param int $user_id the user id
	 * @param string $width the width of the avatar
	 * @param string $height the height of the avatar
	 * @uses buddydrive_get_owner_id() to get the user id
	 * @uses bp_core_get_username() to get the username of the owner
	 * @uses bp_core_fetch_avatar() to get the avatar of the owner 
	 * @return string avatar of the owner
	 */
	function buddydrive_get_show_owner_avatar( $user_id = false, $width = '32', $height = '32' ) {

		if ( empty( $user_id ) )
			$user_id = buddydrive_get_owner_id();

		$username = bp_core_get_username( $user_id );

		$avatar  = bp_core_fetch_avatar( array(
			'item_id'    => $user_id,
			'object'     => 'user',
			'type'       => 'thumb',
			'avatar_dir' => 'avatars',
			'alt'        => sprintf( __( 'User Avatar of %s', 'buddydrive' ), $username ),
			'width'      => $width,
			'height'     => $height,
			'title'      => $username
		) );

		return apply_filters( 'buddydrive_get_show_owner_avatar', $avatar, $user_id, $username );
	}

/**
 * Displays the link to the owner's home page
 * 
 * @uses buddydrive_get_owner_link() to get the link to the owner's home page
 */	
function buddydrive_owner_link() {
	echo buddydrive_get_owner_link();
}

	/**
	 * Gets the link to the owner's home page
	 *
	 * @uses buddydrive_get_owner_id() to get the owner id
	 * @uses bp_core_get_userlink() to get the link to owner's home page
	 * @return the link
	 */
	function buddydrive_get_owner_link() {
		$user_id = buddydrive_get_owner_id();

		$userlink = bp_core_get_userlink( $user_id, false, true );

		return apply_filters( 'buddydrive_get_owner_link', $userlink );
	}

/**
 * Displays the avatar of the group the item is attached to
 * 
 * @uses buddydrive_get_group_avatar() to get the group avatar
 */	
function buddydrive_group_avatar() {
	echo buddydrive_get_group_avatar();
}

	/**
	 * Gets the group avatar the item is attached to
	 * 
	 * @param  int $item_id the item id
	 * @param  boolean $nolink  should we wrap a link to group's page
	 * @param  string  $width   the width of the avatar
	 * @param  string  $height  the height of the avatar
	 * @uses buddydrive_get_parent_item_id() to get parent id
	 * @uses buddydrive_get_item_id() to default to item id
	 * @uses get_post_meta() to get the group id attached to the item
	 * @uses groups_get_group() to get the group object for the group_id
	 * @uses bp_get_group_permalink() to get the group link
	 * @uses bp_core_fetch_avatar() to get the group avatar
	 * @uses groups_is_user_member() to check for user's membership
	 * @uses bp_loggedin_user_id() to get current user id
	 * @return string the group avatar
	 */
	function buddydrive_get_group_avatar( $item_id = false, $nolink = false, $width ='32', $height = '32' ) {

		$buddydrive_item_group_meta = false;

		if ( empty( $item_id ) ) {
			$parent_id = buddydrive_get_parent_item_id();
			$item_id = ( !empty( $parent_id ) ) ? $parent_id : buddydrive_get_item_id();
		}

		$buddydrive_item_group_meta = get_post_meta( $item_id, '_buddydrive_sharing_groups', true );

		if ( empty( $buddydrive_item_group_meta ) )
			return false;
			
		if ( ! bp_is_active( 'groups' ) )
			return false;

		$group = groups_get_group( array( 'group_id' => $buddydrive_item_group_meta ) );
		
		if ( empty( $group) )
			return false;

		$group_link = bp_get_group_permalink( $group );
		$group_name = $group->name;

		$group_avatar  = bp_core_fetch_avatar( array(
										'item_id'    => $buddydrive_item_group_meta,
										'object'     => 'group',
										'type'       => 'thumb',
										'avatar_dir' => 'group-avatars',
										'alt'        => sprintf( __( 'Group logo of %d', 'buddypress' ), $group_name ),
										'width'      => $width,
										'height'     => $height,
										'title'      => $group_name
									) );

		if ( 'hidden' == $group->status && !groups_is_user_member( bp_loggedin_user_id(), $buddydrive_item_group_meta ) && !is_super_admin() )
			$nolink = true;
		
		if ( ! empty( $nolink ) )
			return $group_avatar;
		else
			return apply_filters( 'buddydrive_get_group_avatar', '<a href="'.$group_link.'" title="'.$group_name.'">' . $group_avatar .'</a>');


	}

/**
 * Displays the avatar of the owner or a checkbox
 * 
 * @uses buddydrive_get_owner_or_cb()
 */	
function buddydrive_owner_or_cb() {
	echo buddydrive_get_owner_or_cb();
}

	/**
	 * Choose between the owner's avatar or a checkbox if on loggedin user's BuddyDrive
	 *
	 * @uses bp_is_my_profile() to check we're on a user's profile
	 * @uses bp_current_action() to check for BuddyDrive scope
	 * @uses buddydrive_get_item_id() to get the item id
	 * @uses buddydrive_get_owner_link() to get the link to owner's profile
	 * @uses buddydrive_get_show_owner_avatar() to get owner's avatar.
	 * @return string the right html
	 */
	function buddydrive_get_owner_or_cb() {
		$output = '';
		
		if ( bp_is_my_profile() && bp_current_action() == 'files' )
			$output = '<input type="checkbox" name="buddydrive-item[]" class="buddydrive-item-cb" value="'.buddydrive_get_item_id().'">';
		else
			$output = '<a href="'.buddydrive_get_owner_link().'" title="'.__('Owner', 'buddydrive').'">'.buddydrive_get_show_owner_avatar().'</a>';
			
		return apply_filters( 'buddydrive_get_owner_or_cb', $output );
	}

/**
 * Displays a checkbox or a table header
 * 
 * @uses buddydrive_get_th_owner_or_cb()
 */	
function buddydrive_th_owner_or_cb() {
	echo buddydrive_get_th_owner_or_cb();
}

	/**
	 * Gets a checkbox or a table header
	 *
	 * @uses bp_is_my_profile() to check we're on a user's profile
	 * @uses bp_current_action() to check for BuddyDrive scope
	 * @return string the right html
	 */
	function buddydrive_get_th_owner_or_cb() {
		$output = '';
		
		if ( bp_is_my_profile() && bp_current_action() == 'files')
			$output = '<input type="checkbox" id="buddydrive-sel-all">';
		else
			$output = __('Owner', 'buddydrive');
			
		return apply_filters( 'buddydrive_get_th_owner_or_cb', $output );
	}


/**
 * Displays the privacy of an item
 * 
 * @uses buddydrive_get_item_privacy() to get the privacy option
 * @uses buddydrive_get_group_avatar() to get the group avatar of the group the item is attached to
 * @uses buddydrive_get_item_id() to get the id of the item
 */
function buddydrive_item_privacy() {
	$status = buddydrive_get_item_privacy();
	
	switch ( $status['privacy'] ) {
		case 'private' :
			echo '<a title="'.__( 'Private', 'buddydrive' ).'"><i class="icon bd-icon-lock"></i></a>';
			break;
		case 'public' :
			echo '<a title="'.__( 'Public', 'buddydrive' ).'"><i class="icon bd-icon-unlocked"></i></a>';
			break;
		case 'friends' :
			echo '<a title="'.__( 'Friends', 'buddydrive' ).'"><i class="icon bd-icon-users"></i></a>';
			break;
		case 'password' :
			echo '<a title="'.__( 'Password protected', 'buddydrive' ).'"><i class="icon bd-icon-key"></i></a>';
			break;
		case 'groups' :
			if( !empty( $status['group'] ) )
				echo buddydrive_get_group_avatar( buddydrive_get_item_id() );
			else
				_e( 'Group', 'buddydrive' );
			break;		
	}
}

	/**
	 * Gets the item's privacy
	 *
	 * @global object $buddydrive_template
	 * @uses buddydrive_get_item_id() to get the item id
	 * @uses get_post_meta() to get item's privacy option
	 * @return array the item's privacy
	 */
	function buddydrive_get_item_privacy() {
		global $buddydrive_template;
		
		$status = array();
		$buddyfile_id = buddydrive_get_item_id();
		$item_privacy_id = !( empty( $buddydrive_template->query->post->post_parent ) ) ? $buddydrive_template->query->post->post_parent : $buddyfile_id ;
		
		$status['privacy'] = get_post_meta( $item_privacy_id, '_buddydrive_sharing_option', true );
		
		if ( $status['privacy'] == 'groups' )
			$status['group'] = get_post_meta( $item_privacy_id, '_buddydrive_sharing_groups', true );
			
		return apply_filters( 'buddydrive_get_item_privacy', $status );
	}

/**
 * Displays the mime type of an item
 *
 * @uses buddydrive_get_item_mime_type() to get it !
 */
function buddydrive_item_mime_type() {
	echo buddydrive_get_item_mime_type();
}

	/**
	 * Gets the mime type of an item
	 *
	 * @global object $buddydrive_template
	 * @uses buddydrive_is_buddyfile() to check for a BuddyFile
	 * @return string the mime type
	 */
	function buddydrive_get_item_mime_type() {
		global $buddydrive_template;
		
		$mime_type = __( 'folder', 'buddydrive' );
		
		if ( buddydrive_is_buddyfile() ) {
			$doc = $buddydrive_template->query->post->guid;

			$mime_type = __( 'file', 'buddydrive' );
			
			if ( preg_match( '/^.*?\.(\w+)$/', $doc, $matches ) )
				$mime_type = esc_html( $matches[1] ) .' '. $mime_type;
		}
			
		
		return apply_filters('buddydrive_get_item_mime_type', $mime_type );
	}

/**
 * Displays an icon before the item's title
 * 
 * @uses buddydrive_get_item_icon() to get the icon
 */
function buddydrive_item_icon() {
	echo buddydrive_get_item_icon();
}
	
	/**
	 * Gets the item's icon
	 * 
	 * @uses buddydrive_is_buddyfile() to check for a BuddyFile
	 * @return string html of the icon
	 */
	function buddydrive_get_item_icon() {
		
		$icon = '<i class="icon bd-icon-folder"></i>';
		
		if ( buddydrive_is_buddyfile() )
			$icon = '<i class="icon bd-icon-file"></i>';
		
		return apply_filters( 'buddydrive_get_item_icon', $icon );
		
		
	}

/**
 * Displays the file name of the uploaded file
 * 
 * @uses buddydrive_get_uploaded_file_name() to get it
 */
function buddydrive_uploaded_file_name() {
	echo buddydrive_get_uploaded_file_name();
}

	/**
	 * Gets the mime type of an item
	 *
	 * @global object $buddydrive_template
	 * @return string the uploaded file name
	 */
	function buddydrive_get_uploaded_file_name() {
		global $buddydrive_template;
		
		return basename( $buddydrive_template->query->post->guid );
	}

/**
 * Displays the last modified date of an item
 * 
 * @uses buddydrive_get_item_date() to get it!
 */
function buddydrive_item_date() {
	echo buddydrive_get_item_date();
}

	/**
	 * Gets the item date
	 *
	 * @global object $buddydrive_template
	 * @uses  bp_format_time() to format the date
	 * @return string the formatted date
	 */
	function buddydrive_get_item_date() {
		global $buddydrive_template;
		
		$date = $buddydrive_template->query->post->post_modified_gmt;
		
		$date = bp_format_time( strtotime( $date ), true, false );
		
		return apply_filters( 'buddydrive_get_item_date', $date );
	}

/**
 * Various checks to see if a user can remove an item from a group
 * 
 * @param  int $group_id the group id
 * @uses bp_get_current_group_id() to get current group id
 * @uses buddydrive_is_group() to check we're on a group's BuddyDrive
 * @uses buddydrive_get_parent_item_id() to get parent item
 * @uses groups_is_user_admin() to check if the current user is admin of the group
 * @uses bp_loggedin_user_id() to get current user id
 * @uses is_super_admin() to give power to admin !
 * @return boolean $can_remove
 */
function buddydrive_current_user_can_remove( $group_id = false ) {
	$can_remove = false;

	if ( empty( $group_id ) )
		$group_id = bp_get_current_group_id();

	if ( ! buddydrive_is_group() || buddydrive_get_parent_item_id() )
		$can_remove = false;

	else if ( groups_is_user_admin( bp_loggedin_user_id(), $group_id ) )
		$can_remove = true;
	else if ( is_super_admin() )
		$can_remove = true;

	return apply_filters( 'buddydrive_current_user_can_remove', $can_remove );
}


/**
 * Checks if a user can share an item
 *
 * @uses buddydrive_get_owner_id() to get owner's id
 * @uses bp_loggedin_user_id() to get current user id
 * @return boolean true or false
 */
function buddydrive_current_user_can_share() {
	$can_share = false;

	if ( buddydrive_get_owner_id() == bp_loggedin_user_id() && ! buddydrive_is_group() )
		$can_share = true;

	return apply_filters( 'buddydrive_current_user_can_share', $can_share );
}

/**
 * Checks if the user can get the link of an item
 * 
 * @param  array $privacy the sharing options
 * @uses buddydrive_get_owner_id() to get owner's id
 * @uses bp_loggedin_user_id() to get current user id
 * @uses is_user_logged_in() to check if the visitor is not logged in
 * @uses bp_is_active() to check for friends and groups component
 * @uses friends_check_friendship() to check the friendship between owner and current user
 * @uses groups_is_user_member() to check if the current user is member of the group the BuddyDrive item is attached to
 * @return boolean true or false
 */
function buddydrive_current_user_can_link( $privacy = false ) {
	$can_link = false;

	if ( buddydrive_get_owner_id() == bp_loggedin_user_id() )
		$can_link = true;

	else if ( empty( $privacy ) )
		$can_link = false;

	else if ( ! is_user_logged_in() )
		$can_link = false;

	else if ( $privacy['privacy'] == 'public' )
		$can_link = true;

	else if ( $privacy['privacy'] == 'friends' && bp_is_active('friends') && friends_check_friendship( buddydrive_get_owner_id(), bp_loggedin_user_id() ) )
		$can_link = true;

	else if ( $privacy['privacy'] == 'groups' && bp_is_active('groups') && ! empty( $privacy['group'] ) && groups_is_user_member( bp_loggedin_user_id(), intval( $privacy['group'] ) ) )
		$can_link = true;

	else if ( is_super_admin() )
		$can_link = true;

	return apply_filters( 'buddydrive_current_user_can_link', $can_link );
}

/**
 * Displays the link to row actions
 * 
 * @uses buddydrive_get_row_actions()
 */
function buddydrive_row_actions() {
	echo buddydrive_get_row_actions();
}

	/**
	 * Builds the row actions
	 *
	 * @global object $buddydrive_template
	 * @uses buddydrive_get_item_id() to get item id
	 * @uses buddydrive_is_buddyfile() to check for a file
	 * @uses buddydrive_get_item_description() to get item's description
	 * @uses buddydrive_get_item_privacy() to get item's privacy options
	 * @uses buddydrive_current_user_can_link()
	 * @uses buddydrive_get_action_link()
	 * @uses buddydrive_current_user_can_share()
	 * @uses bp_is_active() to check for the messages, activity and group components.
	 * @uses bp_loggedin_user_domain() to get user's home url
	 * @uses bp_get_messages_slug() to get the messages component slug
	 * @uses buddydrive_current_user_can_share()
	 * @return [type] [description]
	 */
	function buddydrive_get_row_actions() {
		global $buddydrive_template;

		$row_actions = $inside_top = $inside_bottom = false;

		$buddyfile_id = buddydrive_get_item_id();

		if ( buddydrive_is_buddyfile() ) {
			$description = buddydrive_get_item_description();

			if ( ! empty( $description ) ) {
				$inside_top[]= '<a class="buddydrive-show-desc" href="#">' . __('Description', 'buddydrive'). '</a>';
				$inside_bottom .= '<div class="buddydrive-ra-desc hide ba">'.$description.'</div>';
			}
		}

		$privacy = buddydrive_get_item_privacy();

		switch ( $privacy['privacy'] ) {
			case 'public':
				if ( buddydrive_current_user_can_link( $privacy ) ){
					$inside_top[]= '<a class="buddydrive-show-link" href="#">' . __( 'Link', 'buddydrive' ). '</a>';
					$inside_bottom .= '<div class="buddydrive-ra-link hide ba"><input type="text" class="buddydrive-file-input" id="buddydrive-link-' .$buddyfile_id. '" value="' .buddydrive_get_action_link(). '"></div>';
				}
				if ( buddydrive_current_user_can_share() && bp_is_active( 'activity' ) )
					$inside_top[]= '<a class="buddydrive-profile-activity" href="#">' . __( 'Share', 'buddydrive' ). '</a>';
				break;
			case 'password':
				if ( buddydrive_current_user_can_link( $privacy ) ){
					$inside_top[]= '<a class="buddydrive-show-link" href="#">' . __( 'Link', 'buddydrive' ). '</a>';
					$inside_bottom .= '<div class="buddydrive-ra-link hide ba"><input type="text" class="buddydrive-file-input" id="buddydrive-link-' .$buddyfile_id. '" value="' .buddydrive_get_action_link(). '"></div>';
				}
				if ( buddydrive_current_user_can_share() && bp_is_active( 'messages' ) )
					$inside_top[]= '<a class="buddydrive-private-message" href="'.bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?buddyitem='.$buddyfile_id.'">' . __('Share', 'buddydrive'). '</a>';
				break;
			case 'friends':
				if ( buddydrive_current_user_can_link( $privacy ) ){
					$inside_top[]= '<a class="buddydrive-show-link" href="#">' . __( 'Link', 'buddydrive' ). '</a>';
					$inside_bottom .= '<div class="buddydrive-ra-link hide ba"><input type="text" class="buddydrive-file-input" id="buddydrive-link-' .$buddyfile_id. '" value="' .buddydrive_get_action_link(). '"></div>';
				}
				if ( buddydrive_current_user_can_share() && bp_is_active( 'messages' ) )
					$inside_top[]= '<a class="buddydrive-private-message" href="'.bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?buddyitem='.$buddyfile_id.'&friends=1">' . __( 'Share', 'buddydrive' ). '</a>';
				break;
			case 'groups':
				if ( buddydrive_current_user_can_link( $privacy ) ){
					$inside_top[]= '<a class="buddydrive-show-link" href="#">' . __( 'Link', 'buddydrive' ). '</a>';
					$inside_bottom .= '<div class="buddydrive-ra-link hide ba"><input type="text" class="buddydrive-file-input" id="buddydrive-link-' .$buddyfile_id. '" value="' .buddydrive_get_action_link(). '"></div>';
				}
				if ( buddydrive_current_user_can_share() && bp_is_active( 'activity' ) && bp_is_active( 'groups' ) )
					$inside_top[]= '<a class="buddydrive-group-activity" href="#">' . __( 'Share', 'buddydrive' ). '</a>';
				if ( buddydrive_current_user_can_remove( $privacy['group'] ) && bp_is_active( 'groups') )
					$inside_top[]= '<a class="buddydrive-remove-group" href="#" data-group="'.$privacy['group'].'">' . __( 'Remove', 'buddydrive' ). '</a>';
				break;
		}

		if ( ! empty( $inside_top ) )
			$inside_top = '<div class="buddydrive-action-btn">'. implode( ' | ', $inside_top ).'</div>';

		if ( ! empty( $inside_top ) )
			$row_actions .= '<div class="buddydrive-row-actions">' . $inside_top . $inside_bottom .'</div>';

		return apply_filters( 'buddydrive_get_row_actions', $row_actions );
	}


/**
 * Displays a form if the file needs a password to be downloaded.
 */
function buddydrive_file_password_form() {
	?>
	<form action="" method="post" class="standard-form">
		<p><label for="buddypass"><?php _e( 'Password required', 'buddydrive' );?></label>
		<input type="password" id="buddypass" name="buddyfile-form[password]"></p>
		<p><input type="submit" value="Send" name="buddyfile-form[submit]"></p>
	</form>
	<?php
}

/**
 * Outputs the BuddyDrive user's toolbar & sort selectbox.
 */
function buddydrive_item_nav() {
	?>
	<form action="" method="get" id="buddydrive-form-filter">
		<nav id="buddydrive-item-nav">
			<ul>
				
				<?php do_action( 'buddydrive_member_before_toolbar' ); ?>

				<?php if ( buddydrive_is_user_buddydrive() ):?>

					<li id="buddydrive-action-new-file">
						<a href="#" id="buddy-new-file" title="<?php _e('New File', 'buddydrive');?>"><i class="icon bd-icon-newfile"></i></a>
					</li>
					<li id="buddydrive-action-new-folder">
						<a href="#" id="buddy-new-folder" title="<?php _e('New Folder', 'buddydrive');?>"><i class="icon bd-icon-newfolder"></i></a>
					</li>
					<li id="buddydrive-action-edit-item">
						<a href="#" id="buddy-edit-item" title="<?php _e('Edit Item', 'buddydrive');?>"><i class="icon bd-icon-edit"></i></a>
					</li>
					<li id="buddydrive-action-delete-item">
						<a href="#" id="buddy-delete-item" title="<?php _e('Delete Item(s)', 'buddydrive');?>"><i class="icon bd-icon-delete"></i></a>
					</li>
					<li id="buddydrive-action-analytics">
						<a><i class="icon bd-icon-analytics"></i> <?php buddydrive_user_used_quota();?></a>
					</li>

				<?php endif;?>

				<?php do_action( 'buddydrive_member_after_toolbar' ); ?>

				<li class="last"><?php esc_html_e( 'Order by:', 'buddydrive' );?>
					<select name="buddydrive_filter" id="buddydrive-filter">
						<option value="title"><?php esc_html_e( 'Name', 'buddydrive' ) ;?></option>
						<option value="modified"><?php esc_html_e( 'Last edit', 'buddydrive' ) ;?></option>
					</select>
				</li>

			</ul>
		</nav>
	</form>
	<?php
}

/**
 * Outputs the BuddyDrive user's stats.
 */
function buddydrive_wpadmin_profile_stats( $args ) {
	if ( empty( $args['user_id'] ) )
		return;

	$space_left = buddydrive_get_user_space_left( false, $args['user_id'] ) .'%';

	echo '<li class="buddydrive-profile-stats">' . $space_left . '</li>';
}
add_action( 'bp_members_admin_user_stats', 'buddydrive_wpadmin_profile_stats', 10, 1 );
