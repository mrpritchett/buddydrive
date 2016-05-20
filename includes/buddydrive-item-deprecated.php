<?php
/**
 * Deprecated functions & classes
 *
 * @deprecated 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Populates the translation array for js messages
 *
 * @deprecated 2.0.0
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
* The Uploader class
*
* @package BuddyDrive
* @since 1.0
* @deprecated 2.0.0
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
				<input type="hidden" id="buddydrive-sharing-settings" value="<?php echo esc_attr( buddydrive_get_default_privacy() ) ; ?>">
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
						'title'         => __( 'Allowed Files', 'buddydrive' ),
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

/**
 * Loads the css and javascript when on Friends/Group BuddyDrive
 *
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * Adds post datas to include a file / folder to a private message
 *
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * Files are ajax uploaded !
 *
 * Adds some customization to the WordPress upload process.
 *
 * @since  1.0
 * @deprecated 2.0.0
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
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Check the nonce
	check_admin_referer( 'buddydrive-form' );

	$output = '';
	$privacy = $password = $parent = $customs = false;
	$groups = array();

	$buddydrive_file = buddydrive_upload_item( $_FILES, bp_loggedin_user_id() );

	if ( ! empty( $buddydrive_file ) && is_array( $buddydrive_file ) && empty( $buddydrive_file['error'] ) ) {
		$name           = $_FILES['buddyfile-upload']['name'];
		$name_parts     = pathinfo( $name );
		$name           = $name_parts['filename'];
		$content        = false;
		if ( ! empty( $_POST['buddydesc'] ) ) {
			$content    = wp_kses( $_POST['buddydesc'], array() );
		}

		// Defaults to private
		$privacy = 'private';

		if ( ! empty( $_POST['buddyshared'] ) ) {
			$privacy = $_POST['buddyshared'];

			// Shared in a group
			if ( ! empty( $_POST['buddygroup'] ) && 'groups' === $privacy ) {
				$groups = $_POST['buddygroup'];
			}

			// Using a password
			if ( ! empty( $_POST['buddypass'] ) ) {
				$password = $_POST['buddypass'];
			}
		}

		if ( ! empty( $_POST['buddyfolder'] ) ) {
			$parent  = (int) $_POST['buddyfolder'];
		}

		if ( ! empty( $_POST['customs'] ) ) {
			$customs = json_decode( wp_unslash( $_POST['customs'] ) );
		}

		// Construct the buddydrive_file_post_type array
		$args = array(
			'type'             => buddydrive_get_file_post_type(),
			'title'            => $name,
			'content'          => $content,
			'mime_type'        => $buddydrive_file['type'],
			'guid'             => $buddydrive_file['url'],
			'privacy'          => $privacy,
			'groups'           => $groups,
			'password'         => $password,
		);

		if ( ! empty( $parent ) ) {
			$args['parent_folder_id'] = $parent;
		}

		if ( ! empty( $customs ) ) {
			$args['customs'] = $customs;
		}

		$buddyfile_id = buddydrive_add_item( $args );

		if ( ! empty( $buddyfile_id ) && 'public' === $privacy ) {
			buddydrive_set_thumbnail( $buddyfile_id, $buddydrive_file );
		}

		echo $buddyfile_id;

	} else {
		echo '<div class="error-div"><a class="dismiss" href="#">' . __( 'Dismiss', 'buddydrive' ) . '</a><strong>' . sprintf( __( '&#8220;%s&#8221; has failed to upload due to an error : %s', 'buddydrive' ), esc_html( $_FILES['buddyfile-upload']['name'] ), $buddydrive_file['error'] ) . '</strong><br /></div>';
	}

	die();
}

/**
 * Handle public file uploaded using buddydrive_editor
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 */
function buddydrive_add_public_file() {
	_deprecated_function( __FUNCTION__, '2.0' );
	return buddydrive_upload_file();
}

