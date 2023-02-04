<?php
namespace WPHC\Utils;

use WPHC\Admin\Dashboard;
use WPHC\Core\Options;
use WPHC\Core\Plugins;
use WPHC\Core\Server;
use WPHC\Core\SSL;
use WPHC\Core\WordPress;

/**
 * The RegisterHooks class.
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class RegisterHooks {

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {

		register_activation_hook( WPHC_PLUGIN_FILE, [ '\WPHC\Utils\RegisterHooks', 'plugin_activation' ] );
		register_deactivation_hook( WPHC_PLUGIN_FILE, [ '\WPHC\Utils\RegisterHooks', 'plugin_deactivation' ] );
		register_uninstall_hook( WPHC_PLUGIN_FILE, [ '\WPHC\Utils\RegisterHooks', 'plugin_uninstall' ] );

		add_action( 'upgrader_process_complete', [ $this, 'plugin_deactivation' ] );
	}

	/**
	 * Activation hook.
	 *
	 * @since 1.4.0
	 */
	public static function plugin_activation() {

		if ( ! get_option( Options::DISABLE_AUTOLOAD_OPTION ) ) {
			add_option( Options::DISABLE_AUTOLOAD_OPTION, '', '', 'no' );
		}

		wphc()->core()->plugins()->get_outdated_plugins();
		wphc()->core()->ssl()->is_ssl_available();
	}

	/**
	 * Deactivation hook.
	 *
	 * @since 1.4.0
	 */
	public static function plugin_deactivation() {

		self::cleanup_options( true );
	}

	/**
	 * Uninstallation hook.
	 *
	 * @since 1.4.0
	 */
	public static function plugin_uninstall() {

		self::cleanup_options();
	}

	/**
	 * Cleans up the plugin options and transients.
	 *
	 * @since 1.0
	 *
	 * @param bool $only_transients True to remove only the transients.
	 */
	public static function cleanup_options( $only_transients = false ) {

		if ( ! $only_transients ) {
			$options = [
				Dashboard::DISABLE_NOTICES_OPTION,
				Options::DISABLE_AUTOLOAD_OPTION,
				Plugins::DISABLE_OUTDATED_PLUGINS_OPTION,
				Upgrade::PLUGIN_VERSION_OPTION,
				WordPress::CORE_AUTO_UPDATE_OPTION,
			];

			foreach ( $options as $option ) {
				if ( get_option( $option ) ) {
					delete_option( $option );
				}
			}
		}

		$transients = [
			Dashboard::HIDE_NOTICES_TRANSIENT,
			Plugins::OUTDATED_PLUGINS_TRANSIENT,
			Server::MIN_REQUIREMENTS_TRANSIENT,
			Server::SERVER_DATA_TRANSIENT,
			SSL::SSL_AVAILABLE_TRANSIENT,
			SSL::SSL_DATA_TRANSIENT,
		];

		foreach ( $transients as $transient ) {
			if ( get_transient( $transient ) ) {
				delete_transient( $transient );
			}
		}
	}
}

new RegisterHooks();
