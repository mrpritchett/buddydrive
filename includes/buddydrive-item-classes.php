<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* The "CRUD" BuddyDrive Items class
*
* @package BuddyDrive
* @since 1.0
*
*/
class BuddyDrive_Item {

	public $user_id;
	public $id;
	public $type;
	public $parent_folder_id;
	public $title;
	public $content;
	public $mime_type;
	public $guid;
	public $metas;


	public function __construct( $id = 0 ){
		if ( ! empty( $id ) ) {
			$this->id = $id;
			$this->populate();
		}
	}

	/**
	 * request an item id
	 *
	 * @uses WP_Query
	 */
	public function populate() {

		$query_args = array(
			'post_status'	 => 'publish',
			'p' => intval( $this->id )
		);

		$this->query = new WP_Query( $query_args );

	}

	/**
	 * Saves or updates the BuddyDrive items
	 *
	 * @uses wp_update_post() to update an item if it exists
	 * @uses update_post_meta() to update/create privacy options
	 * @uses delete_post_meta() to eventually remove privacy options
	 * @uses wp_insert_post() to add a new item
	 * @return int $result the id of the item created
	 */
	public function save() {
		$this->id               = apply_filters_ref_array( 'buddydrive_item_id_before_save',         array( $this->id,                &$this ) );
		$this->type             = apply_filters_ref_array( 'buddydrive_item_user_id_before_save',    array( $this->type,              &$this ) );
		$this->user_id          = apply_filters_ref_array( 'buddydrive_item_user_id_before_save',    array( $this->user_id,           &$this ) );
		$this->parent_folder_id = apply_filters_ref_array( 'buddydrive_item_parent_id_before_save',  array( $this->parent_folder_id,  &$this ) );
		$this->title            = apply_filters_ref_array( 'buddydrive_item_title_before_save',      array( $this->title,             &$this ) );
		$this->content          = apply_filters_ref_array( 'bbuddydrive_item_content_before_save',   array( $this->content,           &$this ) );
		$this->mime_type        = apply_filters_ref_array( 'bbuddydrive_item_mime_type_before_save', array( $this->mime_type,         &$this ) );
		$this->guid             = apply_filters_ref_array( 'bbuddydrive_item_guid_before_save',      array( $this->guid,              &$this ) );
		$this->metas            = apply_filters_ref_array( 'buddydrive_item_metas_before_save',      array( $this->metas,             &$this ) );


		// Use this, not the filters above
		do_action_ref_array( 'buddydrive_item_before_save', array( &$this ) );

		if ( ! $this->title || ! $this->type )
			return false;

		// If we have an existing ID, update the post, otherwise insert it.
		if ( $this->id ) {

			$wp_update_post_args = array(
				'ID'		     => $this->id,
				'post_author'	 => $this->user_id,
				'post_title'	 => $this->title,
				'post_content'	 => $this->content,
				'post_type'		 => $this->type,
				'post_parent'    => $this->parent_folder_id,
				'post_mime_type' => $this->mime_type,
				'guid'           => $this->guid,
				'post_status'	 => 'publish'
			);

			if ( ! empty( $this->metas->password ) )
				$wp_update_post_args['post_password'] = $this->metas->password;

			$result = wp_update_post( $wp_update_post_args );

			if ( $result ) {

				if ( ! empty( $this->metas->privacy ) )
					update_post_meta( $this->id, '_buddydrive_sharing_option', $this->metas->privacy );
				else
					delete_post_meta( $this->id, '_buddydrive_sharing_option' );

				if ( ! empty( $this->metas->groups ) )
					update_post_meta( $this->id, '_buddydrive_sharing_groups', $this->metas->groups );
				else
					delete_post_meta( $this->id, '_buddydrive_sharing_groups' );

				if ( ! empty( $this->metas->buddydrive_meta ) && is_array( $this->metas->buddydrive_meta ) ) {
					foreach( $this->metas->buddydrive_meta as $buddydrive_meta ) {
						if( empty( $buddydrive_meta->cvalue ) ) {
							delete_post_meta( $this->id, sanitize_key( $buddydrive_meta->cname ) );
						} else {
							$meta_value = is_array( $buddydrive_meta->cvalue ) ? array_map( 'esc_html', $buddydrive_meta->cvalue ) : wp_kses( $buddydrive_meta->cvalue, array() );
							update_post_meta( $this->id, sanitize_key( $buddydrive_meta->cname ), $meta_value );
						}
					}
				}

				do_action_ref_array( 'buddydrive_item_after_update', array( &$this ) );

			}

		} else {

			$wp_insert_post_args = array(
				'post_author'	 => $this->user_id,
				'post_title'	 => $this->title,
				'post_content'	 => $this->content,
				'post_type'		 => $this->type,
				'post_parent'    => $this->parent_folder_id,
				'post_mime_type' => $this->mime_type,
				'guid'           => $this->guid,
				'post_status'	 => 'publish'
			);

			if ( ! empty( $this->metas->password ) )
				$wp_insert_post_args['post_password'] = $this->metas->password;

			$result = wp_insert_post( $wp_insert_post_args );

			if ( $result ) {

				$this->id = $result;

				if ( ! empty( $this->metas->privacy ) )
					update_post_meta( $this->id, '_buddydrive_sharing_option', $this->metas->privacy );

				if ( ! empty( $this->metas->groups ) )
					update_post_meta( $this->id, '_buddydrive_sharing_groups', $this->metas->groups );

				if ( ! empty( $this->metas->buddydrive_meta ) && is_array( $this->metas->buddydrive_meta ) ) {

					foreach( $this->metas->buddydrive_meta as $buddydrive_meta ) {
						$meta_value = is_array( $buddydrive_meta->cvalue ) ? array_map( 'esc_html', $buddydrive_meta->cvalue ) : wp_kses( $buddydrive_meta->cvalue, array() );
						update_post_meta( $this->id, sanitize_key( $buddydrive_meta->cname ), $meta_value );
					}
				}

				do_action_ref_array( 'buddydrive_item_after_insert', array( &$this ) );

			}


		}

		do_action_ref_array( 'buddydrive_item_after_save', array( &$this ) );

		return $result;
	}


