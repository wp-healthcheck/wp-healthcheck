<?php
namespace THSCD\WPHC\Utils;

use THSCD\WPHC\Core\Hookable;
use THSCD\WPHC\Modules\Autoload;
use THSCD\WPHC\Modules\Server;
use THSCD\WPHC\Modules\SSL;
use THSCD\WPHC\Modules\WordPress;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Install class.
 *
 * @package wp-healthcheck
 * @since 1.4.1
 */
final class Install implements Hookable {

	/**
	 * All the plugin options.
	 *
	 * @since 1.4.1
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
	 * @since 1.4.1
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
	 * Register the plugin lifecycle hooks.
	 *
	 * @since 1.4.1
	 */
	public function hooks() {

		register_activation_hook( WPHC_PLUGIN_FILE, [ self::class, 'plugin_activation' ] );
		register_deactivation_hook( WPHC_PLUGIN_FILE, [ self::class, 'plugin_deactivation' ] );
		register_uninstall_hook( WPHC_PLUGIN_FILE, [ self::class, 'plugin_uninstall' ] );
	}

	/**
	 * Plugin activation hook.
	 *
	 * @since 1.4.1
	 */
	public static function plugin_activation() {

		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			wphc( 'module.ssl' )->is_available();
		}
	}

	/**
	 * Plugin deactivation hook.
	 *
	 * @since 1.4.1
	 */
	public static function plugin_deactivation() {

		self::cleanup_plugin_options( true );
	}

	/**
	 * Plugin uninstall hook.
	 *
	 * @since 1.4.1
	 */
	public static function plugin_uninstall() {

		self::cleanup_plugin_options();
	}

	/**
	 * Removes the plugins options and transients.
	 *
	 * @since 1.4.1
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
