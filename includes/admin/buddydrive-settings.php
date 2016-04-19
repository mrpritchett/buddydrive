<?php
/**
 * BuddyDrive Settings
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


/**
 * The main settings arguments
 *
 * @return array
 */
function buddydrive_admin_get_settings_sections() {
	return (array) apply_filters( 'buddydrive_admin_get_settings_sections', array(
		'buddydrive_settings_main' => array(
			'title'    => __( 'Main Settings', 'buddydrive' ),
			'callback' => 'buddydrive_admin_setting_callback_main_section',
			'page'     => 'buddydrive',
		),
		'buddydrive_settings_customs' => array(
			'title'    => __( 'Custom Slugs and Names Settings', 'buddydrive' ),
			'callback' => 'buddydrive_admin_setting_callback_custom_section',
			'page'     => 'buddydrive',
		)
	) );
}

/**
 * The different fields for the main settings
 *
 * @return array
 */
function buddydrive_admin_get_settings_fields() {
	return (array) apply_filters( 'buddydrive_admin_get_settings_fields', array(

		/** Main Section ******************************************************/

		'buddydrive_settings_main' => array(

			// Default Privacy
			'_buddydrive_default_privacy' => array(
				'title'             => __( 'Default privacy for new items', 'buddydrive' ),
				'callback'          => 'buddydrive_admin_setting_callback_default_privacy',
				'sanitize_callback' => 'buddydrive_sanitize_default_privacy',
				'args'              => array()
			),

			// User's quota
			'_buddydrive_user_quota' => array(
				'title'             => __( 'Space available for each user', 'buddydrive' ),
				'callback'          => 'buddydrive_admin_setting_callback_user_quota',
				'sanitize_callback' => 'buddydrive_sanitize_user_quota',
				'args'              => array()
			),

			// Max upload size
			'_buddydrive_max_upload' => array(
				'title'             => __( 'Max upload size', 'buddydrive' ),
				'callback'          => 'buddydrive_admin_setting_callback_max_upload',
				'sanitize_callback' => 'buddydrive_sanitize_max_upload',
				'args'              => array()
			),

			// Allowed extensions
			'_buddydrive_allowed_extensions' => array(
				'title'             => __( 'Mime types allowed', 'buddydrive' ),
				'callback'          => 'buddydrive_admin_setting_callback_allowed_extensions',
				'sanitize_callback' => 'buddydrive_sanitize_allowed_extension',
				'args'              => array()
			),

			// Auto enable BuddyDrive on group creation
			'_buddydrive_auto_group' => array(
				'title'             => __( 'Enable BuddyDrive for groups on group creation', 'buddydrive' ),
				'callback'          => 'buddydrive_admin_setting_callback_auto_group',
				'sanitize_callback' => 'absint',
				'args'              => array()
			)
		),

		/** Custom Section ******************************************************/

		'buddydrive_settings_customs' => array(

			// Main subnav name
			'_buddydrive_user_subnav_name' => array(
				'title'             => __( 'Name for main subnav', 'buddydrive' ),
				'callback'          => 'buddydrive_admin_setting_callback_user_subnav_name',
				'sanitize_callback' => 'buddydrive_sanitize_custom_name',
				'args'              => array()
			),

			// Friends subnav slug
			'_buddydrive_friends_subnav_slug' => array(
				'title'             => __( 'Slug for friends subnav', 'buddydrive' ),
				'callback'          => 'buddydrive_admin_setting_callback_friends_subnav_slug',
				'sanitize_callback' => 'buddydrive_sanitize_custom_slug',
				'args'              => array()
			),

			// Friends subnav slug
			'_buddydrive_friends_subnav_name' => array(
				'title'             => __( 'Name for friends subnav', 'buddydrive' ),
				'callback'          => 'buddydrive_admin_setting_callback_friends_subnav_name',
				'sanitize_callback' => 'buddydrive_sanitize_custom_name',
				'args'              => array()
			),
		)
	) );

}


/**
 * Gives the setting fields for section
 *
 * @param  string $section_id
 * @return array  the fields
 */
function buddydrive_admin_get_settings_fields_for_section( $section_id = '' ) {

	// Bail if section is empty
	if ( empty( $section_id ) )
		return false;

	$fields = buddydrive_admin_get_settings_fields();
	$retval = isset( $fields[$section_id] ) ? $fields[$section_id] : false;

	return (array) apply_filters( 'buddydrive_admin_get_settings_fields_for_section', $retval, $section_id );
}

/**
 * Some text to introduce the settings section
 *
 * @return string html
 */
function buddydrive_admin_setting_callback_main_section() {
?>

	<p><?php _e( 'Customize your Buddydrive!', 'buddydrive' ); ?></p>

<?php
}