/**
 * Gets the latest created file once uploaded
 *
 * Fixes IE shit
 *
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses check_admin_referer() for security reasons
 * @uses buddydrive_delete_item() deletes the post_type (file or folder)
 * @return array the result with the item ids deleted
 */
function buddydrive_delete_items() {

	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Check the nonce
	check_admin_referer( 'buddydrive_actions', '_wpnonce_buddydrive_actions' );

	$items = $_POST['items'];

	$items = substr( $items, 0, strlen( $items ) - 1 );

	$items = explode( ',', $items );

	$deleted = buddydrive_delete_item( array( 'ids' => $items, 'user_id' => false ) );

	if ( ! empty( $deleted ) ) {
		echo json_encode( array( 'result' => count( $deleted ), 'items' => $deleted ) );
	} else {
		echo json_encode( array( 'result' => 0 ) );
	}

	die();

}
add_action( 'wp_ajax_buddydrive_deleteitems', 'buddydrive_delete_items');

/**
 * Loads a form to edit a file or a folder
 *
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
	if ( ! empty( $result ) ) {
		// Update the group's latest activity
		groups_update_last_activity( $group_id );

		echo 1;
	} else {
		_e( 'this is embarassing, it did not work :(', 'buddydrive' );
	}

	die();
}
add_action( 'wp_ajax_buddydrive_groupupdate', 'buddydrive_share_in_group' );

/**
 * Post an activity in user's profile
 *
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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

	if ( empty( $item_id ) || empty( $group_id ) ) {
		echo 0;
	} else {
		$removed = buddydrive_remove_item_from_group( $item_id, $group_id );

		/**
		 * Old UI is expecting 1 and since 2.0.0
		 * we now return the removed item ID.
		 */
		if ( $removed ) {
			echo 1;
		}
	}

	die();

}
add_action( 'wp_ajax_buddydrive_removefromgroup', 'buddydrive_remove_from_group' );

/**
 * Prints the User files screen title
 *
 * @deprecated 2.0.0
 */
function buddydrive_user_files_title() {
	buddydrive_item_nav();
}

/**
 * Prints the User files screen content
 *
 * @deprecated 2.0.0
 */
function buddydrive_user_files_content() {
	?>
	<div id="buddydrive-forms">
		<div class="buddydrive-crumbs"><a href="<?php esc_url( buddydrive_component_home_url() );?>" name="home" id="buddydrive-home"><i class="icon bd-icon-root"></i> <span id="folder-0" class="buddytree current"><?php esc_html_e( 'Root folder', 'buddydrive' );?></span></a></div>

		<?php if ( buddydrive_is_user_buddydrive() ):?>

			<div id="buddydrive-file-uploader" class="hide">
				<?php buddydrive_upload_form();?>
			</div>
			<div id="buddydrive-folder-editor" class="hide">
				<?php buddydrive_folder_form()?>
			</div>
			<div id="buddydrive-edit-item" class="hide"></div>

		<?php endif;?>

	</div>

	<?php do_action( 'buddydrive_after_member_upload_form' ); ?>
	<?php do_action( 'buddydrive_before_member_body' );?>

	<div class="buddydrive single-member" role="main">
		<?php bp_get_template_part( 'buddydrive-loop' );?>
	</div><!-- .buddydrive.single-member -->

	<?php do_action( 'buddydrive_after_member_body' );
}

/**
 * Prints the User friends files screen title
 *
 * @deprecated 2.0.0
 */
function buddydrive_friends_files_title() {
	buddydrive_item_nav();
}

/**
 * Prints the User friends files screen content
 *
 * @deprecated 2.0.0
 */
