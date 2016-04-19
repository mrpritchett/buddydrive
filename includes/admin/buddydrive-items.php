<?php
/**
 * Functions & classes to manage BuddyDrive items
 *
 * @since  1.0
 * @deprecated 2.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Include WP's list table class
if ( !class_exists( 'WP_List_Table' ) ) require( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 * Inspired by BuddyPress 1.7 group admin UI
 */

/**
 * Adds the BuddyDrive item menu to the BuddyPress array of components admin ui
 *
 * @param  array  $custom_menus
 * @return array including BuddyDrive menu
 */
function buddydrive_admin_menu_order( $custom_menus = array() ) {
	array_push( $custom_menus, 'buddydrive-files' );
	return $custom_menus;
}

add_filter( 'bp_admin_menu_order', 'buddydrive_admin_menu_order' );



/**
 * Loads and choose the right action to do in the administration of BuddyDrive Items
 *
 * @global object $buddydrive_list_table
 * @uses remove_query_arg() to remove some args to the url
 * @uses check_admin_referer() for security reasons
 * @uses wp_parse_id_list() to parse ids from a comma separated list
 * @uses buddydrive_delete_item() to delete one or more items
 * @uses add_query_arg() to add args to the url
 * @uses bp_core_redirect() to safely redirect to the right admin area
 * @uses add_screen_option() to organize the layout
 * @uses get_current_screen() to get the admin screen
 * @uses add_meta_box() to register the meta boxes
 * @uses wp_enqueue_script() to enqueue the needed scripts
 * @uses BuddyDrive_List_Table() to init the list of items
 * @uses buddydrive_get_buddyfile() to get a single BuddyDrive item
 * @uses buddydrive_get_folder_post_type() to get the BuddyFolder post type
 * @uses buddydrive_get_file_post_type() to get the BuddyFile post type
 * @uses wp_kses() to sanitize datas
 * @uses buddydrive_update_item() to update a BuddyDrive item
 * @uses wp_redirect() to redirect to the right admin area
 */
function buddydrive_files_admin_load() {
	global $buddydrive_list_table;

	$doaction = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

	// If the bottom is set, let it override the action
	if ( ! empty( $_REQUEST['action2'] ) && $_REQUEST['action2'] != "-1" ) {
		$doaction = $_REQUEST['action2'];
	}

	$redirect_to = remove_query_arg( array( 'action', 'action2', 'bid', 'deleted', 'error', 'updated' ), $_SERVER['REQUEST_URI'] );

	do_action( 'buddydrive_files_admin_load', $doaction );

	if ( 'do_delete' == $doaction && ! empty( $_GET['bid'] ) ) {

		check_admin_referer( 'buddydrive-delete' );

		$item_ids = wp_parse_id_list( $_GET['bid'] );

		$deleted = buddydrive_delete_item( array( 'ids' => $item_ids, 'user_id' => false ) );

		$redirect_to = add_query_arg( 'deleted', count( $deleted ), $redirect_to );

		bp_core_redirect( $redirect_to );

	} elseif ( 'edit' == $doaction && ! empty( $_GET['bid'] ) ) {
		// columns screen option
		add_screen_option( 'layout_columns', array( 'default' => 2, 'max' => 2, ) );

		get_current_screen()->add_help_tab( array(
			'id'      => 'buddydrive-edit-overview',
			'title'   => __( 'Overview', 'buddydrive' ),
			'content' =>
				'<p>' . __( 'This page is a convenient way to edit the details associated with one of your file or folder.', 'buddydrive' ) . '</p>' .
				'<p>' . __( 'The Name and Description box is fixed in place, but you can reposition all the other boxes using drag and drop, and can minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to hide or unhide, or to choose a 1- or 2-column layout for this screen.', 'buddydrive' ) . '</p>'
		) );

		// Register metaboxes for the edit screen.
		add_meta_box( 'submitdiv', _x( 'Save', 'buddydrive-item admin edit screen', 'buddydrive' ), 'buddydrive_admin_edit_metabox_status', get_current_screen()->id, 'side', 'high' );
		add_meta_box( 'buddydrive_item_privacy', _x( 'Privacy', 'buddydrive-item admin edit screen', 'buddydrive' ), 'buddydrive_admin_edit_metabox_privacy', get_current_screen()->id, 'side', 'core' );
		add_meta_box( 'buddydrive_item_children', _x( 'Files', 'buddydrive-item admin edit screen', 'buddydrive' ), 'buddydrive_admin_edit_metabox_list_files', get_current_screen()->id, 'normal', 'core' );

		do_action( 'buddydrive_files_admin_meta_boxes' );

		// Enqueue javascripts
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'dashboard' );
		wp_enqueue_script( 'comment' );

	// Index screen
	} else {

		$buddydrive_list_table = new BuddyDrive_List_Table();

	}

	if ( $doaction && 'save' == $doaction ) {

		// Get item ID
		$item_id = isset( $_REQUEST['bid'] ) ? (int) $_REQUEST['bid'] : '';

		$redirect_to = add_query_arg( array(
			'bid'    => (int) $item_id,
			'action' => 'edit'
		), $redirect_to );

		// Check this is a valid form submission
		check_admin_referer( 'edit-buddydrive-item_' . $item_id );


		$item = buddydrive_get_buddyfile( $item_id, array( buddydrive_get_folder_post_type(), buddydrive_get_file_post_type() ) );

		if ( empty( $item->title ) ) {
			wp_redirect( $redirect_to );
			exit;
		}

		$args = array();

		if( !empty( $_POST['buddydrive-edit']['item-title'] ) )
			$args['title'] = wp_kses( $_POST['buddydrive-edit']['item-title'], array() );
		if( !empty( $_POST['buddydrive-edit']['item-content'] ) )
			$args['content'] = wp_kses( $_POST['buddydrive-edit']['item-content'], array() );
		if( !empty( $_POST['buddydrive-edit']['sharing'] ) )
			$args['privacy'] = $_POST['buddydrive-edit']['sharing'];
		if( !empty( $_POST['buddydrive-edit']['password'] ) )
			$args['password'] = wp_kses( $_POST['buddydrive-edit']['password'], array() );
		if( !empty( $_POST['buddydrive-edit']['buddygroup'] ) )
			$args['group'] = $_POST['buddydrive-edit']['buddygroup'];

		$args['parent_folder_id'] = !empty( $_POST['buddydrive-edit']['folder'] ) ? intval( $_POST['buddydrive-edit']['folder'] ) : 0 ;

		$updated = buddydrive_update_item( $args, $item );

		if( !empty( $updated ) )
			$redirect_to = add_query_arg( 'updated', 1, $redirect_to );
		else
			$redirect_to = add_query_arg( 'error', 1, $redirect_to );

		wp_redirect( apply_filters( 'buddydrive_item_admin_edit_redirect', $redirect_to ) );
		exit;

	}
}

