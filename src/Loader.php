<?php
namespace WPHC;

use WPHC\Utils\CLI;

/**
 * The Loader class.
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class Loader {
	/**
	 * The Admin\Loader object.
	 *
	 * @since 1.4.0
	 *
	 * @var Admin\Loader
	 */
	private $admin;

	/**
	 * The Core\Loader object.
	 *
	 * @since 1.4.0
	 *
	 * @var Core\Loader
	 */
	private $core;

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * WordPress actions and filters.
	 *
	 * @since 1.4.0
	 */
	public function hooks() {

		add_action( 'plugins_loaded', [ $this, 'setup' ] );
	}

	/**
	 * Get the Admin\Loader object.
	 *
	 * @since 1.4.0
	 *
	 * @return Admin\Loader
	 */
	public function admin() {

		return $this->admin;
	}

	/**
	 * Get the Core\Loader object.
	 *
	 * @since 1.4.0
	 *
	 * @return Core\Loader
	 */
	public function core() {

		return $this->core;
	}

	/**
	 * Set all the things up.
	 *
	 * @since 1.4.0
	 */
	public function setup() {

		require_once WPHC_PLUGIN_DIR . '/src/Utils/RegisterHooks.php';
		require_once WPHC_PLUGIN_DIR . '/src/Utils/Helpers.php';

		$this->core  = new Core\Loader();
		$this->admin = new Admin\Loader();

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			new CLI();
		}
	}
}