function buddydrive_friends_files_content() {
	?>
	<div id="buddydrive-forms">
		<div class="buddydrive-crumbs"><a href="<?php esc_url( buddydrive_component_home_url() );?>" name="home" id="buddydrive-home"><i class="icon bd-icon-root"></i> <span id="folder-0" class="buddytree current"><?php esc_html_e( 'Root folder', 'buddydrive' );?></span></a></div>
	</div>

	<?php do_action( 'buddydrive_after_member_upload_form' ); ?>
	<?php do_action( 'buddydrive_before_member_body' );?>

	<div class="buddydrive single-member" role="main">
		<?php bp_get_template_part( 'buddydrive-loop' );?>
	</div><!-- .buddydrive.single-member -->

	<?php do_action( 'buddydrive_after_member_body' );
}

function buddydrive_group_deprecated_display( $group_id = 0 ) {
	buddydrive_item_nav();
	?>
	<div class="buddydrive-crumbs in-group">
		<a href="<?php esc_url( buddydrive_component_home_url() );?>" name="home" id="buddydrive-home" data-group="<?php echo esc_attr( $group_id );?>"><i class="icon bd-icon-root"></i> <span id="folder-0" class="buddytree current"><?php _e( 'Root folder', 'buddydrive');?></span></a>

		<?php if ( groups_is_user_member( bp_loggedin_user_id(), $group_id) ) : ?>
			<?php buddydrive_user_buddydrive_url();?>
		<?php endif ; ?>
	</div>

	<div class="buddydrive single-group" role="main">
		<?php bp_get_template_part( 'buddydrive-loop' );?>
	</div><!-- .buddydrive.single-group -->
	<?php
}

/**
 * Checks if the active theme is  BP Default or a child or a standalone
 *
 * @deprecated 2.0.0
 *
 * @uses get_stylesheet() to check for BP Default
 * @uses get_template() to check for a Child Theme of BP Default
 * @uses current_theme_supports() to check for a standalone BuddyPress theme
 * @return boolean true or false
 */
function buddydrive_is_bp_default() {
	if ( in_array( 'bp-default', array( get_stylesheet(), get_template() ) ) )
        return true;

    if ( current_theme_supports( 'buddypress') ) {
    	return true;
    } else {
        return false;
    }
}

/**
 * Chooses the best way to load BuddyDrive templates
 *
 * @deprecated 2.0.0
 *
 * @param string $template the template needed
 * @param boolean $require_once if we need to load it only once or more
 * @uses buddydrive_is_bp_default() to check for BP Default
 * @uses load_template()
 * @uses bp_get_template_part()
 */
function buddydrive_get_template( $template = false, $require_once = true ) {
	if ( empty( $template ) )
		return false;

	if ( buddydrive_is_bp_default() ) {

		$template = $template . '.php';

		if ( file_exists( STYLESHEETPATH . '/' . $template ) )
			$filtered_templates = STYLESHEETPATH . '/' . $template;
		else
			$filtered_templates = buddydrive_get_plugin_dir() . '/templates/' . $template;

		load_template( apply_filters( 'buddydrive_get_template', $filtered_templates ),  $require_once);

	} else {
		bp_get_template_part( $template );
	}
}

/**
 * Echoes the right link to BuddyDrive root folder regarding to context
 *
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @param string $id       the id of the select box
 * @param string $selected if an option have been selected (edit form)
 * @param string $name     the name of the select boc
 * @uses selected() to activate an option if $selected is defined
 * @return the select box
 */
function buddydrive_select_sharing_options( $id = 'buddydrive-sharing-options', $selected = false, $name = false ) {
	if ( empty( $selected ) ) {
		$selected = buddydrive_get_default_privacy();
	}
	?>
	<select id="<?php echo esc_attr( $id );?>" <?php if ( ! empty( $name ) ) echo 'name="' . $name . '"';?>>

	<?php foreach ( (array) buddydrive_get_sharing_options() as $key => $option ) : ?>
		<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected, $key );?>><?php echo esc_html( $option ); ?></option>
	<?php endforeach; ?>

	</select>
	<?php

}