/**
 * Choose the right section to display
 *
 * @uses buddydrive_files_admin_edit() to load the edit page of a single item
 * @uses buddydrive_files_admin_delete() to request for a confirmation
 * @uses buddydrive_files_admin_index() to load the list of BuddyDrive items
 */
function buddydrive_files_admin() {
	// Decide whether to load the index or edit screen
	$doaction = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

	if ( ! empty( $_REQUEST['action2'] ) && $_REQUEST['action2'] != "-1" ) {
		$doaction = $_REQUEST['action2'];
	}

	// Display the single item edit screen
	if ( 'edit' == $doaction && ! empty( $_GET['bid'] ) ) {
		buddydrive_files_admin_edit();

	// Display the item deletion confirmation screen
	} else if ( 'delete' == $doaction && ! empty( $_GET['bid'] ) ) {
		buddydrive_files_admin_delete();

	// Otherwise, display the items index screen
	} else {
		buddydrive_files_admin_index();
	}
}

/**
 * The list of BuddyDrive items
 *
 * @global object $buddydrive_list_table
 * @global string $plugin_page
 * @uses BuddyDrive_List_Table::prepare_items() to prepare the BuddyDrive items for display
 * @uses screen_icon() to display BuddyDrive icon
 * @uses wp_html_excerpt truncate the string provided
 * @uses esc_html sanitize the string
 * @uses BuddyDrive_List_Table::views() to display the available views
 * @uses BuddyDrive_List_Table::search_box() to display the search box
 * @uses BuddyDrive_List_Table::display() to display each item on its own row
 */
function buddydrive_files_admin_index() {
	global $buddydrive_list_table, $plugin_page;

	$messages = array();

	// If the user has just made a change to an item, build status messages
	if ( ! empty( $_REQUEST['deleted'] ) ) {
		$deleted  = ! empty( $_REQUEST['deleted'] ) ? (int) $_REQUEST['deleted'] : 0;

		if ( $deleted > 0 ) {
			$messages[] = sprintf( _n( '%s item has been permanently deleted.', '%s items have been permanently deleted.', $deleted, 'buddydrive' ), number_format_i18n( $deleted ) );
		}
	}

	// Prepare the BuddyDrive items for display
	$buddydrive_list_table->prepare_items();

	// Call an action for plugins to modify the messages before we display the edit form
	do_action( 'buddydrive_files_admin_index', $messages ); ?>

	<div class="wrap">
		<?php screen_icon( 'buddydrive' ); ?>
		<h2>
			<?php _e( 'BuddyDrive Files', 'buddydrive' ); ?>

			<?php if ( !empty( $_REQUEST['s'] ) ) : ?>
				<span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'buddydrive' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
			<?php endif; ?>
		</h2>

		<?php // If the user has just made a change to an item, display the status messages ?>
		<?php if ( !empty( $messages ) ) : ?>
			<div id="moderated" class="<?php echo ( ! empty( $_REQUEST['error'] ) ) ? 'error' : 'updated'; ?>"><p><?php echo implode( "<br/>\n", $messages ); ?></p></div>
		<?php endif; ?>

		<?php // Display each item on its own row ?>
		<?php $buddydrive_list_table->views(); ?>

		<form id="buddydrive-form" action="" method="get">
			<?php $buddydrive_list_table->search_box( __( 'Search all Items', 'buddydrive' ), 'buddydrive-files' ); ?>
			<input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
			<?php $buddydrive_list_table->display(); ?>
		</form>

	</div>
	<?php
}