	/**
	 * The selection query
	 *
	 * @param array $args arguments to customize the query
	 * @uses wp_parse_args() to merge args with defaults one
	 * @uses bp_displayed_user_id() to get the displayed user id
	 * @uses bp_is_my_profile() to check if we're on current user profile
	 * @uses bp_is_active() to check for groups and friends component
	 * @uses friends_check_friendship() to check if current user is friend with item owner
	 * @uses friends_get_friend_user_ids() to get the friends of current user
	 * @uses paginate_links()
	 * @uses add_query_arg()
	 */
	public function get( $args ) {

		// Only run the query once
		if ( empty( $this->query ) ) {
			$defaults = array(
				'id'                => false,
				'name'              => false,
				'group_id'          => false,
				'user_id'           => false,
				'per_page'          => 10,
				'paged'             => 1,
				'type'              => false,
				'buddydrive_scope'  => false,
				'search'            => false,
				'buddydrive_parent' => 0,
				'exclude'           => false,
				'orderby'           => 'title',
				'order'             => 'ASC'
			);

			$r = wp_parse_args( $args, $defaults );
			extract( $r );


			$paged = ! empty( $_POST['page'] ) ? intval( $_POST['page'] ) : $r['paged'];

			if ( ! empty( $r['id'] ) ){
				$query_args = array(
					'post_status'    => 'publish',
					'post_type'      => $r['type'],
					'p'              => $r['id'],
					'posts_per_page' => $r['per_page'],
					'paged'          => $paged,
				);

			} else if ( ! empty( $r['name'] ) && ! empty( $r['type'] ) ) {

				$query_args = array(
					'post_status'    => 'publish',
					'post_type'      => $r['type'],
					'name'           => $r['name'],
					'posts_per_page' => $r['per_page'],
					'paged'          => $paged,
				);

			} else {

				$query_args = array(
					'post_status'    => 'publish',
					'post_type'      => $r['type'],
					'post_parent'    => $r['buddydrive_parent'],
					'posts_per_page' => $r['per_page'],
					'paged'          => $paged,
					'orderby'        => $r['orderby'],
					'order'          => $r['order'],
					'meta_query'     => array()
				);

				switch ( $r['buddydrive_scope'] ) {

					case 'files' :
						if ( ! empty( $r['user_id'] ) && (int) $r['user_id'] === (int) bp_displayed_user_id() ) {
							$query_args['author'] = $r['user_id'];
						}

						if ( ! bp_is_my_profile() && ! bp_current_user_can( 'bp_moderate' ) ) {
							$privacy = array( 'private' );

							if ( bp_is_active( 'friends' ) && ! friends_check_friendship( $r['user_id'], bp_loggedin_user_id() ) ) {
								$privacy[] = 'friends';
							}

							$query_args['meta_query'][] = array(
								'key'     => '_buddydrive_sharing_option',
								'value'   => $privacy,
								'compare' => 'NOT IN'
							);
						}

						break;

					case 'friends' :
						if ( bp_is_active( 'friends' ) ) {
							$ids = friends_get_friend_user_ids( bp_loggedin_user_id() );

							if ( ! empty( $ids ) ) {
								$query_args['author'] = implode( ',', $ids );

								$query_args['meta_query'][] = array(
									'key'     => '_buddydrive_sharing_option',
									'value'   => 'friends',
									'compare' => '='
								);
							} else {
								// we need to use a dummy query to avoid listing all files !
								$query_args['meta_query'][] = array(
									'key'     => '_buddydrive_sharing_option',
									'value'   => 'dummyvalue',
									'compare' => '='
								);
							}

						}
						break;

					case 'groups' :
						if ( bp_is_active( 'groups' ) && ! empty( $r['group_id'] ) && empty( $r['buddydrive_parent'] ) ) {
							$query_args['meta_query'][] = array(
								'key'     => '_buddydrive_sharing_groups',
								'value'   => $r['group_id'],
								'compare' => 'IN' // Allows $r['group_id'] to be an array
							);
						}
						break;

					case 'admin' :
						if ( ! empty( $r['user_id'] ) ) {
							$query_args['author'] = $r['user_id'];
						}

						if ( bp_is_active( 'groups' ) && ! empty( $r['group_id'] ) && empty( $r['buddydrive_parent'] ) ) {
							$query_args['meta_query'][] = array(
								'key'     => '_buddydrive_sharing_groups',
								'value'   => $r['group_id'],
								'compare' => 'IN' // Allows $group_id to be an array
							);
						}
						// Search is only possible for Super Admin, as searching makes it difficult to garanty privacy
						if ( ! empty( $r['search'] ) ) {
							$query_args['s'] = $r['search'];
						}
						break;
				}

			}

			if ( ! empty( $r['exclude'] ) ) {
				if ( ! is_array( $r['exclude'] ) ) {
					$r['exclude'] = explode( ',', $r['exclude'] );
				}

				$query_args['post__not_in'] = $r['exclude'];
			}


			// Run the query, and store as an object property, so we can access from
			// other methods
			$this->query = new WP_Query( $query_args );

			// Let's also set up some pagination
			$this->pag_links = paginate_links( array(
				'base'      => add_query_arg( 'items_page', '%#%' ),
				'format'    => '',
				'total'     => ceil( (int) $this->query->found_posts / (int) $this->query->query_vars['posts_per_page'] ),
				'current'   => (int) $paged,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'mid_size'  => 1
			) );
		}

	}