/**
 * Displays the select box to choose a folder to attach the BuddyFile to.
 *
 * @deprecated 2.0.0
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
	 *
	 * @deprecated 2.0.0
	 *
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
 * @deprecated 2.0.0
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
	 *
	 * @deprecated 2.0.0
	 *
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
		if ( empty( $user_id ) ) {
			$user_id = bp_loggedin_user_id();
		}

		if ( is_array( $selected ) ) {
			$selected = reset( $selected );
		}

		$name = ! empty( $name ) ? ' name="'.$name.'"' : false ;

		$output = __( 'No group available for BuddyDrive', 'buddydrive' );

		if ( ! bp_is_active( 'groups' ) ) {
			return $output;
		}

		$user_groups = groups_get_groups( array(
			'user_id'     => $user_id,
			'show_hidden' => true,
			'per_page'    => false,
			'meta_query'  => array(
				array(
					'key'     => '_buddydrive_enabled',
					'value'   => 1,
					'compare' => '='
			) )
		) );

		if ( empty( $user_groups['groups'] ) ) {
			return $output;
		}

		/**
		 * Filter here to restrict the groups the user can publish a file into
		 *
		 * @since 1.3.4
		 *
		 * @param array $value   A list of group objects the user can publish a BuddyDrive item into
		 * @param int   $user_id The user ID
		 */
		$buddydrive_groups = apply_filters( 'buddydrive_filter_select_user_group', $user_groups['groups'], $user_id );

		// building the select box
		if ( ! empty( $buddydrive_groups ) && is_array( $buddydrive_groups ) ) {
			$output = '<select id="buddygroup"'.$name.'>' ;
			foreach ( $buddydrive_groups as $buddydrive_group ) {
				$output .= sprintf( '<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $buddydrive_group->id ),
					selected( $selected, $buddydrive_group->id, false ),
					esc_html( $buddydrive_group->name )
				);
			}
			$output .= '</select>';
		}

		return apply_filters( 'buddydrive_get_select_user_group', $output, $buddydrive_groups );
	}


/**
 * Displays the form to create a new folder
 *
 * @deprecated 2.0.0
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
			<input type="hidden" id="buddyfolder-sharing-settings" value="<?php echo esc_attr( buddydrive_get_default_privacy() ) ;?>">
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
 * @deprecated 2.0.0
 *
 * @uses BuddyDrive_Uploader() class
 */
function buddydrive_upload_form() {
	return new BuddyDrive_Uploader();
}

/**
 * BuddyDrive Loop : do we have items for the query asked
 *
 * @deprecated 2.0.0
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

		if ( bp_displayed_user_id() ) {
			$user = bp_displayed_user_id();
		}

		$buddyscope = bp_current_action();

		if ( $buddyscope == buddydrive_get_friends_subnav_slug() ){
			$buddyscope = 'friends';
		}

		if ( is_admin() ) {
			$buddyscope = 'admin';
		}

		if ( bp_is_active( 'groups' ) && buddydrive_is_group() ) {
			$group = groups_get_current_group();

			$group_id = $group->id;
			$buddyscope = 'groups';
		}

		/**
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

		$r = bp_parse_args( $args, $defaults, 'buddydrive_has_items' );

		if ( 'admin' === $r['buddydrive_scope'] && ! bp_current_user_can( 'bp_moderate' ) ) {
			$r['buddydrive_scope'] = 'files';
		}

		$buddydrive_template = new BuddyDrive_Item();

		if ( ! empty( $search ) ) {
			$buddydrive_template->get( array( 'per_page' => $r['per_page'], 'paged' => $r['paged'], 'type' => $r['type'], 'buddydrive_scope' => $r['buddydrive_scope'], 'search' => $r['search'], 'orderby' => $r['orderby'], 'order' => $r['order'] ) );
		} else {
			$buddydrive_template->get( array( 'id' => $r['id'], 'name' => $r['name'], 'group_id' => $r['group_id'], 'user_id' => $r['user_id'], 'per_page' => $r['per_page'], 'paged' => $r['paged'], 'type' => $r['type'], 'buddydrive_scope' => $r['buddydrive_scope'], 'buddydrive_parent' => $r['buddydrive_parent'], 'exclude' => $r['exclude'], 'orderby' => $r['orderby'], 'order' => $r['order'] ) );
		}

		do_action( 'buddydrive_has_items_catch_total_count', $buddydrive_template->query->found_posts );
	}

	return apply_filters( 'buddydrive_has_items', $buddydrive_template->have_posts() );
}

/**
 * BuddyDrive Loop : do we have more items
 *
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_item_id() to get the item id
 */