/**
 * Makes it possible to edit a single item
 *
 * @uses is_super_admin() to check for the current user is an admin
 * @uses buddydrive_get_buddyfile() to get a single item
 * @uses buddydrive_get_folder_post_type() to get BuddyFolder post type
 * @uses buddydrive_get_file_post_type() to get BuddyFile post type
 * @uses remove_meta_box() in case of a file we don't need to list children
 * @uses get_current_screen() to get current admin screen
 * @uses remove_query_arg() to remove some args to the url
 * @uses add_query_args() to add some args to the url
 * @uses screen_icon() to display the BuddyDrive icon
 * @uses esc_attr() to sanitize data
 * @uses wp_kses() to sanitize data
 * @uses do_meta_boxes() to display the meta boxes
 * @uses wp_nonce_field() for security reasons
 */
function buddydrive_files_admin_edit() {

	if ( ! is_super_admin() )
		die( '-1' );

	$messages = array();
	$is_error = ! empty( $_REQUEST['error']   ) ? $_REQUEST['error']   : false;
	$updated  = ! empty( $_REQUEST['updated'] ) ? $_REQUEST['updated'] : false;

	if ( $is_error ) {
		$messages[] = __( 'An error occurred when trying to update your item details.', 'buddydrive' );
	} else if ( ! empty( $updated ) ) {
		$messages[] = __( 'The item has been updated successfully.', 'buddydrive' );
	}

	$item      = buddydrive_get_buddyfile( $_GET['bid'], array( buddydrive_get_folder_post_type(), buddydrive_get_file_post_type() ) );
	$item_name = isset( $item->title ) ? apply_filters( 'buddydrive_get_item_title', $item->title ) : '';

	if( $item->post_type == buddydrive_get_file_post_type() )
		remove_meta_box( 'buddydrive_item_children', get_current_screen()->id, 'normal' );

	// Construct URL for form
	$form_url = remove_query_arg( array( 'action', 'action2', 'deleted', 'error' ), $_SERVER['REQUEST_URI'] );
	$form_url = add_query_arg( 'action', 'save', $form_url );

	// Call an action for plugins to modify the BuddyDrive item before we display the edit form
	do_action_ref_array( 'buddydrive_files_admin_edit', array( &$item ) ); ?>

	<div class="wrap" id="buddydrive-admin-item">
		<?php screen_icon( 'buddydrive' ); ?>
		<h2><?php _e( 'Edit BuddyDrive Item', 'buddydrive' ); ?></h2>

		<?php if ( !empty( $messages ) ) : ?>
			<div id="moderated" class="<?php echo ( $is_error ) ? 'error' : 'updated'; ?>"><p><?php echo implode( "<br/>\n", $messages ); ?></p></div>
		<?php endif; ?>

		<?php if ( ! empty( $item ) ) : ?>

			<form action="<?php echo esc_url( $form_url ); ?>" id="buddydrive-edit-form" method="post">
				<div id="poststuff">

					<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
						<div id="post-body-content">
							<div id="postdiv" class="postarea">
								<div id="buddydrive_item_name" class="postbox">
									<h3><?php _e( 'Name and Description', 'buddydrive' ); ?></h3>
									<div class="inside">
										<label for="buddydrive-item-title"><?php _e( 'Name', 'buddydrive' );?></label>
										<input type="text" name="buddydrive-edit[item-title]" id="buddydrive-item-title" value="<?php echo esc_attr( stripslashes( $item_name ) ) ?>" />

										<?php if( $item->post_type == buddydrive_get_file_post_type() ) :?>
											<label for="buddydrive-item-content"><?php _e( 'Description', 'buddydrive' );?></label>
											<textarea name="buddydrive-edit[item-content]" id="buddydrive-item-content" placeholder="<?php _e('140 characters to do so', 'buddydrive')?>" maxlength="140"><?php echo wp_kses( stripslashes( $item->content ), array() );?></textarea>

										<?php endif;?>

									</div>
								</div>
							</div>
						</div><!-- #post-body-content -->

						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes( get_current_screen()->id, 'side', $item ); ?>
						</div>

						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes( get_current_screen()->id, 'normal', $item ); ?>
							<?php do_meta_boxes( get_current_screen()->id, 'advanced', $item ); ?>
						</div>
					</div><!-- #post-body -->

				</div><!-- #poststuff -->
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				<?php wp_nonce_field( 'edit-buddydrive-item_' . $item->ID ); ?>
			</form>

		<?php else : ?>
			<p><?php printf( __( 'No item found with this ID. <a href="%s">Go back and try again</a>.', 'buddydrive' ), esc_url( bp_get_admin_url( 'admin.php?page=buddydrive-files' ) ) ); ?></p>
		<?php endif; ?>

	</div><!-- .wrap -->
	<?php
}

/**
 * The Action metabox (save updates)
 *
 * @param  object $item The BuddyDrive Item object
 * @uses add_query_args() to add some args to the url
 * @uses bp_get_admin_url() to build the admin url
 * @uses wp_nonce_url() for security reasons
 * @uses submit_button() to build the main action button
 */
function buddydrive_admin_edit_metabox_status( $item ) {
	$base_url = add_query_arg( array(
		'page' => 'buddydrive-files',
		'bid'  => $item->ID
	), bp_get_admin_url( 'admin.php' ) ); ?>

	<div id="submitcomment" class="submitbox">
		<div id="major-publishing-actions">
			<div id="delete-action">
				<a class="submitdelete deletion" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', $base_url ), 'buddydrive-item-delete' ) ); ?>"><?php esc_html_e( 'Delete Item', 'buddydrive' ) ?></a>
			</div>

			<div id="publishing-action">
				<?php submit_button( __( 'Save Changes', 'buddydrive' ), 'primary', 'save', false, array( 'tabindex' => '4' ) ); ?>
			</div>
			<div class="clear"></div>
		</div><!-- #major-publishing-actions -->
	</div><!-- #submitcomment -->

<?php
}

