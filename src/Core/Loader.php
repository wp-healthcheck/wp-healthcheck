<?php
namespace WPHC\Core;

/**
 * The Core\Loader class.
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class Loader {
	private $options;
	private $plugins;
	private $server;
	private $ssl;
	private $transients;
	private $wordpress;

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {
		$this->setup();
	}

	public function options() {
		return $this->options;
	}

	public function plugins() {
		return $this->plugins;
	}

	public function server() {
		return $this->server;
	}

	public function ssl() {
		return $this->ssl;
	}

	public function transients() {
		return $this->transients;
	}

	public function wordpress() {
		return $this->wordpress;
	}

	/**
	 * Set all the things up.
	 *
	 * @since 1.4.0
	 */
	public function setup() {
		$this->options    = new Options();
		$this->plugins    = new Plugins();
		$this->server     = new Server();
		$this->ssl        = new SSL();
		$this->transients = new Transients();
		$this->wordpress  = new WordPress();
	}
}
