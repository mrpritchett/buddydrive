<?php
/**
 * BuddyDrive Item template tags
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

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
		if ( empty( $user_id ) ) {
			$user_id = bp_loggedin_user_id();
		}

		$space_left = buddydrive_get_user_space_data( $user_id );

		if ( 'diff' === $type ) {
			return $space_left['diff'];
		} else {
			return apply_filters( 'buddydrive_get_user_space_left', sprintf( __( '<span id="buddy-quota">%s</span>&#37; used', 'buddydrive' ), $space_left['percent'] ), $space_left );
		}

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
 * Outputs the BuddyDrive user's stats.
 */
function buddydrive_wpadmin_profile_stats( $args ) {
	if ( empty( $args['user_id'] ) )
		return;

	$space_left = buddydrive_get_user_space_left( false, $args['user_id'] ) .'%';

	echo '<li class="buddydrive-profile-stats">' . $space_left . '</li>';
}
add_action( 'bp_members_admin_user_stats', 'buddydrive_wpadmin_profile_stats', 10, 1 );

/**
 * An Editor other plugins can use for their need.
 *
 * @since 1.3.0
 *
 * @param string $editor_id the Editor's id to insert the BuddyDrive oembed link into.
 */
function buddydrive_editor( $editor_id = '' ) {
	$buddydrive       = buddydrive();
	$current_user_can = (bool) apply_filters( 'buddydrive_editor_can', is_user_logged_in() );

	// Bail if current user can't use it and if not in front end
	if ( ! $current_user_can || is_admin() ) {
		return;
	}

	// Enqueue Thickbox
	wp_enqueue_style ( 'thickbox' );
	wp_enqueue_script( 'thickbox' );

	if ( ! empty( $editor_id ) ) {
		$buddydrive->editor_id = $editor_id;
	}

	// Temporary filters to add custom strings and settings
	add_filter( 'bp_attachments_get_plupload_l10n',             'buddydrive_editor_strings',     10, 1 );
	add_filter( 'bp_attachments_get_plupload_default_settings', 'buddydrive_editor_settings',    10, 1 );
	add_filter( 'buddydrive_attachment_script_data',            'buddydrive_editor_script_data', 10, 1 );

	// Enqueue BuddyPress attachments scripts
	bp_attachments_enqueue_scripts( 'BuddyDrive_Attachment' );

	// Remove the temporary filters
	remove_filter( 'bp_attachments_get_plupload_l10n',             'buddydrive_editor_strings',     10, 1 );
	remove_filter( 'bp_attachments_get_plupload_default_settings', 'buddydrive_editor_settings',    10, 1 );
	remove_filter( 'buddydrive_attachment_script_data',            'buddydrive_editor_script_data', 10, 1 );

	$url = remove_query_arg( array_keys( $_REQUEST ) );
	?>
	<a href="<?php echo esc_url( $url );?>#TB_inline?inlineId=buddydrive-public-uploader" title="<?php esc_attr_e( 'Add file', 'buddydrive' );?>" id="buddydrive-btn" class="thickbox button">
		<?php echo esc_html_e( 'Add File', 'buddydrive' ); ?>
	</a>
	<div id="buddydrive-public-uploader" style="display:none;">
		<?php /* Markup for the uploader */ ?>
			<div class="buddydrive-uploader"></div>
			<div class="buddydrive-uploader-status"></div>

		<?php bp_attachments_get_template_part( 'uploader' );
		/* Markup for the uploader */ ?>
	</div>
	<?php
}
