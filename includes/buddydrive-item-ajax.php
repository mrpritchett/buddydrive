<?php
/**
 * BuddyDrive Item Ajax functions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle BuddyDrive Item uploads
 *
 * @since 2.0.0
 */
function buddydrive_upload_file() {
	/**
	 * Sending the json response will be different if
	 * the current Plupload runtime is html4
	 */
	$is_html4 = false;
	if ( ! empty( $_POST['html4' ] ) ) {
		$is_html4 = true;
	}

	// Use deprecated function for the deprecated UI
	if ( empty( $_POST['bp_params'] ) && buddydrive_use_deprecated_ui() ) {
		buddydrive_save_new_buddyfile();
		return;
	}

	// Check the nonce
	check_admin_referer( 'bp-uploader' );

	// Init the BuddyPress parameters
	$bp_params = (array) $_POST['bp_params' ];

	// Check params
	if ( empty( $bp_params['item_id'] ) ) {
		bp_attachments_json_response( false, $is_html4 );
	}

	// Capability check
	if ( ! is_user_logged_in() || ( (int) bp_loggedin_user_id() !== (int) $bp_params['item_id'] && ! bp_current_user_can( 'bp_moderate' ) ) ) {
		bp_attachments_json_response( false, $is_html4 );
	}

	$bd_file = buddydrive_upload_item( $_FILES, $bp_params['item_id'] );

	// Error while trying to upload the file
	if ( ! empty( $bd_file['error'] ) ) {
		bp_attachments_json_response( false, $is_html4, array(
			'type'    => 'upload_error',
			'message' => $bd_file['error'],
		) );
	}

	$name_parts = pathinfo( $bd_file['file'] );
	$url        = $bd_file['url'];
	$mime       = $bd_file['type'];
	$file       = $bd_file['file'];
	$title      = $name_parts['filename'];

	/**
	 * @todo check it has no impact on BuddyDrive Editor
	 */
	$privacy    = buddydrive_get_default_privacy();
	$groups     = array();

	$parent_folder_id = 0;
	if ( ! empty( $bp_params['parent_folder_id'] ) ) {
		$parent_folder_id = (int) $bp_params['parent_folder_id'];
	}

	if ( ! empty( $bp_params['privacy'] ) ) {
		$privacy = $bp_params['privacy'];

		if ( ! empty( $bp_params['privacy_item_id'] ) && 'groups' === $privacy ) {
			$groups = (array) $bp_params['privacy_item_id'];
		}
	}

	$buddyfile_id = buddydrive_add_item( array(
		'user_id'          => $bp_params['item_id'],
		'type'             => buddydrive_get_file_post_type(),
		'guid'             => $url,
		'title'            => $title,
		'mime_type'        => $mime,
		'privacy'          => $privacy,
		'groups'           => $groups,
		'parent_folder_id' => $parent_folder_id,
	) );

	if ( empty( $buddyfile_id ) ) {
		bp_attachments_json_response( false, $is_html4, array(
			'type'    => 'upload_error',
			'message' => __( 'Error while creating the file, sorry.', 'buddydrive' ),
		) );
	} else {
		// Try to create a thumbnail if it's an image and a public file
		if ( 'public' === $privacy ) {
			buddydrive_set_thumbnail( $buddyfile_id, $bd_file );
		}
	}

	$response = buddydrive_prepare_for_js( $buddyfile_id );
	$response['buddydrive_id'] = $response['id'];
	$response['url']           = $response['link'];
	$response['uploaded']      = true;

	unset( $response['id'] );

	// Finally return file to the editor
	bp_attachments_json_response( true, $is_html4, $response );
}
add_action( 'wp_ajax_buddydrive_upload', 'buddydrive_upload_file' );

/**
 * Fetch BuddyDrive Items for the current scope
 *
 * @since  2.0.0
 *
 * @return string JSON reply
 */
