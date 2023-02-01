<?php
namespace WPHC\Core;

use WPHC\Admin\AJAX;

/**
 * The Core\Loader class.
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class Loader {
	private $ajax;
	private $server;
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

	public function ajax() {
		return $this->ajax;
	}

	public function options() {
		return $this->options;
	}

	public function server() {
		return $this->server;
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
		$this->ajax       = new AJAX();
		$this->options    = new Options();
		$this->server     = new Server();
		$this->transients = new Transients();
		$this->wordpress  = new WordPress();
	}
}
