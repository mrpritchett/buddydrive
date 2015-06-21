<?php

/**
 * Requires BuddyPress factory
 */
if ( class_exists( 'BP_UnitTest_Factory' ) ) :
class BuddyDrive_UnitTest_Factory extends BP_UnitTest_Factory {

	function __construct() {
		parent::__construct();
	}
}
else :

die( 'The BP_UnitTest_Factory class does not exist' );

endif;