/**
 * Privacy Metabox settings
 *
 * @param  object $item The BuddyDrive Item object
 * @uses get_post_meta() to get the privacy settings
 * @uses buddydrive_get_show_owner_avatar() to get owner's avatar
 * @uses buddydrive_user_used_quota() to get user's space left
 * @uses buddydrive_select_sharing_options() to display the select box of available privacy options
 * @uses buddydrive_select_user_group() to display the group select box
 * @uses buddydrive_get_file_post_type() to get the BuddyFile post type
 * @uses buddydrive_select_folder_options() to display the parent folder select box
 */
function buddydrive_admin_edit_metabox_privacy( $item ) {

	$privacy_status = buddydrive_get_privacy( $item->ID );
	$owner = $item->user_id;
	$avatar  = buddydrive_get_show_owner_avatar( $owner );
	?>
	<div class="buddydrive-owner-section" id="buddydrive-owner-section-info">
		<div>
			<label><?php _e( 'Owner', 'buddydrive' );?></label>
			<table>
				<tr>
					<td><?php echo $avatar;?></td>
					<td><?php _e('BuddyDrive Usage', 'buddydrive');?> : <?php buddydrive_user_used_quota( false, $owner );?> %</td>
				</tr>
			</table>
		</div>
		<input type="hidden" value="<?php echo $owner;?>" id="buddydrive-owner-id" disabled>
	</div>

	<?php if( empty( $item->post_parent) ) :?>

		<div class="buddydrive-privacy-section" id="buddydrive-privacy-section-options">
			<label for="buddydrive-sharing-option"><?php _e( 'Item Sharing options', 'buddydrive' );?></label>
			<?php buddydrive_select_sharing_options( 'buddydrive-admin-sharing-options', $privacy_status, 'buddydrive-edit[sharing]' );?>

			<div id="buddydrive-admin-privacy-detail">
				<?php if( $privacy_status == 'password' ):?>
					<label for="buddydrive-password"><?php _e( 'Password', 'buddydrive' );?></label>
					<input type="text" value="<?php echo esc_attr( stripslashes( $item->password ) ) ?>" name="buddydrive-edit[password]" id="buddydrive-password"/>
				<?php elseif( $privacy_status == 'groups') :?>
					<label for="buddygroup"><?php _e( 'Choose the group', 'buddydrive' );?></label>
					<?php buddydrive_select_user_group( $owner, $item->group, 'buddydrive-edit[buddygroup]' );?>
				<?php endif;?>
			</div>
		</div>

		<?php if ( empty( $privacy_status ) ): ?>
			<p><strong><?php _e( 'The privacy of this item is not defined, please correct this!', 'buddydrive' );?></strong></p>
		<?php endif; ?>

	<?php else:?>

		<div class="buddydrive-privacy-section" id="buddydrive-privacy-section-options">
			<label for="buddydrive-sharing-option"><?php _e('Item Sharing options', 'buddydrive');?></label>
			<p><?php printf( __( "Privacy of this item rely on its parent <a href=\"%s\">folder</a>", "buddydrive"), esc_url( add_query_arg( array( 'page' => 'buddydrive-files', 'bid' => $item->post_parent, 'action' => 'edit'), bp_get_admin_url( 'admin.php' ) ) ) );?></p>
		</div>

	<?php endif;?>

	<?php if( $item->post_type == buddydrive_get_file_post_type() ) :?>

		<div class="buddydrive-folder-section" id="buddydrive-folder-section-options">
			<label for="buddydrive-folder-option"><?php _e( 'Folder', 'buddydrive' );?></label>
			<?php buddydrive_select_folder_options( $owner, $item->post_parent, 'buddydrive-edit[folder]' );?>
		</div>

	<?php endif;?>

<?php
}


/**
 * Builds an admin BuddyDrive Items loop
 * @param  integer $folder_id the folder id
 * @param  integer $paged     the page to load
 * @uses add_query_args() to add some args to the url
 * @uses bp_get_admin_url() to build the admin url
 * @uses The BuddyDrive loop with some template tags
 * @uses wp_nonce_url() for security reasons
 */
