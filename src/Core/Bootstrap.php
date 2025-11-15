<?php
/**
 * Bootstrap
 *
 * Handles plugin initialization and loading.
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */

namespace THSCD\WPHC\Core;

use THSCD\WPHC\Utils\Install;
use THSCD\WPHC\Utils\Upgrade;
use THSCD\WPHC\Modules\CLI;

/**
 * Class Bootstrap
 *
 * Initializes the plugin components.
 */
class Bootstrap {

	/**
	 * Initialize the plugin.
	 *
	 * @since {VERSION}
	 */
	public static function init() {

		// Initialize utilities.
		new Upgrade();
		new Install();

		// Load legacy classes.
		self::load_legacy();

		// Initialize legacy hooks.
		self::init_legacy_hooks();

		// Load WP-CLI commands.
		self::load_cli();
	}

	/**
	 * Load legacy classes.
	 *
	 * @since {VERSION}
	 */
	private static function load_legacy() {

		require_once WPHC_INC_DIR . '/class-wp-healthcheck.php';

		if ( is_admin() ) {
			require_once WPHC_INC_DIR . '/class-wp-healthcheck-admin.php';
		}
	}

	/**
	 * Initialize legacy hooks.
	 *
	 * @since {VERSION}
	 */
	private static function init_legacy_hooks() {

		add_action( 'init', [ 'WP_Healthcheck', 'init' ] );

		if ( is_admin() ) {
			add_action( 'init', [ 'WP_Healthcheck_Admin', 'init' ] );
		}
	}

	/**
	 * Load WP-CLI commands.
	 *
	 * @since {VERSION}
	 */
	private static function load_cli() {

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'healthcheck', CLI::class );
		}
	}
}

