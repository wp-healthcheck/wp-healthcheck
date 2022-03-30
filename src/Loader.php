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

	public $main;

	public $dashboard;

	public $ajax;

	public $pointers;

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
	}
}