/**
 * Some text to introduce the custom settings section
 *
 * @return string html
 */
function buddydrive_admin_setting_callback_custom_section() {
	$page_id = isset( buddypress()->pages->buddydrive->id ) ? buddypress()->pages->buddydrive->id : false;
?>

	<p><?php _e( 'Customize the slugs and names of Buddydrive!', 'buddydrive' ); ?></p>

	<?php if( !empty( $page_id ) ) :?>
		<p class="description"><?php printf( __( 'NB : to customize the name and the slug of the main directory page, you need to edit the title and permalink of its <a href="%s">WordPress page</a>', 'buddydrive' ), esc_url( get_edit_post_link( $page_id ) ) );?></p>

<?php endif;
}

/**
 * Let Admins define default privacy
 *
 * @since 2.0.0
 */
function buddydrive_admin_setting_callback_default_privacy() {
	$buddydrive_default_privacy = bp_get_option( '_buddydrive_default_privacy', 'buddydrive_public' );
	$stati = get_post_stati( array( 'buddydrive_settings' => true ), 'objects' );
	?>
	<select name="_buddydrive_default_privacy" id="_buddydrive_default_privacy">
		<?php foreach ( $stati as $status )  : ?>
			<option value="<?php echo esc_attr( $status->name ) ;?>" <?php selected( $buddydrive_default_privacy, $status->name );?>><?php echo esc_html( $status->label );?></option>
		<?php endforeach ;?>
	</select>
	<?php
}

/**
 * Let the admin customize users quota
 *
 * @uses bp_get_option() to get the user's quota
 * @uses translate_user_role() to get the role caption out of his name
 * @return string html
 */
function buddydrive_admin_setting_callback_user_quota() {
	$roles = get_editable_roles();

	$user_quota = bp_get_option( '_buddydrive_user_quota', 1000 );

	if( is_array( $user_quota ) )
		$user_quota = array_map( 'absint', $user_quota );

	else {
		$default = intval( $user_quota );
		$user_quota = array_fill_keys( array_keys( $roles ), $default );
	}
	?>
	<table>

		<?php foreach ( $roles as $role => $details ) :
			$name = translate_user_role( $details['name'] );
			?>

			<tr>
				<td><strong><?php echo $name;?></strong></td>
				<td>
					<input name="_buddydrive_user_quota[<?php echo $role;?>]" type="number" min="1" step="1" id="_buddydrive_user_quota-<?php echo $role;?>" value="<?php buddydrive_admin_fill_empty_roles( $role, $user_quota);?>" class="small-text" />
					<label for="_buddydrive_user_quota[<?php echo $role;?>]"><?php _e( 'MO', 'buddydrive' ); ?></label>
				</td>
			</tr>

		<?php endforeach;?>

	</table>
	<?php
}

/**
 * Adds a default value in case of new roles or retrieve the known role value.
 *
 * @since  version 1.1
 *
 * @param  string  $role      role to check
 * @param  array  $user_quota list of customized quota by roles
 * @param  integer $default   1000 as it was previous version default value
 * @return integer            the value
 */
function buddydrive_admin_fill_empty_roles( $role, $user_quota, $default = 1000 ) {

	if( !empty( $user_quota[$role] ) )
		echo intval( $user_quota[$role] );
	else
		echo $default;
}

/**
 * Let the admin customize the max upload size as long as it's less than its config can !
 *
 * @uses buddydrive_max_upload_size() to get the max upload size choosed
 * @return string html
 */
function buddydrive_admin_setting_callback_max_upload() {
	$buddydrive_upload = buddydrive_max_upload_size();
	?>
	<input name="_buddydrive_max_upload" type="number" min="1" step="1" id="_buddydrive_max_upload" value="<?php echo $buddydrive_upload;?>" class="small-text" />
	<label for="_buddydrive_max_upload"><?php _e( 'MO', 'buddydrive' ); ?></label>
	<?php
}

/**
 * Let the admin selects the different mime types he wants
 *
 * @uses get_allowed_mime_types() to get all the WordPress mime types
 * @uses buddydrive_allowed_file_types() to get the one activated for BuddyDrive
 * @uses buddydrive_array_checked() to activate the checkboxes if needed
 * @return string html
 */