	/**
	 * do we have items to show ?
	 */
	public function have_posts() {
		return $this->query->have_posts();
	}


	/**
	 * Part of our BuddyDrive loop
	 */
	public function the_post() {
		return $this->query->the_post();
	}


	/**
	 * list BuddyDrive Files attached to a folder if any
	 *
	 * @param  int $folder_id the BuddyDrive folder id
	 * @global object $wpdb
	 * @return array the list of file ids
	 */
	public function get_buddydrive_folder_children( $folder_id = false ) {
		global $wpdb;

		if ( empty( $folder_id ) )
			return false;

		$folder_id = intval( $folder_id );

		$buddydrive_children = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->base_prefix}posts WHERE post_parent = %d", $folder_id ) );
		return $buddydrive_children;
	}


	/**
	 * Updates the privacy of files attached to a folder
	 *
	 * @param int $folder_id the BuddyFolder id
	 * @param object $metas  the privacy options of the folder
	 * @uses BuddyDrive_Items::get_buddydrive_folder_children() to get the files attached to the folder
	 * @uses update_post_meta() to add privacy options
	 */
	public function update_children( $folder_id = false, $metas = false ) {
		if ( empty( $folder_id ) || empty( $metas ) )
			return false;

		$children = $this->get_buddydrive_folder_children( $folder_id );

		if ( ! empty( $children ) ) {
			foreach( $children as $child ){

				if ( ! empty( $metas->privacy ) )
					update_post_meta( $child, '_buddydrive_sharing_option', $metas->privacy );

				if ( ! empty( $metas->groups ) )
					update_post_meta( $child, '_buddydrive_sharing_groups', $metas->groups );
			}
		}
	}


	/**
	 * Retrieves some items datas for an array of BuddyDrive items
	 *
	 * @param  array  $ids the list of ids
	 * @global object $wpdb
	 * @return object the post_title, ID and post_type of the wanted ids
	 */
	public function get_buddydrive_by_ids( $ids = array() ) {
		global $wpdb;

		$buddydrive_items = false;

		if ( ! empty( $ids ) ) {
			$ids = wp_parse_id_list( $ids );

			$in = '("' . implode( '","', $ids ) . '")';
			$buddydrive_items = $wpdb->get_results( "SELECT ID, post_title, post_type FROM {$wpdb->base_prefix}posts WHERE ID IN {$in}");

		}

		return $buddydrive_items;
	}


	/**
	 * Deletes a list of items or all the items of a given user
	 *
	 * @param  array $ids array of BuddyDrive Item ids
	 * @param  int $user_id the id of a user
	 * @global object $wpdb
	 * @uses get_user_meta() to get the quota of the user id
	 * @uses buddydrive_get_buddyfile() to get the BuddyDrive item
	 * @uses wp_delete_post() to delete the BuddyDrive post type
	 * @uses update_user_meta() to eventually update user's quota
	 * @return int number of deleted items
	 */
	public function delete( $ids = false, $user_id = false ) {
		global $wpdb;

		$buddydrive_ids = array();
		$spaces         = array();
		$new_space      = false;
		$ids            = array_filter( wp_parse_id_list( $ids ) );

		if ( ! empty( $ids ) ) {
			//we need to get the children
			$in = '("' . implode( '","', $ids ) . '")';
			$buddydrive_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->base_prefix}posts WHERE post_parent IN {$in}");

			$buddydrive_ids = array_merge( $buddydrive_ids, $ids );

		} elseif ( ! empty( $user_id ) && empty( $ids ) ) {
			// in case a user is deleted
			$buddydrive_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->base_prefix}posts WHERE post_author = %d AND post_type IN (%s, %s)", $user_id, buddydrive_get_folder_post_type(), buddydrive_get_file_post_type() ) );

			$new_user = (int) apply_filters( 'buddydrive_set_owner_on_user_deleted', 0 );

			// The new user must have the power to post in any group
			if ( ! empty( $new_user ) && user_can( $new_user, 'bp_moderate' ) && ! empty( $buddydrive_ids ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->base_prefix}posts SET post_author = %d WHERE post_author = %d AND post_type IN (%s, %s)", $new_user, $user_id, buddydrive_get_folder_post_type(), buddydrive_get_file_post_type() ) );

				foreach ( $buddydrive_ids as $post_id ) {
					clean_post_cache( $post_id );
				}
			}
		}

		if ( empty( $buddydrive_ids ) ) {
			return false;
		}

		if ( empty( $new_user ) ) {
			foreach ( $buddydrive_ids as $id ){
				$buddyfile = buddydrive_get_buddyfile( $id );

				if ( ! empty( $buddyfile ) ) {

					if ( ! empty( $buddyfile->path ) && file_exists( $buddyfile->path ) ) {

						if ( ! isset( $spaces[ $buddyfile->user_id ] ) ) {
							$spaces[ $buddyfile->user_id ] = filesize( $buddyfile->path );
						} else {
							$spaces[ $buddyfile->user_id ] += filesize( $buddyfile->path );
						}

						unlink( $buddyfile->path );
					}

					// Delete the thumbnail
					if ( 'public' === $buddyfile->check_for ) {
						buddydrive_delete_thumbnail( $buddyfile->ID );
					}
				}

				wp_delete_post( $id, true );
			}
		}

		if ( ! empty( $spaces ) ) {

			foreach ( $spaces as $u_id => $space ) {
				$user_total_space = get_user_meta( $u_id, '_buddydrive_total_space', true );
				$user_total_space = intval( $user_total_space );

				if ( $space < $user_total_space ) {
					buddydrive_update_user_space( $u_id, -1 * absint( $space ) );
				} else {
					delete_user_meta( $u_id, '_buddydrive_total_space' );
				}
			}
		}

		return count( $buddydrive_ids );
	}


	/**
	 * Removes a BuddyDrive item from a group
	 *
	 * @param  int $item_id the BuddyDrive item if
	 * @param  string  $new_status the privacy option to fallback
	 * @uses delete_post_meta() to delete group meta
	 * @uses update_post_meta() to update the privacy option
	 * @uses BuddyDrive_Item::get_buddydrive_folder_children() to get the file attached to a folder
	 * @return int 1
	 */
	public function remove_from_group( $item_id = false, $new_status = 'private' ) {
		if ( empty( $item_id ) )
			return false;

		delete_post_meta( $item_id, '_buddydrive_sharing_groups' );
		update_post_meta( $item_id, '_buddydrive_sharing_option', $new_status );

		// is it a folder ?
		$children = $this->get_buddydrive_folder_children( $item_id );

		if ( ! empty( $children ) && is_array( $children ) && count( $children ) >= 1 ) {
			foreach( $children as $child ) {
				delete_post_meta( $child, '_buddydrive_sharing_groups' );
				update_post_meta( $child, '_buddydrive_sharing_option', $new_status );
			}
		}

		return 1;
	}


	/**
	 * Handles the group deletion and restore a privacy to a BuddyDrive item
	 * @param  integer $group_id
	 * @param  string  $new_status
	 * @global $wpdb
	 * @uses delete_post_meta() to delete group meta
	 * @uses update_post_meta() to update the privacy option
	 * @return boolean success or not
	 */
	public function group_remove_items( $group_id = 0, $new_status = 'private' ) {
		global $wpdb;

		if ( empty( $group_id ) )
			return false;

		$buddydrive_in_group = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_buddydrive_sharing_groups' AND meta_value = %d", $group_id ) );

		foreach ( $buddydrive_in_group as $item ) {
			delete_post_meta( $item, '_buddydrive_sharing_groups' );
			update_post_meta( $item, '_buddydrive_sharing_option', $new_status );
		}

		return true;
	}

}


