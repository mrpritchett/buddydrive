<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'BuddyDrive_Admin' ) ) :
/**
 * Loads BuddyDrive plugin admin area
 *
 * Inspired by bbPress 2.3
 *
 * @package BuddyDrive
 * @subpackage Admin
 * @since version (1.0)
 */
class BuddyDrive_Admin {

	/** Directory *************************************************************/

	/**
	 * @var string Path to the BuddyDrive admin directory
	 */
	public $admin_dir = '';

	/** URLs ******************************************************************/

	/**
	 * @var string URL to the BuddyDrive admin directory
	 */
	public $admin_url = '';

	/**
	 * @var string URL to the BuddyDrive admin styles directory
	 */
	public $styles_url = '';

	/**
	 * @var string URL to the BuddyDrive admin script directory
	 */
	public $js_url = '';

	/**
	 * @var the BuddyDrive settings page for admin or network admin
	 */
	public $settings_page ='';

	/**
	 * @var the notice hook depending on config (multisite or not)
	 */
	public $notice_hook = '';

	/**
	 * @var the user columns filter depending on config (multisite or not)
	 */
	public $user_columns_filter = '';

	/**
	 * @var the BuddyDrive hook_suffixes to eventually load script
	 */
	public $hook_suffixes = array();


	/** Functions *************************************************************/

	/**
	 * The main BuddyDrive admin loader
	 *
	 * @since version (1.0)
	 *
	 * @uses BuddyDrive_Admin::setup_globals() Setup the globals needed
	 * @uses BuddyDrive_Admin::includes() Include the required files
	 * @uses BuddyDrive_Admin::setup_actions() Setup the hooks and actions
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Admin globals
	 *
	 * @since version (1.0)
	 * @access private
	 *
	 * @uses buddydrive() to get some globals of plugin instance
	 * @uses bp_core_do_network_admin() to define the best menu (network)
	 */
	private function setup_globals() {
		$buddydrive = buddydrive();
		$this->admin_dir            = trailingslashit( $buddydrive->includes_dir . 'admin'  ); // Admin path
		$this->admin_url            = trailingslashit( $buddydrive->includes_url . 'admin'  ); // Admin url
		$this->styles_url           = trailingslashit( $this->admin_url   . 'css' ); // Admin styles URL*/
		$this->js_url               = trailingslashit( $this->admin_url   . 'js' );
		$this->settings_page        = 'options-general.php';
		$this->notice_hook          = 'admin_notices' ;
		$this->user_columns_filter  = 'manage_users_columns';
		$this->requires_db_upgrade  = buddydrive_get_db_number_version() < buddydrive_get_number_version();

		if ( bp_core_do_network_admin() ) {
			$this->settings_page       = 'settings.php';
			$this->notice_hook         = 'network_admin_notices';
			$this->user_columns_filter = 'wpmu_users_columns';
			$this->buddydrive_page     = esc_url( add_query_arg( 'page','buddydrive-files', network_admin_url( 'admin.php' ) ) );
		} else {
			$this->buddydrive_page     = esc_url( add_query_arg( 'page','buddydrive-files', admin_url( 'admin.php' ) ) );
		}

		// We are now using a BackBone UI
		$this->items_admin_callback = array( $this, 'items_admin_screen' );

		/**
		 * Use add_filter( 'buddydrive_use_deprecated_ui', '__return_true' ); to use the deprecated UI
		 */
		if ( true === buddydrive_use_deprecated_ui() ) {
			$this->items_admin_callback = 'buddydrive_files_admin';
		}
	}

	/**
	 * Include required files
	 *
	 * @since version (1.0)
	 * @access private
	 */
	private function includes() {
		require( $this->admin_dir . 'buddydrive-settings.php'  );

		/**
		 * Use add_filter( 'buddydrive_use_deprecated_ui', '__return_true' ); to use the deprecated UI
		 */
		if ( true === buddydrive_use_deprecated_ui() ) {
			require( $this->admin_dir . 'buddydrive-items.php'  );
		}
	}