function buddydrive_admin_setting_callback_allowed_extensions() {
	$ext = get_allowed_mime_types();
	$buddydrive_ext = buddydrive_allowed_file_types( $ext );
	?>
	<ul>
		<li><input type="checkbox" id="buddydrive-toggle-all" checked /> <?php _e( 'Select / Unselect all', 'buddydrive' );?></li>
		<?php foreach( $ext as $motif => $mime ):?>

			<li style="display:inline-block;width:45%;margin-right:1em"><input type="checkbox" class="buddydrive-admin-cb" value="<?php echo $motif;?>" name="_buddydrive_allowed_extensions[]" <?php buddydrive_array_checked( $motif, $buddydrive_ext );?>> <?php echo $mime;?></li>

		<?php endforeach;?>
	</ul>
	<script type="text/javascript">
		jQuery('#buddydrive-toggle-all').on('change', function(){
			var status = jQuery(this).attr('checked');

			if( !status )
				status = false;

			jQuery('.buddydrive-admin-cb').each( function() {
				jQuery(this).attr('checked', status );
			});

			return false;
		})
	</script>
	<?php
}

/**
 * Let the admin automatically enable BuddyDrive on group creation
 *
 * @since  version 1.1
 *
 * @uses bp_get_option() to get the user's subnav
 * @uses checked() to eventually add a checked attribute
 * @return string html
 */
function buddydrive_admin_setting_callback_auto_group() {
	$enable = bp_get_option( '_buddydrive_auto_group', 0 );
	?>
	<input id="_buddydrive_auto_group" name="_buddydrive_auto_group" type="checkbox" value="1" <?php checked( 1, $enable );?> />
	<?php
}

/**
 * Let the admin customize the name of the main user's subnav
 *
 * @since  version 1.1
 *
 * @uses bp_get_option() to get the user's subnav
 * @uses sanitize_title() to sanitize user's subnav name
 * @return string html
 */
function buddydrive_admin_setting_callback_user_subnav_name() {
	$user_subnav = bp_get_option( '_buddydrive_user_subnav_name', __( 'BuddyDrive Files', 'buddydrive' ) );
	$user_subnav = sanitize_text_field( $user_subnav );
	?>

	<input name="_buddydrive_user_subnav_name" type="text" id="_buddydrive_user_subnav_name" value="<?php echo $user_subnav;?>" class="regular-text code" />

	<?php
}

/**
 * Let the admin customize the slug of the friends subnav
 *
 * @since  version 1.1
 *
 * @uses bp_get_option() to get the user's subnav
 * @uses sanitize_title() to sanitize user's subnav name
 * @return string html
 */
function buddydrive_admin_setting_callback_friends_subnav_slug() {
	$friends_slug = bp_get_option( '_buddydrive_friends_subnav_slug', 'friends' );
	$friends_slug = sanitize_title( $friends_slug );
	?>

	<input name="_buddydrive_friends_subnav_slug" type="text" id="_buddydrive_friends_subnav_slug" value="<?php echo $friends_slug;?>" class="regular-text code" />

	<?php
}

/**
 * Let the admin customize the slug of the friends subnav
 *
 * @since  version 1.1
 *
 * @uses bp_get_option() to get the user's subnav
 * @uses sanitize_title() to sanitize user's subnav name
 * @return string html
 */
function buddydrive_admin_setting_callback_friends_subnav_name() {
	$friends_subnav = bp_get_option( '_buddydrive_friends_subnav_name', __( 'Between Friends', 'buddydrive' ) );
	$friends_subnav = sanitize_text_field( $friends_subnav );
	?>

	<input name="_buddydrive_friends_subnav_name" type="text" id="_buddydrive_friends_subnav_name" value="<?php echo $friends_subnav;?>" class="regular-text code" />

	<?php
}

/**
 * Sanitize the default privacy
 *
 * @param string $option
 * @return string $option
 */
 function buddydrive_sanitize_default_privacy( $option ) {
 	if ( ! buddydrive_get_privacy( $option ) ) {
 		return '';
 	}

 	return $option;
 }

/**
 * Sanitize the user's quota
 *
 * @param int $option
 * @return int the user's quota
 */
function buddydrive_sanitize_user_quota( $option ) {
	if( is_array( $option ) )
		$option =  array_map( 'intval', $option );
	else
		$option = intval( $option );

	return $option;
}

/**
 * Make sure the max upload remains under the config limit
 *
 * @param  int $option
 * @uses wp_max_upload_size() to get the max value of the config
 * @return int the max upload sanitized
 */
function buddydrive_sanitize_max_upload( $option ) {
	$input = intval( $option );

	if( !empty( $input ) ) {
		$max = wp_max_upload_size();
		$check = $input * 1024 * 1024;

		if( $max < $input )
			$input = $max / 1024 / 1024;
	}

	return $input;
}

/**
 * Sanitize the extensions choosed
 *
 * @param  array $option
 * @return array the sanitized allowed mime types
 */
function buddydrive_sanitize_allowed_extension( $option ) {
	if( is_array( $option ) )
		$input = array_map( 'trim', $option );

	return $input;
}

