<?php
namespace WPHC\Core;

use WPHC\Utils\Upgrade;

/**
 * The Core\Loader class.
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class Loader {
	/**
	 * The Options object.
	 *
	 * @since 1.4.0
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * The Plugins object.
	 *
	 * @since 1.4.0
	 *
	 * @var Plugins
	 */
	private $plugins;

	/**
	 * The Server object.
	 *
	 * @since 1.4.0
	 *
	 * @var Server
	 */
	private $server;

	/**
	 * The SSL object.
	 *
	 * @since 1.4.0
	 *
	 * @var SSL
	 */
	private $ssl;

	/**
	 * The Transients object.
	 *
	 * @since 1.4.0
	 *
	 * @var Transients
	 */
	private $transients;

	/**
	 * The WordPress object.
	 *
	 * @since 1.4.0
	 *
	 * @var WordPress
	 */
	private $wordpress;

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {

		$this->setup();
	}

	/**
	 * Get the Options object.
	 *
	 * @since 1.4.0
	 *
	 * @return Options
	 */
	public function options() {

		return $this->options;
	}

	/**
	 * Get the Plugins object.
	 *
	 * @since 1.4.0
	 *
	 * @return Plugins
	 */
	public function plugins() {

		return $this->plugins;
	}

	/**
	 * Get the Server object.
	 *
	 * @since 1.4.0
	 *
	 * @return Server
	 */
	public function server() {

		return $this->server;
	}

	/**
	 * Get the SSL object.
	 *
	 * @since 1.4.0
	 *
	 * @return SSL
	 */
	public function ssl() {

		return $this->ssl;
	}

	/**
	 * Get the Transients object.
	 *
	 * @since 1.4.0
	 *
	 * @return Transients
	 */
	public function transients() {

		return $this->transients;
	}

	/**
	 * Get the WordPress object.
	 *
	 * @since 1.4.0
	 *
	 * @return WordPress
	 */
	public function wordpress() {

		return $this->wordpress;
	}

	/**
	 * Set all the things up.
	 *
	 * @since 1.4.0
	 */
	public function setup() {

		// If the plugin version has been upgraded, cleans up the transients and updates the 'wphc_version' option.
		$upgrade = new Upgrade();

		$upgrade->maybe_upgrade_db();

		// Set up the objects.
		$this->options    = new Options();
		$this->plugins    = new Plugins();
		$this->server     = new Server();
		$this->ssl        = new SSL();
		$this->transients = new Transients();
		$this->wordpress  = new WordPress();
	}
}
