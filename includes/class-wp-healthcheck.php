<?php
/**
 * The WP_Healthcheck class
 *
 * @package wp-healthcheck
 * @since 1.0
 */
class WP_Healthcheck {
	/**
	 * Option to store the history of disabled autoload options.
	 *
	 * @since 1.0
	 * @var string
	 */
	const DISABLE_AUTOLOAD_OPTION = 'wphc_disable_autoload_history';

	/**
	 * Option to disable admin notices.
	 *
	 * @since 1.0
	 * @var string
	 */
	const DISABLE_NOTICES_OPTION = 'wphc_disable_admin_notices';

	/**
	 * Option to store the auto update status.
	 *
	 * @since 1.3.0
	 * @var string
	 */
	const CORE_AUTO_UPDATE_OPTION = 'wphc_auto_update_status';

	/**
	 * Option to disable outdated plugins check.
	 *
	 * @since 1.3.0
	 * @var string
	 */
	const DISABLE_OUTDATED_PLUGINS_OPTION = 'wphc_disable_outdated_plugins_check';

	/**
	 * Transient to store if an admin notice should be displayed or not.
	 *
	 * @since 1.0
	 * @var string
	 */
	const HIDE_NOTICES_TRANSIENT = 'wphc_hide_admin_notices';

	/**
	 * Transient to store the minimum requirements.
	 *
	 * @since 1.0
	 * @var string
	 */
	const MIN_REQUIREMENTS_TRANSIENT = 'wphc_min_requirements';

	/**
	 * Transient to store the outdated plugins.
	 *
	 * @since 1.3.0
	 * @var string
	 */
	const OUTDATED_PLUGINS_TRANSIENT = 'wphc_plugins_outdated';

	/**
	 * Transient to store the server data.
	 *
	 * @since 1.0
	 * @var string
	 */
	const SERVER_DATA_TRANSIENT = 'wphc_server_data';

	/**
	 * Transient to store the SSL data.
	 *
	 * @since 1.2
	 * @var string
	 */
	const SSL_DATA_TRANSIENT = 'wphc_ssl_data';

	/**
	 * Transient to store if SSL is available or not.
	 *
	 * @since 1.3.0
	 * @var string
	 */
	const SSL_AVAILABLE_TRANSIENT = 'wphc_ssl_available';

	/**
	 * Whether to initiate the WordPress hooks.
	 *
	 * @since 1.0
	 * @var boolean
	 */
	private static $initiated = false;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Initialize the WordPress hooks.
	 *
	 * @since 1.0
	 */
	public static function init_hooks() {
		self::$initiated = true;

		add_action( 'upgrader_process_complete', array( 'WP_Healthcheck', 'plugin_deactivation' ) );
	}

	/**
	 * Retrieves a list of plugins with no updates released on the
	 * last 2 years.
	 *
	 * @since 1.3.0
	 *
	 * @return array|false Slug and number of days since last update
	 * of the plugins or false if none.
	 */
	public static function get_outdated_plugins() {
		if ( get_option( self::DISABLE_OUTDATED_PLUGINS_OPTION ) ) {
			return false;
		}

		$outdated_plugins = get_transient( self::OUTDATED_PLUGINS_TRANSIENT );

		if ( false === $outdated_plugins ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( ! function_exists( 'plugins_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			}

			$outdated_plugins = array();

			foreach ( get_plugins() as $file => $plugin ) {
				$slug = explode( '/', $file );
				$slug = preg_replace( '/\.php/', '', $slug[0] );

				$wp_api = plugins_api(
					'plugin_information',
					array(
						'slug' => $slug,
					)
				);

				if ( empty( $wp_api->errors ) && ! empty( $wp_api->last_updated ) ) {
					$today       = new DateTime();
					$last_update = new DateTime( $wp_api->last_updated );

					$days = $today->diff( $last_update )->format( '%a' );

					if ( $days > 730 ) {
						$outdated_plugins[ $slug ] = $days;
					}
				}
			}

			set_transient( self::OUTDATED_PLUGINS_TRANSIENT, $outdated_plugins, WEEK_IN_SECONDS );
		}

		return $outdated_plugins;
	}

	/**
	 * Returns the system's username of the site owner.
	 *
	 * @since 1.0
	 *
	 * @return string Username of the site owner.
	 */
	public static function get_site_owner() {
		$uid = fileowner( ABSPATH );

		$owner = ( is_numeric( $uid ) ) ? posix_getpwuid( $uid ) : null;

		$user = ( is_null( $owner ) || ! isset( $owner['name'] ) ) ? 'root' : $owner['name'];

		return $user;
	}

	/**
	 * Determine if an option is a WP core one or not.
	 *
	 * @since 1.0
	 *
	 * @param string $option_name The option name.
	 *
	 * @return boolean True if it is a WP core option.
	 */
	public static function is_core_option( $option_name ) {
		$wp_opts_file = WPHC_INC_DIR . '/data/wp_options.json';

		if ( file_exists( $wp_opts_file ) ) {
			$wp_opts = json_decode( file_get_contents( $wp_opts_file ) );
		}

		return ( in_array( $option_name, $wp_opts ) );
	}

	/**
	 * Determines if WordPress auto update constants are enabled or not.
	 *
	 * @since 1.3.0
	 *
	 * @return boolean True if WordPress auto update constants are available.
	 */
	public static function is_wp_auto_update_available() {
		return ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) || defined( 'WP_AUTO_UPDATE_CORE' ) );
	}