/**
* The Uploader class
*
* @package BuddyDrive
* @since 1.0
*
*/
class BuddyDrive_Uploader {


	public function __construct() {
		$this->setup_actions();
		$this->display();
	}

	/**
	 * filters wp_footer to enqueue the needed scripts
	 */
	public function setup_actions() {
		add_action( 'wp_footer', array( $this, 'enqueue_scripts' ), 1 );
	}

	/**
	 * enqueue the needed scripts
	 *
	 * @uses wp_enqueue_script()
	 * @uses wp_localize_script() to translate javascript messages
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'buddydrive', buddydrive_get_includes_url() .'js/buddydrive.js', array( 'plupload-all', 'jquery' ), buddydrive_get_version(), true );

		$pluploadmessages = array(
				'queue_limit_exceeded'      => __( 'You have attempted to queue too many files.', 'buddydrive' ),
				'file_exceeds_size_limit'   => __( '%s exceeds the maximum upload size for this site.', 'buddydrive' ),
				'zero_byte_file'            => __( 'This file is empty. Please try another.', 'buddydrive' ),
				'invalid_filetype'          => __( 'This file type is not allowed. Please try another.', 'buddydrive' ),
				'not_an_image'              => __( 'This file is not an image. Please try another.', 'buddydrive' ),
				'image_memory_exceeded'     => __( 'Memory exceeded. Please try another smaller file.', 'buddydrive' ),
				'image_dimensions_exceeded' => __( 'This is larger than the maximum size. Please try another.', 'buddydrive' ),
				'default_error'             => __( 'An error occurred in the upload. Please try again later.', 'buddydrive' ),
				'missing_upload_url'        => __( 'There was a configuration error. Please contact the server administrator.', 'buddydrive' ),
				'upload_limit_exceeded'     => __( 'You may only upload 1 file.', 'buddydrive' ),
				'http_error'                => __( 'HTTP error.', 'buddydrive' ),
				'upload_failed'             => __( 'Upload failed.', 'buddydrive' ),
				'big_upload_failed'         => __( 'Please try uploading this file with the %1$sbrowser uploader%2$s.', 'buddydrive' ),
				'big_upload_queued'         => __( '%s exceeds the maximum upload size for the multi-file uploader when used in your browser.', 'buddydrive' ),
				'io_error'                  => __( 'IO error.', 'buddydrive' ),
				'security_error'            => __( 'Security error.', 'buddydrive' ),
				'file_cancelled'            => __( 'File canceled.', 'buddydrive' ),
				'upload_stopped'            => __( 'Upload stopped.', 'buddydrive' ),
				'dismiss'                   => __( 'Dismiss', 'buddydrive' ),
				'crunching'                 => __( 'Crunching&hellip;', 'buddydrive' ),
				'deleted'                   => __( 'moved to the trash.', 'buddydrive' ),
				'error_uploading'           => __( '&#8220;%s&#8221; has failed to upload.', 'buddydrive' )
			);

		// get BuddyDrive specific and merge it with plupload
		$buddydrivel10n = buddydrive_get_js_l10n();
		$pluploadmessages = array_merge( $pluploadmessages, $buddydrivel10n );

		wp_localize_script( 'buddydrive', 'pluploadL10n', $pluploadmessages );
	}


	/**
	 * Finally output the uploader
	 *
	 * @global $type, $tab, $pagenow, $is_IE, $is_opera
	 * @return string the output
	 */
	public function display() {
		global $type, $tab, $pagenow, $is_IE, $is_opera;
		?>
		<form enctype="multipart/form-data" method="post" action="" class="media-upload-form type-form validate standard-form" id="file-form">

			<?php
			if ( ! _device_can_upload() ) {
				echo '<p>' . __( 'The web browser on your device cannot be used to upload files. You may be able to use the <a href="http://wordpress.org/extend/mobile/">native app for your device</a> instead.', 'buddydrive' ) . '</p>';
				return;
			}

			$upload_size_unit = $max_upload_size = buddydrive_max_upload_size( true );
			$sizes = array( 'KB', 'MB', 'GB' );

			for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ ) {
				$upload_size_unit /= 1024;
			}