function buddydrive_admin_edit_files_loop( $folder_id = 0, $paged = 1 ) {
	$form_url = add_query_arg( array( 'page' => 'buddydrive-files'), bp_get_admin_url( 'admin.php' ) );

	if ( buddydrive_has_items( array( 'buddydrive_parent' => $folder_id, 'paged' => $paged ) ) ) :?>

		<?php while ( buddydrive_has_items() ): buddydrive_the_item(); ?>

			<tr id="item-<?php buddydrive_item_id();?>">
				<td>
					<input type="checkbox" name="bid[]" class="buddydrive-item-cb" value="<?php esc_attr( buddydrive_item_id() );?>">
				</td>
				<td>
					<?php buddydrive_item_icon();?>&nbsp;<a href="<?php buddydrive_action_link();?>" class="<?php buddydrive_action_link_class();?>" title="<?php esc_attr( buddydrive_item_title() );?>"<?php buddydrive_item_attribute();?>><?php esc_html( buddydrive_item_title() );?></a>
					<div class="row-actions">
						<?php
						$base_url = add_query_arg( array( 'bid' => buddydrive_get_item_id() ), $form_url );
						$edit_url = add_query_arg( array( 'action' => 'edit' ), $base_url );
						$delete_url = wp_nonce_url( $base_url . "&amp;action=delete", 'buddydrive-delete' );
						?>
						<span class="edit">
							<a href="<?php echo esc_url( $edit_url );?>"><?php esc_html_e( 'Edit', 'buddydrive' );?></a> |
						</span>
						<span class="delete">
							<a href="<?php echo esc_url( $delete_url );?>"><?php esc_html_e( 'Delete', 'buddydrive' );?></a>
						</span>
					</div>
				</td>
				<td>
					<?php buddydrive_item_mime_type();?>
				</td>
				<td>
					<?php buddydrive_item_date();?>
				</td>
			</tr>

		<?php endwhile;?>

		<?php if ( buddydrive_has_more_items() ):?>
			<tr>
				<td class="buddydrive-load-more" colspan="4">
					<a href="#more-buddydrive"><?php _e( 'Load More', 'buddydrive' ); ?></a>
				</td>
			</tr>
		<?php endif;?>

	<?php else:?>
		<tr><td colspan="4"><?php _e( 'No files attached to this folder', 'buddydrive' );?></td></tr>
	<?php endif;
}

/**
 * List the files of a folder in a metabox
 *
 * @param  object $item BuddyDrive Item object
 * @uses buddydrive_get_folder_post_type() to check  for the BuddyFolder post type
 * @uses buddydrive_admin_edit_files_loop() to list the attached files
 */
function buddydrive_admin_edit_metabox_list_files( $item ) {
	$is_folder = ( $item->post_type == buddydrive_get_folder_post_type() ) ? true : false ;
	?>

	<?php if ( !empty( $is_folder ) ): ?>
	<div class="buddydrive-children-section" id="buddydrive-children-section-files">
			<label for="buddydrive-list-files"></label>
			<div class="alignleft actions">
				<select name="action2">
					<option value="-1" selected="selected"><?php _e( 'Bulk Actions', 'buddydrive' );?></option>
					<option value="delete"><?php _e( 'Delete', 'buddydrive');?></option>
				</select>
				<input type="submit" name="" id="doaction" class="button action" value="Apply">
			</div>
			<table class="widefat" id="buddydrive-admin-files" data-folder="<?php echo $item->ID;?>">
				<thead>
					<tr><th colspan="2"><?php _e( 'Name', 'buddydrive');?></th><th><?php _e( 'Mime type', 'buddydrive' );?></th><th><?php _e( 'Last Edit', 'buddydrive' );?></th></tr>
				</thead>
				<tbody>
					<?php buddydrive_admin_edit_files_loop( $item->ID );?>
				</tbody>
			</table>
	</div>
	<?php else :?>
		<div class="buddydrive-children-section" id="buddydrive-children-section-files">
			<?php _e( 'The item displayed is a file, the list of files is only available for a folder', 'buddydrive' );?>
		</div>
	<?php endif; ?>

<?php
}

/**
 * Ask for a confirmation before deleting BuddyDrive items
 *
 * @uses is_super_admin() too check current user is admin
 * @uses wp_parse_id_list() to parse the comma separated list of BuddyDrive item ids
 * @uses buddydrive_get_buddyfiles_by_ids() to get some data about each BuddyDrive items of this list
 * @uses remove_query_arg() remove some arguments to the url
 * @uses screen_icon() displays the BuddyDrive icon
 * @uses esc_html() to sanitize title
 * @uses buddydrive_get_folder_post_type() get the BuddyFolder post type
 * @uses wp_nonce_url() for security reasons
 * @uses esc_attr to sanitize url
 */
function buddydrive_files_admin_delete(){
	if ( ! is_super_admin() )
		die( '-1' );

	$item_ids = isset( $_REQUEST['bid'] ) ? $_REQUEST['bid'] : 0;
	if ( ! is_array( $item_ids ) ) {
		$item_ids = explode( ',', $item_ids );
	}
	$item_ids = wp_parse_id_list( $item_ids );
	$items    = buddydrive_get_buddyfiles_by_ids( $item_ids );

	// Create a new list of item ids, based on those that actually exist
	$bids = array();
	foreach ( $items as $item ) {
		$bids[] = $item->ID;
	}

	$base_url  = remove_query_arg( array( 'action', 'action2', 'paged', 's', '_wpnonce', '_wp_http_referer', 'bid' ), $_SERVER['REQUEST_URI'] ); ?>

	<div class="wrap">
		<?php screen_icon( 'buddydrive' ); ?>
		<h2><?php _e( 'Delete Items', 'buddydrive' ); ?></h2>
		<p><?php _e( 'You are about to delete the following items:', 'buddydrive' ); ?></p>

		<ul class="buddydrive-items-delete-list">
		<?php foreach ( $items as $item ) : ?>
			<li>
				<?php echo esc_html( $item->post_title ) ?>
				<?php if( $item->post_type == buddydrive_get_folder_post_type() ):?>
					<?php _e('(and all the files of this folder)', 'buddydrive');?>
				<?php endif;?>
			</li>
		<?php endforeach; ?>
		</ul>

		<p><strong><?php _e( 'This action cannot be undone.', 'buddydrive' ); ?></strong></p>

		<a class="button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'do_delete', 'bid' => implode( ',', $bids ) ), $base_url ), 'buddydrive-delete' ) ); ?>"><?php esc_html_e( 'Delete Permanently', 'buddydrive' ); ?></a>
		<a class="button" href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( 'Cancel', 'buddydrive' ); ?></a>
	</div>

	<?php
}

