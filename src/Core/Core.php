<?php
namespace WPHC\Core;

/**
 * The WP_Healthcheck class
 *
 * @package wp-healthcheck
 * @since 1.0
 */
class Core {
	/**
	 * Option to store the history of disabled autoload options.
	 *
	 * @since 1.0
	 * @var string
	 */
	const DISABLE_AUTOLOAD_OPTION = 'wphc_disable_autoload_history';

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
			WP_Healthcheck_Upgrade::maybe_upgrade_db();

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
	 * Determines if WordPress cron constant is enabled or not.
	 *
	 * @since 1.0
	 *
	 * @return boolean True if WordPress cron is disabled.
	 */
	public static function is_wpcron_disabled() {
		return ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON );
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
				WP_Healthcheck_Upgrade::PLUGIN_VERSION_OPTION,
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