			if ( $u < 0 ) {
				$upload_size_unit = 0;
				$u = 0;
			} else {
				$upload_size_unit = (int) $upload_size_unit;
			}
			?>

			<div id="media-upload-notice"><?php

				if (isset($errors['upload_notice']) )
					echo $errors['upload_notice'];

			?>
			</div>
			<div id="media-upload-error"><?php

				if ( isset($errors['upload_error'] ) && is_wp_error( $errors['upload_error'] ) )
					echo $errors['upload_error']->get_error_message();

			?>
			</div>

			<div id="buddydrive-first-step" class="buddydrive-step">
				<label for="buddyfile-desc"><?php _e( 'Describe your file', 'buddydrive' );?></label>
				<textarea placeholder="<?php _e( '140 characters to do so', 'buddydrive' );?>" maxlength="140" id="buddyfile-desc"></textarea>
				<p class="buddydrive-action"><a href="#" class="next-step button"><?php _e( 'Next Step', 'buddydrive' );?></a></p>
			</div>

			<?php if ( has_action( 'buddydrive_uploader_custom_fields' ) ) : ?>

				<div id="buddydrive-custom-step-new" class="buddydrive-step hide">

					<?php do_action( 'buddydrive_uploader_custom_fields' ) ;?>
					<p class="buddydrive-action"><a href="#" class="next-step button"><?php _e( 'Next Step', 'buddydrive' );?></a></p>