function buddydrive_fetch_items() {
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( array(
			'message' => __( 'The action was not sent correctly.', 'buddydrive' ),
		) );
	}

	$not_allowed = array( 'message' => __( 'You are not allowed to perform this action.', 'buddydrive' ) );

	// Nonce check
	if ( empty( $_POST['buddydrive_nonce'] ) || ! wp_verify_nonce( $_POST['buddydrive_nonce'], 'buddydrive_fetch_items' ) ) {
		wp_send_json_error( $not_allowed );
	}

	$defaults = array(
		'paged'            => 1,
		'per_page'         => 20,
		'type'             => array( buddydrive_get_folder_post_type(), buddydrive_get_file_post_type() ),
		'orderby'          => 'modified',
		'order'            => 'DESC',
		'buddydrive_scope' => buddydrive_get_current_scope(),
	);

	if ( bp_is_user() ) {
		$defaults['user_id']  = bp_displayed_user_id();
	} elseif ( bp_is_group() ) {
		$defaults['group_id'] = bp_get_current_group_id();
	}

	// Ajax ~ is_admin()
	if ( 'admin' === $defaults['buddydrive_scope'] && is_buddypress() ) {
		$defaults['buddydrive_scope'] = 'public';
	}

	$query_args = bp_parse_args( $_POST, $defaults, 'buddydrive_fetch_items' );

	if ( 'title' === $query_args['orderby'] ) {
		$query_args['order'] = 'ASC';
	}

	$buddydrive_items = new BuddyDrive_Item;
	$buddydrive_items->get( $query_args );

	$items = array_map( 'buddydrive_prepare_for_js', array_filter( (array) $buddydrive_items->query->posts ) );

	// Pagination
	$metas       = array( 'paged' => (int) $query_args['paged'], 'has_more_items' => false );
	$found_items = (int) $buddydrive_items->query->found_posts;

	if ( 0 < $found_items ) {
		$metas['has_more_items'] = (bool) floor( ( $found_items - 1 ) / ( 20 * (int) $query_args['paged'] ) );
	}

	// Make sure to fetch additional data for the parent folder if needed
	if ( ! empty( $query_args['buddydrive_parent'] ) ) {
		$post_parent = buddydrive_get_buddyfile(  $query_args['buddydrive_parent'], buddydrive_get_folder_post_type() );
		$metas['post_parent_title'] = $post_parent->title;
		$metas['post_parent_owner'] = $post_parent->user_id;

		$labels = buddydrive_get_sharing_options();
		$metas['post_parent_infos'] = sprintf(
			_x( 'Files added to this folder will have the following privacy: %s', 'Parent folder privacy infos', 'buddydrive' ),
			esc_html( $labels[ $post_parent->check_for ] )
		);

		if ( 'buddydrive_groups' === $post_parent->post_status ) {
			$groups   = wp_parse_id_list( $post_parent->group );
			$group_id = reset( $groups );

			if ( bp_is_group() && (int) bp_get_current_group_id() === (int) $group_id ) {
				$object = groups_get_current_group();
			} elseif ( bp_is_active( 'groups' ) ) {
				$object = groups_get_group( array( 'group_id' => $group_id ) );
			}

			if ( ! empty( $object->id ) ) {
				$object->buddydrive_type = 'group';
				$group = buddydrive_prepare_bpobject_js( $object );
				$metas['post_parent_infos'] = sprintf(
					_x( 'Files added to this folder will be accessible to members of this group: %s', 'Parent folder privacy infos', 'buddydrive' ),
					sprintf(
						'<a href="%1$s" title="%2$s">%3$s<a>',
						esc_url_raw( $group['link'] ),
						esc_attr( $group['name'] ),
						$group['avatar']
					)
				);
			}
		} elseif ( 'buddydrive_members' === $post_parent->post_status ) {
			$members = wp_parse_id_list( $post_parent->members );

			if ( ! empty( $members ) ) {
				$objects = buddydrive_list_objects( array( 'buddydrive_type' => 'members', 'include' => $members ) );

				$avatars = array();
				foreach ( $objects as $object ) {
					$object = buddydrive_prepare_bpobject_js( $object );

					if ( empty( $object['name'] ) ) {
						continue;
					}

					$avatars[] = sprintf(
						'<a href="%1$s" title="%2$s">%3$s<a>',
						esc_url_raw( $object['link'] ),
						esc_attr( $object['name'] ),
						$object['avatar']
					);
				}

				if ( ! empty( $avatars ) ) {
					$metas['post_parent_infos'] = sprintf(
						_x( 'Files added to this folder will be accessible to the following members: %s', 'Parent folder privacy infos', 'buddydrive' ),
						join( ' ', $avatars )
					);
				}
			}
		}

		// Capability check for the folder!
		$can_list = buddydrive_check_download( $post_parent, bp_loggedin_user_id() );

		if ( is_wp_error( $can_list ) && 'empty_password' !== $can_list->get_error_code() ) {
			wp_send_json_error( $not_allowed );
		}
	}

	if ( empty( $items ) ) {
		$metas['no_items_found'] = __( 'No items found.', 'buddydrive' );

	// Capability check for the edit action
	} elseif ( ! empty( $query_args['id'] ) && ! empty( $query_args['is_edit' ] ) ) {
		$item = reset( $items );

		if ( empty( $item['can_edit'] ) ) {
			wp_send_json_error( $not_allowed );
		}
	}

	wp_send_json_success( array( 'items' => $items, 'metas' => $metas ) );
}
add_action( 'wp_ajax_buddydrive_fetch_items',        'buddydrive_fetch_items' );
add_action( 'wp_ajax_nopriv_buddydrive_fetch_items', 'buddydrive_fetch_items' );