/**
 * List table class for the BuddyDrive component admin page.
 *
 * @since BuddyDrive (1.0)
 */
class BuddyDrive_List_Table extends WP_List_Table {

	/**
	 * What type of view is being displayed?
	 *
	 * @since BuddyDrive (1.0)
	 */
	public $view = 'all';

	/**
	 * Item counts for each post type
	 *
	 * @since BuddyDrive (1.0)
	 */
	public $item_counts = 0;

	/**
	 * Constructor
	 *
	 * @since BuddyDrive (1.0)
	 */
	public function __construct() {

		// Define singular and plural labels, as well as whether we support AJAX.
		parent::__construct( array(
			'ajax'     => false,
			'plural'   => 'items',
			'singular' => 'item',
		) );
	}

	/**
	 * Handle filtering of data, sorting, pagination, and any other data-manipulation required prior to rendering.
	 *
	 * @since BuddyDrive (1.0)
	 * @uses get_current_screen() to get current admin screen
	 * @uses WP_List_Table::get_pagenum to set the current page
	 * @uses buddydrive_get_folder_post_type() to get BuddyFolder post type
	 * @uses buddydrive_get_file_post_type() to get BuddyFile post type
	 * @uses The BuddyDrive loop to list items
	 * @uses WP_List_Table::set_pagination_args() to build the pagination
	 */
	function prepare_items() {
		global $buddydrive_template;

		$screen = get_current_screen();

		// Option defaults
		$include_id   = false;
		$search_terms = false;

		// Set current page
		$page = $this->get_pagenum();

		// Set per page from the screen options
		$per_page = 20;

		// Sort order
		$order = 'ASC';
		if ( !empty( $_REQUEST['order'] ) ) {
			$order = ( 'desc' == strtolower( $_REQUEST['order'] ) ) ? 'DESC' : 'ASC';
		}

		// Order by - default to title
		$orderby = false;
		if ( !empty( $_REQUEST['orderby'] ) ) {
			switch ( $_REQUEST['orderby'] ) {
				case 'name' :
					$orderby = 'title';
					break;
				case 'owner' :
					$orderby = 'user_id';
					break;
				case 'last_edit' :
					$orderby = 'post_modified_gmt';
					break;
			}
		}

		// Are we doing a search?
		if ( !empty( $_REQUEST['s'] ) )
			$search_terms = $_REQUEST['s'];

		// Check if user has clicked on a specific item (if so, fetch only that item).
		if ( !empty( $_REQUEST['bid'] ) )
			$include_id = (int) $_REQUEST['bid'];

		$type = array( buddydrive_get_folder_post_type(), buddydrive_get_file_post_type() );

		/* Set the current view */
		if ( isset( $_GET['buddydrive_type'] ) && in_array( $_GET['buddydrive_type'], $type ) ) {
			$this->view = $_GET['buddydrive_type'];
			$type =  $_GET['buddydrive_type'];
		}

		$buddydrive_args = array(
			'buddydrive_scope'  => 'admin',
			'per_page'        => $per_page,
			'paged'           => $page,
			'type'            => $type
		);

		if( !empty( $orderby) ) {
			$buddydrive_args['orderby'] = $orderby;
			$buddydrive_args['order'] = $order;
		}
		if( !empty( $search_terms ) ) {
			$buddydrive_args['search'] = $search_terms;
		}


		$buddydrive_items = array();

		if ( buddydrive_has_items( $buddydrive_args ) ) {
			while ( buddydrive_has_items() ) {
				buddydrive_the_item();
				$buddydrive_items[] = (array) $buddydrive_template->query->post;
			}
		}

		// Set raw data to display
		$this->items = $buddydrive_items;

		// Store information needed for handling table pagination
		$this->set_pagination_args( array(
			'per_page'    => $per_page,
			'total_items' => $buddydrive_template->query->found_posts,
			'total_pages' => ceil( $buddydrive_template->query->found_posts / $per_page )
		) );
	}

