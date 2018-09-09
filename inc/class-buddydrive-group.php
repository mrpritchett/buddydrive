<?php
/**
 * BuddyDrive Group
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
/**
 * The bp_is_active( 'groups' ) check is recommended, to prevent problems
 * during upgrade or when the Groups component is disabled
 */
if ( bp_is_active( 'groups' ) ) :

class BuddyDrive_Group extends BP_Group_Extension {
	/**
	 * Here you can see more customization of the config options
	 */
	function __construct() {
		$args = array(
			'slug' => 'buddydrive',
			'name' => 'BuddyDrive',
			'nav_item_position' => 105,
		);
		parent::init( $args );
	}

	/**
	 * display() contains the markup that will be displayed on the main
	 * plugin tab
	 */
	function display( $group_id = NULL ) {
		$group_id = bp_get_group_id();
		echo 'What a cool plugin!';
	}

	/**
	 * settings_screen() is the catch-all method for displaying the content
	 * of the edit, create, and Dashboard admin panels
	 */
	function settings_screen( $group_id = NULL ) {
		$setting = groups_get_groupmeta( $group_id, 'group_extension_example_1_setting' );

		?>
		Save your plugin setting here: <input type="text" name="group_extension_example_1_setting" value="<?php echo esc_attr( $setting ) ?>" />
		<?php
	}

	/**
	 * settings_sceren_save() contains the catch-all logic for saving
	 * settings from the edit, create, and Dashboard admin panels
	 */
	function settings_screen_save( $group_id = NULL ) {
		$setting = '';

		if ( isset( $_POST['group_extension_example_1_setting'] ) ) {
			$setting = $_POST['group_extension_example_1_setting'];
		}

		groups_update_groupmeta( $group_id, 'group_extension_example_1_setting', $setting );

	}

}

/**
 * Waits for bp_init hook before loading the BuddyDrive Group
 *
 * @since 3.0.0
 *
 * @uses bp_register_group_extension() to register the group extension
 */
function buddydrive_register_group_extension() {
	bp_register_group_extension( 'BuddyDrive_Group' );
}
add_action( 'bp_init', 'buddydrive_register_group_extension' );

endif;
