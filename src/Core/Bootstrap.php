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

use THSCD\WPHC\Modules\Autoload;
use THSCD\WPHC\Modules\CLI;
use THSCD\WPHC\Modules\Plugins;
use THSCD\WPHC\Modules\Server;
use THSCD\WPHC\Modules\SSL;
use THSCD\WPHC\Modules\Transients;
use THSCD\WPHC\Modules\WordPress;
use THSCD\WPHC\Admin\AJAX;
use THSCD\WPHC\Admin\Dashboard;
use THSCD\WPHC\Admin\Metaboxes;
use THSCD\WPHC\Admin\Notices;
use THSCD\WPHC\Admin\Pointers;
use THSCD\WPHC\Utils\Install;
use THSCD\WPHC\Utils\Upgrade;
use THSCD\WPHC\Utils\View;
use WP_CLI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Bootstrap.
 *
 * The composition root: registers every service in the container and boots the
 * ones that wire themselves into WordPress, in a deterministic order.
 *
 * @since {VERSION}
 */
class Bootstrap {

	/**
	 * Service map: container id => concrete class.
	 *
	 * @since {VERSION}
	 *
	 * @var array
	 */
	private static $services = [
		// Modules.
		'module.autoload'   => Autoload::class,
		'module.plugins'    => Plugins::class,
		'module.server'     => Server::class,
		'module.ssl'        => SSL::class,
		'module.transients' => Transients::class,
		'module.wordpress'  => WordPress::class,

		// Admin.
		'admin.dashboard'   => Dashboard::class,
		'admin.ajax'        => AJAX::class,
		'admin.metaboxes'   => Metaboxes::class,
		'admin.pointers'    => Pointers::class,
		'admin.notices'     => Notices::class,

		// Utils.
		'util.install'      => Install::class,
		'util.upgrade'      => Upgrade::class,
		'util.view'         => View::class,
	];

	/**
	 * Services whose hooks must register on every request (front, admin, cron, CLI).
	 *
	 * @since {VERSION}
	 *
	 * @var array
	 */
	private static $boot_always = [
		'util.upgrade',
		'util.install',
		'module.wordpress',
	];

	/**
	 * Services whose hooks only register on admin requests.
	 *
	 * @since {VERSION}
	 *
	 * @var array
	 */
	private static $boot_admin = [
		'module.ssl', // SSL data is only consumed by admin notices/dashboard; no need to collect it on front-end requests.
		'admin.dashboard',
		'admin.ajax',
		'admin.pointers',
		'admin.notices',
	];

	/**
	 * Initialize the plugin.
	 *
	 * @since {VERSION}
	 */
	public static function init() {

		self::register_services();

		self::boot( self::$boot_always );

		if ( is_admin() ) {
			self::boot( self::$boot_admin );
		}

		self::load_cli();
	}

	/**
	 * Register every service in the container as a singleton.
	 *
	 * @since {VERSION}
	 */
	private static function register_services() {

		$container = wphc();

		foreach ( self::$services as $id => $class ) {
			$container->singleton( $id, $class );
		}
	}

	/**
	 * Boot a group of services: resolve each and register its hooks.
	 *
	 * Resolving a service constructs it (constructors are side-effect free);
	 * hook registration only happens here, through the Hookable contract.
	 *
	 * @since {VERSION}
	 *
	 * @param array $ids Container ids to boot.
	 */
	private static function boot( $ids ) {

		foreach ( $ids as $id ) {
			$service = wphc( $id );

			if ( $service instanceof Hookable ) {
				$service->hooks();
			}
		}
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
