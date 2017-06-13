<?php
/*
Plugin Name: BuddyDrive
Plugin URI: https://wordpress.org/plugins/buddydrive/
Description: A plugin to share files, the BuddyPress way!
Version: 2.1.1
Author: mrpritchett
Author URI: http://pritchett.media
License: GPLv2
Text Domain: buddydrive
Domain Path: /languages/
*/

// Create a helper function for easy SDK access.
function buddydrive_fs() {
    global $buddydrive_fs;

    if ( ! isset( $buddydrive_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $buddydrive_fs = fs_dynamic_init( array(
            'id'                  => '619',
            'slug'                => 'buddydrive',
            'type'                => 'plugin',
            'public_key'          => 'pk_c302f2a54e3a828af10c04778ebc5',
            'is_premium'          => false,
            'has_addons'          => false,
            'has_paid_plans'      => false,
            'menu'                => array(
                'slug'           => 'buddydrive-files',
                'first-path'     => 'index.php?page=buddydrive-about',
                'account'        => false,
                'contact'        => false,
            ),
        ) );
    }

    return $buddydrive_fs;
}

// Init Freemius.
buddydrive_fs();
// Signal that SDK was initiated.
do_action( 'buddydrive_fs_loaded' );

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'BuddyDrive' ) ) :
/**
 * Main BuddyDrive Class
 *
 * Inspired by bbpress 2.3
 */
class BuddyDrive {

	private $data;

	private static $instance;

	/**
	 * Required BuddyPress version for the plugin.
	 *
	 * @package BuddyDrive
	 * @since 1.2.0
	 *
	 * @var      string
	 */
	public static $required_bp_version = '2.5.0';

	/**
	 * BuddyPress config.
	 *
	 * @package BuddyDrive
	 * @since 1.2.0
	 *
	 * @var      array
	 */
	public static $bp_config = array();