	/**
	 * Get an array of all the columns on the page
	 *
	 * @return array the column headers
	 * @since BuddyDrive (1.0)
	 * @uses WP_List_Table::get_columns()
	 * @uses WP_List_Table::get_sortable_columns()
	 */
	function get_column_info() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
			$this->get_primary_column_name(),
		);

		return $this->_column_headers;
	}

	/**
	 * Displays a message on screen when no items are found (e.g. no search matches)
	 *
	 * @since BuddyDrive (1.0)
	 */
	function no_items() {
		_e( 'No items found.', 'buddydrive' );
	}

	/**
	 * Outputs the BuddyDrive Items data table
	 *
	 * @since BuddyDrive (1.0)
	 * @uses WP_List_Table::display_tablenav()
	 * @uses WP_List_Table::get_table_classes()
	 * @uses WP_List_Table::print_column_headers()
	 * @uses WP_List_Table::display_rows_or_placeholder()
	*/
	function display() {

		$this->display_tablenav( 'top' ); ?>

		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>" cellspacing="0">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<?php $this->print_column_headers( false ); ?>
				</tr>
			</tfoot>

			<tbody id="the-comment-list">
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php

		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @param object $item The current item
	 * @since BuddyDrive (1.0)
	 * @uses esc_attr() to sanitize data
	 * @uses WP_List_Table::single_row_columns() to display the row
	 */
	function single_row( $item = array() ) {
		static $even = false;

		$row_classes = array();

		if ( $even ) {
			$row_classes = array( 'even' );
		} else {
			$row_classes = array( 'alternate', 'odd' );
		}

		$row_classes = apply_filters( 'buddydrive_list_table_single_row_class', $row_classes, $item['ID'] );
		$row_class = ' class="' . implode( ' ', $row_classes ) . '"';

		echo '<tr' . $row_class . ' id="item-' . esc_attr( $item['ID'] ) . '" data-parent_id="' . esc_attr( $item['ID'] ) . '" data-root_id="' . esc_attr( $item['ID'] ) . '">';
		echo $this->single_row_columns( $item );
		echo '</tr>';

		$even = ! $even;
	}

	/**
	 * Get the list of views available on this table (e.g. "all", "folder").
	 *
	 * @since BuddyDrive (1.0)
	 * @uses remove_query_arg() to remove arguments to url
	 * @uses esc_attr() to sanitize data
	 * @uses esc_url() to sanitize url
	 * @uses add_query_arg() to add arguments to the url
	 */
	function get_views() {
		$url_base = remove_query_arg( array( 's','orderby', 'order', 'buddydrive_type', '_wpnonce', '_wp_http_referer', 'action', 'action2', 'paged' ), $_SERVER['REQUEST_URI'] ); ?>
		<ul class="subsubsub">
			<li class="all"><a href="<?php echo esc_url( $url_base ); ?>" class="<?php if ( 'all' == $this->view ) echo 'current'; ?>"><?php _e( 'All', 'buddydrive' ); ?></a> |</li>
			<li class="public"><a href="<?php echo esc_url( add_query_arg( 'buddydrive_type', buddydrive_get_file_post_type(), $url_base ) ); ?>" class="<?php if ( buddydrive_get_file_post_type() == $this->view ) echo 'current'; ?>"><?php _e( 'Files', 'buddydrive' ) ; ?></a> |</li>
			<li class="private"><a href="<?php echo esc_url( add_query_arg( 'buddydrive_type', buddydrive_get_folder_post_type(), $url_base ) ); ?>" class="<?php if ( buddydrive_get_folder_post_type() == $this->view ) echo 'current'; ?>"><?php  _e( 'Folders', 'buddydrive' ); ?></a></li>
			<?php do_action( 'buddydrive_list_table_get_views', $url_base, $this->view ); ?>
		</ul>
	<?php
	}

	/**
	 * Get bulk actions
	 *
	 * @return array Key/value pairs for the bulk actions dropdown
	 * @since BuddyDrive (1.0)
	 */
	function get_bulk_actions() {
		return apply_filters( 'buddydrive_list_table_get_bulk_actions', array(
			'delete' => __( 'Delete', 'buddydrive' )
		) );
	}

	/**
	 * Get the table column titles.
	 *
	 * @see WP_List_Table::single_row_columns()
	 * @return array
	 * @since BuddyDrive (1.0)
	 */
	function get_columns() {
		return apply_filters( 'buddydrive_list_table_columns', array(
			'cb'          => '<input name type="checkbox" />',
			'comment'     => _x( 'Name', 'BuddyDrive admin Item Name column header',               'buddydrive' ),
			'description' => _x( 'Description', 'BuddyDrive admin Item Description column header', 'buddydrive' ),
			'status'      => _x( 'Privacy', 'BuddyDrive admin Privacy Status column header',       'buddydrive' ),
			'owner'       => _x( 'Owner', 'BuddyDrive admin Owner column header',                  'buddydrive' ),
			'mime_type'   => _x( 'Mime type', 'BuddyDrive admin Mime type column header',          'buddydrive' ),
			'last_edit'   => _x( 'Last Edit', 'BuddyDrive admin Last Edit column header',          'buddydrive' )
		));
	}

	/**
	 * Get the column names for sortable columns
	 *
	 * @return array
	 * @since BuddyPress (1.0)
	 */
	function get_sortable_columns() {
		return array(
			'comment'   => array( 'name',      false ),
			'owner'     => array( 'owner',     false ),
			'last_edit' => array( 'last_edit', false )
		);
	}

	/**
	 * Checkbox column
	 *
	 * @param array $item A singular item (one full row)
	 * @see WP_List_Table::single_row_columns()
	 * @since BuddyDrive (1.0)
	 */
	function column_cb( $item = array() ) {
		printf( '<input type="checkbox" name="bid[]" value="%d" />', (int) $item['ID'] );
	}

	/**
	 * Name column, and "quick admin" rollover actions.
	 *
	 * Called "comment" in the CSS so we can re-use some WP core CSS.
	 *
	 * @param array $item A singular item (one full row)
	 * @uses bp_get_admin_url() to build the admin url
	 * @uses wp_nonce_url() for security reasons
	 * @uses buddydrive_get_root_url() to get the BuddyDrive root url
	 * @uses buddydrive_get_folder_post_type() to get the BuddyFolder post type
	 * @uses esc_url() to sanitize url
	 * @see WP_List_Table::single_row_columns()
	 * @since BuddyDrive (1.0)
	 */
	function column_comment( $item = array() ) {

		// Preorder items: download | Edit | Delete
		$actions = array(
			'download'  => '',
			'edit'      => '',
			'delete'    => '',
		);

		// Build actions URLs
		$base_url   = bp_get_admin_url( 'admin.php?page=buddydrive-files&amp;bid=' . $item['ID'] );
		$delete_url = wp_nonce_url( $base_url . "&amp;action=delete", 'buddydrive-delete' );
		$edit_url   = $base_url . '&amp;action=edit';
		$download = trailingslashit(  'file/' . $item['post_name'] );
		$visit_url  = buddydrive_get_root_url() .'/'. $download ;


		// Download
		if( $item['post_type'] != buddydrive_get_folder_post_type() )
			$actions['download'] = sprintf( '<a href="%s">%s</a>', esc_url( $visit_url ), __( 'Download', 'buddydrive' ) );

		// Edit
		$actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), __( 'Edit', 'buddydrive' ) );

		// Delete
		$actions['delete'] = sprintf( '<a href="%s">%s</a>', esc_url( $delete_url ), __( 'Delete', 'buddydrive' ) );

		// Other plugins can filter which actions are shown
		$actions = apply_filters( 'buddydrive_admin_comment_row_actions', array_filter( $actions ), $item );

		$content = apply_filters( 'buddydrive_get_item_title', $item['post_title'] );

		$icon = ( $item['post_type'] != buddydrive_get_folder_post_type() ) ? '<i class="icon bd-icon-file"></i>' : '<i class="icon bd-icon-folder"></i>';

		echo $icon . ' ' . $content . ' ' . $this->row_actions( $actions );
	}

	/**
	 * Description column
	 *
	 * @since BuddyDrive (1.0)
	 * @param array $item A singular item (one full row)
	 */
	function column_description( $item = array() ) {
		echo apply_filters( 'buddydrive_get_item_description', $item['post_content'] );
	}

	/**
	 * Status column
	 *
	 * @since BuddyDrive (1.0)
	 * @param array $item A singular item (one full row)
	 * @uses get_post_meta() to get item's privacy
	 * @uses buddydrive_get_group_avatar() to get the avatar of the group the BuddyItem is attached to
	 *
	 */
	function column_status( $item = array() ) {
		$privacy = buddydrive_get_privacy( $item['ID'] );
		$status_desc = '';

		if( !empty( $privacy ) ) {
			switch ( $privacy ) {
				case 'private' :
					$status_desc = '<i class="icon bd-icon-lock"></i> ' . __( 'Private', 'buddydrive' );
					break;

				case 'password' :
					$status_desc = '<i class="icon bd-icon-key"></i> ' . __( 'Password protected', 'buddydrive' );
					break;

				case 'public'  :
					$status_desc = '<i class="icon bd-icon-unlocked"></i> ' . __( 'Public', 'buddydrive' );
					break;

				case 'friends'  :
					$status_desc = '<i class="icon bd-icon-users"></i> ' . __( 'Friends only', 'buddydrive' );
					break;

				case 'groups'  :
					$avatar  = buddydrive_get_group_avatar( $item['ID'] );
					$status_desc = $avatar;
					break;
			}
		}

		echo apply_filters_ref_array( 'buddydrive_admin_get_item_status', array( $status_desc, $privacy ) );
	}

	/**
	 * Owners column
	 *
	 * @since BuddyDrive (1.0)
	 * @param array $item A singular item (one full row)
	 * @uses buddydrive_get_show_owner_avatar() to get owner's avatar
	 */
	function column_owner( $item = array() ) {

		echo buddydrive_get_show_owner_avatar( $item['post_author'] );
	}

	/**
	 * Mime type column
	 *
	 * @since BuddyDrive (1.0)
	 * @param  array $item A singular item (one full row)
	 */
	function column_mime_type( $item = array() ) {
		$mime_type = !empty( $item['post_mime_type'] ) ? $item['post_mime_type'] : 'folder';
		echo apply_filters( 'buddydrive_get_item_mime_type', $mime_type );
	}

	/**
	 * Last Active column
	 *
	 * @since BuddyDrive (1.0)
	 * @param  array $item A singular item (one full row)
	 * @uses bp_format_time() to format the date
	 */
	function column_last_edit( $item = array() ) {
		$date = $item['post_modified_gmt'];
		$date = bp_format_time( strtotime( $date ), true, false );
		echo apply_filters( 'buddydrive_get_item_date', $date );
	}

	function column_default( $item = array(), $column_name ) {
		return apply_filters( "buddydrive_list_table_custom_column", '', $column_name, (int) $item['ID'] );
	}

	/**
	 * Get name of default primary column
	 *
	 * @since BuddyDrive (1.3.0)
	 * @access protected
	 *
	 * @return string
	 */
	protected function get_default_primary_column_name() {
		return 'comment';
	}
}
