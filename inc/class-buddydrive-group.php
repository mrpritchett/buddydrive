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

	function display( $group_id = NULL ) {
		$group_id = bp_get_group_id();
		echo 'This plugin is 2x cooler!';
	}

	function settings_screen( $group_id = NULL ) {
		$setting = groups_get_groupmeta( $group_id, 'group_extension_example_2_setting' );

		?>
		Save your plugin setting here: <input type="text" name="group_extension_example_2_setting" value="<?php echo esc_attr( $setting ) ?>" />
		<?php
	}

	function settings_screen_save( $group_id = NULL ) {
		$setting = isset( $_POST['group_extension_example_2_setting'] ) ? $_POST['group_extension_example_2_setting'] : '';
		groups_update_groupmeta( $group_id, 'group_extension_example_2_setting', $setting );
	}

	/**
	 * create_screen() is an optional method that, when present, will
	 * be used instead of settings_screen() in the context of group
	 * creation.
	 *
	 * Similar overrides exist via the following methods:
	 *   * create_screen_save()
	 *   * edit_screen()
	 *   * edit_screen_save()
	 *   * admin_screen()
	 *   * admin_screen_save()
	 */
	function create_screen( $group_id = NULL ) {
		$setting = groups_get_groupmeta( $group_id, 'group_extension_example_2_setting' );

		?>
		Welcome to your new group! You are neat.
		Save your plugin setting here: <input type="text" name="group_extension_example_2_setting" value="<?php echo esc_attr( $setting ) ?>" />
		<?php
	}

}
bp_register_group_extension( 'BuddyDrive_Group' );

endif;
