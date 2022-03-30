<?php
namespace WPHC;

use WPHC\Admin\AJAX;
use WPHC\Admin\Dashboard;
use WPHC\Admin\Pointers;
use WPHC\Core\CLI;
use WPHC\Core\Upgrade;
use WPHC\Core\WP_Healthcheck;

/**
 * The Loader class.
 *
 * @package wp-healthcheck
 *
 * @since 2.0.0
 */
class Loader {
	/**
	 * The WP_Healthcheck object.
	 *
	 * @since 2.0.0
	 *
	 * @var WP_Healthcheck
	 */
	public $main;

	/**
	 * The Dashboard object.
	 *
	 * @since 2.0.0
	 *
	 * @var Dashboard
	 */
	public $dashboard;

	/**
	 * The AJAX object.
	 *
	 * @since 2.0.0
	 *
	 * @var AJAX
	 */
	public $ajax;

	/**
	 * The Pointers object.
	 *
	 * @since 2.0.0
	 *
	 * @var Pointers
	 */
	public $pointers;

	/**
	 * The Upgrade object.
	 *
	 * @since 2.0.0
	 *
	 * @var Upgrade
	 */
	public $upgrade;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the WordPress hooks.
	 *
	 * @since 2.0.0
	 */
	public function init() {
		add_action( 'plugins_loaded', [ $this, 'loader' ] );
	}

	/**
	 * Load the plugin classes.
	 *
	 * @since 2.0.0
	 */
	public function loader() {
		// Loads Composer.
		require_once WPHC_PLUGIN_DIR . '/vendor/autoload.php';

		// Loads the WP_Healthcheck class.
		$this->main = new WP_Healthcheck();

		// Loads the Upgrade class and upgrade the DB if a new version is found.
		$this->upgrade = new Upgrade();
		$this->upgrade->maybe_upgrade_db();

		// Loads the Dashboard class and dependencies.
		if ( is_admin() ) {
			$this->ajax      = new AJAX();
			$this->pointers  = new Pointers();
			$this->dashboard = new Dashboard();
		}

		// Loads the CLI class.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			new CLI();
		}

		// Enable the register hooks.
		$this->register_hooks();
	}

	/**
	 * Enable the register hooks to run on plugin activation, deactivation, and uninstallation.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		register_activation_hook( __FILE__, [ wphc()->main, 'plugin_activation' ] );
		register_deactivation_hook( __FILE__, [ wphc()->main, 'plugin_deactivation' ] );
		register_uninstall_hook( __FILE__, [ wphc()->main, 'plugin_uninstall' ] );
	}
}
