<?php
/**
 * BuddyDrive Component
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main BuddyDrive Component Class
 *
 * Inspired by BuddyPress skeleton component
 */
class BuddyDrive_Component extends BP_Component {

	/**
	 * Constructor method
	 *
	 *
	 * @package BuddyDrive
	 * @since 1.0
	 */
	public function __construct() {

		parent::start(
			buddydrive_get_slug(),
			buddydrive_get_name(),
			buddydrive_get_includes_dir()
		);

		 $this->includes();

		/**
		 * Put your component into the active components array, so that bp_is_active( 'example' );
		 * returns true when appropriate. We have to do this manually, because non-core
		 * components are not saved as active components in the database.
		 */
		$bp->active_components[$this->id] = '1';
	}

	/**
	 * BuddyDrive needed files
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 *
	 * @uses bp_is_active() to check if group component is active
	 */
	public function includes( $includes = array() ) {

		// Files to include
		$includes = array(
			'class-buddydrive-profile.php',
		);

		if ( bp_is_active( 'groups' ) ) {
			$includes[] = 'class-buddydrive-group.php';
		}

		parent::includes( $includes );
	}

}

/**
 * Finally Loads the component into the $bp global
 *
 * @uses buddypress()
 */
function buddydrive_load_component() {
	global $bp;

	$bp->buddydrive = new BuddyDrive_Component;
}
add_action( 'bp_loaded', 'buddydrive_load_component' );
