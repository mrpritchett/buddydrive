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
	 	$this->actions();
	}

	/**
	 * set some actions
	 *
	 *
	 * @package BuddyDrive
	 * @since 1.2.0
	 */
	private function actions() {

		buddypress()->active_components[$this->id] = '1';

		/**
		 * Register the BuddyDrive custom post types
		 */
		if ( get_current_blog_id() == bp_get_root_blog_id() ) {
			add_action( 'init', array( &$this, 'register_post_types' ), 9 );

			// Register the BuddyDrive upload dir
			add_action( 'bp_init', array( $this, 'register_upload_dir' ) );
		}

		// register the embed handler
		add_action( 'bp_init', array( $this, 'register_embed_code' ), 4 );
	}

	/**
	 * BuddyDrive needed files
	 *
	 * @package BuddyDrive
	 * @since 1.0
	 *
	 * @uses bp_is_active() to check if group component is active
	 */
	public function includes( $includes = array() ) {

		// Files to include
		$includes = array(
			'buddydrive-item-filters.php',
			'buddydrive-item-actions.php',
			'buddydrive-item-screens.php',
			'buddydrive-item-classes.php',
			'buddydrive-item-functions.php',
			'buddydrive-item-template.php',
			'buddydrive-item-ajax.php',
		);

		if ( bp_is_active( 'groups' ) ) {
			$includes[] = 'buddydrive-group-class.php';
		}

		if ( buddydrive_use_deprecated_ui() ) {
			$includes[] = 'buddydrive-item-deprecated.php';
		}

		parent::includes( $includes );
	}

	/**
	 * Set up BuddyDrive globals
	 *
	 * @package BuddyDrive
	 * @since 1.0
	 *
	 * @global obj $bp BuddyPress's global object
	 * @uses buddypress() to get the instance data
	 * @uses buddydrive_get_slug() to get BuddyDrive root slug
	 */
	public function setup_globals( $args = array() ) {
		$bp = buddypress();

		// Set up the $globals array to be passed along to parent::setup_globals()
		$globals = array(
			'slug'                  => buddydrive_get_slug(),
			'root_slug'             => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug : buddydrive_get_slug(),
			'has_directory'         => true,
			'directory_title'       => sprintf( __( '%s download page', 'buddydrive' ), buddydrive_get_name() ),
			'notification_callback' => 'buddydrive_format_notifications',
			'search_string'         => __( 'Search files...', 'buddydrive' )
		);

		// Let BP_Component::setup_globals() do its work.
		parent::setup_globals( $globals );
	}

	/**
	 * Set up buddydrive navigation.
	 *
	 * @uses buddypress() to get the instance data
	 * @uses buddydrive_get_name() to get BuddyDrive name
	 * @uses buddydrive_get_slug() to get BuddyDrive slug
	 * @uses bp_displayed_user_id() to get the displayed user id
	 * @uses bp_displayed_user_domain() to get displayed user profile link
	 * @uses bp_loggedin_user_domain() to get current user profile link
	 * @uses bp_is_active() to check if the friends component is active
	 * @uses buddydrive_get_user_subnav_name() to get main subnav name
	 * @uses buddydrive_get_friends_subnav_name() to get friends subnav name
	 * @uses buddydrive_get_friends_subnav_slug() to get friends subnav slug
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
		$bp =  buddypress();

		$nav = buddydrive_get_name();

		// Only show count on older UI
		if ( bp_is_my_profile() && buddydrive_use_deprecated_ui() ) {
			$nav = sprintf( __( '%s <span class="count">%d</span>', 'buddydrive' ), buddydrive_get_name(), buddydrive_count_user_files() );
		}

		$main_nav = array(
			'name' 		          => $nav,
			'slug' 		          => buddydrive_get_slug(),
			'position' 	          => 80,
			'screen_function'     => array( 'BuddyDrive_Screens', 'user_files' ),
			'default_subnav_slug' => 'files',
		);
		$displayed_user_id = bp_displayed_user_id();
		$user_domain = ( ! empty( $displayed_user_id ) ) ? bp_displayed_user_domain() : bp_loggedin_user_domain();

		$buddydrive_link = trailingslashit( $user_domain . buddydrive_get_slug() );

		// Add a few subnav items under the main Example tab
		$sub_nav[] = array(
			'name'            => buddydrive_get_user_subnav_name(),
			'slug'            => 'files',
			'parent_url'      => $buddydrive_link,
			'parent_slug'     => $this->slug,
			'screen_function' => array( 'BuddyDrive_Screens', 'user_files' ),
			'position'        => 10,
		);

		// Add the subnav items to the friends nav item
		if ( bp_is_active( 'friends' ) && bp_displayed_user_id() == bp_loggedin_user_id() ) {
			$sub_nav[] = array(
				'name'            => buddydrive_get_friends_subnav_name(),
				'slug'            => buddydrive_get_friends_subnav_slug(),
				'parent_url'      => $buddydrive_link,
				'parent_slug'     => $this->slug,
				'screen_function' => array( 'BuddyDrive_Screens', 'friends_files' ),
				'position'        => 20,
			);
		}

		if ( ! buddydrive_use_deprecated_ui() && bp_is_my_profile() ) {
			$sub_nav[] = array(
				'name'            => __( 'Between Members', 'buddydrive' ),
				'slug'            => 'members',
				'parent_url'      => $buddydrive_link,
				'parent_slug'     => $this->slug,
				'screen_function' => array( 'BuddyDrive_Screens', 'user_files' ),
				'position'        => 30,
			);
		}

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Builds the user's navigation in WP Admin Bar
	 *
	 * @uses buddydrive_get_slug() to get BuddyDrive slug
	 * @uses is_user_logged_in() to check if the user is logged in
	 * @uses bp_loggedin_user_domain() to get current user's profile link
	 * @uses buddydrive_get_name() to get BuddyDrive plugin name
	 * @uses buddydrive_get_user_subnav_name() to get main subnav name
	 * @uses buddydrive_get_friends_subnav_name() to get friends subnav name
	 * @uses buddydrive_get_friends_subnav_slug() to get friends subnav slug
	 * @uses bp_is_active() to check for the friends component
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {

		// Prevent debug notices
		$wp_admin_nav = array();
		$buddydrive_slug = buddydrive_get_slug();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$buddydrive_link = trailingslashit( bp_loggedin_user_domain() . $buddydrive_slug );

			// Add main BuddyDrive menu
			$wp_admin_nav[] = array(
				'parent' => 'my-account-buddypress',
				'id'     => 'my-account-' . $buddydrive_slug,
				'title'  => buddydrive_get_name(),
				'href'   => trailingslashit( $buddydrive_link )
			);

			// Add BuddyDrive submenu
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $buddydrive_slug,
				'id'     => 'my-account-' . $buddydrive_slug .'-files',
				'title'  => buddydrive_get_user_subnav_name(),
				'href'   => trailingslashit( $buddydrive_link )
			);

			if ( bp_is_active('friends') ) {
				// Add shared by friends BuddyDrive submenu
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $buddydrive_slug,
					'id'     => 'my-account-' . $buddydrive_slug .'-friends',
					'title'  => buddydrive_get_friends_subnav_name(),
					'href'   => trailingslashit( $buddydrive_link . buddydrive_get_friends_subnav_slug() )
				);
			}

			if ( ! buddydrive_use_deprecated_ui() ) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $buddydrive_slug,
					'id'     => 'my-account-' . $buddydrive_slug .'-members',
					'title'  => __( 'Between Members', 'buddydrive' ),
					'href'   => trailingslashit( $buddydrive_link . 'members' )
				);
			}
		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * registering BuddyDrive custom post types
	 *
	 * @uses buddydrive_get_folder_post_type() to get the BuddyFolder post type
 	 * @uses buddydrive_get_file_post_type() to get the BuddyFile post type
 	 * @uses register_post_type() to register the post type
	 */
	public function register_post_types() {

		// Set up some labels for the post type
		$labels_file = array(
			'name'	             => __( 'BuddyFiles', 'buddydrive' ),
			'singular'           => __( 'BuddyFile', 'buddydrive' ),
			'menu_name'          => __( 'BuddyDrive Files', 'buddydrive' ),
			'all_items'          => __( 'All BuddyFiles', 'buddydrive' ),
			'singular_name'      => __( 'BuddyFile', 'buddydrive' ),
			'add_new'            => __( 'Add New BuddyFile', 'buddydrive' ),
			'add_new_item'       => __( 'Add New BuddyFile', 'buddydrive' ),
			'edit_item'          => __( 'Edit BuddyFile', 'buddydrive' ),
			'new_item'           => __( 'New BuddyFile', 'buddydrive' ),
			'view_item'          => __( 'View BuddyFile', 'buddydrive' ),
			'search_items'       => __( 'Search BuddyFiles', 'buddydrive' ),
			'not_found'          => __( 'No BuddyFiles Found', 'buddydrive' ),
			'not_found_in_trash' => __( 'No BuddyFiles Found in Trash', 'buddydrive' )
		);

		$args_file = array(
			'label'	            => __( 'BuddyFile', 'buddydrive' ),
			'labels'            => $labels_file,
			'public'            => false,
			'rewrite'           => false,
			'show_ui'           => false,
			'show_in_admin_bar' => false,
			'supports'          => array( 'title', 'editor', 'author' )
		);

		// Register the post type for files.
		register_post_type( buddydrive_get_file_post_type(), $args_file );

		$labels_folder = array(
			'name'	             => __( 'BuddyFolders', 'buddydrive' ),
			'singular'           => __( 'BuddyFolder', 'buddydrive' ),
			'menu_name'          => __( 'BuddyDrive Folders', 'buddydrive' ),
			'all_items'          => __( 'All BuddyFolders', 'buddydrive' ),
			'singular_name'      => __( 'BuddyFolder', 'buddydrive' ),
			'add_new'            => __( 'Add New BuddyFolder', 'buddydrive' ),
			'add_new_item'       => __( 'Add New BuddyFolder', 'buddydrive' ),
			'edit_item'          => __( 'Edit BuddyFolder', 'buddydrive' ),
			'new_item'           => __( 'New BuddyFolder', 'buddydrive' ),
			'view_item'          => __( 'View BuddyFolder', 'buddydrive' ),
			'search_items'       => __( 'Search BuddyFolders', 'buddydrive' ),
			'not_found'          => __( 'No BuddyFolders Found', 'buddydrive' ),
			'not_found_in_trash' => __( 'No BuddyFolders Found in Trash', 'buddydrive' )
		);

		$args_folder = array(
			'label'	            => __( 'BuddyFolder', 'buddydrive' ),
			'labels'            => $labels_folder,
			'public'            => false,
			'rewrite'           => false,
			'show_ui'           => false,
			'show_in_admin_bar' => false,
			'supports'          => array( 'title', 'editor', 'author' )
		);

		// Register the post type for files.
		register_post_type( buddydrive_get_folder_post_type(), $args_folder );

		// Register BuddyDrive post status
		foreach ( (array) buddydrive_get_stati() as $status_id => $status_args ) {
			register_post_status( $status_id, $status_args );
		}

		parent::register_post_types();
	}


	/**
	 * register the BuddyDrive upload data in instance
	 *
	 * @uses buddydrive_get_upload_data() to get the specific BuddyDrive upload datas
	 */
	public function register_upload_dir() {
		$upload_data = buddydrive_get_upload_data();

		if ( is_array( $upload_data ) ) {
			buddydrive()->upload_dir = $upload_data['dir'];
			buddydrive()->upload_url = $upload_data['url'];
			buddydrive()->thumbdir   = $upload_data['thumbdir'];
			buddydrive()->thumburl   = $upload_data['thumburl'];
		}

	}

	/**
	 * Registers BuddyDrive embed code
	 *
	 * We need to wait for buddypress()->pages to be set
	 *
	 * @since BuddyDrive 1.1
	 *
	 * @uses wp_embed_register_handler() registers the embed code for BuddyDrive
	 */
	public function register_embed_code() {
		wp_embed_register_handler( 'buddydrive', '#'.buddydrive_get_root_url().'\/(.+?)\/(.+?)\/#i', 'wp_embed_handler_buddydrive' );
	}
}

/**
 * Finally Loads the component into the $bp global
 *
 * @uses buddypress()
 */
function buddydrive_load_component() {
	buddypress()->buddydrive = new BuddyDrive_Component;
}
add_action( 'bp_loaded', 'buddydrive_load_component' );