/**
 * Edit a BuddyDrive Item
 *
 * @since  2.0.0
 *
 * @return string JSON reply
 */
function buddydrive_item_update() {
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( array(
			'message' => __( 'The action was not sent correctly.', 'buddydrive' ),
		) );
	}

	// Nonce check
	if ( empty( $_POST['buddydrive_nonce'] ) || ! wp_verify_nonce( $_POST['buddydrive_nonce'], 'buddydrive_update_item' ) ) {
		wp_send_json_error( array(
			'message' => __( 'You are not allowed to perform this action.', 'buddydrive' ),
		) );
	}

	$r = wp_parse_args( $_POST, array(
		'id' => 0,
	) );

	$error = array( 'message' => __( 'Unknown item.', 'buddydrive' ) );

	if ( empty( $r['id'] ) ) {
		wp_send_json_error( $error );
	}

	// Validate file
	$item = buddydrive_get_buddyfile( (int) $r['id'], array( buddydrive_get_folder_post_type(), buddydrive_get_file_post_type() ) );

	if ( ! $item->ID ) {
		wp_send_json_error( $error );
	}

	if ( 'folder' ===  $r['privacy'] ) {
		// Set default privacy
		$r['privacy'] = buddydrive_get_default_privacy();

		// One folder & one only
		if ( ! empty( $r['folder'] ) )  {
			$r['parent_folder_id'] = reset( wp_parse_id_list( $r['folder'] ) );
		}
	}

	if ( ! buddydrive_update_item( $r, $item ) ) {
		wp_send_json_error( array(
			'message' => __( 'Something went wrong. Please try again later.', 'buddydrive' ),
		) );
	}

	wp_send_json_success( array(
		'message' => __( 'Item updated successfully.', 'buddydrive' ),
	) );
}
add_action( 'wp_ajax_buddydrive_item_update', 'buddydrive_item_update' );

/**
 * Fetch objetcs for advanced privacy options
 *
 * @since  2.0.0
 *
 * @return string JSON reply.
 */
function buddydrive_get_bpobjects() {
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( array(
			'message' => __( 'The action was not sent correctly.', 'buddydrive' ),
		) );
	}

	// Nonce check
	if ( empty( $_POST['buddydrive_nonce'] ) || ! wp_verify_nonce( $_POST['buddydrive_nonce'], 'buddydrive_fetch_objects' ) ) {
		wp_send_json_error( array(
			'message' => __( 'You are not allowed to perform this action.', 'buddydrive' ),
		) );
	}

	$r = wp_parse_args( $_POST, array(
		'include'         => array(),
		'user_id'         => bp_loggedin_user_id(),
		'search_terms'    => '',
		'buddydrive_type' => ''
	) );

	if ( bp_is_group() ) {
		$r['buddydrive_scope'] = 'groups';
		$r['group_id']         = bp_get_current_group_id();
	}

	$objects = buddydrive_list_objects( $r );

	if ( empty( $objects ) ) {
		wp_send_json_error( array( 'error' => __( 'No items were found.', 'buddydrive' ) ) );
	} else {
		wp_send_json_success( array_map( 'buddydrive_prepare_bpobject_js', $objects ) );
	}
}
add_action( 'wp_ajax_buddydrive_get_bpobjects', 'buddydrive_get_bpobjects' );

/**
 * Bulk Edit items
 *
 * @since  2.0.0
 *
 * @return string JSON reply.
 */
function buddydrive_bulk_edit_items() {
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( array(
			'message' => __( 'The action was not sent correctly.', 'buddydrive' ),
		) );
	}

	// Nonce check
	if ( empty( $_POST['buddydrive_nonce'] ) || ! wp_verify_nonce( $_POST['buddydrive_nonce'], 'buddydrive_bulk_edit' ) ) {
		wp_send_json_error( array(
			'message' => __( 'You are not allowed to perform this action.', 'buddydrive' ),
		) );
	}

	$r = wp_parse_args( $_POST, array() );

	$error = array( 'error' => __( 'An unexpected error occured.', 'buddydrive' ) );

	if ( empty( $r['type'] ) ) {
		wp_send_json_error( $error );
	}

	if ( empty( $r['items'] ) ) {
		wp_send_json_error( array( 'error' => __( 'No items were sent.', 'buddydrive' ) ) );
	}

	$bulk_edited = array();

	if ( 'delete' === $r['type'] ) {
		$bulk_edited = buddydrive_delete_item( array( 'ids' => $r['items'], 'user_id' => false ) );
	} elseif ( 'remove' === $r['type'] ) {
		$bulk_edited = buddydrive_items_remove_parent( $r['items'] );
	} elseif ( 'group_remove' === $r['type'] ) {
		$bulk_edited = buddydrive_items_remove_from_group( $r['items'] );
	}

	if ( empty( $bulk_edited ) ) {
		wp_send_json_error( $error );
	}

	wp_send_json_success( $bulk_edited );
}
add_action( 'wp_ajax_buddydrive_bulk_edit_items', 'buddydrive_bulk_edit_items' );

