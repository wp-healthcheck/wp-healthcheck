<?php
namespace THSCD\WPHC\Utils;

use THSCD\WPHC\Modules\Autoload;
use THSCD\WPHC\Modules\Server;
use THSCD\WPHC\Modules\SSL;
use THSCD\WPHC\Modules\WordPress;

/**
 * The Install class.
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */
final class Install {

	/**
	 * All the plugin options.
	 *
	 * @since {VERSION}
	 *
	 * @var array
	 */
	private static $plugin_options = [
		Autoload::DEACTIVATION_HISTORY_OPTION,
		Upgrade::PLUGIN_VERSION_OPTION,
		WordPress::CORE_AUTO_UPDATE_OPTION,
	];

	/**
	 * All the plugin transients.
	 *
	 * @since {VERSION}
	 *
	 * @var array
	 */
	private static $plugin_transients = [
		Server::DATA_TRANSIENT,
		Server::MIN_REQUIREMENTS_TRANSIENT,
		SSL::AVAILABLE_TRANSIENT,
		SSL::DATA_TRANSIENT,
	];

	/**
	 * Constructor.
	 *
	 * @since {VERSION}
	 */
	public function __construct() {

		// Registers the hooks.
		register_activation_hook( WPHC_PLUGIN_FILE, [ self::class, 'plugin_activation' ] );
		register_deactivation_hook( WPHC_PLUGIN_FILE, [ self::class, 'plugin_deactivation' ] );
		register_uninstall_hook( WPHC_PLUGIN_FILE, [ self::class, 'plugin_uninstall' ] );
	}

	/**
	 * Plugin activation hook.
	 *
	 * @since {VERSION}
	 */
	public static function plugin_activation() {

		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			wphc( 'module.ssl' )->is_available();
		}
	}

	/**
	 * Plugin deactivation hook.
	 *
	 * @since {VERSION}
	 */
	public static function plugin_deactivation() {

		self::cleanup_plugin_options( true );
	}

	/**
	 * Plugin uninstall hook.
	 *
	 * @since {VERSION}
	 */
	public static function plugin_uninstall() {

		self::cleanup_plugin_options();
	}

	/**
	 * Removes the plugins options and transients.
	 *
	 * @since {VERSION}
	 *
	 * @param bool $only_transients True if should remove only the transients.
	 */
	public static function cleanup_plugin_options( $only_transients = false ) {

		if ( ! $only_transients ) {
			// Removes the options.
			foreach ( self::$plugin_options as $option ) {
				if ( get_option( $option ) ) {
					delete_option( $option );
				}
			}
		}

		// Removes the transients.
		foreach ( self::$plugin_transients as $transient ) {
			if ( get_transient( $transient ) ) {
				delete_transient( $transient );
			}
		}
	}
}