	/**
	 * Add options when plugin is activated.
	 *
	 * @since 1.0
	 */
	public static function plugin_activation() {
		if ( ! get_option( self::DISABLE_AUTOLOAD_OPTION ) ) {
			add_option( self::DISABLE_AUTOLOAD_OPTION, '', '', 'no' );
		}

		WP_Healthcheck::get_outdated_plugins();
		WP_Healthcheck::is_ssl_available();
	}

	/**
	 * Cleanup transients when plugin is deactivated.
	 *
	 * @since 1.0
	 */
	public static function plugin_deactivation() {
		self::_cleanup_options( true );
	}

	/**
	 * Cleanup options and transients when plugin is uninstalled.
	 *
	 * @since 1.0
	 */
	public static function plugin_uninstall() {
		self::_cleanup_options();
	}

	/**
	 * Sets the wp-healthcheck auto update option value
	 * which could be 'disabled', 'minor', 'major' or 'dev'.
	 *
	 * @param string $option_value Auto update value.
	 *
	 * @since 1.3.0
	 */
	public static function set_core_auto_update_option( $option_value ) {
		$core_auto_update_option = get_option( self::CORE_AUTO_UPDATE_OPTION );

		if ( self::is_wp_auto_update_available() ) {
			if ( $core_auto_update_option ) {
				delete_option( self::CORE_AUTO_UPDATE_OPTION );
			}
		}

		update_option( self::CORE_AUTO_UPDATE_OPTION, $option_value );
	}

	/**
	 * Cleans up the plugin options and transients.
	 *
	 * @since 1.0
	 *
	 * @param boolean $only_transients True to remove only the transients.
	 */
	public static function _cleanup_options( $only_transients = false ) {
		if ( ! $only_transients ) {
			$options = array(
				self::DISABLE_AUTOLOAD_OPTION,
				self::DISABLE_NOTICES_OPTION,
				self::CORE_AUTO_UPDATE_OPTION,
				self::DISABLE_OUTDATED_PLUGINS_OPTION,
				\THSCD\WPHC\Utils\Upgrade::PLUGIN_VERSION_OPTION,
			);

			foreach ( $options as $option ) {
				if ( get_option( $option ) ) {
					delete_option( $option );
				}
			}
		}

		$transients = array(
			self::HIDE_NOTICES_TRANSIENT,
			self::MIN_REQUIREMENTS_TRANSIENT,
			self::OUTDATED_PLUGINS_TRANSIENT,
			self::SERVER_DATA_TRANSIENT,
			self::SSL_AVAILABLE_TRANSIENT,
			self::SSL_DATA_TRANSIENT,
		);

		foreach ( $transients as $transient ) {
			if ( get_transient( $transient ) ) {
				delete_transient( $transient );
			}
		}
	}
}