				</div>

			<?php endif ; ?>

			<div id="buddydrive-second-step" class="buddydrive-step hide">
				<label for="buddydrive-sharing-options"><?php _e( 'Define your sharing options', 'buddydrive' );?></label>

				<?php buddydrive_select_sharing_options()?>

				<div id="buddydrive-sharing-details"></div>
				<input type="hidden" id="buddydrive-sharing-settings" value="private">
				<p class="buddydrive-action"><a href="#" class="next-step button"><?php _e( 'Next Step', 'buddydrive' );?></a></p>
			</div>

			<?php
			if ( is_multisite() && !is_upload_space_available() ) {
				do_action( 'upload_ui_over_quota' );
				return;
			}

			$buddydrive_params = array(
					'action' => 'buddydrive_upload',
					'_wpnonce' => wp_create_nonce( 'buddydrive-form' ),
			);

			$buddydrive_params = apply_filters( 'buddydrive_upload_post_params', $buddydrive_params ); // hook change! old name: 'swfupload_post_params'

			$plupload_init = array(
				'runtimes'            => 'html5,silverlight,flash,html4',
				'browse_button'       => 'plupload-browse-button',
				'container'           => 'plupload-upload-ui',
				'drop_element'        => 'drag-drop-area',
				'file_data_name'      => 'buddyfile-upload',
				'multi_selection'     => false,
				'url'                 => admin_url( 'admin-ajax.php', 'relative' ),
				'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
				'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
				'filters'             => array(
					array(
						'title'         => __( 'Allowed Files' ),
						'extensions'    => '*',
						'max_file_size' => $max_upload_size . 'b',
					)
				),
				'multipart'           => true,
				'urlstream_upload'    => true,
				'multipart_params'    => $buddydrive_params
			);

