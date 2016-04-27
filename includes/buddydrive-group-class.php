<?php
/**
 * BuddyDrive Groups
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'BP_Group_Extension' ) ) :
/**
 * The BuddyDrive group class
 *
 * @package BuddyDrive
 * @since 1.0
 *
 */
class BuddyDrive_Group extends BP_Group_Extension {
	/**
	 * construct method to add some settings and hooks
	 *
	 * @uses buddydrive_get_name() to get the plugin name
	 * @uses buddydrive_get_slug() to get the plugin slug
	 */
	public function __construct() {

		$args = array(
			'slug'              => buddydrive_get_slug(),
			'name'              => buddydrive_get_name(),
			'visibility'        => apply_filters( 'buddydrive_group_nav_visibility', 'private' ),
			'nav_item_position' => 31,
			'enable_nav_item'   => $this->enable_nav_item(),
			'screens'           => array(
				'admin' => array(
					'metabox_context'  => 'side',
					'metabox_priority' => 'core'
				),
				'create' => array(
					'enabled' => false,
				),
				'edit' => array(
					'enabled' => true,
				),
			)
		);

        parent::init( $args );
	}

	/**
	 * The create screen method
	 *
	 * BuddyDrive do not add a step there
	 *
	 * @return boolean false
	 */
	public function create_screen( $group_id = null ) {
		return false;
	}

	/**
	 * The create screen save method
	 *
	 * BuddyDrive do not have to handle this step
	 *
	 * @return boolean false
	 */
	public function create_screen_save( $group_id = null ) {
		return false;
	}

	/**
	 * Displays settings in front/backend group admin
	 *
	 * BuddyDrive do not add a step there
	 *
	 * @param object $group the group object sent by backend
	 * @uses bp_get_current_group_id() to get the group id
	 * @uses groups_get_groupmeta() to get the BuddyDrive option
	 * @uses checked() to activate/deactivate the checkbox
	 * @uses is_admin() to check if we're in WP backend
	 * @return string html output
	 */
	public function edit_screen( $group_id = null ) {

		$group_id = empty( $group_id ) ? bp_get_current_group_id() : $group_id;
		$checked  = groups_get_groupmeta( $group_id, '_buddydrive_enabled' );
		?>

		<h4><?php echo esc_attr( $this->name ) ?> <?php _e( 'settings', 'buddydrive' );?></h4>

		<fieldset>
			<legend class="screen-reader-text"><?php echo esc_attr( $this->name ) ?> <?php _e( 'settings', 'buddydrive' );?></legend>
			<p><?php _e( 'Allow members of this group to share their folders or files.', 'buddydrive' ); ?></p>

			<div class="field-group">
				<div class="checkbox">
					<label><input type="checkbox" name="_group_buddydrive_activate" value="1" <?php checked( $checked )?>> <?php printf( __( 'Activate %s', 'buddydrive' ), $this->name );?></label>
				</div>
			</div>

			<?php if ( ! is_admin() ) : ?>
				<input type="submit" name="save" value="<?php _e( 'Save', 'buddydrive' );?>" />
			<?php endif; ?>

		</fieldset>

		<?php
		wp_nonce_field( 'groups_edit_save_' . $this->slug, 'buddydrive_group_admin' );
	}