function buddydrive_item_id() {
	echo buddydrive_get_item_id();
}

	/**
	 * Gets the item id
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_parent_item_id() to get the parent item id
 */
function buddydrive_parent_item_id() {
	echo buddydrive_get_parent_item_id();
}

	/**
	 * Gets the parent item id
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_item_title() to get the title of the item
 */
function buddydrive_item_title() {
	echo buddydrive_get_item_title();
}

	/**
	 * Gets the title of the BuddyDrive item
	 *
	 * @deprecated 2.0.0
	 *
	 * @global object $buddydrive_template
	 * @return string the title of the item
	 */
	function buddydrive_get_item_title() {
		global $buddydrive_template;

		return apply_filters('buddydrive_get_item_title', esc_html( $buddydrive_template->query->post->post_title ) );
	}

/**
 * Displays the description of the BuddyDrive item
 *
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_item_description() to get the description of the item
 */
function buddydrive_item_description() {
	echo buddydrive_get_item_description();
}

	/**
	 * Gets the description of the BuddyDrive item
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_action_link() to get the action link of the item
 */
function buddydrive_action_link() {
	echo buddydrive_get_action_link();
}

	/**
	 * Gets the action link of the BuddyDrive item
	 *
	 * @deprecated 2.0.0
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

		return apply_filters( 'buddydrive_get_action_link', esc_url( $link ) );
	}

/**
 * Displays an action link class for the BuddyDrive item
 *
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_action_link_class() to get the action link class of the item
 */
function buddydrive_action_link_class() {
	echo buddydrive_get_action_link_class();
}

	/**
	 * Gets the action link class for the BuddyDrive item
	 *
	 * @deprecated 2.0.0
	 *
	 * @global object $buddydrive_template
	 * @uses buddydrive_is_buddyfile() to check for a file
	 * @return string the action link class for the item
	 */
	function buddydrive_get_action_link_class() {
		$class = array();

		$class[] =  buddydrive_is_buddyfile() ? 'buddyfile' : 'buddyfolder';

		$class = apply_filters( 'buddydrive_get_action_link_class', $class );

		return sanitize_html_class( implode( ' ', $class ) );
	}

/**
 * Displays an attribute to identify a folder or a file
 *
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_item_attribute() to get the attribute of the item
 */
function buddydrive_item_attribute() {
	echo buddydrive_get_item_attribute();
}

	/**
	 * Gets the attribute to identify a folder or a file
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_owner_id() to get owner's id
 */
function buddydrive_owner_id() {
	echo buddydrive_get_owner_id();
}

	/**
	 * Gets the user id of the owner of a BuddyDrive item
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_show_owner_avatar() to get avatar of the owner
 */
function buddydrive_owner_avatar() {
	echo buddydrive_get_show_owner_avatar();
}

	/**
	 * Gets the avatar of the owner
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_owner_link() to get the link to the owner's home page
 */
function buddydrive_owner_link() {
	echo buddydrive_get_owner_link();
}

	/**
	 * Gets the link to the owner's home page
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_group_avatar() to get the group avatar
 */