/**
 * Creates a new BuddyDrive folder
 *
 * @since  2.0.0
 *
 * @return string JSON reply.
 */
function buddydrive_add_folder() {
	$error = array( 'error' => __( 'An unexpected error occured.', 'buddydrive' ) );

	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( $error );
	}

	$not_allowed = array( 'message' => __( 'You are not allowed to perform this action.', 'buddydrive' ) );

	// Nonce check
	if ( empty( $_POST['buddydrive_nonce'] ) || ! wp_verify_nonce( $_POST['buddydrive_nonce'], 'buddydrive_new_folder' ) ) {
		wp_send_json_error( $not_allowed );
	}

	// Capability check
	if ( ! buddydrive_current_user_can( 'buddydrive_upload' ) ) {
		wp_send_json_error( $not_allowed );
	}

	$r = wp_parse_args( $_POST, array(
		'privacy' => buddydrive_get_default_privacy(),
	) );

	if ( empty( $r['title'] ) ) {
		wp_send_json_error( $error );
	}

	$args = array(
		'type'    => buddydrive_get_folder_post_type(),
		'title'   => esc_html( $r['title'] ),
		'privacy' => buddydrive_get_privacy( 'buddydrive_' . $r['privacy'] ),
	);

	// Bail if it's an unknown status.
	if ( ! $args['privacy'] ) {
		wp_send_json_error( $error );
	}

	// Allow admins to create folders in other users BuddyDrive
	if ( ! empty( $r['user_id'] ) && bp_current_user_can( 'bp_moderate' ) ) {
		$args['user_id'] = (int) $r['user_id'];
	}

	if ( bp_is_group() ) {
		$args = array_merge( $args,
			array(
				'privacy' => buddydrive_get_privacy( 'buddydrive_groups' ),
				'groups'  => array( bp_get_current_group_id() ),
		) );
	}

	$folder_id = buddydrive_add_item( $args );
	$folder    = get_post( $folder_id );

	if ( empty( $folder->ID ) ) {
		wp_send_json_error( $error );
	} else {
		$folder->new_folder = true;
	}

	wp_send_json_success( buddydrive_prepare_for_js( $folder ) );
}
add_action( 'wp_ajax_buddydrive_add_folder', 'buddydrive_add_folder');

/**
 * Get the current user statistics
 *
 * @since  2.0.0
 *
 * @return string JSON reply.
 */
function buddydrive_get_stats() {
	$error = array( 'error' => __( 'An unexpected error occured.', 'buddydrive' ) );

	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( $error );
	}

	$not_allowed = array( 'message' => __( 'You are not allowed to perform this action.', 'buddydrive' ) );

	// Nonce check
	if ( empty( $_POST['buddydrive_nonce'] ) || ! wp_verify_nonce( $_POST['buddydrive_nonce'], 'buddydrive_user_stats' ) ) {
		wp_send_json_error( $not_allowed );
	}

	$user_id    = bp_loggedin_user_id();
	$space_used = buddydrive_get_user_space_data( $user_id );
	$total      = 0;

	$response = array(
		'id'     => $user_id,
		'used'   => number_format_i18n( $space_used['percent'], 2 ),
		'detail' => array(),
		'total'  => '',
	);

	$stats = BuddyDrive_Item::get_user_stats( $user_id );

	if ( ! empty( $stats) ) {
		$buddydrive_status = buddydrive_get_stati();
		foreach ( $stats as $stat ) {
			if ( ! isset( $buddydrive_status[ $stat->post_status ]['label'] ) ) {
				continue;
			}

			$response['detail'][] = array(
				'type'  => $buddydrive_status[ $stat->post_status ]['buddydrive_privacy'],
				'label' => $buddydrive_status[ $stat->post_status ]['label'],
				'stat'  => sprintf( _n( '%d file', '%d files', $stat->num, 'buddydrive' ), number_format_i18n( $stat->num ) ),
			);

			$total += $stat->num;
		}
	}

	if ( ! empty( $total ) ) {
		$response['total'] = sprintf( _n( '(%d file)', '(%d files)', $total, 'buddydrive' ), number_format_i18n( $total ) );
	}

	wp_send_json_success( $response );
}
add_action( 'wp_ajax_buddydrive_get_stats', 'buddydrive_get_stats' );