/**
 * Sanitize the slug
 *
 * @since  version 1.1
 *
 * @param string $option
 * @uses sanitize_title() to sanitize the slug
 * @return string the slug
 */
function buddydrive_sanitize_custom_slug( $option ) {
	$option = sanitize_title( $option );

	return $option;
}

/**
 * Sanitize the names
 *
 * @since  version 1.1
 *
 * @param string $option
 * @uses sanitize_text_field() to sanitize the name
 * @return string the slug
 */
function buddydrive_sanitize_custom_name( $option ) {
	$option = sanitize_text_field( $option );

	return $option;
}

/**
 * Displays the settings page
 *
 * @uses is_multisite() to check for multisite
 * @uses add_query_arg() to add arguments to query in case of multisite
 * @uses bp_get_admin_url to build the settings url in case of multisite
 * @uses screen_icon() to show BuddyDrive icon
 * @uses settings_fields()
 * @uses do_settings_sections()
 * @uses wp_nonce_field() for security reason in case of multisite
 */
function buddydrive_admin_settings() {
	$form_action = 'options.php';

	if( bp_core_do_network_admin() ) {
		do_action( 'buddydrive_multisite_options' );

		$form_action = add_query_arg( 'page', 'buddydrive', bp_get_admin_url( 'settings.php' ) );
	}
?>

	<div class="wrap">

		<?php screen_icon('buddydrive'); ?>

		<h2><?php _e( 'BuddyDrive Settings', 'buddydrive' ) ?></h2>

		<form action="<?php echo esc_url( $form_action );?>" method="post">

			<?php settings_fields( 'buddydrive' ); ?>

			<?php do_settings_sections( 'buddydrive' ); ?>

			<p class="submit">
				<?php if( bp_core_do_network_admin() ) :?>
					<?php wp_nonce_field( 'buddydrive_settings', '_wpnonce_buddydrive_setting' ); ?>
				<?php endif;?>
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'buddydrive' ); ?>" />
			</p>
		</form>
	</div>

<?php
}


/**
 * Save settings in case of a multisite config
 *
 * @uses check_admin_referer() to check the nonce
 * @uses buddydrive_sanitize_user_quota() to sanitize user's quota
 * @uses bp_update_option() to save the options in root blog
 * @uses buddydrive_sanitize_max_upload() to sanitize max upload
 * @uses buddydrive_sanitize_allowed_extension() to sanitize the mime types
 * @uses buddydrive_sanitize_custom_name() to sanitize the custom names of subnavs
 * @uses buddydrive_sanitize_custom_slug() to sanitize the custom slugs of subnavs
 */
function buddydrive_handle_settings_in_multisite() {

	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	check_admin_referer( 'buddydrive_settings', '_wpnonce_buddydrive_setting' );

	$user_quota  = buddydrive_sanitize_user_quota( $_POST['_buddydrive_user_quota'] );

	if( ! empty( $user_quota ) )
		bp_update_option( '_buddydrive_user_quota', $user_quota );

	$max_upload  = buddydrive_sanitize_max_upload( $_POST['_buddydrive_max_upload'] );

	if( ! empty( $max_upload ) )
		bp_update_option( '_buddydrive_max_upload', $max_upload );

	$allowed_ext = buddydrive_sanitize_allowed_extension( $_POST['_buddydrive_allowed_extensions'] );

	if( ! empty( $allowed_ext ) && is_array( $allowed_ext ) )
		bp_update_option( '_buddydrive_allowed_extensions', $allowed_ext );

	$main_subnav = buddydrive_sanitize_custom_name( $_POST['_buddydrive_user_subnav_name'] );

	if( ! empty( $main_subnav ) )
		bp_update_option( '_buddydrive_user_subnav_name', $main_subnav );

	$friends_slug = buddydrive_sanitize_custom_slug( $_POST['_buddydrive_friends_subnav_slug'] );

	if( ! empty( $friends_slug ) )
		bp_update_option( '_buddydrive_friends_subnav_slug', $friends_slug );

	$friends_subnav = buddydrive_sanitize_custom_name( $_POST['_buddydrive_friends_subnav_name'] );

	if( ! empty( $friends_subnav ) )
		bp_update_option( '_buddydrive_friends_subnav_name', $friends_subnav );

	$group_enable = isset( $_POST['_buddydrive_auto_group'] ) ? intval( $_POST['_buddydrive_auto_group'] ) : 0;
	bp_update_option( '_buddydrive_auto_group', $group_enable );

	?>
	<div id="message" class="updated"><p><?php _e( 'Settings saved', 'buddydrive' );?></p></div>
	<?php

}

add_action( 'buddydrive_multisite_options', 'buddydrive_handle_settings_in_multisite', 0 );