			$plupload_init = apply_filters( 'buddydrive_plupload_init', $plupload_init );

			?>

			<script type="text/javascript">
			var wpUploaderInit = <?php echo json_encode( $plupload_init ); ?>;
			</script>

			<div id="buddydrive-third-step" class="buddydrive-step hide">
				<label for="plupload-browse-buttons"><?php _e( 'Upload your file!', 'buddydrive' );?></label>

				<div id="plupload-upload-ui" class="hide-if-no-js">

					<div id="drag-drop-area">
						<div class="drag-drop-inside">
							<p class="drag-drop-info"><?php _e( 'Drop your file here', 'buddydrive' ); ?></p>
							<p><?php _ex( 'or', 'Uploader: Drop your file here - or - Select your File', 'buddydrive' ); ?></p>
							<p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="<?php esc_attr_e( 'Select your File', 'buddydrive' ); ?>" class="button" /></p>
						</div>
					</div>

				</div>

				<p><span class="max-upload-size"><?php printf( __( 'Maximum upload file size: %d%s.', 'buddydrive' ), esc_html( $upload_size_unit ), esc_html( $sizes[$u] ) ); ?></span></p>
				<p class="buddydrive-action"><a href="#" class="cancel-step button"><?php _e( 'Cancel', 'buddydrive' );?></a></p>

			</div>

		</form>
		<?php
	}
}

if ( class_exists( 'BP_Attachment' ) ) :
/**
 * The Attachments class
 *
 * @since 1.3.0
 */
class BuddyDrive_Attachment extends BP_Attachment {
	/**
	 * The constuctor
	 *
	 * @since 1.3.0
	 */
	public function __construct() {
		parent::__construct( array(
			'action'               => 'buddydrive_upload',
			'file_input'           => 'buddyfile-upload',
			'base_dir'             => 'buddydrive',
			'upload_error_strings' => buddydrive_get_upload_error_strings(),
			'allowed_mime_types'   => buddydrive_get_allowed_upload_exts(),
		) );
	}

