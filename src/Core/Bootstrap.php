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

use THSCD\WPHC\Modules\CLI;
use WP_CLI;

/**
 * Class Bootstrap.
 *
 * Initializes the plugin components.
 *
 * @since {VERSION}
 */
class Bootstrap {

	/**
	 * Initialize the plugin.
	 *
	 * @since {VERSION}
	 */
	public static function init() {

		// Initialize utilities.
		wphc( 'util.upgrade' );
		wphc( 'util.install' );

		// Initialize admin classes.
		self::init_admin();

		// Load WP-CLI commands.
		self::load_cli();
	}

	/**
	 * Initialize admin classes.
	 *
	 * @since {VERSION}
	 */
	private static function init_admin() {

		if ( ! is_admin() ) {
			return;
		}

		wphc( 'admin.dashboard' );
		wphc( 'admin.ajax' );
		wphc( 'admin.pointers' );
		wphc( 'admin.notices' );
	}

	/**
	 * Load WP-CLI commands.
	 *
	 * @since {VERSION}
	 */
	private static function load_cli() {

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'healthcheck', CLI::class );
		}
	}
}