	/**
	 * Main BuddyDrive Instance
	 *
	 * Inspired by bbpress 2.3
	 *
	 * Avoids the use of a global
	 *
	 * @package BuddyDrive
	 * @since 1.0
	 *
	 * @uses BuddyDrive::setup_globals() to set the global needed
	 * @uses BuddyDrive::includes() to include the required files
	 * @uses BuddyDrive::setup_actions() to set up the hooks
	 * @return object the instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new BuddyDrive;
			self::$instance->setup_globals();
			self::$instance->includes();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}


	private function __construct() { /* Do nothing here */ }

	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'buddydrive' ), '1.2.0' ); }

	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'buddydrive' ), '1.2.0' ); }

	public function __isset( $key ) { return isset( $this->data[$key] ); }

	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	public function __set( $key, $value ) { $this->data[$key] = $value; }

	public function __unset( $key ) { if ( isset( $this->data[$key] ) ) unset( $this->data[$key] ); }

	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }


	/**
	 * Some usefull vars
	 *
	 * @package BuddyDrive
	 * @since 1.0
	 *
	 * @uses plugin_basename()
	 * @uses plugin_dir_path() to build BuddyDrive plugin path
	 * @uses plugin_dir_url() to build BuddyDrive plugin url
	 */
	private function setup_globals() {

		/** Version ***********************************************************/

		$this->version    = '2.1.1';
		$this->db_version = 211;

		/** Paths *************************************************************/

		// Setup some base path and URL information
		$this->file       = __FILE__;
		$this->basename   = apply_filters( 'buddydrive_plugin_basename', plugin_basename( $this->file ) );
		$this->plugin_dir = apply_filters( 'buddydrive_plugin_dir_path', plugin_dir_path( $this->file ) );
		$this->plugin_url = apply_filters( 'buddydrive_plugin_dir_url',  plugin_dir_url ( $this->file ) );

		// Includes
		$this->includes_dir = apply_filters( 'buddydrive_includes_dir', trailingslashit( $this->plugin_dir . 'includes'  ) );
		$this->includes_url = apply_filters( 'buddydrive_includes_url', trailingslashit( $this->plugin_url . 'includes'  ) );
		$this->upload_dir   = false;
		$this->upload_url   = false;
		$this->images_url = apply_filters( 'buddydrive_images_url', trailingslashit( $this->includes_url . 'images'  ) );

		// Languages
		$this->lang_dir     = apply_filters( 'buddydrive_lang_dir', trailingslashit( $this->plugin_dir . 'languages' ) );

		// BuddyDrive slug and name
		$this->buddydrive_slug = apply_filters( 'buddydrive_slug', 'buddydrive' );
		$this->buddydrive_name = apply_filters( 'buddydrive_name', 'BuddyDrive' );

		// Post type identifiers
		$this->buddydrive_file_post_type   = apply_filters( 'buddydrive_file_post_type',     'buddydrive-file' );
		$this->buddydrive_folder_post_type = apply_filters( 'buddydrive_folder_post_type', 'buddydrive-folder' );


		/** Misc **************************************************************/

		$this->domain           = 'buddydrive';
		$this->errors           = new WP_Error(); // Feedback
		$this->users_file_count = array();
	}

	/**
	 * includes the needed files
	 *
	 * @package BuddyDrive
	 * @since 1.0
	 *
	 * @uses is_admin() for the settings files
	 */
	private function includes() {
		require( $this->includes_dir . 'buddydrive-actions.php'         );
		require( $this->includes_dir . 'buddydrive-functions.php'       );

		if( is_admin() ){
			require( $this->includes_dir . 'admin/buddydrive-admin.php' );
		}
	}


	/**
	 * It's about hooks!
	 *
	 * @package BuddyDrive
	 * @since 1.0
	 *
	 * The main hook used is bp_include to load our custom BuddyPress component
	 */
	private function setup_actions() {

		// Add actions to plugin activation and deactivation hooks
		add_action( 'activate_'   . $this->basename, 'buddydrive_activation'   );
		add_action( 'deactivate_' . $this->basename, 'buddydrive_deactivation' );

		add_action( 'bp_loaded',  array( $this, 'load_textdomain' ) );
		add_action( 'bp_include', array( $this, 'load_component'  ) );

		do_action_ref_array( 'buddydrive_after_setup_actions', array( &$this ) );
	}

	/**
	 * Loads the translation
	 *
	 * @package BuddyDrive
	 * @since 1.0
	 * @uses get_locale()
	 * @uses load_textdomain()
	 */
	public function load_textdomain() {
		// try to get locale
		$locale = apply_filters( 'buddydrive_load_textdomain_get_locale', get_locale(), $this->domain );
		$mofile = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to a buddydrive subfolder in WP LANG DIR
		$mofile_global = WP_LANG_DIR . '/buddydrive/' . $mofile;

		// Look in global /wp-content/languages/buddydrive folder
		if ( ! load_textdomain( $this->domain, $mofile_global ) ) {

			// Look in local /wp-content/plugins/buddydrive/languages/ folder
			// or /wp-content/languages/plugins/
			load_plugin_textdomain( $this->domain, false, basename( $this->plugin_dir ) . '/languages' );
		}
	}

	/**
	 * Finally, let's safely load our component
	 *
	 * @package BuddyDrive
	 * @since 1.0
	 */
	public function load_component() {
		if ( self::bail() ) {
			add_action( self::$bp_config['network_admin'] ? 'network_admin_notices' : 'admin_notices', array( $this, 'warning' ) );
		} else {
			require( $this->includes_dir . 'buddydrive-component.php' );
		}
	}

	/** Utilities *****************************************************************************/

	/**
	 * Checks BuddyPress version
	 *
	 * @package BuddyDrive
	 * @since 1.2.0
	 */
	public static function version_check() {
		// taking no risk
		if ( ! defined( 'BP_VERSION' ) )
			return false;

		return version_compare( BP_VERSION, self::$required_bp_version, '>=' );
	}

	/**
	 * Checks if your plugin's config is similar to BuddyPress
	 *
	 * @package BuddyDrive
	 * @since 1.2.0
	 */
	public static function config_check() {
		/**
		 * blog_status    : true if your plugin is activated on the same blog
		 * network_active : true when your plugin is activated on the network
		 * network_status : BuddyPress & your plugin share the same network status
		 */
		self::$bp_config = array(
			'blog_status'    => false,
			'network_active' => false,
			'network_status' => true,
			'network_admin'  => false
		);

		$buddypress = false;

		if ( function_exists( 'buddypress' ) ) {
			$buddypress = buddypress()->basename;
		}

		if ( $buddypress && get_current_blog_id() == bp_get_root_blog_id() ) {
			self::$bp_config['blog_status'] = true;
		}

		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );

		// No Network plugins
		if ( empty( $network_plugins ) )
			return self::$bp_config;

		$buddydrive = plugin_basename( __FILE__ );

		// Looking for BuddyDrive
		$check = array( $buddydrive );

		// And for BuddyPress if set
		if ( ! empty( $buddypress ) )
			$check = array_merge( array( $buddypress ), $check );

		// Are they active on the network ?
		$network_active = array_diff( $check, array_keys( $network_plugins ) );

		// If result is 1, your plugin is network activated
		// and not BuddyPress or vice & versa. Config is not ok
		if ( count( $network_active ) == 1 )
			self::$bp_config['network_status'] = false;

		self::$bp_config['network_active'] = isset( $network_plugins[ $buddydrive ] );

		// We need to know if the BuddyPress is network activated to choose the right
		// notice ( admin or network_admin ) to display the warning message.
		self::$bp_config['network_admin']  = ! empty( $buddypress ) && isset( $network_plugins[ $buddypress ] );

		return self::$bp_config;
	}

	/**
	 * Bail if BuddyPress config is different than this plugin
	 *
	 * @package BuddyDrive
	 * @since 1.2.0
	 */
	public static function bail() {
		$retval = false;

		$config = self::config_check();

		if ( ! self::version_check() || ! $config['blog_status'] || ! $config['network_status'] )
			$retval = true;

		return $retval;
	}

	/**
	 * Display a warning message to admin
	 *
	 * @package BuddyDrive
	 * @since 1.2.0
	 */
	public function warning() {
		$warnings = $resolve = array();

		if ( ! self::version_check() ) {
			$warnings[] = sprintf( esc_html__( 'BuddyDrive requires at least version %s of BuddyPress.', 'buddydrive' ), self::$required_bp_version );
			$resolve[]  = sprintf( esc_html__( 'Upgrade BuddyPress to at least version %s', 'buddydrive' ), self::$required_bp_version );
		}

		if ( ! empty( self::$bp_config ) ) {
			$config = self::$bp_config;
		} else {
			$config = self::config_check();
		}

		if ( ! $config['blog_status'] ) {
			$warnings[] = esc_html__( 'BuddyDrive requires to be activated on the blog where BuddyPress is activated.', 'buddydrive' );
			$resolve[]  = esc_html__( 'Activate BuddyDrive on the same blog than BuddyPress', 'buddydrive' );
		}

		if ( ! $config['network_status'] ) {
			$warnings[] = esc_html__( 'BuddyDrive and BuddyPress need to share the same network configuration.', 'buddydrive' );
			$resolve[]  = esc_html__( 'Make sure BuddyDrive is activated at the same level than BuddyPress on the network', 'buddydrive' );
		}

		if ( ! empty( $warnings ) ) {
			// Give some more explanations to administrator
			if ( is_super_admin() ) {
				$deactivate_link = ! empty( $config['network_active'] ) ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' );
				$deactivate_link = '<a href="' . esc_url( $deactivate_link ) . '">' . esc_html__( 'deactivate', 'buddydrive' ) . '</a>';
				$resolve_message = '<ol><li>' . sprintf( __( 'You should %s BuddyDrive', 'buddydrive' ), $deactivate_link ) . '</li>';

				foreach ( (array) $resolve as $step ) {
					$resolve_message .= '<li>' . $step . '</li>';
				}

				if ( $config['network_status'] && $config['blog_status']  )
					$resolve_message .= '<li>' . esc_html__( 'Once done try to activate BuddyDrive again.', 'buddydrive' ) . '</li></ol>';

				$warnings[] = $resolve_message;
			}

		?>
		<div id="message" class="error">
			<?php foreach ( $warnings as $warning ) : ?>
				<p><?php echo $warning; ?></p>
			<?php endforeach ; ?>
		</div>
		<?php
		}
	}

}

function buddydrive() {
	return buddydrive::instance();
}

buddydrive();


endif;
