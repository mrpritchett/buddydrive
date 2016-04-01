<?php
/**
 * @group item_template
 */
class BuddyDrive_Item_Template_Tests extends BuddyDrive_TestCase {

	public function setUp() {
		parent::setUp();

		$this->current_user = get_current_user_id();
		$this->user_id      = $this->factory->user->create();
		$this->set_current_user( $this->user_id );
		$this->groups = array();
	}

	public function tearDown() {
		parent::tearDown();

		$this->set_current_user( $this->current_user );
	}

	public function catch_groups( $output, $groups ) {
		$this->groups = $groups;
		return $output;
	}

	public function restrict_groups( $groups, $user_id ) {
		foreach ( $groups as $g => $group ) {
			if ( ! groups_is_user_admin( $user_id, $group->id ) && ! groups_is_user_mod( $user_id, $group->id ) ) {
				unset( $groups[ $g ] );
			}
		}
		return $groups;
	}

	/**
	 * @group groups
	 */
	public function test_buddydrive_get_select_user_group() {
		$c  = $this->factory->user->create();
		$g1 = $this->factory->group->create( array( 'creator_id' => $c ) );
		groups_join_group( $g1, bp_loggedin_user_id() );
		groups_update_groupmeta( $g1, '_buddydrive_enabled', 1 );

		$g2 = $this->factory->group->create( array( 'status' => 'hidden', 'creator_id' => $c  ) );
		groups_join_group( $g2, bp_loggedin_user_id() );
		groups_update_groupmeta( $g2, '_buddydrive_enabled', 1 );

		$g3 = $this->factory->group->create( array( 'creator_id' => $c ) );
		groups_update_groupmeta( $g3, '_buddydrive_enabled', 1 );

		$g4 = $this->factory->group->create( array( 'creator_id' => bp_loggedin_user_id() ) );

		add_filter( 'buddydrive_get_select_user_group', array( $this, 'catch_groups' ), 10, 2 );

		buddydrive_get_select_user_group();

		$this->assertSame( array( $g1, $g2 ), array_map( 'intval', wp_list_pluck( $this->groups, 'id' ) ) );

		remove_filter( 'buddydrive_get_select_user_group', array( $this, 'catch_groups' ), 10, 2 );

		$this->groups = array();
	}

	/**
	 * @group groups
	 * @group filter
	 */
	public function test_buddydrive_filter_select_user_group() {
		$c  = $this->factory->user->create();
		$g1 = $this->factory->group->create( array( 'creator_id' => $c ) );
		groups_join_group( $g1, bp_loggedin_user_id() );
		groups_update_groupmeta( $g1, '_buddydrive_enabled', 1 );

		$g2 = $this->factory->group->create( array( 'creator_id' => bp_loggedin_user_id() ) );
		groups_update_groupmeta( $g2, '_buddydrive_enabled', 1 );

		add_filter( 'buddydrive_get_select_user_group', array( $this, 'catch_groups' ), 10, 2 );
		add_filter( 'buddydrive_filter_select_user_group', array( $this, 'restrict_groups' ), 10, 2 );

		buddydrive_get_select_user_group();

		$this->assertSame( array( $g2 ), array_map( 'intval', array_values( wp_list_pluck( $this->groups, 'id' ) ) ) );

		remove_filter( 'buddydrive_filter_select_user_group', array( $this, 'restrict_groups' ), 10, 2 );
		remove_filter( 'buddydrive_get_select_user_group', array( $this, 'catch_groups' ), 10, 2 );
	}
}