	/**
	 * Get BuddyDrive upload data
	 *
	 * @since 1.3.0
	 *
	 * @return array an associative array to inform about the upload base dir and url
	 */
	public function get_upload_data() {
		// Make sure to run this part once
		if ( empty( $this->upload_data['dir'] ) ) {
			/**
			 * In previous version it was possible to change
			 * the upload data before upload dir has been created
			 * using this filter. Keeping it for backcompat
			 *
			 * @since  1.2.0
			 *
			 * @param array an associative array to inform about the upload base dir and url
			 */
			$this->upload_data = apply_filters( 'buddydrive_get_upload_data', array(
				'dir'      => $this->upload_path,
				'url'      => $this->url,
				'thumbdir' => $this->upload_path . '-thumbnails',
				'thumburl' => $this->url . '-thumbnails',
			) );

			if ( $this->upload_data['dir'] != $this->upload_path ) {
				$this->upload_path = $this->upload_data['dir'];
			}

			if ( $this->upload_data['url'] != $this->url ) {
				$this->url = $this->upload_data['url'];
			}
		}

		return $this->upload_data;
	}

	/**
	 * Create the BuddyDrive dir and add an .htaccess in it
	 *
	 * @since 1.3.0
	 *
	 * @return bool true on dir created, false otherwise
	 */
	public function create_dir() {
		// Let's be sure old filter is fired
		$this->get_upload_data();

		// Create a public folder for thumbnails
		if ( ! empty( $this->upload_data['thumbdir'] ) && ! is_dir( $this->upload_data['thumbdir'] ) ) {
			wp_mkdir_p( $this->upload_data['thumbdir'] );
		}

		// Check if upload path already exists
		if ( ! is_dir( $this->upload_path ) ) {

			// If path does not exist, attempt to create it
			if ( ! wp_mkdir_p( $this->upload_path ) ) {
				return false;
			}

			// then we need to check for .htaccess and eventually create it
			if ( ! file_exists( $this->upload_path .'/.htaccess' ) ) {
				$this->required_wp_files['misc'] = 'misc';
				$this->includes();

				// Defining the rule, we need to make it unreachable and use php to reach it
				$rules = array( 'Order Allow,Deny','Deny from all' );

				// creating the .htaccess file
				insert_with_markers( $this->upload_path .'/.htaccess', 'Buddydrive', $rules );
				unset( $this->required_wp_files['misc'] );
			}
		}

		// Directory exists
		return true;
	}

	/**
	 * BuddyDrive specific upload rules
	 *
	 * @since 1.3.0
	 *
	 * @param  array $file the temporary file attributes (before it has been moved)
	 * @return array the file
	 */
	public function validate_upload( $file = array() ) {
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
			/**
			 * Validation for custom errors
			 *
			 * @since 1.2.2
			 *
			 * @param $file the file data
			 */
			return apply_filters( 'buddydrive_upload_errors', $file );
		}

		return $file;
	}

	/**
	 * Set the directory when uploading a file
	 *
	 * @since 1.3.0
	 *
	 * @return array upload data (path, url, basedir...)
	 */
	public function upload_dir_filter() {
		$upload_data = parent::upload_dir_filter();
		/**
		 * Filters BuddyDrive's upload data.
		 *
		 * If people used to filter 'buddydrive_upload_datas', we need
		 * to have it here
		 *
		 * @since 1.0
		 *
		 * @param array $value Array containing the path, URL, and other helpful settings.
		 */
		return apply_filters( 'buddydrive_upload_datas', $upload_data );
	}

	/**
	 * Build script datas for the Uploader UI
	 *
	 * @since 1.3.0
	 *
	 * @return array the javascript localization data
	 */
	public function script_data() {
		// Get default script data
		$script_data = parent::script_data();

		$script_data['bp_params'] = array(
			'object'  => buddydrive_get_file_post_type(),
			'item_id' => bp_loggedin_user_id(),
			'privacy' => 'public',
		);

		if ( buddydrive_current_group_is_enabled() ) {
			$script_data['bp_params']['privacy']         = 'groups';
			$script_data['bp_params']['privacy_item_id'] = bp_get_current_group_id();
		}

		// Include our specific css
		$script_data['extra_css'] = array( 'buddydrive-public-style' );

		// Include our specific js
		$script_data['extra_js']  = array( 'buddydrive-public-js' );

		return apply_filters( 'buddydrive_attachment_script_data', $script_data );
	}
}
endif;
