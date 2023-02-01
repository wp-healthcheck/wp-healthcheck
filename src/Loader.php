<?php
namespace WPHC;

use WPHC\Admin\Admin;
use WPHC\Utils\CLI;

/**
 * The Loader class.
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class Loader {
	private $admin;
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

	public function admin() {
		return $this->admin;
	}

	public function core() {
		return $this->core;
	}

	/**
	 * Set all the things up.
	 *
	 * @since 1.4.0
	 */
	public function setup() {
		$this->core = new \WPHC\Core\Loader();
		$this->admin = new Admin();

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			new CLI();
		}
	}
}