function buddydrive_group_avatar() {
	echo buddydrive_get_group_avatar();
}

	/**
	 * Gets the group avatar the item is attached to
	 *
	 * @deprecated 2.0.0
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

		if ( empty( $group) ) {
			return false;
		}

		$group_link = bp_get_group_permalink( $group );
		$group_name = $group->name;

		$group_avatar  = bp_core_fetch_avatar( array(
			'item_id'    => $buddydrive_item_group_meta,
			'object'     => 'group',
			'type'       => 'thumb',
			'avatar_dir' => 'group-avatars',
			'alt'        => sprintf( __( 'Group logo of %d', 'buddydrive' ), esc_attr( $group_name ) ),
			'width'      => $width,
			'height'     => $height,
			'title'      => esc_attr( $group_name )
		) );

		if ( 'hidden' == $group->status && ! groups_is_user_member( bp_loggedin_user_id(), $buddydrive_item_group_meta ) && ! is_super_admin() ) {
			$nolink = true;
		}

		if ( ! empty( $nolink ) ) {
			return $group_avatar;
		} else {
			return apply_filters( 'buddydrive_get_group_avatar', '<a href="' . esc_url( $group_link ) . '" title="' . esc_attr( $group_name ) . '">' . $group_avatar .'</a>' );
		}
	}

/**
 * Displays the avatar of the owner or a checkbox
 *
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_owner_or_cb()
 */
function buddydrive_owner_or_cb() {
	echo buddydrive_get_owner_or_cb();
}

	/**
	 * Choose between the owner's avatar or a checkbox if on loggedin user's BuddyDrive
	 *
	 * @deprecated 2.0.0
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

		if ( bp_is_my_profile() && bp_current_action() == 'files' ) {
			$output = '<input type="checkbox" name="buddydrive-item[]" class="buddydrive-item-cb" value="' . esc_attr( buddydrive_get_item_id() ) . '">';
		} else {
			$output = '<a href="' . esc_url( buddydrive_get_owner_link() ) . '" title="'. esc_attr__( 'Owner', 'buddydrive' ).'">'. buddydrive_get_show_owner_avatar() . '</a>';
		}

		return apply_filters( 'buddydrive_get_owner_or_cb', $output );
	}

/**
 * Displays a checkbox or a table header
 *
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_th_owner_or_cb()
 */
function buddydrive_th_owner_or_cb() {
	echo buddydrive_get_th_owner_or_cb();
}

	/**
	 * Gets a checkbox or a table header
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
		default:
			/**
			 * Hook here to output the content for your custom privacy options
			 *
			 * @since 1.3.3
			 *
			 * @param array $status The privacy status.
			 */
			do_action( 'buddydrive_default_item_privacy', $status );
		break;
	}
}

	/**
	 * Gets the item's privacy
	 *
	 * @deprecated 2.0.0
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

		$status['privacy'] = buddydrive_get_privacy( $item_privacy_id );

		if ( 'groups' === $status['privacy'] ) {
			$status['group'] = get_post_meta( $item_privacy_id, '_buddydrive_sharing_groups', true );
		}

		return apply_filters( 'buddydrive_get_item_privacy', $status );
	}

/**
 * Displays the mime type of an item
 *
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_item_mime_type() to get it !
 */
function buddydrive_item_mime_type() {
	echo buddydrive_get_item_mime_type();
}

	/**
	 * Gets the mime type of an item
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_item_icon() to get the icon
 */
function buddydrive_item_icon() {
	echo buddydrive_get_item_icon();
}

	/**
	 * Gets the item's icon
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_uploaded_file_name() to get it
 */
function buddydrive_uploaded_file_name() {
	echo buddydrive_get_uploaded_file_name();
}

	/**
	 * Gets the mime type of an item
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_item_date() to get it!
 */
function buddydrive_item_date() {
	echo buddydrive_get_item_date();
}

	/**
	 * Gets the item date
	 *
	 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
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
 * @deprecated 2.0.0
 *
 * @uses buddydrive_get_row_actions()
 */