	/**
	 * Save the settings of the group
	 *
	 * @param  integer $group_id the group id we save settings for
	 * @uses check_admin_referer() for security reasons
	 * @uses bp_get_current_group_id() to get the group id
	 * @uses groups_update_groupmeta() to set the BuddyDrive option if needed
	 * @uses groups_delete_groupmeta() to delete the BuddyDrive option if needed
	 * @uses buddydrive_remove_buddyfiles_from_group() to eventually remove attached BuddyDrive items
	 * @uses is_admin() to check if we're in WP backend
	 * @uses bp_core_add_message() to inform about success / error
	 * @uses bp_core_redirect() to avoid some refreshing stuff
	 * @uses bp_get_group_permalink() to redirect to
	 */
	public function edit_screen_save( $group_id = null ) {

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
			return false;

		check_admin_referer( 'groups_edit_save_' . $this->slug, 'buddydrive_group_admin' );

		$group_id = ! empty( $group_id ) ? $group_id : bp_get_current_group_id();

		/* Insert your edit screen save code here */
		$buddydrive_ok = ! empty( $_POST['_group_buddydrive_activate'] ) ? $_POST['_group_buddydrive_activate'] : false ;

		if( ! empty( $buddydrive_ok ) ){
			$success = groups_update_groupmeta( $group_id, '_buddydrive_enabled', $buddydrive_ok );
		} else {
			$success = groups_delete_groupmeta( $group_id, '_buddydrive_enabled' );

			// we need to remove folders and items attached to this group in this case
			buddydrive_remove_buddyfiles_from_group( $group_id );
		}

		if ( ! is_admin() ) {
			/* To post an error/success message to the screen, use the following */
			if ( !$success )
				bp_core_add_message( __( 'There was an error saving, please try again', 'buddydrive' ), 'error' );
			else
				bp_core_add_message( __( 'Settings saved successfully', 'buddydrive' ) );

			bp_core_redirect( bp_get_group_permalink( buddypress()->groups->current_group ) . 'admin/' . $this->slug );
		}

	}

	/**
	 * Displays the form into the Group Admin Meta Box
	 *
	 * @since version 1.1
	 *
	 * @param  integer $group_id group id
	 * @uses  BuddyDrive_Group::edit_screen() to output the form
	 */
	public function admin_screen( $group_id = null ) {
		$this->edit_screen( $group_id );
	}

	/**
	 * Saves the settings from the Group Admin Meta Box
	 *
	 * @since version 1.1
	 *
	 * @param integer $group_id the group id
	 * @uses BuddyDrive_Group::edit_screen_save() to save the settings
	 */
	public function admin_screen_save( $group_id = null ) {
		$this->edit_screen_save( $group_id );
	}

	/**
	 * Displays the BuddyDrive of the group
	 *
	 * @return string html output
	 */
	public function display( $group_id = null ) {
		$current_group = groups_get_current_group();

		if ( ( 'public' !== $this->visibility || 'public' !== $current_group->status ) && ! groups_is_user_member( bp_loggedin_user_id(), $current_group->id ) && ! bp_current_user_can( 'bp_moderate' ) ) {
			printf( '<div id="message" class="info"><p>%s</p></div>', esc_html__( 'You must be a member of this group to view the files.', 'buddydrive' ) );
			return;
		}

		if ( buddydrive_use_deprecated_ui() ) {
			buddydrive_group_deprecated_display( bp_get_current_group_id() );
		} else {
			buddydrive_ui();
		}
	}

	/**
	 * We do not use widgets
	 *
	 * @return boolean false
	 */
	public function widget_display() {
		return false;
	}

	/**
	 * Loads the BuddyDrive navigation if group admin activated BuddyDrive
	 *
	 * @uses bp_get_current_group_id() to get the group id
	 * @uses groups_get_groupmeta() to get the BuddyDrive option
	 * @return boolean true or false
	 */
	public function enable_nav_item() {
		$retval   = false;
		$group_id = bp_get_current_group_id();

		if ( empty( $group_id ) ) {
			return $retval;
		}

		if ( groups_get_groupmeta( $group_id, '_buddydrive_enabled' ) ) {
			$retval = true;
		}

		return (bool) apply_filters( 'buddydrive_group_enable_nav_item', $retval, $group_id );
	}
}

/**
 * Waits for bp_init hook before loading the group extension
 *
 * Let's make sure the group id is defined before loading our stuff
 *
 * @since 1.1.1
 *
 * @uses bp_register_group_extension() to register the group extension
 */
function buddydrive_register_group_extension() {
	bp_register_group_extension( 'BuddyDrive_Group' );
}

add_action( 'bp_init', 'buddydrive_register_group_extension' );

endif;