	/**
	 * Setup the admin hooks, actions and filters
	 *
	 * @since version (1.0)
	 * @access private
	 *
	 * @uses add_action() To add various actions
	 * @uses bp_core_admin_hook() to hook the right menu (network or not)
	 * @uses add_filter() To add various filters
	 */
	private function setup_actions() {
		// Bail if config does not match what we need
		if ( buddydrive::bail() )
			return;

		/** General Actions ***************************************************/

		add_action( bp_core_admin_hook(),                 array( $this, 'admin_menus'             )        ); // Add menu item to settings menu
		add_action( 'buddydrive_admin_head',              array( $this, 'admin_head'              )        ); // Add some general styling to the admin area
		add_action( $this->notice_hook,                   array( $this, 'activation_notice'       ),     9 ); // Checks for BuddyDrive Upload directory once activated
		add_action( 'buddydrive_admin_register_settings', array( $this, 'register_admin_settings' )        ); // Add settings
		add_action( 'admin_enqueue_scripts',              array( $this, 'enqueue_scripts'         ), 10, 1 ); // Add enqueued JS and CSS
		add_action( 'wp_ajax_buddydrive_upgrader',        array( $this, 'do_upgrade'              )        );

		/** Filters ***********************************************************/

		// Modify BuddyDrive's admin links
		add_filter( 'plugin_action_links',               array( $this, 'modify_plugin_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'modify_plugin_action_links' ), 10, 2 );

		if ( ! buddydrive_use_deprecated_ui() ) {
			add_filter( 'bp_admin_menu_order', array( $this, 'items_admin_menu_order' ), 10, 1 );
		}

		// Allow plugins to modify these actions
		do_action_ref_array( 'buddydrive_admin_loaded', array( &$this ) );
	}

	/**
	 * Builds BuddyDrive admin menus
	 *
	 * @uses bp_current_user_can() to check for user's capability
	 * @uses add_submenu_page() to add the settings page
	 * @uses add_menu_page() to add the admin area for BuddyDrive items
	 * @uses add_dashboard_page() to add the BuddyDrive Welcome Screen
	 */
	public function admin_menus() {

		// Bail if user cannot manage options
		if ( ! bp_current_user_can( 'manage_options' ) )
			return;


		$this->hook_suffixes[] = add_submenu_page(
			$this->settings_page,
			_x( 'BuddyDrive', 'BuddyDrive Settings page title', 'buddydrive' ),
			_x( 'BuddyDrive', 'BuddyDrive Settings menu title', 'buddydrive' ),
			'manage_options',
			'buddydrive',
			'buddydrive_admin_settings'
		);

		$hook = add_menu_page(
			_x( 'BuddyDrive', 'BuddyDrive User Files Admin page title', 'buddydrive' ),
			_x( 'BuddyDrive', 'BuddyDrive User Files Admin menu title',  'buddydrive' ),
			'manage_options',
			'buddydrive-files',
			$this->items_admin_callback,
			'div'
		);

		$this->hook_suffixes[] = $hook;

		// About
		$this->hook_suffixes[] = add_dashboard_page(
			__( 'Welcome to BuddyDrive',  'buddydrive' ),
			__( 'Welcome to BuddyDrive',  'buddydrive' ),
			'manage_options',
			'buddydrive-about',
			array( $this, 'about_screen' )
		);

		// Upgrade DB Screen
		if ( $this->requires_db_upgrade ) {
			$this->hook_suffixes['upgrade'] = add_dashboard_page(
				__( 'BuddyDrive Upgrades',  'buddydrive' ),
				__( 'BuddyDrive Upgrades',  'buddydrive' ),
				'manage_options',
				'buddydrive-upgrade',
				array( $this, 'upgrade_screen' )
			);
		}


		/**
		 * Use add_filter( 'buddydrive_use_deprecated_ui', '__return_true' ); to use the deprecated UI
		 */
		if ( true === buddydrive_use_deprecated_ui() ) {
			// Hook into early actions to load custom CSS and our init handler.
			add_action( "load-$hook", 'buddydrive_files_admin_load' );
		}

		// Putting user edit hooks there, this way we're sure they will load at the right place
		add_action( 'edit_user_profile',          array( $this, 'edit_user_quota'           ), 10, 1 );
		add_action( 'edit_user_profile_update',   array( $this, 'save_user_quota'           ), 10, 1 );
		add_action( 'set_user_role',              array( $this, 'update_user_quota_to_role' ), 10, 2 );

		add_filter( $this->user_columns_filter,   array( $this, 'user_quota_column' )        );
		add_filter( 'manage_users_custom_column', array( $this, 'user_quota_row'    ), 10, 3 );

		if( is_multisite() ) {
			$hook_settings = $this->hook_suffixes[0];
			add_action( "load-$hook_settings", array( $this, 'multisite_upload_trick' ) );
		}

	}

	/**
	 * Loads some common css and hides the BuddyDrive about submenu
	 *
	 * @uses remove_submenu_page() to remove the BuddyDrive About submenu
	 */
	public function admin_head() {

		// Hide About page
		remove_submenu_page( 'index.php', 'buddydrive-about'   );

		if ( $this->requires_db_upgrade ) {
			remove_submenu_page( 'index.php', 'buddydrive-upgrade' );
		}

		$version = buddydrive_get_version();

		?>

		<style type="text/css" media="screen">
		/*<![CDATA[*/

			@font-face {
				font-family: 'buddydrive-dashicons';
				src: url(data:application/x-font-ttf;charset=utf-8;base64,AAEAAAALAIAAAwAwT1MvMg6R3isAAAC8AAAAYGNtYXAwVKBZAAABHAAAAExnYXNwAAAAEAAAAWgAAAAIZ2x5ZjIALEUAAAFwAAAAjGhlYWQBiNyzAAAB/AAAADZoaGVhB+8ETgAAAjQAAAAkaG10eAaIAGkAAAJYAAAAFGxvY2EAKABaAAACbAAAAAxtYXhwAAkAGAAAAngAAAAgbmFtZbVAQzcAAAKYAAABS3Bvc3QAAwAAAAAD5AAAACAAAwQAAZAABQAAApkCzAAAAI8CmQLMAAAB6wAzAQkAAAAAAAAAAAAAAAAAAAABAQAAAAAAAAAAAAAAAAAAAABAAADQAQPA/8D/wAPAAEAAAAABAAAAAAAAAAAAAAAgAAAAAAACAAAAAwAAABQAAwABAAAAFAAEADgAAAAKAAgAAgACAAEAINAB//3//wAAAAAAINAB//3//wAB/+MwAwADAAEAAAAAAAAAAAAAAAEAAf//AA8AAQAAAAAAAAAAAAIAADc5AQAAAAABAAAAAAAAAAAAAgAANzkBAAAAAAEAAAAAAAAAAAACAAA3OQEAAAAAAwBpAFoELQMuAAwAEQAVAAAlITI+AjUhFB4CMyUzFSM1EyEDIQEdAlsmQjEc/DwcMUIlAls9PXn8tDwDxFocMkEmJkEyHHk9PQJb/h0AAAABAAAAAQAA0YB/9l8PPPUACwQAAAAAAM8ezEQAAAAAzx7MRAAAAAAELQMuAAAACAACAAAAAAAAAAEAAAPA/8AAAASIAAAAAAQtAAEAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAgAAAASIAGkAAAAAAAoAFAAeAEYAAQAAAAUAFgADAAAAAAACAAAAAAAAAAAAAAAAAAAAAAAAAA4ArgABAAAAAAABABIAAAABAAAAAAACAA4AVQABAAAAAAADABIAKAABAAAAAAAEABIAYwABAAAAAAAFABYAEgABAAAAAAAGAAkAOgABAAAAAAAKACgAdQADAAEECQABABIAAAADAAEECQACAA4AVQADAAEECQADABIAKAADAAEECQAEABIAYwADAAEECQAFABYAEgADAAEECQAGABIAQwADAAEECQAKACgAdQBkAGEAcwBoAGkAYwBvAG4AcwBWAGUAcgBzAGkAbwBuACAAMQAuADAAZABhAHMAaABpAGMAbwBuAHNkYXNoaWNvbnMAZABhAHMAaABpAGMAbwBuAHMAUgBlAGcAdQBsAGEAcgBkAGEAcwBoAGkAYwBvAG4AcwBHAGUAbgBlAHIAYQB0AGUAZAAgAGIAeQAgAEkAYwBvAE0AbwBvAG4AAAMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=) format('truetype'),
					 url(data:application/font-woff;charset=utf-8;base64,d09GRk9UVE8AAARoAAoAAAAABCAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAABDRkYgAAAA9AAAANoAAADacVIW4k9TLzIAAAHQAAAAYAAAAGAOkd4rY21hcAAAAjAAAABMAAAATDBUoFlnYXNwAAACfAAAAAgAAAAIAAAAEGhlYWQAAAKEAAAANgAAADYBiNyzaGhlYQAAArwAAAAkAAAAJAfvBE5obXR4AAAC4AAAABQAAAAUBogAaW1heHAAAAL0AAAABgAAAAYABVAAbmFtZQAAAvwAAAFLAAABS7VAQzdwb3N0AAAESAAAACAAAAAgAAMAAAEABAQAAQEBCmRhc2hpY29ucwABAgABADv4HAL4GwP4GAQeCgAJd/+Lix4KAAl3/4uLDAeLSxwEiPpUBR0AAAB9Dx0AAACCER0AAAAJHQAAANESAAYBAQoTFRcaH2Rhc2hpY29uc2Rhc2hpY29uc3UwdTF1MjB1RDAwMQAAAgGJAAMABQEBBAcKDUf+lA7+lA7+lA78lA73HPex5RX474sF74vc3IvvCP5YiwWLJ9w67osI+O/3DRXIi4tOTouLyAX3DfjvFf3gi0/8d/pYiwUO+pQU+pQViwwKAAAAAwQAAZAABQAAApkCzAAAAI8CmQLMAAAB6wAzAQkAAAAAAAAAAAAAAAAAAAABAQAAAAAAAAAAAAAAAAAAAABAAADQAQPA/8D/wAPAAEAAAAABAAAAAAAAAAAAAAAgAAAAAAACAAAAAwAAABQAAwABAAAAFAAEADgAAAAKAAgAAgACAAEAINAB//3//wAAAAAAINAB//3//wAB/+MwAwADAAEAAAAAAAAAAAAAAAEAAf//AA8AAQAAAAEAAJR3TYBfDzz1AAsEAAAAAADPHsxEAAAAAM8ezEQAAAAABC0DLgAAAAgAAgAAAAAAAAABAAADwP/AAAAEiAAAAAAELQABAAAAAAAAAAAAAAAAAAAABQAAAAAAAAAAAAAAAAIAAAAEiABpAABQAAAFAAAAAAAOAK4AAQAAAAAAAQASAAAAAQAAAAAAAgAOAFUAAQAAAAAAAwASACgAAQAAAAAABAASAGMAAQAAAAAABQAWABIAAQAAAAAABgAJADoAAQAAAAAACgAoAHUAAwABBAkAAQASAAAAAwABBAkAAgAOAFUAAwABBAkAAwASACgAAwABBAkABAASAGMAAwABBAkABQAWABIAAwABBAkABgASAEMAAwABBAkACgAoAHUAZABhAHMAaABpAGMAbwBuAHMAVgBlAHIAcwBpAG8AbgAgADEALgAwAGQAYQBzAGgAaQBjAG8AbgBzZGFzaGljb25zAGQAYQBzAGgAaQBjAG8AbgBzAFIAZQBnAHUAbABhAHIAZABhAHMAaABpAGMAbwBuAHMARwBlAG4AZQByAGEAdABlAGQAIABiAHkAIABJAGMAbwBNAG8AbwBuAAADAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA) format('woff');
				font-weight: normal;
				font-style: normal;
			}

			body.wp-admin #adminmenu .toplevel_page_buddydrive-files .wp-menu-image:before,
			body.wp-admin .buddydrive-profile-stats:before {
				font-family: 'buddydrive-dashicons';
				speak: none;
				font-style: normal;
				font-weight: normal;
				font-variant: normal;
				text-transform: none;
				line-height: 1;
				/* Better Font Rendering =========== */
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
				content:"\d001";
			}

			body.wp-admin .buddydrive-profile-stats:before {
				font-size: 18px;
				vertical-align: bottom;
				margin-right: 5px;
			}

			body.wp-admin #adminmenu .toplevel_page_buddydrive-files .wp-menu-image {
				content: "";
			}


			body.wp-admin .buddydrive-badge {
				font: normal 150px/1 'buddydrive-dashicons' !important;
				/* Better Font Rendering =========== */
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;

				color: #000;
				display: inline-block;
				content:'';
			}

			body.wp-admin .buddydrive-badge:before{
				content: "\d001";
			}

			.about-wrap .buddydrive-badge {
				position: absolute;
				top: 0;
				right: 0;
			}
				body.rtl .about-wrap .buddydrive-badge {
					right: auto;
					left: 0;
				}


		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Creates the upload dir and htaccess file
	 *
	 * @uses buddydrive_get_upload_data() to get BuddyDrive upload datas
	 * @uses wp_mkdir_p() to create the dir
	 * @uses insert_with_markers() to create the htaccess file
	 */
	public function activation_notice() {
		// we need to eventually create the upload dir and the .htaccess file
		$buddydrive_upload = buddydrive_get_upload_data();

		if ( empty( $buddydrive_upload['dir'] ) || ! file_exists( $buddydrive_upload['dir'] ) ){
			bp_core_add_admin_notice( __( 'The main BuddyDrive directory is missing', 'buddydrive' ) );
		}

		$display_upgrade_notice = true;
		$current_screen = get_current_screen();

		if ( isset( $current_screen->id ) && in_array( $current_screen->id, $this->hook_suffixes ) ) {
			$display_upgrade_notice = false;
		}

		if ( $this->requires_db_upgrade && $display_upgrade_notice ) {
			bp_core_add_admin_notice( sprintf(
				__( 'BuddyDrive is almost ready. It needs to update some of the datas it is using. If you have not done a database backup yet, please do it <strong>before</strong> clicking on <a href="%s">this link</a>.', 'buddydrive' ),
				esc_url( add_query_arg( array( 'page' => 'buddydrive-upgrade' ), bp_get_admin_url( 'index.php' ) ) )
			), 'error' );
		}
	}

	/**
	 * Registers admin settings for BuddyDrive
	 *
	 * @uses buddydrive_admin_get_settings_sections() to get the settings section
	 * @uses buddydrive_admin_get_settings_fields_for_section() to get the fields
	 * @uses bp_current_user_can() to check for user's capability
	 * @uses add_settings_section() to add the settings section
	 * @uses add_settings_field() to add the fields
	 * @uses register_setting() to fianlly register the settings
	 */
	public static function register_admin_settings() {

		// Bail if no sections available
		$sections = buddydrive_admin_get_settings_sections();

		if ( empty( $sections ) )
			return false;

		// Loop through sections
		foreach ( (array) $sections as $section_id => $section ) {

			// Only proceed if current user can see this section
			if ( ! bp_current_user_can( 'manage_options' ) )
				continue;

			// Only add section and fields if section has fields
			$fields = buddydrive_admin_get_settings_fields_for_section( $section_id );
			if ( empty( $fields ) )
				continue;

			// Add the section
			add_settings_section( $section_id, $section['title'], $section['callback'], $section['page'] );

			// Loop through fields for this section
			foreach ( (array) $fields as $field_id => $field ) {

				// Add the field
				add_settings_field( $field_id, $field['title'], $field['callback'], $section['page'], $section_id, $field['args'] );

				// Register the setting
				register_setting( $section['page'], $field_id, $field['sanitize_callback'] );
			}
		}
	}

	/**
	 * Eqnueues scripts and styles if needed
	 *
	 * @param  string $hook the WordPress admin page
	 * @uses wp_enqueue_style() to enqueue the style
	 * @uses wp_enqueue_script() to enqueue the script
	 */
	public function enqueue_scripts( $hook = false ) {
		if ( ! in_array( $hook, $this->hook_suffixes ) ) {
			return;
		}

		$min = '.min';
		if ( defined( 'SCRIPT_DEBUG' ) && true == SCRIPT_DEBUG )  {
			$min = '';
		}

		wp_enqueue_style( 'buddydrive-admin-css', $this->styles_url . "buddydrive-admin{$min}.css", array(), buddydrive_get_version() );

		if ( ! empty( $this->hook_suffixes[1] ) && $hook == $this->hook_suffixes[1] && ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
			/**
			 * Use add_filter( 'buddydrive_use_deprecated_ui', '__return_true' ); to use the deprecated UI
			 */
			if ( true === buddydrive_use_deprecated_ui() ) {
				wp_enqueue_script ( 'buddydrive-admin-js', $this->js_url .'buddydrive-admin.js' );
				wp_localize_script( 'buddydrive-admin-js', 'buddydrive_admin', buddydrive_get_js_l10n() );
			}
		}

		if ( isset( $this->hook_suffixes['upgrade'] ) && $hook === $this->hook_suffixes['upgrade'] ) {
			wp_register_script(
				'buddydrive-upgrader-js',
				$this->js_url . "buddydrive-upgrader{$min}.js",
				array( 'jquery', 'json2', 'wp-backbone' ),
				buddydrive_get_version(),
				true
			);
		}
	}

	/**
	 * Modifies the links in plugins table
	 *
	 * @param  array $links the existing links
	 * @param  string $file  the file of plugins
	 * @uses plugin_basename() to get the file name of BuddyDrive plugin
	 * @uses add_query_arg() to add args to the link
	 * @uses bp_get_admin_url() to build the new links
	 * @return array  the existing links + the new ones
	 */
	public function modify_plugin_action_links( $links, $file ) {

		// Return normal links if not BuddyPress
		if ( plugin_basename( buddydrive()->file ) != $file )
			return $links;

		// Add a few links to the existing links array
		return array_merge( $links, array(
			'settings' => '<a href="' . esc_url( add_query_arg( array( 'page' => 'buddydrive'       ), bp_get_admin_url( $this->settings_page ) ) ) . '">' . esc_html__( 'Settings', 'buddydrive' ) . '</a>',
			'about'    => '<a href="' . esc_url( add_query_arg( array( 'page' => 'buddydrive-about' ), bp_get_admin_url( 'index.php'          ) ) ) . '">' . esc_html__( 'About',    'buddydrive' ) . '</a>'
		) );
	}

	/**
	 * Displays the Welcome screen
	 *
	 * @uses buddydrive_get_version() to get the current version of the plugin
	 * @uses bp_get_admin_url() to build the url to settings page
	 * @uses add_query_arg() to add args to the url
	 */
	public function about_screen() {
		$display_version = buddydrive_get_version();
		$settings_url = add_query_arg( array( 'page' => 'buddydrive'), bp_get_admin_url( $this->settings_page ) );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'BuddyDrive %s', 'buddydrive' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for upgrading to the latest version of BuddyDrive! BuddyDrive %s is ready to manage the files and folders of your buddies!', 'buddydrive' ), $display_version ); ?></div>
			<div class="buddydrive-badge"></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url(  bp_get_admin_url( add_query_arg( array( 'page' => 'buddydrive-about' ), 'index.php' ) ) ); ?>">
					<?php _e( 'About', 'buddydrive' ); ?>
				</a>
			</h2>

			<div class="headline-feature feature-section one-col">
				<h2><?php _e( 'This is the new BuddyDrive User Interface', 'buddydrive' ); ?></h2>

				<div class="media-container" style="text-align:center">
					<img src="<?php echo esc_url( buddydrive_get_images_url() . '/buddydrive-ui.gif' );?>" alt="<?php esc_attr_e( 'The BuddyDrive Editor', 'buddydrive' ); ?>">
				</div>

				<div class="col" style="margin-right: auto;margin-left: auto; float: none">
					<h4><?php _e( 'The UI has been completely revamped and is bringing multiple file uploads!', 'buddydrive' ); ?></h4>
					<p><?php _e( 'Uploading files has never been so easy! Drag, drop it&#8217;s uploaded. The default privacy does not match your need? No worries, you can edit it at any time!', 'buddydrive' ); ?></p>
				</div>

				<div class="clear"></div>
			</div>

			<div class="feature-section two-col">
				<h2><?php _e( 'BuddyPress Groups integration Improvements', 'buddydrive' ); ?></h2>
				<div class="col">
					<img src="<?php echo buddydrive_get_plugin_url();?>/screenshot-1.png">
					<h3><?php _e( 'Share with multiple Groups', 'buddydrive' ); ?></h3>
					<p><?php _e( 'Have you ever uploaded the same file several times to share it with different groups? That was before! Now, you can attach a file to as many Group as you need.', 'buddydrive' ); ?></p>
				</div>
				<div class="col">
					<img src="<?php echo buddydrive_get_plugin_url();?>/screenshot-2.png">
					<h3><?php _e( 'Create new items directly from the Group.', 'buddydrive' ); ?></h3>
					<p><?php _e( 'Tired of going back to your profile to share items within a Group? That&#8217;s history! Now you can create folders and upload new files directly from the Group.', 'buddydrive' ); ?></p>
				</div>
			</div>

			<hr />

			<div class="feature-section two-col">
				<h2><?php _e( 'Sharing Improvements', 'buddydrive' ); ?></h2>
				<div class="col">
					<img src="<?php echo buddydrive_get_plugin_url();?>/screenshot-3.png">
					<h3><?php _e( 'The members of your choice!', 'buddydrive' ); ?></h3>
					<p><?php _e( 'Now you can restrict the access to your folders and files to the happy fiew you chose! Find all the files and folders the other shared with you into the new &quot;Between Members&quot; tab of your BuddyDrive.', 'buddydrive' ); ?></p>
				</div>
				<div class="col">
					<img src="<?php echo buddydrive_get_plugin_url();?>/screenshot-4.png">
					<h3><?php _e( 'Real shared folders', 'buddydrive' ); ?></h3>
					<p><?php _e( 'The user can access to your folder? Now he can also add new files in it. The folder owner still has the last word and will be able to remove all added files.', 'buddydrive' ); ?></p>
				</div>
			</div>

			<hr />

			<div class="changelog">
				<h2><?php printf( __( 'The other improvements in %s', 'buddydrive' ), $display_version ); ?></h2>

				<div class="under-the-hood three-col">
					<div class="col">
						<h3><?php _e( 'Administrators privileges', 'buddydrive' ); ?></h3>
						<p><?php _e( 'Administrators can now browse a specific user&#8217;s files and folders from the BuddyDrive Administration screen and add/edit or remove any file or folder.', 'buddydrive' ); ?></p>
					</div>
					<div class="col">
						<h3><?php _e( 'Search', 'buddydrive' ); ?></h3>
						<p><?php _e( 'A new search field has been added to the UI so that you can easily find your old items!', 'buddydrive' ); ?></p>
					</div>
					<div class="col">
						<h3><?php _e( 'Detailed user statistics', 'buddydrive' ); ?></h3>
						<p><?php _e( 'In addition to disk usage, detailed statistics will be displayed to specify the distribution of the number of files by visibility.', 'buddydrive' ); ?></p>
					</div>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( $settings_url );?>" title="<?php esc_attr_e( 'Configure BuddyDrive', 'buddydrive' ); ?>"><?php esc_html_e( 'Go to the BuddyDrive Settings page', 'buddydrive' );?></a>
			</div>

		</div>
	<?php
	}

	public function multisite_upload_trick() {
		remove_filter( 'upload_mimes', 'check_upload_mimes' );
		remove_filter( 'upload_size_limit', 'upload_size_limit_filter' );
	}

	/**
	 * Displays a field to customize the user's upload quota
	 *
	 * @since version 1.1
	 *
	 * @param  object $profileuser data about the user being edited
	 * @global $blog_id the id of the current blog
	 * @uses bp_get_root_blog_id() to make sure we're on the blog BuddyPress is activated on
	 * @uses  current_user_can() to check for edit user capability
	 * @uses ve_get_quota_by_user_id() to get user's quota (default to role's default)
	 * @uses esc_html_e() to sanitize translation before display.
	 * @return string html output
	 */
	public static function edit_user_quota( $profileuser ) {
		global $blog_id;

		if( $blog_id != bp_get_root_blog_id() )
			return;

		// Bail if current user cannot edit users
		if ( ! current_user_can( 'edit_user', $profileuser->ID ) )
			return;

		$user_quota = buddydrive_get_quota_by_user_id( $profileuser->ID );
		?>

		<h3><?php esc_html_e( 'User&#39;s BuddyDrive quota', 'buddydrive' ); ?></h3>

		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="_buddydrive_user_quota"><?php esc_html_e( 'Space available', 'buddydrive' ); ?></label></th>
					<td>
						<input name="_buddydrive_user_quota" type="number" min="1" step="1" id="_buddydrive_user_quota" value="<?php echo $user_quota;?>" class="small-text" />
						<label for="_buddydrive_user_quota"><?php _e( 'MO', 'buddydrive' ); ?></label>
					</td>
				</tr>

			</tbody>
		</table>

		<?php
	}

	/**
	 * Saves the user's quota on profile edit
	 *
	 * @since version 1.1
	 *
	 * @param  integer $user_id (the on being edited)
	 * @global $wpdb the WordPress db class
	 * @uses bp_get_root_blog_id() to make sure we're on the blog BuddyPress is activated on
	 * @uses current_user_can() to check for edit user capability
	 * @uses get_user_meta() to get user's preference
	 * @uses bp_get_option() to get blog's preference
	 * @uses buddydrive() to get the old role global
	 * @uses update_user_meta() to save user's quota
	 */
	public static function save_user_quota( $user_id ) {
		global $wpdb;

		if ( ! bp_is_root_blog() ) {
			return;
		}

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		if ( empty( $_POST['_buddydrive_user_quota'] ) ) {
			return;
		}

		$user_roles = get_user_meta( $user_id, $wpdb->get_blog_prefix( bp_get_root_blog_id() ) . 'capabilities', true );
		$user_role  = bp_get_option( 'default_role' );

		if ( ! empty( $user_roles ) && is_array( $user_roles ) ) {
			$user_role = reset( $user_roles );
		}

		// temporarly setting old role
		buddydrive()->old_role = $user_role;

		update_user_meta( $user_id, '_buddydrive_user_quota', intval( $_POST['_buddydrive_user_quota'] ) );
	}

	/**
	 * Updates the user quota on role changed
	 *
	 * @since version 1.1
	 *
	 * @param  integer $user_id the id of the user being edited
	 * @param  string $role the new role of the user
	 * @uses bp_get_root_blog_id() to make sure we're on the blog BuddyPress is activated on
	 * @uses buddydrive() to get the old role global
	 * @uses bp_get_option() to get main blog option
	 * @uses update_user_meta() to save user's preference
	 */
	public static function update_user_quota_to_role( $user_id, $role ) {
		if ( ! bp_is_root_blog() ) {
			return;
		}

		$buddydrive = buddydrive();
		$old_role   = false;

		if ( ! empty( $buddydrive->old_role ) ) {
			$old_role = $buddydrive->old_role;
		}

		if ( isset( $_POST['_buddydrive_user_quota'] ) && $old_role === $role ) {
			return;
		}

		$option_user_quota = bp_get_option( '_buddydrive_user_quota', 1000 );

		if ( is_array( $option_user_quota ) && ! empty( $option_user_quota[ $role ] ) ) {
			$user_quota = $option_user_quota[ $role ];
		} else {
			$user_quota = $option_user_quota;
		}

		update_user_meta( $user_id, '_buddydrive_user_quota', $user_quota );
	}

	/**
	 * Adds a column to admin user listing to show drive usage
	 *
	 * @since version 1.1
	 *
	 * @param  array $columns the different column of the WP_List_Table
	 * @return array the new columns
	 */
	public static function user_quota_column( $columns = array() ) {
		$columns['user_quota'] = __( 'BuddyDrive Usage',  'buddydrive' );

		return $columns;
	}

	/**
	 * Displays the row data for our new column
	 *
	 * @since version 1.1
	 *
	 * @param  string  $retval
	 * @param  string  $column_name
	 * @param  integer $user_id
	 * @uses buddydrive_get_user_space_left() to calculate the disk usage
	 * @return string the user's drive usage
	 */
	public static function user_quota_row( $retval = '', $column_name = '', $user_id = 0 ) {

		if ( 'user_quota' === $column_name && ! empty( $user_id ) ) {
			$quota = buddydrive_get_user_space_data( $user_id );

			if ( ! empty( $quota['percent'] ) && 0 < (float) $quota['percent'] ) {
				$retval = sprintf(
					'<a href="%1$s" title="%2$s">%3$s<a>',
					buddydrive()->admin->buddydrive_page . '#user/' . $user_id,
					esc_attr__( 'View all items for this user', 'buddydrive' ),
					$quota['percent'] . '%'
				);
			} else {
				$retval = $quota['percent'] . '%';
			}
		}

		// Pass retval through
		return $retval;
	}

	public function upgrade_screen() {
		global $wpdb;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'BuddyDrive Upgrade', 'buddydrive' ); ?></h1>
			<div id="message" class="fade updated buddydrive-hide">
				<p><?php esc_html_e( 'Thank you for your patience, you can now fully enjoy BuddyDrive!', 'buddydrive' ); ?></p>
			</div>
			<p>
		<?php
		$tasks = buddydrive_get_upgrade_tasks();

		if ( ! isset( $tasks ) || empty( $tasks ) ) {
			esc_html_e( 'No tasks to run. BuddyDrive is ready.', 'buddydrive' );
		} else {
			foreach ( $tasks as $key => $task ) {
				if ( ! empty( $task['count'] ) && 'upgrade_db_version' !== $task['action_id'] ) {
					$tasks[ $key ]['count'] = $wpdb->get_var( $task['count'] );

					// If nothing needs to be ugraded, remove the task.
					if ( empty( $tasks[ $key ]['count'] ) ) {
						unset( $tasks[ $key ] );
					} else {
						$tasks[ $key ]['message'] = sprintf( $task['message'], $tasks[ $key ]['count'] );
					}
				}
			}

			printf( _n( 'BuddyDrive is almost ready, please wait for the %s following task to proceed.', 'BuddyDrive is almost ready, please wait for the %s following tasks to proceed.', count( $tasks ), 'buddydrive' ), number_format_i18n( count( $tasks ) ) );
		}
		?>
			</p>
			<div id="buddydrive-upgrader"></div>
		</div>
		<?php
		// Add The Upgrader UI
		wp_enqueue_script ( 'buddydrive-upgrader-js' );
		wp_localize_script( 'buddydrive-upgrader-js', 'BuddyDrive_Upgrader', array(
			'tasks' => array_values( $tasks ),
			'nonce' => wp_create_nonce( 'buddydrive-upgrader' ),
		) );
		?>
		<script type="text/html" id="tmpl-progress-window">
			<div id="{{data.id}}">
				<div class="task-description">{{data.message}}</div>
				<div class="buddydrive-progress">
					<div class="buddydrive-bar"></div>
				</div>
			</div>
		</script>
		<?php
	}

	public function do_upgrade() {
		$error = array(
			'message'   => __( 'The task could not process due to an error', 'buddydrive' ),
			'type'      => 'error'
		);

		if ( empty( $_POST['id'] ) || ! isset( $_POST['count'] ) || ! isset( $_POST['done'] ) ) {
			wp_send_json_error( $error );
		}

		// Add the action to the error
		$error['action_id'] = $_POST['id'];

		// Check nonce
		if ( empty( $_POST['_buddydrive_nonce'] ) || ! wp_verify_nonce( $_POST['_buddydrive_nonce'], 'buddydrive-upgrader' ) ) {
			wp_send_json_error( $error );
		}

		// Check capability
		if ( ! bp_current_user_can( 'bp_moderate' ) ) {
			wp_send_json_error( $error );
		}

		$tasks = wp_list_pluck( buddydrive_get_upgrade_tasks(), 'callback', 'action_id' );

		$did = 0;

		// Upgrading the DB version
		if ( 'upgrade_db_version' === $_POST['id'] ) {
			$did = 1;
			update_option( '_buddydrive_db_version', buddydrive_get_number_version() );

		// Processing any other tasks
		} elseif ( isset( $tasks[ $_POST['id'] ] ) && function_exists( $tasks[ $_POST['id'] ] ) ) {
			$did = call_user_func_array( $tasks[ $_POST['id'] ], array( 20 ) );

			// This shouldn't happen..
			if ( 0 === $did && ( (int) $_POST['count'] > ( (int) $_POST['done'] + (int) $did ) ) ) {
				wp_send_json_error( array( 'message' => __( '%d item(s) could not be updated', 'buddydrive' ), 'type' => 'warning', 'action_id' => $_POST['id'] ) );
			}
		} else {
			wp_send_json_error( $error );
		}

		wp_send_json_success( array( 'done' => $did, 'action_id' => $_POST['id'] ) );
	}

	public function items_admin_menu_order( $custom_menus = array() ) {
		array_push( $custom_menus, 'buddydrive-files' );
		return $custom_menus;
	}

	public function items_admin_screen() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'BuddyDrive Items', 'buddydrive' ); ?></h1>

			<?php
			/**
			 * Load The BuddyDrive UI
			 */
			buddydrive_ui(); ?>
		</div>
		<?php
	}
}

endif;

/**
 * Launches the admin
 *
 * @uses buddydrive()
 */
function buddydrive_admin() {
	buddydrive()->admin = new BuddyDrive_Admin();
}

add_action( 'buddydrive_init', 'buddydrive_admin', 0 );