function buddydrive_row_actions() {
	echo buddydrive_get_row_actions();
}

	/**
	 * Builds the row actions
	 *
	 * @deprecated 2.0.0
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
					$inside_bottom .= '<div class="buddydrive-ra-link hide ba"><input type="text" class="buddydrive-file-input" id="buddydrive-link-' . esc_attr( $buddyfile_id ). '" value="' . esc_url( buddydrive_get_action_link() ). '"></div>';
				}
				if ( buddydrive_current_user_can_share() && bp_is_active( 'activity' ) )
					$inside_top[]= '<a class="buddydrive-profile-activity" href="#">' . __( 'Share', 'buddydrive' ). '</a>';
				break;
			case 'password':
				if ( buddydrive_current_user_can_link( $privacy ) ){
					$inside_top[]= '<a class="buddydrive-show-link" href="#">' . __( 'Link', 'buddydrive' ). '</a>';
					$inside_bottom .= '<div class="buddydrive-ra-link hide ba"><input type="text" class="buddydrive-file-input" id="buddydrive-link-' . esc_attr( $buddyfile_id ) . '" value="' . esc_url( buddydrive_get_action_link() ). '"></div>';
				}
				if ( buddydrive_current_user_can_share() && bp_is_active( 'messages' ) )
					$inside_top[]= '<a class="buddydrive-private-message" href="'. esc_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?buddyitem=' . $buddyfile_id ) . '">' . esc_html__('Share', 'buddydrive'). '</a>';
				break;
			case 'friends':
				if ( buddydrive_current_user_can_link( $privacy ) ){
					$inside_top[]= '<a class="buddydrive-show-link" href="#">' . __( 'Link', 'buddydrive' ). '</a>';
					$inside_bottom .= '<div class="buddydrive-ra-link hide ba"><input type="text" class="buddydrive-file-input" id="buddydrive-link-' . esc_attr( $buddyfile_id ). '" value="' . esc_attr( buddydrive_get_action_link() ). '"></div>';
				}
				if ( buddydrive_current_user_can_share() && bp_is_active( 'messages' ) )
					$inside_top[]= '<a class="buddydrive-private-message" href="'. esc_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?buddyitem='.$buddyfile_id ) . '&friends=1">' . esc_html__( 'Share', 'buddydrive' ). '</a>';
				break;
			case 'groups':
				if ( buddydrive_current_user_can_link( $privacy ) ){
					$inside_top[]= '<a class="buddydrive-show-link" href="#">' . __( 'Link', 'buddydrive' ). '</a>';
					$inside_bottom .= '<div class="buddydrive-ra-link hide ba"><input type="text" class="buddydrive-file-input" id="buddydrive-link-' . esc_attr( $buddyfile_id ) . '" value="' . esc_url( buddydrive_get_action_link() ). '"></div>';
				}
				if ( buddydrive_current_user_can_share() && bp_is_active( 'activity' ) && bp_is_active( 'groups' ) )
					$inside_top[]= '<a class="buddydrive-group-activity" href="#">' . __( 'Share', 'buddydrive' ). '</a>';
				if ( buddydrive_current_user_can_remove( $privacy['group'] ) && bp_is_active( 'groups') )
					$inside_top[]= '<a class="buddydrive-remove-group" href="#" data-group="'. esc_attr( $privacy['group'] ).'">' . esc_html__( 'Remove', 'buddydrive' ). '</a>';
				break;
		}

		if ( ! empty( $inside_top ) )
			$inside_top = '<div class="buddydrive-action-btn">'. implode( ' | ', $inside_top ).'</div>';

		if ( ! empty( $inside_top ) )
			$row_actions .= '<div class="buddydrive-row-actions">' . $inside_top . $inside_bottom .'</div>';

		return apply_filters( 'buddydrive_get_row_actions', $row_actions );
	}

/**
 * Outputs the BuddyDrive user's toolbar & sort selectbox.
 *
 * @deprecated 2.0.0
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
